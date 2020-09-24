<?php

namespace CSPProductArchive\CSPData\Categories;

/**
 * This class contain the methods which finds which category specific
 * user based rules are applied for the user ids specified,
 * 
 * @since 4.4.0
 */
include_once 'class-category-commons.php';
if (!class_exists('USPAppliedCategoryProductIds')) {
	class  USPAppliedCategoryProductIds {

		/**
		 * This method returns all the product Ids 
		 * in the discounted categories for the specified user Id
		 *
		 * @param int $userId - user Id
		 * @return array $productIds
		 */
		public function getProductIds( $userId) {
			$productIds = array();
			if (!empty($userId) && is_numeric($userId)) {
				$catSlugs	= $this->getCatSlugsFromDbFor($userId);
				$productIds = CSPCategoryCommons::getProductIdsFor($catSlugs);
			}
			return $productIds;
		}

		/**
		 * This method finds the unique category slugs which have discounted for the
		 * user id specified in the parameter
		 *
		 * @param int $userId - User Id.
		 * @return array $catSlugs - unique category slugs discounted for the role slugs specified
		 */
		private function getCatSlugsFromDbFor( $userId) {
			global $wpdb;
			$catSlugs = $wpdb->get_col($wpdb->prepare('SELECT DISTINCT(cat_slug) FROM ' . $wpdb->prefix . 'wcsp_user_category_pricing_mapping  WHERE user_id=%d', $userId));
			return $catSlugs;
		}
	}
}
