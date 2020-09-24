<?php

namespace CSPCartDiscount;

if (!class_exists('WdmCSPUserProductData')) {
	/**
	* Class contains methods related to the product data retrival for the cart discount feature.
	*/
	class WdmCSPUserProductData {
	
		
		/**
		 * Retrive ID & name pairs for the simple products from the database
		 *
		 * @return void
		 */
		public function getAllProductsIdNamePairs() {
			global $wpdb;
			$parentVariations       = array();
			$parentVariationNames   = array();
			$fullProductList        = array();
			$productsList	=	$wpdb->get_results(
													'SELECT ID, post_title FROM ' . $wpdb->prefix . 
													'posts where post_type="product" AND 
													post_status IN ("draft", "publish", "pending")'
												);

			$variantsList	=	$wpdb->get_results(		
													'SELECT ID, post_parent FROM ' . $wpdb->prefix . 
													'posts where post_type="product_variation" AND 
													post_status IN ("private", "publish", "pending")'
												);
			
			if ($variantsList) {
				foreach ($variantsList as $variant) {
					$parent=$variant->post_parent;
					if (!in_array($parent, $parentVariations)) {
						$parentVariations[]=$parent;
					}
				}
			}
			
			if ($productsList) {
				foreach ($productsList as $singleProduct) {
					$product_id = $singleProduct->ID;
					if (!empty($parentVariations) && in_array($product_id, $parentVariations)) {
						$parentVariationNames[$product_id]=$singleProduct->post_title;
					} else {
						$fullProductList[] = array('ID'=>$product_id, 'name'=>$singleProduct->post_title . ' (pid#' . $product_id . ')');
					}
				}
				// exit;
			}//posts end
			if ($variantsList) {
				foreach ($variantsList as $variant) {
						//$variableProduct=wc_get_product($variant->ID);
						//$attributes=$variableProduct->attributes;
						$fullProductList[]=array('ID'=>$variant->ID, 'name'=>$parentVariationNames[$variant->post_parent] . ' (Variant #' . $variant->ID . ')');
				}
			}
			// sort into alphabetical order, by title
			asort($fullProductList);
			return $fullProductList;
		}


		public function getProductCatSlugPairs() {
			$terms = get_terms('product_cat');
			$productCatList=array();
			
			foreach ($terms as $term) {
				$productCatList[]=array('ID'=> $term->term_id,'name'=>$term->name);
			}
			return $productCatList;
		}


		public function getAllSiteUserIdUserNamePairs() {
			$args = array(
				'blog_id'      => $GLOBALS['blog_id'],
				'orderby'      => 'login',
				'order'        => 'ASC',
				'count_total'  => false,
				'fields'       => array('id', 'display_name'),
			);
			
			$users = get_users($args);
			$usersList = array();
			foreach ($users as $aUser) {
				$usersList[] = array('ID'=>$aUser->id, 'name'=>$aUser->display_name);
			}
			return $usersList;
		}

		public function getAllUserRoles() {
			$allRoles = wp_roles()->roles;
			$roleSlugNamePairs = array();
			foreach ($allRoles as $roleSlug => $RoleAttributes) {
				$roleSlugNamePairs[]= array('ID'=>$roleSlug, 'name'=>$RoleAttributes['name']);
			}
		
			return $roleSlugNamePairs;
		}

		public function getAllUserGroupIdNamePairs() {
			global $wpdb;
			$groupIdNamePairs = array();
			if ($wpdb->get_var('SHOW TABLES LIKE "' . $wpdb->prefix . 'groups_group"')) {
				$groupIdNamePairs  = $wpdb->get_results('SELECT group_id as ID, name FROM ' . $wpdb->prefix . 'groups_group');
			}
			return $groupIdNamePairs;
		}
	}
}
