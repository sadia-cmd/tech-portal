<?php
/**
 * Single Episode Template
 *
 * Displays a Web Channel episode with YouTube media area,
 * guest info, show info sidebar, and related content.
 *
 * @package TechPortal
 */

get_header();
?>

<?php while ( have_posts() ) : the_post();

// Get meta fields
$youtube_id  = get_post_meta( get_the_ID(), 'youtube_video_id', true );
$guest_name  = get_post_meta( get_the_ID(), 'guest_name', true );
$guest_title = get_post_meta( get_the_ID(), 'guest_title', true );
$company     = get_post_meta( get_the_ID(), 'company_name', true );
$show_name   = get_post_meta( get_the_ID(), 'show_name', true );
$show_name   = $show_name ? $show_name : 'Web Channel';
$is_live     = get_post_meta( get_the_ID(), 'youtube_is_live', true );
$is_upcoming = get_post_meta( get_the_ID(), 'youtube_is_upcoming', true );
$duration    = get_post_meta( get_the_ID(), 'youtube_duration', true );
$view_count  = get_post_meta( get_the_ID(), 'youtube_view_count', true );

// Get topic taxonomy
$topics   = get_the_terms( get_the_ID(), 'portal_topic' );
$topic_name = ( $topics && ! is_wp_error( $topics ) ) ? $topics[0]->name : '';

// Related articles query
$article_args = array(
    'post_type'      => array( 'post', 'portal_article' ),
    'post_status'    => 'publish',
    'posts_per_page' => 4,
    'orderby'        => 'date',
    'order'          => 'DESC',
    'meta_query'     => array(
        array(
            'key'     => '_related_episode',
            'value'   => get_the_ID(),
            'compare' => '=',
        ),
    ),
);
$article_query = new WP_Query( $article_args );

// Fallback: search for articles about the same topic/guest
if ( ! $article_query->have_posts() ) {
    $search_terms = get_the_title();
    if ( $guest_name ) {
        $search_terms .= ' ' . $guest_name;
    }
    $article_args_fb = array(
        'post_type'      => array( 'post', 'portal_article' ),
        'post_status'    => 'publish',
        'posts_per_page' => 4,
        'orderby'        => 'date',
        'order'          => 'DESC',
        's'              => $search_terms,
        'post__not_in'   => array( get_the_ID() ),
    );
    $article_query = new WP_Query( $article_args_fb );
}

// Related episodes query
$related_args = array(
    'post_type'      => 'portal_episode',
    'post_status'    => 'publish',
    'posts_per_page' => 3,
    'post__not_in'   => array( get_the_ID() ),
    'orderby'        => 'date',
    'order'          => 'DESC',
);
if ( $topics && ! is_wp_error( $topics ) ) {
    $related_args['tax_query'] = array(
        array(
            'taxonomy' => 'portal_topic',
            'field'    => 'term_id',
            'terms'    => $topics[0]->term_id,
        ),
    );
}
$related_episode_query = new WP_Query( $related_args );
?>

<!-- Breadcrumb-like Navigation -->
<section style="padding:var(--tp-space-4) 0;background:var(--tp-bg-secondary);border-bottom:1px solid var(--tp-border);">
    <div class="tp-container">
        <nav style="font-size:var(--tp-text-sm);color:var(--tp-muted);" aria-label="Breadcrumb">
            <a href="<?php echo esc_url( home_url( '/web-channel/' ) ); ?>" style="color:var(--tp-accent);text-decoration:none;">Web Channel</a>
            <span style="margin:0 var(--tp-space-2);color:var(--tp-border);">/</span>
            <span style="color:var(--tp-text-secondary);"><?php echo esc_html( $show_name ); ?></span>
        </nav>
    </div>
</section>

<!-- Episode Content -->
<section style="padding:var(--tp-space-8) 0;">
    <div class="tp-container">
        <div class="tp-grid">
            <!-- Main Content -->
            <div style="grid-column:span 8;">
                <!-- Episode Title -->
                <h1 style="font-size:var(--tp-text-4xl);margin-bottom:var(--tp-space-4);line-height:var(--tp-leading-tight);">
                    <?php the_title(); ?>
                </h1>

                <!-- Episode Meta -->
                <div style="display:flex;align-items:center;gap:var(--tp-space-4);flex-wrap:wrap;margin-bottom:var(--tp-space-6);font-size:var(--tp-text-sm);color:var(--tp-text-secondary);">
                    <span><?php echo esc_html( get_the_date( 'F j, Y' ) ); ?></span>
                    <?php if ( $topic_name ) : ?>
                        <span style="display:inline-block;padding:2px 8px;background:var(--tp-bg-secondary);border:1px solid var(--tp-border);border-radius:var(--tp-radius-sm);font-size:var(--tp-text-xs);font-weight:var(--tp-weight-semibold);color:var(--tp-accent);text-transform:uppercase;letter-spacing:0.05em;">
                            <?php echo esc_html( $topic_name ); ?>
                        </span>
                    <?php endif; ?>
                    <?php if ( $duration ) : ?>
                        <span>⏱ <?php echo esc_html( $duration ); ?></span>
                    <?php endif; ?>
                    <?php if ( $view_count ) : ?>
                        <span>👁 <?php echo number_format( $view_count ); ?> views</span>
                    <?php endif; ?>
                </div>

                <!-- YouTube Player / Media Area -->
                <?php if ( $youtube_id ) : ?>
                <div style="position:relative;width:100%;aspect-ratio:16/9;background:#000;border-radius:var(--tp-radius-lg);overflow:hidden;margin-bottom:var(--tp-space-8);">
                    <iframe
                        src="https://www.youtube.com/embed/<?php echo esc_attr( $youtube_id ); ?>?rel=0&modestbranding=1"
                        style="width:100%;height:100%;border:none;"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen
                        title="<?php echo esc_attr( get_the_title() ); ?>"
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
                </div>
                <?php elseif ( has_post_thumbnail() ) : ?>
                <div style="position:relative;width:100%;aspect-ratio:16/9;background:#000;border-radius:var(--tp-radius-lg);overflow:hidden;margin-bottom:var(--tp-space-8);">
                    <?php the_post_thumbnail( 'techportal-hero', array(
                        'style'   => 'width:100%;height:100%;object-fit:cover;',
                        'loading' => false,
                    ) ); ?>
                    <!-- Play Icon Overlay -->
                    <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0.3);">
                        <div style="width:80px;height:80px;border-radius:50%;background:rgba(255,255,255,0.15);backdrop-filter:blur(8px);display:flex;align-items:center;justify-content:center;border:2px solid rgba(255,255,255,0.3);">
                            <svg width="36" height="36" viewBox="0 0 24 24" fill="#fff">
                                <polygon points="5 3 19 12 5 21 5 3"/>
                            </svg>
                        </div>
                    </div>
                </div>
                <?php else : ?>
                <!-- Empty Media Placeholder -->
                <div style="position:relative;width:100%;aspect-ratio:16/9;background:#0a0b0e;border-radius:var(--tp-radius-lg);overflow:hidden;margin-bottom:var(--tp-space-8);display:flex;align-items:center;justify-content:center;">
                    <div style="text-align:center;">
                        <div style="width:80px;height:80px;margin:0 auto var(--tp-space-4);border-radius:50%;background:rgba(255,255,255,0.05);display:flex;align-items:center;justify-content:center;">
                            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.3)" stroke-width="1.5">
                                <polygon points="5 3 19 12 5 21 5 3"/>
                            </svg>
                        </div>
                        <p style="font-size:var(--tp-text-sm);color:rgba(255,255,255,0.4);margin:0;">Video coming soon</p>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Guest Info Bar -->
                <?php if ( $guest_name ) : ?>
                <div style="display:flex;align-items:center;gap:var(--tp-space-4);padding:var(--tp-space-4) var(--tp-space-5);background:var(--tp-bg-secondary);border-radius:var(--tp-radius-md);margin-bottom:var(--tp-space-6);border:1px solid var(--tp-border);">
                    <div style="width:48px;height:48px;border-radius:50%;background:linear-gradient(135deg,var(--tp-purple),var(--tp-blue));display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <span style="font-size:var(--tp-text-lg);color:#fff;font-weight:700;">
                            <?php echo esc_html( mb_substr( $guest_name, 0, 1 ) ); ?>
                        </span>
                    </div>
                    <div>
                        <div style="font-size:var(--tp-text-sm);font-weight:var(--tp-weight-semibold);color:var(--tp-text-primary);">
                            <?php echo esc_html( $guest_name ); ?>
                        </div>
                        <div style="font-size:var(--tp-text-xs);color:var(--tp-text-secondary);">
                            <?php
                            $meta_parts = array();
                            if ( $guest_title ) {
                                $meta_parts[] = $guest_title;
                            }
                            if ( $company ) {
                                $meta_parts[] = $company;
                            }
                            echo esc_html( implode( ' at ', $meta_parts ) );
                            ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Bookmark + Episode Description -->
                <div style="display:flex;align-items:center;gap:var(--tp-space-3);margin-bottom:var(--tp-space-4);">
                    <?php if ( class_exists( 'Portal_Membership' ) ) : ?>
                        <?php Portal_Membership::instance()->bookmark_button( get_the_ID() ); ?>
                        <span style="font-size:var(--tp-text-sm);color:var(--tp-muted);">Save this episode</span>
                    <?php endif; ?>
                </div>
                <div style="font-size:var(--tp-text-base);line-height:var(--tp-leading-loose);color:var(--tp-text-primary);">
                    <?php the_content(); ?>
                </div>
            </div>

            <!-- Sidebar: Show Information -->
            <aside style="grid-column:span 4;">
                <!-- Show Info Card -->
                <div style="position:sticky;top:120px;">
                    <div style="background:var(--tp-ink);border-radius:var(--tp-radius-lg);padding:var(--tp-space-6);color:#fff;margin-bottom:var(--tp-space-6);">
                        <h3 style="font-size:var(--tp-text-lg);color:#fff;margin-bottom:var(--tp-space-4);padding-bottom:var(--tp-space-3);border-bottom:1px solid rgba(255,255,255,0.15);">
                            Show Information
                        </h3>
                        <dl style="display:flex;flex-direction:column;gap:var(--tp-space-4);">
                            <div>
                                <dt style="font-size:var(--tp-text-xs);text-transform:uppercase;letter-spacing:0.08em;color:rgba(255,255,255,0.5);font-weight:var(--tp-weight-semibold);margin-bottom:var(--tp-space-1);">Show</dt>
                                <dd style="font-size:var(--tp-text-sm);font-weight:var(--tp-weight-medium);color:#fff;"><?php echo esc_html( $show_name ); ?></dd>
                            </div>
                            <div>
                                <dt style="font-size:var(--tp-text-xs);text-transform:uppercase;letter-spacing:0.08em;color:rgba(255,255,255,0.5);font-weight:var(--tp-weight-semibold);margin-bottom:var(--tp-space-1);">Date</dt>
                                <dd style="font-size:var(--tp-text-sm);font-weight:var(--tp-weight-medium);color:#fff;"><?php echo esc_html( get_the_date( 'F j, Y' ) ); ?></dd>
                            </div>
                            <?php if ( $guest_name ) : ?>
                            <div>
                                <dt style="font-size:var(--tp-text-xs);text-transform:uppercase;letter-spacing:0.08em;color:rgba(255,255,255,0.5);font-weight:var(--tp-weight-semibold);margin-bottom:var(--tp-space-1);">Guest</dt>
                                <dd style="font-size:var(--tp-text-sm);font-weight:var(--tp-weight-medium);color:#fff;"><?php echo esc_html( $guest_name ); ?></dd>
                            </div>
                            <?php endif; ?>
                            <?php if ( $topic_name ) : ?>
                            <div>
                                <dt style="font-size:var(--tp-text-xs);text-transform:uppercase;letter-spacing:0.08em;color:rgba(255,255,255,0.5);font-weight:var(--tp-weight-semibold);margin-bottom:var(--tp-space-1);">Topic</dt>
                                <dd style="font-size:var(--tp-text-sm);font-weight:var(--tp-weight-medium);color:#fff;"><?php echo esc_html( $topic_name ); ?></dd>
                            </div>
                            <?php endif; ?>
                            <?php if ( $duration ) : ?>
                            <div>
                                <dt style="font-size:var(--tp-text-xs);text-transform:uppercase;letter-spacing:0.08em;color:rgba(255,255,255,0.5);font-weight:var(--tp-weight-semibold);margin-bottom:var(--tp-space-1);">Duration</dt>
                                <dd style="font-size:var(--tp-text-sm);font-weight:var(--tp-weight-medium);color:#fff;"><?php echo esc_html( $duration ); ?></dd>
                            </div>
                            <?php endif; ?>
                            <?php if ( $view_count ) : ?>
                            <div>
                                <dt style="font-size:var(--tp-text-xs);text-transform:uppercase;letter-spacing:0.08em;color:rgba(255,255,255,0.5);font-weight:var(--tp-weight-semibold);margin-bottom:var(--tp-space-1);">Views</dt>
                                <dd style="font-size:var(--tp-text-sm);font-weight:var(--tp-weight-medium);color:#fff;"><?php echo number_format( $view_count ); ?></dd>
                            </div>
                            <?php endif; ?>
                        </dl>
                    </div>

                    <!-- Startup/Company Link -->
                    <?php if ( $company ) : ?>
                    <div style="background:var(--tp-bg-secondary);border-radius:var(--tp-radius-lg);padding:var(--tp-space-5);border:1px solid var(--tp-border);margin-bottom:var(--tp-space-6);">
                        <h4 style="font-size:var(--tp-text-sm);font-weight:var(--tp-weight-semibold);margin-bottom:var(--tp-space-3);">Featured Company</h4>
                        <p style="font-size:var(--tp-text-sm);color:var(--tp-text-primary);font-weight:var(--tp-weight-medium);margin:0;">
                            <?php echo esc_html( $company ); ?>
                        </p>
                    </div>
                    <?php endif; ?>

                    <!-- All Episodes Link -->
                    <a href="<?php echo esc_url( home_url( '/web-channel/' ) ); ?>" class="tp-btn tp-btn--outline" style="width:100%;justify-content:center;">
                        ← Back to Web Channel
                    </a>
                </div>
            </aside>
        </div>
    </div>
</section>

<!-- Related Articles -->
<?php if ( $article_query->have_posts() ) : ?>
<section style="padding:var(--tp-space-8) 0;border-top:1px solid var(--tp-border);">
    <div class="tp-container">
        <div class="tp-section-header">
            <h2 class="tp-section-header__title">Related Articles</h2>
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
</section>
<?php endif; ?>

<!-- Related Episodes -->
<?php if ( $related_episode_query->have_posts() ) : ?>
<section style="padding:var(--tp-space-8) 0;background:var(--tp-ink);color:#fff;">
    <div class="tp-container">
        <div class="tp-section-header" style="border-bottom-color:rgba(255,255,255,0.15);">
            <h2 class="tp-section-header__title" style="color:#fff;">More Episodes</h2>
            <a href="<?php echo esc_url( home_url( '/web-channel/' ) ); ?>" class="tp-section-header__link">All Episodes →</a>
        </div>
        <div class="tp-grid">
            <?php while ( $related_episode_query->have_posts() ) : $related_episode_query->the_post(); ?>
            <article class="tp-card tp-card--standard" style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);">
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
</section>
<?php endif; ?>

<?php endwhile; ?>

<?php get_footer(); ?>
