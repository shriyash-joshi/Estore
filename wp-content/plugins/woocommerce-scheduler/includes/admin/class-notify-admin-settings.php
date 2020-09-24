<?php
namespace Includes\AdminSettings;

if (!class_exists('schedulerNotifyAdminSettings')) {
    
    /**
     * This class extends wordpress settings API to register & display the settings
     * on the scheduler settings page.
     * 
     * @since 3.0.0
     */
    class schedulerNotifyAdminSettings {
        

        public function __construct() {
            $this->addSettingSections();
            $this->addSettingFields();
            $this->registerSettings();
        }
        
        /**
         * This method adds setting sections according to wordpress settings API
         *
         * @return void
         */
        public function addSettingSections() {
            add_settings_section('wdmws_notify_feature_status_section',
                                esc_html__('', 'woocommerce-scheduler'), 
                                array($this, 'featureStatusSectionDescription'),
                                'wdmws_notification_settings'    
                            );
           
                add_settings_section('wdmws_notify_feature_settings_section',
                                esc_html__('Settings for notify me feature', 'woocommerce-scheduler'), 
                                array($this, 'notifyFeatureSettingsDescription'),
                                'wdmws_notification_settings'    
                            );


            //emails page
            add_settings_section('wdmws_notify_email_list_section',
                                esc_html__('', 'woocommerce-scheduler'), 
                                null,
                                'wdmws_notification_email_settings'    
                            );

            //enrollment email page
            add_settings_section('wdmws_enrollment_email_template_section',
                                esc_html__('', 'woocommerce-scheduler'), 
                                null,
                                'wdmws_enrollment_email_template_settings'    
                            );

            //notification email page
            add_settings_section('wdmws_notification_email_template_section',
                                esc_html__('', 'woocommerce-scheduler'), 
                                null,
                                'wdmws_notification_email_template_settings'    
                            );
        }

        /**
         * This method registers all the settings using function register_setting.
         *
         * @return void
         */
        public function registerSettings() {
            //Availability Messages Settings
            $checkboxArgs   = array('type' => 'bool', 'sanitize_callback' => array($this, 'sanitizeCheckboxInput'), 'default' => false);
            $textArgs       = array('type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => null);
            $textAreaArgs        = array('type' => 'string', 'sanitize_callback' => null, 'default' => null);
            $enrolMethod    = array('type' => 'string', 'sanitize_callback' => array($this, 'sanitizeEnrollType'), 'default' => 'popup');
            $eventType      = array('type' => 'string', 'sanitize_callback' => array($this, 'sanitizeEventType'), 'default' => 'notify_once');
            $notifyTime     = array('type' => 'string', 'sanitize_callback' => array($this, 'sanitizeNotificationTimeOptions'), 'default' => 'product_avail');
            $customTime     = array('type' => 'string', 'sanitize_callback' => array($this, 'sanitizeCustomTimeHrs'), 'default' => 2);
            
            register_setting('wdmws_notification_settings', 'wdmws_enable_notify', $checkboxArgs);
            $textArgs['default'] = esc_html__('Enrolled Successfully', 'woocommerce-scheduler');
            register_setting('wdmws_notification_settings', 'wdmws_success_enrl_msg', $textArgs);
            $textArgs['default'] = esc_html__('Notify Me', 'woocommerce-scheduler');
            register_setting('wdmws_notification_settings', 'wdmws_notify_btn_txt', $textArgs);            
            register_setting('wdmws_notification_settings', 'wdmws_notify_user_css', $textAreaArgs);
            $checkboxArgs['default'] = false;
            register_setting('wdmws_notification_settings', 'wdmws_guest_user_enrl', $checkboxArgs);
            register_setting('wdmws_notification_settings', 'wdmws_guest_user_enrl_method', $enrolMethod);
            register_setting('wdmws_notification_settings', 'wdmws_notify_event', $eventType);
            register_setting('wdmws_notification_settings', 'wdmws_notify_user_period', $notifyTime);
            register_setting('wdmws_notification_settings', 'wdmws_notify_custom_hr', $customTime);

            $textArgs['default'] = wc_get_page_id( 'myaccount' );
            register_setting('wdmws_notification_email_settings', 'wdmws_unsubscription_page', $textArgs);
            
            register_setting('wdmws_enrollment_email_template_settings', 'wdmws_enable_send_email_enrl', $checkboxArgs);
            $textArgs['default'] = esc_html__('Product enrollment successful from [site_title]', 'woocommerce-scheduler');
            register_setting('wdmws_enrollment_email_template_settings', 'wdmws_enrl_email_sub', $textArgs);
            $textAreaArgs['default'] = \Includes\Frontend\SchedulerEnrollmentEmail::defaultEmailTemplateWithCSS();
            register_setting('wdmws_enrollment_email_template_settings', 'wdmws_enrl_email_body', $textAreaArgs);

            $textArgs['default'] = esc_html__('Product Notification from [site_title]', 'woocommerce-scheduler');
            register_setting('wdmws_notification_email_template_settings', 'wdmws_notification_email_sub', $textArgs);
            $textAreaArgs['default'] = \Includes\Frontend\SchedulerNotificationEmail::defaultEmailTemplateWithCSS();
            register_setting('wdmws_notification_email_template_settings', 'wdmws_notification_email_body', $textAreaArgs);
            
        }


        /**
         * This method uses wp Settings API function add_setting_field
         * to register the callback method to show the setting field
         * on front end.
         *
         * @return void
         */
        public function addSettingFields() {
            //Availability Messages Settings
            add_settings_field('wdmws_enable_notify', esc_html__('Enable Notify User', 'woocommerce-scheduler'), array($this, 'showEnableNotifySetting'), 'wdmws_notification_settings', 'wdmws_notify_feature_status_section');
            add_settings_field('wdmws_success_enrl_msg', esc_html__('Success Enrollment Message', 'woocommerce-scheduler'), array($this, 'showEnrollmentSuccessMessageSetting'), 'wdmws_notification_settings', 'wdmws_notify_feature_settings_section');
            add_settings_field('wdmws_notify_btn_txt', esc_html__('\'Notify Me\' Button Text', 'woocommerce-scheduler'), array($this, 'showNotifyButtonTextSetting'), 'wdmws_notification_settings', 'wdmws_notify_feature_settings_section');
    
            add_settings_field('wdmws_notify_user_css', esc_html__('Custom CSS', 'woocommerce-scheduler'), array($this, 'showNotifyButtonCustomCSSField'), 'wdmws_notification_settings', 'wdmws_notify_feature_settings_section');
            add_settings_field('wdmws_guest_user_enrl', esc_html__('Allow Guest User Enrollment', 'woocommerce-scheduler'), array($this, 'showAllowGuestUserEnrollmentSetting'), 'wdmws_notification_settings', 'wdmws_notify_feature_settings_section');
            add_settings_field('wdmws_guest_user_enrl_method', esc_html__('Guest Enrollment By', 'woocommerce-scheduler'), array($this, 'showGuestEnrollmentByOptions'), 'wdmws_notification_settings', 'wdmws_notify_feature_settings_section');
            add_settings_field('wdmws_notify_event', esc_html__('Notify Event', 'woocommerce-scheduler'), array($this, 'showNotifyEventOptions'), 'wdmws_notification_settings', 'wdmws_notify_feature_settings_section');
            add_settings_field('wdmws_notify_user_period', esc_html__('Notify Customers', 'woocommerce-scheduler'), array($this, 'showNotifyEventPeriodOptions'), 'wdmws_notification_settings', 'wdmws_notify_feature_settings_section');
            add_settings_field('wdmws_notify_custom_hr', esc_html__('Enter Custom Hours', 'woocommerce-scheduler'), array($this, 'showNotifyCustomHoursSetting'), 'wdmws_notification_settings', 'wdmws_notify_feature_settings_section');

            //Email Page
            add_settings_field('wdmws_unsubscription_page', esc_html__('Unsubscription Page', 'woocommerce-scheduler'), array($this, 'showUnsubscriptionPageSetting'), 'wdmws_notification_email_settings', 'wdmws_notify_email_list_section');
            
            //Enrollment email template page
            add_settings_field('wdmws_enable_send_email_enrl', esc_html__('Send Enrollment Notification', 'woocommerce-scheduler'), array($this, 'showEnrollmentNotificationStatusCheckbox'), 'wdmws_enrollment_email_template_settings', 'wdmws_enrollment_email_template_section');
            add_settings_field('wdmws_enrl_email_sub', esc_html__('Subject Text', 'woocommerce-scheduler'), array($this, 'showEnrollmentMailSubject'), 'wdmws_enrollment_email_template_settings', 'wdmws_enrollment_email_template_section');
            add_settings_field('wdmws_enrl_email_body', esc_html__('Email Template', 'woocommerce-scheduler'), array($this, 'showEnrollmentMailEditor'), 'wdmws_enrollment_email_template_settings', 'wdmws_enrollment_email_template_section');

            //Notification Email Template Page
            add_settings_field('wdmws_notification_email_sub', esc_html__('Subject Text', 'woocommerce-scheduler'), array($this, 'showNotificationMailSubject'), 'wdmws_notification_email_template_settings', 'wdmws_notification_email_template_section');
            add_settings_field('wdmws_notification_email_body', esc_html__('Email Template', 'woocommerce-scheduler'), array($this, 'showNotificationMailEditor'), 'wdmws_notification_email_template_settings', 'wdmws_notification_email_template_section');
            
        }
    

        /*********************************************************
         * Setting Field Input Elements : Availability Messages  *
         *********************************************************/
        public function showEnableNotifySetting() {
            $featureEnabled             = get_option('wdmws_enable_notify');
            $description                = esc_html__('This setting field enables the Notify Me feature', 'woocommerce-scheduler');
            ?>
            <input type="checkbox" name="wdmws_enable_notify" id="wdmws_enable_notify" aria-describedby="desc-enable-notify-setting" class="" value="1" <?php checked(1, $featureEnabled, true); ?>>
            <p class="description" id="desc-enable-notify-setting"><?php echo $description; ?></p>
            <?php
        }

        public function showEnrollmentSuccessMessageSetting() {
            $enrolledMessage    = get_option('wdmws_success_enrl_msg');
            $description        = esc_html__('This message will be displayed when a user successfully enrolls for the availability notification', 'woocommerce-scheduler');
            ?>
            <input type="text" name="wdmws_success_enrl_msg" id="wdmws_success_enrl_msg" class="regular-text" aria-describedby="desc-enrolled-message" value="<?php echo esc_attr($enrolledMessage); ?>">
            <p class="description"  id="desc-enrolled-message"><?php echo $description; ?></p>
            <?php
        }

        public function showNotifyButtonTextSetting() {
            $enrolledMessage = get_option('wdmws_notify_btn_txt');
            $description        = esc_html__('This text will be displayed on an enrollment button on a product page', 'woocommerce-scheduler');
            ?>
            <input type="text" name="wdmws_notify_btn_txt" id="wdmws_notify_btn_txt" class="regular-text" aria-describedby="desc-enrolled-message" value="<?php echo esc_attr($enrolledMessage); ?>">
            <p class="description"  id="desc-enrolled-message"><?php echo $description; ?></p>
            <?php
        }

        public function showNotifyButtonCustomCSSField() {
            $enrolledMessage    = get_option('wdmws_notify_user_css');
            $description        = esc_html__('This CSS will take effect only on a product page when Notify button is visible', 'woocommerce-scheduler');
            ?>
            <textarea name="wdmws_notify_user_css" id="wdmws_notify_user_css" class="code" rows="8" cols="40" aria-describedby="desc-custom-css" value=""><?php echo $enrolledMessage; ?></textarea>
            <p class="description"  id="desc-custom-css"><?php echo $description; ?></p>
            <?php
        }

        public function showAllowGuestUserEnrollmentSetting() {
            $featureEnabled             = get_option('wdmws_guest_user_enrl');
            $description                = esc_html__('Enabling this option will allow to enroll the guest users for the product availability notifications', 'woocommerce-scheduler');
            ?>
            <input type="checkbox" name="wdmws_guest_user_enrl" id="wdmws_guest_user_enrl" aria-describedby="desc-enable-notify-setting" class="" value="1" <?php checked(1, $featureEnabled, true); ?>>
            <p class="description" id="desc-enable-notify-setting"><?php echo $description; ?></p>
            <?php
        }

        public function showGuestEnrollmentByOptions() {
            $enrollFormType             = get_option('wdmws_guest_user_enrl_method');
            $description                = esc_html__('Select how to show enrollment form to the guest users', 'woocommerce-scheduler');
            
            ?>
            <label><input type="radio" name="wdmws_guest_user_enrl_method" id="wdmws_guest_user_enrl_by_popup" aria-describedby="desc-enable-notify-setting" class="" value="popup" <?php checked("popup", $enrollFormType, true); ?>><?php esc_html_e('Popup','woocommerce-scheduler'); ?></label>
            <label><input type="radio" name="wdmws_guest_user_enrl_method" id="wdmws_guest_user_enrl_by_field" aria-describedby="desc-enable-notify-setting" class="" value="field" <?php checked("field", $enrollFormType, true); ?>><?php esc_html_e('Field','woocommerce-scheduler'); ?></label>
            <p class="description" id="desc-enable-notify-setting"><?php echo $description; ?></p>
            <?php
        }


        public function showNotifyEventOptions() {
            $NotifyEvent             = get_option('wdmws_notify_event');
            $description             = esc_html__('Select whether the user will be notified only once when the product becomes available for the first time or user will be notified on every availability of the product', 'woocommerce-scheduler');
            
            ?>
            <label><input type="radio" name="wdmws_notify_event" id="wdmws_guest_user_enrl_by_popup" aria-describedby="desc-event-notify-setting" class="" value="notify_once" <?php checked("notify_once", $NotifyEvent, true); ?>><?php esc_html_e('Once','woocommerce-scheduler'); ?></label>
            <label><input type="radio" name="wdmws_notify_event" id="wdmws_guest_user_enrl_by_field" aria-describedby="desc-event-notify-setting" class="" value="notify_every_avail" <?php checked("notify_every_avail", $NotifyEvent, true); ?>><?php esc_html_e('On Every Availability','woocommerce-scheduler'); ?></label>
            <p class="description" id="desc-event-notify-setting"><?php echo $description; ?></p>
            <?php
        }

        
        public function showNotifyEventPeriodOptions() {
            $NotifyPeriod            = get_option('wdmws_notify_user_period');
            $description             = esc_html__('Select when user should be notified about the product availability.', 'woocommerce-scheduler');
            ?>
            <label><input type="radio" name="wdmws_notify_user_period" id="wdmws_guest_user_enrl_by_popup" aria-describedby="desc-event-notify-period-setting" class="" value="product_avail" <?php checked("product_avail", $NotifyPeriod, true); ?>><?php esc_html_e('As soon as product becomes available','woocommerce-scheduler'); ?></label><br>

            <label><input type="radio" name="wdmws_notify_user_period" id="wdmws_guest_user_enrl_by_popup" aria-describedby="desc-event-notify-period-setting" class="" value="one_hr_before" <?php checked("one_hr_before", $NotifyPeriod, true); ?>><?php esc_html_e('1 hour before product becomes available','woocommerce-scheduler'); ?></label><br>

            <label><input type="radio" name="wdmws_notify_user_period" id="wdmws_guest_user_enrl_by_popup" aria-describedby="desc-event-notify-period-setting" class="" value="one_day_before" <?php checked("one_day_before", $NotifyPeriod, true); ?>><?php esc_html_e('1 day before product becomes available','woocommerce-scheduler'); ?></label><br>

            <label><input type="radio" name="wdmws_notify_user_period" id="wdmws_guest_user_enrl_by_popup" aria-describedby="desc-event-notify-period-setting" class="" value="one_week_before" <?php checked("one_week_before", $NotifyPeriod, true); ?>><?php esc_html_e('1 week before product becomes available','woocommerce-scheduler'); ?></label><br>

            <label><input type="radio" name="wdmws_notify_user_period" id="wdmws_guest_user_enrl_by_popup" aria-describedby="desc-event-notify-period-setting" class="" value="custom_hr" <?php checked("custom_hr", $NotifyPeriod, true); ?>><?php esc_html_e('Custom Hours','woocommerce-scheduler'); ?></label><br>
            
            <p class="description" id="desc-event-notify-period-setting"><?php echo $description; ?></p>
            <?php
        }

        
        public function showNotifyCustomHoursSetting() {
            $customHours    = get_option('wdmws_notify_custom_hr');
            $description    = esc_html__('Enter how many hours before the user will be notified', 'woocommerce-scheduler');
            ?>
            <input type="number" name="wdmws_notify_custom_hr" id="wdmws_notify_custom_hr" class="regular-text" value="<?php echo esc_attr($customHours); ?>" aria-describedby="desc-custom-time-setting">
            <p class="description" id="desc-custom-time-setting"><?php echo $description; ?></p>
            <?php
        }


        public function showUnsubscriptionPageSetting() {
            $selectedPage    = get_option('wdmws_unsubscription_page');
            $pagesData = get_pages(array('post_status' => 'publish'));
            foreach ($pagesData as $pageData) {
                $pages[$pageData->ID] = $pageData->post_title;
            } 
            unset($pageData);

            $description    = esc_html__('Page where the user will be redirected to get unsubscribed from the product notification list. If the unsubscription page is changed, then all the previous unsubscription link won\'t work as the page will get changed.', 'woocommerce-scheduler');
            ?>
            <select name="wdmws_unsubscription_page" id="wdmws_unsubscription_page" class="" aria-describedby="desc-unsub-page-setting">
            <?php 
                foreach ($pages as $pageId => $PageTitle) {
                    ?>
                    <option value="<?php echo esc_attr($pageId); ?>" <?php selected($selectedPage, $pageId); ?>>
                        <?php echo esc_html($PageTitle);?>
                    </option>
                    <?php
                }
            ?>
            </select>
            <p class="description" id="desc-unsub-page-setting"><?php echo $description; ?></p>
            <?php
        }


        public function showEnrollmentNotificationStatusCheckbox() {
            $featureEnabled             = get_option('wdmws_enable_send_email_enrl');
            $description                = esc_html__('Send email to customer after successful enroll message.', 'woocommerce-scheduler');
            ?>
            <input type="checkbox" name="wdmws_enable_send_email_enrl" id="wdmws_enable_send_email_enrl" aria-describedby="desc-enable-enroll-email" class="" value="1" <?php checked(1, $featureEnabled, true); ?>>
            <p class="description" id="desc-enable-enroll-email"><?php echo $description; ?></p>
            <?php
        }


        public function showEnrollmentMailSubject() {
            $enrolledMessageSubject = get_option('wdmws_enrl_email_sub');
            $description        = esc_html__('Subject that would be added in the email sent to the customer on enrollement, Available placeholder: [site_title]', 'woocommerce-scheduler');
            ?>
            <input type="text" name="wdmws_enrl_email_sub" id="wdmws_enrl_email_sub" class="regular-text" aria-describedby="desc-enrolled-message-subject" value="<?php echo esc_attr($enrolledMessageSubject); ?>">
            <p class="description"  id="desc-enrolled-message-subject"><?php echo $description; ?></p>
            <?php
        }


        public function showEnrollmentMailEditor() {
            $enrolledMessageBody = get_option('wdmws_enrl_email_body');
            $description        = esc_html__('Available placeholders: [site_title] | [unsubscribe_link] | [product_title] | [product_url] | [product_quantity] | [product_price] | [user_email] | [user_first_name] | [user_last_name] |  [user_display_name]', 'woocommerce-scheduler');
            wp_editor($enrolledMessageBody, 'wdmws_enrl_email_body', array('options'=>array('wpautop' => true,'quicktags' => true)));
            ?>
            <p class="description"  id="desc-enrolled-message-subject"><?php echo $description; ?></p>
            <input hidden id="reset-enrollment-email-template-nonce" value="<?php  esc_attr_e(wp_create_nonce('reset-enrollment-email-template-nonce')); ?>">
            <button type="button" id="reset-enrollment-email-template" class="reset-enrollment-email-template button button-primary" value=""><?php esc_html_e('Reset template', 'woocommerce-scheduler'); ?></button><span class="spinner"></span>
            <?php       
        }


        // Notification email templates
        public function showNotificationMailSubject() {
            $enrolledMessageSubject = get_option('wdmws_notification_email_sub');
            $description        = esc_html__('Subject that would be added in the email sent to the customer on enrollement, Available placeholder: [site_title]', 'woocommerce-scheduler');
            ?>
            <input type="text" name="wdmws_notification_email_sub" id="wdmws_notification_email_sub" class="regular-text" aria-describedby="desc-notify-message-subject" value="<?php echo esc_attr($enrolledMessageSubject); ?>">
            <p class="description"  id="desc-notify-message-subject"><?php echo $description; ?></p>
            <?php
        }


        public function showNotificationMailEditor() {
            $enrolledMessageBody = get_option('wdmws_notification_email_body');
            $description        = esc_html__('Available placeholders: [site_title] | [unsubscribe_link] | [product_title] | [product_url] | [product_quantity] | [product_price] | [user_email] | [user_first_name] | [user_last_name] |  [user_display_name]', 'woocommerce-scheduler');
            wp_editor($enrolledMessageBody, 'wdmws_notification_email_body', array('options'=>array('wpautop' => true,'quicktags' => true)));
            ?>
            <p class="description"  id="desc-notification-message-body"><?php echo $description; ?></p>
            <input hidden id="reset-notification-email-template-nonce" value="<?php  esc_attr_e(wp_create_nonce('reset-notification-email-template-nonce')); ?>">
            <button type="button" id="reset-notification-email-template" class="reset-notification-email-template button button-primary" value=""><?php esc_html_e('Reset template', 'woocommerce-scheduler'); ?></button><span class="spinner"></span>
            <?php
            
        }

        

        
        /*****************************************
         * Descriptions for the setting sections *
         *****************************************/
        public function featureStatusSectionDescription() {
            //esc_html_e('Click the checkbox below to enable or disable the notify me feature', 'woocommerce-scheduler');
        }

        public function notifyFeatureSettingsDescription() {
            ?>
            <div class="wdmws-notify-settings-section"></div>
            <?php
        }


        /*********************************
         * Custom Sanitization functions *
         *********************************/
        public function sanitizeCheckboxInput( $input) {
            if (\in_array($input, array('1',''))) {
                return $input;
            }
        return false;
        }

        public function sanitizeEnrollType( $input) {
            if (\in_array($input, array('popup','field'))) {
                return $input;
            }
        return false;
        }

        public function sanitizeEventType( $input) {
            if (\in_array($input, array('notify_once','notify_every_avail'))) {
                return $input;
            }
        return false;
        }

        public function sanitizeNotificationTimeOptions( $input) {
            if (\in_array($input, array('product_avail','one_hr_before', 'one_day_before', 'one_week_before', 'custom_hr'))) {
                return $input;
            }
        return false;
        }

        public function sanitizeCustomTimeHrs( $input) {
            if (is_numeric($input) && $input>0) {
                return (int)$input;
            }
        return false;
        }
    }
}