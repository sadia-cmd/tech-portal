<?php
/**
 * Template Name: My Account
 * @package TechPortal
 */
get_header();
?>
<main id="content" style="background:var(--tp-bg-secondary);min-height:80vh;">
    <?php echo do_shortcode('[tp_my_account]'); ?>
</main>
<?php get_footer(); ?>
