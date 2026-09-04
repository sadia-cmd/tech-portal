<?php
/**
 * Template Name: Saved Articles
 * @package TechPortal
 */
get_header();
?>
<main id="content" style="background:var(--tp-bg-secondary);min-height:80vh;">
    <?php echo do_shortcode('[tp_bookmarks]'); ?>
</main>
<?php get_footer(); ?>
