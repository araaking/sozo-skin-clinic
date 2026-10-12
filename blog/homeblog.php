/**
 * ==========================================================
 * SOZO SKIN - HOMEPAGE BLOG SHORTCODE [sozoskin_home]
 * Modern Minimalist Editorial Design (Compact & Theme-Safe)
 * CSS ikut Sozo Design System V1.2, ditulis di fungsi sz_home_print_styles()
 * ==========================================================
 */

// 1. SINKRONISASI MAIN QUERY WORDPRESS JIKA /blog/ ADALAH BLOG INDEX RESMI
add_action('pre_get_posts', 'sz_sync_blog_home_posts_per_page');
function sz_sync_blog_home_posts_per_page($query) {
    if (!is_admin() && $query->is_main_query() && $query->is_home()) {
        $query->set('posts_per_page', 6);
        $query->set('post_status', 'publish');
    }
}

// 2. SHORTCODE UTAMA HOMEPAGE BLOG
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

    // 1. Dapatkan 4 Hero Post IDs secara konsisten agar tidak terjadi artikel duplikat di Page 2+
    $hero_ids   = [];
    $hero_posts = [];

    if (empty($search_query)) {
        $hero_query = new WP_Query([
            'posts_per_page'      => 4,
            'post_status'         => 'publish',
            'orderby'             => 'date',
            'order'               => 'DESC',
            'ignore_sticky_posts' => true,
            'fields'              => ($paged == 1) ? 'all' : 'ids', // Hemat memori di page 2+
        ]);

        if ($hero_query->have_posts()) {
            if ($paged == 1) {
                $hero_posts = $hero_query->posts;
                $hero_ids   = wp_list_pluck($hero_posts, 'ID');
            } else {
                $hero_ids   = $hero_query->posts;
            }
        }
    }
    ?>

    <?php sz_home_print_styles(); ?>

    <div class="sz-blog-wrapper">

        <!-- HERO HEADER SECTION (Hanya tampil di Page 1 tanpa Search) -->
        <section class="sz-hero-banner-section">
            <div class="sz-hero-container">
                <div class="sz-hero-header-text">
                    <span class="sz-hero-badge">Edukasi & Artikel Medis</span>
                    <h1 class="sz-hero-main-title">Artikel & Edukasi Perawatan Kulit</h1>
                    <p class="sz-hero-subtitle">
                        Temukan panduan medis terpercaya seputar kesehatan kulit, rambut, dan estetika dari tim dokter spesialis Sozo Skin Clinic.
                    </p>
                </div>

                <?php if ($paged == 1 && empty($search_query) && !empty($hero_posts)) : ?>
                    <div class="sz-hero-split-grid">
                        <?php
                        // 1. KARTU UTAMA DI KIRI (Post #1)
                        $main_post      = $hero_posts[0];
                        $main_id        = $main_post->ID;
                        $main_thumb_id  = get_post_thumbnail_id($main_id);
                        $main_thumb     = $main_thumb_id ? wp_get_attachment_image_url($main_thumb_id, 'large') : 'https://asset.sozoskinclinic.com/wp-content/uploads/2026/06/LP-Facial-Terdekat-V2-Hero-Image.webp';
                        $main_title     = get_the_title($main_id);
                        $main_excerpt   = wp_trim_words(get_the_excerpt($main_id), 24, '...');
                        $main_author_id = get_post_field('post_author', $main_id);
                        $main_author    = get_the_author_meta('display_name', $main_author_id) ?: 'dr. Sozo Clinic';
                        $main_date      = sz_get_date_display($main_id);
                        $main_cats      = get_the_category($main_id);
                        $main_link      = get_permalink($main_id);
                        ?>
                        <article class="sz-hero-main-card">
                            <a href="<?php echo esc_url($main_link); ?>" class="sz-hero-main-img-wrap" aria-label="<?php echo esc_attr($main_title); ?>">
                                <img src="<?php echo esc_url($main_thumb); ?>" alt="<?php echo esc_attr($main_title); ?>" decoding="async" fetchpriority="high">
                            </a>
                            <div class="sz-hero-main-body">
                                <div class="sz-hero-meta">
                                    <span class="sz-hero-author"><?php echo esc_html($main_author); ?></span>
                                    <span class="sz-hero-meta-dot" aria-hidden="true">&bull;</span>
                                    <span class="sz-hero-date"><?php echo esc_html($main_date); ?></span>
                                </div>

                                <h2 class="sz-hero-main-card-title">
                                    <a href="<?php echo esc_url($main_link); ?>" class="sz-hero-main-link">
                                        <span class="sz-hero-title-text"><?php echo esc_html($main_title); ?></span>
                                        <svg class="sz-hero-arrow-icon" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <line x1="7" y1="17" x2="17" y2="7"></line>
                                            <polyline points="7 7 17 7 17 17"></polyline>
                                        </svg>
                                    </a>
                                </h2>

                                <p class="sz-hero-main-excerpt"><?php echo esc_html($main_excerpt); ?></p>

                                <?php if (!empty($main_cats)) : ?>
                                    <div class="sz-pill-list">
                                        <?php foreach (array_slice($main_cats, 0, 3) as $cat) : ?>
                                            <a href="<?php echo esc_url(get_category_link($cat->term_id)); ?>" class="sz-pill-tag"><?php echo esc_html($cat->name); ?></a>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </article>

                        <?php
                        // 2. TIGA KARTU SAMPING DI KANAN (Post #2, #3, #4)
                        $side_posts = array_slice($hero_posts, 1, 3);
                        if (!empty($side_posts)) :
                        ?>
                            <div class="sz-hero-side-stack">
                                <?php foreach ($side_posts as $side_post) : 
                                    $side_id       = $side_post->ID;
                                    $side_thumb_id = get_post_thumbnail_id($side_id);
                                    $side_thumb    = $side_thumb_id ? wp_get_attachment_image_url($side_thumb_id, 'medium') : 'https://asset.sozoskinclinic.com/wp-content/uploads/2026/06/LP-Facial-Terdekat-V2-Hero-Image.webp';
                                    $side_title    = get_the_title($side_id);
                                    $side_excerpt  = wp_trim_words(get_the_excerpt($side_id), 14, '...');
                                    $side_author_id= get_post_field('post_author', $side_id);
                                    $side_author   = get_the_author_meta('display_name', $side_author_id) ?: 'dr. Sozo Clinic';
                                    $side_date     = sz_get_date_display($side_id);
                                    $side_cats     = get_the_category($side_id);
                                    $side_link     = get_permalink($side_id);
                                ?>
                                    <article class="sz-hero-side-card">
                                        <a href="<?php echo esc_url($side_link); ?>" class="sz-hero-side-img-wrap" aria-label="<?php echo esc_attr($side_title); ?>" tabindex="-1">
                                            <img src="<?php echo esc_url($side_thumb); ?>" alt="<?php echo esc_attr($side_title); ?>" loading="lazy" decoding="async">
                                        </a>
                                        <div class="sz-hero-side-body">
                                            <div class="sz-hero-meta">
                                                <span class="sz-hero-author"><?php echo esc_html($side_author); ?></span>
                                                <span class="sz-hero-meta-dot" aria-hidden="true">&bull;</span>
                                                <span class="sz-hero-date"><?php echo esc_html($side_date); ?></span>
                                            </div>
                                            <h3 class="sz-hero-side-card-title">
                                                <a href="<?php echo esc_url($side_link); ?>"><?php echo esc_html($side_title); ?></a>
                                            </h3>
                                            <p class="sz-hero-side-excerpt"><?php echo esc_html($side_excerpt); ?></p>
                                            <?php if (!empty($side_cats)) : ?>
                                                <div class="sz-pill-list">
                                                    <?php foreach (array_slice($side_cats, 0, 2) as $cat) : ?>
                                                        <a href="<?php echo esc_url(get_category_link($cat->term_id)); ?>" class="sz-pill-tag"><?php echo esc_html($cat->name); ?></a>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
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
                        <a href="<?php echo esc_url($all_link); ?>" class="sz-filter-tab active" aria-current="page">Semua</a>
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
                        'orderby'             => 'date',
                        'order'               => 'DESC',
                        'ignore_sticky_posts' => true,
                    ];

                    if (!empty($search_query)) {
                        $query_args['s'] = $search_query;
                    } elseif (!empty($hero_ids)) {
                        $query_args['post__not_in'] = $hero_ids;
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
                    $max_pages = (int) $recent_query->max_num_pages;
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
 * HELPER: Format Tanggal (Update Status jika > 24 jam)
 * ==========================================================
 */
function sz_get_date_display($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    $u_time = get_the_modified_time('U', $post_id);
    $p_time = get_the_time('U', $post_id);
    if (($u_time - $p_time) > 86400) {
        return 'Diperbarui: ' . get_the_modified_date('j M Y', $post_id);
    }
    return get_the_date('j M Y', $post_id);
}

/**
 * ==========================================================
 * 2. FUNGSI RENDER KARTU ARTIKEL MODERN MINIMALIST
 * ==========================================================
 */
function sz_render_modern_card() {
    $post_id      = get_the_ID();
    $thumb_id     = get_post_thumbnail_id($post_id);
    $thumb        = $thumb_id ? wp_get_attachment_image_url($thumb_id, 'medium_large') : 'https://asset.sozoskinclinic.com/wp-content/uploads/2026/06/LP-Facial-Terdekat-V2-Hero-Image.webp';
    $cats         = get_the_category();
    $cat_name     = !empty($cats) ? esc_html($cats[0]->name) : 'Artikel';
    $cat_link     = !empty($cats) ? get_category_link($cats[0]->term_id) : '#';
    $title        = get_the_title();
    $excerpt      = wp_trim_words(get_the_excerpt(), 15, '...');
    $date_display = sz_get_date_display($post_id);
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
            $cls     = ($i == $current) ? 'active' : '';
            $current_attr = ($i == $current) ? " aria-current='page'" : '';
            $link    = $get_link($i);
            $html .= "<a href='{$link}' class='sz-page {$cls}'{$current_attr}>{$i}</a>";
        } elseif ($i == 2 && $current > 3) {
            $html .= "<span class='sz-dots' aria-hidden='true'>...</span>";
        } elseif ($i == $max - 1 && $current < $max - 2) {
            $html .= "<span class='sz-dots' aria-hidden='true'>...</span>";
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

/**
 * ==========================================================
 * 4. CSS HOMEPAGE BLOG (dicetak sekali per halaman)
 * ==========================================================
 */
function sz_home_print_styles() {
    static $printed = false;
    if ($printed) return;
    $printed = true;
    ?>
    <style id="sz-blog-home-css">
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap');

/* ==========================================================
   SOZO SKIN - HOMEPAGE BLOG (Sozo Design System V1.2)
   Token di-scope ke wrapper, bukan :root, supaya tidak bocor
   ke tema. !important dipakai karena Elementor/tema menimpa.
   ========================================================== */

.sz-blog-wrapper {
    --color-brand-primary: #1A2080;
    --color-brand-secondary: #D9C7B1;
    --color-brand-almond: #E9DFD5;
    --color-brand-isabelline: #DFDBD4;
    --color-white: #FFFFFF;
    --color-text-primary: #171715;
    --color-text-secondary: #41413E;
    --color-text-muted: #777771;
    --color-text-on-primary: #FFFFFF;
    --color-border: #DFDBD4;
    --color-border-strong: #C8C3BA;
    --color-primary-hover: #151A6D;
    --color-primary-subtle: rgba(26, 32, 128, 0.05);
    --color-error: #B64242;
    --font-family-primary: "Poppins", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    --text-h1: clamp(38px, 33.856px + 1.105vw, 48px);
    --text-h4: 24px;
    --text-h5: 20px;
    --text-body: 16px;
    --text-body-sm: 14px;
    --text-label: 12px;
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
    --container-max: 1280px;
    --container-pad: 32px;
    --radius-md: 10px;
    --radius-lg: 16px;
    --radius-full: 999px;
    --shadow-md: 0 8px 24px rgba(23, 23, 21, 0.07);
    --focus-ring: 2px solid var(--color-brand-primary);
    --focus-offset: 3px;
    --duration-fast: 180ms;
    --duration-card: 220ms;
    --duration-media: 400ms;
    --ease-standard: ease;

    width: 100% !important;
    max-width: 100% !important;
    margin: 0 auto !important;
    padding: 0 !important;
    background: var(--color-white) !important;
    color: var(--color-text-secondary) !important;
    font-family: var(--font-family-primary) !important;
    font-size: var(--text-body) !important;
    line-height: 1.6 !important;
    box-sizing: border-box !important;
    overflow-x: clip !important;
}

.sz-blog-wrapper *,
.sz-blog-wrapper *::before,
.sz-blog-wrapper *::after {
    box-sizing: inherit !important;
    overflow-wrap: break-word !important;
    font-family: var(--font-family-primary) !important;
}

.sz-blog-wrapper a {
    text-decoration: none !important;
}

.sz-blog-wrapper a:focus-visible,
.sz-blog-wrapper input:focus-visible {
    outline: var(--focus-ring) !important;
    outline-offset: var(--focus-offset) !important;
}

.sz-blog-wrapper img {
    max-width: 100% !important;
    height: auto !important;
    display: block !important;
    border: none !important;
    box-shadow: none !important;
}

/* ==========================================================
   1. Hero Header
   ========================================================== */

.sz-hero-banner-section {
    background: var(--color-white) !important;
    padding: var(--space-8) var(--container-pad) var(--space-7) !important;
    width: 100% !important;
    max-width: 100% !important;
    border-bottom: 1px solid var(--color-border) !important;
}

.sz-hero-container {
    max-width: var(--container-max) !important;
    width: 100% !important;
    margin: 0 auto !important;
}

.sz-hero-header-text {
    max-width: 720px !important;
    margin-bottom: var(--space-7) !important;
    text-align: left !important;
}

/* Badge Label (DEC-26) */
.sz-hero-badge {
    display: inline-flex !important;
    align-items: center !important;
    min-height: 30px !important;
    padding: 0 18px !important;
    margin: 0 0 var(--space-5) !important;
    border-radius: var(--radius-full) !important;
    background: var(--color-brand-almond) !important;
    color: var(--color-brand-primary) !important;
    font-size: var(--text-label) !important;
    font-weight: 600 !important;
    line-height: 1.4 !important;
    letter-spacing: var(--tracking-label) !important;
    text-transform: uppercase !important;
}

.sz-hero-main-title {
    font-size: var(--text-h1) !important;
    font-weight: 700 !important;
    color: var(--color-brand-primary) !important;
    line-height: 1.15 !important;
    margin: 0 0 var(--space-4) !important;
    padding: 0 !important;
    letter-spacing: 0 !important;
}

.sz-hero-subtitle {
    max-width: 65ch !important;
    font-size: var(--text-body) !important;
    color: var(--color-text-secondary) !important;
    line-height: 1.6 !important;
    margin: 0 !important;
    padding: 0 !important;
}

/* ==========================================================
   2. Hero Split (1 artikel utama + 3 artikel samping)
   ========================================================== */

.sz-hero-split-grid {
    display: grid !important;
    grid-template-columns: 1.15fr 1fr !important;
    gap: 36px !important;
    align-items: stretch !important;
    width: 100% !important;
}

.sz-hero-main-card {
    display: flex !important;
    flex-direction: column !important;
    height: 100% !important;
}

.sz-hero-main-img-wrap,
.sz-hero-side-img-wrap {
    width: 100% !important;
    overflow: hidden !important;
    display: block !important;
    background: var(--color-brand-isabelline) !important;
}

.sz-hero-main-img-wrap {
    aspect-ratio: 16 / 10 !important;
    border-radius: var(--radius-lg) !important;
}

.sz-hero-side-img-wrap {
    aspect-ratio: 16 / 11 !important;
    border-radius: var(--radius-md) !important;
}

.sz-hero-main-img-wrap img,
.sz-hero-side-img-wrap img {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
    margin: 0 !important;
    transition: transform var(--duration-media) var(--ease-standard) !important;
}

.sz-hero-main-card:hover .sz-hero-main-img-wrap img,
.sz-hero-side-card:hover .sz-hero-side-img-wrap img {
    transform: scale(1.03) !important;
}

.sz-hero-main-body {
    display: flex !important;
    flex-direction: column !important;
    flex-grow: 1 !important;
    padding-top: 20px !important;
}

.sz-hero-meta {
    display: flex !important;
    flex-wrap: wrap !important;
    align-items: center !important;
    gap: var(--space-2) !important;
    font-size: 13px !important;
    font-weight: 500 !important;
    line-height: 1.4 !important;
    color: var(--color-text-muted) !important;
    margin-bottom: var(--space-2) !important;
}

.sz-hero-author {
    color: var(--color-text-secondary) !important;
    font-weight: 600 !important;
}

.sz-hero-meta-dot {
    color: var(--color-border-strong) !important;
}

.sz-hero-main-card-title {
    margin: 0 0 var(--space-3) !important;
    padding: 0 !important;
}

.sz-hero-main-link {
    display: flex !important;
    align-items: flex-start !important;
    justify-content: space-between !important;
    gap: var(--space-3) !important;
    color: var(--color-brand-primary) !important;
    font-size: var(--text-h4) !important;
    font-weight: 700 !important;
    line-height: 1.3 !important;
}

.sz-hero-main-link:hover .sz-hero-title-text {
    text-decoration: underline !important;
    text-underline-offset: 4px !important;
}

.sz-hero-arrow-icon {
    flex-shrink: 0 !important;
    margin-top: 6px !important;
    color: var(--color-brand-primary) !important;
    transition: transform var(--duration-fast) var(--ease-standard) !important;
}

.sz-hero-main-card:hover .sz-hero-arrow-icon {
    transform: translate(3px, -3px) !important;
}

.sz-hero-main-excerpt {
    color: var(--color-text-secondary) !important;
    font-size: var(--text-body-sm) !important;
    line-height: 1.55 !important;
    margin: 0 0 var(--space-4) !important;
    padding: 0 !important;
    display: -webkit-box !important;
    -webkit-line-clamp: 3 !important;
    -webkit-box-orient: vertical !important;
    overflow: hidden !important;
}

.sz-hero-side-stack {
    display: flex !important;
    flex-direction: column !important;
    justify-content: space-between !important;
    gap: var(--space-5) !important;
    height: 100% !important;
}

.sz-hero-side-card {
    display: grid !important;
    grid-template-columns: 200px 1fr !important;
    gap: 18px !important;
    align-items: flex-start !important;
}

.sz-hero-side-body {
    display: flex !important;
    flex-direction: column !important;
    justify-content: center !important;
}

.sz-hero-side-card-title {
    margin: 0 0 6px !important;
    padding: 0 !important;
}

.sz-hero-side-card-title a {
    color: var(--color-brand-primary) !important;
    font-size: 16px !important;
    font-weight: 600 !important;
    line-height: 1.35 !important;
    display: -webkit-box !important;
    -webkit-line-clamp: 2 !important;
    -webkit-box-orient: vertical !important;
    overflow: hidden !important;
    text-underline-offset: 4px !important;
}

.sz-hero-side-card-title a:hover {
    text-decoration: underline !important;
}

.sz-hero-side-excerpt {
    color: var(--color-text-secondary) !important;
    font-size: 13px !important;
    line-height: 1.5 !important;
    margin: 0 0 10px !important;
    padding: 0 !important;
    display: -webkit-box !important;
    -webkit-line-clamp: 2 !important;
    -webkit-box-orient: vertical !important;
    overflow: hidden !important;
}

/* Tag kategori di hero: link, jadi gaya chip (bukan badge) */
.sz-pill-list {
    display: flex !important;
    flex-wrap: wrap !important;
    gap: var(--space-2) !important;
    align-items: center !important;
}

.sz-pill-tag {
    display: inline-flex !important;
    align-items: center !important;
    min-height: 30px !important;
    padding: 0 var(--space-3) !important;
    border-radius: var(--radius-full) !important;
    border: 1px solid var(--color-border) !important;
    background: var(--color-white) !important;
    color: var(--color-text-secondary) !important;
    font-size: var(--text-label) !important;
    font-weight: 500 !important;
    line-height: 1.2 !important;
    transition:
        border-color var(--duration-fast) var(--ease-standard),
        color var(--duration-fast) var(--ease-standard) !important;
}

.sz-pill-tag:hover {
    border-color: var(--color-brand-primary) !important;
    color: var(--color-brand-primary) !important;
}

/* ==========================================================
   3. Main Content (Filter, Search, Grid)
   ========================================================== */

.sz-blog-main-section {
    padding: var(--space-7) var(--container-pad) 64px !important;
    max-width: calc(var(--container-max) + (var(--container-pad) * 2)) !important;
    width: 100% !important;
    margin: 0 auto !important;
}

.sz-blog-container {
    width: 100% !important;
}

.sz-controls-bar {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    gap: var(--space-4) !important;
    border-bottom: 1px solid var(--color-border) !important;
    padding-bottom: 20px !important;
    margin-bottom: var(--space-6) !important;
    flex-wrap: wrap !important;
    width: 100% !important;
}

/* Chip kategori (pola sama dengan .tc-chip di /treatment/) */
.sz-filter-tabs {
    display: flex !important;
    align-items: center !important;
    gap: var(--space-2) !important;
    overflow-x: auto !important;
    scrollbar-width: none !important;
    -webkit-overflow-scrolling: touch !important;
    padding: 2px !important;
}

.sz-filter-tabs::-webkit-scrollbar {
    display: none !important;
}

.sz-filter-tab {
    display: inline-flex !important;
    align-items: center !important;
    flex: 0 0 auto !important;
    min-height: 40px !important;
    padding: 0 var(--space-4) !important;
    border: 1px solid var(--color-border) !important;
    border-radius: var(--radius-full) !important;
    background: var(--color-white) !important;
    color: var(--color-text-secondary) !important;
    font-size: var(--text-body-sm) !important;
    font-weight: 500 !important;
    line-height: 1.2 !important;
    white-space: nowrap !important;
    box-shadow: none !important;
    transition:
        background-color var(--duration-fast) var(--ease-standard),
        border-color var(--duration-fast) var(--ease-standard),
        color var(--duration-fast) var(--ease-standard) !important;
}

.sz-filter-tab:hover {
    border-color: var(--color-brand-primary) !important;
    color: var(--color-brand-primary) !important;
}

.sz-filter-tab.active {
    background: var(--color-brand-primary) !important;
    border-color: var(--color-brand-primary) !important;
    color: var(--color-text-on-primary) !important;
}

/* Search */
.sz-search-box {
    position: relative !important;
    width: 300px !important;
    max-width: 100% !important;
    margin: 0 !important;
}

.sz-search-icon {
    position: absolute !important;
    left: 16px !important;
    top: 50% !important;
    transform: translateY(-50%) !important;
    color: var(--color-text-muted) !important;
    pointer-events: none !important;
}

.sz-search-box:focus-within .sz-search-icon {
    color: var(--color-brand-primary) !important;
}

.sz-search-input {
    display: block !important;
    width: 100% !important;
    min-height: 44px !important;
    margin: 0 !important;
    padding: 0 16px 0 44px !important;
    border-radius: var(--radius-full) !important;
    border: 1px solid var(--color-border-strong) !important;
    background: var(--color-white) !important;
    font-size: var(--text-body-sm) !important;
    line-height: 1.4 !important;
    color: var(--color-text-primary) !important;
    box-shadow: none !important;
    -webkit-appearance: none !important;
    appearance: none !important;
    transition: border-color var(--duration-fast) var(--ease-standard) !important;
}

.sz-search-input::placeholder {
    color: var(--color-text-muted) !important;
    opacity: 1 !important;
}

.sz-search-input:hover {
    border-color: var(--color-text-muted) !important;
}

.sz-search-input:focus {
    border-color: var(--color-brand-primary) !important;
    outline: none !important;
}

/* Judul hasil pencarian */
.sz-search-results-heading {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    flex-wrap: wrap !important;
    gap: var(--space-3) !important;
    margin-bottom: var(--space-5) !important;
}

.sz-search-results-heading h3 {
    font-size: var(--text-h5) !important;
    line-height: 1.35 !important;
    color: var(--color-text-primary) !important;
    margin: 0 !important;
    font-weight: 600 !important;
}

.sz-search-results-heading h3 span {
    color: var(--color-brand-primary) !important;
}

.sz-clear-search {
    font-size: var(--text-body-sm) !important;
    color: var(--color-error) !important;
    font-weight: 600 !important;
    text-underline-offset: 4px !important;
}

.sz-clear-search:hover {
    text-decoration: underline !important;
}

/* ==========================================================
   4. Grid Artikel (Base Card)
   ========================================================== */

.sz-article-grid {
    display: grid !important;
    grid-template-columns: repeat(3, 1fr) !important;
    gap: var(--space-5) !important;
    width: 100% !important;
}

.sz-card {
    background: var(--color-white) !important;
    border: 1px solid var(--color-border) !important;
    border-radius: var(--radius-lg) !important;
    overflow: hidden !important;
    display: flex !important;
    flex-direction: column !important;
    box-shadow: none !important;
    transition:
        transform var(--duration-card) var(--ease-standard),
        box-shadow var(--duration-card) var(--ease-standard),
        border-color var(--duration-card) var(--ease-standard) !important;
}

.sz-card:hover {
    transform: translateY(-2px) !important;
    box-shadow: var(--shadow-md) !important;
    border-color: var(--color-border-strong) !important;
}

.sz-card-img {
    aspect-ratio: 16 / 10 !important;
    width: 100% !important;
    overflow: hidden !important;
    display: block !important;
    background: var(--color-brand-isabelline) !important;
    position: relative !important;
}

.sz-card-img img {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
    display: block !important;
    margin: 0 !important;
    transition: transform var(--duration-media) var(--ease-standard) !important;
}

.sz-card:hover .sz-card-img img {
    transform: scale(1.03) !important;
}

.sz-card-body {
    padding: var(--space-5) !important;
    display: flex !important;
    flex-direction: column !important;
    flex-grow: 1 !important;
}

.sz-card-cat-wrap {
    margin-bottom: var(--space-2) !important;
}

.sz-card-cat {
    display: inline-block !important;
    font-size: var(--text-label) !important;
    font-weight: 600 !important;
    line-height: 1.4 !important;
    color: var(--color-text-muted) !important;
    text-transform: uppercase !important;
    letter-spacing: var(--tracking-eyebrow) !important;
    transition: color var(--duration-fast) var(--ease-standard) !important;
}

.sz-card-cat:hover {
    color: var(--color-brand-primary) !important;
}

.sz-card-title {
    margin: 0 0 var(--space-2) !important;
    font-size: var(--text-h5) !important;
    font-weight: 600 !important;
    line-height: 1.35 !important;
    padding: 0 !important;
}

.sz-card-title a {
    color: var(--color-brand-primary) !important;
    display: -webkit-box !important;
    -webkit-line-clamp: 2 !important;
    -webkit-box-orient: vertical !important;
    overflow: hidden !important;
    text-underline-offset: 4px !important;
}

.sz-card-title a:hover {
    text-decoration: underline !important;
}

.sz-card-excerpt {
    font-size: var(--text-body-sm) !important;
    color: var(--color-text-secondary) !important;
    line-height: 1.55 !important;
    margin: 0 0 20px !important;
    padding: 0 !important;
    display: -webkit-box !important;
    -webkit-line-clamp: 2 !important;
    -webkit-box-orient: vertical !important;
    overflow: hidden !important;
}

.sz-card-meta {
    margin-top: auto !important;
    padding-top: var(--space-3) !important;
    border-top: 1px solid var(--color-border) !important;
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    gap: var(--space-3) !important;
}

.sz-card-date {
    font-weight: 500 !important;
    color: var(--color-text-muted) !important;
    font-size: var(--text-label) !important;
    line-height: 1.4 !important;
}

/* Link Standalone */
.sz-card-read-link {
    display: inline-flex !important;
    align-items: center !important;
    color: var(--color-brand-primary) !important;
    font-size: var(--text-body-sm) !important;
    font-weight: 600 !important;
    white-space: nowrap !important;
    text-underline-offset: 4px !important;
}

.sz-card-read-link:hover {
    text-decoration: underline !important;
}

/* Empty state */
.sz-no-posts {
    grid-column: 1 / -1 !important;
    text-align: center !important;
    padding: var(--space-8) 20px !important;
    border-radius: var(--radius-lg) !important;
    border: 1px solid var(--color-border) !important;
    background: var(--color-white) !important;
}

.sz-no-posts p {
    font-size: var(--text-body) !important;
    color: var(--color-text-secondary) !important;
    margin: 0 0 var(--space-5) !important;
}

/* Button Outline md */
.sz-btn-secondary {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    min-height: 48px !important;
    padding: 0 var(--space-5) !important;
    border: 1px solid var(--color-brand-primary) !important;
    border-radius: var(--radius-full) !important;
    background: transparent !important;
    color: var(--color-brand-primary) !important;
    font-size: var(--text-body-sm) !important;
    font-weight: 600 !important;
    line-height: 1 !important;
    white-space: nowrap !important;
    transition:
        background-color var(--duration-fast) var(--ease-standard),
        transform var(--duration-fast) var(--ease-standard) !important;
}

.sz-btn-secondary:hover {
    background: var(--color-primary-subtle) !important;
    transform: translateY(-1px) !important;
}

.sz-btn-secondary:active {
    transform: translateY(0) !important;
}

/* ==========================================================
   5. Pagination
   ========================================================== */

.sz-pagination-wrapper {
    margin-top: var(--space-8) !important;
    display: flex !important;
    justify-content: center !important;
}

.sz-pagination {
    display: flex !important;
    flex-wrap: wrap !important;
    justify-content: center !important;
    align-items: center !important;
    gap: var(--space-2) !important;
}

.sz-page {
    min-width: 40px !important;
    height: 40px !important;
    padding: 0 10px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    border: 1px solid var(--color-border) !important;
    border-radius: var(--radius-md) !important;
    background: var(--color-white) !important;
    color: var(--color-text-secondary) !important;
    font-weight: 600 !important;
    font-size: var(--text-body-sm) !important;
    line-height: 1 !important;
    transition:
        border-color var(--duration-fast) var(--ease-standard),
        color var(--duration-fast) var(--ease-standard) !important;
}

.sz-page:hover {
    border-color: var(--color-brand-primary) !important;
    color: var(--color-brand-primary) !important;
}

.sz-page.active {
    background: var(--color-brand-primary) !important;
    border-color: var(--color-brand-primary) !important;
    color: var(--color-text-on-primary) !important;
    pointer-events: none !important;
}

.sz-dots {
    padding: 0 6px !important;
    color: var(--color-text-muted) !important;
    font-weight: 600 !important;
}

.sz-arrow {
    color: var(--color-brand-primary) !important;
}

/* ==========================================================
   6. Responsive (1024 / 768 / 480)
   ========================================================== */

@media (max-width: 1024px) {
    .sz-hero-split-grid {
        grid-template-columns: 1fr !important;
        gap: var(--space-6) !important;
    }

    .sz-hero-side-card {
        grid-template-columns: 180px 1fr !important;
    }

    .sz-article-grid {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 20px !important;
    }
}

@media (max-width: 768px) {
    .sz-blog-wrapper {
        --container-pad: 20px;
    }

    .sz-hero-banner-section {
        padding: var(--space-6) var(--container-pad) var(--space-6) !important;
    }

    .sz-hero-header-text {
        margin-bottom: var(--space-5) !important;
    }

    .sz-hero-badge {
        margin-bottom: var(--space-4) !important;
    }

    .sz-hero-main-title {
        margin-bottom: var(--space-3) !important;
    }

    .sz-hero-subtitle {
        font-size: var(--text-body-sm) !important;
        line-height: 1.55 !important;
    }

    .sz-hero-split-grid {
        gap: var(--space-5) !important;
    }

    .sz-hero-main-link {
        font-size: var(--text-h5) !important;
        line-height: 1.35 !important;
    }

    .sz-hero-main-excerpt {
        -webkit-line-clamp: 2 !important;
        margin-bottom: var(--space-3) !important;
    }

    .sz-hero-side-stack {
        gap: var(--space-4) !important;
    }

    .sz-hero-side-card {
        grid-template-columns: 110px 1fr !important;
        gap: var(--space-3) !important;
    }

    .sz-hero-side-card-title a {
        font-size: var(--text-body-sm) !important;
        line-height: 1.4 !important;
    }

    .sz-hero-side-excerpt {
        display: none !important;
    }

    .sz-hero-meta {
        font-size: var(--text-label) !important;
        gap: 6px !important;
        margin-bottom: 6px !important;
    }

    .sz-blog-main-section {
        padding: var(--space-6) var(--container-pad) var(--space-8) !important;
    }

    .sz-controls-bar {
        flex-direction: column !important;
        align-items: stretch !important;
        gap: var(--space-3) !important;
        padding-bottom: var(--space-4) !important;
        margin-bottom: var(--space-5) !important;
    }

    .sz-filter-tabs {
        width: 100% !important;
        max-width: 100% !important;
    }

    .sz-filter-tab {
        min-height: 36px !important;
        padding: 0 14px !important;
        font-size: 13px !important;
    }

    .sz-search-box {
        width: 100% !important;
    }

    /* 16px mencegah auto-zoom iOS saat input di-focus */
    .sz-search-input {
        font-size: var(--text-body) !important;
    }

    .sz-article-grid {
        grid-template-columns: 1fr !important;
        gap: var(--space-4) !important;
    }

    .sz-card-body {
        padding: 20px !important;
    }

    .sz-card-title {
        font-size: 18px !important;
    }

    .sz-pagination-wrapper {
        margin-top: var(--space-6) !important;
    }
}

@media (max-width: 480px) {
    .sz-hero-side-card {
        grid-template-columns: 96px 1fr !important;
    }

    .sz-page {
        min-width: 36px !important;
        height: 36px !important;
        padding: 0 8px !important;
    }
}

@media (prefers-reduced-motion: reduce) {
    .sz-blog-wrapper *,
    .sz-blog-wrapper *::before,
    .sz-blog-wrapper *::after {
        transition-duration: 0ms !important;
    }

    .sz-card:hover,
    .sz-card:hover .sz-card-img img,
    .sz-hero-main-card:hover .sz-hero-main-img-wrap img,
    .sz-hero-side-card:hover .sz-hero-side-img-wrap img,
    .sz-hero-main-card:hover .sz-hero-arrow-icon,
    .sz-btn-secondary:hover {
        transform: none !important;
    }
}
    </style>
    <?php
}
