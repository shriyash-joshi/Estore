<?php

if (!function_exists('woo_schedule_update_term_details')) :
/*
* This update product category details
* @param $term_id  integer  Product category term id
* @param $start_date string   start date of schedule
* @param $end_date string end date of schedule
* @param $selected_days string serialized array/empty string of selected days
*
* @return $result integer/boolean returns no. of rows affected / false on failure
*/
    function woo_schedule_update_term_details($term_id, $scheduleData, $selection_type)
    {
        global $wpdb;
        $startDate=date('Y-m-d h:i:s A', strtotime($scheduleData['startDate']." ".$scheduleData['startTime']));
        $endDate=date('Y-m-d h:i:s A', strtotime($scheduleData['endDate']." ".$scheduleData['endTime']));
        $table = $wpdb->prefix . 'woo_schedule_category';
        $start_date =date("Y-m-d H:i:s", strtotime($startDate));
        $end_date =date("Y-m-d H:i:s", strtotime($endDate));
        $selectedDays = serialize($scheduleData['daysSelected']);

        $availabilityPairs=wdmWsScheduleNewOnTimeCrons($term_id, $selection_type, $scheduleData);
        $availabilityPairs=serialize($availabilityPairs);
        $result = '';
        if (woo_schedule_check_row_exists($term_id)) {
            $data = array(  'start_date'        => $start_date,
                            'end_date'          => $end_date,
                            'selected_days'     => $selectedDays,
                            'show_timer'        => true,
                            'last_updated'      => current_time('mysql'),
                            'hide_unavailable'  =>$scheduleData['hideUnavailable'],
                            'schedule_type'     =>$scheduleData['type'],
                            'availability_array'=>$availabilityPairs,
                            'start_timer'       =>$scheduleData['startTimer']=="true"?true:false,
                            'end_timer'         =>$scheduleData['endTimer']=="true"?true:false,
                            'start_skip_date'   =>date('Y-m-d', strtotime($scheduleData['skipStartDate'])),
                            'end_skip_date'     =>date('Y-m-d', strtotime($scheduleData['skipEndDate']))
                        );

            $where = array('term_id' => $term_id);
            $result = $wpdb->update($table, $data, $where);
        } else {
            $result = woo_schedule_insert_term_details($term_id, $start_date, $end_date, $selectedDays, $availabilityPairs, $scheduleData);
        }
        
        //scheduleOnTimeCrons((int)$term_id, $wdm_start_date, $wdm_end_date, $wdm_start_time, $wdm_end_time, $selected_days, 'category');
        return $result;
    }

endif;

if (!function_exists('woo_schedule_insert_term_details')) :
/*
* This inserts product category metas
* @param $term_id  integer  Product category term id
* @param $start_date string   start date of schedule
* @param $end_date string end date of schedule
* @param $selected_days string serialized array/empty string of selected days
*
* @return $result integer/boolean returns no. of rows affected / false on failure
*/

    function woo_schedule_insert_term_details($term_id, $start_date, $end_date, $selectedDays, $availabilityPairs, $scheduleData)
    {
        global $wpdb;

        $table = $wpdb->prefix . 'woo_schedule_category';

        $data = array(  'term_id' => $term_id,
                        'start_date' => $start_date,
                        'end_date' => $end_date,
                        'selected_days' => $selectedDays,
                        'show_timer' => true,
                        'hide_unavailable'  =>$scheduleData['hideUnavailable'],
                        'last_updated' => current_time('mysql'),
                        'schedule_type'     =>$scheduleData['type'],
                        'availability_array'=>$availabilityPairs,
                        'start_timer'       =>$scheduleData['startTimer'],
                        'end_timer'         =>$scheduleData['endTimer'],
                        'start_skip_date'    =>$scheduleData['skipStartDate'],
                        'end_skip_date'     =>$scheduleData['skipEndDate']
                    );

        $result = $wpdb->insert($table, $data);

        return $result;
    }

endif;

if (!function_exists('getDateTime')) :
    function getDateTime($Date, $wdmTime)
    {
        return $Date.' '.$wdmTime;
    }
endif;

if (!function_exists('setCategoryCronSchedule')) :
    function setCategoryCronSchedule($scheduleCronArray, $cronText, $term_id, $daysSelected)
    {
        if (empty($daysSelected)) {
            return;
        }

        if (!empty($scheduleCronArray)) {
            foreach ($scheduleCronArray as $cronTime) {
                $day = date('l', $cronTime);
                if (isset($daysSelected[$day]) && $daysSelected[$day] == 'on') {
                    update_post_meta($term_id, 'is_cron_set', true);
                    wp_schedule_single_event($cronTime, $cronText, array($term_id));
                }
            }
        }
    }
endif;

/**
 *
 *
 * @param [type] $startDate
 * @param [type] $endDate
 * @param [type] $wdmStartTimeArray
 * @param [type] $wdmEndTimeArray
 * @return void
 */
if (!function_exists('getDatesToSetCron')) :
    function getDatesToSetCron($startDate, $endDate, $wdmStartTimeArray, $wdmEndTimeArray)
    {
        $scheduleCronDates = array();
        $scheduleCronStartDates = array();
        $scheduleCronEndDates = array();

        $startDateTime = getDateTime($startDate, $wdmStartTimeArray);
        $endDateTime = getDateTime($endDate, $wdmEndTimeArray);

        $dateDiff = strtotime($endDateTime) - strtotime($startDateTime);
        $dayDiff = floor($dateDiff / (60 * 60 * 24));

        $tempStartDate = $startDateTime;
        $tempEndDate = getDateTime($startDate, $wdmEndTimeArray);
        $startObject = new \DateTime($tempStartDate);
        $endObject = new \DateTime($tempEndDate);
        $scheduleCronStartDates[0] = strtotime(get_gmt_from_date($startObject->format('Y-m-d H:i:s')).' GMT');
        $scheduleCronEndDates[0] = strtotime(get_gmt_from_date($endObject->format('Y-m-d H:i:s')).' GMT');

        if ($dayDiff > 0) {
            for ($i = 1; $i <= $dayDiff; $i++) {
                $tempStartDate = date('Y-m-d h:i:s A', strtotime($tempStartDate .' +1 day'));
                $wpStartDate = new \DateTime($tempStartDate);
                $wpStartDate = strtotime(get_gmt_from_date($wpStartDate->format('Y-m-d H:i:s')) . ' GMT');
                $scheduleCronStartDates[$i] = $wpStartDate;
            }

            for ($i=1; $i <= $dayDiff; $i++) {
                $tempEndDate = date('Y-m-d h:i:s A', strtotime($tempEndDate .' +1 day'));
                $wpEndDate = new \DateTime($tempEndDate);
                $wpEndDate = strtotime(get_gmt_from_date($wpEndDate->format('Y-m-d H:i:s')) . ' GMT');
                $scheduleCronEndDates[$i] = $wpEndDate;
            }
        }

        $scheduleCronDates['scheduleCronStartDates'] = $scheduleCronStartDates;
        $scheduleCronDates['scheduleCronEndDates'] = $scheduleCronEndDates;

        return $scheduleCronDates;
    }
endif;

if (!function_exists('woo_schedule_delete_term_details')) :
/*
* This delete all product category metas
* @param $term_id  integer/array  Product category term id
*
* @return $result integer/boolean returns no. of rows affected / false on failure
*/

    function woo_schedule_delete_term_details($term_id)
    {
        global $wpdb;

        if (empty($term_id)) {
            return false;
        }

        $table = $wpdb->prefix . 'woo_schedule_category';

        if (is_array($term_id)) {
            $term_id = implode(',', $term_id);
        }

        $where = array('term_id' => $term_id);

        return $wpdb->delete($table, $where);
    }

endif;

if (!function_exists('woo_schedule_check_row_exists')) :
/*
* This function checks whether $meta_key exists or not
*
* @param $term_id  integer  Product category term id
*
* @return boolean
*/
    function woo_schedule_check_row_exists($term_id)
    {
        global $wpdb;

        $table = $wpdb->prefix . 'woo_schedule_category';

        $query = "SELECT `term_id` FROM " . $table . " WHERE `term_id` = '{$term_id}'";

        $result = $wpdb->get_results($query);

        if (!empty($result)) {
            return true;
        } else {
            return false;
        }
    }

endif;

if (!function_exists('woo_schedule_check_category_availability')) :
/*
* It checks whether Product category has any schedule set
* and if setting is set then product available in that time period.
*
* @param $product_id integer Product ID
* @return boolean returns True - if product purchase is available otherwise false
*/
    function woo_schedule_check_category_availability($product_id)
    {
        global $wpdb;

        if (empty($product_id)) {
            return true;
        }

        $product_terms = wp_get_object_terms($product_id, 'product_cat', array('fields' => 'ids'));

        if (! empty($product_terms)) {
            if (! is_wp_error($product_terms)) {
                $currentDateTime        = strtotime(current_time('Y-m-d H:i:s'));
                //query to get the most rescent scheduled term from the scheduled product terms.
                $table=$wpdb->prefix.'woo_schedule_category';
                $availability_query     = "SELECT * FROM $table WHERE term_id IN (" . implode(',', $product_terms) . ") HAVING MAX(last_updated)";
                $termScheduleData       = $wpdb->get_results($availability_query, ARRAY_A);

                if (!empty($termScheduleData)) {
                    //one or more terms are scheduled
                    $availabilityPairs=maybe_unserialize($termScheduleData[0]['availability_array']);
                    return getTermAvailabilityFromAvailabilityPairs($currentDateTime, $availabilityPairs);
                } else {
                    // No term sceduled , therefore available by default
                    return true;
                }
            }
        }

        return true;
    }

endif;



if (!function_exists('wdm_check_category_availability')) :
    /*
    * It checks whether Product category has any schedule set
    * and if setting is set then product available in that time period.
    *
    * @param $product_id integer Product ID
    * @return boolean returns True - if product purchase is available otherwise false
    */
    function wdm_check_category_availability($catId)
    {
        global $wpdb;
        if (empty($catId)) {
            return true;
        }
    
            $currentDateTime        = strtotime(current_time('Y-m-d H:i:s'));
            //query to get the most rescent scheduled term from the scheduled product terms.
            $table=$wpdb->prefix.'woo_schedule_category';
            $availability_query     = "SELECT * FROM $table WHERE term_id=$catId HAVING MAX(last_updated)";
            $termScheduleData       = $wpdb->get_results($availability_query, ARRAY_A);
    
        if (!empty($termScheduleData)) {
            //one or more terms are scheduled
            $availabilityPairs=maybe_unserialize($termScheduleData[0]['availability_array']);
            return getTermAvailabilityFromAvailabilityPairs($currentDateTime, $availabilityPairs);
        } else {
            // No term sceduled , therefore available by default
            return true;
        }
    }
    
endif;


if (!function_exists('wdm_get_is_cat_hide_unavailable')) {

    function wdm_get_is_cat_hide_unavailable($catId)
    {
        global $wpdb;
        $table=$wpdb->prefix.'woo_schedule_category';
        $availability_query     = "SELECT * FROM $table WHERE term_id=$catId HAVING MAX(last_updated)";
        $termScheduleData       = $wpdb->get_results($availability_query, ARRAY_A);
        $hideUnavailable        = false;
        if (!empty($termScheduleData) && $termScheduleData[0]['hide_unavailable']!=null) {
            $hideUnavailable          = $termScheduleData[0]['hide_unavailable']=="true"?true:false;
        }
        return $hideUnavailable;
    }
}

if (!function_exists('getTermAvailabilityFromAvailabilityPairs')) {
    /**
     * Finds out wether current date-time lies between any availability-unavailability date-time pair.
     * (Determine current availability of the object)
     *
     * @param int $currentDateTime
     * @param array $availabilityPairs
     * @return bool
     */
    function getTermAvailabilityFromAvailabilityPairs($currentDateTime, $availabilityPairs)
    {
        if (!empty($availabilityPairs['makeAvailable'])) {
            $pairCount          = sizeof($availabilityPairs['makeAvailable']);
            
            for ($i=0; $i<$pairCount; $i++) {
                if ($currentDateTime>=$availabilityPairs['makeAvailable'][$i] && $currentDateTime<$availabilityPairs['makeUnAvailable'][$i]) {
                    return true;
                }
            }
        }
        return false;
    }
}




if (!function_exists('setProductWiseCron')) {
    function setProductWiseCron($curr_prod, $post_id, $type)
    {
        if ('product' == $type) {
            $startDate      = getDateTimeFromPOST('wdm_start_date', $post_id);
            $endDate        = getDateTimeFromPOST('wdm_end_date', $post_id);
            $startTime      = getDateTimeFromPOST('wdm_start_time', $post_id);
            $endTime        = getDateTimeFromPOST('wdm_end_time', $post_id);
            $daysSelected   = isset($_POST['days_of_week']) && isset($_POST['days_of_week'][$post_id]) && !empty($_POST['days_of_week'][$post_id]) ? $_POST['days_of_week'][$post_id] : array();
            $scheduleData   = wdmGetPostScheduleDataToSave($post_id);
            $scheduleData   = wdmValidateSchedule($scheduleData);
        } else {
            // start date
            $startDate = \Includes\WdmwsNotifyCron::getDate($post_id, 'wdm_start_date');
            // end date
            $endDate   = \Includes\WdmwsNotifyCron::getDate($post_id, 'wdm_end_date');

            // start time
            $startTime = \Includes\WdmwsNotifyCron::getTime($post_id, 'wdm_start_time_hr', 'wdm_start_time_min');
            // end time
            $endTime   = \Includes\WdmwsNotifyCron::getTime($post_id, 'wdm_end_time_hr', 'wdm_end_time_min');

            // days selected
            $daysSelected = get_post_meta($post_id, 'wdm_days_selected', true);
        }

        if ($curr_prod->get_type() == 'variable') {
            $childrens = getVariationIdsWhenUnavailable($curr_prod->get_id());
            if (empty($childrens) || !is_array($childrens)) {
                return;
            }
            foreach ($childrens as $variation_id) {
                $scheduleData   = wdmGetVariantScheduleDataToSave($variation_id);
                $scheduleData   = wdmValidateSchedule($scheduleData);
                if (!empty($scheduleData)) {
                    wdmWsScheduleNewOnTimeCrons($variation_id, $type, $scheduleData);
                }
            }
        } elseif (in_array($curr_prod->get_type(), array('course', 'simple', 'variation'))) {
            if (!empty($scheduleData)) {
                wdmWsScheduleNewOnTimeCrons($post_id, $type, $scheduleData);
                return;
            }
        }
    }
}

if (!function_exists('wdmWsScheduleNewOnTimeCrons')) {
    function wdmWsScheduleNewOnTimeCrons($selectedId, $cronType, $scheduleData)
    {
        wp_clear_scheduled_hook('update_'.$cronType.'_status_start', array($selectedId));
        wp_clear_scheduled_hook('update_'.$cronType.'_status_end', array($selectedId));
        switch ($scheduleData['type']) {
            case 'productLaunch':
                $startDateTime = SchedulerAdmin::getDateTime($scheduleData['startDate'], $scheduleData['startTime'], $cronType);
                $startDate = new \DateTime($startDateTime);
                $gmtDateTime=strtotime(get_gmt_from_date($startDate->format('Y-m-d H:i:s')) . ' GMT');
                wp_schedule_single_event($gmtDateTime, 'update_'.$cronType.'_status_start', array($selectedId));
                update_post_meta($selectedId, 'is_cron_set', true);
                do_action('wdmws_notify_crons_after_scheduled', $selectedId, array($startDate->getTimestamp()), 'productLaunch', $cronType);
                return array();
            case 'wholeDay':
                return wdmWsScheduleWholeDayCrons($selectedId, $scheduleData, $cronType);
            case 'specificTime':
                return wdmWsScheduleSpecificTimeCrons($selectedId, $scheduleData, $cronType);
            default:
                break;
        }
    }
}

if (!function_exists('wdmWsScheduleSpecificTimeCrons')) {
    /**
     * This function creates cron jobs for seting "specific time on selected days."
     *  - check for the selected duration
     *  - removes the dates which were skipped by selecting skipStart & skipEnd Dates
     *  - filter the days according to selected weekdays.
     *  - for each remaining day start and end schedule is created such as
     *  the product will be available on selceted day during startTime to the endTime selected by an admin.
     * @param int $selectedId - Product Id
     * @param array $scheduleData all the data required for scheduling
     * @param string $cronType - Objectfor which this cron is getting created (product/category)
     */
    function wdmWsScheduleSpecificTimeCrons($selectedId, $scheduleData, $cronType)
    {
        $startDate          = $scheduleData['startDate'];
        $endDate            = $scheduleData['endDate'];
        $startTime          = $scheduleData['startTime'];
        $endTime            = $scheduleData['endTime'];
        $daysSelected       = $scheduleData['daysSelected'];
        $skipStartDate      = $scheduleData['skipStartDate'];
        $skipEndDate        = $scheduleData['skipEndDate'];
        $scheduleDates      = array();
        $scheduleDates      = wdmWsGetDatesArray($startDate, $endDate, "includingTerminals");
        $skipDates          = wdmWsGetDatesArray($skipStartDate, $skipEndDate, "includingTerminals");
        $scheduleDates      = array_diff($scheduleDates, $skipDates);
        $availabilityPairs  = array();
        if (sizeof($daysSelected)<7) {
            $scheduleDates  = wdmWsFilterDatesByDays($scheduleDates, $daysSelected);
        }

        foreach ($scheduleDates as $date) {
            $date = date("m/d/Y", $date);
            $startDateTime = SchedulerAdmin::getDateTime($date, $startTime, $cronType);
            $endDateTime = SchedulerAdmin::getDateTime($date, $endTime, $cronType);
            $startDateTime = new \DateTime($startDateTime); /// get wordpress timezone
            $endDateTime = new \DateTime($endDateTime);
            $availabilityPairs['makeAvailable'][]   = $startDateTime->getTimestamp();
            $availabilityPairs['makeUnAvailable'][] = $endDateTime->getTimestamp();
            wp_schedule_single_event(strtotime(get_gmt_from_date($startDateTime->format("Y-m-d H:i:s"))." GMT"), 'update_'.$cronType.'_status_start', array($selectedId));
            wp_schedule_single_event(strtotime(get_gmt_from_date($endDateTime->format("Y-m-d H:i:s"))." GMT"), 'update_'.$cronType.'_status_end', array($selectedId));
        }
        //schedule notification crons for products or categories if notification feature enabled.
        do_action('wdmws_notify_crons_after_scheduled', $selectedId, $availabilityPairs['makeAvailable'], 'specificTime', $cronType);
        if ($cronType=="product") {
            update_post_meta($selectedId, 'is_cron_set', true);
            update_post_meta($selectedId, 'availability_pairs', $availabilityPairs);
        } else {
            return $availabilityPairs;
        }
    }
}


if (!function_exists('scheduleOnTimeCrons')) {
    function scheduleOnTimeCrons($selectedId, $startDate, $endDate, $wdmStartTimeArray, $wdmEndTimeArray, $daysSelected, $type)
    {
        
        if ('product' == $type || 'category' == $type) {
            wp_clear_scheduled_hook('update_'.$type.'_status_start', array($selectedId));
            wp_clear_scheduled_hook('update_'.$type.'_status_end', array($selectedId));
        }

        if (empty($startDate) || empty($endDate) || empty($wdmStartTimeArray) || empty($wdmEndTimeArray)) {
            return;
        }

        $wdmwsSettings = \Includes\AdminSettings\WdmWsSettings::getSettings();
       
        $processToSetCron = apply_filters('wdmws_process_to_set_cron', true, $selectedId, $startDate, $endDate, $wdmStartTimeArray, $wdmEndTimeArray, $daysSelected, $type);
        if (!$processToSetCron) {
            return;
        }

        $product_exp_type = isset($wdmwsSettings) && isset($wdmwsSettings['wdmws_custom_product_expiration_type'][0]) ? $wdmwsSettings['wdmws_custom_product_expiration_type'][0] : "per_day";

        if ($product_exp_type == 'per_day') {
            $scheduleCronDates = getDatesToSetCron($startDate, $endDate, $wdmStartTimeArray, $wdmEndTimeArray);
            if (!empty($scheduleCronDates)) {
                SchedulerAdmin::setCronSchedule($scheduleCronDates['scheduleCronStartDates'], 'update_'.$type.'_status_start', $selectedId, $daysSelected);
                SchedulerAdmin::setCronSchedule($scheduleCronDates['scheduleCronEndDates'], 'update_'.$type.'_status_end', $selectedId, $daysSelected);
            }
        } else {
            $startDateTime = SchedulerAdmin::getDateTime($startDate, $wdmStartTimeArray, $type);
            $endDateTime = SchedulerAdmin::getDateTime($endDate, $wdmEndTimeArray, $type);

            $startDate = new \DateTime($startDateTime); /// get wordpress timezone
            $endDate = new \DateTime($endDateTime); /// get wordpress timezone

            update_post_meta($selectedId, 'is_cron_set', true);
            wp_schedule_single_event(strtotime(get_gmt_from_date($startDate->format('Y-m-d H:i:s')) . ' GMT'), 'update_'.$type.'_status_start', array($selectedId));///
            wp_schedule_single_event(strtotime(get_gmt_from_date($endDate->format('Y-m-d H:i:s')) . ' GMT'), 'update_'.$type.'_status_end', array($selectedId));
        }
    }
}

if (!function_exists('wdmWsScheduleWholeDayCrons')) {
    /**
 * Setup crons for type whole time on selected days
 *
 * @param [type] $selectedId
 * @param [type] $scheduleData
 * @param [type] $daysSelected
 * @param [type] $type
 * @return void
 */
    function wdmWsScheduleWholeDayCrons($selectedId, $scheduleData, $cronType)
    {
        $startDateTime  = SchedulerAdmin::getDateTime($scheduleData['startDate'], $scheduleData['startTime'], $cronType);
        $startDateTime      = new \DateTime($startDateTime); /// get wordpress timezone
        $endDateTime    = SchedulerAdmin::getDateTime($scheduleData['endDate'], $scheduleData['endTime'], $cronType);
        $endDateTime        = new \DateTime($endDateTime); /// get wordpress timezone
        $startDateStamp=strtotime($scheduleData['startDate']);
        $endDateStamp=strtotime($scheduleData['endDate']);
        
        $startDate      = $scheduleData['startDate'];
        $endDate        = $scheduleData['endDate'];
        $daysSelected   = $scheduleData['daysSelected'];
        $skipStartDate  = $scheduleData['skipStartDate'];
        $skipEndDate    = $scheduleData['skipEndDate'];
        $scheduleDates  =   array();
        $scheduleDates  = wdmWsGetDatesArray($startDate, $endDate, "includingTerminals");
        $daysInBetween  = $scheduleDates;
        $skipDates      = wdmWsGetDatesArray($skipStartDate, $skipEndDate, "includingTerminals");
        $scheduleDates  = array_diff($scheduleDates, $skipDates);
        $availabilityPairs=array();
        if (sizeof($daysSelected)<7) {
            $scheduleDates  = wdmWsFilterDatesByDays($scheduleDates, $daysSelected);
        } else {
            if (empty($skipDates)) {
                $availabilityPairs['makeAvailable'][0]   = strtotime($scheduleData['startDate']." ".$scheduleData['startTime']);
                $availabilityPairs['makeUnAvailable'][0] = strtotime($scheduleData['endDate']." ".$scheduleData['endTime']);
                wp_schedule_single_event(strtotime(get_gmt_from_date($startDateTime->format('Y-m-d H:i:s')) . ' GMT'), 'update_'.$cronType.'_status_start', array($selectedId));
                wp_schedule_single_event(strtotime(get_gmt_from_date($endDateTime->format('Y-m-d H:i:s')) . ' GMT'), 'update_'.$cronType.'_status_end', array($selectedId));
                update_post_meta($selectedId, 'is_cron_set', true);
                update_post_meta($selectedId, 'availability_pairs', $availabilityPairs);
                return;
            }
        }

        $status="ended";
        if (in_array($startDateStamp, $scheduleDates)) {
            $availabilityPairs['makeAvailable'][]   = strtotime($scheduleData['startDate']." ".$scheduleData['startTime']);
            wp_schedule_single_event(strtotime(get_gmt_from_date($startDateTime->format('Y-m-d H:i:s')) . ' GMT'), 'update_'.$cronType.'_status_start', array($selectedId));
            $status = "started";
        }
        foreach ($daysInBetween as $dayToCheck) {
            if (!in_array($dayToCheck, $scheduleDates)) {
                if ($status=="started") {
                    $availabilityPairs['makeUnAvailable'][] = $dayToCheck;
                    $dayToCheck=date("m/d/Y", $dayToCheck);
                    $dayToCheck=new \DateTime($dayToCheck);
                    wp_schedule_single_event(strtotime(get_gmt_from_date($dayToCheck->format("Y-m-d H:i:s"))." GMT"), 'update_'.$cronType.'_status_end', array($selectedId));
                    $status = "ended";
                }
            } else {
                if ($status=="ended") {
                    $availabilityPairs['makeAvailable'][]   =$dayToCheck;
                    $dayToCheck                             =date("m/d/Y", $dayToCheck);
                    $dayToCheck                             =new \DateTime($dayToCheck);
                    wp_schedule_single_event(strtotime(get_gmt_from_date($dayToCheck->format("Y-m-d H:i:s"))." GMT"), 'update_'.$cronType.'_status_start', array($selectedId));
                    $status="started";
                }
            }
        }

        if (in_array($endDateStamp, $scheduleDates)) {
            if ($status=="ended") {
                $availabilityPairs['makeAvailable'][]   =strtotime($endDate);
                $endDateStart                           = new \DateTime($endDate);
                wp_schedule_single_event(strtotime(get_gmt_from_date($endDateStart->format('Y-m-d H:i:s')) . ' GMT'), 'update_'.$cronType.'_status_start', array($selectedId));
            }
            wp_schedule_single_event(strtotime(get_gmt_from_date($endDateTime->format('Y-m-d H:i:s')) . ' GMT'), 'update_'.$cronType.'_status_end', array($selectedId));
            $availabilityPairs['makeUnAvailable'][] = strtotime($scheduleData['endDate']." ".$scheduleData['endTime']);
        }
                
        //schedule notification crons for products or categories if notification feature enabled .
        do_action('wdmws_notify_crons_after_scheduled', $selectedId, $availabilityPairs['makeAvailable'], 'wholeDay', $cronType);
        if ($cronType=='product') {
            update_post_meta($selectedId, 'is_cron_set', true);
            update_post_meta($selectedId, 'availability_pairs', $availabilityPairs);
        } else {
            //product category
            return $availabilityPairs;
        }
    }
}


if (!function_exists('wdmWsFilterDatesByDays')) {
    /**
     * This function returns available dates which falls under selected weekdays
     *
     * @param array $availableDates -an array of dates in terms of the timestamps
     * @param array $selectedDays   -an array of days in terms of "Dayname"=>"Status"
     * @return array $finalAvailableDates
     */
    function wdmWsFilterDatesByDays($availableDates, $selectedDays)
    {
        $finalAvailableDates=array();
        foreach ($availableDates as $availableDate) {
            if (array_key_exists(date("l", $availableDate), $selectedDays)) {
                $finalAvailableDates[]=$availableDate;
            }
        }
        return $finalAvailableDates;
    }
}


if (!function_exists('wdmWsGetDatesArray')) {
/**
 * Gives timestamp for all the days between duration selected.
 * accroding to type selected it gives an array of days with or without
 * first & last dates (terminals)
 * @param string $startDate
 * @param string $endDate
 * @param string $type "excludingTerminals/includingTerminals"
 * @return array $datesArray
 */
    function wdmWsGetDatesArray($startDate, $endDate, $type)
    {
        $datesArray = array();
        $day        = (24*60*60);
        $startDate  = strtotime($startDate);
        $endDate    = strtotime($endDate);

        if ($type=="excludingTerminals") {
            $startDate  += $day;
            $endDate    -=$day;
        }
        while ($startDate <= $endDate) {
            $datesArray[]=$startDate;
            $startDate+=$day;
        }
        return $datesArray;
    }
}

/**
 * Returns the Base dir of the WooCommerce plugin without trailing slash.
 * @return string Woocommerce directory
 */
function wdmwsWcPluginDir()
{
    return untrailingslashit(plugin_dir_path(dirname(dirname(__FILE__))).'woocommerce');
}

/**
 * It returns the 'wdm_start_date', 'wdm_end_date', 'wdm_start_time' or
 * 'wdm_end_time' from the $_POST variable when product is updated on
 * from the product edit page.
 *
 * @param   $key    Possible values are:
 *                  wdm_start_date
 *                  wdm_end_date
 *                  wdm_start_time
 *                  wdm_end_time
 * @param   $postId The post id.
 *
 * @return  string  Returns the start date, end date, start time or end time
 *                  (depending on the argument passed in $key) from $_POST
 *                  variable.
 */
function getDateTimeFromPOST($key, $postId)
{
    return isset($_POST[$key][$postId]) ? $_POST[$key][$postId] : '';
}

function wdmWooSchedulerPath()
{
    // gets the absolute path to this plugin directory
    return untrailingslashit(plugin_dir_path(dirname(__FILE__)));
}

function showExpirationMsg($product_id, $wdmwsSettings)
{
    $expirationMsg = isset($wdmwsSettings) && isset($wdmwsSettings['wdmws_custom_product_expiration']) ? $wdmwsSettings['wdmws_custom_product_expiration'] : "" ;

    if ($expirationMsg != "") {
        return "<p class='wdm_message'>" . apply_filters('wdm_expiration_message', $expirationMsg, $product_id) . "</p>";
    } elseif (current_user_can('manage_options')) {
        return "<p class='wdm_message'>" . __('You can set a custom message in Scheduler for Woocommerce->Settings->Global->Single Product Expiration Message', WDM_WOO_SCHED_TXT_DOMAIN) . "</p>";
    }
}

function getTime($hour, $min, $type)
{
    $time = "";
    if (!empty($hour) && !empty($min)) {
        $time = $hour.':'.$min;
    } else {
        if ($type == 'start') {
            $time = '00:00';
        } elseif ($type == 'end') {
            $time = '23:59';
        }
    }

    return $time;
}

function getConcatenatedDateTime($date, $time)
{
    if (!empty($date) && !empty($time)) {
        $date .= " " . $time;
    }
    return $date;
}

function wdmwsShowStartTimer($wdm_start_date, $scheduleType, $wdmwsSettings)
{
    if (empty($wdm_start_date)) {
        return;
    }

    $css = "";
    $wdmwsStartTimerText = isset($wdmwsSettings) && isset($wdmwsSettings['wdmws_start_timer_text']) ? $wdmwsSettings['wdmws_start_timer_text'] : __("Available In:", WDM_WOO_SCHED_TXT_DOMAIN) ;
    if ($scheduleType=='productLaunch') {
        $wdmwsStartTimerText = isset($wdmwsSettings) && isset($wdmwsSettings['wdmws_launch_start_timer_text']) ? $wdmwsSettings['wdmws_launch_start_timer_text'] : __("Wil Be Launched In:", WDM_WOO_SCHED_TXT_DOMAIN) ;
    }
    echo "<div class = 'wdmws_timer_circles' id = 'display_start_timer' style = '".$css."'>";
    echo "<p>{$wdmwsStartTimerText}</p>";
        $seconds_to_go = strtotime($wdm_start_date) - current_time('timestamp');
        $timerText = "data-timer='{$seconds_to_go}'";
        echo "<div id = 'wdmws_start_timer' {$timerText}></div>";
        echo "</div>";
}


function wdmWsGetTimerSettings($productId, $wdmwsSettings, $wdmwsProductSettings, $TimerType)
{
    $wdmwsShowTimer = isset($wdmwsSettings) && isset($wdmwsSettings['wdmws_display_timer']) ? $wdmwsSettings['wdmws_display_timer'] : '' ;
    $wdmwsShowEndTimer = isset($wdmwsSettings) && isset($wdmwsSettings['wdmws_display_end_timer']) ? $wdmwsSettings['wdmws_display_end_timer'] : '' ;
    $wdmwsShowStartTimer = isset($wdmwsSettings) && isset($wdmwsSettings['wdmws_display_start_timer']) ? $wdmwsSettings['wdmws_display_start_timer'] : '' ;

    if (empty($enableProductSpecificTimer)) {
        $enableProductSpecificTimer = get_post_meta($productId, 'wdm_show_timer', true);
    }
    if ($TimerType=="start") {
        if (!empty($wdmwsProductSettings)) {
            $startTimer=$wdmwsProductSettings['startTimer'];
            if ($startTimer) {
                $wdmwsShowTimer="enable";
                $wdmwsShowStartTimer="enable_start";
                $enableProductSpecificTimer="yes";
            } else {
                $wdmwsShowTimer="disable";
                $wdmwsShowStartTimer="disable_start";
                $enableProductSpecificTimer="no";
            }
        }
        $timerValues = array('wdmwsShowTimer' => $wdmwsShowTimer, 'wdmwsShowStartTimer'=>$wdmwsShowStartTimer, 'enableProductSpecificTimer'=>$enableProductSpecificTimer);
    } else {
        if (!empty($wdmwsProductSettings)) {
            $endTimer = !empty($wdmwsProductSettings['endTimer'])?$wdmwsProductSettings['endTimer']:false;
            if ($endTimer) {
                $wdmwsShowTimer="enable";
                $wdmwsShowEndTimer="enable_end";
                $enableProductSpecificTimer="yes";
            } else {
                $wdmwsShowTimer="disable";
                $wdmwsShowEndTimer="disable_end";
                $enableProductSpecificTimer="no";
            }
        }
        $timerValues = array('wdmwsShowTimer' => $wdmwsShowTimer, 'wdmwsShowEndTimer'=>$wdmwsShowEndTimer, 'enableProductSpecificTimer'=>$enableProductSpecificTimer);
    }
    return $timerValues;
}

function wdmwsShowEndTimer($wdm_end_date, $wdmwsSettings)
{
    if (empty($wdm_end_date)) {
        return;
    }

    $css = "";
    $wdmwsEndTimerText = isset($wdmwsSettings) && isset($wdmwsSettings['wdmws_end_timer_text']) ? $wdmwsSettings['wdmws_end_timer_text'] : __("Available For:", WDM_WOO_SCHED_TXT_DOMAIN) ;
    echo "<div class = 'wdmws_timer_circles' id = 'display_end_timer' style = '".$css."'>";
    echo "<p>{$wdmwsEndTimerText}</p>";
    $seconds_to_go = strtotime($wdm_end_date) - current_time('timestamp');
    $timerText =  "data-timer='{$seconds_to_go}'";
    echo "<div id = 'wdmws_end_timer' {$timerText}></div>";
    echo "</div>";
}

function wooSchedulerProductId($productObject)
{
    if (is_callable(array($productObject, 'get_id'))) {
        return $productObject->get_id();
    }
    return $productObject->id ;
}

function wooSchedulerDisableAddToCart()
{
    return false;
}

function getVariableAvailability($product)
{
    $childrens = $product->get_children();

    if (!is_array($childrens)) {
        return;
    }

    $childrens_flag = loopVariationsForAvailability($childrens);

    if (empty($childrens_flag)) {
        return false;
    } else {
        return true;
    }
}

function loopVariationsForAvailability($childrens)
{
    $childrens_flag = array();
    foreach ($childrens as $key => $value) {
        unset($key);
        if (wdmCheckDateValidation($value)) {
            $childrens_flag[] = $value;
        }
    }

    return $childrens_flag;
}

function getVariationIdsWhenUnavailable($parent_id)
{
    $per_page       = -1;
    $page           = 1;
    $variations     = wc_get_products(array(
        'status'         => array( 'private', 'publish' ),
        'type'           => 'variation',
        'parent'         => $parent_id,
        'limit'          => $per_page,
        'page'           => $page,
        'orderby'        => array(
            'menu_order' => 'ASC',
            'ID'         => 'DESC',
        ),
        'return'         => 'objects',
    ));

    $childrens = array();

    if ($variations) {
        foreach ($variations as $variation_object) {
            $childrens[]   = $variation_object->get_id();
        }
    }

    if (!empty($childrens)) {
        return $childrens;
    }
}

function getVariationData($parent_id)
{
    $product = wc_get_product($parent_id);
    $childrens = $product->get_children();
    $wdmwsSettings = \Includes\AdminSettings\WdmWsSettings::getSettings();
    $variation_data = array();

    if (!is_array($childrens) || empty($childrens)) {
        return;
    }

    foreach ($childrens as $variation_id) {
        $variation_data = loopVariationforData($variation_id, $parent_id, $variation_data, $wdmwsSettings);
    }

    return $variation_data;
}

function loopVariationforData($variation_id, $parent_id, $variation_data, $wdmwsSettings)
{
    $availability = wdmCheckDateValidation($variation_id);
    $startTimer=false;
    $endTimer=false;
    $curr_time = current_time('H:i:s');
    $curr_date = date('m/d/Y');
    $wdm_start_date = get_post_meta($variation_id, 'wdm_start_date', true);
    $wdm_end_date = get_post_meta($variation_id, 'wdm_end_date', true);
    $wdm_start_time_hr = get_post_meta($variation_id, 'wdm_start_time_hr', true);
    $wdm_start_time_min = get_post_meta($variation_id, 'wdm_start_time_min', true);
    $wdm_end_time_hr = get_post_meta($variation_id, 'wdm_end_time_hr', true);
    $wdm_end_time_min = get_post_meta($variation_id, 'wdm_end_time_min', true);
    $wdm_start_time = getTime($wdm_start_time_hr, $wdm_start_time_min, 'start');
    $wdm_end_time = getTime($wdm_end_time_hr, $wdm_end_time_min, 'end');
    $enableProductSpecificTimer = get_post_meta($variation_id, 'wdm_show_timer', true);
    $selectedDays = get_post_meta($variation_id, 'wdm_days_selected', true);

    $productScheduleSettings=get_post_meta($variation_id, 'wdm_schedule_settings', true);
    $catDates = getCategoryDates($parent_id);
    $scheduleType="";
    $availabilityPairs=array();
    $availabilityPairs=get_post_meta($variation_id, 'availability_pairs', true);

    if (!empty($productScheduleSettings)) {
        $scheduleType=$productScheduleSettings['type'];
        $startTimer=$productScheduleSettings['startTimer'];
        $endTimer=$productScheduleSettings['endTimer'];
    }

    if (empty($wdm_start_date) && !empty($catDates)) {
        $availability       = woo_schedule_check_category_availability($parent_id);
        $start_date         = $catDates[0]->start_date;
        $start_date         = date('Y-m-d h:i:s A', strtotime($start_date));
        $end_date           = $catDates[0]->end_date;
        $end_date           = date('Y-m-d h:i:s A', strtotime($end_date));
        $selectedDays       = maybe_unserialize($catDates[0]->selected_days);
        $enableProductSpecificTimer = $catDates[0]->show_timer;
        $scheduleType       =$catDates[0]->schedule_type;
        $splitStartDate     = explode(" ", $start_date);
        $splitEndDate       = explode(" ", $end_date);
        $wdm_start_date     = $splitStartDate[0];
        $wdm_start_time     = $splitStartDate[1]. " ". $splitStartDate[2];
        $wdm_end_date       = $splitEndDate[0];
        $wdm_end_time       = $splitEndDate[1]. " ". $splitEndDate[2];
        $availabilityPairs  = maybe_unserialize($catDates[0]->availability_array);
        $startTimer         =$catDates[0]->start_timer;
        $endTimer           =$catDates[0]->end_timer;
    }

    if (empty($wdm_start_date) && !woo_schedule_check_category_availability($parent_id)) {
        return;
    }

    $variation_data[$variation_id]['scheduleType']              = $scheduleType;
    $variation_data[$variation_id]['availability']              = $availability;
    $variation_data[$variation_id]['availabilityPairs']              = json_encode($availabilityPairs);
    $variation_data[$variation_id]['selectedDays']              = $selectedDays;
    $variation_data[$variation_id]['isToBeScheduledCondition1']       = strtotime($curr_date) == strtotime($wdm_start_date);
    $variation_data[$variation_id]['isToBeScheduledCondition2']       = strtotime($curr_date) > strtotime($wdm_start_date) && strtotime($curr_date) <= strtotime($wdm_end_date);
    $variation_data[$variation_id]['isToBeScheduledCondition3']       = strtotime($curr_date) < strtotime($wdm_start_date);
    $variation_data[$variation_id]['showCurrentTimer1']       = strtotime($curr_date) < strtotime($wdm_end_date) && strtotime($curr_time) > strtotime($wdm_start_time);
    $variation_data[$variation_id]['showCurrentTimer2']       = strtotime($curr_time) < strtotime($wdm_start_time);
    $variation_data[$variation_id]['showAfterTimer1']       = strtotime($curr_time) > strtotime($wdm_start_time) && strtotime($curr_time) > strtotime($wdm_end_time);
    $variation_data[$variation_id]['showAfterTimer2']       = strtotime($curr_time) < strtotime($wdm_start_time);
    $variation_data[$variation_id]['scheduleWillEnd1']       = strtotime($curr_time) < strtotime($wdm_end_time);
    $variation_data[$variation_id]['startTime']          = getGMTTime(getConcatenatedDateTime($wdm_start_date, $wdm_start_time));
    $variation_data[$variation_id]['wdmBeginDate']          = getGMTTime(getConcatenatedDateTime($wdm_start_date, $wdm_start_time));
    $variation_data[$variation_id]['tillTime']      = getGMTTime(getConcatenatedDateTime($curr_date, $wdm_end_time), '+2 seconds');
    $variation_data[$variation_id]['nextDate']          = getNextScheduleDate($curr_date, $wdm_start_time, $wdm_end_date, $selectedDays);
    $variation_data[$variation_id]['wdmFinishDate']         = getGMTTime(getConcatenatedDateTime($wdm_end_date, $wdm_end_time), '+2 seconds');
    $variation_data[$variation_id]['wdmws_is_scheduled']    = !empty($productScheduleSettings) || !empty($catDates);
    $variation_data[$variation_id]['enableStartTimer']         = $startTimer;
    $variation_data[$variation_id]['enableEndTimer']         = $endTimer;
    unset($wdmwsSettings);
    return $variation_data;
}

function getNextScheduleDate($curr_date, $wdm_start_time, $wdm_end_date, $selectedDays)
{
    $nextDate = date('Y-m-d', strtotime($curr_date .' +1 day'));
    for (; strtotime($nextDate) <= strtotime($wdm_end_date);) {
        $nextDay = date('l', strtotime($nextDate));
        if (!isset($selectedDays[$nextDay])) {
            $nextDate = date('Y-m-d', strtotime($nextDate .' +1 day'));
            continue;
        } elseif ($selectedDays[$nextDay] == "on") {
            return getGMTTime(getConcatenatedDateTime($nextDate, $wdm_start_time));
        }
    }
}


/**
 * In case of whole day and specific duration schedule type this function returns
 * the next availability/unvavailability dateTime for the product.
 *
 * @param int $currentDateTime
 * @param string $availabilityType
 * @return void
 */
function wdmwsGetNextAvailabilityTime($productId, $availabilityType, $scheduleSettings, $availabilityPairs)
{
    $curr_time          = current_time('H:i:s');
    $curr_date          = current_time('m/d/Y');
    $currentDateTime    = strtotime($curr_date ." ".$curr_time);
            
    if (isset($scheduleSettings['type']) && $scheduleSettings['type']=="productLaunch") {
        $startDate      = get_post_meta($productId, 'wdm_start_date', true);
        $startTimeHr    = get_post_meta($productId, 'wdm_start_time_hr', true);
        $startTimeMin   = get_post_meta($productId, 'wdm_start_time_min', true);
        $startTime      = $startTimeHr.":".$startTimeMin;
        $launchOn       = strtotime($startDate." ".$startTime." +2 seconds");
        return $launchOn;
    }

    if (isset($availabilityPairs)) {
        //$pairCount          = sizeof($availabilityPairs['makeAvailable']);
        if (!empty($availabilityPairs)) {
            if ($availabilityType=="unavailable") {
                foreach ($availabilityPairs['makeUnAvailable'] as $nextUnavailable) {
                    if ($currentDateTime < $nextUnavailable) {
                        return $nextUnavailable+2;
                    }
                }
            } else {
                foreach ($availabilityPairs['makeAvailable'] as $nextAvailable) {
                    if ($currentDateTime < $nextAvailable) {
                        return $nextAvailable+2;
                    }
                }
            }
        }
    }
    return ;
}

function isSelectedDay($day, $selectedDays)
{
    $wdmwsSettings = \Includes\AdminSettings\WdmWsSettings::getSettings();
    $type = isset($wdmwsSettings) && isset($wdmwsSettings['wdmws_custom_product_expiration_type'][0]) ? $wdmwsSettings['wdmws_custom_product_expiration_type'][0] : 'per_day' ;
    if ($type == 'entire_day') {
        return true;
    } elseif (!isset($selectedDays[$day]) || $selectedDays[$day] != "on") {
        return false;
    }

    return true;
}

function getAddedTime($date, $diff = "")
{
    return date('Y-m-d h:i:s A', strtotime($date.' '.$diff));
}


function getGMTTime($date, $diff = "")
{
    $date = getAddedTime($date, $diff);
    $nextDate = new \DateTime($date);
    $date = get_gmt_from_date($nextDate->format('Y-m-d H:i:s')) . ' GMT';
    $date=date_create($date);
    return date_format($date, "M d Y H:i:s e");
}

function addScheduleNotice($message, $notice_type)
{
    return wc_add_notice($message, $notice_type);
}


function showFinishTimer($product_id, $wdmwsProductSettings, $availabilityPairs, $categorySettings, $wdmwsSettings)
{
    if ((isset($wdmwsProductSettings['endTimer']) && $wdmwsProductSettings['endTimer']) || $categorySettings['endTimer']) {
        $nextUnavailable=wdmwsGetNextAvailabilityTime($product_id, 'unavailable', $wdmwsProductSettings, $availabilityPairs);
        $nextUnavailable= date("m/d/Y H:i:s", $nextUnavailable);
        wdmwsShowEndTimer($nextUnavailable, $wdmwsSettings);
    }
}

function showTimerWhenProductUnavailable($product_id, $wdmwsSettings, $curr_date, $wdm_end_date, $curr_time, $wdm_end_time, $wdmwsProductSettings, $availabilityPairs, $categorySettings)
{
    preventAddToCartForNoTimer($curr_date, $curr_time, $wdm_end_date, $wdm_end_time, $wdmwsProductSettings, $categorySettings);
    if ((isset($wdmwsProductSettings['startTimer']) && $wdmwsProductSettings['startTimer']) || $categorySettings['startTimer']) {
        $nextAvailable      = wdmwsGetNextAvailabilityTime($product_id, 'available', $wdmwsProductSettings, $availabilityPairs);
        if (!empty($nextAvailable)) {
            $nextAvailable      = date("m/d/Y H:i:s", $nextAvailable);
            $scheduleType=empty($wdmwsProductSettings)?$categorySettings['type']:$wdmwsProductSettings['type'];
            wdmwsShowStartTimer($nextAvailable, $scheduleType, $wdmwsSettings);
        }
    }
    echo showExpirationMsg($product_id, $wdmwsSettings);
}

/**
 * This method is used to mark the product unpurchasable(Hiding add to cart form from the prouct page)
 * when no start timer is selected also product is marked unpurchasable when it's out of schedule duration.
 * (End Date is passed.)
 *
 * @param [type] $curr_date
 * @param [type] $curr_time
 * @param [type] $wdm_end_date
 * @param [type] $wdm_end_time
 * @param [type] $startTimerEnabled
 * @return void
 */
function preventAddToCartForNoTimer($curr_date, $curr_time, $wdm_end_date, $wdm_end_time, $productScheduleSettings, $categoryTimerSettings)
{
    if (strtotime($curr_date) >= strtotime($wdm_end_date) && strtotime($curr_time) > strtotime($wdm_end_time)) {
        addDisableCartFilter();
    }
    if ((isset($productScheduleSettings['startTimer']) && !$productScheduleSettings['startTimer']) || !$categoryTimerSettings['startTimer']) {
        addDisableCartFilter();
    }
}

function addDisableCartFilter()
{
    add_filter('woocommerce_is_purchasable', 'wooSchedulerDisableAddToCart', 500);
}

function getCategoryDates($product_id)
{
    global $wpdb;
    $catRecords = array();
    $catTable = $wpdb->prefix . "woo_schedule_category";
    $product_terms = wp_get_object_terms($product_id, 'product_cat', array('fields' => 'ids'));
    $catIds = implode(",", $product_terms);
    if (!empty($catIds)) {
        $query = "SELECT * FROM $catTable WHERE term_id IN ($catIds) ORDER BY  `last_updated` DESC LIMIT 1";
        $catRecords = $wpdb->get_results($query);
    }
    
    return isset($catRecords) && !empty($catRecords) ? $catRecords : array();
}

/**
 * Search for products and return product ids.
 *
 * @param string $term       (default: '')
 * @param string $post_types (default: array('product'))
 *
 * @return Returns product ids.
 */
function returnProductIdsForSearchTerm($term, $post_types = array('product', 'product_variation'))
{
    global $wpdb;

    if (empty($term)) {
        $term = wc_clean(stripslashes($_GET['term']));
    } else {
        $term = wc_clean($term);
    }

    $like_term = '%'.$wpdb->esc_like($term).'%';

    $query = $wpdb->prepare("
        SELECT ID FROM {$wpdb->posts} posts LEFT JOIN {$wpdb->postmeta} postmeta ON posts.ID = postmeta.post_id
        WHERE posts.post_status = 'publish'
        AND (
            posts.post_title LIKE %s
            OR (
                postmeta.meta_key = '_sku' AND postmeta.meta_value LIKE %s
            )
        )
    ", $like_term, $like_term);

    $query .= " AND posts.post_type IN ('".implode("','", array_map('esc_sql', $post_types))."')";

    $posts = array_unique($wpdb->get_col($query));
    return $posts;
}

/**
 * Manipulation to show the notify me button on the product page.
 *
 * @param int   $product_id     Product ID.
 * @param array $wdmwsSettings  Scheduler settings.
 * @param bool  $includeForm    Whether to include 'form' tag.
 */
function wdmwsShowNotifyMeButton($product_id, $wdmwsSettings, $includeForm = true)
{
    // If notify me feature is not enabled.
    if (!isset($wdmwsSettings['wdmws_enable_notify']) || ! '1' == $wdmwsSettings['wdmws_enable_notify']) {
        return;
    }

    if (is_user_logged_in()) {
        wdmwsNotifyMeButtonHTML($product_id, $wdmwsSettings, 0, $includeForm);
        wdmwsEnqueueNotifyScriptStyle($wdmwsSettings);
    } else {
        if (isset($wdmwsSettings['wdmws_guest_user_enrl_method']) && 'field' == $wdmwsSettings['wdmws_guest_user_enrl_method']) {
            wdmwsNotifyMeButtonHTML($product_id, $wdmwsSettings, 1, $includeForm);
        } else {
            wdmwsNotifyMeButtonHTML($product_id, $wdmwsSettings, 2, $includeForm);
            add_action('wp_footer', 'wdmwsShowGuestUserEnrlModal');

            // enqueue bootstrap modal js
            wp_enqueue_script('wdmws_modal_js');
            // enqueue bootstrap modal css
            wp_enqueue_style('wdmws_modal_css');
        }
        wdmwsEnqueueNotifyScriptStyle($wdmwsSettings);
    }
}

/**
 * Render Notify Me button HTML.
 *
 * @param int   $product_id Product ID.
 * @param array $wdmwsSettings  Scheduler Settings
 * @param int   $enrlType       How the user is going to enroll for notification.
 *                              0 = Logged in User
 *                              1 = Guest User and Enrollment method set to 'field'
 *                              2 = Guest User and Enrollment method set to 'popup'
 */
function wdmwsNotifyMeButtonHTML($product_id, $wdmwsSettings, $enrlType, $includeForm)
{
    $notifyMeBtnText          = isset($wdmwsSettings['wdmws_notify_btn_txt']) && !empty($wdmwsSettings['wdmws_notify_btn_txt'])? $wdmwsSettings['wdmws_notify_btn_txt'] : __('Notify Me', WDM_WOO_SCHED_TXT_DOMAIN);
    $isGuestEnrollmentEnabled = isset($wdmwsSettings['wdmws_guest_user_enrl']) && '1' == $wdmwsSettings['wdmws_guest_user_enrl'] ? true : false;
    $modalAttrs               = '';
    $disableNotifyButton      = '';
    $alreadyEnrlMessage       = '';
    $messageLabelClass        = '';

    if (2 == $enrlType) {
        $modalAttrs = 'data-toggle="modal" data-target="#notify-me-guest-modal"';
    }

    if (true == $includeForm) {
        ?>
    <form class="wdm-notify-me-form" method="post">
        <?php
    } else {
        ?>
    <div class="wdm-notify-me-nonce">
        <?php
    }
    wp_nonce_field('wdmws_notify_me_enrl');
    ?>
    <?php
    // If guest user is allowed and enrolment type is field.
    if (1 == $enrlType && $isGuestEnrollmentEnabled) {
        $emailFieldPlaceHolder = __('Enter email address:', WDM_WOO_SCHED_TXT_DOMAIN);
        ?>
        <input type="text" id="wdmws-notify-me-email-field-<?php echo $product_id; ?>" class="wdmws-notify-me-email-field" placeholder="<?php echo $emailFieldPlaceHolder; ?>">
        <?php
    } elseif (0 == $enrlType) {
        $current_user = wp_get_current_user();

        // If currently logged in user is already enrolled for product notification.
        if ($current_user->exists() && wdmwsIsUserEnrolledForProdNotification($product_id, $current_user->user_email)) {
            $disableNotifyButton = 'disabled';
            // Already enrolled message
            $alreadyEnrlMessage  = apply_filters('wdmws_user_already_enrl_msg', __('You are already enrolled!', WDM_WOO_SCHED_TXT_DOMAIN), $wdmwsSettings);
            $messageLabelClass = 'wdmws-notify-success wdmws-show-msg-label';
        }
    }

    wdmwsRenderNotifyButtonLabel($product_id, $isGuestEnrollmentEnabled, $enrlType, $notifyMeBtnText, $modalAttrs, $disableNotifyButton, $messageLabelClass, $alreadyEnrlMessage);

    if (true == $includeForm) {
        ?>    
        </form>
        <?php
    } else {
        ?>
        </div>
        <?php
    }
}

/**
 * Render Notify button HTML and label.
 */
function wdmwsRenderNotifyButtonLabel($productId, $isGuestEnrollmentEnabled, $enrlType, $notifyMeBtnText, $modalAttrs, $disableNotifyButton, $messageLabelClass, $alreadyEnrlMessage)
{
    // If user is not loggedin and guest user enollment is disabled, redirect
    // the user to the login page when user clicks on Notify Me button.
    if (0 != $enrlType && !$isGuestEnrollmentEnabled) :
        ?>
        <button type="submit" class="button wdmws-notify-me-btn-common-class wdmws-notify-me-btn-req-login" id="wdmws-notify-me-btn-<?php echo $productId; ?>" name="wdmws-notify-me-btn-req-login" value="wdmws-notify-me-btn-req-login" data-product-id="<?php echo $productId; ?>" data-enrl-type="<?php echo $enrlType; ?>">
            <?php echo apply_filters('wdmws_notify_me_btn_text', $notifyMeBtnText, $productId); ?>
        </button>
        <?php
    else :
        ?>
        <button type="button" class="button wdmws-notify-me-btn-common-class wdmws-notify-me-btn" id="wdmws-notify-me-btn-<?php echo $productId; ?>" data-product-id="<?php echo $productId; ?>" data-enrl-type="<?php echo $enrlType; ?>" <?php echo $modalAttrs.' '.$disableNotifyButton; ?>>
            <?php echo apply_filters('wdmws_notify_me_btn_text', $notifyMeBtnText, $productId); ?>
        </button>
        <?php
    endif;

    if ((0 == $enrlType) || (1 == $enrlType && $isGuestEnrollmentEnabled)) {
        ?>
        <label class="wdmws-enrl-success-msg <?php echo $messageLabelClass; ?>"><?php echo $alreadyEnrlMessage; ?></label>
        <?php
    }
}

/**
 * Render Guest user modal so that guest user can enroll.
 */
function wdmwsShowGuestUserEnrlModal()
{
    global $product;
    $product_id = wooSchedulerProductId($product);

    $modalHeader = apply_filters('wdmws_guest_enrl_modal_header', sprintf(__('Get Notification for %s', WDM_WOO_SCHED_TXT_DOMAIN), $product->get_title()), $product_id);
    ?>
    <!-- The Guest User Enrollment Modal -->
    <div class="modal wdmws-notify-me-modal" id="notify-me-guest-modal" aria-labelledby="wdmws_guest_modal_label">
      <div class="modal-dialog">
        <div class="modal-content">
        
            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title" id="wdmws_guest_modal_label"><?php echo $modalHeader; ?></h4>
                <button type="button" class="close" data-dismiss="modal" style="display:inline-block;">&times;</button>
            </div>
          
            <!-- Modal body -->
            <div class="modal-body">
                <form class="wdm-notify-me-guest-form-modal">
                    <label for="wdmws-notify-me-guest-email-modal">
                        <?php echo __('Enter Email Address:', WDM_WOO_SCHED_TXT_DOMAIN);?>
                    </label>
                    <input type="text" id="wdmws-notify-me-guest-email-modal" class="wdmws-notify-me-guest-email-modal">
                    <button type="button" class="button wdmws-notify-me-guest-sbt-modal" id="wdmws-notify-me-guest-sbt-modal-<?php echo $product_id; ?>" data-product-id="<?php echo $product_id; ?>">
                    <?php echo apply_filters('wdmws_guest_user_enrl_sbt', __('Submit', WDM_WOO_SCHED_TXT_DOMAIN), $product_id); ?>
                    </button>
                    <label class="wdmws-enrl-success-msg-modal"></label>
                </form>
            </div>          
        </div>
      </div>
    </div>
    <?php
}

/**
 * Enqueue notify feature related scripts and styles on product page.
 *
 * @param array $wdmwsSettings Scheduler Settings.
 */
function wdmwsEnqueueNotifyScriptStyle($wdmwsSettings)
{
    // Enrollment success message
    $successMessage = isset($wdmwsSettings['wdmws_success_enrl_msg']) && !empty($wdmwsSettings['wdmws_success_enrl_msg']) ? $wdmwsSettings['wdmws_success_enrl_msg'] : __('Enrolled Successfully.', WDM_WOO_SCHED_TXT_DOMAIN);

    // Already enrolled message
    $alreadyEnrlMessage  = apply_filters('wdmws_user_already_enrl_msg', __('You are already enrolled!', WDM_WOO_SCHED_TXT_DOMAIN), $wdmwsSettings);

    // Email invalid message
    $emailInvalidMessage = apply_filters('wdmws_enrl_email_invalid_msg', __('Email is invalid.', WDM_WOO_SCHED_TXT_DOMAIN));

    // Email invalid message
    $emailRequiredMessage = apply_filters('wdmws_enrl_email_required_msg', __('Email is required.', WDM_WOO_SCHED_TXT_DOMAIN));

    // enqueue notify me script
    wp_enqueue_script('wdmws_notify_me_js');

    // enqueue notify me style
    wp_enqueue_style('wdmws_notify_me_css');

    // notify me localize script
    wp_localize_script(
        'wdmws_notify_me_js',
        'wdmws_notification_object',
        array(
            'ajax_url'             => admin_url('admin-ajax.php'),
            'enrl_succ_msg'        => $successMessage,
            'already_enrl_msg'     => $alreadyEnrlMessage,
            'email_invalid_msg'    => $emailInvalidMessage,
            'email_required_msg'   => $emailRequiredMessage
        )
    );

    // enqueue Custom CSS
    wmdwsEnqueueNotifyInlineCSS($wdmwsSettings, 'wdmws_notify_me_css');
}

/**
 * Unschedule the cron by the hook name.
 *
 * @param string $hook Cron hook name.
 */
function wdmwsUnscheduleCrons($hook)
{
    $crons = _get_cron_array();
    if (empty($crons)) {
        return;
    }
    foreach ($crons as $timestamp => $cron) {
        if (! empty($cron[$hook])) {
            unset($crons[$timestamp][$hook]);
        }

        if (empty($crons[$timestamp])) {
            unset($crons[$timestamp]);
        }
    }
    _set_cron_array($crons);
}

/**
 * Enqueue notify user feature custom css.
 */
function wmdwsEnqueueNotifyInlineCSS($wdmwsSettings, $handleName)
{
    $customCss = isset($wdmwsSettings['wdmws_notify_user_css']) ? $wdmwsSettings['wdmws_notify_user_css'] : '';

    if (!empty($customCss)) {
        wp_add_inline_style($handleName, $customCss);
    }
}

/**
 * Returns the table name where user enrolled list is stored.
 */
function wdmwsReturnEnrlUserListTable()
{
    global $wpdb;
    return $wpdb->prefix.'wdmws_enrl_users';
}

/**
 * Returns whether the user is enrolled for particular product notification.
 *
 * @param int       $productId Product Id.
 * @param string    $userEmail User Email address.
 *
 * @return bool     True if user is enrolled for product notification, false
 *                  otherwise.
 */
function wdmwsIsUserEnrolledForProdNotification($productId, $userEmail)
{
    global $wpdb;
    $table = wdmwsReturnEnrlUserListTable();
    $query = "SELECT id from ".$table." WHERE product_id=".$productId." AND user_email='".$userEmail."'";
    $primaryId = $wpdb->get_var($query);

    if (empty($primaryId)) {
        $isEnrolled = false;
    } else {
        $isEnrolled = true;
    }

    return apply_filters('wdmws_is_user_enrolled', $isEnrolled, $primaryId, $productId, $userEmail);
}

/**
 * Generates hash/ unsubscribe link using product id, user email, random number.
 *
 * @param int    $productId Product Id.
 * @param string $userEmail User Email ID.
 *
 * @return string Generates and returns Unsubscribe link.
 */
function wdmwsGenerateUnsubscriptionHash($productId, $userEmail)
{
    $unsubscriptionLink = wp_hash($productId.'_'.$userEmail.'_'.random_int(0, getrandmax()));

    return apply_filters('wdmws_unsubscription_link_generation', $unsubscriptionLink, $productId, $userEmail);
}

/**
 * Returns hash/ unsubscribe link from DB.
 *
 * @param int    $productId Product Id.
 * @param string $userEmail User Email ID.
 *
 * @return string Returns Unsubscribe link from DB.
 */
function returnUnsubscriptionHashFromDB($productId, $userEmail)
{
    global $wpdb;
    $enrollmentTable = wdmwsReturnEnrlUserListTable();
    $query = "SELECT unsubscription_link FROM ".$enrollmentTable." WHERE product_id=".$productId." AND user_email='".$userEmail."'";
    $unsubscriptionLink = $wpdb->get_var($query);

    return apply_filters('wdmws_unsubscription_link_from_db', $unsubscriptionLink, $productId, $userEmail);
}

function wdmwsGetUnsubscribeLink($wdmwsSettings, $productId, $userEmail)
{
    // Unsubscription Link processing
    $pageId = isset($wdmwsSettings['wdmws_unsubscription_page']) ? $wdmwsSettings['wdmws_unsubscription_page'] : '-1';

    if ('-1' == $pageId) {
        return '';
    } else {
        $pageLink = get_page_link($pageId);
    }

    $unsubscribeHash = returnUnsubscriptionHashFromDB($productId, $userEmail);
    return add_query_arg('wdmws_unsubscribe', $unsubscribeHash, $pageLink);
}

/**
 * This function is used to add short code on given page.
 *
 * @param [int]    $pageId    [page id]
 * @param [string] $shortcode [SHortcode to be added]
 */
function wdmwsAddShortcodeOnPage($pageId, $shortcode)
{
    //get content of the page
    $selectedPage = get_post($pageId);

    if ($selectedPage !== null) {
        if (wdmwsDoesContentHaveShortcode($selectedPage->post_content, $shortcode) === false) {
            // Update Selected Page
            $page_data = array(
                'ID' => $selectedPage->ID,
                'post_content' => $selectedPage->post_content."<br /> [$shortcode]",
            );

            // Update the page into the database
            wp_update_post($page_data);
        }
    }
}

/**
 * This function is used to remove short code on given page.
 *
 * @param [int]    $pageId    [page id]
 * @param [string] $shortcode [SHortcode to be added]
 */
function wdmwsRemoveShortcodeFromPage($pageId, $shortcode)
{
    //get content of the page
    $selectedPage = get_post($pageId);

    if ($selectedPage !== null) {
        // Update Selected Page
        $page_data = array(
            'ID' => $selectedPage->ID,
            'post_content' => str_replace("[$shortcode]", '', $selectedPage->post_content),
        );

        // Update the page into the database
        wp_update_post($page_data);
    }
}

/**
 * Checks if content has provided shortcode.
 *
 * @param string $content   Content in which shortcode is to be searched
 * @param string $shortcode Shortcode to search
 *
 * @return bool returns true if found, else returns false
 */
function wdmwsDoesContentHaveShortcode($content, $shortcode)
{
    if (false === strstr($content, "[$shortcode]")) {
        return false;
    }

    return true;
}


if (!function_exists('wdmValidateSchedule')) {
    //schedule validation
    function wdmValidateSchedule($wdmScheduleArray)
    {
        $validScheduleArray=array();
        switch ($wdmScheduleArray['type']) {
            case 'productLaunch':
                $validScheduleArray= wdmValidateLaunchSchedule($wdmScheduleArray);
                break;
            case 'wholeDay':
                $validScheduleArray= wdmValidateWholeDaySchedule($wdmScheduleArray);
                break;
            case 'specificTime':
                $validScheduleArray= wdmValidateSpecificTimeSchedule($wdmScheduleArray);
                break;
            default:
                return false;
        }
        return $validScheduleArray;
    }
}

if (!function_exists('wdmValidateLaunchSchedule')) {
    /**
     * Launch schedule should must have schedule start date and schedule start time
     * and the schedule date and time must be the future time. method validates &
     * returns true if schedule values are correct else return false
     */
    function wdmValidateLaunchSchedule($wdmScheduleArray)
    {
        if (empty($wdmScheduleArray['startTime']) || empty($wdmScheduleArray['startDate'])) {
            $errorMessage=__("Please specify a valid start date & time for product launch", WDM_WOO_SCHED_TXT_DOMAIN);
            return array("errorMessage"=>$errorMessage);
        }
        $scheduleTime  = $wdmScheduleArray['startDate']." ".$wdmScheduleArray['startTime'];
        $scheduleTime  = new DateTime($scheduleTime);
        $timeNow       = current_time('timestamp');
        $scheduleTime  = $scheduleTime->getTimestamp();
                
        if ($scheduleTime<=$timeNow) {
            $errorMessage=__("Invalid Schedule Details, You can not schedule a launch for the past date or time", WDM_WOO_SCHED_TXT_DOMAIN);
            return array("errorMessage"=>$errorMessage);
        }
        return $wdmScheduleArray;
    }
}

if (!function_exists('wdmValidateWholeDaySchedule')) {
    /**
     * When a schedule type is whole day on selected days during selected duration,
     * following things are checked.
     * - Start Date & time must be greater than current time (! important)
     * - End Date must be greater than start date
     * - skip dates should be optional
     *  - skip start date must be less than or equal to skip end date
     *  - skip start date must be greater than schedule start date and skip end date should must be less than schedule end date.
     *  - either of one can be empty (remove both and return array)
     *  when any of the above condition fails schedule array is returned with no skip dates.
     *
     * @param array $wdmScheduleArray
     * @return mixed
     */
    function wdmValidateWholeDaySchedule($wdmScheduleArray)
    {
        $scheduleStartTimestamp  = $wdmScheduleArray['startDate']." ".$wdmScheduleArray['startTime'];
        $scheduleEndTimestamp    = $wdmScheduleArray['endDate']." ".$wdmScheduleArray['endTime'];

        $scheduleStartTimestamp =new DateTime($scheduleStartTimestamp);
        $scheduleStartTimestamp =$scheduleStartTimestamp->getTimestamp();
        $scheduleEndTimestamp   =new DateTime($scheduleEndTimestamp);
        $scheduleEndTimestamp   =$scheduleEndTimestamp->getTimestamp();
                
        if ($scheduleStartTimestamp>=$scheduleEndTimestamp) {
            $errorMessage=__("Start Time Can not be same as or greater than the end time", WDM_WOO_SCHED_TXT_DOMAIN);
            return array("errorMessage"=>$errorMessage);
        }
                
        $wdmScheduleArray['daysSelected']=getSelectedDaysBetween($wdmScheduleArray['startDate'], $wdmScheduleArray['endDate'], $wdmScheduleArray['daysSelected']);

        if (empty($wdmScheduleArray['daysSelected'])) {
            $errorMessage=__("Invalid Schedule, No weekdays selected", WDM_WOO_SCHED_TXT_DOMAIN);
            return array("errorMessage"=>$errorMessage);
        }

        if (empty($wdmScheduleArray['skipStartDate']) || empty($wdmScheduleArray['skipEndDate'])) {
            unset($wdmScheduleArray['skipStartDate']);
            unset($wdmScheduleArray['skipEndDate']);
            return $wdmScheduleArray;
        }
        $skipStartDate  =new DateTime($wdmScheduleArray['skipStartDate']);
        $skipEndDate    =new DateTime($wdmScheduleArray['skipEndDate']);
        $startDate      =new DateTime($wdmScheduleArray['startDate']);
        $endDate        =new DateTime($wdmScheduleArray['endDate']);
        $startDate      =$startDate->getTimestamp();
        $endDate        =$endDate->getTimestamp();
        $skipStartDate  =$skipStartDate->getTimestamp();
        $skipEndDate    =$skipEndDate->getTimestamp();
                
        if ($skipStartDate>$skipEndDate || ($skipStartDate<=$startDate || $skipEndDate>=$endDate)) {
            unset($wdmScheduleArray['skipStartDate']);
            unset($wdmScheduleArray['skipEndDate']);
        }
        return $wdmScheduleArray;
    }
}

if (!function_exists('wdmValidateSpecificTimeSchedule')) {
    /**
     * wdmValidateSpecificTimeSchedule
     * this method validates if valid data is provided for spesific time schedule type.
     * returns false in case of invalid data , returns array containing schedule data
     * skip dates are skipped when invalid dates were specified.
     *
     * @param [type] $wdmScheduleArray
     * @return void
     */
    function wdmValidateSpecificTimeSchedule($wdmScheduleArray)
    {
                
        $arbitraryDate="01/01/2019";
        $scheduleStartTimestamp  = $arbitraryDate." ".$wdmScheduleArray['startTime'];
        $scheduleEndTimestamp    = $arbitraryDate." ".$wdmScheduleArray['endTime'];
                
        $scheduleStartDate  =new DateTime($wdmScheduleArray['startDate']);
        $scheduleEndDate    =new DateTime($wdmScheduleArray['endDate']);
        $scheduleStartTime  =new DateTime($scheduleStartTimestamp);
        $scheduleEndTime    =new DateTime($scheduleEndTimestamp);

        $scheduleStartDate  =$scheduleStartDate->getTimestamp();
        $scheduleEndDate    =$scheduleEndDate->getTimestamp();
        $scheduleStartTime  =$scheduleStartTime->getTimestamp();
        $scheduleEndTime    =$scheduleEndTime->getTimestamp();

        if ($scheduleStartDate>$scheduleEndDate) {
            $errorMessage=__("Start date cannot be greater than the end date", WDM_WOO_SCHED_TXT_DOMAIN);
            return array("errorMessage"=>$errorMessage);
        }
        if ($scheduleStartTime>=$scheduleEndTime) {
            $errorMessage=__("Start Time Can not be same as or greater than the end time", WDM_WOO_SCHED_TXT_DOMAIN);
            return array("errorMessage"=>$errorMessage);
        }
               $wdmScheduleArray['daysSelected']= getSelectedDaysBetween($wdmScheduleArray['startDate'], $wdmScheduleArray['endDate'], $wdmScheduleArray['daysSelected']);
        if (empty($wdmScheduleArray['daysSelected'])) {
            $errorMessage=__("Invalid Schedule, No wekdays are selected.", WDM_WOO_SCHED_TXT_DOMAIN);
            return array("errorMessage"=>$errorMessage);
        }

        if (empty($wdmScheduleArray['skipStartDate']) || empty($wdmScheduleArray['skipEndDate'])) {
            unset($wdmScheduleArray['skipStartDate']);
            unset($wdmScheduleArray['skipEndDate']);
            return $wdmScheduleArray;
        }
               $skipStartDate  =new DateTime($wdmScheduleArray['skipStartDate']);
               $skipEndDate    =new DateTime($wdmScheduleArray['skipEndDate']);
               $skipStartDate  =$skipStartDate->getTimestamp();
               $skipEndDate    =$skipEndDate->getTimestamp();
        if ($skipStartDate>$skipEndDate || ($skipStartDate<=$scheduleStartDate || $skipEndDate>=$scheduleEndDate)) {
            unset($wdmScheduleArray['skipStartDate']);
            unset($wdmScheduleArray['skipEndDate']);
        }
        return $wdmScheduleArray;
    }
}

if (!function_exists('getSelectedDaysBetween')) {
    /**
     * This method is used to filter the selected weekdays by their occurance during
     * scheduled dates. i.e. days not occuring during schedule duration will be removed.
     * from the existing days array.
     * @param string $startDate - schedule start date
     * @param string $endDate - schedule End date.
     * @param array $daysArray - an array of the days selected by the admin.
     * @return mixed $daysArray.
     */
    function getSelectedDaysBetween($startDate, $endDate, $daysSelected)
    {
           $days       = array("1"=>"Monday", "2"=>"Tuesday", "3"=>"Wednesday", "4"=>"Thursday", "5"=>"Friday", "6"=>"Saturday", "7"=>"Sunday");
           $availableDays=array();
        if (!empty($daysSelected["Everyday"]) && $daysSelected["Everyday"]=="on") {
            return $daysSelected;
        }

           $startDate  =new DateTime($startDate);
           $endDate    =new DateTime($endDate);
           $startDate  =$startDate->getTimestamp();
           $endDate    =$endDate->getTimestamp();

        if (($endDate-$startDate)>=(24*60*60*7)) {
            return $daysSelected;
        }

           //when duration selected is less than 7 get days fall between selected duration
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


if (!function_exists('wdmGetVariantScheduleDataToSave')) {
    /**
     * This method returns the schedule data in a scheduler data array format
     * for variable product variations by fetching it from the database for variation
     * id passed as a parameter.
     * @param int $variationId
     * @return array an Array Containing the schedule details
     */
    function wdmGetVariantScheduleDataToSave($variationId)
    {
        $wdmScheduleArray=array();
        $scheduleSettings=get_post_meta($variationId, 'wdm_schedule_settings', true);
                
        if (empty($scheduleSettings)) {
            return array();
        }

        switch ($scheduleSettings['type']) {
            case 'productLaunch':
                $wdmScheduleArray['type']           = $scheduleSettings['type'];
                $wdmScheduleArray['startDate']      = get_post_meta($variationId, 'wdm_start_date', true);
                $timeHrs                            = get_post_meta($variationId, 'wdm_start_time_hr', true);
                $timeMins                           = get_post_meta($variationId, 'wdm_start_time_min', true);
                $wdmScheduleArray['startTime']      = $timeHrs.":".$timeMins;
                $wdmScheduleArray["startTimer"]     = $scheduleSettings['startTimer'];
                $wdmScheduleArray["hideUnavailable"]= get_post_meta($variationId, '_hide_if_unavailable', true);
                break;
            case 'wholeDay':
            case 'specificTime':
                $wdmScheduleArray['type']           = $scheduleSettings['type'];
                $wdmScheduleArray['startDate']      = get_post_meta($variationId, 'wdm_start_date', true);
                $timeHrs                            = get_post_meta($variationId, 'wdm_start_time_hr', true);
                $timeMins                           = get_post_meta($variationId, 'wdm_start_time_min', true);
                $wdmScheduleArray['startTime']      = $timeHrs.":".$timeMins;
                $wdmScheduleArray['endDate']        = get_post_meta($variationId, 'wdm_end_date', true);
                $timeHrs                            = get_post_meta($variationId, 'wdm_end_time_hr', true);
                $timeMins                           = get_post_meta($variationId, 'wdm_end_time_min', true);
                $wdmScheduleArray['endTime']        = $timeHrs.":".$timeMins;
                $wdmScheduleArray['daysSelected']   = $scheduleSettings['daysSelected'];
                $wdmScheduleArray['skipStartDate']  = $scheduleSettings['skipStartDate'];
                $wdmScheduleArray['skipEndDate']    = $scheduleSettings['skipEndDate'];
                $wdmScheduleArray["startTimer"]     = $scheduleSettings['startTimer'];
                $wdmScheduleArray["endTimer"]       = $scheduleSettings['endTimer'];
                $wdmScheduleArray["hideUnavailable"]= get_post_meta($variationId, '_hide_if_unavailable', true);
                break;
            default:
                $wdmScheduleArray=array();
                break;
        }
        return $wdmScheduleArray;
    }
}



if (!function_exists('wdmGetPostScheduleDataToSave')) {
    /**
     * This method retrives schedule data form $_POST superglobal.
     * data is stored in the form of array and array according
     * to the schesule type is returned
     * schedule is not validated in this method.
     *
     *
     * @param [type] $productId
     * @return array
     */
    function wdmGetPostScheduleDataToSave($productId)
    {
        $wdmScheduleArray=array();
                
        $scheduleType=isset($_POST['ScheduleType'][$productId])?$_POST['ScheduleType'][$productId]:"";
        if ($scheduleType=="scheduleDuration") {
            $scheduleType=isset($_POST['AvailabilityType'][$productId])?$_POST['AvailabilityType'][$productId]:"";
        }

        switch ($scheduleType) {
            case 'productLaunch':
                $wdmScheduleArray['startDate']      =$_POST["wdmLaunchDate"][$productId];
                $wdmScheduleArray['startTime']      =$_POST["wdmLaunchTime"][$productId];
                break;
            case 'wholeDay':
                $wdmScheduleArray['startDate']      =$_POST["wdmWdStartDate"][$productId];
                $wdmScheduleArray['startTime']      =$_POST["wdmWdStartTime"][$productId];
                $wdmScheduleArray['endDate']        =$_POST["wdmWdEndDate"][$productId];
                $wdmScheduleArray['endTime']        =$_POST["wdmWdEndTime"][$productId];
                $daysSelected                       =$_POST['wdmWdWeekdays'][$productId];
                $wdmScheduleArray['daysSelected']   =$daysSelected;
                $wdmScheduleArray['skipStartDate']  =$_POST['wdmWdSkipStartDate'][$productId];
                $wdmScheduleArray['skipEndDate']    =$_POST['wdmWdSkipEndDate'][$productId];
                break;
            case 'specificTime':
                $wdmScheduleArray['startDate']      =$_POST["wdmLtStartDate"][$productId];
                $wdmScheduleArray['startTime']      =$_POST["wdmLtStartTime"][$productId];
                $wdmScheduleArray['endDate']        =$_POST["wdmLtEndDate"][$productId];
                $wdmScheduleArray['endTime']        =$_POST["wdmLtEndTime"][$productId];
                $daysSelected                       =$_POST['wdmLtWeekdays'][$productId];
                $wdmScheduleArray['daysSelected']   =$daysSelected;
                $wdmScheduleArray['skipStartDate']  =$_POST['wdmLtSkipStartDate'][$productId];
                $wdmScheduleArray['skipEndDate']    =$_POST['wdmLtSkipEndDate'][$productId];
                break;
            default:
                $wdmScheduleArray=array();
                break;
        }
        $wdmScheduleArray['type']           =$scheduleType;
        $wdmScheduleArray["startTimer"]     =$_POST["startTimer"][$productId]=="on"?true:false;
        $wdmScheduleArray["endTimer"]       =$_POST["wdmEndTimer"][$productId]=="on"?true:false;
        $wdmScheduleArray["hideUnavailable"]=$_POST["wdmHideUnavailabe"][$productId]=="on"?"yes":"no";
        return $wdmScheduleArray;
    }
}


if (!function_exists('getWdmShowFeedbackModal')) {
//A function to deside weather a modal for feedback should be displayed or not.
    function getWdmShowFeedbackModal()
    {
        $feedbackDate=get_option('wdmws_date_to_ask_for_the_feedback');
        $datetimeNow=current_time('timestamp');
        $isFeedbackGiven=get_option('wdmws_feedback_status');

        if (!empty($isFeedbackGiven) && ("submitted"==$isFeedbackGiven || "never_ask"==$isFeedbackGiven)) {
            return false;
        }
        if ($datetimeNow>$feedbackDate) {
            return true;
        }
        return false;
    }
}
