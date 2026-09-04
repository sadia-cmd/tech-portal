<?php
/**
 * Template Tags for Tech Portal
 *
 * @package TechPortal
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Print the category label
 */
function techportal_category_label( $post_id = null ) {
    if ( ! $post_id ) $post_id = get_the_ID();
    $categories = get_the_category( $post_id );
    if ( empty( $categories ) ) return;
    $cat = $categories[0];
    printf(
        '<a href="%s" class="tp-category-label">%s</a>',
        esc_url( get_category_link( $cat->term_id ) ),
        esc_html( $cat->name )
    );
}

/**
 * Print the post meta (author, date, reading time)
 */
function techportal_post_meta( $post_id = null ) {
    if ( ! $post_id ) $post_id = get_the_ID();
    $author = get_the_author_meta( 'display_name', get_post_field( 'post_author', $post_id ) );
    $date   = get_the_date( 'M j, Y', $post_id );

    // Estimate reading time
    $content    = get_post_field( 'post_content', $post_id );
    $word_count = str_word_count( strip_tags( $content ) );
    $minutes    = max( 1, ceil( $word_count / 250 ) );

    // Comment count — only shown when there's something to show
    $comments      = (int) get_comments_number( $post_id );
    $comment_html  = '';
    if ( $comments > 0 ) {
        $comment_html = sprintf(
            '<span>•</span><span>%d comment%s</span>',
            $comments,
            $comments === 1 ? '' : 's'
        );
    }

    printf(
        '<div class="tp-card__meta">
            <span>%s</span>
            <span>•</span>
            <span>%s</span>
            <span>•</span>
            <span>%d min read</span>
            %s
        </div>',
        esc_html( $author ),
        esc_html( $date ),
        $minutes,
        $comment_html
    );
}

/**
 * Print the post meta for articles (inline format)
 *
 * Correct pattern: By AUTHOR NAME | Published DATE | Updated DATE | READ TIME
 * Does NOT render "By" without an author name.
 */
function techportal_article_meta( $post_id = null ) {
    if ( ! $post_id ) $post_id = get_the_ID();
    $author = get_the_author_meta( 'display_name', get_post_field( 'post_author', $post_id ) );
    $date   = get_the_date( 'F j, Y', $post_id );
    $content    = get_post_field( 'post_content', $post_id );
    $word_count = str_word_count( strip_tags( $content ) );
    $minutes    = max( 1, ceil( $word_count / 250 ) );

    $modified = get_the_modified_date( 'F j, Y', $post_id );
    $show_updated = ( $modified !== $date );

    echo '<div class="tp-article__meta">';
    if ( ! empty( $author ) ) {
        printf( '<span>By <strong>%s</strong></span>', esc_html( $author ) );
    }
    printf( '<span>Published %s</span>', esc_html( $date ) );
    if ( $show_updated ) {
        printf( '<span>Updated %s</span>', esc_html( $modified ) );
    }
    printf( '<span>%d min read</span>', $minutes );
    echo '</div>';
}

/**
 * Get formatted time ago
 */
function techportal_time_ago( $post_id = null ) {
    if ( ! $post_id ) $post_id = get_the_ID();
    $post_date = get_the_date( 'U', $post_id );
    $diff = current_time( 'timestamp' ) - $post_date;
    
    if ( $diff < 60 ) return 'just now';
    if ( $diff < 3600 ) return floor( $diff / 60 ) . 'm ago';
    if ( $diff < 86400 ) return floor( $diff / 3600 ) . 'h ago';
    if ( $diff < 604800 ) return floor( $diff / 86400 ) . 'd ago';
    return get_the_date( 'M j', $post_id );
}

/**
 * Check if post is recent (within 24 hours)
 */
function techportal_is_recent( $post_id = null ) {
    if ( ! $post_id ) $post_id = get_the_ID();
    $post_date = get_the_date( 'U', $post_id );
    return ( current_time( 'timestamp' ) - $post_date ) < 86400;
}
