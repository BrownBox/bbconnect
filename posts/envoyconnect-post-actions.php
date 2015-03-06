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

function envoyconnect_post_actions() {
	
	return apply_filters( 'envoyconnect_post_actions', array( 
			
	) );

}

/**
 * Retrieves the post actions before registering them as a CPT.
 *
 * @since 1.0.2
 *
 * @param This function accepts no parameters.
 *
 * @return null If you don't see your actions, it didn't work. :)
 */

function envoyconnect_get_init_post_actions() {
	return apply_filters( 'envoyconnect_get_init_post_actions', envoyconnect_post_actions() ); 
}


/**
 * Retrieves the post actions and opens up a filter.
 *
 * @since 1.0.2
 *
 * @param This function accepts no parameters.
 *
 * @return null If you don't see your actions, it didn't work. :)
 */

function envoyconnect_get_post_actions() {
	return apply_filters( 'envoyconnect_get_post_actions', envoyconnect_post_actions() ); 
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
function envoyconnect_init_post_actions() {
  
  	$envoyconnect_post_actions = envoyconnect_get_init_post_actions();
  	
	foreach ( $envoyconnect_post_actions as $action ) {
	
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
	  	$args = null;
	  	if ( isset( $action['args'] ) ) {
	  		$args = $action['args'];
	  	}	
	  		
		$args = envoyconnect_init_post_actions_args( $labels, $cptype, $args );
		/*
		//$args['label'] = null;
		$args['labels'] = $labels;
		$args['description'] = '';
		if ( isset( $action['options']['public'] ) ) {
			$args['public'] = $action['options']['public'];			
		} else {
			$args['public'] = false;			
		}
		//$args['public'] = false;
		$args['publicly_queryable'] = false;
		$args['exclude_from_search'] = true;
		$args['show_ui'] = true; 
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
		$args['map_meta_cap'] = false;
		$args['hierarchical'] = false;
		$args['register_meta_box_cb'] = 'envoyconnect_post_actions_meta_box_cb';
		//$args['taxonomies'] = null;
		//$args['permalink_epmask'] = EP_PERMALINK;
		$args['has_archive'] = false;
		$args['rewrite'] = false; // array( 'slug' => $cploc . '/'.( $cpplural ) );
		$args['query_var'] = true;
		$args['can_export'] = true;
		$args['show_in_nav_menus'] = true;
		if ( isset( $action['options']['menu'] ) ) {
			$args['show_in_menu'] = $action['options']['menu'];			
		} else {
			$args['show_in_menu'] = false;			
		}	
		$args['supports'] = array( 'title', 'editor' );
		
		// THESE ARE THE WP OPTIONS
		// 'title','editor','excerpt','trackbacks','comments','thumbnail','author'
	    */
		register_post_type( $cptype, $args );
	  
	}

}


function envoyconnect_init_post_actions_args( $labels, $cptype, $args = null ) {
	
	// SET THE DEFAULTS TO BE OVERRIDDEN AS DESIRED
	$defaults = array( 
						'labels' => $labels, 
						'description' => '', 
						'public' => false, 
						'publicly_queryable' => false, 
						'exclude_from_search' => true, 
						'show_ui' => true, 
						'menu_position' => null, 
						'menu_icon' => null, 
						'capability_type' => 'post', 
						'capabilities' => array( 
												'publish_posts' => 'manage_'.$cptype,
												'edit_posts' => 'manage_'.$cptype,
												'edit_others_posts' => 'manage_'.$cptype,
												'delete_posts' => 'manage_'.$cptype,
												'delete_others_posts' => 'manage_'.$cptype,
												'read_private_posts' => 'manage_'.$cptype,
												'edit_post' => 'manage_'.$cptype,
												'delete_post' => 'manage_'.$cptype,
												'read_post' => 'manage_'.$cptype
						), 
						'map_meta_cap' => false, 
						'hierarchical' => false, 
						'register_meta_box_cb' => 'envoyconnect_post_actions_meta_box_cb', 
						'taxonomies' => array(), 
						'permalink_epmask' => EP_PERMALINK, 
						'has_archive' => false, 
						'rewrite' => false, 
						'query_var' => true, 
						'can_export' => true, 
						'show_in_nav_menus' => false, 
						'show_in_menu' => false, 
						'supports' => array( 'title', 'editor' ), 
				);
	
	// PARSE THE INCOMING ARGS
	$args = wp_parse_args( $args, $defaults );

	// RETURN THE ARGS
	return $args;
	
}


function envoyconnect_post_actions_nonce_field() {
	global $post, $ppnonce;
	if ( false == $ppnonce ) {
		wp_nonce_field( 'envoyconnect-' . $post->post_type.'-meta', 'envoyconnect-' . $post->post_type.'-nonce' );
		$ppnonce = true;
	}
}

?>