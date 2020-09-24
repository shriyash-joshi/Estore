<?php
namespace {

    if (!class_exists('SchedulerInstall')) {
        class SchedulerInstall
        {
            public function __construct()
            {
                add_action('plugins_loaded', array($this, 'wooScheduleUpdateCheck'),99);
            }

        /*
         * Check for Plugin updatation
         * @since 1.2.4
         */

            public function wooScheduleCreateTables()
            {
             //create table
                global $wpdb;
                $charset_collate       = $wpdb->get_charset_collate();
                $woo_product_cate_tbl = $wpdb->prefix . 'woo_schedule_category';
                $table_present_result = $wpdb->get_var("SHOW TABLES LIKE '{$woo_product_cate_tbl}'");
                if ($table_present_result === null || $table_present_result != $woo_product_cate_tbl) {
                    $woo_sche_cate_tbl = "CREATE TABLE IF NOT EXISTS $woo_product_cate_tbl (
            		    meta_id  bigint(20) AUTO_INCREMENT,
            		    term_id bigint(20),
            		    start_date TIMESTAMP,
            		    end_date TIMESTAMP,
            		    selected_days longtext,
                        show_timer int(2),
                        last_updated TIMESTAMP,
                        hide_unavailable VARCHAR(5),
                        schedule_type VARCHAR(20),
                        start_timer   VARCHAR(10),
                        end_timer   VARCHAR(10),
                        start_skip_date   TIMESTAMP NULL,
                        end_skip_date   TIMESTAMP NULL,
                        availability_array longtext,
            		    PRIMARY KEY id (meta_id)
            		) $charset_collate;";
                    
                    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        
                    dbDelta($woo_sche_cate_tbl);
                }

                //when table is exist but newly creted collumns weren't alter the table with those collumns
                if (!$wpdb->get_var("SHOW COLUMNS FROM `$woo_product_cate_tbl` LIKE 'show_timer';")) {
                    $wpdb->query("ALTER TABLE $woo_product_cate_tbl ADD show_timer int(2) UNSIGNED NOT NULL DEFAULT 0");
                }
                if (!$wpdb->get_var("SHOW COLUMNS FROM `$woo_product_cate_tbl` LIKE 'last_updated';")) {
                    $wpdb->query("ALTER TABLE $woo_product_cate_tbl ADD last_updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");
                }
                if (!$wpdb->get_var("SHOW COLUMNS FROM `$woo_product_cate_tbl` LIKE 'hide_unavailable';")) {
                    $wpdb->query("ALTER TABLE $woo_product_cate_tbl ADD hide_unavailable VARCHAR(5) NOT NULL DEFAULT 'no'");
                }
                //new feature schedule Type
                if (!$wpdb->get_var("SHOW COLUMNS FROM `$woo_product_cate_tbl` LIKE 'schedule_type';")) {
                    $wpdb->query("ALTER TABLE $woo_product_cate_tbl ADD schedule_type VARCHAR(20) NOT NULL DEFAULT ''");
                    $wpdb->query("ALTER TABLE $woo_product_cate_tbl ADD start_timer BOOLEAN");
                    $wpdb->query("ALTER TABLE $woo_product_cate_tbl ADD end_timer BOOLEAN");
                    $wpdb->query("ALTER TABLE $woo_product_cate_tbl ADD start_skip_date TIMESTAMP NULL");
                    $wpdb->query("ALTER TABLE $woo_product_cate_tbl ADD end_skip_date TIMESTAMP NULL");
                    $wpdb->query("ALTER TABLE $woo_product_cate_tbl ADD availability_array longtext DEFAULT ''");
                }


                $wdmws_enrl_users = $wpdb->prefix . 'wdmws_enrl_users';
                $table_present_result = $wpdb->get_var("SHOW TABLES LIKE '{$wdmws_enrl_users}'");
                if ($table_present_result === null || $table_present_result != $wdmws_enrl_users) {
                    $wdmws_enrl_users_table = "CREATE TABLE IF NOT EXISTS $wdmws_enrl_users (
            		    ID  bigint(20) AUTO_INCREMENT,
            		    product_id bigint(20),
                        enrolled_date datetime DEFAULT CURRENT_TIMESTAMP,
                        user_email varchar(100),
                        unsubscription_link varchar(255),
            		    PRIMARY KEY (id)
            		    ) $charset_collate;";
                    
                    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        
                    dbDelta($wdmws_enrl_users_table);
                }
            }

            /*
             * Function to handle things on plugins activation
             */
            public function schedulerOnActivation()
            {
                $this->wdmMigrateSettings();
                $wdmwsSettings = array();
                $migrateFlag = get_option('wdmws_schedule_migrated');
                $feedbackDate=current_time('timestamp')+(86400*7); //replace 7 to change days after which feedback modal should be displayed.
                update_option('wdmws_date_to_ask_for_the_feedback', $feedbackDate);
                update_option('wdmws_feedback_status', 'remind_later');
                if (!$migrateFlag) {
                    if (get_option('woocommerce_custom_product_expiration')) {
                        $wdmwsSettings['wdmws_custom_product_expiration'] = get_option('woocommerce_custom_product_expiration');
                    }
                    if (get_option('woocommerce_custom_product_shop_expiration')) {
                        $wdmwsSettings['wdmws_custom_product_shop_expiration'] = get_option('woocommerce_custom_product_shop_expiration');
                    }
                    if (get_option('woocommerce_custom_product_expiration_type')) {
                        $wdmwsSettings['wdmws_custom_product_expiration_type'][0] = get_option('woocommerce_custom_product_expiration_type');
                    }

                    if (!empty($wdmwsSettings)) {
                        update_option('wdmws_settings', $wdmwsSettings);
                    }

                    // To change the _check_avalibility meta key to _hide_if_unavailable
                    if (!get_option('check_availabilty_to_hide_if_unavailable')) {
                        $this->changeMetaKey();
                        update_option('check_availabilty_to_hide_if_unavailable', true);
                    }

                    $scheduleData = $this->getScheduledProductsMeta();
                    if (!empty($scheduleData)) {
                        $this->convertAndSaveTime($scheduleData);
                        $this->setCron($scheduleData);
                    }
                    update_option('wdmws_schedule_migrated', true);
                }
                $this->wdmMigrateScheduleTypes();
                $this->wdmMigrateScheduleTypesCategories();
            }

            /**
             * This function is written to migrate the setting fields from the piklist settings
             * due to removal of the piklist framework
             *
             * @since 2.3.5
             * @return void
             */
            public function wdmMigrateSettings() {
                $settingsMigrated = get_option('wdmws_settings_migrated', false);
                if (!$settingsMigrated && defined('WC_VERSION')) {
                    include_once 'admin/class-wdmws-settings.php';
                    Includes\AdminSettings\WdmWsSettings::migrateSettingsFromOlderVersions();
                    update_option('wdmws_settings_migrated', WS_VERSION);
                }
            }

            /**
             * This function is used to convert data of all the old schedules
             * to the new schedules format
             * @version 2.3.1
             */
            private function wdmMigrateScheduleTypes()
            {
                global $wpdb;
                $wdmwsSettings = \Includes\AdminSettings\WdmWsSettings::getSettings();
                $timerSettings=array("start"=>false,"end"=>false);
                if (!empty($wdmwsSettings)) {
                    $type= isset($wdmwsSettings) && isset($wdmwsSettings['wdmws_custom_product_expiration_type'][0]) ? $wdmwsSettings['wdmws_custom_product_expiration_type'][0] : 'per_day' ;
                    $scheduleType="wholeDay";
                    if ($type=="per_day") {
                        $scheduleType="specificTime";
                    }
                    $enableShowTimer = isset($wdmwsSettings) && isset($wdmwsSettings['wdmws_display_timer']) ? $wdmwsSettings['wdmws_display_timer'] : false ;
                    if ($enableShowTimer) {
                        $timerSettings['start'] = $wdmwsSettings['wdmws_display_start_timer']=="enable_start"?true:false;
                        $timerSettings['end']   = $wdmwsSettings['wdmws_display_end_timer']=="enable_end"?true:false;
                    }

                    //get ids of the products having old schedule
                    $table =$wpdb->prefix.'postmeta';
                    $query = "SELECT post_id FROM (SELECT post_id FROM `$table` WHERE meta_key=\"wdm_start_date\") as allSchedules WHERE allSchedules.post_id NOT IN (SELECT post_id FROM `$table` WHERE meta_key=\"wdm_schedule_settings\")";
                    $results = $wpdb->get_results($query, ARRAY_A);
                    $idsWithOldSchedule=array();
                    foreach ($results as $Id) {
                        $idsWithOldSchedule[]=(int)$Id['post_id'];
                    }
                    $availabilityArray=array();
                    foreach ($idsWithOldSchedule as $productId) {
                            $data=$this->getOldDataForNewSchedule($productId, $scheduleType, $timerSettings);
                            $this->wdmSaveHideUnavailableForVariants($data, $productId);
                            $availabilityArray=$this->getAvailabilityArray($data, $scheduleType);
                        update_post_meta($productId, 'availability_pairs', $availabilityArray);
                    }
                }
            }

            /**
             * This method is used during migration to V2.3.3
             * to check if the product is a variable product having
             * variations and selected to get hidden when unavailable.
             * in such cases hide when unavailable option is stored for each of its
             * child variants.
             *
             * @param array $data Product schedule data
             * @param int $productId
             * @return void
             */
            private function wdmSaveHideUnavailableForVariants($data, $productId)
            {
                if (!empty($data['hideUnavailable'])) {
                    return;
                }
                
                $product = wc_get_product($productId);
                
                if ($product->get_type()=="variation") {
                    $parentId=$product->get_parent_id();
                    $hideOption=get_post_meta($parentId, '_hide_if_unavailable', true);
                    if ($hideOption=='yes') {
                        update_post_meta($productId, '_hide_if_unavailable', 'yes');
                    }
                }
            }

            /**
             * This function is used to convert data of all the old category schedules
             * to the new schedules format
             * @version 2.3.1
             */
            private function wdmMigrateScheduleTypesCategories()
            {
                $migrateFlag = get_option('wdmws_cat_schedule_migrated');
                if ($migrateFlag) {
                    return ;
                }
                $wdmwsSettings = \Includes\AdminSettings\WdmWsSettings::getSettings();
                $timerSettings=array("start"=>false,"end"=>false);
                if (!empty($wdmwsSettings)) {
                    $type= isset($wdmwsSettings) && isset($wdmwsSettings['wdmws_custom_product_expiration_type'][0]) ? $wdmwsSettings['wdmws_custom_product_expiration_type'][0] : 'per_day' ;
                    $scheduleType="wholeDay";
                    if ($type=="per_day") {
                        $scheduleType="specificTime";
                    }
                    $enableShowTimer = isset($wdmwsSettings) && isset($wdmwsSettings['wdmws_display_timer']) ? $wdmwsSettings['wdmws_display_timer'] : false ;
                    if ($enableShowTimer) {
                        $timerSettings['start'] = $wdmwsSettings['wdmws_display_start_timer']=="enable_start"?true:false;
                        $timerSettings['end']   = $wdmwsSettings['wdmws_display_end_timer']=="enable_end"?true:false;
                    }
                }

                $oldCategorySchedules=$this->retrieveScheduledProdCatData();
                foreach ($oldCategorySchedules as $oldCatSchedule) {
                    $this->wdmUpdateCatSchedule($oldCatSchedule, $scheduleType, $timerSettings['start'], $timerSettings['end']);
                }

                update_option('wdmws_cat_schedule_migrated', true);
            }



            public function wdmUpdateCatSchedule($oldCatSchedule, $scheduleType, $startTimer, $endTimer)
            {
                global $wpdb;
                $table = $wpdb->prefix . 'woo_schedule_category';
                $termId = $oldCatSchedule['term_id'];
                $startDateTime = $oldCatSchedule['start_date'];
                $endDateTime   = $oldCatSchedule['end_date'];
                $startDate = date("Y-m-d", strtotime($startDateTime));
                $endDate   = date("Y-m-d", strtotime($endDateTime));
                $startTime = date('h:i A', strtotime($startDateTime));
                $endTime   = date('h:i A', strtotime($endDateTime));
                $daysSelected = $oldCatSchedule['selected_days'];
                $daysSelected = unserialize($daysSelected);
                $data=array('startDate'=>$startDate, 'endDate'=>$endDate,
                            'startTime'=>$startTime, 'endTime'=>$endTime,
                            'daysSelected'=>$daysSelected
                        );
                
                $timerEnabled=$oldCatSchedule['show_timer'];
                if (!$timerEnabled) {
                    $startTimer=false;
                    $endTimer=false;
                }
                $availabilityPairs= $this->getAvailabilityArray($data, $scheduleType);
                $availabilityPairs=serialize($availabilityPairs);
                
                $updateData = array('start_date'=>$startDateTime,'schedule_type'=>$scheduleType,
                              'availability_array'=>$availabilityPairs,
                              'start_timer'=>$startTimer,
                              'end_timer'=>$endTimer,
                            );
                $where = array('term_id' => $termId);

                $wpdb->update($table, $updateData, $where);
            }

        /**
         * Returns scheduled product categories data.
         *
         * @return  array   Array containing data related to the
         *                  product categories which have been scheduled.
         */
            private function retrieveScheduledProdCatData()
            {
                global $wpdb;
            
                $query = "SELECT * FROM " . $wpdb->prefix . "woo_schedule_category";

                $terms = $wpdb->get_results($query, ARRAY_A);

                return $terms;
            }

            /**
             * Gives the availability unavailability pairs for the old products
             * according to the product schedule type
             *
             * @param array $data
             * @param string $type
             * @return array
             */
            private function getAvailabilityArray($data, $type)
            {
                $availabilityArray=array("makeAvailable"=>array(),"makeUnAvailable"=>array());
                if ($type=="wholeDay") {
                    $startDateTime                          = strtotime($data['startDate']." ".$data['startTime']);
                    $endDateTime                            = strtotime($data['endDate']." ".$data['endTime']);
                    $availabilityArray['makeAvailable'][0]  =$startDateTime;
                    $availabilityArray['makeUnAvailable'][0]=$endDateTime;
                    return $availabilityArray;
                } else {
                  //old schedule type was per_day
                    $startDateStartTime      = strtotime($data['startDate']." ".$data['startTime']);
                    $startDateEndTime        = strtotime($data['startDate']." ".$data['endTime']);
                    $endDateTime             = strtotime($data['endDate']." ".$data['endTime']);
                    if (!($startDateStartTime<$startDateEndTime) || empty($data['daysSelected'])) {
                        return $availabilityArray;
                    }
                    
                    while ($startDateStartTime<$endDateTime) {
                        $day  = date('l', $startDateStartTime);
                        if (isset($data['daysSelected'][$day]) && $data['daysSelected'][$day]=="on") {
                            $availabilityArray['makeAvailable'][]   =$startDateStartTime;
                            $availabilityArray['makeUnAvailable'][] =$startDateEndTime;
                        }
                        $startDateStartTime                     =strtotime('+1 day', $startDateStartTime);
                        $startDateEndTime                       =strtotime('+1 day', $startDateEndTime);
                    }
                    return $availabilityArray;
                }
            }

            /**
             * This function gets schedule data for the product id specified.
             * saves the schedule settings by converting according to new schedule types.
             * & returns the data useful to calculate & save availability durations for the product.
             * @return void
             */
            private function getOldDataForNewSchedule($productId, $type, $timerSettings)
            {
                $wdmScheduleArray=array();
                $startTime  = $this->getTime($productId, 'wdm_start_time_hr', 'wdm_start_time_min');
                $endTime    = $this->getTime($productId, 'wdm_end_time_hr', 'wdm_end_time_min');
                
                $productLevelTimer= get_post_meta($productId, 'wdm_show_timer', true);
                if (!$productLevelTimer) {
                    $timerSettings['start'] =false;
                    $timerSettings['end']   =false;
                }
                
                $daysSelected=array("Everyday"=>"on", "Monday"=>"on", "Tuesday"=>"on", "Wednesday"=>"on", "Thursday"=>"on", "Friday"=>"on", "Saturday"=>"on", "Sundayday"=>"on");
                if ($type=="specificTime") {
                    $days=get_post_meta($productId, 'wdm_days_selected', true);
                    $daysSelected=empty($days)?array():$days;
                }
  
                $wdmScheduleArray['startDate']      = get_post_meta($productId, 'wdm_start_date', true);
                $wdmScheduleArray['startTime']      = $startTime;
                $wdmScheduleArray['endDate']        = get_post_meta($productId, 'wdm_end_date', true);
                $wdmScheduleArray['endTime']        = $endTime;
                $wdmScheduleArray['daysSelected']   = $daysSelected;
                $wdmScheduleArray['hideUnavailable']= get_post_meta($productId, '_hide_if_unavailable', true);

                $settings = array(
                    'type'          => $type,
                    'startTimer'    => $timerSettings['start'],
                    'endTimer'      => $timerSettings['end'],
                    'daysSelected'  => $daysSelected,
                    'skipStartDate' => '',
                    'skipEndDate'   => ''
                    );
                update_post_meta($productId, 'wdm_schedule_settings', $settings);
                return $wdmScheduleArray;
            }

            public function getTime($post_id, $hr_key, $min_key)
            {
                $time_hr = get_post_meta($post_id, $hr_key, true);
                $time_min = get_post_meta($post_id, $min_key, true);

                return $time_hr. ":" . $time_min;
            }

            public function setCron($scheduleData)
            {
                foreach ($scheduleData as $key => $value) {
                    // get time from databse as it is updated by migration (from 24 HRs to 12 Hrs)
                    $wdm_start_time = $this->getTime($key, 'wdm_start_time_hr', 'wdm_start_time_min');
                    $wdm_end_time = $this->getTime($key, 'wdm_end_time_hr', 'wdm_end_time_min');

                    if (isset($value['wdm_days_selected'])) {
                        $wdm_days_selected = $value['wdm_days_selected'];
                        $this->scheduleOnTimeCronsProductsMigration($key, $value['wdm_start_date'], $value['wdm_end_date'], $wdm_start_time, $wdm_end_time, $wdm_days_selected);
                    } else {
                        $wdm_days_selected = array();
                        $this->scheduleOnTimeCronsProductsMigration($key, $value['wdm_start_date'], $value['wdm_end_date'], $wdm_start_time, $wdm_end_time, $wdm_days_selected, 'entire_day');
                    }
                }
            }

            public function getScheduledProductsMeta()
            {
                global $wpdb;
                $postMetaTable = $wpdb->prefix . "postmeta";
                $schedulerMetaKeys = array('wdm_start_date', 'wdm_end_date', 'wdm_start_time_hr', 'wdm_start_time_min', 'wdm_end_time_hr', 'wdm_end_time_min', 'wdm_days_selected');

                $whereCondition = '"'.implode('","', $schedulerMetaKeys).'"';
                $query = "SELECT post_id, meta_key, meta_value FROM $postMetaTable WHERE meta_key IN ($whereCondition)";
                $postmetasSet = $wpdb->get_results($query);
                
                if (!empty($postmetasSet)) {
                    return $this->makePostArray($postmetasSet);
                }
            }


            public function getCronProducts()
            {
                global $wpdb;
                $postMetaTable = $wpdb->prefix . "postmeta";
                $query = "SELECT post_id FROM $postMetaTable WHERE meta_key = 'is_cron_set'";

                return $wpdb->get_results($query);
            }

            public function clearProductCrons($productIds)
            {
                foreach ($productIds as $key => $value) {
                    unset($key);
                    wp_clear_scheduled_hook('update_product_status_start', array($value->post_id));
                    wp_clear_scheduled_hook('update_product_status_end', array($value->post_id));
                }
            }

    // Convert to time to AM PM and save back
    // Set Crons

            public function convertAndSaveTime($scheduleData)
            {
                foreach ($scheduleData as $key => $value) {
                    $this->convertAndSaveTimeSingle($key, $value, 'wdm_start_time_hr', 'wdm_start_time_min');
                    $this->convertAndSaveTimeSingle($key, $value, 'wdm_end_time_hr', 'wdm_end_time_min');
                }
            }

            public function convertAndSaveTimeSingle($key, $value, $hr_key, $min_key)
            {
                $wdm_time = date("g:i A", strtotime($value[$hr_key].":".$value[$min_key]));
                $wdm_time_split = explode(":", $wdm_time);
                $wdm_hr = $wdm_time_split[0];
                $wdm_min = $wdm_time_split[1];
                update_post_meta($key, $hr_key, $wdm_hr);
                update_post_meta($key, $min_key, $wdm_min);
            }

            public function makePostArray($postmeta)
            {
                $scheduleData = array();
                // $postIds = array();
                foreach ($postmeta as $key => $value) {
                    unset($key);
                    $scheduleData[$value->post_id][$value->meta_key] = $value->meta_value;
                }

                return $scheduleData;
            }

            public function changeMetaKey()
            {
                global $wpdb;
                $changeKey = "_hide_if_unavailable";
                $postMetaTable = $wpdb->prefix . "postmeta";
                $postmetasSet = $wpdb->get_results("SELECT meta_id, post_id, meta_key, meta_value FROM $postMetaTable WHERE meta_key = '_check_avalibility' OR meta_key = 'check_avalibility'");
                
                foreach ($postmetasSet as $value) {
                    $wpdb->update($postMetaTable, array(
                        'meta_key'  => $changeKey,
                    ), array(
                        'meta_id'   => $value->meta_id,
                    ), array(
                        '%s',
                    ), array(
                        '%d',
                    ));
                }
            }

            public function scheduleOnTimeCronsProductsMigration($post_id, $startDate, $endDate, $wdmStartTimeArray, $wdmEndTimeArray, $daysSelected = array(), $product_exp_type = "")
            {
                wp_clear_scheduled_hook('update_product_status_start', array($post_id));
                wp_clear_scheduled_hook('update_product_status_end', array($post_id));
                if (empty($startDate) || empty($endDate) || empty($wdmStartTimeArray) || empty($wdmEndTimeArray)) {
                    return;
                }

                $wdmwsSettings = \Includes\AdminSettings\WdmWsSettings::getSettings();

                if (empty($product_exp_type)) {
                    $product_exp_type = isset($wdmwsSettings) && isset($wdmwsSettings['wdmws_custom_product_expiration_type'][0]) ? $wdmwsSettings['wdmws_custom_product_expiration_type'][0] : "per_day";
                }

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
                    wp_schedule_single_event(strtotime(get_gmt_from_date($startDateObject->format('Y-m-d H:i:s')) . ' GMT'), 'update_product_status_start', array($post_id));///
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
                return $Date.' '.$wdmTime;
            }

            public function wdmProductExpiration()
            {
                $time=current_time('timestamp');
                add_option('today', $time);
                $this->wdmBackwardCompatibility();
            }

            public function wooScheduleUpdateCheck()
            {
                global $wdmPluginDataScheduler;
                if (!empty($wdmPluginDataScheduler)) {
                    $get_plugin_version = get_option($wdmPluginDataScheduler['pluginSlug'] . '_version', false);
                    if ($get_plugin_version === false || $get_plugin_version  != $wdmPluginDataScheduler['pluginVersion']) {
                        $this->wooScheduleCreateTables();
                        update_option($wdmPluginDataScheduler['pluginSlug'] . '_version', $wdmPluginDataScheduler['pluginVersion']);
                    }   
                }
            }


            /**
 * wdmBackwardCompatibility Apply schedule of already exsiting variable product to its variations.
 * @return [type] [description]
 */
            public function wdmBackwardCompatibility()
            {
                global $wpdb;
    
                $parent_variable = "SELECT distinct post_parent  FROM `{$wpdb->prefix}posts` WHERE `post_type` = 'product_variation' ";
    
                $variable_products = $wpdb->get_col($parent_variable);
    
                foreach ($variable_products as $vid) {
                    $wdm_start_date = get_post_meta($vid, 'wdm_start_date', true);
                    $wdm_end_date = get_post_meta($vid, 'wdm_end_date', true);
                    $wdm_start_time_hr = get_post_meta($vid, 'wdm_start_time_hr', true);
                    $wdm_start_time_min = get_post_meta($vid, 'wdm_start_time_min', true);
                    $wdm_end_time_hr = get_post_meta($vid, 'wdm_end_time_hr', true);
                    $wdm_end_time_min = get_post_meta($vid, 'wdm_end_time_min', true);
                    $wdm_days_selected = get_post_meta($vid, 'wdm_days_selected', true);
    
        
                    if (!empty($wdm_start_date) && !empty($wdm_end_date)) {
                        $args = array(
                        'post_parent' => $vid,
                        'post_type'   => 'product_variation',
                        'numberposts' => -1,
                        'post_status' => 'any'
                        );
                
                        $variants = get_children($args);
                
                        if (is_array($variants)) {
                            $variants = array_keys($variants);
                        } else {
                            continue;
                        }
                
                        foreach ($variants as $variant) {
                            update_post_meta($variant, 'wdm_start_date', $wdm_start_date);
                            update_post_meta($variant, 'wdm_end_date', $wdm_end_date);
                    
                            if (!empty($wdm_start_time_hr)) {
                                update_post_meta($variant, 'wdm_start_time_hr', $wdm_start_time_hr);
                            }
                            if (!empty($wdm_start_time_min)) {
                                update_post_meta($variant, 'wdm_start_time_min', $wdm_start_time_min);
                            }
                            if (!empty($wdm_end_time_hr)) {
                                update_post_meta($variant, 'wdm_end_time_hr', $wdm_end_time_hr);
                            }
                            if (!empty($wdm_end_time_min)) {
                                update_post_meta($variant, 'wdm_end_time_min', $wdm_end_time_min);
                            }
                            if (!empty($wdm_days_selected)) {
                                update_post_meta($variant, 'wdm_days_selected', $wdm_days_selected);
                            }
                        }
                    }
        
                    delete_post_meta($vid, 'wdm_start_date');
                    delete_post_meta($vid, 'wdm_end_date');
                    delete_post_meta($vid, 'wdm_start_time_hr');
                    delete_post_meta($vid, 'wdm_start_time_min');
                    delete_post_meta($vid, 'wdm_end_time_hr');
                    delete_post_meta($vid, 'wdm_end_time_min');
                    delete_post_meta($vid, 'wdm_days_selected');
                }
            }
        }
    }
}
