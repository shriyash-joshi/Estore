<?php
/**
 * Geolocation via Ajax
 *
 * @since   1.7.0
 * @version 1.8.6
 * @package WCPBC
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ajax Geolocation Class
 */
class WCPBC_Ajax_Geolocation {

	/**
	 * Init hooks
	 */
	public static function init() {
		if ( ! self::is_enabled() ) {
			return;
		}
		add_filter( 'woocommerce_get_price_html', array( __CLASS__, 'price_html_wrapper' ), 0, 2 );
		add_action( 'wc_ajax_wcpbc_get_location', array( __CLASS__, 'get_customer_location' ) );
		add_filter( 'wc_price_based_country_ajax_geolocation_widget_content', array( __CLASS__, 'widget_content' ), 10, 2 );
	}

	/**
	 * Is ajax geolocation enabled?
	 *
	 * @return boolean
	 */
	public static function is_enabled() {
		return 'yes' === get_option( 'wc_price_based_country_caching_support', 'no' );
	}

	/**
	 * Add a wrapper to html price
	 *
	 * @param string     $price HTML product price.
	 * @param WC_Product $product The product object.
	 */
	public static function price_html_wrapper( $price, $product ) {
		if ( is_callable( array( 'WC_Subscriptions_Product', 'is_subscription' ) ) && WC_Subscriptions_Product::is_subscription( $product ) ) {
			return $price;
		}

		return self::wrapper_price( $product, $price );
	}

	/**
	 * Retrun html with the wrapper
	 *
	 * @param WC_Product $product The product object.
	 * @param string     $price_html HTML product price.
	 */
	public static function wrapper_price( $product, $price_html ) {
		if ( is_cart() || is_account_page() || is_checkout() || is_customize_preview() ) {
			return $price_html;
		}

		$class   = '';
		$spinner = '';

		if ( ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			$class   = ' loading';
			$spinner = '<span class="wcpbc-spinner"></span>';
		}
		$product_id = version_compare( WC_VERSION, '3.0', '<' ) ? $product->id : $product->get_id();

		return sprintf( '<span class="wcpbc-price wcpbc-price-%2$s%1$s" data-product-id="%2$s">%3$s%4$s</span>', $class, $product->get_id(), $price_html, $spinner );
	}

	/**
	 * Return customer location and array of product prices
	 */
	public static function get_customer_location() {
		$postdata       = wc_clean( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification
		$cached_version = self::get_cached_version( $postdata );

		if ( $cached_version ) {
			$data = $cached_version;
		} else {
			$data = array(
				'products' => array(),
				'areas'    => array(),
			);

			// Products.
			if ( ! empty( $postdata['ids'] ) && is_array( $postdata['ids'] ) ) {

				$product_ids = array_unique( array_map( 'absint', $postdata['ids'] ) );

				foreach ( $product_ids as $id ) {
					$_product = wc_get_product( $id );
					if ( $_product ) {
						$data['products'][ $id ] = apply_filters(
							'wc_price_based_country_ajax_geolocation_product_data',
							array(
								'id'                    => $id,
								'price_html'            => $_product->get_price_html(),
								'display_price'         => wc_get_price_to_display( $_product ),
								'display_regular_price' => wc_get_price_to_display( $_product, array( 'price' => $_product->get_regular_price() ) ),
							),
							$_product,
							! empty( $postdata['is_single'] )
						);
					}
				}
			}

			// Areas.
			if ( ! empty( $postdata['areas'] ) && is_array( $postdata['areas'] ) ) {

				foreach ( $postdata['areas'] as $type => $areas ) {

					foreach ( $areas as $id => $area ) {

						$content = apply_filters( 'wc_price_based_country_ajax_geolocation_' . $type . '_content', '', $area );

						if ( ! empty( $content ) ) {

							$data['areas'][] = array(
								'area'    => $type,
								'id'      => $id,
								'content' => $content,
							);
						}
					}
				}
			}

			// Currency.
			$data['currency_params'] = array(
				'symbol'       => get_woocommerce_currency_symbol(),
				'num_decimals' => esc_attr( wc_get_price_decimals() ),
				'decimal_sep'  => esc_attr( wc_get_price_decimal_separator() ),
				'thousand_sep' => esc_attr( wc_get_price_thousand_separator() ),
				'format'       => esc_attr( str_replace( array( '%1$s', '%2$s' ), array( '%s', '%v' ), get_woocommerce_price_format() ) ),
			);

			// Pricing zone ID.
			$data['zone_id'] = wcpbc_the_zone() ? wcpbc_the_zone()->get_zone_id() : '';

			self::set_cached_version( $postdata, $data );
		}

		wp_send_json( $data );
	}

	/**
	 * Whether or not the AJAX geolocation should use the caching layer.
	 *
	 * @return boolean Whether or not to utilize caching.
	 */
	protected static function should_use_cache() {
		return ! is_user_logged_in() && defined( 'WCPBC_CACHE_AJAXGEO_RESPONSE' ) && WCPBC_CACHE_AJAXGEO_RESPONSE;
	}

	/**
	 * Set the cached version.
	 *
	 * @param array $postdata POST array.
	 * @param array $reponse Data to be cached.
	 * @return array|bool
	 */
	private static function set_cached_version( $postdata, $reponse ) {
		if ( ! self::should_use_cache() ) {
			return false;
		}

		$transient_name    = self::get_transient_name( $postdata );
		$transient_version = WC_Cache_Helper::get_transient_version( 'product' );

		set_transient(
			$transient_name,
			array(
				'version' => $transient_version,
				'data'    => $reponse,
			),
			DAY_IN_SECONDS * 30
		);
	}

	/**
	 * Return the cached version.
	 *
	 * @param array $postdata POST array.
	 * @return array|bool
	 */
	private static function get_cached_version( $postdata ) {
		if ( ! self::should_use_cache() ) {
			return false;
		}

		$data           = false;
		$transient_name = self::get_transient_name( $postdata );
		$cached_data    = get_transient( $transient_name );

		if ( $cached_data && is_array( $cached_data ) && isset( $cached_data['version'] ) && isset( $cached_data['data'] ) ) {
			$transient_version = WC_Cache_Helper::get_transient_version( 'product' );
			if ( $transient_version === $cached_data['version'] ) {
				$data = $cached_data['data'];
			}
		}
		return $data;
	}

	/**
	 * Return the cache name.
	 *
	 * @param array $postdata Data of the _POST array.
	 * @return string
	 */
	private static function get_transient_name( $postdata ) {
		unset( $postdata['country'] );

		return 'pbc_ajaxgeo_' . md5(
			wp_json_encode(
				array(
					'data'    => $postdata,
					'country' => wcpbc_get_woocommerce_country(),
					'version' => wcpbc()->version . ( wcpbc_is_pro() && isset( WC_Product_Price_Based_Country_Pro::$version ) ? '.' . WC_Product_Price_Based_Country_Pro::$version : '' ),
				)
			)
		);
	}

	/**
	 * Return the widget content
	 *
	 * @param string $content The HTML widget content to return.
	 * @param array  $widget Widget data.
	 * @return string
	 */
	public static function widget_content( $content, $widget ) {

		$classname = self::get_widget_class_name( $widget['id'] );

		if ( class_exists( $classname ) ) {
			ob_start();

			the_widget(
				$classname,
				$widget['instance'],
				array(
					'before_widget' => '',
					'after_widget'  => '',
				)
			);

			$content = ob_get_clean();
		}

		return $content;
	}

	/**
	 * Return the widget class name from widget ID
	 *
	 * @param string $widget_id Widget ID.
	 * @return string
	 */
	private static function get_widget_class_name( $widget_id ) {
		$classname = str_replace( 'wcpbc', '', $widget_id );
		$classname = 'WCPBC_Widget' . implode( '_', array_map( 'ucfirst', explode( '_', $classname ) ) );
		return $classname;
	}
}

