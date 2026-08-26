<?php
/**
 * News Radar — GNews API Fetcher
 *
 * @package PortalCore
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Portal_News_Radar_Fetcher {

    /** Topic → search query mapping */
    const TOPICS = array(
        'pakistan-tech'      => 'Pakistan technology',
        'pakistan-startups'  => 'Pakistan startups',
        'ai'                 => 'artificial intelligence AI',
        'cybersecurity'      => 'cybersecurity',
        'cloud'              => 'cloud computing',
        'fintech'            => 'fintech financial technology',
    );

    /** Topic → suggested WordPress category slug */
    const CATEGORY_MAP = array(
        'pakistan-tech'      => 'pakistan-technology',
        'pakistan-startups'  => 'startup-stories',
        'ai'                 => 'ai-cloud',
        'cybersecurity'      => 'cybersecurity',
        'cloud'              => 'ai-cloud',
        'fintech'            => 'it-news',
    );

    private $api_key;
    private $base_url = 'https://gnews.io/api/v4/search';

    public function __construct() {
        $this->api_key = defined( 'GNEWS_API_KEY' ) ? GNEWS_API_KEY : '';
        if ( empty( $this->api_key ) ) {
            $this->api_key = getenv( 'GNEWS_API_KEY' );
        }
    }

    public function has_key() {
        return ! empty( $this->api_key );
    }

    /** Fetch stories for a single topic. Returns array of normalized stories. */
    public function fetch_topic( $topic_key, $max = 10 ) {
        if ( ! $this->has_key() ) {
            return array( 'error' => 'No GNEWS_API_KEY configured.' );
        }

        $query = self::TOPICS[ $topic_key ] ?? $topic_key;

        $params = array(
            'q'       => $query,
            'lang'    => 'en',
            'country' => 'pk',
            'max'     => min( $max, 10 ),
            'expand'  => 'content',
            'apikey'  => $this->api_key,
        );

        $url = add_query_arg( $params, $this->base_url );

        $response = wp_remote_get( $url, array(
            'timeout' => 15,
            'headers' => array( 'Accept' => 'application/json' ),
        ) );

        if ( is_wp_error( $response ) ) {
            return array( 'error' => $response->get_error_message() );
        }

        $code = wp_remote_retrieve_response_code( $response );
        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        if ( $code !== 200 || empty( $data['articles'] ) ) {
            $err = $data['errors'][0] ?? "HTTP {$code}";
            return array( 'error' => $err );
        }

        $stories = array();
        foreach ( $data['articles'] as $article ) {
            $stories[] = array(
                'gnews_url'          => $article['url'] ?? '',
                'title'              => $article['title'] ?? '',
                'description'        => $article['description'] ?? $article['content'] ?? '',
                'image_url'          => $article['image'] ?? '',
                'source_name'        => $article['source']['name'] ?? '',
                'source_url'         => $article['source']['url'] ?? '',
                'published_at'       => $article['publishedAt'] ?? '',
                'topic'              => $topic_key,
                'category_suggested' => self::CATEGORY_MAP[ $topic_key ] ?? 'it-news',
            );
        }

        return $stories;
    }

    /** Fetch all topics and store new stories. Returns summary. */
    public function fetch_all() {
        $summary = array(
            'fetched'  => 0,
            'new'      => 0,
            'duplicates' => 0,
            'errors'   => array(),
        );

        foreach ( self::TOPICS as $topic_key => $label ) {
            $stories = $this->fetch_topic( $topic_key, 10 );

            if ( isset( $stories['error'] ) ) {
                $summary['errors'][ $topic_key ] = $stories['error'];
                continue;
            }

            foreach ( $stories as $story ) {
                $summary['fetched']++;

                $id = Portal_News_Radar_DB::insert_story( $story );
                if ( $id ) {
                    $summary['new']++;
                } else {
                    $summary['duplicates']++;
                }
            }

            // Rate limit: 200ms between topics
            usleep( 200000 );
        }

        return $summary;
    }

    /** Test API key validity */
    public function test_connection() {
        if ( ! $this->has_key() ) {
            return array( 'ok' => false, 'message' => 'GNEWS_API_KEY not set in .env or wp-config.' );
        }

        $url = add_query_arg( array(
            'q'      => 'technology',
            'lang'   => 'en',
            'max'    => 1,
            'apikey' => $this->api_key,
        ), $this->base_url );

        $response = wp_remote_get( $url, array( 'timeout' => 10 ) );

        if ( is_wp_error( $response ) ) {
            return array( 'ok' => false, 'message' => $response->get_error_message() );
        }

        $code = wp_remote_retrieve_response_code( $response );
        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( $code === 200 ) {
            $count = count( $body['articles'] ?? array() );
            return array( 'ok' => true, 'message' => "API connected. Got {$count} test article(s)." );
        }

        $err = $body['errors'][0] ?? "HTTP {$code}";
        return array( 'ok' => false, 'message' => "API error: {$err}" );
    }
}
