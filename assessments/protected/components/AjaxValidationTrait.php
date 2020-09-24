<?php

trait AjaxValidationTrait{

    public function ajaxValidatePost($models, $attributes = null, $loadInput = true){
        if(Yii::app()->request->isAjaxRequest){
            echo CActiveForm::validate($models, $attributes, $loadInput);
            Yii::app()->end();
        }
    }
}