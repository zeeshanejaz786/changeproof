<?php
/**
 * Plugin Name: Changeproof
 * Description: High-integrity change tracking with forced intent and investigation modes.
 * Version: 1.0.1
 * Author: Zeeshan Qureshi
 * Author URI: https://zeeshan.qureshi.co
 * Text Domain: changeproof
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'CP_VERSION', '1.0' );
define( 'CP_PATH', plugin_dir_path( __FILE__ ) );

// Load all core files
$files = [
    'includes/helpers.php',
    'includes/install.php',
    'includes/change-capture.php',
    'includes/investigations.php',
    'includes/timeline.php',
    'includes/settings.php',
];

foreach ( $files as $file ) {
    $path = CP_PATH . $file;
    if ( file_exists( $path ) ) require_once $path;
}

// Install database on activation
register_activation_hook( __FILE__, 'cp_install_database' );

// Admin menu
add_action( 'admin_menu', function() {
    add_menu_page(
        __( 'Changeproof', 'changeproof' ),
        __( 'Changeproof', 'changeproof' ),
        'manage_options',
        'changeproof',
        'cp_render_timeline_page',
        'dashicons-shield-check',
        80
    );

    add_submenu_page(
        'changeproof',
        __( 'Timeline', 'changeproof' ),
        __( 'Timeline', 'changeproof' ),
        'manage_options',
        'changeproof',
        'cp_render_timeline_page'
    );

    add_submenu_page(
        'changeproof',
        __( 'Investigations', 'changeproof' ),
        __( 'Investigations', 'changeproof' ),
        'manage_options',
        'cp-investigations',
        'cp_render_investigations_page'
    );

    add_submenu_page(
        'changeproof',
        __( 'Settings', 'changeproof' ),
        __( 'Settings', 'changeproof' ),
        'manage_options',
        'cp-settings',
        'cp_render_settings_page'
    );
});

// Enqueue admin JS/CSS
add_action( 'admin_enqueue_scripts', function( $hook ) {
    if ( strpos( $hook, 'changeproof' ) === false ) return;

    wp_enqueue_style( 'cp-admin-css', plugins_url( 'assets/css/admin.css', __FILE__ ), [], CP_VERSION );
    wp_enqueue_script( 'cp-admin-js', plugins_url( 'assets/js/admin.js', __FILE__ ), ['jquery'], CP_VERSION, true );

    wp_localize_script( 'cp-admin-js', 'cp_data', [
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'cp_intent_nonce' )
    ]);
});