<?php
/**
 * Plugin Name: Portal Core
 * Plugin URI: https://techportal.24.jugaar.ai
 * Description: Core functionality for the Tech Portal — custom post types, taxonomies, REST API extensions, and modular feature loading.
 * Version: 1.0.0
 * Author: Jugaar Tech
 * Author URI: https://jugaar.ai
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: portal-core
 * Domain Path: /languages
 * Requires at least: 6.4
 * Requires PHP: 8.1
 *
 * @package PortalCore
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'PORTAL_CORE_VERSION', '1.0.0' );
define( 'PORTAL_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'PORTAL_CORE_URL', plugin_dir_url( __FILE__ ) );

/**
 * Main Plugin Class
 *
 * Modular bootstrap — each feature is a separate include file.
 * Features can be independently enabled/disabled via constants or options.
 */
final class Portal_Core {

    private static $instance = null;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->load_includes();
        $this->init_hooks();
    }

    /**
     * Load core includes
     */
    private function load_includes() {
        // Core utilities
        require_once PORTAL_CORE_DIR . 'includes/class-portal-helpers.php';

        // Feature modules (loaded conditionally)
        require_once PORTAL_CORE_DIR . 'includes/class-portal-cpt.php';        // Custom Post Types
        require_once PORTAL_CORE_DIR . 'includes/class-portal-taxonomies.php'; // Custom Taxonomies
        require_once PORTAL_CORE_DIR . 'includes/class-portal-rest.php';       // REST API Extensions
        require_once PORTAL_CORE_DIR . 'includes/class-portal-admin.php';      // Admin Enhancements

        // Phase 3A — News Radar
        require_once PORTAL_CORE_DIR . 'includes/class-portal-news-radar-db.php';
        require_once PORTAL_CORE_DIR . 'includes/class-portal-news-radar-fetcher.php';
        require_once PORTAL_CORE_DIR . 'includes/class-portal-news-radar-admin.php';
        require_once PORTAL_CORE_DIR . 'includes/class-portal-news-radar.php';

        // Phase 3B — YouTube Integration
        require_once PORTAL_CORE_DIR . 'includes/class-portal-youtube-db.php';
        require_once PORTAL_CORE_DIR . 'includes/class-portal-youtube.php';
        require_once PORTAL_CORE_DIR . 'includes/class-portal-youtube-admin.php';

        // Phase 3C — Membership & Bookmarks
        require_once PORTAL_CORE_DIR . 'includes/class-portal-membership.php';

        // Phase 3D — Features (Submit, Sponsors, Search, Related, Trending, Video Archive)
        require_once PORTAL_CORE_DIR . 'includes/class-portal-features.php';

        // Phase 4 — Security, Performance, SEO Hardening
        require_once PORTAL_CORE_DIR . 'includes/class-portal-phase4.php';
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

        add_action( 'init', array( $this, 'load_textdomain' ) );
        // Bootstrap CPT and Taxonomy classes (constructors add init hooks)
        Portal_CPT::instance();
        Portal_Taxonomies::instance();
        add_action( 'rest_api_init', array( 'Portal_REST', 'instance' ) );
        add_action( 'admin_menu', array( 'Portal_Admin', 'instance' ) );

        // Phase 3A — News Radar
        Portal_News_Radar::instance();

        // Phase 3B — YouTube Integration
        Portal_YouTube_DB::instance()->create_table();
        Portal_YouTube_Admin::instance();
        Portal_YouTube_Admin::instance()->schedule_cron();

        // Phase 3C — Membership & Bookmarks
        Portal_Membership::instance();

        // Phase 3D — Features
        Portal_Features::instance();

        // Phase 4 — Security, Performance, SEO
        Portal_Phase4::instance();

        // Register custom cron interval
        add_filter( 'cron_schedules', array( $this, 'add_youtube_cron_interval' ) );
    }

    /**
     * Add custom cron interval for YouTube sync
     */
    public function add_youtube_cron_interval( $schedules ) {
        $schedules['portal_youtube_hourly'] = array(
            'interval' => HOUR_IN_SECONDS,
            'display'  => __( 'Every Hour', 'portal-core' ),
        );
        return $schedules;
    }

    /**
     * Load plugin text domain
     */
    public function load_textdomain() {
        load_plugin_textdomain( 'portal-core', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
    }

    /**
     * Activation: flush rewrite rules, set default options
     */
    public function activate() {
        // Flush rewrite rules for custom post types
        Portal_CPT::instance()->register();
        Portal_Taxonomies::instance()->register();
        flush_rewrite_rules();

        // Set default options
        $defaults = array(
            'portal_version' => PORTAL_CORE_VERSION,
            'features'       => array(
                'startups'      => true,
                'founders'      => true,
                'funding'       => true,
                'web_channel'   => true,
                'membership'    => false,  // Phase 3
                'comments'      => true,
                'bookmarks'     => false,  // Phase 3
                'newsletter'    => false,  // Phase 3
                'press_releases'=> true,
                'sponsors'      => true,
            ),
        );
        update_option( 'portal_core_settings', $defaults );
    }

    /**
     * Deactivation: clean up
     */
    public function deactivate() {
        flush_rewrite_rules();
    }
}

// Initialize
Portal_Core::instance();
