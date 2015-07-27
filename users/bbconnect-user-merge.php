<?php
// add_action('admin_menu', 'bb_merge_users');

function bb_merge_users() {
    $settings_page = add_submenu_page('users.php', 'Merge Contacts', 'Merge Contacts', 'list_users', 'bb-merge-users-page', 'bb_merge_users_page');
}

function bb_merge_users_page() {
    if (!current_user_can('list_users')) { // @todo who should this be restricted to?
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    echo '<div class="wrap">'."\n";
    echo '<h1>Merge Contacts</h1>'."\n";

    $args = array(
            'orderby' => 'display_name',
    );
    $users = get_users($args);
    $from_user = $to_user = '';
    $confirm_needed = false;

    if (!empty($_REQUEST['from_user']) && !empty($_REQUEST['to_user'])) {
        $from_user = $_REQUEST['from_user'];
        $to_user = $_REQUEST['to_user'];

         // @todo sanity checking

        if (isset($_GET['confirm']) && $_GET['confirm'] == 'merge') {
            bb_merge_users_process($from_user, $to_user);
            $from_user = $to_user = '';
        } else {
            $confirm_needed = true;
            $from_user_obj = get_userdata($from_user);
            $to_user_obj = get_userdata($to_user);
            echo '<h2>Please Confirm</h2>'."\n";
            echo '<div class="error"><p>Are you sure you want to merge contact #'.$from_user.' - '.$from_user_obj->display_name.' ('.$from_user_obj->user_email.') into contact #'.$to_user.' - '.$to_user_obj->display_name.' ('.$to_user_obj->user_email.')?</p>'."\n";
            echo '<p>This will transfer all the history from contact #'.$from_user.' to contact #'.$to_user.' and delete contact #'.$from_user.' permanently!</p>'."\n";
            echo '<p><a href="?page=bb-merge-users-page" class="button">I\'m having second thoughts...</a>'."\n";
            echo '<a href="?page=bb-merge-users-page&from_user='.$from_user.'&to_user='.$to_user.'&confirm=merge" class="button trash">Yes, I\'m sure</a></p></div>'."\n";
        }
    }

    if (!$confirm_needed) {
?>
    <form action="?page=bb-merge-users-page" method="post">
        Merge From Contact: <?php echo bb_merge_users_generate_select('from_user', $users, $from_user); ?><br>
        Merge Into Contact: <?php echo bb_merge_users_generate_select('to_user', $users, $to_user); ?><br>
        <?php submit_button(__('Merge', 'bb_merge_contacts'), 'primary', 'submit'); ?>
    </form>
<?php
    }
    echo '</div>'."\n";
}

function bb_merge_users_generate_select($name, $users, $selected) {
    $select = '<select name="'.$name.'">'."\n";
    $select .= '<option value="" '.selected('' == $selected, true, false).'>Please Select</option>'."\n";
    foreach ($users as $user) {
        $select .= '<option value="'.$user->ID.'" '.selected($user->ID == $selected, true, false).'>'.$user->display_name.' ('.$user->user_email.')</option>'."\n";
    }
    $select .= '</select>'."\n";
    return $select;
}

function bb_merge_users_process($from_user, $to_user, $return = false) {
    global $wpdb;
    // GF doesn't hook into delete_user so we need to change leads manually
    $wpdb->query('UPDATE '.GFFormsModel::get_lead_table_name().' SET created_by = '.$to_user.' WHERE created_by = '.$from_user);
    // But WP handles the rest of it for us :-)
    wp_delete_user($from_user, $to_user);

    if ($return) return true;

    echo '<div class="updated"><p>Merge Complete</p></div>'."\n";
}
