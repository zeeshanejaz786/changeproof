<?php
/**
 * Plugin Name: Changeproof
 * Description: High-integrity change tracking with forced intent and investigation modes.
 * Version: 1.0.0
 * Author: Zeeshan Qureshi
 * Text Domain: changeproof
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * 1. Define Constants
 */
define( 'CP_VERSION', '1.0.0' );
define( 'CP_PATH', plugin_dir_path( __FILE__ ) );
define( 'CP_URL', plugin_dir_url( __FILE__ ) );

/**
 * 2. Load Includes
 */
require_once CP_PATH . 'includes/helpers.php';
require_once CP_PATH . 'includes/install.php';
require_once CP_PATH . 'includes/investigations.php';
require_once CP_PATH . 'includes/change-capture.php';
require_once CP_PATH . 'includes/timeline.php';
require_once CP_PATH . 'includes/settings.php';

/**
 * 3. Register Activation Hook
 * Logic is handled in includes/install.php
 */
register_activation_hook( __FILE__, 'cp_activate_plugin' );

/**
 * 4. Register Admin Menu
 */
add_action( 'admin_menu', 'cp_register_admin_menu' );
function cp_register_admin_menu() {
	// Main Timeline Page
	add_menu_page(
		__( 'Changeproof', 'changeproof' ),
		__( 'Changeproof', 'changeproof' ),
		'manage_options',
		'changeproof',
		'cp_render_timeline_page', // Found in includes/timeline.php
		'dashicons-shield-check',
		80
	);

	// Investigations Submenu
	add_submenu_page(
		'changeproof',
		__( 'Investigations', 'changeproof' ),
		__( 'Investigations', 'changeproof' ),
		'manage_options',
		'cp-investigations',
		'cp_render_investigations_page' // Found in includes/investigations.php
	);

	// Settings Submenu
	add_submenu_page(
		'changeproof',
		__( 'Settings', 'changeproof' ),
		__( 'Settings', 'changeproof' ),
		'manage_options',
		'cp-settings',
		'cp_render_settings_page' // Found in includes/settings.php
	);
}

/**
 * 5. Enqueue Scripts Conditionally
 */
add_action( 'admin_enqueue_scripts', 'cp_enqueue_admin_assets' );
function cp_enqueue_admin_assets( $hook ) {
	$screen = get_current_screen();

	// Global Admin UI (Load on all plugin pages)
	if ( strpos( $hook, 'changeproof' ) !== false ) {
		wp_enqueue_style( 'cp-admin-css', CP_URL . 'assets/css/admin.css', [], CP_VERSION );
		wp_enqueue_script( 'cp-admin-ui', CP_URL . 'assets/js/admin-ui.js', [ 'jquery' ], CP_VERSION, true );
	}

	// Editor Modal (Load only on Post/Page editors)
	if ( $screen && 'post' === $screen->base ) {
		wp_enqueue_style( 'cp-admin-css', CP_URL . 'assets/css/admin.css', [], CP_VERSION );
		wp_enqueue_script( 'cp-editor-modal', CP_URL . 'assets/js/editor-modal.js', [ 'jquery' ], CP_VERSION, true );

		// Pass data to JS for intent enforcement
		wp_localize_script( 'cp-editor-modal', 'cp_data', [
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'cp_intent_nonce' ),
			'enforce'  => get_option( 'cp_enable_intent', '1' ),
		] );
	}
}