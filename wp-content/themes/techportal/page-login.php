<?php
/**
 * Template Name: Login
 * @package TechPortal
 */
get_header();
?>
<main id="content" style="background:var(--tp-bg-secondary);min-height:80vh;">
    <?php echo do_shortcode('[tp_login]'); ?>
</main>
<?php get_footer(); ?>
