<?php
class ERForms_Emails
{   
    private $from= null;
    private $from_name= null;
    private static $instance = null;
    
    private function __construct()
    {   
        add_action('erf_post_submission_completed', array($this,'post_submission_notification'),20);
        add_action('erf_async_post_edit_submission', array($this,'post_edit_submission_notification'),20); // Called after current_time + 10
        add_action('erf_post_submission', array($this,'auto_reply_user'),20); 
        add_action('erf_post_edit_submission', array($this,'edit_sub_auto_reply_to_user'),20);
        add_action('erf_async_user_activated',array($this,'async_user_activated'),20); // Called after current_time + 20
        add_action('erf_user_activated',array($this,'user_activated'),20);
        add_action('wp_ajax_erf_send_uninstall_feedback',array($this,'send_uninstall_feedback'),20);
        add_action('erf_send_verification_link',array($this,'send_verification_link'),20,4);
        add_action('erf_submission_deleted',array($this,'send_sub_deletion_notification'),20,3);
        add_action('erf_sub_payment_pending',array($this,'payment_pending'),20,1);
        add_action('erf_payment_status_changed',array($this,'payment_status_changed'),20,4);
        add_action('erf_submission_note_processed',array($this,'notify_note_to_user'),20,2);
    }
    
    public static function get_instance()
    {   
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }
    
    /*
     * Notifies admin on submission completion
     */
    public function async_notify_admin($sub_id){
        $submission_model= erforms()->submission;
        $submission= $submission_model->get_submission($sub_id);
        $form= erforms()->form->get_form($submission['form_id']);
        if(empty($form['enable_admin_notification']))
            return;
        
        
        $subject= $form['admin_notification_subject'];
        $message=  do_shortcode(wpautop($form['admin_notification_msg']));
        
        // Submission and Form field short tags
        $short_tags = erforms_short_tags($form,$submission);
        $subject = str_ireplace(array_keys($short_tags), array_values($short_tags), $subject);
        
        $to= $form['admin_notification_to'];
        if(empty($to)){
           $to= get_option('admin_email');
        }
        
        if(!empty($form['admin_notification_from'])){
             $this->from= $form['admin_notification_from'];
             add_filter( 'wp_mail_from', array($this,'set_email_from'));
        }
        if(!empty($form['admin_notification_from_name'])){
            $this->from_name= $form['admin_notification_from_name'];
            add_filter( 'wp_mail_from_name', array($this,'set_email_from_name'));
        }
        
        add_filter('wp_mail_content_type',array($this,'set_html_content_type'));
        $message = str_ireplace(array_keys($short_tags), array_values($short_tags), $message);
        $message= apply_filters('erf_admin_sub_email',$message,$submission); // Allows to dynamically update the email content
        wp_mail($to,$subject,$message);
        remove_filter('wp_mail_content_type',array($this,'set_html_content_type'));
        $this->from= null;
        remove_filter('wp_mail_from',array($this,'set_email_from'));
        $this->from_name= null;
        remove_filter('wp_mail_from_name',array($this,'set_email_from_name'));
    }
    
    /*
     * Send auto reply message to user. Called after user creation for registration forms.
     */
    public function auto_reply_user($submission){
        $submission_id= $submission['id']; // Fetching submission entry from database for latest values
        $submission= erforms()->submission->get_submission($submission_id);
        $form= erforms()->form->get_form($submission['form_id']);
        if(empty($form['enabled_auto_reply']))
            return;
        $subject= $form['auto_reply_subject'];
        $message= $form['auto_reply_msg'];
        
        if($form['type']=='reg'){
            $user= isset($submission['user']) ? $submission['user'] : false;
            if(empty($user))
                return false;
            $to= $user['user_email'];
        }
        else
        {
           if(!empty($form['auto_reply_to'])){
               $to= $form['auto_reply_to'];
           }
           else if(!empty($submission['primary_field_val'])){
               $to= $submission['primary_field_val'];
           }
           else{
                return;
           }
        }
        // Submission and Form field short tags
        $short_tags = erforms_short_tags($form,$submission);
        $to = str_ireplace(array_keys($short_tags), array_values($short_tags), $to);
        $subject = str_ireplace(array_keys($short_tags), array_values($short_tags), $subject);
        
        $message= do_shortcode(wpautop($message));
        
        if(!empty($form['auto_reply_from'])){
             $this->from= $form['auto_reply_from'];
             add_filter( 'wp_mail_from', array($this,'set_email_from'));
        }
        
        if(!empty($form['auto_reply_from_name'])){
            $this->from_name= $form['auto_reply_from_name'];
            add_filter( 'wp_mail_from_name', array($this,'set_email_from_name'));
        }
        $message = str_ireplace(array_keys($short_tags), array_values($short_tags), $message);
        $message= apply_filters('erf_auto_reply_email',$message,$submission); // Allows to dynamically update the email content    
        add_filter('wp_mail_content_type',array($this,'set_html_content_type'));
        wp_mail($to,$subject,$message);
        remove_filter('wp_mail_content_type',array($this,'set_html_content_type'));
        $this->from= null;
        remove_filter('wp_mail_from',array($this,'set_email_from'));
        $this->from_name= null;
        remove_filter('wp_mail_from_name',array($this,'set_email_from_name'));
    }
    
    public function async_user_activated($user_id){     
        $this->user_activated($user_id);
    }
    
    public function set_html_content_type($content_type) {
	return 'text/html';
    }
    
    public function user_activated($user_id){
        $user_model= erforms()->user;
        $form_id= $user_model->get_meta($user_id,'form');
        if(empty($form_id))
            return false;
        
        $form= erforms()->form->get_form($form_id);
        if(empty($form['enable_act_notification']))
            return false;
                
        $user= $user_model->get_user($user_id);
        $subject= $form['user_act_subject'];
        $message= $form['user_act_msg'];
        $to= $user->user_email;
        
        $submission_id = $user_model->get_meta($user_id,'submission');
        $submission = erforms()->submission->get_submission($submission_id);
        $short_tags = erforms_short_tags($form,$submission);
        if(!empty($submission)){
            $placeholders= array('{{first_name}}','{{last_name}}'); // Old placeholders till 2.0.3 version
            $placeholder_values= array('{{user_firstname}}','{{user_lastname}}');
            $message= str_ireplace($placeholders, $placeholder_values, $message);
            $subject = str_ireplace(array_keys($short_tags), array_values($short_tags), $subject);
        } else {
             // Parsing for any dynamic values (Backward compatibility)
            $placeholders= array('{{first_name}}','{{last_name}}','{{user_email}}','{{display_name}}');
            $placeholder_values= array($user->first_name,$user->last_name,$user->user_email,$user->display_name);
            $message= str_ireplace($placeholders, $placeholder_values, $message);
        }
        $message= do_shortcode(wpautop($message));
        if(!empty($form['user_act_from'])){
             $this->from= $form['user_act_from'];
             add_filter( 'wp_mail_from', array($this,'set_email_from'));
        }
        if(!empty($form['user_act_from_name'])){
            $this->from_name= $form['user_act_from_name'];
            add_filter( 'wp_mail_from_name', array($this,'set_email_from_name'));
        }
        $message = str_ireplace(array_keys($short_tags), array_values($short_tags), $message);
        $message= apply_filters('erf_user_activated_email',$message,$user_id); // Allows to dynamically update the email content
        
        add_filter('wp_mail_content_type',array($this,'set_html_content_type'));
        wp_mail($to,$subject,$message);
        remove_filter('wp_mail_content_type',array($this,'set_html_content_type'));
        $this->from= null;
        remove_filter('wp_mail_from',array($this,'set_email_from'));
        $this->from_name= null;
        remove_filter('wp_mail_from_name',array($this,'set_email_from_name'));
    }
    
    public function report_issue($name,$email,$subject,$message){
        $subject= $subject;
        $message= $message;
        $message.= ' <br> From: '.$email;
        $to= 'erformswp@gmail.com';
        add_filter('wp_mail_content_type',array($this,'set_html_content_type'));
        wp_mail($to,$subject,$message);
        remove_filter('wp_mail_content_type',array($this,'set_html_content_type'));
    }
    
    public function quick_email($to,$subject,$message,$from='',$from_name=''){
        $message= do_shortcode(wpautop($message));
        if(!empty($from)){
            $this->from= $from;
            add_filter( 'wp_mail_from', array($this,'set_email_from'));
        }
        
        if(!empty($from_name)){
            $this->from_name= $from_name;
            add_filter( 'wp_mail_from_name', array($this,'set_email_from_name'));
        }
        
        add_filter('wp_mail_content_type',array($this,'set_html_content_type'));
        wp_mail($to,$subject,$message);
        remove_filter('wp_mail_content_type',array($this,'set_html_content_type'));
        if(!empty($this->from)){
            $this->from= null;
            remove_filter('wp_mail_from',array($this,'set_email_from'));
        }
        remove_filter('wp_mail_from_name',array($this,'set_email_from_name'));
    }
    
    public function offline_payment_notification($sub_id){
        $options= erforms()->options->get_options();
        $submission_model= erforms()->submission;
        $submission= $submission_model->get_submission($sub_id);
        if(empty($options['send_offline_email']) || empty($options['offline_email']) || empty($submission['payment_method'])){
            return;
        }
        
        if($submission['payment_method']!='offline'){
            return;
        }
        
        $user= isset($submission['user']) ? $submission['user'] : false;
        if(!empty($user)){
            $to=  $user['user_email'];
        }
        else if(!empty($submission['primary_field_val'])){
            $to= $submission['primary_field_val'];
        }
        else{
            return;
        }
        $form= erforms()->form->get_form($submission['form_id']);
        $subject = !empty($options['offline_email_subject']) ? $options['offline_email_subject'] : $form['name'].' '.__('Offline Payment','erforms');
        $message= $options['offline_email'];
        // Submission and Form field short tags
        $short_tags = erforms_short_tags($form,$submission);
        $subject = str_ireplace(array_keys($short_tags), array_values($short_tags), $subject);
        
        $message= do_shortcode(wpautop($message));
        $message = str_ireplace(array_keys($short_tags), array_values($short_tags), $message);
        $message= apply_filters('erf_offline_email',$message,$submission); // Allows to dynamically update the email content
        
        
        if(!empty($options['offline_email_from'])){
             $this->from= $options['offline_email_from'];
             add_filter( 'wp_mail_from', array($this,'set_email_from'));
        }
        
        if(!empty($options['offline_email_from_name'])){
            $this->from_name= $options['offline_email_from_name'];
            add_filter( 'wp_mail_from_name', array($this,'set_email_from_name'));
        }
        
        add_filter('wp_mail_content_type',array($this,'set_html_content_type'));
        wp_mail($to,$subject,$message);
        remove_filter('wp_mail_content_type',array($this,'set_html_content_type'));   
        remove_filter( 'wp_mail_from', array($this,'set_email_from'));
        remove_filter( 'wp_mail_from_name', array($this,'set_email_from_name'));
        
    }
    
    public function post_submission_notification($sub_id){
        $this->async_notify_admin($sub_id);
        $this->offline_payment_notification($sub_id);
    }
    
    /* Responds user after edit submission */
    public function edit_sub_auto_reply_to_user($submission){
        $submission_id= $submission['id']; // Fetching submission entry from database for latest values
        $submission= erforms()->submission->get_submission($submission_id);
        $form= erforms()->form->get_form($submission['form_id']);
   
        if(empty($form['enable_edit_notifications']))
            return;
        
        $user= isset($submission['user']) ? $submission['user'] : false;
        if(!empty($user)){
            $to= $user['user_email'];
        }
        else if(!empty($submission['primary_field_val'])){
            $to= $submission['primary_field_val'];
        }
        else{
            return false;
        }
        $subject= $form['edit_sub_user_subject'];
        $message= $form['edit_sub_user_email'];
        
        // Submission and Form field short tags
        $short_tags = erforms_short_tags($form,$submission);
        $subject = str_ireplace(array_keys($short_tags), array_values($short_tags), $subject);
        
        $message= do_shortcode(wpautop($message));
        if(!empty($form['edit_sub_user_from'])){
             $this->from= $form['edit_sub_user_from'];
             add_filter( 'wp_mail_from', array($this,'set_email_from'));
        }
        
        if(!empty($form['edit_sub_user_from_name'])){
            $this->from_name= $form['edit_sub_user_from_name'];
            add_filter( 'wp_mail_from_name', array($this,'set_email_from_name'));
        }
        $message = str_ireplace(array_keys($short_tags), array_values($short_tags), $message);
        $message= apply_filters('erf_edit_sub_auto_reply_email',$message,$submission); // Allows to dynamically update the email content
        
        add_filter('wp_mail_content_type',array($this,'set_html_content_type'));
        wp_mail($to,$subject,$message);
        remove_filter('wp_mail_content_type',array($this,'set_html_content_type'));
        $this->from= null;
        remove_filter('wp_mail_from',array($this,'set_email_from'));
        remove_filter('wp_mail_from_name',array($this,'set_email_from_name'));
    
    }
    
    /*Notify admin after edit submission */
    public function notify_edit_sub_to_admin($sub_id){
        $submission_model= erforms()->submission;
        $submission= $submission_model->get_submission($sub_id);
        
        $form= erforms()->form->get_form($submission['form_id']);
        if(empty($form['enable_edit_notifications']))
            return;
        
        
        $subject= $form['edit_sub_admin_subject'];
        $message= $form['edit_sub_admin_email'];

        // Submission and Form field short tags
        $short_tags = erforms_short_tags($form,$submission);
        $subject = str_ireplace(array_keys($short_tags), array_values($short_tags), $subject);
        
        $to= $form['edit_sub_admin_list'];
        if(empty($to)){
           $to= get_option('admin_email');
        }
        $message= do_shortcode(wpautop($message));
        if(!empty($form['edit_sub_admin_from'])){
             $this->from= $form['edit_sub_admin_from'];
             add_filter( 'wp_mail_from', array($this,'set_email_from'));
        }
        
        if(!empty($form['edit_sub_admin_from_name'])){
            $this->from_name= $form['edit_sub_admin_from_name'];
            add_filter( 'wp_mail_from_name', array($this,'set_email_from_name'));
        }
        
        add_filter('wp_mail_content_type',array($this,'set_html_content_type'));
        $message = str_ireplace(array_keys($short_tags), array_values($short_tags), $message);
        $message= apply_filters('erf_edit_sub_admin_email',$message,$submission); // Allows to dynamically update the email content
       
        wp_mail($to,$subject,$message);
        remove_filter('wp_mail_content_type',array($this,'set_html_content_type'));
        $this->from= null;
        remove_filter('wp_mail_from',array($this,'set_email_from'));
        remove_filter('wp_mail_from_name',array($this,'set_email_from_name'));
    }
    
    public function post_edit_submission_notification($sub_id){
         $this->notify_edit_sub_to_admin($sub_id);
    }
    
    public function report_usage(){
        $subject= 'ERForms Usage';
        $message= get_site_url();
        $to= 'erformswp@gmail.com';
        add_filter('wp_mail_content_type',array($this,'set_html_content_type'));
        wp_mail($to,$subject,$message);
        remove_filter('wp_mail_content_type',array($this,'set_html_content_type'));
    } 
    
    public function send_uninstall_feedback(){
        $subject= 'Uninstall Feedback';
        $message= esc_html(sanitize_text_field($_POST['msg']));
        $message .= ' <br> Site: '.get_site_url();
        $to= 'erformswp@gmail.com';
        add_filter('wp_mail_content_type',array($this,'set_html_content_type'));
        wp_mail($to,$subject,$message);
        remove_filter('wp_mail_content_type',array($this,'set_html_content_type'));
        wp_die();
    }
    
    public function send_submission_report($report,$path){
        $subject= $report['email_subject'];
        $message= $report['email_message'];
        $message= do_shortcode(wpautop($message));
        if(!empty($report['receipents'])){
            $to= $report['receipents'];
        }else{
            $to= get_option('admin_email');
        }
        add_filter('wp_mail_content_type',array($this,'set_html_content_type'));
        wp_mail($to,$subject,$message,'',$path);
        remove_filter('wp_mail_content_type',array($this,'set_html_content_type'));
    }
    
    public function set_email_from($from){
        if(!empty($this->from)){
            return $this->from;
        }
        return $from;
    }
    
    public function set_email_from_name($from_name){
        if(!empty($this->from_name)){
            return $this->from_name;
        }
        return $from_name;
    }
    
    public function send_verification_link($user_id,$hash,$form,$sub_id){
        if(empty($form['en_user_ver_msg'])){
            return;
        }
        
        if(!empty($form['user_ver_subject'])){
            $subject= $form['user_ver_subject'];
        }
        else
        {
            $subject= __('Account Verification','erforms');
        }
        
        $submission= erforms()->submission->get_submission($sub_id);
        if(empty($submission)) return;
        $message= $form['user_ver_email_msg'];
        
        $short_tags = erforms_short_tags($form,$submission);
        $subject = str_ireplace(array_keys($short_tags), array_values($short_tags), $subject);
        $url = !empty($form['after_user_ver_page']) ? add_query_arg(array('erf_account_hash'=>$hash,'erf_form'=>$form['id']),get_permalink($form['after_user_ver_page'])) : add_query_arg(array('erf_account_hash'=>$hash,'action'=>'erf_account_verification','erf_form'=>$form['id']),admin_url('admin-ajax.php'));
        $message= str_ireplace('{{verification_link}}',"<a href='$url' targe='_blank'>$url</a>",$message);        
        $message= do_shortcode(wpautop($message));
        $message = str_ireplace(array_keys($short_tags), array_values($short_tags), $message);
        $message = apply_filters('erf_verification_email',$message,$submission);
        
        $user= get_user_by('id',$user_id);
        $to= $user->user_email;
        if(!empty($form['user_ver_from'])){
             $this->from= $form['user_ver_from'];
             add_filter( 'wp_mail_from', array($this,'set_email_from'));
        }
        
        if(!empty($form['user_ver_from_name'])){
            $this->from_name= $form['user_ver_from_name'];
            add_filter( 'wp_mail_from_name', array($this,'set_email_from_name'));
        }
        add_filter('wp_mail_content_type',array($this,'set_html_content_type'));
        wp_mail($to,$subject,$message);
        remove_filter('wp_mail_content_type',array($this,'set_html_content_type'));
        $this->from= null;
        remove_filter('wp_mail_from',array($this,'set_email_from'));
        $this->from_name= null;
        remove_filter('wp_mail_from_name',array($this,'set_email_from_name'));
    }
    
    
    // Send notification on submission deletion
    public function send_sub_deletion_notification($form,$submission,$user){
        if(empty($form['enable_delete_notifications'])) // Submission notification not enabled
            return false;
        /*
         * Admin Notification
         */
        $subject= $form['delete_sub_admin_subject'];
        if(empty($subject)){
            $subject= __('Submission Deleted','erforms');
        }
        $message= $form['delete_sub_admin_email'];
        // Submission and Form field short tags
        $short_tags = erforms_short_tags($form,$submission);
        $subject = str_ireplace(array_keys($short_tags), array_values($short_tags), $subject);
        
        $message = str_ireplace(array_keys($short_tags), array_values($short_tags), $message);
        $message = apply_filters('erf_sub_del_admin_email',do_shortcode(wpautop($message)),$submission);
        
        if(!empty($form['delete_sub_admin_from'])){
             $this->from= $form['delete_sub_admin_from'];
             add_filter( 'wp_mail_from', array($this,'set_email_from'));
        }
        
        if(!empty($form['delete_sub_admin_from_name'])){
            $this->from_name= $form['delete_sub_admin_from_name'];
            add_filter( 'wp_mail_from_name', array($this,'set_email_from_name'));
        }
        $to= $form['delete_sub_admin_list'];
        if(empty($to)){
           $to= get_option('admin_email');
        }
        
        add_filter('wp_mail_content_type',array($this,'set_html_content_type'));
        wp_mail($to,$subject,$message);
        remove_filter('wp_mail_content_type',array($this,'set_html_content_type'));
        $this->from= null;
        remove_filter('wp_mail_from',array($this,'set_email_from'));
        $this->from_name= null;
        remove_filter('wp_mail_from_name',array($this,'set_email_from_name'));
        
        // Admin notification ends here
        
        /*
         * User notification
         */
        $subject= $form['delete_sub_user_subject'];
        if(empty($subject)){
            $subject= __('Submission Deleted','erforms');
        }
        $to= $user->user_email;
        $message= $form['delete_sub_user_email'];
        // Submission and Form field short tags
        $short_tags = erforms_short_tags($form,$submission);
        $message = str_ireplace(array_keys($short_tags), array_values($short_tags), $message);
        $subject = str_ireplace(array_keys($short_tags), array_values($short_tags), $subject);
        $message= do_shortcode(wpautop($message));
        $message = apply_filters('erf_sub_del_user_email',$message,$submission);
        if(!empty($form['delete_sub_user_from'])){
             $this->from= $form['delete_sub_user_from'];
             add_filter( 'wp_mail_from', array($this,'set_email_from'));
        }
        
        if(!empty($form['delete_sub_user_from_name'])){
            $this->from_name= $form['delete_sub_user_from_name'];
            add_filter( 'wp_mail_from_name', array($this,'set_email_from_name'));
        }
        
        add_filter('wp_mail_content_type',array($this,'set_html_content_type'));
        wp_mail($to,$subject,$message);
        remove_filter('wp_mail_content_type',array($this,'set_html_content_type'));
        $this->from= null;
        remove_filter('wp_mail_from',array($this,'set_email_from'));
        $this->from_name= null;
        remove_filter('wp_mail_from_name',array($this,'set_email_from_name'));
    }
    
    public function payment_pending($sub_id){
        $submission= erforms()->submission->get_submission($sub_id);
        if(empty($submission) || empty($submission['plans']) || $submission['payment_status']!=ERFORMS_PENDING){
            return;
        }
        $form= erforms()->form->get_form($submission['form_id']);
        $options= erforms()->options->get_options();
        if(empty($options['en_payment_pending_email']))
            return;
        $from_email= $options['pending_pay_email_from'];
        
        if(empty($from_email)){
           $from_email= get_option('admin_email');
        }
        $this->from= $from_email;
        add_filter( 'wp_mail_from', array($this,'set_email_from'));
        
        if(!empty($options['pending_pay_email_from_name'])){
            $this->from_name= $options['pending_pay_email_from_name'];
            add_filter( 'wp_mail_from_name', array($this,'set_email_from_name'));
        }
        $subject= $options['pending_pay_email_subject'];
        if(empty($subject)){
            $subject= __('Pending Payment','erforms');
        }

        // Submission and Form field short tags
        $short_tags = erforms_short_tags($form,$submission);
        $subject = str_ireplace(array_keys($short_tags), array_values($short_tags), $subject);
        
        if(!empty($submission['user'])){
            $to= $submission['user']['user_email'];
        }
        else if(!empty($submission['primary_field_val'])){
            $to= $submission['primary_field_val'];
        }
        else{
            return;
        }
        $message = str_ireplace(array_keys($short_tags), array_values($short_tags), $options['payment_pending_email']);
        $message= apply_filters('erf_pending_pay_email_msg',do_shortcode(wpautop($message)),$submission);
        
        add_filter('wp_mail_content_type',array($this,'set_html_content_type'));
        wp_mail($to,$subject,$message);
        remove_filter('wp_mail_content_type',array($this,'set_html_content_type'));
        $this->from= null;
        remove_filter('wp_mail_from',array($this,'set_email_from'));
        $this->from_name= null;
        remove_filter('wp_mail_from_name',array($this,'set_email_from_name'));
    }
    
    public function payment_completed($sub_id){
        // User Notification
        $submission= erforms()->submission->get_submission($sub_id);
        if(empty($submission) || empty($submission['plans']) || $submission['payment_status']!=ERFORMS_COMPLETED){
            return;
        }
        $form= erforms()->form->get_form($submission['form_id']);
        $options= erforms()->options->get_options();
        if(!empty($options['en_payment_completed_email'])){
            $from_email= $options['completed_pay_email_from'];
            if(empty($from_email)){
               $from_email= get_option('admin_email');
            }
            $this->from= $from_email;
            add_filter( 'wp_mail_from', array($this,'set_email_from'));

            if(!empty($options['completed_pay_email_from_name'])){
                $this->from_name= $options['completed_pay_email_from_name'];
                add_filter( 'wp_mail_from_name', array($this,'set_email_from_name'));
            }
            $subject= $options['completed_pay_email_subject'];
            if(empty($subject)){
                $subject= __('Payment Completed','erforms');
            }
            $message=  $message= do_shortcode(wpautop($options['payment_completed_email']));
            
            if(!empty($submission['user'])){
                $to= $submission['user']['user_email'];
            }
            else if(!empty($submission['primary_field_val'])){
                $to= $submission['primary_field_val'];
            }
            else{
                return;
            }
            // Submission and Form field short tags
            $short_tags = erforms_short_tags($form,$submission);
            $subject = str_ireplace(array_keys($short_tags), array_values($short_tags), $subject);
            $message = str_ireplace(array_keys($short_tags), array_values($short_tags), $message);
            $message= apply_filters('erf_completed_pay_email_msg',$message,$submission);
            
            
            add_filter('wp_mail_content_type',array($this,'set_html_content_type'));
            wp_mail($to,$subject,$message);
            remove_filter('wp_mail_content_type',array($this,'set_html_content_type'));
            $this->from= null;
            remove_filter('wp_mail_from',array($this,'set_email_from'));
            $this->from_name= null;
            remove_filter('wp_mail_from_name',array($this,'set_email_from_name'));
        }
        
        
        //Admin Notification
        if(!empty($options['en_pay_completed_admin_email'])){
            $from_email= $options['completed_pay_admin_email_from'];
            if(!empty($from_email)){
                $this->from= $from_email;
                add_filter( 'wp_mail_from', array($this,'set_email_from'));
            }
            
            if(!empty($options['completed_pay_admin_email_from_name'])){
                $this->from_name= $options['completed_pay_admin_email_from_name'];
                add_filter( 'wp_mail_from_name', array($this,'set_email_from_name'));
            }
            $subject= $options['completed_pay_admin_email_subject'];
            if(empty($subject)){
                $subject= __('Payment Completed','erforms');
            }
            add_filter('wp_mail_content_type',array($this,'set_html_content_type'));
            $to= get_option('admin_email');
            if(!empty($options['completed_pay_admin_email_to'])){
                $to= $options['completed_pay_admin_email_to'];
            }
            $message= do_shortcode(wpautop($options['pay_completed_admin_email']));
            // Submission and Form field short tags
            $short_tags = erforms_short_tags($form,$submission);
            $message = str_ireplace(array_keys($short_tags), array_values($short_tags), $message);
            $subject = str_ireplace(array_keys($short_tags), array_values($short_tags), $subject);
            $message= apply_filters('erf_completed_pay_admin_email_msg',$message);
            wp_mail($to,$subject,$message);
            remove_filter('wp_mail_content_type',array($this,'set_html_content_type'));
            $this->from= null;
            remove_filter('wp_mail_from',array($this,'set_email_from'));
            $this->from_name= null;
            remove_filter('wp_mail_from_name',array($this,'set_email_from_name'));
        }
    }
    
    public function payment_status_changed($new_status,$old_status,$sub_id,$notify){
        if(!empty($notify) && $new_status=='pending'){
            $this->payment_pending($sub_id);
        }
        else if(!empty($notify) && $new_status=='completed'){
            $this->payment_completed($sub_id);
        }
        
    }
    
    // Function will send notification if note is added from individual submission page.
    public function notify_note_to_user($submission,$note){
        if(empty($note['recipients'])){
            return;
        }
        $to= $note['recipients'];
        $form= erforms()->form->get_form($submission['form_id']);
        
        if (!has_action('erf_custom_sub_user_note_email') && !empty($to)){
            $options= erforms()->options->get_options();
            $display_name='';
            $message = !empty($options['note_user_email']) ? str_ireplace(array('{{message}}'), array($note['text']), wpautop($options['note_user_email'])) : __('Hello','erforms').' ' . ucwords($display_name) . '<br><br>' . $note['text'];
            $subject = !empty($options['note_user_email_sub']) ? $options['note_user_email_sub'] : ucwords($form['title']) . ' ' . __('Notification', 'erforms');
            
            // Submission and Form field short tags
            $short_tags = erforms_short_tags($form,$submission);
            $message = str_ireplace(array_keys($short_tags), array_values($short_tags), do_shortcode(wpautop($message)));
            $subject = str_ireplace(array_keys($short_tags), array_values($short_tags), $subject);

            $this->quick_email($to, $subject, $message, $options['note_user_email_from'], $options['note_user_email_from_name']);
        }
        else
        {
            do_action('erf_custom_sub_user_note_email',$submission);
        }
    }
    
}

ERForms_Emails::get_instance();