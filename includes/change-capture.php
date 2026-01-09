<?php
/**
 * Change Capture Engine
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * AJAX Handler: Store Intent
 */
add_action( 'wp_ajax_cp_submit_intent', 'cp_handle_ajax_intent' );
function cp_handle_ajax_intent() {
    check_ajax_referer( 'cp_intent_nonce', 'security' );

    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_send_json_error( 'Permission denied' );
    }

    $reason = isset( $_POST['reason'] ) ? sanitize_text_field( $_POST['reason'] ) : '';
    
    // Crucial: Clear any old intent first
    delete_user_meta( get_current_user_id(), '_cp_pending_intent' );
    
    // Store new intent
    $stored = update_user_meta( get_current_user_id(), '_cp_pending_intent', $reason );

    if ( $stored ) {
        wp_send_json_success();
    } else {
        // Even if update_user_meta returns false (because the value didn't change), 
        // we should still succeed if the reason is there.
        wp_send_json_success();
    }
}

/**
 * Core Capture Hook
 */
add_action( 'post_updated', 'cp_capture_post_update', 10, 3 );
function cp_capture_post_update( $post_ID, $post_after, $post_before ) {
    // 1. Safety Checks
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( wp_is_post_revision( $post_ID ) ) return;
    
    // 2. Hash Comparison (Did anything actually change?)
    $old_content = $post_before->post_title . $post_before->post_content;
    $new_content = $post_after->post_title . $post_after->post_content;
    
    $old_hash = hash( 'sha256', $old_content );
    $new_hash = hash( 'sha256', $new_content );

    if ( $old_hash === $new_hash ) {
        // No meaningful change to record.
        return;
    }

    // 3. Retrieve Intent
    $user_id = get_current_user_id();
    // Use a direct database query for the meta to bypass any cache issues
    $intent = get_user_meta( $user_id, '_cp_pending_intent', true );

    // If no intent found, check if we are in an active investigation
    $investigation_id = cp_get_active_investigation_id( $user_id );

    if ( empty( $intent ) && ! $investigation_id ) {
        // Log this to wp-content/debug.log if enabled
        error_log("Changeproof: Post $post_ID updated but no intent or active investigation found.");
        return;
    }

    // 4. Insert into Database
    global $wpdb;
    $table = $wpdb->prefix . 'cp_changes';

    $result = $wpdb->insert(
        $table,
        [
            'investigation_id' => $investigation_id ? $investigation_id : null,
            'user_id'          => $user_id,
            'object_type'      => 'post',
            'object_id'        => (string)$post_ID,
            'change_type'      => 'update',
            'before_data'      => $old_content,
            'after_data'       => $new_content,
            'reason'           => !empty($intent) ? $intent : __('Active Investigation Session', 'changeproof'),
            'hash'             => $new_hash,
            'created_at'       => current_time( 'mysql' )
        ],
        [ '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ]
    );

    if ( false === $result ) {
        error_log( "Changeproof Database Error: " . $wpdb->last_error );
    } else {
        // Success! Clean up the intent for the next save
        delete_user_meta( $user_id, '_cp_pending_intent' );
    }
}