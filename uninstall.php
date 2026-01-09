<?php
/**
 * Changeproof Uninstall
 * 
 * This file runs when the plugin is deleted via the WordPress Admin.
 * It removes all options and user metadata.
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
	'cp_excluded_post_types'
];

foreach ( $options as $option ) {
	delete_option( $option );
}

/**
 * 2. Remove User Metadata
 * Cleans up active investigations and pending intents for all users.
 */
delete_metadata( 'user', 0, '_cp_active_investigation', '', true );
delete_metadata( 'user', 0, '_cp_pending_intent', '', true );

/**
 * 3. Database Tables
 * 
 * Since the plugin handles sensitive audit data, we drop the tables 
 * only during a full uninstall.
 */
$table_investigations = $wpdb->prefix . 'cp_investigations';
$table_changes        = $wpdb->prefix . 'cp_changes';

$wpdb->query( "DROP TABLE IF EXISTS $table_investigations" );
$wpdb->query( "DROP TABLE IF EXISTS $table_changes" );
