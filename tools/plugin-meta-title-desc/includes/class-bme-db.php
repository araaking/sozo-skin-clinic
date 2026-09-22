<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BME_DB {

	const DB_VERSION = '1.0.0';

	public static function init() {
		add_action( 'bme_daily_cleanup', array( __CLASS__, 'cleanup' ) );
	}

	public static function table() {
		global $wpdb;
		return $wpdb->prefix . 'bme_history';
	}

	public static function legacy_table() {
		global $wpdb;
		return $wpdb->prefix . 'sozo_meta_history';
	}

	public static function create_table() {
		global $wpdb;

		$table   = self::table();
		$charset = $wpdb->get_charset_collate();

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$sql = "CREATE TABLE {$table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			post_id bigint(20) unsigned NOT NULL,
			field_key varchar(50) NOT NULL,
			old_value text NULL,
			new_value text NULL,
			source varchar(20) NOT NULL DEFAULT 'manual',
			user_id bigint(20) unsigned NOT NULL DEFAULT 0,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY post_field (post_id, field_key),
			KEY created_at (created_at),
			KEY source (source)
		) {$charset};";

		dbDelta( $sql );
	}

	/**
	 * Jalankan create_table + migrasi saat versi skema berubah.
	 */
	public static function maybe_upgrade() {
		if ( get_option( 'bme_db_version' ) === self::DB_VERSION ) {
			return;
		}

		self::create_table();
		self::maybe_migrate();
		update_option( 'bme_db_version', self::DB_VERSION );
	}

	/**
	 * Pindahkan data dari tabel lama `sozo_meta_history` kalau ada dan tabel baru masih kosong.
	 */
	public static function maybe_migrate() {
		global $wpdb;

		$old = self::legacy_table();
		$new = self::table();

		if ( $old === $new ) {
			return;
		}

		$old_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $old ) ) );
		if ( ! $old_exists ) {
			return;
		}

		$new_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$new}" );
		if ( $new_count > 0 ) {
			return;
		}

		$wpdb->query(
			"INSERT INTO {$new} (id, post_id, field_key, old_value, new_value, user_id, created_at, source)
			SELECT id, post_id, field_key, old_value, new_value, user_id, created_at, 'manual' FROM {$old}"
		);

		update_option( 'bme_migrated_legacy', 1 );
	}

	/**
	 * Rekam nilai meta yang sudah ada sebagai titik awal (baseline).
	 *
	 * Dijalankan saat aktivasi dan bisa diulang manual dari Settings. Hanya
	 * mencatat pasangan post+field yang belum punya baris baseline, jadi tidak
	 * menimpa baseline lama dan tidak menghasilkan duplikat.
	 *
	 * @return int Jumlah baris baseline baru.
	 */
	public static function take_baseline() {
		global $wpdb;

		$provider = BME_Provider::instance();
		if ( ! $provider ) {
			return 0;
		}

		$types = BME_Settings::supported_post_types();
		$keys  = array_values( $provider->field_keys() );

		if ( empty( $types ) || empty( $keys ) ) {
			return 0;
		}

		$table   = self::table();
		$key_ph  = implode( ', ', array_fill( 0, count( $keys ), '%s' ) );
		$type_ph = implode( ', ', array_fill( 0, count( $types ), '%s' ) );
		$now     = current_time( 'mysql' );

		$select = "SELECT pm.post_id, pm.meta_key, pm.meta_value
			FROM {$wpdb->postmeta} pm
			INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
			WHERE pm.meta_key IN ({$key_ph})
				AND p.post_type IN ({$type_ph})
				AND p.post_status NOT IN ('auto-draft', 'trash', 'inherit')";

		$base_params = array_merge( $keys, $types );
		$batch_size  = 1000;
		$offset      = 0;
		$inserted    = 0;

		do {
			$candidates = $wpdb->get_results(
				$wpdb->prepare(
					$select . ' LIMIT %d OFFSET %d',
					array_merge( $base_params, array( $batch_size, $offset ) )
				)
			);

			$offset += $batch_size;

			if ( empty( $candidates ) ) {
				break;
			}

			$post_ids = array();
			foreach ( $candidates as $candidate ) {
				$post_ids[] = (int) $candidate->post_id;
			}
			$post_ids = array_values( array_unique( $post_ids ) );
			$id_ph    = implode( ', ', array_fill( 0, count( $post_ids ), '%d' ) );

			$existing_rows = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT post_id, field_key FROM {$table} WHERE source = 'baseline' AND post_id IN ({$id_ph})",
					$post_ids
				)
			);

			$exists = array();
			foreach ( $existing_rows as $existing ) {
				$exists[ $existing->post_id . ':' . $existing->field_key ] = true;
			}

			$missing = array();
			foreach ( $candidates as $candidate ) {
				$pair = $candidate->post_id . ':' . $candidate->meta_key;

				if ( isset( $exists[ $pair ] ) ) {
					continue;
				}

				$exists[ $pair ] = true;
				$missing[]       = $candidate;
			}

			if ( empty( $missing ) ) {
				continue;
			}

			foreach ( array_chunk( $missing, 100 ) as $chunk ) {
				$placeholders = array();
				$params       = array();

				foreach ( $chunk as $row ) {
					$placeholders[] = '(%d, %s, %s, %s, %s, %d, %s)';
					$params[]       = (int) $row->post_id;
					$params[]       = $row->meta_key;
					$params[]       = $row->meta_value;
					$params[]       = $row->meta_value;
					$params[]       = 'baseline';
					$params[]       = 0;
					$params[]       = $now;
				}

				$wpdb->query(
					$wpdb->prepare(
						"INSERT INTO {$table} (post_id, field_key, old_value, new_value, source, user_id, created_at) VALUES " . implode( ', ', $placeholders ),
						$params
					)
				);

				$inserted += (int) $wpdb->rows_affected;
			}
		} while ( count( $candidates ) === $batch_size );

		return $inserted;
	}

	/**
	 * Catat satu perubahan. Lewati kalau nilai lama dan baru sama.
	 *
	 * @param string $source manual|bulk|csv|rollback
	 * @return int ID baris, 0 kalau tidak dicatat.
	 */
	public static function log( $post_id, $field_key, $old_value, $new_value, $source = 'manual', $user_id = null ) {
		global $wpdb;

		$provider = BME_Provider::instance();
		if ( ! $provider || ! $provider->owns_key( $field_key ) ) {
			return 0;
		}

		$post_id = (int) $post_id;
		if ( $post_id <= 0 ) {
			return 0;
		}

		$old_value = is_string( $old_value ) ? $old_value : '';
		$new_value = is_string( $new_value ) ? $new_value : '';

		if ( $old_value === $new_value ) {
			return 0;
		}

		if ( $user_id === null ) {
			$user_id = get_current_user_id();
		}

		$source = in_array( $source, array( 'manual', 'bulk', 'csv', 'rollback', 'baseline' ), true ) ? $source : 'manual';

		$inserted = $wpdb->insert(
			self::table(),
			array(
				'post_id'    => $post_id,
				'field_key'  => $field_key,
				'old_value'  => $old_value,
				'new_value'  => $new_value,
				'source'     => $source,
				'user_id'    => (int) $user_id,
				'created_at' => current_time( 'mysql' ),
			),
			array( '%d', '%s', '%s', '%s', '%s', '%d', '%s' )
		);

		return $inserted ? (int) $wpdb->insert_id : 0;
	}

	public static function get_history( $args = array() ) {
		global $wpdb;

		$table = self::table();

		$defaults = array(
			'post_id'   => 0,
			'field_key' => '',
			'user_id'   => 0,
			'source'    => '',
			'date_from' => '',
			'date_to'   => '',
			'page'      => 1,
			'per_page'  => 50,
		);
		$args = wp_parse_args( $args, $defaults );

		$where  = array( '1=1' );
		$params = array();

		if ( ! empty( $args['post_id'] ) ) {
			$where[]  = 'h.post_id = %d';
			$params[] = (int) $args['post_id'];
		}

		if ( ! empty( $args['field_key'] ) ) {
			$provider = BME_Provider::instance();
			if ( $provider && $provider->owns_key( $args['field_key'] ) ) {
				$where[]  = 'h.field_key = %s';
				$params[] = $args['field_key'];
			}
		}

		if ( ! empty( $args['user_id'] ) ) {
			$where[]  = 'h.user_id = %d';
			$params[] = (int) $args['user_id'];
		}

		if ( ! empty( $args['source'] ) && in_array( $args['source'], array( 'manual', 'bulk', 'csv', 'rollback', 'baseline' ), true ) ) {
			$where[]  = 'h.source = %s';
			$params[] = $args['source'];
		}

		if ( ! empty( $args['date_from'] ) && preg_match( '/^\d{4}-\d{2}-\d{2}$/', $args['date_from'] ) ) {
			$where[]  = 'h.created_at >= %s';
			$params[] = $args['date_from'] . ' 00:00:00';
		}

		if ( ! empty( $args['date_to'] ) && preg_match( '/^\d{4}-\d{2}-\d{2}$/', $args['date_to'] ) ) {
			$where[]  = 'h.created_at <= %s';
			$params[] = $args['date_to'] . ' 23:59:59';
		}

		$where_sql = implode( ' AND ', $where );
		$page      = max( 1, (int) $args['page'] );
		$per_page  = min( 200, max( 10, (int) $args['per_page'] ) );
		$offset    = ( $page - 1 ) * $per_page;

		$count_sql = "SELECT COUNT(*) FROM {$table} h WHERE {$where_sql}";
		$total     = (int) ( $params ? $wpdb->get_var( $wpdb->prepare( $count_sql, $params ) ) : $wpdb->get_var( $count_sql ) );

		$params[] = $per_page;
		$params[] = $offset;

		$sql = "SELECT h.*, p.post_title AS page_title
			FROM {$table} h
			LEFT JOIN {$wpdb->posts} p ON p.ID = h.post_id
			WHERE {$where_sql}
			ORDER BY h.created_at DESC, h.id DESC
			LIMIT %d OFFSET %d";

		$rows = $wpdb->get_results( $wpdb->prepare( $sql, $params ) );

		return array(
			'rows'     => $rows ? $rows : array(),
			'total'    => $total,
			'page'     => $page,
			'per_page' => $per_page,
		);
	}

	public static function get_row( $id ) {
		global $wpdb;
		return $wpdb->get_row(
			$wpdb->prepare( 'SELECT * FROM ' . self::table() . ' WHERE id = %d', (int) $id )
		);
	}

	/**
	 * Perubahan terakhir per post, untuk kolom "terakhir diubah" di editor.
	 *
	 * @param int[] $post_ids
	 * @return array<int, object>
	 */
	public static function last_edits( $post_ids ) {
		global $wpdb;

		$post_ids = array_values( array_filter( array_map( 'intval', (array) $post_ids ) ) );
		if ( empty( $post_ids ) ) {
			return array();
		}

		$table = self::table();
		$in    = implode( ',', $post_ids );

		$rows = $wpdb->get_results(
			"SELECT h.post_id, h.created_at, h.user_id, h.source, h.field_key
			FROM {$table} h
			INNER JOIN (
				SELECT post_id, MAX(id) AS max_id FROM {$table}
				WHERE post_id IN ({$in}) AND source <> 'baseline'
				GROUP BY post_id
			) latest ON latest.max_id = h.id"
		);

		$map = array();
		foreach ( (array) $rows as $row ) {
			$map[ (int) $row->post_id ] = $row;
		}

		return $map;
	}

	/**
	 * Perubahan terakhir per post per field, untuk kolom "before" di export.
	 * Nilai before = old_value dari baris history terakhir (non-baseline).
	 *
	 * @param int[] $post_ids
	 * @return array<int, array<string, object>>
	 */
	public static function last_field_edits( $post_ids ) {
		global $wpdb;

		$post_ids = array_values( array_filter( array_map( 'intval', (array) $post_ids ) ) );
		if ( empty( $post_ids ) ) {
			return array();
		}

		$table = self::table();
		$in    = implode( ',', $post_ids );

		$rows = $wpdb->get_results(
			"SELECT h.post_id, h.field_key, h.old_value, h.new_value, h.created_at, h.user_id, h.source
			FROM {$table} h
			INNER JOIN (
				SELECT post_id, field_key, MAX(id) AS max_id FROM {$table}
				WHERE post_id IN ({$in}) AND source <> 'baseline'
				GROUP BY post_id, field_key
			) latest ON latest.max_id = h.id"
		);

		$map = array();
		foreach ( (array) $rows as $row ) {
			$map[ (int) $row->post_id ][ $row->field_key ] = $row;
		}

		return $map;
	}

	/**
	 * User yang pernah muncul di history, untuk filter.
	 *
	 * @return int[]
	 */
	public static function history_users() {
		global $wpdb;
		$table = self::table();
		$ids   = $wpdb->get_col( "SELECT DISTINCT user_id FROM {$table} WHERE user_id > 0" );
		return array_map( 'intval', (array) $ids );
	}

	/**
	 * Hapus log lebih lama dari retensi. Nilai 0 = simpan selamanya.
	 */
	public static function cleanup() {
		global $wpdb;

		$days = (int) BME_Settings::get( 'retention_days' );
		if ( $days < 1 ) {
			return;
		}

		$table = self::table();
		$wpdb->query(
			$wpdb->prepare( "DELETE FROM {$table} WHERE source <> 'baseline' AND created_at < DATE_SUB( NOW(), INTERVAL %d DAY )", $days )
		);
	}
}
