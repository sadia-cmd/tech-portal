/**
 * Portal Membership JS — Auth, Bookmarks, Account
 */
(function() {
    'use strict';

    var cfg = window.tpMembership || {};

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

    function showError(el, msg) { el.textContent = msg; el.style.display = 'block'; }
    function showSuccess(el, msg) { el.style.color = '#166534'; el.style.background = '#f0fdf4'; el.style.borderColor = '#bbf7d0'; el.textContent = msg; el.style.display = 'block'; }

    document.addEventListener('DOMContentLoaded', function() {

        /* ─── Login Form ─── */
        var loginForm = document.getElementById('tp-login-form');
        if (loginForm) {
            loginForm.addEventListener('submit', function(e) {
                e.preventDefault();
                var errEl = document.getElementById('tp-login-error');
                var btn = loginForm.querySelector('button[type="submit"]');
                btn.disabled = true; btn.textContent = 'Signing in…';
                ajax('tp_login', {
                    log: loginForm.log.value,
                    pwd: loginForm.pwd.value
                }, function(err, res) {
                    btn.disabled = false; btn.textContent = 'Sign In';
                    if (err || !res) { showError(errEl, 'Connection error. Try again.'); return; }
                    if (res.success) { window.location.href = res.data.redirect; }
                    else { showError(errEl, res.data.message); }
                });
            });
        }

        /* ─── Register Form ─── */
        var regForm = document.getElementById('tp-register-form');
        if (regForm) {
            regForm.addEventListener('submit', function(e) {
                e.preventDefault();
                var errEl = document.getElementById('tp-register-error');
                var btn = regForm.querySelector('button[type="submit"]');
                btn.disabled = true; btn.textContent = 'Creating account…';
                ajax('tp_register', {
                    display_name: regForm.display_name.value,
                    user_email: regForm.user_email.value,
                    user_login: regForm.user_login.value,
                    user_pass: regForm.user_pass.value
                }, function(err, res) {
                    btn.disabled = false; btn.textContent = 'Create Account';
                    if (err || !res) { showError(errEl, 'Connection error. Try again.'); return; }
                    if (res.success) { window.location.href = res.data.redirect; }
                    else { showError(errEl, res.data.message); }
                });
            });
        }

        /* ─── Account Tabs ─── */
        var tabs = document.querySelectorAll('.tp-account-tab');
        var panels = document.querySelectorAll('.tp-account-panel');
        tabs.forEach(function(tab) {
            tab.addEventListener('click', function() {
                tabs.forEach(function(t) { t.classList.remove('active'); });
                panels.forEach(function(p) { p.classList.remove('active'); });
                tab.classList.add('active');
                var target = document.getElementById('tp-panel-' + tab.dataset.tab);
                if (target) target.classList.add('active');
                if (tab.dataset.tab === 'saved') loadBookmarks('tp-saved-list');
            });
        });

        /* ─── Profile Form ─── */
        var profileForm = document.getElementById('tp-profile-form');
        if (profileForm) {
            profileForm.addEventListener('submit', function(e) {
                e.preventDefault();
                var msgEl = document.getElementById('tp-profile-msg');
                var btn = profileForm.querySelector('button[type="submit"]');
                btn.disabled = true; btn.textContent = 'Saving…';
                ajax('tp_update_profile', {
                    display_name: profileForm.display_name.value,
                    user_email: profileForm.user_email.value,
                    user_url: profileForm.user_url.value,
                    description: profileForm.description.value,
                    tp_location: profileForm.tp_location ? profileForm.tp_location.value : ''
                }, function(err, res) {
                    btn.disabled = false; btn.textContent = 'Save Changes';
                    if (res && res.success) showSuccess(msgEl, res.data.message);
                    else showError(msgEl, (res && res.data) ? res.data.message : 'Error saving profile.');
                });
            });
        }

        /* ─── Password Form ─── */
        var passForm = document.getElementById('tp-password-form');
        if (passForm) {
            passForm.addEventListener('submit', function(e) {
                e.preventDefault();
                var msgEl = document.getElementById('tp-pass-msg');
                var btn = passForm.querySelector('button[type="submit"]');
                if (passForm.new_password.value !== passForm.confirm_password.value) {
                    showError(msgEl, 'Passwords do not match.'); return;
                }
                btn.disabled = true; btn.textContent = 'Changing…';
                ajax('tp_change_password', {
                    current_password: passForm.current_password.value,
                    new_password: passForm.new_password.value,
                    confirm_password: passForm.confirm_password.value
                }, function(err, res) {
                    btn.disabled = false; btn.textContent = 'Change Password';
                    if (res && res.success) { showSuccess(msgEl, res.data.message); passForm.reset(); }
                    else showError(msgEl, (res && res.data) ? res.data.message : 'Error changing password.');
                });
            });
        }

        /* ─── Bookmark Buttons ─── */
        document.addEventListener('click', function(e) {
            var btn = e.target.closest('.tp-bookmark-btn');
            if (!btn) return;
            e.preventDefault();

            if (!cfg.loggedIn) {
                window.location.href = '/login/';
                return;
            }

            btn.style.pointerEvents = 'none';
            ajax('tp_toggle_bookmark', {
                post_id: btn.dataset.postId,
                post_type: btn.dataset.postType || 'post'
            }, function(err, res) {
                btn.style.pointerEvents = '';
                if (err || !res || !res.success) {
                    alert((res && res.data && res.data.message) || 'Error. Please try again.');
                    return;
                }
                var svg = btn.querySelector('svg');
                if (res.data.bookmarked) {
                    btn.classList.add('is-bookmarked');
                    svg.setAttribute('fill', 'currentColor');
                } else {
                    btn.classList.remove('is-bookmarked');
                    svg.setAttribute('fill', 'none');
                }
            });
        });

        /* ─── Load Bookmarks ─── */
        function loadBookmarks(containerId) {
            var container = document.getElementById(containerId);
            if (!container) return;
            container.innerHTML = '<p style="color:var(--tp-muted);">Loading…</p>';

            ajax('tp_get_bookmarks', {}, function(err, res) {
                if (err || !res || !res.success) {
                    container.innerHTML = '<p style="color:var(--tp-muted);">Could not load bookmarks.</p>';
                    return;
                }
                var items = res.data.items;
                if (!items || items.length === 0) {
                    container.innerHTML = '<div class="tp-saved-empty"><div class="tp-saved-empty__icon">🔖</div><h3>No saved items yet</h3><p>Click the bookmark icon on any article to save it here.</p></div>';
                    return;
                }
                var html = '<div class="tp-saved-grid">';
                items.forEach(function(item) {
                    html += '<div class="tp-saved-card" data-post-id="' + item.post_id + '">';
                    if (item.thumbnail) {
                        html += '<a href="' + item.url + '"><img class="tp-saved-card__image" src="' + item.thumbnail + '" alt="" loading="lazy" /></a>';
                    }
                    html += '<div class="tp-saved-card__body">';
                    if (item.category) html += '<div class="tp-saved-card__cat">' + item.category + '</div>';
                    html += '<h3 class="tp-saved-card__title"><a href="' + item.url + '">' + item.title + '</a></h3>';
                    html += '<div class="tp-saved-card__meta">';
                    html += '<span>' + item.date + '</span>';
                    html += '<button class="tp-saved-card__remove" data-post-id="' + item.post_id + '" title="Remove">✕</button>';
                    html += '</div></div></div>';
                });
                html += '</div>';
                container.innerHTML = html;
            });
        }

        /* ─── Remove Bookmark from Saved List ─── */
        document.addEventListener('click', function(e) {
            var btn = e.target.closest('.tp-saved-card__remove');
            if (!btn) return;
            e.preventDefault();
            var postId = btn.dataset.postId;
            ajax('tp_toggle_bookmark', { post_id: postId, post_type: 'post' }, function(err, res) {
                if (res && res.success && !res.data.bookmarked) {
                    var card = btn.closest('.tp-saved-card');
                    if (card) card.remove();
                    // Update counts
                    var countEls = document.querySelectorAll('[data-saved-count]');
                    countEls.forEach(function(el) {
                        var n = parseInt(el.textContent) || 0;
                        el.textContent = Math.max(0, n - 1);
                    });
                    // Reload if empty
                    var grid = document.querySelector('.tp-saved-grid');
                    if (grid && grid.children.length === 0) {
                        loadBookmarks('tp-saved-list');
                    }
                }
            });
        });

        /* ─── Logout ─── */
        var logoutBtn = document.getElementById('tp-logout-btn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', function(e) {
                e.preventDefault();
                ajax('tp_logout', {}, function(err, res) {
                    if (res && res.success) window.location.href = res.data.redirect;
                });
            });
        }
        var headerLogout = document.getElementById('tp-header-logout');
        if (headerLogout) {
            headerLogout.addEventListener('click', function(e) {
                e.preventDefault();
                ajax('tp_logout', {}, function(err, res) {
                    if (res && res.success) window.location.href = res.data.redirect;
                });
            });
        }
    });
})();
