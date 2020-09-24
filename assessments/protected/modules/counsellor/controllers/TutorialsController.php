<?php

class TutorialsController extends AbstractCounsellorController {

    use AjaxValidationTrait;

    public function actionIndex(){

        $model = new RequestDemo();
        $this->requestDemo();

        $this->render('index', compact('model'));
    }

    public function actionSuperCounsellor(){

        $Counsellor = $this->Counsellor;
        EmailMessage::getInstance()
            ->setSubject('Super Counsellor Lead')
            ->setHtml(Yii::app()->controller->renderFile(Yii::app()->basePath . '/views/emails/sc_lead.php', compact('Counsellor'), true))
            ->setTo('products@univariety.com', 'products@univariety.com')
            ->send();

        $this->redirect('https://www.globalcareercounsellor.com/supercounsellor');
    }

    private function requestDemo(){
        $model = new RequestDemo();
        if(Yii::app()->request->isPostRequest){
            $this->ajaxValidatePost($model);

            $model->setAttributes(Yii::app()->request->getParam(get_class($model)));
            if($model->validate() && $model->processDemo()){
                Yii::app()->user->setFlash('alert-success', "Thank you, we will contact you to schedule a demo.");
                Yii::app()->session->add('demo_requested', true);
                $this->refresh();
            }
        }
    }

}

class RequestDemo extends CFormModel{

    public $my_benifits;
    public $student_benifits;

    public function rules() {
        return [
            //['my_benifits, student_benifits', 'required'],
            ['my_benifits, student_benifits', 'length', 'min' => 5, 'max' => 1000],
        ];
    }

    public function attributeLabels() {
        return [
            'my_benifits' => 'Counsellor benifits',
            'student_benifits' => 'Student benifits'
        ];
    }

    public function processDemo(){


        return true;
    }

}