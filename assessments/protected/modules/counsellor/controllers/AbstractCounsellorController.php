<?php

abstract class AbstractCounsellorController extends AbstractMainController {

    public $layout = '//layouts/counsellor';

    /* @var Counsellor $Counsellor */
    protected $Counsellor;

    public function beforeAction($action) {
        parent::beforeAction($action);

        $ref = filter_input(INPUT_GET, 'ref');
        if($ref){
            $customer_id = OpenSslEncrypt::getInstance()->decrypt($ref);
            if($customer_id && $this->isSessionValid($customer_id)){
                $this->loadCounsellor($customer_id);
            }

            $this->redirect($this->createUrl('/counsellor/inventory'));
        }else{
            if(Yii::app()->session->get('customer_id')){
                $this->loadCounsellor(Yii::app()->session->get('customer_id'));
            }
        }

        if(!$this->Counsellor || !$this->isSessionValid(Yii::app()->session->get('customer_id'))){
            $this->redirect('http://' . Yii::app()->getRequest()->serverName);
        }

        return true;
    }

    private function loadCounsellor($customer_id){
        $this->Counsellor = Counsellor::model()->find('ID = :customer_id', [':customer_id' => $customer_id]);
        if($this->Counsellor){

            // $SiDi = Yii::app()->db->createCommand()
            //     ->select(['tsd.inst_website', 'MD5(lm.hash_key) AS hash_key'])
            //     ->from(UNI_DB . '.tbl_si_di tsd')
            //     ->join(UNI_DB . '.login_master lm', 'lm.login_id = tsd.login_ref_id AND lm.is_active = 1')
            //     ->where('tsd.inst_type = "SFE" AND tsd.is_active = "Y" AND lm.username = :email', [':email' => $this->Counsellor->email])
            //     ->queryRow();

            // if($SiDi){
            //     Yii::app()->session->add('dashboard_login', sprintf('%s/app/redirect/lwhc?h=%s&u=/', $SiDi['inst_website'], $SiDi['hash_key']));
            // }
            $customer_group_id = 2;
            $roles =   Yii::app()->db->createCommand()
            ->select(['um.meta_value'])
            ->from('unwp_usermeta um')
            ->where('um.meta_key = "unwp_capabilities" AND um.user_id = :ID', [':ID' =>$customer_id])
           ->queryRow();

           if(!empty($roles)){            
              $role_array = unserialize($roles['meta_value']);
                if(array_key_exists('counsellor', $role_array)){
                    $customer_group_id = 3;
                }
                 elseif(array_key_exists('super_counsellor', $role_array)){
                      $customer_group_id = 2;  
               }
               else {
                $customer_group_id = 2; 
               }    
           }

            Yii::app()->session->add('customer_id', $customer_id);
            Yii::app()->session->add('customer_group_id', $customer_group_id);
            Yii::app()->session->add('firstname', $this->Counsellor->display_name ? $this->Counsellor->display_name : 'Howdy!');
            Yii::app()->session->add('email', $this->Counsellor->user_email);
        }else{
            Yii::app()->session->add('customer_id', null);
            Yii::app()->session->add('customer_group_id', null);
            Yii::app()->session->add('firstname', null);
            Yii::app()->session->add('lastname', null);
            Yii::app()->session->add('email', null);
            Yii::app()->session->add('dashboard_login', null);
        }
    }

    /**
     * @param int $customer_id
     *
     * @return bool
     * @throws CException
     */
    private function isSessionValid($customer_id){

        // $max_session_time = ini_get('session.gc_maxlifetime');

        // $id = Yii::app()->db->createCommand()
        //     ->select(['co.customer_id'])
        //     ->from('oc_customer_online co')
        //     ->join('oc_customer c', 'c.customer_id = co.customer_id AND customer_group_id IN(2, 3) AND c.status = 1')
        //     ->where('co.customer_id = :customer_id AND co.date_added > (NOW() - INTERVAL :seconds SECOND)', [
        //         ':customer_id' => $customer_id,
        //         ':seconds' => 28800
        //     ])->queryScalar();

        // //return (int)$customer_id == (int)$id;
        return true;
    }

}
