<?php
/**
 * Single post template — Enhanced Article Reading UX (Phase 7)
 *
 * @package TechPortal
 */

get_header();
?>

<!-- Reading Progress Bar -->
<div class="tp-progress-bar"></div>

<?php while ( have_posts() ) : the_post(); ?>

<?php
// Related posts query
$related_args = array(
    'post_type'      => get_post_type(),
    'post_status'    => 'publish',
    'posts_per_page' => 4,
    'post__not_in'   => array( get_the_ID() ),
    'orderby'        => 'rand',
);
if ( has_category() ) {
    $related_args['category_name'] = get_the_category()[0]->slug ?? '';
}
$related_query = new WP_Query( $related_args );

// Author info
$author_bio   = get_post_meta( get_the_ID(), '_tp_author_bio', true );
$author_title = get_post_meta( get_the_ID(), '_tp_author_title', true );
$author_twitter  = get_post_meta( get_the_ID(), '_tp_author_twitter', true );
$author_linkedin = get_post_meta( get_the_ID(), '_tp_author_linkedin', true );
$author_name = get_the_author();
$author_initial = strtoupper( mb_substr( $author_name, 0, 1 ) );

// Share URLs
$share_url   = urlencode( get_permalink() );
$share_title = urlencode( get_the_title() );
?>

<!-- Floating Social Share (appears on scroll) -->
<div class="tp-share-floating">
    <a href="https://twitter.com/intent/tweet?url=<?php echo $share_url; ?>&text=<?php echo $share_title; ?>" target="_blank" rel="noopener" class="tp-share-btn tp-share-btn--twitter" aria-label="Share on Twitter">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
    </a>
    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $share_url; ?>" target="_blank" rel="noopener" class="tp-share-btn tp-share-btn--facebook" aria-label="Share on Facebook">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
    </a>
    <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo $share_url; ?>" target="_blank" rel="noopener" class="tp-share-btn tp-share-btn--linkedin" aria-label="Share on LinkedIn">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
    </a>
    <a href="https://api.whatsapp.com/send?text=<?php echo $share_title; ?>%20<?php echo $share_url; ?>" target="_blank" rel="noopener" class="tp-share-btn tp-share-btn--whatsapp" aria-label="Share on WhatsApp">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
    </a>
    <button class="tp-share-btn tp-share-btn--copy" onclick="navigator.clipboard.writeText(window.location.href);this.innerHTML='✓';setTimeout(()=>this.innerHTML='<svg width=16 height=16 viewBox=\'0 0 24 24\' fill=currentColor><path d=\'M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z\'/></svg>',1000)" aria-label="Copy link">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>
    </button>
</div>

<article class="tp-article" <?php post_class(); ?>>
    <header class="tp-article__header">
        <div class="tp-article__category">
            <?php techportal_category_label(); ?>
        </div>
        <h1 class="tp-article__title"><?php the_title(); ?></h1>
        <?php techportal_article_meta(); ?>
    </header>

    <?php if ( has_post_thumbnail() ) : ?>
        <figure style="margin-bottom:var(--tp-space-8);">
            <?php the_post_thumbnail( 'large', array(
                'loading' => false,
                'style'   => 'border-radius:var(--tp-radius-lg);width:100%;',
            ) ); ?>
            <?php if ( get_the_post_thumbnail_caption() ) : ?>
                <figcaption><?php echo esc_html( get_the_post_thumbnail_caption() ); ?></figcaption>
            <?php endif; ?>
        </figure>
    <?php endif; ?>

    <!-- Table of Contents (auto-generated from h2/h3) -->
    <div class="tp-toc" style="display:none;"></div>

    <div class="tp-article__content">
        <?php the_content(); ?>
    </div>

    <?php
    // Source attribution for news radar posts
    $source_url  = get_post_meta( get_the_ID(), '_news_radar_source_url', true );
    $source_name = get_post_meta( get_the_ID(), '_news_radar_source_name', true );
    if ( $source_url ) : ?>
        <div style="margin:var(--tp-space-6) 0;padding:var(--tp-space-4);background:var(--tp-bg-secondary);border:1px solid var(--tp-border);border-radius:var(--tp-radius-md);font-size:var(--tp-text-sm);">
            <strong>Originally published by:</strong>
            <a href="<?php echo esc_url( $source_url ); ?>" target="_blank" rel="noopener noreferrer" style="color:var(--tp-accent);text-decoration:none;">
                <?php echo esc_html( $source_name ?: wp_parse_url( $source_url, PHP_URL_HOST ) ); ?> →
            </a>
        </div>
    <?php endif;
    ?>

    <?php
    wp_link_pages( array(
        'before' => '<div class="page-links">Pages:',
        'after'  => '</div>',
    ) );
    ?>

    <!-- Tags -->
    <?php if ( has_tag() ) : ?>
        <div style="margin-top:var(--tp-space-8);padding-top:var(--tp-space-6);border-top:1px solid var(--tp-border);">
            <strong style="font-size:var(--tp-text-sm);">Tags:</strong>
            <div style="display:flex;gap:var(--tp-space-2);flex-wrap:wrap;margin-top:var(--tp-space-2);">
                <?php foreach ( get_the_tags() as $tag ) : ?>
                    <a href="<?php echo esc_url( get_tag_link( $tag ) ); ?>"
                       style="display:inline-block;padding:4px 12px;background:var(--tp-bg-secondary);border:1px solid var(--tp-border);border-radius:var(--tp-radius-full);font-size:var(--tp-text-xs);color:var(--tp-text-secondary);text-decoration:none;transition:all 0.15s;">
                        <?php echo esc_html( $tag->name ); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Inline Share + Bookmark -->
    <div style="margin-top:var(--tp-space-6);padding-top:var(--tp-space-4);border-top:1px solid var(--tp-border);display:flex;align-items:center;gap:var(--tp-space-3);">
        <span style="font-size:var(--tp-text-sm);font-weight:600;">Share:</span>
        <a href="https://twitter.com/intent/tweet?url=<?php echo $share_url; ?>&text=<?php echo $share_title; ?>" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:var(--tp-radius-md);background:var(--tp-bg-secondary);color:var(--tp-text-secondary);text-decoration:none;" aria-label="Twitter">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
        </a>
        <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo $share_url; ?>" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:var(--tp-radius-md);background:var(--tp-bg-secondary);color:var(--tp-text-secondary);text-decoration:none;" aria-label="LinkedIn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
        </a>
        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $share_url; ?>" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:var(--tp-radius-md);background:var(--tp-bg-secondary);color:var(--tp-text-secondary);text-decoration:none;" aria-label="Facebook">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
        </a>
        <div style="margin-left:auto;">
            <?php
            if ( class_exists( 'Portal_Membership' ) ) {
                Portal_Membership::instance()->bookmark_button( get_the_ID() );
            }
            ?>
        </div>
    </div>

    <!-- Author Bio Box -->
    <div class="tp-author-box">
        <div class="tp-author-box__avatar"><?php echo esc_html( $author_initial ); ?></div>
        <div>
            <div class="tp-author-box__name"><?php echo esc_html( $author_name ); ?></div>
            <?php if ( $author_title ) : ?>
                <div style="font-size:var(--tp-text-xs);color:var(--tp-accent);margin-bottom:8px;font-weight:600;"><?php echo esc_html( $author_title ); ?></div>
            <?php endif; ?>
            <?php if ( $author_bio ) : ?>
                <div class="tp-author-box__bio"><?php echo esc_html( $author_bio ); ?></div>
            <?php else : ?>
                <div class="tp-author-box__bio">TechPortal Editorial — Covering Pakistan's technology ecosystem, startups, AI, cybersecurity, and innovation.</div>
            <?php endif; ?>
            <div class="tp-author-box__social">
                <?php if ( $author_twitter ) : ?>
                    <a href="https://twitter.com/<?php echo esc_attr( ltrim( $author_twitter, '@' ) ); ?>" target="_blank" rel="noopener">Twitter/X →</a>
                <?php endif; ?>
                <?php if ( $author_linkedin ) : ?>
                    <a href="<?php echo esc_url( $author_linkedin ); ?>" target="_blank" rel="noopener">LinkedIn →</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</article>

<!-- Related Posts -->
<?php if ( $related_query->have_posts() ) : ?>
<section style="padding:var(--tp-space-10) 0;border-top:1px solid var(--tp-border);">
    <div class="tp-container">
        <div class="tp-section-header">
            <h2 class="tp-section-header__title">Related Stories</h2>
        </div>
        <div class="tp-grid">
            <?php while ( $related_query->have_posts() ) : $related_query->the_post(); ?>
                <article class="tp-card tp-card--standard">
                    <?php if ( has_post_thumbnail() ) : ?>
                        <div class="tp-card__image">
                            <a href="<?php the_permalink(); ?>">
                                <?php the_post_thumbnail( 'techportal-card', array( 'loading' => 'lazy' ) ); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                    <div class="tp-card__body">
                        <div class="tp-card__category"><?php techportal_category_label(); ?></div>
                        <h3 class="tp-card__title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h3>
                        <?php techportal_post_meta(); ?>
                    </div>
                </article>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php
// Previous/Next post navigation
the_post_navigation( array(
    'prev_text' => '<span style="font-size:var(--tp-text-xs);color:var(--tp-text-muted);display:block;">Previous</span>%title',
    'next_text' => '<span style="font-size:var(--tp-text-xs);color:var(--tp-text-muted);display:block;">Next</span>%title',
    'class'     => 'tp-article',
) );

// Comments
if ( comments_open() || get_comments_number() ) {
    comments_template();
}
?>

<?php endwhile; ?>

<!-- Back to Top Button -->
<button class="tp-back-to-top" aria-label="Back to top">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 15l-6-6-6 6"/></svg>
</button>

<?php get_footer(); ?>
