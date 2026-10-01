// ==========================================
// 1. JALUR UTAMA UNTUK GAMBAR SISTEM WP & ADMIN (ANTI-DOUBLE WEBP)
// ==========================================

add_filter( 'wp_get_attachment_url', 'sozo_global_url_ke_webp', 99 );
function sozo_global_url_ke_webp( $url ) {
if ( strpos( $url, 'wp-content/uploads/' ) !== false ) {
$old_paths = array(
'https://sozoskinclinic.com/wp-content/uploads/',
'http://sozoskinclinic.com/wp-content/uploads/',
'https://staging.sozoskinclinic.com/wp-content/uploads/',
'http://staging.sozoskinclinic.com/wp-content/uploads/',
'https://sozoskinclinic.cloudindo.co.id/wp-content/uploads/',
'http://sozoskinclinic.cloudindo.co.id/wp-content/uploads/'
);
$url = str_replace( $old_paths, 'https://asset.sozoskinclinic.com/wp-content/uploads/', $url );

// PROTEKSI: Jika sudah berakhiran .webp, jangan ganti ekstensinya lagi
if ( strtolower( pathinfo( $url, PATHINFO_EXTENSION ) ) !== 'webp' ) {
$url = str_replace( array('.jpg', '.jpeg', '.png', '.avif', '.JPG', '.JPEG', '.PNG', '.AVIF'), '.webp', $url );
}
}
return $url;
}

add_filter( 'wp_get_attachment_image_src', 'sozo_global_src_ke_webp', 99, 4 );
function sozo_global_src_ke_webp( $image, $attachment_id, $size, $icon ) {
if ( $image && isset( $image[0] ) && strpos( $image[0], 'wp-content/uploads/' ) !== false ) {
$old_paths = array(
'https://sozoskinclinic.com/wp-content/uploads/',
'http://sozoskinclinic.com/wp-content/uploads/',
'https://staging.sozoskinclinic.com/wp-content/uploads/',
'http://staging.sozoskinclinic.com/wp-content/uploads/',
'https://sozoskinclinic.cloudindo.co.id/wp-content/uploads/',
'http://sozoskinclinic.cloudindo.co.id/wp-content/uploads/'
);
$image[0] = str_replace( $old_paths, 'https://asset.sozoskinclinic.com/wp-content/uploads/', $image[0] );

// PROTEKSI: Jika sudah berakhiran .webp, jangan ganti ekstensinya lagi
if ( strtolower( pathinfo( $image[0], PATHINFO_EXTENSION ) ) !== 'webp' ) {
$image[0] = str_replace( array('.jpg', '.jpeg', '.png', '.avif', '.JPG', '.JPEG', '.PNG', '.AVIF'), '.webp', $image[0] );
}
}
return $image;
}

add_filter( 'wp_calculate_image_srcset', 'sozo_global_srcset_ke_webp', 99 );
function sozo_global_srcset_ke_webp( $sources ) {
if ( is_array( $sources ) ) {
foreach ( $sources as &$source ) {
if ( isset( $source['url'] ) && strpos( $source['url'], 'wp-content/uploads/' ) !== false ) {
$old_paths = array(
'https://sozoskinclinic.com/wp-content/uploads/',
'http://sozoskinclinic.com/wp-content/uploads/',
'https://staging.sozoskinclinic.com/wp-content/uploads/',
'http://staging.sozoskinclinic.com/wp-content/uploads/',
'https://sozoskinclinic.cloudindo.co.id/wp-content/uploads/',
'http://sozoskinclinic.cloudindo.co.id/wp-content/uploads/'
);
$source['url'] = str_replace( $old_paths, 'https://asset.sozoskinclinic.com/wp-content/uploads/', $source['url'] );

// PROTEKSI: Jika sudah berakhiran .webp, jangan ganti ekstensinya lagi
if ( strtolower( pathinfo( $source['url'], PATHINFO_EXTENSION ) ) !== 'webp' ) {
$source['url'] = str_replace( array('.jpg', '.jpeg', '.png', '.avif', '.JPG', '.JPEG', '.PNG', '.AVIF'), '.webp', $source['url'] );
}
}
}
}
return $sources;
}

add_filter( 'wp_prepare_attachment_for_js', 'sozo_global_grid_ke_webp', 99, 3 );
function sozo_global_grid_ke_webp( $response, $attachment, $meta ) {
$old_paths = array(
'https://sozoskinclinic.com/wp-content/uploads/',
'http://sozoskinclinic.com/wp-content/uploads/',
'https://staging.sozoskinclinic.com/wp-content/uploads/',
'http://staging.sozoskinclinic.com/wp-content/uploads/',
'https://sozoskinclinic.cloudindo.co.id/wp-content/uploads/',
'http://sozoskinclinic.cloudindo.co.id/wp-content/uploads/'
);
if ( isset( $response['url'] ) && strpos( $response['url'], 'wp-content/uploads/' ) !== false ) {
$response['url'] = str_replace( $old_paths, 'https://asset.sozoskinclinic.com/wp-content/uploads/', $response['url'] );

// PROTEKSI: Jika sudah berakhiran .webp, jangan ganti ekstensinya lagi
if ( strtolower( pathinfo( $response['url'], PATHINFO_EXTENSION ) ) !== 'webp' ) {
$response['url'] = str_replace( array('.jpg', '.jpeg', '.png', '.avif', '.JPG', '.JPEG', '.PNG', '.AVIF'), '.webp', $response['url'] );
}
}
if ( isset( $response['sizes'] ) && is_array( $response['sizes'] ) ) {
foreach ( $response['sizes'] as &$size ) {
if ( isset( $size['url'] ) && strpos( $size['url'], 'wp-content/uploads/' ) !== false ) {
$size['url'] = str_replace( $old_paths, 'https://asset.sozoskinclinic.com/wp-content/uploads/', $size['url'] );

// PROTEKSI: Jika sudah berakhiran .webp, jangan ganti ekstensinya lagi
if ( strtolower( pathinfo( $size['url'], PATHINFO_EXTENSION ) ) !== 'webp' ) {
$size['url'] = str_replace( array('.jpg', '.jpeg', '.png', '.avif', '.JPG', '.JPEG', '.PNG', '.AVIF'), '.webp', $size['url'] );
}
}
}
}
return $response;
}

// ==========================================
// 2. MENCEGAT CUSTOM HTML ELEMENTOR SECARA AMAN (ANTI-MERUSAK CSS UI)
// ==========================================

add_action( 'template_redirect', 'sozo_mulai_cegat_html_manual', 999 );
function sozo_mulai_cegat_html_manual() {
if ( ! is_admin() ) {
ob_start( 'sozo_proses_ganti_ekstensi_html_aman' );
}
}

function sozo_proses_ganti_ekstensi_html_aman( $html_content ) {
// Daftar domain lama yang ingin diarahkan ke asset
$old_domains = array(
'https://sozoskinclinic.com',
'http://sozoskinclinic.com',
'https://staging.sozoskinclinic.com',
'http://staging.sozoskinclinic.com',
'https://sozoskinclinic.cloudindo.co.id',
'http://sozoskinclinic.cloudindo.co.id'
);

foreach ( $old_domains as $domain ) {
// REGEX AMAN: Hanya mencari URL di folder uploads yang BERAKHIRAN file gambar.
// File .css dan .js Elementor akan diabaikan dan aman.
$pattern = '/' . preg_quote( $domain . '/wp-content/uploads/', '/' ) . '([^"\'\s>]+)\.(jpg|jpeg|png|avif|webp)/i';

$html_content = preg_replace_callback( $pattern, function( $matches ) {
$file_path = $matches[1]; // Path file (contoh: 2023/11/laser-asset9)

// Apapun ekstensi aslinya, kita ubah menjadi webp secara paksa untuk output
$new_extension = 'webp';

// Gabungkan dengan domain asset yang baru
return 'https://asset.sozoskinclinic.com/wp-content/uploads/' . $file_path . '.' . $new_extension;

}, $html_content );
}

return $html_content;
}