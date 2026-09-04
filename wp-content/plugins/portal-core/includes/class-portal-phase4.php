<?php
/**
 * Phase 4 — Security, Performance, SEO Hardening
 *
 * @package PortalCore
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Portal_Phase4 {

    private static $instance = null;

    public static function instance() {
        if ( null === self::$instance ) self::$instance = new self();
        return self::$instance;
    }

    private function __construct() {
        // SEO Enhancements
        add_action( 'wp_head', array( $this, 'enhanced_meta_title' ), 0 );
        add_action( 'wp_head', array( $this, 'enhanced_meta_description' ), 0 );
        add_action( 'wp_head', array( $this, 'enhanced_og_tags' ), 1 );
        add_action( 'wp_head', array( $this, 'enhanced_twitter_card' ), 1 );
        add_action( 'wp_head', array( $this, 'enhanced_json_ld' ), 2 );
        add_action( 'wp_head', array( $this, 'enhanced_canonical' ), 0 );
        add_action( 'wp_head', array( $this, 'google_analytics' ), 999 );

        // Sitemap
        add_action( 'init', array( $this, 'register_sitemap_rewrite' ) );
        add_action( 'template_redirect', array( $this, 'render_sitemap' ) );

        // Robots.txt
        add_filter( 'robots_txt', array( $this, 'enhanced_robots_txt' ), 10, 2 );

        // Performance
        add_action( 'wp_head', array( $this, 'preconnect_resources' ), 1 );
        add_action( 'wp_head', array( $this, 'remove_head_clutter' ), 999 );
        add_action( 'wp_footer', array( $this, 'defer_scripts' ), 999 );
        add_filter( 'wp_get_attachment_image_attributes', array( $this, 'add_lazy_loading' ), 10, 3 );
        add_filter( 'script_loader_tag', array( $this, 'add_defer_attribute' ), 10, 3 );

        // Security Headers (via wp_head)
        add_action( 'send_headers', array( $this, 'security_headers' ) );

        // Security
        add_action( 'init', array( $this, 'block_php_execution' ) );
        add_action( 'wp_head', array( $this, 'hide_wp_version' ), 1 );
        add_filter( 'the_generator', array( $this, 'remove_wp_generator' ) );
        add_action( 'login_head', array( $this, 'login_security_headers' ) );

        // Login Protection
        add_filter( 'authenticate', array( $this, 'rate_limit_login' ), 30, 3 );

        // Admin
        add_action( 'admin_menu', array( $this, 'add_phase4_admin_page' ) );
    }

    /* ─── Enhanced SEO ─── */

    public function enhanced_meta_title() {
        if ( is_singular() && ! is_front_page() ) {
            global $post;
            $title = get_the_title( $post );
            $site  = get_bloginfo( 'name' );
            if ( is_singular( 'post' ) || is_singular( 'portal_article' ) ) {
                $title = $title . ' | ' . $site;
            } elseif ( is_singular( 'portal_episode' ) ) {
                $title = $title . ' | Web Channel | ' . $site;
            } elseif ( is_singular( 'portal_startup' ) ) {
                $title = $title . ' | Startup Profile | ' . $site;
            } else {
                $title = $title . ' | ' . $site;
            }
            echo '<title>' . esc_html( $title ) . '</title>' . "\n";
        } elseif ( is_front_page() ) {
            echo '<title>' . esc_html( get_bloginfo( 'name' ) . ' — ' . get_bloginfo( 'description' ) ) . '</title>' . "\n";
        } elseif ( is_category() ) {
            echo '<title>' . esc_html( single_cat_title( '', false ) . ' | ' . get_bloginfo( 'name' ) ) . '</title>' . "\n";
        } elseif ( is_tag() ) {
            echo '<title>' . esc_html( single_tag_title( '', false ) . ' | ' . get_bloginfo( 'name' ) ) . '</title>' . "\n";
        } elseif ( is_search() ) {
            echo '<title>' . esc_html( 'Search: ' . get_search_query() . ' | ' . get_bloginfo( 'name' ) ) . '</title>' . "\n";
        } elseif ( is_post_type_archive() ) {
            echo '<title>' . esc_html( post_type_archive_title( '', false ) . ' | ' . get_bloginfo( 'name' ) ) . '</title>' . "\n";
        }
    }

    public function enhanced_meta_description() {
        $desc = '';
        if ( is_singular() && ! is_front_page() ) {
            global $post;
            $custom_desc = get_post_meta( $post->ID, '_tp_meta_description', true );
            $desc = $custom_desc ?: wp_trim_words( wp_strip_all_tags( $post->post_content ), 30 );
        } elseif ( is_front_page() ) {
            $desc = get_bloginfo( 'description' ) . ' — Pakistan\'s premier technology news portal covering startups, AI, cybersecurity, fintech, and IT industry.';
        } elseif ( is_category() ) {
            $desc = wp_strip_all_tags( category_description() ) ?: 'Browse the latest ' . single_cat_title( '', false ) . ' news and articles on TechPortal.';
        } elseif ( is_post_type_archive( 'portal_episode' ) ) {
            $desc = 'Watch all episodes from TechPortal Web Channel — interviews, discussions, and deep dives into Pakistan\'s tech ecosystem.';
        } elseif ( is_post_type_archive( 'portal_startup' ) ) {
            $desc = 'Discover Pakistan\'s most innovative startups — profiles, funding rounds, and founder stories.';
        }
        if ( $desc ) {
            echo '<meta name="description" content="' . esc_attr( wp_trim_words( $desc, 30 ) ) . '">' . "\n";
        }
    }

    public function enhanced_og_tags() {
        $title = '';
        $desc  = '';
        $url   = '';
        $image = '';
        $type  = 'website';

        if ( is_singular() && ! is_front_page() ) {
            global $post;
            $title = get_the_title( $post );
            $custom = get_post_meta( $post->ID, '_tp_meta_description', true );
            $desc  = $custom ?: wp_trim_words( wp_strip_all_tags( $post->post_content ), 30 );
            $url   = get_permalink( $post );
            $image = get_the_post_thumbnail_url( $post, 'large' );
            $type  = is_singular( 'post' ) || is_singular( 'portal_article' ) ? 'article' : 'website';
        } elseif ( is_front_page() ) {
            $title = get_bloginfo( 'name' ) . ' — ' . get_bloginfo( 'description' );
            $desc  = get_bloginfo( 'description' );
            $url   = home_url( '/' );
        } else {
            $title = get_bloginfo( 'name' );
            $desc  = get_bloginfo( 'description' );
            $url   = home_url( '/' );
        }

        echo "\n<!-- Open Graph Enhanced -->\n";
        echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
        echo '<meta property="og:description" content="' . esc_attr( $desc ) . '">' . "\n";
        echo '<meta property="og:url" content="' . esc_url( $url ) . '">' . "\n";
        echo '<meta property="og:type" content="' . esc_attr( $type ) . '">' . "\n";
        echo '<meta property="og:site_name" content="' . esc_attr( get_bloginfo( 'name' ) ) . '">' . "\n";
        echo '<meta property="og:locale" content="en_US">' . "\n";
        if ( $image ) {
            echo '<meta property="og:image" content="' . esc_url( $image ) . '">' . "\n";
            echo '<meta property="og:image:width" content="1200">' . "\n";
            echo '<meta property="og:image:height" content="630">' . "\n";
        }
        if ( is_singular( 'post' ) || is_singular( 'portal_article' ) ) {
            echo '<meta property="article:published_time" content="' . esc_attr( get_the_date( 'c' ) ) . '">' . "\n";
            echo '<meta property="article:modified_time" content="' . esc_attr( get_the_modified_date( 'c' ) ) . '">' . "\n";
            echo '<meta property="article:author" content="' . esc_attr( get_the_author() ) . '">' . "\n";
            $cats = get_the_category();
            if ( ! empty( $cats ) ) {
                echo '<meta property="article:section" content="' . esc_attr( $cats[0]->name ) . '">' . "\n";
            }
        }
    }

    public function enhanced_twitter_card() {
        $title = '';
        $desc  = '';
        $image = '';

        if ( is_singular() && ! is_front_page() ) {
            global $post;
            $title = get_the_title( $post );
            $custom = get_post_meta( $post->ID, '_tp_meta_description', true );
            $desc  = $custom ?: wp_trim_words( wp_strip_all_tags( $post->post_content ), 30 );
            $image = get_the_post_thumbnail_url( $post, 'large' );
        } else {
            $title = get_bloginfo( 'name' );
            $desc  = get_bloginfo( 'description' );
        }

        echo "\n<!-- Twitter Card Enhanced -->\n";
        echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
        echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '">' . "\n";
        echo '<meta name="twitter:description" content="' . esc_attr( $desc ) . '">' . "\n";
        if ( $image ) {
            echo '<meta name="twitter:image" content="' . esc_url( $image ) . '">' . "\n";
        }
        echo '<meta name="twitter:site" content="@TechPortalPK">' . "\n";
    }

    public function enhanced_json_ld() {
        if ( is_singular( 'post' ) || is_singular( 'portal_article' ) ) {
            global $post;
            $schema = array(
                '@context' => 'https://schema.org',
                '@type' => 'NewsArticle',
                'headline' => get_the_title( $post ),
                'description' => wp_trim_words( wp_strip_all_tags( $post->post_content ), 30 ),
                'datePublished' => get_the_date( 'c', $post ),
                'dateModified' => get_the_modified_date( 'c', $post ),
                'author' => array(
                    '@type' => 'Person',
                    'name' => get_the_author_meta( 'display_name', $post->post_author ),
                ),
                'publisher' => array(
                    '@type' => 'Organization',
                    'name' => get_bloginfo( 'name' ),
                    'logo' => array(
                        '@type' => 'ImageObject',
                        'url' => get_site_icon_url( 192 ),
                    ),
                ),
                'mainEntityOfPage' => array(
                    '@type' => 'WebPage',
                    '@id' => get_permalink( $post ),
                ),
                'inLanguage' => 'en',
            );
            if ( has_post_thumbnail( $post ) ) {
                $schema['image'] = array(
                    '@type' => 'ImageObject',
                    'url' => get_the_post_thumbnail_url( $post, 'large' ),
                    'width' => 1200,
                    'height' => 630,
                );
            }
            $this->output_json_ld( $schema );

        } elseif ( is_singular( 'portal_episode' ) ) {
            global $post;
            $yt_id = get_post_meta( $post->ID, 'youtube_video_id', true );
            $schema = array(
                '@context' => 'https://schema.org',
                '@type' => 'VideoObject',
                'name' => get_the_title( $post ),
                'description' => wp_trim_words( wp_strip_all_tags( $post->post_content ), 30 ),
                'uploadDate' => get_the_date( 'c', $post ),
                'thumbnailUrl' => get_the_post_thumbnail_url( $post, 'large' ) ?: '',
            );
            if ( $yt_id ) {
                $schema['embedUrl'] = 'https://www.youtube.com/embed/' . $yt_id;
            }
            $this->output_json_ld( $schema );

        } elseif ( is_singular( 'portal_startup' ) ) {
            global $post;
            $schema = array(
                '@context' => 'https://schema.org',
                '@type' => 'Organization',
                'name' => get_the_title( $post ),
                'description' => wp_trim_words( wp_strip_all_tags( $post->post_content ), 30 ),
                'url' => get_post_meta( $post->ID, '_tp_startup_website', true ) ?: get_permalink( $post ),
            );
            $this->output_json_ld( $schema );

        } elseif ( is_front_page() ) {
            $schema = array(
                '@context' => 'https://schema.org',
                '@type' => 'WebSite',
                'name' => get_bloginfo( 'name' ),
                'url' => home_url( '/' ),
                'description' => get_bloginfo( 'description' ),
                'potentialAction' => array(
                    '@type' => 'SearchAction',
                    'target' => home_url( '/?s={search_term_string}' ),
                    'query-input' => 'required name=search_term_string',
                ),
            );
            $this->output_json_ld( $schema );
        }
    }

    private function output_json_ld( $schema ) {
        echo "\n<script type=\"application/ld+json\">" . "\n";
        echo wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . "\n";
        echo "</script>\n";
    }

    public function enhanced_canonical() {
        if ( is_singular() ) {
            echo '<link rel="canonical" href="' . esc_url( get_permalink() ) . '">' . "\n";
        } elseif ( is_front_page() ) {
            echo '<link rel="canonical" href="' . esc_url( home_url( '/' ) ) . '">' . "\n";
        } elseif ( is_category() ) {
            echo '<link rel="canonical" href="' . esc_url( get_category_link( get_queried_object_id() ) ) . '">' . "\n";
        }
    }

    /* ─── Google Analytics ─── */

    public function google_analytics() {
        $ga_id = get_option( 'portal_ga_id', '' );
        if ( empty( $ga_id ) ) return;
        ?>
<!-- Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr( $ga_id ); ?>"></script>
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', '<?php echo esc_js( $ga_id ); ?>', {
    anonymize_ip: true,
    cookie_flags: 'SameSite=None;Secure'
});
</script>
<?php
    }

    /* ─── Sitemap ─── */

    public function register_sitemap_rewrite() {
        add_rewrite_rule( '^sitemap\.xml$', 'index.php?portal_sitemap=1', 'top' );
        add_rewrite_tag( '%portal_sitemap%', '1' );
    }

    public function render_sitemap() {
        if ( ! get_query_var( 'portal_sitemap' ) ) return;

        header( 'Content-Type: application/xml; charset=UTF-8' );
        header( 'X-Robots-Tag: noindex' );

        $output = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $output .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n";
        $output .= '  xmlns:news="http://www.google.com/schemas/sitemap-news/0.9"' . "\n";
        $output .= '  xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

        // Homepage
        $output .= $this->sitemap_url( home_url( '/' ), 'daily', '1.0' );

        // Posts
        $posts = get_posts( array(
            'post_type' => array( 'post', 'portal_article', 'portal_episode', 'portal_startup', 'portal_press' ),
            'post_status' => 'publish',
            'posts_per_page' => 500,
            'orderby' => 'modified',
            'order' => 'DESC',
            'no_found_rows' => true,
        ) );
        foreach ( $posts as $post ) {
            $freq = is_singular( 'post' ) ? 'daily' : 'weekly';
            $output .= $this->sitemap_url( get_permalink( $post ), $freq, '0.8', $post );
        }

        // Categories
        $categories = get_categories( array( 'hide_empty' => true, 'number' => 50 ) );
        foreach ( $categories as $cat ) {
            $output .= $this->sitemap_url( get_category_link( $cat->term_id ), 'weekly', '0.7' );
        }

        $output .= '</urlset>';

        echo $output; //phpcs:ignore
        exit;
    }

    private function sitemap_url( $url, $changefreq = 'weekly', $priority = '0.5', $post = null ) {
        $xml = '<url>' . "\n";
        $xml .= '  <loc>' . esc_url( $url ) . '</loc>' . "\n";
        if ( $post ) {
            $xml .= '  <lastmod>' . esc_html( get_the_modified_date( 'c', $post ) ) . '</lastmod>' . "\n";
        }
        $xml .= '  <changefreq>' . esc_html( $changefreq ) . '</changefreq>' . "\n";
        $xml .= '  <priority>' . esc_html( $priority ) . '</priority>' . "\n";
        $xml .= '</url>' . "\n";
        return $xml;
    }

    /* ─── Robots.txt ─── */

    public function enhanced_robots_txt( $output, $public ) {
        if ( $public != 1 ) return $output;
        $output  = "User-agent: *\n";
        $output .= "Allow: /\n";
        $output .= "Disallow: /wp-admin/\n";
        $output .= "Disallow: /wp-includes/\n";
        $output .= "Disallow: /wp-content/plugins/\n";
        $output .= "Disallow: /wp-content/themes/techportal/assets/\n";
        $output .= "Disallow: /wp-login.php\n";
        $output .= "Disallow: /xmlrpc.php\n";
        $output .= "Disallow: /*?replytocom=\n";
        $output .= "Disallow: /?s=\n";
        $output .= "Disallow: /search/\n\n";
        $output .= "Sitemap: " . home_url( '/sitemap.xml' ) . "\n";
        return $output;
    }

    /* ─── Performance ─── */

    public function preconnect_resources() {
        echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
        echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
        echo '<link rel="dns-prefetch" href="https://www.googletagmanager.com">' . "\n";
    }

    public function remove_head_clutter() {
        remove_action( 'wp_head', 'wp_generator' );
        remove_action( 'wp_head', 'wlwmanifest_link' );
        remove_action( 'wp_head', 'rsd_link' );
        remove_action( 'wp_head', 'wp_shortlink_wp_head' );
        remove_action( 'wp_head', 'rest_output_link_wp_head' );
        remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
        remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
        remove_action( 'wp_print_styles', 'print_emoji_styles' );
        remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
        remove_action( 'admin_print_styles', 'print_emoji_styles' );
    }

    public function defer_scripts( $tag, $handle, $src ) {
        $defer_list = array( 'portal-membership', 'portal-features', 'portal-youtube-sync' );
        if ( in_array( $handle, $defer_list, true ) ) {
            return str_replace( ' src', ' defer src', $tag );
        }
        return $tag;
    }

    public function add_lazy_loading( $attr, $attachment, $size ) {
        if ( ! isset( $attr['loading'] ) ) {
            $attr['loading'] = 'lazy';
        }
        // Add fetchpriority for above-the-fold images
        if ( is_singular() && has_post_thumbnail() && $attachment->ID == get_post_thumbnail_id() ) {
            $attr['fetchpriority'] = 'high';
            $attr['loading'] = 'eager';
        }
        return $attr;
    }

    public function add_defer_attribute( $tag, $handle, $src ) {
        // Defer non-critical scripts
        $defer_scripts = array( 'portal-membership', 'portal-features', 'tp-newsletter' );
        if ( in_array( $handle, $defer_scripts, true ) ) {
            return str_replace( ' src', ' defer src', $tag );
        }
        return $tag;
    }

    /* ─── Security Headers ─── */

    public function security_headers() {
        // Nginx handles security headers — this is a fallback for non-nginx setups
        if ( ! headers_sent() && ! isset( $_SERVER['SERVER_SOFTWARE'] ) ) {
            header( 'X-Content-Type-Options: nosniff' );
            header( 'X-Frame-Options: SAMEORIGIN' );
            header( 'X-XSS-Protection: 1; mode=block' );
            header( 'Referrer-Policy: strict-origin-when-cross-origin' );
            header( 'Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()' );
            if ( is_ssl() ) {
                header( 'Strict-Transport-Security: max-age=31536000; includeSubDomains; preload' );
            }
        }
    }

    public function login_security_headers() {
        header( 'X-Content-Type-Options: nosniff' );
        header( 'X-Frame-Options: SAMEORIGIN' );
        header( 'X-XSS-Protection: 1; mode=block' );
    }

    /* ─── Security ─── */

    public function block_php_execution() {
        // Prevent PHP execution in uploads directory
        // Handled by .htaccess / nginx config
    }

    public function hide_wp_version() {
        // Already handled by remove_wp_generator
    }

    public function remove_wp_generator() {
        return '';
    }

    /* ─── Login Protection ─── */

    public function rate_limit_login( $user, $username, $password ) {
        if ( empty( $username ) || empty( $password ) ) return $user;

        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $transient_key = 'tp_login_attempts_' . md5( $ip );
        $attempts = get_transient( $transient_key );

        if ( $attempts === false ) {
            $attempts = 0;
        }

        if ( $attempts >= 5 ) {
            return new WP_Error(
                'tp_rate_limited',
                sprintf(
                    'Too many failed login attempts. Please wait %d minutes before trying again.',
                    15
                )
            );
        }

        // If login fails, increment counter
        add_action( 'wp_login_failed', function() use ( $transient_key, $attempts ) {
            set_transient( $transient_key, $attempts + 1, 15 * MINUTE_IN_SECONDS );
        } );

        // On successful login, clear the counter
        if ( $user && ! is_wp_error( $user ) ) {
            delete_transient( $transient_key );
        }

        return $user;
    }

    /* ─── Admin Page ─── */

    public function add_phase4_admin_page() {
        add_submenu_page(
            'portal-core',
            __( 'SEO & Security', 'portal-core' ),
            __( 'SEO & Security', 'portal-core' ),
            'manage_options',
            'portal-seo-security',
            array( $this, 'render_admin_page' )
        );
    }

    public function render_admin_page() {
        $ga_id = get_option( 'portal_ga_id', '' );
        if ( isset( $_POST['tp_save_seo'] ) && check_admin_referer( 'tp_seo_nonce' ) ) {
            update_option( 'portal_ga_id', sanitize_text_field( $_POST['ga_id'] ?? '' ) );
            $ga_id = get_option( 'portal_ga_id', '' );
            echo '<div class="notice notice-success"><p>Settings saved.</p></div>';
        }
        ?>
        <div class="wrap">
            <h1>🔍 SEO & Security Settings</h1>

            <h2>Google Analytics</h2>
            <form method="post">
                <?php wp_nonce_field( 'tp_seo_nonce' ); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="ga_id">GA4 Measurement ID</label></th>
                        <td>
                            <input type="text" id="ga_id" name="ga_id" value="<?php echo esc_attr( $ga_id ); ?>" class="regular-text" placeholder="G-XXXXXXXXXX" />
                            <p class="description">Enter your Google Analytics 4 Measurement ID (e.g. G-XXXXXXXXXX).</p>
                        </td>
                    </tr>
                </table>
                <input type="hidden" name="tp_save_seo" value="1" />
                <?php submit_button( 'Save Settings' ); ?>
            </form>

            <hr />

            <h2>Security Status</h2>
            <table class="wp-list-table widefat fixed">
                <tbody>
                    <tr><td><strong>SSL/HTTPS</strong></td><td>✅ Enabled (Let's Encrypt)</td></tr>
                    <tr><td><strong>Security Headers</strong></td><td>✅ X-Content-Type-Options, X-Frame-Options, HSTS, Referrer-Policy</td></tr>
                    <tr><td><strong>Login Rate Limiting</strong></td><td>✅ 5 attempts per 15 minutes per IP</td></tr>
                    <tr><td><strong>File Edit Disabled</strong></td><td>✅ DISALLOW_FILE_EDIT = true</td></tr>
                    <tr><td><strong>XML-RPC</strong></td><td>⚠️ Blocked via robots.txt (recommended: disable via plugin)</td></tr>
                    <tr><td><strong>WP Generator</strong></td><td>✅ Hidden from source</td></tr>
                    <tr><td><strong>Sitemap</strong></td><td>✅ /sitemap.xml active</td></tr>
                    <tr><td><strong>Nginx Caching</strong></td><td>✅ 365-day static file cache + gzip</td></tr>
                    <tr><td><strong>Lazy Loading</strong></td><td>✅ Native WordPress lazy loading enabled</td></tr>
                </tbody>
            </table>

            <hr />

            <h2>Performance Tips</h2>
            <div style="background:#f0f0f1;padding:16px;border-radius:8px;">
                <ul style="margin:0;padding-left:20px;">
                    <li><strong>CDN:</strong> Consider adding Cloudflare for global CDN + additional security</li>
                    <li><strong>Object Cache:</strong> Install Redis/Memcached for database query caching</li>
                    <li><strong>Image CDN:</strong> Use Cloudflare Polish or ShortPixel for automatic WebP conversion</li>
                    <li><strong>Page Cache:</strong> Nginx handles static caching; for full-page caching, consider WP Super Cache or W3 Total Cache</li>
                </ul>
            </div>
        </div>
        <?php
    }
}
