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

add_action('bbconnect_admin_profile_activity', 'bbconnect_user_activity_log');
function bbconnect_user_activity_log() {
    global $user_id;
    $activities = bbconnect_get_recent_activity($user_id);
    bbconnect_output_activity_log($activities, $user_id);
}

add_action('wp_ajax_bbconnect_activity_log_load_page', 'bbconnect_activity_log_load_page');
function bbconnect_activity_log_load_page() {
    extract($_POST);
    if (!isset($user_id) || !isset($from_date) || !isset($to_date)) {
        die('Missing or invalid parameters');
    }
    $activities = bbconnect_get_recent_activity($user_id, $from_date, $to_date);
    bbconnect_output_activity_log_page($activities, $user_id);
    die();
}

function bbconnect_output_activity_log($activities, $user_id = null) {
?>
        <table class="wp-list-table striped widefat activity-log">
            <tbody id="bbconnect_activity_log_items">
<?php
        bbconnect_output_activity_log_page($activities, $user_id);
?>
            </tbody>
            <tr id="bbconnect_activity_loadmore_wrapper">
                <td colspan="5"><p style="text-align: center;"><a class="button" id="bbconnect_activity_loadmore">Load More</a></p></td>
            </tr>
        </table>
        <script>
            jQuery(document).ready(function() {
                var processing = false;
                var to_date = new Date();
                var from_date = new Date();
                to_date.setDate(from_date.getDate() - 7);
                from_date.setDate(to_date.getDate() - 6);
                jQuery('#bbconnect_activity_loadmore').click(function() {
                    if (processing) {
                        return;
                    }
                    processing = true;
                    var the_button = jQuery(this);
                    the_button.html('<i class="dashicons dashicons-update bbspin"></i>');
                    jQuery.post(ajaxurl,
                            {
                                action: 'bbconnect_activity_log_load_page',
                                from_date: jQuery.datepicker.formatDate('yy-mm-dd', from_date),
                                to_date: jQuery.datepicker.formatDate('yy-mm-dd', to_date),
                                user_id: '<?php echo $user_id; ?>'
                            },
                            function(data) {
                                jQuery('table.activity-log tbody#bbconnect_activity_log_items').append(data);
                                from_date.setDate(from_date.getDate() - 7);
                                to_date.setDate(to_date.getDate() - 7);
                                the_button.html('Load More');
                                processing = false;
                            }
                    );
                });
            });
        </script>
<?php
}

function bbconnect_output_activity_log_page($activities, $user_id) {
    if (count($activities) > 0) {
        $last_date = null;
        $datetime_format = get_option('date_format').' '.get_option('time_format');
        foreach ($activities as $activity) {
            $activity_datetime = bbconnect_get_datetime($activity['date']);
            if ($activity_datetime->format('Y-m-d') != $last_date) {
?>
            <tr>
                <th colspan="5"><h2><?php echo $activity_datetime->format('jS F'); ?></h2></th>
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
    } else {
?>
            <tr>
                <td colspan="5">
                    <p style="text-align: center;">No more activities to display</p>
                    <script>
                        jQuery('#bbconnect_activity_loadmore_wrapper').remove();
                    </script>
                </td>
            </tr>
<?php
    }
}

function bbconnect_get_recent_activity($user_id = null, $from_date = null, $to_date = null) {
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

    // Get everything from last 7 days by default
    if (empty($from_date)) {
        $from_date = strtotime('-6 days');
    }
    $from_datetime = bbconnect_get_datetime($from_date);

    if (empty($to_date)) {
        $to_date = time();
    }
    $to_datetime = bbconnect_get_datetime($to_date);

    // Notes
    $args = array(
            'post_type' => 'bb_note',
            'posts_per_page' => -1,
            'post_status' => array('publish', 'private'),
            'date_query' => array(
                    array(
                            'after' => array(
                                    'year'  => $from_datetime->format('Y'),
                                    'month' => $from_datetime->format('m'),
                                    'day'   => $from_datetime->format('d'),
                            ),
                            'before' => array(
                                    'year'  => $to_datetime->format('Y'),
                                    'month' => $to_datetime->format('m'),
                                    'day'   => $to_datetime->format('d'),
                            ),
                            'inclusive' => true,
                    ),
            ),
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
        // GF stores entries in DB timezone not WP timezone, so we're working on the assumption that's UTC
        $utc = bbconnect_get_timezone('UTC');
        $gf_from_datetime = clone($from_datetime);
        $gf_from_datetime->setTimezone($utc);
        $gf_to_datetime = clone($to_datetime);
        $gf_to_datetime->setTimezone($utc);

        $search_criteria = array(
                'start_date' => $gf_from_datetime->format('Y-m-d'),
                'end_date' => $gf_to_datetime->format('Y-m-d'),
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

        $offset = 0;
        $page_size = 100;
        $entries = array();
        do {
            $paging = array('offset' => $offset, 'page_size' => $page_size);
            $entries = array_merge($entries, GFAPI::get_entries(0, $search_criteria, null, $paging, $total_count));
            $offset += $page_size;
        } while ($offset < $total_count);

        foreach ($entries as $entry) {
            if (!isset($forms[$entry['form_id']])) {
                $forms[$entry['form_id']] = GFAPI::get_form($entry['form_id']);
            }
            $created = bbconnect_get_datetime($entry['date_created'], $utc); // Again we're assuming DB is configured to use UTC...
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
        $where = ' AND n.date_created BETWEEN "'.$gf_from_datetime->format('Y-m-d').'" AND "'.$gf_to_datetime->format('Y-m-d').' 23:59:59" ';
        if ($user_id) {
            $where .= ' AND (user_id = '.$user_id.' OR l.created_by = '.$user_id.') ';
        }
        $results = $wpdb->get_results('SELECT n.*, l.form_id FROM '.$wpdb->prefix.'rg_lead_notes n JOIN '.$wpdb->prefix.'rg_lead l ON (l.id = n.lead_id) WHERE 1=1 '.$where.' ORDER BY n.date_created DESC;');
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
    $where = ' AND created_at BETWEEN "'.$from_datetime->format('Y-m-d').'" AND "'.$to_datetime->format('Y-m-d').' 23:59:59" ';
    if ($user_id) {
        $where .= ' AND user_id = '.$user_id;
    }
    $results = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'bbconnect_activity_tracking WHERE 1=1 '.$where.' ORDER BY created_at DESC;');
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

    $activities = apply_filters('bbconnect_get_recent_activity', $activities, $user_id, $from_datetime, $to_datetime);

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
