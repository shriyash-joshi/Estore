<?php

class ChooseAssessmentController extends AbstractMainController {
    
    private $OrderAssessment;
    
    use AjaxValidationTrait;
    
    public function beforeAction($action) {
        parent::beforeAction($action);
        
        $this->OrderAssessment = OrderAssessment::model()
            ->find('unique_id = :h', [':h' => Yii::app()->request->getParam('h')]);
        
        if(!$this->OrderAssessment){
            throw new CHttpException(404);
        }
        
        if($this->OrderAssessment->test_id){
            
            switch($this->OrderAssessment->test_id){
                
                case ChooseAssessmentForm::TEST_COMMERCE:
                    $this->redirect($this->createUrl('/commerce?h=' . $this->OrderAssessment->unique_id));
                    
                case ChooseAssessmentForm::TEST_ENGINEERING:
                    $this->redirect($this->createUrl('/engineering?h=' . $this->OrderAssessment->unique_id));
                    
                case ChooseAssessmentForm::TEST_HUMANITIES:
                    $this->redirect($this->createUrl('/humanities?h=' . $this->OrderAssessment->unique_id));
                    
                case ChooseAssessmentForm::TEST_LEARNING_STYLE:
                    $this->redirect($this->createUrl('/learningStyle?h=' . $this->OrderAssessment->unique_id));
                    
                case ChooseAssessmentForm::TEST_MULTI_INTELLEGENCE:
                    $this->redirect($this->createUrl('/multiIntelligence?h=' . $this->OrderAssessment->unique_id));
                    
                case ChooseAssessmentForm::TEST_PERSONALITY_STYLE:
                    $this->redirect($this->createUrl('/personalityStyle?h=' . $this->OrderAssessment->unique_id));
                    
                case ChooseAssessmentForm::TEST_PSYCHOMETRIC:
                    $this->redirect($this->createUrl('/idealCareer?h=' . $this->OrderAssessment->unique_id));
                    
                case ChooseAssessmentForm::TEST_STREAM_SELECTOR:
                    $this->redirect($this->createUrl('/streamSelector?h=' . $this->OrderAssessment->unique_id));
                
                default:
                    throw new CHttpException(404);
            }
        }
        
        return true;
    }
    
    public function actionIndex(){
        $model = new ChooseAssessmentForm;
        
        if(Yii::app()->request->isPostRequest){
            $this->ajaxValidatePost($model);
            $model->setAttributes(Yii::app()->request->getParam(get_class($model)));
            
            if($model->validate()){
                $model->updateAssessment($this->OrderAssessment);
                $this->refresh();
            }
        }
        
        $this->render('index', compact('model'));
    }
    
}
