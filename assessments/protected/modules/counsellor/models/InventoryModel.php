<?php

class InventoryModel extends CFormModel {

    public $order_id;
    public $product_id;
    public $assigned_name;
    public $assigned_email;
    public $assessment_status;
    public $counselling_status;
    public $date_added;

    public $date_from;
    public $date_to;

    public function rules() {
        return [
            ['order_id, product_id', 'numerical', 'integerOnly' => true],
            ['date_added, date_from, date_to', 'date', 'format' => 'Y-m-d'],
            ['assessment_status', 'in', 'range' => [1, 2]],
            ['counselling_status', 'in', 'range' => [1, 2]],
            ['assigned_name, assigned_email', 'safe'],
        ];
    }

    /* @var $Counsellor Counsellor */
    private $Counsellor;

    /**
     * @return Counsellor
     */
    public function getCounsellor(){
        return $this->Counsellor;
    }

    /**
     * @param Counsellor $Counsellor
     */
    public function setCounsellor(Counsellor $Counsellor) {
        $this->Counsellor = $Counsellor;
    }



    /**
     * @return mixed
     * @throws CException
     */
    public function getInventorySummary() {

        $products_brought = Yii::app()->db->createCommand()
            ->select(['p.post_title AS product_name', 'opl.product_id', 'SUM(opl.product_qty) AS qty', 'cl.user_id as customer_id',
                "(SELECT COUNT(DISTINCT CONCAT_WS('_', oa1.order_product_id, oa1.group_no)) FROM unwp_order_assessment  oa1 JOIN unwp_woocommerce_order_itemmeta oim ON oim.meta_key = 'oc_order_product_id' AND oim.meta_value = oa1.order_product_id JOIN unwp_wc_order_product_lookup opl1 ON opl1.order_item_id = oim.order_item_id join unwp_wc_order_stats os1 on os1.order_id = opl1.order_id WHERE opl1.customer_id = opl.customer_id AND opl1.product_id = opl.product_id AND oa1.assigned_name IS NULL AND os1.status = 'wc-completed') AS unused",
            ])
            ->from('unwp_wc_order_product_lookup opl')
            ->join('unwp_posts p', 'p.ID = opl.product_id')
            ->join('unwp_wc_order_stats os', 'os.order_id = opl.order_id')
            ->join('unwp_wc_customer_lookup cl', 'cl.customer_id = opl.customer_id')
            ->where("os.status = 'wc-completed' AND cl.user_id = :customer_id", [':customer_id' => $this->Counsellor->ID])
            ->group('opl.product_id')
            ->queryAll();

        $mrp = 0; $paid = 0;
        foreach($products_brought as $product){
            $mrp += Yii::app()->db->createCommand("(SELECT SUM(b.totals) FROM(SELECT COUNT(DISTINCT CONCAT_WS('_', oa.order_product_id, oa.group_no))*pm.meta_value AS totals FROM unwp_wc_order_product_lookup opl JOIN unwp_woocommerce_order_itemmeta oim ON oim.order_item_id = opl.order_item_id AND oim.meta_key = 'oc_order_product_id' JOIN unwp_order_assessment oa ON oa.order_product_id = oim.meta_value JOIN unwp_wc_order_stats os ON os.order_id = opl.order_id JOIN unwp_postmeta pm ON pm.post_id = opl.product_id AND pm.meta_key = '_regular_price' JOIN unwp_wc_customer_lookup cl on cl.customer_id = opl.customer_id WHERE cl.user_id = :customer_id AND opl.product_id = :product_id AND os.status = 'wc-completed' AND oa.assigned_name IS NULL GROUP BY pm.meta_value) b)")
                ->queryScalar([':customer_id' => $product['customer_id'], ':product_id' => $product['product_id']]);

            $paid += Yii::app()->db->createCommand("(SELECT SUM(b.totals) FROM(SELECT COUNT(DISTINCT CONCAT_WS('_', oa.order_product_id, oa.group_no))*opl.product_gross_revenue AS totals FROM unwp_wc_order_product_lookup opl JOIN unwp_woocommerce_order_itemmeta oim ON oim.order_item_id = opl.order_item_id AND oim.meta_key = 'oc_order_product_id' JOIN unwp_order_assessment oa ON oa.order_product_id = oim.meta_value JOIN unwp_wc_order_stats os ON os.order_id = opl.order_id JOIN unwp_wc_customer_lookup cl on cl.customer_id = opl.customer_id WHERE cl.user_id= :customer_id AND opl.product_id = :product_id AND os.status = 'wc-completed' AND oa.assigned_name IS NULL GROUP BY opl.product_gross_revenue) b)")
                ->queryScalar([':customer_id' => $product['customer_id'], ':product_id' => $product['product_id']]);
        }

        return ['mrp' => $mrp, 'paid' => $paid];
    }

    /**
     * @return CSqlDataProvider
     * @throws CException
     */
    public function getInventoryDetails() {

        $command = Yii::app()->db->createCommand()
            ->select(['p.post_title AS product_name', 'opl.product_id', 'SUM(opl.product_qty) AS qty', 'cl.user_id as customer_id',
                "(SELECT COUNT(DISTINCT CONCAT_WS('_', oa1.order_product_id, oa1.group_no)) FROM unwp_order_assessment oa1 JOIN unwp_woocommerce_order_itemmeta oim ON oim.meta_key = 'oc_order_product_id' AND oim.meta_value = oa1.order_product_id JOIN unwp_wc_order_product_lookup opl1 ON opl1.order_item_id = oim.order_item_id JOIN unwp_wc_order_stats os1 on os1.order_id = opl1.order_id WHERE opl1.customer_id = opl.customer_id AND opl1.product_id = opl.product_id AND oa1.assigned_name IS NULL AND os1.status = 'wc-completed') AS unused",
            ])
            ->from('unwp_wc_order_product_lookup opl')
            ->join('unwp_posts p', 'p.ID = opl.product_id')
            ->join('unwp_wc_order_stats os', 'os.order_id = opl.order_id')
            ->join('unwp_wc_customer_lookup cl', 'cl.customer_id = opl.customer_id')
            ->where("os.status = 'wc-completed' AND cl.user_id = :customer_id",[':customer_id' => $this->Counsellor->ID]);

        if($this->product_id){
            $command->andWhere('opl.product_id = :product_id', [':product_id' => $this->product_id]);
        }
        
        /* @var CDbCommand $totalCommand */
        $totalCommand = clone $command;

        $totalCommand->select(['COUNT(DISTINCT opl.product_id)']);
        $command->group('opl.product_id');

        return new CSqlDataProvider($command, [
            'keyField'=>'product_name',
            'totalItemCount' => $totalCommand->queryScalar(),
            'sort' => [
                'attributes' => ['product_name', 'mrp', 'paid', 'qty', 'unused', 'profit']
            ],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
    }

    public function getStudentSummary() {

        $Command = Yii::app()->db->createCommand()
            ->select(['oa.order_product_id', 'oa.group_no', 'oa.assessment_name',
                'oa.order_assessment_id', 'oa.assigned_name', 'oa.assigned_email', 'p.post_title AS product_name', 'oa.test_user_email', 'oa.test_user_name',
                'oa.test_link', 'oa.added_on', 'oa.modified_on', 'oa.started_on', 'oa.completed_on', 'oa.counselling',
            ])
            ->from('unwp_wc_order_product_lookup opl')
            ->join('unwp_woocommerce_order_itemmeta oim', "oim.order_item_id = opl.order_item_id AND oim.meta_key = 'oc_order_product_id'")
            ->join('unwp_order_assessment oa', 'oa.order_product_id = oim.meta_value AND oa.assigned_name IS NOT NULL AND oa.assigned_email IS NOT NULL')
            ->join('unwp_wc_order_stats os', 'os.order_id = opl.order_id')
            ->join('unwp_posts p', 'p.ID = opl.product_id')
            ->join('unwp_wc_customer_lookup cl', 'cl.customer_id = opl.customer_id')
            ->where("os.status = 'wc-completed' AND cl.user_id = :customer_id", [':customer_id' => $this->Counsellor->ID]);

        if($this->product_id){
            $Command->andWhere('opl.product_id = :product_id', [':product_id' => $this->product_id]);
        }

        if($this->assessment_status){
            if($this->assessment_status == 1){
                $Command->andWhere('oa.test_user_email IS NOT NULL OR oa.test_user_name IS NOT NULL OR oa.started_on IS NOT NULL');
            }

            if($this->assessment_status == 2){
                $Command->andWhere('oa.test_user_email IS NULL AND oa.test_user_name IS NULL AND oa.started_on IS NULL');
            }
        }

        if($this->counselling_status){
            if($this->counselling_status == 1){
                $Command->andWhere('oa.counselling = 1');
            }

            if($this->counselling_status == 2){
                $Command->andWhere('oa.counselling = 0 OR oa.counselling IS NULL');
            }
        }

        if($this->assigned_name){
            $Command->andWhere(['like', 'oa.assigned_name', '%' . $this->assigned_name . '%']);
        }

        if($this->assigned_email){
            $Command->andWhere(['like', 'oa.assigned_email', '%' . $this->assigned_email . '%']);
        }


        /* @var CDbCommand $totalCommand */
        $totalCommand = clone $Command;

        $totalCommand->select(['COUNT(DISTINCT oa.order_assessment_id)']);
        $Command->group('oa.order_assessment_id');

        if(!Yii::app()->request->getParam('sort')){
            $Command->order('CONCAT_WS("_", oa.order_product_id, oa.group_no)');
        }

        return new CSqlDataProvider($Command, [
            'keyField'=>'assigned_name',
            'totalItemCount' => $totalCommand->queryScalar(),
            'sort' => [
                'attributes' => ['assigned_name', 'assigned_email', 'product_name', 'counselling']
            ],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
    }

    public function getOrderHistory(){
        $command = Yii::app()->db->createCommand()
            ->select(['pm_o.meta_value as order_id', 'opl.product_id', 'p.post_title as product_name', 'opl.product_qty as quantity',
                '(opl.product_qty * pmp.meta_value) AS mrp', '(opl.product_qty * opl.product_net_revenue) AS paid',
                '((opl.product_qty * pmp.meta_value) - (opl.product_qty * opl.product_net_revenue)) AS discount', 'pm_pd.meta_value as date_added'
            ])
            ->from('unwp_wc_order_product_lookup opl')
            ->join('unwp_posts p', 'p.ID = opl.product_id')
            ->join('unwp_wc_order_stats os', 'os.order_id = opl.order_id')
            ->join('unwp_wc_customer_lookup cl', 'cl.customer_id = opl.customer_id')
            ->leftJoin('unwp_postmeta pmp', "pmp.post_id = opl.product_id AND pmp.meta_key = '_regular_price'")
            ->leftJoin('unwp_postmeta pm_o', "pm_o.post_id = opl.order_id AND pm_o.meta_key = '_order_number'")
            ->leftJoin('unwp_postmeta pm_pd', "pm_pd.post_id = opl.order_id AND pm_pd.meta_key = '_paid_date'")
            ->where("os.status='wc-completed' AND cl.user_id = :customer_id", [':customer_id' => $this->Counsellor->ID]);

        if($this->product_id){
            $command->andWhere('opl.product_id = :product_id', [':product_id' => $this->product_id]);
        }

        if($this->date_from || $this->date_to){
            if($this->date_from && $this->date_to){
                $command->andWhere('DATE(o.date_added) BETWEEN :from AND :to', [
                    ':from' => date('Y-m-d', strtotime($this->date_from)),
                    ':to' => date('Y-m-d', strtotime($this->date_to)),
                ]);
            }else{
                if($this->date_from){
                    $command->andWhere('DATE(o.date_added) >= :from', [':from' => date('Y-m-d', strtotime($this->date_from))]);
                }

                if($this->date_to){
                    $command->andWhere('DATE(o.date_added) <= :to', [':to' => date('Y-m-d', strtotime($this->date_to))]);
                }
            }
        }

        /* @var CDbCommand $totalCommand */
        $totalCommand = clone $command;

        $command->group('opl.order_item_id');
        $totalCommand->select(['COUNT(DISTINCT opl.order_item_id)']);

        return new CSqlDataProvider($command, [
            'keyField'=>'order_id',
            'totalItemCount' => $totalCommand->queryScalar(),
            'sort' => [
                'attributes' => ['order_id', 'product_name', 'quantity', 'mrp', 'paid', 'discount', 'date_added']
            ],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
    }

    public function getOrderHistoryAllProducts(){
        $Command = Yii::app()->db->createCommand()
            ->select(['SUM(opl.product_qty * pmp.meta_value) AS mrp', 'SUM(opl.product_net_revenue * opl.product_qty) AS paid'])
            ->from('unwp_wc_order_product_lookup opl')
            ->join('unwp_postmeta pmp', "pmp.post_id = opl.product_id AND pmp.meta_key = '_regular_price'")
            ->join('unwp_wc_order_stats os', "os.order_id = opl.order_id")
            ->join('unwp_wc_customer_lookup cl', "cl.customer_id = opl.customer_id")
            ->where("os.status='wc-completed' AND cl.user_id= :customer_id", [':customer_id' => $this->Counsellor->ID]);

        if($this->date_from || $this->date_to){
            if($this->date_from && $this->date_to){
                $Command->andWhere('DATE(o.date_added) BETWEEN :from AND :to', [
                    ':from' => date('Y-m-d', strtotime($this->date_from)),
                    ':to' => date('Y-m-d', strtotime($this->date_to)),
                ]);
            }else{
                if($this->date_from){
                    $Command->andWhere('DATE(o.date_added) >= :from', [':from' => date('Y-m-d', strtotime($this->date_from))]);
                }

                if($this->date_to){
                    $Command->andWhere('DATE(o.date_added) <= :to', [':to' => date('Y-m-d', strtotime($this->date_to))]);
                }
            }
        }

        return $Command->queryRow();
    }

    public function getOrderHistoryAllowcatedProducts(){

        $Command = Yii::app()->db->createCommand()
            ->select(['p.post_title AS product_name', 'opl.product_id', 'SUM(opl.product_qty) AS qty', 'cl.user_id as customer_id',
                "(SELECT COUNT(DISTINCT CONCAT_WS('_', oa1.order_product_id, oa1.group_no)) FROM unwp_order_assessment oa1 JOIN unwp_woocommerce_order_itemmeta oim ON oim.meta_key = 'oc_order_product_id' AND oim.meta_value = oa1.order_product_id JOIN unwp_wc_order_product_lookup opl1 ON opl1.order_item_id = oim.order_item_id JOIN unwp_wc_order_stats os1 on os1.order_id = opl1.order_id WHERE opl1.customer_id = opl.customer_id AND opl1.product_id = opl.product_id AND oa1.assigned_name IS NOT NULL AND os1.status = 'wc-completed') AS unused",
            ])
            ->from('unwp_wc_order_product_lookup opl')
            ->join('unwp_posts p', 'p.ID = opl.product_id')
            ->join('unwp_wc_order_stats os', 'os.order_id = opl.order_id')
            ->join('unwp_wc_customer_lookup cl', 'cl.customer_id = opl.customer_id')
            ->where("os.status = 'wc-completed' AND cl.user_id = :customer_id", [':customer_id' => $this->Counsellor->ID]);

        if($this->date_from || $this->date_to){
            if($this->date_from && $this->date_to){
                $Command->andWhere('DATE(o.date_added) BETWEEN :from AND :to', [
                    ':from' => date('Y-m-d', strtotime($this->date_from)),
                    ':to' => date('Y-m-d', strtotime($this->date_to)),
                ]);
            }else{
                if($this->date_from){
                    $Command->andWhere('DATE(o.date_added) >= :from', [':from' => date('Y-m-d', strtotime($this->date_from))]);
                }

                if($this->date_to){
                    $Command->andWhere('DATE(o.date_added) <= :to', [':to' => date('Y-m-d', strtotime($this->date_to))]);
                }
            }
        }

        $products_brought = $Command->group('opl.product_id')
            ->queryAll();

        $mrp = 0; $paid = 0;
        foreach($products_brought as $product){
            $mrp += Yii::app()->db->createCommand("(SELECT SUM(b.totals) FROM(SELECT COUNT(DISTINCT CONCAT_WS('_', oa.order_product_id, oa.group_no))*pm.meta_value AS totals FROM unwp_wc_order_product_lookup opl JOIN unwp_woocommerce_order_itemmeta oim ON oim.order_item_id = opl.order_item_id AND oim.meta_key = 'oc_order_product_id' JOIN unwp_order_assessment oa ON oa.order_product_id = oim.meta_value JOIN unwp_wc_order_stats os ON os.order_id = opl.order_id JOIN unwp_postmeta pm ON pm.post_id = opl.product_id AND pm.meta_key = '_regular_price' JOIN unwp_wc_customer_lookup cl on cl.customer_id = opl.customer_id WHERE cl.user_id = :customer_id AND opl.product_id = :product_id AND os.status = 'wc-completed' AND oa.assigned_name IS NOT NULL GROUP BY pm.meta_value) b)")
                ->queryScalar([':customer_id' => $product['customer_id'], ':product_id' => $product['product_id']]);

            $paid += Yii::app()->db->createCommand("(SELECT SUM(b.totals) FROM(SELECT COUNT(DISTINCT CONCAT_WS('_', oa.order_product_id, oa.group_no))*opl.product_gross_revenue AS totals FROM unwp_wc_order_product_lookup opl JOIN unwp_woocommerce_order_itemmeta oim ON oim.order_item_id = opl.order_item_id AND oim.meta_key = 'oc_order_product_id' JOIN unwp_order_assessment oa ON oa.order_product_id = oim.meta_value JOIN unwp_wc_order_stats os ON os.order_id = opl.order_id JOIN unwp_wc_customer_lookup cl on cl.customer_id = opl.customer_id WHERE cl.user_id= :customer_id AND opl.product_id = :product_id AND os.status = 'wc-completed' AND oa.assigned_name IS NOT NULL GROUP BY opl.product_gross_revenue) b)")
                ->queryScalar([':customer_id' => $product['customer_id'], ':product_id' => $product['product_id']]);
        }

        return ['mrp' => $mrp, 'paid' => $paid];
    }

    public function getDistinctProducts(){
        $results = Yii::app()->db->createCommand()
            ->select(['opl.product_id as id', 'p.post_title as label'])
            ->from('unwp_wc_order_product_lookup opl')
            ->join('unwp_posts p', 'p.ID = opl.product_id')
            ->join('unwp_wc_order_stats os', 'os.order_id = opl.order_id')
            ->join('unwp_wc_customer_lookup cl', 'cl.customer_id = opl.customer_id')
            ->where("os.status='wc-completed' AND cl.user_id = :customer_id", [':customer_id' => $this->Counsellor->ID])
            ->group('label')
            ->order('label')
            ->queryAll();

        return CHtml::listData($results, 'id', 'label');
    }

    public function getDistinctUsedProducts(){
        $results = Yii::app()->db->createCommand()
            ->select(['opl.product_id AS id', 'p.post_title AS label'])
            ->from('unwp_wc_order_product_lookup opl')
            ->join('unwp_posts p', 'p.ID = opl.product_id')
            ->join('unwp_wc_order_stats os', 'os.order_id = opl.order_id')
            ->join('unwp_woocommerce_order_itemmeta oim', "oim.order_item_id = opl.order_item_id AND oim.meta_key = 'oc_order_product_id'")
            ->join('unwp_order_assessment oa', "oa.order_product_id = oim.meta_value AND oa.assigned_name IS NOT NULL AND oa.assigned_email IS NOT NULL")
            ->join('unwp_wc_customer_lookup cl', "cl.customer_id = opl.customer_id")
            ->where("os.status = 'wc-completed' AND cl.user_id = :customer_id", [':customer_id' => $this->Counsellor->ID])
            ->order('label')
            ->queryAll();

        return CHtml::listData($results, 'id', 'label');
    }

}
