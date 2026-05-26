<?php
/**
 * Deal of the Week Widget
 */

namespace DRC\AWW\Widgets;

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

class Deal_of_the_Week extends Base_Widget {

	public function get_name(): string {
		return 'drc_deal_of_week';
	}

	public function get_title(): string {
		return __( 'Deal of the Week', 'drc-advanced-woo-widgets' );
	}

	public function get_icon(): string {
		return 'eicon-sale';
	}

	public function get_keywords(): array {
		return [ 'woocommerce', 'deal', 'sale', 'discount', 'week' ];
	}

	protected function register_controls(): void {
		$this->start_controls_section(
			'section_content',
			[ 'label' => __( 'Content', 'drc-advanced-woo-widgets' ) ]
		);

		$this->add_named_control( [
			'name'    => 'min_discount',
			'label'   => __( 'Min Discount %', 'drc-advanced-woo-widgets' ),
			'type'    => Controls_Manager::NUMBER,
			'default' => 10,
			'min'     => 1,
			'max'     => 100,
		] );

		$this->add_named_control( [
			'name'    => 'show_discount_badge',
			'label'   => __( 'Show Discount Badge', 'drc-advanced-woo-widgets' ),
			'type'    => Controls_Manager::SWITCHER,
			'default' => 'yes',
		] );

		$this->add_named_control( $this->get_manual_products_control() );
		$this->add_named_control( $this->get_category_control() );
		$this->add_named_control( $this->get_tags_control() );
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

		$this->add_named_control( [
			'name' => 'badge_bg',
			'label' => __( 'Badge Background', 'drc-advanced-woo-widgets' ),
			'type' => Controls_Manager::COLOR,
			'default' => '#e74c3c',
			'selectors' => [ '{{WRAPPER}} .drc-deal-badge' => 'background-color: {{VALUE}};' ],
		] );

		$this->add_group_control( Group_Control_Typography::get_type(), [
			'name' => 'title_typography', 'label' => __( 'Title Typography', 'drc-advanced-woo-widgets' ),
			'selector' => '{{WRAPPER}} .drc-product-title',
		] );

		$this->end_controls_section();
	}

	protected function get_products( array $settings ): array {
		$min_discount = (int) ( $settings['min_discount'] ?? 10 );
		$products = wc_get_products( [
			'limit' => $settings['products_count'] ?? 8,
			'status' => 'publish',
		] );

		$deals = [];
		foreach ( $products as $product ) {
			if ( ! $product->is_on_sale() ) continue;
			$discount = $this->calc_discount( $product );
			if ( $discount < $min_discount ) continue;
			$deals[ $product->get_id() ] = $discount;
		}

		arsort( $deals );
		return array_slice( array_keys( $deals ), 0, $settings['products_count'] ?? 8 );
	}

	private function calc_discount( $product ): int {
		$regular = (float) $product->get_regular_price();
		$sale = (float) $product->get_sale_price();
		return ( $regular > 0 ) ? (int) round( ( ( $regular - $sale ) / $regular ) * 100 ) : 0;
	}

	protected function render(): void {
		$settings = $this->get_settings_for_display();
		$this->render_products( $this->get_products( $settings ), $settings );
	}
}