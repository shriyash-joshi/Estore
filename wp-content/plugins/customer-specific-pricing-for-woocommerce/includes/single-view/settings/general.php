<?php

namespace cspSingleView\general;

if (! class_exists('WdmSingleViewGeneral')) {
	/**
	* Class for the general(product-pricing) tab settings in the CSP single view.
	*/
	class WdmSingleViewGeneral {
	

		public function __construct() {
			add_action('csp_single_view_product_pricing', array( $this, 'generalSettingCallback' ));
		}

		/**
		* Product Pricing tab, Set Rules tab.
		* Enqueue scripts for the Product Pricing tab.
		* Prepare the data to send to javascript.
		* Loads the existing details of rules, products selections ,etc.
		* Prepare data for localization.
		*/
		public function generalSettingCallback() {
			global $wpdb, $ruleManager, $cspFunctions;
			$query_log_id     = isset($_GET[ 'query_log' ]) ? sanitize_text_field($_GET[ 'query_log' ]) : '';
			$option_selected  = '';
			$query_log_result = '';

			self::enqueueScript();

			if (! empty($query_log_id)) {
				$query_log_result = $wpdb->get_row($wpdb->prepare('SELECT rule_title, rule_type FROM ' . $wpdb->prefix . 'wusp_rules WHERE rule_id = %d', $query_log_id));
				if (null!= $query_log_result) {
					$option_selected = strtolower($query_log_result->rule_type);
				}
			}

			$available_options = apply_filters('csp_single_view_option_types', array( 'customer'    => __('Customer Specific Pricing', 'customer-specific-pricing-for-woocommerce'),
				'role'       => __('Role Specific Pricing', 'customer-specific-pricing-for-woocommerce'),
				'group'      => __('Group Specific Pricing', 'customer-specific-pricing-for-woocommerce'), ));
			?>
			<hr/>
			<div class="wdm-csp-single-view-general-wrapper">

				<div class="form-group row wdm-csp-single-view-from-group">
					<label class="col-md-3 form-control-label"> 
					<?php 
					echo esc_html_e('Select Option', 'customer-specific-pricing-for-woocommerce');
					?>
					 </label>
					<div class="col-md-4 form-control-wrap">
						<select name="wdm_setting_option_type" id="wdm_setting_option_type" class="form-control wdm-csp-single-view-form-control">
							<option value="-1">
							<?php 
							echo esc_html__('Select any value', 'customer-specific-pricing-for-woocommerce');
							?>
							</option>
			<?php
				 
			if (! empty($available_options) && is_array($available_options)) {
				foreach ($available_options as $key => $value) {
					?>
						<option value="<?php echo esc_attr($key); ?>" <?php selected($key, $option_selected); ?>><?php echo esc_html(trim($value)); ?></option>
											<?php
				} //foreach ends
			} //if ends
			?>
						</select>
					</div>
				</div>

				<div class="wdm-csp-single-view-result-wrapper">
			<?php

			$product_result = $cspFunctions->loadExistingDetails($option_selected, $query_log_result, $query_log_id);

			$array_to_be_sent = array( 'admin_ajax_path'             => admin_url('admin-ajax.php'),
				'error_selection_empty'      => __(' selection empty.', 'customer-specific-pricing-for-woocommerce'),
				'error_product_list_empty'   => __('product selection empty.', 'customer-specific-pricing-for-woocommerce'),
				'hide_column_msg'            => __('Hide / Show Columns', 'customer-specific-pricing-for-woocommerce'),
				'invalid_quantity_value'     => __('Invalid Quantity Value', 'customer-specific-pricing-for-woocommerce'),
				'error_query_title_empty'    => __('Please fill Rule Title.', 'customer-specific-pricing-for-woocommerce'),
				'error_all_fields_empty'     => __('Please fill some values, all fields are empty.', 'customer-specific-pricing-for-woocommerce'),
				'error_field_not_numeric'    => __('All values should be numeric.', 'customer-specific-pricing-for-woocommerce'),
				'error_field_max_val'        => __('Max val for discount type % should be less than 100'),
				'error_field_negative_number'=> __('Values should not be negative.', 'customer-specific-pricing-for-woocommerce'),
				'confirm_msg_if_error'       => __('There are some errors, do you still want to proceed? This will override all other values.', 'customer-specific-pricing-for-woocommerce'),
				'confirm_msg_if_empty'       => __('Some fields are empty, do you still want to proceed? Empty fields will be ignored.', 'customer-specific-pricing-for-woocommerce'),
				'confirm_msg_invalid_values' => __('Some fields have invalid values, do you still want to proceed? Empty fields will be ignored.', 'customer-specific-pricing-for-woocommerce'),
				'confirm_msg'                => __('This will override all values. Do you still want to proceed?', 'customer-specific-pricing-for-woocommerce'),
				'loading_image_path'         => plugins_url('/images/loading .gif', dirname(dirname(dirname(__FILE__)))),
				'progress_loading_text'      => __('Loading..', 'customer-specific-pricing-for-woocommerce'),
				'progress_complete_text'     => __('Completed', 'customer-specific-pricing-for-woocommerce'),
				'query_log_id'               => $query_log_id,
				'product_result'             => $product_result,
				'customer_text'              => __('customer'),
				'role_text'              => __('role'),
				'group_text'              => __('group'),
				'change_product_selection'            => __(' or products selection', 'customer-specific-pricing-for-woocommerce'),
				'change_text'               => __('Change ', 'customer-specific-pricing-for-woocommerce'),
				// 'change_group'           => __('Change group or products selection', 'customer-specific-pricing-for-woocommerce'),
				'update_rule'               => __('Update Rule', 'customer-specific-pricing-for-woocommerce'),
				'show_all'                  => __('Show all', 'customer-specific-pricing-for-woocommerce'),
				'showing_all'               => __('Showing all {0}', 'customer-specific-pricing-for-woocommerce'),
				'empty_list'                => __('Empty list', 'customer-specific-pricing-for-woocommerce'),
				'filter'                    => __('filter', 'customer-specific-pricing-for-woocommerce'),
				'filtered'                  => __('filtered', 'customer-specific-pricing-for-woocommerce'),
				'move_selected'             => __('Move selected', 'customer-specific-pricing-for-woocommerce'),
				'move_all'                  => __('Move all', 'customer-specific-pricing-for-woocommerce'),
				'remove_all'                => __('Remove all', 'customer-specific-pricing-for-woocommerce'),
				'remove_selected'           => __('Remove selected', 'customer-specific-pricing-for-woocommerce'),
				'length_menu'               => __('Show _MENU_ entries', 'customer-specific-pricing-for-woocommerce'),
				'showing_info'              => __('Showing _START_ to _END_ of _TOTAL_ entries', 'customer-specific-pricing-for-woocommerce'),
				'empty_table'               => __('No data available in table', 'customer-specific-pricing-for-woocommerce'),
				'info_empty'                => __('Showing 0 to 0 of 0 entries', 'customer-specific-pricing-for-woocommerce'),
				'info_filtered'             => __('(filtered from _MAX_ total entries)', 'customer-specific-pricing-for-woocommerce'),
				'zero_records'              => __('No matching records found', 'customer-specific-pricing-for-woocommerce'),
				'loading_records'           => __('Loading...', 'customer-specific-pricing-for-woocommerce'),
				'empty_regular_price'       => __('% discount cannot be applied to the product having no price or price 0', 'customer-specific-pricing-for-woocommerce'),
				'processing'                => __('Processing...', 'customer-specific-pricing-for-woocommerce'),
				'search'                    => __('Search:', 'customer-specific-pricing-for-woocommerce'),
				'first'                     => __('First', 'customer-specific-pricing-for-woocommerce'),
				'prev'                      => __('Previous', 'customer-specific-pricing-for-woocommerce'),
				'next'                      => __('Next', 'customer-specific-pricing-for-woocommerce'),
				'last'                      => __('Last', 'customer-specific-pricing-for-woocommerce'),
				);

			wp_localize_script('csp_single_general_js', 'single_view_obj', $array_to_be_sent);

			if (! empty($option_selected)) {
				self::loadAdditionalButtons();
			}
			?>
				</div>
			</div>
					<?php
		}

		// /**
		// * Gets the subrules for the Rules.
		// * Get the associated-entities for the subrules.
		// * For Product-Pricing tab,
		// * Display the Products and Rule-type selection.
		// * Display the Set Prices module.
		// * Gets the Product Details titles
		// * Gets the product names , product variation names.
		// * Gets the Product details for the rule selection.
		// * Get the Rules page template
		// * @param string $option_selected rule type selected.
		// * @param array $query_log_result Rows for the rule_id.
		// * @param int $query_log_id rule-id
		// * @return array $product_result array of Products titles, details,
		// * rules info.
		// */
		// private function loadExistingDetails($option_selected, $query_log_result, $query_log_id)
		// {
		//     global $subruleManager;
		//     $product_result      = '';
		//     $subruleInfo         = '';
		//     $selectedEntities    = array();
		//     $selectedProducts    = array();
		//     $selectionValues     = array();

		//     if (! empty($option_selected) && ! is_wp_error($query_log_result)) {
		//         if ($query_log_id != null) {
		//             $subruleInfo = $subruleManager->getAllSubrulesInfoForRule($query_log_id);
		//         }
						
		//         if (empty($subruleInfo)) {
		//             return;
		//         }

		//         //Find out all entities and products which were selected
		//         if (is_array($subruleInfo)) {
		//             foreach ($subruleInfo as $singleSubrule) {
		//                 if (! in_array($singleSubrule[ 'associated_entity' ], $selectedEntities)) {
		//                     $selectedEntities[] = $singleSubrule[ 'associated_entity' ];
		//                 }

		//                 $selectionValues = $this->getSelectionValues($option_selected, $selectedEntities);

								
		//                 if (! in_array($singleSubrule[ 'product_id' ], $selectedProducts)) {
		//                     $selectedProducts[] = $singleSubrule[ 'product_id' ];
		//                 }
		//             }
		//         }
		//         $csp_ajax = new \cspAjax\WdmWuspAjax();

				
		//         $csp_ajax->displayTypeSelection($option_selected, $selectedEntities, $selectedProducts);
		
		//         $product_result[ 'title_name' ] = $csp_ajax->getProductDetailTitles($option_selected);
		//         $product_name_list   = array();
		//         $product_list        =  $selectedProducts;

		//         foreach ($product_list as $single_product_id) {
		//             if (get_post_type($single_product_id) == 'product_variation') {
		//                 $parent_id           = wp_get_post_parent_id($single_product_id);
		//                 $product_title       = get_the_title($parent_id);
		//                 $variable_product    = new \WC_Product_Variation($single_product_id);
		//                 $attributes          = $variable_product->get_variation_attributes();

		//                 //get all attributes name associated with this variation
		//                 $attribute_names = array_keys($variable_product->get_attributes());

		//                 $pos = 0; //Counter for the position of empty attribute
		//                 foreach ($attributes as $key => $value) {
		//                     if (empty($value)) {
		//                         $attributes[$key] = "Any ".$attribute_names[$pos++];
		//                     }
		//                 }

		//                 $product_title .= '-->' . implode(', ', $attributes);

		//                 $product_name_list[ $single_product_id ] = $product_title;
		//             } else {
		//                 $product_name_list[ $single_product_id ] = get_the_title($single_product_id);
		//             }
		//         }

		//         $product_result[ 'value' ] = $csp_ajax->getProductDetailList($option_selected, $product_name_list, $selectionValues, $subruleInfo);

		//         $product_result[ 'query_input' ] = $csp_ajax->getQueryInput($query_log_result->rule_title);
		//     }

		//     return $product_result;
		// }

		/**
		* Loads additional buttons for edit and clear.
		*/
		private function loadAdditionalButtons() {
			?>
			<p class="pull-right">
<!--                <input type="button" class="btn btn-primary" id="wdm_edit_entries" value="<?php esc_attr_e('Edit', 'customer-specific-pricing-for-woocommerce'); ?>"/>-->
<!--                <input type="button" class="btn btn-primary" id="wdm_clear_entries" value="<?php esc_attr_e('Clear', 'customer-specific-pricing-for-woocommerce'); ?>"/>-->
			</p>
			<?php
		}

		/**
		* Enqueue scripts and styles
		*/
		private function enqueueScript() {
			//Enqueue JS & CSS

			wp_enqueue_style('csp_general_css_handler', plugins_url('/css/single-view/wdm-single-view.css', dirname(dirname(dirname(__FILE__)))), array(), CSP_VERSION);
			wp_enqueue_script('csp_single_general_js', plugins_url('/js/single-view/wdm-general-settings.js', dirname(dirname(dirname(__FILE__)))), array( 'jquery' ), CSP_VERSION);

			//ListBox asset
			wp_enqueue_script('csp_singleview_listbox_js', plugins_url('/js/single-view/jquery.bootstrap-duallistbox.min.js', dirname(dirname(dirname(__FILE__)))), array( 'jquery' ), CSP_VERSION);

			wp_enqueue_style('csp_singleview_listbox_css', plugins_url('/css/single-view/bootstrap-duallistbox.min.css', dirname(dirname(dirname(__FILE__)))), array(), CSP_VERSION);

			//Bootstrap
			wp_enqueue_style('csp_bootstrap_css', plugins_url('/css/import-css/bootstrap.css', dirname(dirname(dirname(__FILE__)))), array(), CSP_VERSION);

			//Progress bar
			wp_enqueue_script('csp_bootstrap_progressbar_js', plugins_url('/js/single-view/bootstrap-progressbar.min.js', dirname(dirname(dirname(__FILE__)))), array( 'jquery' ), CSP_VERSION);
			wp_enqueue_style('csp_bootstrap_progressbar_css', plugins_url('/css/single-view/bootstrap-progressbar-3.3.4.min.css', dirname(dirname(dirname(__FILE__)))), array(), CSP_VERSION);

			//Datatable
			wp_enqueue_script('csp_singleview_datatable_js', plugins_url('/js/single-view/jquery.dataTables.min.js', dirname(dirname(dirname(__FILE__)))), array( 'jquery' ), CSP_VERSION);
			wp_enqueue_script('csp_singleview_bootstrap_datatable_js', plugins_url('/js/single-view/dataTables.bootstrap.min.js', dirname(dirname(dirname(__FILE__)))), array( 'jquery' ), CSP_VERSION);
			wp_enqueue_script('csp_singleview_button_js', plugins_url('/js/single-view/dataTables.buttons.min.js', dirname(dirname(dirname(__FILE__)))), array( 'csp_singleview_datatable_js' ), CSP_VERSION);
			wp_enqueue_script('csp_singleview_button_column_js', plugins_url('/js/single-view/buttons.colVis.min.js', dirname(dirname(dirname(__FILE__)))), array( 'csp_singleview_datatable_js' ), CSP_VERSION);

			wp_enqueue_style('csp_datatable_bootstrap_css', plugins_url('/css/single-view/dataTables.bootstrap.min.css', dirname(dirname(dirname(__FILE__)))), array(), CSP_VERSION);
			wp_enqueue_style('csp_datatable_css', plugins_url('/css/single-view/jquery.dataTables.min.css', dirname(dirname(dirname(__FILE__)))), array(), CSP_VERSION);
			wp_enqueue_style('csp_button_datatable_css', plugins_url('/css/single-view/buttons.dataTables.min.css', dirname(dirname(dirname(__FILE__)))), array(), CSP_VERSION);
		}
	}

}
