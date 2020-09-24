<?php
/**
 * WooCommerce Price Based on Country admin metaboxes
 *
 * @package WCPBC
 * @version 1.8.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * WCPBC_Admin_Product_Data Class
 */
class WCPBC_Admin_Meta_Boxes {

	/**
	 * Init hooks
	 */
	public static function init() {

		add_action( 'woocommerce_product_options_general_product_data', array( __CLASS__, 'options_general_product_data' ) );
		add_action( 'woocommerce_product_after_variable_attributes', array( __CLASS__, 'after_variable_attributes' ), 10, 3 );
		add_action( 'woocommerce_process_product_meta_simple', array( __CLASS__, 'process_product_meta' ) );
		add_action( 'woocommerce_process_product_meta_external', array( __CLASS__, 'process_product_meta' ) );
		add_action( 'woocommerce_save_product_variation', array( __CLASS__, 'process_product_meta' ), 10, 2 );
		add_action( 'woocommerce_product_quick_edit_save', array( __CLASS__, 'product_quick_edit_save' ) );
		add_action( 'woocommerce_product_bulk_edit_save', array( __CLASS__, 'product_quick_edit_save' ), 20 );
		add_action( 'woocommerce_product_import_inserted_product_object', array( __CLASS__, 'import_inserted_product_object' ), 20, 2 );
		add_action( 'woocommerce_bulk_edit_variations', array( __CLASS__, 'bulk_edit_variations' ), 20, 4 );
		add_action( 'woocommerce_coupon_options', array( __CLASS__, 'coupon_options' ) );
		add_action( 'woocommerce_coupon_options_save', array( __CLASS__, 'coupon_options_save' ) );
	}

	/**
	 * Output the zone pricing for simple products
	 */
	public static function options_general_product_data() {
		$wrapper_class = array( 'options_group', 'show_if_simple', 'show_if_external' );
		if ( ! wcpbc_is_pro() ) {
			foreach ( array_keys( wcpbc_product_types_supported( 'pro', 'product-data' ) ) as $product_type ) {
				$wrapper_class[] = 'hide_if_' . $product_type;
			}
		}

		$field = array(
			'wrapper_class' => implode( ' ', $wrapper_class ),
			'fields'        => array_merge(
				array(
					array(
						'name'  => '_regular_price',
						// Translators: currency symbol.
						'label' => __( 'Regular price (%s)', 'woocommerce-product-price-based-on-countries' ),
					),
					array(
						'name'  => '_sale_price',
						// Translators: currency symbol.
						'label' => __( 'Sale price (%s)', 'woocommerce-product-price-based-on-countries' ),
						'class' => 'wcpbc_sale_price',
					),
					array(
						'name'          => '_sale_price_dates',
						'type'          => 'radio',
						'default_value' => 'default',
						'class'         => 'wcpbc_sale_price_dates',
						'label'         => __( 'Sale price dates', 'woocommerce-product-price-based-on-countries' ),
						'options'       => array(
							'default' => __( 'Same as default price', 'woocommerce-product-price-based-on-countries' ),
							'manual'  => __( 'Set specific dates', 'woocommerce-product-price-based-on-countries' ),
						),
					),
					array(
						'name'          => '_sale_price_dates_from',
						'label'         => '',
						'data_type'     => 'date',
						'class'         => 'sale_price_dates_from',
						'wrapper_class' => 'sale_price_dates_fields wcpbc_hide_if_sale_dates_default',
						'placeholder'   => _x( 'From&hellip;', 'placeholder', 'woocommerce-product-price-based-on-countries' ) . ' YYYY-MM-DD',
					),
					array(
						'name'          => '_sale_price_dates_to',
						'label'         => '',
						'data_type'     => 'date',
						'class'         => 'sale_price_dates_to',
						'wrapper_class' => 'sale_price_dates_fields wcpbc_hide_if_sale_dates_default',
						'placeholder'   => _x( 'To&hellip;', 'placeholder', 'woocommerce-product-price-based-on-countries' ) . ' YYYY-MM-DD',
					),
				),
				apply_filters( 'wc_price_based_country_product_simple_fields', array() )
			),
		);

		// Output the input control.
		foreach ( WCPBC_Pricing_Zones::get_zones() as $zone ) {
			wcpbc_pricing_input( $field, $zone );
		}
	}

	/**
	 * Output the zone pricing for variations
	 *
	 * @param int                  $loop Variations loop index.
	 * @param array                $variation_data Array of variation data @deprecated.
	 * @param WC_Product_Variation $variation The variation product instance.
	 */
	public static function after_variable_attributes( $loop, $variation_data, $variation ) {
		$post_id = $variation->ID;
		$field   = array(
			'name'          => "_variable_price_method[$loop]",
			'wrapper_class' => wcpbc_is_pro() ? '' : 'hide_if_variable-subscription hide_if_nyp-wcpbc',
			'fields'        => array_merge(
				array(
					'_regular_price'         => array(
						'name'          => "_variable_regular_price[$loop]",
						// Translators: currency symbol.
						'label'         => __( 'Regular price (%s)', 'woocommerce-product-price-based-on-countries' ),
						'wrapper_class' => 'form-row form-row-first _variable_regular_price_wcpbc_field',
					),
					'_sale_price'            => array(
						'name'          => "_variable_sale_price[$loop]",
						// Translators: currency symbol.
						'label'         => __( 'Sale price (%s)', 'woocommerce-product-price-based-on-countries' ),
						'class'         => 'wcpbc_sale_price',
						'wrapper_class' => 'form-row form-row-last _variable_sale_price_wcpbc_field',
					),
					'_sale_price_dates'      => array(
						'name'          => "_variable_sale_price_dates[$loop]",
						'type'          => 'radio',
						'class'         => 'wcpbc_sale_price_dates',
						'wrapper_class' => 'wcpbc_sale_price_dates_wrapper',
						'default_value' => 'default',
						'label'         => __( 'Sale price dates', 'woocommerce-product-price-based-on-countries' ),
						'options'       => array(
							'default' => __( 'Same as default price', 'woocommerce-product-price-based-on-countries' ),
							'manual'  => __( 'Set specific dates', 'woocommerce-product-price-based-on-countries' ),
						),
					),
					'_sale_price_dates_from' => array(
						'name'          => "_variable_sale_price_dates_from[$loop]",
						'label'         => __( 'Sale start date', 'woocommerce-product-price-based-on-countries' ),
						'data_type'     => 'date',
						'class'         => 'sale_price_dates_from',
						'wrapper_class' => 'form-row form-row-first sale_price_dates_fields wcpbc_hide_if_sale_dates_default',
						'placeholder'   => _x( 'From&hellip;', 'placeholder', 'woocommerce-product-price-based-on-countries' ) . ' YYYY-MM-DD',
					),
					'_sale_price_dates_to'   => array(
						'name'          => "_variable_sale_price_dates_to[$loop]",
						'label'         => __( 'Sale end date', 'woocommerce-product-price-based-on-countries' ),
						'data_type'     => 'date',
						'class'         => 'sale_price_dates_to',
						'wrapper_class' => 'form-row form-row-last sale_price_dates_fields wcpbc_hide_if_sale_dates_default',
						'placeholder'   => _x( 'To&hellip;', 'placeholder', 'woocommerce-product-price-based-on-countries' ) . ' YYYY-MM-DD',
					),
				),
				apply_filters( 'wc_price_based_country_product_variation_fields', array(), $loop )
			),
		);

		// Output the input control.
		foreach ( WCPBC_Pricing_Zones::get_zones() as $zone ) {

			$field['value'] = $zone->get_postmeta( $post_id, '_price_method' );

			foreach ( $field['fields'] as $key => $field_data ) {
				$field['fields'][ $key ]['value'] = $zone->get_postmeta( $post_id, $key );
			}

			wcpbc_pricing_input( $field, $zone );
		}
	}

	/**
	 * Save product metadata
	 *
	 * @param int $post_id Post ID.
	 * @param int $index Index of variations to save.
	 */
	public static function process_product_meta( $post_id, $index = false ) {
		$fields = array( '_price_method', '_regular_price', '_sale_price', '_sale_price_dates', '_sale_price_dates_from', '_sale_price_dates_to' );
		foreach ( WCPBC_Pricing_Zones::get_zones() as $zone ) {
			$data = array();
			foreach ( $fields as $field ) {
				$var_name       = false !== $index ? '_variable' . $field : $field;
				$data[ $field ] = $zone->get_input_var( $var_name, $index );
			}

			// Save metadata.
			wcpbc_update_product_pricing( $post_id, $zone, $data );
		}
	}

	/**
	 * Quick and Bulk product edit.
	 *
	 * @param WC_Product $product Product instance.
	 */
	public static function product_quick_edit_save( $product ) {
		foreach ( WCPBC_Pricing_Zones::get_zones() as $zone ) {
			if ( $zone->is_exchange_rate_price( $product->get_id() ) ) {

				wcpbc_update_product_pricing( $product->get_id(), $zone );
			}
		}
	}

	/**
	 * Update exchange rate prices after process the CSV import.
	 *
	 * @param WC_Product $product Product being imported or updated.
	 * @param array      $data CSV data read for the product.
	 */
	public static function import_inserted_product_object( $product, $data ) {
		if ( in_array( $product->get_type(), WCPBC_Product_Sync::get_parent_product_types(), true ) ) {
			return;
		}

		$default_price_keys = array_intersect( array( 'regular_price', 'sale_price', 'date_on_sale_from', 'date_on_sale_to' ), array_keys( $data ) );
		if ( ! empty( $default_price_keys ) ) {
			self::product_quick_edit_save( $product );
		}
	}

	/**
	 * Bulk edit variations via AJAX.
	 *
	 * @param string $bulk_action Variation bulk action.
	 * @param array  $data Sanitized post data.
	 * @param int    $product_id Variable product ID.
	 * @param array  $variations Array of varations ID.
	 */
	public static function bulk_edit_variations( $bulk_action, $data, $product_id, $variations ) {
		$actions = array( 'variable_regular_price', 'variable_sale_price', 'variable_sale_schedule', 'variable_regular_price_increase', 'variable_regular_price_decrease', 'variable_sale_price_increase', 'variable_sale_price_decrease' );

		if ( ! in_array( $bulk_action, $actions, true ) ) {
			return;
		}

		foreach ( WCPBC_Pricing_Zones::get_zones() as $zone ) {
			foreach ( $variations as $variation_id ) {
				if ( $zone->is_exchange_rate_price( $variation_id ) ) {
					wcpbc_update_product_pricing( $variation_id, $zone );
				}
			}
		}
	}

	/**
	 * Display coupon amount options.
	 *
	 * @since 1.6
	 */
	public static function coupon_options() {
		woocommerce_wp_checkbox(
			array(
				'id'          => 'zone_pricing_type',
				'cbvalue'     => 'exchange_rate',
				'label'       => __( 'Calculate amount by exchange rate', 'woocommerce-product-price-based-on-countries' ),
				// Translators: HTML tags.
				'description' => sprintf( __( 'Check this box if, for the pricing zones, the coupon amount must be calculated using the exchange rate. %1$s(%2$sUpgrade to Price Based on Country Pro to set copupon amount by zone%3$s)', 'woocommerce-product-price-based-on-countries' ), '<br />', '<a target="_blank" el="noopener noreferrer" href="https://www.pricebasedcountry.com/pricing/?utm_source=coupon&utm_medium=banner&utm_campaign=Get_Pro">', '</a>' ),
			)
		);
	}

	/**
	 * Save coupon amount options.
	 *
	 * @since 1.6
	 * @param int $post_id Post ID.
	 */
	public static function coupon_options_save( $post_id ) {
		$discount_type     = empty( $_POST['discount_type'] ) ? 'fixed_cart' : wc_clean( wp_unslash( $_POST['discount_type'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
		$zone_pricing_type = in_array( $discount_type, array( 'fixed_cart', 'fixed_product' ), true ) && isset( $_POST['zone_pricing_type'] ) ? 'exchange_rate' : 'nothig'; // phpcs:ignore WordPress.Security.NonceVerification
		update_post_meta( $post_id, 'zone_pricing_type', $zone_pricing_type );
	}
}
