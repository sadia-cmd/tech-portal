/**
 * Phase 7 — Dark Mode, Infinite Scroll, Social Share, Progress Bar, TOC, Pull Quotes
 * @package PortalCore
 */

(function() {
'use strict';

/* ─── Dark Mode ─── */
const dm = {
    KEY: 'tp_dark_mode',
    init() {
        const saved = localStorage.getItem(this.KEY);
        if (saved === '1' || (!saved && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
        document.querySelectorAll('.tp-dark-toggle').forEach(btn => {
            btn.addEventListener('click', () => this.toggle());
            this.updateIcon(btn);
        });
    },
    toggle() {
        document.documentElement.classList.toggle('dark');
        const isDark = document.documentElement.classList.contains('dark');
        localStorage.setItem(this.KEY, isDark ? '1' : '0');
        document.querySelectorAll('.tp-dark-toggle').forEach(btn => this.updateIcon(btn));
    },
    updateIcon(btn) {
        const isDark = document.documentElement.classList.contains('dark');
        if (btn.querySelector('.tp-moon')) btn.querySelector('.tp-moon').style.display = isDark ? 'none' : 'block';
        if (btn.querySelector('.tp-sun')) btn.querySelector('.tp-sun').style.display = isDark ? 'block' : 'none';
    }
};

/* ─── Reading Progress Bar ─── */
const progress = {
    bar: null,
    init() {
        this.bar = document.querySelector('.tp-progress-bar');
        if (!this.bar) return;
        window.addEventListener('scroll', () => this.update(), { passive: true });
        this.update();
    },
    update() {
        if (!this.bar) return;
        const doc = document.documentElement.scrollHeight - window.innerHeight;
        const pct = doc > 0 ? (window.scrollY / doc) * 100 : 0;
        this.bar.style.width = pct + '%';
    }
};

/* ─── Social Share Bar (floating) ─── */
const share = {
    init() {
        const bar = document.querySelector('.tp-share-floating');
        if (!bar) return;
        window.addEventListener('scroll', () => {
            bar.classList.toggle('tp-share-floating--visible', window.scrollY > 400);
        }, { passive: true });
    }
};

/* ─── Table of Contents ─── */
const toc = {
    init() {
        const container = document.querySelector('.tp-toc');
        const content = document.querySelector('.tp-article__content');
        if (!container || !content) return;

        const headings = content.querySelectorAll('h2, h3');
        if (headings.length < 2) { container.style.display = 'none'; return; }

        let html = '<div class="tp-toc__title">Table of Contents</div><nav class="tp-toc__nav">';
        headings.forEach((h, i) => {
            const id = 'section-' + i;
            h.id = id;
            const indent = h.tagName === 'H3' ? 'padding-left:1rem;' : '';
            html += '<a href="#' + id + '" class="tp-toc__link" style="' + indent + '">' + h.textContent + '</a>';
        });
        html += '</nav>';
        container.innerHTML = html;
        container.style.display = 'block';

        // Highlight active section
        const links = container.querySelectorAll('.tp-toc__link');
        const observer = new IntersectionObserver(entries => {
            entries.forEach(e => {
                if (e.isIntersecting) {
                    links.forEach(l => l.classList.remove('tp-toc__link--active'));
                    const link = container.querySelector('a[href="#' + e.target.id + '"]');
                    if (link) link.classList.add('tp-toc__link--active');
                }
            });
        }, { rootMargin: '-80px 0px -60% 0px' });
        headings.forEach(h => observer.observe(h));
    }
};

/* ─── Pull Quotes (highlight to tweet) ─── */
const pullquotes = {
    init() {
        document.addEventListener('mouseup', () => {
            setTimeout(() => this.check(), 10);
        });
    },
    check() {
        // Remove existing popup
        const old = document.querySelector('.tp-pullquote-popup');
        if (old) old.remove();

        const sel = window.getSelection();
        const text = sel ? sel.toString().trim() : '';
        if (text.length < 20 || text.length > 280) return;

        const range = sel.getRangeAt(0);
        const rect = range.getBoundingClientRect();

        // Don't show if selection is inside the share bar or nav
        const parent = range.commonAncestorContainer.parentElement;
        if (parent && (parent.closest('.tp-share-floating') || parent.closest('nav') || parent.closest('header'))) return;

        const popup = document.createElement('div');
        popup.className = 'tp-pullquote-popup';
        popup.innerHTML = '<button class="tp-pullquote-btn" data-action="tweet">Tweet this</button>' +
            '<button class="tp-pullquote-btn" data-action="copy">Copy</button>';

        popup.style.position = 'fixed';
        popup.style.left = (rect.left + rect.width / 2) + 'px';
        popup.style.top = (rect.top - 45 + window.scrollY) + 'px';
        popup.style.transform = 'translateX(-50%)';
        document.body.appendChild(popup);

        popup.querySelector('[data-action="tweet"]').addEventListener('click', () => {
            const url = encodeURIComponent(window.location.href);
            const quote = encodeURIComponent(text);
            window.open('https://twitter.com/intent/tweet?text=' + quote + '&url=' + url, '_blank', 'width=600,height=400');
            popup.remove();
        });

        popup.querySelector('[data-action="copy"]').addEventListener('click', () => {
            navigator.clipboard.writeText(text).then(() => {
                popup.querySelector('[data-action="copy"]').textContent = 'Copied!';
                setTimeout(() => popup.remove(), 800);
            });
        });

        // Close on click outside
        document.addEventListener('mousedown', function close(e) {
            if (!popup.contains(e.target)) {
                popup.remove();
                document.removeEventListener('mousedown', close);
            }
        });
    }
};

/* ─── Infinite Scroll / Load More ─── */
const infiniteScroll = {
    init() {
        const btn = document.querySelector('.tp-load-more');
        const container = document.querySelector('.tp-infinite-scroll');
        if (!btn || !container) return;

        btn.addEventListener('click', () => this.load(btn, container));

        // Auto-load on scroll (optional)
        if (container.dataset.autoLoad === 'true') {
            const sentinel = document.createElement('div');
            sentinel.className = 'tp-scroll-sentinel';
            container.appendChild(sentinel);
            const obs = new IntersectionObserver(entries => {
                if (entries[0].isIntersecting && !btn.classList.contains('tp-loading')) {
                    this.load(btn, container);
                }
            }, { rootMargin: '200px' });
            obs.observe(sentinel);
        }
    },
    load(btn, container) {
        const page = parseInt(btn.dataset.page || '1') + 1;
        const max = parseInt(btn.dataset.max || '10');
        btn.classList.add('tp-loading');
        btn.textContent = 'Loading...';

        fetch(window.location.pathname + '?tp_ajax_load=' + page + '&tp_ajax_type=' + (btn.dataset.type || 'posts'))
            .then(r => r.text())
            .then(html => {
                if (html.trim() === '' || page >= max) {
                    btn.style.display = 'none';
                    return;
                }
                container.insertAdjacentHTML('beforeend', html);
                btn.dataset.page = page;
                btn.classList.remove('tp-loading');
                btn.textContent = 'Load More';
            })
            .catch(() => {
                btn.classList.remove('tp-loading');
                btn.textContent = 'Load More';
            });
    }
};

/* ─── Back to Top ─── */
const backToTop = {
    init() {
        const btn = document.querySelector('.tp-back-to-top');
        if (!btn) return;
        window.addEventListener('scroll', () => {
            btn.classList.toggle('tp-back-to-top--visible', window.scrollY > 600);
        }, { passive: true });
        btn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
    }
};

/* ─── Initialize all ─── */
document.addEventListener('DOMContentLoaded', () => {
    dm.init();
    progress.init();
    share.init();
    toc.init();
    pullquotes.init();
    infiniteScroll.init();
    backToTop.init();
});

})();
