<?php

namespace cspImportExport\cspExport;

/**
 * Display the export option for customer specific,role specific and group specific pricing
 *
 * @author WisdmLabs
 */

if (!class_exists('WdmWuspExport')) {
	/**
	* Class to display export options.
	*/
	class WdmWuspExport {
	

		private $_class_value_pairs = array();

		/**
		 * Call the function for display export option and create csv file
		 */
		public function __construct() {
			add_action('show_export', array($this, 'wdmShowExportOptions'));
			add_filter('csp_product_sku', array($this, 'returnVariationProductEmptySKU'), 20, 2);
		}

		/**
		 * Store class value pairs for display in dropdown
		 *
		 * @param array $class_value_pairs
		 */
		public function setOptionValuesPair( $class_value_pairs) {
			$this->_class_value_pairs = $class_value_pairs;
		}

		/**
		 * Display export form
		 * Prepares data for js.
		 * Creates nonce for export, error messages.
		 */
		public function wdmShowExportOptions() {
			$array_to_be_send = array(
					'ajaxurl'       =>  admin_url('admin-ajax.php'),
					'please_Assign_valid_user_file_msg' => __('Please Assign User Specific Prices to export the CSV file successfully.', 'customer-specific-pricing-for-woocommerce'),
					'please_Assign_valid_role_file_msg' => __('Please Assign Role Specific Prices to export the CSV file successfully.', 'customer-specific-pricing-for-woocommerce'),
					'please_Assign_valid_group_file_msg' => __('Please Assign Group Specific Prices to export the CSV file successfully.', 'customer-specific-pricing-for-woocommerce'),
					'export_nonce'      => wp_create_nonce('export_nonce'),
				);
			wp_enqueue_style('wdm_csp_export_css', plugins_url('/css/export-css/wdm-csp-export.css', dirname(dirname(dirname(__FILE__)))), array(), CSP_VERSION);
			wp_enqueue_script('wdm_csp_export_js', plugins_url('/js/export-js/wdm-csp-export.js', dirname(dirname(dirname(__FILE__)))), array('jquery'), CSP_VERSION, true);
			wp_localize_script('wdm_csp_export_js', 'wdm_csp_export_ajax', $array_to_be_send);
			?>
			<div class="wrap">
				<h3 class="import-export-header"> <?php esc_html_e('Export Pricing Rules', 'customer-specific-pricing-for-woocommerce'); ?> </h3>
			 </div>
			<div id="wdm_message" class="below-h2" style="display: block;"><p class="wdm_message_p"></p></div>
			<form name="export_form" class="wdm_export_form" method="POST">
				<table cellspacing="10px" class = "wdm_csp_export_table">
					<?php 
					$allowedHtml = array(
						'tr'=> true,
						'th'=> array('scope'=>true),
						'label'=> array('class'=>true),
						'div' =>true,
						'span'=>array('class'=>true),
						'td' => true,
						'fieldset' => true,
						'legend'=> array('class'=>true),
						'ul'=> array('class'=>true),
						'input'=> array('type'=>true, 'class'=>true, 'name'=>true, 'value'=>true, 'checked'=>true),
					);
					echo wp_kses($this->showExportByfields(), $allowedHtml); 
					
					?>
					<tr>
						<th>
							<label for="dd_show_export_options"><?php esc_html_e('Export Type :', 'customer-specific-pricing-for-woocommerce'); ?> </label>
						</th>
						<td>
							<select name="dd_show_export_options" id="dd_show_export_options">
								<?php
								foreach ($this->_class_value_pairs as $key => $val) {
									echo '<option value=' . esc_attr($key) . '>' . esc_html($val) . '</option>';
								}
								?>
							</select>
						</td>
					</tr>
					<tr>
						<td>
						<input type="submit" value="<?php esc_html_e('Export', 'customer-specific-pricing-for-woocommerce'); ?>" id="export" name="export" class="button button-primary">
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<?php
							esc_html_e('While exporting using SKU, Please make sure all products have SKUs.', 'customer-specific-pricing-for-woocommerce');
							?>
							<br>
							<?php
							esc_html_e('The products which do not have an associated SKU will be skipped during the export operation', 'customer-specific-pricing-for-woocommerce');
							?>
						</td>
					</tr>
				</table>
			</form>
			<?php
		}

		public function showExportByfields() {
			?>
			<tr>
				<th scope="row">
					<label><?php esc_html_e('Export Using', 'customer-specific-pricing-for-woocommerce'); ?></label>
					<div>
						<span class="icon-help"></span>
					</div>
				</th>
				<td>
					<fieldset>
						<legend class="screen-reader-text">
						</legend>
						<ul class="wdm_csp_ul">
							<li>
								<label class="">
									<input type="radio" class="wdm_csp_export_using" name="wdm_csp_export_using" value="product id" checked="checked">
									<span class=""><?php esc_html_e('Product Id', 'customer-specific-pricing-for-woocommerce'); ?></span>
								</label>
							</li>
							<li>
								<label>
									<input type="radio" class="wdm_csp_export_using" name="wdm_csp_export_using" value="sku">
									<span class=""><?php esc_html_e('SKU', 'customer-specific-pricing-for-woocommerce'); ?></span>
								</label>
							</li>
						</ul>
					</fieldset>
				</td>
			</tr>
			<?php
		}

		/**
		 * Hook - returnVariationProductEmptySKU
		 * Returns the empty string. By default, if SKU is not set for variation product,
		 * then parent SKU is returned. This function returns empty string if
		 * variation product is not having any SKU set.
		 */
		public function returnVariationProductEmptySKU( $sku, $product) {
			// If product is variation product and parent SKU is same as SKU, it means that
			// the SKU is not set for the current variation product as SKU is unique can't 
			// be duplicated/repeated.
			if ('variation' == $product->get_type() && $sku == $product->get_parent_data()['sku']) {
				$sku = '';
			}

			return $sku;
		}
	}

}

/**
 * Include all files required for Export
 */
require_once 'process-export/class-wdm-wusp-group-specific-pricing-export.php';
require_once 'process-export/class-wdm-wusp-role-specific-pricing-export.php';
require_once 'process-export/class-wdm-wusp-user-specific-pricing-export.php';
