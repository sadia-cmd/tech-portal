/**
 * Portal Features JS — Submit News, Video Archive Search
 */
(function() {
    'use strict';

    var cfg = window.tpFeatures || {};

    function ajax(action, data, cb) {
        var fd = new FormData();
        fd.append('action', action);
        fd.append('tp_nonce', cfg.nonce);
        for (var k in data) fd.append(k, data[k]);
        var xhr = new XMLHttpRequest();
        xhr.open('POST', cfg.ajaxUrl, true);
        xhr.onload = function() {
            try { cb(null, JSON.parse(xhr.responseText)); }
            catch(e) { cb(e, null); }
        };
        xhr.onerror = function() { cb(new Error('Network error'), null); };
        xhr.send(fd);
    }

    function ajaxGet(url, cb) {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', url, true);
        xhr.onload = function() {
            try { cb(null, JSON.parse(xhr.responseText)); }
            catch(e) { cb(e, null); }
        };
        xhr.onerror = function() { cb(new Error('Network error'), null); };
        xhr.send();
    }

    document.addEventListener('DOMContentLoaded', function() {

        /* ─── Submit News Form ─── */
        var submitForm = document.getElementById('tp-submit-form');
        if (submitForm) {
            submitForm.addEventListener('submit', function(e) {
                e.preventDefault();
                var errEl  = document.getElementById('tp-submit-error');
                var sucEl  = document.getElementById('tp-submit-success');
                var btn    = submitForm.querySelector('button[type="submit"]');
                errEl.style.display = 'none';
                sucEl.style.display = 'none';
                btn.disabled = true; btn.textContent = 'Submitting…';

                var fd = new FormData(submitForm);
                fd.append('action', 'tp_submit_news');
                fd.append('tp_nonce', cfg.nonce);

                var xhr = new XMLHttpRequest();
                xhr.open('POST', cfg.ajaxUrl, true);
                xhr.onload = function() {
                    btn.disabled = false; btn.textContent = 'Submit for Review';
                    try {
                        var res = JSON.parse(xhr.responseText);
                        if (res.success) {
                            sucEl.textContent = res.data.message;
                            sucEl.style.display = 'block';
                            submitForm.reset();
                        } else {
                            errEl.textContent = res.data.message;
                            errEl.style.display = 'block';
                        }
                    } catch(ex) {
                        errEl.textContent = 'Connection error. Please try again.';
                        errEl.style.display = 'block';
                    }
                };
                xhr.onerror = function() {
                    btn.disabled = false; btn.textContent = 'Submit for Review';
                    errEl.textContent = 'Network error. Please try again.';
                    errEl.style.display = 'block';
                };
                xhr.send(fd);
            });
        }

        /* ─── Video Archive Search ─── */
        var vidSearch = document.getElementById('tp-vid-search');
        var vidTopic  = document.getElementById('tp-vid-topic');
        var vidShow   = document.getElementById('tp-vid-show');
        var vidResults = document.getElementById('tp-vid-results');

        if (vidSearch && vidResults) {
            var searchTimeout;

            function loadVideos(page) {
                page = page || 1;
                vidResults.innerHTML = '<p class="tp-vid-empty"><span class="tp-spinner tp-spinner--dark"></span>Searching…</p>';

                var params = new URLSearchParams();
                params.append('action', 'tp_search_episodes');
                params.append('s', vidSearch.value);
                params.append('topic', vidTopic.value);
                params.append('show', vidShow.value);
                params.append('page', page);

                ajaxGet(cfg.ajaxUrl + '?' + params.toString(), function(err, res) {
                    if (err || !res || !res.success) {
                        vidResults.innerHTML = '<p class="tp-vid-empty">Error loading videos.</p>';
                        return;
                    }
                    var items = res.data.items;
                    var total = res.data.total;
                    var totalPages = res.data.total_pages;

                    if (!items || items.length === 0) {
                        vidResults.innerHTML = '<p class="tp-vid-empty">No videos found. Try different search terms.</p>';
                        return;
                    }

                    var html = '';
                    items.forEach(function(v) {
                        html += '<article class="tp-vid-card">';
                        html += '<a href="' + v.url + '" class="tp-vid-card__image">';
                        if (v.thumbnail) {
                            html += '<img src="' + v.thumbnail + '" alt="" loading="lazy" />';
                        } else {
                            html += '<div style="width:100%;height:100%;background:linear-gradient(135deg,var(--tp-purple),var(--tp-blue));display:flex;align-items:center;justify-content:center;"><svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.3)" stroke-width="1.5"><polygon points="5 3 19 12 5 21 5 3"/></svg></div>';
                        }
                        if (v.is_live) html += '<span class="tp-vid-card__badge tp-vid-card__badge--live">🔴 LIVE</span>';
                        else if (v.is_upcoming) html += '<span class="tp-vid-card__badge tp-vid-card__badge--upcoming">🕐 UPCOMING</span>';
                        if (v.duration) html += '<span class="tp-vid-card__duration">' + v.duration + '</span>';
                        html += '</a>';
                        html += '<div class="tp-vid-card__body">';
                        if (v.topic) html += '<div class="tp-vid-card__topic">' + v.topic + '</div>';
                        html += '<h3 class="tp-vid-card__title"><a href="' + v.url + '">' + v.title + '</a></h3>';
                        html += '<div class="tp-vid-card__meta">';
                        html += '<span>' + v.date + (v.guest ? ' · ' + v.guest : '') + '</span>';
                        if (v.views) html += '<span>👁 ' + v.views.toLocaleString() + '</span>';
                        html += '</div></div></article>';
                    });
                    vidResults.innerHTML = html;

                    // Pagination
                    var pag = document.getElementById('tp-vid-pagination');
                    if (pag && totalPages > 1) {
                        var ph = '';
                        if (page > 1) ph += '<button class="tp-btn tp-btn--outline tp-btn--sm" data-page="' + (page-1) + '">← Prev</button>';
                        ph += '<span style="padding:0 12px;font-size:13px;color:var(--tp-muted);">Page ' + page + ' of ' + totalPages + ' (' + total + ' videos)</span>';
                        if (page < totalPages) ph += '<button class="tp-btn tp-btn--outline tp-btn--sm" data-page="' + (page+1) + '">Next →</button>';
                        pag.innerHTML = ph;
                    }
                });
            }

            // Debounced search
            vidSearch.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() { loadVideos(1); }, 400);
            });
            vidTopic.addEventListener('change', function() { loadVideos(1); });
            vidShow.addEventListener('change', function() { loadVideos(1); });

            // Pagination clicks
            document.addEventListener('click', function(e) {
                var btn = e.target.closest('#tp-vid-pagination button[data-page]');
                if (btn) { e.preventDefault(); loadVideos(parseInt(btn.dataset.page)); }
            });

            // Initial load
            loadVideos(1);
        }
    });
})();
