<?php
/**
 * Tech Portal Theme Functions
 *
 * @package TechPortal
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'TECHPORTAL_VERSION', '1.0.0' );
define( 'TECHPORTAL_DIR', get_template_directory() );
define( 'TECHPORTAL_URI', get_template_directory_uri() );

/**
 * Theme Setup
 */
function techportal_setup() {
    // Add default posts and comments RSS feed links to head
    add_theme_support( 'automatic-feed-links' );

    // Let WordPress manage the document title
    add_theme_support( 'title-tag' );

    // Enable support for Post Thumbnails on posts and pages
    add_theme_support( 'post-thumbnails' );
    set_post_thumbnail_size( 1200, 630, true );
    add_image_size( 'techportal-hero', 1400, 700, true );
    add_image_size( 'techportal-card', 600, 340, true );
    add_image_size( 'techportal-card-horizontal', 400, 260, true );
    add_image_size( 'techportal-compact', 200, 200, true );

    // Register navigation menus
    register_nav_menus( array(
        'primary'   => esc_html__( 'Primary Navigation', 'techportal' ),
        'footer'    => esc_html__( 'Footer Navigation', 'techportal' ),
        'mobile'    => esc_html__( 'Mobile Navigation', 'techportal' ),
    ) );

    // Switch default core markup to output valid HTML5
    add_theme_support( 'html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
        'navigation-widgets',
    ) );

    // Add support for core custom logo
    add_theme_support( 'custom-logo', array(
        'height'      => 60,
        'width'       => 200,
        'flex-height' => true,
        'flex-width'  => true,
    ) );

    // Add support for custom background
    add_theme_support( 'custom-background', array(
        'default-color' => 'ffffff',
    ) );

    // Add support for selective refresh for widgets
    add_theme_support( 'customize-selective-refresh-widgets' );

    // Add support for responsive embeds
    add_theme_support( 'responsive-embeds' );

    // Add support for editor styles
    add_theme_support( 'editor-styles' );
    add_editor_style( 'assets/css/editor-style.css' );

    // Wide and full alignment
    add_theme_support( 'align-wide' );
}
add_action( 'after_setup_theme', 'techportal_setup' );

/**
 * Enqueue scripts and styles
 */
function techportal_scripts() {
    // Main stylesheet
    wp_enqueue_style(
        'techportal-style',
        get_stylesheet_uri(),
        array(),
        TECHPORTAL_VERSION
    );

    // Google Fonts - Inter
    wp_enqueue_style(
        'techportal-fonts',
        'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap',
        array(),
        null
    );

    // Main JS
    wp_enqueue_script(
        'techportal-main',
        TECHPORTAL_URI . '/assets/js/main.js',
        array(),
        TECHPORTAL_VERSION,
        true
    );

    // Localize script for AJAX etc.
    wp_localize_script( 'techportal-main', 'techportal', array(
        'ajaxurl' => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce( 'techportal_nonce' ),
    ) );

    // Comment reply script
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
        'name'          => esc_html__( 'Sidebar', 'techportal' ),
        'id'            => 'sidebar-1',
        'description'   => esc_html__( 'Add widgets here.', 'techportal' ),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ) );

    register_sidebar( array(
        'name'          => esc_html__( 'Footer Column 1', 'techportal' ),
        'id'            => 'footer-1',
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h4 class="tp-footer__heading">',
        'after_title'   => '</h4>',
    ) );

    register_sidebar( array(
        'name'          => esc_html__( 'Footer Column 2', 'techportal' ),
        'id'            => 'footer-2',
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h4 class="tp-footer__heading">',
        'after_title'   => '</h4>',
    ) );

    register_sidebar( array(
        'name'          => esc_html__( 'Sponsor Slots', 'techportal' ),
        'id'            => 'sponsors',
        'description'   => esc_html__( 'Sponsor advertisement slots.', 'techportal' ),
        'before_widget' => '<div class="tp-sponsor-slot widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<span class="tp-sponsor-slot__label">',
        'after_title'   => '</span>',
    ) );
}
add_action( 'widgets_init', 'techportal_widgets_init' );

/**
 * Custom excerpt length
 */
function techportal_excerpt_length( $length ) {
    return 25;
}
add_filter( 'excerpt_length', 'techportal_excerpt_length' );

/**
 * Custom excerpt more
 */
function techportal_excerpt_more( $more ) {
    return '&hellip;';
}
add_filter( 'excerpt_more', 'techportal_excerpt_more' );

/**
 * Add custom body classes
 */
function techportal_body_classes( $classes ) {
    if ( is_singular() ) {
        $classes[] = 'singular';
    }
    if ( is_singular( 'post' ) ) {
        $classes[] = 'single-post';
    }
    if ( is_front_page() && ! is_home() ) {
        $classes[] = 'front-page';
    }
    return $classes;
}
add_filter( 'body_class', 'techportal_body_classes' );

/**
 * Customizer settings placeholder
 */
function techportal_customize_register( $wp_customize ) {
    // Panel
    $wp_customize->add_panel( 'techportal_panel', array(
        'title'    => esc_html__( 'Tech Portal Settings', 'techportal' ),
        'priority' => 30,
    ) );

    // Section: Branding
    $wp_customize->add_section( 'techportal_branding', array(
        'title' => esc_html__( 'Branding', 'techportal' ),
        'panel' => 'techportal_panel',
    ) );

    // Breaking news text
    $wp_customize->add_setting( 'techportal_breaking_text', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'techportal_breaking_text', array(
        'label'   => esc_html__( 'Breaking News Text', 'techportal' ),
        'section' => 'techportal_branding',
        'type'    => 'text',
    ) );

    // Newsletter CTA
    $wp_customize->add_setting( 'techportal_newsletter_title', array(
        'default'           => 'Stay Ahead in Tech',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'techportal_newsletter_title', array(
        'label'   => esc_html__( 'Newsletter Title', 'techportal' ),
        'section' => 'techportal_branding',
        'type'    => 'text',
    ) );
}
add_action( 'customize_register', 'techportal_customize_register' );

/**
 * Include theme inc files
 */
require_once TECHPORTAL_DIR . '/inc/template-tags.php';
require_once TECHPORTAL_DIR . '/inc/seo.php';
