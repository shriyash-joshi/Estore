<?php

class LearningStyleController extends KeysToSucceedController {
    
    const TEST_ID = "101";
    
    public function actionIndex(){
        
        $this->render('index');
    }
    
    public function getTestId() {
        return self::TEST_ID;
    }
    
}
