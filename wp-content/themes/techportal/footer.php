</main>

<!-- Footer -->
<footer class="tp-footer" role="contentinfo">
    <div class="tp-container">
        <div class="tp-footer__grid">
            <!-- Brand Column -->
            <div>
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="tp-header__logo" style="color:#fff;margin-bottom:1rem;display:inline-block;">
                    Tech<span style="color:var(--tp-accent);">Portal</span>
                </a>
                <p style="font-size:var(--tp-text-sm);line-height:1.6;margin-top:var(--tp-space-3);color:rgba(255,255,255,0.6);">
                    <?php echo esc_html( get_bloginfo( 'description' ) ); ?>
                </p>
                <!-- Newsletter mini-form -->
                <div class="tp-footer__newsletter" style="margin-top:var(--tp-space-4);">
                    <p style="font-size:var(--tp-text-xs);font-weight:var(--tp-weight-semibold);text-transform:uppercase;letter-spacing:0.05em;color:rgba(255,255,255,0.5);margin-bottom:var(--tp-space-2);">Stay Updated</p>
                    <form class="tp-footer__newsletter-form" data-newsletter="footer">
                        <input type="email" placeholder="your@email.com" class="tp-footer__newsletter-input" aria-label="Email for newsletter" required>
                        <button type="submit" class="tp-footer__newsletter-btn tp-btn tp-btn--primary">→</button>
                    </form>
                </div>
            </div>

            <!-- News -->
            <div>
                <h4 class="tp-footer__heading">News</h4>
                <ul class="tp-footer__list">
                    <li><a href="<?php echo esc_url( home_url( '/category/it-news/' ) ); ?>">IT News</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/category/startup-stories/' ) ); ?>">Startups</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/category/ai-cloud/' ) ); ?>">AI & Cloud</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/category/cybersecurity/' ) ); ?>">Cybersecurity</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/category/reviews/' ) ); ?>">Reviews</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/category/pakistan-technology/' ) ); ?>">Pakistan Tech</a></li>
                </ul>
            </div>

            <!-- Media -->
            <div>
                <h4 class="tp-footer__heading">Media</h4>
                <ul class="tp-footer__list">
                    <li><a href="<?php echo esc_url( home_url( '/web-channel/' ) ); ?>">Web Channel</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/episode/' ) ); ?>">Episodes</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/startup/' ) ); ?>">Startups</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/category/founder-interviews/' ) ); ?>">Founder Interviews</a></li>
                </ul>
            </div>

            <!-- Company -->
            <div>
                <h4 class="tp-footer__heading">Company</h4>
                <ul class="tp-footer__list">
                    <li><a href="<?php echo esc_url( home_url( '/about/' ) ); ?>">About</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Contact</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/advertise/' ) ); ?>">Advertise</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/submit-news/' ) ); ?>">Submit News</a></li>
                </ul>
            </div>

            <!-- Legal -->
            <div>
                <h4 class="tp-footer__heading">Legal</h4>
                <ul class="tp-footer__list">
                    <li><a href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>">Privacy</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/terms/' ) ); ?>">Terms</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/cookie-policy/' ) ); ?>">Cookie Policy</a></li>
                </ul>
            </div>
        </div>

        <div class="tp-footer__bottom">
            <span>&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php echo esc_html( get_bloginfo( 'name' ) ); ?>. All rights reserved.</span>
            <div class="tp-footer__social">
                <a href="#" aria-label="YouTube" class="tp-footer__social-link">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.555a3.017 3.017 0 0 0-2.122 2.136C0 8.07 0 12 0 12s0 3.93.478 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.555 9.376.555 9.376.555s7.505 0 9.377-.555a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                </a>
                <a href="#" aria-label="Twitter / X" class="tp-footer__social-link">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                </a>
                <a href="#" aria-label="LinkedIn" class="tp-footer__social-link">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                </a>
                <a href="#" aria-label="Facebook" class="tp-footer__social-link">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                </a>
            </div>
        </div>
    </div>
</footer>

<!-- Footer Banner -->
<?php if ( is_active_sidebar( 'banner-footer' ) ) : ?>
<div style="background:var(--tp-bg-secondary);border-top:1px solid var(--tp-border);padding:var(--tp-space-4) 0;">
    <div class="tp-container" style="text-align:center;">
        <?php dynamic_sidebar( 'banner-footer' ); ?>
    </div>
</div>
<?php endif; ?>

<!-- Cookie Consent Banner -->
<div class="tp-cookie-banner" id="tp-cookie-banner" role="region" aria-label="<?php esc_attr_e( 'Cookie notice', 'techportal' ); ?>" hidden>
    <div class="tp-container tp-cookie-banner__inner">
        <p class="tp-cookie-banner__text">
            <?php
            printf(
                /* translators: %s: link to the Cookie Policy page */
                esc_html__( 'We use cookies to improve your experience and analyze site traffic. Read our %s.', 'techportal' ),
                '<a href="' . esc_url( home_url( '/cookie-policy/' ) ) . '">' . esc_html__( 'Cookie Policy', 'techportal' ) . '</a>'
            );
            ?>
        </p>
        <div class="tp-cookie-banner__actions">
            <button type="button" class="tp-btn tp-btn--ghost tp-cookie-banner__decline"><?php esc_html_e( 'Decline', 'techportal' ); ?></button>
            <button type="button" class="tp-btn tp-btn--primary tp-cookie-banner__accept"><?php esc_html_e( 'Accept', 'techportal' ); ?></button>
        </div>
    </div>
</div>

<?php wp_footer(); ?>
</body>
</html>
