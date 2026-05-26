<?php
/**
 * Main plugin class
 */

namespace DRC\AWW\Core;

defined( 'ABSPATH' ) || exit;

use DRC\AWW\Admin\Admin;
use DRC\AWW\Ajax\Ajax_Handler;
use DRC\AWW\Cache\Product_Cache;
use DRC\AWW\Helpers\Template_Loader;
use DRC\AWW\Widgets\Widget_Manager;

class Plugin {

	private static $instance = null;

	private $version = DRC_AWW_PLUGIN_VERSION;

	public static function instance(): Plugin {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->init_hooks();
		$this->load_components();
		$this->clear_stale_transients();
	}

	private function init_hooks(): void {
		add_action( 'init', [ $this, 'load_textdomain' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'elementor/preview/enqueue_scripts', [ $this, 'enqueue_editor_preview_scripts' ] );
		add_action( 'elementor/init', [ $this, 'elementor_init' ] );
	}

	public function load_textdomain(): void {
		load_plugin_textdomain( 'drc-advanced-woo-widgets', false, dirname( DRC_AWW_PLUGIN_BASENAME ) . '/languages' );
	}

	public function enqueue_scripts(): void {
		wp_enqueue_style(
			'drc-aww-styles',
			DRC_AWW_PLUGIN_URL . 'assets/css/frontend.css',
			[],
			$this->version
		);

		wp_enqueue_script(
			'drc-aww-swiper',
			'https://cdn.jsdelivr.net/npm/swiper@8.4.7/swiper-bundle.min.js',
			[],
			'8.4.7',
			true
		);

		wp_enqueue_script(
			'drc-aww-scripts',
			DRC_AWW_PLUGIN_URL . 'assets/js/frontend.js',
			[ 'jquery', 'drc-aww-swiper' ],
			$this->version,
			true
		);

		wp_localize_script( 'drc-aww-scripts', 'drc_aww_ajax', [
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'drc_aww_nonce' ),
		] );

		wp_localize_script( 'drc-aww-scripts', 'drc_aww_i18n', [
			'loading'   => __( 'Loading...', 'drc-advanced-woo-widgets' ),
			'load_more' => __( 'Load More', 'drc-advanced-woo-widgets' ),
			'expired'   => __( 'Sale ended', 'drc-advanced-woo-widgets' ),
			'error'     => __( 'Error', 'drc-advanced-woo-widgets' ),
		] );
	}

	public function enqueue_editor_preview_scripts(): void {
		wp_enqueue_style(
			'drc-aww-styles',
			DRC_AWW_PLUGIN_URL . 'assets/css/frontend.css',
			[],
			$this->version
		);

		wp_enqueue_script(
			'drc-aww-swiper',
			'https://cdn.jsdelivr.net/npm/swiper@8.4.7/swiper-bundle.min.js',
			[],
			'8.4.7',
			true
		);

		wp_enqueue_script(
			'drc-aww-scripts',
			DRC_AWW_PLUGIN_URL . 'assets/js/frontend.js',
			[ 'jquery', 'drc-aww-swiper' ],
			$this->version,
			true
		);
	}

	public function elementor_init(): void {
		if ( ! defined( 'ELEMENTOR_VERSION' ) ) {
			add_action( 'admin_notices', [ $this, 'elementor_missing_notice' ] );
			return;
		}

		Widget_Manager::instance();
	}

	public function elementor_missing_notice(): void {
		echo '<div class="error"><p>' .
			__( 'DRC Advanced Woo Widgets requires Elementor to be installed and activated.', 'drc-advanced-woo-widgets' ) .
			'</p></div>';
	}

	/**
	 * Clear stale transients that may have cached empty product results
	 * before the WooCommerce fallback was added.
	 */
	private function clear_stale_transients(): void {
		$cleared_version = get_option( 'drc_aww_transients_cleared_version', '' );
		if ( $cleared_version === $this->version ) {
			return;
		}

		global $wpdb;

		// Delete all DRC product cache transients
		$wpdb->query(
			"DELETE FROM {$wpdb->options}
			 WHERE option_name LIKE '_site_transient_drc_aww_%'
			 OR option_name LIKE '_site_transient_timeout_drc_aww_%'"
		);

		update_option( 'drc_aww_transients_cleared_version', $this->version );
	}

	private function load_components(): void {
		// Load Admin
		if ( is_admin() ) {
			Admin::instance();
		}

		// Load Ajax handlers
		Ajax_Handler::instance();

		// Load cache system
		Product_Cache::instance();

		// Load helpers
		Template_Loader::instance();
	}
}