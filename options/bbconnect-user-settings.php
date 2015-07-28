<?php



function bbconnect_user_settings() {
	
	return apply_filters( 'bbconnect_user_settings', array(
		
		array( 'meta' => array( 
								'source' => 'bbconnect', 
								'meta_key' => '_bbconnect_user_admin_title', 
								'name' => __( 'Administrator Controls', 'bbconnect' ), 
								'help' => '', 
								'description' => '', 
								'options' => array( 
													'field_type' => 'title',
													'req' => false, 
													'public' => false, 
													'choices' => false
								) 
		) ),
		
		array( 'meta' => array( 
								'source' => 'bbconnect', 
								'meta_key' => '_bbconnect_capabilities', 
								'name' => __( 'Grant database access to:', 'bbconnect' ), 
								'help' => __( 'Determine who can create, edit, and delete users from your system.', 'bbconnect' ), 
								'options' => array( 
													'field_type' => 'plugin',
													'req' => false, 
													'public' => false, 
													'choices' => 'bbconnect_capabilities' 
								) 
		) ),
		
		array( 'meta' => array( 
								'source' => 'bbconnect', 
								'meta_key' => '_bbconnect_user_user_title', 
								'name' => __( 'User Controls', 'bbconnect' ), 
								'help' => '', 
								'description' => '', 
								'options' => array( 
													'field_type' => 'title',
													'req' => false, 
													'public' => false, 
													'choices' => false
								) 
		) ),
		
		array( 'meta' => array( 
								'source' => 'bbconnect', 
								'meta_key' => '_bbconnect_access', 
								'name' => __( 'Prevent Subscribers from accessing the WordPress Admin Area', 'bbconnect' ), 
								'help' => __( 'This applies to the Subscriber role and restricts all operations to the front end of the site.', 'bbconnect' ), 
								'options' => array( 
													'field_type' => 'checkbox',
													'req' => false, 
													'public' => false, 
													'choices' => false 
								) 
		) ),
		
		array( 'meta' => array( 
								'source' => 'bbconnect', 
								'meta_key' => '_bbconnect_public', 
								'name' => __( 'Default display of public fields to "on"', 'bbconnect' ), 
								'help' => __( 'Public fields by default are hidden. If a user has not stated a preference on their profile, you can change the default to show their public fields.', 'bbconnect' ), 
								'options' => array( 
													'field_type' => 'checkbox',
													'req' => false, 
													'public' => false, 
													'choices' => false 
								) 
		) ),
		
		array( 'meta' => array( 
								'source' => 'bbconnect', 
								'meta_key' => 'bbconnectpanels_user_contact', 
								'name' => __( 'Default display of "contact me" to "on"', 'bbconnect' ), 
								'help' => __( 'Individual contact forms are disabled by default. If a user has not stated a preference on their profile, you can change the default to enable their contact form.', 'bbconnect' ), 
								'options' => array( 
													'field_type' => 'checkbox',
													'req' => false, 
													'public' => false, 
													'choices' => false
								) 
		) ),
		
		array( 'meta' => array( 
								'source' => 'bbconnect', 
								'meta_key' => '_bbconnect_in_page_link', 
								'name' => __( 'Enable links to in-page profiles', 'bbconnect' ), 
								'help' => __( 'User profiles are automatically enabled via the panel system. This option, however, is an additional resource for users to link back directly to a page on your site containing their profile and public content.', 'bbconnect' ), 
								'options' => array( 
													'field_type' => 'checkbox',
													'req' => false, 
													'public' => false, 
													'choices' => false
								) 
		) ),
				
		array( 'meta' => array( 
								'source' => 'bbconnect', 
								'meta_key' => '_bbconnect_fields_title', 
								'name' => __( 'Profile Controls', 'bbconnect' ), 
								'help' => '', 
								'description' => '', 
								'options' => array( 
													'field_type' => 'title',
													'req' => false, 
													'public' => false, 
													'choices' => false
								) 
		) ),
		
		array( 'meta' => array( 
								'source' => 'bbconnect', 
								'meta_key' => '_bbconnect_username_prefix', 
								'name' => __( 'Set defaults for randomized usernames', 'bbconnect' ), 
								'help' => __( 'Usernames are required by WordPress and they must be unique. You may not always get to choose them so, these defaults will stand-in.', 'bbconnect' ), 
								'options' => array( 
													'field_type' => 'plugin',
													'req' => false, 
													'public' => false, 
													'choices' => 'bbconnect_username_prefix', 
								) 
		) ),
		
		array( 'meta' => array( 
								'source' => 'bbconnect', 
								'meta_key' => '_bbconnect_taxonomy_display', 
								'name' => __( 'Alternate display of user taxonomies', 'bbconnect' ), 
								'help' => __( 'The select list option uses an advanced type-as-you-go option.', 'bbconnect' ), 
								'options' => array( 
													'field_type' => 'select',
													'req' => false, 
													'public' => false, 
													'choices' => array( 
																		'checkboxes' => 'Checkboxes', 
																		'select' => 'Select List', 
													)
								) 
		) ),
		/*
		array( 'meta' => array( 
								'source' => 'bbconnect', 
								'meta_key' => '_bbconnect_user_display', 
								'name' => __( 'Basic Information Layout', 'bbconnect' ), 
								'help' => '', 
								'options' => array( 
													'field_type' => 'multiselect',
													'req' => false, 
													'public' => false, 
													'choices' => bbconnect_get_user_metadata( array( 'group_break' => 'true' ) ), 
								) 
		) ),
		*/
		array( 'meta' => array( 
								'source' => 'bbconnect', 
								'meta_key' => '_bbconnect_compatability_title', 
								'name' => __( 'WordPress Compatability Mode', 'bbconnect' ), 
								'help' => '', 
								'description' => __( 'Preferences to ease the transition.', 'bbconnect' ), 
								'options' => array( 
													'field_type' => 'title',
													'req' => false, 
													'public' => false, 
													'choices' => false
								) 
		) ),
		
		array( 'meta' => array( 
								'source' => 'bbconnect', 
								'meta_key' => '_bbconnect_compatability_mode', 
								'name' => __( 'Enable WordPress Default User View', 'bbconnect' ), 
								'help' => __( 'By default, BB Connect replaces the WordPress default list view. You can re-enable it with this option.', 'bbconnect' ), 
								'options' => array( 
													'field_type' => 'checkbox',
													'req' => false, 
													'public' => false, 
													'choices' => false 
								) 
		) ),
	
	));
	
}

function bbconnect_capabilities( $args = null ) {

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

	if ( !isset( $wp_roles ) )
		$wp_roles = new WP_Roles();
		
	echo '<ul>';

	foreach ( $wp_roles->roles as $key => $val ) {
?>
		<li>
			<?php if ( 'administrator' == $key ) { ?>
			<a class="umtf admin on"><?php printf( __( '%1$s Enabled by default', 'bbconnect' ), $val['name'] ); ?></a>
			<?php } else { ?>
			<a class="umt admin <?php if ( !empty( $fvalue[$key] ) ) { echo 'on'; } else { echo 'off'; } ?>" title="admin_<?php echo 'bbconnect_' . $key; ?>"><input type="hidden" id="admin_<?php echo 'bbconnect_' . $key; ?>"  name="_bbc_option[_bbconnect_capabilities][<?php echo $key; ?>]" value="<?php if ( !empty( $fvalue[$key] ) ) { echo $fvalue[$key]; } else { echo '0'; } ?>" /><?php echo $val['name']; ?></a>
			<?php } ?>
		</li>
<?php 
	}

	echo '</ul>';

}


function bbconnect_save_capabilities() {
	if ( empty( $_POST['_bbc_option']['_bbconnect_capabilities'] ) )
		return false;
		
	foreach ( $_POST['_bbc_option']['_bbconnect_capabilities'] as $key => $val ) {
		if ( 'administrator' != $key ) {
			if ( false != $val ) {
				bbconnect_role_capabilities( 'add', $key );
			} else {
				bbconnect_role_capabilities( 'remove', $key );
			}
		}		
	}
}


function bbconnect_username_prefix( $args = null ) {

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
	
	echo '<input type="text" class="input-short" name="_bbc_option[_bbconnect_username_prefix]" value="' . $fvalue . '" />' . bbconnect_random( array( 'compact' => true ) ) . '<br />';
	echo '<span class="example-text">' . sprintf( __( 'You may use this code %1$s for the Year or any text string as a prefix.', 'bbconnect' ), '%y%' ) . '</span>';
	
}