(function($) {
    'use strict';

    const Changeproof = {
        isGutenberg: false,
        isLocked: false,
        intentVerified: false,

        init: function() {
            this.isGutenberg = typeof wp !== 'undefined' && typeof wp.data !== 'undefined' && typeof wp.editPost !== 'undefined';
            
           

            if (this.isGutenberg) {
                this.initBlockEditor();
            } else {
                this.initClassicEditor();
            }

            this.bindButtons();
        },

        // Universal button binding (Works for both editors)
        bindButtons: function() {
            var self = this;

            // Use 'body' delegation to ensure clicks are caught even if Gutenberg re-renders
            $('body').on('click', '#cp-confirm-intent', function(e) {
                e.preventDefault();
                e.stopImmediatePropagation();
                
                const reason = $('#cp-intent-reason').val();
              

                if (reason.length >= 5) {
                    $(this).prop('disabled', true).text('Processing...');
                    self.submitIntent(reason);
                } else {
                    alert('Reason too short.');
                }
            });

            $('body').on('click', '#cp-cancel-intent', function(e) {
                e.preventDefault();
                e.stopImmediatePropagation();
               
                self.closeModal();
            });
        },

        initClassicEditor: function() {
            var self = this;
            $('#publish, #save-post').on('click', function(e) {
                if (self.intentVerified) return true;
                e.preventDefault();
                self.openModal();
            });
        },

        initBlockEditor: function() {
            var self = this;
            
            // Watch the saving state
            wp.data.subscribe(function() {
                const isSaving = wp.data.select('core/editor').isSavingPost();
                const isAutosaving = wp.data.select('core/editor').isAutosavingPost();

                // If Gutenberg starts saving and we haven't verified intent yet
                if (isSaving && !isAutosaving && !self.intentVerified && !self.isLocked) {
                  
                    
                    // 1. Lock the editor immediately
                    self.isLocked = true;
                    wp.data.dispatch('core/editor').lockPostSaving('cp_intent_lock');
                    
                    // 2. Open Modal
                    self.openModal();
                }
            });
        },

        openModal: function() {
          
            $('#cp-intent-modal').fadeIn(200);
            $('#cp-intent-reason').focus();
        },

        closeModal: function() {
           
            $('#cp-intent-modal').hide();
            
            if (this.isGutenberg) {
                wp.data.dispatch('core/editor').unlockPostSaving('cp_intent_lock');
            }
            this.isLocked = false;
            $('#cp-confirm-intent').prop('disabled', false).text('Confirm & Save');
        },

        submitIntent: function(reason) {
           
            var self = this;
            $.ajax({
                url: cp_data.ajax_url,
                type: 'POST',
                data: {
                    action: 'cp_submit_intent',
                    security: cp_data.nonce,
                    reason: reason
                },
                success: function(response) {
                    if (response.success) {
                       
                        self.intentVerified = true;
                        self.closeModal();
                        self.resumeSave();
                    } else {
                        alert("Error: " + response.data);
                        self.closeModal();
                    }
                }
            });
        },

        resumeSave: function() {
            if (this.isGutenberg) {
                wp.data.dispatch('core/editor').savePost();
            } else {
                $('#publish').trigger('click');
            }
        }
    };

    $(document).ready(function() {
        Changeproof.init();
    });

})(jQuery);