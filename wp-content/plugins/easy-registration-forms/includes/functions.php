<?php

/**
 * Performs json_decode and unslash.
 *
 * @since 1.0.0
 *
 * @param string $data
 *
 * @return array|bool
 */
function erforms_decode($data) {

    if (!$data || empty($data)) {
        return false;
    }

    return wp_unslash(json_decode($data, true));
}

/**
 * Performs json_encode and wp_slash.
 *
 * @since 1.3.1.3
 *
 * @param mixed $data
 *
 * @return string
 */
function erforms_encode($data = false) {

    if (empty($data)) {
        return false;
    }

    return wp_slash(wp_json_encode($data));
}

function erforms_dropdown_forms($selected = '') {
    $r = '';
    $forms = erforms()->form->get();
    foreach ($forms as $form) {
        if ($selected == $form->ID) {
            $r .= "\n\t<option selected='selected' value='" . $form->ID . "'>$form->post_title</option>";
        } else {
            $r .= "\n\t<option value='" . $form->ID . "'>$form->post_title</option>";
        }
    }
    echo $r;
}

function erforms_primary_field_types() {
    return array('user_email', 'password');
}

/*
 * Filters user related fields from submission data
 */

function erforms_filter_user_fields($form_id, $data = array()) {
    $form = erforms()->form->get_form($form_id);

    $field_map = array();
    foreach ($form['fields'] as $field_data) {
        if (!empty($field_data['addUserField']) && !empty($field_data['addUserFieldMap'])) {
            $field_map[$field_data['name']] = $field_data['addUserFieldMap'];
        }

        if (in_array($field_data['type'], erforms_primary_field_types())) {
            $field_map[$field_data['type']] = $field_data['name'];
        }
        if (erforms_is_username_field($field_data)) {
            $field_map['username'] = $field_data['name'];
        }
    }
    return $field_map;
}

function erforms_is_username_field($field) {
    if ($field['type'] == 'username') {
        return true;
    }
    return false;
}

/*
 * Sanitizes request data as per field schema
 */

function erforms_sanitize_request_data($fields = array(), $req_type = 'POST') {
    $data = $_POST;
    // Overriding with sanitized values based on the field type
    foreach ($fields as $field) {
        erforms_sanitize_field_data($field, $data);
    }
    return $data;
}

function erforms_sanitize_field_data($field, &$data) {
    if (empty($field))
        return false;

    $field = (object) $field;
    $type = strtolower($field->type);
    switch ($type) {
        case 'user_email':
        case 'email': $data[$field->name] = isset($_POST[$field->name]) ? sanitize_email(wp_unslash($_POST[$field->name])) : '';
            break;
        case 'tel':
        case 'text':
        case 'country':
        case 'zip':
        case 'state':
        case 'city':
        case 'street1':
        case 'street2':
        case 'username':
        case 'date':
        case 'hidden':
        case 'radio-group':
        case 'password': $data[$field->name] = isset($_POST[$field->name]) ? sanitize_text_field(wp_unslash($_POST[$field->name])) : '';
            break;
        case 'textarea': $data[$field->name] = isset($_POST[$field->name]) ? sanitize_textarea_field(wp_unslash($_POST[$field->name])) : '';
            break;
        case 'number': $data[$field->name] = isset($_POST[$field->name]) ? erforms_get_numeric(wp_unslash($_POST[$field->name])) : 0;
            break;
        case 'url': $data[$field->name] = isset($_POST[$field->name]) ? esc_url_raw(wp_unslash($_POST[$field->name])) : '';
            break;
        case 'checkbox-group': if (isset($_POST[$field->name])) {
                $data[$field->name] = wp_unslash($_POST[$field->name]);
                if (is_array($data[$field->name])) {
                    $data[$field->name] = array_map('sanitize_text_field', $data[$field->name]);
                } else {
                    $data[$field->name] = sanitize_text_field($data[$field->name]);
                }
            } else {
                $data[$field->name] = array();
            }
            break;
        case 'select': if (isset($_POST[$field->name])) {
                $data[$field->name] = wp_unslash($_POST[$field->name]);
                if (is_array($data[$field->name])) {
                    $data[$field->name] = array_map('sanitize_text_field', $data[$field->name]);
                } else {
                    $data[$field->name] = sanitize_text_field($data[$field->name]);
                }
            } else {
                $data[$field->name] = !empty($field->multiple) ? array() : '';
            }
            break;
    }

    return $data;
}

function erforms_sample_reg_form() {
    $r1 = wp_generate_password(6, false, false);
    $r2 = wp_generate_password(6, false, false);
    $r3 = wp_generate_password(6, false, false);
    $r4 = wp_generate_password(6, false, false);
    $r5 = wp_generate_password(6, false, false);
    return array
        (
        'settings' => array
            (
            'form_desc' => '',
            'notifications' => ''
        ),
        'fields' => '[{"type":"text","value":"","label":"First Name","advance":"+ Advanced Settings","className":"form-control","name":"text-' . $r1 . '","addUserField":true,"addUserFieldMap":"first_name,display_name"},{"type":"text","value":"","label":"Last Name","advance":"+ Advanced Settings","className":"form-control","name":"text-' . $r2 . '","addUserField":true,"addUserFieldMap":"last_name"},{"type":"user_email","required":true,"value":"","label":"User Email","advance":"+ Advanced Settings","className":"form-control","name":"text-' . $r3 . '"},{"type":"password","required":true,"value":"","label":"Password","advance":"+ Advanced Settings","placeholder":"","className":"form-control","name":"text-' . $r4 . '"},{"type":"button","subtype":"submit","label":"Register","className":"btn btn-default","name":"button-' . $r5 . '","style":"default"}]',
    );
}

function erforms_sample_contact_form() {
    $r1 = wp_rand(3541231, 314431431631);
    $r2 = wp_rand(3541231, 314431431631);
    $r3 = wp_rand(3541231, 31443431631);
    $r4 = wp_rand(3541233, 31443431631);
    $r5 = wp_rand(3541233, 31443431631);
    return array
        (
        'settings' => array
            (
            'form_title' => '',
            'form_desc' => '',
            'notifications' => ''
        ),
        'fields' => '[{"type":"text","maxlength":"50","required":true,"label":"First Name","advance":"+ Advanced Settings","placeholder":"","className":"form-control","name":"text-' . $r1 . '"},{"type":"text","required":true,"label":"Last Name","maxlength":"50","advance":"+ Advanced Settings","className":"form-control","name":"text-' . $r2 . '"},{"type":"email","required":true,"label":"Email","advance":"+ Advanced Settings","className":"form-control","name":"text-' . $r3 . '"},{"type":"textarea","maxlength":"500","rows":"5","required":true,"label":"Message","className":"form-control","name":"textarea-' . $r4 . '"},{"type":"button","label":"Send","subtype":"submit","className":"btn btn-default","name":"button-' . $r5 . '","style":"default"}]'
    );
}

function erforms_default_form_meta($form_type) {
    $meta_input = array('type' => $form_type);

    $meta_input['default_role'] = get_option('default_role');
    $meta_input['auto_user_activation'] = 1;
    $meta_input['access_roles'] = array();
    $meta_input['auto_reply'] = __('Thank you for registering with us. You will soon receive an account activation email. After that you can log into our website through login page.', 'erforms');
    $meta_input['enable_external_url'] = 0;
    $meta_input['external_url'] = '';
    $meta_input['enable_limit'] = 0;
    $meta_input['auto_login'] = 0;
    $meta_input['limit_by_date'] = '';
    $meta_input['limit_by_number'] = 0;
    $meta_input['limit_type'] = 'date';
    $meta_input['enable_login_form'] = 0;
    $meta_input['login_and_register'] = 0;
    $meta_input['enabled_auto_reply'] = 1;
    $meta_input['enable_admin_notification'] = 1;
    $meta_input['admin_notification_msg'] = 'Hello Admin,<br> You have received a new submission. Here are the details: <br><br> {{REGISTRATION_DATA}}';
    $meta_input['enable_act_notification'] = 1;
    $meta_input['user_act_subject'] = 'Account Activation';
    $meta_input['user_act_msg'] = 'Your account has been activated successfully.';

    $meta_input['auto_reply_subject'] = 'Registration';
    $meta_input['admin_notification_from'] = '';
    $meta_input['admin_notification_from_name'] = '';
    $meta_input['unique_id_gen_method'] = 'auto';
    $meta_input['unique_id_padding'] = 0;
    $meta_input['unique_id_offset'] = 1;
    $meta_input['unique_id_index'] = 1;
    $meta_input['unique_id_prefix'] = '';
    $meta_input['recaptcha_enabled'] = 0;
    $meta_input['enable_unique_id'] = 0;
    $meta_input['redirect_to'] = '';
    $meta_input['label_position'] = 'top';
    $meta_input['layout'] = 'one-column';
    $meta_input['field_style'] = 'rounded-corner';
//$meta_input['success_msg']= "Thank you for registering with us!"; // changing success msg
    $meta_input['access_denied_msg'] = 'You are not authorised to access this form.';
    $meta_input['plan_enabled'] = 0;
    $meta_input['limit_message'] = 'Form submission limit reached.';
    $meta_input['admin_notification_to'] = '';
    $meta_input['en_pwd_restriction'] = 0;
    $meta_input['pwd_res_description'] = 'Please answer the question to access the form.';
    $meta_input['pwd_res_question'] = 'Your Password';
    $meta_input['pwd_res_err'] = 'Incorrect password.';
    $meta_input['pwd_res_en_logged_in'] = 0;
    $meta_input['pwd_res_answer'] = '';
    $meta_input['auto_reply_from'] = '';
    $meta_input['auto_reply_from_name'] = '';
    $meta_input['reports'] = array();
    if ($form_type == "reg") {
        $meta_input['auto_reply_msg'] = 'Hello,<br>Thank you for registering with us.';
        $meta_input['admin_notification_subject'] = 'New Submission';
        $meta_input['before_form'] = 'Register with us by filling out the form below.';
        $meta_input['role_choices'] = array();
        $meta_input['role_choice_position'] = '';
        $meta_input['success_msg'] = "Thank you for registering with us!";

        $meta_input['user_act_from'] = '';
        $meta_input['user_act_from_name'] = '';
        $meta_input['allow_re_register'] = 1;
        $meta_input['en_email_verification'] = 0;
        //$meta_input['act_link_expiry']=0;
        $meta_input['user_acc_verification_msg'] = 'Your account has been verified. <br> [erforms_my_account]';
        $meta_input['en_user_ver_msg'] = 0;
        $meta_input['user_ver_from'] = '';
        $meta_input['user_ver_from_name'] = '';
        $meta_input['user_ver_subject'] = 'Account Verification';
        $meta_input['user_ver_email_msg'] = 'Your account is not activated yet. Please follow below given link to activate your account : <br> {{verification_link}}';
        $meta_input['after_user_ver_page'] = 0;
        $meta_input['auto_login_after_ver'] = 0;
        $meta_input['show_before_login_form'] = 0;
    } else {
        $meta_input['auto_reply_msg'] = 'Hello,<br>Thank you for contacting us.';
        $meta_input['admin_notification_subject'] = 'New Contact Request';
        $meta_input['before_form'] = 'Contact us by filling out the form below.';
        $meta_input['success_msg'] = "Thank you for contacting us!";
        $meta_input['allow_only_registered'] = 0;
    }
    $meta_input['auto_reply_to'] = '';
    $meta_input['primary_field'] = '';
    $meta_input['primary_contact_name_field'] = '';
    $meta_input['allow_single_plan'] = 0;
    $meta_input['payment_header'] = __('Payment Details', 'erforms');
    $meta_input['plans'] = array('enabled' => array(), 'required' => array());
    $meta_input['enable_edit_notifications'] = 0;
    $meta_input['edit_sub_user_email'] = __('Your submission edited successfully.');
    $meta_input['edit_sub_user_from'] = '';
    $meta_input['edit_sub_user_from_name'] = '';
    $meta_input['edit_sub_user_subject'] = 'Submission Edited';
    $meta_input['edit_sub_admin_email'] = __('Edit Submission Details are : {{registration_data}}');
    $meta_input['edit_sub_admin_from'] = '';
    $meta_input['edit_sub_admin_from_name'] = '';
    $meta_input['edit_sub_admin_subject'] = 'Submission Edited';
    $meta_input['edit_sub_admin_list'] = '';
    $meta_input['enable_delete_notifications'] = 0;
    $meta_input['delete_sub_user_email'] = __('Your submission deleted successfully.');
    $meta_input['delete_sub_user_from'] = '';
    $meta_input['delete_sub_user_from_name'] = '';
    $meta_input['delete_sub_user_subject'] = 'Submission Deleted';
    $meta_input['delete_sub_admin_email'] = __('Deleted Submission Details are : {{registration_data}}');
    $meta_input['delete_sub_admin_from'] = '';
    $meta_input['delete_sub_admin_from_name'] = '';
    $meta_input['delete_sub_admin_subject'] = 'Submission Deleted';
    $meta_input['delete_sub_admin_list'] = '';
    $meta_input['form_admin_list'] = '';
    $meta_input['en_edit_sub'] = 0;
    $meta_input['allow_sub_deletion'] = 0;
    $meta_input['edit_fields'] = array();
    $meta_input['opt_in'] = 0;
    $meta_input['opt_text'] = __('Subscribe for emails', 'erforms');
    $meta_input['opt_default_state'] = 0;
    $meta_input['limit_time'] = '';
    $meta_input['sub_columns'] = array();
    $meta_input['dis_mul_sub'] = 0;
    $meta_input['mul_sub_denial_msg'] = __('You have already submitted this form.', 'erforms');
    $meta_input = apply_filters('erforms_default_form_meta', $meta_input, $form_type);
    return $meta_input;
}

function erforms_non_input_fields() {
    return array('button', 'splitter', 'header', 'separator', 'richtext');
}

function erforms_get_form_input_fields($form_id) {
    $form_id = absint($form_id);
    $form = erforms()->form->get_form($form_id);
    $fields = array();

    $excluded_fields = erforms_non_input_fields();
    if (is_array($form['fields'])) {
        foreach ($form['fields'] as $field) {
            if (in_array($field['type'], $excluded_fields) || ($field['type'] == 'password'))
                continue;
            array_push($fields, $field);
        }
    }

    return $fields;
}

function erforms_get_fields_tinymce($form_id, $json = true) {
    $form_id = absint($form_id);
    $form = erforms()->form->get_form($form_id);
    $fields = array();

    $excluded_fields = erforms_non_input_fields();
    foreach ($form['fields'] as $field) {
        if (in_array($field['type'], $excluded_fields) || ($field['type'] == 'password'))
            continue;
        array_push($fields, array('text' => $field['label'], 'value' => $field['label']));
    }
    if (!empty($form['enable_unique_id'])) {
        array_push($fields, array('text' => 'UNIQUE_ID', 'value' => 'UNIQUE_ID'));
    }

    return $json ? json_encode($fields) : $fields;
}

function erforms_validate_captcha($g_r_captcha) {
    $options = erforms()->options->get_options();
    if (!empty($options['recaptcha_configured']) && !empty($options['rc_secret_key'])) {
        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $response = wp_remote_get($url . "?secret=" . $options['rc_secret_key'] . "&response=" . $g_r_captcha . "&remoteip=" . $_SERVER['REMOTE_ADDR'], array('timeout' => 10));
        if (is_array($response)) {
            $response = json_decode($response['body']);
        } else {
            return false;
        }

        if (!empty($response) && !empty($response->success)) {
            return true;
        }
        return false;
    }
    return true;
}

/**
 * Helper function to determine if ERForms Admin Page
 *
 * @since 1.0.0
 * @return boolean
 */
function erforms_is_admin_page() {
    $admin_pages = array('erforms-dashboard', 'erforms-overview', 'erforms-submissions', 'erforms-submission', 'erforms-settings', 'erforms-analytics', 'erforms-labels', 'erforms-plans', 'erforms-plan', 'erforms-tools', 'erforms-help', 'erforms-field-shortcodes');
    $admin_pages = apply_filters('erf_admin_pages', $admin_pages);
    $page = isset($_REQUEST['page']) ? sanitize_text_field($_REQUEST['page']) : '';
    if (is_admin() && in_array($page, $admin_pages)) {
        return true;
    }

    return false;
}

function erforms_error_strings() {
    $strings = array(
        'defaultMessage' => __('This value seems to be invalid.', 'erforms'),
        'type' => array(
            'email' => __('This value should be a valid email.', 'erforms'),
            'url' => __('This value should be a valid url.', 'erforms'),
            'number' => __('This value should be a valid number.', 'erforms'),
            'integer' => __('This value should be a valid integer.', 'erforms'),
            'digits' => __('This value should be digits.', 'erforms'),
            'alphanum' => __('This value should be alphanumeric.', 'erforms')
        ),
        'notblank' => __('This value should not be blank.', 'erforms'),
        'required' => __('This value is required.', 'erforms'),
        'pattern' => __('This value seems to be invalid.', 'erforms'),
        'min' => __('This value should be greater than or equal to %s.', 'erforms'),
        'max' => __('This value should be lower than or equal to %s.', 'erforms'),
        'range' => __('This value should be between %s and %s.', 'erforms'),
        'minlength' => __('This value is too short. It should have %s characters or more.', 'erforms'),
        'maxlength' => __('This value is too long. It should have %s characters or fewer.', 'erforms'),
        'length' => __('This value length is invalid. It should be between %s and %s characters long.', 'erforms'),
        'mincheck' => __('You must select at least %s choices.', 'erforms'),
        'maxcheck' => __('You must select %s choices or fewer.', 'erforms'),
        'check' => __('You must select between %s and %s choices.', 'erforms'),
        'equalto' => __('This value should be the same.', 'erforms'),
        'date' => __('Invalid date value', 'erforms'),
        'confirmPassword' => __('These passwords are not similar to each other. Try again?', 'erforms')
    );

    return $strings;
}

function erforms_text_helpers() {
    $help_texts = array(
        'required' => __('Ensure that this field is completed before allowing the form to be submitted.', 'erforms'),
        'label' => __('Label of the field as it appears on forms. Only Alphanumeric,Space,Underscores are allowed. Do not use any other special characters.', 'erforms'),
        'description' => __('Show a helptext of this field on front end.', 'erforms'),
        'dataDateFormat' => __('Accepted date format.', 'erforms'),
        'placeholder' => __('A sample value or a short description of the expected value/format.', 'erforms'),
        'className' => __('Add a custom CSS class.', 'erforms'),
        'masking' => sprintf(__('Allows fixed width in a certain format (dates,phone numbers, etc). <a href="%s" target="_blank">More Details</a>', 'erforms'), 'http://www.easyregistrationforms.com/masked-input-field-using-easy-registration-forms/'),
        'maxlength' => __('Maximum length of the field.', 'erforms'),
        'minlength' => __('Minimum length of the field.', 'erforms'),
        'addUserField' => sprintf(__('Map field value with WordPress user meta. <a href="%s" target="_blank">More Details</a>', 'erforms'), 'http://www.easyregistrationforms.com/how-to-create-custom-user-meta-fields-with-easy-registration-forms/'),
        'addUserFieldMap' => __('Meta key(s) to map with User Account in WordPress. Use comma(,) to separate multiple keys.', 'erforms'),
        'accept' => __('Specify supported file formats separated with single space. For eg. PNG JPG DOC', 'erforms'),
        'min' => __('This specifies the minimum value of the field.', 'erforms'),
        'max' => __('This specifies the maximum value of the field.', 'erforms'),
        'enableUnique' => __('No two submission can have same value for this field.', 'erforms'),
        'pattern' => sprintf(__('The pattern attribute specifies a regular expression that the &lt;input&gt; element\'s value is checked against.<a target="_blank" href="%s">More Details</a>', 'erforms'), 'https://www.w3schools.com/tags/att_input_pattern.asp'),
        'confirmPassword' => __('Displays a confirm password field.', 'erforms'),
        'user_roles' => __('Allow to show user role list to pick from. User will be registered under selected role. <br>*Note: Please do not forget to change "Assign User Role" to "Inherit from Form" in Configuration->User Account settings.', 'erforms'),
        'other' => __('This allows to add any custom option from frontend.', 'erforms'),
        'inline' => __('Displays all the options in one line.', 'erforms'),
        'dataRefLabel' => __('This is for refering fields within the system and will not visible on front end. Length can not be greater than 40 characters.', 'erforms'),
        'dataErfBtnPos' => __("Select where the button is placed.", 'erforms'),
        'enableIntl' => sprintf(__('It adds a flag dropdown to the field to detects the user\'s country.<a target="_blank" href="%s">More Details</a>', 'erforms'), 'https://www.jqueryscript.net/form/jQuery-International-Telephone-Input-With-Flags-Dial-Codes.html'),
        'icon' => __("Display an icon before the field's label.", 'erforms'),
        'richtext' => __("Allows to show formatted HTML code.", 'erforms'),
        'deleteFieldConfirm' => __("Are you sure you want to remove this field?", 'erforms'),
        'width' => __('Sets the width of the element on the form row. Each row has a total width of 12.', 'erforms'),
        'value' => __('Sets the default value for the input field.', 'erforms'),
        'name' => __('This name attribute can be used to reference the element in HTML,Javascript or PHP integration.', 'erforms'),
        'hide' => __('If checked, field will be hidden during form render.', 'erforms'),
        'save' => __('If checked, field value will be saved into the database.', 'erforms'),
    );
    return $help_texts;
}

function erforms_admin_text_helpers() {
    $help_texts = array(
        'sub_del_prompt' => __("Are you sure to delete selected item(s)? Once deleted, you won't be able to recover the data.", 'erforms'),
    );
    return $help_texts;
}

function erforms_get_roles_checkbox($name, $selected) {
    global $wp_roles;

    if (!isset($wp_roles))
        $wp_roles = new WP_Roles();
    $roles = $wp_roles->get_names();
    if (empty($selected) || !is_array($selected))
        $selected = array();
    $html = '';
    foreach ($roles as $role_value => $role_name) {
        if (in_array($role_value, $selected))
            $html .= '<label class="erf-user-roles"><input checked name="' . $name . '[]" type="checkbox" value="' . $role_value . '"><span>' . $role_name . '</span></label>';
        else
            $html .= '<label class="erf-user-roles"><input name="' . $name . '[]" type="checkbox" value="' . $role_value . '"><span>' . $role_name . '</span></label>';
    }
    return $html;
}

function erforms_get_forms_tinymce() {
    $forms = erforms()->form->get();
    $form_names = array(array('text' => 'Login Form', 'value' => 'login_form'));
    foreach ($forms as $form) {
        array_push($form_names, array('text' => $form->post_title, 'value' => $form->ID));
    }
    return json_encode($form_names);
}

/*
 * Formatting submission data before sending to external URL
 */

function erforms_format_submission_for_external($submission) {
    if (empty($submission))
        return array();

    return $submission;
}

function erforms_admin_submission_table($submission, $exclude = array()) {
    if (!is_array($submission))
        return;
    ?>
    <table class="erf-submission-table striped wp-list-table fixed widefat">
        <tbody>
            <tr><th colspan="2" class="erf-submission-title"><?php _e('Submission Date', 'erforms'); ?> : <?php echo $submission['created_date']; ?></th></tr>
    <?php if (!empty($submission['unique_id'])) : ?>
                <tr>
                    <th><?php _e('Unique Submission ID', 'erforms'); ?></th>
                    <td><?php echo $submission['unique_id']; ?></td>
                </tr>
            <?php endif; ?>

    <?php
    $formatter = new ERForms_Submission_Formatter('html', $submission);
    $submission = $formatter->format();
    ?>
            <?php foreach ($submission['fields_data'] as $single): ?>
                <tr>
                <?php echo '<th><label>' . $single['f_label'] . '</label></th>'; ?>
                    <td><?php echo $single['f_val']; ?></td>    
                </tr>

            <?php endforeach; ?>
            <tr>
                <th><?php _e('Modified Date', 'erforms'); ?></th>
                <td><?php echo $submission['modified_date']; ?></td>
            </tr>

            <?php if (!empty($submission['external_url'])) : $external_url = $submission['external_url']; ?>    

                <tr>
                    <th colspan="2"><?php _e('External Request Information', 'erforms'); ?></th>
                </tr>

                <tr>
                    <th><?php _e('URL', 'erforms'); ?></th>
                    <td><?php echo $external_url['url']; ?></td>
                </tr>

                <tr>
                    <th><?php _e('Status', 'erforms'); ?></th>
                    <td><?php $external_url['status'] ? _e('Success', 'erforms') : _e('Failure', 'erforms'); ?></td>
                </tr>

        <?php if (!empty($external_url['error'])) : ?>
                    <tr>
                        <th><?php _e('Error', 'erforms'); ?></th>
                        <td><?php echo $external_url['error']; ?></td>
                    </tr>
                <?php endif; ?>



    <?php endif; ?>    
        </tbody>
    </table>

    <?php
}

function erforms_show_user_fields($user) {
    $active = erforms()->user->get_meta($user->ID, 'active');
    ?>
    <h3><?php _e('Activate User', 'erforms') ?></h3>
    <table class="form-table">
        <tr>
            <th><label for="user_status"><?php _e('Status', 'erforms'); ?></label></th>

            <td>
                <select name="erf_user_status" id="user_status">
                    <option <?php echo $active !== '0' ? 'selected' : '' ?> value="1"><?php _e('Active', 'erforms') ?></option>
                    <option <?php echo $active === '0' ? 'selected' : '' ?> value="0"><?php _e('Deactive', 'erforms') ?></option>
                </select>
                <p class="description"><?php _e("Deactivated users won't be able to login.", 'erforms'); ?></p>
            </td>
        </tr>

    </table>
    <?php
}

function erforms_js_strings() {
    return array('next' => __('Next', 'erforms'), 'prev' => __('Previous', 'erforms'),
        'loading_edit_form' => __('Loading Form...', 'erforms'),
        'edit_form_load_error' => __('Unable to load form data.', 'erforms'),
        'loading_submission_info' => __('Loading submission data...', 'erforms'), 'delete_file' => __('Delete File', 'erforms'), 'other' => __('Other', 'erforms'));
}

function erforms_map_integer($a) {
    return absint($a);
}

/**
 * Get full list of currency codes.
 *
 * @return array
 */
function erforms_currencies() {
    return array(
        'AED' => __('United Arab Emirates dirham', 'erforms'),
        'AFN' => __('Afghan afghani', 'erforms'),
        'ALL' => __('Albanian lek', 'erforms'),
        'AMD' => __('Armenian dram', 'erforms'),
        'ANG' => __('Netherlands Antillean guilder', 'erforms'),
        'AOA' => __('Angolan kwanza', 'erforms'),
        'ARS' => __('Argentine peso', 'erforms'),
        'AUD' => __('Australian dollar', 'erforms'),
        'AWG' => __('Aruban florin', 'erforms'),
        'AZN' => __('Azerbaijani manat', 'erforms'),
        'BAM' => __('Bosnia and Herzegovina convertible mark', 'erforms'),
        'BBD' => __('Barbadian dollar', 'erforms'),
        'BDT' => __('Bangladeshi taka', 'erforms'),
        'BGN' => __('Bulgarian lev', 'erforms'),
        'BHD' => __('Bahraini dinar', 'erforms'),
        'BIF' => __('Burundian franc', 'erforms'),
        'BMD' => __('Bermudian dollar', 'erforms'),
        'BND' => __('Brunei dollar', 'erforms'),
        'BOB' => __('Bolivian boliviano', 'erforms'),
        'BRL' => __('Brazilian real', 'erforms'),
        'BSD' => __('Bahamian dollar', 'erforms'),
        'BTC' => __('Bitcoin', 'erforms'),
        'BTN' => __('Bhutanese ngultrum', 'erforms'),
        'BWP' => __('Botswana pula', 'erforms'),
        'BYR' => __('Belarusian ruble (old)', 'erforms'),
        'BYN' => __('Belarusian ruble', 'erforms'),
        'BZD' => __('Belize dollar', 'erforms'),
        'CAD' => __('Canadian dollar', 'erforms'),
        'CDF' => __('Congolese franc', 'erforms'),
        'CHF' => __('Swiss franc', 'erforms'),
        'CLP' => __('Chilean peso', 'erforms'),
        'CNY' => __('Chinese yuan', 'erforms'),
        'COP' => __('Colombian peso', 'erforms'),
        'CRC' => __('Costa Rican col&oacute;n', 'erforms'),
        'CUC' => __('Cuban convertible peso', 'erforms'),
        'CUP' => __('Cuban peso', 'erforms'),
        'CVE' => __('Cape Verdean escudo', 'erforms'),
        'CZK' => __('Czech koruna', 'erforms'),
        'DJF' => __('Djiboutian franc', 'erforms'),
        'DKK' => __('Danish krone', 'erforms'),
        'DOP' => __('Dominican peso', 'erforms'),
        'DZD' => __('Algerian dinar', 'erforms'),
        'EGP' => __('Egyptian pound', 'erforms'),
        'ERN' => __('Eritrean nakfa', 'erforms'),
        'ETB' => __('Ethiopian birr', 'erforms'),
        'EUR' => __('Euro', 'erforms'),
        'FJD' => __('Fijian dollar', 'erforms'),
        'FKP' => __('Falkland Islands pound', 'erforms'),
        'GBP' => __('Pound sterling', 'erforms'),
        'GEL' => __('Georgian lari', 'erforms'),
        'GGP' => __('Guernsey pound', 'erforms'),
        'GHS' => __('Ghana cedi', 'erforms'),
        'GIP' => __('Gibraltar pound', 'erforms'),
        'GMD' => __('Gambian dalasi', 'erforms'),
        'GNF' => __('Guinean franc', 'erforms'),
        'GTQ' => __('Guatemalan quetzal', 'erforms'),
        'GYD' => __('Guyanese dollar', 'erforms'),
        'HKD' => __('Hong Kong dollar', 'erforms'),
        'HNL' => __('Honduran lempira', 'erforms'),
        'HRK' => __('Croatian kuna', 'erforms'),
        'HTG' => __('Haitian gourde', 'erforms'),
        'HUF' => __('Hungarian forint', 'erforms'),
        'IDR' => __('Indonesian rupiah', 'erforms'),
        'ILS' => __('Israeli new shekel', 'erforms'),
        'IMP' => __('Manx pound', 'erforms'),
        'INR' => __('Indian rupee', 'erforms'),
        'IQD' => __('Iraqi dinar', 'erforms'),
        'IRR' => __('Iranian rial', 'erforms'),
        'IRT' => __('Iranian toman', 'erforms'),
        'ISK' => __('Icelandic kr&oacute;na', 'erforms'),
        'JEP' => __('Jersey pound', 'erforms'),
        'JMD' => __('Jamaican dollar', 'erforms'),
        'JOD' => __('Jordanian dinar', 'erforms'),
        'JPY' => __('Japanese yen', 'erforms'),
        'KES' => __('Kenyan shilling', 'erforms'),
        'KGS' => __('Kyrgyzstani som', 'erforms'),
        'KHR' => __('Cambodian riel', 'erforms'),
        'KMF' => __('Comorian franc', 'erforms'),
        'KPW' => __('North Korean won', 'erforms'),
        'KRW' => __('South Korean won', 'erforms'),
        'KWD' => __('Kuwaiti dinar', 'erforms'),
        'KYD' => __('Cayman Islands dollar', 'erforms'),
        'KZT' => __('Kazakhstani tenge', 'erforms'),
        'LAK' => __('Lao kip', 'erforms'),
        'LBP' => __('Lebanese pound', 'erforms'),
        'LKR' => __('Sri Lankan rupee', 'erforms'),
        'LRD' => __('Liberian dollar', 'erforms'),
        'LSL' => __('Lesotho loti', 'erforms'),
        'LYD' => __('Libyan dinar', 'erforms'),
        'MAD' => __('Moroccan dirham', 'erforms'),
        'MDL' => __('Moldovan leu', 'erforms'),
        'MGA' => __('Malagasy ariary', 'erforms'),
        'MKD' => __('Macedonian denar', 'erforms'),
        'MMK' => __('Burmese kyat', 'erforms'),
        'MNT' => __('Mongolian t&ouml;gr&ouml;g', 'erforms'),
        'MOP' => __('Macanese pataca', 'erforms'),
        'MRO' => __('Mauritanian ouguiya', 'erforms'),
        'MUR' => __('Mauritian rupee', 'erforms'),
        'MVR' => __('Maldivian rufiyaa', 'erforms'),
        'MWK' => __('Malawian kwacha', 'erforms'),
        'MXN' => __('Mexican peso', 'erforms'),
        'MYR' => __('Malaysian ringgit', 'erforms'),
        'MZN' => __('Mozambican metical', 'erforms'),
        'NAD' => __('Namibian dollar', 'erforms'),
        'NGN' => __('Nigerian naira', 'erforms'),
        'NIO' => __('Nicaraguan c&oacute;rdoba', 'erforms'),
        'NOK' => __('Norwegian krone', 'erforms'),
        'NPR' => __('Nepalese rupee', 'erforms'),
        'NZD' => __('New Zealand dollar', 'erforms'),
        'OMR' => __('Omani rial', 'erforms'),
        'PAB' => __('Panamanian balboa', 'erforms'),
        'PEN' => __('Peruvian nuevo sol', 'erforms'),
        'PGK' => __('Papua New Guinean kina', 'erforms'),
        'PHP' => __('Philippine peso', 'erforms'),
        'PKR' => __('Pakistani rupee', 'erforms'),
        'PLN' => __('Polish z&#x142;oty', 'erforms'),
        'PRB' => __('Transnistrian ruble', 'erforms'),
        'PYG' => __('Paraguayan guaran&iacute;', 'erforms'),
        'QAR' => __('Qatari riyal', 'erforms'),
        'RON' => __('Romanian leu', 'erforms'),
        'RSD' => __('Serbian dinar', 'erforms'),
        'RUB' => __('Russian ruble', 'erforms'),
        'RWF' => __('Rwandan franc', 'erforms'),
        'SAR' => __('Saudi riyal', 'erforms'),
        'SBD' => __('Solomon Islands dollar', 'erforms'),
        'SCR' => __('Seychellois rupee', 'erforms'),
        'SDG' => __('Sudanese pound', 'erforms'),
        'SEK' => __('Swedish krona', 'erforms'),
        'SGD' => __('Singapore dollar', 'erforms'),
        'SHP' => __('Saint Helena pound', 'erforms'),
        'SLL' => __('Sierra Leonean leone', 'erforms'),
        'SOS' => __('Somali shilling', 'erforms'),
        'SRD' => __('Surinamese dollar', 'erforms'),
        'SSP' => __('South Sudanese pound', 'erforms'),
        'STD' => __('S&atilde;o Tom&eacute; and Pr&iacute;ncipe dobra', 'erforms'),
        'SYP' => __('Syrian pound', 'erforms'),
        'SZL' => __('Swazi lilangeni', 'erforms'),
        'THB' => __('Thai baht', 'erforms'),
        'TJS' => __('Tajikistani somoni', 'erforms'),
        'TMT' => __('Turkmenistan manat', 'erforms'),
        'TND' => __('Tunisian dinar', 'erforms'),
        'TOP' => __('Tongan pa&#x2bb;anga', 'erforms'),
        'TRY' => __('Turkish lira', 'erforms'),
        'TTD' => __('Trinidad and Tobago dollar', 'erforms'),
        'TWD' => __('New Taiwan dollar', 'erforms'),
        'TZS' => __('Tanzanian shilling', 'erforms'),
        'UAH' => __('Ukrainian hryvnia', 'erforms'),
        'UGX' => __('Ugandan shilling', 'erforms'),
        'USD' => __('United States dollar', 'erforms'),
        'UYU' => __('Uruguayan peso', 'erforms'),
        'UZS' => __('Uzbekistani som', 'erforms'),
        'VEF' => __('Venezuelan bol&iacute;var', 'erforms'),
        'VND' => __('Vietnamese &#x111;&#x1ed3;ng', 'erforms'),
        'VUV' => __('Vanuatu vatu', 'erforms'),
        'WST' => __('Samoan t&#x101;l&#x101;', 'erforms'),
        'XAF' => __('Central African CFA franc', 'erforms'),
        'XCD' => __('East Caribbean dollar', 'erforms'),
        'XOF' => __('West African CFA franc', 'erforms'),
        'XPF' => __('CFP franc', 'erforms'),
        'YER' => __('Yemeni rial', 'erforms'),
        'ZAR' => __('South African rand', 'erforms'),
        'ZMW' => __('Zambian kwacha', 'erforms'),
    );
}

/**
 * Get Currency symbols.
 *
 * @param string $currency (default: '')
 * @return string
 */
function erforms_currency_symbol($currency, $raw = true) {
    $symbols = array(
        'AED' => '&#x62f;.&#x625;',
        'AFN' => '&#x60b;',
        'ALL' => 'L',
        'AMD' => 'AMD',
        'ANG' => '&fnof;',
        'AOA' => 'Kz',
        'ARS' => '&#36;',
        'AUD' => '&#36;',
        'AWG' => 'Afl.',
        'AZN' => 'AZN',
        'BAM' => 'KM',
        'BBD' => '&#36;',
        'BDT' => '&#2547;&nbsp;',
        'BGN' => '&#1083;&#1074;.',
        'BHD' => '.&#x62f;.&#x628;',
        'BIF' => 'Fr',
        'BMD' => '&#36;',
        'BND' => '&#36;',
        'BOB' => 'Bs.',
        'BRL' => '&#82;&#36;',
        'BSD' => '&#36;',
        'BTC' => '&#3647;',
        'BTN' => 'Nu.',
        'BWP' => 'P',
        'BYR' => 'Br',
        'BYN' => 'Br',
        'BZD' => '&#36;',
        'CAD' => '&#36;',
        'CDF' => 'Fr',
        'CHF' => '&#67;&#72;&#70;',
        'CLP' => '&#36;',
        'CNY' => '&yen;',
        'COP' => '&#36;',
        'CRC' => '&#x20a1;',
        'CUC' => '&#36;',
        'CUP' => '&#36;',
        'CVE' => '&#36;',
        'CZK' => '&#75;&#269;',
        'DJF' => 'Fr',
        'DKK' => 'DKK',
        'DOP' => 'RD&#36;',
        'DZD' => '&#x62f;.&#x62c;',
        'EGP' => 'EGP',
        'ERN' => 'Nfk',
        'ETB' => 'Br',
        'EUR' => '&euro;',
        'FJD' => '&#36;',
        'FKP' => '&pound;',
        'GBP' => '&pound;',
        'GEL' => '&#x10da;',
        'GGP' => '&pound;',
        'GHS' => '&#x20b5;',
        'GIP' => '&pound;',
        'GMD' => 'D',
        'GNF' => 'Fr',
        'GTQ' => 'Q',
        'GYD' => '&#36;',
        'HKD' => '&#36;',
        'HNL' => 'L',
        'HRK' => 'Kn',
        'HTG' => 'G',
        'HUF' => '&#70;&#116;',
        'IDR' => 'Rp',
        'ILS' => '&#8362;',
        'IMP' => '&pound;',
        'INR' => '&#8377;',
        'IQD' => '&#x639;.&#x62f;',
        'IRR' => '&#xfdfc;',
        'IRT' => '&#x062A;&#x0648;&#x0645;&#x0627;&#x0646;',
        'ISK' => 'kr.',
        'JEP' => '&pound;',
        'JMD' => '&#36;',
        'JOD' => '&#x62f;.&#x627;',
        'JPY' => '&yen;',
        'KES' => 'KSh',
        'KGS' => '&#x441;&#x43e;&#x43c;',
        'KHR' => '&#x17db;',
        'KMF' => 'Fr',
        'KPW' => '&#x20a9;',
        'KRW' => '&#8361;',
        'KWD' => '&#x62f;.&#x643;',
        'KYD' => '&#36;',
        'KZT' => 'KZT',
        'LAK' => '&#8365;',
        'LBP' => '&#x644;.&#x644;',
        'LKR' => '&#xdbb;&#xdd4;',
        'LRD' => '&#36;',
        'LSL' => 'L',
        'LYD' => '&#x644;.&#x62f;',
        'MAD' => '&#x62f;.&#x645;.',
        'MDL' => 'MDL',
        'MGA' => 'Ar',
        'MKD' => '&#x434;&#x435;&#x43d;',
        'MMK' => 'Ks',
        'MNT' => '&#x20ae;',
        'MOP' => 'P',
        'MRO' => 'UM',
        'MUR' => '&#x20a8;',
        'MVR' => '.&#x783;',
        'MWK' => 'MK',
        'MXN' => '&#36;',
        'MYR' => '&#82;&#77;',
        'MZN' => 'MT',
        'NAD' => '&#36;',
        'NGN' => '&#8358;',
        'NIO' => 'C&#36;',
        'NOK' => '&#107;&#114;',
        'NPR' => '&#8360;',
        'NZD' => '&#36;',
        'OMR' => '&#x631;.&#x639;.',
        'PAB' => 'B/.',
        'PEN' => 'S/.',
        'PGK' => 'K',
        'PHP' => '&#8369;',
        'PKR' => '&#8360;',
        'PLN' => '&#122;&#322;',
        'PRB' => '&#x440;.',
        'PYG' => '&#8370;',
        'QAR' => '&#x631;.&#x642;',
        'RMB' => '&yen;',
        'RON' => 'lei',
        'RSD' => '&#x434;&#x438;&#x43d;.',
        'RUB' => '&#8381;',
        'RWF' => 'Fr',
        'SAR' => '&#x631;.&#x633;',
        'SBD' => '&#36;',
        'SCR' => '&#x20a8;',
        'SDG' => '&#x62c;.&#x633;.',
        'SEK' => '&#107;&#114;',
        'SGD' => '&#36;',
        'SHP' => '&pound;',
        'SLL' => 'Le',
        'SOS' => 'Sh',
        'SRD' => '&#36;',
        'SSP' => '&pound;',
        'STD' => 'Db',
        'SYP' => '&#x644;.&#x633;',
        'SZL' => 'L',
        'THB' => '&#3647;',
        'TJS' => '&#x405;&#x41c;',
        'TMT' => 'm',
        'TND' => '&#x62f;.&#x62a;',
        'TOP' => 'T&#36;',
        'TRY' => '&#8378;',
        'TTD' => '&#36;',
        'TWD' => '&#78;&#84;&#36;',
        'TZS' => 'Sh',
        'UAH' => '&#8372;',
        'UGX' => 'UGX',
        'USD' => '&#36;',
        'UYU' => '&#36;',
        'UZS' => 'UZS',
        'VEF' => 'Bs F',
        'VND' => '&#8363;',
        'VUV' => 'Vt',
        'WST' => 'T',
        'XAF' => 'CFA',
        'XCD' => '&#36;',
        'XOF' => 'CFA',
        'XPF' => 'Fr',
        'YER' => '&#xfdfc;',
        'ZAR' => '&#82;',
        'ZMW' => 'ZK'
    );
    if ($raw)
        $currency_symbol = isset($symbols[$currency]) ? ' (' . $symbols[$currency] . ') ' : '';
    else
        $currency_symbol = isset($symbols[$currency]) ? $symbols[$currency] : '';

    return $currency_symbol;
}

function erforms_payment_method_title($type) {
    $methods = array('offline' => __('Offline', 'erforms'), 'none' => __('None', 'erforms'));
    $methods = apply_filters('erf_payment_method_titles', $methods);
    $title = isset($methods[$type]) ? $methods[$type] : '';
    return $title;
}

function erforms_status_options() {
    return array(ERFORMS_COMPLETED, ERFORMS_PENDING, ERFORMS_HOLD, ERFORMS_DECLINED);
}

function erforms_redirect($url) {
    if (empty($url))
        return false;
    if (headers_sent()) {
        echo '<script>window.location.href="' . esc_url_raw($url) . '";</script>';
        exit();
    } else {
        wp_redirect($url);
        exit();
    }
}

function erforms_wp_roles() {
    if (!function_exists('get_editable_roles')) {
        require_once ABSPATH . 'wp-admin/includes/user.php';
    }
    return get_editable_roles();
}

function erforms_wp_roles_dropdown() {
    $editable_roles = erforms_wp_roles();
    $roles = array();
    foreach ($editable_roles as $role => $details) {
        $sub['role'] = esc_attr($role);
        $sub['name'] = translate_user_role($details['name']);
        $roles[] = $sub;
    }
    return $roles;
}

function erforms_get_selected_role($form_id, $data) {
    $form_model = erforms()->form;
    $form = $form_model->get_form($form_id);
    $selected_role = '';
    foreach ($form['fields'] as $field) {
        if (!empty($field['user_roles'])) {
            if (is_array($field['values'])) {
                foreach ($field['values'] as $role) {
                    if ($data[$field['name']] == $role['value']) {
                        $selected_role = $role['value'];
                        break;
                    }
                }
            }
        }
    }
    return $selected_role;
}

function erforms_submission_parsers() {
    return array('user_role');
}

function erforms_schedule_report($time, $recurrence, $args) {
    $original_args = array('form_id' => (int) $args['form_id'], 'index' => (int) $args['index']);
    // Delete previously added scheduled report with same arguments   
    erforms_delete_scheduled_report($original_args);
    wp_schedule_event($time, $recurrence, 'erf_submission_report', $original_args);
}

function erforms_delete_scheduled_report($args) {
    $timestamp = wp_next_scheduled('erf_submission_report', $args);  // Passing values after conversion
    wp_unschedule_event($timestamp, 'erf_submission_report', $args);
}

/**
 * Converts a period of time in seconds into a human-readable format representing the interval.
 *
 * Example:
 *
 *     echo self::interval( 90 );
 *     // 1 minute 30 seconds
 *
 * @param  int    $since A period of time in seconds.
 * @return string        An interval represented as a string.
 */
function erforms_interval($since) {
// array of time period chunks
    $chunks = array(
        /* translators: 1: The number of years in an interval of time. */
        array(60 * 60 * 24 * 365, _n_noop('%s year', '%s years', 'wp-crontrol')),
        /* translators: 1: The number of months in an interval of time. */
        array(60 * 60 * 24 * 30, _n_noop('%s month', '%s months', 'wp-crontrol')),
        /* translators: 1: The number of weeks in an interval of time. */
        array(60 * 60 * 24 * 7, _n_noop('%s week', '%s weeks', 'wp-crontrol')),
        /* translators: 1: The number of days in an interval of time. */
        array(60 * 60 * 24, _n_noop('%s day', '%s days', 'wp-crontrol')),
        /* translators: 1: The number of hours in an interval of time. */
        array(60 * 60, _n_noop('%s hour', '%s hours', 'wp-crontrol')),
        /* translators: 1: The number of minutes in an interval of time. */
        array(60, _n_noop('%s minute', '%s minutes', 'wp-crontrol')),
        /* translators: 1: The number of seconds in an interval of time. */
        array(1, _n_noop('%s second', '%s seconds', 'wp-crontrol')),
    );

    if ($since <= 0) {
        return __('now', 'wp-crontrol');
    }

// we only want to output two chunks of time here, eg:
// x years, xx months
// x days, xx hours
// so there's only two bits of calculation below:

    $j = count($chunks);

// step one: the first chunk
    for ($i = 0; $i < $j; $i++) {
        $seconds = $chunks[$i][0];
        $name = $chunks[$i][1];

// finding the biggest chunk (if the chunk fits, break)
        $count = floor($since / $seconds);
        if ($count) {
            break;
        }
    }

// set output var
    $output = sprintf(translate_nooped_plural($name, $count, 'wp-crontrol'), $count);

// step two: the second chunk
    if ($i + 1 < $j) {
        $seconds2 = $chunks[$i + 1][0];
        $name2 = $chunks[$i + 1][1];
        $count2 = floor(( $since - ( $seconds * $count ) ) / $seconds2);
        if ($count2) {
// add to output var
            $output .= ' ' . sprintf(translate_nooped_plural($name2, $count2, 'wp-crontrol'), $count2);
        }
    }

    return $output;
}

function erforms_get_default_submission_fields() {
    return array('created_date', 'unique_id', 'amount', 'payment_invoice', 'payment_status', 'user_active', 'user_role', 'modified_date', 'plans', 'edited', 'tags');
}

function erforms_get_report_fields($form_id) {
    $form = erforms()->form->get_form($form_id);
    $form_fields = erforms()->form->get_fields_dropdown($form_id, array('submit', 'password'));

    $other_fields = array();
    // Adding internal fields 
    $other_fields['created_date'] = __('Created Date', 'erforms');
    $other_fields['modified_date'] = __('Modified Date', 'erforms');
    if (!empty($form['enable_unique_id'])) {
        $other_fields['unique_id'] = __('Unique Submission ID', 'erforms');
    }

    if (!empty($form['plan_enabled'])) {
        $other_fields['amount'] = __('Amount', 'erforms');
        $other_fields['payment_invoice'] = __('Invoice', 'erforms');
        $other_fields['payment_status'] = __('Payment Status', 'erforms');
        $other_fields['plans'] = __('Plan(s)', 'erforms');
    }

    if ($form['type'] == 'reg') {
        $other_fields['user_active'] = __('User Status', 'erforms');
        $other_fields['user_role'] = __('User Role', 'erforms');
    }

    $other_fields['tags'] = __('Submission Label(s)', 'erforms');
    $other_fields['edited'] = __('Edited', 'erforms');
    return array_merge(array('id' => __('Submission ID', 'erforms')), $form_fields, $other_fields);
}

function erforms_debug($value) {
    echo '<pre>';
    print_r($value);
}

function erforms_download_file($file, $mime_type, $delete = true) {
    if (ob_get_contents())
        ob_end_clean();

    if (file_exists($file)) {
        header('Content-Description: File Download');
        header('Content-Type:' . $mime_type);
        header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        header('Expires: 0');
        readfile($file);
        if ($delete)
            @unlink($file);
    }
}

function erforms_address_country() {

    $countries = array(
        '' => __('Select Country', 'erforms'),
        'AF' => __('Afghanistan', 'erforms'),
        'AX' => __('Aland Islands', 'erforms'),
        'AL' => __('Albania', 'erforms'),
        'DZ' => __('Algeria', 'erforms'),
        'AS' => __('American Samoa', 'erforms'),
        'AD' => __('Andorra', 'erforms'),
        'AO' => __('Angola', 'erforms'),
        'AI' => __('Anguilla', 'erforms'),
        'AQ' => __('Antarctica', 'erforms'),
        'AG' => __('Antigua and Barbuda', 'erforms'),
        'AR' => __('Argentina', 'erforms'),
        'AM' => __('Armenia', 'erforms'),
        'AW' => __('Aruba', 'erforms'),
        'AU' => __('Australia', 'erforms'),
        'AT' => __('Austria', 'erforms'),
        'AZ' => __('Azerbaijan', 'erforms'),
        'BS' => __('Bahamas', 'erforms'),
        'BH' => __('Bahrain', 'erforms'),
        'BD' => __('Bangladesh', 'erforms'),
        'BB' => __('Barbados', 'erforms'),
        'BY' => __('Belarus', 'erforms'),
        'BE' => __('Belgium', 'erforms'),
        'PW' => __('Belau', 'erforms'),
        'BZ' => __('Belize', 'erforms'),
        'BJ' => __('Benin', 'erforms'),
        'BM' => __('Bermuda', 'erforms'),
        'BT' => __('Bhutan', 'erforms'),
        'BO' => __('Bolivia', 'erforms'),
        'BQ' => __('Bonaire, Saint Eustatius and Saba', 'erforms'),
        'BA' => __('Bosnia and Herzegovina', 'erforms'),
        'BW' => __('Botswana', 'erforms'),
        'BV' => __('Bouvet Island', 'erforms'),
        'BR' => __('Brazil', 'erforms'),
        'IO' => __('British Indian Ocean Territory', 'erforms'),
        'VG' => __('British Virgin Islands', 'erforms'),
        'BN' => __('Brunei', 'erforms'),
        'BG' => __('Bulgaria', 'erforms'),
        'BF' => __('Burkina Faso', 'erforms'),
        'BI' => __('Burundi', 'erforms'),
        'KH' => __('Cambodia', 'erforms'),
        'CM' => __('Cameroon', 'erforms'),
        'CA' => __('Canada', 'erforms'),
        'CV' => __('Cape Verde', 'erforms'),
        'KY' => __('Cayman Islands', 'erforms'),
        'CF' => __('Central African Republic', 'erforms'),
        'TD' => __('Chad', 'erforms'),
        'CL' => __('Chile', 'erforms'),
        'CN' => __('China', 'erforms'),
        'CX' => __('Christmas Island', 'erforms'),
        'CC' => __('Cocos (Keeling) Islands', 'erforms'),
        'CO' => __('Colombia', 'erforms'),
        'KM' => __('Comoros', 'erforms'),
        'CG' => __('Congo (Brazzaville)', 'erforms'),
        'CD' => __('Congo (Kinshasa)', 'erforms'),
        'CK' => __('Cook Islands', 'erforms'),
        'CR' => __('Costa Rica', 'erforms'),
        'HR' => __('Croatia', 'erforms'),
        'CU' => __('Cuba', 'erforms'),
        'CW' => __('Cura&ccedil;ao', 'erforms'),
        'CY' => __('Cyprus', 'erforms'),
        'CZ' => __('Czech Republic', 'erforms'),
        'DK' => __('Denmark', 'erforms'),
        'DJ' => __('Djibouti', 'erforms'),
        'DM' => __('Dominica', 'erforms'),
        'DO' => __('Dominican Republic', 'erforms'),
        'EC' => __('Ecuador', 'erforms'),
        'EG' => __('Egypt', 'erforms'),
        'SV' => __('El Salvador', 'erforms'),
        'GQ' => __('Equatorial Guinea', 'erforms'),
        'ER' => __('Eritrea', 'erforms'),
        'EE' => __('Estonia', 'erforms'),
        'ET' => __('Ethiopia', 'erforms'),
        'FK' => __('Falkland Islands', 'erforms'),
        'FO' => __('Faroe Islands', 'erforms'),
        'FJ' => __('Fiji', 'erforms'),
        'FI' => __('Finland', 'erforms'),
        'FR' => __('France', 'erforms'),
        'GF' => __('French Guiana', 'erforms'),
        'PF' => __('French Polynesia', 'erforms'),
        'TF' => __('French Southern Territories', 'erforms'),
        'GA' => __('Gabon', 'erforms'),
        'GM' => __('Gambia', 'erforms'),
        'GE' => __('Georgia', 'erforms'),
        'DE' => __('Germany', 'erforms'),
        'GH' => __('Ghana', 'erforms'),
        'GI' => __('Gibraltar', 'erforms'),
        'GR' => __('Greece', 'erforms'),
        'GL' => __('Greenland', 'erforms'),
        'GD' => __('Grenada', 'erforms'),
        'GP' => __('Guadeloupe', 'erforms'),
        'GU' => __('Guam', 'erforms'),
        'GT' => __('Guatemala', 'erforms'),
        'GG' => __('Guernsey', 'erforms'),
        'GN' => __('Guinea', 'erforms'),
        'GW' => __('Guinea-Bissau', 'erforms'),
        'GY' => __('Guyana', 'erforms'),
        'HT' => __('Haiti', 'erforms'),
        'HM' => __('Heard Island and McDonald Islands', 'erforms'),
        'HN' => __('Honduras', 'erforms'),
        'HK' => __('Hong Kong', 'erforms'),
        'HU' => __('Hungary', 'erforms'),
        'IS' => __('Iceland', 'erforms'),
        'IN' => __('India', 'erforms'),
        'ID' => __('Indonesia', 'erforms'),
        'IR' => __('Iran', 'erforms'),
        'IQ' => __('Iraq', 'erforms'),
        'IE' => __('Ireland', 'erforms'),
        'IM' => __('Isle of Man', 'erforms'),
        'IL' => __('Israel', 'erforms'),
        'IT' => __('Italy', 'erforms'),
        'CI' => __('Ivory Coast', 'erforms'),
        'JM' => __('Jamaica', 'erforms'),
        'JP' => __('Japan', 'erforms'),
        'JE' => __('Jersey', 'erforms'),
        'JO' => __('Jordan', 'erforms'),
        'KZ' => __('Kazakhstan', 'erforms'),
        'KE' => __('Kenya', 'erforms'),
        'KI' => __('Kiribati', 'erforms'),
        'KW' => __('Kuwait', 'erforms'),
        'KG' => __('Kyrgyzstan', 'erforms'),
        'LA' => __('Laos', 'erforms'),
        'LV' => __('Latvia', 'erforms'),
        'LB' => __('Lebanon', 'erforms'),
        'LS' => __('Lesotho', 'erforms'),
        'LR' => __('Liberia', 'erforms'),
        'LY' => __('Libya', 'erforms'),
        'LI' => __('Liechtenstein', 'erforms'),
        'LT' => __('Lithuania', 'erforms'),
        'LU' => __('Luxembourg', 'erforms'),
        'MO' => __('Macao S.A.R., China', 'erforms'),
        'MK' => __('Macedonia', 'erforms'),
        'MG' => __('Madagascar', 'erforms'),
        'MW' => __('Malawi', 'erforms'),
        'MY' => __('Malaysia', 'erforms'),
        'MV' => __('Maldives', 'erforms'),
        'ML' => __('Mali', 'erforms'),
        'MT' => __('Malta', 'erforms'),
        'MH' => __('Marshall Islands', 'erforms'),
        'MQ' => __('Martinique', 'erforms'),
        'MR' => __('Mauritania', 'erforms'),
        'MU' => __('Mauritius', 'erforms'),
        'YT' => __('Mayotte', 'erforms'),
        'MX' => __('Mexico', 'erforms'),
        'FM' => __('Micronesia', 'erforms'),
        'MD' => __('Moldova', 'erforms'),
        'MC' => __('Monaco', 'erforms'),
        'MN' => __('Mongolia', 'erforms'),
        'ME' => __('Montenegro', 'erforms'),
        'MS' => __('Montserrat', 'erforms'),
        'MA' => __('Morocco', 'erforms'),
        'MZ' => __('Mozambique', 'erforms'),
        'MM' => __('Myanmar', 'erforms'),
        'NA' => __('Namibia', 'erforms'),
        'NR' => __('Nauru', 'erforms'),
        'NP' => __('Nepal', 'erforms'),
        'NL' => __('Netherlands', 'erforms'),
        'NC' => __('New Caledonia', 'erforms'),
        'NZ' => __('New Zealand', 'erforms'),
        'NI' => __('Nicaragua', 'erforms'),
        'NE' => __('Niger', 'erforms'),
        'NG' => __('Nigeria', 'erforms'),
        'NU' => __('Niue', 'erforms'),
        'NF' => __('Norfolk Island', 'erforms'),
        'MP' => __('Northern Mariana Islands', 'erforms'),
        'KP' => __('North Korea', 'erforms'),
        'NO' => __('Norway', 'erforms'),
        'OM' => __('Oman', 'erforms'),
        'PK' => __('Pakistan', 'erforms'),
        'PS' => __('Palestinian Territory', 'erforms'),
        'PA' => __('Panama', 'erforms'),
        'PG' => __('Papua New Guinea', 'erforms'),
        'PY' => __('Paraguay', 'erforms'),
        'PE' => __('Peru', 'erforms'),
        'PH' => __('Philippines', 'erforms'),
        'PN' => __('Pitcairn', 'erforms'),
        'PL' => __('Poland', 'erforms'),
        'PT' => __('Portugal', 'erforms'),
        'PR' => __('Puerto Rico', 'erforms'),
        'QA' => __('Qatar', 'erforms'),
        'RE' => __('Reunion', 'erforms'),
        'RO' => __('Romania', 'erforms'),
        'RU' => __('Russia', 'erforms'),
        'RW' => __('Rwanda', 'erforms'),
        'BL' => __('Saint Barth&eacute;lemy', 'erforms'),
        'SH' => __('Saint Helena', 'erforms'),
        'KN' => __('Saint Kitts and Nevis', 'erforms'),
        'LC' => __('Saint Lucia', 'erforms'),
        'MF' => __('Saint Martin (French part)', 'erforms'),
        'SX' => __('Saint Martin (Dutch part)', 'erforms'),
        'PM' => __('Saint Pierre and Miquelon', 'erforms'),
        'VC' => __('Saint Vincent and the Grenadines', 'erforms'),
        'SM' => __('San Marino', 'erforms'),
        'ST' => __('S&atilde;o Tom&eacute; and Pr&iacute;ncipe', 'erforms'),
        'SA' => __('Saudi Arabia', 'erforms'),
        'SN' => __('Senegal', 'erforms'),
        'RS' => __('Serbia', 'erforms'),
        'SC' => __('Seychelles', 'erforms'),
        'SL' => __('Sierra Leone', 'erforms'),
        'SG' => __('Singapore', 'erforms'),
        'SK' => __('Slovakia', 'erforms'),
        'SI' => __('Slovenia', 'erforms'),
        'SB' => __('Solomon Islands', 'erforms'),
        'SO' => __('Somalia', 'erforms'),
        'ZA' => __('South Africa', 'erforms'),
        'GS' => __('South Georgia/Sandwich Islands', 'erforms'),
        'KR' => __('South Korea', 'erforms'),
        'SS' => __('South Sudan', 'erforms'),
        'ES' => __('Spain', 'erforms'),
        'LK' => __('Sri Lanka', 'erforms'),
        'SD' => __('Sudan', 'erforms'),
        'SR' => __('Suriname', 'erforms'),
        'SJ' => __('Svalbard and Jan Mayen', 'erforms'),
        'SZ' => __('Swaziland', 'erforms'),
        'SE' => __('Sweden', 'erforms'),
        'CH' => __('Switzerland', 'erforms'),
        'SY' => __('Syria', 'erforms'),
        'TW' => __('Taiwan', 'erforms'),
        'TJ' => __('Tajikistan', 'erforms'),
        'TZ' => __('Tanzania', 'erforms'),
        'TH' => __('Thailand', 'erforms'),
        'TL' => __('Timor-Leste', 'erforms'),
        'TG' => __('Togo', 'erforms'),
        'TK' => __('Tokelau', 'erforms'),
        'TO' => __('Tonga', 'erforms'),
        'TT' => __('Trinidad and Tobago', 'erforms'),
        'TN' => __('Tunisia', 'erforms'),
        'TR' => __('Turkey', 'erforms'),
        'TM' => __('Turkmenistan', 'erforms'),
        'TC' => __('Turks and Caicos Islands', 'erforms'),
        'TV' => __('Tuvalu', 'erforms'),
        'UG' => __('Uganda', 'erforms'),
        'UA' => __('Ukraine', 'erforms'),
        'AE' => __('United Arab Emirates', 'erforms'),
        'GB' => __('United Kingdom (UK)', 'erforms'),
        'US' => __('United States (US)', 'erforms'),
        'UM' => __('United States (US) Minor Outlying Islands', 'erforms'),
        'VI' => __('United States (US) Virgin Islands', 'erforms'),
        'UY' => __('Uruguay', 'erforms'),
        'UZ' => __('Uzbekistan', 'erforms'),
        'VU' => __('Vanuatu', 'erforms'),
        'VA' => __('Vatican', 'erforms'),
        'VE' => __('Venezuela', 'erforms'),
        'VN' => __('Vietnam', 'erforms'),
        'WF' => __('Wallis and Futuna', 'erforms'),
        'EH' => __('Western Sahara', 'erforms'),
        'WS' => __('Samoa', 'erforms'),
        'YE' => __('Yemen', 'erforms'),
        'ZM' => __('Zambia', 'erforms'),
        'ZW' => __('Zimbabwe', 'erforms'),
    );

    $countries = apply_filters('erforms_countries', $countries);
    $decoded_countries = array();
    foreach ($countries as $key => $val) {
        $encoded_val = html_entity_decode($val);
        $decoded_countries[$key] = $encoded_val;
    }
    return $decoded_countries;
}

function erforms_address_country_load($command, $form) {
    $field_names = array();
    foreach ($form['fields'] as $field) {
        if (!isset($field['name']))
            continue;
        if ($field['type'] == 'country') {
            array_push($field_names, $field['name']);
        }
    }
    $command['data'] = erforms_address_country();
    $command['on'] = $field_names;
    $command['options'] = true;

    return $command;
}

function erforms_default_field_command() {
    return array('data' => '', 'default_value' => '', 'options' => false, 'on' => array(), 'callback' => '', 'error' => '');
}

// Returns states for imediate state field
function erforms_address_country_change($commands, $form) {
    $command = erforms_default_field_command();
    $field_names = array();
    $source = sanitize_text_field($_POST['field_name']);
    $country_found = false;
    foreach ($form['fields'] as $field) {
        if (!isset($field['name']))
            continue;
        if ($source == $field['name']) {
            $country_found = true;
        }

        if ($field['type'] == 'state' && $country_found) {
            array_push($field_names, $field['name']);
            break;
        }
    }

    $command['on'] = $field_names;
    $field_value = sanitize_text_field($_POST['field_value']);
    $states = erforms_load_country_states($field_value);
    if (empty($states[$field_value])) {
        $command['data'] = array();
    } else {
        $command['data'] = array_merge(array('' => __('Select State/Province', 'erforms')), $states[$field_value]);
    }
    $command['options'] = true;

    array_push($commands, $command);
    return $commands;
}

function erforms_load_country_states($code) {
    $states = array();
    if (file_exists(ERFORMS_PLUGIN_DIR . '/assets/states/' . $code . '.php')) {
        include ERFORMS_PLUGIN_DIR . '/assets/states/' . $code . '.php';
    }
    $states = apply_filters('erforms_states_by_country_code', $states, $code);
    if (isset($states[$code])) {
        $decoded_states = array();
        foreach ($states[$code] as $key => $val) {
            $encoded_val = html_entity_decode($val);
            $decoded_states[$key] = $encoded_val;
        }
        return array($code => $decoded_states);
    }
    return $states;
}

function erforms_front_submission_table($submission, $exclude = array()) {
    if (!is_array($submission))
        return;

    $html = '';
    $html .= '<table class="erf-submission-table striped wp-list-table fixed widefat"><tbody>';
    if (!empty($submission['unique_id'])) {
        $html .= '<tr><th>' . __('Unique Submission ID', 'erforms') . '</th>' .
                '<td>' . $submission['unique_id'] . '</td>' .
                '</tr>';
    }
    $formatter = new ERForms_Submission_Formatter('html', $submission);
    $submission = $formatter->format();

    foreach ($submission['fields_data'] as $single) {
        $html .= '<tr>' .
                '<th><label>' . $single['f_label'] . '</label></th>' .
                '<td>' . $single['f_val'] . '</td>' .
                '</tr>';
    }
    $selected_tags = erforms()->label->tags_by_submission($submission['id']);
    if (!empty($selected_tags)) {
        $tag_html = '';
        foreach ($selected_tags as $tag_label) {
            $tag = erforms()->label->get_label_by_name($tag_label);
            if (empty($tag))
                continue;
            $tag_html .= "<span title='" . $tag['desc'] . "' class='erf-tag' style='background-color:#" . $tag['color'] . "'>$tag_label</span>";
        }

        $html .= '<tr>' .
                '<th><label>' . __('Submission Label(s)', 'erforms') . '</label></th>' .
                '<td>' . $tag_html . '</td>' .
                '</tr>';
    }
    if (!empty($submission['plans'])) {
        $plan_names = array();
        foreach ($submission['plans'] as $row) {
            $plan = erforms()->plan->get_plan($row['id']);
            if (!empty($plan)) {
                array_push($plan_names, $plan['name']);
            }
        }
        $html .= '<tr>' .
                '<th><label>' . __('Payment via', 'erforms') . '</label></th>' .
                '<td>' . erforms_payment_method_title($submission['payment_method']) . '</td>' .
                '</tr>' .
                '<tr>' .
                '<th><label>' . __('Amount', 'erforms') . '</label></th>' .
                '<td>' . erforms_currency_symbol($submission['currency'], false) . $submission['amount'] . '</td>' .
                '</tr>' .
                '<tr>' .
                '<th><label>' . __('Payment Status', 'erforms') . '</label></th>' .
                '<td>' . ucwords($submission['payment_status']) . '</td>' .
                '</tr>' .
                '<tr>' .
                '<th><label>' . __('Payment Invoice', 'erforms') . '</label></th>' .
                '<td>' . $submission['payment_invoice'] . '</td>' .
                '</tr>' .
                '<tr>' .
                '<th><label>' . __('Plan Name', 'erforms') . '</label></th>' .
                '<td>' . implode(', ', $plan_names) . '</td>' .
                '</tr>' .
                '<tr>' .
                '<th><label>' . __('Date', 'erforms') . '</label></th>' .
                '<td>' . $submission['modified_date'] . '</td>' .
                '</tr>';
    }

    $html .= '</tbody></table>';
    return $html;
}

/* Checks for edit permission */

function erforms_edit_permission($form, $submission) {
    $allowed = false;
    if (!is_array($form)) { // Fetch Form data if form ID passed
        $form = erforms()->form->get_form($form);
    }

    if (!is_array($submission)) {
        $submission = erforms()->submission->get_submission($submission);
    }

    if (empty($form) || empty($submission)) {
        $allowed = false;
    } else {
        if (current_user_can('manage_options') && $submission['form_id'] == $form['id']) {
            $allowed = true;
        } else {
            if (empty($form['en_edit_sub'])) {
                $allowed = false;
            } else if (isset($submission['dis_edit_submission']) && !empty($submission['dis_edit_submission'])) {

                $allowed = false;
            } else {
                $current_user = wp_get_current_user();
                if (!empty($current_user->ID)) {
                    if ($submission['form_id'] == $form['id'] && !empty($submission['user']) && $submission['user']['ID'] == $current_user->ID) {
                        $allowed = true;
                    }
                }
            }
        }
    }

    $allowed = apply_filters('erf_edit_submission_allowed', $allowed, $submission, $form);
    return $allowed;
}

/* Checks for view permission */

function erforms_submission_view_permission($form, $submission) {
    $allowed = false;
    if (!is_array($form)) { // Fetch Form data if form ID passed
        $form = erforms()->form->get_form($form);
    }

    if (!is_array($submission)) {
        $submission = erforms()->submission->get_submission($submission);
    }

    if (empty($form) || empty($submission)) {
        $allowed = false;
    } else {
        if (current_user_can('manage_options') && $submission['form_id'] == $form['id']) {
            $allowed = true;
        } else {
            $current_user = wp_get_current_user();
            if (!empty($current_user->ID)) {
                if ($submission['form_id'] == $form['id'] && !empty($submission['user']) && $submission['user']['ID'] == $current_user->ID) {
                    $allowed = true;
                }
            }
        }
    }

    $allowed = apply_filters('erforms_view_submission_check', $allowed, $form, $submission['id']);
    return $allowed;
}

function erforms_is_woocommerce_activated() {
    if (class_exists('WooCommerce')) {
        return true;
    }
    return false;
}

function erforms_non_editable_fields() {
    return array('submit', 'password', 'user_email', 'username', 'hidden');
}

function erforms_is_user_admin() {
    if (current_user_can('manage_options')) {
        return true;
    }
    return false;
}

function erforms_show_opt_in() {
    if (in_array('mailchimp', erforms()->extensions) || in_array('mailpoet', erforms()->extensions)) {
        return true;
    }
    return false;
}

function erforms_duplicate_post($post, $meta = true) {
    global $wpdb;
    if (empty($post) || !erforms_is_user_admin()) {
        return false;
    }

    $current_user = wp_get_current_user();
    $new_post_author = $current_user->ID;

    /*
     * new post data array
     */
    $args = array(
        'comment_status' => $post->comment_status,
        'ping_status' => $post->ping_status,
        'post_author' => $new_post_author,
        'post_content' => $post->post_content,
        'post_excerpt' => $post->post_excerpt,
        'post_name' => $post->post_name,
        'post_parent' => $post->post_parent,
        'post_password' => $post->post_password,
        'post_status' => 'publish',
        'post_title' => $post->post_title . '(Copy)',
        'post_type' => $post->post_type,
        'to_ping' => $post->to_ping,
        'menu_order' => $post->menu_order
    );

    /*
     * insert the post by wp_insert_post() function
     */
    $new_post_id = wp_insert_post($args);

    /*
     * get all current post terms ad set them to the new post draft
     */
    $taxonomies = get_object_taxonomies($post->post_type); // returns array of taxonomy names for post type, ex array("category", "post_tag");
    foreach ($taxonomies as $taxonomy) {
        $post_terms = wp_get_object_terms($post->ID, $taxonomy, array('fields' => 'slugs'));
        wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
    }

    /*
     * duplicate all post meta just in two SQL queries
     */
    if ($meta) {
        $post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post->ID");
        if (count($post_meta_infos) != 0) {
            $sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
            foreach ($post_meta_infos as $meta_info) {
                $meta_key = $meta_info->meta_key;
                if ($meta_key == '_wp_old_slug')
                    continue;
                $meta_value = addslashes($meta_info->meta_value);
                $sql_query_sel[] = "SELECT $new_post_id, '$meta_key', '$meta_value'";
            }
            $sql_query .= implode(" UNION ALL ", $sql_query_sel);
            $wpdb->query($sql_query);
        }
    }


    return $new_post_id;
}

function erforms_rand_hex_color() {
    return sprintf('#%06X', mt_rand(0, 0xFFFFFF));
}

function erforms_system_form_notifications($form_type) {
    $defaults = array('auto_reply' => __('User Auto Reply', 'erforms'), 'admin_notification' => __('Admin Notification', 'erforms'),
        'edit_submission' => __('Edit Submission', 'erforms'), 'delete_submission' => __('Delete Submission', 'erforms'));
    if ($form_type == 'reg') {
        $defaults['user_activation'] = __('User Activation', 'erforms');
        $defaults['user_verification'] = __('User Verification', 'erforms');
    }
    return apply_filters('erf_system_form_notifications', $defaults, $form_type);
}

function erforms_system_notifications() {
    $defaults = array('offline' => __('Offline User Email', 'erforms'), 'payment_pending' => __('Payment Pending(User)', 'erforms'),
        'payment_completed' => __('Payment Completed(User)', 'erforms'), 'payment_completed_admin' => __('Payment Completed(Admin)', 'erforms'),
        'note_to_user' => __('Submission Note', 'erforms'), 'forgot_password' => __('Forgot Password', 'erforms'));

    return apply_filters('erf_system_notifications', $defaults);
}

function erforms_system_form_notification_by_type($type, $form) {
    $notifications = array();
    switch ($type) {
        case 'auto_reply': $notifications = array('enabled' => $form['enabled_auto_reply'], 'recipients' => $form['auto_reply_to'], 'subject' => $form['auto_reply_subject'], 'help' => __('Auto reply emails are sent to the User who is filling the form.', 'erforms'));
            break;
        case 'admin_notification': $notifications = array('enabled' => $form['enable_admin_notification'], 'recipients' => $form['admin_notification_to'], 'subject' => $form['admin_notification_subject'], 'help' => __('These emails are sent to recipient(s) or to site Admin for every submission.', 'erforms'));
            break;
        case 'user_activation': $notifications = array('enabled' => $form['enable_act_notification'], 'recipients' => __("User's Email Address", 'erforms'), 'subject' => $form['user_act_subject'], 'help' => __('This email is sent to the User when user is activated after submission.', 'erforms'));
            break;
        case 'edit_submission': $notifications = array('enabled' => $form['enable_edit_notifications'], 'recipients' => __("User's and Admin Email Address", 'erforms'), 'subject' => $form['edit_sub_user_subject'], 'help' => __('These emails are sent to the User and Admin on every edit submission.', 'erforms'));
            break;
        case 'delete_submission': $notifications = array('enabled' => $form['enable_delete_notifications'], 'recipients' => __("User's and Admin Email Address", 'erforms'), 'subject' => $form['delete_sub_user_subject'], 'help' => __('These emails are sent to the User and Admin on every submission deletion from front end <b>My Account</b> <code>[erforms_my_account]</code>', 'erforms'));
            break;
        case 'user_verification': $notifications = array('enabled' => $form['en_user_ver_msg'], 'recipients' => __("User's Email Address", 'erforms'), 'subject' => $form['user_ver_subject'], 'help' => __('User verification allows to verify User\'s email account.', 'erforms'));
            break;
    }

    $notifications = apply_filters('erf_system_form_notification_by_type', $notifications, $type, $form);
    return $notifications;
}

function erforms_system_notification_by_type($type, $options) {
    $notifications = array();
    switch ($type) {
        case 'offline': $notifications = array('enabled' => $options['send_offline_email'], 'subject' => $options['offline_email_subject'], 'help' => __('Sends email to users to let them know about the payment procedure.', 'erforms'));
            break;
        case 'payment_pending': $notifications = array('enabled' => $options['en_payment_pending_email'], 'subject' => $options['pending_pay_email_subject'], 'help' => __('Email will be sent to user when payment status is pending.', 'erforms'));
            break;
        case 'payment_completed': $notifications = array('enabled' => $options['en_payment_completed_email'], 'subject' => $options['completed_pay_email_subject'], 'help' => __('Email will be sent to user when payment status is completed.', 'erforms'));
            break;
        case 'payment_completed_admin': $notifications = array('enabled' => $options['en_pay_completed_admin_email'], 'subject' => $options['completed_pay_admin_email_subject'], 'help' => __('Email will be sent to Admin when payment status is completed.', 'erforms'));
            break;
        case 'note_to_user': $notifications = array('enabled' => $options['en_note_user_email'], 'subject' => $options['note_user_email_subject'], 'help' => __('Email will be sent to user when admin adds any note within the submission and check "notify user" option.', 'erforms'));
            break;
        case 'forgot_password': $notifications = array('enabled' => 1, 'subject' => $options['forgot_pass_email_subject'], 'help' => __('Forgot password email content.', 'erforms'));
            break;
    }

    $notifications = apply_filters('erforms_system_notification_by_type', $notifications, $type, $options);
    return $notifications;
}

function erforms_global_setting_menus() {
    $defaults = array('general' => array('label' => __('General Settings', 'erforms'), 'desc' => array(__('Default Registration, Upload Directory', 'erforms'))),
        'user_login' => array('label' => __('Login Options', 'erforms'), 'desc' => array(__('Social Login, After Login Redirection, Role Based Login Redirection, reCaptcha, Logout Redirection, Form Layout', 'erforms'))),
        'payments' => array('label' => __('Payments', 'erforms'), 'desc' => array(__('Currency,Offline', 'erforms'))),
        'external' => array('label' => __('External Integration', 'erforms'), 'desc' => array(__('WooCommerce Integration, Google reCaptcha', 'erforms'))),
        'notifications' => array('label' => __('Global Notifications', 'erforms'), 'desc' => array(__('Offline payment, Payment completed,Payment pending', 'erforms'))));

    $menus = apply_filters('erf_global_setting_menus', $defaults);
    return $menus;
}

function erforms_form_configuration_menus($type) {
    $menus = array('general' => array('label' => __('General Settings', 'erforms'), 'desc' => array(__('reCaptcha, Unique Submission ID, Primary Field', 'erforms'))),
        'display' => array('label' => __('Display Settings', 'erforms'), 'desc' => array(__('Layout, Label position, Content above the Form', 'erforms'))),
        'post_sub' => array('label' => __('Post Submission'), 'desc' => array(__('After submission redirection, Success message, Post to external URL', 'erforms'))),
        'restrictions' => array('label' => __('Restrictions', 'erforms'), 'desc' => array(__('Allowed User by role, Password restriction, Limit Submissions', 'erforms'))),
        'edit_sub' => array('label' => __('Edit Submission', 'erforms'), 'desc' => array(__('Enable edit submission', 'erforms'))),
        'plans' => array('label' => __('Plans', 'erforms'), 'desc' => array(__('Configure payment plan(s)', 'erforms')))
    );

    if ($type == 'reg') {
        $menus['general'] = array('label' => __('General Settings', 'erforms'), 'desc' => array(__('reCaptcha,Login Form,Unique Submission ID', 'erforms')));
        $menus['user_account'] = array('label' => __('User Account', 'erforms'), 'desc' => array(__('Auto User activation,Login after registration, User verification link,User role assignment', 'erforms')));
    }
    $menus = apply_filters('erf_form_configuration_menus', $menus, $type);
    return $menus;
}

// Migrate Plan structure for versions less than or equal to 1.4.4
function erforms_144_plans() {
    $plans = erforms()->plan->get_plans();
    foreach ($plans as $plan) {
        if ($plan['type'] == 'fixed') {
            $plan['type'] = 'product';
            erforms()->plan->update_plan($plan);
        }
    }

    // Updating submission data as per the new plan structure
    $args = array(
        'meta_query' => array(
            array(
                'key' => 'erform_amount',
                'compare' => 'EXISTS',
            )
        )
    );
    $posts = erforms()->submission->get('', $args);
    foreach ($posts as $post) {
        $temp = erforms()->submission->get_meta($post->ID, 'plan');
        $plans = array();
        if (is_array($temp)) {
            foreach ($temp as $t) {
                if (isset($t['type']) && $t['type'] == 'fixed') {
                    $t['type'] = 'product';
                    array_push($plans, array('plan' => $t, 'amount' => $t['price'], 'id' => $t['id']));
                } else {
                    $amount = erforms()->submission->get_meta($post->ID, 'amount');
                    array_push($plans, array('plan' => $t, 'amount' => $amount, 'id' => $t['id']));
                }
            }
        }
        erforms()->submission->update_meta($post->ID, 'plans', $plans);
    }

    // Updating form data
    $forms = erforms()->form->get_forms();
    foreach ($forms as $form) {
        if ($form['type'] != 'reg' || empty($form['plan_enabled']))
            continue;
        $plans = array('enabled' => array(), 'required' => array());
        if ($form['plan_type'] == 'fixed') {
            if (!empty($form['fixed_plan_ids'])) {
                $plans['enabled'] = $form['fixed_plan_ids'];
                if ($form['plan_required']) {
                    $plans['required'] = $form['fixed_plan_ids'];
                }
            }
        } else if ($form['plan_type'] == 'user') {
            if (!empty($form['user_plan_id'])) {
                $form['user_plan_id'] = is_array($form['user_plan_id']) ? $form['user_plan_id'] : array($form['user_plan_id']);
                $plans['enabled'] = $form['user_plan_id'];
                if ($form['plan_required']) {
                    $plans['required'] = $form['fixed_plan_ids'];
                }
            }
        }
        $form['plans'] = $plans;
        erforms()->form->update_form($form);
    }
}

// Migrate Plan structure for versions less than or equal to 1.4.5
function erforms_145_migration() {
    // Updating form data for Number padding and offset changes.
    $forms = erforms()->form->get_forms();
    foreach ($forms as $form) {
        if (!empty($form['unique_id_padding'])) {
            $form['unique_id_offset'] = $form['unique_id_padding'];
            $form['unique_id_padding'] = 0;
            erforms()->form->update_form($form);
        }
    }
}

// Creates post meta for all the previous submissions
function erforms_17_migration() {
    $all_forms = erforms()->form->get_forms();
    if (!empty($all_forms)) {
        foreach ($all_forms as $form) {
            $submissions = erforms()->submission->get_submissions_by_form($form['id']);
            foreach ($submissions as $submission) {
                if (!empty($submission['fields_data']) && is_array($submission['fields_data'])) {
                    $meta_values = array();
                    foreach ($submission['fields_data'] as $fd) {
                        if (!empty($fd['f_name']) && !empty($fd['f_val'])) {
                            if ($fd['f_type'] == 'date' && !empty($fd['f_timestamp'])) {
                                $fd['f_val'] = $fd['f_timestamp'];
                            }
                            $meta_values['erform_' . $fd['f_name']] = $fd['f_val'];
                        }
                    }
                    if (!empty($meta_values)) {
                        $post = array(
                            'ID' => $submission['id'],
                            'meta_input' => $meta_values
                        );
                        wp_update_post($post);
                    }
                }
            }
        }
    }
}

function erforms_php_date_format_by_js_format($format) {
    switch ($format) {
        case 'mm/dd/yy' : return 'm/d/Y';
        case 'dd/mm/yy' : return 'd/m/Y';
        case 'dd.mm.yy' : return 'd.m.Y';
        case 'mm-dd-yy' : return 'm-d-Y';
        case 'dd-mm-yy' : return 'd-m-Y';
        case 'yy-mm-dd' : return 'Y-m-d';
        default: return 'm/d/Y';
    }
}

function erforms_get_numeric($val) {
    $val = sanitize_text_field($val);
    $val = str_replace(',', '.', $val); // Some countries uses comma instead of dot. For example: France
    if (is_numeric($val)) {
        return $val + 0;
    }
    return 0;
}

/* Checks for delete permission */

function erforms_delete_permission($form, $submission) {
    $allowed = false;
    if (current_user_can('manage_options') && $submission['form_id'] == $form['id']) {
        $allowed = true;
    } else {
        if (empty($form['en_edit_sub'])) {
            $allowed = false;
        } else if (empty($form['allow_sub_deletion'])) {
            $allowed = false;
        } else if (isset($submission['dis_edit_submission']) && !empty($submission['dis_edit_submission'])) {
            $allowed = false;
        } else {
            $current_user = wp_get_current_user();
            if (!empty($current_user->ID)) {
                if ($submission['form_id'] == $form['id'] && !empty($submission['user']) && $submission['user']['ID'] == $current_user->ID) {
                    $allowed = true;
                }
            }
        }
    }

    $allowed = apply_filters('erf_submission_deletion_allowed', $allowed, $submission, $form);
    return $allowed;
}

function erforms_login_captcha_enabled() {
    $options = erforms()->options->get_options();
    $captcha = !empty($options['recaptcha_configured']) && !empty($options['rc_site_key']) && !empty($options['en_login_recaptcha']) ? true : false;
    $captcha = apply_filters('erf_login_captcha', $captcha);
    return $captcha;
}

function erforms_logout_url() {
    $options = erforms()->options->get_options();
    if (!empty($options['logout_redirection'])) {
        $redirection = apply_filters('erf_logout_url', $options['logout_redirection']);
        return wp_logout_url($redirection);
    }
    return wp_logout_url(get_permalink());
}

function erforms_pass_config($form = null) {
    $conf = array('shortPass' => __('The password is too short.', 'erforms'),
        'badPass' => __('Weak, try combining letters & numbers.', 'erforms'),
        'goodPass' => __('Medium, try using special characters.', 'erforms'),
        'strongPass' => __('Strong password.', 'erforms'),
        'containsUsername' => __('The password contains the username.', 'erforms'),
        'enterPass' => __('Type your password.', 'erforms'),
        'showPercent' => false,
        'showText' => true,
        'animate' => true,
        'animateSpeed' => 'fast',
        'username' => false,
        'usernamePartialMatch' => true,
        'minimumLength' => 4
    );
    // Details about all the above parameters https://github.com/elboletaire/password-strength-meter
    $conf = apply_filters('erf_pass_meter_conf', $conf, $form);
    return $conf;
}

function erforms_tell_config($form) {
    //https://github.com/jackocnr/intl-tel-input
    $conf = array('utilsScript' => ERFORMS_PLUGIN_URL . 'assets/js/intlTelUtil.js', 'separateDialCode' => true, 'initialCountry' => 'auto',
        'geoIpLookup' => 'geo_ip_lookpup_tel', 'formatOnDisplay' => false);
    $conf = apply_filters('erf_tel_conf', $conf, $form);
    return $conf;
}

function erforms_global_localize() {
    global $wp_locale;
    $data = array();
    $data['ajax_url'] = admin_url('admin-ajax.php');
    $data['my_account'] = array('sub_del_confirm_msg' => __("This will delete the submission permanently. Please confirm.", 'erforms'), 'pass_config' => erforms_pass_config());
    $data['parsley'] = erforms_error_strings();
    $data['user_fields'] = array(); //erforms_filter_user_fields($form['id'])
    $data['logged_in'] = is_user_logged_in() ? 1 : 0;
    $data['is_admin'] = erforms_is_user_admin();
    $data['js_strings'] = erforms_js_strings();
    $data['plan'] = erforms()->plan->get_plans();
    $options = erforms()->options->get_options();
    $data['recaptcha_v'] = $options['recaptcha_version'];
    $data['rc_site_key'] = $options['rc_site_key'];
    $data['datepicker_defaults'] = array(
        'closeText' => __('Close'),
        'currentText' => __('Today'),
        'monthNames' => array_values($wp_locale->month),
        'monthNamesShort' => array_values($wp_locale->month_abbrev),
        'nextText' => __('Next'),
        'prevText' => __('Previous'),
        'dayNames' => array_values($wp_locale->weekday),
        'dayNamesShort' => array_values($wp_locale->weekday_abbrev),
        'dayNamesMin' => array_values($wp_locale->weekday_initial),
        'firstDay' => absint(get_option('start_of_week')),
        'isRTL' => $wp_locale->is_rtl(),
    );
    $data = apply_filters('erf_global_localize', $data);
    return $data;
}

function erforms_mailchimp_compatibility_notice() {
    ?>
    <div class="notice notice-success is-dismissible">
        <p><?php _e('MailChimp add-on is not compatible to the core EasyRegistrationForms plugin. Please update it from plugin dashboard.', 'erforms'); ?></p>
    </div>
<?php }

function erforms_conditional_compatibility_notice() {
    ?>
    <div class="notice notice-success is-dismissible">
        <p><?php _e('Conditional add-on is not compatible to the core EasyRegistrationForms plugin. Please update it from plugin dashboard.', 'erforms'); ?></p>
    </div>
<?php }

function erforms_gdpr_compatibility_notice() {
    ?>
    <div class="notice notice-success is-dismissible">
        <p><?php _e('GDPR add-on is not compatible to the core EasyRegistrationForms plugin. Please update it from plugin dashboard.', 'erforms'); ?></p>
    </div>
<?php }

function erforms_mailpoet_compatibility_notice() {
    ?>
    <div class="notice notice-success is-dismissible">
        <p><?php _e('MailPoet add-on is not compatible to the core EasyRegistrationForms plugin. Please update it from plugin dashboard.', 'erforms'); ?></p>
    </div>
<?php
}

function erforms_array_field_sanitizer(&$item, $key, $exclude= array()) {
    if(!in_array($key, $exclude)){
        $item = sanitize_text_field($item);
    }
}

function erforms_field_types($type) {
    $types = array();
    array_push($types, array('key' => 'text', 'label' => __('Text Field', 'erforms'), 'cat' => 'input'));
    array_push($types, array('key' => 'email', 'label' => __('Email', 'erforms'), 'cat' => 'input'));
    array_push($types, array('key' => 'number', 'label' => __('Number', 'erforms'), 'cat' => 'input'));
    array_push($types, array('key' => 'date', 'label' => __('Date', 'erforms'), 'cat' => 'input'));
    array_push($types, array('key' => 'url', 'label' => __('URL', 'erforms'), 'cat' => 'input'));
    array_push($types, array('key' => 'tel', 'label' => __('Phone', 'erforms'), 'cat' => 'input'));
    array_push($types, array('key' => 'file', 'label' => __('File Upload', 'erforms'), 'cat' => 'input'));
    array_push($types, array('key' => 'radio_group', 'label' => __('Radio Group', 'erforms'), 'cat' => 'input'));
    array_push($types, array('key' => 'checkbox_group', 'label' => __('Checkbox Group', 'erforms'), 'cat' => 'input'));
    array_push($types, array('key' => 'select', 'label' => __('Select', 'erforms'), 'cat' => 'input'));
    array_push($types, array('key' => 'textarea', 'label' => __('Textarea', 'erforms'), 'cat' => 'input'));
    array_push($types, array('key' => 'hidden', 'label' => __('Hidden', 'erforms'), 'cat' => 'input'));
    array_push($types, array('key' => 'button', 'label' => __('Button', 'erforms'), 'cat' => 'input'));
    array_push($types, array('key' => 'address', 'label' => __('Address', 'erforms'), 'cat' => 'input'));

    if ($type == 'reg') {
        array_push($types, array('key' => 'username', 'label' => __('Username', 'erforms'), 'cat' => 'input'));
        //array_push($types,array('user_email'=>'textbox','label'=>__('User Email','erforms'),'cat'=>'input'));
    }


    array_push($types, array('key' => 'header', 'label' => __('Header', 'erforms'), 'cat' => 'display'));
    array_push($types, array('key' => 'richtext', 'label' => __('Rich Text', 'erforms'), 'cat' => 'display'));
    array_push($types, array('key' => 'splitter', 'label' => __('Splitter', 'erforms'), 'cat' => 'display'));
    array_push($types, array('key' => 'separator', 'label' => __('Separator', 'erforms'), 'cat' => 'display'));

    // WordPress fields
    array_push($types, array('key' => 'first_name', 'label' => __('First Name', 'erforms'), 'cat' => 'wordpress_profile'));
    array_push($types, array('key' => 'last_name', 'label' => __('Last Name', 'erforms'), 'cat' => 'wordpress_profile'));
    array_push($types, array('key' => 'nick_name', 'label' => __('Nick Name', 'erforms'), 'cat' => 'wordpress_profile'));
    array_push($types, array('key' => 'bio', 'label' => __('Biographical Info', 'erforms'), 'cat' => 'wordpress_profile'));
    array_push($types, array('key' => 'website', 'label' => __('Website', 'erforms'), 'cat' => 'wordpress_profile'));
    
    // Function fields
    array_push($types, array('key' => 'formula', 'label' => __('Formula', 'erforms'), 'cat' => 'function'));
    return $types;
}

function erforms_short_tags($form, $submission) {
    $short_tags = array();
    $registration_html = '';

    if (!empty($submission['unique_id'])) {
        $short_tags['{{unique_id}}'] = $submission['unique_id'];
        $registration_html = '<div>' . __('Unique Submission ID', 'erforms') . ': ' . $submission['unique_id'] . '</div><br>';
    }
    foreach ($submission['fields_data'] as $field) {
        if (empty($field['f_label']))
            contnue;
        if (is_array($field['f_val'])) {
            $field['f_val'] = implode(',', $field['f_val']);
        }
        if ($field['f_type'] == 'file' && !empty($field['f_val'])) {
            if (wp_attachment_is_image($field['f_val'])) {
                $field['f_val'] = '<a target="_blank" href="' . esc_url(erforms_get_attachment_url($field['f_val'],$submission['id'])) . '">' . __('View File', 'erforms') . '</a>';
            } else {
                $url = erforms_get_attachment_url($field['f_val'],$submission['id']);
                $field['f_val'] = '<a target="_blank" href="' . esc_url($url) . '">' . __('View File', 'erforms') . '</a>';
            }
        }
        $short_tags['{{' . $field['f_label'] . '}}'] = $field['f_val'];
        $registration_html .= '<div>' . $field['f_label'] . ': ' . $field['f_val'] . '</div> <br>';
    }

    if (!empty($submission['plans'])) {
        $short_tags['{{plan_amount}}'] = erforms_currency_symbol($submission['currency'], false) . $submission['amount'];
        $short_tags['{{plan_payment_status}}'] = ucwords($submission['payment_status']);
        $short_tags['{{plan_invoice}}'] = $submission['payment_invoice'];
        $short_tags['{{plan_payment_method}}'] = erforms_payment_method_title($submission['payment_method']);
        $payment_html = '';
        $payment_html .= '<div>' . __('Amount', 'erforms') . ': ' . erforms_currency_symbol($submission['currency'], false) . $submission['amount'] . '</div> <br>';
        $payment_html .= '<div>' . __('Payment Status', 'erforms') . ': ' . ucwords($submission['payment_status']) . '</div> <br>';
        $payment_html .= '<div>' . __('Payment Invoice', 'erforms') . ': ' . $submission['payment_invoice'] . '</div> <br>';
        $payment_html .= '<div>' . __('Payment Method', 'erforms') . ': ' . erforms_payment_method_title($submission['payment_method']) . '</div> <br>';
        $short_tags['{{payment_details}}'] = $payment_html;
        $registration_html .= $payment_html;
    }
    if (!empty($submission['user'])) {
        $user = get_user_by('ID', $submission['user']['ID']);
        if (!empty($user)) {
            $short_tags['{{display_name}}'] = $user->display_name;
            $short_tags['{{current_user}}'] = $user->user_email;
            $short_tags['{{user_email}}'] = $user->user_email;
            $short_tags['{{user_login}}'] = $user->user_login;
            $short_tags['{{user_display_name}}'] = $user->display_name;
            $short_tags['{{user_nice_name}}'] = $user->user_nicename;
            $short_tags['{{user_firstname}}'] = $user->first_name;
            $short_tags['{{user_lastname}}'] = $user->last_name;
            $short_tags['{{user_id}}'] = $user->ID;
        }
    } else if (!empty($submission['primary_field_val'])) {
        $display_name = !empty($submission['primary_contact_name_val']) ? $submission['primary_contact_name_val'] : $submission['primary_field_val'];
        $short_tags['{{display_name}}'] = $display_name;
    }

    $short_tags['{{registration_data}}'] = $registration_html;
    $short_tags['{{submission_data}}'] = $registration_html;
    return $short_tags;
}

function erforms_forgot_pass_email_content() {
    $site_name = erforms_site_name();
    $message = __('Someone has requested a password reset for the following account:') . "\r\n\r\n";
    $message .= sprintf(__('Site Name: %s'), $site_name) . "\r\n\r\n";
    $message .= sprintf(__('Username: %s'), '{{user_login}}') . "\r\n\r\n";
    $message .= __('If this was a mistake, just ignore this email and nothing will happen.') . "\r\n\r\n";
    $message .= __('To reset your password, use the OTP: ' . '{{otp}}') . "\r\n\r\n";
    return $message;
}

function erforms_site_name() {
    return is_multisite() ? get_network()->site_name : wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
}

/**
 * Wrapper to call update_user_meta function. 
 * This simply calls wordpress meta function and does not add any special prefix. 
 * Checks for any special meta key to update user table data. 
 * For example: display_name : updates user's display name. Instead of adding display_name usermeta
 */
function erforms_update_user_meta($user_id, $m_key, $m_val) {
    switch ($m_key) {
        case 'display_name' : $status = wp_update_user(array('ID' => $user_id, $m_key => $m_val));
            return is_wp_error($status) ? false : true;
    }
    return update_user_meta($user_id, $m_key, $m_val);
}

function erforms_update_post_meta($post_id,$meta_key,$meta_value,$prefix = true){
    if($prefix){
        $meta_key = 'erform_'.$meta_key;
    }
    return update_post_meta($post_id,$meta_key, $meta_value);
}

function erforms_get_post_meta($post_id,$meta_key='',$prefix = true){
    if($prefix && !empty($meta_key)){
        $meta_key = 'erform_'.$meta_key;
    }
    return get_post_meta($post_id,$meta_key,true);
}

function erforms_get_attachment_url($post_id, $submission_id,$size=''){
    if(wp_attachment_is_image($post_id)){
        $image = wp_get_attachment_image_src($post_id,$size);
        $url = $image ? $image[0] : '';
    } else {
        $url = wp_get_attachment_url($post_id);
    }
    return apply_filters('erf_attachment_url',$url, $post_id, $submission_id,$size);
}

function erforms_get_attachment_image($post_id, $submission_id){
    $img = wp_get_attachment_image_src($post_id,'medium');
    $url = $img[0];
    return apply_filters('erf_attachment_image',$url, $post_id, $submission_id);
}


