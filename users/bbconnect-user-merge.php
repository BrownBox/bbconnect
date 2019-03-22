<?php
/**
 * Merge users
 * @param integer $from_user Source user ID
 * @param integer $to_user Target user ID
 * @param boolean $return Optional. Whether to return true or echo success message. Default false (echo success message).
 * @return boolean|null True if $return is true, else echo success message and return null.
 */
function bbconnect_merge_users_process($from_user, $to_user, $return = false) {
    global $wpdb;

    $old_user = new WP_User($from_user);
    do_action('bbconnect_merge_users', $to_user, $old_user); // Same parameters as profile_update so we can hook the same methods into it

    // GF doesn't hook into delete_user so we need to change entries manually
    // GF < 2.3
    $wpdb->query('UPDATE '.GFFormsModel::get_lead_table_name().' SET created_by = '.$to_user.' WHERE created_by = '.$from_user);
    $wpdb->query('UPDATE '.GFFormsModel::get_lead_meta_table_name().' SET meta_value = '.$to_user.' WHERE meta_key = "agent_id" AND meta_value = '.$from_user);
    // GF >= 2.3
    $wpdb->query('UPDATE '.GFFormsModel::get_entry_table_name().' SET created_by = '.$to_user.' WHERE created_by = '.$from_user);
    $wpdb->query('UPDATE '.GFFormsModel::get_entry_meta_table_name().' SET meta_value = '.$to_user.' WHERE meta_key = "agent_id" AND meta_value = '.$from_user);

    // Update Connexions activity tracking records
    $wpdb->query('UPDATE '.$wpdb->prefix.'bbconnect_activity_log SET user_id = '.$to_user.' WHERE user_id = '.$from_user);
    $wpdb->query('UPDATE '.$wpdb->prefix.'bbconnect_activity_tracking SET user_id = '.$to_user.' WHERE user_id = '.$from_user);

    // Copy across user meta if empty in target record
    $old_meta = get_user_meta($from_user);
    $new_meta = get_user_meta($to_user);
    $update_first_trans = $update_last_trans = false;
    foreach ($old_meta as $meta_key => $meta_value) {
        switch ($meta_key) {
            case 'bbconnect_offset_transaction_amount':
            case 'bbconnect_offset_transaction_count':
                if (!empty($meta_value[0])) {
                    if (!empty($new_meta[$meta_key][0])) {
                        update_user_meta($to_user, $meta_key, $new_meta[$meta_key][0]+$meta_value[0]);
                    } else {
                        update_user_meta($to_user, $meta_key, $meta_value[0]);
                    }
                }
                break;
            case 'bbconnect_offset_first_transaction_amount':
                // Have to check date so just save this for later
                $first_transaction_amount = $meta_value[0];
                break;
            case 'bbconnect_offset_first_transaction_date':
                if (!empty($meta_value[0]) && (empty($new_meta[$meta_key]) || strtotime($meta_value[0]) < strtotime($new_meta[$meta_key][0]))) {
                    update_user_meta($to_user, $meta_key, $meta_value[0]);
                    $update_first_trans = true;
                }
                break;
            case 'bbconnect_offset_last_transaction_amount':
                // Have to check date so just save this for later
                $last_transaction_amount = $meta_value[0];
            case 'bbconnect_offset_last_transaction_date':
                if (!empty($meta_value[0]) && (empty($new_meta[$meta_key]) || strtotime($meta_value[0]) > strtotime($new_meta[$meta_key][0]))) {
                    update_user_meta($to_user, $meta_key, $meta_value[0]);
                    $update_last_trans = true;
                }
                break;
            default:
                if (empty($new_meta[$meta_key]) && !empty($meta_value)) {
                    if (count($meta_value) == 1) {
                        update_user_meta($to_user, $meta_key, $meta_value[0]);
                    } else {
                        foreach ($meta_value as $v) {
                            add_user_meta($to_user, $meta_key, $v);
                        }
                    }
                }
                break;
        }
    }

    if ($update_first_trans) {
        update_user_meta($to_user, 'bbconnect_offset_first_transaction_amount', $first_transaction_amount);
    }
    if ($update_last_trans) {
        update_user_meta($to_user, 'bbconnect_offset_last_transaction_amount', $last_transaction_amount);
    }

    // WP handles posts etc for us :-)
    wp_delete_user($from_user, $to_user);

    // After transaction records have been moved
    bbconnect_bb_cart_recalculate_kpis_for_user($to_user);

    if ($return) {
        return true;
    }

    echo '<div class="updated"><p>Merge Complete</p></div>'."\n";
}
