<?php
/**
 * Front-end currency.
 *
 * @since   2.0.0
 * @version 2.5.2
 * @package WCPBC/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 *
 * WCPBC_Frontend_Currency class
 */
class WCPBC_Frontend_Currency {

	/**
	 * Array of zone data
	 *
	 * @var array
	 */
	private static $zone_data = false;

	/**
	 * Init plugin, Hook actions and filters
	 *
	 * @since 1.0
	 */
	public static function init() {
		add_filter( 'woocommerce_email_order_items_args', array( __CLASS__, 'email_order_zone_data' ) );
		add_filter( 'woocommerce_price_format', array( __CLASS__, 'get_price_format' ), 10, 2 );
		add_filter( 'wc_price_args', array( __CLASS__, 'wc_price_args' ), 5, 2 );
		add_filter( 'wc_price', array( __CLASS__, 'wc_price' ), 10, 3 );
		add_filter( 'option_woocommerce_price_thousand_sep', array( __CLASS__, 'price_thousand_sep' ), 100 );
		add_filter( 'option_woocommerce_price_decimal_sep', array( __CLASS__, 'price_decimal_sep' ), 100 );
		add_filter( 'option_woocommerce_price_num_decimals', array( __CLASS__, 'price_num_decimals' ), 100 );
		add_filter( 'woocommerce_currency_symbol', array( __CLASS__, 'alt_currency_symbol' ), 100, 2 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ), 0 );

		// Country widget.
		add_filter( 'wc_price_based_country_widget_settings', array( __CLASS__, 'country_widget_settings' ) );
		add_filter( 'wc_price_based_country_shortcode_atts', array( __CLASS__, 'country_shortcode_atts' ) );
		add_action( 'wc_price_based_country_widget_before_selected', array( __CLASS__, 'country_widget_before_selected' ), 10, 4 );
	}

	/**
	 * Return zone data
	 *
	 * @param string $key Property to get.
	 * @param mixed  $default Default value.
	 * @return mixed
	 */
	private static function get_zone_data_value( $key, $default = false ) {
		$value = false;

		if ( self::$zone_data && isset( self::$zone_data[ $key ] ) ) {
			$value = self::$zone_data[ $key ];
		} elseif ( WCPBC()->current_zone ) {
			$data = WCPBC()->current_zone->get_data();
			if ( ! wcpbc_empty_nozero( $data[ $key ] ) ) {
				$value = $data[ $key ];
			}
		}

		if ( wcpbc_empty_nozero( $value ) ) {
			$value = $default;
		}

		return $value;
	}

	/**
	 * Set zone data  for order items email
	 *
	 * @param array $args Order items args.
	 * @return array
	 */
	public static function email_order_zone_data( $args ) {
		self::$zone_data = false;

		if ( isset( $args['order'] ) ) {
			$zone = WCPBC_Pricing_Zones::get_zone_from_order( $args['order'] );
			if ( $zone ) {
				self::$zone_data = $zone->get_data();
				add_filter( 'woocommerce_currency', array( __CLASS__, 'email_order_currency' ), 200 );
				add_filter( 'woocommerce_mail_content', array( __CLASS__, 'clear_email_order_zone_data' ) );
			}
		}

		return $args;
	}

	/**
	 * Return the order email currency code.
	 *
	 * @param string $currency_code Currency code.
	 * @return string
	 */
	public static function email_order_currency( $currency_code ) {
		return self::get_zone_data_value( 'currency' );
	}

	/**
	 * Clear email zone data and hooks
	 *
	 * @param string $content Mail content.
	 * @return string
	 */
	public static function clear_email_order_zone_data( $content = '' ) {
		self::$zone_data = false;
		remove_filter( 'woocommerce_currency', array( __CLASS__, 'email_order_currency' ), 200 );
		remove_filter( 'woocommerce_mail_content', array( __CLASS__, 'clear_email_order_zone_data' ) );

		return $content;
	}

	/**
	 * Get the price format.
	 *
	 * @param string $format Price format.
	 * @param string $currency_pos WooCommerce currency position option value.
	 * @return string
	 */
	public static function get_price_format( $format, $currency_pos ) {
		$currency_format = self::get_zone_data_value( 'currency_format', get_option( 'wc_price_based_currency_format' ) );
		if ( $currency_format ) {

			if ( strpos( $currency_format, '[price]' ) === false ) {
				$currency_format .= '[price]';
			}
			if ( strpos( $currency_format, '[symbol]' ) !== false && strpos( $currency_format, '[symbol-alt]' ) !== false ) {
				$currency_format = str_replace( '[symbol]', '', $currency_format );
			}

			$format = str_replace( array( '[symbol]', '[symbol-alt]', '[price]', ' ', '[code]' ), array( '%1$s', '%1$s', '%2$s', '&nbsp;', get_woocommerce_currency() ), $currency_format );
		}
		return $format;
	}

	/**
	 * Set currency code to price format
	 *
	 * @param array $args WC_Price arguments.
	 * @return array
	 */
	public static function wc_price_args( $args ) {
		if ( ! empty( $args['currency'] ) ) {
			$args['price_format'] = str_replace( get_woocommerce_currency(), $args['currency'], $args['price_format'] );
		}

		return $args;
	}

	/**
	 * Filters the string of price markup.
	 *
	 * @param string $html  Price HTML markup.
	 * @param string $price Formatted price.
	 * @param array  $args  Pass on the args.
	 */
	public static function wc_price( $html, $price, $args ) {
		$currency_code = empty( $args['currency'] ) ? get_woocommerce_currency() : $args['currency'];
		$html          = str_replace( $currency_code, '<span class="woocommerce-Price-currencyCode">' . $currency_code . '</span>', $html );

		return $html;
	}

	/**
	 * Return the thousand separator for prices.
	 *
	 * @param string $thousand_sep Thousand separator.
	 * @return string
	 */
	public static function price_thousand_sep( $thousand_sep ) {
		$_price_thousand_sep = self::get_zone_data_value( 'price_thousand_sep' );
		if ( $_price_thousand_sep ) {
			$thousand_sep = $_price_thousand_sep;
		}

		return $thousand_sep;
	}

	/**
	 * Return the decimal separator for prices.
	 *
	 * @param string $decimal_sep Decimal separator.
	 * @return string
	 */
	public static function price_decimal_sep( $decimal_sep ) {
		$_price_decimal_sep = self::get_zone_data_value( 'price_decimal_sep' );
		if ( $_price_decimal_sep ) {
			$decimal_sep = $_price_decimal_sep;
		}

		return $decimal_sep;
	}

	/**
	 * Return the number of decimals for prices.
	 *
	 * @param string $num_decimals Number of decimals.
	 * @return string
	 */
	public static function price_num_decimals( $num_decimals ) {

		$_price_num_decimals = self::get_zone_data_value( 'price_num_decimals' );

		if ( false !== $_price_num_decimals ) {
			$num_decimals = $_price_num_decimals;
		}

		return $num_decimals;
	}

	/**
	 * Returns the default number of decimals for prices.
	 *
	 * @since 2.8.6
	 */
	public static function base_num_decimals() {
		remove_filter( 'option_woocommerce_price_num_decimals', array( __CLASS__, 'price_num_decimals' ), 100 );
		$base_num_decimals = get_option( 'woocommerce_price_num_decimals' );
		add_filter( 'option_woocommerce_price_num_decimals', array( __CLASS__, 'price_num_decimals' ), 100 );
		return $base_num_decimals;
	}

	/**
	 * Retruns the alternative currency symbols.
	 *
	 * @since 2.7.0
	 * @return array
	 */
	public static function get_alt_currency_symbols() {
		return array(
			'USD' => 'US&#36;',
			'CAD' => 'CA&#36;',
			'AED' => 'AED',
			'AFN' => 'Af',
			'ALL' => 'ALL',
			'AMD' => 'AMD',
			'ARS' => 'AR&#36;',
			'AUD' => 'AU&#36;',
			'AZN' => 'man&#46;',
			'BDT' => 'Tk',
			'BGN' => 'BGN',
			'BHD' => 'BD',
			'BND' => 'BN&#36;',
			'BWP' => 'BWP',
			'BZD' => 'BZ&#36;',
			'CDF' => 'CDF',
			'CLP' => 'CL&#36;',
			'COP' => 'CO&#36;',
			'DKK' => 'Dkr',
			'DZD' => 'DA',
			'EEK' => 'Ekr',
			'EGP' => 'EGP',
			'GTQ' => 'GTQ',
			'HKD' => 'HK&#36;',
			'HNL' => 'HNL',
			'INR' => 'Rs',
			'IQD' => 'IQD',
			'IRR' => 'IRR',
			'ISK' => 'Ikr',
			'JMD' => 'J&#36;',
			'JOD' => 'JD',
			'KHR' => 'KHR',
			'KMF' => 'CF',
			'KWD' => 'KD',
			'KZT' => 'KZT',
			'LBP' => 'LB&pound;',
			'LKR' => 'SLRs',
			'LYD' => 'LD',
			'MAD' => 'MAD',
			'MMK' => 'MMK',
			'MXN' => 'MX&#36;',
			'NOK' => 'Nkr',
			'NPR' => 'NPRs',
			'NZD' => 'NZ&#36;',
			'OMR' => 'OMR',
			'PKR' => 'PKRs',
			'QAR' => 'QR',
			'RSD' => 'din&#46;',
			'RUB' => 'RUB',
			'RWF' => 'RWF',
			'SAR' => 'SR',
			'SEK' => 'Skr',
			'SGD' => 'S&#36;',
			'SYP' => 'SY&pound;',
			'TND' => 'DT',
			'TTD' => 'TT&#36;',
			'UYU' => '&#36;U',
			'YER' => 'YR',
		);
	}

	/**
	 * Retrun the alternative currency symbol.
	 *
	 * @since 2.7.0
	 * @param string $currency_symbol Currency symbol.
	 * @param string $currency Currency code.
	 * @return string
	 */
	public static function alt_currency_symbol( $currency_symbol, $currency ) {

		$currency_format = self::get_zone_data_value( 'currency_format', get_option( 'wc_price_based_currency_format' ) );

		if ( strpos( $currency_format, '[symbol-alt]' ) === false ) {
			return $currency_symbol;
		}

		$symbols = self::get_alt_currency_symbols();

		return ( isset( $symbols[ $currency ] ) ? $symbols[ $currency ] : $currency_symbol );
	}

	/**
	 * Enqueue scripts
	 *
	 * @since 2.2.0
	 */
	public static function enqueue_scripts() {

		if ( ! did_action( 'before_woocommerce_init' ) ) {
			return;
		}

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_register_script(
			'wc-price-based-country-pro-frontend',
			WC_Product_Price_Based_Country_Pro::plugin_url() . 'assets/js/frontend' . $suffix . '.js',
			array( 'jquery' ),
			WC_Product_Price_Based_Country_Pro::$version,
			true
		);
		wp_localize_script(
			'wc-price-based-country-pro-frontend',
			'wc_price_based_country_pro_frontend_params',
			array(
				'currency_format_symbol'       => get_woocommerce_currency_symbol(),
				'currency_format_code'         => get_woocommerce_currency(),
				'currency_format_num_decimals' => esc_attr( wc_get_price_decimals() ),
				'currency_format_decimal_sep'  => esc_attr( wc_get_price_decimal_separator() ),
				'currency_format_thousand_sep' => esc_attr( wc_get_price_thousand_separator() ),
				'currency_format'              => esc_attr( str_replace( array( '%1$s', '%2$s' ), array( '%s', '%v' ), get_woocommerce_price_format() ) ),
			)
		);
	}

	/**
	 * Return the Pro country switcher widget settings
	 *
	 * @param array $settings Array with settings.
	 * @return array
	 */
	public static function country_widget_settings( $settings ) {
		unset( $settings['remove_other_countries_pro'] );
		$settings['remove_other_countries'] = array(
			'type'  => 'checkbox',
			'std'   => 0,
			'label' => __( 'Remove "Other countries" from switcher.', 'wc-price-based-country-pro' ),
		);
		return $settings;
	}

	/**
	 * Add the remove_other_countries param to shortcode default atts
	 *
	 * @param array $defaults Defautls values.
	 * @return array
	 */
	public static function country_shortcode_atts( $defaults ) {
		$defaults['remove_other_countries'] = false;

		return $defaults;
	}

	/**
	 * Filter the widget countries option before display
	 *
	 * @param string $other_country Country code for no country match.
	 * @param array  $countries Countries.
	 * @param string $base_country The shop base country.
	 * @param array  $instance Widget parameters.
	 */
	public static function country_widget_before_selected( &$other_country, &$countries, $base_country, $instance ) {

		if ( ! empty( $instance['remove_other_countries'] ) && 'false' !== $instance['remove_other_countries'] ) {
			unset( $countries[ $other_country ] );
			$other_country = $base_country;
		}

	}
}

