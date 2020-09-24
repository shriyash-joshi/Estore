<?php

class SurveyBasicInfo extends CFormModel {
    
    public $full_name;
    public $email;
    public $contact_number;

    public function rules(){
        return [
            ['full_name, email', 'required'],
            ['full_name', 'length', 'min' => 3, 'max' => 150],
            ['email', 'email'],
            ['contact_number', 'validateContactNumber'],
        ];
    }
    
    public function validateContactNumber(){
        if($this->contact_number && !is_numeric($this->contact_number)){
            $this->addError('contact_number', 'Invalid contact number');
        }
    }
    
    

}
