<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div id="cp-intent-modal" style="display:none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 99999999;">
    <div class="cp-modal-overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8);"></div>
    <div class="cp-modal-content" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 400px; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 10px 50px rgba(0,0,0,0.5); font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen-Sans, Ubuntu, Cantarell, 'Helvetica Neue', sans-serif;">
        <h2 style="margin-top:0;"><?php _e( 'Change Intent', 'changeproof' ); ?></h2>
        <p><?php _e( 'Provide a reason to unlock saving:', 'changeproof' ); ?></p>
        
        <textarea id="cp-intent-reason" style="width:100%; height: 100px; margin-bottom: 20px; border: 1px solid #ccc; padding: 10px;" placeholder="What did you change?"></textarea>
        
        <div style="display: flex; justify-content: flex-end; gap: 10px;">
            <button type="button" id="cp-cancel-intent" class="button button-large" style="cursor: pointer;"><?php _e( 'Cancel', 'changeproof' ); ?></button>
            <button type="button" id="cp-confirm-intent" class="button button-primary button-large" style="cursor: pointer;"><?php _e( 'Confirm & Save', 'changeproof' ); ?></button>
        </div>
    </div>
</div>