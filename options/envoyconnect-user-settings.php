<?php



function envoyconnect_user_settings() {
	
	return apply_filters( 'envoyconnect_user_settings', array(
		
		array( 'meta' => array( 
								'source' => 'envoyconnect', 
								'meta_key' => '_envoyconnect_user_admin_title', 
								'name' => __( 'Administrator Controls', 'envoyconnect' ), 
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
								'source' => 'envoyconnect', 
								'meta_key' => '_envoyconnect_capabilities', 
								'name' => __( 'Grant database access to:', 'envoyconnect' ), 
								'help' => __( 'Determine who can create, edit, and delete users from your system.', 'envoyconnect' ), 
								'options' => array( 
													'field_type' => 'plugin',
													'req' => false, 
													'public' => false, 
													'choices' => 'envoyconnect_capabilities' 
								) 
		) ),
		
		array( 'meta' => array( 
								'source' => 'envoyconnect', 
								'meta_key' => '_envoyconnect_user_user_title', 
								'name' => __( 'User Controls', 'envoyconnect' ), 
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
								'source' => 'envoyconnect', 
								'meta_key' => '_envoyconnect_access', 
								'name' => __( 'Prevent Subscribers from accessing the WordPress Admin Area', 'envoyconnect' ), 
								'help' => __( 'This applies to the Subscriber role and restricts all operations to the front end of the site.', 'envoyconnect' ), 
								'options' => array( 
													'field_type' => 'checkbox',
													'req' => false, 
													'public' => false, 
													'choices' => false 
								) 
		) ),
		
		array( 'meta' => array( 
								'source' => 'envoyconnect', 
								'meta_key' => '_envoyconnect_public', 
								'name' => __( 'Default display of public fields to "on"', 'envoyconnect' ), 
								'help' => __( 'Public fields by default are hidden. If a user has not stated a preference on their profile, you can change the default to show their public fields.', 'envoyconnect' ), 
								'options' => array( 
													'field_type' => 'checkbox',
													'req' => false, 
													'public' => false, 
													'choices' => false 
								) 
		) ),
		
		array( 'meta' => array( 
								'source' => 'envoyconnect', 
								'meta_key' => 'envoyconnectpanels_user_contact', 
								'name' => __( 'Default display of "contact me" to "on"', 'envoyconnect' ), 
								'help' => __( 'Individual contact forms are disabled by default. If a user has not stated a preference on their profile, you can change the default to enable their contact form.', 'envoyconnect' ), 
								'options' => array( 
													'field_type' => 'checkbox',
													'req' => false, 
													'public' => false, 
													'choices' => false
								) 
		) ),
		
		array( 'meta' => array( 
								'source' => 'envoyconnect', 
								'meta_key' => '_envoyconnect_in_page_link', 
								'name' => __( 'Enable links to in-page profiles', 'envoyconnect' ), 
								'help' => __( 'User profiles are automatically enabled via the panel system. This option, however, is an additional resource for users to link back directly to a page on your site containing their profile and public content.', 'envoyconnect' ), 
								'options' => array( 
													'field_type' => 'checkbox',
													'req' => false, 
													'public' => false, 
													'choices' => false
								) 
		) ),
				
		array( 'meta' => array( 
								'source' => 'envoyconnect', 
								'meta_key' => '_envoyconnect_fields_title', 
								'name' => __( 'Profile Controls', 'envoyconnect' ), 
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
								'source' => 'envoyconnect', 
								'meta_key' => '_envoyconnect_username_prefix', 
								'name' => __( 'Set defaults for randomized usernames', 'envoyconnect' ), 
								'help' => __( 'Usernames are required by WordPress and they must be unique. You may not always get to choose them so, these defaults will stand-in.', 'envoyconnect' ), 
								'options' => array( 
													'field_type' => 'plugin',
													'req' => false, 
													'public' => false, 
													'choices' => 'envoyconnect_username_prefix', 
								) 
		) ),
		
		array( 'meta' => array( 
								'source' => 'envoyconnect', 
								'meta_key' => '_envoyconnect_taxonomy_display', 
								'name' => __( 'Alternate display of user taxonomies', 'envoyconnect' ), 
								'help' => __( 'The select list option uses an advanced type-as-you-go option.', 'envoyconnect' ), 
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
								'source' => 'envoyconnect', 
								'meta_key' => '_envoyconnect_user_display', 
								'name' => __( 'Basic Information Layout', 'envoyconnect' ), 
								'help' => '', 
								'options' => array( 
													'field_type' => 'multiselect',
													'req' => false, 
													'public' => false, 
													'choices' => envoyconnect_get_user_metadata( array( 'group_break' => 'true' ) ), 
								) 
		) ),
		*/
		array( 'meta' => array( 
								'source' => 'envoyconnect', 
								'meta_key' => '_envoyconnect_compatability_title', 
								'name' => __( 'WordPress Compatability Mode', 'envoyconnect' ), 
								'help' => '', 
								'description' => __( 'Preferences to ease the transition.', 'envoyconnect' ), 
								'options' => array( 
													'field_type' => 'title',
													'req' => false, 
													'public' => false, 
													'choices' => false
								) 
		) ),
		
		array( 'meta' => array( 
								'source' => 'envoyconnect', 
								'meta_key' => '_envoyconnect_compatability_mode', 
								'name' => __( 'Enable WordPress Default User View', 'envoyconnect' ), 
								'help' => __( 'By default, EnvoyConnect replaces the WordPress default list view. You can re-enable it with this option.', 'envoyconnect' ), 
								'options' => array( 
													'field_type' => 'checkbox',
													'req' => false, 
													'public' => false, 
													'choices' => false 
								) 
		) ),
	
	));
	
}

function envoyconnect_capabilities( $args = null ) {

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
			<a class="umtf admin on"><?php printf( __( '%1$s Enabled by default', 'envoyconnect' ), $val['name'] ); ?></a>
			<?php } else { ?>
			<a class="umt admin <?php if ( !empty( $fvalue[$key] ) ) { echo 'on'; } else { echo 'off'; } ?>" title="admin_<?php echo 'envoyconnect_' . $key; ?>"><input type="hidden" id="admin_<?php echo 'envoyconnect_' . $key; ?>"  name="_pp_option[_envoyconnect_capabilities][<?php echo $key; ?>]" value="<?php if ( !empty( $fvalue[$key] ) ) { echo $fvalue[$key]; } else { echo '0'; } ?>" /><?php echo $val['name']; ?></a>
			<?php } ?>
		</li>
<?php 
	}

	echo '</ul>';

}


function envoyconnect_save_capabilities() {
	if ( empty( $_POST['_pp_option']['_envoyconnect_capabilities'] ) )
		return false;
		
	foreach ( $_POST['_pp_option']['_envoyconnect_capabilities'] as $key => $val ) {
		if ( 'administrator' != $key ) {
			if ( false != $val ) {
				envoyconnect_role_capabilities( 'add', $key );
			} else {
				envoyconnect_role_capabilities( 'remove', $key );
			}
		}		
	}
}


function envoyconnect_username_prefix( $args = null ) {

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
	
	echo '<input type="text" class="input-short" name="_pp_option[_envoyconnect_username_prefix]" value="' . $fvalue . '" />' . envoyconnect_random( array( 'compact' => true ) ) . '<br />';
	echo '<span class="example-text">' . sprintf( __( 'You may use this code %1$s for the Year or any text string as a prefix.', 'envoyconnect' ), '%y%' ) . '</span>';
	
}