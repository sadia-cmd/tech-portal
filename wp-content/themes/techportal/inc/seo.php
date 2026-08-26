<?php
/**
 * SEO Functions for Tech Portal
 *
 * @package TechPortal
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Add Open Graph meta tags
 */
function techportal_opengraph_meta() {
    if ( is_singular() && ! is_front_page() ) {
        global $post;
        $title       = get_the_title( $post );
        $description = wp_trim_words( strip_tags( $post->post_content ), 30 );
        $url         = get_permalink( $post );
        $image       = get_the_post_thumbnail_url( $post, 'large' ) ?: '';
        $type        = is_singular( 'post' ) ? 'article' : 'website';
        $site_name   = get_bloginfo( 'name' );
        $locale      = get_locale();
    } elseif ( is_front_page() ) {
        $title       = get_bloginfo( 'name' ) . ' — ' . get_bloginfo( 'description' );
        $description = get_bloginfo( 'description' );
        $url         = home_url( '/' );
        $image       = '';
        $type        = 'website';
        $site_name   = get_bloginfo( 'name' );
        $locale      = get_locale();
    } else {
        $title       = get_bloginfo( 'name' );
        $description = get_bloginfo( 'description' );
        $url         = home_url( '/' );
        $image       = '';
        $type        = 'website';
        $site_name   = get_bloginfo( 'name' );
        $locale      = get_locale();
    }

    echo "\n<!-- Open Graph -->\n";
    echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
    echo '<meta property="og:description" content="' . esc_attr( $description ) . '">' . "\n";
    echo '<meta property="og:url" content="' . esc_url( $url ) . '">' . "\n";
    echo '<meta property="og:type" content="' . esc_attr( $type ) . '">' . "\n";
    echo '<meta property="og:site_name" content="' . esc_attr( $site_name ) . '">' . "\n";
    echo '<meta property="og:locale" content="' . esc_attr( $locale ) . '">' . "\n";
    if ( $image ) {
        echo '<meta property="og:image" content="' . esc_url( $image ) . '">' . "\n";
    }

    // Twitter Card
    echo "\n<!-- Twitter Card -->\n";
    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
    echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '">' . "\n";
    echo '<meta name="twitter:description" content="' . esc_attr( $description ) . '">' . "\n";
    if ( $image ) {
        echo '<meta name="twitter:image" content="' . esc_url( $image ) . '">' . "\n";
    }
}
add_action( 'wp_head', 'techportal_opengraph_meta', 1 );

/**
 * Add JSON-LD structured data
 */
function techportal_structured_data() {
    if ( is_singular( 'post' ) ) {
        global $post;
        $schema = array(
            '@context'      => 'https://schema.org',
            '@type'         => 'NewsArticle',
            'headline'      => get_the_title( $post ),
            'description'   => wp_trim_words( strip_tags( $post->post_content ), 30 ),
            'datePublished' => get_the_date( 'c', $post ),
            'dateModified'  => get_the_modified_date( 'c', $post ),
            'author'        => array(
                '@type' => 'Person',
                'name'  => get_the_author_meta( 'display_name', $post->post_author ),
            ),
            'publisher'     => array(
                '@type' => 'Organization',
                'name'  => get_bloginfo( 'name' ),
                'logo'  => array(
                    '@type' => 'ImageObject',
                    'url'   => home_url( '/wp-content/themes/techportal/assets/images/logo.png' ),
                ),
            ),
            'mainEntityOfPage' => array(
                '@type' => 'WebPage',
                '@id'   => get_permalink( $post ),
            ),
        );

        if ( has_post_thumbnail( $post ) ) {
            $schema['image'] = array(
                '@type'  => 'ImageObject',
                'url'    => get_the_post_thumbnail_url( $post, 'large' ),
                'width'  => 1200,
                'height' => 630,
            );
        }

        echo "\n<script type=\"application/ld+json\">\n";
        echo wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
        echo "\n</script>\n";
    } elseif ( is_front_page() ) {
        $schema = array(
            '@context'  => 'https://schema.org',
            '@type'     => 'WebSite',
            'name'      => get_bloginfo( 'name' ),
            'url'       => home_url( '/' ),
            'description' => get_bloginfo( 'description' ),
            'potentialAction' => array(
                '@type'       => 'SearchAction',
                'target'      => home_url( '/?s={search_term_string}' ),
                'query-input' => 'required name=search_term_string',
            ),
        );

        echo "\n<script type=\"application/ld+json\">\n";
        echo wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
        echo "\n</script>\n";
    }
}
add_action( 'wp_head', 'techportal_structured_data', 2 );

/**
 * Add meta description for pages
 */
function techportal_meta_description() {
    if ( is_singular() ) {
        global $post;
        $desc = wp_trim_words( strip_tags( $post->post_content ), 25 );
        echo '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n";
    } elseif ( is_front_page() ) {
        echo '<meta name="description" content="' . esc_attr( get_bloginfo( 'description' ) ) . '">' . "\n";
    } elseif ( is_category() ) {
        $desc = category_description();
        if ( $desc ) {
            echo '<meta name="description" content="' . esc_attr( wp_strip_all_tags( $desc ) ) . '">' . "\n";
        }
    }
}
add_action( 'wp_head', 'techportal_meta_description', 1 );

/**
 * Add canonical URL
 */
function techportal_canonical() {
    if ( is_singular() ) {
        echo '<link rel="canonical" href="' . esc_url( get_permalink() ) . '">' . "\n";
    } elseif ( is_front_page() ) {
        echo '<link rel="canonical" href="' . esc_url( home_url( '/' ) ) . '">' . "\n";
    }
}
add_action( 'wp_head', 'techportal_canonical', 1 );
