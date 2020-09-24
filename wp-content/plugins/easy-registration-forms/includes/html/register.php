<?php
$captcha_enabled = $form['recaptcha_enabled'];
$label_position = $form['label_position'];
$layout = $form['layout'];
$plan_model = erforms()->plan;
$sub_id = isset($_GET['sub_id']) ? absint($_GET['sub_id']) : 0;
$show_form = true;

// Checking what form to display first if Login Form enabled.
$reg_form_active = true;
$login_form_enabled = (!is_user_logged_in() && !empty($form['enable_login_form'])) ? true : false;
if (!empty($login_form_enabled)) {
    $reg_form_active = false;
    if (!empty($form['show_before_login_form'])) {
        $reg_form_active = true;
    }
}
$side_by_side = false;
if (!empty($login_form_enabled) && !empty($form['login_and_register'])) {
    $side_by_side = true;
    $reg_form_active = true;
}
?>
<div id="erf_form_container_<?php echo $form['id']; ?>" class="erf-container erf-label-<?php echo $label_position ?> erf-layout-<?php echo $layout ?> erf-style-<?php echo $form['field_style']; ?> <?php echo $side_by_side ? 'erf-login-register-form' : ''; ?> <?php echo!empty($form['login_and_register']) ? 'erf-login-register' : ''; ?>">
    <?php
    if ($side_by_side) {
        echo do_shortcode('[erforms_login form_id="' . $form['id'] . '"]');
    }
    ?>
    <div class="erf-reg-form-container" style="<?php echo empty($reg_form_active) ? 'display:none;' : ''; ?>">
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
                    echo '<div class="erf-error-row erf-error form-group">' . $error[1] . '</div>';
                }
                $show_form = false;
            }


            if (empty($form['allow_re_register']) && is_user_logged_in()) {
                _e('You are not allowed to submit the form as you are already registered.', 'erforms');
                $show_form = false;
            }
            ?> 
            <?php if ($show_form): ?>
                <div class="erf-content-above">
                    <?php echo apply_filters('erf_filter_before_form_msg',$form['before_form'],$form); ?>
                </div>
                <form method="post" enctype="multipart/form-data" class="erf-form erf-front-form" data-parsley-validate="" novalidate="true" autocomplete="off" data-erf-submission-id="<?php echo esc_attr($sub_id); ?>" data-erf-form-id="<?php echo esc_attr($form['id']); ?>">
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
                                <input type='checkbox' value="1" name='opt_in' <?php echo!empty($form['opt_default_state']) ? 'checked' : ''; ?> />
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

                    <input type="hidden" name="erform_id" value="<?php echo esc_attr($form['id']); ?>" />
                    <?php wp_nonce_field('erform_submission_nonce', 'erform_submission_nonce' ); ?>
                    <input type="hidden" name="action" value="erf_submit_form" />
                    <input type="hidden" name="redirect_to" id="erform_redirect_to" />

                    <?php if (is_user_logged_in()): ?>
                        <input type="hidden" name="erf_user" value="<?php echo get_current_user_id(); ?>" />
                    <?php endif; ?>

                    <?php if (!is_user_logged_in() && !empty($form['enable_login_form']) && empty($side_by_side)) : ?>
                        <div class="erf-account-switch erf-clearfix">
                            <a class="erf-show-login" href="javascript:void(0)"><?php _e('Already have an account?', 'erforms'); ?></a>
                        </div>
                    <?php endif; ?>


                </form>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <?php
    if (!is_user_logged_in() && !empty($form['enable_login_form']) && empty($side_by_side)) {
        if ($reg_form_active) {
            echo do_shortcode('[erforms_login hide="1" form_id="' . $form['id'] . '"]');
        } else {
            echo do_shortcode('[erforms_login form_id="' . $form['id'] . '"]');
        }
    }
    ?>

</div>
