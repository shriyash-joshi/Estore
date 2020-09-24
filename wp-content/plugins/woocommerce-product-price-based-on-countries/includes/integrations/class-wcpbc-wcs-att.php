<?php
/**
 * Handle integration with All Products for WooCommerce Subscriptions Developed by SomewhereWarm.
 *
 * @see https://woocommerce.com/products/all-products-for-woocommerce-subscriptions/
 * @since 1.8.15
 * @package WCPBC/Integrations
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WCPBC_WCS_ATT' ) ) :

	/**
	 * * WCPBC_WCS_ATT Class
	 */
	class WCPBC_WCS_ATT {

		/**
		 * Hook actions and filters
		 */
		public static function init() {
			add_action( 'woocommerce_single_product_summary', array( __CLASS__, 'adjust_product_price' ), -100 );
		}

		/**
		 * Set the product price to the pricing zone price for the product in the single page.
		 */
		public static function adjust_product_price() {
			global $product;
			WCPBC_Frontend_Pricing::adjust_product_price( $product );
		}
	}

	add_action( 'wc_price_based_country_frontend_princing_init', array( 'WCPBC_WCS_ATT', 'init' ), 100 );

endif;
