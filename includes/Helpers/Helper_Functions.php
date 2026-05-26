<?php
/**
 * Helper functions for DRC Advanced Woo Widgets
 */

namespace DRC\AWW\Helpers;

defined( 'ABSPATH' ) || exit;

class Helper_Functions {

	public static function get_period_dates( string $period ): array {
		$dates = [
			'start' => '',
			'end'   => '',
		];

		switch ( $period ) {
			case 'today':
				$dates['start'] = date( 'Y-m-d 00:00:00' );
				$dates['end']   = date( 'Y-m-d 23:59:59' );
				break;

			case 'week':
				$dates['start'] = date( 'Y-m-d 00:00:00', strtotime( 'monday this week' ) );
				$dates['end']   = date( 'Y-m-d 23:59:59', strtotime( 'sunday this week' ) );
				break;

			case 'month':
				$dates['start'] = date( 'Y-m-01 00:00:00' );
				$dates['end']   = date( 'Y-m-t 23:59:59' );
				break;

			case 'last7days':
				$dates['start'] = date( 'Y-m-d 00:00:00', strtotime( '-6 days' ) );
				$dates['end']   = date( 'Y-m-d 23:59:59' );
				break;

			case 'last30days':
				$dates['start'] = date( 'Y-m-d 00:00:00', strtotime( '-29 days' ) );
				$dates['end']   = date( 'Y-m-d 23:59:59' );
				break;

			case 'custom':
				// Handle via widget controls
				break;
		}

		return $dates;
	}

	public static function get_product_categories(): array {
		$terms = get_terms( [
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
		] );

		$options = [ '' => __( 'All Categories', 'drc-advanced-woo-widgets' ) ];
		foreach ( $terms as $term ) {
			$options[ $term->term_id ] = $term->name;
		}

		return $options;
	}

	public static function get_product_tags(): array {
		$terms = get_terms( [
			'taxonomy'   => 'product_tag',
			'hide_empty' => false,
		] );

		$options = [ '' => __( 'All Tags', 'drc-advanced-woo-widgets' ) ];
		foreach ( $terms as $term ) {
			$options[ $term->term_id ] = $term->name;
		}

		return $options;
	}

	public static function get_stock_status_options(): array {
		return [
			'instock'     => __( 'In Stock', 'drc-advanced-woo-widgets' ),
			'outofstock'  => __( 'Out of Stock', 'drc-advanced-woo-widgets' ),
			'onbackorder' => __( 'On Backorder', 'drc-advanced-woo-widgets' ),
		];
	}

	public static function get_layout_options(): array {
		return [
			'grid'    => __( 'Grid', 'drc-advanced-woo-widgets' ),
			'list'    => __( 'List', 'drc-advanced-woo-widgets' ),
			'carousel' => __( 'Carousel', 'drc-advanced-woo-widgets' ),
			'compact' => __( 'Compact', 'drc-advanced-woo-widgets' ),
			'masonry' => __( 'Masonry', 'drc-advanced-woo-widgets' ),
		];
	}

	public static function get_sort_options(): array {
		return [
			'sales'        => __( 'Sales', 'drc-advanced-woo-widgets' ),
			'views'        => __( 'Views', 'drc-advanced-woo-widgets' ),
			'rating'       => __( 'Average Rating', 'drc-advanced-woo-widgets' ),
			'review_count' => __( 'Review Count', 'drc-advanced-woo-widgets' ),
			'date'         => __( 'Date', 'drc-advanced-woo-widgets' ),
			'random'       => __( 'Random', 'drc-advanced-woo-widgets' ),
			'score'        => __( 'Custom Score', 'drc-advanced-woo-widgets' ),
		];
	}

	public static function get_period_options(): array {
		return [
			'today'      => __( 'Today', 'drc-advanced-woo-widgets' ),
			'week'       => __( 'This Week', 'drc-advanced-woo-widgets' ),
			'month'      => __( 'This Month', 'drc-advanced-woo-widgets' ),
			'last7days'  => __( 'Last 7 Days', 'drc-advanced-woo-widgets' ),
			'last30days' => __( 'Last 30 Days', 'drc-advanced-woo-widgets' ),
			'custom'     => __( 'Custom Period', 'drc-advanced-woo-widgets' ),
		];
	}

	public static function clear_all_transients(): void {
		$transients = [
			'drc_aww_best_sellers',
			'drc_aww_trending',
			'drc_aww_popular',
			'drc_aww_recently_sold',
			'drc_aww_flash_sale',
		];

		foreach ( $transients as $transient ) {
			delete_site_transient( $transient );
		}
	}
}