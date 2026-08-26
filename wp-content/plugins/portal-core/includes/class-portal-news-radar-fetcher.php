<?php
/**
 * News Radar — Dual-Source Fetcher (GNews + NewsAPI.org)
 *
 * GNews is the primary source. NewsAPI.org acts as secondary/fallback:
 *   1. Fetch from GNews for all topics
 *   2. If GNews fails or returns 0 articles for a topic, try NewsAPI.org
 *   3. Each story is tagged with api_source = 'gnews' | 'newsapi'
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

    /** NewsAPI category mapping for top-headlines fallback */
    const NEWSAPI_CATEGORIES = array(
        'ai'            => 'technology',
        'cybersecurity' => 'science',
        'cloud'         => 'technology',
        'fintech'       => 'business',
    );

    private $gnews_key;
    private $newsapi_key;
    private $gnews_base   = 'https://gnews.io/api/v4/search';
    private $newsapi_base = 'https://newsapi.org/v2/everything';

    public function __construct() {
        $this->gnews_key = defined( 'GNEWS_API_KEY' ) ? GNEWS_API_KEY : '';
        if ( empty( $this->gnews_key ) ) {
            $this->gnews_key = getenv( 'GNEWS_API_KEY' );
        }
        $this->newsapi_key = defined( 'NEWSAPI_KEY' ) ? NEWSAPI_KEY : '';
        if ( empty( $this->newsapi_key ) ) {
            $this->newsapi_key = getenv( 'NEWSAPI_KEY' );
        }
    }

    public function has_gnews_key() {
        return ! empty( $this->gnews_key );
    }

    public function has_newsapi_key() {
        return ! empty( $this->newsapi_key );
    }

    public function has_any_key() {
        return $this->has_gnews_key() || $this->has_newsapi_key();
    }

    /** Get status of both APIs */
    public function get_status() {
        return array(
            'gnews'   => $this->has_gnews_key() ? 'configured' : 'missing_key',
            'newsapi' => $this->has_newsapi_key() ? 'configured' : 'missing_key',
        );
    }

    /* ─── GNews Fetching ─── */

    private function fetch_gnews_topic( $topic_key, $max = 10 ) {
        if ( ! $this->has_gnews_key() ) {
            return array( 'error' => 'No GNEWS_API_KEY configured.' );
        }

        $query = self::TOPICS[ $topic_key ] ?? $topic_key;

        $params = array(
            'q'       => $query,
            'lang'    => 'en',
            'country' => 'pk',
            'max'     => min( $max, 10 ),
            'expand'  => 'content',
            'apikey'  => $this->gnews_key,
        );

        $url = add_query_arg( $params, $this->gnews_base );

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
                'api_source'         => 'gnews',
            );
        }

        return $stories;
    }

    /* ─── NewsAPI.org Fetching ─── */

    private function fetch_newsapi_topic( $topic_key, $max = 10 ) {
        if ( ! $this->has_newsapi_key() ) {
            return array( 'error' => 'No NEWSAPI_KEY configured.' );
        }

        $query = self::TOPICS[ $topic_key ] ?? $topic_key;

        $params = array(
            'q'        => $query,
            'language' => 'en',
            'sortBy'   => 'publishedAt',
            'pageSize' => min( $max, 10 ),
            'page'     => 1,
            'apiKey'   => $this->newsapi_key,
        );

        $url = add_query_arg( $params, $this->newsapi_base );

        $response = wp_remote_get( $url, array(
            'timeout' => 15,
            'headers' => array(
                'Accept'     => 'application/json',
                'User-Agent' => 'TechPortal NewsRadar/2.0',
            ),
        ) );

        if ( is_wp_error( $response ) ) {
            return array( 'error' => $response->get_error_message() );
        }

        $code = wp_remote_retrieve_response_code( $response );
        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        if ( $code !== 200 || empty( $data['articles'] ) ) {
            $err = $data['message'] ?? "HTTP {$code}";
            return array( 'error' => $err );
        }

        $stories = array();
        foreach ( $data['articles'] as $article ) {
            $stories[] = array(
                'gnews_url'          => $article['url'] ?? '',
                'title'              => $article['title'] ?? '',
                'description'        => $article['description'] ?? '',
                'image_url'          => $article['urlToImage'] ?? '',
                'source_name'        => $article['source']['name'] ?? '',
                'source_url'         => $article['source']['url'] ?? $article['url'] ?? '',
                'published_at'       => $article['publishedAt'] ?? '',
                'topic'              => $topic_key,
                'category_suggested' => self::CATEGORY_MAP[ $topic_key ] ?? 'it-news',
                'api_source'         => 'newsapi',
            );
        }

        return $stories;
    }

    /* ─── Dual-Source Orchestrator ─── */

    /** Fetch stories for a single topic — tries GNews first, falls back to NewsAPI */
    public function fetch_topic( $topic_key, $max = 10 ) {
        // Try GNews first
        if ( $this->has_gnews_key() ) {
            $result = $this->fetch_gnews_topic( $topic_key, $max );
            if ( ! isset( $result['error'] ) && ! empty( $result ) ) {
                return $result;
            }
            // GNews failed — fall through to NewsAPI
        }

        // Fallback: NewsAPI.org
        if ( $this->has_newsapi_key() ) {
            $result = $this->fetch_newsapi_topic( $topic_key, $max );
            if ( ! isset( $result['error'] ) && ! empty( $result ) ) {
                return $result;
            }
            // Both failed — return the error
            return $result;
        }

        return array( 'error' => 'No API keys configured. Add GNEWS_API_KEY or NEWSAPI_KEY to .env' );
    }

    /** Fetch all topics, store new stories, returns summary with per-source counts */
    public function fetch_all() {
        $summary = array(
            'fetched'      => 0,
            'new'          => 0,
            'duplicates'   => 0,
            'errors'       => array(),
            'gnews_count'  => 0,
            'newsapi_count'=> 0,
            'sources_used' => array(),
        );

        foreach ( self::TOPICS as $topic_key => $label ) {
            $stories = $this->fetch_topic( $topic_key, 10 );

            if ( isset( $stories['error'] ) ) {
                $summary['errors'][ $topic_key ] = $stories['error'];
                continue;
            }

            $topic_source = '';
            foreach ( $stories as $story ) {
                $summary['fetched']++;
                $src = $story['api_source'] ?? 'gnews';

                $id = Portal_News_Radar_DB::insert_story( $story );
                if ( $id ) {
                    $summary['new']++;
                    if ( $src === 'newsapi' ) {
                        $summary['newsapi_count']++;
                    } else {
                        $summary['gnews_count']++;
                    }
                } else {
                    $summary['duplicates']++;
                }
                $topic_source = $src;
            }

            if ( ! empty( $topic_source ) ) {
                $summary['sources_used'][ $topic_key ] = $topic_source;
            }

            // Rate limit between topics
            usleep( 200000 );
        }

        return $summary;
    }

    /* ─── Connection Tests ─── */

    /** Test GNews API key */
    public function test_gnews() {
        if ( ! $this->has_gnews_key() ) {
            return array( 'ok' => false, 'message' => 'GNEWS_API_KEY not set in .env or wp-config.' );
        }

        $url = add_query_arg( array(
            'q'      => 'technology',
            'lang'   => 'en',
            'max'    => 1,
            'apikey' => $this->gnews_key,
        ), $this->gnews_base );

        $response = wp_remote_get( $url, array( 'timeout' => 10 ) );

        if ( is_wp_error( $response ) ) {
            return array( 'ok' => false, 'message' => $response->get_error_message() );
        }

        $code = wp_remote_retrieve_response_code( $response );
        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( $code === 200 ) {
            $count = count( $body['articles'] ?? array() );
            return array( 'ok' => true, 'message' => "GNews connected. Got {$count} test article(s)." );
        }

        $err = $body['errors'][0] ?? "HTTP {$code}";
        return array( 'ok' => false, 'message' => "GNews error: {$err}" );
    }

    /** Test NewsAPI.org key */
    public function test_newsapi() {
        if ( ! $this->has_newsapi_key() ) {
            return array( 'ok' => false, 'message' => 'NEWSAPI_KEY not set in .env or wp-config.' );
        }

        $url = add_query_arg( array(
            'q'        => 'technology',
            'language' => 'en',
            'pageSize' => 1,
            'apiKey'   => $this->newsapi_key,
        ), $this->newsapi_base );

        $response = wp_remote_get( $url, array(
            'timeout'  => 10,
            'headers'  => array(
                'User-Agent' => 'TechPortal NewsRadar/2.0',
            ),
        ) );

        if ( is_wp_error( $response ) ) {
            return array( 'ok' => false, 'message' => $response->get_error_message() );
        }

        $code = wp_remote_retrieve_response_code( $response );
        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( $code === 200 ) {
            $count = count( $body['articles'] ?? array() );
            $total = $body['totalResults'] ?? 0;
            return array( 'ok' => true, 'message' => "NewsAPI connected. Got {$count} test article(s) ({$total} total results)." );
        }

        $err = $body['message'] ?? "HTTP {$code}";
        return array( 'ok' => false, 'message' => "NewsAPI error: {$err}" );
    }

    /** Test both APIs (legacy method kept for backward compat) */
    public function test_connection() {
        return $this->test_gnews();
    }
}
