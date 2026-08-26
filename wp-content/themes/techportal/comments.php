<?php
/**
 * Comments template — Clean, styled comment form
 *
 * @package TechPortal
 */

if ( post_password_required() ) return;
?>

<div id="comments" class="tp-comments">
    <?php if ( have_comments() ) : ?>
        <h3 class="tp-comments__title">
            <?php
            printf(
                esc_html( _nx( '%s Comment', '%s Comments', get_comments_number(), 'comments title', 'techportal' ) ),
                number_format_i18n( get_comments_number() )
            );
            ?>
        </h3>

        <ol class="tp-comments__list">
            <?php
            wp_list_comments( array(
                'style'       => 'ol',
                'short_ping'  => true,
                'avatar_size' => 48,
                'callback'    => 'tp_comment_template',
            ) );
            ?>
        </ol>

        <?php the_comments_navigation(); ?>
    <?php endif; ?>

    <?php
    comment_form( array(
        'title_reply'        => esc_html__( 'Leave a Comment', 'techportal' ),
        'title_reply_before' => '<h3 id="reply-title" class="tp-comments__reply-title">',
        'title_reply_after'  => '</h3>',
        'comment_notes_before' => '',
        'comment_field'      => '<div class="tp-form-group"><label for="comment" class="tp-form-label">' . esc_html__( 'Comment', 'techportal' ) . ' <span class="tp-required">*</span></label><textarea id="comment" name="comment" class="tp-form-textarea" rows="6" required></textarea></div>',
        'fields'             => array(
            'author' => '<div class="tp-form-row"><div class="tp-form-group tp-form-group--half"><label for="author" class="tp-form-label">' . esc_html__( 'Name', 'techportal' ) . ' <span class="tp-required">*</span></label><input id="author" name="author" class="tp-form-input" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" required></div>',
            'email'  => '<div class="tp-form-group tp-form-group--half"><label for="email" class="tp-form-label">' . esc_html__( 'Email', 'techportal' ) . ' <span class="tp-required">*</span></label><input id="email" name="email" class="tp-form-input" type="email" value="' . esc_attr( $commenter['comment_author_email'] ) . '" required></div></div>',
            'url'    => '<div class="tp-form-group"><label for="url" class="tp-form-label">' . esc_html__( 'Website', 'techportal' ) . '</label><input id="url" name="url" class="tp-form-input" type="url" value="' . esc_attr( $commenter['comment_author_url'] ) . '"></div>',
        ),
        'submit_button'      => '<button type="submit" name="%1$s" id="%2$s" class="tp-btn tp-btn--primary tp-comment-submit">%4$s</button>',
        'submit_field'       => '<div class="tp-form-group tp-form-submit">%1$s %2$s</div>',
    ) );
    ?>
</div>

<?php
/**
 * Custom comment template callback
 */
function tp_comment_template( $comment, $args, $depth ) {
    $tag = ( 'div' === $args['style'] ) ? 'div' : 'li';
    ?>
    <<?php echo $tag; ?> id="comment-<?php comment_ID(); ?>" <?php comment_class( 'tp-comment' ); ?>>
        <div class="tp-comment__header">
            <div class="tp-comment__avatar">
                <?php echo get_avatar( $comment, $args['avatar_size'], '', '', array( 'class' => 'tp-comment__avatar-img' ) ); ?>
            </div>
            <div class="tp-comment__meta">
                <span class="tp-comment__author"><?php comment_author_link(); ?></span>
                <time class="tp-comment__date" datetime="<?php echo esc_attr( get_comment_date( 'c' ) ); ?>">
                    <?php printf( esc_html__( '%1$s at %2$s', 'techportal' ), get_comment_date(), get_comment_time() ); ?>
                </time>
            </div>
        </div>
        <div class="tp-comment__body">
            <?php comment_text(); ?>
        </div>
        <div class="tp-comment__actions">
            <?php
            comment_reply_link( array_merge( $args, array(
                'depth'     => $depth,
                'max_depth' => $args['max_depth'],
                'before'    => '<span class="tp-comment__reply">',
                'after'     => '</span>',
            ) ) );
            ?>
        </div>
    <?php
}
