<?php

/*
 * System wide utility hooks
 */

class ERForms_Do_Actions {

    public function __construct() {
        add_action('init', array($this, 'load_tasks'));
        add_action('erforms_before_login_button', array($this, 'before_login_button'));
        /* Submission property formatters */
        add_filter('erforms_address_country_formatter_html', array($this, 'country_formatter'), 10, 3);
        add_filter('erforms_address_state_formatter_html', array($this, 'state_formatter'), 10, 3);
        add_filter('erforms_address_country_formatter_csv', array($this, 'country_formatter'), 10, 3);
        add_filter('erforms_address_state_formatter_csv', array($this, 'state_formatter'), 10, 3);
        add_action('wp_ajax_erforms_get_form_for_edit', array($this, 'get_form_for_edit_ajax'));
        add_action('wp_ajax_erforms_delete_submission', array($this, 'delete_submission_ajax'));
        add_action('wp_ajax_erforms_delete_sub_attachment', array($this, 'delete_sub_attachment'));
        add_filter('allowed_redirect_hosts', array($this, 'allowed_redirect_hosts'));
        add_action('erforms_loaded', array($this, 'hide_admin_bar'));
        add_filter('cron_schedules', array($this, 'schedules'));
        add_action('wp_ajax_erf_change_sub_columns', array($this, 'change_sub_columns'));
        add_filter('erf_process_form_html', array($this, 'process_form_html'), 10, 2);
        add_filter('erf_process_edit_form_html', array($this, 'process_form_html'), 10, 2);
        add_action('admin_init', array($this, 'check_compatible_addons'));
    }

    public function country_formatter($country_code, $field_name, $submission) {
        if (empty($country_code))
            return $country_code;

        $countries = erforms_address_country();
        if (!empty($countries[$country_code])) {
            return $countries[$country_code];
        }
        return $country_code;
    }

    // Returns state name on the basis of previous country field
    public function state_formatter($state_code, $field_name, $sub_id) {
        if (empty($state_code) || empty($field_name) || empty($sub_id))
            return $state_code;

        $submission = erforms()->submission->get_submission($sub_id); // Load submission data
        $country_code = '';
        foreach ($submission['fields_data'] as $field) {
            if (!empty($field['f_entity']) && $field['f_entity'] == 'address' && !empty($field['f_entity_property'])) {
                $field['f_type'] = $field['f_entity_property'];
            }
            if ($field['f_type'] == 'country') {
                $country_code = $field['f_val'];
            } else if ($field['f_type'] == 'state') {
                if ($field['f_name'] == $field_name) {
                    if (empty($country_code)) // No country field found
                        return $state_code;
                    break;
                }
                else {
                    $country_code = '';
                }
            }
        }

        if (empty($country_code))
            return $state_code;

        $states = erforms_load_country_states($country_code);
        if (!empty($states) && !empty($states[$country_code][$state_code]))
            return $states[$country_code][$state_code];
        return $state_code;
    }

    public function before_login_button() {
        $options = erforms()->options->get_options();
        $output = do_shortcode($options['social_login']);
        if (!empty($output))
            echo $output;
    }

    public function get_form_for_edit_ajax() {
        $form_id = absint($_POST['form_id']);
        $submission_id = absint($_POST['submission_id']);
        $form = erforms()->form->get_form($form_id);
        $submission = erforms()->submission->get_submission($submission_id);
        // Adding attachment URLs
        if (!empty($submission['attachments'])) {
            foreach ($submission['attachments'] as $index => $attachment) {
                if (wp_attachment_is_image($attachment['f_val'])) {
                    $attachment['image_url'] = erforms_get_attachment_url($attachment['f_val'],$submission['id']);
                } else {
                    $link_url = erforms_get_attachment_url($attachment['f_val'],$submission['id']);;
                    if (!empty($link_url)) {
                        $attachment['link_url'] = $link_url;
                        $attachment['link_label'] = ucwords(get_the_title($attachment['f_val']));
                    }
                }
                $submission['attachments'][$index] = $attachment;
            }
        }

        if (empty($form) || empty($submission)) {
            wp_send_json_error();
        }

        if (erforms_edit_permission($form, $submission)) {
            $response = array();
            $html_generator = ERForms_Form_Render::get_instance();
            $html = '<div class="erf-reg-form-container">' .
                    '<div class="erf-success"></div>' .
                    '<form method="post" enctype="multipart/form-data" class="erf-form erf-front-form" data-parsley-validate="" novalidate="true" autocomplete="off" data-erf-submission-id="' . $submission['id'] . '" data-erf-form-id="' . $form['id'] . '">' .
                    '<div class="erf-errors"></div>' .
                    '<div class="erf-form-html" id="erf_form_' . $form_id . '">' . $html_generator->generate_html_from_json($form) . '</div>' .
                    '<div class="erf-submit-button clearfix"></div>' .
                    '<div class="erf-form-nav clearfix"></div>' .
                    '<input type="hidden" name="erform_id" value="' . $form_id . '" />' .
                    '<input type="hidden" name="erform_submission_nonce" value="' . wp_create_nonce('erform_submission_nonce') . '" />' .
                    '<input type="hidden" name="action" value="erf_submit_form" />';
            if ($form['type'] == 'reg') {
                $html .= '<input type="hidden" name="redirect_to" id="erform_redirect_to" />';
                $html .= '<input type="hidden" name="erf_user" value="' . get_current_user_id() . '" />';
            }


            $html .= '</form></div></div>';
            $html = apply_filters('erf_process_edit_form_html', $html, $form);
            $response['form_html'] = $html;
            $response['localize'] = erforms()->frontend->localize_data($form);
            $response['submission'] = $submission;
            wp_send_json_success($response);
        }

        wp_send_json_error(array('error' => __('You not not allowed to edit this submission', 'erforms')));
    }

    public function load_tasks() {
        // Update plugin version into database.
        $existing_version = get_site_option('erforms_version');
        if (!empty($existing_version)) {
            $current_version = erforms()->version;
            if (version_compare($existing_version, '1.4.5', '<')) {
                erforms_144_plans();
            }
            if (version_compare($existing_version, '1.4.6', '<')) {
                erforms_145_migration();
            }
            if (version_compare($existing_version, '1.7.1', '<')) {
                erforms_17_migration();
            }
            add_action('erf_migration_on_load',$existing_version);
            
            if (version_compare($existing_version, $current_version, '<')) {
                update_site_option('erforms_version', erforms()->version);
            }
        } else {
            update_site_option('erforms_version', erforms()->version);
        }
    }

    public function delete_submission_ajax() {
        $form_id = absint($_POST['form_id']);
        $submission_id = absint($_POST['submission_id']);
        $form = erforms()->form->get_form($form_id);
        $submission = erforms()->submission->get_submission($submission_id);

        if (empty($form) || empty($submission)) {
            wp_send_json_error(array('msg' => __('Operation not allowed', 'erforms')));
        }

        if (erforms_delete_permission($form, $submission)) {
            erforms()->submission->delete(array($submission_id));
            $user = wp_get_current_user();
            wp_schedule_single_event(time() + 100, 'erf_submission_deleted', array($form, $submission, $user));
            wp_send_json_success();
        } else {
            wp_send_json_error(array('msg' => __('Operation not allowed', 'erforms')));
        }
        wp_send_json_error(array('msg' => __('Operation not allowed', 'erforms')));
    }

    // Registers hosts to allow external redirections.
    public function allowed_redirect_hosts($allowed) {
        $options_obj = erforms()->options;
        if (empty($options_obj)) {
            return $allowed;
        }
        $options = erforms()->options->get_options();
        if (!empty($options['logout_redirection'])) {
            $parse = parse_url($options['logout_redirection']);
            if (!empty($parse)) {
                $allowed[] = $parse['host'];
            }
        }

        if (!empty($options['en_role_redirection'])) {
            $roles = erforms_wp_roles_dropdown();
            foreach ($roles as $single) {
                $role = $single['role'];
                if (!empty($options[$role . '_login_redirection'])) {
                    $parse = parse_url($options[$role . '_login_redirection']);
                    if (!empty($parse)) {
                        $allowed[] = $parse['host'];
                    }
                }
            }
        } else if (!empty($options['after_login_redirect_url'])) {
            $parse = parse_url($options['after_login_redirect_url']);
            if (!empty($parse)) {
                $allowed[] = $parse['host'];
            }
        }
        return $allowed;
    }

    public function hide_admin_bar() {
        if (is_admin()) // Should not run for admin screens
            return;
        $options = erforms()->options->get_options();
        if (!empty($options['hide_admin_bar'])) {
            $show = apply_filters('erf_hide_admin_bar', false);
            show_admin_bar($show);
        }
    }

    /*
     * Allows deletion of submission attachment.
     */

    public function delete_sub_attachment() {
        $sub_id = absint($_POST['submission_id']);
        $submission = erforms()->submission->get_submission($sub_id);
        $file_id = absint($_POST['file_id']);
        $field_name = sanitize_text_field(wp_unslash($_POST['f_name']));
        if (empty($submission) || empty($file_id) || empty($field_name)) {
            wp_send_json_error(array('error' => __('Invalid request parameters.', 'erforms')));
        }

        // check ownership. (Only admin or submission owner is allowed to delete the attachment)
        if (!erforms_is_user_admin()) {
            if (empty($submission['user']))
                wp_send_json_error(array('error' => __('Can not identify submission ownership.', 'erforms')));

            $user = wp_get_current_user();
            if ($submission['user']['ID'] != $user->ID) {
                wp_send_json_error(array('error' => __('You are not allowed to delete submission attachment.', 'erforms')));
            }
            // Check if field is editable
            $form = erforms()->form->get_form($submission['form_id']);
            if (empty($form)) {
                wp_send_json_error(array('error' => __('Form does not exists.', 'erforms')));
            }
            if (!in_array($field_name, $form['edit_fields'])) {
                wp_send_json_error(array('error' => __('Attachment deletion not allowed.', 'erforms')));
            }
        }

        /*
         * Looping through submission attachments and submission fields data
         * to make sure data consistency after deletion.
         */
        if (!empty($submission['attachments'])) {
            $attachments = $submission['attachments'];
            foreach ($attachments as $index => $attachment) {
                if ($attachment['f_val'] == $file_id) {
                    $deleted = wp_delete_attachment($file_id);
                    if (!empty($deleted)) {
                        unset($attachments[$index]);
                        foreach ($submission['fields_data'] as $index => $f_data) {
                            if ($f_data['f_name'] == $field_name) {
                                $submission['fields_data'][$index]['f_val'] = '';
                            }
                        }
                    }
                }
            }
            erforms()->submission->update_meta($submission['id'], 'attachments', $attachments);
            erforms()->submission->update($submission);
        }
        wp_send_json_success();
    }

    public function schedules($schedules) {
        $schedules['weekly'] = array(
            'interval' => 604800,
            'display' => __('Once Weekly', 'erforms')
        );
        $schedules['monthly'] = array(
            'interval' => 2635200,
            'display' => __('Once Monthly', 'erforms')
        );
        return $schedules;
    }

    public function change_sub_columns() {
        if (!erforms_is_user_admin()) {
            wp_send_json_success();
        }
        $form_id = isset($_POST['form']) ? absint($_POST['form']) : 0;
        $columns = !empty($_POST['columns']) ? array_map('sanitize_text_field', $_POST['columns']) : array();
        $columns = array_map(function($item) {
            return sanitize_text_field($item);
        }, $columns);
        $form = erforms()->form->get_form($form_id);
        if (empty($form)) {
            wp_send_json_success();
        }
        $form['sub_columns'] = $columns;
        $form = erforms()->form->update_form($form);
        wp_send_json_success();
    }

    public function process_form_html($html, $form) {
        $fields = erforms_get_form_input_fields($form['id']);
        $search = array();
        $replace = array();
        foreach ($fields as $field) {
            array_push($search, '__' . $field['label'] . '__'); // Backward compatibility
            $field['type'] == 'file' ? array_push($replace, '<span class="erf_dynamic_ph_file">__' . $field['label'] . '__</span>') : array_push($replace, '<span class="erf_dynamic_ph">__' . $field['label'] . '__</span>');
            
//            if(!empty($field['dataRefLabel'])){
//                array_push($search, '%' . $field['dataRefLabel'] . '%');
//                $field['type'] == 'file' ? array_push($replace, '<span class="erf_dynamic_ph_file">%' . $field['dataRefLabel'] . '%</span>') : array_push($replace, '<span class="erf_dynamic_ph">%' . $field['dataRefLabel'] . '%</span>');
//            }
            
        }
        $html = str_ireplace($search, $replace, $html);
        return $html;
    }

    public function check_compatible_addons() {
        if (!erforms_is_user_admin()) {
            return;
        }
        // Checking MailChimp compatibility
        if (class_exists('ERF_MC') && defined('ERFORMS_MC_VERSION')) {
            if (version_compare(ERFORMS_MC_VERSION, '1.0.7', '<')) {
                add_action('admin_notices', 'erforms_mailchimp_compatibility_notice');
            }
        }
        // Checking Conditional add-on compatibility
        if (class_exists('ERF_Conditional') && defined('ERFORMS_COND_VERSION')) {
            if (version_compare(ERFORMS_COND_VERSION, '1.1.3', '<')) {
                add_action('admin_notices', 'erforms_conditional_compatibility_notice');
            }
        }
        // Checking GDPR compatibility
        if (class_exists('ERF_GDPR') && defined('ERFORMS_GDPR_VERSION')) {
            if (version_compare(ERFORMS_GDPR_VERSION, '1.0.3', '<')) {
                add_action('admin_notices', 'erforms_gdpr_compatibility_notice');
            }
        }
        // Checking MailPoet compatibility
        if (class_exists('ERF_MP') && defined('ERFORMS_GDPR_VERSION')) {
            if (version_compare(ERFORMS_MP_VERSION, '1.0.1', '<')) {
                add_action('admin_notices', 'erforms_mailpoet_compatibility_notice');
            }
        }
    }
}

new ERForms_Do_Actions();
