<?php
/**
 * Portal Helpers
 *
 * Utility functions used across the plugin.
 *
 * @package PortalCore
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Portal_Helpers {

    /**
     * Get plugin settings
     */
    public static function get_settings() {
        return get_option( 'portal_core_settings', array() );
    }

    /**
     * Check if a feature is enabled
     */
    public static function is_feature_enabled( $feature ) {
        $settings = self::get_settings();
        return isset( $settings['features'][ $feature ] ) && $settings['features'][ $feature ];
    }

    /**
     * Get supported post types for the portal
     */
    public static function get_portal_post_types() {
        return array(
            'portal_article'   => __( 'Articles', 'portal-core' ),
            'portal_startup'   => __( 'Startups', 'portal-core' ),
            'portal_founder'   => __( 'Founders', 'portal-core' ),
            'portal_episode'   => __( 'Episodes', 'portal-core' ),
            'portal_press'     => __( 'Press Releases', 'portal-core' ),
        );
    }

    /**
     * Get reading time for content
     */
    public static function reading_time( $content, $wpm = 250 ) {
        $word_count = str_word_count( strip_tags( $content ) );
        return max( 1, ceil( $word_count / $wpm ) );
    }

    /**
     * Format large numbers (e.g., funding amounts)
     */
    public static function format_number( $number ) {
        if ( $number >= 1000000000 ) {
            return round( $number / 1000000000, 1 ) . 'B';
        } elseif ( $number >= 1000000 ) {
            return round( $number / 1000000, 1 ) . 'M';
        } elseif ( $number >= 1000 ) {
            return round( $number / 1000, 1 ) . 'K';
        }
        return $number;
    }
}
