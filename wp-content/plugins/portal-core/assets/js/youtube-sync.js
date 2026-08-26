/**
 * YouTube Sync Admin JS
 */
(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {

        function ajaxPost(action, data, callback) {
            var fd = new FormData();
            fd.append('action', action);
            fd.append('nonce', youtubeSync.nonce);
            for (var k in data) fd.append(k, data[k]);

            var xhr = new XMLHttpRequest();
            xhr.open('POST', youtubeSync.ajaxUrl, true);
            xhr.onload = function() {
                try { callback(null, JSON.parse(xhr.responseText)); }
                catch(e) { callback(e, null); }
            };
            xhr.onerror = function() { callback(new Error('Network error'), null); };
            xhr.send(fd);
        }

        var resultEl = document.getElementById('yt-result');
        var testBtn = document.getElementById('yt-test-btn');
        var syncBtn = document.getElementById('yt-sync-btn');

        /* Test Connection */
        if (testBtn) {
            testBtn.addEventListener('click', function(e) {
                e.preventDefault();
                testBtn.disabled = true;
                testBtn.textContent = '⏳ Testing…';
                resultEl.textContent = '';

                ajaxPost('youtube_test_connection', {}, function(err, res) {
                    testBtn.disabled = false;
                    testBtn.textContent = '🔌 Test Connection';
                    if (err || !res) {
                        resultEl.innerHTML = '<span style="color:#b32d2e">❌ Request failed.</span>';
                    } else if (res.ok) {
                        resultEl.innerHTML = '<span style="color:#46b450">✅ ' + res.message + '</span>';
                    } else {
                        resultEl.innerHTML = '<span style="color:#b32d2e">❌ ' + res.message + '</span>';
                    }
                });
            });
        }

        /* Sync Now */
        if (syncBtn) {
            syncBtn.addEventListener('click', function(e) {
                e.preventDefault();
                syncBtn.disabled = true;
                syncBtn.textContent = '⏳ Syncing…';
                resultEl.textContent = '';

                ajaxPost('youtube_sync_now', {}, function(err, res) {
                    syncBtn.disabled = false;
                    syncBtn.textContent = '⚡ Sync Now';
                    if (err || !res) {
                        resultEl.innerHTML = '<span style="color:#b32d2e">❌ Request failed.</span>';
                    } else if (res.success) {
                        var d = res.data;
                        var msg = 'Found: ' + d.found + ' | Created: ' + d.created + ' | Updated: ' + d.updated;
                        if (d.errors && d.errors.length) {
                            msg += ' | Errors: ' + d.errors.length;
                        }
                        resultEl.innerHTML = '<span style="color:#46b450">✅ ' + msg + '</span>';
                        setTimeout(function() { location.reload(); }, 2000);
                    } else {
                        resultEl.innerHTML = '<span style="color:#b32d2e">❌ ' + (res.data.message || 'Sync failed.') + '</span>';
                    }
                });
            });
        }
    });
})();
