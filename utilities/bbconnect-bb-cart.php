<?php
add_action('bb_cart_post_purchase', 'bbconnect_bb_cart_post_purchase', 10, 4);
function bbconnect_bb_cart_post_purchase($cart_items, $entry, $form, $transaction_id) {
    $user = null;
    $transaction = get_post($transaction_id);
    if ($transaction instanceof WP_Post && !empty($transaction->post_author)) {
        $user = new WP_User($transaction->post_author);
    } elseif (is_user_logged_in()) {
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
        $total = get_post_meta($transaction_id, 'total_amount', true);
        $previous_total = (float)get_user_meta($user->ID, 'bbconnect_kpi_transaction_amount', true);
        $previous_count = (int)get_user_meta($user->ID, 'bbconnect_kpi_transaction_count', true);

        update_user_meta($user->ID, 'bbconnect_kpi_transaction_amount', $total+$previous_total);
        update_user_meta($user->ID, 'bbconnect_kpi_transaction_count', $previous_count+1);
        update_user_meta($user->ID, 'bbconnect_kpi_last_transaction_amount', $total);
        update_user_meta($user->ID, 'bbconnect_kpi_last_transaction_date', bbconnect_get_current_datetime()->format('Y-m-d'));
        update_user_meta($user->ID, 'bbconnect_kpi_days_since_last_transaction', 0);

        // Add note to user record
        $description = 'Transaction for $'.$total.' processed successfully. <a href="/wp-admin/admin.php?page=gf_entries&view=entry&id='.$entry['form_id'].'&lid='.$entry['id'].'">Entry details can be viewed here.</a>';
        bbconnect_insert_user_note($user->ID, 'Transaction - '.$form['title'], $description, array('type' => 'system', 'subtype' => 'transaction'), $transaction_id);
    }
}

add_action('bb_cart_webhook_paydock_recurring_success', 'bbconnect_bb_cart_webhook_paydock_recurring_success', 10, 3);
function bbconnect_bb_cart_webhook_paydock_recurring_success($user, $amount, $transaction_id = null) {
    if ($user instanceof WP_User) {
        $previous_total = (float)get_user_meta($user->ID, 'bbconnect_kpi_transaction_amount', true);
        $previous_count = (int)get_user_meta($user->ID, 'bbconnect_kpi_transaction_count', true);

        update_user_meta($user->ID, 'bbconnect_kpi_transaction_amount', $amount+$previous_total);
        update_user_meta($user->ID, 'bbconnect_kpi_transaction_count', $previous_count+1);
        update_user_meta($user->ID, 'bbconnect_kpi_last_transaction_amount', $amount);
        update_user_meta($user->ID, 'bbconnect_kpi_last_transaction_date', bbconnect_get_current_datetime()->format('Y-m-d'));
        update_user_meta($user->ID, 'bbconnect_kpi_days_since_last_transaction', 0);

        // Add note to user record
        $description = 'Transaction for $'.$amount.' processed successfully via PayDock.';
        bbconnect_insert_user_note($user->ID, 'Automated Recurring Transaction Success', $description, array('type' => 'system', 'subtype' => 'transaction'), $transaction_id);
    }
}

add_action('bb_cart_post_import', 'bbconnect_bb_cart_post_import', 10, 3);
function bbconnect_bb_cart_post_import($user, $amount, $transaction_id = null) {
    if ($user instanceof WP_User) {
        $previous_total = (float)get_user_meta($user->ID, 'bbconnect_kpi_transaction_amount', true);
        $previous_count = (int)get_user_meta($user->ID, 'bbconnect_kpi_transaction_count', true);

        update_user_meta($user->ID, 'bbconnect_kpi_transaction_amount', $amount+$previous_total);
        update_user_meta($user->ID, 'bbconnect_kpi_transaction_count', $previous_count+1);
        update_user_meta($user->ID, 'bbconnect_kpi_last_transaction_amount', $amount);
        update_user_meta($user->ID, 'bbconnect_kpi_last_transaction_date', bbconnect_get_current_datetime()->format('Y-m-d'));
        update_user_meta($user->ID, 'bbconnect_kpi_days_since_last_transaction', 0);

        // Add note to user record
        $description = 'Transaction for $'.$amount.' imported successfully.';
        bbconnect_insert_user_note($user->ID, 'Imported Transaction', $description, array('type' => 'system', 'subtype' => 'transaction'), $transaction_id);
    }
}

function bbconnect_bb_cart_recalculate_kpis() {
    ini_set('memory_limit', '2048M');
    $offset = 0;
    $limit = 100;
    $get_total = true;
    $processed_users = array();
    global $blog_id;

    // First process any users with an offset amount - i.e. historical transactions that don't exist in the system as discrete records
    do {
        $args = array(
                'fields' => array('ID'),
                'blog_id' => $blog_id,
                'number' => $limit,
                'offset' => $offset,
                'count_total' => $get_total,
                'meta_query' => array(
                        array(
                                'key' => 'bbconnect_offset_transaction_count',
                                'value' => '',
                                'compare' => '!=',
                        ),
                ),
        );
        $query = new WP_User_Query($args);
        $users = $query->get_results();
        if ($get_total) {
            $total_users = $query->get_total();
        }

        foreach ($users as $user) {
            bbconnect_bb_cart_recalculate_kpis_for_user($user->ID);
            $processed_users[] = $user->ID;
        }
        $get_total = false;
        $offset += $limit;
        unset($query, $users, $user);
    } while ($offset <= $total_users);

    // Now anyone with discrete transaction records (if not already processed above)
    global $wpdb;
    $users = $wpdb->get_col('SELECT DISTINCT(post_author) FROM '.$wpdb->posts.' WHERE post_type = "transaction" AND NOT post_author IN ('.implode(',', $processed_users).')');
    unset($processed_users);
    foreach ($users as $user_id) {
        bbconnect_bb_cart_recalculate_kpis_for_user($user_id);
    }
}

function bbconnect_bb_cart_recalculate_kpis_for_user($user_id) {
    set_time_limit(600);
    $now = bbconnect_get_current_datetime();
    $now->setTime(0, 0, 0);
    $meta = array(
            'bbconnect_kpi_transaction_amount' => 0,
            'bbconnect_kpi_transaction_count' => 0,
            'bbconnect_kpi_first_transaction_amount' => null,
            'bbconnect_kpi_first_transaction_date' => null,
            'bbconnect_kpi_last_transaction_amount' => null,
            'bbconnect_kpi_last_transaction_date' => null,
            'bbconnect_kpi_days_since_last_transaction' => null,
    );

    $offsets = array(
            'transaction_amount',
            'transaction_count',
            'first_transaction_amount',
            'first_transaction_date',
            'last_transaction_amount',
            'last_transaction_date',
    );
    $user_meta = get_user_meta($user_id);
    foreach ($offsets as $offset) {
        if (!empty($user_meta['bbconnect_offset_'.$offset][0])) {
            $meta['bbconnect_kpi_'.$offset] = $user_meta['bbconnect_offset_'.$offset][0];
        }
    }

    $offset = 0;
    $limit = 20;
    $get_total = true;
    do {
        set_time_limit(600);
        $args = array(
                'post_type'      => 'transaction',
                'status'         => 'publish',
                'author'         => $user_id,
                'orderby'        => 'date',
                'order'          => 'DESC',
                'posts_per_page' => $limit,
                'offset'         => $offset,
        );
        $query = new WP_Query($args);
        $transactions = $query->get_posts();
        if ($get_total) {
            $total_transactions = $query->found_posts;
            $meta['bbconnect_kpi_transaction_count'] += $total_transactions;
            $get_total = false;
        }

        foreach ($transactions as $transaction) {
            $amount = get_post_meta($transaction->ID, 'total_amount', true);
            if ($amount > 0) {
                $transaction_date = bbconnect_get_datetime($transaction->post_date);
                $transaction_date->setTime(0, 0, 0);
                if (is_null($meta['bbconnect_kpi_first_transaction_date']) || strtotime($meta['bbconnect_kpi_first_transaction_date']) > $transaction_date->getTimestamp()) {
                    $meta['bbconnect_kpi_first_transaction_date'] = $transaction_date->format('Y-m-d');
                    $meta['bbconnect_kpi_first_transaction_amount'] = $amount;
                }
                if (is_null($meta['bbconnect_kpi_last_transaction_date']) || strtotime($meta['bbconnect_kpi_last_transaction_date']) < $transaction_date->getTimestamp()) {
                    $meta['bbconnect_kpi_last_transaction_date'] = $transaction_date->format('Y-m-d');
                    $meta['bbconnect_kpi_last_transaction_amount'] = $amount;
                }
                $meta['bbconnect_kpi_transaction_amount'] += $amount;
            }
        }
        $offset += $limit;
        unset($query, $transactions, $transaction, $transaction_date);
    } while ($offset <= $total_transactions);

    if (!empty($meta['bbconnect_kpi_last_transaction_date'])) {
        $last_transaction_date = new DateTime($meta['bbconnect_kpi_last_transaction_date']);
        $days_since_last_transaction = $last_transaction_date->diff($now, true);
        $meta['bbconnect_kpi_days_since_last_transaction'] = $days_since_last_transaction->days;
    }

    foreach ($meta as $key => $value) {
        update_user_meta($user_id, $key, $value);
    }
}
