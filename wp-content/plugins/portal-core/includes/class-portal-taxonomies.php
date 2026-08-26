<?php
/**
 * Custom Taxonomies
 *
 * @package PortalCore
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Portal_Taxonomies {

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
        $this->register_topic();
        $this->register_startup_stage();
        $this->register_funding_round();
    }

    /**
     * Topic taxonomy — replaces/extends categories for all post types
     */
    private function register_topic() {
        register_taxonomy( 'portal_topic', array( 'post', 'portal_article', 'portal_press' ), array(
            'labels' => array(
                'name'              => __( 'Topics', 'portal-core' ),
                'singular_name'     => __( 'Topic', 'portal-core' ),
                'search_items'      => __( 'Search Topics', 'portal-core' ),
                'all_items'         => __( 'All Topics', 'portal-core' ),
                'parent_item'       => __( 'Parent Topic', 'portal-core' ),
                'parent_item_colon' => __( 'Parent Topic:', 'portal-core' ),
                'edit_item'         => __( 'Edit Topic', 'portal-core' ),
                'update_item'       => __( 'Update Topic', 'portal-core' ),
                'add_new_item'      => __( 'Add New Topic', 'portal-core' ),
                'new_item_name'     => __( 'New Topic Name', 'portal-core' ),
                'menu_name'         => __( 'Topics', 'portal-core' ),
            ),
            'hierarchical'      => true,
            'public'            => true,
            'show_in_rest'      => true,
            'show_admin_column' => true,
            'rewrite'           => array( 'slug' => 'topic' ),
        ) );
    }

    /**
     * Startup Stage — Seed, Pre-Seed, Series A, etc.
     */
    private function register_startup_stage() {
        if ( ! Portal_Helpers::is_feature_enabled( 'startups' ) ) return;

        register_taxonomy( 'startup_stage', array( 'portal_startup' ), array(
            'labels' => array(
                'name'          => __( 'Startup Stages', 'portal-core' ),
                'singular_name' => __( 'Stage', 'portal-core' ),
                'add_new_item'  => __( 'Add New Stage', 'portal-core' ),
            ),
            'hierarchical'      => true,
            'public'            => true,
            'show_in_rest'      => true,
            'show_admin_column' => true,
            'rewrite'           => array( 'slug' => 'stage' ),
        ) );
    }

    /**
     * Funding Round — Pre-Seed, Seed, Series A, B, C, etc.
     */
    private function register_funding_round() {
        if ( ! Portal_Helpers::is_feature_enabled( 'funding' ) ) return;

        register_taxonomy( 'funding_round', array( 'portal_startup', 'portal_article' ), array(
            'labels' => array(
                'name'          => __( 'Funding Rounds', 'portal-core' ),
                'singular_name' => __( 'Round', 'portal-core' ),
                'add_new_item'  => __( 'Add New Round', 'portal-core' ),
            ),
            'hierarchical'      => true,
            'public'            => true,
            'show_in_rest'      => true,
            'show_admin_column' => true,
            'rewrite'           => array( 'slug' => 'funding' ),
        ) );
    }
}
