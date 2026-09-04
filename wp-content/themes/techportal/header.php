<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#37215F">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <?php if ( ! has_site_icon() ) :
        // Fallback favicon so the browser tab never shows a blank generic icon
        // before a real site icon is set in Appearance → Customize → Site Identity.
        $techportal_favicon_svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64"><rect width="64" height="64" rx="14" fill="#37215F"/><text x="32" y="44" font-family="Arial, sans-serif" font-size="32" font-weight="800" fill="#ffffff" text-anchor="middle">T</text></svg>';
    ?>
    <link rel="icon" href="data:image/svg+xml;charset=UTF-8,<?php echo rawurlencode( $techportal_favicon_svg ); ?>">
    <?php endif; ?>
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'techportal' ); ?></a>

<!-- Utility Bar -->
<div class="tp-header__utility">
    <div class="tp-container">
        <div>
            <?php
            $date_format = get_option( 'date_format' );
            echo esc_html( wp_date( $date_format ) );
            ?>
        </div>
        <div class="tp-header__utility-links">
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

<!-- Header -->
<header class="tp-header" role="banner">
    <!-- Masthead -->
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
                <button class="tp-btn tp-btn--outline tp-hide-mobile" aria-label="<?php esc_attr_e( 'Search', 'techportal' ); ?>">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                    </svg>
                    <?php esc_html_e( 'Search', 'techportal' ); ?>
                </button>
                <!-- Dark Mode Toggle -->
                <button class="tp-dark-toggle tp-hide-mobile" aria-label="Toggle dark mode">
                    <svg class="tp-moon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                    <svg class="tp-sun" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none;"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                </button>
                <a href="<?php echo esc_url( home_url( '/newsletter/' ) ); ?>" class="tp-btn tp-btn--outline tp-hide-mobile" aria-label="<?php esc_attr_e( 'Newsletter', 'techportal' ); ?>">
                    <?php esc_html_e( 'Newsletter', 'techportal' ); ?>
                </a>
                <div class="tp-hide-mobile">
                    <?php do_action( 'tp_header_actions' ); ?>
                </div>
                <button class="tp-nav-toggle" aria-label="<?php esc_attr_e( 'Toggle navigation', 'techportal' ); ?>" aria-expanded="false" aria-controls="tp-mobile-nav">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M3 12h18M3 6h18M3 18h18"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Primary Navigation -->
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

    <!-- Mobile Navigation Overlay -->
    <div class="tp-nav__mobile-overlay" id="tp-mobile-nav" aria-hidden="true" role="dialog" aria-label="<?php esc_attr_e( 'Mobile Navigation', 'techportal' ); ?>">
        <div class="tp-nav__mobile-inner">
            <button class="tp-nav__mobile-close" aria-label="<?php esc_attr_e( 'Close menu', 'techportal' ); ?>">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M18 6 6 18M6 6l12 12"/>
                </svg>
            </button>

            <div class="tp-nav__mobile-search">
                <button class="tp-btn tp-btn--outline" aria-label="<?php esc_attr_e( 'Search', 'techportal' ); ?>">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                    </svg>
                    <?php esc_html_e( 'Search', 'techportal' ); ?>
                </button>
            </div>

            <nav aria-label="<?php esc_attr_e( 'Mobile Navigation', 'techportal' ); ?>">
                <?php
                wp_nav_menu( array(
                    'theme_location' => 'primary',
                    'container'      => false,
                    'menu_class'     => 'tp-nav__mobile-list',
                    'fallback_cb'    => false,
                    'depth'          => 2,
                ) );
                ?>
            </nav>

            <div class="tp-nav__mobile-actions">
                <a href="<?php echo esc_url( home_url( '/newsletter/' ) ); ?>" class="tp-btn tp-btn--outline tp-btn--full">
                    <?php esc_html_e( 'Newsletter', 'techportal' ); ?>
                </a>
                <a href="<?php echo esc_url( wp_login_url() ); ?>" class="tp-btn tp-btn--primary tp-btn--full">
                    <?php esc_html_e( 'Sign In', 'techportal' ); ?>
                </a>
            </div>
        </div>
    </div>
</header>

<main id="content" role="main">
