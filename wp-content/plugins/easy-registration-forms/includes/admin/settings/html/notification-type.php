<div style="<?php echo $type != 'offline' ? 'display:none' : '' ?>">

    <div class="erf-row">
        <div class="erf-control-label">
            <label><?php _e('Enable Offline Notification', 'erforms'); ?></label>
        </div>
        <div class="erf-control erf-has-child">
            <input class="erf_toggle" type="checkbox" data-has-child="1" name="send_offline_email" value="1" <?php echo $options['send_offline_email'] == 1 ? 'checked' : ''; ?>/>
            <label></label>
            <p class="description"><?php _e('Sends email to users to let them know about the offline payment procedure.', 'erforms') ?></p>
        </div>  
    </div>

    <div class="erf-child-rows" style="<?php echo!empty($options['send_offline_email']) ? '' : 'display:none'; ?>">
        <div class="erf-row">
            <div class="erf-control-label">
                <label><?php _e('From Email', 'erforms'); ?></label>
            </div>
            <div class="erf-control">
                <input type="text" class="erf-input-field" value="<?php echo esc_attr($options['offline_email_from']); ?>" name="offline_email_from" />
                <p class='description'><?php _e('This displays who the message is from, It is recommended to use Domain email address.', 'erforms'); ?></p>
            </div>  
        </div>

        <div class="erf-row">
            <div class="erf-control-label">
                <label><?php _e('From Name', 'erforms'); ?></label>
            </div>
            <div class="erf-control">
                <input type="text" class="erf-input-field" value="<?php echo esc_attr($options['offline_email_from_name']); ?>" name="offline_email_from_name" />
                <p class='description'><?php _e('When used together with the \'From Email\', it creates a from address like Name "&ltemail@address.com&gt"', 'erforms'); ?></p>
            </div>  
        </div>

        <div class="erf-row">
            <div class="erf-control-label">
                <label><?php _e('Subject', 'erforms'); ?></label>
            </div>
            <div class="erf-control">
                <input type="text" class="erf-input-field" value="<?php echo esc_attr($options['offline_email_subject']); ?>" name="offline_email_subject" /><br>
                <p class="description">
                    <?php printf(__('Subject of the mail sent to the user.You can also use <a href="%s" target="_blank">ERF short tags</a> to dynamicaly replace the values.', 'erforms'), admin_url('admin.php?page=erforms-field-shortcodes')); ?>
                </p>
            </div>  
        </div>

        <div>
            <div class="erf-row">
                <div class="erf-control-label">
                    <label><?php _e('Message', 'erforms'); ?></label>
                </div>
                <div class="erf-control">
                    <?php
                    $editor_id = 'offline_email';
                    $settings = array('editor_class' => 'erf-editor');
                    wp_editor($options['offline_email'], $editor_id, $settings);
                    ?>
                    <p class="description">
                        <?php printf(__('Email content to be sent to the user. Create personalized message using <a href="%s" target="_blank">ERF short tags</a> to dynamicaly replace the values.', 'erforms'), admin_url('admin.php?page=erforms-field-shortcodes')); ?>
                    </p>
                </div>  
            </div>
        </div>
    </div>
</div>

<div style="<?php echo $type != 'payment_pending' ? 'display:none' : '' ?>">
    <div class="erf-row">
        <div class="erf-control-label">
            <label><?php _e('Enable Payment Pending (User)', 'erforms'); ?></label>
        </div>
        <div class="erf-control erf-has-child">
            <input class="erf_toggle" type="checkbox" data-has-child="1" name="en_payment_pending_email" value="1" <?php echo $options['en_payment_pending_email'] == 1 ? 'checked' : ''; ?>/>
            <label></label>
            <p class="description"><?php _e('Enable email notification to be sent in payment pending state.', 'erforms') ?></p>
        </div>  
    </div>
    <div class="erf-child-rows" style="<?php echo!empty($options['en_payment_pending_email']) ? '' : 'display:none'; ?>">

        <div class="erf-row">
            <div class="erf-control-label">
                <label><?php _e('From Email', 'erforms'); ?></label>
            </div>
            <div class="erf-control">
                <input type="text" class="erf-input-field" value="<?php echo esc_attr($options['pending_pay_email_from']); ?>" name="pending_pay_email_from" /><br>
                <p class='description'><?php _e('This displays who the message is from, It is recommended to use Domain email address.', 'erforms'); ?></p>
            </div>  
        </div>

        <div class="erf-row">
            <div class="erf-control-label">
                <label><?php _e('From Name', 'erforms'); ?></label>
            </div>
            <div class="erf-control">
                <input type="text" class="erf-input-field" value="<?php echo esc_attr($options['pending_pay_email_from_name']); ?>" name="pending_pay_email_from_name" /><br>
                <p class='description'><?php _e('When used together with the \'From Email\', it creates a from address like Name "&ltemail@address.com&gt"', 'erforms'); ?></p>
            </div>  
        </div>

        <div class="erf-row">
            <div class="erf-control-label">
                <label><?php _e('Subject', 'erforms'); ?></label>
            </div>
            <div class="erf-control">
                <input type="text" class="erf-input-field" value="<?php echo esc_attr($options['pending_pay_email_subject']); ?>" name="pending_pay_email_subject" /><br>
                <p class="description">
                    <?php printf(__('Subject of the mail sent to the user.You can also use <a href="%s" target="_blank">ERF short tags</a> to dynamicaly replace the values.', 'erforms'), admin_url('admin.php?page=erforms-field-shortcodes')); ?>
                </p>
            </div>  
        </div>

        <div class="erf-row">
            <div class="erf-control-label">
                <label><?php _e('Email Content', 'erforms'); ?></label>
            </div>
            <div class="erf-control">
                <?php
                $editor_id = 'payment_pending_email';
                $settings = array('editor_class' => 'erf-editor');
                wp_editor($options['payment_pending_email'], $editor_id, $settings);
                ?>
                <p class="description">
                    <?php printf(__('Email content to be sent to the user. Create personalized message using <a href="%s" target="_blank">ERF short tags</a> to dynamicaly replace the values.', 'erforms'), admin_url('admin.php?page=erforms-field-shortcodes')); ?>
                </p>
            </div>  
        </div>
    </div>


</div>


<div style="<?php echo $type != 'payment_completed' ? 'display:none' : '' ?>">
    <div class="erf-row">
        <div class="erf-control-label">
            <label><?php _e('Enable Payment Completion (User)', 'erforms'); ?></label>
        </div>
        <div class="erf-control erf-has-child">
            <input class="erf_toggle" type="checkbox" data-has-child="1" name="en_payment_completed_email" value="1" <?php echo $options['en_payment_completed_email'] == 1 ? 'checked' : ''; ?>/>
            <label></label>
            <p class="description"><?php _e('Enable email notification to be sent on payment completion.', 'erforms') ?></p>
        </div>  
    </div>

    <div class="erf-child-rows" style="<?php echo!empty($options['en_payment_completed_email']) ? '' : 'display:none'; ?>">

        <div class="erf-row">
            <div class="erf-control-label">
                <label><?php _e('From Email', 'erforms'); ?></label>
            </div>
            <div class="erf-control">
                <input type="text" class="erf-input-field" value="<?php echo esc_attr($options['completed_pay_email_from']); ?>" name="completed_pay_email_from" /><br>
                <p class="description"><?php _e('This displays who the message is from, It is recommended to use Domain email address.', 'erforms') ?></p>
            </div>  
        </div>

        <div class="erf-row">
            <div class="erf-control-label">
                <label><?php _e('From Name', 'erforms'); ?></label>
            </div>
            <div class="erf-control">
                <input type="text" class="erf-input-field" value="<?php echo esc_attr($options['completed_pay_email_from_name']); ?>" name="completed_pay_email_from_name" /><br>
                <p class='description'><?php _e('When used together with the \'From Email\', it creates a from address like Name "&ltemail@address.com&gt"', 'erforms'); ?></p>
            </div>  
        </div>

        <div class="erf-row">
            <div class="erf-control-label">
                <label><?php _e('Subject', 'erforms'); ?></label>
            </div>
            <div class="erf-control">
                <input type="text" class="erf-input-field" value="<?php echo esc_attr($options['completed_pay_email_subject']); ?>" name="completed_pay_email_subject" /><br>
                <p class="description">
                    <?php printf(__('Subject of the mail sent to the user.You can also use <a href="%s" target="_blank">ERF short tags</a> to dynamicaly replace the values.', 'erforms'), admin_url('admin.php?page=erforms-field-shortcodes')); ?>
                </p>
            </div>  
        </div>

        <div class="erf-row">
            <div class="erf-control-label">
                <label><?php _e('Payment Completed Notification', 'erforms'); ?></label>
            </div>
            <div class="erf-control">
                <?php
                $editor_id = 'payment_completed_email';
                $settings = array('editor_class' => 'erf-editor');
                wp_editor($options['payment_completed_email'], $editor_id, $settings);
                ?>
                <p class="description">
                    <?php printf(__('Email content to be sent to the user. Create personalized message using <a href="%s" target="_blank">ERF short tags</a> to dynamicaly replace the values.', 'erforms'), admin_url('admin.php?page=erforms-field-shortcodes')); ?>
                </p>
            </div>  
        </div>
    </div>
</div>

<div style="<?php echo $type != 'payment_completed_admin' ? 'display:none' : '' ?>">
    <div class="erf-row">
        <div class="erf-control-label">
            <label><?php _e('Enable Payment Completion (Admin)', 'erforms'); ?></label>
        </div>
        <div class="erf-control erf-has-child">
            <input class="erf_toggle" type="checkbox" data-has-child="1" name="en_pay_completed_admin_email" value="1" <?php echo $options['en_pay_completed_admin_email'] == 1 ? 'checked' : ''; ?>/>
            <label></label>
            <p class="description"><?php _e('Enable email notification to Admin on payment completion.', 'erforms') ?></p>
        </div>  
    </div>

    <div class="erf-child-rows" style="<?php echo!empty($options['en_pay_completed_admin_email']) ? '' : 'display:none'; ?>">
        <div class="erf-row">
            <div class="erf-control-label">
                <label><?php _e('Recipient(s)', 'erforms'); ?></label>
            </div>
            <div class="erf-control">
                <input type="text" class="erf-input-field" value="<?php echo esc_attr($options['completed_pay_admin_email_to']); ?>" name="completed_pay_admin_email_to" /><br>
                <?php echo __('Email(s) where you want to receive notifications for completed payments. Multiple emails can be given using comma(,) sepration. In case this value is empty, system will send the notification to site admin.', 'erforms') . '(' . get_option('admin_email') . ')'; ?>
            </div>  
        </div>

        <div class="erf-row">
            <div class="erf-control-label">
                <label><?php _e('From Email', 'erforms'); ?></label>
            </div>
            <div class="erf-control">
                <input type="text" class="erf-input-field" value="<?php echo esc_attr($options['completed_pay_admin_email_from']); ?>" name="completed_pay_admin_email_from" /><br>
                <p class="description"><?php _e('This displays who the message is from, It is recommended to use Domain email address.', 'erforms') ?></p>
            </div>  
        </div>

        <div class="erf-row">
            <div class="erf-control-label">
                <label><?php _e('From Name', 'erforms'); ?></label>
            </div>
            <div class="erf-control">
                <input type="text" class="erf-input-field" value="<?php echo esc_attr($options['completed_pay_admin_email_from_name']); ?>" name="completed_pay_admin_email_from_name" /><br>
                <p class='description'><?php _e('When used together with the \'From Email\', it creates a from address like Name "&ltemail@address.com&gt"', 'erforms'); ?></p>
            </div>  
        </div>

        <div class="erf-row">
            <div class="erf-control-label">
                <label><?php _e('Subject', 'erforms'); ?></label>
            </div>
            <div class="erf-control">
                <input type="text" class="erf-input-field" value="<?php echo esc_attr($options['completed_pay_admin_email_subject']); ?>" name="completed_pay_admin_email_subject" /><br>
                <p class="description">
                    <?php printf(__('Subject of the mail sent to the user.You can also use <a href="%s" target="_blank">ERF short tags</a> to dynamicaly replace the values.', 'erforms'), admin_url('admin.php?page=erforms-field-shortcodes')); ?>
                </p>
            </div>  
        </div>

        <div class="erf-row">
            <div class="erf-control-label">
                <label><?php _e('Payment Completed Notification', 'erforms'); ?></label>
            </div>
            <div class="erf-control">
                <?php
                $editor_id = 'pay_completed_admin_email';
                $settings = array('editor_class' => 'erf-editor');
                wp_editor($options['pay_completed_admin_email'], $editor_id, $settings);
                ?>
                <p class="description">
                    <?php printf(__('Email content to be sent to the user. Create personalized message using <a href="%s" target="_blank">ERF short tags</a> to dynamicaly replace the values.', 'erforms'), admin_url('admin.php?page=erforms-field-shortcodes')); ?>
                </p>
            </div>  
        </div>
    </div>
</div>

<div style="<?php echo $type != 'note_to_user' ? 'display:none' : '' ?>">
    <div class="erf-row">
        <div class="erf-control-label">
            <label><?php _e('Enable Submission Note', 'erforms'); ?></label>
        </div>
        <div class="erf-control erf-has-child">
            <input class="erf_toggle" type="checkbox" data-has-child="1" name="en_note_user_email" value="1" <?php echo $options['en_note_user_email'] == 1 ? 'checked' : ''; ?>/>
            <label></label>
            <p class="description"><?php _e('Allow to send notification to User when admin adds note on submission page.', 'erforms') ?></p>
        </div>  
    </div>

    <div class="erf-child-rows" style="<?php echo!empty($options['en_note_user_email']) ? '' : 'display:none'; ?>">
        <div class="erf-row">
            <div class="erf-control-label">
                <label><?php _e('From Email', 'erforms'); ?></label>
            </div>
            <div class="erf-control">
                <input type="text" class="erf-input-field" value="<?php echo esc_attr($options['note_user_email_from']); ?>" name="note_user_email_from" />
                <p class='description'><?php _e('This displays who the message is from, It is recommended to use Domain email address.', 'erforms'); ?></p>
            </div>  
        </div>

        <div class="erf-row">
            <div class="erf-control-label">
                <label><?php _e('From Name', 'erforms'); ?></label>
            </div>
            <div class="erf-control">
                <input type="text" class="erf-input-field" value="<?php echo esc_attr($options['note_user_email_from_name']); ?>" name="note_user_email_from_name" />
                <p class='description'><?php _e('When used together with the \'From Email\', it creates a from address like Name "&ltemail@address.com&gt"', 'erforms'); ?></p>
            </div>  
        </div>

        <div class="erf-row">
            <div class="erf-control-label">
                <label><?php _e('Subject', 'erforms'); ?></label>
            </div>
            <div class="erf-control">
                <input type="text" class="erf-input-field" value="<?php echo esc_attr($options['note_user_email_sub']); ?>" name="note_user_email_sub" /><br>
                <p class="description">
                    <?php printf(__('Subject of the mail sent to the user.You can also use <a href="%s" target="_blank">ERF short tags</a> to dynamicaly replace the values.', 'erforms'), admin_url('admin.php?page=erforms-field-shortcodes')); ?>
                </p>
            </div>  
        </div>

        <div>
            <div class="erf-row">
                <div class="erf-control-label">
                    <label><?php _e('Message', 'erforms'); ?></label>
                </div>
                <div class="erf-control">
                    <?php
                    $editor_id = 'note_user_email';
                    $settings = array('editor_class' => 'erf-editor');
                    wp_editor($options['note_user_email'], $editor_id, $settings);
                    ?>
                    <p class="description">
                        <?php printf(__('Email content to be sent to the user. Create personalized message using <a href="%s" target="_blank">ERF short tags</a> to dynamicaly replace the values.', 'erforms'), admin_url('admin.php?page=erforms-field-shortcodes')); ?>
                    </p>
                </div>  
            </div>
        </div>
    </div>
</div>

<div style="<?php echo $type != 'forgot_password' ? 'display:none' : '' ?>">
    <div class="erf-row">
        <div class="erf-control-label">
            <label><?php _e('Subject', 'erforms'); ?></label>
        </div>
        <div class="erf-control">
            <input type="text" class="erf-input-field" value="<?php echo esc_attr($options['forgot_pass_email_subject']); ?>" name="forgot_pass_email_subject" /><br>
            <p class="description">
                <?php printf(__('Subject of the mail sent to the user.', 'erforms'), admin_url('admin.php?page=erforms-field-shortcodes')); ?>
            </p>
        </div>  
    </div>

    <div>
        <div class="erf-row">
            <div class="erf-control-label">
                <label><?php _e('Message', 'erforms'); ?></label>
            </div>
            <div class="erf-control">
                <?php
                $editor_id = 'forgot_pass_email';
                $settings = array('editor_class' => 'erf-editor');
                wp_editor($options['forgot_pass_email'], $editor_id, $settings);
                ?>
                <p class="description">
                    <?php printf(__('Email content to be sent to the user. <code>{{user_login}}</code> and <code>{{otp}}</code> short tags can be used to dynamically place Username and OTP values.', 'erforms'), admin_url('admin.php?page=erforms-field-shortcodes')); ?>
                </p>
            </div>  
        </div>
    </div>
</div>

<?php if ($tab == 'notifications' && !empty($type)): ?>
    <input type="hidden" name="erf_gs_tab" value="notifications" />
<?php endif; ?>







