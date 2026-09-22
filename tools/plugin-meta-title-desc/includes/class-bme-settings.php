<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BME_Settings {

	const OPTION = 'bme_settings';

	/**
	 * @var array|null
	 */
	private static $cache = null;

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_post_bme_save_settings', array( __CLASS__, 'handle_save' ) );
		add_action( 'admin_post_bme_take_baseline', array( __CLASS__, 'handle_baseline' ) );
	}

	public static function menu() {
		add_submenu_page(
			'bulk-meta-editor',
			__( 'Settings', 'bulk-meta-editor' ),
			__( 'Settings', 'bulk-meta-editor' ),
			'manage_options',
			'bulk-meta-editor-settings',
			array( __CLASS__, 'render' )
		);
	}

	public static function defaults() {
		return array(
			'post_types'     => array( 'page' ),
			'per_page'       => 20,
			'retention_days' => 0,
			'log_external'   => 1,
		);
	}

	public static function all() {
		if ( self::$cache === null ) {
			$stored      = get_option( self::OPTION, array() );
			self::$cache = wp_parse_args( is_array( $stored ) ? $stored : array(), self::defaults() );
		}
		return self::$cache;
	}

	public static function get( $key ) {
		$all = self::all();
		return isset( $all[ $key ] ) ? $all[ $key ] : null;
	}

	public static function maybe_upgrade() {
		if ( ! is_array( get_option( self::OPTION, null ) ) ) {
			update_option( self::OPTION, self::defaults() );
			self::$cache = null;
		}
	}

	/**
	 * Post type yang dikelola plugin. Hanya post type publik.
	 *
	 * @return string[]
	 */
	public static function supported_post_types() {
		$types = (array) self::get( 'post_types' );
		$types = array_filter(
			array_map( 'sanitize_key', $types ),
			function ( $type ) {
				$object = get_post_type_object( $type );
				return $object && ! empty( $object->public );
			}
		);

		if ( empty( $types ) ) {
			$types = array( 'page' );
		}

		return array_values( array_unique( $types ) );
	}

	public static function is_supported_post( $post_id ) {
		$post = get_post( $post_id );
		return $post && in_array( $post->post_type, self::supported_post_types(), true );
	}

	/**
	 * Kapabilitas minimum untuk membuka plugin, diturunkan dari post type pertama.
	 */
	public static function capability() {
		foreach ( self::supported_post_types() as $type ) {
			$object = get_post_type_object( $type );
			if ( $object && ! empty( $object->cap->edit_posts ) ) {
				return $object->cap->edit_posts;
			}
		}
		return 'edit_pages';
	}

	public static function can_access() {
		return current_user_can( self::capability() );
	}

	public static function can_edit_post( $post_id ) {
		$object = get_post_type_object( get_post_type( $post_id ) );
		if ( ! $object || empty( $object->cap->edit_post ) ) {
			return false;
		}
		return current_user_can( $object->cap->edit_post, $post_id );
	}

	public static function per_page() {
		$value = (int) self::get( 'per_page' );
		return min( 100, max( 10, $value ? $value : 20 ) );
	}

	public static function handle_save() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Kamu tidak punya akses.', 'bulk-meta-editor' ) );
		}

		check_admin_referer( 'bme_save_settings', 'bme_settings_nonce' );

		$raw = isset( $_POST['bme'] ) && is_array( $_POST['bme'] ) ? wp_unslash( $_POST['bme'] ) : array();

		$post_types = isset( $raw['post_types'] ) ? array_map( 'sanitize_key', (array) $raw['post_types'] ) : array();
		if ( empty( $post_types ) ) {
			$post_types = array( 'page' );
		}

		$settings = array(
			'post_types'     => $post_types,
			'per_page'       => min( 100, max( 10, isset( $raw['per_page'] ) ? (int) $raw['per_page'] : 20 ) ),
			'retention_days' => max( 0, isset( $raw['retention_days'] ) ? (int) $raw['retention_days'] : 0 ),
			'log_external'   => empty( $raw['log_external'] ) ? 0 : 1,
		);

		update_option( self::OPTION, $settings );
		self::$cache = null;

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'    => 'bulk-meta-editor-settings',
					'updated' => 1,
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	public static function handle_baseline() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Kamu tidak punya akses.', 'bulk-meta-editor' ) );
		}

		check_admin_referer( 'bme_take_baseline', 'bme_baseline_nonce' );

		$count = BME_DB::take_baseline();

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'     => 'bulk-meta-editor-settings',
					'baseline' => (int) $count,
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Kamu tidak punya akses.', 'bulk-meta-editor' ) );
		}

		$settings = self::all();
		$provider = BME_Provider::instance();
		$types    = get_post_types( array( 'public' => true ), 'objects' );
		unset( $types['attachment'] );

		if ( isset( $_GET['updated'] ) ) {
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Settings disimpan.', 'bulk-meta-editor' ) . '</p></div>';
		}

		if ( isset( $_GET['baseline'] ) ) {
			echo '<div class="notice notice-success is-dismissible"><p>';
			printf(
				/* translators: %d = jumlah baris baseline baru */
				esc_html__( '%d nilai baseline baru direkam.', 'bulk-meta-editor' ),
				(int) $_GET['baseline']
			);
			echo '</p></div>';
		}
		?>
		<div class="wrap bme-wrap">
			<h1><?php esc_html_e( 'Bulk Meta Editor — Settings', 'bulk-meta-editor' ); ?></h1>

			<h2><?php esc_html_e( 'Provider SEO', 'bulk-meta-editor' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Provider aktif', 'bulk-meta-editor' ); ?></th>
					<td>
						<?php if ( $provider ) : ?>
							<span class="bme-pill bme-pill-ok"><span class="dashicons dashicons-yes-alt"></span> <?php echo esc_html( $provider->get_label() ); ?></span>
						<?php else : ?>
							<span class="bme-pill bme-pill-error"><span class="dashicons dashicons-warning"></span> <?php esc_html_e( 'Tidak terdeteksi', 'bulk-meta-editor' ); ?></span>
						<?php endif; ?>
						<p class="description">
							<?php esc_html_e( 'Saat ini baru Yoast SEO yang didukung. Rank Math dan AIOSEO menyusul; adapter provider ada di includes/class-bme-provider.php.', 'bulk-meta-editor' ); ?>
						</p>
					</td>
				</tr>
			</table>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="bme_save_settings" />
				<?php wp_nonce_field( 'bme_save_settings', 'bme_settings_nonce' ); ?>

				<h2><?php esc_html_e( 'Cakupan', 'bulk-meta-editor' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Post type', 'bulk-meta-editor' ); ?></th>
						<td>
							<fieldset>
								<?php foreach ( $types as $type ) : ?>
									<label class="bme-check">
										<input type="checkbox" name="bme[post_types][]" value="<?php echo esc_attr( $type->name ); ?>" <?php checked( in_array( $type->name, (array) $settings['post_types'], true ) ); ?> />
										<?php echo esc_html( $type->labels->name ); ?> <code><?php echo esc_html( $type->name ); ?></code>
									</label>
								<?php endforeach; ?>
								<p class="description"><?php esc_html_e( 'Minimal pilih satu. Kalau tidak ada yang dicentang, plugin memakai Page.', 'bulk-meta-editor' ); ?></p>
							</fieldset>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="bme-per-page"><?php esc_html_e( 'Baris per halaman', 'bulk-meta-editor' ); ?></label></th>
						<td>
							<input type="number" id="bme-per-page" name="bme[per_page]" min="10" max="100" step="5" value="<?php echo esc_attr( (int) $settings['per_page'] ); ?>" class="small-text" />
							<p class="description"><?php esc_html_e( 'Digunakan di editor dan halaman history.', 'bulk-meta-editor' ); ?></p>
						</td>
					</tr>
				</table>

				<h2><?php esc_html_e( 'History', 'bulk-meta-editor' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="bme-retention"><?php esc_html_e( 'Retensi log (hari)', 'bulk-meta-editor' ); ?></label></th>
						<td>
							<input type="number" id="bme-retention" name="bme[retention_days]" min="0" step="1" value="<?php echo esc_attr( (int) $settings['retention_days'] ); ?>" class="small-text" />
							<p class="description"><?php esc_html_e( 'Isi 0 untuk menyimpan log selamanya. Pembersihan berjalan otomatis sekali sehari.', 'bulk-meta-editor' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Perubahan dari luar plugin', 'bulk-meta-editor' ); ?></th>
						<td>
							<label class="bme-check">
								<input type="checkbox" name="bme[log_external]" value="1" <?php checked( ! empty( $settings['log_external'] ) ); ?> />
								<?php esc_html_e( 'Catat juga perubahan meta dari metabox Yoast, editor klasik, atau import plugin lain.', 'bulk-meta-editor' ); ?>
							</label>
						</td>
					</tr>
				</table>

				<?php submit_button( __( 'Simpan Settings', 'bulk-meta-editor' ) ); ?>
			</form>

			<div class="bme-card">
				<h2><?php esc_html_e( 'Baseline History', 'bulk-meta-editor' ); ?></h2>
				<p class="description">
					<?php esc_html_e( 'Perubahan sebelum plugin dipasang tidak bisa direkonstruksi karena WordPress dan Yoast tidak menyimpan riwayat meta. Baseline merekam nilai yang ada saat ini sebagai titik awal: nilai itu muncul di History sebagai sumber "Baseline", bisa jadi restore point, dan tidak ikut terhapus oleh retensi. Baseline otomatis dibuat saat aktivasi; tombol ini untuk menjalankannya ulang.', 'bulk-meta-editor' ); ?>
				</p>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="bme_take_baseline" />
					<?php wp_nonce_field( 'bme_take_baseline', 'bme_baseline_nonce' ); ?>
					<?php submit_button( __( 'Ambil Baseline Sekarang', 'bulk-meta-editor' ), 'secondary', '', false ); ?>
					<span class="description"><?php esc_html_e( 'Hanya menambah baris untuk post+field yang belum punya baseline. Data lama tidak ditimpa.', 'bulk-meta-editor' ); ?></span>
				</form>
			</div>
		</div>
		<?php
	}
}
