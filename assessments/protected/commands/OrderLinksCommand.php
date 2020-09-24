<?php 

require_once('../config.php');
include_once(Yii::app()->basePath . '/commands/UniCommand.php');

class OrderLinksCommand extends UniCommand{
    
    const TEST_PSYCHOMETRIC = '12';
    const TEST_STREAM_SELECTOR = '8';
    const TEST_MULTI_INTELLEGENCE = '100';
    const TEST_LEARNING_STYLE = '101';
    const TEST_PERSONALITY_STYLE = '104';
    const TEST_ENGINEERING = '9';
    const TEST_HUMANITIES = '10';
    const TEST_COMMERCE = '11';
    const TEST_PROVIDER_CAREER_GUIDE = 'CareerGuide';
    const TEST_PROVIDER_KTS = 'KTS';
    const TEST_PROVIDER_UNIVARIETY = 'Univariety';
    const TEST_PROVIDER_UNIVARIETY_CE = 'UnivarietyCE';
    const TEST_PROVIDER_IMMRSE = 'Immrse';
    
    private $order_assessment = [];
    
    private $group_number;
    
    public $debug = false;
    
    public function beforeAction($action, $params) {
        parent::beforeAction($action, $params);
        
        return true;
    }
    
    public function actionIndex($orderId){
        
        
        $orders = Yii::app()->db->createCommand()
                    ->select('o.order_id, op.product_id, op.order_product_id, op.quantity, p.sku, p.model, pd.email_subject, pd.email_body, o.payment_firstname, o.payment_lastname, o.email, o.store_id')
                    ->from('oc_order o')
                    ->join('oc_order_product op', 'op.order_id = o.order_id')
                    ->join('oc_product p', 'p.product_id = op.product_id')
                    ->join('oc_product_description pd', 'pd.product_id = p.product_id')
                    ->where('o.order_status_id = 17 AND o.link_generated = "0" AND o.order_id =  '.$orderId)
                    ->queryAll();
        
        if (count($orders) > 0) {
            foreach($orders as $order) {
                $linkSrow = Yii::app()->db->createCommand('select count(*) as cnt from oc_order_assessment where order_product_id = '.$order['order_product_id'])->queryRow();
                if ($linkSrow['cnt'] > 0) {
                    echo "links are already there for order - ".$order['order_id'];
                } else {                
                    $links = $this->addTestLinks($order); 

                    if ($order['store_id'] == '1') {
                         $this->sendEmail($order, $links);
                    }
                }
               Yii::app()->db->createCommand("Update oc_order set link_generated = '1' WHERE order_id = ".$order['order_id'])->execute();
               
            }
        }
        
    }
    
    public function addTestLinks($order){
        $this->order_assessment = [];
        $sku = strtolower($order['sku']);
        
        if(strpos($sku, 'immrse') !== false){
            //OrderAssessment::model()->deleteAll("order_product_id = '".(int)$order['order_product_id']."'");
            //$this->db->query("DELETE FROM " . DB_PREFIX . "order_assessment WHERE order_product_id = '" . (int)$order['order_product_id'] . "'");
            
            $this->group_number = 0;
            for ($i = 1; $i <= $order['quantity']; $i++) {
                $this->immrseLink(str_replace('immrse', '', $sku), $order);
                $this->group_number++;
            }
        }else{
            if($sku && method_exists($this, $sku)){
                //OrderAssessment::model()->deleteAll("order_product_id = '".(int)$order['order_product_id']."'");
                //$this->db->query("DELETE FROM " . DB_PREFIX . "order_assessment WHERE order_product_id = '" . (int)$order['order_product_id'] . "'");

                $this->group_number = 0;
                for ($i = 1; $i <= $order['quantity']; $i++) {
                    call_user_func_array([$this, $sku], [$order]);
                    $this->group_number++;
                }
            }
        }
        
        return $this->order_assessment;
    }
    
    private function immrseLink($product_id, $order){
        $pid = str_replace('immrse', '', $product_id);
        
        $this->generateTestLink($order, 
                sprintf('%sassessments/immrse', HTTPS_SERVER), 
                $pid ? $pid : null, 
                self::TEST_PROVIDER_IMMRSE,
            self::TEST_PROVIDER_IMMRSE);
    }

    protected function vipcounselling($order){
        $this->immrseLink('immrse', $order);
    }

    protected function vip3($order){
        $this->immrseLink('immrse', $order);
        $this->group_number++;
        $this->immrseLink('immrse', $order);
        $this->group_number++;
        $this->immrseLink('immrse', $order);
        $this->group_number++;
    }
    
    protected function vip10($order){
        for ($i = 0; $i < 10; $i++) {
            $this->immrseLink('immrse', $order);
            $this->group_number++;
        }
    }

    protected function vip3counselling($order){
        $this->immrseLink('immrse', $order);
        $this->group_number++;
        $this->immrseLink('immrse', $order);
        $this->group_number++;
        $this->immrseLink('immrse', $order);
        $this->group_number++;
    }

    protected function vipidealcareer($order){
        $this->immrseLink('immrse', $order);
        $this->test12($order);
    }

    protected function curriculum($order){
        $this->generateTestLink($order, 
                sprintf('%sassessments/curriculumCourse', HTTPS_SERVER), 
                'curriculum-course', 
                self::TEST_PROVIDER_UNIVARIETY_CE,
            'Curriculum Evaluator');
    }

    protected function personalityassessments($order){
        $this->test100($order);
        $this->test101($order);
        $this->test104($order);
    }

    protected function test12($order){
        $this->generateTestLink($order, 
                sprintf('%sassessments/idealCareer', HTTPS_SERVER), 
                self::TEST_PSYCHOMETRIC, 
                self::TEST_PROVIDER_CAREER_GUIDE,
            'Ideal Career Assessment');
    }
    
    protected function test8($order){
        $this->generateTestLink($order, 
                sprintf('%sassessments/streamSelector', HTTPS_SERVER), 
                self::TEST_STREAM_SELECTOR, 
                self::TEST_PROVIDER_CAREER_GUIDE,
            'Stream Selector Assessment');
    }
    
    protected function test9($order){
        $this->generateTestLink($order, 
                sprintf('%sassessments/engineering', HTTPS_SERVER), 
                self::TEST_ENGINEERING, 
                self::TEST_PROVIDER_CAREER_GUIDE,
            'Engineering Assessment');
    }
    
    protected function test10($order){
        $this->generateTestLink($order, 
                sprintf('%sassessments/humanities', HTTPS_SERVER), 
                self::TEST_HUMANITIES, 
                self::TEST_PROVIDER_CAREER_GUIDE,
            'Humanities Assessment');
    }
    
    protected function test11($order){
        $this->generateTestLink($order, 
                sprintf('%sassessments/commerce', HTTPS_SERVER), 
                self::TEST_COMMERCE, 
                self::TEST_PROVIDER_CAREER_GUIDE,
            'Commerce Assessment');
    }
    
    protected function test100($order){
        $this->generateTestLink($order, 
                sprintf('%sassessments/multiIntelligence', HTTPS_SERVER), 
                self::TEST_MULTI_INTELLEGENCE, 
                self::TEST_PROVIDER_KTS,
            'Multi Intelligence Assessment');
    }
    
    protected function test101($order){
        $this->generateTestLink($order, 
                sprintf('%sassessments/learningStyle', HTTPS_SERVER), 
                self::TEST_LEARNING_STYLE, 
                self::TEST_PROVIDER_KTS,
            'Learning Style Assessment');
    }
    
    protected function test104($order){
        $this->generateTestLink($order, 
                sprintf('%sassessments/personalityStyle', HTTPS_SERVER), 
                self::TEST_PERSONALITY_STYLE, 
                self::TEST_PROVIDER_KTS,
            'Personality Style Assessment');
    }
    
    protected function gem($order){
        
        $this->test12($order);

        $account_server = str_ireplace(['products.', 'shop.'], ['www.', 'www.'], HTTPS_SERVER);
        $this->addGenericTest($order);
        $this->generateTestLink($order, 
                sprintf('%sapp/shopAccountGm', $account_server), 
                'gem', 
                self::TEST_PROVIDER_UNIVARIETY,
            'Signup');
    }
    
    protected function promap($order){
        $account_server = str_ireplace(['products.', 'shop.'], ['www.', 'www.'], HTTPS_SERVER);
        $this->generateTestLink($order, 
                sprintf('%sapp/shopAccountPm', $account_server), 
                'promap', 
                self::TEST_PROVIDER_UNIVARIETY,
            'Signup');
    }

    protected function promapPlus($order){
        
        //$this->test12($order);

        $account_server = str_ireplace(['products.', 'shop.'], ['www.', 'www.'], HTTPS_SERVER);
        //$this->addGenericTest($order);
        $this->generateTestLink($order, 
                sprintf('%sapp/shopAccountPp', $account_server), 
                'promapPlus', 
                self::TEST_PROVIDER_UNIVARIETY,
            'Signup');
    }
    
    protected function promapSuper($order){
        
        //$this->test12($order);
        
        $account_server = str_ireplace(['products.', 'shop.'], ['www.', 'www.'], HTTPS_SERVER);
        //$this->addGenericTest($order);
        $this->generateTestLink($order, 
                sprintf('%sapp/shopAccountPs', $account_server), 
                'promapSuper', 
                self::TEST_PROVIDER_UNIVARIETY,
            'Signup');
    }
    
    protected function gccgold($order){
        $this->test12($order);
        $this->test8($order);
        $this->test9($order);
        $this->test11($order);
    }
    
    protected function careerpro710($order){
        $this->test12($order);
        $this->test8($order);
    }
    
    protected function CareerPro1112($order){
        $this->test12($order);
        $this->test100($order);
    }

    private function addGenericTest($order){
        $this->generateTestLink($order, sprintf('%sassessments/chooseAssessment', HTTPS_SERVER), null, null, 'Assessment');
    }
    
    private function generateTestLink($order, $url, $test_id, $test_provider, $assessment_name = null){
        
        $row = Yii::app()->db->createCommand('select UUID() as uuid')->queryRow();
        $uuid = md5($row['uuid']);

        $link = [
            'assessment_name' => $assessment_name,
            'unique_id' => $uuid,
            'test_link' => sprintf('%s?h=%s', $url, $uuid),
            'test_provider' => $test_provider,
            'test_id'=> $test_id,
            'product_id' => $order['product_id'],
            'order_product_id' => $order['order_product_id'],
            'order_id' => $order['order_id'],
            'group_no'=>$this->group_number,
        ];
        
        if($test_id && $test_provider){
            
             $insert  = sprintf('INSERT INTO ' . DB_PREFIX . 'order_assessment SET assessment_name = "%s", unique_id = "%s", test_link = "%s", test_provider = "%s", test_id = "%s", product_id = %d, order_product_id = %d, added_on = "%s", group_no = %d', $assessment_name, $link['unique_id'], $link['test_link'], $link['test_provider'], $link['test_id'], $link['product_id'], $link['order_product_id'], date('Y-m-d H:i:s'), $this->group_number);
            Yii::app()->db->createCommand($insert)->execute();
            
            //$this->db->query(sprintf('INSERT INTO ' . DB_PREFIX . 'order_assessment SET assessment_name = "%s", unique_id = "%s", test_link = "%s", test_provider = "%s", test_id = "%s", product_id = %d, order_product_id = %d, added_on = "%s", group_no = %d', $assessment_name, $link['unique_id'], $link['test_link'], $link['test_provider'], $link['test_id'], $link['product_id'], $link['order_product_id'], date('Y-m-d H:i:s'), $this->group_number));
        }else{
            $insert  = sprintf('INSERT INTO ' . DB_PREFIX . 'order_assessment SET assessment_name = "%s", unique_id = "%s", test_link = "%s", product_id = %d, order_product_id = %d, added_on = "%s", group_no = %d', $assessment_name, $link['unique_id'], $link['test_link'], $link['product_id'], $link['order_product_id'], date('Y-m-d H:i:s'), $this->group_number);
            Yii::app()->db->createCommand($insert)->execute();
            
            //$this->db->query(sprintf('INSERT INTO ' . DB_PREFIX . 'order_assessment SET assessment_name = "%s", unique_id = "%s", test_link = "%s", product_id = %d, order_product_id = %d, added_on = "%s", group_no = %d', $assessment_name, $link['unique_id'], $link['test_link'], $link['product_id'], $link['order_product_id'], date('Y-m-d H:i:s'), $this->group_number));
        }
        
        $this->order_assessment[] = $link;
        return $link;
    }
    
    protected function sendEmail($order, $links) {
        if (count($links) > 0) {
            $subject = $order['email_subject'].'- Order '.$order['order_id'];
            
            $links_html = '';
            foreach ($links as $row) {
                $link = $row['test_link'];
                $label = 'Take Test';
                $showFooter = 0;
                switch ($row['test_provider']) {
                    case 'Univariety':
                        $label = 'Signup/Signin';
                        $showFooter = 1;
                        break;
                    case 'UnivarietyCE':
                        $label = 'Get Started';
                        $showFooter = ($showFooter == 1) ? 1 : 0;
                        break;
                    case 'Immrse':
                        $label = 'Start Program';
                        $showFooter = ($showFooter == 1) ? 1 : 0;
                        break;
                    default :
                        if(!$row['test_provider'] && !$row['test_id']) {
                            $label = 'Choose Test';
                            $showFooter = ($showFooter == 1) ? 1 : 0;
                        }
                        break;
                }
                
                $links_html .= '<tr><td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">'.$order['model'].'</td><td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">'.$link.'</td><td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;"><a href="'.$link.'"><button style=" width: 120px; height: 30px; border-radius: 2px; background:rgba(80, 173, 85, 1); text-transform:uppercase; color: white; border: none;">'.$label.'</button></a></td></tr>';
            }
            
            $footerHtml = '';
            if ($showFooter == 1) {
                $footerHtml = '<p style="background-color: transparent; font-family: Arial; font-size: 14px; white-space: pre-wrap;">Note: Use these links for taking tests and also for SignUp/SignIn into Univariety Account. Creating/logging into Univariety Account is essential for exploring a large database of Careers, Courses, Colleges, Scholarships and many more on our platform.</p>';
            }
            
            $body = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/1999/REC-html401-19991224/strict.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>
<body style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #000000;">
<div style="width: 680px;">'.$order['email_body'].'
 <table style="border-collapse: collapse; width: 100%; border: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;">
    <thead>
      <tr>
        <td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #EFEFEF; font-weight: bold; text-align: center; padding: 7px; color: #222222;">Product</td>
        <td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #EFEFEF; font-weight: bold; text-align: center; padding: 7px; color: #222222;">Important Links</td>
        <td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #EFEFEF; font-weight: bold; text-align: center; padding: 7px; color: #222222;">Action</td>
      </tr>
    </thead>
    <tbody>'.$links_html.'
    </tbody>   
  </table>'.$footerHtml.'
  <p style="background-color: transparent; font-family: Arial; font-size: 14px; white-space: pre-wrap;">Thanks,</p>
  <p style="background-color: transparent; font-family: Arial; font-size: 14px; white-space: pre-wrap;">Team Univariety</p>
</div>
</body>
</html>';
            
            $replace = [$order['payment_firstname'], $order['payment_lastname'], $order['sku']];
            $key_words = ['{{FIRST_NAME}}', '{{LAST_NAME}}', '{{PRODUCT_NAME}}'];
            $email_body = str_replace($key_words, $replace, $body);
            
            $obj = EmailMessage::getInstance();
            $res = $obj->setTo($order['email'], $order['payment_firstname'])
                    ->setBcc('naveen@univariety.com', 'naveen@univariety.com')
                    ->setHtml($email_body)
                    ->setReplyto('products@univariety.com', 'Student Products')
                    ->setSubject($subject)
                    ->send();
            print_r($res);
        }
    }
}

