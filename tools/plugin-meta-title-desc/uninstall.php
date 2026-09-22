<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}bme_history" );

delete_option( 'bme_settings' );
delete_option( 'bme_db_version' );
delete_option( 'bme_migrated_legacy' );

wp_clear_scheduled_hook( 'bme_daily_cleanup' );

/*
 * Tabel legacy `sozo_meta_history` sengaja tidak dihapus. Data lama tetap
 * tersedia kalau plugin perlu dipasang ulang atau dimigrasi manual.
 */
