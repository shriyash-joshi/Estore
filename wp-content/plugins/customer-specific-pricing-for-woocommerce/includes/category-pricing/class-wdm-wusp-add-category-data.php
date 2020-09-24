<?php

namespace cspCategoryPricing\addData;

if (!class_exists('WdmWuspAddCategoryData')) {
	/**
	* Inserts the category-specific pricing pairs in database.
	*/
	class WdmWuspAddCategoryData {
	
		private static $instance;
		public $errors;
		public $userPriceTable;
		public $rolePriceTable;
		public $groupPriceTable;

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
			$this->userPriceTable	= $wpdb->prefix . 'wcsp_user_category_pricing_mapping';
			$this->rolePriceTable	= $wpdb->prefix . 'wcsp_role_category_pricing_mapping';
			$this->groupPriceTable	= $wpdb->prefix . 'wcsp_group_category_pricing_mapping';
		}

		/**
		* Delete user-category-pricing pairs which are not in current
		* submission.
		* Add the user-category-pricing pairs in the database.
		 *
		* @param array $catArray category array for current selection in
		*  user-specific-pricing.
		* @param array $userIdsArray user-id array for current selection in * user-specific-pricing.
		* @param array $priceArray price of products array for current
		* selection in user-specific-pricing.
		* @param array $minQtyArray Min Quantity array for current selection * in user-specific-pricing.
		* @param array $discountTypeArray discount-type array for current
		* selection in user-specific-pricing.
		*/
		public function addUserCategoryRecords( $catArray, $userIdsArray, $priceArray, $minQtyArray, $discountTypeArray) {
			global $deleteCatRecords;
			include_once(CSP_PLUGIN_URL . '/includes/rules/wdm-csp-rule-customer.php');
			$UserCatQtyArray     = array();
			//$user_names          = '';

			//delete records
			$deleteCatRecords->removeUserCatQtyList($catArray, $userIdsArray, $minQtyArray);
			$wdmSavedRules=array();
			//Insert and Update records
			if (! empty($userIdsArray) && ! empty($minQtyArray) && ! empty($catArray)) {
				foreach ($userIdsArray as $index => $wdmWooUserId) {
					$UserCatQtyArray = $this->loopAddUserCatRecord($index, $wdmWooUserId, $catArray, $priceArray, $minQtyArray, $discountTypeArray, $UserCatQtyArray);
					$wdmSavedRules[]= new \rules\CustomerBasedRule($catArray[$index], $wdmWooUserId, $discountTypeArray[$index], $minQtyArray[$index], $priceArray[$index], 'category');
				}//foreach ends
				do_action('wdm_rules_saved', 'customer_specific_category_rules', $wdmSavedRules);
			}
		}


		/**
		* Prepare the user-category-quantity pairs array.
		* Add the current selection pricing pairs in the database.
		 *
		* @param int $index index in user-ids array of user-category-pricing.
		* @param int $wdmWooUserId user-id of the index.
		* @param array $catArray category array for current selection in
		*  user-specific-pricing.
		* @param array $priceArray price of products array for current
		* selection in user-specific-pricing.
		* @param array $minQtyArray Min Quantity array for current selection * in user-specific-pricing.
		* @param array $discountTypeArray discount-type array for current
		* selection in user-specific-pricing.
		* @param array $UserCatQtyArray array of pairs including
		* (user-id,category and min-quantity) empty at first.
		* @return array $UserCatQtyArray array of pairs including
		* (user-id,category and min-quantity)
		*/
		public function loopAddUserCatRecord( $index, $wdmWooUserId, $catArray, $priceArray, $minQtyArray, $discountTypeArray, $UserCatQtyArray) {
			global $wpdb;

			if (isset($wdmWooUserId) && '-1'!= $wdmWooUserId) {
				$userCatQtyPair = $wdmWooUserId . '-' . $catArray[ $index ] . '-' . $minQtyArray[ $index ];
				if (! in_array($userCatQtyPair, $UserCatQtyArray)) {
					array_push($UserCatQtyArray, $userCatQtyPair);
					$userId = '-1'!= $wdmWooUserId ? $wdmWooUserId : '';
					$qty = '-1'!=$minQtyArray[ $index ] ? $minQtyArray[ $index ] : '';
					$categorySlug = '-1' != $catArray[ $index ] ? $catArray[ $index ] : '';
					if (isset($priceArray[ $index ]) && isset($discountTypeArray[ $index ]) && '-1'!=$discountTypeArray[ $index ] && isset($qty) && !( $qty <= 0 )) {
						$pricing = wc_format_decimal($priceArray[ $index ]);
						$priceType = $discountTypeArray[ $index ];
						$this->addSingleUserRecord($userId, $pricing, $priceType, $wdmWooUserId, $qty, $categorySlug);
					}
					//If price is not set delete that record
					if (empty($pricing)) {
						$wpdb->delete(
							$this->userPriceTable,
							array(
							'user_id'       => $userId,
							'cat_slug' => $categorySlug,
							'min_qty'    => $qty,
							),
							array(
							'%d',
							'%s',
							'%d',
							)
						);
					}
				}
			}

			return $UserCatQtyArray;
		}

		/**
		* Adds single user-category pricing pair in database.
		* check if already such pricing pair exists if yes, update with new * values, otherwise insert the new record.
		 *
		* @param int $userId User-id (can be blank)
		* @param float $pricing Price of the product in the pair.
		* @param int $priceType 1 for flat 2 for % discount.
		* @param int $wdmWooUserId User-id (can be -1)
		* @param int $qty min-quantity for the current selection pair.
		* @param string $categorySlug category slug for current selection
		* pair.
		*/
		public function addSingleUserRecord( $userId, $pricing, $priceType, $wdmWooUserId, $qty, $categorySlug) {
			global $wpdb;
			if (! empty($userId) && ! empty($pricing) && ! empty($priceType)) {
				$result = $wpdb->get_results($wpdb->prepare('SELECT id FROM ' . $wpdb->prefix . 'wcsp_user_category_pricing_mapping WHERE user_id = %d and min_qty = %d and cat_slug=%s', $wdmWooUserId, $qty, $categorySlug));
				if (count($result) > 0) {
					$update_status = $wpdb->update($this->userPriceTable, array(
						'user_id'                   => $userId,
						'price'                  => $pricing,
						'flat_or_discount_price' => $priceType,
						'cat_slug'             => $categorySlug,
						'min_qty'                => $qty,
					), array( 'user_id' => $userId, 'cat_slug' => $categorySlug, 'min_qty' => $qty ));
				} else {
					$wpdb->insert($this->userPriceTable, array(
						'user_id'                => $userId,
						'price'                  => $pricing,
						'flat_or_discount_price' => $priceType,
						'cat_slug'             => $categorySlug,
						'min_qty'                => $qty,
					), array(
						'%d',
						'%s',
						'%d',
						'%s',
						'%d',
					));
				}
			}
		}


		/**
		* Delete role-category-pricing pairs which are not in current
		* submission.
		* Add the role-category-pricing pairs in the database.
		 *
		* @param array $catArray category array for current selection in
		*  user-specific-pricing.
		* @param array $rolesArray roles array for current selection in
		* role-specific-pricing.
		* @param array $priceArray price of products array for current
		* selection in role-specific-pricing.
		* @param array $minQtyArray Min Quantity array for current selection * in role-specific-pricing.
		* @param array $discountTypeArray discount-type array for current
		* selection in role-specific-pricing.
		*/
		public function addRoleCategoryRecords( $catArray, $rolesArray, $priceArray, $minQtyArray, $discountTypeArray) {
			global $deleteCatRecords;
			include_once(CSP_PLUGIN_URL . '/includes/rules/wdm-csp-rule-role.php');

			//delete records
			$deleteCatRecords->removeRoleCatQtyList($catArray, $rolesArray, $minQtyArray);
			$wdmSavedRules=array();

			$RoleCatQtyArray     = array();
			if (! empty($rolesArray) && ! empty($minQtyArray) && ! empty($catArray)) {
				foreach ($rolesArray as $index => $wdmRoleName) {
					$RoleCatQtyArray = $this->loopAddRoleCatRecord($index, $wdmRoleName, $catArray, $priceArray, $minQtyArray, $discountTypeArray, $RoleCatQtyArray);
					$wdmSavedRules[]= new \rules\RoleBasedRule($catArray[$index], $wdmRoleName, $discountTypeArray[$index], $minQtyArray[$index], $priceArray[$index], 'category');
				}//foreach ends
				do_action('wdm_rules_saved', 'role_specific_category_rules', $wdmSavedRules);
			}
		}

		/**
		* Prepare the role-category-quantity pairs array.
		* Add the current selection pricing pairs in the database.
		 *
		* @param int $index index in roles array of role-category-pricing.
		* @param int $wdmRoleName role of the index.
		* @param array $catArray category array for current selection in
		*  role-specific-pricing.
		* @param array $priceArray price of products array for current
		* selection in role-specific-pricing.
		* @param array $minQtyArray Min Quantity array for current selection * in role-specific-pricing.
		* @param array $discountTypeArray discount-type array for current
		* selection in role-specific-pricing.
		* @param array $RoleCatQtyArray array of pairs including
		* (role,category and min-quantity) empty at first.
		* @return array $RoleCatQtyArray array of pairs including
		* (role,category and min-quantity)
		*/
		public function loopAddRoleCatRecord( $index, $wdmRoleName, $catArray, $priceArray, $minQtyArray, $discountTypeArray, $RoleCatQtyArray) {
			global $wpdb;
			if (isset($wdmRoleName) &&  '-1'!=$wdmRoleName) {
				$roleCatQtyPair = $wdmRoleName . '-' . $catArray[ $index ] . '-' . $minQtyArray[ $index ];
				if (! in_array($roleCatQtyPair, $RoleCatQtyArray)) {
					array_push($RoleCatQtyArray, $roleCatQtyPair);
					$roleId = $wdmRoleName;
					$qty = '-1'!=$minQtyArray[ $index ]  ? $minQtyArray[ $index ] : '';
					$categorySlug =  '-1'!=$catArray[ $index ]  ? $catArray[ $index ] : '';
					if (isset($priceArray[ $index ]) && isset($discountTypeArray[ $index ]) &&  '-1'!=$discountTypeArray[ $index ]  && isset($qty) && !( $qty <= 0 )) {
						$pricing = wc_format_decimal($priceArray[ $index ]);
						$priceType = $discountTypeArray[ $index ];
						$this->addSingleRoleRecord($roleId, $pricing, $priceType, $wdmRoleName, $qty, $categorySlug);
					}
					if (empty($pricing)) {
						$wpdb->delete(
							$this->rolePriceTable,
							array(
							'role'       => $roleId,
							'cat_slug' => $categorySlug,
							'min_qty'    => $qty,
							),
							array(
							'%s',
							'%d',
							'%d',
							)
						);
					}
				}
				// $counter ++;
			}

			return $RoleCatQtyArray;
		}

		/**
		* Adds single role-category pricing pair in database.
		* check if already such pricing pair exists if yes, update with new * values, otherwise insert the new record.
		 *
		* @param int $roleId Role-id (can be blank)
		* @param float $pricing Price of the product in the pair.
		* @param int $priceType 1 for flat 2 for % discount.
		* @param int $wdmRoleName Role name
		* @param int $qty min-quantity for the current selection pair.
		* @param string $categorySlug category slug for current selection
		* pair.
		*/
		public function addSingleRoleRecord( $roleId, $pricing, $priceType, $wdmRoleName, $qty, $categorySlug) {
			global $wpdb;
			if (! empty($roleId) && ! empty($pricing) && ! empty($priceType)) {
				$result = $wpdb->get_results($wpdb->prepare('SELECT id FROM ' . $wpdb->prefix . 'wcsp_role_category_pricing_mapping WHERE role = %s and min_qty = %d and cat_slug=%s', $wdmRoleName, $qty, $categorySlug));
				if (count($result) > 0) {
					$wpdb->update($this->rolePriceTable, array(
						'role'                   => $roleId,
						'price'                  => $pricing,
						'flat_or_discount_price' => $priceType,
						'cat_slug'             => $categorySlug,
						'min_qty'                => $qty,
					), array( 'role' => $roleId, 'cat_slug' => $categorySlug, 'min_qty' => $qty ));
				} else {
					$wpdb->insert($this->rolePriceTable, array(
						'role'                   => $roleId,
						'price'                  => $pricing,
						'flat_or_discount_price' => $priceType,
						'cat_slug'             => $categorySlug,
						'min_qty'                => $qty,
					), array(
						'%s',
						'%s',
						'%d',
						'%s',
						'%d',
					));
				}
			}
		}

		/**
		* Delete group-category-pricing pairs which are not in current
		* submission.
		* Add the group-category-pricing pairs in the database.
		 *
		* @param array $catArray category array for current selection in
		*  group-specific-pricing.
		* @param array $groupIdsArray group-ids array for current selection
		* in group-specific-pricing.
		* @param array $priceArray price of products array for current
		* selection in group-specific-pricing.
		* @param array $minQtyArray Min Quantity array for current selection * in group-specific-pricing.
		* @param array $discountTypeArray discount-type array for current
		* selection in group-specific-pricing.
		*/
		public function addGroupCategoryRecords( $catArray, $groupIdsArray, $priceArray, $minQtyArray, $discountTypeArray) {
			global $deleteCatRecords;
			include_once(CSP_PLUGIN_URL . '/includes/rules/wdm-csp-rule-group.php');
			//delete records
			$deleteCatRecords->removeGroupCatQtyList($catArray, $groupIdsArray, $minQtyArray);
			$wdmSavedRules=array();

			$GroupCatQtyArray     = array();
			if (isset($groupIdsArray) && ! empty($minQtyArray) && ! empty($catArray)) {
				foreach ($groupIdsArray as $index => $wdmGroupId) {
					$GroupCatQtyArray = $this->loopAddGroupCatRecord($index, $wdmGroupId, $catArray, $priceArray, $minQtyArray, $discountTypeArray, $GroupCatQtyArray);
					$wdmSavedRules[]= new \rules\GroupBasedRule($catArray[$index], $wdmGroupId, $discountTypeArray[$index], $minQtyArray[$index], $priceArray[$index], 'category');
				}//foreach ends
				do_action('wdm_rules_saved', 'group_specific_category_rules', $wdmSavedRules);
			}
		}

		/**
		* Prepare the group-category-quantity pairs array.
		* Add the current selection pricing pairs in the database.
		 *
		* @param int $index index in group-ids array of
		* group-category-pricing.
		* @param int $wdmGroupId group-id of the index.
		* @param array $catArray category array for current selection in
		*  group-specific-pricing.
		* @param array $priceArray price of products array for current
		* selection in group-specific-pricing.
		* @param array $minQtyArray Min Quantity array for current selection * in group-specific-pricing.
		* @param array $discountTypeArray discount-type array for current
		* selection in group-specific-pricing.
		* @param array $GroupCatQtyArray array of pairs including
		* (group-id,category and min-quantity) empty at first.
		* @return array $GroupCatQtyArray array of pairs including
		* (group-id,category and min-quantity)
		*/
		public function loopAddGroupCatRecord( $index, $wdmGroupId, $catArray, $priceArray, $minQtyArray, $discountTypeArray, $GroupCatQtyArray) {
			global $wpdb;
			if (isset($wdmGroupId) && '-1'!=$wdmGroupId ) {
				$groupCatQtyPair = $wdmGroupId . '-' . $catArray[ $index ] . '-' . $minQtyArray[ $index ];
				if (! in_array($groupCatQtyPair, $GroupCatQtyArray)) {
					array_push($GroupCatQtyArray, $groupCatQtyPair);
					$groupId = $wdmGroupId;
					$qty = '-1'!= $minQtyArray[ $index ] ? $minQtyArray[ $index ] : '';
					$categorySlug = '-1'!= $catArray[ $index ]  ? $catArray[ $index ] : '';
					if (isset($priceArray[ $index ]) && isset($discountTypeArray[ $index ]) && isset($qty) && !( $qty <= 0 )) {
						$pricing = wc_format_decimal($priceArray[ $index ]);
						$priceType = $discountTypeArray[ $index ];
						$this->addSingleGroupRecord($groupId, $pricing, $priceType, $wdmGroupId, $qty, $categorySlug);
					}

					if (empty($pricing)) {
						$wpdb->delete(
							$this->groupPriceTable,
							array(
							'group_id'      => $groupId,
							'cat_slug'    => $categorySlug,
							'min_qty'       => $qty,
							),
							array(
							'%d',
							'%d',
							'%d',
							)
						);
					}
				}
			}

			return $GroupCatQtyArray;
		}

		/**
		* Adds single group-category pricing pair in database.
		* check if already such pricing pair exists if yes, update with new * values, otherwise insert the new record.
		 *
		* @param int $groupId Group-id (can be blank)
		* @param float $pricing Price of the product in the pair.
		* @param int $priceType 1 for flat 2 for % discount.
		* @param int $wdmGroupId Group-id
		* @param int $qty min-quantity for the current selection pair.
		* @param string $categorySlug category slug for current selection
		* pair.
		* @SuppressWarnings(PHPMD.UnusedLocalVariable)
		* @SuppressWarnings(PHPMD.UnusedFormalParameter)
		*/
		public function addSingleGroupRecord( $groupId, $pricing, $priceType, $wdmGroupId, $qty, $categorySlug) {
			global $wpdb;
			
			if (! empty($groupId) && ! empty($pricing) && ! empty($priceType)) {
				$result = $wpdb->get_results($wpdb->prepare('SELECT id FROM ' . $wpdb->prefix . 'wcsp_group_category_pricing_mapping WHERE group_id = %d and min_qty = %d and cat_slug=%s', $groupId, $qty, $categorySlug));
				if (count($result) > 0) {
					$update_status = $wpdb->update($this->groupPriceTable, array(
						'group_id'                   => $groupId,
						'price'                  => $pricing,
						'flat_or_discount_price' => $priceType,
						'cat_slug'             => $categorySlug,
						'min_qty'                => $qty,
					), array( 'group_id' => $groupId, 'cat_slug' => $categorySlug, 'min_qty' => $qty ));
				} else {
					$wpdb->insert($this->groupPriceTable, array(
						'group_id'                => $groupId,
						'price'                  => $pricing,
						'flat_or_discount_price' => $priceType,
						'cat_slug'             => $categorySlug,
						'min_qty'                => $qty,
					), array(
						'%d',
						'%s',
						'%d',
						'%s',
						'%d',
					));
				}
			}
		}
	}
}
$GLOBALS['addCatRecords'] = WdmWuspAddCategoryData::getInstance();
