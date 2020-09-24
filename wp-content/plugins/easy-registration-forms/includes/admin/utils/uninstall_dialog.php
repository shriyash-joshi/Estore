<?php 
    wp_enqueue_script('jquery');
    wp_enqueue_script('erf-util-functions', ERFORMS_PLUGIN_URL . 'assets/js/utility-functions.js');
    wp_enqueue_script ('erf-admin' ,ERFORMS_PLUGIN_URL.'assets/admin/js/admin.js'); 

    wp_enqueue_style('erf-admin-style');
?>
<script type="text/javascript">
    jQuery(document).ready(function () {

        var deactivationLink;
        // Shows feedback dialog     
        jQuery('#the-list').find('[data-slug="easy-registration-forms"] span.deactivate a').click(function (event) {
            deactivationLink= jQuery(this).attr('href');
            jQuery("#erf_uninstall_feedback_dialog").show();
            event.preventDefault();
        });

        jQuery(".erf-submit-btn").click(function () {
            var selectedVal= jQuery('[name=erf_feedback_key]:checked').val();
            if(!selectedVal)
            {
                location.href= deactivationLink;
                return;
            }
            var message= '';
            var messageElement= jQuery('#erf_' + selectedVal);
            switch(selectedVal){
                case 'plugin_broke_site': message= 'Plugin broke my site. <br> Reason :'; if(messageElement.length>0) { message= message + messageElement.val();} break;
                case 'feature_not_available':  message= messageElement.length>0 ? 'Feature not available. <br>' + messageElement.val() : 'Feature not available'; break;
                case 'different_plugin': message= messageElement.length>0 ? 'Different Plugin. <br> Plugin Name :' + messageElement.val() : 'Different Plugin '; break;
                case 'other':message= messageElement.length>0 ? 'Other. <br> Reason :' + messageElement.val() : 'Other :'; break; 
            }
            
            var data = {
                'action': 'erf_send_uninstall_feedback',
                'msg': message

            };
            jQuery(this).addClass('erf-progress');
            jQuery.post(ajaxurl, data, function (response) {
                location.href= deactivationLink;
            });
            
        });

        jQuery(".erf-cancel-button,.erf-modal-close").click(function () {
            jQuery("#erf_uninstall_feedback_dialog").hide();
        });

        // Show hide child parent options
        jQuery('.erf-child-rows').slideUp();
        
        jQuery('.erf-dialog-input').change(function(){
            jQuery('.erf-child-rows').hide();
            if(jQuery(this).is(':checked')){
                var child= jQuery(this).data('child-row');
                jQuery('#' + child).slideDown();
            }
        });
    });




</script>
<div id="erf_uninstall_feedback_dialog" class="erf_dialog" style="display:none;">

    <div class="modal-content">
        <div class="modal-header"><h5><?php _e('Easy Registration Forms Feedback','erforms'); ?></h5>
            <button type="button" class="close erf_close_dialog">
                <span>×</span>
            </button>
        </div>


        <div class="modal-body">
            <div class="erf-content-row erf-content-heading">
                If you have a moment, please share why you are deactivating Easy Registration Forms
            </div>

            <div class="erf-row erf-content-row">    
                <div class="erf-control">
                    <input data-child-row="erf_feedback_broken" id="erf_fb_key_broke" class="erf-dialog-input" type="radio" name="erf_feedback_key" value="plugin_broke_site">
                    <label for="erf_fb_key_broke" class="erf-dialog-label">Plugin broke my Site.</label>
                </div>
            </div>

            <div class="erf-child-rows"  id="erf_feedback_broken">
                <div class="erf-row">
                    <input class="erf-input-field" type="text" id="erf_plugin_broke_site" name="erf_plugin_broke_site" placeholder="Issue Details">
                    <p class="description"><b>Apologies for the inconveineince caused. We request you to send the details <a href="http://www.easyregistrationforms.com/support/" target="_blank">here</a>, We will get back to you at the earliest.</b></p>
                </div>
            </div>

            <div class="erf-row erf-content-row">    
                <div class="erf-control">
                    <input data-child-row="erf_feedback_feature" id="erf_fb_key_na" class="erf-dialog-input" type="radio" name="erf_feedback_key" value="feature_not_available">
                    <label for='erf_fb_key_na' class="erf-dialog-label">Doesn't have the feature I need</label>
                </div>
            </div>

            <div class="erf-child-rows" id="erf_feedback_feature">
                <div class="erf-row">
                    <input class="erf-input-field" type="text" id="erf_feature_not_available" name="erf_feature_not_available" placeholder="Please let us know the missing feature...">
                </div>
            </div>

            <div class="erf-row erf-content-row">    
                <div class="erf-control">
                    <input data-child-row="erf_feedback_plugin" id="erf_fb_key_diferent" class="erf-dialog-input" type="radio" name="erf_feedback_key" value="different_plugin">
                    <label for="erf_fb_key_diferent" class="erf-dialog-label">Moved to a different plugin.</label>
                </div>
            </div>

            <div class="erf-child-rows" id="erf_feedback_plugin">
                <div class="erf-row">
                    <input class="erf-input-field" type="text" id="erf_different_plugin" name="erf_different_plugin" placeholder="<?php _e('Plugin Name', 'erforms'); ?>">
                </div>
            </div>

            <div class="erf-row erf-content-row">    
                <div class="erf-control">
                    <input data-child-row="erf_feedback_other" id="erf_fb_key_other" class="erf-dialog-input" type="radio" name="erf_feedback_key" value="other">
                    <label for="erf_fb_key_other" class="erf-dialog-label"><?php _e('Other', 'erforms'); ?></label>
                </div>
            </div>

            <div class="erf-child-rows" id="erf_feedback_other">
                <div class="erf-row">
                    <input class="erf-input-field" type="text" id="erf_other" name="erf_other" placeholder="<?php _e('Reason', 'erforms'); ?>">
                </div>
            </div>

        </div>
        <div class="modal-footer">
            <div class="erf-modal-button-wrap">
                <button type="button" class="erf-cancel-button button button-danger" role="button">
                    <span class="erf-modal-button-text"><?php _e('Cancel', 'erforms'); ?></span>
                </button>
                <button type="button" class="erf-submit-btn button button-primary" role="button">
                    <span class="erf-modal-button-text"><?php _e('Submit', 'erforms'); ?></span>
                </button>
            </div>
        </div>

    </div>
</div>
