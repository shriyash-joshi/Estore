<?php
namespace Includes;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Handles all functionalities related to the notification cron.
 * For example, setting cron, sending notification email on product availability, etc.
 * @author WisdmLabs
 */
if (!class_exists('WdmwsNotifyCron')) {
    class WdmwsNotifyCron
    {
        private $settings;

        public function __construct()
        {
            $wdmwsSettings = \Includes\AdminSettings\WdmWsSettings::getSettings();
            $this->settings = $wdmwsSettings;

            if (isset($wdmwsSettings['wdmws_enable_notify']) && '1' == $wdmwsSettings['wdmws_enable_notify']) {
                add_action('wdmws_after_product_scheduled', array($this, 'setNotificationCron'), 20, 2);
                add_action('wdmws_after_product_unscheduled', array($this, 'removeProductNotificationCron'), 20, 1);
                add_action('wdmws_after_term_unscheduled', array($this, 'removeProductCatNotificationCron'), 20, 1);
                add_action('wdmws_product_notify_user', array($this, 'executeProductNotificationCron'));
                add_action('wdmws_product_cat_notify_user', array($this, 'executeProductCatNotificationCron'));
                add_action('wdmws_category_notify_user', array($this, 'executeProductCatNotificationCron'));
                add_filter('wdmws_process_to_set_cron', array($this, 'scheduleOnTimeCrons'), 20, 8);
                add_action('admin_footer', array($this, 'removeSendEmailNowCookie'));
                add_action('wdmws_notify_crons_after_scheduled', array($this,'setNewNotificationCrons'), 20, 4);
            }
            add_filter('piklistfw_pre_update_option', array($this, 'checkNotificationCronNeedToSet'), 20, 4);
        }

        /**
         * This method sets up new notification crons according to the schedule type selected.
         * creates a single notification cron for launch product schedule type. for other
         * schedule types creates multiple notification crons according to the settings specified
         * and availability array passed.
         *
         * @param [type] $productId
         * @param [type] $availabilityStartArray
         * @param [type] $scheduleType
         * @return void
         */
        public function setNewNotificationCrons($productId, $availabilityStartArray, $scheduleType, $cronFor)
        {
            
            $wdmwsSettings                  = $this->settings;
            $shouldSendEmailNow             = isset($_COOKIE['wdmwsSendEmailNow']) && 'saveSend' == $_COOKIE['wdmwsSendEmailNow'];
        
            $notificationCronName    = ($cronFor=="product")?"wdmws_product_notify_user":"wdmws_product_cat_notify_user";

            wp_clear_scheduled_hook($notificationCronName, array($productId));
            switch ($scheduleType) {
                case "productLaunch":
                    $cronDateTime = $this->calcCronTimeBasedOnSettings($wdmwsSettings, $availabilityStartArray[0]);
                    $cronDateTime = (new \DateTime())->setTimestamp($cronDateTime);
                    $cronDateTimeGMT = strtotime(get_gmt_from_date($cronDateTime->format('Y-m-d H:i:s')) . ' GMT');
                    if (time() >= $cronDateTimeGMT) {
                        if (true == $shouldSendEmailNow && time() <= $availabilityStartArray[0]) {
                            $this->sendEmailNow($productId, $notificationCronName);
                        }
                    } else {
                        wp_schedule_single_event($cronDateTimeGMT, $notificationCronName, array($productId));
                    }
                    break;
                default:
                    //for schedule type "whole Time On Selected Days" & "Specific Time On Selected Days"
                    $this->wdmNotificationCronsForDurationSelected($productId, $availabilityStartArray, $cronFor);
                    break;
            }
        }

        /**
         * Used to setup the notification crons for the "wholeDay" & "specific time on selected days setting"
         *
         * @param [type] $productId
         * @param [type] $availabilityStartArray
         * @return void
         */
        private function wdmNotificationCronsForDurationSelected($productId, $availabilityStartArray, $cronFor)
        {
            $wdmwsSettings                  = $this->settings;
            $shouldSendEmailNow             = isset($_COOKIE['wdmwsSendEmailNow']) && 'saveSend' == $_COOKIE['wdmwsSendEmailNow'];
            $notificationCronName    = ($cronFor=="product")?"wdmws_product_notify_user":"wdmws_product_cat_notify_user";
            $notifyOnlyOnce = isset($wdmwsSettings['wdmws_notify_event']) && 'notify_every_avail' == $wdmwsSettings['wdmws_notify_event'] ? false : true;
            $isEmailSent = false;
            foreach ($availabilityStartArray as $cronTime) {
                    $availabilityTime = $cronTime;
                    $cronTime = $this->calcCronTimeBasedOnSettings($wdmwsSettings, $cronTime);
                    
                if (false === $cronTime) {
                    continue;
                } elseif (time() >= $cronTime) {
                    if ($shouldSendEmailNow && !$isEmailSent && time() <= $availabilityTime) {
                        $isEmailSent = true;
                        $this->sendEmailNow($productId, $notificationCronName);
                    }
                    if ($notifyOnlyOnce && $shouldSendEmailNow) {
                        return;
                    }
                    continue;
                }
                    $cronDateTime = (new \DateTime())->setTimestamp($cronTime);
                    $cronDateTimeGMT = strtotime(get_gmt_from_date($cronDateTime->format('Y-m-d H:i:s')) . ' GMT');
                    wp_schedule_single_event($cronDateTimeGMT, $notificationCronName, array($productId));

                if ($notifyOnlyOnce) {
                    return;
                }
            }
        }

        /**
         * Remove product notification cron.
         */
        public function removeProductNotificationCron($productId)
        {
            wp_clear_scheduled_hook('wdmws_product_notify_user', array(strval($productId)));
        }

        /**
         * Remove product cat notification cron.
         */
        public function removeProductCatNotificationCron($termId)
        {
            wp_clear_scheduled_hook('wdmws_product_cat_notify_user', array(strval($termId)));
        }

        /**
         * Set the notification for the product or the product category.
         * @param array     $selectionsList Array containing the product Ids or
         *                                  product categories Ids for which
         *                                  notififcation cron has to be set.
         * @param string    $selectionType  Selection type either 'product' or
         *                                  'category'.
         */
        public function setNotificationCron($selectionsList, $selectionType)
        {
            if (!empty($selectionsList)) {
                // If cron has to be set for product categories.
                if ('category' == $selectionType) {
                    global $wpdb;
            
                    $query = "SELECT * FROM " . $wpdb->prefix . "woo_schedule_category WHERE term_id IN (" . implode(',', $selectionsList) . ")";
        
                    $terms = $wpdb->get_results($query, ARRAY_A);
        
                    foreach ($terms as $term) {
                        $this->setProductCatWiseCron($term);
                    }
                    return;
                }

                // If cron has to be set for products.
                // 'selectedId' => product Id or product category Id
                foreach ($selectionsList as $productId) {
                    $this->setProductWiseCron($productId);
                }
            }
            $this->removeSendEmailNowCookie();
        }

        /**
         * Returns scheduled product Ids.
         *
         * @return  array   Array containing product Ids or empty
         *                  array if no data found.
         */
        public function retrieveScheduledProdIds()
        {
            global $wpdb;
            $query = "SELECT post_id FROM " . $wpdb->prefix . "postmeta WHERE 
            meta_key = 'wdm_start_date' AND meta_value <> '' Order by post_id";

            $productIds = $wpdb->get_results($query, ARRAY_A);

            return $productIds;
        }

        /**
         * Returns scheduled product categories data.
         *
         * @return  array   Array containing data related to the
         *                  product categories which have been scheduled.
         */
        public function retrieveScheduledProdCatData()
        {
            global $wpdb;
            
            $query = "SELECT * FROM " . $wpdb->prefix . "woo_schedule_category";

            $terms = $wpdb->get_results($query, ARRAY_A);

            return $terms;
        }

        /**
         * Set notification cron the product.
         * @param int $productId Product ID.
         */
        public function setProductWiseCron($productId)
        {
            $currProd = wc_get_product($productId);
            setProductWiseCron($currProd, $productId, 'wdmws_product_notify_user');
        }

        /**
         * Set notification cron for the product catrgory.
         *
         * @param array $term Scheduled product category related data.
         */
        public function setProductCatWiseCron($term)
        {
            $termId = $term['term_id'];
            $startDateTime = $term['start_date'];
            $endDateTime   = $term['end_date'];

            $startDate = date("Y-m-d", strtotime($startDateTime));
            $endDate   = date("Y-m-d", strtotime($endDateTime));

            $startTime = date('h:i A', strtotime($startDateTime));
            $endTime   = date('h:i A', strtotime($endDateTime));

            // days selected
            $daysSelected = $term['selected_days'];
            $daysSelected = unserialize($daysSelected);

            $this->scheduleOnTimeCrons('false', $termId, $startDate, $endDate, $startTime, $endTime, $daysSelected, 'wdmws_product_cat_notify_user');
        }

        /**
         * Set the notification cron for the product or product category ID.
         *
         * @param int    $selectedId    Product ID or product category Id.
         * @param string $startDate     Start date of the schedule.
         * @param string $endDate       End date of the schedule.
         * @param string $startTime     Start time of the schedule.
         * @param string $endTime       End time of the schedule.
         * @param array  $daysSelected  Array containing the days when product
         *                              would be available.
         * @param string $notificationCronHookName Cron action hook name
         * @param string $wdmwsSettings Scheduler setting.
         */
        public function scheduleOnTimeCrons($processToSetCron, $selectedId, $startDate, $endDate, $startTime, $endTime, $daysSelected, $notificationCronHookName)
        {
            if ('product' == $notificationCronHookName || 'wdmws_product_notify_user' == $notificationCronHookName || 'wdmws_product_cat_notify_user' == $notificationCronHookName) {
                if ('product' == $notificationCronHookName) {
                    $processToSetCron = true;
                    $notificationCronHookName = 'wdmws_product_notify_user';
                } else {
                    $processToSetCron = false;
                }

                $startTime = trim($startTime);
                $endTime   = trim($endTime);

                if (':' == $startTime || ':' == $endTime) {
                    return $processToSetCron;
                }

                $wdmwsSettings = $this->settings;

                wp_clear_scheduled_hook($notificationCronHookName, array($selectedId));

                $product_exp_type = isset($wdmwsSettings) && isset($wdmwsSettings['wdmws_custom_product_expiration_type'][0]) ? $wdmwsSettings['wdmws_custom_product_expiration_type'][0] : 'per_day';

                if ($product_exp_type == 'per_day') {
                    if (empty($daysSelected)) {
                        return;
                    }

                    $scheduleCronDates = getDatesToSetCron($startDate, $endDate, $startTime, $endTime);
                    if (!empty($scheduleCronDates)) {
                        $this->setCronSchedulePerDay($scheduleCronDates['scheduleCronStartDates'], $notificationCronHookName, $selectedId, $daysSelected, $wdmwsSettings);
                    }
                } else {
                    $shouldSendEmailNow = isset($_COOKIE['wdmwsSendEmailNow']) && 'saveSend' == $_COOKIE['wdmwsSendEmailNow'];
                    $startDateTime = getConcatenatedDateTime($startDate, $startTime);
                    $startDateTime = new \DateTime($startDateTime);
                    $startDateTimeGMT = strtotime(get_gmt_from_date($startDateTime->format('Y-m-d H:i:s')) . ' GMT');
                    $cronDateTime = $this->calcCronTimeBasedOnSettings($wdmwsSettings, $startDateTime->getTimestamp());
                    $cronDateTime = (new \DateTime())->setTimestamp($cronDateTime);
                    $cronDateTimeGMT = strtotime(get_gmt_from_date($cronDateTime->format('Y-m-d H:i:s')) . ' GMT');

                    if (false === $cronDateTimeGMT) {
                        return;
                    } elseif (time() >= $cronDateTimeGMT) {
                        if (true == $shouldSendEmailNow && time() <= $startDateTimeGMT) {
                            $this->sendEmailNow($selectedId, $notificationCronHookName);
                        }
                    } else {
                        wp_schedule_single_event($cronDateTimeGMT, $notificationCronHookName, array($selectedId));
                    }
                }
            }
            return $processToSetCron;
        }

        /**
         * Calculates the cron time based on the following settings:
         *     - Notify Event
         *     - Notify Customers
         *     - Scheduler Setting
         *
         * @param array $wdmwsSettings      Scheduler Settings
         * @param int   $startDateTimeStamp Timestamp of the product start date time.
         *
         * @return int Timestamp at what cron should be set.
         */
        public function calcCronTimeBasedOnSettings($wdmwsSettings, $startDateTimeStamp)
        {
            $startDateTimeStamp = $this->calcCronTimeBasedOnNotifyCustomers($wdmwsSettings, $startDateTimeStamp);

            return (int)$startDateTimeStamp;
        }

        /**
         * Calculate cron time based on 'Notify Customers' setting.
         *
         * @param array $wdmwsSettings      Scheduler Settings
         * @param int   $startDateTimeStamp Timestamp of the product start date time.
         *
         * @return int Timestamp at what cron should be set.
         */
        public function calcCronTimeBasedOnNotifyCustomers($wdmwsSettings, $startDateTimeStamp)
        {
            $notifyCustomerBefore = isset($wdmwsSettings['wdmws_notify_user_period']) ? $wdmwsSettings['wdmws_notify_user_period'] : 'product_avail';

            switch ($notifyCustomerBefore) {
                case 'one_hr_before':
                    $startDateTimeStamp = strtotime('-1 hour', $startDateTimeStamp);
                    break;
                case 'one_day_before':
                    $startDateTimeStamp = strtotime('-1 day', $startDateTimeStamp);
                    break;
                case 'one_week_before':
                    $startDateTimeStamp = strtotime('-1 week', $startDateTimeStamp);
                    break;
                case 'custom_hr':
                    $customHr = $wdmwsSettings['wdmws_notify_custom_hr'];
                    $startDateTimeStamp = strtotime('-'.intval($customHr).' hours', $startDateTimeStamp);
                    break;
            }

            return $startDateTimeStamp;
        }

        /**
         * Used  to set the cron schedule if 'Per Day' setting is enabled.
         *
         * @param array $scheduleCronArray  Array containing the all possible
         *                                  start timestamps of product
         *                                  notifiation cron.
         * @param string $cronText          The name of an action hook to
         *                                  execute.
         * @param int    $post_id           Product Id or category Id for
         *                                  which cron would be scheduled.
         * @param array  $daysSelected      Array containing the days on
         *                                  which product would be available.
         * @param array  $wdmwsSettings     Scheduler settings.
         */
        public function setCronSchedulePerDay($scheduleCronArray, $cronText, $post_id, $daysSelected, $wdmwsSettings)
        {
            if (empty($daysSelected)) {
                return;
            }

            if (!empty($scheduleCronArray)) {
                $shouldSendEmailNow = isset($_COOKIE['wdmwsSendEmailNow']) && 'saveSend' == $_COOKIE['wdmwsSendEmailNow'];
                $notifyOnlyOnce = isset($wdmwsSettings['wdmws_notify_event']) && 'notify_every_avail' == $wdmwsSettings['wdmws_notify_event'] ? false : true;
                $isEmailSent = false;
                foreach ($scheduleCronArray as $cronTime) {
                    $day = date('l', $cronTime);
                    if (isset($daysSelected[$day]) && $daysSelected[$day] == 'on') {
                        $availabilityTime = $cronTime;
                        $cronTime = $this->calcCronTimeBasedOnSettings($wdmwsSettings, $cronTime);

                        if (false === $cronTime) {
                            continue;
                        } elseif (time() >= $cronTime) {
                            if ($shouldSendEmailNow && !$isEmailSent && time() <= $availabilityTime) {
                                $isEmailSent = true;
                                $this->sendEmailNow($post_id, $cronText);
                            }
                            if ($notifyOnlyOnce && $shouldSendEmailNow) {
                                return;
                            }
                            continue;
                        }

                        wp_schedule_single_event($cronTime, $cronText, array($post_id));

                        if ($notifyOnlyOnce) {
                            return;
                        }
                    }
                }
            }
        }

        /**
         * Returns the start/ end time in hr:min format.
         *
         * @param int    $post_id   Product Id.
         * @param string $hr_key    Post meta key name to fetch hr for start or
         *                          end time of product scheduled.
         * @param string $min_key   Post meta key name to fetch min for start or
         *                          end time of product scheduled.
         *
         * @return string Returns the start/end time of scheduled product in
         *                hr:min format.
         */
        public static function getTime($post_id, $hr_key, $min_key)
        {
            $time_hr = get_post_meta($post_id, $hr_key, true);
            $time_min = get_post_meta($post_id, $min_key, true);

            return $time_hr. ":" . $time_min;
        }

        /**
         * Returns the start date or end date of scheduled product.
         *
         * @param int     $postId       Scheduled product Id.
         * @param string  $dateKey      Post meta key to fetch the date.
         *
         * @return string Returns the start date or end date of the product.
         */
        public static function getDate($postId, $dateKey)
        {
            $date = get_post_meta($postId, $dateKey, true);
            return $date;
        }

        /**
         * Check whether there is need to set the notification cron for
         * products and product categories if settings have been changed.
         * If yes, set the notificationo cron. Also, check whether need
         * to unset the Notification cron if 'Notify User' feature is
         * disabled. If yes, unset the crons.
         */
        public function checkNotificationCronNeedToSet($settings, $setting, $new, $old)
        {
            if ($this->isCronRelatedSettingsChanged($new, $old)) {
                $this->updateAllNotificationCron($settings, $new);
            }
            
            unset($setting);
            return $settings;
        }

        /**
         * Check whether cron related settings are changed.
         * Cron related settings are:
         *      Scheduler setting (wdmws_custom_product_expiration_type)
         *      Enable Notify User
         *      Notify Event
         *      Notify Customers
         *
         * @param array $new    New scheduler settings.
         * @param array $old    Old scheduler settings.
         *
         * @return bool Returns true if any of the cron related settings is
         *              changed.
         *
         */
        public function isCronRelatedSettingsChanged($new, $old)
        {
            if ($this->isNotifyUserSettingChanged($new, $old)) {
                return true;
            } elseif (isset($new['wdmws_enable_notify']) && empty($new['wdmws_enable_notify'])) {
                return false;
            } elseif ($this->isProductExpirationTypeSettingChanged($new, $old) || $this->isNotifyEventSettingChanged($new, $old) || $this->isNotifyCustomersSettingChanged($new, $old)) {
                return true;
            }

            return false;
        }

        /**
         * Update all crons.
         *
         * @param array $wdmwsSettings  Scheduler settings.
         * @param array $new            New scheudler settings after clicking
         *                              on 'Save Settings' button on admin page.
         */
        public function updateAllNotificationCron($wdmwsSettings, $new)
        {
            if (isset($new['wdmws_enable_notify']) && empty($new['wdmws_enable_notify'])) {
                wdmwsUnscheduleCrons('wdmws_product_notify_user');
                wdmwsUnscheduleCrons('wdmws_product_cat_notify_user');
            } else {
                $this->settings = $wdmwsSettings;

                $scheduledProductIds = $this->retrieveScheduledProdIds();

                foreach ($scheduledProductIds as $productId) {
                    //$this->setProductWiseCron();
                    $availabilityArray = get_post_meta($productId['post_id'], 'availability_pairs', true);
                    $scheduleSettings  = get_post_meta($productId['post_id'], 'wdm_schedule_settings', true);
                    $availabilityStartArray= !empty($availabilityArray)?$availabilityArray['makeAvailable']:array();
                    if (!empty($scheduleSettings) && $scheduleSettings['type']=="productLaunch") {
                        $startDate              = get_post_meta($productId['post_id'], 'wdm_start_date', true);
                            $startTimeHr            = get_post_meta($productId['post_id'], 'wdm_start_time_hr', true);
                            $startTimeMin           = get_post_meta($productId['post_id'], 'wdm_start_time_min', true);
                        $startTime= $startTimeHr.':'.$startTimeMin;
                        $startDateTime = $startDate.' '.$startTime;
                        $startDateTime = new \DateTime($startDateTime);
                        $availabilityStartArray=array($startDateTime->getTimestamp());
                    }
                    
                    $this->setNewNotificationCrons((int)$productId['post_id'], $availabilityStartArray, $scheduleSettings['type'], "product");
                }

                $terms = $this->retrieveScheduledProdCatData();

                foreach ($terms as $term) {
                    //$this->setProductCatWiseCron($term);
                    $availabilityArray=maybe_unserialize($term['availability_array']);
                    $availabilityStartArray=!empty($availabilityArray)?$availabilityArray['makeAvailable']:array();
                    $type=$term['schedule_type'];
                    $this->setNewNotificationCrons((int)$term['term_id'], $availabilityStartArray, $type, "category");
                }
            }
        }

        /**
         * Check whether notify user admin setting is changed.
         *
         * @param $new New settings.
         * @param $old Old settings.
         *
         * @return bool true or false.
         */
        public function isNotifyUserSettingChanged($new, $old)
        {
            if (isset($new['wdmws_enable_notify'])) {
                if (!isset($old['wdmws_enable_notify']) && '1' == $new['wdmws_enable_notify']) {
                    return true;
                } elseif (isset($old['wdmws_enable_notify']) && $new['wdmws_enable_notify'] != $old['wdmws_enable_notify']) {
                    return true;
                }
            }
        
            return false;
        }

        /**
         * Check whether product expiration type admin setting is changed.
         *
         * @param $new New settings.
         * @param $old Old settings.
         *
         * @return bool true or false.
         */
        public function isProductExpirationTypeSettingChanged($new, $old)
        {
            //wdmws_custom_product_expiration_type
            if (isset($new['wdmws_custom_product_expiration_type']) && $new['wdmws_custom_product_expiration_type'] != $old['wdmws_custom_product_expiration_type']) {
                return true;
            }
            return false;
        }

        /**
         * Check whether notify event admin setting is changed.
         *
         * @param $new New settings.
         * @param $old Old settings.
         *
         * @return bool true or false.
         */
        public function isNotifyEventSettingChanged($new, $old)
        {
            //wdmws_notify_event
            if (isset($new['wdmws_notify_event'])) {
                if (!isset($old['wdmws_notify_event'])) {
                    return true;
                } elseif ($new['wdmws_notify_event'] != $old['wdmws_notify_event']) {
                    return true;
                }
            }
        
            return false;
        }

        /**
         * Check whether notify customer periods admin setting is changed.
         *
         * @param $new New settings.
         * @param $old Old settings.
         *
         * @return bool true or false.
         */
        public function isNotifyCustomersSettingChanged($new, $old)
        {
            //wdmws_notify_user_period
            if (isset($new['wdmws_notify_user_period'])) {
                if (!isset($old['wdmws_notify_user_period'])) {
                    return true;
                } elseif ($new['wdmws_notify_user_period'] != $old['wdmws_notify_user_period'] || ('custom_hr' == $new['wdmws_notify_user_period'] && $new['wdmws_notify_custom_hr'] != $old['wdmws_notify_custom_hr'])) {
                    return true;
                }
            }
        
            return false;
        }

        /**
         * Send the email if admin has confirmed to send the email 'right now'
         * if notification cron is not getting set for product/ product category.
         *
         * @param int    $selectedId                Product ID.
         * @param string $notificationCronHookName  Cron action hook name to
         *                                          determine whether the ID is
         *                                          of product or category.
         *
         */
        public function sendEmailNow($selectedId, $notificationCronHookName)
        {
            if ('wdmws_product_notify_user' == $notificationCronHookName) {
                // Send email for products whose cron is not set.
                $this->executeProductNotificationCron($selectedId);
            } else {
                // Send email for product categories whose cron is not set.
                $this->executeProductCatNotificationCron($selectedId);
            }
        }

        /**
         * Remove 'wdmwsSendEmailNow' cookie.
         */
        public function removeSendEmailNowCookie()
        {
            if (isset($_COOKIE['wdmwsSendEmailNow'])) {
                unset($_COOKIE['wdmwsSendEmailNow']);
                setcookie('wdmwsSendEmailNow', null, -1, '/');
            }
        }

        // Executing Cron functionalities

        /**
         * Retrieves the customers list and sends the notification when cron is
         * executed for a particular product Id.
         */
        public function executeProductNotificationCron($productId)
        {
            $productCustomerPair = $this->getCustomersListForProduct($productId);
            if (!empty($productCustomerPair)) {
                foreach ($productCustomerPair as $productId => $userEmails) {
                    foreach ($userEmails as $userEmail) {
                        $emailObject = \Includes\Frontend\SchedulerNotificationEmail::getInstance();
                        $emailObject->prepareData($productId, $userEmail);
                        do_action('wdmws_send_notification_email', $emailObject);
                    }
                }
            }
        }

        /**
         * Retrieves the customers list and sends the notification when cron is
         * executed for a particular product category.
         */
        public function executeProductCatNotificationCron($productCatId)
        {
            $productCustomerPair = $this->getCustomersListForProductCat($productCatId);
            $wdmwsSettings = \Includes\AdminSettings\WdmWsSettings::getSettings();
            
            if (!empty($productCustomerPair)) {
                foreach ($productCustomerPair as $productId => $userEmails) {
                    foreach ($userEmails as $userEmail) {
                        $emailObject = \Includes\Frontend\SchedulerNotificationEmail::getInstance($wdmwsSettings);//, $productId, $userEmail);
                        $emailObject->prepareData($productId, $userEmail);
                        do_action('wdmws_send_notification_email', $emailObject);
                    }
                }
            }
        }

        /**
         * Returns the enrolled customers list for the provided product ID.
         *
         * @param int $productId Product Id.
         *
         * @return array Returns the array containing product => customer
         *               pair.
         */
        public function getCustomersListForProduct($productId)
        {
            global $wpdb;
            $query = "SELECT user_email from ".wdmwsReturnEnrlUserListTable()." WHERE product_id=".$productId."";
            $customers = $wpdb->get_col($query);
            $customerProductPair = array();

            if (!empty($customers)) {
                $customerProductPair = array(
                    $productId => $customers
                );
            }

            $customerProductPair = apply_filters('wdmws_change_prod_cust_pair_on_prod_cron', $customerProductPair);
            return $customerProductPair;
        }

        /**
         * Returns the enrolled customers list for all the products of the provided
         * product category Id.
         *
         * @param int $productCatId Product category Id.
         *
         * @return array Returns the array containing product => customer
         *               pair.
         */
        public function getCustomersListForProductCat($productCatId)
        {
            global $wpdb;

            $query = "SELECT object_id as product_id FROM {$wpdb->prefix}term_relationships WHERE term_taxonomy_id = {$productCatId}";
            $products = $wpdb->get_results($query);

            $customerProductPair = array();

            foreach ($products as $key => $value) {
                unset($key);
                $productId = $value->product_id;
                $isCronSet = get_post_meta($productId, 'wdm_start_date', true);

                if (empty($isCronSet)) {
                    $query = "SELECT user_email from ".wdmwsReturnEnrlUserListTable()." WHERE product_id=".$productId."";
                    $customers = $wpdb->get_col($query);

                    if (!empty($customers)) {
                        $customerProductPair[$productId] = $customers;
                    }
                }
            }

            $customerProductPair = apply_filters('wdmws_change_prod_cust_pair_on_prod_cron', $customerProductPair);
            return $customerProductPair;
        }
    }
    new WdmwsNotifyCron();
}
