<?php
/**
 * Changeproof Helper Utilities
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Safely truncate text for UI display
 */
function cp_truncate( $text, $length = 120 ) {
    $text = wp_strip_all_tags( (string) $text );
    if ( mb_strlen( $text ) <= $length ) {
        return $text;
    }
    return mb_substr( $text, 0, $length ) . '…';
}

/**
 * Human-readable status label
 */
function cp_status_label( $status ) {
    $map = [
        'active'     => __( 'Active', 'changeproof' ),
        'completed'  => __( 'Completed', 'changeproof' ),
        'expired'    => __( 'Expired', 'changeproof' ),
    ];

    return $map[ $status ] ?? ucfirst( $status );
}

/**
 * CSS class for status badge
 */
function cp_status_class( $status ) {
    return 'cp-status-' . sanitize_html_class( $status );
}

/**
 * Format datetime for admin display
 */
function cp_format_time( $datetime ) {
    if ( ! $datetime ) return '—';

    $timestamp = strtotime( $datetime );
    return date_i18n(
        get_option( 'date_format' ) . ' ' . get_option( 'time_format' ),
        $timestamp
    );
}

/**
 * Returns human-friendly relative time
 */
function cp_time_ago( $datetime ) {
    if ( ! $datetime ) return '';

    $timestamp = strtotime( $datetime );
    return sprintf(
        __( '%s ago', 'changeproof' ),
        human_time_diff( $timestamp, current_time( 'timestamp' ) )
    );
}

/**
 * Prepare diff-ready content (normalized)
 * Used before diff calculation
 */
function cp_normalize_content( $content ) {
    $content = wp_strip_all_tags( $content );
    $content = preg_replace( "/\r\n|\r/", "\n", $content );
    return trim( $content );
}

/**
 * Compute a line-level diff (lightweight)
 * Returns array with added / removed / unchanged
 */
function cp_simple_diff( $before, $after ) {
    $before = explode( "\n", cp_normalize_content( $before ) );
    $after  = explode( "\n", cp_normalize_content( $after ) );

    $diff = [];

    foreach ( $after as $line ) {
        if ( ! in_array( $line, $before, true ) ) {
            $diff[] = [ 'type' => 'added', 'text' => $line ];
        }
    }

    foreach ( $before as $line ) {
        if ( ! in_array( $line, $after, true ) ) {
            $diff[] = [ 'type' => 'removed', 'text' => $line ];
        }
    }

    return $diff;
}

/**
 * Safely decode JSON stored snapshots
 */
function cp_decode_snapshot( $json ) {
    $data = json_decode( $json, true );
    return is_array( $data ) ? $data : [];
}

/**
 * Extract content excerpt from snapshot
 */
function cp_snapshot_excerpt( $snapshot ) {
    $data = cp_decode_snapshot( $snapshot );
    $text = '';

    if ( isset( $data['title'] ) ) {
        $text .= $data['title'] . "\n";
    }

    if ( isset( $data['content'] ) ) {
        $text .= $data['content'];
    }

    return cp_truncate( $text, 200 );
}
