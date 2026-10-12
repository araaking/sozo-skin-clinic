/**
 * Plugin Name: SOZO Skin Promo Engine - Full Clean Admin & UI
 * Description: Bulk upload gambar promo, Master Shortcode, Modular Shortcode, Category Shortcode, Strict CSS & Exact WA Link
 */

if (!defined('ABSPATH')) exit;

// ============ 1. REGISTER CUSTOM POST TYPE & TAXONOMY ============
add_action('init', function() {
    $labels = array(
        'name'          => 'Promo SOZO',
        'singular_name' => 'Promo',
        'menu_name'     => 'Promo SOZO',
    );

    $args = array(
        'labels'             => $labels,
        'public'             => false,
        'publicly_queryable' => false,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'has_archive'        => false,
        'menu_icon'          => 'dashicons-tickets-alt',
        'supports'           => array('title'),
        'capabilities'       => array(
            'create_posts' => 'do_not_allow',
        ),
        'map_meta_cap'       => true,
    );
    register_post_type('sozo_promo', $args);

    register_taxonomy('promo_category', 'sozo_promo', array(
        'label'             => 'Kategori Promo',
        'labels'            => array(
            'name'          => 'Kategori Promo',
            'singular_name' => 'Kategori Promo',
        ),
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'rewrite'           => array('slug' => 'kategori-promo'),
    ));
});

// ============ 2. BERSIHKAN SUBMENU YANG TIDAK TERPAKAI ============
add_action('admin_menu', function() {
    remove_submenu_page('edit.php?post_type=sozo_promo', 'edit.php?post_type=sozo_promo');
}, 999);


// ============ 3. NATIVE GALLERY FIELD DI KATEGORI ============
add_action('promo_category_add_form_fields', 'sozo_promo_taxonomy_add_meta_fields', 10, 2);
add_action('promo_category_edit_form_fields', 'sozo_promo_taxonomy_edit_meta_fields', 10, 2);

function sozo_promo_taxonomy_add_meta_fields($taxonomy) {
    ?>
    <div class="form-field term-group">
        <label for="sozo_promo_gallery">Gambar Promo (Upload Banyak Sekaligus)</label>
        <input type="hidden" id="sozo_promo_gallery" name="sozo_promo_gallery" value="">
        <div id="sozo-gallery-preview" style="display:flex; flex-wrap:wrap; gap:10px; margin-bottom:10px;"></div>
        <button type="button" class="button button-secondary" id="sozo-upload-gallery-btn">Pilih / Upload Gambar Promo</button>
    </div>
    <?php
}

function sozo_promo_taxonomy_edit_meta_fields($term, $taxonomy) {
    $gallery_ids = get_term_meta($term->term_id, 'sozo_promo_gallery', true);
    ?>
    <tr class="form-field term-group-wrap">
        <th scope="row"><label for="sozo_promo_gallery">Gambar Promo (Upload Banyak Sekaligus)</label></th>
        <td>
            <input type="hidden" id="sozo_promo_gallery" name="sozo_promo_gallery" value="<?php echo esc_attr($gallery_ids); ?>">
            <div id="sozo-gallery-preview" style="display:flex; flex-wrap:wrap; gap:10px; margin-bottom:10px;">
                <?php
                if (!empty($gallery_ids)) {
                    $ids = explode(',', $gallery_ids);
                    foreach ($ids as $id) {
                        $url = wp_get_attachment_image_url($id, 'thumbnail');
                        if ($url) {
                            echo '<div style="position:relative;" data-id="' . esc_attr($id) . '"><img src="' . esc_url($url) . '" style="width:80px;height:80px;object-fit:cover;border-radius:6px;border:1px solid #ccc;"><span class="sozo-remove-img" style="position:absolute;top:-5px;right:-5px;background:red;color:#fff;border-radius:50%;width:18px;height:18px;text-align:center;line-height:16px;cursor:pointer;font-size:11px;font-weight:bold;">&times;</span></div>';
                        }
                    }
                }
                ?>
            </div>
            <button type="button" class="button button-secondary" id="sozo-upload-gallery-btn">Pilih / Upload Gambar Promo</button>
        </td>
    </tr>
    <?php
}

add_action('created_promo_category', 'sozo_save_taxonomy_custom_meta', 10, 2);
add_action('edited_promo_category', 'sozo_save_taxonomy_custom_meta', 10, 2);

function sozo_save_taxonomy_custom_meta($term_id, $tt_id) {
    if (isset($_POST['sozo_promo_gallery'])) {
        update_term_meta($term_id, 'sozo_promo_gallery', sanitize_text_field($_POST['sozo_promo_gallery']));
    }
}

add_action('admin_enqueue_scripts', function($hook) {
    if ('edit-tags.php' === $hook || 'term.php' === $hook) {
        wp_enqueue_media();
        add_action('admin_footer', function() {
            ?>
            <script>
            jQuery(document).ready(function($){
                var frame;
                $('#sozo-upload-gallery-btn').on('click', function(e){
                    e.preventDefault();
                    if (frame) { frame.open(); return; }
                    frame = wp.media({
                        title: 'Pilih / Upload Gambar Promo',
                        button: { text: 'Gunakan Gambar Ini' },
                        multiple: true
                    });
                    frame.on('select', function(){
                        var selection = frame.state().get('selection');
                        var ids = $('#sozo_promo_gallery').val() ? $('#sozo_promo_gallery').val().split(',') : [];
                        
                        selection.map(function(attachment){
                            attachment = attachment.toJSON();
                            if (ids.indexOf(attachment.id.toString()) === -1) {
                                ids.push(attachment.id);
                                var thumb = attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;
                                $('#sozo-gallery-preview').append('<div style="position:relative;" data-id="'+attachment.id+'"><img src="'+thumb+'" style="width:80px;height:80px;object-fit:cover;border-radius:6px;border:1px solid #ccc;"><span class="sozo-remove-img" style="position:absolute;top:-5px;right:-5px;background:red;color:#fff;border-radius:50%;width:18px;height:18px;text-align:center;line-height:16px;cursor:pointer;font-size:11px;font-weight:bold;">&times;</span></div>');
                            }
                        });
                        $('#sozo_promo_gallery').val(ids.join(','));
                    });
                    frame.open();
                });

                $(document).on('click', '.sozo-remove-img', function(){
                    var parent = $(this).parent();
                    var id = parent.data('id').toString();
                    var ids = $('#sozo_promo_gallery').val().split(',');
                    ids = ids.filter(function(val){ return val !== id; });
                    $('#sozo_promo_gallery').val(ids.join(','));
                    parent.remove();
                });
            });
            </script>
            <?php
        });
    }
});


// ============ 4. HELPER GLOBAL ============
function sozo_get_wa_link() {
    return "https://api.whatsapp.com/send?phone=6285175225664&text=Halo%2C%20saya%20mau%20booking%20promo%20treatment%20%20di%20Sozo%20%5Bsrc%3AORG-Promo%5D";
}

function sozo_render_category_gallery_slider($category_slug, $custom_wa_link = '') {
    $images = array();
    $is_general = ($category_slug === 'general-promo');
    $term = get_term_by('slug', $category_slug, 'promo_category');
    $category_name = $term && !is_wp_error($term) ? $term->name : 'Promo';
    $wa_link = $custom_wa_link ? $custom_wa_link : sozo_get_wa_link();

    // Data statis khusus General Promo
    if ($is_general) {
        $images = array(
            array('url' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2026/08/WhatsApp-Image-2026-08-28-at-15.25.53.webp', 'alt' => 'General Promo SOZO Skin'),
            array('url' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2026/08/WhatsApp-Image-2026-08-28-at-15.25.53-1.webp', 'alt' => 'General Promo SOZO Skin'),
            array('url' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2026/09/SEO-Banner-SKIN-Agustus-2026-16x9-8.webp', 'alt' => 'General Promo SOZO Skin'),
            array('url' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2026/09/SEO-Banner-SKIN-Agustus-2026-16x9-15.webp', 'alt' => 'General Promo SOZO Skin'),
        );
    } else {
        if ($term) {
            $gallery_ids = get_term_meta($term->term_id, 'sozo_promo_gallery', true);
            if (!empty($gallery_ids)) {
                $ids = explode(',', $gallery_ids);
                foreach ($ids as $id) {
                    $url = wp_get_attachment_image_url($id, 'full');
                    $alt = get_post_meta($id, '_wp_attachment_image_alt', true);
                    if ($url) {
                        $images[] = array(
                            'url' => $url,
                            'alt' => $alt ? $alt : 'Promo SOZO Skin'
                        );
                    }
                }
            }
        }
    }

    if (empty($images)) {
        return '<p class="sz-promo-empty">Belum ada promo aktif untuk kategori ini.</p>';
    }

    ob_start();
    ?>
    <div class="sz-promo-slider-container <?php echo $is_general ? 'sz-slider-general' : ''; ?>">
        <?php if (!$is_general): ?>
        <button class="sz-promo-nav-btn prev" aria-label="Promo sebelumnya" type="button">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
        </button>
        <?php endif; ?>

        <div class="sz-promo-grid">
            <?php 
            $i = 0;
            foreach ($images as $img) : 
            $i++;
            $image_alt = !empty($img['alt']) && $img['alt'] !== 'Promo SOZO Skin' && $img['alt'] !== 'General Promo SOZO Skin'
                ? $img['alt']
                : 'Promo ' . $category_name . ' pilihan ' . $i;
            $card_class = $is_general ? 'sz-promo-card sz-card-banner' : 'sz-promo-card';
            ?>
                <article class="<?php echo esc_attr($card_class); ?>">
                    <button class="sz-promo-image-trigger" type="button" aria-label="Perbesar gambar <?php echo esc_attr($image_alt); ?>" data-image-src="<?php echo esc_url($img['url']); ?>" data-image-alt="<?php echo esc_attr($image_alt); ?>">
                        <img src="<?php echo esc_url($img['url']); ?>" alt="<?php echo esc_attr($image_alt); ?>" loading="lazy">
                    </button>
                    <a class="sz-promo-claim-btn" href="<?php echo esc_url($wa_link); ?>" target="_blank" rel="noopener noreferrer">
                        Klaim Promo
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </a>
                </article>
            <?php endforeach; ?>
        </div>

        <?php if (!$is_general): ?>
        <button class="sz-promo-nav-btn next" aria-label="Promo berikutnya" type="button">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
        </button>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}


// ============ 5. SHORTCODES MODULAR ============

// 5.1. Hero Shortcode
add_shortcode('sozo_promo_hero', function() {
    $wa_link = sozo_get_wa_link();
    ob_start(); ?>
    <div class="sozo-promo-wrapper">
        <section class="hero">
            <div class="wrap hero-grid">
                <div>
                    <nav class="sozo-promo-breadcrumb" aria-label="Breadcrumb">
                        <ol>
                            <li><a href="https://sozoskinclinic.com/">Beranda</a></li>
                            <li><span aria-current="page">Promo</span></li>
                        </ol>
                    </nav>
                    <span class="hero-eyebrow">Promo Bulan Ini</span>
                    <h1>Bisa cantik<br>tanpa <em>bikin ragu.</em></h1>
                    <p>Diskon treatment wajah, tubuh, dan rambut — plus cicilan 0% dan cashback hingga Rp500.000 untuk booking bulan ini.</p>
                    <div class="hero-actions">
                        <a href="#all-promos" class="btn-primary">Lihat Promo</a>
                        <a href="<?php echo esc_url($wa_link); ?>" target="_blank" rel="noopener noreferrer" class="btn-ghost">Konsultasi Gratis</a>
                    </div>
                </div>
                <div class="hero-visual">
                    <div class="hero-poster">
                        <img src="https://asset.sozoskinclinic.com/wp-content/uploads/2026/09/diskon-potongan-100rb.webp" alt="Promo SOZO Skin Potongan 100rb" width="800" height="800" fetchpriority="high" decoding="async">
                    </div>
                    <div class="hero-tag">Promo Terbatas</div>
                </div>
            </div>
        </section>
    </div>
    <?php return ob_get_clean();
});

// 5.2. Special Shortcode
add_shortcode('sozo_promo_special', function() {
    $wa_link = sozo_get_wa_link();
    ob_start(); ?>
    <div class="sozo-promo-wrapper">
        <section class="section" id="promo-special">
            <div class="wrap">
                <div class="special">
                    <div class="special-inner">
                        <div class="special-content">
                            <span class="section-eyebrow">Promo Pembayaran</span>
                            <h2>Cicilan <em>0%</em>,<br>cashback sampai 500rb</h2>
                            <p>Bayar treatment favoritmu lebih ringan pakai paylater atau kartu kredit pilihan, tanpa bunga tambahan.</p>
                            <div class="pay-logos">
                                <span>Indodana</span><span>Kredivo</span><span>Atome</span><span>SPayLater</span><span>BRI</span><span>BCA</span><span>Mandiri</span>
                            </div>
                            <a href="<?php echo esc_url($wa_link); ?>" target="_blank" rel="noopener noreferrer" class="special-cta"><span class="special-cta-desktop">Klaim Promo Pembayaran</span><span class="special-cta-mobile">Ambil Promo</span></a>
                        </div>
                        <div class="special-visual">
                            <img src="https://asset.sozoskinclinic.com/wp-content/uploads/2026/09/cicilan0.webp" alt="Cicilan 0% SOZO Skin" loading="lazy" decoding="async">
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <?php return ob_get_clean();
});

// 5.3. List All Promos Shortcode
add_shortcode('sozo_promo_list', function() {
    $categories = array(
        'general-promo'    => array('title' => 'General Promo', 'id' => 'general-promo'),
        'single-treatment' => array('title' => 'Promo Single Treatment', 'id' => 'single-treatment'),
        'skin-treatment'   => array('title' => 'Skin Treatment', 'id' => 'skin-treatment'),
        'acne-scar-free'   => array('title' => 'Acne & Scar Free', 'id' => 'acne-scar-free'),
        'hair-treatment'   => array('title' => 'Hair Treatment', 'id' => 'hair-treatment'),
        'body-slimming'    => array('title' => 'Body Slimming', 'id' => 'body-slimming'),
    );
    ob_start(); ?>
    <div class="sozo-promo-wrapper">
        <section class="section" id="all-promos">
            <div class="wrap">
                <div class="section-head">
                    <div>
                        <span class="section-eyebrow">Semua Promo</span>
                        <h2>Pilih Sesuai Kebutuhanmu</h2>
                    </div>
                    <p>Dapatkan penawaran harga terbaik untuk perawatan impianmu bulan ini.</p>
                </div>
                <nav class="cat-nav">
                    <?php foreach ($categories as $slug => $cat): ?>
                        <a href="#<?php echo esc_attr($cat['id']); ?>"><?php echo esc_html($cat['title']); ?></a>
                    <?php endforeach; ?>
                </nav>
                <?php foreach ($categories as $slug => $cat): ?>
                    <div class="cat-block" id="<?php echo esc_attr($cat['id']); ?>">
                        <div class="cat-block-head">
                            <h3><?php echo esc_html($cat['title']); ?></h3>
                        </div>
                        <?php echo sozo_render_category_gallery_slider($slug); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
    <?php return ob_get_clean();
});

// 5.4. Claim Steps Shortcode
add_shortcode('sozo_promo_claim', function() {
    ob_start(); ?>
    <div class="sozo-promo-wrapper">
        <section class="section" id="claim">
            <div class="wrap">
                <div class="claim">
                    <div class="claim-inner">
                        <div class="section-head">
                            <div>
                                <span class="section-eyebrow">Gampang Kok</span>
                                <h2>Cara Klaim Promo</h2>
                            </div>
                            <p>Dari pilih promo sampai treatment, cuma 4 langkah.</p>
                        </div>
                        <div class="claim-grid">
                            <div class="claim-step">
                                <div class="claim-num">01</div>
                                <h3>Pilih Promo</h3>
                                <p>Pilih promo yang sesuai dengan kebutuhanmu.</p>
                            </div>
                            <div class="claim-step">
                                <div class="claim-num">02</div>
                                <h3>Booking via WA</h3>
                                <p>Tekan tombol Klaim Promo pada kartu untuk langsung menghubungi tim kami via WhatsApp.</p>
                            </div>
                            <div class="claim-step">
                                <div class="claim-num">03</div>
                                <h3>Datang ke Klinik</h3>
                                <p>Tunjukkan konfirmasi booking ke resepsionis di outlet SOZO Skin pilihanmu.</p>
                            </div>
                            <div class="claim-step">
                                <div class="claim-num">04</div>
                                <h3>Nikmati Treatment</h3>
                                <p>Harga promo otomatis berlaku di kasir, tanpa proses tambahan.</p>
                            </div>
                        </div>
                        <div class="claim-note">
                            <p>* <strong>Syarat & Ketentuan:</strong> Berlaku untuk booking periode bulan ini. Harga belum termasuk service charge 5% (maks. Rp150.000). Masa berlaku paket perawatan: 6 bulan dari tanggal pembelian. Promo tersedia hanya di outlet tertentu.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <?php return ob_get_clean();
});

// 5.5. CTA Shortcode
add_shortcode('sozo_promo_cta', function() {
    $wa_link = sozo_get_wa_link();
    ob_start(); ?>
    <div class="sozo-promo-wrapper">
        <section class="final-cta">
            <div class="wrap">
                <h2>Masih bingung pilih promo yang mana?</h2>
                <p>Konsultasi gratis dengan tim kami, kami bantu rekomendasikan treatment yang paling pas.</p>
                <a href="<?php echo esc_url($wa_link); ?>" target="_blank" rel="noopener noreferrer" class="btn-primary">Chat via WhatsApp</a>
            </div>
        </section>
    </div>
    <?php return ob_get_clean();
});

// 5.6. Master Shortcode
add_shortcode('sozo_promo_landing', function() {
    ob_start();
    ?>
    <div class="sozo-promo-master-container">
        <?php 
        echo do_shortcode('[sozo_promo_hero]');
        echo do_shortcode('[sozo_promo_special]');
        echo do_shortcode('[sozo_promo_list]');
        echo do_shortcode('[sozo_promo_claim]');
        echo do_shortcode('[sozo_promo_cta]');
        ?>
        
        <!-- FLOATING BUTTON -->
        <div class="sozo-promo-wrapper">
            <button class="sz-floating-promo-btn" type="button">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true">
                    <polyline points="20 12 20 22 4 22 4 12"></polyline>
                    <rect x="2" y="7" width="20" height="5"></rect>
                    <line x1="12" y1="22" x2="12" y2="7"></line>
                    <path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"></path>
                    <path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"></path>
                </svg>
                Klaim Cashback s.d. 500rb
            </button>
        </div>
    </div>
    <?php
    return ob_get_clean();
});

// 5.7. Shortcode Per Kategori Spesifik
add_shortcode('sozo_promo_kategori', function($atts) {
    $atts = shortcode_atts(array(
        'slug'        => '', 
        'title'       => '',
        'wa_link'     => '',
        'subtitle'    => 'Wujudkan Kulit Impian dengan Cicilan 0%! Klaim Promonya Sekarang, Syarat & Ketentuan Berlaku.',
        'promo_url'   => 'https://sozoskinclinic.com/promo/',
        'link_text'   => 'Lihat Promo Lengkapnya',
        'show_header' => 'true'
    ), $atts);

    if (empty($atts['slug'])) {
        return '<p style="color:red;">Error: Masukkan slug kategori. Contoh: [sozo_promo_kategori slug="skin-treatment"]</p>';
    }

    $title = $atts['title'];
    if (empty($title)) {
        $term = get_term_by('slug', $atts['slug'], 'promo_category');
        $title = $term ? $term->name : 'Promo';
    }

    $final_wa_link = !empty($atts['wa_link']) ? $atts['wa_link'] : sozo_get_wa_link();
    $show_header = filter_var($atts['show_header'], FILTER_VALIDATE_BOOLEAN);

    ob_start();
    ?>
    <div class="sozo-promo-wrapper sozo-promo-kategori-wrapper">
        <div class="cat-block" id="<?php echo esc_attr($atts['slug']); ?>" data-custom-wa="<?php echo esc_url($final_wa_link); ?>">
            <?php if ($show_header): ?>
            <div class="sz-promo-header">
                <div class="sz-promo-text">
                    <h2 class="sz-promo-title"><?php echo esc_html($title); ?></h2>
                    <p class="sz-promo-subtitle"><?php echo esc_html($atts['subtitle']); ?></p>
                </div>
                <a href="<?php echo esc_url($atts['promo_url']); ?>" target="_blank" rel="noopener" class="sz-promo-link-desktop">
                    <?php echo esc_html($atts['link_text']); ?>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>
                </a>
            </div>
            <?php else: ?>
            <div class="cat-block-head">
                <h3><?php echo esc_html($title); ?></h3>
            </div>
            <?php endif; ?>

            <?php echo sozo_render_category_gallery_slider($atts['slug'], $final_wa_link); ?>

            <?php if ($show_header): ?>
            <div class="sz-promo-footer mobile-only">
                <a href="<?php echo esc_url($atts['promo_url']); ?>" target="_blank" rel="noopener" class="sz-promo-btn-mobile">
                    <?php echo esc_html($atts['link_text']); ?>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
});


// ============ 6. INJECT STRICT CSS & FONT ============
add_action('wp_head', function() {
    if (!is_page('promo')) return;
    ?>
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@graph": [
        {
          "@type": "WebPage",
          "@id": "https://sozoskinclinic.com/promo/#webpage",
          "url": "https://sozoskinclinic.com/promo/",
          "name": "Promo Sozo Skin Clinic",
          "description": "Lihat promo perawatan kulit, tubuh, dan rambut terbaru dari Sozo Skin Clinic.",
          "inLanguage": "id",
          "isPartOf": { "@id": "https://sozoskinclinic.com/#website" },
          "about": { "@id": "https://sozoskinclinic.com/#organization" },
          "breadcrumb": { "@id": "https://sozoskinclinic.com/promo/#breadcrumb" }
        },
        {
          "@type": "BreadcrumbList",
          "@id": "https://sozoskinclinic.com/promo/#breadcrumb",
          "itemListElement": [
            {
              "@type": "ListItem",
              "position": 1,
              "name": "Beranda",
              "item": "https://sozoskinclinic.com/"
            },
            {
              "@type": "ListItem",
              "position": 2,
              "name": "Promo"
            }
          ]
        }
      ]
    }
    </script>
    <?php
}, 20);

add_action('wp_head', function() {
    ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style id="sozo-promo-inline-css">
        /* ============================================================
           Sozo Design System V1.2 (design-system/), Elementor Safe.
           Token di-scope ke wrapper promo + lightbox, tidak di :root,
           supaya tidak bentrok dengan halaman lain.
           ============================================================ */
        .sozo-promo-wrapper,
        .sz-promo-lightbox {
            --color-brand-primary: #1A2080;
            --color-brand-secondary: #D9C7B1;
            --color-brand-almond: #E9DFD5;
            --color-white: #FFFFFF;
            --color-text-primary: #171715;
            --color-text-secondary: #41413E;
            --color-text-muted: #777771;
            --color-text-on-primary: #FFFFFF;
            --color-border: #DFDBD4;
            --color-border-strong: #C8C3BA;
            --color-primary-hover: #151A6D;
            --color-secondary-hover: #CBB79F;
            --color-primary-subtle: rgba(26, 32, 128, 0.05);
            --overlay-modal: rgba(23, 23, 21, 0.88);

            --font-family-primary: "Poppins", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            --text-h1: clamp(38px, 33.856px + 1.105vw, 48px);
            --text-h2: clamp(32px, 28.685px + 0.884vw, 40px);
            --text-h3: clamp(28px, 26.343px + 0.442vw, 32px);
            --text-h5: 20px;
            --text-body-lg: 18px;
            --text-body: 16px;
            --text-body-sm: 14px;
            --text-caption: 12px;
            --text-label: 12px;
            --leading-h1: 1.15;
            --leading-h2: 1.20;
            --leading-h3: 1.25;
            --leading-h5: 1.35;
            --leading-body: 1.60;
            --leading-body-sm: 1.55;
            --leading-caption: 1.40;
            --tracking-eyebrow: 0.12em;
            --tracking-label: 0.25em;

            --space-1: 4px;
            --space-2: 8px;
            --space-3: 12px;
            --space-4: 16px;
            --space-5: 24px;
            --space-6: 32px;
            --space-7: 40px;
            --space-8: 48px;
            --space-9: 64px;

            --container-pad: 32px;
            --grid-gap: 24px;

            --radius-sm: 6px;
            --radius-md: 10px;
            --radius-lg: 16px;
            --radius-xl: 24px;
            --radius-full: 999px;
            --shadow-sm: 0 2px 8px rgba(23, 23, 21, 0.04);
            --shadow-md: 0 8px 24px rgba(23, 23, 21, 0.07);
            --shadow-lg: 0 16px 40px rgba(23, 23, 21, 0.10);
            --focus-ring: 2px solid var(--color-brand-primary);
            --focus-ring-inverse: 2px solid var(--color-white);
            --focus-offset: 3px;

            --duration-fast: 180ms;
            --duration-card: 220ms;
            --duration-media: 400ms;
            --ease-standard: ease;
        }
        @media (max-width: 1024px) {
            .sozo-promo-wrapper { --grid-gap: 20px; }
        }
        @media (max-width: 768px) {
            .sozo-promo-wrapper { --container-pad: 20px; --grid-gap: 16px; }
        }
        @media (prefers-reduced-motion: reduce) {
            .sozo-promo-wrapper,
            .sz-promo-lightbox { --duration-fast: 0ms; --duration-card: 0ms; --duration-media: 0ms; }
        }

        .sozo-promo-wrapper {
            background: var(--color-white) !important;
            color: var(--color-text-secondary) !important;
            font-family: var(--font-family-primary) !important;
            line-height: var(--leading-body) !important;
        }
        .sozo-promo-wrapper *,
        .sozo-promo-wrapper *::before,
        .sozo-promo-wrapper *::after { box-sizing: border-box !important; }
        .sozo-promo-wrapper h1,
        .sozo-promo-wrapper h2,
        .sozo-promo-wrapper h3 {
            font-family: var(--font-family-primary) !important;
            color: var(--color-brand-primary) !important;
        }
        .sozo-promo-wrapper .wrap { max-width: 1160px !important; margin: 0 auto !important; padding: 0 var(--container-pad) !important; }

        /* ---------- Breadcrumb (components/breadcrumb.md) ---------- */
        .sozo-promo-breadcrumb { display: flex !important; justify-content: flex-start !important; margin: 0 0 var(--space-5) !important; }
        .sozo-promo-breadcrumb ol {
            display: flex !important; align-items: center !important; flex-wrap: wrap !important;
            gap: var(--space-2) !important; list-style: none !important; margin: 0 !important; padding: 0 !important;
            font-size: 13px !important; line-height: var(--leading-caption) !important;
        }
        .sozo-promo-breadcrumb li { display: inline-flex !important; align-items: center !important; gap: var(--space-2) !important; margin: 0 !important; }
        .sozo-promo-breadcrumb li + li::before { content: "/" !important; color: var(--color-border-strong) !important; }
        .sozo-promo-breadcrumb a { color: var(--color-text-muted) !important; font-weight: 500 !important; text-decoration: none !important; transition: color var(--duration-fast) var(--ease-standard) !important; }
        .sozo-promo-breadcrumb a:hover { color: var(--color-brand-primary) !important; }
        .sozo-promo-breadcrumb a:focus-visible { outline: var(--focus-ring) !important; outline-offset: 2px !important; }
        .sozo-promo-breadcrumb [aria-current="page"] { color: var(--color-brand-primary) !important; font-weight: 600 !important; }

        /* ---------- Badge label section (components/badge.md, badge--label) ---------- */
        .sozo-promo-wrapper .hero-eyebrow,
        .sozo-promo-wrapper .section-head .section-eyebrow {
            display: inline-flex !important;
            align-items: center !important;
            min-height: 30px !important;
            margin: 0 0 var(--space-5) !important;
            padding: 0 18px !important;
            border-radius: var(--radius-full) !important;
            background-color: var(--color-brand-almond) !important;
            color: var(--color-brand-primary) !important;
            font-size: var(--text-label) !important;
            font-weight: 600 !important;
            line-height: var(--leading-caption) !important;
            letter-spacing: var(--tracking-label) !important;
            text-transform: uppercase !important;
        }
        .sozo-promo-wrapper .section-head .section-eyebrow { margin-bottom: var(--space-3) !important; }

        /* Eyebrow teks di atas surface biru (Almond-on-Blue tidak dipakai) */
        .sozo-promo-wrapper .section-eyebrow {
            display: block !important;
            margin: 0 0 var(--space-3) !important;
            font-size: var(--text-label) !important;
            font-weight: 600 !important;
            line-height: var(--leading-caption) !important;
            letter-spacing: var(--tracking-eyebrow) !important;
            text-transform: uppercase !important;
            color: var(--color-brand-primary) !important;
        }

        /* ---------- Button (components/button.md) ---------- */
        .sozo-promo-wrapper .btn-primary,
        .sozo-promo-wrapper .btn-ghost,
        .sozo-promo-wrapper .special-cta,
        .sozo-promo-wrapper .sz-promo-btn-mobile {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: var(--space-2) !important;
            min-height: 56px !important;
            padding: 0 28px !important;
            border: 1px solid transparent !important;
            border-radius: var(--radius-full) !important;
            font-family: var(--font-family-primary) !important;
            font-size: 15px !important;
            font-weight: 600 !important;
            line-height: 1 !important;
            white-space: nowrap !important;
            text-decoration: none !important;
            box-shadow: none !important;
            cursor: pointer !important;
            transition:
                background-color var(--duration-fast) var(--ease-standard),
                color var(--duration-fast) var(--ease-standard),
                border-color var(--duration-fast) var(--ease-standard),
                transform var(--duration-fast) var(--ease-standard) !important;
        }
        .sozo-promo-wrapper .btn-primary,
        .sozo-promo-wrapper .sz-promo-btn-mobile {
            background: var(--color-brand-primary) !important;
            color: var(--color-text-on-primary) !important;
        }
        .sozo-promo-wrapper .btn-primary:hover,
        .sozo-promo-wrapper .sz-promo-btn-mobile:hover {
            background: var(--color-primary-hover) !important;
            color: var(--color-text-on-primary) !important;
            transform: translateY(-1px) !important;
        }
        .sozo-promo-wrapper .btn-ghost {
            background: transparent !important;
            color: var(--color-brand-primary) !important;
            border-color: var(--color-brand-primary) !important;
        }
        .sozo-promo-wrapper .btn-ghost:hover {
            background: var(--color-primary-subtle) !important;
            color: var(--color-brand-primary) !important;
            transform: translateY(-1px) !important;
        }
        /* Di atas surface biru: Secondary (Dun), bukan tombol putih */
        .sozo-promo-wrapper .special-cta {
            min-height: 48px !important;
            padding: 0 var(--space-5) !important;
            font-size: 14px !important;
            background: var(--color-brand-secondary) !important;
            color: var(--color-brand-primary) !important;
        }
        .sozo-promo-wrapper .special-cta:hover {
            background: var(--color-secondary-hover) !important;
            color: var(--color-brand-primary) !important;
            transform: translateY(-1px) !important;
        }
        .sozo-promo-wrapper .btn-primary:active,
        .sozo-promo-wrapper .btn-ghost:active,
        .sozo-promo-wrapper .special-cta:active,
        .sozo-promo-wrapper .sz-promo-btn-mobile:active { transform: translateY(0) !important; }
        .sozo-promo-wrapper .btn-primary:focus-visible,
        .sozo-promo-wrapper .btn-ghost:focus-visible,
        .sozo-promo-wrapper .sz-promo-btn-mobile:focus-visible { outline: var(--focus-ring) !important; outline-offset: var(--focus-offset) !important; }
        .sozo-promo-wrapper .special-cta:focus-visible { outline: var(--focus-ring-inverse) !important; outline-offset: var(--focus-offset) !important; }

        /* ---------- Hero ---------- */
        .sozo-promo-wrapper .hero { padding: 60px 0 70px !important; }
        .sozo-promo-wrapper .hero-grid { display: grid !important; grid-template-columns: 1fr 0.86fr !important; gap: var(--space-8) !important; align-items: center !important; }
        .sozo-promo-wrapper .hero h1 {
            font-size: var(--text-h1) !important;
            line-height: var(--leading-h1) !important;
            font-weight: 700 !important;
            margin: 0 0 var(--space-4) !important;
        }
        .sozo-promo-wrapper .hero h1 em {
            font-style: normal !important;
            font-weight: 800 !important;
            color: var(--color-brand-primary) !important;
            text-decoration: underline !important;
            text-decoration-color: var(--color-brand-secondary) !important;
            text-decoration-thickness: 4px !important;
            text-underline-offset: 6px !important;
        }
        .sozo-promo-wrapper .hero p {
            max-width: 430px !important;
            margin: 0 0 var(--space-6) !important;
            font-size: var(--text-body) !important;
            line-height: var(--leading-body) !important;
            color: var(--color-text-secondary) !important;
        }
        .sozo-promo-wrapper .hero-actions { display: flex !important; gap: var(--space-3) !important; align-items: center !important; }
        .sozo-promo-wrapper .hero-visual { position: relative !important; }
        .sozo-promo-wrapper .hero-poster { width: 100% !important; aspect-ratio: 1 / 1 !important; border-radius: var(--radius-xl) !important; overflow: hidden !important; background: var(--color-brand-almond) !important; box-shadow: var(--shadow-lg) !important; }
        .sozo-promo-wrapper .hero-poster img { display: block !important; width: 100% !important; height: 100% !important; object-fit: cover !important; }
        .sozo-promo-wrapper .hero-tag {
            position: absolute !important; top: 18px !important; left: 18px !important;
            display: inline-flex !important; align-items: center !important; min-height: 30px !important; padding: 0 var(--space-4) !important;
            border-radius: var(--radius-full) !important;
            background: var(--color-white) !important; color: var(--color-brand-primary) !important;
            font-size: var(--text-label) !important; font-weight: 600 !important; line-height: var(--leading-caption) !important;
            box-shadow: var(--shadow-md) !important;
        }

        /* ---------- Section umum ---------- */
        .sozo-promo-wrapper .section { padding: 60px 0 !important; }
        .sozo-promo-wrapper .section-head {
            display: flex !important; justify-content: space-between !important; align-items: flex-end !important;
            flex-wrap: wrap !important; gap: var(--space-5) !important; margin-bottom: var(--space-6) !important;
        }
        .sozo-promo-wrapper .section-head h2 {
            margin: 0 !important;
            font-size: var(--text-h3) !important;
            font-weight: 700 !important;
            line-height: var(--leading-h3) !important;
        }
        .sozo-promo-wrapper .section-head p {
            max-width: 320px !important;
            margin: 0 !important;
            font-size: var(--text-body-sm) !important;
            line-height: var(--leading-body-sm) !important;
            color: var(--color-text-secondary) !important;
        }

        /* ---------- Promo pembayaran (surface biru) ---------- */
        .sozo-promo-wrapper .special { background: var(--color-brand-primary) !important; border-radius: var(--radius-xl) !important; overflow: hidden !important; color: var(--color-text-on-primary) !important; box-shadow: none !important; }
        .sozo-promo-wrapper .special-inner { display: grid !important; grid-template-columns: 1.1fr 0.9fr !important; align-items: center !important; }
        .sozo-promo-wrapper .special-content { padding: 52px var(--space-8) !important; }
        .sozo-promo-wrapper .special-content .section-eyebrow { color: var(--color-text-on-primary) !important; }
        .sozo-promo-wrapper .special-content h2 {
            margin: 0 0 var(--space-4) !important;
            font-size: var(--text-h3) !important;
            font-weight: 700 !important;
            line-height: var(--leading-h3) !important;
            color: var(--color-text-on-primary) !important;
        }
        .sozo-promo-wrapper .special-content h2 em {
            font-style: normal !important;
            font-weight: 800 !important;
            color: var(--color-text-on-primary) !important;
            text-decoration: underline !important;
            text-decoration-color: var(--color-brand-secondary) !important;
            text-decoration-thickness: 3px !important;
            text-underline-offset: 5px !important;
        }
        .sozo-promo-wrapper .special-content p {
            max-width: 380px !important;
            margin: 0 0 var(--space-5) !important;
            font-size: var(--text-body-sm) !important;
            line-height: var(--leading-body) !important;
            color: var(--color-text-on-primary) !important;
        }
        .sozo-promo-wrapper .pay-logos { display: flex !important; flex-wrap: wrap !important; gap: var(--space-2) !important; margin-bottom: var(--space-5) !important; }
        .sozo-promo-wrapper .pay-logos span {
            display: inline-flex !important; align-items: center !important; min-height: 30px !important;
            padding: 0 var(--space-3) !important;
            border: 1px solid rgba(255, 255, 255, 0.35) !important;
            border-radius: var(--radius-sm) !important;
            background: rgba(255, 255, 255, 0.12) !important;
            color: var(--color-text-on-primary) !important;
            font-size: var(--text-label) !important; font-weight: 600 !important;
        }
        .sozo-promo-wrapper .special-cta-mobile { display: none !important; }
        .sozo-promo-wrapper .special-visual { height: 100% !important; min-height: 380px !important; }
        .sozo-promo-wrapper .special-visual img { display: block !important; width: 100% !important; height: 100% !important; object-fit: cover !important; }

        /* ---------- Navigasi kategori: chip (sama dengan /treatment/) ---------- */
        .sozo-promo-wrapper .cat-nav {
            display: flex !important; flex-wrap: nowrap !important; gap: var(--space-2) !important;
            overflow-x: auto !important; margin-bottom: var(--space-7) !important; padding: 2px 2px var(--space-2) !important;
            scrollbar-width: none !important; scroll-snap-type: x mandatory !important; -webkit-overflow-scrolling: touch !important;
        }
        .sozo-promo-wrapper .cat-nav::-webkit-scrollbar { display: none !important; }
        .sozo-promo-wrapper .cat-nav a {
            display: inline-flex !important; align-items: center !important; flex: 0 0 auto !important;
            min-height: 40px !important; padding: 0 var(--space-4) !important;
            border: 1px solid var(--color-border) !important; border-radius: var(--radius-full) !important;
            background: var(--color-white) !important; color: var(--color-text-secondary) !important;
            font-size: var(--text-body-sm) !important; font-weight: 500 !important; line-height: 1.2 !important;
            white-space: nowrap !important; text-decoration: none !important; box-shadow: none !important;
            scroll-snap-align: start !important;
            transition:
                border-color var(--duration-fast) var(--ease-standard),
                color var(--duration-fast) var(--ease-standard) !important;
        }
        .sozo-promo-wrapper .cat-nav a:hover { border-color: var(--color-brand-primary) !important; color: var(--color-brand-primary) !important; }
        .sozo-promo-wrapper .cat-nav a:focus-visible { outline: var(--focus-ring) !important; outline-offset: 2px !important; }

        .sozo-promo-wrapper .cat-block { margin-bottom: 44px !important; scroll-margin-top: 90px !important; }
        .sozo-promo-wrapper .cat-block:last-of-type { margin-bottom: var(--space-3) !important; }
        .sozo-promo-wrapper #all-promos { padding-bottom: var(--space-3) !important; }
        .sozo-promo-wrapper #claim { padding-top: 28px !important; }
        .sozo-promo-wrapper .cat-block-head {
            display: flex !important; align-items: baseline !important; gap: var(--space-3) !important;
            margin-bottom: var(--space-5) !important; padding-bottom: var(--space-4) !important;
            border-bottom: 1px solid var(--color-border) !important;
        }
        .sozo-promo-wrapper .cat-block-head h3 { margin: 0 !important; font-size: var(--text-h5) !important; font-weight: 600 !important; line-height: var(--leading-h5) !important; }
        .sozo-promo-wrapper .sz-promo-empty { margin: 0 !important; font-size: var(--text-body-sm) !important; color: var(--color-text-secondary) !important; }

        /* ---------- Slider + kartu promo (components/promo-card.md, EX-02 untuk banner 16:9) ---------- */
        .sz-promo-slider-container { position: relative !important; }
        .sz-promo-grid {
            display: flex !important; flex-wrap: nowrap !important; gap: 20px !important;
            overflow-x: auto !important; scroll-snap-type: x mandatory !important; scroll-behavior: smooth !important;
            padding: 10px 0 20px !important; scrollbar-width: none !important; -webkit-overflow-scrolling: touch !important;
        }
        .sz-promo-grid::-webkit-scrollbar { display: none !important; }

        .sz-promo-card {
            flex: 0 0 calc(25% - 15px) !important; scroll-snap-align: start !important;
            display: flex !important; flex-direction: column !important; overflow: hidden !important;
            border: 1px solid var(--color-border) !important; border-radius: var(--radius-lg) !important;
            background: var(--color-white) !important; box-shadow: none !important;
            transition:
                transform var(--duration-card) var(--ease-standard),
                box-shadow var(--duration-card) var(--ease-standard),
                border-color var(--duration-card) var(--ease-standard) !important;
        }
        .sz-promo-card:hover { transform: translateY(-2px) !important; box-shadow: var(--shadow-md) !important; border-color: var(--color-border-strong) !important; }
        .sz-promo-image-trigger {
            display: block !important; width: 100% !important; aspect-ratio: 1 / 1 !important; overflow: hidden !important;
            margin: 0 !important; padding: 0 !important; border: 0 !important; border-radius: 0 !important;
            background: var(--color-brand-almond) !important; cursor: zoom-in !important;
            appearance: none !important; -webkit-appearance: none !important;
        }
        .sz-promo-image-trigger img { display: block !important; width: 100% !important; height: 100% !important; object-fit: cover !important; transition: transform var(--duration-media) var(--ease-standard) !important; }
        .sz-promo-image-trigger:hover img { transform: scale(1.03) !important; }
        .sz-promo-image-trigger:focus-visible { outline: var(--focus-ring) !important; outline-offset: -3px !important; }
        .sz-promo-card.sz-card-banner { flex-basis: calc(33.333% - 11px) !important; }
        .sz-promo-card.sz-card-banner .sz-promo-image-trigger { aspect-ratio: 16 / 9 !important; }

        /* Tombol kartu: Button Primary sm */
        .sz-promo-claim-btn {
            display: inline-flex !important; align-items: center !important; justify-content: center !important; gap: var(--space-2) !important;
            min-height: 40px !important; margin: var(--space-4) 20px 20px !important; padding: 0 18px !important;
            border: 1px solid var(--color-brand-primary) !important; border-radius: var(--radius-full) !important;
            background: var(--color-brand-primary) !important; color: var(--color-text-on-primary) !important;
            font-family: var(--font-family-primary) !important; font-size: 13px !important; font-weight: 600 !important; line-height: 1 !important;
            text-decoration: none !important;
            transition:
                background-color var(--duration-fast) var(--ease-standard),
                transform var(--duration-fast) var(--ease-standard) !important;
        }
        .sz-promo-claim-btn:hover { background: var(--color-primary-hover) !important; color: var(--color-text-on-primary) !important; text-decoration: none !important; transform: translateY(-1px) !important; }
        .sz-promo-claim-btn:focus-visible { outline: var(--focus-ring) !important; outline-offset: var(--focus-offset) !important; }
        .sz-promo-claim-btn svg { width: 16px !important; height: 16px !important; fill: none !important; stroke: currentColor !important; stroke-width: 1.8 !important; stroke-linecap: round !important; stroke-linejoin: round !important; }

        /* Tombol navigasi slider */
        button.sz-promo-nav-btn {
            position: absolute !important; top: 50% !important; transform: translateY(-50%) !important; z-index: 10 !important;
            display: flex !important; align-items: center !important; justify-content: center !important;
            width: 46px !important; height: 46px !important; margin: 0 !important; padding: 0 !important;
            border: 1px solid var(--color-border) !important; border-radius: var(--radius-full) !important;
            background: var(--color-white) !important; color: var(--color-brand-primary) !important;
            box-shadow: var(--shadow-md) !important; cursor: pointer !important;
            appearance: none !important; -webkit-appearance: none !important;
            transition:
                background-color var(--duration-fast) var(--ease-standard),
                border-color var(--duration-fast) var(--ease-standard) !important;
        }
        button.sz-promo-nav-btn:hover { background: var(--color-white) !important; color: var(--color-brand-primary) !important; border-color: var(--color-brand-primary) !important; }
        button.sz-promo-nav-btn:focus-visible { outline: var(--focus-ring) !important; outline-offset: var(--focus-offset) !important; }
        button.sz-promo-nav-btn svg { display: block !important; width: 24px !important; height: 24px !important; margin: 0 !important; stroke: currentColor !important; stroke-width: 2 !important; fill: none !important; }
        button.sz-promo-nav-btn.prev { left: -20px !important; }
        button.sz-promo-nav-btn.next { right: -20px !important; }

        .sz-promo-slider-container.sz-slider-general .sz-promo-grid { gap: var(--space-4) !important; }
        .sz-promo-slider-container.sz-slider-general button.sz-promo-nav-btn { display: none !important; }

        /* ---------- Lightbox (z-index di atas header Elementor, EX-03) ---------- */
        .sz-promo-lightbox {
            position: fixed !important; inset: 0 !important; z-index: 999999 !important;
            display: none !important; align-items: center !important; justify-content: center !important;
            padding: var(--space-5) !important; background: var(--overlay-modal) !important;
        }
        .sz-promo-lightbox.is-open { display: flex !important; }
        .sz-promo-lightbox-dialog { position: relative !important; display: flex !important; align-items: center !important; justify-content: center !important; max-width: 100% !important; max-height: 100% !important; }
        #szPromoLightbox .sz-promo-lightbox-dialog img {
            display: block !important; width: auto !important; height: auto !important;
            max-width: min(78vw, 1050px) !important; max-height: 78vh !important; object-fit: contain !important;
            border-radius: var(--radius-lg) !important; background: var(--color-white) !important;
        }
        #szPromoLightbox .sz-promo-lightbox-close {
            position: absolute !important; top: var(--space-3) !important; right: var(--space-3) !important;
            display: inline-flex !important; align-items: center !important; justify-content: center !important;
            width: 44px !important; min-width: 44px !important; max-width: 44px !important;
            height: 44px !important; min-height: 44px !important; max-height: 44px !important;
            margin: 0 !important; padding: 0 !important;
            border: 1px solid var(--color-white) !important; border-radius: var(--radius-full) !important;
            background: var(--color-brand-primary) !important; color: var(--color-text-on-primary) !important;
            font-family: var(--font-family-primary) !important; font-size: 26px !important; font-weight: 400 !important; line-height: 1 !important;
            box-shadow: var(--shadow-md) !important; cursor: pointer !important;
            transition: background-color var(--duration-fast) var(--ease-standard) !important;
        }
        #szPromoLightbox .sz-promo-lightbox-close:hover { background: var(--color-primary-hover) !important; }
        #szPromoLightbox .sz-promo-lightbox-close:focus-visible { outline: var(--focus-ring-inverse) !important; outline-offset: var(--focus-offset) !important; }

        /* ---------- Header shortcode kategori ---------- */
        .sozo-promo-wrapper.sozo-promo-kategori-wrapper {
            max-width: 1200px !important;
            margin: var(--space-7) auto !important;
            padding: 0 20px !important;
            background: transparent !important;
        }
        .sozo-promo-kategori-wrapper .cat-block { margin-bottom: 0 !important; }
        .sozo-promo-wrapper .sz-promo-header {
            display: flex !important; justify-content: space-between !important; align-items: center !important;
            gap: 20px !important; margin-bottom: var(--space-5) !important;
        }
        .sozo-promo-wrapper .sz-promo-text { flex: 1 !important; }
        .sozo-promo-wrapper .sz-promo-title {
            margin: 0 0 var(--space-2) !important;
            font-size: var(--text-h3) !important; font-weight: 700 !important; line-height: var(--leading-h3) !important;
        }
        .sozo-promo-wrapper .sz-promo-subtitle {
            margin: 0 !important;
            font-size: var(--text-body-sm) !important; line-height: var(--leading-body-sm) !important;
            color: var(--color-text-secondary) !important;
        }
        /* Link standalone (components/link.md) */
        .sozo-promo-wrapper .sz-promo-link-desktop {
            display: inline-flex !important; align-items: center !important; gap: var(--space-1) !important;
            color: var(--color-brand-primary) !important; font-size: var(--text-body-sm) !important; font-weight: 600 !important;
            white-space: nowrap !important; text-decoration: none !important; text-underline-offset: 4px !important;
        }
        .sozo-promo-wrapper .sz-promo-link-desktop:hover { color: var(--color-brand-primary) !important; text-decoration: underline !important; }
        .sozo-promo-wrapper .sz-promo-link-desktop:focus-visible { outline: var(--focus-ring) !important; outline-offset: var(--focus-offset) !important; }
        .sozo-promo-wrapper .sz-promo-footer.mobile-only { display: none !important; }
        .sozo-promo-wrapper .sz-promo-btn-mobile { width: 100% !important; min-height: 48px !important; font-size: 14px !important; }

        /* ---------- Floating CTA (DEC-24: shadow-lg, tanpa animasi terus-menerus) ---------- */
        .sozo-promo-wrapper button.sz-floating-promo-btn,
        button.sz-floating-promo-btn {
            position: fixed !important; bottom: var(--space-5) !important; right: var(--space-5) !important; z-index: 900 !important;
            display: flex !important; align-items: center !important; gap: var(--space-2) !important;
            min-height: 48px !important; padding: 0 var(--space-5) !important;
            border: 2px solid var(--color-white) !important; border-radius: var(--radius-full) !important;
            background-color: var(--color-brand-primary) !important; color: var(--color-text-on-primary) !important;
            font-family: var(--font-family-primary) !important; font-size: 14px !important; font-weight: 600 !important; line-height: 1 !important;
            box-shadow: var(--shadow-lg) !important; cursor: pointer !important;
            appearance: none !important; -webkit-appearance: none !important;
            transition:
                opacity var(--duration-fast) var(--ease-standard),
                visibility var(--duration-fast) var(--ease-standard),
                transform var(--duration-fast) var(--ease-standard),
                background-color var(--duration-fast) var(--ease-standard) !important;
        }
        button.sz-floating-promo-btn.hidden { opacity: 0 !important; visibility: hidden !important; transform: translateY(20px) !important; pointer-events: none !important; }
        button.sz-floating-promo-btn:hover { background-color: var(--color-primary-hover) !important; transform: translateY(-1px) !important; }
        button.sz-floating-promo-btn:focus-visible { outline: var(--focus-ring) !important; outline-offset: var(--focus-offset) !important; }
        button.sz-floating-promo-btn svg { display: block !important; width: 20px !important; height: 20px !important; fill: none !important; stroke: currentColor !important; stroke-width: 2 !important; stroke-linecap: round !important; stroke-linejoin: round !important; }

        /* ---------- Cara klaim ---------- */
        .sozo-promo-wrapper .claim { background: var(--color-white) !important; border: 1px solid var(--color-border) !important; border-radius: var(--radius-xl) !important; box-shadow: none !important; }
        .sozo-promo-wrapper .claim-inner { padding: 56px var(--space-8) !important; }
        .sozo-promo-wrapper .claim-grid { display: grid !important; grid-template-columns: repeat(4, 1fr) !important; gap: 28px !important; }
        .sozo-promo-wrapper .claim-num { margin-bottom: var(--space-3) !important; font-size: 40px !important; font-weight: 800 !important; line-height: 1 !important; color: var(--color-brand-primary) !important; }
        .sozo-promo-wrapper .claim-step h3 { margin: 0 0 var(--space-2) !important; font-size: var(--text-h5) !important; font-weight: 600 !important; line-height: var(--leading-h5) !important; }
        .sozo-promo-wrapper .claim-step p { margin: 0 !important; font-size: var(--text-body-sm) !important; line-height: var(--leading-body-sm) !important; color: var(--color-text-secondary) !important; }
        .sozo-promo-wrapper .claim-note {
            margin-top: 36px !important; padding: 18px 0 0 !important;
            background: transparent !important; border: none !important; border-top: 1px dashed var(--color-border) !important;
            border-radius: 0 !important; box-shadow: none !important; display: block !important;
        }
        .sozo-promo-wrapper .claim-note p { margin: 0 !important; font-size: var(--text-caption) !important; font-weight: 500 !important; line-height: var(--leading-body) !important; color: var(--color-text-muted) !important; }
        .sozo-promo-wrapper .claim-note strong { color: var(--color-brand-primary) !important; font-weight: 600 !important; }

        /* ---------- CTA penutup ---------- */
        .sozo-promo-wrapper .final-cta { padding: var(--space-9) 0 !important; text-align: center !important; }
        .sozo-promo-wrapper .final-cta h2 { margin: 0 0 var(--space-3) !important; font-size: var(--text-h3) !important; font-weight: 700 !important; line-height: var(--leading-h3) !important; }
        .sozo-promo-wrapper .final-cta p { margin: 0 0 var(--space-6) !important; font-size: var(--text-body) !important; line-height: var(--leading-body) !important; color: var(--color-text-secondary) !important; }

        /* ---------- Responsive ---------- */
        @media (max-width: 1024px) {
            .sz-promo-card { flex: 0 0 calc(33.333% - 14px) !important; }
            .sozo-promo-wrapper .hero-grid,
            .sozo-promo-wrapper .special-inner { grid-template-columns: 1fr !important; }
            .sozo-promo-wrapper .claim-grid { grid-template-columns: repeat(2, 1fr) !important; }
            .sozo-promo-wrapper .claim-inner { padding: 36px 28px !important; }
        }
        @media (max-width: 768px) {
            .sozo-promo-breadcrumb { justify-content: center !important; margin-bottom: var(--space-4) !important; }
            .sozo-promo-breadcrumb ol { justify-content: center !important; }
            .sozo-promo-wrapper .hero-actions { width: 100% !important; gap: var(--space-2) !important; }
            .sozo-promo-wrapper .hero-actions .btn-primary,
            .sozo-promo-wrapper .hero-actions .btn-ghost { flex: 1 1 0 !important; min-width: 0 !important; min-height: 48px !important; padding: 0 var(--space-2) !important; font-size: 13px !important; }
            .sozo-promo-wrapper .hero p { margin-bottom: 22px !important; }
            .sozo-promo-wrapper .special-content { padding: 36px 28px !important; }
            .sozo-promo-wrapper .special-cta-desktop { display: none !important; }
            .sozo-promo-wrapper .special-cta-mobile { display: inline !important; }
            .sozo-promo-wrapper.sozo-promo-kategori-wrapper { padding: 0 var(--space-4) !important; margin: var(--space-5) auto !important; }
            .sozo-promo-wrapper .sz-promo-header { flex-direction: column !important; align-items: flex-start !important; gap: var(--space-3) !important; margin-bottom: 20px !important; }
            .sozo-promo-wrapper .sz-promo-link-desktop,
            button.sz-promo-nav-btn { display: none !important; }
            .sozo-promo-wrapper .mobile-only,
            .sozo-promo-wrapper .sz-promo-footer.mobile-only { display: block !important; }
            .sozo-promo-wrapper .sz-promo-footer { margin-top: 10px !important; text-align: center !important; }
            .sz-promo-grid { gap: var(--space-4) !important; padding-bottom: 10px !important; }
            .sz-promo-card,
            .sz-promo-card.sz-card-banner { flex: 0 0 min(82%, 340px) !important; }
            .sozo-promo-wrapper #all-promos { padding-bottom: var(--space-2) !important; }
            .sozo-promo-wrapper .cat-block { margin-bottom: var(--space-6) !important; }
            .sozo-promo-wrapper .cat-block:last-of-type { margin-bottom: var(--space-2) !important; }
            .sozo-promo-wrapper #claim { padding-top: 20px !important; }
            .sozo-promo-wrapper button.sz-floating-promo-btn,
            button.sz-floating-promo-btn { bottom: var(--space-4) !important; right: var(--space-4) !important; }
            .sz-promo-lightbox { padding: var(--space-4) !important; }
            #szPromoLightbox .sz-promo-lightbox-dialog img { max-width: calc(100vw - 32px) !important; max-height: calc(100dvh - 32px) !important; }
        }
        @media (max-width: 480px) {
            .sozo-promo-wrapper .claim-grid { grid-template-columns: 1fr !important; }
        }
        @media (prefers-reduced-motion: reduce) {
            .sz-promo-card:hover,
            .sz-promo-image-trigger:hover img,
            .sozo-promo-wrapper .btn-primary:hover,
            .sozo-promo-wrapper .btn-ghost:hover,
            .sozo-promo-wrapper .special-cta:hover,
            .sz-promo-claim-btn:hover,
            button.sz-floating-promo-btn:hover { transform: none !important; }
        }
    </style>
    <?php
});

// ============ 7. SLIDER & FLOATING CTA INTERACTIONS ============
add_action('wp_footer', function() {
    ?>
    <div class="sz-promo-lightbox" id="szPromoLightbox" aria-hidden="true">
        <div class="sz-promo-lightbox-dialog" role="dialog" aria-modal="true" aria-label="Detail gambar promo" tabindex="-1">
            <button class="sz-promo-lightbox-close" type="button" aria-label="Tutup detail gambar promo">&times;</button>
            <img src="" alt="">
        </div>
    </div>
    <script id="sozo-promo-inline-js">
    (function() {
        if (window.__sozoPromoJSLoaded) return;
        window.__sozoPromoJSLoaded = true;
        const lightbox = document.getElementById('szPromoLightbox');
        const lightboxImage = lightbox ? lightbox.querySelector('img') : null;
        let lastImageTrigger = null;
        let previousBodyOverflow = '';

        function closePromoLightbox() {
            if (!lightbox || !lightbox.classList.contains('is-open')) return;
            lightbox.classList.remove('is-open');
            lightbox.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = previousBodyOverflow;
            if (lightboxImage) {
                lightboxImage.src = '';
                lightboxImage.alt = '';
            }
            if (lastImageTrigger) lastImageTrigger.focus();
        }

        document.addEventListener('click', function(event) {
            const imageTrigger = event.target.closest('.sz-promo-image-trigger');
            if (imageTrigger && lightbox && lightboxImage) {
                lastImageTrigger = imageTrigger;
                previousBodyOverflow = document.body.style.overflow;
                lightboxImage.src = imageTrigger.dataset.imageSrc;
                lightboxImage.alt = imageTrigger.dataset.imageAlt || '';
                lightbox.classList.add('is-open');
                lightbox.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
                lightbox.querySelector('.sz-promo-lightbox-close').focus();
                return;
            }

            if (event.target.closest('.sz-promo-lightbox-close') || event.target === lightbox) {
                closePromoLightbox();
                return;
            }

            const navButton = event.target.closest('.sz-promo-nav-btn');
            if (navButton) {
                const grid = navButton.closest('.sz-promo-slider-container')?.querySelector('.sz-promo-grid');
                if (!grid) return;
                const direction = navButton.classList.contains('prev') ? -1 : 1;
                grid.scrollBy({ left: direction * grid.clientWidth * 0.8, behavior: 'smooth' });
                return;
            }

            const floatingButton = event.target.closest('.sz-floating-promo-btn');
            if (floatingButton) {
                document.querySelector('#all-promos')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') closePromoLightbox();
        });

        window.addEventListener('scroll', function() {
            const floatingButton = document.querySelector('.sz-floating-promo-btn');
            const promoSection = document.querySelector('#all-promos');
            if (!floatingButton || !promoSection) return;
            const bounds = promoSection.getBoundingClientRect();
            const isVisible = bounds.top < window.innerHeight && bounds.bottom >= 0;
            floatingButton.classList.toggle('hidden', isVisible);
        }, { passive: true });
    })();
    </script>
    <?php
});
