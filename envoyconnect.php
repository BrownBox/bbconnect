<?php
/*
Plugin Name: EnvoyConnect
Plugin URI: http://envoyconnect.com/
Description: A CRM framework for WordPress and any industry. The prefix acronym stands for Perspectives on the Actions of your Users.
Version: 1.5.7
Author: havahula.org
Author URI: http://havahula.org/
Text Domain: envoyconnect
Domain Path: languages

EnvoyConnect is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

EnvoyConnect is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with EnvoyConnect. If not, see <http://www.gnu.org/licenses/>.
*/



/* -----------------------------------------------------------
	SETUP, OPTIONS & ACTIONS
   ----------------------------------------------------------- */

/**
 * Security: Shut it down if the plugin is called directly
 *
 * @since 1.0.0
 */
if ( !function_exists( 'add_action' ) ) {
	echo "hi there!  i'm just a plugin, not much i can do when called directly.";
	exit;
}


define( 'PAUPRESS_VER', '1.5.7' );
define( 'PAUPRESS_URL', plugin_dir_url( __FILE__ ) );
define( 'PAUPRESS_DIR', plugin_dir_path(__FILE__) );
define( 'PAUPRESS_SLUG', plugin_basename( __FILE__ ) );

 
/**
 * Register the activation and deactivation hooks to keep things tidy.
 *
 * @since 1.0.0
 */
register_activation_hook( __FILE__, 'envoyconnect_activate' );


/**
 * Include the necessary supporting scripts.
 *
 * @since 1.0.0
 */
include_once( 'options/envoyconnect-defaults.php' );
include_once( 'options/envoyconnect-settings.php' );
include_once( 'options/envoyconnect-general-settings.php' );
include_once( 'options/envoyconnect-user-settings.php' );
include_once( 'options/envoyconnect-actions-settings.php' );
include_once( 'options/envoyconnect-system-settings.php' );
include_once( 'options/envoyconnect-forms-settings.php' );
include_once( 'options/envoyconnect-panel-settings.php' );
include_once( 'options/envoyconnect-pro-settings.php' );

include_once( 'utilities/envoyconnect-fields.php' );
include_once( 'utilities/envoyconnect-help.php' );
include_once( 'utilities/envoyconnect-general.php' );
include_once( 'utilities/envoyconnect-security.php' );
include_once( 'utilities/envoyconnect-users.php' );

include_once( 'reports/envoyconnect-reports.php' );
include_once( 'reports/envoyconnect-filter-form.php' );
include_once( 'reports/envoyconnect-filter.php' );
include_once( 'reports/envoyconnect-edit.php' );
include_once( 'reports/envoyconnect-actions.php' );
include_once( 'reports/envoyconnect-queries.php' );

include_once( 'posts/envoyconnect-post-actions.php' );
include_once( 'posts/envoyconnect-user-actions.php' );
include_once( 'posts/envoyconnect-post-actions-meta.php' );
include_once( 'posts/envoyconnect-user-actions-meta.php' );

include_once( 'users/envoyconnect-users.php' );
include_once( 'users/envoyconnect-profile.php' );
include_once( 'users/envoyconnect-user-plugins.php' );

include_once( 'fields/envoyconnect-field.php' );
include_once( 'fields/envoyconnect-field-management.php' );
include_once( 'fields/envoyconnect-field-helpers.php' );
include_once( 'fields/envoyconnect-form.php' );

include_once( 'theme/envoyconnectpanels.php' );
include_once( 'theme/envoyconnectpanels-incoming.php' );

// SETS TRANSLATION SOURCES
function envoyconnect_textdomain() {
	load_plugin_textdomain( 'envoyconnect', false, dirname( PAUPRESS_SLUG ) . '/languages/' );
	$envoyconnect_t_strings = array(
									'login' => __( 'sign in', 'envoyconnect' ),
									'logout' => __( 'sign out', 'envoyconnect' ),
									'signup' => __( 'sign up', 'envoyconnect' ),
									'profile' => __( 'My Profile', 'envoyconnect' ), 
									'thanks' => __( 'Thank you!', 'envoyconnect' ), 
									'lost' => __( 'How did you get here?', 'envoyconnect' ), 
									'confirm_delete' => __( 'Confirm Deletion?', 'envoyconnect' ), 
									'confirm_submit' => __( 'Confirm Submission?', 'envoyconnect' ), 
									'support_docs' => __( 'Support Documentation', 'envoyconnect' ), 
									'discount' => __( 'Discount', 'envoyconnect' ), 
									'quantity' => __( 'Quantity', 'envoyconnect' ), 
									'amount' => __( 'Amount', 'envoyconnect' ), 
									'tax' => __( 'Tax', 'envoyconnect' ), 
									'shipping' => __( 'Shipping', 'envoyconnect' ), 
									'billing' => __( 'Billing', 'envoyconnect' ), 
									'refund' => __( 'Refund', 'envoyconnect' ), 
									'credit' => __( 'Credit', 'envoyconnect' ), 
									'default' => sprintf( __( '%1$sDefault%2$s', 'envoyconnect' ), '(', ')' ), 
	);
	$envoyconnect_t_strings = apply_filters( 'envoyconnect_t_strings', $envoyconnect_t_strings );
	
	foreach ( $envoyconnect_t_strings as $k => $v ) {
		$envoyconnect_t_reserves = array( 'ver', 'url', 'dir', 'slug' );
		if ( !in_array( $k, $envoyconnect_t_reserves ) )
			define( 'PAUPRESS_' . strtoupper( $k ), $v ); 
	}		
}


/**
 * Register the Administration supporting files
 *
 * @since 1.0.0
 */
function envoyconnect_init_register(){
	
	// GENERAL PAUPRESS SCRIPT
	$envoyconnect_ajax_array = array();
	$envoyconnect_ajax_array['ajaxurl'] = admin_url( 'admin-ajax.php' );
	$envoyconnect_ajax_array['envoyconnect_nonce'] = wp_create_nonce( 'envoyconnect-nonce' );
	$envoyconnect_ajax_array['ajaxload'] = plugins_url( '/assets/g/loading.gif', __FILE__ );
	
	// DATEPICKER OPTION FOR LOW YEAR
	$envoyconnect_ajax_array['yearLow'] = 5;
	if ( false != get_option( '_envoyconnect_yearlow' ) )
		$envoyconnect_ajax_array['yearLow'] = get_option( '_envoyconnect_yearlow' );
	
	// DATEPICKER OPTION FOR HIGH YEAR	
	$envoyconnect_ajax_array['yearHigh'] = 10;
	if ( false != get_option( '_envoyconnect_yearhigh' ) )
		$envoyconnect_ajax_array['yearHigh'] = get_option( '_envoyconnect_yearhigh' );
	
	wp_register_script( 'envoyconnectJS', PAUPRESS_URL . 'assets/j/envoyconnect.js', array( 'jquery' ), PAUPRESS_VER, false );
	wp_localize_script( 'envoyconnectJS', 'envoyconnectAjax', $envoyconnect_ajax_array );
	/*
	$user_agent = $_SERVER['HTTP_USER_AGENT']; 
	if (preg_match('/Firefox/i', $user_agent)) { 
	   wp_register_script( 'envoyconnectJS', PAUPRESS_URL . 'assets/j/envoyconnect-ff.js', array( 'jquery' ), PAUPRESS_VER, false );
	} else {
	   wp_register_script( 'envoyconnectJS', PAUPRESS_URL . 'assets/j/envoyconnect.js', array( 'jquery' ), PAUPRESS_VER, false );
	}
	*/
	
	// PAUPRESS ADDITIONAL SCRIPTS
	wp_register_script( 'envoyconnectAdminJS', PAUPRESS_URL . 'assets/j/envoyconnect-admin.js', array( 'jquery', 'wp-color-picker' ), PAUPRESS_VER, false );
	wp_register_script( 'envoyconnectpanelsJS', PAUPRESS_URL . 'assets/j/envoyconnectpanels.js', array( 'jquery' ), PAUPRESS_VER, false );
	wp_register_script( 'envoyconnectSearchJS', PAUPRESS_URL . 'assets/j/envoyconnect-search.js', array( 'jquery' ), PAUPRESS_VER, false );
	wp_register_script( 'envoyconnectViewsJS', PAUPRESS_URL . 'assets/j/envoyconnect-views.js', array( 'jquery' ), PAUPRESS_VER, false );
	wp_register_script( 'tiptipJS', PAUPRESS_URL . 'assets/j/tiptip/jquery.tiptip.js', array( 'jquery' ), PAUPRESS_VER, false );
	wp_register_script( 'chosenJS', PAUPRESS_URL . 'assets/j/chosen/chosen.jquery.js', array( 'jquery' ), PAUPRESS_VER, false );
	wp_register_script( 'cookieJS', PAUPRESS_URL . 'assets/j/jquery.cookie.js', array( 'jquery' ), PAUPRESS_VER, false );

	// REGISTER PLUGIN STYLES
	wp_register_style( 'envoyconnectCSS', PAUPRESS_URL . 'assets/c/envoyconnect.css', array(), PAUPRESS_VER, 'screen' );
	wp_register_style( 'envoyconnectAdminCSS', PAUPRESS_URL . 'assets/c/envoyconnect-admin.css', array(), PAUPRESS_VER, 'screen' );
	wp_register_style( 'envoyconnectPrintCSS', PAUPRESS_URL . 'assets/c/envoyconnect-print.css', array(), PAUPRESS_VER, 'print' );
	wp_register_style( 'envoyconnectpanelsCSS', PAUPRESS_URL . 'assets/c/envoyconnectpanels.css', array(), PAUPRESS_VER, 'screen' );
	wp_register_style( 'envoyconnectGridCSS', PAUPRESS_URL . 'assets/c/envoyconnect-grid.css', array(), PAUPRESS_VER, 'screen' );
	wp_register_style( 'tiptipCSS', PAUPRESS_URL . 'assets/j/tiptip/tiptip.css', array(), PAUPRESS_VER, 'screen' );
	wp_register_style( 'chosenCSS', PAUPRESS_URL . 'assets/j/chosen/chosen.css', array(), PAUPRESS_VER, 'screen' );
	wp_register_style( 'jqueryuiCSS', PAUPRESS_URL . 'assets/c/jquery-ui/jquery-ui.envoyconnect.css', array(), PAUPRESS_VER, 'screen' );
	
	// THIRD PARTY SUPPORT FILES
	if ( defined( 'USER_AVATAR_UPLOAD_PATH' ) )
		wp_register_style( 'user-avatar', plugins_url('/user-avatar/css/user-avatar.css') );
	
}

/**
 * Include the Administration supporting files
 *
 * @since 1.0.0
 */
function envoyconnect_admin_scripts(){

	// QUEUE PLUGIN SCRIPTS
	wp_enqueue_script( 'envoyconnectSearchJS' );
	wp_enqueue_script( 'envoyconnectViewsJS' );
	//wp_enqueue_script( 'tiptipJS', PAUPRESS_URL . 'assets/j/tiptip/jquery.tiptip.js', array( 'jquery' ), PAUPRESS_VER, false );
	//wp_enqueue_script( 'chosenJS', PAUPRESS_URL . 'assets/j/chosen/chosen.jquery.js', array( 'jquery' ), PAUPRESS_VER, false );

	// QUEUE PLUGIN STYLES
	wp_enqueue_style( 'envoyconnectCSS' );
	wp_enqueue_style( 'envoyconnectAdminCSS' );
	wp_enqueue_style( 'envoyconnectPrintCSS' );
	//wp_enqueue_style( 'tiptipCSS', PAUPRESS_URL . 'assets/j/tiptip/tiptip.css', array(), PAUPRESS_VER, 'screen' );
	//wp_enqueue_style( 'chosenCSS', PAUPRESS_URL . 'assets/j/chosen/chosen.css', array(), PAUPRESS_VER, 'screen' );
	wp_enqueue_style( 'jqueryuiCSS' );
	
	// QUEUE WORDPRESS STYLES
	wp_enqueue_style( 'thickbox' );
	wp_enqueue_style( 'wp-color-picker' );
	
	// QUEUE THIRD-PARTY STYLES
	if ( defined( 'USER_AVATAR_UPLOAD_PATH' ) )
		wp_enqueue_style( 'user-avatar' );
	
	// HOOK THE AJAX ENGINE FOR SEARCH 
	wp_localize_script( 'envoyconnectSearchJS', 'envoyconnectSearchAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'envoyconnect_search_nonce' => wp_create_nonce( 'envoyconnect-search-nonce' ), 'ajaxload' => plugins_url('/assets/g/loading.gif', __FILE__) ) );
	
	// HOOK THE AJAX ENGINE FOR REPORTS 
	wp_localize_script( 'envoyconnectSearchJS', 'envoyconnectReportAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'envoyconnect_report_nonce' => wp_create_nonce( 'envoyconnect-report-nonce' ), 'ajaxload' => plugins_url('/assets/g/loading.gif', __FILE__) ) );
	
	// HOOK THE AJAX ENGINE FOR POLLING 
	wp_localize_script( 'envoyconnectSearchJS', 'envoyconnectPollAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'envoyconnect_poll_nonce' => wp_create_nonce( 'envoyconnect-poll-nonce' ), 'ajaxload' => plugins_url('/assets/g/loading.gif', __FILE__) ) );
	
	// HOOK THE AJAX ENGINE FOR ELEMENT CHOICES 
	wp_localize_script( 'envoyconnectAdminJS', 'envoyconnectElemChoicesAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'envoyconnect_element_choices_nonce' => wp_create_nonce( 'envoyconnect-element-choices-nonce' ), 'ajaxload' => plugins_url('/assets/g/loading.gif', __FILE__) ) );
	
	
	/* DEPRECATED */
	
	/* QUEUE WORDPRESS SCRIPTS
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'jquery-ui-core' );
	wp_enqueue_script( 'jquery-ui-sortable' );
	wp_enqueue_script( 'jquery-ui-draggable' );
	wp_enqueue_script( 'jquery-ui-datepicker' );
	wp_enqueue_script( 'thickbox' );
	*/
	
	
	/* QUEUE PLUGIN SCRIPTS
	//wp_enqueue_script( 'envoyconnectJS' );
	//wp_enqueue_script( 'envoyconnectAdminJS', PAUPRESS_URL . 'assets/j/envoyconnect-admin.js', array( 'jquery' ), PAUPRESS_VER, false );
	//wp_enqueue_script( 'envoyconnectJS', PAUPRESS_URL . 'assets/j/envoyconnect.js', array( 'jquery' ), PAUPRESS_VER, false );
	
	$user_agent = $_SERVER['HTTP_USER_AGENT']; 
	if (preg_match('/Firefox/i', $user_agent)) { 
	   //wp_enqueue_script( 'envoyconnectJS', PAUPRESS_URL . 'assets/j/envoyconnect-ff.js', array( 'jquery' ), PAUPRESS_VER, false );
	} else {
	   wp_enqueue_script( 'envoyconnectJS', PAUPRESS_URL . 'assets/j/envoyconnect.js', array( 'jquery' ), PAUPRESS_VER, false );
	}
	*/
	
	/* HOOK THE AJAX ENGINE FOR GENERAL OPS
	wp_localize_script( 'envoyconnectJS', 'envoyconnectAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'envoyconnect_nonce' => wp_create_nonce( 'envoyconnect-nonce' ), 'ajaxload' => plugins_url('/assets/g/loading.gif', __FILE__) ) );
	*/
	
	/* HOOK THE AJAX ENGINE FOR ADMIN ELEMENTS 
	$admin_ajax_array = array();
	$admin_ajax_array['ajaxurl'] = admin_url( 'admin-ajax.php' );
	$admin_ajax_array['envoyconnect_admin_nonce'] = wp_create_nonce( 'envoyconnect-admin-nonce' );
	$admin_ajax_array['ajaxload'] = plugins_url('/assets/g/loading.gif', __FILE__);
	
	// PASS A VALUE IF FIREFOX IS DETECTED
	$user_agent = $_SERVER['HTTP_USER_AGENT']; 
	if ( preg_match( '/Firefox/i', $user_agent ) )
		$admin_ajax_array['firefox'] = true;
		
	wp_localize_script( 'envoyconnectAdminJS', 'envoyconnectAdminAjax', $admin_ajax_array);
	*/
		
}


function envoyconnect_admin_globals() {
		
	// QUEUE WORDPRESS SCRIPTS
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'jquery-ui-core' );
	wp_enqueue_script( 'jquery-ui-sortable' );
	wp_enqueue_script( 'jquery-ui-draggable' );
	wp_enqueue_script( 'jquery-ui-datepicker' );
	wp_enqueue_script( 'thickbox' );

	// QUEUE PAUPRESS SCRIPTS
	wp_enqueue_script( 'tiptipJS' );
	wp_enqueue_script( 'chosenJS' );
	wp_enqueue_script( 'envoyconnectJS' );
	wp_enqueue_script( 'envoyconnectAdminJS' );
	
	// QUEUE PLUGIN SCRIPTS
	//wp_enqueue_script( 'envoyconnectAdminJS', PAUPRESS_URL . 'assets/j/envoyconnect-admin.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-sortable', 'jquery-ui-draggable', 'jquery-ui-datepicker', 'thickbox', 'chosenJS' ), PAUPRESS_VER, false );
	
	// SET UP VARIABLES FOR ADMIN SCREENS
	$get_merge_tags = envoyconnect_get_user_metadata( array( 'return_val' => true, 'include' => array( 'text' ) ) );
	//$merge_tags = unserialize( urldecode( $_GET['mt'] ) );
	$merge_tags = array();
	foreach ( $get_merge_tags as $val ) {
		$merge_tags[$val['meta_key']] = $val['name'];
	}
	$merge_tags = apply_filters( 'envoyconnect_merge_filter', $merge_tags );
	$merged_tags = urlencode( serialize( $merge_tags ) );
	
	// HOOK THE AJAX ENGINE FOR ADMIN ELEMENTS
	$admin_ajax_array = array();
	$admin_ajax_array['ajaxurl'] = admin_url( 'admin-ajax.php' );
	$admin_ajax_array['envoyconnect_admin_nonce'] = wp_create_nonce( 'envoyconnect-admin-nonce' );
	$admin_ajax_array['ajaxload'] = plugins_url( '/assets/g/loading.gif', __FILE__ );
	$admin_ajax_array['ajaxhome'] = home_url();
	$admin_ajax_array['metaKeyTaken'] = __( 'duplicate key!', 'envoyconnect' );
	$admin_ajax_array['oneMoment'] = __( 'One Moment Please...', 'envoyconnect' );
	$admin_ajax_array['mergeref'] = plugins_url( '/assets/j/tmce/merge-ref.php?tags='.$merged_tags, __FILE__ );
	$admin_ajax_array['errMsg'] = sprintf( __( 'We found some errors -- please attend to the fields below marked with %1$s', 'envoyconnect' ), '<span class="halt-example">&nbsp;</span>' );
	$admin_ajax_array['confirmDelete'] = PAUPRESS_CONFIRM_DELETE;
	
	// PASS A VALUE IF FIREFOX IS DETECTED
	$user_agent = $_SERVER['HTTP_USER_AGENT']; 
	if ( preg_match( '/Firefox/i', $user_agent ) )
		$admin_ajax_array['firefox'] = true;
	
	// LOCALIZE THE ADMIN SCRIPT	
	wp_localize_script( 'envoyconnectAdminJS', 'envoyconnectAdminAjax', $admin_ajax_array );
	
	// QUEUE STYLES
	//wp_enqueue_style( 'chosenCSS', PAUPRESS_URL . 'assets/j/chosen/chosen.css', array(), PAUPRESS_VER, false );
	wp_enqueue_style( 'chosenCSS' );
	wp_enqueue_style( 'tiptipCSS' );
	
	/*
	wp_localize_script( 'envoyconnectAdminJS', 'envoyconnectAdminAjax', array( 
		'ajaxurl' 						=> admin_url( 'admin-ajax.php' ), 
		'envoyconnect_admin_nonce' 			=> wp_create_nonce( 'envoyconnect-admin-nonce' ), 
		'ajaxload' 						=> plugins_url( '/assets/g/loading.gif', __FILE__ ), 
		'metaKeyTaken'					=> __( 'duplicate key!', 'envoyconnect' ), 
		'oneMoment'						=> __( 'One Moment Please...', 'envoyconnect' ), 
		'mergeref'						=> plugins_url( '/assets/j/tmce/merge-ref.php?tags='.$merged_tags, __FILE__ ), 
		'errMsg' 						=> sprintf( __( 'We found some errors -- please attend to the fields below marked with %1$s', 'envoyconnect' ), '<span class="halt-example">&nbsp;</span>' ), 
	) );
	*/
}


/**
 * Modify the WordPress Administration Menus
 *
 * @since 1.0.0
 */
 
function envoyconnect_menu() {
	
	// SET THE ARRAY
	$envoyconnect_menu = array();
	
	// REMOVE THE DEFAULT EDIT USER MENU OPTION AND REPLACE IT
	remove_submenu_page( 'users.php', 'profile.php' );
	$envoyconnect_menu['envoyconnect_edit_user'] = add_submenu_page( 'users.php', 'EnvoyConnect User', 'My Profile', 'add_users', 'envoyconnect_edit_user', 'envoyconnect_edit_user');
	
	// REMOVE THE DEFAULT EDIT USER MENU OPTION FOR NON-ADMINS AND REPLACE IT
	remove_menu_page( 'profile.php' );
	$envoyconnect_menu['envoyconnect_edit_user_profile'] = add_menu_page( __( 'EnvoyConnect User', 'envoyconnect' ), __( 'My Profile', 'envoyconnect' ), 'read', 'envoyconnect_edit_user_profile', 'envoyconnect_edit_user');
	
	// REMOVE THE DEFAULT ADD USER MENU OPTION AND REPLACE IT
	remove_submenu_page( 'users.php', 'user-new.php' );
	$envoyconnect_menu['envoyconnect_new_user'] = add_submenu_page( 'users.php', __( 'EnvoyConnect User', 'envoyconnect' ), __( 'Add New User', 'envoyconnect' ), 'add_users', 'envoyconnect_new_user', 'envoyconnect_new_user');
	
	// ADD THE SEARCH UTILITY TO THE WORDPRESS SYSTEM
	$envoyconnect_menu['envoyconnect_reports'] = add_submenu_page( 'users.php', __( 'EnvoyConnect Reports', 'envoyconnect' ), __( 'User Reports', 'envoyconnect' ), 'add_users', 'envoyconnect_reports', 'envoyconnect_search');
	
	// CREATE THE ADMINISTRATIVE MENU
	$envoyconnect_menu['envoyconnect_caps_options'] = add_menu_page( __( 'EnvoyConnect', 'envoyconnect' ), __( 'EnvoyConnect', 'envoyconnect' ), 'manage_options', 'envoyconnect_options', 'envoyconnect_options', PAUPRESS_URL.'/assets/g/envoyconnect.png', '70.1' );
	
	// ADD THE USER OPTIONS PAGES TO THE WORDPRESS SYSTEM
	$envoyconnect_menu['envoyconnect_meta_options'] = add_submenu_page( 'envoyconnect_options', __( 'Manage Fields', 'envoyconnect' ), __( 'Manage Fields', 'envoyconnect' ), 'activate_plugins', 'envoyconnect_meta_options', 'envoyconnect_meta_options_form' );
	
	// ADD THE INBOUND COMMUNICATION DASHBOARD
	$envoyconnect_menu['envoyconnect_inbound'] = add_submenu_page( 'envoyconnect_options', __( 'Inbound Activity', 'envoyconnect' ), __( 'Inbound Activity', 'envoyconnect' ), 'add_users', 'envoyconnect_inbounds', 'envoyconnect_inbounds' );
	
	// ADD THE MODAL WINDOW
	$envoyconnect_menu['envoyconnect_modal'] = add_submenu_page( NULL, __( 'Actions', 'envoyconnect' ), '', 'add_users', 'envoyconnect_modal_action', 'envoyconnect_modal_action' );
	
	$envoyconnect_menu['users'] = 'users.php';
	
	$envoyconnect_menu = apply_filters( 'envoyconnect_push_menu', $envoyconnect_menu );

	foreach ( $envoyconnect_menu as $key => $value ) {
		add_action( 'admin_print_styles-' . $value, 'envoyconnect_admin_scripts', 9 );
		add_action( 'load-' . $value, 'envoyconnect_help_screens' );
	}
			
}


function envoyconnect_actions_link( $links ) { 
	$settings_link = '<a href="' . admin_url() . '?page=envoyconnect_options">Settings</a>'; 
  	array_unshift($links, $settings_link); 
  	return $links; 
}


/**
 * WordPress Actions & Filters
 *
 * @since 1.0.0
 */
// ADDS THE GLOBAL MENU STRUCTURE
add_action( 'admin_menu', 'envoyconnect_menu', 9  );
add_filter( 'admin_body_class', 'envoyconnect_admin_body_class' );
//add_action( 'admin_bar_menu', 'envoyconnect_add_nodes', 999 );

// RUN FIRST
add_action( 'init', 'envoyconnect_restrict_redirect' );
add_action( 'init', 'envoyconnect_minified_admin' );
add_action( 'init', 'envoyconnect_options_save' );
add_action( 'init', 'envoyconnect_init_register' );
add_action( 'init', 'envoyconnect_init_roles' );
add_action( 'init', 'envoyconnect_init_user_actions' );
add_action( 'init', 'envoyconnect_init_post_actions' );
add_action( 'init', 'envoyconnect_wp_taxonomies' );
add_action( 'init', 'envoyconnect_add_merge_tags_button', 20 );
add_action( 'admin_init', 'envoyconnect_listener' );
//add_action( 'admin_init', 'envoyconnect_init_register' );
add_action( 'admin_init', 'envoyconnect_activate' );
add_action( 'admin_init', 'envoyconnect_update_user' );
//add_action( 'admin_init', 'envoyconnect_action_minified' );
add_action( 'plugins_loaded', 'envoyconnect_textdomain' );
add_action( 'envoyconnect_options_save_ext','envoyconnect_save_capabilities' );
add_action( 'admin_enqueue_scripts', 'envoyconnect_admin_globals' );

// QUEUES SUPPORTING FILES
add_action( 'admin_head', 'envoyconnect_admin_fixes' );

// ADD SHORTCODES
add_shortcode( 'ppmt', 'envoyconnect_merge_tags_shortcode' );
add_shortcode( 'envoyconnectf', 'envoyconnectpanels_forms_shortcode' );
add_filter('widget_text', 'do_shortcode');

// DISPLAYS THE ADDITIONAL DATA FOR EACH USER
add_action( 'envoyconnect_action_search_meta', 'envoyconnect_action_search_meta', 10, 2 );

// OPENS UP A RESOURCE FOR EXTERNAL APIS
add_filter( 'query_vars', 'envoyconnect_query_vars' );
add_action( 'generate_rewrite_rules', 'envoyconnect_rewrite_rules' );
add_action( 'parse_request', 'envoyconnect_parse_request' );

// HOOK INTO THE USER ACTIONS META API
add_filter( 'envoyconnect_get_user_actions', 'envoyconnect_push_user_actions' );
add_filter( 'envoyconnect_get_user_actions_meta', 'envoyconnect_push_user_actions_meta' );

// MODIFY USER HISTORY PANEL
add_filter( 'envoyconnect_ai_class_filter', 'pp_log_ai_class_filter', 10, 2 );

// FILTER OUT PAUCONTENT
add_filter( 'paucontent_exclude_accepted_post_types', 'envoyconnect_exclude_accepted_post_types' );
add_filter( 'paucontent_exclude_restricted_post_types', 'envoyconnect_exclude_restricted_post_types' );

// SAVES THE ACTION META DATA
add_action( 'save_post', 'envoyconnect_save_action_meta' ); // action_save_meta
add_filter( 'attachment_fields_to_save', 'envoyconnect_attachment_save', 10, 2);

// MODIFIES DEFAULT USER COLUMNS
//add_action('manage_users_custom_column', 'envoyconnect_manage_users_custom_column', 15, 3);

// MODIFY THE LOGIN FORM FOR EMAIL LABELS
add_action( 'login_form', 'envoyconnect_email_login' );

// HOOK ADMIN AJAX SUBMISSION FOR SAVING QUERIES
add_action( 'wp_ajax_envoyconnect_process_queries', 'envoyconnect_process_saved_queries' );

// HOOK ADMIN AJAX SUBMISSION FOR ADMINISTERING SAVED IMPORTS
add_action( 'wp_ajax_envoyconnect_process_saved_imports', 'envoyconnect_saved_import_process' );

// HOOK ADMIN AJAX SUBMISSION FOR SEARCHES
add_action( 'wp_ajax_envoyconnect_search_form', 'envoyconnect_search_form' );

// HOOK ADMIN AJAX SUBMISSION FOR REPORTS
add_action( 'wp_ajax_envoyconnect_report_form', 'envoyconnect_report_process' );

// HOOK ADMIN AJAX SUBMISSION FOR NEW ELEMENTS
add_action( 'wp_ajax_envoyconnect_new_elements_form', 'envoyconnect_new_elements_forms' );

// HOOK ADMIN AJAX SUBMISSION FOR INLINE POSTS
add_action( 'wp_ajax_envoyconnect_get_post_to_edit', 'envoyconnect_get_post_to_edit' );
add_action( 'wp_ajax_envoyconnect_save_new_post', 'envoyconnect_save_new_post' );

// HOOK ADMIN AJAX SUBMISSION FOR ELEMENT CHOICES
add_action( 'wp_ajax_envoyconnect_element_choices_form', 'envoyconnect_element_choices_forms' );

// HOOK ADMIN AJAX SUBMISSION FOR ELEMENT CHOICES
add_action( 'wp_ajax_envoyconnect_poll_status', 'envoyconnect_poll_statuses' );
add_action( 'wp_ajax_envoyconnect_new_search_row', 'envoyconnect_add_search_row' );

// SWAPS TARGET USER IN AS POST AUTHOR
add_filter( 'wp_insert_post_data' , 'envoyconnect_log_user_action_meta' , '99', 2 );

// AUTHENTICATE BY EMAIL
remove_filter( 'authenticate', 'wp_authenticate_username_password', 20, 3 );
add_filter( 'authenticate', 'envoyconnect_email_authenticate', 20, 3 );

add_action( 'wp_ajax_envoyconnect_load_form', 'envoyconnect_load_form' );
add_action( 'wp_ajax_envoyconnect_new_form', 'envoyconnect_new_form' );
add_action( 'wp_ajax_envoyconnect_delete_form', 'envoyconnect_delete_form' );
add_action( 'wp_ajax_envoyconnect_profile_fields_update', 'envoyconnect_profile_fields_update' );

// EXTEND THE ADDRESS OPTIONS FOR A PRIMARY MARKER
add_action( 'envoyconnect_group_markers', 'envoyconnect_primary_marker', 3, 10 );

// EXTEND UI FOR SETTINGS
add_filter( 'plugin_action_links_'.PAUPRESS_SLUG, 'envoyconnect_actions_link' );
add_action( 'envoyconnect_options_pre', 'envoyconnect_option_welcome' );
add_action( 'envoyconnect_options_post', 'envoyconnect_option_goodbye' );

// PAUPANELS SHORT CODES
add_shortcode( 'ppf_link', 'envoyconnectpanels_link_shortcode' );

// SETS THE MAIL DEFAULTS
add_filter( 'wp_mail_from', 'envoyconnect_mail_from' );
add_filter( 'wp_mail_from_name', 'envoyconnect_mail_from_name' );

/*add_action( 'welcome_panel', 'envoyconnect_welcome', 18 );
function envoyconnect_welcome() {
?>
</div>
<div id="envoyconnect-welcome-panel" class="welcome-panel">
	<h3>EnvoyConnect</h3>
	<p class="about-description">hi there. we're glad you're here!</p>
	<div class="welcome-panel-column-container">
		<div class="welcome-panel-column"><h4>Howdy!</h4></div>
		<div class="welcome-panel-column"><h4>Hej!</h4></div>
		<div class="welcome-panel-column"><h4>Wilkomen!</h4></div>
	</div>

<?php
}
*/
