/**
 * ==========================================================
 * SOZO SKIN - SINGLE POST LAYOUT + SCHEMA
 * Shortcode: [sozoskin_single]
 *
 * Includes:
 * - Single post editorial layout
 * - Featured image optimized with srcset/sizes
 * - Auto Table of Contents from H2/H3
 * - Sticky desktop TOC, accordion mobile TOC
 * - Medical editorial byline
 * - References accordion
 * - Sidebar kanan: CTA banner + Artikel terbaru (BARU)
 * - Related articles
 * - JSON-LD schema
 * - Yoast schema disabled only on single post
 * ==========================================================
 */

/**
 * ==========================================================
 * 1. Disable Yoast JSON-LD schema on single posts only
 * ==========================================================
 */
add_filter('wpseo_json_ld_output', 'sz_disable_yoast_schema_on_single_post');
function sz_disable_yoast_schema_on_single_post($data) {
    if (is_singular('post')) {
        return false;
    }

    return $data;
}

/**
 * ==========================================================
 * 2. Generate TOC and inject heading IDs
 * ==========================================================
 */
function sz_generate_toc_and_inject_ids($html_content) {
    $toc_items = array();

    if (trim((string) $html_content) === '' || !class_exists('DOMDocument')) {
        return array($html_content, $toc_items);
    }

    $dom = new DOMDocument();

    libxml_use_internal_errors(true);
    $dom->loadHTML('<?xml encoding="utf-8" ?><div>' . $html_content . '</div>');
    libxml_clear_errors();

    $xpath    = new DOMXPath($dom);
    $headings = $xpath->query('//h2 | //h3');

    $used_slugs = array();

    foreach ($headings as $heading) {
        $text = trim($heading->textContent);

        if ($text === '') {
            continue;
        }

        $existing_id = $heading->getAttribute('id');
        $slug        = $existing_id !== '' ? sanitize_title($existing_id) : sanitize_title($text);

        if ($slug === '') {
            $slug = 'section';
        }

        $base_slug = $slug;
        $i         = 2;

        while (in_array($slug, $used_slugs, true)) {
            $slug = $base_slug . '-' . $i;
            $i++;
        }

        $used_slugs[] = $slug;

        $heading->setAttribute('id', $slug);

        $toc_items[] = array(
            'id'    => $slug,
            'text'  => $text,
            'level' => (strtolower($heading->tagName) === 'h3') ? 3 : 2,
        );
    }

    $wrapper = $dom->getElementsByTagName('div')->item(0);

    if (!$wrapper) {
        return array($html_content, $toc_items);
    }

    $new_html = '';

    foreach ($wrapper->childNodes as $child) {
        $new_html .= $dom->saveHTML($child);
    }

    return array($new_html, $toc_items);
}

/**
 * ==========================================================
 * 2B. Reviewer meta helper — ambil custom field per post dengan fallback
 *
 * Custom fields (semua optional, set per post di panel Custom Fields):
 *   sozo_reviewer_key           → pilih dari registry: 'elisabeth' | 'gesha' | 'audi' | 'putri' | 'syerli'
 *   sozo_reviewer_display_name  → override nama UI (byline + trust panel)
 *   sozo_reviewer_display_url   → override link UI
 *   sozo_reviewer_display_role  → override jabatan/judul di UI
 *   sozo_reviewer_schema_name   → override nama + gelar untuk JSON-LD
 *   sozo_reviewer_schema_url    → override URL profile untuk JSON-LD
 *   sozo_reviewer_schema_role   → override jobTitle untuk JSON-LD
 *
 * Field kosong → fallback ke registry / default (gak breaking post lama).
 * ==========================================================
 */
function sz_reviewer_meta($post_id, $key, $fallback) {
    $val = trim((string) get_post_meta($post_id, $key, true));
    return $val !== '' ? $val : $fallback;
}

/**
 * ==========================================================
 * 2C. Reviewers registry — dokter yang tersedia sebagai reviewer
 *
 * Dipanggil dari shortcode untuk populate $reviewer_* variables dan schema.
 * Tambah dokter baru → tambah entry di sini aja.
 * ==========================================================
 */
function sz_get_reviewers_registry() {
    return array(
        'elisabeth' => array(
            'display_name' => 'dr. Elisabeth Ryan, Sp.D.V.E',
            'display_url'  => 'https://sozoskinclinic.com/tim-dokter-sozo-skin/elisabeth-ryan/',
            'display_role' => 'Dermatologi, Venereologi, & Estetika',
            'schema_name'  => 'dr. Elisabeth Ryan, Sp.D.V.E',
            'schema_url'   => 'https://sozoskinclinic.com/tim-dokter-sozo-skin/elisabeth-ryan/',
            'schema_role'  => 'Dermatologi, Venereologi, & Estetika',
            'expertise'    => array('Trikoskopi', 'Bedah Kulit', 'Esetetika Medis'),
            'image_url'    => 'https://asset.sozoskinclinic.com/wp-content/uploads/2026/07/dr-eli.webp',
            'credentials'  => array(
                array(
                    '@type'              => 'EducationalOccupationalCredential',
                    'credentialCategory' => 'license',
                    'name'               => 'Surat Tanda Registrasi (STR)',
                    'identifier'         => 'STRUI00001652595312',
                    'recognizedBy'       => array(
                        '@type' => 'Organization',
                        'name'  => 'Konsil Kedokteran Indonesia',
                    ),
                ),
                array(
                    '@type'              => 'EducationalOccupationalCredential',
                    'credentialCategory' => 'degree',
                    'name'               => 'Spesialis Kulit dan Kelamin (Sp.DVE)',
                    'recognizedBy'       => array(
                        '@type' => 'Organization',
                        'name'  => 'Universitas Indonesia',
                        'url'   => 'https://www.ui.ac.id/',
                    ),
                ),
            ),
            'alumni' => array(
                array('@type' => 'CollegeOrUniversity', 'name' => 'Universitas Kristen Krida Wacana',     'url' => 'https://www.ukrida.ac.id/'),
                array('@type' => 'CollegeOrUniversity', 'name' => 'Research Institute for Tropical Medicine', 'url' => 'https://www.ritm.gov.ph/'),
                array('@type' => 'CollegeOrUniversity', 'name' => 'Medical University of Warsaw',        'url' => 'https://www.wum.edu.pl/'),
                array('@type' => 'CollegeOrUniversity', 'name' => 'Universitas Indonesia',               'url' => 'https://www.ui.ac.id/'),
            ),
        ),
        'gesha' => array(
            'display_name' => 'dr. Gesha Kautzar Putri, M.Biomed (AAM)',
            'display_url'  => 'https://sozoskinclinic.com/tim-dokter-sozo-skin/gesha-kautzar-putri/',
            'display_role' => 'Anti-Aging & Estetika',
            'schema_name'  => 'dr. Gesha Kautzar Putri, M.Biomed (AAM)',
            'schema_url'   => 'https://sozoskinclinic.com/tim-dokter-sozo-skin/gesha-kautzar-putri/',
            'schema_role'  => 'Anti-Aging & Estetika',
            'expertise'    => array('Botox', 'Threadlift', 'Filler', 'Laser', 'Skinbooster'),
            'image_url'    => 'https://asset.sozoskinclinic.com/wp-content/uploads/2026/07/dr.-Gesha-Kautzar-Putri-M.Biomed-AAM.webp',
            'credentials'  => array(
                array(
                    '@type'              => 'EducationalOccupationalCredential',
                    'credentialCategory' => 'license',
                    'name'               => 'Surat Tanda Registrasi (STR)',
                    'identifier'         => 'KT00000123255767',
                    'recognizedBy'       => array(
                        '@type' => 'Organization',
                        'name'  => 'Konsil Kedokteran Indonesia',
                    ),
                ),
                array(
                    '@type'              => 'EducationalOccupationalCredential',
                    'credentialCategory' => 'degree',
                    'name'               => 'Magister Biomedis Anti-Aging Medicine (M.Biomed AAM)',
                    // recognizedBy omitted: universitas asal belum dikonfirmasi
                ),
            ),
            'alumni' => array(),
        ),
        'audi' => array(
            'display_name' => 'dr. Audi Sugiharto, Sp.D.V.E',
            'display_url'  => 'https://sozoskinclinic.com/tim-dokter-sozo-skin/audi-sugiharto/',
            'display_role' => 'Dermatologi, Venereologi, & Estetika',
            'schema_name'  => 'dr. Audi Sugiharto, Sp.D.V.E',
            'schema_url'   => 'https://sozoskinclinic.com/tim-dokter-sozo-skin/audi-sugiharto/',
            'schema_role'  => 'Dermatologi, Venereologi, & Estetika',
            'expertise'    => array('Bedah Kulit', 'Bedah Kecantikan', 'Esetetika Medis'),
            'image_url'    => 'https://asset.sozoskinclinic.com/wp-content/uploads/2026/07/dr.-Audi-Sugiharto-Sp.D.V.E.webp',
            'credentials'  => array(
                array(
                    '@type'              => 'EducationalOccupationalCredential',
                    'credentialCategory' => 'license',
                    'name'               => 'Surat Tanda Registrasi (STR)',
                    'identifier'         => 'VP00000073538568',
                    'recognizedBy'       => array(
                        '@type' => 'Organization',
                        'name'  => 'Konsil Kedokteran Indonesia',
                    ),
                ),
                array(
                    '@type'              => 'EducationalOccupationalCredential',
                    'credentialCategory' => 'degree',
                    'name'               => 'Spesialis Kulit dan Kelamin (Sp.D.V.E)',
                    // recognizedBy omitted: universitas asal belum dikonfirmasi
                ),
            ),
            'alumni' => array(),
        ),
        'putri' => array(
            'display_name' => 'dr. Putri Rizkya',
            'display_url'  => 'https://sozoskinclinic.com/tim-dokter-sozo-skin/dr-rr-putri-rizkya/',
            'display_role' => 'Anti-Aging & Estetika',
            'schema_name'  => 'dr. RR. Putri Rizkya',
            'schema_url'   => 'https://sozoskinclinic.com/tim-dokter-sozo-skin/dr-rr-putri-rizkya/',
            'schema_role'  => 'Anti-Aging & Estetika',
            'expertise'    => array('Injeksi', 'Kontur Wajah', 'Laser', 'Skin Booster'),
            'image_url'    => 'https://asset.sozoskinclinic.com/wp-content/uploads/2026/07/dokter-putri.webp',
            'credentials'  => array(),
            'alumni'       => array(
                array(
                    '@type' => 'CollegeOrUniversity',
                    'name'  => 'Universitas Hang Tuah, Surabaya',
                ),
            ),
        ),
        'syerli' => array(
            'display_name' => 'dr. Syerli Rahmadeni',
            'display_url'  => 'https://sozoskinclinic.com/tim-dokter-sozo-skin/dr-syerli-rahmadeni/',
            'display_role' => 'Anti-Aging & Estetika',
            'schema_name'  => 'dr. Syerli Rahmadeni',
            'schema_url'   => 'https://sozoskinclinic.com/tim-dokter-sozo-skin/dr-syerli-rahmadeni/',
            'schema_role'  => 'Anti-Aging & Estetika',
            'expertise'    => array('Estetika Wajah', 'Peremajaan', 'Kontur Wajah'),
            'image_url'    => 'https://asset.sozoskinclinic.com/wp-content/uploads/2026/07/dr-sherly.webp',
            'credentials'  => array(
                array(
                    '@type'              => 'EducationalOccupationalCredential',
                    'credentialCategory' => 'license',
                    'name'               => 'Surat Tanda Registrasi (STR)',
                    'identifier'         => '1321100222169296',
                    'recognizedBy'       => array(
                        '@type' => 'Organization',
                        'name'  => 'Konsil Kedokteran Indonesia',
                    ),
                ),
            ),
            'alumni'       => array(
                array(
                    '@type' => 'CollegeOrUniversity',
                    'name'  => 'Universitas Baiturrahmah, Padang',
                ),
            ),
        ),
    );
}

/**
 * ==========================================================
 * 2D. Reviewer meta box — dropdown UI di post editor
 *
 * Pilih dokter reviewer dari registry, simpan ke post meta sozo_reviewer_key.
 * Aman: nonce + capability check + whitelist key dari registry.
 * ==========================================================
 */

// Tampilkan meta box di sidebar post editor
add_action('add_meta_boxes', 'sz_add_reviewer_meta_box');

function sz_add_reviewer_meta_box() {
    add_meta_box(
        'sz_reviewer_meta',         // ID
        'Peninjau Medis',           // Judul box
        'sz_render_reviewer_meta_box', // Callback render
        'post',                     // Post type (cuma di post)
        'side',                     // Posisi: sidebar kanan
        'high'                      // Prioritas tinggi
    );
}

// Render: form select + deskripsi
function sz_render_reviewer_meta_box($post) {
    // Nonce untuk keamanan save_post
    wp_nonce_field('sz_save_reviewer', 'sz_reviewer_nonce');

    $current_key = (string) get_post_meta($post->ID, 'sozo_reviewer_key', true);
    $registry    = sz_get_reviewers_registry();
    ?>
    <label for="sozo_reviewer_key" style="display:block; margin:0 0 6px; font-weight:600;">
        Pilih Dokter Reviewer:
    </label>

    <select name="sozo_reviewer_key" id="sozo_reviewer_key" style="width:100%;">
        <option value="">— Default (Tim Medis) —</option>

        <?php foreach ($registry as $key => $doc) : ?>
            <option value="<?php echo esc_attr($key); ?>" <?php selected($current_key, $key); ?>>
                <?php echo esc_html($doc['display_name']); ?> — <?php echo esc_html($doc['display_role']); ?>
            </option>
        <?php endforeach; ?>
    </select>

    <p class="description" style="margin-top:10px;">
        Schema JSON-LD (nama, URL, STR, keahlian) otomatis ter-update sesuai pilihan.
    </p>

    <details style="margin-top:10px; font-size:12px; color:#666;">
        <summary style="cursor:pointer;">Atau isi manual via Custom Fields</summary>
        <p style="margin-top:6px;">
            Name: <code>sozo_reviewer_key</code><br>
            Value: <code>elisabeth</code> / <code>gesha</code> / <code>audi</code> / <code>putri</code> / <code>syerli</code>
        </p>
    </details>
    <?php
}

// Save handler dengan validasi
add_action('save_post_post', 'sz_save_reviewer_meta_box');

function sz_save_reviewer_meta_box($post_id) {
    // 1. Nonce verification
    if (!isset($_POST['sz_reviewer_nonce']) || !wp_verify_nonce($_POST['sz_reviewer_nonce'], 'sz_save_reviewer')) {
        return;
    }

    // 2. Skip autosave / revision
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (wp_is_post_revision($post_id)) {
        return;
    }

    // 3. Capability check (user harus bisa edit post ini)
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // 4. Whitelist validation (hanya terima key yang ada di registry)
    $registry_keys = array_keys(sz_get_reviewers_registry());
    $submitted     = isset($_POST['sozo_reviewer_key']) ? sanitize_key($_POST['sozo_reviewer_key']) : '';

    // Key gak dikenal → abaikan (gak nyimpan apa-apa)
    if ($submitted !== '' && !in_array($submitted, $registry_keys, true)) {
        return;
    }

    // 5. Simpan: kosong = hapus meta, isi = update
    if ($submitted === '') {
        delete_post_meta($post_id, 'sozo_reviewer_key');
    } else {
        update_post_meta($post_id, 'sozo_reviewer_key', $submitted);
    }
}

/**
 * ==========================================================
 * 3. Shortcode
 * ==========================================================
 */
add_shortcode('sozoskin_single', 'sz_single_post_shortcode');

function sz_single_post_shortcode() {
    if (!is_singular('post')) {
        return '<p style="text-align:center;">Shortcode ini khusus untuk halaman Single Post.</p>';
    }

    ob_start();

    $post_id   = get_the_ID();
    $permalink = get_permalink($post_id);
    $site_url  = home_url('/');

    /**
     * ======================================================
     * Article data
     * ======================================================
     */
    $categories = get_the_category($post_id);
    $cat_name   = !empty($categories) ? $categories[0]->name : 'Artikel';
    $cat_link   = !empty($categories) ? get_category_link($categories[0]->term_id) : home_url('/artikel/');

    $post_title = get_the_title($post_id);

    $content_raw   = get_post_field('post_content', $post_id);
    $content_plain = wp_strip_all_tags(strip_shortcodes($content_raw));
    $article_body  = trim(preg_replace('/\s+/u', ' ', $content_plain));

    if (has_excerpt($post_id)) {
        $description = wp_strip_all_tags(get_the_excerpt($post_id));
    } else {
        $description = wp_trim_words($content_plain, 28, '...');
    }

    preg_match_all('/\p{L}+/u', $content_plain, $matches);
    $word_count   = isset($matches[0]) ? count($matches[0]) : 0;
    $reading_time = max(1, (int) ceil($word_count / 200));

    $date_published_iso     = get_the_date('c', $post_id);
    $date_modified_iso      = get_the_modified_date('c', $post_id);
    $date_published_display = get_the_date('j F Y', $post_id);
    $date_modified_display  = get_the_modified_date('j F Y', $post_id);

    /**
     * ======================================================
     * Featured image
     * ======================================================
     */
    $thumb_id   = get_post_thumbnail_id($post_id);
    $thumb_data = $thumb_id ? wp_get_attachment_image_src($thumb_id, 'full') : false;

    $thumb_url = $thumb_data ? $thumb_data[0] : '';
    $thumb_w   = $thumb_data ? (int) $thumb_data[1] : 1200;
    $thumb_h   = $thumb_data ? (int) $thumb_data[2] : 675;

    /**
     * ======================================================
     * Content + TOC
     * ======================================================
     */
    $content_html = apply_filters('the_content', get_the_content(null, false, $post_id));
    list($content_html, $toc_items) = sz_generate_toc_and_inject_ids($content_html);

    /**
     * ======================================================
     * Author and reviewer data
     * ======================================================
     */
    $post_author_id = (int) get_post_field('post_author', $post_id);
    $author_name    = get_the_author_meta('display_name', $post_author_id);
    if (empty($author_name)) {
        $author_name = 'Tim Editorial Sozo Skin Clinic';
    }

    $author_url = get_author_posts_url($post_author_id);
    if (empty($author_url)) {
        $author_url = home_url('/editorial-board/');
    }

    $author_avatar = get_avatar_url($post_author_id, array('size' => 112));
    if (empty($author_avatar)) {
        $author_avatar = 'https://sozoskinclinic.com/wp-content/uploads/2026/04/sozologo.avif';
    }

    $author_role = get_the_author_meta('job_title', $post_author_id);
    if (empty($author_role)) {
        $author_role = 'Medical Content Specialist';
    }

    // ── Reviewer selection: registry key → 6 custom field override → hardcoded default ──

    $reviewers_registry = sz_get_reviewers_registry();
    $reviewer_key       = trim((string) get_post_meta($post_id, 'sozo_reviewer_key', true));

    if ($reviewer_key !== '' && isset($reviewers_registry[$reviewer_key])) {
        // Path 1: Key ketemu di registry → pake data dokter itu (override per-field tetep bisa)
        $r = $reviewers_registry[$reviewer_key];

        $reviewer_display_name = sz_reviewer_meta($post_id, 'sozo_reviewer_display_name', $r['display_name']);
        $reviewer_display_url  = sz_reviewer_meta($post_id, 'sozo_reviewer_display_url',  $r['display_url']);
        $reviewer_display_role = sz_reviewer_meta($post_id, 'sozo_reviewer_display_role', $r['display_role']);
        $reviewer_schema_name  = sz_reviewer_meta($post_id, 'sozo_reviewer_schema_name',  $r['schema_name']);
        $reviewer_schema_url   = sz_reviewer_meta($post_id, 'sozo_reviewer_schema_url',   $r['schema_url']);
        $reviewer_schema_role  = sz_reviewer_meta($post_id, 'sozo_reviewer_schema_role',  $r['schema_role']);

        $reviewer_credentials = $r['credentials'];
        $reviewer_alumni      = $r['alumni'];
        $reviewer_expertise   = $r['expertise'];
        $reviewer_image_url   = sz_reviewer_meta($post_id, 'sozo_reviewer_image_url',   $r['image_url']);
    } else {
        // Path 2: Key kosong/invalid → fallback ke 6 field + hardcode (zero breaking change)
        $reviewer_display_name = sz_reviewer_meta($post_id, 'sozo_reviewer_display_name', 'Tim Medis Sozo Skin Clinic');
        $reviewer_display_url  = sz_reviewer_meta($post_id, 'sozo_reviewer_display_url',  home_url('/editorial-board/'));
        $reviewer_display_role = sz_reviewer_meta($post_id, 'sozo_reviewer_display_role', 'Medical Reviewer');
        $reviewer_schema_name  = sz_reviewer_meta($post_id, 'sozo_reviewer_schema_name',  'dr. Elisabeth Ryan, Sp.D.V.E');
        $reviewer_schema_url   = sz_reviewer_meta($post_id, 'sozo_reviewer_schema_url',   'https://sozoskinclinic.com/tim-dokter-sozo-skin/elisabeth-ryan/');
        $reviewer_schema_role  = sz_reviewer_meta($post_id, 'sozo_reviewer_schema_role',  'Dermatologi, Venereologi, & Estetika');

        // Credentials & alumni default (legacy hardcode Elisabeth)
        $reviewer_credentials = array(
            array(
                '@type'              => 'EducationalOccupationalCredential',
                'credentialCategory' => 'license',
                'name'               => 'Surat Tanda Registrasi (STR)',
                'identifier'         => 'STRUI00001652595312',
                'recognizedBy'       => array(
                    '@type' => 'Organization',
                    'name'  => 'Konsil Kedokteran Indonesia',
                ),
            ),
            array(
                '@type'              => 'EducationalOccupationalCredential',
                'credentialCategory' => 'degree',
                'name'               => 'Spesialis Kulit dan Kelamin (Sp.DVE)',
                'recognizedBy'       => array(
                    '@type' => 'Organization',
                    'name'  => 'Universitas Indonesia',
                    'url'   => 'https://www.ui.ac.id/',
                ),
            ),
        );
        $reviewer_alumni = array(
            array('@type' => 'CollegeOrUniversity', 'name' => 'Universitas Kristen Krida Wacana',     'url' => 'https://www.ukrida.ac.id/'),
            array('@type' => 'CollegeOrUniversity', 'name' => 'Research Institute for Tropical Medicine', 'url' => 'https://www.ritm.gov.ph/'),
            array('@type' => 'CollegeOrUniversity', 'name' => 'Medical University of Warsaw',        'url' => 'https://www.wum.edu.pl/'),
            array('@type' => 'CollegeOrUniversity', 'name' => 'Universitas Indonesia',               'url' => 'https://www.ui.ac.id/'),
        );
        $reviewer_expertise = array('Trikoskopi', 'Bedah Kulit', 'Esetetika Medis');
        $reviewer_image_url = sz_reviewer_meta($post_id, 'sozo_reviewer_image_url', 'https://sozoskinclinic.com/wp-content/uploads/2026/04/sozologo.avif');
    }

    // Reviewer diperlakukan sebagai Person kalau key registry valid, atau kalau
    // post lama override schema_name + schema_url manual. Selain itu reviewedBy
    // mengarah ke tim medis (#organization) supaya nama dokter di schema tidak
    // beda dengan nama yang tampil di UI.
    $reviewer_is_person = ($reviewer_key !== '' && isset($reviewers_registry[$reviewer_key]));

    if (!$reviewer_is_person
        && trim((string) get_post_meta($post_id, 'sozo_reviewer_schema_name', true)) !== ''
        && trim((string) get_post_meta($post_id, 'sozo_reviewer_schema_url', true)) !== ''
    ) {
        $reviewer_is_person = true;
    }

    // Label profile link: "Profil Dokter" kalau URL dokter spesifik,
    // "Standar Editorial" kalau URL generic (editorial board)
    $reviewer_profile_label = (strpos($reviewer_display_url, '/tim-dokter-sozo-skin/') !== false)
        ? 'Lihat Profil Dokter'
        : 'Lihat Standar Editorial';

    $last_reviewed_raw = trim((string) get_post_meta($post_id, 'sozo_last_reviewed', true));

    if ($last_reviewed_raw !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $last_reviewed_raw)) {
        $last_reviewed_iso     = $last_reviewed_raw;
        $last_reviewed_display = date_i18n('j F Y', strtotime($last_reviewed_raw));
    } else {
        $last_reviewed_iso     = get_the_modified_date('Y-m-d', $post_id);
        $last_reviewed_display = $date_modified_display;
    }

    $medical_topic = trim((string) get_post_meta($post_id, 'sozo_medical_topic', true));

    if ($medical_topic === '') {
        $medical_topic = 'Dermatology';
    }

    /**
     * ======================================================
     * References
     * Custom field: sozo_references
     * One URL per line
     * ======================================================
     */
    $references_raw = trim((string) get_post_meta($post_id, 'sozo_references', true));
    $references     = array();

    if ($references_raw !== '') {
        $reference_lines = preg_split('/\r\n|\r|\n/', $references_raw);

        foreach ($reference_lines as $line) {
            $line = trim($line);

            if ($line !== '' && filter_var($line, FILTER_VALIDATE_URL)) {
                $references[] = esc_url_raw($line);
            }
        }
    }

    /**
     * ======================================================
     * Related posts query
     * ======================================================
     */
    $related_query = null;

    $related_args = array(
        'category__in'        => wp_get_post_categories($post_id),
        'post__not_in'        => array($post_id),
        'posts_per_page'      => 3,
        'no_found_rows'       => true,
        'ignore_sticky_posts' => true,
    );

    $related_query = new WP_Query($related_args);

    /**
     * ======================================================
     * (BARU) Sidebar kanan: CTA Banner + Artikel Terbaru
     * Ganti teks, nomor, dan link di bawah sesuai kebutuhan.
     * ======================================================
     */
    $sidebar_cta_heading     = 'Ada Pertanyaan Seputar Kulitmu?';
    $sidebar_cta_text        = 'Konsultasikan langsung dengan tim kami, gratis dan tanpa antre.';
    $sidebar_cta_phone_raw   = '6285175225664';
    $sidebar_cta_phone_label = '+62 851-7522-5664';
    $sidebar_cta_button_text = 'Konsultasi Sekarang';
    $sidebar_cta_button_url  = 'https://api.whatsapp.com/send?phone=6285175225664&text=Halo%2C%20saya%20mau%20booking%20promo%20treatment%20Skin%20di%20Sozo%20%5Bsrc%3ABLOG-Skin%5D';
    $sidebar_promo_img_url   = 'https://asset.sozoskinclinic.com/wp-content/uploads/2026/09/cicilan0.webp';
    $sidebar_promo_img_alt   = 'Promo Cicilan 0% Sozo Skin Clinic';

    $sidebar_recent_query = new WP_Query(array(
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => 3,
        'post__not_in'        => array($post_id),
        'orderby'             => 'date',
        'order'               => 'DESC',
        'no_found_rows'       => true,
        'ignore_sticky_posts' => true,
    ));

    /**
     * ======================================================
     * Main output
     * ======================================================
     */
    ?>

    <main class="sz-single-wrapper" id="post-<?php echo esc_attr($post_id); ?>">

        <header class="sz-single-header">

            <div class="sz-breadcrumbs" aria-label="Breadcrumb">
                <a href="<?php echo esc_url(home_url('/blog/')); ?>">Blog</a>
                <span aria-hidden="true">/</span>
                <a href="<?php echo esc_url($cat_link); ?>"><?php echo esc_html($cat_name); ?></a>
            </div>

            <div class="sz-single-meta-top">
                <a href="<?php echo esc_url($cat_link); ?>" class="sz-cat-link">
                    <?php echo esc_html($cat_name); ?>
                </a>
                <span class="sz-dot" aria-hidden="true">&bull;</span>
                <time datetime="<?php echo esc_attr($date_published_iso); ?>">
                    <?php echo esc_html($date_published_display); ?>
                </time>
                <span class="sz-dot" aria-hidden="true">&bull;</span>
                <span class="sz-read-time">
                    <?php echo esc_html($reading_time); ?> mnt baca
                </span>
            </div>

            <h1 class="sz-single-title">
                <?php echo esc_html($post_title); ?>
            </h1>

            <div class="sz-editorial-byline" aria-label="Informasi Editorial">
                <div class="sz-byline-info">
                    Ditulis oleh
                    <a href="<?php echo esc_url($author_url); ?>">
                        <?php echo esc_html($author_name); ?>
                    </a>

                    <span class="sz-byline-separator" aria-hidden="true">|</span>

                    Ditinjau medis oleh
                    <a href="<?php echo esc_url($reviewer_display_url); ?>">
                        <?php echo esc_html($reviewer_display_name); ?>
                    </a>

                    <br>

                    <span class="sz-byline-updated">
                        Diperbarui pada <?php echo esc_html($last_reviewed_display); ?>
                    </span>
                </div>
            </div>

        </header>

        <?php if ($thumb_id && $thumb_url) : ?>
            <figure class="sz-single-hero-img">
                <?php
                echo wp_get_attachment_image(
                    $thumb_id,
                    'full',
                    false,
                    array(
                        'alt'           => $post_title,
                        'fetchpriority' => 'high',
                        'decoding'      => 'async',
                        'data-no-lazy'  => '1',
                        'class'         => 'skip-lazy',
                        'sizes'         => '(min-width: 768px) 720px, 100vw',
                    )
                );
                ?>
            </figure>
        <?php endif; ?>

        <div class="sz-article-layout<?php echo (count($toc_items) >= 2) ? ' sz-has-toc' : ''; ?>">

            <?php if (count($toc_items) >= 2) : ?>
                <aside class="sz-toc-sidebar">
                    <nav class="sz-toc" aria-label="Daftar Isi Artikel">
                        <details class="sz-toc-details" open>
                            <summary class="sz-toc-summary">
                                <span class="sz-toc-icon" aria-hidden="true">&#9776;</span>
                                <span>Daftar Isi</span>
                            </summary>

                            <ol class="sz-toc-list">
                                <?php foreach ($toc_items as $item) : ?>
                                    <li class="sz-toc-item sz-toc-level-<?php echo esc_attr($item['level']); ?>">
                                        <a href="#<?php echo esc_attr($item['id']); ?>">
                                            <?php echo esc_html($item['text']); ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ol>
                        </details>
                    </nav>
                </aside>
            <?php endif; ?>

            <article class="sz-single-article">
                <div class="sz-single-content">
                    <?php echo $content_html; ?>
                </div>
            </article>

            <aside class="sz-side-sidebar" aria-label="Konsultasi dan Artikel Terbaru">

                <div class="sz-cta-banner">
                    <h3 class="sz-cta-banner-title"><?php echo esc_html($sidebar_cta_heading); ?></h3>
                    <p class="sz-cta-banner-text"><?php echo esc_html($sidebar_cta_text); ?></p>

                    <a href="<?php echo esc_url($sidebar_cta_button_url); ?>" target="_blank" rel="noopener" class="sz-cta-banner-button">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.882-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.015-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.82 9.82 0 0 1 2.893 6.994c-.003 5.45-4.437 9.885-9.885 9.885m8.413-18.297A11.81 11.81 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.88 11.88 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.8 11.8 0 0 0-3.48-8.413Z"/>
                        </svg>
                        <?php echo esc_html($sidebar_cta_button_text); ?>
                    </a>
                </div>

                <?php if ($sidebar_recent_query && $sidebar_recent_query->have_posts()) : ?>
                    <div class="sz-recent-posts">
                        <h3 class="sz-recent-posts-title">Artikel Terbaru</h3>

                        <ul class="sz-recent-posts-list">
                            <?php
                            while ($sidebar_recent_query->have_posts()) :
                                $sidebar_recent_query->the_post();

                                $rp_thumb_id = get_post_thumbnail_id(get_the_ID());
                            ?>
                                <li class="sz-recent-post-item">
                                    <a href="<?php the_permalink(); ?>" class="sz-recent-post-thumb" aria-label="Baca artikel: <?php echo esc_attr(get_the_title()); ?>" tabindex="-1">
                                        <?php if ($rp_thumb_id) : ?>
                                            <?php
                                             echo wp_get_attachment_image(
                                                $rp_thumb_id,
                                                'thumbnail',
                                                false,
                                                array(
                                                    'alt'      => get_the_title(),
                                                    'loading'  => 'lazy',
                                                    'decoding' => 'async',
                                                )
                                            );
                                            ?>
                                        <?php else : ?>
                                            <img src="<?php echo esc_url($author_avatar); ?>" alt="" width="42" height="42" loading="lazy" decoding="async">
                                        <?php endif; ?>
                                    </a>
                                    <div class="sz-recent-post-body">
                                        <time datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                                            <?php echo esc_html(get_the_date('j F Y')); ?>
                                        </time>
                                        <h4>
                                            <a href="<?php the_permalink(); ?>">
                                                <?php echo esc_html(get_the_title()); ?>
                                            </a>
                                        </h4>
                                    </div>
                                </li>
                            <?php
                            endwhile;
                            wp_reset_postdata();
                            ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <div class="sz-sidebar-promo-banner">
                    <a href="<?php echo esc_url($sidebar_cta_button_url); ?>" target="_blank" rel="noopener" class="sz-sidebar-promo-link" aria-label="<?php echo esc_attr($sidebar_promo_img_alt); ?>">
                        <img src="<?php echo esc_url($sidebar_promo_img_url); ?>" alt="<?php echo esc_attr($sidebar_promo_img_alt); ?>" width="245" height="306" loading="lazy" decoding="async">
                    </a>
                </div>

            </aside>

        </div>

        <?php if (count($toc_items) >= 2) : ?>
            <script>
            (function () {
                var tocDetails = document.querySelector('.sz-toc-details');
                var tocLinks = document.querySelectorAll('.sz-toc-list a');

                if (tocDetails) {
                    var mobileQuery = window.matchMedia('(max-width: 768px)');

                    function syncTocOpenState() {
                        if (mobileQuery.matches) {
                            tocDetails.removeAttribute('open');
                        } else {
                            tocDetails.setAttribute('open', '');
                        }
                    }

                    syncTocOpenState();

                    if (typeof mobileQuery.addEventListener === 'function') {
                        mobileQuery.addEventListener('change', syncTocOpenState);
                    } else if (typeof mobileQuery.addListener === 'function') {
                        mobileQuery.addListener(syncTocOpenState);
                    }
                }

                if (!tocLinks.length || !('IntersectionObserver' in window)) {
                    return;
                }

                var headingMap = {};

                tocLinks.forEach(function (link) {
                    var href = link.getAttribute('href') || '';
                    var id = href.replace('#', '');
                    var heading = document.getElementById(id);

                    if (heading) {
                        headingMap[id] = link;
                    }
                });

                function setActive(link) {
                    tocLinks.forEach(function (item) {
                        item.classList.remove('is-active');
                        item.removeAttribute('aria-current');
                    });

                    link.classList.add('is-active');
                    link.setAttribute('aria-current', 'true');
                }

                var observer = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        var link = headingMap[entry.target.id];

                        if (!link) {
                            return;
                        }

                        if (entry.isIntersecting) {
                            setActive(link);
                        }
                    });
                }, {
                    rootMargin: '-120px 0px -68% 0px',
                    threshold: 0
                });

                Object.keys(headingMap).forEach(function (id) {
                    var heading = document.getElementById(id);

                    if (heading) {
                        observer.observe(heading);
                    }
                });
            })();
            </script>
        <?php endif; ?>

        <?php if (!empty($references)) : ?>
            <details class="sz-references-accordion">
                <summary>
                    Sumber Referensi Medis
                    <span><?php echo esc_html(count($references)); ?></span>
                </summary>

                <ol>
                    <?php foreach ($references as $reference_url) : ?>
                        <li>
                            <a href="<?php echo esc_url($reference_url); ?>" target="_blank" rel="nofollow noopener">
                                <?php echo esc_html($reference_url); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </details>
        <?php endif; ?>

        <?php
        $tags = get_the_tags($post_id);

        if ($tags) :
        ?>
            <div class="sz-post-tags" aria-label="Tag Artikel">
                <?php
                foreach ($tags as $tag) {
                    echo '<a href="' . esc_url(get_tag_link($tag->term_id)) . '">#' . esc_html($tag->name) . '</a>';
                }
                ?>
            </div>
        <?php endif; ?>

        <aside class="sz-trust-panel" aria-label="Tim Editorial Kami">

            <div class="sz-trust-col">
                <h4>Penulis Artikel</h4>

                <div class="sz-trust-profile">
                    <img src="<?php echo esc_url($author_avatar); ?>" alt="<?php echo esc_attr($author_name); ?>" width="56" height="56" loading="lazy" decoding="async">

                    <div class="sz-trust-profile-info">
                        <h3>
                            <a href="<?php echo esc_url($author_url); ?>">
                                <?php echo esc_html($author_name); ?>
                            </a>
                        </h3>

                        <p><?php echo esc_html($author_role); ?></p>

                        <a href="<?php echo esc_url($author_url); ?>" class="sz-profile-link">
                            Lihat Profil Penulis &rarr;
                        </a>
                    </div>
                </div>
            </div>

            <div class="sz-trust-col">
                <h4>Peninjau Medis</h4>

                <div class="sz-trust-profile">
                    <img src="<?php echo esc_url($reviewer_image_url); ?>" alt="<?php echo esc_attr($reviewer_display_name); ?>" width="56" height="56" loading="lazy" decoding="async">

                    <div class="sz-trust-profile-info">
                        <h3>
                            <a href="<?php echo esc_url($reviewer_display_url); ?>">
                                <?php echo esc_html($reviewer_display_name); ?>
                            </a>
                        </h3>

                        <p><?php echo esc_html($reviewer_display_role); ?></p>

                        <a href="<?php echo esc_url($reviewer_display_url); ?>" class="sz-profile-link">
                            <?php echo esc_html($reviewer_profile_label); ?> &rarr;
                        </a>
                    </div>
                </div>
            </div>

        </aside>

        <div class="sz-mobile-cta-float" id="sz-mobile-cta-float">
            <button type="button" class="sz-mobile-cta-close" aria-label="Tutup banner">&times;</button>
            <a href="<?php echo esc_url($sidebar_cta_button_url); ?>" target="_blank" rel="noopener" class="sz-mobile-cta-link" aria-label="<?php echo esc_attr($sidebar_cta_heading); ?>">
                <img src="https://asset.sozoskinclinic.com/wp-content/uploads/2026/07/banner-blog-cta.webp" alt="<?php echo esc_attr($sidebar_cta_heading); ?>" loading="lazy" decoding="async">
            </a>
        </div>

        <a href="<?php echo esc_url($sidebar_cta_button_url); ?>" target="_blank" rel="noopener" class="sz-custom-wa-float" id="sz-custom-wa-float" aria-label="Chat WhatsApp">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.882-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.015-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.82 9.82 0 0 1 2.893 6.994c-.003 5.45-4.437 9.885-9.885 9.885m8.413-18.297A11.81 11.81 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.88 11.88 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.8 11.8 0 0 0-3.48-8.413Z"/>
            </svg>
            Chat Whatsapp
        </a>

        <script>
        (function () {
            var floatBanner = document.getElementById('sz-mobile-cta-float');
            var waFloat = document.getElementById('sz-custom-wa-float');
            var storageKey = 'sz_mobile_cta_dismissed';

            // Cek apakah user sudah pernah menutup banner di sesi ini
            var isBannerDismissed = false;
            try {
                if (sessionStorage.getItem(storageKey) === '1') {
                    isBannerDismissed = true;
                }
            } catch (e) {}

            // Skenario 1: Jika sudah pernah di-close, langsung munculkan WA saja
            if (isBannerDismissed) {
                if (waFloat) waFloat.classList.add('is-visible');
                return; // Script berhenti di sini
            }

            // Skenario 2: Jika belum pernah di-close, munculkan Banner Promo
            if (floatBanner) {
                floatBanner.classList.add('is-visible');

                var closeBtn = floatBanner.querySelector('.sz-mobile-cta-close');
                if (closeBtn) {
                    closeBtn.addEventListener('click', function () {
                        // Sembunyikan banner
                        floatBanner.classList.remove('is-visible');
                        floatBanner.classList.add('is-dismissed');

                        try {
                            sessionStorage.setItem(storageKey, '1');
                        } catch (e) {}

                        // Munculkan tombol WA dengan jeda sedikit agar efeknya mulus
                        setTimeout(function() {
                            if (waFloat) waFloat.classList.add('is-visible');
                        }, 200);
                    });
                }
            }
        })();
        </script>

    </main>

    <?php if ($related_query && $related_query->have_posts()) : ?>
        <section class="sz-related-section" aria-labelledby="sz-related-title">
            <div class="sz-related-container">

                <h2 id="sz-related-title">Direkomendasikan Untuk Anda</h2>

                <div class="sz-article-grid">
                    <?php
                    while ($related_query->have_posts()) :
                        $related_query->the_post();

                        $rel_id         = get_the_ID();
                        $rel_thumb_id   = get_post_thumbnail_id($rel_id);
                        $rel_thumb_data = $rel_thumb_id ? wp_get_attachment_image_src($rel_thumb_id, 'medium_large') : false;

                        $rel_thumb = $rel_thumb_data ? $rel_thumb_data[0] : 'https://sozoskinclinic.com/wp-content/uploads/2026/04/sozologo.avif';
                        $rel_w     = $rel_thumb_data ? (int) $rel_thumb_data[1] : 600;
                        $rel_h     = $rel_thumb_data ? (int) $rel_thumb_data[2] : 400;
                        ?>

                        <article class="sz-card">
                            <a href="<?php the_permalink(); ?>" class="sz-card-img" aria-label="Baca artikel: <?php echo esc_attr(get_the_title()); ?>" tabindex="-1">
                                <?php if ($rel_thumb_id) : ?>
                                    <?php
                                    echo wp_get_attachment_image(
                                        $rel_thumb_id,
                                        'medium_large',
                                        false,
                                        array(
                                            'alt'      => get_the_title(),
                                            'loading'  => 'lazy',
                                            'decoding' => 'async',
                                            'sizes'    => '(min-width: 920px) 300px, (min-width: 768px) 50vw, 100vw',
                                        )
                                    );
                                    ?>
                                <?php else : ?>
                                    <img src="<?php echo esc_url($rel_thumb); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" width="<?php echo esc_attr($rel_w); ?>" height="<?php echo esc_attr($rel_h); ?>" loading="lazy" decoding="async">
                                <?php endif; ?>
                            </a>

                            <div class="sz-card-body">
                                <h3>
                                    <a href="<?php the_permalink(); ?>">
                                        <?php the_title(); ?>
                                    </a>
                                </h3>

                                <div class="sz-meta">
                                    <time datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                                        <?php echo esc_html(get_the_date('j F Y')); ?>
                                    </time>
                                </div>
                            </div>
                        </article>

                        <?php
                    endwhile;
                    ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php
    wp_reset_postdata();

    /**
     * ======================================================
     * JSON-LD Schema (versi simple — Article saja)
     * Satu @graph per artikel:
     * - Article        : headline, image, dates, author minimal,
     *                    publisher ref
     * - BreadcrumbList : Blog > Kategori > Judul
     * Tanpa reviewedBy (spec: properti itu hanya valid di WebPage).
     * Info reviewer tetap tampil di byline + trust panel UI.
     * Sengaja dibuang: MedicalWebPage wrapper, Person node detail
     * (hasCredential/alumniOf/knowsAbout/image), articleBody,
     * wordCount, timeRequired, keywords, citation, about medis.
     * #organization dan #website hanya direferensikan lewat @id,
     * deklarasi penuh tetap di homepage (lihat AGENTS.md §2).
     * ======================================================
     */
    $breadcrumb_items = array(
        array(
            '@type'    => 'ListItem',
            'position' => 1,
            'name'     => 'Blog',
            'item'     => home_url('/blog/'),
        ),
        array(
            '@type'    => 'ListItem',
            'position' => 2,
            'name'     => $cat_name,
            'item'     => esc_url_raw($cat_link),
        ),
        array(
            '@type'    => 'ListItem',
            'position' => 3,
            'name'     => $post_title,
            'item'     => esc_url_raw($permalink),
        ),
    );

    $blog_posting = array(
        '@type'            => 'Article',
        '@id'              => trailingslashit($permalink) . '#article',
        'mainEntityOfPage' => esc_url_raw($permalink),
        'url'              => esc_url_raw($permalink),
        'headline'         => $post_title,
        'description'      => $description,
        'inLanguage'       => 'id-ID',
        'isPartOf'         => array(
            '@id' => home_url('/#website'),
        ),
        'datePublished'    => $date_published_iso,
        'dateModified'     => $date_modified_iso,
        'articleSection'   => $cat_name,
        'author'           => array(
            '@type' => 'Person',
            'name'  => $author_name,
            'url'   => esc_url_raw($author_url),
        ),
        'publisher'        => array(
            '@id' => home_url('/#organization'),
        ),
    );

    if (!empty($thumb_url)) {
        $blog_posting['image'] = esc_url_raw($thumb_url);
    }

    $schema_graph = array(
        $blog_posting,
        array(
            '@type'           => 'BreadcrumbList',
            '@id'             => trailingslashit($permalink) . '#breadcrumb',
            'itemListElement' => $breadcrumb_items,
        ),
    );

    $schema = array(
        '@context' => 'https://schema.org',
        '@graph'   => $schema_graph,
    );
    ?>

    <script type="application/ld+json">
        <?php echo wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT); ?>
    </script>

    <?php

    return ob_get_clean();
}