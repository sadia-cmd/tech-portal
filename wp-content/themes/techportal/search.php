<?php
/**
 * Search results template — Full search UX
 *
 * @package TechPortal
 */

get_header();
?>

<section class="tp-search-hero">
    <div class="tp-container">
        <h1 class="tp-search-hero__title">Search</h1>
        <form role="search" method="get" class="tp-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
            <input type="search" class="tp-search-form__input" placeholder="Search articles, startups, episodes…" value="<?php echo esc_attr( get_search_query() ); ?>" name="s" aria-label="Search" autofocus>
            <button type="submit" class="tp-btn tp-btn--primary">Search</button>
        </form>
    </div>
</section>

<div class="tp-container tp-search-content">
    <?php if ( have_posts() ) : ?>
        <div class="tp-search-results__header">
            <p class="tp-search-results__count">
                <?php
                printf(
                    esc_html( _nx( '%s result for "%s"', '%s results for "%s"', $wp_query->found_posts, 'search results', 'techportal' ) ),
                    number_format_i18n( $wp_query->found_posts ),
                    '<span class="tp-search-results__query">' . esc_html( get_search_query() ) . '</span>'
                );
                ?>
            </p>
        </div>

        <div class="tp-grid tp-search-grid">
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
                            <?php
                            $post_type_obj = get_post_type_object( get_post_type() );
                            if ( $post_type_obj ) : ?>
                                <span class="tp-category-label"><?php echo esc_html( $post_type_obj->labels->singular_name ); ?></span>
                            <?php endif;
                            techportal_category_label();
                            ?>
                        </div>
                        <h3 class="tp-card__title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h3>
                        <p class="tp-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 20 ) ); ?></p>
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
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            </div>
            <h2 class="tp-empty-state__title">No Results Found</h2>
            <p class="tp-empty-state__desc">
                Sorry, nothing matched "<?php echo esc_html( get_search_query() ); ?>". Try a different search term.
            </p>
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="tp-btn tp-btn--primary" style="margin-top:var(--tp-space-4);">Back to Homepage</a>
        </div>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
