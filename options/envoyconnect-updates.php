<?php 

/**
 * Define the Versions
 *
 * @since 1.0.0
 */
function envoyconnect_versions() {
	
	$envoyconnect_versions = array(
		'0.9.2' => 'envoyconnect_update_v_0_9_2', 
		'0.9.4' => 'envoyconnect_update_v_0_9_4', 
		'1.0.0' => 'envoyconnect_update_v_1_0_0', 
		);
		
	return $envoyconnect_versions;

}

function envoyconnect_update_v_0_9_2() {

	$um = array( '1', '2', '3', '4' );
	foreach ( $um as $m ) {
		
		$meta = envoyconnect_get_option( 'address_' . $m );
		if ( false != $meta ) {
			$address_keys = array();

			foreach ( $meta['options']['choices'] as $k => $v ) {
				if ( false !== strpos( $v, 'address_type' ) ) {
					$address_keys[] = $v;
					
					$loc = '_'.$m;
					$loc_tag = strtoupper( substr( $m, 0, 1 ) );
					
					$address = array();
					// ADDRESS RECIPIENT
					$address[] = array( 'source' => 'envoyconnect', 'meta_key' => 'address_recipient'.$loc, 'tag' => 'ADREP'.$loc_tag, 'name' => __( 'Address Recipient', 'envoyconnect' ), 'options' => array( 'admin' => true, 'user' => true, 'signup' => false, 'reports' => true, 'public' => false, 'req' => false, 'field_type' => 'text', 'choices' => array() ), 'group' => 'address'.$loc, 'help' => __( 'The person receiving deliveries at this address.', 'envoyconnect' ) );
					// ADDRESS ORGANIZATION
					$address[] = array( 'source' => 'envoyconnect', 'meta_key' => 'address_organization'.$loc, 'tag' => 'ADORG'.$loc_tag, 'name' => __( 'Address Organization', 'envoyconnect' ), 'options' => array( 'admin' => true, 'user' => true, 'signup' => false, 'reports' => true, 'public' => false, 'req' => false, 'field_type' => 'text', 'choices' => array() ), 'group' => 'address'.$loc, 'help' => __( 'The company or organization at this address.', 'envoyconnect' ) );
					
					foreach ( $address as $key => $value ) {		
					
						if ( false != get_option( 'envoyconnect_'.$value['meta_key'] ) )
							continue;
						
						// SET A NAMED VALUE FOR THE PAUPRESS_USER_META ARRAY AND 
						$address_keys[] = 'envoyconnect_'.$value['meta_key'];
						// ADD THE OPTION
						add_option( 'envoyconnect_'.$value['meta_key'], $value );		
						
					}
					
				} else {
					$address_keys[] = $v;
				}
			}
			$meta['options']['choices'] = $address_keys;
			update_option( envoyconnect_get_option( 'address_' . $m, true ), $meta );
			
		}
			
	}

	return 'success!';
}


function envoyconnect_update_v_0_9_4() {
	
	// UPDATE RESERVED OPTIONS TO UNDERSCORE PREFIXES
	$optcon = array( 
					'envoyconnect_post_types' => '_envoyconnect_post_types', 
					'envoyconnect_error_log' => '_envoyconnect_error_log', 
					'envoyconnect_capabilities' => '_envoyconnect_capabilities', 
					'envoyconnect_user_access_option' => '_envoyconnect_access', 
					'envoyconnect_user_public_option' => '_envoyconnect_public', 
					'envoyconnect_taxonomy_display' => '_envoyconnect_taxonomy_display', 
					'envoyconnect_basic_user_display' => '_envoyconnect_user_display', 
					'envoyconnect_compatability_mode' => '_envoyconnect_compatability_mode', 
					'envoyconnect_user_meta' => '_envoyconnect_user_meta', 
					'envoyconnect_forms' => '_envoyconnect_user_forms', 
					'envoyconnect_user_queries' => '_envoyconnect_user_queries', 
					'envoyconnect_user_imports' => '_envoyconnect_user_imports', 
					'envoyconnect_user_exports' => '_envoyconnect_user_exports', 
					'envoyconnect_reserved_fields' => '_envoyconnect_reserved_fields', 
					'envoyconnect_wp_taxonomies' => '_envoyconnect_wp_taxonomies', 
					'envoyconnect_default_paupay_optional_fields' => '_envoyconnect_form_default_paupay_optional_fields', 
					'envoyconnect_default_paumail_signup_form' => '_envoyconnect_form_default_paumail_signup', 
	);
	foreach ( $optcon as $k => $v ) {
		
		// ESCAPE IF WE'VE ALREADY DONE THE UPDATE
		if ( false !== get_option( $v ) )
			continue;
			
		if ( 'envoyconnect_forms' == $k ) {
			$forms = get_option( $k );
			if ( false === $forms )
				continue;
				
			$formct = count( $forms );
			$formcg = 0;
			foreach ( $forms as $fk => $fv ) {
				// UPDATE
				$cv = get_option( 'envoyconnect_'.$fk );
				$new = update_option( '_envoyconnect_form_'.$fk, $cv );
				// VERIFY
				if ( !$new )
					continue;
					
				if ( $cv !== get_option( '_envoyconnect_form_'.$fk ) )
					continue;
					
				// DELETE
				delete_option( 'envoyconnect_'.$fk );
				$formcg++;
			}
			
			if ( $formct != $formcg )
				continue;
				
		} else if ( 'envoyconnect_user_queries' == $k ) {
			$queries = get_option( $k );
			if ( false === $queries )
				continue;
				
			$querct = count( $queries );
			$quercg = 0;
			foreach ( $queries as $qk => $qv ) {
				// UPDATE
				$cv = get_option( $qv['query'] );
				$new = update_option( '_'.$qv['query'], $cv );
				// VERIFY
				if ( !$new )
					continue;
					
				if ( $cv !== get_option( '_'.$qv['query'] ) )
					continue;
					
				// DELETE
				delete_option( $qv['query'] );
				$qv['query'] = '_'.$qv['query'];
				$new_queries[$qk] = $qv;
				$quercg++;
			}
			
			if ( $querct != $quercg )
				continue;
				
			update_option( $k, $new_queries );
		}
			
		// UPDATE
		$new = update_option( $v, get_option( $k ) );
		
		// VERIFY
		if ( !$new )
			continue;
			
		if ( get_option( $v ) !== get_option( $k ) )
			continue;
			
		// DELETE
		delete_option( $k );
						
	}
	
	// UPDATE USER FIELDS WITH ADDITIONAL DEFAULTS
	$field = array();
	$field_keys = array();
	
	// USERID -- WILL NOT BY SYNCED
	$field[] = array( 'source' => 'wpr', 'meta_key' => 'ID', 'tag' => '', 'name' => __( 'User ID', 'envoyconnect' ), 'options' => array( 'admin' => true, 'user' => false, 'signup' => false, 'reports' => true, 'public' => false, 'req' => false, 'field_type' => 'text', 'choices' => array() ), 'help' => '', 'column' => 'section_account_information', 'section' => 'account_information' );
	// USERDATE -- WILL NOT BY SYNCED
	$field[] = array( 'source' => 'wpr', 'meta_key' => 'user_registered', 'tag' => '', 'name' => __( 'Date Registered', 'envoyconnect' ), 'options' => array( 'admin' => true, 'user' => false, 'signup' => false, 'reports' => true, 'public' => false, 'req' => false, 'field_type' => 'text', 'choices' => array() ), 'help' => '', 'column' => 'section_account_information', 'section' => 'account_information' );
	
	foreach ( $field as $key => $value ) {		
	
		if ( false != get_option( 'envoyconnect_'.$value['meta_key'] ) )
			continue;
		
		// SET A NAMED VALUE FOR THE PAUPRESS_USER_META ARRAY AND 
		$field_keys[] = $value['meta_key'];
		// ADD THE OPTION
		add_option( 'envoyconnect_'.$value['meta_key'], $value );		
		
	}
	
	if ( !empty( $field_keys ) ) {
		$umo = get_option( '_envoyconnect_user_meta' );
		foreach ( $umo as $uk => $uv ) {
			// COLUMNS
			foreach ( $uv as $suk => $suv ) {
				if ( 'envoyconnect_account_information' == $suv ) {
					$acct = get_option( $suv );
					foreach ( $field_keys as $fk => $fv ) 
						$acct['options']['choices'][] = $fv;
					update_option( $suv, $acct );
					$aok = true;
				}
			}
		}
		// IF NO JOY, PUT IT IN COLUMN 3
		if ( !isset( $aok ) ) {
			foreach ( $field_keys as $fk => $fv ) 
				$umo['column_3'][] = 'envoyconnect_'.$fv;
			
			update_option( '_envoyconnect_user_meta', $umo );
		}
	}
	
	// LASTLY, LET'S ADD A DEFAULT CONTACT FORM
	$default_form = get_option( '_envoyconnect_form_contact_form' );
	if ( false === $default_form )
		$contact_form = envoyconnect_form_create();
	
	$forms_opt = get_option( '_envoyconnect_user_forms' );
	if ( false === $forms_opt ) {
		add_option( '_envoyconnect_user_forms', $contact_form );
	} else if ( isset( $contact_form ) ) {
		foreach ( $contact_form as $cfk => $cfv )
			$forms_opt[$cfk] = $cfv;
		
		update_option( '_envoyconnect_user_forms', $forms_opt );
	}

	return 'success!';
}


function envoyconnect_update_v_1_0_0() {
	
	global $wpdb;
	$q_query = $wpdb->get_col( "SELECT $wpdb->posts.ID from $wpdb->posts where post_type = 'pp_item'" );
	$allct = 0;
	$oldct = 0;
	$newct = 0;
	global $post;
	foreach ( $q_query as $id ) {
		$allct++;
		if ( false == get_post_meta( $id, '_pp_item_quantity', true ) ) {
			$oldct++;
			if ( false != update_post_meta( $id, '_pp_item_quantity', (int) 1 ) )
				$newct++;
		}
	}
	
	return "All is $allct and Old is $oldct and now, new is $newct";
	
}

?>