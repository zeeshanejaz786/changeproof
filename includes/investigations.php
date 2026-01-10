<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Returns active investigation ID for a user, auto-expiring after 24 hours.
 */
function cp_get_active_investigation_id( $user_id ) {
    $active_id = get_user_meta( $user_id, '_cp_active_investigation', true );
    if ( ! $active_id ) return false;

    global $wpdb;
    $row = $wpdb->get_row( $wpdb->prepare(
        "SELECT id, start_time FROM {$wpdb->prefix}cp_investigations WHERE id = %d LIMIT 1",
        $active_id
    ));

    // Orphaned user_meta?
    if ( ! $row ) {
        delete_user_meta( $user_id, '_cp_active_investigation' );
        return false;
    }

    // Auto-expire
    if ( strtotime( $row->start_time ) < strtotime( '-24 hours' ) ) {
        cp_end_investigation( $user_id, 'expired', __('Auto-closed after 24 hours.', 'changeproof') );
        return false;
    }

    return (int) $row->id;
}

/**
 * Start an investigation
 */
function cp_start_investigation( $user_id, $note = '' ) {
    if ( cp_get_active_investigation_id( $user_id ) ) return false;

    global $wpdb;
    $inserted = $wpdb->insert(
        $wpdb->prefix . 'cp_investigations',
        [
            'user_id'      => $user_id,
            'start_time'   => current_time( 'mysql' ),
            'status'       => 'active',
            'initial_note' => sanitize_textarea_field( $note )
        ]
    );

    if ( ! $inserted ) return false;

    $id = $wpdb->insert_id;
    update_user_meta( $user_id, '_cp_active_investigation', $id );

    return $id;
}

/**
 * End an investigation
 */
function cp_end_investigation( $user_id, $status = 'completed', $note = '' ) {
    $active_id = cp_get_active_investigation_id( $user_id );
    if ( ! $active_id ) return false;

    global $wpdb;
    $updated = $wpdb->update(
        $wpdb->prefix . 'cp_investigations',
        [
            'end_time'   => current_time( 'mysql' ),
            'status'     => sanitize_text_field( $status ),
            'final_note' => sanitize_textarea_field( $note )
        ],
        [ 'id' => $active_id ]
    );

    delete_user_meta( $user_id, '_cp_active_investigation' );

    return (bool) $updated;
}

/**
 * Render Investigation UI
 */
function cp_render_investigations_page() {
    if ( ! current_user_can( 'manage_options' ) ) wp_die( __('Unauthorized.', 'changeproof') );

    global $wpdb;
    $user_id   = get_current_user_id();
    $active_id = cp_get_active_investigation_id( $user_id );

    // History: current user only for now
    $history = $wpdb->get_results( $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}cp_investigations WHERE user_id = %d ORDER BY start_time DESC LIMIT 50",
        $user_id
    ));

    $template = CP_PATH . 'templates/investigations-ui.php';
    if ( file_exists( $template ) ) {
        include $template;
    } else {
        echo '<div class="wrap"><h1>Error</h1><p>Missing template: <code>investigations-ui.php</code></p></div>';
    }
}

/**
 * AJAX: Start Investigation
 */
add_action( 'wp_ajax_cp_ajax_start_investigation', function() {
    check_ajax_referer( 'cp_investigation_nonce', 'security' );
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

    $note = isset($_POST['note']) ? sanitize_textarea_field($_POST['note']) : '';
    $id = cp_start_investigation( get_current_user_id(), $note );

    if ( $id ) wp_send_json_success([ 'id' => $id ]);
    wp_send_json_error([ 'message' => 'Could not start investigation.' ]);
});

/**
 * AJAX: End Investigation
 */
add_action( 'wp_ajax_cp_ajax_end_investigation', function() {
    check_ajax_referer( 'cp_investigation_nonce', 'security' );
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

    $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : 'completed';
    $note   = isset($_POST['note']) ? sanitize_textarea_field($_POST['note']) : '';

    if ( cp_end_investigation( get_current_user_id(), $status, $note ) ) {
        wp_send_json_success();
    } else {
        wp_send_json_error([ 'message' => 'No active investigation found.' ]);
    }
});
