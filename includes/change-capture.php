<?php
/**
 * Change Capture Logic
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * -------------------------------------------------
 * AJAX: Save Intent (per-save)
 * -------------------------------------------------
 */
add_action( 'wp_ajax_cp_submit_intent', 'cp_ajax_submit_intent' );
function cp_ajax_submit_intent() {
    check_ajax_referer( 'cp_intent_nonce', 'security' );

    $reason = isset( $_POST['reason'] ) ? sanitize_text_field( $_POST['reason'] ) : '';
    if ( strlen( $reason ) < 5 ) {
        wp_send_json_error( 'Intent too short.' );
    }

    update_user_meta(
        get_current_user_id(),
        '_cp_pending_intent',
        $reason
    );

    wp_send_json_success();
}

/**
 * -------------------------------------------------
 * CORE RECORDER (single source of truth)
 * -------------------------------------------------
 */
function cp_record_change( array $args ) {
    global $wpdb;

    $defaults = [
        'investigation_id' => null,
        'user_id'          => get_current_user_id(),
        'object_type'      => '',
        'object_id'        => '',
        'change_type'      => 'update',
        'before_data'      => null,
        'after_data'       => null,
        'reason'           => null,
        'hash'             => null,
        'created_at'       => current_time( 'mysql' ),
    ];

    $data = wp_parse_args( $args, $defaults );

    // Hard stop if object is not defined
    if ( empty( $data['object_type'] ) || empty( $data['object_id'] ) ) {
        return;
    }

    $wpdb->insert(
        $wpdb->prefix . 'cp_changes',
        $data
    );
}

/**
 * -------------------------------------------------
 * UNIVERSAL POST CAPTURE
 * Handles Gutenberg, Classic, REST, Quick Edit
 * -------------------------------------------------
 */
add_action( 'save_post', 'cp_capture_post_change', 100, 3 );
function cp_capture_post_change( $post_id, $post, $update ) {

    // Safety guards
    if ( ! $update ) return;
    if ( wp_is_post_revision( $post_id ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! $post instanceof WP_Post ) return;

    $user_id = get_current_user_id();

    // Respect excluded post types
    $excluded = (array) get_option( 'cp_excluded_post_types', [] );
    if ( in_array( $post->post_type, $excluded, true ) ) {
        return;
    }

    // Fetch intent + investigation
    $intent        = get_user_meta( $user_id, '_cp_pending_intent', true );
    $investigation = cp_get_active_investigation_id( $user_id );

    // Nothing to log
    if ( ! $intent && ! $investigation ) {
        return;
    }

    // Build snapshot (v1 = after only)
    $content = [
        'title'   => $post->post_title,
        'content' => $post->post_content,
        'status'  => $post->post_status,
    ];

    $serialized = wp_json_encode( $content );

    cp_record_change( [
        'investigation_id' => $investigation,
        'user_id'          => $user_id,
        'object_type'      => $post->post_type,   // ← FIXED
        'object_id'        => (string) $post_id,
        'change_type'      => 'update',
        'after_data'       => $serialized,
        'reason'           => $intent ?: __( 'Investigation mode active', 'changeproof' ),
        'hash'             => md5( $serialized ),
    ] );

    // 🔥 CRITICAL: intent is one-time only
    delete_user_meta( $user_id, '_cp_pending_intent' );
}

/**
 * -------------------------------------------------
 * PLUGIN ACTIVATION / DEACTIVATION
 * (System changes – bypass intent)
 * -------------------------------------------------
 */
add_action( 'activated_plugin', 'cp_capture_plugin_activation', 10, 1 );
function cp_capture_plugin_activation( $plugin ) {
    cp_record_change( [
        'object_type' => 'plugin',
        'object_id'   => $plugin,
        'change_type' => 'activate',
        'reason'      => __( 'Plugin activated', 'changeproof' ),
    ] );
}

add_action( 'deactivated_plugin', 'cp_capture_plugin_deactivation', 10, 1 );
function cp_capture_plugin_deactivation( $plugin ) {
    cp_record_change( [
        'object_type' => 'plugin',
        'object_id'   => $plugin,
        'change_type' => 'deactivate',
        'reason'      => __( 'Plugin deactivated', 'changeproof' ),
    ] );
}
