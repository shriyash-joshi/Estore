<?php
/**
 * WooCommerce Price Based Country Front-End
 *
 * @version 1.8.10
 * @package WCPBC
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * WCPBC_Frontend Class
 */
class WCPBC_Frontend {

	/**
	 * Hook actions and filters
	 */
	public static function init() {
		add_filter( 'woocommerce_customer_default_location', array( __CLASS__, 'allowed_countries_filter' ) );
		add_filter( 'woocommerce_customer_default_location_array', array( __CLASS__, 'test_default_location' ) );
		add_filter( 'woocommerce_geolocate_ip', array( __CLASS__, 'set_remote_addr_to_real_ip' ), 5 );
		add_filter( 'woocommerce_cart_hash', array( __CLASS__, 'get_cart_hash' ) );
		add_filter( 'woocommerce_update_order_review_fragments', array( __CLASS__, 'update_order_review_fragments' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'load_scripts' ), 1 );
		add_action( 'wc_price_based_country_before_frontend_init', array( __CLASS__, 'set_customer_country' ), 20 );
		add_action( 'wp_footer', array( __CLASS__, 'test_store_message' ) );
		add_action( 'wcpbc_manual_country_selector', array( __CLASS__, 'output_country_selector' ) );
		add_shortcode( 'wcpbc_country_selector', array( __CLASS__, 'shortcode_country_selector' ) );
	}

	/**
	 * Add the allowed countries filter.
	 *
	 * @param mixed $value Value.
	 */
	public static function allowed_countries_filter( $value ) {
		add_filter( 'pre_option_woocommerce_allowed_countries', array( __CLASS__, 'allowed_countries' ) );
		return $value;
	}

	/**
	 * Returns 'all' for allowed countries countries option in the function wc_get_customer_default_location.
	 *
	 * @param string $value Option value.
	 */
	public static function allowed_countries( $value ) {
		return 'all';
	}

	/**
	 * Returns the test country as default location.
	 *
	 * @param array $location Country and state default location.
	 * @return array
	 */
	public static function test_default_location( $location ) {
		if ( get_option( 'wc_price_based_country_test_mode', 'no' ) === 'yes' && get_option( 'wc_price_based_country_test_country' ) ) {
			$location = wc_format_country_state_string( get_option( 'wc_price_based_country_test_country' ) );
		}
		remove_filter( 'pre_option_woocommerce_allowed_countries', array( __CLASS__, 'allowed_countries' ) );
		return $location;
	}

	/**
	 * Some hostings do not provide a valid customer's IP in the HTTP_X_FORWARDED_FOR server variable.
	 * To fix it update the HTTP_X_REAL_IP variable with the REMOTE_ADDR before WooCommerce get the customer country form the IP.
	 *
	 * @param string $value Value to return by the filter.
	 */
	public static function set_remote_addr_to_real_ip( $value ) {
		if ( defined( 'WCPBC_USE_REMOTE_ADDR' ) && WCPBC_USE_REMOTE_ADDR && empty( $_SERVER['HTTP_X_REAL_IP'] ) && isset( $_SERVER['REMOTE_ADDR'] ) ) {
			$_SERVER['HTTP_X_REAL_IP'] = wcpbc_sanitize_server_var( $_SERVER['REMOTE_ADDR'] ); // WPCS: sanitization ok.
		}
		return $value;
	}

	/**
	 * Add the currency to the cart hash.
	 *
	 * @param string $cart_hash Cart hash.
	 * @param bool   $force Force the cart hash addition. Default false.
	 */
	public static function get_cart_hash( $cart_hash, $force = false ) {
		if ( ! empty( $cart_hash ) || $force || isset( $_COOKIE['woocommerce_cart_hash'] ) ) {
			$cart_hash = md5( $cart_hash . get_woocommerce_currency() );
		}
		return $cart_hash;
	}

	/**
	 * Add the mini cart to the update order review fragments.
	 *
	 * @param array $fragments Order review fragments.
	 */
	public static function update_order_review_fragments( $fragments ) {
		$cart_hash = isset( WC()->cart ) && is_callable( array( WC()->cart, 'get_cart_hash' ) ) ? WC()->cart->get_cart_hash() : '-1';

		if ( ! empty( $_COOKIE['woocommerce_cart_hash'] ) && wc_clean( $_COOKIE['woocommerce_cart_hash'] ) !== $cart_hash ) {
			ob_start();

			woocommerce_mini_cart();

			$mini_cart = ob_get_clean();

			$fragments = array_merge(
				$fragments,
				apply_filters(
					'woocommerce_add_to_cart_fragments',
					array(
						'div.widget_shopping_cart_content' => '<div class="widget_shopping_cart_content">' . $mini_cart . '</div>',
					)
				)
			);
		}

		return $fragments;
	}

	/**
	 * Register/queue frontend scripts.
	 */
	public static function load_scripts() {

		if ( ! did_action( 'before_woocommerce_init' ) ) {
			return;
		}

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		// Scripts.
		$deps = array( 'jquery' );
		if ( wcpbc_is_pro() ) {
			$deps[] = 'wc-price-based-country-pro-frontend';
		}

		wp_register_script( 'wc-price-based-country-ajax-geo', WCPBC()->plugin_url() . 'assets/js/ajax-geolocation' . $suffix . '.js', $deps, WCPBC()->version, true );
		wp_localize_script(
			'wc-price-based-country-ajax-geo',
			'wc_price_based_country_ajax_geo_params',
			array(
				'wc_ajax_url' => WC_AJAX::get_endpoint( '%%endpoint%%' ),
			)
		);

		// Styles.
		wp_register_style( 'wc-price-based-country-frontend', WCPBC()->plugin_url() . 'assets/css/frontend' . $suffix . '.css', array(), WCPBC()->version );

		// Enqueue.
		if ( WCPBC_Ajax_Geolocation::is_enabled() && ! ( is_cart() || is_account_page() || is_checkout() || is_customize_preview() ) ) {
			wp_enqueue_script( 'wc-price-based-country-ajax-geo' );
			wp_enqueue_style( 'wc-price-based-country-frontend' );
		}
	}

	/**
	 * Set the customer country before the frontend pricing is loaded
	 *
	 * @since 1.7.8
	 */
	public static function set_customer_country() {
		// phpcs:disable WordPress.Security.NonceVerification
		if ( isset( $_GET['pay_for_order'] ) && ! empty( $_GET['key'] ) ) {
			// Pay for order page.
			self::pay_for_order_country( wc_clean( wp_unslash( $_GET['key'] ) ) );

		} elseif ( ! empty( $_REQUEST['wcpbc-manual-country'] ) ) {
			// Request param.
			wcpbc_set_woocommerce_country( wc_clean( wp_unslash( $_REQUEST['wcpbc-manual-country'] ) ) );
			add_action( 'send_headers', array( __CLASS__, 'init_session' ) );

		} elseif ( defined( 'WC_DOING_AJAX' ) && WC_DOING_AJAX && isset( $_GET['wc-ajax'] ) && 'update_order_review' === $_GET['wc-ajax'] ) {
			// Checkout page.
			self::checkout_country();

		} elseif ( ! empty( $_POST['calc_shipping_country'] ) && self::verify_shipping_calculator_nonce() ) {
			// Shipping calculator.
			self::calculate_shipping_country();
		}
		// phpcs:enable
	}

	/**
	 * Set customer country when customer arrives to the pay for order page
	 *
	 * @param string $order_key Key of the order.
	 * @since 1.7.8
	 */
	private static function pay_for_order_country( $order_key ) {
		$order_id = wc_get_order_id_by_order_key( $order_key );
		if ( $order_id ) {
			$billing_country  = get_post_meta( $order_id, '_billing_country', true );
			$shipping_country = get_post_meta( $order_id, '_shipping_country', true );
			if ( $billing_country ) {
				WC()->customer->set_billing_country( $billing_country );
				WC()->customer->set_shipping_country( $billing_country );
			}
			if ( $shipping_country ) {
				WC()->customer->set_shipping_country( $shipping_country );
			}

			add_action( 'send_headers', array( __CLASS__, 'init_session' ), 10 );
		}
	}

	/**
	 * Update WooCommerce Customer country on checkout
	 */
	private static function checkout_country() {
		check_ajax_referer( 'update-order-review', 'security' );

		$country   = isset( $_POST['country'] ) ? wc_clean( wp_unslash( $_POST['country'] ) ) : false;
		$s_country = isset( $_POST['s_country'] ) ? wc_clean( wp_unslash( $_POST['s_country'] ) ) : false;

		if ( $country ) {
			wcpbc_set_prop_value( wc()->customer, 'billing_country', $country );
		}

		if ( wc_ship_to_billing_address_only() ) {
			if ( $country ) {
				WC()->customer->set_shipping_country( $country );
			}
		} else {
			if ( $s_country ) {
				WC()->customer->set_shipping_country( $s_country );
			}
		}
	}

	/**
	 * Verify the shipping calculator nonce
	 *
	 * @since 1.7.6
	 * @return boolan
	 */
	private static function verify_shipping_calculator_nonce() {

		$nonce_value = ! empty( $_REQUEST['woocommerce-shipping-calculator-nonce'] ) ? $_REQUEST['woocommerce-shipping-calculator-nonce'] : ''; // @codingStandardsIgnoreLine.
		if ( empty( $nonce_value ) && ! empty( $_REQUEST['_wpnonce'] ) ) {
			$nonce_value = $_REQUEST['_wpnonce']; // @codingStandardsIgnoreLine.
		}
		return wp_verify_nonce( $nonce_value, 'woocommerce-shipping-calculator' ) || wp_verify_nonce( $nonce_value, 'woocommerce-cart' );
	}

	/**
	 * Update WooCommerce Customer country on calculate shipping
	 */
	private static function calculate_shipping_country() {

		$country = isset( $_POST['calc_shipping_country'] ) ? wc_clean( wp_unslash( $_POST['calc_shipping_country'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		if ( $country ) {
			wcpbc_set_prop_value( wc()->customer, 'billing_country', $country );
			WC()->customer->set_shipping_country( $country );
		}
	}

	/**
	 * Init customer session and refresh cart totals
	 *
	 * @since 1.7.0
	 * @access public
	 */
	public static function init_session() {
		if ( ! is_null( WC()->session ) && ! WC()->session->has_session() ) {
			WC()->session->set_customer_session_cookie( true );
		}

		if ( is_user_logged_in() ) {
			WC()->customer->save();
		}

		// Refresh cart total.
		$cart_content_total = version_compare( WC_VERSION, '3.2', '<' ) ? WC()->cart->cart_contents_total : WC()->cart->get_cart_contents_total();
		if ( $cart_content_total ) {
			WC()->cart->calculate_totals();
		} else {
			// Set the cart hash to force the cart fragments refresh.
			wc_setcookie( 'woocommerce_cart_hash', self::get_cart_hash( '', true ) );
		}
	}

	/**
	 * Print test store message
	 */
	public static function test_store_message() {
		if ( 'no' === get_option( 'wc_price_based_country_test_mode', 'no' ) ) {
			return;
		}

		$test_country = get_option( 'wc_price_based_country_test_country' );
		$countries    = WC()->countries->countries;

		if ( is_string( $test_country ) && $test_country && ! empty( $countries[ $test_country ] ) ) {
			$country = WC()->countries->countries[ $test_country ];
			// translators: HTML tags.
			echo wp_kses_post( '<p class="demo_store">' . sprintf( __( '%1$sPrice Based Country%2$s test mode enabled for testing %3$s. You should do tests on private browsing mode. Browse in private with %4$sFirefox%7$s, %5$sChrome%7$s and %6$sSafari%7$s', 'woocommerce-product-price-based-on-countries' ), '<strong>', '</strong>', $country, '<a style="display:inline;float:none;text-decoration:underline;" target="_blank" href="https://support.mozilla.org/en-US/kb/private-browsing-use-firefox-without-history">', '<a style="display:inline;float:none;text-decoration:underline;" target="_blank" href="https://support.google.com/chrome/answer/95464?hl=en">', '<a style="display:inline;float:none;text-decoration:underline;" target="_blank" href="https://support.apple.com/kb/ph19216?locale=en_US">', '</a>' ) . '</p>' );
		}
	}

	/**
	 * Output manual country select form
	 *
	 * @param string $other_countries_text Other countries text.
	 */
	public static function output_country_selector( $other_countries_text = '' ) {
		$atts = array();

		if ( ! empty( $other_countries_text ) ) {
			$atts = array(
				'other_countries_text' => $other_countries_text,
			);
		}

		echo self::shortcode_country_selector( $atts ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Return the country select form
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public static function shortcode_country_selector( $atts ) {

		$atts = shortcode_atts(
			array_merge(
				array(
					'other_countries_text' => apply_filters( 'wcpbc_other_countries_text', __( 'Other countries', 'woocommerce-product-price-based-on-countries' ) ),
					'title'                => '',
				),
				apply_filters( 'wc_price_based_country_shortcode_atts', array() )
			),
			$atts,
			'wcpbc_country_selector'
		);

		ob_start();

		the_widget(
			'WCPBC_Widget_Country_Selector',
			$atts,
			array(
				'before_widget' => '',
				'after_widget'  => '',
			)
		);

		return ob_get_clean();
	}

}
