<?php
function bbconnect_get_send_email_form() {
    $send_email_form_id = get_option('bbconnect_send_email_form_id');
    $send_email_form = array(
            'title' => '[Connexions] Sent Messages',
            'description' => 'Connexions form for tracking sent emails and SMS messages',
            'is_active' => true,
            'cssClass' => 'bbconnect',
            'button' => array(
                    'type' => 'text',
                    'text' => 'Send',
                    'imageUrl' => ''
            ),
            'confirmations' => array(
                    0 => array(
                            'id' => '5965b5ea7ed44',
                            'name' => 'Default Confirmation',
                            'isDefault' => true,
                            'type' => 'message',
                            'message' => 'Your message has been sent.',
                    ),
            ),
            'fields' => array(
                    0 => array(
                            'type' => 'name',
                            'id' => 2,
                            'label' => 'To: Name',
                            'isRequired' => false,
                            'nameFormat' => 'advanced',
                            'cssClass' => 'readonly',
                            'inputs' => array(
                                    0 => array(
                                            'id' => '2.2',
                                            'label' => 'Prefix',
                                            'name' => '',
                                            'choices' => array(
                                                    0 => array(
                                                            'text' => 'Mr.',
                                                            'value' => 'Mr.',
                                                            'isSelected' => false,
                                                            'price' => ''
                                                    ),
                                                    1 => array(
                                                            'text' => 'Mrs.',
                                                            'value' => 'Mrs.',
                                                            'isSelected' => false,
                                                            'price' => ''
                                                    ),
                                                    2 => array(
                                                            'text' => 'Miss',
                                                            'value' => 'Miss',
                                                            'isSelected' => false,
                                                            'price' => ''
                                                    ),
                                                    3 => array(
                                                            'text' => 'Ms.',
                                                            'value' => 'Ms.',
                                                            'isSelected' => false,
                                                            'price' => ''
                                                    ),
                                                    4 => array(
                                                            'text' => 'Dr.',
                                                            'value' => 'Dr.',
                                                            'isSelected' => false,
                                                            'price' => ''
                                                    ),
                                                    5 => array(
                                                            'text' => 'Prof.',
                                                            'value' => 'Prof.',
                                                            'isSelected' => false,
                                                            'price' => ''
                                                    ),
                                                    6 => array(
                                                            'text' => 'Rev.',
                                                            'value' => 'Rev.',
                                                            'isSelected' => false,
                                                            'price' => ''
                                                    )
                                            ),
                                            'isHidden' => true,
                                            'inputType' => 'radio'
                                    ),
                                    1 => array(
                                            'id' => '2.3',
                                            'label' => 'First',
                                            'name' => ''
                                    ),
                                    2 => array(
                                            'id' => '2.4',
                                            'label' => 'Middle',
                                            'name' => '',
                                            'isHidden' => true
                                    ),
                                    3 => array(
                                            'id' => '2.6',
                                            'label' => 'Last',
                                            'name' => ''
                                    ),
                                    4 => array(
                                            'id' => '2.8',
                                            'label' => 'Suffix',
                                            'name' => '',
                                            'isHidden' => true
                                    )
                            ),
                    ),
                    1 => array(
                            'type' => 'email',
                            'id' => 3,
                            'label' => 'To: Email',
                            'isRequired' => false,
                            'cssClass' => 'readonly',
                    ),
                    2 => array(
                            'type' => 'text',
                            'id' => 10,
                            'label' => 'To: Number',
                            'isRequired' => false,
                            'cssClass' => 'readonly',
                    ),
                    3 => array(
                            'type' => 'text',
                            'id' => 4,
                            'label' => 'Subject',
                            'isRequired' => false,
                    ),
                    4 => array(
                            'type' => 'textarea',
                            'id' => 5,
                            'label' => 'Message',
                            'isRequired' => false,
                            'useRichTextEditor' => true,
                    ),
                    5 => array(
                            'type' => 'hidden',
                            'id' => 6,
                            'label' => 'Source',
                            'isRequired' => false,
                            'defaultValue' => 'bbconnect',
                            'cssClass' => 'readonly',
                    ),
                    6 => array(
                            'type' => 'hidden',
                            'id' => 7,
                            'label' => 'Activity Type',
                            'isRequired' => false,
                            'defaultValue' => 'email',
                            'cssClass' => 'readonly',
                            'uniqueName' => 'bb_activity_type',
                    ),
                    7 => array(
                            'type' => 'hidden',
                            'id' => 8,
                            'label' => 'Internal Reference',
                            'isRequired' => false,
                            'defaultValue' => '',
                            'cssClass' => 'readonly',
                            'uniqueName' => 'bb_internal_reference',
                    ),
                    8 => array(
                            'type' => 'hidden',
                            'id' => 9,
                            'label' => 'External Reference',
                            'isRequired' => false,
                            'defaultValue' => '',
                            'cssClass' => 'readonly',
                            'uniqueName' => 'bb_external_reference',
                    ),
            ),
    );

    if (!$send_email_form_id || !GFAPI::form_id_exists($send_email_form_id)) { // If form doesn't exist, create it
        $send_email_form_id = GFAPI::add_form($send_email_form);
        update_option('bbconnect_send_email_form_id', $send_email_form_id);
    } else { // Otherwise if we've created it previously, just update it to make sure it hasn't been modified and is the latest version
        $send_email_form['id'] = $send_email_form_id;
        GFAPI::update_form($send_email_form);
    }

    return $send_email_form_id;
}

function bbconnect_get_action_form() {
    $action_form_id = get_option('bbconnect_action_form_id');
    $action_form = array(
            'title' => '[Connexions] Action Notes',
            'description' => 'Connexions form for tracking actions',
            'is_active' => true,
            'cssClass' => 'bbconnect',
            'button' => array(
                    'type' => 'text',
                    'text' => 'Save',
                    'imageUrl' => '',
            ),
            'confirmations' => array(
                    0 => array(
                            'id' => '59767b01c4d04',
                            'name' => 'Default Confirmation',
                            'isDefault' => true,
                            'type' => 'message',
                            'message' => 'Action note saved successfully.',
                    )
            ),
            'fields' => array(
                    0 => array(
                            'type' => 'email',
                            'id' => 18,
                            'label' => 'User email',
                            'isRequired' => true,
                            'cssClass' => 'hidden',
                    ),
                    1 => array(
                            'type' => 'section',
                            'id' => 2,
                            'label' => 'Note Definition',
                            'isRequired' => false,
                            'visibility' => 'administrative',
                    ),
                    2 => array(
                            'type' => 'radio',
                            'id' => 1,
                            'label' => 'Note type',
                            'isRequired' => false,
                            'choices' => array(
                                    0 => array(
                                            'text' => 'observation/comment',
                                            'value' => 'note',
                                            'isSelected' => false,
                                            'price' => ''
                                    ),
                                    1 => array(
                                            'text' => 'communication/conversation/correspondence',
                                            'value' => 'comms',
                                            'isSelected' => false,
                                            'price' => ''
                                    ),
                                    2 => array(
                                            'text' => 'did/do work',
                                            'value' => 'work',
                                            'isSelected' => false,
                                            'price' => ''
                                    )
                            ),
                    ),
                    3 => array(
                            'type' => 'radio',
                            'id' => 3,
                            'label' => 'Comms type',
                            'isRequired' => false,
                            'choices' => array(
                                    0 => array(
                                            'text' => 'meeting',
                                            'value' => 'meeting',
                                            'isSelected' => false,
                                    ),
                                    1 => array(
                                            'text' => 'phone',
                                            'value' => 'phone',
                                            'isSelected' => false,
                                    ),
                                    2 => array(
                                            'text' => 'email',
                                            'value' => 'email',
                                            'isSelected' => false,
                                    ),
                                    3 => array(
                                            'text' => 'other',
                                            'value' => 'other',
                                            'isSelected' => false,
                                    ),
                            ),
                            'cssClass' => 'horizontal',
                            'conditionalLogic' => array(
                                    'actionType' => 'show',
                                    'logicType' => 'all',
                                    'rules' => array(
                                            0 => array(
                                                    'fieldId' => '1',
                                                    'operator' => 'is',
                                                    'value' => 'comms',
                                            ),
                                    ),
                            ),
                    ),
                    4 => array(
                            'type' => 'radio',
                            'id' => 4,
                            'label' => 'Work type',
                            'isRequired' => false,
                            'choices' => array(
                                    0 => array(
                                            'text' => 'progressed application',
                                            'value' => 'progressed application',
                                            'isSelected' => false,
                                    ),
                                    1 => array(
                                            'text' => 'other',
                                            'value' => 'other',
                                            'isSelected' => false,
                                    ),
                            ),
                            'cssClass' => 'horizontal',
                            'conditionalLogic' => array(
                                    'actionType' => 'show',
                                    'logicType' => 'all',
                                    'rules' => array(
                                            0 => array(
                                                    'fieldId' => '1',
                                                    'operator' => 'is',
                                                    'value' => 'work'
                                            )
                                    )
                            ),
                    ),
                    5 => array(
                            'type' => 'text',
                            'id' => 5,
                            'label' => 'Work Queue',
                            'isRequired' => false,
                            'visibility' => 'administrative',
                    ),
                    6 => array(
                            'type' => 'section',
                            'id' => 6,
                            'label' => 'Note Details',
                            'isRequired' => false,
                            'visibility' => 'administrative',
                    ),
                    7 => array(
                            'type' => 'text',
                            'id' => 7,
                            'label' => 'Headline',
                            'isRequired' => false,
                            'size' => 'large',
                    ),
                    8 => array(
                            'type' => 'textarea',
                            'id' => 8,
                            'label' => 'Details',
                            'isRequired' => false,
                            'useRichTextEditor' => true,
                    ),
                    9 => array(
                            'type' => 'fileupload',
                            'id' => 17,
                            'label' => 'Attachment',
                            'isRequired' => false,
                            'multipleFiles' => true,
                            'allowedExtensions' => 'jpg, png, pdf',
                    ),
                    10 => array(
                            'type' => 'section',
                            'id' => 13,
                            'label' => 'Follow Up Details',
                            'isRequired' => false,
                            'visibility' => 'administrative',
                    ),
                    11 => array(
                            'type' => 'checkbox',
                            'id' => 9,
                            'label' => '',
                            'isRequired' => false,
                            'choices' => array(
                                    0 => array(
                                            'text' => 'Follow up required',
                                            'value' => 'Follow up required',
                                            'isSelected' => false,
                                    ),
                            ),
                            'inputs' => array(
                                    0 => array(
                                            'id' => '9.1',
                                            'label' => 'Follow up required',
                                            'name' => '',
                                    ),
                            ),
                    ),
                    12 => array(
                            'type' => 'date',
                            'id' => 11,
                            'label' => 'Follow up date',
                            'isRequired' => false,
                            'dateType' => 'datepicker',
                            'calendarIconType' => 'none',
                            'conditionalLogic' => array(
                                    'actionType' => 'show',
                                    'logicType' => 'all',
                                    'rules' => array(
                                            0 => array(
                                                    'fieldId' => '9',
                                                    'operator' => 'is',
                                                    'value' => 'Follow up required'
                                            )
                                    )
                            ),
                    ),
                    13 => array(
                            'type' => 'textarea',
                            'id' => 14,
                            'label' => 'Follow up instructions',
                            'isRequired' => false,
                            'conditionalLogic' => array(
                                    'actionType' => 'show',
                                    'logicType' => 'all',
                                    'rules' => array(
                                            0 => array(
                                                    'fieldId' => '9',
                                                    'operator' => 'is',
                                                    'value' => 'Follow up required',
                                            ),
                                    ),
                            ),
                            'useRichTextEditor' => true,
                    ),
                    14 => array(
                            'type' => 'email',
                            'id' => 12,
                            'label' => 'Follow up notification email',
                            'isRequired' => false,
                            'conditionalLogic' => array(
                                    'actionType' => 'show',
                                    'logicType' => 'all',
                                    'rules' => array(
                                            0 => array(
                                                    'fieldId' => '9',
                                                    'operator' => 'is',
                                                    'value' => 'Follow up required',
                                            ),
                                    ),
                            ),
                    ),
            ),
    );

    if (!$action_form_id || !GFAPI::form_id_exists($action_form_id)) { // If form doesn't exist, create it
        $action_form_id = GFAPI::add_form($action_form);
        update_option('bbconnect_action_form_id', $action_form_id);
    } else { // Otherwise if we've created it previously, just update it to make sure it hasn't been modified and is the latest version
        $action_form['id'] = $action_form_id;
        GFAPI::update_form($action_form);
    }

    return $action_form_id;
}

function bbconnect_get_question_form(){
    $question_form_id = get_option('bbconnect_question_form_id');
    $id = 2;
    $question_lists = bbconnect_retrieve_question_data();

    $question_form = array(
            'title' => '[Connexions] Question Form',
            'description' => 'Connexions form for get to know you questions',
            'is_active' => true,
            'cssClass' => 'bbconnect',
            'button' => array(
                    'type' => 'text',
                    'text' => 'Submit',
                    'imageUrl' => '',
            ),
            'confirmations' => array(
                    0 => array(
                            'id' => '59727b81c1f09',
                            'name' => 'Default Confirmation',
                            'isDefault' => true,
                            'type' => 'message',
                            'message' => 'Thanks!',
                    )
            ),
            'fields' => array(
                    0 => array(
                            'type' => 'email',
                            'id' => 1,
                            'label' => 'User email',
                            'isRequired' => true,
                            'cssClass' => '',
                            'visibility' => 'hidden',
                    ),
            ),
    );

    foreach ($question_lists as $type => $questions) {
        foreach ($questions as $question) {
            switch ($question['options']['field_type']) {
                case 'radio':
                case 'select':
                    $field_type = 'radio';
                    break;
                default:
                    $field_type = $question['options']['field_type'];
                    break;
            }
            $choices = array();
            if (!empty($question['options']['choices'])) {
                foreach ($question['options']['choices'] as $value => $text) {
                    $choices[] = array(
                            'text' => $text,
                            'value' => $value,
                            'isSelected' => false,
                    );
                }
            }
            $question_form['fields'][] = array(
                    'type' => $field_type,
                    'id' => $id++,
                    'label' => $question['name'],
                    'isRequired' => false,
                    'cssClass' => '',
                    'bbQuestionType' => $type,
                    'choices' => $choices,
                    'usermeta_key' => $question['meta_key'],
            );
        }
    }

    if (!$question_form_id || !GFAPI::form_id_exists($question_form_id)) { // If form doesn't exist, create it
        $question_form_id = GFAPI::add_form($question_form);
        update_option('bbconnect_question_form_id', $question_form_id);
    } else { // Otherwise if we've created it previously, just update it to make sure it hasn't been modified and is the latest version
        $question_form['id'] = $question_form_id;
        GFAPI::update_form($question_form);
    }

    return $question_form_id;
}

function bbconnect_get_crm_user() {
    $user = get_user_by('login', 'connexions');
    if (!$user) {
        $user = new WP_User();
        $user->user_login = 'connexions';
        $user->user_email = 'connexions@brownbox.net.au';
        $user->first_name = 'Connexions';
        $user->last_name = 'System User';
        $user->user_pass = wp_generate_password();
        $user->ID = wp_insert_user($user);
    }
    return $user;
}

add_filter('bbconnect_get_crm_forms', 'bbconnect_get_crm_forms', 0);
function bbconnect_get_crm_forms(array $forms) {
    $forms[] = bbconnect_get_send_email_form();
    $forms[] = bbconnect_get_action_form();
    $forms[] = bbconnect_get_question_form();
    return $forms;
}

add_action('init', 'bbconnect_form_locking', 999); // Run as late as possible to make sure GF has inited first
function bbconnect_form_locking() {
    if (class_exists('GFFormLocking')) {
        class BBConnectGFFormLocking extends GFFormLocking {
            private $bbconnect_forms = array();

            public function __construct() {
                $this->bbconnect_forms = apply_filters('bbconnect_get_crm_forms', array());

                $this->_redirect_url = admin_url( 'admin.php?page=gf_edit_forms' );

                add_action('gform_form_list_column_title', array($this, 'form_list_form_title'));
                add_filter('gform_form_actions', array($this, 'form_list_lock_message'), 999, 2);
                parent::__construct();
            }

            protected function check_lock($object_id) {
                if (in_array($object_id, $this->bbconnect_forms)) {
                    return bbconnect_get_crm_user()->ID;
                }
                return parent::check_lock($object_id);
            }

            public function get_strings() {
                if (in_array($this->get_object_id(), $this->bbconnect_forms)) {
                    $strings = array(
                        'currently_locked'  => __('This form is managed by Connexions. You cannot edit this form.', 'bbconnect'),
                        'currently_editing' => __('This form is managed by Connexions. You cannot edit this form.', 'bbconnect'),
                    );

                    return array_merge(parent::get_strings(), $strings);
                }
                return parent::get_strings();
            }

            public function get_lock_ui($user_id) {
                if (in_array($this->get_object_id(), $this->bbconnect_forms)) {
                    $html = '<div id="gform-lock-dialog" class="notification-dialog-wrap">
                            <div class="notification-dialog-background"></div>
                            <div class="notification-dialog">
                                <div class="gform-locked-message">
                                    <div class="gform-locked-avatar"><img src="'.trailingslashit(BBCONNECT_URL).'assets/g/brand.png" alt=""></div>
                                    <p class="currently-editing" tabindex="0">'.$this->get_string('currently_locked').'</p>
                                    <p><a class="button" href="'.esc_url($this->_redirect_url).'">'.$this->get_string('cancel').'</a></p>
                                </div>
                            </div>
                         </div>';
                    return $html;
                }
                return parent::get_lock_ui($user_id);
            }

            public function form_list_form_title($form) {
                if (in_array($form->id, $this->bbconnect_forms)) {
                    echo '<strong>'.esc_html($form->title).'</strong>';
                } else {
                    echo '<strong><a href="?page=gf_edit_forms&id='.absint($form->id).'">'.esc_html($form->title).'</a></strong>';
                }
            }

            public function form_list_lock_message($form_actions, $form_id) {
                if (in_array($form_id, $this->bbconnect_forms)) {
                    echo __('This form is managed by Connexions. You cannot edit this form.<br>', 'bbconnect');
                    unset($form_actions['edit'], $form_actions['settings'], $form_actions['trash']);
                }
                return $form_actions;
            }
        }

        class BBConnectGFFormSettingsLocking extends GFFormSettingsLocking {
            private $bbconnect_forms = array();

            public function __construct() {
                $this->bbconnect_forms = apply_filters('bbconnect_get_crm_forms', array());

                $this->_redirect_url = admin_url( 'admin.php?page=gf_edit_forms' );

                parent::__construct();
            }

            protected function check_lock($object_id) {
                list($subview, $form_id) = explode('-', $object_id);
                if (in_array($form_id, $this->bbconnect_forms)) {
                    return bbconnect_get_crm_user()->ID;
                }
                return parent::check_lock($object_id);
            }

            public function get_strings() {
                $object_id = $this->get_object_id();
                list($subview, $form_id) = explode('-', $object_id);
                if (in_array($form_id, $this->bbconnect_forms)) {
                    $strings = array(
                        'currently_locked'  => __( 'This form is managed by Connexions. You cannot edit this form.', 'gravityforms' ),
                        'currently_editing' => 'This form is managed by Connexions. You cannot edit this form.',
                    );

                    return array_merge(parent::get_strings(), $strings);
                }
                return parent::get_strings();
            }

            public function get_lock_ui($user_id) {
                $object_id = $this->get_object_id();
                list($subview, $form_id) = explode('-', $object_id);
                if (in_array($form_id, $this->bbconnect_forms)) {
                    $html = '<div id="gform-lock-dialog" class="notification-dialog-wrap">
                            <div class="notification-dialog-background"></div>
                            <div class="notification-dialog">
                                <div class="gform-locked-message">
                                    <div class="gform-locked-avatar"><img src="'.trailingslashit(BBCONNECT_URL).'assets/g/brand.png" alt=""></div>
                                    <p class="currently-editing" tabindex="0">'.$this->get_string('currently_locked').'</p>
                                    <p><a class="button" href="'.esc_url($this->_redirect_url).'">'.$this->get_string('cancel').'</a></p>
                                </div>
                            </div>
                         </div>';
                    return $html;
                }
                return parent::get_lock_ui($user_id);
            }
        }
        $form_lock = new BBConnectGFFormLocking();
        $form_settings_lock = new BBConnectGFFormSettingsLocking();
    }
}
