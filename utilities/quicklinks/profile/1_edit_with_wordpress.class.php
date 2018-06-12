<?php
/**
 * Edit With Wordpress quicklink
 * @author markparnell
 */
class profile_1_edit_with_wordpress_quicklink extends bb_page_quicklink {
    public function __construct() {
        parent::__construct();
        if (isset($_GET['user_id'])) {
            $user_id = $_GET['user_id'];
        } else {
            $user_id = get_current_user_id();
        }
        if (get_current_user_id() == $user_id) {
            $edit_link = get_edit_profile_url($user_id);
        } else {
            $edit_link = add_query_arg('user_id', $user_id, self_admin_url('user-edit.php'));
        }
        $edit_link = add_query_arg('no_redirect', '1', $edit_link);
        $this->url = $edit_link;
        $this->title = 'Edit with Wordpress';
    }
}
