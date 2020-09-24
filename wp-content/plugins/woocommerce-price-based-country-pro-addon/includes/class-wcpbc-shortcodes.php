<?php
/**
 * Shortcodes.
 *
 * @version 2.4.10
 * @package WCPBC/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WCPBC_Shortcodes' ) ) :

	/**
	 * Shortcodes Class
	 */
	class WCPBC_Shortcodes {

		/**
		 * Curency code.
		 *
		 * @var string
		 */
		private static $product_price_currency = false;

		/**
		 * Init shortcodes.
		 */
		public static function init() {
			$shortcodes = array(
				'currency_switcher',
				'product_price',
				'content',
				'content_default',
				'convert',
			);

			foreach ( $shortcodes as $shortcode ) {
				add_shortcode( "wcpbc_{$shortcode}", array( __CLASS__, $shortcode ), 20 );
			}

			// Refresh area to convert shortcode.
			add_filter( 'wc_price_based_country_ajax_geolocation_convert_amount_content', array( __CLASS__, 'ajax_convert' ), 10, 2 );
		}

		/**
		 * Output Currency Switcher
		 *
		 * @param array $atts Shortcode attributes.
		 * @return string
		 */
		public static function currency_switcher( $atts ) {

			$atts = shortcode_atts(
				array(
					'currency_display_style' => '[name] ([symbol])',
					'title'                  => '',
				),
				$atts,
				'wcpbc_currency_switcher'
			);

			$atts['currency_display_style'] = str_replace( array( '{', '}' ), array( '[', ']' ), $atts['currency_display_style'] );

			ob_start();

			the_widget( 'WCPBC_Widget_Currency_Switcher', $atts );

			return ob_get_clean();
		}

		/**
		 * Output price html for a pair product and zone
		 *
		 * @param array $atts Shortcode attributes.
		 */
		public static function product_price( $atts ) {
			$atts = shortcode_atts(
				array(
					'id'         => '',
					'zone'       => '',
					'product_id' => '',
					'zone_slug'  => '',
				),
				$atts,
				'wcpbc_product_price'
			);

			$price_html = '';
			$product_id = empty( $atts['id'] ) ? $atts['product_id'] : $atts['id'];
			$zone_slug  = empty( $atts['zone'] ) ? $atts['zone_slug'] : $atts['zone'];
			$_product   = wc_get_product( $product_id );

			if ( $_product ) {

				if ( ! $zone_slug ) {

					$price_html = $_product->get_price_html();

				} else {

					$zone = WCPBC_Pricing_Zones::get_zone( $zone_slug );
					if ( $zone ) {
						self::$product_price_currency = $zone->get_currency();

						$zone_slug = $zone->get_zone_id();

						$_product->set_regular_price( $_product->get_meta( "_{$zone_slug}_regular_price" ) );
						$_product->set_sale_price( $_product->get_meta( "_{$zone_slug}_sale_price" ) );
						$_product->set_price( $_product->get_meta( "_{$zone_slug}_price" ) );

						add_filter( 'wc_price_args', array( __CLASS__, 'product_price_args' ) );

						$price_html = $_product->get_price_html();

						remove_filter( 'wc_price_args', array( __CLASS__, 'product_price_args' ) );

						self::$product_price_currency = false;
					} else {
						return '<span>' . __( 'Pricing zone not found.', 'wc-price-based-country-pro' ) . '<span>';
					}
				}
			} else {
				return '<span>' . __( 'Product not found.', 'wc-price-based-country-pro' ) . '<span>';
			}

			return $price_html;
		}

		/**
		 * Return wc_price $args for product_price shortcode
		 *
		 * @param array $args WC Price arguments.
		 * @return array
		 */
		public static function product_price_args( $args ) {
			if ( ! self::$product_price_currency ) {
				return $args;
			}

			$args['currency']     = self::$product_price_currency;
			$args['price_format'] = str_replace( get_woocommerce_currency(), self::$product_price_currency, $args['price_format'] );

			return $args;
		}

		/**
		 * Is the Geolcation AJAX enabled.
		 *
		 * @return bool
		 */
		private static function is_geolocation_ajax() {
			if ( is_cart() || is_account_page() || is_checkout() || is_customize_preview() ) {
				return false;
			}
			return WCPBC_Ajax_Geolocation::is_enabled();
		}

		/**
		 * Output the content filtered by zone
		 *
		 * @since 2.4.10
		 * @param array  $atts Shortcode attributes.
		 * @param string $content HTML content that comes between the shortcode tags.
		 * @return string HTML
		 */
		public static function content( $atts, $content = '' ) {
			$atts = shortcode_atts(
				array(
					'zone'        => '',
					'zone_slug'   => '',
					'wrapper_tag' => 'span',
				),
				$atts,
				'wcpbc_content'
			);

			$zone_slug = empty( $atts['zone'] ) ? $atts['zone_slug'] : $atts['zone'];
			return self::wrapper_content( $content, $zone_slug, $atts['wrapper_tag'] );
		}

		/**
		 * Output the content for default zone
		 *
		 * @since 2.4.10
		 * @param array  $atts Shortcode attributes.
		 * @param string $content HTML content that comes between the shortcode tags.
		 * @return string HTML
		 */
		public static function content_default( $atts = array(), $content = '' ) {
			$atts = shortcode_atts(
				array(
					'wrapper_tag' => 'span',
				),
				$atts,
				'wcpbc_content'
			);

			return self::wrapper_content( $content, false, $atts['wrapper_tag'] );
		}

		/**
		 * Add wrapper to the content shortcode.
		 *
		 * @param string $content Shortcode content.
		 * @param string $zone_id Pricing zone ID.
		 * @param string $tag Wrapper HTML tag.
		 */
		private static function wrapper_content( $content, $zone_id, $tag ) {
			$_content        = '';
			$current_zone_id = wcpbc_the_zone() ? wcpbc_the_zone()->get_zone_id() : false;
			$atts            = array();
			$tag             = esc_attr( $tag );

			if ( self::is_geolocation_ajax() ) {
				$atts     = array(
					'data-zone_id' => $zone_id,
					'class'        => 'wcpbc-content ' . ( $zone_id ? 'content-' . $zone_id : 'no-zone' ),
				);
				$_content = $content;

			} elseif ( $current_zone_id === $zone_id ) {
				$_content = $content;
			}

			if ( ! empty( $_content ) ) {
				$content_start = '<' . $tag . ' ';
				foreach ( $atts as $key => $value ) {
					$content_start .= $key . '="' . esc_attr( $value ) . '" ';
				}
				$content_start .= '>';
				$_content       = $content_start . wp_kses_post( $_content ) . '</' . $tag . '>';
			}

			return $_content;
		}

		/**
		 * Return an amount convert by exchange rate.
		 *
		 * @since 2.5.0
		 * @param array $atts Shortcode attributes.
		 * @return string HTML
		 */
		public static function convert( $atts ) {
			$atts = shortcode_atts(
				array(
					'amount' => '',
				),
				$atts,
				'wcpbc_convert'
			);

			$output         = '';
			$amount         = wc_format_decimal( wp_kses_post( $atts['amount'] ) );
			$convert_amount = false;
			if ( $amount ) {
				$convert_amount = wcpbc_the_zone() ? wcpbc_the_zone()->get_exchange_rate_price( $amount ) : $amount;
				$output         = wc_price( $convert_amount );

				if ( self::is_geolocation_ajax() ) {
					$options       = wp_json_encode( array( 'amount' => $amount ) );
					$id            = md5( $options );
					$loading_class = defined( 'DOING_AJAX' ) && DOING_AJAX ? '' : ' wcpbc-price loading';
					$output        = '<span class="wc-price-based-country-refresh-area' . $loading_class . '" data-area="convert_amount" data-id="' . esc_attr( $id ) . '" data-options="' . esc_attr( $options ) . '">' . $output . '</span>';
				}
			}

			return $output;
		}

		/**
		 * Return the convert shortcode output. Use by AJAX geolocation.
		 *
		 * @param string $output The output.
		 * @param array  $options Refresh area options.
		 * @return string
		 */
		public static function ajax_convert( $output, $options ) {
			return self::convert( $options );
		}
	}

endif;
