<?php

namespace CSPProductArchive\CSPData\Categories;

/**
 * This class contain the methods which finds which category specific
 * group based rules are applied for the user groups specified,
 * 
 * @since 4.4.0
 */
include_once 'class-category-commons.php';
if (!class_exists('GSPAppliedCategoryProductIds')) {
	class  GSPAppliedCategoryProductIds {

		/**
		 * This method returns all the product Ids 
		 * in the discounted categories for the specified group ids
		 *
		 * @param array $userGroups - array of user group Ids
		 * @return array $productIds
		 */
		public function getProductIds( $userGroups) {
			$productIds = array();
			if (!empty($userGroups)) {
				$catSlugs	= $this->getCatSlugsFromDbFor($userGroups);
				$productIds = CSPCategoryCommons::getProductIdsFor($catSlugs);
			}
			return $productIds;
		}

		/**
		 * This method finds the unique category slugs which have discounted for the
		 * user groups specified in the parameter
		 *
		 * @param array $userGroups - array of group ids.
		 * @return array $catSlugs - unique category slugs discounted for the group ids specified
		 */
		private function getCatSlugsFromDbFor( $userGroups) {
			global $wpdb;
			$catSlugs = $wpdb->get_col($wpdb->prepare('SELECT DISTINCT(cat_slug) FROM ' . $wpdb->prefix . 'wcsp_group_category_pricing_mapping  WHERE group_id IN(' . implode(', ', array_fill(0, count($userGroups), '%d')) . ')', $userGroups));
			return $catSlugs;
		}
	}
}
