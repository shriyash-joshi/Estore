<?php
if (!empty($attr['form_id'])) {
    $form = erforms()->form->get_form($attr['form_id']);
    if (!empty($form)) {
        $attr['label_position'] = $form['label_position'];
        $attr['field_style'] = $form['field_style'];
        $attr['layout'] = $form['layout'];
    }
}
$options = erforms()->options->get_options();
if ($options['allow_login_from'] == 'username') {
    $username_label = __('Username', 'erforms');
} elseif ($options['allow_login_from'] == 'email') {
    $username_label = __('Email', 'erforms');
} else {
    $username_label = __('Username/Email', 'erforms');
}
?>
<div class="<?php echo empty($attr['form_id']) ? 'erf-container' : ''; ?>">
    <div class="erf-login-container erf-label-<?php echo $attr['label_position']; ?> erf-style-<?php echo $attr['field_style']; ?> erf-layout-<?php echo $attr['layout']; ?>" style="<?php echo !empty($attr['hide']) ? 'display:none' : ''; ?>">     
        <div class="erf-message"></div>

        <?php if (!is_user_logged_in()) : ?>
            <form action="" method="post" class="erf-login-form erf-form">
                <div class="fb-text form-group">
                    <label for="erf_username" class="fb-text-label">
                        <?php echo $username_label; ?><span class="erf-required">*</span>
                    </label>

                    <input required="" placeholder="<?php echo $attr['label_position'] == 'no-label' ? $username_label : ''; ?>" value="" type="text" class="form-control" id="erf_username" name="erf_username">
                </div>

                <div class="fb-text form-group">
                    <label for="erf_password" class="fb-text-label">
                        <?php _e('Password', 'erforms') ?><span class="erf-required">*</span>
                    </label>

                    <input placeholder="<?php echo $attr['label_position'] == 'no-label' ? __('Password', 'erforms') : ''; ?>" type="password" value="" required="" class="form-control" id="erf_password" name="erf_password">
                </div>

                <div class="fb-text form-group">

                    <label for="rememberme" class="fb-text-label">
                        <input name="rememberme" <?php echo isset($_POST['rememberme']) ? 'checked' : ''; ?> type="checkbox" id="erf_rememberme" value="forever">
                        <?php _e('Remember', 'erforms') ?>
                    </label>
                </div>


                <input type="hidden" name="action" value="erf_login_user"  />
                <input type="hidden" name="erf_login_nonce" id="erf_login_nonce" value="<?php echo wp_create_nonce('erf_login_nonce'); ?>" />

                <div class="erf-before-login-btn">
                    <?php do_action('erforms_before_login_button'); ?>
                </div>   

                <div class="erf-external-form-elements">
                    <?php if (erforms_login_captcha_enabled()) : ?>
                        <!-- Show reCaptcha if configured -->
                        <?php if($this->options['recaptcha_version']==2): ?>
                            <div class="g-recaptcha erf-recaptcha clearfix" data-sitekey="<?php echo esc_attr($this->options['rc_site_key']); ?>"></div>
                        <?php else: ?>
                            <div class="g-recaptcha erf-recaptcha clearfix"></div>
                        <?php endif; ?>    
                        <!-- reCaptcha ends here -->
                    <?php endif; ?>
                </div>   
                <div class="erf-error"></div>
                <div class="erf-submit-button erf-clearfix">    
                    <div class="fb-button form-group">
                        <button type="submit" class="btn btn-default" style="default"><?php _e('Login', 'erforms') ?></button>
                    </div>
                </div>
                <?php if (!empty($attr['form_id']) && empty($form['login_and_register'])) : ?>
                    <div class="erf-account-switch">
                        <a class="erf-show-register" href="javascript:void(0)"><?php _e('Register', 'erforms') ?></a>
                        <a class="erf-show-lost-password"  href="javascript:void(0)" title="<?php _e('Lost/Forgot Password?', 'erforms') ?>"><?php _e('Lost Password?', 'erforms') ?></a>
                    </div>
                <?php else: ?>
                    <div class="erf-account-switch">
                        <a class="erf-show-lost-password"  href="javascript:void(0)" title="<?php _e('Lost/Forgot Password?', 'erforms') ?>"><?php _e('Lost Password?', 'erforms') ?></a>
                    </div>
                <?php endif; ?>

            </form>

            <form id="erf_login_reload_form" method="POST">

            </form>

        <?php else: ?>
            <div>   
                <?php _e('You are already logged in.', 'erforms') ?><br><br>
                <button onClick="location.href = '<?php echo erforms_logout_url(); ?>'" class="btn btn-default"><?php _e('Logout', 'erforms'); ?></button>
            </div>
        <?php endif; ?>
    </div>
    <?php
    /*
     * 1. If Login widget is not configured within Registration Form.
     * 2. If Login widget is configured to appear together with Registeration Form
     */

    if (empty($attr['form_id']) || (!empty($form)) && !empty($form['login_and_register']))
        include 'lost_password.php';
    ?>
</div>    
<?php
/*
 * 1. If Login widget is configured within Registration Form.
 * 2. If Login widget is configured not to appear together with Registeration Form
 */
if (!empty($attr['form_id']) && (!empty($form)) && empty($form['login_and_register']))
    include 'lost_password.php';