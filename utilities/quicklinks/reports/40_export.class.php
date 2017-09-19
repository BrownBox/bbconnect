<?php
/**
 * Export quicklink
 * @author markparnell
 */
class reports_40_export_quicklink extends bb_form_quicklink {
    var $trigger_export = true;

    public function __construct() {
        parent::__construct();
        $this->title = 'Export with Note';
    }

    protected function form_contents(array $user_ids = array(), array $args = array()) {
        echo '<div class="modal-row"><label for="note_title">Title:</label><input type="text" name="note_title" class="full-width"></div>';
        echo '<div class="modal-row"><label for="note_content">Comments:</label><textarea id="note_content" name="note_content" rows="10"></textarea></div>';
    }

    public static function post_submission() {
        extract($_POST);
        if (empty($note_title) || empty($note_content)) {
            echo 'All fields are required.';
            return;
        }

        if (self::add_note($note_title, $note_content, explode(',', $user_ids))) {
            return true;
        } else {
            echo 'Unable to add note. Please try again.';
            return;
        }
    }
}
