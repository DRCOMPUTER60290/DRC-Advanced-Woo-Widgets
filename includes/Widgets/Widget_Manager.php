<?php
/**
 * Elementor widget manager
 */

namespace DRC\AWW\Widgets;

defined( 'ABSPATH' ) || exit;

class Widget_Manager {

	private static $instance = null;

	private function __construct() {
		add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );
		add_action( 'elementor/elements/categories_registered', [ $this, 'register_category' ] );
	}

	public static function instance(): Widget_Manager {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function register_category( $elements_manager ): void {
		$elements_manager->add_category(
			'drc-woo-widgets',
			[
				'title' => __( 'DRC Woo Widgets', 'drc-advanced-woo-widgets' ),
				'icon'  => 'fa fa-shopping-cart',
			]
		);
	}

	public function register_widgets( $widgets_manager ): void {
		$widgets_manager->register( new Best_Selling_Products() );
		$widgets_manager->register( new Deal_of_the_Week() );
		$widgets_manager->register( new Trending_Products() );
		$widgets_manager->register( new Popular_Products() );
		$widgets_manager->register( new Recently_Sold_Products() );
		$widgets_manager->register( new Flash_Sale_Products() );
	}
}