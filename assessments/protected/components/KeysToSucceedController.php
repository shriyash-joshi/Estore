<?php

abstract class KeysToSucceedController extends AbstractMainController {
    
    use AjaxValidationTrait;
    
    protected $OrderAssessment;

    protected $accountKey;
    protected $accountPassword;
    protected $accountId;
    protected $testMode;
    protected $testServer;
    
    abstract function getTestId();
    
    public function beforeAction($action) {
        parent::beforeAction($action);
        
        if (Yii::app()->getRequest()->isSecureConnection) {
            $url = 'http://' . Yii::app()->getRequest()->serverName . Yii::app()->getRequest()->requestUri;
            Yii::app()->request->redirect($url);
        }
        
        $this->getOrderAssessment();
        if(!$this->OrderAssessment){
            throw new CHttpException(404);
        }
        
        if(filter_input(INPUT_SERVER, 'HTTP_HOST') == 'shop.univariety.com' || true){
            $this->testMode = 'prod';
            $this->testServer = 'https://api.keystosucceed.cn';
            $this->accountKey = 'ZPabX1ve69yQjQvx';
            $this->accountPassword = '2wllKRSQIqMgfC8P';
            $this->accountId = 10000000016;
        }else{
            $this->testMode = 'staging';
            $this->testServer = 'https://api.staging.humanesources.com';
            $this->accountKey = 'fl8Jqeqc8eEIHumq';
            $this->accountPassword = '0dTg5LCwZpkRJRp7';
            $this->accountId = 100108;
        }
        
        return true;
    }
    
    public function actionTakeTest() {
        $this->header = false;
        
        if(!$this->OrderAssessment->test_user_id){
            $this->redirect($this->createUrl('personalInfo', filter_input_array(INPUT_GET) ? filter_input_array(INPUT_GET) : []));
        }
        
        $testUrl = $this->getTestUrl();
        
        $this->render('//site/take_test', compact('testUrl'));
    }
    
    public function actionPersonalInfo() {
        $this->header = false;
        
        if($this->OrderAssessment->test_user_id){
            $this->redirect($this->createUrl('takeTest', filter_input_array(INPUT_GET) ? filter_input_array(INPUT_GET) : []));
        }
        
        $model = new PersonalInfoForm();
        
        if(Yii::app()->request->isPostRequest){
            $this->ajaxValidatePost($model);
            
            $model->setAttributes(Yii::app()->request->getParam(get_class($model)), true);
            if($model->validate() && $model->updatePersonalInfo($this->OrderAssessment)){
                if(!$this->OrderAssessment->test_user_id){
                    $this->createUserAccount();
                }
                $this->redirect($this->createUrl('takeTest', filter_input_array(INPUT_GET) ? filter_input_array(INPUT_GET) : []));
            }
        }
        
        $this->render('//site/persional_info', compact('model'));
    }
    
    public function actionTestCompleted(){
        $data = filter_input_array(INPUT_POST);
        
        if(isset($data['completed_at'])){
            $this->OrderAssessment->completed_on = date('Y-m-d H:i:s', strtotime($data['completed_at']));
            $this->OrderAssessment->save(false);
        }
        
        Yii::app()->end();
    }
    
    private function getTestUrl() {
        $HesClient = new HesClient($this->testMode);
        $nonce = $HesClient->handshake($this->accountId, $this->accountPassword, $this->accountKey);
        $encryptedUserId = $HesClient->encryptMe($this->OrderAssessment->test_user_id, $this->accountKey);

        return $HesClient->getLoginUrl($this->accountId, $this->OrderAssessment->test_id, $encryptedUserId, $nonce);
    }
    
    protected function createUserAccount() {
        
        if(!$this->OrderAssessment->test_user_name || !$this->OrderAssessment->test_user_email){
            $this->redirect($this->createUrl('personalInfo', filter_input_array(INPUT_GET) ? filter_input_array(INPUT_GET) : []));
        }
        
        $userDetails = [
            'user_type_id' => 1,
            'first_name' => $this->OrderAssessment->test_user_name,
            'email_address' => urlencode($this->OrderAssessment->test_user_email),
            'under_13' => 1,
        ];

        $HesClient = new HesClient($this->testMode);
        $nonce = $HesClient->handshake($this->accountId, $this->accountPassword, $this->accountKey);
        $response = json_decode($HesClient->createUser($this->accountId, $nonce, $userDetails));
        Yii::log(json_encode($response), CLogger::LEVEL_ERROR);
        if (json_last_error() == JSON_ERROR_NONE && isset($response[0]->id)) {
            $this->OrderAssessment->test_user_id = $response[0]->id;
            $this->OrderAssessment->save(false);
        }else{
            $this->OrderAssessment->started_on = null;
            $this->OrderAssessment->save(false);
            echo 'We are unable to process your request. Please contact support team';
            Yii::app()->end();
        }
        
    }
    
    private function getOrderAssessment(){
        
        if($this->OrderAssessment === null){
            $this->OrderAssessment = OrderAssessment::model()
                ->find('unique_id = :h AND test_id = :id', [':h' => Yii::app()->request->getParam('h'), ':id' => $this->getTestId()]);
        }
        
        return $this->OrderAssessment;
    }
    
    
}
