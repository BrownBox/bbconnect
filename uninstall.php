<?php

if( !defined( 'ABSPATH' ) && !defined( 'WP_UNINSTALL_PLUGIN' ) )
    exit();

/**
 * Runs on deactivation to remove all options. We may want to add an option to delete certain data.
 *
 * @since 1.0.0
 */
  
global $wpdb, $wp_roles;
 
// REMOVE ROLES
$bbc_caps = get_option( '_bbconnect_capabilities' );
foreach ( $bbc_caps as $key => $val ) {
	if ( 'administrator' != $key ) {
		$wp_roles->remove_cap( $key, 'list_users' );
		$wp_roles->remove_cap( $key, 'add_users' );
		$wp_roles->remove_cap( $key, 'create_users' );
		$wp_roles->remove_cap( $key, 'edit_users' );
	}
}
 
// DELETE THE OPTIONS
$wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '%bbconnect%';");
//$wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '%bbconnectpanels%';");
//$wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '%bbcpay%';");
//$wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '%bbcmail%';");
//$wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '%bbcpro%';");
//$wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '%bbcgeo%';");
//$wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '%bbccontent%';");
        
?>