<?php
/**
 * ==========================================================
 * SOZO SKIN - AUTHOR PROFILE & ARCHIVE TEMPLATE (ALL-IN-ONE)
 * File: blog/author.php
 *
 * Cara Pakai di WPCode:
 * 1. Buat snippet baru di WPCode.
 * 2. Code Type: PHP Snippet.
 * 3. Location: Frontend Only (atau Run Everywhere).
 * 4. Paste seluruh isi file ini, lalu aktifkan snippet.
 *
 * Otomatis aktif di semua URL author: /blog/author/[slug]/
 * Fallback shortcode: [sozoskin_author]
 * ==========================================================
 */

// 1. AUTO OVERRIDE TEMPLATE AUTHOR WORDPRESS
add_filter('template_include', 'sz_author_template_override', 99);

function sz_author_template_override($template) {
    if (is_author()) {
        get_header();
        sz_render_author_page_content();
        get_footer();
        exit;
    }
    return $template;
}

// 2. SHORTCODE FALLBACK JIKA INGIN DIPASANG DI ELEMENTOR: [sozoskin_author]
add_shortcode('sozoskin_author', 'sz_author_shortcode_callback');

function sz_author_shortcode_callback($atts) {
    ob_start();
    sz_render_author_page_content();
    return ob_get_clean();
}

/**
 * 3. FUNGSI UTAMA RENDER HALAMAN AUTHOR (HTML + CSS + SCHEMA)
 */
function sz_render_author_page_content() {
    $author = get_queried_object();
    if (!$author || !isset($author->ID)) {
        $author_id = get_query_var('author');
        $author    = get_userdata($author_id);
    }
    if (!$author) return;

    $author_id    = $author->ID;
    $display_name = $author->display_name;
    $bio          = get_the_author_meta('description', $author_id);
    $avatar_url   = get_avatar_url($author_id, ['size' => 240]);
    $website      = $author->user_url;
    $author_url   = get_author_posts_url($author_id);
    $post_count   = count_user_posts($author_id, 'post', true);

    // Fallback bio editorial medis jika profil WP masih kosong
    if (empty($bio)) {
        $bio = 'Penulis dan tim editorial medis di Sozo Skin Clinic. Berdedikasi menyajikan informasi berbasis riset klinis dan panduan kesehatan kulit, rambut, serta estetika yang terpercaya dan ditinjau oleh tim dokter spesialis.';
    }

    // Role badge E-E-A-T
    $role_badge = get_the_author_meta('job_title', $author_id) ?: 'Medical Content Specialist';

    // Pagination setup
    $paged = (get_query_var('paged')) ? get_query_var('paged') : ((get_query_var('page')) ? get_query_var('page') : 1);
    $posts_per_page = 9;

    // Query artikel murni berdasarkan tanggal publish terbaru (DESC)
    $author_query = new WP_Query([
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'author'              => $author_id,
        'posts_per_page'      => $posts_per_page,
        'paged'               => $paged,
        'orderby'             => 'date',
        'order'               => 'DESC',
        'ignore_sticky_posts' => true,
    ]);
    ?>

    <!-- SCHEMA MARKUP E-E-A-T (ProfilePage + Person) -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@graph": [
        {
          "@type": "ProfilePage",
          "@id": "<?php echo esc_url($author_url); ?>#webpage",
          "url": "<?php echo esc_url($author_url); ?>",
          "name": "<?php echo esc_attr($display_name); ?> - Profil Penulis Sozo Skin Clinic",
          "isPartOf": { "@id": "https://sozoskinclinic.com/#website" },
          "about": { "@id": "<?php echo esc_url($author_url); ?>#person" }
        },
        {
          "@type": "Person",
          "@id": "<?php echo esc_url($author_url); ?>#person",
          "name": "<?php echo esc_attr($display_name); ?>",
          "jobTitle": "<?php echo esc_attr($role_badge); ?>",
          "description": "<?php echo esc_attr(wp_strip_all_tags($bio)); ?>",
          "image": "<?php echo esc_url($avatar_url); ?>",
          "worksFor": {
            "@type": "MedicalOrganization",
            "@id": "https://sozoskinclinic.com/#organization"
          }
          <?php if (!empty($website)) : ?>, "sameAs": ["<?php echo esc_url($website); ?>"]<?php endif; ?>
        }
      ]
    }
    </script>

    <!-- STYLESHEET (SCOPED & THEME-SAFE) -->
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap');

    .sz-author-wrapper {
        --sz-primary: #1A2080;
        --sz-primary-hover: #12165c;
        --sz-text-dark: #1A2080;
        --sz-text-desc: #24345c;
        --sz-text-meta: #4d6599;
        --sz-border: #e2e8f0;
        --sz-bg-light: #f8fafc;
        
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 auto !important;
        padding: 0 !important;
        color: var(--sz-text-desc) !important;
        font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
        line-height: 1.5 !important;
        box-sizing: border-box !important;
        background: #ffffff !important;
    }

    .sz-author-wrapper *,
    .sz-author-wrapper *::before,
    .sz-author-wrapper *::after {
        box-sizing: inherit !important;
        overflow-wrap: break-word !important;
        word-wrap: break-word !important;
        word-break: break-word !important;
        font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
    }

    .sz-author-wrapper a {
        text-decoration: none !important;
        transition: all 0.25s ease !important;
    }

    .sz-author-wrapper img {
        max-width: 100% !important;
        height: auto !important;
        display: block !important;
        border: none !important;
    }

    .sz-author-container {
        max-width: 1200px !important;
        width: 100% !important;
        margin: 0 auto !important;
        padding: 0 20px !important;
        box-sizing: border-box !important;
    }

    /* 1. AUTHOR PROFILE HERO SECTION */
    .sz-author-hero {
        background: linear-gradient(180deg, #F3F7FD 0%, #FFFFFF 100%) !important;
        border-bottom: 1px solid rgba(226, 232, 240, 0.8) !important;
        padding: 44px 0 36px !important;
        box-sizing: border-box !important;
    }

    .sz-author-profile-card {
        display: flex !important;
        align-items: flex-start !important;
        gap: 36px !important;
        background: #ffffff !important;
        border: 1.5px solid var(--sz-border) !important;
        border-radius: 20px !important;
        padding: 36px 40px !important;
        box-shadow: 0 10px 30px rgba(26, 32, 128, 0.04) !important;
        box-sizing: border-box !important;
    }

    .sz-author-avatar-wrap {
        flex-shrink: 0 !important;
        position: relative !important;
    }

    .sz-author-avatar {
        width: 140px !important;
        height: 140px !important;
        border-radius: 50% !important;
        object-fit: cover !important;
        border: 4px solid #ffffff !important;
        box-shadow: 0 8px 24px rgba(26, 32, 128, 0.14) !important;
        display: block !important;
    }

    .sz-author-verified-badge {
        position: absolute !important;
        bottom: 4px !important;
        right: 4px !important;
        background: var(--sz-primary) !important;
        color: #ffffff !important;
        border-radius: 50% !important;
        width: 28px !important;
        height: 28px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        border: 2.5px solid #ffffff !important;
        box-shadow: 0 3px 8px rgba(26, 32, 128, 0.25) !important;
    }

    .sz-author-info {
        flex-grow: 1 !important;
    }

    .sz-author-top-meta {
        display: flex !important;
        align-items: center !important;
        gap: 12px !important;
        margin-bottom: 8px !important;
        flex-wrap: wrap !important;
    }

    .sz-author-badge {
        background: #dbeafe !important;
        color: var(--sz-primary) !important;
        padding: 4px 12px !important;
        border-radius: 999px !important;
        font-size: 11.5px !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        display: inline-block !important;
    }

    .sz-author-stat {
        font-size: 13px !important;
        color: var(--sz-text-meta) !important;
        font-weight: 600 !important;
    }

    .sz-author-name {
        font-size: clamp(24px, 2.8vw, 32px) !important;
        font-weight: 800 !important;
        color: var(--sz-primary) !important;
        margin: 0 0 10px 0 !important;
        line-height: 1.2 !important;
        letter-spacing: -0.02em !important;
    }

    .sz-author-bio {
        font-size: 14.5px !important;
        line-height: 1.65 !important;
        color: var(--sz-text-desc) !important;
        margin: 0 0 18px 0 !important;
        max-width: 820px !important;
    }

    .sz-author-trust-pill {
        display: inline-flex !important;
        align-items: center !important;
        gap: 8px !important;
        background: #f8fafc !important;
        border: 1px solid var(--sz-border) !important;
        padding: 6px 14px !important;
        border-radius: 999px !important;
        font-size: 12px !important;
        color: var(--sz-text-meta) !important;
        font-weight: 600 !important;
    }

    /* 2. ARTICLES ARCHIVE SECTION */
    .sz-author-content-section {
        padding: 40px 0 60px !important;
        box-sizing: border-box !important;
    }

    .sz-author-section-heading {
        margin-bottom: 28px !important;
        padding-bottom: 14px !important;
        border-bottom: 1.5px solid var(--sz-border) !important;
        display: flex !important;
        justify-content: space-between !important;
        align-items: flex-end !important;
        flex-wrap: wrap !important;
        gap: 12px !important;
    }

    .sz-author-section-heading h2 {
        font-size: 20px !important;
        font-weight: 800 !important;
        color: var(--sz-primary) !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .sz-author-section-heading span {
        font-size: 13.5px !important;
        color: var(--sz-text-meta) !important;
        font-weight: 600 !important;
    }

    /* GRID ARTIKEL */
    .sz-author-grid {
        display: grid !important;
        grid-template-columns: repeat(3, 1fr) !important;
        gap: 24px !important;
        width: 100% !important;
        box-sizing: border-box !important;
    }

    .sz-card {
        background: #ffffff !important;
        border: 1px solid var(--sz-border) !important;
        border-radius: 18px !important;
        overflow: hidden !important;
        display: flex !important;
        flex-direction: column !important;
        transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease !important;
        box-shadow: 0 3px 10px rgba(15, 23, 42, 0.03) !important;
        box-sizing: border-box !important;
    }

    .sz-card:hover {
        transform: translateY(-4px) !important;
        box-shadow: 0 12px 24px rgba(15, 23, 42, 0.08) !important;
        border-color: #cbd5e1 !important;
    }

    .sz-card-img {
        aspect-ratio: 16 / 10 !important;
        width: 100% !important;
        overflow: hidden !important;
        display: block !important;
        background: #f1f5f9 !important;
        position: relative !important;
    }

    .sz-card-img img {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
        transition: transform 0.5s ease !important;
        display: block !important;
        margin: 0 !important;
    }

    .sz-card:hover .sz-card-img img {
        transform: scale(1.04) !important;
    }

    .sz-card-body {
        padding: 18px 20px !important;
        display: flex !important;
        flex-direction: column !important;
        flex-grow: 1 !important;
        box-sizing: border-box !important;
    }

    .sz-card-cat {
        display: inline-block !important;
        font-size: 11.5px !important;
        font-weight: 750 !important;
        color: var(--sz-primary) !important;
        text-transform: uppercase !important;
        letter-spacing: 0.04em !important;
        text-decoration: none !important;
        margin-bottom: 6px !important;
    }

    .sz-card-cat:hover {
        color: var(--sz-primary-hover) !important;
    }

    .sz-card-title {
        margin: 0 0 8px 0 !important;
        font-size: 16.5px !important;
        font-weight: 800 !important;
        line-height: 1.35 !important;
        padding: 0 !important;
    }

    .sz-card-title a {
        color: var(--sz-primary) !important;
        text-decoration: none !important;
        display: -webkit-box !important;
        -webkit-line-clamp: 2 !important;
        -webkit-box-orient: vertical !important;
        overflow: hidden !important;
        transition: color 0.2s ease !important;
    }

    .sz-card-title a:hover {
        color: var(--sz-primary-hover) !important;
    }

    .sz-card-excerpt {
        font-size: 13.5px !important;
        color: var(--sz-text-desc) !important;
        line-height: 1.5 !important;
        margin: 0 0 16px 0 !important;
        padding: 0 !important;
        display: -webkit-box !important;
        -webkit-line-clamp: 2 !important;
        -webkit-box-orient: vertical !important;
        overflow: hidden !important;
    }

    .sz-card-meta {
        margin-top: auto !important;
        padding-top: 12px !important;
        border-top: 1px solid #f1f5f9 !important;
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        font-size: 12px !important;
    }

    .sz-card-date {
        font-weight: 500 !important;
        color: var(--sz-text-meta) !important;
        font-size: 12px !important;
    }

    .sz-card-read-link {
        font-weight: 700 !important;
        color: var(--sz-primary) !important;
        text-transform: uppercase !important;
        font-size: 11.5px !important;
        letter-spacing: 0.03em !important;
        text-decoration: none !important;
        transition: all 0.2s ease !important;
    }

    .sz-card-read-link:hover {
        color: var(--sz-primary-hover) !important;
        transform: translateX(2px) !important;
    }

    .sz-author-no-posts {
        grid-column: 1 / -1 !important;
        text-align: center !important;
        padding: 48px 20px !important;
        background: #f8fafc !important;
        border-radius: 16px !important;
        border: 1px dashed #cbd5e1 !important;
        color: var(--sz-text-desc) !important;
        font-size: 15px !important;
    }

    /* 3. PAGINATION SEO */
    .sz-pagination-wrapper {
        margin-top: 44px !important;
        display: flex !important;
        justify-content: center !important;
    }

    .sz-pagination {
        display: flex !important;
        flex-wrap: wrap !important;
        justify-content: center !important;
        align-items: center !important;
        gap: 8px !important;
    }

    .sz-page {
        min-width: 38px !important;
        height: 38px !important;
        padding: 0 10px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        border: 1.5px solid var(--sz-border) !important;
        border-radius: 9px !important;
        color: var(--sz-primary) !important;
        font-weight: 700 !important;
        font-size: 13.5px !important;
        text-decoration: none !important;
        transition: all 0.2s ease !important;
        background: #ffffff !important;
        box-sizing: border-box !important;
    }

    .sz-page:hover {
        border-color: var(--sz-primary);
        color: var(--sz-primary) !important;
        transform: translateY(-2px) !important;
    }

    .sz-page.active {
        background: var(--sz-primary) !important;
        color: #ffffff !important;
        border-color: var(--sz-primary) !important;
        box-shadow: 0 4px 12px rgba(26, 32, 128, 0.25) !important;
        pointer-events: none !important;
    }

    /* 4. RESPONSIVE BREAKPOINTS */
    @media (max-width: 991px) {
        .sz-author-grid {
            grid-template-columns: repeat(2, 1fr) !important;
            gap: 20px !important;
        }

        .sz-author-profile-card {
            padding: 28px 30px !important;
            gap: 28px !important;
        }

        .sz-author-avatar {
            width: 120px !important;
            height: 120px !important;
        }
    }

    @media (max-width: 767px) {
        .sz-author-hero {
            padding: 24px 0 20px !important;
        }

        .sz-author-profile-card {
            flex-direction: column !important;
            align-items: center !important;
            text-align: center !important;
            padding: 24px 18px !important;
            gap: 18px !important;
            border-radius: 16px !important;
        }

        .sz-author-avatar {
            width: 100px !important;
            height: 100px !important;
        }

        .sz-author-top-meta {
            justify-content: center !important;
        }

        .sz-author-name {
            font-size: 22px !important;
            margin-bottom: 8px !important;
        }

        .sz-author-bio {
            font-size: 13.5px !important;
            line-height: 1.55 !important;
        }

        .sz-author-content-section {
            padding: 26px 0 44px !important;
        }

        .sz-author-grid {
            grid-template-columns: 1fr !important;
            gap: 16px !important;
        }

        .sz-card {
            border-radius: 14px !important;
        }

        .sz-card-body {
            padding: 14px 16px !important;
        }

        .sz-card-title {
            font-size: 15.5px !important;
        }

        .sz-pagination-wrapper {
            margin-top: 30px !important;
        }

        .sz-page {
            min-width: 34px !important;
            height: 34px !important;
            font-size: 12.5px !important;
        }
    }
    </style>

    <!-- HTML LAYOUT -->
    <main class="sz-author-wrapper" id="content">

        <!-- 1. AUTHOR PROFILE HERO SECTION -->
        <section class="sz-author-hero">
            <div class="sz-author-container">
                <div class="sz-author-profile-card">
                    <div class="sz-author-avatar-wrap">
                        <img src="<?php echo esc_url($avatar_url); ?>" alt="<?php echo esc_attr($display_name); ?>" class="sz-author-avatar">
                        <span class="sz-author-verified-badge" title="Verified Medical Contributor">
                            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </span>
                    </div>

                    <div class="sz-author-info">
                        <div class="sz-author-top-meta">
                            <span class="sz-author-badge"><?php echo esc_html($role_badge); ?></span>
                            <span class="sz-author-stat">&bull; <?php echo (int) $post_count; ?> Artikel Diterbitkan</span>
                        </div>

                        <h1 class="sz-author-name"><?php echo esc_html($display_name); ?></h1>
                        <p class="sz-author-bio"><?php echo esc_html($bio); ?></p>

                        <div class="sz-author-trust-pill">
                            <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="#1A2080" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                            </svg>
                            <span>Ditinjau oleh Tim Dokter Sozo Skin Clinic</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 2. ARTICLE ARCHIVE SECTION -->
        <section class="sz-author-content-section">
            <div class="sz-author-container">
                <div class="sz-author-section-heading">
                    <h2>Artikel oleh <?php echo esc_html($display_name); ?></h2>
                    <span>Halaman <?php echo (int) $paged; ?> dari <?php echo (int) $author_query->max_num_pages ?: 1; ?></span>
                </div>

                <div class="sz-author-grid">
                    <?php
                    if ($author_query->have_posts()) :
                        while ($author_query->have_posts()) : $author_query->the_post();
                            $post_id   = get_the_ID();
                            $thumb_id  = get_post_thumbnail_id($post_id);
                            $thumb     = $thumb_id ? wp_get_attachment_image_url($thumb_id, 'medium_large') : 'https://asset.sozoskinclinic.com/wp-content/uploads/2026/06/LP-Facial-Terdekat-V2-Hero-Image.webp';
                            $cats      = get_the_category();
                            $cat_name  = !empty($cats) ? esc_html($cats[0]->name) : 'Artikel';
                            $cat_link  = !empty($cats) ? get_category_link($cats[0]->term_id) : '#';
                            $title     = get_the_title();
                            $excerpt   = wp_trim_words(get_the_excerpt(), 15, '...');

                            // Logika tanggal update > 24 jam
                            $u_time = get_the_modified_time('U', $post_id);
                            $p_time = get_the_time('U', $post_id);
                            $date_display = (($u_time - $p_time) > 86400) ? 'Diperbarui: ' . get_the_modified_date('j M Y', $post_id) : get_the_date('j M Y', $post_id);
                            ?>
                            <article class="sz-card">
                                <a href="<?php the_permalink(); ?>" class="sz-card-img" aria-label="<?php echo esc_attr($title); ?>">
                                    <img src="<?php echo esc_url($thumb); ?>" alt="<?php echo esc_attr($title); ?>" loading="lazy" decoding="async">
                                </a>
                                <div class="sz-card-body">
                                    <a href="<?php echo esc_url($cat_link); ?>" class="sz-card-cat"><?php echo $cat_name; ?></a>
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
                        endwhile;
                        wp_reset_postdata();
                    else :
                    ?>
                        <p class="sz-author-no-posts">Belum ada artikel yang diterbitkan oleh penulis ini.</p>
                    <?php endif; ?>
                </div>

                <!-- 3. SEO PAGINATION -->
                <?php if ($author_query->max_num_pages > 1) : ?>
                    <div class="sz-pagination-wrapper">
                        <div class="sz-pagination">
                            <?php
                            $max = $author_query->max_num_pages;
                            if ($paged > 1) {
                                echo '<a href="' . esc_url(get_pagenum_link($paged - 1)) . '" class="sz-page" aria-label="Sebelumnya">&#10094;</a>';
                            }
                            for ($i = 1; $i <= $max; $i++) {
                                if ($i == 1 || $i == $max || ($i >= $paged - 1 && $i <= $paged + 1)) {
                                    $cls = ($i == $paged) ? 'active' : '';
                                    echo '<a href="' . esc_url(get_pagenum_link($i)) . '" class="sz-page ' . $cls . '">' . $i . '</a>';
                                }
                            }
                            if ($paged < $max) {
                                echo '<a href="' . esc_url(get_pagenum_link($paged + 1)) . '" class="sz-page" aria-label="Berikutnya">&#10095;</a>';
                            }
                            ?>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </section>

    </main>
    <?php
}
