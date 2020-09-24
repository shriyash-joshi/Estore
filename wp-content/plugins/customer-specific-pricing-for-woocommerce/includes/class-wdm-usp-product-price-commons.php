<?php

namespace WuspSimpleProduct;

//use WuspGetData as cspGetData;

//check whether a class with the same name exists
if (! class_exists('WuspCSPProductPriceCommons')) {
	/**
	 * Class contains common variables and their getters & setters used in class WuspSCPProductPrice
	 */
	class WuspCSPProductPriceCommons {
	
		private $currentAddToCartId      ='';
		private $insideShopLoop          = false;
		public $skipCSPForProductsInCart = array();
		public $groupIdsForTheUser = array();
		public $cspSettings = array();
		private $uspCatCache	= array();
		private $rspCatCache	= array();
		private $gspCatCache	= array();

		
		/**
		 * Getter & setters for $currentAddToCartId
		 */
		public function getcurrentAddToCartId() {
			return $this->currentAddToCartId;
		}
		public function setcurrentAddToCartId( $productId) {
			$this->currentAddToCartId =$productId;
		}

		/**
		 * Getter & setters for $insideShopLoop
		 */
		public function setInsideShopLoopFlag() {
			$this->insideShopLoop = true;
		}

		public function unsetInsideShopLoopFlag() {
			$this->insideShopLoop = false;
		}

		public function getInsideShopLoopFlag() {
			return $this->insideShopLoop;
		}


		/**
		 * This function checks if the sent product variations id is on sale or not according to the following logic.
		 * 1. Product variation has sale price set and it is less than regular price.
		 * 2. Product variations current price is equal to the sale price (This happens only during the sale schedule).
		 *
		 * @since 4.2.4         - Created to check wether product variation is on sale while resolving bug #54282
		 * @param array $prices - Current,Regular,Sale prices of all the variations of the product
		 * @param int $vId      - id of the variation.
		 * @return bool true when variation is on sale.
		 */
		public function variationSaleEnabled( $prices, $vId) {
			$salePrice    =!empty($prices['sale_price'][$vId])?(float) $prices['sale_price'][$vId]:'';
			$regularPrice =!empty($prices['regular_price'][$vId])?(float) $prices['regular_price'][$vId]:'';
			if (''!=$salePrice && $salePrice<$regularPrice) {
				if ($prices['price'][$vId]==$salePrice) {
					return true;
				}
			}
			return false;
		}


		 /**
		 * Adds regular price before the pricing table(provided html) & returns the modified html.
		 *
		 * @since 4.3.0
		 * @param [type] $table
		 * @param [type] $regularPrice
		 * @param [type] $cspSettings
		 * @return void
		 */
		public function wdmAddRegularPriceNearPriceTable( $regularPrice, $cspSettings, $productId) {
			$regularPriceHtml ='';
			if (isset($cspSettings['show_regular_price']) && 'enable'==$cspSettings['show_regular_price'] && !empty($regularPrice)) {
				$regularPriceText =isset($cspSettings['regular_price_display_text'])?$cspSettings['regular_price_display_text']:'Regular Price';
				$regularPriceHtml ="<div class='qty-fieldset-regular-price'>$regularPriceText " . wc_price($regularPrice) . '</div>';
				$regularPriceHtml =apply_filters('wdm_csp_regular_price_html', $regularPriceHtml, $regularPriceText, $regularPrice, $productId);
			}
			return $regularPriceHtml;
		}

		/**
		 * This method returns the discription to be displayed on the product page
		 * if saved in the settings returns empty string if not available.
		 *
		 * @since 4.3.0
		 * @param [type] $cspSettings
		 * @param [type] $productId
		 * @return string $cspDiscriptionHtml
		 */
		public function wdmGetCSPDiscriptionIfAvailable( $cspSettings, $productId) {
			$cspDiscriptionHtml ='';
			if (!empty($cspSettings['csp_discription_text'])) {
				$cspDiscriptionHtml ="<div class='csp-discription-text'>" . $cspSettings['csp_discription_text'] . '</div>';
				$cspDiscriptionHtml =apply_filters('wdm_csp_discription_html', $cspDiscriptionHtml, $cspSettings['csp_discription_text'], $productId);
			}
			return $cspDiscriptionHtml;
		}


		/**
		 * Retrives CSP prices of the product for the user &
		 * returns the CSP for thespecified quantity for the user.
		 *
		 * @since 4.3.0
		 * @param [type] $productId
		 * @param [type] $price
		 * @param [type] $quantity
		 * @param [type] $userId
		 * @return void
		 */
		public function wdmGetCSPrice( $price, $productId, $quantity, $userId) {
			$price =WuspCSPProductPrice::getDBPrice($productId, $price, $quantity, $userId);
			return $price;
		}


		 /**
		 * This method is hooked to the 'woocommerce_cart_item_price' hook,
		 * which is used to update the price multipier in the woocommerce minicart
		 * (The cart displayed on hovering over the store cart icon)
		 *
		 * @param [type] $price
		 * @param [type] $cart_item
		 * @param [type] $cart_item_key
		 * @return void
		 */
		public function filterMiniCartPrice( $price, $cart_item, $cart_item_key) {
			$cart_product_id = !empty($cart_item['variation_id']) ? $cart_item['variation_id'] : $cart_item['product_id'];
			$price           = WuspCSPProductPrice::getDBPrice($cart_product_id, $cart_item['data']->get_price(), $cart_item['quantity']);
			unset($cart_item_key);
			return wc_price($price);
		}


		/**
		 * Fetches CSP settings array from the cache or from the database.
		 * & returns the same
		 * 
		 * @since 4.4.3
		 * @return void
		 */
		public function getCSPSettings() {
			if ( !empty($this->cspSettings)) {
				return $this->cspSettings;
			}
			$cspSettings  		= get_option('wdm_csp_settings');
			$this->cspSettings	= $cspSettings;
			return $cspSettings;
		}

		/**
		 * Returns the group ids to which the user belong
		 *
		 * @since 4.4.3
		 * @param int $userId
		 * @return array $groupIds
		 */
		public function getGroupIdsForTheUser( $userId) {
			$groupIds = array();
			if (!is_user_logged_in() || !defined('GROUPS_CORE_VERSION')) {
				return $groupIds;
			}
			if (!empty($this->groupIdsForTheUser[$userId])) {
				return $this->groupIdsForTheUser[$userId];
			}
			global $wpdb;
			$userGroupId        = $wpdb->get_results($wpdb->prepare('SELECT group_id FROM ' . $wpdb->prefix . 'groups_user_group WHERE user_id=%d', $userId));
			foreach ($userGroupId as $groupId) {
				$groupIds[] = $groupId->group_id;
			}
			$this->groupIdsForTheUser[$userId] = $groupIds;
			return $groupIds;
		}

		/**
		 * $prices is an array with prices associated with all the variation Ids
		 * This method replaces the prices for the variation ids present in the 
		 * arguement $cspPrices .
		 *  
		 * @since 4.4.3
		 * @param	array	$prices		- priceArray of variable product variations
		 * @param	array 	$cspPrices	- user/role/group specific product|category prices
		 * @return	array	$prices		- modified priceArray with prices for variations found in $cspPrices
		 */
		public function replaceWithCspPrices( $prices, $cspPrices) {
			if (!empty($cspPrices)) {
				foreach ($cspPrices as $vid => $price) {
					$prices[$vid]	= (string) $price;
				}
			}
			return $prices;
		}

		/**
		 * For each csp rule of a type (usp, rsp, gsp)
		 * * calculates the csp price according to the rule.
		 * * if calculated price is lesser than the previosly calculated price replaces the price
		 * * returns the filtered array of prices 
		 *
		 * @since	4.4.3
		 * @param	array $rules (USP|RSP|GSP)
		 * @param	array $prices
		 * @param	array $variantsToSkip
		 * @return	array - Prices calulated by CSP rules
		 */
		public function cspCalculatePrices( $rules, $prices, $variantsToSkip = array()) {
			$cspPrices = array();

			if (!empty($rules) && !empty($prices)) {
				foreach ($rules as $aRule) {
					$vid 			= $aRule->product_id;
					if (\in_array($vid, $variantsToSkip)) {
						continue;
					}
					$cspPrice		= $this->getCspFromRule( $aRule, $prices[$vid]);
					if (!empty($cspPrices[$vid])) {
						$cspPrices[$vid]=$cspPrices[$vid]<$cspPrice?$cspPrices[$vid]:$cspPrice;
					} else {
						$cspPrices[$vid]=$cspPrice;
					}
				}
			}
			return $cspPrices;
		}

		/**
		 * For each csp rule of a type (usp, rsp, gsp)
		 * * calculates the csp price according to the rule.
		 * * if calculated price is lesser than the previosly calculated price replaces the price
		 * * returns the filtered array of prices 
		 *
		 * @since 4.4.3
		 * @param array $rules (USP|RSP|GSP) - array or rule object
		 * @param array $prices
		 * @param array $variantsToSkip
		 * @return void
		 */
		public function cspCalculateCategoryPrices( $rules, $prices, $variantsToSkip = array()) {
			$cspPrices = array();
			if (!empty($rules) && !empty($prices)) {
				foreach ($prices as $vid => $price) {
					if (\in_array($vid, $variantsToSkip)) {
						continue;
					}
					foreach ($rules as $aRule) {
						$cspPrice		= $this->getCspFromRule( $aRule, $price);
						if (!empty($cspPrices[$vid])) {
							$cspPrices[$vid]=$cspPrices[$vid]<$cspPrice?$cspPrices[$vid]:$cspPrice;
						} else {
							$cspPrices[$vid]=$cspPrice;
						}	
					}
				}
			}
			return $cspPrices;
		}

		/**
		 * Calculates & returns the CSP price for the given
		 * CSP rule & price of the product
		 *
		 * @since	4.4.3
		 * @param	object	$rule
		 * @param	array	$price
		 * @return	float	- Calculated price according to the CSP rule
		 */
		public function getCspFromRule( $rule, $price) {
			if (2==$rule->flat_or_discount_price) {
				//% discount
				$discountValue		= $rule->price;
				$price = $price - ( $price * $discountValue/100 );
			} else {
				//flat price
				$price = $rule->price;
			}
			return $price;
		}


		/**
		 * Fetches & returns an array of user specific CSP rules for the current user
		 * & for specified variation ids for quantity one.
		 *
		 * @since 4.4.3
		 * @param	array	$productIds
		 * @param	array	$userId
		 * @return	array	- Role Specifc Pricing Rules For The Products Specified in $productIds
		 */
		public function getAllUspRulesForUser( $productIds, $userId) {
			global $wpdb;
			$uspRules = array();
			if (!empty($productIds)) {
				$prepareArgs = array_merge((array) $userId, (array) $productIds);	
				$uspRules = $wpdb->get_results($wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . 'wusp_user_pricing_mapping WHERE user_id = %d AND product_id IN(' . implode(', ', array_fill(0, count((array) $productIds), '%d')) . ') AND min_qty=1', $prepareArgs));
				$uspRules = apply_filters('wdm_csp_filter_usp_rules_for_a_product', $uspRules, $userId, $productIds);
			}
			return $uspRules;
		}

		/**
		 * Fetches & returns an array of role specific CSP rules for the current user
		 * & for specified variation ids for quantity one.
		 * 
		 * @since 4.4.3
		 * @param	array	$productIds
		 * @param	array	$roles
		 * @return	array	- Role Specifc Pricing Rules For The Products Specified in $productIds
		 */
		public function getAllRspRulesForUser( $productIds, $roles) {
			global $wpdb;
			$rspRules = array();
			if (!empty($productIds) && !empty($roles)) {
				$prepareArgs = array_merge((array) $roles, (array) $productIds);
				$rspRules = $wpdb->get_results($wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . 'wusp_role_pricing_mapping WHERE role IN (' . implode(', ', array_fill(0, count((array) $roles), '%s')) . ') AND product_id IN(' . implode(', ', array_fill(0, count((array) $productIds), '%d')) . ') AND min_qty=1', $prepareArgs));
				$rspRules	=  apply_filters('wdm_csp_filter_rsp_rules_for_a_product', $rspRules, $roles, $productIds);
			}
			return $rspRules;
		}

		/**
		 * Fetches & returns an array of group specific CSP rules for the current user
		 * & for specified variation ids for quantity one.
		 *
		 * @since 4.4.3
		 * @param	array	$productIds
		 * @param	array	$groupIds
		 * @return	array 	- Group Specific Pricing Rules For The Products Specified in $productIds
		 */
		public function getAllGspRulesForUser( $productIds, $groupIds) {
			global $wpdb;
			$gspRules = array();
			if (!empty($productIds) && !empty($groupIds)) {
				$prepareArgs= array_merge((array) $groupIds, (array) $productIds);	
				$gspRules 	= $wpdb->get_results($wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . 'wusp_group_product_price_mapping WHERE group_id IN(' . implode(', ', array_fill(0, count((array) $groupIds), '%d')) . ') AND product_id IN(' . implode(', ', array_fill(0, count((array) $productIds), '%d')) . ') AND min_qty=1', $prepareArgs));
				$gspRules	= apply_filters('wdm_csp_filter_gsp_rules_for_a_product', $gspRules, $groupIds, $productIds);
			}			
			return $gspRules;
		}



		/**********************************
		 * User Specific Category Pricing *
		 **********************************/
		/**
		 * Fetches & returns an array of user specific CSP rules for the current user
		 * & for specified product categories for quantity one.
		 *
		 * @since 4.4.3
		 * @param [type] $catSlugs
		 * @param [type] $userId
		 * @return void
		 */
		public function getAllUspCatRulesForUser( $catSlugs, $userId) {
			global $wpdb;
			$uspRules = array();
			if (!empty($catSlugs)) {
				$prepareArgs = array_merge((array) $userId, (array) $catSlugs);
				$uspRules		= $this->getCatCache('user', $prepareArgs);
				if (false===$uspRules) {
					$uspRules = $wpdb->get_results($wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . 'wcsp_user_category_pricing_mapping WHERE user_id = %d AND cat_slug IN(' . implode(', ', array_fill(0, count((array) $catSlugs), '%s')) . ') AND min_qty=1', $prepareArgs));
					$this->setCatCache( 'user', $prepareArgs, $uspRules);
				}	
			}
			return $uspRules;
		}


		/**********************************
		 * Role Specific Category Pricing *
		 **********************************/
		/**
		 * Fetches & returns an array of role specific CSP rules for the current user
		 * & for specified product categories for quantity one.
		 *
		 * @since 4.4.3
		 * @param [type] $catSlugs
		 * @param [type] $roles
		 * @return void
		 */
		public function getAllRspCatRulesForUser( $catSlugs, $roles) {
			global $wpdb;
			$rspRules = array();
			if (!empty($catSlugs) && !empty($roles)) {
				$prepareArgs = array_merge((array) $roles, (array) $catSlugs);
				$rspRules		= $this->getCatCache('role', $prepareArgs);
				if (false===$rspRules) {
					$rspRules = $wpdb->get_results($wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . 'wcsp_role_category_pricing_mapping WHERE role IN (' . implode(', ', array_fill(0, count((array) $roles), '%s')) . ') AND cat_slug IN(' . implode(', ', array_fill(0, count((array) $catSlugs), '%s')) . ') AND min_qty=1', $prepareArgs));	
					$this->setCatCache( 'role', $prepareArgs, $rspRules);
				}
			}
			return $rspRules;
		}


		/***********************************
		 * Group Specific Category Pricing *
		 ***********************************/
		/**
		 * Fetches & returns an array of group specific CSP rules for the current user
		 * & for specified product categories for quantity one.
		 *
		 * @since 4.4.3
		 * @param [type] $catSlugs
		 * @param [type] $groupIds
		 * @return void
		 */
		public function getAllGspCatRulesForUser( $catSlugs, $groupIds) {
			global $wpdb;
			$gspRules = array();
			if (!empty($catSlugs) && !empty($groupIds)) {
				$prepareArgs = array_merge((array) $groupIds, (array) $catSlugs);	
				$gspRules		= $this->getCatCache('group', $prepareArgs);
				if (false===$gspRules) {
					$gspRules    = $wpdb->get_results($wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . 'wcsp_group_category_pricing_mapping WHERE group_id IN(' . implode(', ', array_fill(0, count((array) $groupIds), '%d')) . ') AND cat_slug IN(' . implode(', ', array_fill(0, count((array) $catSlugs), '%s')) . ') AND min_qty=1', $prepareArgs));	
					$this->setCatCache( 'group', $prepareArgs, $gspRules);
				}
			}
			return $gspRules;
		}



		/****************************************************************
		 * Implementation of rule caching for category specific pricing *
		 ****************************************************************/
		/**
		 * This method is used to check if the rules for category
		 * are fethed previously & catched if the rules are cached
		 * then return the rules else returns false
		 *
		 * @param array $keyCandidates an array used to form the unique cache key, these are the cominations of unique items used to identify the rule
		 * @return mixed
		 */
		private function getCatCache( $ruleType, $keyCandidates) {
			sort($keyCandidates);
			$cacheKey = \sha1(implode('_', $keyCandidates));

			switch ($ruleType) {
				case 'user':
					if (isset($this->uspCatCache[$cacheKey])) {
						return $this->uspCatCache[$cacheKey];
					}
					break;
				case 'role':
					if (isset($this->rspCatCache[$cacheKey])) {
						return $this->rspCatCache[$cacheKey];
					}
					break;
				case 'group':
					if (isset($this->gspCatCache[$cacheKey])) {
						return $this->gspCatCache[$cacheKey];
					}
					break;
			}
			return false;
		}

		/**
		 * This method is used to save the rules for category
		 * in a cache
		 * 
		 * @param array $keyCandidates an array used to form the unique cache key, these are the cominations of unique items used to identify the rule
		 * @return void
		 */
		private function setCatCache( $ruleType, $keyCandidates, $rulesData) {
			sort($keyCandidates);	
			$cacheKey = \sha1(implode('_', $keyCandidates));
			switch ($ruleType) {
				case 'user':
						$this->uspCatCache[$cacheKey] = $rulesData;
					break;
				case 'role':
						$this->rspCatCache[$cacheKey] = $rulesData;
					break;
				case 'group':
						$this->gspCatCache[$cacheKey] = $rulesData;
					break;
			}
		}
	}
}
