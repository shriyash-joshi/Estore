<?php
class WP_Gateway_Paynimo extends WC_Payment_Gateway
{
    const DEFAULT_LABEL = 'Credit Card/Debit Card/NetBanking';
    const SESSION_KEY = 'paynimo_wc_order_id';
    const DEFAULT_SUCCESS_MESSAGE = 'Thank you for buying the course';
    public $method_description = 'Allow customers to securely pay via Paynimo (Credit/Debit Cards, NetBanking, UPI, Wallets)';
    public function __construct($hooks = true)
    {
        global $woocommerce;
        $this->id = 'paynimo';
        $this->method_title = __('Paynimo', 'paynimo');
        $this->icon = plugins_url('images/paynimo.png', __FILE__);
        $this->has_fields = false;
        $this->init_form_fields();
        $this->init_settings();
        $this->title = $this->settings['title'];
        $this->description = $this->settings['description'];
        $this->paynimo_merchant_code = $this->settings['paynimo_merchant_code'];
        $this->paynimo_request_type = $this->settings['paynimo_request_type'];
        $this->paynimo_key = $this->settings['paynimo_key'];
        $this->SALT = $this->settings['paynimo_salt'];
        $this->paynimo_iv = $this->settings['paynimo_iv'];
        $this->paynimo_webservice_locator = $this->settings['paynimo_webservice_locator'];
        $this->paynimo_merchant_scheme_code = $this->settings['paynimo_merchant_scheme_code'];
        $this->paynimo_redirect_msg = $this->settings['paynimo_redirect_msg'];
        $this->paynimo_decline_msg = $this->settings['paynimo_decline_msg'];
        $this->paynimo_success_msg = $this->settings['paynimo_success_msg'];

        if (version_compare(WOOCOMMERCE_VERSION, '4.0.0', '>='))
        {
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array(
                $this,
                'process_admin_options'
            ));
        }
        else
        {
            add_action('woocommerce_update_options_payment_gateways', array(&$this,
                'process_admin_options'
            ));
        }
        $this->notify_url = add_query_arg('wc-api', 'woocommerce_paynimo', home_url('/'));
        $this->msg['message'] = "";
        $this->msg['class'] = "";

        if ($hooks)
        {
            $this->initHooks();
        }

     }

    protected function initHooks()
    {
        add_action('init', array(&$this,
            'check_paynimo_response'
        ));

        add_action('woocommerce_receipt_paynimo', array(
            $this,
            'receipt_page'
        ));

        add_action('woocommerce_api_paynimo', array(
            $this,
            'check_paynimo_response'
        ));

        add_action('woocommerce_thankyou_paynimo', array(
            $this,
            'thankyou_page'
        ));

    }

    function init_form_fields()
    {

        $this->form_fields = array(
            'enabled' => array(
                'title' => __('Enable/Disable', 'paynimo') ,
                'type' => 'checkbox',
                'label' => __('Enable Paynimo Payment Module.', 'paynimo') ,
                'default' => 'no'
            ) ,
            'title' => array(
                'title' => __('<span style="color: #a00;">* </span>Title:', 'Paynimo') ,
                'type' => 'text',
                'id' => "title",
                'desc_tip' => true,
                'placeholder' => __('Paynimo', 'woocommerce') ,
                'description' => __('Your desire title name .it will show during checkout proccess.', 'paynimo') ,
                'default' => __('Paynimo', 'paynimo')
            ) ,
            'description' => array(
                'title' => __('<span style="color: #a00;">* </span>Description:', 'paynimo') ,
                'type' => 'textarea',
                'desc_tip' => true,
                'placeholder' => __('Description', 'woocommerce') ,
                'description' => __('Pay securely through Paynimo.', 'paynimo') ,
                'default' => __('Pay securely through Paynimo.', 'paynimo')
            ) ,
            'paynimo_merchant_code' => array(
                'title' => __('<span style="color: #a00;">* </span>Merchant Code', 'paynimo') ,
                'type' => 'text',
                'desc_tip' => true,
                'placeholder' => __('Merchant Code', 'woocommerce') ,
                'description' => __('Merchant Code')
            ) ,
            'paynimo_request_type' => array(
                'title' => __('<span style="color: #a00;">* </span>Request Type', 'woocommerce') ,
                'type' => 'select',
                'css' => 'min-width:137px;',
                'description' => __('Choose request type.', 'woocommerce') ,
                'default' => 'T',
                'desc_tip' => true,
                'options' => array(
                    'T' => __('T', 'woocommerce') ,
                )
            ) ,
            'paynimo_key' => array(
                'title' => __('<span style="color: #a00;">* </span>Key', 'paynimo') ,
                'type' => 'text',
                'desc_tip' => true,
                'placeholder' => __('Key', 'woocommerce') ,
                'description' => __('Key')
            ) ,
            'paynimo_salt' => array(
                'title' => __('<span style="color: #a00;">* </span>SALT', 'paynimo') ,
                'type' => 'text',
                'desc_tip' => true,
                'placeholder' => __('SALT', 'woocommerce') ,
                'description' => __('SALT')
            ) ,
            'paynimo_iv' => array(
                'title' => __('<span style="color: #a00;">* </span>IV', 'paynimo') ,
                'type' => 'text',
                'desc_tip' => true,
                'placeholder' => __('IV', 'woocommerce') ,
                'description' => __('IV')
            ) ,
            'paynimo_webservice_locator' => array(
                'title' => __('<span style="color: #a00;">* </span>Webservice Locator', 'woocommerce') ,
                'type' => 'select',
                'css' => 'min-width:137px;',
                'description' => __('Choose Webservice Locator.', 'woocommerce') ,
                'default' => 'Test',
                'desc_tip' => true,
                'options' => array(
                    'Test' => __('TEST', 'woocommerce') ,
                    'Live' => __('LIVE', 'woocommerce') ,
                )
            ) ,
            'paynimo_merchant_scheme_code' => array(
                'title' => __('<span style="color: #a00;">* </span>Merchant Scheme Code', 'paynimo') ,
                'type' => 'text',
                'desc_tip' => true,
                'placeholder' => __('Merchant Scheme Code', 'woocommerce') ,
                'description' => __('Merchant Scheme Code')
            ) ,
            'paynimo_success_msg' => array(
                'title' => __('<span style="color: #a00;">* </span>Success Message', 'paynimo') ,
                'type' => 'textarea',
                'desc_tip' => true,
                'default' => 'Thank you for shopping with us. Your account has been charged and your transaction is successful.',
                'description' => __('Success Message')
            ) ,
            'paynimo_decline_msg' => array(
                'title' => __('<span style="color: #a00;">* </span>Decline Message', 'paynimo') ,
                'type' => 'textarea',
                'desc_tip' => true,
                'default' => 'Thank you for shopping with us. However, the transaction has been declined.',
                'description' => __('Decline Message')
            ) ,
            'paynimo_redirect_msg' => array(
                'title' => __('<span style="color: #a00;">* </span>Redirect Message', 'paynimo') ,
                'type' => 'textarea',
                'desc_tip' => true,
                'default' => 'Thank you for your order. We are now redirecting you to paynimo to make payment.',
                'description' => __('Redirect Message')
            ) ,

        );
    }

    public function admin_options()
    {
        echo '<h3>' . __('Paynimo Payment Gateway', 'paynimo') . '</h3>';?>
        <a href="#" target="_blank"><img src="<?php echo $this->icon = plugins_url('images/paynimo.png', __FILE__); ?>"/></a>			
        <?php
        echo '<table class="form-table">';
        $this->generate_settings_html();
        echo '</table>';
    }

    function payment_fields()
    {
        if ($this->description) echo wpautop(wptexturize($this->description));
    }

    function receipt_page($order)
    {
        echo '<p>' . __('Thank you for your order, please click the button below to pay with Paynimo.', 'paynimo') . '</p>';
        echo $this->generate_paynimo_form($order);
    }

    public function generate_paynimo_form($order_id)
    {
        global $woocommerce;
        $order = wc_get_order($order_id);
        $_SESSION['order_id'] = $order_id;
        $order_id = $order_id . '_' . date("ymds");
        $orderKey = $order->get_order_key();
        $merchant_txn_id = str_replace('wc_order_','',$orderKey);
        $cur_date = date("d-m-Y");
        $returnUrl = $this->getRedirectUrl();
        if (is_user_logged_in()) $customer_id = get_current_user_id();
        else $customer_id = md5(wp_generate_password(32));
        $custom_logo_id = get_theme_mod('custom_logo');
        $image = wp_get_attachment_image_src($custom_logo_id, 'full');
        $order_amount = $order->get_total();
        $hash_data = $this->paynimo_merchant_code . '|' . $merchant_txn_id . '|'.$order_amount.'||' . $customer_id . '|' . $order->billing_phone . '|' . $order->billing_email . '||||||||||'.$this->SALT;
        $hashed = hash('sha512', $hash_data);
        $form = '<button id="btnSubmit">Make a Payment</button>';
        $form .= '<script type="text/javascript" src="https://www.paynimo.com/paynimocheckout/server/lib/checkout.js"></script>';
        $form .= "<script>
        jQuery(document).ready(function() {
            function handleResponse(res) {
                    // handled with check_paynimo_response
            };

            jQuery(function(){
               
                var configJson = {
                    'tarCall': false,
                    'features': {
                        'showPGResponseMsg': true,
                        'enableAbortResponse': true,
                        'enableExpressPay': true,
                        'enableNewWindowFlow': true    //for hybrid applications please disable this by passing false
                    },
                    'consumerData': {
                        'deviceId': 'WEBSH2',	//possible values 'WEBSH1', 'WEBSH2' and 'WEBMD5'
                        'token': '$hashed',
                        'returnUrl': '$returnUrl',    //merchant response page URL
                        'responseHandler': handleResponse,
                        'paymentMode': 'all',
                        'merchantLogoUrl': '$image[0]',  //provided merchant logo will be displayed
                        'merchantId': '$this->paynimo_merchant_code',
                        'currency': 'INR',
                        'consumerId': '$customer_id',
                        'consumerMobileNo': '$order->billing_phone',
                        'consumerEmailId': '$order->billing_email',
                        'txnId': '$merchant_txn_id',   //Unique merchant transaction ID
                        'items': [{
                            'itemId': '$this->paynimo_merchant_scheme_code',
                            'amount': '$order_amount',
                            'comAmt': '0'
                        }],
                        'customStyle': {
                            'PRIMARY_COLOR_CODE': '#4c6eea',   //merchant primary color code
                            'SECONDARY_COLOR_CODE': '#eee',   //provide merchant's suitable color code
                            'BUTTON_COLOR_CODE_1': '#f5c603',   //merchant's button background color code
                            'BUTTON_COLOR_CODE_2': '#FFFFFF'   //provide merchant's suitable color code for button text
                        }
                    }
                };
                jQuery.pnCheckout(configJson);
                if(configJson.features.enableNewWindowFlow){
                    pnCheckoutShared.openNewWindow();
                }
            });
        });
    </script>";
        return $form;

    }

    function process_payment($order_id)
    {
        global $woocommerce;
        $order = new WC_Order($order_id);
        $woocommerce
            ->session
            ->set(self::SESSION_KEY, $order_id);
        $orderKey = $order->get_order_key();
        return array(
            'result' => 'success',
            'redirect' => add_query_arg('key', $orderKey, $order->get_checkout_payment_url(true))
        );

    }    

    function check_paynimo_response()
    {

        global $woocommerce;
        $orderId = $woocommerce
            ->session
            ->get(self::SESSION_KEY);
        $order = new WC_Order($orderId);
        $_POST = explode('|', $_POST['msg']);
        // If the order has already been paid for
        // redirect user to success page
        if ($order->needs_payment() === false)
        {
            $this->redirectUser($order);
        }

        $paymentId = $_POST[3];
        $success = false;
        $error = '';
      
        if ($orderId and !empty($_POST[3]))
        {
            if ($_POST[0] == '0300')
            {
                $success = false;
                try
                {
                    //$this->verifySignature($orderId); // needed hash key check for additional security
                    $success = true;                   
                }
                catch (Error $e)
                {
                    $error = 'WOOCOMMERCE_ERROR: Payment to Paynimo Failed';
                }
            }
            elseif ($_POST[0] == '0399')
            {
                $success = false;
                $error = 'Transaction Failed or Cancelled';
            }
            else
            {
                $success = false;
                $error = "Payment Failed.";
            }
            if($success == false){
                $this->handleErrorCase($order);
                $this->updateOrder($order, $success, $error, $paymentId);
                wp_redirect(wc_get_checkout_url());
                exit;
            }
        
        }

        if( $success == true ){
            $this->updateOrder($order, $success, $error, $paymentId);
            $this->redirectUser($order);
        }
       
    }

    protected function redirectUser($order)
    {
        $redirectUrl = $this->get_return_url($order);
        wp_redirect($redirectUrl);
        exit;
    }

     /**
     * Modifies existing order and handles success case
     *
     * @param $success, & $order
     */
    public function updateOrder(&$order, $success, $errorMessage, $paymentID, $webhook = false)
    {
        global $woocommerce;

        $orderId = $order->get_order_number();

        if (($success === true) and ($order->needs_payment() === true))
        {
            $this->msg['message'] = $this->getCustomOrdercreationMessage() . "&nbsp; Order Id: $orderId";
            $this->msg['class'] = 'success';

            $order->payment_complete($paymentID);
            $order->add_order_note("Payment successful <br/> Txn Id: $paymentID");

            if (isset($woocommerce->cart) === true)
            {
                $woocommerce
                    ->cart
                    ->empty_cart();
            }
        }
        else
        {
            $this->msg['class'] = 'error';
            $this->msg['message'] = $errorMessage;

            if ($paymentID)
            {
                $order->add_order_note("Payment Failed. Please check Paynimo Dashboard. <br/> Txn Id: $paymentID");
            }

            $order->add_order_note("Transaction Failed: $errorMessage<br/>");
            $order->update_status('failed');
        }

        if ($webhook === false)
        {
            $this->add_notice($this->msg['message'], $this->msg['class']);
        }

    }

    protected function handleErrorCase(&$order)
    {
        $orderId = $order->get_order_number();

        $this->msg['class'] = 'error';
        $this->msg['message'] = $this->getErrorMessage($orderId);
    }

    protected function getErrorMessage($orderId)
    {
        // We don't have a proper order id
        if ($orderId !== null)
        {
            $message = 'An error occured while processing this payment';
        }
        if (isset($_POST['error']) === true)
        {
            $error = $_POST['error'];

            $description = htmlentities($error['description']);
            $code = htmlentities($error['code']);

            $message = 'An error occured. Description : ' . $description . '. Code : ' . $code;

            if (isset($error['field']) === true)
            {
                $fieldError = htmlentities($error['field']);
                $message .= 'Field : ' . $fieldError;
            }
        }
        else
        {
            $message = 'An error occured. Please contact administrator for assistance';
        }

        return $message;
    }

   

    public function getSetting($key)
    {
        return $this->settings[$key];
    }

    protected function getCustomOrdercreationMessage()
    {
        $message = $this->getSetting('woocommerce_paynimo_paynimo_success_msg');
        if (isset($message) === false)
        {
            $message = static ::DEFAULT_SUCCESS_MESSAGE;
        }
        return $message;
    }

    protected function add_notice($message, $type = 'notice')
    {
        global $woocommerce;
        $type = in_array($type, array(
            'notice',
            'error',
            'success'
        ) , true) ? $type : 'notice';
        // Check for existence of new notification api. Else use previous add_error
        if (function_exists('wc_add_notice'))
        {
            wc_add_notice($message, $type);
        }
        else
        {
            // Retrocompatibility WooCommerce < 2.1
            switch ($type)
            {
                case "error":
                    $woocommerce->add_error($message);
                break;
                default:
                    $woocommerce->add_message($message);
                break;
            }
        }
    }

    private function getRedirectUrl()
    {
        return add_query_arg('wc-api', $this->id, trailingslashit(get_home_url()));
    }
}

