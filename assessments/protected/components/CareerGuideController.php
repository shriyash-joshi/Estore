<?php

abstract class CareerGuideController extends AbstractMainController {
    
    use AjaxValidationTrait;
    
    protected $OrderAssessment;

    private $desUrl = 'http://www.univariety.meracareerguide.com/getencryptedvalue.aspx?e=%s';
    private $testUrl = 'http://www.univariety.meracareerguide.com/auth-asmnt.aspx?n=%s&e=%s&t=%s';
    
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
        
        return true;
    }
    
    public function actionTakeTest() {
        $this->header = false;
        
        if(!$this->OrderAssessment->test_user_email){
            $this->redirect($this->createUrl('personalInfo', filter_input_array(INPUT_GET) ? filter_input_array(INPUT_GET) : []));
        }
        
        $testUrl = $this->getTestUrl($this->getTestId());
        $testUrl = 'https://www.univariety.com/app/redirect/pageRedirect?u='.urlencode($testUrl);
        
        $this->render('//site/take_test', compact('testUrl'));
    }
    
    public function actionPersonalInfo() {
        $this->header = false;
        
        if($this->OrderAssessment->test_user_email){
            $this->redirect($this->createUrl('takeTest', filter_input_array(INPUT_GET) ? filter_input_array(INPUT_GET) : []));
        }
        
        $model = new PersonalInfoForm();
        
        if(Yii::app()->request->isPostRequest){
            $this->ajaxValidatePost($model);
            
            $model->setAttributes(Yii::app()->request->getParam(get_class($model)), true);
            if($model->validate() && $model->updatePersonalInfo($this->OrderAssessment)){
                $this->redirect($this->createUrl('takeTest', filter_input_array(INPUT_GET) ? filter_input_array(INPUT_GET) : []));
            }
        }
        
        $this->render('//site/persional_info', compact('model'));
    }
    
    private function getTestUrl($test_id){
        $nameDes = $this->tripleDes($this->OrderAssessment->test_user_name);
        $emailDes = $this->tripleDes($this->OrderAssessment->test_user_email);
        $testDes = $this->tripleDes($test_id);
        
        return sprintf($this->testUrl, $nameDes, $emailDes, $testDes);
    }
    
    private function tripleDes($string){
        return $this->curlGetRequest(sprintf($this->desUrl, urlencode($string)));
    }
    
    private function curlGetRequest($url){
        $curl = new HttpCurl();
        return $curl->get($url, [
            CURLOPT_REFERER => 'https://www.univariety.com',
            CURLOPT_TIMEOUT => 120,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true,
        ]);
    }
    
    private function getOrderAssessment(){
        
        if($this->OrderAssessment === null){
            $this->OrderAssessment = OrderAssessment::model()
                ->find('unique_id = :h AND test_id = :id', [':h' => Yii::app()->request->getParam('h'), ':id' => $this->getTestId()]);
        }
        
        return $this->OrderAssessment;
    }
    
}
