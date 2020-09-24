<?php

class Survey extends CActiveRecord {

    /**
     * @param string $className
     * @param int $id
     * @param string $unique_id
     * @param string $survey_type
     * @param string $full_name
     * @param string $email
     * @param string $contact_number
     * @param int $completed
     * @param string $last_question
     * @param int $rating
     * @param string $rating_comments
     * @param string $added_on
     * @param string $modified_on
     * @return CActiveRecord
     */

    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    public function tableName() {
        return 'oc_survey';
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
    

}
