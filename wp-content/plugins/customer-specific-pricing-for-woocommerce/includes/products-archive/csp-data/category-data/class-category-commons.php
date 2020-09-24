<?php

namespace CSPProductArchive\CSPData\Categories;

/**
 * This class contains the implementation of the common methods used
 * for category specific pricing in feature "CSP applied user specific product archive".
 * 
 * @since 4.4.0
 */
if (!class_exists('CSPCategoryCommons')) {
	class  CSPCategoryCommons {

		/**
		 * This method returns all the product ids of the specified categories.
		 *
		 * @param array $catSlugs - array of category slugs 
		 * @return array $productIds - list of the product Ids belongs to the categories passed
		 */
		public static function getProductIdsFor( $catSlugs) {
			$productIds = array();
			if (!empty($catSlugs)) {
				$args = array(
					'post_type' => 'product',
					'product_cat'=> implode(', ', $catSlugs),
					'numberposts' => -1,
					'post_status' => 'publish',
					'fields' => 'ids');
					
				$productIds = get_posts($args);
			}
			return $productIds;
		}
	}
}
