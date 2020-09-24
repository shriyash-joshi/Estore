<?php
/**
 * Exchange rates provider base class.
 *
 * @since   1.0.0
 * @package WCPBC
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WCPBC_Exchange_Rates_Provider Class
 */
abstract class WCPBC_Exchange_Rates_Provider {

	/**
	 * Exchange rates provider name.
	 *
	 * @var $name
	 */
	protected $name = '';

	/**
	 *
	 * From currency.
	 *
	 * @var $from_currency
	 */
	protected $from_currency = false;

	/**
	 * To currency.
	 *
	 * @var $to_currency
	 */
	protected $to_currency = array();

	/**
	 * Return API endpoint.
	 *
	 * @return string
	 */
	abstract protected function get_api_endpoint();

	/**
	 * Parse the response and return an array of rates.
	 *
	 * @param string $response API response.
	 * @return array
	 */
	abstract protected function parse_response( $response );

	/**
	 * Do request.
	 *
	 * @return array
	 */
	protected function do_request() {
		$api_endpoint = esc_url_raw( $this->get_api_endpoint() );
		$cache_key    = 'wcpbc_request_' . md5( $api_endpoint );
		$data         = get_transient( $cache_key );

		if ( false === $data ) {

			$response = wp_safe_remote_get(
				$api_endpoint,
				array(
					'timeout' => 15,
				)
			);

			if ( is_wp_error( $response ) ) {
				$data = $response;
			} elseif ( empty( $response['response']['code'] ) || 200 !== absint( $response['response']['code'] ) ) {
				$data = new WP_Error( 'fail', $response['body'] );
			} else {
				$data = $response['body'];
				set_transient( $cache_key, $data, 2 * HOUR_IN_SECONDS );
			}
		}

		if ( $data && ! is_wp_error( $data ) ) {
			$data = $this->parse_response( $data );
		}

		return $data;
	}

	/**
	 * Return the provider name
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Return the provider options fields.
	 *
	 * @return string
	 */
	public function get_options_fields() {
		return false;
	}

	/**
	 * Validate options fields.
	 */
	public function validate_options_fields() {
	}

	/**
	 * Return exchage rates array.
	 *
	 * @param string $from_currency From currency.
	 * @param array  $to_currency To currencies.
	 * @return array
	 */
	public function get_exchange_rates( $from_currency, $to_currency ) {

		$this->from_currency = $from_currency;
		$this->to_currency   = $to_currency;

		$rates = $this->do_request();

		if ( is_wp_error( $rates ) ) {

			$logger = new WC_Logger();
			// Translators: Exchange rate API Name.
			$logger->add( 'wc_price_based_country', sprintf( __( 'Unable to update exchange rate from API: %s', 'wc-price-based-country-pro' ), $rates->get_error_message() ) );

			return array();
		}

		return $rates;
	}
}
