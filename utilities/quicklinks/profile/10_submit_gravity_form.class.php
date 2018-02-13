<?php
/**
 * Add Gravity Form Entry quicklink
 * @author markparnell
 */
class profile_10_submit_gravity_form_quicklink extends bb_modal_quicklink {
    public function __construct() {
        parent::__construct();
        $this->title = 'Submit Form';
        $this->modal_width = 800;
    }

    protected function modal_contents(array $user_ids = array(), array $args = array()) {
        echo '<p>Which form would you like to complete on behalf of this user?</p>'."\n";
        echo '<div class="bbconnect-form-tiles">'."\n";
        $t_period = DAY_IN_SECONDS;
        $transient_forms = 'bbconnect_forms';
        if ($_GET['transient'] == 'false') delete_transient($transient_forms);
        if (false === ($forms = get_transient($transient_forms))) {
            $forms = GFAPI::get_forms();
            $forms = apply_filters('bbconnect_gf_quicklink_form_list', $forms);
            usort($forms, array($this, 'sort_forms'));
            set_transient($transient_forms, $forms, $t_period);
        }
        foreach ($forms as $form) {
            if (strstr($form['title'], '[')) {
                $form_namespace = substr($form['title'], strpos($form['title'], '['), strpos($form['title'], ']')+1);
                $title = str_replace($form_namespace, '', $form['title']);
            } else {
                $title = $form['title'];
            }
            echo '<a href="users.php?page=bbconnect_submit_gravity_form&user_id='.$user_ids[0].'&form_id='.$form['id'].'" target="_blank" onclick="tb_remove();">'."\n";
            echo '    <span class="dashicons dashicons-format-aside"></span>';
            echo '    <p><strong>'.$title.'</strong><br>'.$form['description'].'</p>'."\n";
            echo '</a>'."\n";
        }
        echo '</div>'."\n";
    }

    private function sort_forms($a, $b) {
        return strcasecmp($a['title'], $b['title']);
    }
}
