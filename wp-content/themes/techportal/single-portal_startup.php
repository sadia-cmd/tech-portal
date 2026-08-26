<?php
/**
 * Single Startup Profile Template
 *
 * Displays a company profile page — NOT a news article layout.
 * Shows company header, description, founders, details sidebar,
 * related news, and related episodes.
 *
 * @package TechPortal
 */

get_header();
?>

<?php while ( have_posts() ) : the_post();

// Get meta fields
$funding_stage = get_post_meta( get_the_ID(), 'funding_stage', true );
$founded_year  = get_post_meta( get_the_ID(), 'founded_year', true );
$location      = get_post_meta( get_the_ID(), 'location', true );
$website       = get_post_meta( get_the_ID(), 'website', true );
$industry      = get_post_meta( get_the_ID(), 'industry', true );

// Get taxonomy terms
$stages = get_the_terms( get_the_ID(), 'startup_stage' );
$stage_name = ( $stages && ! is_wp_error( $stages ) ) ? $stages[0]->name : '';

// Query founders associated with this startup
$founder_args = array(
    'post_type'      => 'portal_founder',
    'post_status'    => 'publish',
    'posts_per_page' => 6,
    'meta_query'     => array(
        array(
            'key'   => '_startup_id',
            'value' => get_the_ID(),
        ),
    ),
);
$founder_query = new WP_Query( $founder_args );

// Query related articles
$article_args = array(
    'post_type'      => array( 'post', 'portal_article' ),
    'post_status'    => 'publish',
    'posts_per_page' => 4,
    'orderby'        => 'date',
    'order'          => 'DESC',
    'meta_query'     => array(
        array(
            'key'     => '_related_startup',
            'value'   => get_the_ID(),
            'compare' => '=',
        ),
    ),
);
$article_query = new WP_Query( $article_args );

// If no meta-based relation, try category/tag based relation
if ( ! $article_query->have_posts() ) {
    $startup_name = get_the_title();
    $article_args_alt = array(
        'post_type'      => array( 'post', 'portal_article' ),
        'post_status'    => 'publish',
        'posts_per_page' => 4,
        'orderby'        => 'date',
        'order'          => 'DESC',
        's'              => $startup_name,
        'post__not_in'   => array( get_the_ID() ),
    );
    $article_query = new WP_Query( $article_args_alt );
}

// Query related episodes
$episode_args = array(
    'post_type'      => 'portal_episode',
    'post_status'    => 'publish',
    'posts_per_page' => 3,
    'orderby'        => 'date',
    'order'          => 'DESC',
    'meta_query'     => array(
        array(
            'key'     => '_related_startup',
            'value'   => get_the_ID(),
            'compare' => '=',
        ),
    ),
);
$episode_query = new WP_Query( $episode_args );
?>

<!-- Company Header -->
<section style="background:var(--tp-bg-secondary);padding:var(--tp-space-10) 0 var(--tp-space-8);">
    <div class="tp-container">
        <div class="tp-grid" style="align-items:center;gap:var(--tp-space-8);">
            <!-- Logo Area -->
            <div style="grid-column:span 2;display:flex;align-items:center;justify-content:center;">
                <?php if ( has_post_thumbnail() ) : ?>
                    <div style="width:120px;height:120px;border-radius:var(--tp-radius-lg);overflow:hidden;background:var(--tp-white);border:1px solid var(--tp-border);display:flex;align-items:center;justify-content:center;">
                        <?php the_post_thumbnail( 'techportal-compact', array(
                            'style' => 'width:100%;height:100%;object-fit:cover;',
                        ) ); ?>
                    </div>
                <?php else : ?>
                    <div style="width:120px;height:120px;border-radius:var(--tp-radius-lg);background:linear-gradient(135deg,var(--tp-purple),var(--tp-blue));display:flex;align-items:center;justify-content:center;">
                        <span style="font-size:var(--tp-text-3xl);color:#fff;font-weight:800;">
                            <?php echo esc_html( mb_substr( get_the_title(), 0, 2 ) ); ?>
                        </span>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Company Info -->
            <div style="grid-column:span 10;">
                <div style="display:flex;align-items:center;gap:var(--tp-space-3);flex-wrap:wrap;margin-bottom:var(--tp-space-2);">
                    <?php if ( $stage_name ) : ?>
                        <span class="tp-category-label tp-category-label--purple">
                            <?php echo esc_html( $stage_name ); ?>
                        </span>
                    <?php endif; ?>
                    <?php if ( $funding_stage ) : ?>
                        <span class="tp-category-label">
                            <?php echo esc_html( $funding_stage ); ?>
                        </span>
                    <?php endif; ?>
                </div>

                <h1 style="font-size:var(--tp-text-4xl);margin-bottom:var(--tp-space-3);line-height:var(--tp-leading-tight);">
                    <?php the_title(); ?>
                </h1>

                <div style="display:flex;align-items:center;gap:var(--tp-space-6);flex-wrap:wrap;font-size:var(--tp-text-sm);color:var(--tp-text-secondary);">
                    <?php if ( $location ) : ?>
                        <span style="display:flex;align-items:center;gap:6px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                            <?php echo esc_html( $location ); ?>
                        </span>
                    <?php endif; ?>
                    <?php if ( $industry ) : ?>
                        <span style="display:flex;align-items:center;gap:6px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                            </svg>
                            <?php echo esc_html( $industry ); ?>
                        </span>
                    <?php endif; ?>
                    <?php if ( $website ) : ?>
                        <a href="<?php echo esc_url( $website ); ?>" target="_blank" rel="noopener noreferrer" style="display:flex;align-items:center;gap:6px;color:var(--tp-accent);">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <line x1="2" y1="12" x2="22" y2="12"/>
                                <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                            </svg>
                            Visit Website
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Main Content + Sidebar -->
<section style="padding:var(--tp-space-8) 0;">
    <div class="tp-container">
        <div class="tp-grid">
            <!-- Main Content -->
            <div style="grid-column:span 8;">
                <!-- Company Description -->
                <div style="margin-bottom:var(--tp-space-8);">
                    <div class="tp-section-header">
                        <h2 class="tp-section-header__title">About <?php echo esc_html( get_the_title() ); ?></h2>
                    </div>
                    <div style="font-size:var(--tp-text-base);line-height:var(--tp-leading-relaxed);color:var(--tp-text-primary);">
                        <?php the_content(); ?>
                    </div>
                </div>

                <!-- Founders Section -->
                <?php if ( $founder_query->have_posts() ) : ?>
                <div style="margin-bottom:var(--tp-space-8);">
                    <div class="tp-section-header">
                        <h2 class="tp-section-header__title">Founders</h2>
                    </div>
                    <div class="tp-grid">
                        <?php while ( $founder_query->have_posts() ) : $founder_query->the_post(); ?>
                        <div class="tp-card tp-card--compact">
                            <?php if ( has_post_thumbnail() ) : ?>
                                <div class="tp-card__image">
                                    <a href="<?php the_permalink(); ?>">
                                        <?php the_post_thumbnail( 'techportal-compact', array( 'loading' => 'lazy' ) ); ?>
                                    </a>
                                </div>
                            <?php else : ?>
                                <div class="tp-card__image" style="background:linear-gradient(135deg,var(--tp-purple),var(--tp-blue));display:flex;align-items:center;justify-content:center;">
                                    <span style="font-size:var(--tp-text-xl);color:#fff;font-weight:700;">
                                        <?php echo esc_html( mb_substr( get_the_title(), 0, 1 ) ); ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                            <div class="tp-card__body">
                                <h3 class="tp-card__title" style="margin-bottom:var(--tp-space-1);">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h3>
                                <p class="tp-card__excerpt"><?php echo esc_html( wp_trim_excerpt() ); ?></p>
                            </div>
                        </div>
                        <?php endwhile; wp_reset_postdata(); ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Founder Interviews (from articles mentioning this startup) -->
                <?php
                $interview_args = array(
                    'post_type'      => array( 'post', 'portal_article' ),
                    'post_status'    => 'publish',
                    'posts_per_page' => 3,
                    'orderby'        => 'date',
                    'order'          => 'DESC',
                    's'              => get_the_title() . ' founder interview',
                );
                $interview_query = new WP_Query( $interview_args );
                if ( $interview_query->have_posts() ) :
                ?>
                <div style="margin-bottom:var(--tp-space-8);">
                    <div class="tp-section-header">
                        <h2 class="tp-section-header__title">Founder Interviews</h2>
                    </div>
                    <div class="tp-grid">
                        <?php while ( $interview_query->have_posts() ) : $interview_query->the_post(); ?>
                        <article class="tp-card tp-card--standard">
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
                        <?php endwhile; wp_reset_postdata(); ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Latest News About This Startup -->
                <?php if ( $article_query->have_posts() ) : ?>
                <div style="margin-bottom:var(--tp-space-8);">
                    <div class="tp-section-header">
                        <h2 class="tp-section-header__title">Latest News</h2>
                    </div>
                    <div class="tp-grid">
                        <?php while ( $article_query->have_posts() ) : $article_query->the_post(); ?>
                        <article class="tp-card tp-card--standard">
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
                        <?php endwhile; wp_reset_postdata(); ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Related Episodes -->
                <?php if ( $episode_query->have_posts() ) : ?>
                <div style="margin-bottom:var(--tp-space-8);">
                    <div class="tp-section-header">
                        <h2 class="tp-section-header__title">📺 Related Episodes</h2>
                    </div>
                    <div class="tp-grid">
                        <?php while ( $episode_query->have_posts() ) : $episode_query->the_post(); ?>
                        <article class="tp-card tp-card--standard" style="background:var(--tp-soft-black);color:#fff;">
                            <?php if ( has_post_thumbnail() ) : ?>
                                <div class="tp-card__image">
                                    <a href="<?php the_permalink(); ?>">
                                        <?php the_post_thumbnail( 'techportal-card', array( 'loading' => 'lazy' ) ); ?>
                                    </a>
                                </div>
                            <?php else : ?>
                                <div class="tp-card__image" style="background:linear-gradient(135deg,#1a1a2e,#16213e);display:flex;align-items:center;justify-content:center;">
                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.3)" stroke-width="1.5">
                                        <polygon points="5 3 19 12 5 21 5 3"/>
                                    </svg>
                                </div>
                            <?php endif; ?>
                            <div class="tp-card__body">
                                <h3 class="tp-card__title" style="color:#fff;">
                                    <a href="<?php the_permalink(); ?>" style="color:#fff;"><?php the_title(); ?></a>
                                </h3>
                                <p class="tp-card__excerpt" style="color:rgba(255,255,255,0.6);">
                                    <?php echo esc_html( wp_trim_excerpt() ); ?>
                                </p>
                                <div style="font-size:var(--tp-text-xs);color:rgba(255,255,255,0.4);margin-top:auto;">
                                    <?php echo esc_html( get_the_date( 'M j, Y' ) ); ?>
                                </div>
                            </div>
                        </article>
                        <?php endwhile; wp_reset_postdata(); ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar: Company Details -->
            <aside style="grid-column:span 4;">
                <div style="position:sticky;top:120px;background:var(--tp-bg-secondary);border-radius:var(--tp-radius-lg);padding:var(--tp-space-6);border:1px solid var(--tp-border);">
                    <h3 style="font-size:var(--tp-text-lg);font-weight:var(--tp-weight-bold);margin-bottom:var(--tp-space-4);padding-bottom:var(--tp-space-3);border-bottom:2px solid var(--tp-border);">
                        Company Details
                    </h3>

                    <dl style="display:flex;flex-direction:column;gap:var(--tp-space-4);">
                        <?php if ( $founded_year ) : ?>
                        <div>
                            <dt style="font-size:var(--tp-text-xs);text-transform:uppercase;letter-spacing:0.08em;color:var(--tp-muted);font-weight:var(--tp-weight-semibold);margin-bottom:var(--tp-space-1);">Founded</dt>
                            <dd style="font-size:var(--tp-text-sm);font-weight:var(--tp-weight-medium);color:var(--tp-text-primary);"><?php echo esc_html( $founded_year ); ?></dd>
                        </div>
                        <?php endif; ?>

                        <?php if ( $stage_name ) : ?>
                        <div>
                            <dt style="font-size:var(--tp-text-xs);text-transform:uppercase;letter-spacing:0.08em;color:var(--tp-muted);font-weight:var(--tp-weight-semibold);margin-bottom:var(--tp-space-1);">Stage</dt>
                            <dd style="font-size:var(--tp-text-sm);font-weight:var(--tp-weight-medium);color:var(--tp-text-primary);"><?php echo esc_html( $stage_name ); ?></dd>
                        </div>
                        <?php endif; ?>

                        <?php if ( $industry ) : ?>
                        <div>
                            <dt style="font-size:var(--tp-text-xs);text-transform:uppercase;letter-spacing:0.08em;color:var(--tp-muted);font-weight:var(--tp-weight-semibold);margin-bottom:var(--tp-space-1);">Industry</dt>
                            <dd style="font-size:var(--tp-text-sm);font-weight:var(--tp-weight-medium);color:var(--tp-text-primary);"><?php echo esc_html( $industry ); ?></dd>
                        </div>
                        <?php endif; ?>

                        <?php if ( $location ) : ?>
                        <div>
                            <dt style="font-size:var(--tp-text-xs);text-transform:uppercase;letter-spacing:0.08em;color:var(--tp-muted);font-weight:var(--tp-weight-semibold);margin-bottom:var(--tp-space-1);">Location</dt>
                            <dd style="font-size:var(--tp-text-sm);font-weight:var(--tp-weight-medium);color:var(--tp-text-primary);"><?php echo esc_html( $location ); ?></dd>
                        </div>
                        <?php endif; ?>

                        <?php if ( $website ) : ?>
                        <div>
                            <dt style="font-size:var(--tp-text-xs);text-transform:uppercase;letter-spacing:0.08em;color:var(--tp-muted);font-weight:var(--tp-weight-semibold);margin-bottom:var(--tp-space-1);">Website</dt>
                            <dd>
                                <a href="<?php echo esc_url( $website ); ?>" target="_blank" rel="noopener noreferrer" style="font-size:var(--tp-text-sm);font-weight:var(--tp-weight-medium);color:var(--tp-accent);display:flex;align-items:center;gap:6px;">
                                    <?php echo esc_html( wp_parse_url( $website, PHP_URL_HOST ) ); ?>
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                                        <polyline points="15 3 21 3 21 9"/>
                                        <line x1="10" y1="14" x2="21" y2="3"/>
                                    </svg>
                                </a>
                            </dd>
                        </div>
                        <?php endif; ?>
                    </dl>

                    <?php if ( $funding_stage ) : ?>
                    <div style="margin-top:var(--tp-space-5);padding-top:var(--tp-space-4);border-top:1px solid var(--tp-border);">
                        <div style="font-size:var(--tp-text-xs);text-transform:uppercase;letter-spacing:0.08em;color:var(--tp-muted);font-weight:var(--tp-weight-semibold);margin-bottom:var(--tp-space-1);">Funding Stage</div>
                        <div style="font-size:var(--tp-text-sm);font-weight:var(--tp-weight-bold);color:var(--tp-accent);"><?php echo esc_html( $funding_stage ); ?></div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Related Startups -->
                <?php
                $related_startup_args = array(
                    'post_type'      => 'portal_startup',
                    'post_status'    => 'publish',
                    'posts_per_page' => 3,
                    'post__not_in'   => array( get_the_ID() ),
                    'orderby'        => 'rand',
                );
                if ( $stages && ! is_wp_error( $stages ) ) {
                    $related_startup_args['tax_query'] = array(
                        array(
                            'taxonomy' => 'startup_stage',
                            'field'    => 'term_id',
                            'terms'    => $stages[0]->term_id,
                        ),
                    );
                }
                $related_startup_query = new WP_Query( $related_startup_args );
                if ( $related_startup_query->have_posts() ) :
                ?>
                <div style="margin-top:var(--tp-space-6);">
                    <h4 style="font-size:var(--tp-text-sm);font-weight:var(--tp-weight-semibold);text-transform:uppercase;letter-spacing:0.05em;color:var(--tp-muted);margin-bottom:var(--tp-space-3);">Similar Startups</h4>
                    <?php while ( $related_startup_query->have_posts() ) : $related_startup_query->the_post(); ?>
                    <div style="padding:var(--tp-space-3) 0;border-bottom:1px solid var(--tp-border);">
                        <a href="<?php the_permalink(); ?>" style="font-size:var(--tp-text-sm);font-weight:var(--tp-weight-medium);color:var(--tp-text-primary);display:block;line-height:var(--tp-leading-snug);">
                            <?php the_title(); ?>
                        </a>
                        <?php
                        $rs_stages = get_the_terms( get_the_ID(), 'startup_stage' );
                        if ( $rs_stages && ! is_wp_error( $rs_stages ) ) :
                        ?>
                            <span style="font-size:var(--tp-text-xs);color:var(--tp-muted);"><?php echo esc_html( $rs_stages[0]->name ); ?></span>
                        <?php endif; ?>
                    </div>
                    <?php endwhile; wp_reset_postdata(); ?>
                </div>
                <?php endif; ?>
            </aside>
        </div>
    </div>
</section>

<?php endwhile; ?>

<?php get_footer(); ?>
