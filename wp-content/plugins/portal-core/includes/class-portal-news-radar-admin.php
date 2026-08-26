<?php
/**
 * News Radar — Admin Page & AJAX
 *
 * @package PortalCore
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Portal_News_Radar_Admin {

    private static $instance = null;

    public static function instance() {
        if ( null === self::$instance ) self::$instance = new self();
        return self::$instance;
    }

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'register_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        // AJAX handlers
        add_action( 'wp_ajax_newsradar_fetch', array( $this, 'ajax_fetch' ) );
        add_action( 'wp_ajax_newsradar_action', array( $this, 'ajax_action' ) );
        add_action( 'wp_ajax_newsradar_test_api', array( $this, 'ajax_test_api' ) );
        // Cron
        add_action( 'newsradar_cron_fetch', array( $this, 'cron_fetch' ) );
    }

    /** Register News Radar admin page under Portal menu */
    public function register_menu() {
        add_submenu_page(
            'portal-core',
            __( 'News Radar', 'portal-core' ),
            __( 'News Radar', 'portal-core' ),
            'manage_options',
            'news-radar',
            array( $this, 'render_page' )
        );
    }

    /** Enqueue admin CSS/JS only on our page */
    public function enqueue_assets( $hook ) {
        if ( strpos( $hook, 'portal-core_page_news-radar' ) === false ) return;
        wp_enqueue_style( 'portal-news-radar', PORTAL_CORE_URL . 'assets/css/news-radar.css', array(), PORTAL_CORE_VERSION );
        wp_enqueue_script( 'portal-news-radar', PORTAL_CORE_URL . 'assets/js/news-radar.js', array( 'jquery' ), PORTAL_CORE_VERSION, true );
        wp_localize_script( 'portal-news-radar', 'newsRadar', array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'newsradar_nonce' ),
        ) );
    }

    /* ─── AJAX: Test API ─── */
    public function ajax_test_api() {
        check_ajax_referer( 'newsradar_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( 'Unauthorized' );

        $fetcher = new Portal_News_Radar_Fetcher();
        $result  = $fetcher->test_connection();
        wp_send_json( $result );
    }

    /* ─── AJAX: Fetch Now ─── */
    public function ajax_fetch() {
        check_ajax_referer( 'newsradar_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( 'Unauthorized' );

        $fetcher = new Portal_News_Radar_Fetcher();
        if ( ! $fetcher->has_key() ) {
            wp_send_json_error( 'GNEWS_API_KEY not configured.' );
        }

        $summary = $fetcher->fetch_all();
        wp_send_json_success( $summary );
    }

    /* ─── AJAX: Story Actions (view_source / create_draft / ignore) ─── */
    public function ajax_action() {
        check_ajax_referer( 'newsradar_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( 'Unauthorized' );

        $story_id = intval( $_POST['story_id'] ?? 0 );
        $action   = sanitize_text_field( $_POST['story_action'] ?? '' );

        if ( ! $story_id || ! $action ) {
            wp_send_json_error( 'Missing parameters.' );
        }

        global $wpdb;
        $table = Portal_News_Radar_DB::table_name();
        $story = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $story_id ) );

        if ( ! $story ) wp_send_json_error( 'Story not found.' );

        switch ( $action ) {
            case 'view_source':
                wp_send_json_success( array( 'url' => $story->source_url ?: $story->gnews_url ) );
                break;

            case 'ignore':
                Portal_News_Radar_DB::set_status( $story_id, 'ignored' );
                wp_send_json_success( array( 'message' => 'Story ignored.' ) );
                break;

            case 'create_draft':
                $result = $this->create_draft_from_story( $story );
                wp_send_json( $result );
                break;

            default:
                wp_send_json_error( 'Unknown action.' );
        }
    }

    /** Create a WordPress draft from a radar story */
    private function create_draft_from_story( $story ) {
        // Find or create category
        $cat_id = get_cat_ID( $story->category_suggested );
        if ( ! $cat_id && ! empty( $story->category_suggested ) ) {
            $cat_id = wp_insert_category( array(
                'cat_name'   => ucwords( str_replace( '-', ' ', $story->category_suggested ) ),
                'cat_slug'   => $story->category_suggested,
            ) );
        }

        // Build content
        $content = '';
        if ( ! empty( $story->description ) ) {
            $content .= '<p>' . esc_html( $story->description ) . '</p>';
        }
        $content .= '<!-- wp:paragraph -->' . "\n";
        $content .= '<p><em>';
        $content .= sprintf(
            'Source: <a href="%s" target="_blank" rel="noopener">%s</a>',
            esc_url( $story->source_url ?: $story->gnews_url ),
            esc_html( $story->source_name ?: 'Original Source' )
        );
        $content .= '</em></p>' . "\n";
        $content .= '<!-- /wp:paragraph -->';

        // Featured image
        $featured_id = null;
        if ( ! empty( $story->image_url ) ) {
            $featured_id = $this->download_featured_image( $story->image_url, $story->title );
        }

        $post_data = array(
            'post_title'   => $story->title,
            'post_content' => $content,
            'post_status'  => 'draft',
            'post_category'=> $cat_id ? array( $cat_id ) : array(),
            'meta_input'   => array(
                '_news_radar_source_url'  => $story->gnews_url,
                '_news_radar_source_name' => $story->source_name,
                '_news_radar_story_id'    => $story->id,
                '_news_radar_fetched_at'  => $story->created_at,
            ),
        );

        $post_id = wp_insert_post( $post_data, true );

        if ( is_wp_error( $post_id ) ) {
            return array( 'ok' => false, 'message' => $post_id->get_error_message() );
        }

        if ( $featured_id ) {
            set_post_thumbnail( $post_id, $featured_id );
        }

        // Update radar story status
        Portal_News_Radar_DB::set_status( $story->id, 'drafted', $post_id );

        $edit_url = get_edit_post_link( $post_id, 'raw' );
        return array(
            'ok'      => true,
            'message' => "Draft created (Post #{$post_id})",
            'post_id' => $post_id,
            'edit_url'=> admin_url( 'post.php?post=' . $post_id . '&action=edit' ),
        );
    }

    /** Download and attach featured image */
    private function download_featured_image( $url, $title ) {
        if ( ! function_exists( 'media_handle_sideload' ) ) {
            require_once ABSPATH . 'wp-admin/includes/media.php';
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';
        }

        $tmp = download_url( $url, 10 );
        if ( is_wp_error( $tmp ) ) return false;

        $ext  = pathinfo( parse_url( $url, PHP_URL_PATH ), PATHINFO_EXTENSION ) ?: 'jpg';
        $file = array(
            'name'     => sanitize_file_name( substr( $title, 0, 50 ) . '.' . $ext ),
            'tmp_name' => $tmp,
            'error'    => 0,
            'size'     => filesize( $tmp ),
        );

        $id = media_handle_sideload( $file, 0 );
        return is_wp_error( $id ) ? false : $id;
    }

    /* ─── Cron Fetch ─── */
    public function cron_fetch() {
        $fetcher = new Portal_News_Radar_Fetcher();
        if ( ! $fetcher->has_key() ) return;
        $fetcher->fetch_all();
    }

    /* ─── Render the admin page ─── */
    public function render_page() {
        global $wpdb;
        $table = Portal_News_Radar_DB::table_name();
        $fetcher = new Portal_News_Radar_Fetcher();
        $has_key = $fetcher->has_key();

        $counts = array(
            'all'       => Portal_News_Radar_DB::count_stories(),
            'new'       => Portal_News_Radar_DB::count_stories( array( 'status' => 'new' ) ),
            'drafted'   => Portal_News_Radar_DB::count_stories( array( 'status' => 'drafted' ) ),
            'ignored'   => Portal_News_Radar_DB::count_stories( array( 'status' => 'ignored' ) ),
        );

        // Filter
        $filter_status = sanitize_text_field( $_GET['status'] ?? '' );
        $filter_topic  = sanitize_text_field( $_GET['topic'] ?? '' );
        $current_page  = max( 1, intval( $_GET['paged'] ?? 1 ) );

        $stories = Portal_News_Radar_DB::get_stories( array(
            'status'   => $filter_status,
            'topic'    => $filter_topic,
            'per_page' => 20,
            'page'     => $current_page,
        ) );

        $total = Portal_News_Radar_DB::count_stories( array(
            'status' => $filter_status,
            'topic'  => $filter_topic,
        ) );
        $total_pages = max( 1, ceil( $total / 20 ) );

        // Cron status
        $cron_next = wp_next_scheduled( 'newsradar_cron_fetch' );
        $cron_active = (bool) $cron_next;
        $cron_time = $cron_next ? gmdate( 'Y-m-d H:i:s', $cron_next ) : '—';
        ?>
        <div class="wrap news-radar-wrap">
            <h1>📡 News Radar</h1>

            <?php if ( ! $has_key ): ?>
            <div class="notice notice-error">
                <p><strong>GNEWS_API_KEY is not configured.</strong></p>
                <p>Add your GNews API key to <code>/root/workspace/tech-media-portal/.env</code> as <code>GNEWS_API_KEY=your_key_here</code>, or define it in <code>wp-config.php</code>.</p>
                <p>Get a free key at <a href="https://gnews.io/register" target="_blank">gnews.io/register</a> (100 requests/day free tier).</p>
            </div>
            <?php endif; ?>

            <!-- Status bar -->
            <div class="newsradar-status-bar">
                <div class="status-cards">
                    <div class="status-card"><span class="num"><?php echo $counts['all']; ?></span><span class="label">Total Stories</span></div>
                    <div class="status-card new"><span class="num"><?php echo $counts['new']; ?></span><span class="label">New</span></div>
                    <div class="status-card drafted"><span class="num"><?php echo $counts['drafted']; ?></span><span class="label">Drafted</span></div>
                    <div class="status-card ignored"><span class="num"><?php echo $counts['ignored']; ?></span><span class="label">Ignored</span></div>
                </div>
                <div class="status-actions">
                    <button id="nr-test-api" class="button" <?php echo $has_key ? '' : 'disabled'; ?>>🔌 Test API</button>
                    <button id="nr-fetch-now" class="button button-primary" <?php echo $has_key ? '' : 'disabled'; ?>>⚡ Fetch Now</button>
                    <span id="nr-api-result"></span>
                </div>
            </div>

            <!-- Cron info -->
            <div class="newsradar-cron-info">
                <span>Cron: <?php echo $cron_active ? "✅ Active — next run {$cron_time}" : "⏸ Inactive"; ?></span>
                <span>Topics: <?php echo count( Portal_News_Radar_Fetcher::TOPICS ); ?> (<?php echo implode( ', ', array_keys( Portal_News_Radar_Fetcher::TOPICS ) ); ?>)</span>
            </div>

            <!-- Filters -->
            <div class="newsradar-filters">
                <a href="?page=news-radar" class="button <?php echo empty( $filter_status ) ? 'button-primary' : ''; ?>">All (<?php echo $counts['all']; ?>)</a>
                <a href="?page=news-radar&status=new" class="button <?php echo $filter_status === 'new' ? 'button-primary' : ''; ?>">New (<?php echo $counts['new']; ?>)</a>
                <a href="?page=news-radar&status=drafted" class="button <?php echo $filter_status === 'drafted' ? 'button-primary' : ''; ?>">Drafted (<?php echo $counts['drafted']; ?>)</a>
                <a href="?page=news-radar&status=ignored" class="button <?php echo $filter_status === 'ignored' ? 'button-primary' : ''; ?>">Ignored (<?php echo $counts['ignored']; ?>)</a>
                <span class="separator">|</span>
                <select id="nr-topic-filter" onchange="window.location='?page=news-radar&topic='+this.value+'&status=<?php echo esc_attr( $filter_status ); ?>'">
                    <option value="">All Topics</option>
                    <?php foreach ( Portal_News_Radar_Fetcher::TOPICS as $k => $v ): ?>
                    <option value="<?php echo esc_attr( $k ); ?>" <?php selected( $filter_topic, $k ); ?>><?php echo esc_html( $v ); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Stories table -->
            <?php if ( empty( $stories ) ): ?>
            <div class="newsradar-empty">
                <p>📭 No stories found.<?php echo $has_key ? ' Click <strong>Fetch Now</strong> to pull stories from GNews.' : ' Configure your API key above to get started.'; ?></p>
            </div>
            <?php else: ?>
            <table class="wp-list-table widefat fixed striped newsradar-table">
                <thead>
                    <tr>
                        <th class="column-title" style="width:30%">Headline</th>
                        <th style="width:10%">Topic</th>
                        <th style="width:12%">Source</th>
                        <th style="width:10%">Category</th>
                        <th style="width:10%">Published</th>
                        <th style="width:8%">Status</th>
                        <th style="width:20%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ( $stories as $s ): ?>
                    <tr data-story-id="<?php echo esc_attr( $s->id ); ?>">
                        <td>
                            <strong class="story-title"><?php echo esc_html( mb_substr( $s->title, 0, 80 ) ); ?></strong>
                            <?php if ( $s->description ): ?>
                            <p class="story-desc"><?php echo esc_html( mb_substr( $s->description, 0, 120 ) ); ?>…</p>
                            <?php endif; ?>
                            <?php if ( $s->image_url ): ?>
                            <img src="<?php echo esc_url( $s->image_url ); ?>" class="story-thumb" alt="" />
                            <?php endif; ?>
                        </td>
                        <td><span class="topic-badge topic-<?php echo esc_attr( $s->topic ); ?>"><?php echo esc_html( $s->topic ); ?></span></td>
                        <td><?php echo esc_html( $s->source_name ?: '—' ); ?></td>
                        <td><?php echo esc_html( $s->category_suggested ?: '—' ); ?></td>
                        <td><?php echo $s->published_at ? gmdate( 'M j, Y g:i a', strtotime( $s->published_at ) ) : '—'; ?></td>
                        <td><span class="status-badge status-<?php echo esc_attr( $s->status ); ?>"><?php echo esc_html( $s->status ); ?></span></td>
                        <td class="story-actions">
                            <?php if ( $s->status === 'new' ): ?>
                            <button class="button button-small nr-action" data-action="view_source" data-id="<?php echo $s->id; ?>" title="View Source">🔗 Source</button>
                            <button class="button button-small button-primary nr-action" data-action="create_draft" data-id="<?php echo $s->id; ?>" title="Create Draft">📝 Draft</button>
                            <button class="button button-small nr-action" data-action="ignore" data-id="<?php echo $s->id; ?>" title="Ignore">🗑 Ignore</button>
                            <?php elseif ( $s->status === 'drafted' ): ?>
                            <a href="<?php echo admin_url( 'post.php?post=' . $s->wp_post_id . '&action=edit' ); ?>" class="button button-small" target="_blank">✏️ Edit Draft</a>
                            <button class="button button-small nr-action" data-action="view_source" data-id="<?php echo $s->id; ?>">🔗 Source</button>
                            <?php else: ?>
                            <button class="button button-small nr-action" data-action="view_source" data-id="<?php echo $s->id; ?>">🔗 Source</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php if ( $total_pages > 1 ): ?>
            <div class="tablenav bottom">
                <div class="tablenav-pages">
                    <?php
                    echo paginate_links( array(
                        'base'    => add_query_arg( 'paged', '%#%' ),
                        'format'  => '',
                        'current' => $current_page,
                        'total'   => $total_pages,
                        'prev_text' => '&laquo;',
                        'next_text' => '&raquo;',
                    ) );
                    ?>
                </div>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php
    }
}
