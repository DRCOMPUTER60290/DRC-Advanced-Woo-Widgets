<?php
/**
 * Plugin activation/deactivation handlers
 */

namespace DRC\AWW\Core;

defined( 'ABSPATH' ) || exit;

class Activator {

	public static function activate(): void {
		global $wpdb;

		// Create custom stats table
		self::create_stats_table();

		// Schedule cron jobs
		if ( ! wp_next_scheduled( 'drc_aww_calculate_stats' ) ) {
			wp_schedule_event( time(), 'hourly', 'drc_aww_calculate_stats' );
		}

		// Flush rewrite rules
		flush_rewrite_rules();

		// Save plugin version
		update_option( 'drc_aww_version', DRC_AWW_PLUGIN_VERSION );
	}

	public static function deactivate(): void {
		// Clear scheduled hooks
		wp_clear_scheduled_hook( 'drc_aww_calculate_stats' );

		// Flush rewrite rules
		flush_rewrite_rules();

		// Clear all transients
		Helper_Functions::clear_all_transients();
	}

	private static function create_stats_table(): void {
		global $wpdb;

		$table_name = $wpdb->prefix . 'drc_product_stats';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table_name} (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			product_id bigint(20) UNSIGNED NOT NULL,
			stat_type varchar(50) NOT NULL DEFAULT 'sales',
			stat_period varchar(50) NOT NULL DEFAULT 'total',
			stat_value decimal(10,2) NOT NULL DEFAULT 0,
			stat_count int(11) NOT NULL DEFAULT 0,
			last_updated datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY product_type (product_id, stat_type),
			KEY stat_period (stat_period),
			KEY last_updated (last_updated)
		) {$charset_collate}";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}
}