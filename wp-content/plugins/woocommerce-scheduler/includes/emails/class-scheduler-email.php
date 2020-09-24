<?php

namespace Includes\Frontend;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

WC()->mailer();
/**
 * This class is used to send mail to customer.
 */
if (!class_exists('SchedulerEmail')) {
    class SchedulerEmail extends \WC_Email
    {
        protected $wdmwsSettings;
        protected $productId;

        public function __construct($type)
        {
            $this->wdmwsSettings = \Includes\AdminSettings\WdmWsSettings::getSettings();
            $this->email_type = 'text/html';
            $this->subject = $this->getMailSubject($this->wdmwsSettings);

            // Triggers for this email
            add_action("wdmws_send_{$type}_email", array($this,'trigger'), 15);

            parent::__construct();
        }

        /**
         * Replace placeholders in the email content with their values.
         *
         * @param string $emailContent Email Content.
         *
         * @return string Returns the email content after replacing placeholders.
         */
        public function replaceEmailPlaceholders($emailContent)
        {
            $productId = $this->productId;
            $userEmail = $this->recipient;
            $wdmwsSettings = $this->wdmwsSettings;
            $siteTitle = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
            $unsubscribeLink = wdmwsGetUnsubscribeLink($wdmwsSettings, $productId, $userEmail);
            if (!empty($unsubscribeLink)) {
                $unsubscribeLink = '<a href="'.$unsubscribeLink.'">'.__('unsubscribe', WDM_WOO_SCHED_TXT_DOMAIN).'</a>';
            }

            // product data
            $product = wc_get_product($productId);
            $productTitle = $product->get_title();
            $productUrl = $product->get_permalink();
            $productUrl = '<a href="'.$productUrl.'">'.$productTitle.'</a>';
            $productQty = $product->get_stock_quantity();
            $productPrice = $product->get_price();

            // user data
            $userData = get_user_by('email', $userEmail);

            if (false === $userData) {
                $userFirstName = $userLastName = $userDisplayName = $userEmail;
            } else {
                $userFirstName   = $userData->first_name;
                $userLastName    = $userData->last_name;
                $userDisplayName = $userData->display_name;
            }

            $findReplace = array(
                '[site_title]'          => $siteTitle,
                '[unsubscribe_link]'    => $unsubscribeLink,
                '[product_title]'       => $productTitle,
                '[product_url]'         => $productUrl,
                '[product_quantity]'    => $productQty,
                '[product_price]'       => $productPrice,
                '[user_email]'          => $userEmail,
                '[user_first_name]'     => $userFirstName,
                '[user_last_name]'      => $userLastName,
                '[user_display_name]'   => $userDisplayName
            );

            $findReplace = apply_filters('wdmws_email_placeholders_data', $findReplace, $productId, $userEmail);
            $emailContent = str_replace(array_keys($findReplace), array_values($findReplace), $emailContent);
            $emailContent = apply_filters('wdmws_email_content_after_replacing_placeholders', $emailContent, $productId, $userEmail);

            return $emailContent;
        }

        /**
         * Get headers for email.
         *
         * @return string email headers
         */
        public function getAdminHeaders()
        {
            $header = 'Reply-to: '.$this->getFromAddress().'\r\n';
            return apply_filters('wdmws_mail_headers', $header);
        }

        /**
         * Get the from name for outgoing emails.
         *
         * @return string email from name
         */
        public function getFromName()
        {
            $blogName = get_option('woocommerce_email_from_name');
            if (empty($blogName)) {
                $blogName = get_option('blogname');
            }
            $fromName = apply_filters('wdmws_email_from_name', $blogName);

            return wp_specialchars_decode(esc_html($fromName), ENT_QUOTES);
        }

        /**
         * Get the from address for outgoing emails.
         *
         * @return string address for mails
         */
        public function getFromAddress()
        {
            $fromAddress = get_option('woocommerce_email_from_address');
            if (empty($fromAddress)) {
                $fromAddress = get_option('admin_email');
            }
            $fromAddress = apply_filters('wdmws_email_from_address', $fromAddress);

            return sanitize_email($fromAddress);
        }
    }
}
