<?php
/**
 * Database Installation Logic
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Creates or updates the custom tables for Changeproof.
 * Called via register_activation_hook in changeproof.php
 */
function cp_install_database() {
	global $wpdb;

	$charset_collate = $wpdb->get_charset_collate();

	// 1. wp_cp_investigations table
	$table_investigations = $wpdb->prefix . 'cp_investigations';
	
	// 2. wp_cp_changes table
	$table_changes = $wpdb->prefix . 'cp_changes';

	/**
	 * Schema for Investigations
	 * Tracks who started an audit session and why.
	 */
	$sql_investigations = "CREATE TABLE $table_investigations (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		user_id bigint(20) NOT NULL,
		start_time datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
		end_time datetime DEFAULT NULL,
		status varchar(20) DEFAULT 'active' NOT NULL,
		initial_note text NOT NULL,
		final_note text DEFAULT NULL,
		PRIMARY KEY  (id),
		KEY user_id (user_id)
	) $charset_collate;";

	/**
	 * Schema for Changes
	 * Stores the diff, the hash for threshold checks, and the intent reason.
	 */
	$sql_changes = "CREATE TABLE $table_changes (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		investigation_id bigint(20) DEFAULT NULL,
		user_id bigint(20) NOT NULL,
		object_type varchar(50) NOT NULL,
		object_id varchar(100) NOT NULL,
		change_type varchar(50) NOT NULL,
		before_data longtext DEFAULT NULL,
		after_data longtext DEFAULT NULL,
		reason text NOT NULL,
		hash varchar(64) NOT NULL,
		created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
		PRIMARY KEY  (id),
		KEY investigation_id (investigation_id),
		KEY user_id (user_id),
		KEY object_id (object_id)
	) $charset_collate;";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	
	// Execute the queries
	dbDelta( $sql_investigations );
	dbDelta( $sql_changes );

	// Store version in options
	add_option( 'cp_db_version', CP_VERSION );
	update_option( 'cp_db_version', CP_VERSION );
}