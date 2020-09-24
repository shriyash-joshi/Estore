<?php

class CommerceController extends CareerGuideController {
    
    const TEST_ID = "11";
    
    public function actionIndex(){
        
        $this->render('index');
    }
    
    public function getTestId() {
        return self::TEST_ID;
    }
    
}
