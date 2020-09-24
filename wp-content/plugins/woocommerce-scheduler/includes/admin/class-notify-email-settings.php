<?php
namespace Includes\AdminSettings;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Notify Email Settings
 * @author WisdmLabs
 */
if (!class_exists('SchedulerNotifyEmailSettings')) {
    class SchedulerNotifyEmailSettings
    {
        /**
         * Display the email options (User Enrollement and Product Availability).
         */
        public function showNotificationEmailOptions()
        {
            $email_templates = $this->getEmails();

            ?>
            <tr valign="top">
            <td class="wdmws_emails_wrapper" colspan="2">
                <table class="wdmws_emails widefat" cellspacing="0">
                    <thead>
                        <tr>
                            <?php
                            $columns = apply_filters(
                                'wdmws_email_setting_columns',
                                array(
                                    // 'status'     => '',
                                    'name'       => __('Email', WDM_WOO_SCHED_TXT_DOMAIN),
                                    // 'email_type' => __( 'Content type', 'woocommerce' ),
                                    // 'recipient'  => __( 'Recipient(s)', 'woocommerce' ),
                                    'actions'    => '',
                                )
                            );
                            foreach ($columns as $key => $column) {
                                echo '<th class="wdmws-email-settings-table-'.esc_attr($key).'">'.esc_html($column).'</th>';
                            }
                            ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $url = admin_url('admin.php?page=wdmws_settings&tab=notification_settings&flow_page=notify_user_email&message=');
                            foreach ($email_templates as $email_key => $email) {
                                echo '<tr>';

                                foreach ($columns as $key => $column) {
                                    switch ($key) {
                                        case 'name':
                                            echo '<td class="wdmws-email-settings-table-'.esc_attr($key) . '">
                                            <a href="'.$url.strtolower($email_key).'">'.$email['title'].'</a>
                                            ' . wc_help_tip($email['description']) . '
                                        </td>';
                                            break;
                                        case 'actions':
                                            echo '<td class="wdmws-email-settings-table-' . esc_attr($key) . '">
                                            <a class="button alignright" href="'.$url.strtolower($email_key).'">' . esc_html__('Manage', WDM_WOO_SCHED_TXT_DOMAIN) . '</a>
                                        </td>';
                                            break;
                                        default:
                                            do_action('wdmws_email_setting_column_' . $key, $email);
                                            break;
                                    }
                                }

                                echo '</tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </td>
            </tr>
            <form action="options.php" method="post">
				<?php
				settings_fields('wdmws_notification_email_settings');
				do_settings_sections('wdmws_notification_email_settings');
				submit_button('Save Settings');
				?>
			</form>
            <?php
        }

        /**
         * Return the email types.
         *
         * @return array Return the array containing the email options/
         *               types.
         */
        public function getEmails()
        {
            $wdmwsSettings = \Includes\AdminSettings\WdmWsSettings::getSettings();
            $isEnrollementEmailEnabled = isset($wdmwsSettings['wdmws_enable_send_email_enrl']) && '1' == $wdmwsSettings['wdmws_enable_send_email_enrl'] ? 'yes' : 'no';

            $emailTemplates = array(
                'wdmws_user_enrollment_email' => array(
                    'id'    => 'user_enrollment',
                    'title' => __('User Enrollment', WDM_WOO_SCHED_TXT_DOMAIN),
                    'description' => __('This email would be sent when user enrolls for product availability notification.', WDM_WOO_SCHED_TXT_DOMAIN),
                    'enabled' => $isEnrollementEmailEnabled
                ),
                'wdmws_product_availability_email' => array(
                    'id'    => 'product_availability',
                    'title' => __('Product Availability', WDM_WOO_SCHED_TXT_DOMAIN),
                    'description' => __('This email would be sent to the customers when product becomes available.', WDM_WOO_SCHED_TXT_DOMAIN),
                    'enabled' => 'yes'
                )
            );
            return $emailTemplates;
        }

        /**
         * Display the settings for user enrollment email.
         */
        public function showUserEnrollmentEmailSettings()
        {
            $emailOptionsPage = admin_url().'admin.php?page=wdmws_settings&tab=notification_settings&flow_page=notify_user_email';
            $resetEnrollmentEmailBtn = '<br /><button type="button" id="reset-enrl-email-template" class="reset-enrl-email-template button button-primary" value="">'.__('Reset template', WDM_WOO_SCHED_TXT_DOMAIN).'</button><span class="spinner"></span>';
            ?>

            <h2 class="email-type-name">
                <?php echo __('Enrollment Email Settings', WDM_WOO_SCHED_TXT_DOMAIN); ?>
                <small class="wc-admin-breadcrumb">
                    <a href="<?php echo esc_url($emailOptionsPage); ?>">
                        <img draggable="false" class="emoji" alt="⤴" src="https://s.w.org/images/core/emoji/11/svg/2934.svg">
                    </a>
                </small>
            </h2>
            <form action="options.php" method="post">
				<?php
				settings_fields('wdmws_enrollment_email_template_settings');
				do_settings_sections('wdmws_enrollment_email_template_settings');
				submit_button('Save Settings');
				?>
			</form>
            <?php
        }

        /**
         * Display the settings for the product availability email.
         */
        public function showProductAvailabiltiyEmailSettings()
        {
            $emailOptionsPage = admin_url().'admin.php?page=wdmws_settings&tab=notification_settings&flow_page=notify_user_email';
            ?>
            <h2 class="email-type-name">
                <?php echo __('Notification Email Settings', WDM_WOO_SCHED_TXT_DOMAIN); ?>
                <small class="wc-admin-breadcrumb">
                    <a href="<?php echo esc_url($emailOptionsPage); ?>">
                        <img draggable="false" class="emoji" alt="⤴" src="https://s.w.org/images/core/emoji/11/svg/2934.svg">
                    </a>
                </small>
            </h2>

            <form action="options.php" method="post">
				<?php
				settings_fields('wdmws_notification_email_template_settings');
				do_settings_sections('wdmws_notification_email_template_settings');
				submit_button('Save Settings');
				?>
			</form>
        <?php
        }
    }
}
