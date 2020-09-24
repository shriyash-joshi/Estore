<?php
/**
 * Handle integration with WooCommerce Google Product Feed by Ademti Software Ltd.
 *
 * @see https://woocommerce.com/products/google-product-feed/
 * @since 1.8.15
 * @package WCPBC/Integrations
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WCPBC_GPF' ) ) :

	/**
	 * * WCPBC_GPF Class
	 */
	class WCPBC_GPF {

		/**
		 * Hook actions and filters
		 */
		public static function init() {
			// Bail if no currency forced.
			if ( empty( $_GET['wcpbc-manual-country'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				return;
			}
			add_filter( 'woocommerce_gpf_cache_name', array( __CLASS__, 'granularise_cache_name' ), 10, 1 );
			add_filter( 'woocommerce_gpf_feed_item', array( __CLASS__, 'add_country_arg_to_product_permalinks' ), 10, 2 );
		}

		/**
		 * Add the country to the cache salt.
		 *
		 * @param string $name Cache salt.
		 * @return string
		 */
		public static function granularise_cache_name( $name ) {
			return $name . '_' . wc_clean( $_GET['wcpbc-manual-country'] ); // phpcs:ignore WordPress.Security.NonceVerification
		}

		/**
		 * Add country to the product permalink.
		 *
		 * @param WoocommerceGpfFeedItem $feed_item Feed item.
		 * @param WC_Product             $wc_product Product.
		 * @return mixed
		 */
		public static function add_country_arg_to_product_permalinks( $feed_item, $wc_product ) {

			$feed_item->purchase_link = add_query_arg(
				array(
					'wcpbc-manual-country' => wc_clean( $_GET['wcpbc-manual-country'] ), // phpcs:ignore WordPress.Security.NonceVerification
				),
				$feed_item->purchase_link
			);

			return $feed_item;
		}
	}

	WCPBC_GPF::init();
endif;
