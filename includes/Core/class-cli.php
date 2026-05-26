<?php
/**
 * WP-CLI commands for DRC Advanced Woo Widgets
 */

namespace DRC\AWW\Core;

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

class CLI {

	public function __construct() {
		\WP_CLI::add_command( 'drc-aww', __CLASS__ );
	}

	/**
	 * Recalculate product stats
	 *
	 * ## EXAMPLES
	 *     wp drc-aww recalculate
	 *
	 * @when after_wp_load
	 */
	public function recalculate(): void {
		\WP_CLI::line( __( 'Recalculating product stats...', 'drc-advanced-woo-widgets' ) );

		$cache = \DRC\AWW\Cache\Product_Cache::instance();
		$cache->calculate_all_stats();

		\WP_CLI::success( __( 'Product stats recalculated.', 'drc-advanced-woo-widgets' ) );
	}

	/**
	 * Clear all caches
	 *
	 * ## EXAMPLES
	 *     wp drc-aww clear-cache
	 *
	 * @when after_wp_load
	 */
	public function clear_cache(): void {
		\DRC\AWW\Helpers\Helper_Functions::clear_all_transients();

		// Also clear stats table
		global $wpdb;
		$table = $wpdb->prefix . 'drc_product_stats';
		$wpdb->query( "TRUNCATE TABLE {$table}" );

		\WP_CLI::success( __( 'All caches cleared.', 'drc-advanced-woo-widgets' ) );
	}

	/**
	 * Show top products
	 *
	 * ## OPTIONS
	 * [--period=<period>]
	 * : Period (total, last7days, last30days)
	 * ---
	 * default: total
	 * ---
	 *
	 * ## EXAMPLES
	 *     wp drc-aww top-products --period=last7days
	 *
	 * @when after_wp_load
	 */
	public function top_products( array $args = [], array $assoc_args = [] ): void {
		$period = $assoc_args['period'] ?? 'total';
		$products = \DRC\AWW\Cache\Product_Cache::instance()->get_best_sellers( [
			'limit' => 10,
			'period' => $period,
		] );

		$items = [];
		foreach ( $products as $id ) {
			$product = wc_get_product( $id );
			if ( ! $product ) continue;
			$items[] = [
				'ID'    => $id,
				'Name'  => $product->get_name(),
				'Price' => $product->get_price(),
			];
		}

		\WP_CLI\Utils\format_items( 'table', $items, [ 'ID', 'Name', 'Price' ] );
	}
}

new CLI();