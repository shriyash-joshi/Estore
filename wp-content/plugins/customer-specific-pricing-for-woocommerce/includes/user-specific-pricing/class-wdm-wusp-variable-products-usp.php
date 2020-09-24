<?php

namespace WuspVariableProduct;

include_once(CSP_PLUGIN_URL . '/includes/rules/wdm-csp-rule-customer.php');

//check whether a class with the same name exists
if (! class_exists('WdmWuspVariableProductsUsp')) {

	/**
	 * Class to Display & Process data of Variable Products for User Specific Pricing
	 */
	//class declartion
	class WdmWuspVariableProductsUsp {
	
		/**
		* When CSP is activated with correct license key:
		* 1: Add action for adding Customer specific pricing tab for every variation * in variable product.
		* 2: Enqueue Admin scripts.
		*
		* @SuppressWarnings(PHPMD.UnusedLocalVariable)
		*/
		public function __construct() {
			global $wdmPluginDataCSP;

				//to display variable fields on front end
				add_action('woocommerce_product_after_variable_attributes', array( $this, 'variableFields' ), 4, 3);

				//admin Necessary js file and localized variables
				add_action('admin_enqueue_scripts', array( $this, 'adminScripts' ), 99);

				// Moved to new class
				//generate Prices to be displayed on front-end
				// add_filter('woocommerce_variable_sale_price_html', array( $this, 'variationPriceFormat' ), 10, 2);
				// //wdm_new
				// add_filter('woocommerce_variable_price_html', array( $this, 'variationPriceFormat' ), 10, 2);

				//Update user pricing whenever the 'Save Changes' button is clicked on edit variable product page.
				add_action('woocommerce_ajax_save_product_variations', array( $this, 'processUserPricingPairs' ), 10);
				add_action('save_post_product', array( $this, 'processUserPricingPairs' ), 10);
			// }
		}

		/**
		 * Shows option to set User-Pricing Pairs for variations
		 * Display the customer specific tab.
		 * Fetch the user ids for the dropdown of customer names.
		 * Give the notes for the Customer specific pricing tab about
		 * working mechanism.
		 *
		 * @global object $wpdb Database Object
		 * @param int $loop
		 * @param array $variation_data Variation Data for current variation
		 * @param object $variation Basic Variation details for current variation
		 * @SuppressWarnings(PHPMD)
		 */
		public function variableFields( $loop, $variation_data, $variation) {
			global $wpdb, $wp_version;
			// Fall back for wordpress version below 4.5.0
			$show_user = 'user_login';
			if ($wp_version >= '4.5.0') {
				$show_user = 'display_name_with_login';
			}

			$discountOptions = array('1'=>__('Flat', 'customer-specific-pricing-for-woocommerce'), '2'=>'%');
			$wdm_users_dropdown_v    = '';
			$wusp_pricing_table      = $wpdb->prefix . 'wusp_user_pricing_mapping';
			$user_price_result       = null;
			if (! isset($variation_data[ 'variation_post_id' ])) {
				$variation_data[ 'variation_post_id' ] = $variation->ID;
			}
			$variation_data['variation_post_id'] = apply_filters('get_base_product_id_for_translated_product', $variation_data['variation_post_id']);
			if (isset($variation_data[ 'variation_post_id' ])) {
				$user_price_result = $wpdb->get_results($wpdb->prepare('SELECT user_id, price, min_qty, flat_or_discount_price as price_type FROM ' . $wpdb->prefix . 'wusp_user_pricing_mapping WHERE product_id = %d', $variation_data[ 'variation_post_id' ]));
			}
			?>
			<br>
			<strong><?php esc_html_e('Customer Specific Price', 'customer-specific-pricing-for-woocommerce'); ?></strong>
			<hr>
			<span class="csp-notes-title"><?php esc_html_e('Notes:', 'customer-specific-pricing-for-woocommerce'); ?></span>
			<ol class="csp-notes-content wdm-loop-<?php echo esc_html($loop); ?>">
					<li><?php esc_html_e('If a customer is added more than once, the customer-price combination first in the list will be saved, and other combinations will be removed.', 'customer-specific-pricing-for-woocommerce'); ?></li>
					<li><?php esc_html_e('If the price field is left blank, then default product price will be shown.', 'customer-specific-pricing-for-woocommerce'); ?></li>
					<li><?php esc_html_e('Please set the min qty before saving. Only then, the discounted amount will be saved and will reflect to the logged in user, role or group.', 'customer-specific-pricing-for-woocommerce'); ?></li>
					<li><?php esc_html_e('If a customer belongs to multiple groups (or roles), the least price set for the group (or role) will be applied', 'customer-specific-pricing-for-woocommerce'); ?></li>
					<li><?php esc_html_e('The priorities are as follows -', 'customer-specific-pricing-for-woocommerce'); ?>
						<ol>
							<li><?php esc_html_e('Customer Specific Price', 'customer-specific-pricing-for-woocommerce'); ?></li>
							<li><?php esc_html_e('Role Specific Price', 'customer-specific-pricing-for-woocommerce'); ?></li>
							<li><?php esc_html_e('Group Specific Price', 'customer-specific-pricing-for-woocommerce'); ?></li>
							<li><?php esc_html_e('Regular Price', 'customer-specific-pricing-for-woocommerce'); ?></li>
						</ol>
					</li>
				</ol>
			<div class="accordion csp-accordion">
				<?php
				do_action('wdm_add_before_variable_csp');
				?>
				<h3 class="wdm-heading"><?php esc_html_e('Customer Based Pricing', 'customer-specific-pricing-for-woocommerce'); ?></h3>
				<div>
					<tr>
						<td colspan="2"/>
					<div class="wdm_user_price_mapping_wrapper">
						<?php
						$args                    = array(
							'show_option_all'            => null, // string
							'show_option_none'           => null, // string
							'hide_if_only_one_author'    => null, // string
							'orderby'                    => 'display_name',
							'order'                      => 'ASC',
							'include'                    => null, // string
							'exclude'                    => null, // string
							'multi'                      => false,
							'show'                       => $show_user,
							'echo'                       => false,
							'name'                       => "wdm_woo_variation_username[{$variation_data[ 'variation_post_id' ]}][]", // string
							'id'                         => 'usr_' . $variation_data[ 'variation_post_id' ], // integer
							'class'                      => 'chosen-select', // string
							'blog_id'                    => $GLOBALS[ 'blog_id' ],
							'who'                        => null, // string
						);
						$wdm_users_dropdown_v    = wp_dropdown_users($args);
						$allowedHtml			= array('select'=>array('name'=>true,'id'=>true,'class'=>true),'option'=>array('value'=>true,'selected'=>true));
						?>
						<span style="display:none" class="wdm_hidden_user_dropdown_csp"><?php echo wp_kses(base64_encode(str_replace("\n", '', $wdm_users_dropdown_v)), $allowedHtml); ?></span>
						<span  style="display:none" class="wdm_hidden_variation_data_csp"><?php echo esc_html($variation_data['variation_post_id']); ?></span>

						<table style="clear:both" class="wdm_variable_product_usp_table" rel='<?php echo esc_attr($variation_data[ 'variation_post_id' ]); ?>' id='<?php echo 'var_tab_' . esc_attr($variation_data[ 'variation_post_id' ]); ?>' >
						<thead>
							<tr>
								<th>
									<?php esc_html_e('Customer Name', 'customer-specific-pricing-for-woocommerce'); ?>
								</th>
								<th>
									<?php esc_html_e('Discount Type', 'customer-specific-pricing-for-woocommerce'); ?>
								</th>
								<th>
									<?php esc_html_e('Min Qty', 'customer-specific-pricing-for-woocommerce'); ?>
								</th>
								<th colspan="3">
									<?php esc_html_e('Value', 'customer-specific-pricing-for-woocommerce'); ?>
								</th>
							</tr>
						</thead>
						<tbody>
							<?php
							//Add condition to check if array is not empty
							$discountType = 1;
							$cspPercentClass = 'wdm_price csp-percent-discount';
							if (! empty($user_price_result)) {
								foreach ($user_price_result as $key => $rows) {
									$args = array(
										'show_option_all'            => null, // string
										'show_option_none'           => null, // string
										'hide_if_only_one_author'    => null, // string
										'orderby'                    => 'display_name',
										'order'                      => 'ASC',
										'include'                    => null, // string
										'exclude'                    => null, // string
										'multi'                      => false,
										'show'                       => $show_user,
										'echo'                       => false,
										'name'                       => "wdm_woo_variation_username[{$variation_data[ 'variation_post_id' ]}][]", // string
										'id'                         => 'usr_' . $variation_data[ 'variation_post_id' ], // integer
										'class'                      => 'chosen-select', // string
										'blog_id'                    => $GLOBALS[ 'blog_id' ],
										'who'                        => null, // string,
										'selected'                   => $rows->user_id,
									);

									$wdm_users_dropdown_v	= wp_dropdown_users($args);
									$allowedHtml			= array('select'=>array('name'=>true,'id'=>true,'class'=>true),'option'=>array('value'=>true,'selected'=>true));
									?>
									<tr>
										<td><?php echo wp_kses(str_replace("\n", '', $wdm_users_dropdown_v), $allowedHtml); ?></td>
										<td><select name='wdm_user_price_type_v[<?php echo esc_attr($variation_data[ 'variation_post_id' ]); ?>][]' class='chosen-select csp_wdm_action'>
											<?php
											foreach ($discountOptions as $i => $value) {
												if ($rows->price_type == $i) {
													$discountType = $i;
													echo "<option value = '" . esc_attr($rows->price_type) . "' selected>" . esc_html($value) . '</option>';
												} else {
													echo "<option value = '" . esc_attr($i) . "'>" . esc_html($value) . '</option>';
												}
											}
											if ('Flat'==$discountOptions[$discountType]) {
												$cspPercentClass = 'wdm_price';
											}
											?>
										</select></td>
										<td><input type="number" min = "1" name="wdm_woo_variation_qty[<?php echo esc_attr($variation_data['variation_post_id']); ?>][]" size="5" class="wdm_qty" value="<?php echo esc_attr($rows->min_qty); ?>" /></td>
										<td><input type="text" name="wdm_woo_variation_price[<?php echo esc_attr($variation_data['variation_post_id']); ?>][]" size="5" class="<?php echo esc_attr($cspPercentClass); ?>" value="<?php echo esc_attr(wc_format_localized_price($rows->price)); ?>" /></td>
										<td class="remove_var_csp" style="color:#ff0000;cursor: pointer;" tabindex="0"><img src='<?php echo esc_url(plugins_url('/images/minus-icon.png', dirname(dirname(__FILE__)))); ?>'></td>
										<?php
										if (( count($user_price_result) - 1 )===$key) {
											?>
											<td class="add_var_csp" style="color:#008000;cursor: pointer;"><img src='<?php echo esc_url(plugins_url('/images/plus-icon.png', dirname(dirname(__FILE__)))); ?>' tabindex="0"></td>
											<?php
										}
										?>
									</tr>
									<?php
								}
							} else {
								?>
								<tr class="single_variable_csp_row">
									<td><?php echo wp_kses(str_replace("\n", '', $wdm_users_dropdown_v), $allowedHtml); ?></td>
									<td><select name='wdm_user_price_type_v[<?php echo esc_attr($variation_data[ 'variation_post_id' ]); ?>][]' class='chosen-select csp_wdm_action'>
									<?php
									foreach ($discountOptions as $i => $value) {
										if (1 == $i) {
											echo "<option value = '" . esc_attr($i) . "' selected>" . esc_html($value) . '</option>';
										} else {
											echo "<option value = '" . esc_attr($i) . "'>" . esc_html($value) . '</option>';
										}
									}
									?>
									</select></td>
									<td><input type="number" min = "1" size="5" class='wdm_qty' name="wdm_woo_variation_qty[<?php echo esc_attr($variation_data['variation_post_id']); ?>][]" /></td>
									<td><input type="text" size="5" class='wdm_price' name="wdm_woo_variation_price[<?php echo esc_attr($variation_data['variation_post_id']); ?>][]" /></td>
									<td class="remove_var_csp" style="color:#ff0000;cursor: pointer;" tabindex="0"><img src='<?php echo esc_url(plugins_url('/images/minus-icon.png', dirname(dirname(__FILE__)))); ?>'></td>
									<td class="add_var_csp" style="cursor: pointer;"><img src='<?php echo esc_url(plugins_url('/images/plus-icon.png', dirname(dirname(__FILE__)))); ?>'  tabindex="0"></td>
								</tr>

								<?php
							}
							unset($wdm_users_dropdown_v); //Unset users dropdown variable
							?>
							</tbody>
						</table>
					</div> <!-- End Test Div -->
					</td>
					</tr>
				</div>
				<?php
				do_action('wdm_add_after_variable_csp', $variation_data, $variation);
				?>
				</div>
			<?php
		}

		/**
		* Gets the current screen.
		* For product's edit page:
		* Add scripts and styles for the display of customer specific tab for every * variation
		* Add script for the validation of the fields in the customer specific tab.
		 *
		* @SuppressWarnings(PHPMD.UnusedLocalVariable)
		*/
		public function adminScripts() {
			global $post;
			$screen = get_current_screen();

			if (in_array($screen->id, array( 'product', 'edit-product' ))) {
				wp_enqueue_script('wdm_csp_functions', plugins_url('/js/wdm-csp-functions.js', dirname(dirname(__FILE__))), array('jquery'), CSP_VERSION);
				wp_localize_script(
					'wdm_csp_functions',
					'wdm_csp_function_object',
					array(
					'decimal_separator' => wc_get_price_decimal_separator(),
					'thousand_separator' => wc_get_price_thousand_separator(),
					'decimals' => wc_get_price_decimals(),
					'price_format' => get_woocommerce_price_format(),
					'currency_symbol' => get_woocommerce_currency_symbol(),
					'please_verify_regular_prices'   => __('It seems regular price is not set for variation id(s) : ', 'customer-specific-pricing-for-woocommerce'),
					'please_set_regular_prices'   => __('. Please set regular price for specified variation(s)', 'customer-specific-pricing-for-woocommerce'),
					'please_verify_prices'   => __('It seems that price/quantity mapped to users, roles or groups are not valid. Please verify it again for variation id : ', 'customer-specific-pricing-for-woocommerce'),
					)
				);
				wp_enqueue_script('jquery-ui-accordion');
				// Enqueue script for showing the customer specific pricing tab for every variation in variable product.
				wp_enqueue_script('wdm-variable-product-mapping', plugins_url('/js/variable-products/wdm-user-specific-pricing.js', dirname(dirname(__FILE__))), array( 'jquery', 'jquery-ui-accordion'), CSP_VERSION);
				// Localizing data for access in javascipt
				wp_localize_script('wdm-variable-product-mapping', 'wdm_variable_product_csp_object', array(
					'please_verify_prices'   => __('It seems that price/quantity mapped to users, roles or groups are not valid. Please verify it again.', 'customer-specific-pricing-for-woocommerce'),
					'add_new_pair'   => __('Add New Customer-Price Pair', 'customer-specific-pricing-for-woocommerce'),
					'flat'           => __('Flat', 'customer-specific-pricing-for-woocommerce'),
					'minus_image'     => plugins_url('/images/minus-icon.png', dirname(dirname(__FILE__))),
					'plus_image'    => plugins_url('/images/plus-icon.png', dirname(dirname(__FILE__)))
				));
				// Enqueue style for user pricing tab css
				wp_enqueue_style('wdm_user_pricing_tab_css', plugins_url('/css/wdm-user-pricing-tab.css', dirname(dirname(__FILE__))), array(), CSP_VERSION);
				//Edit product page valiadtions and error display.
				wp_enqueue_script('wdm_csp_edit_page', plugins_url('/js/wdm-edit-page-validations.js', dirname(dirname(__FILE__))), array('jquery'), CSP_VERSION);
				wp_localize_script(
					'wdm_csp_edit_page',
					'wdm_csp_edit_page_object',
					array(
					'please_verify_regular_prices'  => __('It seems regular price is not set for the product. Please set the regular price.', 'customer-specific-pricing-for-woocommerce'),
					'please_verify_prices' => __('It seems that price/quantity mapped to users, roles or groups are not valid. Please verify it again.', 'customer-specific-pricing-for-woocommerce'),
					'regular_price_required_for_percent_discount' => __('One of your CSP rule include % discount for which entering a regular price is required.', 'customer-specific-pricing-for-woocommerce'),
					)
				);
			}
		}

		/**
		 * Saves User-Pricing Pairs for Variable Products in the database
		 *
		 * @global object $wpdb Database object
		 * @param int $post_id ID of the Post in context
		 */
		public function processUserPricingPairs() {
			global $wpdb;
			$wusp_pricing_table = $wpdb->prefix . 'wusp_user_pricing_mapping';
			$postArray = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
			if (! isset($postArray[ 'variable_post_id' ]) || empty($postArray[ 'variable_post_id' ])) {
				return;
			}

			$variable_post_id    = $postArray[ 'variable_post_id' ];

			if (! isset($postArray['wdm_woo_variation_username']) || ! isset($postArray['wdm_woo_variation_price'])|| ! isset($postArray['wdm_woo_variation_qty'])) {
				foreach ($variable_post_id as $single_post_id) {
					if (! isset($single_post_id)) {
						//delete record, as  all records removed for particular variation
						continue;
					}
					$var_id = (int) $single_post_id;
					$wpdb->delete($wusp_pricing_table, array(
						'product_id' => $var_id,
					));
				}
			} else {
				self::processVariationLoop($postArray['wdm_woo_variation_price'], $postArray['wdm_woo_variation_qty'], $postArray['wdm_user_price_type_v' ], $postArray['wdm_woo_variation_username'], $postArray['variable_post_id']);
			}
		}

		/**
		* Process the data for specific pricing for all the variations.
		 *
		* @param array $variable_price_field Variation prices
		* @param array $variable_qty_field Variation quantities
		* @param array $variable_price_type price types(discount/flat)
		* @param array $variable_csp_user User names
		* @param array $variable_post_id Variable Post Ids.
		*
		* @SuppressWarnings(PHPMD.UnusedLocalVariable)
		*/

		private function processVariationLoop( $variable_price_field, $variable_qty_field, $variable_price_type, $variable_csp_user, $variable_post_id) {

			global $wpdb, $subruleManager, $wdmPluginDataCSP;

				$wusp_pricing_table  = $wpdb->prefix . 'wusp_user_pricing_mapping';
				$max_loop            = max(array_keys($variable_post_id));

				//Loop through all variations
			for ($i = 0; $i <= $max_loop; $i ++) {
				if (! isset($variable_post_id[ $i ])) {
					continue;
				}

				$var_id = (int) $variable_post_id[ $i ];

				self::addVariationUserPriceMappingInDb($variable_csp_user, $variable_price_field, $variable_qty_field, $variable_price_type, $var_id);
			}//foreach ends
			// }//if ends
		}
		/**
		* Deletes the records which are in DB but not in current selection.
		* Makes the new array of current selection.
		* Fetch records from the database.
		* Delete the records which are in DB but not in current selection.
		* Deletes the subrules associated with such records.
		 *
		* @param array $variable_csp_user User name
		* @param array $variable_qty_field Variation quantity
		* @param int $var_id Variation Post id.
		* @global object $wpdb Object responsible for executing db queries
		*
		* @SuppressWarnings(PHPMD.UnusedLocalVariable)
		*/
		private function removeVariationPrices( $variable_csp_user, $variable_qty_field, $var_id) {
			global $wpdb, $subruleManager, $cspFunctions;

			$wusp_pricing_table = $wpdb->prefix . 'wusp_user_pricing_mapping';
			$userType = 'user_id';
			//make a new array from the array of current records
			foreach ($variable_csp_user[$var_id] as $index => $wdmSingleUser) {
				$newArray[] = array(
						'user_id'    => $wdmSingleUser,
						'min_qty' => $variable_qty_field[$var_id][ $index ]
					);
			}
			$user_names = "('" . implode("','", $variable_csp_user[$var_id]) . "')";
			$qty = '(' . implode(',', $variable_qty_field[$var_id]) . ')';

			//Fetch records from databse
			$existing = $wpdb->get_results($wpdb->prepare('SELECT user_id, min_qty FROM ' . $wpdb->prefix . 'wusp_user_pricing_mapping WHERE product_id = %d', $var_id), ARRAY_A);

			//Seperating records to be deleted, i.e the records which are in DB but not in current submission
			$deletedValues = $cspFunctions->multiArrayDiff($newArray, $existing, $userType);
			foreach ($deletedValues as $key => $value) {
				$deleteUsers[] = $existing[$key][$userType];
				$deleteQty[]   = $existing[$key]['min_qty'];
			}

			//delete records which are not in submission but saved in the DB
			if (count($deletedValues) > 0) {
				foreach ($deleteUsers as $index => $singleUser) {
					$wpdb->get_results($wpdb->prepare('DELETE FROM ' . $wpdb->prefix . 'wusp_user_pricing_mapping WHERE user_id = %d AND min_qty = %d AND product_id = %d', $singleUser, $deleteQty[$index], $var_id));
				}
				//Deactivate subrule for deleted record
				$subruleManager->deactivateSubrulesForCustomersNotInArray($var_id, $deleteUsers, $deleteQty);
			}
		}

		/**
		* Processing the records and performing insert, delete and update on * it
		* Delete records which are not in submission but saved in the DB and * delete the subrules associated with it
		* Update and insert records in the user_pricing_mapping table with
		* the current selection.
		* Deactivates the subrules of that customer for that Product if any * existed previously.
		* If pricing not set delete the record.
		* Also, delete the records from DB if all records are deleted.
		 *
		* @param array $variable_price_field Variation price
		* @param array $variable_qty_field Variation quantity
		* @param array $variable_price_type price type (discount/flat)
		* @param array $variable_csp_user User name
		* @param int $var_id Variation Post Id.
		* @global object $wpdb Object responsible for executing db queries
		*
		* @SuppressWarnings(PHPMD.UnusedLocalVariable)
		*/
		private function addVariationUserPriceMappingInDb( $variable_csp_user, $variable_price_field, $variable_qty_field, $variable_price_type, $var_id) {

			global $wpdb, $subruleManager;
			$temp_user_qty_array    = array();
			$deleteUsers            = array();
			$deleteQty              = array();
			$deletedValues          = array();
			$newArray               = array();
			$wusp_pricing_table     = $wpdb->prefix . 'wusp_user_pricing_mapping';
			$user_names             = '';
			$wdmSavedRules       = array();

			//delete records
			if (isset($variable_csp_user[$var_id])) {
				self::removeVariationPrices($variable_csp_user, $variable_qty_field, $var_id);
			}

			//Insert and Update records
			if (isset($variable_csp_user[$var_id]) && ! empty($variable_csp_user[$var_id]) && isset($variable_qty_field[$var_id]) && ! empty($variable_qty_field[$var_id])) {
				foreach ($variable_csp_user[$var_id] as $index => $wdm_woo_user_id) {
					if (isset($wdm_woo_user_id)) {
						$userQtyPair = $wdm_woo_user_id . '-' . $variable_qty_field[$var_id][ $index ];
						if (! in_array($userQtyPair, $temp_user_qty_array)) {
							array_push($temp_user_qty_array, $userQtyPair);
							$user_id = $wdm_woo_user_id;
							$qty = $variable_qty_field[$var_id][ $index ];

							if (isset($variable_price_field[$var_id][ $index ]) && isset($variable_price_type[$var_id][ $index ]) && isset($qty) && !( $qty <= 0 )) {
								$pricing = wc_format_decimal($variable_price_field[$var_id][ $index ]);
								$price_type = $variable_price_type[$var_id][ $index ];

								if (! empty($user_id) && ! empty($pricing) && ! empty($price_type)) {
									$result = $wpdb->get_results($wpdb->prepare('SELECT id FROM ' . $wpdb->prefix . 'wusp_user_pricing_mapping WHERE user_id = %d and min_qty = %d and product_id=%d', $wdm_woo_user_id, $qty, $var_id));
									if (count($result) > 0) {
										$update_status = $wpdb->update($wusp_pricing_table, array(
											'user_id'                   => $user_id,
											'price'                  => $pricing,
											'flat_or_discount_price' => $price_type,
											'product_id'             => $var_id,
											'min_qty'                => $qty,
										), array( 'user_id' => $user_id, 'product_id' => $var_id, 'min_qty' => $qty ));

										if ($update_status) {
											$subruleManager->deactivateSubrulesOfCustomerForProduct($var_id, $user_id, $qty);
										}
									} else {
										$wpdb->insert($wusp_pricing_table, array(
											'user_id'                => $user_id,
											'price'                  => $pricing,
											'flat_or_discount_price' => $price_type,
											'product_id'             => $var_id,
											'min_qty'                => $qty,
										), array(
											'%d',
											'%s',
											'%d',
											'%d',
											'%d',
										));
									}
									$wdmSavedRules[]= new  \rules\CustomerBasedRule($var_id, $user_id, $price_type, $qty, $pricing);
								}
							}
							//If price is not set delete that record
							if (empty($pricing)) {
								$wpdb->delete(
									$wusp_pricing_table,
									array(
									'user_id'       => $user_id,
									'product_id' => $var_id,
									'min_qty'    => $qty,
									),
									array(
									'%d',
									'%d',
									'%d',
									)
								);
								$subruleManager->deactivateSubrulesOfCustomerForProduct($var_id, $user_id, $qty);
							}
						}
					}
				}//foreach ends
				do_action('wdm_rules_saved', 'customer_specific_variation_rules', $wdmSavedRules);
			} else {
				// If all records for the product are removed
				$wpdb->delete(
					$wusp_pricing_table,
					array(
					'product_id' => $var_id,
					),
					array(
					'%d',
					)
				);
				$subruleManager->deactivateSubrulesOfAllCustomerForProduct($var_id);
			}
		}

		/**
		 * Strike out default prices shown by WooCommerce with new prices
		 *
		 * @param float $price price of product
		 * @param object $product Product object
		 * @return float $price specific pricing
		 *
		 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
		 */
		public function variationPriceFormat( $price, $product) {
			global $wdmPluginDataCSP;

			if (! is_user_logged_in() || ! apply_filters('wdm_csp_change_variation_price_format', true)) {
					return $price;
			}

			if ('variable' === $product->product_type) {
				include_once(dirname(dirname(__FILE__)) . '/role-specific-pricing/class-wdm-wusp-simple-products-rsp.php');

				$product_variation = $product->get_available_variations();

				$csp_prices = self::getVariationPrices($product_variation);

				if (isset($price) && is_array($csp_prices)) {
					$csp_prices      = array_unique(array_filter($csp_prices));
					sort($csp_prices);
					$csp_price_count = count($csp_prices);
					if (!empty($csp_prices)) {
						$price           = isset($csp_prices[ 0 ]) && isset($csp_prices[ 1 ]) && ( $csp_prices[ 0 ] !== $csp_prices[ 1 ] ) ? wc_price($csp_prices[ 0 ]) . ' to ' . wc_price($csp_prices[ $csp_price_count - 1 ]) : wc_price($csp_prices[ 0 ]);

						$price = ' <ins>' . $price . '</ins>' . $product->get_price_suffix();
					}
				}
			}
			// }
			return $price;
		}

		/**
		* Get the variation specific prices for the variations.
		* Priority given to User specific then role and then group.
		* If neither of them specified return the regular price.
		 *
		* @param array $product_variation Available variations of product.
		* @return array $csp_prices specific pricing array for variations.
		*/
		private function getVariationPrices( $product_variation) {
			global $cspFunctions;
			$csp_prices = array();

			$user_id = get_current_user_id();

			foreach ($product_variation as $single_variation) {
				$variation_id = $single_variation[ 'variation_id' ];

				$csp_price           = \WdmCSP\WdmWuspGetData::getPriceOfProductForUser($user_id, $variation_id);

				$rsp_price           = \WuspSimpleProduct\WrspSimpleProduct\WdmWuspSimpleProductsRsp::getPriceOfProductForRole($user_id, $variation_id);
				$gsp_price           = false;
				/**
				 * Check if Groups is active
				 */
				if ($cspFunctions->wdmIsActive('groups/groups.php')) {
					$gsp_price = \WdmCSP\WdmWuspGetData::getPriceOfProductForGroup($user_id, $variation_id);
				}

				$regular_price = \WdmCSP\WdmWuspGetData::getRegularPriceOfTheProduct($variation_id);

				$csp_prices[] = $csp_price ? $csp_price : ( $rsp_price ? $rsp_price : ( $gsp_price ? $gsp_price : $regular_price ) );
			}
				// exit;
			return $csp_prices;
		}

		/**
		 * Function returns custom html price format variation product.
		 *
		 * @param float $price price of product
		 * @param object $product Product object
		 * @return string $cspPrice Specific price of variation html.
		 */
		public function getCustomPriceOfVariation( $price, $product) {
			global $cspFunctions;
			$cspPrice = '';

			$user_id = get_current_user_id();

			if (!is_user_logged_in() || ! apply_filters('wdm_csp_custom_html_price_variation_pro', true)) {
				return $price;
			} else {
				if ('variation' === $product->product_type) {
					$csp_price           = \WdmCSP\WdmWuspGetData::getPriceOfProductForUser($user_id, $product->variation_id);
					$rsp_price           = \WuspSimpleProduct\WrspSimpleProduct\WdmWuspSimpleProductsRsp::getPriceOfProductForRole($user_id, $product->variation_id);
					$gsp_price           = false;

					//Check if Groups is active
					if ($cspFunctions->wdmIsActive('groups/groups.php')) {
						$gsp_price = \WdmCSP\WdmWuspGetData::getPriceOfProductForGroup($user_id, $product->variation_id);
					}

					$regular_price = \WdmCSP\WdmWuspGetData::getRegularPriceOfTheProduct($product->variation_id);

					$cspPrice = $csp_price ? $csp_price : ( $rsp_price ? $rsp_price : ( $gsp_price ? $gsp_price : $regular_price ) );
				}
			}
			return ' <ins>' . $cspPrice . '</ins>';
			;
		}
	}
}
