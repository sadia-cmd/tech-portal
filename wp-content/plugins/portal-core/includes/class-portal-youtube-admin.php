<?php
/**
 * YouTube Admin Page
 *
 * Provides admin UI for YouTube synchronization.
 *
 * @package PortalCore
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Portal_YouTube_Admin {

    private static $instance = null;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'register_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );

        // AJAX handlers
        add_action( 'wp_ajax_youtube_test_connection', array( $this, 'ajax_test_connection' ) );
        add_action( 'wp_ajax_youtube_sync_now', array( $this, 'ajax_sync_now' ) );
        add_action( 'wp_ajax_youtube_fetch_channel', array( $this, 'ajax_fetch_channel' ) );

        // Cron hook
        add_action( 'portal_youtube_sync', array( $this, 'cron_sync' ) );
    }

    /**
     * Register YouTube Sync admin page
     */
    public function register_menu() {
        add_submenu_page(
            'portal-core',
            __( 'YouTube Sync', 'portal-core' ),
            __( 'YouTube Sync', 'portal-core' ),
            'manage_options',
            'youtube-sync',
            array( $this, 'render_page' )
        );
    }

    /**
     * Enqueue admin CSS/JS on our page
     */
    public function enqueue_assets( $hook ) {
        if ( strpos( $hook, 'youtube-sync' ) === false ) {
            return;
        }
        wp_enqueue_style( 'portal-youtube-sync', PORTAL_CORE_URL . 'assets/css/youtube-sync.css', array(), PORTAL_CORE_VERSION );
        wp_enqueue_script( 'portal-youtube-sync', PORTAL_CORE_URL . 'assets/js/youtube-sync.js', array(), PORTAL_CORE_VERSION, true );
        wp_localize_script( 'portal-youtube-sync', 'youtubeSync', array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'youtube_sync_nonce' ),
        ) );
    }

    /**
     * AJAX: Test YouTube API connection
     */
    public function ajax_test_connection() {
        check_ajax_referer( 'youtube_sync_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Unauthorized' ) );
        }

        $yt = new Portal_YouTube();
        $result = $yt->test_connection();
        wp_send_json( $result );
    }

    /**
     * AJAX: Manual sync
     */
    public function ajax_sync_now() {
        check_ajax_referer( 'youtube_sync_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Unauthorized' ) );
        }

        $yt = new Portal_YouTube();
        if ( ! $yt->has_api_key() || ! $yt->has_channel_id() ) {
            wp_send_json_error( array( 'message' => 'YouTube API not configured.' ) );
        }

        $result = $yt->sync_videos_to_episodes();
        $yt->update_last_sync_time();

        wp_send_json_success( $result );
    }

    /**
     * AJAX: Fetch channel info
     */
    public function ajax_fetch_channel() {
        check_ajax_referer( 'youtube_sync_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Unauthorized' ) );
        }

        $yt = new Portal_YouTube();
        $result = $yt->test_connection();
        wp_send_json( $result );
    }

    /**
     * Cron sync
     */
    public function cron_sync() {
        $yt = new Portal_YouTube();
        if ( ! $yt->has_api_key() || ! $yt->has_channel_id() ) {
            return;
        }

        $result = $yt->sync_videos_to_episodes();
        $yt->update_last_sync_time();
    }

    /**
     * Schedule cron
     */
    public function schedule_cron() {
        if ( ! wp_next_scheduled( 'portal_youtube_sync' ) ) {
            wp_schedule_event( time(), 'hourly', 'portal_youtube_sync' );
        }
    }

    /**
     * Render the admin page
     */
    public function render_page() {
        $yt = Portal_YouTube::instance();
        $has_key = $yt->has_api_key();
        $has_channel = $yt->has_channel_id();
        $last_sync = $yt->get_last_sync_time();

        // Cron status
        $cron_next = wp_next_scheduled( 'portal_youtube_sync' );
        $cron_active = (bool) $cron_next;
        $cron_time = $cron_next ? gmdate( 'Y-m-d H:i:s', $cron_next ) : '—';

        // Channel info
        $channel_info = '';
        if ( $has_key && $has_channel ) {
            $info = $yt->test_connection();
            $channel_info = $info['ok'] ? $info['message'] : $info['message'];
        }
        ?>
        <div class="wrap youtube-sync-wrap">
            <h1><?php _e( '📺 YouTube Sync', 'portal-core' ); ?></h1>

            <?php if ( ! $has_key || ! $has_channel ) : ?>
            <div class="notice notice-error">
                <p><strong><?php _e( 'YouTube API is not fully configured.', 'portal-core' ); ?></strong></p>
                <p><?php _e( 'Add the following to your .env file:', 'portal-core' ); ?></p>
                <pre style="background:#f6f7f7;padding:12px;border-radius:6px;font-size:13px;margin-top:8px;"><code><?php echo $has_key ? 'YOUTUBE_API_KEY=' . substr( $yt->get_api_key(), 0, 8 ) . '...' : 'YOUTUBE_API_KEY=your_api_key_here'; ?>
<?php echo $has_channel ? 'YOUTUBE_CHANNEL_ID=' . substr( $yt->get_channel_id(), 0, 12 ) . '...' : 'YOUTUBE_CHANNEL_ID=your_channel_id_here'; ?></code></pre>
                <p style="margin-top:8px;font-size:13px;color:#666;">
                    <?php _e( 'Get your API key at', 'portal-core' ); ?> <a href="https://console.cloud.google.com/apis/credentials" target="_blank"><?php _e( 'Google Cloud Console', 'portal-core' ); ?></a>
                    <?php _e( '(enable YouTube Data API v3)', 'portal-core' ); ?>
                </p>
            </div>
            <?php endif; ?>

            <!-- Status Cards -->
            <div class="yt-sync-status-bar">
                <div class="yt-status-cards">
                    <div class="yt-status-card">
                        <span class="yt-status-card__num"><?php echo number_format( $yt->count_cached_videos() ); ?></span>
                        <span class="yt-status-card__label"><?php _e( 'Videos Cached', 'portal-core' ); ?></span>
                    </div>
                    <div class="yt-status-card yt-status-card--live">
                        <span class="yt-status-card__num"><?php echo $cron_active ? '✅' : '⏸'; ?></span>
                        <span class="yt-status-card__label"><?php _e( 'Auto Sync', 'portal-core' ); ?></span>
                    </div>
                    <div class="yt-status-card yt-status-card--time">
                        <span class="yt-status-card__num" style="font-size:14px;"><?php echo $last_sync ? human_time_diff( strtotime( $last_sync ), current_time( 'timestamp' ) ) . ' ago' : 'Never'; ?></span>
                        <span class="yt-status-card__label"><?php _e( 'Last Sync', 'portal-core' ); ?></span>
                    </div>
                </div>

                <div class="yt-sync-actions">
                    <button id="yt-test-btn" class="button" <?php echo ( $has_key && $has_channel ) ? '' : 'disabled'; ?>>
                        🔌 <?php _e( 'Test Connection', 'portal-core' ); ?>
                    </button>
                    <button id="yt-sync-btn" class="button button-primary" <?php echo ( $has_key && $has_channel ) ? '' : 'disabled'; ?>>
                        ⚡ <?php _e( 'Sync Now', 'portal-core' ); ?>
                    </button>
                    <span id="yt-result"></span>
                </div>
            </div>

            <!-- Channel Info -->
            <?php if ( $channel_info ) : ?>
            <div class="yt-channel-info">
                <strong><?php _e( 'Channel:', 'portal-core' ); ?></strong> <?php echo esc_html( $channel_info ); ?>
                <span style="margin-left:16px;color:#666;">
                    <?php _e( 'Cron:', 'portal-core' ); ?>
                    <?php echo $cron_active ? sprintf( __( 'Active — next run %s', 'portal-core' ), $cron_time ) : __( 'Inactive', 'portal-core' ); ?>
                </span>
            </div>
            <?php endif; ?>

            <!-- Cached Videos Table -->
            <?php
            $videos = $yt->get_cached_videos( array( 'per_page' => 20 ) );
            $total_videos = $yt->count_cached_videos();
            ?>
            <h2><?php _e( 'Cached Videos', 'portal-core' ); ?> (<?php echo $total_videos; ?>)</h2>

            <?php if ( empty( $videos ) ) : ?>
            <div class="yt-empty">
                <p>📭 <?php _e( 'No videos cached yet. Click "Sync Now" to fetch videos from your YouTube channel.', 'portal-core' ); ?></p>
            </div>
            <?php else : ?>
            <table class="wp-list-table widefat fixed striped yt-videos-table">
                <thead>
                    <tr>
                        <th style="width:5%"><?php _e( 'Thumb', 'portal-core' ); ?></th>
                        <th style="width:35%"><?php _e( 'Title', 'portal-core' ); ?></th>
                        <th style="width:10%"><?php _e( 'Status', 'portal-core' ); ?></th>
                        <th style="width:10%"><?php _e( 'Duration', 'portal-core' ); ?></th>
                        <th style="width:10%"><?php _e( 'Views', 'portal-core' ); ?></th>
                        <th style="width:12%"><?php _e( 'Published', 'portal-core' ); ?></th>
                        <th style="width:8%"><?php _e( 'Synced', 'portal-core' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ( $videos as $v ) : ?>
                    <tr>
                        <td>
                            <?php if ( $v->thumbnail_url ) : ?>
                                <img src="<?php echo esc_url( $v->thumbnail_url ); ?>" style="width:60px;height:34px;object-fit:cover;border-radius:4px;" alt="" />
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?php echo esc_html( mb_substr( $v->title, 0, 80 ) ); ?></strong>
                            <div style="font-size:12px;color:#666;margin-top:2px;">ID: <?php echo esc_html( $v->video_id ); ?></div>
                        </td>
                        <td>
                            <?php if ( $v->is_live ) : ?>
                                <span class="yt-badge yt-badge--live">🔴 LIVE</span>
                            <?php elseif ( $v->is_upcoming ) : ?>
                                <span class="yt-badge yt-badge--upcoming">🕐 UPCOMING</span>
                            <?php else : ?>
                                <span class="yt-badge yt-badge--published">✅ PUBLISHED</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo esc_html( $v->duration ); ?></td>
                        <td><?php echo number_format( $v->view_count ); ?></td>
                        <td><?php echo $v->published_at ? date( 'M j, Y', strtotime( $v->published_at ) ) : '—'; ?></td>
                        <td><?php echo $v->synced_at ? human_time_diff( strtotime( $v->synced_at ), current_time( 'timestamp' ) ) . ' ago' : '—'; ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
        <?php
    }
}
