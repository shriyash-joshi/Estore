<?php

class SurveyResponse extends CActiveRecord {

    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    public function tableName() {
        return 'oc_survey_response';
    }
    
    public function behaviors(){
	return [
            'CTimestampBehavior' => [
                'class' => 'zii.behaviors.CTimestampBehavior',
                'createAttribute' => 'added_on',
                'updateAttribute' => 'modified_on',
                'timestampExpression' => "date('Y-m-d H:i:s')",
            ],
        ];
    }
    
    public static function getResponse($survey_id, $question_id, $create_new, $answer_type = 'InputText'){
        $Response = self::model()->find('survey_id = :sid AND question_id = :qid', [
            ':sid' => $survey_id, 
            ':qid' => (String)$question_id
        ]);
        if(!$Response && $create_new){
            $Response = new SurveyResponse;
            $Response->survey_id = $survey_id;
            $Response->question_id = $question_id;
            $Response->answer_type = $answer_type;
            $Response->save(false);
        }
        
        return $Response;
    }
    
    public static function getCurriculumStudentName($survey_id){
        return self::getAnswer($survey_id, 3.1);
    }
    
    public static function getCurriculumStudentGrade($survey_id){
        return self::getAnswer($survey_id, 6);
    }
    
    public static function getCurriculumStudentSchoolName($survey_id){
        return self::getAnswer($survey_id, 7.1);
    }
    
    public static function getCurriculumStudentCurriculum($survey_id){
        return self::getAnswer($survey_id, 7.2);
    }
    
    public static function getCurriculumStudentCity($survey_id){
        return self::getAnswer($survey_id, 5.1);
    }
    
    public static function getCurriculumStudentDob($survey_id){
        return self::getAnswer($survey_id, 4.2);
    }
    
    private static function getAnswer($survey_id, $question_id){
        return Yii::app()->db->createCommand()
                ->select('answer')
                ->from('oc_survey_response')
                ->where('survey_id = :sid AND question_id = :qid', [':sid' => $survey_id, ':qid' => $question_id])
                ->queryScalar();
        
    }
    

}
