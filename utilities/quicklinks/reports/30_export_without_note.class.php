<?php
/**
 * Export without note quicklink
 * @author markparnell
 */
class reports_30_export_without_note_quicklink extends bb_modal_quicklink {
    var $trigger_export = true;

    public function __construct() {
        parent::__construct();
        $this->title = 'Export without Note';
    }

    protected function form_contents(array $user_ids = array(), array $args = array()) {
       // $this->output_note_type_selects();
        // echo 'Title: <input type="text" name="note_title"><br>';
        // echo '<input type="checkbox" name="action_required" value="1"> Action Required<br>';
        // echo 'Content: <br><textarea id="note_content" name="note_content" rows="10"></textarea><br>';
    }

    public static function post_submission() {
        extract($_POST);
        // if (empty($note_type) || empty($note_subtype) || empty($note_title) || empty($note_content)) {
        //     echo 'All fields are required.';
        //     return;
        // }

        // if (self::add_note($note_title, $note_content, $note_type, $note_subtype, explode(',', $user_ids), array(), (bool)$action_required)) {
        //     return true;
        // } else {
        //     echo 'Unable to add note. Please try again.';
        //     return;
        // }
        return true;
    }
}