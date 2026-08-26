<?php
/**
 * News Radar — Database layer
 * Custom table for fetched stories with dedup.
 *
 * @package PortalCore
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Portal_News_Radar_DB {

    private static $instance = null;
    public static function table_name() {
        global $wpdb;
        return $wpdb->prefix . 'portal_news_radar';
    }

    public static function instance() {
        if ( null === self::$instance ) self::$instance = new self();
        return self::$instance;
    }

    /** Create / upgrade the custom table */
    public function create_table() {
        global $wpdb;
        $table = self::table_name();
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            gnews_url VARCHAR(512) NOT NULL DEFAULT '',
            title VARCHAR(512) NOT NULL DEFAULT '',
            title_normalized VARCHAR(512) NOT NULL DEFAULT '',
            content_hash VARCHAR(64) NOT NULL DEFAULT '',
            description TEXT,
            image_url VARCHAR(512) DEFAULT '',
            source_name VARCHAR(255) DEFAULT '',
            source_url VARCHAR(512) DEFAULT '',
            published_at DATETIME DEFAULT NULL,
            topic VARCHAR(64) NOT NULL DEFAULT '',
            category_suggested VARCHAR(128) DEFAULT '',
            status VARCHAR(20) NOT NULL DEFAULT 'new',
            wp_post_id BIGINT UNSIGNED DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY idx_content_hash (content_hash),
            KEY idx_status (status),
            KEY idx_topic (topic),
            KEY idx_published (published_at),
            KEY idx_gnews_url (gnews_url(191))
        ) {$charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    /** Insert a story row. Returns id or false on duplicate. */
    public static function insert_story( $data ) {
        global $wpdb;
        $table = self::table_name();

        $normalized = self::normalize_title( $data['title'] );
        $hash       = hash( 'sha256', $normalized . '|' . $data['gnews_url'] );

        // Check duplicate by hash
        $exists = $wpdb->get_var( $wpdb->prepare(
            "SELECT id FROM {$table} WHERE content_hash = %s", $hash
        ) );
        if ( $exists ) return false;

        $result = $wpdb->insert( $table, array(
            'gnews_url'          => $data['gnews_url'],
            'title'              => $data['title'],
            'title_normalized'   => $normalized,
            'content_hash'       => $hash,
            'description'        => $data['description'] ?? '',
            'image_url'          => $data['image_url'] ?? '',
            'source_name'        => $data['source_name'] ?? '',
            'source_url'         => $data['source_url'] ?? '',
            'published_at'       => ! empty( $data['published_at'] ) ? gmdate( 'Y-m-d H:i:s', strtotime( $data['published_at'] ) ) : null,
            'topic'              => $data['topic'] ?? '',
            'category_suggested' => $data['category_suggested'] ?? '',
            'status'             => 'new',
        ), array( '%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s' ) );

        return $result ? $wpdb->insert_id : false;
    }

    /** Update story status */
    public static function set_status( $id, $status, $post_id = null ) {
        global $wpdb;
        $table = self::table_name();
        $update = array( 'status' => $status );
        $format = array( '%s' );
        if ( null !== $post_id ) {
            $update['wp_post_id'] = $post_id;
            $format[] = '%d';
        }
        return $wpdb->update( $table, $update, array( 'id' => $id ), $format, array( '%d' ) );
    }

    /** Get stories filtered */
    public static function get_stories( $args = array() ) {
        global $wpdb;
        $table = self::table_name();

        $defaults = array(
            'status'  => '',
            'topic'   => '',
            'per_page'=> 50,
            'page'    => 1,
            'orderby' => 'published_at',
            'order'   => 'DESC',
        );
        $args = wp_parse_args( $args, $defaults );

        $where = "WHERE 1=1";
        $params = array();

        if ( ! empty( $args['status'] ) ) {
            $where .= " AND status = %s";
            $params[] = $args['status'];
        }
        if ( ! empty( $args['topic'] ) ) {
            $where .= " AND topic = %s";
            $params[] = $args['topic'];
        }

        $allowed_order = array( 'published_at', 'created_at', 'title', 'topic', 'status' );
        $orderby = in_array( $args['orderby'], $allowed_order ) ? $args['orderby'] : 'published_at';
        $order   = strtoupper( $args['order'] ) === 'ASC' ? 'ASC' : 'DESC';
        $offset  = max( 0, ( intval( $args['page'] ) - 1 ) * intval( $args['per_page'] ) );
        $limit   = intval( $args['per_page'] );

        $sql = "SELECT * FROM {$table} {$where} ORDER BY {$orderby} {$order} LIMIT {$limit} OFFSET {$offset}";

        if ( ! empty( $params ) ) {
            $sql = $wpdb->prepare( $sql, ...$params );
        }

        return $wpdb->get_results( $sql );
    }

    /** Count stories */
    public static function count_stories( $args = array() ) {
        global $wpdb;
        $table = self::table_name();
        $where = "WHERE 1=1";
        $params = array();

        if ( ! empty( $args['status'] ) ) {
            $where .= " AND status = %s";
            $params[] = $args['status'];
        }
        if ( ! empty( $args['topic'] ) ) {
            $where .= " AND topic = %s";
            $params[] = $args['topic'];
        }

        $sql = "SELECT COUNT(*) FROM {$table} {$where}";
        if ( ! empty( $params ) ) {
            $sql = $wpdb->prepare( $sql, ...$params );
        }
        return intval( $wpdb->get_var( $sql ) );
    }

    /** Normalize title for dedup */
    public static function normalize_title( $title ) {
        $t = strtolower( trim( $title ) );
        $t = preg_replace( '/[^a-z0-9\s]/', '', $t );
        $t = preg_replace( '/\s+/', ' ', $t );
        return $t;
    }

    /** Check if URL already exists */
    public static function url_exists( $url ) {
        global $wpdb;
        $table = self::table_name();
        return (bool) $wpdb->get_var( $wpdb->prepare(
            "SELECT id FROM {$table} WHERE gnews_url = %s", $url
        ) );
    }
}
