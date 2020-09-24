<?php

namespace cspImportExport\cspExport;

/**
 * Fetch and return the group specific pricing data for exporting in csv file
 *
 * @author WisdmLabs
 */
if (! class_exists('WdmWuspGroupSpecificPricingExport')) {
	class WdmWuspGroupSpecificPricingExport extends \cspImportExport\cspExport\WdmWuspExport {
	
		/**
		 * Fetch the data form database for group specific pricing.
		 *
		 * @global object $wpdb database object
		 * @return array content for creating csv
		 */
		public function wdmFetchData() {
					 /**
		 * Check if Groups is active
		 */
			global $cspFunctions;
			$group_product_result=array();
			$postArray = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
			if ($cspFunctions->wdmIsActive('groups/groups.php')) {
				global $wpdb;
				$exportType        = isset($postArray['export_type']) && 'sku' == $postArray['export_type'] ? 'sku' : 'product_id';

				if ('sku' == $exportType) {
					$group_headings  = array( 'SKU', 'Group Name', 'Min Qty', 'Flat', '%' );
				} else {
					$group_headings  = array( 'Product id', 'Group Name', 'Min Qty', 'Flat', '%' );
				}

				$group_product_result           = $wpdb->get_results('SELECT product_id, name, min_qty, price, flat_or_discount_price as discount_price FROM ' . $wpdb->prefix . 'wusp_group_product_price_mapping,' . $wpdb->prefix . 'groups_group,' . $wpdb->prefix . 'posts WHERE ' . $wpdb->prefix . 'wusp_group_product_price_mapping.group_id=' . $wpdb->prefix . 'groups_group.group_id and ' . $wpdb->prefix . 'wusp_group_product_price_mapping.product_id = ' . $wpdb->prefix . 'posts.id');

				if ($group_product_result) {
					$group_product_result = $this->processResult($group_product_result, $exportType);
					return array( $group_headings, $group_product_result );
				}
			}
		}

		/**
		 * [processResult process the data to be exported]
		 *
		 * @param  [array] $group_product_result [Fetched result from database]
		 * @param  [string] $exportType         Export type to decide whether to export using
		 *                                      Product ID or SKU.
		 *                                      Possible values: 'sku', 'product_id'.
		 * @return [array] $group_product_result [Processed result]
		 */
		public function processResult( $group_product_result, $exportType = 'product_id') {
			global $cspFunctions;

			foreach ($group_product_result as $key => $result) {
				if ('sku' == $exportType) {
					$productSKU = $cspFunctions->getProductSku($group_product_result[$key]->product_id);
					// If SKU is empty, don't add the record in the CSV.
					if (empty($productSKU)) {
						continue;
					}
					$formatedResult[$key]               = new \stdClass();
					$formatedResult[$key]->sku          = $productSKU;
					$formatedResult[$key]->name         = $group_product_result[$key]->name;
					$formatedResult[$key]->min_qty      = $group_product_result[$key]->min_qty;
					$formatedResult[$key]->price        = $group_product_result[$key]->price;
					// $formatedResult[$key]->discount_price   = $user_product_result[$key]->discount_price;
				} else {
					$formatedResult[$key] = $group_product_result[$key];
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
			return '/group_specific_pricing.csv';
		}
	}

}
