<?php
/**
 * Template Name: Watch
 * YouTube video watch page with player + playlist sidebar
 *
 * @package TechPortal
 */
get_header();

// Get video ID from URL param or default to latest
$watch_id = isset( $_GET['v'] ) ? sanitize_text_field( $_GET['v'] ) : '';

// Find the episode for this video ID
$current_ep = null;
if ( $watch_id ) {
    $ep_args = array(
        'post_type'      => 'portal_episode',
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        'meta_query'     => array(
            array( 'key' => 'youtube_video_id', 'value' => $watch_id, 'compare' => '=' ),
        ),
    );
    $ep_query = new WP_Query( $ep_args );
    if ( $ep_query->have_posts() ) {
        $ep_query->the_post();
        $current_ep = get_the_ID();
    }
}

// If no specific video, get latest
if ( ! $current_ep ) {
    $latest_ep = new WP_Query( array(
        'post_type'      => 'portal_episode',
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        'meta_key'       => 'youtube_video_id',
        'meta_compare'   => '!=',
        'meta_value'     => '',
        'orderby'        => 'date',
        'order'          => 'DESC',
    ));
    if ( $latest_ep->have_posts() ) {
        $latest_ep->the_post();
        $current_ep = get_the_ID();
    }
}

$yt_id    = get_post_meta( $current_ep, 'youtube_video_id', true );
$show     = get_post_meta( $current_ep, '_tp_episode_show_name', true );
$guest    = get_post_meta( $current_ep, 'guest_name', true );
$guest_title = get_post_meta( $current_ep, 'guest_title', true );
$is_live  = get_post_meta( $current_ep, 'youtube_is_live', true );
$duration = get_post_meta( $current_ep, 'youtube_duration', true );
$views    = get_post_meta( $current_ep, 'youtube_view_count', true );

// All episodes for playlist
$all_eps = new WP_Query( array(
    'post_type'      => 'portal_episode',
    'post_status'    => 'publish',
    'posts_per_page' => 20,
    'meta_key'       => 'youtube_video_id',
    'meta_compare'   => '!=',
    'meta_value'     => '',
    'orderby'        => 'date',
    'order'          => 'DESC',
));
?>

<main id="content" style="background:#0f0f0f;min-height:100vh;color:#fff;">
    <div style="max-width:1400px;margin:0 auto;padding:var(--tp-space-4) var(--tp-space-6);display:grid;grid-template-columns:1fr 400px;gap:var(--tp-space-5);">

        <!-- Left: Player + Info -->
        <div>
            <!-- Video Player -->
            <div style="position:relative;width:100%;aspect-ratio:16/9;background:#000;border-radius:var(--tp-radius-lg);overflow:hidden;margin-bottom:var(--tp-space-4);">
                <?php if ( $yt_id ) : ?>
                    <iframe
                        src="https://www.youtube.com/embed/<?php echo esc_attr( $yt_id ); ?>?rel=0&modestbranding=1&autoplay=1"
                        style="position:absolute;top:0;left:0;width:100%;height:100%;border:0;"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        allowfullscreen
                        title="<?php echo esc_attr( get_the_title( $current_ep ) ); ?>"
                    ></iframe>
                <?php else : ?>
                    <div style="display:flex;align-items:center;justify-content:center;width:100%;height:100%;">
                        <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.2)" stroke-width="1"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Video Info -->
            <div style="margin-bottom:var(--tp-space-6);">
                <?php if ( $is_live ) : ?>
                    <div style="display:inline-flex;align-items:center;gap:6px;background:rgba(220,38,38,0.9);padding:4px 12px;border-radius:var(--tp-radius-full);margin-bottom:var(--tp-space-3);">
                        <span style="width:8px;height:8px;border-radius:50%;background:#fff;animation:tp-pulse 2s ease-in-out infinite;"></span>
                        <span style="font-size:var(--tp-text-xs);font-weight:700;color:#fff;">LIVE</span>
                    </div>
                <?php endif; ?>

                <h1 style="font-size:var(--tp-text-2xl);font-weight:var(--tp-weight-extrabold);margin-bottom:var(--tp-space-2);line-height:1.3;">
                    <?php echo esc_html( get_the_title( $current_ep ) ); ?>
                </h1>

                <div style="display:flex;align-items:center;gap:var(--tp-space-4);color:rgba(255,255,255,0.6);font-size:var(--tp-text-sm);margin-bottom:var(--tp-space-4);">
                    <?php if ( $show ) : ?>
                        <span style="color:var(--tp-accent);font-weight:600;"><?php echo esc_html( $show ); ?></span>
                    <?php endif; ?>
                    <span><?php echo esc_html( get_the_date( 'M j, Y', $current_ep ) ); ?></span>
                    <?php if ( $views ) : ?>
                        <span><?php echo esc_html( number_format( intval( $views ) ) ); ?> views</span>
                    <?php endif; ?>
                </div>

                <?php if ( $guest ) : ?>
                <div style="display:flex;align-items:center;gap:var(--tp-space-3);padding:var(--tp-space-4);background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);border-radius:var(--tp-radius-md);margin-bottom:var(--tp-space-4);">
                    <div style="width:48px;height:48px;border-radius:50%;background:var(--tp-accent);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:var(--tp-text-lg);">
                        <?php echo esc_html( strtoupper( substr( $guest, 0, 1 ) ) ); ?>
                    </div>
                    <div>
                        <div style="font-weight:600;color:#fff;"><?php echo esc_html( $guest ); ?></div>
                        <?php if ( $guest_title ) : ?>
                            <div style="font-size:var(--tp-text-xs);color:rgba(255,255,255,0.5);"><?php echo esc_html( $guest_title ); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Description -->
                <div style="padding:var(--tp-space-4);background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);border-radius:var(--tp-radius-md);color:rgba(255,255,255,0.7);font-size:var(--tp-text-sm);line-height:1.7;">
                    <?php echo wpautop( get_the_content( null, false, $current_ep ) ); ?>
                </div>

                <!-- Actions -->
                <div style="display:flex;gap:var(--tp-space-3);margin-top:var(--tp-space-4);">
                    <a href="<?php echo esc_url( get_permalink( $current_ep ) ); ?>" style="display:inline-flex;align-items:center;gap:6px;padding:var(--tp-space-2) var(--tp-space-4);background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.15);border-radius:var(--tp-radius-md);color:#fff;font-size:var(--tp-text-sm);text-decoration:none;transition:background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.15)'" onmouseout="this.style.background='rgba(255,255,255,0.1)'">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        Read Article
                    </a>
                    <a href="https://www.youtube.com/watch?v=<?php echo esc_attr( $yt_id ); ?>" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:6px;padding:var(--tp-space-2) var(--tp-space-4);background:rgba(220,38,38,0.2);border:1px solid rgba(220,38,38,0.3);border-radius:var(--tp-radius-md);color:#fff;font-size:var(--tp-text-sm);text-decoration:none;transition:background 0.2s;" onmouseover="this.style.background='rgba(220,38,38,0.3)'" onmouseout="this.style.background='rgba(220,38,38,0.2)'">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                        Watch on YouTube
                    </a>
                </div>
            </div>
        </div>

        <!-- Right: Playlist Sidebar -->
        <div>
            <div style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);border-radius:var(--tp-radius-lg);overflow:hidden;position:sticky;top:80px;">
                <div style="padding:var(--tp-space-4);border-bottom:1px solid rgba(255,255,255,0.08);">
                    <h3 style="font-size:var(--tp-text-sm);font-weight:700;color:#fff;margin:0;">All Episodes (<?php echo $all_eps->found_posts; ?>)</h3>
                </div>
                <div style="max-height:calc(100vh - 160px);overflow-y:auto;">
                <?php
                $pl_idx = 1;
                if ( $all_eps->have_posts() ) :
                    while ( $all_eps->have_posts() ) : $all_eps->the_post();
                        $pl_yt_id = get_post_meta( get_the_ID(), 'youtube_video_id', true );
                        $pl_show  = get_post_meta( get_the_ID(), '_tp_episode_show_name', true );
                        $pl_guest = get_post_meta( get_the_ID(), 'guest_name', true );
                        $is_current = ( get_the_ID() == $current_ep );
                        $pl_thumb = "https://img.youtube.com/vi/{$pl_yt_id}/default.jpg";
                ?>
                    <a href="<?php echo esc_url( home_url( '/watch/?v=' . $pl_yt_id ) ); ?>" style="display:flex;gap:var(--tp-space-3);padding:var(--tp-space-3) var(--tp-space-4);text-decoration:none;color:inherit;transition:background 0.2s;<?php echo $is_current ? 'background:rgba(255,255,255,0.08);' : ''; ?>" onmouseover="if(!this.style.background.includes('0.08'))this.style.background='rgba(255,255,255,0.05)'" onmouseout="if(!this.style.background.includes('0.08'))this.style.background=''">
                        <!-- Thumbnail -->
                        <div style="position:relative;min-width:120px;width:120px;height:68px;border-radius:var(--tp-radius-sm);overflow:hidden;background:#222;">
                            <img src="<?php echo esc_url( $pl_thumb ); ?>" alt="" style="width:100%;height:100%;object-fit:cover;" loading="lazy" />
                            <?php if ( $is_current ) : ?>
                                <div style="position:absolute;top:0;left:0;width:100%;height:100%;background:rgba(255,0,0,0.3);display:flex;align-items:center;justify-content:center;">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="#fff"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                                </div>
                            <?php endif; ?>
                            <div style="position:absolute;bottom:2px;right:2px;background:rgba(0,0,0,0.8);color:#fff;font-size:9px;font-weight:600;padding:1px 4px;border-radius:2px;"><?php echo esc_html( $pl_idx ); ?>/<?php echo $all_eps->found_posts; ?></div>
                        </div>
                        <!-- Info -->
                        <div style="flex:1;min-width:0;padding-top:2px;">
                            <div style="font-size:var(--tp-text-xs);color:rgba(255,255,255,0.5);margin-bottom:2px;"><?php echo esc_html( $pl_show ?: 'Episode' ); ?></div>
                            <div style="font-size:var(--tp-text-xs);color:<?php echo $is_current ? '#fff' : 'rgba(255,255,255,0.85)'; ?>;line-height:1.4;font-weight:<?php echo $is_current ? '600' : '400' ?>;overflow:hidden;text-overflow:ellipsis;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;"><?php the_title(); ?></div>
                            <?php if ( $pl_guest ) : ?><div style="font-size:10px;color:rgba(255,255,255,0.35);margin-top:2px;"><?php echo esc_html( $pl_guest ); ?></div><?php endif; ?>
                        </div>
                    </a>
                <?php
                    $pl_idx++;
                    endwhile;
                    wp_reset_postdata();
                endif;
                ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php get_footer(); ?>
