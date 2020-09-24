<?php
/**
 * Plugin Name: Customer Specific Pricing for WooCommerce
 * Plugin URI: https://wisdmlabs.com/woocommerce-user-specific-pricing-extension/
 * Description: Allows administrator to add customer specific pricing, role specific & group specific pricing for Simple & Variable WooCommerce Products.
 * Author: WisdmLabs
 * Version: 4.4.4
 * Text Domain: customer-specific-pricing-for-woocommerce
 * Author URI: https://wisdmlabs.com
 * License: GNU General Public License v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * WC requires at least: 3.0.0
 * WC tested up to: 4.0.1
 *  
 */

/*
  Copyright (C) 2015  WisdmLabs

  This program is free software; you can redistribute it and/or
  modify it under the terms of the GNU General Public License
  as published by the Free Software Foundation; either version 2
  of the License, or (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

if (!defined('ABSPATH')) {
	exit;
}// Exit if accessed directly

global $wdmPluginDataCSP;

// Define plugin constants
// PluginBaseName
if (!defined('CSP_PLUGIN_FILE')) {
	define('CSP_PLUGIN_FILE', __FILE__);
}

// Constant for text domain
if (!defined('CSP_PLUGIN_URL')) {
	define('CSP_PLUGIN_URL', untrailingslashit(plugin_dir_path(__FILE__)));
}

 
if (!defined('CSP_VERSION')) {
	define('CSP_VERSION', '4.4.4');
}

//get site url
$str      = get_home_url();
$site_url = preg_replace('#^https?://#', '', $str);


if (!defined('CSP_SITE_URL')) {
	define('CSP_SITE_URL', $site_url);
}

//CSP EPITROVE License Integration //Do not remove comment on this line

/**
* Loads plugin text-domain
*/
function cspTextInitFunc() {
	$cspSettings = get_option('wdm_csp_settings');
	if (isset($cspSettings['csp_api_status']) && 'enable' == $cspSettings['csp_api_status']) {
		include_once CSP_PLUGIN_URL . '/includes/csp-api/csp-api.php';	
	}
	load_plugin_textdomain('customer-specific-pricing-for-woocommerce', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}

$php_version = '';

//setting the php version of the system.
if (function_exists('phpversion')) {
	$php_version = phpversion();
} elseif (defined(PHP_VERSION)) {
	$php_version = PHP_VERSION;
}

// Including classes for handling pricing manager database tables
require_once CSP_PLUGIN_URL . '/includes/single-view/class-wdm-subrule-management.php';

require_once CSP_PLUGIN_URL . '/includes/single-view/class-wdm-rule-management.php';

//Including a class having common CSP functions
require_once CSP_PLUGIN_URL . '/includes/class-wdm-wusp-functions.php';

if (version_compare($php_version, '5.6.0') >= 0) {
	//Register common style scripts TODO : Register all usefull styles & scripts
	add_action('admin_enqueue_scripts', 'cspRegisterCommonStylesAndScripts');

	//Files that handles the functions for installation of CSP
	include CSP_PLUGIN_URL . '/includes/class-wdm-wusp-install.php';

	//Install Tables associated with User Specific Pricing plugin
	register_activation_hook(__FILE__, array('WdmWuspInstall\WdmWuspInstall', 'createTables'));
	add_action('upgrader_process_complete', 'wdmCSPAfterUpgrade', 99, 2);

	add_action('admin_notices', 'cspAdminAfterUpgradeMessage', 2);
	//add_action('admin_notices', 'cspLicenseRenewalNotice', 1);
	//change threshold for the rule display
	add_action('wp_ajax_nopriv_csp_cd_dismiss_update_notice', 'cspDismissUpdateNotice');
	add_action('wp_ajax_csp_cd_dismiss_update_notice', 'cspDismissUpdateNotice');

	global $cspFunctions;
	/**
	 * Check if WooCommerce is active
	 */

	if ($cspFunctions->wdmIsActive('woocommerce/woocommerce.php')) {
		include_once('wdm-license.php');//Do not remove comment on this line
		add_action('plugins_loaded', 'cspUpdateMeta');
		add_action('plugins_loaded', 'cspTextInitFunc');

		global $wdmPluginDataCSP;

		include_once CSP_PLUGIN_URL . '/includes/class-wdm-wusp-add-data-in-db.php';
		include_once CSP_PLUGIN_URL . '/includes/class-wdm-wusp-delete-data.php';
		include_once CSP_PLUGIN_URL . '/includes/class-wdm-wusp-update-data.php';

		new WdmCSP\WdmWuspAddDataInDB();
		new WdmCSP\WdmWuspUpdateDataInDB();


		//deleting records from wp_wusp_user_pricing_mapping tables if user doesn't exists(i.e if the user is deleted later.)
		add_action('delete_user', 'deletePricingPairsForUser');
		add_action('delete_post', 'deletePricingPairsForProduct');
		add_action('groups_deleted_group', 'deletePricingPairsForGroups', 10, 1);
		add_action('delete_term', 'deletePricingPairsForCategory', 10, 4);
		add_action('init', 'cspBootstrap');
	} else {
		add_action('admin_notices', 'cspBasePluginInactiveNotice');
	}
} else {
	add_action('admin_notices', 'cspPHPVersionNotice');
}

/*
 * Starts the functioning of CSP Plugin
 *
 *
 */
function cspBootstrap() {
	if (is_user_logged_in()) {
		//including file for the working of Customer Specific Pricing on Simple Products
		include_once CSP_PLUGIN_URL . '/includes/user-specific-pricing/class-wdm-wusp-simple-products-usp.php';
		new WuspSimpleProduct\WdmWuspSimpleProductsUsp();
		
		//including file for the working of Customer Specific Pricing on Variable Products
		include_once CSP_PLUGIN_URL . '/includes/user-specific-pricing/class-wdm-wusp-variable-products-usp.php';
		new WuspVariableProduct\WdmWuspVariableProductsUsp();

		//including file for the working of Group Based Pricing on Simple Products
		include_once CSP_PLUGIN_URL . '/includes/group-specific-pricing/class-wdm-wusp-simple-products-gsp.php';
		new WuspSimpleProduct\WgspSimpleProduct\WdmWuspSimpleProductsGsp();

		//including file for the working of Group Based Pricing on Variable Products
		include_once CSP_PLUGIN_URL . '/includes/group-specific-pricing/class-wdm-wusp-variable-products-gsp.php';
		new WuspVariableProduct\WgspVariableProduct\WdmWuspVariableProductsGsp();

		//including file for the working of Role Based Pricing on Simple Products
		include_once CSP_PLUGIN_URL . '/includes/role-specific-pricing/class-wdm-wusp-simple-products-rsp.php';
		new WuspSimpleProduct\WrspSimpleProduct\WdmWuspSimpleProductsRsp();

		//including file for the working of Role Based Pricing on Variable Products
		include_once CSP_PLUGIN_URL . '/includes/role-specific-pricing/class-wdm-wusp-variable-products-rsp.php';
		new WuspVariableProduct\WrspVariableProduct\WdmWuspVariableProductsRsp();

		//including file for the working of Customer Specific Price on Products
		include_once CSP_PLUGIN_URL . '/includes/class-wdm-usp-product-price-commons.php';
		include_once CSP_PLUGIN_URL . '/includes/class-wdm-apply-usp-product-price.php';
		new WuspSimpleProduct\WuspCSPProductPrice();
		
		
		//csp for order creation from backend
		include_once CSP_PLUGIN_URL . '/includes/dashboard-orders/class-wdm-customer-specific-pricing-new-order.php';
		new cspNewOrder\WdmCustomerSpecificPricingNewOrder();

		include_once CSP_PLUGIN_URL . '/includes/class-wdm-single-view-tabs.php';
		new SingleView\WdmShowTabs();

		include_once CSP_PLUGIN_URL . '/includes/class-wdm-wusp-ajax.php';
		new cspAjax\WdmWuspAjax();
	
		include_once CSP_PLUGIN_URL . '/includes/class-wdm-product-tables.php';
		new WdmCSP\WdmCspProductTables();
	}
	
	include_once CSP_PLUGIN_URL . '/includes/products-archive/class-product-archive.php';
	new CSPProductArchive\CustomerSpecificProductArchive();
	
	include_once CSP_PLUGIN_URL . '/includes/cart-discount/class-wdm-csp-cd-application.php';
	new CSPCartDiscount\WdmCSPCartDiscountApplication();
}


/**
* If PHP version is less than 5.6 give the admin notice.
*/
if (!function_exists('cspPHPVersionNotice')) {
	function cspPHPVersionNotice() {
		if (current_user_can('activate_plugins')) :
			global $php_version;
			?>
		<div id="message" class="error">
			<p>
			<?php 
				/* translators: %s: Plugin Name */
				printf(esc_html__('%1$s is inactive. requires PHP version 5.6 or greater', 'customer-specific-pricing-for-woocommerce'), 'Customer Specific Pricing for WooCommerce'); 
			?>

			<?php
			if (!empty($php_version)) {
				/* translators: %1$s:html %2$s:PHP Version %3$s:html*/
				printf(esc_html__(' ( Current PHP version is %1$s %2$s %3$s)', 'customer-specific-pricing-for-woocommerce'), '<strong>', esc_html__($php_version), '</strong>');
			} 
			?>
			</p>
		</div>
			<?php
		endif;
	}
}

/**
* If Woocommerce is not active give the admin notice.
*/
if (!function_exists('cspBasePluginInactiveNotice')) {
	function cspBasePluginInactiveNotice() {
		if (current_user_can('activate_plugins')) :
			//global $wdmPluginDataCSP;

			?>
		<div id="message" class="error">
			<p>
			<?php 
			/* translators: %1$s:html %2$s:PHP Version %3$s:html*/
			printf(esc_html__('%1$s %2$s is inactive.%3$s Install and activate %4$sWooCommerce%5$s for %6$s to work.', 'customer-specific-pricing-for-woocommerce'), '<strong>', 'Customer Specific Pricing for WooCommerce', '</strong>', '<a href="http://wordpress.org/extend/plugins/woocommerce/">', '</a>', 'Customer Specific Pricing for WooCommerce'); 
			?>
			</p>
		</div>
			<?php
		endif;
	}

}//if ends -- function exists

/**
*Function to delete records from wusp_user_pricing_mapping tables if the user is * deleted from users table in database.
 *
* @param int $userId User Id.
*/
if (!function_exists('deletePricingPairsForUser')) {
	function deletePricingPairsForUser( $userId) {
		\WuspDeleteData\WdmWuspDeleteData::deleteCustomerMapping('user_id', 'wusp_user_pricing_mapping', '%d', 'customer', $userId);
	}
}//if ends -- function exists

/**
* Function to delete records from pricing_mapping tables if the product
* is deleted from  database.
*
* @param int $productId Product Id.
*/
if (!function_exists('deletePricingPairsForProduct')) {
	function deletePricingPairsForProduct( $productId) {
		\WuspDeleteData\WdmWuspDeleteData::deleteMappingForProducts($productId);
	}
}//if ends -- function exists

/**
*Function to delete records from wusp_group_pricing_mapping tables if the group
* is deleted from  database.
 *
* @param int $groupId group Id.
*/
if (!function_exists('deletePricingPairsForGroups')) {
	function deletePricingPairsForGroups( $groupId) {
		\WuspDeleteData\WdmWuspDeleteData::deleteMappingForGroups($groupId);
	}
}//if ends -- function exists

/**
* Delete the entries of user/group/role category mapping from category_mappings table with * the category specified.
 *
* @param int     $term         Term ID.
* @param int     $tt_id        Term taxonomy ID.
* @param string  $taxonomy     Taxonomy slug.
* @param mixed   $deleted_term Copy of the already-deleted term, in the form specified
*                              by the parent function. WP_Error otherwise.
* @param array   $object_ids   List of term object IDs.
*/
if (!function_exists('deletePricingPairsForCategory')) {
	function deletePricingPairsForCategory( $term, $tt_id, $deleted_term, $object_ids) {
		global $getCatRecords, $deleteCatRecords;
		if (empty($object_ids) || null == $getCatRecords) {
			return;
		}

		if ($getCatRecords->isUserCatPresent($object_ids->slug)) {
			$deleteCatRecords->deleteUserCatEntries($object_ids->slug);
		}

		if ($getCatRecords->isRoleCatPresent($object_ids->slug)) {
			$deleteCatRecords->deleteRoleCatEntries($object_ids->slug);
		}

		if ($getCatRecords->isGroupCatPresent($object_ids->slug)) {
			$deleteCatRecords->deleteGroupCatEntries($object_ids->slug);
		}
		// \WuspDeleteData\WdmWuspDeleteData::deleteMappingForGroups($groupId);
		unset($term);
		unset($tt_id);
		unset($deleted_term);
	}
}//if ends -- function exists

function cspPrintDebug( $data) {
	echo '<pre>' . esc_html__(print_r($data, true)) . '</pre>';
}

function addBreakpoint() {
	$fileinfo  = 'no_file_info';
	$backtrace = debug_backtrace();
	if (!empty($backtrace[0]) && is_array($backtrace[0])) {
		$fileinfo = $backtrace[0]['file'] . ':' . $backtrace[0]['line'];
	}
	/* translators: $1$s:File Info */
	printf(esc_html__('Calling file info: %1$s \n', 'customer-scpecific-pricing-for-woocommerce'), esc_html__($fileinfo));
	exit;
}

/**
 * Function wdmCSPAfterUpgrade
 * Function is called everytime after WordPress plugin/theme upgader completes the upgrade process.
 * Use of this function here is to check if this plugin is upgraded by the upgraded & to manage migration
 * process for the plugin.
 *
 * @param [type] $upgraderObject contain details for the recently completed upgrade process
 * @param [type] $options - list of upgradable entities.
 * @return void
 * @version 2.3.1
 */
function wdmCSPAfterUpgrade( $upgraderObject, $options) {
	$cspPathName = plugin_basename(__FILE__);
	if ('update'==$options['action'] && 'plugin'==$options['type']) {
		foreach ($options['plugins'] as $pluginName) {
			if ($pluginName==$cspPathName) {
				update_option('csp-update-status', 'updated_and_notice_undismissed');
			}
		}
	}
	unset($upgraderObject);
}

/**
 * This method shows the dismissible notice on the admin page when,
 * * CSP gets updated to the latest version and
 * * this notice is not dismissed by the admin post update
 *
 * @since 4.3.0
 * @return void
 */
function cspAdminAfterUpgradeMessage() {
	global $pagenow;
	$whatsNewPageUrl = admin_url('admin.php?page=customer_specific_pricing_single_view&tabie=promotions_tab');
	$cspUpdateStatus = get_option('csp-update-status', 'not_updated');
	/* translators: v%s:Plugin Version Installed */
	$updateMessage  = sprintf(esc_html__('You have recently updated Customer Specific Pricing plugin to v%s. We have added some performance improvements', 'customer-specific-pricing-for-woocommerce'), CSP_VERSION);
	$whatsNewText   = __('Know more', 'customer-specific-pricing-for-woocommerce');
	$afterWhatsNew  = ' (' . __('link will open in a new tab', 'customer-specific-pricing-for-woocommerce') . ').';
	$dismissText    = __('Dismiss', 'customer-specific-pricing-for-woocommerce');
	$nonce          = wp_create_nonce('csp-update-nonce');
	$wisdmLogo      = plugin_dir_url(__FILE__) . 'images/wisdmlabs_logo.png';
	$onWhatsNewPage = false;
	if ('admin.php'==$pagenow) {
		if (isset($_GET['page']) && isset($_GET['tabie'])) {
			if ('customer_specific_pricing_single_view'==$_GET['page'] && 'promotions_tab'==$_GET['tabie']) {
				$onWhatsNewPage = true;
			}
		}
	}
	if ('updated_and_notice_undismissed'==$cspUpdateStatus && !$onWhatsNewPage) {
		wp_enqueue_style('csp-common-notice-styles');
		
		echo '<div class="csp-after-update-notice notice notice-info" data-nonce="' . esc_attr($nonce) . '">
         <table class="csp-notice-structure">
            <tr>
                <td class="csp-notice-image">
                <img src=' . esc_url($wisdmLogo) . '>
                </td>
                <td class="csp-update-notice-text">
                <p>' . esc_html__($updateMessage) . '
                    <br><a href="' . esc_url($whatsNewPageUrl) . '" target="_blank">' . esc_html__($whatsNewText) . '</a>'
					. esc_html__($afterWhatsNew) .
				'</p>
                </td>
                <td class="csp-update-notice-dismiss">
                    <span id="csp_dissmiss">' . esc_html__($dismissText) . '</span>
                </td>
            </tr>
         </table>
         </div>
         <script type="text/javascript">
         jQuery(document).ready(function(){jQuery("span#csp_dissmiss").click(function(){var e=jQuery(this).closest(".csp-after-update-notice").data("nonce");jQuery.ajax(ajaxurl,{type:"POST",data:{action:"csp_cd_dismiss_update_notice",nonce:e}}),jQuery(this).closest(".csp-after-update-notice").hide(200)})});
         </script>';
	}
}


/**
 * Ajax call back for dismissing the admin page ajax notice
 *
 * @since 4.3.0
 */
function cspDismissUpdateNotice() {
	if ( !empty( $_POST['nonce'] ) && wp_verify_nonce(sanitize_text_field($_POST['nonce']), 'csp-update-nonce')) {
		update_option('csp-update-status', 'update_notice_dismissed');
	}
}

if (!function_exists('cspRegisterCommonStylesAndScripts')) {
	function cspRegisterCommonStylesAndScripts() {
		global $wdmPluginDataCSP;
		$pluginVersion = !empty($wdmPluginDataCSP['pluginVersion'])?$wdmPluginDataCSP['pluginVersion']:'';
		wp_register_style('csp-common-notice-styles', plugins_url('css/csp-notices.css', __FILE__), array(), $pluginVersion);
	}
}

if (!function_exists('cspUpdateMeta')) {
	function cspUpdateMeta() {
		$cspMeta = get_option('wisdmCSPMeta', false);
		if (!$cspMeta) {
			$cspMeta = array(
				'siteURL'		=> CSP_SITE_URL,
				'cspVersion'	=> CSP_VERSION,
				'installTime'	=> time()
			);
			\WdmWuspInstall\WdmWuspInstall::createTables();
			update_option('wisdmCSPMeta', $cspMeta);
			update_option('csp-update-status', 'fresh_install');
		} elseif (CSP_VERSION!=$cspMeta['cspVersion']) {
			do_action('before_wisdm_csp_upgrade', $cspMeta, CSP_VERSION);
			$cspMeta['cspVersion']  = CSP_VERSION;
			$cspMeta['installTime'] = time();
			\WdmWuspInstall\WdmWuspInstall::createTables();
			update_option('wisdmCSPMeta', $cspMeta);
			do_action('after_wisdm_csp_upgrade', $cspMeta, CSP_VERSION);
			update_option('csp-update-status', 'updated_and_notice_undismissed');
		}
	}
}
