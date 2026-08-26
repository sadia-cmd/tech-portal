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
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

        add_action( 'init', array( $this, 'load_textdomain' ) );
        add_action( 'init', array( 'Portal_CPT', 'instance' ) );
        add_action( 'init', array( 'Portal_Taxonomies', 'instance' ) );
        add_action( 'rest_api_init', array( 'Portal_REST', 'instance' ) );
        add_action( 'admin_menu', array( 'Portal_Admin', 'instance' ) );
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
