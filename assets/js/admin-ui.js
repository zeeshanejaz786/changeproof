jQuery(document).ready(function($) {
    'use strict';

    // Start Investigation
    $('#cp-btn-start-investigation').on('click', function() {
        const note = $('#cp-start-note').val();
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
                alert(response.data);
            }
        });
    });

    // End Investigation
    $('#cp-btn-end-investigation').on('click', function() {
        const note = $('#cp-final-note').val();
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
                alert(response.data);
            }
        });
    });
});