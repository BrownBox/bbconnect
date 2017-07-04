<?php
/**
 * Export quicklink
 * @author markparnell
 */
class reports_40_export_quicklink extends bb_modal_quicklink {
    var $trigger_export = true;

    public function __construct() {
        parent::__construct();
        $this->title = 'Export with Note';
    }

    protected function form_contents(array $user_ids = array(), array $args = array()) {
        $this->output_note_type_selects();
        echo '<div class="modal-row"><label for="note_title">Title:</label><input type="text" name="note_title" class="full-width"></div>';
        echo '<div class="modal-row"><label for="note_content">Comments:</label><textarea id="note_content" name="note_content" rows="10"></textarea></div>';
        echo '<div class="modal-row"><label for="action_required">Action Required</label><input type="checkbox" name="action_required" value="1"></div>';
    }

    public static function post_submission() {
        extract($_POST);
        if (empty($note_type) || empty($note_subtype) || empty($note_title) || empty($note_content)) {
            echo 'All fields are required.';
            return;
        }

        if (self::add_note($note_title, $note_content, $note_type, $note_subtype, explode(',', $user_ids), array(), (bool)$action_required)) {
            return true;
        } else {
            echo 'Unable to add note. Please try again.';
            return;
        }
    }
}