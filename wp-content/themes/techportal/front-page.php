<?php
/**
 * Front Page Template — Tech Portal Homepage
 *
 * 18 sections: Utility header, Masthead, Category nav, Breaking ticker,
 * Hero story, Top stories, Latest feed, Trending, Startups, Web Channel,
 * Episodes, AI & Cloud, Cybersecurity, Sponsors, Newsletter, Footer.
 *
 * @package TechPortal
 */

get_header();
?>

<?php
// ---- Queries ----
$hero_args = array(
    'post_type'      => array( 'post', 'portal_article' ),
    'post_status'    => 'publish',
    'posts_per_page' => 1,
    'orderby'        => 'date',
    'order'          => 'DESC',
);
$hero_query = new WP_Query( $hero_args );

$top_args = array(
    'post_type'      => array( 'post', 'portal_article' ),
    'post_status'    => 'publish',
    'posts_per_page' => 5,
    'orderby'        => 'date',
    'order'          => 'DESC',
    'post__not_in'   => $hero_query->posts ? array( $hero_query->posts[0]->ID ) : array(),
);
$top_query = new WP_Query( $top_args );

$latest_args = array(
    'post_type'      => array( 'post', 'portal_article' ),
    'post_status'    => 'publish',
    'posts_per_page' => 15,
    'orderby'        => 'date',
    'order'          => 'DESC',
);
$latest_query = new WP_Query( $latest_args );

$trending_args = array(
    'post_type'      => array( 'post', 'portal_article' ),
    'post_status'    => 'publish',
    'posts_per_page' => 8,
    'orderby'        => 'comment_count',
    'order'          => 'DESC',
    'date_query'     => array( array( 'after' => '14 days ago' ) ),
);
$trending_query = new WP_Query( $trending_args );

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
?>

<!-- ============================================
     SECTION 7: Major Hero Story
     ============================================ -->
<?php if ( $hero_query->have_posts() ) : $hero = $hero_query->posts[0]; ?>
<section class="tp-hero-section" style="background:var(--tp-ink);padding:var(--tp-space-6) 0;">
    <div class="tp-container">
        <div class="tp-grid">
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

            <!-- Side: Top Stories -->
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


<!-- ============================================
     SECTION 8: Curated Top Stories Grid
     ============================================ -->
<section style="padding:var(--tp-space-10) 0;">
    <div class="tp-container">
        <div class="tp-section-header">
            <h2 class="tp-section-header__title">Editor's Picks</h2>
            <a href="<?php echo esc_url( home_url( '/category/pakistan-technology/' ) ); ?>" class="tp-section-header__link">View All →</a>
        </div>
        <div class="tp-grid">
            <?php
            if ( $top_query->have_posts() ) :
                $grid_count = 0;
                while ( $top_query->have_posts() ) : $top_query->the_post();
                    $grid_count++;
                    $card_class = $grid_count <= 2 ? 'tp-card--feature' : 'tp-card--standard';
            ?>
            <article class="tp-card <?php echo esc_attr( $card_class ); ?>">
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
                    <p class="tp-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 18 ) ); ?></p>
                    <?php techportal_post_meta(); ?>
                </div>
            </article>
            <?php
                endwhile;
                wp_reset_postdata();
            endif;
            ?>
        </div>
    </div>
</section>


<!-- ============================================
     SECTION 9: Latest News Feed + Section 10: Trending
     ============================================ -->
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
                if ( $trending_query->have_posts() ) :
                    $rank = 1;
                    while ( $trending_query->have_posts() ) : $trending_query->the_post();
                ?>
                <div class="tp-trending-item">
                    <span class="tp-trending-item__rank"><?php echo esc_html( str_pad( $rank, 2, '0', STR_PAD_LEFT ) ); ?></span>
                    <div>
                        <a href="<?php the_permalink(); ?>" class="tp-latest-item__title" style="display:block;">
                            <?php the_title(); ?>
                        </a>
                        <div style="font-size:var(--tp-text-xs);color:var(--tp-muted);margin-top:2px;">
                            <?php
                            $cats = get_the_category( get_the_ID() );
                            echo esc_html( $cats[0]->name ?? '' );
                            ?>
                        </div>
                    </div>
                </div>
                <?php
                        $rank++;
                    endwhile;
                    wp_reset_postdata();
                endif;
                ?>
            </div>
        </div>
    </div>
</section>


<!-- ============================================
     SECTION 11: Startup Ecosystem Showcase
     ============================================ -->
<?php if ( $startup_query->have_posts() ) : ?>
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
                </div>
            </article>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
    </div>
</section>
<?php endif; ?>


<!-- ============================================
     SECTION 12 & 13: Web Channel / Episodes
     ============================================ -->
<section style="padding:var(--tp-space-10) 0;background:var(--tp-ink);color:#fff;">
    <div class="tp-container">
        <div class="tp-section-header" style="border-bottom-color:rgba(255,255,255,0.15);">
            <h2 class="tp-section-header__title" style="color:#fff;">📺 Web Channel</h2>
            <a href="<?php echo esc_url( home_url( '/episode/' ) ); ?>" class="tp-section-header__link">All Episodes →</a>
        </div>

        <!-- Live Indicator Placeholder -->
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
                <div style="display:flex;gap:var(--tp-space-3);flex-wrap:wrap;">
                    <span style="font-size:var(--tp-text-xs);color:rgba(255,255,255,0.5);">Every Friday • 8:00 PM PKT</span>
                </div>
            </div>
        </div>

        <!-- Episodes Grid -->
        <?php if ( $episode_query->have_posts() ) : ?>
        <div class="tp-grid">
            <?php
            while ( $episode_query->have_posts() ) : $episode_query->the_post();
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
                    <h3 class="tp-card__title" style="color:#fff;">
                        <a href="<?php the_permalink(); ?>" style="color:#fff;"><?php the_title(); ?></a>
                    </h3>
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


<!-- ============================================
     SECTION 14: AI & Cloud + SECTION 15: Cybersecurity
     ============================================ -->
<section style="padding:var(--tp-space-10) 0;">
    <div class="tp-container">
        <div class="tp-grid">
            <!-- AI & Cloud -->
            <div class="tp-col-6">
                <div class="tp-section-header">
                    <h2 class="tp-section-header__title">🤖 AI & Cloud</h2>
                    <a href="<?php echo esc_url( home_url( '/topic/ai/' ) ); ?>" class="tp-section-header__link">More →</a>
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


<!-- ============================================
     SECTION 16: Sponsor Inventory
     ============================================ -->
<section style="padding:var(--tp-space-8) 0;border-top:1px solid var(--tp-border);border-bottom:1px solid var(--tp-border);">
    <div class="tp-container">
        <div style="text-align:center;margin-bottom:var(--tp-space-4);">
            <span style="font-size:var(--tp-text-xs);text-transform:uppercase;letter-spacing:0.1em;color:var(--tp-muted);font-weight:600;">Sponsored</span>
        </div>
        <div class="tp-grid" style="align-items:center;">
            <div class="tp-col-4">
                <div class="tp-sponsor-slot">
                    <span class="tp-sponsor-slot__label">Sponsor Slot 1</span>
                </div>
            </div>
            <div class="tp-col-4">
                <div class="tp-sponsor-slot">
                    <span class="tp-sponsor-slot__label">Sponsor Slot 2</span>
                </div>
            </div>
            <div class="tp-col-4">
                <div class="tp-sponsor-slot">
                    <span class="tp-sponsor-slot__label">Sponsor Slot 3</span>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- ============================================
     SECTION 17: Newsletter CTA
     ============================================ -->
<section style="padding:var(--tp-space-10) 0;">
    <div class="tp-container">
        <div class="tp-newsletter">
            <h2 class="tp-newsletter__title">Stay Ahead in Tech</h2>
            <p class="tp-newsletter__desc">
                Get Pakistan's top technology news, startup insights, and industry analysis delivered to your inbox every morning.
            </p>
            <form class="tp-newsletter__form" action="#" method="post">
                <input type="email" class="tp-newsletter__input" placeholder="your@email.com" required aria-label="Email address">
                <button type="submit" class="tp-newsletter__btn">Subscribe</button>
            </form>
            <p style="font-size:var(--tp-text-xs);color:rgba(255,255,255,0.4);margin-top:var(--tp-space-3);">
                No spam. Unsubscribe anytime. Join 15,000+ tech professionals.
            </p>
        </div>
    </div>
</section>


<?php get_footer(); ?>
