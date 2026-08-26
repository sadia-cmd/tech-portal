<?php
/**
 * Page template
 *
 * @package TechPortal
 */

get_header();
?>

<?php while ( have_posts() ) : the_post(); ?>

<article class="tp-article" <?php post_class(); ?>>
    <header class="tp-article__header">
        <h1 class="tp-article__title"><?php the_title(); ?></h1>
    </header>

    <div class="tp-article__content">
        <?php the_content(); ?>
    </div>
</article>

<?php endwhile; ?>

<?php get_footer(); ?>
