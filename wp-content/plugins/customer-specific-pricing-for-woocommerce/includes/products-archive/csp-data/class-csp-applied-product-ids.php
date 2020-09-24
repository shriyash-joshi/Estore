<?php

namespace CSPProductArchive\CSPData;

/**
 * This class contains the implementation of the following methods,
 * * get product ids for different types of csp for the user.
 * * find an array of unique product ids.
 */

if (!class_exists('CSPAppliedProductIds')) {
	class  CSPAppliedProductIds {
		/**
		 * Id of the current user or the specified user
		 *
		 * @var int
		 */
		private $userId;
		
		/**
		 * User roles of the Specified user
		 *
		 * @var array(string)
		 */
		private $userRoles;

		/**
		 * User groups of the specified users
		 * empty array when groups plugin is not active
		 *
		 * @var array(int)
		 */
		private $userGroups;


		/**
		 * On instance creation all the user Roles & Groups are fetched
		 * for the specified user id & stored in the member variables for 
		 * later use
		 *
		 * @param int $userId
		 */
		public function __construct( $userId) {
			if (!empty($userId) && is_numeric($userId)) {
				$this->setUserId($userId);
				$this->setUserRoles($userId);
				$this->setUserGroupIds($userId);
			}
		}

		/**
		 * Setter method for $userGroups
		 *
		 * @param int $userId
		 * @return void
		 */
		private function setUserGroupIds( $userId) {
			$groupIds = array();
			if (!is_user_logged_in() || !defined('GROUPS_CORE_VERSION')) {
				return $groupIds;
			}
			global $wpdb;
			$userGroupId        = $wpdb->get_results($wpdb->prepare('SELECT group_id FROM ' . $wpdb->prefix . 'groups_user_group WHERE user_id=%d', $userId));
			foreach ($userGroupId as $groupId) {
				$groupIds[]		= $groupId->group_id;
			}
			$this->userGroups	=  $groupIds;
		}

		/**
		 * Setter method for the $userRoles - user roles
		 *
		 * @param int $userId
		 * @return void
		 */
		private function setUserRoles( $userId) {
			$userMeta		= get_userdata($userId);
			$this->userRoles= $userMeta->roles;
		}

		/**
		 * Setter method for $userId 
		 *
		 * @param int $userId
		 * @return void
		 */
		private function setUserId( $userId) {
			$this->userId	= $userId;
		}

		/**
		 * This method fetches product ids for all of the following CSP types
		 * * User Specific Product Prices
		 * * Role Specific Product Prices
		 * * Group Specific Product PRices (If groups plugin active)
		 * If Category pricing feature is enabled
		 * * User Specific Category Prices
		 * * Role Specific Category Prices
		 * * Group Specific Category PRices (If groups plugin active)
		 *
		 * Picks up uniq product ids
		 * 
		 * @return array
		 */
		public function getUniqueProductIds() {
			$productIds = array();
			//Product Specific Pricing
			$productIds = array_unique(array_merge($productIds, $this->getProductIdsUSP()));  
			$productIds = array_unique(array_merge($productIds, $this->getProductIdsRSP()));
			$productIds = array_unique(array_merge($productIds, $this->getProductIdsGSP()));

			//Category Specific Pricing
			if ($this->categoryPricingEnabled()) {
				$productIds = array_unique(array_merge($productIds, $this->getCatProductIdsUSP()));
				$productIds = array_unique(array_merge($productIds, $this->getCatProductIdsRSP()));
				$productIds = array_unique(array_merge($productIds, $this->getCatProductIdsGSP()));
			}
			
			//Get only parent products for all the product ids fetched.
			$productIds = $this->getAllTheParentProducts($productIds);  
			return $productIds;
		}


		// Product Pricing
		/**
		 * Returns all the product ids for which Product USP is applied
		 *
		 * @return array $productIds
		 */
		private function getProductIdsUSP() {
			$productIds 		= array();
			if (apply_filters('csp_archive_show_product_usp', true, $this->userId, $this->userRoles, $this->userGroups)) {
				include_once 'product-data/class-get-product-usp.php';
				$productUSP 		= new Products\USPAppliedProductIds();
				$productIds 		= $productUSP->getProductIds($this->userId);
			}
			return $productIds;
		}

		/**
		 * Returns all the product ids for which Product RSP is applied
		 *
		 * @return array $productIds
		 */
		private function getProductIdsRSP() {
			$productIds			= array();
			if (apply_filters('csp_archive_show_product_rsp', true, $this->userId, $this->userRoles, $this->userGroups)) {
				include_once 'product-data/class-get-product-rsp.php';
				$productRSP			= new Products\RSPAppliedProductIds();
				$productIds			= $productRSP->getProductIds($this->userRoles);
			}
			return $productIds;
		}

		/**
		 * Returns all the product ids for which Product GSP is applied
		 *
		 * @return array $productIds
		 */
		private function getProductIdsGSP() {
			$productIds 		= array();
			if (apply_filters('csp_archive_show_product_gsp', true, $this->userId, $this->userRoles, $this->userGroups)) {
				include_once 'product-data/class-get-product-gsp.php';
				$productRSP 		= new Products\GSPAppliedProductIds();
				$productIds 		= $productRSP->getProductIds($this->userGroups);
			}
			return $productIds;
		}


		// Category Pricing
		/**
		 * Returns all the product ids for the categories for which USP is applied
		 *
		 * @return array $productIds
		 */
		private function getCatProductIdsUSP() {
			$productIds 		= array();
			if (apply_filters('csp_archive_show_cat_usp', true, $this->userId, $this->userRoles, $this->userGroups)) {
				include_once 'category-data/class-get-cat-usp.php';
				$productUSP 		= new Categories\USPAppliedCategoryProductIds();
				$productIds 		= $productUSP->getProductIds($this->userId);
			}
			return $productIds;
		}

		/**
		 * Returns all the product ids for the categories for which RSP is applied
		 *
		 * @return array $productIds
		 */
		private function getCatProductIdsRSP() {
			$productIds 		= array();
			if (apply_filters('csp_archive_show_cat_rsp', true, $this->userId, $this->userRoles, $this->userGroups)) {
				include_once 'category-data/class-get-cat-rsp.php';
				$productRSP 		= new Categories\RSPAppliedCategoryProductIds();
				$productIds 		= $productRSP->getProductIds($this->userRoles);
			}
			return $productIds;
		}

		/**
		 * Returns all the product ids for the categories for which GSP is applied
		 *
		 * @return array $productIds
		 */
		private function getCatProductIdsGSP() {
			$productIds 		= array();
			if (apply_filters('csp_archive_show_cat_gsp', true, $this->userId, $this->userRoles, $this->userGroups)) {
				include_once 'category-data/class-get-cat-gsp.php';
				$productGSP 		= new Categories\GSPAppliedCategoryProductIds();
				$productIds 		= $productGSP->getProductIds($this->userGroups);
			}
			return $productIds;
		}

		/**
		 * Returns featur status of category specific pricing
		 *
		 * @return bool true|false
		 */
		private function categoryPricingEnabled() {
			$status				= get_option('cspCatPricingStatus', 'enable');
			if ('enable'===$status) {
				return true;
			}
			return false;
		}

		/**
		 * This method returns the array of all the parent product Ids for
		 * the avariable product variations & all the ids of the simple products.
		 * * Gets all the variable product variation & variation parent ids from the database
		 * * Seperates out variation ids from simple product ids.
		 * * Get all the parent ids for the product ids present in the Argument array $productIds
		 * * Stores only unique ids in an array
		 * * Returns an array of unique variation parent product Ids & simple product ids
		 * 
		 * @param array $productIds
		 * @return array
		 */
		private function getAllTheParentProducts( $productIds) {
			global $wpdb;
			$parentProducts 	= array();
			$variantsList 		= $wpdb->get_results('SELECT ID, post_parent FROM ' . $wpdb->prefix . 'posts where post_type="product_variation" AND post_status IN ("publish")');
			$variationIds 		= array_column($variantsList, 'ID');
			$variationParents	= array_column($variantsList, 'post_parent');
			$presentVariants	= array_intersect($variationIds, $productIds);
			$parentProducts		= array_diff($productIds, $presentVariants);

			foreach ($presentVariants as $variantId) {
				$position 		= array_search($variantId, $variationIds);
				if ($position && !in_array($variationParents[$position], $parentProducts)) {
					$parentProducts[] = $variationParents[$position];
				}	
			}

			return $parentProducts;
		}
	}
}
