<?php
/**
 * Template Name: Web Channel
 *
 * Landing page template for the Web Channel video series.
 * Shows a featured hero area, show info, and episode grid.
 *
 * @package TechPortal
 */

get_header();
?>

<?php
// Query episodes for the grid
$episode_args = array(
    'post_type'      => 'portal_episode',
    'post_status'    => 'publish',
    'posts_per_page' => 12,
    'orderby'        => 'date',
    'order'          => 'DESC',
);
$episode_query = new WP_Query( $episode_args );

// Get the latest/featured episode for the hero
$featured_args = array(
    'post_type'      => 'portal_episode',
    'post_status'    => 'publish',
    'posts_per_page' => 1,
    'orderby'        => 'date',
    'order'          => 'DESC',
);
$featured_query = new WP_Query( $featured_args );
$featured_episode = $featured_query->have_posts() ? $featured_query->posts[0] : null;
?>

<!-- Hero / Featured Episode Area -->
<section style="background:var(--tp-ink);padding:var(--tp-space-10) 0;color:#fff;">
    <div class="tp-container">
        <?php if ( $featured_episode ) : ?>
            <?php
            $youtube_id  = get_post_meta( $featured_episode->ID, 'youtube_video_id', true );
            $guest_name  = get_post_meta( $featured_episode->ID, 'guest_name', true );
            $is_live     = get_post_meta( $featured_episode->ID, 'youtube_is_live', true );
            $is_upcoming = get_post_meta( $featured_episode->ID, 'youtube_is_upcoming', true );
            $duration    = get_post_meta( $featured_episode->ID, 'youtube_duration', true );
            $view_count  = get_post_meta( $featured_episode->ID, 'youtube_view_count', true );
            ?>

            <!-- 16:9 YouTube Player or Thumbnail -->
            <div style="position:relative;border-radius:var(--tp-radius-lg);overflow:hidden;margin-bottom:var(--tp-space-8);aspect-ratio:16/9;background:#000;">
                <?php if ( $youtube_id ) : ?>
                    <!-- YouTube iframe embed -->
                    <iframe
                        src="https://www.youtube.com/embed/<?php echo esc_attr( $youtube_id ); ?>?rel=0&modestbranding=1"
                        style="width:100%;height:100%;border:none;"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen
                        title="<?php echo esc_attr( $featured_episode->post_title ); ?>"
                    ></iframe>

                    <!-- LIVE Badge -->
                    <?php if ( $is_live ) : ?>
                        <div style="position:absolute;top:var(--tp-space-3);left:var(--tp-space-3);background:#c62828;color:#fff;padding:4px 12px;border-radius:var(--tp-radius-sm);font-size:var(--tp-text-xs);font-weight:700;text-transform:uppercase;letter-spacing:0.05em;display:flex;align-items:center;gap:6px;">
                            <span style="width:8px;height:8px;border-radius:50%;background:#fff;animation:blink 1s infinite;"></span>
                            LIVE
                        </div>
                        <style>@keyframes blink { 0%,100% { opacity:1; } 50% { opacity:0.3; } }</style>
                    <?php endif; ?>

                    <!-- UPCOMING Badge -->
                    <?php if ( $is_upcoming && ! $is_live ) : ?>
                        <div style="position:absolute;top:var(--tp-space-3);left:var(--tp-space-3);background:#e65100;color:#fff;padding:4px 12px;border-radius:var(--tp-radius-sm);font-size:var(--tp-text-xs);font-weight:700;text-transform:uppercase;letter-spacing:0.05em;">
                            🕐 UPCOMING
                        </div>
                    <?php endif; ?>

                    <!-- Duration Badge -->
                    <?php if ( $duration && ! $is_live && ! $is_upcoming ) : ?>
                        <div style="position:absolute;bottom:var(--tp-space-3);right:var(--tp-space-3);background:rgba(0,0,0,0.8);color:#fff;padding:4px 8px;border-radius:var(--tp-radius-sm);font-size:var(--tp-text-xs);">
                            <?php echo esc_html( $duration ); ?>
                        </div>
                    <?php endif; ?>
                <?php elseif ( has_post_thumbnail( $featured_episode->ID ) ) : ?>
                    <!-- Thumbnail fallback with play icon -->
                    <div style="position:relative;width:100%;height:100%;">
                        <?php echo get_the_post_thumbnail( $featured_episode->ID, 'techportal-hero', array(
                            'style'   => 'width:100%;height:100%;object-fit:cover;',
                            'loading' => false,
                        ) ); ?>
                        <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;">
                            <a href="<?php echo esc_url( get_permalink( $featured_episode->ID ) ); ?>" style="text-decoration:none;">
                                <div style="width:80px;height:80px;border-radius:50%;background:rgba(255,255,255,0.15);backdrop-filter:blur(8px);display:flex;align-items:center;justify-content:center;border:2px solid rgba(255,255,255,0.3);transition:transform 0.2s;">
                                    <svg width="36" height="36" viewBox="0 0 24 24" fill="#fff">
                                        <polygon points="5 3 19 12 5 21 5 3"/>
                                    </svg>
                                </div>
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Featured Episode Info -->
            <div class="tp-grid" style="align-items:start;">
                <div class="tp-col-8">
                    <h1 style="font-size:var(--tp-text-4xl);margin-bottom:var(--tp-space-3);line-height:var(--tp-leading-tight);">
                        <?php echo esc_html( $featured_episode->post_title ); ?>
                    </h1>
                    <?php if ( $guest_name ) : ?>
                        <p style="font-size:var(--tp-text-lg);color:rgba(255,255,255,0.8);margin-bottom:var(--tp-space-2);">
                            Guest: <?php echo esc_html( $guest_name ); ?>
                        </p>
                    <?php endif; ?>
                    <p style="font-size:var(--tp-text-base);color:rgba(255,255,255,0.6);max-width:600px;line-height:1.6;">
                        <?php echo esc_html( wp_trim_words( $featured_episode->post_content, 40 ) ); ?>
                    </p>
                </div>
                <div class="tp-col-4" style="display:flex;flex-direction:column;gap:var(--tp-space-3);">
                    <?php if ( $duration ) : ?>
                        <div style="font-size:var(--tp-text-sm);color:rgba(255,255,255,0.6);">
                            <strong style="color:rgba(255,255,255,0.9);">Duration:</strong>
                            <?php echo esc_html( $duration ); ?>
                        </div>
                    <?php endif; ?>
                    <?php if ( $view_count ) : ?>
                        <div style="font-size:var(--tp-text-sm);color:rgba(255,255,255,0.6);">
                            <strong style="color:rgba(255,255,255,0.9);">Views:</strong>
                            <?php echo number_format( $view_count ); ?>
                        </div>
                    <?php endif; ?>
                    <div style="font-size:var(--tp-text-sm);color:rgba(255,255,255,0.6);">
                        <strong style="color:rgba(255,255,255,0.9);">Published:</strong>
                        <?php echo esc_html( get_the_date( 'M j, Y', $featured_episode->ID ) ); ?>
                    </div>
                    <a href="<?php echo esc_url( get_permalink( $featured_episode->ID ) ); ?>" class="tp-btn tp-btn--primary" style="margin-top:var(--tp-space-2);">
                        Watch Episode
                    </a>
                </div>
            </div>
        <?php else : ?>
            <!-- Empty State Hero -->
            <div style="text-align:center;padding:var(--tp-space-16) 0;">
                <div style="width:120px;height:120px;margin:0 auto var(--tp-space-6);border-radius:50%;background:rgba(255,255,255,0.05);display:flex;align-items:center;justify-content:center;">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.3)" stroke-width="1.5">
                        <polygon points="5 3 19 12 5 21 5 3"/>
                    </svg>
                </div>
                <h1 style="font-size:var(--tp-text-3xl);margin-bottom:var(--tp-space-3);">📺 Web Channel</h1>
                <p style="font-size:var(--tp-text-lg);color:rgba(255,255,255,0.6);max-width:500px;margin:0 auto;">
                    Our video series covering Pakistan's tech ecosystem, startup stories, and emerging technologies. Episodes coming soon.
                </p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Show Information — All Shows -->
<section style="padding:var(--tp-space-8) 0;background:var(--tp-bg-secondary);">
    <div class="tp-container">
        <div class="tp-section-header">
            <h2 class="tp-section-header__title">Our Shows</h2>
        </div>
        <div class="tp-grid" style="grid-template-columns:repeat(3,1fr);gap:var(--tp-space-6);">
            <!-- TechTalk Live -->
            <div style="background:var(--tp-white);border:1px solid var(--tp-border);border-radius:var(--tp-radius-lg);padding:var(--tp-space-6);">
                <div style="font-size:24px;margin-bottom:var(--tp-space-3);">🎙️</div>
                <h3 style="font-size:var(--tp-text-xl);margin:0 0 var(--tp-space-2);">TechTalk Live</h3>
                <p style="font-size:var(--tp-text-sm);color:var(--tp-text-secondary);line-height:1.6;margin-bottom:var(--tp-space-3);">
                    Weekly live discussions on Pakistan's tech ecosystem, startup funding, and emerging technologies. Featuring interviews with founders, investors, and industry leaders.
                </p>
                <div style="font-size:var(--tp-text-xs);color:var(--tp-muted);">
                    <strong>Schedule:</strong> Fridays 8:00 PM PKT<br>
                    <strong>Episodes:</strong> <?php
                        $tt_count = new WP_Query( array( 'post_type'=>'portal_episode','post_status'=>'publish','posts_per_page'=>1,'meta_query'=>array(array('key'=>'_tp_episode_show_name','value'=>'TechTalk Live','compare'=>'LIKE')),'no_found_rows'=>false ) );
                        echo $tt_count->found_posts . ' published';
                        wp_reset_postdata();
                    ?>
                </div>
            </div>
            <!-- Startup Spotlight -->
            <div style="background:var(--tp-white);border:1px solid var(--tp-border);border-radius:var(--tp-radius-lg);padding:var(--tp-space-6);">
                <div style="font-size:24px;margin-bottom:var(--tp-space-3);">🚀</div>
                <h3 style="font-size:var(--tp-text-xl);margin:0 0 var(--tp-space-2);">Startup Spotlight</h3>
                <p style="font-size:var(--tp-text-sm);color:var(--tp-text-secondary);line-height:1.6;margin-bottom:var(--tp-space-3);">
                    Deep-dive interviews with Pakistan's most innovative startup founders. From seed stage to unicorn, we explore the journeys that are shaping the ecosystem.
                </p>
                <div style="font-size:var(--tp-text-xs);color:var(--tp-muted);">
                    <strong>Schedule:</strong> Wednesdays 7:00 PM PKT<br>
                    <strong>Episodes:</strong> <?php
                        $ss_count = new WP_Query( array( 'post_type'=>'portal_episode','post_status'=>'publish','posts_per_page'=>1,'meta_query'=>array(array('key'=>'_tp_episode_show_name','value'=>'Startup Spotlight','compare'=>'LIKE')),'no_found_rows'=>false ) );
                        echo $ss_count->found_posts . ' published';
                        wp_reset_postdata();
                    ?>
                </div>
            </div>
            <!-- Deep Dive -->
            <div style="background:var(--tp-white);border:1px solid var(--tp-border);border-radius:var(--tp-radius-lg);padding:var(--tp-space-6);">
                <div style="font-size:24px;margin-bottom:var(--tp-space-3);">🔬</div>
                <h3 style="font-size:var(--tp-text-xl);margin:0 0 var(--tp-space-2);">Deep Dive</h3>
                <p style="font-size:var(--tp-text-sm);color:var(--tp-text-secondary);line-height:1.6;margin-bottom:var(--tp-space-3);">
                    In-depth technical explorations of specific technologies and trends. From AI in healthcare to cloud infrastructure, we go beyond the headlines.
                </p>
                <div style="font-size:var(--tp-text-xs);color:var(--tp-muted);">
                    <strong>Schedule:</strong> Bi-weekly, Saturdays 10:00 AM PKT<br>
                    <strong>Episodes:</strong> <?php
                        $dd_count = new WP_Query( array( 'post_type'=>'portal_episode','post_status'=>'publish','posts_per_page'=>1,'meta_query'=>array(array('key'=>'_tp_episode_show_name','value'=>'Deep Dive','compare'=>'LIKE')),'no_found_rows'=>false ) );
                        echo $dd_count->found_posts . ' published';
                        wp_reset_postdata();
                    ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Upcoming Show -->
<section style="padding:var(--tp-space-8) 0;background:linear-gradient(135deg,var(--tp-ink) 0%,#1a1a2e 100%);color:#fff;">
    <div class="tp-container">
        <div class="tp-grid" style="align-items:center;grid-template-columns:1fr 1fr;gap:var(--tp-space-8);">
            <div>
                <span style="display:inline-block;padding:4px 12px;background:rgba(255,255,255,0.1);border-radius:20px;font-size:var(--tp-text-xs);font-weight:600;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:var(--tp-space-4);">🕐 Next Episode</span>
                <h2 style="font-size:var(--tp-text-3xl);margin:0 0 var(--tp-space-3);line-height:1.2;">
                    <?php
                    // Get upcoming episode (marked as upcoming or next by date)
                    $upcoming_args = array(
                        'post_type'      => 'portal_episode',
                        'post_status'    => 'publish',
                        'posts_per_page' => 1,
                        'meta_query'     => array( //phpcs:ignore
                            array( 'key' => 'youtube_is_upcoming', 'value' => '1', 'compare' => '=' ),
                        ),
                        'orderby'        => 'date',
                        'order'          => 'DESC',
                    );
                    $upcoming_query = new WP_Query( $upcoming_args );
                    if ( ! $upcoming_query->have_posts() ) {
                        // Fallback: latest episode
                        $upcoming_args['meta_query'] = array(); //phpcs:ignore
                        $upcoming_query = new WP_Query( $upcoming_args );
                    }
                    if ( $upcoming_query->have_posts() ) :
                        $upcoming_query->the_post();
                        echo esc_html( get_the_title() );
                    else :
                        echo 'Next TechTalk Live — Coming Friday';
                    endif;
                    wp_reset_postdata();
                    ?>
                </h2>
                <p style="font-size:var(--tp-text-base);color:rgba(255,255,255,0.7);line-height:1.6;margin-bottom:var(--tp-space-4);">
                    <?php if ( $upcoming_query->have_posts() ) : ?>
                        <?php
                        global $post;
                        $up_guest = get_post_meta( $post->ID, 'guest_name', true );
                        $up_title = get_post_meta( $post->ID, 'guest_title', true );
                        $up_show  = get_post_meta( $post->ID, '_tp_episode_show_name', true );
                        ?>
                        <?php if ( $up_guest ) : ?>
                            <strong style="color:#fff;"><?php echo esc_html( $up_guest ); ?></strong>
                            <?php if ( $up_title ) : ?>
                                <br><span style="font-size:var(--tp-text-sm);color:rgba(255,255,255,0.5);"><?php echo esc_html( $up_title ); ?></span>
                            <?php endif; ?>
                        <?php endif; ?>
                        <?php if ( $up_show ) : ?>
                            <br><span style="font-size:var(--tp-text-sm);color:var(--tp-accent);font-weight:600;"><?php echo esc_html( $up_show ); ?></span>
                        <?php endif; ?>
                    <?php else : ?>
                        Join us for our next live discussion on Pakistan's tech ecosystem.
                    <?php endif; ?>
                </p>
                <div style="display:flex;gap:var(--tp-space-3);">
                    <a href="<?php echo esc_url( home_url( '/video-archive/' ) ); ?>" class="tp-btn tp-btn--primary">View All Episodes</a>
                    <a href="https://www.youtube.com/@TechPortalPK" target="_blank" rel="noopener" class="tp-btn" style="border:1px solid rgba(255,255,255,0.2);color:#fff;">Subscribe on YouTube</a>
                </div>
            </div>
            <div style="text-align:center;">
                <div style="width:200px;height:200px;margin:0 auto;border-radius:50%;background:linear-gradient(135deg,var(--tp-accent),var(--tp-purple));display:flex;align-items:center;justify-content:center;animation:pulse-glow 3s ease-in-out infinite;">
                    <svg width="80" height="80" viewBox="0 0 24 24" fill="#fff"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                </div>
                <style>@keyframes pulse-glow { 0%,100% { box-shadow: 0 0 0 0 rgba(99,102,241,0.4); } 50% { box-shadow: 0 0 30px 10px rgba(99,102,241,0.2); } }</style>
            </div>
        </div>
    </div>
</section>

<!-- Episodes Grid -->
<section style="padding:var(--tp-space-10) 0;">
    <div class="tp-container">
        <div class="tp-section-header">
            <h2 class="tp-section-header__title">All Episodes</h2>
        </div>

        <?php if ( $episode_query->have_posts() ) : ?>
            <div class="tp-grid">
                <?php
                while ( $episode_query->have_posts() ) : $episode_query->the_post();
                    $ep_youtube_id = get_post_meta( get_the_ID(), 'youtube_video_id', true );
                    $ep_guest      = get_post_meta( get_the_ID(), 'guest_name', true );
                    $ep_is_live    = get_post_meta( get_the_ID(), 'youtube_is_live', true );
                    $ep_is_upcoming = get_post_meta( get_the_ID(), 'youtube_is_upcoming', true );
                    $ep_duration   = get_post_meta( get_the_ID(), 'youtube_duration', true );
                    $ep_views      = get_post_meta( get_the_ID(), 'youtube_view_count', true );
                ?>
                <article class="tp-card tp-card--standard">
                    <?php if ( has_post_thumbnail() ) : ?>
                        <div class="tp-card__image">
                            <a href="<?php the_permalink(); ?>">
                                <?php the_post_thumbnail( 'techportal-card', array( 'loading' => 'lazy' ) ); ?>
                            </a>
                            <!-- Play Icon + Status -->
                            <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;">
                                <div style="width:48px;height:48px;border-radius:50%;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="#fff">
                                        <polygon points="5 3 19 12 5 21 5 3"/>
                                    </svg>
                                </div>
                            </div>
                            <!-- Live/Upcoming Badges -->
                            <?php if ( $ep_is_live ) : ?>
                                <div style="position:absolute;top:8px;left:8px;background:#c62828;color:#fff;padding:2px 8px;border-radius:4px;font-size:10px;font-weight:700;text-transform:uppercase;">
                                    🔴 LIVE
                                </div>
                            <?php elseif ( $ep_is_upcoming ) : ?>
                                <div style="position:absolute;top:8px;left:8px;background:#e65100;color:#fff;padding:2px 8px;border-radius:4px;font-size:10px;font-weight:700;text-transform:uppercase;">
                                    🕐 UPCOMING
                                </div>
                            <?php endif; ?>
                            <!-- Duration -->
                            <?php if ( $ep_duration && ! $ep_is_live && ! $ep_is_upcoming ) : ?>
                                <div style="position:absolute;bottom:8px;right:8px;background:rgba(0,0,0,0.8);color:#fff;padding:2px 6px;border-radius:4px;font-size:10px;">
                                    <?php echo esc_html( $ep_duration ); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else : ?>
                        <div class="tp-card__image" style="background:linear-gradient(135deg,var(--tp-purple),var(--tp-blue));display:flex;align-items:center;justify-content:center;">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.3)" stroke-width="1.5">
                                <polygon points="5 3 19 12 5 21 5 3"/>
                            </svg>
                        </div>
                    <?php endif; ?>

                    <div class="tp-card__body">
                        <h3 class="tp-card__title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h3>

                        <?php if ( $ep_guest ) : ?>
                            <div style="font-size:var(--tp-text-xs);color:var(--tp-accent);font-weight:600;margin-bottom:var(--tp-space-2);">
                                Guest: <?php echo esc_html( $ep_guest ); ?>
                            </div>
                        <?php endif; ?>

                        <p class="tp-card__excerpt"><?php echo esc_html( wp_trim_excerpt() ); ?></p>

                        <div style="display:flex;justify-content:space-between;align-items:center;margin-top:auto;padding-top:var(--tp-space-3);border-top:1px solid var(--tp-border);font-size:var(--tp-text-xs);color:var(--tp-muted);">
                            <span><?php echo esc_html( get_the_date( 'M j, Y' ) ); ?></span>
                            <?php if ( $ep_views ) : ?>
                                <span>👁 <?php echo number_format( $ep_views ); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
        <?php else : ?>
            <!-- Empty State -->
            <div style="text-align:center;padding:var(--tp-space-16) 0;background:var(--tp-bg-secondary);border-radius:var(--tp-radius-lg);">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="var(--tp-border)" stroke-width="1.5" style="margin:0 auto var(--tp-space-4);display:block;">
                    <polygon points="5 3 19 12 5 21 5 3"/>
                </svg>
                <h3 style="font-size:var(--tp-text-xl);margin-bottom:var(--tp-space-2);color:var(--tp-text-secondary);">No Episodes Yet</h3>
                <p style="font-size:var(--tp-text-base);color:var(--tp-muted);max-width:400px;margin:0 auto;">
                    We're preparing our first episodes. Stay tuned for interviews and discussions about Pakistan's tech ecosystem.
                </p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php
// Clean up
wp_reset_postdata();
if ( $featured_query ) {
    wp_reset_postdata();
}

get_footer();
