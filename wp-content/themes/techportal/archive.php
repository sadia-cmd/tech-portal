<?php
/**
 * Archive template — Professional editorial archives
 *
 * @package TechPortal
 */

get_header();

$is_category = is_category();
$archive_title = get_the_archive_title();
$archive_desc = get_the_archive_description();
?>

<section class="tp-archive-hero">
    <div class="tp-container">
        <div class="tp-archive-hero__content">
            <h1 class="tp-archive-hero__title"><?php echo esc_html( wp_strip_all_tags( $archive_title ) ); ?></h1>
            <?php if ( $archive_desc ) : ?>
                <p class="tp-archive-hero__desc"><?php echo wp_kses_post( $archive_desc ); ?></p>
            <?php endif; ?>
        </div>
    </div>
</section>

<div class="tp-container tp-archive-content">
    <?php if ( have_posts() ) : ?>
        <?php
        // First post as lead story
        if ( have_posts() ) :
            the_post();
        ?>
        <article class="tp-card tp-card--horizontal tp-archive-lead" <?php post_class(); ?>>
            <?php if ( has_post_thumbnail() ) : ?>
                <div class="tp-card__image">
                    <a href="<?php the_permalink(); ?>">
                        <?php the_post_thumbnail( 'techportal-card-horizontal', array( 'loading' => 'lazy' ) ); ?>
                    </a>
                </div>
            <?php endif; ?>
            <div class="tp-card__body">
                <div class="tp-card__category"><?php techportal_category_label(); ?></div>
                <h2 class="tp-card__title">
                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                </h2>
                <p class="tp-card__excerpt"><?php echo esc_html( wp_trim_excerpt() ); ?></p>
                <?php techportal_post_meta(); ?>
            </div>
        </article>
        <?php endif; ?>

        <!-- Supporting stories grid -->
        <div class="tp-section-header" style="margin-top:var(--tp-space-8);">
            <h2 class="tp-section-header__title">Latest Stories</h2>
        </div>
        <div class="tp-grid tp-archive-grid">
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
                        <div class="tp-card__category"><?php techportal_category_label(); ?></div>
                        <h3 class="tp-card__title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h3>
                        <p class="tp-card__excerpt"><?php echo esc_html( wp_trim_excerpt() ); ?></p>
                        <?php techportal_post_meta(); ?>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>

        <div class="tp-archive-pagination">
            <?php the_posts_pagination( array(
                'mid_size'  => 2,
                'prev_text' => '&laquo;',
                'next_text' => '&raquo;',
            ) ); ?>
        </div>

    <?php else : ?>
        <div class="tp-empty-state">
            <div class="tp-empty-state__icon">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
            </div>
            <h2 class="tp-empty-state__title">No Stories Yet</h2>
            <p class="tp-empty-state__desc">Content for this section is on its way. Check back soon.</p>
        </div>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
