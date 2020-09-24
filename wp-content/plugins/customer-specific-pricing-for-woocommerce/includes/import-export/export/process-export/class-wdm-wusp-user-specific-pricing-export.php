<?php

namespace cspImportExport\cspExport;

/**
 * Fetch and return the customer specific pricing data for exporting in csv file
 *
 * @author WisdmLabs
 */
if (! class_exists('WdmWuspUserSpecificPricingExport')) {
	/**
	* When User Specific Data is exported in csv file.
	* Returns the data of user specific pricing for csv.
	*/
	class WdmWuspUserSpecificPricingExport extends \cspImportExport\cspExport\WdmWuspExport {
	
		/**
		 * Fetch the data form database for user specific pricing.
		 *
		 * @global object $wpdb database object.
		 * @return array content for creating csv
		 */
		public function wdmFetchData() {
			global $wpdb;
			$user_product_result = array();
			$post                = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
			// $wpusp_pricing_table = $wpdb->prefix . 'wusp_user_pricing_mapping';
			// $wdm_users           = $wpdb->prefix . 'users';
			// $wdm_post            = $wpdb->prefix . 'posts';
			$exportType          = isset($post['export_type']) && 'sku' == $post['export_type'] ? 'sku' : 'product_id';

			if ('sku' == $exportType) {
				$user_headings = array( 'SKU', 'User', 'Min Qty', 'Flat', '%' );
			} else {
				$user_headings = array( 'Product id', 'User', 'Min Qty', 'Flat', '%' );
			}
			if (!is_multisite()) {
				$user_product_result = $wpdb->get_results('SELECT product_id, user_login, min_qty, price,
				flat_or_discount_price as discount_price 
				FROM ' . 
				$wpdb->prefix . 'wusp_user_pricing_mapping, ' . 
				$wpdb->prefix . 'users, ' . 
				$wpdb->prefix . 'posts 
				WHERE ' . 
				$wpdb->prefix . 'wusp_user_pricing_mapping.user_id=' . $wpdb->prefix . 'users.id
				and ' . 
				$wpdb->prefix . 'wusp_user_pricing_mapping.product_id = ' . $wpdb->prefix . 'posts.id');
			} else {
				//multisite
				//$tableAlias='wdm_current_site_users';
				$capabilities  =$wpdb->prefix . 'capabilities';

				$user_product_result = $wpdb->get_results($wpdb->prepare('SELECT product_id, user_login, min_qty, price,
                flat_or_discount_price as discount_price 
                FROM ' . 
				$wpdb->prefix . 'wusp_user_pricing_mapping,
                (SELECT * FROM ' . $wpdb->base_prefix . 'users u JOIN ' . $wpdb->base_prefix . 'usermeta um ON u.id=um.user_id WHERE um.meta_key=%s) wdm_current_site_users,
                ' . $wpdb->prefix . 'posts 
                WHERE 
                ' . $wpdb->prefix . 'wusp_user_pricing_mapping.user_id=wdm_current_site_users.id
                 and 
                ' . $wpdb->prefix . 'wusp_user_pricing_mapping.product_id = ' . $wpdb->prefix . 'posts.id', $capabilities));
			}

			
			if ($user_product_result) {
				$user_product_result = $this->processResult($user_product_result, $exportType);
				return array( $user_headings, $user_product_result );
			}
		}

		/**
		 * [processResult process the data to be exported]
		 *
		 * @param  [array] $user_product_result [Fetched result from database]
		 * @param  [string] $exportType         Export type to decide whether to export using
		 *                                      Product ID or SKU.
		 *                                      Possible values: 'sku', 'product_id'.
		 * @return [array] $user_product_result [Processed result]
		 */
		public function processResult( $user_product_result, $exportType = 'product_id') {
			global $cspFunctions;
			foreach ($user_product_result as $key => $result) {
				if ('sku' == $exportType) {
					$productSKU = $cspFunctions->getProductSku($user_product_result[$key]->product_id);
					// If SKU is empty, don't add the record in the CSV.
					if (empty($productSKU)) {
						continue;
					}
					$formatedResult[$key]             = new \stdClass();
					$formatedResult[$key]->sku        = $productSKU;
					$formatedResult[$key]->user_login = $user_product_result[$key]->user_login;
					$formatedResult[$key]->min_qty    = $user_product_result[$key]->min_qty;
					$formatedResult[$key]->price      = $user_product_result[$key]->price;
					// $formatedResult[$key]->discount_price   = $user_product_result[$key]->discount_price;
				} else {
					$formatedResult[$key] = $user_product_result[$key];
				}

				if (2==$result->discount_price) {
					$formatedResult[$key]->discount_price = $result->price;
					$formatedResult[$key]->price          = '';
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
			return '/user_specific_pricing.csv';
		}
	}

}
