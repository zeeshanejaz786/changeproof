jQuery(document).ready(function($) {
    'use strict';

    // Helper to prepend Dashicons
    function addIcon($btn, iconClass) {
        if (!$btn.find('span.dashicons').length) {
            $btn.prepend('<span class="dashicons ' + iconClass + '" style="margin-right:6px;"></span>');
        }
    }

    // Start Investigation
    const $startBtn = $('#cp-btn-start-investigation');
    addIcon($startBtn, 'dashicons-flag'); // Flag icon for start

    $startBtn.on('click', function() {
        const note = $('#cp-start-note').val().trim();
        const nonce = $(this).data('nonce');

        if (!note) {
            alert('Please provide an initial note.');
            return;
        }

        $.post(ajaxurl, {
            action: 'cp_ajax_start_investigation',
            security: nonce,
            note: note
        }, function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert(response.data.message || response.data || 'Failed to start investigation.');
            }
        });
    });

    // End Investigation
    const $endBtn = $('#cp-btn-end-investigation');
    addIcon($endBtn, 'dashicons-yes'); // Checkmark icon for end

    $endBtn.on('click', function() {
        const note = $('#cp-final-note').val().trim();
        const nonce = $(this).data('nonce');

        $.post(ajaxurl, {
            action: 'cp_ajax_end_investigation',
            security: nonce,
            note: note,
            status: 'completed'
        }, function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert(response.data.message || response.data || 'Failed to end investigation.');
            }
        });
    });
});
