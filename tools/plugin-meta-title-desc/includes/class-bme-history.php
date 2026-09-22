<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BME_History {

	/**
	 * Counter pause logging. Dipakai BME_Bulk_Editor dan BME_CSV saat menulis meta
	 * supaya tidak dobel log dengan hook WordPress.
	 *
	 * @var int
	 */
	private static $paused = 0;

	/**
	 * Cache nilai lama sebelum update_post_meta jalan.
	 *
	 * @var array<string, string>
	 */
	private static $old_cache = array();

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'handle_restore' ) );
		add_action( 'wp_ajax_bme_search_posts', array( __CLASS__, 'ajax_search_posts' ) );

		add_filter( 'update_post_metadata', array( __CLASS__, 'capture_old' ), 10, 5 );
		add_action( 'updated_post_meta', array( __CLASS__, 'log_update' ), 10, 4 );
		add_action( 'added_post_meta', array( __CLASS__, 'log_update' ), 10, 4 );
		add_action( 'deleted_post_meta', array( __CLASS__, 'log_delete' ), 10, 4 );
	}

	public static function menu() {
		add_submenu_page(
			'bulk-meta-editor',
			__( 'Meta History', 'bulk-meta-editor' ),
			__( 'History', 'bulk-meta-editor' ),
			BME_Settings::capability(),
			'bulk-meta-editor-history',
			array( __CLASS__, 'render' )
		);
	}

	public static function pause() {
		self::$paused++;
	}

	public static function resume() {
		self::$paused = max( 0, self::$paused - 1 );
	}

	private static function is_paused() {
		return self::$paused > 0;
	}

	private static function cache_key( $post_id, $meta_key ) {
		return $post_id . ':' . $meta_key;
	}

	/**
	 * Cek apakah perubahan meta ini layak dicatat.
	 */
	private static function should_log( $object_id, $meta_key ) {
		if ( ! (int) BME_Settings::get( 'log_external' ) ) {
			return false;
		}
		if ( wp_is_post_revision( $object_id ) || wp_is_post_autosave( $object_id ) ) {
			return false;
		}
		if ( ! BME_Settings::is_supported_post( $object_id ) ) {
			return false;
		}

		$provider = BME_Provider::instance();
		return $provider && $provider->owns_key( $meta_key );
	}

	/**
	 * Filter update_post_metadata: simpan nilai lama, kembalikan nilai asli supaya WP lanjut.
	 */
	public static function capture_old( $check, $object_id, $meta_key, $meta_value, $prev_value ) {
		if ( self::is_paused() || ! self::should_log( $object_id, $meta_key ) ) {
			return $check;
		}

		self::$old_cache[ self::cache_key( $object_id, $meta_key ) ] = (string) get_post_meta( $object_id, $meta_key, true );

		return $check;
	}

	/**
	 * Catat perubahan dari luar plugin: metabox Yoast, editor klasik, import plugin lain.
	 */
	public static function log_update( $meta_id, $object_id, $meta_key, $meta_value ) {
		if ( self::is_paused() || ! self::should_log( $object_id, $meta_key ) ) {
			return;
		}

		$key = self::cache_key( $object_id, $meta_key );
		$old = array_key_exists( $key, self::$old_cache ) ? self::$old_cache[ $key ] : '';
		unset( self::$old_cache[ $key ] );

		$new = is_string( $meta_value ) ? $meta_value : maybe_serialize( $meta_value );
		if ( $old === $new ) {
			return;
		}

		BME_DB::log( $object_id, $meta_key, $old, $new, 'manual' );
	}

	/**
	 * Catat penghapusan meta.
	 */
	public static function log_delete( $meta_ids, $object_id, $meta_key, $meta_value ) {
		if ( self::is_paused() || ! self::should_log( $object_id, $meta_key ) ) {
			return;
		}

		$old = is_string( $meta_value ) ? $meta_value : maybe_serialize( $meta_value );
		BME_DB::log( $object_id, $meta_key, $old, '', 'manual' );
	}

	/**
	 * Rollback satu baris history. Selalu menulis entri baru, tidak pernah menghapus log.
	 */
	public static function handle_restore() {
		if ( empty( $_POST['bme_restore_nonce'] ) || empty( $_POST['bme_restore_id'] ) ) {
			return;
		}

		if ( ! BME_Settings::can_access() ) {
			wp_die( esc_html__( 'Kamu tidak punya akses.', 'bulk-meta-editor' ) );
		}

		check_admin_referer( 'bme_restore', 'bme_restore_nonce' );

		$row = BME_DB::get_row( (int) $_POST['bme_restore_id'] );
		if ( ! $row ) {
			return;
		}

		if ( ! BME_Settings::is_supported_post( $row->post_id ) || ! BME_Settings::can_edit_post( $row->post_id ) ) {
			wp_die( esc_html__( 'Kamu tidak punya akses ke post ini.', 'bulk-meta-editor' ) );
		}

		$provider = BME_Provider::instance();
		$field    = $provider ? $provider->field_from_key( $row->field_key ) : '';

		if ( ! $provider || '' === $field ) {
			return;
		}

		$current = $provider->get( $row->post_id, $field );

		self::pause();
		$provider->set( $row->post_id, $field, (string) $row->old_value );
		self::resume();

		BME_DB::log( $row->post_id, $row->field_key, $current, (string) $row->old_value, 'rollback' );

		$redirect = isset( $_POST['bme_redirect'] )
			? wp_validate_redirect( esc_url_raw( wp_unslash( $_POST['bme_redirect'] ) ), admin_url( 'admin.php?page=bulk-meta-editor-history' ) )
			: admin_url( 'admin.php?page=bulk-meta-editor-history' );

		wp_safe_redirect( add_query_arg( array( 'restored' => 1 ), $redirect ) );
		exit;
	}

	public static function source_label( $source ) {
		$labels = array(
			'manual'   => __( 'Manual / Yoast', 'bulk-meta-editor' ),
			'bulk'     => __( 'Bulk Editor', 'bulk-meta-editor' ),
			'csv'      => __( 'Import CSV', 'bulk-meta-editor' ),
			'rollback' => __( 'Rollback', 'bulk-meta-editor' ),
			'baseline' => __( 'Baseline (sebelum plugin)', 'bulk-meta-editor' ),
		);

		return isset( $labels[ $source ] ) ? $labels[ $source ] : $source;
	}

	public static function ajax_search_posts() {
		check_ajax_referer( 'bme_ajax', 'nonce' );

		if ( ! BME_Settings::can_access() ) {
			wp_send_json_error( array(), 403 );
		}

		$term  = isset( $_GET['term'] ) ? sanitize_text_field( wp_unslash( $_GET['term'] ) ) : '';
		$posts = get_posts(
			array(
				'post_type'        => BME_Settings::supported_post_types(),
				'post_status'      => array( 'publish', 'draft', 'pending', 'private', 'future' ),
				's'                => $term,
				'posts_per_page'   => 20,
				'orderby'          => 'title',
				'order'            => 'ASC',
				'suppress_filters' => false,
			)
		);

		$results = array();
		foreach ( $posts as $post ) {
			$results[] = array(
				'id'   => (int) $post->ID,
				'text' => sprintf( '%s (#%d)', $post->post_title ? $post->post_title : __( '(tanpa judul)', 'bulk-meta-editor' ), $post->ID ),
			);
		}

		wp_send_json_success( $results );
	}

	public static function render() {
		if ( ! BME_Settings::can_access() ) {
			wp_die( esc_html__( 'Kamu tidak punya akses ke halaman ini.', 'bulk-meta-editor' ) );
		}

		$provider = BME_Provider::instance();
		if ( ! $provider ) {
			echo '<div class="wrap bme-wrap"><h1>' . esc_html__( 'Meta History', 'bulk-meta-editor' ) . '</h1>';
			echo '<div class="notice notice-error"><p>' . esc_html__( 'Tidak ada SEO plugin yang didukung.', 'bulk-meta-editor' ) . '</p></div></div>';
			return;
		}

		$post_id   = isset( $_GET['post_id'] ) ? (int) $_GET['post_id'] : 0;
		$field_key = isset( $_GET['field'] ) ? sanitize_key( wp_unslash( $_GET['field'] ) ) : '';
		$user_id   = isset( $_GET['user_id'] ) ? (int) $_GET['user_id'] : 0;
		$source    = isset( $_GET['source'] ) ? sanitize_key( wp_unslash( $_GET['source'] ) ) : '';
		$date_from = isset( $_GET['from'] ) ? sanitize_text_field( wp_unslash( $_GET['from'] ) ) : '';
		$date_to   = isset( $_GET['to'] ) ? sanitize_text_field( wp_unslash( $_GET['to'] ) ) : '';
		$paged     = isset( $_GET['paged'] ) ? max( 1, (int) $_GET['paged'] ) : 1;

		if ( ! $provider->owns_key( $field_key ) ) {
			$field_key = '';
		}

		$result = BME_DB::get_history(
			array(
				'post_id'   => $post_id,
				'field_key' => $field_key,
				'user_id'   => $user_id,
				'source'    => $source,
				'date_from' => $date_from,
				'date_to'   => $date_to,
				'page'      => $paged,
				'per_page'  => BME_Settings::per_page(),
			)
		);

		$user_ids   = BME_DB::history_users();
		$post_label = '';
		if ( $post_id ) {
			$post       = get_post( $post_id );
			$post_label = $post ? $post->post_title : sprintf( __( '(post dihapus #%d)', 'bulk-meta-editor' ), $post_id );
		}

		$base_args = array(
			'page'    => 'bulk-meta-editor-history',
			'post_id' => $post_id,
			'field'   => $field_key,
			'user_id' => $user_id,
			'source'  => $source,
			'from'    => $date_from,
			'to'      => $date_to,
		);
		?>
		<div class="wrap bme-wrap">
			<h1><?php esc_html_e( 'Meta History', 'bulk-meta-editor' ); ?></h1>

			<?php if ( isset( $_GET['restored'] ) ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Nilai lama berhasil dikembalikan dan tercatat di history.', 'bulk-meta-editor' ); ?></p></div>
			<?php endif; ?>

			<form method="get" class="bme-toolbar">
				<input type="hidden" name="page" value="bulk-meta-editor-history" />

				<div class="bme-autocomplete" data-bme-autocomplete>
					<input type="text" class="bme-ac-input" value="<?php echo esc_attr( $post_label ); ?>" placeholder="<?php esc_attr_e( 'Semua post — ketik untuk mencari…', 'bulk-meta-editor' ); ?>" autocomplete="off" />
					<input type="hidden" name="post_id" class="bme-ac-value" value="<?php echo (int) $post_id; ?>" />
					<div class="bme-ac-results" hidden></div>
				</div>

				<select name="field">
					<option value=""><?php esc_html_e( 'Semua field', 'bulk-meta-editor' ); ?></option>
					<?php foreach ( $provider->field_keys() as $field => $key ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $field_key, $key ); ?>>
							<?php echo esc_html( $provider->field_label( $key ) ); ?>
						</option>
					<?php endforeach; ?>
				</select>

				<select name="user_id">
					<option value="0"><?php esc_html_e( 'Semua user', 'bulk-meta-editor' ); ?></option>
					<?php foreach ( $user_ids as $uid ) : ?>
						<?php $user = get_userdata( $uid ); ?>
						<option value="<?php echo (int) $uid; ?>" <?php selected( $user_id, $uid ); ?>>
							<?php echo esc_html( $user ? $user->display_name : sprintf( __( 'user #%d', 'bulk-meta-editor' ), $uid ) ); ?>
						</option>
					<?php endforeach; ?>
				</select>

				<select name="source">
					<option value=""><?php esc_html_e( 'Semua sumber', 'bulk-meta-editor' ); ?></option>
					<?php foreach ( array( 'manual', 'bulk', 'csv', 'rollback', 'baseline' ) as $source_option ) : ?>
						<option value="<?php echo esc_attr( $source_option ); ?>" <?php selected( $source, $source_option ); ?>>
							<?php echo esc_html( self::source_label( $source_option ) ); ?>
						</option>
					<?php endforeach; ?>
				</select>

				<label class="bme-date">
					<?php esc_html_e( 'Dari', 'bulk-meta-editor' ); ?>
					<input type="date" name="from" value="<?php echo esc_attr( $date_from ); ?>" />
				</label>
				<label class="bme-date">
					<?php esc_html_e( 'Sampai', 'bulk-meta-editor' ); ?>
					<input type="date" name="to" value="<?php echo esc_attr( $date_to ); ?>" />
				</label>

				<?php submit_button( __( 'Filter', 'bulk-meta-editor' ), 'secondary', '', false ); ?>
				<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=bulk-meta-editor-history' ) ); ?>"><?php esc_html_e( 'Reset', 'bulk-meta-editor' ); ?></a>
				<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=bulk-meta-editor' ) ); ?>"><?php esc_html_e( 'Kembali ke Editor', 'bulk-meta-editor' ); ?></a>
			</form>

			<table class="widefat striped bme-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Waktu', 'bulk-meta-editor' ); ?></th>
						<th><?php esc_html_e( 'Post', 'bulk-meta-editor' ); ?></th>
						<th><?php esc_html_e( 'Field', 'bulk-meta-editor' ); ?></th>
						<th><?php esc_html_e( 'User', 'bulk-meta-editor' ); ?></th>
						<th><?php esc_html_e( 'Sumber', 'bulk-meta-editor' ); ?></th>
						<th class="bme-col-change"><?php esc_html_e( 'Perubahan', 'bulk-meta-editor' ); ?></th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( $result['rows'] ) : ?>
						<?php foreach ( $result['rows'] as $row ) : ?>
							<?php $user = get_userdata( (int) $row->user_id ); ?>
							<tr>
								<td><?php echo esc_html( mysql2date( 'd M Y H:i', $row->created_at ) ); ?></td>
								<td>
									<?php if ( get_post( $row->post_id ) ) : ?>
										<a href="<?php echo esc_url( get_edit_post_link( $row->post_id ) ); ?>">
											<?php echo esc_html( $row->page_title ? $row->page_title : '#' . (int) $row->post_id ); ?>
										</a>
									<?php else : ?>
										<?php echo esc_html( sprintf( __( '(post dihapus #%d)', 'bulk-meta-editor' ), $row->post_id ) ); ?>
									<?php endif; ?>
								</td>
								<td><?php echo esc_html( $provider->field_label( $row->field_key ) ); ?></td>
								<td><?php echo $user ? esc_html( $user->display_name ) : '—'; ?></td>
								<td><?php echo esc_html( self::source_label( $row->source ) ); ?></td>
								<td>
									<div class="bme-old"><span><?php esc_html_e( 'SEBELUM:', 'bulk-meta-editor' ); ?></span> <?php echo esc_html( mb_strimwidth( (string) $row->old_value, 0, 160, '…' ) ); ?></div>
									<div class="bme-new"><span><?php esc_html_e( 'SESUDAH:', 'bulk-meta-editor' ); ?></span> <?php echo esc_html( mb_strimwidth( (string) $row->new_value, 0, 160, '…' ) ); ?></div>
								</td>
								<td>
									<form method="post" class="bme-restore-form">
										<?php wp_nonce_field( 'bme_restore', 'bme_restore_nonce' ); ?>
										<input type="hidden" name="bme_restore_id" value="<?php echo (int) $row->id; ?>" />
										<input type="hidden" name="bme_redirect" value="<?php echo esc_url( add_query_arg( array_merge( $base_args, array( 'paged' => $paged ) ), admin_url( 'admin.php' ) ) ); ?>" />
										<?php submit_button( __( 'Restore', 'bulk-meta-editor' ), 'small', '', false ); ?>
									</form>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php else : ?>
						<tr><td colspan="7"><?php esc_html_e( 'Belum ada history yang cocok.', 'bulk-meta-editor' ); ?></td></tr>
					<?php endif; ?>
				</tbody>
			</table>

			<?php
			$total_pages = (int) ceil( $result['total'] / $result['per_page'] );
			if ( $total_pages > 1 ) {
				$base = add_query_arg(
					array_merge( $base_args, array( 'paged' => '%#%' ) ),
					admin_url( 'admin.php' )
				);

				echo '<div class="tablenav"><div class="tablenav-pages">';
				echo paginate_links(
					array(
						'base'      => $base,
						'format'    => '',
						'current'   => $result['page'],
						'total'     => $total_pages,
						'prev_text' => '&laquo;',
						'next_text' => '&raquo;',
					)
				);
				echo '</div></div>';
			}
			?>
		</div>
		<?php
	}
}
