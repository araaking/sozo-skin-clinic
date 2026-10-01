<?php
/**
 * SOZO SKIN - ABOUT PAGE SCHEMA (dynamic)
 * File   : about-us-schema-markup.php
 * Pasang : WPCode > PHP Snippet > Run Everywhere
 * Kondisi: slug 'tentang-kami' sudah dijaga di dalam fungsi,
 *          jadi Smart Conditional Logic boleh OFF atau set Page = Tentang Kami.
 * Output : AboutPage + BreadcrumbList, 2 node, tanpa FAQ/CreativeWork.
 * Title + description ngikutin Yoast SEO halaman ini.
 */

// Jangan output dua kali kalau snippet kepasang ganda.
if (!function_exists('sozo_about_schema')) {

    add_action('wp_head', 'sozo_about_schema', 10);

    function sozo_about_schema() {
        if (!function_exists('is_page') || !is_page()) {
            return;
        }

        $obj  = get_queried_object();
        $slug = ($obj instanceof WP_Post) ? (string) $obj->post_name : '';
        if ($slug !== 'tentang-kami' && !is_page('tentang-kami')) {
            return;
        }

        $page_id  = get_queried_object_id();
        $home     = trailingslashit(home_url('/'));
        $org_id   = $home . '#organization';
        $site_id  = $home . '#website';
        $page_url = $page_id ? trailingslashit((string) get_permalink($page_id)) : $home . 'tentang-kami/';
        $web_id   = $page_url . '#webpage';
        $crumb_id = $page_url . '#breadcrumb';

        $locale = get_locale() ? str_replace('_', '-', (string) get_locale()) : 'id';
        // Samakan dengan konvensi repo (70+ halaman pakai 'id', bukan 'id-ID').
        $lang = (stripos((string) $locale, 'id') === 0) ? 'id' : ($locale ? $locale : 'id');

        // Judul + deskripsi ngikutin meta Yoast yang tampil di tab/browser.
        $page_name = sozo_about_meta_title($page_id);
        $page_desc = sozo_about_meta_desc($page_id);

        $data = array(
            '@context' => 'https://schema.org',
            '@graph'   => array(
                array(
                    '@type'       => 'AboutPage',
                    '@id'         => $web_id,
                    'url'         => $page_url,
                    'name'        => $page_name,
                    'description' => $page_desc,
                    'inLanguage'  => $lang,
                    'isPartOf'    => array('@id' => $site_id),
                    'about'       => array('@id' => $org_id),
                    'breadcrumb'  => array('@id' => $crumb_id),
                ),
                array(
                    '@type'            => 'BreadcrumbList',
                    '@id'              => $crumb_id,
                    'itemListElement'  => array(
                        array(
                            '@type'    => 'ListItem',
                            'position' => 1,
                            'name'     => 'Beranda',
                            'item'     => $home,
                        ),
                        array(
                            '@type'    => 'ListItem',
                            'position' => 2,
                            'name'     => 'Tentang Kami',
                        ),
                    ),
                ),
            ),
        );

        echo '<script type="application/ld+json">' . wp_json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>' . "\n";
    }
}

if (!function_exists('sozo_about_meta_title')) {

    // Urutan: Yoast halaman ini > judul page > document title > default.
    function sozo_about_meta_title($page_id) {
        $title = '';

        if ($page_id) {
            $t = (string) get_post_meta($page_id, '_yoast_wpseo_title', true);
            if ($t !== '') {
                $title = $t;
            }
            if ($title === '') {
                $title = (string) get_the_title($page_id);
            }
        }

        if ($title === '') {
            $title = (string) wp_get_document_title();
        }

        // Ganti variabel Yoast seperti %%sitename%% kalau API-nya tersedia.
        if (strpos($title, '%%') !== false && function_exists('wpseo_replace_vars')) {
            $post  = $page_id ? get_post($page_id) : null;
            $title = (string) wpseo_replace_vars($title, $post);
        }

        $title = html_entity_decode(wp_strip_all_tags($title), ENT_QUOTES, 'UTF-8');
        $title = trim((string) preg_replace('/\s+/u', ' ', $title));

        if ($title === '') {
            $title = 'Tentang Sozo Skin Clinic';
        }

        return $title;
    }
}

if (!function_exists('sozo_about_meta_desc')) {

    // Urutan: Yoast halaman ini > excerpt page > default.
    function sozo_about_meta_desc($page_id) {
        $desc = '';

        if ($page_id) {
            $d = (string) get_post_meta($page_id, '_yoast_wpseo_metadesc', true);
            if ($d !== '') {
                $desc = $d;
            }
            if ($desc === '') {
                $excerpt = (string) get_post_field('post_excerpt', $page_id);
                if ($excerpt !== '') {
                    $desc = $excerpt;
                }
            }
        }

        if (strpos($desc, '%%') !== false && function_exists('wpseo_replace_vars')) {
            $post = $page_id ? get_post($page_id) : null;
            $desc = (string) wpseo_replace_vars($desc, $post);
        }

        $desc = html_entity_decode(wp_strip_all_tags($desc), ENT_QUOTES, 'UTF-8');
        $desc = trim((string) preg_replace('/\s+/u', ' ', $desc));

        if ($desc === '') {
            $desc = 'Klinik kecantikan medis terbaik & tepercaya dengan 75+ lokasi di Indonesia. Menghadirkan perawatan yang aman, memberikan hasil nyata, dan ditangani langsung oleh dokter ahli profesional.';
        }

        return $desc;
    }
}
