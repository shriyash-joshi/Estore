<?php

/**
 * Form front-end rendering.
 *
 * @package    ERForms
 * @author     ERForms
 * @since      1.0.0
 */
class ERForms_Frontend {

    /**
     *
     * @var array
     */
    public $validator;
    public $errors = array();
    public $options = array(); // Global options
    public $submission_id = 0;
    public $edit_sub_status = false;
    public $rendered_forms = array();
    private static $instance = null;

    /**
     * Primary class constructor.
     *
     * @since 1.0.0
     */
    private function __construct() {
        // Register shortcode.
        add_shortcode('erforms', array($this, 'form_shortcode'));
        add_shortcode('erforms_login', array($this, 'login_shortcode'));
        add_shortcode('erforms_preview', array($this, 'preview'));
        add_shortcode('erforms_my_account', array($this, 'my_account'));
        add_shortcode('erforms_my_submissions', array($this, 'my_submissions'));

        add_filter('erf_form_validated', array($this, 'form_validated'), 10, 3);
        add_filter('erf_after_submission_insertion', array($this, 'after_submission_insertion'), 10, 3);
        add_filter('register_url', array($this, 'register_url'));
        add_filter('erf_form_render_allowed', array($this, 'form_render_allowed'), 10, 2);
        // Ajax actions
        add_action('wp_ajax_erf_change_form_layout', array($this, 'change_form_layout'));

        add_action('wp_ajax_erf_submit_form', array($this, 'ajax_submit_form'));
        add_action('wp_ajax_nopriv_erf_submit_form', array($this, 'ajax_submit_form'));
        add_action('wp_enqueue_scripts', array($this, 'load_scripts'));

        $this->validator = new ERForms_Validator;
        $this->options = erforms()->options->get_options();
    }

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function register_scripts(){
        wp_register_script('formula-engine',ERFORMS_PLUGIN_URL.'assets/js/formula-engine.js',array(),ERFORMS_VERSION);
        wp_register_script('erf-global',ERFORMS_PLUGIN_URL.'assets/js/erforms-global.js',array('jquery','moment','formula-engine'),ERFORMS_VERSION);
        $global_localize=erforms_global_localize();
        wp_localize_script('erf-global','erf_global',$global_localize);
        wp_register_script('erf-util-functions', ERFORMS_PLUGIN_URL . 'assets/js/utility-functions.js',array(),ERFORMS_VERSION);
        wp_register_script('erf-editable-dd', ERFORMS_PLUGIN_URL . 'assets/js/jquery-editable-select.min.js');
        wp_register_script('pwd-meter', ERFORMS_PLUGIN_URL . 'assets/js/password.min.js', array('jquery'));
        wp_register_script('erf-print-submission', ERFORMS_PLUGIN_URL . 'assets/js/printThis.js');
        wp_register_script('intl-tel-input',ERFORMS_PLUGIN_URL.'assets/js/intlTelInput.min.js');
    }

    public function register_styles() {
        wp_register_style('erf-front-style', ERFORMS_PLUGIN_URL . 'assets/css/style.css','',ERFORMS_VERSION);
        wp_register_style('erf-front-style-responsive', ERFORMS_PLUGIN_URL . 'assets/css/responsive.css','',ERFORMS_VERSION);
        wp_register_style('erf-jquery-datepicker-css', ERFORMS_PLUGIN_URL . 'assets/css/jquery-datepicker.css');
        wp_register_style('erf-editable-dd-css', ERFORMS_PLUGIN_URL . 'assets/css/jquery-editable-select.min.css');
        wp_register_style('pwd-meter', ERFORMS_PLUGIN_URL . 'assets/css/password.min.css');
        wp_register_style('intl-tel-input',ERFORMS_PLUGIN_URL.'assets/css/intlTelInput.min.css');
        wp_register_style('erf-font-awesome-css','https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
    }

    public function load_scripts() {
        $this->register_scripts();
        $this->register_styles();
        do_action('erf_register_front_scripts');
    }

    /**
     * Shortcode wrapper for the outputting a form.
     *
     * @since 1.0.0
     *
     * @param array $atts
     *
     * @return string
     */
    public function form_shortcode($atts) {

        $atts = shortcode_atts(array(
            'id' => false,
            'title' => false,
            'layout_options' => 1,
            'description' => false,
                ), $atts, 'output');
        ob_start();

        $sub_id = isset($_POST['submission_id']) ? absint($_POST['submission_id']) : 0;
        if (!empty($sub_id)) { // Edit submission allowed for only admin (Exception: My Account page allows to edit submission from user)
            if(erforms_edit_permission($atts['id'], $sub_id)){
                $this->edit_submission($sub_id, $atts);
            }
            else{
                _e('Preview is available only form admin users.','erforms');
                return;
            }
        }

        $this->render_form($atts);
        return ob_get_clean();
    }

    /**
     * Shortcode wrapper for the outputting a login form.
     *
     * @since 1.0.0
     *
     * @param array $atts
     *
     * @return string
     */
    public function login_shortcode($attr) {
        ob_start();
        wp_enqueue_script('erf-global');
        wp_enqueue_script('erf-util-functions');
        wp_enqueue_style('erf-front-style', ERFORMS_PLUGIN_URL . 'assets/css/style.css');
        wp_enqueue_style('erf-front-responsive-style', ERFORMS_PLUGIN_URL . 'assets/css/responsive.css');
        if(erforms_login_captcha_enabled()){
            $this->options['recaptcha_version']==2 ? wp_enqueue_script('google-recaptcha', 'https://www.google.com/recaptcha/api.js') : wp_enqueue_script('google-recaptcha', 'https://www.google.com/recaptcha/api.js?render='.$this->options['rc_site_key']);
        }
        $this->render_login_form($attr);

        return ob_get_clean();
    }

    /**
     * Primary function to render a login form on the frontend.
     *
     * @since 1.0.0
     *
     */
    public function render_login_form($attr) {
        $template = apply_filters('erf_login_form_template', __DIR__ . 'html/login-form.php');
        $template = file_exists($template) ? $template : 'html/login-form.php';
        if(empty($attr))
            $attr= array();
        
        $options= erforms()->options->get_options();
        // Do not overwrite login form display settings if given within the shortcode
        if(!isset($attr['layout'])){
            $attr['layout']= $options['login_layout'];
        }
        if(!isset($attr['field_style'])){
            $attr['field_style']= $options['login_field_style'];
        }
        if(!isset($attr['label_position'])){
            $attr['label_position']= $options['login_label_position'];
        }
        include $template;
        
    }

    /**
     * Primary function to render a form on the frontend.
     *
     * @since 1.0.0
     *
     * @param int $id
     * @param boolean $title
     * @param boolean $description
     */
    public function render_form($atts) {
        $id = $atts['id'];
        $title = $atts['title'];
        $description = $atts['description'];

        $id = absint($id);
        if (empty($id)) {
            return;
        }

        $form_model = erforms()->form;
        $form = $form_model->get_form($id);
        if (empty($form)) {
            _e('No such form exists in Database.', 'erforms');
            return;
        }


        // Basic information.
        $success = false;
        $title = filter_var($title, FILTER_VALIDATE_BOOLEAN);
        $description = filter_var($description, FILTER_VALIDATE_BOOLEAN);

        // If the form does not contain any fields do not proceed.
        if (empty($form['fields'])) {
            _e('No form fields.', 'erforms');
            return;
        }

        $action = isset($_POST['action']) ? sanitize_text_field($_POST['action']) : '';
        $posted_form_id = isset($_POST['erform_id']) ? absint($_POST['erform_id']) : 0;
        if ($action == 'erf_submit_form' && !empty($posted_form_id) && $posted_form_id == $id) {
            $response = $this->submit_form($form);
            if (!empty($response)) {
                $success = true;
            }
        }
        
        if (!empty($success)) {
            $success = $this->show_success_message($form);
        }
        $this->enqueues($form);
        $html_generator = ERForms_Form_Render::get_instance();
        $form_html = apply_filters('erf_process_form_html',$html_generator->generate_html_from_json($form),$form);
        $show_form = apply_filters('erf_form_render_allowed', true, $form);
        if ($show_form) {
            $form_template = $form['type'] == 'reg' ? 'register.php' : 'contact.php';
            include 'html/' . $form_template;
        }
        array_push($this->rendered_forms,$form['id']);
    }

    /*
     * Handles form validation & submission
     */

    private function submit_form($form) {
        if (empty($form)) {
            $this->errors = array('erf_form_error', __('No such Form exists', 'erforms'));
            return false;
        }
        $request_data = erforms_sanitize_request_data($form['fields']);
        $submission_id = isset($request_data['submission_id']) ? absint($request_data['submission_id']) : 0;
        if (!empty($submission_id)) { // Set edit submission status
            $this->edit_sub_status = true;
        }
        $this->errors = $this->validate($request_data, $form);
        if (!empty($this->errors)) {
            return false;
        }
        
        $user = wp_get_current_user();
        $auto_login= false;
        if (empty($user->ID)) {
            $auto_login = !empty($form['auto_user_activation']) && !empty($form['auto_login']) ? true : false;
        }
        $errors = apply_filters('erf_form_validated', array(), $form['id'], $request_data);
        if (!empty($errors)) {
            $this->errors = $errors;
            return false;
        }
        
        $form['success_msg']= str_replace('[erforms_resend_verification_link','[erforms_resend_verification_link sub_id="'.$this->submission_id.'" ', $form['success_msg']);
        $success_message = do_shortcode(wpautop(apply_filters('erforms_parse_success_message', $form['success_msg'], $this->submission_id)));
        $response = array(
            'success' => true,
            'msg' => $success_message,
            'form_id' => $form['id'],
            'submission_id' => $this->submission_id
        );
        $redirect_to = $form['redirect_to'];
        if (!empty($redirect_to)) {
            $response['redirect_to'] = $redirect_to;
        } else { 
            // After login URL
            if ($auto_login) { 
                $submission = erforms()->submission->get_submission($this->submission_id);
                if (!empty($submission['user'])) {
                    $user = get_user_by('ID', $submission['user']['ID']);
                    $redirect_to = apply_filters('erf_login_redirect', '','', $user);
                    if (!empty($redirect_to)) {
                        $response['redirect_to'] = $redirect_to;
                    }
                    else{
                        $response['reload']=true;
                    }
                }
            }
        }

        $response = apply_filters('erf_ajax_before_sub_response', $response);
        return $response;
    }

    /*
     * Validates form while submission
     * Make sure to pass only sanitized data
     */

    public function validate($data, $form) {
        if (!is_user_logged_in()) { // Check captcha only for guest users
            $g_r_captcha = isset($_POST['g-recaptcha-response']) ? sanitize_text_field($_POST['g-recaptcha-response']) : 'wrong captcha';
            if ($form['recaptcha_enabled']) {
                $valid = erforms_validate_captcha($g_r_captcha);
                if (!$valid) {
                    return array(array('invalid_recaptcha', __('Invalid/Expired Recapctha.', 'erforms')));
                }
            }
        }

        $errors = apply_filters('erforms_before_form_processing', array(), $form);
        if (!empty($errors) && is_array($errors)) {
            return $errors;
        }

        $errors = $this->validator->validate($form, $data);

        return $errors;
    }

    /*
     * Check if success message has to be shown (Only for non ajax submissions)
     */

    public function show_success_message($form) {
        $form_id = isset($_GET['erf_form']) ? absint($_GET['erf_form']) : 0;
        $auto_login = isset($_GET['erf_auto_login']) ? absint($_GET['erf_auto_login']) : 0; 
        
        if (empty($form_id) || empty($auto_login))
            return false;

       
        if ($form['id'] != $form_id)
            return false;
        $auto_login = $form['auto_login'];
        if (!empty($auto_login)) {
            return true;
        }
    }

    /*
     * Called after form validation.
     * Saves submission data
     */

    public function form_validated($errors, $form_id, $data) {
        $submission_id = isset($data['submission_id']) ? absint($data['submission_id']) : 0;
        $data = apply_filters('erf_before_submission_save', $data, $form_id);
        if (!empty($submission_id)) { // Edit submission
            $errors = erforms()->submission->save($form_id, $data, true);
        } else {  // Inserts new submission
            $save_sub = apply_filters('erf_submission_save_allowed', true, $form_id, $data);
            if (!empty($save_sub)) {
                $errors = erforms()->submission->save($form_id, $data);
            } else {
                do_action('erf_submission_not_saved', $form_id, $data);
            }
        }

        return $errors;
    }

    /*
     * Called after submission save
     * Registers new user into WordPress.
     * Also map field values to user meta (If configured)
     */

    public function after_submission_insertion($errors, $submission, $data) {
        $sub_model = erforms()->submission;
        $form_model = erforms()->form;
        $form = $form_model->get_form($submission['form_id']);
        
        // Copy attachment values in data from submission (as $data does not have any uploaded file values)
        if(!empty($submission['attachments'])){
            foreach($submission['attachments'] as $attachment){
                if(!isset($data[$attachment['f_name']])){
                    $data[$attachment['f_name']]= $attachment['f_val'];
                }
            }
        }
        
        if ($form['type'] == "reg") { // Handling of registration forms
            $user = 0;
            $id = 0;
            // Get mapping for user meta fields if any
            $user_field_map = erforms_filter_user_fields($form['id'], $submission['fields_data']);
            // Avoid user registration process if user already logged in
            if (!is_user_logged_in()) {
                $email_or_username = $user_field_map['user_email'];

                if (isset($user_field_map['password'])) {
                    // Silently creates user  
                    $username = isset($user_field_map['username']) ? $data[$user_field_map['username']] : $data[$email_or_username];
                    do_action('erf_before_user_creation',$submission);
                    $id = wp_create_user($username, $data[$user_field_map['password']], $data[$email_or_username]);
                } else {
                    // Register user and sends random password via email notification
                    do_action('erf_before_user_creation',$submission);
                    $id = register_new_user($data[$email_or_username], $data[$email_or_username]);
                }

                if (is_wp_error($id)) {
                    // In case something goes wrong delete the submission
                    wp_delete_post($submission['id'], true);
                    $error_code = $id->get_error_code();
                    if ($error_code == 'existing_user_login') {
                        $email_or_username = 'username_error';
                    }

                    $errors[] = array($email_or_username, $id->get_error_message($id->get_error_code()));
                    return $errors;
                } else {
                    $selected_role = erforms_get_selected_role($submission['form_id'], $data);
                    if (!empty($selected_role)) { // Means user has selected any role
                        $user_model = erforms()->user;
                        $selected_role= apply_filters('erf_before_setting_user_role',$selected_role,$id,$form, $submission);
                        $user_model->set_user_role($id, $selected_role);
                    }
                    foreach ($user_field_map as $req_key => $meta_key) {
                        $is_primary_key = in_array($meta_key, erforms_primary_field_types());
                        if (isset($data[$req_key]) && !$is_primary_key) {
                            $m_keys= explode(',',$meta_key);
                            foreach($m_keys as $m_key){
                                if(!empty($m_key)){
                                    $status = erforms_update_user_meta($id, $m_key, $data[$req_key]);
                                    do_action('erf_user_meta_updated',$m_key,$id,$data[$req_key],$status);
                                }
                            }
                        }
                    }
                    do_action('erf_user_created', $id, $form['id'], $submission['id']);
                }
            } else {
                // Get user details
                $user = wp_get_current_user();
                $id = $user->ID;
                
                foreach ($user_field_map as $req_key => $meta_key) {
                    $is_primary_key = in_array($meta_key, erforms_primary_field_types());
                    if (isset($data[$req_key]) && !$is_primary_key) {
                        $m_keys= explode(',',$meta_key);
                        foreach($m_keys as $m_key){
                            if(!empty($m_key)){
                                $status= erforms_update_user_meta($id,$m_key,$data[$req_key]);
                                do_action('erf_user_meta_updated',$m_key,$id,$data[$req_key],$status);
                            }
                        }
                    }
                }
            }
            if (!$this->edit_sub_status) {
                $sub_model->update_meta($submission['id'], 'user', $id);
            }
        } 
        else {
            $user = wp_get_current_user();
            if (!$this->edit_sub_status) {
                if (!empty($user->ID)) {
                    $user = wp_get_current_user();
                    $sub_model->update_meta($submission['id'], 'user', $user->ID);
                }
            }
            // Get mapping for user meta fields if any
            if (!empty($user->ID)) {
                $user_field_map = erforms_filter_user_fields($form['id'], $submission['fields_data']);
                foreach ($user_field_map as $req_key => $meta_key) {
                    if (isset($data[$req_key])) {
                        $m_keys= explode(',',$meta_key);
                        foreach($m_keys as $m_key){
                            if(!empty($m_key)){
                                $status= erforms_update_user_meta($user->ID, $m_key, $data[$req_key]);
                                do_action('erf_user_meta_updated',$m_key,$user->ID,$data[$req_key],$status);
                            }
                        }
                    }
                }
            }
        }
        $this->submission_id = $submission['id'];
        return $errors;
    }

    /*
     * Handles ajax form submission
     */

    public function ajax_submit_form() {
        $form_id = absint($_POST['erform_id']);
        $form = erforms()->form->get_form($form_id);
        $response = $this->submit_form($form);
        if (empty($response)) { // If empty then show errors
            wp_send_json_error($this->errors);
        }
        wp_send_json($response);
    }

    /*
     * Filter to return default registration URL for WordPress
     */

    public function register_url($url) {
        $post_id = $this->options['default_register_url'];
        if (empty($post_id))
            return $url;
        $post = get_post($post_id);
        if (empty($post))
            return $url;

        $url = home_url("?p=" . $post_id);
        return $url;
    }

    /*
     * For admin only (Allows form layout settings change from front end)
     */

    public function change_form_layout() {
        if (!current_user_can('administrator') || empty($_POST['change_form_layout_nonce']))
            return;

        $change_form_layout_nonce = sanitize_text_field($_POST['change_form_layout_nonce']);
        if (!wp_verify_nonce($change_form_layout_nonce, 'change_form_layout_nonce'))
            return;

        $form_id = absint($_POST['erform_id']);
        if (empty($form_id))
            return;

        $form_model = erforms()->form;
        $form = $form_model->get_form($form_id);
        if (empty($form))
            return;

        $layout = sanitize_text_field($_POST['layout']);
        $label_position = sanitize_text_field($_POST['label_position']);


        $form['layout'] = $layout;
        $form['label_position'] = $label_position;
        $form_model->update_form($form);
        $response = array(
            'success' => true,
        );

        wp_send_json($response);
        die;
    }

    /*
     * Shows Form Preview
     */

    public function preview() {
        if (empty($_GET['erform_id']))
            return;
        $form_id = absint($_GET['erform_id']);
        if (empty($form_id))
            return;

        ob_start();

        echo do_shortcode('[erforms id="' . $form_id . '"]');

        return ob_get_clean();
    }

    /*
     * Edit submission
     */

    private function edit_submission($sub_id, $form_atts) {
        $submission = erforms()->submission->get_submission($sub_id);
        if (empty($submission) || empty($form_atts['id']))
            return;

        // Make sure Form exists and Form ID matches with submission ID
        $form = erforms()->form->get_form($form_atts['id']);
        if (empty($form))
            return;
        $this->edit_sub_status = true;
    }

    /*
     * Called just before rendering the form
     */

    public function form_render_allowed($show_form, $form) {
        // Check if form is password protected
        if (!empty($form['en_pwd_restriction'])) {
            if (is_user_logged_in() && empty($form['pwd_res_en_logged_in'])) { // Password protection disabled for logged in users 
                return $show_form;
            }

            $password_error = false;
            if (isset($_POST['erform_id']) && absint($_POST['erform_id']) == $form['id']) {
                if (isset($_POST['erf_answer'])) {
                    $answer = strtolower(sanitize_text_field(wp_unslash($_POST['erf_answer'])));
                    if ($answer == strtolower(trim($form['pwd_res_answer']))) {
                        return $show_form;
                    } else {
                        $password_error = true;
                    }
                }
            }

            include('html/password_protection.php');
            $show_form= false;
        }
        
        // Check if multiple submissions are not allowed from same user
        if(!empty($show_form) && !empty($form['dis_mul_sub'])){
            $current_user = wp_get_current_user();
            $submissions = erforms()->submission->get_submissions_from_user($current_user->ID);
            foreach($submissions as $sub){
                if($sub['form_id']==$form['id']){
                    echo do_shortcode($form['mul_sub_denial_msg']);
                    $show_form= false;
                    break;
                }
            }
        }
        
        return $show_form;
    }

    /*
     * Renders frontend my account.
     */

    public function my_account($atts) {
        global $wp;
        ob_start();

        if (!is_user_logged_in()) { // Show Login if user is not already logged in 
            return do_shortcode('[erforms_login]');
        }
        
        $this->enqueues();
        wp_enqueue_script('erf-print-submission');

        $current_user = wp_get_current_user();

        /* Pagination related */
        $per_page = 10;
        $paged = isset($_GET['erf_paged']) ? absint($_GET['erf_paged']) : 0;
        $submissions = erforms()->submission->get_submissions_from_user($current_user->ID);
        $total_submissions = count($submissions);
        $offset = $paged * $per_page;
        $show_next = ($offset + $per_page) < $total_submissions ? true : false;
        $show_prev = $offset > 0 ? true : false;

        $submissions = array_slice($submissions, $offset, $per_page, true);
        if (!empty($atts['wc'])) {
            $template = apply_filters('erf_wc_my_account_template', __DIR__ . '/html/wc_my_account.php');
            $template = file_exists($template) ? $template : 'html/wc_my_account.php';
            include($template);
        } else {
            $template = apply_filters('erf_my_account_template', __DIR__ . '/html/my_account.php');
            $template = file_exists($template) ? $template : 'html/my_account.php';
            include($template);
        }
        return ob_get_clean();
    }

    public function enqueues($form = null) {
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('erf-parsley', ERFORMS_PLUGIN_URL . 'assets/js/parsley.min.js');
        wp_enqueue_script("erf-masked-input", ERFORMS_PLUGIN_URL . 'assets/js/jquery.masked.input.js');
        if (!empty($this->options['recaptcha_configured'])) {
            $this->options['recaptcha_version']==2 ? wp_enqueue_script('google-recaptcha', 'https://www.google.com/recaptcha/api.js') : wp_enqueue_script('google-recaptcha', 'https://www.google.com/recaptcha/api.js?render='.$this->options['rc_site_key']); 
        }
        wp_enqueue_script('erf-global');
        wp_enqueue_script('erf-util-functions');
        wp_enqueue_script('erf-editable-dd');
        wp_enqueue_script('jquery-ui-datepicker', '', array('jquery'));
        wp_enqueue_script('pwd-meter');
        //wp_enqueue_script('intl-tel-data');
        //wp_enqueue_script('intl-tel-util');
        wp_enqueue_script('intl-tel-input');
        
        wp_enqueue_style('erf-front-style');
        wp_enqueue_style('erf-front-style-responsive');
        wp_enqueue_style('erf-jquery-datepicker-css');
        wp_enqueue_style('erf-editable-dd-css');
        wp_enqueue_style('pwd-meter');
        wp_enqueue_style('intl-tel-input');
        wp_enqueue_style('erf-font-awesome-css');
        if(!empty($form)){
            $data= $this->localize_data($form);
            if(!empty($data)){
                wp_localize_script('erf-global','form_'.$form['id'],$data);
            }
        }
        do_action('erforms_frontend_enqueues', $form);
    }
    
    /*
     * Returns data to be used as JS script data. 
     * 1. Form  attributes.
     * 2. Submission data (if submission ID passed)
     * 3. User meta as per the binding of Form fields.
     * 4. URL params data
     */
    public function localize_data($form){
        if(in_array($form['id'],$this->rendered_forms)){ // Localize data is already enqueued
            return array();
        }
        
        $data = erforms()->form->frontend_localize_data($form);
        
        // Check if Submission data has to be added
        $submission_id = isset($_POST['submission_id']) ? absint($_POST['submission_id']) : 0;
        $submission = erforms()->submission->get_submission($submission_id);
        if(!empty($submission)){
            // Check if submission belong to the Form
            if($submission['form_id']==$form['id']){
                $sub_data= erforms()->submission->frontend_localize_data($form,$submission);
                if(!empty($sub_data)){
                    $data['submission']= $sub_data;
                }
            }
            else{
                $submission = array();
            }
        }
        
        // User meta,URL params or default values should be prefilled only when we are not loading submission data
        if(empty($submission)){
            $user_meta = erforms()->user->frontend_localize_user_meta($form);
            $filtered_url_params = array();
            foreach ($_GET as $key => $val) {
                $filtered_url_params[urldecode(strtolower(wp_unslash($key)))] = sanitize_text_field(wp_unslash($val));
            }

            $url_keys = array_keys($filtered_url_params);
            foreach ($form['fields'] as $field) {
                $label = !empty($field['label']) ? strtolower(str_replace(' ', '_', $field['label'])) : '';
                $label = str_replace('&', 'and', $label); // Cause URL params do not allow & 
                if (!empty($field['name']) && !empty($label) && in_array($label, $url_keys)) {
                    if (!isset($user_meta[$field['name']]) && !empty($filtered_url_params[$label])) {
                        $user_meta[$field['name']] = stristr($filtered_url_params[$label], '|') ? explode('|', $filtered_url_params[$label]) : $filtered_url_params[$label];
                    }
                }
                if(!empty($field['name']) && empty($user_meta[$field['name']]) && !empty($field['value'])){
                    $user_meta[$field['name']] = $field['value'];
                }
                
            }

            if (!empty($user_meta)) {
                $data['user_meta'] = $user_meta;
            }
        }
        $data= apply_filters('erf_form_localize_data',$data,$form);
        return $data;
    }
    
    public function my_submissions($atts) {
        global $wp;
        ob_start();
        
        
        if (!is_user_logged_in()) { // Show Login if user is not already logged in 
            return do_shortcode('[erforms_login]');
        }
        
        $this->enqueues();
        wp_enqueue_script('erf-print-submission');

        $current_user = wp_get_current_user();

        /* Pagination related */
        $per_page = 10;
        $paged = isset($_GET['erf_paged']) ? absint($_GET['erf_paged']) : 0;
        $submissions = erforms()->submission->get_submissions_from_user($current_user->ID);
        $total_submissions = count($submissions);
        $offset = $paged * $per_page;
        $show_next = ($offset + $per_page) < $total_submissions ? true : false;
        $show_prev = $offset > 0 ? true : false;

        $submissions = array_slice($submissions, $offset, $per_page, true);
        $template = apply_filters('erf_my_submissions_template', __DIR__ . '/html/my_submissions.php');
        $template = file_exists($template) ? $template : 'html/my_submissions.php';
        include($template);
        return ob_get_clean();
    }
    
}
