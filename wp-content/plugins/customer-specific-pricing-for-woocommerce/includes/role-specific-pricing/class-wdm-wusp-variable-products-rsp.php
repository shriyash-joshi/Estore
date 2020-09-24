<?php

namespace WuspVariableProduct\WrspVariableProduct;

require_once CSP_PLUGIN_URL . '/includes/rules/wdm-csp-rule-role.php';
//check whether a class with the same name exists
if (! class_exists('WdmWuspVariableProductsRsp')) {

	/**
	 * Class to Display & Process data of Variable Products for Role Specific Pricing
	 */
	//class declartion
	class WdmWuspVariableProductsRsp {
	

		/**
		* Gets the licensing information from database.
		* If return value is available then:
		* 1: Action for the Role Specific Pricing tab for each product
		* variation.
		* 2: Action for saving the data of current selection in database.
		*/
		public function __construct() {
			//global $wdmPluginDataCSP;

			// License check
				add_action('admin_enqueue_scripts', array( $this, 'adminScripts' ), 99);
				add_action('wdm_add_after_variable_csp', array( $this, 'cspDisplayUserRoleForVariableProduct' ), 4, 2);
				//add_action('woocommerce_process_product_meta_variable', array($this, 'processRolePricingPairs'), 4, 1);
				//Update role pricing whenever the 'Save Changes' button is clicked on edit variable product page.
				add_action('woocommerce_ajax_save_product_variations', array( $this, 'processRolePricingPairs' ), 10);
				add_action('save_post_product', array( $this, 'processRolePricingPairs' ), 10);
			// }
		}
		/**
		* Gets the current screen.
		* For product's edit page:
		* Add scripts and styles for the display of role specific tab for
		* every variation
		* Prepare the data and send for localization in js.
		*/
		public function adminScripts() {
			//global $post;

			$screen = get_current_screen();
			add_filter('editable_roles', array($this,'wdmListAllRoles'), 99, 1);
			ob_start();
			wp_dropdown_roles();
			$wdm_roles_dropdown_v = ob_get_contents();
			ob_end_clean();

			if (in_array($screen->id, array( 'product', 'edit-product' ))) {
					wp_enqueue_script('wdm-variable-product-mapping-v', plugins_url('/js/variable-products/wdm-role-specific-pricing.js', dirname(dirname(__FILE__))), array( 'jquery' ), CSP_VERSION);
					wp_localize_script('wdm-variable-product-mapping-v', 'wdm_variable_product_role_csp_object', array(
						'plus_image'                 => plugins_url('/images/plus-icon.png', dirname(dirname(__FILE__))),
						'minus_image'                => plugins_url('/images/minus-icon.png', dirname(dirname(__FILE__))),
						'wdm_roles_dropdown_html'    => str_replace("\n", '', $wdm_roles_dropdown_v),
						'wdm_discount_options'       => array('1'=>__('Flat', 'customer-specific-pricing-for-woocommerce'), '2'=>'%')
					));
			}
		}

		/**
		 * Filter roles in such a way that all the roles are visible
		 *
		 * @param [type] $roles
		 * @return void
		 */
		public function wdmListAllRoles( $roles) {
			unset($roles);
			global $wp_roles;
			return $wp_roles->roles;
		}

		/**
		 * Shows option to set Role-Pricing Pairs for variations
		 * Display the Role specific tab.
		 * Fetch the roles for the dropdown of roles
		 * Give the notes for the Role specific pricing tab about
		 * working mechanism.
		 *
		 * @global object $wpdb Database Object
		 * @param object $variation_data Variation Data for current variation
		 * @param object $variation Basic Variation details for current
		 * variation
		 */
		public function cspDisplayUserRoleForVariableProduct( $variation_data, $variation) {
			global $wpdb;
			$discountOptions = array('1'=>__('Flat', 'customer-specific-pricing-for-woocommerce'), '2'=>'%');
			
			$role_price_products = null;
			if (! isset($variation_data[ 'variation_post_id' ])) {
				$variation_data[ 'variation_post_id' ] = $variation->ID;
			}
			if (isset($variation_data[ 'variation_post_id' ])) {
				$role_price_products = $wpdb->get_results($wpdb->prepare('
                SELECT role, price, min_qty, flat_or_discount_price as price_type
                FROM ' . $wpdb->prefix . 'wusp_role_pricing_mapping
                WHERE product_id = %d
                Order by `id` ASC', $variation_data[ 'variation_post_id' ]));
			}
			?>
			<h3 class="wdm-heading"><?php esc_html_e('Role Based Pricing', 'customer-specific-pricing-for-woocommerce'); ?></h3>
			<div>
				<tr>
					<td colspan="2">
						<div class="wdm_user_price_mapping_wrapper">
			<?php
			add_filter('editable_roles', array($this,'wdmListAllRoles'), 99, 1);
			ob_start();
			$wdm_dropdown_content = wp_dropdown_roles();

			$wdm_roles_dropdown_v = ob_get_contents();
			// $minQtyLabel = __('Min Qty', 'customer-specific-pricing-for-woocommerce');
			$minQtyTip = __('Set minimum quantity', 'customer-specific-pricing-for-woocommerce');
			$priceTip  = __('Price will be applicable for min quantity and above.', 'customer-specific-pricing-for-woocommerce');
			ob_end_clean();
			?>
							<span style="display:none" class="wdm_hidden_user_dropdown_csp"><?php echo esc_html(base64_encode(str_replace("\n", '', $wdm_dropdown_content))); ?></span>
							<span  style="display:none" class="wdm_hidden_variation_data_csp"><?php echo esc_attr($variation_data['variation_post_id']); ?></span>
							<table style="clear:both" class="wdm_variable_product_role_usp_table" rel='<?php echo esc_attr($variation_data['variation_post_id']); ?>' id='var_tab_<?php echo esc_attr($variation_data[ 'variation_post_id' ]); ?>' >
								<thead>
								<tr>
									<th>
										<?php esc_html_e('User Role', 'customer-specific-pricing-for-woocommerce'); ?>
									</th>
									<th>
										<?php esc_html_e('Discount Type', 'customer-specific-pricing-for-woocommerce'); ?>
									</th>
									<th><span class='help_tip tips' data-tip='<?php echo esc_attr($minQtyTip); ?>'><?php echo esc_attr(__('Min Qty', 'customer-specific-pricing-for-woocommerce')); ?></span>
									</th>
									<th colspan=3><span class='help_tip tips' data-tip='<?php echo esc_attr($priceTip); ?>'><?php echo esc_attr(__('Value', 'customer-specific-pricing-for-woocommerce')); ?></span>
									</th>
								</tr>
								</thead>
								<tbody>
			<?php
			$discountType    = 1;
			$cspPercentClass = 'wdm_price csp-percent-discount';
			$allowedHtml = array('select'=>array(
				'name' => true,
				'class'=>true
				),
				'option'=>array(
				'value'=>true,
				'selected'=>true
				),
			);
			if (! empty($role_price_products)) {
				foreach ($role_price_products as $key => $rows) {
					add_filter('editable_roles', array($this,'wdmListAllRoles'), 99, 1);
					ob_start();
					$wdm_dropdown_content = wp_dropdown_roles($rows->role);
					$wdm_roles_dropdown_v = ob_get_contents();

					ob_end_clean();
					?>

										<tr>
											<td><select name='wdm_woo_rolename_v[<?php echo esc_attr($variation_data[ 'variation_post_id' ]); ?>][]' class='chosen-select'><?php echo wp_kses(str_replace("\n", '', $wdm_roles_dropdown_v), $allowedHtml); ?></select></td>
											<td><select name='wdm_role_price_type_v[<?php echo esc_attr($variation_data[ 'variation_post_id' ]); ?>][]' class='chosen-select csp_wdm_action'>
											<?php
											foreach ($discountOptions as $i => $value) {
												if ($rows->price_type == $i) {
													$discountType = $i;
													echo "<option value = '" . esc_html($rows->price_type) . "' selected>" . esc_html($value) . '</option>';
												} else {
													echo "<option value = '" . esc_attr($i) . "'>" . esc_html($value) . '</option>';
												}
											}
											if ( 'Flat'==$discountOptions[$discountType]) {
												$cspPercentClass = 'wdm_price';
											}
											?>
											</select></td>
											<td><input type="number" min = "1" size="5" class ="wdm_qty" name="wdm_woo_variation_qty_v[<?php echo esc_attr($variation_data['variation_post_id']); ?>][]" value="<?php echo esc_attr($rows->min_qty); ?>"/></td>
											<td><input type="text"  size="5" class ="<?php echo esc_attr($cspPercentClass); ?>" name="wdm_woo_variation_price_v[<?php echo esc_attr($variation_data[ 'variation_post_id' ]); ?>][]" value="<?php echo wp_kses(wc_format_localized_price($rows->price), array('span'=>array('class'=>true))); ?>"/></td>
											<td class="remove_var_csp_v" style="color:#ff0000;cursor: pointer;" tabindex="0"><img src='<?php echo esc_url(plugins_url('/images/minus-icon.png', dirname(dirname(__FILE__)))); ?>'></td>
					<?php
					if ( ( count($role_price_products) - 1 ) === $key ) {
						?>
												<td class="add_var_csp_v" style="color:#008000;cursor: pointer;"><img src='<?php echo esc_url(plugins_url('/images/plus-icon.png', dirname(dirname(__FILE__)))); ?>' tabindex="0"></td>

												<?php
					}
					?>
										</tr>
											<?php
				}
			} else {
				?>
			<tr class="single_variable_csp_row">
				<td><select name='wdm_woo_rolename_v[<?php echo esc_attr($variation_data['variation_post_id']); ?>][]' class='chosen-select'><?php echo wp_kses(str_replace("\n", '', $wdm_roles_dropdown_v), $allowedHtml); ?></select></td>
				<td><select name='wdm_role_price_type_v[<?php echo esc_attr($variation_data[ 'variation_post_id' ]); ?>][]' class='chosen-select csp_wdm_action'>
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
				<td><input type="number" min = "1" size="5" class ='wdm_qty' name="wdm_woo_variation_qty_v[<?php echo esc_attr($variation_data['variation_post_id']); ?>][]" />
				<td><input type="text" size="5" class ='wdm_price' name="wdm_woo_variation_price_v[<?php echo esc_attr($variation_data[ 'variation_post_id' ]); ?>][]" />
				<td class="remove_var_csp_v" style="color:#ff0000;cursor: pointer;" tabindex="0"><img src='<?php echo esc_url(plugins_url('/images/minus-icon.png', dirname(dirname(__FILE__)))); ?>'></td>
				<td class="add_var_csp_v" style="cursor: pointer;"><img src='<?php echo esc_url(plugins_url('/images/plus-icon.png', dirname(dirname(__FILE__)))); ?>' tabindex="0"></td>
			</tr>

				<?php
			}

			unset($wdm_roles_dropdown_v); //Unset roles dropdown variable
			?>
							</tbody>
							</table>
						</div> <!-- End Test Div -->

			</div>
			<?php
		}

		//end of function cspDisplayUserRoleForVariableProduct

		/**
		 * Saves Role-Pricing Pairs for Variable Products in the database
		 *
		 * @global object $wpdb Database object
		 * @param int $post_id ID of the Post in context
		 */
		public function processRolePricingPairs() {
			global $wpdb;
			$role_pricing_table = $wpdb->prefix . 'wusp_role_pricing_mapping';
			//unused while pushing to git
			$postArray = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
			if (! isset($postArray[ 'variable_post_id' ]) || empty($postArray[ 'variable_post_id' ])) {
				return;
			}

			$variable_post_id = $postArray['variable_post_id'];

			if (! isset($postArray['wdm_woo_rolename_v']) || ! isset($postArray['wdm_woo_variation_price_v'])|| ! isset($postArray['wdm_woo_variation_qty_v'])) {
				foreach ($variable_post_id as $single_post_id) {
					if (! isset($single_post_id)) {
						//delete record, as  all records removed for particular variation
						continue;
					}
					$var_id = (int) $single_post_id;
					$wpdb->delete($role_pricing_table, array(
						'product_id' => $var_id,
					));
				}
			} else {
				self::processRoleVariationLoop($postArray['wdm_woo_variation_price_v'], $postArray['wdm_woo_variation_qty_v'], $postArray['wdm_role_price_type_v'], $postArray[ 'wdm_woo_rolename_v' ], $postArray['variable_post_id']);
			}
		}

		//function ends

		/**
		* Process the data for specific pricing for all the variations.
		 *
		* @param array $variable_price_field Variation prices
		* @param array $variable_qty_field Variation quantities
		* @param array $variable_price_type price types(discount/flat)
		* @param array $variable_csp_role Roles
		* @param array $variable_post_id Variable Post Ids.
		*/
		private function processRoleVariationLoop( $variable_price_field, $variable_qty_field, $variable_price_type, $variable_csp_role, $variable_post_id) {
			// global $wpdb, $subruleManager, $wdmPluginDataCSP;

				$max_loop = max(array_keys($variable_post_id));

				//Loop through all variations
			for ($i = 0; $i <= $max_loop; $i ++) {
				if (! isset($variable_post_id[ $i ])) {
					continue;
				}

				$var_id = (int) $variable_post_id[ $i ];

				self::addVariationRolePriceMappingInDb($variable_csp_role, $variable_qty_field, $variable_price_field, $variable_price_type, $var_id);
			}//foreach ends
			// }//if ends
		}

		// private function removeRolePrices($user_in, $var_id)
		// {
		//     global $wpdb;

		//     $role_pricing_table = $wpdb->prefix . 'wusp_role_pricing_mapping';

		//     $remaining_products = $wpdb->get_col($wpdb->prepare("SELECT id FROM {$role_pricing_table} WHERE role NOT IN $user_in and product_id=%d", $var_id));

		//     if ($remaining_products) {
		//         $rem_id = '(' . implode(',', $remaining_products) . ')';
		//         if (sizeof($remaining_products) > 0) {
		//             $wpdb->query($wpdb->prepare("DELETE FROM {$role_pricing_table}  WHERE product_id = %d ", $rem_id));
		//         }
		//         unset($remaining_products);
		//     }
		// }

		/**
		* Inserts pricing and role-product mapping in database
		* Processing the records and performing insert, delete and update on * it
		* Delete records which are not in submission but saved in the DB and * delete the subrules associated with it
		* Update and insert records in the role_pricing_mapping table with
		* the current selection.
		* Deactivates the subrules of that customer for that Product if any * existed previously.
		* If pricing not set delete the record.
		* Also, delete the records from DB if all records are deleted.
		 *
		* @param array $variable_csp_role Roles
		* @param array $variable_price_field Variation price
		* @param array $variable_qty_field Variation quantity
		* @param array $variable_price_type price type (discount/flat)
		* @param int $var_id Variation Post Id.
		* @global object $wpdb Object responsible for executing db queries
		*
		* @SuppressWarnings(PHPMD.CyclomaticComplexity)
		* @SuppressWarnings(PHPMD.NPathComplexity)
		*
		*/
		private function addVariationRolePriceMappingInDb( $variable_csp_role, $variable_qty_field, $variable_price_field, $variable_price_type, $var_id) {
			global $wpdb, $subruleManager, $cspFunctions;
			$temp_array_role_qty = array();
			$deleteRoles         = array();
			$deleteQty           = array();
			$deletedValues       = array();
			$newArray            = array();
			$role_pricing_table  = $wpdb->prefix . 'wusp_role_pricing_mapping';
			$role_names          = '';
			$wdmSavedRules       = array();

			if (isset($variable_csp_role[$var_id])) {
				foreach ($variable_csp_role[$var_id] as $index => $wdm_woo_role_name) {
					$newArray[] = array(
							'role'    => $wdm_woo_role_name,
							'min_qty' => $variable_qty_field[$var_id][ $index ]
						);
				}

				$role_names    = "('" . implode("','", $variable_csp_role[$var_id]) . "')";
				$qty           = '(' . implode(',', $variable_qty_field[$var_id]) . ')';
				$existing      = $wpdb->get_results($wpdb->prepare('SELECT role, min_qty FROM ' . $wpdb->prefix . 'wusp_role_pricing_mapping WHERE product_id = %d', $var_id), ARRAY_A);
				$userType      = 'role';
				$deletedValues = $cspFunctions->multiArrayDiff($newArray, $existing, $userType);

				foreach ($deletedValues as $key => $value) {
					$deleteRoles[] = $existing[$key][$userType];
					$deleteQty[]   = $existing[$key]['min_qty'];
					unset($value);
				}

				$mapping_count = count($deletedValues);
				if ($mapping_count > 0) {
					foreach ($deleteRoles as $index => $singleRole) {
						$wpdb->get_results($wpdb->prepare('DELETE FROM ' . $wpdb->prefix . 'wusp_role_pricing_mapping WHERE role = %s AND min_qty = %d AND product_id = %d', $singleRole, $deleteQty[$index], $var_id));
					}
					$subruleManager->deactivateSubrulesForRolesNotInArray($var_id, $deleteRoles, $deleteQty);
				}
			}
			if (isset($variable_csp_role[$var_id]) && ! empty($variable_csp_role[$var_id])) {
				foreach ($variable_csp_role[$var_id] as $index => $wdm_woo_role_name) {
					if (isset($wdm_woo_role_name)) {
						$roleQtyPair = $wdm_woo_role_name . '-' . $variable_qty_field[ $var_id ][ $index ];
						if (! in_array($roleQtyPair, $temp_array_role_qty)) {
							array_push($temp_array_role_qty, $roleQtyPair);
							$role_id = $wdm_woo_role_name;
							$qty     = $variable_qty_field[$var_id][ $index ];

							if (isset($variable_price_field[$var_id][ $index ]) && isset($variable_price_type[$var_id][ $index ]) && isset($qty) && !( $qty <= 0 )) {
								$pricing    = wc_format_decimal($variable_price_field[$var_id][ $index ]);
								$price_type = $variable_price_type[$var_id][ $index ];

								if (! empty($role_id) && ! empty($pricing) && ! empty($price_type)) {
									$result = $wpdb->get_results($wpdb->prepare('SELECT id FROM ' . $wpdb->prefix . 'wusp_role_pricing_mapping WHERE role = %s and min_qty = %d and product_id=%d', $wdm_woo_role_name, $qty, $var_id));
									if (count($result) > 0) {
										$update_status = $wpdb->update($role_pricing_table, array(
											'role'                   => $role_id,
											'price'                  => $pricing,
											'flat_or_discount_price' => $price_type,
											'product_id'             => $var_id,
											'min_qty'                => $qty,
										), array( 'role' => $role_id, 'product_id' => $var_id, 'min_qty' => $qty ));

										if ($update_status) {
											$subruleManager->deactivateSubrulesOfRoleForProduct($var_id, $role_id, $qty);
										}
									} else {
										$wpdb->insert($role_pricing_table, array(
											'role'                   => $role_id,
											'price'                  => $pricing,
											'flat_or_discount_price' => $price_type,
											'product_id'             => $var_id,
											'min_qty'                => $qty,
										), array(
											'%s',
											'%s',
											'%d',
											'%d',
											'%d',
										));
									}
									$wdmSavedRules[] = new \rules\RoleBasedRule($var_id, $role_id, $price_type, $qty, $pricing);
								}
							}
							if (empty($pricing)) {
								$wpdb->delete(
									$role_pricing_table,
									array(
									'role'       => $role_id,
									'product_id' => $var_id,
									'min_qty'    => $qty,
									),
									array(
									'%s',
									'%d',
									'%d',
									)
								);
								$subruleManager->deactivateSubrulesOfRoleForProduct($var_id, $role_id, $qty);
							}
						}
					}
				}//foreach ends
				do_action('wdm_rules_saved', 'role_specific_variation_rules', $wdmSavedRules);
			} else {
				$wpdb->delete(
					$role_pricing_table,
					array(
					'product_id' => $var_id,
					),
					array(
					'%d',
					)
				);
				$subruleManager->deactivateSubrulesOfAllRolesForProduct($var_id);
			}
		}

		// private function removeProductRolePrice($variable_post_id)
		// {
		//     global $wpdb;

		//     $role_pricing_table = $wpdb->prefix . 'wusp_role_pricing_mapping';

		//     foreach ($variable_post_id as $single_post_id) {
		//         if (isset($single_post_id)) {
		//             $var_id = $single_post_id;
		//             $wpdb->delete(
		//                 $role_pricing_table,
		//                 array(
		//                 'product_id' => $var_id,
		//                 ),
		//                 array(
		//                 '%d',
		//                 )
		//             );
		//         }
		//     }
		// }
	}

	//end of class
}//end of if class exists
