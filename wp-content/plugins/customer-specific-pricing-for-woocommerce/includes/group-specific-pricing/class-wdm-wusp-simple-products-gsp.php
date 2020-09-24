<?php

namespace WuspSimpleProduct\WgspSimpleProduct;

include_once(CSP_PLUGIN_URL . '/includes/rules/wdm-csp-rule-group.php');
//check whether a class with the same name exists
if (! class_exists('WdmWuspSimpleProductsGsp')) {

	/**
	 * Class to Display & Process data of Simple Products for Group Specific Pricing
	 */
	//class declartion
	class WdmWuspSimpleProductsGsp {
	

		/**
		* Gets the licensing information from database.
		* If return value is available then:
		* 1: Action for the Group Specific Pricing tab for each product.
		* 2: Action for saving the data of current selection in database.
		*/
		public function __construct() {
			//global $wdmPluginDataCSP;
			//including the below file to get the function to validate license
			include_once(dirname(dirname(__FILE__)) . '/class-wdm-wusp-get-data.php');
				// to add show the groups setting tabs after the CSP
				add_action('wdm_add_after_simple_csp', array( $this, 'printGroupTabs' ), 10);

				//including the template for group specific pricing tab
				// Removed as the woocommerce_product_write_panels is deprecated instead used woocommerce_product_data_panels
				// add_action('woocommerce_product_write_panels', array( $this, 'groupSpecificPricingTabOptions' ));
				add_action('woocommerce_product_data_panels', array( $this, 'groupSpecificPricingTabOptions' ));

				//handle the saving of groups and price pair
				add_action('woocommerce_process_product_meta_simple', array( $this, 'processGroupPricingPairs' ));
			// }
		}

		//display Groups GUI
		/**
		 * Shows Group Specific Pricing tab on Product create/edit page
		 *
		 * This tab shows options to add price for specific groups
		 * while creating a product or editing the product.
		 * Check if the groups plugin is active.
		 * If yes : Display the GUI for Group Specific Pricing.
		 * If no: Display a message to activate the groups plugin.
		 */
		public function printGroupTabs() {
			/**
			 * Check if Groups is active
			 */
			global $cspFunctions;
			if ($cspFunctions->wdmIsActive('groups/groups.php')) {
				?>
				<h3 class="wdm-heading"><?php esc_html_e('Group Based Pricing', 'customer-specific-pricing-for-woocommerce'); ?></h3>
				<div  id="group_specific_pricing_tab_data">
					<!-- <button type="button" class="button" id="wdm_add_new_group_price_pair"><?php //_e('Add New Group-Price Pair', 'customer-specific-pricing-for-woocommerce') ?></button> -->
					<div class="options_group wdm_group_pricing_tab_options">
						<table cellpadding="0" cellspacing="0" class="wc-metabox-content wdm_simple_product_gsp_table" style="display: table;">
							<thead class="groupname_price_thead">
								<tr>
									<th style="text-align: left">
										<?php esc_html_e('Group Name', 'customer-specific-pricing-for-woocommerce'); ?>
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
							<tbody id="wdm_group_specific_pricing_tbody"></tbody>
						</table>
					</div>
				</div>
				<?php
			} else {
				?>
				<h3 class="wdm-heading"><?php esc_html_e('Group Based Pricing', 'customer-specific-pricing-for-woocommerce'); ?></h3>
				<div  id="group_specific_pricing_tab_data">
					<?php esc_html_e("Activate the 'Groups' Plugin to enjoy the benefits of Group Specific Pricing.", 'customer-specific-pricing-for-woocommerce'); ?>
				</div>
				<?php
			}
		}

		/**
		 * Group Specific Tab Content
		 * If the groups plugin is active,
		 * Include the template for group specific tab.
		 * Shows the tab content i.e. allows admin to add pair and
		 * remove group-price pair
		 */
		public function groupSpecificPricingTabOptions() {
			global $cspFunctions;
			/**
			 * Check if Groups is active
			 */
			if ($cspFunctions->wdmIsActive('groups/groups.php')) {
				include(trailingslashit(dirname(dirname(dirname(__FILE__)))) . 'templates/print_group_specific_pricing_tab_content.php');
			}
		}

		/**
		 * Process meta
		 *
		 * Processes the custom tab options when a post is saved
		 * If groups plugin is active:
		 * Deletes the records which are in DB but not in current selection.
		 * Add the group-pricing pairs in database for the current selection.
		 *
		 * @param int $product_id Product Id.
		 */
		public function processGroupPricingPairs( $product_id) {
			global $wpdb,$cspFunctions;

			if ($cspFunctions->wdmIsActive('groups/groups.php')) {
				$group_product_table     = $wpdb->prefix . 'wusp_group_product_price_mapping';

				$postArray = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
				self::removeGroupProductList($product_id, $group_product_table, $postArray);

				self::addGroupProductList($product_id, $group_product_table, $postArray);
			}
		}

		/**
		* Gets the group pricing pairs of current selection.
		* Adds the current selection data in database.
		* Deletes the records from database which are removed from current selection.
		 *
		* @param int $product_id Product Id.
		* @param string $group_product_table wusp_group_product_price_mapping
		* @global object $wpdb database object.
		*/
		public function addGroupProductList( $product_id, $group_product_table, $post) {
			global $wpdb;
			$temp_group_qty_array     = array();
			if (isset($post[ 'wdm_woo_groupname' ]) && ! empty($post[ 'wdm_woo_group_qty' ])) {
				//Collect all the updated and newly inserted CSP rules for the product
				$wdmSavedRules=array();

				foreach ($post[ 'wdm_woo_groupname' ] as $index => $wdm_woo_group_id) {
					$temp_group_qty_array = self::addGroupPriceMappingInDb($product_id, $index, $wdm_woo_group_id, $group_product_table, $temp_group_qty_array, $post);
					
					$wdmSavedRules[]= new \rules\GroupBasedRule($product_id, $wdm_woo_group_id, $post['wdm_group_price_type'][$index], $post['wdm_woo_group_qty'][$index], $post['wdm_woo_group_price'][$index]);
				}//foreach ends
				do_action('wdm_rules_saved', 'group_specific_product_rules', $wdmSavedRules);
			} else {
				$wpdb->delete(
					$group_product_table,
					array(
					'product_id' => $product_id,
					),
					array(
					'%d',
					)
				);
			}
		}

		/**
		* Sets the group pricing pairs for the current selection of pricing.
		* Insert, update and delete in database.
		 *
		* @param int $product_id Product Id.
		* @param int $index Index of the current selection pair.
		* @param int $group_id Group Id for the selection.
		* @param string $group_product_table wusp_group_product_price_mapping
		* @param array $temp_group_qty_array temporary group quantity array * initially empty
		* @param object $sanitized post global object array
		* @return array $temp_group_qty_array group quantity array.
		*/
		public function addGroupPriceMappingInDb( $product_id, $index, $group_id, $group_product_table, $temp_group_qty_array, $post) {
			if (isset($group_id)) {
				$groupQtyPair = $group_id . '-' . $post[ 'wdm_woo_group_qty' ][ $index ];
				if (! in_array($groupQtyPair, $temp_group_qty_array)) {
					array_push($temp_group_qty_array, $groupQtyPair);
					self::setGroupPricingPairs($group_product_table, $product_id, $group_id, $index, $post);
				}
			}

			return $temp_group_qty_array;
		}

		/**
		* Sets the group-pricing pairs.
		* Insert the group-pricing pairs in database if not already present.
		* If present already update with the new values of current selection.
		*
		* @param int $product_id Product Id.
		* @param int $index Index of the current selection pair.
		* @param int $group_id Group Id for the selection.
		* @param 
		* @param string $group_product_table wusp_group_product_price_mapping
		*/
		public function setGroupPricingPairs( $group_product_table, $product_id, $group_id, $index, $post) {
			global $wpdb, $subruleManager;
			$qty = $post[ 'wdm_woo_group_qty' ][ $index ];
			$pricing = '';

			if (isset($post[ 'wdm_woo_group_price' ][ $index ]) && isset($post[ 'wdm_group_price_type' ][ $index ]) && isset($qty) && !( $qty <= 0 )) {
				$pricing = wc_format_decimal($post[ 'wdm_woo_group_price' ][ $index ]);
				self::insertGroupPricingPairs($group_product_table, $pricing, $product_id, $group_id, $index, $qty, $post);
			}

			if (empty($pricing)) {
				$wpdb->delete(
					$group_product_table,
					array(
					'group_id'      => $group_id,
					'product_id'    => $product_id,
					'min_qty'       => $qty,
					),
					array(
					'%d',
					'%d',
					'%d',
					)
				);
				$subruleManager->deactivateSubrulesOfGroupForProduct($product_id, $group_id, $qty);
			}
		}

		/**
		* Checks if there is a record already present in database with same * group-id as the current selection.
		* If yes update the database with the values from the current
		* selection.
		* After updating deactivate the subrules associated with such
		* records.
		* If there isn't any record of same group-id insert it in database.
		 *
		* @param string $group_product_table wusp_group_product_price_mapping
		* @param string $pricing sanitized pricing.
		* @param int $product_id Product Id.
		* @param int $index Index of the current selection pair.
		* @param int $group_id Group Id for the selection.
		*/
		public function insertGroupPricingPairs( $group_product_table, $pricing, $product_id, $group_id, $index, $qty, $post) {
			global $wpdb, $subruleManager;
			$price_type = $post[ 'wdm_group_price_type' ][ $index ];
			if (! empty($group_id) && ! empty($pricing) && ! empty($price_type)) {
				$result = $wpdb->get_results($wpdb->prepare('SELECT id FROM ' . $wpdb->prefix . 'wusp_group_product_price_mapping WHERE group_id = %d and min_qty = %d and product_id=%d', $group_id, $qty, $product_id));
				if (count($result) > 0) {
					$update_status = $wpdb->update($group_product_table, array(
						'group_id'                   => $group_id,
						'price'                  => $pricing,
						'flat_or_discount_price' => $price_type,
						'product_id'             => $product_id,
						'min_qty'                => $qty,
					), array( 'group_id' => $group_id, 'product_id' => $product_id, 'min_qty' => $qty ));

					if ($update_status) {
						$subruleManager->deactivateSubrulesOfGroupForProduct($product_id, $group_id, $qty);
					}
				} else {
					$wpdb->insert($group_product_table, array(
						'group_id'                => $group_id,
						'price'                  => $pricing,
						'flat_or_discount_price' => $price_type,
						'product_id'             => $product_id,
						'min_qty'                => $qty,
					), array(
						'%d',
						'%s',
						'%d',
						'%d',
						'%d',
					));
				}
			}
		}
		/**
		* Deletes the records which are in DB but not in current selection.
		* Makes the new array of current selection.
		* Fetch records from the database.
		* Delete the records which are in DB but not in current selection.
		* Deletes the subrules associated with such records.
		 *
		* @param int $product_id Current Product Id
		* @param string $group_product_table wusp_group_product_price_mapping
		* @global object $wpdb Object responsible for executing db queries
		*/
		public function removeGroupProductList( $product_id, $group_product_table, $post) {
			global $wpdb;
			global $subruleManager, $cspFunctions;

			$deleteGroups           = array();
			$deleteQty              = array();
			$deletedValues          = array();
			$newArray               = array();
			$user_names             = '';
			$userType               = 'group_id';
			if (isset($post[ 'wdm_woo_groupname' ])) {
				foreach ($post[ 'wdm_woo_groupname' ] as $index => $wdmSingleUser) {
					$newArray[] = array(
							'group_id'    => $wdmSingleUser,
							'min_qty' => $post[ 'wdm_woo_group_qty' ][ $index ]
						);
				}

				$user_names = "('" . implode("','", $post[ 'wdm_woo_groupname' ]) . "')";
				//$qty = "(" . implode(",", $_POST[ 'wdm_woo_group_qty' ]) . ")";

				$existing = $wpdb->get_results($wpdb->prepare('SELECT group_id, min_qty FROM ' . $wpdb->prefix . 'wusp_group_product_price_mapping WHERE product_id = %d', $product_id), ARRAY_A);

				$deletedValues = $cspFunctions->multiArrayDiff($newArray, $existing, $userType);

				foreach ($deletedValues as $key => $value) {
					$deleteGroups[] = $existing[$key][$userType];
					$deleteQty[]   = $existing[$key]['min_qty'];
					unset($value);
				}

				$mapping_count = count($deletedValues);
				if ($mapping_count > 0) {
					foreach ($deleteGroups as $index => $singleGroup) {
						$wpdb->get_results($wpdb->prepare('DELETE FROM ' . $wpdb->prefix . 'wusp_group_product_price_mapping WHERE group_id = %d AND min_qty = %d AND product_id = %d', $singleGroup, $deleteQty[$index], $product_id));
					}
					$subruleManager->deactivateSubrulesForGroupsNotInArray($product_id, $deleteGroups, $deleteQty);
				}
			}
		}
	}
}
