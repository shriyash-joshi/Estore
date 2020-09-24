<?php
$captcha_enabled = $form['recaptcha_enabled'];
$label_position = $form['label_position'];
$layout = $form['layout'];
$sub_id = isset($_GET['sub_id']) ? absint($_GET['sub_id']) : 0;
$show_form = true;
?>
<div id="erf_form_container_<?php echo $form['id']; ?>" class="erf-container erf-contact erf-label-<?php echo $label_position ?> erf-layout-<?php echo $layout ?> erf-style-<?php echo $form['field_style']; ?>">
    <?php include('layout_options.php'); ?>
    <?php if ($success) : ?>
        <div class="erf-success">
            <?php echo $form['success_msg']; ?>
        </div> 
    <?php else: ?>

        <?php
        // Before output hook.
        $errors = apply_filters('erforms_before_form_processing', array(), $form);
        if (!empty($errors) && is_array($errors)) {
            foreach ($errors as $error) {
                echo $error[1] . '<br>';
            }
            $show_form = false;
        }

        if (!empty($form['allow_only_registered']) && !is_user_logged_in()) {
            _e('Only authenticated users are allowed to submit the form. If you are already registered, Please login and try again.', 'erforms');
            $show_form = false;
        }
        ?> 
        <?php if ($show_form): ?>
            <div class="erf-content-above">
                <?php echo apply_filters('erf_filter_before_form_msg',$form['before_form'],$form); ?>
            </div>
            <form method="post" enctype="multipart/form-data" class="erf-form erf-front-form" data-parsley-validate="" novalidate="true" data-erf-submission-id="<?php echo esc_attr($sub_id); ?>" data-erf-form-id="<?php echo esc_attr($form['id']); ?>">
                <div class="erf-form-html" id="erf_form_<?php echo $id; ?>">
                    <div class="rendered-form">
                        <?php echo $form_html; ?>
                    </div> 
                </div>    

                <div class="erf-external-form-elements">
                    <?php do_action('erf_before_submit_btn', $form); ?>

                    <?php
                    if (!empty($form['plan_enabled']) && !empty($this->options['payment_methods']) && empty($sub_id)) {
                        if (!empty($form['plans']['enabled'])) {
                            include('payment_part.php');
                        }
                    }
                    ?>

                    <?php if (!empty($form['opt_in']) && empty($sub_id) && erforms_show_opt_in()): ?>
                        <!-- Opt in checkbox -->
                        <div class="form-group">
                            <input type='checkbox' name='opt_in' <?php echo!empty($form['opt_default_state']) ? 'checked' : ''; ?> />
                            <?php echo $form['opt_text']; ?>
                        </div>
                        <!-- Opt in ends here --> 
                    <?php endif; ?>


                    <?php do_action('erforms_form_end', $form); ?>

                    <?php if (!is_user_logged_in() && !empty($this->options['recaptcha_configured']) && !empty($this->options['rc_site_key']) && $captcha_enabled) : ?>
                        <!-- Show reCaptcha if configured -->
                            <?php if($this->options['recaptcha_version']==2): ?>
                                <div class="g-recaptcha erf-recaptcha clearfix" data-sitekey="<?php echo esc_attr($this->options['rc_site_key']); ?>"></div>
                            <?php else: ?>    
                                <div class="g-recaptcha erf-recaptcha clearfix"></div>
                            <?php endif; ?>    
                        <!-- reCaptcha ends here -->
                    <?php endif; ?>
                    <div class="erf-errors" style="<?php echo !empty($this->errors) ? '' : 'display:none' ?>">
                        <span class="erf-errors-head erf-error-row"><?php _e('Error occured. Please confirm your data and submit again:','erforms') ?></span>
                        <div class="erf-errors-body">
                            <?php foreach ($this->errors as $error) : ?>
                                <div class='erf-error-row'><?php echo $error[1]; ?></div>
                            <?php endforeach; ?>
                        </div> 
                    </div>

                </div>
                <!-- Contains multipage Next,Previous buttons -->
                <div class="erf-form-nav clearfix"></div> 

                <!-- Single page form button -->
                <div class="erf-submit-button clearfix"></div>


                <input type="hidden" name="erform_id" value="<?php echo $form['id']; ?>" />
                <?php wp_nonce_field('erform_submission_nonce', 'erform_submission_nonce' ); ?>
                <input type="hidden" name="action" value="erf_submit_form" />

            </form>
        <?php endif; ?>
    <?php endif; ?>

</div>
