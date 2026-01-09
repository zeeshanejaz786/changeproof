<?php
/**
 * Investigation Mode Logic
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 1. CORE LOGIC FUNCTIONS
 */

/**
 * Get the current active investigation ID for a specific user.
 */
function cp_get_active_investigation_id( $user_id ) {
	$active_id = get_user_meta( $user_id, '_cp_active_investigation', true );
	return ( $active_id ) ? (int) $active_id : false;
}

/**
 * Start a new investigation for a user.
 */
function cp_start_investigation( $user_id, $data ) {
	global $wpdb;

	if ( cp_get_active_investigation_id( $user_id ) ) {
		return new WP_Error( 'cp_already_active', __( 'An active investigation is already running.', 'changeproof' ) );
	}

	$table = $wpdb->prefix . 'cp_investigations';
	
	$inserted = $wpdb->insert(
		$table,
		[
			'user_id'      => $user_id,
			'start_time'   => current_time( 'mysql' ),
			'status'       => 'active',
			'initial_note' => sanitize_textarea_field( $data['initial_note'] )
		],
		[ '%d', '%s', '%s', '%s' ]
	);

	if ( false === $inserted ) {
		return new WP_Error( 'db_error', __( 'Database error.', 'changeproof' ) );
	}

	$investigation_id = $wpdb->insert_id;
	update_user_meta( $user_id, '_cp_active_investigation', $investigation_id );

	return $investigation_id;
}

/**
 * End an active investigation.
 */
function cp_end_investigation( $user_id, $status, $note ) {
	global $wpdb;
	$active_id = cp_get_active_investigation_id( $user_id );

	if ( ! $active_id ) return false;

	$wpdb->update(
		$wpdb->prefix . 'cp_investigations',
		[
			'end_time'   => current_time( 'mysql' ),
			'status'     => sanitize_text_field( $status ),
			'final_note' => sanitize_textarea_field( $note )
		],
		[ 'id' => $active_id ]
	);

	delete_user_meta( $user_id, '_cp_active_investigation' );
	return true;
}

/**
 * 2. UI CONTROLLER
 */
function cp_render_investigations_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'Unauthorized.', 'changeproof' ) );
	}

	global $wpdb;
	$user_id = get_current_user_id();
	$active_id = cp_get_active_investigation_id( $user_id );
	
	$history = $wpdb->get_results( $wpdb->prepare(
		"SELECT * FROM {$wpdb->prefix}cp_investigations WHERE user_id = %d ORDER BY start_time DESC",
		$user_id
	));

	include CP_PATH . 'templates/investigations-ui.php';
}

/**
 * 3. AJAX HANDLERS
 */
add_action( 'wp_ajax_cp_ajax_start_investigation', 'cp_handle_ajax_start_investigation' );
function cp_handle_ajax_start_investigation() {
	check_ajax_referer( 'cp_investigation_nonce', 'security' );
	if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( 'Forbidden' );

	$note = isset( $_POST['note'] ) ? $_POST['note'] : '';
	$result = cp_start_investigation( get_current_user_id(), [ 'initial_note' => $note ] );

	if ( is_wp_error( $result ) ) {
		wp_send_json_error( $result->get_error_message() );
	}
	wp_send_json_success();
}

add_action( 'wp_ajax_cp_ajax_end_investigation', 'cp_handle_ajax_end_investigation' );
function cp_handle_ajax_end_investigation() {
	check_ajax_referer( 'cp_investigation_nonce', 'security' );
	if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( 'Forbidden' );

	$note = isset( $_POST['note'] ) ? $_POST['note'] : '';
	$success = cp_end_investigation( get_current_user_id(), 'completed', $note );

	if ( $success ) wp_send_json_success();
	wp_send_json_error( 'No active investigation found.' );
}