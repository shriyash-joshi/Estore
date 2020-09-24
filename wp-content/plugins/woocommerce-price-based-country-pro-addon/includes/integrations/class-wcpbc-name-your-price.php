<?php
/**
 * Handle integration with WooCommerce Name Your Price.
 *
 * @since 2.6.0
 * @package WCPBC
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WCPBC_Name_Your_Price' ) ) :

	/**
	 * WCPBC_Name_Your_Price Class
	 */
	class WCPBC_Name_Your_Price {

		/**
		 * Hook actions and filters
		 */
		public static function init() {
			add_action( 'wc_price_based_country_frontend_princing_init', array( __CLASS__, 'frontend_pricing_init' ) );
			add_filter( 'woocommerce_add_cart_item_data', array( __CLASS__, 'add_cart_item_data' ), 10 );
			add_filter( 'woocommerce_get_cart_item_from_session', array( __CLASS__, 'get_cart_item_from_session' ), 18, 2 );
			add_filter( 'wc_price_based_country_ajax_geolocation_product_data', array( __CLASS__, 'ajax_geolocation_product_data' ), 10, 3 );
			add_filter( 'woocommerce_nyp_html', array( __CLASS__, 'ajax_geolocation_price_html' ), 10, 2 );
			add_filter( 'woocommerce_variable_nyp_html', array( __CLASS__, 'ajax_geolocation_price_html' ), 10, 2 );
			if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
				// Admin.
				add_filter( 'wc_price_based_country_product_simple_fields', array( __CLASS__, 'product_simple_fields' ) );
				add_filter( 'wc_price_based_country_product_variation_fields', array( __CLASS__, 'product_variation_fields' ), 10, 2 );
				add_action( 'woocommerce_process_product_meta_simple', array( __CLASS__, 'process_product_meta' ), 20 );
				add_action( 'woocommerce_process_product_meta_subscription', array( __CLASS__, 'process_product_meta' ), 30 );
				add_action( 'woocommerce_save_product_variation', array( __CLASS__, 'process_product_meta' ), 40, 2 );
			}
		}

		/**
		 * Frontend pricing init
		 */
		public static function frontend_pricing_init() {
			add_filter( 'woocommerce_raw_minimum_price', array( __CLASS__, 'get_metadata' ), 5, 2 );
			add_filter( 'woocommerce_raw_maximum_price', array( __CLASS__, 'get_metadata' ), 5, 2 );
			add_filter( 'woocommerce_raw_suggested_price', array( __CLASS__, 'get_metadata' ), 5, 2 );
		}

		/**
		 * Retrun the name your price meta.
		 *
		 * @param array $value Original value.
		 * @param array $post_id Product ID.
		 * @return array
		 */
		public static function get_metadata( $value, $post_id ) {
			$meta_key = str_replace( 'woocommerce_raw', '', current_filter() );
			$meta_key = '_minimum_price' === $meta_key ? '_min_price' : $meta_key;

			return wcpbc_the_zone()->get_post_price( $post_id, $meta_key );
		}

		/**
		 * Add cart session data.
		 *
		 * @param array $cart_item_data extra cart item data we want to pass into the item.
		 * @return array
		 */
		public static function add_cart_item_data( $cart_item_data ) {
			if ( ! empty( $cart_item_data['nyp'] ) ) {
				$cart_item_data['wcpbc_data'] = array(
					'zone_id'  => wcpbc_the_zone() ? wcpbc_the_zone()->get_zone_id() : false,
					'base_nyp' => wcpbc_the_zone() ? wcpbc_the_zone()->get_base_currency_amount( $cart_item_data['nyp'] ) : $cart_item_data['nyp'],
				);
			}
			return $cart_item_data;
		}

		/**
		 * Adjust the product based on cart session data.
		 *
		 * @param array $cart_item Cart item data.
		 * @param array $values Values from session.
		 */
		public static function get_cart_item_from_session( $cart_item, $values ) {
			if ( ! empty( $values['nyp'] ) && ! empty( $values['wcpbc_data'] ) ) {

				$current_zone_id = wcpbc_the_zone() ? wcpbc_the_zone()->get_zone_id() : false;

				if ( $current_zone_id !== $values['wcpbc_data']['zone_id'] ) {
					if ( $current_zone_id ) {
						$cart_item['nyp'] = wcpbc_the_zone()->get_exchange_rate_price( $values['wcpbc_data']['base_nyp'] );
					} else {
						$cart_item['nyp'] = $values['wcpbc_data']['base_nyp'];
					}
					$values['wcpbc_data']['zone_id'] = $current_zone_id;
				}
				$cart_item['wcpbc_data'] = $values['wcpbc_data'];

				// Adjust the product price.
				WC_Name_Your_Price()->cart->add_cart_item( $cart_item );
			}
			return $cart_item;
		}

		/**
		 * Support for Ajax geolocation.
		 *
		 * @param array      $data Array of product data.
		 * @param WC_Product $product Product instance.
		 * @param bool       $is_single Is single page?.
		 * @return array
		 */
		public static function ajax_geolocation_product_data( $data, $product, $is_single ) {
			if ( $is_single && WC_Name_Your_Price_Helpers::is_nyp( $product ) ) {
				$data['max_price']       = WC_Name_Your_Price_Helpers::get_maximum_price( $product );
				$data['min_price']       = WC_Name_Your_Price_Helpers::get_minimum_price( $product );
				$data['suggested_price'] = WC_Name_Your_Price_Helpers::get_suggested_price( $product );
				$data['price_attr']      = esc_attr( WC_Name_Your_Price_Helpers::format_price( WC_Name_Your_Price_Helpers::get_price_value_attr( $product ) ) );
				$data['min_price_html']  = WC_Name_Your_Price_Helpers::get_minimum_price_html( $product );
			}
			return $data;
		}

		/**
		 * Add a wrapper for ajax geolocation
		 *
		 * @param string     $price_html Subscription price string.
		 * @param WC_Product $product Product instance.
		 */
		public static function ajax_geolocation_price_html( $price_html, $product ) {
			if ( is_callable( array( 'WCPBC_Ajax_Geolocation', 'is_enabled' ) ) && WCPBC_Ajax_Geolocation::is_enabled() ) {
				$price_html = WCPBC_Ajax_Geolocation::wrapper_price( $product, $price_html );
			}

			return $price_html;
		}

		/**
		 * Add the name your price fields to product simple.
		 *
		 * @param array $fields Product simple fields.
		 * @return array
		 */
		public static function product_simple_fields( $fields ) {

			$fields[] = array(
				'name'          => '_suggested_price',
				'wrapper_class' => 'wcpbc_show_if_nyp',
				// translators: %s is a currency symbol.
				'label'         => __( 'Suggested Price (%s)', 'wc-price-based-country-pro' ),
			);

			$fields[] = array(
				'name'          => '_min_price',
				'wrapper_class' => 'wcpbc_show_if_nyp',
				// translators: %s is a currency symbol.
				'label'         => __( 'Minimum Price (%s)', 'wc-price-based-country-pro' ),
			);

			$fields[] = array(
				'name'          => '_maximum_price',
				'wrapper_class' => 'wcpbc_show_if_nyp',
				// translators: %s is a currency symbol.
				'label'         => __( 'Maximum Price (%s)', 'wc-price-based-country-pro' ),
			);

			return $fields;
		}

		/**
		 * Add the the name your price fields to product variation.
		 *
		 * @param array $fields Product simple fields.
		 * @param int   $loop Index of loop variation.
		 * @return array
		 */
		public static function product_variation_fields( $fields, $loop ) {
			$fields['_suggested_price'] = array(
				'name'          => "_variable_suggested_price[$loop]",
				// translators: %s is a currency symbol.
				'label'         => __( 'Suggested Price (%s)', 'wc-price-based-country-pro' ),
				'wrapper_class' => 'form-row form-row-first wcpbc_show_if_nyp',
			);
			$fields['_min_price']       = array(
				'name'          => "_variable_min_price[$loop]",
				// translators: %s is a currency symbol.
				'label'         => __( 'Minimum Price (%s)', 'wc-price-based-country-pro' ),
				'wrapper_class' => 'form-row form-row-last wcpbc_show_if_nyp',
			);
			$fields['_maximum_price']   = array(
				'name'          => "_variable_maximum_price[$loop]",
				// translators: %s is a currency symbol.
				'label'         => __( 'Maximum Price (%s)', 'wc-price-based-country-pro' ),
				'wrapper_class' => 'form-row form-row-first wcpbc_show_if_nyp',
			);

			return $fields;
		}

		/**
		 * Save product metadata
		 *
		 * @param int $post_id Post ID.
		 * @param int $index Index of variations to save.
		 */
		public static function process_product_meta( $post_id, $index = false ) {
			$_nyp = get_post_meta( $post_id, '_nyp', true );

			foreach ( WCPBC_Pricing_Zones::get_zones() as $zone ) {
				$variable = false === $index ? '' : '_variable';
				if ( wcpbc_is_exchange_rate( $zone->get_input_var( $variable . '_price_method', $index ) ) ) {
					$_suggested_price = $zone->get_exchange_rate_price_by_post( $post_id, '_suggested_price' );
					$_min_price       = $zone->get_exchange_rate_price_by_post( $post_id, '_min_price' );
					$_maximum_price   = $zone->get_exchange_rate_price_by_post( $post_id, '_maximum_price' );
				} else {
					$_suggested_price = wc_format_decimal( $zone->get_input_var( $variable . '_suggested_price', $index ) );
					$_min_price       = wc_format_decimal( $zone->get_input_var( $variable . '_min_price', $index ) );
					$_maximum_price   = wc_format_decimal( $zone->get_input_var( $variable . '_maximum_price', $index ) );
				}

				$zone->set_postmeta( $post_id, '_suggested_price', $_suggested_price );
				$zone->set_postmeta( $post_id, '_min_price', $_min_price );
				$zone->set_postmeta( $post_id, '_maximum_price', $_maximum_price );

				if ( 'yes' === $_nyp ) {
					$zone->set_postmeta( $post_id, '_price', $_min_price );
					$zone->set_postmeta( $post_id, '_regular_price', $_min_price );
					$zone->set_postmeta( $post_id, '_sale_price', '' );

					$product_type = ! empty( $_POST['product-type'] ) ? wc_clean( wp_unslash( $_POST['product-type'] ) ) : false; // WPCS: CSRF ok.

					if ( 'subscription' === $product_type || 'variable-subscription' === $product_type ) {
						$zone->set_postmeta( $post_id, '_subscription_price', $_min_price );
					}
				}
			}
		}

		/**
		 * Display admin minimun version required
		 */
		public static function min_version_notice() {
			// translators: 1: HTML tag, 2: HTML tag, 3: Name Your Price version.
			$notice = sprintf( __( '%1$sPrice Based on Country Pro & Name Your Price%2$s compatibility requires Name Your Price version +2.9.3. You are running Name Your Price %3$s.', 'wc-price-based-country-pro' ), '<strong>', '</strong>', ( function_exists( 'WC_Name_Your_Price' ) ? WC_Name_Your_Price()->version : 'unknown' ) );
			echo '<div id="message" class="error"><p>' . wp_kses_post( $notice ) . '</p></div>';
		}
	}

	if ( function_exists( 'WC_Name_Your_Price' ) && version_compare( WC_Name_Your_Price()->version, '2.9.3', '>=' ) ) {
		WCPBC_Name_Your_Price::init();
	} else {
		add_action( 'admin_notices', array( 'WCPBC_Name_Your_Price', 'min_version_notice' ) );
	}

endif;

