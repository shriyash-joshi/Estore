<?php

class ChooseProgramForm extends CFormModel {

    public $assessment_name;

    public function rules() {
        return [
            ['assessment_name', 'required', 'message' => 'Please choose a assessment'],
            ['assessment_name', 'in', 'range' => array_keys($this->getTestNames())],
        ];
    }

    public function updateAssessment(OrderAssessment $OrderAssessment) {

        $testNames = $this->getTestNames();

        $OrderAssessment->setAttributes([
            'test_id' => $this->assessment_name,
            'test_provider' => $this->getTestProvider($this->assessment_name),
            'assessment_name' => $testNames[$this->assessment_name],
            'started_on' => date('Y-m-d H:i:s')
        ], false);

        return $OrderAssessment->save(false);
    }

    public function getTestNames() {
        return [
            'architecture' => 'Architecture',
            'chartered-accountant' => 'Chartered accountancy',
            'computer-engineering' => 'Computer Engineering',
            'civil-services' => 'Civil Services',
            'dentist' => 'Dentistry',
            'ethical-hacking' => 'Ethical Hacking',
            'family-business' => 'Family Business',
            'fashion-designing' => 'Fashion Designing',
            'film-making' => 'Film making',
            'fund-management' => 'Fund Management',
            'graphic-designing' => 'Graphic Designing',
            'hospitality' => 'Hospitality',
            'law' => 'Law',
            'marketing' => 'Marketing',
            'mechanical-engineering' => 'Mechanical Engineering',
            'medicine' => 'Medicine',
            'psychology' => 'Psychology',
            'teaching' => 'Teaching'
        ];
    }

    private function getTestProvider($assessment_name) {
        return 'Immrse';
    }

}
