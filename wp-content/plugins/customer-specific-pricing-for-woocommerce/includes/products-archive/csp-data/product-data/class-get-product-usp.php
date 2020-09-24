<?php

namespace CSPProductArchive\CSPData\Products;

/**
 * This class is contains the implementation of the methods which returns
 * unique product ids for which group specific pricing applied for the specified user Id
 * 
 * @since 4.4.0
 */
if (!class_exists('USPAppliedProductIds')) {
	class  USPAppliedProductIds {
		
		/**
		 * Returns unique product ids for which user specific CSP is applied
		 * 
		 * @param  int $userId
		 * @return array $productIds
		 */
		public function getProductIds( $userId) {
			$productIds = array();
			if (!empty($userId) && is_numeric($userId)) {
				$productIds	= $this->getProductIdsFromDbFor($userId);
			}
			return $productIds;
		}

		/**
		 * Fetches unique product ids from the databse table for which 
		 * the User Specific Pricing is applied
		 *
		 * @param int $userId
		 * @return array $productIds
		 */
		private function getProductIdsFromDbFor( $userId) {
			global $wpdb;
			$productIds = $wpdb->get_col($wpdb->prepare('SELECT DISTINCT(product_id) FROM ' . $wpdb->prefix . 'wusp_user_pricing_mapping  WHERE user_id=%d', $userId));
			return $productIds;
		}
	}
}
