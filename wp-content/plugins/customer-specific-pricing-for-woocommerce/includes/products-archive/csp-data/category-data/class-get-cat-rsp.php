<?php

namespace CSPProductArchive\CSPData\Categories;

/**
 * This class contain the methods which finds which category specific
 * role based rules are applied for the user groups specified,
 * 
 * @since 4.4.0
 */
include_once 'class-category-commons.php';
if (!class_exists('RSPAppliedCategoryProductIds')) {
	class  RSPAppliedCategoryProductIds {

		/**
		 * This method returns all the product Ids 
		 * in the discounted categories for the specified role slugs
		 *
		 * @param array $userRoles - array of user role slugs
		 * @return array $productIds
		 */
		public function getProductIds( $userRoles) {
			$productIds = array();
			if (!empty($userRoles)) {
				$catSlugs	= $this->getCatSlugsFromDbFor($userRoles);
				$productIds = CSPCategoryCommons::getProductIdsFor($catSlugs);
			}
			return $productIds;
		}

		/**
		 * This method finds the unique category slugs which have discounted for the
		 * user roles specified in the parameter
		 *
		 * @param array $userRoles - array of role slugs.
		 * @return array $catSlugs - unique category slugs discounted for the role slugs specified
		 */
		private function getCatSlugsFromDbFor( $userRoles) {
			global $wpdb;
			$catSlugs = $wpdb->get_col($wpdb->prepare('SELECT DISTINCT(cat_slug) FROM ' . $wpdb->prefix . 'wcsp_role_category_pricing_mapping  WHERE role IN(' . implode(', ', array_fill(0, count($userRoles), '%s')) . ')', $userRoles));
			return $catSlugs;
		}
	}
}
