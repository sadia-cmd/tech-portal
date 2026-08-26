<?php
/**
 * News Radar — Main Orchestrator
 *
 * Wires DB, Fetcher, Admin, and Cron together.
 * Loaded by portal-core plugin.
 *
 * @package PortalCore
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Portal_News_Radar {

    private static $instance = null;

    public static function instance() {
        if ( null === self::$instance ) self::$instance = new self();
        return self::$instance;
    }

    private function __construct() {
        // Create DB table on load (safe — dbDelta is idempotent)
        add_action( 'init', array( $this, 'maybe_create_table' ), 1 );

        // Load admin features
        if ( is_admin() ) {
            Portal_News_Radar_Admin::instance();
        }

        // Schedule cron if not already scheduled
        add_action( 'init', array( $this, 'schedule_cron' ) );

        // Load GNEWS_API_KEY from .env if not already defined
        if ( ! defined( 'GNEWS_API_KEY' ) || empty( GNEWS_API_KEY ) ) {
            $this->load_env_key();
        }
    }

    /** Create/upgrade the custom DB table */
    public function maybe_create_table() {
        global $wpdb;
        $table = Portal_News_Radar_DB::table_name();
        // Check if table exists
        $exists = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = %s AND table_name = %s",
            $wpdb->dbname, $table
        ) );

        $installed = get_option( 'news_radar_db_version', '0' );
        if ( ! $exists || version_compare( $installed, PORTAL_CORE_VERSION, '<' ) ) {
            Portal_News_Radar_DB::instance()->create_table();
            update_option( 'news_radar_db_version', PORTAL_CORE_VERSION );
        }
    }

    /** Schedule WP Cron for fetching every 45 minutes */
    public function schedule_cron() {
        if ( ! wp_next_scheduled( 'newsradar_cron_fetch' ) ) {
            wp_schedule_event( time(), 'newsradar_45min', 'newsradar_cron_fetch' );
        }
        // Register the custom interval
        add_filter( 'cron_schedules', array( $this, 'add_cron_interval' ) );
    }

    /** Custom 45-minute cron interval */
    public function add_cron_interval( $schedules ) {
        $schedules['newsradar_45min'] = array(
            'interval' => 45 * 60,
            'display'  => __( 'Every 45 Minutes', 'portal-core' ),
        );
        return $schedules;
    }

    /** Load GNEWS_API_KEY from .env file */
    private function load_env_key() {
        $env_file = dirname( dirname( __DIR__ ) ) . '/.env'; // plugin dir ../../ = WP root
        if ( ! file_exists( $env_file ) ) return;

        $lines = file( $env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
        foreach ( $lines as $line ) {
            if ( strpos( trim( $line ), '#' ) === 0 ) continue;
            if ( strpos( $line, '=' ) === false ) continue;
            list( $name, $value ) = explode( '=', $line, 2 );
            $name  = trim( $name );
            $value = trim( $value, " \t\n\r\0\x0B\"'" );
            if ( $name === 'GNEWS_API_KEY' && ! empty( $value ) ) {
                if ( ! defined( 'GNEWS_API_KEY' ) ) {
                    define( 'GNEWS_API_KEY', $value );
                }
                putenv( "GNEWS_API_KEY={$value}" );
                break;
            }
        }
    }
}
