<?php
/**
 * YouTube API Fetcher
 *
 * Fetches videos from a YouTube channel using the YouTube Data API v3.
 * Stores video metadata in a custom database table for local caching.
 *
 * @package PortalCore
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Portal_YouTube {

    private static $instance = null;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Load API key from .env
        if ( ! defined( 'YOUTUBE_API_KEY' ) || empty( YOUTUBE_API_KEY ) ) {
            $this->load_env_key( 'YOUTUBE_API_KEY' );
        }
        if ( ! defined( 'YOUTUBE_CHANNEL_ID' ) || empty( YOUTUBE_CHANNEL_ID ) ) {
            $this->load_env_key( 'YOUTUBE_CHANNEL_ID' );
        }
    }

    /**
     * Load an environment variable from .env file
     */
    private function load_env_key( $key ) {
        $env_file = dirname( dirname( __DIR__ ) ) . '/.env';
        if ( ! file_exists( $env_file ) ) {
            return;
        }

        $lines = file( $env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
        foreach ( $lines as $line ) {
            $line = trim( $line );
            if ( empty( $line ) || $line[0] === '#' ) {
                continue;
            }
            if ( strpos( $line, '=' ) === false ) {
                continue;
            }
            list( $k, $v ) = explode( '=', $line, 2 );
            $k = trim( $k );
            $v = trim( $v, " \t\n\r\0\x0B\"" );
            if ( $k === $key && ! empty( $v ) ) {
                if ( ! defined( $k ) ) {
                    define( $k, $v );
                }
                putenv( "{$k}={$v}" );
            }
        }
    }

    /**
     * Check if API key is configured
     */
    public function has_api_key() {
        return defined( 'YOUTUBE_API_KEY' ) && ! empty( YOUTUBE_API_KEY );
    }

    /**
     * Check if Channel ID is configured
     */
    public function has_channel_id() {
        return defined( 'YOUTUBE_CHANNEL_ID' ) && ! empty( YOUTUBE_CHANNEL_ID );
    }

    /**
     * Get API key
     */
    public function get_api_key() {
        return defined( 'YOUTUBE_API_KEY' ) ? YOUTUBE_API_KEY : '';
    }

    /**
     * Get Channel ID
     */
    public function get_channel_id() {
        return defined( 'YOUTUBE_CHANNEL_ID' ) ? YOUTUBE_CHANNEL_ID : '';
    }

    /**
     * Test the API connection
     *
     * @return array { ok: bool, message: string }
     */
    public function test_connection() {
        if ( ! $this->has_api_key() ) {
            return array( 'ok' => false, 'message' => 'YOUTUBE_API_KEY not configured.' );
        }
        if ( ! $this->has_channel_id() ) {
            return array( 'ok' => false, 'message' => 'YOUTUBE_CHANNEL_ID not configured.' );
        }

        // Try to fetch channel info
        $response = $this->api_request( 'channels', array(
            'part'  => 'snippet,statistics',
            'id'    => $this->get_channel_id(),
        ) );

        if ( is_wp_error( $response ) ) {
            return array( 'ok' => false, 'message' => $response->get_error_message() );
        }

        if ( empty( $response['items'] ) ) {
            return array( 'ok' => false, 'message' => 'Channel not found. Check YOUTUBE_CHANNEL_ID.' );
        }

        $channel = $response['items'][0];
        $snippet = $channel['snippet'];
        $stats   = $channel['statistics'] ?? array();

        return array(
            'ok'        => true,
            'message'   => sprintf(
                'Connected to: %s (%s subscribers, %s videos)',
                $snippet['title'],
                number_format( intval( $stats['subscriberCount'] ?? 0 ) ),
                number_format( intval( $stats['videoCount'] ?? 0 ) )
            ),
            'channel'   => $snippet['title'],
            'subscribers' => intval( $stats['subscriberCount'] ?? 0 ),
            'video_count' => intval( $stats['videoCount'] ?? 0 ),
        );
    }

    /**
     * Fetch channel uploads playlist ID
     */
    public function get_uploads_playlist_id() {
        if ( ! $this->has_api_key() || ! $this->has_channel_id() ) {
            return false;
        }

        $response = $this->api_request( 'channels', array(
            'part'  => 'contentDetails',
            'id'    => $this->get_channel_id(),
        ) );

        if ( is_wp_error( $response ) || empty( $response['items'] ) ) {
            return false;
        }

        return $response['items'][0]['contentDetails']['relatedPlaylists']['uploads'] ?? false;
    }

    /**
     * Fetch all videos from channel uploads
     *
     * @param int $max_results Maximum videos to fetch
     * @return array { videos: array, errors: array }
     */
    public function fetch_channel_videos( $max_results = 50 ) {
        $all_videos = array();
        $errors     = array();

        $playlist_id = $this->get_uploads_playlist_id();
        if ( ! $playlist_id ) {
            return array( 'videos' => array(), 'errors' => array( 'Could not fetch uploads playlist.' ) );
        }

        // Get video IDs from playlist
        $page_token = '';
        $fetched    = 0;

        while ( $fetched < $max_results ) {
            $params = array(
                'part'       => 'snippet',
                'playlistId' => $playlist_id,
                'maxResults' => min( 50, $max_results - $fetched ),
            );
            if ( ! empty( $page_token ) ) {
                $params['pageToken'] = $page_token;
            }

            $response = $this->api_request( 'playlistItems', $params );

            if ( is_wp_error( $response ) ) {
                $errors[] = $response->get_error_message();
                break;
            }

            if ( empty( $response['items'] ) ) {
                break;
            }

            $video_ids = array();
            foreach ( $response['items'] as $item ) {
                $video_id = $item['snippet']['resourceId']['videoId'] ?? '';
                if ( ! empty( $video_id ) ) {
                    $video_ids[] = $video_id;
                    $all_videos[ $video_id ] = array(
                        'video_id'    => $video_id,
                        'title'       => $item['snippet']['title'] ?? '',
                        'description' => $item['snippet']['description'] ?? '',
                        'thumbnail'   => $item['snippet']['thumbnails']['high']['url']
                                          ?? $item['snippet']['thumbnails']['medium']['url']
                                          ?? $item['snippet']['thumbnails']['default']['url']
                                          ?? '',
                        'published_at'=> $item['snippet']['publishedAt'] ?? '',
                        'channel_id' => $item['snippet']['channelId'] ?? '',
                    );
                }
            }

            // Fetch video details (duration, view count, live status)
            if ( ! empty( $video_ids ) ) {
                $details = $this->api_request( 'videos', array(
                    'part'  => 'contentDetails,statistics,status,liveStreamingDetails',
                    'id'    => implode( ',', $video_ids ),
                ) );

                if ( ! is_wp_error( $details ) && ! empty( $details['items'] ) ) {
                    foreach ( $details['items'] as $detail ) {
                        $vid = $detail['id'] ?? '';
                        if ( empty( $vid ) || ! isset( $all_videos[ $vid ] ) ) {
                            continue;
                        }

                        // Duration (ISO 8601 → human readable)
                        $duration_iso = $detail['contentDetails']['duration'] ?? 'PT0S';
                        $all_videos[ $vid ]['duration'] = $this->parse_duration( $duration_iso );

                        // View count
                        $all_videos[ $vid ]['view_count'] = intval( $detail['statistics']['viewCount'] ?? 0 );

                        // Live status
                        $all_videos[ $vid ]['is_live'] = false;
                        $all_videos[ $vid ]['is_upcoming'] = false;
                        $all_videos[ $vid ]['is_broadcast'] = false;

                        if ( isset( $detail['status']['publishAt'] ) && strtotime( $detail['status']['publishAt'] ) > time() ) {
                            $all_videos[ $vid ]['is_upcoming'] = true;
                            $all_videos[ $vid ]['is_broadcast'] = true;
                        } elseif ( ! empty( $detail['liveStreamingDetails'] ) ) {
                            $lsd = $detail['liveStreamingDetails'];
                            if ( isset( $lsd['actualStartTime'] ) && empty( $lsd['actualEndTime'] ) ) {
                                $all_videos[ $vid ]['is_live'] = true;
                            }
                            $all_videos[ $vid ]['is_broadcast'] = true;
                        } elseif ( isset( $detail['contentDetails']['duration'] ) ) {
                            // Check for upcoming scheduled streams
                            $all_videos[ $vid ]['is_broadcast'] = ( $detail['status']['uploadStatus'] ?? '' ) === 'processed'
                                && ! empty( $detail['liveStreamingDetails'] );
                        }
                    }
                }
            }

            $fetched += count( $video_ids );
            $page_token = $response['nextPageToken'] ?? '';
            if ( empty( $page_token ) ) {
                break;
            }

            // Rate limit: 100ms between API calls
            usleep( 100000 );
        }

        return array( 'videos' => array_values( $all_videos ), 'errors' => $errors );
    }

    /**
     * Parse ISO 8601 duration to human readable format
     */
    private function parse_duration( $iso_duration ) {
        $parts = preg_split( '/[PT]/', $iso_duration );
        $hours = 0;
        $minutes = 0;
        $seconds = 0;

        foreach ( $parts as $part ) {
            if ( preg_match( '/(\d+)H/', $part, $m ) ) {
                $hours = intval( $m[1] );
            } elseif ( preg_match( '/(\d+)M/', $part, $m ) ) {
                $minutes = intval( $m[1] );
            } elseif ( preg_match( '/(\d+)S/', $part, $m ) ) {
                $seconds = intval( $m[1] );
            }
        }

        if ( $hours > 0 ) {
            return sprintf( '%d:%02d:%02d', $hours, $minutes, $seconds );
        }
        return sprintf( '%d:%02d', $minutes, $seconds );
    }

    /**
     * Make a YouTube Data API v3 request
     */
    private function api_request( $endpoint, $params = array() ) {
        $params['key'] = $this->get_api_key();

        $url = add_query_arg( $params, "https://www.googleapis.com/youtube/v3/{$endpoint}" );

        $response = wp_remote_get( $url, array(
            'timeout' => 15,
            'headers' => array(
                'Accept' => 'application/json',
            ),
        ) );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code( $response );
        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( $code !== 200 ) {
            $error_msg = $body['error']['message'] ?? "HTTP {$code}";
            return new WP_Error( 'youtube_api_error', $error_msg );
        }

        return $body;
    }

    /**
     * Sync channel videos to WordPress episodes
     *
     * @return array { found: int, created: int, updated: int, errors: array }
     */
    public function sync_videos_to_episodes() {
        $result = array(
            'found'   => 0,
            'created'=> 0,
            'updated'=> 0,
            'errors' => array(),
        );

        $videos = $this->fetch_channel_videos( 50 );
        $result['found'] = count( $videos['videos'] );
        $result['errors'] = $videos['errors'];

        foreach ( $videos['videos'] as $video ) {
            $video_id = $video['video_id'];
            if ( empty( $video_id ) ) {
                continue;
            }

            // Check if episode already exists for this YouTube video ID
            $existing = $this->find_episode_by_youtube_id( $video_id );

            if ( $existing ) {
                // Update existing episode
                $this->update_episode_from_video( $existing->ID, $video );
                $result['updated']++;
            } else {
                // Create new episode
                $this->create_episode_from_video( $video );
                $result['created']++;
            }

            // Store in local cache
            $this->cache_video( $video );
        }

        return $result;
    }

    /**
     * Find an episode by YouTube video ID
     */
    private function find_episode_by_youtube_id( $youtube_id ) {
        global $wpdb;

        $query = new WP_Query( array(
            'post_type'      => 'portal_episode',
            'post_status'    => array( 'publish', 'draft', 'pending' ),
            'posts_per_page' => 1,
            'meta_query'     => array(
                array(
                    'key'   => 'youtube_video_id',
                    'value' => $youtube_id,
                ),
            ),
        ) );

        $post = $query->have_posts() ? $query->posts[0] : null;
        wp_reset_postdata();
        return $post;
    }

    /**
     * Create a new Episode from a YouTube video
     */
    private function create_episode_from_video( $video ) {
        $post_id = wp_insert_post( array(
            'post_title'   => $video['title'],
            'post_content' => $video['description'],
            'post_status'  => 'draft',
            'post_type'    => 'portal_episode',
        ) );

        if ( is_wp_error( $post_id ) ) {
            return false;
        }

        // Set YouTube video ID
        update_post_meta( $post_id, 'youtube_video_id', $video['video_id'] );

        // Set additional meta fields
        update_post_meta( $post_id, '_tp_episode_youtube_id', $video['video_id'] );
        update_post_meta( $post_id, 'youtube_duration', $video['duration'] ?? '' );
        update_post_meta( $post_id, 'youtube_view_count', $video['view_count'] ?? 0 );
        update_post_meta( $post_id, 'youtube_published_at', $video['published_at'] ?? '' );
        update_post_meta( $post_id, 'youtube_is_live', $video['is_live'] ? '1' : '0' );
        update_post_meta( $post_id, 'youtube_is_upcoming', $video['is_upcoming'] ? '1' : '0' );
        update_post_meta( $post_id, 'youtube_is_broadcast', $video['is_broadcast'] ? '1' : '0' );

        // Set thumbnail
        if ( ! empty( $video['thumbnail'] ) ) {
            $thumb_id = $this->download_thumbnail( $video['thumbnail'], $video['video_id'] );
            if ( $thumb_id ) {
                set_post_thumbnail( $post_id, $thumb_id );
            }
        }

        return $post_id;
    }

    /**
     * Update an existing Episode with YouTube video data
     */
    private function update_episode_from_video( $post_id, $video ) {
        // Update YouTube meta fields
        update_post_meta( $post_id, 'youtube_video_id', $video['video_id'] );
        update_post_meta( $post_id, '_tp_episode_youtube_id', $video['video_id'] );
        update_post_meta( $post_id, 'youtube_duration', $video['duration'] ?? '' );
        update_post_meta( $post_id, 'youtube_view_count', $video['view_count'] ?? 0 );
        update_post_meta( $post_id, 'youtube_published_at', $video['published_at'] ?? '' );
        update_post_meta( $post_id, 'youtube_is_live', $video['is_live'] ? '1' : '0' );
        update_post_meta( $post_id, 'youtube_is_upcoming', $video['is_upcoming'] ? '1' : '0' );
        update_post_meta( $post_id, 'youtube_is_broadcast', $video['is_broadcast'] ? '1' : '0' );

        // Update thumbnail if different
        if ( ! empty( $video['thumbnail'] ) ) {
            $current_thumb = get_the_post_thumbnail_url( $post_id, 'full' );
            if ( $current_thumb !== $video['thumbnail'] ) {
                $thumb_id = $this->download_thumbnail( $video['thumbnail'], $video['video_id'] );
                if ( $thumb_id ) {
                    set_post_thumbnail( $post_id, $thumb_id );
                }
            }
        }

        return true;
    }

    /**
     * Download and attach a thumbnail
     */
    private function download_thumbnail( $url, $video_id ) {
        if ( ! function_exists( 'media_handle_sideload' ) ) {
            require_once ABSPATH . 'wp-admin/includes/media.php';
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';
        }

        $tmp = download_url( $url, 10 );
        if ( is_wp_error( $tmp ) ) {
            return false;
        }

        $ext = pathinfo( parse_url( $url, PHP_URL_PATH ), PATHINFO_EXTENSION ) ?: 'jpg';
        $file = array(
            'name'     => "yt-thumb-{$video_id}.{$ext}",
            'tmp_name' => $tmp,
            'error'    => 0,
            'size'     => filesize( $tmp ),
        );

        $id = media_handle_sideload( $file, 0 );
        return is_wp_error( $id ) ? false : $id;
    }

    /**
     * Cache a video in the local database table
     */
    private function cache_video( $video ) {
        global $wpdb;
        $table = $wpdb->prefix . 'portal_youtube_cache';

        $wpdb->replace( $table, array(
            'video_id'      => $video['video_id'],
            'title'         => $video['title'],
            'description'   => $video['description'],
            'thumbnail_url' => $video['thumbnail'],
            'duration'      => $video['duration'] ?? '',
            'view_count'    => $video['view_count'] ?? 0,
            'published_at'  => ! empty( $video['published_at'] ) ? gmdate( 'Y-m-d H:i:s', strtotime( $video['published_at'] ) ) : null,
            'is_live'       => $video['is_live'] ? 1 : 0,
            'is_upcoming'   => $video['is_upcoming'] ? 1 : 0,
            'is_broadcast'  => $video['is_broadcast'] ? 1 : 0,
            'synced_at'     => current_time( 'mysql' ),
        ), array( '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%d', '%d', '%d', '%s' ) );
    }

    /**
     * Get cached videos
     */
    public function get_cached_videos( $args = array() ) {
        global $wpdb;
        $table = $wpdb->prefix . 'portal_youtube_cache';

        $defaults = array(
            'per_page' => 20,
            'page'     => 1,
            'orderby'  => 'published_at',
            'order'    => 'DESC',
        );
        $args = wp_parse_args( $args, $defaults );

        $offset = ( $args['page'] - 1 ) * $args['per_page'];
        $limit  = $args['per_page'];

        $allowed_order = array( 'published_at', 'title', 'view_count', 'duration' );
        $orderby = in_array( $args['orderby'], $allowed_order ) ? $args['orderby'] : 'published_at';
        $order   = strtoupper( $args['order'] ) === 'ASC' ? 'ASC' : 'DESC';

        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$table} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d",
            $limit,
            $offset
        ) );
    }

    /**
     * Count cached videos
     */
    public function count_cached_videos() {
        global $wpdb;
        $table = $wpdb->prefix . 'portal_youtube_cache';
        return intval( $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" ) );
    }

    /**
     * Get last sync time
     */
    public function get_last_sync_time() {
        return get_option( 'portal_youtube_last_sync', '' );
    }

    /**
     * Update last sync time
     */
    public function update_last_sync_time() {
        update_option( 'portal_youtube_last_sync', current_time( 'mysql' ) );
    }
}
