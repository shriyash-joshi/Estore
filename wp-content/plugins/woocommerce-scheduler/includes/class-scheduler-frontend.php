<?php
namespace{
    
    if (!class_exists('SchedulerFrontend')) {
        class SchedulerFrontend
        {
            public function __construct()
            {
                add_action('woocommerce_single_product_summary', array($this,'wdmProductPageSummary'), 30);
                add_filter('woocommerce_locate_template', array($this,'wdmChangeWooTemplatePath'), 10, 3);
                add_filter('woocommerce_loop_add_to_cart_link', array($this, 'customWoocommerceProductAddToCartText'), 10, 2);
                add_action('woocommerce_add_to_cart_validation', array($this,'validateAddToCart'), 6, 10);
                add_action('template_redirect', array($this, 'removeUnavailableProductFromCart'));
                add_action('init', array($this, 'redirectToLoginPage'), 20);
                add_shortcode('wdm_scheduler_product_page_timer', array($this, 'productPageShortcode'));
            }

            public function validateAddToCart($add, $product_id, $product_quantity, $variation_id = '', $variations = array(), $cart_item_data = array())
            {
                $pid = $product_id;
                unset($add);
                unset($variations);
                unset($product_quantity);
                unset($cart_item_data);
                $availabilityFlag = "";
                if (!empty($variation_id)) {
                    $pid = $variation_id;
                    $availabilityFlag = wdmCheckDateValidation($pid, 'variation')?"yes":"no";
                } else {
                    $pid = $product_id;
                    $availabilityFlag = wdmCheckDateValidation($pid)?"yes":"no";
                }

                if ($availabilityFlag == 'no') {
                    addScheduleNotice(sprintf(__('Sorry, the selected product &quot;%s&quot; cannot be purchased.', 'custom-product-boxes'), get_the_title($pid)), 'error');
                    return false;
                } else {
                    return true;
                }
            }

            public function wdmChangeWooTemplatePath($template, $template_name, $template_path)
            {
                global $woocommerce;
                $coreTemplate = $template;
                if (! $template_path) {
                    $template_path = $woocommerce->template_url;
                }
                $plugin_path  = wdmWooSchedulerPath() . '/woocommerce/';

                // Look within passed path within the theme - this is priority
                $template = locate_template(
                    array(
                        $template_path . $template_name,
                        $template_name
                    )
                );
                // Modification: Get the template from this plugin, if it exists
                if (!$template && file_exists($plugin_path . $template_name)) {
                    $template = $plugin_path . $template_name;
                }

                // Use default template
                if (!$template) {
                    $template = $coreTemplate;
                }


                // Return what we found
                return $template;
            }


            /**
            * Display Avalibility text
            * @param  [type] $link    Add to cart link
            * @param  [type] $product product data
            * @return [type]          [description]
            */
            public function customWoocommerceProductAddToCartText($link, $product)
            {
                $availability=true;
                $product_id = wooSchedulerProductId($product);

                if (in_array($product->get_type(), array('course', 'simple'))) {
                    $availability = wdmCheckDateValidation($product_id);
                } elseif ($product->get_type() == 'variable') {
                    $availability = getVariableAvailability($product);
                }
                
                $wdmwsSettings = \Includes\AdminSettings\WdmWsSettings::getSettings();
                $expirationMsg = isset($wdmwsSettings) && isset($wdmwsSettings['wdmws_custom_product_shop_expiration']) ? $wdmwsSettings['wdmws_custom_product_shop_expiration'] : "Unavailable" ;
                if (!$availability) {
                    if ($expirationMsg != "") {
                        return "<p class='wdm_message'>" . apply_filters('wdm_expiration_message', $expirationMsg, $product) . "</p>";
                    } else {
                        return "";
                    }
                }
                return $link;
            }

            /**
             * This function is hooked to the shortcode 'wdm_scheduler_product_page_timer'
             * This can be used to display the timer on the custom product pages
             * eg. elementor custom product page
             *
             * @param [type] $args
             * @return void
             */
            public function productPageShortcode($args) {
                if (!is_product()) {
                    return ;
                }
                $this->wdmProductPageSummary();
            }

            public function wdmProductPageSummary()
            {
                global $product;

                if ($product->get_type() == 'variable') {
                    return;
                }
                $availability = true;
                $availabilityPairs=array();
                $categorySettings=array('startTimer'=>false, 'endTimer'=>false);
                $product_id = wooSchedulerProductId($product);
                $enableSpecificTimer = "";
                $wdm_start_date = get_post_meta($product_id, 'wdm_start_date', true);
                $wdm_end_date = get_post_meta($product_id, 'wdm_end_date', true);

                $wdm_start_time_hr = get_post_meta($product_id, 'wdm_start_time_hr', true);
                $wdm_start_time_min = get_post_meta($product_id, 'wdm_start_time_min', true);

                $wdm_end_time_hr = get_post_meta($product_id, 'wdm_end_time_hr', true);
                $wdm_end_time_min = get_post_meta($product_id, 'wdm_end_time_min', true);

                $selectedDays = get_post_meta($product_id, 'wdm_days_selected', true);
                $wdmwsProductSettings= get_post_meta($product_id, 'wdm_schedule_settings', true);
                
                if (in_array($product->get_type(), array('course', 'simple'))) {
                    $availability = wdmCheckDateValidation($product_id);
                }

                $wdm_start_time = getTime($wdm_start_time_hr, $wdm_start_time_min, 'start');
                $wdm_end_time = getTime($wdm_end_time_hr, $wdm_end_time_min, 'end');
                $catDates = getCategoryDates($product_id);
                $availabilityPairs= get_post_meta($product_id, 'availability_pairs', true);
                if (empty($wdm_start_date) && !empty($catDates)) {
                    $availability        = woo_schedule_check_category_availability($product_id);
                    $availabilityPairs   = maybe_unserialize($catDates[0]->availability_array);
                    $categorySettings['startTimer']=$catDates[0]->start_timer;
                    $categorySettings['endTimer']=$catDates[0]->end_timer;
                    $categorySettings['type']=$catDates[0]->schedule_type;
                    $start_date          = $catDates[0]->start_date;
                    $start_date          = date('Y-m-d h:i:s A', strtotime($start_date));
                    $end_date            = $catDates[0]->end_date;
                    $end_date            = date('Y-m-d h:i:s A', strtotime($end_date));
                    $selectedDays        = maybe_unserialize($catDates[0]->selected_days);
                    $enableSpecificTimer = $catDates[0]->show_timer;
                    $splitStartDate = explode(" ", $start_date);
                    $splitEndDate = explode(" ", $end_date);
                    $wdm_start_date = $splitStartDate[0];
                    $wdm_start_time = $splitStartDate[1]. " ". $splitStartDate[2];
                    $wdm_end_date = $splitEndDate[0];
                    $wdm_end_time = $splitEndDate[1]. " ". $splitEndDate[2];
                }

                if (empty($wdm_start_date) && !woo_schedule_check_category_availability($product_id)) {
                    return;
                }


                $curr_time = current_time('h:i:s A');
                $curr_date = date('m/d/Y');

                $wdmwsSettings = \Includes\AdminSettings\WdmWsSettings::getSettings();
                
                if (!$availability) {
                    update_post_meta($product_id, 'availability_flag', 'no');
                    showTimerWhenProductUnavailable($product_id, $wdmwsSettings, $curr_date, $wdm_end_date, $curr_time, $wdm_end_time, $wdmwsProductSettings, $availabilityPairs, $categorySettings);
                    wdmwsShowNotifyMeButton($product_id, $wdmwsSettings);
                } else {
                    update_post_meta($product_id, 'availability_flag', 'yes');
                    showFinishTimer($product_id, $wdmwsProductSettings, $availabilityPairs, $categorySettings, $wdmwsSettings);
                }
            }

            /**
             * Redirect the user to the login page when guest user enrollment
             * is disabled.
             */
            public function redirectToLoginPage()
            {
                if (isset($_POST['wdmws-notify-me-btn-req-login'])) {
                    auth_redirect();
                }
            }

            /**
             * Remove unavailable product from the cart.
             */
            public function removeUnavailableProductFromCart()
            {
                // Run only in the Cart or Checkout Page
                $removedProducts=array();
                if (is_cart() || is_checkout()) {
                    // Cycle through each product in the cart
                    foreach (WC()->cart->cart_contents as $cartItemKey => $prodInCart) {
                        // Get the Variation or Product ID
                        $productId = (isset($prodInCart['variation_id']) && $prodInCart['variation_id'] != 0) ? $prodInCart['variation_id'] : $prodInCart['product_id'];
                        $availability = true;
                        $product = wc_get_product($productId);
                        if (in_array($product->get_type(), array('course', 'simple'))) {
                            $availability = wdmCheckDateValidation($productId);
                        } elseif ('variation' == $product->get_type()) {
                            $availability = wdmCheckDateValidation($productId, 'variant');
                        }

                        // If product is available.
                        if ($availability) {
                            continue;
                        }
                        $removedProducts[]=WC()->cart->cart_contents[$cartItemKey]['data']->get_title();
                        unset(WC()->cart->cart_contents[$cartItemKey]);
                    }
                    if (sizeof($removedProducts)>0) {
                        $removedProducts = implode(", ", $removedProducts);
                        wc_add_notice(__('Sorry, following products have been removed from your cart due to the unavailability :'.$removedProducts), 'error');
                    }
                }
            }
        }
        new SchedulerFrontend();
    }
}
