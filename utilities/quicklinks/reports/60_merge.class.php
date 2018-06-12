<?php
/**
 * Merge quicklink
 * @author markparnell
 */
class reports_60_merge_quicklink extends bb_form_quicklink {
    var $modal_width = 800;

    public function __construct() {
        parent::__construct();
        $this->title = 'Merge Users';
    }

    protected function form_contents(array $user_ids = array(), array $args = array()) {
        echo '<style type="text/css">#TB_window select {height: auto;}</style>';
        $user_options = '';
        foreach ($user_ids as $user_id) {
            $user = get_userdata($user_id);
            $user_options .= '<option value="'.$user_id.'">'.$user->display_name.' ('.$user->user_email.')</option>';
        }
        echo '<table width="100%">';
        echo '    <tr>';
        echo '        <td width="40%">';
        echo '            <select name="from_user_id" size="6">';
        echo '                <option value="">Please select source user</option>';
        echo $user_options;
        echo '            </select>';
        echo '        </td>';
        echo '        <td rowspan="2" width="20%">></td>';
        echo '        <td width="40%">';
        echo '            <select name="to_user_id" size="6">';
        echo '                <option value="">Please select target user</option>';
        echo $user_options;
        echo '            </select><br>';
        echo '        </td>';
        echo '    </tr>';
        echo '    <tr>';
        echo '        <td width="40%">';
        echo '            or enter email address:<br>';
        echo '            <input type="text" name="from_user_email">';
        echo '        </td>';
        echo '        <td width="40%">';
        echo '            or enter email address:<br>';
        echo '            <input type="text" name="to_user_email">';
        echo '        </td>';
        echo '    </tr>';
        echo '</table>';
        echo '<p>Depending on the amount of history and other data associated with the source record this process can take some time. Please be patient while Connexions migrates the data to the target user.</p>'."\n";
    }

    public static function post_submission() {
        extract($_POST);
        if (empty($from_user_id) && !empty($from_user_email)) {
            $from_user = get_user_by_email($from_user_email);
            if (!$from_user) {
                echo 'Failed to find user with email "'.$from_user_email.'". Please check the address and try again.';
                return;
            }
            $from_user_id = $from_user->ID;
        }
        if (empty($to_user_id) && !empty($to_user_email)) {
            $to_user = get_user_by_email($to_user_email);
            if (!$to_user) {
                echo 'Failed to find user with email "'.$to_user_email.'". Please check the address and try again.';
                return;
            }
            $to_user_id = $to_user->ID;
        }
        if (empty($from_user_id) || empty($to_user_id)) {
            echo 'Please select a source user and a target user';
            return;
        }
        bbconnect_merge_users_process($from_user_id, $to_user_id, true);
    }
}
