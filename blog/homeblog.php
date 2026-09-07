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

    // 1. Dapatkan Hero Posts (Hanya di page 1 dan jika tidak sedang search)
    $hero_ids   = [];
    $hero_posts = [];

    if ($paged == 1 && empty($search_query)) {
        $hero_query = new WP_Query([
            'posts_per_page'      => 4,
            'post_status'         => 'publish',
            'orderby'             => 'date',
            'order'               => 'DESC',
            'ignore_sticky_posts' => true,
        ]);

        if ($hero_query->have_posts()) {
            $hero_posts = $hero_query->posts;
            $hero_ids   = wp_list_pluck($hero_posts, 'ID');
        }
    }
    ?>

    <div class="sz-blog-wrapper">

        <!-- HERO HEADER SECTION (Editorial Split Layout) -->
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
                                    <span class="sz-hero-meta-dot">&bull;</span>
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
                                    $side_id        = $side_post->ID;
                                    $side_thumb_id  = get_post_thumbnail_id($side_id);
                                    $side_thumb     = $side_thumb_id ? wp_get_attachment_image_url($side_thumb_id, 'medium') : 'https://asset.sozoskinclinic.com/wp-content/uploads/2026/06/LP-Facial-Terdekat-V2-Hero-Image.webp';
                                    $side_title     = get_the_title($side_id);
                                    $side_excerpt   = wp_trim_words(get_the_excerpt($side_id), 14, '...');
                                    $side_author_id = get_post_field('post_author', $side_id);
                                    $side_author    = get_the_author_meta('display_name', $side_author_id) ?: 'dr. Sozo Clinic';
                                    $side_date      = sz_get_date_display($side_id);
                                    $side_cats      = get_the_category($side_id);
                                    $side_link      = get_permalink($side_id);
                                ?>
                                    <article class="sz-hero-side-card">
                                        <a href="<?php echo esc_url($side_link); ?>" class="sz-hero-side-img-wrap" aria-label="<?php echo esc_attr($side_title); ?>" tabindex="-1">
                                            <img src="<?php echo esc_url($side_thumb); ?>" alt="<?php echo esc_attr($side_title); ?>" loading="lazy" decoding="async">
                                        </a>
                                        <div class="sz-hero-side-body">
                                            <div class="sz-hero-meta">
                                                <span class="sz-hero-author"><?php echo esc_html($side_author); ?></span>
                                                <span class="sz-hero-meta-dot">&bull;</span>
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