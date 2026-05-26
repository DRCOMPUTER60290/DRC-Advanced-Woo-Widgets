<?php
/**
 * Best Selling Products Widget
 */

namespace DRC\AWW\Widgets;

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use DRC\AWW\Cache\Product_Cache;

class Best_Selling_Products extends Base_Widget {

	public function get_name(): string {
		return 'drc_best_selling_products';
	}

	public function get_title(): string {
		return __( 'Best Selling Products', 'drc-advanced-woo-widgets' );
	}

	public function get_icon(): string {
		return 'eicon-products';
	}

	public function get_keywords(): array {
		return [ 'woocommerce', 'products', 'best', 'selling', 'sales' ];
	}

	protected function register_controls(): void {
		// Content Tab
		$this->start_controls_section(
			'section_content',
			[
				'label' => __( 'Content', 'drc-advanced-woo-widgets' ),
			]
		);

		$this->add_control( $this->get_period_control() );
		$this->add_control( $this->get_category_control() );
		$this->add_control( $this->get_tags_control() );
		$this->add_control( $this->get_featured_control() );
		$this->add_control( $this->get_onsale_control() );
		$this->add_control( $this->get_stock_control() );
		$this->add_control( $this->get_layout_type_control() );
		$this->add_control( $this->get_columns_control() );
		$this->add_control( $this->get_products_count_control() );
		$this->add_control( $this->get_sort_by_control() );
		$this->add_control( $this->get_sort_order_control() );

		$this->end_controls_section();

		// Style Tab - General
		$this->start_controls_section(
			'section_style_general',
			[
				'label' => __( 'General', 'drc-advanced-woo-widgets' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control( $this->get_columns_gap_control() );
		$this->add_control( $this->get_items_gap_control() );
		$this->add_control( $this->get_border_radius_control() );

		$this->end_controls_section();

		// Style Tab - Product Title
		$this->start_controls_section(
			'section_style_title',
			[
				'label' => __( 'Product Title', 'drc-advanced-woo-widgets' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control( $this->get_title_color_control() );

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'title_typography',
				'label'    => __( 'Typography', 'drc-advanced-woo-widgets' ),
				'selector' => '{{WRAPPER}} .drc-product-title',
			]
		);

		$this->end_controls_section();

		// Style Tab - Price
		$this->start_controls_section(
			'section_style_price',
			[
				'label' => __( 'Price', 'drc-advanced-woo-widgets' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control( $this->get_price_color_control() );

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'price_typography',
				'label'    => __( 'Typography', 'drc-advanced-woo-widgets' ),
				'selector' => '{{WRAPPER}} .drc-product-price',
			]
		);

		$this->end_controls_section();

		// Style Tab - Button
		$this->start_controls_section(
			'section_style_button',
			[
				'label' => __( 'Button', 'drc-advanced-woo-widgets' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		foreach ( $this->get_button_controls() as $control ) {
			$this->add_control( $control );
		}

		$this->add_control(
			'button_background',
			[
				'name'     => 'button_background',
				'label'    => __( 'Background', 'drc-advanced-woo-widgets' ),
				'type'     => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .drc-product-button' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_text_color',
			[
				'name'     => 'button_text_color',
				'label'    => __( 'Text Color', 'drc-advanced-woo-widgets' ),
				'type'     => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .drc-product-button' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'button_typography',
				'label'    => __( 'Typography', 'drc-advanced-woo-widgets' ),
				'selector' => '{{WRAPPER}} .drc-product-button',
			]
		);

		$this->end_controls_section();
	}

	protected function get_products( array $settings ): array {
		$args = [
			'limit'    => $settings['products_count'] ?? 8,
			'period'   => $settings['period'] ?? 'total',
			'category' => $settings['category'] ?? '',
			'tag'      => $settings['tags'] ?? '',
			'featured' => $settings['featured_only'] ?? '',
			'onsale'   => $settings['onsale_only'] ?? '',
			'stock'    => $settings['stock_status'] ?? '',
		];

		return Product_Cache::instance()->get_best_sellers( $args );
	}

	protected function render(): void {
		$settings = $this->get_settings_for_display();
		$products = $this->get_products( $settings );

		$this->render_products( $products, $settings );
	}
}