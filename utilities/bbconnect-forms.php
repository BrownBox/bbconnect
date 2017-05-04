<?php
/**
 * Gravity Forms integrations
 */

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
 * Automatically add/update user on any form submission
 * @param array $entry
 * @param array $form
 */
function bb_crm_create_update_user($entry, $form) {
    // First look for an email address so we can locate the user
    $user = $email = null;
    foreach ($form['fields'] as $field) {
        if ($field->type == 'email') {
            $email = $entry[$field->id];
            $user = get_user_by('email', $email);
            break;
        }
    }

    if (!empty($email)) {
        $firstname = $lastname = 'Unknown';
        $usermeta = array();
        // Go through the fields again to get relevant data
        foreach ($form['fields'] as $field) {
            switch ($field->type) {
                case 'name':
                    foreach ($field->inputs as $input) {
                        if ($input['id'] == $field->id.'.3') {
                            $firstname = $entry[(string)$input['id']];
                        } elseif ($input['id'] == $field->id.'.6') {
                            $lastname = $entry[(string)$input['id']];
                        }
                    }
                    $usermeta['first_name'] = $firstname;
                    $usermeta['nickname'] = $firstname;
                    $usermeta['last_name'] = $lastname;
                    break;
                case 'address':
                    $countries = bbconnect_helper_country();
                    foreach ($field->inputs as $input) {
                        if ($input['id'] == $field->id.'.1') {
                            $usermeta['bbconnect_address_one_1'] = $entry[(string)$input['id']];
                        } elseif ($input['id'] == $field->id.'.2') {
                            $usermeta['bbconnect_address_two_1'] = $entry[(string)$input['id']];
                        } elseif ($input['id'] == $field->id.'.3') {
                            $usermeta['bbconnect_address_city_1'] = $entry[(string)$input['id']];
                        } elseif ($input['id'] == $field->id.'.4') {
                            $usermeta['bbconnect_address_state_1'] = $entry[(string)$input['id']];
                        } elseif ($input['id'] == $field->id.'.5') {
                            $usermeta['bbconnect_address_postal_code_1'] = $entry[(string)$input['id']];
                        } elseif ($input['id'] == $field->id.'.6') {
                            $country = $entry[(string)$input['id']];
                            if (in_array($country, $countries)) {
                                $countries = array_flip($countries);
                                $usermeta['bbconnect_address_country_1'] = $countries[$country];
                            } else {
                                $usermeta['bbconnect_address_country_1'] = $country;
                            }
                        }
                    }
                    break;
                case 'phone':
                    $phone_number = $entry[$field['id']];
                    break;
            }
            if (!empty($field->inputs)) {
                foreach ($field->inputs as $input) {
                    if (!empty($input->usermeta_key)) {
                        switch ($input->usermeta_key) {
                            case 'telephone':
                                $phone_number = $entry[$input['id']];
                                break;
                            default:
                                $usermeta[$input->usermeta_key] = $entry[$input['id']];
                                break;
                        }
                    }
                }
            } elseif (!empty($field->usermeta_key)) {
                switch ($field->usermeta_key) {
                    case 'telephone':
                        $phone_number = $entry[$field['id']];
                        break;
                    default:
                        $usermeta[$field->usermeta_key] = $entry[$field['id']];
                        break;
                }
            }
        }

        if ($user instanceof WP_User) { // Update
            if (is_multisite() && !is_user_member_of_blog($user->ID, $blog_id)) {
                add_existing_user_to_blog(array('user_id' => $user->ID, 'role' => 'subscriber'));
            }
            if (!empty($phone_number)) {
                $phone_data = maybe_unserialize(get_user_meta($user->ID, 'telephone', true));
                $phone_exists = false;
                foreach ($phone_data as $existing_phone) {
                    if (isset($existing_phone['value']) && $existing_phone['value'] == $phone) {
                        $phone_exists = true;
                        break;
                    }
                }
                if (!$phone_exists) {
                    $phone_data[] = array(
                            'value' => $phone_number,
                            'type' => 'home',
                    );
                    $usermeta['telephone'] = $phone_data;
                }
            }
            foreach ($usermeta as $meta_key => $meta_value) {
                update_user_meta($user->ID, $meta_key, $meta_value);
            }
            GFAPI::update_entry_property($entry['id'], 'created_by', $user->ID);
            $entry['created_by'] = $user->ID;
        } else { // Create
            do {
                $username = wp_generate_password(12, false);
            } while (username_exists($username));
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
            $user_id = wp_insert_user($userdata);
            if (!empty($phone_number)) {
                $usermeta['telephone'] = array(
                        array(
                                'value' => $phone_number,
                                'type' => 'home',
                        )
                );
            }
            $usermeta['bbconnect_source'] = 'form';
            foreach ($usermeta as $meta_key => $meta_value) {
                update_user_meta($user_id, $meta_key, $meta_value);
            }
            GFAPI::update_entry_property($entry['id'], 'created_by', $user_id);
            $entry['created_by'] = $user_id;
        }
    }
}

add_action('gform_after_submission', 'bb_crm_track_form_submission', 20, 2); // Needs to happen after create/update user logic above
/**
 * Add note to user record to track form submission
 * @param array $entry
 * @param array $form
 */
function bb_crm_track_form_submission($entry, $form) {
    foreach ($form['fields'] as $field) {
        // First look for an email address so we can locate the user
        if ($field->type == 'email') {
            $user = get_user_by('email', $entry[$field->id]);

            // We have a matching user - add the note
            $post = array(
                    'post_title'    => 'Form Submission - '.$form['title'],
                    'post_status'   => 'publish',
                    'post_type'     => 'bb_note',
                    'post_content'  => '<a href="/wp-admin/admin.php?page=gf_entries&view=entry&id='.$entry['form_id'].'&lid='.$entry['id'].'">Entry details can be viewed here.</a>',
                    'post_author'   => $user->ID,
            );

            $post_id = wp_insert_post($post);
            wp_set_object_terms($post_id, array('system', 'form-submission'), 'bb_note_type');
            break;
        }
    }

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
