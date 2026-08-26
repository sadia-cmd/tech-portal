<?php
/**
 * Archive template
 *
 * @package TechPortal
 */

get_header();
?>

<div class="tp-container" style="padding-top:var(--tp-space-8);padding-bottom:var(--tp-space-8);">
    <header class="tp-section-header">
        <h1 class="tp-section-header__title">
            <?php the_archive_title(); ?>
        </h1>
        <?php the_archive_description( '<p style="color:var(--tp-text-secondary);margin-top:var(--tp-space-2);">', '</p>' ); ?>
    </header>

    <?php if ( have_posts() ) : ?>
        <div class="tp-grid">
            <?php while ( have_posts() ) : the_post(); ?>
                <article class="tp-card tp-card--standard" <?php post_class(); ?>>
                    <?php if ( has_post_thumbnail() ) : ?>
                        <div class="tp-card__image">
                            <a href="<?php the_permalink(); ?>">
                                <?php the_post_thumbnail( 'techportal-card', array( 'loading' => 'lazy' ) ); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                    <div class="tp-card__body">
                        <div class="tp-card__category">
                            <?php techportal_category_label(); ?>
                        </div>
                        <h3 class="tp-card__title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h3>
                        <?php techportal_post_meta(); ?>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>

        <?php the_posts_pagination(); ?>

    <?php else : ?>
        <div class="tp-col-12" style="text-align:center;padding:var(--tp-space-16) 0;">
            <p style="color:var(--tp-text-secondary);">No posts found in this archive.</p>
        </div>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
