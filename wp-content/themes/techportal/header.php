<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#37215F">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'techportal' ); ?></a>

<!-- Utility Header -->
<div class="tp-header__utility">
    <div class="tp-container">
        <div>
            <?php
            $date_format = get_option( 'date_format' );
            echo esc_html( wp_date( $date_format ) );
            ?>
        </div>
        <div style="display:flex;gap:1rem;align-items:center;">
            <a href="<?php echo esc_url( home_url( '/category/pakistan-technology/' ) ); ?>">Pakistan Tech</a>
            <a href="<?php echo esc_url( home_url( '/category/startup-stories/' ) ); ?>">Startups</a>
            <a href="<?php echo esc_url( home_url( '/web-channel/' ) ); ?>">Web Channel</a>
        </div>
    </div>
</div>

<!-- Breaking News Ticker -->
<?php
$breaking = get_theme_mod( 'techportal_breaking_text', '' );
if ( ! empty( $breaking ) ) : ?>
<div class="tp-ticker">
    <div class="tp-ticker__inner tp-container">
        <div class="tp-ticker__label">Breaking</div>
        <div class="tp-ticker__content">
            <div class="tp-ticker__scroll">
                <span class="tp-ticker__item"><?php echo esc_html( $breaking ); ?></span>
                <span class="tp-ticker__item"><?php echo esc_html( $breaking ); ?></span>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Main Header -->
<header class="tp-header" role="banner">
    <div class="tp-header__masthead">
        <div class="tp-container">
            <?php if ( has_custom_logo() ) : ?>
                <div class="tp-header__logo">
                    <?php the_custom_logo(); ?>
                </div>
            <?php else : ?>
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="tp-header__logo">
                    Tech<span>Portal</span>
                </a>
            <?php endif; ?>

            <div class="tp-header__actions">
                <button class="tp-btn tp-btn--outline tp-hide-mobile" aria-label="Search">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                    </svg>
                    Search
                </button>
                <a href="<?php echo esc_url( wp_login_url() ); ?>" class="tp-btn tp-btn--primary tp-hide-mobile">
                    Sign In
                </a>
                <button class="tp-nav-toggle" aria-label="Toggle navigation" aria-expanded="false">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 12h18M3 6h18M3 18h18"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Category Navigation -->
    <nav class="tp-nav" role="navigation" aria-label="<?php esc_attr_e( 'Primary Navigation', 'techportal' ); ?>">
        <div class="tp-container">
            <?php
            wp_nav_menu( array(
                'theme_location' => 'primary',
                'container'      => false,
                'menu_class'     => 'tp-nav__list',
                'fallback_cb'    => 'techportal_fallback_menu',
                'depth'          => 2,
            ) );
            ?>
        </div>
    </nav>
</header>

<main id="content" role="main">
<?php

/**
 * Fallback menu if no menu is assigned
 */
function techportal_fallback_menu() {
    echo '<ul class="tp-nav__list">';
    $categories = get_categories( array(
        'number'  => 8,
        'orderby' => 'count',
        'order'   => 'DESC',
    ) );
    foreach ( $categories as $cat ) {
        printf(
            '<li><a href="%s" class="tp-nav__link">%s</a></li>',
            esc_url( get_category_link( $cat->term_id ) ),
            esc_html( $cat->name )
        );
    }
    echo '</ul>';
}
