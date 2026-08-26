<?php
/**
 * YouTube Cache Database Table
 *
 * Stores cached YouTube video metadata for fast local access.
 *
 * @package PortalCore
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Portal_YouTube_DB {

    private static $instance = null;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get the table name with WordPress prefix
     */
    public static function table_name() {
        global $wpdb;
        return $wpdb->prefix . 'portal_youtube_cache';
    }

    /**
     * Create or upgrade the custom table
     */
    public function create_table() {
        global $wpdb;
        $table = self::table_name();
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            video_id VARCHAR(20) NOT NULL,
            title VARCHAR(255) NOT NULL DEFAULT '',
            description LONGTEXT,
            thumbnail_url VARCHAR(500) DEFAULT '',
            duration VARCHAR(20) DEFAULT '',
            view_count BIGINT UNSIGNED DEFAULT 0,
            published_at DATETIME DEFAULT NULL,
            is_live TINYINT(1) DEFAULT 0,
            is_upcoming TINYINT(1) DEFAULT 0,
            is_broadcast TINYINT(1) DEFAULT 0,
            synced_at DATETIME DEFAULT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY video_id (video_id),
            KEY published_at (published_at),
            KEY view_count (view_count)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );

        update_option( 'portal_youtube_db_version', '1.0.0' );
    }
}
