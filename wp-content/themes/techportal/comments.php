<?php
/**
 * Comments template
 *
 * @package TechPortal
 */

if ( post_password_required() ) return;
?>

<div id="comments" style="max-width:var(--tp-container-narrow);margin:0 auto;padding:var(--tp-space-8) var(--tp-gutter);">
    <?php if ( have_comments() ) : ?>
        <h3 style="margin-bottom:var(--tp-space-6);">
            <?php
            printf(
                esc_html( _nx( '%s Comment', '%s Comments', get_comments_number(), 'comments title', 'techportal' ) ),
                number_format_i18n( get_comments_number() )
            );
            ?>
        </h3>

        <ol style="list-style:none;padding:0;">
            <?php
            wp_list_comments( array(
                'style'      => 'ol',
                'short_ping' => true,
                'avatar_size'=> 48,
            ) );
            ?>
        </ol>

        <?php the_comments_navigation(); ?>
    <?php endif; ?>

    <?php comment_form(); ?>
</div>
