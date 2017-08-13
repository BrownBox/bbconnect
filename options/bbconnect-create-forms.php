<?php
function bbconnect_get_send_email_form() {
    $send_email_form_id = get_option('bbconnect_send_email_form_id');
    $send_email_form = array(
            'title' => 'Send Message',
            'description' => 'Connexions form for sending emails and SMS messages',
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
                            'class' => 'readonly',
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
                            'class' => 'readonly',
                    ),
                    2 => array(
                            'type' => 'text ',
                            'id' => 10,
                            'label' => 'To:Number',
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
                    4 => array(
                            'type' => 'hidden',
                            'id' => 6,
                            'label' => 'Source',
                            'isRequired' => false,
                            'defaultValue' => 'bbconnect',
                            'class' => 'readonly',
                    ),
                    5 => array(
                            'type' => 'hidden',
                            'id' => 7,
                            'label' => 'Activity Type',
                            'isRequired' => false,
                            'defaultValue' => 'email',
                            'class' => 'readonly',
                            'uniqueName' => 'bb_activity_type',
                    ),
                    6 => array(
                            'type' => 'hidden',
                            'id' => 8,
                            'label' => 'Internal Reference',
                            'isRequired' => false,
                            'defaultValue' => '',
                            'class' => 'readonly',
                            'uniqueName' => 'bb_internal_reference',
                    ),
                    7 => array(
                            'type' => 'hidden',
                            'id' => 9,
                            'label' => 'External Reference',
                            'isRequired' => false,
                            'defaultValue' => '',
                            'class' => 'readonly',
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

add_action('init', 'bbconnect_form_locking', 999); // Run as late as possible to make sure GF has inited first
function bbconnect_form_locking() {
    if (class_exists('GFFormLocking')) {
        class BBConnectGFFormLocking extends GFFormLocking {
            private $bbconnect_forms = array();

            public function __construct() {
                $this->bbconnect_forms[] = bbconnect_get_send_email_form();

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
                $this->bbconnect_forms[] = bbconnect_get_send_email_form();

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
