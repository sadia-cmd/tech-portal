<?php
/**
 * Front Page Template — Tech Portal Homepage
 *
 * Editorial hierarchy: Breaking → Hero → Top Stories → Editor's Picks →
 * Latest News → Trending → Startups → Watch → AI & Cloud →
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

$ai_args = array(
    'post_type'      => array( 'post', 'portal_article' ),
    'post_status'    => 'publish',
    'posts_per_page' => 4,
    'category_name'  => 'ai-cloud',
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
// HERO + TOP STORIES
// ============================================
if ( $hero ) :
?>
<section class="tp-hero">
    <div class="tp-container tp-hero__grid">
        <div class="tp-hero__main">
            <?php if ( has_post_thumbnail( $hero->ID ) ) : ?>
                <div class="tp-hero__image">
                    <a href="<?php echo esc_url( get_permalink( $hero->ID ) ); ?>">
                        <?php echo get_the_post_thumbnail( $hero->ID, 'techportal-hero', array( 'loading' => false, 'style' => 'width:100%;height:100%;object-fit:cover;border-radius:var(--tp-radius-lg);' ) ); ?>
                    </a>
                </div>
            <?php else : ?>
                <div class="tp-hero__image" style="background:linear-gradient(135deg,var(--tp-accent),#0ea5e9);border-radius:var(--tp-radius-lg);"></div>
            <?php endif; ?>
            <div class="tp-hero__overlay">
                <a href="<?php echo esc_url( get_category_link( get_post_meta( $hero->ID, '_category_id', true ) ?: 0 ) ); ?>" class="tp-badge tp-badge--primary">
                    <?php echo esc_html( get_the_category( $hero->ID )[0]->name ?? 'News' ); ?>
                </a>
                <h1 class="tp-hero__title">
                    <a href="<?php echo esc_url( get_permalink( $hero->ID ) ); ?>"><?php echo esc_html( $hero->post_title ); ?></a>
                </h1>
                <p class="tp-hero__excerpt"><?php echo esc_html( wp_trim_words( $hero->post_content, 25 ) ); ?></p>
                <div class="tp-hero__meta">
                    <span>TechPortal Editorial</span>
                    <span>•</span>
                    <span><?php echo esc_html( get_the_date( 'M j, Y', $hero->ID ) ); ?></span>
                    <span>•</span>
                    <span><?php echo esc_html( tp_estimate_read_time( $hero->ID ) ); ?> min read</span>
                </div>
            </div>
        </div>

        <div class="tp-hero__sidebar">
            <h2 class="tp-hero__sidebar-title">Top Stories</h2>
            <?php if ( $top_query->have_posts() ) : ?>
                <?php while ( $top_query->have_posts() ) : $top_query->the_post(); ?>
                    <a href="<?php the_permalink(); ?>" class="tp-hero__sidebar-item">
                        <span class="tp-hero__sidebar-cat"><?php echo esc_html( get_the_category()[0]->name ?? 'News' ); ?></span>
                        <span class="tp-hero__sidebar-title-link"><?php the_title(); ?></span>
                        <span class="tp-hero__sidebar-meta"><?php echo esc_html( tp_estimate_read_time() ); ?> min read</span>
                    </a>
                <?php endwhile; wp_reset_postdata(); ?>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php
// ============================================
// LATEST NEWS
// ============================================
if ( $latest_query->have_posts() ) :
?>
<section style="padding:var(--tp-space-8) 0;">
    <div class="tp-container">
        <div class="tp-section-header">
            <h2 class="tp-section-header__title">Latest News</h2>
        </div>
        <div class="tp-grid tp-grid--news">
            <?php while ( $latest_query->have_posts() ) : $latest_query->the_post(); ?>
                <article class="tp-card tp-card--horizontal">
                    <?php if ( has_post_thumbnail() ) : ?>
                        <div class="tp-card__image tp-card__image--sm">
                            <a href="<?php the_permalink(); ?>">
                                <?php the_post_thumbnail( 'techportal-card', array( 'loading' => 'lazy' ) ); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                    <div class="tp-card__body">
                        <span class="tp-card__category"><?php echo esc_html( get_the_category()[0]->name ?? 'News' ); ?></span>
                        <h3 class="tp-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                        <?php techportal_post_meta(); ?>
                    </div>
                </article>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php
// ============================================
// TRENDING
// ============================================
if ( ! empty( $trending ) ) :
?>
<section style="padding:var(--tp-space-8) 0;background:var(--tp-bg-secondary);border-top:1px solid var(--tp-border);border-bottom:1px solid var(--tp-border);">
    <div class="tp-container">
        <div class="tp-section-header">
            <h2 class="tp-section-header__title">Trending</h2>
        </div>
        <div class="tp-trending-grid">
            <?php foreach ( $trending as $idx => $tp ) : ?>
                <a href="<?php echo esc_url( get_permalink( $tp->ID ) ); ?>" class="tp-trending-item">
                    <span class="tp-trending-number"><?php echo str_pad( $idx + 1, 2, '0', STR_PAD_LEFT ); ?></span>
                    <div>
                        <span class="tp-trending-cat"><?php echo esc_html( get_the_category( $tp->ID )[0]->name ?? 'News' ); ?></span>
                        <span class="tp-trending-title"><?php echo esc_html( $tp->post_title ); ?></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php
// ============================================
// STARTUP ECOSYSTEM
// ============================================
if ( $startup_query->have_posts() ) :
?>
<section style="padding:var(--tp-space-8) 0;">
    <div class="tp-container">
        <div class="tp-section-header">
            <h2 class="tp-section-header__title"><svg class="tp-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"/><path d="M12 15l-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"/><path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"/></svg>Startup Ecosystem</h2>
            <a href="<?php echo esc_url( home_url( '/startup/' ) ); ?>" class="tp-section-header__link">All Startups →</a>
        </div>
        <div class="tp-grid tp-grid--startups">
            <?php while ( $startup_query->have_posts() ) : $startup_query->the_post(); ?>
                <article class="tp-card tp-card--startup">
                    <?php if ( has_post_thumbnail() ) : ?>
                        <div class="tp-card__image">
                            <a href="<?php the_permalink(); ?>">
                                <?php the_post_thumbnail( 'techportal-card', array( 'loading' => 'lazy' ) ); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                    <div class="tp-card__body">
                        <span class="tp-card__category"><?php $sm = tp_get_startup_meta( get_the_ID() ); echo esc_html( $sm['industry'] ); ?></span>
                        <h3 class="tp-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                        <p class="tp-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 20 ) ); ?></p>
                        <div class="tp-card__meta">
                            <span><?php echo esc_html( $sm['funding_stage'] ); ?></span>
                            <span>•</span>
                            <span>📍 <?php echo esc_html( $sm['location'] ); ?></span>
                        </div>
                    </div>
                </article>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php
// ============================================
// WATCH — YOUTUBE VIDEO SECTION
// ============================================
?>
<section class="tp-watch">
    <div class="tp-container">
        <div class="tp-section-header tp-section-header--inverse">
            <h2 class="tp-section-header__title"><svg class="tp-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="6" width="20" height="14" rx="2"/><path d="M8 21h8M7 3l5 3 5-3"/></svg>Watch</h2>
            <a href="<?php echo esc_url( home_url( '/watch/' ) ); ?>" class="tp-section-header__link">All Videos →</a>
        </div>

        <?php
        // Featured video — latest episode with a YouTube ID
        $featured_args = array(
            'post_type'      => 'portal_episode',
            'post_status'    => 'publish',
            'posts_per_page' => 1,
            'meta_key'       => 'youtube_video_id',
            'meta_compare'   => '!=',
            'meta_value'     => '',
            'orderby'        => 'date',
            'order'          => 'DESC',
        );
        $featured_query = new WP_Query( $featured_args );
        if ( $featured_query->have_posts() ) : $featured_query->the_post();
            $feat_yt_id   = get_post_meta( get_the_ID(), 'youtube_video_id', true );
            $feat_show    = get_post_meta( get_the_ID(), '_tp_episode_show_name', true );
            $feat_guest   = get_post_meta( get_the_ID(), 'guest_name', true );
            $feat_live    = get_post_meta( get_the_ID(), 'youtube_is_live', true );
        ?>
        <div class="tp-watch__featured">
            <!-- Main Player -->
            <div class="tp-watch__player">
                <?php if ( $feat_yt_id ) : ?>
                    <iframe
                        src="https://www.youtube.com/embed/<?php echo esc_attr( $feat_yt_id ); ?>?rel=0&modestbranding=1&autoplay=0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen
                        loading="lazy"
                        title="<?php echo esc_attr( get_the_title() ); ?>"
                    ></iframe>
                <?php else : ?>
                    <div class="tp-watch__player-placeholder">
                        <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.2)" stroke-width="1"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                    </div>
                <?php endif; ?>
                <?php if ( $feat_live ) : ?>
                    <div class="tp-watch__live-badge">
                        <span class="tp-watch__live-dot"></span>
                        <span class="tp-watch__live-label">LIVE</span>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Playlist Sidebar -->
            <div class="tp-watch__sidebar">
                <div class="tp-watch__sidebar-head">
                    <h3>Now Playing</h3>
                    <a href="<?php the_permalink(); ?>">Watch Full →</a>
                </div>
                <div class="tp-watch__now-playing">
                    <?php if ( $feat_show ) : ?><div class="tp-watch__now-playing-show"><?php echo esc_html( $feat_show ); ?></div><?php endif; ?>
                    <h4 class="tp-watch__now-playing-title"><?php the_title(); ?></h4>
                    <?php if ( $feat_guest ) : ?><div class="tp-watch__now-playing-guest">Guest: <?php echo esc_html( $feat_guest ); ?></div><?php endif; ?>
                </div>
                <div class="tp-watch__playlist">
                <?php
                $playlist_args = array(
                    'post_type'      => 'portal_episode',
                    'post_status'    => 'publish',
                    'posts_per_page' => 8,
                    'meta_key'       => 'youtube_video_id',
                    'meta_compare'   => '!=',
                    'meta_value'     => '',
                    'orderby'        => 'date',
                    'order'          => 'DESC',
                    'post__not_in'   => array( get_the_ID() ),
                );
                $playlist_query = new WP_Query( $playlist_args );
                $pl_idx = 2;
                if ( $playlist_query->have_posts() ) :
                    while ( $playlist_query->have_posts() ) : $playlist_query->the_post();
                        $pl_yt_id  = get_post_meta( get_the_ID(), 'youtube_video_id', true );
                        $pl_show   = get_post_meta( get_the_ID(), '_tp_episode_show_name', true );
                        $pl_guest  = get_post_meta( get_the_ID(), 'guest_name', true );
                ?>
                    <a href="<?php echo esc_url( home_url( '/watch/?v=' . $pl_yt_id ) ); ?>" class="tp-watch__playlist-item">
                        <span class="tp-watch__playlist-num"><?php echo $pl_idx++; ?></span>
                        <div class="tp-watch__playlist-body">
                            <div class="tp-watch__playlist-show"><?php echo esc_html( $pl_show ?: 'Episode' ); ?></div>
                            <div class="tp-watch__playlist-title"><?php the_title(); ?></div>
                            <?php if ( $pl_guest ) : ?><div class="tp-watch__playlist-guest"><?php echo esc_html( $pl_guest ); ?></div><?php endif; ?>
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
        <?php wp_reset_postdata(); endif; ?>

        <!-- Video Grid -->
        <?php
        $video_args = array(
            'post_type'      => 'portal_episode',
            'post_status'    => 'publish',
            'posts_per_page' => 8,
            'meta_key'       => 'youtube_video_id',
            'meta_compare'   => '!=',
            'meta_value'     => '',
            'orderby'        => 'date',
            'order'          => 'DESC',
        );
        $video_query = new WP_Query( $video_args );
        if ( $video_query->have_posts() ) :
        ?>
        <div class="tp-watch__grid">
            <?php
            while ( $video_query->have_posts() ) : $video_query->the_post();
                $v_yt_id  = get_post_meta( get_the_ID(), 'youtube_video_id', true );
                $v_show   = get_post_meta( get_the_ID(), '_tp_episode_show_name', true );
                $v_guest  = get_post_meta( get_the_ID(), 'guest_name', true );
                $thumb    = get_the_post_thumbnail_url( get_the_ID(), 'medium' );
                $yt_thumb = $v_yt_id ? "https://img.youtube.com/vi/{$v_yt_id}/hqdefault.jpg" : '';
                $img_url  = $thumb ?: $yt_thumb;
            ?>
            <a href="<?php echo esc_url( home_url( '/watch/?v=' . $v_yt_id ) ); ?>" class="tp-video-card">
                <div class="tp-video-card__thumb">
                    <?php if ( $img_url ) : ?>
                        <img src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" loading="lazy" />
                    <?php else : ?>
                        <div class="tp-video-card__thumb-placeholder">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.2)" stroke-width="1"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                        </div>
                    <?php endif; ?>
                    <!-- Play Button Overlay -->
                    <div class="tp-video-card__play">
                        <div class="tp-video-card__play-btn">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="#fff"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                        </div>
                    </div>
                </div>
                <div class="tp-video-card__body">
                    <?php if ( $v_show ) : ?><div class="tp-video-card__show"><?php echo esc_html( $v_show ); ?></div><?php endif; ?>
                    <h3 class="tp-video-card__title"><?php the_title(); ?></h3>
                    <?php if ( $v_guest ) : ?><div class="tp-video-card__guest">Guest: <?php echo esc_html( $v_guest ); ?></div><?php endif; ?>
                    <div class="tp-video-card__date"><?php echo esc_html( get_the_date( 'M j, Y' ) ); ?></div>
                </div>
            </a>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
        <?php endif; ?>
    </div>
</section>


<?php
// ============================================
// AI & CLOUD + CYBERSECURITY
// ============================================
if ( $ai_query->have_posts() || $cyber_query->have_posts() ) :
?>
<section style="padding:var(--tp-space-8) 0;">
    <div class="tp-container">
        <div class="tp-split-grid">
            <?php if ( $ai_query->have_posts() ) : ?>
            <div>
                <div class="tp-section-header">
                    <h2 class="tp-section-header__title"><svg class="tp-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="7" width="16" height="12" rx="2"/><path d="M9 3v4M15 3v4M8 12h.01M16 12h.01M9 16h6"/></svg>AI &amp; Cloud</h2>
                    <a href="<?php echo esc_url( home_url( '/category/ai-cloud/' ) ); ?>" class="tp-section-header__link">More →</a>
                </div>
                <?php while ( $ai_query->have_posts() ) : $ai_query->the_post(); ?>
                    <article class="tp-card tp-card--compact">
                        <div class="tp-card__body">
                            <h3 class="tp-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                            <div class="tp-card__meta"><span><?php echo esc_html( get_the_date( 'M j' ) ); ?></span></div>
                        </div>
                    </article>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
            <?php endif; ?>

            <?php if ( $cyber_query->have_posts() ) : ?>
            <div>
                <div class="tp-section-header">
                    <h2 class="tp-section-header__title"><svg class="tp-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2 4 6v6c0 5 3.4 8.7 8 10 4.6-1.3 8-5 8-10V6l-8-4z"/></svg>Cybersecurity</h2>
                    <a href="<?php echo esc_url( home_url( '/category/cybersecurity/' ) ); ?>" class="tp-section-header__link">More →</a>
                </div>
                <?php while ( $cyber_query->have_posts() ) : $cyber_query->the_post(); ?>
                    <article class="tp-card tp-card--compact">
                        <div class="tp-card__body">
                            <h3 class="tp-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                            <div class="tp-card__meta"><span><?php echo esc_html( get_the_date( 'M j' ) ); ?></span></div>
                        </div>
                    </article>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php
// ============================================
// SPONSORS
// ============================================
if ( $sponsors ) :
?>
<section style="padding:var(--tp-space-8) 0;background:var(--tp-bg-secondary);border-top:1px solid var(--tp-border);border-bottom:1px solid var(--tp-border);">
    <div class="tp-container">
        <div class="tp-section-header">
            <h2 class="tp-section-header__title">Partners</h2>
        </div>
        <div class="tp-partners-row">
            <?php foreach ( $sponsors as $sponsor ) :
                $sponsor_url  = get_post_meta( $sponsor->ID, '_tp_sponsor_url', true );
                $sponsor_label = get_post_meta( $sponsor->ID, '_tp_sponsor_label', true );
                $link_tag = $sponsor_url ? '<a href="' . esc_url( $sponsor_url ) . '" target="_blank" rel="noopener sponsored">' : '';
                $link_end = $sponsor_url ? '</a>' : '';
            ?>
                <div class="tp-partner">
                    <?php echo $link_tag; ?>
                        <?php if ( has_post_thumbnail( $sponsor->ID ) ) : ?>
                            <?php echo get_the_post_thumbnail( $sponsor->ID, 'medium' ); ?>
                        <?php else : ?>
                            <span class="tp-partner__fallback"><?php echo esc_html( $sponsor->post_title ); ?></span>
                        <?php endif; ?>
                    <?php echo $link_end; ?>
                    <?php if ( $sponsor_label ) : ?>
                        <div class="tp-partner__label"><?php echo esc_html( $sponsor_label ); ?></div>
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
<section class="tp-newsletter-cta">
    <div class="tp-container tp-newsletter-cta__inner">
        <h2 class="tp-newsletter-cta__title">Stay Ahead in Tech</h2>
        <p class="tp-newsletter-cta__desc">
            Get Pakistan's top technology news, startup insights, and industry analysis delivered to your inbox every morning.
        </p>
        <form class="tp-newsletter-form tp-newsletter__form" action="#" method="post">
            <input type="email" name="email" placeholder="your@email.com" required class="tp-newsletter__input" />
            <button type="submit" class="tp-newsletter__btn tp-newsletter__btn--light">Subscribe</button>
        </form>
        <p class="tp-newsletter-cta__note">No spam. Unsubscribe anytime.</p>
    </div>
</section>

<?php get_footer(); ?>
