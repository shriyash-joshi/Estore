<?php
/**
 * Handle integration with Payment Gateway Based Fees.
 *
 * @version 2.7.1
 * @package WCPBC
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WCPBC_Additional_Fees' ) ) :

	/**
	 * WCPBC_Additional_Fees Class
	 */
	class WCPBC_Additional_Fees {

		/**
		 * Hook actions and filters
		 */
		public static function init() {
			add_action( 'wc_price_based_country_frontend_princing_init', array( __CLASS__, 'frontend_pricing_init' ) );
		}

		/**
		 * Front-end init.
		 */
		public static function frontend_pricing_init() {
			add_filter( 'wc_add_fees_gateway_fee', array( __CLASS__, 'gateway_fee' ), 10, 2 );
			add_filter( 'wc_add_fees_gateway_fee_fixed', array( __CLASS__, 'fee_by_exchange_rate' ) );
			add_filter( 'wc_add_fees_gateway_fee_minimum', array( __CLASS__, 'fee_by_exchange_rate' ) );
			add_filter( 'wc_add_fees_maximum_cart_order_value', array( __CLASS__, 'fee_by_exchange_rate' ) );
			add_filter( 'wc_add_fees_minimum_cart_order_value', array( __CLASS__, 'fee_by_exchange_rate' ) );
		}

		/**
		 * Return the gateway fee for the pricing zone.
		 *
		 * @param float $fee The gateway fee.
		 * @param array $options The gateway options.
		 */
		public static function gateway_fee( $fee, $options ) {
			if ( 'fixed_value' === $options['addvaluetype'] ) {
				$fee = wcpbc_the_zone()->get_exchange_rate_price( $fee );
			}
			return $fee;
		}

		/**
		 * Return the gateway fee by the exchange rate.
		 *
		 * @param float $fee The gateway fee.
		 */
		public static function fee_by_exchange_rate( $fee ) {
			return wcpbc_the_zone()->get_exchange_rate_price( $fee );
		}

	}

	WCPBC_Additional_Fees::init();

endif;
