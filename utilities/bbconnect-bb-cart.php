<?php
add_action('bb_cart_post_purchase', 'bbconnect_bb_cart_post_purchase', 10, 4);
function bbconnect_bb_cart_post_purchase($cart_items, $entry, $form, $transaction) {
    $user = null;
    if (is_user_logged_in()) {
        $user = wp_get_current_user();
    } else {
        // Look for an email address so we can locate the user
        foreach ($form['fields'] as $field) {
            if ($field->type == 'email') {
                $email = $entry[$field->id];
                $user = get_user_by('email', $email);
                break;
            }
        }
    }

    if ($user instanceof WP_User) {
        $total = bb_cart_total_price();
        $previous_total = (float)get_user_meta($user->ID, 'bbconnect_kpi_transaction_amount', true);
        $previous_count = (int)get_user_meta($user->ID, 'bbconnect_kpi_transaction_count', true);

        update_user_meta($user->ID, 'bbconnect_kpi_transaction_amount', $total+$previous_total);
        update_user_meta($user->ID, 'bbconnect_kpi_transaction_count', $previous_count+1);
        update_user_meta($user->ID, 'bbconnect_kpi_last_transaction_date', bbconnect_get_current_datetime()->format('Y-m-d'));
        update_user_meta($user->ID, 'bbconnect_kpi_days_since_last_transaction', 0);
    }
}
