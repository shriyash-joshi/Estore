<?php

namespace cspAjax;

if (! class_exists('WdmWuspAjax')) {

	/**
	* Class for various ajax callbacks in CSP.
	*/
	class WdmWuspAjax {
	

		/**
		* Adds the ajax callback actions in CSP:
		* Ajax action for creating csv.
		* Ajax action for rule-type selection in Product-pricing tab.
		* Ajax action for updating options table for notice dismissal.
		* Ajax action for selecting product-titles and their specific pricing
		* Ajax action for saving the rule log of product pricings.
		* Ajax action for Progress status from the options table.
		* Ajax action for getting the selection in search by tab.
		* Ajax action for displaying selection list for various entities
		* pricings
		* Ajax action for deleting the rule from rule log.
		* Ajax action for dropping the batch numbers column from the table.
		*/

		public function __construct() {
			add_action('wp_ajax_create_csv', array($this, 'wdmCreateCsv'));
			add_action('wp_ajax_get_type_selection_result', array( $this, 'getTypeSelectionResultCallback' ));

			add_action('wp_ajax_get_product_price_list', array( $this, 'getProductPriceListCallback' ));

			add_action('wp_ajax_save_query_log', array( $this, 'saveQueryLogCallback' ));

			add_action('wp_ajax_get_progress_status', array( $this, 'getProgressStatusCallback' ));

			add_action('wp_ajax_get_search_selection_result', array( $this, 'getSearchSelectionCallback' ));

			add_action('wp_ajax_display_product_prices_selection', array( $this, 'displayProductPricesCallback' ));

			add_action('wp_ajax_remove_single_csp_price', array($this, 'removeSingleRecordCSPPrice'));

			// Callback to remove the CSP price records when 'Delete' button is clicked in
			// 'Search by & Delete' tab in the admin section.
			add_action('wp_ajax_remove_bulk_csp_price', array($this, 'removeBulkRecordCSPPrice'));

			add_action('wp_ajax_remove_query_log', array( $this, 'removeQueryLogCallback' ));

			add_action('wp_ajax_drop_batch_numbers', array( $this, 'dropBatchNumbers'));

			add_action('wp_ajax_csp_download_report', array($this, 'generateAndDownloadReport'));
		}

		
		/**
		* Gets the file type.
		* Drop the batch numbers column from the table depending on
		* rule-type.
		*
		*/
		public function dropBatchNumbers() {
			$postArray = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

			$fileType = isset($postArray['file_type']) ? $postArray['file_type'] : '';
			if (!empty($fileType)) {
				$this->deleteBatchColumn($fileType);
			}
			die();
		}

		/**
		* Delete the column of batch numbers for particular table depending on
		* rule-type.
		 *
		* @param string $fileType rule/file type.
		*/
		public function deleteBatchColumn( $fileType) {
			global $wpdb;
		
			switch ($fileType) {
				case 'user':
					$cspTable = $wpdb->prefix . 'wusp_user_pricing_mapping';
					$existingColumn = $wpdb->get_var($wpdb->prepare('SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = %s AND column_name = %s', $cspTable, 'batch_numbers'));
					if (!empty($existingColumn)) {
						$wpdb->query('ALTER TABLE ' . $wpdb->prefix . 'wusp_user_pricing_mapping DROP COLUMN batch_numbers');
					}
					break;
				
				case 'role':
					$cspTable = $wpdb->prefix . 'wusp_role_pricing_mapping';	
					$existingColumn = $wpdb->get_var($wpdb->prepare('SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = %s AND column_name = %s', $cspTable, 'batch_numbers'));
					if (!empty($existingColumn)) {
						$wpdb->query('ALTER TABLE ' . $wpdb->prefix . 'wusp_role_pricing_mapping DROP COLUMN batch_numbers');
					}
					break;
				
				case 'group':
					$cspTable = $wpdb->prefix . 'wusp_group_product_price_mapping';
					$existingColumn = $wpdb->get_var($wpdb->prepare('SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = %s AND column_name = %s', $cspTable, 'batch_numbers'));
					if (!empty($existingColumn)) {
						$wpdb->query('ALTER TABLE ' . $wpdb->prefix . 'wusp_group_product_price_mapping DROP COLUMN batch_numbers');
					}
					break;
				default:
					# Do nothing...
					break;
			}
		}

		/**
		 * Create csv file for export
		 * Verifies the nonce for export.
		 * Check the capability of the user.
		 * Fetch the data of particular entity specific pricing from DB.
		 * Gets the file name for the csv on basis of entity.
		 * If that file is already present in uploadsdirectory delete that
		 * file and create new file with the data fetched from DB.
		 */
		public function wdmCreateCsv() {
			//WdmUserSpecificPricingExport
			$nonce = isset($_REQUEST['_wpnonce'])?sanitize_text_field($_REQUEST['_wpnonce']):'';
			$nonce_verification = wp_verify_nonce($nonce, 'export_nonce');

			//Override nonce verification for extending import functionality in any third party extension
			$nonce_verification = apply_filters('csp_export_nonce_verification', $nonce_verification);
			if (! $nonce_verification) {
				 echo 'Security Check';
				 exit;
			} else {
				//Allow only admin to import csv files
				$capabilityToExport = apply_filters('csp_export_allowed_user_capability', 'manage_options');
				$can_user_export = apply_filters('csp_can_user_export_csv', current_user_can($capabilityToExport));
				if (!$can_user_export) {
					echo 'Security Check';
					exit;
				}
			}

			$class_name = isset($_POST['option_val'])? '\cspImportExport\cspExport\WdmWusp' . sanitize_text_field($_POST['option_val']) . 'SpecificPricingExport' : '';

			if (!empty($class_name)) {
				$export_object = new $class_name();
				$user_product_mapping = $export_object->wdmFetchData();
				if (isset($user_product_mapping)) {
					$file_name = $export_object->wdmFileName();
					$upload_dir = wp_upload_dir();

					$deleteFile = glob($upload_dir['basedir'] . $file_name);
					if ($deleteFile) {
						foreach ($deleteFile as $file) {
							unlink($file);
						}
					}

					$output = fopen($upload_dir['basedir'] . $file_name, 'w');
					fputcsv($output, $user_product_mapping[0]);
					foreach ($user_product_mapping[1] as $row) {
						$array = (array) $row;
						fputcsv($output, $array);
					}
					fclose($output);
					echo esc_url($upload_dir['baseurl'] . $file_name);
				} else {
					echo esc_url(menu_page_url('customer_specific_pricing_export'));
				}
			} else {
				echo esc_url(menu_page_url('customer_specific_pricing_export'));
			}
			exit();
		}

		/**
		* For Product-Pricing tab,
		* Display the Products and Rule-type selection.
		* Display the Set Prices module.
		* If option not selected display error.
		*/
		public function getTypeSelectionResultCallback() {
			//Allow only admin to get selection
			$capability_required = apply_filters('csp_get_type_selection_user_capability', 'manage_options');
			$can_user_select = apply_filters('csp_can_user_get_type_selection', current_user_can($capability_required));
			$postArray = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
			
			if (!$can_user_select) {
				echo 'Security Check';
				exit;
			}

			$option_selection = isset($postArray[ 'option_type' ]) ? $postArray[ 'option_type' ] : '';

			if (! empty($option_selection)) {
				$this->displayTypeSelection($option_selection);
			} else {
				$this->cspDisplayError(__('There is some error in option selection.', 'customer-specific-pricing-for-woocommerce'));
			}

			die();
		}

		/**
		* Gets the rule-type selected.
		* For that option:
		* Gets the display names for the particular rule-type
		* Displays the selection list in the Search By tab.
		* If option not selected , display error.
		*/
		public function getSearchSelectionCallback() {
			//Allow only admin to get selection
			$capability_required = apply_filters('csp_get_search_selection_user_capability', 'manage_options');
			$can_user_select = apply_filters('csp_can_user_get_search_selection', current_user_can($capability_required));
			if (!$can_user_select) {
				echo 'Security Check';
				exit;
			}

			$postArray = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

			$option_selection = isset($postArray[ 'option_type' ]) ? $postArray[ 'option_type' ] : '';

			if (! empty($option_selection)) {
				$selection_list = $this->getSelectionList($option_selection);

				$this->displaySelectionList($selection_list, array(), false);
			} else {
				$this->cspDisplayError(__('There is some error in option selection.', 'customer-specific-pricing-for-woocommerce'));
			}

			die();
		}

		/**
		 * Displays Selection & Product List
		 * If the rule-type is group check if groups plugin is active
		 * otherwise give an error.
		 * Gets the selection list for the rule-type selected.
		 * Gets the full product list for selection.
		 * If for that selection already a rule exists, show edit button.
		 *
		 * @param  string $option_selection rule-type
		 * @param array $existing_selections associated-entities of subrules
		 * @param array $existing_products product-ids of subrule.
		*/
		public function displayTypeSelection( $option_selection, $existing_selections = array(), $existing_products = array()) {
			global $cspFunctions;
			if ('group' === $option_selection && !$cspFunctions->wdmIsActive('groups/groups.php')) {
				$this->cspDisplayError(__("Activate the 'Groups' Plugin to enjoy the benefits of Group Specific Pricing.", 'customer-specific-pricing-for-woocommerce'));
				die();
			}
			?>
			<div class="csp-selection-wrapper wdm-clear">
			<?php

			$selection_list  = $this->getSelectionList($option_selection);

			$product_list    = $this->getProductList();

			$this->displaySelectionList($selection_list, $existing_selections);

			$this->displayProductList($product_list, $existing_products);

			if (! empty($selection_list) && ! empty($product_list)) {
				?>
				<input type="button" class="btn btn-primary" id="wdm_csp_set_price" value="<?php echo esc_attr__('Set Prices', 'customer-specific-pricing-for-woocommerce'); ?>">
				<!-- Show edit button only if query_log parameter is set -->
				<?php if (isset($_GET[ 'query_log' ])) { ?>
						<input type="button" class="btn btn-primary" id="wdm_edit_entries" value="<?php esc_attr_e('Edit this rule', 'customer-specific-pricing-for-woocommerce'); ?>"/>
						<input type="button" class="btn btn-primary" id="wdm_back" data-selected-feild = "akshay" value="<?php esc_attr_e('Back', 'customer-specific-pricing-for-woocommerce'); ?>"/>
					<?php 
				}
				  do_action('wdm_after_product_selection');
				?>
				<div class="wdm-csp-product-details-list"></div>
				<?php
			}
			?>
			</div>
			<?php
		}

		//function ends -- displayTypeSelection

		/**
		* Displays the error strings.
		 *
		* @param string $error_string error string.
		*/
		public function cspDisplayError( $error_string) {
			?>
			<div class="error">
			<p><?php echo esc_html($error_string); ?> </p>
			</div>
			<?php
		}

		/**
		* Return the rule-type selected
		 *
		* @param string $optionType rule-type selected
		* @return string rule-type.
		*/
		private function getOptionSelection( $optionType) {
			if ('customer' ==$optionType) {
				return __('customer', 'customer-specific-pricing-for-woocommerce');
			}
			if ('role' == $optionType) {
				return __('role', 'customer-specific-pricing-for-woocommerce');
			}
			if ('group' == $optionType) {
				return __('group', 'customer-specific-pricing-for-woocommerce');
			}
		}

		/**
		* Displays the selection list in the Search By tab.
		* Gets the rule-type selected.
		* Gets the selections of the rule-type.
		* Gets the existing selections of rule-type having the subrules.
		* If selection list empty display error.
		 *
		* @param array $selection_list  all Selections of rule-type.
		* @param array $existing_selections existing selections of rule-type * in that subrule
		* @param bool $default_option if accesed directly.
		*/
		private function displaySelectionList( $selection_list, $existing_selections = array(), $default_option = false) {
			$postArray = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
			if (isset($postArray['option_type'])) {
				$option_selection = $this->getOptionSelection($postArray['option_type']);
			} else {
				$option_selection = '';
			}

			if (isset($selection_list[ 'value' ]) && is_array($selection_list[ 'value' ])) {
				if (isset($postArray['single_view_action']) &&  'search' == $postArray['single_view_action']) {
					$this->printSearchDropdown($option_selection, $default_option, $selection_list, $existing_selections);
				} else {
					$this->printSelectionDropdown($option_selection, $default_option, $selection_list, $existing_selections);
				}
				?>

						<?php
			} else {
				$this->cspDisplayError(__('Selection List empty.', 'customer-specific-pricing-for-woocommerce'));
			}
		}

		/**
		* Display the existing selections display names for the rule-type
		* selected in a drop-down.
		 *
		* @param string $option_selection rule-type selected.
		* @param bool $default_option if accesed directly.
		* @param array $selection_list all Selections of rule-type.
		* @param array $existing_selections existing selections of rule-type * in that subrule
		*
		* @SuppressWarnings(PHPMD.UnusedFormalParameter)
		*/
		private function printSelectionDropdown( $option_selection, $default_option, $selection_list, $existing_selections) {
			?>
			<div class="csp-selection-list-wrapper">
				<div class="form-group row">
					<label class="wdm-csp-single-view-section-heading col-md-2 form-control-label">
					<?php echo esc_html(isset($selection_list[ 'label' ]) ? $selection_list['label'] : ''); ?>
					</label>
					<div class="col-md-4 form-control-wrap form-control-wrap-alt">
						<select name='wdm_selections' class="form-control wdm-csp-single-view-form-control" id="selected-list_wdm_selections" multiple>
				<?php
				foreach ($selection_list[ 'value' ] as $key => $value) {
					?>
		   <option value="<?php echo esc_attr($key); ?>" 
									 <?php
										if (in_array($key, $existing_selections)) {
											echo 'selected="selected"';
										}
										?>
			><?php echo esc_html($value); ?></option>
					<?php
				}//foreach ends
				?>
						</select>
					</div>
				</div>
			</div>
			<?php
		}

		/**
		* Display the selections display names for the rule-type selected in * a drop-down.
		 *
		* @param string $option_selection rule-type selected.
		* @param bool $default_option if accesed directly.
		* @param array $selection_list all Selections of rule-type.
		* @param array $existing_selections existing selections of rule-type * in that subrule
		*/
		private function printSearchDropdown( $option_selection, $default_option, $selection_list, $existing_selections) {
			?>
			<div class="csp-selection-list-wrapper">
				<div class="form-group row">
					<label class="wdm-csp-single-view-section-heading col-md-2 form-control-label select-entity-type">
					<?php echo esc_html(isset($selection_list[ 'label' ]) ? $selection_list[ 'label' ] : ''); ?>
					</label>
					<div class="col-md-4 form-control-wrap form-control-wrap-alt">
						<select name='wdm_selections' class="form-control wdm-csp-single-view-form-control" id="selected-list_wdm_selections">
							<option value="-1"><?php echo esc_html__('--Select--', 'customer-specific-pricing-for-woocommerce'); ?></option>
						<?php
						if ( false!==$default_option) {
							?>
							<option value="-1"><?php __('Select', 'customer-specific-pricing-for-woocommerce') . ' ' . $default_option; ?></option>
							<?php
						}
						foreach ($selection_list[ 'value' ] as $key => $value) {
							?>
							<option value="<?php echo esc_attr($key); ?>" 
							<?php
							if (in_array($key, $existing_selections)) {
								echo 'selected="selected"';
							}
							?>
><?php echo esc_html($value); ?></option>
							<?php
						}//foreach ends
						?>
						</select>
					</div>
				</div>
			</div>
			<?php
		}

		/**
		* For Product-Pricing tab gets the Products list.
		* If no products added , display error.
		 *
		* @param array $product_list all products in database.
		* @param array $existing_products existing products for that
		* subrule,initially empty.
		*/
		private function displayProductList( $product_list, $existing_products = array()) {
			if (! empty($product_list)) {
				?>
				<div class="csp-product-list csp-selection-wrapper-sections">
			<div class="form-group row">
				<label class="wdm-csp-single-view-section-heading col-md-2 form-control-label"><?php echo esc_html__('Select Product', 'customer-specific-pricing-for-woocommerce'); ?></label>
					<div class="col-md-4 form-control-wrap form-control-wrap-alt">
						<select name='wdm_product_lists' id='wdm_product_lists' multiple class="form-control wdm-csp-single-view-form-control">
				<?php
				foreach ($product_list as $product_id => $product_name) {
					?>
					<option value="<?php echo esc_attr($product_id); ?>" 
											  <?php
												if (in_array($product_id, $existing_products)) {
													echo 'selected="selected"';
												}
												?>
					><?php echo esc_html($product_name); ?></option>
									<?php
				}
				?>
				</select>
				</div>
				</div>
						<?php
			} else {
				$this->cspDisplayError(__('Please add Products.', 'customer-specific-pricing-for-woocommerce'));
			}
		}

		/**
		* Gets the display names for the particular rule-type
		* If the result is empty, or there are no such names for that
		* rule-type, show the label for selection.
		* Returns the array of names associated with id.
		*
		* @param string $option_type rule-type
		* @return array $selection_list id and display names of rule-type.
		*/
		private function getSelectionList( $option_type) {

			$option_type = trim(strtolower($option_type));
			global $wpdb,$cspFunctions;
			

			if ( 'group'===$option_type && !$cspFunctions->wdmIsActive('groups/groups.php')) {
				$this->cspDisplayError(__("Activate the 'Groups' Plugin to enjoy the benefits of Group Specific Pricing.", 'customer-specific-pricing-for-woocommerce'));
				die();
			}

			$selection_list = array();

			if (! empty($option_type)) {
				if ( 'customer'===$option_type) {
					$user_list = $cspFunctions->getSiteUserIdNamePairs();
					
					if (! empty($user_list)) {
						$selection_list[ 'label' ] = __('Customer', 'customer-specific-pricing-for-woocommerce');

						foreach ($user_list as $single_user) {
							$selection_list[ 'value' ][ $single_user->id] = $single_user->user_login;
						}//foreach ends --loop through user list
					}//if ends -- User list not empty
				} elseif ( 'role'===$option_type) {
					$editable_roles = array_reverse(get_editable_roles());

					if (! empty($editable_roles)) {
						$selection_list[ 'label' ] = __('Role', 'customer-specific-pricing-for-woocommerce');

						foreach ($editable_roles as $role => $details) {
							$name                                = translate_user_role($details[ 'name' ]);
							$selection_list[ 'value' ][ $role ]  = $name;
						}//foreach ends
					}//if ends -- editable rows not empty
				} elseif ( 'group'===$option_type) {

					$get_group_details = $wpdb->get_results('SELECT  group_id ,  name
					FROM  ' . $wpdb->prefix . 'groups_group
					Order By name');

					if (! empty($get_group_details)) {
						$selection_list[ 'label' ] = __('Groups', 'customer-specific-pricing-for-woocommerce');

						foreach ($get_group_details as $single_group) {
							$selection_list[ 'value' ][ $single_group->group_id ] = $single_group->name;
						}
					}
				}
			}//if ends -- option_type not empty
			return $selection_list;
		}

		//function ends -- getSelectionList

		/**
		* Gets all the posts which are products.
		* Gets the array with key as product-id and value as name.
		* For variable products key is variation-id and value is
		* variation-attributes
		 *
		* @return array $full_product_list Product list
		*/
		private function getProductList() {
			global $wpdb;
			$parentVariations=array();
			$parentVariationNames=array();
			$full_product_list = array();
			
			$productsList   = $wpdb->get_results('SELECT ID, post_title FROM ' . $wpdb->prefix . 'posts where post_type="product" AND post_status IN ("draft", "publish", "pending")');
			$variantsList   = $wpdb->get_results('SELECT ID, post_parent FROM ' . $wpdb->prefix . 'posts where post_type="product_variation" AND post_status IN ("private", "publish", "pending")');
			
			if ($variantsList) {
				foreach ($variantsList as $variant) {
					$parent=$variant->post_parent;
					if (!in_array($parent, $parentVariations)) {
						$parentVariations[]=$parent;
					}
				}
			}
			
			if ($productsList) {
				foreach ($productsList as $singleProduct) {
					$product_id = $singleProduct->ID;
					if (!empty($parentVariations) && in_array($product_id, $parentVariations)) {
						$parentVariationNames[$product_id]=$singleProduct->post_title;
					} else {
						$full_product_list[$product_id] = $singleProduct->post_title;
					}
				}
				// exit;
			}//posts end
			if ($variantsList) {
				foreach ($variantsList as $variant) {
						//$variableProduct=wc_get_product($variant->ID);
						//$attributes=$variableProduct->attributes;
						$full_product_list[$variant->ID]=$parentVariationNames[$variant->post_parent] . ' -Variant #' . $variant->ID;
				}
			}
			// sort into alphabetical order, by title
			asort($full_product_list);
			return $full_product_list;
		}

		/**
		* Checks the capability of the user.
		* Gets the products titles for selection.
		* Gets the specific pricing details array for the products.
		* Gets the query log for rules page.
		* Returns the array containing above three parameters as values
		* Displays error if there is no selection.
		*/
		public function getProductPriceListCallback() {
			$capability_required = apply_filters('csp_get_product_price_list_user_capability', 'manage_options');
			$can_user_select = apply_filters('csp_can_user_get_product_price_list', current_user_can($capability_required));
			if (!$can_user_select) {
				echo 'Security Check';
				exit;
			}
			$selection_list = '';
			$postArray = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

			if (isset($postArray[ 'selection_list' ])) {
				$selection_list = $postArray[ 'selection_list' ];
			}
	
			$product_list = '';
			if (isset($postArray[ 'product_list' ])) {
				$product_list = $postArray[ 'product_list' ];
			}
			$option_type = '';
			if (isset($postArray[ 'option_type' ])) {
				$option_type = $postArray[ 'option_type' ];
			}

			if (! empty($selection_list) && ! empty($product_list) && ! empty($option_type)) {
				//Process the details

				$product_result[ 'title_name' ] = $this->getProductDetailTitles($option_type);

				$product_result[ 'value' ] = $this->getProductDetailList($option_type, $product_list, $selection_list);

				$product_result[ 'query_input' ] = $this->getQueryInput();
				echo json_encode($product_result);
			} else {
				$this->cspDisplayError(__('Some details are not found.', 'customer-specific-pricing-for-woocommerce'));
			}
			die();
		}

		/**
		* Returns the specific pricing details array for the products.
		* Prepare the details of subrules in array format.
		 *
		* @param string $option_selected rule-type selected.
		* @param array $product_list Product tiles
		* @param array $selection_values selected rule-type names
		* @param array $subruleInfo all subrules for the rule.
		* @return array $product_detail_list pricing details for the products
		*/
		public function getProductDetailList( $option_selected, $product_list, $selection_values, $subruleInfo = array()) {

			$query_log_details   = array();

			if (! empty($subruleInfo)) {
				foreach ($subruleInfo as $singleRule) {
					if (! isset($query_log_details[ $singleRule[ 'product_id' ] . '_' . $singleRule[ 'associated_entity' ] ])) {
						$query_log_details[ $singleRule[ 'product_id' ] . '_' . $singleRule[ 'associated_entity' ] ][ 'action' ] = ( '2'== $singleRule['flat_or_discount_price'] ) ? '2' : '1';
						$query_log_details[ $singleRule[ 'product_id' ] . '_' . $singleRule[ 'associated_entity' ] ][ 'value' ] = $singleRule[ 'price' ];
						$query_log_details[ $singleRule[ 'product_id' ] . '_' . $singleRule[ 'associated_entity' ] ][ 'min_qty' ] = $singleRule[ 'min_qty' ];
					}
				}
			}
			$product_detail_list = $this->getProductPriceMapping($option_selected, $product_list, $selection_values, $query_log_details);

			return apply_filters('csp_single_view_product_list', $product_detail_list);
		}

		/**
		* Gets the Product specific price mapping list.
		* Returns the specific pricing details array for the products.
		 *
		* @param string $option_selected rule-type selected.
		* @param array $product_list Product titles
		* @param array $selection_values selected rule-type names
		* @param array $query_log_details all subrules details entity wise.
		* @return array $product_detail_list pricing details for the products
		*
		* @SuppressWarnings(PHPMD.UnusedLocalVariable)
		* @SuppressWarnings(PHPMD.UnusedFormalParameter)
		*/
		public function getProductPriceMapping( $option_selected, $product_list, $selection_values, $query_log_details) {
			$discountOptions = array('1'=>__('Flat', 'customer-specific-pricing-for-woocommerce'), '2'=>'%');
			$product_detail_list = array();
			$value = '';
			$minQty = '';

			foreach ($selection_values as $SingleUser => $SingleName) {
				$userId = $SingleUser;
				foreach ($product_list as $product_id => $product_name) {
					$qtyFlag = false;
					$regular_price   = $this->wdmGetPriceForProductPricingTable(get_post_meta($product_id, '_regular_price', true));
					$sale_price      = $this->wdmGetPriceForProductPricingTable(get_post_meta($product_id, '_sale_price', true));

					$existing_qty       = 1;
					$existing_value     = '';
					$existing_action    = '';
					if (isset($query_log_details[ $product_id . '_' . $userId ][ 'value' ])) {
						$existing_value = wc_format_localized_price($query_log_details[ $product_id . '_' . $userId ][ 'value' ]);
					}
					if (isset($query_log_details[ $product_id . '_' . $userId ][ 'min_qty' ])) {
						$existing_qty = $query_log_details[ $product_id . '_' . $userId ][ 'min_qty' ];
					}
					if (isset($query_log_details[ $product_id . '_' . $userId ][ 'action' ])) {
						$existing_action = $query_log_details[ $product_id . '_' . $userId ][ 'action' ];
					}

					$minQty = '<input type="number" min = "1" value="' . $existing_qty . '" placeholder="1" name="csp_qty_' . $product_id . '_' . $userId . '" id="csp_qty" class="csp_single_view_qty" data-oldval = "' . $existing_qty . '" />';
					if ( '2'==$existing_action) {
						if ( '%' ==$discountOptions[$existing_action]) {
							$value = '<input type="text" value="' . $existing_value . '" placeholder="0" name="csp_value_' . $product_id . '_' . $userId . '" id="csp_value" class="csp_single_view_value csp-percent-discount" />';
						}
					} else {
						$value = '<input type="text" value="' . $existing_value . '" placeholder="0" name="csp_value_' . $product_id . '_' . $userId . '" id="csp_value" class="csp_single_view_value" value="' . $existing_value . '"/>';
					}

					$action = '<select name="wdm_csp_price_type' . $product_id . '_' . $userId . '" class="chosen-select csp_single_view_action">';

					foreach ($discountOptions as $k => $val) {
						if ($existing_action == $k) {
							$action .= '<option value = "' . $k . '" selected>' . $discountOptions[$k] . '</option>';
						} else {
							$action .= '<option value = "' . $k . '">' . $discountOptions[$k] . '</option>';
						}
						unset($val);
					}
					$action			.= '</select>';
					$productEditLink = get_permalink($product_id);
					$productLink	 = '<a href="' . $productEditLink . '" target="_blank">' . $product_id . '</a>';
					$product_detail_list[] = array( $productLink , $product_name, $SingleName, $regular_price, $sale_price, $action, $minQty, $value);
				}                # code...
			}

			return apply_filters('csp_single_view_product_price_mapping', $product_detail_list);
		}





		/**
		 * Returns Float value if $value is not empty
		 * Returns "--" when value is not empty.
		 *
		 * @param [type] $value
		 * @return mixed
		 */
		private function wdmGetPriceForProductPricingTable( $value) {
			if (!empty($value)) {
				$value=floatval($value);
			} else {
				$value='--';
			}
			return $value;
		}

		/**
		* Gets the Product Details titles
		 *
		* @param string $option_type rule-type.
		* @return array $titles titles for Product details
		*/
		public function getProductDetailTitles( $option_type) {
			$tableOptionTypes    = array(
			'role'       => __('Role', 'customer-specific-pricing-for-woocommerce'),
			'group'      => __('Group', 'customer-specific-pricing-for-woocommerce'),
			'customer'   => __('Customer', 'customer-specific-pricing-for-woocommerce'),
			);
			$titles              = array(
			array( 'title' => __('Product ID', 'customer-specific-pricing-for-woocommerce') ),
			array( 'title' => __('Product Name', 'customer-specific-pricing-for-woocommerce') ),
			array( 'title' => $tableOptionTypes[ $option_type ] ),
			array( 'title' => __('Regular Price', 'customer-specific-pricing-for-woocommerce') ),
			array( 'title' => __('Sale Price', 'customer-specific-pricing-for-woocommerce') ),
			array( 'title' => __('Flat or Discounts', 'customer-specific-pricing-for-woocommerce') ),
			array( 'title' => __('Min Qty', 'customer-specific-pricing-for-woocommerce') ),
			array( 'title' => __('Value', 'customer-specific-pricing-for-woocommerce') ),
			);

			return apply_filters('csp_single_view_table_titles', $titles);
		}

		/**
		* On the Rules page for new rule or edit rule.
		* Display the templates for the buttons.
		 *
		* @param string $query_title rule-title
		* @return string HTML for the Rules page.
		*/
		public function getQueryInput( $query_title = '') {
			ob_start();
			?>
	<div class="row form-group">
	<label class="col-md-2 form-control-label"><?php esc_html_e('Rule Title', 'customer-specific-pricing-for-woocommerce'); ?>
					<span class="wdm-required">*</span>
					<a class="wdm_wrapper">
						<img class="help_tip" src="<?php echo esc_url(plugins_url('/images/help.png', dirname(__FILE__))); ?>" height="20" width="20">
						<span class='wdm-tooltip-content'>
							<span class="wdm-tooltip-text">
								<span class="wdm-tooltip-inner">
			<?php esc_html_e('Rule title will help identify the rules generated for Users/Roles/Groups.', 'customer-specific-pricing-for-woocommerce'); ?>
								</span>
							</span>
						</span>
					</a>
				</label>
				<div class="col-md-4 form-control-wrap">
					<input type="text" name="wdm_csp_query_title" id="wdm_csp_query_title" size="80" value="<?php echo esc_attr($query_title); ?>" class="form-control" />
					</span>
					<input type="hidden" name="wdm_csp_query_time" id="wdm_csp_query_time" value="<?php echo esc_attr(get_current_user_id() . '_' . time()); ?>">
				</div>
			</div>
			<input type="button" class="btn btn-primary" id="wdm_csp_save_changes" value="<?php echo isset($_GET['query_log']) ? esc_attr__('Update Rule', 'customer-specific-pricing-for-woocommerce') : esc_attr__('Save Rule', 'customer-specific-pricing-for-woocommerce'); ?>">

			<div class="progress progress-striped">
				<div class="progress-bar six-sec-ease-in-out" role="progressbar" data-transitiongoal="0"></div>
			</div>
			<p class="csp-log-progress"></p>
			<?php
			$result = ob_get_contents();

			ob_end_clean();

			return $result;
		}

		/**
		* Check the capability for user.
		* Take the product pricing from the rules and updates in the DB.
		* Save the new pricing pairs in database.
		*/
		public function saveQueryLogCallback() {
			global $cspFunctions;
			//Allow only admin to get selection
			$capability_required = apply_filters('csp_save_query_log_user_capability', 'manage_options');
			$can_user_save = apply_filters('csp_can_user_save_query_log', current_user_can($capability_required));
			if (!$can_user_save) {
				echo 'Security Check';
				exit;
			}
			$wdm_save_result = '';

			$default_values = array(
				'option_type' => '',
				'selection_list' => '',
				'product_values' => '',
				'product_actions' => '',
				'product_quantities' => '',
				'productOldQuantities' => '',
				'query_title' => '',
				'option_name' => '',
				'current_query_id' => ''
			);
			$postArray = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
			$wdm_data_array = array_filter(array(
				 'option_type'          => $postArray[ 'option_type' ],
				 'selection_list'       => $postArray[ 'selection_list' ],
				 'product_values'       => $postArray[ 'product_values' ],
				 'product_actions'      => $postArray[ 'product_actions' ],
				 'product_quantities'   => $postArray[ 'product_quantities' ],
				 'productOldQuantities' => $postArray[ 'productOldQuantities' ],
				 'query_title'          => $postArray[ 'query_title' ],
				 'option_name'          => $postArray[ 'option_name' ],
				 'current_query_id'     => isset($postArray[ 'current_query_id' ]) ? $postArray[ 'current_query_id' ] : ''
				));

			$wdm_parsed_values = wp_parse_args($wdm_data_array, $default_values);

			$selection_list = trim($wdm_parsed_values[ 'selection_list' ], ',');

			$option_type = $wdm_parsed_values[ 'option_type' ];

			$values  = array();
			$quantities = array();
			$oldQuantities = array();
			$actions = array();

			parse_str($wdm_parsed_values[ 'product_values' ], $values);
			parse_str($wdm_parsed_values[ 'product_quantities' ], $quantities);
			parse_str($wdm_parsed_values[ 'productOldQuantities' ], $oldQuantities);
			parse_str($wdm_parsed_values[ 'product_actions' ], $actions);
			if ('customer'===$option_type) {
				$wdm_save_result = $cspFunctions->saveCustomerPricingPair(explode(',', $selection_list), $values, $quantities, $oldQuantities, $actions, $wdm_parsed_values[ 'query_title' ], $wdm_parsed_values[ 'option_name' ], $wdm_parsed_values[ 'current_query_id' ]);
			} elseif ('role'===$option_type) {
				$wdm_save_result = $cspFunctions->saveRolePricingPair(explode(',', $selection_list), $values, $quantities, $oldQuantities, $actions, $wdm_parsed_values[ 'query_title' ], $wdm_parsed_values[ 'option_name' ], $wdm_parsed_values[ 'current_query_id' ]);
			} elseif ('group'===$option_type) {
				$wdm_save_result = $cspFunctions->saveGroupPricingPair(explode(',', $selection_list), $values, $quantities, $oldQuantities, $actions, $wdm_parsed_values[ 'query_title' ], $wdm_parsed_values[ 'option_name' ], $wdm_parsed_values[ 'current_query_id' ]);
			}

			echo json_encode($wdm_save_result);

			die();
		}

		/**
		* Gets the Progress Status from the options table.
		*/
		public function getProgressStatusCallback() {
			//Allow only admin to get selection
			$capability_required = apply_filters('csp_get_progress_status_user_capability', 'manage_options');
			$can_user_get_status = apply_filters('csp_can_user_get_progress_status', current_user_can($capability_required));
			if (!$can_user_get_status) {
				echo 'Security Check';
				exit;
			}

			$postArray = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
			$option_name = isset($postArray['option_name']) ? $postArray['option_name'] : '';
			$result      = array( 'value' => 0, 'status' => '' );

			if (! empty($option_name)) {
				$result[ 'value' ]   = get_option($option_name . '_value', 0);
				$result[ 'status' ]  = get_option($option_name . '_status', '');
			}

			echo json_encode($result);
			die();
		}

		/**
		* If the user can access the backend.
		* Get the selections for the customer/group/role specific pricing
		* pairs.
		* Get the pricing pairs for the entities when accesed directly, or
		* by rules.
		* Display the selection list for various entities pricings
		*/
		public function displayProductPricesCallback() {
			global $cspFunctions;
			//Allow only admin to get selection
			$capability_required = apply_filters('csp_display_product_price_user_capability', 'manage_options');
			$can_user_display = apply_filters('csp_can_user_display_product_price', current_user_can($capability_required));
			if (!$can_user_display) {
				echo 'Security Check';
				exit;
			}
			$option_type     = '';
			$selection_name  = '';

			$postArray = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

			if (isset($postArray['option_type'])) {
				$option_type = $postArray[ 'option_type' ];
			}

			if (isset($postArray[ 'selection_name' ])) {
				$selection_name = $postArray[ 'selection_name' ];
			}

			$selection_list  = array();
			$product_list    = array();

			$group_plugin_active = $cspFunctions->wdmIsActive('groups/groups.php');

			if ('customer'===$option_type) {
				$user_id         = intval($selection_name);
				$selection_list  = $this->getSelectionCustomer($user_id, true);
				if (! empty($selection_list)) {
					$product_list = array_keys($selection_list);
				}
				$selection_list = $selection_list + $this->getSelectionCustomerDirect($user_id, $product_list, true);
				
				$selection_list = $cspFunctions->msort($selection_list, 'min_qty');
				
				$user_info = get_userdata($user_id);
				
				$product_list    = $product_list + array_keys($selection_list);

				$selection_list  = $selection_list + $this->getSelectionRole($user_info->roles, $product_list, true);
				
				$product_list    = $product_list + array_keys($selection_list);
				$selection_list  = $selection_list + $this->getSelectionRoleDirect($user_info->roles, $product_list, true);
			
				if ($group_plugin_active) {
					$product_list = array_keys($selection_list);
					$groups_user = new \Groups_User(intval($selection_name));
					
					// get group ids (user is direct member)
					$user_group_ids = $groups_user->group_ids;

					$product_list    = $product_list + array_keys($selection_list);
					$selection_list  = $selection_list + $this->getSelectionGroup($user_group_ids, $product_list, true);

					$product_list    = $product_list + array_keys($selection_list);
					$selection_list  = $selection_list + $this->getSelectionGroupDirect($user_group_ids, $product_list, true);
				}
			} elseif ('role' === $option_type) {
				$selection_list = $this->getSelectionRole(array( $selection_name ), array(), true);
				if (! empty($selection_list)) {
					$product_list = array_keys($selection_list);
				}
				$selection_list = $selection_list + $this->getSelectionRoleDirect(array( $selection_name ), $product_list, true);
			} elseif (  'group'===$option_type && $group_plugin_active) {
				$selection_list = $this->getSelectionGroup(array( $selection_name ), array(), true);
				if (! empty($selection_list)) {
					$product_list = array_keys($selection_list);
				}

				$selection_list = $selection_list + $this->getSelectionGroupDirect(array( $selection_name ), $product_list, true);
			}
			//Print selection
			$selection_list = $cspFunctions->msort($selection_list, 'min_qty');

			echo json_encode($this->displaySelections($selection_list));
			die();
		}

		/**
		* Returns the display list with the various parameters of the
		* entities specific pricing.
		* If selection list is empty gives an error.
		 *
		* @param array $selection_list array of pricing selections for
		* various entities.
		*
		* @SuppressWarnings(PHPMD.UnusedLocalVariable)
		*/
		private function displaySelections( $selection_list) {
			$display_list = array();

			if (! empty($selection_list)) {
				foreach ($selection_list as $id => $selection_detail) {
					$product_id = $selection_detail['product_id'];
					if (get_post_type($product_id) == 'product_variation') {
						$parent_id           = wp_get_post_parent_id($product_id);
						$product_title       = get_the_title($parent_id);
						$variable_product    = new \WC_Product_Variation($product_id);
						$attributes          = $variable_product->get_variation_attributes();

						//get all attributes name associated witj this variation
						$attribute_names = array_keys($variable_product->get_attributes());

						$pos = 0; //Counter for the position of empty attribute
						foreach ($attributes as $key => $value) {
							if (empty($value)) {
								$attributes[$key] = 'Any ' . $attribute_names[$pos++];
							}
						}

						$product_title .= '-->' . implode(', ', $attributes);
					} else {
						$product_title = get_the_title($product_id);
					}

					$selection_detail_price = number_format(wc_format_localized_price($selection_detail['price']), wc_get_price_decimals());
					$selectionCb = '<input class="wdm-csp-sel-cb" type="checkbox">';
					$selDetailQueryTitle = $selection_detail['query_title'];
					/**
					 * It contains 'Role' or 'Group' if CSP pricing is 'role
					 * specific' or 'group specific' respectively. If the
					 * pricing is neither 'role specific' nor 'group specific',
					 * then it contains 'Customer' as string.
					 *
					 * @var string
					 */
					$roleGroupCSP = 'Customer';
					$ruleNo       = $selection_detail['query_id'];

					$queryTitleForSourceColumn = strstr($selDetailQueryTitle, '-wdm-csp', true);
					$queryTitleForTrashSource  = strstr($selDetailQueryTitle, 'wdm-csp');

					// If the 'title' is 'Direct', then '$queryTitleForSourceColumn'
					// and '$queryTitleForTrashSource' would be empty.
					if (empty($queryTitleForSourceColumn)) {
						$queryTitleForSourceColumn = $selDetailQueryTitle;
						$queryTitleForTrashSource  = $selDetailQueryTitle;
					}
					
					if ('--' == $ruleNo) {
						if (strpos($queryTitleForTrashSource, 'wdm-csp-role') !== false) {
							$roleGroupCSP = 'Role';
						} elseif (strpos($queryTitleForTrashSource, 'wdm-csp-group') !== false) {
							$roleGroupCSP = 'Group';
						}
					} else {
						global $ruleManager;
						$roleGroupCSP = $ruleManager->getRuleType($ruleNo);
					}

					$selectionMinQty    = $selection_detail['min_qty'];
					$selectionPriceType = $selection_detail['price_type'];
					$selectionRuleNo    = $selection_detail['query_id'];
					$removeButton = "<a class='wdm-csp-rm-record'
                                        data-product-id = $product_id
                                        data-min-qty    = $selectionMinQty
                                        data-act-price  = $selection_detail_price
                                        data-dis-type   = $selectionPriceType
                                        data-rule-no    = $selectionRuleNo
                                        data-source     = $queryTitleForTrashSource
                                        data-role-group = $roleGroupCSP
                                        >
                                        <span class='dashicons dashicons-trash'></span>
                                    </a>";
					if (strpos($selDetailQueryTitle, '-wdm-csp-cat') !== false) {
						$helpTipText = __('Unable to delete this product rule as it was assigned to a category-based product rule. Please make changes in the category pricing tab.', 'customer-specific-pricing-for-woocommerce');
						$selection_detail[ 'query_id' ] = 'category';
						$removeButton                   = '<a class="wdm-csp-rm-record wdm-csp-cat-record"><span class="csp-help-tip dashicons dashicons-trash" data-tip="' . $helpTipText . '"></span></a>';

						$selectionCb                    = '<div class="csp-help-tip" data-tip="' . $helpTipText . '"><input class="wdm-csp-sel-cb" type="checkbox" disabled></div>';
					}

					$display_list[] = array( $selectionCb, $product_title, $selection_detail[ 'min_qty' ], $selection_detail_price, $selection_detail[ 'price_type' ], $selection_detail[ 'query_id' ], stripcslashes($queryTitleForSourceColumn), $removeButton);
				}
				return $display_list;
			} else {
				return array( 'error' => '<div class="error">' . __('No details saved.', 'customer-specific-pricing-for-woocommerce') . '</div>' );
			}
		}

		/**
		* Process the data of subrule of that user.
		* Prepare the data and form in a particular format.
		* Calculate the discounted price if the price-type is %.
		 *
		* @param array $res Subrules info for that user.
		* @param string $source source (rule)
		* @param bool   $addRuleType True if rule type needs to be added in the
		*                            'query_title'.
		* @return array $result formatted subrule data.
		*
		* @SuppressWarnings(PHPMD.UnusedLocalVariable)
		* @SuppressWarnings(PHPMD.UnusedFormalParameter)
		*/
		private function processResult( $res, $source, $addRuleType = false) {
			global $wpdb, $ruleManager, $cspFunctions;

			$result = array();
			foreach ($res as $key) {
				$prod_name  = $wpdb->get_results(
					$wpdb->prepare(
						'select post_title FROM ' . $wpdb->prefix . 'posts where ID = %d',
						$key[ 'product_id' ]
					),
					ARRAY_A
				);

				$result[ $key[ 'product_id' ] . '_' . $key[ 'min_qty' ] ][ 'product_id' ]  = $key[ 'product_id' ];
				$result[ $key[ 'product_id' ] . '_' . $key[ 'min_qty' ] ][ 'product_name' ]    = $prod_name[ 0 ][ 'post_title' ];

				if ('rule'==$source) {
					$rule_title = $ruleManager->getRuleTitle($key[ 'rule_id' ]);
					$result[ $key[ 'product_id' ] . '_' . $key[ 'min_qty' ] ][ 'query_title' ]     = $rule_title[ 'rule_title' ];
					$result[ $key[ 'product_id' ] . '_' . $key[ 'min_qty' ] ][ 'query_id' ]        = $key[ 'rule_id' ];
				} else {
					$result[ $key[ 'product_id' ] . '_' . $key[ 'min_qty' ] ][ 'query_title' ]     = $key[ 'source' ];
					$result[ $key[ 'product_id' ] . '_' . $key[ 'min_qty' ] ][ 'query_id' ]        = '--';
				}

				$result[ $key[ 'product_id' ] . '_' . $key[ 'min_qty' ] ][ 'price_type' ]  = $key[ 'price_type' ];
				$result[ $key[ 'product_id' ] . '_' . $key[ 'min_qty' ] ][ 'min_qty' ]  = $key[ 'min_qty' ];
				if (2 ==$key['price_type']) {
					$cspSettings = get_option('wdm_csp_settings');
					$salePriceDiscountEnabled = isset($cspSettings['enable_sale_price_discount']) && 'enable' == $cspSettings['enable_sale_price_discount'] ? true : false;
					$salePrice=$cspFunctions->wdmGetSalePrice($key['product_id']);
					if ($salePriceDiscountEnabled && $salePrice) {
						$currentPrice=floatval($salePrice);
					} else {
						$currentPrice = floatval(get_post_meta($key[ 'product_id' ], '_regular_price', true));
					}
					if ($currentPrice >= 0) {
						$result[ $key[ 'product_id' ] . '_' . $key[ 'min_qty' ] ][ 'price' ] = $currentPrice - ( ( $key[ 'price' ] / 100 ) * $currentPrice );
					}
				} else {
					$result[ $key[ 'product_id' ] . '_' . $key[ 'min_qty' ] ][ 'price' ]       = $key[ 'price' ];
				}
			}
			return $result;
		}

		/**
		* Gets the quantity based pricing for that product for that user.
		* Gets the specific pricing for every products for that user.
		*
		* @param int $user_id User Id.
		* @param array $product_exclude Products already included.
		* @return array subrule product details for the subrule.
		*/
		private function getSelectionCustomerDirect( $user_id, $product_exclude = array(), $addKey = false) {
			global $wpdb, $getCatRecords, $cspFunctions;

			$source = __('Direct', 'customer-specific-pricing-for-woocommerce');
			$res = $wpdb->get_results($wpdb->prepare('SELECT product_id, price, flat_or_discount_price as price_type, min_qty, %s as "source" FROM ' . $wpdb->prefix . 'wusp_user_pricing_mapping WHERE user_id = %d order by product_id', $source, $user_id), ARRAY_A);
			
			$catPrice = $getCatRecords->getAllProductPricesByUser($user_id, $addKey);

			$mergedPrices = $cspFunctions->mergeProductCatPriceSearch($res, $catPrice);
			if (empty($mergedPrices)) {
				return array();
			}

			foreach ($mergedPrices as $key => $singleResult) {
				if (in_array($singleResult['product_id'] . '_' . $singleResult['min_qty'], $product_exclude)) {
					unset($mergedPrices[$key]);
				}
			}

			$resultDirectCustomer = $this->processResult($mergedPrices, 'direct');

			if (! empty($resultDirectCustomer)) {
				return $this->processSelectionResult($resultDirectCustomer);
			}

			return array();
		}


		// getSelectionCustomerDirect ends


		/**
		* Gets the quantity based pricing for that product for that roles.
		* Gets the specific pricing for every products for that roles.
		*
		* @param array $role_list Roles.
		* @param array $product_exclude Products already included.
		* @return array subrule product details for the subrule.
		*/
		private function getSelectionRoleDirect( $role_list, $product_exclude = array(), $addKey = false) {
			global $wpdb, $getCatRecords, $cspFunctions;

			$source = __('Direct', 'customer-specific-pricing-for-woocommerce');
			$prepareArgs = array_merge((array) $source, (array) $role_list);
			$res = $wpdb->get_results($wpdb->prepare('SELECT product_id, price, flat_or_discount_price as price_type, min_qty, %s as "source" FROM ' . $wpdb->prefix . 'wusp_role_pricing_mapping WHERE role IN (' . implode(', ', array_fill(0, count($role_list), '%s')) . ') order by product_id', $prepareArgs), ARRAY_A);

			if ($addKey) {
				foreach ($res as $key => $value) {
					$res[$key]['source'] .= '-wdm-csp-role-' . $role_list[0];
				}
			}
			
			// getAllProductPricesByRoles
			$catPrice = $getCatRecords->getAllProductPricesByRoles($role_list);
			if ($addKey) {
				foreach ($catPrice as $key => $value) {
					foreach ($value as $qty => $cspDetails) {
						if ('Direct' == $cspDetails['source']) {
							$catPrice[$key][$qty]['source'] .= '-wdm-csp-role-' . $role_list[0];
						} else {
							$catPrice[$key][$qty]['source'] .= '-wdm-csp-cat';
						}
					}
				}
			}

			$mergedPrices = $cspFunctions->mergeProductCatPriceSearch($res, $catPrice);

			if (null==$mergedPrices) {
				return array();
			}


			foreach ($mergedPrices as $key => $singleResult) {
				if (in_array($singleResult['product_id'] . '_' . $singleResult['min_qty'], $product_exclude)) {
					unset($mergedPrices[$key]);
				}
			}

			$resultRoleDirect = $this->processResult($mergedPrices, 'direct');

			if (! empty($resultRoleDirect)) {
				return $this->processSelectionResult($resultRoleDirect);
			}

			return array();
		}

		// getSelectionRoleDirect


		/**
		* Gets the quantity based pricing for that product for that group-ids
		* Gets the specific pricing for every products for that group-ids.
		 *
		* @param array $group_ids Group Ids. Pass array containing single group
		*                         when '$add_key' is true.
		* @param array $product_exclude Products already included.
		* @param bool  $addKey True if 'wdm-csp-group-{group_id}' key needs
		*                      to be added, or false otherwise.
		* @return array subrule product details for the subrule.
		*/
		private function getSelectionGroupDirect( $group_ids, $product_exclude = array(), $addKey = false) {
			global $wpdb, $getCatRecords, $cspFunctions;

			$source = __('Direct', 'customer-specific-pricing-for-woocommerce');
			$prepareArgs = array_merge((array) $source, (array) $group_ids);

			$res = $wpdb->get_results($wpdb->prepare('SELECT product_id, group_id, price, flat_or_discount_price as price_type, min_qty, %s as "source" FROM ' . $wpdb->prefix . 'wusp_group_product_price_mapping WHERE group_id IN (' . implode(', ', array_fill(0, count($group_ids), '%s')) . ') order by product_id', $prepareArgs), ARRAY_A);

			if ($addKey) {
				foreach ($res as $key => $value) {
					$res[$key]['source'] .= '-wdm-csp-group-' . $res[$key]['group_id'];
				}
			}

			$catPrice = $getCatRecords->getAllProductPricesByGroups($group_ids);
			if ($addKey) {
				foreach ($catPrice as $key => $value) {
					foreach ($value as $qty => $cspDetails) {
						if ('Direct' == $cspDetails['source']) {
							$catPrice[$key][$qty]['source'] .= '-wdm-csp-group-' . $group_ids[0];
						} else {
							$catPrice[$key][$qty]['source'] .= '-wdm-csp-cat';
						}
					}
				}
			}
			$mergedPrices = $cspFunctions->mergeProductCatPriceSearch($res, $catPrice);

			if ( null==$mergedPrices) {
				return array();
			}

			foreach ($mergedPrices as $key => $singleResult) {
				if (in_array($singleResult['product_id'] . '_' . $singleResult['min_qty'], $product_exclude)) {
					unset($mergedPrices[$key]);
				}
			}


			$resultGroupDirect = $this->processResult($mergedPrices, 'direct');

			if (! empty($resultGroupDirect)) {
				return $this->processSelectionResult($resultGroupDirect);
			}

			return array();
		}

		/**
		* For the user gets all the active subrules from the DB.
		* Prepare the subrule data in proper format.
		* Get the Product details of the selection.
		 *
		* @param int $user_id User Id.
		* @param bool $addKey  True if key needs to be added to idenify
		*                      the source, false otherwise.
		* @return array $product_details Selected products details for
		* subrule.
		*
		* @SuppressWarnings(PHPMD.UnusedLocalVariable)
		*/
		private function getSelectionCustomer( $user_id, $addKey = false) {
			global $subruleManager;

			$product_details = array();

			$res = $subruleManager->getAllActiveSubrulesInfoForUserRules($user_id);

			if ( null == $res) {
				return array();
			}

			$resultCustomer = $this->processResult($res, 'rule');

			if ($addKey) {
				foreach ($resultCustomer as $key => $value) {
					$resultCustomer[$key]['query_title'] .= '-wdm-csp-rule-ae-' . $user_id;
				}
			}

			if (! empty($resultCustomer)) {
				return $this->processSelectionResult($resultCustomer);
			}

			return $product_details;
		}

		//getSelectionCustomer ends

		/**
		* For the roles gets all the active subrules from the DB.
		* Prepare the subrule data in proper format.
		* Get the Product details of the selection.
		 *
		* @param array $role_list Roles.
		* @param array $product_exclude Products already included.
		* @param bool $addKey  Add CSP key to identify the source.
		* @return array $product_details Selected products details for
		* subrule.
		* @SuppressWarnings(PHPMD.UnusedFormalParameter)
		* @SuppressWarnings(PHPMD.UnusedLocalVariable)
		*/
		private function getSelectionRole( $role_list, $product_exclude = array(), $addKey = false) {
			global $subruleManager;
			$product_details = array();
			$res             = $subruleManager->getAllActiveSubrulesInfoForRolesRule($role_list);
					
			if ( null==$res) {
				return array();
			}

			foreach ($res as $key => $singleResult) {
				if (in_array($singleResult['product_id'] . '_' . $singleResult['min_qty'], $product_exclude)) {
					unset($res[$key]);
				}
			}

			$resultRole = $this->processResult($res, 'rule');

			if ($addKey) {
				foreach ($resultRole as $key => $value) {
					$resultRole[$key]['query_title'] .= '-wdm-csp-rule-ae-' . $role_list[0];
				}
			}

			if (! empty($resultRole)) {
				return $this->processSelectionResult($resultRole);
			}
			return $product_details;
		}

		// getSelectionRole ends

		/**
		* For the groups gets all the active subrules from the DB.
		* Prepare the subrule data in proper format.
		* Get the Product details of the selection.
		 *
		* @param array $user_group_ids Group-ids. Pass an array containing
		*                              single group id when '$addKey' is true.
		* @param array $product_exclude Products already included.
		* @param bool $addKey  True if key needs to be added to idenify
		*                       the source, false otherwise.
		* @return array $product_details Selected products details for
		* subrule.
		*
		* @SuppressWarnings(PHPMD.UnusedLocalVariable)
		*/
		private function getSelectionGroup( $user_group_ids, $product_exclude = array(), $addKey = false) {
			global $subruleManager;
			$product_details = array();
			$res             = $subruleManager->getAllActiveSubrulesInfoForGroupsRule($user_group_ids);

			if (null==$res) {
				return array();
			}

			foreach ($res as $key => $singleResult) {
				if (in_array($singleResult['product_id'] . '_' . $singleResult['min_qty'], $product_exclude)) {
					unset($res[$key]);
				}
			}

			$resultGroup = $this->processResult($res, 'rule');

			if ($addKey) {
				foreach ($resultGroup as $key => $value) {
					$resultGroup[$key]['query_title'] .= '-wdm-csp-rule-ae-' . $user_group_ids[0];
				}
			}

			if (! empty($resultGroup)) {
				return $this->processSelectionResult($resultGroup);
			}
			return $product_details;
		}

		/**
		* Process the selected products results in array for that subrule
		* info.
		 *
		* @param array $result subrule data.
		* @return array $product_details Selected products details for
		* subrule.
		*/
		private function processSelectionResult( $result) {
			$product_details = array();

			foreach ($result as $product) {
				if (! in_array($product[ 'product_id' ] . '_' . $product[ 'min_qty' ], $product_details)) {
					if (is_null($product[ 'query_id' ])) {
						$product[ 'query_id' ] = '--';
					}

					if (is_null($product[ 'query_title' ])) {
						$product[ 'query_title' ] = __('Direct', 'customer-specific-pricing-for-woocommerce');
					}
					if ( 1==$product[ 'price_type' ]) {
						$product[ 'price_type' ] = __('Flat', 'customer-specific-pricing-for-woocommerce');
					} elseif ( 2 ==$product[ 'price_type' ]) {
						$product[ 'price_type' ] = '%';
					}

					$product_details[ $product[ 'product_id' ] . '_' . $product[ 'min_qty' ] ] = array( 'product_id'   => $product[ 'product_id' ],
						'product_name'  => $product[ 'product_name' ],
						'price'         => $product[ 'price' ],
						'price_type'    => $product[ 'price_type' ],
						'min_qty'       => $product[ 'min_qty' ],
						'query_id'      => $product[ 'query_id' ],
						'query_title'   => $product[ 'query_title' ] );
				}
			}
			return $product_details;
		}

		/**
		* When remove rule is clicked.
		* Gets the rule-id and deletes the rule fromDB.
		*/
		public function removeQueryLogCallback() {
			global $ruleManager;
			//Allow Admin access
			$capability_required = apply_filters('csp_remove_query_log_user_capability', 'manage_options');
			$can_user_remove = apply_filters('csp_can_user_remove_query_log', current_user_can($capability_required));
			if (!$can_user_remove) {
				echo 'Security Check';
				exit;
			}

			$postArray = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
			
			$query_log_ids = $postArray['query_log_id'];

			if (! empty($query_log_ids)) {
				foreach ($query_log_ids as $single_qlog_id) {
					$ruleManager->deleteRule($single_qlog_id);
				}

				echo '<div class="updated wdm-qlog-notification settings-error notice is-dismissible"><p>' . esc_html__('Rule Deleted.', 'customer-specific-pricing-for-woocommerce') . '</p></div>';
			} else {
				echo '<div class="error wdm-qlog-notification"><p>' . esc_html__('Please select some Log', 'customer-specific-pricing-for-woocommerce') . '</p></div>';
			}

			die();
		}

		/**
		 * Ajax callback to remove the single CSP price record.
		 */
		public function removeSingleRecordCSPPrice() {
			global $cspFunctions;
			$postArray = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
			$optionType     = $postArray['option_type'];
			$selectionName  = $postArray['selection_name'];
			$recordData     = $postArray['record_data'];
			$cspSource         = $recordData['csp_source'];
			$productId      = $recordData['product_id'];
			$minQty         = $recordData['min_qty'];
			$ruleId         = $recordData['rule_no'];

			// if ('--' != $ruleId) {
			//     global $ruleManager, $subruleManager;
			//     $ruleType = $ruleManager->getRuleType($ruleId);
			//     $associatedEnt = substr($cspSource, 16);
			//     $subruleManager->deleteSubruleByRecordData($ruleId, $productId, $ruleType, $associatedEnt, $minQty);
			//     $cspFunctions->deleteCSPPrice($cspSource, $productId, $minQty, $associatedEnt, $ruleType);
			// } else {
			//     $cspFunctions->deleteCSPPrice($cspSource, $productId, $minQty, $selectionName);
			// }
			$cspFunctions->evaluateAndRemoveCSPPrice($optionType, $selectionName, $recordData, $cspSource, $productId, $minQty, $ruleId);
			wp_die(1);
		}

		/**
		 * Ajax callback to remove the bulk CSP price records.
		 */
		public function removeBulkRecordCSPPrice() {
			global $cspFunctions;
			$postArray = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
			$optionType     = $postArray['option_type'];
			$selectionName  = $postArray['selection_name'];
			$selectedRecords= $postArray['record_data'];
			
			foreach ($selectedRecords as $recordData) {
				$cspSource      = $recordData['csp_source'];
				$productId      = $recordData['product_id'];
				$minQty         = $recordData['min_qty'];
				$ruleId         = $recordData['rule_no'];

				$cspFunctions->evaluateAndRemoveCSPPrice($optionType, $selectionName, $recordData, $cspSource, $productId, $minQty, $ruleId);
			}
			wp_die(1);
		}
		//function ends -- removeQueryLogCallback


		/**
		 * Generates a csv report by merging all the report containing batches.
		 * * Verify the nonce
		 * * gets report directory, deletes the report result file if exists.
		 * * fetches paths of all the files in the reports directory.
		 * * merge all the report batch files using method joinFiles.
		 * * returns the download path for the merged report file.
		 *
		 * @since 4.3.0
		 * @return void
		 */
		public function generateAndDownloadReport() {
			$importNonce = isset($_REQUEST['_wp_import_nonce']) ? sanitize_text_field($_REQUEST['_wp_import_nonce']):'';
			$nonceVerification = wp_verify_nonce($importNonce, 'import_nonce');

			//Override nonce verification for extending import functionality in any third party extension
			$nonceVerification = apply_filters('csp_import_nonce_verification', $nonceVerification);
			if (!$nonceVerification) {
				 echo 'Security Check';
				 exit;
			}

			$uploadDir=wp_upload_dir();
			$reportsDir     = $uploadDir['basedir'] . '/cspReports';
			$mergedReportFile   = $reportsDir . '/mergedReport.csv';
			if (file_exists($mergedReportFile)) {
				unlink($mergedReportFile);
			}
			
			$allBatches = glob($reportsDir . '/*.csv');
			$this->joinFiles($allBatches, $mergedReportFile);
			
			$reportFileUrl=$uploadDir['baseurl'] . '/cspReports/mergedReport.csv';
			echo esc_url($reportFileUrl);
			die();
		}


		/**
		 * JoinFiles method works as follows
		 * * Creates the result file on the path provided by $result parameter
		 * * Iterates each line in all the files mentioned in the array $files & writes it to the result file
		 *
		 * @since 4.3.0
		 * @param array $files
		 * @param string $result - path to the file which contains the merged batches.
		 * @return void
		 */
		private function joinFiles( array $files, $result) {
			if (!is_array($files)) {
				throw new Exception(' $files must be an array');
			}
		
			$mergedFile = fopen($result, 'w');
		
			foreach ($files as $file) {
				$singleFile = fopen($file, 'r');
				while (!feof($singleFile)) {
					fwrite($mergedFile, fgets($singleFile));
				}
				fclose($singleFile);
				unset($singleFile);
			}
			fclose($mergedFile);
			unset($mergedFile);
		}
	}
}
