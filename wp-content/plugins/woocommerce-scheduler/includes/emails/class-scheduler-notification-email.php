<?php

namespace Includes\Frontend;

include_once 'class-scheduler-email.php';
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * This class is used to send mail to customer about the product notification.
 */
if (!class_exists('SchedulerNotificationEmail')) {
    class SchedulerNotificationEmail extends SchedulerEmail
    {
        private static $instance;
    
        /**
        * This function returns the Singleton emailObject.
        */
        public static function getInstance()
        {
            if (null === static::$instance) {
                static::$instance = new static();
            }
    
            return static::$instance;
        }

        public function __construct()//, $productId, $userEmail)
        {
            parent::__construct('notification');//, $productId, $userEmail, 'notification');
        }

        /**
         * Returns default template HTML code.
         *
         * @return string Default Notification Email template HTML.
         */
        public static function returnDefaultNotificationEmailTemplate()
        {
            ob_start();
            ?>
            <div id="wrapper">
                <table id="template_container" border="0" width="90%" cellspacing="0" cellpadding="0">
                    <tbody>
                        <tr>
                            <th id="head">
                                <h1 style="color: white; margin: 0; padding: 28px 24px; text-shadow: 0 1px 0 0; display: block; font-family: Arial; font-size: 30px; font-weight: bold; text-align: left; line-height: 150%;">You have been successfully&nbsp;enrolled.</h1>
                            </th>
                        </tr>
                        <tr>
                            <td style="padding: 20px; border-radius: 6px !important;" align="center" valign="top">
                                <div id="content">
                                    <div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">Hi [user_first_name]</div>
                                    <div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;"></div>
                                    <div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">The product "[product_url]" is available on " [site_title] ".&nbsp;</div>
                                    <div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;"></div>
                                    <div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">Happy Shopping <h3>[site_title]</h3></div>
                                    <div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;"></div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: center; border-top: 0; -webkit-border-radius: 6px;" align="center" valign="top">
                                <div id="template_footer">
                                    <p style="font-family: Arial;">
                                        [unsubscribe_link]
                                    </p>
                                    <?php echo wpautop(wp_kses_post(wptexturize(apply_filters('woocommerce_email_footer_text', get_option('woocommerce_email_footer_text')))));
                                    ?>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <?php
            return ob_get_clean();
        }

        /**
         * Returns CSS which will be applied to the default notification email template.
         *
         * @return string Default CSS to be applied on the email template.
         */
        public static function returnDefaultNotificationEmailTemplateCSS()
        {
            $bgColor         = get_option('woocommerce_email_background_color');
            $body            = get_option('woocommerce_email_body_background_color');
            $base            = get_option('woocommerce_email_base_color');
            $base_text       = wc_light_or_dark($base, '#202020', '#ffffff');
            $text            = get_option('woocommerce_email_text_color');
            $base_lighter_40 = wc_hex_lighter($base, 40);

            // Pick a contrasting color for links.
            $link = wc_hex_is_light($base) ? $base : $base_text;
            if (wc_hex_is_light($body)) {
                $link = wc_hex_is_light($base) ? $base_text : $base;
            }

            ob_start();
            ?>
            #wrapper {
                background-color: <?php echo esc_attr($bgColor); ?>;
                width: 100%;
                -webkit-text-size-adjust: none !important;
                margin: 0;
                padding: 60px 0;
            }

            #template_container {
                background-color: <?php echo esc_attr($body); ?>;
                border-radius: 6px !important;
                box-shadow: 0 0 0 3px rgba(0,0,0,0.025) !important;
                margin: auto;
                padding-bottom: 20px;
            }

            #head {
                background-color: <?php echo esc_attr($base); ?>;
                border-top-left-radius: 6px !important;
                border-top-right-radius: 6px !important;
                border-bottom: 0;
                font-family: Arial;
                font-weight: bold;
                line-height: 100%;
                vertical-align: middle;
            }

            #template_container td {
                background-color: <?php echo esc_attr($body); ?>;
            }

            #content {
                color: <?php echo esc_attr($text); ?>;                
            }

            #content h1, 
            #content h2,
            #content h3,
            #content h4,
            #content h5,
            #content h6 {
                color: <?php echo esc_attr($base); ?>;
            }

            a {
                color: <?php echo esc_attr($link); ?>;
                font-weight: normal;
                text-decoration: underline;
            }

            #template_footer {
                color: <?php echo esc_attr($base_lighter_40); ?>;
            }
            <?php
            return ob_get_clean();
        }

        /**
         * Returns default email template's HTML markup with CSS.
         *
         * @return string Returns email template with the CSS.
         */
        public static function defaultEmailTemplateWithCSS()
        {
            $content = SchedulerNotificationEmail::returnDefaultNotificationEmailTemplate();
            $css = SchedulerNotificationEmail::returnDefaultNotificationEmailTemplateCSS();

            if (! class_exists('Emogrifier') && class_exists('DOMDocument') && (version_compare(WC_VERSION, '4.0.0')<0)) {
                include_once dirname(WC_PLUGIN_FILE).'/includes/libraries/class-emogrifier.php';
            }

            // apply CSS styles inline
            try {
                $emogrifier = new \Pelago\Emogrifier($content, $css);
                $content    = $emogrifier->emogrify();
            } catch (Exception $e) {
                $logger = wc_get_logger();
                $logger->error($e->getMessage(), array('source' => 'emogrifier'));
            }

            return wp_specialchars_decode($content, ENT_QUOTES);
        }

        /**
         * Gets the content in HTML for the mail to be sent.
         * @return HTML content of email
         */
        // @codingStandardsIgnoreLine
        public function get_content_html()
        {
            $content = isset($this->wdmwsSettings['wdmws_notification_email_body']) ? $this->wdmwsSettings['wdmws_notification_email_body'] : SchedulerNotificationEmail::defaultEmailTemplateWithCSS();

            $content = $this->replaceEmailPlaceholders($content);
            return wp_specialchars_decode($content, ENT_QUOTES);
        }

        /**
         * Sends the email with the data to specific recipient.
         */
        public function trigger()
        {
            $this->send($this->recipient, $this->subject, $this->get_content(), $this->getAdminHeaders(), $this->get_attachments());
        }

        /**
         * This function is used to get notification email subject.
         *
         * @param string $wdmwsSettings Scheduler settings.
         *
         * @return string Subject for notification email.
         */
        public function getMailSubject($wdmwsSettings)
        {
            $subject = isset($wdmwsSettings['wdmws_notification_email_sub']) ? $wdmwsSettings['wdmws_notification_email_sub'] : __('Product Notification from [site_title]', WDM_WOO_SCHED_TXT_DOMAIN);

            $siteTitle = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
            $subject = str_replace('[site_title]', $siteTitle, $subject);

            return apply_filters('wdmws_notification_email_subject', $subject);
        }

        /**
         * Sets the product ID and recipient data.
         *
         * @param int       $productId    Product ID.
         * @param string    $userEmail    User email address.
         */
        public function prepareData($productId, $userEmail)
        {
            $this->productId = $productId;
            $this->recipient = $userEmail;
        }
    }
}
