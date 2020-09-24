<?php

/**
 * Main form handler
 *
 * Contains a bunch of helper methods as well.
 *
 * @package    ERForms
 * @author     ERForms
 * @since      1.0.0
 */
class ERForms_Form extends ERForms_Post {

    protected $post_type = 'erforms';
    private static $instance = null;

    private function __construct() {
        // Register erforms custom post type
        $this->register_cpt();

        // Add erforms to new-content admin bar menu
        add_action('admin_bar_menu', array($this, 'admin_bar'), 99);
        add_action('wp_ajax_erf_new_form', array($this, 'ajax_add_form'));
        add_action('wp_ajax_erf_save_form', array($this, 'ajax_save_form'));
        add_action('wp_ajax_erf_get_form', array($this, 'get_form_ajax'));
        add_action('wp_ajax_nopriv_erf_get_form', array($this, 'get_form_ajax'));
        add_filter('erforms_before_form_processing', array($this, 'check_submission_limits'), 10, 2);
        add_filter('erforms_before_form_processing', array($this, 'check_form_user_restrictions'), 10, 2);
        add_filter('erforms_parse_success_message', array($this, 'parse_fields_in_content'), 10, 2);
        add_filter('erf_filter_before_form_msg',array($this,'before_form_msg'),10,2);
        $this->initialize_form_field_hooks();
    }

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Registers the custom post type to be used for forms.
     *
     * @since 1.0.0
     */
    public function register_cpt() {

        // Custom post type arguments, which can be filtered if needed
        $args = apply_filters(
                'erforms_post_type_args', array(
            'labels' => array(),
            'public' => false,
            'exclude_from_search' => true,
            'show_ui' => false,
            'show_in_admin_bar' => false,
            'rewrite' => false,
            'query_var' => false,
            'can_export' => false,
            'supports' => array('title'),
                )
        );

        // Register the post type for Forms
        register_post_type(ERFORMS_FORM_POST_TYPE, $args);
    }

    /**
     * Adds "ERForm" item to new-content admin bar menu item.
     *
     * @since 1.1.7.2
     *
     * @param object $wp_admin_bar
     */
    public function admin_bar($wp_admin_bar) {

        if (!is_admin_bar_showing() || !current_user_can(apply_filters('erforms_manage_cap', 'manage_options'))) {
            return;
        }

        $args = array(
            'id' => 'erform',
            'title' => 'ERForm',
            'href' => admin_url('admin.php?page=erforms-dashboard'),
            'parent' => 'new-content',
        );
        $wp_admin_bar->add_node($args);
    }

    public function get_form($post) {
        if (!($post instanceof WP_Post)) {
            $post = $this->get($post);
        }

        if (empty($post->ID))
            return false;

        $form = $this->get_meta($post->ID, 'form_data');
        $form['title'] = $post->post_title;
        $form['id'] = $post->ID;
        $form['fields'] = $form['fields'];
        
        // Change form fields for backward compatibility before version 2.0
        foreach($form['fields'] as $index=>$field){
            if($field['type']=='button' || $field['type']=='header') // Allow subtype property only with button and header fields
                continue;
            
            if(!empty($field['entityProperty'])){
                $field['type'] = $field['entityProperty'];
                unset($field['entityType']);
                unset($field['entityProperty']);
            }
            
            if(!empty($field['type']) && !empty($field['subtype'])){
                if($field['type']=='text'){
                    $field['type']= $field['subtype'];
                }
            }
            
            if(!empty($field['customType'])){
                if($field['customType']=='page-break'){
                    $field['type'] = 'splitter';
                } else if($field['customType']=='spacer'){
                    $field['type'] = 'separator';
                }
            }
            if(isset($field['subtype'])){
                unset($field['subtype']);
            }
            if($field['type']=='paragraph'){
                $field['type']= 'richtext';
            }
            $form['fields'][$index] = $field;
        }
        $default_meta_values = erforms_default_form_meta($form['type']);
        foreach ($default_meta_values as $key => $val) {
            if (!isset($form[$key])) {
                if (isset($default_meta_values[$key]))
                    $form[$key] = $default_meta_values[$key];
                else
                    $form[$key] = '';
            }
        }
        $form = apply_filters('erf_get_form_object',$form);
        return $form;
    }

    public function get_user_field_from_schema($form_schema) {
        if (empty($form_schema) || empty($form_schema['fields']) || !is_array($form_schema['fields']))
            return false;

        foreach ($form_schema['fields'] as $key => $field) {
            if ($field['type'] == "user_email") {
                return $field;
            }
        }
    }

    public function is_user_role_allowed($form) {
        if (!is_array($form)) {
            $form = $this->get_form($form);
        }

        $current_user = wp_get_current_user();
        $current_user_roles = (array) $current_user->roles;
        $allowed = false;
        if (!empty($form['access_roles'])) {
            foreach ($current_user_roles as $role) {
                if (in_array($role, $form['access_roles'])) {
                    $allowed = true;
                    break;
                }
            }
        } else {
            $allowed = true;
        }

        return $allowed;
    }

    public function ajax_add_form() {
        if (!current_user_can('administrator'))
            wp_die('Operation now allowed');

        $form_title = sanitize_text_field(wp_unslash($_POST['title']));
        $form_type = sanitize_text_field(wp_unslash($_POST['form_type']));
        $form_type = empty($form_type) ? "reg" : $form_type;
        $form_id = $this->add_form($form_title, $form_type);
        if ($form_id) {
            $response = array(
                'id' => $form_id,
                'redirect' => add_query_arg(
                        array(
                    'tab' => 'build',
                    'form_id' => $form_id,
                    'newform' => '1',
                        ), admin_url('admin.php?page=erforms-dashboard')
                ),
            );
            wp_send_json_success($response);
        } else {
            die(__('Error creating form', 'erforms'));
        }
    }

    /**
     * Create a new form
     *
     * @since 1.0.0
     */
    public function add_form($form_title, $form_type = 'reg') {
        // Create form
        $data = array();

        if ($form_type == "reg") {
            $form_content = erforms_sample_reg_form();
        } elseif ($form_type == "contact") {
            $form_content = erforms_sample_contact_form();
        }

        $form_content = apply_filters('erf_default_form_fields', $form_content, $form_type);
        $form_content['title'] = $form_title;
        $form_content['fields'] = erforms_decode($form_content['fields']);

        $all_meta = erforms_default_form_meta($form_type);
        foreach ($all_meta as $key => $value) {
            $form_content[$key] = $value;
        }
        // Updating dynamic form attributes
        $form_content['auto_reply_subject'] = $form_title . ' ' . $form_content['auto_reply_subject'];
        $args = array(
            'post_title' => $form_title,
            'post_content' => 'Form Data',
            'post_type' => $this->post_type,
            'post_status' => 'publish',
            'meta_input' => array('erform_form_data' => $form_content)
        );

        $form_id = $this->add($args);
        return $form_id;
    }

    public function update_form($form) {
        if (empty($form))
            return false;
        // Update post 
        $post = array(
            'ID' => $form['id'],
            'post_title' => $form['title'],
            'meta_input' => wp_slash(array('erform_form_data' => $form))
        );
        wp_update_post($post);
    }
    
    // Save form schema for admin user.
    public function ajax_save_form() {
        $errors = array();
        // Check for form data
        if (empty($_POST['data'])) {
            $errors[] = array('key', __('No data provided', 'erforms'));
            wp_send_json_error(array('errors' => $errors));
        }

        if (!current_user_can('administrator')) {
            $errors[] = array('key', __('Operation now allowed', 'erforms'));
            wp_send_json_error(array('errors' => $errors));
        }

        $data = wp_unslash($_POST['data']);
        $form_id = absint($data['id']);
        if (empty($form_id)) {
            $errors[] = array('key', __('No such Form exists', 'erforms'));
            wp_send_json_error(array('errors' => $errors));
        }

        $form = $this->get_form($form_id);
        if (empty($form)) {
            $errors[] = array('key', __('No such Form exists', 'erforms'));
            wp_send_json_error(array('errors' => $errors));
        }

        $title = sanitize_text_field($data['title']);
        if(empty($title)){
            $errors[] = array('key' => __('Invalid form title.', 'erforms'));
            wp_send_json_error($errors);
        }
        $fields = json_decode($data['fields'],true);
        if (empty($fields)) { //Invalid data, Do not save data
            $errors[] = array('key' => __('Invalid form data.', 'erforms'));
            wp_send_json_error($errors);
        }
        $form['fields'] = $fields;
        array_walk_recursive($form['fields'],'erforms_array_field_sanitizer', array('expression'));
        if (is_array($fields)) {
            foreach ($fields as $index => $form_field) {
                if (!empty($form_field['type']) && !in_array($form_field['type'],erforms_non_input_fields())) {
                    $form_field['label'] = sanitize_text_field($form_field['label']);
                    if(empty($form_field['label'])){
                        $errors[] = array('key' => __('Please make sure to provide labels for all the fields.', 'erforms'), 'field_index' => $index);
                    }
                }

                if (isset($form_field['values']) && is_array($form_field['values'])) {
                    foreach ($form_field['values'] as $i => $single) {
                        if (isset($single['label'])) {
                            $single['label'] = sanitize_text_field($single['label']);
                        }
                        if (isset($single['value'])) {
                            $single['value'] = sanitize_text_field($single['value']);
                        }
                        $form['fields'][$index]['values'][$i] = $single;
                    }
                }
                if(!empty($form_field['type']) && in_array($form_field['type'],erforms_non_input_fields())){
                    $form['fields'][$index]['label'] = wp_kses_post($form_field['label']);
                }
            }
            if (!empty($errors)) {
                wp_send_json_error(array('errors' => $errors));
            }
        }
        
        $form['id'] = $form_id;
        $form['title'] = $title;
        if (!$form['id']) {
            $errors[] = array('key' => __('An error occurred and the form could not be saved', 'erforms'));
            wp_send_json_error(array('errors' => $errors));
        } else {
            $this->update_form($form);
            wp_send_json_success(
                    array(
                        'form_desc' => $form['settings']['form_desc'],
                        'redirect' => admin_url('admin.php?page=erforms-dashboard&form_id=' . $form['id']),
                    )
            );
        }
	
    }

    // Returns true if allowed
    public function check_limit_by_date($form) {
        if (empty($form['limit_by_date']))
            return true;
        if (!empty($form['limit_time'])) {
            $limit_date = DateTime::createFromFormat('!Y-m-d g:ia', $form['limit_by_date'] . ' ' . $form['limit_time']);
        } else {
            $limit_date = DateTime::createFromFormat('!Y-m-d', $form['limit_by_date']);
        }

        if (empty($limit_date))
            return true;

        $limit = empty($form['limit_time']) ? $limit_date->getTimestamp() + (23 * 3600) : $limit_date->getTimestamp();
        $current_time = current_time('timestamp');
        if ($current_time > $limit) {
            return false;
        }
        return true;
    }

    // Returns true if allowed
    public function check_limit_number_of_sub($form) {
        $limit = $form['limit_by_number'];
        if (empty($limit))
            return true;
        $submission_model = erforms()->submission;
        $submissions = $submission_model->get_submissions_by_form($form['id']);
        if (count($submissions) >= $limit) {
            return false;
        }
        return true;
    }

    public function check_submission_limits($errors, $form) {
        if (empty($form['enable_limit']))
            return $errors;

        if ($form['limit_type'] == 'date') {
            if ($this->check_limit_by_date($form)) {
                return $errors;
            }
        } else if ($form['limit_type'] == 'number') {
            if ($this->check_limit_number_of_sub($form)) {
                return $errors;
            }
        } else if ($form['limit_type'] == 'both') {
            if ($this->check_limit_by_date($form) && $this->check_limit_number_of_sub($form)) {
                return $errors;
            }
        }
        $errors[] = array('limit_error', $form['limit_message']);
        return $errors;
    }

    public function check_form_user_restrictions($errors, $form) {
        // Check for user roles
        $allowed = $this->is_user_role_allowed($form);
        if (empty($allowed)) {
            $errors[] = array('role_not_allowed', $form['access_denied_msg']);
        }


        return $errors;
    }

    /**
     * Delete Form (Along with all related submissions).
     *
     * @since 1.0.0
     *
     * @param array $ids
     *
     * @return boolean
     */
    public function delete($ids = array()) {
        if (!is_array($ids)) {
            $ids = array($ids);
        }

        $ids = array_map('absint', $ids);
        $submission_model = erforms()->submission;
        foreach ($ids as $id) {
            //Get all the submissions
            $submissions = $submission_model->get_submissions_by_form($id);
            foreach ($submissions as $submission) {
                wp_delete_post($submission['id'], true);
            }
            wp_delete_post($id, true);
        }

        return true;
    }

    /**
     * Duplicate Form.
     *
     * @since 1.0.0
     *
     * @param array $ids
     *
     * @return boolean
     */
    public function duplicate($ids = array()) {
        if (!is_array($ids)) {
            $ids = array($ids);
        }

        $ids = array_map('absint', $ids);
        foreach ($ids as $id) {
            $post = get_post($id);
            if (!empty($post)) {
                erforms_duplicate_post($post);
            }
        }
        return true;
    }

    public function get_fields_dropdown($form_id, $exclude = array('submit', 'password')) {
        $form = $this->get_form($form_id);
        $dropdown = array();

        foreach ($form['fields'] as $field) {
            if (isset($field['type']) && in_array($field['type'], $exclude)) {
                continue;
            }
            if (isset($field['name'])) {
                $dropdown[$field['name']] = $field['label'];
            }
        }

        return $dropdown;
    }

    /*
     * Return content with replaced data values after form submission
     */

    public function parse_fields_in_content($content, $submission_id) {
        if (empty($submission_id))
            return $content;
        $submission = erforms()->submission->get_submission($submission_id);
        if (empty($submission))
            return $content;
        $form = erforms()->form->get_form($submission['form_id']);
        $short_tags = erforms_short_tags($form,$submission);
        $content = str_ireplace(array_keys($short_tags), array_values($short_tags), $content);
        return $content;
    }

    public function get_form_ajax() {
        $form_id = absint($_POST['form_id']);
        if (empty($form_id))
            die(__('No such Form exists', 'erforms'));
        $form = $this->get_form($form_id);
        if (empty($form))
            die(__('No such Form exists', 'erforms'));

        $form = $this->filter_form_info($form);
        $response = array('form' => $form);
        wp_send_json_success($response);
        wp_die();
    }

    public function dynamic_rules($form) {
        if (!isset($form['id'])) { // Load form object if form id passed
            $form = $this->form_model->get_form($form);
        }
        $global_types = array('country'=>'address', 'user_email'=>'user');
        $form['dynamic_rules'] = array('change' => array(), 'load' => array());

        foreach ($form['fields'] as $field) {
            if (!isset($field['name']))
                continue;
            
            if($field['type']=='country'){
                $has_action = has_action('erforms_address_' . $field['type'] . '_load');
                if ($has_action) {
                    array_push($form['dynamic_rules']['load'], array('action' => 'erforms_address_' . $field['type'] . '_load', 'field_name' => $field['name']));
                }
                $has_action = has_action('erforms_address_' . $field['type'] . '_change');
                if ($has_action) {
                    array_push($form['dynamic_rules']['change'], array('action' => 'erforms_address_' . $field['type'] . '_change', 'field_name' => $field['name']));
                }
            }
            if($field['type']=='username' || $field['type']=='user_email'){
                $has_action = has_action('erforms_user_' . $field['type'] . '_load');
                if ($has_action) {
                    array_push($form['dynamic_rules']['load'], array('action' => 'erforms_user_' . $field['type'] . '_load', 'field_name' => $field['name']));
                }
                $has_action = has_action('erforms_user_' . $field['type'] . '_change');
                if ($has_action) {
                    array_push($form['dynamic_rules']['change'], array('action' => 'erforms_user_' . $field['type'] . '_change', 'field_name' => $field['name']));
                }
            }
            /*if (in_array($field['type'], array_keys($global_types))) {
                $has_action = has_action('erforms_' .$global_types[$field['type']] . '_' . $field['type'] . '_load');
                if ($has_action) {
                    array_push($form['dynamic_rules']['load'], array('action' => 'erforms_' . $global_types[$field['type']] . '_' . $field['type'] . '_load', 'field_name' => $field['name']));
                }

                $has_action = has_action('erforms_' . $global_types[$field['type']] . '_' . $field['type'] . '_change');
                if ($has_action) {
                    array_push($form['dynamic_rules']['change'], array('action' => 'erforms_' . $global_types[$field['type']] . '_' . $field['type'] . '_change', 'field_name' => $field['name']));
                }
            }*/
            
            // Check for any hooks
            $has_filter = has_action('erforms_' . $form['id'] . '_' . $field['name'] . '_change');
            if ($has_filter) {
                array_push($form['dynamic_rules']['change'], array('action' => 'erforms_' . $form['id'] . '_' . $field['name'] . '_change', 'field_name' => $field['name']));
            }

            $has_filter = has_action('erforms_' . $form['id'] . '_' . $field['name'] . '_load');
            if ($has_filter) {
                array_push($form['dynamic_rules']['load'], array('action' => 'erforms_' . $form['id'] . '_' . $field['name'] . '_load', 'field_name' => $field['name']));
            }
        }
        //erforms_debug($form['dynamic_rules']); die;
        return $form['dynamic_rules'];
    }

    private function initialize_form_field_hooks() {
        add_action('wp_ajax_erforms_field_change_command', array($this, 'field_change_command_ajax'));
        add_action('wp_ajax_nopriv_erforms_field_change_command', array($this, 'field_change_command_ajax'));
        add_filter('erforms_address_country_load', 'erforms_address_country_load', 10, 2);
        add_filter('erforms_address_country_change', 'erforms_address_country_change', 10, 2);
    }

    public function get_form_meta($form, $meta) {
        $form_meta = array();
        $form = $this->filter_form_info($form);
        $response = array();
        if (method_exists($this, $meta)) {
            $form_meta[$meta] = $this->$meta($form);
        } else {
            if (isset($form[$meta])) {
                $form_meta[$meta] = $form[$meta];
            }
        }
        if (!empty($form_meta)) {
            $form_meta['tel_config'] = erforms_tell_config($form);
            $form_meta['pass_config'] = erforms_pass_config($form);
        }
        return $form_meta;
    }

    private function filter_form_info($form, $exclude = array('pwd_res_answer')) {
        foreach ($exclude as $info) {
            if (isset($form[$info])) {
                unset($form[$info]);
            }
        }
        return $form;
    }

    /*
     *   Returns load commands which must be executed after form load.
     *  `action_on` : Represents names of all the fields where values have to be filled
     *  `data : Data to be filled with in the HTML element
     *  `default_value`: Element's default value 
     *  `options`: Represents dropdown options existence in data
     *   `callback`: Callback function called after command execution. 
     */

    public function field_load_commands($form) {
        $commands = array();
        $dynamic_rules = $this->dynamic_rules($form);
        foreach ($dynamic_rules['load'] as $rule) {
            $command = array('data' => '', 'default_value' => '', 'options' => false, 'on' => array(), 'callback' => '');
            $command = apply_filters($rule['action'], $command, $form);
            array_push($commands, $command);
        }
        return $commands;
    }

    /*
     *   Returns change commands which are triggered during field value change.
     *  `on` : Represents names of all the fields where values have to be filled
     *  `default_value`: Element's default value 
     *  `options`: Represents dropdown options existence in data
     *   `callback`: Callback function called after command execution.
     */

    public function field_change_command_ajax() {
        $form_id = absint($_POST['form_id']);
        if (empty($form_id)) {
            wp_send_json_error();
        }

        $form = $this->get_form($form_id);
        if (empty($form)) {
            wp_send_json_error();
        }

        $action = sanitize_text_field($_POST['change_action']);
        if (empty($action)) {
            wp_send_json_error();
        }

        /*
         * Single command example
         */
        $commands = apply_filters($action, array(), $form);
        wp_send_json_success(array('commands' => $commands));
    }

    public function get_forms() {
        $posts = $this->get();
        if (empty($posts))
            return array();
        $forms = array();
        foreach ($posts as $post) {
            $form = $this->get_form($post);
            array_push($forms, $form);
        }
        return $forms;
    }

    public function get_form_fields($form) {
        $fields = array();
        foreach ($form['fields'] as $field) {
            if (isset($field['name'])) {
                $fields[$field['name']] = $field;
            }
        }
        return $fields;
    }

    /*
     * Returns Form data to be used in frontend global script
     */
    public function frontend_localize_data($form) {
        $data = array();
        // Sending only required data
        $form_attr_data = array('conditions' => array(), 'fields' => $form['fields'], 'id' => $form['id'], 'en_edit_sub' => $form['en_edit_sub'], 'edit_fields' => $form['edit_fields']);
        $data['form'] = apply_filters('erf_localize_form_data',$form_attr_data,$form);
        $data['form_meta'] = $this->get_form_meta($form, 'dynamic_rules');
        $data['load_commands'] = $this->field_load_commands($form);
        return $data;
    }
    
    public function before_form_msg($msg,$form){
        if ( false === strpos( $msg, '{{' ) ) {
            return do_shortcode($msg);
        }
        $tags= array('submission_counter');
        foreach($tags as $tag){
           $msg = str_replace('{{'.$tag,'{{erforms_'.$tag.' form="'.$form['id'].'" ',$msg); 
        }
        
        $msg= str_replace(array('{{','}}'),array('[',']'),$msg);
        echo do_shortcode($msg);
    }

}