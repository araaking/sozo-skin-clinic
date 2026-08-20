<?php
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

function sozo_render_category_gallery_slider($category_slug) {
    $term = get_term_by('slug', $category_slug, 'promo_category');
    $images = array();

    if ($term) {
        $gallery_ids = get_term_meta($term->term_id, 'sozo_promo_gallery', true);
        if (!empty($gallery_ids)) {
            $ids = explode(',', $gallery_ids);
            foreach ($ids as $id) {
                $url = wp_get_attachment_image_url($id, 'full');
                $alt = get_post_meta($id, '_wp_attachment_image_alt', true);
                if ($url) {
                    $images[] = array('url' => $url, 'alt' => $alt ? $alt : 'Promo SOZO Skin');
                }
            }
        }
    }

    if (empty($images)) {
        return '<p style="font-size:14px; color:#666;">Belum ada promo aktif untuk kategori ini.</p>';
    }

    ob_start();
    ?>
    <div class="sz-promo-slider-container">
        <button class="sz-promo-nav-btn prev" aria-label="Previous Slide" type="button">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"/></svg>
        </button>

        <div class="sz-promo-grid">
            <?php 
            $i = 0;
            foreach ($images as $img) : 
                $i++;
                $fetch_priority = ($i <= 2) ? 'fetchpriority="high"' : 'loading="lazy"';
            ?>
                <div class="sz-promo-card" data-full-img="<?php echo esc_url($img['url']); ?>" data-modal-id="szPromoModal">
                    <div class="sz-promo-image">
                        <img src="<?php echo esc_url($img['url']); ?>" alt="<?php echo esc_attr($img['alt']); ?>" <?php echo $fetch_priority; ?>>
                        <div class="sz-promo-overlay">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line><line x1="11" y1="8" x2="11" y2="14"></line><line x1="8" y1="11" x2="14" y2="11"></line></svg>
                            <span>Lihat Detail</span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <button class="sz-promo-nav-btn next" aria-label="Next Slide" type="button">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></svg>
        </button>
    </div>
    <?php
    // SCRIPT INLINE DIHAPUS DARI SINI UNTUK MENCEGAH KONFLIK DENGAN WP_FOOTER
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
                    <span class="hero-eyebrow">Promo Bulan Ini</span>
                    <h1>Bisa cantik<br>tanpa <em>bikin ragu.</em></h1>
                    <p>Diskon treatment wajah, tubuh, dan rambut — plus cicilan 0% dan cashback hingga Rp500.000 untuk booking bulan ini.</p>
                    <div class="hero-actions">
                        <a href="#all-promos" class="btn-primary">Lihat Semua Promo</a>
                        <a href="<?php echo esc_url($wa_link); ?>" target="_blank" class="btn-ghost">Konsultasi Gratis</a>
                    </div>
                </div>
                <div class="hero-visual">
                    <div class="hero-poster">
                        <img src="https://asset.sozoskinclinic.com/wp-content/uploads/2026/08/agustus_halaman_1.webp" alt="Promo SOZO Skin">
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
                            <a href="<?php echo esc_url($wa_link); ?>" target="_blank" class="special-cta">Klaim Promo Pembayaran</a>
                        </div>
                        <div class="special-visual">
                            <img src="https://asset.sozoskinclinic.com/wp-content/uploads/2026/08/agustus_halaman_2.webp" alt="Cicilan 0% SOZO Skin">
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
                                <p>Klik promo visual pilihanmu untuk melihat detail gambar penawaran.</p>
                            </div>
                            <div class="claim-step">
                                <div class="claim-num">02</div>
                                <h4>Booking via WA</h4>
                                <p>Klik tombol WhatsApp di modal untuk langsung booking jadwal dan amankan slot promo.</p>
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

// 5.6. Master Shortcode (Semua Gabung Jadi Satu)
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
        
        <!-- FLOATING BUTTON (Hanya Muncul Jika Memanggil Master Landing Page) -->
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

// 5.7. Shortcode Per Kategori Spesifik (dengan custom WA link + header opsional)
add_shortcode('sozo_promo_kategori', function($atts) {
    $atts = shortcode_atts(array(
        'slug'       => '', 
        'title'      => '',
        'wa_link'    => '',
        'subtitle'   => 'Wujudkan Kulit Impian dengan Cicilan 0%! Klaim Promonya Sekarang, Syarat & Ketentuan Berlaku.',
        'promo_url'  => 'https://sozoskinclinic.com/promo/',
        'link_text'  => 'Lihat Promo Lengkapnya',
        'show_header'=> 'true'
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

            <?php echo sozo_render_category_gallery_slider($atts['slug']); ?>

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


// ============ 6. INJECT STRICT CSS ============
add_action('wp_head', function() {
    ?>
    <style id="sozo-promo-inline-css">
        :root {
            --sozo-blue: #1A2080;
            --sozo-blue-hover: #2a33a3;
            --sozo-cyan: #3AB4F2;
            --sozo-text: #333333;
            --sozo-border: #e5e7eb;
            --sozo-light-bg: #F4F8FA;
            --sozo-white: #FFFFFF;
            --sozo-font-heading: 'Poppins', 'Inter', system-ui, -apple-system, sans-serif;
            --sozo-font-body: 'Inter', system-ui, -apple-system, sans-serif;
            --card-radius: 20px;
        }

        .sozo-promo-wrapper { background: var(--sozo-light-bg); color: var(--sozo-text); font-family: var(--sozo-font-body); }
        .sozo-promo-wrapper h1, .sozo-promo-wrapper h2, .sozo-promo-wrapper h3, .sozo-promo-wrapper h4 { font-family: var(--sozo-font-heading); color: var(--sozo-blue); }
        .sozo-promo-wrapper .wrap { max-width: 1160px; margin: 0 auto; padding: 0 32px; }

        .sozo-promo-wrapper .hero { padding: 56px 0 64px; }
        .sozo-promo-wrapper .hero-grid { display: grid; grid-template-columns: 1fr 0.86fr; gap: 48px; align-items: center; }
        .sozo-promo-wrapper .hero-eyebrow { display: inline-flex; align-items: center; gap: 8px; color: var(--sozo-cyan); font-size: 12px; font-weight: 600; letter-spacing: .12em; text-transform: uppercase; margin-bottom: 20px; }
        .sozo-promo-wrapper .hero-eyebrow::before { content: ""; width: 20px; height: 1.5px; background: var(--sozo-cyan); }
        .sozo-promo-wrapper .hero h1 { font-size: clamp(2rem, 3.5vw, 2.75rem); line-height: 1.25; color: var(--sozo-blue); font-weight: 700; margin-bottom: 18px; }
        .sozo-promo-wrapper .hero h1 em { font-style: normal; color: var(--sozo-cyan); font-weight: 800; }
        .sozo-promo-wrapper .hero p { font-size: 16px; color: var(--sozo-text); line-height: 1.65; max-width: 430px; margin-bottom: 30px; }
        .sozo-promo-wrapper .hero-actions { display: flex; gap: 12px; align-items: center; }
        
        .sozo-promo-wrapper .btn-primary { 
            background: var(--sozo-blue) !important; 
            color: var(--sozo-white) !important; 
            border: none !important; 
            padding: 14px 26px !important; 
            border-radius: 8px !important; 
            font-weight: 600 !important; 
            font-size: 14.5px !important; 
            text-decoration: none !important; 
            display: inline-block !important; 
            transition: background-color .2s !important; 
        }
        .sozo-promo-wrapper .btn-primary:hover { background: var(--sozo-blue-hover) !important; color: #fff !important; }
        
        .sozo-promo-wrapper .btn-ghost { 
            background: transparent !important; 
            border: 1.5px solid var(--sozo-border) !important; 
            color: var(--sozo-blue) !important; 
            padding: 13px 22px !important; 
            border-radius: 8px !important; 
            font-weight: 500 !important; 
            font-size: 14.5px !important; 
            text-decoration: none !important; 
            display: inline-block !important; 
            transition: all .2s !important; 
        }
        .sozo-promo-wrapper .btn-ghost:hover { border-color: var(--sozo-blue) !important; background: var(--sozo-white) !important; }
        
        .sozo-promo-wrapper .hero-visual { position: relative; }
        .sozo-promo-wrapper .hero-poster { width: 100%; aspect-ratio: 1/1; border-radius: 20px; overflow: hidden; box-shadow: 0 24px 48px -18px rgba(26, 32, 128, 0.2); }
        .sozo-promo-wrapper .hero-poster img { width: 100%; height: 100%; object-fit: cover; }
        .sozo-promo-wrapper .hero-tag { position: absolute; top: 18px; left: 18px; background: var(--sozo-white); color: var(--sozo-blue); font-size: 12px; font-weight: 600; padding: 8px 14px; border-radius: 999px; box-shadow: 0 8px 16px -6px rgba(0,0,0,.15); }

        .sozo-promo-wrapper .section { padding: 60px 0; }
        
        .sozo-promo-wrapper .section-head { 
            display: flex !important; 
            justify-content: space-between !important; 
            align-items: flex-end !important; 
            margin-bottom: 32px !important; 
            gap: 24px !important; 
            flex-wrap: wrap !important; 
        }
        .sozo-promo-wrapper .section-eyebrow { font-size: 12px; font-weight: 600; letter-spacing: .1em; text-transform: uppercase; color: var(--sozo-cyan); margin-bottom: 8px; display: block; }
        .sozo-promo-wrapper .section-head h2 { font-size: clamp(1.5rem, 2.5vw, 2rem) !important; color: var(--sozo-blue) !important; font-weight: 700 !important; margin: 0 !important; line-height: 1.1 !important; }
        
        .sozo-promo-wrapper .section-head p { 
            color: var(--sozo-text) !important; 
            font-size: 14.5px !important; 
            max-width: 320px !important; 
            line-height: 1.5 !important;
            margin: 0 !important;
        }

        .sozo-promo-wrapper .special { background: linear-gradient(135deg, var(--sozo-blue) 0%, var(--sozo-blue-hover) 100%); border-radius: 24px; overflow: hidden; color: var(--sozo-white); }
        .sozo-promo-wrapper .special-inner { display: grid; grid-template-columns: 1.1fr 0.9fr; align-items: center; }
        .sozo-promo-wrapper .special-content { padding: 52px 48px; }
        .sozo-promo-wrapper .special-content h2 { color: var(--sozo-white); font-size: clamp(1.5rem, 2.5vw, 2rem); margin-bottom: 14px; font-weight: 700; }
        .sozo-promo-wrapper .special-content h2 em { font-style: normal; color: var(--sozo-cyan); font-weight: 800; }
        .sozo-promo-wrapper .special-content p { color: rgba(255, 255, 255, 0.85); font-size: 14.5px; max-width: 380px; margin-bottom: 24px; line-height: 1.6; }
        .sozo-promo-wrapper .pay-logos { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 26px; }
        
        .sozo-promo-wrapper .pay-logos span { 
            background: rgba(255, 255, 255, 0.12) !important; 
            border: 1px solid rgba(255, 255, 255, 0.2) !important; 
            padding: 7px 12px !important; 
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
            border: none !important; 
            padding: 13px 24px !important; 
            border-radius: 8px !important; 
            font-weight: 600 !important; 
            font-size: 14px !important; 
            cursor: pointer !important; 
            display: inline-block !important;
            text-decoration: none !important;
            box-shadow: 0 4px 14px rgba(0,0,0,0.1) !important;
            outline: none !important;
            appearance: none !important;
            -webkit-appearance: none !important;
            transition: background-color .2s !important; 
        }
        .sozo-promo-wrapper .special-cta:hover { background: var(--sozo-light-bg) !important; color: var(--sozo-blue) !important; }
        
        .sozo-promo-wrapper .special-visual { height: 100%; min-height: 380px; }
        .sozo-promo-wrapper .special-visual img { width: 100%; height: 100%; object-fit: cover; }

        .sozo-promo-wrapper .cat-nav { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 44px; }
        .sozo-promo-wrapper .cat-nav a { 
            text-decoration: none !important; 
            color: var(--sozo-text) !important; 
            font-weight: 600 !important; 
            font-size: 13.5px !important; 
            padding: 9px 16px !important; 
            border-radius: 999px !important; 
            border: 1px solid var(--sozo-border) !important; 
            background: var(--sozo-white) !important; 
            display: inline-block !important;
            transition: all .2s !important; 
        }
        .sozo-promo-wrapper .cat-nav a:hover { color: var(--sozo-blue) !important; border-color: var(--sozo-blue) !important; background: var(--sozo-light-bg) !important; }
        .sozo-promo-wrapper .cat-block { margin-bottom: 64px; scroll-margin-top: 90px; }
        .sozo-promo-wrapper .cat-block-head { display: flex; align-items: baseline; gap: 12px; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid var(--sozo-border); }
        .sozo-promo-wrapper .cat-block-head h3 { font-size: 1.25rem; color: var(--sozo-blue); font-weight: 600; }

        .sz-promo-slider-container { position: relative !important; }
        .sz-promo-grid { display: flex !important; flex-wrap: nowrap !important; overflow-x: auto !important; scroll-snap-type: x mandatory !important; scroll-behavior: smooth !important; gap: 20px !important; padding: 10px 0 20px 0 !important; -webkit-overflow-scrolling: touch !important; scrollbar-width: none !important; }
        .sz-promo-grid::-webkit-scrollbar { display: none !important; }
        .sz-promo-card { flex: 0 0 calc(25% - 15px) !important; scroll-snap-align: start !important; cursor: pointer !important; border-radius: var(--card-radius) !important; overflow: hidden !important; box-shadow: 0 4px 15px rgba(0,0,0,0.06) !important; transition: transform 0.3s ease, box-shadow 0.3s ease !important; background: #fff !important; position: relative !important; aspect-ratio: 1 / 1 !important; outline: none !important; border: none !important; }
        .sz-promo-card:focus-visible { outline: 3px solid #1A2080 !important; outline-offset: 2px !important; }
        .sz-promo-card:hover { transform: translateY(-6px) !important; box-shadow: 0 12px 24px rgba(26, 32, 128, 0.15) !important; }
        .sz-promo-image { width: 100% !important; height: 100% !important; position: relative !important; }
        .sz-promo-image img { width: 100% !important; height: 100% !important; object-fit: cover !important; display: block !important; transition: transform 0.5s ease !important; }
        .sz-promo-card:hover .sz-promo-image img { transform: scale(1.05) !important; }
        .sz-promo-overlay { position: absolute !important; top: 0 !important; left: 0 !important; width: 100% !important; height: 100% !important; background: rgba(26, 32, 128, 0.6) !important; display: flex !important; flex-direction: column !important; align-items: center !important; justify-content: center !important; color: #fff !important; opacity: 0 !important; transition: opacity 0.3s ease !important; }
        .sz-promo-card:hover .sz-promo-overlay, .sz-promo-card:focus .sz-promo-overlay { opacity: 1 !important; }
        .sz-promo-overlay svg { margin-bottom: 8px !important; }
        .sz-promo-overlay span { font-size: 14px !important; font-weight: 600 !important; letter-spacing: 0.5px !important; }

        button.sz-promo-nav-btn {
            position: absolute !important; top: 50% !important; transform: translateY(-50%) !important;
            width: 46px !important; height: 46px !important; border-radius: 50% !important;
            background: #ffffff !important; color: #1A2080 !important; box-shadow: 0 4px 14px rgba(0,0,0,0.18) !important;
            display: flex !important; align-items: center !important; justify-content: center !important;
            cursor: pointer !important; z-index: 10 !important; border: 1px solid #e2e8f0 !important;
            padding: 0 !important; margin: 0 !important; outline: none !important; transition: all 0.3s ease !important;
            appearance: none !important; -webkit-appearance: none !important;
        }
        button.sz-promo-nav-btn:hover { background: #1A2080 !important; color: #ffffff !important; border-color: #1A2080 !important; }
        button.sz-promo-nav-btn svg { width: 24px !important; height: 24px !important; stroke: currentColor !important; fill: none !important; display: block !important; margin: 0 !important; }
        button.sz-promo-nav-btn.prev { left: -20px !important; }
        button.sz-promo-nav-btn.next { right: -20px !important; }

        /* Header untuk shortcode kategori saja */
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
            color:#475569 !important; font-family:var(--sozo-font-body) !important;
            font-size:15px !important; margin:0 !important; line-height:1.5 !important;
        }
        .sozo-promo-wrapper .sz-promo-link-desktop{
            display:inline-flex !important; align-items:center !important; gap:6px !important;
            color:#1A2080 !important; font-size:15px !important; font-weight:600 !important;
            text-decoration:none !important; white-space:nowrap !important; transition:all 0.3s ease !important;
        }
        .sozo-promo-wrapper .sz-promo-link-desktop:hover{ color:#131861 !important; transform:translateX(4px) !important; }
        .sozo-promo-wrapper .sz-promo-footer.mobile-only{ display:none !important; }
        .sozo-promo-wrapper .sz-promo-btn-mobile{
            display:inline-flex !important; align-items:center !important; justify-content:center !important;
            width:100% !important; gap:8px !important; background:#1A2080 !important; color:#fff !important;
            padding:14px 20px !important; border-radius:30px !important; font-size:15px !important;
            font-weight:600 !important; text-decoration:none !important; box-shadow:0 4px 12px rgba(26,32,128,.2) !important;
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
            background-color: #2a33a3 !important; 
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

        .sz-promo-modal { display: none; position: fixed !important; z-index: 999999 !important; top: 0 !important; left: 0 !important; width: 100% !important; height: 100% !important; background-color: rgba(0,0,0,0.85) !important; backdrop-filter: blur(5px) !important; align-items: center !important; justify-content: center !important; opacity: 0; transition: opacity 0.3s ease !important; }
        .sz-promo-modal.show { display: flex !important; opacity: 1 !important; }
        .sz-modal-content { position: relative !important; max-width: 420px !important; width: 90% !important; max-height: 90vh !important; animation: sz-zoomIn 0.3s ease !important; }
        .sz-modal-body { display: flex !important; flex-direction: column !important; align-items: center !important; gap: 14px !important; }
        .sz-modal-content img { max-width: 100% !important; max-height: 62vh !important; border-radius: 12px !important; box-shadow: 0 10px 30px rgba(0,0,0,0.5) !important; display: block !important; object-fit: contain !important; }
        .sz-promo-wa-btn { display: inline-flex !important; align-items: center !important; justify-content: center !important; gap: 10px !important; background-color: #1A2080 !important; color: #ffffff !important; padding: 14px 32px !important; border-radius: 50px !important; font-size: 16px !important; font-weight: 600 !important; text-decoration: none !important; transition: all 0.3s ease !important; border: 2px solid #1A2080 !important; box-shadow: 0 4px 15px rgba(26,32,128,0.2) !important; }
        .sz-promo-wa-btn:hover { background-color: #ffffff !important; color: #1A2080 !important; transform: translateY(-2px) !important; box-shadow: 0 8px 20px rgba(26,32,128,0.3) !important; }
        .sz-promo-wa-btn svg { width: 20px !important; height: 20px !important; fill: currentColor !important; }
        .sz-modal-close { position: absolute !important; top: -40px !important; right: 0px !important; color: #fff !important; font-size: 35px !important; font-weight: bold !important; cursor: pointer !important; transition: color 0.3s !important; z-index: 100001 !important; line-height: 1 !important; pointer-events: auto !important; padding: 6px 10px !important; user-select: none !important; }

        .sozo-promo-wrapper .claim { background: var(--sozo-white); border: 1px solid var(--sozo-border); border-radius: 24px; }
        .sozo-promo-wrapper .claim-inner { padding: 56px 48px; }
        .sozo-promo-wrapper .claim-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 28px; }
        .sozo-promo-wrapper .claim-num { font-family: var(--sozo-font-heading); font-size: 38px; font-weight: 700; color: var(--sozo-cyan); margin-bottom: 14px; }
        
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
            color: #6b7280 !important; 
            line-height: 1.6 !important; 
            margin: 0 !important; 
        }
        .sozo-promo-wrapper .claim-note strong { color: var(--sozo-text) !important; font-weight: 600 !important; }

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

        @keyframes sz-zoomIn { from { transform: scale(0.95); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        @keyframes sz-pulse { 0% { box-shadow: 0 0 0 0 rgba(26,32,128,0.6); } 70% { box-shadow: 0 0 0 12px rgba(26,32,128,0); } 100% { box-shadow: 0 0 0 0 rgba(26,32,128,0); } }
        @media (max-width: 1024px){ .sz-promo-card { flex: 0 0 calc(33.333% - 14px) !important; } }
        @media (max-width: 980px){ 
            .sozo-promo-wrapper .hero-grid, .sozo-promo-wrapper .special-inner { grid-template-columns: 1fr; } 
            .sozo-promo-wrapper .claim-grid { grid-template-columns: repeat(2,1fr); } 
            .sozo-promo-wrapper .claim-inner { padding: 36px 28px !important; }
        }
        @media (max-width: 768px){
            .sz-promo-wrapper { padding: 0 16px !important; margin: 24px auto !important; }
            .sz-promo-wrapper .sz-promo-header{ flex-direction:column !important; align-items:flex-start !important; gap:12px !important; margin-bottom:20px !important; }
            .sz-promo-wrapper .sz-promo-title{ font-size:22px !important; }
            .sozo-promo-wrapper .sz-promo-subtitle{ font-size:14px !important; }
            .sozo-promo-wrapper .sz-promo-link-desktop, button.sz-promo-nav-btn { display: none !important; }
            .mobile-only { display: block !important; }
            .sz-promo-grid { gap: 16px !important; padding-bottom: 10px !important; }
            .sz-promo-card { flex: 0 0 82% !important; border-radius: 16px !important; }
            .sozo-promo-wrapper .sz-promo-footer{ margin-top: 10px !important; text-align: center !important; }
            .sz-promo-btn-mobile { display: inline-flex !important; align-items: center !important; justify-content: center !important; width: 100% !important; box-sizing: border-box !important; gap: 8px !important; background-color: #1A2080 !important; color: #ffffff !important; padding: 14px 20px !important; border-radius: 30px !important; font-size: 15px !important; font-weight: 600 !important; text-decoration: none !important; box-shadow: 0 4px 12px rgba(26,32,128,0.2) !important; }
            .sz-modal-content { max-width: 92% !important; }
            .sz-modal-content img { max-height: 68vh !important; }
            .sz-modal-close { top: -35px !important; right: 0px !important; font-size: 30px !important; }
            .sz-promo-wa-btn { width: 100% !important; padding: 12px 20px !important; font-size: 14px !important; }
        }
        @media (max-width: 560px){ .sozo-promo-wrapper .claim-grid { grid-template-columns: 1fr; } }
    </style>
    <?php
});

// ============ 7. INJECT GLOBAL MODAL & JS ============
add_action('wp_footer', function() {
    $wa_link = sozo_get_wa_link();
    ?>
    <!-- LIGHTBOX MODAL GLOBAL -->
    <div id="szPromoModal" class="sz-promo-modal">
        <div class="sz-modal-content">
            <span class="sz-modal-close" data-modal-id="szPromoModal">&times;</span>
            <div class="sz-modal-body">
                <img class="sz-promo-modal-img" src="" alt="Detail Promo">
                <a href="<?php echo esc_url($wa_link); ?>" target="_blank" class="sz-promo-wa-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                        <path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/>
                    </svg>
                    Klaim Promo Sekarang
                </a>
            </div>
        </div>
    </div>

    <!-- SCRIPT INTERAKSI UI (CONSOLIDATED) -->
    <script id="sozo-promo-inline-js">
    (function(){
        // Cegah eksekusi ganda jika wp_footer dipanggil dua kali oleh tema
        if (window.__sozoPromoJSLoaded) return;
        window.__sozoPromoJSLoaded = true;

        window.sozoOpenPromoModal = function(imageSrc, modalId, cardElement) {
            let modal = document.getElementById(modalId || 'szPromoModal');
            // jika ada duplikat id (contoh lama + plugin), pilih yang ada WA button
            if (modal && !modal.querySelector('.sz-promo-wa-btn') && document.querySelectorAll('#' + (modalId || 'szPromoModal')).length > 1) {
                const all = document.querySelectorAll('#' + (modalId || 'szPromoModal'));
                for (let i=0;i<all.length;i++){ if(all[i].querySelector('.sz-promo-wa-btn')){ modal = all[i]; break; } }
            }
            if (!modal) return;
            // support dua versi modal: plugin (class) dan contoh lama (id)
            let modalImg = modal.querySelector('.sz-promo-modal-img') || modal.querySelector('#szPromoModalImage');
            let modalWaBtn = modal.querySelector('.sz-promo-wa-btn');
            // auto-inject WA button kalau modal contoh lama tanpa button
            if (!modalWaBtn) {
                const waHref = (function(){
                    const cb = cardElement ? cardElement.closest('.cat-block') : null;
                    const cw = cb ? (cb.dataset.customWa || cb.getAttribute('data-custom-wa')) : null;
                    return cw || "<?php echo esc_js(sozo_get_wa_link()); ?>";
                })();
                const btnHtml = '<a href="'+waHref+'" target="_blank" rel="noopener" class="sz-promo-wa-btn" style="display:inline-flex !important;align-items:center !important;gap:10px !important;background:#1A2080 !important;color:#fff !important;padding:14px 32px !important;border-radius:50px !important;font-size:15px !important;font-weight:600 !important;text-decoration:none !important;box-shadow:0 4px 15px rgba(26,32,128,.3) !important;"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" width="20" height="20" fill="currentColor"><path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157z"/></svg> Klaim Promo Sekarang</a>';
                const body = modal.querySelector('.sz-modal-body') || modal.querySelector('.sz-modal-content') || modal;
                body.insertAdjacentHTML('beforeend', btnHtml);
                modalWaBtn = modal.querySelector('.sz-promo-wa-btn');
                // jika modal contoh lama pakai #szPromoModalImage, tambahkan class biar ter-styling
                if (modalImg && !modalImg.classList.contains('sz-promo-modal-img')) modalImg.classList.add('sz-promo-modal-img');
            }
            // Atur Link WA dinamis
            if (modalWaBtn) {
                const catBlock = cardElement ? cardElement.closest('.cat-block') : null;
                const customWa = catBlock ? (catBlock.dataset.customWa || catBlock.getAttribute('data-custom-wa')) : null;
                if (!modalWaBtn.dataset.defaultWa) {
                    modalWaBtn.dataset.defaultWa = modalWaBtn.href;
                }
                modalWaBtn.href = customWa ? customWa : modalWaBtn.dataset.defaultWa;
            }
            if (modalImg && imageSrc) modalImg.src = imageSrc;
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        };

        window.sozoClosePromoModal = function(modalId) {
            // close semua modal dengan id tersebut (handle duplikat)
            const modals = modalId ? document.querySelectorAll('#' + modalId) : document.querySelectorAll('.sz-promo-modal.show');
            if (!modals.length) {
                const single = document.getElementById(modalId || 'szPromoModal');
                if (!single) return;
                single.classList.remove('show');
                setTimeout(function () {
                    if (!single.classList.contains('show')) {
                        document.body.style.overflow = '';
                        const img = single.querySelector('.sz-promo-modal-img') || single.querySelector('#szPromoModalImage');
                        if (img) img.src = '';
                    }
                }, 300);
                return;
            }
            modals.forEach(function(modal){
                if (!modal.classList.contains('show')) return;
                modal.classList.remove('show');
                setTimeout(function () {
                    if (!modal.classList.contains('show')) {
                        // hanya reset overflow jika tidak ada modal lain yang masih show
                        if (!document.querySelector('.sz-promo-modal.show')) document.body.style.overflow = '';
                        const img = modal.querySelector('.sz-promo-modal-img') || modal.querySelector('#szPromoModalImage');
                        if (img) img.src = '';
                    }
                }, 300);
            });
        };

        function szInitEvents(){
            // Gunakan Event Delegation murni (Cocok untuk Elementor yang merender DOM dinamis)
            document.addEventListener('click', function (e) {
                // 1. Klik pada Card Promo
                const card = e.target.closest('.sz-promo-card');
                if (card) {
                    let src = card.dataset.fullImg || card.getAttribute('data-full-img');
                    if (!src) {
                        const img = card.querySelector('img');
                        if (img) src = img.src;
                    }
                    const mid = card.dataset.modalId || card.getAttribute('data-modal-id') || 'szPromoModal';
                    window.sozoOpenPromoModal(src, mid, card);
                    return;
                }

                // 2. Klik pada tombol Close Modal — close modal terdekat langsung (tahan duplikat id)
                const closeBtn = e.target.closest('.sz-modal-close');
                if (closeBtn) {
                    const parentModal = closeBtn.closest('.sz-promo-modal');
                    if (parentModal) {
                        parentModal.classList.remove('show');
                        document.body.style.overflow = '';
                        const img = parentModal.querySelector('.sz-promo-modal-img') || parentModal.querySelector('#szPromoModalImage');
                        if (img) setTimeout(function(){ if(!parentModal.classList.contains('show')) img.src=''; },300);
                    } else {
                        const mid = closeBtn.dataset.modalId || closeBtn.getAttribute('data-modal-id') || 'szPromoModal';
                        window.sozoClosePromoModal(mid);
                    }
                    return;
                }

                // 3. Klik Area Luar Modal (Backdrop)
                const modal = e.target.closest('.sz-promo-modal');
                if (modal && e.target === modal) {
                    window.sozoClosePromoModal(modal.id || 'szPromoModal');
                    return;
                }

                // 4. Klik Navigasi Slider
                const navBtn = e.target.closest('.sz-promo-nav-btn');
                if (navBtn) {
                    const container = navBtn.closest('.sz-promo-slider-container');
                    if (!container) return;
                    const grid = container.querySelector('.sz-promo-grid');
                    if (!grid) return;
                    const scrollAmount = grid.clientWidth * 0.80;
                    if (navBtn.classList.contains('prev')) {
                        grid.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
                    } else {
                        grid.scrollBy({ left: scrollAmount, behavior: 'smooth' });
                    }
                    return;
                }

                // 5. Klik Tombol Floating
                const floatBtn = e.target.closest('.sz-floating-promo-btn');
                if (floatBtn) {
                    const targetSection = document.querySelector('#all-promos');
                    if (targetSection) {
                        targetSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }
            });

            // Tutup Modal via tombol Escape
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    document.querySelectorAll('.sz-promo-modal.show').forEach(function (m) {
                        window.sozoClosePromoModal(m.id);
                    });
                }
            });

            // Logika sembunyikan tombol floating saat user sudah melihat seksi promo
            window.addEventListener('scroll', function () {
                const floatBtn = document.querySelector('.sz-floating-promo-btn');
                const promoSection = document.querySelector('#all-promos');
                if (!floatBtn || !promoSection) return;
                const rect = promoSection.getBoundingClientRect();
                const isVisible = rect.top < window.innerHeight && rect.bottom >= 0;
                if (isVisible) {
                    floatBtn.classList.add('hidden');
                } else {
                    floatBtn.classList.remove('hidden');
                }
            });
        }

        // Pastikan event di-bind tanpa peduli status readyState DOM
        szInitEvents();

    })();
    </script>
    <?php
});