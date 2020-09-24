<?php

/**
 * @property integer $customer_id
 * @property integer $customer_type
 * @property integer $customer_group_id
 * @property string $firstname
 * @property string $lastname
 * @property string $email
 * @property string $telephone
 * @property integer $status
 */

class Counsellor extends CActiveRecord {

    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    public function tableName() {
        return 'unwp_users';
    }

    /**
     * @return string
     */
    public function getFullName() {
        return trim($this->firstname . ' ' . $this->lastname);
    }


}
