<?php

function bbconnect_init_roles() {
	global $wp_roles;
	$bbconnect_user_actions = bbconnect_get_init_user_actions();
	$bbconnect_post_actions = bbconnect_get_init_post_actions();
	$bbconnect_actions = array_merge( $bbconnect_user_actions, $bbconnect_post_actions );
	if ( isset( $wp_roles ) ) {
		foreach ( $bbconnect_actions as $key => $value ) {
			$action = $value['type'];
			$wp_roles->add_cap( 'administrator', 'manage_'.$action );
		}
    	$wp_roles->add_cap('administrator', 'manage_padlock_fields');
	}
}


function bbconnect_role_capabilities( $status, $role ) {

	// SET GLOBALS
	global $wp_roles;
	$bbconnect_user_actions = bbconnect_get_init_user_actions();

	if ( isset( $wp_roles ) ) {
		if ( 'add' == $status ) {
			$wp_roles->add_cap( $role, 'list_users' );
			$wp_roles->add_cap( $role, 'add_users' );
			$wp_roles->add_cap( $role, 'create_users' );
			$wp_roles->add_cap( $role, 'edit_users' );
			foreach ( $bbconnect_user_actions as $key => $value ) {
				$action = $value['type'];
				$wp_roles->add_cap( $role, 'manage_'.$action );

			}
		} else {
			$wp_roles->remove_cap( $role, 'list_users' );
			$wp_roles->remove_cap( $role, 'add_users' );
			$wp_roles->remove_cap( $role, 'create_users' );
			$wp_roles->remove_cap( $role, 'edit_users' );
			foreach ( $bbconnect_user_actions as $key => $value ) {
				$action = $value['type'];
				$altaction = array();
				$wp_roles->remove_cap( $role, 'manage_'.$action );
			}
		}

	}
}


function bbconnect_is_panels() {

	if ( isset( $_POST['rel'] ) )
		return true;

	return false;

}

function bbconnect_user_can( $action, $args = null ) {

	if ( !$action )
		return false;

	// SET THE DEFAULTS TO BE OVERRIDDEN AS DESIRED
	// USER ONE WILL ALWAYS BE THE TARGET USER YOU WANT TO KNOW ABOUT
	// IT WILL USUALLY BE THE CURRENT USER BUT WE'RE LEAVING IT OPEN BECAUSE WE JUST DON'T KNOW YET...
	// USER TWO WILL ALWAYS BE THE OBJECT USER YOU WANT TO KNOW ABOUT
	$defaults = array(
					'one' => false,
					'two' => false,
				);

	// PARSE THE INCOMING ARGS
	$args = wp_parse_args( $args, $defaults );

	// EXTRACT THE VARIABLES
	extract( $args, EXTR_SKIP );

	switch( $action ) {

		// QUESTION: CAN USER ONE EDIT USER TWO?
		case 'edit_user' :

			if ( false == $one || false == $two )
				return false;

			if ( (int) $one === (int) $two )
				return true;

			if ( user_can( $one, 'list_users' ) ) {

				// FOR NOW, WE'LL LET IT RIDE...
				return apply_filters( 'bbconnect_user_can_edit_user', true, $one, $two );
			}

		break;

		// EXTEND THE PANEL SYSTEM
		default :
			do_action( 'bbconnect_user_can_switch', $action, $one, $two );
		break;

	}

	return false;

}


function bbconnect_email_login() {
?>
	<script type="text/javascript">
	// Form Label
	document.getElementById('loginform').childNodes[1].childNodes[1].childNodes[0].nodeValue = '<?php _e( 'Email', 'email-login' ); ?>';

	// Error Messages
	if ( document.getElementById('login_error') )
		document.getElementById('login_error').innerHTML = document.getElementById('login_error').innerHTML.replace( '<?php _e( 'username', 'email-login' ); ?>', '<?php _e( 'Email', 'email-login' ); ?>' );
	</script>
<?php
}

function bbconnect_email_authenticate( $user, $username, $password ) {
	$user = get_user_by( 'email', $username );
	if ( $user )
		$username = $user->user_login;

	return wp_authenticate_username_password( null, $username, $password );
}

function bbconnect_address_corrections( $args = '' ) {

	// SET THE DEFAULTS TO BE OVERRIDDEN AS DESIRED
	$defaults = array(
					'key' => '',
					'value' => '',
					'k_array' => array(),
					'v_array' => array()
				);

	// PARSE THE INCOMING ARGS
	$args = wp_parse_args( $args, $defaults );

	// EXTRACT THE VARIABLES
	extract( $args, EXTR_SKIP );

	if ( false !== strpos( $key, 'address_country' ) ) {
		$bbconnect_helper_country = bbconnect_helper_country();
		if ( strlen( $value ) > 3 ) {
			$value = in_array_r( $value, $bbconnect_helper_country, true );
		} else {
			$value = substr( $value, 0, 2 );
		}

	} else if ( false !== strpos( $key, 'address_state' ) ) {
		$bbconnect_helper_state = bbconnect_helper_state();
		if ( strlen( $value ) > 3 ) {
			$top_value = in_array_r( $value, $bbconnect_helper_state, true );
			$value = in_array_r( $value, $bbconnect_helper_state[$top_value], true );
		} else {
			$bbconnect_helper_country = bbconnect_helper_country();

			// GET THE SUFFIX FOR COUNTRY MATCHING
			if ( strlen( $key ) > 13 ) {
				$state_suffix = strrchr( $value, '_' );
				$state_count = 0 - strlen( $state_suffix );
			} else {
				$state_suffix = '';
			}

			// GET THE PREFIX FOR COUNTRY MATCHING
			$state_prefix = explode( 'address_state', $key );
			if ( 'address_state' == $state_prefix[0] ) {
				$state_prefix = '';
			} else {
				$state_prefix = $state_prefix[0];
			}

			$country_match = $state_prefix . 'address_country' . $state_suffix;

			if ( !empty( $k_array ) && !empty( $v_array ) ) {
				if ( in_array_r( $country_match, $k_array ) ) {
					$country_key = in_array_r( $country_match, $k_array, true );
					$country = $v_array[$country_key];
				}
			}
		}
	}
	return $value;
}

function bbconnect_state_lookdown( $key, $value ) {
	return $value;
}

function bbconnect_admin_body_class( $admin_body_class ) {
	if ( false != get_option( '_bbconnect_admin_body_class' ) ) {
		$class = explode( ' ', $admin_body_class );
		foreach( $class as $k => $v ) {
			if ( false !== strpos( $v, 'admin-color-' ) ) {
				$class[$k] = 'admin-color-' . get_option( '_bbconnect_admin_body_class' );
			}
		}
		$admin_body_class = implode( ' ', $class );
	}
	return $admin_body_class;
}

/**
 * Get user by email, or create them if they don't exist
 * @param array $args
 * @param string $other
 * @param string $is_new_contact
 * @return boolean|number|unknown
 */
function bbconnect_get_user($args, $other = null, &$is_new_contact = false) {
    extract($args);

    if (!isset($email)) {
        return false;
    }

    if (empty($country) && !empty($other['country'])) {
        $country = $other['country'];
    }

    if (email_exists($email)) { // Existing user
        $user = get_user_by('email', $email);
        $active = get_user_meta($user->ID, 'active', true);
        if ($active == 'false') {
            update_user_meta($user->ID, 'receives_letters', 'true');
            update_user_meta($user->ID, 'receives_newsletters', 'true');
            update_user_meta($user->ID, 'active', 'true');
        }

        do_action('bbconnect_update_user', $user, $args, $other);

        return $user->ID;
    } else { // New user
        $user_name = wp_generate_password(8, false);
        $random_password = wp_generate_password(12, false);

        if (!isset($firstname) || !isset($lastname)) {
            return false;
        }

        $userdata = array(
                'user_login' => $user_name,
                'first_name' => $firstname,
                'last_name' => $lastname,
                'user_pass' => $random_password,
                'user_email' => $email,
                'user_nicename' => $firstname
        );
        $user_id = wp_insert_user($userdata);

        //On fail
        if (is_wp_error($user_id)) {
            return false;
        } else {
            update_user_meta($user_id, 'active', 'true');
            update_user_meta($user_id, 'receives_letters', 'true');
            update_user_meta($user_id, 'receives_newsletters', 'true');

            if (isset($title)) {
                update_user_meta($user_id, 'title',$title);
            }
            if (isset($address1)) {
                update_user_meta($user_id, 'bbconnect_address_one_1', $address1);
            }
            if (isset($address2)) {
                update_user_meta($user_id, 'bbconnect_address_two_1', $address2);
            }
            if (isset($suburb)) {
                update_user_meta($user_id, 'bbconnect_address_city_1', $suburb);
            }
            if (isset($state)) {
                update_user_meta($user_id, 'bbconnect_address_state_1', $state);
            }
            if (isset($postcode)) {
                update_user_meta($user_id, 'bbconnect_address_postal_code_1', $postcode);
            }
            if (!empty($country)) {
                $country = bbconnect_process_country($country);
                update_user_meta($user_id, 'bbconnect_address_country_1', $country);
            }

            update_user_meta($user_id, 'bbconnect_bbc_primary', 'address_1');

            if (!empty($phone)) {
                $phone_data = array(
                        array(
                                'value' => $phone,
                                'type' => 'home',
                        ),
                );
                update_user_meta($user_id, 'telephone', $phone_data);
            }

            do_action('bbconnect_create_user', $user_id);

            $is_new_contact = true;

            return $user_id;
        }
    }

    return false;
}

function bbconnect_process_country($country) {
    $bbconnect_helper_country = bbconnect_helper_country();
    if (strlen($country) > 3) {
        $country = in_array_r($country, $bbconnect_helper_country, true);
    } else {
        $country = substr($country, 0, 2);
    }
    return $country;
}

function bbconnect_address_compare($val1, $val2) {
    return preg_replace('/[^a-zA-Z0-9]/', '', strtolower($val1)) == preg_replace('/[^a-zA-Z0-9]/', '', strtolower($val2));
}