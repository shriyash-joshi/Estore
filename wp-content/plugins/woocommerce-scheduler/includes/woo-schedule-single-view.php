<?php
namespace WooScheduleSingleView;

if (!class_exists('WooScheduleSingleView')) {
    class WooScheduleSingleView
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
        }

        public function registerSingleViewSubmenuPage()
        {
            add_action('admin_enqueue_scripts', array($this,'wacListTableEnqueueScripts'), 20);
            if (isset($_GET) && isset($_GET['page']) && $_GET['page'] == 'wdmws_settings_bulk_schedule') {
                //add_action('admin_enqueue_scripts', array($this,'wooSingleViewSubmenuPageCallback'));
            }
        }

        public function wacListTableEnqueueScripts()
        {
            if (isset($_GET) && isset($_GET['page']) && $_GET['page'] == 'wdmws_settings_bulk_schedule') {
                // wp_enqueue_style('general_settings_css');
                // wp_enqueue_style('jquery_datatables_css');
            }

            if ($this->isBulkSchedulePage() || $this->isPageNotify() || $this->isPageGlobal()) {
                // Show Send Email Now confirmation modal
                // add_action('admin_footer', array(new \SchedulerAdmin(), 'showSendMailNowConfirmation'));
// 
                // wp_enqueue_script('notify_me_settings_js');
                // wp_enqueue_script('wdmws_modal_js');
// 
                // wp_enqueue_style('notify_me_settings_css');
                // wp_enqueue_style('wdmws_modal_css');
            // 
                // $this->localizeSendEmailConfirmationScript();
            }

            if ($this->isPageEnrolledUsers()) {
            //     // enqueue script
            //     wp_enqueue_script('jquery_datatables_js');
            //     wp_enqueue_script('datatables_semantic_js');
            //     wp_enqueue_script('datatables_semanticui_js');
            //     wp_enqueue_script('notify_me_settings_js');

            //     // enqueue style
            //     wp_enqueue_style('notify_me_settings_css');
            //    // wp_enqueue_style('jquery_datatables_css');
            //     wp_enqueue_style('datatables_semantic_css');
            //     wp_enqueue_style('datatables_semanticui_css');
               
            //     wp_localize_script(
            //         'notify_me_settings_js',
            //         'export_data',
            //         array(
            //             'security_check'  => __('Don\'t have access to export', WDM_WOO_SCHED_TXT_DOMAIN),
            //             'user_list_empty' => __('User List is empty', WDM_WOO_SCHED_TXT_DOMAIN)
            //         )
            //     );
            }

            if ($this->isPageOtherExtension()) {
                // wp_enqueue_style('wdmws_other_extension_css');
            }

            if ($this->isPageGlobal()) {
                // wp_enqueue_style('global_settings_css');
                // wp_enqueue_script('global_settings_js');
                // $wdm_settings_object=array(
                    // 'wdmFeedbackStatus'=>getWdmShowFeedbackModal()?"show":"do_not_show",
                // );
                // wp_localize_script('global_settings_js', 'wdm_settings_object', $wdm_settings_object);
            }
        }

        /**
         * Localize variables which are required for 'Send Email Now'
         * confirmation popup manipulation.
         */
        public function localizeSendEmailConfirmationScript()
        {
            $wdmwsSettings = \Includes\AdminSettings\WdmWsSettings::getSettings();

            $wdmwsNotifyFeatureEnabled = isset($wdmwsSettings['wdmws_enable_notify']) ? $wdmwsSettings['wdmws_enable_notify'] : '';
            $wdmwsNotifyEvent = isset($wdmwsSettings['wdmws_notify_event']) ? $wdmwsSettings['wdmws_notify_event'] : 'notify_once';
            $wdmwsNotifyUserPeriod = isset($wdmwsSettings['wdmws_notify_user_period']) ? $wdmwsSettings['wdmws_notify_user_period'] : 'product_avail';
            $wdmwsCustomProductExpType = isset($wdmwsSettings['wdmws_custom_product_expiration_type']) ? $wdmwsSettings['wdmws_custom_product_expiration_type'] : 'per_day';
            $sendEmailConfMsg = __('Some crons may not be set. Do you want to send the notification email for those crons now? Please review email template before sending notification email.', WDM_WOO_SCHED_TXT_DOMAIN);

            wp_localize_script(
                'notify_me_settings_js',
                'wdmws_send_email_confirmation',
                array(
                    'wdmws_notify_enabled'      => $wdmwsNotifyFeatureEnabled,
                    'wdmws_notify_event'        => $wdmwsNotifyEvent,
                    'wdmws_notify_user_period'  => $wdmwsNotifyUserPeriod,
                    'wdmws_product_exp_type'    => $wdmwsCustomProductExpType,
                    'wdmws_send_email_conf_msg' => $sendEmailConfMsg
                )
            );
        }

        public function isPageGeneral()
        {
            $getPageArray = "";
            if (!isset($_GET) || !isset($_GET['page']) || $_GET['page'] != 'wdmws_settings') {
                return false;
            }
            $getPageArray = !isset($_GET['_']) ? "" : $_GET['_'];

            if (empty($getPageArray)) {
                return false;
            }
            if (!isset($getPageArray['flow']) && $getPageArray['flow'] != 'schedule_product_settings') {
                return false;
            }
                
            if ($getPageArray['flow_page'] != "general") {
                return false;
            }

            return true;
        }

        public function isBulkSchedulePage()
        {
            if (isset($_GET) && isset($_GET['page']) && $_GET['page'] == 'wdmws_settings_bulk_schedule') {
                return true;
            }
            return false;
        }

        public function isPageGlobal()
        {
            $getPageArray = "";
            if (!isset($_GET) || !isset($_GET['page']) || $_GET['page'] != 'wdmws_settings') {
                return false;
            }
            $getPageArray = !isset($_GET['_']) ? "" : $_GET['_'];

            if (empty($getPageArray)) {
                return true;
            }

            if (!isset($getPageArray['flow']) && $getPageArray['flow'] != 'schedule_product_settings') {
                return false;
            }
                
            if ($getPageArray['flow_page'] != "global") {
                return false;
            }

            return true;
        }

        public function isPageOtherExtension()
        {
            $getPageArray = "";
            if (!isset($_GET) || !isset($_GET['page']) || $_GET['page'] != 'wdmws_settings') {
                return false;
            }
            $getPageArray = !isset($_GET['_']) ? "" : $_GET['_'];

            if (empty($getPageArray)) {
                return false;
            }

            if (!isset($getPageArray['flow']) && $getPageArray['flow'] != 'schedule_product_settings') {
                return false;
            }
                
            if ($getPageArray['flow_page'] != "other_extensions") {
                return false;
            }

            return true;
        }

        public function isPageNotify()
        {
            $getPageArray = "";
            if (!isset($_GET) || !isset($_GET['page']) || $_GET['page'] != 'wdmws_settings') {
                return false;
            }
            $getPageArray = !isset($_GET['_']) ? "" : $_GET['_'];

            if (empty($getPageArray)) {
                return false;
            }
            if (!isset($getPageArray['flow']) && $getPageArray['flow'] != 'schedule_product_settings') {
                return false;
            }

            if ($this->checkNotifyPageCondition($getPageArray)) {
                return false;
            }

            return true;
        }

        public function isPageEnrolledUsers()
        {
            if (!isset($_GET) || !isset($_GET['page']) || $_GET['page'] != 'wdmws_settings_enrolled_users') {
                return false;
            }

            return true;
        }

        public function checkNotifyPageCondition($getPageArray)
        {
            if ('notify_user_global' != $getPageArray['flow_page'] && 'notify_user' != $getPageArray['flow_page'] && 'notify_user_email' != $getPageArray['flow_page'] && 'notify_user_enrolled_users' != $getPageArray['flow_page']) {
                return true;
            }
            return false;
        }


    }
}
