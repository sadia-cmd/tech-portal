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
            $youtube_id = get_post_meta( $featured_episode->ID, 'youtube_video_id', true );
            $guest_name = get_post_meta( $featured_episode->ID, 'guest_name', true );
            $schedule   = get_post_meta( $featured_episode->ID, 'episode_schedule', true );
            ?>

            <!-- 16:9 Media Placeholder Area -->
            <div style="position:relative;border-radius:var(--tp-radius-lg);overflow:hidden;margin-bottom:var(--tp-space-8);aspect-ratio:16/9;background:#000;display:flex;align-items:center;justify-content:center;">
                <?php if ( has_post_thumbnail( $featured_episode->ID ) ) : ?>
                    <?php echo get_the_post_thumbnail( $featured_episode->ID, 'techportal-hero', array(
                        'style'   => 'width:100%;height:100%;object-fit:cover;',
                        'loading' => false,
                    ) ); ?>
                <?php endif; ?>

                <!-- Play Icon Overlay -->
                <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;">
                    <div style="width:80px;height:80px;border-radius:50%;background:rgba(255,255,255,0.15);backdrop-filter:blur(8px);display:flex;align-items:center;justify-content:center;border:2px solid rgba(255,255,255,0.3);transition:transform 0.2s;">
                        <svg width="36" height="36" viewBox="0 0 24 24" fill="#fff">
                            <polygon points="5 3 19 12 5 21 5 3"/>
                        </svg>
                    </div>
                </div>

                <!-- YouTube Video ID Badge -->
                <?php if ( $youtube_id ) : ?>
                    <div style="position:absolute;bottom:var(--tp-space-3);right:var(--tp-space-3);background:rgba(0,0,0,0.7);padding:4px 10px;border-radius:var(--tp-radius-sm);font-size:var(--tp-text-xs);color:rgba(255,255,255,0.7);">
                        YouTube: <?php echo esc_html( $youtube_id ); ?>
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
                    <?php if ( $schedule ) : ?>
                        <div style="font-size:var(--tp-text-sm);color:rgba(255,255,255,0.6);">
                            <strong style="color:rgba(255,255,255,0.9);">Schedule:</strong>
                            <?php echo esc_html( $schedule ); ?>
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

<!-- Show Information -->
<section style="padding:var(--tp-space-8) 0;background:var(--tp-bg-secondary);">
    <div class="tp-container">
        <div class="tp-grid" style="align-items:center;">
            <div class="tp-col-8">
                <h2 style="font-size:var(--tp-text-2xl);margin-bottom:var(--tp-space-2);">TechTalk Live</h2>
                <p style="font-size:var(--tp-text-base);color:var(--tp-text-secondary);line-height:1.6;">
                    Join us every week for live discussions on Pakistan's tech ecosystem, startup funding, and emerging technologies. Featuring interviews with founders, investors, and industry leaders.
                </p>
            </div>
            <div class="tp-col-4" style="display:flex;flex-direction:column;gap:var(--tp-space-2);align-items:flex-end;">
                <span style="font-size:var(--tp-text-sm);color:var(--tp-text-secondary);">
                    <strong style="color:var(--tp-text-primary);">Schedule:</strong> Weekly • Fridays 8:00 PM PKT
                </span>
                <span style="font-size:var(--tp-text-sm);color:var(--tp-text-secondary);">
                    <strong style="color:var(--tp-text-primary);">Episodes:</strong>
                    <?php echo esc_html( $episode_query->found_posts ); ?> published
                </span>
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
                ?>
                <article class="tp-card tp-card--standard">
                    <?php if ( has_post_thumbnail() ) : ?>
                        <div class="tp-card__image">
                            <a href="<?php the_permalink(); ?>">
                                <?php the_post_thumbnail( 'techportal-card', array( 'loading' => 'lazy' ) ); ?>
                            </a>
                            <!-- Play Icon -->
                            <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;">
                                <div style="width:48px;height:48px;border-radius:50%;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="#fff">
                                        <polygon points="5 3 19 12 5 21 5 3"/>
                                    </svg>
                                </div>
                            </div>
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
                            <?php if ( $ep_youtube_id ) : ?>
                                <span>YT: <?php echo esc_html( $ep_youtube_id ); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
        <?php else : ?>
            <!-- Empty State -->
            <div style="text-align:center;padding:var(--tp-space-16) 0;background:var(--tp-bg-secondary);border-radius:var(--tp-radius-lg);">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="var(--tp-border)" stroke-width="1.5" style="margin:0 auto var(--tp-space-4);">
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
