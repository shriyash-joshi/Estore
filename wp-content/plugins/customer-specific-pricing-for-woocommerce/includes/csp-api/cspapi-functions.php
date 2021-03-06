<?php

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

/**
 * Returns the Base Url of the plugin without trailing slash.
 *
 * @return string plugins' url
 */
function cspapiPluginUrl() {
	return untrailingslashit(plugins_url('/', __FILE__));
}

/**
 * Returns the Base dir of the plugin without trailing slash.
 *
 * @return string PEP Plugin directory.
 */
function cspapiPluginDir() {
	return untrailingslashit(plugin_dir_path(__FILE__));
}

/**
 * Returns the Base dir of the WooCommerce plugin without trailing slash.
 *
 * @return string Woocommerce directory
 */
function cspapiWcPluginDir() {
	return untrailingslashit(plugin_dir_path(dirname(__FILE__)) . 'woocommerce');
}


/**
 * Generate and return hash for API using product Id, customer entity, quantity,
 * discount type, price.
 *
 * @param  int      $productID      Product ID of product
 * @param  string   $entity         Customer|email|role|group
 *                                  need to be set.
 * @param  int      $qty            Quantity for pricing.
 * @param  string   $discountType   'flat' or '%'.
 * @param  float    $price          Price of the product.
 *
 * @return string   API hash value.
 */
function cspapiGenerateProductHashByEntity( $productID, $entity, $qty, $discountType, $price) {
	$productID    = apply_filters('cspapi_product_id_for_hash', (int) $productID);
	$entity       = apply_filters('cspapi_customer_entity_for_hash', $entity);
	$qty          = apply_filters('cspapi_quantity_for_hash', (int) $qty);
	$discountType = apply_filters('cspapi_product_id_for_hash', $discountType);
	$price        = apply_filters('cspapi_price_for_hash', (float) $price);

	return md5($productID . '|' . $entity . '|' . $qty . '|' . $discountType . '|' . $price);
}

function getUserIdByEmail( $email) {
	$user = get_user_by('email', $email);
	if (empty($user)) {
		return 0;
	}

	return $user->ID;
}

function getUserEmailById( $userid) {
	$user = get_userdata($userid);
	if (empty($user)) {
		return false;
	}

	return $user->user_email;
}

/**
 * Checks if provided product Id is valid.
 *
 * @param   int     $productId  Product Id.
 * @return  bool    True if valid, false otherwise.
 */
function cspapiIsValidProduct( $productId) {
	$productObject = wc_get_product($productId);
	if (false!==$productObject) {
		$productType = $productObject->get_type();
		if ( 'simple'==$productType ||  'variation'==$productType) {
			return true;
		}
	}
	return false;
}

/**
 * Check if hash received in request is valid.
 *
 * @param   string  $clientHash     Hash receieved in request.
 * @param   string  $serverHash     Hash generated by the server.
 * @param   array   $item           Customer pricing related data.
 *
 * @return  bool    True if hash is valid, false otherwise.
 */
function cspapiIsAPIHashValid( $clientHash, $serverHash, $item) {
	$isHashValid = false;

	if ($clientHash == $serverHash) {
		$isHashValid = true;
	}

	$isHashValid = apply_filters('cspapi_is_api_hash_valid', $isHashValid, $clientHash, $serverHash, $item);
	return $isHashValid;
}

/**
 * Check if subrule exists for given pair.
 *
 * @param    int    $role          USer ID|Group ID|Role Slug.
 * @param    int    $productId     Product Id.
 * @param    int    $minQty        Minimum quantity for customer-product
 *                                 pairing.
 *
 * @return    int|bool    Returns the rule Id if given pair exists in the table,
 *                        false otherwise.
 */
function cspapiIsSubruleExists( $entity, $productId, $minQty, $ruleType) {
	global $wpdb;
	$result = $wpdb->get_var($wpdb->prepare('SELECT rule_id FROM ' . $wpdb->prefix . 'wusp_subrules WHERE rule_type = %s AND associated_entity = %s AND product_id = %d AND min_qty = %d AND active = 1 ', $ruleType, $entity, $productId, $minQty));
	if (null == $result) {
		return false;
	}
	return true;
}


/**
 * Get CSP data.
 * It returns the default data if any data is missing.
 *
 * @param   array   $item   Array containing particular CSP data of a request.
 * @param   string  $key    Key which needs to retrieved.
 *
 * @return  mixed   Returns original CSP data if present. Otherwise returns
 *                  default value for provided key.
 */
function cspapiGetDefaultCspData( $item, $key) {
	$cspData = 0;
	switch ($key) {
		case 'product_id':
		case 'customer_id':
		case 'min_qty':
		case 'discount_type':
		case 'gsp_price':
		case 'rsp_price':
		case 'csp_price':
			$cspData = isset($item[$key]) ? $item[$key] : 0;
			break;
		case 'customer_email':
		case 'group_name':
		case 'role':
		case 'hash':
			$cspData = isset($item[$key]) ? $item[$key] : '';
			break;
	}

	return apply_filters('cspapi_default_csp_data_if_missing', $cspData, $item, $key);
}

function cspapiGetProductIdsCustomerDirect( $userId) {
	global $wpdb;
	$productIds = $wpdb->get_col($wpdb->prepare('SELECT DISTINCT product_id FROM ' . $wpdb->prefix . 'wusp_user_pricing_mapping WHERE user_id=%d', $userId));
	return $productIds;
}

function cspapiGetProductIdsRoleDirect( $role) {
	global $wpdb;
	$productIds = $wpdb->get_col($wpdb->prepare('SELECT DISTINCT product_id FROM ' . $wpdb->prefix . 'wusp_role_pricing_mapping WHERE role=%s', $role));
	return $productIds;
}


function getGroupIdsByUser( $userId) {
	global $wpdb;
	$user_groupid = $wpdb->get_results($wpdb->prepare('SELECT group_id FROM ' . $wpdb->prefix . 'groups_user_group WHERE user_id=%d', $userId));
	$group_ids = wp_list_pluck($user_groupid, 'group_id');
	return $group_ids;
}

function getGroupIdByName( $groupName) {
	if (empty($groupName)) {
		return 0;
	}
	global $wpdb;
	$user_groupid = $wpdb->get_results($wpdb->prepare('SELECT group_id FROM ' . $wpdb->prefix . 'groups_group WHERE name=%s', $groupName));
	$group_id = wp_list_pluck($user_groupid, 'group_id');
	$group_id = !empty($group_id) && is_array($group_id) ? $group_id[0] : 0;

	return $group_id;
}

function getGroupNameById( $groupId) {
	if (empty($groupId)) {
		return 0;
	}
	global $wpdb;
	$user_group_names = $wpdb->get_results($wpdb->prepare('SELECT name FROM ' . $wpdb->prefix . 'groups_group WHERE group_id=%d', $groupId));
	$groupName = wp_list_pluck($user_group_names, 'name');
	$groupName = !empty($groupName) && is_array($groupName) ? $groupName[0] : '';
	return $groupName;
}

function cspapiGetProductIdsGroupDirect( $userId) {
	global $wpdb;
	$group_ids = getGroupIds($userId);
	if (!empty($group_ids)) {
		$productIds = $wpdb->get_col($wpdb->prepare('SELECT DISTINCT product_id FROM ' . $wpdb->prefix . 'wusp_group_product_price_mapping WHERE group_id IN (' . implode(', ', array_fill(0, count($group_ids), '%d')) . ')', $group_ids));
	}
	return $productIds;
}

/**
 * Returns all product Ids for which pricing is set for a particular customer.
 * If argument is empty, this function uses the current logged in user data
 * to fetch the product Ids.
 * If user Id is 0 and user is not logged in, returns empty array.
 *
 * @param   int     $userId     User Id for whom product Ids needs to be
 *                              fetched.
 *
 * @return  array   Returns array containing the products Ids or empty array
 *                  if user Id is 0 and user is not logged in.
 */
function cspapiGetProductIdsForCustomer( $userId = 0) {
	$userId = empty($userId) ? get_current_user_id() : (int) $userId;

	if (empty($userId)) {
		return array();
	}

	global $getCatRecords;
	$userData             = get_userdata($userId);
	$userRole             = $userData->roles;
	$productIdsCustDirect = cspapiGetProductIdsCustomerDirect($userId);
	$productIdsRoleDirect = cspapiGetProductIdsRoleDirect($userData->roles);
	if (wdmGroupsPluginActive()) {
		$productIdsGroupDirect = cspapiGetProductIdsGroupDirect($userId);
	}
	$catsForCustomer = $getCatRecords->getCategoriesForUser($userId);
	$catsForRole     = $getCatRecords->getCategoriesForRole($userRole);
	$catsForGroup    = $getCatRecords->getCategoriesForGroup(getGroupIds($userId));
	$productCats     = array_unique(array_merge($catsForCustomer, $catsForRole, $catsForGroup));

	$args                  = array(
		'post_type' => 'product',
		'post_status' => 'publish',
		'fields' => 'ids',
		'tax_query' => array(
			array(
				'taxonomy' => 'product_cat',
				'field' => 'slug',
				'include_children' => false, // don't include child categories
				'terms' => $productCats
			)
		)
	);
	$productIdsForCategory = new WP_Query($args);
	$productIds            = array_unique(array_merge($productIdsCustDirect, $productIdsRoleDirect, $productIdsGroupDirect, $productIdsForCategory->posts));
	return $productIds;
}

/**
 * Checks if groups plugin active or active sitewide in case of multisite setup
 * returns true when active
 *
 * @return bool
 */
function wdmGroupsPluginActive() {
	return wdmIsActive('groups/groups.php');
}


/**
 * This method checks if plugin is activated on site
 *
 * @param [type] $plugin - Plugin Name or a slug to check if its activated on site
 * @return bool true if $plugin is activated on site or sitewide in multisite setup.
 */
function wdmIsActive( $plugin) {
	$arrayOfActivatedPlugins = apply_filters('active_plugins', get_option('active_plugins'));
	$wcActiveOnSite          =in_array($plugin, $arrayOfActivatedPlugins);
	$wcActiveSiteWide        =false;
	if (is_multisite()) {
		$arrayOfActivatedPlugins = get_site_option('active_sitewide_plugins');
		$wcActiveSiteWide        = array_key_exists($plugin, $arrayOfActivatedPlugins);
	}
	if ($wcActiveOnSite || $wcActiveSiteWide) {
		return true;
	}
	return false;
}

/**
 * This function checks whether the Ids provided in the array are valid product
 * Ids. If product Id belongs to a variation product, it replaces the variation
 * Id with its parent Id i.e. with variable product Id.
 *
 * @param   array   $productIds     Array containing product Ids which need to
 *                                  be verified.
 *
 * @return array    Array containing valid product Ids and variable product Ids
 *                  instead of variation product Ids. Empty array if none of
 *                  the Ids are valid product Id.
 */
function cspapiReturnValidProductIds( $productIds) {
	// containing valid product Ids.
	$validProductIds = array();

	foreach ($productIds as $productId) {
		$productObject = wc_get_product($productId);
		if (false!== $productObject) {
			$productType = $productObject->get_type();

			// If product type is simple, add it to the list of valid product Ids.
			if ('simple' == $productType) {
				$validProductIds[] = (int) $productId;
			} elseif ('variation' == $productType) {
				// If product type is variation, find its parent Id
				// (variable product Id) and replace variation Id with variable
				// product Id.
				$productId = $productObject->get_parent_id();
				if (!in_array($productId, $validProductIds)) {
					$validProductIds[] = $productId;
				}
			}
		}
	}
	return $validProductIds;
}


function role_exists( $role) {

	if (!empty($role)) {
		return $GLOBALS['wp_roles']->is_role($role);
	}
  
	return false;
}
