<?php
/**
 * Admin interface for DRC Advanced Woo Widgets
 */

namespace DRC\AWW\Admin;

defined( 'ABSPATH' ) || exit;

use DRC\AWW\Cache\Product_Cache;

class Admin {

	private static $instance = null;

	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_scripts' ] );
		add_action( 'add_meta_boxes', [ $this, 'add_product_meta_box' ] );
		add_action( 'save_post_product', [ $this, 'save_product_meta' ] );
	}

	public function add_admin_menu(): void {
		add_menu_page(
			__( 'DRC Woo Widgets', 'drc-advanced-woo-widgets' ),
			__( 'DRC Woo Widgets', 'drc-advanced-woo-widgets' ),
			'manage_options',
			'drc-aww-settings',
			[ $this, 'render_settings_page' ],
			'dashicons-chart-bar',
			58
		);
	}

	public function register_settings(): void {
		register_setting( 'drc_aww_settings', 'drc_aww_cache_duration' );
		register_setting( 'drc_aww_settings', 'drc_aww_products_per_page' );
		register_setting( 'drc_aww_settings', 'drc_aww_enable_ajax' );
		register_setting( 'drc_aww_settings', 'drc_aww_cron_interval' );

		add_settings_section(
			'drc_aww_general',
			__( 'General Settings', 'drc-advanced-woo-widgets' ),
			null,
			'drc-aww-settings'
		);

		add_settings_field(
			'drc_aww_cache_duration',
			__( 'Cache Duration (hours)', 'drc-advanced-woo-widgets' ),
			[ $this, 'render_number_field' ],
			'drc-aww-settings',
			'drc_aww_general',
			[ 'name' => 'drc_aww_cache_duration', 'default' => 1 ]
		);

		add_settings_field(
			'drc_aww_enable_ajax',
			__( 'Enable AJAX Loading', 'drc-advanced-woo-widgets' ),
			[ $this, 'render_checkbox_field' ],
			'drc-aww-settings',
			'drc_aww_general',
			[ 'name' => 'drc_aww_enable_ajax', 'default' => '1' ]
		);
	}

	public function render_settings_page(): void {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'DRC Advanced Woo Widgets', 'drc-advanced-woo-widgets' ); ?></h1>

			<div class="drc-aww-admin-header">
				<p><?php esc_html_e( 'Configure your advanced WooCommerce product widgets.', 'drc-advanced-woo-widgets' ); ?></p>
			</div>

			<form method="post" action="options.php">
				<?php settings_fields( 'drc_aww_settings' ); ?>
				<?php do_settings_sections( 'drc-aww-settings' ); ?>
				<?php submit_button(); ?>
			</form>

			<div class="drc-aww-stats-overview">
				<h2><?php esc_html_e( 'Product Statistics', 'drc-advanced-woo-widgets' ); ?></h2>
				<?php $this->render_stats_table(); ?>
			</div>
		</div>
		<?php
	}

	public function render_number_field( array $args ): void {
		$name  = $args['name'];
		$value = get_option( $name, $args['default'] ?? '' );
		printf( '<input type="number" name="%s" value="%s" class="small-text" />', esc_attr( $name ), esc_attr( $value ) );
	}

	public function render_checkbox_field( array $args ): void {
		$name  = $args['name'];
		$value = get_option( $name, $args['default'] ?? '0' );
		printf( '<input type="checkbox" name="%s" value="1" %s />', esc_attr( $name ), checked( $value, '1', false ) );
	}

	private function render_stats_table(): void {
		global $wpdb;
		$table = $wpdb->prefix . 'drc_product_stats';

		$results = $wpdb->get_results( "
			SELECT product_id, stat_type, stat_value, stat_count
			FROM {$table}
			WHERE stat_period = 'total'
			ORDER BY stat_value DESC
			LIMIT 20
		" );

		if ( empty( $results ) ) {
			echo '<p>' . esc_html__( 'No product statistics available yet.', 'drc-advanced-woo-widgets' ) . '</p>';
			return;
		}

		echo '<table class="widefat fixed striped">';
		echo '<thead><tr>';
		echo '<th>' . esc_html__( 'Product', 'drc-advanced-woo-widgets' ) . '</th>';
		echo '<th>' . esc_html__( 'Type', 'drc-advanced-woo-widgets' ) . '</th>';
		echo '<th>' . esc_html__( 'Value', 'drc-advanced-woo-widgets' ) . '</th>';
		echo '<th>' . esc_html__( 'Count', 'drc-advanced-woo-widgets' ) . '</th>';
		echo '</tr></thead><tbody>';

		foreach ( $results as $row ) {
			$product = wc_get_product( $row->product_id );
			echo '<tr>';
			echo '<td>' . ( $product ? esc_html( $product->get_name() ) : '#' . absint( $row->product_id ) ) . '</td>';
			echo '<td>' . esc_html( $row->stat_type ) . '</td>';
			echo '<td>' . esc_html( $row->stat_value ) . '</td>';
			echo '<td>' . esc_html( $row->stat_count ) . '</td>';
			echo '</tr>';
		}

		echo '</tbody></table>';
	}

	public function enqueue_admin_scripts( string $hook ): void {
		if ( 'toplevel_page_drc-aww-settings' !== $hook ) {
			return;
		}

		wp_enqueue_style( 'drc-aww-admin', DRC_AWW_PLUGIN_URL . 'assets/css/admin.css', [], DRC_AWW_PLUGIN_VERSION );
	}

	public function add_product_meta_box(): void {
		add_meta_box(
			'drc_aww_product_stats',
			__( 'DRC Product Stats', 'drc-advanced-woo-widgets' ),
			[ $this, 'render_product_meta_box' ],
			'product',
			'side',
			'default'
		);
	}

	public function render_product_meta_box( \WP_Post $post ): void {
		$views = (int) get_post_meta( $post->ID, '_drc_product_views', true );
		?>
		<p>
			<label><?php esc_html_e( 'Views:', 'drc-advanced-woo-widgets' ); ?></label>
			<strong><?php echo absint( $views ); ?></strong>
		</p>
		<?php
	}

	public function save_product_meta( int $post_id ): void {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
	}
}