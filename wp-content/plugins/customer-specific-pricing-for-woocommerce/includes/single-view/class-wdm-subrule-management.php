<?php

namespace cspSingleView;

if (! class_exists('WdmSubruleManagement')) {

	/**
	* Class that includes functions for subrule management.
	*/

	class WdmSubruleManagement {
	

		public $subruleTable;

		/**
		 * The reference to *Singleton* instance of this class
		 * 
		 * @var Singleton The reference to *Singleton* instance of this class
		 */
		private static $instance;
		public $errors;

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
		 * Protected constructor to prevent creating a new instance of the
		 * *Singleton* via the `new` operator from outside of this class.
		 * Gives the name for User specific subrules table, according to individual product.
		 */
		protected function __construct() {
			global $wpdb;
			$this->subruleTable = $wpdb->prefix . 'wusp_subrules';

		}

		/**
		* Appends the current message to error message string.
		 *
		* @param string $message error message
		*/
		private function addError( $message) {
			$this->errors .= $message;
		}

		/**
		* Add the subrule for the product pricing specified.
		 *
		* @param string $sruleType rule-type.
		* @param int $ruleId Rule id.
		* @param int $product_id Product Id.
		* @param float $price Price of Product.
		* @param int $flatOrDiscountPrice 2 for % and 1 for flat.
		* @param int  $associationEntity associated entity id
		* @return bool true if successfully updated otherwise false.
		*/
		public function addSubrule( $ruleId, $productId, $quantity, $flatOrDiscountPrice, $price, $ruleType, $associationEntity) {
			$price = wc_format_decimal($price);
			if ($price < 0) {
				/* translators: %s productID */
				$this->addError(sprintf(esc_html__('Price is not valid for Product Id: %s', 'customer-specific-pricing-for-woocommerce'), $productId));
				return;
			}
			$ruleType = strtolower($ruleType);
			switch ($ruleType) {
				case 'group':
					$deactivateMethod    = 'deactivateSubrulesOfGroupForProduct';
					break;
				case 'role':
					$deactivateMethod    = 'deactivateSubrulesOfRoleForProduct';
					break;
				default:
					$deactivateMethod    = 'deactivateSubrulesOfCustomerForProduct';
					break;
			}
			$ruleType = ucfirst($ruleType);
			global $wpdb;
			//insert Rule in db
			if ($wpdb->insert($this->subruleTable, array(
				'rule_id'                => $ruleId,
				'product_id'             => $productId,
				'flat_or_discount_price' => $flatOrDiscountPrice,
				'price'                  => $price,
				'active'                 => 1,
				'rule_type'              => $ruleType,
				'associated_entity'      => $associationEntity,
				'min_qty'                => $quantity,
			), array(
				'%d',
				'%d',
				'%d',
				'%f',
				'%d',
				'%s',
				'%s',
				'%d',
			)) ) {
				$currentSubruleId = $wpdb->insert_id;
				//Deactivate Other Rules
				call_user_func(array( $this, $deactivateMethod ), $productId, $associationEntity, $quantity, $currentSubruleId);
				return true;
			}
			$this->addError(__('Could not add subrule in the database. Please check if correct data is added in the form.', 'customer-specific-pricing-for-woocommerce'));
			return false;
		}

		public function updateSubrule( $subruleId, $dataTobeUpdated) {
			//$ruleId, $productId, $flatOrDiscountPrice, $price, $ruleType, $associationEntity

			global $wpdb;

			if (! isset($dataTobeUpdated[ 'active' ])) {
				$dataTobeUpdated[ 'active' ] = 1;
			}

			$sizeOfData          = count($dataTobeUpdated);
			$queryPlaceholders   = array_fill(0, $sizeOfData, '%d');
			$columnsTobeUpdated  = array_keys($dataTobeUpdated);

			//lets set placeholder for price key in query
			$positionOfPrice = array_search('price', $columnsTobeUpdated);
			if (false!== $positionOfPrice) {
				$queryPlaceholders[ $positionOfPrice ] = '%f';

				if ($dataTobeUpdated[ 'price' ] < 0) {
					/* translators: %s Product Id */
					$this->addError(sprintf(esc_html__('Price is not valid for Product Id: %s', 'customer-specific-pricing-for-woocommerce'), $dataTobeUpdated[ 'product_id' ]));
					return;
				}
			}

			//lets set placeholder for rule_type key in query
			$posRuleTypeData = array_search('rule_type', $columnsTobeUpdated);
			if (false!== $posRuleTypeData) {
				$queryPlaceholders[ $posRuleTypeData ]    = '%s';
				$dataTobeUpdated[ 'rule_type' ]                  = ucfirst(strtolower($dataTobeUpdated[ 'rule_type' ]));
			}

			//lets set placeholder for associated_entity key in query
			$positionOfEntityData = array_search('associated_entity', $columnsTobeUpdated);
			if (false!== $positionOfEntityData) {
				$queryPlaceholders[ $positionOfEntityData ] = '%s';
			}

			$noOfRowsUpdated = $wpdb->update($this->subruleTable, $dataTobeUpdated, array(
				'subrule_id' => $subruleId,
			), $queryPlaceholders, array(
				'%d'
			));

			if ($noOfRowsUpdated ||  0 ==$noOfRowsUpdated) {
				return true;
			}
			$this->addError(__('Could not update subrule in the database. Please check if correct data is added in the form.', 'customer-specific-pricing-for-woocommerce'));
			return false;
		}

		/**
		* Deactivate the subrules for the customers which are not there in
		* current
		* submission but present in DB.
		* Set active key to 0 for such results.
		 *
		* @param int $productId Product Id
		* @param array $customerIds Deleted user ids for that product.
		* @param array $qty Deleted min quantity details for that Product
		*/
		public function deactivateSubrulesForCustomersNotInArray( $productId, $customerIds, $qty) {
			if (empty($customerIds)) {
				return;
			}
			global $wpdb;
			if (is_array($customerIds) && is_array($qty)) {
				foreach ($customerIds as $index => $singleUser) {
					@$wpdb->query($wpdb->prepare('UPDATE ' . $wpdb->prefix . 'wusp_subrules SET `active`= 0 WHERE `associated_entity` = %d AND `min_qty` = %d AND `rule_type` = %s AND product_id=%d', $singleUser, $qty[$index], 'customer', $productId));
				}

				//     $qty = implode(', ', $qty);
				//     $user_in             = implode(', ', $customerIds);
				//     $sizeOfData          = count($customerIds);
				//     $queryPlaceholders   = array_fill(0, $sizeOfData, '%d');
				//     $queryPlaceholders   = implode(', ', $queryPlaceholders);
				//     $qtyQueryPlaceholders   = array_fill(0, $sizeOfData, '%d');
				//     $qtyQueryPlaceholders   = implode(', ', $qtyQueryPlaceholders);
				//     @$wpdb->query($wpdb->prepare("UPDATE {$this->subruleTable} SET `active`= 0 WHERE `associated_entity` NOT IN ($queryPlaceholders)) AND `min_qty` NOT IN ($qtyQueryPlaceholders) AND `rule_type` = %s AND product_id=%d", $user_in, $qty, 'customer', $productId));
				// } else {
				//     $user_in = $customerIds;
				//     @$wpdb->query($wpdb->prepare("UPDATE {$this->subruleTable} SET `active`= 0 WHERE `associated_entity` != `$user_in` AND  `rule_type` = %s AND product_id=%d AND min_qty != %d", 'customer', $productId, $qty));
			}
		}

		/**
		* Deactivates the subrules for the roles not present in database.
		 *
		* @param int $productId Product Id.
		* @param array $roles Deleted roles in role-pricing table.
		* @param array $qty Quantities in current selection of role specific * pricing.
		*/
		public function deactivateSubrulesForRolesNotInArray( $productId, $roles, $qty) {
			if (empty($roles) || empty($qty)) {
				return;
			}
			global $wpdb;
			if (is_array($roles) && is_array($qty)) {
				foreach ($roles as $index => $singleRole) {
					@$wpdb->query($wpdb->prepare('UPDATE ' . $wpdb->prefix . 'wusp_subrules SET `active`= 0 WHERE `associated_entity` = %s AND `min_qty` = %d AND `rule_type` = %s AND product_id=%d', $singleRole, $qty[$index], 'Role', $productId));
				}
				// $qty                    = implode(', ', $qty);
				// $roles_in               = implode(', ', $roles);
				// $sizeOfData             = count($roles);
				// $queryPlaceholders      = array_fill(0, $sizeOfData, '%s');
				// $queryPlaceholders      = implode(', ', $queryPlaceholders);
				// $qtyQueryPlaceholders   = array_fill(0, $sizeOfData, '%d');
				// $qtyQueryPlaceholders   = implode(', ', $qtyQueryPlaceholders);
				// @$wpdb->query($wpdb->prepare("UPDATE {$this->subruleTable} SET `active`= 0 WHERE `associated_entity` NOT IN ($queryPlaceholders) AND `min_qty` NOT IN ($qtyQueryPlaceholders) AND `rule_type` = %s AND product_id=%d", $roles_in, $qty, 'role', $productId));
			}
			//  else {
			//     $roles_in = $roles;
			//     @$wpdb->query($wpdb->prepare("UPDATE {$this->subruleTable} SET `active`= 0 WHERE `associated_entity` != `$roles_in` AND  `rule_type` = %s AND product_id=%d AND min_qty != %d", 'role', $productId, $qty));
			// }
		}


		/**
		* Deactivates the Subrules for groups not in the database.
		 *
		* @param int $productId Product Id.
		* @param array $groups deleted groups.
		* @param array $qty Quantities for the selection.
		*/
		public function deactivateSubrulesForGroupsNotInArray( $productId, $groups, $qty) {
			if (empty($groups)) {
				return;
			}
			global $wpdb;
			if (is_array($groups) && is_array($qty)) {
				foreach ($groups as $index => $singleGroup) {
					@$wpdb->query($wpdb->prepare('UPDATE ' . $wpdb->prefix . 'wusp_subrules SET `active`= 0 WHERE `associated_entity` = %d AND `min_qty` = %d AND `rule_type` = %s AND product_id=%d', $singleGroup, $qty[$index], 'group', $productId));
				}

				//     $groups_in           = implode(', ', $groups);
				//     $sizeOfData          = count($groups);
				//     $queryPlaceholders   = array_fill(0, $sizeOfData, '%d');
				//     $queryPlaceholders   = implode(', ', $queryPlaceholders);
				//     @ $wpdb->query($wpdb->prepare("UPDATE {$this->subruleTable} SET `active`= 0 WHERE `associated_entity` NOT IN ($queryPlaceholders) AND  `rule_type` = %s AND product_id=%d", $groups_in, 'group', $productId));
				// } else {
				//     $groups_in = $groups;
				//     @$wpdb->query($wpdb->prepare("UPDATE {$this->subruleTable} SET `active`= 0 WHERE `associated_entity` != `$groups_in` AND  `rule_type` = %s AND product_id=%d", 'group', $productId));
			}
		}
		/**
		* Deactivates subrules of customer for given product.
		 *
		* @param int $productId Product Id
		* @param int $customerId User id.
		* @param int $qty Quantity for that selection
		* @param array $exceptionSubruleIds exceptional subrules.
		*/
		public function deactivateSubrulesOfCustomerForProduct( $productId, $customerId, $qty, $exceptionSubruleIds = null) {
			$this->deactivateSubrulesOfEntityForProduct($productId, 'customer', $customerId, $qty, $exceptionSubruleIds);
		}

		/**
		* Deactivates subrules of role for given product.
		 *
		* @param int $productId Product Id
		* @param int $roleName Role name.
		* @param int $qty Quantity for that selection
		* @param array $exceptionSubruleIds exceptional subrules.
		*/
		public function deactivateSubrulesOfRoleForProduct( $productId, $roleName, $qty, $exceptionSubruleIds = null) {
			$this->deactivateSubrulesOfEntityForProduct($productId, 'role', $roleName, $qty, $exceptionSubruleIds);
		}

		/**
		* Deactivates subrules of group for given product.
		 *
		* @param int $productId Product Id
		* @param int $groupId Group id.
		* @param int $qty Quantity for that selection
		* @param array $exceptionSubruleIds exceptional subrules.
		*/
		public function deactivateSubrulesOfGroupForProduct( $productId, $groupId, $qty, $exceptionSubruleIds = null) {
			$this->deactivateSubrulesOfEntityForProduct($productId, 'group', $groupId, $qty, $exceptionSubruleIds);
		}

		/**
		* Deactivate subrules of all roles for that product, since all
		* the role specific selections are deleted for that Product.
		 *
		* @param int $productId Product Id.
		*/
		public function deactivateSubrulesOfAllRolesForProduct( $productId) {
			global $wpdb;
			$wpdb->update($this->subruleTable, array(
				'active' => 0,
			), array(
				'product_id' => $productId,
				'active'     => 1,
				'rule_type'  => 'Role',
			), array(
				'%d'
			), array(
				'%d',
				'%d',
				'%s'
			));
		}

		/**
		* Deactivate subrules of all customers for that product, since all
		* the customer specific selections are deleted for that Product.
		 *
		* @param int $productId Product Id.
		*/
		public function deactivateSubrulesOfAllCustomerForProduct( $productId) {
			global $wpdb;
			$wpdb->update($this->subruleTable, array(
				'active' => 0,
			), array(
				'product_id' => $productId,
				'active'     => 1,
				'rule_type'  => 'customer',
			), array(
				'%d'
			), array(
				'%d',
				'%d',
				'%s'
			));
		}

		/**
		* Deactivate subrules of all groups for that product, since all
		* the group specific selections are deleted for that Product.
		 *
		* @param int $productId Product Id.
		*/

		public function deactivateSubrulesOfAllGroupsForProduct( $productId) {
			global $wpdb;
			$wpdb->update($this->subruleTable, array(
				'active' => 0,
			), array(
				'product_id' => $productId,
				'active'     => 1,
				'rule_type'  => 'Group',
			), array(
				'%d'
			), array(
				'%d',
				'%d',
				'%s'
			));
		}
		/**
		* Deactivates the subrules of the entity(user/group/role) for given product.
		* Skips the exceptional subrules for the product.
		* If update is not successful displays an error message.
		 *
		* @param int $productId Product Id
		* @param string $ruleType Rule type.
		* @param int/string $associatedEntity associated Entity user_id/group_id/role
		* @param int $qty Quantity for that selection 
		* @param array $exceptionSubruleIds exceptional subrules.
		*/
		public function deactivateSubrulesOfEntityForProduct( $productId, $ruleType, $associatedEntity, $qty, $exceptionSubruleIds = null) {
			global $wpdb;
			$ruleType = ucfirst(strtolower($ruleType));
			//Deactivate all subrules
			if (null== $exceptionSubruleIds || empty($exceptionSubruleIds)) {
				$update_status = $wpdb->update($this->subruleTable, array(
					'active' => 0,
				), array(
					'associated_entity'  => $associatedEntity,
					'product_id'         => $productId,
					'active'             => 1,
					'rule_type'          => $ruleType,
					'min_qty'            => $qty,
				), array(
					'%d'
				), array(
					'%s',
					'%d',
					'%d',
					'%s',
					'%d'
				));
				if (false === $update_status) {
					$this->addError(__('Could not deactivate pre-existing subrules', 'customer-specific-pricing-for-woocommerce'));
				}
			} else {
				//Deactivate only few subrules
				if (is_array($exceptionSubruleIds)) {
					$queryStatus = $wpdb->query($wpdb->prepare('UPDATE ' . $wpdb->prefix . 'wusp_subrules SET `active`= 0 WHERE `associated_entity` = %s AND `min_qty` = %d AND `product_id` = %d AND `active` = %d AND `rule_type` = %s AND subrule_id NOT IN (' . implode(', ', array_fill(0, count($exceptionSubruleIds), '%d')) . ')', $associatedEntity, $qty, $productId, 1, $ruleType, $exceptionSubruleIds));
				} else {
					$queryStatus = $wpdb->query($wpdb->prepare('UPDATE ' . $wpdb->prefix . 'wusp_subrules SET `active`= 0 WHERE `associated_entity` = %s AND `min_qty` = %d AND `product_id` = %d AND `active` = %d AND `rule_type` = %s AND subrule_id != %d', $associatedEntity, $qty, $productId, 1, $ruleType, $exceptionSubruleIds));
				}

				if (false === $queryStatus) {
					$this->addError(__('There was an error while deactivating subrules.', 'customer-specific-pricing-for-woocommerce'));
				}
			}
		}
		/**
		* Returns the count of subrules for a particular rule id.
		 *
		* @param int $ruleId Rule Id.
		* @return int total count of subrules for that rule id.
		*/
		public function countSubrules( $ruleId) {
			$subrules = $this->getSubruleIds($ruleId);
			if (empty($subrules)) {
				return 0;
			}
			return count($subrules);
		}

		/**
		* Gets the Inactive subrules for the specific USP rule.
		* Gets the subrules which are inactive and group them with the rule_ids
		 *
		* @param array $ruleIds associative array of USP rule ids with no. of subrules inactive
		* @return array/bool $rules array of USP rule_ids with the no.of inactive subrules, or false if no inactive subrules
		*/
		public function getCountOfInactiveSubrulesForRules( $ruleIds = array()) {
			global $wpdb;
			$rules = array();
			if (empty($ruleIds)) {
				return false;
			}
			$inactiveCountArray  = $wpdb->get_results( $wpdb->prepare('SELECT rule_id, count(active) as total_inactive_rules FROM ' . $wpdb->prefix . 'wusp_subrules WHERE rule_id IN (' . implode(', ', array_fill(0, count($ruleIds), '%s')) . ') AND active = 0 GROUP BY rule_id', $ruleIds), ARRAY_A);
			if ($inactiveCountArray) {
				foreach ($inactiveCountArray as $singleResult) {
					$rules[ $singleResult[ 'rule_id' ] ] = $singleResult[ 'total_inactive_rules' ];
				}
				return $rules;
			}
			return false;
		}

		public function deleteSubruleByRecordData( $ruleId, $productId, $ruleType, $associatedEntity, $minQty) {
			global $wpdb, $ruleManager;
			$wpdb->delete($this->subruleTable, array(
				'rule_id' => $ruleId,
				'product_id' => $productId,
				'rule_type' => $ruleType,
				'associated_entity' => $associatedEntity,
				'min_qty' => $minQty,
			), array(
				'%d',
				'%d',
				'%s',
				'%s',
				'%d',
			));

			$ruleManager->updateTotalNumberOfSubrules($ruleId, true);
		}

		/**
		* Get the subrule ids for a particular rule id.
		 *
		* @param int $ruleId Rule Id.
		* @return array $subrules subrule ids array for particular rule id.
		*/
		public function getSubruleIds( $ruleId) {
			global $wpdb;
			$subrules = $wpdb->get_col($wpdb->prepare('SELECT subrule_id FROM ' . $wpdb->prefix . 'wusp_subrules WHERE rule_id=%d', $ruleId));
			return $subrules;
		}

		/**
		* Deletes subrules and all the (user, role and group) specific pricing
		* mapping for the given product id.
		* Updates the total no. of subrules for the rules
		 *
		* @param int $product_id deleted product id.
		*/
		public function deleteSubruleIdsForProduct( $product_id) {
			global $wpdb, $ruleManager;
			$subrules = $wpdb->get_results($wpdb->prepare('SELECT subrule_id, rule_id FROM ' . $wpdb->prefix . 'wusp_subrules WHERE product_id = %d', $product_id), ARRAY_A);
			if (isset($subrules)) {
				foreach ($subrules as $subrule) {
					$this->deleteSubrule($subrule['subrule_id']);
					$ruleManager->updateTotalNumberOfSubrules($subrule['rule_id']);
				}
			}
		}
		/**
		* Delete the subrule for the given subrule id.
		 *
		* @param int $subruleId subrule id to be deleted.
		*/
		public function deleteSubrule( $subruleId) {
			global $wpdb;
			// $subruleTable = $wpdb->prefix . 'wusp_subrules';
			$wpdb->delete($this->subruleTable, array(
				'subrule_id' => $subruleId,
			), array(
				'%d',
			));
		}
		/**
		* Delete the subrules for the given subrule ids.
		 *
		* @param array $subruleId subrule ids to be deleted.
		*/
		public function deleteSubrules( $subruleIds = array()) {

			global $wpdb;
			$wpdb->show_errors();
			if (empty($subruleIds)) {
				return;
			}
			if (is_array($subruleIds)) {
				//Delete query was not wokring with placeholder. So executing query directly.
				$wpdb->query($wpdb->prepare('DELETE FROM ' . $wpdb->prefix . 'wusp_subrules WHERE subrule_id IN (' . implode(', ', array_fill(0, count($subruleIds), '%d')) . ')', $subruleIds));
			}
		}
		/**
		* Deletes the subrules of the specific rule Id.
		* Gets the associated entities for the active subrules of that particular 
		* rule.
		* Depending on the rule_type select the pricing_mapping table.
		* Delete the entry for specific product_id and associated entity.
		* Delete the subrules for rule id.
		 *
		* @param int $ruleId Rule Id.
		* @param string $ruleType rule type.
		*/
		public function deleteSubrulesOfRule( $ruleId, $ruleType = null) {
			global $wpdb;
			$productsEntities    = $this->getAssociatedEntitiesForActiveSubrulesOfRule($ruleId);
			$tableName           = '';
			$entityColumnName = '';
			if (! empty($productsEntities) && null!= $ruleType) {
				//Delete values from corresponding tables.
				$ruleType = strtolower($ruleType);
				switch ($ruleType) {
					case 'role':
						$tableName          = $wpdb->prefix . 'wusp_role_pricing_mapping';
						$entityColumnName    = 'role';
						break;
					case 'customer':
						$tableName          = $wpdb->prefix . 'wusp_user_pricing_mapping';
						$entityColumnName    = 'user_id';
						break;
					case 'group':
						$tableName             = $wpdb->prefix . 'wusp_group_product_price_mapping';
						$entityColumnName    = 'group_id';
						break;
				}
				foreach ($productsEntities as $productID => $entity) {
					$wpdb->delete($tableName, array(
						'product_id'         => $productID,
						$entityColumnName    => $entity,
					));
				}
			}
			// echo $ruleId;
			$wpdb->delete($this->subruleTable, array(
				'rule_id' => $ruleId,
			), array(
				'%d',
			));
		}
		/**
		* Gets the associated entities for the active subrules of the particular 
		* rule.
		* Make the associative array with key as product id and the value as 
		* associated entity for that subrule.
		 *
		* @param int $ruleId Rule Id.
		* @return array array of associated entities.
		*/
		public function getAssociatedEntitiesForActiveSubrulesOfRule( $ruleId) {
			global $wpdb;
			$productsEntities    = array();
			$products            = $wpdb->get_results($wpdb->prepare('SELECT product_id, associated_entity FROM ' . $wpdb->prefix . 'wusp_subrules WHERE rule_id = %d AND active = %d', $ruleId, 1));

			if ($products) {
				foreach ($products as $singleProduct) {
					$productsEntities[ $singleProduct->product_id ] = $singleProduct->associated_entity;
				}
			}
			return $productsEntities;
		}

		/**
		* Get all the subrules associated with the Rule id.
		 *
		* @param int $ruleId Rule Id.
		*/
		public function getAllSubrulesInfoForRule( $ruleId) {
			global $wpdb;
			$subrules = $wpdb->get_results($wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . 'wusp_subrules WHERE rule_id=%d', $ruleId), ARRAY_A);
			return $subrules;
		}

		public function getActiveSubrulesForProduct( $productId, $ruleType) {
			if (empty($productId) || empty($ruleType)) {
				return;
			}
			global $wpdb;
			$ruleType = ucfirst(strtolower($ruleType));

			$subrules        = $wpdb->get_results($wpdb->prepare('SELECT subrule_id, associated_entity, price, min_qty, flat_or_discount_price FROM ' . $wpdb->prefix . 'wusp_subrules WHERE product_id = %d AND active = %d AND rule_type = %s', $productId, 1, $ruleType), ARRAY_A);

			$activeSubrules  = array();
			if ($subrules) {
				foreach ($subrules as $singleSubrule) {
					$activeSubrules[ $singleSubrule[ 'associated_entity' ] ][ $singleSubrule[ 'min_qty' ] ] = array(
						'price'     => $singleSubrule[ 'price' ],
						// 'min_qty'   => $single_result->min_qty,
						'price_type'=> $singleSubrule[ 'flat_or_discount_price' ]
					);
					// $activeSubrules[ $singleSubrule[ 'associated_entity' ] ]['price'] = $singleSubrule[ 'price' ];
					// $activeSubrules[ $singleSubrule[ 'associated_entity' ] ]['price_type'] = $singleSubrule[ 'flat_or_discount_price' ];
				}
				if (! empty($activeSubrules)) {
					return $activeSubrules;
				}
				return false;
			}
			return false;
		}

		/**
		* Gets the rules associated with the particular entity.
		 *
		* @param int $associatedEntity id of the entity(group/user)
		* @param string $ruleType rule type (group/customer)
		*/
		public function getAllRuleInfoForAssociatedEntity( $associatedEntity, $ruleType) {
			global $wpdb;
			$ruleType = ucfirst(strtolower($ruleType));
			$subrules = $wpdb->get_results($wpdb->prepare('SELECT rule_id FROM ' . $wpdb->prefix . 'wusp_subrules WHERE rule_type = %s AND associated_entity = %s', $ruleType, $associatedEntity), ARRAY_A);
			return $subrules;
		}

		/**
		* Select the Active subrules information for the user Id.
		 *
		* @param int $userId User Id for the selection
		* @param array $prodIds Product Ids.
		* @return array $subrules subrules array for the user
		*/
		public function getAllActiveSubrulesInfoForUserRules( $userId, $prodIds = array()) {
			global $wpdb;

			if (! empty($prodIds)) {
				$prepareArgs = array_merge((array) $userId, (array) $prodIds);
				$prepareArgs = array_merge((array) 'customer', (array) $prepareArgs);

				$subrules = $wpdb->get_results( $wpdb->prepare('SELECT rule_id, product_id, price, min_qty, flat_or_discount_price as price_type FROM ' . $wpdb->prefix . 'wusp_subrules WHERE rule_type = %s AND associated_entity = %d AND active = 1 AND product_id NOT IN (' . implode(', ', array_fill(0, count($prodIds), '%s')) . ')', $prepareArgs), ARRAY_A);
			} else {
				$subrules = $wpdb->get_results( $wpdb->prepare('SELECT rule_id, product_id, price, min_qty, flat_or_discount_price as price_type FROM ' . $wpdb->prefix . 'wusp_subrules WHERE rule_type = %s AND associated_entity = %d AND active = 1 ', 'customer', $userId), ARRAY_A);
			}
			return $subrules;
		}

		/**
		* Select the Active subrules information for the user Id.
		 *
		* @param array $roleList Roles for the selection
		* @param array $prodIds Product Ids.
		* @return array $subrules subrules array for the user
		*/
		public function getAllActiveSubrulesInfoForRolesRule( $roleList, $prodIds = array()) {
			global $wpdb;
			if (! empty($prodIds)) {
				$prepareArgs = array_merge((array) 'role', (array) $roleList);
				$prepareArgs = array_merge((array) $prepareArgs, (array) $prodIds);
				$subrules = $wpdb->get_results($wpdb->prepare('SELECT rule_id, product_id, price, min_qty, flat_or_discount_price as price_type FROM ' . $wpdb->prefix . 'wusp_subrules WHERE rule_type = %s AND associated_entity IN (' . implode(', ', array_fill(0, count($roleList), '%s')) . ') AND active = 1 AND `product_id` NOT IN (' . implode(', ', array_fill(0, count($prodIds), '%d')) . ')', $prepareArgs), ARRAY_A);
			} else {
				$prepareArgs = array_merge((array) 'role', (array) $roleList);
				$subrules = $wpdb->get_results($wpdb->prepare('SELECT rule_id, product_id, price, min_qty, flat_or_discount_price as price_type FROM ' . $wpdb->prefix . 'wusp_subrules WHERE rule_type = %s AND associated_entity IN (' . implode(', ', array_fill(0, count($roleList), '%s')) . ') AND active = 1 ', $prepareArgs), ARRAY_A);
			}
			return $subrules;
		}

		/**
		* Select the Active subrules information for the user Id.
		 *
		* @param array $groupIds Group Ids for the selection
		* @param array $prodIds Product Ids.
		* @return array $subrules subrules array for the user
		*/
		public function getAllActiveSubrulesInfoForGroupsRule( $groupIds, $prodIds = array()) {
			global $wpdb;
			$source = __('Direct', 'customer-specific-pricing-for-woocommerce');

			if (!empty($prodIds)) {
				$prepareArgs = array_merge((array) 'group', (array) $groupIds);
				$prepareArgs = array_merge((array) $source, (array) $prepareArgs);
				$prepareArgs = array_merge((array) $prepareArgs, (array) $prodIds);

				$subrules = $wpdb->get_results($wpdb->prepare('SELECT rule_id, product_id, price, min_qty, flat_or_discount_price as price_type, %s as "source" FROM ' . $wpdb->prefix . 'wusp_subrules WHERE rule_type = %s AND associated_entity IN (' . implode(', ', array_fill(0, count($groupIds), '%d')) . ') AND active = 1 AND `product_id` NOT IN (' . implode(', ', array_fill(0, count($groupIds), '%d')) . ')', $prepareArgs), ARRAY_A);
			} else {
				$prepareArgs = array_merge((array) 'group', (array) $groupIds);
				$prepareArgs = array_merge((array) $source, (array) $prepareArgs); 
				$subrules = $wpdb->get_results($wpdb->prepare('SELECT rule_id, product_id, price, min_qty, flat_or_discount_price as price_type, %s as "source" FROM ' . $wpdb->prefix . 'wusp_subrules WHERE rule_type = %s AND associated_entity IN (' . implode(', ', array_fill(0, count($groupIds), '%d')) . ') AND active = 1', $prepareArgs), ARRAY_A);
			}
			return $subrules;
		}

		//      public function shouldRuleBeDeactivated($ruleId){
		//          global $wpdb;
		//            $subrules = $wpdb->get_results($wpdb->prepare("SELECT subrule_id, active FROM {$this->subruleTable} WHERE rule_id=%d", $ruleId), ARRAY_A);
		//            return $subrules;
		//      }
	}

}
$GLOBALS['subruleManager'] = WdmSubruleManagement::getInstance();
