<?php
/**
 * X-Rates API provider.
 *
 * @see https://www.x-rates.com/
 * @since 2.3.0
 * @package WCPBC
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WCPBC_XRates' ) ) :

	/**
	 * WCPBC_XRates class
	 */
	class WCPBC_XRates extends WCPBC_Exchange_Rates_Provider {

		/**
		 * Constructor
		 */
		public function __construct() {
			$this->name = 'X-Rates';
		}

		/**
		 * Return API endpoint
		 *
		 * @return string
		 */
		protected function get_api_endpoint() {
			return 'https://www.x-rates.com/table/?from=' . strtoupper( $this->from_currency ) . '&amount=1';
		}

		/**
		 * Parse the response and return an array of rates.
		 *
		 * @param string $response API response.
		 * @return array
		 */
		protected function parse_response( $response ) {
			$rates    = array();
			$fpattern = "/<a href='http:\/\/www.x-rates.com\/graph\/\?from=%s&amp;to=%s'>(.+)<\/a>/";
			foreach ( $this->to_currency as $to_currency ) {
				$pattern = sprintf( $fpattern, $this->from_currency, $to_currency );
				if ( preg_match( $pattern, $response, $matches ) && count( $matches ) > 0 ) {
					$rates[ $to_currency ] = floatval( $matches[1] );
				}
			}
			if ( empty( $rates ) ) {
				$rates = new WP_Error( 'fail', 'Empty exchange rates from X-Rates.' );
			}
			return $rates;
		}
	}

endif;

return new WCPBC_XRates();
