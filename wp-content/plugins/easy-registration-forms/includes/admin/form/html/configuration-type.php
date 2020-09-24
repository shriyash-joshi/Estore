<?php
$auto_login = empty($form['auto_login']) ? '' : 'checked';
$recaptcha_enabled = $form['recaptcha_enabled'];
$auto_user_activation = empty($form['auto_user_activation']) ? '' : 'checked';
$en_role_choices = empty($form['en_role_choices']) ? '' : 'checked';
wp_enqueue_style('timepicker');
wp_enqueue_script('timepicker');
$fields = erforms()->form->get_fields_dropdown($form['id'], erforms_non_editable_fields());
?>
<div class="erf-form-conf-wrapper">
    <form action="" method="post" id="erf_configuration_form">
        <fieldset class="erf-config-wrap">

            <!-- General Settings -->
            <div style="<?php echo $type == 'general' ? '' : 'display:none' ?>">
                <div class="group-title">
                    <?php _e('General Settings', 'erforms'); ?>
                </div>

                <div class="group-wrap">

                    <?php if (!empty($options['recaptcha_configured'])) : ?>
                        <div class="erf-row">
                            <div class="erf-control-label">
                                <label><?php _e('Enable Recaptcha', 'erforms'); ?></label>
                            </div>
                            <div class="erf-control">
                                <input class="erf_toggle"  type="checkbox" name="recaptcha_enabled" value="1" <?php echo empty($recaptcha_enabled) ? '' : 'checked'; ?>>
                                <label></label>
                                <p class="description"><?php _e('Shows recaptcha above the submit button. Helps to protect from spam submissions.', 'erforms'); ?></p>
                            </div>  
                        </div>
                    <?php else : ?>
                        <div class="erf-row">
                            <div class="erf-control-label">
                                <label><?php _e('Enable Recaptcha', 'erforms'); ?></label>
                            </div>
                            <div class="erf-control">
                                <input class="erf_toggle"  type="checkbox" disabled="">
                                <label></label>
                                <p class="description"><?php printf(__('Recapctha can not be enabled. Please enable and configure keys from <a target="_blank" href="%s">here</a>.', 'erforms'),'?page=erforms-settings&tab=external'); ?></p>
                            </div>  
                        </div>
                    <?php endif; ?>

                    <?php if ($form['type'] == 'reg'): ?>
                        <div class="erf-row">
                            <div class="erf-control-label">
                                <label><?php _e('Enable Login Form', 'erforms'); ?></label>
                            </div>
                            <div class="erf-control erf-has-child">
                                <input class="erf_toggle"  type="checkbox" name="enable_login_form" value="1" <?php echo $form['enable_login_form'] ? 'checked' : '' ?>>
                                <label></label>
                                <p class='description'><?php _e('Shows Login Form.', 'erforms') ?></p>
                            </div>  
                        </div>
                        <div class="erf-child-rows" style="<?php echo !empty($form['enable_login_form']) ? '' : 'display:none'; ?>">
                            <div class="erf-row">
                                    <div class="erf-control-label">
                                        <label><?php _e('Show Registration Form first', 'erforms'); ?></label>
                                    </div>
                                    <div class="erf-control">
                                        <input class="erf_toggle" id="erf_show_before_login_form"  type="checkbox" name="show_before_login_form" value="1" <?php echo $form['show_before_login_form'] ? 'checked' : '' ?>>
                                        <label></label>
                                        <p class='description'><?php _e('If enabled Registration Form will be displayed followed by Login Form.', 'erforms') ?></p>
                                    </div>  
                            </div>
                            
                            <div class="erf-row">
                                <div class="erf-control-label">
                                    <label><?php _e('Show both forms together', 'erforms'); ?></label>
                                </div>
                                <div class="erf-control erf-has-child">
                                    <input class="erf_toggle"  type="checkbox" id="erf_login_and_register" name="login_and_register" value="1" <?php echo $form['login_and_register'] ? 'checked' : '' ?>>
                                    <label></label>
                                    <p class='description'><?php _e('Displays Login and Registration forms side by side.', 'erforms') ?></p>
                                </div>  
                            </div>
                            
                        </div>
                    
                    <?php endif; ?>

                    <div class="erf-row">
                        <div class="erf-control-label">
                            <label><?php _e('Unique ID', 'erforms'); ?></label>
                        </div>
                        <div class="erf-control erf-has-child">
                            <input class="erf_toggle"  type="checkbox" value="1" id="enable_unique_id" name="enable_unique_id" data-has-child="1" <?php echo $form['enable_unique_id'] == '1' ? 'checked' : ''; ?> />
                            <label></label>
                            <p class="description"><?php _e('Generates Unique token for each submission.', 'erforms') ?></p>
                        </div>  
                    </div>
                    
                    <div class="erf-child-rows" style="<?php echo !empty($form['enable_unique_id']) ? '' : 'display:none'; ?>">

                        <div class="erf-row">
                            <div class="erf-control-label">
                                <label><?php _e('Generation Method', 'erforms'); ?></label>
                            </div>
                            <div class="erf-control erf-has-child">
                                <input type="radio" checked value="auto" name="unique_id_gen_method" data-child-index="-1" <?php echo $form['unique_id_gen_method'] == 'auto' ? 'checked' : '' ?>/> Auto
                                <input type="radio" value="configure" name="unique_id_gen_method"  data-child-index="1" <?php echo $form['unique_id_gen_method'] == 'configure' ? 'checked' : '' ?> /> Configure
                            </div>  
                        </div>

                        <div class="erf-child-rows erf-dummy-child">

                        </div>

                        <div class="erf-child-rows" style="<?php echo $form['unique_id_gen_method']=='configure' ? '' : 'display:none'; ?>">
                            
                            <div class="erf-row">
                                <div class="erf-control-label">
                                    <label><?php _e('Current Index', 'erforms'); ?></label>
                                </div>
                                <div class="erf-control">
                                    <input class="erf-input-field" type="number" value="<?php echo esc_attr($form['unique_id_index']); ?>"  name="unique_id_index" />
                                </div>  
                            </div>

                            <div class="erf-row">
                                <div class="erf-control-label">
                                    <label><?php _e('Prefix', 'erforms'); ?></label>
                                </div>
                                <div class="erf-control">
                                    <input class="erf-input-field" type="text" value="<?php echo esc_attr($form['unique_id_prefix']); ?>"  min="1" name="unique_id_prefix" />
                                </div>  
                            </div>

                            <div class="erf-row">
                                <div class="erf-control-label">
                                    <label><?php _e('Number Padding', 'erforms'); ?></label>
                                </div>
                                <div class="erf-control">
                                    <input class="erf-input-field" type="number" value="<?php echo esc_attr($form['unique_id_padding']); ?>" min="0" name="unique_id_padding" />
                                    <p class="description"><?php _e('If you need your IDs to be of the same length. Leave to 0 if you don\'t want fixed length IDs.','erforms'); ?></p>
                                </div>  
                            </div>
                            
                            <div class="erf-row">
                                <div class="erf-control-label">
                                    <label><?php _e('Number Offset', 'erforms'); ?></label>
                                </div>
                                <div class="erf-control">
                                    <input class="erf-input-field" type="number" value="<?php echo esc_attr($form['unique_id_offset']); ?>" min="0" name="unique_id_offset" />
                                    <p class="description"><?php _e('Sets the interval between consecutive submissions.', 'erforms') ?></p>
                                </div>  
                            </div>
                            <div class="erf-feature-request"><p class="description"><?php _e('Looking for help setting up Unique ID, checkout our blog <a target="_blank" href="http://www.easyregistrationforms.com/add-custom-unique-ids-to-your-form-submissions-in-wordpress/">here</a>.', 'erforms') ?></p></div>
                        </div>

                    </div>
                    
                    <?php if($form['type']=='contact'): ?>
                        <div class="erf-row">
                            <div class="erf-control-label">
                                <label><?php _e('Primary Email Field', 'erforms'); ?></label>
                            </div>
                            <div class="erf-control">
                                <select name="primary_field" class="erf-input-field">
                                    <option value=""><?php _e('Select Field','erforms'); ?></option>
                                    <?php 
                                          $input_fields= erforms_get_form_input_fields($form['id']); 
                                          foreach($input_fields as $temp_field): 
                                              if($temp_field['type']!='email')
                                                  continue;
                                    ?>
                                            <option value="<?php echo esc_attr($temp_field['name']); ?>" <?php echo $form['primary_field']==$temp_field['name'] ? 'selected' : ''; ?>><?php echo $temp_field['label']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="description"><?php _e('Selected field value will be used as primary contact. Useful when user does not exists in WordPress. Value won\'t be used if user is logged in.', 'erforms') ?></p>
                            </div>  
                        </div>
                    
                        <div class="erf-row">
                            <div class="erf-control-label">
                                <label><?php _e('Primary Contact Name Field', 'erforms'); ?></label>
                            </div>
                            <div class="erf-control">
                                <select name="primary_contact_name_field"  class="erf-input-field">
                                    <option value=""><?php _e('Select Field','erforms'); ?></option>
                                    <?php foreach($input_fields as $temp_field): ?>
                                            <option value="<?php echo esc_attr($temp_field['name']); ?>" <?php echo $form['primary_contact_name_field']==$temp_field['name'] ? 'selected' : ''; ?>><?php echo $temp_field['label']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="description"><?php _e('Selected field value will be used as contact name in Payment status notifications. Useful when user does not exists in WordPress. Value won\'t be used if user is logged in.', 'erforms') ?></p>
                            </div>  
                        </div>
                    <?php endif; ?>

                    
                    <?php if (erforms_show_opt_in()): ?>
                                <div class="erf-row">
                                    <div class="erf-control-label">
                                        <label><?php _e('Enable opt-in checkbox', 'erforms'); ?></label>
                                    </div>
                                    <div class="erf-control erf-has-child">.
                                        <input type="checkbox"  class="erf_toggle" name="opt_in" <?php echo!empty($form['opt_in']) ? 'checked' : ''; ?>/>
                                        <label></label>
                                        <p class="description"><?php _e('Allow users to opt-in for subscription.', 'erforms'); ?></p>        
                                    </div> 
                                </div>

                                <div class="erf-child-rows" style="<?php echo !empty($form['opt_in']) ? '' : 'display:none'; ?>">
                                    <div class="erf-row">
                                        <div class="erf-control-label">
                                            <label><?php _e('Checkbox Text', 'erforms'); ?></label>
                                        </div>
                                        <div class="erf-control">
                                            <input class="erf-input-field" type="text" name="opt_text" value="<?php echo esc_attr($form['opt_text']); ?>" />
                                            <p class="description"><?php _e('Text will appear with checkbox.', 'erforms'); ?></p>
                                        </div>  
                                    </div>

                                    <div class="erf-row">
                                        <div class="erf-control-label">
                                            <label><?php _e('Default State', 'erforms'); ?></label>
                                        </div>
                                        <div class="erf-control">
                                            <input type="radio" name="opt_default_state" value="1" <?php echo!empty($form['opt_default_state']) ? 'checked' : ''; ?>/><?php _e('Checked', 'erforms'); ?>
                                            <input type="radio" name="opt_default_state" value="0" <?php echo empty($form['opt_default_state']) ? 'checked' : ''; ?>/><?php _e('Unchecked', 'erforms'); ?>
                                            <p class="description"><?php _e('Default state of the checkbox.', 'erforms'); ?></p>
                                        </div>  
                                    </div>
                                </div>   
                    <?php endif; ?>
                    
                    <?php do_action('erf_form_config_user_general'); ?>

                </div>

            </div>

            <div style="<?php echo $type == 'user_account' ? '' : 'display:none' ?>">
                <?php if ($form['type'] == "reg") : ?>
                    <!-- User Account Settings -->
                    <div class="group-title">
                        <?php _e('User Account Settings', 'erforms'); ?>
                    </div>

                    <div class="group-wrap">
                        <div class="erf-row">
                            <div class="erf-control-label">
                                <label><?php _e('Assign User Role', 'erforms'); ?></label>
                            </div>
                            <div class="erf-control">
                                <?php $default_role = isset($form['default_role']) ? $form['default_role'] : get_option('default_role'); ?>
                                <select name="default_role" class="erf-input-field">
                                    <option value=""><?php _e('Inherit from Form', 'erforms'); ?></option>
                                    <?php wp_dropdown_roles($default_role); ?>
                                </select>
                                <p class='description'><?php _e('User Role that will be assigned to the user after successful registration. If "Inherit from Form" option selected, Form has to provide role information. Role option can be allowed for Radio Group field by enabling "User Role" option.', 'erforms'); ?></p>
                            </div>  
                        </div>
                        
                        <div class="erf-row">
                            <div class="erf-control-label">
                                <label><?php _e('Auto User Activation', 'erforms'); ?></label>
                            </div>
                            <div class="erf-control erf-has-child">
                                <input class="erf_toggle" type="checkbox" data-has-child="1" name="auto_user_activation" value="1" <?php echo $auto_user_activation; ?>>
                                <label></label>
                                <p class="description"><?php printf(__("Activates user's account after submission. Notifications can be configured from <a target='_blank' href='%s'>here</a>.", 'erforms'), '?page=erforms-dashboard&form_id=' . $form_id . '&tab=notifications&type=user_activation'); ?></p>
                            </div>  
                        </div>

                        <div class="erf-child-rows" style="<?php echo !empty($form['auto_user_activation']) ? '' : 'display:none'; ?>">
                            <div class="erf-row">
                                <div class="erf-control-label">
                                    <label><?php _e('Log in', 'erforms'); ?></label>
                                </div>

                                <div class="erf-control">
                                    <input class="erf_toggle" type="checkbox" name="auto_login" value="1" <?php echo $auto_login; ?>>
                                    <label></label>
                                    <p class="description"><?php _e('Logs in user after submission.', 'erforms'); ?></p>
                                </div>  
                            </div>
                        </div>



                        <div class="erf-row" id="verification_link">
                            <div class="erf-control-label">
                                <label><?php _e('Send Verification Link', 'erforms'); ?></label>
                            </div>
                            <div class="erf-control erf-has-child">
                                <input class="erf_toggle" type="checkbox" data-has-child="1" name="en_email_verification" value="1" <?php echo empty($form['auto_user_activation']) ? '' : 'disabled'; ?> <?php echo!empty($form['en_email_verification']) ? 'checked' : '' ?>>
                                <label></label>
                                <p class="description"><?php printf(__('After successful form submission, user will receive an email with account verification link. Clicking the link will activate the account. Make sure <b>Auto User Activation</b> is disabled. Otherwise it won\'t work. To change the notification content, Please click <a target="_blank" href="%s">here</a>', 'erforms'), '?page=erforms-dashboard&form_id=' . $form_id . '&tab=notifications&type=user_verification'); ?></p>
                            </div>  
                        </div>

                        <div class="erf-child-rows" style="<?php echo !empty($form['en_email_verification']) ? '' : 'display:none'; ?>">
                            <div class="erf-row">
                                <div class="erf-control-label">
                                    <label><?php _e('Account Activation Message', 'erforms'); ?></label>
                                </div>

                                <div class="erf-control">
                                    <?php
                                    $editor_id = 'user_acc_verification_msg';
                                    $settings = array('editor_class' => 'erf-editor');
                                    wp_editor($form['user_acc_verification_msg'], $editor_id, $settings);
                                    ?>
                                    <p class="desription"><?php _e('Message will appear on successful account activation. Here you can add any plugin shortcode to show login box or any other elements.', 'erforms') ?></p>
                                </div>
                            </div>

                            <div class="erf-row">
                                <div class="erf-control-label">
                                    <label><?php _e('Account Verification Page', 'erforms'); ?><sup>*</sup></label>
                                </div>

                                <div class="erf-control">
                                    <?php wp_dropdown_pages(array('selected' => $form['after_user_ver_page'], 'show_option_none' => 'Select Page', 'option_none_value' => 0, 'name' => 'after_user_ver_page', 'class' => 'erf-input-field')); ?>
                                    <p class="desription"><?php printf(__("This Page's link will be sent to User for account re-verification. Make sure to add <code>%s</code> shortcode on the selected page.", 'erforms'), '[erforms_account_verification]') ?></p>
                                </div>
                            </div>

                            <div class="erf-row">
                                <div class="erf-control-label">
                                    <label><?php _e('Auto login after verification', 'erforms'); ?></label>
                                </div>

                                <div class="erf-control">
                                    <input class="erf_toggle" type="checkbox" name="auto_login_after_ver" value="1"  <?php echo!empty($form['auto_login_after_ver']) ? 'checked' : '' ?>>
                                    <label></label>
                                    <p class="desription"><?php _e('Logs in User after successful verfication. In case <b>After Login Redirection</b> or <b>Role Based Login Redirection</b> (Under Global Settings) is enabled, User will be directed to corresponding page.', 'erforms'); ?></p>
                                </div>
                            </div>

                        </div>
                        <?php do_action('erf_form_config_user_account'); ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Form Restriction Setting -->
            <div style="<?php echo $type == 'restrictions' ? '' : 'display:none' ?>">
                <div class="group-title">
                    <?php _e('Restrictions', 'erforms'); ?>
                </div>

                <div class="group-wrap">
                    <div class="erf-row">
                        <div class="erf-control-label">
                            <label><?php _e('Allowed User Roles', 'erforms'); ?></label>
                        </div>
                        <div class="erf-control">
                            <?php echo erforms_get_roles_checkbox('access_roles', $form['access_roles']); ?>
                            <p class='description'><?php _e('Only users with above roles will be allowed to view form. By default it will allow all the users.', 'erforms') ?></p>
                        </div>  
                    </div>

                    <div class="erf-row">
                        <div class="erf-control-label">
                            <label><?php _e('Access Denied Note', 'erforms'); ?></label>
                        </div>
                        <div class="erf-control">
                            <textarea class="erf-input-field" name="access_denied_msg"><?php echo esc_textarea($form['access_denied_msg']); ?></textarea>
                            <p class="description"><?php _e('Users will see this message when they are not allowed to access the form.', 'erforms') ?></p>
                        </div>  
                    </div>

                    <?php if ($form['type'] == 'reg') : ?>
                        <div class="erf-row">
                            <div class="erf-control-label">
                                <label><?php _e('Allow submission from Logged in Users', 'erforms'); ?></label>
                            </div>
                            <div class="erf-control">
                                <input class="erf_toggle"  type="checkbox" name="allow_re_register" value="1" <?php echo $form['allow_re_register'] ? 'checked' : '' ?>>
                                <label></label>
                                <p class='description'><?php _e('If checked form will be visible to logged in users. Helpful in case you want to re-register the users.', 'erforms') ?></p>
                            </div>  
                        </div>
                    <?php else : ?>
                        <div class="erf-row">
                            <div class="erf-control-label">
                                <label><?php _e('Allow only logged in users ', 'erforms'); ?></label>
                            </div>
                            <div class="erf-control">
                                <input class="erf_toggle"  type="checkbox" name="allow_only_registered" value="1" <?php echo $form['allow_only_registered'] ? 'checked' : '' ?>>
                                <label></label>
                                <p class='description'><?php _e('If checked only logged in users will be able to submit the form.', 'erforms') ?></p>
                            </div>  
                        </div>
                    <?php endif; ?>

                    <div class="erf-row">
                        <div class="erf-control-label">
                            <label><?php _e('Password', 'erforms'); ?></label>
                        </div>
                        <div class="erf-control erf-has-child">
                            <input class="erf_toggle"  type="checkbox" name="en_pwd_restriction" value="1" <?php echo $form['en_pwd_restriction'] ? 'checked' : ''; ?> />
                            <label></label>
                            <p class='description'><?php _e('System will ask users to enter a password before accessing form.', 'erforms'); ?>
                        </div>  
                    </div>

                    <div class="erf-child-rows" style="<?php echo !empty($form['en_pwd_restriction']) ? '' : 'display:none'; ?>">

                        <div class="erf-row">
                            <div class="erf-control-label">
                                <label><?php _e('Description', 'erforms'); ?></label>
                            </div>

                            <div class="erf-control">
                                <input type="text" class="erf-input-field" name="pwd_res_description" value="<?php echo esc_attr($form['pwd_res_description']); ?>">
                                <p class="description"><?php _e('Description about the restriction. It will be displayed above the form.', 'erforms'); ?></p>
                            </div>  
                        </div>

                        <div class="erf-row">
                            <div class="erf-control-label">
                                <label><?php _e('Question', 'erforms'); ?></label>
                            </div>

                            <div class="erf-control">
                                <input type="text" class="erf-input-field" name="pwd_res_question" value="<?php echo esc_attr($form['pwd_res_question']); ?>">
                                <p class="description"><?php _e('This question will be asked to user.', 'erforms'); ?></p>
                            </div>  
                        </div>

                        <div class="erf-row">
                            <div class="erf-control-label">
                                <label><?php _e('Password/Answer'); ?></label>
                            </div>

                            <div class="erf-control">
                                <input type="text" class="erf-input-field" name="pwd_res_answer" value="<?php echo esc_attr($form['pwd_res_answer']); ?>">
                                <p class="description"><?php _e('Password/Answer that must be given by user to access the form.', 'erforms'); ?></p>
                            </div>  
                        </div>

                        <div class="erf-row">
                            <div class="erf-control-label">
                                <label><?php _e('Error Message'); ?></label>
                            </div>

                            <div class="erf-control">
                                <input type="text" class="erf-input-field" name="pwd_res_err" value="<?php echo esc_attr($form['pwd_res_err']); ?>">
                                <p class="description"><?php _e('It will be displayed when user enters wrong password/answer.', 'erforms'); ?></p>
                            </div>  
                        </div>

                        <div class="erf-row">
                            <div class="erf-control-label">
                                <label><?php _e('Enable For Logged in Users'); ?></label>
                            </div>

                            <div class="erf-control">
                                <input  class="erf_toggle" type="checkbox" name="pwd_res_en_logged_in" value="1" <?php echo empty($form['pwd_res_en_logged_in']) ? '' : 'checked'; ?>>
                                <label></label>
                                <p class="description"><?php _e('If enabled, Logged in users will have to answer the security question before accessing the form.', 'erforms'); ?></p>
                            </div>  
                        </div>

                    </div>

                    <div class="erf-row">
                        <div class="erf-control-label">
                            <label><?php _e('Limit Submissions', 'erforms'); ?></label>
                        </div>
                        <div class="erf-control erf-has-child">
                            <input class="erf_toggle"  type="checkbox" name="enable_limit" data-has-child="1" value="1" <?php echo $form['enable_limit'] ? 'checked' : ''; ?> />
                            <label></label>
                            <p class='description'><?php _e('Removes the form after required number of submissions or a specific date. ', 'erforms'); ?>
                        </div>  
                    </div>

                    <div class="erf-child-rows" style="<?php echo !empty($form['enable_limit']) ? '' : 'display:none'; ?>">
                        <div class="erf-row">
                            <div class="erf-control-label">
                                <label><?php _e('By Date/ By Number', 'erforms'); ?></label>
                            </div>
                            <div class="erf-control erf-has-child">
                                <input checked type="radio" name="limit_type" data-has-child="1"  value="date" /> <?php _e('Date', 'erforms'); ?>
                                <input type="radio" name="limit_type" data-has-child="1" data-child-index="1" value="number" <?php echo $form['limit_type'] == 'number' ? 'checked' : ''; ?>/> <?php _e('Number', 'erforms'); ?>
                                <input type="radio" name="limit_type" data-has-child="1" data-child-index="0" value="both" <?php echo $form['limit_type'] == 'both' ? 'checked' : ''; ?>/> <?php _e('Both(Whichever is earlier)', 'erforms'); ?>
                            </div>  
                        </div>

                        <div class="erf-child-rows" style="<?php echo ($form['limit_type']=='date' || $form['limit_type']=='both') ? '' : 'display:none'; ?>">
                            <div class="erf-row">
                                <div class="erf-control-label">
                                    <label><?php _e('Date', 'erforms'); ?></label>
                                </div>
                                <div class="erf-control">
                                    <input type="text" class="erf-input-field" id="erf_configure_limit_by_date" name="limit_by_date" data-has-child="1" value="<?php echo esc_attr($form['limit_by_date']); ?>" />
                                    <p class="description"><?php _e('Last date on which this form will appear for users.', 'erforms') ?></p>
                                </div>  
                            </div>
                            
                            <div class="erf-row">
                                <div class="erf-control-label">
                                    <label><?php _e('Time', 'erforms'); ?></label>
                                </div>
                                <div class="erf-control">
                                    <input value="<?php echo isset($form['limit_time']) ? esc_attr($form['limit_time']) : ''; ?>" id="form_limit_time" name="limit_time" type="text" class="time ui-timepicker-input erf-input-field" autocomplete="off">
                                </div>  
                            </div>
                        </div>

                        <div class="erf-child-rows" style="<?php echo ($form['limit_type']=='number' || $form['limit_type']=='both') ? '' : 'display:none'; ?>">
                            <div class="erf-row">
                                <div class="erf-control-label">
                                    <label><?php _e('Number of Submissions', 'erforms'); ?></label>
                                </div>
                                <div class="erf-control">
                                    <input type="number" class="erf-input-field" name="limit_by_number" data-has-child="1" value="<?php echo esc_attr($form['limit_by_number']); ?>" />
                                    <p class="description"><?php _e('Form will not be visible after this number is reached.', 'erforms') ?></p>
                                </div>  
                            </div>
                        </div>

                        <div class="erf-row">
                            <div class="erf-control-label">
                                <label><?php _e('Display message', 'erforms'); ?></label>
                            </div>
                            <div class="erf-control">
                                <textarea class="erf-input-field" name="limit_message"><?php echo esc_textarea($form['limit_message']); ?></textarea>
                                <p class="description"><?php _e('This message will be shown when accessing the form after limit expired.', 'erforms') ?></p>
                            </div>  
                        </div>

                    </div>
                    
                    <div class="erf-row">
                        <div class="erf-control-label">
                            <label><?php _e('Disable multiple Submissions','erforms'); ?></label>
                        </div>

                        <div class="erf-control erf-has-child">
                            <input  class="erf_toggle" type="checkbox" name="dis_mul_sub" value="1" <?php echo empty($form['dis_mul_sub']) ? '' : 'checked'; ?>>
                            <label></label>
                            <p class="description"><?php _e('Restrict multiple submissions from same user.', 'erforms'); ?></p>
                        </div>  
                    </div>
                    
                    <div class="erf-child-rows" style="<?php echo !empty($form['dis_mul_sub']) ? '' : 'display:none'; ?>">
                        <div class="erf-row">
                            <div class="erf-control-label">
                                <label><?php _e('Message', 'erforms'); ?></label>
                            </div>
                            <div class="erf-control">
                                <textarea class="erf-input-field" name="mul_sub_denial_msg"><?php echo esc_textarea($form['mul_sub_denial_msg']); ?></textarea>
                                <p class="description"><?php _e('Users will see this message if they already have submission(s) on the form.', 'erforms') ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <?php do_action('erf_form_config_restrictions'); ?>
                </div>
            </div>
            <!-- Restriction settings ends here -->

            <div style="<?php echo $type == 'plans' ? '' : 'display:none' ?>">
                <?php wp_enqueue_script('jquery-ui-sortable'); ?>
                <!-- Plan Settings -->
                    <div class="group-title">
                        <?php _e('Plan Settings', 'erforms'); ?>
                    </div>

                    <div class="group-wrap">
                        <?php if (empty($options['payment_methods'])) : ?>
                        <p class="description" style="color:red"><?php printf(__('It appears none of the Payment Method is enabled. Please click <a target="_blank" href="%s">here</a> to configure it.','erforms'),admin_url('admin.php?page=erforms-settings&tab=payments')); ?></p>
                        <?php endif; ?>
                        <?php if($form['type']=='contact' && empty($form['primary_field'])): ?>
                            <div class="erf-warning"><?php printf(__('It is recommended to configure <b>Primary Email</b> field for payment related notifications. You can configure it from <a href="%s" target="_blank">here</a>.','erforms'),admin_url('admin.php?page=erforms-dashboard&form_id='.$form['id'].'&tab=configure&type=general')); ?></div>
                        <?php endif; ?>
                        <div class="erf-row">
                            <div class="erf-control-label">
                                <label><?php _e('Enable Payment', 'erforms'); ?></label>
                            </div>
                            <div class="erf-control erf-has-child">
                                <input class="erf_toggle"  type="checkbox" value="1" name="plan_enabled" data-has-child="1" <?php echo $form['plan_enabled'] == "1" ? 'checked' : ''; ?>/>
                                <label></label>
                                <?php if($form['type']=='contact'): ?>
                                    <p class="description"></p>
                                <?php endif; ?>    
                            </div>  
                        </div>


                        <div class="erf-child-rows" style="<?php echo !empty($form['plan_enabled']) ? '' : 'display:none'; ?>">
                            <div class="erf-row">
                                <div class="erf-control-label">
                                    <label><?php _e('Payment header', 'erforms'); ?></label>
                                </div>
                                <div class="erf-control">
                                    <input type="text" class="erf-input-field" name="payment_header" value="<?php echo esc_attr($form['payment_header']); ?>" />
                                    <p class="description"><?php _e('Above text will appear before payment options','erforms'); ?></p>
                                </div>  
                            </div>
                            
                            <div class="erf-row">
                                <div class="erf-control-label">
                                    <label><?php _e('Select Plan(s)', 'erforms'); ?></label>
                                </div>
                                <div class="erf-control">
                                    <table class="wp-list-table widefat fixed striped erf-plans-table">
                                        <thead>
                                            <th></th>
                                            <th><?php _e('Enable','erforms'); ?></th>
                                            <th><?php _e('Plan Name','erforms'); ?></th>
                                            <th><?php _e('Type','erforms'); ?></th>
                                            <th><?php _e('Required','erforms'); ?></th>
                                        </thead>    
                                        <tbody class="erf-plan-sortable">
                                            <?php 
                                                // Showing all active plans
                                                foreach($form['plans']['enabled'] as $p_id):
                                                    $plan= erforms()->plan->get_plan($p_id);
                                                    if(empty($plan))
                                                        continue;
                                            ?>
                                            <tr>
                                                <td><span class="dashicons dashicons-move"></span></td>
                                                <td><input <?php if(isset($form['plans']['enabled']) && in_array($plan['id'],$form['plans']['enabled'])) echo 'checked'; ?> class="erf_toggle" value="<?php echo $plan['id']; ?>" name="plans[enabled][]" type="checkbox" /><label></label></td>
                                                <td><?php echo $plan['name']; ?></td>
                                                <td><?php echo $plan['type']=='user' ? __('User','erforms') : __('Product','erforms'); ?></td>
                                                <td><input <?php if(isset($form['plans']['required']) && in_array($plan['id'],$form['plans']['required'])) echo 'checked'; ?> class="erf_toggle" value="<?php echo $plan['id']; ?>" name="plans[required][]" type="checkbox" /><label></label></td>
                                            </tr>
                                            <?php endforeach;?>
                                            
                                            <?php 
                                                // Showing disabled plan at once
                                                $plans= erforms()->plan->get_plans();
                                                foreach($plans as $plan):
                                                    if(in_array($plan['id'],$form['plans']['enabled']))
                                                            continue;
                                            ?>
                                                <tr>
                                                    <td><span class="dashicons dashicons-move"></span></td>
                                                    <td><input <?php if(isset($form['plans']['enabled']) && in_array($plan['id'],$form['plans']['enabled'])) echo 'checked'; ?> class="erf_toggle" value="<?php echo $plan['id']; ?>" name="plans[enabled][]" type="checkbox" /><label></label></td>
                                                    <td><?php echo $plan['name']; ?></td>
                                                    <td><?php echo $plan['type']=='user' ? __('User','erforms') : __('Product','erforms'); ?></td>
                                                    <td><input <?php if(isset($form['plans']['required']) && in_array($plan['id'],$form['plans']['required'])) echo 'checked'; ?> class="erf_toggle" value="<?php echo $plan['id']; ?>" name="plans[required][]" type="checkbox" /><label></label></td>
                                                    
                                                </tr>
                                            <?php  endforeach; ?>
                                        </tbody>
                                    </table> 
                                    <p class="description"><?php _e('Selected plans are appended in the end of the form. <b>Required</b> plans will appear first and will be followed by other plans.','erforms'); ?></p>
                                </div>  
                            </div>
                            <div class="erf-row">
                                <div class="erf-control-label">
                                    <label><?php _e('Allow single selection', 'erforms'); ?></label>
                                </div>
                                <div class="erf-control">
                                    <input class="erf_toggle" type="checkbox" name="allow_single_plan" value="1" <?php echo!empty($form['allow_single_plan']) ? 'checked' : ''; ?>>
                                    <label></label>
                                    <p class="description"><?php _e('This will group all the required plans and user will be able to choose only one plan out of multiple payment options.') ?></p>
                                </div>
                            </div>    

                        </div>
                                <script>
                                    jQuery(document).ready(function(){
                                        $= jQuery;
                                        $('.erf-plan-sortable').sortable();
                                    })
                                </script>    
                        <?php do_action('erf_form_config_plans'); ?>    
                    </div>
            </div>

            <!-- Edit Submission Settings -->
            <div style="<?php echo $type == 'edit_sub' ? '' : 'display:none' ?>">
                <div class="group-wrap">

                    <div class="erf-row">
                        <div class="erf-control-label">
                            <label><?php _e('Enable Edit Submission', 'erforms'); ?></label>
                        </div>
                        <div class="erf-control erf-has-child">
                            <input class="erf_toggle" type="checkbox" data-has-child="1" name="en_edit_sub" value="1" <?php echo!empty($form['en_edit_sub']) ? 'checked' : ''; ?>>
                            <label></label>
                            <p class="description"><?php _e("This will allow users' to edit/delete their submissions from front end My Account area <code>[erforms_my_account]</code>", 'erforms'); ?></p>
                        </div>  
                    </div>

                    <div class="erf-child-rows" style="<?php echo !empty($form['en_edit_sub']) ? '' : 'display:none'; ?>">
                        <div class="erf-row">
                            <div class="erf-control-label">
                                <label><?php _e('Edit', 'erforms'); ?></label>
                            </div>
                            <div class="erf-control">
                                <div class="erf-edit-fields-wrapper">
                                    <?php
                                    $field_names = array_keys($fields);
                                    foreach ($fields as $field_name => $field_label):
                                        ?>
                                        <label class="erf-form-field">
                                            <input  name="edit_fields[]" type="checkbox" value="<?php echo esc_attr($field_name); ?>" <?php echo in_array($field_name, $form['edit_fields']) ? 'checked' : ''; ?>>

                                            <span><?php echo $field_label; ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                                <br>
                                <p class="description"><?php printf(__("Only above selected field's data are allowed to edit. Notifications can be configured <a target='_blank' href='%s'>here</a>", 'erforms'), '?page=erforms-dashboard&form_id=' . $form_id . '&tab=notifications&type=edit_submission'); ?></p>
                            </div>  
                        </div>

                        <div class="erf-row">
                            <div class="erf-control-label">
                                <label><?php _e('Delete', 'erforms'); ?></label>
                            </div>
                            <div class="erf-control erf-has-child">
                                <div class="erf-control">
                                    <input class="erf_toggle" type="checkbox" data-has-child="1" name="allow_sub_deletion" value="1" <?php echo!empty($form['allow_sub_deletion']) ? 'checked' : ''; ?>>
                                    <label></label>
                                    <p class="description"><?php printf(__("Allows users to delete their submission(s).  Notifications can be configured <a target='_blank' href='%s'>here</a>", 'erforms'), '?page=erforms-dashboard&form_id=' . $form_id . '&tab=notifications&type=delete_submission'); ?></p>
                                </div>    

                            </div>  
                        </div>
                    </div>

                    <?php do_action('erf_form_config_edit_sub'); ?>
                </div>
            </div>
            <!-- Edit Submission ends here -->


            <!-- Form Layout -->
            <div style="<?php echo $type == 'display' ? '' : 'display:none' ?>">
                <div class="group-title">
                    <?php _e('Display Settings', 'erforms'); ?>
                </div>
                <div class="group-wrap">
                    <div class="erf-row">
                        <div class="erf-control-label">
                            <label><?php _e('Form Layout', 'erforms'); ?></label>
                        </div>
                        <div class="erf-control">
                            <select name="layout"  class="erf-input-field">
                                <option <?php echo $form['layout'] == "one-column" ? 'selected' : ''; ?> value="one-column"><?php _e('One Column', 'erforms'); ?></option>
                                <option <?php echo $form['layout'] == "two-column" ? 'selected' : ''; ?> value="two-column"><?php _e('Two Column', 'erforms'); ?></option>
                            </select>  
                            <p class="description"><?php printf(__("We recommend you to use Field's width proprty to create multi column view. This option is just for backward compatibility. You can change field's width from the <a href='%s' target='_blank'>Fields</a>.",'erforms'),admin_url('admin.php?page=erforms-dashboard&form_id='.$form_id.'&tab=build')); ?></p>
                        </div>  
                    </div>
                    
                    <div class="erf-row">
                        <div class="erf-control-label">
                            <label><?php _e('Field Style', 'erforms'); ?></label>
                        </div>
                        <div class="erf-control">
                            <select name="field_style"  class="erf-input-field">
                                <option <?php echo $form['field_style'] == "flat" ? 'selected' : ''; ?> value="flat"><?php _e('Flat', 'erforms'); ?></option>
                                <option <?php echo $form['field_style'] == "rounded" ? 'selected' : ''; ?> value="rounded"><?php _e('Rounded', 'erforms'); ?></option>
                                <option <?php echo $form['field_style'] == "rounded-corner" ? 'selected' : ''; ?> value="rounded-corner"><?php _e('Rounded Corner', 'erforms'); ?></option>
                                <option <?php echo $form['field_style'] == "border-bottom" ? 'selected' : ''; ?> value="border-bottom"><?php _e('Border Bottom', 'erforms'); ?></option>
                            </select>    
                        </div>  
                    </div>

                    <div class="erf-row">
                        <div class="erf-control-label">
                            <label><?php _e('Label Position','erforms'); ?></label>
                        </div>
                        <div class="erf-control">
                            <select name="label_position"  class="erf-input-field">
                                <option <?php echo $form['label_position'] == "top" ? 'selected' : ''; ?> value="top"><?php _e('Top', 'erforms'); ?></option>
                                <option <?php echo $form['label_position'] == "inline" ? 'selected' : ''; ?> value="inline"><?php _e('Inline', 'erforms'); ?></option>
                                <option <?php echo $form['label_position'] == "no-label" ? 'selected' : ''; ?> value="no-label"><?php _e('No Label', 'erforms'); ?></option>
                            </select>    
                        </div>  
                    </div>

                    <div class="erf-row">
                        <div class="erf-control-label">
                            <label><?php _e('Content Above The Form', 'erforms'); ?></label>
                        </div>
                        <div class="erf-control">
                            <?php
                            $editor_id = 'before_form';
                            $settings = array('editor_class' => 'erf-editor');
                            wp_editor($form['before_form'], $editor_id, $settings);
                            ?>
                            <p class="desription">
                                <?php _e('This will be displayed above the form. Inbuilt shortcode:', 'erforms') ?><br/>
                                <ul>
                                    <li><code>{{submission_counter}}</code>: <?php _e('Shows total number of submissions.'); ?></li>
                                    <li><code>{{submission_counter skip="20"}}</code>: <?php _e('Skips number of records from total submissions.'); ?></li>
                                    <li><code>{{submission_counter skip_before="07/22/2019"}}</code>: <?php _e('Skip submissions before a certain date (Format: mm/dd/YYYY) from total submissions.'); ?></li>
                                </ul>
                            </p>
                        </div>  
                    </div>
                    <?php do_action('erf_form_config_display_settings', $form); ?>
                </div>
            </div>


            <!-- User -->
            <div style="<?php echo $type == 'post_sub' ? '' : 'display:none' ?>">
                <div class="group-title">
                    <?php _e('Post Submission', 'erforms'); ?>
                </div>
                <div class="group-wrap">

                    <div class="erf-row">
                        <div class="erf-control-label">
                            <label><?php _e('Redirection', 'erforms'); ?></label>
                        </div>
                        <div class="erf-control">
                            <input type="url" class="erf-input-field" name="redirect_to" value="<?php echo esc_attr($form['redirect_to']); ?>">
                            <?php if ($form['type'] == 'reg') : ?>
                                <p class='description'><?php _e('URL where the user will be redirected after submission.', 'erforms'); ?></p>
                            <?php else: ?>
                                <p class='description'><?php _e('URL where the user will be redirected after submission.', 'erforms'); ?></p>
                            <?php endif; ?>    
                        </div>  
                    </div>

                    <div class="erf-row">
                        <div class="erf-control-label">
                            <label><?php _e('Success Message', 'erforms'); ?></label>
                        </div>
                        <div class="erf-control">
                            <?php
                            $editor_id = 'success_msg';
                            $settings = array('editor_class' => 'erf-editor');
                            wp_editor($form['success_msg'], $editor_id, $settings);
                            ?>
                            <p class="description">
                                <?php printf(__('Content to be displayed after successful submission. Create personalized message using <a href="%s" target="_blank">ERF short tags</a> to dynamicaly replace the values. You can use <code>[erforms_resend_verification_link label="here"]</code> shortcode to insert <b>Resend Verification</b> link. Make sure to enable notification from <a target="_blank" href="%s">here</a>.','erforms'),admin_url('admin.php?page=erforms-field-shortcodes&form_id='.$form['id']),admin_url('admin.php?page=erforms-dashboard&form_id='.$form['id'].'&tab=notifications&type=user_verification')); ?>
                            </p>
                        </div>  
                    </div>

                    <div class="erf-row">
                        <div class="erf-control-label">
                            <label><?php _e('Post to External URL', 'erforms'); ?></label>
                        </div>
                        <div class="erf-control erf-has-child">
                            <input class="erf_toggle"  type="checkbox" value="1" name="enable_external_url" data-has-child="1" <?php echo $form['enable_external_url'] == '1' ? 'checked' : ''; ?> />
                            <label></label>
                            <p class="description"><?php _e('Posts submission data to external API. Useful for synchronizing submission data on other applications.', 'erforms') ?></p>
                        </div>  
                    </div>

                    <div class="erf-child-rows" style="<?php echo !empty($form['enable_external_url']) ? '' : 'display:none'; ?>">
                        <div class="erf-row">
                            <div class="erf-control-label">
                                <label><?php _e('URL', 'erforms'); ?></label>
                            </div>
                            <div class="erf-control">
                                <input type="text" class="erf-input-field" name="external_url" value="<?php echo esc_attr($form['external_url']); ?>" />
                                <p class='description'><?php _e('API URL which handles submission data.', 'erforms'); ?>
                            </div>  
                        </div>
                    </div>

                    <?php do_action('erf_form_config_post_sub'); ?>
                </div>
            </div>


            <?php do_action('erf_form_configuration', $form,$type); ?>
        </fieldset>

        <p class="submit">
            <input type="hidden" name="erf_save_configuration" />
            <input type="submit" class="button button-primary" value="<?php _e('Save', 'erforms'); ?>" name="save" />
            <input type="submit" value="<?php _e('Save & Close', 'erforms'); ?>" class="button button-primary" name="savec"/>
        </p>
    </form>  
</div>    

<script>
    jQuery(document).ready(function () {
        $ = jQuery;
        var auto_act = $('[name=auto_user_activation]');
        var email_verification = $('[name=en_email_verification]');
        auto_act.change(function () {
            if ($(this).is(':checked')) {
                email_verification.attr('disabled', 'disabled');
                return;
            }
            email_verification.removeAttr('disabled');
        });
        
        $(document).bind('erf_parent_row_changed', function (ev, element) {
            if(element.prop('name')=='limit_type' && element.val()=='both'){
                $('.erf-row [name=limit_by_number]').closest('.erf-child-rows').slideDown();
            }
        });
        
        $('#form_limit_time').timepicker();
        var show_before_login_form= $('#erf_show_before_login_form');
        var login_register= $("#erf_login_and_register");
        login_register.change(function(){
           if($(this).prop("checked")){
               show_before_login_form.prop("checked",false);
           } 
        });
        
        show_before_login_form.change(function(){
            if($(this).prop("checked")){
               login_register.prop("checked",false);
           }
        });
    });
</script>
