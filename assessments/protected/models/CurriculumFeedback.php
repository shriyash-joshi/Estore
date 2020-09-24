<?php

class CurriculumFeedback extends CFormModel {
    
    public $rating;
    public $contact_number;
    public $time_slot;
    
    
    public function rules(){
        return [
            ['rating', 'required', 'message' => 'Please select a rating'],
            ['rating', 'in', 'range' => [1, 2, 3, 4]],
            ['contact_number', 'validateContactNumber'],
            ['time_slot', 'length', 'max' => 250]
        ];
    }
    
    public function validateContactNumber(){
        if($this->contact_number && !is_numeric($this->contact_number)){
            $this->addError('contact_number', 'Invalid contact number');
        }
    }
    
    

}
