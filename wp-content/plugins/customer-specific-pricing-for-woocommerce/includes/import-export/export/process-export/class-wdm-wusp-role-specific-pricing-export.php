<?php

namespace cspImportExport\cspExport;

/**
 * Fetch and return the role specific pricing data for exporting in csv file
 *
 * @author WisdmLabs
 */
if (! class_exists('WdmWuspRoleSpecificPricingExport')) {
	class WdmWuspRoleSpecificPricingExport extends \cspImportExport\cspExport\WdmWuspExport {
	
		/**
		 * Fetch the data form database for role specific pricing.
		 *
		 * @global object $wpdb database object.
		 * @return array content for creating csv
		 */
		public function wdmFetchData() {
			global $wpdb;
			$postArray = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
			$exportType            = isset($postArray['export_type']) && 'sku' == $postArray['export_type'] ? 'sku' : 'product_id';

			if ('sku' == $exportType) {
				$role_heading   = array( 'SKU', 'role', 'Min Qty', 'Flat', '%' );
			} else {
				$role_heading   = array( 'Product id', 'role', 'Min Qty', 'Flat', '%' );
			}

			$role_product_result        = $wpdb->get_results('SELECT product_id, role, min_qty, price, flat_or_discount_price as discount_price FROM ' . $wpdb->prefix . 'wusp_role_pricing_mapping, ' . $wpdb->prefix . 'posts WHERE ' . $wpdb->prefix . 'wusp_role_pricing_mapping.product_id = ' . $wpdb->prefix . 'posts.id');
  
			if ($role_product_result) {
				$role_product_result = $this->processResult($role_product_result, $exportType);
				return array( $role_heading, $role_product_result );
			}
		}

		/**
		 * [processResult process the data to be exported]
		 *
		 * @param  [array] $role_product_result [Fetched result from database]
		 * @param  [string] $exportType         Export type to decide whether to export using
		 *                                      Product ID or SKU.
		 *                                      Possible values: 'sku', 'product_id'.
		 * @return [array] $role_product_result [Processed result]
		 */
		public function processResult( $role_product_result, $exportType = 'product_id') {
			global $cspFunctions;

			foreach ($role_product_result as $key => $result) {
				if ('sku' == $exportType) {
					$productSKU              = $cspFunctions->getProductSku($role_product_result[$key]->product_id);
					// If SKU is empty, don't add the record in the CSV.
					if (empty($productSKU)) {
						continue;
					}
					$formatedResult[$key]           = new \stdClass();
					$formatedResult[$key]->sku      = $productSKU;
					$formatedResult[$key]->role     = $role_product_result[$key]->role;
					$formatedResult[$key]->min_qty  = $role_product_result[$key]->min_qty;
					$formatedResult[$key]->price    = $role_product_result[$key]->price;
				} else {
					$formatedResult[$key] = $role_product_result[$key];
				}

				if ( 2==$result->discount_price) {
					$formatedResult[$key]->discount_price = $result->price;
					$formatedResult[$key]->price = '';
				} else {
					$formatedResult[$key]->discount_price = '' ;
				}
			}
			return array_values($formatedResult);
		}

		/**
		 * Returns name of file for export
		 *
		 * @return string filename
		 */
		public function wdmFileName() {
			return '/role_specific_pricing.csv';
		}
	}

}
