<?php
/**
 * Tech Portal Theme Functions
 *
 * @package TechPortal
 * @version 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'TECHPORTAL_VERSION', '2.1.0' );
define( 'TECHPORTAL_DIR', get_template_directory() );
define( 'TECHPORTAL_URI', get_template_directory_uri() );

/**
 * Theme Setup
 */
function techportal_setup() {
    add_theme_support( 'automatic-feed-links' );
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    set_post_thumbnail_size( 1200, 630, true );
    add_image_size( 'techportal-hero', 1400, 700, true );
    add_image_size( 'techportal-card', 600, 340, true );
    add_image_size( 'techportal-card-horizontal', 400, 260, true );
    add_image_size( 'techportal-compact', 200, 200, true );

    register_nav_menus( array(
        'primary' => esc_html__( 'Primary Navigation', 'techportal' ),
        'footer'  => esc_html__( 'Footer Navigation', 'techportal' ),
        'mobile'  => esc_html__( 'Mobile Navigation', 'techportal' ),
    ) );

    add_theme_support( 'html5', array(
        'search-form', 'comment-form', 'comment-list', 'gallery',
        'caption', 'style', 'script', 'navigation-widgets',
    ) );

    add_theme_support( 'custom-logo', array(
        'height' => 60, 'width' => 200, 'flex-height' => true, 'flex-width' => true,
    ) );

    add_theme_support( 'custom-background', array( 'default-color' => 'ffffff' ) );
    add_theme_support( 'customize-selective-refresh-widgets' );
    add_theme_support( 'responsive-embeds' );
    add_theme_support( 'editor-styles' );
    add_editor_style( 'assets/css/editor-style.css' );
    add_theme_support( 'align-wide' );
}
add_action( 'after_setup_theme', 'techportal_setup' );

/**
 * Enqueue scripts and styles
 */
function techportal_scripts() {
    wp_enqueue_style( 'techportal-style', get_stylesheet_uri(), array(), TECHPORTAL_VERSION );
    wp_enqueue_style( 'techportal-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap', array(), null );
    wp_enqueue_script( 'techportal-main', TECHPORTAL_URI . '/assets/js/main.js', array(), TECHPORTAL_VERSION, true );
    wp_localize_script( 'techportal-main', 'techportal', array(
        'ajaxurl' => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce( 'techportal_nonce' ),
    ) );
    if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
        wp_enqueue_script( 'comment-reply' );
    }
}
add_action( 'wp_enqueue_scripts', 'techportal_scripts' );

/**
 * Register widget areas
 */
function techportal_widgets_init() {
    register_sidebar( array(
        'name' => esc_html__( 'Sidebar', 'techportal' ), 'id' => 'sidebar-1',
        'before_widget' => '<section id="%1$s" class="widget %2$s">', 'after_widget' => '</section>',
        'before_title' => '<h3 class="widget-title">', 'after_title' => '</h3>',
    ) );
    register_sidebar( array(
        'name' => esc_html__( 'Footer Column 1', 'techportal' ), 'id' => 'footer-1',
        'before_widget' => '<section id="%1$s" class="widget %2$s">', 'after_widget' => '</section>',
        'before_title' => '<h4 class="tp-footer__heading">', 'after_title' => '</h4>',
    ) );
    register_sidebar( array(
        'name' => esc_html__( 'Footer Column 2', 'techportal' ), 'id' => 'footer-2',
        'before_widget' => '<section id="%1$s" class="widget %2$s">', 'after_widget' => '</section>',
        'before_title' => '<h4 class="tp-footer__heading">', 'after_title' => '</h4>',
    ) );
    register_sidebar( array(
        'name' => esc_html__( 'Sponsor Slots', 'techportal' ), 'id' => 'sponsors',
        'description' => esc_html__( 'Sponsor advertisement slots.', 'techportal' ),
        'before_widget' => '<div class="tp-sponsor-slot widget %2$s">', 'after_widget' => '</div>',
        'before_title' => '<span class="tp-sponsor-slot__label">', 'after_title' => '</span>',
    ) );
}
add_action( 'widgets_init', 'techportal_widgets_init' );

/** Custom excerpt length */
function techportal_excerpt_length( $length ) { return 25; }
add_filter( 'excerpt_length', 'techportal_excerpt_length' );

/** Custom excerpt more */
function techportal_excerpt_more( $more ) { return '&hellip;'; }
add_filter( 'excerpt_more', 'techportal_excerpt_more' );

/** Remove "Category:", "Tag:", etc. prefix from archive titles */
function techportal_archive_title_strip_prefix( $title ) {
    if ( is_category() ) {
        $title = single_cat_title( '', false );
    } elseif ( is_tag() ) {
        $title = single_tag_title( '', false );
    } elseif ( is_author() ) {
        $title = get_the_author();
    } elseif ( is_post_type_archive() ) {
        $title = post_type_archive_title( '', false );
    }
    return $title;
}
add_filter( 'get_the_archive_title', 'techportal_archive_title_strip_prefix' );

/** Add custom body classes */
function techportal_body_classes( $classes ) {
    if ( is_singular() ) $classes[] = 'singular';
    if ( is_singular( 'post' ) ) $classes[] = 'single-post';
    if ( is_front_page() && ! is_home() ) $classes[] = 'front-page';
    return $classes;
}
add_filter( 'body_class', 'techportal_body_classes' );

/**
 * Fallback menu if no menu is assigned to the primary location.
 */
function techportal_fallback_menu() {
    echo '<ul class="tp-nav__list">';
    $categories = get_categories( array(
        'number' => 8, 'orderby' => 'count', 'order' => 'DESC', 'hide_empty' => true,
    ) );
    foreach ( $categories as $cat ) {
        printf(
            '<li><a href="%s" class="tp-nav__link">%s</a></li>',
            esc_url( get_category_link( $cat->term_id ) ),
            esc_html( $cat->name )
        );
    }
    echo '</ul>';
}

/**
 * Customizer settings
 */
function techportal_customize_register( $wp_customize ) {
    $wp_customize->add_panel( 'techportal_panel', array(
        'title' => esc_html__( 'Tech Portal Settings', 'techportal' ), 'priority' => 30,
    ) );
    $wp_customize->add_section( 'techportal_branding', array(
        'title' => esc_html__( 'Branding', 'techportal' ), 'panel' => 'techportal_panel',
    ) );
    $wp_customize->add_setting( 'techportal_breaking_text', array(
        'default' => '', 'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'techportal_breaking_text', array(
        'label' => esc_html__( 'Breaking News Text', 'techportal' ),
        'section' => 'techportal_branding', 'type' => 'text',
    ) );
    $wp_customize->add_setting( 'techportal_newsletter_title', array(
        'default' => 'Stay Ahead in Tech', 'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'techportal_newsletter_title', array(
        'label' => esc_html__( 'Newsletter Title', 'techportal' ),
        'section' => 'techportal_branding', 'type' => 'text',
    ) );
}
add_action( 'customize_register', 'techportal_customize_register' );

/** Include theme inc files */
require_once TECHPORTAL_DIR . '/inc/template-tags.php';
require_once TECHPORTAL_DIR . '/inc/seo.php';


/* =========================================================================
 * PHASE 2 FEATURES
 * ========================================================================= */

/* ---- 1. BREAKING NEWS ---- */

function tp_add_breaking_meta_box() {
    add_meta_box( 'tp_breaking_meta_box', esc_html__( 'Breaking News', 'techportal' ),
        'tp_breaking_meta_box_callback', 'post', 'side', 'high' );
}
add_action( 'add_meta_boxes', 'tp_add_breaking_meta_box' );

function tp_breaking_meta_box_callback( $post ) {
    wp_nonce_field( 'tp_breaking_meta_nonce', 'tp_breaking_nonce' );
    $value = get_post_meta( $post->ID, '_tp_breaking', true );
    ?>
    <label for="tp_breaking">
        <input type="checkbox" id="tp_breaking" name="tp_breaking" value="1" <?php checked( $value, '1' ); ?> />
        <?php esc_html_e( 'Mark as Breaking News', 'techportal' ); ?>
    </label>
    <?php
}

function tp_save_breaking_meta( $post_id ) {
    if ( ! isset( $_POST['tp_breaking_nonce'] ) || ! wp_verify_nonce( $_POST['tp_breaking_nonce'], 'tp_breaking_meta_nonce' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;
    update_post_meta( $post_id, '_tp_breaking', isset( $_POST['tp_breaking'] ) ? '1' : '0' );
}
add_action( 'save_post', 'tp_save_breaking_meta' );

function tp_get_breaking_posts( $count = 5 ) {
    $query = new WP_Query( array(
        'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => $count,
        'meta_key' => '_tp_breaking', 'meta_value' => '1', 'orderby' => 'date', 'order' => 'DESC',
    ) );
    $posts = $query->posts;
    wp_reset_postdata();
    return $posts;
}


/* ---- 2. HERO STORY & FEATURED ---- */

function tp_add_hero_featured_meta_boxes() {
    add_meta_box( 'tp_hero_featured_meta_box', esc_html__( 'Hero & Featured', 'techportal' ),
        'tp_hero_featured_meta_box_callback', 'post', 'side', 'high' );
}
add_action( 'add_meta_boxes', 'tp_add_hero_featured_meta_boxes' );

function tp_hero_featured_meta_box_callback( $post ) {
    wp_nonce_field( 'tp_hero_featured_meta_nonce', 'tp_hero_featured_nonce' );
    $hero = get_post_meta( $post->ID, '_tp_hero_story', true );
    $featured = get_post_meta( $post->ID, '_tp_featured', true );
    ?>
    <p><label for="tp_hero_story">
        <input type="checkbox" id="tp_hero_story" name="tp_hero_story" value="1" <?php checked( $hero, '1' ); ?> />
        <?php esc_html_e( 'Hero Story', 'techportal' ); ?>
    </label></p>
    <p><label for="tp_featured">
        <input type="checkbox" id="tp_featured" name="tp_featured" value="1" <?php checked( $featured, '1' ); ?> />
        <?php esc_html_e( "Featured / Editor's Pick", 'techportal' ); ?>
    </label></p>
    <?php
}

function tp_save_hero_featured_meta( $post_id ) {
    if ( ! isset( $_POST['tp_hero_featured_nonce'] ) || ! wp_verify_nonce( $_POST['tp_hero_featured_nonce'], 'tp_hero_featured_meta_nonce' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;
    update_post_meta( $post_id, '_tp_hero_story', isset( $_POST['tp_hero_story'] ) ? '1' : '0' );
    update_post_meta( $post_id, '_tp_featured', isset( $_POST['tp_featured'] ) ? '1' : '0' );
}
add_action( 'save_post', 'tp_save_hero_featured_meta' );

function tp_get_hero_story() {
    $query = new WP_Query( array(
        'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 1,
        'meta_key' => '_tp_hero_story', 'meta_value' => '1',
    ) );
    if ( $query->have_posts() ) {
        $post = $query->posts[0];
        wp_reset_postdata();
        return $post;
    }
    // Fallback: latest published post
    $fallback = new WP_Query( array(
        'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 1,
    ) );
    if ( $fallback->have_posts() ) {
        $post = $fallback->posts[0];
        wp_reset_postdata();
        return $post;
    }
    return null;
}

function tp_get_featured_posts( $count = 4 ) {
    $query = new WP_Query( array(
        'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => $count,
        'meta_key' => '_tp_featured', 'meta_value' => '1', 'orderby' => 'date', 'order' => 'DESC',
    ) );
    $posts = $query->posts;
    wp_reset_postdata();
    return $posts;
}


/* ---- 3. STARTUP METADATA ---- */

function tp_add_startup_meta_box() {
    add_meta_box( 'tp_startup_meta_box', esc_html__( 'Startup Information', 'techportal' ),
        'tp_startup_meta_box_callback', 'post', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'tp_add_startup_meta_box' );

function tp_startup_meta_box_callback( $post ) {
    wp_nonce_field( 'tp_startup_meta_nonce', 'tp_startup_nonce' );
    $fields = array(
        'industry' => 'Industry', 'funding_stage' => 'Funding Stage',
        'location' => 'Location', 'website' => 'Website',
        'founded_year' => 'Founded Year', 'logo_url' => 'Logo URL',
    );
    echo '<table class="form-table"><tbody>';
    foreach ( $fields as $key => $label ) {
        $value = get_post_meta( $post->ID, '_tp_startup_' . $key, true );
        printf(
            '<tr><th><label for="tp_startup_%s">%s</label></th><td><input type="text" id="tp_startup_%s" name="tp_startup_%s" value="%s" class="regular-text" /></td></tr>',
            esc_attr( $key ), esc_html( $label ), esc_attr( $key ), esc_attr( $key ), esc_attr( $value )
        );
    }
    echo '</tbody></table>';
}

function tp_save_startup_meta( $post_id ) {
    if ( ! isset( $_POST['tp_startup_nonce'] ) || ! wp_verify_nonce( $_POST['tp_startup_nonce'], 'tp_startup_meta_nonce' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;
    $fields = array( 'industry', 'funding_stage', 'location', 'website', 'founded_year', 'logo_url' );
    foreach ( $fields as $key ) {
        if ( isset( $_POST['tp_startup_' . $key] ) ) {
            update_post_meta( $post_id, '_tp_startup_' . $key, sanitize_text_field( $_POST['tp_startup_' . $key] ) );
        }
    }
}
add_action( 'save_post', 'tp_save_startup_meta' );

function tp_get_startup_meta( $post_id ) {
    return array(
        'industry'      => get_post_meta( $post_id, '_tp_startup_industry', true ),
        'funding_stage' => get_post_meta( $post_id, '_tp_startup_funding_stage', true ),
        'location'      => get_post_meta( $post_id, '_tp_startup_location', true ),
        'website'       => get_post_meta( $post_id, '_tp_startup_website', true ),
        'founded_year'  => get_post_meta( $post_id, '_tp_startup_founded_year', true ),
        'logo_url'      => get_post_meta( $post_id, '_tp_startup_logo_url', true ),
    );
}


/* ---- 4. EPISODE METADATA ---- */

function tp_add_episode_meta_box() {
    add_meta_box( 'tp_episode_meta_box', esc_html__( 'Episode Information', 'techportal' ),
        'tp_episode_meta_box_callback', 'post', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'tp_add_episode_meta_box' );

function tp_episode_meta_box_callback( $post ) {
    wp_nonce_field( 'tp_episode_meta_nonce', 'tp_episode_nonce' );
    $fields = array(
        'youtube_id'     => esc_html__( 'YouTube Video ID', 'techportal' ),
        'guest_name'     => esc_html__( 'Guest Name', 'techportal' ),
        'guest_title'    => esc_html__( 'Guest Title', 'techportal' ),
        'guest_company'  => esc_html__( 'Guest Company / Startup', 'techportal' ),
        'show_name'      => esc_html__( 'Show Name', 'techportal' ),
        'topic'          => esc_html__( 'Topic', 'techportal' ),
        'is_featured'    => esc_html__( 'Featured Episode', 'techportal' ),
    );
    echo '<table class="form-table"><tbody>';
    foreach ( $fields as $key => $label ) {
        if ( $key === 'is_featured' ) {
            $value = get_post_meta( $post->ID, '_tp_episode_' . $key, true );
            printf(
                '<tr><th><label for="tp_episode_%s">%s</label></th><td><input type="checkbox" id="tp_episode_%s" name="tp_episode_%s" value="1" %s /> %s</td></tr>',
                esc_attr( $key ), esc_html( $label ), esc_attr( $key ), esc_attr( $key ),
                checked( $value, '1', false ),
                esc_html__( 'Mark as featured on Web Channel page', 'techportal' )
            );
        } else {
            $value = get_post_meta( $post->ID, '_tp_episode_' . $key, true );
            printf(
                '<tr><th><label for="tp_episode_%s">%s</label></th><td><input type="text" id="tp_episode_%s" name="tp_episode_%s" value="%s" class="regular-text" /></td></tr>',
                esc_attr( $key ), esc_html( $label ), esc_attr( $key ), esc_attr( $key ), esc_attr( $value )
            );
        }
    }

    // Show YouTube sync status (read-only)
    $yt_synced = get_post_meta( $post->ID, 'youtube_video_id', true );
    $yt_views  = get_post_meta( $post->ID, 'youtube_view_count', true );
    $yt_duration = get_post_meta( $post->ID, 'youtube_duration', true );
    $yt_live   = get_post_meta( $post->ID, 'youtube_is_live', true );
    $yt_upcoming = get_post_meta( $post->ID, 'youtube_is_upcoming', true );

    if ( $yt_synced ) {
        echo '<tr><th>' . esc_html__( 'YouTube Sync', 'techportal' ) . '</th><td>';
        echo '<div style="padding:8px 12px;background:#f0f0f1;border-radius:4px;font-size:13px;">';
        echo '✅ ' . esc_html__( 'Synced from YouTube', 'techportal' );
        if ( $yt_live ) echo ' · <span style="color:#c62828;font-weight:600;">🔴 LIVE</span>';
        if ( $yt_upcoming && ! $yt_live ) echo ' · <span style="color:#e65100;font-weight:600;">🕐 UPCOMING</span>';
        if ( $yt_duration ) echo ' · ⏱ ' . esc_html( $yt_duration );
        if ( $yt_views ) echo ' · 👁 ' . number_format( $yt_views ) . ' views';
        echo '</div>';
        echo '</td></tr>';
    }

    echo '</tbody></table>';
}

function tp_save_episode_meta( $post_id ) {
    if ( ! isset( $_POST['tp_episode_nonce'] ) || ! wp_verify_nonce( $_POST['tp_episode_nonce'], 'tp_episode_meta_nonce' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;
    $fields = array( 'youtube_id', 'guest_name', 'guest_title', 'guest_company', 'show_name', 'topic' );
    foreach ( $fields as $key ) {
        if ( isset( $_POST['tp_episode_' . $key] ) ) {
            update_post_meta( $post_id, '_tp_episode_' . $key, sanitize_text_field( $_POST['tp_episode_' . $key] ) );
        }
    }
    // Featured checkbox
    update_post_meta( $post_id, '_tp_episode_is_featured', isset( $_POST['tp_episode_is_featured'] ) ? '1' : '0' );
}
add_action( 'save_post', 'tp_save_episode_meta' );

function tp_get_episode_meta( $post_id ) {
    return array(
        'youtube_id'    => get_post_meta( $post_id, '_tp_episode_youtube_id', true ),
        'guest_name'    => get_post_meta( $post_id, '_tp_episode_guest_name', true ),
        'guest_company' => get_post_meta( $post_id, '_tp_episode_guest_company', true ),
        'show_name'     => get_post_meta( $post_id, '_tp_episode_show_name', true ),
        'topic'         => get_post_meta( $post_id, '_tp_episode_topic', true ),
    );
}


/* ---- 5. SPONSOR SYSTEM ---- */

function tp_register_sponsor_post_type() {
    register_post_type( 'tp_sponsor', array(
        'labels' => array(
            'name' => esc_html__( 'Sponsors', 'techportal' ),
            'singular_name' => esc_html__( 'Sponsor', 'techportal' ),
        ),
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 25,
        'menu_icon' => 'dashicons-money-alt',
        'supports' => array( 'title', 'thumbnail' ),
        'has_archive' => false,
    ) );
}
add_action( 'init', 'tp_register_sponsor_post_type' );

function tp_add_sponsor_meta_box() {
    add_meta_box( 'tp_sponsor_meta_box', esc_html__( 'Sponsor Details', 'techportal' ),
        'tp_sponsor_meta_box_callback', 'tp_sponsor', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'tp_add_sponsor_meta_box' );

function tp_sponsor_meta_box_callback( $post ) {
    wp_nonce_field( 'tp_sponsor_meta_nonce', 'tp_sponsor_nonce' );
    $url = get_post_meta( $post->ID, '_tp_sponsor_url', true );
    $label = get_post_meta( $post->ID, '_tp_sponsor_label', true );
    $slot = get_post_meta( $post->ID, '_tp_sponsor_slot', true );
    ?>
    <table class="form-table"><tbody>
        <tr><th><label for="tp_sponsor_url"><?php esc_html_e( 'Sponsor URL', 'techportal' ); ?></label></th>
            <td><input type="url" id="tp_sponsor_url" name="tp_sponsor_url" value="<?php echo esc_attr( $url ); ?>" class="regular-text" /></td></tr>
        <tr><th><label for="tp_sponsor_label"><?php esc_html_e( 'Label', 'techportal' ); ?></label></th>
            <td><select id="tp_sponsor_label" name="tp_sponsor_label">
                <option value="Sponsored" <?php selected( $label, 'Sponsored' ); ?>>Sponsored</option>
                <option value="Advertisement" <?php selected( $label, 'Advertisement' ); ?>>Advertisement</option>
                <option value="Partner" <?php selected( $label, 'Partner' ); ?>>Partner</option>
            </select></td></tr>
        <tr><th><label for="tp_sponsor_slot"><?php esc_html_e( 'Slot #', 'techportal' ); ?></label></th>
            <td><input type="number" id="tp_sponsor_slot" name="tp_sponsor_slot" value="<?php echo esc_attr( $slot ); ?>" min="1" max="10" style="width:80px;" /></td></tr>
    </tbody></table>
    <?php
}

function tp_save_sponsor_meta( $post_id ) {
    if ( ! isset( $_POST['tp_sponsor_nonce'] ) || ! wp_verify_nonce( $_POST['tp_sponsor_nonce'], 'tp_sponsor_meta_nonce' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;
    update_post_meta( $post_id, '_tp_sponsor_url', esc_url_raw( $_POST['tp_sponsor_url'] ?? '' ) );
    update_post_meta( $post_id, '_tp_sponsor_label', sanitize_text_field( $_POST['tp_sponsor_label'] ?? 'Sponsored' ) );
    update_post_meta( $post_id, '_tp_sponsor_slot', intval( $_POST['tp_sponsor_slot'] ?? 1 ) );
}
add_action( 'save_post_tp_sponsor', 'tp_save_sponsor_meta' );

function tp_get_active_sponsors() {
    $query = new WP_Query( array(
        'post_type' => 'tp_sponsor', 'post_status' => 'publish', 'posts_per_page' => 6,
        'orderby' => 'meta_value_num', 'meta_key' => '_tp_sponsor_slot', 'order' => 'ASC',
    ) );
    $sponsors = array();
    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            $thumb = get_the_post_thumbnail_url( get_the_ID(), 'medium' );
            $sponsors[] = array(
                'id'    => get_the_ID(),
                'title' => get_the_title(),
                'url'   => get_post_meta( get_the_ID(), '_tp_sponsor_url', true ),
                'label' => get_post_meta( get_the_ID(), '_tp_sponsor_label', true ) ?: 'Sponsored',
                'image' => $thumb ?: '',
            );
        }
        wp_reset_postdata();
    }
    return $sponsors;
}


/* ---- 6. TRENDING CONFIGURATION ---- */

function tp_get_trending_posts( $count = 8 ) {
    $manual = get_option( 'tp_trending_posts', array() );
    if ( ! empty( $manual ) && is_array( $manual ) ) {
        $query = new WP_Query( array(
            'post_type' => 'post', 'post_status' => 'publish',
            'post__in' => $manual, 'orderby' => 'post__in', 'posts_per_page' => $count,
        ) );
        $posts = $query->posts;
        wp_reset_postdata();
        return $posts;
    }
    // Fallback: by comment count
    $query = new WP_Query( array(
        'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => $count,
        'orderby' => 'comment_count', 'order' => 'DESC',
        'date_query' => array( array( 'after' => '14 days ago' ) ),
    ) );
    $posts = $query->posts;
    wp_reset_postdata();
    return $posts;
}


/* ---- 7. NEWSLETTER AJAX ---- */

function tp_newsletter_subscribe() {
    check_ajax_referer( 'techportal_nonce', 'nonce' );
    $email = sanitize_email( $_POST['email'] ?? '' );
    if ( ! is_email( $email ) ) {
        wp_send_json_error( array( 'message' => esc_html__( 'Please enter a valid email address.', 'techportal' ) ) );
    }
    $subscribers = get_option( 'tp_newsletter_subscribers', array() );
    if ( in_array( $email, $subscribers, true ) ) {
        wp_send_json_error( array( 'message' => esc_html__( 'You are already subscribed.', 'techportal' ) ) );
    }
    $subscribers[] = $email;
    update_option( 'tp_newsletter_subscribers', $subscribers );
    wp_send_json_success( array( 'message' => esc_html__( 'Successfully subscribed!', 'techportal' ) ) );
}
add_action( 'wp_ajax_tp_newsletter_subscribe', 'tp_newsletter_subscribe' );
add_action( 'wp_ajax_nopriv_tp_newsletter_subscribe', 'tp_newsletter_subscribe' );
