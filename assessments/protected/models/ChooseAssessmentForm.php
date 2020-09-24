<?php

class ChooseAssessmentForm extends CFormModel {
    
    const TEST_PSYCHOMETRIC = '12';
    const TEST_STREAM_SELECTOR = '8';
    const TEST_MULTI_INTELLEGENCE = '100';
    const TEST_LEARNING_STYLE = '101';
    const TEST_PERSONALITY_STYLE = '104';
    const TEST_ENGINEERING = '9';
    const TEST_HUMANITIES = '10';
    const TEST_COMMERCE = '11';
    const TEST_PROVIDER_CAREER_GUIDE = 'CareerGuide';
    const TEST_PROVIDER_KTS = 'KTS';
    
    public $assessment_name;

    public function rules() {
        return [
            ['assessment_name', 'required', 'message' => 'Please choose a assessment'],
            ['assessment_name', 'in', 'range' => array_keys($this->getTestNames())],
        ];
    }
    
    public function updateAssessment(OrderAssessment $OrderAssessment){
        
        $testNames = $this->getTestNames();
        
        if(!$OrderAssessment->test_id && !$OrderAssessment->test_provider){
            $OrderAssessment->setAttributes([
                'test_id' => $this->assessment_name,
                'test_provider' => $this->getTestProvider($this->assessment_name),
                'assessment_name' => isset($testNames[$this->assessment_name]) ? $testNames[$this->assessment_name] : null,
                'started_on' => date('Y-m-d H:i:s')
            ], false);
        }
        
        return $OrderAssessment->save(false);
    }

    public function getTestNames(){
        return [
            //self::TEST_PSYCHOMETRIC => 'Ideal Career Assessment',
            self::TEST_STREAM_SELECTOR => 'Stream Selector Assessment',
            self::TEST_MULTI_INTELLEGENCE => 'Multi Intelligence Assessment',
            self::TEST_LEARNING_STYLE => 'Learning Style Assessment',
            self::TEST_PERSONALITY_STYLE => 'Personality Style Assessment',
            self::TEST_HUMANITIES => 'Humanities Assessment',
            self::TEST_ENGINEERING => 'Engineering Assessment',
            self::TEST_COMMERCE => 'Commerce Assessment',
        ];
    }
    
    private function getTestProvider($assessment_name){
        
        switch($assessment_name){
            case self::TEST_PSYCHOMETRIC:
            case self::TEST_STREAM_SELECTOR:
            case self::TEST_ENGINEERING:
            case self::TEST_HUMANITIES:
            case self::TEST_COMMERCE:
                return self::TEST_PROVIDER_CAREER_GUIDE;
                
            case self::TEST_MULTI_INTELLEGENCE:
            case self::TEST_LEARNING_STYLE:
            case self::TEST_PERSONALITY_STYLE:
                return self::TEST_PROVIDER_KTS;
                
            default:
                return null;
            
        }
    }
    
}
