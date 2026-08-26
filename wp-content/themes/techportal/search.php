<?php
/**
 * Search results template
 *
 * @package TechPortal
 */

get_header();
?>

<div class="tp-container" style="padding-top:var(--tp-space-8);padding-bottom:var(--tp-space-8);">
    <header class="tp-section-header">
        <h1 class="tp-section-header__title">
            <?php printf( esc_html__( 'Search Results for: %s', 'techportal' ), '<span style="color:var(--tp-accent);">' . get_search_query() . '</span>' ); ?>
        </h1>
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
                        <p class="tp-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 20 ) ); ?></p>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>

        <?php the_posts_pagination(); ?>

    <?php else : ?>
        <div class="tp-col-12" style="text-align:center;padding:var(--tp-space-16) 0;">
            <p style="color:var(--tp-text-secondary);">No results found for "<?php echo esc_html( get_search_query() ); ?>".</p>
        </div>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
