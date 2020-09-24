<?php

namespace WuspSimpleProduct;

include_once(CSP_PLUGIN_URL . '/includes/rules/wdm-csp-rule-customer.php');

//use WuspGetData as cspGetData;

//check whether a class with the same name exists
if (! class_exists('WdmWuspSimpleProductsUsp')) {
	/**
	 * Class to Display & Process data of Simple Products for User Specific Pricing
	 */
	//class declartion
	class WdmWuspSimpleProductsUsp {
	

		/**
		* Gets the licensing information from database.
		* If return value is available then:
		* 1: Action for the Customer Specific Pricing tab for each product.
		* 2: Action for saving the data of current selection in database.
		*
		* @SuppressWarnings(PHPMD.UnusedLocalVariable)
		*/
		public function __construct() {
			global $wdmPluginDataCSP;

		   
				add_action('woocommerce_product_write_panel_tabs', array(
				$this, 'userSpecificPricingTab', ));

				// Removed as the woocommerce_product_write_panels is deprecated instead used woocommerce_product_data_panels
				// add_action('woocommerce_product_write_panels', array(
				// $this, 'userSpecificPricingTabOptions', ));

				add_action('woocommerce_product_data_panels', array(
				$this, 'userSpecificPricingTabOptions', ));

				add_action('woocommerce_process_product_meta_simple', array(
				$this, 'addUserPriceMappingInDb', ));

				// Moved to new class
				// add_filter('woocommerce_get_price', array(
				// $this, 'applyCustomPrice', ), 99, 2);
				// add_filter('woocommerce_get_price_html', array($this, 'showQuantityBasedPricing', ), 1, 2);
				// add_action('woocommerce_single_product_summary', array($this,'cspQuantityBasedProductTotal',), 31);
			// }
		}


		/**
		* Displays the total price for the product on basis of specific
		* pricings and the quantity specified.
		*
		*/
		public function cspQuantityBasedProductTotal() {
			global $product;

			// let's setup our divs
			echo sprintf('<div id="product_total_price" style="margin-bottom:20px;">%s %s<input name = "product_qty" type = "hidden"/></div>', esc_html__('Product Total:', 'woocommerce'), '<span class="price">' . esc_html($product->get_price()) . '</span>');
		}

		/**
		 * Shows User Specific Pricing tab on Product create/edit page
		 *
		 * This tab shows options to add price for specific users
		 * while creating a product or editing the product.
		 * Gets the licensing information from database.
		 * If return value is available then:
		 * Show the  tab.
		 */
		public function userSpecificPricingTab() {
			global $wdmPluginDataCSP;
			?>
			<li class="userSpecificPricingTab show_if_simple">
				<a href="#userSpecificPricingTab_data">
					<span>
					<?php esc_html_e('Customer Specific Pricing', 'customer-specific-pricing-for-woocommerce'); ?>
					</span>
				</a>
			</li>
				<?php
		}

		/**
		 * User Specific Tab Content
		 *
		 * Shows the tab content i.e. allows admin to add pair and
		 * remove user-price pair
		 *
		 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
		 */
		public function userSpecificPricingTabOptions() {
			global $post;
			//Includes the template for simple product user specific pricing tab
			include(trailingslashit(dirname(dirname(dirname(__FILE__)))) . 'templates/print_user_specific_pricing_tab_content.php');
		}

		public function getPriceTypeArray() {
			$postArray = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
			if (isset($postArray['wdm_price_type'])) {
				return $postArray['wdm_price_type'];
			} else {
				return array();
			}
		}

		/**
		* Processing the records and performing insert, delete and update on it
		* Delete records which are not in submission but saved in the DB and delete * the subrules associated with it
		* Update and insert records in the user_pricing_mapping table with the
		* current selection.
		* Deactivates the subrules of that customer for that Product if any existed
		* previously.
		* If pricing not set delete the record.
		* Also, delete the records from DB if all records are deleted.
		 *
		* @param int $product_id Product Id.
		*
		* @SuppressWarnings(PHPMD)
		*/
		public function addUserPriceMappingInDb( $product_id) {
			global $wpdb, $cspFunctions;
			global $post, $subruleManager;
			
			$postArray = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
			$isPriceEmpty = empty(get_post_meta($product_id, '_regular_price', true))? true:false;
			$temp_user_qty_array      = array();
			$deleteUsers              = array();
			$deleteQty                = array();
			$deletedValues            = array();
			$newArray                 = array();
			$wusp_pricing_table  = $wpdb->prefix . 'wusp_user_pricing_mapping';
			$user_names          = '';
			$userType = 'user_id';

			//delete records
			if (isset($postArray[ 'wdm_woo_username' ])) {
				//array of curremt records
				foreach ($postArray[ 'wdm_woo_username' ] as $index => $wdmSingleUser) {
					$newArray[] = array(
							'user_id'    => $wdmSingleUser,
							'min_qty' => $postArray[ 'wdm_woo_qty' ][ $index ]
						);
				}
				$user_names = "('" . implode("','", $postArray[ 'wdm_woo_username' ]) . "')";
				$qty = '(' . implode(',', $postArray[ 'wdm_woo_qty' ]) . ')';

				//Fetch records from database
				$existing = $wpdb->get_results($wpdb->prepare('SELECT user_id, min_qty FROM ' . $wpdb->prefix . 'wusp_user_pricing_mapping WHERE product_id = %d', $product_id), ARRAY_A);

				//Separating records to be deleted, i.e the records which are in DB but not in current submission
				$deletedValues = $cspFunctions->multiArrayDiff($newArray, $existing, $userType);
				foreach ($deletedValues as $key => $value) {
					$deleteUsers[] = $existing[$key][$userType];
					$deleteQty[]   = $existing[$key]['min_qty'];
				}

				//delete records which are not in submission but saved in the DB
				if (count($deletedValues) > 0) {
					foreach ($deleteUsers as $index => $singleUser) {
						$wpdb->get_results($wpdb->prepare('DELETE FROM ' . $wpdb->prefix . 'wusp_user_pricing_mapping WHERE user_id = %d AND min_qty = %d AND product_id = %d', $singleUser, $deleteQty[$index], $product_id));
					}
					//Deactivate subrule for deleted record
					$subruleManager->deactivateSubrulesForCustomersNotInArray($product_id, $deleteUsers, $deleteQty);
				}
			}

			//Insert and Update records from the current selection to the database.
			if (isset($postArray[ 'wdm_woo_username' ]) && ! empty($postArray[ 'wdm_woo_username' ]) && isset($postArray[ 'wdm_woo_qty' ]) && ! empty($postArray[ 'wdm_woo_qty' ])) {
				//Collect all the updated and newly inserted CSP rules for the product
				$wdmSavedRules=array();

				foreach ($postArray[ 'wdm_woo_username' ] as $index => $wdm_woo_user_id) {
					if (isset($wdm_woo_user_id)) {
						$userQtyPair = $wdm_woo_user_id . '-' . $postArray[ 'wdm_woo_qty' ][ $index ];
						if (! in_array($userQtyPair, $temp_user_qty_array)) {
							array_push($temp_user_qty_array, $userQtyPair);
							$user_id = $wdm_woo_user_id;
							$qty = $postArray[ 'wdm_woo_qty' ][ $index ];
							if (isset($postArray[ 'wdm_woo_price' ][ $index ]) && isset($postArray[ 'wdm_price_type' ][ $index ]) && isset($qty) && !( $qty <= 0 )) {
								$pricing = wc_format_decimal($postArray[ 'wdm_woo_price' ][ $index ]);
								$price_type = $postArray[ 'wdm_price_type' ][ $index ];
								if (! empty($user_id) && ! empty($pricing) && ! empty($price_type)) {
									$result = $wpdb->get_results($wpdb->prepare('SELECT id FROM ' . $wpdb->prefix . 'wusp_user_pricing_mapping WHERE user_id = %d and min_qty = %d and product_id=%d', $wdm_woo_user_id, $qty, $product_id));
									
									if (count($result) > 0) {
										if (!( $isPriceEmpty && 2==$price_type )) {
											$update_status = $wpdb->update($wusp_pricing_table, array(
												'user_id'                   => $user_id,
												'price'                  => $pricing,
												'flat_or_discount_price' => $price_type,
												'product_id'             => $product_id,
												'min_qty'                => $qty,
											), array( 'user_id' => $user_id, 'product_id' => $product_id, 'min_qty' => $qty ));
										}

										if ($update_status) {
											$subruleManager->deactivateSubrulesOfCustomerForProduct($product_id, $user_id, $qty);
										}
									} else {
										$wpdb->insert($wusp_pricing_table, array(
											'user_id'                => $user_id,
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
									$wdmSavedRules[]= new \rules\CustomerBasedRule($product_id, $user_id, $price_type, $qty, $pricing);
								}
							}
							//If price is not set delete that record
							if (empty($pricing)) {
								$wpdb->delete(
									$wusp_pricing_table,
									array(
									'user_id'       => $user_id,
									'product_id' => $product_id,
									'min_qty'    => $qty,
									),
									array(
									'%d',
									'%d',
									'%d',
									)
								);
								$subruleManager->deactivateSubrulesOfCustomerForProduct($product_id, $user_id, $qty);
							}
						}
						// $counter ++;
					}
				}//foreach ends
				do_action('wdm_rules_saved', 'customer_specific_product_rules', $wdmSavedRules);
			} else {
				// If all records for the product are removed
				$wpdb->delete(
					$wusp_pricing_table,
					array(
					'product_id' => $product_id,
					),
					array(
					'%d',
					)
				);
			}
		}
	}

}
