<?php
/**
 * 404 template
 *
 * @package TechPortal
 */

get_header();
?>

<div class="tp-article" style="text-align:center;padding:var(--tp-space-20) var(--tp-gutter);">
    <h1 style="font-size:var(--tp-text-5xl);color:var(--tp-border);margin-bottom:var(--tp-space-4);">404</h1>
    <h2 style="margin-bottom:var(--tp-space-4);">Page Not Found</h2>
    <p style="color:var(--tp-text-secondary);margin-bottom:var(--tp-space-6);">The page you're looking for doesn't exist or has been moved.</p>
    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="tp-btn tp-btn--primary">Back to Home</a>
</div>

<?php get_footer(); ?>
