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
 
function envoyconnect_post_actions_meta() {
	
	return apply_filters( 'envoyconnect_post_actions_meta', array(
		
	));

}


/**
 * Retrieves the post actions meta and opens up a filter.
 *
 * @since 1.0.2
 *
 * @param This function accepts no parameters.
 *
 * @return null If you don't see your actions, it didn't work. :)
 */

function envoyconnect_get_post_actions_meta() {
	return apply_filters( 'envoyconnect_get_post_actions_meta', envoyconnect_post_actions_meta() ); 
}



/**
 * Callback function for aggregating all of the meta boxes for Post Actions.
 *
 * @since 1.0.2
 *
 * @param This function accepts no parameters.
 *
 * @return null This function does not return anything.
 */
 
function envoyconnect_post_actions_meta_box_cb() {
	
	$envoyconnect_post_actions = envoyconnect_get_post_actions();
	
	if ( !empty( $envoyconnect_post_actions ) ) {
		foreach ( $envoyconnect_post_actions as $key => $value ) {
			add_meta_box( $value['type'] . '-context', __( 'Action Meta', 'envoyconnect' ), 'envoyconnect_post_actions_meta_box', $value['type'], 'normal', 'default' );
			do_action( 'envoyconnect_post_actions_meta_add', $value );
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
 
function envoyconnect_post_actions_meta_box( $post ) {
	envoyconnect_post_actions_nonce_field();
	envoyconnect_post_actions_meta_fields( array( 'post_id' => $post->ID, 'fields' => envoyconnect_get_post_actions_meta() ) );
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
 
function envoyconnect_post_actions_meta_fields( $args = null ) {

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
				<ul class="envoyconnect-list">
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


function envoyconnect_attachment_save( $post, $attachment ) {
	envoyconnect_save_action_meta( array( 'post_data' => $post ) );
	return $post;
}


?>