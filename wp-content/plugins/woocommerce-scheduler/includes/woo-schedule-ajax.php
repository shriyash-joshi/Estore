<?php
namespace WooScheduleAjax;

require_once 'woo-schedule-ajax-methods.php';
if (!class_exists('WooScheduleAjax')) {
    class WooScheduleAjax extends WooScheduleMethods
    {

        /**
         * The ID of this plugin.
         *
         * @since    1.0.0
         * @access   private
         * @var      string    $plugin_name    The ID of this plugin.
         */
        private $plugin_name;

        /**
        * The version of this plugin.
        *
        * @since    1.0.0
        * @access   private
        * @var      string    $version    The current version of this plugin.
        */
        private $version;

        public function __construct($plugin_name, $version)
        {

            $this->plugin_name   = $plugin_name;
            $this->version       = $version;

        // callback to send Selection List

            add_action('wp_ajax_handle_selection_type', array($this,'handleSelectionTypeCallback'));
            add_action('wp_ajax_nopriv_handle_selection_type', array($this,'handleSelectionTypeCallback'));

        //callback to fetch selection details

            add_action('wp_ajax_fetch_selections_details', array($this,'fetch_selections_details_callback'));
            add_action('wp_ajax_nopriv_fetch_selections_details', array($this,'fetch_selections_details_callback'));

        //callback to update selection details for particular product / category

            add_action('wp_ajax_update_expiration_details', array($this,'updateExpirationDetailsCallback'));
            add_action('wp_ajax_nopriv_update_expiration_details', array($this,'updateExpirationDetailsCallback'));

            //callback to update selection type when the page is not refreshed
            add_action('wp_ajax_get_expiration_type', array($this,'getExpirationType'));
            add_action('wp_ajax_nopriv_get_expiration_type', array($this,'getExpirationType'));


        //callback to display scheduler setting

            add_action('wp_ajax_display_scheduler_fields', array($this,'displaySchedulerFieldsCallback'));
            add_action('wp_ajax_nopriv_display_scheduler_fields', array($this,'displaySchedulerFieldsCallback'));
        

        //callback to edit scheduler setting
            add_action('wp_ajax_edit_scheduler_fields', array($this,'editSchedulerFieldsCallback'));
            add_action('wp_ajax_nopriv_edit_scheduler_fields', array($this,'editSchedulerFieldsCallback'));
        //callback to remove Product scheduler setting

            add_action('wp_ajax_remove_product_details', array($this,'removeProductDetailsCallback'));
            add_action('wp_ajax_nopriv_remove_product_details', array($this,'removeProductDetailsCallback'));

        //callback to remove Term scheduler setting

            add_action('wp_ajax_remove_term_details', array($this,'removeTermDetailsCallback'));
            add_action('wp_ajax_nopriv_remove_term_details', array($this,'removeTermDetailsCallback'));

        //callback to update product availabilty

            add_action('wp_ajax_update_variation_availability', array($this, 'updateVariationAvailability'));
            add_action('wp_ajax_nopriv_update_variation_availability', array($this, 'updateVariationAvailability'));

            // Notification feature related callbacks
            // callback to response default enrollent email template.
            add_action('wp_ajax_wdmws_reset_enrollment_email_template', array($this, 'returnDefaultEnrollmentEmailTemplate'));

            // callback to response default notification email template.
            add_action('wp_ajax_wdmws_reset_notification_email_template', array($this, 'returnDefaultNotificationEmailTemplate'));

            // callback to register logged in user/ guest user for product notification.
            add_action('wp_ajax_wdmws_product_notification_enrl', array($this, 'productNotificationEnrl'));
            add_action('wp_ajax_nopriv_wdmws_product_notification_enrl', array($this, 'productNotificationEnrl'));

            // callback to return products and corresponding number of users enrolled for product notification.
            add_action('wp_ajax_wdmws_enrolled_product_user_count', array($this, 'enrlProductUserCount'));

            // callback to return users' list for a particular product.
            add_action('wp_ajax_wdmws_product_enrolled_users_list', array($this, 'productEnrolledUsersList'));

            // callback to remove user email from notification list of a particular product
            // when admin clicks on remove button.
            add_action('wp_ajax_wdmws_product_disenroll_user', array($this, 'disenrollUserFromListAdminAction'));

            // callback to export users list when admin clicks on 'Export Data' button.
            add_action('wp_ajax_wdmws_create_users_list_csv', array($this, 'exportUsersListForProduct'));

            // callback to disenroll/ unsubscribe user from the product
            // notification when user clicks on 'Unsubscribe' button.
            add_action('wp_ajax_wdmws_disenroll_user_from_notification', array($this, 'disenrollUserFromListUserAction'));
            add_action('wp_ajax_nopriv_wdmws_disenroll_user_from_notification', array($this, 'disenrollUserFromListUserAction'));
        
            //callback to set feedback option status - wether feedback is given or remind later option selected.
            add_action('wp_ajax_wdmws_set_feedback_status', array($this,'SetWdmwsFeedbackStatus'));
            add_action('wp_ajax_nopriv_wdmws_set_feedback_status', array($this,'setWdmwsFeedbackStatus'));
        }


        //sets the wp option wdmws_feedback_status according to the status of the feedback form
        public function setWdmwsFeedbackStatus()
        {
            if (isset($_POST['wdmFeedbackStatus'])) {
                $status= $_POST['wdmFeedbackStatus'];
                if ($status="remind_later") {
                    //Display modal again after n days when remind me is selected.
                    $remindAfterDays=7;
                    $feedbackDate=current_time('timestamp')+(86400*$remindAfterDays);
                    update_option('wdmws_date_to_ask_for_the_feedback', $feedbackDate);
                }
                update_option('wdmws_feedback_status', $status);
            }
        }

        public function getExpirationType()
        {
            $wdmwsSettings = \Includes\AdminSettings\WdmWsSettings::getSettings();
            $expiration_type = isset($wdmwsSettings) && isset($wdmwsSettings['wdmws_custom_product_expiration_type'][0]) ? $wdmwsSettings['wdmws_custom_product_expiration_type'][0] : 'per_day' ;
            echo $expiration_type;
            die();
        }

        public function updateVariationAvailability()
        {
            spawn_cron();
            $timer_nonce = $_POST['_wdmws_timer'];
            $nonce_verification = wp_verify_nonce($timer_nonce, 'wdmws_timer_nonce');

            if (! $nonce_verification) {
                 die("Security Check");
            }
            $variation_id = isset($_POST['variation_id']) ? $_POST['variation_id'] : "";
            $status = isset($_POST['product_status']) ? $_POST['product_status'] : "";
            $product_id = isset($_POST['product_id']) ? $_POST['product_id'] : "";
            $this->updateProductSpecificFlags($variation_id, $product_id, $status);

            die();
        }

        public function updateProductSpecificFlags($variation_id, $product_id, $status)
        {
            if (!empty($variation_id)) {
                $this->updateFlag($status, $variation_id);
            }

            if (!empty($product_id)) {
                $this->updateFlag($status, $product_id);
            }
        }

        public function checkSystemDate($cron_date, $currentSystemTime)
        {
            if (empty($cron_date) || empty($currentSystemTime)) {
                die("Something Went Wrong");
            }

            if (strtotime($cron_date) > $currentSystemTime) {
                die("no");
            }
        }

        public function updateFlag($status, $product_id)
        {
            if ($status == 'yes') {
                update_post_meta($product_id, 'availability_flag', 'yes');
            }
            if ($status == 'no') {
                update_post_meta($product_id, 'availability_flag', 'no');
            }
        }

        /**
         * check isset for variables
         * @param  [type] $data [description]
         * @return [type]       [description]
         */
        public function wdmCheckIsset($data)
        {
            if (isset($data)) {
                return $data;
            } else {
                return '';
            }
        }


        /*
        * This function is callback of AJAX -- handle_selection_type
        * It check received data is in valid selection and returns list in dropdown.
        */
        public function handleSelectionTypeCallback()
        {

            $selection = isset($_POST['selection'])? trim($_POST['selection']) : '';

            $valid_selection = array('product','category');

            if ($this->wdmCheckSelectionType($selection, $valid_selection)) {
            //As selection is empty -- there must be some error

                echo self::wooScheduleWrongSelection();
                die();
            }

            ob_start();

            $results = array();

            if ($selection === 'product') {
                $results= $this->handleProductSelectionCallType();
            } elseif ($selection === 'category') {
                $taxonomies = array(
                    'product_cat',
                    );

                $args = array(
                    'orderby'           => 'name',
                    'order'             => 'ASC',
                    'hide_empty'        => false,
                    'fields'            => 'id=>name'
                    );

                $results = get_terms($taxonomies, $args);
            }

            if (!empty($results)) {
                ?>
                <div class="form-group col-md-12 wdm_selections">
                    <label class="col-md-1 control-label" for="woo_schedule_selections"><?php echo ucfirst($selection); ?></label>
                    <div class="col-md-8">
                        <select id="woo_schedule_selections" name="woo_schedule_selections" class="form-control" selection_type="<?php echo $selection; ?>" multiple>
                        <?php echo __('Select ', WDM_WOO_SCHED_TXT_DOMAIN) . ' ' . $selection; ?>
                        <?php foreach ($results as $id => $title) : ?>
                            <option value="<?php echo $id; ?>"><?php echo $title; ?></option>
                        <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-12">
                        <div class="text-right col-md-1">
                        </div>
                        <div class="text-right col-md-2 show-schedule">
                            <input type="button" class="button button-primary" data-loading-text="<?php _e('Loading...', WDM_WOO_SCHED_TXT_DOMAIN); ?>" id="woo_schedule_display_selection" value="<?php _e('Show Schedules', WDM_WOO_SCHED_TXT_DOMAIN); ?>" autocomplete="off">
                        </div>
                    </div>

                </div>


                <script type="text/javascript">
                    jQuery('document').ready(function(){jQuery('#woo_schedule_selections').select2({ placeholder: "<?php echo __('Select ', WDM_WOO_SCHED_TXT_DOMAIN) . $selection; ?>"});});
                </script>
                <?php
            }//results not empty

            $content = ob_get_clean();
            echo $content;

            die();
        }

/*
* This function returns details to displayed if there is wrong selection
* @return $content string  details of wrong selection
* @since 1.0.3
*
*/
        public function wooScheduleWrongSelection()
        {
            ob_start();
            ?>
            <div class="wdm_selections col-md-12">
                <p class="bg-danger wdm-message"><strong><?php _e('Wrong selection.', WDM_WOO_SCHED_TXT_DOMAIN); ?></strong></p>
    </div>

            <?php

            $content = ob_get_clean();
            return $content;
        }

        public function getDaySelected($day_of_week, $days_selected)
        {
            if (!empty($day_of_week) && is_array($day_of_week)) {
                foreach ($day_of_week as $single_day) {
                    $days_selected[$single_day] = 'on';
                }
            }
            return $days_selected;
        }



        public function sendSelectionDetails($selection_type, $wdm_start_date, $wdm_end_date, $selections_list, $days_selected, $show_timer, $wdm_hide_unavailable)
        {
            //Update the details
            if ($selection_type === 'product') {
                $this->wdmSelectionTypeProduct($wdm_start_date, $wdm_end_date, $selections_list, $days_selected, $show_timer, $wdm_hide_unavailable);
            } elseif ($selection_type === 'category') {
                $this->wdmSelectionTypeCategory($selections_list, $days_selected, $wdm_start_date, $wdm_end_date, $show_timer, $wdm_hide_unavailable);
            }
        }

        public function getPtitle($single_selection, $parent_id)
        {
            $variant_prod = new \WC_Product_Variation($single_selection);

            $attr = $variant_prod->get_variation_attributes();

            $ptitle = get_the_title($parent_id);

            $ptitle .= ' (';

            $cnt = count($attr);

            $itr = 0;

            foreach ($attr as $key => $value) {
                $key = str_replace('attribute_', '', $key);
                $ptitle .= "$key:$value";

                if (++$itr <= $cnt-1) {
                    $ptitle .= ' | ';
                }
            }

            $ptitle = $ptitle. ')';

            return $ptitle;
        }

        /**
         * save and display result of category selection
         * @param  [type] $details_result      [description]
         * @param  [type] $days_selection_type [description]
         * @return [type]                      [description]
         */
        public function wdmCategorySelection($details_result)
        {
            if (!empty($details_result)) {
                $tableWholeDayRows      ="";
                $tableSpecificTimeRows  ="";
                foreach ($details_result as $single_result) {
                    $wdm_start_date = $this->wdmCheckIsset($single_result->start_date);
                    $wdm_end_date = $this->wdmCheckIsset($single_result->end_date);
                    $selected_days = $this->wdmCheckIsset($single_result->selected_days);
                    //$show_timer = isset($single_result->show_timer) && $single_result->show_timer == 1 ? __('On', WDM_WOO_SCHED_TXT_DOMAIN) : __('Off', WDM_WOO_SCHED_TXT_DOMAIN);
                    $hide_unavailable= $single_result->hide_unavailable == "true" ? __('On', WDM_WOO_SCHED_TXT_DOMAIN) : __('Off', WDM_WOO_SCHED_TXT_DOMAIN);
                    if (!empty($selected_days)) {
                        $selected_days = unserialize($selected_days);
                        $selected_days = implode(', ', array_keys($selected_days));
                    } else {
                        $selected_days = '--';
                    }
                    $term_array     = get_term_by('id', $single_result->term_id, 'product_cat', 'ARRAY_A');
                    $title          = $term_array['name'];
                    $startTimer     = $single_result->start_timer?"Start":"-";
                    $endTimer       = $single_result->end_timer?"End":"-";
                    $startSkipDate  = !empty($single_result->start_skip_date)?date('m/d/Y', strtotime($single_result->start_skip_date)):"-";
                    $endSkipDate  = !empty($single_result->end_skip_date)?date('m/d/Y', strtotime($single_result->end_skip_date)):"-";

                    switch ($single_result->schedule_type) {
                        case 'wholeDay':
                            $tableWholeDayRows = $tableWholeDayRows."<tr>
                            <td>$title</td>
                            <td>$wdm_start_date</td>
                            <td>$wdm_end_date</td>
                            <td>$selected_days</td>
                            <td>$startSkipDate - $endSkipDate</td>
                            <td>$startTimer $endTimer</td>
                            <td>$hide_unavailable</td>
                            <td><a href='#' class='btn wdm_delete_term' term_id='".$single_result->term_id."'><span class='glyphicon glyphicon-trash'></span></a></td>"."</tr>";
                            break;
                        case 'specificTime':
                            $tableSpecificTimeRows= $tableSpecificTimeRows."<tr>".
                            "<td>$title</td>
                            <td>". date("Y-m-d", strtotime($wdm_start_date)) ."<br>To<br>". date("Y-m-d", strtotime($wdm_end_date)) ."</td>
                            <td>". date("H:i", strtotime($wdm_start_date)) ."<br>To<br>". date("H:i", strtotime($wdm_end_date))."</td>
                            <td>$selected_days</td>
                            <td>$startSkipDate - $endSkipDate</td>
                            <td>$startTimer $endTimer</td>
                            <td>$hide_unavailable</td>
                            <td><a href='#' class='btn wdm_delete_term' term_id='".$single_result->term_id."'><span class='glyphicon glyphicon-trash'></span></a></td>"."</tr>";
                            break;
                    }
                }
                $this->wdmPrintCategoryTables($tableWholeDayRows, $tableSpecificTimeRows);
            } else {
                ?>
                <tr><td><?php _e('No details saved', WDM_WOO_SCHED_TXT_DOMAIN); ?></td></tr>
                <?php
            }
        }

        public function updateExpirationDetailsCallback()
        {
            $scheduleData       = $this->wdmCheckIsset($_POST['scheduleData']);
            $selection_type     = $this->wdmCheckIsset($_POST['selection_type']); //products or categories
            $selections_list    = $this->wdmCheckIsset($_POST['selections_id']); //product ids or term ids selected
            $valid_selection    = array('product','category');
            $this->displayWrongSelection($selection_type, $valid_selection);
            $showParentsInsteadOfVariations = apply_filters('wdmws_show_variation_parents_in_bulk_schedule', false);

            if ($this->wdmCheckEmpty($selection_type, $selections_list)) {
                //  if not-empty selection Update the details
                //  1)Validate the schedule. 2)Save & create crons for each selected category or product
                $scheduleData=$this->getWdmValidateSchedule($scheduleData, $selection_type);
                
                if (isset($scheduleData['errorMessage'])) {
                    ob_start();
                    _e($scheduleData['errorMessage'], WDM_WOO_SCHED_TXT_DOMAIN);
                    $content = ob_get_clean();
                    echo $content;
                    die();
                }
                
                $hideUnavailable=$scheduleData['hideUnavailable']=="false"?"no":"yes";
                if ('product'==$selection_type) {
                    foreach ($selections_list as $productId) {
                        if ($showParentsInsteadOfVariations) {
                            $product = wc_get_product((int)$productId);
                            if ($product->get_type()=='variable') {
                                $variations=$product->get_children();
                                foreach ($variations as $vId) {
                                    $this->saveNewScheduleData((int)$vId, $scheduleData);
                                    wdmWsScheduleNewOnTimeCrons((int)$vId, $selection_type, $scheduleData);
                                    $this->checkHideUnavailable($hideUnavailable, $vId);
                                }
                                $this->checkHideUnavailable($hideUnavailable, $productId, 'variable_parent');
                                continue;
                            }
                        }
                        $this->saveNewScheduleData((int)$productId, $scheduleData);
                        wdmWsScheduleNewOnTimeCrons((int)$productId, $selection_type, $scheduleData);
                        $this->checkHideUnavailable($hideUnavailable, $productId);
                    }
                } else {
                    foreach ($selections_list as $term_id) {
                        woo_schedule_update_term_details((int)$term_id, $scheduleData, $selection_type);
                        if ($hideUnavailable=="yes") {
                            self::wdmCategoryHide($term_id);
                        } else {
                            self::wdmCategoryShow($term_id);
                        }
                    }
                }
                ob_start();
                _e('Details Updated', WDM_WOO_SCHED_TXT_DOMAIN);
                $content = ob_get_clean();
                echo $content;
                //do_action('wdmws_after_product_scheduled', $selections_list, $selection_type);
                die();
            } else {
                ob_start();
                _e('Selection empty', WDM_WOO_SCHED_TXT_DOMAIN);
                self::wooScheduleDisplayExistingDetails($selections_list, $selection_type);
                $content = ob_get_clean();
                echo $content;
                die();
            }
            die();
        }

        public function editSchedulerFieldsCallback()
        {
            $selection_type = isset($_POST['selection_type'])? $_POST['selection_type'] : '';

            $selection_id = isset($_POST['selections_id']) ? $_POST['selections_id'] : ''; //not used currently

            $valid_selection = array('product','category');
            
            if (!empty($selection_type) && !empty($selection_id) && in_array($selection_type, $valid_selection)) {
                ob_start();
                ?>
                <h4><?php printf(__('%s Scheduling', WDM_WOO_SCHED_TXT_DOMAIN), ucfirst($selection_type)); ?></h4>
                <div class="wdm-box clearfix wdm-woo-schedule-category" style="border:none;">
                    <?php
                    $this->getWdmBulkScheduleForm($selection_type);
                    ?>
                </div><!-- /.wdm-box -->
                <div id="wdm-schedule-result">
                    <p class="wdm-message">
                    
                    </p>
                </div>
                <?php
                die();
            }
        }

        /*
         * This is callback to display scheduler setting -
         * Start date, End date & selected dates
         */
        public function displaySchedulerFieldsCallback()
        {

            $selection_type = isset($_POST['selection_type'])? $_POST['selection_type'] : '';

            $selection_id = isset($_POST['selections_id']) ? $_POST['selections_id'] : ''; //not used currently

            $valid_selection = array('product','category');
            $showParentsInsteadOfVariations = apply_filters('wdmws_show_variation_parents_in_bulk_schedule', false);
            if (!empty($selection_type) && !empty($selection_id) && in_array($selection_type, $valid_selection)) {
                ob_start();

                if ($showParentsInsteadOfVariations) {
                    $selection_ids = array();
                    foreach ($selection_id as $pId) {
                        $product = wc_get_product($pId);
                        if ($product->get_type()=="variable") {
                            $variations=$product->get_children();
                            $selection_ids=array_merge($selection_ids, $variations);
                        } else {
                            $selection_ids[]=$pId;
                        }
                    }
                    $selection_id = $selection_ids;
                }

                self::wooScheduleDisplayExistingDetails($selection_id, $selection_type);

                $content = ob_get_clean();
                echo $content;
                die();
            } else {
                echo self::wooScheduleWrongSelection();
                die();
            }
            die();
        }

        /**
         * returns list of products variations in dropdown.
         * @return [type] [description]
         */
        public function handleProductSelectionCallType()
        {
            global $wpdb;
            $parentVariations=[];
            $parentVariationNames=array();
            $full_product_list = array();
            $tableWpPosts= $wpdb->prefix."posts";
            $queryGetProductIdNamePairs="SELECT ID, post_title FROM $tableWpPosts where post_type='product' AND post_status IN ('draft', 'publish', 'pending')";
            $queryGetVariantIdParentPairs= "SELECT ID, post_parent FROM $tableWpPosts where post_type='product_variation' AND post_status IN ('private', 'publish', 'pending')";
            $showParentsInsteadOfVariations = apply_filters('wdmws_show_variation_parents_in_bulk_schedule', false);
            $productsList   = $wpdb->get_results($queryGetProductIdNamePairs);
            $variantsList   = $wpdb->get_results($queryGetVariantIdParentPairs);
            
            if ($variantsList) {
                foreach ($variantsList as $variant) {
                    $parent=$variant->post_parent;
                    if (!in_array($parent, $parentVariations)) {
                        $parentVariations[]=$parent;
                    }
                }
            }
            
            if ($productsList) {
                foreach ($productsList as $singleProduct) {
                    $product_id = $singleProduct->ID;
                    if (!empty($parentVariations) && in_array($product_id, $parentVariations)) {
                        $parentVariationNames[$product_id]=$singleProduct->post_title.__(' (Variable)', WDM_WOO_SCHED_TXT_DOMAIN);
                    } else {
                        $full_product_list[$product_id] = $singleProduct->post_title;
                    }
                }
                // exit;
            }//posts end
            
            if ($showParentsInsteadOfVariations) {
                if (!empty($parentVariationNames)) {
                    foreach ($parentVariationNames as $id => $Name) {
                        $full_product_list[$id] = $Name;
                    }
                }
            } elseif ($variantsList) {
                foreach ($variantsList as $variant) {
                        //$variableProduct=wc_get_product($variant->ID);
                        //$attributes=$variableProduct->attributes;
                        $full_product_list[$variant->ID]=$parentVariationNames[$variant->post_parent]." -Variant #".$variant->ID;
                }
            }
            // sort into alphabetical order, by title
            asort($full_product_list);
            return $full_product_list;
        }

        public function wooScheduleDisplayExistingDetails($selection_id, $selection_type)
        {
            global $wpdb;
            $details_result = array();

            ?>
            <div class="woo-schedule-existing-details">
            <div class="edit-schedule-container">
                <h4><?php echo __('Schedule Details', WDM_WOO_SCHED_TXT_DOMAIN); ?><h4>
                <div class="text-right col-md-2">
                    <input type="button" class="button button-secondary" data-loading-text="<?php _e('Loading...', WDM_WOO_SCHED_TXT_DOMAIN); ?>" id="woo_schedule_edit_selection" value="<?php _e('Edit Schedules', WDM_WOO_SCHED_TXT_DOMAIN); ?>" autocomplete="off">
                </div>   
            </div>


            <div class="clearfix wdm-box"><div class="col-md-12">
                <?php

                if (is_array($selection_id)) {
                    $table_display = true;

                    if ($selection_type === 'product') {
                        $query = "SELECT * FROM `" . $wpdb->prefix . "postmeta` WHERE 
                        `post_id` IN (" . implode(',', $selection_id) . ") 
                        AND `meta_key` IN ('wdm_start_date')
                        AND `meta_value` <> ''
                        Order by `post_id`";

                        $result = $wpdb->get_results($query);

                        if (count($result) === 0) {
                            $table_display = false;
                            echo self::noDisplayMessage();
                        }
                    } elseif ($selection_type === 'category') {
                        $query = "SELECT * FROM `" . $wpdb->prefix . "woo_schedule_category` WHERE `term_id` IN (" . implode(',', $selection_id) . ")";

                        $details_result = $wpdb->get_results($query);

                        if (empty($details_result)) {
                            $table_display = false;
                            echo self::noDisplayMessage();
                        }
                    }

                    if ($table_display) {
                        if ($selection_type === 'product') :
                            $this->wdmProductSelection($selection_id);
                        elseif ($selection_type === 'category') :
                            $this->wdmCategorySelection($details_result);
                        endif;
                    }//table display check ends
                }// is_array($selection_id) check ends
                ?>
            </div>
            </div>
            <div class="collapse-button-wrapper">
                <input type="button" class="button button-secondary" id="woo_schedule_collapse_selection" value="<?php _e('Collapse All Tabs', WDM_WOO_SCHED_TXT_DOMAIN); ?>" autocomplete="off">
            </div>
        </div>
            <?php
        }

        /*
        * This will remove all details of particular product
        */
        public function removeProductDetailsCallback()
        {
            $product_id = isset($_POST['product_id'])? intval($_POST['product_id']) : '';

            if (empty($product_id) || $product_id <= 0) {
                echo __("Product ID incorrect", WDM_WOO_SCHED_TXT_DOMAIN);
                die();
            }

            delete_post_meta($product_id, 'wdm_start_date');
            delete_post_meta($product_id, 'wdm_end_date');
            delete_post_meta($product_id, 'wdm_start_time_hr');
            delete_post_meta($product_id, 'wdm_start_time_min');
            delete_post_meta($product_id, 'wdm_end_time_hr');
            delete_post_meta($product_id, 'wdm_end_time_min');
            delete_post_meta($product_id, 'wdm_days_selected');
            delete_post_meta($product_id, 'wdm_show_timer');
            delete_post_meta($product_id, '_hide_if_unavailable');
            delete_post_meta($product_id, 'availability_flag');
            delete_post_meta($product_id, 'wdm_schedule_settings');
            delete_post_meta($product_id, 'availability_pairs');
            delete_post_meta($product_id, 'wdm_show_product');
            //re-publish the product if its marked unavailable
            $current_post = get_post($product_id, 'ARRAY_A');
            $pid=$product_id;
            if ($current_post['post_type']=="product_variation") {
                $pid=(int)$current_post['post_parent'];
            }
            $catScheduleUnavailableHidden=$this->wdmProductCategoryScheduledAndHidden($pid);
            if (!empty($catScheduleUnavailableHidden) && $catScheduleUnavailableHidden) {
                $current_post['post_status'] = 'draft';
                if ($current_post['post_type']=="product_variation") {
                    $current_post['post_status'] = 'private';
                }
                wp_update_post($current_post);
            } else {
                if ($current_post['post_status'] == 'draft' || $current_post['post_status']=="private") {
                    $current_post['post_status'] = 'publish';
                    wp_update_post($current_post);

                    /**In case of variation if parent product is drafted publish it as well*/
                    if ($current_post['post_type']=="product_variation") {
                        $parentId=$current_post['post_parent'];
                        $parentProduct=get_post($parentId, 'ARRAY_A');
                        if ($parentProduct['post_status'] == 'draft') {
                            $parentProduct['post_status']='publish';
                            wp_update_post($parentProduct);
                        }
                    }
                }
            }
            self::removePostFromAvailableInArray($product_id, $current_post['post_type']);
            wp_clear_scheduled_hook('update_product_status_start', array($product_id));
            wp_clear_scheduled_hook('update_product_status_end', array($product_id));
            do_action('wdmws_after_product_unscheduled', $product_id);
            die();
        }

/*
* This will remove all details of particular Term -- Product category
*/
        public function removeTermDetailsCallback()
        {
            global $wpdb;

            $term_id = isset($_POST['term_id'])? intval($_POST['term_id']) : '';

            if (empty($term_id) || $term_id <= 0) {
                echo __("Term ID incorrect", WDM_WOO_SCHED_TXT_DOMAIN);
                die();
            }

            $table = $wpdb->prefix . 'woo_schedule_category';

            $where = array('term_id' => $term_id);

            $wpdb->delete($table, $where);
            $this::wdmCategoryShow($term_id);
            do_action('wdmws_after_term_unscheduled', $term_id);
            wp_clear_scheduled_hook('update_category_status_start', array($term_id));
            wp_clear_scheduled_hook('update_category_status_end', array($term_id));

            die();
        }

        public function noDisplayMessage()
        {
            ?>
            <p><?php _e('No details saved.', WDM_WOO_SCHED_TXT_DOMAIN); ?></p>
            <?php
        }

        /**
         * update schedule if selection type is category
         * @param  [type] $selections_list [description]
         * @param  String $days_selected   [description]
         * @param  String $wdm_start_date  [description]
         * @param  String $wdm_end_date    [description]
         * @return [type]                  [description]
         */
        public function wdmSelectionTypeCategory($selections_list, $days_selected, $wdm_start_date, $wdm_end_date, $show_timer, $wdm_hide_unavailable)
        {
            if (!empty($wdm_start_date) && !empty($wdm_end_date)) {
                $wdmwsSettings = \Includes\AdminSettings\WdmWsSettings::getSettings();
                $enableShowTimer = isset($wdmwsSettings) && isset($wdmwsSettings['wdmws_display_timer']) ? $wdmwsSettings['wdmws_display_timer'] : false ;
                $type = isset($wdmwsSettings) && isset($wdmwsSettings['wdmws_custom_product_expiration_type'][0]) ? $wdmwsSettings['wdmws_custom_product_expiration_type'][0] : 'per_day' ;

                $show_category_timer = false;
        
                //Update selection details
                if ($enableShowTimer) {
                    $show_category_timer = $show_timer;
                }

                if (!empty($days_selected)) {
                    $days_selected = serialize($days_selected);
                }

                if (!($type == 'per_day')) {
                    $days_selected  = serialize(array());
                }

                $wdm_end_date = str_replace(" PM", ":00 PM", $wdm_end_date);
                $wdm_end_date = str_replace(" AM", ":00 AM", $wdm_end_date);
                $wdm_start_date = str_replace(" PM", ":00 PM", $wdm_start_date);
                $wdm_start_date = str_replace(" AM", ":00 AM", $wdm_start_date);

                foreach ($selections_list as $selections_id) {
                    woo_schedule_update_term_details($selections_id, date('Y-m-d h:i:s A', strtotime($wdm_start_date)), date('Y-m-d h:i:s A', strtotime($wdm_end_date)), $days_selected, $show_category_timer, $wdm_hide_unavailable);
                    if ($wdm_hide_unavailable=="yes") {
                        //Update hide_if_unavailable records to "yes" for products in category
                        $this::wdmCategoryHide($selections_id);
                    } else {
                        //Update hide_if_unavailable records to "no" for products in category
                        $this::wdmCategoryShow($selections_id);
                    }
                }
            } else {
                foreach ($selections_list as $selections_id) {
                    //Update hide_if_unavailable records to "no" for products in category
                    //TODO : delete all records wdmCategoryDelete
                    $this::wdmCategoryShow($selections_id);
                }
                woo_schedule_delete_term_details($selections_list);
            }
        }

        public function scheduleOnTimeCronsProducts($post_id, $startDate, $endDate, $wdmStartTimeArray, $wdmEndTimeArray, $daysSelected = array())
        {
            $post_id = (int)$post_id;
            wp_clear_scheduled_hook('update_product_status_start', array($post_id));
            wp_clear_scheduled_hook('update_product_status_end', array($post_id));
            if (empty($startDate) || empty($endDate) || empty($wdmStartTimeArray) || empty($wdmEndTimeArray)) {
                return;
            }

            $wdmwsSettings = \Includes\AdminSettings\WdmWsSettings::getSettings();
            $product_exp_type = isset($wdmwsSettings) && isset($wdmwsSettings['wdmws_custom_product_expiration_type'][0]) ? $wdmwsSettings['wdmws_custom_product_expiration_type'][0] : "per_day";

            if ($product_exp_type == 'per_day') {
                $scheduleCronDates = getDatesToSetCron($startDate, $endDate, $wdmStartTimeArray, $wdmEndTimeArray);
                if (!empty($scheduleCronDates)) {
                    $this->setProductCronSchedule($scheduleCronDates['scheduleCronStartDates'], 'update_product_status_start', $post_id, $daysSelected);
                    $this->setProductCronSchedule($scheduleCronDates['scheduleCronEndDates'], 'update_product_status_end', $post_id, $daysSelected);
                }
            } else {
                $startDateTime = $this->getDateTime($startDate, $wdmStartTimeArray);
                $endDateTime = $this->getDateTime($endDate, $wdmEndTimeArray);
                $startDateObject = new \DateTime($startDateTime); /// get wordpress timezone
                $endDateObject = new \DateTime($endDateTime); /// get wordpress timezone

                update_post_meta($post_id, 'is_cron_set', true);
                wp_schedule_single_event(strtotime(get_gmt_from_date($startDateObject->format('Y-m-d H:i:s')) . ' GMT'), 'update_product_status_start', array($post_id));
                wp_schedule_single_event(strtotime(get_gmt_from_date($endDateObject->format('Y-m-d H:i:s')) . ' GMT'), 'update_product_status_end', array($post_id));
            }
        }

        public function setProductCronSchedule($scheduleCronArray, $cronText, $post_id, $daysSelected)
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

        public function getDateTime($Date, $wdmTime)
        {
            $DateTime = $Date.' '.$wdmTime;
            return $DateTime;
        }

        /**
         * check selection list is empty or not
         * @param  [type] $selection_type  [description]
         * @param  [type] $selections_list [description]
         * @return boolean                  [description]
         */
        public function wdmCheckEmpty($selection_type, $selections_list)
        {
            if (!empty($selection_type) && !empty($selections_list) && is_array($selections_list)) {
                return true;
            }
            return false;
        }

        /**
         * check selection type is valid or not
         * @param  String $selection_type  current selection type
         * @param  Array $valid_selection valid selection types i.e product or category
         * @return boolean                  [description]
         */
        public function wdmCheckSelectionType($selection_type, $valid_selection)
        {
            if (empty($selection_type) || !in_array($selection_type, $valid_selection)) {
                return true;
            }
            return false;
        }

        /**
         * Save and display data for selected products
         * @param  [type] $selection_id [description]
         * @return [type]               [description]
         */
        public function wdmProductSelection($selection_id)
        {
            $tableLaunch="";
            $tableWholeDay="";
            $tableSpecificTime="";
            $wdmwsSettings = \Includes\AdminSettings\WdmWsSettings::getSettings();
            $days_selection_type= isset($wdmwsSettings) && isset($wdmwsSettings['wdmws_custom_product_expiration_type'][0]) ? $wdmwsSettings['wdmws_custom_product_expiration_type'][0] : 'per_day' ;
            $scheduleTypeOld="wholeDay";
            if ($days_selection_type=='per_day') {
                $scheduleTypeOld='specificTime';
            }
            
            foreach ($selection_id as $single_selection) :
                $wdmProductSettings= get_post_meta($single_selection, 'wdm_schedule_settings', true);
                $scheduleType=$scheduleTypeOld;
                if (!empty($wdmProductSettings)) {
                    $scheduleType=$wdmProductSettings['type'];
                }
                $row="";
                switch ($scheduleType) {
                    case 'productLaunch':
                        $row            =$this->wdmWsGetLaunchDetails($single_selection, $wdmProductSettings);
                        $tableLaunch    =$this->wdmWsAddToLaunchTable($tableLaunch, $row);
                        break;
                    case 'wholeDay':
                        $row            =$this->wdmWsGetWholeDayDetails($single_selection, $wdmProductSettings);
                        $tableWholeDay  =$this->wdmWsAddToWholeDayTable($tableWholeDay, $row);
                        break;
                    case 'specificTime':
                        $row                =$this->wdmWsGetSpecificTimeDetails($single_selection, $wdmProductSettings);
                        $tableSpecificTime  =$this->wdmWsAddToSpecificTimeTable($tableSpecificTime, $row);
                        break;
                    default:
                        break;
                }
            endforeach;
            $tableLaunch        =$this->wdmCloseTableTagIfTableNotEmpty($tableLaunch);
            $tableWholeDay      =$this->wdmCloseTableTagIfTableNotEmpty($tableWholeDay);
            $tableSpecificTime  =$this->wdmCloseTableTagIfTableNotEmpty($tableSpecificTime);
            echo $tableLaunch;
            echo $tableWholeDay;
            echo $tableSpecificTime;
        }

        


        /**
         * This method is used to put rows(schedule entries) in bulk schedules list
         * on bulk schedule page
         */
        public function putTableRow($dataArray)
        {
            ?>
            <tr>  
                <td><?php echo $dataArray['p_title']; ?></td>
                <td><?php echo date('Y-m-d', strtotime($dataArray['start_date'])) . ' ' . $dataArray['start_time_hr'] . ':' . $dataArray['start_time_min']; ?></td>
                <td><?php echo date('Y-m-d', strtotime($dataArray['end_date'])) . ' ' . $dataArray['end_time_hr'] . ':' . $dataArray['end_time_min']; ?> </td>
                <?php if ($dataArray['days_selection_type'] == 'per_day') : ?>
                    <td><?php echo $dataArray['options'];?> </td>
                    <?php
                endif; ?>
                <td><?php echo $dataArray['show_timer'];?> </td>
                <td><?php echo $dataArray['hide_unavailable'];?></td>
                <td>    <a href="#" class="btn wdm_delete_product" product_id="<?php echo $dataArray['single_selection']; ?>"><span class="glyphicon glyphicon-trash"></span></a></td>
                </tr>
            <?php
        }

        public function wdmSelectionTypeProduct($wdm_start_date, $wdm_end_date, $selections_list, $days_selected, $show_timer, $wdm_hide_unavailable)
        {
            if (!empty($wdm_start_date) && !empty($wdm_end_date)) {
                $startDate = substr($wdm_start_date, 0, strpos($wdm_start_date, ','));
                $endDate = substr($wdm_end_date, 0, strpos($wdm_end_date, ','));
                $start = strtotime(trim($startDate));
                $end = strtotime(trim($endDate));

                $start_time = trim(substr($wdm_start_date, strpos($wdm_start_date, ',')+1));
                $end_time = trim(substr($wdm_end_date, strpos($wdm_end_date, ',')+1));
                $start_time_hr = substr($start_time, 0, strpos($start_time, ':'));
                $end_time_hr = substr($end_time, 0, strpos($end_time, ':'));

                $start_time_min = substr($start_time, strpos($start_time, ':')+1);
                $end_time_min = substr($end_time, strpos($end_time, ':')+1);

                $wdmwsSettings = \Includes\AdminSettings\WdmWsSettings::getSettings();
                $type= isset($wdmwsSettings) && isset($wdmwsSettings['wdmws_custom_product_expiration_type'][0]) ? $wdmwsSettings['wdmws_custom_product_expiration_type'][0] : 'per_day' ;

                $start_time  = $start_time_hr . ":" . $start_time_min;
                $end_time    = $end_time_hr . ":" . $end_time_min;
                $start_time = str_replace(" AM", ":00 AM", $start_time);
                $start_time = str_replace(" PM", ":00 PM", $start_time);
                $end_time = str_replace(" AM", ":00 AM", $end_time);
                $end_time = str_replace(" PM", ":00 PM", $end_time);
                $str_start_time  = strtotime($wdm_start_date);
                $str_end_time    = strtotime($wdm_end_date);

                /*
                * Checking whether Start Time is greater than End Time.
                * If true, both of them are set the same value.
                */

                if ($type == 'per_day') {
                    if ($str_start_time > $str_end_time) {
                        $start_time_hr   = $end_time_hr;
                        $start_time_min  = $end_time_min;
                    }
                
                    $end = ($end < $start) ? $start : $end;
                }
                
            
                foreach ($selections_list as $selections_id) {
                //Update selection details
                    $this->checkHideUnavailable($wdm_hide_unavailable, $selections_id);

                    update_post_meta($selections_id, 'wdm_show_timer', $show_timer);
                    if (!empty($days_selected)) {
                        update_post_meta($selections_id, 'wdm_days_selected', $days_selected);
                    } else {
                        delete_post_meta($selections_id, 'wdm_days_selected');
                    }

                    update_post_meta($selections_id, 'wdm_start_date', date('m/d/Y', $start));
                    update_post_meta($selections_id, 'wdm_end_date', date('m/d/Y', $end));

                /*
                * Updating the start and end time
                *
                */
                    if ($start_time_hr != -1 && $start_time_min != -1 && $end_time_hr != -1 && $end_time_min != -1) {
                        update_post_meta($selections_id, 'wdm_start_time_hr', $start_time_hr);
                        update_post_meta($selections_id, 'wdm_start_time_min', $start_time_min);
                        update_post_meta($selections_id, 'wdm_end_time_hr', $end_time_hr);
                        update_post_meta($selections_id, 'wdm_end_time_min', $end_time_min);
                    } else {
                        update_post_meta($selections_id, 'wdm_start_time_hr', -1);
                        update_post_meta($selections_id, 'wdm_start_time_min', -1);
                        update_post_meta($selections_id, 'wdm_end_time_hr', -1);
                        update_post_meta($selections_id, 'wdm_end_time_min', -1);
                    }

                    $this->scheduleOnTimeCronsProducts($selections_id, $startDate, $endDate, $start_time, $end_time, $days_selected);
                }//foreach ends -- selections list loop ends
            } else {
                foreach ($selections_list as $selections_id) {
                    delete_post_meta($selections_id, 'wdm_start_date');
                    delete_post_meta($selections_id, 'wdm_end_date');
                    delete_post_meta($selections_id, 'wdm_start_time_hr');
                    delete_post_meta($selections_id, 'wdm_start_time_min');
                    delete_post_meta($selections_id, 'wdm_end_time_hr');
                    delete_post_meta($selections_id, 'wdm_end_time_min');
                    delete_post_meta($selections_id, 'wdm_show_timer');
                    delete_post_meta($selections_id, '_hide_if_unavailable');
                }
            }
        }

        /**
         * This method checks if option "hide if unavailable" selected
         * if its selected method mark current product as draft add it
         * to the cron availability array, set set_availibility_flag to no
         * & _hide_if_unavailable flag
         */
        public function checkHideUnavailable($wdm_hide_unavailable, $selections_id, $variableParent = '')
        {
            if ($wdm_hide_unavailable == "yes") {
                $current_post = get_post($selections_id, 'ARRAY_A');
                $current_post['post_status'] = 'draft';
                $product = wc_get_product($selections_id);
                if ($product->get_type()=="variation") {
                    $current_post['post_status'] = 'private';
                }
                $postType = ''===$variableParent?$current_post['post_type']:$variableParent;
                $this::addPostInAvailableInArray($selections_id, $postType);
                wp_update_post($current_post);
                update_post_meta($selections_id, 'availability_flag', 'no');
            } else {
                $current_post = get_post($selections_id, 'ARRAY_A');
                $product = wc_get_product($selections_id);
                if ($product->get_type()=="variation") {
                    if ($current_post['post_status']=="private") {
                        $current_post['post_status']="publish";
                    }
                } else {
                    if ($current_post['post_status']=="draft") {
                        $current_post['post_status']="publish";
                    }
                }
                wp_update_post($current_post);
                $postType = ''===$variableParent?$current_post['post_type']:$variableParent;
                $this::removePostFromAvailableInArray($selections_id, $postType);
                update_post_meta($selections_id, 'availability_flag', 'yes');
                update_post_meta($selections_id, '_hide_if_unavailable', 'no');
            }
        }
        


        public function returnDefaultEnrollmentEmailTemplate()
        {
            $isNonceValid = wp_verify_nonce($_POST['security'], 'reset-enrollment-email-template-nonce');
            if ($isNonceValid) {
                $defaultEnrollmentEmailTemplate = \Includes\Frontend\SchedulerEnrollmentEmail::defaultEmailTemplateWithCSS();
                echo $defaultEnrollmentEmailTemplate.'<p><br></p>';
                wp_die();
            } else {
                wp_die('-1');
            }
        }

        public function returnDefaultNotificationEmailTemplate()
        {
            $isNonceValid = wp_verify_nonce($_POST['security'], 'reset-notification-email-template-nonce');
            if ($isNonceValid) {
                $defaultNotificationEmailTemplate = \Includes\Frontend\SchedulerNotificationEmail::defaultEmailTemplateWithCSS();
                echo $defaultNotificationEmailTemplate.'<p><br></p>';
                wp_die();
            } else {
                wp_die('-1');
            }
        }

        public function productNotificationEnrl()
        {
            $wdmwsSettings = \Includes\AdminSettings\WdmWsSettings::getSettings();
            $productId = $_POST['product_id'];
            $userEmail = '';

            if (!check_ajax_referer('wdmws_notify_me_enrl', 'security', false)) {
                wp_die(__('Something went wrong! Try again later.', WDM_WOO_SCHED_TXT_DOMAIN));
            }

            if (is_user_logged_in()) {
                $user = wp_get_current_user();
                $userEmail = $user->user_email;
            } else {
                $userEmail = filter_var($_POST[ 'user_email' ], FILTER_SANITIZE_EMAIL);

                if (false == $userEmail) {
                    wp_die(__('Something went wrong! Try again later.', WDM_WOO_SCHED_TXT_DOMAIN));
                }
            }

            global $wpdb;
            $query = "SELECT * from ".$wpdb->prefix."wdmws_enrl_users WHERE product_id=".$productId." AND user_email='".$userEmail."'";
            $results = $wpdb->get_results($query, ARRAY_A);

            if (!empty($results)) {
                wp_die(__('enrolled', WDM_WOO_SCHED_TXT_DOMAIN));
            }

            $unsubscriptionLink = wdmwsGenerateUnsubscriptionHash($productId, $userEmail);
            $data = array('product_id' => $productId, 'user_email' => $userEmail, 'unsubscription_link' => $unsubscriptionLink);

            $recordAdded = $wpdb->insert($wpdb->prefix.'wdmws_enrl_users', $data);

            // Send enrollment successfull email if setting is enabled.
            if (isset($wdmwsSettings['wdmws_enable_send_email_enrl']) && '1' == $wdmwsSettings['wdmws_enable_send_email_enrl']) {
                $emailObject = \Includes\Frontend\SchedulerEnrollmentEmail::getInstance();
                $emailObject->prepareData($productId, $userEmail);
                do_action('wdmws_send_enrollment_successful_email', $emailObject);
            }

            if (empty($recordAdded)) {
                wp_die(__('Something went wrong! Try again later.', WDM_WOO_SCHED_TXT_DOMAIN));
            } else {
                wp_die('success');
            }
        }

        /**
         * Ajax response for products and corresponding number of users enrolled
         * for product notification.
         */
        public function enrlProductUserCount()
        {
            $data = $_POST;
            
            $enrolledUsers = new \Includes\Admin\SchedulerAdminNotifyEnrolledUsers();
            $results = $enrolledUsers->getProductEnrlUsersCount($data);

            $results = json_encode($results);
            echo $results;
            wp_die();
        }

        public function productEnrolledUsersList()
        {
            $data = $_POST;
            $enrolledUsers = new \Includes\Admin\SchedulerAdminNotifyEnrolledUsers();
            $results = $enrolledUsers->getProductEnrolledUsersList($data);
            
            $results = json_encode($results);
            echo $results;
            wp_die();
        }

        public function disenrollUserFromListAdminAction()
        {
            $data = $_POST;
            $handleUnsubscription = new \Includes\SchedulerHandleUnsubscription();
            $handleUnsubscription->disenrollUserFromEnrlList($data['user_email'], $data['product_id']);

            wp_die();
        }

        public function disenrollUserFromListUserAction()
        {
            $data = $_POST;
            $response = '';
            /**
             * Response Type, used to change the response message using filter.
             *
             * 0 = Nonce is not verified.
             * 1 = Some issues while deleting the records from the table.
             * 2 = Successfully removed the records.
             * 3 = No record found.
             *
             * @var int
             */
            $responseType = 0;

            if (check_ajax_referer('wdmws_unsubscription_option', 'security', false)) {
                $unsubscriptionType = $data['unsubscription_type'];
                $userEmail = $data['user_email'];
                $productId = $data['product_id'];
                $handleUnsubscription = new \Includes\SchedulerHandleUnsubscription();
                $response = '';

                if ('product' == $unsubscriptionType) {
                    $result = $handleUnsubscription->disenrollUserFromEnrlList($userEmail, $productId);
                } else {
                    $result = $handleUnsubscription->disenrollUserFromAllList($userEmail);
                }

                if (false === $result) {
                    $response = __('Something went wrong! Try again later.', WDM_WOO_SCHED_TXT_DOMAIN);
                    $responseType = 1;
                } elseif (0 === $result) {
                    $response = __('No record found.', WDM_WOO_SCHED_TXT_DOMAIN);
                    $responseType = 3;
                } else {
                    $response = 'product' == $unsubscriptionType ? 'product' : 'all';
                    $responseType = 2;
                }
            } else {
                $response = __('There is some issue in authentication.', WDM_WOO_SCHED_TXT_DOMAIN);
            }

            $response = apply_filters('wdmws_user_unsubscription_ajax_response', $response, $data, $responseType);
            wp_die($response);
        }

        public function exportUsersListForProduct()
        {
            //Allow only admin to export csv files
            $capabilityToExport = apply_filters('wdmws_export_allowed_user_capability', 'manage_options');
            $can_user_export = apply_filters('wdmws_can_user_export_csv', current_user_can($capabilityToExport));
            if (!$can_user_export) {
                wp_die('security_check');
            }

            $data = $_POST;

            $export_object = new \Includes\ImportExport\Export\SchedulerEnrolledUsersExport();
            $usersList = $export_object->fetchUsersListForProduct($data['product_id']);

            if (!empty($usersList)) {
                $file_name = $export_object->returnEnrlUsersListCSVName();
                $upload_dir = wp_upload_dir();

                $deleteFile = glob($upload_dir['basedir'] . $file_name);
                if ($deleteFile) {
                    foreach ($deleteFile as $file) {
                        unlink($file);
                    }
                }

                $output = fopen($upload_dir['basedir'] . $file_name, 'w');
                fputcsv($output, $usersList[0]);
                foreach ($usersList[1] as $row) {
                    $array = (array) $row;
                    fputcsv($output, $array);
                }
                fclose($output);
                echo $upload_dir['baseurl'] . $file_name;
            } else {
                wp_die('user_list_empty');
            }

            wp_die();
        }
    }
}
