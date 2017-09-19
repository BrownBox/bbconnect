<?php
/**
 * Export without note quicklink
 * @author markparnell
 */
class reports_30_export_without_note_quicklink extends bb_form_quicklink {
    var $trigger_export = true;

    public function __construct() {
        parent::__construct();
        $this->title = 'Export without Note';
    }

    protected function form_contents(array $user_ids = array(), array $args = array()) {}

    public static function post_submission() {
        extract($_POST);
        return true;
    }
}