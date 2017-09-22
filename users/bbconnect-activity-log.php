<?php
function bbconnect_activity_log() {
    $activities = bbconnect_get_recent_activity();
?>
    <div id="bbconnect" class="wrap">
        <h1><a href="<?php echo admin_url( 'users.php?page=bbconnect_activity_log' ); ?>" class="bbconnect-refresh"><?php _e( 'Activity Log', 'bbconnect' ); ?></a></h1>
        <?php bbconnect_output_activity_log($activities); ?>
    </div>
<?php
}

function bbconnect_output_activity_log($activities, $user_id = null) {
?>
        <table class="wp-list-table striped widefat activity-log">
<?php
    $cols = 5;
    $last_date = null;
    $datetime_format = get_option('date_format').' '.get_option('time_format');
    foreach ($activities as $activity) {
        $activity_datetime = bbconnect_get_datetime($activity['date']);
        if ($activity_datetime->format('Y-m-d') != $last_date) {
?>
            <tr>
                <th colspan="<?php echo $cols; ?>"><h2><?php echo $activity_datetime->format('jS F'); ?></h2></th>
            </tr>
<?php
        }
?>
            <tr>
                <td class="center"><p><img src="<?php echo apply_filters('bbconnect_activity_icon', '', $activity['type']); ?>" alt="<?php echo $activity['type']; ?>" title="<?php echo $activity['type']; ?>"></p></td>
<?php
            $user_display = $activity['user'];
            if (!empty($activity['user_id']) && (empty($user_id) || $user_id != $activity['user_id'])) {
                $user_display = '<a href="?page=bbconnect_edit_user&user_id='.$activity['user_id'].'&tab=activity">'.$user_display.'</a>';
            }
?>
                <td>
                    <h3><?php echo $user_display; ?></h3>
<?php
            if (!empty($activity['user_info'])) {
?>
                    <span class="secondary"><?php echo $activity['user_info']; ?></span>
<?php
            }
?>
                </td>
                <td>
                    <h3><?php echo $activity['title']; ?></h3>
                    <?php echo $activity['details']; ?>
                </td>
                <td><?php echo !empty($activity['extra']) ? $activity['extra'] : '&nbsp;'; ?></td>
                <td class="right"><p><?php echo $activity_datetime->format($datetime_format); ?></p></td>
            </tr>
<?php
        $last_date = $activity_datetime->format('Y-m-d');
    }
?>
        </table>
<?php
}

add_action('bbconnect_admin_profile_activity', 'bbconnect_user_activity_log');
function bbconnect_user_activity_log() {
    $user_id = $_GET['user_id']; // @todo does BBConnect have a nicer way of getting the ID of the user we're looking at?
    $activities = bbconnect_get_recent_activity($user_id);
    bbconnect_output_activity_log($activities, $user_id);
}

function bbconnect_get_recent_activity($user_id = null) {
    global $wpdb;

    $activities = $userlist = array();
    if ($user_id) {
        $userlist[$user_id] = get_user_by('id', $user_id);
    } else {
        $users = get_users();
        foreach ($users as $user) {
            $userlist[$user->ID] = $user;
        }
    }

    // Notes
    $args = array(
            'post_type' => 'bb_note',
            'posts_per_page' => 100,
            'post_status' => array('publish', 'private'),
    );
    if ($user_id) {
        $args['author'] = $user_id;
    }
    $notes = get_posts($args);
    foreach ($notes as $note) {
        $activities[] = array(
                'date' => $note->post_date,
                'user' => $userlist[$note->post_author]->display_name,
                'user_id' => $note->post_author,
                'title' => $note->post_title,
                'details' => apply_filters('the_content', $note->post_content),
                'type' => 'note',
        );
    }

    // Form Entries
    if (class_exists('GFAPI')) { // Gravity Forms
        $search_criteria = array(
                'field_filters' => array(
                        array(
                                'key' => 'status',
                                'value' => 'active',
                        ),
                ),
        );
        if ($user_id) {
            $search_criteria['field_filters'][] = array('key' => 'created_by', 'value' => $user_id);
        }
        $paging = array('offset' => 0, 'page_size' => 100);
        $entries = GFAPI::get_entries(0, $search_criteria, null, $paging);
        foreach ($entries as $entry) {
            if (!isset($forms[$entry['form_id']])) {
                $forms[$entry['form_id']] = GFAPI::get_form($entry['form_id']);
            }
            $created = bbconnect_get_datetime($entry['date_created'], bbconnect_get_timezone('UTC')); // We're assuming DB is configured to use UTC...
            $created->setTimezone(bbconnect_get_timezone()); // Convert to local timezone
            $user_name = !empty($entry['created_by']) ? $userlist[$entry['created_by']]->display_name : 'Anonymous User';
            $agent_details = '';
            $agent = null;
            if (!empty($entry['agent_id']) && $entry['agent_id'] != $entry['created_by']) {
                if (!array_key_exists($entry['agent_id'], $userlist)) {
                    $userlist[$entry['agent_id']] = new WP_User($entry['agent_id']);
                }
                $agent = $userlist[$entry['agent_id']];
                $agent_details = ' (Submitted by '.$userlist[$entry['agent_id']]->display_name.')';
            } else {
                $agent = $userlist[$entry['created_by']];
            }
            $activity_type = 'form';
            foreach ($forms[$entry['form_id']]['fields'] as $field) {
                if ($field->uniqueName == 'bb_activity_type') {
                    $activity_type = $entry[$field->id];
                }
            }
            $activity = array(
                    'date' => $created->format('Y-m-d H:i:s'),
                    'user' => $user_name,
                    'user_id' => $entry['created_by'],
                    'title' => 'Form Submission: '.$forms[$entry['form_id']]['title'].$agent_details,
                    'details' => '<a href="/wp-admin/admin.php?page=gf_entries&view=entry&id='.$entry['form_id'].'&lid='.$entry['id'].'" target="_blank">View Entry #'.$entry['id'].'</a>',
                    'type' => $activity_type,
            );
            $activity = apply_filters('bbconnect_form_activity_details', $activity, $forms[$entry['form_id']], $entry, $agent);
            $activities[] = $activity;
        }

        // Form Notes
        $where = '';
        if ($user_id) {
            $where .= ' AND (user_id = '.$user_id.' OR l.created_by = '.$user_id.') ';
        }
        $results = $wpdb->get_results('SELECT n.*, l.form_id FROM '.$wpdb->prefix.'rg_lead_notes n JOIN '.$wpdb->prefix.'rg_lead l ON (l.id = n.lead_id) WHERE 1=1 '.$where.' ORDER BY date_created DESC LIMIT 100;');
        foreach ($results as $result) {
            $created = bbconnect_get_datetime($result->date_created, bbconnect_get_timezone('UTC')); // We're assuming DB is configured to use UTC...
            $created->setTimezone(bbconnect_get_timezone()); // Convert to local timezone
            $activities[] = array(
                    'date' => $created->format('Y-m-d H:i:s'),
                    'user' => $result->user_name,
                    'user_id' => $result->user_id,
                    'title' => 'Form Note Added',
                    'details' => $result->value.'<br><a href="/wp-admin/admin.php?page=gf_entries&view=entry&id='.$result->form_id.'&lid='.$result->lead_id.'" target="_blank">View Entry #'.$result->lead_id.'</a>',
                    'type' => 'form',
            );
        }
    }

    // CRM Activities
    $where = '';
    if ($user_id) {
        $where .= ' AND user_id = '.$user_id;
    }
    $results = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'bbconnect_activity_tracking WHERE 1=1 '.$where.' ORDER BY created_at DESC LIMIT 100;');
    foreach ($results as $result) {
        $user_name = !empty($result->user_id) ? $userlist[$result->user_id]->display_name : 'Anonymous User';
        $activities[] = array(
                'date' => $result->created_at,
                'user' => $user_name,
                'user_id' => $result->user_id,
                'title' => $result->title,
                'details' => $result->description,
                'type' => $result->activity_type,
        );
    }

    $activities = apply_filters('bbconnect_get_recent_activity', $activities, $user_id);

    usort($activities, 'bbconnect_sort_activities');

    return $activities;
}

function bbconnect_sort_activities($a, $b) {
    $a_date = bbconnect_get_datetime($a['date']);
    $b_date = bbconnect_get_datetime($b['date']);

    return $a_date > $b_date ? -1 : 1;
}

add_filter('bbconnect_form_activity_details', 'bbconnect_form_activity_details', 1, 4);
function bbconnect_form_activity_details($activity, $form, $entry, $agent) {
    switch ($form['id']) {
        case bbconnect_get_send_email_form():
            if (!empty($entry[10])) { // To: Number
                $activity['title'] = 'SMS sent by '.$agent->display_name;
            } else {
                $activity['title'] = 'Email sent by '.$agent->display_name.': "'.$entry[4].'"'; // Subject
            }
            $activity['details'] = $entry[5].'<br>'.$activity['details']; // Message
            break;
        case bbconnect_get_action_form():
            $activity['title'] = 'Action Recorded: '.$entry[1]; // Action type
            $activity['details'] = $entry[8].'<br>'.$activity['details']; // Details
            $activity['type'] = 'action';
            break;
    }
    return $activity;
}

function bbconnect_activity_icon($icon, $activity_type) {
    switch ($activity_type) {
        case 'transaction':
            return trailingslashit(BBCONNECT_URL).'assets/g/activity-log/transaction.png';
            break;
        case 'email':
            return trailingslashit(BBCONNECT_URL).'assets/g/activity-log/subscription.png';
            break;
        case 'note':
            return trailingslashit(BBCONNECT_URL).'assets/g/activity-log/note.png';
            break;
        case 'form':
        case 'activity':
        default:
            return trailingslashit(BBCONNECT_URL).'assets/g/activity-log/activity.png';
            break;
    }
}

/**
 * Add an activity to the log
 * @param array|string $args Either an array or querystring-style list of parameters
 *     string $type Optional. Type of activity. Default is 'activity'
 *     string $source Optional. Entry source (e.g. plugin slug). Default is 'bbconnect'
 *     string $date Optional. Date of activity. Default is current timestamp
 *     string $user_id Optional if email specified. User that performed the activity
 *     string $email Optional if user ID specified. Email address for user that performed the activity
 *     string $title Activity name
 *     string $description Optional. Details of the activity. May contain HTML.
 * @return int|false ID of new log entry or false on failure
 */
function bbconnect_track_activity($args) {
    is_array($args) ? extract($args) : parse_str($args);
    // Some basic validation
    if ((empty($user_id) && empty($email)) || empty($title)) {
        return false;
    }

    // Set some defaults
    if (empty($user_id)) {
        $user = get_user_by('email', $email);
        $user_id = $user->ID;
    } elseif (empty($email)) {
        $user = new WP_User($user_id);
        $email = $user->user_email;
    }

    if (empty($type)) {
        $type = 'activity';
    }

    if (empty($source)) {
        $source = 'bbconnect';
    }

    if (empty($date)) {
        $date = bbconnect_get_current_datetime()->format('Y-m-d H:i:s');
    }

    // Now store it
    global $wpdb;
    $data = array(
            'activity_type' => $type,
            'source' => $source,
            'created_at' => $date,
            'user_id' => $user_id,
            'email' => $email,
            'title' => $title,
            'description' => $description,
    );
    $format = array(
            '%s',
            '%s',
            '%s',
            '%d',
            '%s',
            '%s',
            '%s',
    );
    return $wpdb->insert($wpdb->prefix.'bbconnect_activity_tracking', $data, $format);
}
