<?php



function bbconnect_actions_settings() {

	return apply_filters( 'bbconnect_actions_settings', array(

		array( 'meta' => array(
								'source' => 'bbconnect',
								'meta_key' => '_bbconnect_actions_title',
								'name' => __( 'Connexions Actions', 'bbconnect' ),
								'help' => '',
								'description' => __( 'Connexions actions are, essentially, WordPress post types that users can manipulate.', 'bbconnect' ),
								'options' => array(
													'field_type' => 'title',
													'req' => false,
													'public' => false,
													'choices' => false
								)
		) ),

		array( 'meta' => array(
								'source' => 'bbconnect',
								'meta_key' => '_bbconnect_post_types',
								'name' => '',
								'help' => '',
								'options' => array(
													'field_type' => 'plugin',
													'req' => false,
													'public' => false,
													'choices' => 'bbconnect_post_types'
								)
		) ),

	));

}


function bbconnect_post_types( $args = null ) {

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

	if ( defined( 'BBCPRO_VER' ) ) {
		$pro_msg = '';
	} else {
		$pro_msg = __( 'This is a Pro feature. Visit www.bbconnect.com for more information! ', 'bbconnect' );
	}

	if ( !is_array( $fvalue ) )
		$fvalue = array();

?>
	<table class="widefat option-table">
		<thead>
			<tr>
				<th><?php _e( 'Type', 'bbconnect' ); ?></th>
				<th><?php _e( 'Admins', 'bbconnect' ); ?><a class="help" title="<?php _e( 'Admins can view and edit this type through the user profile history tab', 'bbconnect' ); ?>">&nbsp;</a></th>
				<th><?php _e( 'Users', 'bbconnect' ); ?><a class="help" title="<?php _e( 'Users can view this type through their profile history tab', 'bbconnect' ); ?>">&nbsp;</a></th>
				<th><?php _e( 'Public', 'bbconnect' ); ?><a class="help" title="<?php printf( __( '%1$s This type is publicly visible in the user profile history tab', 'bbconnect' ), $pro_msg ); ?>">&nbsp;</a></th>
				<th><?php _e( 'Reports', 'bbconnect' ); ?><a class="help" title="<?php printf( __( '%1$s Admins can search for this type in the Reports section', 'bbconnect' ), $pro_msg ); ?>">&nbsp;</a></th>
			</tr>
		</thead>
		<tbody class="option-body">
		<?php
			$args = array();
			$post_types = get_post_types( $args, 'objects' );
			$exclusions = array_merge( array( 'attachment', 'revision', 'nav_menu_item' ), bbconnect_exclude_user_actions() );
			$inits = bbconnect_get_init_user_actions();

			foreach ( $post_types as $post ) {

				// EXCLUDE PARTICULAR POST TYPES
				if ( in_array( $post->name, $exclusions ) )
					continue;

				$post_type = $post->name;

				// INTERCEPT INIT USER ACTIONS
				// ALWAYS PRESERVE THE NON-PUBLIC NATURE OF INIT POST TYPES
				if ( isset( $inits[$post_type] ) ) {
					if ( !isset( $fvalue[$post_type]['options']['admin'] ) )
						$fvalue[$post_type]['options']['admin'] = $inits[$post_type]['options']['admin'];

					if ( !isset( $fvalue[$post_type]['options']['user'] ) )
						$fvalue[$post_type]['options']['user'] = $inits[$post_type]['options']['user'];

					$fvalue[$post_type]['options']['public'] = $inits[$post_type]['options']['public'];

					if ( !isset( $fvalue[$post_type]['options']['reports'] ) )
						$fvalue[$post_type]['options']['reports'] = $inits[$post_type]['options']['reports'];

					$ut = 'umt';
					$pt = 'umtf';
				} else {
					$ut = 'umt';
					$pt = 'umt';
				}

				$post_arr = array(
									'source' => 'wp',
									'type' => $post->name,
									'single' => $post->labels->singular_name,
									'plural' => $post->labels->name
				)
		?>
			<tr>
				<td>
					<?php echo $post->labels->name; ?><br />
					<?php echo $post->description; ?>
				</td>
				<td>
					<a class="<?php echo $ut; ?> bbconnect <?php if ( isset( $fvalue[$post_type] ) && false != $fvalue[$post_type]['options']['admin'] ) { echo 'on'; } else { echo 'off'; } ?>" title="bbconnect_<?php echo 'bbconnect_' . $post_type; ?>"><input type="hidden" id="bbconnect_<?php echo 'bbconnect_' . $post_type; ?>"  name="_bbc_option[_bbconnect_post_types][<?php echo $post_type; ?>][options][admin]" value="<?php if ( isset( $fvalue[$post_type] ) && false != $fvalue[$post_type]['options']['admin'] ) { echo $fvalue[$post_type]['options']['admin']; } else { echo '0'; } ?>" />&nbsp;</a>
					<?php
						foreach ( $post_arr as $key => $val )
							echo '<input type="hidden" name="_bbc_option[_bbconnect_post_types][' . $post->name . '][' . $key . ']" value="' . $val . '" />';
					?>
				</td>
				<td>
					<a class="<?php echo $ut; ?> bbconnect <?php if ( isset( $fvalue[$post_type] ) && false != $fvalue[$post_type]['options']['user'] ) { echo 'on'; } else { echo 'off'; } ?>" title="bbconnect_<?php echo 'bbconnect_' . $post_type.'_user'; ?>"><input type="hidden" id="bbconnect_<?php echo 'bbconnect_' . $post_type.'_user'; ?>"  name="_bbc_option[_bbconnect_post_types][<?php echo $post_type; ?>][options][user]" value="<?php if ( isset( $fvalue[$post_type] ) && false != $fvalue[$post_type]['options']['user'] ) { echo $fvalue[$post_type]['options']['user']; } else { echo '0'; } ?>" />&nbsp;</a>
				</td>
				<td>
					<a class="<?php echo $pt; ?> bbconnect <?php if ( isset( $fvalue[$post_type] ) && false != $fvalue[$post_type]['options']['public'] ) { echo 'on'; } else { echo 'off'; } ?>" title="bbconnect_<?php echo 'bbconnect_' . $post_type.'_public'; ?>"><input type="hidden" id="bbconnect_<?php echo 'bbconnect_' . $post_type.'_public'; ?>"  name="_bbc_option[_bbconnect_post_types][<?php echo $post_type; ?>][options][public]" value="<?php if ( isset( $fvalue[$post_type] ) && false != $fvalue[$post_type]['options']['public'] ) { echo $fvalue[$post_type]['options']['public']; } else { echo '0'; } ?>" />&nbsp;</a>
				</td>
				<td>
					<a class="<?php echo $ut; ?> bbconnect <?php if ( isset( $fvalue[$post_type] ) && false != $fvalue[$post_type]['options']['reports'] ) { echo 'on'; } else { echo 'off'; } ?>" title="bbconnect_<?php echo 'bbconnect_' . $post_type.'_reports'; ?>"><input type="hidden" id="bbconnect_<?php echo 'bbconnect_' . $post_type.'_reports'; ?>"  name="_bbc_option[_bbconnect_post_types][<?php echo $post_type; ?>][options][reports]" value="<?php if ( isset( $fvalue[$post_type] ) && false != $fvalue[$post_type]['options']['reports'] ) { echo $fvalue[$post_type]['options']['reports']; } else { echo '0'; } ?>" />&nbsp;</a>
				</td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
<?php
}


?>