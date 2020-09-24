<?php

/**
 * Form submissions
 *
 * @package    ERForms
 * @author     ERForms
 * @since      1.0.0
 */
class ERForms_Admin_Submission {
    
    /**
    * Primary class constructor.
    *
    * @since 1.0.0
    */
    public function __construct() {
           add_action( 'admin_init', array( $this, 'init' ) );
    }
    
    /**
    *
    * @since 1.0.0
    */
    public function init() {
        // Check what page we are on
	$page = isset( $_GET['page'] ) ? sanitize_text_field($_GET['page']) : '';
        
        // Only load if we are actually on the builder
        if ( 'erforms-submissions' === $page ) {
                // Load the class that builds the overview table.
                require_once ERFORMS_PLUGIN_DIR . 'includes/admin/submission/class-submission-table.php';
                add_action( 'erforms_admin_page',array( $this, 'table') );
                add_action( 'admin_enqueue_scripts', array( $this, 'enqueues' ) );
        } 
        else if ( 'erforms-submission' === $page ) {
                add_action( 'erforms_admin_page',array( $this, 'submission') );
                add_action( 'admin_enqueue_scripts', array( $this, 'enqueues' ) );
        }
        $delete_nonce = isset($_GET['delete_nonce']) ? sanitize_text_field($_GET['delete_nonce']) : '';
        if (!empty($delete_nonce) && wp_verify_nonce($delete_nonce, 'submission_delete')){
           $submission_id= absint($_GET['submission_id']);
           $form_id= erforms()->submission->get_meta($submission_id,'id');
           wp_delete_post($submission_id,true);
           $url= admin_url('admin.php?page=erforms-submissions&erform_id='.$form_id);
           wp_redirect($url);
           exit;        
           
        }
    }
    
    /**
    * Enqueue assets for the overview page.
    *
    * @since 1.0.0
    */
    public function enqueues() {
        wp_enqueue_script('erf-print-submission');
        do_action( 'erf_admin_submission_enqueue');
    }
    
    /**
    * @since 1.0.0
    */
    public function table() {
       include 'html/submissions.php';
    }
   
    public function revisions($submission){
        $revisions= erforms()->submission->get_revisions($submission['id']);
        include 'html/revisions.php'; 
    }
   /**
    * @since 1.0.0
    */
    public function submission() {
        $sub_id= absint($_REQUEST['submission_id']);
        $dis_edit_sub_nonce = isset($_GET['dis_edit_sub_nonce']) ? sanitize_text_field($_GET['dis_edit_sub_nonce']) : '';
        if(!empty($dis_edit_sub_nonce) && wp_verify_nonce($dis_edit_sub_nonce,'disable_edit_submission')){
                 erforms()->submission->update_meta($sub_id,'dis_edit_submission',1);
        }
        $en_edit_sub_nonce = isset($_GET['en_edit_sub_nonce']) ? sanitize_text_field($_GET['en_edit_sub_nonce']) : '';
        if(!empty($en_edit_sub_nonce) && wp_verify_nonce($en_edit_sub_nonce, 'enable_edit_submission')){
                erforms()->submission->update_meta($sub_id,'dis_edit_submission',0);
        }
        
        $submission= erforms()->submission->get_submission($sub_id);
        if(empty($submission))
            return;
        $view= !empty($_GET['view']) ? sanitize_text_field($_GET['view']) : '';
        if($view=='revisions'){
            $this->revisions($submission);
            return;
        }

        $this->change_payment_status($sub_id);
        $notes= $this->save_note($sub_id);
        $submission_model= erforms()->submission;
        $submission= $submission_model->get_submission($sub_id);
        // History submissions for same User
        $user_id= $submission_model->get_meta($sub_id,'user');
        $submissions= $submission_model->get_submissions_from_user($user_id,array($submission['id']),$submission['form_id']);
        $revisions= $submission_model->get_revisions($submission['id']);
        include 'html/submission.php';
   }
   
   private function save_note($sub_id){
       $submission_model= erforms()->submission;
       $submission= $submission_model->get_submission($sub_id);
       $form_model= erforms()->form;
       $form= $form_model->get_form($submission['form_id']);
       if(isset($_POST['add_note'])){
           $data= array();
           $data['text']= wpautop(wp_kses_post(wp_unslash($_POST['note_text'])));
           if(!empty($data['text'])){
                $short_tags = erforms_short_tags($form,$submission);
                $data['text'] = str_ireplace(array_keys($short_tags), array_values($short_tags), $data['text']); 
                if(!empty($_POST['notify_user'])){
                    $note_recipients= !empty($_POST['note_recipients']) ? sanitize_text_field(wp_unslash($_POST['note_recipients'])) : '';
                    if(!empty($note_recipients)){
                         $data['recipients']= $note_recipients;
                    }
                }
                $data['save']= !empty($_POST['save_note']) ? true : false;
                $submission_model->add_note($sub_id,$data);
           }
       }
       $notes= $submission_model->get_meta($sub_id,'submission_notes');
       if(is_array($notes))
           $notes= array_reverse($notes);
       
       return $notes;
   }
   
   private function change_payment_status($sub_id){
       $submission_model= erforms()->submission;
       $submission= $submission_model->get_submission($sub_id);
       $form_model= erforms()->form;
       $form= $form_model->get_form($submission['form_id']);
       
       if(isset($_POST['change_payment_status'])){
         $payment_status= sanitize_text_field($_POST['payment_status']);
         $notify_user= isset($_POST['notify_user']) ? 1 : 0;
         
         // Update Payment Status
         if($submission['payment_status']!=$payment_status)
         {
             $data= array();
             $data['text']= __('Payment status changed to','erforms').' '.ucwords($payment_status);
             $submission_model->add_note($sub_id,$data);
             $submission_model->update_meta($sub_id,'payment_status',$payment_status);
             do_action('erf_payment_status_changed',$payment_status,$submission['payment_status'],$submission['id'],$notify_user);
         }
       }
   }
}

new ERForms_Admin_Submission();