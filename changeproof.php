<?php
/**
 * Plugin Name: Changeproof
 * Description: High-integrity change tracking with forced intent and investigation modes.
 * Version: 1.0.0
 * Author: Custom Dev
 * Text Domain: changeproof
 * Namespace: cp_
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
 * We check if files exist before loading to prevent Fatal Errors.
 */
$cp_includes = [
	'includes/helpers.php',
	'includes/install.php',
	'includes/investigations.php',
	'includes/change-capture.php',
	'includes/timeline.php',
	'includes/settings.php',
];

foreach ( $cp_includes as $file ) {
	$full_path = CP_PATH . $file;
	if ( file_exists( $full_path ) ) {
		require_once $full_path;
	} else {
		// If a file is missing, display an admin notice instead of crashing
		add_action( 'admin_notices', function() use ( $file ) {
			echo '<div class="error"><p>';
			printf( esc_html__( 'Changeproof Error: Missing required file: %s', 'changeproof' ), '<code>' . esc_html( $file ) . '</code>' );
			echo '</p></div>';
		});
	}
}

/**
 * 3. Register Activation Hook
 * Logic is located in includes/install.php
 */
register_activation_hook( __FILE__, 'cp_activate_plugin' );
function cp_activate_plugin() {
	if ( function_exists( 'cp_install_database' ) ) {
		cp_install_database();
	}
}

/**
 * 4. Register Admin Menu
 */
add_action( 'admin_menu', 'cp_register_admin_menu' );
function cp_register_admin_menu() {
	// Main Page: Timeline
	add_menu_page(
		__( 'Changeproof', 'changeproof' ),
		__( 'Changeproof', 'changeproof' ),
		'manage_options',
		'changeproof',
		'cp_render_timeline_page', // in includes/timeline.php
		'dashicons-shield-check',
		80
	);

	// Subpage: Investigations
	add_submenu_page(
		'changeproof',
		__( 'Investigations', 'changeproof' ),
		__( 'Investigations', 'changeproof' ),
		'manage_options',
		'cp-investigations',
		'cp_render_investigations_page' // in includes/investigations.php
	);

	// Subpage: Settings
	add_submenu_page(
		'changeproof',
		__( 'Settings', 'changeproof' ),
		__( 'Settings', 'changeproof' ),
		'manage_options',
		'cp-settings',
		'cp_render_settings_page' // in includes/settings.php
	);
}

/**
 * 5. Enqueue Scripts Conditionally
 */
add_action( 'admin_enqueue_scripts', 'cp_enqueue_admin_assets' );
function cp_enqueue_admin_assets( $hook ) {
	$screen = get_current_screen();

	// A. Styles for Changeproof Admin Pages
	if ( strpos( $hook, 'changeproof' ) !== false ) {
		wp_enqueue_style( 'cp-admin-css', CP_URL . 'assets/css/admin.css', [], CP_VERSION );
		wp_enqueue_script( 'cp-admin-ui', CP_URL . 'assets/js/admin-ui.js', [ 'jquery' ], CP_VERSION, true );
		
		// Ensure nonce is available for Investigation start/end buttons
		wp_localize_script( 'cp-admin-ui', 'cp_investigation_data', [
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'cp_investigation_nonce' )
		]);
	}

	// B. Intent Modal for Post Editors
	if ( $screen && 'post' === $screen->base ) {
		wp_enqueue_style( 'cp-admin-css', CP_URL . 'assets/css/admin.css', [], CP_VERSION );
		wp_enqueue_script( 'cp-editor-modal', CP_URL . 'assets/js/editor-modal.js', [ 'jquery' ], CP_VERSION, true );

		// This data is critical for the JS modal and AJAX calls
		wp_localize_script( 'cp-editor-modal', 'cp_data', [
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'cp_intent_nonce' ), // Must match includes/change-capture.php
			'enforce'  => get_option( 'cp_enable_intent', '1' ),
		] );
	}
}

/**
 * 6. Intent Modal HTML Injector
 */
add_action( 'admin_footer', 'cp_inject_intent_modal' );
function cp_inject_intent_modal() {
	$screen = get_current_screen();
	// Only inject if we are on the post edit screen and the template exists
	if ( $screen && 'post' === $screen->base ) {
		$template_path = CP_PATH . 'templates/modal-intent.php';
		if ( file_exists( $template_path ) ) {
			include $template_path;
		}
	}
}