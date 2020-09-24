<?php
/**
 * Handle integration with Germanized for WooCommerce by Vendidero.
 *
 * @see https://es.wordpress.org/plugins/woocommerce-germanized/
 * @version 2.8.8
 * @package WCPBC
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WCPBC_Germanized' ) ) :

	/**
	 *
	 * WCPBC_Germanized Class
	 */
	class WCPBC_Germanized {

		/**
		 * Check enviroment notice.
		 *
		 * @var string
		 */
		private static $notice = '';

		/**
		 * Hook actions and filters
		 */
		public static function init() {
			add_filter( 'wc_price_based_country_product_simple_fields', array( __CLASS__, 'product_simple_fields' ), 20 );
			add_filter( 'wc_price_based_country_product_variation_fields', array( __CLASS__, 'product_variation_fields' ), 20, 2 );
			add_action( 'woocommerce_process_product_meta_simple', array( __CLASS__, 'process_product_meta' ), 30 );
			add_action( 'woocommerce_process_product_meta_subscription', array( __CLASS__, 'process_product_meta' ), 30 );
			add_action( 'woocommerce_save_product_variation', array( __CLASS__, 'process_product_meta' ), 30, 2 );
			add_action( 'wc_price_based_country_frontend_princing_init', array( __CLASS__, 'frontend_init' ) );
			if ( is_callable( array( 'WCPBC_Ajax_Geolocation', 'is_enabled' ) ) && WCPBC_Ajax_Geolocation::is_enabled() ) {
				add_filter( 'woocommerce_gzd_unit_price_html', array( __CLASS__, 'unit_price_html' ), 10, 2 );
				add_filter( 'wc_price_based_country_ajax_geolocation_product_data', array( __CLASS__, 'ajax_geolocation_product_data' ), 10, 2 );
			}
		}

		/**
		 * Add the base regular price and the base sale price fields to the product simple.
		 *
		 * @param array $fields Product simple fields.
		 * @return array
		 */
		public static function product_simple_fields( $fields ) {

			$fields[] = array(
				'name'  => '_unit_price_regular',
				// translators: %s is a currency symbol.
				'label' => __( 'Regular Base Price (%s)', 'wc-price-based-country-pro' ),
			);

			$fields[] = array(
				'name'  => '_unit_price_sale',
				// translators: %s is a currency symbol.
				'label' => __( 'Sale Base Price (%s)', 'wc-price-based-country-pro' ),
			);

			return $fields;
		}

		/**
		 * Add the the base regular price and the base sale price fields to the product variation.
		 *
		 * @param array $fields Product simple fields.
		 * @param int   $loop Index of loop variation.
		 * @return array
		 */
		public static function product_variation_fields( $fields, $loop ) {

			$fields['_unit_price_regular'] = array(
				'name'          => "_variable_unit_price_regular[{$loop}]",
				// translators: %s is a currency symbol.
				'label'         => __( 'Regular Unit Price (%s)', 'wc-price-based-country-pro' ),
				'wrapper_class' => 'form-row form-row-first',
			);

			$fields['_unit_price_sale'] = array(
				'name'          => "_variable_unit_price_sale[{$loop}]",
				// translators: %s is a currency symbol.
				'label'         => __( 'Sale Unit Price (%s)', 'wc-price-based-country-pro' ),
				'wrapper_class' => 'form-row form-row-last',
			);

			return $fields;
		}

		/**
		 * Save the base regular price and the base sale price data.
		 *
		 * @param int $post_id WP post id.
		 * @param int $index   Index of variations to save.
		 */
		public static function process_product_meta( $post_id, $index = false ) {
			$variable = false === $index ? '' : '_variable';

			foreach ( WCPBC_Pricing_Zones::get_zones() as $zone ) {

				$price_method = $zone->get_input_var( $variable . '_price_method', $index );

				if ( wcpbc_is_exchange_rate( $price_method ) ) {
					$unit_price_regular = $zone->get_exchange_rate_price_by_post( $post_id, '_unit_price_regular' );
					$unit_price_sale    = $zone->get_exchange_rate_price_by_post( $post_id, '_unit_price_sale' );
				} else {
					$unit_price_regular = wc_format_decimal( $zone->get_input_var( $variable . '_unit_price_regular', $index ) );
					$unit_price_sale    = wc_format_decimal( $zone->get_input_var( $variable . '_unit_price_sale', $index ) );
				}

				$zone->set_postmeta( $post_id, '_unit_price_regular', $unit_price_regular );
				$zone->set_postmeta( $post_id, '_unit_price_sale', $unit_price_sale );
			}
		}

		/**
		 * Frontend pricing hooks.
		 */
		public static function frontend_init() {
			add_filter( 'woocommerce_gzd_get_unit_regular_price', array( __CLASS__, 'get_unit_price_regular' ), 10, 2 );
			add_filter( 'woocommerce_gzd_get_product_unit_price_regular', array( __CLASS__, 'get_unit_price_regular' ), 10, 2 );
			add_filter( 'woocommerce_gzd_get_product_variation_unit_price_regular', array( __CLASS__, 'get_unit_price_regular' ), 10, 2 );

			add_filter( 'woocommerce_gzd_get_unit_sale_price', array( __CLASS__, 'get_unit_sale_price' ), 10, 2 );
			add_filter( 'woocommerce_gzd_get_product_unit_price_sale', array( __CLASS__, 'get_unit_sale_price' ), 10, 2 );
			add_filter( 'woocommerce_gzd_get_product_variation_unit_price_sale', array( __CLASS__, 'get_unit_sale_price' ), 10, 2 );

			add_filter( 'woocommerce_gzd_get_unit_price_raw', array( __CLASS__, 'get_unit_price_raw' ), 10, 2 );
			add_filter( 'woocommerce_gzd_get_product_unit_price', array( __CLASS__, 'get_unit_price_raw' ), 10, 2 );
			add_filter( 'woocommerce_gzd_get_product_variation_unit_price', array( __CLASS__, 'get_unit_price_raw' ), 10, 2 );

			add_filter( 'woocommerce_gzd_get_variation_unit_prices_hash', array( 'WCPBC_Frontend_Pricing', 'get_variation_prices_hash' ), 10, 3 );
		}

		/**
		 * Calculate base prices automatically?.
		 *
		 * @param WC_Product $product Product object.
		 * @return bool
		 */
		private static function is_unit_price_auto( $product ) {
			return class_exists( 'WooCommerce_Germanized_Pro' ) && 'yes' === $product->get_meta( '_unit_price_auto' );
		}

		/**
		 * Return the unit regular price.
		 *
		 * @param string  $value Unit regular price.
		 * @param WC_Data $data  WC_Data object.
		 */
		public static function get_unit_price_regular( $value, $data ) {
			if ( self::is_unit_price_auto( $data ) ) {
				return $value;
			}
			return wcpbc_the_zone()->get_post_price( $data->get_id(), '_unit_price_regular' );
		}

		/**
		 * Return the unit sale price.
		 *
		 * @param string  $value Unit sale price.
		 * @param WC_Data $data  WC_Data object.
		 */
		public static function get_unit_sale_price( $value, $data ) {
			if ( self::is_unit_price_auto( $data ) ) {
				return $value;
			}
			return wcpbc_the_zone()->get_post_price( $data->get_id(), '_unit_price_sale' );
		}

		/**
		 * Return the raw unit sale price.
		 *
		 * @param string  $value Unit raw price.
		 * @param WC_Data $data  WC_Data object.
		 */
		public static function get_unit_price_raw( $value, $data ) {
			if ( self::is_unit_price_auto( $data ) ) {
				return $value;
			}

			if ( $data->is_on_sale() ) {
				$value = wcpbc_the_zone()->get_post_price( $data->get_id(), '_unit_price_sale' );
			} else {
				$value = wcpbc_the_zone()->get_post_price( $data->get_id(), '_unit_price_regular' );
			}
			return $value;
		}

		/**
		 * Retrun unit price html with the wrapper for AJAX Geolocation.
		 *
		 * @param string         $html    HTML unit price.
		 * @param WC_GZD_Product $product The product object.
		 */
		public static function unit_price_html( $html, $product ) {
			if ( is_cart() || is_account_page() || is_checkout() || is_customize_preview() ) {
				return $html;
			}
			return '<span class="wcpbc-gzd-price-unit wcpbc-gzd-price-unit-' . $product->get_id() . '">' . $html . '</span>';
		}

		/**
		 * Add extra data to the AJAX geolocate array.
		 *
		 * @param array      $data Data to geolocate price.
		 * @param WC_Product $product Product object.
		 * @return array
		 */
		public static function ajax_geolocation_product_data( $data, $product ) {
			if ( function_exists( 'wc_gzd_get_gzd_product' ) ) {
				$gzd_product             = wc_gzd_get_gzd_product( $product );
				$data['unit_price_html'] = is_callable( array( $gzd_product, 'get_unit_price_html' ) ) ? $gzd_product->get_unit_price_html() : $gzd_product->get_unit_html();
			}
			return $data;
		}

		/**
		 * Checks the environment for compatibility problems.
		 *
		 * @return boolean
		 */
		public static function check_environment() {
			$germanized_version = function_exists( 'WC_germanized' ) ? WC_germanized()->version : 'unknown';

			if ( 'unknown' === $germanized_version || version_compare( $germanized_version, '2.3.0', '<' ) ) {
				// translators: 1: HTML tag, 2: HTML tag, 3: Germanized for WooCommerce version.
				self::$notice = sprintf( __( '%1$sPrice Based on Country Pro & Germanized for WooCommerce%2$s compatibility requires Germanized for WooCommerce +2.3.0. You are running Germanized for WooCommerce %3$s.', 'wc-price-based-country-pro' ), '<strong>', '</strong>', $germanized_version );
				add_action( 'admin_notices', array( __CLASS__, 'min_version_notice' ) );
				return false;
			}

			if ( version_compare( WC_VERSION, '3.0', '<' ) ) {
				// translators: 1: HTML tag, 2: HTML tag, 3: WooCommerce version.
				self::$notice = sprintf( __( '%1$sPrice Based on Country Pro & Germanized for WooCommerce%2$s compatibility requires WooCommerce +3.0. You are running WooCommerce %3$s.', 'wc-price-based-country-pro' ), '<strong>', '</strong>', WC_VERSION );
				add_action( 'admin_notices', array( __CLASS__, 'min_version_notice' ) );
				return false;
			}

			return true;
		}

		/**
		 * Display admin minimun version required
		 */
		public static function min_version_notice() {
			echo '<div id="message" class="error"><p>' . wp_kses_post( self::$notice ) . '</p></div>';
		}
	}

	if ( WCPBC_Germanized::check_environment() ) {
		WCPBC_Germanized::init();
	}

endif;

