<?php
/**
 * Template Name: Video Archive
 * @package TechPortal
 */
get_header();
?>
<main id="content" style="background:var(--tp-bg-secondary);min-height:80vh;">
    <?php echo do_shortcode('[tp_video_archive]'); ?>
</main>
<?php get_footer(); ?>
