<?php
/**
 * Changeproof Uninstall
 * 
 * This file runs when the plugin is deleted via the WordPress Admin.
 * It removes all options, user metadata, and custom tables.
 */

// If uninstall not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

/**
 * 1. Remove Plugin Options
 */
$options = [
	'cp_db_version',
	'cp_enable_intent',
	'cp_content_threshold',
	'cp_excluded_post_types',
];

foreach ( $options as $option ) {
	delete_option( $option );
}

/**
 * 2. Remove User Metadata
 */
delete_metadata( 'user', 0, '_cp_active_investigation', '', true );
delete_metadata( 'user', 0, '_cp_pending_intent', '', true );

/**
 * 3. Remove Database Tables
 */
$tables = [
	$wpdb->prefix . 'cp_investigations',
	$wpdb->prefix . 'cp_changes',
];

foreach ( $tables as $table ) {
	$wpdb->query( "DROP TABLE IF EXISTS {$table}" );
}
