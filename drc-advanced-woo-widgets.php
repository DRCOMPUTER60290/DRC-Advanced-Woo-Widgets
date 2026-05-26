<?php
/**
 * Plugin Name: DRC Advanced Woo Widgets
 * Description: Advanced WooCommerce widgets for Elementor with Magical Shop Builder compatibility. Features: Best Selling, Deal of Week, Trending, Popular, Recently Sold, Flash Sale products.
 * Version: 1.0.0
 * Author: DRC Digital
 * Author URI: https://drc.digital
 * Text Domain: drc-advanced-woo-widgets
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * WC requires at least: 7.0
 * WC tested up to: 8.0
 */

defined( 'ABSPATH' ) || exit;

// Define constants
define( 'DRC_AWW_PLUGIN_FILE', __FILE__ );
define( 'DRC_AWW_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'DRC_AWW_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'DRC_AWW_PLUGIN_VERSION', '1.0.0' );
define( 'DRC_AWW_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Autoloader
spl_autoload_register( function ( $class ) {
	if ( strpos( $class, 'DRC\\AWW\\' ) !== 0 ) {
		return;
	}

	$class = substr( $class, 8 );
	$path = str_replace( '\\', DIRECTORY_SEPARATOR, $class ) . '.php';
	$file = DRC_AWW_PLUGIN_DIR . 'includes/' . $path;

	if ( file_exists( $file ) ) {
		require_once $file;
	}
} );

// Activation/Deactivation hooks
register_activation_hook( __FILE__, 'DRC\\AWW\\Core\\Activator::activate' );
register_deactivation_hook( __FILE__, 'DRC\\AWW\\Core\\Activator::deactivate' );

// Load plugin
add_action( 'plugins_loaded', function () {
	DRC\AWW\Core\Plugin::instance();
} );

// Handle direct access prevention
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once DRC_AWW_PLUGIN_DIR . 'includes/Core/class-cli.php';
}