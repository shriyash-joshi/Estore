<?php
/**
 * Fixes compatibility issues with WooCommerce Dynamic Pricing & Discounts by RightPress.
 *
 * @see https://codecanyon.net/item/woocommerce-dynamic-pricing-discounts/7119279
 * @since 2.0.3
 * @package WCPBC/Integrations
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WCPBC_Rightpress_Product_Price_Shop' ) ) :

	/**
	 * * WCPBC_GPF Class
	 */
	class WCPBC_Rightpress_Product_Price_Shop {

		/**
		 * Hook actions and filters
		 */
		public static function init() {
			add_filter( 'rightpress_early_hook_priority', array( __CLASS__, 'early_hook_priority' ), 10, 2 );
			add_action( 'wc_price_based_country_frontend_princing_init', array( __CLASS__, 'frontend_princing_init' ) );
		}

		/**
		 * Increment the early hook priority.
		 *
		 * @param int    $priority Hook priority.
		 * @param string $hook Hook name.
		 */
		public static function early_hook_priority( $priority, $hook ) {
			if ( ! is_callable( array( 'RightPress_Help', 'get_php_int_min' ) ) ) {
				return $priority;
			}

			if ( 'woocommerce_variation_prices' === $hook ) {
				$priority = RightPress_Help::get_php_int_min() + 1000;
			} elseif ( in_array( $hook, array( 'woocommerce_product_get_price', 'woocommerce_product_get_sale_price', 'woocommerce_product_get_regular_price', 'woocommerce_product_variation_get_price', 'woocommerce_product_variation_get_sale_price', 'woocommerce_product_variation_get_regular_price' ), true ) ) {
				// After PBoC hooks.
				$priority = 6;
			}

			return $priority;
		}

		/**
		 * Frontend hooks.
		 */
		public static function frontend_princing_init() {
			if ( ! is_callable( array( 'RightPress_Help', 'get_php_int_min' ) ) ) {
				return;
			}
			add_filter( 'woocommerce_variation_prices', array( __CLASS__, 'add_metadata_filter' ), RightPress_Help::get_php_int_min() );
			add_filter( 'woocommerce_variation_prices', array( __CLASS__, 'remove_metadata_filter' ), -1 );
		}

		/**
		 * Add the metadata filter.
		 *
		 * @param array $value Value to return.
		 */
		public static function add_metadata_filter( $value ) {
			add_filter( 'get_post_metadata', array( __CLASS__, 'get_post_metadata' ), 10, 4 );
			return $value;
		}

		/**
		 * Remove the metadata filter.
		 *
		 * @param array $value Value to return.
		 */
		public static function remove_metadata_filter( $value ) {
			remove_filter( 'get_post_metadata', array( __CLASS__, 'get_post_metadata' ), 10, 4 );
			return $value;
		}

		/**
		 * Return a meta data value
		 *
		 * @param null|array|string $meta_value The value get_metadata() should return - a single metadata value or an array of values.
		 * @param int               $object_id Object ID.
		 * @param string            $meta_key Meta key.
		 * @param bool              $single Whether to return only the first value of the specified $meta_key.
		 */
		public static function get_post_metadata( $meta_value, $object_id, $meta_key, $single ) {
			if ( $single && in_array( $meta_key, array( '_price', '_regular_price', '_sale_price' ), true ) && 'product_variation' === get_post_type( $object_id ) ) {

				remove_filter( 'get_post_metadata', array( __CLASS__, 'get_post_metadata' ), 10, 4 );
				$meta_value = wcpbc_the_zone()->get_post_price( $object_id, $meta_key );
				add_filter( 'get_post_metadata', array( __CLASS__, 'get_post_metadata' ), 10, 4 );
			}

			return $meta_value;
		}

		/**
		 * Check min plugin version
		 */
		public static function check_environment() {
			return defined( 'RP_WCDPD_VERSION' ) && version_compare( RP_WCDPD_VERSION, '2.3.7', '>=' );
		}

		/**
		 * Display the environment alert
		 */
		public static function environment_notice() {
			// translators: 1, 2 HTML tags. 3 plugin version.
			$environment_alert = sprintf( __( 'You are using a not supported version of %1$sWooCommerce Dynamic Pricing & Discounts by RightPress%2$s. The minimum compatible version for this plugin is %3$s.', 'woocommerce-product-price-based-on-countries' ), '<strong>', '</strong>', '2.3.7' );
			echo '<div id="message" class="error">' . sprintf( '<p><strong>%1$s</strong></p>%2$s', 'Price Based on Country - ' . esc_html__( 'Heads up!', 'woocommerce-product-price-based-on-countries' ), wp_kses_post( wpautop( $environment_alert ) ) ) . '</div>';
		}
	}

endif;

if ( WCPBC_Rightpress_Product_Price_Shop::check_environment() ) {
	WCPBC_Rightpress_Product_Price_Shop::init();
} else {
	add_action( 'admin_notices', array( 'WCPBC_Rightpress_Product_Price_Shop', 'environment_notice' ) );
}
