<?php

class SiteController extends AbstractMainController {

    public function actionIndex() {
        
        $this->redirect('http://' . Yii::app()->getRequest()->serverName);
    }
    
    public function actionError(){
        
        $error = Yii::app()->errorHandler->error;
        
        $this->render('error', compact('error'));
    }
}
