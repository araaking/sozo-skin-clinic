<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BME_Bulk_Editor {

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
		add_action( 'admin_init', array( __CLASS__, 'handle_post_save' ) );
		add_action( 'wp_ajax_bme_save', array( __CLASS__, 'ajax_save' ) );
	}

	public static function menu() {
		$capability = BME_Settings::capability();

		add_menu_page(
			__( 'Bulk Meta Editor', 'bulk-meta-editor' ),
			__( 'Bulk Meta Editor', 'bulk-meta-editor' ),
			$capability,
			'bulk-meta-editor',
			array( __CLASS__, 'render' ),
			'dashicons-edit-page',
			81
		);

		add_submenu_page(
			'bulk-meta-editor',
			__( 'Editor', 'bulk-meta-editor' ),
			__( 'Editor', 'bulk-meta-editor' ),
			$capability,
			'bulk-meta-editor',
			array( __CLASS__, 'render' )
		);
	}

	public static function assets( $hook ) {
		if ( strpos( $hook, 'bulk-meta-editor' ) === false ) {
			return;
		}

		$provider = BME_Provider::instance();

		wp_enqueue_style( 'bme-admin', BME_URL . 'assets/admin.css', array(), BME_VERSION );
		wp_enqueue_script( 'bme-admin', BME_URL . 'assets/admin.js', array(), BME_VERSION, true );

		wp_localize_script(
			'bme-admin',
			'bmeAdmin',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'bme_ajax' ),
				'fields'  => $provider ? $provider->field_keys() : array(),
				'strings' => array(
					'saving'            => __( 'Menyimpan…', 'bulk-meta-editor' ),
					'saved'             => __( 'Tersimpan', 'bulk-meta-editor' ),
					'unchanged'         => __( 'Tidak berubah', 'bulk-meta-editor' ),
					'conflict'          => __( 'Konflik: nilai di server sudah berbeda. Baris dilewati.', 'bulk-meta-editor' ),
					'error'             => __( 'Gagal menyimpan.', 'bulk-meta-editor' ),
					'unsaved'           => __( 'Ada perubahan yang belum disimpan.', 'bulk-meta-editor' ),
					'confirmRestore'    => __( 'Kembalikan nilai sebelum perubahan ini?', 'bulk-meta-editor' ),
					'searchPlaceholder' => __( 'Cari halaman…', 'bulk-meta-editor' ),
					'noResults'         => __( 'Tidak ada hasil.', 'bulk-meta-editor' ),
					'importRunning'     => __( 'Menerapkan…', 'bulk-meta-editor' ),
					'importDone'        => __( 'Import selesai. Halaman dimuat ulang…', 'bulk-meta-editor' ),
					'importError'       => __( 'Import gagal.', 'bulk-meta-editor' ),
				),
			)
		);
	}

	/**
	 * Fallback non-JS: submit form biasa, lalu redirect PRG.
	 */
	public static function handle_post_save() {
		if ( empty( $_POST['bme_bulk_nonce'] ) ) {
			return;
		}

		if ( ! BME_Settings::can_access() ) {
			wp_die( esc_html__( 'Kamu tidak punya akses.', 'bulk-meta-editor' ) );
		}

		check_admin_referer( 'bme_bulk_save', 'bme_bulk_nonce' );

		$rows      = isset( $_POST['bme_rows'] ) && is_array( $_POST['bme_rows'] ) ? wp_unslash( $_POST['bme_rows'] ) : array();
		$originals = isset( $_POST['bme_orig'] ) && is_array( $_POST['bme_orig'] ) ? wp_unslash( $_POST['bme_orig'] ) : array();

		$result = self::save_rows( $rows, $originals, 'bulk' );

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'    => 'bulk-meta-editor',
					'saved'   => (int) $result['saved'],
					'skipped' => (int) $result['conflicts'],
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	public static function ajax_save() {
		check_ajax_referer( 'bme_ajax', 'nonce' );

		if ( ! BME_Settings::can_access() ) {
			wp_send_json_error( array( 'message' => __( 'Kamu tidak punya akses.', 'bulk-meta-editor' ) ), 403 );
		}

		$rows      = isset( $_POST['rows'] ) && is_array( $_POST['rows'] ) ? wp_unslash( $_POST['rows'] ) : array();
		$originals = isset( $_POST['originals'] ) && is_array( $_POST['originals'] ) ? wp_unslash( $_POST['originals'] ) : array();

		wp_send_json_success( self::save_rows( $rows, $originals, 'bulk' ) );
	}

	/**
	 * Simpan sekumpulan baris. Dipakai AJAX, fallback POST, dan (via BME_CSV) tidak.
	 *
	 * @return array{results: array, saved: int, unchanged: int, conflicts: int, errors: int}
	 */
	public static function save_rows( $rows, $originals, $source ) {
		$provider = BME_Provider::instance();

		$out = array(
			'results'   => array(),
			'saved'     => 0,
			'unchanged' => 0,
			'conflicts' => 0,
			'errors'    => 0,
		);

		if ( ! $provider ) {
			return $out;
		}

		foreach ( (array) $rows as $post_id => $fields ) {
			$post_id = (int) $post_id;
			if ( $post_id <= 0 || ! is_array( $fields ) ) {
				continue;
			}

			if ( ! BME_Settings::is_supported_post( $post_id ) || ! BME_Settings::can_edit_post( $post_id ) ) {
				$out['errors']++;
				continue;
			}

			foreach ( $fields as $field => $raw ) {
				$meta_key = $provider->meta_key( $field );
				if ( '' === $meta_key || ! is_scalar( $raw ) ) {
					continue;
				}

				$new = ( 'desc' === $field )
					? sanitize_textarea_field( (string) $raw )
					: sanitize_text_field( (string) $raw );

				$current  = $provider->get( $post_id, $field );
				$original = isset( $originals[ $post_id ][ $field ] ) && is_scalar( $originals[ $post_id ][ $field ] )
					? (string) $originals[ $post_id ][ $field ]
					: $current;

				if ( $current !== $original ) {
					$out['conflicts']++;
					$out['results'][ $post_id ][ $field ] = array(
						'status'  => 'conflict',
						'current' => $current,
					);
					continue;
				}

				if ( $new === $current ) {
					$out['unchanged']++;
					$out['results'][ $post_id ][ $field ] = array(
						'status'  => 'unchanged',
						'current' => $current,
					);
					continue;
				}

				BME_History::pause();
				$provider->set( $post_id, $field, $new );
				BME_History::resume();

				BME_DB::log( $post_id, $meta_key, $current, $new, $source );

				$out['saved']++;
				$out['results'][ $post_id ][ $field ] = array(
					'status'  => 'saved',
					'current' => $new,
				);
			}
		}

		return $out;
	}

	public static function render() {
		if ( ! BME_Settings::can_access() ) {
			wp_die( esc_html__( 'Kamu tidak punya akses ke halaman ini.', 'bulk-meta-editor' ) );
		}

		$provider = BME_Provider::instance();

		if ( ! $provider ) {
			echo '<div class="wrap bme-wrap"><h1>' . esc_html__( 'Bulk Meta Editor', 'bulk-meta-editor' ) . '</h1>';
			echo '<div class="notice notice-error"><p>' . esc_html__( 'Tidak ada SEO plugin yang didukung. Saat ini plugin hanya mendukung Yoast SEO.', 'bulk-meta-editor' ) . '</p></div></div>';
			return;
		}

		$types      = BME_Settings::supported_post_types();
		$search     = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
		$post_type  = isset( $_GET['pt'] ) ? sanitize_key( wp_unslash( $_GET['pt'] ) ) : $types[0];
		$status     = isset( $_GET['status'] ) ? sanitize_key( wp_unslash( $_GET['status'] ) ) : '';
		$empty_only = ! empty( $_GET['empty'] );
		$paged      = isset( $_GET['paged'] ) ? max( 1, (int) $_GET['paged'] ) : 1;

		if ( ! in_array( $post_type, $types, true ) ) {
			$post_type = $types[0];
		}

		$statuses        = array( 'publish', 'draft', 'pending', 'private', 'future' );
		$active_statuses = in_array( $status, $statuses, true ) ? array( $status ) : $statuses;

		$title_key = $provider->meta_key( 'title' );
		$desc_key  = $provider->meta_key( 'desc' );

		$query_args = array(
			'post_type'           => $post_type,
			'post_status'         => $active_statuses,
			'posts_per_page'      => BME_Settings::per_page(),
			'paged'               => $paged,
			'orderby'             => 'modified',
			'order'               => 'DESC',
			'ignore_sticky_posts' => true,
		);

		if ( '' !== $search ) {
			$query_args['s'] = $search;
		}

		if ( $empty_only ) {
			$query_args['meta_query'] = array(
				'relation' => 'OR',
				array(
					'key'     => $title_key,
					'compare' => 'NOT EXISTS',
				),
				array(
					'key'   => $title_key,
					'value' => '',
				),
				array(
					'key'     => $desc_key,
					'compare' => 'NOT EXISTS',
				),
				array(
					'key'   => $desc_key,
					'value' => '',
				),
			);
		}

		$query      = new WP_Query( $query_args );
		$page_ids   = wp_list_pluck( $query->posts, 'ID' );
		$last_edits = BME_DB::last_edits( $page_ids );

		$saved   = isset( $_GET['saved'] ) ? (int) $_GET['saved'] : -1;
		$skipped = isset( $_GET['skipped'] ) ? (int) $_GET['skipped'] : 0;

		$base_args = array(
			'page'   => 'bulk-meta-editor',
			's'      => $search,
			'pt'     => $post_type,
			'status' => $status,
			'empty'  => $empty_only ? 1 : 0,
		);
		?>
		<div class="wrap bme-wrap">
			<h1><?php esc_html_e( 'Bulk Meta Editor', 'bulk-meta-editor' ); ?></h1>
			<p class="description">
				<?php
				printf(
					/* translators: %s = nama provider SEO */
					esc_html__( 'Edit meta title dan meta description %s. Setiap perubahan tercatat di History beserta user dan waktunya.', 'bulk-meta-editor' ),
					esc_html( $provider->get_label() )
				);
				?>
			</p>

			<?php if ( $saved >= 0 ) : ?>
				<div class="notice notice-success is-dismissible">
					<p>
						<?php
						printf(
							/* translators: 1: jumlah field tersimpan, 2: jumlah baris dilewati */
							esc_html__( '%1$d field disimpan. %2$d baris dilewati karena konflik.', 'bulk-meta-editor' ),
							(int) $saved,
							(int) $skipped
						);
						?>
					</p>
				</div>
			<?php endif; ?>

			<form method="get" class="bme-toolbar">
				<input type="hidden" name="page" value="bulk-meta-editor" />

				<?php if ( count( $types ) > 1 ) : ?>
					<select name="pt">
						<?php foreach ( $types as $type ) : ?>
							<option value="<?php echo esc_attr( $type ); ?>" <?php selected( $post_type, $type ); ?>>
								<?php echo esc_html( $type ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				<?php endif; ?>

				<select name="status">
					<option value=""><?php esc_html_e( 'Semua status', 'bulk-meta-editor' ); ?></option>
					<?php foreach ( $statuses as $status_option ) : ?>
						<option value="<?php echo esc_attr( $status_option ); ?>" <?php selected( $status, $status_option ); ?>>
							<?php echo esc_html( $status_option ); ?>
						</option>
					<?php endforeach; ?>
				</select>

				<label class="bme-check">
					<input type="checkbox" name="empty" value="1" <?php checked( $empty_only ); ?> />
					<?php esc_html_e( 'Hanya yang meta-nya kosong', 'bulk-meta-editor' ); ?>
				</label>

				<input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Cari…', 'bulk-meta-editor' ); ?>" />

				<?php submit_button( __( 'Filter', 'bulk-meta-editor' ), 'secondary', '', false ); ?>

				<?php if ( '' !== $search || '' !== $status || $empty_only ) : ?>
					<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=bulk-meta-editor&pt=' . rawurlencode( $post_type ) ) ); ?>">
						<?php esc_html_e( 'Reset', 'bulk-meta-editor' ); ?>
					</a>
				<?php endif; ?>

				<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=bulk-meta-editor-csv' ) ); ?>">
					<?php esc_html_e( 'Import / Export CSV', 'bulk-meta-editor' ); ?>
				</a>
				<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=bulk-meta-editor-history' ) ); ?>">
					<?php esc_html_e( 'Lihat History', 'bulk-meta-editor' ); ?>
				</a>
			</form>

			<p class="bme-summary">
				<?php
				printf(
					/* translators: %d = jumlah post */
					esc_html__( '%d post ditemukan.', 'bulk-meta-editor' ),
					(int) $query->found_posts
				);
				?>
			</p>

			<form method="post" id="bme-form">
				<?php wp_nonce_field( 'bme_bulk_save', 'bme_bulk_nonce' ); ?>

				<table class="widefat striped bme-table">
					<thead>
						<tr>
							<th class="bme-col-post"><?php esc_html_e( 'Post', 'bulk-meta-editor' ); ?></th>
							<th class="bme-col-title"><?php echo esc_html( $provider->field_label( $title_key ) ); ?></th>
							<th class="bme-col-desc"><?php echo esc_html( $provider->field_label( $desc_key ) ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php if ( $query->have_posts() ) : ?>
							<?php foreach ( $query->posts as $post ) : ?>
								<?php
								$post_id    = (int) $post->ID;
								$title      = $provider->get( $post_id, 'title' );
								$desc       = $provider->get( $post_id, 'desc' );
								$last       = isset( $last_edits[ $post_id ] ) ? $last_edits[ $post_id ] : null;
								$last_user  = $last ? get_userdata( (int) $last->user_id ) : null;
								$last_label = $last
									? sprintf(
										'%s — %s (%s)',
										mysql2date( 'd M Y H:i', $last->created_at ),
										$last_user ? $last_user->display_name : __( 'user dihapus', 'bulk-meta-editor' ),
										BME_History::source_label( $last->source )
									)
									: __( 'Belum ada catatan perubahan', 'bulk-meta-editor' );
								?>
								<tr data-post-id="<?php echo esc_attr( $post_id ); ?>" class="bme-row">
									<td>
										<strong>
											<a href="<?php echo esc_url( get_edit_post_link( $post_id ) ); ?>">
												<?php echo esc_html( get_the_title( $post_id ) ? get_the_title( $post_id ) : __( '(tanpa judul)', 'bulk-meta-editor' ) ); ?>
											</a>
										</strong>
										<div class="bme-sub">#<?php echo (int) $post_id; ?> · <?php echo esc_html( $post->post_status ); ?></div>
										<div class="bme-sub bme-last-edit"><?php echo esc_html( $last_label ); ?></div>
										<div class="bme-sub">
											<a href="<?php echo esc_url( admin_url( 'admin.php?page=bulk-meta-editor-history&post_id=' . $post_id ) ); ?>">
												<?php esc_html_e( 'History', 'bulk-meta-editor' ); ?>
											</a>
										</div>
									</td>
									<td>
										<input type="text"
											class="widefat bme-input bme-count-title"
											name="bme_rows[<?php echo (int) $post_id; ?>][title]"
											data-post-id="<?php echo esc_attr( $post_id ); ?>"
											data-bme-field="title"
											value="<?php echo esc_attr( $title ); ?>"
											placeholder="<?php echo esc_attr( get_the_title( $post_id ) ); ?>"
											maxlength="255" />
										<input type="hidden"
											name="bme_orig[<?php echo (int) $post_id; ?>][title]"
											data-bme-orig="1"
											data-post-id="<?php echo esc_attr( $post_id ); ?>"
											data-bme-field="title"
											value="<?php echo esc_attr( $title ); ?>" />
										<div class="bme-counter" data-for="title"><?php echo (int) mb_strlen( $title ); ?> / 60</div>
										<div class="bme-field-msg" data-post-id="<?php echo esc_attr( $post_id ); ?>" data-bme-field="title"></div>
									</td>
									<td>
										<textarea
											class="widefat bme-input bme-count-desc"
											name="bme_rows[<?php echo (int) $post_id; ?>][desc]"
											data-post-id="<?php echo esc_attr( $post_id ); ?>"
											data-bme-field="desc"
											rows="3"
											maxlength="320"><?php echo esc_textarea( $desc ); ?></textarea>
										<input type="hidden"
											name="bme_orig[<?php echo (int) $post_id; ?>][desc]"
											data-bme-orig="1"
											data-post-id="<?php echo esc_attr( $post_id ); ?>"
											data-bme-field="desc"
											value="<?php echo esc_attr( $desc ); ?>" />
										<div class="bme-counter" data-for="desc"><?php echo (int) mb_strlen( $desc ); ?> / 160</div>
										<div class="bme-field-msg" data-post-id="<?php echo esc_attr( $post_id ); ?>" data-bme-field="desc"></div>
									</td>
								</tr>
							<?php endforeach; ?>
						<?php else : ?>
							<tr>
								<td colspan="3"><?php esc_html_e( 'Tidak ada post yang cocok dengan filter.', 'bulk-meta-editor' ); ?></td>
							</tr>
						<?php endif; ?>
					</tbody>
				</table>

				<div class="bme-actions">
					<?php submit_button( __( 'Simpan Semua Perubahan', 'bulk-meta-editor' ), 'primary', 'bme-save-button', false ); ?>
					<span class="bme-save-status" id="bme-save-status" aria-live="polite"></span>
					<span class="description"><?php esc_html_e( 'Hanya field yang berubah yang disimpan dan dicatat.', 'bulk-meta-editor' ); ?></span>
				</div>
			</form>

			<?php
			$total_pages = (int) $query->max_num_pages;
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
						'current'   => $paged,
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
