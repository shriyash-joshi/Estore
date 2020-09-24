<?php
/**
 * Handle integration with WooCommerce Composite Products by SomewhereWarm.
 *
 * @version 2.5.0
 * @package WCPBC
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WCPBC_Composite' ) ) :

	/**
	 * WCPBC_Composite Class
	 */
	class WCPBC_Composite {

		/**
		 * Hook actions and filters
		 *
		 * @since 1.0
		 */
		public static function init() {
			add_action( 'woocommerce_process_product_meta_composite', array( __CLASS__, 'process_product_meta' ), 10 );
			add_filter( 'wc_price_based_country_price_meta_keys', array( __CLASS__, 'composite_price_meta_keys' ) );

		}

		/**
		 * Save product metadata
		 *
		 * @param int $post_id Post ID.
		 */
		public static function process_product_meta( $post_id ) {
			WCPBC_Admin_Meta_Boxes::process_product_meta( $post_id );
			foreach ( WCPBC_Pricing_Zones::get_zones() as $zone ) {
				$regular_price = $zone->get_postmeta( $post_id, '_regular_price' );
				$sale_price    = $zone->get_postmeta( $post_id, '_sale_price' );
				$price         = $zone->get_postmeta( $post_id, '_price' );

				$zone->set_postmeta( $post_id, '_bto_base_regular_price', $regular_price );
				$zone->set_postmeta( $post_id, '_bto_base_sale_price', $sale_price );
				$zone->set_postmeta( $post_id, '_bto_base_price', $price );

			}
		}

		/**
		 * Add product bundles pricing meta keys
		 *
		 * @param array $meta_keys Pricing metadata keys.
		 * @return array
		 */
		public static function composite_price_meta_keys( $meta_keys ) {
			array_push( $meta_keys, '_bto_base_regular_price', '_bto_base_sale_price', '_bto_base_price' );
			return $meta_keys;
		}

	}

	WCPBC_Composite::init();

endif;
