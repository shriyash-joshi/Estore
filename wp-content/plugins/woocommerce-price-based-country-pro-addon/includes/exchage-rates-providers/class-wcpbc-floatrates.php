<?php
/**
 * FloatRates API provider.
 *
 * @see http://www.floatrates.com/
 * @package WCPBC
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WCPBC_FloatRates' ) ) :

	/**
	 * WCPBC_FloatRates class
	 */
	class WCPBC_FloatRates extends WCPBC_Exchange_Rates_Provider {

		/**
		 * Constructor
		 */
		public function __construct() {
			$this->name = 'FloatRates';
		}

		/**
		 * Return API endpoint
		 *
		 * @return string
		 */
		protected function get_api_endpoint() {
			return 'http://www.floatrates.com/daily/' . strtolower( $this->from_currency ) . '.json';
		}

		/**
		 * Return rates array from response
		 *
		 * @param string $data Plain text to parse.
		 * @return array
		 */
		protected function parse_response( $data ) {

			$rates = array();
			$data  = json_decode( $data );

			foreach ( $this->to_currency as $currency ) {
				$currency_prop = strtolower( $currency );

				if ( isset( $data->$currency_prop ) ) {
					$rates[ $currency ] = $data->$currency_prop->rate;
				} else {
					$rates[ $currency ] = 1;
				}
			}

			return $rates;
		}

	}

endif;

return new WCPBC_FloatRates();
