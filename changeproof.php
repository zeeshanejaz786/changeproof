<?php
/**
 * Plugin Name: Changeproof
 * Description: High-integrity change tracking with forced intent and investigation modes.
 * Version: 1.0.1
 * Author: Zeeshan Qureshi
 * Author URI: https://zeeshan.qureshi.co
 * Text Domain: changeproof
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Constants
 */
define( 'CP_VERSION', '1.0.1' );
define( 'CP_PATH', plugin_dir_path( __FILE__ ) );
define( 'CP_URL', plugin_dir_url( __FILE__ ) );

/**
 * Includes
 */
require_once CP_PATH . 'includes/helpers.php';
require_once CP_PATH . 'includes/install.php';
require_once CP_PATH . 'includes/change-capture.php';
require_once CP_PATH . 'includes/investigations.php';
require_once CP_PATH . 'includes/timeline.php';
require_once CP_PATH . 'includes/settings.php';

/**
 * Activation
 */
register_activation_hook( __FILE__, 'cp_activate_plugin' );
function cp_activate_plugin() {
	if ( function_exists( 'cp_install_database' ) ) {
		cp_install_database();
	}
}
