<?php
namespace {
    
    include_once("class-scheduler-admin-functions.php");
    if (!class_exists('SchedulerAdmin')) {
        class SchedulerAdmin extends SchedulerAdminFunctions
        {
            public function __construct()
            {
                //add_filter('product_type_options', array($this, 'wdmAddHideProductOption'));
                add_action('save_post_product', array($this, 'wdmProductExpirationSave'));
                add_filter('woocommerce_available_variation', array($this, 'wdmAvailableVariation'), 10, 1);

                //Cron action
                add_action('update_product_status_start', array($this, 'wdmChangePostStatus'));
                add_action('update_product_status_end', array($this, 'wdmChangePostStatus'));
                add_action('update_category_status_start', array($this, 'wdmChangeCategoryStatus'), 10, 1);
                add_action('update_category_status_end', array($this, 'wdmChangeCategoryStatus'), 10, 1);

                add_action('woocommerce_product_after_variable_attributes', array($this, 'wdmVariationSettingsFields'), 10, 3);
                add_action('woocommerce_ajax_save_product_variations', array($this, 'wdmWoocommerceAjaxSaveProductVariations'), 10, 1);
                add_action('woocommerce_product_options_pricing', array($this, 'showScheduleFields'));

                // Settings on admin page
                add_filter('wdmws_add_tab_to_workflow_tabs', array($this, 'wdmShouldTabBeAdded'), 20, 2);
                add_filter('piklistfw_pre_update_option', array($this, 'wdmAddUnsubscriptionShortcode'), 40, 4);
            }

            public function wdmChangeCategoryStatus($catId)
            {
                remove_action('save_post_product', array($this, 'wdmProductExpirationSave'));
                global $wpdb;
                $relationshipTable = $wpdb->prefix.'term_relationships';
                $products = $wpdb->get_results("SELECT object_id as product_id FROM {$relationshipTable} WHERE term_taxonomy_id = {$catId}");

                $categoryAvailable=wdm_check_category_availability($catId);
                $hideOptionForCat = wdm_get_is_cat_hide_unavailable($catId);

                foreach ($products as $key => $value) {
                    unset($key);
                    $productLevelSchedule = get_post_meta($value->product_id, 'wdm_schedule_settings', true);//product level schedule
                    //$categoryScheduled=get_post_meta($value->product_id, 'wdm_show_cat_product', true);
                    $current_post = wc_get_product($value->product_id);

                    if ($current_post->get_type()=="variable") {
                        $childrens=$current_post->get_children();
                        foreach ($childrens as $childId) {
                            $product=get_post($childId, 'ARRAY_A');
                            $variantLevelSchedule=get_post_meta($childId, 'wdm_schedule_settings', true) ;
                            if (empty($variantLevelSchedule) && $hideOptionForCat) {
                                if (!$categoryAvailable) {
                                    $product['post_status'] = 'private';
                                    update_post_meta($childId, 'wdm_show_cat_product', 'no');
                                } else {
                                    update_post_meta($childId, 'wdm_show_cat_product', 'yes');
                                    $product['post_status'] = 'publish';
                                }
                                wp_update_post($product);
                            }
                        }
                    } else {
                        $current_post = get_post($value->product_id, 'ARRAY_A');
                        if (empty($productLevelSchedule) && $hideOptionForCat) {
                            if (!$categoryAvailable && $current_post['post_status'] == 'publish') {
                                $current_post['post_status'] = 'draft';
                                update_post_meta($value->product_id, 'wdm_show_cat_product', 'no');
                            } else {
                                update_post_meta($value->product_id, 'wdm_show_cat_product', 'yes');
                                $current_post['post_status'] = 'publish';
                            }
                            wp_update_post($current_post);
                        }
                    }

                    if (empty($productLevelSchedule)) {
                        $this->wdmChangePostStatus();
                    }
                }
            }

            // public function wdmAddHideProductOption($product_type_options)
            // {
            //     global $post;
            //     $value=get_post_meta($post->ID, '_hide_if_unavailable', true);
            //     $product_type_options['_hide_if_unavailable'] =  array(
            //     'id'            => '_hide_if_unavailable',
            //     'wrapper_class' => 'show_if_simple show_if_variable',
            //     'label'         => __('Hide When Unavailable', WDM_WOO_SCHED_TXT_DOMAIN),
            //     'description'   => __('Display product only in scheduled time', WDM_WOO_SCHED_TXT_DOMAIN),
            //     'default'       => $value,
            //     );
               
            //     return $product_type_options;
            // }

            public function getTime($Hour, $min)
            {
                if (!empty($Hour) && !empty($min)) {
                    return $Hour . ':' . $min;
                }
            }


            public function showScheduleFields()
            {
                global $post;
                if (isset($post) && isset($post->post_type) && $post->post_type == "product") {
                    $product = wc_get_product($post->ID);
                    if (in_array($product->get_type(), array('course', 'simple'))) {
                        $this->wdmStartEndDate();
                    }
                }
            }

            /**
             * Name: getWdmScheduleForm
             * This method is used to display multistep scheduler form on the simple product edit page
             * & variation edit sections in case of variable product. This form contains the steps for
             * multiple types of schedules including Launch Product, Product availability "Whole Days
             * during selected duration" & Product availability "Limited Time during selected duration".
             *
             * @param int $productId
             * @param string $wdm_schedule_type
             * @return void
             */
            public function getWdmScheduleForm($productId, $schedulerData, $wdmProductSettings)
            {
                ?>
                <div class="wisdmScheduler" >
                <div id="wdmSingleForm[<?php echo $productId;?>]" class="wdm-form-container wdmSingleForm">
                
                <?php
                    $type= $this->getFirstFormStep($productId, $schedulerData, $wdmProductSettings);
                    $typeTwo = $this->getSecondFormStep($productId, $type);
                    $this->getThirdFormStepWholeDay($productId, $schedulerData, $wdmProductSettings, $type, $typeTwo);
                    $this->getThirdFormStepSpecificTime($productId, $schedulerData, $wdmProductSettings, $type, $typeTwo);
                    $this->getThirdFormStepLaunch($productId, $schedulerData, $type);
                    $this->wdmGetFinalStep($productId, $schedulerData, $wdmProductSettings, $type, $typeTwo);
                    $this->getFormStatusStep($productId, $schedulerData, $wdmProductSettings);
                    echo "</div>";
            }

   
            public function productTypeVariable($product)
            {
                if ($product->is_type('variable')) {
                   // a variable product
                    return false;
                }
            }


            public function showCategoryMsg($catDates)
            {
                if (!empty($catDates)) {
                    echo '<div id="cat_msg" class="updated settings-error notice is-dismissible"><p><strong>'.__("This product is also scheduled on category level.", WDM_WOO_SCHED_TXT_DOMAIN).'</strong></p></div>';
                }
            }

            public function showDraftMsg($post, $product)
            {
                if ((isset($post->post_status) && $post->post_status == "draft") || (is_callable(array($product, 'get_status')) && $product->get_status() == "private")) {
                    echo '<div id="message" class="error wdm_ws_publish settings-error notice is-dismissible"><p>'.__('This product will not be shown to public', WDM_WOO_SCHED_TXT_DOMAIN).'</p></div>';
                }
            }

            public function showOutStockMsg($product)
            {
                if (!$product->is_in_stock()) {
                    echo '<div id="message" class="error wdm_ws_outstock settings-error notice is-dismissible"><p>'.__('The Product you are scheduling is out of stock. If you still wish to continue, you can save and update.', WDM_WOO_SCHED_TXT_DOMAIN).'</p></div>';
                }
            }

            /**
 * Add fields on edit product page
 * @param  string $post [description]
 * @return [type]       [description]
 */
            public function wdmStartEndDate($post = '')
            {
                //$wdmwsSettings = \Includes\AdminSettings\WdmWsSettings::getSettings();
                if (empty($post)) {
                    global $post;
                    $curr_post_id=0;
                    $is_simple = "wdm_simple_schedule";
                    $curr_post_id=$post->ID;
                    $product = wc_get_product($curr_post_id);
                    $this->productTypeVariable($product);
                } else {
                    $curr_post_id=$post;
                    $is_simple = "";
                }
        
                $product = wc_get_product($curr_post_id);
                
                $wdm_start_date  = get_post_meta($curr_post_id, 'wdm_start_date', true);
                $wdm_end_date    = get_post_meta($curr_post_id, 'wdm_end_date', true);
                
                //For Start time and End time
                $wdm_start_time_hr  = get_post_meta($curr_post_id, 'wdm_start_time_hr', true);
                $wdm_end_time_hr    = get_post_meta($curr_post_id, 'wdm_end_time_hr', true);
                $wdm_start_time_min = get_post_meta($curr_post_id, 'wdm_start_time_min', true);
                $wdm_end_time_min   = get_post_meta($curr_post_id, 'wdm_end_time_min', true);
                
                
                $wdm_show_timer = get_post_meta($curr_post_id, 'wdm_show_timer', true);
                $wdm_show_timer_enable = isset($wdm_show_timer) && $wdm_show_timer == 1 ? 'checked' : "";

                $wdm_start_time = $this->getTime($wdm_start_time_hr, $wdm_start_time_min);
            
                $wdm_end_time = $this->getTime($wdm_end_time_hr, $wdm_end_time_min);

                //$wdmwsShowTimer = isset($wdmwsSettings) && isset($wdmwsSettings['wdmws_display_timer']) ? $wdmwsSettings['wdmws_display_timer'] : '' ;
                $wdmProductSettings  = get_post_meta($curr_post_id, 'wdm_schedule_settings', true);
                
                $catDates = getCategoryDates($curr_post_id);

                $this->showCategoryMsg($catDates);

                $this->showDraftMsg($post, $product);
                
                $this->showOutStockMsg($product);
                
                echo '<div id="message" class="error wdm_ws_stock settings-error notice is-dismissible" style = "display:none"><p>'.__('The Product you are scheduling is out of stock. If you still wish to continue, you can save and update.', WDM_WOO_SCHED_TXT_DOMAIN).'</p></div>';
                $daysSelected=get_post_meta($curr_post_id, 'wdm_days_selected', true);
                $hideUnavailable=get_post_meta($curr_post_id, '_hide_if_unavailable', true);
                $schedulerData=array("wdm_start_date"       => $wdm_start_date,
                                    "wdm_end_date"          => $wdm_end_date,
                                    "wdm_start_time"        => $wdm_start_time,
                                    "wdm_end_time"          =>$wdm_end_time,
                                    "wdm_show_timer"        =>$wdm_show_timer_enable,
                                    "wdm_hide_unavailable"  =>$hideUnavailable,
                                    "wdm_date_array"        =>$daysSelected,
                                    );
                $this->getWdmScheduleForm($curr_post_id, $schedulerData, $wdmProductSettings);
            }

            
            /**
             * Adds the product to availability array when the product is marked hiddn during unavailability
             * marks the product as draft.
             * when option hideUnavaiable is not set removes the product from the array and publish it.
             *
             * @param [type] $post_id
             * @param [type] $product_is_scheduled
             * @return void
             */
            public function updateAvailableSimpleProducts($post_id, $product_is_scheduled)
            {
                $avaliable_products = get_option('wdm_avaliable_products');
                if (! $avaliable_products) {
                    $avaliable_products = array();
                }
                if (isset($_POST['wdmHideUnavailabe']) && $product_is_scheduled) {
                    update_post_meta($post_id, 'wdm_show_product', 'no');
                    $avaliable_products = $this->addPostInAvailableInArray($post_id, $avaliable_products);
                    $this->wdmSingleChangePostStatus($post_id);
                } else {
                    $avaliable_products = array_diff($avaliable_products, array($post_id));
                    
                    $this->wdmPublishProduct($post_id);
                    if ($product_is_scheduled) {
                        update_post_meta($post_id, 'wdm_show_product', 'yes');
                    } else {
                        delete_post_meta($post_id, 'wdm_show_product');
                    }
                }
                update_option('wdm_avaliable_products', $avaliable_products);
            }

            public function addPostInAvailableInArray($post_id, $postArray)
            {
                if (! in_array($post_id, $postArray)) {
                    array_push($postArray, $post_id);
                }
                return $postArray;
            }

            public function getHideVariants($hide_variants, $variants)
            {
                foreach ($variants as $vid) {
                    $hide_variants = $this->addPostInAvailableInArray($vid, $hide_variants);
                }

                return $hide_variants;
            }

            public function getUnhideVariants($unhide_variants, $variants)
            {
                foreach ($variants as $vid) {
                    $unhide_variants[] = $vid;

                    $current_post = get_post($vid, 'ARRAY_A');
                    $current_post['post_status'] = 'publish';
                    update_post_meta($current_post['ID'], 'availability_flag', 'yes');
                    update_post_meta($current_post['ID'], 'wdm_show_product', 'yes');
                    wp_update_post($current_post);
                }

                return $unhide_variants;
            }

            public function updateAvailableVariableProducts($post_id)
            {
                $args = array(
                'post_parent' => $post_id,
                'post_type'   => 'product_variation',
                'numberposts' => -1,
                'post_status' => 'any'
                );

                $variants = get_children($args, ARRAY_A);
                $variants = array_keys($variants);
                $variationsNotHiddenWhenUnavailable=array();
                $hide_variants = get_option('wdm_hide_variants');
                if (!isset($hide_variants) ||  empty($hide_variants)) {
                    $hide_variants = array();
                }

                $prnt_vrbl = get_option('wdm_parent_variable_prods');
                if (!isset($prnt_vrbl) || empty($prnt_vrbl)) {
                    $prnt_vrbl = array();
                }
                foreach ($variants as $variationId) {
                    $hideUnavailable=get_post_meta($variationId, '_hide_if_unavailable', true);
                    if ("yes"==$hideUnavailable) {
                        //add variation to availability array and change product status;
                        $hide_variants=$this->addPostInAvailableInArray($variationId, $hide_variants);
                        $this->wdmSingleChangePostStatus($variationId);
                    } elseif ("no"==$hideUnavailable) {
                        $variationsNotHiddenWhenUnavailable[]=$variationId;
                        $this->wdmPublishProduct($variationId);
                        //remove from variation availability array and change status to publish
                    } else {
                        //not scheduled variant
                        $variationsNotHiddenWhenUnavailable[]=$variationId;
                    }
                }
                $hide_variants = array_diff($hide_variants, $variationsNotHiddenWhenUnavailable);
                
                if (empty($variationsNotHiddenWhenUnavailable)) {
                    $prnt_vrbl=$this->addPostInAvailableInArray($post_id, $prnt_vrbl);
                    update_post_meta($post_id, '_hide_if_unavailable', 'yes');
                    update_post_meta($post_id, 'wdm_show_product', 'yes');
                } else {
                    update_post_meta($post_id, '_hide_if_unavailable', 'no');
                    delete_post_meta($post_id, 'wdm_show_product');
                    $prnt_vrbl=array_diff($prnt_vrbl, array($post_id));
                }
                update_option('wdm_hide_variants', $hide_variants);
                $prnt_vrbl = apply_filters('wdm_parent_staus_change', $prnt_vrbl);
                update_option('wdm_parent_variable_prods', $prnt_vrbl);
            }

        /**
         * update the schedular meta data
         * @param  [type] $post_id [description]
         * @return [type]          [description]
         */
            public function wdmProductExpirationSave($post_id = '')
            {
                //prevent scheduler rehooking if the product is auto-drafted
                $current_post = get_post($post_id, 'ARRAY_A');
                if ($current_post['post_status'] == 'auto-draft' || 'draft'==$current_post['post_status']) {
                    return;
                }

                //Validate and save scheduler data to the database.
                $this->wdmExpiration($_POST);
                
                if (!empty($post_id)) {
                    // update|save product event
                    remove_action('save_post_product', array($this, 'wdmProductExpirationSave'));

                    $curr_prod = wc_get_product($post_id);
                    setProductWiseCron($curr_prod, $post_id, 'product');

                    $product_is_scheduled=$this->wdmCheckIfScheduled($post_id);
                    if (in_array($curr_prod->get_type(), array('course', 'simple'))) {
                        $this->updateAvailableSimpleProducts($post_id, $product_is_scheduled);
                    } elseif ('variable'==$curr_prod->get_type()) {
                        $this->updateAvailableVariableProducts($post_id);
                        //$this->changeParentVariableStatus();
                    }
                    add_action('save_post_product', array($this, 'wdmProductExpirationSave'));
                }
            }

            public static function setCronSchedule($scheduleCronArray, $cronText, $post_id, $daysSelected)
            {
                if (empty($daysSelected)) {
                    return;
                }
                
                if (!empty($scheduleCronArray)) {
                    foreach ($scheduleCronArray as $cronTime) {
                        $day = date('l', $cronTime);
                        if (isset($daysSelected[$day]) && $daysSelected[$day] == 'on') {
                            update_post_meta($post_id, 'is_cron_set', true);
                            wp_schedule_single_event($cronTime, $cronText, array($post_id));
                        }
                    }
                }
            }

            /*
             * Publish the given Product
             */

            public function wdmPublishProduct($pid)
            {
                $product_is_scheduled   = get_post_meta($pid, 'wdm_schedule_settings', true);
                $current_post           = get_post($pid, 'ARRAY_A');
                $productAvailable       = wdmCheckDateValidation($pid, $current_post['post_type']);
                $productId=$pid;
                if ($current_post['post_type']=="product_variation") {
                    $productId=(int)$current_post['post_parent'];
                }
                $product_category_scheduled_hidden=$this->wdmProductCategoryScheduledAndHidden($productId);
            
                if (!empty($product_is_scheduled)) {
                    $current_post['post_status'] = 'publish';
                } else {
                    if ($product_category_scheduled_hidden) {
                        $current_post['post_status'] = 'draft';
                        if ($current_post['post_type']=="product_variation") {
                            $current_post['post_status'] = 'private';
                        }
                    }
                }
                wp_update_post($current_post);
                
                if (!empty($current_post)) {
                    if ($productAvailable) {
                        update_post_meta($pid, 'availability_flag', 'yes');
                        update_post_meta($pid, 'wdm_show_product', 'yes');
                    } else {
                        update_post_meta($pid, 'availability_flag', 'no');
                    }
                    if ($current_post['post_status'] != 'publish' && $current_post['post_status'] != 'trash' && $current_post['post_status'] != 'private' && $current_post['post_status'] != 'draft') {
                        $current_post['post_status'] = 'publish';
                        update_post_meta($pid, 'availability_flag', 'yes');
                        update_post_meta($pid, 'wdm_show_product', 'yes');
                        wp_update_post($current_post);
                    }
                }
            }

            /**
             * Tells if product is scheduled at the categopry level & currently hidden  &
             * if the most recently created post category schedule is marked
             * hidden when unavailable set post meta wdm_show_cat_product to "no"
             *
             */
            public function wdmProductCategoryScheduledAndHidden($product_id)
            {
                global $wpdb;
                $category_ids=getwdmProductCategories($product_id);
                
                if (!empty($category_ids)) {
                    $currentDateTime        = strtotime(current_time('Y-m-d H:i:s'));
                    //query to get the most rescent scheduled term from the scheduled product terms.
                    $table=$wpdb->prefix.'woo_schedule_category';
                    $availability_query     = "SELECT * FROM $table WHERE term_id IN (" . implode(',', $category_ids) . ") HAVING MAX(last_updated)";
                    $termScheduleData       = $wpdb->get_results($availability_query, ARRAY_A);
                    if (!empty($termScheduleData)) {
                        //one or more terms are scheduled
                        $categoryDisplay=$termScheduleData[0]['hide_unavailable']=="true"?'no':'yes';
                        $availabilityPairs=maybe_unserialize($termScheduleData[0]['availability_array']);
                        if (getTermAvailabilityFromAvailabilityPairs($currentDateTime, $availabilityPairs)) {
                            update_post_meta($product_id, 'wdm_show_cat_product', 'yes');
                        } else {
                            update_post_meta($product_id, 'wdm_show_cat_product', $categoryDisplay);
                            if ("no"==$categoryDisplay) {
                                return true;
                            }
                        }
                    }
                }
                return false;
            }

            /*
             * WooCommerce Add to cart validation
             */
            public function wdmAvailableVariation($args)
            {
                $available = false;
                $wdm_start_date = get_post_meta($args['variation_id'], 'wdm_start_date', true);

                if (empty($wdm_start_date)) {
                    $post = get_post($args['variation_id']);
                    $post_parent = isset($post) ? $post->post_parent : "";
                    $available = empty($wdm_start_date) && woo_schedule_check_category_availability($post_parent);
                } else {
                    $available = wdmCheckDateValidation($args['variation_id'], 'variant');
                }

                $args['variation_is_visible'] = $available;

                if (false == $available) {
                    $wdmwsSettings = \Includes\AdminSettings\WdmWsSettings::getSettings();
                    ob_start();
                    wdmwsShowNotifyMeButton($args['variation_id'], $wdmwsSettings, false);
                    $notifyHTML = ob_get_clean();
                    $args['wdmws_notify_button'] = $notifyHTML;
                }
                return $args;
            }

            /**
             * Changes Single Post Status on Save
             * @return [type] [description]
             */
            public function wdmSingleChangePostStatus($pid)
            {
                $flag_publish = false;
                $curr_prod = wc_get_product($pid);
                if (in_array($curr_prod->get_type(), array('course', 'simple'))) {
                    $current_post = get_post($pid, 'ARRAY_A');
                    if (!empty($current_post)) {
                        if (wdmCheckDateValidation($pid, 'simple')) {
                            if ($current_post['post_status'] != 'publish') {
                                $current_post['post_status'] = 'publish';
                                update_post_meta($current_post['ID'], 'availability_flag', 'yes');
                                update_post_meta($current_post['ID'], 'wdm_show_product', 'yes');
                                wp_update_post($current_post);
                            } else {
                                update_post_meta($current_post['ID'], 'availability_flag', 'yes');
                                update_post_meta($current_post['ID'], 'wdm_show_product', 'yes');
                            }
                        } else {
                            $current_post['post_status'] = 'draft';
                            update_post_meta($current_post['ID'], 'availability_flag', 'no');
                            $hide = get_post_meta($current_post['ID'], '_hide_if_unavailable', true);
                            if ($hide == "yes") {
                                update_post_meta($current_post['ID'], 'wdm_show_product', 'no');
                            }
                            wp_update_post($current_post);
                        }
                    }
                } elseif ($curr_prod->get_type()=="variation") {
                    $current_post = get_post($pid, 'ARRAY_A');
                    if (empty($current_post)) {
                        return;
                    }

                    if (wdmCheckDateValidation($pid, 'variant')) {
                        if ($current_post['post_status'] != 'publish') {
                            $current_post['post_status'] = 'publish';
                            $flag_publish = true;
                            update_post_meta($current_post['ID'], 'wdm_show_product', 'yes');
                            update_post_meta($current_post['ID'], 'availability_flag', 'yes');
                            wp_update_post($current_post);
                        } else {
                            update_post_meta($current_post['ID'], 'wdm_show_product', 'yes');
                            update_post_meta($current_post['ID'], 'availability_flag', 'yes');
                        }
                    } else {
                        $start_date = get_post_meta($pid, 'wdm_start_date', true);
                        if (empty($start_date)) {
                            $current_post['post_status'] = 'publish';
                        } else {
                            $current_post['post_status'] = 'private';
        
                            $hide = get_post_meta($current_post['ID'], '_hide_if_unavailable', true);
                            if ($hide == "yes") {
                                update_post_meta($current_post['ID'], 'wdm_show_product', 'no');
                            }
                                update_post_meta($current_post['ID'], 'availability_flag', 'no');
                                wp_update_post($current_post);
                        }
                    }
                }
                return $flag_publish;
            }

/**
 * Cron callback function
 * @return [type] [description]
 */
            public function wdmChangePostStatus()
            {
                remove_action('save_post_product', array($this, 'wdmProductExpirationSave'));
                $avaliable_products = get_option('wdm_avaliable_products', array());
                foreach ($avaliable_products as $pid) {
                    wc_delete_product_transients($pid);
                    $current_post = get_post($pid, 'ARRAY_A');
                    if (!empty($current_post)) {
                        if (wdmCheckDateValidation($pid, 'simple')) {
                            $this->wdmRemoveFromAvailableArrayIfLaunched($pid, $current_post['post_type'], $avaliable_products);
                            if ($current_post['post_status'] != 'publish') {
                                $current_post['post_status'] = 'publish';
                                update_post_meta($current_post['ID'], 'wdm_show_product', 'yes');
                                update_post_meta($current_post['ID'], 'availability_flag', 'yes');
                                wp_update_post($current_post);
                            } else {
                                update_post_meta($current_post['ID'], 'wdm_show_product', 'yes');
                                update_post_meta($current_post['ID'], 'availability_flag', 'yes');
                            }
                        } else {
                            $current_post['post_status'] = 'draft';
                            $hide = get_post_meta($current_post['ID'], '_hide_if_unavailable', true);
                            if ($hide == "yes") {
                                update_post_meta($current_post['ID'], 'wdm_show_product', 'no');
                            }
                            update_post_meta($current_post['ID'], 'availability_flag', 'no');
                            wp_update_post($current_post);
                        }
                    }
                }

                $hide_variants = get_option('wdm_hide_variants');
                if (isset($hide_variants)&& !empty($hide_variants)) {
                    foreach ($hide_variants as $vid) {
                        wc_delete_product_transients($vid);
                        $current_post = get_post($vid, 'ARRAY_A');

                        if (empty($current_post)) {
                            continue;
                        }

                        if (wdmCheckDateValidation($vid, 'variant')) {
                            if ($current_post['post_status'] != 'publish') {
                                $current_post['post_status'] = 'publish';
                                $this->wdmRemoveFromAvailableArrayIfLaunched($vid, $current_post['post_type'], $hide_variants);
                                wp_update_post($current_post);
                                update_post_meta($current_post['ID'], 'wdm_show_product', 'yes');
                                update_post_meta($current_post['ID'], 'availability_flag', 'yes');
                            } else {
                                update_post_meta($current_post['ID'], 'wdm_show_product', 'yes');
                                update_post_meta($current_post['ID'], 'availability_flag', 'yes');
                            }
                        } else {
                            $current_post['post_status'] = 'private';
                            $hide = get_post_meta($current_post['ID'], '_hide_if_unavailable', true);
                            if ($hide == "yes") {
                                update_post_meta($current_post['ID'], 'wdm_show_product', 'no');
                            }
                            update_post_meta($current_post['ID'], 'availability_flag', 'no');
                            wp_update_post($current_post);
                        }
                    }
                }
                $this->changeParentVariableStatus();
            }

/**
 * Change status of variable product and its variations
 * @return [type] [description]
 */
            public function changeParentVariableStatus()
            {
                $prnt_vrbl = get_option('wdm_parent_variable_prods');
                if (isset($prnt_vrbl)&& !empty($prnt_vrbl)) {
                    foreach ($prnt_vrbl as $vid) {
                        $product = wc_get_product($vid);
                        if ('variable'!=$product->get_type()) {
                            continue;
                        }
                        $args = array(
                           'post_parent' => $vid,
                           'post_type'   => 'product_variation',
                           'numberposts' => -1,
                           'post_status' => 'any'
                           );

                        $variants = get_children($args, ARRAY_A);

                        $total_variants = count($variants);

                        $private_variants = 0;

                        $args = array(
                        'post_parent' => $vid,
                        'post_type'   => 'product_variation',
                        'numberposts' => -1,
                        'post_status' => 'private'
                        );
                        $variants = get_children($args, ARRAY_A);
                        $private_variants = count($variants);
                        $hideParentProduct = apply_filters('wdmws_hide_parent_product_when_no_child_available', true, $vid);
                        if (($total_variants == $private_variants) && $hideParentProduct) {
                            $parent_variable = get_post($vid, 'ARRAY_A');
                            $parent_variable['post_status'] = 'draft';
                            update_post_meta($vid, 'availability_flag', 'no');
                            $hide = get_post_meta($vid, '_hide_if_unavailable', true);
                            if ($hide == "yes") {
                                update_post_meta($vid, 'wdm_show_product', 'no');
                            }
                            wp_update_post($parent_variable);
                        } else {
                            update_post_meta($vid, '_stock_status', 'instock');
                            $parent_variable = get_post($vid, 'ARRAY_A');
                            $parent_variable['post_status'] = 'publish';
                            update_post_meta($vid, 'availability_flag', 'yes');
                            update_post_meta($vid, 'wdm_show_product', 'yes');
                            wp_update_post($parent_variable);
                        }
                    }
                }
                add_action('save_post_product', array($this, 'wdmProductExpirationSave'));
            }


    /**
 * Set per minute cron
 * @param  [type] $schedules [description]
 * @return [type]            [description]
 */
            public function wdmWsAddCronSchedule($schedules)
            {
                $schedules['wdm_per_minute'] = array(
                'interval' => 60,
                'display'  => 'wdm_per_minute',
                );
                return $schedules;
            }



            //Add Variation Settings
        /**
         * display meta in all variations
         * @param  [type] $loop           [description]
         * @param  [type] $variation_data [description]
         * @param  [type] $variation      [description]
         * @return [type]                 [description]
         */
            public function wdmVariationSettingsFields($loop, $variation_data, $variation)
            {
                unset($loop);
                unset($variation_data);
                if (isset($variation) && !empty($variation)) {
                    $this->wdmStartEndDate($variation->ID);
                }
            }


        /**
         * Call function for variable product save
         * @param  [type] $pid [description]
         * @return [type]      [description]
         */
            public function wdmWoocommerceAjaxSaveProductVariations($pid)
            {
                $pid;
                $this->wdmProductExpirationSave();
            }


        /**
         * Check expiration date of product
         * @param  [type] $wdm_start_date_array [description]
         * @param  [type] $wdm_end_date_array   [description]
         * @param  [type] $days_of_week_array   [description]
         * @param  [type] $wdm_start_time_array [description]
         * @param  [type] $wdm_end_time_array   [description]
         * @return [type]                       [description]
         */
            public function wdmExpiration($post_data)
            {
                if (!empty($post_data['variable_post_id'])) {
                    //variable product "save variations"
                    $variations= $post_data['variable_post_id'];
                    foreach ($variations as $variationId) {
                        $scheduleData=wdmGetPostScheduleDataToSave($variationId);
                        $scheduleData=wdmValidateSchedule($scheduleData);
                        if (isset($scheduleData['errorMessage'])) {
                            $this::wdmDeleteAllScheduleMeta($variationId);
                        } else {
                            $this->saveNewScheduleData($variationId, $scheduleData);
                        }
                    }
                } elseif (!empty($post_data['post_ID'])) {
                    $scheduleData=wdmGetPostScheduleDataToSave($post_data['post_ID']);
                    $scheduleData=wdmValidateSchedule($scheduleData);
                    if (isset($scheduleData['errorMessage'])) {
                        $this::wdmDeleteAllScheduleMeta($post_data['post_ID']);
                        return;
                    }
                    $this->saveNewScheduleData($post_data['post_ID'], $scheduleData);
                }
            }


           

            public function getWeekArray($post_data, $wdm_week_array, $type)
            {
                if ($type == "per_day" && isset($post_data['days_of_week'])) {
                    $wdm_week_array = $post_data['days_of_week'];
                }

                return $wdm_week_array;
            }



        /**
         * echo value checked or not for days
         * @param  [type] $options [description]
         * @param  [type] $day     [description]
         * @return [type]          [description]
         */
            public function wdmChecked($options, $day)
            {
                if (isset($options[$day])) {
                    return 'checked';
                } else {
                    return '';
                }
            }

            public function wdmShouldTabBeAdded($shouldAdded, $data)
            {
                $wdmwsSettings = \Includes\AdminSettings\WdmWsSettings::getSettings();

                $isNotifyEnabled = isset($wdmwsSettings['wdmws_enable_notify']) && '1' == $wdmwsSettings['wdmws_enable_notify'] ? true : false;

                if (!$isNotifyEnabled && isset($data['title']) && 'Notify User' == $data['title']) {
                    return false;
                }
                return $shouldAdded;
            }

            /**
             * Add 'SCHEDULER_UNSUBSCRIPTION' shortcode.
             */
            public function wdmAddUnsubscriptionShortcode($settings, $setting, $new, $old)
            {
                $isNotifyFeatureEnabled = isset($settings['wdmws_enable_notify']) ? $settings['wdmws_enable_notify'] : '';
                $shortcode = 'SCHEDULER_UNSUBSCRIPTION';

                if ('1' == $isNotifyFeatureEnabled) {
                    $unsubscriptionPageId = isset($settings['wdmws_unsubscription_page']) ? $settings['wdmws_unsubscription_page'] : '-1';

                    if ('-1' == $unsubscriptionPageId) {
                        $myAccountPageId = get_option('woocommerce_myaccount_page_id');
                        wdmwsAddShortcodeOnPage($myAccountPageId, $shortcode);
                        $settings['wdmws_unsubscription_page'] = $myAccountPageId;
                    } else {
                        $oldPageId = isset($old['wdmws_unsubscription_page']) ? $old['wdmws_unsubscription_page'] : '-1';

                        if ($oldPageId != $unsubscriptionPageId) {
                            if ('-1' != $oldPageId) {
                                wdmwsRemoveShortcodeFromPage($oldPageId, $shortcode);
                            }
    
                            if ('-1' != $unsubscriptionPageId) {
                                wdmwsAddShortcodeOnPage($unsubscriptionPageId, $shortcode);
                            }
                        } else {
                            wdmwsAddShortcodeOnPage($unsubscriptionPageId, $shortcode);
                        }
                    }
                } else {
                    $unsubscriptionPageId = isset($settings['wdmws_unsubscription_page']) ? $settings['wdmws_unsubscription_page'] : '-1';
                    if ('-1' != $unsubscriptionPageId) {
                        wdmwsRemoveShortcodeFromPage($unsubscriptionPageId, $shortcode);
                    }
                }
                
                unset($setting);
                unset($new);
                return $settings;
            }

            /**
             * Render confirmation alert for sending email now.
             */
            public function showSendMailNowConfirmation()
            {
                $modalHeader = __('Send Email Now?', WDM_WOO_SCHED_TXT_DOMAIN);
                $emailNowText = __('Some crons may not be set. Do you want to send the notification email for those crons now?<br>Please review email template before sending notification email.', WDM_WOO_SCHED_TXT_DOMAIN);
                $saveSend = __('Save Settings and Send Mail Now', WDM_WOO_SCHED_TXT_DOMAIN);
                $saveWithoutSend = __('Save Settings Without Sending Mail', WDM_WOO_SCHED_TXT_DOMAIN);
                $withoutSaveSend = __('Don\'t Send Mail and Don\'t Save Settings', WDM_WOO_SCHED_TXT_DOMAIN);

                ?>
                <div class="modal wdmws-send-email-now-modal" id="wdmws-send-email-now-modal" aria-labelledby="wdmws_send-email-modal-title">
                <div class="modal-dialog">
                  <div class="modal-content">
                  
                      <!-- Modal Header -->
                      <div class="modal-header">
                          <h4 class="modal-title" id="wdmws_send-email-modal-title"><?php echo $modalHeader; ?></h4>
                          <button type="button" class="close" data-dismiss="modal" style="display:inline-block;">&times;</button>
                      </div>
                    
                      <!-- Modal body -->
                      <div class="modal-body">
                          <form class="wdmws-send-email-form-modal">
                              <p><?php echo $emailNowText; ?></p>
                              <button type="button" class="button wdmws-save-send-sbt-modal" id="wdmws-save-send-sbt-modal"><?php echo $saveSend; ?>
                              <button type="button" class="button wdmws-save-wout-send-sbt-modal" id="wdmws-save-wout-send-sbt-modal"><?php echo $saveWithoutSend; ?>
                              <button type="button" class="button wdmws-wout-save-send-sbt-modal" id="wdmws-wout-save-send-sbt-modal"><?php echo $withoutSaveSend; ?>
                          </form>
                      </div>          
                  </div>
                </div>
                </div>
                <?php
            }
        }
        new SchedulerAdmin();
    }

}
