<?php
/**
 * Base widget class for all DRC widgets
 */

namespace DRC\AWW\Widgets;

defined( 'ABSPATH' ) || exit;

use Elementor\Widget_Base;
use DRC\AWW\Helpers\Helper_Functions;
use DRC\AWW\Queries\Products\Product_Query;

abstract class Base_Widget extends Widget_Base {

	/**
	 * Get widget categories
	 */
	public function get_categories(): array {
		return [ 'drc-woo-widgets' ];
	}

	/**
	 * Add a control from a named array — extracts 'name' key and passes it
	 * as the first argument to parent::add_control().
	 */
	protected function add_named_control( array $control ): void {
		$name = $control['name'];
		unset( $control['name'] );
		$this->add_control( $name, $control );
	}

	/**
	 * Get general section
	 */
	protected function get_general_controls(): array {
		return [
			$this->get_period_control(),
			$this->get_category_control(),
			$this->get_tags_control(),
			$this->get_featured_control(),
			$this->get_onsale_control(),
			$this->get_stock_control(),
		];
	}

	/**
	 * Get layout section
	 */
	protected function get_layout_controls(): array {
		return [
			$this->get_layout_type_control(),
			$this->get_columns_control(),
			$this->get_products_count_control(),
		];
	}

	/**
	 * Get style section
	 */
	protected function get_style_controls_list(): array {
		return [
			$this->get_columns_gap_control(),
			$this->get_items_gap_control(),
			$this->get_border_radius_control(),
			$this->get_title_color_control(),
			$this->get_price_color_control(),
			$this->get_button_controls(),
		];
	}

	/**
	 * Get sort controls
	 */
	protected function get_sort_controls(): array {
		return [
			$this->get_sort_by_control(),
			$this->get_sort_order_control(),
		];
	}

	protected function get_period_control(): array {
		return [
			'name'           => 'period',
			'label'          => __( 'Time Period', 'drc-advanced-woo-widgets' ),
			'type'           => \Elementor\Controls_Manager::SELECT,
			'options'        => Helper_Functions::get_period_options(),
			'default'        => 'total',
			'prefix_class'   => 'drc-period-',
		];
	}

	protected function get_category_control(): array {
		return [
			'name'           => 'category',
			'label'          => __( 'Category', 'drc-advanced-woo-widgets' ),
			'type'           => \Elementor\Controls_Manager::SELECT2,
			'options'        => Helper_Functions::get_product_categories(),
			'label_block'    => true,
			'multiple'       => false,
		];
	}

	protected function get_tags_control(): array {
		return [
			'name'           => 'tags',
			'label'          => __( 'Tags', 'drc-advanced-woo-widgets' ),
			'type'           => \Elementor\Controls_Manager::SELECT2,
			'options'        => Helper_Functions::get_product_tags(),
			'label_block'    => true,
			'multiple'       => true,
		];
	}

	protected function get_featured_control(): array {
		return [
			'name'           => 'featured_only',
			'label'          => __( 'Featured Only', 'drc-advanced-woo-widgets' ),
			'type'           => \Elementor\Controls_Manager::SWITCHER,
			'label_on'       => __( 'Yes', 'drc-advanced-woo-widgets' ),
			'label_off'      => __( 'No', 'drc-advanced-woo-widgets' ),
			'return_value'   => 'yes',
			'default'        => '',
		];
	}

	protected function get_onsale_control(): array {
		return [
			'name'           => 'onsale_only',
			'label'          => __( 'On Sale Only', 'drc-advanced-woo-widgets' ),
			'type'           => \Elementor\Controls_Manager::SWITCHER,
			'label_on'       => __( 'Yes', 'drc-advanced-woo-widgets' ),
			'label_off'      => __( 'No', 'drc-advanced-woo-widgets' ),
			'return_value'   => 'yes',
			'default'        => '',
		];
	}

	protected function get_stock_control(): array {
		return [
			'name'           => 'stock_status',
			'label'          => __( 'Stock Status', 'drc-advanced-woo-widgets' ),
			'type'           => \Elementor\Controls_Manager::SELECT,
			'options'        => Helper_Functions::get_stock_status_options(),
			'default'        => '',
		];
	}

	protected function get_layout_type_control(): array {
		return [
			'name'           => 'layout',
			'label'          => __( 'Layout', 'drc-advanced-woo-widgets' ),
			'type'           => \Elementor\Controls_Manager::SELECT,
			'options'        => Helper_Functions::get_layout_options(),
			'default'        => 'grid',
			'prefix_class'   => 'drc-layout-',
		];
	}

	protected function get_columns_control(): array {
		$breakpoints = [
			'' => __( 'Default', 'drc-advanced-woo-widgets' ),
			'1024' => __( 'Tablet', 'drc-advanced-woo-widgets' ),
			'768'  => __( 'Mobile', 'drc-advanced-woo-widgets' ),
		];

		$controls = [];

		foreach ( [ 1, 2, 3, 4 ] as $cols ) {
			$controls[ 'columns_' . $cols ] = [
				'name'      => 'columns',
				'label'     => __( 'Columns', 'drc-advanced-woo-widgets' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'default'   => 4,
				'min'       => 1,
				'max'       => 12,
			];
		}

		return [
			'name'         => 'columns',
			'label'        => __( 'Columns', 'drc-advanced-woo-widgets' ),
			'type'         => \Elementor\Controls_Manager::SELECT,
			'options'      => [
				'1' => '1',
				'2' => '2',
				'3' => '3',
				'4' => '4',
				'5' => '5',
				'6' => '6',
			],
			'default'      => '4',
			'tablet_default' => '3',
			'mobile_default' => '2',
			'responsive'  => true,
		];
	}

	protected function get_products_count_control(): array {
		return [
			'name'         => 'products_count',
			'label'        => __( 'Products Count', 'drc-advanced-woo-widgets' ),
			'type'         => \Elementor\Controls_Manager::NUMBER,
			'default'      => 8,
			'min'          => 1,
			'max'          => 100,
		];
	}

	protected function get_sort_by_control(): array {
		return [
			'name'         => 'sort_by',
			'label'        => __( 'Sort By', 'drc-advanced-woo-widgets' ),
			'type'         => \Elementor\Controls_Manager::SELECT,
			'options'      => Helper_Functions::get_sort_options(),
			'default'      => 'sales',
		];
	}

	protected function get_sort_order_control(): array {
		return [
			'name'         => 'sort_order',
			'label'        => __( 'Sort Order', 'drc-advanced-woo-widgets' ),
			'type'         => \Elementor\Controls_Manager::SELECT,
			'options'      => [
				'ASC'  => __( 'Ascending', 'drc-advanced-woo-widgets' ),
				'DESC' => __( 'Descending', 'drc-advanced-woo-widgets' ),
			],
			'default'      => 'DESC',
		];
	}

	protected function get_columns_gap_control(): array {
		return [
			'name'         => 'columns_gap',
			'label'        => __( 'Columns Gap', 'drc-advanced-woo-widgets' ),
			'type'         => \Elementor\Controls_Manager::SLIDER,
			'size_units'   => [ 'px', 'em', '%' ],
			'range'        => [
				'px' => [
					'min' => 0,
					'max' => 100,
				],
			],
			'default'      => [
				'unit' => 'px',
				'size' => 20,
			],
			'selectors'    => [
				'{{WRAPPER}} .drc-products-grid' => 'grid-gap: {{SIZE}}{{UNIT}};',
			],
		];
	}

	protected function get_items_gap_control(): array {
		return [
			'name'         => 'items_gap',
			'label'        => __( 'Items Gap', 'drc-advanced-woo-widgets' ),
			'type'         => \Elementor\Controls_Manager::SLIDER,
			'size_units'   => [ 'px', 'em' ],
			'range'        => [
				'px' => [
					'min' => 0,
					'max' => 100,
				],
			],
			'default'      => [
				'unit' => 'px',
				'size' => 20,
			],
			'selectors'    => [
				'{{WRAPPER}} .drc-product-item' => 'margin-bottom: {{SIZE}}{{UNIT}};',
			],
		];
	}

	protected function get_border_radius_control(): array {
		return [
			'name'         => 'border_radius',
			'label'        => __( 'Border Radius', 'drc-advanced-woo-widgets' ),
			'type'         => \Elementor\Controls_Manager::DIMENSIONS,
			'size_units'   => [ 'px', '%' ],
			'selectors'    => [
				'{{WRAPPER}} .drc-product-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		];
	}

	protected function get_title_color_control(): array {
		return [
			'name'         => 'title_color',
			'label'        => __( 'Title Color', 'drc-advanced-woo-widgets' ),
			'type'         => \Elementor\Controls_Manager::COLOR,
			'selectors'    => [
				'{{WRAPPER}} .drc-product-title' => 'color: {{VALUE}};',
			],
		];
	}

	protected function get_price_color_control(): array {
		return [
			'name'         => 'price_color',
			'label'        => __( 'Price Color', 'drc-advanced-woo-widgets' ),
			'type'         => \Elementor\Controls_Manager::COLOR,
			'selectors'    => [
				'{{WRAPPER}} .drc-product-price' => 'color: {{VALUE}};',
			],
		];
	}

	protected function get_button_controls(): array {
		return [
			[
				'name'      => 'button_text',
				'label'     => __( 'Button Text', 'drc-advanced-woo-widgets' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => __( 'View Product', 'drc-advanced-woo-widgets' ),
			],
			[
				'name'      => 'button_show',
				'label'     => __( 'Show Button', 'drc-advanced-woo-widgets' ),
				'type'      => \Elementor\Controls_Manager::SWITCHER,
				'label_on'  => __( 'Show', 'drc-advanced-woo-widgets' ),
				'label_off' => __( 'Hide', 'drc-advanced-woo-widgets' ),
				'default'   => 'yes',
			],
		];
	}

	/**
	 * Get products based on widget type
	 */
	abstract protected function get_products( array $settings ): array;

	/**
	 * Render products
	 */
	protected function render_products( array $products, array $settings ): void {
		if ( empty( $products ) ) {
			echo '<p class="drc-no-products">' . esc_html__( 'No products found.', 'drc-advanced-woo-widgets' ) . '</p>';
			return;
		}

		$layout = $settings['layout'] ?? 'grid';

		// Try theme override first
		$template = locate_template( "drc-aww/layouts/{$layout}.php" );
		if ( ! $template ) {
			$template = DRC_AWW_PLUGIN_DIR . "templates/layouts/{$layout}.php";
		}

		if ( file_exists( $template ) ) {
			include $template;
		} else {
			// Fallback to default grid
			$this->render_default_grid( $products, $settings );
		}
	}

	protected function render_default_grid( array $products, array $settings ): void {
		echo '<div class="drc-products-grid drc-columns-' . intval( $settings['columns'] ?? 4 ) . '">';

		foreach ( $products as $product_id ) {
			$product = wc_get_product( $product_id );
			if ( ! $product ) {
				continue;
			}

			$this->render_product_item( $product, $settings );
		}

		echo '</div>';
	}

	protected function render_product_item( $product, array $settings ): void {
		?>
		<div class="drc-product-item">
			<div class="drc-product-image">
				<a href="<?php echo esc_url( $product->get_permalink() ); ?>">
					<?php echo $product->get_image( 'woocommerce_thumbnail' ); ?>
				</a>
			</div>
			<h3 class="drc-product-title">
				<a href="<?php echo esc_url( $product->get_permalink() ); ?>">
					<?php echo esc_html( $product->get_name() ); ?>
				</a>
			</h3>
			<div class="drc-product-price">
				<?php echo $product->get_price_html(); ?>
			</div>
			<?php if ( ! empty( $settings['button_show'] ) ) : ?>
				<a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="drc-product-button">
					<?php echo esc_html( $settings['button_text'] ?? __( 'View Product', 'drc-advanced-woo-widgets' ) ); ?>
				</a>
			<?php endif; ?>
		</div>
		<?php
	}
}