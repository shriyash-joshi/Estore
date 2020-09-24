<?php

namespace WuspSimpleProduct\WrspSimpleProduct;

include_once(CSP_PLUGIN_URL . '/includes/rules/wdm-csp-rule-role.php');
/**
 * Display and apply the role specific price for simple product
 */
if (! class_exists('WdmWuspSimpleProductsRsp')) {

	/**
	 * Class to Display & Process data of Simple Products for Role Specific Pricing
	 */
	class WdmWuspSimpleProductsRsp {
	

		/**
		* Gets the licensing information from database.
		* If return value is available then:
		* 1: Action for the Role Specific Pricing tab for each product.
		* 2: Action for saving the data of current selection in database.
		*/
		public function __construct() {
			//global $wdmPluginDataCSP;

			// License check
				add_action('wdm_add_after_simple_csp', array( $this, 'printRoleTab' ), 5);
				//including the template for group specific pricing tab
			// Removed as the woocommerce_product_write_panels is deprecated instead used woocommerce_product_data_panels
			// add_action('woocommerce_product_write_panels', array( $this, 'cspDisplayUserRole' ), 10);
				add_action('woocommerce_product_data_panels', array( $this, 'cspDisplayUserRole' ), 10);

				add_action('woocommerce_process_product_meta_simple', array( $this, 'addRolePriceMappingInDb' ), 10, 1);
			// }
		}

		/*
		* displays user role and prices stored in database for the specific product
		*/
		/**
		 * Shows Role Specific Pricing tab on Product create/edit page
		 *
		 * This tab shows options to add price for specific roles
		 * while creating a product or editing the product.
		 * Display the GUI for Role Specific Pricing.
		 */
		public function printRoleTab() {
			?>

			<h3 class="wdm-heading"><?php esc_html_e('Role Based Pricing', 'customer-specific-pricing-for-woocommerce'); ?></h3>
			<div>
				<div id="role_specific_pricing_tab_data">
					<table cellpadding="0" cellspacing="0" class="wc-metabox-content wdm_simple_product_rsp_table" style="display: table;">
						<thead class="role_price_thead">
							<tr>
								<th style="text-align: left">
									<?php esc_html_e('User Role', 'customer-specific-pricing-for-woocommerce'); ?>
								</th>
								<th style="text-align: left">
									<?php esc_html_e('Discount Type', 'customer-specific-pricing-for-woocommerce'); ?>
								</th>
								<th>
									<?php esc_html_e('Min Qty', 'customer-specific-pricing-for-woocommerce'); ?>
								</th>
								<th colspan=3>
									<?php esc_html_e('Value', 'customer-specific-pricing-for-woocommerce'); ?>
								</th>
							</tr>
						</thead>
						<tbody id="wdm_role_specific_pricing_tbody"></tbody>
					</table>
				</div>  </div>
			<?php
		}

		/**
		* Includes the template for the Role Specific tab.
		 *
		* @global object $post Post.
		*/
		public function cspDisplayUserRole() {
			//global $post;

			// $product = wc_get_product($post->ID);
			// if ($product->is_type('simple')) {
				include(trailingslashit(dirname(dirname(dirname(__FILE__)))) . 'templates/print_role_specific_pricing_tab_content.php');
			// }
		}//end of funtion cspDisplayUserRole

		/**
		* Inserts pricing and role-product mapping in database
		* Processing the records and performing insert, delete and update on * it
		* Delete records which are not in submission but saved in the DB and * delete the subrules associated with it
		* Update and insert records in the _role_pricing_mapping table with
		* the current selection.
		* Deactivates the subrules of that role for that Product if any
		* existed previously.
		* If pricing not set delete the record.
		* Also, delete the records from DB if all records are deleted.
		 *
		* @param int $product_id Product Id.
		* @SuppressWarnings(PHPMD)
		*/
		public function addRolePriceMappingInDb( $product_id) {
			global $wpdb;
			global $subruleManager, $cspFunctions;
			// $counter             = 0;
			$temp_array_role_qty          = array();
			// $temp_array_qty           = array();
			$deleteRoles              = array();
			$deleteQty                = array();
			$deletedValues            = array();
			$newArray                 = array();
			// $product_id          = $post->ID;
			$role_pricing_table  = $wpdb->prefix . 'wusp_role_pricing_mapping';
			$role_names          = '';
			$postArray = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

			if (isset($postArray[ 'wdm_woo_rolename' ])) {
				foreach ($postArray[ 'wdm_woo_rolename' ] as $index => $wdm_woo_role_name) {
					$newArray[] = array(
							'role'    => $wdm_woo_role_name,
							'min_qty' => $postArray[ 'wdm_woo_role_qty' ][ $index ]
						);
				}
				$role_names = "('" . implode("','", $postArray[ 'wdm_woo_rolename' ]) . "')";
				$qty = '(' . implode(',', $postArray[ 'wdm_woo_role_qty' ]) . ')';
				// $get_rem_products = $wpdb->get_col($wpdb->prepare("SELECT id FROM {$role_pricing_table} WHERE role NOT IN $role_names and min_qty NOT IN $qty and product_id=%d", $product_id));
				$existing = $wpdb->get_results($wpdb->prepare('SELECT role, min_qty FROM ' . $wpdb->prefix . 'wusp_role_pricing_mapping WHERE product_id = %d', $product_id), ARRAY_A);
				$userType = 'role';
				$deletedValues = $cspFunctions->multiArrayDiff($newArray, $existing, $userType);
				foreach ($deletedValues as $key => $value) {
					$deleteRoles[] = $existing[$key][$userType];
					$deleteQty[]   = $existing[$key]['min_qty'];
				}

				$mapping_count = count($deletedValues);
				if ($mapping_count > 0) {
					foreach ($deleteRoles as $index => $singleRole) {
						$wpdb->get_results($wpdb->prepare('DELETE FROM ' . $wpdb->prefix . 'wusp_role_pricing_mapping WHERE role = %s AND min_qty = %d AND product_id = %d', $singleRole, $deleteQty[$index], $product_id));
					}
					$subruleManager->deactivateSubrulesForRolesNotInArray($product_id, $deleteRoles, $deleteQty);
				}
			}
			if (isset($postArray[ 'wdm_woo_rolename' ]) && ! empty($postArray[ 'wdm_woo_rolename' ])) {
				//Collect all the updated and newly inserted CSP rules for the product
				$wdmSavedRules=array();

				foreach ($postArray[ 'wdm_woo_rolename' ] as $index => $wdm_woo_role_name) {
					if (isset($wdm_woo_role_name)) {
						$roleQtyPair = $wdm_woo_role_name . '-' . $postArray[ 'wdm_woo_role_qty' ][ $index ];
						if (! in_array($roleQtyPair, $temp_array_role_qty)) {
							array_push($temp_array_role_qty, $roleQtyPair);
							// array_push($temp_array_qty, $_POST[ 'wdm_woo_role_qty' ][ $index ]);
							$role_id = $wdm_woo_role_name;
							$qty = $postArray[ 'wdm_woo_role_qty' ][ $index ];
							if (isset($postArray[ 'wdm_woo_role_price' ][ $index ]) && isset($postArray[ 'wdm_role_price_type' ][ $index ]) && isset($qty) && !( $qty <= 0 )) {
								$pricing = wc_format_decimal($postArray[ 'wdm_woo_role_price' ][ $index ]);
								$price_type = $postArray[ 'wdm_role_price_type' ][ $index ];

								if (! empty($role_id) && ! empty($pricing) && ! empty($price_type)) {
									$result = $wpdb->get_results($wpdb->prepare('SELECT id FROM ' . $wpdb->prefix . 'wusp_role_pricing_mapping WHERE role = %s and min_qty = %d and product_id=%d', $wdm_woo_role_name, $qty, $product_id));
									if (count($result) > 0) {
										$update_status = $wpdb->update($role_pricing_table, array(
											'role'                   => $role_id,
											'price'                  => $pricing,
											'flat_or_discount_price' => $price_type,
											'product_id'             => $product_id,
											'min_qty'                => $qty,
										), array( 'role' => $role_id, 'product_id' => $product_id, 'min_qty' => $qty ));

										if ($update_status) {
											$subruleManager->deactivateSubrulesOfRoleForProduct($product_id, $role_id, $qty);
										}
									} else {
										$wpdb->insert($role_pricing_table, array(
											'role'                   => $role_id,
											'price'                  => $pricing,
											'flat_or_discount_price' => $price_type,
											'product_id'             => $product_id,
											'min_qty'                => $qty,
										), array(
											'%s',
											'%s',
											'%d',
											'%d',
											'%d',
										));
									}
									$wdmSavedRules[] = new \rules\RoleBasedRule($product_id, $role_id, $price_type, $qty, $pricing);
								}
							}
							if (empty($pricing)) {
								$wpdb->delete(
									$role_pricing_table,
									array(
									'role'       => $role_id,
									'product_id' => $product_id,
									'min_qty'    => $qty,
									),
									array(
									'%s',
									'%d',
									'%d',
									)
								);
								$subruleManager->deactivateSubrulesOfRoleForProduct($product_id, $role_id, $qty);
							}
						}
						// $counter ++;
					}
				}//foreach ends
				do_action('wdm_rules_saved', 'role_specific_product_rules', $wdmSavedRules);
			} else {
				$wpdb->delete(
					$role_pricing_table,
					array(
					'product_id' => $product_id,
					),
					array(
					'%d',
					)
				);
			}
		}

		//end of function addRolePriceMappingInDb

		/**
		 * Finds out price for specific role for specific product
		 *
		 * @param int $current_user_id User Id.
		 * @param int $product_id Product Id.
		 * @global object $wpdb Database object.
		 * @return mixed if price is found, price is returned. Otherwise it returns false
		 */
		public static function getPriceOfProductForRole( $current_user_id, $product_id) {
			global $wpdb;
			static $userPrices = array();

			if (isset($userPrices[$current_user_id][$product_id])) {
				return $userPrices[$current_user_id][$product_id];
			}

			//global $current_user, $wpdb;
			$user_info           = get_userdata($current_user_id);

			$sqlPrepareArgs = $user_info->roles;
			$sqlPrepareArgs[]=$product_id;
			$sqlPrepareArgs[]='price';

			$priceInfo           = $wpdb->get_row($wpdb->prepare('SELECT price, flat_or_discount_price as price_type FROM ' . $wpdb->prefix . 'wusp_role_pricing_mapping WHERE role IN(' . implode(', ', array_fill(0, count($user_info->roles), '%s')) . ' ) AND product_id=%d ORDER BY %s ASC', $sqlPrepareArgs), ARRAY_A);
			
			$price = $priceInfo['price'];
			$priceType = $priceInfo['price_type'];

			if (2==$priceType) {
				$regularPrice 	= get_post_meta($product_id, '_regular_price', true);
				$regularPrice   = apply_filters('wdm_csp_regular_price', $regularPrice, $product_id);
				if ($regularPrice >= 0) {
					$discount = floatval(( $price/100 ) * $regularPrice);
					$price = $regularPrice - $discount;
				} else {
					$userPrices[$current_user_id][$product_id] = 0;
					return $userPrices[$current_user_id][$product_id];
				}
			}
			if ($price) {
				$userPrices[$current_user_id][$product_id] = $price;
			} else {
				$userPrices[$current_user_id][$product_id] = false;
			}
			return $userPrices[$current_user_id][$product_id];
		}

		//end of function getPriceOfProductForRole

		/**
		 * Finds out qty & price pair for specific role for specific product
		 *
		 * @param int $user_id
		 * @param int $product_id
		 * @global object $wpdb
		 * @return mixed if price is found, price is returned. Otherwise it returns false
		 */
		public static function getQtyPricePairsOfProductForRole( $current_user_id, $product_id, $roleList = array()) {
			global $wpdb;
			static $pricePairs = array();

			if (isset($pricePairs[$current_user_id][$product_id])) {
				return $pricePairs[$current_user_id][$product_id];
			}

			//global $current_user, $wpdb;
			if (empty($roleList)) {
				$user_info           = get_userdata($current_user_id);
				$user_role           = $user_info->roles;
			} else {
				$user_role           = $roleList;
			}
			$sqlPrepareArgs = $user_role;
			$sqlPrepareArgs[]=$product_id;
			$rspRules			= $wpdb->get_results($wpdb->prepare('SELECT price, min_qty, flat_or_discount_price as price_type FROM ' . $wpdb->prefix . 'wusp_role_pricing_mapping WHERE role IN(' . implode(', ', array_fill(0, count($user_role), '%s')) . ') AND product_id=%d ORDER BY min_qty' , $sqlPrepareArgs));
			//Here $price are the pricing rules, TODO:Change the variable name for the better readability
			$rspRules				= apply_filters('wdm_csp_filter_rsp_rules_for_a_product', $rspRules, $user_role, $product_id);
			
			$pricePairs[$current_user_id][$product_id] = $rspRules;
			return $pricePairs[$current_user_id][$product_id];
		}


		/**
		 * Retireves all prices,role,min_qty,discount type for a product
		 * from Database.
		 *
		 * @global object $wpdb database object
		 * @param int $product_id Product Id.
		 * @return array returns array of the selections for role pricing
		 */
		public static function getAllRolePricesForSingleProduct( $product_id) {
			global $wpdb;
			$product_id = apply_filters('get_base_product_id_for_translated_product', $product_id);
			$role_price_table    = $wpdb->prefix . 'wusp_role_pricing_mapping';
			$role_product_result = $wpdb->get_results($wpdb->prepare('SELECT role, price, min_qty, flat_or_discount_price as price_type FROM ' . $wpdb->prefix . 'wusp_role_pricing_mapping WHERE product_id=%d ORDER BY `id` ASC', $product_id));

			if ($role_product_result) {
				return ( $role_product_result );
			}
		}
		// end of function getAllRolePricesForSingleProduct
	}

	//end of class
}//end of if class exists
