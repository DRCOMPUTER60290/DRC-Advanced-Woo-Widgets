<?php
/**
 * Product query builder - optimized queries
 */

namespace DRC\AWW\Queries\Products;

defined( 'ABSPATH' ) || exit;

class Product_Query {

	private $args = [];

	private $defaults = [
		'limit'    => 8,
		'offset'   => 0,
		'period'   => 'total',
		'category' => '',
		'tag'      => '',
		'featured' => false,
		'onsale'   => false,
		'stock'    => '',
		'sort_by'  => 'sales',
		'sort_order' => 'DESC',
	];

	public function __construct( array $args = [] ) {
		$this->args = wp_parse_args( $args, $this->defaults );
	}

	/**
	 * Get best selling products using custom stats table
	 */
	public function get_best_sellers(): array {
		global $wpdb;
		$table = $wpdb->prefix . 'drc_product_stats';

		$sql = $wpdb->prepare(
			"SELECT product_id FROM {$table}
			 WHERE stat_type = 'sales' AND stat_period = %s
			 ORDER BY stat_value DESC
			 LIMIT %d OFFSET %d",
			$this->args['period'],
			$this->args['limit'],
			$this->args['offset']
		);

		$product_ids = $wpdb->get_col( $sql );

		return $this->filter_product_ids( $product_ids );
	}

	/**
	 * Get popular products using composite score
	 */
	public function get_popular(): array {
		global $wpdb;
		$table = $wpdb->prefix . 'drc_product_stats';

		$sql = $wpdb->prepare(
			"SELECT product_id FROM {$table}
			 WHERE stat_type = 'score' AND stat_period = %s
			 ORDER BY stat_value DESC
			 LIMIT %d OFFSET %d",
			$this->args['period'],
			$this->args['limit'],
			$this->args['offset']
		);

		$product_ids = $wpdb->get_col( $sql );

		if ( empty( $product_ids ) ) {
			return $this->get_fallback_products();
		}

		return $this->filter_product_ids( $product_ids );
	}

	/**
	 * Get trending products (recent activity spike)
	 */
	public function get_trending(): array {
		global $wpdb;
		$table = $wpdb->prefix . 'drc_product_stats';

		// Trending = high recent sales activity compared to historical
		$sql = $wpdb->prepare(
			"SELECT product_id, stat_value FROM {$table}
			 WHERE stat_type = 'sales' AND stat_period = 'last7days'
			 ORDER BY stat_value DESC
			 LIMIT %d OFFSET %d",
			$this->args['limit'],
			$this->args['offset']
		);

		$product_ids = $wpdb->get_col( $sql );

		if ( empty( $product_ids ) ) {
			return $this->get_fallback_products();
		}

		return $this->filter_product_ids( $product_ids );
	}

	/**
	 * Fallback to WooCommerce native queries
	 */
	private function get_fallback_products(): array {
		$query_args = [
			'limit'   => $this->args['limit'],
			'status'  => 'publish',
			'orderby' => 'popularity',
			'order'   => 'DESC',
			'return'  => 'ids',
			'page'    => (int) ( $this->args['offset'] / $this->args['limit'] ) + 1,
		];

		if ( ! empty( $this->args['category'] ) ) {
			$query_args['category'] = [ $this->args['category'] ];
		}

		if ( ! empty( $this->args['tag'] ) ) {
			$query_args['tag'] = [ $this->args['tag'] ];
		}

		return wc_get_products( $query_args );
	}

	/**
	 * Filter product IDs by criteria
	 */
	private function filter_product_ids( array $ids ): array {
		$filtered = [];

		foreach ( $ids as $id ) {
			$product = wc_get_product( $id );

			if ( ! $product || ! $product->is_visible() || $product->get_status() !== 'publish' ) {
				continue;
			}

			if ( $this->args['featured'] && ! $product->is_featured() ) {
				continue;
			}

			if ( $this->args['onsale'] && ! $product->is_on_sale() ) {
				continue;
			}

			if ( ! empty( $this->args['stock'] ) && $product->get_stock_status() !== $this->args['stock'] ) {
				continue;
			}

			if ( ! empty( $this->args['category'] ) ) {
				$terms = wp_get_post_terms( $id, 'product_cat', [ 'fields' => 'ids' ] );
				if ( ! in_array( $this->args['category'], $terms ) ) {
					continue;
				}
			}

			if ( ! empty( $this->args['tag'] ) ) {
				$terms = wp_get_post_terms( $id, 'product_tag', [ 'fields' => 'ids' ] );
				if ( ! array_intersect( (array) $this->args['tag'], $terms ) ) {
					continue;
				}
			}

			$filtered[] = $id;

			if ( count( $filtered ) >= $this->args['limit'] ) {
				break;
			}
		}

		return $filtered;
	}
}