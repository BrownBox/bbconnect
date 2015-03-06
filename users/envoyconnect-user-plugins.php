<?php

// LISTENS FOR COMPATIBLE EVENTS  
function envoyconnect_listener() {
	
	// FIND OUT WHAT PAGE THEY'RE REQUESTING
	global $pagenow;
	
	// IF THEY'RE NOT ON THE MANAGE OPTIONS PAGE, EXIT
	if ( !is_admin() )
		return false;
		
	if ( !isset( $_GET['page'] ) )
		return false;
	
	if ( 'envoyconnect_meta_options' != $_GET['page'] )
		return false;

	// FOR NOW, WE'RE JUST GOING TO LISTEN FOR USER AVATAR
	if ( is_plugin_active( 'user-avatar/user-avatar.php' ) && false == get_option( 'envoyconnect_user_avatar' ) ) {
			
			// ADD AN OPTION
			$args = array( 
							'nonce' => false, 
							'output' => 'return', 
							'source' => 'envoyconnect', 
							'meta_key' => 'user_avatar', 
							'tag' => '', 
							'name' => 'User Avatar', 
							'column' => 'column_2', 
							'section' => '', 
							'admin' => true, 
							'user' => true, 
							'reports' => false, 
							'public' => false, 
							'req' => false, 
							'field_type' => 'plugin', 
							'choices' => 'envoyconnect_user_avatar_form', 
							'help' => '', 
							'status' => ''
						);
			$ua_insert = envoyconnect_new_elements( $args );
			add_option( 'envoyconnect_user_avatar', $ua_insert );
			
			// ADD THIS TO THE META ARRAY
			$envoyconnect_user_meta = get_option( '_envoyconnect_user_meta' );
			
			if ( !isset( $envoyconnect_user_meta['column_2'] ) )
				$envoyconnect_user_meta['column_2'] = array();
			
			array_push( $envoyconnect_user_meta['column_2'], 'envoyconnect_user_avatar' );
			update_option( '_envoyconnect_user_meta', $envoyconnect_user_meta );
		
	}
	
	// FOR NOW, WE'RE JUST GOING TO LISTEN FOR USER AVATAR
	if ( is_plugin_active( 'add-local-avatar/avatars.php' ) && false == get_option( 'envoyconnect_add_local_avatar' ) ) {
			
			// ADD AN OPTION
			$args = array( 
							'nonce' => false, 
							'output' => 'return', 
							'source' => 'envoyconnect', 
							'meta_key' => 'add_local_avatar', 
							'tag' => '', 
							'name' => 'Add Local Avatar', 
							'column' => 'column_2', 
							'section' => '', 
							'admin' => true, 
							'user' => true, 
							'reports' => false, 
							'public' => false, 
							'req' => false, 
							'field_type' => 'plugin', 
							'choices' => 'envoyconnect_add_local_avatar_form', 
							'help' => '', 
							'status' => ''
						);
			$ua_insert = envoyconnect_new_elements( $args );
			add_option( 'envoyconnect_add_local_avatar', $ua_insert );
			
			// ADD THIS TO THE META ARRAY
			$envoyconnect_user_meta = get_option( '_envoyconnect_user_meta' );
			
			if ( !isset( $envoyconnect_user_meta['column_2'] ) )
				$envoyconnect_user_meta['column_2'] = array();
			
			array_push( $envoyconnect_user_meta['column_2'], 'envoyconnect_add_local_avatar' );
			update_option( '_envoyconnect_user_meta', $envoyconnect_user_meta );
		
	}
	
	// ALLOW OTHER PLUGINS TO CREATE THEIR OWN USER PLUGINS
	do_action( 'envoyconnect_init_user_plugins' );

}
   


function envoyconnect_user_avatar_form( $args = null ) {

	// SET THE DEFAULTS TO BE OVERRIDDEN AS DESIRED
	$defaults = array( 
					'fdata' => false, 
					'fvalue' => false, 
					'faction' => false, 
					'ftype' => false
				);
	
	// PARSE THE INCOMING ARGS
	$args = wp_parse_args( $args, $defaults );

	// EXTRACT THE VARIABLES
	extract( $args, EXTR_SKIP );

	if ( is_user_logged_in() ) {
	
		if ( '-edit' == $faction ) {
			if ( is_plugin_active( 'user-avatar/user-avatar.php' ) && false != $fdata )
				user_avatar_form( $fdata );
		} else if ( '-view' == $faction ) {
			echo '<div class="user-avatar-wrap"><div id="user-avatar-display-image">' . get_avatar( $fdata->ID, 150 ) . '</div></div>';
		} else {
		}
		
	} else if ( false != $fdata ) {
		echo '<div class="user-avatar-wrap"><div id="user-avatar-display-image">' . get_avatar( $fdata->ID, 150 ) . '</div></div>';
	}

}


function envoyconnect_add_local_avatar_form( $args = null ) {

	// SET THE DEFAULTS TO BE OVERRIDDEN AS DESIRED
	$defaults = array( 
					'fdata' => false, 
					'fvalue' => false, 
					'faction' => false, 
					'ftype' => false
				);
	
	// PARSE THE INCOMING ARGS
	$args = wp_parse_args( $args, $defaults );

	// EXTRACT THE VARIABLES
	extract( $args, EXTR_SKIP );

	if ( is_user_logged_in() ) {
	
		if ( '-edit' == $faction ) {
			if ( is_plugin_active( 'add-local-avatar/avatars.php' ) && false != $fdata ) {
				$ala = new add_local_avatars;
				$ala->avatar_uploader_option( $fdata );
			}
		} else if ( '-view' == $faction ) {
			echo '<div class="user-avatar-wrap"><div id="user-avatar-display-image">' . get_avatar( $fdata->ID, 150 ) . '</div></div>';
		} else {
		}
		
	} else if ( false != $fdata ) {
		echo '<div class="user-avatar-wrap"><div id="user-avatar-display-image">' . get_avatar( $fdata->ID, 150 ) . '</div></div>';
	}

}