<?php
/**
 * Startup archive template
 *
 * @package TechPortal
 */

get_header();
?>

<section style="padding:var(--tp-space-8) 0 var(--tp-space-4);">
    <div class="tp-container">
        <div class="tp-section-header">
            <h1 class="tp-section-header__title">🚀 Startup Ecosystem</h1>
        </div>
        <p style="color:var(--tp-text-secondary);max-width:600px;margin-bottom:var(--tp-space-6);">
            Discover Pakistan's most promising startups — from pre-seed to growth stage. Profiles, funding news, and founder stories.
        </p>
    </div>
</section>

<section style="padding:0 0 var(--tp-space-10);">
    <div class="tp-container">
        <?php if ( have_posts() ) : ?>
            <div class="tp-grid">
                <?php while ( have_posts() ) : the_post();
                    $stages = get_the_terms( get_the_ID(), 'startup_stage' );
                    $stage_name = $stages ? $stages[0]->name : '';
                ?>
                <article class="tp-card tp-card--standard">
                    <?php if ( has_post_thumbnail() ) : ?>
                        <div class="tp-card__image">
                            <a href="<?php the_permalink(); ?>">
                                <?php the_post_thumbnail( 'techportal-card', array( 'loading' => 'lazy' ) ); ?>
                            </a>
                        </div>
                    <?php else : ?>
                        <div class="tp-card__image" style="background:linear-gradient(135deg,var(--tp-purple),var(--tp-blue));display:flex;align-items:center;justify-content:center;">
                            <span style="font-size:var(--tp-text-3xl);color:#fff;font-weight:800;"><?php echo esc_html( mb_substr( get_the_title(), 0, 2 ) ); ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="tp-card__body">
                        <?php if ( $stage_name ) : ?>
                            <span class="tp-category-label tp-category-label--purple" style="margin-bottom:var(--tp-space-2);"><?php echo esc_html( $stage_name ); ?></span>
                        <?php endif; ?>
                        <h3 class="tp-card__title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h3>
                        <p class="tp-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 18 ) ); ?></p>
                        <?php techportal_post_meta(); ?>
                    </div>
                </article>
                <?php endwhile; ?>
            </div>

            <div style="margin-top:var(--tp-space-8);">
                <?php the_posts_pagination(); ?>
            </div>
        <?php else : ?>
            <div style="text-align:center;padding:var(--tp-space-16) 0;">
                <p style="color:var(--tp-text-secondary);">No startups found.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php get_footer(); ?>
