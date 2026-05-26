<?php
/**
 * Flash Sale Products Widget with countdown timer
 */

namespace DRC\AWW\Widgets;

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

class Flash_Sale_Products extends Base_Widget {

	public function get_name(): string {
		return 'drc_flash_sale';
	}

	public function get_title(): string {
		return __( 'Flash Sale Products', 'drc-advanced-woo-widgets' );
	}

	public function get_icon(): string {
		return 'eicon-flash';
	}

	public function get_keywords(): array {
		return [ 'woocommerce', 'flash', 'sale', 'countdown', 'timer' ];
	}

	protected function register_controls(): void {
		$this->start_controls_section(
			'section_content',
			[ 'label' => __( 'Content', 'drc-advanced-woo-widgets' ) ]
		);

		$this->add_control( [
			'name'    => 'show_timer',
			'label'   => __( 'Show Countdown', 'drc-advanced-woo-widgets' ),
			'type'    => Controls_Manager::SWITCHER,
			'default' => 'yes',
		] );

		$this->add_control( [
			'name'    => 'timer_position',
			'label'   => __( 'Timer Position', 'drc-advanced-woo-widgets' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'top',
			'options' => [
				'top'    => __( 'Top', 'drc-advanced-woo-widgets' ),
				'bottom' => __( 'Bottom', 'drc-advanced-woo-widgets' ),
				'overlay' => __( 'Overlay', 'drc-advanced-woo-widgets' ),
			],
			'condition' => [ 'show_timer' => 'yes' ],
		] );

		$this->add_control( $this->get_category_control() );
		$this->add_control( $this->get_tags_control() );
		$this->add_control( $this->get_stock_control() );
		$this->add_control( $this->get_layout_type_control() );
		$this->add_control( $this->get_columns_control() );
		$this->add_control( $this->get_products_count_control() );

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			[ 'label' => __( 'Style', 'drc-advanced-woo-widgets' ), 'tab' => Controls_Manager::TAB_STYLE ]
		);

		$this->add_control( $this->get_columns_gap_control() );
		$this->add_control( $this->get_items_gap_control() );
		$this->add_control( $this->get_border_radius_control() );
		$this->add_control( $this->get_title_color_control() );
		$this->add_control( $this->get_price_color_control() );

		$this->add_control( [
			'name' => 'timer_bg',
			'label' => __( 'Timer Background', 'drc-advanced-woo-widgets' ),
			'type' => Controls_Manager::COLOR,
			'default' => '#e74c3c',
			'selectors' => [ '{{WRAPPER}} .drc-countdown' => 'background-color: {{VALUE}};' ],
		] );

		$this->add_control( [
			'name' => 'timer_text_color',
			'label' => __( 'Timer Text Color', 'drc-advanced-woo-widgets' ),
			'type' => Controls_Manager::COLOR,
			'default' => '#ffffff',
			'selectors' => [ '{{WRAPPER}} .drc-countdown' => 'color: {{VALUE}};' ],
		] );

		$this->add_group_control( Group_Control_Typography::get_type(), [
			'name' => 'title_typography', 'label' => __( 'Title Typography', 'drc-advanced-woo-widgets' ),
			'selector' => '{{WRAPPER}} .drc-product-title',
		] );

		$this->end_controls_section();
	}

	protected function get_products( array $settings ): array {
		$limit = (int) ( $settings['products_count'] ?? 8 );
		$products = wc_get_products( [ 'limit' => $limit * 2, 'status' => 'publish' ] );

		$flash = [];
		$now = current_time( 'timestamp' );

		foreach ( $products as $product ) {
			if ( ! $product->is_on_sale() ) continue;
			$dates = $product->get_date_on_sale_to();
			if ( ! $dates ) continue;
			$end = $dates->getTimestamp();
			if ( $end <= $now ) continue;

			$flash[ $product->get_id() ] = $end;
			if ( count( $flash ) >= $limit ) break;
		}

		asort( $flash ); // Closest expiry first
		return array_slice( array_keys( $flash ), 0, $limit );
	}

	protected function render(): void {
		$settings = $this->get_settings_for_display();
		$ids = $this->get_products( $settings );

		if ( empty( $ids ) ) {
			echo '<p class="drc-no-products">' . esc_html__( 'No flash sale products.', 'drc-advanced-woo-widgets' ) . '</p>';
			return;
		}

		if ( ! empty( $settings['show_timer'] ) && $settings['timer_position'] === 'top' ) {
			$this->render_countdown( $ids );
		}

		$this->render_products( $ids, $settings );

		if ( ! empty( $settings['show_timer'] ) && $settings['timer_position'] === 'bottom' ) {
			$this->render_countdown( $ids );
		}
	}

	private function render_countdown( array $ids ): void {
		$product = wc_get_product( $ids[0] );
		if ( ! $product ) return;
		$dates = $product->get_date_on_sale_to();
		if ( ! $dates ) return;

		$end = $dates->getTimestamp();
		echo '<div class="drc-countdown" data-end="' . esc_attr( $end ) . '">';
		echo '<span class="drc-countdown-label">' . esc_html__( 'Sale ends in:', 'drc-advanced-woo-widgets' ) . '</span> ';
		echo '<span class="drc-countdown-h">00</span>:<span class="drc-countdown-m">00</span>:<span class="drc-countdown-s">00</span>';
		echo '</div>';
	}
}