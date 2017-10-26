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
        $forms = GFAPI::get_forms();
        $forms = apply_filters('bbconnect_gf_quicklink_form_list', $forms);
        usort($forms, array($this, 'sort_forms'));
        foreach ($forms as $form) {
            echo '<a href="users.php?page=bbconnect_submit_gravity_form&user_id='.$user_ids[0].'&form_id='.$form['id'].'" target="_blank">'."\n";
            echo '    <h3>'.$form['title'].'</h3>'."\n";
            echo '    <p>'.$form['description'].'</p>'."\n";
            echo '</a>'."\n";
        }
        echo '</div>'."\n";
    }

    private function sort_forms($a, $b) {
        return strcasecmp($a['title'], $b['title']);
    }
}
