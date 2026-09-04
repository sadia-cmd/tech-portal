<?php
/**
 * Portal Membership — DB, Auth, Bookmarks, Saved
 *
 * @package PortalCore
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Portal_Membership {

    private static $instance = null;

    public static function instance() {
        if ( null === self::$instance ) self::$instance = new self();
        return self::$instance;
    }

    private function __construct() {
        // Shortcodes
        add_shortcode( 'tp_login',        array( $this, 'shortcode_login' ) );
        add_shortcode( 'tp_register',     array( $this, 'shortcode_register' ) );
        add_shortcode( 'tp_my_account',   array( $this, 'shortcode_my_account' ) );
        add_shortcode( 'tp_bookmarks',    array( $this, 'shortcode_bookmarks' ) );

        // AJAX
        add_action( 'wp_ajax_tp_login',              array( $this, 'ajax_login' ) );
        add_action( 'wp_ajax_nopriv_tp_login',        array( $this, 'ajax_login' ) );
        add_action( 'wp_ajax_tp_register',            array( $this, 'ajax_register' ) );
        add_action( 'wp_ajax_nopriv_tp_register',     array( $this, 'ajax_register' ) );
        add_action( 'wp_ajax_tp_logout',              array( $this, 'ajax_logout' ) );
        add_action( 'wp_ajax_tp_toggle_bookmark',     array( $this, 'ajax_toggle_bookmark' ) );
        add_action( 'wp_ajax_tp_get_bookmarks',       array( $this, 'ajax_get_bookmarks' ) );
        add_action( 'wp_ajax_tp_update_profile',      array( $this, 'ajax_update_profile' ) );
        add_action( 'wp_ajax_tp_change_password',     array( $this, 'ajax_change_password' ) );

        // Enqueue
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );

        // Comment form enhancements
        add_filter( 'comment_form_defaults', array( $this, 'enhance_comment_form' ) );
        add_action( 'preprocess_comment', array( $this, 'require_login_for_comment' ) );

        // Header: show login/register or user menu
        add_action( 'tp_header_actions', array( $this, 'header_user_menu' ) );

        // Create tables
        add_action( 'init', array( $this, 'maybe_create_tables' ), 1 );
    }

    /* ─── Database ─── */

    public function maybe_create_tables() {
        global $wpdb;
        $table = $wpdb->prefix . 'portal_bookmarks';
        if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
            $this->create_tables();
        }
    }

    public function create_tables() {
        global $wpdb;
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$wpdb->prefix}portal_bookmarks (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT UNSIGNED NOT NULL,
            post_id BIGINT UNSIGNED NOT NULL,
            post_type VARCHAR(50) NOT NULL DEFAULT 'post',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY user_post (user_id, post_id),
            KEY user_id (user_id),
            KEY post_id (post_id)
        ) {$charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    /* ─── Shortcodes ─── */

    public function shortcode_login() {
        if ( is_user_logged_in() ) {
            return '<div class="tp-auth-notice">You are already logged in. <a href="' . esc_url( home_url( '/my-account/' ) ) . '">Go to My Account →</a></div>';
        }
        ob_start(); ?>
        <div class="tp-auth-page">
            <div class="tp-auth-card">
                <div class="tp-auth-card__icon">👤</div>
                <h1 class="tp-auth-card__title">Sign In</h1>
                <p class="tp-auth-card__subtitle">Welcome back to TechPortal</p>
                <div id="tp-login-error" class="tp-auth-error" style="display:none;"></div>
                <form id="tp-login-form" class="tp-auth-form">
                    <?php wp_nonce_field( 'tp_auth_nonce', 'tp_nonce' ); ?>
                    <div class="tp-form-group">
                        <label for="tp-login-email">Email or Username</label>
                        <input type="text" id="tp-login-email" name="log" required placeholder="you@example.com" autocomplete="username" />
                    </div>
                    <div class="tp-form-group">
                        <label for="tp-login-pass">Password</label>
                        <input type="password" id="tp-login-pass" name="pwd" required placeholder="••••••••" autocomplete="current-password" />
                    </div>
                    <button type="submit" class="tp-btn tp-btn--primary tp-btn--full">Sign In</button>
                </form>
                <div class="tp-auth-card__footer">
                    Don't have an account? <a href="<?php echo esc_url( home_url( '/register/' ) ); ?>">Create one →</a>
                </div>
            </div>
        </div>
        <?php return ob_get_clean();
    }

    public function shortcode_register() {
        if ( is_user_logged_in() ) {
            return '<div class="tp-auth-notice">You are already registered. <a href="' . esc_url( home_url( '/my-account/' ) ) . '">Go to My Account →</a></div>';
        }
        ob_start(); ?>
        <div class="tp-auth-page">
            <div class="tp-auth-card">
                <div class="tp-auth-card__icon">✨</div>
                <h1 class="tp-auth-card__title">Create Account</h1>
                <p class="tp-auth-card__subtitle">Join Pakistan's tech community</p>
                <div id="tp-register-error" class="tp-auth-error" style="display:none;"></div>
                <form id="tp-register-form" class="tp-auth-form">
                    <?php wp_nonce_field( 'tp_auth_nonce', 'tp_nonce' ); ?>
                    <div class="tp-form-group">
                        <label for="tp-reg-name">Full Name</label>
                        <input type="text" id="tp-reg-name" name="display_name" required placeholder="Ahmed Khan" autocomplete="name" />
                    </div>
                    <div class="tp-form-group">
                        <label for="tp-reg-email">Email Address</label>
                        <input type="email" id="tp-reg-email" name="user_email" required placeholder="you@example.com" autocomplete="email" />
                    </div>
                    <div class="tp-form-group">
                        <label for="tp-reg-user">Username</label>
                        <input type="text" id="tp-reg-user" name="user_login" required placeholder="ahmedkhan" autocomplete="username" />
                    </div>
                    <div class="tp-form-group">
                        <label for="tp-reg-pass">Password</label>
                        <input type="password" id="tp-reg-pass" name="user_pass" required placeholder="Min 8 characters" minlength="8" autocomplete="new-password" />
                    </div>
                    <button type="submit" class="tp-btn tp-btn--primary tp-btn--full">Create Account</button>
                </form>
                <div class="tp-auth-card__footer">
                    Already have an account? <a href="<?php echo esc_url( home_url( '/login/' ) ); ?>">Sign in →</a>
                </div>
            </div>
        </div>
        <?php return ob_get_clean();
    }

    public function shortcode_my_account() {
        if ( ! is_user_logged_in() ) {
            return '<div class="tp-auth-notice">Please <a href="' . esc_url( home_url( '/login/' ) ) . '">sign in</a> to view your account.</div>';
        }
        $user  = wp_get_current_user();
        $count = $this->count_bookmarks( $user->ID );
        ob_start(); ?>
        <div class="tp-account-page">
            <div class="tp-account-header">
                <div class="tp-account-avatar">
                    <?php echo esc_html( mb_substr( $user->display_name, 0, 1 ) ); ?>
                </div>
                <div>
                    <h1 class="tp-account-name"><?php echo esc_html( $user->display_name ); ?></h1>
                    <p class="tp-account-email"><?php echo esc_html( $user->user_email ); ?></p>
                    <p class="tp-account-meta">Member since <?php echo esc_html( date( 'F Y', strtotime( $user->user_registered ) ) ); ?> · <?php echo $count; ?> saved items</p>
                </div>
            </div>

            <div class="tp-account-tabs">
                <button class="tp-account-tab active" data-tab="profile">Profile</button>
                <button class="tp-account-tab" data-tab="saved">Saved (<?php echo $count; ?>)</button>
                <button class="tp-account-tab" data-tab="password">Password</button>
            </div>

            <!-- Profile Tab -->
            <div class="tp-account-panel active" id="tp-panel-profile">
                <div id="tp-profile-msg" class="tp-auth-error" style="display:none;"></div>
                <form id="tp-profile-form" class="tp-auth-form">
                    <?php wp_nonce_field( 'tp_auth_nonce', 'tp_nonce' ); ?>
                    <div class="tp-form-row">
                        <div class="tp-form-group">
                            <label for="tp-prof-name">Full Name</label>
                            <input type="text" id="tp-prof-name" name="display_name" value="<?php echo esc_attr( $user->display_name ); ?>" required />
                        </div>
                        <div class="tp-form-group">
                            <label for="tp-prof-email">Email</label>
                            <input type="email" id="tp-prof-email" name="user_email" value="<?php echo esc_attr( $user->user_email ); ?>" required />
                        </div>
                    </div>
                    <div class="tp-form-group">
                        <label for="tp-prof-bio">Bio</label>
                        <textarea id="tp-prof-bio" name="description" rows="3" placeholder="Tell us about yourself…"><?php echo esc_textarea( $user->description ); ?></textarea>
                    </div>
                    <div class="tp-form-row">
                        <div class="tp-form-group">
                            <label for="tp-prof-url">Website</label>
                            <input type="url" id="tp-prof-url" name="user_url" value="<?php echo esc_url( $user->user_url ); ?>" placeholder="https://" />
                        </div>
                        <div class="tp-form-group">
                            <label for="tp-prof-location">Location</label>
                            <input type="text" id="tp-prof-location" name="tp_location" value="<?php echo esc_attr( get_user_meta( $user->ID, 'tp_location', true ) ); ?>" placeholder="Lahore, Pakistan" />
                        </div>
                    </div>
                    <button type="submit" class="tp-btn tp-btn--primary">Save Changes</button>
                </form>
            </div>

            <!-- Saved Tab -->
            <div class="tp-account-panel" id="tp-panel-saved">
                <div id="tp-saved-list">
                    <p style="color:var(--tp-muted);">Loading saved items…</p>
                </div>
            </div>

            <!-- Password Tab -->
            <div class="tp-account-panel" id="tp-panel-password">
                <div id="tp-pass-msg" class="tp-auth-error" style="display:none;"></div>
                <form id="tp-password-form" class="tp-auth-form">
                    <?php wp_nonce_field( 'tp_auth_nonce', 'tp_nonce' ); ?>
                    <div class="tp-form-group">
                        <label for="tp-pass-current">Current Password</label>
                        <input type="password" id="tp-pass-current" name="current_password" required autocomplete="current-password" />
                    </div>
                    <div class="tp-form-group">
                        <label for="tp-pass-new">New Password</label>
                        <input type="password" id="tp-pass-new" name="new_password" required minlength="8" autocomplete="new-password" />
                    </div>
                    <div class="tp-form-group">
                        <label for="tp-pass-confirm">Confirm New Password</label>
                        <input type="password" id="tp-pass-confirm" name="confirm_password" required minlength="8" autocomplete="new-password" />
                    </div>
                    <button type="submit" class="tp-btn tp-btn--primary">Change Password</button>
                </form>
            </div>

            <div class="tp-account-logout">
                <a href="#" id="tp-logout-btn" class="tp-btn tp-btn--outline">Sign Out</a>
            </div>
        </div>
        <?php return ob_get_clean();
    }

    public function shortcode_bookmarks() {
        if ( ! is_user_logged_in() ) {
            return '<div class="tp-auth-notice">Please <a href="' . esc_url( home_url( '/login/' ) ) . '">sign in</a> to view your bookmarks.</div>';
        }
        ob_start(); ?>
        <div class="tp-account-page">
            <h1 style="margin-bottom:var(--tp-space-6);">My Bookmarks</h1>
            <div id="tp-bookmarks-page-list"><p style="color:var(--tp-muted);">Loading…</p></div>
        </div>
        <?php return ob_get_clean();
    }

    /* ─── AJAX Handlers ─── */

    public function ajax_login() {
        check_ajax_referer( 'tp_auth_nonce', 'tp_nonce' );

        $user_login = sanitize_text_field( $_POST['log'] ?? '' );
        $user_pass  = $_POST['pwd'] ?? '';

        $creds = array(
            'user_login'    => $user_login,
            'user_password' => $user_pass,
            'remember'      => true,
        );
        $user = wp_signon( $creds, is_ssl() );

        if ( is_wp_error( $user ) ) {
            wp_send_json_error( array( 'message' => 'Invalid credentials. Please try again.' ) );
        }
        wp_send_json_success( array( 'message' => 'Logged in!', 'redirect' => home_url( '/my-account/' ) ) );
    }

    public function ajax_register() {
        check_ajax_referer( 'tp_auth_nonce', 'tp_nonce' );

        $display_name = sanitize_text_field( $_POST['display_name'] ?? '' );
        $user_email   = sanitize_email( $_POST['user_email'] ?? '' );
        $user_login   = sanitize_user( $_POST['user_login'] ?? '' );
        $user_pass    = $_POST['user_pass'] ?? '';

        if ( empty( $display_name ) || empty( $user_email ) || empty( $user_login ) || empty( $user_pass ) ) {
            wp_send_json_error( array( 'message' => 'All fields are required.' ) );
        }
        if ( strlen( $user_pass ) < 8 ) {
            wp_send_json_error( array( 'message' => 'Password must be at least 8 characters.' ) );
        }
        if ( username_exists( $user_login ) ) {
            wp_send_json_error( array( 'message' => 'Username already taken.' ) );
        }
        if ( email_exists( $user_email ) ) {
            wp_send_json_error( array( 'message' => 'Email already registered.' ) );
        }

        $user_id = wp_create_user( $user_login, $user_pass, $user_email );
        if ( is_wp_error( $user_id ) ) {
            wp_send_json_error( array( 'message' => $user_id->get_error_message() ) );
        }

        wp_update_user( array( 'ID' => $user_id, 'display_name' => $display_name ) );
        wp_update_user( array( 'ID' => $user_id, 'role' => 'subscriber' ) );

        // Auto-login
        wp_set_current_user( $user_id );
        wp_set_auth_cookie( $user_id, true );

        wp_send_json_success( array( 'message' => 'Account created!', 'redirect' => home_url( '/my-account/' ) ) );
    }

    public function ajax_logout() {
        wp_logout();
        wp_send_json_success( array( 'message' => 'Signed out.', 'redirect' => home_url( '/' ) ) );
    }

    public function ajax_update_profile() {
        check_ajax_referer( 'tp_auth_nonce', 'tp_nonce' );
        if ( ! is_user_logged_in() ) wp_send_json_error( array( 'message' => 'Not logged in.' ) );

        $user_id = get_current_user_id();
        wp_update_user( array(
            'ID'           => $user_id,
            'display_name' => sanitize_text_field( $_POST['display_name'] ?? '' ),
            'user_email'   => sanitize_email( $_POST['user_email'] ?? '' ),
            'user_url'     => esc_url_raw( $_POST['user_url'] ?? '' ),
            'description'  => sanitize_textarea_field( $_POST['description'] ?? '' ),
        ) );
        update_user_meta( $user_id, 'tp_location', sanitize_text_field( $_POST['tp_location'] ?? '' ) );

        wp_send_json_success( array( 'message' => 'Profile updated!' ) );
    }

    public function ajax_change_password() {
        check_ajax_referer( 'tp_auth_nonce', 'tp_nonce' );
        if ( ! is_user_logged_in() ) wp_send_json_error( array( 'message' => 'Not logged in.' ) );

        $user = wp_get_current_user();
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if ( ! wp_check_password( $current, $user->user_pass, $user->ID ) ) {
            wp_send_json_error( array( 'message' => 'Current password is incorrect.' ) );
        }
        if ( strlen( $new ) < 8 ) {
            wp_send_json_error( array( 'message' => 'New password must be at least 8 characters.' ) );
        }
        if ( $new !== $confirm ) {
            wp_send_json_error( array( 'message' => 'Passwords do not match.' ) );
        }

        wp_set_password( $new, $user->ID );
        wp_set_current_user( $user->ID );
        wp_set_auth_cookie( $user->ID, true );

        wp_send_json_success( array( 'message' => 'Password changed!' ) );
    }

    public function ajax_toggle_bookmark() {
        check_ajax_referer( 'tp_auth_nonce', 'tp_nonce' );
        if ( ! is_user_logged_in() ) wp_send_json_error( array( 'message' => 'Please sign in to save articles.', 'login_url' => home_url( '/login/' ) ) );

        global $wpdb;
        $table   = $wpdb->prefix . 'portal_bookmarks';
        $user_id = get_current_user_id();
        $post_id = intval( $_POST['post_id'] ?? 0 );
        $post_type = sanitize_text_field( $_POST['post_type'] ?? 'post' );

        if ( ! $post_id ) wp_send_json_error( array( 'message' => 'Invalid post.' ) );

        $exists = $wpdb->get_var( $wpdb->prepare(
            "SELECT id FROM {$table} WHERE user_id = %d AND post_id = %d", $user_id, $post_id
        ) );

        if ( $exists ) {
            $wpdb->delete( $table, array( 'user_id' => $user_id, 'post_id' => $post_id ), array( '%d', '%d' ) );
            wp_send_json_success( array( 'bookmarked' => false, 'count' => $this->count_bookmarks( $user_id ) ) );
        } else {
            $wpdb->insert( $table, array(
                'user_id'   => $user_id,
                'post_id'   => $post_id,
                'post_type' => $post_type,
            ), array( '%d', '%d', '%s' ) );
            wp_send_json_success( array( 'bookmarked' => true, 'count' => $this->count_bookmarks( $user_id ) ) );
        }
    }

    public function ajax_get_bookmarks() {
        if ( ! is_user_logged_in() ) wp_send_json_error( array( 'message' => 'Not logged in.' ) );

        global $wpdb;
        $table = $wpdb->prefix . 'portal_bookmarks';
        $user_id = get_current_user_id();

        $bookmarks = $wpdb->get_results( $wpdb->prepare(
            "SELECT b.post_id, b.post_type, b.created_at, p.post_title, p.post_name, p.post_date
             FROM {$table} b
             JOIN {$wpdb->posts} p ON b.post_id = p.ID
             WHERE b.user_id = %d
             ORDER BY b.created_at DESC", $user_id
        ) );

        $items = array();
        foreach ( $bookmarks as $b ) {
            $thumb = get_the_post_thumbnail_url( $b->post_id, 'techportal-card' );
            $cat = '';
            if ( $b->post_type === 'post' || $b->post_type === 'portal_article' ) {
                $cats = get_the_category( $b->post_id );
                $cat = ! empty( $cats ) ? $cats[0]->name : '';
            }
            $items[] = array(
                'post_id'    => $b->post_id,
                'title'      => $b->post_title,
                'url'        => get_permalink( $b->post_id ),
                'date'       => date( 'M j, Y', strtotime( $b->post_date ) ),
                'thumbnail'  => $thumb ?: '',
                'category'   => $cat,
                'saved_date' => date( 'M j, Y', strtotime( $b->created_at ) ),
            );
        }

        wp_send_json_success( array( 'items' => $items ) );
    }

    /* ─── Helpers ─── */

    public function count_bookmarks( $user_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'portal_bookmarks';
        return intval( $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE user_id = %d", $user_id
        ) ) );
    }

    public function is_bookmarked( $user_id, $post_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'portal_bookmarks';
        return (bool) $wpdb->get_var( $wpdb->prepare(
            "SELECT id FROM {$table} WHERE user_id = %d AND post_id = %d", $user_id, $post_id
        ) );
    }

    /* ─── Bookmark Button (output for templates) ─── */

    public function bookmark_button( $post_id = 0 ) {
        if ( ! $post_id ) $post_id = get_the_ID();
        $post_type = get_post_type( $post_id );
        $logged_in = is_user_logged_in();
        $bookmarked = $logged_in && $this->is_bookmarked( get_current_user_id(), $post_id );

        printf(
            '<button class="tp-bookmark-btn %s" data-post-id="%d" data-post-type="%s" title="%s">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="%s" stroke="currentColor" stroke-width="2">
                    <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/>
                </svg>
            </button>',
            $bookmarked ? 'is-bookmarked' : '',
            $post_id,
            esc_attr( $post_type ),
            $logged_in ? ( $bookmarked ? 'Remove from saved' : 'Save article' ) : 'Sign in to save',
            $bookmarked ? 'currentColor' : 'none'
        );
    }

    /* ─── Comment Enhancements ─── */

    public function enhance_comment_form( $defaults ) {
        $defaults['title_reply'] = __( 'Join the Discussion', 'portal-core' );
        $defaults['title_reply_before'] = '<h3 id="reply-title" class="tp-comments__reply-title">';
        $defaults['title_reply_after']  = '</h3>';
        $defaults['cancel_reply_before'] = ' · ';
        $defaults['comment_notes_before'] = '';
        return $defaults;
    }

    public function require_login_for_comment( $commentdata ) {
        if ( ! is_user_logged_in() ) {
            wp_die( 'Please <a href="' . esc_url( home_url( '/login/' ) ) . '">sign in</a> to comment.', 403 );
        }
        return $commentdata;
    }

    /* ─── Header User Menu ─── */

    public function header_user_menu() {
        if ( is_user_logged_in() ) {
            $user = wp_get_current_user();
            ?>
            <div class="tp-user-menu">
                <button class="tp-user-menu__trigger" aria-label="Account menu">
                    <span class="tp-user-menu__avatar"><?php echo esc_html( mb_substr( $user->display_name, 0, 1 ) ); ?></span>
                    <span class="tp-user-menu__name"><?php echo esc_html( $user->display_name ); ?></span>
                </button>
                <div class="tp-user-menu__dropdown">
                    <a href="<?php echo esc_url( home_url( '/my-account/' ) ); ?>">My Account</a>
                    <a href="<?php echo esc_url( home_url( '/saved/' ) ); ?>">Saved Articles</a>
                    <?php if ( current_user_can( 'manage_options' ) ) : ?>
                        <a href="<?php echo esc_url( admin_url() ); ?>">Admin</a>
                    <?php endif; ?>
                    <hr />
                    <a href="#" id="tp-header-logout">Sign Out</a>
                </div>
            </div>
            <?php
        } else {
            ?>
            <div class="tp-auth-links">
                <a href="<?php echo esc_url( home_url( '/login/' ) ); ?>" class="tp-auth-links__login">Sign In</a>
                <a href="<?php echo esc_url( home_url( '/register/' ) ); ?>" class="tp-auth-links__register">Join</a>
            </div>
            <?php
        }
    }

    /* ─── Enqueue ─── */

    public function enqueue_assets() {
        global $post;

        // Always enqueue membership CSS + JS
        wp_enqueue_style( 'portal-membership', PORTAL_CORE_URL . 'assets/css/membership.css', array(), PORTAL_CORE_VERSION );
        wp_enqueue_script( 'portal-membership', PORTAL_CORE_URL . 'assets/js/membership.js', array(), PORTAL_CORE_VERSION, true );
        wp_localize_script( 'portal-membership', 'tpMembership', array(
            'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'tp_auth_nonce' ),
            'loggedIn' => is_user_logged_in(),
            'userId'   => get_current_user_id(),
        ) );
    }
}
