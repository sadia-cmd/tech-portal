<?php
/**
 * Phase 7 — Production Features
 * Dark Mode, Reading Progress, Floating Share, TOC, Author Bio, Breaking Banner, Pull Quotes, Print, Back-to-Top, Load More
 *
 * @package PortalCore
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Portal_Phase7 {

    private static $instance = null;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_filter( 'body_class', array( $this, 'body_classes' ) );

        // Load More AJAX handler
        add_action( 'wp_ajax_tp_load_more', array( $this, 'ajax_load_more' ) );
        add_action( 'wp_ajax_nopriv_tp_load_more', array( $this, 'ajax_load_more' ) );

        // Author social meta boxes
        add_action( 'add_meta_boxes', array( $this, 'add_author_meta_boxes' ) );
        add_action( 'save_post', array( $this, 'save_author_meta' ) );
    }

    /* ─── Enqueue Assets ─── */
    public function enqueue_assets() {
        wp_enqueue_style(
            'portal-phase7',
            PORTAL_CORE_URL . 'assets/css/phase7.css',
            array(),
            PORTAL_CORE_VERSION
        );
        wp_enqueue_script(
            'portal-phase7',
            PORTAL_CORE_URL . 'assets/js/phase7.js',
            array(),
            PORTAL_CORE_VERSION,
            true
        );
    }

    /* ─── Body Classes ─── */
    public function body_classes( $classes ) {
        if ( is_singular( 'post' ) ) {
            $classes[] = 'tp-singular-post';
        }
        return $classes;
    }

    /* ─── Load More AJAX ─── */
    public function ajax_load_more() {
        $page = isset( $_GET['page'] ) ? intval( $_GET['page'] ) : 2;
        $type = isset( $_GET['type'] ) ? sanitize_text_field( $_GET['type'] ) : 'posts';

        if ( $type === 'posts' ) {
            $args = array(
                'post_type'      => 'post',
                'post_status'    => 'publish',
                'posts_per_page' => 12,
                'paged'          => $page,
                'orderby'        => 'date',
                'order'          => 'DESC',
            );
        } else {
            wp_send_json_error();
            return;
        }

        $query = new WP_Query( $args );
        if ( ! $query->have_posts() ) {
            wp_send_json_success( '' );
            return;
        }

        ob_start();
        while ( $query->have_posts() ) : $query->the_post();
            setup_postdata( get_the_ID() );
            ?>
            <article class="tp-card tp-card--horizontal">
                <?php if ( has_post_thumbnail() ) : ?>
                    <div class="tp-card__image tp-card__image--sm">
                        <a href="<?php the_permalink(); ?>">
                            <?php the_post_thumbnail( 'techportal-card', array( 'loading' => 'lazy' ) ); ?>
                        </a>
                    </div>
                <?php endif; ?>
                <div class="tp-card__body">
                    <span class="tp-card__category"><?php echo esc_html( get_the_category()[0]->name ?? 'News' ); ?></span>
                    <h3 class="tp-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                    <div class="tp-card__meta">
                        <span>TechPortal Editorial</span>
                        <span>•</span>
                        <span><?php echo esc_html( get_the_date( 'M j, Y' ) ); ?></span>
                        <span>•</span>
                        <span><?php echo esc_html( tp_estimate_read_time() ); ?> min read</span>
                    </div>
                </div>
            </article>
            <?php
        endwhile;
        wp_reset_postdata();
        $html = ob_get_clean();
        wp_send_json_success( $html );
    }

    /* ─── Author Meta Boxes ─── */
    public function add_author_meta_boxes() {
        add_meta_box(
            'tp_author_info',
            'Author Info',
            array( $this, 'author_meta_callback' ),
            array( 'post', 'page' ),
            'side',
            'high'
        );
    }

    public function author_meta_callback( $post ) {
        wp_nonce_field( 'tp_author_meta', 'tp_author_nonce' );
        $bio   = get_post_meta( $post->ID, '_tp_author_bio', true );
        $title = get_post_meta( $post->ID, '_tp_author_title', true );
        $twitter = get_post_meta( $post->ID, '_tp_author_twitter', true );
        $linkedin = get_post_meta( $post->ID, '_tp_author_linkedin', true );
        ?>
        <p>
            <label><strong>Author Display Name</strong></label><br/>
            <input type="text" name="tp_author_name" value="<?php echo esc_attr( get_the_author() ); ?>" style="width:100%;" />
        </p>
        <p>
            <label><strong>Author Bio</strong></label><br/>
            <textarea name="tp_author_bio" rows="3" style="width:100%;"><?php echo esc_textarea( $bio ); ?></textarea>
        </p>
        <p>
            <label><strong>Author Title / Role</strong></label><br/>
            <input type="text" name="tp_author_title" value="<?php echo esc_attr( $title ); ?>" style="width:100%;" placeholder="e.g. Tech Editor" />
        </p>
        <p>
            <label><strong>Twitter / X</strong></label><br/>
            <input type="text" name="tp_author_twitter" value="<?php echo esc_attr( $twitter ); ?>" style="width:100%;" placeholder="@username" />
        </p>
        <p>
            <label><strong>LinkedIn</strong></label><br/>
            <input type="text" name="tp_author_linkedin" value="<?php echo esc_attr( $linkedin ); ?>" style="width:100%;" placeholder="https://linkedin.com/in/..." />
        </p>
        <?php
    }

    public function save_author_meta( $post_id ) {
        if ( ! isset( $_POST['tp_author_nonce'] ) || ! wp_verify_nonce( $_POST['tp_author_nonce'], 'tp_author_meta' ) ) return;
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;

        $fields = array( 'tp_author_bio', 'tp_author_title', 'tp_author_twitter', 'tp_author_linkedin' );
        foreach ( $fields as $field ) {
            if ( isset( $_POST[ $field ] ) ) {
                update_post_meta( $post_id, '_' . $field, sanitize_text_field( $_POST[ $field ] ) );
            }
        }
    }

}
