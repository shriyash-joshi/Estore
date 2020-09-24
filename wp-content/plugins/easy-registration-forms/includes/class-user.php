<?php
/**
 * User Related
 *
 * @package    ERForms
 * @author     ERForms
 * @since      1.0.0
 */
class ERForms_User {

    private  $meta_prefix= 'erf_';
    private static $instance = null;
    /**
     * Primary class constructor.
     *
     * @since 1.0.0
     */
    private function __construct() {
        add_action('erf_user_created', array($this, 'post_user_creation'), 10, 3);
        add_filter('login_redirect', array($this,'login_redirect'), 10, 3 );
        add_filter('erf_login_redirect', array($this,'login_redirect'), 10, 3 );
        add_filter('erf_ajax_before_sub_response',array($this,'ajax_before_sub_response'),10,1);
        
        // WP User Related
        add_action('edit_user_profile', array($this,'show_profile_fields'));
        add_action('show_user_profile', array($this,'show_profile_fields'));
        add_action( 'personal_options_update',array($this,'update_user_status'));
        add_action( 'edit_user_profile_update',array($this,'update_user_status'));
        add_action('manage_users_columns',array($this,'add_user_column'));
        add_action('manage_users_custom_column', array($this,'fill_status_column'),10,3);
        add_filter('authenticate', array($this,'authenticate_user'), 30, 3 );
        add_action('wp_ajax_erf_reset_password', array( $this, 'reset_password'));
        add_action('wp_ajax_nopriv_erf_reset_password', array( $this, 'reset_password'));
        add_action('wp_ajax_erf_otp_check', array( $this, 'otp_check'));
        add_action('wp_ajax_nopriv_erf_otp_check', array( $this, 'otp_check'));
        add_action('wp_ajax_erf_update_password', array( $this, 'update_password'));
        add_action('wp_ajax_nopriv_erf_update_password', array( $this, 'update_password'));
        add_action('wp_ajax_erf_login_user', array( $this, 'process_login'));
        add_action('wp_ajax_nopriv_erf_login_user', array( $this, 'process_login'));
        add_action('wp_ajax_erf_change_password', array( $this, 'change_password_ajax'));
        add_action('wp_ajax_erf_account_verification',array($this,'account_verification'));
        add_action('wp_ajax_nopriv_erf_account_verification',array($this,'ajax_account_verification'));
        add_shortcode('erforms_account_verification',array($this,'account_verification'));
        add_action('wp_ajax_erf_ajax_log_in',array($this,'ajax_log_in'));
        add_action('wp_ajax_nopriv_erf_ajax_log_in',array($this,'ajax_log_in'));
        add_shortcode('erforms_resend_verification_link',array($this,'resend_verification_link'));
        add_action('wp_ajax_erf_resend_verification',array($this,'resend_verification'));
        add_action('wp_ajax_nopriv_erf_resend_verification',array($this,'resend_verification'));
        add_filter('erforms_user_username_change',array($this,'check_user_by_username'),10,2);
        add_filter('erforms_user_user_email_change',array($this,'check_user_by_email'),10,2);
        add_action('erf_user_meta_updated',array($this,'user_meta_updated'),10,4);
        add_action('erf_post_submission_completed', array($this,'post_submission_completed'),10);
        add_shortcode('erforms_user_meta',array($this,'user_meta_for_shortcode'));
    }

    public static function get_instance()
    {   
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function post_user_creation($user_id, $form_id,$sub_id) {
        $form_model = erforms()->form;
        $form= $form_model->get_form($form_id);
        $submission= erforms()->submission->get_submission($sub_id);
        // Check for user role assignment if not already logged in
        if (is_user_logged_in())
            return false;
        
        $default_role = $form['default_role'];
        $roles= erforms_wp_roles();
        if(!isset($roles[$default_role])){
            $default_role = '';
        }
        
        if(!empty($default_role)){ 
            $default_role= apply_filters('erf_before_setting_user_role',$default_role,$user_id,$form, $submission);
            $this->set_user_role($user_id, $default_role);
        }
        
        
        // Update form and submission ID in user meta for future reference 
        $this->update_meta($user_id, 'form', $form['id']);
        $this->update_meta($user_id, 'submission', $sub_id);
        
        // Auto User Activation Check
        $auto_activation = $form['auto_user_activation'];
        if(!empty($submission['payment_status']) && $submission['payment_status']==ERFORMS_COMPLETED){
            $auto_activation= true;
        }
        $user_active= false;
        if (!empty($auto_activation)) {
            $active= apply_filters('erf_before_user_activation',true,$user_id,$submission,$form);
            if($active){
                $this->update_meta($user_id, 'active', 1);
                do_action('erf_async_user_activated',$user_id);
                //wp_schedule_single_event(time() + 60, 'erf_async_user_activated',array($user_id));
                $user_active= true;
            }
            else
            {
                $this->update_meta($user_id, 'active', 0);
                do_action('erf_async_user_deactivated',$user_id);
            }
        } else {
            // Check if email verification has to be done
            if(!empty($form['en_email_verification'])){
                $hash=  wp_generate_password(12,false);
                $this->update_meta($user_id, 'verification_hash',$hash);
                do_action('erf_send_verification_link',$user_id,$hash,$form,$sub_id);
                //wp_schedule_single_event(time()+50,'erf_send_verification_link',array($user_id,$hash,$form,$sub_id));
            }
            $this->update_meta($user_id, 'active', 0);
        }
        
        
        // Auto Login check
        $auto_login = $form['auto_login'];
        if (!empty($auto_login) && !empty($user_active)) {
            $this->login_user_by_id($user_id);
        }
    }
    
    

    public function set_user_role($user_id, $role) {
        $user = get_user_by('ID', $user_id);
        if (empty($user))
            return false;

        $user->set_role($role);
    }
    
    public function get_user_roles($user_id) {
        $user = get_user_by('ID', $user_id);
        if (empty($user))
            return false;

        return $user->roles;
    }

    /*
     * Login user by ID
     */
    public function login_user_by_id($user_id) {
        if (headers_sent())
            return;
        //delete_user_meta($user_id,$this->meta_prefix.'verification_hash');
        wp_set_current_user($user_id); 
        wp_set_auth_cookie($user_id);
        do_action('erf_user_logged_in',$user_id);
    }
    
    public function login_user_by_credentials($credentials= array(),$secure_cookie=''){
        return wp_signon($credentials,$secure_cookie);
    }

    public function update_meta($user_id, $meta, $value) {
        $meta = 'erf_' . $meta;
        $status= update_user_meta($user_id, $meta, $value);
        do_action('erf_user_meta_updated',$meta,$user_id,$value,$status);
    }
    
    public function get_meta($user_id, $meta,$single= true) {
        $meta = 'erf_'.$meta;
        return get_user_meta($user_id, $meta, $single);
    }

    public function login_redirect($redirect_to,$requested_redirect_to,$user){
        if(is_wp_error($user) || empty($user))
        return $redirect_to;
    
        if(empty($user->ID))
            return $redirect_to;
        
        $redirect_to= $this->redirection_url_after_login($redirect_to,$user);
        return $redirect_to;

    }
    
    /*
     * Process login form. 
     */
    public function process_login(){
        $response= array('success'=>0,'msg'=>'');
        $options= erforms()->options->get_options();
        
        $action= isset($_POST['action']) ? sanitize_text_field(wp_unslash($_POST['action'])) : '';
        $username = isset($_POST['erf_username']) ? sanitize_text_field(wp_unslash($_POST['erf_username'])) : '';
        $password = isset($_POST['erf_password']) ? sanitize_text_field($_POST['erf_password']) : '';
        if($action=='erf_login_user' && !empty($username) && !empty($password)){
            if(erforms_login_captcha_enabled()){
                $g_r_captcha = isset($_POST['g-recaptcha-response']) ? sanitize_text_field($_POST['g-recaptcha-response']) : __('Invalid Captcha','erforms');
                $valid = empty($g_r_captcha) ? false : erforms_validate_captcha($g_r_captcha);
                if (!$valid) { // Invalid reCaptcha
                    $response['msg']= esc_html(__('Invalid/Expired Recapctha.', 'erforms'));
                    echo json_encode($response);
                    wp_die();
                }
            }
            
            if($options['allow_login_from']=='username'){
                $user= get_user_by('login',$username);
            }
            elseif($options['allow_login_from']=='email'){
                $user= get_user_by('email',$username);
            }
            else{
                $user= get_user_by('login',$username);
                if(empty($user)){
                    $user= get_user_by('email',$username);
                }
            }
            if(!empty($user)){ 
                $creds = array();
                $creds['user_login'] = $user->user_login;
                $creds['user_password'] = $password;
                $creds['remember']= isset($_POST['rememberme']) ? true : false;

                $user= $this->login_user_by_credentials($creds,is_ssl());
                if($user instanceof WP_Error){
                   $response['msg']= (isset($user->errors) && isset($user->errors['incorrect_password'])) ? sprintf(__('The password you entered for the username <strong>%s</strong> is incorrect.','erforms'),$username) : $user->get_error_message();
                }
                else{
                    $response['success']=1;
                    wp_set_current_user($user->ID);
                    $redirect_to= apply_filters('erf_login_redirect','','',$user);
                    if(!empty($redirect_to)){
                        $response['redirect']= $redirect_to;
                    }
                    else{
                       $response['reload']= true;
                    }
                } 
            }
            else
            {
                 $response['msg']= __('ERROR: No such user exists.','erforms');
            }
        }
        $response['msg'] = $response['msg'];
        $response['redirect']= !empty($response['redirect']) ? esc_url_raw($response['redirect']) : '';
        echo json_encode($response);
        wp_die();
    }
    
    public function show_profile_fields($user){ 
        erforms_show_user_fields($user);
    }
    
    public function update_user_status($user_id){
        if ( !current_user_can( 'edit_user', $user_id ) )
		return false;
        $current_user= wp_get_current_user();
   
        if($current_user->ID==$user_id)
            return;
        
        if(!isset($_POST['erf_user_status']))
            return; 
        
        $active= absint($_POST['erf_user_status']);
        $current_status= $this->get_meta( $user_id,'active');
	$this->update_meta( $user_id,'active',$active);
        
        if($current_status!=$active && $active==1)
        {   
            do_action('erf_user_activated',$user_id);
        }
        
    }
    
    public function add_user_column($column_headers) {
        $column_headers['user_status'] = __('User Status','erforms');
        return $column_headers;
    }
    
    public function fill_status_column($value, $column_name, $user_id) {
        $user = get_userdata($user_id);
        if ('user_status'==$column_name) 
        {
          $active= $this->get_meta($user->ID,'active');
          if($active==='0')
              return __('Inactive','erforms');
          else
              return __('Active','erforms');
        }
        return $value;
    }
    
    public function ajax_before_sub_response($response){
        $submission= erforms()->submission->get_submission($response['submission_id']);
        $form= erforms()->form->get_form($submission['form_id']);
        $auto_login = $form['auto_login'];
        $auto_activation = $form['auto_user_activation'];
        $redirect_to= $form['redirect_to'];
        if (!is_user_logged_in() && empty($redirect_to) && !empty($auto_login) && !empty($auto_activation)) {
            $response['msg']= __('Please wait, While we are setting up your account.','erforms');
            $response['reload']= true;
        }
        
        if(erforms()->frontend->edit_sub_status){
            if(isset($response['redirect_to'])) unset($response['redirect_to']);
            if(isset($response['reload'])) unset($response['reload']);
            $response['msg']= __('Submission edited successfully','erforms');
        }

        return $response;
    }
    
    public function get_user($user_id){
        return get_userdata($user_id);
    }
    
    public function authenticate_user($user, $username, $password){
        // username and password are correct
        if ($user instanceof WP_User) {
            $is_active = $this->get_meta($user->ID, 'active');
            if($is_active==='0')
            {
                return new WP_Error('status_deactive',__('ERROR: Your account is not active.','erforms'));
            }
        }
    
        return $user;
    }
    
    /**
    * Check OTP.
    */
    public function otp_check($user_login = '',$user_otp='') {
        $get_method = 0;
        if($user_login!='' && $user_otp!=''){
            $get_method = 1;
        }else{
            $user_login= sanitize_text_field(wp_unslash($_POST['user_login']));
            $user_otp= sanitize_text_field(wp_unslash($_POST['user_otp']));
        }           
        $response= array('success'=>0,'msg'=>__('','erforms'));
        
        if(empty($user_login)){
            $response['msg']= __('ERROR: Enter a username or email address.');
        }elseif(strpos($user_login,'@')){
            $user_data = get_user_by('email',$user_login);
            if (empty($user_data)){
                $response['msg']= __('ERROR: Something goes wrong, Please try again.');
            }
	}else {
            $user_data = get_user_by('login',$user_login);
	}
        
        if (!$user_data) {
            $response['msg']= __('ERROR: Something goes wrong, Please try again.');
	}else{
            $get_user_otp = get_user_meta($user_data->ID, 'erf_user_otp', true);
            if($user_otp==$get_user_otp){
                $response['success']=1;
                $response['msg']= $user_data->ID;
            }else{
                $response['msg']= __('ERROR: Invalid OTP, Please try again.');
            }
        }
         
        $response['msg'] = esc_html($response['msg']);
        if($get_method==0){
            echo json_encode($response);            
        }else{
            return $response;
        }
        exit;
   }
   
   /**
    * Update Password OTP.
    */
   public function update_password() {
        $user_login= sanitize_text_field(wp_unslash($_POST['user_login']));
        $user_otp= sanitize_text_field(wp_unslash($_POST['user_otp']));
        $user_password= sanitize_text_field(wp_unslash($_POST['password']));
        $user_cpassword= sanitize_text_field(wp_unslash($_POST['cpassword']));
        $response= array('success'=>0,'msg'=>__('ERROR: Something goes wrong, Please try again.','erforms'));
        
        $check_otp = $this->otp_check($user_login,$user_otp);
        if($check_otp['success']==1){
            if($user_password==$user_cpassword){
                if(strlen($user_password)>4){
                    wp_set_password($user_password,$check_otp['msg']);
                    $response['success']=1;
                    $response['msg']= 'Password Changed Successfully.';    
                }else{
                    $response['msg']= __('ERROR: Password length should be minimum 5 characters');
                }                                
            }else{
                $response['msg']= __('ERROR: Password and Confirm Password should be same');
            }
        }
        
        $response['msg'] = esc_html($response['msg']);
        echo json_encode($response);
        exit;
   }
    
    /**
    * Initiates password reset.
    */
   public function reset_password() {
        $user_login= sanitize_text_field(wp_unslash($_POST['user_login']));
        $response= array('success'=>1,'msg'=>__('Check your email for the OTP.','erforms'));
        if(empty($user_login)){
            $response['msg']= __('Please provide a valid email or username');
        }
         
        $errors = $this->retrieve_password($user_login);
        if (is_wp_error($errors)) {
            $response['msg']=  $errors->get_error_message();
            $response['success']=0;
        }
        $response['msg'] = esc_html($response['msg']);
        echo json_encode($response);
        exit;
   }
    
   
   public function retrieve_password($user_login){
	$errors = new WP_Error();
	if (empty($user_login)){
            $errors->add('empty_username', __('<strong>ERROR</strong>: Enter a username or email address.'));
	} elseif(strpos($user_login,'@')){
		$user_data = get_user_by('email',$user_login);
		if (empty($user_data))
                    $errors->add('invalid_email', __('<strong>ERROR</strong>: There is no user registered with that email address.'));
	} else {
            $user_data = get_user_by('login',$user_login);
	}
        
	do_action( 'lostpassword_post', $errors );
	if ($errors->get_error_code())
            return $errors;

	if (!$user_data) {
            $errors->add('invalidcombo', __('<strong>ERROR</strong>: Invalid username or email.','erforms'));
            return $errors;
	}
        $options= erforms()->options->get_options();
	// Redefining user_login ensures we return the right case in the email.
	$user_login = $user_data->user_login;
	$user_email = $user_data->user_email;
        $user_id = $user_data->ID;
        $random_number = wp_generate_password(12);
        update_user_meta($user_id,'erf_user_otp',$random_number);
        $message = str_replace(array('{{user_login}}','{{otp}}'), array($user_login,$random_number), $options['forgot_pass_email']);
	$title = $options['forgot_pass_email_subject'];
	$title = apply_filters( 'retrieve_password_title', $title, $user_login, $user_data );
	$message = apply_filters( 'erforms_password_message', $message, $user_login, $user_data );
        
	if ($message && !wp_mail($user_email,wp_specialchars_decode($title),$message)){
            return new WP_Error('erf_email_error',__('The email could not be sent.','erforms'));	
        }
	return true;
   }
   
   public function check_user_by_username($commands,$form){
       if(is_user_logged_in()){
           return $commands;
       }
       $username= sanitize_text_field(wp_unslash($_POST['field_value']));
       if(empty($username)){
           return $commands;
       }
       $command= erforms_default_field_command();
       if(username_exists($username)){
           $command['error']= __('Username already exists.','erforms');
       }
       array_push($commands,$command);
       return $commands;
   }
   
   public function check_user_by_email($commands,$form){
       if(is_user_logged_in()){
           return $commands;
       }
       $email= sanitize_text_field(wp_unslash($_POST['field_value']));
       if(empty($email)){
           return $commands;
       }
       $command= erforms_default_field_command();
       $exists = email_exists($email);
       if(!empty($exists)){
           $command['error']= __('Email already exists.','erforms');
       }
       array_push($commands,$command);
       return $commands;
   }
   
   /*
    * Change password for authenticated users
    */
   public function change_password_ajax(){
     // Delete report action
    if (defined('DOING_AJAX') && DOING_AJAX){
        $errors= array();
        $nonce=  isset($_POST['erform_change_pwd_nonce']) ? sanitize_text_field($_POST['erform_change_pwd_nonce']) : '';
        if (!wp_verify_nonce($nonce,'erform_change_pwd_nonce')) {
            $errors[]=__('Invalid Security Token, Please refresh the page and try again','erforms'); 
            wp_send_json_error(array('errors'=>$errors));
        }
        
        $current_password= sanitize_text_field(wp_unslash($_POST['password_current']));
        if(empty($current_password)){
            $errors[]=__('Current Password Required.','erforms');
            wp_send_json_error(array('errors'=>$errors));
        }
        
        $current_user = wp_get_current_user();
        // Check current password
        $match= wp_check_password($current_password, $current_user->user_pass, $current_user->ID);
        if(empty($match)){
            $errors[]=__('Current Password Incorrect.','erforms');
            wp_send_json_error(array('errors'=>$errors));
        }
        
        $password_1= sanitize_text_field(wp_unslash($_POST['password_1']));
        if(empty($password_1)){
            $errors[]=__('New Password Required.','erforms');
            wp_send_json_error(array('errors'=>$errors));
        }
        
        wp_set_password($password_1,$current_user->ID);
        
        // Log-in again.
        wp_set_auth_cookie($current_user->ID);
        wp_set_current_user($current_user->ID);
        do_action('wp_login', $current_user->user_login, $current_user);
        wp_send_json_success(array('msg'=>__('Password changed successfully.','erforms')));
    }   
   }
   
   // Called when Account verification page is not configured 
   public function ajax_account_verification(){
       $hash= isset($_REQUEST['erf_account_hash']) ? sanitize_text_field($_REQUEST['erf_account_hash']) : '';
       if(empty($hash)){
           _e('Missing verification hash','erforms');
           wp_die();
       }
       
       $form_id= isset($_REQUEST['erf_form']) ? absint($_REQUEST['erf_form']) : 0;
       $form= erforms()->form->get_form($form_id);
       if(empty($form)){
           _e('Form not found.','erforms');
           wp_die();
       }
       
       // Find User
       $user_meta_query= array(
                            array(
                                'key'=>$this->meta_prefix.'verification_hash',
                                'value'=>$hash,
                                'compare'=>''    
                            )
       );
       $users= get_users(array('meta_query'=>$user_meta_query));
       if(empty($users)){
          _e('No such users exists','erforms'); 
          wp_die();
       }
      
       foreach($users as $user){ // Execute block only for first user as verification hash meant to be unique
           $current_status= $this->get_meta($user->ID,'active');
           if($current_status==='' || !empty($current_status))
           {   
                _e('User account is already activated','erforms');
           }
           else
           {
               $this->update_meta($user->ID, 'active', 1);
               do_action('erf_user_activated',$user->ID);
               //delete_user_meta($user->ID,$this->meta_prefix.'verification_hash');
               $auto_login = $form['auto_login_after_ver'];
               $redirect_to= home_url();
               if (!empty($auto_login)) {
                    $this->login_user_by_id($user->ID);
                    $redirect_to= $this->redirection_url_after_login($redirect_to,$user);
               }
               do_action('erf_user_account_verified',$user);
               echo strip_shortcodes($form['user_acc_verification_msg']);
               echo '<script>document.location="'.$redirect_to.'";</script>';
           }
           break;
       }
       exit;
   }
   
   // Registered with [erforms_account_verification] shortcode
   public function account_verification(){
       ob_start();
       $hash= isset($_REQUEST['erf_account_hash']) ? sanitize_text_field($_REQUEST['erf_account_hash']) : '';
       if(empty($hash)){
           return ob_get_clean();
       }
       
       $form_id= isset($_REQUEST['erf_form']) ? absint($_REQUEST['erf_form']) : 0;
       $form= erforms()->form->get_form($form_id);
       if(empty($form) || $form['type']!='reg'){
           return ob_get_clean();
       }
       
       // Find User
       $user_meta_query= array(
                            array(
                                'key'=>$this->meta_prefix.'verification_hash',
                                'value'=>$hash,
                                'compare'=>''    
                            )
       );
       $users= get_users(array('meta_query'=>$user_meta_query));
       if(empty($users)){
           _e('Verification link does not match with any User\'s record.','erforms');
           return ob_get_clean();
       }
       if(is_user_logged_in()){
           include('html/account_verification.php');
            return ob_get_clean();
       }
       
       foreach($users as $user){ // Execute block only for first user as verification hash meant to be unique
           $current_status= $this->get_meta($user->ID,'active');
           if($current_status==='' || !empty($current_status))
           {   
               _e('User account is already activated','erforms');
           }
           else
           {
               $this->update_meta($user->ID, 'active', 1);
               do_action('erf_user_activated',$user->ID);
               //delete_user_meta($user->ID,$this->meta_prefix.'verification_hash');
               include('html/account_verification.php');
               do_action('erf_user_account_verified',$user);
           }
           break;
       }
       return ob_get_clean();
   }
   
   // Logins user from AJAX request. Example: Used in Auto Login after successfull account verification
   public function ajax_log_in(){
       $type= sanitize_text_field($_POST['type']);
       $value= sanitize_text_field($_POST['value']);
       
       if(empty($type) || empty($value)){
           wp_send_json_error();
       }
       
       if($type=='user_hash'){
           $user_meta_query= array(
                            array(
                                'key'=>$this->meta_prefix.'verification_hash',
                                'value'=>$value,
                                'compare'=>''    
                            )
            );
           $users= get_users(array('meta_query'=>$user_meta_query));
           if(empty($users)){
            wp_send_json_error();
           }
           $options= erforms()->options->get_options();
           foreach($users as $user){
                $this->login_user_by_id($user->ID);
                $redirect_to= sanitize_text_field($_POST['redirect_to']);
                $redirect_to= $this->redirection_url_after_login($redirect_to,$user);
                break;
           }
           wp_send_json_success(array('redirect_to'=>esc_url($redirect_to)));
       }
       wp_send_json_success(); 
   }
   
   // Returns redirection URL as configured in Global Settings
   public function redirection_url_after_login($default_url,$user){
        $options= erforms()->options->get_options();
        if(!empty($options['en_role_redirection'])){
            foreach($user->roles as $role){
                if(!empty($options[$role.'_login_redirection'])){
                     $default_url= $options[$role.'_login_redirection'];
                     break;
                }
            }
        }
        else if(!empty($options['after_login_redirect_url']))
        {   
            $default_url = $options['after_login_redirect_url'];
        }
        return $default_url;
   }
   
   public function resend_verification_link($attrs){
       if(empty($attrs['sub_id']))
           return '';
       $label= empty($attrs['label']) ? __('resend verification link','erforms') : $attrs['label'];
       $nonce= wp_create_nonce('erf-resend-verification');
       $html= "<a data-sub='".$attrs['sub_id']."' data-nonce='$nonce' class='erf_resend_ver_link' href='javascript:void(0)'>$label</a>";
       return $html;
   }
   
   public function resend_verification(){
        $nonce= sanitize_text_field($_POST['nonce']);
        if (!wp_verify_nonce($nonce,'erf-resend-verification')) {
              wp_send_json_error(array('msg'=>__('Security token expired.','erforms')));
        }
        $sub= sanitize_text_field($_POST['sub']);
        $submission= erforms()->submission->get_submission($sub);
        if(empty($submission)){
            wp_send_json_error(array('msg'=>__('System is unable to send verification email.','erforms')));
        }
        //Check if user exists in WordPress
        $user= get_user_by('id',$submission['user']['ID']);
        if(empty($user)){ // User is no more available in WordPress
            wp_send_json_error(array('msg'=>__('User data is unavailable.','erforms')));
        }
        $is_active= $this->get_meta($user->ID, 'active');
        if(!empty($is_active)){
            wp_send_json_error(array('msg'=>__('User is already verified.','erforms')));
        }
        $hash= $this->get_meta($user->ID,'verification_hash');
        if(empty($hash)){
            wp_send_json_error(array('msg'=>__('No verification key available.','erforms')));
        }
        $form= erforms()->form->get_form($submission['form_id']);
        do_action('erf_send_verification_link',$user->ID,$hash,$form,$submission['id']);
        wp_send_json_success(array('msg'=>__('Verification link has been sent. Please check your email.','erforms')));
   }
   
   /*
    * Returns User meta data as per the field's configuration in Form. 
    */
   public function frontend_localize_user_meta($form) {
        $user_meta = array('attachments'=>array());
        // Looping through form fields for user meta
        if (is_user_logged_in()) {
            $user = wp_get_current_user();
            if (is_array($form['fields'])) {
                foreach ($form['fields'] as $field) {
                    if (!empty($field['name']) && !empty($field['addUserField']) && !empty($field['addUserFieldMap'])) {
                        $m_keys = explode(',', $field['addUserFieldMap']);
                        $user_meta[$field['name']] = get_user_meta($user->ID, $m_keys[0], true);
                        if(!empty($field['type']) && $field['type']=='file' && !empty($user_meta[$field['name']])){
                            $attachment= array();
                            $attachment_id= $user_meta[$field['name']];
                            $attachment_submission_id = erforms_get_post_meta($attachment_id,'submission_id');
                            if(wp_attachment_is_image($attachment_id)){
                                $attachment['image_url']= erforms_get_attachment_url($attachment_id,$attachment_submission_id);
                            }
                            else{
                                $link_url = erforms_get_attachment_url($attachment_id,$attachment_submission_id);
                                if(!empty($link_url)){
                                    $attachment['link_url']= $link_url;
                                    $attachment['link_label']= ucwords(get_the_title($attachment_id));
                                }
                            }
                            if(!empty($attachment)){
                                $attachment['f_label']= $field['label'];
                                $attachment['f_name']= $field['name'];
                                $attachment['f_val']= $attachment_id;
                                array_push($user_meta['attachments'],$attachment);
                            }
                        }
                    }
                    
                    if($form['type']=='reg'){
                        // Username field
                        if($field['type']=='username'){
                            $user_meta[$field['name']] = $user->user_login;
                        }
                        else if($field['type']=='user_email'){
                            $user_meta[$field['name']] = $user->user_email;
                        }
                        else if($field['type']=='password'){
                            $user_meta[$field['name']] = 'Cheating!!!';
                        }
                        
                    }
                }
            }
        }
        return $user_meta;
   }
   
   public function user_meta_updated($key,$user_id,$value,$status){
       if($key=='user_url'){
           wp_update_user(array('ID'=>$user_id,$key=>$value));
       }
   }
   
   public function post_submission_completed($sub_id){
        $submission = erforms()->submission->get_submission($sub_id);
        if(empty($submission) || empty($submission['user']) || erforms()->initial_login_status)
           return;
        
        $plans = !empty($submission['plans']) ? $submission['plans'] : array();
        $u = new WP_User($submission['user']['ID']);
        if(empty($u->ID))
            return;
        $existing_roles = $this->get_user_roles($submission['user']['ID']);
        $new_roles = array();
        foreach($plans as $p){
            $plan = erforms()->plan->get_plan($p['id']);
            if(!empty($plan) && !empty($plan['en_role'])){ // Assign user role
                $selected_roles = $plan['roles'];
                
                if(!empty($selected_roles)){
                    foreach($selected_roles as $r){
                        $u->add_role($r);
                        array_push($new_roles,$r);
                    }
                    if(!empty($plan['del_old_role'])){ // Removing previous user roles
                        foreach($existing_roles as $index=>$r){ // Removing previous roles
                            if(!in_array($r,$new_roles)){
                                $u->remove_role($r);
                                unset($existing_roles[$index]);
                            }
                        }
                    }
                }
                
            }
        }
   }
   
   public function user_meta_for_shortcode($args){
        $meta_key = isset($args['key']) ? sanitize_text_field($args['key']) : '';
        if(empty($meta_key)){
            return '';
        }
        $user_id = isset($args['id']) ? sanitize_text_field($args['id']) : '';
        if(empty($user_id)){
            $user = wp_get_current_user();
            if(!empty($user->ID)){
                $user_id = $user->ID;
            }
        }
        if(empty($user_id))
            return '';
        
        $meta_value = get_user_meta($user_id,$meta_key,true);
        if(empty($meta_value)){
            return '';
        }
        if(is_array($meta_value)){
            return implode(',', $meta_value);
        }
        return $meta_value;
   }
}
