<?php
/**
 * Gravity Forms integrations
 */

add_action('gform_loaded', 'bbconnect_gf_addon_launch');
function bbconnect_gf_addon_launch() {
    add_filter('gform_pre_render', 'bbconnect_populate_form_with_user_details');
    /**
     * Pre-render form with user data based on key
     * @param array $form
     * @return array $form
     */
    function bbconnect_populate_form_with_user_details($form) {
        if (rgar($form, 'bbconnect_no_prerender') != 'true') {
            if (is_admin() && isset($_GET['user_id'])) { // If we're in WP Admin and have a user ID in querystring let's start there
                $user = get_user_by('id', (int)$_GET['user_id']);
            } elseif (is_user_logged_in()) {
                $user = wp_get_current_user(); // Otherwise if user is logged in we'll use them
            }
            $user = apply_filters('bbconnect_identify_user', $user); // Allow override by other plugins etc
            if ($user instanceof WP_User) {
                // Get user meta for later
                $usermeta = get_user_meta($user->ID);

                // Phone numbers are a special case
                $phone_number = '';
                $phone_data = maybe_unserialize($usermeta['telephone'][0]);
                if (is_array($phone_data)) {
                    foreach ($phone_data as $existing_phone) {
                        if (!empty($existing_phone['value'])) {
                            $phone_number = $existing_phone['value'];
                            break;
                        }
                    }
                }
                foreach ($form['fields'] as &$field) {
                    // Standard fields
                    switch ($field->type) {
                        case 'email':
                            $field->defaultValue = $user->user_email;
                            break;
                        case 'name':
                            foreach ($field->inputs as &$input) {
                                if ($input['id'] == $field->id.'.3') {
                                    $input['defaultValue'] = $user->user_firstname;
                                } elseif ($input['id'] == $field->id.'.6') {
                                    $input['defaultValue'] = $user->user_lastname;
                                }
                            }
                            break;
                        case 'address':
                            foreach ($field->inputs as &$input) {
                                if ($input['id'] == $field->id.'.1') {
                                    $input['defaultValue'] = $usermeta['bbconnect_address_one_1'][0];
                                } elseif ($input['id'] == $field->id.'.2') {
                                    $input['defaultValue'] = $usermeta['bbconnect_address_two_1'][0];
                                } elseif ($input['id'] == $field->id.'.3') {
                                    $input['defaultValue'] = $usermeta['bbconnect_address_city_1'][0];
                                } elseif ($input['id'] == $field->id.'.4') {
                                    $input['defaultValue'] = $usermeta['bbconnect_address_state_1'][0];
                                } elseif ($input['id'] == $field->id.'.5') {
                                    $input['defaultValue'] = $usermeta['bbconnect_address_postal_code_1'][0];
                                } elseif ($input['id'] == $field->id.'.6') {
                                    $countries = bbconnect_helper_country();
                                    $country = $usermeta['bbconnect_address_country_1'][0];
                                    if (array_key_exists($country, $countries)) {
                                        $input['defaultValue'] = $countries[$country];
                                    } else {
                                        $input['defaultValue'] = $country;
                                    }
                                }
                            }
                            break;
                    }

                    // Fields mapped to user meta
                    if (!empty($field->inputs)) {
                        foreach ($field->inputs as &$input) {
                            if (!empty($input['usermeta_key'])) {
                                switch ($input['usermeta_key']) {
                                    case 'telephone':
                                        $input['defaultValue'] = $phone_number;
                                        break;
                                    default:
                                        if (isset($usermeta[$input['usermeta_key']])) {
                                            $input['defaultValue'] = $usermeta[$input['usermeta_key']][0];
                                        }
                                        break;
                                }
                            }
                        }
                    } elseif (!empty($field->usermeta_key)) {
                        switch ($field->usermeta_key) {
                            case 'telephone':
                                $field->defaultValue = $phone_number;
                                break;
                            default:
                                if (isset($usermeta[$field->usermeta_key])) {
                                    $field->defaultValue = $usermeta[$field->usermeta_key][0];
                                }
                                break;
                        }
                    }
                }
            }
        }
        return $form;
    }

    add_filter('gform_pre_render', 'bb_crm_pre_render_states');
    add_filter('gform_admin_pre_render', 'bb_crm_pre_render_states');
    add_filter('gform_pre_submission_filter', 'bb_crm_pre_render_states');
    add_filter('gform_pre_validation', 'bb_crm_pre_render_states');
    /**
     * Populate "State" field with Australian states
     * @param $form array
     * return array Updated form array
     */
    function bb_crm_pre_render_states($form) {
        foreach ($form['fields'] as &$field) {
            if ($field['uniquenameField'] == 'state') {
                $states = bbconnect_get_helper_states();
                $items[] = array('text' => 'Please Select...', 'value' => '');
                foreach ($states['AU'] as $state => $name) {
                    $items[] = array('text' => $name, 'value' => $state);
                }
                $field['choices'] = $items;
            }
        }
        return $form;
    }

    add_action('gform_after_submission', 'bb_crm_create_update_user', 10, 2);
    /**
     * Automatically add/update user(s) on any form submission
     * @param array $entry
     * @param array $form
     */
    function bb_crm_create_update_user($entry, $form) {
        // First look for email addresses so we can locate the user(s)
        $emails = array();
        foreach ($form['fields'] as $field) {
            if ($field->type == 'email' && !empty($entry[$field->id])) {
                $emails[] = $entry[$field->id];
            }
        }

        if (!empty($emails)) {
            $i = 0;
            $usermeta = $phone_numbers = $passwords = array();
            // Go through the fields again to get relevant data
            foreach ($form['fields'] as $field) {
                switch ($field->type) {
                    case 'name':
                        foreach ($field->inputs as $input) {
                            if ($input['id'] == $field->id.'.3') {
                                $usermeta['nickname'][] = $usermeta['first_name'][] = $entry[(string)$input['id']];
                            } elseif ($input['id'] == $field->id.'.6') {
                                $usermeta['last_name'][] = $entry[(string)$input['id']];
                            }
                        }
                        break;
                    case 'address':
                        $state_groups = bbconnect_get_helper_states();
                        $countries = bbconnect_helper_country();
                        foreach ($field->inputs as $input) {
                            if ($input['id'] == $field->id.'.1') {
                                $usermeta['bbconnect_address_one_1'][] = $entry[(string)$input['id']];
                            } elseif ($input['id'] == $field->id.'.2') {
                                $usermeta['bbconnect_address_two_1'][] = $entry[(string)$input['id']];
                            } elseif ($input['id'] == $field->id.'.3') {
                                $usermeta['bbconnect_address_city_1'][] = $entry[(string)$input['id']];
                            } elseif ($input['id'] == $field->id.'.4') {
                                $state = strtoupper($entry[(string)$input['id']]);
                                foreach ($state_groups as $country => $states) {
                                    $states = array_flip(array_map('strtoupper', $states));
                                    if (isset($states[$state])) {
                                        $state = $states[$state];
                                    }
                                }
                                $usermeta['bbconnect_address_state_1'][] = $state;
                            } elseif ($input['id'] == $field->id.'.5') {
                                $usermeta['bbconnect_address_postal_code_1'][] = $entry[(string)$input['id']];
                            } elseif ($input['id'] == $field->id.'.6') {
                                $country = strtoupper($entry[(string)$input['id']]);
                                $countries = array_flip(array_map('strtoupper', $countries));
                                if (isset($countries[$country])) {
                                    $country = $countries[$country];
                                }
                                $usermeta['bbconnect_address_country_1'][] = $country;
                            }
                        }
                        break;
                    case 'phone':
                        $phone_numbers[] = $entry[$field->id];
                        break;
                    case 'password':
                        $passwords[] = $entry[$field->id];
                }
                if (!empty($field->inputs)) {
                    foreach ($field->inputs as $input) {
                        if (!empty($input['usermeta_key'])) {
                            switch ($input['usermeta_key']) {
                                case 'telephone':
                                    $phone_numbers[] = $entry[$input['id']];
                                    break;
                                default:
                                    $usermeta[$input['usermeta_key']][] = $entry[$input['id']];
                                    break;
                            }
                        }
                    }
                } elseif (!empty($field->usermeta_key)) {
                    switch ($field->usermeta_key) {
                        case 'telephone':
                            $phone_numbers[] = $entry[$field['id']];
                            break;
                        default:
                            $usermeta[$field->usermeta_key][] = $entry[$field['id']];
                            break;
                    }
                }
            }

            $email_count = count($emails);
            foreach ($emails as $n => $email) {
                $user = get_user_by('email', $email);
                if ($user instanceof WP_User) { // Update
                    if (is_multisite() && !is_user_member_of_blog($user->ID, $blog_id)) {
                        add_existing_user_to_blog(array('user_id' => $user->ID, 'role' => 'subscriber'));
                    }
                    $password = bbconnect_get_matching_submitted_value($n, $passwords, $email_count);
                    if (!empty($password)) {
                        $auth = wp_authenticate($user->user_login, $password);
                        if (is_wp_error($auth)) {
                            // If there's a password field and they entered the wrong password, don't update anything
                            continue;
                        }
                    }
                    $phone_number = bbconnect_get_matching_submitted_value($n, $phone_numbers, $email_count);
                    if (!empty($phone_number)) {
                        $phone_data = maybe_unserialize(get_user_meta($user->ID, 'telephone', true));
                        $phone_exists = false;
                        if (is_array($phone_data)) {
                            foreach ($phone_data as $existing_phone) {
                                if (isset($existing_phone['value']) && $existing_phone['value'] == $phone_number) {
                                    $phone_exists = true;
                                    break;
                                }
                            }
                        } else {
                            $phone_data = array();
                        }
                        if (!$phone_exists) {
                            $phone_data[] = array(
                                    'value' => $phone_number,
                                    'type' => 'home',
                            );
                            update_user_meta($user->ID, 'telephone', $phone_data);
                        }
                    }
                    foreach ($usermeta as $meta_key => $meta_values) {
                        $meta_value = bbconnect_get_matching_submitted_value($n, $meta_values, $email_count);
                        if (!empty($meta_value)) {
                            update_user_meta($user->ID, $meta_key, $meta_value);
                        }
                    }
                    $user_id = $user->ID;
                } else { // Create
                    do {
                        $username = wp_generate_password(12, false);
                    } while (username_exists($username));

                    $firstname = bbconnect_get_matching_submitted_value($n, $usermeta['first_name'], $email_count);
                    if (empty($firstname)) {
                        $firstname = 'Unknown';
                    }
                    $lastname = bbconnect_get_matching_submitted_value($n, $usermeta['last_name'], $email_count);
                    if (empty($lastname)) {
                        $lastname = 'Unknown';
                    }
                    $userdata = array(
                            'user_login' => $username,
                            'user_nicename' => $firstname.' '.$lastname,
                            'display_name' => $firstname.' '.$lastname,
                            'user_email' => $email,
                            'first_name' => $firstname,
                            'nickname' => $firstname,
                            'last_name' => $lastname,
                            'role' => 'subscriber',
                    );
                    $password = bbconnect_get_matching_submitted_value($n, $passwords, $email_count);
                    if (!empty($password)) {
                        $userdata['user_pass'] = $password;
                    }
                    $user_id = wp_insert_user($userdata);
                    $phone_number = bbconnect_get_matching_submitted_value($n, $phone_numbers, $email_count);
                    if (!empty($phone_number)) {
                        $phone_data = array(
                                array(
                                        'value' => $phone_number,
                                        'type' => 'home',
                                )
                        );
                    }
                    update_user_meta($user->ID, 'telephone', $phone_data);
                    update_user_meta($user->ID, 'bbconnect_source', 'form');

                    foreach ($usermeta as $meta_key => $meta_values) {
                        $meta_value = bbconnect_get_matching_submitted_value($n, $meta_values, $email_count);
                        if (!empty($meta_value)) {
                            update_user_meta($user_id, $meta_key, $meta_value);
                        }
                    }
                }
            }
            if (is_user_logged_in()) {
                $agent_id = get_current_user_id();
            } elseif (!empty($entry['created_by'])) {
                $agent_id = $entry['created_by'];
            } else {
                $agent_id = $user_id;
            }
            $entry['agent_id'] = $agent_id;
            $entry['created_by'] = $user_id;
            GFAPI::update_entry($entry, $entry['id']);
        }
    }

    function bbconnect_get_matching_submitted_value($n, $values, $c) {
        if (!empty($values)) {
            if ($n == 0 || count($values) == $c) {
                return $values[$n];
            }
            if (count($values) == 1) {
                return $values[0];
            }
        }
        return null;
    }

    //Pre render user meta value and set the field visibilty to administrative
    add_filter('gform_pre_render', 'bb_crm_prerender_question_meta', 99); // Make sure it happens after our main pre_render above
    function bb_crm_prerender_question_meta($form) {
        if ($form['id'] == bbconnect_get_question_form()) {
            $fun = 0;
            foreach ($form['fields'] as &$field) {
                if (!empty($field->usermeta_key) && !empty($field->defaultValue)) {
                    $field->visibility = 'hidden';
                } elseif ($field->bbQuestionType == 'fun') {
                    $fun++;
                }
            }
            if ($fun == 0) {
                return null;
            }
        }
        return $form;
    }

    function bbconnect_get_to_know_you() {
        $form_id = bbconnect_get_question_form();
        $form = gravity_form($form_id, false, false, false, null, false, 300, false);
        if ($form) {
            echo '<p>We\'d like to get to know you a little better.</p>';
            echo $form;
        }
    }

    // Pre submission to delete the entry and track the submission
    add_action('gform_after_submission', 'bbconnect_delete_question_form_entry', 20, 2);
    function bbconnect_delete_question_form_entry($entry, $form) {
        if ($form['id'] == bbconnect_get_question_form()) {
            $email = null;
            foreach ($form['fields'] as $field) {
                if ($field->type == 'email') {
                    $email = $entry[$field->id];
                    break;
                }
            }

            if (!empty($email)) {
                $args = array(
                        'email' => $email,
                        'title' => 'Submitted get to know you form',
                );
                bbconnect_track_activity($args);
            }

            GFAPI::delete_entry($entry['id']);
        }
    }

    // Output page for GF completion in admin
    function bbconnect_submit_gravity_form() {
        echo '<div class="wrap">'."\n";
        $form_id = (int)$_GET['form_id'];
        $user_id = (int)$_GET['user_id'];
        if ($form_id && $user_id) {
            $user = get_user_by('id', $user_id);
            $form = GFAPI::get_form($form_id);
            if ($user && $form) {
?>
                <style>
                    body .wrap .gform_wrapper .gform_footer {margin-left: 0; width: 100%;}
                </style>
                <script>
                    jQuery(document).ready(function() {
                        jQuery('input.gform_button').addClass('button-primary');
                    });
                </script>
                <h1><?php echo $user->display_name; ?> (<?php echo $user->user_email; ?>)</h1>
                <p>When you are done just close this tab to return to the user profile.</p>
<?php
                gravity_form($form_id);
            }
        } else {
            echo '<p>Invalid request. Please try again.</p>';
        }
        echo '</div>'."\n";
    }

    // Add support for updating existing entry
    if (!empty($_GET['page']) && $_GET['page'] == 'bbconnect_submit_gravity_form' && is_numeric($_GET['entry_id'])) {
        $entry = GFAPI::get_entry($_GET['entry_id']);
        if ($entry['form_id'] == $_GET['form_id']) {
            add_filter('gform_pre_render_'.$_GET['form_id'], 'bbconnect_populate_form_with_existing_entry');
            function bbconnect_populate_form_with_existing_entry($form) {
                $entry = GFAPI::get_entry($_GET['entry_id']);
                foreach ($form['fields'] as &$field) {
                    if (!empty($field->inputs)) {
                        foreach ($field->inputs as &$input) {
                            $input['defaultValue'] = $entry[$field->id.'.'.$input['id']];
                        }
                    } else {
                        $field['defaultValue'] = $entry[$field->id];
                    }
                }
                ?>
            <script type="text/javascript">
                function DeleteFile(fieldId, deleteButton){
                    if (confirm(<?php _e("'Would you like to delete this file? \'Cancel\' to stop. \'OK\' to delete'", "gravityforms"); ?>)) {
                        jQuery(deleteButton).parent().find('input[type=hidden]').val(fieldId);
                        jQuery(deleteButton).parent().hide('slow');
                        return true;
                    }
                }
            </script>
<?php
                return $form;
            }

            add_filter('gform_field_content', 'bbconnect_field_content', 10, 5);
            function bbconnect_field_content($content, $field, $value, $lead_id, $form_id) {
                if ('fileupload' == $field['type']) {
                    $id 			= $field['id'];
                    $multiple_files	= $field['multipleFiles'];
                    $file_list_id 	= "gform_preview_" . $form_id . "_". $id;
                    $file_urls = $multiple_files ? json_decode($value) : array($value);
                    $preview .= sprintf("<div id='preview_existing_files_%d' data-formid='%d' class='ginput_preview'>", $id, $form_id);
                    if ($file_urls) {
                        foreach ($file_urls as $file_index => $file_url) {
                            // remove url protocol?
                            $file_url = esc_attr($file_url);
                            $preview .= sprintf("<div id='preview_file_%d'><input type='hidden' name='delete_file_%d' /><a href='%s' target='_blank' alt='%s' title='%s'>%s</a><a href='%s' target='_blank' alt='" . __("Download file", "gravityforms") . "' title='" . __("Download file", "gravityforms") . "'><img src='%s' style='margin-left:10px;'/></a><a href='javascript:void(0);' alt='" . __("Delete file", "gravityforms") . "' title='" . __("Delete file", "gravityforms") . "' onclick='DeleteFile(%d, this);' ><img src='%s' style='margin-left:10px;'/></a></div>", $file_index, $id, $file_url, $file_url, $file_url, GFCommon::truncate_url($file_url), $file_url, GFCommon::get_base_url() . "/images/download.png", $id, GFCommon::get_base_url() . "/images/delete.png");
                        }
                        $preview .="</div>";
                        return $content . $preview;
                    }
                }
                return $content;
            }

            add_action("gform_pre_submission_".$_GET['form_id'], "bbconnect_pre_submission_handler");
            function bbconnect_pre_submission_handler($form) {
                $entry = GFAPI::get_entry($_GET['entry_id']);
                foreach ($form['fields'] as $field) {
                    if ($field->type == 'fileupload') {
                        if ($_POST['delete_file_' . $field['id']] == $field['id']) {
                            $_POST['input_'.$field->id] = '';
                        } elseif (empty($_POST['input_'.$field->id])) {
                            $_POST['input_'.$field->id] = $entry[$field->id];
                        }
                    }
                }
            }

            add_filter('gform_save_field_value_'.$_GET['form_id'], 'bbconnect_file_upload_fix', 10, 5);
            function bbconnect_file_upload_fix($value, $lead, $field, $form, $input_id) {
                if ($field->type == 'fileupload') {
                    if (empty($_POST['delete_file_'.$field['id']]) && empty($value)) {
                        $entry = GFAPI::get_entry($_GET['entry_id']);
                        return $entry[$field->id];
                    }
                }
                return $value;
            }

            add_action('gform_entry_id_pre_save_lead_'.$_GET['form_id'], "bbconnect_pre_save_lead", 10, 2);
            function bbconnect_pre_save_lead($entry_id, $form) {
                return $_GET['entry_id'];
            }
        }
    }

    add_filter('gform_form_settings', 'bbconnect_custom_form_setting', 10, 2);
    function bbconnect_custom_form_setting($settings, $form) {
        if (rgar($form, 'bbconnect_no_prerender') == 'true') {
            $checked_text = 'checked="checked"';
        } else {
            $checked_text = '';
        }

        $settings['Form Options']['bbconnect_no_prerender'] = '
        <tr>
            <th><label for="bbconnect_no_prerender">Don\'t auto-populate user details</label></th>
            <td><label><input type="checkbox" value="true" '.$checked_text.' name="bbconnect_no_prerender"> Connexions automatically fills in the current user\'s details when they are logged in. Tick this option to override this functionality.</label></td>
        </tr>';
        return $settings;
    }

    add_filter('gform_pre_form_settings_save', 'bbconnect_save_form_setting');
    function bbconnect_save_form_setting($form) {
        $form['bbconnect_no_prerender'] = rgpost('bbconnect_no_prerender');
        return $form;
    }

    add_action('gform_field_advanced_settings', 'bb_crm_field_settings', 10, 2);
    /**
     * Add setting to fields for custom user meta mapping
     * @param integer $position
     * @param integer $form_id
     */
    function bb_crm_field_settings($position, $form_id) {
        // Position 50 (right after Admin Label)
        if ($position == 50) {
?>
            <li class="bbconnect_setting bbconnect_usermeta_setting field_setting">
                <label class="section_label" for="field_bbconnect_usermeta_key">
                    <?php _e("User Meta", "gravityforms"); ?>
                    <?php gform_tooltip("form_field_bbconnect_usermeta_key") ?>
                </label>
                <div id="field_bbconnect_usermeta_keys_container">
                    <!-- content dynamically created from JS -->
                </div>
                <!-- input type="text" id="field_bbconnect_usermeta_key"-->
            </li>
<?php
        }
    }

    add_action('gform_editor_js', 'bb_crm_gf_editor_script');
    /**
     * Action to inject supporting script to the form editor page
     */
    function bb_crm_gf_editor_script() {
?>
        <script>
            // Add setting to all field types
            for (var t in fieldSettings) {
                fieldSettings[t] += ", .bbconnect_usermeta_setting";
            }

            // Bind to the load field settings event to initialize the value
            jQuery(document).bind("gform_load_field_settings", function(event, field, form){
                var field_str, usermeta_key, inputName, inputId, id, inputs;
                if (!field['inputs']) {
                    field_str = "<label for='field_bbconnect_usermeta_key' class='inline'>" + <?php echo json_encode(esc_html__('User Meta Key', 'gravityforms')); ?> + "&nbsp;</label>";
                    usermeta_key = typeof field["usermeta_key"] != 'undefined' ? field["usermeta_key"] : '';
                    field_str += "<input type='text' value='" + usermeta_key + "' id='field_bbconnect_usermeta_key' onblur='SetFieldProperty(\"usermeta_key\", this.value);'>";
                } else {
                    field_str = "<table class='usermeta_keys'><tr><td><strong>Field</strong></td><td><strong>" + <?php echo json_encode( esc_html__( 'User Meta Key', 'gravityforms' ) ); ?> + "</strong></td></tr>";
                    for (var i = 0; i < field["inputs"].length; i++) {
                        id = field["inputs"][i]["id"];
                        inputName = 'input_' + id.toString();
                        inputId = inputName.replace('.', '_');
                        if (!document.getElementById(inputId) && jQuery('[name="' + inputName + '"]').length == 0) {
                            continue;
                        }
                        field_str += "<tr class='bbconnect_usermeta_key_row' data-input_id='" + id + "' id='input_bbconnect_usermeta_key_" + inputId + "'><td><label for='field_bbconnect_usermeta_key_" + id + "' class='inline'>" + field["inputs"][i]["label"] + "</label></td>";
                        usermeta_key = typeof field["inputs"][i]["usermeta_key"] != 'undefined' ? field["inputs"][i]["usermeta_key"] : '';
                        field_str += "<td><input class='bbconnect_usermeta_key_value' type='text' value='" + usermeta_key + "' id='field_bbconnect_usermeta_key_" + id + "' onblur='SetInputProperty(\"usermeta_key\", this.value, " + id + ");'></td></tr>";
                    }
                }
                jQuery("#field_bbconnect_usermeta_keys_container").html(field_str);
            });

            function SetInputProperty(property, value, inputId){
                var field = GetSelectedField();

                if (value) {
                    value = value.trim();
                }

                if (!inputId) {
                    field[property] = value;
                } else {
                    for(var i=0; i<field["inputs"].length; i++) {
                        if (field["inputs"][i]["id"] == inputId) {
                            field["inputs"][i][property] = value;
                            return;
                        }
                    }
                }
            }
        </script>
<?php
    }

    add_filter('gform_tooltips', 'bb_crm_gf_tooltips');
    /**
     * Filter to add a new tooltip
     * @param array $tooltips
     * @return array
     */
    function bb_crm_gf_tooltips($tooltips) {
       $tooltips['form_field_bbconnect_usermeta_key'] = "<h6>User Meta Key</h6> To save the value of a field into user meta enter the meta key you want to save it to (requires an email field to be present in the form).";
       return $tooltips;
    }
    if (!method_exists('GFForms', 'include_addon_framework')) {
        return;
    }

    GFForms::include_addon_framework();
    class GFBBConnect extends GFAddOn {
        protected $_version = BBCONNECT_VER;
        protected $_min_gravityforms_version = '1.9';
        protected $_slug = 'bbconnect';
        protected $_path = 'bbconnect/utilities/bbconnect-forms.php';
        protected $_full_path = __FILE__;
        protected $_title = 'Gravity Forms Connexions Integrations';
        protected $_short_title = 'Connexions';
        private static $_instance = null;

        public function init() {
            add_filter('gform_entries_field_value', array($this, 'filter_field_values'), 10, 4);
            add_action('gform_entry_info', array($this, 'meta_box_entry_info'), 10, 2);
            parent::init();
        }

        /**
         * Returns an instance of this class, and stores it in the $_instance property.
         *
         * @return object $_instance An instance of this class.
         */
        public static function get_instance() {
            if (self::$_instance == null) {
                self::$_instance = new self();
            }

            return self::$_instance;
        }

        public function get_entry_meta($entry_meta, $form_id) {
            $entry_meta['agent_id'] = array(
                    'label' => 'Submitter',
                    'is_numeric' => false,
                    'is_default_column' => false,
                    'filter' => array(
                            'operators' => array(
                                    'is',
                                    'isnot'
                            ),
                            'choices' => $this->get_agent_list(),
                    ),
            );

            return $entry_meta;
        }

        /**
         * Customise display of our custom meta
         * @param mixed $value
         * @param integer $form_id
         * @param mixed $field_id
         * @param array $entry
         * @return mixed
         */
        public function filter_field_values($value, $form_id, $field_id, $entry) {
            switch ($field_id) {
                case 'agent_id':
                    if (!empty($value)) {
                        $userdata = get_userdata($value);
                        if (!empty($userdata)) {
                            $value = $userdata->user_login;
                        }
                    }
                    break;
            }
            return $value;
        }

        /**
         * Add our custom meta to Entry meta box on entry details
         * @param integer $form_id
         * @param array $entry
         */
        public function meta_box_entry_info($form_id, $entry) {
            if (!empty($entry['agent_id']) && $usermeta = get_userdata($entry['agent_id'])) {
?>
                Submitter:
                <a href="user-edit.php?user_id=<?php echo absint($entry['agent_id']); ?>" alt="<?php esc_attr_e('View user profile', 'gravityforms'); ?>" title="<?php esc_attr_e('View user profile', 'gravityforms'); ?>"><?php echo esc_html($usermeta->user_login); ?></a>
                <br><br>
<?php
            }
        }

        private function get_agent_list() {
            $account_choices = array();
            $args = apply_filters('gform_filters_get_users', array(
                    'number' => 200,
                    'fields' => array('ID', 'user_login'),
            ));
            $accounts = get_users($args);
            $account_choices = array();
            foreach ($accounts as $account) {
                $account_choices[] = array('text' => $account->user_login, 'value' => $account->ID);
            }
            return $account_choices;
        }
    }

    GFAddOn::register('GFBBConnect');
}
