<?php
/**
 *
 * Handle exchange rates updates from API providers
 *
 * @version  2.4.5
 * @package WCPBC
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WCPBC_Update_Exchange_Rates' ) ) :

	/**
	 * WCPBC_Update_Exchange_Rates Class.
	 */
	class WCPBC_Update_Exchange_Rates {

		/**
		 * Exchange provides.
		 *
		 * @var array
		 */
		private static $exchange_rates_providers = array();

		/**
		 * Init plugin, Hook actions and filters
		 */
		public static function init() {
			add_action( 'woocommerce_scheduled_sales', array( __CLASS__, 'update_exchange_rates' ), 5 );
			add_action( 'update_option_woocommerce_currency', array( __CLASS__, 'update_exchange_rates' ) );
		}

		/**
		 * Get exchange rates providers.
		 *
		 * @return array
		 */
		public static function get_exchange_rates_providers() {

			if ( empty( self::$exchange_rates_providers ) ) {

				$exchange_rates_providers = array();

				$exchange_rates_providers['floatrates']        = include dirname( __FILE__ ) . '/exchage-rates-providers/class-wcpbc-floatrates.php';
				$exchange_rates_providers['openexchangerates'] = include dirname( __FILE__ ) . '/exchage-rates-providers/class-wcpbc-open-exchange-rates.php';
				$exchange_rates_providers['xrates']            = include dirname( __FILE__ ) . '/exchage-rates-providers/class-wcpbc-xrates.php';

				self::$exchange_rates_providers = apply_filters( 'wc_price_based_country_exchange_providers', $exchange_rates_providers );
			}

			return self::$exchange_rates_providers;
		}

		/**
		 * Update exchange rates
		 *
		 * @return void
		 */
		public static function update_exchange_rates() {

			$zones       = WCPBC_Pricing_Zones::get_zones();
			$to_currency = array();

			foreach ( $zones as $zone ) {
				if ( $zone->get_auto_exchange_rate() ) {
					$to_currency[] = $zone->get_currency();
				}
			}

			$rates = self::get_exchange_rate_from_api( array_unique( $to_currency ) );

			if ( $rates ) {

				$base_currency = wcpbc_get_base_currency();

				foreach ( $zones as $zone ) {

					$rate = empty( $rates[ $zone->get_currency() ] ) ? 0 : floatval( $rates[ $zone->get_currency() ] );

					if ( $zone->get_auto_exchange_rate() && ! empty( $rate ) ) {

						if ( $base_currency !== $zone->get_currency() && 1 != $rate ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
							$_rate = $rate * ( 1 + ( $zone->get_exchange_rate_fee() / 100 ) );
							$zone->set_exchange_rate( $_rate );
						} elseif ( $base_currency === $zone->get_currency() ) {
							$_rate = 1 * ( 1 + ( $zone->get_exchange_rate_fee() / 100 ) );
							$zone->set_exchange_rate( $_rate );
						}
					}
				}

				WCPBC_Pricing_Zones::bulk_save( $zones );
			}
		}

		/**
		 * Return a exchange rate
		 *
		 * @param array|string $to_currency Currency code.
		 * @param string       $from_currency Currency code.
		 * @return array|float
		 */
		public static function get_exchange_rate_from_api( $to_currency, $from_currency = '' ) {

			$rates         = false;
			$single        = is_array( $to_currency ) ? false : true;
			$to_currency   = $single ? array( $to_currency ) : $to_currency;
			$from_currency = $from_currency ? $from_currency : get_option( 'woocommerce_currency' );

			$api_providers     = self::get_exchange_rates_providers();
			$exchange_rate_api = get_option( 'wc_price_based_country_exchange_rate_api', 'floatrates' );

			if ( $exchange_rate_api && isset( $api_providers[ $exchange_rate_api ] ) ) {

				$rates        = array();
				$_to_currency = array();

				foreach ( $to_currency as $currency ) {
					if ( $currency === $from_currency ) {
						$rates[ $currency ] = 1;
					} else {
						$_to_currency[] = $currency;
					}
				}

				if ( ! empty( $_to_currency ) ) {
					$rates = array_map( 'floatval', array_merge( $rates, $api_providers[ $exchange_rate_api ]->get_exchange_rates( $from_currency, $_to_currency ) ) );
				}

				if ( $single ) {
					$rates = current( $rates );
				}
			}

			return $rates;
		}
	}
endif;
