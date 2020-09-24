<?php
namespace WooScheduleAjax;

require_once 'woo-schedule-ajax-save-methods.php';
if (!class_exists('WooScheduleMethods')) {

    /**
    * This class contains methods which are used during scheduling products via ajax
    */
    class WooScheduleMethods extends WooScheduleSaveMethods
    {
        /**
         * This method adds postId to available posts array to later check
         * scheduling for the posts in cron job
         */
        public static function addPostInAvailableInArray($productId, $productType)
        {
            switch ($productType) {
                case 'product_variation':
                    $availableProducts = (array)get_option('wdm_hide_variants', array());
                    
                    if (! in_array($productId, $availableProducts)) {
                        array_push($availableProducts, $productId);
                    }
                    update_option('wdm_hide_variants', $availableProducts);
                    break;

                case 'cource':
                case 'product':
                    $availableProducts = (array) get_option('wdm_avaliable_products', array());
                    
                    if (! in_array($productId, $availableProducts)) {
                        array_push($availableProducts, $productId);
                    }
                    update_option('wdm_avaliable_products', $availableProducts);
                    break;
                case 'variable_parent':
                    $availableProducts = (array)get_option('wdm_parent_variable_prods', array());
                    if (! in_array($productId, $availableProducts)) {
                        array_push($availableProducts, $productId);
                    }
                    update_option('wdm_parent_variable_prods', $availableProducts);
                    break;
                default:
                    break;
            }
        }


        /**
         * This method removes postId from available posts array
         */
        public static function removePostFromAvailableInArray($productId, $productType)
        {
            switch ($productType) {
                case 'product_variation':
                    $availableProducts = get_option('wdm_hide_variants');
                    if (empty($availableProducts)) {
                        $availableProducts = array();
                    }
                    $availableProducts = array_diff($availableProducts, array($productId));
                    update_option('wdm_hide_variants', $availableProducts);
                    break;
                case 'cource':
                case 'product':
                    $availableProducts = get_option('wdm_avaliable_products');
                    if (empty($availableProducts)) {
                        $availableProducts = array();
                    }
                    $availableProducts = array_diff($availableProducts, array($productId));
                    update_option('wdm_avaliable_products', $availableProducts);
                    break;
                case 'variable_parent':
                    $availableProducts = (array)get_option('wdm_parent_variable_prods', array());
                    array_diff($availableProducts, array($productId));
                    update_option('wdm_parent_variable_prods', $availableProducts);
                    break;
            }
        }


        /**
         * This method is used for assigning hide_if_unavailable status
         * to of all the products which are not scheduled at product level
         * inside selected category
         */
        public static function wdmCategoryHide($cat_id)
        {
            global $wpdb;
            $relationship_table = $wpdb->prefix.'term_relationships';
            $products = $wpdb->get_results("SELECT object_id as product_id FROM {$relationship_table} WHERE term_taxonomy_id = {$cat_id}");
            foreach ($products as $key => $value) {
                unset($key);
                $product=wc_get_product($value->product_id);
                if ($product->get_type()=="variable") {
                    $childrens=$product->get_children();
                    foreach ($childrens as $childId) {
                        update_post_meta($childId, 'wdm_show_cat_product', 'no');
                        $current_post = get_post($childId, 'ARRAY_A');
                        $product_level_schedule=get_post_meta($childId, 'wdm_schedule_settings', true);
                        if ($current_post['post_status'] == 'publish' && empty($product_level_schedule)) {
                            $current_post['post_status'] = 'private';
                            wp_update_post($current_post);
                        }
                    }
                } else {
                    update_post_meta($value->product_id, 'wdm_show_cat_product', 'no');
                    $current_post = get_post($value->product_id, 'ARRAY_A');
                    $product_level_schedule=get_post_meta($value->product_id, 'wdm_schedule_settings', true);
                    if ($current_post['post_status'] == 'publish' && empty($product_level_schedule)) {
                        $current_post['post_status'] = 'draft';
                        wp_update_post($current_post);
                    }
                }
            }
        }


        /**
         * This method is used for unsetting hide_if_unavailable status
         * for all the products inside selected category
         */
        public static function wdmCategoryShow($cat_id)
        {
            global $wpdb;
            $relationship_table = $wpdb->prefix.'term_relationships';
            $products = $wpdb->get_results("SELECT object_id as product_id FROM {$relationship_table} WHERE term_taxonomy_id = {$cat_id}");
            
            foreach ($products as $key => $value) {
                unset($key);
                $product=wc_get_product($value->product_id);
                if ($product->get_type()=="variable") {
                    $childrens=$product->get_children();
                    foreach ($childrens as $childId) {
                        $catDisplay=get_post_meta($childId, 'wdm_show_cat_product', true);
                        $current_post = get_post($childId, 'ARRAY_A');
                        $product_level_schedule=get_post_meta($childId, 'wdm_schedule_settings', true);
                        if ($current_post['post_status'] == 'private' && empty($product_level_schedule) && (!empty($catDisplay) && 'no'==$catDisplay)) {
                            $current_post['post_status'] = 'publish';
                            wp_update_post($current_post);
                        }
                    }
                } else {
                    $catDisplay=get_post_meta($value->product_id, 'wdm_show_cat_product', true);
                    $current_post = get_post($value->product_id, 'ARRAY_A');
                    $product_level_schedule=get_post_meta($value->product_id, 'wdm_schedule_settings', true);
                    if ($current_post['post_status'] == 'draft' && empty($product_level_schedule) && (!empty($catDisplay) && 'no'==$catDisplay)) {
                        $current_post['post_status'] = 'publish';
                        wp_update_post($current_post);
                    }
                }
                update_post_meta($value->product_id, 'wdm_show_cat_product', 'yes');
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
                    $categoryDisplay=$termScheduleData[0]['hide_unavailable']=='false'?'yes':'no';
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


        /**
         * This method is used for deleting hide_if_unavailable meta
         * for all the products except products scheduled at product level
         * in selected category
         */
        // public static function wdmCategoryDelete($cat_id)
        // {
        // }

        //New Mwthods
        public function wdmCloseTableTagIfTableNotEmpty($table)
        {
            if ($table=="") {
                //$table=__("No schedules for selected ID's found for this schedule type", WDM_WOO_SCHED_TXT_DOMAIN);
                return $table;
            }
            $table=$table."</tbody></table>";
            return $table;
        }

        /**
         * Adds the row to the variable containing html table.
         * adds table header while adding the first row to the table
         * @param [type] $tableLaunch
         * @param [type] $row
         * @return void
         */
        public function wdmWsAddToLaunchTable($tableLaunch, $row)
        {
            if ($tableLaunch=="" && $row!="") {
                $tableLaunch= "<h5 class='schedule-type-title'>".__("Schedules For Product Launch", WDM_WOO_SCHED_TXT_DOMAIN)."</h5>".
                sprintf(
                    "<table class='scheduleTable' >
                <thead>
                    <tr>
                    <th>%s</th>
                    <th>%s</th>
                    <th>%s</th>
                    <th>%s</th>
                    <th>%s</th>
                    <th>%s</th>                
                    <th>%s</th>
                    </tr>
                </thead>
                <tbody>",
                    __('Id', WDM_WOO_SCHED_TXT_DOMAIN),
                    __('Title', WDM_WOO_SCHED_TXT_DOMAIN),
                    __('Start Date', WDM_WOO_SCHED_TXT_DOMAIN),
                    __('Start Time', WDM_WOO_SCHED_TXT_DOMAIN),
                    __('Timers', WDM_WOO_SCHED_TXT_DOMAIN),
                    __('Hide Unavailable', WDM_WOO_SCHED_TXT_DOMAIN),
                    __('Action', WDM_WOO_SCHED_TXT_DOMAIN)
                );
            }
            $tableLaunch = $tableLaunch.$row;
            return $tableLaunch;
        }


        /**
         * Adds the row to the variable containing html table.
         * adds table header while adding the first row to the table
         * @param [type] $tableLaunch
         * @param [type] $row
         * @return void
         */
        public function wdmWsAddToWholeDayTable($tableWholeDay, $row)
        {
            if ($tableWholeDay=="" && $row!="") {
                $tableWholeDay="<h5 class='schedule-type-title'>".__("Schedules For Whole Time On Selected days", WDM_WOO_SCHED_TXT_DOMAIN)."</h5>".
                sprintf(
                    "<table class='scheduleTable' >
                <thead>
                    <tr>
                    <th>%s</th>
                    <th>%s</th>
                    <th>%s</th>
                    <th>%s</th>
                    <th>%s</th>
                    <th>%s</th>                
                    <th>%s</th>
                    <th>%s</th>
                    <th>%s</th>
                    </tr>
                </thead>
                <tbody>",
                    __('Id', WDM_WOO_SCHED_TXT_DOMAIN),
                    __('Title', WDM_WOO_SCHED_TXT_DOMAIN),
                    __('Start At', WDM_WOO_SCHED_TXT_DOMAIN),
                    __('End By', WDM_WOO_SCHED_TXT_DOMAIN),
                    __('Selected Days', WDM_WOO_SCHED_TXT_DOMAIN),
                    __('Skip Between', WDM_WOO_SCHED_TXT_DOMAIN),
                    __('Timers', WDM_WOO_SCHED_TXT_DOMAIN),
                    __('Hide Unavailable', WDM_WOO_SCHED_TXT_DOMAIN),
                    __('Action', WDM_WOO_SCHED_TXT_DOMAIN)
                );
            }
            $tableWholeDay = $tableWholeDay.$row;
            return $tableWholeDay;
        }

        public function wdmWsAddToSpecificTimeTable($tableSpecificTime, $row)
        {
            if ($tableSpecificTime=="" && $row!="") {
                $tableSpecificTime="<h5 class='schedule-type-title'>".__("Schedules For Specific Time On Selected days", WDM_WOO_SCHED_TXT_DOMAIN)."</h5>".
                sprintf(
                    "<table class='scheduleTable'>
                <thead>
                    <tr>
                    <th>%s</th>
                    <th>%s</th>
                    <th>%s</th>
                    <th>%s</th>
                    <th>%s</th>
                    <th>%s</th>                
                    <th>%s</th>
                    <th>%s</th>
                    <th>%s</th>
                    </tr>
                </thead>
                <tbody>",
                    __('Id', WDM_WOO_SCHED_TXT_DOMAIN),
                    __('Title', WDM_WOO_SCHED_TXT_DOMAIN),
                    __('Date Duration', WDM_WOO_SCHED_TXT_DOMAIN),
                    __('Time Duration', WDM_WOO_SCHED_TXT_DOMAIN),
                    __('Selected Days', WDM_WOO_SCHED_TXT_DOMAIN),
                    __('Skip Between', WDM_WOO_SCHED_TXT_DOMAIN),
                    __('Timers', WDM_WOO_SCHED_TXT_DOMAIN),
                    __('Hide Unavailable', WDM_WOO_SCHED_TXT_DOMAIN),
                    __('Action', WDM_WOO_SCHED_TXT_DOMAIN)
                );
            }
            $tableSpecificTime = $tableSpecificTime.$row;
            return $tableSpecificTime;
        }

        /**
         * Gets all the data required to create the schedule row for schedule type "launchProduct"
         * creates the row for the same and returns the row in case of invalid/noSchedule returns empty row.
         *
         * @param [type] $productId
         * @param [type] $wdmProductSettings
         * @return void
         */
        public function wdmWsGetLaunchDetails($productId, $wdmProductSettings)
        {
                $startDate              = get_post_meta($productId, 'wdm_start_date', true);
                $startTimeHr            = get_post_meta($productId, 'wdm_start_time_hr', true);
                $startTimeMin           = get_post_meta($productId, 'wdm_start_time_min', true);
                $title                  = get_the_title($productId);
                $startTimer             = $wdmProductSettings['startTimer']?"Start":"";
                $hideUnavailable       = get_post_meta($productId, '_hide_if_unavailable', true)=="yes" ? "on":"off";
                $row="";
            if (!empty($startDate)) {
                $row    ="<tr>
                    <td>$productId</td>
                    <td>$title</td>
                    <td>$startDate</td>
                    <td>$startTimeHr:$startTimeMin</td>
                    <td>$startTimer</td>
                    <td>$hideUnavailable</td>
                    <td><a href='#' class='btn wdm_delete_product' product_id='$productId'><span class='glyphicon glyphicon-trash'></span></a> </td>
                    </tr>";
            }
                return $row;
        }



        public function wdmWsGetWholeDayDetails($productId, $wdmProductSettings)
        {
                $title                  = get_the_title($productId);
                $startDate              = get_post_meta($productId, 'wdm_start_date', true);
                $startTimeHr            = get_post_meta($productId, 'wdm_start_time_hr', true);
                $startTimeMin           = get_post_meta($productId, 'wdm_start_time_min', true);
                $endDate                = get_post_meta($productId, 'wdm_end_date', true);
                $endTimeHr              = get_post_meta($productId, 'wdm_end_time_hr', true);
                $endTimeMin             = get_post_meta($productId, 'wdm_end_time_min', true);
                $daysSelected           = get_post_meta($productId, 'wdm_days_selected', true);
                $hideUnavailable        = get_post_meta($productId, '_hide_if_unavailable', true)=="yes" ? "on":"off";
                $skipStart              = "-";
                $skipEnd                = "-";
            if (!empty($wdmProductSettings)) {
                $startTimer             = $wdmProductSettings['startTimer']?"Start":"";
                $endTimer               = $wdmProductSettings['endTimer']?"End":"";
                $daysSelected           = $wdmProductSettings['daysSelected'];
                $skipStart              = $wdmProductSettings['skipStartDate'];
                $skipEnd                = $wdmProductSettings['skipEndDate'];
            }
                $daysSelected           =array_keys($daysSelected);
            
            if (in_array("Everyday", $daysSelected) || sizeof($daysSelected)>=7) {
                $daysSelected="Everyday";
            } else {
                $daysSelected= implode(", ", $daysSelected);
            }
                $row                   = "";
            if (!empty($startDate)) {
                $row    ="<tr>
                    <td>$productId</td>
                    <td>$title</td>
                    <td>$startDate $startTimeHr:$startTimeMin</td>
                    <td>$endDate $endTimeHr:$endTimeMin</td>
                    <td>$daysSelected</td>
                    <td>$skipStart - $skipEnd</td>
                    <td>$startTimer - $endTimer</td>
                    <td>$hideUnavailable</td>
                    <td><a href='#' class='btn wdm_delete_product' product_id='$productId'><span class='glyphicon glyphicon-trash'></span></a> </td>
                    </tr>";
            }
                return $row;
        }


        /**
         * Undocumented function
         *
         * @param [type] $productId
         * @param [type] $wdmProductSettings
         * @return void
         */
        public function wdmWsGetSpecificTimeDetails($productId, $wdmProductSettings)
        {
                $title                  = get_the_title($productId);
                $startDate              = get_post_meta($productId, 'wdm_start_date', true);
                $startTimeHr            = get_post_meta($productId, 'wdm_start_time_hr', true);
                $startTimeMin           = get_post_meta($productId, 'wdm_start_time_min', true);
                $endDate                = get_post_meta($productId, 'wdm_end_date', true);
                $endTimeHr              = get_post_meta($productId, 'wdm_end_time_hr', true);
                $endTimeMin             = get_post_meta($productId, 'wdm_end_time_min', true);
                $daysSelected           = get_post_meta($productId, 'wdm_days_selected', true);
                $skipDuration           ="--";
            if (!empty($wdmProductSettings)) {
                $startTimer             = $wdmProductSettings['startTimer']?"Start":"";
                $endTimer               = $wdmProductSettings['endTimer']?"End":"";
                $daysSelected           = $wdmProductSettings['daysSelected'];
                $skipStart              = $wdmProductSettings['skipStartDate'];
                $skipEnd                = $wdmProductSettings['skipEndDate'];
            }
                $daysSelected           = array_keys($daysSelected);

            if (!empty($skipStart)) {
                    $skipDuration= $skipStart." - ".$skipEnd;
            }
            if (in_array("Everyday", $daysSelected) || sizeof($daysSelected)>=7) {
                $daysSelected="Everyday";
            } else {
                $daysSelected= implode(", ", $daysSelected);
            }
                $hideUnavailable       = get_post_meta($productId, '_hide_if_unavailable', true)=="yes" ? "on":"off";
                $row                   = "";
            if (!empty($startDate)) {
                $row    ="<tr>
                    <td>$productId</td>
                    <td>$title</td>
                    <td>$startDate To $endDate</td>
                    <td>$startTimeHr:$startTimeMin  To $endTimeHr:$endTimeMin</td>
                    <td>$daysSelected</td>
                    <td>$skipDuration</td>
                    <td>$startTimer , $endTimer</td>
                    <td>$hideUnavailable</td>
                    <td><a href='#' class='btn wdm_delete_product' product_id='$productId'><span class='glyphicon glyphicon-trash'></span></a> </td>
                    </tr>";
            }
            return $row;
        }


        /**
         * Name: getWdmBulkScheduleForm
         * This method is used to display multistep scheduler form on bulk scheduling page,
         * This form contains the steps for
         * multiple types of schedules including Launch Product, Product availability "Whole Days
         * during selected duration" & Product availability "Limited Time during selected duration".
         *
         * @param int $typeSelection
         * @param string $wdm_schedule_type
         * @return void
         */
        public function getWdmBulkScheduleForm($typeSelection)
        {
            ?>
                <div class="wisdmScheduler" >
                <div id="wdmSingleForm[<?php echo $typeSelection;?>]" class="wdm-form-container wdmSingleForm">
                <?php
                $this->getFirstBulkFormStep($typeSelection);
                $this->getSecondBulkFormStep();
                $this->getThirdBulkFormStepWholeDay();
                $this->getThirdBulkFormStepSpecificTime();
                $this->getThirdBulkFormStepLaunch();
                $this->wdmGetFinalStep();
                echo "</div></div>";
        }


        private function getFirstBulkFormStep($typeSelection)
        {
            $launch="checked";
            $specificDuration="";
            $type="products";
            if ($typeSelection=="category") {
                $launch="disabled";
                $specificDuration="checked";
                $type="categories";
            }
            ?>
            <!--First Step-->
            <div id="wdmSingleFormScheduleType" class="wdm-form-step wdmSingleFormScheduleType">
              <h4><?php _e('I want to schedule the '.$type.' to', WDM_WOO_SCHED_TXT_DOMAIN);  ?></h4>
              <div class="wdmFormRadioWrapper<?php echo $specificDuration; ?>">
                <label for="launchScheduleType" class="wdmFormLabel">
                  <input type="radio" value="productLaunch" wdm-radio-group="ScheduleType" id="launchScheduleType" name="ScheduleType" <?php echo $launch;?>>
                  <strong> <?php _e('Launch on a specific day', WDM_WOO_SCHED_TXT_DOMAIN);?></strong>
                  <p class="radio-discription"> <?php _e('Use this option if you want to schedule a product for spesific duration', WDM_WOO_SCHED_TXT_DOMAIN);?></p>
                </label>
              </div>
              <div class="wdmFormRadioWrapper">
                <label for="perDayScheduleType" class="wdmFormLabel">
                  <input type="radio" value="scheduleDuration" wdm-radio-group="ScheduleType" id="perDayScheduleType" name="ScheduleType" <?php echo $specificDuration;?>>
                  <strong> <?php _e('make them available for some duration', WDM_WOO_SCHED_TXT_DOMAIN);?> </strong>
                  <p class="radio-discription"> <?php _e('Use this option if you want to repeatively schedule a product', WDM_WOO_SCHED_TXT_DOMAIN);?></p>                    
                </label>
            </div>
            </div>
            <?php
        }

        private function getSecondBulkFormStep()
        {
            ?>
            <!--Second Step-->
            <div id="wdmSingleFormScheduleAvailability" class="wdm-form-step wdmSingleFormScheduleAvailability">
              <h4><?php _e('I want to keep it available', WDM_WOO_SCHED_TXT_DOMAIN);?></h4>
              <div class="wdmFormRadioWrapper">
                <label for="wholeTimeAvailabilityType=" class="wdmFormLabel">
                  <input type="radio" value="wholeDay" wdm-radio-group="AvailabilityType" id="wholeTimeAvailabilityType" name="AvailabilityType">
                  <strong> <?php _e('For the whole time on the selected days', WDM_WOO_SCHED_TXT_DOMAIN);?></strong>
                  <p class="radio-discription"> <?php _e('Use this option if product should be availabe whole days between availability period', WDM_WOO_SCHED_TXT_DOMAIN);?></p>
                </label>
              </div>
              <div class="wdmFormRadioWrapper">
                <label for="specificTimeAvailabilityType" class="wdmFormLabel">
                  <input type="radio" value="specificTime" wdm-radio-group="AvailabilityType" id="specificTimeAvailabilityType" name="AvailabilityType">
                  <strong> <?php _e('For the specific time on the selected days', WDM_WOO_SCHED_TXT_DOMAIN);?> </strong>
                  <p class="radio-discription"> <?php _e('Use this option if product should be available for some time only on each selected day between availability period', WDM_WOO_SCHED_TXT_DOMAIN);?></p>
                </label>
              </div>
            </div>
            <?php
        }

        private function getThirdBulkFormStepWholeDay()
        {
            ?>
            <!--Third Step 3.1 Whole Day-->
            <div id="wdmSingleFormScheduleTypeWholeDay" class="wdm-form-step wdmSingleFormScheduleTypeWholeDay">
                <h4> <?php _e('Product should be available', WDM_WOO_SCHED_TXT_DOMAIN);?></h4>
                <div class="wdm-date-container">
                <h5><?php _e('From', WDM_WOO_SCHED_TXT_DOMAIN);?></h5>
                <div class="wdm-row">
                  <div style="position: relative;" class="wdm-col-6">
                    <input type="text" name="wdmWdStartDate" id="wdmWdStartDate" class="wdmDatePicker wdmWdStartDate form-control" placeholder="mm / dd / yyyy">
                    <span class="calender-img"></span>
                  </div>
                  <div style="position: relative;" class="wdm-col-6">
                    <input type="text" name="wdmWdStartTime" id="wdmWdStartTime" class="wdmTimePicker wdmWdStartTime form-control" placeholder="hh:mm">
                    <span class="clock-img"></span>
                  </div>
                </div>
                <h5><?php _e('To', WDM_WOO_SCHED_TXT_DOMAIN);?></h5>
                <div class="wdm-row">
                    <div style="position: relative;" class="wdm-col-6">
                      <input type="text" name="wdmWdEndDate" id="wdmWdEndDate" class="wdmDatePicker wdmWdEndDate form-control" placeholder="mm / dd / yyyy">
                      <span class="calender-img"></span>
                    </div>
                    <div style="position: relative;" class="wdm-col-6">
                      <input type="text" name="wdmWdEndTime" id="wdmWdEndTime" class="wdmTimePicker wdmWdEndTime form-control" placeholder="hh:mm">
                      <span class="clock-img"></span>
                    </div>
                </div>
                </div>
                <div class="wdm-day-container">
                    <?php  $this->wdmMultistepBulkFormDays('wdmWdWeekdays');  ?>
                </div>
                <div class="wdm-duration-container">                  
                <h5><?php _e('Skip During', WDM_WOO_SCHED_TXT_DOMAIN);?><span class="optional"> <?php _e('(Optional)', WDM_WOO_SCHED_TXT_DOMAIN);?></span></h5> 
                <div class="wdm-row">
                  <div style="position: relative;" class=" wdm-col-5">
                    <input type="text" name="wdmWdSkipStartDate" id="wdmWdSkipStartDate" class="wdmDatePicker wdmWdSkipStartDate form-control" placeholder="mm / dd / yyyy">
                     <span class="calender-img"></span>
                  </div>
                  <div class="wdm-col-1"> <span><h5><?php _e('To', WDM_WOO_SCHED_TXT_DOMAIN);?></h5></span> </div>
                  <div style="position: relative;" class=" wdm-col-5">
                      <input type="text" name="wdmWdSkipEndDate" id="wdmWdSkipEndDate" class="wdmDatePicker wdmWdSkipEndDate form-control" placeholder="mm / dd / yyyy">
                      <span class="calender-img"></span>
                  </div>
                </div>
                </div>
              </div>    
            <?php
        }

        private function getThirdBulkFormStepSpecificTime()
        {
            ?>
            <!--Third Step 3.2 Specific Time-->
            <div id="wdmSingleFormScheduleSpecificTime" class="wdm-form-step wdmSingleFormScheduleSpecificTime">
                <h4> <?php _e("Product should be available", WDM_WOO_SCHED_TXT_DOMAIN);?> </h4>
                <div class="wdm-date    -container">
                <div class="wdm-row">
                 <div class="wdm-col-6"><h5><?php _e("Between Dates", WDM_WOO_SCHED_TXT_DOMAIN); ?></h5></div>
                </div>
                <div class="wdm-row">
                  <div style="position: relative;" class="wdm-col-5">
                    <input type="text" name="wdmLtStartDate" id="wdmLtStartDate" class="wdmDatePicker wdmLtStartDate form-control" placeholder="mm / dd / yyyy">
                    <span class="calender-img"></span>
                  </div>
                  <div class="wdm-col-1 text-center"> <span> <h5><?php _e('To', WDM_WOO_SCHED_TXT_DOMAIN);?></h5> </span> </div>
                  <div style="position: relative;" class="wdm-col-5">
                      <input type="text" name="wdmLtEndDate" id="wdmLtEndDate" class="wdmDatePicker wdmLtEndDate form-control" placeholder="mm / dd /yyyy">
                      <span class="calender-img"></span> 
                    </div>
                </div>  
                </div>
                <div class="wdm-day-container">
                    <?php  $this->wdmMultistepBulkFormDays('wdmLtWeekdays');  ?>
                </div>
                <div class="wdm-duration-container">
                <h5><?php _e('Between Time', WDM_WOO_SCHED_TXT_DOMAIN);?></h5>
                <div class="wdm-row">
                    <div style="position: relative;" class="wdm-col-5">
                        <input type="text" name="wdmLtStartTime" id="wdmLtStartTime" class="wdmTimePicker wdmLtStartTime form-control" placeholder="hh:mm">
                        <span class="clock-img"></span>
                    </div>
                    <div class="wdm-col-1"> <span> <h5><?php _e('To', WDM_WOO_SCHED_TXT_DOMAIN);?></h5> </span> </div>
                    <div style="position: relative;" class="wdm-col-5">
                      <input type="text" name="wdmLtEndTime" id="wdmLtEndTime" class="wdmTimePicker wdmLtEndTime form-control" placeholder="hh:mm">
                      <span class="clock-img"></span>
                    </div>
                </div>
                <h5><?php _e('Skip During', WDM_WOO_SCHED_TXT_DOMAIN);?><span class="optional"> <?php _e('Optional', WDM_WOO_SCHED_TXT_DOMAIN);?></span></h5> 
                <div class="wdm-row">
                  <div style="position: relative;" class="wdm-col-5">
                    <input type="text" name="wdmLtSkipStartDate" id="wdmLtSkipStartDate" class="wdmDatePicker wdmLtSkipStartDate form-control" placeholder="mm / dd / yyyy">
                    <span class="calender-img"></span>
                  </div>
                  <div class="wdm-col-1"> <span> <h5><?php _e('To', WDM_WOO_SCHED_TXT_DOMAIN);?></h5> </span> </div>
                  <div style="position: relative;" class="wdm-col-5">
                      <input type="text" name="wdmLtSkipEndDate" id="wdmLtSkipEndDate" class="wdmDatePicker wdmLtSkipEndDate form-control" placeholder="mm /dd / yyyy">
                      <span class="calender-img"></span>
                  </div>
                </div>
                </div>
                </div>
            <?php
        }


        private function getThirdBulkFormStepLaunch()
        {
            ?>
            <!--Third Step 3.3 productLaunch-->
            <div id="wdmSingleFormScheduleLaunch" class="wdm-form-step wdmSingleFormScheduleLaunch">
                  <h4> <?php _e("Launch these products/categories", WDM_WOO_SCHED_TXT_DOMAIN)?></h4>
                  <div class="wdm-row">
                    <div style="position: relative;" class="wdm-col-6">
                      <h5><?php _e("On", WDM_WOO_SCHED_TXT_DOMAIN) ?></h5>
                      <input type="text" name="wdmLaunchDate"  id="wdmLaunchDate" class="wdmDatePicker wdmLaunchDate form-control" placeholder="mm / dd / yyyy" >
                      <span class="calender-img"></span>
                    </div>
                    <div style="position: relative;" class="wdm-col-6">
                      <h5><?php _e("At", WDM_WOO_SCHED_TXT_DOMAIN); ?></h5>
                      <input type="text" name="wdmLaunchTime"  id="wdmLaunchTime" class="wdmTimePicker wdmLaunchTime form-control" placeholder="hh:mm">
                      <span class="clock-img"></span>
                      
                    </div>
                  </div>
            </div>
            <?php
        }


        private function wdmGetFinalStep()
        {
            ?>
            <div id="wdmSingleFormScheduleTimer" class="wdm-form-step wdmSingleFormScheduleTimer">
                  <h4><?php _e('Display & Timer Options', WDM_WOO_SCHED_TXT_DOMAIN);?></h4>
                  <div class="wdmFormCheckboxWrapper">
                    <label for="startTimer" class="wdmFormLabel">
                      <input type="checkbox" id="startTimer" name="startTimer" >
                      <strong> <?php _e('Enable Start Timer', WDM_WOO_SCHED_TXT_DOMAIN);?></strong>
                      <p class="checkbox-discription"><?php _e('Use this option to show sale start countdown timer on the product page', WDM_WOO_SCHED_TXT_DOMAIN);?></p>
                    </label>
                  </div>
                  <div class="wdmFormCheckboxWrapper">
                    <label for="wdmEndTimer" class="wdmFormLabel">
                      <input type="checkbox" class="wdmEndTimer" id="wdmEndTimer" name="wdmEndTimer">
                      <strong><?php _e('Enable End Timer', WDM_WOO_SCHED_TXT_DOMAIN);?> </strong>
                      <p class="checkbox-discription"><?php _e('Use this option to show sale end countdown timer on your product page', WDM_WOO_SCHED_TXT_DOMAIN);?></p>
                    </label>
                  </div>
                  <div class="wdmFormCheckboxWrapper" title="<?php _e('Start timer won\'t be available when this option is enabled');?>">
                    <label for="wdmHideUnavailabe" class="wdmFormLabel">
                      <input type="checkbox" id="wdmHideUnavailabe" name="wdmHideUnavailabe">
                      <strong> <?php _e('Hide Product When Not Available', WDM_WOO_SCHED_TXT_DOMAIN);?></strong>
                      <p class="checkbox-discription"> <?php _e('Use this option to hide your product when it is not available', WDM_WOO_SCHED_TXT_DOMAIN); ?></p>
                    </label>
                  </div>
                </div>
                <div class="wdm-form-step hidden">
                  <button id="wdmScheduleReset"><?php _e('Reset Schedule', WDM_WOO_SCHED_TXT_DOMAIN);?></button>
                </div>
                <div id="wdmNextPrev" class="wdmNextPrev">
                  <button type="button" class="button btn btn-info wdmFormPreviousButton"     id="wdmFrmPrev" ></span><?php _e('Previous', WDM_WOO_SCHED_TXT_DOMAIN); ?></button>
                  <button type="button" class="button btn btn-info wdmFormNextButton" id="wdmFrmNxt"><?php _e('Next', WDM_WOO_SCHED_TXT_DOMAIN); ?></button> 
                  <input type="hidden" class="wdmStepManager" value="wdmSingleFormScheduleType"/>
                </div>
              </div>
            <?php
        }


        private function wdmMultistepBulkFormDays($formTypeName)
        {
            ?>
            <h5><?php _e("On Days", WDM_WOO_SCHED_TXT_DOMAIN); ?></h5>
            <div class="wdm-days-row" style="margin:10px">
            <div class="wdm-col-1">
                  <label class="wdmFormLabel"><input type="checkbox" class="wdmWeekDayCheckBoxes" data-week-day="Everyday" name="<?php echo $formTypeName; ?>[EveryDay]" checked/><?php _e("Everyday", WDM_WOO_SCHED_TXT_DOMAIN); ?></label>
                </div>
                <div class="wdm-col-1">
                    <label class="wdmFormLabel"><input type="checkbox" class="wdmWeekDayCheckBoxes" data-week-day="Monday" name="<?php echo $formTypeName; ?>[Monday]" checked/><?php _e("Mondays", WDM_WOO_SCHED_TXT_DOMAIN); ?></label>
                </div>
                <div class="wdm-col-1">
                    <label class="wdmFormLabel"><input type="checkbox" class="wdmWeekDayCheckBoxes" data-week-day="Tuesday" name="<?php echo $formTypeName; ?>[Tuesday]" checked/><?php _e("Tuesdays", WDM_WOO_SCHED_TXT_DOMAIN); ?> </label>
                </div>
                <div class="wdm-col-1">
                    <label class="wdmFormLabel"><input type="checkbox" class="wdmWeekDayCheckBoxes" data-week-day="Wednesday" name="<?php echo $formTypeName; ?>[Wednesday]" checked /> <?php _e("Wednesdays", WDM_WOO_SCHED_TXT_DOMAIN); ?> </label>
                </div>
                <div class="wdm-col-1">
                    <label class="wdmFormLabel"><input type="checkbox" class="wdmWeekDayCheckBoxes" data-week-day="Thursday" name="<?php echo $formTypeName; ?>[Thursday]" checked /><?php _e("Thursdays", WDM_WOO_SCHED_TXT_DOMAIN); ?> </label>
                </div>
                <div class="wdm-col-1">
                    <label class="wdmFormLabel"><input type="checkbox" class="wdmWeekDayCheckBoxes" data-week-day="Friday" name="<?php echo $formTypeName; ?>[Friday]" checked /><?php _e("Fridays", WDM_WOO_SCHED_TXT_DOMAIN); ?> </label>
                </div>
                <div class="wdm-col-1">
                    <label class="wdmFormLabel"><input type="checkbox" class="wdmWeekDayCheckBoxes" data-week-day="Saturday" name="<?php echo $formTypeName; ?>[Saturday]" checked /><?php _e("Saturdays", WDM_WOO_SCHED_TXT_DOMAIN); ?> </label>
                </div>
                <div class="wdm-col-1">
                    <label class="wdmFormLabel"><input type="checkbox" class="wdmWeekDayCheckBoxes" data-week-day="Sunday" name="<?php echo $formTypeName; ?>[Sunday]" checked/><?php _e("Sundays", WDM_WOO_SCHED_TXT_DOMAIN); ?> </label>
                </div>                        
            </div>
            <?php
        }



        /**A method to print category tables  */
        public function wdmPrintCategoryTables($tableWholeDayRows, $tableSpecificTimeRows)
        {
            if (""!=$tableWholeDayRows) {
                $tableHead="<h5 class='schedule-type-title'>".__("Schedules For Whole Time On Selected days", WDM_WOO_SCHED_TXT_DOMAIN)."</h5>".
                sprintf(
                    "<table class='scheduleTable' >
                <thead>
                    <tr>
                    <th>%s</th>
                    <th>%s</th>
                    <th>%s</th>
                    <th>%s</th>
                    <th>%s</th>                
                    <th>%s</th>
                    <th>%s</th>
                    <th>%s</th>
                    </tr>
                </thead>
                <tbody style='text-align:center;'>",
                    __('Title', WDM_WOO_SCHED_TXT_DOMAIN),
                    __('Start At', WDM_WOO_SCHED_TXT_DOMAIN),
                    __('End By', WDM_WOO_SCHED_TXT_DOMAIN),
                    __('Selected Days', WDM_WOO_SCHED_TXT_DOMAIN),
                    __('Skip Between', WDM_WOO_SCHED_TXT_DOMAIN),
                    __('Timers', WDM_WOO_SCHED_TXT_DOMAIN),
                    __('Hide Unavailable', WDM_WOO_SCHED_TXT_DOMAIN),
                    __('Action', WDM_WOO_SCHED_TXT_DOMAIN)
                );
                $tableWholeDay=$tableHead.$tableWholeDayRows."</tbody></table>";
                echo $tableWholeDay;
            }
            if (""!=$tableSpecificTimeRows) {
                $tableHead="<h5 class='schedule-type-title'>".__("Schedules For Specific Time On Selected days", WDM_WOO_SCHED_TXT_DOMAIN)."</h5>".
                sprintf(
                    "<table class='scheduleTable'>
                <thead>
                    <tr>
                    <th>%s</th>
                    <th>%s</th>
                    <th>%s</th>
                    <th>%s</th>
                    <th>%s</th>                
                    <th>%s</th>
                    <th>%s</th>
                    <th>%s</th>
                    </tr>
                </thead>
                <tbody>",
                    __('Title', WDM_WOO_SCHED_TXT_DOMAIN),
                    __('Date Duration', WDM_WOO_SCHED_TXT_DOMAIN),
                    __('Time Duration', WDM_WOO_SCHED_TXT_DOMAIN),
                    __('Selected Days', WDM_WOO_SCHED_TXT_DOMAIN),
                    __('Skip Between', WDM_WOO_SCHED_TXT_DOMAIN),
                    __('Timers', WDM_WOO_SCHED_TXT_DOMAIN),
                    __('Hide Unavailable', WDM_WOO_SCHED_TXT_DOMAIN),
                    __('Action', WDM_WOO_SCHED_TXT_DOMAIN)
                );
                $tableSpecificTime=$tableHead.$tableSpecificTimeRows."</tbody></table>";
                echo $tableSpecificTime;
            }
        }

        //moved from woo-schedule-ajax.php
        public function displayWrongSelection($selection_type, $valid_selection)
        {
            if ($this->wdmCheckSelectionType($selection_type, $valid_selection)) {
                //As selection is empty -- there must be some error

                ob_start();

                ?>
                <p class="bg-danger"><strong><?php _e('Wrong selection.', WDM_WOO_SCHED_TXT_DOMAIN); ?></strong></p>
                <?php

                $content = ob_get_clean();

                echo $content;

                die();
            }
        }
    }
}
