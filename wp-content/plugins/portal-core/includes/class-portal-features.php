<?php
/**
 * Portal Features — Submit News, Sponsors, Banners, Related, Trending, Video Archive
 *
 * @package PortalCore
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Portal_Features {

    private static $instance = null;

    public static function instance() {
        if ( null === self::$instance ) self::$instance = new self();
        return self::$instance;
    }

    private function __construct() {
        // Shortcodes
        add_shortcode( 'tp_submit_news',     array( $this, 'shortcode_submit_news' ) );
        add_shortcode( 'tp_video_archive',   array( $this, 'shortcode_video_archive' ) );
        add_shortcode( 'tp_related_posts',   array( $this, 'shortcode_related_posts' ) );
        add_shortcode( 'tp_trending',        array( $this, 'shortcode_trending' ) );
        add_shortcode( 'tp_banner',          array( $this, 'shortcode_banner' ) );
        add_shortcode( 'tp_sponsors',        array( $this, 'shortcode_sponsors' ) );

        // AJAX
        add_action( 'wp_ajax_tp_submit_news',          array( $this, 'ajax_submit_news' ) );
        add_action( 'wp_ajax_nopriv_tp_submit_news',    array( $this, 'ajax_submit_news' ) );
        add_action( 'wp_ajax_tp_search_episodes',       array( $this, 'ajax_search_episodes' ) );

        // Enqueue
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );

        // Banner widget areas
        add_action( 'widgets_init', array( $this, 'register_banner_widgets' ) );

        // Related posts on single pages
        add_action( 'tp_related_posts_area', array( $this, 'output_related_posts' ) );

        // Trending on homepage
        add_action( 'tp_trending_area', array( $this, 'output_trending' ) );

        // Press release submission status column
        add_filter( 'manage_portal_press_posts_columns', array( $this, 'add_submission_columns' ) );
        add_action( 'manage_portal_press_posts_custom_column', array( $this, 'render_submission_columns' ), 10, 2 );

        // Admin columns for sponsors
        add_filter( 'manage_tp_sponsor_posts_columns', array( $this, 'add_sponsor_columns' ) );
        add_action( 'manage_tp_sponsor_posts_custom_column', array( $this, 'render_sponsor_columns' ), 10, 2 );
    }

    /* ─── Submit News Form ─── */

    public function shortcode_submit_news() {
        if ( is_user_logged_in() ) {
            $user = wp_get_current_user();
            $default_name  = $user->display_name;
            $default_email = $user->user_email;
        } else {
            $default_name  = '';
            $default_email = '';
        }
        ob_start(); ?>
        <div class="tp-submit-page">
            <div class="tp-submit-card">
                <div class="tp-submit-card__icon">📰</div>
                <h1 class="tp-submit-card__title">Submit a News Tip or Press Release</h1>
                <p class="tp-submit-card__subtitle">Share news about Pakistan's tech ecosystem. Submissions are reviewed by our editorial team before publishing.</p>

                <div id="tp-submit-success" class="tp-auth-error" style="display:none;background:#f0fdf4;border-color:#bbf7d0;color:#166534;"></div>
                <div id="tp-submit-error" class="tp-auth-error" style="display:none;"></div>

                <form id="tp-submit-form" class="tp-auth-form">
                    <?php wp_nonce_field( 'tp_auth_nonce', 'tp_nonce' ); ?>
                    <div class="tp-form-row">
                        <div class="tp-form-group">
                            <label for="tp-submit-name">Your Name *</label>
                            <input type="text" id="tp-submit-name" name="submitter_name" required value="<?php echo esc_attr( $default_name ); ?>" />
                        </div>
                        <div class="tp-form-group">
                            <label for="tp-submit-email">Email *</label>
                            <input type="email" id="tp-submit-email" name="submitter_email" required value="<?php echo esc_attr( $default_email ); ?>" />
                        </div>
                    </div>
                    <div class="tp-form-group">
                        <label for="tp-submit-company">Company / Organization</label>
                        <input type="text" id="tp-submit-company" name="submitter_company" placeholder="Optional" />
                    </div>
                    <div class="tp-form-group">
                        <label for="tp-submit-type">Submission Type *</label>
                        <select id="tp-submit-type" name="submission_type" required>
                            <option value="">Select type…</option>
                            <option value="press_release">Press Release</option>
                            <option value="news_tip">News Tip</option>
                            <option value="event">Event Announcement</option>
                            <option value="funding">Funding Announcement</option>
                            <option value="launch">Product Launch</option>
                        </select>
                    </div>
                    <div class="tp-form-group">
                        <label for="tp-submit-title">Headline / Title *</label>
                        <input type="text" id="tp-submit-title" name="post_title" required placeholder="e.g. Karachi Startup Raises $5M Series A" />
                    </div>
                    <div class="tp-form-group">
                        <label for="tp-submit-content">Content / Details *</label>
                        <textarea id="tp-submit-content" name="post_content" rows="8" required placeholder="Write the full press release or news details here…"></textarea>
                    </div>
                    <div class="tp-form-row">
                        <div class="tp-form-group">
                            <label for="tp-submit-url">Source URL</label>
                            <input type="url" id="tp-submit-url" name="source_url" placeholder="https://" />
                        </div>
                        <div class="tp-form-group">
                            <label for="tp-submit-contact">Media Contact</label>
                            <input type="text" id="tp-submit-contact" name="media_contact" placeholder="Name, phone, or email" />
                        </div>
                    </div>
                    <div class="tp-form-group">
                        <label for="tp-submit-image">Featured Image URL</label>
                        <input type="url" id="tp-submit-image" name="image_url" placeholder="https://example.com/image.jpg" />
                        <small style="color:var(--tp-muted);font-size:12px;">Paste a direct link to an image (JPG, PNG). Optional.</small>
                    </div>
                    <button type="submit" class="tp-btn tp-btn--primary tp-btn--full">Submit for Review</button>
                </form>
            </div>
        </div>
        <?php return ob_get_clean();
    }

    public function ajax_submit_news() {
        check_ajax_referer( 'tp_auth_nonce', 'tp_nonce' );

        $title    = sanitize_text_field( $_POST['post_title'] ?? '' );
        $content  = wp_kses_post( $_POST['post_content'] ?? '' );
        $name     = sanitize_text_field( $_POST['submitter_name'] ?? '' );
        $email    = sanitize_email( $_POST['submitter_email'] ?? '' );
        $company  = sanitize_text_field( $_POST['submitter_company'] ?? '' );
        $type     = sanitize_text_field( $_POST['submission_type'] ?? '' );
        $url      = esc_url_raw( $_POST['source_url'] ?? '' );
        $contact  = sanitize_text_field( $_POST['media_contact'] ?? '' );
        $image    = esc_url_raw( $_POST['image_url'] ?? '' );

        if ( empty( $title ) || empty( $content ) || empty( $name ) || empty( $email ) || empty( $type ) ) {
            wp_send_json_error( array( 'message' => 'All required fields must be filled.' ) );
        }

        // Create as pending press release
        $post_id = wp_insert_post( array(
            'post_title'   => $title,
            'post_content' => $content,
            'post_status'  => 'pending',
            'post_type'    => 'portal_press',
            'post_author'  => get_current_user_id() ?: 1,
        ) );

        if ( is_wp_error( $post_id ) ) {
            wp_send_json_error( array( 'message' => $post_id->get_error_message() ) );
        }

        // Save submission metadata
        update_post_meta( $post_id, '_tp_submission_name', $name );
        update_post_meta( $post_id, '_tp_submission_email', $email );
        update_post_meta( $post_id, '_tp_submission_company', $company );
        update_post_meta( $post_id, '_tp_submission_type', $type );
        update_post_meta( $post_id, '_tp_submission_url', $url );
        update_post_meta( $post_id, '_tp_submission_contact', $contact );
        update_post_meta( $post_id, '_tp_submission_status', 'pending' );
        update_post_meta( $post_id, '_tp_submission_ip', $_SERVER['REMOTE_ADDR'] ?? '' );

        // Set featured image if provided
        if ( ! empty( $image ) ) {
            $this->set_featured_image_from_url( $post_id, $image );
        }

        wp_send_json_success( array(
            'message' => 'Thank you! Your submission has been received and is pending editorial review.',
            'post_id' => $post_id,
        ) );
    }

    /* ─── Related Articles ─── */

    public function shortcode_related_posts() {
        ob_start();
        $this->output_related_posts();
        return ob_get_clean();
    }

    public function output_related_posts() {
        if ( ! is_singular( array( 'post', 'portal_article' ) ) ) return;

        $post_id    = get_the_ID();
        $categories = get_the_category( $post_id );
        $tags       = get_the_tags( $post_id );
        $cat_ids    = wp_list_pluck( $categories, 'term_id' );
        $tag_ids    = $tags ? wp_list_pluck( $tags, 'term_id' ) : array();

        $args = array(
            'post_type'      => array( 'post', 'portal_article' ),
            'post_status'    => 'publish',
            'posts_per_page' => 4,
            'post__not_in'   => array( $post_id ),
            'orderby'        => 'relevance',
            'no_found_rows'  => true,
        );

        $tax_query = array();
        if ( ! empty( $cat_ids ) ) {
            $tax_query[] = array( 'taxonomy' => 'category', 'field' => 'term_id', 'terms' => $cat_ids );
        }
        if ( ! empty( $tag_ids ) ) {
            $tax_query[] = array( 'taxonomy' => 'post_tag', 'field' => 'term_id', 'terms' => $tag_ids );
        }
        if ( count( $tax_query ) > 1 ) {
            $tax_query['relation'] = 'OR';
        }
        if ( ! empty( $tax_query ) ) {
            $args['tax_query'] = $tax_query; //phpcs:ignore
        }

        $query = new WP_Query( $args );

        if ( ! $query->have_posts() ) {
            // Fallback: latest posts
            $args = array(
                'post_type'      => array( 'post', 'portal_article' ),
                'post_status'    => 'publish',
                'posts_per_page' => 4,
                'post__not_in'   => array( $post_id ),
                'orderby'        => 'date',
                'order'          => 'DESC',
                'no_found_rows'  => true,
            );
            $query = new WP_Query( $args );
        }

        if ( ! $query->have_posts() ) return;
        ?>
        <section class="tp-related-section">
            <div class="tp-container">
                <div class="tp-section-header">
                    <h2 class="tp-section-header__title">Related Stories</h2>
                </div>
                <div class="tp-grid">
                    <?php while ( $query->have_posts() ) : $query->the_post(); ?>
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
        <?php
    }

    /* ─── Trending Content ─── */

    public function shortcode_trending() {
        ob_start();
        $this->output_trending();
        return ob_get_clean();
    }

    public function output_trending() {
        $trending = tp_get_trending_posts( 8 );
        if ( empty( $trending ) ) return;
        ?>
        <section class="tp-trending-section">
            <div class="tp-container">
                <div class="tp-section-header">
                    <h2 class="tp-section-header__title">Trending</h2>
                </div>
                <div class="tp-grid">
                    <?php
                    $rank = 0;
                    foreach ( $trending as $tp ) :
                        $rank++;
                        $thumb = get_the_post_thumbnail_url( $tp->ID, 'techportal-card' );
                        $cats  = get_the_category( $tp->ID );
                        $cat_name = ! empty( $cats ) ? $cats[0]->name : '';
                        $cat_link = ! empty( $cats ) ? get_category_link( $cats[0]->term_id ) : '#';
                        ?>
                        <article class="tp-trending-card">
                            <a href="<?php echo esc_url( get_permalink( $tp->ID ) ); ?>" class="tp-trending-card__link">
                                <?php if ( $thumb ) : ?>
                                    <div class="tp-trending-card__image">
                                        <img src="<?php echo esc_url( $thumb ); ?>" alt="" loading="lazy" />
                                    </div>
                                <?php endif; ?>
                                <div class="tp-trending-card__rank"><?php echo str_pad( $rank, 2, '0', STR_PAD_LEFT ); ?></div>
                                <div class="tp-trending-card__body">
                                    <?php if ( $cat_name ) : ?>
                                        <span class="tp-trending-card__cat"><?php echo esc_html( $cat_name ); ?></span>
                                    <?php endif; ?>
                                    <h3 class="tp-trending-card__title"><?php echo esc_html( $tp->post_title ); ?></h3>
                                    <span class="tp-trending-card__date"><?php echo esc_html( human_time_diff( strtotime( $tp->post_date ), current_time( 'timestamp' ) ) . ' ago' ); ?></span>
                                </div>
                            </a>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php
    }

    /* ─── Video Archive Search/Filter ─── */

    public function shortcode_video_archive() {
        ob_start(); ?>
        <div class="tp-video-archive">
            <div class="tp-video-archive__header">
                <h1>Video Archive</h1>
                <p>Search and browse all episodes from our Web Channel.</p>
            </div>
            <div class="tp-video-archive__filters">
                <div class="tp-form-group" style="flex:2;">
                    <input type="search" id="tp-vid-search" placeholder="Search videos by title, topic, or guest…" style="width:100%;padding:10px 14px;border:1px solid var(--tp-border);border-radius:var(--tp-radius-md);font-size:var(--tp-text-base);" />
                </div>
                <div class="tp-form-group" style="flex:1;">
                    <select id="tp-vid-topic" style="width:100%;padding:10px 14px;border:1px solid var(--tp-border);border-radius:var(--tp-radius-md);font-size:var(--tp-text-base);">
                        <option value="">All Topics</option>
                        <?php
                        $topics = get_terms( array( 'taxonomy' => 'portal_topic', 'hide_empty' => true ) );
                        if ( ! is_wp_error( $topics ) ) {
                            foreach ( $topics as $t ) {
                                printf( '<option value="%s">%s</option>', esc_attr( $t->slug ), esc_html( $t->name ) );
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="tp-form-group" style="flex:1;">
                    <select id="tp-vid-show" style="width:100%;padding:10px 14px;border:1px solid var(--tp-border);border-radius:var(--tp-radius-md);font-size:var(--tp-text-base);">
                        <option value="">All Shows</option>
                        <option value="TechTalk Live">TechTalk Live</option>
                        <option value="Startup Spotlight">Startup Spotlight</option>
                        <option value="Deep Dive">Deep Dive</option>
                    </select>
                </div>
            </div>
            <div id="tp-vid-results" class="tp-grid">
                <p style="color:var(--tp-muted);grid-column:1/-1;">Loading videos…</p>
            </div>
            <div id="tp-vid-pagination" class="tp-archive-pagination"></div>
        </div>
        <?php return ob_get_clean();
    }

    public function ajax_search_episodes() {
        $search = sanitize_text_field( $_GET['s'] ?? '' );
        $topic  = sanitize_text_field( $_GET['topic'] ?? '' );
        $show   = sanitize_text_field( $_GET['show'] ?? '' );
        $page   = max( 1, intval( $_GET['page'] ?? 1 ) );
        $per_page = 12;

        $args = array(
            'post_type'      => 'portal_episode',
            'post_status'    => 'publish',
            'posts_per_page' => $per_page,
            'paged'          => $page,
            'orderby'        => 'date',
            'order'          => 'DESC',
        );

        if ( ! empty( $search ) ) {
            $args['s'] = $search;
        }

        $tax_query = array();
        if ( ! empty( $topic ) ) {
            $tax_query[] = array( 'taxonomy' => 'portal_topic', 'field' => 'slug', 'terms' => $topic );
        }
        if ( ! empty( $tax_query ) ) {
            $args['tax_query'] = $tax_query; //phpcs:ignore
        }

        if ( ! empty( $show ) ) {
            $args['meta_query'] = array( //phpcs:ignore
                array( 'key' => '_tp_episode_show_name', 'value' => $show, 'compare' => 'LIKE' ),
            );
        }

        $query = new WP_Query( $args );
        $items = array();

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                $yt_id = get_post_meta( get_the_ID(), 'youtube_video_id', true );
                $guest = get_post_meta( get_the_ID(), 'guest_name', true );
                $dur   = get_post_meta( get_the_ID(), 'youtube_duration', true );
                $views = get_post_meta( get_the_ID(), 'youtube_view_count', true );
                $live  = get_post_meta( get_the_ID(), 'youtube_is_live', true );
                $upcoming = get_post_meta( get_the_ID(), 'youtube_is_upcoming', true );
                $topics = get_the_terms( get_the_ID(), 'portal_topic' );
                $topic_name = ( $topics && ! is_wp_error( $topics ) ) ? $topics[0]->name : '';

                $items[] = array(
                    'id'        => get_the_ID(),
                    'title'     => get_the_title(),
                    'url'       => get_permalink(),
                    'thumbnail' => get_the_post_thumbnail_url( get_the_ID(), 'techportal-card' ) ?: '',
                    'date'      => get_the_date( 'M j, Y' ),
                    'guest'     => $guest,
                    'duration'  => $dur,
                    'views'     => $views,
                    'topic'     => $topic_name,
                    'yt_id'     => $yt_id,
                    'is_live'   => (bool) $live,
                    'is_upcoming' => (bool) $upcoming,
                );
            }
            wp_reset_postdata();
        }

        wp_send_json_success( array(
            'items'      => $items,
            'total'      => $query->found_posts,
            'total_pages' => $query->max_num_pages,
            'page'       => $page,
        ) );
    }

    /* ─── Banner / Sponsor Widgets ─── */

    public function register_banner_widgets() {
        register_sidebar( array(
            'name'          => __( 'Banner — Top of Content', 'portal-core' ),
            'id'            => 'banner-top-content',
            'description'   => __( 'Banner ad shown above article content.', 'portal-core' ),
            'before_widget' => '<div class="tp-banner-widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<span class="screen-reader-text">',
            'after_title'   => '</span>',
        ) );

        register_sidebar( array(
            'name'          => __( 'Banner — Mid-Content', 'portal-core' ),
            'id'            => 'banner-mid-content',
            'description'   => __( 'Banner ad shown within article content.', 'portal-core' ),
            'before_widget' => '<div class="tp-banner-widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<span class="screen-reader-text">',
            'after_title'   => '</span>',
        ) );

        register_sidebar( array(
            'name'          => __( 'Banner — Sidebar', 'portal-core' ),
            'id'            => 'banner-sidebar',
            'description'   => __( 'Banner ad for sidebar placement.', 'portal-core' ),
            'before_widget' => '<div class="tp-banner-widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<h4 class="tp-banner-widget__title">',
            'after_title'   => '</h4>',
        ) );

        register_sidebar( array(
            'name'          => __( 'Banner — Footer', 'portal-core' ),
            'id'            => 'banner-footer',
            'description'   => __( 'Full-width banner above footer.', 'portal-core' ),
            'before_widget' => '<div class="tp-banner-widget tp-banner-widget--full %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<span class="screen-reader-text">',
            'after_title'   => '</span>',
        ) );
    }

    public function shortcode_banner( $atts ) {
        $atts = shortcode_atts( array( 'position' => 'top-content' ), $atts );
        ob_start();
        if ( is_active_sidebar( 'banner-' . $atts['position'] ) ) {
            dynamic_sidebar( 'banner-' . $atts['position'] );
        }
        return ob_get_clean();
    }

    public function shortcode_sponsors() {
        $sponsors = tp_get_active_sponsors();
        if ( empty( $sponsors ) ) return '';
        ob_start(); ?>
        <section class="tp-sponsors-section">
            <div class="tp-container">
                <div class="tp-sponsors-bar">
                    <span class="tp-sponsors-bar__label">Sponsored</span>
                    <div class="tp-sponsors-bar__logos">
                        <?php foreach ( $sponsors as $sponsor ) : ?>
                            <a href="<?php echo esc_url( $sponsor['url'] ); ?>" target="_blank" rel="noopener" class="tp-sponsors-bar__item">
                                <?php if ( $sponsor['image'] ) : ?>
                                    <img src="<?php echo esc_url( $sponsor['image'] ); ?>" alt="<?php echo esc_attr( $sponsor['title'] ); ?>" style="max-height:40px;" />
                                <?php else : ?>
                                    <span><?php echo esc_html( $sponsor['title'] ); ?></span>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </section>
        <?php return ob_get_clean();
    }

    /* ─── Search Enhancements ─── */

    public static function get_search_type_counts( $query ) {
        $counts = array();
        $types = array( 'post' => 'Articles', 'portal_article' => 'Articles', 'portal_startup' => 'Startups', 'portal_episode' => 'Episodes', 'portal_press' => 'Press Releases' );

        foreach ( $types as $type => $label ) {
            $args = array(
                'post_type'      => $type,
                'post_status'    => 'publish',
                's'              => $query,
                'posts_per_page' => 1,
                'no_found_rows'  => false,
            );
            $q = new WP_Query( $args );
            if ( $q->found_posts > 0 ) {
                $counts[ $label ] = ( $counts[ $label ] ?? 0 ) + $q->found_posts;
            }
            wp_reset_postdata();
        }
        return $counts;
    }

    /* ─── Admin Columns ─── */

    public function add_submission_columns( $columns ) {
        $new = array();
        foreach ( $columns as $key => $val ) {
            $new[ $key ] = $val;
            if ( $key === 'title' ) {
                $new['submission_type'] = __( 'Type', 'portal-core' );
                $new['submission_from'] = __( 'Submitted By', 'portal-core' );
                $new['submission_status'] = __( 'Review Status', 'portal-core' );
            }
        }
        return $new;
    }

    public function render_submission_columns( $column, $post_id ) {
        switch ( $column ) {
            case 'submission_type':
                echo esc_html( ucfirst( str_replace( '_', ' ', get_post_meta( $post_id, '_tp_submission_type', true ) ) ) );
                break;
            case 'submission_from':
                $name  = get_post_meta( $post_id, '_tp_submission_name', true );
                $email = get_post_meta( $post_id, '_tp_submission_email', true );
                printf( '%s<br><small>%s</small>', esc_html( $name ), esc_html( $email ) );
                break;
            case 'submission_status':
                $status = get_post_meta( $post_id, '_tp_submission_status', true ) ?: 'pending';
                $colors = array( 'pending' => '#f59e0b', 'approved' => '#16a34a', 'rejected' => '#dc2626' );
                printf(
                    '<span style="display:inline-block;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:600;background:%s20;color:%s;">%s</span>',
                    esc_attr( $colors[ $status ] ?? '#6b7280' ),
                    esc_attr( $colors[ $status ] ?? '#6b7280' ),
                    esc_html( strtoupper( $status ) )
                );
                break;
        }
    }

    public function add_sponsor_columns( $columns ) {
        $new = array();
        foreach ( $columns as $key => $val ) {
            $new[ $key ] = $val;
            if ( $key === 'title' ) {
                $new['sponsor_url']   = __( 'URL', 'portal-core' );
                $new['sponsor_slot']  = __( 'Slot', 'portal-core' );
                $new['sponsor_label'] = __( 'Label', 'portal-core' );
            }
        }
        return $new;
    }

    public function render_sponsor_columns( $column, $post_id ) {
        switch ( $column ) {
            case 'sponsor_url':
                $url = get_post_meta( $post_id, '_tp_sponsor_url', true );
                if ( $url ) printf( '<a href="%s" target="_blank">%s</a>', esc_url( $url ), esc_html( wp_parse_url( $url, PHP_URL_HOST ) ) );
                break;
            case 'sponsor_slot':
                echo esc_html( get_post_meta( $post_id, '_tp_sponsor_slot', true ) );
                break;
            case 'sponsor_label':
                echo esc_html( get_post_meta( $post_id, '_tp_sponsor_label', true ) );
                break;
        }
    }

    /* ─── Helpers ─── */

    private function set_featured_image_from_url( $post_id, $url ) {
        if ( ! function_exists( 'media_handle_sideload' ) ) {
            require_once ABSPATH . 'wp-admin/includes/media.php';
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';
        }
        $tmp = download_url( $url, 15 );
        if ( is_wp_error( $tmp ) ) return false;
        $ext = pathinfo( parse_url( $url, PHP_URL_PATH ), PATHINFO_EXTENSION ) ?: 'jpg';
        $file = array( 'name' => "submission-{$post_id}.{$ext}", 'tmp_name' => $tmp, 'error' => 0, 'size' => filesize( $tmp ) );
        $id = media_handle_sideload( $file, 0 );
        if ( ! is_wp_error( $id ) ) set_post_thumbnail( $post_id, $id );
        return true;
    }

    /* ─── Enqueue ─── */

    public function enqueue_assets() {
        wp_enqueue_style( 'portal-features', PORTAL_CORE_URL . 'assets/css/features.css', array(), PORTAL_CORE_VERSION );
        wp_enqueue_script( 'portal-features', PORTAL_CORE_URL . 'assets/js/features.js', array(), PORTAL_CORE_VERSION, true );
        wp_localize_script( 'portal-features', 'tpFeatures', array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'tp_auth_nonce' ),
        ) );
    }
}
