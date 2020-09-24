<?php

namespace cspSingleView\searchSetting;

if (!class_exists('WdmSingleViewSearch')) {
	/**
	* Class for the search tab settings in the CSP single view.
	*/
	class WdmSingleViewSearch {
	

		/**
		* Adds action for the search tab in CSP.
		*/
		public function __construct() {
			add_action('csp_single_view_search_settings', array($this,'searchSettingsCallback'));
		}

		/**
		* For the Search tab
		* Enqueues the scripts and styles.
		* For the search options make dropdown of rule-types.
		*/
		public function searchSettingsCallback() {
			?>
			<div class="wrap"><h3 class="csp-tab-header">
			<?php esc_html_e('Search By & Delete', 'customer-specific-pricing-for-woocommerce'); ?></h3>
			</div>
			<?php
			$available_options = apply_filters('csp_single_view_option_types', array('customer' => __('Customer', 'customer-specific-pricing-for-woocommerce'),
									'role' => __('Role', 'customer-specific-pricing-for-woocommerce'),
									'group'=> __('Group', 'customer-specific-pricing-for-woocommerce'))
								);

			self::enqueueScript();

			?>
			<hr/>
				
				<div class="wdm-csp-single-view-search-wrapper">

					<div class="form-group row wdm-csp-single-view-from-group">
					<label class="col-md-2 form-control-label"> <?php echo esc_html_e('Search price being applied for a', 'customer-specific-pricing-for-woocommerce'); ?> </label>
						<div class="col-md-2 form-control-wrap">
							<select name="wdm_setting_option_type" id="wdm_setting_option_type" class="form-control wdm-csp-single-view-form-control">
								<option value="-1"><?php echo esc_html__('Select any value', 'customer-specific-pricing-for-woocommerce'); ?></option>
			<?php 
			if (!empty($available_options) && is_array($available_options)) {
				foreach ($available_options as $key => $value) { 
					?>
		<option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($value); ?></option>
											<?php
				} //foreach ends
			} //if ends
			?>
							</select>
						</div>
					</div>

					<div class="wdm-csp-single-view-result-wrapper">
					</div>

					<input type="button" class="button-primary wdm-remove-sel-csp-price" name="remove-sel-csp-price" id="remove-sel-csp-price" value="<?php esc_attr_e('Delete', 'customer-specific-pricing-for-woocommerce'); ?>">

				</div>
					<?php
		}//function ends -- Search Tab callback

		/**
		* Enqueue the scripts
		* Prepare the data for localization.
		* Enqueue styles and scripts for datatables.
		*/
		private function enqueueScript() {
			//Enqueue JS & CSS

			wp_enqueue_style('csp_general_css_handler', plugins_url('/css/single-view/wdm-single-view.css', dirname(dirname(dirname(__FILE__)))), array(), CSP_VERSION);
			wp_enqueue_style('csp_tiptip_css', plugins_url('/css/csp-tiptip.css', dirname(dirname(dirname(__FILE__)))), array(), CSP_VERSION);

			wp_enqueue_script('jquery-tiptip');
			wp_enqueue_script('csp_single_search_js', plugins_url('/js/single-view/wdm-search-settings.js', dirname(dirname(dirname(__FILE__)))), array('jquery'), CSP_VERSION);

			$titles = array(
							array( 'title' => '<input name="select_all" value="1" type="checkbox">' ),
							array( 'title' => __('Product Name', 'customer-specific-pricing-for-woocommerce') ),
							array( 'title' => __('Min Qty', 'customer-specific-pricing-for-woocommerce') ),
							array( 'title' => __('Active Price', 'customer-specific-pricing-for-woocommerce') ),
							array( 'title' => __('Discount Type', 'customer-specific-pricing-for-woocommerce') ),
							array( 'title' => __('Rule No.', 'customer-specific-pricing-for-woocommerce') ),
							array( 'title' => __('Source', 'customer-specific-pricing-for-woocommerce') ),
							array( 'title' => __('Action', 'customer-specific-pricing-for-woocommerce') ),
							);

			$array_to_be_sent = array('admin_ajax_path' => admin_url('admin-ajax.php'),
			 'loading_image_path' => plugins_url('/images/loading .gif', dirname(dirname(dirname(__FILE__)))),
			 'title_names' => $titles,
			 'length_menu' => __('Show _MENU_ entries', 'customer-specific-pricing-for-woocommerce'),
			 'showing_info'=> __('Showing _START_ to _END_ of _TOTAL_ entries', 'customer-specific-pricing-for-woocommerce'),
			 'empty_table' => __('No data available in table', 'customer-specific-pricing-for-woocommerce'),
			 'info_empty'=> __('Showing 0 to 0 of 0 entries', 'customer-specific-pricing-for-woocommerce'),
			 'info_filtered'=> __('(filtered from _MAX_ total entries)', 'customer-specific-pricing-for-woocommerce'),
			 'zero_records'=> __('No matching records found', 'customer-specific-pricing-for-woocommerce'),
			 'loading_records'=> __('Loading...', 'customer-specific-pricing-for-woocommerce'),
			 'processing' => __('Processing...', 'customer-specific-pricing-for-woocommerce'),
			 'search' => __('Search:', 'customer-specific-pricing-for-woocommerce'),
			 'first' => __('First', 'customer-specific-pricing-for-woocommerce'),
			 'prev' => __('Previous', 'customer-specific-pricing-for-woocommerce'),
			 'next' => __('Next', 'customer-specific-pricing-for-woocommerce'),
			 'last' => __('Last', 'customer-specific-pricing-for-woocommerce'),
			 'remove_rec_conf_txt' => __('Do you want to remove this CSP price?', 'customer-specific-pricing-for-woocommerce'),
			 'remove_rec_role_conf_txt' => __('When \'Role\' specific pricing is removed, then pricing is removed for all the users belonging to that particular \'Role\'. Do you want to remove this CSP price?', 'customer-specific-pricing-for-woocommerce'),
			 'remove_rec_group_conf_txt' => __('When \'Group\' specific pricing is removed, then pricing is removed for all the users belonging to that particular \'Group\'. Do you want to remove this CSP price?', 'customer-specific-pricing-for-woocommerce'),
			 'remove_rec_cutomer_opt_type_conf_txt' => __('When \'Role\' or \'Group\' specific pricing is removed, then pricing is removed for all the users belonging to that particular \'Role\' or \'Group\'. Do you want to remove this CSP price?', 'customer-specific-pricing-for-woocommerce'),
			 'remove_sel_rec_conf_txt' => __('Do you want to remove the selected CSP records?', 'customer-specific-pricing-for-woocommerce'),
			 'remove_sel_rec_customer_opt_type_conf_txt' => __('When \'Role\' or \'Group\' specific pricing is removed, then pricing is removed for all the users belonging to that particular \'Role\' or \'Group\'. Do you want to remove the selected CSP records?', 'customer-specific-pricing-for-woocommerce'),
			 'error_selection_empty' => __('Please, select some records to be removed.', 'customer-specific-pricing-for-woocommerce'),
			 // 'zero_records'=> __('No matching records found', 'customer-specific-pricing-for-woocommerce'),
			 'query_log_link' => admin_url('/admin.php?page=customer_specific_pricing_single_view&tab=product_pricing&query_log=')
			 );

			wp_localize_script('csp_single_search_js', 'single_view_obj', $array_to_be_sent);

			//Bootstrap
			wp_enqueue_style('csp_bootstrap_css', plugins_url('/css/import-css/bootstrap.css', dirname(dirname(dirname(__FILE__)))), array(), CSP_VERSION);

			//Datatable
			wp_enqueue_script('csp_singleview_datatable_js', plugins_url('/js/single-view/jquery.dataTables.min.js', dirname(dirname(dirname(__FILE__)))), array('jquery'), CSP_VERSION);
			wp_enqueue_script('csp_singleview_bootstrap_datatable_js', plugins_url('/js/single-view/dataTables.bootstrap.min.js', dirname(dirname(dirname(__FILE__)))), array('jquery'), CSP_VERSION);
			wp_enqueue_script('csp_singleview_button_js', plugins_url('/js/single-view/dataTables.buttons.min.js', dirname(dirname(dirname(__FILE__)))), array('csp_singleview_datatable_js'), CSP_VERSION);
			wp_enqueue_script('csp_singleview_button_column_js', plugins_url('/js/single-view/buttons.colVis.min.js', dirname(dirname(dirname(__FILE__)))), array('csp_singleview_datatable_js'), CSP_VERSION);

			wp_enqueue_style('csp_datatable_bootstrap_css', plugins_url('/css/single-view/dataTables.bootstrap.min.css', dirname(dirname(dirname(__FILE__)))), array(), CSP_VERSION);
			wp_enqueue_style('csp_datatable_css', plugins_url('/css/single-view/jquery.dataTables.min.css', dirname(dirname(dirname(__FILE__)))), array(), CSP_VERSION);
			wp_enqueue_style('csp_button_datatable_css', plugins_url('/css/single-view/buttons.dataTables.min.css', dirname(dirname(dirname(__FILE__)))), array(), CSP_VERSION);
		}//enqueueScript ends
	}
}
