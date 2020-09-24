<?php

class MultiIntelligenceController extends KeysToSucceedController {
    
    const TEST_ID = "100";
    
    public function actionIndex(){
        
        $this->render('index');
    }
    
    public function getTestId() {
        return self::TEST_ID;
    }
    
}
