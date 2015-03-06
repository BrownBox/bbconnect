<?php

/**
 * Simple array to define the actions that directly affect users.
 *
 * @since 1.0.2
 *
 * @param This function accepts no parameters.
 *
 * @return arr. Standardized array of action data.
 */

function envoyconnect_user_actions() {
	
	return apply_filters( 'envoyconnect_user_actions', array( 
					
			'pp_log' => array( 
										'source' => 'envoyconnect', 
										'type' => 'pp_log', 
										'single' => __( 'Log', 'envoyconnect' ),  
										'plural' => __( 'Logs', 'envoyconnect' ), 
										'class' => 'origin',
										'options' => array( 
															'admin' => true, 
															'user' => false, 
															'public' => false, 
															'reports' => true, 
															'choices' => false
										)
			), 
			
			'pp_interaction' => array( 
										'source' => 'envoyconnect', 
										'type' => 'pp_interaction', 
										'single' => __( 'Interaction', 'envoyconnect' ), 
										'plural' => __( 'Interactions', 'envoyconnect' ), 
										'class' => 'origin',
										'options' => array( 
															'admin' => true, 
															'user' => false, 
															'public' => false, 
															'reports' => true,
															'choices' => envoyconnect_user_interaction_types()
										)
			), 
			
	) );

}


/**
 * Retrieves the user actions before registering them as a CPT.
 *
 * @since 1.0.2
 *
 * @param This function accepts no parameters.
 *
 * @return null If you don't see your actions, it didn't work. :)
 */

function envoyconnect_get_init_user_actions() {
	return apply_filters( 'envoyconnect_get_init_user_actions', envoyconnect_user_actions() ); 
}

/**
 * Retrieves the user actions for general use within the system.
 *
 * @since 1.0.2
 *
 * @param This function accepts no parameters.
 *
 * @return null If you don't see your actions, it didn't work. :)
 */

function envoyconnect_get_user_actions() {
	return apply_filters( 'envoyconnect_get_user_actions', envoyconnect_get_init_user_actions() ); 
}


/**
 * Removes particular post types from the user-admin panel for user actions.
 *
 * @since 1.0.2
 *
 * @param This function accepts no parameters but anticipates the slug of the desired post type to exclude.
 *
 * @return null If you see your actions, it didn't work. :)
 */

function envoyconnect_exclude_user_actions() {
	return apply_filters( 'envoyconnect_exclude_user_actions', array() ); 
}




/**
 * Instantiates a WordPress Custom Post Type (CPT) as an Action on INIT.
 *
 * @since 1.0.2
 *
 * @param This function accepts no parameters.
 *
 * @return null If you don't see your actions, it didn't work. :)
 */
function envoyconnect_init_user_actions() {
  
  	$envoyconnect_user_actions = envoyconnect_get_init_user_actions();
  	
	foreach ( $envoyconnect_user_actions as $action ) {
	
		if ( isset( $action['exception'] ) &&  false != $action['exception'] )
			continue;
  
		$cptype = $action['type'];
		$cpsingle = $action['single'];
		$cpplural = $action['plural'];
	  
		$labels = array();
		$labels['name'] = $cpplural;
		$labels['singular_name'] = $cpsingle;
		$labels['add_new'] = 'New '.$cpsingle;
		$labels['add_new_item'] = 'Add New '.$cpsingle;
		$labels['edit_item'] = 'Edit '.$cpsingle;
		$labels['new_item'] = 'New '.$cpsingle;
		$labels['view_item'] = 'View '.$cpplural;
		$labels['search_items'] = 'Search '.$cpplural;
		$labels['not_found'] = 'No '.$cpplural.' found';
		$labels['not_found_in_trash'] = 'No '.$cpplural.' found in Trash'; 
		$labels['parent_item_colon'] = '';
	  	
	  	// THESE WILL NEED TO BE OPTIONS
	  	// FOR NOW, WE'RE SETTING THEM
		$args = array();
		//$args['label'] = null;
		$args['labels'] = $labels;
		$args['description'] = '';
		$args['public'] = false;
		$args['publicly_queryable'] = false;
		$args['exclude_from_search'] = true;
		$args['show_ui'] = true; 
		$args['show_in_menu'] = false;
		$args['menu_position'] = null;
		$args['menu_icon'] = null;
		$args['capability_type'] = 'post';
		$args['capabilities'] = array( 
				'publish_posts' 		=> 'manage_'.$cptype,
				'edit_posts' 			=> 'manage_'.$cptype,
				'edit_others_posts' 	=> 'manage_'.$cptype,
				'delete_posts' 			=> 'manage_'.$cptype,
				'delete_others_posts'	=> 'manage_'.$cptype,
				'read_private_posts'	=> 'manage_'.$cptype,
				'edit_post' 			=> 'manage_'.$cptype,
				'delete_post' 			=> 'manage_'.$cptype,
				'read_post' 			=> 'manage_'.$cptype
			);
		$args['hierarchical'] = false;
		$args['register_meta_box_cb'] = 'envoyconnect_user_actions_meta_box_cb';
		$args['has_archive'] = false;
		$args['rewrite'] = false; // array( 'slug' => $cploc . '/'.( $cpplural ) );
		$args['query_var'] = true;
		$args['can_export'] = true;
		$args['show_in_nav_menus'] = false;		
		$args['supports'] = array( 'title', 'editor' );
		
		// THESE ARE THE WP OPTIONS
		// 'title','editor','excerpt','trackbacks','comments','thumbnail','author'
	    
		register_post_type( $cptype, $args );
	  
	}

}


/**
 * Generic nonce field for all EnvoyConnect data.
 *
 * @since 1.0.2
 * @param arr. The EnvoyConnect User Actions Array.
 * @return arr. The filtered Actions Array.
 */

function envoyconnect_user_actions_nonce_field() {
	global $post;
	wp_nonce_field( 'envoyconnect-' . $post->post_type.'-meta', 'envoyconnect-' . $post->post_type.'-nonce' );
}


/**
 * Pushes the user actions to EnvoyConnect.
 *
 * @since 1.0.2
 * @param arr. The EnvoyConnect User Actions Array.
 * @return arr. The filtered Actions Array.
 */

function envoyconnect_push_user_actions( $envoyconnect_get_user_actions ) {
	
	$envoyconnect_post_types = get_option( '_envoyconnect_post_types' );
	
	if ( empty( $envoyconnect_post_types ) )
		return $envoyconnect_get_user_actions;
		
	$envoyconnect_get_user_actions = $envoyconnect_post_types;
	
	return $envoyconnect_get_user_actions;
}

/**
 * Loads the correct fields for the selected actions under Profile > Actions.
 *
 * @since 1.0.0
 * @param $data str. The type of action being requested.
 * @return html. The fields.
 */

function envoyconnect_get_post_to_edit() {
	
	if ( ! wp_verify_nonce( $_POST['envoyconnect_admin_nonce'], 'envoyconnect-admin-nonce' ) )
		die (  __( 'Terribly sorry.', 'envoyconnect' ) );
	
	if ( isset( $_POST['data'] ) ) {
	
		$cid = $_POST['cid'];
		$type = $_POST['type'];
		global $post;
		
		// WE'RE EDITING AN EXISTING FILE
		if ( false !== strpos( $_POST['data'], 'edit-' ) ) {
			$post_id = (int) substr( $_POST['data'], 5 );
			$post = get_post( $post_id );
			$post_author = $post->post_author;
			$post_type = $post->post_type;
			$action = 'edit';
			$post_val = array();
		} else {
			if ( 'user' == $type ) {
				$envoyconnect_actions = envoyconnect_get_user_actions();
			} else {
				$envoyconnect_actions = envoyconnect_get_post_actions();
			}
			$post_type = false;
			$post_val = array();
			$post_type = $_POST['data'];
			/*
			foreach ( $envoyconnect_user_actions as $key => $value ) {
			*/
				/*
				if ( isset( $value['options']['choices'] ) && false != $value['options']['choices'] ) {
					foreach( $value['options']['choices'] as $ckey => $cval ) {
						if ( $ckey === $_POST['data']['rel'] ) {
							$post_type = $value['type'];
						}
					}
				} else {
				*/
					/*
					if ( $value['type'] === $_POST['data'] )
						$post_type = $value['type'];
					*/
				/*
				}
				*/ 
			/*
			}
			*/
			if ( false == $post_type )
				return 'error';
			
			$post = get_default_post_to_edit( $post_type, true );
			$post_author = $_POST['uid'];
			$action = $_POST['actung'];
			
		}
		
		// SET THE NONCE
		if ( 'user' == $type ) {
			envoyconnect_user_actions_nonce_field();
		} else {
			envoyconnect_post_actions_nonce_field();
		}
		
		$post_fields = array( 
			array( 'meta' => array( 
									'source' => 'wpr', 
									'meta_key' => 'post_title', 
									'name' => __( 'Title', 'envoyconnect' ), 
									'help' =>'', 
									'options' => array( 
														'field_type' => 'text',
														'req' => false, 
														'public' => false, 
														'choices' => false 
									) 
			) ),
			
			array( 'meta' => array( 
									'source' => 'wpr', 
									'meta_key' => 'post_date', 
									'name' => __( 'Date', 'envoyconnect' ), 
									'help' =>'', 
									'options' => array( 
														'field_type' => 'date',
														'req' => false, 
														'public' => false, 
														'choices' => false 
									) 
			) ),
		);
		foreach ( $post_fields as $field ) {
			
			$meta_key = $field['meta']['meta_key'];
			$field['type'] = 'post';
			$field['action'] = $action;
			$field['id'] = $post->ID;
			$field['swap_name'] = $meta_key;
			if ( isset( $post->{$meta_key} ) ) {
				$field['post_val'] = $post->{$meta_key};
			}
			echo '<p><ul style="display: block; float: none;">';
			envoyconnect_get_field( $field );
			echo '</ul></p>';
		}
		
		if ( 'bulk-edit' == $action ) {
			echo '<ul><li class="meta-item"><span class="envoyconnect-label">';
			echo '<a class="rui off" title="'.$cid.'bulk-edit">Enable Text</a>';
			echo '</span><span class="envoyconnect-field">';
		}
		
		echo '<div style="width: 90%;padding: .3em;margin: .2em 0;">&nbsp;</div>';
		//echo '<p>'. __( 'Title', 'envoyconnect' ) .'<br /><input type="text" name="post_title" class="regular-text" value="'.$post->post_title.'" /></p>';
		//echo '<p>'. __( 'Date', 'envoyconnect' ) .'<br /><input type="text" class="envoyconnect-date" name="post_date" class="regular-text" value="'.$post->post_date.'" /></p>';
		if (preg_match('/Firefox/i', $_SERVER['HTTP_USER_AGENT'])) { 
		wp_editor( stripslashes( $post->post_content ), $cid, array( 'tinymce' => false, 'textarea_name' => 'post_content', 'teeny' => true, 'quicktags' => true ) );
		} else {
		wp_editor( stripslashes( $post->post_content ), $cid, array( 'tinymce' => true, 'textarea_name' => 'post_content', 'teeny' => false, 'quicktags' => true ) );
		}
		
		if ( 'bulk-edit' == $action ) {
			echo '</span></li></ul>';
		}
		
		// SET THE META
		if ( 'user' == $type ) {
			envoyconnect_user_actions_meta_fields( array( 'post_id' => $post->ID, 'fields' => envoyconnect_get_user_actions_meta(), 'action' => $action, 'post_val' => $post_val ) );
		} else {
			envoyconnect_post_actions_meta_fields( array( 'post_id' => $post->ID, 'fields' => envoyconnect_get_post_actions_meta(), 'action' => $action, 'post_val' => $post_val ) );
		}
		
		?>
		<input type="hidden" name="post_ID" value="<?php echo $post->ID; ?>" />
		<input type="hidden" name="post_status" value="publish" />
		<input type="hidden" name="post_author" value="<?php echo $post_author; ?>" />
		<input type="hidden" name="post_type" value="<?php echo $post_type; ?>" />
		<?php
		$inline_button = apply_filters( 'envoyconnect_inline_do_action_button', array(
				'<input type="submit" class="envoyconnect-actions-save button-primary '.$type.'" name="save" value="'.__( 'Save', 'envoyconnect' ).'" />', 
			), $post_type, $type, $action  
		);
		echo '<div class="tright">';
		echo implode( ' ', $inline_button );
		echo '</div>';
				
	} else {
		echo 'error';
	}
	if ( '3.9' <= get_bloginfo( 'version' ) ) {
		_WP_Editors::enqueue_scripts();
		//print_footer_scripts();
		_WP_Editors::editor_js();
		echo '<script src="'.admin_url( 'js/editor.js' ).'" />';
	}
	die();
	
}


/**
 * Saves for the selected actions under Profile > Actions.
 *
 * @since 1.0.0
 * @param $data arr. The POST data.
 * @return html. The content and miscellaneous followups.
 */

function envoyconnect_save_new_post() {
	
	if ( ! wp_verify_nonce( $_POST['envoyconnect_admin_nonce'], 'envoyconnect-admin-nonce' ) )
		die (  __( 'Terribly sorry.', 'envoyconnect' ) );

	if ( isset( $_POST['data'] ) ) {
		
		parse_str( $_POST['data'], $postarr );
		$sid = $_POST['sid'];
		$type = $_POST['type'];
		
		if ( isset( $postarr['post_ID'] ) ) {
			$postarr['ID'] = $postarr['post_ID'];
			//unset( $postarr['post_ID'] );
		}
					
		$pid = wp_insert_post( $postarr );
		envoyconnect_save_action_meta( array( 'post_data' => $postarr, 'override' => true ) );		
		$act = get_post( $pid );
		$class = apply_filters( 'envoyconnect_save_new_class', array(), $act );
		envoyconnect_profile_action_item( array( 
											'act' => $act, 
											'type' => $type, 
											'new' => true, 
											'class' => $class, 
											'ok_edit' => true, 
											'action' => 'edit', 
											'envoyconnect_cap' => 'admin'
		) );
		?>
		<script type="text/javascript">
		jQuery(document).ready(function () {
			// IF THE ELEMENT EXISTS, REMOVE IT & THE PARENT
			if ( jQuery('#post-<?php echo $pid; ?>').length != 0 ) {
				jQuery('#post-<?php echo $pid; ?>').parent('li').remove();
			}
			
			// CLONE THE NEW ELEMENT
			jQuery('.new-post').clone().prependTo('#<?php echo $sid; ?> .actions-history-list');
			
			// UPDATE AND REMOVE THE NEW-POST IDENTIFIER
			jQuery('#<?php echo $sid; ?> .actions-history-list .new-post').each(function(){
				jQuery(this).find('div[title="post-<?php echo $pid; ?>"]').attr('id','post-<?php echo $pid; ?>').attr('title','');
				jQuery(this).removeClass('new-post').fadeIn('fast');
			});
			
			// UPGRADE THE ORIGINAL
			var newparent = jQuery('.new-post').parent('.envoyconnect-viewer').attr('id');
			jQuery('#'+newparent+' .new-post').each(function(){
				jQuery(this).find('div[title="post-<?php echo $pid; ?>"]').attr('id','new-post-<?php echo $pid; ?>').attr('title','');
				jQuery('#new-post-<?php echo $pid; ?>').prependTo('#'+newparent).fadeIn('fast');
				jQuery(this).remove();
			});
			
		});
		</script>
	<?php
	} else {
		echo 'error';
	}

	die();

}

/**
 * Excludes specific actions from PauContent consideration.
 *
 * @since 1.0.2
 *
 * @param arr. The EnvoyConnect User Actions Array.
 *
 * @return arr. The filtered Actions Array.
 */

function envoyconnect_exclude_accepted_post_types( $paucontent_accepted_post_types ) {
	
	$envoyconnect_user_actions = envoyconnect_user_actions();
	
	foreach ( $envoyconnect_user_actions as $key => $value )
		array_push( $paucontent_accepted_post_types, $value['type'] );

	return $paucontent_accepted_post_types;
}

function envoyconnect_exclude_restricted_post_types( $paucontent_restricted_post_types ) {
	
	$envoyconnect_user_actions = envoyconnect_user_actions();
	
	foreach ( $envoyconnect_user_actions as $key => $value )
		array_push( $paucontent_restricted_post_types, $value['type'] );

	return $paucontent_restricted_post_types;
}


function envoyconnect_modal_action(){

	switch ( $_GET['action'] ) {
	
		case 'test' :
			global $pagenow;
    		echo 'howdy, user# ' . $_GET['ID'] . ' ' . $pagenow;
    		//envoyconnect_actions_editor( array( 'user_id' => $_GET['ID'], 'envoyconnect_cap' => 'admin', 'action' => 'edit', 'embed' => true, 'embed_id' => 'new-action-mini' ) );
    		
    	break;
    	
    	default :
    		echo 'hello world!';
    		wp_editor( '', 'post_content', array( 'tinymce' => true, 'textarea_name' => 'post_content', 'teeny' => false, 'quicktags' => true ) );
    		
    	break;
    	
    }
    
}

function envoyconnect_add_nodes( $wp_admin_bar ) {
	global $current_user;
	$args = array(
	      'id' => 'new-action', 
	      'parent' => 'new-content', 
	      'title' => __( 'EnvoyConnect Action', 'envoyconnect' ), 
	      'href' => admin_url( '/users.php?page=envoyconnect_modal_action&amp;action=&amp;ID='.$current_user->ID.'&amp;TB_iframe=true&amp;height=300&amp;width=900' ), 
	      'meta' => array( 'html' => "<script>jQuery('#wp-admin-bar-new-action a').addClass('thickbox');</script>" )
	);

	$wp_admin_bar->add_node($args);
	
}

// REMOVE THE WORDPRESS ADMIN MENU FOR NOTES AND SUCH
function envoyconnect_action_minified() {
	global $pagenow;
	
	if ( is_admin() ) {
		if ( isset( $_GET['page'] ) && 'envoyconnect_modal_action' == $_GET['page'] ) {
	    	//add_action( 'admin_head', 'envoyconnect_action_minify' );
	    }
	}
	
}

function envoyconnect_action_minify() {
	if ( isset( $_GET['ID'] ) ) {
		echo "<style>\r\n";
		echo "#adminmenuback, #adminmenuwrap, #wphead, #footer, #wpfooter, #wpadminbar { display: none; }\r\n";
		echo "div { margin: 0 !important; padding: 0 !important; max-width: 180px !important; min-width: 180px !important; }\r\n";
		echo "body.wp-admin { min-width: 180px !important; }\r\n";
		echo "#wpbody-content { width: 250px; padding: 10px !important; text-align: center; }\r\n";
		echo "</style>\r\n";
	}

}
?>
