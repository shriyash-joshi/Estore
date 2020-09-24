<?php
namespace {
    
    if (!class_exists('SchedulerAdmin')) {
        class SchedulerAdminFunctions
        {
            /**Checks if the product is schedule is opted before saving the simple product */
            public function wdmCheckIfScheduled($productId)
            {
                $schedule=get_post_meta($productId, "wdm_schedule_settings", true);
                if (!empty($schedule)) {
                    return true;
                }
                return false;
            }

            public static function getDateTime($Date, $wdmTime, $type = 'product')
            {
                $DateTime = $Date.' '.$wdmTime;
                if ('category' == $type) {
                    return $DateTime;
                }
                $DateTime = str_replace(" PM", ":00 PM", $DateTime);
                return str_replace(" AM", ":00 AM", $DateTime);
            }


    
            public function saveNewScheduleData($productId, $wdmScheduleArray)
            {
                $scheduleType=$wdmScheduleArray['type'];
                switch ($scheduleType) {
                    case 'productLaunch':
                        self::wdmSaveLaunchSchedule($productId, $wdmScheduleArray);
                        break;
                    case 'wholeDay':
                        self::wdmSaveScheduleDuration($productId, $wdmScheduleArray);
                        break;
                    case 'specificTime':
                        self::wdmSaveScheduleDuration($productId, $wdmScheduleArray);
                        break;
                    default:
                        break;
                }
            }


            /**
             * Save Launch Product Settings and date time to the product meta
             *
             * @param [type] $productId
             * @param [type] $wdmScheduleArray
             * @return void
             */
            public static function wdmSaveLaunchSchedule($productId, $wdmScheduleArray)
            {
                self::wdmDeleteAllScheduleMeta($productId);
                update_post_meta($productId, 'wdm_start_date', $wdmScheduleArray['startDate']);
                update_post_meta($productId, 'wdm_start_time', $wdmScheduleArray['startTime']);
                update_post_meta($productId, '_hide_if_unavailable', $wdmScheduleArray['hideUnavailable']);
                self::updateScheduleRecordForLaunch($wdmScheduleArray['startTime'], $productId);
                $settings = array(
                    'type' => $wdmScheduleArray['type'],
                    'startTimer'    => $wdmScheduleArray["startTimer"],
                    'endTimer'      => '',
                    'daysSelected'  =>  array(),
                    );
                update_post_meta($productId, 'wdm_schedule_settings', $settings);
            }

            /**
             * Save Whole day schedule type settings , date-times to product meta
             *
             * @param [type] $productId
             * @param [type] $wdmScheduleArray
             * @return void
             */
            public static function wdmSaveScheduleDuration($productId, $wdmScheduleArray)
            {
                self::wdmDeleteAllScheduleMeta($productId);
                update_post_meta($productId, 'wdm_start_date', $wdmScheduleArray['startDate']);
                update_post_meta($productId, 'wdm_start_time', $wdmScheduleArray['startTime']);
                update_post_meta($productId, 'wdm_end_date', $wdmScheduleArray['endDate']);
                update_post_meta($productId, 'wdm_end_time', $wdmScheduleArray['endTime']);
                update_post_meta($productId, '_hide_if_unavailable', $wdmScheduleArray['hideUnavailable']);
                
                self::updateScheduleRecord($wdmScheduleArray['startTime'], $wdmScheduleArray['endTime'], $productId, "");
                
                $settings = array(
                    'type'          => $wdmScheduleArray['type'],
                    'startTimer'    => $wdmScheduleArray["startTimer"],
                    'endTimer'      => $wdmScheduleArray["endTimer"],
                    'daysSelected'  => $wdmScheduleArray["daysSelected"],
                    );

                if (!empty($wdmScheduleArray['skipStartDate'])) {
                    $settings['skipStartDate']=$wdmScheduleArray['skipStartDate'];
                    $settings['skipEndDate']=$wdmScheduleArray['skipEndDate'];
                }
                update_post_meta($productId, 'wdm_schedule_settings', $settings);
            }



            /**
             * This function is called before saving scheduler meta to the databse.
             * this function deletes all the existing scheduler meta fileds from the database.
             *
             * @param [type] $productId
             * @return void
             */
            public static function wdmDeleteAllScheduleMeta($productId)
            {
                delete_post_meta($productId, 'wdm_show_timer');
                delete_post_meta($productId, 'wdm_start_date');
                delete_post_meta($productId, 'wdm_end_date');
                delete_post_meta($productId, 'wdm_start_time_hr');
                delete_post_meta($productId, 'wdm_start_time_min');
                delete_post_meta($productId, 'wdm_end_time_hr');
                delete_post_meta($productId, 'wdm_end_time_min');
                delete_post_meta($productId, 'wdm_days_selected');
                delete_post_meta($productId, '_hide_if_unavailable');
                delete_post_meta($productId, 'wdm_days_selected');
                delete_post_meta($productId, 'wdm_schedule_settings');
            }


            public static function updateScheduleRecord($wdm_start_time, $wdm_end_time, $post_id, $type)
            {
                if (!empty($wdm_start_time) && !empty($wdm_end_time)) {
                    $start_time  =  $wdm_start_time . ":00";
                    $end_time    = $wdm_end_time . ":59";
        
                    $str_start_time  = strtotime($start_time);
                    $str_end_time    = strtotime($end_time);
                    
                    //Checking whether Start Time is greater than End Time. If true, both of them are set the same value.
                    if ($type == 'per_day' && ($str_start_time > $str_end_time)) {
                            $wdm_end_time = $wdm_start_time;
                    }
                    $wdm_start_time_split = explode(":", $wdm_start_time);
                    $wdm_end_time_split = explode(":", $wdm_end_time);

        
                    $wdm_start_time_hr = $wdm_start_time_split[0];
                    $wdm_start_time_min = $wdm_start_time_split[1];
                    $wdm_end_time_hr = $wdm_end_time_split[0];
                    $wdm_end_time_min = $wdm_end_time_split[1];

                    update_post_meta($post_id, 'wdm_start_time_hr', $wdm_start_time_hr);
                    update_post_meta($post_id, 'wdm_start_time_min', $wdm_start_time_min);
                    update_post_meta($post_id, 'wdm_end_time_hr', $wdm_end_time_hr);
                    update_post_meta($post_id, 'wdm_end_time_min', $wdm_end_time_min);
                } else {
                    delete_post_meta($post_id, 'wdm_start_time_hr');
                    delete_post_meta($post_id, 'wdm_start_time_min');
                    delete_post_meta($post_id, 'wdm_end_time_hr');
                    delete_post_meta($post_id, 'wdm_end_time_min');
                    delete_post_meta($post_id, 'wdm_show_timer');
                }
            }


            /**
             * Only start time is specified in case of product launch hence this function is
             * a colne of updateScheduleRecord , modified to store only start time of the product
             *
             * @param [type] $wdm_start_time
             * @param [type] $post_id
             * @return void
             */
            public static function updateScheduleRecordForLaunch($wdm_start_time, $post_id)
            {
                if (!empty($wdm_start_time)) {
                    $wdm_start_time_split = explode(":", $wdm_start_time);
                    $wdm_start_time_hr = $wdm_start_time_split[0];
                    $wdm_start_time_min = $wdm_start_time_split[1];
                    update_post_meta($post_id, 'wdm_start_time_hr', $wdm_start_time_hr);
                    update_post_meta($post_id, 'wdm_start_time_min', $wdm_start_time_min);
                } else {
                    delete_post_meta($post_id, 'wdm_start_time_hr');
                    delete_post_meta($post_id, 'wdm_start_time_min');
                    delete_post_meta($post_id, 'wdm_end_time_hr');
                    delete_post_meta($post_id, 'wdm_end_time_min');
                    delete_post_meta($post_id, 'wdm_show_timer');
                }
            }



        //section to get prefilled multistep form steps
            public function getFirstFormStep($productId, $scheduleData, $wdmProductSettings)
            {
                $type="";
                $scheduleDuration   ="";
                $productLaunch      ="";
                if (!empty($wdmProductSettings)) {
                    switch ($wdmProductSettings['type']) {
                        case 'productLaunch':
                            $scheduleDuration   ="";
                            $productLaunch      ="checked";
                            $type="productLaunch";
                            break;
                        case 'wholeDay':
                            $scheduleDuration   ="checked";
                            $productLaunch      ="";
                            $type="wholeDay";
                            break;
                        case 'specificTime':
                            $scheduleDuration   ="checked";
                            $productLaunch      ="";
                            $type="specificTime";
                            break;
                        default:
                            $scheduleDuration   ="";
                            $productLaunch      ="";
                            $type="";
                            break;
                    }
                }
                if (!empty($scheduleData['wdm_start_date']) && ($scheduleDuration=="" && $productLaunch=="")) {
                    $scheduleDuration   ="checked";
                    $productLaunch      ="";
                    $type="old";
                }
                ?>
                <!--First Step-->
                <div id="wdmSingleFormScheduleType[<?php echo $productId;?>]" class="wdm-form-step wdmSingleFormScheduleType">
                  <h4><?php _e('I want to', WDM_WOO_SCHED_TXT_DOMAIN);?></h4>
                  <div class="wdmFormRadioWrapper">
                    <label for="launchScheduleType[<?php echo $productId;?>]" class="wdmFormLabel">
                      <input type="radio" value="productLaunch" wdm-radio-group="ScheduleType" id="launchScheduleType[<?php echo $productId;?>]" name="ScheduleType[<?php echo $productId;?>]" <?php echo $productLaunch;?> >
                      <strong> <?php _e('Launch a product on a specific day', WDM_WOO_SCHED_TXT_DOMAIN);?></strong>
                      <p class="radio-discription"> <?php _e('Use this option if you want to schedule a product for spesific duration', WDM_WOO_SCHED_TXT_DOMAIN);?></p>
                    </label>
                  </div>
                
                  <div class="wdmFormRadioWrapper">
                    <label for="perDayScheduleType[<?php echo $productId;?>]" class="wdmFormLabel">
                      <input type="radio" value="scheduleDuration" wdm-radio-group="ScheduleType" id="perDayScheduleType[<?php echo $productId;?>]" name="ScheduleType[<?php echo $productId;?>]" <?php echo $scheduleDuration;?> >
                      <strong> <?php _e('Make a product available for some duration', WDM_WOO_SCHED_TXT_DOMAIN);?> </strong>
                      <p class="radio-discription"> <?php _e('Use this option if you want to repeatively schedule a product', WDM_WOO_SCHED_TXT_DOMAIN);?></p>
                    </label>
                  </div>
                </div>
                <?php
                return $type;
            }


            public function getSecondFormStep($productId, $type)
            {
                $wholeDay="";
                $specificTime="";
                if ($type!="old" || $type!="") {
                    if ($type=="wholeDay") {
                        $wholeDay="checked";
                    }
                    if ($type=="specificTime") {
                        $specificTime="checked";
                    }
                }
                if ($type=="old") {
                    $wdmwsSettings = \Includes\AdminSettings\WdmWsSettings::getSettings();
                    $type= isset($wdmwsSettings) && isset($wdmwsSettings['wdmws_custom_product_expiration_type'][0]) ? $wdmwsSettings['wdmws_custom_product_expiration_type'][0] : 'per_day' ;
                    if ($type=="per_day") {
                        $specificTime="checked";
                        $type="specificTime";
                    } else {
                        $wholeDay="checked";
                        $type="wholeDay";
                    }
                }

                ?>
                <!--Second Step-->
                <div id="wdmSingleFormScheduleAvailability[<?php echo $productId;?>]" class="wdm-form-step wdmSingleFormScheduleAvailability">
                  <h4><?php _e('I want to keep it available', WDM_WOO_SCHED_TXT_DOMAIN);?></h4>
                  <div class="wdmFormRadioWrapper">
                    <label for="wholeTimeAvailabilityType[<?php echo $productId;?>]" class="wdmFormLabel">
                      <input type="radio" value="wholeDay" wdm-radio-group="AvailabilityType" id="wholeTimeAvailabilityType[<?php echo $productId;?>]" name="AvailabilityType[<?php echo $productId;?>]" <?php echo $wholeDay; ?>>
                      <strong> <?php _e('For the whole time on the selected days', WDM_WOO_SCHED_TXT_DOMAIN);?></strong>
                      <p class="radio-discription"> <?php _e('Use this option if product should be availabe whole days between availability period', WDM_WOO_SCHED_TXT_DOMAIN);?></p>
                    </label>
                  </div>
                  <div class="wdmFormRadioWrapper">
                    <label for="specificTimeAvailabilityType[<?php echo $productId;?>]" class="wdmFormLabel">
                      <input type="radio" value="specificTime" wdm-radio-group="AvailabilityType" id="specificTimeAvailabilityType[<?php echo $productId;?>]" name="AvailabilityType[<?php echo $productId;?>]" <?php echo $specificTime; ?>>
                      <strong> <?php _e('For the specific time on the selected days', WDM_WOO_SCHED_TXT_DOMAIN);?> </strong>
                      <p class="radio-discription"> <?php _e('Use this option if product should be available for some time only on each selected day between availability period', WDM_WOO_SCHED_TXT_DOMAIN);?></p>
                    </label>
                  </div>
                </div>
                <?php
                return $type;
            }

            public function getThirdFormStepWholeDay($productId, $scheduleData, $wdmProductSettings, $type, $typeTwo)
            {
                $startDate      ="";
                $startTime      ="";
                $endDate        ="";
                $endTime        ="";
                $skipStartDate  ="";
                $skipEndDate    ="";
                $daysSelected=array();
                if ($type=="wholeDay" || $typeTwo=="wholeDay") {
                    $startDate      =$scheduleData['wdm_start_date'];
                    $startTime      =$scheduleData['wdm_start_time'];
                    $endDate        =$scheduleData['wdm_end_date'];
                    $endTime        =$scheduleData['wdm_end_time'];
                    if (isset($wdmProductSettings) && !empty($wdmProductSettings)) {
                        $skipStartDate  =isset($wdmProductSettings['skipStartDate'])?$wdmProductSettings['skipStartDate']:"";
                        $skipEndDate    =isset($wdmProductSettings['skipEndDate'])?$wdmProductSettings['skipEndDate']:"";
                        $daysSelected   =isset($wdmProductSettings['daysSelected'])?$wdmProductSettings['daysSelected']:array();
                    }
                }
                ?>
                <!--Third Step 3.1-->
                <div id="wdmSingleFormScheduleTypeWholeDay[<?php echo $productId;?>]" class="wdm-form-step wdmSingleFormScheduleTypeWholeDay">
                    <h4> <?php _e('Product should be available', WDM_WOO_SCHED_TXT_DOMAIN);?></h4>
                    <div class="wdm-date-container">
                    <h5><?php _e('From', WDM_WOO_SCHED_TXT_DOMAIN);?></h5>
                    <div class="wdm-row">
                      <div style="position: relative;" class="wdm-col-5">
                        <input type="text" name="wdmWdStartDate[<?php echo $productId;?>]" id="wdmWdStartDate[<?php echo $productId;?>]" class="wdmDatePicker wdmWdStartDate form-control" value="<?php echo $startDate; ?>" placeholder="mm / dd / yyyy">
                        <span class="calender-img"></span>
                      </div>
                      <div class="wdm-col-1 text-center"></div>
                      <div style="position: relative;" class="wdm-col-5">
                        <input type="text" name="wdmWdStartTime[<?php echo $productId;?>]" id="wdmWdStartTime[<?php echo $productId;?>]" class="wdmTimePicker wdmWdStartTime form-control" value="<?php echo $startTime; ?>" placeholder="hh:mm">
                        <span class="clock-img"></span>
                      </div>
                    </div>
                    <h5><?php _e('To', WDM_WOO_SCHED_TXT_DOMAIN);?></h5>
                    <div class="wdm-row">
                        <div style="position: relative;" class="wdm-col-5">
                          <input type="text" name="wdmWdEndDate[<?php echo $productId;?>]" id="wdmWdEndDate[<?php echo $productId;?>]" class="wdmDatePicker wdmWdEndDate form-control" value="<?php echo $endDate; ?>" placeholder="mm / dd / yyyy">
                          <span class="calender-img"></span>
                        </div>
                        <div class="wdm-col-1 text-center"></div>
                        <div style="position: relative;" class="wdm-col-5">
                          <input type="text" name="wdmWdEndTime[<?php echo $productId;?>]" id="wdmWdEndTime[<?php echo $productId;?>]" class="wdmTimePicker wdmWdEndTime form-control" value="<?php echo $endTime; ?>" placeholder="hh:mm">
                          <span class="clock-img"></span>
                        </div>
                    </div>
                    </div>
                    <div class="wdm-day-container">
                    <?php  $this->wdmMultistepFormDays($productId, 'wdmWdWeekdays', $daysSelected);  ?>  
                    </div>
                    <div class="wdm-duration-container">                
                    <h5><?php _e('Skip During', WDM_WOO_SCHED_TXT_DOMAIN);?><span class="optional"> <?php _e('(Optional)', WDM_WOO_SCHED_TXT_DOMAIN);?></span></h5> 
                    <div class="wdm-row">
                      <div style="position: relative;" class=" wdm-col-5">
                        <input type="text" name="wdmWdSkipStartDate[<?php echo $productId;?>]" id="wdmWdSkipStartDate[<?php echo $productId;?>]" class="wdmDatePicker wdmWdSkipStartDate form-control" value="<?php echo $skipStartDate; ?>" placeholder="mm / dd / yyyy">
                        <span class="calender-img"></span>
                      </div>
                      <div class="wdm-col-1 text-center"> <span><h5><?php _e(' To ', WDM_WOO_SCHED_TXT_DOMAIN);?></h5></span> </div>
                      <div style="position: relative;" class=" wdm-col-5">
                          <input type="text" name="wdmWdSkipEndDate[<?php echo $productId;?>]" id="wdmWdSkipEndDate[<?php echo $productId;?>]" class="wdmDatePicker wdmWdSkipEndDate form-control" value="<?php echo $skipEndDate; ?>" placeholder="mm / dd / yyyy">
                          <span class="calender-img"></span>
                      </div>
                    </div>
                    </div>
                  </div> 
                <?php
            }

            public function getThirdFormStepSpecificTime($productId, $scheduleData, $wdmProductSettings, $type, $typeTwo)
            {
                $startDate      ="";
                $startTime      ="";
                $endDate        ="";
                $endTime        ="";
                $skipStartDate  ="";
                $skipEndDate    ="";
                $daysSelected=array();
                if ($type=="specificTime" || $typeTwo=="specificTime") {
                    $startDate      =$scheduleData['wdm_start_date'];
                    $startTime      =$scheduleData['wdm_start_time'];
                    $endDate        =$scheduleData['wdm_end_date'];
                    $endTime        =$scheduleData['wdm_end_time'];
                    if (isset($wdmProductSettings['skipStartDate'])) {
                        $skipStartDate  =$wdmProductSettings['skipStartDate'];
                        $skipEndDate    =$wdmProductSettings['skipEndDate'];
                    }
                    $daysSelected   =isset($wdmProductSettings['daysSelected'])?$wdmProductSettings['daysSelected']:array();
                    if ($type=="old" && $typeTwo=="specificTime") {
                        $daysSelected=$scheduleData['wdm_date_array'];
                    }
                }
                ?>
                <!--Third Step 3.2-->
                <div id="wdmSingleFormScheduleSpecificTime[<?php echo $productId;?>]" class="wdm-form-step wdmSingleFormScheduleSpecificTime">
                    <h4> <?php _e("Product should be available", WDM_WOO_SCHED_TXT_DOMAIN);?> </h4>
                    <div class="wdm-date-container">
                    <div class="wdm-row">
                     <div class="wdm-col-6"><h5><?php _e("Between Dates", WDM_WOO_SCHED_TXT_DOMAIN); ?></h5></div>
                    </div>
                    <div class="wdm-row">
                      <div style="position: relative;" class="wdm-col-5">
                        <input type="text" name="wdmLtStartDate[<?php echo $productId;?>]" id="wdmLtStartDate[<?php echo $productId;?>]" class="wdmDatePicker wdmLtStartDate form-control" value="<?php echo $startDate; ?>" placeholder="mm / dd / yyyy">
                        <span class="calender-img"></span>
                      </div>
                      <div class="wdm-col-1 text-center"> <span> <h5><?php _e('To', WDM_WOO_SCHED_TXT_DOMAIN);?></h5> </span> </div>
                      <div style="position: relative;" class="wdm-col-5">
                          <input type="text" name="wdmLtEndDate[<?php echo $productId;?>]" id="wdmLtEndDate[<?php echo $productId;?>]" class="wdmDatePicker wdmLtEndDate form-control" value="<?php echo $endDate; ?>" placeholder="mm / dd / yyyy">
                          <span class="calender-img"></span> 
                        </div>
                    </div>
                    </div>
                    <div class="wdm-day-container">
                    <?php  $this->wdmMultistepFormDays($productId, 'wdmLtWeekdays', $daysSelected);  ?>
                    </div>
                    <div class="wdm-duration-container">
                    <h5><?php _e('Between Time', WDM_WOO_SCHED_TXT_DOMAIN);?></h5>
                    <div class="wdm-row">
                        <div style="position: relative;" class="wdm-col-5">
                            <input type="text" name="wdmLtStartTime[<?php echo $productId;?>]" id="wdmLtStartTime[<?php echo $productId;?>]" class="wdmTimePicker wdmLtStartTime form-control" value="<?php echo $startTime; ?>" placeholder="hh:mm">
                            <span class="clock-img"></span>
                        </div>
                        <div class="wdm-col-1 text-center"> <span> <h5><?php _e('To', WDM_WOO_SCHED_TXT_DOMAIN);?></h5> </span> </div>
                        <div style="position: relative;" class="wdm-col-5">
                          <input type="text" name="wdmLtEndTime[<?php echo $productId;?>]" id="wdmLtEndTime[<?php echo $productId;?>]" class="wdmTimePicker wdmLtEndTime form-control" value="<?php echo $endTime; ?>" placeholder="hh:mm">
                          <span class="clock-img"></span>
                        </div>
                    </div>
                    <h5><?php _e('Skip During', WDM_WOO_SCHED_TXT_DOMAIN);?><span class="optional"><?php _e('Optional', WDM_WOO_SCHED_TXT_DOMAIN);?></span></h5> 
                    <div class="wdm-row">
                      <div style="position: relative;" class="wdm-col-5">
                        <input type="text" name="wdmLtSkipStartDate[<?php echo $productId;?>]" id="wdmLtSkipStartDate[<?php echo $productId;?>]" class="wdmDatePicker wdmLtSkipStartDate form-control" value="<?php echo $skipStartDate; ?>" placeholder="mm / dd / yyyy">
                        <span class="calender-img"></span>
                      </div>
                      <div class="wdm-col-1 text-center"> <span> <h5><?php _e('To', WDM_WOO_SCHED_TXT_DOMAIN);?></h5> </span> </div>
                      <div style="position: relative;" class="wdm-col-5">
                          <input type="text" name="wdmLtSkipEndDate[<?php echo $productId;?>]" id="wdmLtSkipEndDate[<?php echo $productId;?>]" class="wdmDatePicker wdmLtSkipEndDate form-control" value="<?php echo $skipEndDate; ?>" placeholder="mm / dd / yyyy">
                          <span class="calender-img"></span>
                      </div>
                    </div>
                </div>
                </div> 

                <?php
            }

            public function getThirdFormStepLaunch($productId, $scheduleData, $type)
            {
                $startDate="";
                $startTime="";
                if ($type=="productLaunch") {
                    $startDate=$scheduleData['wdm_start_date'];
                    $startTime=$scheduleData['wdm_start_time'];
                }
                ?>
                <!--Third Step 3.3-->
                <div id="wdmSingleFormScheduleLaunch[<?php echo $productId;?>]" class="wdm-form-step wdmSingleFormScheduleLaunch">
                  <h4> <?php _e("Launch this Product", WDM_WOO_SCHED_TXT_DOMAIN)?></h4>
                  <div class="wdm-row">
                    <div style="position: relative;" class="wdm-col-5">
                      <h5><?php _e("On", WDM_WOO_SCHED_TXT_DOMAIN) ?></h5>
                      <input type="text" name="wdmLaunchDate[<?php echo $productId; ?>]" data-variation_id="<?php echo $productId; ?>" id="wdmLaunchDate[<?php echo $productId; ?>]" class="wdmDatePicker wdmLaunchDate form-control" value="<?php echo $startDate; ?>" placeholder="mm / dd / yyyy"><span class="calender-img"></span>
                    </div>
                    <div class="wdm-col-1 text-center"></div>
                    <div style="position: relative;" class="wdm-col-5">
                      <h5><?php _e("At", WDM_WOO_SCHED_TXT_DOMAIN); ?></h5>
                      <input type="text" name="wdmLaunchTime[<?php echo $productId; ?>]" data-variation_id="<?php echo $productId; ?>" id="wdmLaunchTime[<?php echo $productId; ?>]" class="wdmTimePicker wdmLaunchTime form-control" value="<?php echo $startTime; ?>" placeholder="hh:mm"><span class="clock-img"></span>
                      <br>
                    </div>
                  </div>
                </div>
                <?php
            }


            public function wdmGetFinalStep($productId, $scheduleData, $wdmProductSettings, $type, $typeTwo)
            {
                $startTimer         ="";
                $endTimer           ="";
                $hideUnavailable    ="";
                $hideUnavailable    =$scheduleData['wdm_hide_unavailable']=="yes"?"checked":"";
                
                if ($type=="old") {
                    if ($scheduleData['wdm_show_timer']=="checked") {
                        $startTimer="checked";
                        $endTimer="checked";
                    }
                }
                if ($type!="old" && !empty($type) && ($typeTwo="wholeDay" || $typeTwo="specificTime")) {
                    $startTimer         =$wdmProductSettings['startTimer']=="on"?"checked":"";
                    $endTimer           =$wdmProductSettings['endTimer']=="on"?"checked":"";
                }

                ?>
                <!--Fourth Step-->
                <div id="wdmSingleFormScheduleTimer[<?php echo $productId;?>]" class="wdm-form-step wdmSingleFormScheduleTimer">
                  <h4><?php _e('Display & Timer Options', WDM_WOO_SCHED_TXT_DOMAIN);?></h4>
                  <div class="wdmFormCheckboxWrapper">
                    <label for="startTimer[<?php echo $productId;?>]" class="wdmFormLabel">
                      <input type="checkbox" class="wdmStartTimer" id="startTimer[<?php echo $productId;?>]" name="startTimer[<?php echo $productId; ?>]" data-variation_id="<?php echo $productId; ?>" <?php echo $startTimer; ?>>
                      <strong> <?php _e(' Enable Start Timer', WDM_WOO_SCHED_TXT_DOMAIN);?></strong>
                      <p class="checkbox-discription"><?php _e('Use this option to show sale start countdown timer on the product page', WDM_WOO_SCHED_TXT_DOMAIN);?></p>
                    </label>
                  </div>
                  <div class="wdmFormCheckboxWrapper">
                    <label for="wdmEndTimer[<?php echo $productId;?>]" class="wdmFormLabel">
                      <input type="checkbox" class="wdmEndTimer" id="wdmEndTimer[<?php echo $productId;?>]" name="wdmEndTimer[<?php echo $productId; ?>]" data-variation_id="<?php echo $productId; ?>" <?php echo $endTimer; ?>>
                      <strong><?php _e(' Enable End Timer', WDM_WOO_SCHED_TXT_DOMAIN);?> </strong>
                      <p class="checkbox-discription"><?php _e('Use this option to show sale end countdown timer on your product page', WDM_WOO_SCHED_TXT_DOMAIN);?></p>
                    </label>
                  </div>
                  <div class="wdmFormCheckboxWrapper" title="<?php _e('Start timer won\'t be available when this option is enabled');?>">
                    <label for="wdmHideUnavailabe[<?php echo $productId;?>]" class="wdmFormLabel">
                      <input type="checkbox" class="wdmHideUnavailable" id="wdmHideUnavailabe[<?php echo $productId;?>]" name="wdmHideUnavailabe[<?php echo $productId; ?>]" data-variation_id="<?php echo $productId; ?>" <?php echo $hideUnavailable; ?>>
                      <strong> <?php _e(' Hide Product When Not Available', WDM_WOO_SCHED_TXT_DOMAIN);?></strong>
                      <p class="checkbox-discription"> <?php _e('Use this option to hide your product when it is not available', WDM_WOO_SCHED_TXT_DOMAIN); ?></p>
                    </label>
                  </div>
                </div>
                <div id="wdmNextPrev[<?php echo $productId;?>]" class="wdmNextPrev">
                    <button type="button" class="button btn btn-info wdmFormCancelButton" wdm-product-id="<?php echo $productId ;?>"  id="wdmFrmCancel[<?php echo $productId;?>]" ><?php _e(' Cancel', WDM_WOO_SCHED_TXT_DOMAIN); ?></button>
                    <button type="button" class="button btn btn-info wdmFormPreviousButton"     id="wdmFrmPrev[<?php echo $productId;?>]" disabled><?php _e('Previous', WDM_WOO_SCHED_TXT_DOMAIN); ?></button>
                    <button type="button" class="button btn btn-info wdmFormNextButton" id="wdmFrmNxt[<?php echo $productId;?>]"><?php _e('Next', WDM_WOO_SCHED_TXT_DOMAIN); ?></button> 
                    <input  class="wdmStepManager" value="wdmSingleFormScheduleType" type="hidden"/>
                    <div class="finalized-msg">
                        <?php _e('Please update the product to save the schedule.', WDM_WOO_SCHED_TXT_DOMAIN); ?>
                    </div>
                </div>
              </div>
              <?php
            }

            public function wdmMultistepFormDays($productId, $formTypeName, $daysSelected = array())
            {
                $days=array("Everyday"=>"","Monday"=>"","Tuesday"=>"","Wednesday"=>"","Thursday"=>"","Friday"=>"","Saturday"=>"","Sunday"=>"");
                
                if (empty($daysSelected) || (isset($daysSelected) && sizeof($daysSelected)>6)) {
                    $days=array("Everyday"=>"checked","Monday"=>"checked","Tuesday"=>"checked","Wednesday"=>"checked","Thursday"=>"checked","Friday"=>"checked","Saturday"=>"checked","Sunday"=>"checked");
                } else {
                    foreach ($daysSelected as $day => $status) {
                        $days[$day]="checked";
                        unset($status);
                    }
                }

                ?>
                <h5><?php _e("On Days", WDM_WOO_SCHED_TXT_DOMAIN); ?></h5>
                <div class="wdm-days-row" style="margin:10px">
                <div class="wdm-col-1">
                      <label class="wdmFormLabel <?php echo "wdmws-color-".$days["Everyday"] ?>"><input type="checkbox" class="wdmWeekDayCheckBoxes" data-week-day="everyday" name="<?php echo $formTypeName."[".$productId."]"; ?>[EveryDay]" <?php echo $days["Everyday"]; ?>/><?php _e("Everyday", WDM_WOO_SCHED_TXT_DOMAIN); ?></label>
                    </div>
                    <div class="wdm-col-1">
                        <label class="wdmFormLabel <?php echo "wdmws-color-".$days["Monday"] ?>"><input type="checkbox" class="wdmWeekDayCheckBoxes" data-week-day="monday" name="<?php echo $formTypeName."[".$productId."]"; ?>[Monday]" <?php echo $days["Monday"]; ?> /><?php _e("Mondays", WDM_WOO_SCHED_TXT_DOMAIN); ?></label>
                    </div>
                    <div class="wdm-col-1">
                        <label class="wdmFormLabel <?php echo "wdmws-color-".$days["Tuesday"] ?>"><input type="checkbox" class="wdmWeekDayCheckBoxes" data-week-day="tuesday" name="<?php echo $formTypeName."[".$productId."]"; ?>[Tuesday]" <?php echo $days["Tuesday"]; ?>/> <?php _e("Tuesdays", WDM_WOO_SCHED_TXT_DOMAIN); ?> </label>
                    </div>
                    <div class="wdm-col-1">
                        <label class="wdmFormLabel <?php echo "wdmws-color-".$days["Wednesday"] ?>"><input type="checkbox" class="wdmWeekDayCheckBoxes" data-week-day="wednesday" name="<?php echo $formTypeName."[".$productId."]"; ?>[Wednesday]" <?php echo $days["Wednesday"]; ?>/><?php _e("Wednesdays", WDM_WOO_SCHED_TXT_DOMAIN); ?> </label>
                    </div>
                    <div class="wdm-col-1">
                        <label class="wdmFormLabel <?php echo "wdmws-color-".$days["Thursday"] ?>"><input type="checkbox" class="wdmWeekDayCheckBoxes" data-week-day="thursday" name="<?php echo $formTypeName."[".$productId."]"; ?>[Thursday]" <?php echo $days["Thursday"]; ?>/> <?php _e("Thursdays", WDM_WOO_SCHED_TXT_DOMAIN); ?> </label>
                    </div>
                    <div class="wdm-col-1">
                        <label class="wdmFormLabel <?php echo "wdmws-color-".$days["Friday"] ?>"><input type="checkbox" class="wdmWeekDayCheckBoxes" data-week-day="friday" name="<?php echo $formTypeName."[".$productId."]"; ?>[Friday]" <?php echo $days["Friday"]; ?>/><?php _e("Fridays", WDM_WOO_SCHED_TXT_DOMAIN); ?> </label>
                    </div>
                    <div class="wdm-col-1">
                        <label class="wdmFormLabel <?php echo "wdmws-color-".$days["Saturday"] ?>"><input type="checkbox" class="wdmWeekDayCheckBoxes" data-week-day="saturday" name="<?php echo $formTypeName."[".$productId."]"; ?>[Saturday]" <?php echo $days["Saturday"]; ?>/> <?php _e("Saturdays", WDM_WOO_SCHED_TXT_DOMAIN); ?> </label>
                    </div>
                    <div class="wdm-col-1">
                        <label class="wdmFormLabel <?php echo "wdmws-color-".$days["Sunday"] ?>"><input type="checkbox" class="wdmWeekDayCheckBoxes" data-week-day="sunday" name="<?php echo $formTypeName."[".$productId."]"; ?>[Sunday]" <?php echo $days["Sunday"]; ?>/><?php _e("Sundays", WDM_WOO_SCHED_TXT_DOMAIN); ?> </label>
                    </div>                        
                </div>
                <?php
            }

            public function getFormStatusStep($productId, $scheduleData, $wdmProductSettings)
            {
                echo '<div class="wdm-form-status">';
                if (empty($scheduleData['wdm_start_date'])) {
                    $this->getSetNewScheduleButton($productId, false);
                    echo "<div>No Existing Schedule Found</div>";
                } else {
                    echo "<table class=' table-status'>";
                    if (empty($wdmProductSettings)) {
                        $wdmProductSettings=array('type'=>'old');
                    }
                    switch ($wdmProductSettings['type']) {
                        case 'productLaunch':
                            $this->getSetNewScheduleButton($productId, true);
                            echo "<caption>Existing Schedule Type : Product Launch</caption>";
                            echo "<tr><td>Launch Date </td><td>".$scheduleData['wdm_start_date']." (".date("l", strtotime($scheduleData['wdm_start_date'])).")</td></tr>";
                            echo "<tr><td>Launch Time </td><td>".$scheduleData['wdm_start_time']."</td></tr>";
                            echo "<tr><td colspan='2' class='caption'><strong>Other Settings</strong></td></tr>";
                            echo "<tr><td>Start Timer </td><td>";
                            echo $wdmProductSettings['startTimer']?"<span class='dashicons dashicons-yes' title='Enabled'></span>":"<span class='dashicons dashicons-no-alt' title='Disabled'></span>";
                            echo "</td></tr>";
                            $hideOption=$scheduleData['wdm_hide_unavailable']=="yes"?"<span class='dashicons dashicons-yes' title='Enabled'></span>":"<span class='dashicons dashicons-no-alt' title='Disabled'></span>";
                            echo "<tr><td>Hide On Unavailability </td><td>$hideOption</td></tr>";
                            break;
                        case 'wholeDay':
                            $this->getSetNewScheduleButton($productId, true);
                            echo "<caption>Existing Schedule Type : Whole Time On Selected Days</caption>";
                            $dateTimeDay=$scheduleData['wdm_start_date']." ".$scheduleData['wdm_start_time'];
                            $dateTimeDay= $dateTimeDay." (".date("l", strtotime($scheduleData['wdm_start_date'])).")";
                            echo "<tr><td>From  </td><td>$dateTimeDay</td></tr>";
                            $dateTimeDay=$scheduleData['wdm_end_date']." ".$scheduleData['wdm_end_time'];
                            $dateTimeDay= $dateTimeDay." (".date("l", strtotime($scheduleData['wdm_end_date'])).")";
                            echo "<tr><td>To  </td><td>$dateTimeDay</td></tr>";
                            echo "<tr><td colspan='2' class='caption'><strong>Other Settings</strong></td></tr>";
                            echo "<tr><td>Start Timer </td><td>";
                            echo $wdmProductSettings['startTimer']?"<span class='dashicons dashicons-yes' title='Enabled'></span>":"<span class='dashicons dashicons-no-alt' title='Disabled'></span>";
                            echo "</td></tr>";
                            echo "<tr><td>End Timer </td><td>";
                            echo $wdmProductSettings['endTimer']?"<span class='dashicons dashicons-yes' title='Enabled'></span>":"<span class='dashicons dashicons-no-alt' title='Disabled'></span>";
                            echo "</td></tr>";
                            echo "<tr><td>Selected Days </td><td>";
                            $days=$this->wdmwsgetStatusDaysView($wdmProductSettings['daysSelected']);
                            echo $days;
                            echo "</td></tr>";
                            echo "</td></tr>";
                            echo "<tr><td>Skip Between  </td><td>";
                            echo isset($wdmProductSettings['skipStartDate'])?$wdmProductSettings['skipStartDate']:"-";
                            echo "-";
                            echo isset($wdmProductSettings['skipEndDate'])?$wdmProductSettings['skipEndDate']:"-";
                            echo "</td></tr>";
                            $hideOption=$scheduleData['wdm_hide_unavailable']=="yes"?"<span class='dashicons dashicons-yes' title='Enabled'></span>":"<span class='dashicons dashicons-no-alt' title='Disabled'></span>";
                            echo "<tr><td>Hide On Unavailability </td><td>".$hideOption."</td></tr>";
                            break;
                        case 'specificTime':
                            $this->getSetNewScheduleButton($productId, true);
                            echo "<caption>Existing Schedule Type : Specific Time On Selected Days</caption>";
                            $startDateTimeDay=$scheduleData['wdm_start_date'];
                            $startDateTimeDay= $startDateTimeDay." (".date("D", strtotime($scheduleData['wdm_start_date'])).")";
                            $endDateTimeDay=$scheduleData['wdm_end_date'];
                            $endDateTimeDay= $endDateTimeDay." (".date("D", strtotime($scheduleData['wdm_end_date'])).")";

                            echo "<tr><td>Date Duration </td><td>".$startDateTimeDay." To ".$endDateTimeDay."</td></tr>";
                            echo "<tr><td>Time Duration </td><td>".$scheduleData['wdm_start_time']." To ".$scheduleData['wdm_end_time']."</td></tr>";
                            echo "<tr><td colspan='2' class='caption'><strong>Other Settings</strong></td></tr>";
                            echo "<tr><td>Start Timer </td><td>";
                            echo $wdmProductSettings['startTimer']?"<span class='dashicons dashicons-yes' title='Enabled'></span>":"<span class='dashicons dashicons-no-alt' title='Disabled'></span>";
                            echo "</td></tr>";
                            echo "<tr><td>End Timer </td><td>";
                            echo $wdmProductSettings['endTimer']?"<span class='dashicons dashicons-yes' title='Enabled'></span>":"<span class='dashicons dashicons-no-alt' title='Disabled'></span>";
                            echo "</td></tr>";
                            echo "<tr><td>Selected Days </td><td>";
                            $days=$this->wdmwsgetStatusDaysView($wdmProductSettings['daysSelected']);
                            echo $days;
                            echo "</td></tr>";
                            echo "</td></tr>";
                            echo "<tr><td>Skip Between  </td><td>";
                            echo isset($wdmProductSettings['skipStartDate'])?$wdmProductSettings['skipStartDate']:"-";
                            echo "-";
                            echo isset($wdmProductSettings['skipEndDate'])?$wdmProductSettings['skipEndDate']:"-";
                            echo "</td></tr>";
                            $hideOption=$scheduleData['wdm_hide_unavailable']=="yes"?"<span class='dashicons dashicons-yes' title='Enabled'></span>":"<span class='dashicons dashicons-no-alt' title='Disabled'></span>";
                            echo "<tr><td>Hide On Unavailability </td><td>".$hideOption."</td></tr>";
                            break;
                        default:
                            $this->getSetNewScheduleButton($productId, true);
                            echo "<caption>Existing Schedule Type : Scheduled with Old Version <br>(Please deactivate & activate the plugin Woocommerce Scheduler)</caption>";
                            echo "<tr><td>Start Date </td><td>".$scheduleData['wdm_start_date']."</td></tr>";
                            echo "<tr><td>Start Time </td><td>".$scheduleData['wdm_start_time']."</td></tr>";
                            echo "<tr><td>End Date </td><td>".$scheduleData['wdm_end_date']."</td></tr>";
                            echo "<tr><td>End Time </td><td>".$scheduleData['wdm_end_time']."</td></tr>";
                            echo "<tr><td colspan='2' class='caption'><strong>Other Settings</strong></td></tr>";
                            echo "<tr><td>Hide On Unavailability </td><td>".$scheduleData['wdm_hide_unavailable']."</td></tr>";
                            break;
                    }
                    echo "</table>";
                }
                echo "</div>";
                ?>


                <?php
            }

            private function wdmwsgetStatusDaysView($daysSelected)
            {
                if (empty($daysSelected)) {
                    return "No Days Selected";
                }
                if (sizeof($daysSelected)>6) {
                    return "All Days";
                }
                $days=array("Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday");
                $returnVal="<p>";
                $selDays=array_keys($daysSelected);
                foreach ($days as $day) {
                    if (in_array($day, $selDays)) {
                        $returnVal=$returnVal." ".$day.",";
                    } else {
                        $returnVal=$returnVal." <del>".$day."</del>,";
                    }
                }
                $returnVal=rtrim($returnVal, ',');
                $returnVal=$returnVal."</p>";
                return $returnVal;
            }

            private function getSetNewScheduleButton($productId, $scheduleExist)
            {
                $clearButtonVisibility=$scheduleExist?'':'hidden';
                ?>
                <br>
                <div class="wdmScheduleButtons">
                <input type="button" value="New Schedule" wdm-product-id="<?php echo $productId ;?>" class="wdmNewSchedule button">
                <input type="button" value="Clear Schedule" wdm-product-id="<?php echo $productId ;?>" class="wdmClearSchedule button <?php echo $clearButtonVisibility;?>">   
                </div>
                <br>
                <?php
            }


            /**
             * This method removes the product from availability arrays which are used to manage
             * Hide when unavailable option,
             * when Product is scheduled for the launch and launch cron gets executed this
             * method removes the product from availability array in order to prevent it from
             * getting hidden again.
             *
             * * Note: calling this method inside a loop is nessesory
             * because recording the launch products and updating variable after the loop won't be
             * convinient in the case where multiple crons running at the same time.
             * @param [type] $pid
             * @param [type] $postType
             * @param [type] $availableProducts
             * @return void
             */
            public function wdmRemoveFromAvailableArrayIfLaunched($pid, $postType, $availableProducts)
            {
                $scheduleSettings=get_post_meta($pid, 'wdm_schedule_settings', true);

                if (!empty($scheduleSettings) && $scheduleSettings['type']!='productLaunch') {
                    return;
                }
                switch ($postType) {
                    case 'product_variation':
                    case 'variation':
                        $availableProducts=get_option('wdm_hide_variants');
                        $availableProducts=array_diff($availableProducts, array($pid));
                        update_option('wdm_hide_variants', $availableProducts);
                        break;
                    case 'simple':
                        $availableProducts=array_diff($availableProducts, array($pid));
                        update_option('wdm_avaliable_products', $availableProducts);
                        break;
                }
            }
        }
    }
}
