</main>

<!-- Footer -->
<footer class="tp-footer" role="contentinfo">
    <div class="tp-container">
        <div class="tp-footer__grid">
            <!-- About Column -->
            <div>
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="tp-header__logo" style="color:#fff;margin-bottom:1rem;display:inline-block;">
                    Tech<span style="color:var(--tp-accent);">Portal</span>
                </a>
                <p style="font-size:var(--tp-text-sm);line-height:1.6;margin-top:var(--tp-space-3);color:rgba(255,255,255,0.6);">
                    <?php echo esc_html( get_bloginfo( 'description' ) ); ?>
                </p>
            </div>

            <!-- Categories -->
            <div>
                <h4 class="tp-footer__heading">Categories</h4>
                <ul class="tp-footer__list">
                    <li><a href="<?php echo esc_url( home_url( '/category/it-news/' ) ); ?>">IT News</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/category/pakistan-technology/' ) ); ?>">Pakistan Technology</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/category/startup-stories/' ) ); ?>">Startup Stories</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/category/cybersecurity/' ) ); ?>">Cybersecurity</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/category/ai-cloud/' ) ); ?>">AI & Cloud</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/category/reviews/' ) ); ?>">Reviews</a></li>
                </ul>
            </div>

            <!-- Company -->
            <div>
                <h4 class="tp-footer__heading">Company</h4>
                <ul class="tp-footer__list">
                    <li><a href="<?php echo esc_url( home_url( '/about/' ) ); ?>">About Us</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Contact</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/advertise/' ) ); ?>">Advertise</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>">Privacy Policy</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/terms/' ) ); ?>">Terms of Use</a></li>
                </ul>
            </div>

            <!-- Connect -->
            <div>
                <h4 class="tp-footer__heading">Connect</h4>
                <ul class="tp-footer__list">
                    <li><a href="#">YouTube</a></li>
                    <li><a href="#">Twitter / X</a></li>
                    <li><a href="#">LinkedIn</a></li>
                    <li><a href="#">Facebook</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/newsletter/' ) ); ?>">Newsletter</a></li>
                </ul>
            </div>
        </div>

        <div class="tp-footer__bottom">
            <span>&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php echo esc_html( get_bloginfo( 'name' ) ); ?>. All rights reserved.</span>
            <span>Built for Pakistan's Tech Ecosystem</span>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
