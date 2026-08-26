/**
 * News Radar — Admin JS (vanilla, no jQuery)
 * Dual-source: GNews + NewsAPI.org
 */
(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {

        function ajaxPost(action, data, callback) {
            var fd = new FormData();
            fd.append('action', action);
            fd.append('nonce', newsRadar.nonce);
            for (var k in data) fd.append(k, data[k]);

            var xhr = new XMLHttpRequest();
            xhr.open('POST', newsRadar.ajaxUrl, true);
            xhr.onload = function() {
                try { callback(null, JSON.parse(xhr.responseText)); }
                catch(e) { callback(e, null); }
            };
            xhr.onerror = function() { callback(new Error('Network error'), null); };
            xhr.send(fd);
        }

        var resultEl = document.getElementById('nr-api-result');

        /* ─── Test GNews ─── */
        var testGnews = document.getElementById('nr-test-gnews');
        if (testGnews) {
            testGnews.addEventListener('click', function(e) {
                e.preventDefault();
                testGnews.disabled = true;
                testGnews.textContent = '⏳ Testing…';
                resultEl.textContent = '';

                ajaxPost('newsradar_test_gnews', {}, function(err, res) {
                    testGnews.disabled = false;
                    testGnews.textContent = '🔌 Test GNews';
                    if (err || !res) {
                        resultEl.innerHTML = '<span style="color:#b32d2e">❌ GNews request failed.</span>';
                    } else if (res.ok) {
                        resultEl.innerHTML = '<span style="color:#46b450">✅ ' + res.message + '</span>';
                    } else {
                        resultEl.innerHTML = '<span style="color:#b32d2e">❌ ' + res.message + '</span>';
                    }
                });
            });
        }

        /* ─── Test NewsAPI ─── */
        var testNewsapi = document.getElementById('nr-test-newsapi');
        if (testNewsapi) {
            testNewsapi.addEventListener('click', function(e) {
                e.preventDefault();
                testNewsapi.disabled = true;
                testNewsapi.textContent = '⏳ Testing…';
                resultEl.textContent = '';

                ajaxPost('newsradar_test_newsapi', {}, function(err, res) {
                    testNewsapi.disabled = false;
                    testNewsapi.textContent = '🔌 Test NewsAPI';
                    if (err || !res) {
                        resultEl.innerHTML = '<span style="color:#b32d2e">❌ NewsAPI request failed.</span>';
                    } else if (res.ok) {
                        resultEl.innerHTML = '<span style="color:#46b450">✅ ' + res.message + '</span>';
                    } else {
                        resultEl.innerHTML = '<span style="color:#b32d2e">❌ ' + res.message + '</span>';
                    }
                });
            });
        }

        /* ─── Fetch Now ─── */
        var fetchBtn = document.getElementById('nr-fetch-now');
        if (fetchBtn) {
            fetchBtn.addEventListener('click', function(e) {
                e.preventDefault();
                fetchBtn.disabled = true;
                fetchBtn.textContent = '⏳ Fetching…';
                resultEl.textContent = '';

                ajaxPost('newsradar_fetch', {}, function(err, res) {
                    fetchBtn.disabled = false;
                    fetchBtn.textContent = '⚡ Fetch Now';
                    if (err || !res) {
                        resultEl.innerHTML = '<span style="color:#b32d2e">❌ Request failed.</span>';
                    } else if (res.success) {
                        var d = res.data;
                        var msg = 'Fetched: ' + d.fetched + ' | New: ' + d.new + ' | Duplicates: ' + d.duplicates;
                        msg += ' | GNews: ' + d.gnews_count + ' | NewsAPI: ' + d.newsapi_count;
                        if (d.errors && Object.keys(d.errors).length) {
                            msg += ' | Errors: ' + Object.keys(d.errors).length + ' topics';
                        }
                        resultEl.innerHTML = '<span style="color:#46b450">✅ ' + msg + '</span>';
                        setTimeout(function() { location.reload(); }, 1500);
                    } else {
                        resultEl.innerHTML = '<span style="color:#b32d2e">❌ ' + (res.data || 'Unknown error') + '</span>';
                    }
                });
            });
        }

        /* ─── Story Actions (delegated) ─── */
        document.addEventListener('click', function(e) {
            var btn = e.target.closest('.nr-action');
            if (!btn) return;
            e.preventDefault();

            var action = btn.getAttribute('data-action');
            var storyId = btn.getAttribute('data-id');

            if (action === 'view_source') {
                ajaxPost('newsradar_action', { story_id: storyId, story_action: 'view_source' }, function(err, res) {
                    if (res && res.success && res.data.url) window.open(res.data.url, '_blank');
                });
                return;
            }

            if (action === 'ignore') {
                if (!confirm('Ignore this story?')) return;
            }

            if (action === 'create_draft') {
                btn.disabled = true;
                btn.textContent = '⏳ Creating…';
            }

            ajaxPost('newsradar_action', { story_id: storyId, story_action: action }, function(err, res) {
                if (res && res.success) {
                    if (action === 'create_draft' && res.data.edit_url) {
                        var row = document.querySelector('tr[data-story-id="' + storyId + '"]');
                        if (row) {
                            var actionsCell = row.querySelector('.story-actions');
                            actionsCell.innerHTML =
                                '<a href="' + res.data.edit_url + '" class="button button-small" target="_blank">✏️ Edit Draft</a> ' +
                                '<button class="button button-small nr-action" data-action="view_source" data-id="' + storyId + '">🔗 Source</button>';
                            var badge = row.querySelector('.status-badge');
                            if (badge) { badge.className = 'status-badge status-drafted'; badge.textContent = 'drafted'; }
                        }
                        var newCount = document.querySelector('.status-card.new .num');
                        var draftCount = document.querySelector('.status-card.drafted .num');
                        if (newCount) newCount.textContent = Math.max(0, parseInt(newCount.textContent) - 1);
                        if (draftCount) draftCount.textContent = parseInt(draftCount.textContent) + 1;
                    } else if (action === 'ignore') {
                        var row = document.querySelector('tr[data-story-id="' + storyId + '"]');
                        if (row) {
                            var badge = row.querySelector('.status-badge');
                            if (badge) { badge.className = 'status-badge status-ignored'; badge.textContent = 'ignored'; }
                            var actionsCell = row.querySelector('.story-actions');
                            actionsCell.innerHTML =
                                '<button class="button button-small nr-action" data-action="view_source" data-id="' + storyId + '">🔗 Source</button>';
                        }
                        var newCount = document.querySelector('.status-card.new .num');
                        var ignoredCount = document.querySelector('.status-card.ignored .num');
                        if (newCount) newCount.textContent = Math.max(0, parseInt(newCount.textContent) - 1);
                        if (ignoredCount) ignoredCount.textContent = parseInt(ignoredCount.textContent) + 1;
                    }
                } else {
                    alert((res && res.data) || 'Action failed.');
                    btn.disabled = false;
                    if (action === 'create_draft') btn.textContent = '📝 Draft';
                }
            });
        });

    });
})();
