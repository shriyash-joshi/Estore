<?php
global $wdmPluginDataScheduler;
wp_register_style($wdmPluginDataScheduler['pluginSlug'] .'-feedback', plugins_url('css/feedback-page.css', dirname(dirname(__FILE__))), array(), $wdmPluginDataScheduler['pluginVersion']);
// Enqueue admin styles
wp_enqueue_style($wdmPluginDataScheduler['pluginSlug'] .'-feedback');
?>
<script src="https://embed.typeform.com/embed.js" type="text/javascript"></script>

<!-- Container for the typeform -->
<div id="feedbackFormContainer">    
</div>

<script type="text/javascript">
window.addEventListener("DOMContentLoaded", function() {
    const embedElement = document.querySelector('#feedbackFormContainer')
    typeformEmbed.makeWidget(embedElement, 'https://hardik60.typeform.com/to/zFfGgb',
  {
    hideHeaders: true,
    hideFooter: true,
    opacity: 0 ,
    onSubmit: function () {
    // To be replaced by the ajax call to save feedback status
    jQuery.ajax({
                type: 'POST',
                url: ajaxurl,
                data:{
                    action:'wdmws_set_feedback_status',
                    security: $('#_wpnonce').val(),
                    wdmFeedbackStatus:'submitted'
                },
                success: function ( response ) {
                    //location.reload(); 
                }
            });
    }
  });
});
</script>
      
<?php
