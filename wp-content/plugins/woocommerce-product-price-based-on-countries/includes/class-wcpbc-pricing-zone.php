<?php
/**
 * Represents a single pricing zone
 *
 * @since   1.7.0
 * @version 1.7.13
 * @package WCPBC
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WCPBC_Pricing_Zone
 */
class WCPBC_Pricing_Zone {

	/**
	 * Zone data.
	 *
	 * @var array
	 */
	protected $data = array();

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
				'auto_exchange_rate'     => 'no',
				'disable_tax_adjustment' => 'no',
			)
		);
	}

	/**
	 * Get zone data.
	 *
	 * @return array
	 */
	public function get_data() {
		return $this->data;
	}

	/**
	 * Gets a prop for a getter method.
	 *
	 * @since 1.7.9
	 * @param  string $prop Name of prop to get.
	 * @return mixed
	 */
	protected function get_prop( $prop ) {
		return isset( $this->data[ $prop ] ) ? $this->data[ $prop ] : false;
	}

	/**
	 * Sets a prop for a setter method.
	 *
	 * @since 1.8.0
	 * @param string $prop Name of prop to set.
	 * @param mixed  $value Value to set.
	 */
	protected function set_prop( $prop, $value ) {
		if ( isset( $this->data[ $prop ] ) ) {
			$this->data[ $prop ] = $value;
		}
	}

	/**
	 * Set zone id.
	 *
	 * @param string $id Zone ID.
	 */
	public function set_id( $id ) {
		$this->set_prop( 'zone_id', $id );
	}

	/**
	 * Get zone id.
	 *
	 * @return string
	 */
	public function get_id() {
		return $this->get_prop( 'zone_id' );
	}

	/**
	 * Set zone id.
	 *
	 * @param string $id Zone ID.
	 */
	public function set_zone_id( $id ) {
		$this->set_id( $id );
	}

	/**
	 * Get zone id.
	 *
	 * @return string
	 */
	public function get_zone_id() {
		return $this->get_id();
	}

	/**
	 * Get zone name.
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->get_prop( 'name' );
	}

	/**
	 * Set the zone name.
	 *
	 * @param string $name Zone name.
	 */
	public function set_name( $name ) {
		$this->set_prop( 'name', $name );
	}

	/**
	 * Get countries.
	 *
	 * @return array
	 */
	public function get_countries() {
		return $this->get_prop( 'countries' );
	}

	/**
	 * Set countries of the zone.
	 *
	 * @param array $countries Countries.
	 */
	public function set_countries( $countries ) {
		if ( is_array( $countries ) ) {
			$this->set_prop( 'countries', $countries );
		}
	}

	/**
	 * Get zone currency.
	 *
	 * @return string
	 */
	public function get_currency() {
		return $this->get_prop( 'currency' );
	}

	/**
	 * Set the zone currency.
	 *
	 * @param string $currency Zone currency.
	 */
	public function set_currency( $currency ) {
		$this->set_prop( 'currency', $currency );
	}

	/**
	 * Get exchange rate.
	 *
	 * @return float
	 */
	public function get_exchange_rate() {
		return floatval( $this->get_prop( 'exchange_rate' ) );
	}

	/**
	 * Get exchange rate.
	 *
	 * @since 1.9.0
	 * @return float
	 */
	public function get_real_exchange_rate() {
		return $this->get_currency() === wcpbc_get_base_currency() ? 1 : floatval( $this->get_exchange_rate() );
	}

	/**
	 * Set the zone exchange rate.
	 *
	 * @param float $exchange_rate Zone exchange_rate.
	 */
	public function set_exchange_rate( $exchange_rate ) {
		$this->set_prop( 'exchange_rate', wc_format_decimal( $exchange_rate ) );
	}

	/**
	 * Get disable tax adjustment.
	 *
	 * @return bool
	 */
	public function get_disable_tax_adjustment() {
		return 'yes' === $this->get_prop( 'disable_tax_adjustment' ) && wc_prices_include_tax();
	}

	/**
	 * Set disable tax adjustment.
	 *
	 * @param string $disable Yes or No.
	 */
	public function set_disable_tax_adjustment( $disable ) {
		return $this->set_prop( 'disable_tax_adjustment', ( 'yes' === $disable ? 'yes' : 'no' ) );
	}

	/**
	 * Get a meta key based on zone ID
	 *
	 * @param string $meta_key Metadata key.
	 * @return string
	 */
	public function get_postmetakey( $meta_key = '' ) {
		return esc_attr( '_' . $this->get_zone_id() . $meta_key );
	}

	/**
	 * Get a meta value based on zone ID
	 *
	 * @param int    $post_id Post ID.
	 * @param string $meta_key Metadata key.
	 * @param bool   $deprecated Optional. If true, returns only the first value for the specified meta key.
	 * @return mixed
	 */
	public function get_postmeta( $post_id, $meta_key, $deprecated = true ) {
		if ( ! $deprecated ) {
			wc_deprecated_argument( 'single', '2.0.5', '' );
		}
		return get_post_meta( $post_id, $this->get_postmetakey( $meta_key ), true );
	}

	/**
	 * Add meta data field to a post
	 *
	 * @param int    $post_id Post ID.
	 * @param string $meta_key Metadata key.
	 * @param string $meta_value Metadata value.
	 * @return int|bool
	 */
	public function add_postmeta( $post_id, $meta_key, $meta_value ) {
		return add_post_meta( $post_id, $this->get_postmetakey( $meta_key ), $meta_value, false );
	}

	/**
	 * Update meta value based on zone ID
	 *
	 * @param int    $post_id Post ID.
	 * @param string $meta_key Metadata key.
	 * @param string $meta_value Metadata value.
	 * @param bool   $force Force the meta data update. Prevents issues with object cache plugins.
	 * @return int|bool
	 */
	public function set_postmeta( $post_id, $meta_key, $meta_value, $force = false ) {
		if ( $force && wp_using_ext_object_cache() ) {
			wp_cache_delete( $post_id, 'post_meta' );
		}
		return update_post_meta( $post_id, $this->get_postmetakey( $meta_key ), $meta_value );
	}

	/**
	 * Remove metadata from a post
	 *
	 * @param int    $post_id Post ID.
	 * @param string $meta_key Metadata key.
	 * @return bool True on success, false on failure.
	 */
	public function delete_postmeta( $post_id, $meta_key ) {
		return delete_post_meta( $post_id, $this->get_postmetakey( $meta_key ) );
	}

	/**
	 * Product price by exchange rate?
	 *
	 * @param WC_Data $data Object instance.
	 * @return bool
	 */
	public function is_exchange_rate_price( $data ) {
		$post_id      = false;
		$product_type = false;

		if ( is_numeric( $data ) ) {
			$post_id = $data;
		} elseif ( is_object( $data ) && is_callable( array( $data, 'get_id' ) ) ) {
			$post_id      = $data->get_id();
			$product_type = is_callable( array( $data, 'get_type' ) ) && is_a( 'WC_Product', $data ) ? $data->get_type() : false;
		}
		$price_method = $this->get_postmeta( $post_id, '_price_method' );

		return wcpbc_is_exchange_rate( $price_method ) && ! in_array( $product_type, WCPBC_Product_Sync::get_parent_product_types(), true );
	}

	/**
	 * Set product price by exchange
	 *
	 * @since 1.7.9
	 * @param int  $post_id Post ID.
	 * @param bool $by_exchange_rate TRUE: exchange rate price. FALSE: manual price.
	 */
	public function set_exchange_rate_price( $post_id, $by_exchange_rate = true ) {
		$value = $by_exchange_rate ? 'exchange_rate' : 'manual';
		$this->set_postmeta( $post_id, '_price_method', $value );
	}

	/**
	 * Set product price manual
	 *
	 * @since 1.7.9
	 * @param int $post_id Post ID.
	 */
	public function set_manual_price( $post_id ) {
		$this->set_exchange_rate_price( $post_id, false );
	}

	/**
	 * Return product price calculate by exchange rate
	 *
	 * @param int    $post_id Post ID.
	 * @param string $meta_key Metadata key.
	 * @param bool   $deprecated Must be the price round?.
	 * @return float
	 */
	public function get_exchange_rate_price_by_post( $post_id, $meta_key, $deprecated = false ) {
		if ( $deprecated ) {
			wc_deprecated_argument( 'round', '2.0.0', 'Use WCPBC_Pricing_Zone::get_post_price method instead' );
		}
		$base_price = get_post_meta( $post_id, $meta_key, true );
		return $this->get_exchange_rate_price( $base_price, false );
	}

	/**
	 * Return a price calculate by exchange rate
	 *
	 * @param float  $price The base price to convert.
	 * @param bool   $round Must be the price round?.
	 * @param string $context What the value is for?. Default "generic".
	 * @param mixed  $data Source of the price.
	 * @return float
	 */
	public function get_exchange_rate_price( $price, $round = true, $context = 'generic', $data = null ) {
		if ( empty( $price ) ) {
			$value = $price;
		} else {
			$value = $this->by_exchange_rate( $price );
			if ( $round ) {
				$value = $this->round( $value, '', $context, $data );
			} else {
				// Round to 8 decimals.
				$value = round( $value, 8 );
			}
		}

		return $value;
	}

	/**
	 * Apply the exchange rate to an amount
	 *
	 * @since 1.7.9
	 * @param float $amount Amount to apply the exchange rate.
	 * @return float
	 */
	protected function by_exchange_rate( $amount ) {
		return floatval( $amount ) * $this->get_exchange_rate();
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
		if ( wcpbc_empty_nozero( $num_decimals ) ) {
			$num_decimals = wc_get_price_decimals();
		}

		$value = $price;

		if ( ! empty( $value ) ) {
			$value = round( $value, $num_decimals );
		}
		return $value;
	}

	/**
	 * Get a price metada from a post ID.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $meta_key Metadata key.
	 * @param string $context What the value is for?. Default "product".
	 * @return mixed
	 */
	public function get_post_price( $post_id, $meta_key, $context = 'product' ) {
		$zone_price = $this->get_postmeta( $post_id, $meta_key );

		if ( $this->is_exchange_rate_price( $post_id ) ) {

			$_price = strval( $this->get_exchange_rate_price_by_post( $post_id, $meta_key ) );

			if ( $_price !== $zone_price ) {
				$zone_price = $_price;
				$this->set_postmeta( $post_id, $meta_key, $_price, true );
			}
			$zone_price = $this->round( $zone_price, '', $context, $post_id );
		}

		return $zone_price;
	}

	/**
	 * Get a price property.
	 *
	 * @since 1.9
	 *
	 * @param WC_Data $data Object instance.
	 * @param mixed   $value Original value of the propery.
	 * @param string  $meta_key Metadata key.
	 * @param string  $context What the value is for?. Default "product".
	 * @return mixed
	 */
	public function get_price_prop( $data, $value, $meta_key, $context = 'product' ) {
		if ( ! ( is_object( $data ) && is_callable( array( $data, 'get_id' ) ) ) ) {
			return $value;
		}

		$price = $this->get_postmeta( $data->get_id(), $meta_key );

		if ( $this->is_exchange_rate_price( $data ) ) {

			$_price = $this->get_exchange_rate_price( $value, false );

			if ( floatval( $price ) !== floatval( $_price ) ) {
				$price = $_price;
				$this->set_postmeta( $data->get_id(), $meta_key, strval( $price ), true );
			}

			$price = $this->round( $price, '', $context, $data );
		}

		return $price;
	}

	/**
	 * Return a date property.
	 *
	 * @since 1.9
	 *
	 * @param WC_Data $data Object instance.
	 * @param mixed   $value Original value of the propery.
	 * @param string  $meta_key Metadata key.
	 * @return WC_DateTime
	 */
	public function get_date_prop( $data, $value, $meta_key ) {
		if ( ! ( is_object( $data ) && is_callable( array( $data, 'get_id' ) ) ) ) {
			return $value;
		}

		if ( 'manual' === $this->get_postmeta( $data->get_id(), '_sale_price_dates' ) && ! $this->is_exchange_rate_price( $data ) ) {
			try {

				$metadata = $this->get_postmeta( $data->get_id(), $meta_key );

				if ( is_numeric( $metadata ) ) {
					$datetime = new WC_DateTime( "@{$metadata}", new DateTimeZone( 'UTC' ) );

					// Set local timezone or offset.
					if ( get_option( 'timezone_string' ) ) {
						$datetime->setTimezone( new DateTimeZone( wc_timezone_string() ) );
					} else {
						$datetime->set_utc_offset( wc_timezone_offset() );
					}

					$value = $datetime;

				} else {
					$value = '';
				}
			} catch ( Exception $e ) {} // @codingStandardsIgnoreLine.
		}

		return $value;
	}

	/**
	 * Return an amount in the shop base currency
	 *
	 * @since 1.7.4
	 * @version 1.9.0 Use real exchange rate to calculate the amont.
	 *
	 * @param float $amount Amount to convert to base currency.
	 * @return float
	 */
	public function get_base_currency_amount( $amount ) {
		$amount = floatval( $amount );
		return ( $amount / $this->get_real_exchange_rate() );
	}

	/**
	 * Helper function that return the value of a $_POST variable.
	 *
	 * @since 1.8.0
	 * @param string $key POST parameter name.
	 * @param int    $index If the POST value is a array, the index array to return.
	 * @return mixed
	 */
	public function get_input_var( $key, $index = false ) {
		$metakey = $this->get_postmetakey( $key );
		$value   = null;

		// phpcs:disable WordPress.Security.NonceVerification
		if ( false !== $index && isset( $_POST[ $metakey ][ $index ] ) ) {
			$value = wc_clean( wp_unslash( $_POST[ $metakey ][ $index ] ) );
		} elseif ( isset( $_POST[ $metakey ] ) ) {
			$value = wc_clean( wp_unslash( $_POST[ $metakey ] ) );
		}
		// phpcs:enable
		return $value;
	}
}
