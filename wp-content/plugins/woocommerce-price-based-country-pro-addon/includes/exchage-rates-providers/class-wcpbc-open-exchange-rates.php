<?php
/**
 * Open exchange rates API provider.
 *
 * @see https://openexchangerates.org
 * @since   2.7.2
 * @package WCPBC
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WCPBC_Open_Exchange_Rates' ) ) :

	/**
	 * WCPBC_Open_Exchange_Rates class
	 */
	class WCPBC_Open_Exchange_Rates extends WCPBC_Exchange_Rates_Provider {

		/**
		 * Constructor
		 */
		public function __construct() {
			$this->name = 'Open Exchange Rates';
		}

		/**
		 * Return the provider options fields.
		 *
		 * @return string
		 */
		public function get_options_fields() {
			return array(
				array(
					'title'    => __( 'App ID', 'wc-price-based-country-pro' ),
					'desc_tip' => __( 'Open exchange rate App ID.', 'wc-price-based-country-pro' ),
					// translators: HTML tags.
					'desc'     => sprintf( __( 'You can find the App ID in your %1$sOpen Exchange Rates dashboard%3$s. If you have not an account, you can open a free Open Exchange Rates account %2$shere%3$s.', 'wc-price-based-country-pro' ), '<a href="https://openexchangerates.org/account">', '<a href="https://openexchangerates.org/signup/free">', '</a>' ),
					'id'       => 'wc_price_based_country_openexchange_rate_app_id',
					'default'  => '',
					'type'     => 'text',
				),
			);
		}

		/**
		 * Validate options fields.
		 */
		public function validate_options_fields() {
			if ( empty( $_POST['wc_price_based_country_openexchange_rate_app_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				WC_Admin_Settings::add_error( __( 'The Open Exchange Rates App ID is required.', 'wc-price-based-country-pro' ) );
			}
		}

		/**
		 * Return API endpoint
		 *
		 * @return string
		 */
		protected function get_api_endpoint() {
			$app_id = get_option( 'wc_price_based_country_openexchange_rate_app_id', '' );

			return 'https://openexchangerates.org/api/latest.json?base=USD&app_id=' . $app_id;
		}

		/**
		 * Parse the response and return an array of rates.
		 *
		 * @param string $response API response.
		 * @return array
		 */
		protected function parse_response( $response ) {
			$rates     = array();
			$data      = json_decode( $response, true );
			$base_rate = 1;

			if ( isset( $data['rates'] ) ) {
				$data['rates'] = array_map( 'floatval', $data['rates'] );
			}

			if ( ! empty( $data['rates'][ $this->from_currency ] ) && $this->from_currency !== $data['base'] ) {
				$base_rate = $data['rates'][ $this->from_currency ];
			}

			foreach ( $this->to_currency as $currency ) {
				if ( ! empty( $data['rates'][ $currency ] ) ) {
					$rates[ $currency ] = $data['rates'][ $currency ] / $base_rate;
				}
			}

			return $rates;
		}
	}

endif;

return new WCPBC_Open_Exchange_Rates();
