<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Kontrak provider meta SEO.
 *
 * Field yang dikenal plugin: 'title' dan 'desc'.
 * Provider baru (Rank Math, AIOSEO, dsb.) cukup menambah subclass dan
 * mengembalikannya lewat filter `bme_provider`.
 */
abstract class BME_Provider {

	/**
	 * @var BME_Provider|null
	 */
	private static $instance = null;

	/**
	 * Provider aktif untuk situs ini. Null kalau tidak ada yang cocok.
	 *
	 * @return BME_Provider|null
	 */
	public static function instance() {
		if ( self::$instance instanceof BME_Provider ) {
			return self::$instance;
		}

		$provider = apply_filters( 'bme_provider', null );

		if ( ! $provider instanceof BME_Provider ) {
			$yoast    = new BME_Yoast_Provider();
			$provider = $yoast->is_active() ? $yoast : null;
		}

		if ( $provider instanceof BME_Provider ) {
			self::$instance = $provider;
		}

		return self::$instance;
	}

	abstract public function is_active();

	abstract public function get_label();

	/**
	 * Map field internal ke meta key. Contoh: array('title' => '_yoast_wpseo_title').
	 *
	 * @return array<string, string>
	 */
	abstract public function field_keys();

	abstract public function get( $post_id, $field );

	abstract public function set( $post_id, $field, $value );

	public function meta_key( $field ) {
		$keys = $this->field_keys();
		return isset( $keys[ $field ] ) ? $keys[ $field ] : '';
	}

	public function field_from_key( $meta_key ) {
		foreach ( $this->field_keys() as $field => $key ) {
			if ( $key === $meta_key ) {
				return $field;
			}
		}
		return '';
	}

	public function owns_key( $meta_key ) {
		return in_array( $meta_key, $this->field_keys(), true );
	}

	public function field_labels() {
		return array(
			'title' => __( 'SEO Title', 'bulk-meta-editor' ),
			'desc'  => __( 'Meta Description', 'bulk-meta-editor' ),
		);
	}

	public function field_label( $meta_key ) {
		$field  = $this->field_from_key( $meta_key );
		$labels = $this->field_labels();
		return isset( $labels[ $field ] ) ? $labels[ $field ] : $meta_key;
	}
}

/**
 * Adapter Yoast SEO.
 */
class BME_Yoast_Provider extends BME_Provider {

	const TITLE_KEY = '_yoast_wpseo_title';
	const DESC_KEY  = '_yoast_wpseo_metadesc';

	public function is_active() {
		return defined( 'WPSEO_VERSION' ) || class_exists( 'WPSEO_Options' );
	}

	public function get_label() {
		return 'Yoast SEO';
	}

	public function field_keys() {
		return array(
			'title' => self::TITLE_KEY,
			'desc'  => self::DESC_KEY,
		);
	}

	public function get( $post_id, $field ) {
		$key = $this->meta_key( $field );
		if ( '' === $key ) {
			return '';
		}
		return (string) get_post_meta( $post_id, $key, true );
	}

	/**
	 * Nilai kosong menghapus meta supaya Yoast kembali memakai template default.
	 */
	public function set( $post_id, $field, $value ) {
		$key = $this->meta_key( $field );
		if ( '' === $key ) {
			return false;
		}

		$value = (string) $value;

		if ( '' === $value ) {
			return delete_post_meta( $post_id, $key );
		}

		return update_post_meta( $post_id, $key, $value );
	}
}
