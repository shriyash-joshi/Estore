<?php

class StreamSelectorController extends CareerGuideController {
    
    const TEST_ID = "8";
    
    public function actionIndex(){
        
        $this->render('index');
    }
    
    public function getTestId() {
        return self::TEST_ID;
    }
    
}
