<?php
/**
 * Front Page Template — Tech Portal Homepage
 *
 * Editorial hierarchy: Breaking → Hero → Top Stories → Editor's Picks →
 * Latest News → Trending → Startups → Web Channel → AI & Cloud →
 * Cybersecurity → Sponsors → Newsletter → Footer
 *
 * @package TechPortal
 */

get_header();
?>

<?php
// ---- Editorial Queries using new functions ----
$hero = tp_get_hero_story();
$featured = tp_get_featured_posts( 4 );
$breaking = tp_get_breaking_posts( 5 );
$trending = tp_get_trending_posts( 8 );

$latest_args = array(
    'post_type'      => array( 'post', 'portal_article' ),
    'post_status'    => 'publish',
    'posts_per_page' => 12,
    'orderby'        => 'date',
    'order'          => 'DESC',
);
$latest_query = new WP_Query( $latest_args );

$hero_id = $hero ? $hero->ID : 0;

$top_args = array(
    'post_type'      => array( 'post', 'portal_article' ),
    'post_status'    => 'publish',
    'posts_per_page' => 5,
    'orderby'        => 'date',
    'order'          => 'DESC',
    'post__not_in'   => $hero_id ? array( $hero_id ) : array(),
);
$top_query = new WP_Query( $top_args );

$startup_args = array(
    'post_type'      => 'portal_startup',
    'post_status'    => 'publish',
    'posts_per_page' => 6,
    'orderby'        => 'date',
    'order'          => 'DESC',
);
$startup_query = new WP_Query( $startup_args );

$episode_args = array(
    'post_type'      => 'portal_episode',
    'post_status'    => 'publish',
    'posts_per_page' => 4,
    'orderby'        => 'date',
    'order'          => 'DESC',
);
$episode_query = new WP_Query( $episode_args );

$ai_args = array(
    'post_type'      => array( 'post', 'portal_article' ),
    'post_status'    => 'publish',
    'posts_per_page' => 4,
    'tax_query'      => array(
        array(
            'taxonomy' => 'portal_topic',
            'field'    => 'slug',
            'terms'    => array( 'ai', 'cloud' ),
        ),
    ),
    'orderby'        => 'date',
    'order'          => 'DESC',
);
$ai_query = new WP_Query( $ai_args );

$cyber_args = array(
    'post_type'      => array( 'post', 'portal_article' ),
    'post_status'    => 'publish',
    'posts_per_page' => 4,
    'category_name'  => 'cybersecurity',
    'orderby'        => 'date',
    'order'          => 'DESC',
);
$cyber_query = new WP_Query( $cyber_args );

// Sponsors
$sponsors = tp_get_active_sponsors();
?>

<?php
// ============================================
// BREAKING NEWS TICKER
// ============================================
if ( ! empty( $breaking ) ) :
?>
<section class="tp-ticker">
    <div class="tp-ticker__inner tp-container">
        <div class="tp-ticker__label">Breaking</div>
        <div class="tp-ticker__content">
            <div class="tp-ticker__scroll">
                <?php foreach ( $breaking as $bp ) : ?>
                    <a href="<?php echo esc_url( get_permalink( $bp->ID ) ); ?>" class="tp-ticker__item">
                        <?php echo esc_html( $bp->post_title ); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>


<?php
// ============================================
// HERO STORY + TOP STORIES
// ============================================
if ( $hero ) :
?>
<section class="tp-hero-section" style="background:var(--tp-ink);padding:var(--tp-space-6) 0;">
    <div class="tp-container">
        <div class="tp-grid">
            <!-- Hero: Major Story (8 cols) -->
            <div class="tp-col-8" style="position:relative;border-radius:var(--tp-radius-lg);overflow:hidden;">
                <?php if ( has_post_thumbnail( $hero->ID ) ) : ?>
                    <a href="<?php echo esc_url( get_permalink( $hero->ID ) ); ?>" style="display:block;">
                        <?php echo get_the_post_thumbnail( $hero->ID, 'techportal-hero', array(
                            'style' => 'width:100%;height:100%;object-fit:cover;min-height:420px;',
                            'loading' => false,
                        ) ); ?>
                    </a>
                <?php else : ?>
                    <div style="background:linear-gradient(135deg,var(--tp-purple),var(--tp-blue));min-height:420px;border-radius:var(--tp-radius-lg);"></div>
                <?php endif; ?>
                <div style="position:absolute;bottom:0;left:0;right:0;padding:var(--tp-space-8) var(--tp-space-6);background:linear-gradient(transparent,rgba(0,0,0,0.85));">
                    <div style="margin-bottom:var(--tp-space-2);">
                        <?php techportal_category_label( $hero->ID ); ?>
                    </div>
                    <h1 style="font-size:var(--tp-text-4xl);color:#fff;margin-bottom:var(--tp-space-3);line-height:var(--tp-leading-tight);">
                        <a href="<?php echo esc_url( get_permalink( $hero->ID ) ); ?>" style="color:#fff;text-decoration:none;">
                            <?php echo esc_html( $hero->post_title ); ?>
                        </a>
                    </h1>
                    <p style="color:rgba(255,255,255,0.8);font-size:var(--tp-text-base);max-width:600px;line-height:1.6;">
                        <?php echo esc_html( wp_trim_words( $hero->post_content, 30 ) ); ?>
                    </p>
                    <div style="margin-top:var(--tp-space-3);color:rgba(255,255,255,0.6);font-size:var(--tp-text-sm);">
                        <?php
                        $author = get_the_author_meta( 'display_name', $hero->post_author );
                        $date = get_the_date( 'M j, Y', $hero->ID );
                        $minutes = Portal_Helpers::reading_time( $hero->post_content );
                        echo esc_html( "$author • $date • {$minutes} min read" );
                        ?>
                    </div>
                </div>
            </div>

            <!-- Side: Top Stories (4 cols) -->
            <div class="tp-col-4" style="display:flex;flex-direction:column;gap:var(--tp-space-3);">
                <div style="font-size:var(--tp-text-xs);font-weight:var(--tp-weight-bold);text-transform:uppercase;letter-spacing:0.08em;color:var(--tp-accent);padding-bottom:var(--tp-space-2);border-bottom:1px solid rgba(255,255,255,0.15);margin-bottom:var(--tp-space-1);">
                    Top Stories
                </div>
                <?php
                $side_count = 0;
                if ( $top_query->have_posts() ) :
                    while ( $top_query->have_posts() && $side_count < 4 ) : $top_query->the_post();
                        $side_count++;
                ?>
                <a href="<?php the_permalink(); ?>" style="display:block;padding:var(--tp-space-3);background:rgba(255,255,255,0.05);border-radius:var(--tp-radius-md);text-decoration:none;border:1px solid rgba(255,255,255,0.08);transition:background 0.2s;">
                    <div style="font-size:var(--tp-text-xs);color:var(--tp-accent);font-weight:600;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:4px;">
                        <?php
                        $cats = get_the_category( get_the_ID() );
                        echo esc_html( $cats[0]->name ?? '' );
                        ?>
                    </div>
                    <div style="color:#fff;font-size:var(--tp-text-sm);font-weight:600;line-height:1.35;">
                        <?php the_title(); ?>
                    </div>
                    <div style="color:rgba(255,255,255,0.5);font-size:var(--tp-text-xs);margin-top:4px;">
                        <?php echo esc_html( Portal_Helpers::reading_time( get_the_content() ) ); ?> min read
                    </div>
                </a>
                <?php
                    endwhile;
                    wp_reset_postdata();
                endif;
                ?>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>


<?php
// ============================================
// EDITOR'S PICKS / CURATED
// ============================================
if ( ! empty( $featured ) ) :
?>
<section style="padding:var(--tp-space-10) 0;">
    <div class="tp-container">
        <div class="tp-section-header">
            <h2 class="tp-section-header__title">Editor's Picks</h2>
            <a href="<?php echo esc_url( home_url( '/category/pakistan-technology/' ) ); ?>" class="tp-section-header__link">View All →</a>
        </div>
        <div class="tp-grid">
            <?php
            $grid_count = 0;
            foreach ( $featured as $fp ) :
                setup_postdata( $fp );
                $grid_count++;
                $card_class = $grid_count <= 2 ? 'tp-card--feature' : 'tp-card--standard';
            ?>
            <article class="tp-card <?php echo esc_attr( $card_class ); ?>">
                <?php if ( has_post_thumbnail( $fp->ID ) ) : ?>
                    <div class="tp-card__image">
                        <a href="<?php echo esc_url( get_permalink( $fp->ID ) ); ?>">
                            <?php echo get_the_post_thumbnail( $fp->ID, 'techportal-card', array( 'loading' => 'lazy' ) ); ?>
                        </a>
                    </div>
                <?php endif; ?>
                <div class="tp-card__body">
                    <div class="tp-card__category"><?php techportal_category_label( $fp->ID ); ?></div>
                    <h3 class="tp-card__title">
                        <a href="<?php echo esc_url( get_permalink( $fp->ID ) ); ?>"><?php echo esc_html( $fp->post_title ); ?></a>
                    </h3>
                    <p class="tp-card__excerpt"><?php echo esc_html( wp_trim_words( $fp->post_content, 18 ) ); ?></p>
                    <?php techportal_post_meta( $fp->ID ); ?>
                </div>
            </article>
            <?php endforeach; wp_reset_postdata(); ?>
        </div>
    </div>
</section>
<?php endif; ?>


<?php
// ============================================
// LATEST NEWS + TRENDING
// ============================================
?>
<section style="padding:var(--tp-space-8) 0;background:var(--tp-bg-secondary);">
    <div class="tp-container">
        <div class="tp-grid">
            <!-- Latest News Feed (8 cols) -->
            <div class="tp-col-8">
                <div class="tp-section-header">
                    <h2 class="tp-section-header__title">Latest News</h2>
                </div>
                <?php
                if ( $latest_query->have_posts() ) :
                    while ( $latest_query->have_posts() ) : $latest_query->the_post();
                ?>
                <div class="tp-latest-item">
                    <span class="tp-latest-item__time"><?php echo esc_html( Portal_Helpers::reading_time( get_the_content() ) ); ?>m</span>
                    <div>
                        <div style="margin-bottom:2px;">
                            <span class="tp-category-label" style="font-size:0.625rem;padding:2px 6px;">
                                <?php
                                $cats = get_the_category( get_the_ID() );
                                echo esc_html( $cats[0]->name ?? 'News' );
                                ?>
                            </span>
                        </div>
                        <a href="<?php the_permalink(); ?>" class="tp-latest-item__title">
                            <?php the_title(); ?>
                        </a>
                    </div>
                </div>
                <?php
                    endwhile;
                    wp_reset_postdata();
                endif;
                ?>
            </div>

            <!-- Trending (4 cols) -->
            <div class="tp-col-4">
                <div class="tp-section-header">
                    <h2 class="tp-section-header__title">Trending</h2>
                </div>
                <?php
                if ( ! empty( $trending ) ) :
                    $rank = 1;
                    foreach ( $trending as $tp ) :
                        setup_postdata( $tp );
                ?>
                <div class="tp-trending-item">
                    <span class="tp-trending-item__rank"><?php echo esc_html( str_pad( $rank, 2, '0', STR_PAD_LEFT ) ); ?></span>
                    <div>
                        <a href="<?php echo esc_url( get_permalink( $tp->ID ) ); ?>" class="tp-latest-item__title" style="display:block;">
                            <?php echo esc_html( $tp->post_title ); ?>
                        </a>
                        <div style="font-size:var(--tp-text-xs);color:var(--tp-muted);margin-top:2px;">
                            <?php
                            $cats = get_the_category( $tp->ID );
                            echo esc_html( $cats[0]->name ?? '' );
                            ?>
                        </div>
                    </div>
                </div>
                <?php
                        $rank++;
                    endforeach;
                    wp_reset_postdata();
                endif;
                ?>
            </div>
        </div>
    </div>
</section>


<?php
// ============================================
// STARTUP ECOSYSTEM
// ============================================
if ( $startup_query->have_posts() ) :
?>
<section style="padding:var(--tp-space-10) 0;">
    <div class="tp-container">
        <div class="tp-section-header">
            <h2 class="tp-section-header__title">🚀 Startup Ecosystem</h2>
            <a href="<?php echo esc_url( home_url( '/startup/' ) ); ?>" class="tp-section-header__link">All Startups →</a>
        </div>
        <div class="tp-grid">
            <?php
            while ( $startup_query->have_posts() ) : $startup_query->the_post();
                $stages = get_the_terms( get_the_ID(), 'startup_stage' );
                $stage_name = $stages ? $stages[0]->name : '';
                $meta = tp_get_startup_meta( get_the_ID() );
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
                    <p class="tp-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 15 ) ); ?></p>
                    <?php if ( ! empty( $meta['location'] ) ) : ?>
                        <div style="font-size:var(--tp-text-xs);color:var(--tp-text-secondary);margin-top:auto;padding-top:var(--tp-space-2);">
                            📍 <?php echo esc_html( $meta['location'] ); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </article>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
    </div>
</section>
<?php endif; ?>


<?php
// ============================================
// WEB CHANNEL / EPISODES
// ============================================
?>
<section style="padding:var(--tp-space-10) 0;background:var(--tp-ink);color:#fff;">
    <div class="tp-container">
        <div class="tp-section-header" style="border-bottom-color:rgba(255,255,255,0.15);">
            <h2 class="tp-section-header__title" style="color:#fff;">📺 Web Channel</h2>
            <a href="<?php echo esc_url( home_url( '/web-channel/' ) ); ?>" class="tp-section-header__link">Visit Web Channel →</a>
        </div>

        <!-- TechTalk Live Promo -->
        <div style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:var(--tp-radius-lg);padding:var(--tp-space-6);margin-bottom:var(--tp-space-8);display:flex;align-items:center;gap:var(--tp-space-6);">
            <div style="position:relative;width:100%;max-width:560px;aspect-ratio:16/9;background:#000;border-radius:var(--tp-radius-md);overflow:hidden;display:flex;align-items:center;justify-content:center;">
                <div style="color:rgba(255,255,255,0.3);font-size:var(--tp-text-lg);">Live Stream Area</div>
                <div style="position:absolute;top:var(--tp-space-3);left:var(--tp-space-3);display:flex;align-items:center;gap:6px;background:rgba(0,0,0,0.7);padding:4px 10px;border-radius:var(--tp-radius-full);">
                    <span style="width:8px;height:8px;border-radius:50%;background:#DC2626;animation:tp-pulse 2s ease-in-out infinite;"></span>
                    <span style="font-size:var(--tp-text-xs);font-weight:600;color:#fff;text-transform:uppercase;">Live</span>
                </div>
            </div>
            <div style="flex:1;">
                <h3 style="font-size:var(--tp-text-2xl);margin-bottom:var(--tp-space-2);">TechTalk Live</h3>
                <p style="color:rgba(255,255,255,0.7);font-size:var(--tp-text-sm);margin-bottom:var(--tp-space-3);line-height:1.6;">
                    Join us every Friday at 8 PM PKT for live discussions on Pakistan's tech ecosystem, startup funding, and emerging technologies.
                </p>
                <span style="font-size:var(--tp-text-xs);color:rgba(255,255,255,0.5);">Every Friday • 8:00 PM PKT</span>
            </div>
        </div>

        <!-- Episodes Grid -->
        <?php if ( $episode_query->have_posts() ) : ?>
        <div class="tp-grid">
            <?php
            while ( $episode_query->have_posts() ) : $episode_query->the_post();
                $ep_meta = tp_get_episode_meta( get_the_ID() );
            ?>
            <article class="tp-card tp-card--standard" style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);">
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
                    <?php if ( ! empty( $ep_meta['show_name'] ) ) : ?>
                        <div style="font-size:var(--tp-text-xs);font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:var(--tp-accent);margin-bottom:var(--tp-space-1);">
                            <?php echo esc_html( $ep_meta['show_name'] ); ?>
                        </div>
                    <?php endif; ?>
                    <h3 class="tp-card__title" style="color:#fff;">
                        <a href="<?php the_permalink(); ?>" style="color:#fff;"><?php the_title(); ?></a>
                    </h3>
                    <?php if ( ! empty( $ep_meta['guest_name'] ) ) : ?>
                        <div style="font-size:var(--tp-text-xs);color:rgba(255,255,255,0.6);margin-bottom:var(--tp-space-2);">
                            Guest: <?php echo esc_html( $ep_meta['guest_name'] ); ?>
                        </div>
                    <?php endif; ?>
                    <p class="tp-card__excerpt" style="color:rgba(255,255,255,0.6);"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 15 ) ); ?></p>
                    <div style="font-size:var(--tp-text-xs);color:rgba(255,255,255,0.4);margin-top:auto;">
                        <?php echo esc_html( get_the_date( 'M j, Y' ) ); ?>
                    </div>
                </div>
            </article>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
        <?php endif; ?>
    </div>
</section>


<?php
// ============================================
// AI & CLOUD + CYBERSECURITY
// ============================================
?>
<section style="padding:var(--tp-space-10) 0;">
    <div class="tp-container">
        <div class="tp-grid">
            <!-- AI & Cloud -->
            <div class="tp-col-6">
                <div class="tp-section-header">
                    <h2 class="tp-section-header__title">🤖 AI & Cloud</h2>
                    <a href="<?php echo esc_url( home_url( '/category/ai-cloud/' ) ); ?>" class="tp-section-header__link">More →</a>
                </div>
                <?php
                if ( $ai_query->have_posts() ) :
                    $ai_count = 0;
                    while ( $ai_query->have_posts() ) : $ai_query->the_post();
                        $ai_count++;
                        if ( $ai_count === 1 ) :
                ?>
                <article class="tp-card" style="margin-bottom:var(--tp-space-4);">
                    <?php if ( has_post_thumbnail() ) : ?>
                        <div class="tp-card__image">
                            <a href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'techportal-card', array( 'loading' => 'lazy' ) ); ?></a>
                        </div>
                    <?php endif; ?>
                    <div class="tp-card__body">
                        <h3 class="tp-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                        <p class="tp-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 15 ) ); ?></p>
                    </div>
                </article>
                <?php
                        else :
                ?>
                <div class="tp-latest-item">
                    <div>
                        <a href="<?php the_permalink(); ?>" class="tp-latest-item__title"><?php the_title(); ?></a>
                        <div style="font-size:var(--tp-text-xs);color:var(--tp-muted);"><?php echo esc_html( get_the_date( 'M j' ) ); ?></div>
                    </div>
                </div>
                <?php
                        endif;
                    endwhile;
                    wp_reset_postdata();
                endif;
                ?>
            </div>

            <!-- Cybersecurity -->
            <div class="tp-col-6">
                <div class="tp-section-header">
                    <h2 class="tp-section-header__title">🔒 Cybersecurity</h2>
                    <a href="<?php echo esc_url( home_url( '/category/cybersecurity/' ) ); ?>" class="tp-section-header__link">More →</a>
                </div>
                <?php
                if ( $cyber_query->have_posts() ) :
                    $cy_count = 0;
                    while ( $cyber_query->have_posts() ) : $cyber_query->the_post();
                        $cy_count++;
                        if ( $cy_count === 1 ) :
                ?>
                <article class="tp-card" style="margin-bottom:var(--tp-space-4);">
                    <?php if ( has_post_thumbnail() ) : ?>
                        <div class="tp-card__image">
                            <a href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'techportal-card', array( 'loading' => 'lazy' ) ); ?></a>
                        </div>
                    <?php endif; ?>
                    <div class="tp-card__body">
                        <h3 class="tp-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                        <p class="tp-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 15 ) ); ?></p>
                    </div>
                </article>
                <?php
                        else :
                ?>
                <div class="tp-latest-item">
                    <div>
                        <a href="<?php the_permalink(); ?>" class="tp-latest-item__title"><?php the_title(); ?></a>
                        <div style="font-size:var(--tp-text-xs);color:var(--tp-muted);"><?php echo esc_html( get_the_date( 'M j' ) ); ?></div>
                    </div>
                </div>
                <?php
                        endif;
                    endwhile;
                    wp_reset_postdata();
                endif;
                ?>
            </div>
        </div>
    </div>
</section>


<?php
// ============================================
// SPONSORS (only show if sponsors exist)
// ============================================
if ( ! empty( $ponsors ) ) :
?>
<section style="padding:var(--tp-space-8) 0;border-top:1px solid var(--tp-border);">
    <div class="tp-container">
        <div style="text-align:center;margin-bottom:var(--tp-space-4);">
            <span style="font-size:var(--tp-text-xs);text-transform:uppercase;letter-spacing:0.1em;color:var(--tp-text-secondary);font-weight:var(--tp-weight-semibold);">Sponsored</span>
        </div>
        <div class="tp-grid" style="justify-items:center;">
            <?php foreach ( $ponsors as $sponsor ) : ?>
                <div class="tp-sponsor-slot">
                    <?php if ( $sponsor['image'] ) : ?>
                        <a href="<?php echo esc_url( $sponsor['url'] ); ?>" target="_blank" rel="noopener">
                            <img src="<?php echo esc_url( $sponsor['image'] ); ?>" alt="<?php echo esc_attr( $sponsor['label'] ); ?>" style="max-height:60px;">
                        </a>
                    <?php else : ?>
                        <span class="tp-sponsor-slot__label"><?php echo esc_html( $sponsor['label'] ); ?></span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>


<?php
// ============================================
// NEWSLETTER CTA
// ============================================
?>
<section style="padding:var(--tp-space-10) 0;">
    <div class="tp-container">
        <div class="tp-newsletter">
            <h2 class="tp-newsletter__title">Stay Ahead in Tech</h2>
            <p class="tp-newsletter__desc">Get Pakistan's top technology news, startup insights, and industry analysis delivered to your inbox every morning.</p>
            <form class="tp-newsletter__form" data-newsletter="main">
                <input type="email" class="tp-newsletter__input" placeholder="your@email.com" required aria-label="Email address">
                <button type="submit" class="tp-newsletter__btn">Subscribe</button>
            </form>
            <p style="font-size:var(--tp-text-xs);color:rgba(255,255,255,0.4);margin-top:var(--tp-space-3);">No spam. Unsubscribe anytime.</p>
        </div>
    </div>
</section>


<?php
// Clean up queries
wp_reset_postdata();

get_footer();
