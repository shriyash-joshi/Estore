<?php

class EngineeringController extends CareerGuideController {
    
    const TEST_ID = "9";
    
    public function actionIndex(){
        
        $this->render('index');
    }
    
    public function getTestId() {
        return self::TEST_ID;
    }
    
}
