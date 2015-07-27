<?php

function bbconnect_system_settings() {
	
	return apply_filters( 'bbconnect_system_settings', array(
						
		array( 'meta' => array( 
								'source' => 'bbconnect', 
								'meta_key' => '_bbconnect_mail_defaults', 
								'name' => __( 'Mail Defaults', 'bbconnect' ), 
								'help' => '', 
								'description' => __( 'Set the default email information for your site', 'bbconnect' ), 
								'options' => array( 
													'field_type' => 'title',
													'req' => false, 
													'public' => false, 
													'choices' => false
								) 
		) ),
		
		array( 'meta' => array( 
								'source' => 'bbconnect', 
								'meta_key' => '_bbconnect_mail_from', 
								'name' => __( 'Default email', 'bbconnect' ), 
								'help' => '', 
								'options' => array( 
													'field_type' => 'text',
													'req' => false, 
													'public' => false, 
													'choices' => get_option( 'admin_email' ),  
								) 
		) ),
		
		array( 'meta' => array( 
								'source' => 'bbconnect', 
								'meta_key' => '_bbconnect_mail_from_name', 
								'name' => __( 'Default email name', 'bbconnect' ), 
								'help' => '', 
								'options' => array( 
													'field_type' => 'text',
													'req' => false, 
													'public' => false, 
													'choices' => get_option( 'blogname' ),  
								) 
		) ),
		
		array( 'meta' => array( 
								'source' => 'bbconnect', 
								'meta_key' => '_bbconnect_field_preferences', 
								'name' => __( 'Field Preferences', 'bbconnect' ), 
								'help' => '', 
								'options' => array( 
													'field_type' => 'title',
													'req' => false, 
													'public' => false, 
													'choices' => false
								) 
		) ),
		
		array( 'meta' => array( 
								'source' => 'bbconnect', 
								'meta_key' => '_bbconnect_yearlow', 
								'name' => __( 'Low range for date fields', 'bbconnect' ), 
								'help' => __( 'Choose how many years the date field will go into the past', 'bbconnect' ), 
								'options' => array( 
													'field_type' => 'text',
													'req' => false, 
													'public' => false, 
													'class' => 'input-short', 
													'choices' => 5,  
								) 
		) ),
		
		array( 'meta' => array( 
								'source' => 'bbconnect', 
								'meta_key' => '_bbconnect_yearhigh', 
								'name' => __( 'High range for date fields', 'bbconnect' ), 
								'help' => __( 'Choose how many years the date field will go into the future', 'bbconnect' ), 
								'options' => array( 
													'field_type' => 'text',
													'req' => false, 
													'public' => false, 
													'class' => 'input-short', 
													'choices' => 10,  
								) 
		) ),
		
		array( 'meta' => array( 
								'source' => 'bbconnect', 
								'meta_key' => '_bbconnect_systems_title', 
								'name' => __( 'System Information', 'bbconnect' ), 
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
								'meta_key' => '_bbconnect_error_log', 
								'name' => __( 'Error Log', 'bbconnect' ), 
								'help' => '', 
								'options' => array( 
													'field_type' => 'plugin',
													'req' => false, 
													'public' => false, 
													'choices' => 'bbconnect_get_error_log' 
								) 
		) ),
		
	));
	
}


function bbconnect_get_error_log( $args = null ) {	

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
	
	//bbconnect_error_log( array( 'error' => 'silliness' ) );

?>
	<table class="widefat option-table">
		<thead>
			<tr>
				<th><?php _e( 'Date', 'bbconnect' ); ?></th>
				<th><?php _e( 'Application', 'bbconnect' ); ?><a class="help" title="<?php _e( 'What component was affected?', 'bbconnect' ); ?>">&nbsp;</a></th>
				<th><?php _e( 'Type', 'bbconnect' ); ?><a class="help" title="<?php _e( 'Did this affect a user or an action?', 'bbconnect' ); ?>">&nbsp;</a></th>
				<th><?php _e( 'ID', 'bbconnect' ); ?><a class="help" title="<?php _e( 'The ID of the affected user or action', 'bbconnect' ); ?>">&nbsp;</a></th>
				<th><?php _e( 'Error', 'bbconnect' ); ?><a class="help" title="<?php _e( 'Details on the error', 'bbconnect' ); ?>">&nbsp;</a></th>
			</tr>
		</thead>
		<tbody class="option-body">
		<?php 
			if ( false != $fvalue && !empty( $fvalue ) ) {

				foreach ( $fvalue as $key => $line ) { 
		?>
				<tr>
					<td><?php echo date( get_option( 'date_format' ), $line['date'] ); ?></td>
					<td><?php echo esc_attr( $line['app'] ); ?></td>
					<td><?php echo esc_attr( $line['type'] ); ?></td>
					<td><?php echo (int) esc_attr( $line['id'] ); ?></td>
					<td><?php echo esc_attr( $line['error'] ); ?></td>
				</tr>
		<?php 
				} 
			} else {
				echo '<tr><td colspan="5"><h2 style="text-align: center;">'. __( 'hooray! no errors here!', 'bbconnect' ) . '</h2></td></tr>';
			}
		?>
		</tbody>
		<tfoot>
			<tr id="log-actions">
				<th colspan="5" style="text-align: right;">
					<a id="clear-log" class="button sub"><?php _e( 'Clear Log', 'bbconnect' ); ?></a>
					<input id="error-log-reset" type="hidden" name="_bbc_option[_bbconnect_error_log]" value="" disabled="disabled" />
					<script type="text/javascript">
					jQuery(document).ready(function(){
						jQuery('#log-actions').on('click','#clear-log',function(){
							var answer = confirm( '<?php echo BBCONNECT_CONFIRM_DELETE; ?>' );
							if (answer) {
								jQuery('#error-log-reset').removeAttr('disabled');
								jQuery('.submit input[type="submit"]').click();
							}
						});
					});
					</script>
				</th>
			</tr>
		</tfoot>
	</table>
<?php
}

// READDRESSES THE EMAIL SENDER
function bbconnect_mail_from() {
	$from = get_option( '_bbconnect_mail_from' );
	if ( false === $from )
		$from = get_option( 'admin_email' );

	return $from;
}

// RENAMES THE EMAIL SENDER
function bbconnect_mail_from_name() {
	$from = get_option( '_bbconnect_mail_from_name' );
	if ( false === $from )
		$from = get_option( 'blogname' );

	return $from;
}
?>