<?php
/**
 * Recently Sold Products Widget
 */

namespace DRC\AWW\Widgets;

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

class Recently_Sold_Products extends Base_Widget {

	public function get_name(): string {
		return 'drc_recently_sold';
	}

	public function get_title(): string {
		return __( 'Recently Sold Products', 'drc-advanced-woo-widgets' );
	}

	public function get_icon(): string {
		return 'eicon-check-circle';
	}

	public function get_keywords(): array {
		return [ 'woocommerce', 'sold', 'recent', 'products' ];
	}

	protected function register_controls(): void {
		$this->start_controls_section(
			'section_content',
			[ 'label' => __( 'Content', 'drc-advanced-woo-widgets' ) ]
		);

		$this->add_named_control( [
			'name'    => 'hours_back',
			'label'   => __( 'Hours Back', 'drc-advanced-woo-widgets' ),
			'type'    => Controls_Manager::NUMBER,
			'default' => 24,
			'min'     => 1,
			'max'     => 168,
		] );

		$this->add_named_control( [
			'name'    => 'show_sold_count',
			'label'   => __( 'Show Sold Count', 'drc-advanced-woo-widgets' ),
			'type'    => Controls_Manager::SWITCHER,
			'default' => 'yes',
		] );

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

		$this->add_group_control( Group_Control_Typography::get_type(), [
			'name' => 'title_typography', 'label' => __( 'Title Typography', 'drc-advanced-woo-widgets' ),
			'selector' => '{{WRAPPER}} .drc-product-title',
		] );

		$this->end_controls_section();
	}

	protected function get_products( array $settings ): array {
		global $wpdb;
		$hours = (int) ( $settings['hours_back'] ?? 24 );
		$since = date( 'Y-m-d H:i:s', strtotime( "-{$hours} hours" ) );
		$limit = (int) ( $settings['products_count'] ?? 8 );

		$ids = $wpdb->get_col( $wpdb->prepare(
			"SELECT DISTINCT oim.meta_value
			FROM {$wpdb->posts} o
			INNER JOIN {$wpdb->prefix}woocommerce_order_items oi ON o.ID = oi.order_id
			INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta oim ON oi.order_item_id = oim.order_item_id
			WHERE o.post_type = 'shop_order'
			AND o.post_date >= %s
			AND o.post_status IN ('wc-completed','wc-processing')
			AND oim.meta_key = '_product_id'
			ORDER BY o.post_date DESC
			LIMIT %d",
			$since, $limit
		) );

		return array_filter( array_map( 'intval', (array) $ids ), function( $id ) {
			$p = wc_get_product( $id );
			return $p && $p->is_visible();
		} );
	}

	protected function render(): void {
		$settings = $this->get_settings_for_display();
		$this->render_products( $this->get_products( $settings ), $settings );
	}
}