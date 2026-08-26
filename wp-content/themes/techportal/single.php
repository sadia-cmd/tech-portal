<?php
/**
 * Single post template
 *
 * @package TechPortal
 */

get_header();
?>

<?php while ( have_posts() ) : the_post(); ?>

<article class="tp-article" <?php post_class(); ?>>
    <header class="tp-article__header">
        <div class="tp-article__category">
            <?php techportal_category_label(); ?>
        </div>
        <h1 class="tp-article__title"><?php the_title(); ?></h1>
        <?php techportal_article_meta(); ?>
    </header>

    <?php if ( has_post_thumbnail() ) : ?>
        <figure style="margin-bottom:var(--tp-space-8);">
            <?php the_post_thumbnail( 'large', array( 'loading' => 'lazy', 'style' => 'border-radius:var(--tp-radius-lg);width:100%;' ) ); ?>
            <?php if ( get_the_post_thumbnail_caption() ) : ?>
                <figcaption><?php echo esc_html( get_the_post_thumbnail_caption() ); ?></figcaption>
            <?php endif; ?>
        </figure>
    <?php endif; ?>

    <div class="tp-article__content">
        <?php the_content(); ?>
    </div>

    <?php
    wp_link_pages( array(
        'before' => '<div class="page-links">Pages:',
        'after'  => '</div>',
    ) );
    ?>

    <?php if ( has_category() ) : ?>
        <div style="margin-top:var(--tp-space-8);padding-top:var(--tp-space-6);border-top:1px solid var(--tp-border);">
            <strong style="font-size:var(--tp-text-sm);">Tags:</strong>
            <?php the_tags( '<span style="display:inline-flex;gap:0.5rem;flex-wrap:wrap;margin-top:0.5rem;">', '</span> <span></span>', '</span>' ); ?>
        </div>
    <?php endif; ?>
</article>

<?php
// Previous/Next post navigation
the_post_navigation( array(
    'prev_text' => '<span style="font-size:var(--tp-text-xs);color:var(--tp-muted);display:block;">Previous</span>%title',
    'next_text' => '<span style="font-size:var(--tp-text-xs);color:var(--tp-muted);display:block;">Next</span>%title',
    'class'     => 'tp-article',
) );

// Comments
if ( comments_open() || get_comments_number() ) {
    comments_template();
}
?>

<?php endwhile; ?>

<?php get_footer(); ?>
