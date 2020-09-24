<?php

class InventoryController extends AbstractCounsellorController {

    use AjaxValidationTrait;

    public function actionIndex(){

        $model = new InventoryModel();
        $model->setCounsellor($this->Counsellor);
        $model->setAttributes(Yii::app()->request->getParam(get_class($model)), true);

        $AllowcateForm = new AllowcateForm();

        if(Yii::app()->request->isPostRequest){
            $AllowcateForm->setAttributes(Yii::app()->request->getParam(get_class($AllowcateForm)));
            if($AllowcateForm->validate() && $AllowcateForm->allocate($this->Counsellor)){
                echo json_encode([]);
                Yii::app()->end();
            }else{
                $this->ajaxValidatePost($AllowcateForm);
            }
        }

        $this->render('index', compact('model', 'AllowcateForm'));
    }

    public function actionStudents(){
        $model = new InventoryModel();
        $model->setCounsellor($this->Counsellor);
        $model->setAttributes(Yii::app()->request->getParam(get_class($model)), true);

        $this->render('students', compact('model'));
    }

    public function actionHistory(){
        $model = new InventoryModel();
        $model->setCounsellor($this->Counsellor);
        $model->setAttributes(Yii::app()->request->getParam(get_class($model)), true);

        if(Yii::app()->request->isPostRequest){
            $model->date_from = filter_input(INPUT_POST, 'date_from');
            $model->date_to = filter_input(INPUT_POST, 'date_to');

            if(filter_input(INPUT_POST, 'type') == 'all'){

                $all_products = $model->getOrderHistoryAllProducts();
                $data = [];
                if($all_products){
                    $data['mrp'] = '₹' . Yii::app()->numberFormatter->format('#,##,##,###', $all_products['mrp']);
                    $data['paid'] = '₹' . Yii::app()->numberFormatter->format('#,##,##,###', $all_products['paid']);
                    $data['profit'] = '₹' . Yii::app()->numberFormatter->format('#,##,##,###', ($all_products['mrp'] - $all_products['paid']));
                }
            }

            if(filter_input(INPUT_POST, 'type') == 'allowcated'){

                $all_products = $model->getOrderHistoryAllowcatedProducts();
                $data = [];
                if($all_products){
                    $data['mrp'] = '₹' . Yii::app()->numberFormatter->format('#,##,##,###', $all_products['mrp']);
                    $data['paid'] = '₹' . Yii::app()->numberFormatter->format('#,##,##,###', $all_products['paid']);
                    $data['profit'] = '₹' . Yii::app()->numberFormatter->format('#,##,##,###', ($all_products['mrp'] - $all_products['paid']));
                }
            }

            echo json_encode($data);
            Yii::app()->end();
        }

        $this->render('history', compact('model'));
    }

    public function actionCounsellingStatus(){
        if(Yii::app()->request->isPostRequest){
            $id = OpenSslEncrypt::getInstance()->decrypt(filter_input(INPUT_POST, 'id'));

            if($id){
                /* @var $OrderAssessment OrderAssessment */
                $OrderAssessment = OrderAssessment::model()->findByPk($id);
                if($OrderAssessment && $OrderAssessment->completed_on){
                    $OrderAssessment->counselling = 1 - $OrderAssessment->counselling;
                    $OrderAssessment->save(false);
                }
            }
        }
    }

    public function actionPullBack(){
        if(Yii::app()->request->isPostRequest){
            $id = OpenSslEncrypt::getInstance()->decrypt(filter_input(INPUT_POST, 'id'));
            if($id){
                /* @var $OrderAssessment OrderAssessment */
                $OrderAssessment = OrderAssessment::model()->find('order_assessment_id = :id AND test_user_email IS NULL AND test_user_name IS NULL AND started_on IS NULL', [':id' => $id]);
                if($OrderAssessment){

                    $OrderAssessments = OrderAssessment::model()->findAll('order_product_id = :id AND group_no = :gno', [
                        ':id' => $OrderAssessment->order_product_id,
                        ':gno' => $OrderAssessment->group_no
                    ]);

                    /* @var $OrderAssessments OrderAssessment[] */

                    foreach($OrderAssessments as $assessment){
                        if($OrderAssessment->test_user_name || $OrderAssessment->test_user_name || $OrderAssessment->started_on){
                            Yii::app()->end();
                        }
                    }

                    foreach($OrderAssessments as $assessment){
                        $assessment->assigned_email = null;
                        $assessment->assigned_name = null;
                        $assessment->test_user_name = null;
                        $assessment->test_user_email = null;

                        $new_uuid = md5(StringUtils::uuid());
                        $old_uuid = $assessment->unique_id;
                        $assessment->test_link = str_replace($old_uuid, $new_uuid, $assessment->test_link);
                        $assessment->unique_id = $new_uuid;
                        $assessment->save(false);

                        $Survey = Survey::model()->find('unique_id = :id', [':id' => $old_uuid]);
                        if($Survey){
                            $Survey->unique_id = $assessment->unique_id;
                            $Survey->save(false);
                        }

                    }
                }
            }
        }
    }

    public function actionFindStudents(){
        $query = filter_input(INPUT_GET, 'term');

        $data = [];
        if($query){
            $inst_id = Yii::app()->db->createCommand()
                ->select(['tsd.inst_id'])
                ->from(UNI_DB . '.tbl_si_di tsd')
                ->join(UNI_DB . '.login_master lm', 'lm.login_id = tsd.login_ref_id')
                ->where('lm.username = :email', [':email' => $this->Counsellor->email])
                ->queryScalar();

            if($inst_id){
                $Students = Yii::app()->db->createCommand()
                    ->select(['lm.username', 'CONCAT_WS(" ", um.first_name, um.last_name) as name'])
                    ->from(UNI_DB . '.login_master lm')
                    ->join(UNI_DB . '.tbl_user_master um', 'um.login_ref_id = lm.login_id AND um.is_test = "N"')
                    ->join(UNI_DB . '.tbl_si_students tss', 'tss.user_ref_id = um.user_id')
                    ->where('lm.is_active = 1 AND tss.inst_ref_id = :inst_id', [':inst_id' => $inst_id])
                    ->andWhere(['like', 'lm.username', "%$query%"])
                    ->limit(10)
                    ->queryAll();

                foreach($Students as $Student){
                    array_push($data, ['id' => $Student['username'], 'label' => $Student['username'], 'name' => $Student['name']]);
                }
            }
        }

        echo json_encode($data);
        Yii::app()->end();
    }

    public function actionLogout(){

        Yii::app()->session->add('customer_id', null);
        Yii::app()->session->add('customer_group_id', null);
        Yii::app()->session->add('firstname', null);
        Yii::app()->session->add('lastname', null);
        Yii::app()->session->add('email', null);
        Yii::app()->session->add('dashboard_login', null);

        $this->redirect('https://' . Yii::app()->getRequest()->serverName . '/index.php?route=account/logout');
    }
}

class AllowcateForm extends CFormModel {

    public $id;
    public $name;
    public $email;

    public function rules(){
        return [
            ['id, name, email', 'required'],
            ['name, email', 'length', 'min' => 3, 'max' => 150],
            ['id', 'length', 'min' => 1],
            ['email', 'email']
        ];
    }

    public function attributeLabels() {
        return [
            'name' => 'Student name',
            'email' => 'Student email'
        ];
    }

    public function allocate(Counsellor $Counsellor){
        $product_id = OpenSslEncrypt::getInstance()
            ->decrypt($this->id);

        if($product_id){
            $result = Yii::app()->db->createCommand()
                ->select(['oa.group_no', 'oa.order_product_id', 'p.post_title AS product_name'])
                ->from('unwp_order_assessment oa')
                ->join('unwp_posts p', 'p.ID = oa.product_id')
                ->join('unwp_woocommerce_order_itemmeta oim', "oim.meta_key = 'oc_order_product_id' AND oim.meta_value = oa.order_product_id")
                ->join('unwp_wc_order_product_lookup opl', "opl.order_item_id = oim.order_item_id")
                ->join('unwp_wc_order_stats os', "os.order_id = opl.order_id")
                ->join('unwp_wc_customer_lookup cl', "cl.customer_id = opl.customer_id")
                ->where("os.status='wc-completed' AND cl.user_id = :customer_id", [':customer_id' => $Counsellor->ID])
                ->andWhere('opl.product_id = :product_id', [':product_id' => $product_id])
                ->andWhere('oa.assigned_name IS NULL AND oa.assigned_email IS NULL AND oa.test_user_email IS NULL AND oa.test_user_name IS NULL AND oa.started_on IS NULL')
                ->queryRow();

            if($result){

                $OrderAssessments = OrderAssessment::model()->findAll('order_product_id = :id AND group_no = :gno', [
                    ':id' => $result['order_product_id'],
                    ':gno' => $result['group_no']
                ]);

                if($OrderAssessments){
                    foreach($OrderAssessments as $OrderAssessment){
                        if($OrderAssessment && !$OrderAssessment->assigned_name && !$OrderAssessment->assigned_email){
                            $OrderAssessment->assigned_email = $this->email;
                            $OrderAssessment->assigned_name = $this->name;
                            $OrderAssessment->save(false);
                        }
                    }

                    $product_name = $result['product_name'];
                    $model = $this;
                    EmailMessage::getInstance()
                        ->setSubject('Career counselling product - ' . $product_name)
                        ->setHtml(Yii::app()->controller->renderFile(Yii::app()->basePath . '/views/emails/allowcate.php', compact('model',
                            'OrderAssessments'), true))
                        ->setTo($this->email, $this->name)
                        ->send();

                    return true;
                }

            }
        }

        return false;
    }
}