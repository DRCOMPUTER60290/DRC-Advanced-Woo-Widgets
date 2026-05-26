<?php
/**
 * Template loader for layouts
 */

namespace DRC\AWW\Helpers;

defined( 'ABSPATH' ) || exit;

class Template_Loader {

	private static $instance = null;

	private function __construct() {}

	public static function instance(): Template_Loader {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function load_template( string $template, array $args = [] ): void {
		$template_path = $this->locate_template( $template );

		if ( ! $template_path ) {
			return;
		}

		extract( $args );
		include $template_path;
	}

	public function locate_template( string $template ): ?string {
		// Theme override
		$template_path = locate_template( 'drc-aww/' . $template . '.php' );
		if ( $template_path ) {
			return $template_path;
		}

		// Plugin template
		$plugin_path = DRC_AWW_PLUGIN_DIR . 'templates/' . $template . '.php';
		if ( file_exists( $plugin_path ) ) {
			return $plugin_path;
		}

		return null;
	}

	public function get_template_part( string $template, array $args = [] ): string {
		$template_path = $this->locate_template( $template );

		if ( ! $template_path ) {
			return '';
		}

		ob_start();
		extract( $args );
		include $template_path;
		return ob_get_clean();
	}
}