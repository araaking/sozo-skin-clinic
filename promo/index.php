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
        return '<p style="font-size:14px; color:#666;">Belum ada promo aktif untuk kategori ini.</p>';
    }

    ob_start();
    ?>
    <div class="sz-promo-slider-container <?php echo $is_general ? 'sz-slider-general' : ''; ?>">
        <?php if (!$is_general): ?>
        <button class="sz-promo-nav-btn prev" aria-label="Promo sebelumnya" type="button">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"/></svg>
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
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></svg>
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
                            <li class="separator" aria-hidden="true">/</li>
                            <li aria-current="page"><span>Promo</span></li>
                        </ol>
                    </nav>
                    <span class="hero-eyebrow">Promo Bulan Ini</span>
                    <h1>Bisa cantik<br>tanpa <em>bikin ragu.</em></h1>
                    <p>Diskon treatment wajah, tubuh, dan rambut — plus cicilan 0% dan cashback hingga Rp500.000 untuk booking bulan ini.</p>
                    <div class="hero-actions">
                        <a href="#all-promos" class="btn-primary">Lihat Promo</a>
                        <a href="<?php echo esc_url($wa_link); ?>" target="_blank" class="btn-ghost">Konsultasi Gratis</a>
                    </div>
                </div>
                <div class="hero-visual">
                    <div class="hero-poster">
                        <img src="https://asset.sozoskinclinic.com/wp-content/uploads/2026/09/diskon-potongan-100rb.webp" alt="Promo SOZO Skin Potongan 100rb">
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
                            <img src="https://asset.sozoskinclinic.com/wp-content/uploads/2026/09/cicilan0.webp" alt="Cicilan 0% SOZO Skin">
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
                                <h4>Pilih Promo</h4>
                                <p>Pilih promo yang sesuai dengan kebutuhanmu.</p>
                            </div>
                            <div class="claim-step">
                                <div class="claim-num">02</div>
                                <h4>Booking via WA</h4>
                                <p>Tekan tombol Klaim Promo pada kartu untuk langsung menghubungi tim kami via WhatsApp.</p>
                            </div>
                            <div class="claim-step">
                                <div class="claim-num">03</div>
                                <h4>Datang ke Klinik</h4>
                                <p>Tunjukkan konfirmasi booking ke resepsionis di outlet SOZO Skin pilihanmu.</p>
                            </div>
                            <div class="claim-step">
                                <div class="claim-num">04</div>
                                <h4>Nikmati Treatment</h4>
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
                <a href="<?php echo esc_url($wa_link); ?>" target="_blank" class="btn-primary">Chat via WhatsApp</a>
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
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
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
    <div class="sozo-promo-wrapper sozo-promo-kategori-wrapper" style="background: transparent; max-width:1200px; margin:40px auto; padding:0 20px; box-sizing:border-box;">
        <div class="cat-block" id="<?php echo esc_attr($atts['slug']); ?>" style="margin-bottom: 0;" data-custom-wa="<?php echo esc_url($final_wa_link); ?>">
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
        :root {
            --sozo-blue: #1A2080;
            --sozo-blue-hover: #1A2080;
            --sozo-text: #41413E;
            --sozo-border: #DFDBD4;
            --sozo-almond: #E9DFD5;
            --sozo-dun: #D9C7B1;
            --sozo-gradient-bg: #FFFFFF;
            --sozo-white: #FFFFFF;
            --sozo-font-heading: 'Poppins', system-ui, -apple-system, sans-serif;
            --sozo-font-body: 'Poppins', system-ui, -apple-system, sans-serif;
            --card-radius: 20px;
        }

        .sozo-promo-wrapper { 
            background: var(--sozo-gradient-bg) !important; 
            color: var(--sozo-text); 
            font-family: var(--sozo-font-body); 
        }
        .sozo-promo-wrapper h1, 
        .sozo-promo-wrapper h2, 
        .sozo-promo-wrapper h3, 
        .sozo-promo-wrapper h4 { 
            font-family: var(--sozo-font-heading); 
            color: var(--sozo-blue); 
        }
        .sozo-promo-wrapper .wrap { max-width: 1160px; margin: 0 auto; padding: 0 32px; }

        .sozo-promo-breadcrumb { display: flex !important; justify-content: flex-start !important; margin: 0 0 20px !important; }
        .sozo-promo-breadcrumb ol { display: flex !important; align-items: center !important; flex-wrap: wrap !important; gap: 8px 10px !important; list-style: none !important; margin: 0 !important; padding: 0 !important; font-size: 12px !important; font-weight: 500 !important; line-height: 1.4 !important; }
        .sozo-promo-breadcrumb a { color: #777771 !important; text-decoration: none !important; transition: color 180ms ease !important; }
        .sozo-promo-breadcrumb a:hover, .sozo-promo-breadcrumb a:focus-visible { color: var(--sozo-blue) !important; text-decoration: none !important; }
        .sozo-promo-breadcrumb .separator { color: #777771 !important; }
        .sozo-promo-breadcrumb [aria-current="page"] { color: var(--sozo-blue) !important; font-weight: 600 !important; }

        .sozo-promo-wrapper .hero { padding: 60px 0 70px; }
        .sozo-promo-wrapper .hero-grid { display: grid; grid-template-columns: 1fr 0.86fr; gap: 48px; align-items: center; }
        .sozo-promo-wrapper .hero-eyebrow { 
            display: inline-flex; 
            align-items: center; 
            gap: 8px; 
            color: var(--sozo-blue) !important; 
            font-size: 13px; 
            font-weight: 700; 
            letter-spacing: .12em; 
            text-transform: uppercase; 
            margin-bottom: 20px; 
        }
        .sozo-promo-wrapper .hero-eyebrow::before { 
            content: ""; 
            width: 20px; 
            height: 2.5px; 
            background: var(--sozo-blue) !important; 
            border-radius: 2px;
        }
        .sozo-promo-wrapper .hero h1 { font-size: clamp(2rem, 3.5vw, 2.75rem); line-height: 1.25; color: var(--sozo-blue); font-weight: 800; margin-bottom: 18px; }
        .sozo-promo-wrapper .hero h1 em { font-style: normal; color: var(--sozo-blue); font-weight: 800; text-decoration: underline; text-decoration-color: rgba(26, 32, 128, 0.3); }
        .sozo-promo-wrapper .hero p { font-size: 16px; color: var(--sozo-text); line-height: 1.65; max-width: 430px; margin-bottom: 30px; }
        .sozo-promo-wrapper .hero-actions { display: flex; gap: 12px; align-items: center; }
        .sozo-promo-wrapper .special-cta-mobile { display: none !important; }
        
        .sozo-promo-wrapper .btn-primary { 
            background: var(--sozo-blue) !important; 
            color: var(--sozo-white) !important; 
            border: 2px solid var(--sozo-blue) !important; 
            padding: 14px 28px !important; 
            border-radius: 50px !important; 
            font-weight: 600 !important; 
            font-size: 15px !important; 
            text-decoration: none !important; 
            display: inline-block !important; 
            transition: all .3s ease !important; 
            box-shadow: 0 4px 15px rgba(26, 32, 128, 0.2) !important;
        }
        .sozo-promo-wrapper .btn-primary:hover { 
            background: transparent !important; 
            color: var(--sozo-blue) !important; 
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(26, 32, 128, 0.15) !important;
        }
        
        .sozo-promo-wrapper .btn-ghost { 
            background: transparent !important; 
            border: 2px solid var(--sozo-blue) !important; 
            color: var(--sozo-blue) !important; 
            padding: 14px 26px !important; 
            border-radius: 50px !important; 
            font-weight: 600 !important; 
            font-size: 15px !important; 
            text-decoration: none !important; 
            display: inline-block !important; 
            transition: all .3s ease !important; 
        }
        .sozo-promo-wrapper .btn-ghost:hover { 
            background: var(--sozo-blue) !important; 
            color: var(--sozo-white) !important; 
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(26, 32, 128, 0.15) !important;
        }
        
        .sozo-promo-wrapper .hero-visual { position: relative; }
        .sozo-promo-wrapper .hero-poster { width: 100%; aspect-ratio: 1/1; border-radius: 24px; overflow: hidden; box-shadow: 0 24px 48px -18px rgba(26, 32, 128, 0.2); }
        .sozo-promo-wrapper .hero-poster img { width: 100%; height: 100%; object-fit: cover; }
        .sozo-promo-wrapper .hero-tag { position: absolute; top: 18px; left: 18px; background: var(--sozo-white); color: var(--sozo-blue); font-size: 12px; font-weight: 700; padding: 8px 16px; border-radius: 999px; box-shadow: 0 8px 16px -6px rgba(26, 32, 128, 0.2); }

        .sozo-promo-wrapper .section { padding: 60px 0; }
        
        .sozo-promo-wrapper .section-head { 
            display: flex !important; 
            justify-content: space-between !important; 
            align-items: flex-end !important; 
            margin-bottom: 32px !important; 
            gap: 24px !important; 
            flex-wrap: wrap !important; 
        }
        .sozo-promo-wrapper .section-eyebrow { 
            font-size: 12px; 
            font-weight: 700; 
            letter-spacing: .12em; 
            text-transform: uppercase; 
            color: var(--sozo-blue) !important; 
            margin-bottom: 8px; 
            display: block; 
        }
        .sozo-promo-wrapper .section-head h2 { font-size: clamp(1.5rem, 2.5vw, 2rem) !important; color: var(--sozo-blue) !important; font-weight: 700 !important; margin: 0 !important; line-height: 1.2 !important; }
        
        .sozo-promo-wrapper .section-head p { 
            color: var(--sozo-text) !important; 
            font-size: 14.5px !important; 
            max-width: 320px !important; 
            line-height: 1.5 !important;
            margin: 0 !important;
        }

        .sozo-promo-wrapper .special { background: var(--sozo-blue); border-radius: 24px; overflow: hidden; color: var(--sozo-white); box-shadow: 0 20px 40px rgba(26, 32, 128, 0.15); }
        .sozo-promo-wrapper .special-inner { display: grid; grid-template-columns: 1.1fr 0.9fr; align-items: center; }
        .sozo-promo-wrapper .special-content { padding: 52px 48px; }
        .sozo-promo-wrapper .special-content .section-eyebrow { color: rgba(255, 255, 255, 0.85) !important; }
        .sozo-promo-wrapper .special-content h2 { color: var(--sozo-white); font-size: clamp(1.5rem, 2.5vw, 2rem); margin-bottom: 14px; font-weight: 700; }
        .sozo-promo-wrapper .special-content h2 em { font-style: normal; color: #FFFFFF; font-weight: 800; text-decoration: underline; }
        .sozo-promo-wrapper .special-content p { color: rgba(255, 255, 255, 0.9); font-size: 14.5px; max-width: 380px; margin-bottom: 24px; line-height: 1.6; }
        .sozo-promo-wrapper .pay-logos { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 26px; }
        
        .sozo-promo-wrapper .pay-logos span { 
            background: rgba(255, 255, 255, 0.15) !important; 
            border: 1px solid rgba(255, 255, 255, 0.25) !important; 
            padding: 7px 14px !important; 
            border-radius: 8px !important; 
            font-size: 12px !important; 
            font-weight: 600 !important; 
            color: var(--sozo-white) !important; 
            display: inline-block !important;
        }
        
        .sozo-promo-wrapper .special-cta,
        a.special-cta { 
            background: var(--sozo-white) !important; 
            color: var(--sozo-blue) !important; 
            border: 2px solid var(--sozo-white) !important; 
            padding: 13px 26px !important; 
            border-radius: 50px !important; 
            font-weight: 600 !important; 
            font-size: 14px !important; 
            cursor: pointer !important; 
            display: inline-block !important; 
            text-decoration: none !important; 
            box-shadow: 0 4px 14px rgba(0,0,0,0.12) !important;
            transition: all .3s ease !important; 
        }
        .sozo-promo-wrapper .special-cta:hover { 
            background: transparent !important; 
            color: var(--sozo-white) !important; 
            transform: translateY(-2px);
        }
        
        .sozo-promo-wrapper .special-visual { height: 100%; min-height: 380px; }
        .sozo-promo-wrapper .special-visual img { width: 100%; height: 100%; object-fit: cover; }

        .sozo-promo-wrapper .cat-nav { display: flex; gap: 8px; flex-wrap: nowrap; overflow-x: auto; margin-bottom: 36px; padding: 2px 2px 10px; scrollbar-width: none; scroll-snap-type: x mandatory; -webkit-overflow-scrolling: touch; }
        .sozo-promo-wrapper .cat-nav::-webkit-scrollbar { display: none; }
        .sozo-promo-wrapper .cat-nav a { 
            text-decoration: none !important; 
            color: var(--sozo-text) !important; 
            font-weight: 600 !important; 
            font-size: 13.5px !important; 
            padding: 9px 18px !important; 
            border-radius: 999px !important; 
            border: 1px solid var(--sozo-border) !important; 
            background: var(--sozo-white) !important; 
            display: inline-block !important; 
            flex: 0 0 auto !important;
            scroll-snap-align: start;
            transition: all .2s !important; 
            box-shadow: 0 2px 6px rgba(0,0,0,0.03) !important;
        }
        .sozo-promo-wrapper .cat-nav a:hover { 
            color: var(--sozo-blue) !important; 
            border-color: var(--sozo-blue) !important; 
            transform: translateY(-1px);
        }
        .sozo-promo-wrapper .cat-block { margin-bottom: 44px; scroll-margin-top: 90px; }
        .sozo-promo-wrapper .cat-block:last-of-type { margin-bottom: 12px; }
        .sozo-promo-wrapper #all-promos { padding-bottom: 12px; }
        .sozo-promo-wrapper #claim { padding-top: 28px; }
        .sozo-promo-wrapper .cat-block-head { display: flex; align-items: baseline; gap: 12px; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid var(--sozo-border); }
        .sozo-promo-wrapper .cat-block-head h3 { font-size: 1.25rem; color: var(--sozo-blue); font-weight: 700; }

        .sz-promo-slider-container { position: relative !important; }
        .sz-promo-grid { display: flex !important; flex-wrap: nowrap !important; overflow-x: auto !important; scroll-snap-type: x mandatory !important; scroll-behavior: smooth !important; gap: 20px !important; padding: 10px 0 20px 0 !important; -webkit-overflow-scrolling: touch !important; scrollbar-width: none !important; }
        .sz-promo-grid::-webkit-scrollbar { display: none !important; }

        /* Promo cards: image-first, 4 cards per row, 1:1 imagery */
        .sz-promo-card { flex: 0 0 calc(25% - 15px) !important; scroll-snap-align: start !important; display: flex !important; flex-direction: column !important; overflow: hidden !important; border: 1px solid var(--sozo-border) !important; border-radius: 12px !important; background: var(--sozo-white) !important; box-shadow: none !important; transition: transform 0.22s ease, box-shadow 0.22s ease !important; }
        .sz-promo-card:hover { transform: translateY(-2px) !important; box-shadow: 0 4px 16px rgba(26, 32, 128, 0.08) !important; }
        .sz-promo-image-trigger { display: block !important; width: 100% !important; aspect-ratio: 1 / 1 !important; overflow: hidden !important; padding: 0 !important; border: 0 !important; background: var(--sozo-almond) !important; cursor: zoom-in !important; appearance: none !important; -webkit-appearance: none !important; }
        .sz-promo-image-trigger img { display: block !important; width: 100% !important; height: 100% !important; object-fit: cover !important; }
        .sz-promo-image-trigger:focus-visible { outline: 2px solid var(--sozo-blue) !important; outline-offset: -3px !important; }
        .sz-promo-card.sz-card-banner { flex-basis: calc(33.333% - 11px) !important; }
        .sz-promo-card.sz-card-banner .sz-promo-image-trigger { aspect-ratio: 16 / 9 !important; }
        .sz-promo-claim-btn { display: inline-flex !important; align-items: center !important; justify-content: center !important; gap: 8px !important; margin: 16px 20px 20px !important; padding: 11px 16px !important; border: 1px solid var(--sozo-blue) !important; border-radius: 999px !important; background: var(--sozo-blue) !important; color: var(--sozo-white) !important; font-size: 13px !important; font-weight: 600 !important; line-height: 1.4 !important; text-decoration: none !important; transition: background-color 180ms ease, color 180ms ease !important; }
        .sz-promo-claim-btn:hover, .sz-promo-claim-btn:focus-visible { background: var(--sozo-white) !important; color: var(--sozo-blue) !important; text-decoration: none !important; }
        .sz-promo-claim-btn svg { width: 16px !important; height: 16px !important; fill: none !important; stroke: currentColor !important; stroke-width: 1.8 !important; stroke-linecap: round !important; stroke-linejoin: round !important; }

        /* Tombol Navigasi Kategori Standar */
        button.sz-promo-nav-btn {
            position: absolute !important; top: 50% !important; transform: translateY(-50%) !important;
            width: 46px !important; height: 46px !important; border-radius: 50% !important;
            background: #ffffff !important; color: #1A2080 !important; box-shadow: 0 4px 14px rgba(26, 32, 128, 0.18) !important;
            display: flex !important; align-items: center !important; justify-content: center !important;
            cursor: pointer !important; z-index: 10 !important; border: 1px solid #e2e8f0 !important;
            padding: 0 !important; margin: 0 !important; outline: none !important; transition: all 0.3s ease !important;
            appearance: none !important; -webkit-appearance: none !important;
        }
        button.sz-promo-nav-btn:hover { background: #1A2080 !important; color: #ffffff !important; border-color: #1A2080 !important; }
        button.sz-promo-nav-btn svg { width: 24px !important; height: 24px !important; stroke: currentColor !important; fill: none !important; display: block !important; margin: 0 !important; }
        button.sz-promo-nav-btn.prev { left: -20px !important; }
        button.sz-promo-nav-btn.next { right: -20px !important; }

        /* General Promo: banner 16:9, three cards, no arrow buttons */
        .sz-promo-slider-container.sz-slider-general .sz-promo-grid { gap: 16px !important; }
        .sz-promo-slider-container.sz-slider-general button.sz-promo-nav-btn { display: none !important; }

        .sz-promo-lightbox { position: fixed !important; inset: 0 !important; z-index: 999999 !important; display: none !important; align-items: center !important; justify-content: center !important; padding: 24px !important; background: rgba(23, 23, 21, 0.88) !important; }
        .sz-promo-lightbox.is-open { display: flex !important; }
        .sz-promo-lightbox-dialog { position: relative !important; display: flex !important; align-items: center !important; justify-content: center !important; max-width: 100% !important; max-height: 100% !important; }
        #szPromoLightbox .sz-promo-lightbox-dialog img { display: block !important; width: auto !important; height: auto !important; max-width: min(78vw, 1050px) !important; max-height: 78vh !important; object-fit: contain !important; border-radius: 12px !important; background: #FFFFFF !important; }
        #szPromoLightbox .sz-promo-lightbox-close { position: absolute !important; top: 12px !important; right: 12px !important; display: inline-flex !important; align-items: center !important; justify-content: center !important; width: 42px !important; min-width: 42px !important; max-width: 42px !important; height: 42px !important; min-height: 42px !important; max-height: 42px !important; margin: 0 !important; padding: 0 !important; box-sizing: border-box !important; border: 1px solid rgba(255,255,255,.55) !important; border-radius: 50% !important; background: rgba(26, 32, 128, .92) !important; color: #FFFFFF !important; font-family: Arial, sans-serif !important; font-size: 26px !important; font-weight: 300 !important; line-height: 1 !important; cursor: pointer !important; box-shadow: 0 3px 12px rgba(23, 23, 21, .22) !important; transition: transform 180ms ease, background-color 180ms ease !important; }
        #szPromoLightbox .sz-promo-lightbox-close:hover { background: #1A2080 !important; transform: scale(1.05) !important; }
        #szPromoLightbox .sz-promo-lightbox-close:focus-visible { outline: 2px solid #FFFFFF !important; outline-offset: 3px !important; }

        /* Header Kategori */
        .sozo-promo-wrapper .sz-promo-header{
            display:flex !important; justify-content:space-between !important; align-items:center !important;
            gap:20px !important; margin-bottom:24px !important;
        }
        .sozo-promo-wrapper .sz-promo-text{ flex:1 !important; }
        .sozo-promo-wrapper .sz-promo-title{
            color:#1A2080 !important; font-family:var(--sozo-font-heading) !important;
            font-size:28px !important; font-weight:700 !important; margin:0 0 8px 0 !important; line-height:1.3 !important;
        }
        .sozo-promo-wrapper .sz-promo-subtitle{
            color:var(--sozo-text) !important; font-family:var(--sozo-font-body) !important;
            font-size:15px !important; margin:0 !important; line-height:1.5 !important;
        }
        .sozo-promo-wrapper .sz-promo-link-desktop{
            display:inline-flex !important; align-items:center !important; gap:6px !important;
            color:#1A2080 !important; font-size:15px !important; font-weight:600 !important;
            text-decoration:none !important; white-space:nowrap !important; transition:all 0.3s ease !important;
        }
        .sozo-promo-wrapper .sz-promo-link-desktop:hover{ color:#1A2080 !important; transform:translateX(4px) !important; }
        .sozo-promo-wrapper .sz-promo-footer.mobile-only{ display:none !important; }
        .sozo-promo-wrapper .sz-promo-btn-mobile{
            display:inline-flex !important; align-items:center !important; justify-content:center !important;
            width:100% !important; gap:8px !important; background:#1A2080 !important; color:#fff !important;
            padding:14px 20px !important; border-radius:30px !important; font-size:15px !important;
            font-weight:600 !important; text-decoration:none !important; box-shadow:0 4px 15px rgba(26,32,128,.2) !important;
        }

        .sozo-promo-wrapper button.sz-floating-promo-btn,
        button.sz-floating-promo-btn {
            position: fixed !important;
            bottom: 24px !important;
            right: 24px !important;
            z-index: 999 !important;
            background-color: #1A2080 !important;
            color: #FFFFFF !important;
            border: 2px solid #FFFFFF !important;
            padding: 14px 24px !important;
            border-radius: 50px !important;
            font-size: 14px !important;
            font-weight: 600 !important;
            font-family: var(--sozo-font-body) !important;
            cursor: pointer !important;
            display: flex !important;
            align-items: center !important;
            gap: 10px !important;
            box-shadow: 0 8px 25px rgba(26, 32, 128, 0.35) !important;
            transition: all 0.3s ease !important;
            outline: none !important;
            appearance: none !important;
            -webkit-appearance: none !important;
            animation: sz-pulse 2s infinite !important;
        }
        
        button.sz-floating-promo-btn.hidden { 
            opacity: 0 !important; 
            visibility: hidden !important; 
            transform: translateY(20px) !important; 
            pointer-events: none !important; 
        }
        button.sz-floating-promo-btn:hover { 
            background-color: #1A2080 !important; 
            transform: translateY(-3px) scale(1.03) !important; 
        }
        button.sz-floating-promo-btn svg { 
            width: 20px !important; 
            height: 20px !important; 
            fill: none !important; 
            stroke: currentColor !important; 
            stroke-width: 2 !important; 
            stroke-linecap: round !important; 
            stroke-linejoin: round !important; 
            display: block !important; 
        }

        .sozo-promo-wrapper .claim { background: var(--sozo-white); border: 1px solid var(--sozo-border); border-radius: 24px; box-shadow: 0 10px 30px rgba(26, 32, 128, 0.05); }
        .sozo-promo-wrapper .claim-inner { padding: 56px 48px; }
        .sozo-promo-wrapper .claim-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 28px; }
        .sozo-promo-wrapper .claim-num { font-family: var(--sozo-font-heading); font-size: 40px; font-weight: 800; color: var(--sozo-blue) !important; margin-bottom: 14px; }
        
        .sozo-promo-wrapper .claim-note { 
            margin-top: 36px !important; 
            background: transparent !important; 
            border: none !important; 
            border-top: 1px dashed var(--sozo-border) !important; 
            border-radius: 0 !important; 
            padding: 18px 0 0 0 !important; 
            display: block !important; 
            box-shadow: none !important;
        }
        .sozo-promo-wrapper .claim-note p { 
            font-size: 12.5px !important; 
            color: #64748b !important; 
            line-height: 1.6 !important; 
            margin: 0 !important; 
        }
        .sozo-promo-wrapper .claim-note strong { color: var(--sozo-blue) !important; font-weight: 700 !important; }

        .sozo-promo-wrapper .final-cta { padding: 64px 0; text-align: center; }
        .sozo-promo-wrapper .final-cta h2 { font-size: clamp(1.5rem, 2.5vw, 2rem) !important; color: var(--sozo-blue) !important; margin-bottom: 12px !important; font-weight: 700 !important; }
        .sozo-promo-wrapper .final-cta p { 
            color: var(--sozo-text) !important; 
            margin-bottom: 28px !important; 
            font-size: 15px !important; 
            line-height: 1.6 !important; 
        }

        @keyframes sz-pulse {
            0% { box-shadow: 0 0 0 0 rgba(26, 32, 128, 0.6) !important; }
            70% { box-shadow: 0 0 0 12px rgba(26, 32, 128, 0) !important; }
            100% { box-shadow: 0 0 0 0 rgba(26, 32, 128, 0) !important; }
        }

        @media (max-width: 1024px){ .sz-promo-card { flex: 0 0 calc(33.333% - 14px) !important; } }
        @media (max-width: 980px){ 
            .sozo-promo-wrapper .hero-grid, .sozo-promo-wrapper .special-inner { grid-template-columns: 1fr; } 
            .sozo-promo-wrapper .claim-grid { grid-template-columns: repeat(2,1fr); } 
            .sozo-promo-wrapper .claim-inner { padding: 36px 28px !important; }
        }
        @media (max-width: 768px){
            .sozo-promo-breadcrumb { justify-content: center !important; margin-bottom: 16px !important; }
            .sozo-promo-breadcrumb ol { justify-content: center !important; }
            .sozo-promo-wrapper .hero-actions { width: 100% !important; gap: 8px !important; }
            .sozo-promo-wrapper .hero-actions .btn-primary,
            .sozo-promo-wrapper .hero-actions .btn-ghost { flex: 1 1 0 !important; min-width: 0 !important; padding: 11px 8px !important; font-size: 13px !important; line-height: 1.35 !important; text-align: center !important; white-space: nowrap !important; }
            .sozo-promo-wrapper .hero p { margin-bottom: 22px !important; }
            .sozo-promo-wrapper .special-cta-desktop { display: none !important; }
            .sozo-promo-wrapper .special-cta-mobile { display: inline !important; }
            .sz-promo-wrapper { padding: 0 16px !important; margin: 24px auto !important; }
            .sz-promo-wrapper .sz-promo-header{ flex-direction:column !important; align-items:flex-start !important; gap:12px !important; margin-bottom:20px !important; }
            .sz-promo-wrapper .sz-promo-title{ font-size:22px !important; }
            .sozo-promo-wrapper .sz-promo-subtitle{ font-size:14px !important; }
            .sozo-promo-wrapper .sz-promo-link-desktop, button.sz-promo-nav-btn { display: none !important; }
            .mobile-only { display: block !important; }
            .sz-promo-grid { gap: 16px !important; padding-bottom: 10px !important; }
            .sz-promo-card { flex: 0 0 min(82%, 340px) !important; border-radius: 12px !important; }
            .sz-promo-card.sz-card-banner { flex-basis: min(82%, 340px) !important; }
            .sozo-promo-wrapper .sz-promo-footer{ margin-top: 10px !important; text-align: center !important; }
            .sz-promo-btn-mobile { display: inline-flex !important; align-items: center !important; justify-content: center !important; width: 100% !important; box-sizing: border-box !important; gap: 8px !important; background-color: #1A2080 !important; color: #ffffff !important; padding: 14px 20px !important; border-radius: 30px !important; font-size: 15px !important; font-weight: 600 !important; text-decoration: none !important; box-shadow: 0 4px 12px rgba(26,32,128,0.2) !important; }
            .sozo-promo-wrapper #all-promos { padding-bottom: 8px !important; }
            .sozo-promo-wrapper .cat-block { margin-bottom: 32px !important; }
            .sozo-promo-wrapper .cat-block:last-of-type { margin-bottom: 8px !important; }
            .sozo-promo-wrapper #claim { padding-top: 20px !important; }
            .sz-promo-lightbox { padding: 16px !important; }
            #szPromoLightbox .sz-promo-lightbox-dialog img { max-width: calc(100vw - 32px) !important; max-height: calc(100dvh - 32px) !important; }
        }
        @media (max-width: 560px){ .sozo-promo-wrapper .claim-grid { grid-template-columns: 1fr; } }
        @media (max-width: 560px){ .sozo-promo-wrapper .claim-grid { grid-template-columns: 1fr; } }
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
