// ==========================================================
// 1. SHORTCODE UTAMA: [sozoskin_home]
// ==========================================================
add_shortcode('sozoskin_home', 'sz_home_shortcode');
function sz_home_shortcode() {
    ob_start();
    
    // Deteksi Halaman Saat Ini (SEO Friendly)
    $paged = (get_query_var('paged')) ? get_query_var('paged') : ((get_query_var('page')) ? get_query_var('page') : 1);
    $posts_per_page = 6;
    
    // 1. Dapatkan Hero Post terlebih dahulu
    // (Ini dipindah ke atas agar kita bisa mengambil ID-nya untuk di-exclude di list artikel terbaru)
    $hero_query = new WP_Query([
        'posts_per_page' => 1, 
        'post_status'    => 'publish',
        'orderby'        => 'post_modified',
        'order'          => 'DESC'
    ]);

    $hero_id = 0;
    if ($hero_query->have_posts()) {
        $hero_id = $hero_query->posts[0]->ID; // Simpan ID post hero
    }
    ?>
    
    <div class="sz-blog-wrapper">
        
        <?php if ($paged == 1 && $hero_query->have_posts()) : ?>
            <div id="sz-hero-wrapper">
                <?php
                // Tampilkan post Hero
                $hero_query->the_post();
                $cats = get_the_category();
                $cat_name = !empty($cats) ? esc_html($cats[0]->name) : 'Artikel';
                $thumb = get_the_post_thumbnail_url(get_the_ID(), 'large') ?: 'https://images.unsplash.com/photo-1511295742362-92c96b5add36?auto=format&fit=crop&q=80&w=1200';
                ?>
                <div class="sz-hero">
                    <div class="sz-hero-img">
                        <a href="<?php the_permalink(); ?>"><img src="<?php echo esc_url($thumb); ?>" alt="Hero"></a>
                        <span class="sz-badge">Pilihan Editor</span>
                    </div>
                    <div class="sz-hero-content">
                        <span class="sz-cat"><?php echo $cat_name; ?></span>
                        <h1 class="sz-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>
                        <p class="sz-excerpt"><?php echo wp_trim_words(get_the_excerpt(), 20); ?></p>
                        <a href="<?php the_permalink(); ?>" class="sz-btn">Baca Artikel</a>
                    </div>
                </div>
                <?php wp_reset_postdata(); ?>
                
                <div class="sz-topics-section">
                    <div class="sz-header-flex">
                        <h2>Jelajahi Topik</h2>
                        <a href="/blog/">Lihat Semua &rarr;</a>
                    </div>
                    <div class="sz-topics-grid">
                        <a href="/blog/category/hair/" class="sz-topic"><span>✨ Hair</span></a>
                        <a href="/blog/category/skin/" class="sz-topic"><span>💧 Skin</span></a>
                        <a href="/blog/category/slimming/" class="sz-topic"><span>⚖️ Slimming</span></a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="sz-recent-section" id="sz-scroll-target">
            <div class="sz-header-flex">
                <h2>Artikel Terbaru</h2>
            </div>
            
            <div class="sz-article-grid">
                <?php
                // 2. Recent Query: Menggunakan paged standar dan post__not_in 
                // Kita TIDAK menggunakan offset manual lagi.
                $query_args = [
                    'post_status'    => 'publish',
                    'posts_per_page' => $posts_per_page,
                    'paged'          => $paged,
                    'orderby'        => 'post_modified',
                    'order'          => 'DESC'
                ];

                // Kecualikan post hero agar tidak muncul dua kali di list artikel terbaru
                if ($hero_id) {
                    $query_args['post__not_in'] = [$hero_id];
                }

                $recent_query = new WP_Query($query_args);
                
                if ($recent_query->have_posts()) {
                    while ($recent_query->have_posts()) {
                        $recent_query->the_post();
                        sz_render_card();
                    }
                    wp_reset_postdata();
                }
                ?>
            </div>
            
            <div class="sz-pagination">
                <?php
                // 3. Gunakan $recent_query->max_num_pages bawaan WordPress
                // Ini 100% akurat tanpa perlu menghitung offset atau hitung total_posts manual.
                $max_pages = $recent_query->max_num_pages;
                
                if ($max_pages > 1) {
                    echo sz_generate_seo_pagination($max_pages, $paged);
                }
                ?>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// ==========================================================
// 2. FUNGSI RENDER KARTU ARTIKEL (TOLERANSI WAKTU 24 JAM)
// ==========================================================
function sz_render_card() {
    $thumb = get_the_post_thumbnail_url(get_the_ID(), 'medium_large') ?: 'https://images.unsplash.com/photo-1490645935967-10de6ba17061?auto=format&fit=crop&q=80&w=600';
    $cats = get_the_category();
    $cat_name = !empty($cats) ? esc_html($cats[0]->name) : 'Artikel';

    $u_time = get_the_modified_time('U');
    $p_time = get_the_time('U');
    
    // Hanya tampilkan "Diperbarui" jika jeda edit minimal 1 hari (86400 detik) setelah publish
    if (($u_time - $p_time) > 86400) {
        $date_display = 'Diperbarui: ' . get_the_modified_date();
    } else {
        $date_display = get_the_date();
    }
    ?>
    <div class="sz-card">
        <a href="<?php the_permalink(); ?>" class="sz-card-img">
            <img src="<?php echo esc_url($thumb); ?>" alt="Thumbnail">
            <span class="sz-card-badge"><?php echo $cat_name; ?></span>
        </a>
        <div class="sz-card-body">
            <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
            <div class="sz-meta">
                <span><?php echo esc_html($date_display); ?></span>
                <a href="<?php the_permalink(); ?>">Baca</a>
            </div>
        </div>
    </div>
    <?php
}

// ==========================================================
// 3. FUNGSI RENDER PAGINATION SEO (URL FISIK BERSIH)
// ==========================================================
function sz_generate_seo_pagination($max, $current) {
    $html = '';
    if ($max <= 1) return '';

    // Tombol Prev (<)
    if ($current > 1) {
        $prev = $current - 1;
        $link = get_pagenum_link($prev);
        $html .= "<a href='{$link}' class='sz-page sz-arrow'>&#10094;</a>";
    }

    // Angka Halaman dengan Elipsis (...)
    for ($i = 1; $i <= $max; $i++) {
        if ($i == 1 || $i == $max || ($i >= $current - 1 && $i <= $current + 1)) {
            $cls = ($i == $current) ? 'active' : '';
            $link = get_pagenum_link($i);
            $html .= "<a href='{$link}' class='sz-page $cls'>$i</a>";
        } elseif ($i == 2 && $current > 3) {
            $html .= "<span class='sz-dots'>...</span>";
        } elseif ($i == $max - 1 && $current < $max - 2) {
            $html .= "<span class='sz-dots'>...</span>";
        }
    }

    // Tombol Next (>)
    if ($current < $max) {
        $next = $current + 1;
        $link = get_pagenum_link($next);
        $html .= "<a href='{$link}' class='sz-page sz-arrow'>&#10095;</a>";
    }

    return $html;
}