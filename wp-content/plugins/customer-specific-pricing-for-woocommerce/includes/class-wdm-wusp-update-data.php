<?php

namespace WdmCSP;

if (! class_exists('WdmWuspUpdateDataInDB')) {
	/**
	* Class for updating the entity product pricing details in DB.
	*/
	class WdmWuspUpdateDataInDB {
	
		/**
		* Updates the user product pricing data in DB.
		 *
		* @param int $update_id Id for the pricing pair.
		* @param float $price Price for Product.
		* @param string $price_type Price type % or flat.
		* @param int $quantity Quantity of Product.
		*/
		public static function updateUserPricingInDb( $update_id, $price, $price_type, $quantity, $product_id) {
			global $wpdb;
			$wpusp_product_table = $wpdb->prefix . 'wusp_user_pricing_mapping';
			$price = wc_format_decimal($price);
			$isPriceEmpty = empty(get_post_meta($product_id, '_regular_price', true))? true:false;
			if (!( $isPriceEmpty && 2==$price_type )) {
				if (! empty($update_id) && ! empty($price)) {
					$wpdb->update($wpusp_product_table, array(
							'min_qty'                   => $quantity,
							'price'                     => $price,
							'flat_or_discount_price'    => $price_type,
						), array(
							'id'        => $update_id,
						), array(
						'%d',
						'%s',
						'%d',
						), array(
						'%d'));
				}
				return true;
			}
			return false;
		}

		/**
		* Updates the role product pricing data in DB.
		 *
		* @param int $update_id Id for the pricing pair.
		* @param float $price Price for Product.
		* @param string $price_type Price type % or flat.
		* @param int $quantity Quantity of Product.
		*/

		public static function updateRolePricingInDb( $update_id, $role, $product_id, $price, $price_type, $quantity) {
			global $wpdb;

			$role_product_table = $wpdb->prefix . 'wusp_role_pricing_mapping';
			$price = wc_format_decimal($price);
			$isPriceEmpty = empty(get_post_meta($product_id, '_regular_price', true))? true:false;
			
			if (!( $isPriceEmpty && 2==$price_type )) {
				if (! empty($role) && ! empty($price) && !empty($product_id)) {
					$wpdb->update($role_product_table, array(
						'min_qty'                   => $quantity,
						'price'                     => $price,
						'flat_or_discount_price'    => $price_type,
					), array(
						'id'    => $update_id
					), array(
					'%d',
					'%s',
					'%d',
					), array(
					'%d',
					'%s',
					'%d'));
				}
				return true;
			}
			return false;
		}

		/**
		* Updates the group product pricing data in DB.
		 *
		* @param int $update_id Id for the pricing pair.
		* @param float $price Price for Product.
		* @param string $price_type Price type % or flat.
		* @param int $quantity Quantity of Product.
		*/
		public static function updateGroupPricingInDb( $update_id, $group_id, $product_id, $price, $price_type, $quantity) {
			global $wpdb;

			$group_product_table = $wpdb->prefix . 'wusp_group_product_price_mapping';
			$price = wc_format_decimal($price);
			$isPriceEmpty = empty(get_post_meta($product_id, '_regular_price', true))? true:false;
			if (!( $isPriceEmpty && 2==$price_type )) {
				if (! empty($group_id) && ! empty($price) && !empty($product_id)) {
					$wpdb->update($group_product_table, array(
						'min_qty'                   => $quantity,
						'price'                     => $price,
						'flat_or_discount_price'    => $price_type,
					), array(
						'id'    => $update_id
					), array(
					'%d',
					'%s',
					'%d',
					), array(
					'%d',
					'%d',
					'%d'));
				}
				return true;
			}
			return false;
		}
	}
}
