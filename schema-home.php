<?php
/**
 * SOZO SKIN - HOMEPAGE SCHEMA (dynamic)
 * File   : schema-home.php
 * Pasang : WPCode > PHP Snippet > Run Everywhere
 * Kondisi: is_front_page() sudah dijaga di dalam fungsi,
 *          jadi Smart Conditional Logic boleh OFF atau set Is Homepage.
 * Output : Organization + WebSite + WebPage, 3 node, tanpa FAQ/Breadcrumb.
 */

// Jangan output dua kali kalau snippet kepasang ganda.
if (!function_exists('sozo_homepage_schema')) {

    add_action('wp_head', 'sozo_homepage_schema', 10);

    function sozo_homepage_schema() {
        if (!is_front_page()) {
            return;
        }

        $home      = trailingslashit(home_url('/'));
        $org_id    = $home . '#organization';
        $site_id   = $home . '#website';
        $logo_id   = $home . '#/schema/logo/image/';
        $locale    = get_locale() ? str_replace('_', '-', (string) get_locale()) : 'id';
        // Samakan dengan konvensi repo (70+ halaman pakai 'id', bukan 'id-ID').
        $lang      = (stripos((string) $locale, 'id') === 0) ? 'id' : ($locale ? $locale : 'id');

        // Judul + deskripsi ngikutin meta SEO yang tampil di tab/browser.
        $page_name = sozo_home_meta_title();
        $page_desc = sozo_home_meta_desc();

        // Logo: pakai custom_logo kalau theme punya, fallback ke asset yang dikonfirmasi.
        $logo_url = 'https://asset.sozoskinclinic.com/wp-content/uploads/2026/09/logo-sozo.webp';
        $custom_logo_id = (int) get_theme_mod('custom_logo');
        if ($custom_logo_id) {
            $src = wp_get_attachment_image_src($custom_logo_id, 'full');
            if (!empty($src[0])) {
                $logo_url = $src[0];
            }
        }

        $data = array(
            '@context' => 'https://schema.org',
            '@graph'   => array(
                array(
                    '@type'         => 'Organization',
                    '@id'           => $org_id,
                    'name'          => 'Sozo Skin Clinic',
                    'alternateName' => 'Sozo Clinic',
                    'url'           => $home,
                    'description'   => 'Sozo Skin Clinic adalah jaringan klinik kecantikan premium di Indonesia dengan 78 cabang yang tersebar di Jabodetabek, Jawa, Sumatera, Bali, Kalimantan, dan Sulawesi. Klinik ini menawarkan berbagai perawatan, seperti skin booster, laser treatment, dan facial, yang didukung oleh dokter ahli dengan harga terjangkau.',
                    'foundingDate'  => '2022',
                    'logo'          => array(
                        '@type'      => 'ImageObject',
                        '@id'        => $logo_id,
                        'url'        => $logo_url,
                        'contentUrl' => $logo_url,
                        'caption'    => 'Sozo Skin Clinic',
                        'inLanguage' => 'id',
                    ),
                    'contactPoint'  => array(
                        '@type'            => 'ContactPoint',
                        'telephone'        => '+6285175225664',
                        'email'            => 'info@sozoskinclinic.com',
                        'contactType'      => 'customer service',
                        'availableLanguage' => 'Indonesian',
                    ),
                    'areaServed'    => array(
                        '@type' => 'Country',
                        'name'  => 'Indonesia',
                    ),
                    'sameAs'        => array(
                        'https://www.facebook.com/p/Sozo-Skin-Clinic-100089987562109/',
                        'https://www.instagram.com/sozo.skinclinic/',
                        'https://www.tiktok.com/@sozo.skin.clinic',
                    ),
                ),
                array(
                    '@type'         => 'WebSite',
                    '@id'           => $site_id,
                    'url'           => $home,
                    'name'          => 'Sozo Skin Clinic',
                    'alternateName' => 'Sozo Clinic',
                    'inLanguage'    => $lang,
                    'publisher'     => array('@id' => $org_id),
                ),
                array(
                    '@type'       => 'WebPage',
                    '@id'         => $home,
                    'url'         => $home,
                    'name'        => $page_name,
                    'description' => $page_desc,
                    'inLanguage'  => $lang,
                    'isPartOf'    => array('@id' => $site_id),
                    'about'       => array('@id' => $org_id),
                ),
            ),
        );

        echo '<script type="application/ld+json">' . wp_json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>' . "\n";
    }
}

if (!function_exists('sozo_home_meta_title')) {

    // Urutan: Yoast > RankMath > judul front page > document title > nama situs.
    function sozo_home_meta_title() {
        $front_id = (int) get_option('page_on_front');
        $title = '';

        if ($front_id) {
            $t = (string) get_post_meta($front_id, '_yoast_wpseo_title', true);
            if ($t !== '') {
                $title = $t;
            }
            if ($title === '') {
                $t = (string) get_post_meta($front_id, 'rank_math_title', true);
                if ($t !== '') {
                    $title = $t;
                }
            }
            if ($title === '') {
                $title = (string) get_the_title($front_id);
            }
        }

        if ($title === '') {
            $title = (string) wp_get_document_title();
        }
        if ($title === '') {
            $title = (string) get_bloginfo('name');
        }

        // Ganti variabel Yoast seperti %%sitename%% kalau API-nya tersedia.
        if (strpos($title, '%%') !== false && function_exists('wpseo_replace_vars')) {
            $post = $front_id ? get_post($front_id) : null;
            $title = (string) wpseo_replace_vars($title, $post);
        }

        $title = html_entity_decode(wp_strip_all_tags($title), ENT_QUOTES, 'UTF-8');
        $title = trim((string) preg_replace('/\s+/u', ' ', $title));

        if ($title === '') {
            $title = 'Sozo Skin Clinic - Klinik Kecantikan Terdekat, Harga Terjangkau';
        }

        // Judul mentah nama situs saja (tanpa meta spesifik) bukan judul homepage.
        // Kembalikan ke default panjang supaya cocok dengan contoh statis.
        if ($title === (string) get_bloginfo('name')) {
            $title = 'Sozo Skin Clinic - Klinik Kecantikan Terdekat, Harga Terjangkau';
        }

        return $title;
    }
}

if (!function_exists('sozo_home_meta_desc')) {

    // Urutan: Yoast > RankMath > excerpt front page > tagline > default.
    function sozo_home_meta_desc() {
        $front_id = (int) get_option('page_on_front');
        $desc = '';

        if ($front_id) {
            $d = (string) get_post_meta($front_id, '_yoast_wpseo_metadesc', true);
            if ($d !== '') {
                $desc = $d;
            }
            if ($desc === '') {
                $d = (string) get_post_meta($front_id, 'rank_math_description', true);
                if ($d !== '') {
                    $desc = $d;
                }
            }
            if ($desc === '') {
                $excerpt = (string) get_post_field('post_excerpt', $front_id);
                if ($excerpt !== '') {
                    $desc = $excerpt;
                }
            }
        }

        if ($desc === '') {
            $desc = (string) get_bloginfo('description');
        }

        if (strpos($desc, '%%') !== false && function_exists('wpseo_replace_vars')) {
            $post = isset($front_id) && $front_id ? get_post($front_id) : null;
            $desc = (string) wpseo_replace_vars($desc, $post);
        }

        $desc = html_entity_decode(wp_strip_all_tags($desc), ENT_QUOTES, 'UTF-8');
        $desc = trim((string) preg_replace('/\s+/u', ' ', $desc));

        if ($desc === '') {
            $desc = 'Klinik kecantikan terdekat dan terbaik. Laser Treatment, Facial, Hair Removal, HIFU, Meso, dan Infus Whitening bersama dokter berpengalaman.';
        }

        return $desc;
    }
}
