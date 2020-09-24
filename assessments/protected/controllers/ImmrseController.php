<?php

class ImmrseController extends AbstractMainController {

    use AjaxValidationTrait;

    public $OrderAssessment;
    public $OrderProductSKU;

    public function beforeAction($action) {
        parent::beforeAction($action);

        if (Yii::app()->getRequest()->isSecureConnection) {
            $url = 'http://' . Yii::app()->getRequest()->serverName . Yii::app()->getRequest()->requestUri;
            Yii::app()->request->redirect($url);
        }

        $this->getOrderAssessment();

        return true;
    }

    public function actionIndex() {

        if (!$this->OrderAssessment->test_id) {
            $this->redirect($this->createUrl('program', filter_input_array(INPUT_GET) ? filter_input_array(INPUT_GET) : []));
        } else {
            $course = $this->OrderAssessment->test_id;
            $this->render('index', compact('course'));
        }
    }

    public function actionProgram() {
        $model = new ChooseProgramForm();
        if ($this->OrderAssessment->test_id) {
            $model->assessment_name = $this->OrderAssessment->test_id;
        }
        
        if ($this->OrderAssessment->test_user_id) {
            $this->redirect($this->createUrl('index', filter_input_array(INPUT_GET) ? filter_input_array(INPUT_GET) : []));
        }
        
        if (Yii::app()->request->isPostRequest) {
            $this->ajaxValidatePost($model);
            $model->setAttributes(Yii::app()->request->getParam(get_class($model)));

            if ($model->validate()) {
                $model->updateAssessment($this->OrderAssessment);
                
                $this->redirect($this->createUrl('index', filter_input_array(INPUT_GET) ? filter_input_array(INPUT_GET) : []));
            }
        }

        $this->render('choose_program', compact('model'));
    }

    public function actionTakeTest() {
        $this->header = false;

        if (!$this->OrderAssessment->test_user_name) {
            $this->redirect($this->createUrl('personalInfo', filter_input_array(INPUT_GET) ? filter_input_array(INPUT_GET) : []));
        }

        $testUrl = $this->getTestUrl($this->OrderAssessment->test_id);

        $this->render('//site/take_immrse', compact('testUrl'));
    }

    private function getTestUrl($test_id) {
        $nameParts = $this->separateFirstAndLastName($this->OrderAssessment->test_user_name);

        $obj = new Immrse();
        $obj->first_name = $nameParts['first_name'];
        $obj->last_name = isset($nameParts['last_name']) ? $nameParts['last_name'] : '';
        $obj->email = $this->OrderAssessment->test_user_email;
        $res = $obj->signup();

        if ($res['success'] == 1) {
            $studentPin = $res['data']['student']['pin'];

            $this->OrderAssessment->test_user_id = $studentPin;
            $this->OrderAssessment->save(false);

            $obj->studentPin = $studentPin;
            $obj->career = $test_id;
            $url = $obj->getEncryptionCareer();

            if ($url) {
                return $url;
            } else {
                echo 'We are unable to process your request. Please contact support team1';
                Yii::app()->end();
            }
        } else {
            echo 'We are unable to process your request. Please contact support team';
            Yii::app()->end();
        }
    }

    public function actionPersonalInfo() {
        $this->header = false;

        if ($this->OrderAssessment->test_user_name) {
            $this->redirect($this->createUrl('takeTest', filter_input_array(INPUT_GET) ? filter_input_array(INPUT_GET) : []));
        }

        $model = new PersonalInfoForm();

        if (Yii::app()->request->isPostRequest) {
            $this->ajaxValidatePost($model);

            $model->setAttributes(Yii::app()->request->getParam(get_class($model)), true);
            if ($model->validate() && $model->updatePersonalInfo($this->OrderAssessment)) {
                $this->redirect($this->createUrl('takeTest', filter_input_array(INPUT_GET) ? filter_input_array(INPUT_GET) : []));
            }
        }

        $this->render('//site/persional_info', compact('model'));
    }

    private function getOrderAssessment() {

        if ($this->OrderAssessment === null) {
            $this->OrderAssessment = OrderAssessment::model()
                    ->find('unique_id = :h ', [':h' => Yii::app()->request->getParam('h')]);
        }

        if (!$this->OrderAssessment) {
            throw new CHttpException(404);
        }

        $this->OrderProductSKU = Yii::app()->db->createCommand("select sku FROM oc_product WHERE product_id = ".$this->OrderAssessment->product_id)->queryScalar();

        return $this->OrderAssessment;
    }

    private function separateFirstAndLastName($fullName) {
        $name = array();
        $stringToSplit = trim($fullName);
        $split = explode(
                ' ', implode(
                        ' ', explode(
                                ".", $stringToSplit
                        )
                )
        );

        if (count($split) == 1) {
            $name['first_name'] = trim($split[0]);
        } else {
            $name['last_name'] = end($split); // taking last word as last name
            array_pop($split);
            $name['first_name'] = trim(implode(" ", $split)); // taking except last word as first name
        }
        return $name;
    }

}
