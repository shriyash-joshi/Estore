<?php
if (!defined('EDD_WCSP_STORE_URL')) {
	define('EDD_WCSP_STORE_URL', 'https://wisdmlabs.com/check-license/');
}

if (!defined('EDD_WCSP_ITEM_NAME')) {
	define('EDD_WCSP_ITEM_NAME', 'Customer Specific Pricing for WooCommerce');
}

add_action('plugins_loaded', 'cspLoadLicense', 5);
add_action('plugins_loaded', 'cspUpdateCheck');
add_action('admin_notices', 'cspLicenseRenewalNotice', 1);



/**
 * Sets global variable of plugin data
 */
function cspLoadLicense() {
	global $wdmPluginDataCSP;
	$wdmPluginDataCSP = include_once 'license.config.php';
	require_once 'licensing/class-wdm-license.php';
	new \Licensing\WdmLicense($wdmPluginDataCSP);
}

/**
 * Update tables for CSP if plugin is updated
 *
 * @global array $wdmPluginDataCSP array of CSP Plugin data.
 */
function cspUpdateCheck() {
	global $wdmPluginDataCSP;

	$get_plugin_version = get_option($wdmPluginDataCSP['pluginSlug'] . '_version', false);

	if (false === $get_plugin_version || $get_plugin_version != $wdmPluginDataCSP['pluginVersion']) {
		 \WdmWuspInstall\WdmWuspInstall::createTables();
		 update_option('csp-update-status', 'updated_and_notice_undismissed');
		 update_option($wdmPluginDataCSP['pluginSlug'] . '_version', $wdmPluginDataCSP['pluginVersion']);
	}
}


if (!function_exists('getExpirationNoticeHtml')) {
	/**
	 * This function returns the html code for the license renewal notice
	 *
	 * @since 4.3.2
	 * @param string $renewLink - Website checkout link to process with the renewal
	 * @return void
	 */
	function getExpirationNoticeHtml( $renewLink = 'https://wisdmlabs.com/') {
		$notice             = '';
		$wisdmLogo          = plugin_dir_url(__FILE__) . 'images/wisdmlabs_logo.png';
		$renewalMessageHead = __('Your License For "Customer Specific Pricing" Has Expired', 'customer-specific-pricing-for-woocommerce');
		$renewalMessage     = __('renew your license today, to keep getting feature updates & premium support', 'customer-specific-pricing-for-woocommerce');
		$buttonText         = __('Renew License', 'customer-specific-pricing-for-woocommerce');
		$notice             = '<div class="csp-renewal-notice notice notice-error">
            <table class="csp-notice-structure">
                <tr>
                    <td class="csp-notice-image">
                    <img src=' . $wisdmLogo . '>
                    </td>
                    <td class="csp-renewal-notice-text">
                        <p>
                            <span class="csp-renewal-message-head">' . $renewalMessageHead . '</span>
                            <br>' . $renewalMessage . '</p>
                     </td>
                    <td class="csp-renewal-notice-button-div">
                        <a href=' . $renewLink . ' target="_blank">
                            <button id="btn-csp-renew" class="csp-renewal-notice-button">' . $buttonText . '</button>
                        </a>
                    </td>
                </tr>
            </table>
        </div>
        ';

		return $notice;
	}
}





/**
 * This function checks for the Licence validity of a plugin & displays a
 * notice with a renewal link for the expired license key
 *
 * @since 4.3.2
 * @return void
 */
if (!function_exists('cspLicenseRenewalNotice')) {
	function cspLicenseRenewalNotice() {
		global $wdmPluginDataCSP;
		if (empty($wdmPluginDataCSP['pluginSlug'])) {
			return;
		}
		$pluginSlug       = trim($wdmPluginDataCSP['pluginSlug']);
		$licenseTransient = 'wdm_' . $pluginSlug . '_license_trans';
		$licenseData      = get_option($licenseTransient, false);
	
		if (empty($licenseData['value']) || 'expired'!=trim(str_replace('"', '', $licenseData['value']))) {
			return;
		}
	
	
		$renewLink = get_option('wdm_' . $pluginSlug . '_product_site', false);
	
		wp_enqueue_style('csp-common-notice-styles');
		echo getExpirationNoticeHtml($renewLink);
	}
}
