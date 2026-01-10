<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div id="cp-intent-modal" style="display:none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 99999999; align-items: center; justify-content: center;">
    <div class="cp-modal-overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85);"></div>
    <div class="cp-modal-content" style="position: relative; width: 420px; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 20px 60px rgba(0,0,0,0.5);">
        <h2 style="margin-top:0; font-size: 20px;"><?php _e( 'Required: Change Intent', 'changeproof' ); ?></h2>
        <p style="color: #666;"><?php _e( 'Describe why you are making this change to proceed.', 'changeproof' ); ?></p>
        
        <textarea id="cp-intent-reason" style="width:100%; height: 120px; margin: 15px 0; border: 1px solid #ddd; padding: 12px; font-size: 14px; border-radius: 4px;" placeholder="e.g. Updating product pricing for Winter sale"></textarea>
        
        <div style="display: flex; justify-content: flex-end; gap: 12px;">
            <button type="button" id="cp-cancel-intent" class="button button-large" style="height: 40px;"><?php _e( 'Cancel', 'changeproof' ); ?></button>
            <button type="button" id="cp-confirm-intent" class="button button-primary button-large" style="height: 40px;"><?php _e( 'Confirm & Save', 'changeproof' ); ?></button>
        </div>
    </div>
</div>