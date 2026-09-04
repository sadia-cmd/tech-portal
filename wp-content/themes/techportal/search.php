<?php
/**
 * Search results template — Enhanced search with type filters
 *
 * @package TechPortal
 */

get_header();
$search_query = get_search_query();
?>

<section class="tp-search-hero">
    <div class="tp-container">
        <h1 class="tp-search-hero__title">Search</h1>
        <form role="search" method="get" class="tp-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
            <input type="search" class="tp-search-form__input" placeholder="Search articles, startups, episodes…" value="<?php echo esc_attr( $search_query ); ?>" name="s" aria-label="Search" autofocus>
            <button type="submit" class="tp-btn tp-btn--primary">Search</button>
        </form>
        <?php if ( ! empty( $search_query ) ) : ?>
            <div class="tp-search-type-filters" id="tp-search-filters">
                <button class="tp-search-type-btn active" data-type="all">All</button>
                <button class="tp-search-type-btn" data-type="post">Articles</button>
                <button class="tp-search-type-btn" data-type="portal_article">Longform</button>
                <button class="tp-search-type-btn" data-type="portal_startup">Startups</button>
                <button class="tp-search-type-btn" data-type="portal_episode">Episodes</button>
                <button class="tp-search-type-btn" data-type="portal_press">Press</button>
            </div>
        <?php endif; ?>
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
                    '<span class="tp-search-results__query">' . esc_html( $search_query ) . '</span>'
                );
                ?>
            </p>
        </div>

        <div class="tp-grid tp-search-grid">
            <?php while ( have_posts() ) : the_post(); ?>
                <article class="tp-card tp-card--standard" data-post-type="<?php echo esc_attr( get_post_type() ); ?>" <?php post_class(); ?>>
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
                            $type_labels = array(
                                'post' => 'Article', 'portal_article' => 'Longform',
                                'portal_startup' => 'Startup', 'portal_episode' => 'Episode',
                                'portal_press' => 'Press Release',
                            );
                            $type_label = $type_labels[ get_post_type() ] ?? ( $post_type_obj ? $post_type_obj->labels->singular_name : '' );
                            if ( $type_label ) : ?>
                                <span class="tp-search-type-label"><?php echo esc_html( $type_label ); ?></span>
                            <?php endif; ?>
                            <?php techportal_category_label(); ?>
                        </div>
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
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            </div>
            <h2 class="tp-empty-state__title">No Results Found</h2>
            <p class="tp-empty-state__desc">
                Sorry, nothing matched "<?php echo esc_html( $search_query ); ?>". Try a different search term.
            </p>
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="tp-btn tp-btn--primary" style="margin-top:var(--tp-space-4);">Back to Homepage</a>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var filters = document.getElementById('tp-search-filters');
    if (!filters) return;
    var btns = filters.querySelectorAll('.tp-search-type-btn');
    var cards = document.querySelectorAll('.tp-search-grid .tp-card');

    btns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            btns.forEach(function(b) { b.classList.remove('active'); });
            btn.classList.add('active');
            var type = btn.dataset.type;
            cards.forEach(function(card) {
                if (type === 'all' || card.dataset.postType === type) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });
});
</script>

<?php get_footer(); ?>
