<?php

class PersonalityStyleController extends KeysToSucceedController {
    
    const TEST_ID = "104";
    
    public function actionIndex(){
        
        $this->render('index');
    }
    
    public function getTestId() {
        return self::TEST_ID;
    }
    
}
