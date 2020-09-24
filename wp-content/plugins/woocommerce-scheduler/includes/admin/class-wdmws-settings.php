<?php
namespace Includes\AdminSettings;

if (!class_exists('WdmWsSettings')) {
    class WdmWsSettings {

        /**
         * This method is used to migrate the setting fields defined in versions
         * before v2.3.5
         * 
         * @since 2.3.5
         */
        public static function migrateSettingsFromOlderVersions() {
            $wdmwsSettings = get_option('wdmws_settings', array());
            if (!empty($wdmwsSettings)) {
                foreach ($wdmwsSettings as $aSetting => $value) {
                    if (in_array($aSetting, array('wdmws_enable_notify', 'wdmws_guest_user_enrl','wdmws_enable_send_email_enrl'))) {
                        $value = 'enable'==$value?1:0;
                    } elseif ( in_array($aSetting, array('wdmws_notify_event', 'wdmws_notify_user_period', 'wdmws_guest_user_enrl_method'))) {
                        $value = $value[0];
                    }
                    update_option($aSetting, $value);
                }
            }
        }
        
        /**
         * This function returns the array of all the scheduler setting fields if called without an arguement
         * else return the value of the perticular setting field specified in the arguement 
         *
         * @since 2.3.5
         * @param string $aSetting - setting name
         * @return void
         */
        public static function getSettings($aSetting = null) {
            $settingsMigrated = get_option('wdmws_settings_migrated', false);
            if (!$settingsMigrated) {
                self::migrateSettingsFromOlderVersions();
                update_option('wdmws_settings_migrated', WS_VERSION);
            }
            if (null == $aSetting) {
                return self::fetchAllSettingFields();
            } else {
                $settingValue = get_option($aSetting);
                return empty($settingValue)?getDefault($aSetting):$settingValue;
            }
        }

        /**
         * This method returns all the setting field values 
         * of the scheduler
         *
         * @return mixed
         */
        public static function fetchAllSettingFields(){
            $wdmwsSettings   = array();
            $settingFields   = array(
                                    'wdmws_custom_product_expiration',
                                    'wdmws_custom_product_shop_expiration',
                                    'wdmws_start_timer_text',
                                    'wdmws_end_timer_text',
                                    'wdmws_launch_start_timer_text',
                                    'wdmws_font_color',
                                    'wdmws_background_color',
                                    'wdmws_front_color',
                                    'wdmws_enable_notify',
                                    'wdmws_success_enrl_msg',
                                    'wdmws_notify_btn_txt',
                                    'wdmws_notify_user_css',
                                    'wdmws_guest_user_enrl',
                                    'wdmws_guest_user_enrl_method',
                                    'wdmws_notify_event',
                                    'wdmws_notify_user_period',
                                    'wdmws_notify_custom_hr',
                                    'wdmws_unsubscription_page',
                                    'wdmws_enable_send_email_enrl',
                                    'wdmws_enrl_email_sub',
                                    'wdmws_enrl_email_body',
                                    'wdmws_notification_email_sub',
                                    'wdmws_notification_email_body'
                                    );
            foreach ($settingFields as $aSetting) {
                $settingValue = get_option($aSetting);
                if (empty($settingValue)) {
                    $settingValue = self::getDefault($aSetting);
                }
                $wdmwsSettings[$aSetting] = $settingValue;
            }
            return $wdmwsSettings;
        }


        /**
         * This method returns the default values for the setting fields
         *
         * @param [type] $settingField
         * @return 
         */
        public static function getDefault($settingField) {
            $defaultValue = '';
            switch ($settingField) {
                case 'wdmws_custom_product_expiration':
                    $defaultValue = esc_html__('Currently Unavailable', 'woocommerce-scheduler');
                    break;
                case 'wdmws_custom_product_shop_expiration':
                    $defaultValue = esc_html__('Unavailable', 'woocommerce-scheduler');
                    break;
                case 'wdmws_start_timer_text':
                    $defaultValue = esc_html__('This product will be available in', 'woocommerce-scheduler');
                    break;
                case 'wdmws_end_timer_text':
                    $defaultValue = esc_html__('This product will be available only for', 'woocommerce-scheduler');
                    break;
                case 'wdmws_launch_start_timer_text':
                    $defaultValue = esc_html__('This product will be launched in', 'woocommerce-scheduler');
                    break;
                case 'wdmws_font_color':
                    $defaultValue = '';
                    break;
                case 'wdmws_background_color':
                    $defaultValue = '';
                    break;
                case 'wdmws_front_color':
                    $defaultValue = '';
                    break;
                case 'wdmws_enable_notify':
                    $defaultValue = 0 ;
                    break;
                case 'wdmws_success_enrl_msg':
                    $defaultValue = esc_html__('Enrolled Successfully', 'woocommerce-scheduler');
                    break;
                case 'wdmws_notify_btn_txt':
                    $defaultValue = esc_html__('Notify Me', 'woocommerce-scheduler');
                    break;
                case 'wdmws_notify_user_css':
                    $defaultValue = '';
                    break;
                case 'wdmws_guest_user_enrl':
                    $defaultValue = 0;
                    break;
                case 'wdmws_guest_user_enrl_method':
                    $defaultValue = 'popup';
                    break;
                case 'wdmws_notify_event':
                    $defaultValue = 'notify_once';
                    break;
                case 'wdmws_notify_user_period':
                    $defaultValue = 'product_avail';
                    break;
                case 'wdmws_notify_custom_hr':
                    $defaultValue = 2;
                    break;
                case 'wdmws_unsubscription_page':
                    $defaultValue = '';
                    break;
                case 'wdmws_enable_send_email_enrl':
                    $defaultValue = 0;
                    break;
                case 'wdmws_enrl_email_sub':
                    $defaultValue = esc_html__('Product enrollment successful from [site_title]', 'woocommerce-scheduler');
                    break;
                case 'wdmws_enrl_email_body':
                    include_once WDMWS_PLUGIN_PATH.'/includes/emails/class-scheduler-enrollment-email.php';
                    $defaultValue = \Includes\Frontend\SchedulerEnrollmentEmail::defaultEmailTemplateWithCSS();
                    break;
                case 'wdmws_notification_email_sub':
                    $defaultValue = esc_html__('Product Notification from [site_title]', 'woocommerce-scheduler');
                    break;
                case 'wdmws_notification_email_body':
                    include_once WDMWS_PLUGIN_PATH.'/includes/emails/class-scheduler-notification-email.php';
                    $defaultValue = \Includes\Frontend\SchedulerNotificationEmail::defaultEmailTemplateWithCSS();
                    break;
                default:
                    $defaultValue = '';
                    break;
            }
            return $defaultValue;
        }
    }
}
