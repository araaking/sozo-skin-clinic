<?php
/**
 * Plugin Name: Bulk Meta Editor
 * Description: Edit massal meta title & meta description SEO (Yoast), lengkap dengan history, rollback, dan import/export CSV.
 * Version: 1.2.0
 * Author: Sozo Skin Clinic SEO Team
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * License: GPL-2.0-or-later
 * Text Domain: bulk-meta-editor
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BME_VERSION', '1.2.0' );
define( 'BME_FILE', __FILE__ );
define( 'BME_PATH', plugin_dir_path( __FILE__ ) );
define( 'BME_URL', plugin_dir_url( __FILE__ ) );

require_once BME_PATH . 'includes/class-bme-provider.php';
require_once BME_PATH . 'includes/class-bme-db.php';
require_once BME_PATH . 'includes/class-bme-settings.php';
require_once BME_PATH . 'includes/class-bme-bulk-editor.php';
require_once BME_PATH . 'includes/class-bme-history.php';
require_once BME_PATH . 'includes/class-bme-csv.php';

/**
 * Aktivasi: buat tabel, jalankan migrasi, jadwalkan cleanup harian.
 */
function bme_activate() {
	BME_DB::create_table();
	BME_DB::maybe_migrate();
	BME_Settings::maybe_upgrade();

	$baseline = BME_DB::take_baseline();
	if ( $baseline > 0 ) {
		set_transient( 'bme_baseline_notice', (int) $baseline, 5 * MINUTE_IN_SECONDS );
	}

	if ( ! wp_next_scheduled( 'bme_daily_cleanup' ) ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'bme_daily_cleanup' );
	}
}
register_activation_hook( __FILE__, 'bme_activate' );

/**
 * Deaktivasi: hentikan cron. Data tidak dihapus.
 */
function bme_deactivate() {
	wp_clear_scheduled_hook( 'bme_daily_cleanup' );
}
register_deactivation_hook( __FILE__, 'bme_deactivate' );

/**
 * Bootstrap plugin.
 */
function bme_init() {
	BME_DB::init();
	BME_DB::maybe_upgrade();
	BME_Bulk_Editor::init();
	BME_History::init();
	BME_CSV::init();
	BME_Settings::init();
	BME_Settings::maybe_upgrade();
}
add_action( 'plugins_loaded', 'bme_init' );

/**
 * Pastikan cron cleanup selalu terjadwal, termasuk saat plugin di-update tanpa reaktivasi.
 */
function bme_maybe_schedule_cleanup() {
	if ( ! wp_next_scheduled( 'bme_daily_cleanup' ) ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'bme_daily_cleanup' );
	}
}
add_action( 'init', 'bme_maybe_schedule_cleanup' );

/**
 * Peringatan kalau tidak ada provider SEO yang didukung.
 */
function bme_provider_missing_notice() {
	if ( BME_Provider::instance() ) {
		return;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	echo '<div class="notice notice-warning"><p>';
	echo esc_html__( 'Bulk Meta Editor: tidak ada SEO plugin yang didukung (saat ini Yoast SEO). Plugin dalam keadaan idle.', 'bulk-meta-editor' );
	echo '</p></div>';
}
add_action( 'admin_notices', 'bme_provider_missing_notice' );

/**
 * Kabari admin berapa nilai lama yang terekam sebagai baseline saat aktivasi.
 */
function bme_baseline_notice() {
	$count = get_transient( 'bme_baseline_notice' );

	if ( false === $count ) {
		return;
	}

	delete_transient( 'bme_baseline_notice' );

	if ( $count < 1 || ! current_user_can( 'manage_options' ) ) {
		return;
	}

	echo '<div class="notice notice-success is-dismissible"><p>';
	printf(
		/* translators: %d = jumlah nilai baseline */
		esc_html__( 'Bulk Meta Editor: %d nilai meta lama berhasil direkam sebagai baseline. Lihat di menu History.', 'bulk-meta-editor' ),
		(int) $count
	);
	echo '</p></div>';
}
add_action( 'admin_notices', 'bme_baseline_notice' );
