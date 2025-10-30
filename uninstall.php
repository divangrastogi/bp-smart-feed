<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @since 1.0.0
 * @package BPSFC
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Check if user wants to keep data.
$keep_data = get_option( 'bpsfc_keep_data_on_uninstall', 'no' );

if ( 'no' === $keep_data ) {
    // Drop custom tables.
    global $wpdb;
    
    $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}bpsfc_activity_scores" );
    $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}bpsfc_user_interests" );
    
    // Remove all plugin options.
    $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'bpsfc_%'" );
    
    // Remove user meta.
    $wpdb->query( "DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE 'bpsfc_%'" );
    
    // Clear transients.
    $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_bpsfc_%'" );
    $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_bpsfc_%'" );
}
