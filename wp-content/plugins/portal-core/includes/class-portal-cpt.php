<?php
/**
 * Custom Post Types
 *
 * Registers all custom post types for the portal.
 *
 * @package PortalCore
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Portal_CPT {

    private static $instance = null;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_action( 'init', array( $this, 'register' ) );
    }

    public function register() {
        $this->register_article();
        $this->register_startup();
        $this->register_founder();
        $this->register_episode();
        $this->register_press_release();
    }

    /**
     * Articles — the primary content type
     */
    private function register_article() {
        register_post_type( 'portal_article', array(
            'labels' => array(
                'name'               => __( 'Articles', 'portal-core' ),
                'singular_name'      => __( 'Article', 'portal-core' ),
                'add_new'            => __( 'Add New Article', 'portal-core' ),
                'add_new_item'       => __( 'Add New Article', 'portal-core' ),
                'edit_item'          => __( 'Edit Article', 'portal-core' ),
                'all_items'          => __( 'All Articles', 'portal-core' ),
                'search_items'       => __( 'Search Articles', 'portal-core' ),
                'not_found'          => __( 'No articles found.', 'portal-core' ),
            ),
            'public'       => true,
            'has_archive'  => true,
            'rewrite'      => array( 'slug' => 'article' ),
            'menu_icon'    => 'dashicons-admin-post',
            'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt', 'comments', 'revisions', 'author' ),
            'show_in_rest' => true,
            'show_in_menu' => true,
        ) );
    }

    /**
     * Startups — company profiles
     */
    private function register_startup() {
        if ( ! Portal_Helpers::is_feature_enabled( 'startups' ) ) return;

        register_post_type( 'portal_startup', array(
            'labels' => array(
                'name'               => __( 'Startups', 'portal-core' ),
                'singular_name'      => __( 'Startup', 'portal-core' ),
                'add_new'            => __( 'Add New Startup', 'portal-core' ),
                'add_new_item'       => __( 'Add New Startup Profile', 'portal-core' ),
                'edit_item'          => __( 'Edit Startup', 'portal-core' ),
                'all_items'          => __( 'All Startups', 'portal-core' ),
            ),
            'public'       => true,
            'has_archive'  => true,
            'rewrite'      => array( 'slug' => 'startup' ),
            'menu_icon'    => 'dashicons-building',
            'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
            'show_in_rest' => true,
            'menu_position'=> 5,
        ) );
    }

    /**
     * Founders — people profiles
     */
    private function register_founder() {
        if ( ! Portal_Helpers::is_feature_enabled( 'founders' ) ) return;

        register_post_type( 'portal_founder', array(
            'labels' => array(
                'name'               => __( 'Founders', 'portal-core' ),
                'singular_name'      => __( 'Founder', 'portal-core' ),
                'add_new'            => __( 'Add New Founder', 'portal-core' ),
                'add_new_item'       => __( 'Add New Founder Profile', 'portal-core' ),
                'edit_item'          => __( 'Edit Founder', 'portal-core' ),
                'all_items'          => __( 'All Founders', 'portal-core' ),
            ),
            'public'       => true,
            'has_archive'  => true,
            'rewrite'      => array( 'slug' => 'founder' ),
            'menu_icon'    => 'dashicons-businessman',
            'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
            'show_in_rest' => true,
            'menu_position'=> 6,
        ) );
    }

    /**
     * Episodes — Web Channel video content
     */
    private function register_episode() {
        if ( ! Portal_Helpers::is_feature_enabled( 'web_channel' ) ) return;

        register_post_type( 'portal_episode', array(
            'labels' => array(
                'name'               => __( 'Episodes', 'portal-core' ),
                'singular_name'      => __( 'Episode', 'portal-core' ),
                'add_new'            => __( 'Add New Episode', 'portal-core' ),
                'add_new_item'       => __( 'Add New Episode', 'portal-core' ),
                'edit_item'          => __( 'Edit Episode', 'portal-core' ),
                'all_items'          => __( 'All Episodes', 'portal-core' ),
            ),
            'public'       => true,
            'has_archive'  => true,
            'rewrite'      => array( 'slug' => 'episode' ),
            'menu_icon'    => 'dashicons-video-alt3',
            'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ),
            'show_in_rest' => true,
            'menu_position'=> 7,
        ) );
    }

    /**
     * Press Releases
     */
    private function register_press_release() {
        if ( ! Portal_Helpers::is_feature_enabled( 'press_releases' ) ) return;

        register_post_type( 'portal_press', array(
            'labels' => array(
                'name'               => __( 'Press Releases', 'portal-core' ),
                'singular_name'      => __( 'Press Release', 'portal-core' ),
                'add_new'            => __( 'Add New Press Release', 'portal-core' ),
                'add_new_item'       => __( 'Add New Press Release', 'portal-core' ),
                'edit_item'          => __( 'Edit Press Release', 'portal-core' ),
                'all_items'          => __( 'All Press Releases', 'portal-core' ),
            ),
            'public'       => true,
            'has_archive'  => true,
            'rewrite'      => array( 'slug' => 'press-release' ),
            'menu_icon'    => 'dashicons-megaphone',
            'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
            'show_in_rest' => true,
            'menu_position'=> 8,
        ) );
    }
}
