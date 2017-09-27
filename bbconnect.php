<?php
/*
Plugin Name: Connexions
Plugin URI: http://connexionscrm.com/
Description: A CRM framework for Wordpress
Version: 2.6.0
Author: Brown Box
Author URI: http://brownbox.net.au/
Text Domain: bbconnect
Domain Path: languages

Connexions is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Connexions is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Connexions. If not, see <http://www.gnu.org/licenses/>.
*/

/* -----------------------------------------------------------
	SETUP, OPTIONS & ACTIONS
   ----------------------------------------------------------- */

/**
 * Security: Shut it down if the plugin is called directly
 *
 * @since 1.0.0
 */
if (!function_exists( 'add_action')) {
	echo "Hi there!  I'm just a plugin, not much I can do when called directly.";
	exit;
}

define('BBCONNECT_VER', '2.6.0');
define('BBCONNECT_URL', plugin_dir_url(__FILE__));
define('BBCONNECT_DIR', plugin_dir_path(__FILE__));
define('BBCONNECT_SLUG', plugin_basename(__FILE__));

/**
 * Register the activation and deactivation hooks to keep things tidy.
 *
 * @since 1.0.0
 */
register_activation_hook( __FILE__, 'bbconnect_activate' );

register_deactivation_hook(__FILE__, 'bbconnect_deactivate');

/**
 * Add support for automatic updates
 *
 * @since 2.3.0
 */
require_once('utilities/bbconnect-updates.php');
if (is_admin()) {
    new BbConnectUpdates(__FILE__, 'BrownBox', 'bbconnect');
}

/**
 * Include the necessary supporting scripts.
 *
 * @since 1.0.0
 */
include_once( 'options/bbconnect-defaults.php' );
include_once( 'options/bbconnect-settings.php' );
include_once( 'options/bbconnect-general-settings.php' );
include_once( 'options/bbconnect-user-settings.php' );
include_once( 'options/bbconnect-actions-settings.php' );
include_once( 'options/bbconnect-system-settings.php' );
include_once( 'options/bbconnect-forms-settings.php' );
include_once( 'options/bbconnect-panel-settings.php' );
include_once( 'options/bbconnect-create-forms.php' );

include_once( 'utilities/bbconnect-time.php' );
include_once( 'utilities/bbconnect-fields.php' );
include_once( 'utilities/bbconnect-help.php' );
include_once( 'utilities/bbconnect-general.php' );
include_once( 'utilities/bbconnect-security.php' );
include_once( 'utilities/bbconnect-users.php' );
include_once( 'utilities/bbconnect-tax-meta.php' );
include_once( 'utilities/bbconnect-forms.php' );
include_once( 'utilities/bbconnect-bb-cart.php' );
include_once( 'utilities/bbconnect-quicklinks.php' );

include_once( 'reports/bbconnect-reports.php' );
include_once( 'reports/bbconnect-filter-form.php' );
include_once( 'reports/bbconnect-filter.php' );
include_once( 'reports/bbconnect-edit.php' );
include_once( 'reports/bbconnect-actions.php' );
include_once( 'reports/bbconnect-queries.php' );
include_once( 'reports/bbconnect-savedsearch.php' );
include_once( 'reports/bbconnect-savedsearch-meta.php' );
include_once( 'reports/bbconnect-savedsearch-modal.php' );

include_once( 'posts/bbconnect-post-actions.php' );
include_once( 'posts/bbconnect-user-actions.php' );
include_once( 'posts/bbconnect-post-actions-meta.php' );
include_once( 'posts/bbconnect-user-actions-meta.php' );
include_once( 'posts/bbconnect-user-actions-tax.php' );
include_once( 'posts/bbconnect-post-notes-meta.php' );

include_once( 'users/bbconnect-activity-log.php' );
include_once( 'users/bbconnect-users.php' );
include_once( 'users/bbconnect-profile.php' );
include_once( 'users/bbconnect-user-plugins.php' );
include_once( 'users/bbconnect-user-merge.php' );

include_once( 'fields/bbconnect-field.php' );
include_once( 'fields/bbconnect-field-management.php' );
include_once( 'fields/bbconnect-field-helpers.php' );
include_once( 'fields/bbconnect-form.php' );

include_once( 'theme/bbconnectpanels.php' );

// SETS TRANSLATION SOURCES
function bbconnect_textdomain() {
	load_plugin_textdomain( 'bbconnect', false, dirname( BBCONNECT_SLUG ) . '/languages/' );
	$bbconnect_t_strings = array(
									'login' => __( 'sign in', 'bbconnect' ),
									'logout' => __( 'sign out', 'bbconnect' ),
									'signup' => __( 'sign up', 'bbconnect' ),
									'profile' => __( 'My Profile', 'bbconnect' ),
									'thanks' => __( 'Thank you!', 'bbconnect' ),
									'lost' => __( 'How did you get here?', 'bbconnect' ),
									'confirm_delete' => __( 'Confirm Deletion?', 'bbconnect' ),
									'confirm_submit' => __( 'Confirm Submission?', 'bbconnect' ),
									'support_docs' => __( 'Support Documentation', 'bbconnect' ),
									'discount' => __( 'Discount', 'bbconnect' ),
									'quantity' => __( 'Quantity', 'bbconnect' ),
									'amount' => __( 'Amount', 'bbconnect' ),
									'tax' => __( 'Tax', 'bbconnect' ),
									'shipping' => __( 'Shipping', 'bbconnect' ),
									'billing' => __( 'Billing', 'bbconnect' ),
									'refund' => __( 'Refund', 'bbconnect' ),
									'credit' => __( 'Credit', 'bbconnect' ),
									'default' => sprintf( __( '%1$sDefault%2$s', 'bbconnect' ), '(', ')' ),
	);
	$bbconnect_t_strings = apply_filters( 'bbconnect_t_strings', $bbconnect_t_strings );

	foreach ( $bbconnect_t_strings as $k => $v ) {
		$bbconnect_t_reserves = array( 'ver', 'url', 'dir', 'slug' );
		if ( !in_array( $k, $bbconnect_t_reserves ) )
			define( 'BBCONNECT_' . strtoupper( $k ), $v );
	}
}


/**
 * Register the Administration supporting files
 *
 * @since 1.0.0
 */
function bbconnect_init_register(){

	// GENERAL BBCONNECT SCRIPT
	$bbconnect_ajax_array = array();
	$bbconnect_ajax_array['ajaxurl'] = admin_url( 'admin-ajax.php' );
	$bbconnect_ajax_array['bbconnect_nonce'] = wp_create_nonce( 'bbconnect-nonce' );
	$bbconnect_ajax_array['ajaxload'] = plugins_url( '/assets/g/loading.gif', __FILE__ );

	// DATEPICKER OPTION FOR LOW YEAR
	$bbconnect_ajax_array['yearLow'] = 5;
	if ( false != get_option( '_bbconnect_yearlow' ) )
		$bbconnect_ajax_array['yearLow'] = get_option( '_bbconnect_yearlow' );

	// DATEPICKER OPTION FOR HIGH YEAR
	$bbconnect_ajax_array['yearHigh'] = 10;
	if ( false != get_option( '_bbconnect_yearhigh' ) )
		$bbconnect_ajax_array['yearHigh'] = get_option( '_bbconnect_yearhigh' );

	wp_register_script( 'bbconnectJS', BBCONNECT_URL . 'assets/j/bbconnect.js', array( 'jquery' ), BBCONNECT_VER, false );
	wp_localize_script( 'bbconnectJS', 'bbconnectAjax', $bbconnect_ajax_array );

	// BBCONNECT ADDITIONAL SCRIPTS
	wp_register_script( 'bbconnectAdminJS', BBCONNECT_URL . 'assets/j/bbconnect-admin.js', array( 'jquery', 'wp-color-picker' ), BBCONNECT_VER, false );
	wp_register_script( 'bbconnectpanelsJS', BBCONNECT_URL . 'assets/j/bbconnectpanels.js', array( 'jquery' ), BBCONNECT_VER, false );
	wp_register_script( 'bbconnectSearchJS', BBCONNECT_URL . 'assets/j/bbconnect-search.js', array( 'jquery' ), BBCONNECT_VER, false );
	wp_register_script( 'bbconnectViewsJS', BBCONNECT_URL . 'assets/j/bbconnect-views.js', array( 'jquery' ), BBCONNECT_VER, false );
	wp_register_script( 'tiptipJS', BBCONNECT_URL . 'assets/j/tiptip/jquery.tiptip.js', array( 'jquery' ), BBCONNECT_VER, false );
	wp_register_script( 'chosenJS', BBCONNECT_URL . 'assets/j/chosen/chosen.jquery.js', array( 'jquery' ), BBCONNECT_VER, false );
	wp_register_script( 'cookieJS', BBCONNECT_URL . 'assets/j/jquery.cookie.js', array( 'jquery' ), BBCONNECT_VER, false );

	// TABLE EXPORT
    wp_register_script('Blob', BBCONNECT_URL . 'assets/j/export/Blob.js', array('jquery'), '1.1.1', false);
    wp_register_script('FileSaver', BBCONNECT_URL . 'assets/j/export/FileSaver.min.js', array('jquery', 'Blob'), '1.3.6', false);
    wp_register_script('tableExport', BBCONNECT_URL . 'assets/j/export/tableExport.js', array('jquery', 'Blob', 'FileSaver'), '4.0.2', false);
    wp_register_script('tableExportBase64', BBCONNECT_URL . 'assets/j/export/jquery.base64.js', array('jquery'), BBCONNECT_VER, false);

	// REGISTER PLUGIN STYLES
	wp_register_style( 'bbconnectCSS', BBCONNECT_URL . 'assets/c/bbconnect.css', array(), BBCONNECT_VER, 'screen' );
	wp_register_style( 'bbconnectAdminCSS', BBCONNECT_URL . 'assets/c/bbconnect-admin.css', array(), BBCONNECT_VER, 'screen' );
	wp_register_style( 'bbconnectPrintCSS', BBCONNECT_URL . 'assets/c/bbconnect-print.css', array(), BBCONNECT_VER, 'print' );
	wp_register_style( 'bbconnectpanelsCSS', BBCONNECT_URL . 'assets/c/bbconnectpanels.css', array(), BBCONNECT_VER, 'screen' );
	wp_register_style( 'bbconnectGridCSS', BBCONNECT_URL . 'assets/c/bbconnect-grid.css', array(), BBCONNECT_VER, 'screen' );
	wp_register_style( 'tiptipCSS', BBCONNECT_URL . 'assets/j/tiptip/tiptip.css', array(), BBCONNECT_VER, 'screen' );
	wp_register_style( 'chosenCSS', BBCONNECT_URL . 'assets/j/chosen/chosen.css', array(), BBCONNECT_VER, 'screen' );
	wp_register_style( 'jqueryuiCSS', BBCONNECT_URL . 'assets/c/jquery-ui/jquery-ui.bbconnect.css', array(), BBCONNECT_VER, 'screen' );

	// THIRD PARTY SUPPORT FILES
	if ( defined( 'USER_AVATAR_UPLOAD_PATH' ) ) {
		wp_register_style( 'user-avatar', plugins_url('/user-avatar/css/user-avatar.css') );
	}
}

/**
 * Include the Administration supporting files
 *
 * @since 1.0.0
 */
function bbconnect_admin_scripts(){
	// QUEUE PLUGIN SCRIPTS
	wp_enqueue_script( 'bbconnectSearchJS' );
	wp_enqueue_script( 'bbconnectViewsJS' );
	wp_enqueue_script('Blob');
    wp_enqueue_script('FileSaver');
	wp_enqueue_script('tableExport');
	wp_enqueue_script('tableExportBase64');

	// QUEUE PLUGIN STYLES
	wp_enqueue_style( 'bbconnectCSS' );
	wp_enqueue_style( 'bbconnectAdminCSS' );
	wp_enqueue_style( 'bbconnectPrintCSS' );
	wp_enqueue_style( 'jqueryuiCSS' );

	// QUEUE WORDPRESS STYLES
	wp_enqueue_style( 'thickbox' );
	wp_enqueue_style( 'wp-color-picker' );

	// QUEUE THIRD-PARTY STYLES
	if ( defined( 'USER_AVATAR_UPLOAD_PATH' ) )
		wp_enqueue_style( 'user-avatar' );

	// HOOK THE AJAX ENGINE FOR SEARCH
	wp_localize_script( 'bbconnectSearchJS', 'bbconnectSearchAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'bbconnect_search_nonce' => wp_create_nonce( 'bbconnect-search-nonce' ), 'ajaxload' => plugins_url('/assets/g/loading.gif', __FILE__) ) );

	// HOOK THE AJAX ENGINE FOR REPORTS
	wp_localize_script( 'bbconnectSearchJS', 'bbconnectReportAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'bbconnect_report_nonce' => wp_create_nonce( 'bbconnect-report-nonce' ), 'ajaxload' => plugins_url('/assets/g/loading.gif', __FILE__) ) );

	// HOOK THE AJAX ENGINE FOR POLLING
	wp_localize_script( 'bbconnectSearchJS', 'bbconnectPollAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'bbconnect_poll_nonce' => wp_create_nonce( 'bbconnect-poll-nonce' ), 'ajaxload' => plugins_url('/assets/g/loading.gif', __FILE__) ) );

	// HOOK THE AJAX ENGINE FOR ELEMENT CHOICES
	wp_localize_script( 'bbconnectAdminJS', 'bbconnectElemChoicesAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'bbconnect_element_choices_nonce' => wp_create_nonce( 'bbconnect-element-choices-nonce' ), 'ajaxload' => plugins_url('/assets/g/loading.gif', __FILE__) ) );
}


function bbconnect_admin_globals() {
	// QUEUE WORDPRESS SCRIPTS
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'jquery-ui-core' );
	wp_enqueue_script( 'jquery-ui-sortable' );
	wp_enqueue_script( 'jquery-ui-draggable' );
	wp_enqueue_script( 'jquery-ui-datepicker' );
	wp_enqueue_script( 'thickbox' );

	// QUEUE BBCONNECT SCRIPTS
	wp_enqueue_script( 'tiptipJS' );
	wp_enqueue_script( 'chosenJS' );
	wp_enqueue_script( 'bbconnectJS' );
	wp_enqueue_script( 'bbconnectAdminJS' );

	// SET UP VARIABLES FOR ADMIN SCREENS
	$get_merge_tags = bbconnect_get_user_metadata( array( 'return_val' => true, 'include' => array( 'text' ) ) );
	//$merge_tags = unserialize( urldecode( $_GET['mt'] ) );
	$merge_tags = array();
	foreach ( $get_merge_tags as $val ) {
		$merge_tags[$val['meta_key']] = $val['name'];
	}
	$merge_tags = apply_filters( 'bbconnect_merge_filter', $merge_tags );
	$merged_tags = urlencode( serialize( $merge_tags ) );

	// HOOK THE AJAX ENGINE FOR ADMIN ELEMENTS
	$admin_ajax_array = array();
	$admin_ajax_array['ajaxurl'] = admin_url( 'admin-ajax.php' );
	$admin_ajax_array['bbconnect_admin_nonce'] = wp_create_nonce( 'bbconnect-admin-nonce' );
	$admin_ajax_array['ajaxload'] = plugins_url( '/assets/g/loading.gif', __FILE__ );
	$admin_ajax_array['ajaxhome'] = home_url();
	$admin_ajax_array['metaKeyTaken'] = __( 'duplicate key!', 'bbconnect' );
	$admin_ajax_array['oneMoment'] = __( 'One Moment Please...', 'bbconnect' );
	$admin_ajax_array['mergeref'] = plugins_url( '/assets/j/tmce/merge-ref.php?tags='.$merged_tags, __FILE__ );
	$admin_ajax_array['errMsg'] = sprintf( __( 'We found some errors -- please attend to the fields below marked with %1$s', 'bbconnect' ), '<span class="halt-example">&nbsp;</span>' );
	$admin_ajax_array['confirmDelete'] = BBCONNECT_CONFIRM_DELETE;

	// PASS A VALUE IF FIREFOX IS DETECTED
	$user_agent = $_SERVER['HTTP_USER_AGENT'];
	if ( preg_match( '/Firefox/i', $user_agent ) )
		$admin_ajax_array['firefox'] = true;

	// LOCALIZE THE ADMIN SCRIPT
	wp_localize_script( 'bbconnectAdminJS', 'bbconnectAdminAjax', $admin_ajax_array );

	// QUEUE STYLES
	//wp_enqueue_style( 'chosenCSS', BBCONNECT_URL . 'assets/j/chosen/chosen.css', array(), BBCONNECT_VER, false );
	wp_enqueue_style( 'chosenCSS' );
	wp_enqueue_style( 'tiptipCSS' );
}


/**
 * Modify the WordPress Administration Menus
 *
 * @since 1.0.0
 */

function bbconnect_menu() {

	// SET THE ARRAY
	$bbconnect_menu = array();

	// REMOVE THE DEFAULT EDIT USER MENU OPTION AND REPLACE IT
	remove_submenu_page( 'users.php', 'profile.php' );
	$bbconnect_menu['bbconnect_edit_user'] = add_submenu_page( 'users.php', 'Connexions User', 'My Profile', 'add_users', 'bbconnect_edit_user', 'bbconnect_edit_user');

	// REMOVE THE DEFAULT EDIT USER MENU OPTION FOR NON-ADMINS AND REPLACE IT
	remove_menu_page( 'profile.php' );
	$bbconnect_menu['bbconnect_edit_user_profile'] = add_menu_page( __( 'Connexions User', 'bbconnect' ), __( 'My Profile', 'bbconnect' ), 'read', 'bbconnect_edit_user_profile', 'bbconnect_edit_user');

	// REMOVE THE DEFAULT ADD USER MENU OPTION AND REPLACE IT
	remove_submenu_page( 'users.php', 'user-new.php' );
	$bbconnect_menu['bbconnect_new_user'] = add_submenu_page( 'users.php', __( 'Connexions User', 'bbconnect' ), __( 'Add New User', 'bbconnect' ), 'add_users', 'bbconnect_new_user', 'bbconnect_new_user');

	// ADD THE SEARCH UTILITY TO THE WORDPRESS SYSTEM
	$bbconnect_menu['bbconnect_reports'] = add_submenu_page( 'users.php', __( 'Connexions Reports', 'bbconnect' ), __( 'User Reports', 'bbconnect' ), 'add_users', 'bbconnect_reports', 'bbconnect_search');

	// ACTIVITY LOG
	$bbconnect_menu['bbconnect_activity_log'] = add_submenu_page( 'users.php', __( 'Connexions Activity Log', 'bbconnect' ), __( 'Activity Log', 'bbconnect' ), 'add_users', 'bbconnect_activity_log', 'bbconnect_activity_log');

	// CREATE THE ADMINISTRATIVE MENU
	$bbconnect_menu['bbconnect_caps_options'] = add_menu_page( __( 'Connexions', 'bbconnect' ), __( 'Connexions', 'bbconnect' ), 'list_users', 'bbconnect_options', 'bbconnect_options', BBCONNECT_URL.'/assets/g/bbconnect.png', '70.1' );

	// ADD THE USER OPTIONS PAGES TO THE WORDPRESS SYSTEM
	$bbconnect_menu['bbconnect_meta_options'] = add_submenu_page( 'bbconnect_options', __( 'Manage Fields', 'bbconnect' ), __( 'Manage Fields', 'bbconnect' ), 'activate_plugins', 'bbconnect_meta_options', 'bbconnect_meta_options_form' );

	// ADD THE MODAL WINDOW
	$bbconnect_menu['bbconnect_modal'] = add_submenu_page( NULL, __( 'Actions', 'bbconnect' ), '', 'add_users', 'bbconnect_modal_action', 'bbconnect_modal_action' );

	// ADD THE FORM PAGE
	// By adding and then removing the submenu we can register the page without it appearing in the menu, while keeping Users highlighted when on the page. Nice!
	$bbconnect_menu['bbconnect_gravity_form'] = add_submenu_page( 'users.php', __( 'Submit Form', 'bbconnect' ), '', 'add_users', 'bbconnect_submit_gravity_form', 'bbconnect_submit_gravity_form' );
	remove_submenu_page('users.php', 'bbconnect_submit_gravity_form');

	$bbconnect_menu['users'] = 'users.php';

	$bbconnect_menu = apply_filters( 'bbconnect_push_menu', $bbconnect_menu );

	foreach ( $bbconnect_menu as $key => $value ) {
		add_action( 'admin_print_styles-' . $value, 'bbconnect_admin_scripts', 9 );
		add_action( 'load-' . $value, 'bbconnect_help_screens' );
	}

}


function bbconnect_actions_link( $links ) {
	$settings_link = '<a href="' . admin_url() . '?page=bbconnect_options">Settings</a>';
  	array_unshift($links, $settings_link);
  	return $links;
}


/**
 * WordPress Actions & Filters
 *
 * @since 1.0.0
 */
// ADDS THE GLOBAL MENU STRUCTURE
add_action( 'admin_menu', 'bbconnect_menu', 9  );
add_filter( 'admin_body_class', 'bbconnect_admin_body_class' );
//add_action( 'admin_bar_menu', 'bbconnect_add_nodes', 999 );

// RUN FIRST
add_action( 'init', 'bbconnect_restrict_redirect' );
add_action( 'init', 'bbconnect_minified_admin' );
add_action( 'init', 'bbconnect_options_save' );
add_action( 'init', 'bbconnect_init_register' );
add_action( 'init', 'bbconnect_init_roles' );
add_action( 'init', 'bbconnect_init_user_actions' );
add_action( 'init', 'bbconnect_init_post_actions' );
add_action( 'init', 'bbconnect_wp_taxonomies' );
add_action( 'init', 'bbconnect_add_merge_tags_button', 20 );
add_action( 'admin_init', 'bbconnect_listener' );
//add_action( 'admin_init', 'bbconnect_init_register' );
add_action( 'admin_init', 'bbconnect_activate' );
add_action( 'admin_init', 'bbconnect_update_user' );
//add_action( 'admin_init', 'bbconnect_action_minified' );
add_action( 'plugins_loaded', 'bbconnect_textdomain' );
add_action( 'bbconnect_options_save_ext','bbconnect_save_capabilities' );
add_action( 'admin_enqueue_scripts', 'bbconnect_admin_globals' );

// QUEUES SUPPORTING FILES
add_action( 'admin_head', 'bbconnect_admin_fixes' );

// ADD SHORTCODES
add_shortcode( 'ppmt', 'bbconnect_merge_tags_shortcode' );
add_shortcode( 'bbconnectf', 'bbconnectpanels_forms_shortcode' );
add_filter('widget_text', 'do_shortcode');

// DISPLAYS THE ADDITIONAL DATA FOR EACH USER
add_action( 'bbconnect_action_search_meta', 'bbconnect_action_search_meta', 10, 2 );

// OPENS UP A RESOURCE FOR EXTERNAL APIS
add_filter( 'query_vars', 'bbconnect_query_vars' );
add_action( 'generate_rewrite_rules', 'bbconnect_rewrite_rules' );
add_action( 'parse_request', 'bbconnect_parse_request' );

// HOOK INTO THE USER ACTIONS META API
add_filter( 'bbconnect_get_user_actions', 'bbconnect_push_user_actions' );
add_filter( 'bbconnect_get_user_actions_meta', 'bbconnect_push_user_actions_meta' );

// ACTIVITY LOG
add_filter('bbconnect_activity_icon', 'bbconnect_activity_icon', 0, 2);

// MODIFY USER HISTORY PANEL
add_filter( 'bbconnect_ai_class_filter', 'bbc_log_ai_class_filter', 10, 2 );

// FILTER OUT BBCCONTENT
add_filter( 'bbccontent_exclude_accepted_post_types', 'bbconnect_exclude_accepted_post_types' );
add_filter( 'bbccontent_exclude_restricted_post_types', 'bbconnect_exclude_restricted_post_types' );

// SAVES THE ACTION META DATA
add_action( 'save_post', 'bbconnect_save_action_meta' ); // action_save_meta
add_filter( 'attachment_fields_to_save', 'bbconnect_attachment_save', 10, 2);

// MODIFIES DEFAULT USER COLUMNS
//add_action('manage_users_custom_column', 'bbconnect_manage_users_custom_column', 15, 3);

// ONLY SHOW USERS FROM CURRENT SITE
add_filter('bbconnect_search_results', 'bbconnect_filter_users_current_blog', 1, 1);

// MODIFY THE LOGIN FORM FOR EMAIL LABELS
add_action( 'login_form', 'bbconnect_email_login' );

// HOOK ADMIN AJAX SUBMISSION FOR SAVING QUERIES
add_action( 'wp_ajax_bbconnect_process_queries', 'bbconnect_process_saved_queries' );
//HOOK ADMIN AJAX FOR SUBMISSION TO DISPLAY SAVED SEARCHES
add_action( 'wp_ajax_bbconnect_display_savedsearches', 'bbconnect_display_savedsearches' );
//HOOK ADMIN AJAX FOR SUBMISSION TO ARCHIVE A SAVED SEARCH RESULT WHEN CHECKBOX TICKED.
add_action( 'wp_ajax_bbconnect_archive_saved_search', 'bbconnect_archive_saved_search' );

// HOOK ADMIN AJAX SUBMISSION FOR ADMINISTERING SAVED IMPORTS
add_action( 'wp_ajax_bbconnect_process_saved_imports', 'bbconnect_saved_import_process' );

// HOOK ADMIN AJAX SUBMISSION FOR SEARCHES
add_action( 'wp_ajax_bbconnect_search_form', 'bbconnect_search_form' );

// HOOK ADMIN AJAX SUBMISSION FOR REPORTS
add_action( 'wp_ajax_bbconnect_report_form', 'bbconnect_report_process' );

// HOOK ADMIN AJAX SUBMISSION FOR SAVE SEARCH CRITERIA
add_action( 'wp_ajax_bbconnect_save_search', 'bbconnect_save_search' );
add_action( 'wp_ajax_bbconnect_create_search_post', 'bbconnect_create_search_post' );

// HOOK ADMIN AJAX SUBMISSION FOR NEW ELEMENTS
add_action( 'wp_ajax_bbconnect_new_elements_form', 'bbconnect_new_elements_forms' );

// HOOK ADMIN AJAX SUBMISSION FOR INLINE POSTS
add_action( 'wp_ajax_bbconnect_get_post_to_edit', 'bbconnect_get_post_to_edit' );
add_action( 'wp_ajax_bbconnect_save_new_post', 'bbconnect_save_new_post' );

// HOOK ADMIN AJAX SUBMISSION FOR ELEMENT CHOICES
add_action( 'wp_ajax_bbconnect_element_choices_form', 'bbconnect_element_choices_forms' );

// HOOK ADMIN AJAX SUBMISSION FOR ELEMENT CHOICES
add_action( 'wp_ajax_bbconnect_poll_status', 'bbconnect_poll_statuses' );
add_action( 'wp_ajax_bbconnect_new_search_row', 'bbconnect_add_search_row' );

// SWAPS TARGET USER IN AS POST AUTHOR
add_filter( 'wp_insert_post_data' , 'bbconnect_log_user_action_meta' , '99', 2 );

// AUTHENTICATE BY EMAIL
remove_filter( 'authenticate', 'wp_authenticate_username_password', 20, 3 );
add_filter( 'authenticate', 'bbconnect_email_authenticate', 20, 3 );

add_action( 'wp_ajax_bbconnect_load_form', 'bbconnect_load_form' );
add_action( 'wp_ajax_bbconnect_new_form', 'bbconnect_new_form' );
add_action( 'wp_ajax_bbconnect_delete_form', 'bbconnect_delete_form' );
add_action( 'wp_ajax_bbconnect_profile_fields_update', 'bbconnect_profile_fields_update' );

// EXTEND THE ADDRESS OPTIONS FOR A PRIMARY MARKER
add_action( 'bbconnect_group_markers', 'bbconnect_primary_marker', 3, 10 );

// EXTEND UI FOR SETTINGS
add_filter( 'plugin_action_links_'.BBCONNECT_SLUG, 'bbconnect_actions_link' );
add_action( 'bbconnect_options_pre', 'bbconnect_option_welcome' );
add_action( 'bbconnect_options_post', 'bbconnect_option_goodbye' );

// BBCPANELS SHORT CODES
add_shortcode( 'ppf_link', 'bbconnectpanels_link_shortcode' );

// SETS THE MAIL DEFAULTS
add_filter( 'wp_mail_from', 'bbconnect_mail_from' );
add_filter( 'wp_mail_from_name', 'bbconnect_mail_from_name' );

// DON'T NOTIFY USERS WHEN EMAIL IS CHANGED
add_filter('send_email_change_email', '__return_false', 999);

// LOAD QUICKLINKS
add_action('plugins_loaded', 'bbconnect_quicklinks_init');
