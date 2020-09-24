<div class="erf-wrapper erforms-settings wrap">
    <div class="erf-page-title">
        <h1 class="wp-heading-inline">
            <?php echo $menus[$tab]['label']; ?>
        </h1>
    </div>
    <div class="erforms-admin-content">
        <form method="POST">
            <fieldset>
                <div style="<?php echo $tab == 'general' ? '' : 'display:none' ?>">
                    <div class="erf-row">
                        <div class="erf-control-label">
                            <label><?php echo __('Default Registration Page', 'erforms'); ?></label>
                        </div>
                        <div class="erf-control">
                            <?php wp_dropdown_pages(array('selected' => $options['default_register_url'], 'show_option_none' => 'Select Page', 'option_none_value' => 0, 'name' => 'default_register_url', 'class' => 'erf-input-field')); ?>
                            <p class="description"><?php _e('Replaces default WordPress registration page.', 'erforms') ?></p>
                        </div>  
                    </div>

                    <div class="erf-row">
                        <div class="erf-control-label">
                            <label><?php echo __('Default Upload Directory', 'erforms'); ?></label>
                        </div>
                        <div class="erf-control">
                            <input pattern="^[A-Za-z-_]+$" type="text" class="erf-input-field" name="upload_dir" value="<?php echo esc_attr($options['upload_dir']); ?>" />
                            <p class="description"><?php _e('Upload directory name where all the file uploads will take place. Please do not use any special characters in directory name. (Only Alphabets,Hyphens(-) and Underscores(_) allowed.)', 'erforms') ?></p>
                        </div>  
                    </div>

                    <?php do_action('erf_settings_general', $options, $tab); ?> 
                </div>

                <div style="<?php echo $tab == 'user_login' ? '' : 'display:none' ?>">
                    <div class="erf-row">
                        <div class="erf-control-label">
                            <label><?php _e('Form Layout', 'erforms'); ?></label>
                        </div>
                        <div class="erf-control">
                            <select name="login_layout" class="erf-input-field">
                                <option <?php echo $options['login_layout'] == "one-column" ? 'selected' : ''; ?> value="one-column"><?php _e('One Column', 'erforms'); ?></option>
                                <option <?php echo $options['login_layout'] == "two-column" ? 'selected' : ''; ?> value="two-column"><?php _e('Two Column', 'erforms'); ?></option>
                            </select>    
                            <p class="description"><?php _e('Login Form layout.', 'erforms'); ?></p>
                        </div>   
                    </div>

                    <div class="erf-row">
                        <div class="erf-control-label">
                            <label><?php _e('Field Style', 'erforms'); ?></label>
                        </div>
                        <div class="erf-control">
                            <select name="login_field_style" class="erf-input-field">
                                <option <?php echo $options['login_field_style'] == "flat" ? 'selected' : ''; ?> value="flat"><?php _e('Flat', 'erforms'); ?></option>
                                <option <?php echo $options['login_field_style'] == "rounded" ? 'selected' : ''; ?> value="rounded"><?php _e('Rounded', 'erforms'); ?></option>
                                <option <?php echo $options['login_field_style'] == "rounded-corner" ? 'selected' : ''; ?> value="rounded-corner"><?php _e('Rounded Corner', 'erforms'); ?></option>
                                <option <?php echo $options['login_field_style'] == "border-bottom" ? 'selected' : ''; ?> value="border-bottom"><?php _e('Border Bottom', 'erforms'); ?></option>
                            </select> 
                            <p class="description"><?php _e('Field style for login form fields.', 'erforms'); ?></p>
                        </div> 
                    </div>

                    <div class="erf-row">
                        <div class="erf-control-label">
                            <label><?php _e('Label Position', 'erforms'); ?></label>
                        </div>
                        <div class="erf-control">
                            <select name="login_label_position" class="erf-input-field">
                                <option <?php echo $options['login_label_position'] == "top" ? 'selected' : ''; ?> value="top"><?php _e('Top', 'erforms'); ?></option>
                                <option <?php echo $options['login_label_position'] == "inline" ? 'selected' : ''; ?> value="inline"><?php _e('Inline', 'erforms'); ?></option>
                                <option <?php echo $options['login_label_position'] == "no-label" ? 'selected' : ''; ?> value="no-label"><?php _e('No Label', 'erforms'); ?></option>
                            </select>  
                            <p class="description"><?php _e('Label position for login form fields.', 'erforms'); ?></p>
                        </div>  
                    </div>

                    <div class="erf-row">
                        <div class="erf-control-label">
                            <label><?php _e('Hide WordPress admin bar', 'erforms'); ?></label>
                        </div>
                        <div class="erf-control">
                            <input class="erf_toggle" type="checkbox" name="hide_admin_bar" value="1" <?php echo empty($options['hide_admin_bar']) ? '' : 'checked'; ?>/>
                            <label></label>
                            <p class="description"><?php _e('Hides top WordPress admin bar for logged in front end users.', 'erforms') ?></p>
                        </div>  
                    </div>

                    <div class="erf-row">
                        <div class="erf-control-label">
                            <label><?php _e('Allow login from', 'erforms'); ?></label>
                        </div>
                        <div class="erf-control">
                            <select name="allow_login_from" class="erf-input-field">
                                <option <?php echo $options['allow_login_from'] == 'username' ? 'selected' : ''; ?> value="username"><?php _e('Username', 'erforms'); ?></option>
                                <option <?php echo $options['allow_login_from'] == 'email' ? 'selected' : ''; ?> value="email"><?php _e('Email', 'erforms'); ?></option>
                                <option <?php echo $options['allow_login_from'] == 'both' ? 'selected' : ''; ?> value="both"><?php _e('Username and Email', 'erforms'); ?></option>
                            </select>  
                            <p class="description"><?php _e("Allows to login from Email,Username or both.", 'erforms') ?></p>
                        </div>  
                    </div>

                    <div class="erf-row">
                        <div class="erf-control-label">
                            <label><?php _e('Social Login Shortcode', 'erforms'); ?></label>
                        </div>
                        <div class="erf-control">
                            <textarea name="social_login" class="erf-input-field"><?php echo $options['social_login']; ?></textarea>
                            <p class="description"><?php _e("Place any content (including shortcodes) after login button. Useful in case you want to integrate any other plugin's functionality with ERF Login. For example: You can use a Social Login plugin and can place the shortcode here.", 'erforms'); ?></p>
                        </div>  
                    </div>
                    
                    <?php if (!empty($options['recaptcha_configured'])) : ?>
                    <div class="erf-row">
                        <div class="erf-control-label">
                            <label><?php _e('Enable reCaptcha', 'erforms'); ?></label>
                        </div>
                        <div class="erf-control">
                            <input class="erf_toggle" type="checkbox" name="en_login_recaptcha" value="1" <?php echo empty($options['en_login_recaptcha']) ? '' : 'checked'; ?>/>
                            <label></label>
                            <p class="description"><?php _e("It helps protect websites from spam and abuse. A “CAPTCHA” is a test to tell human and bots apart. Make sure recaptcha is configured in <b>Global Settings->External Integration->Configure Google reCaptcha</b>", 'erforms'); ?></p>
                        </div>  
                    </div>
                    <?php endif; ?>
                    
                    <div class="erf-row">
                        <div class="erf-control-label">
                            <label><?php _e('After Login Redirection', 'erforms'); ?></label>
                        </div>
                        <div class="erf-control">
                            <input type="url" class="erf-input-field" name="after_login_redirect_url" value="<?php echo esc_url($options['after_login_redirect_url']) ?>" />
                            <p class="description"><?php _e('URL of the page where user will be redirected after login to WordPress. This value will be overriden in case below role based redirection is enabled and configured.', 'erforms') ?></p>
                        </div>  
                    </div>

                    <div class="erf-row">
                        <div class="erf-control-label">
                            <label><?php _e('Role Based Login Redirection', 'erforms'); ?></label>
                        </div>
                        <div class="erf-control erf-has-child">
                            <input class="erf_toggle" class="erf-input-field" type="checkbox" name="en_role_redirection" value="1" <?php echo empty($options['en_role_redirection']) ? '' : 'checked'; ?>/>
                            <label></label>
                            <p class="description"><?php _e('Enable login redirection per role.', 'erforms') ?></p>
                        </div>  
                    </div>

                    <div class="erf-child-rows" style="<?php echo !empty($options['en_role_redirection']) ? '' : 'display:none'; ?>">
                        <?php $roles = erforms_wp_roles(); ?>
                        <?php foreach ($roles as $key => $role): ?>
                            <div class="erf-row">
                                <div class="erf-control-label">
                                    <label><?php echo $role['name']; ?></label>
                                </div>
                                <div class="erf-control">
                                    <input type="text" class="erf-input-field" value="<?php echo isset($options[$key . '_login_redirection']) ? esc_attr($options[$key . '_login_redirection']) : '' ?>" name="<?php echo $key ?>_login_redirection" />
                                    <p class="description"><?php echo $role['name'] . __(' will be redirected to this URL after login.', 'erforms') ?></p>
                                </div>  
                            </div>               
                        <?php endforeach; ?>
                    </div>

                    <div class="erf-row">
                        <div class="erf-control-label">
                            <label><?php _e('Logout Redirection', 'erforms'); ?></label>
                        </div>
                        <div class="erf-control erf-has-child">
                            <input type="text" class="erf-input-field" value="<?php echo esc_url($options['logout_redirection']); ?>" name="logout_redirection" />
                            <p class="description"><?php _e('Page URL where user will be directed after logout. Leave empty for WordPress default behaviour.', 'erforms'); ?></p>
                        </div>  
                    </div>

                    <?php do_action('erf_settings_user_login', $options, $tab); ?> 
                </div>

                <div style="<?php echo $tab == 'external' ? '' : 'display:none' ?>">
                    <div class="erf-row <?php echo erforms_is_woocommerce_activated() ? '' : 'erf-disabled'; ?>">
                        <div class="erf-control-label">
                            <label><?php _e('WooCommerce My Account Integration', 'erforms'); ?></label>
                        </div>
                        <div class="erf-control">
                            <input class="erf_toggle" type="checkbox" name="en_wc_my_account" value="1" <?php echo empty($options['en_wc_my_account']) ? '' : 'checked'; ?>/>
                            <label></label>
                            <p class="description"><?php _e('Adds a <b>Submissions</b> link in WooCommerce <b>My Account</b> area. Please resave Permalinks from WordPress settings.', 'erforms') ?></p>
                        </div>  
                    </div>
                    
                    <div class="erf-row" id="erf_recaptcha">
                        <div class="erf-control-label">
                            <label><?php _e('Configure Google Recaptcha', 'erforms'); ?></label>
                        </div>
                        <div class="erf-control erf-has-child">
                            <input class="erf_toggle" type="checkbox" data-has-child="1" data-erf-child="erf_settings_recaptcha_options" id="erf_settings_recaptcha_configured" name="recaptcha_configured" value="1" <?php echo empty($options['recaptcha_configured']) ? '' : 'checked'; ?>/>
                            <label></label>
                            <p class="description"><?php _e('It helps protect websites from spam and abuse. A “CAPTCHA” is a test to tell human and bots apart. Also make sure to enable Recapctha setting in Form->Configure->General Settings.', 'erforms') ?></p>
                        </div>  
                    </div>
                    
                    
                    <div class="erf-child-rows" style="<?php echo !empty($options['recaptcha_configured']) ? '' : 'display:none'; ?>"> 
                        <div class="erf-row">
                            <div class="erf-control-label">
                                <label><?php _e('Version', 'erforms'); ?></label>
                            </div>
                            <div class="erf-control">
                                <select name="recaptcha_version" class="erf-input-field">
                                    <option <?php echo $options['recaptcha_version'] == "2" ? 'selected' : ''; ?> value="2"><?php _e('reCpatcha v2', 'erforms'); ?></option>
                                    <option <?php echo $options['recaptcha_version'] == "3" ? 'selected' : ''; ?> value="3"><?php _e('reCpatcha v3', 'erforms'); ?></option>
                                </select>
                                <p class="description"><?php _e('Mandatory for reCAPTCHA. For more details, <a href="https://www.google.com/recaptcha/intro/index.html">Click Here</a>', 'erforms') ?></p>
                            </div>  
                        </div>
                        
                        <div class="erf-row">
                            <div class="erf-control-label">
                                <label><?php _e('Site Key', 'erforms'); ?></label>
                            </div>
                            <div class="erf-control">
                                <input type="text" class="erf-input-field" name="rc_site_key" value="<?php echo esc_attr($options['rc_site_key']); ?>"/>
                                <p class="description"><?php _e('Mandatory for reCAPTCHA. For more details, <a href="https://www.google.com/recaptcha/intro/index.html">Click Here</a>', 'erforms') ?></p>
                            </div>  
                        </div>

                        <div class="erf-row">
                            <div class="erf-control-label">
                                <label><?php _e('Secret Key', 'erforms'); ?></label>
                            </div>
                            <div class="erf-control">
                                <input type="text" class="erf-input-field" name="rc_secret_key" value="<?php echo esc_attr($options['rc_secret_key']); ?>"/>
                                <p class="description"><?php _e('Mandatory for reCAPTCHA. For more details, <a href="https://www.google.com/recaptcha/intro/index.html">Click Here</a>', 'erforms') ?></p>
                            </div>  
                        </div>
                    </div>
                    <?php 
                        $extensions = erforms()->extensions; 
                        if(in_array('views', $extensions)):
                    ?>
                        <div class="erf-row">
                            <div class="erf-control-label">
                                <label><?php _e('Google Map API Key', 'erforms'); ?></label>
                            </div>
                            <div class="erf-control">
                                <input type="text" class="erf-input-field" name="gmap_api" value="<?php echo esc_attr($options['gmap_api']); ?>"/>
                                <p class="description"><?php _e('Mandatory to use Google Map field inside views.', 'erforms') ?></p>
                            </div>  
                        </div>
                    <?php endif; ?>
                    
                    <?php do_action('erf_settings_external', $options); ?> 
                </div>

                <?php do_action('erf_global_settings', $options, $tab); ?> 

                <div style="<?php echo $tab == 'payments' ? '' : 'display:none' ?>">
                    <div class="erf-row">
                        <div class="erf-control-label">
                            <label><?php _e('Currency', 'erforms'); ?></label>
                        </div>
                        <div class="erf-control">
                            <?php $currencies = erforms_currencies(); ?>
                            <select name="currency" class="erf-input-field">
                                <?php foreach ($currencies as $code => $name): ?>
                                    <?php if ($options['currency'] == $code): ?>
                                        <option selected value="<?php echo esc_attr($code); ?>"><?php echo $name . erforms_currency_symbol($code); ?></option>
                                    <?php else: ?>
                                        <option value="<?php echo esc_attr($code); ?>"><?php echo $name . erforms_currency_symbol($code); ?></option>
                                    <?php endif; ?>    
                                <?php endforeach; ?>
                            </select>    
                        </div>  
                    </div>

                    <?php do_action('erf_settings_payment', $options); ?>
                </div>

                <div style="<?php echo $tab == 'notifications' ? '' : 'display:none' ?>">
                    <?php include('notifications.php'); ?>
                </div>

                <input type="hidden" name="erf_save_settings" />
                <?php $type = !empty($_REQUEST['type']) ? sanitize_text_field($_REQUEST['type']) : ''; ?>
                <div style="<?php echo $tab == 'notifications' && empty($type) ? 'display:none' : '' ?>">
                    <p class="submit">
                        <input type="submit" class="button button-primary" value="<?php _e('Save', 'erforms'); ?>" name="save" /> 
                        <input type="submit" class="button button-primary" value="<?php _e('Save & Close', 'erforms'); ?>" name="savec" /> 
                    </p>
                </div>
            </fieldset>
        </form>
    </div>
</div>
