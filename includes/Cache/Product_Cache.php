<?php
/**
 * Product caching system
 */

namespace DRC\AWW\Cache;

defined( 'ABSPATH' ) || exit;

class Product_Cache {

	private static $instance = null;

	private function __construct() {
		add_action( 'drc_aww_calculate_stats', [ $this, 'calculate_all_stats' ] );
		add_action( 'woocommerce_order_status_completed', [ $this, 'record_sale' ], 10, 2 );
	}

	public static function instance(): Product_Cache {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function calculate_all_stats(): void {
		global $wpdb;

		$table_name = $wpdb->prefix . 'drc_product_stats';

		// Calculate sales stats
		$this->calculate_sales_stats();

		// Calculate view stats
		$this->calculate_view_stats();

		// Calculate composite scores
		$this->calculate_custom_scores();
	}

	private function calculate_sales_stats(): void {
		global $wpdb;

		$table_name = $wpdb->prefix . 'drc_product_stats';

		// Get sales counts for last 30 days
		$query = "
			SELECT
				posts.post_parent as product_id,
				COUNT(posts.ID) as sales_count
			FROM {$wpdb->posts} posts
			WHERE posts.post_type = 'shop_order'
			AND posts.post_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
			AND posts.post_status IN ('wc-completed', 'wc-processing')
			GROUP BY posts.post_parent
		";

		$results = $wpdb->get_results( $query );

		foreach ( $results as $result ) {
			$this->upsert_stat( $result->product_id, 'sales', 'last30days', $result->sales_count, $result->sales_count );
		}
	}

	private function calculate_view_stats(): void {
		// This would integrate with a views tracking system
		// For now, we use product view meta if available
		$products = wc_get_products( [
			'limit' => -1,
			'status' => 'publish',
		] );

		foreach ( $products as $product ) {
			$views = (int) get_post_meta( $product->get_id(), '_drc_product_views', true );
			$this->upsert_stat( $product->get_id(), 'views', 'total', $views, $views );
		}
	}

	private function calculate_custom_scores(): void {
		global $wpdb;

		$table_name = $wpdb->prefix . 'drc_product_stats';

		// Get all products with their scores
		$query = "
			SELECT
				product_id,
				SUM(CASE WHEN stat_type = 'sales' THEN stat_value ELSE 0 END) * 0.5 +
				SUM(CASE WHEN stat_type = 'views' THEN stat_value ELSE 0 END) * 0.3 +
				SUM(CASE WHEN stat_type = 'rating' THEN stat_value ELSE 0 END) * 0.2 as composite_score
			FROM {$table_name}
			GROUP BY product_id
			ORDER BY composite_score DESC
		";

		// For now, we calculate scores based on available data
		$products = wc_get_products( [
			'limit' => 100,
			'status' => 'publish',
			'orderby' => 'popularity',
		] );

		foreach ( $products as $index => $product ) {
			$score = $this->calculate_product_score( $product );
			$this->upsert_stat( $product->get_id(), 'score', 'total', $score, $score );
		}
	}

	public function calculate_product_score( $product ): float {
		$sales_score = (float) $this->get_cached_stat( $product->get_id(), 'sales', 'last30days' );
		$views_score = (float) $this->get_cached_stat( $product->get_id(), 'views', 'total' );
		$rating_score = (float) $product->get_average_rating();
		$reviews_score = (float) $product->get_review_count();

		// Weighted composite score
		return ( $sales_score * 0.4 ) + ( $views_score * 0.3 ) + ( $rating_score * 0.2 ) + ( $reviews_score * 0.1 );
	}

	public function upsert_stat( int $product_id, string $type, string $period, float $value, int $count = 0 ): void {
		global $wpdb;

		$table_name = $wpdb->prefix . 'drc_product_stats';

		$wpdb->query( $wpdb->prepare(
			"INSERT INTO {$table_name} (product_id, stat_type, stat_period, stat_value, stat_count)
			 VALUES (%d, %s, %s, %f, %d)
			 ON DUPLICATE KEY UPDATE stat_value = VALUES(stat_value), stat_count = VALUES(stat_count)",
			$product_id,
			$type,
			$period,
			$value,
			$count
		) );
	}

	public function get_cached_stat( int $product_id, string $type, string $period ): float {
		global $wpdb;

		$table_name = $wpdb->prefix . 'drc_product_stats';

		$value = $wpdb->get_var( $wpdb->prepare(
			"SELECT stat_value FROM {$table_name}
			 WHERE product_id = %d AND stat_type = %s AND stat_period = %s",
			$product_id,
			$type,
			$period
		) );

		return (float) $value;
	}

	public function record_sale( int $order_id, $order = null ): void {
		$order = wc_get_order( $order_id );

		foreach ( $order->get_items() as $item ) {
			$product_id = $item->get_product_id();
			$quantity   = $item->get_quantity();

			// Increment sales count
			$this->increment_stat( $product_id, 'sales', 'last30days', $quantity );
			$this->increment_stat( $product_id, 'sales', 'total', $quantity );
		}
	}

	private function increment_stat( int $product_id, string $type, string $period, float $increment ): void {
		global $wpdb;

		$table_name = $wpdb->prefix . 'drc_product_stats';

		$wpdb->query( $wpdb->prepare(
			"INSERT INTO {$table_name} (product_id, stat_type, stat_period, stat_value, stat_count)
			 VALUES (%d, %s, %s, %f, 1)
			 ON DUPLICATE KEY UPDATE stat_value = stat_value + VALUES(stat_value), stat_count = stat_count + VALUES(stat_count)",
			$product_id,
			$type,
			$period,
			$increment
		) );
	}

	public function get_best_sellers( array $args = [] ): array {
		$defaults = [
			'limit'     => 8,
			'period'    => 'total',
			'category'  => '',
			'tag'       => '',
			'featured'  => false,
			'onsale'    => false,
			'stock'     => '',
		];

		$args = wp_parse_args( $args, $defaults );

		// Try transient first
		$cache_key = 'drc_aww_best_sellers_' . md5( wp_json_encode( $args ) );
		$cached = get_site_transient( $cache_key );
		if ( false !== $cached ) {
			return $cached;
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'drc_product_stats';

		$query = $wpdb->prepare(
			"SELECT product_id, stat_value FROM {$table_name}
			 WHERE stat_type = 'sales' AND stat_period = %s",
			$args['period']
		);

		$query .= " ORDER BY stat_value DESC LIMIT " . intval( $args['limit'] );

		$product_ids = $wpdb->get_col( $query );

		if ( empty( $product_ids ) ) {
			$product_ids = wc_get_products( [
				'limit'   => $args['limit'] * 2,
				'status'  => 'publish',
				'orderby' => 'total_sales',
				'order'   => 'DESC',
				'return'  => 'ids',
			] );
		}

		$products = $this->filter_products( $product_ids, $args );

		set_site_transient( $cache_key, $products, HOUR_IN_SECONDS );
		return $products;
	}

	public function get_trending_products( array $args = [] ): array {
		$defaults = [
			'limit'     => 8,
			'period'    => 'last7days',
			'category'  => '',
			'tag'       => '',
			'featured'  => false,
			'onsale'    => false,
			'stock'     => '',
		];

		$args = wp_parse_args( $args, $defaults );

		return $this->get_products_by_score( $args );
	}

	public function get_popular_products( array $args = [] ): array {
		$defaults = [
			'limit'     => 8,
			'period'    => 'total',
			'category'  => '',
			'tag'       => '',
			'featured'  => false,
			'onsale'    => false,
			'stock'     => '',
		];

		$args = wp_parse_args( $args, $defaults );

		return $this->get_products_by_score( $args );
	}

	private function get_products_by_score( array $args ): array {
		global $wpdb;

		$table_name = $wpdb->prefix . 'drc_product_stats';

		$cache_key = 'drc_aww_products_score_' . md5( wp_json_encode( $args ) );
		$cached = get_site_transient( $cache_key );
		if ( false !== $cached ) {
			return $cached;
		}

		$query = $wpdb->prepare(
			"SELECT product_id, stat_value FROM {$table_name}
			 WHERE stat_type = 'score' AND stat_period = %s",
			$args['period']
		);

		$query .= " ORDER BY stat_value DESC LIMIT " . intval( $args['limit'] );

		$product_ids = $wpdb->get_col( $query );

		// Fallback to WooCommerce products if no cached data
		if ( empty( $product_ids ) ) {
			$product_ids = $this->get_fallback_product_ids( $args );
		}

		$products = $this->filter_products( $product_ids, $args );

		set_site_transient( $cache_key, $products, HOUR_IN_SECONDS );
		return $products;
	}

	private function get_fallback_product_ids( array $args ): array {
		$query_args = [
			'limit'    => $args['limit'],
			'status'   => 'publish',
			'orderby'  => 'popularity',
			'order'    => 'DESC',
			'return'   => 'ids',
		];

		if ( ! empty( $args['category'] ) ) {
			$query_args['category'] = [ $args['category'] ];
		}

		if ( ! empty( $args['tag'] ) ) {
			$query_args['tag'] = [ $args['tag'] ];
		}

		return wc_get_products( $query_args );
	}

	private function filter_products( array $product_ids, array $args ): array {
		if ( empty( $product_ids ) ) {
			return [];
		}

		$filtered_ids = [];

		foreach ( $product_ids as $id ) {
			$product = wc_get_product( $id );

			if ( ! $product || ! $product->is_visible() ) {
				continue;
			}

			// Featured filter
			if ( $args['featured'] && ! $product->is_featured() ) {
				continue;
			}

			// On sale filter
			if ( $args['onsale'] && ! $product->is_on_sale() ) {
				continue;
			}

			// Stock filter
			if ( ! empty( $args['stock'] ) ) {
				$stock_status = $product->get_stock_status();
				if ( $stock_status !== $args['stock'] ) {
					continue;
				}
			}

			// Category filter
			if ( ! empty( $args['category'] ) ) {
				$terms = wp_get_post_terms( $id, 'product_cat', [ 'fields' => 'ids' ] );
				if ( ! in_array( $args['category'], $terms ) ) {
					continue;
				}
			}

			// Tag filter
			if ( ! empty( $args['tag'] ) ) {
				$terms = wp_get_post_terms( $id, 'product_tag', [ 'fields' => 'ids' ] );
				if ( ! in_array( $args['tag'], $terms ) ) {
					continue;
				}
			}

			$filtered_ids[] = $id;
		}

		return array_slice( $filtered_ids, 0, $args['limit'] );
	}
}