<?php

/**
 * Loads default meta for processing.
 *
 * @since 1.0.0
 *
 * @param This function accepts no parameters.
 *
 * @return array.
 */
 
function envoyconnect_user_actions_meta() {
	
	return apply_filters( 'envoyconnect_user_actions_meta', array(
						
		array( 'meta' => array( 
								'source' => 'envoyconnect', 
								'meta_key' => '_pp_author', 
								'name' => '', 
								'help' => '', 
								'options' => array( 
													'field_type' => 'plugin',
													'req' => false, 
													'public' => false, 
													'choices' => 'envoyconnect_get_action_author'
								) 
		) ),
		
		array( 'meta' => array( 
								'source' => 'envoyconnect', 
								'meta_key' => '_pp_agent', 
								'name' => '', 
								'help' =>'', 
								'options' => array( 
													'field_type' => 'plugin',
													'req' => false, 
													'public' => false, 
													'choices' => 'envoyconnect_get_action_agent' 
								) 
		) ),
		
		array( 'meta' => array( 
								'source' => 'envoyconnect', 
								'meta_key' => '_pp_log', 
								'name' => '', 
								'help' =>'', 
								'options' => array( 
													'field_type' => 'plugin',
													'req' => false, 
													'public' => false, 
													'choices' => 'envoyconnect_get_action_log' 
								) 
		) ),
		
	));

}


/**
 * Retrieves the user actions meta and opens up a filter.
 *
 * @since 1.0.2
 *
 * @param This function accepts no parameters.
 *
 * @return null If you don't see your actions, it didn't work. :)
 */

function envoyconnect_get_user_actions_meta() {
	return apply_filters( 'envoyconnect_get_user_actions_meta', envoyconnect_user_actions_meta() ); 
}



/**
 * Callback function for aggregating all of the meta boxes for User Actions.
 *
 * @since 1.0.2
 *
 * @param This function accepts no parameters.
 *
 * @return null This function does not return anything.
 */
 
function envoyconnect_user_actions_meta_box_cb() {
	
	$envoyconnect_user_actions = envoyconnect_get_user_actions();

	if ( !empty( $envoyconnect_user_actions ) ) {
		foreach ( $envoyconnect_user_actions as $key => $value ) {
			add_meta_box( $value['type'] . '-context', __( 'Action Meta', 'envoyconnect' ), 'envoyconnect_user_actions_meta_box', $value['type'], 'normal', 'default' );
			do_action( 'envoyconnect_user_actions_meta_add', $value );
		}
	}
	
}

/**
 * Displays a standardized context meta box for Actions.
 *
 * @since 1.0.2
 *
 * @param obj $post. The current post data.
 *
 * @return html output or the custom meta panel.
 */
 
function envoyconnect_user_actions_meta_box( $post ) {
	envoyconnect_user_actions_nonce_field();
	envoyconnect_user_actions_meta_fields( array( 'post_id' => $post->ID, 'fields' => envoyconnect_get_user_actions_meta() ) );
}


/**
 * Displays the fields captured by post type conscripted as an "action." Utilizes the fields API.
 *
 * @since 1.0.0
 *
 * @param none.
 *
 * @return array.
 */
 
function envoyconnect_user_actions_meta_fields( $args = null ) {

	// SET THE DEFAULTS TO BE OVERRIDDEN AS DESIRED
	$defaults = array( 
					'post_id' => false, 
					'fields' => false, 
					'action' => 'edit',
					'post_val' => array()
				);
	
	// PARSE THE INCOMING ARGS
	$args = wp_parse_args( $args, $defaults );

	// EXTRACT THE VARIABLES
	extract( $args, EXTR_SKIP );
?>
	<div class="action-panel">
			
		<div class="action-field"><div class="inside">
				
			<fieldset>
				<ul>
				<?php
					foreach ( $fields as $field ) {
						
						$meta_key = $field['meta']['meta_key'];
						$field['type'] = 'post';
						$field['action'] = $action;
						$field['id'] = $post_id;
						if ( isset( $post_val[$meta_key] ) ) {
							$field['post_val'] = $post_val;
						}
						envoyconnect_get_field( $field );
					}
				?>
				</ul>
			</fieldset>
		</div></div>
	</div>
<?php
}


/**
 * Ensures the form correctly assigns authorship to the target user.
 *
 * @since 1.0.0
 *
 * @param This function accepts no parameters.
 *
 * @return array.
 */
 
function envoyconnect_get_action_author( $args = null ) {
	
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
	
	// SET THE TARGET USER AS THE AUTHOR
	if ( empty( $fvalue ) ) {
		
		if ( isset( $_GET['uid'] ) ) {
			$fvalue = $_GET['uid'];
		} else if ( isset( $_POST['uid'] ) ) {
			$fvalue = $_POST['uid'];
		} else {
			global $post;
			$fvalue = $post->post_author;
		}
		
	}
	echo '<input type="hidden" name="_pp_post[_pp_author]" value="' . $fvalue . '" />';
}

/**
 * Ensures the form correctly logs the actual author.
 *
 * @since 1.0.0
 *
 * @param This function accepts no parameters.
 *
 * @return array.
 */

function envoyconnect_get_action_agent( $args = null ) {

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
	
	global $current_user;
	echo '<input type="hidden" name="_pp_post[_pp_agent]" value="' . $current_user->ID . '" />';
}

/**
 * Logs the agent and date/time of the event for investigation.
 *
 * @since 1.0.0
 *
 * @param This function accepts no parameters.
 *
 * @return array.
 */

function envoyconnect_get_action_log( $args = null ) {

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
	
	if ( !empty( $fvalue ) ) {
		$last_user = end( $fvalue );
		$last_user_data = get_userdata( $last_user['id'] );
		echo '<p>' . sprintf ( __( 'Last edited by: %1$s %2$s on %3$s ', 'envoyconnect' ), $last_user_data->first_name, $last_user_data->last_name, date( 'F j, Y @ H:i:s', $last_user['date'] ) ) . '</p>';
	}
}


/**
 * Loads default interaction meta for processing.
 *
 * @since 1.0.0
 *
 * @param This function accepts no parameters.
 *
 * @return array. An array of default values for interaction data.
 */

function envoyconnect_user_interaction_meta() {
	
	return apply_filters( 'envoyconnect_user_interaction_meta', array(
						
		array( 'meta' => array( 
								'source' => 'envoyconnect', 
								'meta_key' => '_pp_interaction_type', 
								'name' => __( 'Type of Interaction', 'envoyconnect' ), 
								'help' => '', 
								'options' => array( 
													'field_type' => 'select',
													'req' => false, 
													'public' => false, 
													'choices' => envoyconnect_user_interaction_types()
								) 
		) ),
		
		array( 'meta' => array( 
								'source' => 'envoyconnect', 
								'meta_key' => '_pp_action_status', 
								'name' => __( 'Current Status', 'envoyconnect' ), 
								'help' => '', 
								'options' => array( 
													'field_type' => 'select',
													'req' => false, 
													'public' => false, 
													'choices' => envoyconnect_action_status()
								) 
		) ),
		
	));

}

/**
 * Loads default interaction types.
 *
 * @since 1.0.0
 *
 * @param This function accepts no parameters.
 *
 * @return array. An array of interaction types.
 */
function envoyconnect_user_interaction_types() {

	return array_unique( 
				apply_filters( 'envoyconnect_user_interaction_types', array( 
														'note' => __( 'Note', 'envoyconnect' ),
														'email' => __( 'E-mail Conversation', 'envoyconnect' ),
														'phone' => __( 'Telephone Conversation', 'envoyconnect' ),
														'person' => __( 'In-Person Conversation', 'envoyconnect' ) 
	) ) );
	
}


/**
 * Loads default log meta for processing.
 *
 * @since 1.0.0
 *
 * @param This function accepts no parameters.
 *
 * @return array. An array of default values for log data.
 */

function envoyconnect_user_log_meta() {
	
	return apply_filters( 'envoyconnect_user_log_meta', array(
						
		array( 'meta' => array( 
								'source' => 'envoyconnect', 
								'meta_key' => '_pp_log_type', 
								'name' => __( 'Type of log', 'envoyconnect' ), 
								'help' => '', 
								'options' => array( 
													'field_type' => 'select',
													'req' => false, 
													'public' => false, 
													'choices' => envoyconnect_user_log_types()
								) 
		) ),
		
		array( 'meta' => array( 
								'source' => 'envoyconnect', 
								'meta_key' => '_pp_action_status', 
								'name' => __( 'Current Status', 'envoyconnect' ), 
								'help' => '', 
								'options' => array( 
													'field_type' => 'select',
													'req' => false, 
													'public' => false, 
													'choices' => envoyconnect_action_status()
								) 
		) ),
		
	));

}

/**
 * Loads default log types.
 *
 * @since 1.0.0
 *
 * @param This function accepts no parameters.
 *
 * @return array. An array of log types.
 */
function envoyconnect_user_log_types() {

	return array_unique( 
				apply_filters( 'envoyconnect_user_log_types', array( 
														'admin_registration' => __( 'Admin Registration', 'envoyconnect' ),
														'user_registration' => __( 'User Registration', 'envoyconnect' ),
														'import' => __( 'Import', 'envoyconnect' ), 
														'contact_form' => __( 'Contact Form', 'envoyconnect' ), 
														'content_submission' => __( 'Content Submission', 'envoyconnect' ) 
	) ) );
	
}


/**
 * Sets additional icons for user histories.
 *
 * @since 1.0.0
 * *
 * @return array. An array of classes.
 */
function pp_log_ai_class_filter( $class, $act ) {

	if ( 'pp_log' == $act->post_type ) {
		$class[] = get_post_meta( $act->ID, '_pp_log_type', true );
		$class[] = get_post_meta( $act->ID, '_pp_action_status', true );
	}
	return $class;

}


/**
 * Loads default status codes.
 *
 * @since 1.0.0
 *
 * @param This function accepts no parameters.
 *
 * @return array. An array of log status codes.
 */
function envoyconnect_action_status() {

	return array_unique( 
				apply_filters( 'envoyconnect_action_status', array( 
														'archived' => __( 'Archived', 'envoyconnect' ),
	) ) );
	
}


/**
 * Push the user actions meta to EnvoyConnect if this is a processed item.
 *
 * @since 1.0.0
 *
 * @param arr. The EnvoyConnect user actions meta array.
 *
 * @return arr. The filtered EnvoyConnect user actions meta array.
 */
function envoyconnect_push_user_actions_meta( $envoyconnect_get_user_actions_meta ) {
	global $post;
	if ( 'pp_log' == $post->post_type ) {
		$envoyconnect_push_meta = envoyconnect_user_log_meta();
	} else if ( 'pp_interaction' == $post->post_type ) {
		$envoyconnect_push_meta = envoyconnect_user_interaction_meta();
	}
	if ( !empty( $envoyconnect_push_meta ) ) {
		foreach ( $envoyconnect_push_meta as $key => $val ) {
			array_push( $envoyconnect_get_user_actions_meta, $val );
		}
		
	}
	return $envoyconnect_get_user_actions_meta;
}


/**
 * Generic function for saving Post Meta for Actions.
 *
 * @since 1.0.0
 *
 * @param This function accepts no parameters.
 *
 * @return This function does not return any data.
 */
function envoyconnect_save_action_meta( $args = null ) {
	
	// SET THE DEFAULTS TO BE OVERRIDDEN AS DESIRED
	$defaults = array( 
					'override' => false,
					'verified' => false,  
					'post_data' => $_POST, 
				);
	
	// PARSE THE INCOMING ARGS
	$args = wp_parse_args( $args, $defaults );

	// EXTRACT THE VARIABLES
	extract( $args, EXTR_SKIP );
	
	// SET THE LOCAL VARIABLE
	if ( isset( $post_data['post_ID'] ) )
		$id = $post_data['post_ID'];
		
	if ( !isset( $id ) )
		return false;
	
	if ( isset( $post_data['post_type'] ) )
		$post_type = $post_data['post_type'];
	
	// MAKE SURE THIS CAME FROM A VALID PAGE
	// YOU MUST OVERRIDE THIS MANUALLY AND ONLY IF A NONCE HAS BEEN PREVIOUSLY VERIFIED
	if ( false != $override ) {
	} else {
		if ( 
			!isset( $post_type ) || 
			!isset( $post_data['envoyconnect-'.$post_type.'-nonce'] ) || 
			!wp_verify_nonce( $post_data['envoyconnect-'.$post_type.'-nonce'], 'envoyconnect-'.$post_type.'-meta' ) 
		)
			return false;
	}
	
	// MAKE SURE THE USER HAS THE CORRECT PRIVILEGE
	// YOU MUST OVERRIDE THIS MANUALLY AND ONLY IF A USER HAS BEEN PREVIOUSLY VERIFIED
	if ( false != $verified ) {
	} else {
		// MAKE SURE THEY HAVE ACCESS TO EDIT
		if ( 'page' == $post_type ) {
			if ( !current_user_can( 'edit_page', $id ))
			return $id;
		} else {
			if ( !current_user_can( 'edit_post', $id ))
			return $id;
		}
	}
	
	// ALLOW PLUGINS TO MODIFY THE POST DATA
	if ( isset( $post_data['_pp_post'] ) ) {
		$envoyconnect_data = apply_filters( 'envoyconnect_modify_action_meta', $post_data['_pp_post'], $id, $post_type, $post_data );
		
		// LOOP THROUGH THE POSTDATA AND SAVE THE VALUES
		foreach ( $envoyconnect_data as $key => $value ) {
			
		    if ( get_post_meta( $id, $key ) == '' ) :
		    	add_post_meta( $id, $key, $value, true );
		    elseif ( $value != get_post_meta( $id, $key, true ) ) :
		    	update_post_meta( $id, $key, $value );
		    elseif ( $value == '' ) :
		    	delete_post_meta( $id, $key, get_post_meta( $id, $key, true ) );
		    endif;
		    	    		
		}
	}
	
	do_action( 'envoyconnect_process_action_meta', $id, $post_type );
	
}


/**
 * Swaps the originating author for the targeted user when inserting actions into the db and updates the log.
 *
 * @since 1.0.2
 *
 * @param arr $data Required. An array containing the post data.
 * @param arr $postarr Required. An array containing the submitted data on $_POST.
 *
 * @return arr an array of default actions.
 */

function envoyconnect_log_user_action_meta( $data, $postarr ) {

	// FIRST, MAKE SURE THAT A SUBMISSION HAS OCCURRED
	if ( isset( $postarr['_pp_post']['_pp_author'] ) ) { 
	
		$envoyconnect_user_actions = envoyconnect_get_user_actions();
		
		// PROCESS THE REGISTERED ACTIONS TO RETRIEVE THE WP POST TYPE
		$envoyconnect_user_action_types = array();
		foreach ( $envoyconnect_user_actions as $key => $value ) {
			$envoyconnect_user_action_types[] = $value['type'];
		}
		
		// CHECK TO SEE IF THIS IS A REGISTERED ACTION
		if ( in_array_r( $data['post_type'], $envoyconnect_user_action_types ) ) {
		
			// IF THE POSTED _pp_author CONVERSION !MATCHES THE POST_AUTHOR UPDATE IT
			if ( $data['post_author'] != $postarr['_pp_post']['_pp_author'] ) {
				
				$data['post_author'] = $postarr['_pp_post']['_pp_author'];
				
			}
			
			// UPDATE THE LOG
			if ( isset( $postarr['_pp_post']['_pp_agent'] ) ) {

				$cur_user_arr = array( 'id' => $postarr['_pp_post']['_pp_agent'], 'date' => time() );
				$cur_log = get_post_meta( $postarr['ID'], '_pp_log', true );
				
				if ( empty( $cur_log ) )
					$cur_log = array();
			
				array_push( $cur_log, $cur_user_arr );
				update_post_meta( $postarr['ID'], '_pp_log', $cur_log );
			
			}
		
		}
		
	}
	
	return $data;
}

?>