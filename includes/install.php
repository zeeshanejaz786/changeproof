<?php
/**
 * Database Installation Logic
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Creates or updates the custom tables for Changeproof.
 */
function cp_install_database() {
    global $wpdb;

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    $charset_collate = $wpdb->get_charset_collate();

    /**
     * TABLE: cp_investigations
     */
    $table_investigations = $wpdb->prefix . 'cp_investigations';

    $sql_investigations = "
        CREATE TABLE $table_investigations (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT UNSIGNED NOT NULL,
            start_time DATETIME NOT NULL,
            end_time DATETIME NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'active',
            initial_note TEXT NULL,
            final_note TEXT NULL,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY status (status),
            KEY start_time (start_time)
        ) $charset_collate;
    ";

    /**
     * TABLE: cp_changes
     */
    $table_changes = $wpdb->prefix . 'cp_changes';

    $sql_changes = "
        CREATE TABLE $table_changes (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            investigation_id BIGINT UNSIGNED NULL,
            user_id BIGINT UNSIGNED NOT NULL,
            object_type VARCHAR(50) NOT NULL,
            object_id VARCHAR(191) NOT NULL,
            change_type VARCHAR(50) NOT NULL DEFAULT 'update',
            before_data LONGTEXT NULL,
            after_data LONGTEXT NULL,
            reason TEXT NULL,
            hash CHAR(32) NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY investigation_id (investigation_id),
            KEY object_type (object_type),
            KEY created_at (created_at)
        ) $charset_collate;
    ";

    dbDelta( $sql_investigations );
    dbDelta( $sql_changes );

    update_option( 'cp_db_version', CP_VERSION );
}
