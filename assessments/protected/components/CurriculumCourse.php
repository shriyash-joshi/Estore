<?php

class CurriculumCourse{
    
    private $logger;
    private $Questions;
    private $ws_total = 0;
    private $fm_total = 0;
    private $sd_total = 0;
    private $ca_total = 0;

    private $ws_max_total = 0;
    private $fm_max_total = 0;
    private $sd_max_total = 0;
    private $ca_max_total = 0;
    
    private $ws = 0;
    private $fm = 0;
    private $sd = 0;
    private $ca = 0;
    
    private $messages = [];
    
    private $boards = [
        'ib' => [
            'ws' => ['min' => 0.8, 'max' => 0.9, 'calc' => 0],
            'fm' => ['min' => 0.8, 'max' => 0.9, 'calc' => 0],
            'sd' => ['min' => 0.8, 'max' => 0.9, 'calc' => 0],
            'ca' => ['min' => 0.8, 'max' => 0.9, 'calc' => 0],
        ],
        'cambridge' => [
            'ws' => ['min' => 0.7, 'max' => 0.8, 'calc' => 0],
            'fm' => ['min' => 0.7, 'max' => 0.8, 'calc' => 0],
            'sd' => ['min' => 0.7, 'max' => 0.8, 'calc' => 0],
            'ca' => ['min' => 0.7, 'max' => 0.8, 'calc' => 0],
        ],
        'icse' => [
            'ws' => ['min' => 0.5, 'max' => 0.6, 'calc' => 0],
            'fm' => ['min' => 0.6, 'max' => 0.7, 'calc' => 0],
            'sd' => ['min' => 0.6, 'max' => 0.7, 'calc' => 0],
            'ca' => ['min' => 0.6, 'max' => 0.7, 'calc' => 0],
        ],
        'cbse' => [
            'ws' => ['min' => 0.4, 'max' => 0.5, 'calc' => 0],
            'fm' => ['min' => 0.4, 'max' => 0.5, 'calc' => 0],
            'sd' => ['min' => 0.4, 'max' => 0.5, 'calc' => 0],
            'ca' => ['min' => 0.4, 'max' => 0.5, 'calc' => 0],
        ],
    ];
    
    private $boards_avg = ['ib' => 0, 'cambridge' => 0, 'icse' => 0, 'cbse' => 0];

    public function __construct() {
        $this->logger = Klogger::instance(Yii::getPathOfAlias('application') 
                . DIRECTORY_SEPARATOR 
                . 'runtime', 
                Klogger::DEBUG, 
                'curriculum_course'
        );
        
        $this->addQuestions();
        
    }

    public function getBoardsMinMax(){
        return $this->boards;
    }

    public function getQuestionsTotal(){
        return count($this->Questions);
    }
    
    public function setAnswer(&$Question, $answer){
        $method_name = 'setAnswer' . $Question['type'];
        if(method_exists($this, $method_name)){
            call_user_func_array([$this, $method_name], [&$Question, $answer]);
        }
    }
    
    public function setAnswers($Answers){
        foreach($this->Questions as &$Question){
            
            if($Question['type'] == 'Questions'){
                foreach($Question['Questions'] as &$_Question){
                    if(isset($Answers[(String)$_Question['id']])){
                        $this->setAnswer($_Question, $Answers[(string)$_Question['id']]);
                    }
                }
            }else{
                if(isset($Answers[$Question['id']])){
                    $this->setAnswer($Question, $Answers[$Question['id']]);
                }
            }
        }
    }

    public function setQuestions($Questions){
        $this->Questions = $Questions;
    }
    
    public function getQuestions(){
        return $this->Questions;
    }
 
    public function getSuggestedBoards(){
        $this->runCalculation();
        asort($this->boards_avg);
        
        $this->_d('');
        $this->_d($this->boards_avg);
        
        return $this->boards_avg;
    }
    
    public function getWs(){
        return $this->ws;
    }
    
    public function getFm(){
        return $this->fm;
    }
    
    public function getSd(){
        return $this->sd;
    }
    
    public function getCa(){
        return $this->ca;
    }

    public function getMessages(){
        return $this->messages;
    }
    
    public function getAspirationsMatch($grade, $board){
        
        $am = [null, null, null, null,null, null, null, null,null, null, null, null,null, null, null, null];
        foreach($this->Questions as &$Question){
            if($Question['type'] == 'Questions'){
                foreach($Question['Questions'] as $Ques){
                    $this->getAm($Ques, $grade, $board, $am);
                }
            }else{
                $this->getAm($Question, $grade, $board, $am);
            }
        }
        
        return array_unique(array_values(array_filter($am)));
    }
    
    private function getAm($Question, $index, $board, &$am){
        if(isset($Question[$index])){
            $answer = $this->getSelectedAnswer($Question);
            foreach($Question[$index] as $m){
                if($m['board'] !== $board) continue;
                
                if(isset($m['inverse']) && $m['inverse']){
                    if(!in_array($answer, $m['match'])){
                        array_splice($am, $m['order'], 0, $m['label']);
                    }
                }else{
                    if(in_array($answer, $m['match'])){
                        array_splice($am, $m['order'], 0, $m['label']);
                    }
                }
            }
        }
    }
    
    private function getSelectedAnswer($Question){
        $label = null;
        
        if(isset($Question['value']) && $Question['value']){
            return $Question['value'];
        }
        
        if($Question['type'] == 'InputRadio'){
            foreach($Question['options'] as $Option){
                if(isset($Option['selected']) && $Option['selected']){
                    return $Option['label'];
                }
            }
        }
        
        return $label;
    }
    
    private function runCalculation(){
        
        $this->_d('Running Curriculum Course');
        
        foreach($this->Questions as &$Question){
            if($Question['type'] == 'Questions'){
                foreach($Question['Questions'] as $_Question){
                    $this->calcQuestionWeights($_Question);
                }
            }else{
                $this->calcQuestionWeights($Question);
            }
        }
        
        $this->_d('');
        $this->_d('ws_total: ' . $this->ws_total);
        $this->_d('ws_max_total: ' . $this->ws_max_total);
        $this->_d('fm_total: ' . $this->fm_total);
        $this->_d('fm_max_total: ' . $this->fm_max_total);
        $this->_d('sd_total: ' . $this->sd_total);
        $this->_d('sd_max_total: ' . $this->sd_max_total);
        $this->_d('ca_total: ' . $this->ca_total);
        $this->_d('ca_max_total: ' . $this->ca_max_total);
        
        $this->ws = $this->ws_total/$this->ws_max_total;
        $this->fm = $this->fm_total/$this->fm_max_total;
        $this->sd = $this->sd_total/$this->sd_max_total;
        $this->ca = $this->ca_total/$this->ca_max_total;
        
        $this->_d('');
        $this->_d('ws: ' . $this->ws);
        $this->_d('fm: ' . $this->fm);
        $this->_d('sd: ' . $this->sd);
        $this->_d('ca: ' . $this->ca);
        
        foreach($this->boards as &$_board){
            $_board['ws']['calc'] = ($this->ws >= $_board['ws']['min']) ? 0 : $_board['ws']['min'] - $this->ws;
            
            $_board['fm']['calc'] = ($this->fm >= $_board['fm']['min'] && $this->fm <= $_board['fm']['max']) 
                    ? 0 
                    : min([abs($this->fm - $_board['fm']['min']), abs($this->fm - $_board['fm']['max'])]);
            
            $_board['sd']['calc'] = ($this->sd >= $_board['sd']['min'] && $this->sd <= $_board['sd']['max']) 
                    ? 0 
                    : min([abs($this->sd - $_board['sd']['min']), abs($this->sd - $_board['sd']['max'])]);
            
            $_board['ca']['calc'] = ($this->ca >= $_board['ca']['min'] && $this->ca <= $_board['ca']['max']) 
                    ? 0 
                    : min([abs($this->ca - $_board['ca']['min']), abs($this->ca - $_board['ca']['max'])]);
        }

        foreach($this->boards as $label => $b){
            $this->_d('');
            $this->_d($label);
            $this->_d($b);
            $this->boards_avg[$label] = ($b['ws']['calc']*10 + $b['fm']['calc']*26 + $b['sd']['calc']*32 + $b['ca']['calc']*32)/100;
        }
        
    }

    private function calcQuestionWeights($Question){
        if(!isset($Question['weights']) || !$Question['weights']) return;
        
        $this->_d('');
        $this->_d('Question: ' . $Question['title']);
        foreach($Question['weights'] as $key => $weight){
            $method_name = 'calc' . ucfirst($key);
            if(method_exists($this, $method_name)){
                call_user_func_array([$this, $method_name], [$weight, $Question['options'], ($Question['type'] == 'InputCheckbox')]);
            }
        }
    }
    
    protected function calcWs($ws, $Options, $is_multiple){
        foreach($Options as $index => $Option){
            if(isset($Option['selected']) && $Option['selected']){
                $this->_d($Option['label']);
                $this->_d('ws: ' . ($ws['weight'] * $ws['score'][$index]));
                $this->ws_total += ($ws['weight'] * $ws['score'][$index]);
                if($is_multiple){
                    $this->ws_max_total += ($ws['weight'] * $ws['score'][$index]);
                }else{
                    $this->ws_max_total += max($ws['score']) * $ws['weight'];
                    break;
                }
            }
        }
        
        $this->_d('ws_total: ' . $this->ws_total);
        $this->_d('ws_max_total: ' . $this->ws_max_total);
    }
    
    protected function calcFm($fm, $Options, $is_multiple){
        foreach($Options as $index => $Option){
            if(isset($Option['selected']) && $Option['selected']){
                $this->_d($Option['label']);
                $this->_d('fm: ' . ($fm['weight'] * $fm['score'][$index]));
                $this->fm_total += ($fm['weight'] * $fm['score'][$index]);
                if($is_multiple){
                    $this->fm_max_total += ($fm['weight'] * $fm['score'][$index]);
                }else{
                    $this->fm_max_total += max($fm['score']) * $fm['weight'];
                    break;
                }
            }
        }
        
        $this->_d('fm_total: ' . $this->fm_total);
        $this->_d('fm_max_total: ' . $this->fm_max_total);
    }
    
    protected function calcSd($sd, $Options, $is_multiple = false){
        foreach($Options as $index => $Option){
            if(isset($Option['selected']) && $Option['selected']){
                $this->_d($Option['label']);
                $this->_d('sd: ' . ($sd['weight'] * $sd['score'][$index]));
                $this->sd_total += ($sd['weight'] * $sd['score'][$index]);
                if($is_multiple){
                    $this->sd_max_total += ($sd['weight'] * $sd['score'][$index]);
                }else{
                    $this->sd_max_total += max($sd['score']) * $sd['weight'];
                    break;
                }
            }
        }
        
        $this->_d('sd_total: ' . $this->sd_total);
        $this->_d('sd_max_total: ' . $this->sd_max_total);
    }
    
    protected function calcCa($ca, $Options, $is_multiple = false){
        foreach($Options as $index => $Option){
            if(isset($Option['selected']) && $Option['selected']){
                $this->_d($Option['label']);
                $this->_d('ca: ' . ($ca['weight'] * $ca['score'][$index]));
                $this->ca_total += ($ca['weight'] * $ca['score'][$index]);
                if($is_multiple){
                    $this->ca_max_total += ($ca['weight'] * $ca['score'][$index]);
                }else{
                    $this->ca_max_total += max($ca['score']) * $ca['weight'];
                    break;
                }
            }
        }
        
        $this->_d('ca_total: ' . $this->ca_total);
        $this->_d('ca_max_total: ' . $this->ca_max_total);
    }
    
    protected function setAnswerInputText(&$Question, $answer){
        $Question['value'] = trim($answer);
    }

    protected function setAnswerInputEmail(&$Question, $answer){
        $this->setAnswerInputText($Question, $answer);
    }

    protected function setAnswerTextArea(&$Question, $answer){
        $this->setAnswerInputText($Question, $answer);
    }

    protected function setAnswerInputMobile(&$Question, $answer){
        $this->setAnswerInputText($Question, $answer);
    }

    protected function setAnswerInputDate(&$Question, $answer){
        $Question['value'] = date('Y-m-d', strtotime(trim($answer)));
    }

    protected function setAnswerInputRadio(&$Question, $answer){
        foreach($Question['options'] as &$Option){
            if($Option['label'] == trim($answer)){
                $Option['selected'] = true;
                break;
            }
        }
    }
    
    protected function setAnswerInputCheckbox(&$Question, $answer){
        $answers = json_decode($answer, true);
        if(json_last_error() == JSON_ERROR_NONE){
            foreach($Question['options'] as &$Option){
                if(in_array($Option['label'], $answers)){
                    $Option['selected'] = true;
                }
            }
        }
    }
    
    public function getQuestion($id){
        
        if(floor($id) !== $id){
            $key = $this->getQuestionIndex(floor($id));
            return $this->Questions[$key][$this->getSubQuestionIndex($key, $id)];
        }
        
        return $this->Questions[$this->getQuestionIndex($id)];
    }
    
    public function getQuestionIndex($id){
        foreach($this->Questions as $index => $Question){
            if($Question['id'] == $id) return $index;
        }
        return null;
    }
    
    public function getSubQuestionIndex($key, $id){
        foreach($this->Questions[$key]['Questions'] as $index => $Questions){
            if($Questions['id'] == $id) return $index;
        }
        return null;
    }
    
    public function validateQuestion(&$Question, &$values){
        
        $required = (isset($Question['required']) && $Question['required']);
        if(isset($Question['value']) && $Question['value']){
            $required = true;
        }
        
        if(!$required) return true;
        
        if($Question['type'] == 'InputText'){
            if((!$Question['value'] || !trim($Question['value']))){
                $Question['error'] = true;
                $Question['error_message'] = 'Please fill the required information';
                return false;
            }
            
            array_push($values, $Question['value']);
            return true;
        }
        
        if($Question['type'] == 'TextArea'){
            if((!$Question['value'] || !trim($Question['value']))){
                $Question['error'] = true;
                $Question['error_message'] = 'Please fill the required information';
                return false;
            }
            
            array_push($values, $Question['value']);
            return true;
        }
        
        if($Question['type'] == 'InputEmail'){
            if((!$Question['value'] || !filter_var($Question['value'], FILTER_VALIDATE_EMAIL))){
                $Question['error'] = true;
                $Question['error_message'] = 'Invalid email address';
                return false;
            }
            
            array_push($values, $Question['value']);
            return true;
        }
        
        if($Question['type'] == 'InputMobile'){
            if((!$Question['value'] || !is_numeric($Question['value']))){
                $Question['error'] = true;
                $Question['error_message'] = 'Invalid mobile number';
                return false;
            }
            
            array_push($values, $Question['value']);
            return true;
        }
        
        if($Question['type'] == 'InputDate'){
            if((!$Question['value'] || !strtotime($Question['value']))){
                $Question['error'] = true;
                $Question['error_message'] = 'Invalid date';
                return false;
            }
            
            array_push($values, $Question['value']);
            return true;
        }
        
        if($Question['type'] == 'InputRadio'){
            $selected = false;
            foreach($Question['options'] as $Option){
                if($Option['selected']){
                    $selected = true;
                    if(isset($Question['unique']) && $Question['unique'] && in_array($Option['label'], $values)){
                        $Question['error'] = true;
                        $Question['error_message'] = 'You already selected this option';
                        return false;
                    }
                    
                    array_push($values, $Option['label']);
                }
            }
            
            if(!$selected){
                $Question['error'] = true;
                $Question['error_message'] = 'Please select any one option';
                return false;
            }
        }
        
        if($Question['type'] == 'InputCheckbox'){
            $selected = false;
            foreach($Question['options'] as $Option){
                if($Option['selected']) $selected = true;
            }
            
            if(!$selected){
                $Question['error'] = true;
                $Question['error_message'] = 'Please select all applicable options';
                return false;
            }
        }
        
        return true;
    }
    
    private function addQuestions(){
        $this->Questions = [
            
            ['id' => 1,
                'title' => 'Parent Information',
                'type' => 'Questions',
                'Questions' => [
                    ['id' => 1.1,
                        'title' => 'DOB of Parent (Optional)',
                        'type' => 'InputDate',
                        'placeholder' => 'Parent date of birth',
                        'required' => false,
                        'value' => null,
                    ],
                    
                    ['id' => 1.2,
                        'title' => 'Spouse Name (Optional)',
                        'type' => 'InputText',
                        'placeholder' => 'Spouse name',
                        'required' => false,
                        'value' => null,
                    ],
                ],
                'required' => true,
                'filter' => range(0, 12),
            ],
            
            ['id' => 2,
                'title' => 'Total No. of Children',
                'type' => 'InputRadio',
                'inline' => true,
                'options' => [
                    ['label' => 1, 'selected' => false],
                    ['label' => 2, 'selected' => false],
                    ['label' => 3, 'selected' => false],
                    ['label' => 4, 'selected' => false],
                    ['label' => 5, 'selected' => false]
                ],
                'weights' => [
                    'ws' => ['score' => [5, 3, 1, 1, 1], 'weight' => 0.2],
                ],
                'required' => true,
                'filter' => range(0, 12),
            ],

            ['id' => 3,
                'title' => 'Child Information',
                'type' => 'Questions',
                'Questions' => [
                    
                    ['id' => 3.1,
                        'title' => 'Name of your Child',
                        'type' => 'InputText',
                        'placeholder' => 'Your child name',
                        'value' => null,
                        'required' => true,
                    ],
                    
                    ['id' => 3.2,
                        'title' => 'Relationship with the Child',
                        'type' => 'InputRadio',
                        'options' => [
                            ['label' => 'Mother', 'selected' => false],
                            ['label' => 'Father', 'selected' => false],
                            ['label' => 'Gaurdian', 'selected' => false]
                        ],
                        'required' => true,
                    ],
                    
                    
                ],
                'required' => true,
                'filter' => range(0, 12),
            ],
            
            ['id' => 4,
                'title' => 'Child Information',
                'type' => 'Questions',
                'Questions' => [
                    
                    ['id' => 4.1,
                        'title' => 'Gender of your Child',
                        'type' => 'InputRadio',
                        'options' => [
                            ['label' => 'Male', 'selected' => false],
                            ['label' => 'Female', 'selected' => false],
                            ['label' => 'Prefer not to say', 'selected' => false]
                        ],
                        'required' => true,
                    ],
                    
                    ['id' => 4.2,
                        'title' => 'What is your Child\'s date of birth?',
                        'type' => 'InputDate',
                        'placeholder' => 'Child date of birth',
                        'value' => null,
                        'required' => true,
                    ],
                    
                    ['id' => 4.3,
                        'title' => 'Child\'s Citizenship (Optional)',
                        'type' => 'InputText',
                        'placeholder' => 'Child\'s citizenship',
                        'value' => null,
                        'am1' => [
                            ['board' => 'ib',
                                'label' => 'Possibility of International Relocation',
                                'alt' => '',
                                'about' => '',
                                'match' => ['Indian'], 'order' => 7, 'inverse' => true,
                            ],
                        ],
                        'required' => false,
                    ],
                    
                ],
                'required' => true,
                'filter' => range(0, 12),
            ],
            
            ['id' => 5,
                'title' => 'In what location does your child study?',
                'type' => 'Questions',
                'Questions' => [
                    ['id' => 5.1,
                        'type' => 'InputText',
                        'title' => 'City Name',
                        'placeholder' => 'City name',
                        'value' => null,
                        'required' => true,
                        'css_class' => 'g_autocomplete g_city_name',
                    ],
                    
                    ['id' => 5.2,
                        'type' => 'InputText',
                        'title' => 'State Name',
                        'placeholder' => 'State name',
                        'value' => null,
                        'required' => true,
                        'css_class' => 'g_state_name',
                    ],
                    
                    ['id' => 5.3,
                        'type' => 'InputText',
                        'title' => 'Country Name',
                        'placeholder' => 'Country name',
                        'value' => null,
                        'required' => true,
                        'css_class' => 'g_country_name',
                    ],
                ],
                'required' => true,
                'filter' => range(0, 12),
            ],

            ['id' => 6,
                'title' => 'Child\'s current grade',
                'type' => 'InputRadio',
                'options' => $this->getGradeOptions(),
                'required' => true,
                'filter' => range(0, 12),
            ],

            ['id' => 7,
                'type' => 'Questions',
                'Questions' => [
                    ['id' => 7.1,
                        'title' => 'Name of the School',
                        'type' => 'InputText',
                        'placeholder' => 'School name',
                        'value' => null,
                        'required' => true,
                    ],
                    
                    ['id' => 7.2,
                        'title' => 'What is your child\'s current curriculum?',
                        'type' => 'InputRadio',
                        'options' => [
                            ['label' => 'American Curriculum', 'selected' => false], 
                            ['label' => 'Cambridge', 'selected' => false], 
                            ['label' => 'IB', 'selected' => false], 
                            ['label' => 'CBSE', 'selected' => false], 
                            ['label' => 'ICSE', 'selected' => false],
                            ['label' => 'StateBoard', 'selected' => false],
                            ['label' => 'Others', 'selected' => false]
                        ],
                        'required' => true,
                    ],
                ],
                'required' => true,
                'filter' => range(0, 12),
            ],

            ['id' => 8,
                'title' => 'What is your Annual Household Income?',
                'type' => 'InputRadio',
                'options' => [
                    ['label' => 'Less than 10 Lakh per annum', 'selected' => false],
                    ['label' => '10 - 20 Lakh per annum', 'selected' => false], 
                    ['label' => '20 - 30 Lakh per annum', 'selected' => false], 
                    ['label' => '30 - 50 Lakh per annum', 'selected' => false],
                    ['label' => '50 - 80 Lakh per annum', 'selected' => false],
                    ['label' => 'Above 80 Lakh per annum', 'selected' => false],
                ],
                'weights' => [
                    'ws' => ['score' => [1, 1, 3, 5, 5, 5], 'weight' => 0.8],
                ],
                'required' => true,
                'filter' => range(0, 12),
            ],

            ['id' => 9,
                'title' => 'What is the current Annual School Fee that you pay for your child?',
                'type' => 'InputRadio',
                'options' => [
                    ['label' => 'Not Yet Started', 'selected' => false],
                    ['label' => 'Less than Rs 50000', 'selected' => false],
                    ['label' => '50000 - 1 Lakh', 'selected' => false],
                    ['label' => '1 Lakh - 2 Lakh', 'selected' => false],
                    ['label' => '2 Lakh - 3 Lakh', 'selected' => false],
                    ['label' => '3 Lakh - 4 Lakh', 'selected' => false],
                    ['label' => 'Above 4 Lakhs', 'selected' => false],
                ],
                'am3' => [
                    [
                        'board' => 'cbse',
                        'label' => 'School choice within budget',
                        'match' => ['Less than Rs 50000', '50000 - 1 Lakh', '1 Lakh - 2 Lakh'], 'order' => 2,
                    ],
                    [
                        'board' => 'icse',
                        'label' => 'School choice within budget',
                        'match' => ['Less than Rs 50000', '50000 - 1 Lakh', '1 Lakh - 2 Lakh'], 'order' => 2,
                    ],
                ],
                'am2' => [
                    [
                        'board' => 'cbse',
                        'label' => 'School choice within budget',
                        'match' => ['Less than Rs 50000', '50000 - 1 Lakh', '1 Lakh - 2 Lakh'], 'order' => 2,
                    ],
                ],
                'weights' => [
                    'ws' => ['score' => [1, 2, 2, 4, 5, 5, 5], 'weight' => 0.7],
                ],
                'required' => true,
                'filter' => range(0, 12),
            ],

            ['id' => 10,
                'title' => 'In the last 12 months, how much have you spent for your child towards extra-curricular activities, hobby classes and other non-academic pursuits, i.e. Sports, art classes, etc. Do not include coaching & tuition class fees',
                'type' => 'InputRadio',
                'options' => [
                    ['label' => 'Less than 10,000', 'selected' => false],
                    ['label' => '10,000 - 20,000', 'selected' => false],
                    ['label' => '20,000 - 30,000', 'selected' => false],
                    ['label' => '30,000 - 40,000', 'selected' => false],
                    ['label' => 'Above 40,000', 'selected' => false],
                ],
                'am3' => [
                    [
                        'board' => 'icse',
                        'label' => 'Focus on extracurricular activities',
                        'match' => ['20,000 - 30,000', '30,000 - 40,000', 'Above 40,000'], 'order' => 3,
                    ],
                    [
                        'board' => 'cambridge',
                        'label' => 'Focus on extracurricular activities',
                        'match' => ['20,000 - 30,000', '30,000 - 40,000', 'Above 40,000'], 'order' => 3,
                    ],
                    [
                        'board' => 'ib',
                        'label' => 'Focus on extracurricular activities',
                        'match' => ['20,000 - 30,000', '30,000 - 40,000', 'Above 40,000'], 'order' => 3,
                    ],
                ],
                'am2' => [
                    [
                        'board' => 'icse',
                        'label' => 'Focus on extracurricular activities',
                        'match' => ['20,000 - 30,000', '30,000 - 40,000', 'Above 40,000'], 'order' => 1,
                    ],
                    [
                        'board' => 'cambridge',
                        'label' => 'Focus on extracurricular activities',
                        'match' => ['20,000 - 30,000', '30,000 - 40,000', 'Above 40,000'], 'order' => 2,
                    ],
                    [
                        'board' => 'ib',
                        'label' => 'Focus on extracurricular activities',
                        'match' => ['20,000 - 30,000', '30,000 - 40,000', 'Above 40,000'], 'order' => 2,
                    ],
                ],
                'weights' => [
                    'ws' => ['score' => [1, 1, 3, 4, 5], 'weight' => 0.7],
                    'fm' => ['score' => [1, 1, 3, 4, 5], 'weight' => 0.7],
                    'sd' => ['score' => [1, 1, 3, 4, 5], 'weight' => 0.7],
                ],
                'required' => true,
                'filter' => range(1, 12),
            ],

            ['id' => 11,
                'title' => 'If you had the opportunity to, would you',
                'type' => 'InputRadio',
                'options' => [
                    ['label' => 'Increase expenditure on extra-curricular activities', 'selected' => false],
                    ['label' => 'Decrease Expenditure on extra-curricular activities', 'selected' => false],
                    ['label' => 'Neither', 'selected' => false],
                    ['label' => 'Not sure', 'selected' => false],
                    ['label' => 'Other', 'selected' => false]
                ],
                'weights' => [
                    'ws' => ['score' => [5, 1, 3, 1, 0], 'weight' => 0.4],
                    'fm' => ['score' => [5, 1, 3, 1, 0], 'weight' => 0.2],
                    'sd' => ['score' => [5, 1, 3, 1, 0], 'weight' => 0.4],
                ],
                'required' => true,
                'filter' => range(1, 12),
            ],

            ['id' => 12,
                'title' => 'Please rate the following activities based on what you are most likely to do as a family on weekends.',
                'type' => 'Questions',
                'Questions' => [
                    ['id' => 12.1,
                        'title' => 'Eat out in a restaurant',
                        'type' => 'InputRadio',
                        'options' => $this->optionsLikely(),
                        'weights' => [
                            'ws' => ['score' => [5, 4, 3, 2, 1], 'weight' => 0.05],
                            'ca' => ['score' => [5, 4, 3, 2, 1], 'weight' => 0.15],
                        ],
                        'required' => true,
                    ],

                    ['id' => 12.2,
                        'title' => 'Sports & Outdoor Activities',
                        'type' => 'InputRadio',
                        'options' => $this->optionsLikely(),
                        'weights' => [
                            'fm' => ['score' => [5, 4, 3, 2, 0], 'weight' => 0.2],
                            'sd' => ['score' => [5, 4, 3, 2, 0], 'weight' => 0.2],
                            'ca' => ['score' => [5, 4, 3, 2, 0], 'weight' => 0.2],
                        ],
                        'required' => true,
                    ],

                    ['id' => 12.3,
                        'title' => 'Shopping',
                        'type' => 'InputRadio',
                        'options' => $this->optionsLikely(),
                        'weights' => [
                            'ws' => ['score' => [5, 4, 3, 2, 1], 'weight' => 0.2],
                        ],
                        'required' => true,
                    ],

                    ['id' => 12.4,
                        'title' => 'Work on School Assignments and projects',
                        'type' => 'InputRadio',
                        'options' => $this->optionsLikely(),
                        'weights' => [
                            'fm' => ['score' => [4, 3, 3, 1, 0], 'weight' => 0.1],
                            'sd' => ['score' => [5, 4, 3, 2, 1], 'weight' => 0.1],
                            'ca' => ['score' => [5, 4, 3, 2, 1], 'weight' => 0.2],
                        ],
                        'required' => true,
                    ],

                    ['id' => 12.5,
                        'title' => 'Watch documentaries on TV',
                        'type' => 'InputRadio',
                        'options' => $this->optionsLikely(),
                        'weights' => [
                            'fm' => ['score' => [5, 4, 3, 2, 1], 'weight' => 0.4],
                            'sd' => ['score' => [5, 4, 3, 2, 1], 'weight' => 0.2],
                            'ca' => ['score' => [5, 4, 3, 2, 1], 'weight' => 0.3],
                        ],
                        'required' => true,
                    ],

                    ['id' => 12.6,
                        'title' => 'Short Trip to nearby places',
                        'type' => 'InputRadio',
                        'options' => $this->optionsLikely(),
                        'weights' => [
                            'ws' => ['score' => [5, 4, 3, 2, 1], 'weight' => 0.1],
                            'fm' => ['score' => [5, 4, 3, 2, 1], 'weight' => 0.2],
                            'sd' => ['score' => [5, 4, 3, 2, 1], 'weight' => 0.2],
                            'ca' => ['score' => [5, 4, 3, 2, 1], 'weight' => 0.2],
                        ],
                        'required' => true,
                    ],
                ],
                'required' => true,
                'filter' => range(0, 12),
            ],
            
            ['id' => 13,
                'title' => 'How often do you go on a family vacation?',
                'type' => 'InputRadio',
                'options' => [
                    ['label' => 'More than once a year', 'selected' => false], 
                    ['label' => 'Once a year', 'selected' => false], 
                    ['label' => 'Once in 2 years', 'selected' => false], 
                    ['label' => 'Rarely', 'Never', 'selected' => false]
                ],
                'weights' => [
                    'ws' => ['score' => [5, 4, 3, 1, 0], 'weight' => 0.4],
                    'sd' => ['score' => [5, 4, 3, 1, 0], 'weight' => 0.3],
                    'ca' => ['score' => [5, 4, 3, 1, 0], 'weight' => 0.4],
                ],
                'required' => true,
                'filter' => range(0, 12),
            ],

            ['id' => 14,
                'title' => 'In the last 12 months, have you taken a Holiday with your family outside India?',
                'type' => 'InputRadio',
                'options' => $this->optionsYesNo(),
                'weights' => [
                    'ws' => ['score' => [5, 1], 'weight' => 0.5],
                    'ca' => ['score' => [5, 1], 'weight' => 0.4],
                ],
                'required' => true,
                'filter' => range(0, 12),
            ],

            ['id' => 15,
                'title' => 'Whose advice do you seek in important matters like your child\'s education?',
                'type' => 'InputRadio',
                'options' => [
                    ['label' => 'Seeking advice from people in relevant fields.', 'selected' => false],
                    ['label' => 'School Teachers, Coordinators or Career Counsellors.', 'selected' => false],
                    ['label' => 'Immediate Family Members and Relatives.', 'selected' => false],
                    ['label' => 'Friends and Peers', 'selected' => false],
                    ['label' => 'Nobody, I do my own research.', 'selected' => false]
                ],
                'weights' => [
                    'fm' => ['score' => [5, 4, 5, 2, 1], 'weight' => 0.4],
                ],
                'required' => true,
                'filter' => range(0, 12),
            ],

            ['id' => 16,
                'title' => 'How aware are you of the different curricula available in India?',
                'type' => 'Questions',
                'Questions' => [
                    ['id' => 16.1,
                        'title' => 'International Baccalaureate',
                        'type' => 'InputRadio',
                        'options' => $this->optionsAware(),
                        'weights' => [
                            'fm' => ['score' => [5, 3, 1, 0], 'weight' => 0.6],
                            'ca' => ['score' => [5, 3, 1, 0], 'weight' => 0.4],
                        ],
                        'required' => true,
                    ],

                    ['id' => 16.2,
                        'title' => 'Cambridge (IGCSE)',
                        'type' => 'InputRadio',
                        'options' => $this->optionsAware(),
                        'weights' => [
                            'fm' => ['score' => [5, 3, 1, 0], 'weight' => 0.6],
                            'ca' => ['score' => [5, 3, 1, 0], 'weight' => 0.4],
                        ],
                        'required' => true,
                    ],

                    ['id' => 16.3,
                        'title' => 'ICSE',
                        'type' => 'InputRadio',
                        'options' => $this->optionsAware(),
                        'weights' => [
                            'fm' => ['score' => [3, 3, 2, 0], 'weight' => 0.3],
                            'ca' => ['score' => [3, 3, 2, 0], 'weight' => 0.2],
                        ],
                        'required' => true,
                    ],

                    ['id' => 16.4,
                        'title' => 'CBSE',
                        'type' => 'InputRadio',
                        'options' => $this->optionsAware(),
                        'weights' => [
                            'fm' => ['score' => [3, 3, 2, 0], 'weight' => 0.1],
                            'ca' => ['score' => [3, 3, 2, 0], 'weight' => 0.1],
                        ],
                        'required' => true,
                    ],

                    ['id' => 16.5,
                        'title' => 'State Board',
                        'type' => 'InputRadio',
                        'options' => $this->optionsAware(),
                        'weights' => [
                            'fm' => ['score' => [3, 3, 2, 0], 'weight' => 0.05],
                            'ca' => ['score' => [3, 3, 2, 0], 'weight' => 0.05],
                        ],
                        'required' => true,
                    ],
                ],
                'required' => true,
                'filter' => range(0, 12),
            ],

            ['id' => 17,
                'title' => 'Why are you considering another curriculum for your Child?',
                'type' => 'InputRadio',
                'options' => [
                    ['label' => 'Seeking better Academic Growth', 'selected' => false],
                    ['label' => 'Seeking Better Exposure & Personality Development', 'selected' => false],
                    ['label' => 'For better College Admission chances', 'selected' => false],
                    ['label' => 'Upon the suggestion of Family & Friends', 'selected' => false],
                    ['label' => 'Just exploring options', 'selected' => false],
                    ['label' => 'Not considering a change', 'selected' => false],
                    ['label' => 'Others', 'selected' => false]
                ],
                'am3' => [
                    [
                        'board' => 'cbse',
                        'label' => 'Good academic results',
                        'match' => ['Seeking better Academic Growth', 'Upon the suggestion of Family & Friends'], 
                        'order' => 4,
                    ],
                    [
                        'board' => 'icse',
                        'label' => 'Good academic results',
                        'match' => ['Seeking better Academic Growth', 'Upon the suggestion of Family & Friends'], 
                        'order' => 5,
                    ],
                     [
                        'board' => 'cambridge',
                        'label' => 'All round development',
                        'match' => ['Seeking Better Exposure & Personality Development', 'For better College Admission chances'], 
                        'order' => 5,
                    ],
                    [
                        'board' => 'ib',
                        'label' => 'All round development',
                        'match' => ['Seeking Better Exposure & Personality Development', 'For better College Admission chances'], 
                        'order' => 5,
                    ],
                ],
                'am2' => [
                    [
                        'board' => 'cbse',
                        'label' => 'Good academic results',
                        'match' => ['Seeking better Academic Growth', 'Upon the suggestion of Family & Friends'], 
                        'order' => 4,
                    ],
                    [
                        'board' => 'icse',
                        'label' => 'Good academic results',
                        'match' => ['Seeking better Academic Growth', 'Upon the suggestion of Family & Friends'], 
                        'order' => 5,
                    ],
                    [
                        'board' => 'cambridge',
                        'label' => 'All round development',
                        'match' => ['Seeking Better Exposure & Personality Development', 'For better College Admission chances'], 
                        'order' => 5,
                    ],
                    [
                        'board' => 'ib',
                        'label' => 'All round development',
                        'match' => ['Seeking Better Exposure & Personality Development', 'For better College Admission chances'], 
                        'order' => 5,
                    ],
                ],
                'weights' => [
                    'fm' => ['score' => [1, 5, 3, 1, 2, 0, 0], 'weight' => 0.5],
                    'sd' => ['score' => [1, 5, 3, 1, 0, 0, 0], 'weight' => 0.2],
                    'ca' => ['score' => [1, 5, 3, 1, 1, 0, 0], 'weight' => 0.05],
                ],
                'required' => true,
                'filter' => range(1, 12),
            ],

            ['id' => 18,
                'title' => 'By when are you considering changing your Child\'s curriculum?',
                'type' => 'InputRadio',
                'options' => [
                    ['label' => 'Next Academic Year', 'selected' => false],
                    ['label' => 'In another 2-3 years', 'selected' => false],
                    ['label' => 'Not Sure', 'selected' => false],
                    ['label' => 'Not Applicable', 'selected' => false]
                ],
                'weights' => [
                    'fm' => ['score' => [5, 3, 1, 0], 'weight' => 0.3],
                    'ca' => ['score' => [5, 3, 1, 0], 'weight' => 0.2],
                ],
                'required' => true,
                'filter' => range(1, 12),
            ],

            ['id' => 19,
                'title' => 'Please rank in order of importance the following factors for your child\'s education (from 1-5, 1 being most important)',
                'type' => 'Questions',
                'Questions' => [

                    ['id' => 19.1,
                        'title' => 'Small Size classroom for more attention to each student',
                        'type' => 'InputRadio',
                        'options' => $this->optionsNumeric(1, 5),
                        'inline' => true,
                        'am3' => [
                            [
                                'board' => 'cambridge',
                                'label' => 'Small Size class rooms',
                                'match' => [1,2,3], 'order' => 3,
                            ],
                            [
                                'board' => 'ib',
                                'label' => 'Small Size class rooms',
                                'match' => [1,2,3], 'order' => 3,
                            ],
                        ],
                        'am2' => [
                            [
                                'board' => 'cambridge',
                                'label' => 'Small Size class rooms',
                                'match' => [1,2,3], 'order' => 3,
                            ],
                            [
                                'board' => 'ib',
                                'label' => 'Small Size class rooms',
                                'match' => [1,2,3], 'order' => 3,
                            ],
                        ],
                        'am1' => [
                            [
                                'board' => 'cambridge',
                                'label' => 'Small Size class rooms',
                                'match' => [1,2,3,4], 'order' => 1,
                            ],
                            [
                                'board' => 'ib',
                                'label' => 'Small Size class rooms',
                                'match' => [1,2,3,4], 'order' => 2,
                            ],
                        ],
                        'weights' => [
                            'ws' => ['score' => [5, 4, 3, 2, 1], 'weight' => 0.1],
                            'fm' => ['score' => [5, 4, 3, 2, 1], 'weight' => 0.3],
                            'sd' => ['score' => [5, 4, 3, 2, 1], 'weight' => 0.3],
                            'ca' => ['score' => [5, 4, 3, 2, 1], 'weight' => 0.15],
                        ],
                        'unique' => true,
                        'required' => true,
                    ],

                    ['id' => 19.2,
                        'title' => 'Teaching with Technology in Classroom',
                        'type' => 'InputRadio',
                        'inline' => true,
                        'options' => $this->optionsNumeric(1, 5),
                        'am3' => [
                            [
                                'board' => 'cambridge',
                                'label' => 'Infrastructure and facilities',
                                'match' => [1,2,3], 'order' => 2,
                            ],
                            [
                                'board' => 'ib',
                                'label' => 'Infrastructure and facilities',
                                'match' => [1,2,3], 'order' => 4,
                            ],
                        ],
                        'am2' => [
                            [
                                'board' => 'cambridge',
                                'label' => 'Infrastructure and facilities',
                                'match' => [1,2,3], 'order' => 4,
                            ],
                            [
                                'board' => 'ib',
                                'label' => 'Infrastructure and facilities',
                                'match' => [1,2,3], 'order' => 4,
                            ],
                        ],
                        'am1' => [
                            [
                                'board' => 'cambridge',
                                'label' => 'Infrastructure and facilities',
                                'match' => [1,2,3,4], 'order' => 3,
                            ],
                            [
                                'board' => 'ib',
                                'label' => 'Infrastructure and facilities',
                                'match' => [1,2,3,4], 'order' => 4,
                            ],
                        ],
                        'weights' => [
                            'fm' => ['score' => [5, 4, 3, 2, 1], 'weight' => 0.3],
                            'sd' => ['score' => [5, 4, 3, 2, 1], 'weight' => 0.3],
                            'ca' => ['score' => [5, 4, 3, 2, 1], 'weight' => 0.15],
                        ],
                        'unique' => true,
                        'required' => true,
                    ],

                    ['id' => 19.3,
                        'title' => 'Extra-Curricular Activities',
                        'type' => 'InputRadio',
                        'inline' => true,
                        'options' => $this->optionsNumeric(1, 5),
                        'am3' => [
                            [
                                'board' => 'icse',
                                'label' => 'Focus on extracurricular activities',
                                'match' => [1,2,3], 'order' => 3,
                            ],
                            [
                                'board' => 'cambridge',
                                'label' => 'Focus on extracurricular activities',
                                'match' => [1,2,3], 'order' => 2,
                            ],
                            [
                                'board' => 'ib',
                                'label' => 'Focus on extracurricular activities',
                                'match' => [1,2,3], 'order' => 2,
                            ],
                        ],
                        'am2' => [
                            [
                                'board' => 'cambridge',
                                'label' => 'Focus on extracurricular activities',
                                'match' => [1,2,3], 'order' => 2,
                            ],
                            [
                                'board' => 'ib',
                                'label' => 'Focus on extracurricular activities',
                                'match' => [1,2,3], 'order' => 2,
                            ],
                        ],
                        'am1' => [
                            [
                                'board' => 'cbse',
                                'label' => 'Focus on extracurricular activities',
                                'match' => [1,2,3], 'order' => 3,
                            ],
                            ['board' => 'icse',
                                'label' => 'Focus on extracurricular activities',
                                'match' => [1,2,3], 'order' => 3,
                            ],
                            ['board' => 'cambridge',
                                'label' => 'Focus on extracurricular activities',
                                'match' => [1,2,3,4], 'order' => 2,
                            ],
                            ['board' => 'ib',
                                'label' => 'Focus on extracurricular activities',
                                'match' => [1,2,3,4], 'order' => 1,
                            ],
                        ],
                        'weights' => [
                            'fm' => ['score' => [5, 4, 3, 2, 1], 'weight' => 0.4],
                            'sd' => ['score' => [5, 4, 3, 2, 1], 'weight' => 0.4],
                            'ca' => ['score' => [5, 4, 3, 2, 1], 'weight' => 0.1],
                        ],
                        'unique' => true,
                        'required' => true,
                    ],

                    ['id' => 19.4,
                        'title' => 'Flexibility of subject choices',
                        'type' => 'InputRadio',
                        'inline' => true,
                        'options' => $this->optionsNumeric(1, 5),
                        'am3' => [
                            ['board' => 'cambridge',
                                'label' => 'Varied Subject choices',
                                'match' => [1,2,3], 'order' => 6,
                            ],
                            ['board' => 'ib',
                                'label' => 'Varied Subject choices',
                                'match' => [1,2,3], 'order' => 6,
                            ],
                        ],
                        'am2' => [
                            ['board' => 'cambridge',
                                'label' => 'Varied Subject choices',
                                'match' => [1,2,3], 'order' => 6,
                            ],
                            ['board' => 'ib',
                                'label' => 'Varied Subject choices',
                                'match' => [1,2,3], 'order' => 6,
                            ],
                        ],
                        'am1' => [
                            ['board' => 'icse',
                                'label' => 'Varied Subject choices',
                                'match' => [1,2,3], 'order' => 5,
                            ],
                            ['board' => 'cambridge',
                                'label' => 'Varied Subject choices',
                                'match' => [1,2,3,4], 'order' => 99,
                            ],
                            ['board' => 'ib',
                                'label' => 'Varied Subject choices',
                                'match' => [1,2,3,4], 'order' => 6,
                            ],
                        ],
                        'weights' => [
                            'fm' => ['score' => [4, 3, 2, 1, 1], 'weight' => 0.3],
                            'sd' => ['score' => [3, 2, 2, 1, 1], 'weight' => 0.1],
                            'ca' => ['score' => [4, 3, 2, 1, 1], 'weight' => 0.3],
                        ],
                        'unique' => true,
                        'required' => true,
                    ],

                    ['id' => 19.5,
                        'title' => 'Test Prep Support for Engineering & Medical Entrance Exams',
                        'type' => 'InputRadio',
                        'inline' => true,
                        'options' => $this->optionsNumeric(1, 5),
                        'am3' => [
                            ['board' => 'cambridge',
                                'label' => 'Career Aspirations in India',
                                'match' => [1,2,3], 'order' => 0,
                            ],
                            ['board' => 'ib',
                                'label' => 'Career Aspirations in India',
                                'match' => [1,2,3], 'order' => 0,
                            ],
                        ],
                        'am2' => [
                            ['board' => 'cbse',
                                'label' => 'Pursuing professional courses in India',
                                'match' => [1,2,3], 'order' => 0,
                            ],
                        ],
                        'am3' => [
                            ['board' => 'cbse',
                                'label' => 'Pursuing professional courses in India',
                                'match' => [1,2,3], 'order' => 0,
                            ],
                            ['board' => 'icse',
                                'label' => 'Career Aspirations in India',
                                'match' => [1,2,3], 'order' => 0,
                            ],
                        ],
                        'weights' => [
                            'fm' => ['score' => [5, 5, 3, 1, 1], 'weight' => 0.2],
                            'ca' => ['score' => [5, 5, 3, 1, 1], 'weight' => 0.1],
                        ],
                        'unique' => true,
                        'required' => true,
                    ],

                ],
                'required' => true,
                'filter' => range(0, 12),
            ],

            ['id' => 20,
                'title' => 'Is your child eligible for any tuition fee concessions?',
                'type' => 'InputRadio',
                'options' => $this->optionsYesNo(),
                'weights' => [
                    'ws' => ['score' => [5, 1], 'weight' => 0.6, 'max' => 3],
                ],
                'required' => true,
                'filter' => range(0, 12),
            ],

            ['id' => 21,
                'title' => 'Is one of your aspirations for your child to pursue a professional course like engineering, medicine, law etc. at the undergraduate level?',
                'type' => 'InputRadio',
                'options' => $this->optionsYesNoMaybe(),
                'am2' => [
                    ['board' => 'cbse',
                        'label' => 'Pursuing professional courses in India',
                        'match' => ['Yes'], 'order' => 0,
                    ],
                ],
                
                'weights' => [
                    'fm' => ['score' => [1, 3, 1], 'weight' => 1],
                ],
                'required' => true,
                'filter' => range(0, 7),
            ],

            ['id' => 22,
                'title' => 'Which country would you like your child to study in?',
                'type' => 'InputRadio',
                'options' => [
                    ['label' => 'India', 'selected' => false], 
                    ['label' => 'USA', 'selected' => false], 
                    ['label' => 'UK', 'selected' => false], 
                    ['label' => 'Australia', 'selected' => false], 
                    ['label' => 'Canada', 'selected' => false], 
                    ['label' => 'Other', 'selected' => false]
                ],
                'am2' => [
                    ['board' => 'cbse',
                        'label' => 'Pursuing professional courses in India',
                        'match' => ['India'], 'order' => 0,
                    ],
                    ['board' => 'cambridge',
                        'label' => 'International career aspirations',
                        'match' => ['USA', 'UK', 'Australia', 'Canada', 'Other'], 'order' => 0,
                    ],
                    ['board' => 'ib',
                        'label' => 'International career aspirations',
                        'match' => ['USA', 'UK', 'Australia', 'Canada', 'Other'], 'order' => 0,
                    ],
                    
                ],
                'am1' => [
                    ['board' => 'cbse',
                        'label' => 'Career Aspirations in India',
                        'match' => ['India'], 'order' => 0,
                    ],
                    ['board' => 'icse',
                        'label' => 'Career Aspirations in India',
                        'match' => ['India'], 'order' => 0,
                    ],
                    ['board' => 'cambridge',
                        'label' => 'International career aspirations',
                        'match' => ['USA', 'UK', 'Australia', 'Canada', 'Other'], 'order' => 0,
                    ],
                    ['board' => 'ib',
                        'label' => 'International career aspirations',
                        'match' => ['USA', 'UK', 'Australia', 'Canada', 'Other'], 'order' => 0,
                    ],
                ],
                'weights' => [
                    'ws' => ['score' => [1, 5, 5, 5, 5, 0], 'weight' => 0.5],
                    'fm' => ['score' => [1, 5, 5, 5, 5, 0], 'weight' => 1],
                    'sd' => ['score' => [1, 5, 5, 5, 5, 0], 'weight' => 1],
                ],
                'required' => true,
                'filter' => range(0, 7),
            ],

            ['id' => 23,
                'title' => 'Are you considering sending your child to a residential school?',
                'type' => 'InputRadio',
                'options' => $this->optionsYesNoMaybe(),
                'weights' => [
                    'ws' => ['score' => [5, 0, 3], 'weight' => 0.1],
                ],
                'required' => true,
                'filter' => range(1, 12),
            ],

            ['id' => 24,
                'title' => 'What is the primary reason for making this decision?',
                'type' => 'InputRadio',
                'depends' => ['id' => 23, 'values' => ['Yes', 'May be']],
                'options' => [
                    ['label' => 'Family situations', 'selected' => false],
                    ['label' => 'For better exposure', 'selected' => false],
                    ['label' => 'To learn to be more resilient', 'selected' => false],
                    ['label' => 'Constant Supervision', 'selected' => false],
                    ['label' => 'Establishing a regular routine', 'selected' => false]
                ],
                'weights' => [
                    'fm' => ['score' => [5, 4, 3, 2, 1], 'weight' => 0.4],
                    'sd' => ['score' => [2, 3, 3, 1, 1], 'weight' => 0.2],
                    'ca' => ['score' => [5, 3, 3, 1, 1], 'weight' => 0.1],
                ],
                'required' => true,
                'filter' => range(1, 12),
            ],

            ['id' => 25,
                'title' => 'According to you, what kind of extra curricular activities is your child interested in?',
                'type' => 'InputCheckbox',
                'options' => $this->optionsExtraCurricular(),
                'required' => true,
                'filter' => range(1, 12),
            ],

            ['id' => 26,
                'title' => 'Are you supporting your child to pursue the above mentioned extra curricular activity  at an advanced or professional level?',
                'type' => 'InputRadio',
                'options' => [
                    ['label' => 'Great deal', 'selected' => false], 
                    ['label' => 'A lot', 'selected' => false], 
                    ['label' => 'Moderately', 'selected' => false], 
                    ['label' => 'A little', 'selected' => false], 
                    ['label' => 'Not at all', 'selected' => false]
                ],
                'am3' => [
                    ['board' => 'cambridge',
                        'label' => 'Focus on extracurricular activities',
                        'match' => ['Great deal', 'A lot'], 'order' => 2,
                    ],
                    ['board' => 'ib',
                        'label' => 'Focus on extracurricular activities',
                        'match' => ['Great deal', 'A lot'], 'order' => 3,
                    ],
                ],
                'am2' => [
                    ['board' => 'icse',
                        'label' => 'Focus on extracurricular activities',
                        'match' => ['Great deal', 'A lot'], 'order' => 1,
                    ],
                    ['board' => 'cambridge',
                        'label' => 'Focus on extracurricular activities',
                        'match' => ['Great deal', 'A lot'], 'order' => 2,
                    ],
                    ['board' => 'ib',
                        'label' => 'Focus on extracurricular activities',
                        'match' => ['Great deal', 'A lot'], 'order' => 2,
                    ],
                ],
                'weights' => [
                    'fm' => ['score' => [5, 3, 1, 1, 0], 'weight' => 0.2],
                    'sd' => ['score' => [5, 3, 1, 1, 0], 'weight' => 0.2],
                    'ca' => ['score' => [5, 3, 1, 1, 0], 'weight' => 0.2],
                ],
                'required' => true,
                'filter' => range(1, 12),
            ],

            ['id' => 27,
                'title' => 'How does your child best learn and understand concepts?',
                'type' => 'InputRadio',
                'options' => [
                    ['label' => 'Teacher Driven', 'selected' => false],
                    ['label' => 'Independent learner', 'selected' => false],
                    ['label' => 'Structured learning through prescribed text books', 'selected' => false],
                    ['label' => 'Inquiry and research based learning.', 'selected' => false]
                ],
                'am3' => [
                    [
                        'board' => 'cbse',
                        'label' => 'Structured Learning',
                        'match' => ['Teacher Driven', 'Structured learning through prescribed text books'], 'order' => 1,
                    ],
                    [
                        'board' => 'icse',
                        'label' => 'Structured Learning',
                        'match' => ['Teacher Driven', 'Structured learning through prescribed text books'], 'order' => 1,
                    ],
                    [
                        'board' => 'icse',
                        'label' => 'Experiential learning',
                        'match' => ['Inquiry and research based learning.'], 'order' => 1,
                    ],
                    [
                        'board' => 'cambridge',
                        'label' => 'Active learning',
                        'match' => ['Independent learner'], 'order' => 1,
                    ],
                    [
                        'board' => 'ib',
                        'label' => 'Active learning',
                        'match' => ['Independent learner'], 'order' => 1,
                    ],
                    [
                        'board' => 'cambridge',
                        'label' => 'Experiential learning',
                        'match' => ['Inquiry and research based learning.'], 'order' => 1,
                    ],
                    [
                        'board' => 'ib',
                        'label' => 'Experiential learning',
                        'match' => ['Inquiry and research based learning.'], 'order' => 1,
                    ],
                ],
                'am2' => [
                    ['board' => 'cbse',
                        'label' => 'Structured Learning',
                        'match' => ['Teacher Driven', 'Structured learning through prescribed text books'], 'order' => 1,
                    ],
                    ['board' => 'icse',
                        'label' => 'Structured Learning',
                        'match' => ['Teacher Driven', 'Structured learning through prescribed text books'], 'order' => 3,
                    ],
                    ['board' => 'cambridge',
                        'label' => 'Active learning',
                        'match' => ['Independent learner', 'Inquiry and research based learning.'], 'order' => 1,
                    ],
                ],
                'weights' => [
                    'fm' => ['score' => [1, 5, 1, 5], 'weight' => 0.5],
                    'sd' => ['score' => [1, 5, 1, 5], 'weight' => 0.5],
                    'ca' => ['score' => [1, 5, 1, 5], 'weight' => 0.5],
                ],
                'required' => true,
                'filter' => range(1, 12),
            ],

            ['id' => 28,
                'title' => 'How involved are you with your child\'s school-work & assignments?',
                'type' => 'InputRadio',
                'options' => [
                    ['label' => 'Very involved', 'selected' => false],
                    ['label' => 'Somewhat involved', 'selected' => false],
                    ['label' => 'Rarely involved', 'selected' => false],
                    ['label' => 'Never involved', 'selected' => false]
                ],
                'weights' => [
                    'sd' => ['score' => [2, 1, 1, 0], 'weight' => 0.1],
                    'ca' => ['score' => [2, 1, 1, 0], 'weight' => 0.1],
                ],
                'required' => true,
                'filter' => range(1, 12),
            ],

            ['id' => 29,
                'title' => 'What are your Career Aspirations for your child?',
                'type' => 'InputRadio',
                'options' => $this->optionsCareers(),
                'required' => true,
                'filter' => range(8, 12),
            ],

            ['id' => 30,
                'title' => 'What factors helped you to discover your career aspirations for your child?',
                'type' => 'InputCheckbox',
                'options' => [
                    ['label' => 'Based on my own research and observations', 'selected' => false],
                    ['label' => 'Based on my own interests', 'selected' => false],
                    ['label' => 'Based on psychometric assessments taken by the child', 'selected' => false],
                    ['label' => 'Based on perception of teachers and family members', 'selected' => false]
                ],
                'weights' => [
                    'fm' => ['score' => [4, 0, 5, 2], 'weight' => 0.4],
                    'ca' => ['score' => [4, 0, 5, 2], 'weight' => 0.4],
                ],
                'required' => true,
                'filter' => range(8, 12),
            ],

            ['id' => 31,
                'type' => 'Questions',
                'Questions' => [
                    ['id' => 31.1,
                        'title' => 'Where do you aspire your child to pursue his/her undergrad education?',
                        'type' => 'InputRadio',
                        'options' => [
                            ['label' => 'India', 'selected' => false],
                            ['label' => 'USA', 'selected' => false],
                            ['label' => 'UK', 'selected' => false],
                            ['label' => 'Australia', 'selected' => false],
                            ['label' => 'Canada', 'selected' => false],
                            ['label' => 'Other', 'selected' => false]
                        ],
                        'am3' => [
                            ['board' => 'cbse',
                                'label' => 'Pursuing professional courses in India',
                                'match' => ['India'], 'order' => 0,
                            ],
                            ['board' => 'icse',
                                'label' => 'Career Aspirations in India',
                                'match' => ['India'], 'order' => 0,
                            ],
                            ['board' => 'cambridge',
                                'label' => 'International career aspirations',
                                'match' => ['India'], 'order' => 0, 'inverse' => true,
                            ],
                            ['board' => 'ib',
                                'label' => 'International career aspirations',
                                'match' => ['India'], 'order' => 0, 'inverse' => true,
                            ],
                        ],
                        'required' => true,
                    ],
                    
                    ['id' => 31.2,
                        'title' => 'What was the major reason for choosing the above mentioned country?',
                        'type' => 'InputCheckbox',
                        'options' => [
                            ['label' => 'Better teaching methodology.', 'selected' => false],
                            ['label' => 'Tuition Fees', 'selected' => false],
                            ['label' => 'Better career opportunities', 'selected' => false],
                            ['label' => 'Proximity', 'selected' => false],
                            ['label' => 'Better International/Cultural Exposure', 'selected' => false],
                            ['label' => 'My own interests or aspirations', 'selected' => false],
                            ['label' => 'To learn life skills', 'selected' => false]
                        ],
                        'weights' => [
                            'ws' => ['score' => [0, 2, 0, 0, 0, 0, 0], 'weight' => 0.5],
                            'fm' => ['score' => [5, 2, 3, 2, 5, 1, 5], 'weight' => 0.8],
                            'sd' => ['score' => [5, 0, 3, 2, 5, 1, 5], 'weight' => 0.5],
                            'ca' => ['score' => [5, 0, 3, 2, 5, 1, 5], 'weight' => 0.5],
                        ],
                        'required' => true,
                    ],
                ],
                'required' => true,
                'filter' => range(8, 12),
            ],

            ['id' => 32,
                'title' => 'What are your child\'s career aspirations?',
                'type' => 'InputRadio',
                'options' => $this->optionsCareers(),
                'am3' => [
                    ['board' => 'cbse',
                        'label' => 'Pursuing professional courses in India',
                        'match' => ['Engineering & Technology', 'Maths and Statistics'], 'order' => 0,
                    ],
                    ['board' => 'icse',
                        'label' => 'Career Aspirations in India',
                        'match' => ['Arts', 'Business, Commerce and Economics'], 'order' => 0,
                    ],
                    ['board' => 'cambridge',
                        'label' => 'International career aspirations',
                        'match' => ['*'], 'order' => 0, 'inverse' => true,
                    ],
                    ['board' => 'ib',
                        'label' => 'International career aspirations',
                        'match' => ['*'], 'order' => 0, 'inverse' => true,
                    ],
                ],
                'required' => true,
                'filter' => range(8, 12),
            ],

            ['id' => 33,
                'title' => 'How have you helped your child to evaluate the choices?',
                'type' => 'InputCheckbox',
                'options' => [
                    ['label' => 'Arranged for a Summer Program/Internships', 'selected' => false],
                    ['label' => 'Helped the child to research', 'selected' => false],
                    ['label' => 'Encouraged the child to take psychometric assessments.', 'selected' => false],
                    ['label' => 'Arranged for discussions with professionals/alumni', 'selected' => false],
                    ['label' => 'Primarily due to influence of close relatives', 'selected' => false],
                    ['label' => 'I choose not to be involved in his/her choices.', 'selected' => false],
                ],
                'weights' => [
                    'ws' => ['score' => [5, 0, 5, 0, 0, 0], 'weight' => 0.5],
                    'fm' => ['score' => [5, 4, 5, 4, 0, 0], 'weight' => 0.6],
                    'sd' => ['score' => [5, 5, 3, 3, 0, 0], 'weight' => 0.6],
                    'ca' => ['score' => [5, 5, 4, 4, 0, 0], 'weight' => 0.7],
                ],
                'required' => true,
                'filter' => range(8, 12),
            ],

            ['id' => 34,
                'title' => 'How does your child cope with exams?',
                'type' => 'InputRadio',
                'options' => [
                    ['label' => 'Very Stressed', 'selected' => false],
                    ['label' => 'Somewhat Stressed', 'selected' => false],
                    ['label' => 'Not Stressed at all', 'selected' => false],
                    ['label' => 'Not Sure', 'selected' => false],
                    ['label' => 'Not Applicable', 'selected' => false],
                ],
                'weights' => [
                    'sd' => ['score' => [1, 2, 5, 1, 0], 'weight' => 0.05],
                    'ca' => ['score' => [1, 2, 5, 1, 0], 'weight' => 0.05],
                ],
                'required' => true,
                'filter' => range(8, 12),
            ],

            ['id' => 35,
                'title' => 'Has your child taken a Psychometric Assessment to identify his Aptitude, Interest & Personality?',
                'type' => 'InputRadio',
                'options' => $this->optionsYesNo(),
                'weights' => [
                    'fm' => ['score' => [5, 1], 'weight' => 0.1],
                    'sd' => ['score' => [4, 1], 'weight' => 0.1],
                ],
                'required' => true,
                'filter' => range(8, 12),
            ],

            ['id' => 36,
                'title' => 'Do you seek your child\'s opinion before making decisions related to him/her?',
                'type' => 'InputRadio',
                'options' => [
                    ['label' => 'Always', 'selected' => false],
                    ['label' => 'Sometimes', 'selected' => false],
                    ['label' => 'Rarely', 'selected' => false],
                    ['label' => 'Never', 'selected' => false],
                    ['label' => 'NA', 'selected' => false]
                ],
                'weights' => [
                    'sd' => ['score' => [5, 4, 2, 0, 0], 'weight' => 0.2],
                    'ca' => ['score' => [5, 4, 2, 0, 0], 'weight' => 0.4],
                ],
                'required' => true,
                'filter' => range(8, 12),
            ],

            ['id' => 37,
                'title' => 'How do you encourage your child to socialize?',
                'type' => 'InputCheckbox',
                'options' => [
                    ['label' => 'Sports Activities', 'selected' => false],
                    ['label' => 'Extra-curricular Activities', 'selected' => false],
                    ['label' => 'Trips organized by School/Organisations', 'selected' => false],
                    ['label' => 'Family get togethers', 'selected' => false],
                    ['label' => 'Trust my child to have his/her own social connections', 'selected' => false]
                ],
                'weights' => [
                    'ws' => ['score' => [3, 4, 5, 0, 0], 'weight' => 0.1],
                    'fm' => ['score' => [5, 5, 5, 5, 0], 'weight' => 0.1],
                    'sd' => ['score' => [5, 5, 5, 5, 0], 'weight' => 0.2],
                    'ca' => ['score' => [5, 5, 5, 5, 0], 'weight' => 0.2],
                ],
                'required' => true,
                'filter' => range(8, 12),
            ],

            ['id' => 38,
                'title' => 'Is it a priority that your child is involved in extra - curricular activities?',
                'type' => 'InputRadio',
                'options' => $this->optionsYesNoNotSure(),
                'weights' => [
                    'fm' => ['score' => [5, 2, 3], 'weight' => 0.3],
                    'sd' => ['score' => [5, 2, 3], 'weight' => 0.3],
                    'ca' => ['score' => [5, 2, 1], 'weight' => 0.4],
                ],
                'required' => true,
                'filter' => range(8, 12),
            ],

            ['id' => 39,
                'title' => 'Please rank the following factors in order of importance while choosing a school - 1 being the most important to you as a parent.',
                'type' => 'Questions',
                'Questions' => [
                    ['id' => 39.1,
                        'title' => 'School Fee',
                        'type' => 'InputRadio',
                        'inline' => true,
                        'options' => $this->optionsNumeric(1, 6),
                        'am3' => [
                            ['board' => 'cbse',
                                'label' => 'School choice within budget',
                                'match' => [1,2,3], 'order' => 2,
                            ],
                            ['board' => 'icse',
                                'label' => 'School choice within budget',
                                'match' => [1,2,3], 'order' => 2,
                            ],
                        ],
                        'am2' => [
                            ['board' => 'cbse',
                                'label' => 'School choice within budget',
                                'match' => [1,2,3], 'order' => 2,
                            ],
                            ['board' => 'icse',
                                'label' => 'School choice within budget',
                                'match' => [1,2,3], 'order' => 0,
                            ],
                        ],
                        'am1' => [
                            ['board' => 'cbse',
                                'label' => 'School choice within budget',
                                'match' => [1,2,3], 'order' => 1,
                            ],
                            ['board' => 'icse',
                                'label' => 'School choice within budget',
                                'match' => [1,2,3], 'order' => 1,
                            ],
                        ],
                        'weights' => [
                            'ws' => ['score' => [1, 1, 2, 3, 4, 5], 'weight' => 0.5],
                        ],
                        'unique' => true,
                        'required' => true,
                    ],

                    ['id' => 39.2,
                        'title' => 'Distance from home',
                        'type' => 'InputRadio',
                        'inline' => true,
                        'options' => $this->optionsNumeric(1, 6),
                        'am3' => [
                            ['board' => 'cbse',
                                'label' => 'Proximity of good schools',
                                'match' => [1,2,3], 'order' => 3,
                            ],
                            ['board' => 'icse',
                                'label' => 'Proximity of good schools',
                                'match' => [1,2,3], 'order' => 4,
                            ],
                        ],
                        'am2' => [
                            ['board' => 'cbse',
                                'label' => 'Proximity of good schools',
                                'match' => [1,2,3], 'order' => 3,
                            ],
                            ['board' => 'icse',
                                'label' => 'Proximity of good schools',
                                'match' => [1,2,3], 'order' => 2,
                            ],
                        ],
                        'am1' => [
                            ['board' => 'cbse',
                                'label' => 'Proximity of good schools',
                                'match' => [1,2,3], 'order' => 4,
                            ],
                            ['board' => 'icse',
                                'label' => 'Proximity of good schools',
                                'match' => [1,2,3], 'order' => 4,
                            ],
                        ],
                        'weights' => [
                            'fm' => ['score' => [2, 2, 3, 4, 4, 5], 'weight' => 0.1],
                            'sd' => ['score' => [2, 2, 3, 4, 4, 5], 'weight' => 0.05],
                            'ca' => ['score' => [2, 2, 3, 4, 4, 5], 'weight' => 0.1],
                        ],
                        'unique' => true,
                        'required' => true,
                    ],

                    ['id' => 39.3,
                        'title' => 'Infrastructure and facilities - Smart class rooms,library,Computer lab,canteen',
                        'type' => 'InputRadio',
                        'inline' => true,
                        'options' => $this->optionsNumeric(1, 6),
                        'am3' => [
                            ['board' => 'cambridge',
                                'label' => 'Infrastructure and facilities',
                                'match' => [1,2,3], 'order' => 4,
                            ],
                            ['board' => 'ib',
                                'label' => 'Infrastructure and facilities',
                                'match' => [1,2,3], 'order' => 4,
                            ],
                        ],
                        'am2' => [
                            ['board' => 'cambridge',
                                'label' => 'Infrastructure and facilities',
                                'match' => [1,2,3], 'order' => 4,
                            ],
                            ['board' => 'ib',
                                'label' => 'Infrastructure and facilities',
                                'match' => [1,2,3], 'order' => 4,
                            ],
                        ],
                        'am1' => [
                            ['board' => 'cbse',
                                'label' => 'Infrastructure and facilities',
                                'match' => [4, 5, 6], 'order' => 2,
                            ],
                            ['board' => 'icse',
                                'label' => 'Infrastructure and facilities',
                                'match' => [4, 5, 6], 'order' => 2,
                            ],
                            ['board' => 'cambridge',
                                'label' => 'Infrastructure and facilities',
                                'match' => [1,2,3], 'order' => 3,
                                'filter' => [0],
                            ],
                            ['board' => 'ib',
                                'label' => 'Infrastructure and facilities',
                                'match' => [1,2,3], 'order' => 3,
                                'filter' => [0],
                            ],
                        ],
                        'weights' => [
                            'fm' => ['score' => [5, 4, 3, 2, 1, 1], 'weight' => 0.2],
                            'sd' => ['score' => [3, 3, 3, 2, 1, 1], 'weight' => 0.1],
                            'ca' => ['score' => [5, 4, 3, 2, 1, 1], 'weight' => 0.2],
                        ],
                        'unique' => true,
                        'required' => true,
                    ],

                    ['id' => 39.4,
                        'title' => 'Extra - Curricular Activities (Sports,Arts,Music,Yoga, Clubs)',
                        'type' => 'InputRadio',
                        'inline' => true,
                        'options' => $this->optionsNumeric(1, 6),
                        'am3' => [
                            ['board' => 'cambridge',
                                'label' => 'Focus on extracurricular activities',
                                'match' => [1, 2, 3], 'order' => 2,
                            ],
                            ['board' => 'ib',
                                'label' => 'Focus on extracurricular activities',
                                'match' => [1, 2, 3], 'order' => 2,
                            ],
                        ],
                        'am2' => [
                            ['board' => 'icse',
                                'label' => 'Focus on extracurricular activities',
                                'match' => [1, 2, 3], 'order' => 1,
                            ],
                            ['board' => 'cambridge',
                                'label' => 'Focus on extracurricular activities',
                                'match' => [1, 2, 3], 'order' => 2,
                            ],
                            ['board' => 'ib',
                                'label' => 'Focus on extracurricular activities',
                                'match' => [1, 2, 3], 'order' => 2,
                            ],
                        ],
                        'am1' => [
                            ['board' => 'cbse',
                                'label' => 'Focus on extracurricular activities',
                                'match' => [1, 2, 3], 'order' => 3,
                            ],
                            ['board' => 'icse',
                                'label' => 'Focus on extracurricular activities',
                                'match' => [1, 2, 3], 'order' => 3,
                            ],
                            ['board' => 'cambridge',
                                'label' => 'Focus on extracurricular activities',
                                'match' => [1, 2, 3], 'order' => 2,
                            ],
                            ['board' => 'ib',
                                'label' => 'Focus on extracurricular activities',
                                'match' => [1, 2, 3], 'order' => 1,
                            ],
                        ],
                        'weights' => [
                            'ws' => ['score' => [3, 3, 2, 2, 1, 1], 'weight' => 0.1],
                            'fm' => ['score' => [5, 4, 3, 2, 1, 1], 'weight' => 0.2],
                            'sd' => ['score' => [5, 4, 3, 2, 1, 1], 'weight' => 0.2],
                            'ca' => ['score' => [5, 4, 3, 2, 1, 1], 'weight' => 0.2],
                        ],
                        'unique' => true,
                        'required' => true,
                    ],

                    ['id' => 39.5,
                        'title' => 'Quality of Teachers',
                        'type' => 'InputRadio',
                        'inline' => true,
                        'options' => $this->optionsNumeric(1, 6),
                        'am3' => [
                            ['board' => 'cambridge',
                                'label' => 'Highly skilled teachers',
                                'match' => [1, 2, 3], 'order' => 7,
                            ],
                            ['board' => 'ib',
                                'label' => 'Highly skilled teachers',
                                'match' => [1, 2, 3], 'order' => 7,
                            ],
                        ],
                        'am2' => [
                            ['board' => 'cambridge',
                                'label' => 'Highly skilled teachers',
                                'match' => [1, 2, 3], 'order' => 7,
                            ],
                            ['board' => 'ib',
                                'label' => 'Highly skilled teachers',
                                'match' => [1, 2, 3], 'order' => 7,
                            ],
                        ],
                        'am1' => [
                            ['board' => 'cambridge',
                                'label' => 'Highly skilled teachers',
                                'match' => [1, 2, 3], 'order' => 5,
                            ],
                            ['board' => 'ib',
                                'label' => 'Highly skilled teachers',
                                'match' => [1, 2, 3], 'order' => 5,
                            ],
                        ],
                        'weights' => [
                            'ws' => ['score' => [4, 4, 3, 2, 1, 1], 'weight' => 0.05],
                            'fm' => ['score' => [5, 4, 3, 2, 1, 1], 'weight' => 0.2],
                            'sd' => ['score' => [3, 3, 3, 2, 2, 1], 'weight' => 0.1],
                            'ca' => ['score' => [5, 4, 3, 2, 1, 1], 'weight' => 0.3],
                        ],
                        'unique' => true,
                        'required' => true,
                    ],

                    ['id' => 39.6,
                        'title' => 'College Placements and Alumni Success',
                        'type' => 'InputRadio',
                        'inline' => true,
                        'options' => $this->optionsNumeric(1, 6),
                        'weights' => [
                            'fm' => ['score' => [5, 4, 3, 2, 1, 1], 'weight' => 0.05],
                            'ca' => ['score' => [5, 4, 3, 2, 1, 1], 'weight' => 0.05],
                        ],
                        'unique' => true,
                        'required' => true,
                    ],

                ],
                'required' => true,
                'filter' => range(0, 12),
            ],

            ['id' => 40,
                'title' => 'What is your Highest Level of Education?',
                'type' => 'InputRadio',
                'options' => [
                    ['label' => 'Not Completed High School', 'selected' => false],
                    ['label' => 'High School (Grade 12)', 'selected' => false],
                    ['label' => 'Graduate', 'selected' => false],
                    ['label' => 'Post Graduate', 'selected' => false],
                    ['label' => 'Doctorate', 'selected' => false],
                    ['label' => 'Doctorate & Above', 'selected' => false],
                ],
                'weights' => [
                    'fm' => ['score' => [1, 2, 3, 5, 5, 5], 'weight' => 0.4],
                ],
                'required' => true,
                'filter' => range(0, 12),
            ],

            ['id' => 41,
                'depends' => ['id' => 40, 'values' => ['Graduate', 'Post Graduate', 'Doctorate', 'Doctorate & Above']],
                'title' => 'Which College/University did you graduate from?',
                'type' => 'InputText',
                'placeholder' => 'Graduate college name',
                'value' => null,
                'required' => true,
                'filter' => range(0, 12),
            ],

            ['id' => 42,
                'depends' => ['id' => 40, 'values' => ['Post Graduate', 'Doctorate', 'Doctorate & Above']],
                'title' => 'Which College/University did you complete your Post-Graduation from?',
                'type' => 'InputText',
                'placeholder' => 'University name',
                'value' => null,
                'required' => true,
                'filter' => range(0, 12),
            ],

            ['id' => 43,
                'title' => 'What industry do you work in?',
                'type' => 'InputRadio',
                'options' => $this->optionsIndustries(),
                'required' => true,
                'filter' => range(0, 12),
            ],
            
            ['id' => 44,
                'title' => 'Which company do you currently work?',
                'type' => 'InputText',
                'placeholder' => 'Company name',
                'value' => null,
                'required' => true,
                'filter' => range(0, 12),
            ],
            
            ['id' => 45,
                'title' => 'Type of Employment',
                'type' => 'InputRadio',
                'options' => [
                    ['label' => 'Salaried', 'selected' => false],
                    ['label' => 'Self-Employed', 'selected' => false],
                    ['label' => 'Home maker', 'selected' => false],
                    ['label' => 'Freelancer', 'selected' => false],
                    ['label' => 'Unemployed', 'selected' => false],
                ],
                'weights' => [
                    'ws' => ['score' => [3, 3, 1, 3, 1], 'weight' => 0.1],
                ],
                'required' => true,
                'filter' => range(0, 12),
            ],
            
            ['id' => 46,
                'title' => 'What is your designation?',
                'depends' => ['id' => 45, 'values' => ['Salaried', 'Self-Employed', 'Freelancer']],
                'type' => 'InputText',
                'placeholder' => 'Designation',
                'value' => null,
                'required' => true,
                'filter' => range(0, 12),
            ],

            ['id' => 47,
                'title' => 'What type of an employment does your spouse have?',
                'type' => 'InputRadio',
                'options' => [
                    ['label' => 'Salaried', 'selected' => false],
                    ['label' => 'Self-Employed', 'selected' => false],
                    ['label' => 'Home maker', 'selected' => false],
                    ['label' => 'Freelancer', 'selected' => false],
                    ['label' => 'Unemployed', 'selected' => false],
                ],
                'weights' => [
                    'ws' => ['score' => [5, 4, 0, 3, 0], 'weight' => 0.2],
                ],
                'required' => true,
                'filter' => range(0, 12),
            ],

            ['id' => 48,
                'title' => 'What industry does your spouse work in?',
                'depends' => ['id' => 47, 'values' => ['Salaried', 'Self-Employed', 'Freelancer']],
                'type' => 'InputRadio',
                'placeholder' => 'Industry name',
                'value' => null,
                'options' => $this->optionsIndustries(),
                'required' => true,
                'filter' => range(0, 12),
            ],

            ['id' => 49,
                'title' => 'Have you ever traveled outside India?',
                'depends' => ['id' => 14, 'values' => ['No']],
                'type' => 'InputRadio',
                'options' => $this->optionsYesNo(),
                'required' => true,
                'filter' => range(0, 12),
            ],

            ['id' => 50,
                'title' => 'Due to the nature of work or for any other reason do you relocate frequently(at least once in 5 years)?',
                'type' => 'InputRadio',
                'options' => $this->optionsYesNo(),
                'required' => true,
                'filter' => range(0, 12),
            ],

            ['id' => 51,
                'title' => 'If Yes, Where?',
                'depends' => ['id' => 50, 'values' => ['Yes']],
                'type' => 'InputRadio',
                'options' => [
                    ['label' => 'India', 'selected' => false],
                    ['label' => 'International', 'selected' => false],
                    ['label' => 'Both', 'selected' => false]
                ],
                'am3' => [
                    ['board' => 'cbse',
                        'label' => 'Constant relocation within India',
                        'match' => ['India'], 'order' => 9,
                    ],
                    ['board' => 'icse',
                        'label' => 'Constant relocation within India',
                        'match' => ['India'], 'order' => 5,
                    ],
                    ['board' => 'cambridge',
                        'label' => 'Possibility of International Relocation',
                        'match' => ['India'], 'order' => 8,
                    ],
                    ['board' => 'ib',
                        'label' => 'Possibility of International Relocation',
                        'match' => ['India'], 'order' => 8,
                    ],
                ],
                'am2' => [
                    ['board' => 'cambridge',
                        'label' => 'Possibility of International Relocation',
                        'match' => ['International', 'Both'], 'order' => 8,
                    ],
                    ['board' => 'ib',
                        'label' => 'Possibility of International Relocation',
                        'match' => ['International', 'Both'], 'order' => 8,
                    ],
                    ['board' => 'cbse',
                        'label' => 'Constant relocation within India',
                        'match' => ['India'], 'order' => 0,
                    ],
                    ['board' => 'icse',
                        'label' => 'Constant relocation within India',
                        'match' => ['India'], 'order' => 4,
                    ],
                ],
                'am1' => [
                    ['board' => 'cbse',
                        'label' => 'Constant relocation within India',
                        'match' => ['India'], 'order' => 7,
                    ],
                    ['board' => 'icse',
                        'label' => 'Constant relocation within India',
                        'match' => ['India'], 'order' => 7,
                    ],
                    ['board' => 'cambridge',
                        'label' => 'Possibility of International Relocation',
                        'match' => ['International', 'Both'], 'order' => 5,
                    ],
                    ['board' => 'ib',
                        'label' => 'Possibility of International Relocation',
                        'match' => ['International', 'Both'], 'order' => 7,
                    ],
                ],
                
                'weights' => [
                    'ws' => ['score' => [1, 5, 2], 'weight' => 0.2],
                ],
                'required' => true,
                'filter' => range(0, 12),
            ],

            ['id' => 52,
                'title' => 'What is your biggest concern for your child\'s education?',
                'type' => 'TextArea',
                'placeholder' => 'Type here',
                'value' => null,
                'required' => true,
                'filter' => range(0, 12),
            ],
            
        ];
    }
    
    private function optionsLikely(){
        return [
            ['label' => 'Very Likely', 'selected' => false], 
            ['label' => 'Likely', 'selected' => false], 
            ['label' => 'Somewhat likely', 'selected' => false], 
            ['label' => 'Unlikely', 'selected' => false], 
            ['label' => 'Very Unlikely', 'selected' => false]
        ];
    }
    
    private function optionsYesNo(){
        return [
            ['label' => 'Yes', 'selected' => false],
            ['label' => 'No', 'selected' => false]
        ];
    }
    
    private function optionsYesNoMaybe(){
        return [
            ['label' => 'Yes', 'selected' => false],
            ['label' => 'No', 'selected' => false],
            ['label' => 'May be', 'selected' => false]
        ];
    }
    
    private function optionsYesNoNotSure(){
        return [
            ['label' => 'Yes', 'selected' => false],
            ['label' => 'No', 'selected' => false],
            ['label' => 'Not sure', 'selected' => false]
        ];
    }
    
    private function optionsAware(){
        return [
            ['label' => 'Very aware', 'selected' => false], 
            ['label' => 'Aware', 'selected' => false], 
            ['label' => 'Somewhat aware', 'selected' => false],
            ['label' => 'Not at all aware', 'selected' => false]
        ];
    }
    
    private function optionsExtraCurricular(){
        return [
            ['label' => 'Sports', 'selected' => false],
            ['label' => 'Drama & Elocution', 'selected' => false],
            ['label' => 'Music', 'selected' => false],
            ['label' => 'Dance', 'selected' => false],
            ['label' => 'Art & Craft', 'selected' => false],
            ['label' => 'Creative Writing', 'selected' => false],
            ['label' => 'Volunteer Work', 'selected' => false],
            ['label' => 'I am not sure', 'selected' => false],
            ['label' => 'None', 'selected' => false],
            ['label' => 'Others', 'selected' => false]
        ];
    }
    
    private function optionsCareers(){
        return [
            ['label' => 'Arts', 'selected' => false],
            ['label' => 'Engineering & Technology', 'selected' => false],
            ['label' => 'Business, Commerce and Economics', 'selected' => false],
            ['label' => 'Humanities', 'selected' => false],
            ['label' => 'Maths and Statistics', 'selected' => false],
            ['label' => 'Bio Sciences', 'selected' => false],
            ['label' => 'Medicine', 'selected' => false],
            ['label' => 'Not Sure', 'selected' => false]
        ];
    }
    
    private function optionsIndustries(){
        return [
            ['label' => 'Automobile', 'selected' => false],
            ['label' => 'Civil Services', 'selected' => false],
            ['label' => 'Construction', 'selected' => false],
            ['label' => 'Consumer Retail', 'selected' => false],
            ['label' => 'Design', 'selected' => false],
            ['label' => 'Education', 'selected' => false],
            ['label' => 'Energy', 'selected' => false],
            ['label' => 'Engineering', 'selected' => false],
            ['label' => 'Finance', 'selected' => false],
            ['label' => 'Health', 'selected' => false],
            ['label' => 'Hospitality', 'selected' => false],
            ['label' => 'Law', 'selected' => false],
            ['label' => 'Media', 'selected' => false],
            ['label' => 'Public Sector', 'selected' => false],
            ['label' => 'Technology', 'selected' => false],
            ['label' => 'Textile', 'selected' => false],
            ['label' => 'Others', 'selected' => false],
        ];
    }
    
    private function optionsNumeric($start, $end){
        
        $options = [];
        foreach(range($start, $end) as $number){
            $options[] = ['label' => $number, 'selected' => false];
        }
        
        return $options;
    }
    
    private function getGradeOptions(){
        $grades = [];
        foreach($this->getGrades() as $Grade){
            array_push($grades, ['label' => $Grade, 'selected' => false]);
        }
        
        return $grades;
    }
    
    public function getGrades(){
        return ['Below Grade 1', 'Grade 1', 'Grade 2', 'Grade 3', 'Grade 4',
                'Grade 5', 'Grade 6', 'Grade 7', 'Grade 8', 'Grade 9', 'Grade 10', 'Grade 11', 'Grade 12'];
    }
    
    private function _d($message, $severity = Klogger::DEBUG){
        $line = sprintf('%s', is_array($message) ? json_encode($message) : $message);
        array_push($this->messages, $line);
        $this->logger->log($line, $severity);
    }
    
}
