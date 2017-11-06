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

function bbconnect_activity_log_days_per_page($user_id) {
    $days = !empty($user_id) ? 30 : 7;
    return apply_filters('bbconnect_activity_log_days_per_page', $days, $user_id);
}

function bbconnect_output_activity_log($activities, $user_id = null) {
    $days_per_page = bbconnect_activity_log_days_per_page($user_id);
    $activity_types = bbconnect_activity_type_list();
    $to_datetime = bbconnect_get_datetime();
    $to_datetime->sub(new DateInterval('P'.$days_per_page.'D'));
    $from_datetime = clone($to_datetime);
    $from_datetime->sub(new DateInterval('P'.($days_per_page-1).'D'));

    echo '<ul class="activity-log-filters">'."\n";
    echo '    <li class="button button-primary show-all" data-type="all">Show All Types</li>' . "\n";
    foreach ($activity_types as $type => $label) {
        echo '    <li class="button button-primary" data-type="'.$type.'">'.$label.'</li>'."\n";
    }
    echo '    <li class="button show-none" data-type="none">Hide All Types</li>' . "\n";
    echo '</ul>'."\n";
    echo '<p>Please note that activity log data may be delayed by up to an hour.</p>'."\n";
?>
    <table class="wp-list-table striped widefat activity-log">
<?php
    bbconnect_output_activity_log_page($activities, $user_id);
?>
        <tfoot id="bbconnect_activity_loadmore_wrapper">
            <tr>
                <td colspan="5"><p style="text-align: center;"><a class="button" id="bbconnect_activity_loadmore">Load More</a><i class="dashicons dashicons-update bbspin" id="bbconnect_activity_loading" style="display: none;"></i></p></td>
            </tr>
        </tfoot>
    </table>
    <script>
        jQuery(document).ready(function() {
            // Pagination
            var processing = false;
            var to_date = new Date('<?php echo $to_datetime->format('Y-m-d'); ?>');
            var from_date = new Date('<?php echo $from_datetime->format('Y-m-d'); ?>');
            jQuery('#bbconnect_activity_loadmore').click(function() {
                if (processing) {
                    return;
                }
                processing = true;
                var the_button = jQuery(this);
                the_button.hide();
                jQuery('#bbconnect_activity_loading').show();
                jQuery.post(ajaxurl,
                        {
                            action: 'bbconnect_activity_log_load_page',
                            from_date: jQuery.datepicker.formatDate('yy-mm-dd', from_date),
                            to_date: jQuery.datepicker.formatDate('yy-mm-dd', to_date),
                            user_id: '<?php echo $user_id; ?>'
                        },
                        function(data) {
                            jQuery('table.activity-log tfoot#bbconnect_activity_loadmore_wrapper').before(data);
                            bbconnect_activity_log_apply_filters();
                            from_date.setDate(from_date.getDate() - <?php echo $days_per_page; ?>);
                            to_date.setDate(to_date.getDate() - <?php echo $days_per_page; ?>);
                            the_button.show();
                            jQuery('#bbconnect_activity_loading').hide();
                            processing = false;
                        }
                );
            });

            // Filters
            jQuery('ul.activity-log-filters li').on('click', function() {
                var the_li = jQuery(this);
                var type = the_li.attr('data-type');
                if (type == 'all') {
                    jQuery('ul.activity-log-filters li:not(.show-all, .show-none)').addClass('button-primary');
                } else if (type == 'none') {
                    jQuery('ul.activity-log-filters li:not(.show-all, .show-none)').removeClass('button-primary');
                } else {
                    if (the_li.hasClass('button-primary')) {
                        the_li.removeClass('button-primary');
                    } else {
                        the_li.addClass('button-primary');
                    }
                }
                bbconnect_activity_log_apply_filters();
            });

            function bbconnect_activity_log_apply_filters() {
        	        jQuery('ul.activity-log-filters li').each(function() {
                    var the_li = jQuery(this);
                    var type = the_li.attr('data-type');
                    if (the_li.data('type') == 'all' || the_li.data('type') == 'none') {
                        return;
                    }
                    if (the_li.hasClass('button-primary')) {
                        jQuery('table.activity-log tbody tr.type-'+type).show();
                    } else {
                        jQuery('table.activity-log tbody tr.type-'+type).hide();
                    }
                    jQuery('table.activity-log tbody tr.date-header').each(function() {
                        if (jQuery(this).nextUntil('tr.date-header', ':visible').length > 0) {
                            jQuery(this).show();
                        } else {
                            jQuery(this).hide();
                        }
                    });
        	        });
            }
        });
    </script>
<?php
}

function bbconnect_output_activity_log_page($activities, $user_id) {
    echo '<tbody>';
    if (count($activities) > 0) {
        $activity_types = bbconnect_activity_type_list();
        $last_date = null;
        $datetime_format = get_option('date_format').' '.get_option('time_format');
        foreach ($activities as $activity) {
            $activity_type = isset($activity_types[$activity['type']]) ? $activity_types[$activity['type']] : $activity['type'];
            $activity_datetime = bbconnect_get_datetime($activity['created_at']);
            if ($activity_datetime->format('Y-m-d') != $last_date) {
?>
            <tr class="date-header">
                <th colspan="5"><h2><?php echo $activity_datetime->format('jS F'); ?></h2></th>
            </tr>
<?php
            }
?>
            <tr class="type-<?php echo $activity['type']; ?>">
                <td class="center"><p><img src="<?php echo apply_filters('bbconnect_activity_icon', '', $activity['type']); ?>" alt="<?php echo $activity['type']; ?>" title="<?php echo $activity_type; ?>"></p></td>
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
    echo '</tbody>';
}

function bbconnect_get_recent_activity($user_id = null, $from_date = null, $to_date = null) {
    global $wpdb;

    // Set up date filters
    $days_per_page = bbconnect_activity_log_days_per_page($user_id);
    $to_datetime = bbconnect_get_datetime($to_date);
    if (empty($from_date)) {
        $from_datetime = clone($to_datetime);
        $from_datetime->sub(new DateInterval('P'.($days_per_page-1).'D'));
    } else {
        $from_datetime = bbconnect_get_datetime($from_date);
    }

    $where = ' AND created_at BETWEEN "'.$from_datetime->format('Y-m-d').'" AND "'.$to_datetime->format('Y-m-d').' 23:59:59" ';
    if ($user_id) {
        $where .= ' AND user_id = '.$user_id;
    }

    $offset = 0;
    $page_size = 100;
    $activities = array();
    $sql = 'SELECT * FROM '.$wpdb->prefix.'bbconnect_activity_log WHERE 1=1 '.$where.' ORDER BY created_at DESC';
    do {
        $page_results = $wpdb->get_results($sql.' LIMIT '.$page_size.' OFFSET '.$offset, ARRAY_A);
        $activities = array_merge($activities, $page_results);
        $offset += $page_size;
    } while (count($page_results) > 0);

    $activities = apply_filters('bbconnect_activity_log', $activities);

    return $activities;
}

function bbconnect_update_activity_log() {
    global $wpdb;

    $userlist = array();

    // Notes
    $latest = $wpdb->get_var('SELECT MAX(external_id) FROM '.$wpdb->prefix.'bbconnect_activity_log WHERE external_ref = "note"');
    if ($latest) {
        $note_ids = $wpdb->get_col('SELECT ID FROM '.$wpdb->posts.' WHERE post_type = "bb_note" AND ID > '.$latest);
    }
    $offset = 0;
    $page_size = 100;
    do {
        $activities = array();
        $args = array(
                'post_type' => 'bb_note',
                'posts_per_page' => $page_size,
                'offset' => $offset,
                'orderby' => 'ID',
                'order' => 'ASC',
                'post_status' => array('publish', 'private'),
        );
        if (!empty($note_ids)) {
            $args['post__in'] = $note_ids;
        }
        $notes = get_posts($args);
        foreach ($notes as $note) {
            if (!isset($userlist[$note->post_author])) {
                $userlist[$note->post_author] = get_user_by('id', $note->post_author);
            }
            $activities[] = array(
                    'created_at' => $note->post_date,
                    'user' => $userlist[$note->post_author]->display_name,
                    'user_id' => $note->post_author,
                    'title' => $note->post_title,
                    'details' => apply_filters('the_content', $note->post_content),
                    'type' => 'note',
                    'external_id' => $note->ID,
                    'external_ref' => 'note',
            );
        }
        if (!empty($activities)) {
            bbconnect_insert_rows($wpdb->prefix.'bbconnect_activity_log', $activities);
        }
        $offset += $page_size;
    } while (count($notes) > 0);

    unset($note_ids, $notes);

    // Form Entries
    if (class_exists('GFAPI')) { // Gravity Forms
        $latest = $wpdb->get_var('SELECT MAX(external_id) FROM '.$wpdb->prefix.'bbconnect_activity_log WHERE external_ref = "form"');
        $search_criteria = array(
                'field_filters' => array(
                        array(
                                'key' => 'status',
                                'value' => 'active',
                        ),
                ),
        );
        if ($latest) {
            $search_criteria['field_filters'][] = array(
                    'key' => 'id',
                    'value' => $latest,
                    'operator' => '>',
            );
        }

        $utc = bbconnect_get_timezone('UTC');
        $local_tz = bbconnect_get_timezone();
        $sorting = array('key' => 'id', 'direction' => 'ASC', 'is_numeric' => true);
        $offset = 0;
        $page_size = 100;
        do {
            $activities = array();
            $paging = array('offset' => $offset, 'page_size' => $page_size);
            $entries = GFAPI::get_entries(0, $search_criteria, $sorting, $paging, $total_count);
            foreach ($entries as $entry) {
                $created = bbconnect_get_datetime($entry['date_created'], $utc); // We're assuming DB is configured to use UTC...
                $created->setTimezone($local_tz); // Convert to local timezone
                if (!empty($entry['created_by']) && !isset($userlist[$entry['created_by']])) {
                    $userlist[$entry['created_by']] = get_user_by('id', $entry['created_by']);
                }
                if (!empty($entry['agent_id']) && !isset($userlist[$entry['agent_id']])) {
                    $userlist[$entry['agent_id']] = get_user_by('id', $entry['agent_id']);
                }
                if (!isset($forms[$entry['form_id']])) {
                    $forms[$entry['form_id']] = GFAPI::get_form($entry['form_id']);
                }
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
                        'created_at' => $created->format('Y-m-d H:i:s'),
                        'user' => $user_name,
                        'user_id' => $entry['created_by'],
                        'title' => 'Form Submission: '.$forms[$entry['form_id']]['title'].$agent_details,
                        'details' => '<a class="form-entry" data-entry-id="'.$entry['id'].'" href="/wp-admin/admin.php?page=gf_entries&view=entry&id='.$entry['form_id'].'&lid='.$entry['id'].'" target="_blank">View Entry #'.$entry['id'].'</a>',
                        'type' => $activity_type,
                        'external_id' => $entry['id'],
                        'external_ref' => 'form',
                );
                $activity = apply_filters('bbconnect_form_activity_details', $activity, $forms[$entry['form_id']], $entry, $agent);
                $activities[] = $activity;
            }
            if (!empty($activities)) {
                bbconnect_insert_rows($wpdb->prefix.'bbconnect_activity_log', $activities);
            }
            $offset += $page_size;
        } while ($offset < $total_count);
        unset($entries);

        // Form Notes
        $latest = $wpdb->get_var('SELECT MAX(external_id) FROM '.$wpdb->prefix.'bbconnect_activity_log WHERE external_ref = "form_note"');
        $where = '';
        if ($latest) {
            $where = ' AND n.id > '.$latest;
        }

        $offset = 0;
        $page_size = 100;
        $sql = 'SELECT n.*, l.form_id FROM '.$wpdb->prefix.'rg_lead_notes n JOIN '.$wpdb->prefix.'rg_lead l ON (l.id = n.lead_id) WHERE 1=1 '.$where.' ORDER BY n.id ASC';
        do {
            $activities = array();
            $results = $wpdb->get_results($sql.' LIMIT '.$page_size.' OFFSET '.$offset);
            foreach ($results as $result) {
                $created = bbconnect_get_datetime($result->date_created, $utc); // Again we're assuming DB is configured to use UTC...
                $created->setTimezone($local_tz); // Convert to local timezone
                $activities[] = array(
                        'created_at' => $created->format('Y-m-d H:i:s'),
                        'user' => $result->user_name,
                        'user_id' => $result->user_id,
                        'title' => 'Form Note Added',
                        'details' => $result->value.'<br><a class="form-entry" data-entry-id="'.$entry['id'].'" href="/wp-admin/admin.php?page=gf_entries&view=entry&id='.$result->form_id.'&lid='.$result->lead_id.'" target="_blank">View Entry #'.$result->lead_id.'</a>',
                        'type' => 'form_note',
                        'external_id' => $result->id,
                        'external_ref' => 'form_note',
                );
            }
            if (!empty($activities)) {
                bbconnect_insert_rows($wpdb->prefix.'bbconnect_activity_log', $activities);
            }
            $offset += $page_size;
        } while (count($page_results) > 0);
        unset($results);
    }

    // CRM Activities
    $latest = $wpdb->get_var('SELECT MAX(external_id) FROM '.$wpdb->prefix.'bbconnect_activity_log WHERE external_ref = "bbconnect"');
    $where = '';
    if ($latest) {
        $where = ' AND id > '.$latest;
    }
    $offset = 0;
    $page_size = 100;
    $sql = 'SELECT * FROM '.$wpdb->prefix.'bbconnect_activity_tracking WHERE 1=1 '.$where.' ORDER BY id ASC';
    do {
        $activities = array();
        $results = $wpdb->get_results($sql.' LIMIT '.$page_size.' OFFSET '.$offset);
        foreach ($results as $result) {
            if (!empty($result->user_id) && !isset($userlist[$result->user_id])) {
                $userlist[$result->user_id] = get_user_by('id', $result->user_id);
            }
            $user_name = !empty($result->user_id) ? $userlist[$result->user_id]->display_name : 'Anonymous User';
            $activities[] = array(
                    'created_at' => $result->created_at,
                    'user' => $user_name,
                    'user_id' => $result->user_id,
                    'title' => $result->title,
                    'details' => $result->description,
                    'type' => $result->activity_type,
                    'external_id' => $result->id,
                    'external_ref' => 'bbconnect',
            );
        }
        if (!empty($activities)) {
            bbconnect_insert_rows($wpdb->prefix.'bbconnect_activity_log', $activities);
        }
        $offset += $page_size;
    } while (count($page_results) > 0);
    unset($results);

    $activities = array();
    $activities = apply_filters('bbconnect_update_activity_log', $activities);
    if (!empty($activities)) {
        bbconnect_insert_rows($wpdb->prefix.'bbconnect_activity_log', $activities);
    }
    return true;
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

function bbconnect_activity_type_list() {
    $types = array(
            'email' => 'Email',
            'form' => 'Form Entry',
            'note' => 'Note',
            'action' => 'Recorded Action',
            'activity' => 'User Activity',
    );
    $types = apply_filters('bbconnect_activity_types', $types);
    natcasesort($types);
    return $types;
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
        case 'form_note':
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
