/**
 * Snippet Name : SOZO Branch Page Template
 * Description  : Shortcode [sozo_branch] untuk halaman detail cabang (81 cabang). Data dari post meta,
 *                markup + CSS + JS mengikuti reference-arteri-v3.html (Sozo Design System V1.2).
 *                Schema MedicalClinic (+ FAQPage) disuntik ke graph Yoast lewat wpseo_schema_graph.
 * Run          : WPCode Lite / Code Snippets, tipe PHP, "Run everywhere".
 * Cache        : setelah pasang, purge LiteSpeed + regenerate CSS Elementor.
 * Font         : Poppins tidak dimuat default. Aktifkan lewat define('SZ_BRANCH_LOAD_FONT', true) di wp-config.php.
 */

if (!defined('ABSPATH')) exit;

if (!defined('SZ_BRANCH_VERSION')) {
    define('SZ_BRANCH_VERSION', '1.0.0');
}
if (!defined('SZ_BRANCH_LOAD_FONT')) {
    define('SZ_BRANCH_LOAD_FONT', false);
}

// ============ 1. DATA MODEL: post meta di 'page' ============
// Format: key => array(tipe REST, jenis sanitizer)
if (!function_exists('sz_branch_meta_defs')) {
    function sz_branch_meta_defs() {
        return array(
            'sz_branch_name'   => array('string', 'text'),
            'sz_lead'          => array('string', 'textarea'),
            'sz_rating'        => array('number', 'rating'),
            'sz_review_count'  => array('integer', 'int'),
            'sz_gbp_url'       => array('string', 'url'),
            'sz_maps_query'    => array('string', 'text'),
            'sz_hours'         => array('string', 'json_hours'),
            'sz_address'       => array('string', 'text'),
            'sz_street'        => array('string', 'text'),
            'sz_locality'      => array('string', 'text'),
            'sz_city'          => array('string', 'text'),
            'sz_region'        => array('string', 'text'),
            'sz_postal_code'   => array('string', 'text'),
            'sz_lat'           => array('number', 'lat'),
            'sz_lng'           => array('number', 'lng'),
            'sz_phone'         => array('string', 'phone'),
            'sz_wa_src'        => array('string', 'slug'),
            'sz_gallery'       => array('string', 'json_gallery'),
            'sz_access'        => array('string', 'rich'),
            'sz_treatments'    => array('string', 'json_treatments'),
            'sz_faq'           => array('string', 'json_faq'),
            'sz_nearby'        => array('string', 'json_ids'),
        );
    }
}

if (!function_exists('sz_branch_time_to_minutes')) {
    // "09:00" -> 540. Null kalau format salah.
    function sz_branch_time_to_minutes($time) {
        if (!preg_match('/^([01]?\d|2[0-3]):([0-5]\d)$/', trim((string) $time), $m)) {
            return null;
        }
        return ((int) $m[1]) * 60 + (int) $m[2];
    }
}

if (!function_exists('sz_branch_minutes_to_time')) {
    function sz_branch_minutes_to_time($mins) {
        $mins = (int) $mins;
        return str_pad((string) floor($mins / 60), 2, '0', STR_PAD_LEFT) . ':' . str_pad((string) ($mins % 60), 2, '0', STR_PAD_LEFT);
    }
}

if (!function_exists('sz_branch_decode_json')) {
    // Terima string JSON atau array, kembalikan array atau null.
    function sz_branch_decode_json($value) {
        if (is_array($value)) {
            return $value;
        }
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        $data = json_decode($value, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            return null;
        }
        return $data;
    }
}

if (!function_exists('sz_branch_encode_json')) {
    function sz_branch_encode_json($data) {
        return wp_json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}

if (!function_exists('sz_branch_clean_hours')) {
    // 7 hari wajib lengkap (day 0-6, unik). Hari tutup: {"day":0,"closed":true}.
    function sz_branch_clean_hours($value) {
        $data = sz_branch_decode_json($value);
        if (!$data) {
            return '';
        }
        $out = array();
        $seen = array();
        foreach ($data as $row) {
            if (!is_array($row) || !isset($row['day']) || !is_numeric($row['day'])) {
                return '';
            }
            $day = (int) $row['day'];
            if ($day < 0 || $day > 6 || isset($seen[$day])) {
                return '';
            }
            $seen[$day] = true;
            if (!empty($row['closed'])) {
                $out[] = array('day' => $day, 'closed' => true);
                continue;
            }
            $open = sz_branch_time_to_minutes(isset($row['open']) ? $row['open'] : '');
            $close = sz_branch_time_to_minutes(isset($row['close']) ? $row['close'] : '');
            if ($open === null || $close === null || $close <= $open) {
                return '';
            }
            $out[] = array('day' => $day, 'open' => sz_branch_minutes_to_time($open), 'close' => sz_branch_minutes_to_time($close));
        }
        if (count($out) !== 7) {
            return '';
        }
        return sz_branch_encode_json($out);
    }
}

if (!function_exists('sz_branch_clean_gallery')) {
    // [{"id":123}] atau [{"url":"...","alt":"...","label":"..."}], maksimal 12 foto.
    function sz_branch_clean_gallery($value) {
        $data = sz_branch_decode_json($value);
        if (!$data) {
            return '';
        }
        $out = array();
        foreach ($data as $row) {
            if (!is_array($row)) {
                continue;
            }
            $label = isset($row['label']) ? sanitize_text_field((string) $row['label']) : '';
            if (!empty($row['id']) && (int) $row['id'] > 0) {
                $item = array('id' => (int) $row['id']);
            } elseif (!empty($row['url'])) {
                $url = esc_url_raw(trim((string) $row['url']));
                if ($url === '') {
                    continue;
                }
                $item = array('url' => $url);
                if (!empty($row['alt'])) {
                    $item['alt'] = sanitize_text_field((string) $row['alt']);
                }
            } else {
                continue;
            }
            if ($label !== '') {
                $item['label'] = $label;
            }
            $out[] = $item;
            if (count($out) >= 12) {
                break;
            }
        }
        return $out ? sz_branch_encode_json($out) : '';
    }
}

if (!function_exists('sz_branch_clean_treatments')) {
    function sz_branch_clean_treatments($value) {
        $data = sz_branch_decode_json($value);
        if (!$data) {
            return '';
        }
        $out = array();
        foreach ($data as $row) {
            if (!is_array($row) || empty($row['name'])) {
                continue;
            }
            $item = array('name' => sanitize_text_field((string) $row['name']));
            if ($item['name'] === '') {
                continue;
            }
            if (!empty($row['url'])) {
                $url = esc_url_raw(trim((string) $row['url']));
                if ($url !== '') {
                    $item['url'] = $url;
                }
            }
            $out[] = $item;
        }
        return $out ? sz_branch_encode_json($out) : '';
    }
}

if (!function_exists('sz_branch_clean_faq')) {
    // Teks polos satu paragraf, supaya teks visible dan teks schema identik.
    function sz_branch_clean_faq($value) {
        $data = sz_branch_decode_json($value);
        if (!$data) {
            return '';
        }
        $out = array();
        foreach ($data as $row) {
            if (!is_array($row) || empty($row['q']) || empty($row['a'])) {
                continue;
            }
            $q = sanitize_text_field((string) $row['q']);
            $a = sanitize_text_field((string) $row['a']);
            if ($q === '' || $a === '') {
                continue;
            }
            $out[] = array('q' => $q, 'a' => $a);
            if (count($out) >= 10) {
                break;
            }
        }
        return $out ? sz_branch_encode_json($out) : '';
    }
}

if (!function_exists('sz_branch_clean_ids')) {
    function sz_branch_clean_ids($value) {
        $data = sz_branch_decode_json($value);
        if (!$data) {
            return '';
        }
        $out = array();
        foreach ($data as $id) {
            $id = is_numeric($id) ? (int) $id : 0;
            if ($id > 0 && !in_array($id, $out, true)) {
                $out[] = $id;
            }
            if (count($out) >= 6) {
                break;
            }
        }
        return $out ? sz_branch_encode_json($out) : '';
    }
}

if (!function_exists('sz_branch_sanitize')) {
    function sz_branch_sanitize($value, $kind) {
        switch ($kind) {
            case 'text':
                return sanitize_text_field((string) $value);
            case 'textarea':
                return sanitize_textarea_field((string) $value);
            case 'url':
                return esc_url_raw(trim((string) $value));
            case 'rich':
                return wp_kses_post((string) $value);
            case 'int':
                return (is_numeric($value) && (int) $value > 0) ? (int) $value : '';
            case 'rating':
                return (is_numeric($value) && (float) $value > 0 && (float) $value <= 5) ? round((float) $value, 1) : '';
            case 'lat':
                return (is_numeric($value) && abs((float) $value) <= 90) ? (float) $value : '';
            case 'lng':
                return (is_numeric($value) && abs((float) $value) <= 180) ? (float) $value : '';
            case 'phone':
                return preg_replace('/\D+/', '', (string) $value);
            case 'slug':
                return preg_replace('/[^A-Za-z0-9_\-]/', '', (string) $value);
            case 'json_hours':
                return sz_branch_clean_hours($value);
            case 'json_gallery':
                return sz_branch_clean_gallery($value);
            case 'json_treatments':
                return sz_branch_clean_treatments($value);
            case 'json_faq':
                return sz_branch_clean_faq($value);
            case 'json_ids':
                return sz_branch_clean_ids($value);
        }
        return '';
    }
}

if (!function_exists('sz_branch_register_meta')) {
    function sz_branch_register_meta() {
        foreach (sz_branch_meta_defs() as $key => $def) {
            $kind = $def[1];
            register_post_meta('page', $key, array(
                'type'              => $def[0],
                'single'            => true,
                'show_in_rest'      => true,
                'sanitize_callback' => function ($value) use ($kind) {
                    return sz_branch_sanitize($value, $kind);
                },
                'auth_callback'     => function ($allowed, $meta_key, $post_id) {
                    return current_user_can('edit_post', $post_id);
                },
            ));
        }
    }
    // WPCode kadang menjalankan snippet setelah init sudah lewat.
    if (did_action('init')) {
        sz_branch_register_meta();
    } else {
        add_action('init', 'sz_branch_register_meta');
    }
}

// ============ 2. DATA LOADER ============
// Satu kali baca per request, dipakai shortcode, assets, dan schema.
if (!function_exists('sz_branch_data')) {
    function sz_branch_data($post_id) {
        static $cache = array();
        $post_id = (int) $post_id;
        if ($post_id <= 0) {
            return null;
        }
        if (array_key_exists($post_id, $cache)) {
            return $cache[$post_id];
        }
        $cache[$post_id] = null;
        if (get_post_type($post_id) !== 'page') {
            return null;
        }
        $name = trim((string) get_post_meta($post_id, 'sz_branch_name', true));
        if ($name === '') {
            return null;
        }

        $get = function ($key) use ($post_id) {
            return trim((string) get_post_meta($post_id, $key, true));
        };

        $d = array(
            'id'          => $post_id,
            'name'        => $name,
            'lead'        => $get('sz_lead'),
            'rating'      => (float) $get('sz_rating'),
            'reviews'     => (int) $get('sz_review_count'),
            'gbp_url'     => $get('sz_gbp_url'),
            'maps_query'  => $get('sz_maps_query'),
            'address'     => $get('sz_address'),
            'street'      => $get('sz_street'),
            'locality'    => $get('sz_locality'),
            'city'        => $get('sz_city'),
            'region'      => $get('sz_region'),
            'postal'      => $get('sz_postal_code'),
            'lat'         => $get('sz_lat'),
            'lng'         => $get('sz_lng'),
            'phone'       => preg_replace('/\D+/', '', $get('sz_phone')),
            'wa_src'      => $get('sz_wa_src'),
            'access'      => $get('sz_access'),
            'hours'       => array(),
            'gallery'     => array(),
            'treatments'  => array(),
            'faq'         => array(),
            'nearby'      => array(),
        );
        if ($d['maps_query'] === '') {
            $d['maps_query'] = 'Sozo Skin Clinic - ' . $name;
        }
        if ($d['wa_src'] === '') {
            $d['wa_src'] = 'ORG-LOK';
        }

        // Hours: index per hari (0 = Minggu). Hanya diterima kalau 7 hari lengkap.
        $hours = sz_branch_decode_json($get('sz_hours'));
        if ($hours && count($hours) === 7) {
            foreach ($hours as $row) {
                if (is_array($row) && isset($row['day'])) {
                    $d['hours'][(int) $row['day']] = $row;
                }
            }
            if (count($d['hours']) !== 7) {
                $d['hours'] = array();
            }
        }
        $gallery = sz_branch_decode_json($get('sz_gallery'));
        $d['gallery'] = $gallery ? $gallery : array();
        $treatments = sz_branch_decode_json($get('sz_treatments'));
        $d['treatments'] = $treatments ? $treatments : array();
        $faq = sz_branch_decode_json($get('sz_faq'));
        $d['faq'] = $faq ? $faq : array();
        $nearby = sz_branch_decode_json($get('sz_nearby'));
        $d['nearby'] = $nearby ? $nearby : array();

        $cache[$post_id] = $d;
        return $d;
    }
}

if (!function_exists('sz_branch_gallery_items')) {
    // Resolve galeri ke daftar siap render: src, thumb, alt, label, width, height.
    function sz_branch_gallery_items($d) {
        $items = array();
        foreach ($d['gallery'] as $row) {
            if (!is_array($row)) {
                continue;
            }
            $label = isset($row['label']) ? (string) $row['label'] : '';
            $src = '';
            $thumb = '';
            $alt = '';
            $w = 1600;
            $h = 900;
            if (!empty($row['id'])) {
                $aid = (int) $row['id'];
                $full = wp_get_attachment_image_src($aid, 'full');
                if (!$full || empty($full[0])) {
                    continue;
                }
                $src = $full[0];
                $w = !empty($full[1]) ? (int) $full[1] : $w;
                $h = !empty($full[2]) ? (int) $full[2] : $h;
                $thumb = (string) wp_get_attachment_image_url($aid, 'medium');
                $alt = trim((string) get_post_meta($aid, '_wp_attachment_image_alt', true));
            } elseif (!empty($row['url'])) {
                $src = (string) $row['url'];
                $alt = isset($row['alt']) ? (string) $row['alt'] : '';
            }
            if ($src === '') {
                continue;
            }
            if ($thumb === '') {
                $thumb = $src;
            }
            if ($alt === '') {
                $alt = ($label !== '' ? ucfirst($label) . ' ' : '') . 'Sozo Skin Clinic ' . $d['name'];
            }
            $items[] = array('src' => $src, 'thumb' => $thumb, 'alt' => $alt, 'label' => $label, 'w' => $w, 'h' => $h);
        }
        return $items;
    }
}

// ============ 3. HELPER MARKUP ============
if (!function_exists('sz_branch_breadcrumb_items')) {
    // Beranda > Lokasi > semua ancestor (terluar dulu). Item terakhir ditambahkan oleh pemanggil.
    function sz_branch_breadcrumb_items($post_id) {
        $lokasi = home_url('/lokasi/');
        $items = array(
            array('label' => 'Beranda', 'url' => home_url('/')),
            array('label' => 'Lokasi', 'url' => $lokasi),
        );
        foreach (array_reverse(get_post_ancestors($post_id)) as $aid) {
            if (get_post_status($aid) !== 'publish') {
                continue;
            }
            $url = get_permalink($aid);
            $title = trim(wp_strip_all_tags(get_the_title($aid)));
            // Hindari "Lokasi" ganda kalau /lokasi/ sendiri adalah ancestor.
            if (!$url || $title === '' || untrailingslashit($url) === untrailingslashit($lokasi)) {
                continue;
            }
            $items[] = array('label' => $title, 'url' => $url);
        }
        return $items;
    }
}

if (!function_exists('sz_branch_nearby_items')) {
    function sz_branch_nearby_items($d) {
        $post_id = $d['id'];
        $ids = array();
        foreach ($d['nearby'] as $id) {
            $id = (int) $id;
            if ($id > 0 && $id !== $post_id) {
                $ids[] = $id;
            }
        }
        if (!$ids) {
            $parent = (int) wp_get_post_parent_id($post_id);
            if ($parent > 0) {
                $found = get_posts(array(
                    'post_type'      => 'page',
                    'post_status'    => 'publish',
                    'post_parent'    => $parent,
                    'post__not_in'   => array($post_id),
                    'posts_per_page' => 6,
                    'orderby'        => 'title',
                    'order'          => 'ASC',
                    'fields'         => 'ids',
                    'no_found_rows'  => true,
                    'meta_query'     => array(
                        array('key' => 'sz_branch_name', 'value' => '', 'compare' => '!='),
                    ),
                ));
                $ids = array_map('intval', $found);
            }
        }
        $items = array();
        foreach (array_slice($ids, 0, 6) as $id) {
            if (get_post_status($id) !== 'publish' || get_post_type($id) !== 'page') {
                continue;
            }
            $name = trim((string) get_post_meta($id, 'sz_branch_name', true));
            $url = get_permalink($id);
            if ($name === '' || !$url) {
                continue;
            }
            $items[] = array(
                'name' => $name,
                'url'  => $url,
                'sub'  => trim((string) get_post_meta($id, 'sz_locality', true)),
            );
        }
        return $items;
    }
}

if (!function_exists('sz_branch_icon')) {
    function sz_branch_icon($name) {
        $icons = array(
            'star'  => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2.5l2.9 6.1 6.6.8-4.9 4.6 1.3 6.5L12 17.3l-5.9 3.2 1.3-6.5L2.5 9.4l6.6-.8z"/></svg>',
            'copy'  => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 1H6a2 2 0 0 0-2 2v12h2V3h10V1zm3 4H10a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h9a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2zm0 16H10V7h9v14z"/></svg>',
            'wa'    => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M.057 24l1.687-6.163a11.867 11.867 0 0 1-1.587-5.946C.16 5.335 5.495 0 12.05 0a11.817 11.817 0 0 1 8.413 3.488 11.824 11.824 0 0 1 3.48 8.414c-.003 6.557-5.338 11.892-11.893 11.892a11.9 11.9 0 0 1-5.688-1.448L.057 24zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>',
            'route' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 11l19-9-9 19-2-8-8-2z"/></svg>',
            'prev'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>',
            'next'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>',
        );
        return isset($icons[$name]) ? $icons[$name] : '';
    }
}

if (!function_exists('sz_branch_faq_items')) {
    // Dipakai render dan schema, supaya keduanya membaca daftar yang sama persis.
    function sz_branch_faq_items($d) {
        $out = array();
        foreach ($d['faq'] as $row) {
            if (is_array($row) && !empty($row['q']) && !empty($row['a'])) {
                $out[] = array('q' => (string) $row['q'], 'a' => (string) $row['a']);
            }
        }
        return $out;
    }
}

// ============ 4. RENDER: shortcode [sozo_branch] ============
if (!function_exists('sz_branch_markup')) {
    function sz_branch_markup($d) {
        $name = $d['name'];
        $o = array();
        $gallery = sz_branch_gallery_items($d);
        $total = count($gallery);
        $body_class = 'sz-branch__body' . ($total ? '' : ' sz-branch__body--solo');

        $o[] = '<div class="sz-branch" data-sz-branch>';
        $o[] = '<div class="sz-container">';

        // Breadcrumb
        $o[] = '<nav class="sz-breadcrumb" aria-label="Breadcrumb"><ol class="sz-breadcrumb__list">';
        foreach (sz_branch_breadcrumb_items($d['id']) as $crumb) {
            $o[] = '<li><a href="' . esc_url($crumb['url']) . '">' . esc_html($crumb['label']) . '</a></li>';
        }
        $o[] = '<li><span class="sz-breadcrumb__current" aria-current="page">' . esc_html($name) . '</span></li>';
        $o[] = '</ol></nav>';

        $o[] = '<div class="' . esc_attr($body_class) . '">';

        // 1. Header
        $o[] = '<header class="sz-branch__head">';
        $o[] = '<h1 class="sz-branch__title">' . esc_html('Sozo Skin Clinic - ' . $name) . '</h1>';
        if ($d['rating'] > 0 || $d['hours']) {
            $o[] = '<div class="sz-branch__meta">';
            if ($d['rating'] > 0) {
                $rate = '<span class="sz-rating">' . sz_branch_icon('star') . '<strong>' . esc_html(number_format($d['rating'], 1, '.', '')) . '</strong>';
                if ($d['reviews'] > 0) {
                    $reviews_url = $d['gbp_url'] !== '' ? $d['gbp_url'] : 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($d['maps_query']);
                    $rate .= '<a href="' . esc_url($reviews_url) . '" target="_blank" rel="noopener">' . esc_html(number_format($d['reviews'], 0, ',', '.') . ' ulasan Google') . '</a>';
                }
                $o[] = $rate . '</span>';
            }
            if ($d['hours']) {
                $o[] = '<p class="sz-hours__status" data-sz-status hidden><span class="sz-hours__dot" aria-hidden="true"></span><span data-sz-status-text></span></p>';
            }
            $o[] = '</div>';
        }
        if ($d['lead'] !== '') {
            $o[] = '<p class="sz-branch__lead">' . esc_html($d['lead']) . '</p>';
        }
        $o[] = '</header>';

        // 2. Info cabang
        $o[] = '<section class="sz-panel" aria-label="Info klinik">';
        if ($d['hours']) {
            $day_names = array(0 => 'Minggu', 1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu');
            $o[] = '<div class="sz-panel__group sz-hours">';
            $o[] = '<div class="sz-panel__head"><h2 class="sz-panel__label">Jam Operasional (WIB)</h2></div>';
            $o[] = '<dl class="sz-hours__list">';
            foreach (array(1, 2, 3, 4, 5, 6, 0) as $day) {
                $row = $d['hours'][$day];
                $open_m = empty($row['closed']) ? sz_branch_time_to_minutes(isset($row['open']) ? $row['open'] : '') : null;
                $close_m = empty($row['closed']) ? sz_branch_time_to_minutes(isset($row['close']) ? $row['close'] : '') : null;
                if ($open_m === null || $close_m === null) {
                    $o[] = '<div class="sz-hours__item" data-day="' . $day . '"><dt>' . esc_html($day_names[$day]) . '</dt><dd>Tutup</dd></div>';
                    continue;
                }
                $open_t = sz_branch_minutes_to_time($open_m);
                $close_t = sz_branch_minutes_to_time($close_m);
                $o[] = '<div class="sz-hours__item" data-day="' . $day . '" data-open="' . (int) $open_m . '" data-close="' . (int) $close_m . '">'
                    . '<dt>' . esc_html($day_names[$day]) . '</dt>'
                    . '<dd><time datetime="' . esc_attr($open_t) . '">' . esc_html($open_t) . '</time> - <time datetime="' . esc_attr($close_t) . '">' . esc_html($close_t) . '</time></dd>'
                    . '</div>';
            }
            $o[] = '</dl></div>';
        }

        $o[] = '<div class="sz-panel__group">';
        if ($d['address'] !== '') {
            $o[] = '<div class="sz-panel__head"><h2 class="sz-panel__label">Alamat</h2>'
                . '<button type="button" class="sz-textbtn" data-sz-copy>' . sz_branch_icon('copy') . '<span data-sz-copy-label>Salin alamat</span></button></div>';
            $o[] = '<p class="sz-panel__address" data-sz-address>' . esc_html($d['address']) . '</p>';
            $o[] = '<span class="sz-visually-hidden" aria-live="polite" data-sz-copy-status></span>';
        }
        $o[] = '<div class="sz-panel__actions">';
        if ($d['phone'] !== '') {
            $wa_text = 'Halo, saya mau booking treatment sozo di Sozo Skin Clinic ' . $name . ' [src:' . $d['wa_src'] . ']';
            $wa_url = 'https://api.whatsapp.com/send?phone=' . rawurlencode($d['phone']) . '&text=' . rawurlencode($wa_text);
            $o[] = '<a href="' . esc_url($wa_url) . '" target="_blank" rel="noopener" class="sz-btn sz-btn--primary sozo-master-cta">' . sz_branch_icon('wa') . '<span>Buat Jadwal</span></a>';
        }
        $dir_url = 'https://www.google.com/maps/dir/?api=1&destination=' . rawurlencode($d['maps_query']);
        $o[] = '<a href="' . esc_url($dir_url) . '" target="_blank" rel="noopener" class="sz-btn sz-btn--outline">' . sz_branch_icon('route') . '<span>Petunjuk Arah</span></a>';
        $o[] = '</div></div>';
        $o[] = '</section>';

        // 3. Galeri
        if ($total) {
            $single = ($total === 1);
            $o[] = '<section class="sz-gallery' . ($single ? ' sz-gallery--single' : '') . '" aria-label="' . esc_attr('Galeri foto Sozo Skin Clinic ' . $name) . '">';
            $o[] = '<div class="sz-gallery__stage">';
            $first = $gallery[0];
            $o[] = '<img src="' . esc_url($first['src']) . '" alt="' . esc_attr($first['alt']) . '" width="' . (int) $first['w'] . '" height="' . (int) $first['h'] . '" loading="eager" fetchpriority="high" decoding="async" draggable="false">';
            if (!$single) {
                $o[] = '<button type="button" class="sz-gallery__nav sz-gallery__nav--prev" aria-label="Foto sebelumnya">' . sz_branch_icon('prev') . '</button>';
                $o[] = '<button type="button" class="sz-gallery__nav sz-gallery__nav--next" aria-label="Foto selanjutnya">' . sz_branch_icon('next') . '</button>';
                $o[] = '<span class="sz-gallery__count" aria-live="polite">1 / ' . (int) $total . '</span>';
            }
            $o[] = '</div>';
            if (!$single) {
                $o[] = '<div class="sz-gallery__thumbs">';
                foreach ($gallery as $i => $img) {
                    $aria = 'Foto ' . ($i + 1) . ($img['label'] !== '' ? ': ' . $img['label'] : '');
                    $o[] = '<button type="button" class="sz-gallery__thumb"' . ($i === 0 ? ' aria-current="true"' : '')
                        . ' data-full="' . esc_url($img['src']) . '" data-alt="' . esc_attr($img['alt']) . '" aria-label="' . esc_attr($aria) . '">'
                        . '<img src="' . esc_url($img['thumb']) . '" alt="" width="320" height="180" loading="lazy" decoding="async"></button>';
                }
                $o[] = '</div>';
            }
            $o[] = '</section>';
        }

        $o[] = '</div>'; // .sz-branch__body

        // 4. Seksi opsional: tampil hanya kalau datanya ada
        if ($d['access'] !== '') {
            $o[] = '<section class="sz-section" aria-label="Cara ke lokasi"><h2 class="sz-section__title">Cara ke Lokasi</h2>'
                . '<div class="sz-section__body">' . wpautop(wp_kses_post($d['access'])) . '</div></section>';
        }

        $chips = array();
        foreach ($d['treatments'] as $t) {
            if (!is_array($t) || empty($t['name'])) {
                continue;
            }
            if (!empty($t['url'])) {
                $chips[] = '<li><a class="sz-chip" href="' . esc_url($t['url']) . '">' . esc_html($t['name']) . '</a></li>';
            } else {
                $chips[] = '<li><span class="sz-chip">' . esc_html($t['name']) . '</span></li>';
            }
        }
        if ($chips) {
            $o[] = '<section class="sz-section" aria-label="Treatment di cabang ini"><h2 class="sz-section__title">Treatment di Cabang Ini</h2>'
                . '<ul class="sz-chips">' . implode('', $chips) . '</ul></section>';
        }

        $faq_items = sz_branch_faq_items($d);
        if ($faq_items) {
            $o[] = '<section class="sz-section" aria-label="Pertanyaan yang sering diajukan"><h2 class="sz-section__title">Pertanyaan yang Sering Diajukan</h2><div class="sz-faq__list">';
            foreach ($faq_items as $f) {
                $o[] = '<details class="sz-faq__item"><summary>' . esc_html($f['q']) . '</summary><div class="sz-faq__answer"><p>' . esc_html($f['a']) . '</p></div></details>';
            }
            $o[] = '</div></section>';
        }

        $nearby = sz_branch_nearby_items($d);
        $parent_id = (int) wp_get_post_parent_id($d['id']);
        $parent_link = '';
        if ($parent_id > 0 && get_post_status($parent_id) === 'publish') {
            $p_title = trim(wp_strip_all_tags(get_the_title($parent_id)));
            $p_url = get_permalink($parent_id);
            if ($p_title !== '' && $p_url) {
                $parent_link = '<p class="sz-section__more"><a href="' . esc_url($p_url) . '">' . esc_html('Lihat semua cabang di ' . $p_title) . '</a></p>';
            }
        }
        if ($nearby || $parent_link) {
            $o[] = '<section class="sz-section" aria-label="Cabang terdekat"><h2 class="sz-section__title">Cabang Terdekat</h2>';
            if ($nearby) {
                $o[] = '<ul class="sz-nearby">';
                foreach ($nearby as $n) {
                    $o[] = '<li><a class="sz-nearby__link" href="' . esc_url($n['url']) . '"><span class="sz-nearby__name">' . esc_html('Sozo Skin Clinic - ' . $n['name']) . '</span>'
                        . ($n['sub'] !== '' ? '<span class="sz-nearby__sub">' . esc_html($n['sub']) . '</span>' : '') . '</a></li>';
                }
                $o[] = '</ul>';
            }
            $o[] = $parent_link . '</section>';
        }

        $o[] = '</div></div>'; // .sz-container, .sz-branch
        return implode("\n", $o);
    }
}

if (!function_exists('sz_branch_render')) {
    function sz_branch_render($atts = array()) {
        try {
            $atts = shortcode_atts(array('id' => 0), $atts, 'sozo_branch');
            $explicit = (int) $atts['id'];
            $post_id = $explicit > 0 ? $explicit : (int) get_queried_object_id();
            // Atribut id dipakai untuk testing. Halaman non-publish hanya terlihat oleh editor-nya.
            if ($explicit > 0 && get_post_status($explicit) !== 'publish' && !current_user_can('edit_post', $explicit)) {
                return '';
            }
            $d = sz_branch_data($post_id);
            if (!$d) {
                return '';
            }
            sz_branch_enqueue();
            return sz_branch_markup($d);
        } catch (Throwable $e) {
            return '';
        }
    }
    add_shortcode('sozo_branch', 'sz_branch_render');
}

// ============ 5. ASSETS: CSS + JS inline, hanya di halaman cabang ============
if (!function_exists('sz_branch_css')) {
    function sz_branch_css() {
        return <<<'SZCSS'
  .sz-branch {
    /* Token: salinan design-system/tokens.css yang dipakai halaman ini */
    --color-brand-primary: #1A2080 !important;
    --color-brand-secondary: #D9C7B1 !important;
    --color-brand-almond: #E9DFD5 !important;
    --color-brand-isabelline: #DFDBD4 !important;
    --color-white: #FFFFFF !important;
    --color-promo-yellow: #FFC200 !important;
    --color-text-primary: #171715 !important;
    --color-text-secondary: #41413E !important;
    --color-text-muted: #777771 !important;
    --color-text-on-primary: #FFFFFF !important;
    --color-border: #DFDBD4 !important;
    --color-border-strong: #C8C3BA !important;
    --color-primary-hover: #151A6D !important;
    --color-primary-subtle: rgba(26, 32, 128, 0.05) !important;
    --color-success: #267A57 !important;
    --color-error: #B64242 !important;
    --font-family-primary: "Poppins", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
    --font-weight-regular: 400 !important;
    --font-weight-medium: 500 !important;
    --font-weight-semibold: 600 !important;
    --font-weight-bold: 700 !important;
    --text-h4: 24px !important;
    --text-h5: 20px !important;
    --text-body: 16px !important;
    --text-body-sm: 14px !important;
    --text-caption: 12px !important;
    --text-label: 12px !important;
    --leading-h4: 1.30 !important;
    --leading-h5: 1.35 !important;
    --leading-body: 1.60 !important;
    --leading-body-sm: 1.55 !important;
    --leading-label: 1.40 !important;
    --tracking-eyebrow: 0.12em !important;
    --measure: 65ch !important;
    --space-1: 4px !important;
    --space-2: 8px !important;
    --space-3: 12px !important;
    --space-4: 16px !important;
    --space-5: 24px !important;
    --space-6: 32px !important;
    --space-7: 40px !important;
    --space-8: 48px !important;
    --space-9: 64px !important;
    --container-max: 1280px !important;
    --container-pad: 32px !important;
    --grid-gap: 24px !important;
    --section-y: 80px !important;
    --radius-sm: 6px !important;
    --radius-md: 10px !important;
    --radius-lg: 16px !important;
    --radius-full: 999px !important;
    --shadow-sm: 0 2px 8px rgba(23, 23, 21, 0.04) !important;
    --shadow-md: 0 8px 24px rgba(23, 23, 21, 0.07) !important;
    --focus-ring: 2px solid var(--color-brand-primary) !important;
    --focus-offset: 3px !important;
    --duration-fast: 180ms !important;
    --duration-media: 400ms !important;
    --ease-standard: ease !important;
    --pulse-ring: rgba(38, 122, 87, 0.35) !important; /* lokal: cincin pulse status buka, turunan --color-success */

    width: 100% !important;
    margin: 0 !important;
    padding: var(--space-7) 0 var(--section-y) !important;
    background: var(--color-white) !important;
    color: var(--color-text-secondary) !important;
    font-family: var(--font-family-primary) !important;
    font-size: var(--text-body) !important;
    line-height: var(--leading-body) !important;
    box-sizing: border-box !important;
    overflow-x: clip !important;
  }
  .sz-branch *, .sz-branch *::before, .sz-branch *::after {
    box-sizing: border-box !important;
    font-family: var(--font-family-primary) !important;
  }
  .sz-branch a { text-decoration: none !important; }
  .sz-branch a:focus-visible,
  .sz-branch button:focus-visible,
  .sz-branch summary:focus-visible {
    outline: var(--focus-ring) !important;
    outline-offset: var(--focus-offset) !important;
  }
  .sz-branch .sz-visually-hidden {
    position: absolute !important;
    width: 1px !important;
    height: 1px !important;
    overflow: hidden !important;
    clip: rect(0 0 0 0) !important;
    white-space: nowrap !important;
  }

  /* Container (foundations/04-spacing-layout.md) */
  .sz-branch .sz-container {
    width: min(100% - (var(--container-pad) * 2), var(--container-max)) !important;
    margin-inline: auto !important;
  }

  /* ---------- Button (components/button.md) ---------- */
  /* Ukuran btn--sm (40px / 18px / 13px) di semua viewport, termasuk mobile (permintaan user,
     menyimpang dari aturan button.md "aksi utama mobile ≥ btn--md") */
  .sz-branch .sz-btn {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: var(--space-2) !important;
    min-height: 40px !important;
    margin: 0 !important;
    padding: 0 18px !important;
    border: 1px solid transparent !important;
    border-radius: var(--radius-full) !important;
    font-size: 13px !important;
    font-weight: var(--font-weight-semibold) !important;
    line-height: 1 !important;
    white-space: nowrap !important;
    text-transform: none !important;
    cursor: pointer !important;
    transition: background-color var(--duration-fast) var(--ease-standard), transform var(--duration-fast) var(--ease-standard) !important;
  }
  .sz-branch .sz-btn:hover { transform: translateY(-1px) !important; }
  .sz-branch .sz-btn:active { transform: translateY(0) !important; }
  .sz-branch .sz-btn--primary,
  .sz-branch .sz-btn--primary:visited { background: var(--color-brand-primary) !important; color: var(--color-text-on-primary) !important; }
  .sz-branch .sz-btn--primary:hover { background: var(--color-primary-hover) !important; color: var(--color-text-on-primary) !important; }
  .sz-branch .sz-btn--outline,
  .sz-branch .sz-btn--outline:visited { background: transparent !important; color: var(--color-brand-primary) !important; border-color: var(--color-brand-primary) !important; }
  .sz-branch .sz-btn--outline:hover { background: var(--color-primary-subtle) !important; color: var(--color-brand-primary) !important; }
  .sz-branch .sz-btn svg { width: 16px !important; height: 16px !important; flex: none !important; }

  /* ---------- Breadcrumb (components/breadcrumb.md) ---------- */
  .sz-branch .sz-breadcrumb__list {
    display: flex !important;
    flex-wrap: wrap !important;
    align-items: center !important;
    gap: var(--space-2) !important;
    margin: 0 0 var(--space-5) !important;
    padding: 0 !important;
    list-style: none !important;
    font-size: 13px !important;
    line-height: var(--leading-label) !important;
  }
  .sz-branch .sz-breadcrumb__list li { display: inline-flex !important; align-items: center !important; gap: var(--space-2) !important; margin: 0 !important; }
  .sz-branch .sz-breadcrumb__list li + li::before { content: "/" !important; color: var(--color-border-strong) !important; }
  .sz-branch .sz-breadcrumb a { color: var(--color-text-muted) !important; font-weight: var(--font-weight-medium) !important; }
  .sz-branch .sz-breadcrumb a:hover { color: var(--color-brand-primary) !important; }
  .sz-branch .sz-breadcrumb__current { color: var(--color-brand-primary) !important; font-weight: var(--font-weight-semibold) !important; }

  /* ---------- 1. Header (compact: judul skala H4, lead body-sm) ---------- */
  .sz-branch .sz-branch__head { margin: 0 !important; }
  .sz-branch .sz-branch__title {
    margin: 0 0 var(--space-2) !important;
    font-size: var(--text-h4) !important;
    font-weight: var(--font-weight-bold) !important;
    line-height: var(--leading-h4) !important;
    color: var(--color-brand-primary) !important;
  }
  /* Rating + status buka satu baris, langsung di bawah judul */
  .sz-branch .sz-branch__meta {
    display: flex !important;
    flex-wrap: wrap !important;
    align-items: center !important;
    gap: var(--space-2) var(--space-4) !important;
    margin: 0 0 var(--space-3) !important;
  }
  .sz-branch .sz-rating {
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    font-size: var(--text-body-sm) !important;
    line-height: var(--leading-body-sm) !important;
    color: var(--color-text-secondary) !important;
  }
  .sz-branch .sz-rating svg { width: 16px !important; height: 16px !important; fill: var(--color-promo-yellow) !important; flex: none !important; }
  .sz-branch .sz-rating strong { color: var(--color-text-primary) !important; font-weight: var(--font-weight-semibold) !important; }
  .sz-branch .sz-rating a,
  .sz-branch .sz-rating a:visited {
    color: var(--color-brand-primary) !important;
    font-weight: var(--font-weight-medium) !important;
    text-decoration: underline !important;
    text-underline-offset: 3px !important;
  }
  .sz-branch .sz-rating a:hover { color: var(--color-primary-hover) !important; }
  .sz-branch .sz-branch__lead {
    max-width: var(--measure) !important;
    margin: 0 !important;
    font-size: var(--text-body-sm) !important;
    line-height: var(--leading-body-sm) !important;
    color: var(--color-text-secondary) !important;
  }

  /* ---------- 2. Layout: semua info di kolom kiri, galeri kanan setinggi kolom kiri ---------- */
  .sz-branch .sz-branch__body {
    display: grid !important;
    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    grid-template-rows: auto auto !important;
    gap: 0 var(--space-7) !important;
  }
  .sz-branch .sz-branch__head { grid-column: 1 !important; grid-row: 1 !important; }
  .sz-branch .sz-panel { grid-column: 1 !important; grid-row: 2 !important; }
  .sz-branch .sz-gallery { grid-column: 2 !important; grid-row: 1 / span 2 !important; }

  /* Info tanpa kotak: grup dipisah garis tipis supaya ringan */
  .sz-branch .sz-panel {
    margin: 0 !important;
    padding: 0 !important;
    background: none !important;
    border: 0 !important;
    box-shadow: none !important;
  }
  .sz-branch .sz-panel__group {
    margin: var(--space-4) 0 0 !important;
    padding: var(--space-4) 0 0 !important;
    border-top: 1px solid var(--color-border) !important;
  }
  .sz-branch .sz-panel__label {
    margin: 0 0 var(--space-2) !important;
    font-size: var(--text-label) !important;
    font-weight: var(--font-weight-semibold) !important;
    line-height: var(--leading-label) !important;
    letter-spacing: var(--tracking-eyebrow) !important;
    text-transform: uppercase !important;
    color: var(--color-text-muted) !important;
  }
  /* Baris kepala grup: label kiri, status/aksi kecil kanan */
  .sz-branch .sz-panel__head {
    display: flex !important;
    flex-wrap: wrap !important;
    justify-content: space-between !important;
    align-items: center !important;
    gap: var(--space-1) var(--space-3) !important;
    margin: 0 0 var(--space-2) !important;
  }
  .sz-branch .sz-panel__head .sz-panel__label { margin: 0 !important; }
  /* Area tap tetap 44px, tapi tidak menambah tinggi baris */
  .sz-branch .sz-panel__head .sz-textbtn { margin: calc(var(--space-3) * -1) 0 !important; }
  .sz-branch .sz-panel__address {
    max-width: var(--measure) !important;
    margin: 0 !important;
    font-size: var(--text-body-sm) !important;
    line-height: var(--leading-body-sm) !important;
    color: var(--color-text-primary) !important;
  }
  .sz-branch .sz-textbtn {
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    min-height: 44px !important;
    margin: 0 !important;
    padding: 0 !important;
    background: none !important;
    border: none !important;
    border-radius: 0 !important;
    font-size: var(--text-body-sm) !important;
    font-weight: var(--font-weight-semibold) !important;
    line-height: 1 !important;
    color: var(--color-brand-primary) !important;
    cursor: pointer !important;
  }
  .sz-branch .sz-textbtn:hover,
  .sz-branch .sz-textbtn:focus { background: none !important; color: var(--color-brand-primary) !important; }
  .sz-branch .sz-textbtn:hover span { text-decoration: underline !important; text-underline-offset: 3px !important; }
  .sz-branch .sz-textbtn svg { width: 16px !important; height: 16px !important; fill: currentColor !important; flex: none !important; }
  .sz-branch .sz-textbtn.is-done { color: var(--color-success) !important; }

  /* Tombol selebar isinya di desktop (button.md: desktop tidak memakai tombol lebar penuh) */
  .sz-branch .sz-panel__actions {
    display: flex !important;
    flex-wrap: wrap !important;
    gap: var(--space-3) !important;
    margin-top: var(--space-4) !important;
  }

  /* Jam operasional: 7 hari selalu tampil sebagai teks HTML (terbaca Google),
     2 kolom mengalir ke bawah (Senin-Kamis | Jumat-Minggu), hari ini disorot */
  .sz-branch .sz-hours__list {
    display: grid !important;
    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    grid-template-rows: repeat(4, auto) !important;
    grid-auto-flow: column !important;
    gap: 0 var(--space-4) !important;
    max-width: 440px !important;
    margin: 0 !important;
    padding: 0 !important;
  }
  .sz-branch .sz-hours__item {
    display: flex !important;
    justify-content: space-between !important;
    align-items: baseline !important;
    gap: var(--space-2) !important;
    margin: 0 !important;
    padding: var(--space-1) var(--space-2) !important;
    border-radius: var(--radius-sm) !important;
    font-size: var(--text-body-sm) !important;
    line-height: var(--leading-body-sm) !important;
  }
  .sz-branch .sz-hours__item dt {
    margin: 0 !important;
    font-weight: var(--font-weight-regular) !important;
    color: var(--color-text-secondary) !important;
  }
  .sz-branch .sz-hours__item dd {
    margin: 0 !important;
    color: var(--color-text-primary) !important;
    font-variant-numeric: tabular-nums !important;
    white-space: nowrap !important;
  }
  .sz-branch .sz-hours__item.is-today {
    background: var(--color-primary-subtle) !important;
    animation: sz-fade-in var(--duration-media) var(--ease-standard) both !important;
  }
  .sz-branch .sz-hours__item.is-today dt,
  .sz-branch .sz-hours__item.is-today dd {
    font-weight: var(--font-weight-semibold) !important;
    color: var(--color-brand-primary) !important;
  }
  .sz-branch .sz-hours__status {
    display: flex !important;
    align-items: center !important;
    gap: var(--space-2) !important;
    margin: 0 !important;
    font-size: var(--text-body-sm) !important;
    font-weight: var(--font-weight-semibold) !important;
    line-height: var(--leading-body-sm) !important;
    animation: sz-fade-in var(--duration-media) var(--ease-standard) both !important;
  }
  .sz-branch .sz-hours__status[hidden] { display: none !important; }
  .sz-branch .sz-hours__status.is-open { color: var(--color-success) !important; }
  .sz-branch .sz-hours__status.is-closed { color: var(--color-error) !important; }
  .sz-branch .sz-hours__dot {
    width: 8px !important;
    height: 8px !important;
    border-radius: var(--radius-full) !important;
    background: currentColor !important;
    flex: none !important;
  }
  .sz-branch .sz-hours__status.is-open .sz-hours__dot { animation: sz-pulse 2s var(--ease-standard) infinite !important; }

  @keyframes sz-fade-in {
    from { opacity: 0; transform: translateY(4px); }
    to { opacity: 1; transform: translateY(0); }
  }
  @keyframes sz-pulse {
    0% { box-shadow: 0 0 0 0 var(--pulse-ring); }
    70% { box-shadow: 0 0 0 8px transparent; }
    100% { box-shadow: 0 0 0 0 transparent; }
  }

  /* Galeri (components/image.md, standalone radius-lg). Desktop: tinggi ikut kolom kiri,
     foto besar mengisi sisa tinggi (rasio menyesuaikan, object-fit cover). Tablet & mobile: rasio tetap. */
  .sz-branch .sz-gallery {
    display: flex !important;
    flex-direction: column !important;
    min-width: 0 !important;
    margin: 0 !important;
  }
  .sz-branch .sz-gallery__stage {
    position: relative !important;
    flex: 1 1 auto !important;
    min-height: 280px !important;
    overflow: hidden !important;
    margin: 0 !important;
    border-radius: var(--radius-lg) !important;
    background: var(--color-brand-almond) !important;
    touch-action: pan-y !important;
    cursor: pointer !important;
    user-select: none !important;
    -webkit-user-select: none !important;
  }
  .sz-branch .sz-gallery__stage img {
    position: absolute !important;
    inset: 0 !important;
    width: 100% !important;
    height: 100% !important;
    max-width: none !important;
    margin: 0 !important;
    border: none !important;
    border-radius: 0 !important;
    box-shadow: none !important;
    object-fit: cover !important;
    object-position: center !important;
    transition: opacity var(--duration-fast) var(--ease-standard) !important;
  }
  .sz-branch .sz-gallery__stage img.is-fading { opacity: 0 !important; }
  .sz-branch .sz-gallery__nav {
    position: absolute !important;
    top: 50% !important;
    z-index: 2 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 44px !important;
    height: 44px !important;
    min-height: 0 !important;
    margin: -22px 0 0 !important;
    padding: 0 !important;
    border: none !important;
    border-radius: var(--radius-full) !important;
    background: var(--color-white) !important;
    color: var(--color-brand-primary) !important;
    box-shadow: var(--shadow-md) !important;
    cursor: pointer !important;
    transition: transform var(--duration-fast) var(--ease-standard) !important;
  }
  .sz-branch .sz-gallery__nav:hover,
  .sz-branch .sz-gallery__nav:focus { background: var(--color-white) !important; color: var(--color-brand-primary) !important; transform: scale(1.05) !important; }
  .sz-branch .sz-gallery__nav svg { width: 20px !important; height: 20px !important; flex: none !important; }
  .sz-branch .sz-gallery__nav--prev { left: var(--space-4) !important; }
  .sz-branch .sz-gallery__nav--next { right: var(--space-4) !important; }
  .sz-branch .sz-gallery__count {
    position: absolute !important;
    right: var(--space-4) !important;
    bottom: var(--space-4) !important;
    z-index: 2 !important;
    display: inline-flex !important;
    align-items: center !important;
    min-height: 30px !important;
    padding: 0 10px !important;
    border-radius: var(--radius-full) !important;
    background: var(--color-white) !important;
    color: var(--color-text-primary) !important;
    font-size: var(--text-label) !important;
    font-weight: var(--font-weight-semibold) !important;
    line-height: var(--leading-label) !important;
    font-variant-numeric: tabular-nums !important;
    box-shadow: var(--shadow-sm) !important;
  }

  .sz-branch .sz-gallery__thumbs {
    display: grid !important;
    flex: none !important;
    grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
    gap: var(--space-3) !important;
    margin: var(--space-3) 0 0 !important;
    padding: 0 !important;
  }
  .sz-branch .sz-gallery__thumb {
    position: relative !important;
    display: block !important;
    width: 100% !important;
    aspect-ratio: 16 / 9 !important;
    min-height: 0 !important;
    margin: 0 !important;
    padding: 0 !important;
    overflow: hidden !important;
    border: none !important;
    border-radius: var(--radius-md) !important;
    background: var(--color-brand-almond) !important;
    cursor: pointer !important;
    opacity: 0.6 !important;
    transition: opacity var(--duration-fast) var(--ease-standard) !important;
  }
  .sz-branch .sz-gallery__thumb::after {
    content: "" !important;
    position: absolute !important;
    inset: 0 !important;
    border: 2px solid transparent !important;
    border-radius: inherit !important;
    transition: border-color var(--duration-fast) var(--ease-standard) !important;
  }
  .sz-branch .sz-gallery__thumb:hover,
  .sz-branch .sz-gallery__thumb:focus { background: var(--color-brand-almond) !important; opacity: 1 !important; }
  .sz-branch .sz-gallery__thumb[aria-current="true"] { opacity: 1 !important; }
  .sz-branch .sz-gallery__thumb[aria-current="true"]::after { border-color: var(--color-brand-primary) !important; }
  .sz-branch .sz-gallery__thumb img {
    position: absolute !important;
    inset: 0 !important;
    width: 100% !important;
    height: 100% !important;
    max-width: none !important;
    margin: 0 !important;
    border: none !important;
    border-radius: 0 !important;
    object-fit: cover !important;
  }

  /* ---------- Tablet (≤ 1024px) ---------- */
  @media (max-width: 1024px) {
    .sz-branch { --grid-gap: 20px !important; --section-y: 64px !important; }
    .sz-branch .sz-branch__body { column-gap: var(--space-5) !important; }
    /* Kolom kiri tablet lebih tinggi; galeri tidak ditarik supaya tidak jadi potret */
    .sz-branch .sz-gallery { align-self: start !important; }
    .sz-branch .sz-gallery__stage { flex: none !important; min-height: 0 !important; aspect-ratio: 4 / 3 !important; }
  }

  /* ---------- Mobile (≤ 768px): stack, urutan prioritas tetap (info dulu) ---------- */
  @media (max-width: 768px) {
    .sz-branch { --container-pad: 20px !important; --grid-gap: 16px !important; --section-y: 56px !important; padding-top: var(--space-5) !important; }
    .sz-branch .sz-branch__body { grid-template-columns: minmax(0, 1fr) !important; grid-template-rows: none !important; }
    .sz-branch .sz-branch__head { grid-column: 1 !important; grid-row: 1 !important; }
    .sz-branch .sz-panel { grid-column: 1 !important; grid-row: 2 !important; }
    .sz-branch .sz-gallery { grid-column: 1 !important; grid-row: 3 !important; margin-top: var(--space-5) !important; }
    .sz-branch .sz-breadcrumb__list { margin-bottom: var(--space-3) !important; }
    .sz-branch .sz-branch__title { font-size: var(--text-h5) !important; line-height: var(--leading-h5) !important; }
    .sz-branch .sz-branch__meta { margin-bottom: var(--space-2) !important; }
    .sz-branch .sz-gallery__stage { aspect-ratio: 16 / 9 !important; border-radius: var(--radius-md) !important; }
    .sz-branch .sz-gallery__nav { display: none !important; }
    .sz-branch .sz-gallery__thumbs { gap: var(--space-2) !important; margin-top: var(--space-2) !important; }
    .sz-branch .sz-gallery__thumb { border-radius: var(--radius-sm) !important; }
  }

  /* ---------- Mobile kecil (≤ 480px): CTA lebar penuh ---------- */
  @media (max-width: 480px) {
    .sz-branch .sz-panel__actions { flex-direction: column !important; }
    .sz-branch .sz-panel__actions .sz-btn { width: 100% !important; }
    .sz-branch .sz-hours__list { gap: 0 var(--space-2) !important; }
    .sz-branch .sz-hours__item { gap: var(--space-1) !important; padding: var(--space-1) !important; font-size: var(--text-caption) !important; }
  }

  @media (prefers-reduced-motion: reduce) {
    .sz-branch .sz-btn:hover,
    .sz-branch .sz-gallery__nav:hover { transform: none !important; }
    .sz-branch .sz-gallery__stage img { transition: none !important; }
    .sz-branch .sz-hours__status,
    .sz-branch .sz-hours__item.is-today,
    .sz-branch .sz-hours__status.is-open .sz-hours__dot { animation: none !important; }
  }

  /* ---------- Varian dinamis: tanpa galeri / satu foto ---------- */
  .sz-branch .sz-branch__body--solo { grid-template-columns: minmax(0, 1fr) !important; }
  .sz-branch .sz-gallery--single .sz-gallery__stage { cursor: default !important; }

  /* ---------- Seksi opsional di bawah hero (Cara ke Lokasi, Treatment, FAQ, Cabang Terdekat) ---------- */
  .sz-branch .sz-section {
    margin: var(--space-8) 0 0 !important;
    padding: var(--space-6) 0 0 !important;
    border-top: 1px solid var(--color-border) !important;
  }
  .sz-branch .sz-section__title {
    margin: 0 0 var(--space-4) !important;
    font-size: var(--text-h5) !important;
    font-weight: var(--font-weight-bold) !important;
    line-height: var(--leading-h5) !important;
    color: var(--color-brand-primary) !important;
  }
  .sz-branch .sz-section__body {
    max-width: var(--measure) !important;
    font-size: var(--text-body-sm) !important;
    line-height: var(--leading-body-sm) !important;
    color: var(--color-text-secondary) !important;
  }
  .sz-branch .sz-section__body p { margin: 0 0 var(--space-3) !important; }
  .sz-branch .sz-section__body p:last-child { margin-bottom: 0 !important; }
  .sz-branch .sz-section__body ul,
  .sz-branch .sz-section__body ol { margin: 0 0 var(--space-3) !important; padding-left: var(--space-5) !important; }
  .sz-branch .sz-section__body a,
  .sz-branch .sz-section__body a:visited {
    color: var(--color-brand-primary) !important;
    font-weight: var(--font-weight-medium) !important;
    text-decoration: underline !important;
    text-underline-offset: 3px !important;
  }
  .sz-branch .sz-section__more { margin: var(--space-4) 0 0 !important; font-size: var(--text-body-sm) !important; line-height: var(--leading-body-sm) !important; }
  .sz-branch .sz-section__more a,
  .sz-branch .sz-section__more a:visited {
    color: var(--color-brand-primary) !important;
    font-weight: var(--font-weight-semibold) !important;
    text-decoration: underline !important;
    text-underline-offset: 3px !important;
  }

  /* Treatment: chip pill */
  .sz-branch .sz-chips {
    display: flex !important;
    flex-wrap: wrap !important;
    gap: var(--space-2) !important;
    margin: 0 !important;
    padding: 0 !important;
    list-style: none !important;
  }
  .sz-branch .sz-chips li { margin: 0 !important; padding: 0 !important; }
  .sz-branch .sz-chip,
  .sz-branch .sz-chip:visited {
    display: inline-flex !important;
    align-items: center !important;
    min-height: 40px !important;
    padding: 0 16px !important;
    border: 1px solid var(--color-border-strong) !important;
    border-radius: var(--radius-full) !important;
    background: transparent !important;
    font-size: 13px !important;
    font-weight: var(--font-weight-medium) !important;
    line-height: 1 !important;
    color: var(--color-brand-primary) !important;
    transition: background-color var(--duration-fast) var(--ease-standard), border-color var(--duration-fast) var(--ease-standard) !important;
  }
  .sz-branch a.sz-chip:hover { background: var(--color-primary-subtle) !important; border-color: var(--color-brand-primary) !important; color: var(--color-brand-primary) !important; }

  /* FAQ: details/summary */
  .sz-branch .sz-faq__list { max-width: 820px !important; margin: 0 !important; padding: 0 !important; }
  .sz-branch .sz-faq__item { margin: 0 !important; padding: 0 !important; border-bottom: 1px solid var(--color-border) !important; }
  .sz-branch .sz-faq__item:first-child { border-top: 1px solid var(--color-border) !important; }
  .sz-branch .sz-faq__item summary {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: var(--space-4) !important;
    min-height: 56px !important;
    margin: 0 !important;
    padding: var(--space-3) 0 !important;
    font-size: var(--text-body) !important;
    font-weight: var(--font-weight-semibold) !important;
    line-height: var(--leading-h5) !important;
    color: var(--color-text-primary) !important;
    list-style: none !important;
    cursor: pointer !important;
  }
  .sz-branch .sz-faq__item summary::-webkit-details-marker { display: none !important; }
  .sz-branch .sz-faq__item summary::after {
    content: "+" !important;
    flex: none !important;
    font-size: 22px !important;
    font-weight: var(--font-weight-regular) !important;
    line-height: 1 !important;
    color: var(--color-brand-primary) !important;
  }
  .sz-branch .sz-faq__item[open] summary::after { content: "\2212" !important; }
  .sz-branch .sz-faq__answer { margin: 0 !important; padding: 0 0 var(--space-4) !important; }
  .sz-branch .sz-faq__answer p {
    max-width: var(--measure) !important;
    margin: 0 !important;
    font-size: var(--text-body-sm) !important;
    line-height: var(--leading-body-sm) !important;
    color: var(--color-text-secondary) !important;
  }

  /* Cabang terdekat */
  .sz-branch .sz-nearby {
    display: grid !important;
    grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
    gap: var(--space-3) !important;
    margin: 0 !important;
    padding: 0 !important;
    list-style: none !important;
  }
  .sz-branch .sz-nearby li { margin: 0 !important; padding: 0 !important; }
  .sz-branch .sz-nearby__link,
  .sz-branch .sz-nearby__link:visited {
    display: block !important;
    height: 100% !important;
    padding: var(--space-4) !important;
    border: 1px solid var(--color-border) !important;
    border-radius: var(--radius-md) !important;
    background: var(--color-white) !important;
    transition: border-color var(--duration-fast) var(--ease-standard) !important;
  }
  .sz-branch .sz-nearby__link:hover { border-color: var(--color-brand-primary) !important; }
  .sz-branch .sz-nearby__name {
    display: block !important;
    font-size: var(--text-body-sm) !important;
    font-weight: var(--font-weight-semibold) !important;
    line-height: var(--leading-body-sm) !important;
    color: var(--color-brand-primary) !important;
  }
  .sz-branch .sz-nearby__sub {
    display: block !important;
    margin-top: var(--space-1) !important;
    font-size: var(--text-caption) !important;
    line-height: var(--leading-label) !important;
    color: var(--color-text-muted) !important;
  }

  @media (max-width: 1024px) {
    .sz-branch .sz-nearby { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; }
  }
  @media (max-width: 768px) {
    .sz-branch .sz-section { margin-top: var(--space-6) !important; padding-top: var(--space-5) !important; }
    .sz-branch .sz-faq__item summary { font-size: var(--text-body-sm) !important; }
  }
  @media (max-width: 480px) {
    .sz-branch .sz-nearby { grid-template-columns: minmax(0, 1fr) !important; }
  }
SZCSS;
    }
}

if (!function_exists('sz_branch_js')) {
    function sz_branch_js() {
        return <<<'SZJS'
(function () {
  var DAY_NAMES = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

  function fmt(mins) {
    return String(Math.floor(mins / 60)).padStart(2, '0') + ':' + String(mins % 60).padStart(2, '0');
  }

  function nowInJakarta() {
    var parts = new Intl.DateTimeFormat('en-US', {
      timeZone: 'Asia/Jakarta', weekday: 'short', hour: '2-digit', minute: '2-digit', hour12: false
    }).formatToParts(new Date());
    function get(type) { return parts.find(function (p) { return p.type === type; }).value; }
    var day = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'].indexOf(get('weekday'));
    return { day: day, mins: (parseInt(get('hour'), 10) % 24) * 60 + parseInt(get('minute'), 10) };
  }

  // Jam buka dibaca dari data-open / data-close (menit sejak 00:00 WIB). Hari tutup tidak punya atribut itu.
  function readHours(root) {
    var map = {};
    root.querySelectorAll('.sz-hours__item').forEach(function (el) {
      var day = parseInt(el.getAttribute('data-day'), 10);
      var open = el.getAttribute('data-open');
      var close = el.getAttribute('data-close');
      map[day] = (open !== null && close !== null) ? [parseInt(open, 10), parseInt(close, 10)] : null;
    });
    return map;
  }

  function nextOpening(hours, day) {
    for (var i = 1; i <= 7; i++) {
      var d = (day + i) % 7;
      if (hours[d]) return (i === 1 ? 'besok' : DAY_NAMES[d]) + ' ' + fmt(hours[d][0]);
    }
    return '';
  }

  // Status live + sorot hari ini. Dihitung di browser karena HTML di-cache LiteSpeed.
  // Tanpa JS keduanya tidak muncul, daftar 7 hari tetap tampil lengkap.
  function setupHours(root) {
    var status = root.querySelector('[data-sz-status]');
    var text = root.querySelector('[data-sz-status-text]');
    if (!status || !text) return;

    var hours = readHours(root);
    var now = nowInJakarta();
    var today = hours[now.day];

    var item = root.querySelector('.sz-hours__item[data-day="' + now.day + '"]');
    if (item) item.classList.add('is-today');

    var open = false;
    var message;
    if (today && now.mins >= today[0] && now.mins < today[1]) {
      open = true;
      message = 'Buka sekarang · tutup ' + fmt(today[1]) + ' WIB';
    } else if (today && now.mins < today[0]) {
      message = 'Tutup · buka hari ini ' + fmt(today[0]) + ' WIB';
    } else {
      var next = nextOpening(hours, now.day);
      message = next ? 'Tutup · buka ' + next + ' WIB' : 'Tutup';
    }

    text.textContent = message;
    status.classList.add(open ? 'is-open' : 'is-closed');
    status.hidden = false;
  }

  function setupCopy(root) {
    var btn = root.querySelector('[data-sz-copy]');
    var label = root.querySelector('[data-sz-copy-label]');
    var status = root.querySelector('[data-sz-copy-status]');
    var address = root.querySelector('[data-sz-address]');
    if (!btn || !label || !address) return;

    function fallbackCopy(value) {
      var area = document.createElement('textarea');
      area.value = value;
      area.setAttribute('readonly', '');
      area.style.position = 'absolute';
      area.style.left = '-9999px';
      document.body.appendChild(area);
      area.select();
      var ok = false;
      try { ok = document.execCommand('copy'); } catch (e) { ok = false; }
      document.body.removeChild(area);
      return ok;
    }

    var resetTimer;
    function done(ok) {
      label.textContent = ok ? 'Alamat disalin' : 'Gagal menyalin';
      if (status) status.textContent = ok ? 'Alamat disalin ke clipboard' : 'Alamat gagal disalin';
      btn.classList.toggle('is-done', ok);
      clearTimeout(resetTimer);
      resetTimer = setTimeout(function () {
        label.textContent = 'Salin alamat';
        btn.classList.remove('is-done');
      }, 2200);
    }

    btn.addEventListener('click', function () {
      var value = address.textContent.trim();
      if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(value).then(function () { done(true); }, function () { done(fallbackCopy(value)); });
      } else {
        done(fallbackCopy(value));
      }
    });
  }

  function setupGallery(root) {
    var gallery = root.querySelector('.sz-gallery');
    if (!gallery) return;
    var stage = gallery.querySelector('.sz-gallery__stage');
    var main = gallery.querySelector('.sz-gallery__stage img');
    var count = gallery.querySelector('.sz-gallery__count');
    var thumbs = Array.prototype.slice.call(gallery.querySelectorAll('.sz-gallery__thumb'));
    // Satu foto: tidak ada thumbs, tidak ada yang perlu digerakkan.
    if (!stage || !main || thumbs.length < 2) return;

    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var current = 0;
    var fadeTimer;

    // URL foto dari data-full; fallback ke gambar thumbnail kalau plugin lazy-load mengubah atribut
    function fullSrc(thumb) {
      var img = thumb.querySelector('img');
      return thumb.getAttribute('data-full') || (img && (img.currentSrc || img.src)) || '';
    }

    function show(index) {
      current = (index + thumbs.length) % thumbs.length;
      var thumb = thumbs[current];
      function swap() {
        main.removeAttribute('srcset');
        main.src = fullSrc(thumb);
        main.alt = thumb.getAttribute('data-alt') || '';
        main.classList.remove('is-fading');
      }
      clearTimeout(fadeTimer);
      if (reduceMotion) {
        swap();
      } else {
        main.classList.add('is-fading');
        fadeTimer = setTimeout(swap, 150);
      }
      thumbs.forEach(function (t, i) {
        if (i === current) t.setAttribute('aria-current', 'true');
        else t.removeAttribute('aria-current');
      });
      if (count) count.textContent = (current + 1) + ' / ' + thumbs.length;
    }

    // Swipe/drag: sentuh di HP, tarik mouse di desktop
    var startX = null;
    var startY = 0;
    var dragged = false;
    stage.addEventListener('pointerdown', function (e) {
      if (e.target.closest('.sz-gallery__nav')) return;
      startX = e.clientX;
      startY = e.clientY;
      dragged = false;
    });
    stage.addEventListener('pointerup', function (e) {
      if (startX === null) return;
      var dx = e.clientX - startX;
      var dy = e.clientY - startY;
      startX = null;
      if (Math.abs(dx) > 40 && Math.abs(dx) > Math.abs(dy)) {
        dragged = true;
        show(current + (dx < 0 ? 1 : -1));
      }
    });
    stage.addEventListener('pointercancel', function () { startX = null; });
    main.addEventListener('dragstart', function (e) { e.preventDefault(); });

    // Satu listener di level galeri: thumbnail, panah, dan klik foto besar (= foto berikutnya)
    gallery.addEventListener('click', function (e) {
      var thumb = e.target.closest('.sz-gallery__thumb');
      if (thumb) { show(thumbs.indexOf(thumb)); return; }
      if (e.target.closest('.sz-gallery__nav--prev')) { show(current - 1); return; }
      if (e.target.closest('.sz-gallery__nav--next')) { show(current + 1); return; }
      if (e.target.closest('.sz-gallery__stage')) {
        if (dragged) { dragged = false; return; }
        show(current + 1);
      }
    });

    gallery.addEventListener('keydown', function (e) {
      if (e.key === 'ArrowLeft') show(current - 1);
      if (e.key === 'ArrowRight') show(current + 1);
    });
  }

  function init() {
    // Tiap wrapper dan tiap fitur jalan sendiri, supaya error di satu bagian tidak mematikan yang lain
    document.querySelectorAll('.sz-branch[data-sz-branch]').forEach(function (root) {
      if (root.getAttribute('data-sz-ready')) return;
      root.setAttribute('data-sz-ready', '1');
      [setupHours, setupCopy, setupGallery].forEach(function (fn) {
        try { fn(root); } catch (err) { console.error('[sz-branch]', err); }
      });
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
SZJS;
    }
}

if (!function_exists('sz_branch_enqueue')) {
    // Dipanggil dari wp_enqueue_scripts (halaman cabang) dan dari shortcode (fallback untuk [sozo_branch id=""]).
    function sz_branch_enqueue() {
        static $done = false;
        if ($done) {
            return;
        }
        $done = true;

        if (SZ_BRANCH_LOAD_FONT) {
            wp_enqueue_style('sz-branch-font', 'https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap', array(), null);
        }

        wp_register_style('sz-branch', false, array(), SZ_BRANCH_VERSION);
        wp_enqueue_style('sz-branch');
        wp_add_inline_style('sz-branch', sz_branch_css());

        // Skrip inline tidak bisa di-defer. Di footer + init() menunggu DOMContentLoaded, hasilnya setara.
        wp_register_script('sz-branch', false, array(), SZ_BRANCH_VERSION, true);
        wp_enqueue_script('sz-branch');
        wp_add_inline_script('sz-branch', sz_branch_js());
    }
}

if (!function_exists('sz_branch_maybe_enqueue')) {
    function sz_branch_maybe_enqueue() {
        // Cek meta, bukan has_shortcode(): konten Elementor tersimpan di _elementor_data.
        if (!is_page() || !sz_branch_data(get_queried_object_id())) {
            return;
        }
        sz_branch_enqueue();
    }
    add_action('wp_enqueue_scripts', 'sz_branch_maybe_enqueue');
}

if (!function_exists('sz_branch_resource_hints')) {
    function sz_branch_resource_hints($urls, $relation) {
        if (SZ_BRANCH_LOAD_FONT && $relation === 'preconnect' && is_page() && sz_branch_data(get_queried_object_id())) {
            $urls[] = 'https://fonts.googleapis.com';
            $urls[] = array('href' => 'https://fonts.gstatic.com', 'crossorigin' => 'anonymous');
        }
        return $urls;
    }
    add_filter('wp_resource_hints', 'sz_branch_resource_hints', 10, 2);
}

// ============ 6. SCHEMA: sisipkan MedicalClinic + FAQPage ke graph Yoast ============
if (!function_exists('sz_branch_piece_has_type')) {
    function sz_branch_piece_has_type($piece, $type) {
        if (!is_array($piece) || !isset($piece['@type'])) {
            return false;
        }
        return in_array($type, (array) $piece['@type'], true);
    }
}

if (!function_exists('sz_branch_prune')) {
    // Buang nilai kosong supaya JSON-LD tidak membawa properti hampa.
    function sz_branch_prune($data) {
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                $v = sz_branch_prune($v);
                if (!$v) {
                    unset($data[$k]);
                    continue;
                }
                $data[$k] = $v;
            } elseif ($v === '' || $v === null) {
                unset($data[$k]);
            }
        }
        return $data;
    }
}

if (!function_exists('sz_branch_schema_graph')) {
    function sz_branch_schema_graph($graph, $context = null) {
        try {
            if (!is_array($graph) || !is_page()) {
                return $graph;
            }
            $post_id = (int) get_queried_object_id();
            $d = sz_branch_data($post_id);
            if (!$d) {
                return $graph;
            }

            $permalink = (is_object($context) && !empty($context->canonical)) ? $context->canonical : get_permalink($post_id);
            if (!$permalink) {
                return $graph;
            }
            $clinic_id = $permalink . '#clinic';

            // Ambil @id Organization dan WebPage langsung dari graph Yoast.
            $org_id = home_url('/#organization');
            $webpage_key = null;
            foreach ($graph as $i => $piece) {
                if (sz_branch_piece_has_type($piece, 'Organization') && !empty($piece['@id'])) {
                    $org_id = $piece['@id'];
                }
                if ($webpage_key === null && sz_branch_piece_has_type($piece, 'WebPage')) {
                    $webpage_key = $i;
                }
            }
            $webpage_id = ($webpage_key !== null && !empty($graph[$webpage_key]['@id'])) ? $graph[$webpage_key]['@id'] : $permalink . '#webpage';

            // Jam buka: hari dengan jam sama digabung jadi satu spesifikasi.
            $en_days = array(0 => 'Sunday', 1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday');
            $groups = array();
            foreach (array(1, 2, 3, 4, 5, 6, 0) as $day) {
                if (empty($d['hours'][$day]) || !empty($d['hours'][$day]['closed'])) {
                    continue;
                }
                $row = $d['hours'][$day];
                if (!isset($row['open'], $row['close'])) {
                    continue;
                }
                $key = $row['open'] . '|' . $row['close'];
                if (!isset($groups[$key])) {
                    $groups[$key] = array('opens' => $row['open'], 'closes' => $row['close'], 'days' => array());
                }
                $groups[$key]['days'][] = $en_days[$day];
            }
            $hours_spec = array();
            foreach ($groups as $g) {
                $hours_spec[] = array(
                    '@type'     => 'OpeningHoursSpecification',
                    'dayOfWeek' => count($g['days']) === 1 ? $g['days'][0] : $g['days'],
                    'opens'     => $g['opens'],
                    'closes'    => $g['closes'],
                );
            }

            $images = array();
            foreach (sz_branch_gallery_items($d) as $img) {
                $images[] = $img['src'];
            }

            // Alamat ikut hanya kalau minimal satu field terisi (addressCountry saja tidak cukup).
            $address = array();
            if ($d['street'] !== '' || $d['locality'] !== '' || $d['region'] !== '' || $d['postal'] !== '') {
                $address = array(
                    '@type'           => 'PostalAddress',
                    'streetAddress'   => $d['street'],
                    'addressLocality' => $d['locality'],
                    'addressRegion'   => $d['region'],
                    'postalCode'      => $d['postal'],
                    'addressCountry'  => 'ID',
                );
            }

            $clinic = array(
                '@type'                     => 'MedicalClinic',
                '@id'                       => $clinic_id,
                'name'                      => 'Sozo Skin Clinic - ' . $d['name'],
                'url'                       => $permalink,
                'image'                     => $images,
                'telephone'                 => $d['phone'] !== '' ? '+' . $d['phone'] : '',
                'address'                   => $address,
                'geo'                       => (is_numeric($d['lat']) && is_numeric($d['lng'])) ? array(
                    '@type'     => 'GeoCoordinates',
                    'latitude'  => (float) $d['lat'],
                    'longitude' => (float) $d['lng'],
                ) : array(),
                'openingHoursSpecification' => $hours_spec,
                'hasMap'                    => $d['gbp_url'],
                'sameAs'                    => $d['gbp_url'] !== '' ? array($d['gbp_url']) : array(),
                'parentOrganization'        => array('@id' => $org_id),
            );
            $graph[] = sz_branch_prune($clinic);

            // Hubungkan WebPage Yoast ke klinik.
            if ($webpage_key !== null) {
                $ref = array('@id' => $clinic_id);
                if (empty($graph[$webpage_key]['about'])) {
                    $graph[$webpage_key]['about'] = $ref;
                } else {
                    $existing = $graph[$webpage_key]['about'];
                    $list = isset($existing['@id']) ? array($existing) : (array) $existing;
                    $list[] = $ref;
                    $graph[$webpage_key]['about'] = $list;
                }
            }

            // FAQPage: teks sama persis dengan yang tampil di halaman.
            $faq_items = sz_branch_faq_items($d);
            if ($faq_items) {
                $entities = array();
                foreach ($faq_items as $f) {
                    $entities[] = array(
                        '@type'          => 'Question',
                        'name'           => $f['q'],
                        'acceptedAnswer' => array('@type' => 'Answer', 'text' => $f['a']),
                    );
                }
                $graph[] = array(
                    '@type'      => 'FAQPage',
                    '@id'        => $permalink . '#faq',
                    'isPartOf'   => array('@id' => $webpage_id),
                    'mainEntity' => $entities,
                );
            }
        } catch (Throwable $e) {
            return $graph;
        }
        return $graph;
    }
    add_filter('wpseo_schema_graph', 'sz_branch_schema_graph', 20, 2);
}

// ============ 7. META BOX: isi data manual di layar edit halaman ============
// Form ini menulis ke post meta yang sama dengan REST (tabel wp_postmeta), lewat sanitizer yang sama.
// Disimpan hanya kalau form-nya ikut terkirim (nonce ada), jadi save dari Elementor atau REST tidak menimpa data.
if (!function_exists('sz_branch_metabox_register')) {
    function sz_branch_metabox_register() {
        add_meta_box('sz-branch-box', 'Data Cabang Sozo (template [sozo_branch])', 'sz_branch_metabox_render', 'page', 'normal', 'high');
    }
    add_action('add_meta_boxes', 'sz_branch_metabox_register');
}

if (!function_exists('sz_branch_metabox_classes')) {
    // Halaman yang belum jadi halaman cabang: kotak tertutup supaya tidak mengganggu.
    function sz_branch_metabox_classes($classes) {
        global $post;
        if ($post && trim((string) get_post_meta($post->ID, 'sz_branch_name', true)) === '') {
            $classes[] = 'closed';
        }
        return $classes;
    }
    add_filter('postbox_classes_page_sz-branch-box', 'sz_branch_metabox_classes');
}

if (!function_exists('sz_branch_bx_input')) {
    function sz_branch_bx_input($label, $key, $value, $hint = '', $type = 'text') {
        return '<p class="sz-bx-field"><label><span>' . esc_html($label) . '</span>'
            . '<input type="' . esc_attr($type) . '" name="sz_branch_f[' . esc_attr($key) . ']" value="' . esc_attr($value) . '"></label>'
            . ($hint !== '' ? '<small>' . esc_html($hint) . '</small>' : '') . '</p>';
    }
}

if (!function_exists('sz_branch_bx_textarea')) {
    function sz_branch_bx_textarea($label, $key, $value, $hint = '', $rows = 3) {
        return '<p class="sz-bx-field sz-bx-field--wide"><label><span>' . esc_html($label) . '</span>'
            . '<textarea name="sz_branch_f[' . esc_attr($key) . ']" rows="' . (int) $rows . '">' . esc_textarea($value) . '</textarea></label>'
            . ($hint !== '' ? '<small>' . esc_html($hint) . '</small>' : '') . '</p>';
    }
}

if (!function_exists('sz_branch_bx_row')) {
    // Satu baris repeater. $specs: array(array(field, placeholder, value, 'input'|'textarea'), ...)
    function sz_branch_bx_row($group, $index, $specs) {
        $h = '<div class="sz-bx-row">';
        foreach ($specs as $s) {
            $name = 'sz_branch_' . $group . '[' . $index . '][' . $s[0] . ']';
            if ($s[3] === 'textarea') {
                $h .= '<textarea name="' . esc_attr($name) . '" data-f="' . esc_attr($s[0]) . '" rows="2" placeholder="' . esc_attr($s[1]) . '">' . esc_textarea($s[2]) . '</textarea>';
            } else {
                $h .= '<input type="text" name="' . esc_attr($name) . '" data-f="' . esc_attr($s[0]) . '" value="' . esc_attr($s[2]) . '" placeholder="' . esc_attr($s[1]) . '">';
            }
        }
        return $h . '<button type="button" class="button-link button-link-delete" data-sz-remove>Hapus</button></div>';
    }
}

if (!function_exists('sz_branch_bx_group')) {
    // Daftar baris + template kosong + tombol tambah.
    function sz_branch_bx_group($group, $title, $hint, $rows, $blank_specs, $row_specs_fn, $add_label, $extra = '') {
        $h = '<div class="sz-bx-section"><h4>' . esc_html($title) . '</h4>';
        if ($hint !== '') {
            $h .= '<p class="description">' . esc_html($hint) . '</p>';
        }
        $h .= '<div data-sz-group="' . esc_attr($group) . '">';
        $i = 0;
        foreach ($rows as $row) {
            if (is_array($row)) {
                $h .= sz_branch_bx_row($group, $i, call_user_func($row_specs_fn, $row));
                $i++;
            }
        }
        $h .= '</div>';
        $h .= '<template data-sz-tpl="' . esc_attr($group) . '">' . sz_branch_bx_row($group, '__i__', $blank_specs) . '</template>';
        $h .= '<p><button type="button" class="button" data-sz-add="' . esc_attr($group) . '">' . esc_html($add_label) . '</button> ' . $extra . '</p></div>';
        return $h;
    }
}

if (!function_exists('sz_branch_metabox_render')) {
    function sz_branch_metabox_render($post) {
        $id = (int) $post->ID;
        wp_nonce_field('sz_branch_save', 'sz_branch_nonce');
        $m = function ($key) use ($id) {
            return (string) get_post_meta($id, $key, true);
        };

        $o = '<div class="sz-bx">';
        $o .= '<p class="description">Isi <strong>Nama Cabang</strong> untuk menjadikan halaman ini halaman cabang. Taruh shortcode <code>[sozo_branch]</code> di Elementor. Bagian yang dikosongkan tidak tampil di halaman.</p>';

        $warn_key = 'sz_branch_warn_' . $id . '_' . get_current_user_id();
        if (get_transient($warn_key)) {
            delete_transient($warn_key);
            $o .= '<div class="notice notice-warning inline"><p>Jam operasional tidak disimpan: semua 7 hari harus diisi (jam buka dan tutup, atau dicentang Libur), dan jam tutup harus lebih besar dari jam buka. Jam lama dipertahankan.</p></div>';
        }

        $o .= '<h4>Info utama</h4><div class="sz-bx-grid">';
        $o .= sz_branch_bx_input('Nama Cabang *', 'sz_branch_name', $m('sz_branch_name'), 'Persis nama di Google Maps, tanpa awalan "Sozo Skin Clinic - ". Contoh: Arteri Pondok Indah');
        $o .= sz_branch_bx_input('Nomor WhatsApp', 'sz_phone', $m('sz_phone'), 'Format 62..., tanpa plus. Contoh: 6285175225664');
        $o .= sz_branch_bx_input('Rating Google', 'sz_rating', $m('sz_rating'), 'Angka 0 sampai 5. Contoh: 4.7');
        $o .= sz_branch_bx_input('Jumlah Ulasan', 'sz_review_count', $m('sz_review_count'), 'Contoh: 1856');
        $o .= sz_branch_bx_input('Link Google Maps / GBP', 'sz_gbp_url', $m('sz_gbp_url'), 'Dipakai untuk link ulasan dan schema.', 'url');
        $o .= sz_branch_bx_input('Kata Kunci Petunjuk Arah', 'sz_maps_query', $m('sz_maps_query'), 'Kosong = "Sozo Skin Clinic - {nama cabang}".');
        $o .= sz_branch_bx_input('Kode Tracking WhatsApp', 'sz_wa_src', $m('sz_wa_src'), 'Kosong = ORG-LOK.');
        $o .= '</div>';
        $o .= sz_branch_bx_textarea('Paragraf Intro', 'sz_lead', $m('sz_lead'), 'Teks polos di bawah judul.', 3);

        $o .= '<h4>Alamat dan schema</h4>';
        $o .= sz_branch_bx_textarea('Alamat Lengkap (tampil di halaman)', 'sz_address', $m('sz_address'), '', 2);
        $o .= '<div class="sz-bx-grid">';
        $o .= sz_branch_bx_input('Jalan', 'sz_street', $m('sz_street'), 'Contoh: Jl. Sultan Iskandar Muda No.68A');
        $o .= sz_branch_bx_input('Kecamatan / Kelurahan', 'sz_locality', $m('sz_locality'), 'Contoh: Kebayoran Lama');
        $o .= sz_branch_bx_input('Kota', 'sz_city', $m('sz_city'), 'Contoh: Jakarta Selatan');
        $o .= sz_branch_bx_input('Provinsi', 'sz_region', $m('sz_region'), 'Contoh: DKI Jakarta');
        $o .= sz_branch_bx_input('Kode Pos', 'sz_postal_code', $m('sz_postal_code'));
        $o .= '<span></span>';
        $o .= sz_branch_bx_input('Latitude', 'sz_lat', $m('sz_lat'), 'Dari Google Maps. Contoh: -6.2447');
        $o .= sz_branch_bx_input('Longitude', 'sz_lng', $m('sz_lng'), 'Contoh: 106.7822');
        $o .= '</div>';

        // Jam operasional
        $hours = array();
        $decoded = sz_branch_decode_json($m('sz_hours'));
        if ($decoded) {
            foreach ($decoded as $row) {
                if (is_array($row) && isset($row['day'])) {
                    $hours[(int) $row['day']] = $row;
                }
            }
        }
        $day_names = array(0 => 'Minggu', 1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu');
        $o .= '<div class="sz-bx-section"><h4>Jam operasional (WIB)</h4>';
        $o .= '<p class="description">Isi semua 7 hari. Hari libur: centang Libur.</p>';
        $o .= '<table class="sz-bx-hours"><thead><tr><th>Hari</th><th>Libur</th><th>Buka</th><th>Tutup</th></tr></thead><tbody>';
        foreach (array(1, 2, 3, 4, 5, 6, 0) as $day) {
            $r = isset($hours[$day]) ? $hours[$day] : array();
            $closed = !empty($r['closed']);
            $o .= '<tr data-day="' . $day . '"><td>' . esc_html($day_names[$day]) . '</td>'
                . '<td><input type="checkbox" name="sz_branch_h[' . $day . '][closed]" value="1"' . checked($closed, true, false) . '></td>'
                . '<td><input type="time" name="sz_branch_h[' . $day . '][open]" value="' . esc_attr(isset($r['open']) ? $r['open'] : '') . '"></td>'
                . '<td><input type="time" name="sz_branch_h[' . $day . '][close]" value="' . esc_attr(isset($r['close']) ? $r['close'] : '') . '"></td></tr>';
        }
        $o .= '</tbody></table>';
        $o .= '<p><button type="button" class="button" data-sz-copy-hours>Samakan semua hari dengan Senin</button></p></div>';

        // Galeri
        $gallery_rows = sz_branch_decode_json($m('sz_gallery'));
        $o .= sz_branch_bx_group(
            'g',
            'Galeri foto',
            'Foto pertama jadi foto utama. Kolom pertama diisi ID lampiran atau URL gambar. Maksimal 12.',
            $gallery_rows ? $gallery_rows : array(),
            array(array('src', 'ID lampiran atau URL gambar', '', 'input'), array('alt', 'Alt text (kosong = otomatis)', '', 'input'), array('label', 'Label, mis. tampak depan klinik', '', 'input')),
            function ($row) {
                return array(
                    array('src', 'ID lampiran atau URL gambar', !empty($row['id']) ? (string) $row['id'] : (isset($row['url']) ? (string) $row['url'] : ''), 'input'),
                    array('alt', 'Alt text (kosong = otomatis)', isset($row['alt']) ? (string) $row['alt'] : '', 'input'),
                    array('label', 'Label, mis. tampak depan klinik', isset($row['label']) ? (string) $row['label'] : '', 'input'),
                );
            },
            'Tambah foto (isi manual)',
            '<button type="button" class="button button-primary" data-sz-media>Pilih dari Media Library</button>'
        );

        // Seksi opsional
        $o .= '<h4>Seksi opsional (tampil hanya kalau diisi)</h4>';
        $o .= sz_branch_bx_textarea('Cara ke Lokasi', 'sz_access', $m('sz_access'), 'Landmark, parkir, lantai mal, transportasi umum. Boleh HTML dasar (p, ul, li, strong, a).', 5);

        $treat_rows = sz_branch_decode_json($m('sz_treatments'));
        $o .= sz_branch_bx_group(
            't',
            'Treatment di Cabang Ini',
            'Nama treatment dan link halamannya (link boleh kosong).',
            $treat_rows ? $treat_rows : array(),
            array(array('name', 'Nama treatment', '', 'input'), array('url', 'https://sozoskinclinic.com/...', '', 'input')),
            function ($row) {
                return array(
                    array('name', 'Nama treatment', isset($row['name']) ? (string) $row['name'] : '', 'input'),
                    array('url', 'https://sozoskinclinic.com/...', isset($row['url']) ? (string) $row['url'] : '', 'input'),
                );
            },
            'Tambah treatment'
        );

        $faq_rows = sz_branch_decode_json($m('sz_faq'));
        $o .= sz_branch_bx_group(
            'q',
            'FAQ',
            'Teks polos satu paragraf. Teks yang sama dipakai di schema FAQPage. Maksimal 10.',
            $faq_rows ? $faq_rows : array(),
            array(array('q', 'Pertanyaan', '', 'input'), array('a', 'Jawaban', '', 'textarea')),
            function ($row) {
                return array(
                    array('q', 'Pertanyaan', isset($row['q']) ? (string) $row['q'] : '', 'input'),
                    array('a', 'Jawaban', isset($row['a']) ? (string) $row['a'] : '', 'textarea'),
                );
            },
            'Tambah pertanyaan'
        );

        $near = sz_branch_decode_json($m('sz_nearby'));
        $o .= sz_branch_bx_input('Cabang Terdekat (ID halaman)', 'sz_nearby', $near ? implode(', ', array_map('intval', $near)) : '', 'Pisahkan dengan koma, maksimal 6. Kosong = otomatis dari halaman lain dengan parent yang sama.');

        echo $o . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput -- semua nilai sudah di-escape di helper
    }
}

if (!function_exists('sz_branch_metabox_save')) {
    function sz_branch_metabox_save($post_id, $post = null) {
        if (!isset($_POST['sz_branch_nonce'])) {
            return;
        }
        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['sz_branch_nonce'])), 'sz_branch_save')) {
            return;
        }
        if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || wp_is_post_revision($post_id) || !current_user_can('edit_post', $post_id)) {
            return;
        }
        try {
            $f = (isset($_POST['sz_branch_f']) && is_array($_POST['sz_branch_f'])) ? wp_unslash($_POST['sz_branch_f']) : array();
            $values = array();

            // Field tunggal: lewat sanitizer yang sama dengan REST.
            foreach (sz_branch_meta_defs() as $key => $def) {
                if (strpos($def[1], 'json_') === 0 || !array_key_exists($key, $f) || is_array($f[$key])) {
                    continue;
                }
                $raw = trim((string) $f[$key]);
                if (in_array($def[1], array('rating', 'lat', 'lng'), true)) {
                    $raw = str_replace(',', '.', $raw);
                }
                $values[$key] = sz_branch_sanitize($raw, $def[1]);
            }

            // Jam: salah isi tidak menghapus jam lama, cukup beri peringatan.
            $h = (isset($_POST['sz_branch_h']) && is_array($_POST['sz_branch_h'])) ? wp_unslash($_POST['sz_branch_h']) : array();
            $rows = array();
            foreach (array(1, 2, 3, 4, 5, 6, 0) as $day) {
                $r = (isset($h[$day]) && is_array($h[$day])) ? $h[$day] : array();
                $open = isset($r['open']) ? trim((string) $r['open']) : '';
                $close = isset($r['close']) ? trim((string) $r['close']) : '';
                if (!empty($r['closed'])) {
                    $rows[] = array('day' => $day, 'closed' => true);
                } elseif ($open !== '' || $close !== '') {
                    $rows[] = array('day' => $day, 'open' => $open, 'close' => $close);
                }
            }
            if (!$rows) {
                $values['sz_hours'] = '';
            } else {
                $clean_hours = sz_branch_clean_hours($rows);
                if ($clean_hours === '') {
                    set_transient('sz_branch_warn_' . $post_id . '_' . get_current_user_id(), 1, 120);
                } else {
                    $values['sz_hours'] = $clean_hours;
                }
            }

            // Repeater: galeri, treatment, FAQ.
            $g = (isset($_POST['sz_branch_g']) && is_array($_POST['sz_branch_g'])) ? wp_unslash($_POST['sz_branch_g']) : array();
            $rows = array();
            foreach ($g as $row) {
                $src = (is_array($row) && isset($row['src'])) ? trim((string) $row['src']) : '';
                if ($src === '') {
                    continue;
                }
                $item = ctype_digit($src) ? array('id' => (int) $src) : array('url' => $src);
                $item['alt'] = isset($row['alt']) ? (string) $row['alt'] : '';
                $item['label'] = isset($row['label']) ? (string) $row['label'] : '';
                $rows[] = $item;
            }
            $values['sz_gallery'] = sz_branch_clean_gallery($rows);

            $t = (isset($_POST['sz_branch_t']) && is_array($_POST['sz_branch_t'])) ? wp_unslash($_POST['sz_branch_t']) : array();
            $rows = array();
            foreach ($t as $row) {
                if (is_array($row) && !empty($row['name'])) {
                    $rows[] = array('name' => (string) $row['name'], 'url' => isset($row['url']) ? trim((string) $row['url']) : '');
                }
            }
            $values['sz_treatments'] = sz_branch_clean_treatments($rows);

            $q = (isset($_POST['sz_branch_q']) && is_array($_POST['sz_branch_q'])) ? wp_unslash($_POST['sz_branch_q']) : array();
            $rows = array();
            foreach ($q as $row) {
                if (is_array($row) && !empty($row['q']) && !empty($row['a'])) {
                    $rows[] = array('q' => (string) $row['q'], 'a' => (string) $row['a']);
                }
            }
            $values['sz_faq'] = sz_branch_clean_faq($rows);

            $near_raw = isset($f['sz_nearby']) && !is_array($f['sz_nearby']) ? trim((string) $f['sz_nearby']) : '';
            $near = ($near_raw === '') ? array() : preg_split('/[\s,;]+/', $near_raw);
            $values['sz_nearby'] = sz_branch_clean_ids($near);

            foreach ($values as $key => $value) {
                if ($value === '' || $value === null) {
                    delete_post_meta($post_id, $key);
                } else {
                    update_post_meta($post_id, $key, is_string($value) ? wp_slash($value) : $value);
                }
            }
        } catch (Throwable $e) {
            return;
        }
    }
    add_action('save_post_page', 'sz_branch_metabox_save', 10, 2);
}

if (!function_exists('sz_branch_metabox_is_screen')) {
    function sz_branch_metabox_is_screen() {
        if (!function_exists('get_current_screen')) {
            return false;
        }
        $screen = get_current_screen();
        return $screen && $screen->base === 'post' && $screen->post_type === 'page';
    }
}

if (!function_exists('sz_branch_metabox_media')) {
    function sz_branch_metabox_media() {
        if (sz_branch_metabox_is_screen()) {
            wp_enqueue_media();
        }
    }
    add_action('admin_enqueue_scripts', 'sz_branch_metabox_media');
}

if (!function_exists('sz_branch_metabox_assets')) {
    // CSS + JS meta box, hanya di layar edit halaman.
    function sz_branch_metabox_assets() {
        if (!sz_branch_metabox_is_screen()) {
            return;
        }
        echo '<style>' . <<<'SZBXCSS'
#sz-branch-box .sz-bx h4 { margin: 20px 0 6px !important; font-size: 14px !important; }
#sz-branch-box .sz-bx > h4:first-of-type { margin-top: 12px !important; }
#sz-branch-box .sz-bx-grid { display: grid !important; grid-template-columns: repeat(2, minmax(0, 1fr)) !important; gap: 0 16px !important; }
#sz-branch-box .sz-bx-field { margin: 0 0 12px !important; }
#sz-branch-box .sz-bx-field label span { display: block !important; margin-bottom: 3px !important; font-weight: 600 !important; }
#sz-branch-box .sz-bx-field input,
#sz-branch-box .sz-bx-field textarea { width: 100% !important; }
#sz-branch-box .sz-bx-field small { display: block !important; margin-top: 3px !important; color: #646970 !important; }
#sz-branch-box .sz-bx-hours { border-collapse: collapse !important; }
#sz-branch-box .sz-bx-hours th,
#sz-branch-box .sz-bx-hours td { padding: 4px 14px 4px 0 !important; text-align: left !important; }
#sz-branch-box .sz-bx-row { display: flex !important; flex-wrap: wrap !important; gap: 8px !important; align-items: flex-start !important; margin: 0 0 8px !important; }
#sz-branch-box .sz-bx-row input,
#sz-branch-box .sz-bx-row textarea { flex: 1 1 200px !important; min-width: 0 !important; }
#sz-branch-box .sz-bx-row .button-link-delete { flex: none !important; padding-top: 6px !important; }
@media (max-width: 782px) { #sz-branch-box .sz-bx-grid { grid-template-columns: minmax(0, 1fr) !important; } }
SZBXCSS
        . '</style><script>' . <<<'SZBXJS'
(function () {
  if (window.szBranchBoxReady) return;
  window.szBranchBoxReady = true;
  var counter = 1000;

  function addRow(group) {
    var holder = document.querySelector('#sz-branch-box [data-sz-group="' + group + '"]');
    var tpl = document.querySelector('#sz-branch-box template[data-sz-tpl="' + group + '"]');
    if (!holder || !tpl) return null;
    holder.insertAdjacentHTML('beforeend', tpl.innerHTML.replace(/__i__/g, String(counter++)));
    return holder.lastElementChild;
  }

  document.addEventListener('click', function (e) {
    if (!e.target.closest || !e.target.closest('#sz-branch-box')) return;

    var add = e.target.closest('[data-sz-add]');
    if (add) { addRow(add.getAttribute('data-sz-add')); return; }

    var rm = e.target.closest('[data-sz-remove]');
    if (rm) { var row = rm.closest('.sz-bx-row'); if (row) row.remove(); return; }

    if (e.target.closest('[data-sz-copy-hours]')) {
      var first = document.querySelector('#sz-branch-box .sz-bx-hours tr[data-day="1"]');
      if (!first) return;
      var closed = first.querySelector('input[type="checkbox"]').checked;
      var times = first.querySelectorAll('input[type="time"]');
      document.querySelectorAll('#sz-branch-box .sz-bx-hours tbody tr').forEach(function (tr) {
        tr.querySelector('input[type="checkbox"]').checked = closed;
        var t = tr.querySelectorAll('input[type="time"]');
        t[0].value = times[0].value;
        t[1].value = times[1].value;
      });
      return;
    }

    if (e.target.closest('[data-sz-media]')) {
      if (!window.wp || !wp.media) { alert('Media Library tidak tersedia di layar ini. Isi ID atau URL foto manual.'); return; }
      var frame = wp.media({ title: 'Pilih foto cabang', multiple: true, library: { type: 'image' }, button: { text: 'Tambah ke galeri' } });
      frame.on('select', function () {
        frame.state().get('selection').each(function (att) {
          var a = att.toJSON();
          var row = addRow('g');
          if (!row) return;
          row.querySelector('[data-f="src"]').value = String(a.id);
          row.querySelector('[data-f="alt"]').value = a.alt || '';
        });
      });
      frame.open();
    }
  });
})();
SZBXJS
        . '</script>';
    }
    add_action('admin_footer', 'sz_branch_metabox_assets');
}
