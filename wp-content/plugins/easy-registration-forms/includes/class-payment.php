<?php

class ERForms_Offline_Payment
{   
    /**
     * Primary class constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {
        add_action('erf_settings_payment', array($this,'payment_settings'));
        add_filter('erf_before_save_settings', array($this,'save_settings'));
        add_filter('erf_before_submission_insert', array($this, 'update_submission'),10,3);
        add_filter('erf_default_global_options',array($this,'global_options'));
        add_action('erf_post_submission', array($this, 'post_submission'));
        add_filter('erf_before_user_activation',array($this,'before_user_activation'),10,4);
        
    }
    
    /*
     * Loads Offline related global settings
     */
    public function payment_settings($options){
        include 'admin/settings/html/payment-settings.php';
    }
    
    /*
     * Saving offline related settings
     */
    public function save_settings($options){
      $post = wp_unslash($_POST);  
      $options['send_offline_email'] =   isset($post['send_offline_email']) ? 1 : 0;
      $options['offline_email'] =   wp_kses_post($post['offline_email']);
      $options['offline_email_from'] =   sanitize_text_field($post['offline_email_from']);
      $options['offline_email_from_name'] =   sanitize_text_field($post['offline_email_from_name']);
      $options['offline_email_subject'] =   sanitize_text_field($post['offline_email_subject']);
      
      $options['en_payment_pending_email'] =   isset($post['en_payment_pending_email']) ? 1 : 0;
      $options['payment_pending_email'] =   isset($post['payment_pending_email']) ? wp_kses_post($post['payment_pending_email']) : '';
      $options['pending_pay_email_from'] =   sanitize_text_field($post['pending_pay_email_from']);
      $options['pending_pay_email_from_name'] =   sanitize_text_field($post['pending_pay_email_from_name']);
      $options['pending_pay_email_subject'] =   sanitize_text_field($post['pending_pay_email_subject']);
      
      $options['en_payment_completed_email'] =   isset($post['en_payment_completed_email']) ? 1 : 0;
      $options['payment_completed_email'] =   isset($post['payment_completed_email']) ? wp_kses_post($post['payment_completed_email']) : '';
      $options['completed_pay_email_from'] =   sanitize_text_field($post['completed_pay_email_from']);
      $options['completed_pay_email_from_name'] =   sanitize_text_field($post['completed_pay_email_from_name']);
      $options['completed_pay_email_subject'] =   sanitize_text_field($post['completed_pay_email_subject']);
      
      return $options;
    }
    
    //Update Payment related meta
    public function update_submission($meta,$form_id,$data){
            $form_model= erforms()->form;
            $form= $form_model->get_form($form_id);
            $options_model= erforms()->options;
            $options= $options_model->get_options();
            $plan_model = erforms()->plan;
            $original_meta= $meta;
            
            if(empty($form['plan_enabled'])) // Checking if plan is enabled and form is of Registration type
                return $meta;
            
            $payment_method= isset($data['payment_method']) ? $data['payment_method'] : '';
            
            $meta['erform_plans']= array();
            $amount= 0;
            
            if(!empty($form['allow_single_plan'])){
                $plan_id=  isset($data['plan_id']) ? absint($data['plan_id']) : 0;
                if(!empty($plan_id)){
                    $plan= erforms()->plan->get_plan($plan_id); // Confirming plan exists in database
                    if(!empty($plan)){
                        array_push($meta['erform_plans'],array('plan'=>$plan,'amount'=>$plan['price'],'id'=>$plan['id']));
                        $amount += $plan['price'];
                    }
                }
            }
            
            foreach($form['plans']['enabled'] as $p_id){
                $plan= $plan_model->get_plan($p_id);  
                if(!empty($plan)){
                    if($plan['type']=='product'){
                        $selected_plan_id=  isset($data['plan_id_'.$p_id]) ? absint($data['plan_id_'.$p_id]) : 0;
                        if(!empty($selected_plan_id)){
                            $selected_plan= erforms()->plan->get_plan($selected_plan_id);
                            if(!empty($selected_plan)){
                                array_push($meta['erform_plans'],array('plan'=>$selected_plan,'amount'=>$selected_plan['price'],'id'=>$selected_plan['id']));
                                $amount += $plan['price'];
                            }
                        }
                        /*else if(empty($form['allow_single_plan']) && in_array($plan['id'], $form['plans']['required'])){
                            $selected_plan= erforms()->plan->get_plan($plan['id']);
                            if(!empty($selected_plan)){
                                array_push($meta['erform_plans'],array('plan'=>$selected_plan,'amount'=>$selected_plan['price'],'id'=>$selected_plan['id']));
                                $amount += $plan['price'];
                            }
                        }*/
                    }
                    else if($plan['type']=='user')
                    {
                        $price_value=  isset($data['plan_id_'.$p_id]) ? (float) ($data['plan_id_'.$p_id]) : 0;
                        if(!empty($price_value)){
                            array_push($meta['erform_plans'],array('plan'=>$plan,'amount'=>$price_value,'id'=>$plan['id']));
                            $amount += $price_value;
                        }
                    }
                }
            }
            
            $meta['erform_amount']= sprintf('%0.2f',$amount);
            if(empty($meta['erform_amount']) || $amount<=0){
                $meta['erform_payment_status']= ERFORMS_COMPLETED;
                $meta['erform_payment_method']= 'none';
            }
            else
            {
              $meta['erform_payment_status']= ERFORMS_PENDING;
              $meta['erform_payment_method']= $payment_method;
            }
            $meta['erform_payment_invoice']= wp_generate_password(10,false,false);
            $meta['erform_currency']= $options['currency']; 
            return $meta;
        }
        
        public function global_options($options){
            $options['offline_email']='';
            $options['offline_email_from']='';
            $options['offline_email_from_name']='';
            $options['offline_email_subject']='';
            return $options;
        }
        
        public function post_submission($submission){
            if(empty($submission['plans']))
                return;
            foreach($submission['plans'] as $temp){
                erforms()->submission->update_meta($submission['id'], 'plan_id_'.$temp['id'], 1);
            }
            if($submission['payment_status']==ERFORMS_PENDING){ // Payment status pending
                wp_schedule_single_event(time() + 50,'erf_sub_payment_pending',array($submission['id']));
            }
        }
        
        public function before_user_activation($status,$user_id,$submission,$form){
            $options= erforms()->options->get_options();
            
            if(empty($status) || empty($form['plan_enabled']) || empty($form['plans']) || empty($submission['payment_method']))
                return $status;
            
            if(empty($form['plans']['required'])){
                return $status;
            }
            if($submission['payment_method']=='offline' && $submission['payment_status']=='pending'){
                if(count($options['payment_methods'])>1){ //If multiple payment methods are enabled
                    $status= false;
                }
            }

            return $status;
        }
}

new ERForms_Offline_Payment;