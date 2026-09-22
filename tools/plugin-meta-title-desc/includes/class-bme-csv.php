<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BME_CSV {

	const IMPORT_DIR = 'bme-imports';
	const MAX_ROWS   = 5000;
	const BATCH_SIZE = 50;
	const PREVIEW    = 200;

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_post_bme_export_csv', array( __CLASS__, 'handle_export' ) );
		add_action( 'admin_post_bme_import_upload', array( __CLASS__, 'handle_upload' ) );
		add_action( 'wp_ajax_bme_import_apply', array( __CLASS__, 'ajax_apply' ) );
	}

	public static function menu() {
		add_submenu_page(
			'bulk-meta-editor',
			__( 'Import / Export CSV', 'bulk-meta-editor' ),
			__( 'Import / Export', 'bulk-meta-editor' ),
			BME_Settings::capability(),
			'bulk-meta-editor-csv',
			array( __CLASS__, 'render' )
		);
	}

	/* ---------------------------------------------------------------------
	 * Export
	 * ------------------------------------------------------------------ */

	public static function handle_export() {
		if ( ! BME_Settings::can_access() ) {
			wp_die( esc_html__( 'Kamu tidak punya akses.', 'bulk-meta-editor' ) );
		}

		check_admin_referer( 'bme_export_csv', 'bme_export_nonce' );

		$provider = BME_Provider::instance();
		if ( ! $provider ) {
			wp_die( esc_html__( 'Tidak ada SEO plugin yang didukung.', 'bulk-meta-editor' ) );
		}

		$types = BME_Settings::supported_post_types();
		$pt    = isset( $_GET['pt'] ) ? sanitize_key( wp_unslash( $_GET['pt'] ) ) : $types[0];
		if ( ! in_array( $pt, $types, true ) ) {
			$pt = $types[0];
		}

		$empty_only = ! empty( $_GET['empty'] );

		$args = array(
			'post_type'      => $pt,
			'post_status'    => array( 'publish', 'draft', 'pending', 'private', 'future' ),
			'posts_per_page' => 200,
			'paged'          => 1,
			'orderby'        => 'ID',
			'order'          => 'ASC',
			'fields'         => 'ids',
			'no_found_rows'  => true,
		);

		if ( $empty_only ) {
			$args['meta_query'] = self::empty_meta_query( $provider );
		}

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="bulk-meta-editor-' . gmdate( 'Ymd-His' ) . '.csv"' );

		$out = fopen( 'php://output', 'w' );
		fwrite( $out, "\xEF\xBB\xBF" );

		fputcsv(
			$out,
			array( 'post_id', 'post_type', 'url', 'post_title', 'seo_title', 'meta_description', 'seo_title_before', 'meta_description_before', 'last_edited_at', 'last_edited_by' ),
			',',
			'"',
			''
		);

		$title_key = $provider->meta_key( 'title' );
		$desc_key  = $provider->meta_key( 'desc' );

		do {
			$ids = get_posts( $args );

			if ( empty( $ids ) ) {
				break;
			}

			$edits       = BME_DB::last_edits( $ids );
			$field_edits = BME_DB::last_field_edits( $ids );

			foreach ( $ids as $id ) {
				$edit = isset( $edits[ $id ] ) ? $edits[ $id ] : null;
				$user = $edit ? get_userdata( (int) $edit->user_id ) : null;

				$title_before = isset( $field_edits[ $id ][ $title_key ] ) ? (string) $field_edits[ $id ][ $title_key ]->old_value : '';
				$desc_before  = isset( $field_edits[ $id ][ $desc_key ] ) ? (string) $field_edits[ $id ][ $desc_key ]->old_value : '';

				fputcsv(
					$out,
					array(
						$id,
						get_post_type( $id ),
						get_permalink( $id ),
						get_the_title( $id ),
						self::protect( $provider->get( $id, 'title' ) ),
						self::protect( $provider->get( $id, 'desc' ) ),
						self::protect( $title_before ),
						self::protect( $desc_before ),
						$edit ? $edit->created_at : '',
						$user ? $user->display_name : '',
					),
					',',
					'"',
					''
				);
			}

			$args['paged']++;
		} while ( count( $ids ) === 200 );

		fclose( $out );
		exit;
	}

	/* ---------------------------------------------------------------------
	 * Import
	 * ------------------------------------------------------------------ */

	public static function handle_upload() {
		if ( ! BME_Settings::can_access() ) {
			wp_die( esc_html__( 'Kamu tidak punya akses.', 'bulk-meta-editor' ) );
		}

		check_admin_referer( 'bme_import_upload', 'bme_import_nonce' );

		$tmp = isset( $_FILES['bme_file']['tmp_name'] ) && is_string( $_FILES['bme_file']['tmp_name'] )
			? $_FILES['bme_file']['tmp_name']
			: '';

		if ( '' === $tmp || ! is_uploaded_file( $tmp ) ) {
			self::redirect_notice( 'no_file' );
		}

		if ( ! empty( $_FILES['bme_file']['error'] ) ) {
			self::redirect_notice( 'upload_failed' );
		}

		$original = isset( $_FILES['bme_file']['name'] ) && is_string( $_FILES['bme_file']['name'] )
			? sanitize_file_name( wp_unslash( $_FILES['bme_file']['name'] ) )
			: '';
		$ext      = strtolower( pathinfo( $original, PATHINFO_EXTENSION ) );

		if ( ! in_array( $ext, array( 'csv', 'txt' ), true ) ) {
			self::redirect_notice( 'invalid_file' );
		}

		$uploads = wp_upload_dir();
		$dir     = trailingslashit( $uploads['basedir'] ) . self::IMPORT_DIR;

		if ( ! wp_mkdir_p( $dir ) ) {
			self::redirect_notice( 'upload_failed' );
		}

		self::cleanup_old_files( $dir );

		$filename = 'import-' . wp_generate_password( 12, false, false ) . '.csv';
		$dest     = trailingslashit( $dir ) . $filename;

		if ( ! move_uploaded_file( $tmp, $dest ) ) {
			self::redirect_notice( 'upload_failed' );
		}

		$parsed = self::parse_all( $dest );

		if ( is_wp_error( $parsed ) ) {
			@unlink( $dest );
			self::redirect_notice( 'invalid_csv' );
		}

		if ( count( $parsed['rows'] ) > self::MAX_ROWS ) {
			@unlink( $dest );
			self::redirect_notice( 'too_many' );
		}

		$token = wp_generate_password( 20, false, false );

		set_transient(
			'bme_import_' . $token,
			array(
				'user_id'     => get_current_user_id(),
				'file'        => $filename,
				'rows'        => count( $parsed['rows'] ),
				'apply_empty' => ! empty( $_POST['bme_apply_empty'] ) ? 1 : 0,
				'created'     => time(),
			),
			30 * MINUTE_IN_SECONDS
		);

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'         => 'bulk-meta-editor-csv',
					'import_token' => $token,
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	public static function ajax_apply() {
		check_ajax_referer( 'bme_ajax', 'nonce' );

		if ( ! BME_Settings::can_access() ) {
			wp_send_json_error( array( 'message' => __( 'Kamu tidak punya akses.', 'bulk-meta-editor' ) ), 403 );
		}

		$token  = isset( $_POST['token'] ) ? sanitize_text_field( wp_unslash( $_POST['token'] ) ) : '';
		$offset = isset( $_POST['offset'] ) ? max( 0, (int) $_POST['offset'] ) : 0;

		$data = get_transient( 'bme_import_' . $token );

		if ( ! $data || (int) $data['user_id'] !== get_current_user_id() ) {
			wp_send_json_error( array( 'message' => __( 'Sesi import kedaluwarsa. Upload ulang file CSV.', 'bulk-meta-editor' ) ) );
		}

		$path = self::import_path( $data['file'] );
		if ( '' === $path ) {
			delete_transient( 'bme_import_' . $token );
			wp_send_json_error( array( 'message' => __( 'File import tidak ditemukan. Upload ulang.', 'bulk-meta-editor' ) ) );
		}

		$parsed = self::parse_all( $path );
		if ( is_wp_error( $parsed ) ) {
			wp_send_json_error( array( 'message' => __( 'File CSV tidak valid.', 'bulk-meta-editor' ) ) );
		}

		$rows  = $parsed['rows'];
		$batch = array_slice( $rows, $offset, self::BATCH_SIZE );

		$counts = array(
			'applied'   => 0,
			'unchanged' => 0,
			'errors'    => 0,
		);

		$provider = BME_Provider::instance();
		foreach ( $batch as $row ) {
			$status = self::apply_row( $row, $parsed, $provider, ! empty( $data['apply_empty'] ) );
			if ( isset( $counts[ $status ] ) ) {
				$counts[ $status ]++;
			}
		}

		$next = $offset + count( $batch );
		$done = $next >= count( $rows );

		if ( $done ) {
			@unlink( $path );
			delete_transient( 'bme_import_' . $token );
		}

		wp_send_json_success(
			array(
				'counts' => $counts,
				'next'   => $next,
				'total'  => count( $rows ),
				'done'   => $done,
			)
		);
	}

	private static function apply_row( $row, $parsed, $provider, $apply_empty ) {
		$analysis = self::analyze_row( $row, $parsed, $provider, $apply_empty );

		if ( 'error' === $analysis['status'] ) {
			return 'errors';
		}
		if ( 'unchanged' === $analysis['status'] ) {
			return 'unchanged';
		}

		$post_id = (int) $analysis['post_id'];

		foreach ( $analysis['changes'] as $field => $change ) {
			BME_History::pause();
			$provider->set( $post_id, $field, $change['new'] );
			BME_History::resume();

			BME_DB::log( $post_id, $provider->meta_key( $field ), $change['old'], $change['new'], 'csv' );
		}

		return 'applied';
	}

	private static function analyze_row( $row, $parsed, $provider, $apply_empty ) {
		$post_id = 0;

		if ( ! empty( $row['post_id'] ) ) {
			$post = get_post( (int) $row['post_id'] );
			if ( $post ) {
				$post_id = (int) $post->ID;
			}
		}

		if ( ! $post_id && ! empty( $row['url'] ) ) {
			$post_id = (int) url_to_postid( $row['url'] );
		}

		if ( ! $post_id || ! BME_Settings::is_supported_post( $post_id ) ) {
			return array(
				'status'  => 'error',
				'post_id' => $post_id,
				'message' => __( 'Post tidak ditemukan atau post type tidak didukung.', 'bulk-meta-editor' ),
			);
		}

		if ( ! BME_Settings::can_edit_post( $post_id ) ) {
			return array(
				'status'  => 'error',
				'post_id' => $post_id,
				'message' => __( 'Tidak punya akses edit.', 'bulk-meta-editor' ),
			);
		}

		$changes = array();

		foreach ( array( 'title', 'desc' ) as $field ) {
			if ( empty( $parsed[ 'has_' . $field ] ) || null === $row[ $field ] ) {
				continue;
			}

			$value = trim( (string) $row[ $field ] );

			if ( '' === $value && ! $apply_empty ) {
				continue;
			}

			$current = $provider->get( $post_id, $field );

			if ( $value !== $current ) {
				$changes[ $field ] = array(
					'old' => $current,
					'new' => $value,
				);
			}
		}

		if ( empty( $changes ) ) {
			return array(
				'status'  => 'unchanged',
				'post_id' => $post_id,
			);
		}

		$is_new = ( '' === $provider->get( $post_id, 'title' ) && '' === $provider->get( $post_id, 'desc' ) );

		return array(
			'status'  => $is_new ? 'new' : 'update',
			'post_id' => $post_id,
			'changes' => $changes,
		);
	}

	/* ---------------------------------------------------------------------
	 * Parser
	 * ------------------------------------------------------------------ */

	private static function parse_all( $path ) {
		$handle = fopen( $path, 'r' );

		if ( ! $handle ) {
			return new WP_Error( 'bme_open', __( 'File tidak bisa dibaca.', 'bulk-meta-editor' ) );
		}

		$first = fgets( $handle );

		if ( false === $first || '' === trim( $first ) ) {
			fclose( $handle );
			return new WP_Error( 'bme_empty', __( 'File CSV kosong.', 'bulk-meta-editor' ) );
		}

		$delimiter = ( substr_count( $first, ';' ) > substr_count( $first, ',' ) ) ? ';' : ',';

		rewind( $handle );
		$header = fgetcsv( $handle, 100000, $delimiter, '"', '' );
		$header = array_map(
			function ( $column ) {
				$column = preg_replace( '/^\xEF\xBB\xBF/', '', (string) $column );
				return strtolower( trim( str_replace( array( ' ', '-' ), '_', $column ) ) );
			},
			is_array( $header ) ? $header : array()
		);

		$index = array();
		foreach ( $header as $position => $name ) {
			if ( '' !== $name && ! isset( $index[ $name ] ) ) {
				$index[ $name ] = $position;
			}
		}

		$aliases = array(
			'post_id' => array( 'post_id', 'id', 'post' ),
			'url'     => array( 'url', 'permalink', 'link' ),
			'title'   => array( 'seo_title', 'meta_title', 'title', 'yoast_title' ),
			'desc'    => array( 'meta_description', 'meta_desc', 'metadesc', 'description', 'yoast_metadesc' ),
		);

		$columns = array();
		foreach ( $aliases as $field => $names ) {
			foreach ( $names as $name ) {
				if ( isset( $index[ $name ] ) ) {
					$columns[ $field ] = $index[ $name ];
					break;
				}
			}
		}

		if ( ! isset( $columns['post_id'] ) && ! isset( $columns['url'] ) ) {
			fclose( $handle );
			return new WP_Error( 'bme_no_id', __( 'CSV harus punya kolom post_id atau url.', 'bulk-meta-editor' ) );
		}

		if ( ! isset( $columns['title'] ) && ! isset( $columns['desc'] ) ) {
			fclose( $handle );
			return new WP_Error( 'bme_no_meta', __( 'CSV harus punya kolom seo_title atau meta_description.', 'bulk-meta-editor' ) );
		}

		$rows = array();

		while ( ( $data = fgetcsv( $handle, 100000, $delimiter, '"', '' ) ) !== false ) {
			if ( ! is_array( $data ) || array( null ) === $data ) {
				continue;
			}

			$row = array(
				'post_id' => 0,
				'url'     => '',
				'title'   => null,
				'desc'    => null,
			);

			if ( isset( $columns['post_id'] ) ) {
				$row['post_id'] = (int) trim( (string) ( isset( $data[ $columns['post_id'] ] ) ? $data[ $columns['post_id'] ] : '' ) );
			}
			if ( isset( $columns['url'] ) ) {
				$row['url'] = trim( (string) ( isset( $data[ $columns['url'] ] ) ? $data[ $columns['url'] ] : '' ) );
			}
			if ( isset( $columns['title'] ) ) {
				$row['title'] = self::unprotect( (string) ( isset( $data[ $columns['title'] ] ) ? $data[ $columns['title'] ] : '' ) );
			}
			if ( isset( $columns['desc'] ) ) {
				$row['desc'] = self::unprotect( (string) ( isset( $data[ $columns['desc'] ] ) ? $data[ $columns['desc'] ] : '' ) );
			}

			if ( 0 === $row['post_id'] && '' === $row['url'] && null === $row['title'] && null === $row['desc'] ) {
				continue;
			}

			$rows[] = $row;
		}

		fclose( $handle );

		return array(
			'rows'      => $rows,
			'has_title' => isset( $columns['title'] ),
			'has_desc'  => isset( $columns['desc'] ),
		);
	}

	/* ---------------------------------------------------------------------
	 * Helpers
	 * ------------------------------------------------------------------ */

	private static function empty_meta_query( $provider ) {
		return array(
			'relation' => 'OR',
			array(
				'key'     => $provider->meta_key( 'title' ),
				'compare' => 'NOT EXISTS',
			),
			array(
				'key'   => $provider->meta_key( 'title' ),
				'value' => '',
			),
			array(
				'key'     => $provider->meta_key( 'desc' ),
				'compare' => 'NOT EXISTS',
			),
			array(
				'key'   => $provider->meta_key( 'desc' ),
				'value' => '',
			),
		);
	}

	/**
	 * Cegah CSV injection di Excel: cell yang diawali = + - @ diberi apostrof.
	 */
	private static function protect( $value ) {
		$value = (string) $value;
		if ( '' !== $value && in_array( $value[0], array( '=', '+', '-', '@' ), true ) ) {
			return "'" . $value;
		}
		return $value;
	}

	private static function unprotect( $value ) {
		$value = (string) $value;
		if ( strlen( $value ) > 1 && "'" === $value[0] && in_array( $value[1], array( '=', '+', '-', '@' ), true ) ) {
			return substr( $value, 1 );
		}
		return $value;
	}

	private static function import_path( $filename ) {
		$filename = basename( (string) $filename );

		if ( ! preg_match( '/^import-[A-Za-z0-9]+\.csv$/', $filename ) ) {
			return '';
		}

		$uploads = wp_upload_dir();
		$path    = trailingslashit( $uploads['basedir'] ) . self::IMPORT_DIR . '/' . $filename;

		return file_exists( $path ) ? $path : '';
	}

	/**
	 * Hapus file import yang menggantung lebih dari sehari.
	 */
	private static function cleanup_old_files( $dir ) {
		$files = glob( trailingslashit( $dir ) . 'import-*.csv' );

		if ( ! is_array( $files ) ) {
			return;
		}

		foreach ( $files as $file ) {
			if ( filemtime( $file ) < time() - DAY_IN_SECONDS ) {
				@unlink( $file );
			}
		}
	}

	private static function redirect_notice( $code ) {
		wp_safe_redirect(
			add_query_arg(
				array(
					'page'       => 'bulk-meta-editor-csv',
					'bme_notice' => $code,
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	private static function notice_message( $code ) {
		$messages = array(
			'no_file'       => __( 'Tidak ada file yang diupload.', 'bulk-meta-editor' ),
			'upload_failed' => __( 'Upload gagal. Coba lagi.', 'bulk-meta-editor' ),
			'invalid_file'  => __( 'Format file harus .csv atau .txt.', 'bulk-meta-editor' ),
			'invalid_csv'   => __( 'CSV tidak valid: butuh kolom post_id atau url, plus seo_title atau meta_description.', 'bulk-meta-editor' ),
			'too_many'      => sprintf(
				/* translators: %d = batas baris */
				__( 'File terlalu besar. Maksimal %d baris per import.', 'bulk-meta-editor' ),
				self::MAX_ROWS
			),
			'done'          => __( 'Import selesai.', 'bulk-meta-editor' ),
		);

		return isset( $messages[ $code ] ) ? $messages[ $code ] : '';
	}

	/* ---------------------------------------------------------------------
	 * Render
	 * ------------------------------------------------------------------ */

	public static function render() {
		if ( ! BME_Settings::can_access() ) {
			wp_die( esc_html__( 'Kamu tidak punya akses ke halaman ini.', 'bulk-meta-editor' ) );
		}

		$provider = BME_Provider::instance();
		if ( ! $provider ) {
			echo '<div class="wrap bme-wrap"><h1>' . esc_html__( 'Import / Export CSV', 'bulk-meta-editor' ) . '</h1>';
			echo '<div class="notice notice-error"><p>' . esc_html__( 'Tidak ada SEO plugin yang didukung.', 'bulk-meta-editor' ) . '</p></div></div>';
			return;
		}

		$notice = isset( $_GET['bme_notice'] ) ? sanitize_key( wp_unslash( $_GET['bme_notice'] ) ) : '';
		$token  = isset( $_GET['import_token'] ) ? sanitize_text_field( wp_unslash( $_GET['import_token'] ) ) : '';
		$data   = $token ? get_transient( 'bme_import_' . $token ) : false;

		if ( $data && (int) $data['user_id'] !== get_current_user_id() ) {
			$data = false;
		}

		$types = BME_Settings::supported_post_types();
		?>
		<div class="wrap bme-wrap">
			<h1><?php esc_html_e( 'Import / Export CSV', 'bulk-meta-editor' ); ?></h1>

			<?php if ( '' !== $notice ) : ?>
				<?php $message = self::notice_message( $notice ); ?>
				<?php if ( '' !== $message ) : ?>
					<div class="notice notice-<?php echo 'done' === $notice ? 'success' : 'error'; ?> is-dismissible"><p><?php echo esc_html( $message ); ?></p></div>
				<?php endif; ?>
			<?php endif; ?>

			<?php if ( $data ) : ?>
				<?php self::render_preview( $token, $data, $provider ); ?>
			<?php else : ?>
				<?php if ( $token ) : ?>
					<div class="notice notice-error"><p><?php esc_html_e( 'Sesi import kedaluwarsa. Upload ulang file CSV.', 'bulk-meta-editor' ); ?></p></div>
				<?php endif; ?>

				<div class="bme-card">
					<h2><?php esc_html_e( 'Export CSV', 'bulk-meta-editor' ); ?></h2>
					<p class="description"><?php esc_html_e( 'File berisi post_id, url, judul, meta title, meta description, versi sebelumnya (before), dan info perubahan terakhir. Kolom before dan last_edited_* hanya untuk referensi — diabaikan saat import.', 'bulk-meta-editor' ); ?></p>
					<form method="get" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="bme_export_csv" />
						<?php wp_nonce_field( 'bme_export_csv', 'bme_export_nonce' ); ?>

						<?php if ( count( $types ) > 1 ) : ?>
							<label class="bme-check">
								<?php esc_html_e( 'Post type', 'bulk-meta-editor' ); ?>
								<select name="pt">
									<?php foreach ( $types as $type ) : ?>
										<option value="<?php echo esc_attr( $type ); ?>"><?php echo esc_html( $type ); ?></option>
									<?php endforeach; ?>
								</select>
							</label>
						<?php endif; ?>

						<label class="bme-check">
							<input type="checkbox" name="empty" value="1" />
							<?php esc_html_e( 'Hanya yang meta-nya kosong', 'bulk-meta-editor' ); ?>
						</label>

						<?php submit_button( __( 'Download CSV', 'bulk-meta-editor' ), 'secondary', '', false ); ?>
					</form>
				</div>

				<div class="bme-card">
					<h2><?php esc_html_e( 'Import CSV', 'bulk-meta-editor' ); ?></h2>
					<p class="description">
						<?php esc_html_e( 'Baris dicocokkan lewat post_id, lalu fallback ke url. Setelah upload, akan muncul pratinjau (dry-run) sebelum perubahan diterapkan.', 'bulk-meta-editor' ); ?>
					</p>
					<form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="bme_import_upload" />
						<?php wp_nonce_field( 'bme_import_upload', 'bme_import_nonce' ); ?>

						<label class="bme-check">
							<input type="file" name="bme_file" accept=".csv,.txt" required />
						</label>

						<label class="bme-check">
							<input type="checkbox" name="bme_apply_empty" value="1" />
							<?php esc_html_e( 'Terapkan juga sel kosong sebagai penghapusan meta', 'bulk-meta-editor' ); ?>
						</label>

						<?php submit_button( __( 'Upload & Pratinjau', 'bulk-meta-editor' ), 'secondary', '', false ); ?>
					</form>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	private static function render_preview( $token, $data, $provider ) {
		$path = self::import_path( $data['file'] );

		if ( '' === $path ) {
			echo '<div class="notice notice-error"><p>' . esc_html__( 'File import tidak ditemukan. Upload ulang.', 'bulk-meta-editor' ) . '</p></div>';
			return;
		}

		$parsed = self::parse_all( $path );

		if ( is_wp_error( $parsed ) ) {
			echo '<div class="notice notice-error"><p>' . esc_html( $parsed->get_error_message() ) . '</p></div>';
			return;
		}

		$apply_empty = ! empty( $data['apply_empty'] );
		$counts      = array(
			'new'       => 0,
			'update'    => 0,
			'unchanged' => 0,
			'error'     => 0,
		);
		$analyses    = array();

		foreach ( $parsed['rows'] as $index => $row ) {
			$analysis       = self::analyze_row( $row, $parsed, $provider, $apply_empty );
			$analyses[]     = array(
				'line'     => $index + 2,
				'analysis' => $analysis,
			);
			$counts[ $analysis['status'] ]++;
		}
		?>
		<div class="bme-card">
			<h2><?php esc_html_e( 'Pratinjau Import (dry-run)', 'bulk-meta-editor' ); ?></h2>
			<p>
				<?php
				printf(
					/* translators: 1: total baris, 2: update, 3: baru, 4: sama, 5: error */
					esc_html__( '%1$d baris terbaca — %2$d update, %3$d baru, %4$d tidak berubah, %5$d error.', 'bulk-meta-editor' ),
					count( $parsed['rows'] ),
					(int) $counts['update'],
					(int) $counts['new'],
					(int) $counts['unchanged'],
					(int) $counts['error']
				);
				?>
			</p>

			<label class="bme-check">
				<?php if ( $apply_empty ) : ?>
					<span class="bme-pill bme-pill-warn"><?php esc_html_e( 'Sel kosong diterapkan sebagai penghapusan meta', 'bulk-meta-editor' ); ?></span>
				<?php else : ?>
					<span class="bme-pill"><?php esc_html_e( 'Sel kosong dilewati', 'bulk-meta-editor' ); ?></span>
				<?php endif; ?>
			</label>

			<table class="widefat striped bme-table bme-preview-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Baris', 'bulk-meta-editor' ); ?></th>
						<th><?php esc_html_e( 'Post', 'bulk-meta-editor' ); ?></th>
						<th><?php esc_html_e( 'Status', 'bulk-meta-editor' ); ?></th>
						<th class="bme-col-change"><?php esc_html_e( 'Perubahan', 'bulk-meta-editor' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php $shown = 0; ?>
					<?php foreach ( $analyses as $item ) : ?>
						<?php
						$analysis = $item['analysis'];

						if ( 'unchanged' === $analysis['status'] || 'error' === $analysis['status'] ) {
							continue;
						}
						if ( $shown >= self::PREVIEW ) {
							break;
						}
						$shown++;
						?>
						<tr>
							<td><?php echo (int) $item['line']; ?></td>
							<td>
								<?php if ( ! empty( $analysis['post_id'] ) && get_post( $analysis['post_id'] ) ) : ?>
									<a href="<?php echo esc_url( get_edit_post_link( $analysis['post_id'] ) ); ?>">
										<?php echo esc_html( get_the_title( $analysis['post_id'] ) ); ?>
									</a>
								<?php else : ?>
									—
								<?php endif; ?>
							</td>
							<td>
								<span class="bme-pill <?php echo 'new' === $analysis['status'] ? 'bme-pill-ok' : 'bme-pill-warn'; ?>">
									<?php echo 'new' === $analysis['status'] ? esc_html__( 'Baru', 'bulk-meta-editor' ) : esc_html__( 'Update', 'bulk-meta-editor' ); ?>
								</span>
							</td>
							<td>
								<?php foreach ( $analysis['changes'] as $field => $change ) : ?>
									<div class="bme-change">
										<strong><?php echo esc_html( $provider->field_label( $provider->meta_key( $field ) ) ); ?></strong>
										<div class="bme-old"><?php echo esc_html( mb_strimwidth( (string) $change['old'], 0, 120, '…' ) ); ?></div>
										<div class="bme-new"><?php echo esc_html( mb_strimwidth( (string) $change['new'], 0, 120, '…' ) ); ?></div>
									</div>
								<?php endforeach; ?>
							</td>
						</tr>
					<?php endforeach; ?>
					<?php if ( 0 === $shown ) : ?>
						<tr><td colspan="4"><?php esc_html_e( 'Tidak ada perubahan yang perlu diterapkan.', 'bulk-meta-editor' ); ?></td></tr>
					<?php endif; ?>
				</tbody>
			</table>

			<?php if ( $counts['error'] > 0 ) : ?>
				<p class="description">
					<?php
					printf(
						/* translators: %d = jumlah baris error */
						esc_html__( '%d baris error akan dilewati saat apply.', 'bulk-meta-editor' ),
						(int) $counts['error']
					);
					?>
				</p>
			<?php endif; ?>

			<div class="bme-actions">
				<button type="button" class="button button-primary" id="bme-import-apply"
					data-token="<?php echo esc_attr( $token ); ?>"
					data-total="<?php echo (int) count( $parsed['rows'] ); ?>"
					<?php disabled( $counts['update'] + $counts['new'], 0 ); ?>>
					<?php esc_html_e( 'Terapkan Perubahan', 'bulk-meta-editor' ); ?>
				</button>
				<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=bulk-meta-editor-csv' ) ); ?>"><?php esc_html_e( 'Batal', 'bulk-meta-editor' ); ?></a>
				<span id="bme-import-status" aria-live="polite"></span>
			</div>

			<div class="bme-progress" id="bme-import-progress" hidden>
				<div class="bme-progress-bar" id="bme-import-bar"></div>
			</div>
		</div>
		<?php
	}
}
