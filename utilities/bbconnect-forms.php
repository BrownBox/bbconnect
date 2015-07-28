<?php
add_filter('gform_pre_render', 'bb_crm_pre_render_states');
add_filter('gform_admin_pre_render', 'bb_crm_pre_render_states');
add_filter('gform_pre_submission_filter', 'bb_crm_pre_render_states');
add_filter('gform_pre_validation', 'bb_crm_pre_render_states');
function bb_crm_pre_render_states($form) {
    foreach ($form['fields'] as &$field) {
        if ($field['uniquenameField'] == 'state') {
            $states = envoyconnect_get_helper_states();
            $items[] = array('text' => 'Please Select...', 'value' => '');
            foreach ($states['AU'] as $state => $name) {
                $items[] = array('text' => $name, 'value' => $state);
            }
            $field['choices'] = $items;
        }
    }
    return $form;
}