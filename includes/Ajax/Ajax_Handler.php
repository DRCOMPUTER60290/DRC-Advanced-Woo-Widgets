<?php
/**
 * AJAX handler for load more, filters, pagination
 */

namespace DRC\AWW\Ajax;

defined( 'ABSPATH' ) || exit;

use DRC\AWW\Cache\Product_Cache;
use Elementor\Plugin as ElementorPlugin;

class Ajax_Handler {

	private static $instance = null;

	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'wp_ajax_drc_aww_load_more', [ $this, 'load_more' ] );
		add_action( 'wp_ajax_nopriv_drc_aww_load_more', [ $this, 'load_more' ] );
		add_action( 'wp_ajax_drc_aww_filter_products', [ $this, 'filter_products' ] );
		add_action( 'wp_ajax_nopriv_drc_aww_filter_products', [ $this, 'filter_products' ] );
	}

	public function load_more(): void {
		check_ajax_referer( 'drc_aww_nonce', 'nonce' );

		$page     = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;
		$widget_id = isset( $_POST['widget_id'] ) ? sanitize_text_field( wp_unslash( $_POST['widget_id'] ) ) : '';
		$settings = isset( $_POST['settings'] ) ? json_decode( stripslashes( $_POST['settings'] ), true ) : [];


		$args = [
			'limit'    => $settings['products_count'] ?? 8,
			'period'   => $settings['period'] ?? 'total',
			'category' => $settings['category'] ?? '',
			'tag'      => $settings['tags'] ?? '',
			'featured' => ! empty( $settings['featured_only'] ),
			'onsale'   => ! empty( $settings['onsale_only'] ),
			'stock'    => $settings['stock_status'] ?? '',
			'offset'   => ( $page - 1 ) * ( $settings['products_count'] ?? 8 ),
		];

		$cache = Product_Cache::instance();
		$type = $settings['widget_type'] ?? 'best_selling';

		switch ( $type ) {
			case 'trending':
				$products = $cache->get_trending_products( $args );
				break;
			case 'popular':
				$products = $cache->get_popular_products( $args );
				break;
			default:
				$products = $cache->get_best_sellers( $args );
		}

		ob_start();
		$layout = $settings['layout'] ?? 'grid';
		$template = locate_template( "drc-aww/layouts/{$layout}.php" );

		if ( ! $template ) {
			$template = DRC_AWW_PLUGIN_DIR . "templates/layouts/{$layout}.php";
		}

		if ( file_exists( $template ) ) {
			foreach ( $products as $product_id ) {
				$product = wc_get_product( $product_id );
				if ( ! $product ) continue;
				$args = [ 'product' => $product, 'settings' => $settings ];
				include $template;
			}
		}

		$html = ob_get_clean();
		$has_more = count( $products ) >= ( $settings['products_count'] ?? 8 );

		wp_send_json_success( [
			'html'     => $html,
			'has_more' => $has_more,
			'page'     => $page,
		] );
	}

	public function filter_products(): void {
		check_ajax_referer( 'drc_aww_nonce', 'nonce' );

		$filters = isset( $_POST['filters'] ) ? json_decode( stripslashes( $_POST['filters'] ), true ) : [];
		$type    = isset( $_POST['widget_type'] ) ? sanitize_text_field( wp_unslash( $_POST['widget_type'] ) ) : 'best_selling';

		$args = [
			'limit'    => $filters['products_count'] ?? 8,
			'period'   => $filters['period'] ?? 'total',
			'category' => $filters['category'] ?? '',
			'tag'      => $filters['tags'] ?? '',
			'featured' => ! empty( $filters['featured_only'] ),
			'onsale'   => ! empty( $filters['onsale_only'] ),
			'stock'    => $filters['stock_status'] ?? '',
		];

		$cache = Product_Cache::instance();

		switch ( $type ) {
			case 'trending':
				$products = $cache->get_trending_products( $args );
				break;
			case 'popular':
				$products = $cache->get_popular_products( $args );
				break;
			default:
				$products = $cache->get_best_sellers( $args );
		}

		ob_start();
		$layout = $filters['layout'] ?? 'grid';
		$template = locate_template( "drc-aww/layouts/{$layout}.php" );

		if ( ! $template ) {
			$template = DRC_AWW_PLUGIN_DIR . "templates/layouts/{$layout}.php";
		}

		if ( file_exists( $template ) ) {
			foreach ( $products as $product_id ) {
				$product = wc_get_product( $product_id );
				if ( ! $product ) continue;
				$args = [ 'product' => $product, 'settings' => $filters ];
				include $template;
			}
		}

		wp_send_json_success( [
			'html'  => ob_get_clean(),
			'count' => count( $products ),
		] );
	}
}