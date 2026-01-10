(function($) {
    'use strict';

    const Changeproof = {
        postsState: {},

        init: function() {
            this.isGutenberg = typeof wp !== 'undefined' && wp.data && wp.data.select('core/editor');
            this.bindEvents();
        },

        bindEvents: function() {
            const self = this;

            // Gutenberg save interception
            if (self.isGutenberg) {
                wp.data.subscribe(function() {
                    const post = wp.data.select('core/editor').getCurrentPost();
                    if (!post) return;

                    const postID = post.id;
                    const isSaving = wp.data.select('core/editor').isSavingPost();
                    const isAutosave = wp.data.select('core/editor').isAutosavingPost();

                    if (isSaving && !isAutosave && !self.postsState[postID]?.verified && !self.postsState[postID]?.processing) {
                        self.postsState[postID] = { processing: true };
                        wp.data.dispatch('core/editor').lockPostSaving('cp_lock_' + postID);
                        self.showModal(postID);
                    }
                });
            }

            // Classic / Quick Edit
            $(document).on('click', '#publish, #save-post, .inline-edit-save .save', function(e) {
                const $btn = $(this);
                const postID = $btn.data('post-id') || $('#post_ID').val() || 'unknown';
                self.postsState[postID] = self.postsState[postID] || {};

                if (self.postsState[postID].verified) return true;

                e.preventDefault();
                self.postsState[postID].processing = true;
                self.showModal(postID, $btn);
            });

            // Modal confirm
            $(document).on('click', '#cp-confirm-intent', function() {
                const reason = $('#cp-intent-reason').val().trim();
                if (reason.length < 5) return alert('Please provide a reason.');
                const postID = $(this).data('post-id');

                $(this).prop('disabled', true).text('Verifying...');
                self.submitIntent(reason, postID);
            });

            // Modal cancel
            $(document).on('click', '#cp-cancel-intent', function() {
                const postID = $(this).data('post-id');
                self.hideModal(postID);

                if (self.isGutenberg && wp.data.select('core/editor').getCurrentPost()?.id == postID) {
                    wp.data.dispatch('core/editor').unlockPostSaving('cp_lock_' + postID);
                }

                self.postsState[postID] = { verified: false, processing: false };
            });
        },

        showModal: function(postID, $btn = null) {
            $('#cp-intent-reason').val('');
            $('#cp-intent-modal').css('display', 'flex').show();
            $('#cp-confirm-intent').data('post-id', postID);
            $('#cp-cancel-intent').data('post-id', postID);
            $('#cp-intent-reason').focus();
            this.postsState[postID].currentBtn = $btn;
        },

        hideModal: function(postID) {
            $('#cp-intent-modal').hide();
            $('#cp-confirm-intent').prop('disabled', false).text('Confirm & Save');
        },

        submitIntent: function(reason, postID) {
            const self = this;
            $.ajax({
                url: cp_data.ajax_url,
                type: 'POST',
                data: {
                    action: 'cp_submit_intent',
                    security: cp_data.nonce,
                    reason: reason
                },
                success: function() {
                    self.postsState[postID].verified = true;
                    self.hideModal(postID);
                    self.saveNow(postID);
                },
                error: function() {
                    alert('Failed to save intent. Please try again.');
                    self.postsState[postID].processing = false;
                }
            });
        },

        saveNow: function(postID) {
            const state = this.postsState[postID];
            if (!state) return;

            if (this.isGutenberg) {
                wp.data.dispatch('core/editor').unlockPostSaving('cp_lock_' + postID);
                wp.data.dispatch('core/editor').savePost();
            } else if (state.currentBtn) {
                state.currentBtn.off('click.cpSave').trigger('click');
            }

            // Reset state for next save
            this.postsState[postID].processing = false;
            setTimeout(() => {
                this.postsState[postID].verified = false;
            }, 500);
        }
    };

    $(document).ready(() => Changeproof.init());
})(jQuery);
