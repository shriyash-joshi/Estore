<?php

class CurriculumCourseController extends AbstractMainController {
    
    const TEST_ID = "curriculum-course";
    const PDF_KEY = 'a1f587f6247fcdb4b6fa6268a714e3f3';
    
    use AjaxValidationTrait;

    /* @var OrderAssessment $OrderAssessment */
    private $OrderAssessment;

    /* @var Survey $Survey */
    private $Survey;

    /* @var CurriculumEvaluator $CurriculumEvaluator */
    private $CurriculumEvaluator;

    /* @var CurriculumCourse $CurriculumCourse */
    private $CurriculumCourse;

    public function beforeAction($action) {
        parent::beforeAction($action);
       
        $this->CurriculumCourse = new CurriculumCourse();
        $this->getOrderAssessment();
        $this->getSurvey();
        $this->getCurriculumEvaluator();
        
        return true;
    }
    
    
    public function actionIndex(){
        
        $model = new CurriculumFeedback();
        
        if(Yii::app()->request->isPostRequest){
            $this->ajaxValidatePost($model);
            $model->setAttributes(Yii::app()->request->getParam(get_class($model)));
            
            if($model->validate()){
                $this->Survey->rating = $model->rating;
                $this->Survey->rating_comments = null;
                if($model->contact_number){
                    $this->Survey->contact_number = $model->contact_number;
                }
                if((int)$model->rating < 4){
                    $this->Survey->rating_comments = $model->time_slot;
                }
                
                $this->Survey->save(false);
                $this->refresh();
            }
            
        }else{
            $model->rating = $this->Survey->rating;
            $model->contact_number = $this->Survey->contact_number;
            if((int)$model->rating < 4){
                $model->time_slot = $this->Survey->rating_comments;
            }
        }
        
        $survey_complete = ($this->Survey->completed == 1);
        $pdf_generated = (strlen($this->CurriculumEvaluator->pdf) > 4);
        $has_feedback = ($this->Survey->rating >= 1);
        $has_time_slot = ($this->Survey->rating < 4 && $this->Survey->rating_comments);
        
        $this->render('index', compact('survey_complete', 'pdf_generated', 'model', 'has_feedback', 'has_time_slot'));
    }
    
    public function actionBasicInfo(){
        
        $model = new SurveyBasicInfo;
        
        if(Yii::app()->request->isPostRequest){
            $this->ajaxValidatePost($model);
            $model->setAttributes(Yii::app()->request->getParam(get_class($model)));
            
            if($model->validate()){
                $this->Survey->full_name = $model->full_name;
                $this->Survey->email = $model->email;
                if($model->contact_number){
                    $this->Survey->contact_number = $model->contact_number;
                }
                
                $this->Survey->save(false);
                
                $this->redirect($this->createUrl('questionnaire', filter_input_array(INPUT_GET) ? filter_input_array(INPUT_GET) : []));
            }
        }else{
            $model->full_name = $this->Survey->full_name;
            $model->email = $this->Survey->email;
            $model->contact_number = $this->Survey->contact_number;
        }
        
        $this->render('basic_info', compact('model'));
    }
    
    public function actionGenerateReport(){
        if(!$this->Survey->completed 
                || !$this->CurriculumEvaluator->course_completed 
                || (strlen($this->CurriculumEvaluator->pdf) > 4)){
            $this->redirect($this->createUrl('index', filter_input_array(INPUT_GET) ? filter_input_array(INPUT_GET) : []));
        }
        
        if(Yii::app()->request->isPostRequest){
            
            $pdf_dir = Yii::getPathOfAlias('application') 
                    . DIRECTORY_SEPARATOR . 'runtime'
                    . DIRECTORY_SEPARATOR . 'ce' .DIRECTORY_SEPARATOR;
            
            if(!is_dir($pdf_dir)){
                if(!mkdir($pdf_dir, 0777, true)){
                    throw new Exception(sprintf('unable to create the directory "%s"', $pdf_dir));
                }
            }

            $AnswerResults = Yii::app()->db->createCommand()
                ->select(['question_id AS id', 'answer as title'])
                ->from('oc_survey_response')
                ->where('survey_id = :id', [':id' => $this->Survey->id])
                ->queryAll();

            if(!$AnswerResults){
                $this->redirect($this->createUrl('index', filter_input_array(INPUT_GET) ? filter_input_array(INPUT_GET) : []));
            }

            $Answers = CHtml::listData($AnswerResults, 'id', 'title');
            $this->CurriculumCourse->setAnswers($Answers);

            $boards_avg = $this->CurriculumCourse->getSuggestedBoards();

            $this->CurriculumEvaluator->setAttributes([
                'ws' => $this->CurriculumCourse->getWs(),
                'fm' => $this->CurriculumCourse->getFm(),
                'sd' => $this->CurriculumCourse->getSd(),
                'ca' => $this->CurriculumCourse->getCa(),
                'ib_avg' => $boards_avg['ib'],
                'cambridge_avg' => $boards_avg['cambridge'],
                'icse_avg' => $boards_avg['icse'],
                'cbse_avg' => $boards_avg['cbse']
            ], false);

            $file_name = md5(uniqid()) . '.pdf';
            $url = Yii::app()->createAbsoluteUrl('/curriculumCourse/curriculamPdfGenerator',
                    filter_input_array(INPUT_GET) ? filter_input_array(INPUT_GET) : []);
            
            //$this->redirect($url);

            if(!$this->OrderAssessment->completed_on){
                $this->OrderAssessment->completed_on = date('Y-m-d H:i:s');
                $this->OrderAssessment->save(false);
            }

            $command = sprintf('%s --page-height "313mm" --page-width "250mm" --javascript-delay 2500 -R 0 -L 0 -T 0 -B 0 %s %s', '/usr/bin/wkhtmltopdf',
                $url, $pdf_dir . $file_name);
            shell_exec($command);
            
            $this->CurriculumEvaluator->pdf = $file_name;
            $this->CurriculumEvaluator->save(false);
            
            ob_clean();
            header('Content-type: application/pdf');
            header('Content-Disposition: inline; filename="the.pdf"');
            header('Content-Length: ' . filesize($pdf_dir . $file_name));
            @readfile($pdf_dir . $file_name);            
            
            Yii::app()->end();
        }
        
        $this->render('generate_report');
    }
    
    public function actionDownloadReport(){
        
        if(!$this->CurriculumEvaluator->pdf){
            throw new CHttpException(404);
        }
        
        $pdf_dir = Yii::getPathOfAlias('application') 
                . DIRECTORY_SEPARATOR . 'runtime'
                . DIRECTORY_SEPARATOR . 'ce' .DIRECTORY_SEPARATOR;
        
        ob_clean();
        header('Content-type: application/pdf');
        header('Content-Disposition: inline; filename="the.pdf"');
        header('Content-Length: ' . filesize($pdf_dir . $this->CurriculumEvaluator->pdf));
        @readfile($pdf_dir . $this->CurriculumEvaluator->pdf);            

        Yii::app()->end();
    }

    public function actionCurriculamPdfGenerator(){

        $AnswerResults = Yii::app()->db->createCommand()
                ->select(['question_id AS id', 'answer as title'])
                ->from('oc_survey_response')
                ->where('survey_id = :id', [':id' => $this->Survey->id])
                ->queryAll();

        if(!$AnswerResults){
            $this->redirect($this->createUrl('index', filter_input_array(INPUT_GET) ? filter_input_array(INPUT_GET) : []));
        }

        $SelectedGrade = SurveyResponse::getResponse($this->Survey->id, 6, false);
        $GradeIndex = array_search($SelectedGrade->answer, $this->CurriculumCourse->getGrades());

        $Answers = CHtml::listData($AnswerResults, 'id', 'title');
        $this->CurriculumCourse->setAnswers($Answers);

        $boards_avg = $this->CurriculumCourse->getSuggestedBoards();

        $boards = array_keys($boards_avg);
            
        $cbse_override = false;
        if($boards[0] !== 'cbse'){
            if($SelectedGrade){

                if($GradeIndex >= 8){
                    $Q29 = SurveyResponse::getResponse($this->Survey->id, 29, false);
                    if($Q29 && in_array($Q29->answer, ['Engineering & Technology', 'Medicine'])){
                        $Q3P1 = SurveyResponse::getResponse($this->Survey->id, 31.1, false);
                        if($Q3P1 && $Q3P1->answer == 'India' && $boards[0] !== 'cbse'){
                            unset($boards[array_search('cbse', $boards)]);
                            array_unshift($boards, 'cbse');
                            $cbse_override = true;
                        }
                    }
                }

                if($GradeIndex < 8){
                    $Q21 = SurveyResponse::getResponse($this->Survey->id, 21, false);
                    if($Q21 && $Q21->answer == 'Yes'){
                        $Q22 = SurveyResponse::getResponse($this->Survey->id, 22, false);
                        if($Q22 && $Q22->answer == 'India' && $boards[0] !== 'cbse'){
                            unset($boards[array_search('cbse', $boards)]);
                            array_unshift($boards, 'cbse');
                            $cbse_override = true;
                        }
                    }
                }
            }
        }

        $grade = 'am3';
        if((int)$GradeIndex == 0){
            $grade = 'am1';
        }

        if((int)$GradeIndex >=1 && (int)$GradeIndex <= 7){
            $grade = 'am2';
        }

        $Am0 = $this->CurriculumCourse->getAspirationsMatch($grade, $boards[0]);
            
        if(count($Am0) < 4){
            $Am1 = $this->CurriculumCourse->getAspirationsMatch($grade, $boards[1]);
            foreach($Am1 as $_am){
                if(!in_array($_am, $Am0)){
                    array_push($Am0, $_am);
                }
            }
        }

        $am = array_slice($Am0, 0, 4);
        
        if(count($am) < 4){
            if($grade == 'am1' && $boards[0] == 'cbse'){
                foreach(['Builds foundation for competitive exams', 'Highly recognised national curriculum'] as $label){
                    array_push($am, $label);
                    if(count($am) >= 4) break;
                }
            }

            if($grade == 'am2' && $boards[0] == 'icse'){
                foreach(['Capability to excel in any field', 'Highly recognised national curriculum'] as $label){
                    array_push($am, $label);
                    if(count($am) >= 4) break;
                }
            }

            if($boards[0] == 'cambridge'){
                foreach(['More control over course load', 'Skills based learning'] as $label){
                    array_push($am, $label);
                    if(count($am) >= 4) break;
                }
            }

            if($boards[0] == 'ib'){
                foreach(['Skills based learning', 'Choice for student to choose levels of difficulty in upper classes'] as $label){
                    array_push($am, $label);
                    if(count($am) >= 4) break;
                }
            }
        }
        
        $keywords = [
            'cbse' => [
                'Focus on extracurricular activities' => [
                    'alt' => 'Balanced extracurricular activities',
                    'desc' => 'If extracurricular activities matter to you, then CBSE is a good choice. In recent times, the CBSE curriculum has been encouraging extracurricular activities for overall personality development.',
                ],
                'Good academic results' => [
                    'alt' => 'Structured Assessments',
                    'desc' => 'The curriculum supports well structured syllabus that enables students to prepare and achieve good academic results and also entrance exam centric.',
                ],
                'Pursuing professional courses in India' => [
                    'alt' => 'Entrance Exam Centric',
                    'desc' => 'The Entrance Exams are completely based on CBSE syllabus and therefore the students get good foundation for test preparation. Your aspirations to pursue professional courses in India can be fullfilled by choosing a CBSE curriculum.',
                ],
                'Structured Learning' => [
                    'alt' => 'Well designed syllabus',
                    'desc' => 'The CISCE curriculum has a well structured syllabus that is comprehensive and aims to build analytical skills and practical knowledge.',
                ],
                'Career Aspirations in India' => [
                    'alt' => 'Promising Professional Careers',
                    'desc' => 'Your choice for your child to take up professional courses in India makes CBSE the right fit. It will help your child best prepare for the entrance exams and get into top colleges in India.',
                ],
                'School choice within budget' => [
                    'alt' => 'Moderate fee structure',
                    'desc' => 'National curricula such as the CBSE, and affiliated schools offer moderate fee structure when compared to international curricula, since that might be one of the deciding factors.',
                ],
                'Proximity of good schools' => [
                    'alt' => 'Highest number of school affiliations',
                    'desc' => 'Since you have given a higher priority to choosing a school within proximity, CBSE is the most suitable option as CBSE curriculum schools have a wide network all over the country.',
                ],
                'Infrastructure and facilities' => [
                    'alt' => 'State of the art private campuses',
                    'desc' => 'There are a few top-notch CBSE schools that follow a national curriculum and strive hard to enhance the student experiences in schools, by providing state of the art infrastructure facilities.',
                ],
                'Constant relocation within India' => [
                    'alt' => 'Wide network of schools affiliated',
                    'desc' => 'Due to your frequent relocation between cities, you could prefer choosing a national curriculum that is available across the country, like the CBSE, that ensures continuity in learning.',
                ],
                'Builds foundation for competitive exams' => [
                    'alt' => 'Entrance exams based Curriculum',
                    'desc' => 'The competitive exams such as JEE, NEET base their exams on NCERT books that CBSE schools follow. So, the CBSE curriculum provides a solid base for entrance exams in India.',
                ],
                'Highly recognised national curriculum' => [
                    'alt' => 'Recognised national curriculum with wide acceptance',
                    'desc' => 'National curriculum has wide acceptance across the national Universities as well as the international Universities. The curriculum is well rounded in terms of content and students easily secure admissions in top colleges.',
                ],
            ],
            'icse' => [
                'Focus on extracurricular activities' => [
                    'alt' => 'Moderate amount of extracurricular activities',
                    'desc' => 'If extracurricular activities matter to you, then CISCE is a good choice. In recent times, the CISCE curriculum has been encouraging extracurricular activities for overall personality development.'
                ],
                'Experiential learning' => [
                    'alt' => 'Encourages learning by doing',
                    'desc' => 'Your priority that your child should have a more experiential learning, then CISCE is the best fit curriculum as the pedogogical methods support experiential learning.'
                ],
                'Good academic results' => [
                    'alt' => 'Structured Assessments',
                    'desc' => 'The curriculum supports well structured syllabus that enables students to prepare and achieve good academic results.'
                ],
                'Varied Subject choices' => [
                    'alt' => 'Wide range of subjects to choose',
                    'desc' => 'CISCE offers a myriad of subjects, with strong English skills, that allow your child to excel in not just in Engineering and Medicine but also in any other field in India and globally.',
                ],
                'Career Aspirations in India' => [
                    'alt' => 'Promising Professional Careers',
                    'desc' => 'Your choice for your child to take up professional courses in India makes CISCE the right fit. It will help your child best prepare for the entrance exams and get into top colleges in India.'
                ],
                'School choice within budget' => [
                    'alt' => 'Moderate fee structure',
                    'desc' => 'National curricula such as the CISCE, and affiliated schools offer moderate fee structure when compared to international curricula, since that might be one of the deciding factors. '
                ],
                'Proximity of good schools' => [
                    'alt' => 'Highest number of school affiliations',
                    'desc' => 'Since you have given a higher priority to choosing a school within proximity, CISCE is the most suitable option as CISCE curriculum schools have a wide network all over the country. '
                ],
                'Infrastructure and facilities' => [
                    'alt' => 'State of the art private campuses',
                    'desc' => 'There are a few top-notch CISCE schools that follow a national curriculum and strive hard to enhance the student experiences in schools, by providing state of the art infrastructure facilities.'
                ],
                'Constant relocation within India' => [
                    'alt' => 'Wide network of schools affiliated',
                    'desc' => 'Due to your frequent relocation between cities, you could prefer choosing a national curriculum that is available across the country, like the CISCE, that ensures continuity in learning.'
                ],
                'Capability to excel in any field' => [
                    'alt' => 'Diverse Syllabus',
                    'desc' => 'CISCE offers a myriad of subjects, with strong English skills, that allow your child to excel in not just in Engineering and Medicine but also in any other field in India and globally.'
                ],
                'Highly recognised national curriculum' => [
                    'alt' => 'Recognised national curriculum with wide acceptance',
                    'desc' => 'National curriculum has wide acceptance across the national Universities as well as the international Universities. The curriculum is well rounded in terms of content and students easily secure admissions in top colleges.'
                ],
                'Pursuing professional courses in India' => [
                    'alt' => 'Varied Career choices',
                    'desc' => 'The CISCE curriculum offers various subject choices that enables the students to have various career options. It also supports STEM and NON-STEM subjects equally.'
                ],
                'Structured Learning' => [
                    'alt' => 'Well designed syllabus',
                    'desc' => 'The CISCE curriculum has a well structured syllabus that is comprehensive and aims to build analytical skills and practical knowledge.'
                ],
            ],
            'cambridge' => [
                'Small Size class rooms' => [
                    'alt' => 'Allows 20-25 per class',
                    'desc' => 'International schools, such as Cambridge schools, normally have a class size of 20-25. With smaller sized classrooms, schools can indulge in many interactive learning engagements.'
                ],
                'Infrastructure and facilities' => [
                    'alt' => 'State of the art Campuses',
                    'desc' => 'Since having good Infrastructure facilities is one of your priorities, the Cambridge Curriculum could be a good fit. Cambridge schools offer world class infrastructure to emulate international standards. '
                ],
                'Focus on extracurricular activities' => [
                    'alt' => 'Mandatory extracurricular activities',
                    'desc' => 'International schools ensure mandatory extracurricular activities in their curriculum which enables the child to build an all rounded profile that prove to be a gateway for top universities across the globe.'
                ],
                'Varied Subject choices' => [
                    'alt' => 'Wide range of subjects to choose',
                    'desc' => 'There is no better option than an international curriculum if you’re concerned with a wider subject choice. Cambridge schools offer real choice - both of subject and subject combination.'
                ],
                'International career aspirations' => [
                    'alt' => 'Curriculum accepted globally',
                    'desc' => 'Your aspirations for global-standard education, makes you a great fit for the Cambridge curriculum. The knowledge and exposure appeals to the highest ranking universities.'
                ],
                'Highly skilled teachers' => [
                    'alt' => 'Employs teachers with Professional Development Qualifications',
                    'desc' => 'If you’re looking for a curriculum that commits to ongoing professional development of its faculty, then Cambridge is the way to go. The curriculum mandates regular training of teachers.'
                ],
                'Possibility of International Relocation' => [
                    'alt' => 'Global Standard Education',
                    'desc' => 'If your role requires you to move countries regularly, choosing the Cambridge curriculum for your child now can help with future opportunities since it follows a global standard of education.'
                ],
                'More control over course load' => [
                    'alt' => 'Help students learn in-depth in certain subjects',
                    'desc' => 'Cambridge curriculum offers different levels of difficulty for each subject. This enables the child to pick the level of difficulty which helps in striking a balance.'
                ],
                'Skills based learning' => [
                    'alt' => 'Active Learning',
                    'desc' => 'One of major aspirations is holistic development of your child by developing skills,then,choosing Cambridge curriculum is apt. Problem solving, critical thinking, independent research, collaboration and presenting arguments.'
                ],
                'Active learning' => [
                    'alt' => 'Student Centric Learning',
                    'desc' => 'Well rounded curriculum encourages active learning where the child involves and participates in activities. The Curriculum is very Student Centric.'
                ],
                'All round development' => [
                    'alt' => 'Balanced Curriculum',
                    'desc' => 'The curriculum caters to all round development of your child by enforcing a rigorous, challenging and a balanced program. You aspiration for holistic education for your child could be fullfilled by choosing a Cambridge Curriculum.'
                ],
            ],
            'ib' => [
                'Small Size class rooms' => [
                    'alt' => 'Allows 20-25 per class',
                    'desc' => 'International schools, such as IB schools, normally have a class size of 20-25. Sessions can be more interactive and each student can be given adequate attention by the teacher.'
                ],
                'Infrastructure and facilities' => [
                    'alt' => 'State of the art Campuses',
                    'desc' => 'Since you’re looking for good Infrastructure facilities, the IB curriculum could be a good fit. IB schools offer world class infrastructure to emulate international standards.'
                ],
                'Focus on extracurricular activities' => [
                    'alt' => 'Mandatory extracurricular activities',
                    'desc' => 'International schools ensure mandatory extracurricular activities in their curriculum which enables the child to build an all rounded profile that prove to be a gateway for top universities across the globe.'
                ],
                'Varied Subject choices' => [
                    'alt' => 'Wide range of subjects to choose',
                    'desc' => 'There is no better option than an international curriculum if you’re concerned with a wider subject choice. IB schools offer real choice - both of subject and subject combination.'
                ],
                'International career aspirations' => [
                    'alt' => 'Curriculum Accepted Global Universities',
                    'desc' => 'Your aspirations for global-standard education makes you a great fit for the IB curriculum. The knowledge and exposure appeals to the highest ranking universities.'
                ],
                'Highly skilled teachers' => [
                    'alt' => 'Employs teachers with Professional Development Qualifications',
                    'desc' => 'If you’re looking for highly skilled teachers then a curriculum that commits to ongoing professional development of its faculty such as IB is the way to go. The curriculum mandates regular training of teachers.'
                ],
                'Possibility of International Relocation' => [
                    'alt' => 'Global Standard Education',
                    'desc' => 'If your role requires you to move countries regularly, choosing the IB for your child now can help with future opportunities since it follows a global standard of education.'
                ],
                'Skills based learning' => [
                    'alt' => 'Active Learning',
                    'desc' => 'One of major aspirations is holistic development of your child by developing skills,then,choosing IB curriculum is apt. Problem solving, critical thinking, independent research, collaboration and presenting arguments.'
                ],
                'Choice for student to choose levels of difficulty in upper classes' => [
                    'alt' => 'Levels of academic challenge',
                    'desc' => 'IB curriculum offers different levels of difficulty for each subject. This enables the child to pick the level of difficulty which helps in striking a balance.'
                ],
                'All round development' => [
                    'alt' => 'Balanced Curriculum',
                    'desc' => 'The curriculum caters to all round development of your child by enforcing a rigorous, challenging and a balanced program. You aspiration for holistic education for your child could be fullfilled by choosing an IB curriculum.'
                ],
                'Active learning' => [
                    'alt' => 'Student Centric Learning',
                    'desc' => 'Well rounded curriculum encourages active learning where the child involves and participates in activities. The IB Curriculum is very Student Centric therefore a best fit for your child.'
                ],
            ],
        ];
            
        $Survey = $this->Survey;
        
        $aspirations = [];
        foreach($am as $label){
            $alt = null; $about = null;
            if(isset($keywords[$boards[0]][$label])){
                $alt = $keywords[$boards[0]][$label]['alt'];
                $about = $keywords[$boards[0]][$label]['desc'];
                
            }elseif(isset($keywords[$boards[1]][$label])){
                $alt = $keywords[$boards[1]][$label]['alt'];
                $about = $keywords[$boards[1]][$label]['desc'];
            }
            
            array_push($aspirations, [
                'label' => $label,
                'alt' => $alt,
                'about' => $about,
            ]);
            
        }

        $board_parameters = [
            $boards[0] => ['ws' => 0, 'fm' => 0, 'ca' => 0, 'sd' => 0],
            $boards[1] => ['ws' => 0, 'fm' => 0, 'ca' => 0, 'sd' => 0],
        ];

        $boards_min_max = $this->CurriculumCourse->getBoardsMinMax();

        $fm_avg_0 = ($boards_min_max[$boards[0]]['fm']['min']+$boards_min_max[$boards[0]]['fm']['max'])/2;
        $fm_avg_1 = ($boards_min_max[$boards[1]]['fm']['min']+$boards_min_max[$boards[1]]['fm']['max'])/2;

        $ca_avg_0 = ($boards_min_max[$boards[0]]['ca']['min']+$boards_min_max[$boards[0]]['ca']['max'])/2;
        $ca_avg_1 = ($boards_min_max[$boards[1]]['ca']['min']+$boards_min_max[$boards[1]]['ca']['max'])/2;

        $sd_avg_0 = ($boards_min_max[$boards[0]]['sd']['min']+$boards_min_max[$boards[0]]['sd']['max'])/2;
        $sd_avg_1 = ($boards_min_max[$boards[1]]['sd']['min']+$boards_min_max[$boards[1]]['sd']['max'])/2;

        $ws_avg_0 = $boards_min_max[$boards[0]]['ws']['min'];
        $ws_avg_1 = $boards_min_max[$boards[1]]['ws']['min'];

        if($cbse_override && $boards[1] ==  'ib'){
            $fm_avg_0 = 0.65;
            $ca_avg_0 = 0.65;
            $sd_avg_0 = 0.65;
            $ws_avg_0 = 0.6;
        }

        if($cbse_override && $boards[1] ==  'cambridge'){
            $fm_avg_0 = 0.55;
            $ca_avg_0 = 0.55;
            $sd_avg_0 = 0.55;
            $ws_avg_0 = 0.5;
        }

        $board_parameters[$boards[0]]['ca'] = round(($this->CurriculumCourse->getCa() / $ca_avg_0) * 100);
        $board_parameters[$boards[1]]['ca'] = round(($this->CurriculumCourse->getCa() / $ca_avg_1) * 100);

        $board_parameters[$boards[0]]['sd'] = round(($this->CurriculumCourse->getSd() / $sd_avg_0) * 100);
        $board_parameters[$boards[1]]['sd'] = round(($this->CurriculumCourse->getSd() / $sd_avg_1) * 100);

        $board_parameters[$boards[0]]['fm'] = round(($this->CurriculumCourse->getFm() / $fm_avg_0) * 100);
        $board_parameters[$boards[1]]['fm'] = round(($this->CurriculumCourse->getFm() / $fm_avg_1) * 100);

        $board_parameters[$boards[0]]['ws'] = round(($this->CurriculumCourse->getWs() / $ws_avg_0) * 100);
        $board_parameters[$boards[1]]['ws'] = round(($this->CurriculumCourse->getWs() / $ws_avg_1) * 100);

        foreach($board_parameters as &$board_parameter){
            foreach($board_parameter as &$parameter){
                if($parameter > 100){
                    $parameter = 100 - ($parameter%100);
                }
            }
        }

        $this->renderPartial('pdf_report', compact('Survey', 'GradeIndex', 'boards', 'aspirations', 'cbse_override', 'board_parameters'));
    }
    
    public function actionReevalute(){
        $this->Survey->last_question = null;
        $this->Survey->save(false);
        
        $this->CurriculumEvaluator->course_completed = 1;
        $this->CurriculumEvaluator->save(false);
        
        $this->redirect($this->createUrl('basicInfo', filter_input_array(INPUT_GET) ? filter_input_array(INPUT_GET) : []));
    }
    
    public function actionQuestionTemplate(){

        $this->renderPartial('question_template');
    }

    public function actionQuestionnaire(){
        
        if($this->CurriculumEvaluator->pdf
                || ($this->Survey->last_question && $this->Survey->completed)){
            $this->redirect($this->createUrl('index', filter_input_array(INPUT_GET) ? filter_input_array(INPUT_GET) : []));
        }
        
        $index = $this->Survey->last_question ? $this->CurriculumCourse->getQuestionIndex(floor($this->Survey->last_question))+1 : 0;
        
        $SelectedGrade = SurveyResponse::getResponse($this->Survey->id, 6, false);
        
        if($SelectedGrade){
            $GradeIndex = array_search($SelectedGrade->answer, $this->CurriculumCourse->getGrades());
            Yii::app()->session->add('SelectedGrade', $GradeIndex);
        }else{
            Yii::app()->session->add('SelectedGrade', null);
        }

        $CurriculumCourse = $this->CurriculumCourse;
        $this->render('questionnaire', compact('CurriculumCourse', 'index'));
    }
    
    public function actionNextQuestion(){
        
        if(!Yii::app()->request->isPostRequest){
            throw new CHttpException(405);
        }
        
        $raw_post = file_get_contents("php://input");
        $json = json_decode($raw_post, true);
        
        $Questions = $this->CurriculumCourse->getQuestions();
        
        $Question = []; $is_new_question = false;
        if(json_last_error() == JSON_ERROR_NONE){
            
            $index = $json['i'];
            if($json['p']){
                $Question = isset($Questions[$index]) ? $Questions[$index] : [];
                $is_new_question = true;
            }else{
                $Question = $json['q'];
                
                if($Question && $Question['type'] == 'Questions'){
                    
                    $has_error = false; $values = [];
                    foreach($Question['Questions'] as &$_Question){
                        if(!$this->CurriculumCourse->validateQuestion($_Question, $values)){
                            $has_error = true;
                        }
                    }
                    
                    if(!$has_error){
                        foreach($Question['Questions'] as &$_Question){
                            $this->saveQuestion($_Question);
                        }
                        $Question = isset($Questions[$index]) ? $Questions[$index] : [];
                        $is_new_question = true;
                    }
                    
                }else{
                    $values = [];
                    if($this->CurriculumCourse->validateQuestion($Question, $values)){
                        $this->saveQuestion($Question);
                        $Question = isset($Questions[$index]) ? $Questions[$index] : [];
                        $is_new_question = true;
                    }
                }
            }
            
            $GradeIndex = Yii::app()->session->get('SelectedGrade');
            if($GradeIndex !== null || $GradeIndex !== false){
                while(isset($Question['filter']) && !in_array($GradeIndex, $Question['filter'])){
                    $json['p'] ? $index-- : $index++;
                    if(isset($Questions[$index])){
                        $Question = $Questions[$index];
                        $is_new_question = true;
                    }else{
                        $Question = [];
                        break;
                    }
                }
            }
            
            while(isset($Question['depends'])){
                $Response = SurveyResponse::getResponse($this->Survey->id, $Question['depends']['id'], false);
                if($Response && $Response->answer){
                    if(in_array($Response->answer, $Question['depends']['values'])){
                       break; 
                    }
                }
                
                $json['p'] ? $index-- : $index++;
                
                if(isset($Questions[$index])){
                    $Question = $Questions[$index];
                    $is_new_question = true;
                }else{
                    $Question = [];
                    break;
                }
            }
        }
        
        if($is_new_question && $Question){
            if($Question['type'] == 'Questions'){
                foreach($Question['Questions'] as &$_Question){
                    $Response = SurveyResponse::getResponse($this->Survey->id, $_Question['id'], false);
                    if($Response && $Response->answer){
                        $this->CurriculumCourse->setAnswer($_Question, $Response->answer);
                    }
                }
            }else{
                $Response = SurveyResponse::getResponse($this->Survey->id, $Question['id'], false);
                if($Response && $Response->answer){
                    $this->CurriculumCourse->setAnswer($Question, $Response->answer);
                }
            }
        }
        
        ob_clean();
        if($Question){
            echo json_encode(['i' => $this->CurriculumCourse->getQuestionIndex($Question['id']), 'q' => $Question]);
        }else{
            if(!$this->Survey->completed){
                $this->Survey->completed = 1;
                $this->Survey->save(false);
            }
            
            echo json_encode(['i' => 0, 'q' => [], 'end' => true, 'generate' => ($this->CurriculumEvaluator->course_completed == 1)]);
        }
        
        Yii::app()->end();
    }
    
    private function saveQuestion($Question){
        
        if(!isset($Question['id'])) return;
        
        $Response = SurveyResponse::getResponse($this->Survey->id, $Question['id'], $Question['type'], true);
        if(!$Response->question){
            $Response->question = $Question['title'];
            $Response->save(false);
        }
        
        if(in_array($Question['type'], ['InputText', 'InputEmail', 'InputMobile', 'InputDate'])){
            if(!isset($Question['value']) || !$Question['value']) return;
            
            if($Question['type'] == 'InputDate'){
                $Question['value'] = date('Y-m-d', strtotime($Question['value']));
            }
            
            $Response->answer = $Question['value'];
            
        }else if($Question['type'] == 'InputRadio'){
            foreach($Question['options'] as $index => $Option){
                if(isset($Option['selected']) && $Option['selected']){
                    $Response->answer = $Option['label'];
                    if($Question['id'] == 6){
                        Yii::app()->session->add('SelectedGrade', $index);
                        $this->gradeEnforce($Response->answer);
                    }
                    break;
                }
            }
            
        }else if($Question['type'] == 'InputCheckbox'){
            $selected = [];
            foreach($Question['options'] as $Option){
                if(isset($Option['selected']) && $Option['selected']){
                    array_push($selected, $Option['label']);
                }
            }
            
            $Response->answer = json_encode($selected);
        }else{
            if(isset($Question['value']) && $Question['value']){
                $Response->answer = trim($Question['value']);
            }
        }
        
        $Response->save(false);
        $this->enforceDependency($Question['id']);
        $this->Survey->last_question = $Question['id'];
        $this->Survey->save(false);
    }

    private function enforceDependency($id){
        
        $Questions = $this->CurriculumCourse->getQuestions();
        
        foreach($Questions as $Question){
            if(isset($Question['depends']) && $Question['depends']['id'] == $id){
                $Response = SurveyResponse::getResponse($this->Survey->id, $id, false);
                if($Response && $Response->answer){
                    if(!in_array($Response->answer, $Question['depends']['values'])){
                        
                        if($Question['type'] == 'Questions'){
                            foreach($Question['Questions'] as $Ques){
                                SurveyResponse::model()->deleteAll('survey_id = :id AND question_id = :qid', [
                                    ':id' => $this->Survey->id,
                                    ':qid' => $Ques['id']
                                ]);
                            }
                        }
                        
                        SurveyResponse::model()->deleteAll('survey_id = :id AND question_id = :qid', [
                            ':id' => $this->Survey->id,
                            ':qid' => $Question['id']
                        ]);
                    }
                }
            }
        }
    }
    
    private function gradeEnforce($SelectedGrade){
        
        $Questions = $this->CurriculumCourse->getQuestions();
        
        $GradeIndex = array_search($SelectedGrade, $this->CurriculumCourse->getGrades());
        
        foreach($Questions as $Question){
            if(isset($Question['filter']) && !in_array($GradeIndex, $Question['filter'])){
                
                if($Question['type'] == 'Questions'){
                    foreach($Question['Questions'] as $Ques){
                        SurveyResponse::model()->deleteAll('survey_id = :id AND question_id = :qid', [
                            ':id' => $this->Survey->id,
                            ':qid' => $Ques['id']
                        ]);
                    }
                }
                
                SurveyResponse::model()->deleteAll('survey_id = :id AND question_id = :qid', [
                    ':id' => $this->Survey->id,
                    ':qid' => $Question['id']
                ]);
            }
        }
    }
    
    private function getOrderAssessment(){
        
        if($this->OrderAssessment === null){
            $this->OrderAssessment = OrderAssessment::model()
                ->find('unique_id = :h AND test_id = :id', [':h' => Yii::app()->request->getParam('h'), ':id' => self::TEST_ID]);
        }
        
        if(!$this->OrderAssessment){
            throw new CHttpException(404);
        }

        if(!$this->OrderAssessment->started_on){
            $this->OrderAssessment->started_on = date('Y-m-d H:i:s');
            $this->OrderAssessment->save(false);
        }

        return $this->OrderAssessment;
    }
    
    private function getSurvey(){
        
        $this->Survey = Survey::model()->find('unique_id = :id', [':id' => Yii::app()->request->getParam('h')]);
        
        if(!$this->Survey){
            $this->Survey = new Survey;
            $this->Survey->setAttributes([
                'unique_id' => Yii::app()->request->getParam('h'),
                'survey_type' => self::TEST_ID,
            ], false);
            
            $this->Survey->save(false);
        }
    }
    
    private function getCurriculumEvaluator(){
        if(!$this->Survey) return;
        
        $this->CurriculumEvaluator = CurriculumEvaluator::model()->find('survey_id = :id', [':id' => $this->Survey->id]);
        if(!$this->CurriculumEvaluator){
            $this->CurriculumEvaluator = new CurriculumEvaluator;
            $this->CurriculumEvaluator->survey_id = $this->Survey->id;
            $this->CurriculumEvaluator->save(false);
        }
    }
    
    
}
