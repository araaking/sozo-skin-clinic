<?php
/**
 * WPCode Snippet - Dynamic Article Schema untuk Blog
 * 
 * Tipe      : PHP Snippet
 * Location  : Run Everywhere (atau kondisi: Page URL contains /blog/)
 * 
 * Cara pakai:
 * 1. Copy semua kode ini ke WPCode > Add New > Code Type: PHP Snippet
 * 2. Insert Method: Auto Insert > Location: Run Everywhere
 * 3. Smart Conditional Logic: Show if "Page URL contains" > /blog/
 * 4. Aktifkan & Update
 */

add_action( 'wp_head', function() {
    // Hanya jalankan di single post / artikel
    if ( ! is_single() ) return;

    global $post;
    if ( ! $post ) return;

    // Ambil data dari WordPress
    $permalink    = get_permalink( $post );
    $title        = esc_attr( get_the_title( $post ) );
    $excerpt      = esc_attr( get_the_excerpt( $post ) ?: wp_trim_words( strip_tags( $post->post_content ), 20 ) );
    $author_name  = get_the_author_meta( 'display_name', $post->post_author );
    $date_pub     = get_the_date( 'c', $post );
    $date_mod     = get_the_modified_date( 'c', $post );
    $thumbnail    = get_the_post_thumbnail_url( $post, 'full' ) ?: '';
    $category     = get_the_category( $post );
    $cat_name     = ! empty( $category ) ? esc_attr( $category[0]->name ) : 'Blog';

    // Breadcrumb: Home > Blog > [Category] > [Judul]
    $breadcrumb_items = [
        [
            '@type' => 'ListItem',
            'position' => 1,
            'name' => 'Home',
            'item' => home_url( '/' )
        ],
        [
            '@type' => 'ListItem',
            'position' => 2,
            'name' => 'Blog',
            'item' => home_url( '/blog/' )
        ]
    ];

    $position = 3;
    if ( ! empty( $category ) ) {
        $breadcrumb_items[] = [
            '@type' => 'ListItem',
            'position' => $position,
            'name' => $cat_name,
            'item' => get_category_link( $category[0]->term_id )
        ];
        $position++;
    }

    $breadcrumb_items[] = [
        '@type' => 'ListItem',
        'position' => $position,
        'name' => $title,
        'item' => $permalink
    ];

    // Bangun schema
    $schema = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'Article',
                '@id' => $permalink . '#article',
                'headline' => $title,
                'description' => $excerpt,
                'inLanguage' => 'id',
                'isPartOf' => [
                    '@id' => home_url( '/' ) . '#website'
                ],
                'about' => [
                    '@id' => home_url( '/' ) . '#organization'
                ],
                'author' => [
                    '@type' => 'Person',
                    'name' => $author_name
                ],
                'publisher' => [
                    '@id' => home_url( '/' ) . '#organization'
                ],
                'image' => ! empty( $thumbnail ) ? [
                    '@type' => 'ImageObject',
                    'url' => $thumbnail
                ] : null,
                'datePublished' => $date_pub,
                'dateModified' => $date_mod,
                'breadcrumb' => [
                    '@id' => $permalink . '#breadcrumb'
                ],
                'mainEntityOfPage' => [
                    '@type' => 'WebPage',
                    '@id' => $permalink
                ]
            ],
            [
                '@type' => 'BreadcrumbList',
                '@id' => $permalink . '#breadcrumb',
                'itemListElement' => $breadcrumb_items
            ]
        ]
    ];

    // Hapus null values (misal kalau tidak ada thumbnail)
    $schema['@graph'][0] = array_filter( $schema['@graph'][0], function( $v ) {
        return $v !== null;
    });

    // Output
    echo '<script type="application/ld+json">' . "\n";
    echo wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . "\n";
    echo '</script>' . "\n";
}, 1 );
