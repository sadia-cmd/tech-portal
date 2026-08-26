<?php
/**
 * Admin Enhancements
 *
 * Dashboard customizations and admin utilities.
 *
 * @package PortalCore
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Portal_Admin {

    private static $instance = null;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_pages' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_filter( 'manage_posts_columns', array( $this, 'add_reading_time_column' ) );
        add_action( 'manage_posts_custom_column', array( $this, 'display_reading_time_column' ), 10, 2 );
    }

    /**
     * Add admin pages
     */
    public function add_admin_pages() {
        add_menu_page(
            __( 'Portal Settings', 'portal-core' ),
            __( 'Portal', 'portal-core' ),
            'manage_options',
            'portal-core',
            array( $this, 'settings_page' ),
            'dashicons-admin-generic',
            3
        );
    }

    /**
     * Settings page callback
     */
    public function settings_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Portal Core Settings', 'portal-core' ); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'portal_core_group' );
                do_settings_sections( 'portal-core-settings' );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting( 'portal_core_group', 'portal_core_settings', array( $this, 'sanitize_settings' ) );

        add_settings_section(
            'portal_features',
            __( 'Feature Flags', 'portal-core' ),
            null,
            'portal-core-settings'
        );

        $features = array(
            'startups'       => __( 'Startup Profiles', 'portal-core' ),
            'founders'       => __( 'Founder Profiles', 'portal-core' ),
            'funding'        => __( 'Funding Tracker', 'portal-core' ),
            'web_channel'    => __( 'Web Channel / Episodes', 'portal-core' ),
            'press_releases' => __( 'Press Releases', 'portal-core' ),
            'sponsors'       => __( 'Sponsor Management', 'portal-core' ),
            'membership'     => __( 'Membership System', 'portal-core' ),
            'bookmarks'      => __( 'User Bookmarks', 'portal-core' ),
            'newsletter'     => __( 'Newsletter Integration', 'portal-core' ),
        );

        foreach ( $features as $key => $label ) {
            add_settings_field(
                'portal_feature_' . $key,
                $label,
                array( $this, 'render_checkbox' ),
                'portal-core-settings',
                'portal_features',
                array( 'feature' => $key, 'label' => $label )
            );
        }
    }

    /**
     * Render checkbox field
     */
    public function render_checkbox( $args ) {
        $settings = get_option( 'portal_core_settings', array() );
        $features = $settings['features'] ?? array();
        $checked = isset( $features[ $args['feature'] ] ) && $features[ $args['feature'] ];
        printf(
            '<input type="checkbox" name="portal_core_settings[features][%s]" value="1" %s />',
            esc_attr( $args['feature'] ),
            checked( $checked, true, false )
        );
    }

    /**
     * Sanitize settings
     */
    public function sanitize_settings( $input ) {
        $sanitized = array();
        $sanitized['portal_version'] = PORTAL_CORE_VERSION;
        $sanitized['features'] = array();
        
        if ( isset( $input['features'] ) && is_array( $input['features'] ) ) {
            foreach ( $input['features'] as $key => $value ) {
                $sanitized['features'][ sanitize_key( $key ) ] = ! empty( $value );
            }
        }
        
        return $sanitized;
    }

    /**
     * Add reading time column to posts list
     */
    public function add_reading_time_column( $columns ) {
        $columns['reading_time'] = __( 'Read Time', 'portal-core' );
        return $columns;
    }

    /**
     * Display reading time in posts list
     */
    public function display_reading_time_column( $column, $post_id ) {
        if ( 'reading_time' === $column ) {
            $content = get_post_field( 'post_content', $post_id );
            $minutes = Portal_Helpers::reading_time( $content );
            printf( '%d min', $minutes );
        }
    }
}
