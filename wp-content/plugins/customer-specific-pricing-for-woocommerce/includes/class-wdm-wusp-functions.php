<?php

namespace cspFunctions;

if (! class_exists('WdmWuspFunctions')) {

	class WdmWuspFunctions {
	
		private static $instance;
		/**
		 * This member variable is used to store & retrive
		 * the cached woocommerce product categories
		 *
		 * @since 4.4.3
		 * @var array - array of term objects
		 */
		private static $categories = array();
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
		 * This function tells if PEP plugin is active or not.
		 */
		public static function isPepActive() {

			static $isActive = null;
			
			if ( null!== $isActive) {
				return $isActive;
			}
			
			$isActive = false;
			if (class_exists('QuoteUp\QuoteUp')) {
				$isActive = true;
			}
			
			return $isActive;
		}

		/**
		* Save customer specific pricing pair from rules.
		 *
		* @param array $customer_ids selected customers
		* @param array $product_values Product names.
		* @param array $product_quantities Product quantities.
		* @param array $product_actions Price-type selected.
		* @param string $query_title rule title.
		* @param string $option_name rule-type.
		* @param int $current_query_id current rule id.
		* @return string HTML for the message on Rules Page.
		*/
		public function saveCustomerPricingPair( $customer_ids, $product_values, $product_quantities, $oldQuantities, $product_actions, $query_title, $option_name, $current_query_id = null) {
			global $wpdb;
			$selection_details[ 'table_name' ]       = $wpdb->prefix . 'wusp_user_pricing_mapping';
			$selection_details[ 'selection_column' ] = 'user_id';
			$selection_details[ 'selection_type' ]   = 'customer';
			
			return $this->saveSelectionPairs($selection_details, $customer_ids, $product_values, $product_quantities, $oldQuantities, $product_actions, $query_title, $option_name, $current_query_id);
		}

		//function ends -- saveCustomerPricingPair

		/**
		* Save Role specific pricing pair from rules.
		 *
		* @param array $role_list selected roles
		* @param array $product_values Product names.
		* @param array $product_quantities Product quantities.
		* @param array $product_actions Price-type selected.
		* @param string $query_title rule title.
		* @param string $option_name rule-type.
		* @param int $current_query_id current rule id.
		* @return string HTML for the message on Rules Page.
		*/
		public function saveRolePricingPair( $role_list, $product_values, $product_quantities, $oldQuantities, $product_actions, $query_title, $option_name, $current_query_id = null) {
			global $wpdb;

			$selection_details[ 'table_name' ]       = $wpdb->prefix . 'wusp_role_pricing_mapping';
			$selection_details[ 'selection_column' ] = 'role';
			$selection_details[ 'selection_type' ]   = 'role';

			return $this->saveSelectionPairs($selection_details, $role_list, $product_values, $product_quantities, $oldQuantities, $product_actions, $query_title, $option_name, $current_query_id);
		}

		//function ends -- saveRolePricingPair

		/**
		* Save Group specific pricing pair from rules.
		 *
		* @param array $group_ids selected groups.
		* @param array $product_values Product names.
		* @param array $product_quantities Product quantities.
		* @param array $product_actions Price-type selected.
		* @param string $query_title rule title.
		* @param string $option_name rule-type.
		* @param int $current_query_id current rule id.
		* @return string HTML for the message on Rules Page.
		*/
		public function saveGroupPricingPair( $group_ids, $product_values, $product_quantities, $oldQuantities, $product_actions, $query_title, $option_name, $current_query_id = null) {
			global $wpdb;

			$selection_details[ 'table_name' ]       = $wpdb->prefix . 'wusp_group_product_price_mapping';
			$selection_details[ 'selection_column' ] = 'group_id';
			$selection_details[ 'selection_type' ]   = 'group';

			return $this->saveSelectionPairs($selection_details, $group_ids, $product_values, $product_quantities, $oldQuantities, $product_actions, $query_title, $option_name, $current_query_id);
		}

		//function ends -- saveGroupPricingPair
		/**
		 * Checks if Pricing row exists.
		 *
		 * @param string $table_name table name.
		 * @param string $option_type rule-type.
		 * @param string $option_key column name.
		 * @return int $result count of the entries.
		 */
		private static function checkPricingRow( $table_name, $option_type, $option_key) {
			global $wpdb;

			switch ($table_name) {
				case $wpdb->prefix . 'wusp_user_pricing_mapping':
					$result = $wpdb->get_var($wpdb->prepare('SELECT Count(id) FROM ' . $wpdb->prefix . 'wusp_user_pricing_mapping WHERE user_id = %s', $option_type)); 
					break;
				case $wpdb->prefix . 'wusp_role_pricing_mapping':
					$result = $wpdb->get_var($wpdb->prepare('SELECT Count(id) FROM ' . $wpdb->prefix . 'wusp_role_pricing_mapping WHERE role = %s', $option_type)); 	
					break;
				case $wpdb->prefix . 'wusp_group_product_price_mapping':
					$result = $wpdb->get_var($wpdb->prepare('SELECT Count(id) FROM ' . $wpdb->prefix . 'wusp_group_product_price_mapping WHERE group_id = %s', $option_type));	
					break;
				default:
					# do nothing
					break;
			}

			return $result;
		}

		/**
		 * Sort a 2 dimensional array based on 1 or more indexes.
		 * msort() can be used to sort a rowset like array on one or more
		 * 'headers' (keys in the 2th array).
		 *
		 * @param array        $array      The array to sort.
		 * @param string|array $key        The index(es) to sort the array on.
		 * @param int          $sort_flags The optional parameter to modify the sorting
		 *                                 behavior. This parameter does not work when
		 *                                 supplying an array in the $key parameter.
		 *
		 * @return array The sorted array.
		 */
		public function msort( $array, $key) {
			if (is_array($array) && count($array) > 0) {
				if (!empty($key)) {
					$mapping = array();
					foreach ($array as $k => $v) {
						$sort_key = '';
						if (!is_array($key)) {
							$sort_key = $v[$key];
						}
						$mapping[$k] = $sort_key;
					}
					asort($mapping, SORT_REGULAR);
					$sorted = array();
					foreach ($mapping as $k => $v) {
						$sorted[$k] = $array[$k];
					}
					return $sorted;
				}
			}
			return $array;
		}

		/**
		* Returns the Product id for simple product.
		* Returns variation id for variable product.
		 *
		* @param object $productObject Product Object.
		* @param string $context view
		*/
		public function getProductId( $productObject, $context = 'view') {

			if (is_callable(array($productObject, 'get_id'))) {
				return $productObject->get_id($context);
			}
			return isset($productObject->variation_id)? $productObject->variation_id : $productObject->id ;
		}

		/**
		* Returns true if quantity present in array.
		 *
		* @param array $array array of quantities-pricing for any entity.
		* @param int $qty each value in quantities array.
		* @return bool true if present otherwise false.
		*/
		public function hasQty( $array, $qty) {
			$qtyArray = $this->getArrayColumn($array, 'min_qty');

			if (count($qtyArray) > 0 && in_array($qty, $qtyArray)) {
				return true;
			}
			return false;
		}
		/**
		* Returns true if Quantity is there in quantity pricing array.
		 *
		* @param array $qtysArray MinQuantities array
		* @param int $qty Quantity of Product.
		* @return true if present in array
		*/
		public function hasQtyInPriceArray( $qtysArray, $qty) {
			if (count($qtysArray) > 0 && in_array($qty, $qtysArray)) {
				return true;
			}
			return false;
		}

		/**
		* If category specific pricing is there for particular product.
		* More Priority given to user/group/role specific pricing than
		* category specific pricing.
		 *
		* @param array $productPrices entity specific pricing details.
		* @param array $catPrices category specific pricing for entity.
		* @return array $allPrices merged specific pricing for that entity.
		*/
		public function mergeProductCatPriceSearch( $productPrices, $catPrices) {
			$allPrices = array();
			foreach ($productPrices as $key => $record) {
				if (isset($catPrices[$record['product_id']]) && isset($catPrices[$record['product_id']][$record['min_qty']])) {
					continue;
				} else {
					$allPrices[] = $record;
				}
			}
			
			foreach ($catPrices as $key => $record) {
				foreach ($record as $value) {
					$allPrices[] = $value;
				}
			}
			return $allPrices;
		}

		/**
		* Gets the Product categories for specific product.
		*
		* @param object $product wc-product
		* @return array array of the categories of that product.
		*/
		public function getProductCategories( $product) {
			if ( empty($product) ) {
				return array();
			}

			if (empty(self::$categories)) {
				self::retriveAndStoreAllProductCats();
			}

			$productId = $product->get_id();
			if ('simple' === $product->get_type() || 'variable' === $product->get_type()) {
				$productId = $product->get_id();
				$catArray  = $this->getCategoriesIfExistInCache($product);
				if (!empty($catArray)) {
					return $catArray;
				}
			} elseif ('variation' === $product->get_type()) {
				if (version_compare(WC_VERSION, '3.0', '<')) {
					$productId = $product->id;
				} else {
					$productId = $product->get_parent_id();
				}
			}
			return wp_get_post_terms($productId, 'product_cat');
		}


		/**
		* Save selected specific pricing pair from rules.
		* Add the rule in DB.
		* Gets the subrules for that rule.
		* Updates the rules and subrules based on recent pricing selection.
		 *
		* @param array $selection_details DB details for the entity of
		* selection.
		* @param array $selection_ids selected entities.
		* @param array $product_values Product names.
		* @param array $product_quantities Product quantities.
		* @param array $product_actions Price-type selected.
		* @param string $query_title rule title.
		* @param string $option_name rule-type.
		* @param int $current_query_id current rule id.
		* @return string HTML for the message on Rules Page.
		*/
		private function saveSelectionPairs( $selection_details, $selection_ids, $product_values, $product_quantities, $oldQuantities, $product_actions, $query_title, $option_name, $current_query_id = null) {
			global $ruleManager, $subruleManager;
			$ruleCreated    = true;
			$error          = '';
			$subrulesOfRule = array();
			//Create Main Rule
			$rule_id = $current_query_id;
			if (empty($current_query_id)) {
				$rule_id = $ruleManager->addRule($query_title, $selection_details[ 'selection_type' ]);
				//If main rule can not be created, Return here
				if ( false===$rule_id) {
					return;
				}
			} else {
				$subrulesOfRule = self::getSubruleOfRule($current_query_id, $rule_id, $query_title, $selection_details);
			}
			$selection_entry_list = array();
			$price_list           = array();
			$product_id_list      = array();

			$row_count           = 0;
			$total_process_count = count($selection_ids) * count($product_values);

			$selection_entry_list = self::loopSelectionIds($selection_ids, $product_values, $product_quantities, $oldQuantities, $product_actions, $option_name, $row_count, $total_process_count, $selection_details, $selection_entry_list, $product_id_list, $rule_id, $price_list);

			delete_option($option_name . '_value');
			delete_option($option_name . '_status');

			if ($subruleManager->countSubrules($rule_id) == 0) {
				$ruleManager->deleteRule($rule_id);
				$error       = __('Rule could not be created', 'customer-specific-pricing-for-woocommerce');
				$ruleCreated = false;
			}
			$subruleErrors = trim($subruleManager->errors);
			$ruleErrors    = trim($ruleManager->errors);
			if (!empty($subruleErrors) || !empty($ruleErrors)) {
				$ruleCreated = false;
			}
			if ($ruleCreated) {
				//Delete old subrules asssociated with current rule
				if (! empty($current_query_id)) {
					
					$subruleManager->deleteSubrules($subrulesOfRule);
				}
				$ruleManager->updateTotalNumberOfSubrules($rule_id);
				$ruleManager->setUnusedRulesAsInactive();
			}

			$this->callRuleCreatedHook($selection_entry_list, $selection_details);
		
			return $this->sendSelectionResult($ruleCreated, $ruleManager->errors . ' ' . $subruleManager->errors . ' ' . $error, $selection_entry_list, $rule_id, $current_query_id);
		} //function ends -- saveSelectionPairs

		/**
		 * Calls the hook wdm_rules_saved if the rules are saved to the database.
		 *
		 * @param array $selection_entry_list - ids of the rules updated or deleted.
		 * @return void
		 */
		public function callRuleCreatedHook( $selection_entry_list, $selection_details) {
			//call hook
			if (!empty($selection_entry_list)) {
				global $wpdb;
				$ruleType =$selection_details['selection_type'];
				if ('customer'==$ruleType) {
					$databaseRuleType ='user_id';
				} elseif ('group'==$ruleType) {
					$databaseRuleType ='group_id';
				} else {
					$databaseRuleType =$ruleType;
				}
				include_once CSP_PLUGIN_URL . "/includes/rules/wdm-csp-rule-$ruleType.php";
				$ruleClassName ="\\rules\\" . ucfirst($ruleType) . 'BasedRule';
				$wdmSavedRules =array();
				$tableName     =$selection_details['table_name'];
				
				switch ($tableName) {
					case $wpdb->prefix . 'wusp_user_pricing_mapping':
						$results =$wpdb->get_results($wpdb->prepare('SELECT * From ' . $wpdb->prefix . 'wusp_user_pricing_mapping WHERE id IN (' . implode(', ', array_fill(0, count($selection_entry_list), '%s')) . ')', $selection_entry_list));
						break;
					
					case $wpdb->prefix . 'wusp_role_pricing_mapping':
						$results =$wpdb->get_results($wpdb->prepare('SELECT * From ' . $wpdb->prefix . 'wusp_role_pricing_mapping WHERE id IN (' . implode(', ', array_fill(0, count($selection_entry_list), '%s')) . ')', $selection_entry_list));
						break;

					case $wpdb->prefix . 'wusp_group_product_price_mapping':
						$results =$wpdb->get_results($wpdb->prepare('SELECT * From ' . $wpdb->prefix . 'wusp_group_product_price_mapping WHERE id IN (' . implode(', ', array_fill(0, count($selection_entry_list), '%s')) . ')', $selection_entry_list));
						break;

					default:
						$results = '';
						break;
				}
				
				foreach ($results as $savedRule) {
					$wdmSavedRules[] =new $ruleClassName($savedRule->id, $savedRule->$databaseRuleType, $savedRule->flat_or_discount_price, $savedRule->min_qty, $savedRule->price);
				}
				$ruleCreationType =$ruleType . '_specific_product_rules_product_pricing_page';
				do_action('wdm_rules_saved', $ruleCreationType, $wdmSavedRules);
			}
		} //function ends -- saveSelectionPairs



		/**
		* Adds the selected product-pricings in the database.
		* Returns the array of the insertion ids of the pricing mappings.
		 *
		* @param array $selection_ids selected entities.
		* @param array $product_values Product names.
		* @param array $product_quantities Product quantities.
		* @param array $product_actions Price-type selected.
		* @param string $option_name rule-type.
		* @param int $row_count intially zero
		* @param int $total_process_count product of count of selections and * products
		* @param array $selection_details DB details for the entity of
		* selection.
		* @param array $selection_entry_list initially empty
		* @param array $product_id_list initially empty
		* @param int $rule_id Rule Id.
		* @param array $price_list initially empty
		* @param string $query_title rule title.

		* @param int $current_query_id current rule id.
		*/
		private static function loopSelectionIds( $selection_ids, $product_values, $product_quantities, $oldQuantities, $product_actions, $option_name, $row_count, $total_process_count, $selection_details, $selection_entry_list, $product_id_list, $rule_id, $price_list) {
			
			if (! empty($product_values) && ! empty($selection_ids)) {
				foreach ($product_values as $key => $price) {
					$pattern = trim(str_replace('csp_value_', '', $key));
					preg_match_all('/(\\d+)/is', $pattern, $matches);
					$productId = $matches[1][0];
					$userId    = trim(str_replace("{$productId}_", '', $pattern));

					$check_rows_exist     = self::checkPricingRow($selection_details[ 'table_name' ], $userId, $selection_details[ 'selection_column' ]);
					$selection_entry_list = self::loopProductValues($productId, $product_quantities, $oldQuantities, $product_actions, $check_rows_exist, $option_name, $row_count, $total_process_count, $selection_details, $userId, $selection_entry_list, $product_id_list, $rule_id, $price_list, $price);
				}//foreach ends
			}
			return $selection_entry_list;
		} //end of function loopSelectionIds

		/**
		* Gets the subrules of the rule.
		* Updates the rule with the details.
		* Gets the subrules associated with them.
		 *
		* @param int $current_query_id current rule id.
		* @param int $rule_id current rule id.
		* @param string $query_title rule title.
		* @param array $selection_details DB details for the entity of
		* selection.
		* @return array $subrules subrule ids array for particular rule id.
		*/
		private static function getSubruleOfRule( $current_query_id, $rule_id, $query_title, $selection_details) {
			global $ruleManager, $subruleManager;
			if (! is_numeric($current_query_id)) {
				return;
			}

			$ruleUpdateStatus = $ruleManager->updateRule($rule_id, array(
				'rule_title' => stripcslashes($query_title),
				'rule_type' => $selection_details[ 'selection_type' ] ));
			$subrulesOfRule   = $subruleManager->getSubruleIds($rule_id);

			//If main rule can not be created, Return here
			if (false === $ruleUpdateStatus) {
				return;
			}
			return $subrulesOfRule;
		} //end of function getSubruleOfRule



		public static function wdmUpdateCSPRule( $selection_details, $row_exist_result, $userId, $productId, $price, $flat_or_discount, $quantity, $rule_id) {
			global $subruleManager;
			$ruleUpdated = self::updateSelectionPair($selection_details[ 'selection_type' ], $row_exist_result[ 0 ]->id, $userId, $productId, $price, $flat_or_discount, $quantity);
			if ($ruleUpdated) {
				$subruleManager->addSubrule($rule_id, $productId, $quantity, $flat_or_discount, $price, $selection_details[ 'selection_type' ], $userId);
			} else {
				$flat_or_discount =$row_exist_result[0]->flat_or_discount_price;
				$price            =$row_exist_result[0]->price;
				$subruleManager->addSubrule($rule_id, $productId, $quantity, $flat_or_discount, $price, $selection_details[ 'selection_type' ], $userId);
			}
			return intval($row_exist_result[0]->id);
		}

		/**
		* Updates the progress status in options DB.
		* Updates the product-pricing in DB if already exists.
		* Add new entry in DB if new product-pricing.
		* Adds the subrule associated with the product-pricing.
		 *
		* @param int $productId Product Id.
		* @param array $selection_ids selected entities.
		* @param array $product_quantities Product quantities.
		* @param array $product_actions Price-type selected.
		* @param int $check_rows_exist count of pricing row.
		* @param string $option_name rule-type.
		* @param int $row_count intially zero
		* @param int $total_process_count product of count of selections and
		* products
		* @param array $selection_details DB details for the entity of
		* selection.
		* @param int $userId User Id.
		* @param array $selection_entry_list initially empty
		* @param array $product_id_list initially empty
		* @param int $rule_id Rule Id.
		* @param array $price_list initially empty
		* @param string $query_title rule title.
		* @param float $price Price for product.
		* @return array $selection_entry_list insertion ids of the product-pricing * in DB.
		*/
		private static function loopProductValues( $productId, $product_quantities, $oldQuantities, $product_actions, $check_rows_exist, $option_name, $row_count, $total_process_count, $selection_details, $userId, $selection_entry_list, $product_id_list, $rule_id, $price_list, $price) {
			global $wpdb, $subruleManager;
			self::updateProgressOption($row_count, $total_process_count, $option_name);
			++$row_count;
			$ruleInserted =false;
			if ( ''!=$price && floatval($price) >= 0) {
				update_option($option_name . '_status', __('Processing Product ID ', 'customer-specific-pricing-for-woocommerce') . ' ' . $productId);

				$flat_or_discount = isset($product_actions[ 'wdm_csp_price_type' . $productId . '_' . $userId ]) ? ( 2 == $product_actions[ 'wdm_csp_price_type' . $productId . '_' . $userId ] ? 2 : 1 ) : 1;

				$quantity    = isset($product_quantities[ 'csp_qty_' . $productId . '_' . $userId ]) ? $product_quantities[ 'csp_qty_' . $productId . '_' . $userId ] : 1;
				$oldQuantity = isset($oldQuantities[ 'csp_qty_' . $productId . '_' . $userId ]) ? $oldQuantities[ 'csp_qty_' . $productId . '_' . $userId ] : 1;
				
				if ($check_rows_exist > 0) {
					$tableName = $selection_details['table_name'];
					switch ($tableName) {
						case $wpdb->prefix . 'wusp_user_pricing_mapping':
							$row_exist_result = $wpdb->get_results($wpdb->prepare('SELECT id,flat_or_discount_price,price FROM ' . $wpdb->prefix . 'wusp_user_pricing_mapping WHERE user_id = %d AND product_id = %d AND min_qty = %d', $userId, $productId, $oldQuantity));
							break;
						case $wpdb->prefix . 'wusp_role_pricing_mapping':
							$row_exist_result = $wpdb->get_results($wpdb->prepare('SELECT id,flat_or_discount_price,price FROM ' . $wpdb->prefix . 'wusp_role_pricing_mapping WHERE role = %d AND product_id = %d AND min_qty = %d', $userId, $productId, $oldQuantity));
							break;
						case $wpdb->prefix . 'wusp_group_product_price_mapping':
							$row_exist_result = $wpdb->get_results($wpdb->prepare('SELECT id,flat_or_discount_price,price FROM ' . $wpdb->prefix . 'wusp_group_product_price_mapping WHERE group_id = %d AND product_id = %d AND min_qty = %d', $userId, $productId, $oldQuantity));
							break;
						default:
							$row_exist_result ='';
							break;
					}

					if ($row_exist_result && isset($row_exist_result[0])) {
						//update the result
						$selection_entry_list[] = self::wdmUpdateCSPRule($selection_details, $row_exist_result, $userId, $productId, $price, $flat_or_discount, $quantity, $rule_id);
						$price_list[]           = $price;
					} else {
						//Insert the result
						$ruleInserted           = self::setSelectionPair($selection_details[ 'selection_type' ], $userId, $productId, $price, $flat_or_discount, $quantity);
						$selection_entry_list[] = $ruleInserted;
						$price_list[]           = $price;
						if ($ruleInserted) {
							$subruleManager->addSubrule($rule_id, $productId, $quantity, $flat_or_discount, $price, $selection_details[ 'selection_type' ], $userId);
						}
					}
				} else {
					$ruleInserted           = self::setSelectionPair($selection_details[ 'selection_type' ], $userId, $productId, $price, $flat_or_discount, $quantity);
					$selection_entry_list[] = $ruleInserted;
					$price_list[]           = $price;
					if ($ruleInserted) {
						$subruleManager->addSubrule($rule_id, $productId, $quantity, $flat_or_discount, $price, $selection_details[ 'selection_type' ], $userId);
					}
				}
				
				if (! in_array($productId, $product_id_list)) {
					$product_id_list[] = $productId;
				}
			}//if ends -- Price not empty
			// }//foreach ends
			return $selection_entry_list;
		} //end processProductValues

		/**
		* Gets the column or the values of the keys sent.
		* For example, slugs for categories in array
		 *
		* @param array $array array of entity.
		* @param string $column column or value name.
		* @return array the values for the entity.
		*/
		public function getArrayColumn( $array, $column) {
			$result = array();
			if (!is_array($array) || empty($column)) {
				return $result;
			} else {
				foreach ($array as $value) {
					$result[] = $value->$column;
				}
				return $result;
			}
		}

		/**
		* Return the calculated price for single product for quantity x.
		* If price type is % , calculate the discounted amount of price.
		* If price type is flat , return the flat price.
		* If multiple prices are available for same quantity
		* calculates and send the price for a quantity with minimum value.
		*
		* @param int $quantity Min Quantity.
		* @param array $priceArray Pricing-quantity array
		* @param int/float $regular_price regular price of product.
		* @return int/float/string Specific Price for the Produc, NO_PRICE when empty
		*/
		public function priceForQuantity( $quantity, $priceArray, $regular_price) {
			
			if (count($priceArray) == 0) {
				return 'NO_PRICE';
			}

			$pricesForQuantity =array();
			foreach ($priceArray as $a) {
				if ($a->min_qty == $quantity) {
					if ( 2==$a->price_type) {
						if (false==$regular_price) {
							continue;
						} else {
							array_push($pricesForQuantity, $this->discountCalculation($regular_price, $a->price));
						}
					} else {
						array_push($pricesForQuantity, $a->price);
					}
				}
			}
			if (!empty($pricesForQuantity)) {
				return min($pricesForQuantity);
			}
			return 'NO_PRICE';
		}

		/**
		 * Returns sale price of the product when % discount is applied and
		 * sale price % discount is enabled from the settings.
		 * else returns regular price.
		 *
		 * @param [type] $product
		 * @return float
		 */
		public function wdmGetCurrentPrice( $productId) {
			$cspSettings                = get_option('wdm_csp_settings');
			$isSalePriceDiscountEnabled = isset($cspSettings['enable_sale_price_discount']) && 'enable' == $cspSettings['enable_sale_price_discount'] ? true : false;
			$salePrice                  =$this->wdmGetSalePrice($productId);
			if ($isSalePriceDiscountEnabled && $salePrice) {
				// % discount is to be applied on sales price.
				$currentPrice = floatval($salePrice);
			} else {
				$currentPrice = floatval(get_post_meta($productId, '_regular_price', true));
				$currentPrice = apply_filters('wdm_csp_regular_price', $currentPrice, $productId);
			}
			return $currentPrice;
		}


		/**
		 * This method returns sale price if sale prie for the product id
		 * is specified && Sale is active according to the sale schedule
		 * method also returns sale price when, Sale Price is Valid &
		 * - Only Sale start date is mentioned and is passed.
		 * - Only Sale end date is mentioned and is not passed.
		 * - No sale schedule is specified.
		 * else returns false
		 *
		 * @param int $productId
		 * @return mixed
		 */
		public function wdmGetSalePrice( $productId) {
			$salePrice      = wc_format_decimal(get_post_meta($productId, '_sale_price', true));
			$salePrice      = apply_filters('wdm_csp_sale_price', $salePrice, $productId);
			$SalePriceValid = is_numeric($salePrice);
			$saleFrom       = get_post_meta($productId, '_sale_price_dates_from', true);
			$saleUpto       = get_post_meta($productId, '_sale_price_dates_to', true);
			$timeNow        = current_time('timestamp');

			if (!$SalePriceValid) {
				return false;
			}
			
			if (empty($saleFrom) && !empty($saleUpto)) {
				if ($timeNow<( $saleUpto+86400 )) {
					return $salePrice;
				}
				return false;
			}
			if (!empty($saleFrom) && empty($saleUpto)) {
				if ($timeNow>=$saleFrom) {
					return $salePrice;
				}
				return false;
			}
			if (!empty($saleFrom) && !empty($saleUpto)) {
				if ($timeNow>=$saleFrom && $timeNow<( $saleUpto+86400 )) {
					return $salePrice;
				}
				return false;
			}
			return $salePrice;
		}

		public function discountCalculation( $regular_price, $newPrice) {
			return ( $regular_price ) - ( round(( $newPrice * $regular_price ), wc_get_price_decimals()) / 100 );
		}

		/**
		* Gets the Specific pricing for that product for that quantity.
		* Returns the array for the Specific price for the quantity of that
		* product.
		 *
		* @param int $quantity quantity of product.
		* @param array $priceArray Quantity pricing array
		* @param object $product Product object.
		* @return array Specific Price on quantity.
		*/
		public function priceForSearchQuantity( $quantity, $priceArray, $product) {
			if (count($priceArray) == 0) {
				return false;
			}

			foreach ($priceArray as $a) {
				if ($a->min_qty == $quantity) {
					return $this->getCSPArray($a, $a->price, $product);
				}
			}
		}

		/**
		* Returns the array for the Specific price for the quantity of that
		* product.
		 *
		* @param object $a pricing quantity based.
		* @param float $price Price for the selected quantity.
		* @param object $product Product Object.
		* @return array $cspPrice Specific Price on quantity.
		*/
		public function getCSPArray( $a, $price, $product) {
			$cspPrice               = array();
			$cspPrice['price']      = $price;
			$cspPrice['min_qty']    = $a->min_qty;
			$cspPrice['price_type'] = $a->price_type;
			$cspPrice['product_id'] = $product->get_id();
			if (property_exists($a, 'cat_slug')) {
				$cspPrice['price_set'] = $a->price_set;
				$cspPrice['source']    = $a->cat_slug;
			} else {
				$cspPrice['source'] = 'Direct';
			}
			return $cspPrice;
		}

		/**
		* Returns the  attachments, revisions, or sub-pages, possibly by
		* product
		 *
		* @param object $product Product to get variations of.
		* @return object details associated with the product.
		*/
		public function getVariationId( $product) {
			return $product->get_children();
		}

		/**
		* Returns the HTML for error display.
		 *
		* @param string $message  message.
		* @param int $rule_id Rule Id.
		* @param bool $error true if some error.
		* @return string HTML for error.
		*/
		private function selectionListMessage( $message, $rule_id, $error = true) {
			global $wpdb;
			$divClass         = 'updated';
			$option_selected  = '';
			$query_log_result = '';
			
			$csp_ajax = new \cspAjax\WdmWuspAjax();

			if (! empty($rule_id)) {
				$query_log_result = $wpdb->get_row($wpdb->prepare('SELECT rule_title, rule_type FROM ' . $wpdb->prefix . 'wusp_rules WHERE rule_id = %d', $rule_id));
				if (null!=$query_log_result) {
					$option_selected = strtolower($query_log_result->rule_type);
				}
			}

			if ($error) {
				$divClass = 'error';
			}

			$productResultObject = $this->getProductResultArray($option_selected, $query_log_result, $rule_id, $csp_ajax);
			$update_div          = '<div rule_id="' . $rule_id . '" class="' . $divClass . ' wdm_result"><p>' . $message . '</p></div>';
			$updateResult        = array(
				'product_result' => $productResultObject['product_result'],
				'update_div' => $update_div
			);

			return $updateResult;
		}


		public function wdmGetSiteUserById( $usersList, $id) {
			foreach ($usersList as $user) {
				if ($user->id==$id) {
					return $user->user_login;
				}
			}
		}

		/**
		* Get the display names form the associated entities table of subrule.
		* User-names, Roles, Group-names.
		 *
		* @param string $option_selected rule type selected.
		* @param array $selectedEntities Associated entities array for the
		* subrules of that rule.
		* @return array $selectionValues array of display names.
		*/
		public function getSelectionValues( $option_selected, $selectedEntities) {
			global $wpdb;
			$selectionValues = array();
			$usersList       =$this->getSiteUserIdNamePairs();
			if ( 'customer'==$option_selected) {
				foreach ($selectedEntities as $value) {
					$selectionValues[$value] = $this->wdmGetSiteUserById($usersList, $value);
				}
			} elseif ( 'role'==$option_selected) {
				$availableRoles = array_reverse(get_editable_roles());
				$roleKeys       = array();
				if (! empty($availableRoles)) {
					$roleKeys = array_keys($availableRoles);
				}
				foreach ($selectedEntities as $value) {
					if (in_array($value, $roleKeys)) {
						$selectionValues[$value] = translate_user_role($availableRoles[$value]['name']);
					}
				}
			} elseif ( 'group'==$option_selected) {
				foreach ($selectedEntities as $value) {
					$result                  =  $wpdb->get_row($wpdb->prepare('SELECT name FROM ' . $wpdb->prefix . 'groups_group WHERE group_id = %d', $value));
					$selectionValues[$value] = $result->name;
				}
			}
			return $selectionValues;
		}

		/**
		* Gets the subrules for the Rules.
		* For Product-Pricing tab,
		* Display the Products and Rule-type selection.
		* Display the Set Prices module.
		* Gets the Product details for the rule selection.
		* Get the Rules page template
		 *
		* @param string $option_selected rule type selected.
		* @param array $query_log_result Rows for the rule_id.
		* @param int $query_log_id rule-id
		* @return array $product_result array of Products titles, details,
		* rules info.
		*/
		public function loadExistingDetails( $option_selected, $query_log_result, $query_log_id) {
			$productResultObject = '';
			$csp_ajax            = new \cspAjax\WdmWuspAjax();
			if (! empty($option_selected) && ! is_wp_error($query_log_result)) {
				$productResultObject = $this->getProductResultArray($option_selected, $query_log_result, $query_log_id, $csp_ajax);
				$csp_ajax->displayTypeSelection($option_selected, $productResultObject['selectedEntities'], $productResultObject['selectedProducts']);
			}

			return !empty($productResultObject) ? $productResultObject['product_result'] : $productResultObject;
		}

		/**
		* Gets the subrules for the Rules.
		* Get the associated-entities for the subrules.
		* For Product-Pricing tab,
		* Gets the Product Details titles
		* Gets the product names , product variation names.
		* Gets the Product details for the rule selection.
		* Get the Rules page template
		 *
		* @param string $option_selected rule type selected.
		* @param array $query_log_result Rows for the rule_id.
		* @param int $query_log_id rule-id
		* @return array $productResultObject array of Products titles, Seleccted entities and Selected produts.
		*/
		public function getProductResultArray( $option_selected, $query_log_result, $query_log_id, $csp_ajax) {
			global $subruleManager;
			$product_result      = array();
			$subruleInfo         = array();
			$selectedEntities    = array();
			$selectedProducts    = array();
			$selectionValues     = array();
			$productResultObject = array();

			if ( null!=$query_log_id) {
				$subruleInfo = $subruleManager->getAllSubrulesInfoForRule($query_log_id);
			}
					
			if (empty($subruleInfo)) {
				return;
			}

			//Find out all entities and products which were selected
			if (is_array($subruleInfo)) {
				foreach ($subruleInfo as $singleSubrule) {
					if (! in_array($singleSubrule[ 'associated_entity' ], $selectedEntities)) {
						$selectedEntities[] = $singleSubrule[ 'associated_entity' ];
					}

					$selectionValues = self::getSelectionValues($option_selected, $selectedEntities);

							
					if (! in_array($singleSubrule[ 'product_id' ], $selectedProducts)) {
						$selectedProducts[] = $singleSubrule[ 'product_id' ];
					}
				}
			}
			// var_dump($product_result[ 'title_name' ]);exit;
			$product_result[ 'title_name' ] = $csp_ajax->getProductDetailTitles($option_selected);
			$product_name_list              = array();
			$product_list                   =  $selectedProducts;

			$product_name_list = $this->getProductNameList($product_list);

			$product_result[ 'value' ] = $csp_ajax->getProductDetailList($option_selected, $product_name_list, $selectionValues, $subruleInfo);

			$product_result[ 'query_input' ] = $csp_ajax->getQueryInput($query_log_result->rule_title);
			$productResultObject             = array(
				'selectedEntities' => $selectedEntities,
				'selectedProducts' => $selectedProducts,
				'product_result' => $product_result,
			);

			return $productResultObject;
		}

		/**
		* Gets the Product Details titles
		* Gets the product names , product variation names.
		* Gets the Product details for the rule selection.
		 *
		* @return array $product_name_list array of Products titles with all attributes for variations(if available).
		*/
		public function getProductNameList( $product_list) {
			$product_name_list = array();
			foreach ($product_list as $single_product_id) {
				if (get_post_type($single_product_id) == 'product_variation') {
					$parent_id        = wp_get_post_parent_id($single_product_id);
					$product_title    = get_the_title($parent_id);
					$variable_product = new \WC_Product_Variation($single_product_id);
					$attributes       = $variable_product->get_variation_attributes();

					//get all attributes name associated with this variation
					$attribute_names = array_keys($variable_product->get_attributes());

					$pos = 0; //Counter for the position of empty attribute
					foreach ($attributes as $key => $value) {
						if (empty($value)) {
							$attributes[$key] = 'Any ' . $attribute_names[$pos++];
						}
					}

					$product_title .= '-->' . implode(', ', $attributes);

					$product_name_list[ $single_product_id ] = $product_title;
				} else {
					$product_name_list[ $single_product_id ] = get_the_title($single_product_id);
				}
			}

			return $product_name_list;
		}

		/**
		 * Displays the message for the Rules page.
		 *
		 * @param bool $ruleCreated true if rule created else false.
		 * @param string $message error messages from rule and subrules manager.
		 * @param array $selection_entry_list insertion ids of pricing mappings.
		 * @param int $rule_id Rule Id.
		 * @param int $current_query_id rule id.
		 */
		private function sendSelectionResult( $ruleCreated, $message, $selection_entry_list, $rule_id, $current_query_id = null) {

			if (! empty($selection_entry_list)) {
				if (! $ruleCreated) {
					return self::selectionListMessage($message, $rule_id);
				} else {
					$message = trim($message);
					if (empty($message)) {
						if (empty($current_query_id)) {
							/* translators:  */
							$message = sprintf(__('Rule created successfully. Click %1$s here %2$s to add new rule.', 'customer-specific-pricing-for-woocommerce'), '<a href="admin.php?page=customer_specific_pricing_single_view&tabie=product_pricing">', '</a>');
						} else {
							/* translators:  */
							$message = sprintf(__('Rule updated successfully. Click %1$s here %2$s to add new rule.', 'customer-specific-pricing-for-woocommerce'), '<a href="admin.php?page=customer_specific_pricing_single_view&tabie=product_pricing">', '</a>');
						}
					}

					return self::selectionListMessage($message, $rule_id, false);
				}
			} else {
				return self::selectionListMessage(__('Values may be improper.', 'customer-specific-pricing-for-woocommerce'), $rule_id);
			}
		}

		/**
		* Update the Progress in options table.
		 *
		* @param string $option_name rule-type.
		* @param int $row_count intially zero
		* @param int $total_process_count product of count of selections and * products
		*/
		private static function updateProgressOption( $count, $total_rows, $option_name) {
			if ( 0===$count) {
				update_option($option_name . '__value', '10');
				update_option($option_name . '_status', __('Initializing', 'customer-specific-pricing-for-woocommerce'));
			} elseif ($count === $total_rows) {
				update_option($option_name . '_value', '95');
			} elseif ($count > abs($total_rows * 0.75)) {
				update_option($option_name . '_value', '80');
			} elseif ($count > abs($total_rows * 0.5)) {
				update_option($option_name . '_value', '60');
			} elseif ($count > abs($total_rows * 0.4)) {
				update_option($option_name . '_value', '40');
			} elseif ($count > abs($total_rows * 0.2)) {
				update_option($option_name . '_value', '22');
			} else {
				update_option($option_name . '_value', '15');
			}
		}

		/**
		* If already the pricing pair doesn't exist add new pair in DB.
		 *
		* @param string $selection_type rule-type.
		* @param int $selection_id Selection id.
		* @param int $product_id Product Id.
		* @param float $price Price of Product.
		* @param int $flat_or_discount 2 for % and 1 for flat.
		* @param int $quantity Quantity for Product.
		* @return int $insert_id insertion id.
		*/
		private static function setSelectionPair( $selection_type, $selection_id, $product_id, $price, $flat_or_discount, $quantity) {
			//Insert the result
			$insert_id = -1;

			if ('customer' === $selection_type) {
				$insert_id = \WdmCSP\WdmWuspAddDataInDB::insertPricingInDb($selection_id, $product_id, $flat_or_discount, $price, $quantity);
			} elseif ( 'role'===$selection_type) {
				$insert_id = \WdmCSP\WdmWuspAddDataInDB::insertRoleProductMappingInDb($selection_id, $product_id, $flat_or_discount, $price, $quantity);
			} elseif ( 'group'===$selection_type) {
				$insert_id = \WdmCSP\WdmWuspAddDataInDB::insertGroupProductPricingInDb($selection_id, $product_id, $flat_or_discount, $price, $quantity);
			}

			return $insert_id;
		}

		/**
		* Updates the product pricing details in DB.
		 *
		* @param string $selection_type rule-type.
		* @param int $existing_id id for the product-pricing
		* @param int $selection_id Selection id.
		* @param int $product_id Product Id.
		* @param float $price Price of Product.
		* @param int $flat_or_discount 2 for % and 1 for flat.
		* @param int $quantity Quantity for Product.
		*/
		private static function updateSelectionPair( $selection_type, $existing_id, $selection_id, $product_id, $price, $flat_or_discount, $quantity) {
			$updateStatus =false;
			if ('customer'===$selection_type ) {
				$updateStatus =\WdmCSP\WdmWuspUpdateDataInDB::updateUserPricingInDb($existing_id, $price, $flat_or_discount, $quantity, $product_id);
			} elseif ( 'role'===$selection_type) {
				$updateStatus =\WdmCSP\WdmWuspUpdateDataInDB::updateRolePricingInDb($existing_id, $selection_id, $product_id, $price, $flat_or_discount, $quantity);
			} elseif ( 'group'===$selection_type) {
				$updateStatus =\WdmCSP\WdmWuspUpdateDataInDB::updateGroupPricingInDb($existing_id, $selection_id, $product_id, $price, $flat_or_discount, $quantity);
			}
			return $updateStatus;
		}

		public function searchAllOccurrences( $arr, $needle) {
			$array_keys = array();
			foreach ($arr as $key => $value) {
				if ($value == $needle) {
					array_push($array_keys, $key);
				}
			}
			return $array_keys;
		}
		/**
		* Compares the product/category pricing mapping from admin side and entries stored in database.
		* Returns the entry which is not stored in database.
		 *
		* @param array $array1 array of pricing mapping present/set on admin side.
		* @param array $array2 array of pricing mapping in database previously.
		* @param string $userType user Type
		* @param bool true if category set if false check for product.
		* @return array $newArray new entry which is not in database.
		*/
		public function multiArrayDiff( $array1, $array2, $userType, $cat = false) {
			$res      = false;
			$newArray = array();
			foreach ($array2 as $key => $val) {
				if ($cat) {
					$res = self::compareArrayCategory($array1, $val, $userType);
				} else {
					$res = self::compareArrayProduct($array1, $val, $userType);
				}
				if (!$res) {
					$newArray[$key] = $val;
				}
			}
			return $newArray;
		}
		/**
		* Compare the array of product price mapping displayed on admin and stored
		* in database are equal or not.
		 *
		* @param array $arr1 array of pricing mapping present/set on admin
		* side.
		* @param array $arr2 array of pricing mapping in database previously.
		* @param string $userType user Type
		* @return bool true if equal, false if not.
		*/
		public function compareArrayProduct( $arr1, $arr2, $userType) {
			foreach ($arr1 as $val) {
				if ($val[$userType] == $arr2[$userType] && $val['min_qty'] == $arr2['min_qty']) {
					return true;
				}
			}
			return false;
		}

		/**
		* Compare the array of category price mapping displayed on admin and stored
		* in database are equal or not.
		 *
		* @param array $arr1 array of pricing mapping present/set on admin
		* side.
		* @param array $arr2 array of pricing mapping in database previously.
		* @param string $userType user Type
		* @return bool true if equal, false if not.
		*/
		public function compareArrayCategory( $arr1, $arr2, $userType) {
			foreach ($arr1 as $val) {
				if ($val[$userType] == $arr2[$userType] && $val['min_qty'] == $arr2['min_qty'] && $val['cat_slug'] == $arr2['cat_slug']) {
					return true;
				}
			}
			return false;
		}

		public function evaluateAndRemoveCSPPrice( $optionType, $selectionName, $recordData, $cspSource, $productId, $minQty, $ruleId) {
			if ('--' != $ruleId) {
				global $ruleManager, $subruleManager;
				$ruleType      = $ruleManager->getRuleType($ruleId);
				$associatedEnt = substr($cspSource, 16);
				$subruleManager->deleteSubruleByRecordData($ruleId, $productId, $ruleType, $associatedEnt, $minQty);
				$this->deleteCSPPrice($cspSource, $productId, $minQty, $associatedEnt, $ruleType);
			} else {
				$this->deleteCSPPrice($cspSource, $productId, $minQty, $selectionName);
			}
			unset($optionType);
			unset($recordData);
		}

		/**
		 * Removes the CSP price applied on a particular product
		 * (from wp_wusp_user_pricing_mapping, wusp_role_pricing_mapping,
		 * wp_group_product_pricing_mapping tables).
		 *
		 * @param string $source    Possible values
		 *                          Direct
		 *                          wdm-csp-role-{$role_name}
		 *                          wdm-csp-group-{$group-id}
		 * @param int    $productId Product Id.
		 * @param int    $minQty    Minimum quantity.
		 * @param string $selectionName Associated entity in case of rule based
		 *                              pricing or user Id in case of Direct customer
		 *                              specific pricing.
		 * @param string $ruleType  Rule Type. Possible values:
		 *                          'Customer' or 'Role' or 'Group'.
		 */
		public function deleteCSPPrice( $source, $productId, $minQty, $selectionName, $ruleType = '') {
			if ('Direct' == $source || 'Customer' == $ruleType) {
				\WuspDeleteData\WdmWuspDeleteData::deleteCustomerMapping('user_id', 'wusp_user_pricing_mapping', '%d', strtolower($ruleType), $selectionName, $productId, $minQty);
			} elseif (strpos($source, 'wdm-csp-role-') !== false || 'Role' == $ruleType) {
				$role = ( 'Role' == $ruleType ) ? $selectionName : substr($source, 13);
				\WuspDeleteData\WdmWuspDeleteData::deleteCustomerMapping('role', 'wusp_role_pricing_mapping', '%s', strtolower($ruleType), $role, $productId, $minQty);
			} elseif (strpos($source, 'wdm-csp-group-') !== false || 'Group' == $ruleType) {
				$groupId = ( 'Group' == $ruleType ) ? $selectionName : substr($source, 14);
				\WuspDeleteData\WdmWuspDeleteData::deleteCustomerMapping('group_id', 'wusp_group_product_price_mapping', '%d', strtolower($ruleType), $groupId, $productId, $minQty);
			}
		}


		/**
		 * Get the product SKU by product ID.
		 *
		 * @param int $product_id   The product ID.
		 *
		 * @return string           Product SKU if exists or empty string if
		 *                          SKU doesn't exist.
		 */
		public function getProductSku( $product_id) {
			$product = wc_get_product($product_id);
			$sku     = '';

			if ($product) {
				$sku = $product->get_sku();
			}

			return apply_filters('csp_product_sku', $sku, $product);
		}

		/**
		 * Returns the CSP set on for a simple product or variation product.
		 *
		 * @param $userId       int     User ID.
		 * @param $productId    int     Simple Product ID or variation product
		 *                              ID.
		 * @param $qty          int     Product Quantity for which CSP price needs
		 *                              be retrieved. If it is set to '0', CSP is
		 *                              returned for all the quantities of a
		 *                              particular product for a user.
		 *
		 * @return array        Returns the CSP price in array format.
		 */
		public function retrieveCSPPriceForProduct( $userId, $productId, $qty = 0) {
			$cspPrice = array();

			if ($userId > 0 && get_userdata($userId)) {
				if ($qty > 0) {
					/**
					 * Retrieves the CSP price in array format.
					 * array (
					 *      'quantity' => 'price'
					 * )
					 */
					$cspPrice[$qty] = \WuspSimpleProduct\WuspCSPProductPrice::getDBPrice($productId, 0, $qty, $userId);
				} elseif ($qty <=0) {
					/**
					 * Retrieves CSP price in array format for all quantities.
					 */
					$cspPrice = \WuspSimpleProduct\WuspCSPProductPrice::getQuantityBasedPricing($productId, $userId);
				}
			}
			return $cspPrice;
		}

		/**
		 * This method checks if plugin is activated on site
		 *
		 * @param [type] $plugin - Plugin Name or a slug to check if its activated on site
		 * @return bool true if $plugin is activated on site or sitewide in multisite setup.
		 */
		public function wdmIsActive( $plugin) {
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
		 * This function is used to retrive the userId-userName pairs from the database,
		 * the function uses custom query to fetch the user data from both simple wp instance
		 * & from the wp site in the wp-network setup.
		 *
		 * @since 4.3.0
		 * @return array - aray of object containing userid-username pairs of the site
		 */
		public function getSiteUserIdNamePairs() {
			global $wpdb;
			if (!is_multisite()) {
				$usersList =$wpdb->get_results('SELECT id, user_login FROM ' . $wpdb->prefix . 'users');
			} else {
				$capabilities =$wpdb->prefix . 'capabilities';
				$usersList    =$wpdb->get_results($wpdb->prepare('SELECT id, user_login FROM (SELECT id,user_login FROM ' . $wpdb->base_prefix . 'users u JOIN ' . $wpdb->base_prefix . 'usermeta um ON u.id=um.user_id WHERE um.meta_key=%s) AS csp_user_query', $capabilities));
			}
			
			return empty($usersList)?array():$usersList;
		}


		/**
		 * To store array data in csv format to the diretory specified.
		 * * Extention is added to the fileName sent as the parameter
		 * * File as per the file name is created in the directory specified.
		 * * Each record in the array of records is stored into the file & file closed.
		 *
		 * @since 4.3.0
		 * @param string $csvName - csv File Name without an extension
		 * @param array $recordsToStore - An array of arrays containing the row to be stored in csv
		 * @param string $dirRelatedToBaseDir - Name of the directory in wp-content/uploads/
		 * @return void
		 */
		public function wdmSaveCSV( $csvName, $recordsToStore, $dirRelatedToBaseDir) {
			$file       ='/' . $csvName . '.csv';
			$reportsDir = wp_upload_dir()['basedir'] . '/' . $dirRelatedToBaseDir;
			$file       =$reportsDir . $file;
			$fh         =fopen($file, 'w');
			foreach ($recordsToStore as $record) {
				\fputcsv($fh, $record);
			}
			\fclose($fh);
		}

		/**
		 * When called, this method fetches all the product categories
		 * & store them in the member variable $categories, this can be later used
		 * as the cache & allow to reduce repeated queries to the database.
		 *
		 * @since 4.4.3
		 * @return void
		 */
		public static function retriveAndStoreAllProductCats() {
			$productCatAttr	= array ('taxonomy' => 'product_cat');
			$categories 	= get_terms($productCatAttr);
			$catArray       = array();
			if (!empty($categories) || !is_wp_error($categories) ) {
				foreach ($categories as $category) {
					$catArray[$category->term_id] = $category;
				}
				self::$categories = $catArray;
			}
		}

		/**
		 * This method checks if the categories for the product are cached,
		 * id cached returns the array of category objects else returns an 
		 * empty array
		 * 
		 * @since 4.4.3
		 * @param object $product
		 * @return array
		 */
		public function getCategoriesIfExistInCache( $product) {
			$catIds   = $product->get_category_ids();
			$catArray = array();
			if (!empty($catIds)) {
				foreach ($catIds as $termId) {
					if (isset(self::$categories[$termId])) {
						$catArray[] = self::$categories[$termId];
					}
				}	
			}
			return $catArray;
		}
	}
}
$GLOBALS['cspFunctions'] = WdmWuspFunctions::getInstance();
