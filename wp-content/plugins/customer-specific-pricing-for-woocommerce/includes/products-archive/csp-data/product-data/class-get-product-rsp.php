<?php

namespace CSPProductArchive\CSPData\Products;

/**
 * This class is contains the implementation of the methods which returns
 * unique product ids for which group specific pricing applied for the user 
 * roles specified
 * 
 * @since 4.4.0
 */
if (!class_exists('RSPAppliedProductIds')) {
	class  RSPAppliedProductIds {

		/**
		 * This method returns the product ids for which role based csp is present
		 * for the User roles specified
		 *
		 * @param array $userRoles - array of user role slugs
		 * @return array
		 */
		public function getProductIds( $userRoles) {
			$productIds = array();
			if (!empty($userRoles)) {
				$productIds	= $this->getProductIdsFromDbFor($userRoles);
			}
			return $productIds;
		}

		/**
		 * This method fetches product ids from the database table 
		 * for which the role based pricing is applied.
		 *
		 * @param array $userRoles
		 * @return array
		 */
		private function getProductIdsFromDbFor( $userRoles) {
			global $wpdb;
			$productIds = $wpdb->get_col($wpdb->prepare('SELECT DISTINCT(product_id) FROM ' . $wpdb->prefix . 'wusp_role_pricing_mapping  WHERE role IN (' . implode(', ', array_fill(0, count($userRoles), '%s')) . ')', $userRoles));
			return $productIds;
		}
	}
}
