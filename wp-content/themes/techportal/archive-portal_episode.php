<?php
/**
 * Episode archive template
 *
 * @package TechPortal
 */

get_header();
?>

<section style="padding:var(--tp-space-8) 0 var(--tp-space-4);background:var(--tp-ink);color:#fff;">
    <div class="tp-container">
        <div class="tp-section-header" style="border-bottom-color:rgba(255,255,255,0.15);">
            <h1 class="tp-section-header__title" style="color:#fff;">📺 Web Channel</h1>
        </div>
        <p style="color:rgba(255,255,255,0.7);max-width:600px;margin-bottom:var(--tp-space-6);">
            Live shows, expert discussions, and in-depth analysis of Pakistan's technology landscape.
        </p>
    </div>
</section>

<section style="padding:var(--tp-space-8) 0 var(--tp-space-10);">
    <div class="tp-container">
        <?php if ( have_posts() ) : ?>
            <div class="tp-grid">
                <?php while ( have_posts() ) : the_post(); ?>
                <article class="tp-card tp-card--standard">
                    <?php if ( has_post_thumbnail() ) : ?>
                        <div class="tp-card__image">
                            <a href="<?php the_permalink(); ?>">
                                <?php the_post_thumbnail( 'techportal-card', array( 'loading' => 'lazy' ) ); ?>
                            </a>
                        </div>
                    <?php else : ?>
                        <div class="tp-card__image" style="background:linear-gradient(135deg,#1a1a2e,#16213e);display:flex;align-items:center;justify-content:center;">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.3)" stroke-width="1.5"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                        </div>
                    <?php endif; ?>
                    <div class="tp-card__body">
                        <h3 class="tp-card__title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h3>
                        <p class="tp-card__excerpt"><?php echo esc_html( wp_trim_excerpt() ); ?></p>
                        <div style="font-size:var(--tp-text-xs);color:var(--tp-muted);margin-top:auto;">
                            <?php echo esc_html( get_the_date( 'M j, Y' ) ); ?>
                        </div>
                    </div>
                </article>
                <?php endwhile; ?>
            </div>

            <div style="margin-top:var(--tp-space-8);">
                <?php the_posts_pagination(); ?>
            </div>
        <?php else : ?>
            <div style="text-align:center;padding:var(--tp-space-16) 0;">
                <p style="color:var(--tp-text-secondary);">No episodes found.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php get_footer(); ?>
