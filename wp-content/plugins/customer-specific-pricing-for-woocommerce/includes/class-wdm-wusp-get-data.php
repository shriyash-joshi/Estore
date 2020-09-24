<?php

namespace WdmCSP;

if (! class_exists('WdmWuspGetData')) {
	class WdmWuspGetData {
	
		private static $userGroup = array();

		/**
		 * Retireves all prices for a product from Database.
		 *
		 * @global object $wpdb
		 * @param int $product_id Product Id
		 * @return array returns array of user=>price combination
		 */
		public static function getAllPricesForSingleProduct( $product_id) {
			global $wpdb;
			$product_id          = apply_filters('get_base_product_id_for_translated_product', $product_id);
			$user_product_result = $wpdb->get_results($wpdb->prepare('SELECT user_id, price, min_qty, flat_or_discount_price as price_type FROM ' . $wpdb->prefix . 'wusp_user_pricing_mapping WHERE product_id=%d ORDER BY `id` ASC', $product_id));
			if ($user_product_result) {
				return ( $user_product_result );
			}
		}

		/**
		 * Retireves all prices for a product from Database.
		 *
		 * @global object $wpdb
		 * @param type $product_id
		 * @return array returns array of group=>price combination
		 */
		public static function getAllGroupPricesForSingleProduct( $product_id) {
			global $wpdb;
			$product_id           = apply_filters('get_base_product_id_for_translated_product', $product_id);
			$group_product_result = $wpdb->get_results($wpdb->prepare('SELECT group_id, price, min_qty, flat_or_discount_price as price_type FROM ' . $wpdb->prefix . 'wusp_group_product_price_mapping WHERE product_id=%d ORDER BY `id` ASC', $product_id));

			if ($group_product_result) {
				return ( $group_product_result );
			}
		}

		/**
		 * Finds out price for specific user for specific product
		 *
		 * @global object $wpdb database object
		 * @param int $user_id User Id
		 * @param int $product_id variation id if variable product else
		 * product id.
		 * @return mixed if price is found, price is returned. Otherwise it returns false
		 */
		public static function getPriceOfProductForUser( $user_id, $product_id) {
			global $wpdb;

			static $userPrices = array();
			if (isset($userPrices[$user_id][$product_id])) {
				return $userPrices[$user_id][$product_id];
			}

			$get_price = $wpdb->get_results($wpdb->prepare('SELECT price, min_qty, flat_or_discount_price as price_type FROM ' . $wpdb->prefix . 'wusp_user_pricing_mapping WHERE user_id=%d AND product_id=%d ORDER BY min_qty', $user_id, $product_id));
			
			$get_price = apply_filters('wdm_csp_filter_usp_rules_for_a_product', $get_price, $user_id, $product_id);
			
			$userPrices[$user_id][$product_id] = $get_price;
			return $userPrices[$user_id][$product_id];
		}

		/**
		 * Finds regular price for product
		 *
		 * @global object $wpdb
		 * @param int $product_id
		 * @return mixed if price is found, price is returned. Otherwise it returns false
		 */
		public static function getRegularPriceOfTheProduct( $product_id) {
			global $wpdb;
			$key = '_price';

			$price = $wpdb->get_var($wpdb->prepare('SELECT meta_value FROM ' . $wpdb->prefix . 'postmeta WHERE post_id=%d AND meta_key=%s', $product_id, $key));

			if ($price) {
				return $price;
			} else {
				return false;
			}
		}

		/**
		 * Finds out regular price for specific product
		 *
		 * @global object $wpdb
		 * @param int $product_id
		 * @return mixed if price is found,price is returned. Otherwise it returns false
		 */
		public static function getRegularPriceOfTheVariationProduct( $product_id) {
			global $wpdb;
			$key = '_price';

			$price = $wpdb->get_var($wpdb->prepare('SELECT meta_value FROM ' . $wpdb->prefix . 'postmeta WHERE post_id=%d AND meta_key=%s', $product_id, $key));

			if ($price) {
				return $price;
			} else {
				return false;
			}
		}

		/**
		* Gets the groups in which the user is there.
		* Gets the price to be applied for the group specific on the product * based on min quantity.
		 *
		* @param int $user_id User Id.
		* @param int $product_id Product Id.
		* @param array $groupIds Group Ids in which user is present.
		* @return array quantity=>price group specific pairs for the product.
		*/
		public static function getQtyPricePairsOfProductForGroup( $user_id, $product_id, $groupIds = array()) {
			global $wpdb, $cspFunctions;

			static $pricePairs = array();
			if (isset($pricePairs[$user_id][$product_id])) {
				return $pricePairs[$user_id][$product_id];
			}

			if (isset(self::$userGroup[$user_id])) {
				$user_groupid = self::$userGroup[$user_id];
			} else {
				$user_groupid              = $wpdb->get_results($wpdb->prepare('SELECT group_id FROM ' . $wpdb->prefix . 'groups_user_group WHERE user_id=%d', $user_id));
				self::$userGroup[$user_id] = $user_groupid;
			}

			if (!empty($groupIds)) {
				$user_groupid = $wpdb->get_results($wpdb->prepare('SELECT group_id FROM ' . $wpdb->prefix . 'groups_user_group WHERE group_id IN (' . implode(', ', array_fill(0, count($groupIds), '%d')) . ')', $groupIds));
			}


			if ($user_groupid) {
				$regular_price = $cspFunctions->wdmGetCurrentPrice($product_id);
				$group_price   = array();

				//TODO : External loop can be removed
				foreach ($user_groupid as $single_user_groupid) {
					//Here a variable $price indicates group specific pricing rules, TODO:Change the variable name for the better readability
					$price = $wpdb->get_results($wpdb->prepare('SELECT price, min_qty, product_id, flat_or_discount_price as price_type FROM ' . $wpdb->prefix . 'wusp_group_product_price_mapping WHERE group_id=%d AND product_id=%d ORDER BY min_qty', $single_user_groupid->group_id, $product_id));
					$price = apply_filters('wdm_csp_filter_gsp_rules_for_a_product', $price, $single_user_groupid->group_id, $product_id);
					if (null == $price) {
						continue;
					}

					for ($i = 0; $i < count($price); $i++) {
						$current_min_qty = $price[$i]->min_qty;
						
						if (isset($group_price[$current_min_qty])) {
							$new_price = $price[$i]->price;

							if (2 == $price[$i]->price_type) {
								$new_price = ( $regular_price ) - ( ( $new_price * $regular_price ) / 100 );
							}

							$old_price = $group_price[$current_min_qty]->price;
							
							if ( 2 == $group_price[$current_min_qty]->price_type) {
								$old_price = ( $regular_price ) - ( ( $old_price * $regular_price ) / 100 );
							}

							if ($new_price < $old_price) {
								$group_price[$current_min_qty] = $price[$i];
							}
						} else {
							$group_price[$current_min_qty] = $price[$i];
						}
					}
				}

				ksort($group_price);

				$pricePairs[$user_id][$product_id] = $group_price;
				return $pricePairs[$user_id][$product_id];
			}

			$pricePairs[$user_id][$product_id] = false;
			return $pricePairs[$user_id][$product_id];
		}

		/**
		 * Finds out price for specific group for specific product
		 *
		 * @global object $wpdb
		 * @param int $user_id
		 * @param int $product_id
		 * @return mixed if price is found, minimum price is returned. Otherwise it returns false
		 */
		public static function getPriceOfProductForGroup( $user_id, $product_id) {
			global $wpdb;

			static $prices = array();

			if (isset($prices[$user_id][$product_id])) {
				return $prices[$user_id][$product_id];
			}

			
			if (isset(self::$userGroup[$user_id])) {
				$user_groupid = self::$userGroup[$user_id];
			} else {
				$user_groupid              = $wpdb->get_results($wpdb->prepare('SELECT group_id FROM ' . $wpdb->prefix . 'groups_user_group WHERE user_id=%d', $user_id));
				self::$userGroup[$user_id] = $user_groupid;
			}

			if ($user_groupid) {
				$group_price = array();
				foreach ($user_groupid as $single_user_groupid) {
					$price = $wpdb->get_var($wpdb->prepare('SELECT price FROM ' . $wpdb->prefix . 'wusp_group_product_price_mapping WHERE group_id=%d AND product_id=%d', $single_user_groupid->group_id, $product_id));

					$priceType = $wpdb->get_var($wpdb->prepare('SELECT flat_or_discount_price as price_type FROM ' . $wpdb->prefix . 'wusp_group_product_price_mapping WHERE group_id=%d AND product_id=%d', $single_user_groupid->group_id, $product_id));

					if ( 2==$priceType) {
						$regularPrice = get_post_meta($product_id, '_regular_price', true);
						$regularPrice = apply_filters('wdm_csp_regular_price', $regularPrice, $product_id);
						
						if ($regularPrice >= 0) {
							$discount = floatval(( $price/100 ) * $regularPrice);
							$price    = $regularPrice - $discount;
						} else {
							$prices[$user_id][$product_id] = 0;
							return $prices[$user_id][$product_id];
						}
					}

					if (isset($price) && $price) {
						array_push($group_price, $price);
					}
				}

				if (isset($group_price[ 0 ])) {
					$prices[$user_id][$product_id] = min($group_price);
				} else {
					$prices[$user_id][$product_id] = false;
				}
				return $prices[$user_id][$product_id];
			}
			$prices[$user_id][$product_id] = false;
			return $prices[$user_id][$product_id];
		}

		// private static function getPercentPrice($product_id, $set_price)
		// {
		//     $regular_price = floatval(get_post_meta($product_id, '_regular_price', true));
		//     if ($regular_price > 0) {
		//         $discount = $regular_price - ($regular_price * ($set_price / 100));
		//         return $discount;
		//     } else {
		//         return 0;
		//     }
		// }
	}
}
