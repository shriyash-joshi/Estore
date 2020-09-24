<?php if(!is_user_logged_in()) : ?>
    <?php 
        $lost_password = isset($_POST['erf_lost_password']) ? true : false;
        if($lost_password) {
            do_action('erf_lost_password'); 
        } 
    ?>
    <div class="erf-password-lost-container <?php echo 'erf-layout'.$attr['layout'].' erf-style-'.$attr['field_style'].' erf-label-'.$attr['label_position']; ?>"  style="display:none">

                    <h3><?php _e( 'Forgot Your Password?', 'erforms' ); ?></h3>
                <p>
                    <?php
                        _e("Enter your email address",'erforms');
                    ?>
                </p>
                <form id="lostpasswordform" class="erf-form erf-reset-password-form" action="" method="post" onkeypress="return event.keyCode != 13;">
                    <div class="fb-text form-group">
                        <input type="email" name="user_login" class="form-control" id="erf_user_login">
                    </div>
                    <div class="erf-error"></div>
                    <div class="fb-text form-group">
                        <button type="button" name="submit" class="lostpassword-button erf-reset-password btn btn-default">
                            <?php _e( 'Reset Password', 'erforms' );?>
                        </button>
                    </div>
                    <input type="hidden" name="action" value="erf_lost_password" />
                    <div class="erf-account-switch erf-clearfix">
                            <a class="erf-show-login" href="javascript:void(0)"><?php _e('Back to Login','erforms'); ?></a>
                    </div>
                </form>
    </div>
    
    <div class="erf-otp-container <?php echo 'erf-layout'.$attr['layout'].' erf-style-'.$attr['field_style'].' erf-label-'.$attr['label_position']; ?>"  style="display:none">
        <div class="erf-message"></div>
        <h3><?php _e( 'Forgot Your Password?', 'erforms' ); ?></h3>
        <p><?php _e("Enter OTP",'erforms'); ?></p>
        <form id="otpform" class="erf-form erf-otp-form" action="" method="post" onkeypress="return event.keyCode != 13;">
            <div class="fb-text form-group">
                <input type="text" name="user_otp" class="form-control" id="erf_user_otp">
            </div>
            <div class="erf-error"></div>
            <div class="fb-text form-group">
                <button type="button" name="submit" class="otp-button erf-otp btn btn-default">
                    <?php _e( 'Submit', 'erforms' );?>
                </button>
            </div>
            <input type="hidden" name="action" value="erf_otp" />
            <div class="erf-account-switch erf-clearfix">
                <a class="erf-show-login" href="javascript:void(0)"><?php _e('Back to Login','erforms'); ?></a>
            </div>
        </form>
    </div>

    <div class="erf-password-update-container <?php echo 'erf-layout'.$attr['layout'].' erf-style-'.$attr['field_style'].' erf-label-'.$attr['label_position']; ?>"  style="display:none">
        <h3><?php _e( 'Forgot Your Password?', 'erforms' ); ?></h3>        
        <p><?php _e("Enter a new password below",'erforms'); ?></p>
        <form id="updatepasswordform" class="erf-form erf-update-password-form" action="" method="post" onkeypress="return event.keyCode != 13;">
            <div class="fb-text form-group">
                <label for="user_password" class="fb-text-label"><?php _e( 'New Password', 'erforms' ); ?></label>
                <input type="password" name="user_password" class="form-control" id="erf_user_password">
            </div>
            <div class="fb-text form-group">
                <label for="user_cpassword" class="fb-text-label"><?php _e( 'Re-enter New Password', 'erforms' ); ?></label>
                <input type="password" name="user_cpassword" class="form-control" id="erf_user_cpassword">
            </div>
            <div class="erf-error"></div>
            <div class="fb-text form-group">
                <button type="button" name="submit" class="updatepassword-button erf-update-password btn btn-default">
                    <?php _e( 'Update Password', 'erforms' );?>
                </button>
            </div>
            <input type="hidden" name="action" value="erf_update_password" />
            <div class="erf-account-switch erf-clearfix">
                <a class="erf-show-login" href="javascript:void(0)"><?php _e('Back to Login','erforms'); ?></a>
            </div>
        </form>
    </div>
<?php endif; ?>
