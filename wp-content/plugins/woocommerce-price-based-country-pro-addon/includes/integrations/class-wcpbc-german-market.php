<?php
/**
 * Handle integration with WooCommerce Product Add-ons by WooCommerce.
 *
 * @since 2.4.5
 * @package WCPBC/Integrations
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WCPBC_German_Market' ) ) :

	/**
	 * WCPBC_German_Market Class
	 */
	class WCPBC_German_Market {

		/**
		 * Hook actions and filters
		 */
		public static function init() {
			add_action( 'woocommerce_product_data_panels', array( __CLASS__, 'product_write_panels' ) );
			add_action( 'woocommerce_product_after_variable_attributes', array( __CLASS__, 'product_after_variable_attributes' ), 20, 3 );
			add_action( 'woocommerce_process_product_meta', array( __CLASS__, 'process_product_meta' ), 20, 2 );
			add_action( 'woocommerce_save_product_variation', array( __CLASS__, 'save_product_variations' ), 10, 2 );
			add_action( 'wc_price_based_country_frontend_princing_init', array( __CLASS__, 'frontend_princing_init' ) );
			add_filter( 'wc_price_based_country_ajax_geolocation_product_data', array( __CLASS__, 'ajax_geolocation_product_data' ), 10, 2 );
			add_filter( 'wgm_product_summary_html', array( __CLASS__, 'product_summary_html' ), 100, 3 );
		}

		/**
		 * Add zone price per unit fields
		 */
		public static function product_write_panels() {
			global $thepostid, $post;
			$thepostid = empty( $thepostid ) ? $post->ID : $thepostid;

			if ( 'on' === get_option( 'woocommerce_de_automatic_calculation_ppu', 'off' ) ) {
				return;
			}

			foreach ( WCPBC_Pricing_Zones::get_zones() as $zone ) {
				wcpbc_pricing_input(
					array(
						'name'          => '_price_method_per_unit',
						'label'         => __( 'Price per Unit for', 'wc-price-based-country' ),
						'wrapper_class' => 'options_group wcpbc_price_per_unit_options',
						'fields'        => array(
							array(
								'name'  => '_regular_price_per_unit',
								// Translators: Currency symbol.
								'label' => __( 'Default Price (%s)', 'wc-price-based-country' ),
							),
							array(
								'name'  => '_sale_price_per_unit',
								// Translators: Currency symbol.
								'label' => __( 'Sale Price (%s)', 'wc-price-based-country' ),
							),
						),
					),
					$zone
				);
			}
		}

		/**
		 * Add zone price per unit fields to variation panel
		 *
		 * @param int     $loop Loop index.
		 * @param array   $variation_data Array of variation data @deprecated.
		 * @param WP_Post $variation Post object.
		 */
		public static function product_after_variable_attributes( $loop, $variation_data, $variation ) {
			if ( 'on' === get_option( 'woocommerce_de_automatic_calculation_ppu', 'off' ) ) {
				return;
			}

			foreach ( WCPBC_Pricing_Zones::get_zones() as $zone ) {
				wcpbc_pricing_input(
					array(
						'name'          => '_price_method_per_unit_variation[' . $loop . ']',
						'value'         => $zone->get_postmeta( $variation->ID, '_price_method_per_unit' ),
						'label'         => __( 'Price per Unit for', 'wc-price-based-country' ),
						'wrapper_class' => 'wcpbc_price_per_unit_variation_options options_group',
						'fields'        => array(
							array(
								'name'          => '_regular_price_per_unit_variation[' . $loop . ']',
								'value'         => $zone->get_postmeta( $variation->ID, '_regular_price_per_unit' ),
								// Translators: Currency symbol.
								'label'         => __( 'Default Price (%s)', 'wc-price-based-country' ),
								'wrapper_class' => 'form-row form-row-first',
							),
							array(
								'name'          => '_sale_price_per_unit_variation[' . $loop . ']',
								'value'         => $zone->get_postmeta( $variation->ID, '_sale_price_per_unit' ),
								// Translators: Currency symbol.
								'label'         => __( 'Sale Price (%s)', 'wc-price-based-country' ),
								'wrapper_class' => 'form-row form-row-last _variable_sale_price_wcpbc_field',
							),
						),
					),
					$zone
				);
			}
		}

		/**
		 * Save price per unit metadata
		 *
		 * @param int     $post_id The Post ID.
		 * @param WP_Post $post The post object.
		 */
		public static function process_product_meta( $post_id, $post ) {
			foreach ( WCPBC_Pricing_Zones::get_zones() as $zone ) {

				$data = array(
					'_price_method_per_unit'  => $zone->get_input_var( '_price_method_per_unit' ),
					'_regular_price_per_unit' => $zone->get_input_var( '_regular_price_per_unit' ),
					'_sale_price_per_unit'    => $zone->get_input_var( '_sale_price_per_unit' ),
				);

				self::save_per_unit_metadata( $post_id, $data, $zone );
			}
		}

		/**
		 * Save price per unit metadata
		 *
		 * @param int $variation_id The post ID of the variation.
		 * @param int $loop Array of variations index.
		 */
		public static function save_product_variations( $variation_id, $loop ) {
			foreach ( WCPBC_Pricing_Zones::get_zones() as $zone ) {
				$price_method_per_unit_key  = $zone->get_postmetakey( '_price_method_per_unit_variation' );
				$regular_price_per_unit_key = $zone->get_postmetakey( '_regular_price_per_unit_variation' );
				$sale_price_per_unit_key    = $zone->get_postmetakey( '_sale_price_per_unit_variation' );

				$data = array(
					'_price_method_per_unit'  => isset( $_POST[ $price_method_per_unit_key ][ $loop ] ) ? wc_clean( wp_unslash( $_POST[ $price_method_per_unit_key ][ $loop ] ) ) : '', // WPCS: CSRF ok.
					'_regular_price_per_unit' => isset( $_POST[ $regular_price_per_unit_key ][ $loop ] ) ? wc_clean( wp_unslash( $_POST[ $regular_price_per_unit_key ][ $loop ] ) ) : '', // WPCS: CSRF ok.
					'_sale_price_per_unit'    => isset( $_POST[ $sale_price_per_unit_key ][ $loop ] ) ? wc_clean( wp_unslash( $_POST[ $sale_price_per_unit_key ][ $loop ] ) ) : '', // WPCS: CSRF ok.
				);

				$values = array(
					'_price_method_per_unit'  => isset( $_POST['variable_regular_price_per_unit'][ $loop ] ) ? wc_format_decimal( wc_clean( wp_unslash( $_POST['variable_regular_price_per_unit'][ $loop ] ) ) ) : '', // WPCS: CSRF ok.
					'_regular_price_per_unit' => isset( $_POST['variable_sale_price_per_unit'][ $loop ] ) ? wc_format_decimal( wc_clean( wp_unslash( $_POST['variable_regular_price_per_unit'][ $loop ] ) ) ) : '', // WPCS: CSRF ok.
				);

				self::save_per_unit_metadata( $variation_id, $data, $zone, $values );
			}
		}

		/**
		 * Save per unit metadata.
		 *
		 * @param int                $post_id The Post ID.
		 * @param array              $data Price per unit metadata.
		 * @param WCPBC_Pricing_Zone $zone Pricing_Zone object.
		 * @param array              $default_values Default price per unit values.
		 */
		private static function save_per_unit_metadata( $post_id, $data, $zone, $default_values = array() ) {
			$data = wp_parse_args( $data, array(
				'_price_method_per_unit'  => '',
				'_regular_price_per_unit' => '',
				'_sale_price_per_unit'    => '',
			) );

			$default_values = wp_parse_args( $default_values, array(
				'_regular_price_per_unit' => get_post_meta( $post_id, '_regular_price_per_unit', true ),
				'_sale_price_per_unit'    => get_post_meta( $post_id, '_sale_price_per_unit', true ),
			) );

			if ( wcpbc_is_exchange_rate( $data['_price_method_per_unit'] ) ) {
				$data['_regular_price_per_unit'] = $zone->get_exchange_rate_price( $default_values['_regular_price_per_unit'], false );
				$data['_sale_price_per_unit']    = $zone->get_exchange_rate_price( $default_values['_sale_price_per_unit'], false );

			} else {
				$data['_regular_price_per_unit'] = wc_format_decimal( $data['_regular_price_per_unit'] );
				$data['_sale_price_per_unit']    = wc_format_decimal( $data['_sale_price_per_unit'] );
			}

			$zone->set_postmeta( $post_id, '_price_method_per_unit', $data['_price_method_per_unit'] );
			$zone->set_postmeta( $post_id, '_regular_price_per_unit', $data['_regular_price_per_unit'] );
			$zone->set_postmeta( $post_id, '_sale_price_per_unit', $data['_sale_price_per_unit'] );
		}

		/**
		 * Add post metadata filter
		 */
		public static function frontend_princing_init() {
			add_filter( 'woocommerce_product_get__regular_price_per_unit', array( __CLASS__, 'get__regular_price_per_unit' ), 10, 2 );
			add_filter( 'woocommerce_product_get__sale_price_per_unit', array( __CLASS__, 'get__sale_price_per_unit' ), 10, 2 );
			add_filter( 'woocommerce_product_variation_get__v_regular_price_per_unit', array( __CLASS__, 'get__regular_price_per_unit' ), 10, 2 );
			add_filter( 'woocommerce_product_variation_get__v_sale_price_per_unit', array( __CLASS__, 'get__regular_price_per_unit' ), 10, 2 );
		}

		/**
		 * Return _regular_price_per_unit metadata
		 *
		 * @param string  $value Sign up fee value.
		 * @param WC_Data $data WC_Data object.
		 * @return mixed
		 */
		public static function get__regular_price_per_unit( $value, $data ) {
			return self::get_price_per_unit( $value, $data->get_id(), '_regular_price_per_unit' );
		}

		/**
		 * Return _sale_price_per_unit metadata
		 *
		 * @param string  $value Sign up fee value.
		 * @param WC_Data $data WC_Data object.
		 * @return mixed
		 */
		public static function get__sale_price_per_unit( $value, $data ) {
			return self::get_price_per_unit( $value, $data->get_id(), '_sale_price_per_unit' );
		}

		/**
		 * Return regular or sale price_per_unit metadata
		 *
		 * @param string $value Sign up fee value.
		 * @param int    $post_id WC_Data object.
		 * @param string $metadata_key _regular_price_per_unit|_sale_price_per_unit.
		 * @return mixed
		 */
		public static function get_price_per_unit( $value, $post_id, $metadata_key ) {
			$zone   = WCPBC()->current_zone;
			$_value = $value;
			if ( $zone->is_exchange_rate_price_per_unit( $post_id ) ) {
				$_value = $zone->get_exchange_rate_price( $value );
			} else {
				$_value = $zone->get_postmeta( $post_id, $metadata_key );
			}
			return $_value;
		}

		/**
		 * Add extra data to the AJAX geolocate array.
		 *
		 * @param array      $data Data to geolocate price.
		 * @param WC_Product $product Product object.
		 * @return array
		 */
		public static function ajax_geolocation_product_data( $data, $product ) {
			// Add price per unit.
			if ( is_callable( array( 'WGM_Price_Per_Unit', 'get_price_per_unit_string' ) ) ) {

				$data['price_per_unit_html'] = WGM_Price_Per_Unit::get_price_per_unit_string( $product );

				if ( $product->is_type( 'variation' ) && is_callable( array( 'WGM_Helper', 'prepare_variation_data' ) ) ) {
					$data = WGM_Helper::prepare_variation_data( $data, null, $product );
				}
			}

			// Taxes.
			if ( is_callable( array( 'WGM_Tax', 'text_including_tax' ) ) ) {
				$data['text_including_tax'] = WGM_Tax::text_including_tax( $product );
			}

			return $data;
		}

		/**
		 * Add extra classes to handle AJAX geolocation in German Market product sumary
		 *
		 * @param string     $output_html Output HTML.
		 * @param array      $output_parts Array of GM product sumary info.
		 * @param WC_Product $product Product instance.
		 */
		public static function product_summary_html( $output_html, $output_parts, $product ) {
			if ( ! is_callable( array( 'WCPBC_Ajax_Geolocation', 'is_enabled' ) ) || ! WCPBC_Ajax_Geolocation::is_enabled() || is_cart() || is_account_page() || is_checkout() || is_customize_preview() ) {
				return $output_html;
			}
			$output_html = str_replace( 'class="wgm-info ', 'class="wgm-info wgm-pbc-info wgm-pbc-info-' . $product->get_id() . ' ', $output_html );

			return $output_html;
		}

		/**
		 * Display admin minimun version required
		 */
		public static function min_version_notice() {
			// translators: 1: HTML tag, 2: HTML tag, 3: German Market version.
			$notice = sprintf( __( '%1$sPrice Based on Country Pro & German Market%2$s compatibility requires German Market version +3.8. You are running German Market %3$s.', 'wc-price-based-country-pro' ), '<strong>', '</strong>', Woocommerce_German_Market::$version );
			echo '<div id="message" class="error"><p>' . wp_kses_post( $notice ) . '</p></div>';
		}
	}

	if ( version_compare( Woocommerce_German_Market::$version, '3.8', '>=' ) ) {
		WCPBC_German_Market::init();
	} else {
		add_action( 'admin_notices', array( 'WCPBC_German_Market', 'min_version_notice' ) );
	}

endif;
