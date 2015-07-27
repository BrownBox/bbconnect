<?php 

/**
 * Define the Versions
 *
 * @since 1.0.0
 */
function bbconnect_versions() {
	
	$bbconnect_versions = array(
		'0.9.2' => 'bbconnect_update_v_0_9_2', 
		'0.9.4' => 'bbconnect_update_v_0_9_4', 
		'1.0.0' => 'bbconnect_update_v_1_0_0', 
		);
		
	return $bbconnect_versions;

}

function bbconnect_update_v_0_9_2() {

	$um = array( '1', '2', '3', '4' );
	foreach ( $um as $m ) {
		
		$meta = bbconnect_get_option( 'address_' . $m );
		if ( false != $meta ) {
			$address_keys = array();

			foreach ( $meta['options']['choices'] as $k => $v ) {
				if ( false !== strpos( $v, 'address_type' ) ) {
					$address_keys[] = $v;
					
					$loc = '_'.$m;
					$loc_tag = strtoupper( substr( $m, 0, 1 ) );
					
					$address = array();
					// ADDRESS RECIPIENT
					$address[] = array( 'source' => 'bbconnect', 'meta_key' => 'address_recipient'.$loc, 'tag' => 'ADREP'.$loc_tag, 'name' => __( 'Address Recipient', 'bbconnect' ), 'options' => array( 'admin' => true, 'user' => true, 'signup' => false, 'reports' => true, 'public' => false, 'req' => false, 'field_type' => 'text', 'choices' => array() ), 'group' => 'address'.$loc, 'help' => __( 'The person receiving deliveries at this address.', 'bbconnect' ) );
					// ADDRESS ORGANIZATION
					$address[] = array( 'source' => 'bbconnect', 'meta_key' => 'address_organization'.$loc, 'tag' => 'ADORG'.$loc_tag, 'name' => __( 'Address Organization', 'bbconnect' ), 'options' => array( 'admin' => true, 'user' => true, 'signup' => false, 'reports' => true, 'public' => false, 'req' => false, 'field_type' => 'text', 'choices' => array() ), 'group' => 'address'.$loc, 'help' => __( 'The company or organization at this address.', 'bbconnect' ) );
					
					foreach ( $address as $key => $value ) {		
					
						if ( false != get_option( 'bbconnect_'.$value['meta_key'] ) )
							continue;
						
						// SET A NAMED VALUE FOR THE BBCONNECT_USER_META ARRAY AND 
						$address_keys[] = 'bbconnect_'.$value['meta_key'];
						// ADD THE OPTION
						add_option( 'bbconnect_'.$value['meta_key'], $value );		
						
					}
					
				} else {
					$address_keys[] = $v;
				}
			}
			$meta['options']['choices'] = $address_keys;
			update_option( bbconnect_get_option( 'address_' . $m, true ), $meta );
			
		}
			
	}

	return 'success!';
}


function bbconnect_update_v_0_9_4() {
	
	// UPDATE RESERVED OPTIONS TO UNDERSCORE PREFIXES
	$optcon = array( 
					'bbconnect_post_types' => '_bbconnect_post_types', 
					'bbconnect_error_log' => '_bbconnect_error_log', 
					'bbconnect_capabilities' => '_bbconnect_capabilities', 
					'bbconnect_user_access_option' => '_bbconnect_access', 
					'bbconnect_user_public_option' => '_bbconnect_public', 
					'bbconnect_taxonomy_display' => '_bbconnect_taxonomy_display', 
					'bbconnect_basic_user_display' => '_bbconnect_user_display', 
					'bbconnect_compatability_mode' => '_bbconnect_compatability_mode', 
					'bbconnect_user_meta' => '_bbconnect_user_meta', 
					'bbconnect_forms' => '_bbconnect_user_forms', 
					'bbconnect_user_queries' => '_bbconnect_user_queries', 
					'bbconnect_user_imports' => '_bbconnect_user_imports', 
					'bbconnect_user_exports' => '_bbconnect_user_exports', 
					'bbconnect_reserved_fields' => '_bbconnect_reserved_fields', 
					'bbconnect_wp_taxonomies' => '_bbconnect_wp_taxonomies', 
					'bbconnect_default_bbcpay_optional_fields' => '_bbconnect_form_default_bbcpay_optional_fields', 
					'bbconnect_default_bbcmail_signup_form' => '_bbconnect_form_default_bbcmail_signup', 
	);
	foreach ( $optcon as $k => $v ) {
		
		// ESCAPE IF WE'VE ALREADY DONE THE UPDATE
		if ( false !== get_option( $v ) )
			continue;
			
		if ( 'bbconnect_forms' == $k ) {
			$forms = get_option( $k );
			if ( false === $forms )
				continue;
				
			$formct = count( $forms );
			$formcg = 0;
			foreach ( $forms as $fk => $fv ) {
				// UPDATE
				$cv = get_option( 'bbconnect_'.$fk );
				$new = update_option( '_bbconnect_form_'.$fk, $cv );
				// VERIFY
				if ( !$new )
					continue;
					
				if ( $cv !== get_option( '_bbconnect_form_'.$fk ) )
					continue;
					
				// DELETE
				delete_option( 'bbconnect_'.$fk );
				$formcg++;
			}
			
			if ( $formct != $formcg )
				continue;
				
		} else if ( 'bbconnect_user_queries' == $k ) {
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
	$field[] = array( 'source' => 'wpr', 'meta_key' => 'ID', 'tag' => '', 'name' => __( 'User ID', 'bbconnect' ), 'options' => array( 'admin' => true, 'user' => false, 'signup' => false, 'reports' => true, 'public' => false, 'req' => false, 'field_type' => 'text', 'choices' => array() ), 'help' => '', 'column' => 'section_account_information', 'section' => 'account_information' );
	// USERDATE -- WILL NOT BY SYNCED
	$field[] = array( 'source' => 'wpr', 'meta_key' => 'user_registered', 'tag' => '', 'name' => __( 'Date Registered', 'bbconnect' ), 'options' => array( 'admin' => true, 'user' => false, 'signup' => false, 'reports' => true, 'public' => false, 'req' => false, 'field_type' => 'text', 'choices' => array() ), 'help' => '', 'column' => 'section_account_information', 'section' => 'account_information' );
	
	foreach ( $field as $key => $value ) {		
	
		if ( false != get_option( 'bbconnect_'.$value['meta_key'] ) )
			continue;
		
		// SET A NAMED VALUE FOR THE BBCONNECT_USER_META ARRAY AND 
		$field_keys[] = $value['meta_key'];
		// ADD THE OPTION
		add_option( 'bbconnect_'.$value['meta_key'], $value );		
		
	}
	
	if ( !empty( $field_keys ) ) {
		$umo = get_option( '_bbconnect_user_meta' );
		foreach ( $umo as $uk => $uv ) {
			// COLUMNS
			foreach ( $uv as $suk => $suv ) {
				if ( 'bbconnect_account_information' == $suv ) {
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
				$umo['column_3'][] = 'bbconnect_'.$fv;
			
			update_option( '_bbconnect_user_meta', $umo );
		}
	}
	
	// LASTLY, LET'S ADD A DEFAULT CONTACT FORM
	$default_form = get_option( '_bbconnect_form_contact_form' );
	if ( false === $default_form )
		$contact_form = bbconnect_form_create();
	
	$forms_opt = get_option( '_bbconnect_user_forms' );
	if ( false === $forms_opt ) {
		add_option( '_bbconnect_user_forms', $contact_form );
	} else if ( isset( $contact_form ) ) {
		foreach ( $contact_form as $cfk => $cfv )
			$forms_opt[$cfk] = $cfv;
		
		update_option( '_bbconnect_user_forms', $forms_opt );
	}

	return 'success!';
}


function bbconnect_update_v_1_0_0() {
	
	global $wpdb;
	$q_query = $wpdb->get_col( "SELECT $wpdb->posts.ID from $wpdb->posts where post_type = 'bbc_item'" );
	$allct = 0;
	$oldct = 0;
	$newct = 0;
	global $post;
	foreach ( $q_query as $id ) {
		$allct++;
		if ( false == get_post_meta( $id, '_bbc_item_quantity', true ) ) {
			$oldct++;
			if ( false != update_post_meta( $id, '_bbc_item_quantity', (int) 1 ) )
				$newct++;
		}
	}
	
	return "All is $allct and Old is $oldct and now, new is $newct";
	
}

?>