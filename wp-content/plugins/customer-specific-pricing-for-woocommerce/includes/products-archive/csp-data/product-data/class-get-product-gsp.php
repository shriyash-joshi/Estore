<?php

namespace CSPProductArchive\CSPData\Products;

/**
 * This class is contains the implementation of the methods which returns
 * unique product ids for which group specific pricing applied for the user Groups specified
 * 
 * @since 4.4.0
 */
if (!class_exists('GSPAppliedProductIds')) {
	class  GSPAppliedProductIds {

		/**
		 * Returns the unique product ids with the csp discounts 
		 * for the specified user groups
		 *
		 * @param array $userGroups - array of user group ids
		 * @return array $productIds 
		 */
		public function getProductIds( $userGroups) {
			$productIds = array();
			if (!empty($userGroups)) {
				$productIds	= $this->getProductIdsFromDbFor($userGroups);
			}
			return $productIds;
		}

		/**
		 * This method fetches the product ids for which
		 * group based CSP is applied from the database table
		 *
		 * @param array $userGroups
		 * @return array 
		 */
		private function getProductIdsFromDbFor( $userGroups) {
			global $wpdb;
			$productIds = $wpdb->get_col($wpdb->prepare('SELECT DISTINCT(product_id) FROM ' . $wpdb->prefix . 'wusp_group_product_price_mapping  WHERE group_id IN (' . implode(', ', array_fill(0, count($userGroups), '%s')) . ')', $userGroups));
			return $productIds;
		}
	}
}
