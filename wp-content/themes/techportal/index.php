<?php
/**
 * The main template file
 *
 * @package TechPortal
 */

get_header();
?>

<div class="tp-container" style="padding-top:var(--tp-space-8);padding-bottom:var(--tp-space-8);">

    <?php if ( is_home() && ! is_front_page() ) : ?>
        <header class="tp-section-header">
            <h1 class="tp-section-header__title"><?php single_post_title(); ?></h1>
        </header>
    <?php endif; ?>

    <div class="tp-grid">

        <?php if ( have_posts() ) : ?>

            <?php
            $post_index = 0;
            while ( have_posts() ) : the_post();
                $post_index++;

                if ( $post_index === 1 ) :
                    // Hero card — first post
                    ?>
                    <article class="tp-card tp-card--hero" <?php post_class(); ?>>
                        <?php if ( has_post_thumbnail() ) : ?>
                            <div class="tp-card__image">
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_post_thumbnail( 'techportal-hero', array( 'loading' => 'lazy' ) ); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                        <div class="tp-card__body">
                            <div class="tp-card__category">
                                <?php techportal_category_label(); ?>
                            </div>
                            <h2 class="tp-card__title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h2>
                            <p class="tp-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 30 ) ); ?></p>
                            <?php techportal_post_meta(); ?>
                        </div>
                    </article>
                    <?php

                elseif ( $post_index <= 3 ) :
                    // Feature cards — posts 2-3
                    ?>
                    <article class="tp-card tp-card--feature" <?php post_class(); ?>>
                        <?php if ( has_post_thumbnail() ) : ?>
                            <div class="tp-card__image">
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_post_thumbnail( 'techportal-card', array( 'loading' => 'lazy' ) ); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                        <div class="tp-card__body">
                            <div class="tp-card__category">
                                <?php techportal_category_label(); ?>
                            </div>
                            <h3 class="tp-card__title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h3>
                            <p class="tp-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 20 ) ); ?></p>
                            <?php techportal_post_meta(); ?>
                        </div>
                    </article>
                    <?php

                else :
                    // Standard cards
                    ?>
                    <article class="tp-card tp-card--standard" <?php post_class(); ?>>
                        <?php if ( has_post_thumbnail() ) : ?>
                            <div class="tp-card__image">
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_post_thumbnail( 'techportal-card', array( 'loading' => 'lazy' ) ); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                        <div class="tp-card__body">
                            <div class="tp-card__category">
                                <?php techportal_category_label(); ?>
                            </div>
                            <h3 class="tp-card__title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h3>
                            <?php techportal_post_meta(); ?>
                        </div>
                    </article>
                    <?php
                endif;

            endwhile;
            ?>

        <?php else : ?>
            <div class="tp-col-12">
                <p>No posts found. Start writing!</p>
            </div>
        <?php endif; ?>

    </div><!-- .tp-grid -->

    <!-- Pagination -->
    <div style="margin-top:var(--tp-space-8);">
        <?php
        the_posts_pagination( array(
            'mid_size'  => 2,
            'prev_text' => '&laquo; Previous',
            'next_text' => 'Next &raquo;',
            'class'     => 'tp-pagination',
        ) );
        ?>
    </div>

</div><!-- .tp-container -->

<?php
get_footer();
