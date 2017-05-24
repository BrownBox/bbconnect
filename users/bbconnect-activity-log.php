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
    $cols = empty($user_id) ? 4 : 3;
    $last_date = null;
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
                <td class="center"><p><img src="<?php echo apply_filters('bbconnect_activity_icon', $activity['type']); ?>" alt="<?php echo $activity['type']; ?>" title="<?php echo $activity['type']; ?>"></p></td>
<?php
        if (empty($user_id)) {
            $user_display = $activity['user'];
            if (!empty($activity['user_id'])) {
                $user_display = '<a href="?page=bbconnect_edit_user&user_id='.$activity['user_id'].'&tab=activity">'.$user_display.'</a>';
            }
?>
                <td>
                    <h3><?php echo $user_display; ?></h3>
                </td>
<?php
        }
?>
                <td>
                    <h3><?php echo $activity['title']; ?></h3>
                    <?php echo $activity['details']; ?>
                </td>
                <td class="right"><p><?php echo $activity_datetime->format('j/m/Y h:ia'); ?></p></td>
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
        $search_criteria = array();
        if ($user_id) {
            $search_criteria['field_filters'][] = array('key' => 'created_by', 'value' => $user_id);
        }
        $paging = array('offset' => 0, 'page_size' => 100);
        $entries = GFAPI::get_entries(0, $search_criteria, array(), $paging);
        foreach ($entries as $entry) {
            if (!isset($forms[$entry['form_id']])) {
                $forms[$entry['form_id']] = GFAPI::get_form($entry['form_id']);
            }
            $user_name = !empty($entry['created_by']) ? $userlist[$entry['created_by']]->display_name : 'Anonymous User';
            $activities[] = array(
                    'date' => $entry['date_created'],
                    'user' => $user_name,
                    'user_id' => $entry['created_by'],
                    'title' => $forms[$entry['form_id']]['title'],
                    'details' => '<a href="/wp-admin/admin.php?page=gf_entries&view=entry&id='.$entry['form_id'].'&lid='.$entry['id'].'" target="_blank">View Entry</a>',
                    'type' => 'form',
            );
        }
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

function bbconnect_activity_icon($activity_type) {
    switch ($activity_type) {
        case 'transaction':
            return trailingslashit(BBCONNECT_URL).'assets/g/activity-log/transaction.png';
            break;
        case 'email':
            return trailingslashit(BBCONNECT_URL).'assets/g/activity-log/email.png';
            break;
        case 'note':
            return trailingslashit(BBCONNECT_URL).'assets/g/activity-log/note.png';
            break;
        case 'activity':
        default:
            return trailingslashit(BBCONNECT_URL).'assets/g/activity-log/activity.png';
            break;
    }
}
