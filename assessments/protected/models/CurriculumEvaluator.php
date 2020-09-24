<?php

class CurriculumEvaluator extends CActiveRecord {
    
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    public function tableName() {
        return 'oc_curriculum_evaluator';
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
