<?php

// find'n replace Saved searches & Saved searche (as cpt). *Remember to preseve case
// remember to include in functions.php

function cpt_savedsearch() {
	$labels = array(
		'name'               => _x( 'Saved searches', 'post type general name', 'tn_' ),
		'singular_name'      => _x( 'Saved search', 'post type singular name', 'tn_' ),
		'add_new'            => _x( 'Add New', 'Saved searche', 'tn_' ),
		'add_new_item'       => __( 'Add New Saved searche', 'tn_' ),
		'edit_item'          => __( 'Edit Saved search', 'tn_' ),
		'new_item'           => __( 'New Saved search', 'tn_' ),
		'all_items'          => __( 'All Saved searches', 'tn_' ),
		'view_item'          => __( 'View Saved search', 'tn_' ),
		'search_items'       => __( 'Search Saved searches', 'tn_' ),
		'not_found'          => __( 'No Saved searches found', 'tn_' ),
		'not_found_in_trash' => __( 'No Saved searches found in the Trash', 'tn_' ),
		'parent_item_colon'  => '',
		'menu_name'          => 'Saved searches'
	);

	$args = array(
		'labels'        => $labels,
		'description'   => 'Holds our Saved search posts',
		'public'        => true,
		'menu_position' => 20,
	 	'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'comments', 'page-attributes'),
		'has_archive'   => true,
		'hierarchical' 	=> true
	);
	register_post_type( 'savedsearch', $args );
}
add_action( 'init', 'cpt_savedsearch' );

// Set Messages
function cpt_savedsearch_messages( $messages ) {
//http://codex.wordpress.org/Function_Reference/register_post_type

  global $post, $post_ID;

  $messages['savedsearch'] = array(
	0 => '', // Unused. Messages start at index 1.
	1 => sprintf( __('Saved search Post updated.', 'your_text_domain'), esc_url( get_permalink($post_ID) ) ),
	2 => __('Saved searche updated.', 'your_text_domain'),
	3 => __('Saved search deleted.', 'your_text_domain'),
	4 => __('Saved search Post updated.', 'your_text_domain'),
	/* translators: %s: date and time of the revision */
	5 => isset($_GET['revision']) ? sprintf( __('Saved search Post restored to revision from %s', 'your_text_domain'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
	6 => sprintf( __('Saved search Post published. <a href="%s">View Saved search Post</a>', 'your_text_domain'), esc_url( get_permalink($post_ID) ) ),
	7 => __('Saved search Post saved.', 'your_text_domain'),
	8 => sprintf( __('Saved search Post submitted. <a target="_blank" href="%s">Preview Saved search Post</a>', 'your_text_domain'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
	9 => sprintf( __('Saved search Post scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Saved search Post</a>', 'your_text_domain'),
	  // translators: Publish box date format, see http://php.net/date
	  date_i18n( __( 'M j, Y @ G:i', 'tn_' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
	10 => sprintf( __('Saved search Post draft updated. <a target="_blank" href="%s">Preview Saved search Post</a>', 'your_text_domain'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
  );

  return $messages;
}
add_filter( 'post_updated_messages', 'cpt_savedsearch_messages' );

?>