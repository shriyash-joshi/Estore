<?php

namespace WuspVariableProduct\WgspVariableProduct;

include_once(CSP_PLUGIN_URL . '/includes/rules/wdm-csp-rule-group.php');

if (! class_exists('WdmWuspVariableProductsGsp')) {

	/**
	 * Class to Display & Process data of Variable Products for Group Specific Pricing
	 */
	//class declaration
	class WdmWuspVariableProductsGsp {
	

		/**
		* Gets the licensing data.
		* If return value is available is then:
		* 1: Action for enqueuing admin scripts.
		* 2: Action for showing the groups specific tab for each variation
		* 2: Action for adding/saving the current selection values in
		* database.
		*/
		public function __construct() {
			//global $wdmPluginDataCSP;

			// License check
				//Save variation fields
				add_action('admin_enqueue_scripts', array( $this, 'adminScripts' ), 99);
				//add_action('woocommerce_process_product_meta_variable', array($this, 'processGroupPricingPairs'), 4, 1);
				add_action('wdm_add_after_variable_csp', array( $this, 'variableGroupFields' ), 10, 2);
				//Update group pricing whenever the 'Save Changes' button is clicked on edit variable product page.
				add_action('woocommerce_ajax_save_product_variations', array( $this, 'processGroupPricingPairs' ));
				add_action('save_post_product', array( $this, 'processGroupPricingPairs' ), 10);
			// }
		}

		/**
		* Gets the current screen.
		* For product's edit page:
		* Add scripts and styles for the display of group specific tab for
		* every variation
		*/
		public function adminScripts() {
			//global $post;
			$screen = get_current_screen();

			if (in_array($screen->id, array( 'product', 'edit-product' ))) {
				wp_enqueue_script('wdm-variable-group-product-mapping', plugins_url('/js/variable-products/wdm-group-specific-pricing.js', dirname(dirname(__FILE__))), array( 'jquery' ), CSP_VERSION);
			}
		}

		/**
		 * Shows option to set Group-Pricing Pairs for variations
		 * Checks if the group plugins is active.
		 * If yes , display the group specific pricing tab headings.
		 * Fetch the group names for the dropdown from the groups table.
		 * Show the fields in the tab.
		 * If groups plugin is not active, Display a message to activate the * same.
		 *
		 * @global object $wpdb Database Object
		 * @param object $variation_data Variation Data for current variation
		 * @param object $variation Basic Variation details for current variation
		 */
		public function variableGroupFields( $variation_data, $variation) {
			/**
			 * Check if Groups is active
			 */
			global $cspFunctions;

			if ($cspFunctions->wdmIsActive('groups/groups.php')) {
				global $wpdb;
				$discountOptions = array('1'=>__('Flat', 'customer-specific-pricing-for-woocommerce'), '2'=>'%');
				$groups_dropdown_v   = '';
				$group_product_table = $wpdb->prefix . 'wusp_group_product_price_mapping';
				$group_price_result  = null;
				if (! isset($variation_data[ 'variation_post_id' ])) {
					$variation_data[ 'variation_post_id' ] = $variation->ID;
				}
				if (isset($variation_data[ 'variation_post_id' ])) {
					$group_price_result = $wpdb->get_results($wpdb->prepare(
																			'SELECT group_id, price, min_qty, 
																			flat_or_discount_price as price_type 
																			FROM ' 
																			. $wpdb->prefix . 'wusp_group_product_price_mapping 
																			WHERE product_id = %d
																			', $variation_data[ 'variation_post_id' ]
																			)
															);
				}
				?>
				<h3 class="wdm-heading"><?php esc_html_e('Group Based Pricing', 'customer-specific-pricing-for-woocommerce'); ?></h3>
				<div>
					<tr>
						<td colspan="2"/>
					<div class="wdm_group_price_mapping_wrapper">
					
						<?php
						/**
						 * Fetch WordPress groups. And creating a Dropdown List
						 */
						$group_names     = $wpdb->get_results('SELECT group_id, name FROM ' . $wpdb->prefix . 'groups_group');
						$groups_dropdown_v   = $this->wdmGenerateGroupNameDropDown($variation_data, $group_names);
						//Group Specific tab for variations.
						?>

						<span style="display:none" class="wdm_hidden_group_dropdown_csp"><?php echo esc_html(base64_encode(str_replace( "\n", '', $groups_dropdown_v))); ?></span>
						<span  style="display:none" class="wdm_hidden_variation_group_data_csp"><?php echo esc_html($variation_data[ 'variation_post_id' ]); ?></span>
						<table style="clear:both" class="wdm_variable_product_gsp_table" rel='<?php echo esc_attr($variation_data[ 'variation_post_id' ]); ?>' id='var_g_tab_<?php echo esc_attr($variation_data[ 'variation_post_id' ]); ?>' >
							<thead>
							<tr>
								<th>
									<?php esc_html_e('Group Name', 'customer-specific-pricing-for-woocommerce'); ?>
								</th>
								<th>
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
							<tbody>
							<?php
							//Add condition to check if array is not empty
							$discountType = 1;
							$cspPercentClass = 'wdm_price csp-percent-discount';
							$allowedHtml = array(	'select'=>array(
																'name' => true,
																'id' => true,
																'class'=>true
													),
													'option'=>array(
																'value'=>true,
																'selected'=>true
													),
												);
							if (! empty($group_price_result)) {
								foreach ($group_price_result as $key => $rows) {
									/**
									 * Fetch WordPress groups. And creating a Dropdown List with preselected group name
									 */
									$groups_dropdown_v   = $this->wdmGenerateGroupNameDropDown($variation_data, $group_names, $rows);
									?>
									<tr>
										<td><?php echo wp_kses(str_replace("\n", '', $groups_dropdown_v), $allowedHtml); ?></td>
										<td><select name='wdm_group_price_type_v[<?php echo esc_attr($variation_data[ 'variation_post_id' ]); ?>][]' class='chosen-select csp_wdm_action'>
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
										<td><input type="number" min="1" size="5" name="wdm_woo_variation_group_qty[<?php echo esc_attr($variation_data[ 'variation_post_id' ]); ?>][]" value="<?php echo esc_attr($rows->min_qty); ?>" class="wdm_qty"/></td>
										<td><input type="text"  size="5" name="wdm_woo_variation_group_price[<?php echo esc_attr($variation_data[ 'variation_post_id' ]); ?>][]" value="<?php echo esc_attr(wc_format_localized_price($rows->price)); ?>" class="<?php echo esc_attr($cspPercentClass); ?>"/></td>
										<td class="remove_var_g_csp" style="color:#ff0000;cursor: pointer;" tabindex="0"><img src='<?php echo esc_url(plugins_url('/images/minus-icon.png', dirname(dirname(__FILE__)))); ?>'></td>
										<?php
										if ( ( count($group_price_result)-1 )===$key) {
											?>
											<td class="add_var_g_csp" style="color:#008000;cursor: pointer;"><img src='<?php echo esc_url(plugins_url('/images/plus-icon.png', dirname(dirname(__FILE__)))); ?>'  tabindex="0"></td>

											<?php
										}
										?>
									</tr>
									<?php
								}
							} else {
								?>
								<tr class="single_variable_csp_row">
									<td><?php echo wp_kses(str_replace("\n", '', $groups_dropdown_v), $allowedHtml); ?></td>   
									<td><select name='wdm_group_price_type_v[<?php echo esc_attr($variation_data[ 'variation_post_id' ]); ?>][]' class='chosen-select csp_wdm_action'>
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
									<td><input type="number" min = "1" size="5" name="wdm_woo_variation_group_qty[<?php echo esc_attr($variation_data[ 'variation_post_id' ]); ?>][]" class="wdm_qty"/>
									<td><input type="text" size="5" name="wdm_woo_variation_group_price[<?php echo esc_attr($variation_data[ 'variation_post_id' ]); ?>][]" class="wdm_price"/>
									<td class="remove_var_g_csp" style="color:#ff0000;cursor: pointer;"  tabindex="0"><img src='<?php echo esc_url(plugins_url('/images/minus-icon.png', dirname(dirname(__FILE__)))); ?>'></td>
									<td class="add_var_g_csp" style="cursor: pointer;"><img src='<?php echo esc_url(plugins_url('/images/plus-icon.png', dirname(dirname(__FILE__)))); ?>'  tabindex="0"></td>
								</tr>

								<?php
							}
							unset($groups_dropdown_v); //Unset groups dropdown variable
							?>
							</tbody>
						</table>
					</div> <!-- End Test Div -->
				</td>
				</tr>
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
		 * This method takes variation ids & group names as an array to generate
		 * select menu having dropdown list of options in html format & returns it.
		 * An optional parameter $rows is there to get select menu with preselected option,
		 *
		 * @param array $variation_data
		 * @param array $group_names
		 * @param object $rows database row having group specific CSP rule.
		 * @return string an html select menu.
		 */
		public function wdmGenerateGroupNameDropDown( $variation_data, $group_names, $rows = '') {
			$html= "<select name='wdm_woo_variation_groupname[" . $variation_data[ 'variation_post_id' ] . "][]' id='grp_" . $variation_data[ 'variation_post_id' ] . "'  class='chosen-select'>";
			// Gets the dropdown for group names.
			$selectedGroupForRow='';
			if ( ''!=$rows) {
				$selectedGroupForRow=$rows->group_id;
			}
			foreach ($group_names as $single_group_name) {
				$html .= '<option value=' . $single_group_name->group_id . ' ' . ( ( $single_group_name->group_id === $selectedGroupForRow ) ? ' selected ' : ' ' ) . ' >' . esc_html($single_group_name->name) . '</option>';
			}
			$html .= '</select>';
			return $html;
		}


		public function displayDiscountOptions( $discountOptions, $rows) {
			foreach ($discountOptions as $i => $value) {
				if ($rows->price_type == $i) {
					echo "<option value = '" . esc_attr($rows->price_type) . "' selected>" . esc_html($value) . '</option>';
				} else {
					echo "<option value = '" . esc_attr($i) . "'>" . esc_html($value) . '</option>';
				}
			}
		}

		/**
		 * Saves Group-Pricing Pairs for Variable Products in the database
		 * Checks if the groups plugin is activated.
		 * If yes sends the data to process for saving, updating,deleting,etc
		 * Also,delete record from database, as  all records removed for
		 * particular variation
		 *
		 * @global object $wpdb Database object
		 */
		public function processGroupPricingPairs() {
			global $wpdb,$cspFunctions;
			$group_product_table = $wpdb->prefix . 'wusp_group_product_price_mapping';
			$postArray = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
			/**
			 * Check if Groups is active
			 */
			if ($cspFunctions->wdmIsActive('groups/groups.php')) {
				if (! isset($postArray[ 'variable_post_id' ]) || empty($postArray[ 'variable_post_id' ])) {
					return;
				}

				$variable_post_id = $postArray[ 'variable_post_id' ];

				if (! isset($postArray[ 'wdm_woo_variation_groupname' ]) || ! isset($postArray[ 'wdm_woo_variation_group_price' ])|| ! isset($postArray[ 'wdm_woo_variation_group_qty' ])) {
					foreach ($variable_post_id as $single_post_id) {
						if (! isset($single_post_id)) {
							//delete record, as  all records removed for particular variation
							continue;
						}
						$var_id = (int) $single_post_id;
						$wpdb->delete($group_product_table, array(
							'product_id' => $var_id,
						));
					}
				} else {
					self::processGroupVariationLoop($postArray[ 'wdm_woo_variation_group_price' ], $postArray[ 'wdm_woo_variation_group_qty' ], $postArray[ 'wdm_group_price_type_v' ], $postArray[ 'wdm_woo_variation_groupname' ], $postArray[ 'variable_post_id' ]);
				}
			}
		}

		/**
		* Process the data for specific pricing for all the variations.
		 *
		* @param array $variable_price_field Variation prices
		* @param array $variable_qty_field Variation quantities
		* @param array $variable_price_type price types (discount/flat)
		* @param array $variable_csp_group Group names
		* @param array $variable_post_id Variable Post Ids.
		*/

		private function processGroupVariationLoop( $variable_price_field, $variable_qty_field, $variable_price_type, $variable_csp_group, $variable_post_id) {
			//global $wpdb, $subruleManager, $wdmPluginDataCSP;
				$max_loop            = max(array_keys($variable_post_id));

				//Loop through all variations
			for ($i = 0; $i <= $max_loop; $i ++) {
				if (! isset($variable_post_id[ $i ])) {
					continue;
				}

				$var_id = (int) $variable_post_id[ $i ];

				self::addVariationGroupPriceMappingInDb($variable_csp_group, $variable_qty_field, $variable_price_field, $variable_price_type, $var_id);
			}//foreach ends
			// }//if ends
		}


		/**
		 * This method gets newly added variable_csp_group csp rules array & variable qty field array
		 * maps variable products group rules group IDs with qtys assigned and returns
		 * An array of groupIds associated with minQty.
		 *
		 * @param [type] $variable_csp_group
		 * @param [type] $variable_qty_field
		 * @return array
		 */
		private function getNewVariableGroupUserQtyMappingArray( $variable_csp_group, $variable_qty_field) {
			$newArray = array();
			foreach ($variable_csp_group as $index => $wdmSingleUser) {
				$newArray[] = array(
						'group_id'  => $wdmSingleUser,
						'min_qty'   => $variable_qty_field[ $index ]
					);
			}
			return $newArray;
		}


		/**
		 * Generated deleted groups quantity pairs according to the data provided.
		 *
		 * @param [type] $deletedValues
		 * @param [type] $existing
		 * @param [type] $userType
		 * @return void
		 */
		private function wdmGetDeletedGroupsQtyPairs( $deletedValues, $existing, $userType) {
			$deletedGroupsQtyPairs=array();
			foreach ($deletedValues as $key => $value) {
				$deletedGroupsQtyPairs[0][] = $existing[$key][$userType];
				$deletedGroupsQtyPairs[1][]   = $existing[$key]['min_qty'];
				unset($value);
			}
			return $deletedGroupsQtyPairs;
		}


		private function wdmDeleteRulesFromDB( $deletedGroupsQtyPairs, $var_id) {
			global $wpdb,$subruleManager;
			$deleteGroups=$deletedGroupsQtyPairs[0];
			$deleteQty=$deletedGroupsQtyPairs[1];
			foreach ($deleteGroups as $index => $singleGroup) {
				$wpdb->get_results($wpdb->prepare('DELETE FROM ' . $wpdb->prefix . 'wusp_group_product_price_mapping 
													WHERE group_id = %d 
														AND min_qty = %d 
														AND product_id = %d'	
												, $singleGroup, $deleteQty[$index], $var_id));
			}
			$subruleManager->deactivateSubrulesForGroupsNotInArray($var_id, $deleteGroups, $deleteQty);
		}

		/**
		* Processing the records and performing insert, delete and update on it
		* Delete records which are not in submission but saved in the DB and
		* delete the subrules associated with it
		* Update and insert records in the user_pricing_mapping table with
		* the current selection.
		* Deactivates the subrules of that group for that Product if any
		* existed previously.
		* Deactivates the Subrules for groups not in the database.
		* If pricing not set delete the record.
		* Also, delete the records from DB if all records are deleted.
		 *
		* @param array $variable_csp_group Group names
		* @param array $variable_price_field Variation prices
		* @param array $variable_qty_field Variation quantities
		* @param array $variable_price_type price types (discount/flat)
		* @param int $var_id Variation Post Id.
		* @global object $wpdb Object responsible for executing db queries
		*/
		private function addVariationGroupPriceMappingInDb( $variable_csp_group, $variable_qty_field, $variable_price_field, $variable_price_type, $var_id) {
			global $wpdb, $subruleManager, $cspFunctions;
			$temp_group_qty_array   = array();
			$deletedValues          = array();
			$newArray               = array();
			$group_product_table    = $wpdb->prefix . 'wusp_group_product_price_mapping';
			$user_names             = '';
			$userType               = 'group_id';
			$wdmSavedRules          = array();

			if (isset($variable_csp_group[$var_id])) {
				$newArray=$this->getNewVariableGroupUserQtyMappingArray($variable_csp_group[$var_id], $variable_qty_field[$var_id]);
				
				$user_names = "('" . implode("','", $variable_csp_group[$var_id]) . "')";
				$qty = '(' . implode(',', $variable_qty_field[$var_id]) . ')';

				$existing = $wpdb->get_results($wpdb->prepare('SELECT group_id, min_qty FROM ' . $wpdb->prefix . 'wusp_group_product_price_mapping WHERE product_id = %d', $var_id), ARRAY_A);

				$deletedValues = $cspFunctions->multiArrayDiff($newArray, $existing, $userType);
				$mapping_count = count($deletedValues);
				
				if ($mapping_count > 0) {
					$deletedGroupsQtyPairs=$this->wdmGetDeletedGroupsQtyPairs($deletedValues, $existing, $userType);
					$this->wdmDeleteRulesFromDB($deletedGroupsQtyPairs, $var_id);
				}
			}

			if (isset($variable_csp_group[$var_id]) && ! empty($variable_qty_field[$var_id])) {
				foreach ($variable_csp_group[$var_id] as $index => $wdm_woo_group_id) {
					if (isset($wdm_woo_group_id)) {
						$groupQtyPair = $wdm_woo_group_id . '-' . $variable_qty_field[$var_id][ $index ];
						if (! in_array($groupQtyPair, $temp_group_qty_array)) {
							array_push($temp_group_qty_array, $groupQtyPair);
							$group_id = $wdm_woo_group_id;
							$qty = $variable_qty_field[$var_id][ $index ];
							if (isset($variable_price_field[$var_id][ $index ]) && isset($variable_price_type[$var_id][ $index ]) && isset($qty) && !( $qty <= 0 )) {
								$pricing = wc_format_decimal($variable_price_field[$var_id][ $index ]);
								$price_type = $variable_price_type[$var_id][ $index ];

								if (! empty($group_id) && ! empty($pricing) && ! empty($price_type)) {
									$result = $wpdb->get_results($wpdb->prepare('SELECT id FROM ' . $wpdb->prefix . 'wusp_group_product_price_mapping WHERE group_id = %d and min_qty = %d and product_id=%d', $wdm_woo_group_id, $qty, $var_id));
									if (count($result) > 0) {
										$update_status = $wpdb->update($group_product_table, array(
											'group_id'                  => $group_id,
											'price'                     => $pricing,
											'flat_or_discount_price'    => $price_type,
											'product_id'                => $var_id,
											'min_qty'                   => $qty,
										), array( 'group_id' => $group_id, 'product_id' => $var_id, 'min_qty' => $qty ));

										if ($update_status) {
											$subruleManager->deactivateSubrulesOfGroupForProduct($var_id, $group_id, $qty);
										}
									} else {
										$wpdb->insert($group_product_table, array(
											'group_id'                  => $group_id,
											'price'                     => $pricing,
											'flat_or_discount_price'    => $price_type,
											'product_id'                => $var_id,
											'min_qty'                   => $qty,
										), array(
											'%d',
											'%s',
											'%d',
											'%d',
											'%d',
										));
									}
									$wdmSavedRules[]= new \rules\GroupBasedRule($var_id, $group_id, $price_type, $qty, $pricing);
								}
							}
							if (empty($pricing)) {
								$wpdb->delete(
									$group_product_table,
									array(
									'group_id'      => $group_id,
									'product_id'    => $var_id,
									'min_qty'       => $qty,
									),
									array(
									'%d',
									'%d',
									'%d',
									)
								);
								$subruleManager->deactivateSubrulesOfGroupForProduct($var_id, $group_id, $qty);
							}
						}
					}
				}//foreach ends
				do_action('wdm_rules_saved', 'group_specific_variation_rules', $wdmSavedRules);
			} else {
				$wpdb->delete(
					$group_product_table,
					array(
					'product_id' => $var_id,
					),
					array(
					'%d',
					)
				);
				$subruleManager->deactivateSubrulesOfAllGroupsForProduct($var_id);
			}
		}

		//end if 'available'
	}

}
