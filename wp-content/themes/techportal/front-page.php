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
                        <div class="tp-card__meta">
                            <span><?php echo esc_html( get_the_date( 'M j, Y' ) ); ?></span>
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
            <h2 class="tp-section-header__title">🚀 Startup Ecosystem</h2>
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
<section style="padding:var(--tp-space-10) 0;background:var(--tp-ink);color:#fff;">
    <div class="tp-container">
        <div class="tp-section-header" style="border-bottom-color:rgba(255,255,255,0.15);">
            <h2 class="tp-section-header__title" style="color:#fff;">📺 Watch</h2>
            <a href="<?php echo esc_url( home_url( '/watch/' ) ); ?>" class="tp-section-header__link" style="color:var(--tp-accent);">All Videos →</a>
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
        <div style="display:grid;grid-template-columns:1fr 380px;gap:var(--tp-space-6);margin-bottom:var(--tp-space-8);">
            <!-- Main Player -->
            <div style="position:relative;width:100%;aspect-ratio:16/9;background:#000;border-radius:var(--tp-radius-lg);overflow:hidden;">
                <?php if ( $feat_yt_id ) : ?>
                    <iframe
                        src="https://www.youtube.com/embed/<?php echo esc_attr( $feat_yt_id ); ?>?rel=0&modestbranding=1&autoplay=0"
                        style="position:absolute;top:0;left:0;width:100%;height:100%;border:0;"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen
                        loading="lazy"
                        title="<?php echo esc_attr( get_the_title() ); ?>"
                    ></iframe>
                <?php else : ?>
                    <div style="display:flex;align-items:center;justify-content:center;width:100%;height:100%;background:linear-gradient(135deg,#1a1a2e,#16213e);">
                        <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.2)" stroke-width="1"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                    </div>
                <?php endif; ?>
                <?php if ( $feat_live ) : ?>
                    <div style="position:absolute;top:var(--tp-space-3);left:var(--tp-space-3);display:flex;align-items:center;gap:6px;background:rgba(220,38,38,0.9);padding:4px 12px;border-radius:var(--tp-radius-full);z-index:2;">
                        <span style="width:8px;height:8px;border-radius:50%;background:#fff;animation:tp-pulse 2s ease-in-out infinite;"></span>
                        <span style="font-size:var(--tp-text-xs);font-weight:700;color:#fff;">LIVE</span>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Playlist Sidebar -->
            <div style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);border-radius:var(--tp-radius-lg);overflow:hidden;">
                <div style="padding:var(--tp-space-4);border-bottom:1px solid rgba(255,255,255,0.08);display:flex;align-items:center;justify-content:space-between;">
                    <h3 style="font-size:var(--tp-text-sm);font-weight:700;color:#fff;margin:0;">Now Playing</h3>
                    <a href="<?php the_permalink(); ?>" style="font-size:var(--tp-text-xs);color:var(--tp-accent);text-decoration:none;">Watch Full →</a>
                </div>
                <div style="padding:var(--tp-space-3) var(--tp-space-4);border-bottom:1px solid rgba(255,255,255,0.08);">
                    <?php if ( $feat_show ) : ?><div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:var(--tp-accent);margin-bottom:4px;"><?php echo esc_html( $feat_show ); ?></div><?php endif; ?>
                    <h4 style="font-size:var(--tp-text-sm);color:#fff;margin:0 0 4px;line-height:1.4;"><?php the_title(); ?></h4>
                    <?php if ( $feat_guest ) : ?><div style="font-size:var(--tp-text-xs);color:rgba(255,255,255,0.5);">Guest: <?php echo esc_html( $feat_guest ); ?></div><?php endif; ?>
                </div>
                <div style="overflow-y:auto;max-height:340px;">
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
                    <a href="<?php echo esc_url( home_url( '/watch/?v=' . $pl_yt_id ) ); ?>" style="display:flex;gap:var(--tp-space-3);padding:var(--tp-space-3) var(--tp-space-4);text-decoration:none;color:inherit;transition:background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.05)'" onmouseout="this.style.background='transparent'">
                        <span style="font-size:var(--tp-text-xs);color:rgba(255,255,255,0.3);min-width:20px;padding-top:2px;font-weight:600;"><?php echo $pl_idx++; ?></span>
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:var(--tp-text-xs);color:rgba(255,255,255,0.5);margin-bottom:2px;"><?php echo esc_html( $pl_show ?: 'Episode' ); ?></div>
                            <div style="font-size:var(--tp-text-xs);color:#fff;line-height:1.4;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php the_title(); ?></div>
                            <?php if ( $pl_guest ) : ?><div style="font-size:10px;color:rgba(255,255,255,0.35);margin-top:2px;"><?php echo esc_html( $pl_guest ); ?></div><?php endif; ?>
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
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:var(--tp-space-5);">
            <?php
            while ( $video_query->have_posts() ) : $video_query->the_post();
                $v_yt_id  = get_post_meta( get_the_ID(), 'youtube_video_id', true );
                $v_show   = get_post_meta( get_the_ID(), '_tp_episode_show_name', true );
                $v_guest  = get_post_meta( get_the_ID(), 'guest_name', true );
                $thumb    = get_the_post_thumbnail_url( get_the_ID(), 'medium' );
                $yt_thumb = $v_yt_id ? "https://img.youtube.com/vi/{$v_yt_id}/hqdefault.jpg" : '';
                $img_url  = $thumb ?: $yt_thumb;
            ?>
            <article style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);border-radius:var(--tp-radius-md);overflow:hidden;transition:transform 0.2s,box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 24px rgba(0,0,0,0.3)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
                <a href="<?php echo esc_url( home_url( '/watch/?v=' . $v_yt_id ) ); ?>" style="text-decoration:none;color:inherit;display:block;">
                    <div style="position:relative;width:100%;aspect-ratio:16/9;background:#111;overflow:hidden;">
                        <?php if ( $img_url ) : ?>
                            <img src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" style="width:100%;height:100%;object-fit:cover;" loading="lazy" />
                        <?php else : ?>
                            <div style="width:100%;height:100%;background:linear-gradient(135deg,#1a1a2e,#16213e);display:flex;align-items:center;justify-content:center;">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.2)" stroke-width="1"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                            </div>
                        <?php endif; ?>
                        <!-- Play Button Overlay -->
                        <div style="position:absolute;top:0;left:0;width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0.2);transition:background 0.2s;" onmouseover="this.style.background='rgba(0,0,0,0.4)'" onmouseout="this.style.background='rgba(0,0,0,0.2)'">
                            <div style="width:48px;height:48px;border-radius:50%;background:rgba(255,0,0,0.9);display:flex;align-items:center;justify-content:center;box-shadow:0 2px 12px rgba(255,0,0,0.4);">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="#fff"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                            </div>
                        </div>
                    </div>
                    <div style="padding:var(--tp-space-3);">
                        <?php if ( $v_show ) : ?><div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:var(--tp-accent);margin-bottom:4px;"><?php echo esc_html( $v_show ); ?></div><?php endif; ?>
                        <h3 style="font-size:var(--tp-text-sm);font-weight:600;color:#fff;line-height:1.4;margin:0 0 4px;overflow:hidden;text-overflow:ellipsis;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;"><?php the_title(); ?></h3>
                        <?php if ( $v_guest ) : ?><div style="font-size:var(--tp-text-xs);color:rgba(255,255,255,0.5);">Guest: <?php echo esc_html( $v_guest ); ?></div><?php endif; ?>
                        <div style="font-size:var(--tp-text-xs);color:rgba(255,255,255,0.35);margin-top:4px;"><?php echo esc_html( get_the_date( 'M j, Y' ) ); ?></div>
                    </div>
                </a>
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
if ( $ai_query->have_posts() || $cyber_query->have_posts() ) :
?>
<section style="padding:var(--tp-space-8) 0;">
    <div class="tp-container">
        <div class="tp-grid" style="grid-template-columns:1fr 1fr;gap:var(--tp-space-8);">
            <?php if ( $ai_query->have_posts() ) : ?>
            <div>
                <div class="tp-section-header">
                    <h2 class="tp-section-header__title">🤖 AI & Cloud</h2>
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
                    <h2 class="tp-section-header__title">🔒 Cybersecurity</h2>
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
        <div style="display:flex;align-items:center;justify-content:center;gap:var(--tp-space-8);flex-wrap:wrap;padding:var(--tp-space-4) 0;">
            <?php foreach ( $sponsors as $sponsor ) :
                $sponsor_url  = get_post_meta( $sponsor->ID, '_tp_sponsor_url', true );
                $sponsor_label = get_post_meta( $sponsor->ID, '_tp_sponsor_label', true );
                $link_tag = $sponsor_url ? '<a href="' . esc_url( $sponsor_url ) . '" target="_blank" rel="noopener sponsored">' : '';
                $link_end = $sponsor_url ? '</a>' : '';
            ?>
                <div style="text-align:center;opacity:0.6;transition:opacity 0.2s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.6'">
                    <?php echo $link_tag; ?>
                        <?php if ( has_post_thumbnail( $sponsor->ID ) ) : ?>
                            <?php echo get_the_post_thumbnail( $sponsor->ID, 'medium', array( 'style' => 'max-height:40px;width:auto;filter:grayscale(100%);opacity:0.7;' ) ); ?>
                        <?php else : ?>
                            <span style="font-size:var(--tp-text-sm);font-weight:700;color:var(--tp-text-muted);"><?php echo esc_html( $sponsor->post_title ); ?></span>
                        <?php endif; ?>
                    <?php echo $link_end; ?>
                    <?php if ( $sponsor_label ) : ?>
                        <div style="font-size:10px;color:var(--tp-text-muted);margin-top:4px;"><?php echo esc_html( $sponsor_label ); ?></div>
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
<section style="padding:var(--tp-space-10) 0;background:var(--tp-accent);color:#fff;text-align:center;">
    <div class="tp-container" style="max-width:600px;">
        <h2 style="font-size:var(--tp-text-3xl);font-weight:var(--tp-weight-extrabold);margin-bottom:var(--tp-space-3);">Stay Ahead in Tech</h2>
        <p style="color:rgba(255,255,255,0.85);margin-bottom:var(--tp-space-6);font-size:var(--tp-text-lg);line-height:1.6;">
            Get Pakistan's top technology news, startup insights, and industry analysis delivered to your inbox every morning.
        </p>
        <form class="tp-newsletter-form" style="display:flex;gap:var(--tp-space-3);max-width:460px;margin:0 auto;" action="#" method="post">
            <input type="email" name="email" placeholder="your@email.com" required style="flex:1;padding:var(--tp-space-3) var(--tp-space-4);border-radius:var(--tp-radius-md);border:2px solid rgba(255,255,255,0.3);background:rgba(255,255,255,0.15);color:#fff;font-size:var(--tp-text-base);outline:none;" />
            <button type="submit" style="padding:var(--tp-space-3) var(--tp-space-6);background:#fff;color:var(--tp-accent);border:none;border-radius:var(--tp-radius-md);font-weight:var(--tp-weight-bold);font-size:var(--tp-text-base);cursor:pointer;">Subscribe</button>
        </form>
        <p style="font-size:var(--tp-text-xs);color:rgba(255,255,255,0.6);margin-top:var(--tp-space-3);">No spam. Unsubscribe anytime.</p>
    </div>
</section>

<?php get_footer(); ?>
