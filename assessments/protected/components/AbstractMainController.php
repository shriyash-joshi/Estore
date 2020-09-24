<?php

abstract class AbstractMainController extends Controller {

    public $layout = 'main';
    public $header = true;

    public function init() {
        parent::init();

        Yii::app()->clientScript->scriptMap['jquery.js'] = Yii::app()->request->baseUrl . '/js/jquery.min.2.2.4.js';
        Yii::app()->clientScript->scriptMap['jquery.min.js'] = Yii::app()->request->baseUrl . '/js/jquery.min.2.2.4.js';
        Yii::app()->clientScript->scriptMap['jquery.yiiactiveform.js'] = Yii::app()->request->baseUrl . '/js/jquery.yiiactiveform.modfied.js';
    }

}
