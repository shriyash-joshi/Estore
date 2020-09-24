<?php 
/**
 * Plugin Name: Order Assessments
 * Plugin URI: 
 * Description: Order Assements links generation plugin
 * Version: 1.0
 * Author: Sreenivas
 * Author URI: https://zessta.com
 */  
    define('TEST_PSYCHOMETRIC','12');
    define('TEST_STREAM_SELECTOR','8');
    define('TEST_MULTI_INTELLEGENCE','100');
    define('TEST_LEARNING_STYLE','101');
    define('TEST_PERSONALITY_STYLE','104');
    define('TEST_ENGINEERING','9');
    define('TEST_HUMANITIES','10');
    define('TEST_COMMERCE','11');
    define('TEST_PROVIDER_CAREER_GUIDE','CareerGuide');
    define('TEST_PROVIDER_KTS','KTS');
    define('TEST_PROVIDER_UNIVARIETY','Univariety');
    define('TEST_PROVIDER_UNIVARIETY_CE','UnivarietyCE');
    define('TEST_PROVIDER_IMMRSE','Immrse');
    define('HTTPS_SERVER',home_url().'/');

    $order_assessment = [];
    
    global $group_number;
    
    $debug = false;
   
    
    function generateAssessments($orderId){
        global $wpdb; 
        $order = wc_get_order( $orderId );    
        $sq_order_number = $order->get_order_number();   
    
        foreach ($order->get_items() as $item_key => $item ){
            $item_id = $item->get_id();
            $item_data    = $item->get_data();
            //update oc_order_product_id
            wc_add_order_item_meta($item_id, 'oc_order_product_id', $item_id);
            $linkSrow = $wpdb->get_results("select count(*) as cnt from ".$wpdb->prefix."order_assessment where order_id=".$sq_order_number." and order_product_id = ".trim($item_id) ." limit 1");
            if ($linkSrow[0]->cnt > 0) {
                echo "links are already there for order - ".$orderId;
            } else {               
                $links = addTestLinks($item_data); 
            }
        }        
    }
    
    function addTestLinks($item){
        global $group_number;
        global $product;
        $order_assessment = [];
        $product = wc_get_product($item['product_id']);
        $sku = $product->get_sku();      
        if(strpos($sku, 'immrse') !== false){
            $group_number = 0;
            for ($i = 1; $i <= $item['quantity']; $i++) {
                immrseLink(str_replace('immrse', '', $sku), $item,$group_number);
                $group_number++;
            }
        }else{
            if($sku && function_exists( $sku ) ){
               $group_number = 0;
                for ($i = 1; $i <= $item['quantity']; $i++) {
                    call_user_func_array($sku, [$item]);
                    $group_number++;
                }
            }
        }        
        return $order_assessment;
    }


    function vipidealcareer($item){
        global $group_number;
        immrseLink('immrse', $item,$group_number);
        test12($item);
    }
    
    function immrseLink($product_id, $order){
        global $group_number;
        $pid = str_replace('immrse', '', $product_id);
        generateTestLink($order, 
                sprintf('%sassessments/immrse', HTTPS_SERVER), 
                $pid ? $pid : null, 
                TEST_PROVIDER_IMMRSE,TEST_PROVIDER_IMMRSE);
    }

    function vipcounselling($order){
        immrseLink('immrse', $order);
    }

    function vip3($order){
        global $group_number;
        immrseLink('immrse', $order);
        $group_number++;
        immrseLink('immrse', $order);
        $group_number++;
        immrseLink('immrse', $order);
        $group_number++;
    }
    
    function vip10($order){
        global $group_number;
        for ($i = 0; $i < 10; $i++) {
            immrseLink('immrse', $order);
            $group_number++;
        }
    }

    function vip3counselling($order){
        global $group_number;
        immrseLink('immrse', $order);
        $group_number++;
        immrseLink('immrse', $order);
        $group_number++;
        immrseLink('immrse', $order);
        $group_number++;
    }
    function curriculum($order){
        global $group_number;
        generateTestLink($order, 
                sprintf('%sassessments/curriculumCourse', HTTPS_SERVER), 
                'curriculum-course', 
                TEST_PROVIDER_UNIVARIETY_CE,
            'Curriculum Evaluator');
    }

    function personalityassessments($order){
        test100($order);
        test101($order);
        test104($order);
    }

    function test12($order){
        generateTestLink($order, 
                sprintf('%sassessments/idealCareer', HTTPS_SERVER), 
                TEST_PSYCHOMETRIC, 
                TEST_PROVIDER_CAREER_GUIDE,
            'Ideal Career Assessment');
    }
    
    function test8($order){
        generateTestLink($order, 
                sprintf('%sassessments/streamSelector', HTTPS_SERVER), 
                TEST_STREAM_SELECTOR, 
                TEST_PROVIDER_CAREER_GUIDE,
            'Stream Selector Assessment');
    }
    
    function test9($order){
        generateTestLink($order, 
                sprintf('%sassessments/engineering', HTTPS_SERVER), 
                TEST_ENGINEERING, 
                TEST_PROVIDER_CAREER_GUIDE,
            'Engineering Assessment');
    }
    
    function test10($order){
       generateTestLink($order, 
                sprintf('%sassessments/humanities', HTTPS_SERVER), 
                TEST_HUMANITIES, 
                TEST_PROVIDER_CAREER_GUIDE,
            'Humanities Assessment');
    }
    
    function test11($order){
        generateTestLink($order, 
                sprintf('%sassessments/commerce', HTTPS_SERVER), 
                TEST_COMMERCE, 
               TEST_PROVIDER_CAREER_GUIDE,
            'Commerce Assessment');
    }
    
    function test100($order){
        generateTestLink($order, 
                sprintf('%sassessments/multiIntelligence', HTTPS_SERVER), 
                TEST_MULTI_INTELLEGENCE, 
                TEST_PROVIDER_KTS,
            'Multi Intelligence Assessment');
    }
    
    function test101($order){
        generateTestLink($order, 
                sprintf('%sassessments/learningStyle', HTTPS_SERVER), 
                TEST_LEARNING_STYLE, 
                TEST_PROVIDER_KTS,
            'Learning Style Assessment');
    }
    
    function test104($order){
        generateTestLink($order, 
                sprintf('%sassessments/personalityStyle', HTTPS_SERVER), 
               TEST_PERSONALITY_STYLE, 
               TEST_PROVIDER_KTS,
            'Personality Style Assessment');
    }

    function gempremium($order){
        global $group_number;
        $account_server = str_ireplace(['products.', 'shop.'], ['www.', 'www.'], HTTPS_SERVER);
        generateTestLink($order, 
                sprintf('%sapp/shopAccountGm', $account_server), 
                'gem', 
                TEST_PROVIDER_UNIVARIETY,
            'Signup');
        $group_number++;
        promap($order);
    }
    
    function gem($order){ 
        global $group_number;
        test12($order);
        $group_number++;      
        $account_server = str_ireplace(['products.', 'shop.'], ['www.', 'www.'], HTTPS_SERVER);
        addGenericTest($order);
        generateTestLink($order, 
                sprintf('%sapp/shopAccountGm', $account_server), 
                'gem', 
                TEST_PROVIDER_UNIVARIETY,
            'Signup');
    }
    
    function promap($order){
        $account_server = str_ireplace(['products.', 'shop.'], ['www.', 'www.'], HTTPS_SERVER);
        generateTestLink($order, 
                sprintf('%sapp/shopAccountPm', $account_server), 
                'promap', 
                TEST_PROVIDER_UNIVARIETY,
            'Signup');
    }

    function promapPlus($order){
        $account_server = str_ireplace(['products.', 'shop.'], ['www.', 'www.'], HTTPS_SERVER);
        generateTestLink($order, 
                sprintf('%sapp/shopAccountPp', $account_server), 
                'promapPlus', 
                TEST_PROVIDER_UNIVARIETY,
            'Signup');
    }
    
    function promapSuper($order){        
        $account_server = str_ireplace(['products.', 'shop.'], ['www.', 'www.'], HTTPS_SERVER);
        generateTestLink($order, 
                sprintf('%sapp/shopAccountPs', $account_server), 
                'promapSuper', 
                TEST_PROVIDER_UNIVARIETY,
            'Signup');
    }
    
    function gccgold($order){
        test12($order);
        test8($order);
        test9($order);
        test11($order);
    }
    
    function careerpro710($order){
        test12($order);
        test8($order);
    }
    
    function CareerPro1112($order){
        test12($order);
        test100($order);
    }

    function addGenericTest($order){
        generateTestLink($order, sprintf('%sassessments/chooseAssessment', HTTPS_SERVER), null, null, 'Assessment');
    }
    function generateTestLink($order, $url, $test_id, $test_provider, $assessment_name = null){
        global $wpdb;
        global $group_number;
        $row = $wpdb->get_results('select UUID() as uuid');       
        $uuid = md5($row[0]->uuid);
        $orderMeta =  wc_get_order( $order['order_id'] );
        $sq_order_number = $orderMeta->get_order_number();
        $link = [
            'assessment_name' => $assessment_name,
            'unique_id' => $uuid,
            'test_link' => sprintf('%s?h=%s', $url, $uuid),
            'test_provider' => $test_provider,
            'test_id'=> $test_id,
            'order_id'=>$sq_order_number,
            'product_id' => $order['product_id'],
            'order_product_id' => $order['id'],
            'added_on'=>date('Y-m-d H:i:s'),
            'group_no'=>$group_number,
        ];
        $wpdb->insert($wpdb->prefix.'order_assessment', $link);       
        $order_assessment[] = $link;
        return $link;
    }

    function generateAssessmentsCLI($orderId){
        global $wpdb; 
        $order = wc_get_order( $orderId['0'] );      
        foreach ($order->get_items() as $item_key => $item ){
            $item_id = $item->get_id();
            $item_data    = $item->get_data();
            //update oc_order_product_id
            wc_add_order_item_meta($item_id, 'oc_order_product_id', $item_id);
         
            $links = addTestLinksCLI($item_data);             
        }        
    }

    function addTestLinksCLI($item){
        global $group_number;
        $order_assessment = [];
        $product = wc_get_product($item['product_id']);
        $sku = $product->get_sku();      
        if(strpos($sku, 'immrse') !== false){
            $group_number = 0;
            for ($i = 1; $i <= $item['quantity']; $i++) {
                immrseLink(str_replace('immrse', '', $sku), $item,$group_number);
                $group_number++;
            }
        }else{
            if($sku && function_exists( $sku ) ){
               $group_number = 0;
                for ($i = 1; $i <= $item['quantity']; $i++) {
                    call_user_func_array($sku, [$item]);
                    $group_number++;
                }
            }
        }        
        return $order_assessment;
    }

if ( class_exists( 'WP_CLI' )) {
	WP_CLI::add_command( 'univariety_generate_order_assessment', 'generateAssessmentsCLI' );
}


function sendEmails($orderId){
    global $wpdb; 
    $order = wc_get_order( $orderId['0'] );   
        if ( isset( $order ) && !empty($order) ) {
           $items = $order->get_items();
           foreach ( $items as $item ) {
             $seq_order = $order->get_order_number();
             $product_id = $item->get_product_id();
             $email_subject = get_post_meta( $product_id, 'email_subject')[0];
             $email_body = get_post_meta( $product_id, 'email_body')[0];
             if( $email_subject !=='' && $email_body !==''):        
                 $email_subject = $email_subject .' - Order ' .$seq_order;
                 do_action( 'order_assessment_email_notification', $order, $product_id,$email_subject,$email_body );
               endif;
         }
    }
}

if ( class_exists( 'WP_CLI' )) {
	WP_CLI::add_command( 'univariety_generate_order_assessment_email', 'sendEmails' );
}

