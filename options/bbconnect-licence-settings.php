<?php
function bbconnect_licence_settings() {
    return apply_filters('bbconnect_licence_settings', array(
            array(
                    'meta' => array(
                            'source' => 'bbconnect',
                            'meta_key' => '_bbconnect_licence',
                            'name' => __('Licence Key', 'bbconnect'),
                            'help' => '',
                            'options' => array(
                                        'field_type' => 'text',
                                        'req' => true,
                                        'public' => false,
                                        'class' => 'input-long',
                                        'choices' => false,
                            ),
                    ),
            ),
            array(
                    'meta' => array(
                            'source' => 'bbconnect',
                            'meta_key' => '_bbconnect_licence_summary',
                            'name' => '',
                            'help' => '',
                            'options' => array(
                                    'field_type' => 'plugin',
                                    'req' => false,
                                    'public' => false,
                                    'class' => false,
                                    'choices' => 'bbconnect_licence_summary',
                            ),
                    ),
            ),
    ));
}

add_action('update_option__bbconnect_licence', 'bbconnect_licence_settings_updated', 10, 2);
function bbconnect_licence_settings_updated($old_value, $new_value) {
    delete_transient('connexions_licence_details');
}

/**
 * Get details of Connexions licence including all available add-ons
 * @return array Details of licence status:
 *      licence_status,
 *      expiry,
 *      addons => array(
 *              addon-name => array(
 *                      name,
 *                      licence_status,
 *                      icon,
 *              )
 *              ...
 *      )
 */
function bbconnect_licence_details() {
    $transient_name = 'connexions_licence_details';
    if (false === ($licence_details = get_transient($transient_name))) {
        $licence_details = array(
                'licence_status' => 'empty',
                'expiry' => null,
                'addons' => array(),
        );
        $licence = get_option('_bbconnect_licence');
        if (!empty($licence)) {
            $licence_details['licence_status'] = 'unknown';
            $url = 'https://connexionscrm.com/wp-json/bb-connexions/v1/check-licence?licence='.$licence.'&amp;host='.$_SERVER['HTTP_HOST'];
            $response = wp_remote_get($url);
            if (!is_wp_error($response)) {
                $response_code = wp_remote_retrieve_response_code($response);
                if ($response_code == 200) { // Success!
                    $licence_details = json_decode(wp_remote_retrieve_body($response), true);
                    set_transient($transient_name, $licence_details, 12*HOUR_IN_SECONDS);
                } elseif ($response_code == 404) { // Licence not found
                    $licence_details['licence_status'] = 'invalid';
                }
            }
        }
    }
    return $licence_details;
}

/**
 * Is the licence valid for the specified plugin?
 * @param string $plugin Optional. If empty assumes core Connexions
 * @return boolean
 */
function bbconnect_licence_valid($plugin = BBCONNECT_SLUG) {
	return true; // Treat all licences as valid since updates API is no longer available
    $details = bbconnect_licence_details();
    if ($plugin == BBCONNECT_SLUG) {
        return $details['licence_status'] == 'active';
    } else {
        return array_key_exists($plugin, $details['addons']) && $details['addons'][$plugin]['licence_status'] == 'active';
    }
}

/**
 * Get the status of the licence for the specified plugin
 * @param string $plugin Optional. If empty assumes core Connexions
 * @return string Licence status (active, expired, invalid)
 */
function bbconnect_licence_status($plugin = BBCONNECT_SLUG) {
	return 'active'; // Treat all licences as valid since updates API is no longer available
    $details = bbconnect_licence_details();
    if ($plugin == BBCONNECT_SLUG) {
        return $details['licence_status'];
    } else {
        if (array_key_exists($plugin, $details['addons'])) {
            return $details['addons'][$plugin]['licence_status'];
        }
    }
    return empty(get_option('_bbconnect_licence')) ? 'empty' : 'unknown';
}

/**
 * Output summary table of licence status
 */
function bbconnect_licence_summary() {
    if (empty(get_option('_bbconnect_licence'))) {
        echo '<p>Please enter your licence key and click Save.</p>'."\n";
    } else {
        echo '<h3 class="bbconnect-section">Licence Details</h3>'."\n";
        $details = bbconnect_licence_details();
        switch (bbconnect_licence_status()) {
            case 'active':
                echo '<p class="bbconnect_success">Licence successfully validated.</p>'."\n";
                echo '<h4>Addons</h4>';
                foreach ($details['addons'] as $slug => $addon) {
                    if (is_plugin_active($slug)) {
                        if (bbconnect_licence_status($slug) == 'active') {
                            $status = 'active';
                        } else {
                            $status = 'unlicensed';
                        }
                    } elseif (file_exists(trailingslashit(WP_PLUGIN_DIR).$slug)) {
                        $status = 'inactive';
                    } else {
                        $status = 'not installed';
                    }
                    $class = str_replace(' ', '-', $status);
                    echo '<div class="addon_tile">'."\n";
                    echo '    <div class="addon_tile_inner">'."\n";
                    echo '        <p class="ribbon '.$class.'"><span>'.$status.'</span></p>'."\n";
                    echo '        <div class="icon" style="background-image: url('.$addon['icon'].')"></div>'."\n";
                    echo '        <p><strong>'.$addon['name'].'</strong></p>'."\n";
                    echo '        <p>';
                    if ($status == 'inactive') {
                        $activate_url = sprintf(admin_url('plugins.php?action=activate&plugin=%s&plugin_status=all&paged=1&s'), str_replace('/', '%2F', $slug));
                        // Change the plugin request to the plugin to pass the nonce check
                        $_REQUEST['plugin'] = $slug;
                        $activate_url = wp_nonce_url($activate_url, 'activate-plugin_'.$slug);
                        echo '        <a class="button-primary" href="'.$activate_url.'">Activate</a>';
                    }
                    if (!empty($addon['url'])) {
                        echo '        <a class="button-primary" href="'.$addon['url'].'" target="_blank">Details</a>';
                    }
                    echo '        </p>'."\n";
                    echo '    </div>'."\n";
                    echo '</div>'."\n";
                }
                echo '<div class="addon_tile">'."\n";
                echo '    <div class="addon_tile_inner">'."\n";
                echo '        <div class="icon" style="background-image: url(https://connexionscrm.com/wp-content/uploads/sites/7/2017/10/phone.png)"></div>'."\n";
                echo '        <p><strong>Got an addon you\'re dreaming of?</strong></p>'."\n";
                echo '        <p><a class="button-primary" href="https://connexionscrm.com/contact/" target="_blank">Get in touch</a></p>'."\n";
                echo '    </div>'."\n";
                echo '</div>'."\n";
                break;
            case 'invalid':
                echo '<p class="bbconnect_error">Your licence is invalid. Please check the value above against the licence key provided to you. If you still experience problems, please <a href="https://brownbox.net.au/contact/" target="_blank">contact us</a>.</p>'."\n";
                break;
            case 'expired':
                echo '<p class="bbconnect_error">Your licence expired on '.$details['expiry'].'. Please <a href="https://brownbox.net.au/contact/" target="_blank">contact us</a> to renew or discuss your options.</p>'."\n";
                break;
            case 'unknown':
            default:
                echo '<p class="bbconnect_notice">We were unable to confirm your licence status. Please check back later, and <a href="https://brownbox.net.au/contact/" target="_blank">contact us</a> if this message persists for more than 48 hours.</p>'."\n";
        }
    }
}
