/**
 * News Radar — Admin JS
 */
(function($) {
    'use strict';

    /* ─── Test API ─── */
    $('#nr-test-api').on('click', function(e) {
        e.preventDefault();
        var $btn = $(this), $result = $('#nr-api-result');
        $btn.prop('disabled', true).after('<span class="newsradar-loading"></span>');
        $result.text('');

        $.post(newsRadar.ajaxUrl, {
            action: 'newsradar_test_api',
            nonce: newsRadar.nonce
        }, function(res) {
            $btn.prop('disabled', false).next('.newsradar-loading').remove();
            if (res.ok) {
                $result.html('<span style="color:#46b450">✅ ' + res.message + '</span>');
            } else {
                $result.html('<span style="color:#b32d2e">❌ ' + res.message + '</span>');
            }
        }).fail(function() {
            $btn.prop('disabled', false).next('.newsradar-loading').remove();
            $result.html('<span style="color:#b32d2e">❌ Request failed.</span>');
        });
    });

    /* ─── Fetch Now ─── */
    $('#nr-fetch-now').on('click', function(e) {
        e.preventDefault();
        var $btn = $(this), $result = $('#nr-api-result');
        $btn.prop('disabled', true).text('⏳ Fetching…');
        $result.text('');

        $.post(newsRadar.ajaxUrl, {
            action: 'newsradar_fetch',
            nonce: newsRadar.nonce
        }, function(res) {
            $btn.prop('disabled', false).text('⚡ Fetch Now');
            if (res.success) {
                var d = res.data;
                var msg = 'Fetched: ' + d.fetched + ' | New: ' + d.new + ' | Duplicates: ' + d.duplicates;
                if (Object.keys(d.errors).length) {
                    msg += ' | Errors: ' + JSON.stringify(d.errors);
                }
                $result.html('<span style="color:#46b450">✅ ' + msg + '</span>');
                // Reload page after 1.5s to show new stories
                setTimeout(function() { location.reload(); }, 1500);
            } else {
                $result.html('<span style="color:#b32d2e">❌ ' + (res.data || 'Unknown error') + '</span>');
            }
        }).fail(function() {
            $btn.prop('disabled', false).text('⚡ Fetch Now');
            $result.html('<span style="color:#b32d2e">❌ Request failed.</span>');
        });
    });

    /* ─── Story Actions ─── */
    $(document).on('click', '.nr-action', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var action = $btn.data('action');
        var storyId = $btn.data('id');

        if (action === 'view_source') {
            // Open in new tab via AJAX to get URL
            $.post(newsRadar.ajaxUrl, {
                action: 'newsradar_action',
                nonce: newsRadar.nonce,
                story_id: storyId,
                story_action: 'view_source'
            }, function(res) {
                if (res.success && res.data.url) {
                    window.open(res.data.url, '_blank');
                }
            });
            return;
        }

        if (action === 'ignore') {
            if (!confirm('Ignore this story?')) return;
        }

        if (action === 'create_draft') {
            $btn.prop('disabled', true).text('⏳ Creating…');
        }

        $.post(newsRadar.ajaxUrl, {
            action: 'newsradar_action',
            nonce: newsRadar.nonce,
            story_id: storyId,
            story_action: action
        }, function(res) {
            if (res.success) {
                if (action === 'create_draft' && res.data.edit_url) {
                    // Show link to edit the draft
                    var $row = $('tr[data-story-id="' + storyId + '"]');
                    var $actionsCell = $row.find('.story-actions');
                    $actionsCell.html(
                        '<a href="' + res.data.edit_url + '" class="button button-small" target="_blank">✏️ Edit Draft</a> ' +
                        '<button class="button button-small nr-action" data-action="view_source" data-id="' + storyId + '">🔗 Source</button>'
                    );
                    // Update status badge
                    $row.find('.status-badge').removeClass('status-new').addClass('status-drafted').text('drafted');
                    // Update counts
                    var $newCount = $('.status-card.new .num');
                    var $draftCount = $('.status-card.drafted .num');
                    $newCount.text(Math.max(0, parseInt($newCount.text()) - 1));
                    $draftCount.text(parseInt($draftCount.text()) + 1);
                } else if (action === 'ignore') {
                    var $row = $('tr[data-story-id="' + storyId + '"]');
                    $row.find('.status-badge').removeClass('status-new').addClass('status-ignored').text('ignored');
                    $row.find('.story-actions').html(
                        '<button class="button button-small nr-action" data-action="view_source" data-id="' + storyId + '">🔗 Source</button>'
                    );
                    var $newCount = $('.status-card.new .num');
                    var $ignoredCount = $('.status-card.ignored .num');
                    $newCount.text(Math.max(0, parseInt($newCount.text()) - 1));
                    $ignoredCount.text(parseInt($ignoredCount.text()) + 1);
                }
            } else {
                alert(res.data || 'Action failed.');
                $btn.prop('disabled', false);
                if (action === 'create_draft') $btn.text('📝 Draft');
            }
        }).fail(function() {
            alert('Request failed.');
            $btn.prop('disabled', false);
            if (action === 'create_draft') $btn.text('📝 Draft');
        });
    });

})(jQuery);
