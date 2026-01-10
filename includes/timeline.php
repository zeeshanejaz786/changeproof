<?php
/**
 * Timeline Logic (Admin Only)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * -------------------------------------------------
 * Render Timeline Page
 * -------------------------------------------------
 */
function cp_render_timeline_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'You do not have sufficient permissions to access this page.', 'changeproof' ) );
	}

	global $wpdb;

	$table_changes        = $wpdb->prefix . 'cp_changes';
	$table_investigations = $wpdb->prefix . 'cp_investigations';

	/**
	 * Pull last 100 changes with:
	 * - user display name
	 * - investigation status
	 */
	$query = "
		SELECT 
			c.*,
			u.display_name,
			i.status AS investigation_status
		FROM {$table_changes} c
		LEFT JOIN {$wpdb->users} u ON c.user_id = u.ID
		LEFT JOIN {$table_investigations} i ON c.investigation_id = i.id
		ORDER BY c.created_at DESC
		LIMIT 100
	";

	$changes = $wpdb->get_results( $query );

	include CP_PATH . 'templates/timeline-table.php';
}

/**
 * -------------------------------------------------
 * Normalize change type for UI
 * -------------------------------------------------
 */
function cp_get_change_label( $change_type ) {
	$map = [
		'activate'   => __( 'Activated', 'changeproof' ),
		'deactivate' => __( 'Deactivated', 'changeproof' ),
		'update'     => __( 'Updated', 'changeproof' ),
	];

	return $map[ $change_type ] ?? ucfirst( $change_type );
}

/**
 * -------------------------------------------------
 * UI Icon helper (Dashicons)
 * -------------------------------------------------
 */
function cp_get_change_icon( $change_type ) {
	$icons = [
		'activate'   => 'dashicons-yes-alt',
		'deactivate' => 'dashicons-dismiss',
		'update'     => 'dashicons-edit',
	];

	return $icons[ $change_type ] ?? 'dashicons-warning';
}

/**
 * -------------------------------------------------
 * Safe Data Excerpt (JSON-aware)
 * -------------------------------------------------
 */
function cp_get_data_excerpt( $data ) {
	if ( empty( $data ) ) {
		return '<em>' . esc_html__( 'No data recorded', 'changeproof' ) . '</em>';
	}

	$decoded = json_decode( $data, true );

	if ( is_array( $decoded ) ) {
		$text = implode( ' | ', array_map( 'sanitize_text_field', $decoded ) );
	} else {
		$text = wp_strip_all_tags( $data );
	}

	if ( strlen( $text ) > 160 ) {
		$text = substr( $text, 0, 160 ) . '…';
	}

	return esc_html( $text );
}
