<?php
/**
 * REST API Extensions
 *
 * Custom REST endpoints for the portal.
 *
 * @package PortalCore
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Portal_REST {

    private static $instance = null;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }

    public function register_routes() {
        // Trending posts
        register_rest_route( 'portal/v1', '/trending', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'get_trending' ),
            'permission_callback' => '__return_true',
            'args'                => array(
                'limit' => array(
                    'default'           => 10,
                    'sanitize_callback' => 'absint',
                ),
            ),
        ) );

        // Latest news feed
        register_rest_route( 'portal/v1', '/latest', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'get_latest' ),
            'permission_callback' => '__return_true',
            'args'                => array(
                'limit' => array(
                    'default'           => 20,
                    'sanitize_callback' => 'absint',
                ),
            ),
        ) );

        // Startup spotlight
        register_rest_route( 'portal/v1', '/startup-spotlight', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'get_startup_spotlight' ),
            'permission_callback' => '__return_true',
        ) );

        // Portal settings (public subset)
        register_rest_route( 'portal/v1', '/settings', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'get_public_settings' ),
            'permission_callback' => '__return_true',
        ) );
    }

    /**
     * Get trending posts (by comment count + recent views)
     */
    public function get_trending( $request ) {
        $limit = $request->get_param( 'limit' );
        
        $args = array(
            'post_type'      => array( 'post', 'portal_article' ),
            'post_status'    => 'publish',
            'posts_per_page' => $limit,
            'orderby'        => 'comment_count',
            'order'          => 'DESC',
            'date_query'     => array(
                array( 'after' => '7 days ago' ),
            ),
        );

        $query = new WP_Query( $args );
        $posts = array();

        foreach ( $query->posts as $post ) {
            $posts[] = array(
                'id'         => $post->ID,
                'title'      => $post->post_title,
                'excerpt'    => wp_trim_words( $post->post_content, 20 ),
                'permalink'  => get_permalink( $post->ID ),
                'date'       => get_the_date( 'c', $post->ID ),
                'thumbnail'  => get_the_post_thumbnail_url( $post->ID, 'techportal-card' ),
                'category'   => get_the_category( $post->ID )[0]->name ?? '',
                'reading_time' => Portal_Helpers::reading_time( $post->post_content ),
            );
        }

        return rest_ensure_response( $posts );
    }

    /**
     * Get latest news (chronological)
     */
    public function get_latest( $request ) {
        $limit = $request->get_param( 'limit' );
        
        $args = array(
            'post_type'      => array( 'post', 'portal_article' ),
            'post_status'    => 'publish',
            'posts_per_page' => $limit,
            'orderby'        => 'date',
            'order'          => 'DESC',
        );

        $query = new WP_Query( $args );
        $posts = array();

        foreach ( $query->posts as $post ) {
            $posts[] = array(
                'id'        => $post->ID,
                'title'     => $post->post_title,
                'permalink' => get_permalink( $post->ID ),
                'date'      => get_the_date( 'c', $post->ID ),
                'time_ago'  => Portal_Helpers::reading_time( '' ) . ' ago', // simplified
                'category'  => get_the_category( $post->ID )[0]->name ?? '',
            );
        }

        return rest_ensure_response( $posts );
    }

    /**
     * Get startup spotlight (featured startup)
     */
    public function get_startup_spotlight() {
        $args = array(
            'post_type'      => 'portal_startup',
            'post_status'    => 'publish',
            'posts_per_page' => 1,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'meta_query'     => array(
                array(
                    'key'   => '_portal_featured',
                    'value' => '1',
                ),
            ),
        );

        $query = new WP_Query( $args );
        
        if ( ! $query->have_posts() ) {
            // Fallback: get most recent startup
            $args['meta_query'] = array();
            $query = new WP_Query( $args );
        }

        if ( $query->have_posts() ) {
            $post = $query->posts[0];
            return rest_ensure_response( array(
                'id'        => $post->ID,
                'title'     => $post->post_title,
                'content'   => apply_filters( 'the_content', $post->post_content ),
                'excerpt'   => $post->post_excerpt,
                'permalink' => get_permalink( $post->ID ),
                'thumbnail' => get_the_post_thumbnail_url( $post->ID, 'large' ),
            ) );
        }

        return rest_ensure_response( array() );
    }

    /**
     * Get public settings
     */
    public function get_public_settings() {
        return rest_ensure_response( array(
            'site_name'    => get_bloginfo( 'name' ),
            'description'  => get_bloginfo( 'description' ),
            'features'     => Portal_Helpers::get_settings()['features'] ?? array(),
            'version'      => PORTAL_CORE_VERSION,
        ) );
    }
}
