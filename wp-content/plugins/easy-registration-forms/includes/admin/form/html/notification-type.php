<?php $fields= erforms()->form->get_fields_dropdown($form_id, array('submit', 'password')); ?>
<div class="erf-form-conf-wrapper">
    <form action="" method="post" id="erf_configuration_form">
        <fieldset class="erf-config-wrap">
            <div class="group-wrap">
                <div style="<?php echo $type=='auto_reply' ? '' : 'display:none' ?>">
                    <div class="erf-row">
                        <div class="erf-control-label">
                            <label><?php _e('Enable Auto Reply', 'erforms'); ?></label>
                        </div>
                        <div class="erf-control erf-has-child">
                            <input class="erf_toggle" type="checkbox" data-has-child="1" name="enabled_auto_reply" value="1" <?php echo empty($form['enabled_auto_reply']) ? '' : 'checked'; ?>>
                            <label></label>
                            <p class="description"><?php _e('Auto responder email for the form. After successful submission a customizable email is sent to the user.','erforms'); ?></p>
                        </div>  
                    </div>
                    <div class="erf-child-rows" style="<?php echo !empty($form['enabled_auto_reply']) ? '' : 'display:none'; ?>">
                        <?php if ($form['type'] != "reg") : ?>
                            <div class="erf-row">
                                <div class="erf-control-label">
                                    <label><?php _e('Recipient(s)', 'erforms'); ?></label>
                                </div>
                                <div class="erf-control">
                                    <input type="text" class="erf-input-field" value="<?php echo esc_attr($form['auto_reply_to']); ?>" name="auto_reply_to" />
                                    <p class='description'>
                                        <p class="description"><?php printf(__('Email where you want to receive notification. Multiple emails can be given using comma(,) sepration.You can also use <a href="%s" target="_blank">ERF short tags</a> to dynamicaly replace the values.','erforms'),admin_url('admin.php?page=erforms-field-shortcodes&form_id='.$form_id)); ?></p>
                                    </p>
                                </div>  
                            </div>
                        <?php endif; ?>
                        <div class="erf-row">
                            <div class="erf-control-label">
                                <label><?php _e('From Email', 'erforms'); ?></label>
                            </div>
                            <div class="erf-control">
                                <input type="text" class="erf-input-field" value="<?php echo esc_attr($form['auto_reply_from']); ?>" name="auto_reply_from" />
                                <p class='description'><?php _e('This displays who the message is from, It is recommended to use Domain email address if not using SMTP plugin.', 'erforms'); ?></p>
                            </div>  
                        </div>

                        <div class="erf-row">
                            <div class="erf-control-label">
                                <label><?php _e('From Name', 'erforms'); ?></label>
                            </div>
                            <div class="erf-control">
                                <input type="text" class="erf-input-field" value="<?php echo esc_attr($form['auto_reply_from_name']); ?>" name="auto_reply_from_name" />
                                <p class='description'><?php _e('When used together with the \'From Email\', it creates a from address like Name "&ltemail@address.com&gt"', 'erforms'); ?></p>
                            </div>  
                        </div>

                        <div class="erf-row">
                            <div class="erf-control-label">
                                <label><?php _e('Subject', 'erforms'); ?></label>
                            </div>
                            <div class="erf-control">
                                <input type="text" class="erf-input-field" value="<?php echo esc_attr($form['auto_reply_subject']); ?>" name="auto_reply_subject" /><br>
                                <p class="description">
                                    <?php printf(__('Subject of the mail sent to the user.You can also use <a href="%s" target="_blank">ERF short tags</a> to dynamicaly replace the values.','erforms'),admin_url('admin.php?page=erforms-field-shortcodes&form_id='.$form_id)); ?>
                                </p>
                            </div>  
                        </div>

                        <div class="erf-row">
                            <div class="erf-control-label">
                                <label><?php _e('Message', 'erforms'); ?></label>
                            </div>
                            <div class="erf-control">
                                <?php
                                $editor_id = 'auto_reply_msg';
                                $settings = array('editor_class' => 'erf-editor');
                                wp_editor($form['auto_reply_msg'], $editor_id, $settings);
                                ?>
                                <br>
                                <p class="description"><?php printf(__('Email content to be sent to the user. Create personalized message using <a href="%s" target="_blank">ERF short tags</a> to dynamicaly replace the values.','erforms'),admin_url('admin.php?page=erforms-field-shortcodes&form_id='.$form_id)); ?></p>
                            </div>  
                        </div>
                    </div>
                </div>
                
                <div style="<?php echo $type=='admin_notification' ? '' : 'display:none' ?>">
                    <div class="erf-row">
                        <div class="erf-control-label">
                            <label><?php _e('Enable Admin Notificaton', 'erforms'); ?></label>
                        </div>
                        <div class="erf-control erf-has-child">
                            <input class="erf_toggle"  type="checkbox" data-has-child="1" name="enable_admin_notification" value="1" <?php echo empty($form['enable_admin_notification']) ? '' : 'checked'; ?>>
                            <label></label>
                            <p class="description"><?php _e('Sends email to admin for every submission.','erforms'); ?></p>
                        </div>  
                    </div>

                    <div class="erf-child-rows" style="<?php echo !empty($form['enable_admin_notification']) ? '' : 'display:none'; ?>">
                        <div class="erf-row">
                            <div class="erf-control-label">
                                <label><?php _e('Notification To', 'erforms'); ?></label>
                            </div>
                            <div class="erf-control">
                                <input type="text" class="erf-input-field" value="<?php echo esc_attr($form['admin_notification_to']); ?>" name="admin_notification_to" />
                                <p class="description">
                                    <?php echo __('Email where you want to receive notifications for form submissions. Multiple emails can be given using comma(,) sepration. In case this value is empty, system will send the notification to site admin.', 'erforms') . '(' . get_option('admin_email') . ')'; ?>
                                </p>
                            </div>  
                        </div>

                        <div class="erf-row">
                            <div class="erf-control-label">
                                <label><?php _e('From Email', 'erforms'); ?></label>
                            </div>
                            <div class="erf-control">
                                <input type="text" class="erf-input-field" value="<?php echo esc_attr($form['admin_notification_from']); ?>" name="admin_notification_from" />
                                <p class='description'><?php _e('It is recommended to use Domain email address if not using any SMTP plugin.', 'erforms'); ?></p>
                            </div>  
                        </div>

                        <div class="erf-row">
                            <div class="erf-control-label">
                                <label><?php _e('From Name', 'erforms'); ?></label>
                            </div>
                            <div class="erf-control">
                                <input type="text" class="erf-input-field" value="<?php echo esc_attr($form['admin_notification_from_name']); ?>" name="admin_notification_from_name" />
                                <p class='description'><?php _e('When used together with the \'From Email\', it creates a from address like Name "&ltemail@address.com&gt"', 'erforms'); ?></p>
                            </div>  
                        </div>

                        <div class="erf-row">
                            <div class="erf-control-label">
                                <label><?php _e('Subject', 'erforms'); ?></label>
                            </div>
                            <div class="erf-control">
                                <input type="text" class="erf-input-field" value="<?php echo esc_attr($form['admin_notification_subject']); ?>" name="admin_notification_subject" /><br>
                                <p class="description">
                                    <?php printf(__('Subject of the mail sent to the user.You can also use <a href="%s" target="_blank">ERF short tags</a> to dynamicaly replace the values.','erforms'),admin_url('admin.php?page=erforms-field-shortcodes&form_id='.$form_id)); ?>
                                </p>
                            </div>  
                        </div>

                        <div class="erf-row">
                            <div class="erf-control-label">
                                <label><?php _e('Message', 'erforms'); ?></label>
                            </div>
                            <div class="erf-control">
                                <?php
                                $editor_id = 'admin_notification_msg';
                                $settings = array('editor_class' => 'erf-editor');
                                wp_editor($form['admin_notification_msg'], $editor_id, $settings);
                                ?>
                                <br>
                                <p class="description">
                                    <?php printf(__('Email content to be sent to the user. Create personalized message using <a href="%s" target="_blank">ERF short tags</a> to dynamicaly replace the values.','erforms'),admin_url('admin.php?page=erforms-field-shortcodes&form_id='.$form_id)); ?>
                                </p>
                            </div>  
                        </div>
                    </div>
                </div>
                
                <div style="<?php echo $type=='user_activation' ? '' : 'display:none' ?>">
                    <?php if ($form['type'] == "reg") : ?>
                        <div class="erf-row">
                            <div class="erf-control-label">
                                <label><?php _e('Enable User Activation Email', 'erforms'); ?></label>
                            </div>
                            <div class="erf-control erf-has-child">
                                <input class="erf_toggle"  type="checkbox" data-has-child="1" name="enable_act_notification" value="1" <?php echo empty($form['enable_act_notification']) ? '' : 'checked'; ?>>
                                <label></label>
                                <p class="description"><?php _e('Enable User Activation. Allows you to send emails to User on activation.','erforms'); ?></p>
                            </div>  
                        </div>

                        <div class="erf-child-rows" style="<?php echo !empty($form['enable_act_notification']) ? '' : 'display:none'; ?>">
                            <div class="erf-row">
                                <div class="erf-control-label">
                                    <label><?php _e('From Email', 'erforms'); ?></label>
                                </div>
                                <div class="erf-control">
                                    <input type="text" class="erf-input-field" value="<?php echo esc_attr($form['user_act_from']); ?>" name="user_act_from" />
                                    <p class='description'><?php _e('It is recommended to use Domain email address if not using any SMTP plugin.', 'erforms'); ?></p>
                                </div>  
                            </div>

                            <div class="erf-row">
                                <div class="erf-control-label">
                                    <label><?php _e('From Name', 'erforms'); ?></label>
                                </div>
                                <div class="erf-control">
                                    <input type="text" class="erf-input-field" value="<?php echo esc_attr($form['user_act_from_name']); ?>" name="user_act_from_name" />
                                    <p class='description'><?php _e('When used together with the \'From Email\', it creates a from address like Name "&ltemail@address.com&gt"', 'erforms'); ?></p>
                                </div>  
                            </div>

                            <div class="erf-row">
                                <div class="erf-control-label">
                                    <label><?php _e('Subject', 'erforms'); ?></label>
                                </div>
                                <div class="erf-control">
                                    <input type="text" class="erf-input-field" value="<?php echo esc_attr($form['user_act_subject']); ?>" name="user_act_subject" /><br>
                                    <p class="description">
                                        <?php printf(__('Subject of the mail sent to the user.You can also use <a href="%s" target="_blank">ERF short tags</a> to dynamicaly replace the values.','erforms'),admin_url('admin.php?page=erforms-field-shortcodes&form_id='.$form_id)); ?>
                                    </p>
                                </div>  
                            </div>

                            <div class="erf-row">
                                <div class="erf-control-label">
                                    <label><?php _e('Message', 'erforms'); ?></label>
                                </div>
                                <div class="erf-control">
                                    <?php
                                    $editor_id = 'user_act_msg';
                                    $settings = array('editor_class' => 'erf-editor');
                                    wp_editor($form['user_act_msg'], $editor_id, $settings);
                                    ?>
                                    <br>
                                    <p class="description">
                                        <?php printf(__('Email content to be sent to the user. Create personalized message using <a href="%s" target="_blank">ERF short tags</a> to dynamicaly replace the values.','erforms'),admin_url('admin.php?page=erforms-field-shortcodes&form_id='.$form_id)); ?>
                                    </p>
                                </div>  
                            </div>
                        </div>

                    <?php endif; ?>
                </div>
                <!-- Edit Notifications -->
                    <div style="<?php echo $type=='edit_submission' ? '' : 'display:none' ?>">
                    <div class="erf-row">
                        <div class="erf-control-label">
                            <label><?php _e('Enable Edit Submission Notifications', 'erforms'); ?></label>
                        </div>
                        <div class="erf-control erf-has-child">
                            <input class="erf_toggle"  type="checkbox" data-has-child="1" name="enable_edit_notifications" value="1" <?php echo empty($form['enable_edit_notifications']) ? '' : 'checked'; ?>>
                            <label></label>
                            <p class="description"><?php _e('Sends email after successful edit Submission.','erforms'); ?></p>
                        </div>  
                    </div>
                
                    <div class="erf-child-rows" style="<?php echo !empty($form['enable_edit_notifications']) ? '' : 'display:none'; ?>">
                        <div class="group-title group-title-inner">
                            <?php _e('User Auto Responder','erforms'); ?>              
                        </div>
                        
                        <div class="erf-row">
                            <div class="erf-control-label">
                                <label><?php _e('From Email', 'erforms'); ?></label>
                            </div>
                            <div class="erf-control">
                                <input type="text" class="erf-input-field" value="<?php echo esc_attr($form['edit_sub_user_from']); ?>" name="edit_sub_user_from" />
                                <p class='description'><?php _e('It is recommended to use Domain email address if not using any SMTP plugin.', 'erforms'); ?></p>
                            </div>  
                        </div>
                        
                        <div class="erf-row">
                            <div class="erf-control-label">
                                <label><?php _e('From Name', 'erforms'); ?></label>
                            </div>
                            <div class="erf-control">
                                <input type="text" class="erf-input-field" value="<?php echo esc_attr($form['edit_sub_user_from_name']); ?>" name="edit_sub_user_from_name" />
                                <p class='description'><?php _e('When used together with the \'From Email\', it creates a from address like Name "&ltemail@address.com&gt"', 'erforms'); ?></p>
                            </div>  
                        </div>
                        
                        <div class="erf-row">
                            <div class="erf-control-label">
                                <label><?php _e('Subject', 'erforms'); ?></label>
                            </div>
                            <div class="erf-control">
                                <input type="text" class="erf-input-field" value="<?php echo $form['edit_sub_user_subject']; ?>" name="edit_sub_user_subject" /><br>
                                <p class="description">
                                    <?php printf(__('Subject of the mail sent to the user.You can also use <a href="%s" target="_blank">ERF short tags</a> to dynamicaly replace the values.','erforms'),admin_url('admin.php?page=erforms-field-shortcodes&form_id='.$form_id)); ?>
                                </p>
                            </div>  
                        </div>
                        
                        <div class="erf-row">
                            <div class="erf-control-label">
                                <label><?php _e('User Autoresponder', 'erforms'); ?></label>
                            </div>
                            <div class="erf-control">
                                <?php
                                $editor_id = 'edit_sub_user_email';
                                $settings = array('editor_class' => 'erf-editor');
                                wp_editor($form['edit_sub_user_email'], $editor_id, $settings);
                                ?>
                                <br>
                                <p class="description">
                                    <?php printf(__('Email content to be sent to the user. Create personalized message using <a href="%s" target="_blank">ERF short tags</a> to dynamicaly replace the values.','erforms'),admin_url('admin.php?page=erforms-field-shortcodes&form_id='.$form_id)); ?>
                                </p>
                            </div>  
                        </div>
                        
                         <div class="group-title group-title-inner">
                            <?php _e('Admin Notification','erforms'); ?>              
                        </div>
                        
                        <div class="erf-row">
                            <div class="erf-control-label">
                                <label><?php _e('Notification To', 'erforms'); ?></label>
                            </div>
                            <div class="erf-control">
                                <input type="text" class="erf-input-field" value="<?php echo esc_attr($form['edit_sub_admin_list']); ?>" name="edit_sub_admin_list" />
                                <p class="description">
                                    <?php echo __('Email where you want to receive notifications on edit submission. Multiple emails can be given using comma(,) sepration. In case this value is empty, system will send the notification to site admin.', 'erforms') . '(' . get_option('admin_email') . ')'; ?>
                                </p>
                            </div>  
                        </div>
                        
                        <div class="erf-row">
                            <div class="erf-control-label">
                                <label><?php _e('From Email', 'erforms'); ?></label>
                            </div>
                            <div class="erf-control">
                                <input type="text" class="erf-input-field" value="<?php echo esc_attr($form['edit_sub_admin_from']); ?>" name="edit_sub_admin_from" />
                                <p class='description'><?php _e('It is recommended to use Domain email address if not using any SMTP plugin.', 'erforms'); ?></p>
                            </div>  
                        </div>
                        
                        <div class="erf-row">
                            <div class="erf-control-label">
                                <label><?php _e('From Name', 'erforms'); ?></label>
                            </div>
                            <div class="erf-control">
                                <input type="text" class="erf-input-field" value="<?php echo esc_attr($form['edit_sub_admin_from_name']); ?>" name="edit_sub_admin_from_name" />
                                <p class='description'><?php _e('When used together with the \'From Email\', it creates a from address like Name "&ltemail@address.com&gt"', 'erforms'); ?></p>
                            </div>  
                        </div>

                        <div class="erf-row">
                            <div class="erf-control-label">
                                <label><?php _e('Subject', 'erforms'); ?></label>
                            </div>
                            <div class="erf-control">
                                <input type="text" class="erf-input-field" value="<?php echo esc_attr($form['edit_sub_admin_subject']); ?>" name="edit_sub_admin_subject" /><br>
                                <p class="description">
                                    <?php printf(__('Subject of the mail sent to the user.You can also use <a href="%s" target="_blank">ERF short tags</a> to dynamicaly replace the values.','erforms'),admin_url('admin.php?page=erforms-field-shortcodes&form_id='.$form_id)); ?>
                                </p>
                            </div>  
                        </div>
                        
                        <div class="erf-row">
                            <div class="erf-control-label">
                                <label><?php _e('Admin Notification Message', 'erforms'); ?></label>
                            </div>
                            <div class="erf-control">
                                <?php
                                $editor_id = 'edit_sub_admin_email';
                                $settings = array('editor_class' => 'erf-editor');
                                wp_editor($form['edit_sub_admin_email'], $editor_id, $settings);
                                ?>
                                <br>
                                <p class="description">
                                    <?php printf(__('Email content to be sent to the user. Create personalized message using <a href="%s" target="_blank">ERF short tags</a> to dynamicaly replace the values.','erforms'),admin_url('admin.php?page=erforms-field-shortcodes&form_id='.$form_id)); ?>
                                </p>
                            </div>  
                        </div>
                    </div>
                    </div>    
                <!-- Edit notification ends here -->
                
                
                <!-- Submission Delete Notifications -->
                    <div style="<?php echo $type=='delete_submission' ? '' : 'display:none' ?>">
                    <div class="erf-row">
                        <div class="erf-control-label">
                            <label><?php _e('Enable Delete Submission Notifications', 'erforms'); ?></label>
                        </div>
                        <div class="erf-control erf-has-child">
                            <input class="erf_toggle"  type="checkbox" data-has-child="1" name="enable_delete_notifications" value="1" <?php echo empty($form['enable_delete_notifications']) ? '' : 'checked'; ?>>
                            <label></label>
                            <p class="description"><?php _e('Allows to send email notifications on submission deletion from <b>My Account</b> page.','erforms'); ?></p>
                        </div>  
                    </div>
                
                    <div class="erf-child-rows" style="<?php echo !empty($form['enable_delete_notifications']) ? '' : 'display:none'; ?>">
                        <div class="group-title group-title-inner">
                            <?php _e('User Auto Responder','erforms'); ?>              
                        </div>
                        
                        <div class="erf-row">
                            <div class="erf-control-label">
                                <label><?php _e('From Email', 'erforms'); ?></label>
                            </div>
                            <div class="erf-control">
                                <input type="text" class="erf-input-field" value="<?php echo esc_attr($form['delete_sub_user_from']); ?>" name="delete_sub_user_from" />
                                <p class='description'><?php _e('It is recommended to use Domain email address if not using any SMTP plugin.', 'erforms'); ?></p>
                            </div>  
                        </div>
                        
                        <div class="erf-row">
                            <div class="erf-control-label">
                                <label><?php _e('From Name', 'erforms'); ?></label>
                            </div>
                            <div class="erf-control">
                                <input type="text" class="erf-input-field" value="<?php echo esc_attr($form['delete_sub_user_from_name']); ?>" name="delete_sub_user_from_name" />
                                <p class='description'><?php _e('When used together with the \'From Email\', it creates a from address like Name "&ltemail@address.com&gt"', 'erforms'); ?></p>
                            </div>  
                        </div>
                        
                        <div class="erf-row">
                            <div class="erf-control-label">
                                <label><?php _e('Subject', 'erforms'); ?></label>
                            </div>
                            <div class="erf-control">
                                <input type="text" class="erf-input-field" value="<?php echo esc_attr($form['delete_sub_user_subject']); ?>" name="delete_sub_user_subject" /><br>
                                <p class="description">
                                    <?php printf(__('Subject of the mail sent to the user.You can also use <a href="%s" target="_blank">ERF short tags</a> to dynamicaly replace the values.','erforms'),admin_url('admin.php?page=erforms-field-shortcodes&form_id='.$form_id)); ?>
                                </p>
                            </div>  
                        </div>
                        
                        <div class="erf-row">
                            <div class="erf-control-label">
                                <label><?php _e('User Autoresponder', 'erforms'); ?></label>
                            </div>
                            <div class="erf-control">
                                <?php
                                $editor_id = 'delete_sub_user_email';
                                $settings = array('editor_class' => 'erf-editor');
                                wp_editor($form['delete_sub_user_email'], $editor_id, $settings);
                                ?>
                                <br>
                                <p class="description">
                                    <?php printf(__('Email content to be sent to the user. Create personalized message using <a href="%s" target="_blank">ERF short tags</a> to dynamicaly replace the values.','erforms'),admin_url('admin.php?page=erforms-field-shortcodes&form_id='.$form_id)); ?>
                                </p>
                            </div>  
                        </div>
                        
                         <div class="group-title group-title-inner">
                            <?php _e('Admin Notification','erforms'); ?>              
                        </div>
                        
                        <div class="erf-row">
                            <div class="erf-control-label">
                                <label><?php _e('Notification To', 'erforms'); ?></label>
                            </div>
                            <div class="erf-control">
                                <input type="text" class="erf-input-field" value="<?php echo esc_attr($form['delete_sub_admin_list']); ?>" name="delete_sub_admin_list" />
                                <p class="description">
                                    <?php echo __('Email where you want to receive notifications on submission delete. Multiple emails can be given using comma(,) sepration. In case this value is empty, system will send the notification to site admin.', 'erforms') . '(' . get_option('admin_email') . ')'; ?>
                                </p>
                            </div>  
                        </div>
                        
                        <div class="erf-row">
                            <div class="erf-control-label">
                                <label><?php _e('From Email', 'erforms'); ?></label>
                            </div>
                            <div class="erf-control">
                                <input type="text" class="erf-input-field" value="<?php echo esc_attr($form['delete_sub_admin_from']); ?>" name="delete_sub_admin_from" />
                                <p class='description'><?php _e('It is recommended to use Domain email address if not using any SMTP plugin.', 'erforms'); ?></p>
                            </div>  
                        </div>
                        
                        <div class="erf-row">
                            <div class="erf-control-label">
                                <label><?php _e('From Name', 'erforms'); ?></label>
                            </div>
                            <div class="erf-control">
                                <input type="text" class="erf-input-field" value="<?php echo esc_attr($form['delete_sub_admin_from_name']); ?>" name="delete_sub_admin_from_name" />
                                <p class='description'><?php _e('When used together with the \'From Email\', it creates a from address like Name "&ltemail@address.com&gt"', 'erforms'); ?></p>
                            </div>  
                        </div>

                        <div class="erf-row">
                            <div class="erf-control-label">
                                <label><?php _e('Subject', 'erforms'); ?></label>
                            </div>
                            <div class="erf-control">
                                <input type="text" class="erf-input-field" value="<?php echo esc_attr($form['delete_sub_admin_subject']); ?>" name="delete_sub_admin_subject" /><br>
                                <p class="description">
                                    <?php printf(__('Subject of the mail sent to the user.You can also use <a href="%s" target="_blank">ERF short tags</a> to dynamicaly replace the values.','erforms'),admin_url('admin.php?page=erforms-field-shortcodes&form_id='.$form_id)); ?>
                                </p>
                            </div>  
                        </div>
                        
                        <div class="erf-row">
                            <div class="erf-control-label">
                                <label><?php _e('Admin Notification Message', 'erforms'); ?></label>
                            </div>
                            <div class="erf-control">
                                <?php
                                $editor_id = 'delete_sub_admin_email';
                                $settings = array('editor_class' => 'erf-editor');
                                wp_editor($form['delete_sub_admin_email'], $editor_id, $settings);
                                ?>
                                <br>
                                <p class="description">
                                    <?php printf(__('Email content to be sent to the user. Create personalized message using <a href="%s" target="_blank">ERF short tags</a> to dynamicaly replace the values.','erforms'),admin_url('admin.php?page=erforms-field-shortcodes&form_id='.$form_id)); ?>
                                </p>
                            </div>  
                        </div>
                    </div>
                    </div>    
                <!-- Delete notification ends here -->
                    
                <!-- User verification --->
                    <div style="<?php echo $type=='user_verification' ? '' : 'display:none' ?>">
                    <?php if ($form['type'] == "reg") : ?>
                    <div class="erf-row" id="user_verification">
                        <div class="erf-control-label">
                            <label><?php _e('Enable User Verification', 'erforms'); ?></label>
                        </div>
                        <div class="erf-control erf-has-child">
                            <input class="erf_toggle" type="checkbox" data-has-child="1" name="en_user_ver_msg" value="1" <?php echo empty($form['en_user_ver_msg']) ? '' : 'checked'; ?>>
                            <label></label>
                            <p class="description"><?php printf(__('Enables notification email for User verification. Make sure to enable <b>Send Verification Link</b> from <b><a target="_blank" href="%s">here</a></b>.','erforms'),'?page=erforms-dashboard&form_id='.$form_id.'&tab=configure&type=user_account#verification_link'); ?></p>
                        </div>  
                    </div>

                    <div class="erf-child-rows" style="<?php echo !empty($form['en_user_ver_msg']) ? '' : 'display:none'; ?>">

                        <div class="erf-row">
                            <div class="erf-control-label">
                                <label><?php _e('From Email', 'erforms'); ?></label>
                            </div>
                            <div class="erf-control">
                                <input type="text" class="erf-input-field" value="<?php echo esc_attr($form['user_ver_from']); ?>" name="user_ver_from" />
                                <p class='description'><?php _e('This displays who the message is from, It is recommended to use Domain email address.', 'erforms'); ?></p>
                            </div>  
                        </div>

                        <div class="erf-row">
                            <div class="erf-control-label">
                                <label><?php _e('From Name', 'erforms'); ?></label>
                            </div>
                            <div class="erf-control">
                                <input type="text" class="erf-input-field" value="<?php echo esc_attr($form['user_ver_from_name']); ?>" name="user_ver_from_name" />
                                <p class='description'><?php _e('When used together with the \'From Email\', it creates a from address like Name "&ltemail@address.com&gt"', 'erforms'); ?></p>
                            </div>  
                        </div>

                        <div class="erf-row">
                            <div class="erf-control-label">
                                <label><?php _e('Subject', 'erforms'); ?></label>
                            </div>
                            <div class="erf-control">
                                <input type="text" class="erf-input-field" value="<?php echo esc_attr($form['user_ver_subject']); ?>" name="user_ver_subject" /><br>
                                <p class="description">
                                    <?php printf(__('Subject of the mail sent to the user.You can also use <a href="%s" target="_blank">ERF short tags</a> to dynamicaly replace the values.','erforms'),admin_url('admin.php?page=erforms-field-shortcodes&form_id='.$form_id)); ?>
                                </p>
                            </div>  
                        </div>

                        <div class="erf-row">
                            <div class="erf-control-label">
                                <label><?php _e('Message', 'erforms'); ?></label>
                            </div>
                            <div class="erf-control">
                                <?php
                                $editor_id = 'user_ver_email_msg';
                                $settings = array('editor_class' => 'erf-editor');
                                wp_editor($form['user_ver_email_msg'], $editor_id, $settings);
                                ?>
                                <br>
                                <p class="description">
                                    <?php printf(__('Email content to be sent to the user. Create personalized message using <a href="%s" target="_blank">ERF short tags</a> to dynamicaly replace the values.','erforms'),admin_url('admin.php?page=erforms-field-shortcodes&form_id='.$form_id)); ?>
                                </p>
                            </div>  
                        </div>
                    </div>
                    <?php endif; ?>
                    </div>    
                <!-- User verification ends here -->
                
                <?php do_action('erf_form_notification_settings',$form); ?>
            </div>
            
            
        </fieldset>

        <p class="submit">
            <input type="hidden" name="erf_save_notifications" />
            <input type="submit" class="button button-primary" value="<?php _e('Save', 'erforms'); ?>" name="save" />
            <input type="submit" value="<?php _e('Save & Close', 'erforms'); ?>" class="button button-primary" name="savec"/>
        </p>
    </form>  
</div>    