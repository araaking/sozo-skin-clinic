/**
 * ==========================================================
 * SOZO SKIN - HOMEPAGE BLOG SHORTCODE [sozoskin_home]
 * Modern Minimalist Editorial Design (Compact & Theme-Safe)
 * ==========================================================
 */

add_shortcode('sozoskin_home', 'sz_home_shortcode');

function sz_home_shortcode() {
    ob_start();

    // Deteksi Halaman Saat Ini (SEO Friendly)
    $paged = (get_query_var('paged')) ? get_query_var('paged') : ((get_query_var('page')) ? get_query_var('page') : 1);
    $posts_per_page = 6;
    $search_query   = sanitize_text_field(get_search_query() ?: (isset($_GET['s']) ? $_GET['s'] : ''));

    // Category Links
    $skin_cat      = get_category_by_slug('skin');
    $skin_link     = $skin_cat ? get_category_link($skin_cat->term_id) : home_url('/blog/category/skin/');
    $hair_cat      = get_category_by_slug('hair');
    $hair_link     = $hair_cat ? get_category_link($hair_cat->term_id) : home_url('/blog/category/hair/');
    $slimming_cat  = get_category_by_slug('slimming');
    $slimming_link = $slimming_cat ? get_category_link($slimming_cat->term_id) : home_url('/blog/category/slimming/');
    $all_link      = home_url('/blog/');

    // 1. Dapatkan Hero Post (Hanya di page 1 dan jika tidak sedang search)
    $hero_id = 0;
    $hero_query = null;

    if ($paged == 1 && empty($search_query)) {
        $hero_query = new WP_Query([
            'posts_per_page'      => 1,
            'post_status'         => 'publish',
            'orderby'             => 'post_modified',
            'order'               => 'DESC',
            'no_found_rows'       => true,
            'ignore_sticky_posts' => true,
        ]);

        if ($hero_query->have_posts()) {
            $hero_id = $hero_query->posts[0]->ID;
        }
    }
    ?>

    <div class="sz-blog-wrapper">

        <!-- HERO HEADER SECTION (Gradient Background - Compact) -->
        <section class="sz-hero-banner-section">
            <div class="sz-hero-container">
                <div class="sz-hero-header-text">
                    <span class="sz-hero-badge">Edukasi & Artikel Medis</span>
                    <h1 class="sz-hero-main-title">Artikel & Edukasi Perawatan Kulit</h1>
                    <p class="sz-hero-subtitle">
                        Temukan panduan medis terpercaya seputar kesehatan kulit, rambut, dan estetika dari tim dokter spesialis Sozo Skin Clinic.
                    </p>
                </div>

                <?php if ($paged == 1 && empty($search_query) && $hero_query && $hero_query->have_posts()) : ?>
                    <?php
                    $hero_query->the_post();
                    $hero_post_id  = get_the_ID();
                    $hero_cats     = get_the_category();
                    $hero_cat_name = !empty($hero_cats) ? esc_html($hero_cats[0]->name) : 'Artikel';
                    $hero_thumb_id = get_post_thumbnail_id($hero_post_id);
                    $hero_thumb    = $hero_thumb_id ? wp_get_attachment_image_url($hero_thumb_id, 'full') : 'https://asset.sozoskinclinic.com/wp-content/uploads/2026/06/LP-Facial-Terdekat-V2-Hero-Image.webp';
                    $hero_title    = get_the_title();
                    $hero_excerpt  = wp_trim_words(get_the_excerpt(), 18, '...');

                    // Perhitungan status tanggal perbarui vs terbit
                    $u_time = get_the_modified_time('U', $hero_post_id);
                    $p_time = get_the_time('U', $hero_post_id);
                    if (($u_time - $p_time) > 86400) {
                        $hero_date_display = 'Diperbarui: ' . get_the_modified_date('j M Y', $hero_post_id);
                    } else {
                        $hero_date_display = get_the_date('j M Y', $hero_post_id);
                    }
                    ?>
                    <article class="sz-featured-hero-card">
                        <div class="sz-featured-hero-bg">
                            <img src="<?php echo esc_url($hero_thumb); ?>" alt="<?php echo esc_attr($hero_title); ?>" decoding="async" fetchpriority="high">
                            <div class="sz-featured-hero-overlay"></div>
                        </div>

                        <div class="sz-featured-hero-content">
                            <div class="sz-featured-hero-top">
                                <span class="sz-featured-tag"><?php echo $hero_cat_name; ?></span>
                                <a href="<?php the_permalink(); ?>" class="sz-featured-arrow-link" aria-label="Baca artikel">
                                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <line x1="7" y1="17" x2="17" y2="7"></line>
                                        <polyline points="7 7 17 7 17 17"></polyline>
                                    </svg>
                                </a>
                            </div>

                            <h2 class="sz-featured-title">
                                <a href="<?php the_permalink(); ?>"><?php echo esc_html($hero_title); ?></a>
                            </h2>

                            <p class="sz-featured-excerpt"><?php echo esc_html($hero_excerpt); ?></p>

                            <div class="sz-featured-meta">
                                <div class="sz-featured-date">
                                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                        <line x1="16" y1="2" x2="16" y2="6"></line>
                                        <line x1="8" y1="2" x2="8" y2="6"></line>
                                        <line x1="3" y1="10" x2="21" y2="10"></line>
                                    </svg>
                                    <span><?php echo esc_html($hero_date_display); ?></span>
                                </div>
                            </div>
                        </div>
                    </article>
                    <?php wp_reset_postdata(); ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- MAIN CONTENT SECTION (Filter, Search, Grid, Pagination) -->
        <section class="sz-blog-main-section" id="sz-scroll-target">
            <div class="sz-blog-container">

                <!-- CONTROLS BAR: Category Filter Tabs + Search -->
                <div class="sz-controls-bar">
                    <nav class="sz-filter-tabs" aria-label="Kategori Artikel">
                        <a href="<?php echo esc_url($all_link); ?>" class="sz-filter-tab active">Semua</a>
                        <a href="<?php echo esc_url($skin_link); ?>" class="sz-filter-tab">Skin</a>
                        <a href="<?php echo esc_url($hair_link); ?>" class="sz-filter-tab">Hair</a>
                        <a href="<?php echo esc_url($slimming_link); ?>" class="sz-filter-tab">Slimming</a>
                    </nav>

                    <form role="search" method="get" action="<?php echo esc_url(home_url('/blog/')); ?>" class="sz-search-box">
                        <svg class="sz-search-icon" viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                        <input type="search" name="s" value="<?php echo esc_attr($search_query); ?>" placeholder="Cari artikel..." aria-label="Cari artikel" class="sz-search-input">
                    </form>
                </div>

                <?php if (!empty($search_query)) : ?>
                    <div class="sz-search-results-heading">
                        <h3>Hasil pencarian untuk: <span>"<?php echo esc_html($search_query); ?>"</span></h3>
                        <a href="<?php echo esc_url($all_link); ?>" class="sz-clear-search">Hapus Pencarian &times;</a>
                    </div>
                <?php endif; ?>

                <!-- ARTICLES GRID -->
                <div class="sz-article-grid">
                    <?php
                    $query_args = [
                        'post_type'           => 'post',
                        'post_status'         => 'publish',
                        'posts_per_page'      => $posts_per_page,
                        'paged'               => $paged,
                        'orderby'             => 'post_modified',
                        'order'               => 'DESC',
                        'ignore_sticky_posts' => true,
                    ];

                    if (!empty($search_query)) {
                        $query_args['s'] = $search_query;
                    } elseif ($hero_id) {
                        $query_args['post__not_in'] = [$hero_id];
                    }

                    $recent_query = new WP_Query($query_args);

                    if ($recent_query->have_posts()) {
                        while ($recent_query->have_posts()) {
                            $recent_query->the_post();
                            sz_render_modern_card();
                        }
                        wp_reset_postdata();
                    } else {
                        ?>
                        <div class="sz-no-posts">
                            <p>Tidak ada artikel yang ditemukan<?php echo !empty($search_query) ? ' untuk kata kunci ini.' : '.'; ?></p>
                            <a href="<?php echo esc_url($all_link); ?>" class="sz-btn-secondary">Lihat Semua Artikel</a>
                        </div>
                        <?php
                    }
                    ?>
                </div>

                <!-- PAGINATION -->
                <div class="sz-pagination-wrapper">
                    <?php
                    $max_pages = $recent_query->max_num_pages;
                    if ($max_pages > 1) {
                        echo sz_generate_seo_pagination($max_pages, $paged, $search_query);
                    }
                    ?>
                </div>

            </div>
        </section>

    </div>

    <?php
    return ob_get_clean();
}

/**
 * ==========================================================
 * 2. FUNGSI RENDER KARTU ARTIKEL MODERN MINIMALIST
 * ==========================================================
 */
function sz_render_modern_card() {
    $post_id   = get_the_ID();
    $thumb_id  = get_post_thumbnail_id($post_id);
    $thumb     = $thumb_id ? wp_get_attachment_image_url($thumb_id, 'medium_large') : 'https://asset.sozoskinclinic.com/wp-content/uploads/2026/06/LP-Facial-Terdekat-V2-Hero-Image.webp';
    $cats      = get_the_category();
    $cat_name  = !empty($cats) ? esc_html($cats[0]->name) : 'Artikel';
    $cat_link  = !empty($cats) ? get_category_link($cats[0]->term_id) : '#';
    $title     = get_the_title();
    $excerpt   = wp_trim_words(get_the_excerpt(), 15, '...');

    // Status Tanggal: Diperbarui jika > 24 jam setelah publish
    $u_time = get_the_modified_time('U');
    $p_time = get_the_time('U');
    if (($u_time - $p_time) > 86400) {
        $date_display = 'Diperbarui: ' . get_the_modified_date('j M Y');
    } else {
        $date_display = get_the_date('j M Y');
    }
    ?>
    <article class="sz-card">
        <a href="<?php the_permalink(); ?>" class="sz-card-img" aria-label="<?php echo esc_attr($title); ?>" tabindex="-1">
            <img src="<?php echo esc_url($thumb); ?>" alt="<?php echo esc_attr($title); ?>" loading="lazy" decoding="async">
        </a>
        <div class="sz-card-body">
            <div class="sz-card-cat-wrap">
                <a href="<?php echo esc_url($cat_link); ?>" class="sz-card-cat"><?php echo $cat_name; ?></a>
            </div>
            <h3 class="sz-card-title">
                <a href="<?php the_permalink(); ?>"><?php echo esc_html($title); ?></a>
            </h3>
            <p class="sz-card-excerpt"><?php echo esc_html($excerpt); ?></p>
            <div class="sz-card-meta">
                <span class="sz-card-date"><?php echo esc_html($date_display); ?></span>
                <a href="<?php the_permalink(); ?>" class="sz-card-read-link">Baca &rarr;</a>
            </div>
        </div>
    </article>
    <?php
}

/**
 * ==========================================================
 * 3. FUNGSI RENDER PAGINATION SEO
 * ==========================================================
 */
function sz_generate_seo_pagination($max, $current, $search_query = '') {
    if ($max <= 1) return '';
    $html = '<div class="sz-pagination">';

    $get_link = function($page) use ($search_query) {
        $link = get_pagenum_link($page);
        if (!empty($search_query)) {
            $link = add_query_arg('s', urlencode($search_query), $link);
        }
        return esc_url($link);
    };

    // Tombol Prev (<)
    if ($current > 1) {
        $prev_link = $get_link($current - 1);
        $html .= "<a href='{$prev_link}' class='sz-page sz-arrow' aria-label='Halaman Sebelumnya'>&#10094;</a>";
    }

    // Angka Halaman dengan Elipsis (...)
    for ($i = 1; $i <= $max; $i++) {
        if ($i == 1 || $i == $max || ($i >= $current - 1 && $i <= $current + 1)) {
            $cls = ($i == $current) ? 'active' : '';
            $link = $get_link($i);
            $html .= "<a href='{$link}' class='sz-page {$cls}'>{$i}</a>";
        } elseif ($i == 2 && $current > 3) {
            $html .= "<span class='sz-dots'>...</span>";
        } elseif ($i == $max - 1 && $current < $max - 2) {
            $html .= "<span class='sz-dots'>...</span>";
        }
    }

    // Tombol Next (>)
    if ($current < $max) {
        $next_link = $get_link($current + 1);
        $html .= "<a href='{$next_link}' class='sz-page sz-arrow' aria-label='Halaman Selanjutnya'>&#10095;</a>";
    }

    $html .= '</div>';
    return $html;
}