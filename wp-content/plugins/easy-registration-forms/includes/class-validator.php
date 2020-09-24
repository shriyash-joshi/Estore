<?php

class ERForms_Validator {
    
    public function validate($form,$data) {  
        if(empty($form))
            return array();
        
        $errors= array();
        if(!$this->verify_login_nonce($data['erform_submission_nonce'])){
            $errors[]= array('erf_form_error',__('Security token seems to be incorrect. Please reload the page and try again.','erforms'));
        }
        else
        {
            $form= apply_filters('erf_fields_before_validation',$form,$data);
            $errors= $this->validate_data($form['fields'],$data);
        }
        
        if(!empty($errors))
            return $errors;
        $submission_id= isset($data['submission_id']) ? absint($data['submission_id']) : 0;
        if(!empty($submission_id)){ // Submission edit
            if(!erforms_edit_permission($form,$submission_id))// Check for user permissions
            {
                $errors[]= array('edit_submission_permission',__('You are not allowed to edit this submission','erforms'));
            }
            return $errors;
        }
        $errors= $this->validate_payment($form,$data);
        $errors= apply_filters('erf_custom_validation',$errors,$form,$data);
        return $errors;
    }
    
    private function verify_login_nonce($nonce_value){
        return wp_verify_nonce($nonce_value,'erform_submission_nonce');
    }
    
    /*
     * Loop through all the fields to extract validation and type information from database.
     */
    public function validate_data($fields= array(),$data){
        $submission_id= isset($data['submission_id']) ? absint($data['submission_id']) : 0;
        $error_strings= erforms_error_strings();
        $errors= array();
        $form_id= absint($data['erform_id']);
        $form= erforms()->form->get_form($form_id);
        foreach($fields as $field){
            $field= (object) $field;
            
            if(!isset($field->name)) // Skip validation for non input fields
                continue;
            if(is_user_logged_in()){
                if($field->type=='user_email' || $field->type=='password'){
                    continue;
                }
                 
                $field_array= (array) $field;
                if(erforms_is_username_field($field_array)){
                    continue;
                }
            }
            
            if(!empty($field->required)){
                if($field->type=="file"){
                    $edit_submission= erforms()->frontend->edit_sub_status;
                    // Skip file validation.
                    if ($edit_submission /*&& $form['type']=='reg' && !in_array($field->name, $form['edit_fields'])*/) {
                        continue;
                    }
                    else if(!isset($_FILES[$field->name]) || !ERForms_Validation::is_file_uploaded($field->name)){
                     $errors[]= array($field->name,$field->label.': is not uploaded.');
                    }
                }
                else if(!isset($data[$field->name]) || !ERForms_Validation::required($data[$field->name]))
                    $errors[]= array($field->name,$field->label.': is required field.');
                
            }

            if(!empty($field->user_roles)){ // User roles enabled for a field
               $valid_role_selected= false; 
               if(empty($data[$field->name]))  // If no user role passed with request then mark it as valid.
                   $valid_role_selected= true;
               else {
                   if(is_array($field->values)){
                    foreach($field->values as $role){
                        if($data[$field->name]==$role['value']){
                            $valid_role_selected= true;
                            break;
                        }
                     }
                   }
               }
               
               if(!$valid_role_selected){
                   $errors[]= array('invalid_user_role',$field->label.': Invalid Role selected.');
               }
               
            }
            if(!empty($field->maxlength) && isset($data[$field->name]) && !ERForms_Validation::maxlength($data[$field->name],$field->maxlength)){
                $errors[]= array($field->name,$field->label.': can not be greater than '.$field->maxlength);
            }
            
            if(!empty($field->minlength) && isset($data[$field->name]) && !ERForms_Validation::minlength($data[$field->name],$field->minlength)){
               $errors[]= array($field->name,$field->label.': can not be less than '.$field->minlength);
            }
            
            if(!empty($field->minlength) && isset($data[$field->name]) && !ERForms_Validation::minlength($data[$field->name],$field->minlength)){
               $errors[]= array($field->name,$field->label.': can not be less than '.$field->minlength);
            }
           
            if($field->type=="date"){
                $field_date_pattern= erforms_php_date_format_by_js_format($field->dataDateFormat);
                if(!empty($field->max) && isset($data[$field->name]) && !ERForms_Validation::maxDate($data[$field->name],$field->max,$field_date_pattern)){
                    $dt= DateTime::createFromFormat('m/d/Y', $field->max);
                    $max_value= !empty($dt) ? $dt->format($field_date_pattern) : $field->max;
                    $errors[]= array($field->name,$field->label.': can not be greater than '.$max_value);
                }
                
                if(!empty($field->min) && isset($data[$field->name]) && !ERForms_Validation::minDate($data[$field->name],$field->min,$field_date_pattern)){
                    $dt= DateTime::createFromFormat('m/d/Y', $field->min);
                    $min_value= !empty($dt) ? $dt->format($field_date_pattern) : $field->min;
                    $errors[]= array($field->name,$field->label.': can not be less than '.$min_value);
                }
            }
            
            
            if(!empty($field->enableUnique) && isset($data[$field->name]) && !ERForms_Validation::is_unique($data[$field->name],$field->name,$form_id,$submission_id)){
               $errors[]= array($field->name,$field->label.':'.__('Value already exists in database.','erforms'));
            }
            
            if(isset($data[$field->name]) && method_exists('ERForms_Validation',$field->type)){
				$ftype= $field->type;
                if($field->type=='text'){
                    if(!ERForms_Validation::$ftype($field->type,$data[$field->name])){
                        if($field->type=="user_email"){
                            $errors[]= array($field->name,$field->label.": Invalid email address."); 
                        }
                        else
                        {
                            $errors[]= array($field->name,$field->label.": Invalid value.");
                        }
                    }
                }
                else if($field->type=='date'){
                    $df= erforms_php_date_format_by_js_format($field->dataDateFormat);
                    if(!ERForms_Validation::date($data[$field->name],$df)){
                        $errors[]= array($field->name,$field->label.":".__('Invalid date','erforms'));
                    }
                }
                else if(!ERForms_Validation::$ftype($data[$field->name])){
                    $error_message= ' Invalid value';
                    if(isset($error_strings[$field->type])){
                        $error_message= $error_strings[$field->type];
                    }
                    $errors[]= array($field->name,$field->label.":".$error_message); 
                }
                
            }
            
            
            if($field->type=="file" && isset($_FILES[$field->name]) && ERForms_Validation::is_file_uploaded($field->name)){
               
                // Validate for file extensions
                if(isset($field->accept)){  
                    $accept= trim($field->accept);
                    if(!empty($accept)){
                        $accept= str_replace(' ',',',$accept); // Replace any space with comma
                        $allowed= explode(',', $accept); 
                        $FILES= array($_FILES[$field->name]);
                        foreach($FILES as $FILE){ 
                            if(!ERForms_Validation::verify_file_type($allowed,$FILE,$field->name)){
                                $err_string = sprintf(__('%s: %s is not in correct format. Allowed formats are %s.','erforms'),$field->label,$FILE['name'],implode(',',$allowed));
                                $errors[]= array($field->name,$err_string);
                            }
                        }

                    }
                }
            }
            // Server side checking for pattern regex
            if(!empty($field->pattern) && isset($data[$field->name])){
                if(!empty($data[$field->name]) && !preg_match('/^'.$field->pattern.'$/',$data[$field->name])){
                    $errors[]= array($field->name,$field->label.': '.__('Invalid value.','erforms'));
                }
            }
        }
        return $errors;
    }
    
    /*
     * Validates Payment Price as per the assigned Plan
     */
    private function validate_payment($form,$data){
        $errors= array();
        $options_model= erforms()->options;
        $options= $options_model->get_options();

        // Check if payment method enabled
        if(empty($options['payment_methods']) || ($form['type']=='contact' && !is_user_logged_in()))
        {
            return $errors;
        }
        
        $plan_model = erforms()->plan;
        if(empty($form['plan_enabled']) || empty($form['plans']['enabled']))
            return $errors;
        
        
        $plan_group= array();
        
        $enabled_req= array_intersect($form['plans']['enabled'], $form['plans']['required']); // Getting plans which are both enabled and required
        $grouped_plans= array();
        
        // Check if required plans are grouped in Radio group
        if(!empty($form['allow_single_plan']))
        {
            foreach($enabled_req as $plan_id){ // Filtering only product type of plans
                $plan= $plan_model->get_plan($plan_id);
                if(!empty($plan) && $plan['type']=='product'){
                    array_push($grouped_plans, $plan_id);
                }
            }
            
            if(!empty($grouped_plans)){// Validating if any plan was selected
                $plan_id=  isset($data['plan_id']) ? absint($data['plan_id']) : 0;
                if(empty($plan_id)){
                    $errors[]= array('form_payment_error',__('Please select all the required payment options.','erforms'));
                    return $errors;
                }
            }
        }
        $enabled_req= array_diff($enabled_req, $grouped_plans);
        foreach($enabled_req as $p_id){
           $plan= $plan_model->get_plan($p_id);
           if(!empty($plan)){
                if($plan['type']=='user' || $plan['type']=='product'){
                    $val=  isset($data['plan_id_'.$p_id]) ? (float) $data['plan_id_'.$p_id] : 0;
                    if(empty($val)){
                        $errors[]= array('form_payment_error',__('Please select all the required payment options.','erforms'));
                        return $errors;
                    }
                }
           }
        }
        /*
         * Verifying payment method
         */
        $payment_method= sanitize_text_field($data['payment_method']);
        if(empty($payment_method)) // No payment method selected
        {
            $errors[]= array('payment_method_not_selected',__('Payment method not selected.','erforms'));
        }
        
        // Check if payment method is enabled
        if(!in_array($payment_method,$options['payment_methods'])){
             $errors[]= array('invalid_payment_method',__('Selected payment method is not available.','erforms'));
        }
        
        return $errors;
        
    }
    

}