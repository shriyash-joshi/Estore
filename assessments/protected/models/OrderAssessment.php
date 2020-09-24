<?php

/**
 * Class OrderAssessment
 * @property int $order_assessment_id
 * @property string $assessment_name
 * @property int $order_product_id
 * @property int $product_id
 * @property int $group_no
 * @property string $assigned_name
 * @property string $assigned_email
 * @property string $test_id
 * @property string $test_provider
 * @property string $test_user_email
 * @property string $test_user_name
 * @property string $test_user_id
 * @property string $test_external_id
 * @property string $test_link
 * @property string $unique_id
 * @property int $counselling
 * @property string $added_on
 * @property string $modified_on
 * @property string $started_on
 * @property string $completed_on
 *
 */

class OrderAssessment extends CActiveRecord {

    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    public function tableName() {
        return 'unwp_order_assessment';
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
