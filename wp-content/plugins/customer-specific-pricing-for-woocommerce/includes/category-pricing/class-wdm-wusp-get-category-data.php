<?php

namespace cspCategoryPricing\getData;

if (!class_exists('WdmWuspGetCategoryData')) {
	/**
	* Gets the category-specific pricing pairs from database.
	*/
	class WdmWuspGetCategoryData {
	
		private static $instance;
		private static $categoryCache = array();
		public $errors;
		public $userPriceTable;
		public $rolePriceTable;
		public $groupPriceTable;
		/**
		 * This variable will contain the status whether
		 * the category specific pricing feature is enabled or disabled
		 *
		 * @var bool - true|false
		 */
		public $featureActive;

		/**
		 * Returns the *Singleton* instance of this class.
		 *
		 * @return Singleton The *Singleton* instance.
		 */
		public static function getInstance() {
			if (null === static::$instance) {
				static::$instance = new static();
			}

			return static::$instance;
		}

		/**
		* Define table names for the category pricing mapping of user/role/ * group
		*/
		public function __construct() {
			global $wpdb;
			$this->userPriceTable  	= $wpdb->prefix . 'wcsp_user_category_pricing_mapping';
			$this->rolePriceTable  	= $wpdb->prefix . 'wcsp_role_category_pricing_mapping';
			$this->groupPriceTable 	= $wpdb->prefix . 'wcsp_group_category_pricing_mapping';
			$featureStatus			= get_option('cspCatPricingStatus', 'enable');
			$this->featureActive   	= 'enable'==$featureStatus?true:false;
		}

		/**
		* Checks if the User category mapping with the particular category exists or not.
		 *
		* @param string $catSlug category slug.
		* @return bool true if yes false if no.
		*/
		public function isUserCatPresent( $catSlug) {
			global $wpdb;
			$results = $wpdb->get_results($wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . 'wcsp_user_category_pricing_mapping WHERE cat_slug = %s', $catSlug));
			if (count($results) > 0) {
				return true;
			}
			return false;
		}

		/**
		* Checks if the Role category mapping with the particular category exists or not.
		 *
		* @param string $catSlug category slug.
		* @return bool true if yes false if no.
		*/
		public function isRoleCatPresent( $catSlug) {
			global $wpdb;
			$results = $wpdb->get_results($wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . 'wcsp_role_category_pricing_mapping WHERE cat_slug = %s', $catSlug));
			if (count($results) > 0) {
				return true;
			}
			return false;
		}

		/**
		* Checks if the Group category mapping with the particular category exists or not.
		 *
		* @param string $catSlug category slug.
		* @return bool true if yes false if no.
		*/
		public function isGroupCatPresent( $catSlug) {
			global $wpdb;
			$results = $wpdb->get_results($wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . 'wcsp_group_category_pricing_mapping WHERE cat_slug = %s', $catSlug));
			if (count($results) > 0) {
				return true;
			}
			return false;
		}

		/**
		* Gets the users category pricing pairs.
		* Get the category user pricing pairs form database.
		* Take the min-quantity of pair one by one.
		* If the price is previously set for that min_quantity,
		* Compare the new and old prices for that min quantity.
		* Apply the minimum category user price for that min quantity.
		* Sort the array of pricing on basis of keys.
		* Return the category user pricing pairs based on min_quantity.
		 *
		* @param int $currentUserId current User Id.
		* @param array $catslug category slugs array for the product.
		* @param int $productId Product Id.
		* @param array $extra empty at first.
		* @return array $catUserPrice User category pricing pairs based on
		* min-quantity.
		*/
		public function getUsersCategoryPricingPairs( $currentUserId, $catArray, $productId) {
			global $cspFunctions;
			static $catUserPrices = array();
			$dbRegPrice           =get_post_meta($productId, '_regular_price', true);
			$dbRegPrice           = apply_filters('wdm_csp_regular_price', $dbRegPrice, $productId);
			$isPriceEmpty         = empty($dbRegPrice);
			$regularPrice         = $cspFunctions->wdmGetCurrentPrice($productId);

			if (isset($catUserPrices[$currentUserId][$productId])) {
				return $catUserPrices[$currentUserId][$productId];
			}

			$userPrice           = array();
			$price               =array();
			$price               =$this->getCatUSPFromDatabase($currentUserId, $catArray);
			
			if (null==$price) {
				return;
			}
	
			for ($i = 0; $i < count($price); $i++) {
				$currentMinQty       = $price[$i]->min_qty;
				$category            =get_term_by('slug', $price[$i]->cat_slug, 'product_cat');
				$price[$i]->cat_slug =$category->name;
				//skip applying category based % discount to the product if regular price is not available
				if ($this->applyingPercentDiscountOnEmptyPrice($isPriceEmpty, $price[$i]->price_type)) {
					continue;
				}

				if (isset($userPrice[$currentMinQty])) {
					$newPrice = $price[$i]->price;
					
					if (2==$price[$i]->price_type) {
						$newPrice = ( $regularPrice ) - ( ( $newPrice * $regularPrice ) / 100 );
					}

					$oldPrice = $userPrice[$currentMinQty]->price;
					
					if (2==$userPrice[$currentMinQty]->price_type) {
						$oldPrice = ( $regularPrice ) - ( ( $oldPrice * $regularPrice ) / 100 );
					}

					if ($newPrice < $oldPrice) {
						$userPrice[$currentMinQty] = $price[$i];
					}
				} else {
					$userPrice[$currentMinQty] = $price[$i];
				}
			}
			ksort($userPrice);
			$catUserPrices[$currentUserId][$productId] = $userPrice;
			return $catUserPrices[$currentUserId][$productId];
		}


		/**
		 * Returns role specific category pricing rules from the WordPress transient
		 * if the transient is set else fetch it from the database and save it to the transient
		 * & return the rules. also save name of the transient to wp option to manage dele
		 *
		 * @since 2.4.5
		 * @param [type] $cspCatTransientName
		 * @param [type] $userId
		 * @param [type] $catSlug
		 * @return void
		 */
		public function getCatUSPFromDatabase( $currentUserId, $catArray) {
			global $wpdb;
			$price = array();
			$uspCacheKey	= 'cat_usp_' . $currentUserId . '_' . implode('_', $catArray);
			$price			= isset(self::$categoryCache[$uspCacheKey])?self::$categoryCache[$uspCacheKey]:'';
			if (!empty($price)) {
				return 'NO_CAT_USP_SET'!=$price?$price:array();
			}
			$prepareArgs = array_merge((array) $currentUserId, (array) $catArray);
			$price       = $wpdb->get_results($wpdb->prepare('SELECT price, min_qty,
            	                    cat_slug, flat_or_discount_price as price_type,
            	                    "set" as price_set 
            	                    FROM ' . $wpdb->prefix . 'wcsp_user_category_pricing_mapping WHERE
            	                    user_id = %s
            	                    AND
            	                    cat_slug IN (' . implode(', ', array_fill(0, count($catArray), '%s')) . ') 
									ORDER BY min_qty', $prepareArgs));
			self::$categoryCache[$uspCacheKey] = !empty($price)?$price:'NO_CAT_USP_SET';
			$price 	= ( 'NO_CAT_USP_SET'==$price )?array():$price;
			return $price;
		}



		public function applyingPercentDiscountOnEmptyPrice( $isPriceEmpty, $priceType) {
			return ( $isPriceEmpty && ( 2==$priceType ) );
		}

		/**
		* Gets the roles category pricing pairs.
		* Get the category role pricing pairs form database.
		* Take the min-quantity of pair one by one.
		* If the price is previously set for that min_quantity,
		* Compare the new and old prices for that min quantity.
		* Apply the minimum category role price for that min quantity.
		* Sort the array of pricing on basis of keys.
		* Return the category role pricing pairs based on min_quantity.
		 *
		* @param int $currentUserId current User Id.
		* @param array $catslug category slugs array for the product.
		* @param int $productId Product Id.
		* @param array $roleList empty at first.
		* @return array $catRolePrice Role category pricing pairs based on
		* min-quantity.
		*/
		public function getRolesCategoryPricingPairs( $currentUserId, $catArray, $productId, $roleList = array()) {
			global $cspFunctions;
			static $catRolePrices = array();

			$dbRegPrice   =get_post_meta($productId, '_regular_price', true);
			$dbRegPrice   = apply_filters('wdm_csp_regular_price', $dbRegPrice, $productId);
			$isPriceEmpty = empty($dbRegPrice);
			$regularPrice = $cspFunctions->wdmGetCurrentPrice($productId);

			if (isset($catRolePrices[$currentUserId][$productId])) {
				return $catRolePrices[$currentUserId][$productId];
			}

			$rolePrice = array();

			if (empty($roleList)) {
				$userInfo  = get_userdata($currentUserId);
				$userRoles = $userInfo->roles;
			} else {
				$userRoles = $roleList;
			}
			$price               = $this->getCatRSPFromDatabase($userRoles, $catArray);
			if (null==$price) {
				return;
			}

			for ($i = 0; $i < count($price); $i++) {
				$currentMinQty       = $price[$i]->min_qty;
				$category            =get_term_by('slug', $price[$i]->cat_slug, 'product_cat');
				$price[$i]->cat_slug =$category->name;
				if ($this->applyingPercentDiscountOnEmptyPrice($isPriceEmpty, $price[$i]->price_type)) {
					continue;
				}
				if (isset($rolePrice[$currentMinQty])) {
					$newPrice = $price[$i]->price;
					
					if (2==$price[$i]->price_type) {
						$newPrice = ( $regularPrice ) - ( ( $newPrice * $regularPrice ) / 100 );
					}

					$oldPrice = $rolePrice[$currentMinQty]->price;
					
					if (2==$rolePrice[$currentMinQty]->price_type) {
						$oldPrice = ( $regularPrice ) - ( ( $oldPrice * $regularPrice ) / 100 );
					}

					if ($newPrice < $oldPrice) {
						$rolePrice[$currentMinQty] = $price[$i];
					}
				} else {
					$rolePrice[$currentMinQty] = $price[$i];
				}
			}
			ksort($rolePrice);

			$catRolePrices[$currentUserId][$productId] = $rolePrice;
			return $catRolePrices[$currentUserId][$productId];
		}


		/**
		 * Returns role specific category pricing rules from the WordPress transient
		 * if the transient is set else fetch it from the database and save it to the transient
		 * & return the rules. also save name of the transient to wp option to manage dele
		 *
		 * @since 2.4.5
		 * @param [type] $cspCatTransientName
		 * @param [type] $userRole
		 * @param [type] $catSlug
		 * @return void
		 */
		public function getCatRSPFromDatabase( $userRoles, $catArray) {
			global $wpdb;
			$price = array();
			$rspCacheKey	= 'cat_rsp_' . implode('_', $userRoles) . '_' . implode('_', $catArray);
			$price			= isset(self::$categoryCache[$rspCacheKey])?self::$categoryCache[$rspCacheKey]:'';
			if (!empty($price)) {
				return 'NO_CAT_RSP_SET'!=$price?$price:array();
			}
			$prepareArgs = array_merge((array) $userRoles, (array) $catArray);
			$price       = $wpdb->get_results( $wpdb->prepare(
										'SELECT price, min_qty, cat_slug,
                                         flat_or_discount_price as price_type,
                                        "set" as price_set 
                                        FROM ' . $wpdb->prefix . 'wcsp_role_category_pricing_mapping WHERE
                                        role IN (' . implode(', ', array_fill(0, count($userRoles), '%s')) . ') 
                                        AND 
                                        cat_slug IN (' . implode(', ', array_fill(0, count($catArray), '%s')) . ')
										ORDER BY min_qty', $prepareArgs));
			self::$categoryCache[$rspCacheKey] = !empty($price)?$price:'NO_CAT_RSP_SET';
			$price 	= ( 'NO_CAT_RSP_SET'==$price )?array():$price;
			return $price;
		}

		/**
		* Gets the group-ids for the user.
		* For such group-ids get the group-category-pairs.
		* Get the category group pricing pairs form database.
		* Take the min-quantity of pair one by one.
		* If the price is previously set for that min_quantity,
		* Compare the new and old prices for that min quantity.
		* Apply the minimum category group price for that min quantity.
		* Sort the array of pricing on basis of keys.
		* Return the category group pricing pairs based on min_quantity.
		*
		* @param int $currentUserId current User Id.
		* @param array $catslug category slugs array for the product.
		* @param int $productId Product Id.
		* @param array $groupIds Group Ids for user.
		* @return array $catGroupPrice Group category pricing pairs based on
		* min-quantity.
		*/
		public function getGroupsCategoryPricingPairs( $currentUserId, $catArray, $productId, $groupIds = array()) {
			global $wpdb, $cspFunctions;
			static $catGroupPrices = array();
			$dbRegPrice            = get_post_meta($productId, '_regular_price', true);
			$dbRegPrice            = apply_filters('wdm_csp_regular_price', $dbRegPrice, $productId);

			$isPriceEmpty = empty($dbRegPrice);
			$regularPrice = $cspFunctions->wdmGetCurrentPrice($productId);
			if (isset($catGroupPrices[$currentUserId][$productId])) {
				return $catGroupPrices[$currentUserId][$productId];
			}
			
			//cache groups the user belongs to (going to be the same in a session)
			$groupIdCacheKey 	= 'csp_user_groups_' . $currentUserId;
			$userGroupId		= isset(self::$categoryCache[$groupIdCacheKey])?self::$categoryCache[$groupIdCacheKey]:'';
			if (empty($userGroupId)) {
				$userGroupId = $wpdb->get_results($wpdb->prepare('SELECT group_id FROM ' . $wpdb->prefix . 'groups_user_group WHERE user_id=%d', $currentUserId));	
				self::$categoryCache[$groupIdCacheKey] = !empty($userGroupId)?$userGroupId:'NOT_ADDED_TO_ANY_GROUP';
			}
			
			if (!empty($groupIds)) {
				$userGroupId = $wpdb->get_results($wpdb->prepare('SELECT group_id FROM ' . $wpdb->prefix . 'groups_user_group WHERE group_id IN (' . implode(', ', array_fill(0, count($groupIds), '%s')) . ')', $groupIds));
			}

			if ($userGroupId && 'NOT_ADDED_TO_ANY_GROUP'!=$userGroupId) {
				$dbRegPrice   = get_post_meta($productId, '_regular_price', true);
				$dbRegPrice   = apply_filters('wdm_csp_regular_price', $dbRegPrice, $productId);
				$regularPrice = floatval($dbRegPrice);
				$groupPrice = array();
				$groupIds	= array_column( (array) $userGroupId, 'group_id');
				$price = $this->getCatGSPFromDatabase($groupIds, $catArray);
				if (null!=$price) {
					for ($i = 0; $i < count($price); $i++) {
						$currentMinQty       = $price[$i]->min_qty;
						$category            = get_term_by('slug', $price[$i]->cat_slug, 'product_cat');
						$price[$i]->cat_slug = $category->name;
						//skip applying category based % discount to the product if regular price is not available
						if ($this->applyingPercentDiscountOnEmptyPrice($isPriceEmpty, $price[$i]->price_type)) {
							continue;
						}
						if (isset($groupPrice[$currentMinQty])) {
							$newPrice = $price[$i]->price;
							
							if (2==$price[$i]->price_type) {
								$newPrice = ( $regularPrice ) - ( ( $newPrice * $regularPrice ) / 100 );
							}

							$oldPrice = $groupPrice[$currentMinQty]->price;
							
							if (2==$groupPrice[$currentMinQty]->price_type) {
								$oldPrice = ( $regularPrice ) - ( ( $oldPrice * $regularPrice ) / 100 );
							}

							if ($newPrice < $oldPrice) {
								$groupPrice[$currentMinQty] = $price[$i];
							}
						} else {
							$groupPrice[$currentMinQty] = $price[$i];
						}
					}
					ksort($groupPrice);
					$catGroupPrices[$currentUserId][$productId] = $groupPrice;
					return $catGroupPrices[$currentUserId][$productId];
				}
			}
			
			$catGroupPrices[$currentUserId][$productId] = false;
			return $catGroupPrices[$currentUserId][$productId];
		}


		/**
		 * Returns group specific category pricing rules from the database.
		 * 
		 * @since 4.3.5
		 * @param array $userGroups
		 * @param array $catArray
		 * @return array
		 */
		private function getCatGSPFromDatabase( $userGroupIds, $catArray) {
			global $wpdb;
			$price = array();
			$gspCacheKey	= 'cat_gsp_' . implode('_', $userGroupIds) . '_' . implode('_', $catArray);
			$price			= isset(self::$categoryCache[$gspCacheKey])?self::$categoryCache[$gspCacheKey]:'';
			if (!empty($price)) {
				return 'NO_CAT_GSP_SET'!=$price?$price:array();
			}
			$prepareArgs = array_merge((array) $userGroupIds, (array) $catArray);
			$price       = $wpdb->get_results($wpdb->prepare('SELECT price, min_qty, cat_slug,
																	 flat_or_discount_price as price_type, 
																	 "set" as price_set FROM ' . $wpdb->prefix . 
																	 'wcsp_group_category_pricing_mapping WHERE 
																	 group_id IN (' . implode(', ', array_fill(0, count($userGroupIds), '%d')) . ') AND 
																	 cat_slug IN (' . implode(', ', array_fill(0, count($catArray), '%s')) . ')
																	ORDER BY min_qty', $prepareArgs));
			self::$categoryCache[$gspCacheKey] = !empty($price)?$price:'NO_CAT_GSP_SET';
			$price 	= ( 'NO_CAT_GSP_SET'==$price )?array():$price;
			return $price;
		}

		/**
		* Returns the user category specific pricing pairs from DB.
		 *
		* @return array $catUserPrices category user specific pairs
		*/
		public function getAllUserCategoryPricingPairs() {
			global $wpdb;
			static $catUserPrices = array();
			$catUserPrices        = $wpdb->get_results('SELECT cat_slug, user_id, price, min_qty, flat_or_discount_price as price_type FROM ' . $wpdb->prefix . 'wcsp_user_category_pricing_mapping');
			return $catUserPrices;
		}

		/**
		* Returns the category-user-quantity pairs from the DB.
		*/
		public function getCatUserQtyRecords() {
			global $wpdb;
			return $wpdb->get_results('SELECT user_id, cat_slug, min_qty FROM ' . $wpdb->prefix . 'wcsp_user_category_pricing_mapping', ARRAY_A);
		}

		/**
		* Returns the category-role-quantity pairs from the DB.
		 *
		* @param array $catArray category array for current selection in
		*  role-specific-pricing.
		* @param array $rolesArray roles array for current selection in
		* user-specific-pricing.
		* @param array $minQtyArray Min Quantity array for current selection * in role-specific-pricing.
		* @return array  category-role-quantity pairs from the DB.
		*/
		public function getCatRoleQtyRecords() {
			global $wpdb;
			return $wpdb->get_results('SELECT role, cat_slug, min_qty FROM ' . $wpdb->prefix . 'wcsp_role_category_pricing_mapping', ARRAY_A);
		}

		 /**
		* Returns the category-group-quantity pairs from the DB.
		  *
		* @param array $catArray category array for current selection in
		*  group-specific-pricing.
		* @param array $groupIdsArray group-ids array for current selection
		*  in group-specific-pricing.
		* @param array $minQtyArray Min Quantity array for current selection * in group-specific-pricing.
		* @return array  category-group-quantity pairs from the DB.
		*/
		public function getCatGroupQtyRecords() {
			global $wpdb;
			return $wpdb->get_results('SELECT group_id, cat_slug, min_qty FROM ' . $wpdb->prefix . 'wcsp_group_category_pricing_mapping', ARRAY_A);
		}

		/**
		* Returns the roles category specific pricing pairs from DB.
		 *
		* @return array $catRolePrices category role specific pairs
		*/
		public function getAllRolesCategoryPricingPairs() {
			global $wpdb;
			static $catRolePrices = array();
			$catRolePrices        = $wpdb->get_results('SELECT cat_slug, role, price, min_qty, flat_or_discount_price as price_type FROM ' . $wpdb->prefix . 'wcsp_role_category_pricing_mapping');
			return $catRolePrices;
		}

		/**
		* Returns the group category specific pricing pairs from DB.
		*
		* @return array $catGroupPrices category group specific pairs
		*/
		public function getAllGroupCategoryPricingPairs() {
			global $wpdb;
			static $catGroupPrices = array();
			
			$catGroupPrices = $wpdb->get_results('SELECT cat_slug, group_id, price, min_qty, flat_or_discount_price as price_type FROM ' . $wpdb->prefix . 'wcsp_group_category_pricing_mapping');
			return $catGroupPrices;
		}
		
		/**
		* Gets the products available in the particular category.
		*
		* @param string $catSlug category slug.
		* @return array $values products ids in category.
		*/
		public function getProductsOfCat( $catSlug) {
			global $wpdb;
			$catObj   = get_term_by('slug', trim($catSlug), 'product_cat');
			$catId    = $catObj->term_id;
			$products = $wpdb->get_col($wpdb->prepare('SELECT object_id as product_id FROM ' . $wpdb->prefix . 'term_relationships WHERE term_taxonomy_id = %s', $catId));
			$values   = array_values($products);
			if (is_array($values) && !empty($values)) {
				return $values;
			}
			return array();
		}

		/**
		* Gets the categories for available for that user in user category
		*  pricing.
		* Gets the quantity based pricing for that user.
		* Merge the user specific pricing and category based pricing for the * quantity specific pricing.
		 *
		* @param int $userId User Id.
		* @return array $mergedPrices Quantity Specific pricing array.
		*/
		public function getAllProductPricesByUser( $userId, $addKey = false) {
			if (! $this->featureActive) {
				return array();
			}
			$categories   = $this->getCategoriesForUser($userId);
			$cspPrices    = array();
			$catPrices    = array();
			$mergedPrices = array();

			$products = array();

			foreach ($categories as $value) {
				$products = array_unique(array_merge($products, $this->getProductsOfCat($value)));
			}

			$products = $this->getAllProducts($products);

			foreach ($products as $productId) {
				$cspPrices[$productId] = $this->getCSPPriceForProduct($userId, wc_get_product($productId), '\WdmCSP\WdmWuspGetData::getPriceOfProductForUser');
				
				$catPrices[$productId] = $this->getCatPricesForProduct($userId, wc_get_product($productId), 'getUsersCategoryPricingPairs');
				
				if ($addKey) {
					foreach ($catPrices[$productId] as $qty => $cspDetails) {
						$catPrices[$productId][$qty]['source'] .= '-wdm-csp-cat';
						$tmp                                    =$cspDetails;
					}
				}
				unset($tmp);
				$mergedPrice              = $this->mergeCatPrices($cspPrices[$productId], $catPrices[$productId]);
				$mergedPrices[$productId] = $mergedPrice;
			}

			return $mergedPrices;
		}

		/**
		* Merge the entity specific quantity pricing and the category entity * specific quantity pricing.
		* If there is an entry for both with same quantity entity based
		* pricing applied instead category based.
		 *
		* @param array $priceArray1 entity quantity specific pricing.
		* @param array $priceArray1 entity-category quantity specific
		* pricing.
		* @return array $cspPrices Specific Pricing.
		*/
		public function mergeCatPrices( $priceArray1, $priceArray2 = array()) {
			global $cspFunctions;
			$cspPrices = array();
			if (empty($priceArray1) && empty($priceArray2)) {
				return array();
			}

			$qtyArray1 = array_keys($priceArray1);
			$qtyArray2 = array_keys($priceArray2);

			$qtysArray = array_unique(array_merge($qtyArray1, $qtyArray2));

			foreach ($qtysArray as $qty) {
				if ($cspFunctions->hasQtyInPriceArray($qtyArray1, $qty)) {
					$cspPrices[$qty] = $priceArray1[$qty];
				} elseif ($cspFunctions->hasQtyInPriceArray($qtyArray2, $qty)) {
					$cspPrices[$qty] = $priceArray2[$qty];
				}
			}

			ksort($cspPrices);

			return $cspPrices;
		}
	
		/**
		* Gets the Product Ids or variation ids  for the product ids in the * array.
		 *
		* @param array $products Product Ids array.
		* @return array array of the Product and variation ids.
		*/
		public function getAllProducts( $products) {
			global $cspFunctions;
			$allProducts = array();
			foreach ($products as $key => $value) {
				$product = wc_get_product($value);
				if ($product && $product->get_type() == 'variable') {
					$allProducts = array_unique(array_merge($allProducts, $cspFunctions->getVariationId($product)));
				} else {
					array_push($allProducts, $value);
				}
				unset($key);
			}
			return $allProducts;
		}

		/**
		* Gets the Quantity based pricing array.
		 *
		* @param int $userId User Id.
		* @param object $product Product object.
		* @param string $function function to get the pricing mapping.
		* @param array $extra
		* @return array $cspPrices Quantity based pricing array.
		*/
		public function getCSPPriceForProduct( $userId, $product, $function, $extra = array()) {
			global $cspFunctions;
			$qtyList = array();

			if (!$product) {
				return array();
			}

			$cspPrices = $function($userId, $product->get_id(), $extra);
			if (( isset($cspPrices) && $cspPrices )) {
				$qtyList = $cspFunctions->getArrayColumn($cspPrices, 'min_qty');
			}

			if (!isset($qtyList) || count($qtyList) <= 0) {
				return $qtyList;
			}

			return $this->getQuantityPriceArray($product, $qtyList, $cspPrices);
		}

		/**
		* Gets the Category-Quantity based pricing array.
		 *
		* @param int $userId User Id.
		* @param object $product Product object.
		* @param string $function function to get the pricing mapping.
		* @param array $extra
		* @return array $catSpecificPrices category Quantity based pricing
		* array.
		*/
		public function getCatPricesForProduct( $userId, $product, $function, $extra = array()) {
			global $cspFunctions;
			$userId            = ( null===$userId ) ? get_current_user_id() : $userId;
			$catSpecificPrices = array();

			if ($product && ( $product->get_type() == 'simple' || $product->get_type() == 'variation' )) {
				$productCats = $cspFunctions->getProductCategories($product);
				$qtyList     = array();
				
				//The product does not belong to any category.
				if (!count($productCats)) {
					return false;
				}

				$catArray = $cspFunctions->getArrayColumn($productCats, 'slug');

				$CatPrices = $this->$function($userId, $catArray, $product->get_id(), $extra);
				
				if (( isset($CatPrices) && $CatPrices )) {
					$qtyList = $cspFunctions->getArrayColumn($CatPrices, 'min_qty');
				}

				if (!isset($qtyList) || count($qtyList) <= 0) {
					return $qtyList;
				}

				$catSpecificPrices = $this->getQuantityPriceArray($product, $qtyList, $CatPrices);
			}
			return $catSpecificPrices;
		}

		/**
		* Gets the Specific pricing for that product for that quantity.
		* Returns the specific prices for the min quantities in array.
		 *
		* @param object $product Product object.
		* @param array $qtyList Quantity (min) List.
		* @param array $priceArray1 quantity pricing mapping for product for * entity
		* @param array $priceArray2 quantity pricing mapping for product for * entity second
		* @param bool $direct accesed directly.
		* @return array $cspPrices Quantity based pricing array.
		*/
		public function getQuantityPriceArray( $product, $qtyList, $priceArray1, $priceArray2 = array(), $direct = 'false') {
			global $cspFunctions;
			$cspPrices = array();
			foreach ($qtyList as $qty) {
				if ($cspFunctions->hasQty($priceArray1, $qty)) {
					$cspPrices[$qty] = $cspFunctions->priceForSearchQuantity($qty, $priceArray1, $product);
				} elseif ($cspFunctions->hasQty($priceArray2, $qty)) {
					$cspPrices[$qty] = $cspFunctions->priceForSearchQuantity($qty, $priceArray2, $product);
				}
			}


			// Setting Price for Quantity 1
			if ('false'!=$direct && ( !isset($cspPrices) || count($cspPrices) == 0 || !isset($cspPrices[1]) )) {
				$cspPrices[1] = self::getProductPrice($product);
			}

			ksort($cspPrices);

			return $cspPrices;
		}

		/**
		* Gets the categories for available for that role in role category
		*  pricing.
		* Gets the quantity based pricing for that roles.
		* Merge the role specific pricing and category based pricing for
		* the quantity specific pricing.
		 *
		* @param array $role_list Roles.
		* @return array $mergedPrices Quantity Specific pricing array.
		*/
		public function getAllProductPricesByRoles( $role_list) {
			if (! $this->featureActive) {
				return array();
			}
			$categories   = $this->getCategoriesForRole($role_list);
			$cspPrices    = array();
			$catPrices    = array();
			$mergedPrices = array();
			// $categories = array_unique($cspFunctions->getArrayColumn($prices, 'cat_slug'));

			$products = array();

			foreach ($categories as $value) {
				$products = array_unique(array_merge($products, $this->getProductsOfCat($value)));
			}
			
			$products = $this->getAllProducts($products);

			foreach ($products as $productId) {
				$cspPrices[$productId] = $this->getCSPPriceForProduct(0, wc_get_product($productId), '\WuspSimpleProduct\WrspSimpleProduct\WdmWuspSimpleProductsRsp::getQtyPricePairsOfProductForRole', $role_list);
				
				$catPrices[$productId] = $this->getCatPricesForProduct(0, wc_get_product($productId), 'getRolesCategoryPricingPairs', $role_list);
				$mergedPrice           = $this->mergeCatPrices($cspPrices[$productId], $catPrices[$productId]);

				// Setting Price for Quantity 1
				// if (!isset($mergedPrice) || count($mergedPrice) == 0 || !isset($mergedPrice[1])) {
				//     $mergedPrice[1] = \WuspSimpleProduct\WuspCSPProductPrice::getProductPrice(wc_get_product($productId));
				// }

				$mergedPrices[$productId] = $mergedPrice;
			}
			return $mergedPrices;
		}

		/**
		* Gets the categories for available for that groups in group category
		*  pricing.
		* Gets the quantity based pricing for that group.
		* Merge the group specific pricing and category based pricing for
		* the quantity specific pricing.
		 *
		* @param array $groupIds Group Ids.
		* @return array $mergedPrices Quantity Specific pricing array.
		*/
		public function getAllProductPricesByGroups( $groupIds) {
			if (! $this->featureActive) {
				return array();
			}
			global $cspFunctions;
			/**
			 * Check if Groups is active
			 */
			if (!$cspFunctions->wdmIsActive('groups/groups.php')) {
				return array();
			}
			
			$categories   = $this->getCategoriesForGroup($groupIds);
			$cspPrices    = array();
			$catPrices    = array();
			$mergedPrices = array();
			// $categories = array_unique($cspFunctions->getArrayColumn($prices, 'cat_slug'));

			$products = array();

			foreach ($categories as $value) {
				$products = array_unique(array_merge($products, $this->getProductsOfCat($value)));
			}

			$products = $this->getAllProducts($products);

			foreach ($products as $productId) {
				$cspPrices[$productId] = $this->getCSPPriceForProduct(0, wc_get_product($productId), '\WdmCSP\WdmWuspGetData::getQtyPricePairsOfProductForGroup', $groupIds);
				
				$catPrices[$productId] = $this->getCatPricesForProduct(0, wc_get_product($productId), 'getGroupsCategoryPricingPairs', $groupIds);
				$mergedPrice           = $this->mergeCatPrices($cspPrices[$productId], $catPrices[$productId]);

				// Setting Price for Quantity 1
				// if (!isset($mergedPrice) || count($mergedPrice) == 0 || !isset($mergedPrice[1])) {
				//     $mergedPrice[1] = \WuspSimpleProduct\WuspCSPProductPrice::getProductPrice(wc_get_product($productId));
				// }

				$mergedPrices[$productId] = $mergedPrice;
			}
			return $mergedPrices;
		}



		/**
		* Gets the categories for that user in user category specific
		* pricing table.
		 *
		* @param int $userId User Id.
		* @return array $categories categories array.
		*/
		public function getCategoriesForUser( $userId) {
			global $wpdb;
			$categories = $wpdb->get_col($wpdb->prepare('SELECT cat_slug FROM ' . $wpdb->prefix . 'wcsp_user_category_pricing_mapping WHERE user_id = %s', $userId));
			return $categories;
		}

		 /**
		* Gets the categories for available for that groups in group category
		* pricing.
		  *
		* @param array $groupIds Group-ids
		* @return array array of actegories associated with the group-ids
		*/
		public function getCategoriesForGroup( $groupIds) {
			global $wpdb;
			$categories = $wpdb->get_col($wpdb->prepare('SELECT cat_slug FROM ' . $wpdb->prefix . 'wcsp_group_category_pricing_mapping WHERE group_id IN (' . implode(', ', array_fill(0, count($groupIds), '%d')) . ')', $groupIds));
			return $categories;
		}


		/**
		* Gets the categories for available for that roles in role category
		* pricing.
		 *
		* @param array $roles Roles array
		* @return array array of actegories associated with the group-ids
		*/
		public function getCategoriesForRole( $roles) {
			global $wpdb;
			$categories = $wpdb->get_col($wpdb->prepare('SELECT cat_slug FROM ' . $wpdb->prefix . 'wcsp_role_category_pricing_mapping WHERE role IN (' . implode(', ', array_fill(0, count($roles), '%s')) . ')', $roles));
			return $categories;
		}
	}
}
$GLOBALS['getCatRecords'] = WdmWuspGetCategoryData::getInstance();

