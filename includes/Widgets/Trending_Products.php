<?php
/**
 * Trending Products Widget
 */

namespace DRC\AWW\Widgets;

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use DRC\AWW\Cache\Product_Cache;

class Trending_Products extends Base_Widget {

	public function get_name(): string {
		return 'drc_trending_products';
	}

	public function get_title(): string {
		return __( 'Trending Products', 'drc-advanced-woo-widgets' );
	}

	public function get_icon(): string {
		return 'eicon-trending-up';
	}

	public function get_keywords(): array {
		return [ 'woocommerce', 'trending', 'popular', 'products' ];
	}

	protected function register_controls(): void {
		$this->start_controls_section(
			'section_content',
			[ 'label' => __( 'Content', 'drc-advanced-woo-widgets' ) ]
		);

		$this->add_named_control( array_merge( $this->get_period_control(), [ 'default' => 'last7days' ] ) );
		$this->add_named_control( $this->get_manual_products_control() );
		$this->add_named_control( $this->get_category_control() );
		$this->add_named_control( $this->get_tags_control() );
		$this->add_named_control( $this->get_featured_control() );
		$this->add_named_control( $this->get_onsale_control() );
		$this->add_named_control( $this->get_stock_control() );
		$this->add_named_control( $this->get_layout_type_control() );
		$this->add_named_control( $this->get_columns_control() );
		$this->add_named_control( $this->get_products_count_control() );

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			[ 'label' => __( 'Style', 'drc-advanced-woo-widgets' ), 'tab' => Controls_Manager::TAB_STYLE ]
		);

		$this->add_named_control( $this->get_columns_gap_control() );
		$this->add_named_control( $this->get_items_gap_control() );
		$this->add_named_control( $this->get_border_radius_control() );
		$this->add_named_control( $this->get_title_color_control() );
		$this->add_named_control( $this->get_price_color_control() );

		$this->add_group_control( Group_Control_Typography::get_type(), [
			'name' => 'title_typography', 'label' => __( 'Title Typography', 'drc-advanced-woo-widgets' ),
			'selector' => '{{WRAPPER}} .drc-product-title',
		] );

		$this->end_controls_section();
	}

	protected function get_products( array $settings ): array {
		return Product_Cache::instance()->get_trending_products( [
			'limit'    => $settings['products_count'] ?? 8,
			'period'   => $settings['period'] ?? 'last7days',
			'category' => $settings['category'] ?? '',
			'tag'      => $settings['tags'] ?? '',
			'featured' => ! empty( $settings['featured_only'] ),
			'onsale'   => ! empty( $settings['onsale_only'] ),
			'stock'    => $settings['stock_status'] ?? '',
		] );
	}

	protected function render(): void {
		$settings = $this->get_settings_for_display();
		$this->render_products( $this->get_products( $settings ), $settings );
	}
}