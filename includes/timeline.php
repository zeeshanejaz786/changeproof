<?php
/**
 * Timeline Logic (Admin Only)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders the main Timeline page.
 * Called by the menu registration in changeproof.php
 */
function cp_render_timeline_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'You do not have sufficient permissions to access this page.', 'changeproof' ) );
	}

	global $wpdb;
	$table_changes = $wpdb->prefix . 'cp_changes';

	/**
	 * 1. Query last 100 changes
	 * We join with the users table to get display names efficiently.
	 */
	$query = "
		SELECT c.*, u.display_name 
		FROM $table_changes c
		LEFT JOIN {$wpdb->users} u ON c.user_id = u.ID
		ORDER BY c.created_at DESC 
		LIMIT 100
	";

	$changes = $wpdb->get_results( $query );

	// 2. Include the Template
	// We pass $changes to the template file.
	include CP_PATH . 'templates/timeline-table.php';
}

/**
 * Helper to generate a simple "Diff" excerpt for the timeline.
 * Since we aren't using a heavy diffing library, we show a snapshot.
 */
function cp_get_data_excerpt( $data ) {
	if ( empty( $data ) ) {
		return '<em>' . __( 'None / Empty', 'changeproof' ) . '</em>';
	}

	// Strip tags to show text-only excerpt
	$clean = wp_strip_all_tags( $data );
	
	if ( strlen( $clean ) > 200 ) {
		return esc_html( substr( $clean, 0, 200 ) ) . '...';
	}

	return esc_html( $clean );
}