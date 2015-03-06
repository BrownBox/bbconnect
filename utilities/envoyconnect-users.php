<?php

function envoyconnect_init_roles() {
	global $wp_roles;
	$envoyconnect_user_actions = envoyconnect_get_init_user_actions();
	$envoyconnect_post_actions = envoyconnect_get_init_post_actions();
	$envoyconnect_actions = array_merge( $envoyconnect_user_actions, $envoyconnect_post_actions );
	if ( isset( $wp_roles ) ) {
		foreach ( $envoyconnect_actions as $key => $value ) {
			$action = $value['type'];
			$wp_roles->add_cap( 'administrator', 'manage_'.$action );
		}
	}

}


function envoyconnect_role_capabilities( $status, $role ) {
	
	// SET GLOBALS
	global $wp_roles;
	$envoyconnect_user_actions = envoyconnect_get_init_user_actions();
	
	if ( isset( $wp_roles ) ) {
		if ( 'add' == $status ) {
			$wp_roles->add_cap( $role, 'list_users' );
			$wp_roles->add_cap( $role, 'add_users' );
			$wp_roles->add_cap( $role, 'create_users' );
			$wp_roles->add_cap( $role, 'edit_users' );
			foreach ( $envoyconnect_user_actions as $key => $value ) {
				$action = $value['type'];
				$wp_roles->add_cap( $role, 'manage_'.$action );
				
			}
		} else {
			$wp_roles->remove_cap( $role, 'list_users' );
			$wp_roles->remove_cap( $role, 'add_users' );
			$wp_roles->remove_cap( $role, 'create_users' );
			$wp_roles->remove_cap( $role, 'edit_users' );
			foreach ( $envoyconnect_user_actions as $key => $value ) {
				$action = $value['type'];
				$altaction = array();
				$wp_roles->remove_cap( $role, 'manage_'.$action );
			}
		}	
		
	}
}


function envoyconnect_is_panels() {
	
	if ( isset( $_POST['rel'] ) )
		return true;
		
	return false;
	
}

function envoyconnect_user_can( $action, $args = null ) {

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
				return apply_filters( 'envoyconnect_user_can_edit_user', true, $one, $two );	
			}
			
		break;
		
		// EXTEND THE PANEL SYSTEM
		default :
			do_action( 'envoyconnect_user_can_switch', $action, $one, $two );
		break;
			
	}
	
	return false;
	
}


function envoyconnect_email_login() {
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

function envoyconnect_email_authenticate( $user, $username, $password ) {
	$user = get_user_by( 'email', $username );
	if ( $user )
		$username = $user->user_login;
	
	return wp_authenticate_username_password( null, $username, $password );
}

function envoyconnect_address_corrections( $args = '' ) {

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
		$envoyconnect_helper_country = envoyconnect_helper_country();
		if ( strlen( $value ) > 3 ) {
			$value = in_array_r( $value, $envoyconnect_helper_country, true );
		} else {
			$value = substr( $value, 0, 2 );
		}
		 
	} else if ( false !== strpos( $key, 'address_state' ) ) {
		$envoyconnect_helper_state = envoyconnect_helper_state();
		if ( strlen( $value ) > 3 ) {
			$top_value = in_array_r( $value, $envoyconnect_helper_state, true );
			$value = in_array_r( $value, $envoyconnect_helper_state[$top_value], true );
		} else {
			$envoyconnect_helper_country = envoyconnect_helper_country();
			
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
			
			if ( isset( $country ) ) {
				if ( 'Australia' == $country || 'AU' == $country ) {
					$temp_val = substr( $value, 0, 2 );
					if ( 'SA' == $temp_val ) {
						$value = 'SAA';
					} else if ( 'WA' == $temp_val ) {
						$value = 'WAA';
					} else if ( 'NT' == $temp_val ) {
						$value = 'NTA';
					} else {
						$value = $value;
					} 
				} else {
					$value = $value;
				}
			}
			
		}		
			
	}
	 return $value;
	
}


function envoyconnect_state_lookdown( $key, $value ) {
	if ( false !== strpos( $key, 'address_state' ) ) {
		if ( 'SAA' == $value ) {
			$value = 'SA';
		} else if ( 'WAA' == $value ) {
			$value = 'WA';
		} else if ( 'NTA' == $value ) {
			$value = 'NT';
		}
	}
	return $value;
}

function envoyconnect_admin_body_class( $admin_body_class ) {
	if ( false != get_option( '_envoyconnect_admin_body_class' ) ) {
		$class = explode( ' ', $admin_body_class );
		foreach( $class as $k => $v ) {
			if ( false !== strpos( $v, 'admin-color-' ) ) {
				$class[$k] = 'admin-color-' . get_option( '_envoyconnect_admin_body_class' );
			}
		}
		$admin_body_class = implode( ' ', $class );
	}
	return $admin_body_class;
}