<?php

class PersonalInfoForm extends CFormModel {
    
    public $test_user_name;

    public function rules() {
        return [
            ['test_user_name', 'required'],
            ['test_user_name', 'length', 'min' => '3', 'max'=> 200],
        ];
    }
    
    public function attributeLabels(){
        return [
            'test_user_name' => 'Full Name',
        ];
    }
    
    public function updatePersonalInfo(OrderAssessment $OrderAssessment){
        
        $test_user_email = sprintf('unishop%s@zohomail.com', time());
        $OrderAssessment->setAttributes([
            'test_user_name' => $this->test_user_name,
            'modified_on' => date('Y-m-d H:i:s'),
            'started_on' => date('Y-m-d H:i:s')
        ], false);
        
        if(!$OrderAssessment->test_user_email){
            $OrderAssessment->test_user_email = $test_user_email;
        }

        return $OrderAssessment->save(false);
    }

}
