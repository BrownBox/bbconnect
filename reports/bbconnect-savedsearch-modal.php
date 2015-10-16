<?php
// PROCESS THE AJAX REQUEST TO SAVE SEARCH
function bbconnect_save_search() {
    global $current_user;
	$last_search = get_option( '_bbconnect_' . $current_user->ID . '_current' );

	$modal_element = '<form id="save-search-form" class="save-form" enctype="multipart/form-data" action="#" method="POST" style="padding:2rem;">'."\n";
	$modal_element .= '<h1>Save Search</h1>'."\n";
	$modal_element .= '<div><label>Search Name:</label><br><input style ="width: 100%;" type="text" id="post_title" name="post_title"></div>'."\n";

	// Non-admins can only create basic saved searches for themselves
	if (!user_can(get_current_user_id(), 'manage_options')) {
		$modal_element .= '<input type="hidden" id="private" name="private" checked="checked">'."\n";
		$modal_element .= '<div><input type="hidden" id="segment" name="segment"> <label></label></div>'."\n";
		$modal_element .= '<div><input type="hidden" id="category" name="category"> <label></label></div>'."\n";
	} else {
		$modal_element .= '<div><input type="checkbox" id="private" name="private" checked="checked"> <label>Private</label></div>'."\n";
		$modal_element .= '<div><input type="checkbox" id="segment" name="segment"> <label>Segment</label></div>'."\n";
		$modal_element .= '<div><input type="checkbox" id="category" name="category"> <label>Category</label></div>'."\n";
	}
	$modal_element .= '<div><input style="height: 2.5rem; padding: 0.25rem 2rem;margin-top:2rem;" type="submit" name="search-save-go" value="Save" class="button-primary save-go" /></div>'."\n";
	$modal_element .= '</form>'."\n";
	require_once('bbconnect-savedsearch-save.php');
	echo $modal_element;
    die;
}

//create entry in the search saved custom post
function bbconnect_create_search_post(){
	// RUN A SECURITY CHECK
	if ( ! wp_verify_nonce( $_POST['bbconnect_report_nonce'], 'bbconnect-report-nonce' ) )
		die ( 'terribly sorry.' );
	global $current_user;

	if( !empty($_POST) ) {
			$post = array(
			  'post_content'   => serialize( $_POST['data']['search'] ),
			  'post_title'     =>  $_POST['data']['postTitle'],
			  'post_status'    => 'publish',
			  'post_type'      => 'savedsearch',
			  'post_author'    => $current_user->ID,
			);
			$wp_error = wp_insert_post( $post, $wp_error );
			if( !is_array( $wp_error ) ) {
				add_post_meta($wp_error, 'private', $_POST['data']['privateV']);
				add_post_meta($wp_error, 'segment', $_POST['data']['segment']);
				add_post_meta($wp_error, 'category', $_POST['data']['category']);
				if ( false === $recently_saved ) $recently_saved = array();
				set_transient( 'bbconnect_'.$current_user->ID.'_last_saved', $wp_error, 3600 );
				echo '<div class="updated update-nag"  style="width:95%; border-left: 4px solid #7ad03a;"><p>Search has been saved as <a href="/post.php?post=' . $wp_error . '&action=edit">savedsearch-' . $wp_error . '</a></p></div>'."\n";
			} else {
				echo '<div class="updated"><p>Search has not been saved'. var_dump($wp_error) . '</a></p></div>'."\n";
			}
	}
	die;
}
