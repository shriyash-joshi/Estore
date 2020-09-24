<?php wp_enqueue_script('jquery'); ?>
<div>
    <?php echo do_shortcode($form['user_acc_verification_msg']); ?>
</div>
<?php 
    $auto_login = $form['auto_login_after_ver'];
    if (!empty($auto_login) && !is_user_logged_in()):
    ?>
    <div class="erf_auto_login_message">
        <?php _e('We are trying to log you in. Please wait....','erforms'); ?>
    </div>
    <script>
        jQuery(document).ready(function(){
            $= jQuery;
            var ajax_url= "<?php echo admin_url('admin-ajax.php'); ?>";
            var data= {action: 'erf_ajax_log_in',type: 'user_hash', value: "<?php echo $hash; ?>",'redirect_to':  document.location.href};
            $.post(ajax_url, data, function(response) {
		if(response.success && response.data.hasOwnProperty('redirect_to')){
                    window.location.href= response.data.redirect_to;
                }
                else
                {
                    $(".erf_auto_login_message").html("<?php _e('Unable to login.','erforms'); ?>");
                }
            }).fail(function(xhr, textStatus, e){
                $(".erf_auto_login_message").html("<?php _e('Unable to login.','erforms'); ?>");
            });
        });
    </script>
    <?php endif; ?>