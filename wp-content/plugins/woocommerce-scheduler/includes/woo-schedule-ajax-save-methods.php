<?php
namespace WooScheduleAjax;

if (!class_exists('WooScheduleSaveMethods')) {
    /**
    * class WooScheduleSaveMethods contains all the methods used to validate & save
    * the scheduler data to the database.
    */
    class WooScheduleSaveMethods
    {


/****************************************************************************************************************
 * This Section Contains Methods which are used to save the schedule to the database.*
 ****************************************************************************************************************/
        /**
         * This method checks if schedule settings selected are valid according
         * to the supported scheduling types & scheduleObject (category & products)
         */
        public function getWdmValidateSchedule($scheduleData, $scheduleObject)
        {
            if ("category"==$scheduleObject && "productLaunch"==$scheduleData['type']) {
                ob_start();
                ?>
                <p class="bg-danger"><strong><?php _e('Scheduling the product categories, is currently not supported', WDM_WOO_SCHED_TXT_DOMAIN); ?></strong></p>
                <?php
                $content = ob_get_clean();
                echo $content;
                die();
            }
            
            switch ($scheduleData['type']) {
                case 'productLaunch':
                    return wdmValidateLaunchSchedule($scheduleData);
                case 'wholeDay':
                    return wdmValidateWholeDaySchedule($scheduleData);
                case 'specificTime':
                    return wdmValidateSpecificTimeSchedule($scheduleData);
                default:
                    return false;
            }
        }

       

        /**
         * Save the schedule data according to the type of the schedule specified.
         */
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
            $hideUnavailable=$wdmScheduleArray['hideUnavailable']?"yes":"no";
            update_post_meta($productId, 'wdm_start_date', $wdmScheduleArray['startDate']);
            update_post_meta($productId, 'wdm_start_time', $wdmScheduleArray['startTime']);
            update_post_meta($productId, '_hide_if_unavailable', $hideUnavailable);
            self::updateScheduleRecordForLaunch($wdmScheduleArray['startTime'], $productId);
            $settings = array(
                'type' => $wdmScheduleArray['type'],
                'startTimer'    => $wdmScheduleArray["startTimer"]=='true',
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
            $hideUnavailable=$wdmScheduleArray['hideUnavailable']?"yes":"no";
            update_post_meta($productId, 'wdm_start_date', $wdmScheduleArray['startDate']);
            update_post_meta($productId, 'wdm_start_time', $wdmScheduleArray['startTime']);
            update_post_meta($productId, 'wdm_end_date', $wdmScheduleArray['endDate']);
            update_post_meta($productId, 'wdm_end_time', $wdmScheduleArray['endTime']);
            update_post_meta($productId, '_hide_if_unavailable', $hideUnavailable);
                
            self::updateScheduleRecord($wdmScheduleArray['startTime'], $wdmScheduleArray['endTime'], $productId, "");
                
            $settings = array(
                'type'          => $wdmScheduleArray['type'],
                'startTimer'    => $wdmScheduleArray["startTimer"]=='true',
                'endTimer'      => $wdmScheduleArray["endTimer"]=='true',
                'daysSelected'  => $wdmScheduleArray["daysSelected"],
                );

            if (!empty($wdmScheduleArray['skipStartDate'])) {
                $settings['skipStartDate']  =$wdmScheduleArray['skipStartDate'];
                $settings['skipEndDate']    =$wdmScheduleArray['skipEndDate'];
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
            delete_post_meta($productId, 'availability_flag');
            delete_post_meta($productId, 'wdm_show_product');
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


        public static function updateScheduleRecord($wdm_start_time, $wdm_end_time, $post_id, $type)
        {
            if (!empty($wdm_start_time) && !empty($wdm_end_time)) {
                $start_time  =  $wdm_start_time . ":00";
                $end_time    = $wdm_end_time . ":59";
        
                $str_start_time  = strtotime($start_time);
                $str_end_time    = strtotime($end_time);
                    
                //Checking whether Start Time is greater than End Time. If true, both of them are set the same value.
                if ($type == 'per_day') {
                    if ($str_start_time > $str_end_time) {
                        $wdm_end_time = $wdm_start_time;
                    }
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
             * This method is used to filter the selected weekdays by their occurance during
             * scheduled dates. i.e. days not occuring during schedule duration will be removed.
             * from the existing days array.
             * @param string $startDate - schedule start date
             * @param string $endDate - schedule End date.
             * @param array $daysArray - an array of the days selected by the admin.
             * @return mixed $daysArray.
             */
        public static function getSelectedDaysBetween($startDate, $endDate, $daysSelected)
        {
            $days       = array("1"=>"Monday", "2"=>"Tuesday", "3"=>"Wednesday", "4"=>"Thursday", "5"=>"Friday", "6"=>"Saturday", "7"=>"Sunday");
            $availableDays=array();
            if (!empty($daysSelected["Everyday"]) && $daysSelected["Everyday"]=="on") {
                return $daysSelected;
            }
            if (empty($daysSelected)) {
                $daysSelected["Everyday"]="on";
                return $daysSelected;
            }

            $startDate  =new \DateTime($startDate);
            $endDate    =new \DateTime($endDate);
            $startDate  =$startDate->getTimestamp();
            $endDate    =$endDate->getTimestamp();

            if (($endDate-$startDate)>=(24*60*60*7)) {
                return $daysSelected;
            }

            while ($startDate<=$endDate) {
                $availableDays[]=$days[date('N', $startDate)];
                $startDate+=(24*60*60);
            }

            foreach ($daysSelected as $day => $status) {
                if (!in_array($day, $availableDays)) {
                    unset($daysSelected[$day]);
                    unset($status);
                }
            }
            return $daysSelected;
        }
    }
}
