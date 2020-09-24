<?php

class HumanitiesController extends CareerGuideController {
    
    const TEST_ID = "10";
    
    public function actionIndex(){
        
        $this->render('index');
    }
    
    public function getTestId() {
        return self::TEST_ID;
    }
    
}
