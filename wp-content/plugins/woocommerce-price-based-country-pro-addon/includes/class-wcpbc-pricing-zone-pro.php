<?php
/**
 * Extends the basic pricing zone
 *
 * @since   2.3.0
 * @version 2.5.0
 * @package WCPBC/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WCPBC_Pricing_Zone_Pro Class
 */
class WCPBC_Pricing_Zone_Pro extends WCPBC_Pricing_Zone {

	/**
	 * Constructor for zones.
	 *
	 * @param array $data Pricing zone attributes as array.
	 */
	public function __construct( $data = null ) {

		$this->data = wp_parse_args(
			$data,
			array(
				'zone_id'                => '',
				'name'                   => '',
				'countries'              => array(),
				'currency'               => get_option( 'woocommerce_currency' ),
				'exchange_rate'          => '1',
				'auto_exchange_rate'     => 'yes',
				'exchange_rate_fee'      => '0',
				'round_nearest'          => '',
				'currency_format'        => '',
				'price_thousand_sep'     => get_option( 'woocommerce_price_thousand_sep' ),
				'price_decimal_sep'      => get_option( 'woocommerce_price_decimal_sep' ),
				'price_num_decimals'     => get_option( 'woocommerce_price_num_decimals' ),
				'disable_tax_adjustment' => 'no',
			)
		);
	}

	/**
	 * Get exchange rate is auto update
	 *
	 * @return boolean
	 */
	public function get_auto_exchange_rate() {
		return $this->get_prop( 'auto_exchange_rate' ) === 'yes';
	}

	/**
	 * Set exchange rate auto update
	 *
	 * @param string $auto Yes or No.
	 */
	public function set_auto_exchange_rate( $auto ) {
		$this->set_prop( 'auto_exchange_rate', ( 'yes' === $auto ? 'yes' : 'no' ) );
	}

	/**
	 * Get exchange rate fee.
	 *
	 * @return float
	 */
	public function get_exchange_rate_fee() {
		$exchange_rate_fee = $this->get_prop( 'exchange_rate_fee' );
		return ( $this->get_auto_exchange_rate() && ! empty( $exchange_rate_fee ) ? floatval( $exchange_rate_fee ) : 0 );
	}

	/**
	 * Set the exchange rate fee.
	 *
	 * @param int $fee Exchange rate fee.
	 */
	public function set_exchange_rate_fee( $fee ) {
		$this->set_prop( 'exchange_rate_fee', floatval( $fee ) );
	}

	/**
	 * Get round nearest
	 *
	 * @return float
	 */
	public function get_round_nearest() {
		return floatval( $this->get_prop( 'round_nearest' ) );
	}

	/**
	 * Set round nearest
	 *
	 * @param string $round_nearest Round to nearest value.
	 */
	public function set_round_nearest( $round_nearest ) {
		$this->set_prop( 'round_nearest', wc_format_decimal( $round_nearest ) );
	}

	/**
	 * Get the currency format.
	 *
	 * @return string
	 */
	public function get_currency_format() {
		return $this->get_prop( 'currency_format' );
	}

	/**
	 * Set the currency format.
	 *
	 * @param string $currency_format Currency format.
	 */
	public function set_currency_format( $currency_format ) {
		return $this->set_prop( 'currency_format', $currency_format );
	}

	/**
	 * Get the price thousand separator.
	 *
	 * @return string
	 */
	public function get_price_thousand_sep() {
		return $this->get_prop( 'price_thousand_sep' );
	}

	/**
	 * Set the price thousand separator.
	 *
	 * @param string $sep Price thousand separator.
	 */
	public function set_price_thousand_sep( $sep ) {
		$this->set_prop( 'price_thousand_sep', wp_kses_post( $sep ) );
	}

	/**
	 * Get the price decimal separator.
	 *
	 * @return string
	 */
	public function get_price_decimal_sep() {
		return $this->get_prop( 'price_decimal_sep' );
	}

	/**
	 * Set the price decimal separator.
	 *
	 * @param string $sep Price decimal separator.
	 */
	public function set_price_decimal_sep( $sep ) {
		$this->set_prop( 'price_decimal_sep', wp_kses_post( $sep ) );
	}

	/**
	 * Get the price num decimals.
	 *
	 * @return string
	 */
	public function get_price_num_decimals() {
		return $this->get_prop( 'price_num_decimals' );
	}

	/**
	 * Set the price num decimals.
	 *
	 * @param string $num Num decimals.
	 */
	public function set_price_num_decimals( $num ) {
		$num = wcpbc_empty_nozero( $num ) ? '' : absint( $num );
		$this->set_prop( 'price_num_decimals', $num );
	}

	/**
	 * Round to nearest a value
	 *
	 * @param float $val The value to round.
	 * @return float
	 */
	public function round_to_nearest( $val ) {
		return ceil( $val / $this->get_round_nearest() ) * $this->get_round_nearest();
	}

	/**
	 * Round a price
	 *
	 * @param float  $price Amount to round.
	 * @param float  $num_decimals Number of decimals.
	 * @param string $context What the value is for?. Default "generic".
	 * @param mixed  $data Source of the price.
	 * @return float
	 */
	protected function round( $price, $num_decimals = '', $context = 'generic', $data = null ) {
		$value = parent::round( $price, $num_decimals );

		if ( $value && $this->get_round_nearest() && apply_filters( 'wc_price_based_country_round_to_nearest_' . $context, true ) ) {
			$value = $this->round_to_nearest( $value );
		}

		return apply_filters( 'wc_price_based_country_round_price', $value, $price, $num_decimals, $this, $context, $data );
	}

	/**
	 * Get AffWP exchange rate.
	 *
	 * @return float
	 */
	public function get_affwp_exchange_rate() {
		return $this->get_prop( 'affwp_exchange_rate' ) ? $this->get_prop( 'affwp_exchange_rate' ) : $this->get_exchange_rate();
	}

	/**
	 * German Market Integration - Product price per unit by exchange rate?
	 *
	 * @param int $post_id Post ID.
	 * @return bool
	 */
	public function is_exchange_rate_price_per_unit( $post_id ) {
		return false;
	}
}
