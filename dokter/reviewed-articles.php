/**
 * ==========================================================
 * SOZO SKIN - REVIEWED ARTICLES BY DOCTOR
 * Shortcode: [sozoskin_reviewed_by doctor="gesha" per_page="6"]
 *
 * Cara pakai (Elementor halaman detail dokter, Shortcode widget):
 *   dr-gesha  → [sozoskin_reviewed_by doctor="gesha"]
 *   dr-audi   → [sozoskin_reviewed_by doctor="audi"]
 *   dr-eli    → [sozoskin_reviewed_by doctor="elisabeth"]
 *   dr-putri  → [sozoskin_reviewed_by doctor="putri"]
 *   dr-sherly → [sozoskin_reviewed_by doctor="syerli"]
 * ==========================================================
 */
if (!function_exists('sz_reviewed_by_shortcode')) {
    function sz_reviewed_by_shortcode($atts) {
        $atts = shortcode_atts(
            array(
                'doctor'   => '',
                'per_page' => 6,
            ),
            $atts,
            'sozoskin_reviewed_by'
        );
        $doctor_key = sanitize_key($atts['doctor']);
        $per_page   = max(1, min(12, (int) $atts['per_page']));
        if ($doctor_key === '') {
            return '<p style="text-align:center;">Shortcode butuh atribut doctor. Contoh: [sozoskin_reviewed_by doctor="gesha"]</p>';
        }
        // ── Nama dokter: dari registry singlepost.php kalau ada ──
        $doctor_name = ucfirst($doctor_key);
        if (function_exists('sz_get_reviewers_registry')) {
            $registry = sz_get_reviewers_registry();
            if (!isset($registry[$doctor_key])) {
                return '<p style="text-align:center;">Dokter "' . esc_html($doctor_key) . '" tidak dikenal.</p>';
            }
            $doctor_name = $registry[$doctor_key]['display_name'];
        }
        // ── Pagination: Pages pakai 'page', Posts pakai 'paged' ──
        $paged = 1;
        if (get_query_var('paged')) {
            $paged = (int) get_query_var('paged');
        } elseif (get_query_var('page')) {
            $paged = (int) get_query_var('page');
        }
        $paged = max(1, $paged);
        $query_args = array(
            'post_type'           => 'post',
            'post_status'         => 'publish',
            'posts_per_page'      => $per_page,
            'paged'               => $paged,
            'orderby'             => 'modified',
            'order'               => 'DESC',
            'ignore_sticky_posts' => true,
            'no_found_rows'       => false,
            'meta_query'          => array(
                array(
                    'key'     => 'sozo_reviewer_key',
                    'value'   => $doctor_key,
                    'compare' => '=',
                ),
            ),
        );
        $reviewed_query = new WP_Query($query_args);
        // CSS hanya dicetak sekali per page load (shortcode bisa dipasang >1x).
        static $sz_rev_css_printed = false;
        $sz_rev_print_css = !$sz_rev_css_printed;
        $sz_rev_css_printed = true;
        // Anchor section: pagination balik ke section, bukan ke atas halaman.
        $sz_rev_anchor = '#sz-rev-' . $doctor_key;
        ob_start();
        ?>
        <?php if ($sz_rev_print_css) : ?>
        <style>
            .sz-rev-section {
                font-family: 'Poppins', system-ui, -apple-system, sans-serif !important;
                background: #ffffff !important;
                padding: 80px 24px !important;
            }
            .sz-rev-section * { box-sizing: border-box !important; }
            .sz-rev-container { max-width: 1280px !important; margin: 0 auto !important; }
            .sz-rev-heading { font-size: 32px !important; line-height: 1.25 !important; font-weight: 600 !important; color: #1A2080 !important; margin: 0 0 32px 0 !important; letter-spacing: 0 !important; }
            .sz-rev-grid { display: grid !important; grid-template-columns: repeat(3, minmax(0, 1fr)) !important; gap: 24px !important; }
            .sz-rev-card {
                background: #ffffff !important;
                border: 1px solid #DFDBD4 !important;
                border-radius: 16px !important;
                overflow: hidden !important;
                display: flex !important;
                flex-direction: column !important;
                box-shadow: none !important;
                transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease !important;
            }
            .sz-rev-card:hover {
                transform: translateY(-2px) !important;
                border-color: #D9C7B1 !important;
                box-shadow: 0 8px 24px rgba(23, 23, 21, 0.07) !important;
            }
            .sz-rev-card:focus-within { outline: 2px solid #1A2080 !important; outline-offset: 3px !important; }
            .sz-rev-card-img { display: block !important; aspect-ratio: 16 / 9 !important; overflow: hidden !important; background: #E9DFD5 !important; }
            .sz-rev-card-img img { width: 100% !important; height: 100% !important; object-fit: cover !important; display: block !important; margin: 0 !important; transition: transform 0.3s ease !important; }
            .sz-rev-card:hover .sz-rev-card-img img { transform: scale(1.025) !important; }
            .sz-rev-card-body { padding: 24px !important; display: flex !important; flex-direction: column !important; flex-grow: 1 !important; }
            .sz-rev-cat { font-size: 12px !important; line-height: 1.4 !important; font-weight: 600 !important; text-transform: none !important; letter-spacing: 0 !important; color: #1A2080 !important; text-decoration: none !important; margin-bottom: 12px !important; display: inline-flex !important; align-self: flex-start !important; align-items: center !important; padding: 6px 10px !important; border-radius: 999px !important; background: #E9DFD5 !important; }
            .sz-rev-cat:hover { background: #D9C7B1 !important; }
            .sz-rev-title { margin: 0 0 12px 0 !important; font-size: 20px !important; line-height: 1.35 !important; font-weight: 600 !important; }
            .sz-rev-title a { color: #171715 !important; text-decoration: none !important; display: -webkit-box !important; -webkit-line-clamp: 2 !important; -webkit-box-orient: vertical !important; overflow: hidden !important; }
            .sz-rev-title a:hover { color: #1A2080 !important; }
            .sz-rev-excerpt { font-size: 14px !important; line-height: 1.55 !important; color: #41413E !important; margin: 0 0 24px 0 !important; display: -webkit-box !important; -webkit-line-clamp: 2 !important; -webkit-box-orient: vertical !important; overflow: hidden !important; }
            .sz-rev-meta { margin-top: auto !important; display: flex !important; align-items: center !important; justify-content: space-between !important; gap: 12px !important; font-size: 12px !important; line-height: 1.4 !important; color: #777771 !important; font-weight: 500 !important; }
            .sz-rev-read { color: #1A2080 !important; font-weight: 600 !important; text-decoration: none !important; white-space: nowrap !important; }
            .sz-rev-read:hover { text-decoration: underline !important; text-underline-offset: 3px !important; }
            .sz-rev-empty { background: #ffffff !important; border: 1px solid #DFDBD4 !important; border-radius: 16px !important; padding: 32px 24px !important; text-align: center !important; color: #41413E !important; font-size: 14px !important; line-height: 1.55 !important; }
            .sz-rev-pagination {
                display: flex !important;
                justify-content: center !important;
                align-items: center !important;
                flex-wrap: wrap !important;
                gap: 8px !important;
                margin-top: 40px !important;
                padding-top: 0 !important;
            }
            .sz-rev-pagination .sz-page,
            .sz-rev-pagination a,
            .sz-rev-pagination span.current,
            .sz-rev-pagination .page-numbers {
                min-width: 44px !important;
                height: 44px !important;
                padding: 0 12px !important;
                margin: 0 !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                border: 1px solid #DFDBD4 !important;
                background: #ffffff !important;
                color: #171715 !important;
                border-radius: 12px !important;
                font-size: 14px !important;
                font-weight: 600 !important;
                text-decoration: none !important;
                box-shadow: none !important;
                transition: border-color 0.2s ease, color 0.2s ease, background 0.2s ease !important;
            }
            .sz-rev-pagination .sz-page:hover,
            .sz-rev-pagination a:hover {
                border-color: #1A2080 !important;
                color: #1A2080 !important;
                background: #E9DFD5 !important;
            }
            .sz-rev-pagination .sz-page.active,
            .sz-rev-pagination span.current {
                background: #1A2080 !important;
                border-color: #1A2080 !important;
                color: #ffffff !important;
                box-shadow: none !important;
            }
            .sz-rev-pagination a:focus-visible,
            .sz-rev-cat:focus-visible,
            .sz-rev-title a:focus-visible,
            .sz-rev-read:focus-visible { outline: 2px solid #1A2080 !important; outline-offset: 3px !important; }
            .sz-rev-pagination .sz-arrow {
                font-size: 18px !important;
                line-height: 1 !important;
            }
            .sz-rev-pagination .sz-dots { 
                color: #777771 !important; 
                font-weight: 700 !important; 
                padding: 0 4px !important; 
            }
            @media (max-width: 920px) {
                .sz-rev-section { padding: 64px 20px !important; }
                .sz-rev-grid { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; }
            }
            @media (max-width: 640px) { 
                .sz-rev-section { padding: 56px 16px !important; }
                .sz-rev-heading { font-size: 24px !important; line-height: 1.3 !important; margin-bottom: 24px !important; }
                .sz-rev-grid { grid-template-columns: 1fr !important; gap: 20px !important; }
                .sz-rev-card-body { padding: 20px !important; }
                .sz-rev-title { font-size: 18px !important; }
                .sz-rev-pagination .sz-page,
                .sz-rev-pagination a,
                .sz-rev-pagination span.current {
                    min-width: 40px !important;
                    height: 40px !important;
                    padding: 0 10px !important;
                    font-size: 13px !important;
                }
            }
            @media (prefers-reduced-motion: reduce) {
                .sz-rev-card, .sz-rev-card-img img, .sz-rev-pagination a { transition: none !important; }
            }
        </style>
        <?php endif; ?>
        <section class="sz-rev-section" id="sz-rev-<?php echo esc_attr($doctor_key); ?>" aria-label="Artikel yang ditinjau oleh <?php echo esc_attr($doctor_name); ?>">
            <div class="sz-rev-container">
                <h2 class="sz-rev-heading">Artikel yang Ditinjau oleh <?php echo esc_html($doctor_name); ?></h2>
                <?php if ($reviewed_query->have_posts()) : ?>
                    <div class="sz-rev-grid">
                        <?php
                        while ($reviewed_query->have_posts()) :
                            $reviewed_query->the_post();
                            $card_id    = get_the_ID();
                            $thumb_id   = get_post_thumbnail_id($card_id);
                            $card_title = get_the_title($card_id);
                            $card_link  = get_permalink($card_id);
                            $cats       = get_the_category($card_id);
                            $cat_name   = !empty($cats) ? $cats[0]->name : 'Artikel';
                            $cat_link   = '#';
                            if (!empty($cats)) {
                                $cat_tmp = get_category_link($cats[0]->term_id);
                                if (!is_wp_error($cat_tmp) && !empty($cat_tmp)) {
                                    $cat_link = $cat_tmp;
                                }
                            }
                            $u_time     = get_the_modified_time('U', $card_id);
                            $p_time     = get_the_time('U', $card_id);
                            $date_label = (($u_time - $p_time) > 86400)
                                ? 'Diperbarui: ' . get_the_modified_date('j M Y', $card_id)
                                : get_the_date('j M Y', $card_id);
                            $excerpt = wp_trim_words(get_the_excerpt($card_id), 15, '...');
                        ?>
                            <article class="sz-rev-card">
                                <a href="<?php echo esc_url($card_link); ?>" class="sz-rev-card-img" aria-label="<?php echo esc_attr($card_title); ?>" tabindex="-1">
                                    <?php if ($thumb_id) : ?>
                                        <?php echo wp_get_attachment_image($thumb_id, 'medium_large', false, array('alt' => $card_title, 'loading' => 'lazy', 'decoding' => 'async')); ?>
                                    <?php else : ?>
                                        <img src="https://asset.sozoskinclinic.com/wp-content/uploads/2026/06/LP-Facial-Terdekat-V2-Hero-Image.webp" alt="<?php echo esc_attr($card_title); ?>" loading="lazy" decoding="async">
                                    <?php endif; ?>
                                </a>
                                <div class="sz-rev-card-body">
                                    <a href="<?php echo esc_url($cat_link); ?>" class="sz-rev-cat"><?php echo esc_html($cat_name); ?></a>
                                    <h3 class="sz-rev-title">
                                        <a href="<?php echo esc_url($card_link); ?>"><?php echo esc_html($card_title); ?></a>
                                    </h3>
                                    <p class="sz-rev-excerpt"><?php echo esc_html($excerpt); ?></p>
                                    <div class="sz-rev-meta">
                                        <span><?php echo esc_html($date_label); ?></span>
                                        <a href="<?php echo esc_url($card_link); ?>" class="sz-rev-read">Baca &rarr;</a>
                                    </div>
                                </div>
                            </article>
                        <?php endwhile; ?>
                    </div>
                    <?php
                    $max_pages = (int) $reviewed_query->max_num_pages;
                    if ($max_pages > 1) :
                        if (function_exists('sz_generate_seo_pagination')) {
                            $pg_html = sz_generate_seo_pagination($max_pages, $paged);
                            // Samakan wrapper: homeblog return .sz-pagination, ganti ke .sz-rev-pagination (satu lapis).
                            $pg_html = str_replace('class="sz-pagination"', 'class="sz-rev-pagination"', $pg_html);
                            $pg_html = str_replace("class='sz-pagination'", "class='sz-rev-pagination'", $pg_html);
                            // Tambahkan anchor section ke tiap link pagination.
                            $pg_html = preg_replace("/href='([^'#]+)'/", "href='$1" . $sz_rev_anchor . "'", $pg_html);
                            $pg_html = preg_replace('/href="([^"#]+)"/', 'href="$1' . $sz_rev_anchor . '"', $pg_html);
                            echo $pg_html;
                        } else {
                            echo sz_rev_seo_pagination($max_pages, $paged, $sz_rev_anchor);
                        }
                    endif;
                    ?>
                <?php else : ?>
                    <div class="sz-rev-empty">
                        Belum ada artikel yang ditinjau oleh <?php echo esc_html($doctor_name); ?>.
                        Artikel baru akan muncul otomatis setelah editor memilih beliau di meta box "Peninjau Medis".
                    </div>
                <?php endif; ?>
            </div>
        </section>
        <?php
        wp_reset_postdata();
        return ob_get_clean();
    }
    add_shortcode('sozoskin_reviewed_by', 'sz_reviewed_by_shortcode');
}
/**
 * Fallback pagination SEO-friendly dengan tombol Prev & Next.
 */
if (!function_exists('sz_rev_seo_pagination')) {
    function sz_rev_seo_pagination($max, $current, $anchor = '') {
        if ($max <= 1) {
            return '';
        }
        $html = '<div class="sz-rev-pagination">';
        // Tombol Sebelumnya (<)
        if ($current > 1) {
            $prev_link = esc_url(get_pagenum_link($current - 1) . $anchor);
            $html .= "<a href='{$prev_link}' class='sz-page sz-arrow' aria-label='Sebelumnya'>&lsaquo;</a>";
        }
        for ($i = 1; $i <= $max; $i++) {
            if ($i === 1 || $i === $max || ($i >= $current - 1 && $i <= $current + 1)) {
                $cls  = ($i === $current) ? 'sz-page active' : 'sz-page';
                $link = esc_url(get_pagenum_link($i) . $anchor);
                $html .= "<a href='{$link}' class='{$cls}'>{$i}</a>";
            } elseif ($i === 2 && $current > 3) {
                $html .= "<span class='sz-dots'>...</span>";
            } elseif ($i === $max - 1 && $current < $max - 2) {
                $html .= "<span class='sz-dots'>...</span>";
            }
        }
        // Tombol Selanjutnya (>)
        if ($current < $max) {
            $next_link = esc_url(get_pagenum_link($current + 1) . $anchor);
            $html .= "<a href='{$next_link}' class='sz-page sz-arrow' aria-label='Selanjutnya'>&rsaquo;</a>";
        }
        $html .= '</div>';
        return $html;
    }
}
