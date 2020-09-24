<?php

class IdealCareerController extends CareerGuideController {
    
    const TEST_ID = "12";
    
    public function actionIndex(){
        
        $this->render('index');
    }
    
    public function getTestId() {
        return self::TEST_ID;
    }
    
}
