<?php
/**
 * Integration with WooCommerce Subscriptions by Prospress.
 *
 * @package WCPBC
 * @version  2.8.9
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WCPBC_Subscriptions' ) ) {
	exit;
}

/**
 * WCPBC_Subscriptions class.
 */
class WCPBC_Subscriptions {

	/**
	 * Hook actions and filters.
	 */
	public static function init() {

		add_action( 'woocommerce_variable_product_sync_data', array( __CLASS__, 'unset_min_price_variation_id' ) );
		add_action( 'wc_price_based_country_frontend_princing_init', array( __CLASS__, 'frontend_princing_init' ) );
		add_filter( 'wc_price_based_country_parent_product_types', array( __CLASS__, 'parent_product_types' ) );
		add_filter( 'woocommerce_subscriptions_product_price_string', array( __CLASS__, 'product_price_string' ), 10, 2 );
		add_filter( 'woocommerce_get_cart_item_from_session', array( __CLASS__, 'get_cart_item_from_session' ), 100, 3 );
		add_action( 'woocommerce_checkout_create_order', array( __CLASS__, 'checkout_create_order' ), 1000 );

		if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
			// Admin.
			add_filter( 'wc_price_based_country_product_simple_fields', array( __CLASS__, 'product_simple_fields' ) );
			add_filter( 'wc_price_based_country_product_variation_fields', array( __CLASS__, 'product_variation_fields' ), 10, 2 );
			add_action( 'woocommerce_process_product_meta_subscription', array( __CLASS__, 'process_product_meta' ), 20 );
			add_action( 'woocommerce_save_product_variation', array( __CLASS__, 'process_variation_meta' ), 5, 2 );
			add_action( 'woocommerce_product_bulk_edit_save', array( __CLASS__, 'product_bulk_edit_save' ), 30 );
			add_action( 'woocommerce_bulk_edit_variations', array( __CLASS__, 'bulk_edit_variations' ), 30, 4 );
			add_action( 'wc_price_based_country_after_bulk_edit_variation', array( __CLASS__, 'after_bulk_edit_variation' ), 10, 3 );

			include_once dirname( __FILE__ ) . '/includes/class-wcpbc-subscription-reports.php';
		}

	}

	/**
	 * Remove the _min_price_variation_id metakey.
	 *
	 * @param WC_Product $product Product instance.
	 */
	public static function unset_min_price_variation_id( $product ) {
		$product_id = is_callable( array( $product, 'get_id' ) ) ? $product->get_id() : 0;

		if ( $product_id ) {
			foreach ( WCPBC_Pricing_Zones::get_zones() as $zone ) {
				$zone->set_postmeta( $product_id, '_min_price_variation_id', '' );
			}
		}
	}

	/**
	 * Add variable subscriptions product type
	 *
	 * @param array $types Array of parent product types.
	 * @return array
	 */
	public static function parent_product_types( $types ) {
		array_push( $types, 'variable-subscription' );
		return $types;
	}

	/**
	 * Frontend init hooks
	 *
	 * @since 1.0
	 */
	public static function frontend_princing_init() {
		add_filter( 'wc_price_based_country_product_types_overriden', array( __CLASS__, 'product_types_overriden' ) );
		add_filter( 'woocommerce_product_get__min_price_variation_id', array( __CLASS__, 'get_min_price_variation_id' ), 5, 2 );
		foreach ( array( '_subscription_sign_up_fee', '_subscription_price' ) as $prop ) {
			add_filter( 'woocommerce_product_get_' . $prop, array( __CLASS__, 'get_product_subscription_prop' ), 5, 2 );
			add_filter( 'woocommerce_product_variation_get_' . $prop, array( __CLASS__, 'get_product_subscription_prop' ), 5, 2 );
		}
	}

	/**
	 * Add subscription product types to the properties. WC 3.6 compatibility.
	 *
	 * @param array $types Array of product types.
	 */
	public static function product_types_overriden( $types ) {
		array_push( $types, 'variable-subscription', 'subscription', 'subscription_variation' );
		return $types;
	}

	/**
	 * Returns the min price variation ID.
	 *
	 * @param mixed      $value Property value.
	 * @param WC_Product $product Product instance.
	 */
	public static function get_min_price_variation_id( $value, $product ) {
		self::variable_subscription_product_sync( $product );

		$min_price_variation_id = wcpbc_the_zone()->get_postmeta( $product->get_id(), '_min_price_variation_id' );

		if ( empty( $min_price_variation_id ) ) {
			$min_price_variation_id = $value;
		}
		return $min_price_variation_id;
	}


	/**
	 * Returns a subscription property.
	 *
	 * @param mixed      $value Property value.
	 * @param WC_Product $product Product instance.
	 */
	public static function get_product_subscription_prop( $value, $product ) {
		if ( 'variable-subscription' === $product->get_type() ) {
			// Sync data.
			self::variable_subscription_product_sync( $product );
		}
		return WCPBC_Frontend_Pricing::get_product_price_property( $value, $product );
	}

	/**
	 * Sync the variable subscription prices with the childrens.
	 *
	 * @param WC_Product $product Product instance.
	 */
	private static function variable_subscription_product_sync( $product ) {
		$min_price_variation_id = wcpbc_the_zone()->get_postmeta( $product->get_id(), '_min_price_variation_id' );

		if ( empty( $min_price_variation_id ) && function_exists( 'wcs_get_min_max_variation_data' ) ) {

			$child_variation_ids = $product->get_visible_children();

			if ( $child_variation_ids ) {
				$min_max_data = wcs_get_min_max_variation_data( $product, $child_variation_ids );

				wcpbc_the_zone()->set_postmeta( $product->get_id(), '_min_price_variation_id', $min_max_data['min']['variation_id'] );
				wcpbc_the_zone()->set_postmeta( $product->get_id(), '_subscription_price', $min_max_data['min']['price'] );
				wcpbc_the_zone()->set_postmeta( $product->get_id(), '_subscription_sign_up_fee', $min_max_data['subscription']['signup-fee'] );
			}
		}
	}

	/**
	 * Add a wrapper for ajax geolocation
	 *
	 * @since 2.2.7
	 * @param string     $subscription_string Subscription price string.
	 * @param WC_Product $product Product instance.
	 */
	public static function product_price_string( $subscription_string, $product ) {

		if ( is_callable( array( 'WCPBC_Ajax_Geolocation', 'is_enabled' ) ) && WCPBC_Ajax_Geolocation::is_enabled() ) {
			$subscription_string = WCPBC_Ajax_Geolocation::wrapper_price( $product, $subscription_string );
		}

		return $subscription_string;
	}

	/**
	 * Apply the exchage rates to the renewal order product object.
	 *
	 * @since 2.3.3
	 * @param array  $cart_item_session_data Session data.
	 * @param array  $cart_item              Item values.
	 * @param string $key                   Current session data key.
	 * @return array
	 */
	public static function get_cart_item_from_session( $cart_item_session_data, $cart_item, $key ) {

		if ( ! empty( $cart_item_session_data['subscription_renewal']['renewal_order_id'] ) ) {

			$order = wc_get_order( $cart_item_session_data['subscription_renewal']['renewal_order_id'] );

			if ( $order ) {

				$order_currency   = $order->get_currency();
				$current_currency = WCPBC()->current_zone ? WCPBC()->current_zone->get_currency() : wcpbc_get_base_currency();

				if ( $order_currency !== $current_currency ) {

					$_product   = $cart_item_session_data['data'];
					$price      = $_product->get_price();
					$order_zone = WCPBC_Pricing_Zones::get_zone_from_order( $order );

					if ( $order_zone ) {
						if ( $order_zone->get_currency() !== $order_currency ) {

							$order_zone = new WCPBC_Pricing_Zone(
								array(
									'exchange_rate' => WCPBC_Update_Exchange_Rates::get_exchange_rate_from_api( $order_currency ),
									'currency'      => $order_currency,
								)
							);
						}

						$price = $order_zone->get_base_currency_amount( $price );
					}

					if ( WCPBC()->current_zone ) {
						$price = WCPBC()->current_zone->get_exchange_rate_price( $price );
					}

					// Set the product price.
					$_product->set_price( $price );
				}
			}
		}

		return $cart_item_session_data;
	}

	/**
	 * Fix order currency. WooCommerce Subscription copy metadata from the original subscription and overwrite the WooCommerce currency.
	 *
	 * @see WCS_Cart_Early_Renewal::copy_subscription_meta_to_order
	 * @param WC_Order $order The WC Order object.
	 *
	 * @since 2.6.3
	 */
	public static function checkout_create_order( $order ) {
		if ( $order->get_currency() !== get_woocommerce_currency() && function_exists( 'wcs_cart_contains_renewal' ) && wcs_cart_contains_renewal() ) {
			$order->set_currency( get_woocommerce_currency() );
			$order->calculate_totals();
		}
	}

	/**
	 * Add the subscription fields to product simple.
	 *
	 * @since 2.5
	 * @param array $fields Product simple fields.
	 * @return array
	 */
	public static function product_simple_fields( $fields ) {

		$fields[] = array(
			'name'              => '_subscription_price',
			'class'             => 'short wc_input_price wcpbc_input_subscription_price',
			'wrapper_class'     => 'wcpbc_show_if_manual_subscription',
			// translators: %s is a currency symbol.
			'label'             => __( 'Subscription Price (%s)', 'woocommerce-subscriptions' ),
			'placeholder'       => _x( 'e.g. 5.90', 'example price', 'woocommerce-subscriptions' ),
			'type'              => 'text',
			'custom_attributes' => array(
				'step' => 'any',
				'min'  => '0',
			),
		);

		$fields[] = array(
			'name'              => '_subscription_sign_up_fee',
			'class'             => 'short wc_input_price',
			'wrapper_class'     => 'wcpbc_show_if_manual_subscription',
			// translators: %s is a currency symbol.
			'label'             => __( 'Sign-up Fee (%s)', 'woocommerce-subscriptions' ),
			'placeholder'       => _x( 'e.g. 9.90', 'example price', 'woocommerce-subscriptions' ),
			'description'       => __( 'Optionally include an amount to be charged at the outset of the subscription. The sign-up fee will be charged immediately, even if the product has a free trial or the payment dates are synced.', 'woocommerce-subscriptions' ),
			'desc_tip'          => true,
			'type'              => 'text',
			'custom_attributes' => array(
				'step' => 'any',
				'min'  => '0',
			),
		);

		return $fields;
	}

	/**
	 * Add the subscription fields to product variation.
	 *
	 * @since 2.5
	 * @param array $fields Product simple fields.
	 * @param int   $loop Index of loop variation.
	 * @return array
	 */
	public static function product_variation_fields( $fields, $loop ) {
		$fields['_subscription_sign_up_fee'] = array(
			'name'          => "_variable_subscription_sign_up_fee[$loop]",
			// Translators: currency symbol.
			'label'         => __( 'Sign-up Fee (%s)', 'woocommerce-subscriptions' ),
			'wrapper_class' => 'form-row form-row-first wcpbc_show_if_manual_subscription',
		);
		$fields['_subscription_price']       = array(
			'name'          => "_variable_subscription_price[$loop]",
			'class'         => 'wcpbc_input_subscription_price',
			// Translators: currency symbol.
			'label'         => __( 'Subscription Price (%s)', 'woocommerce-subscriptions' ),
			'wrapper_class' => 'form-row form-row-last wcpbc_show_if_manual_subscription',
		);

		return $fields;
	}

	/**
	 * Save product metadata
	 *
	 * @param int $post_id Post ID.
	 */
	public static function process_product_meta( $post_id ) {
		// phpcs:disable WordPress.Security.NonceVerification
		$postdata = array(
			'subscription_price'       => isset( $_POST['_subscription_price'] ) ? wc_clean( wp_unslash( $_POST['_subscription_price'] ) ) : '', // WPCS: CSRF ok.
			'subscription_sign_up_fee' => isset( $_POST['_subscription_sign_up_fee'] ) ? wc_clean( wp_unslash( $_POST['_subscription_sign_up_fee'] ) ) : '', // WPCS: CSRF ok.
		);
		// phpcs:enable
		self::save_subscription_metadata( $post_id, $postdata );
		WCPBC_Admin_Meta_Boxes::process_product_meta( $post_id );
	}

	/**
	 * Save product metadata
	 *
	 * @param int $post_id Post ID.
	 * @param int $index Index of variations to save.
	 */
	public static function process_variation_meta( $post_id, $index ) {
		// phpcs:disable WordPress.Security.NonceVerification
		$product_type = isset( $_POST['product-type'] ) ? wc_clean( wp_unslash( $_POST['product-type'] ) ) : false;
		if ( 'variable-subscription' !== $product_type ) {
			return;
		}

		$postdata = array(
			'subscription_price'       => isset( $_POST['variable_subscription_price'][ $index ] ) ? wc_clean( wp_unslash( $_POST['variable_subscription_price'][ $index ] ) ) : '',
			'subscription_sign_up_fee' => isset( $_POST['variable_subscription_sign_up_fee'][ $index ] ) ? wc_clean( wp_unslash( $_POST['variable_subscription_sign_up_fee'][ $index ] ) ) : '',
		);
		// phpcs:enable

		self::save_subscription_metadata( $post_id, $postdata, $index );
	}

	/**
	 * Save subscription metada.
	 *
	 * @param int   $post_id Post ID.
	 * @param array $postdata Array with the subscription_price and subscription_sign_up_fee values.
	 * @param int   $index Index of variations to save.
	 */
	private static function save_subscription_metadata( $post_id, $postdata, $index = false ) {
		$variable          = false === $index ? '' : '_variable';
		$regular_price_key = $variable . '_regular_price';

		foreach ( WCPBC_Pricing_Zones::get_zones() as $zone ) {

			if ( wcpbc_is_exchange_rate( $zone->get_input_var( $variable . '_price_method', $index ) ) ) {

				$_subscription_price       = $zone->get_exchange_rate_price( $postdata['subscription_price'], false );
				$_subscription_sign_up_fee = $zone->get_exchange_rate_price( $postdata['subscription_sign_up_fee'], false );

			} else {

				$_subscription_price       = $zone->get_input_var( $variable . '_subscription_price', $index );
				$_subscription_sign_up_fee = $zone->get_input_var( $variable . '_subscription_sign_up_fee', $index );

				// Copy the subscription price to regular price.
				$_regular_price_key = $zone->get_postmetakey( $regular_price_key );
				if ( false === $index ) {
					$_POST[ $_regular_price_key ] = $_subscription_price;
				} else {
					$_POST[ $_regular_price_key ][ $index ] = $_subscription_price;
				}

				// Sanitize values.
				$_subscription_price       = wc_format_decimal( $_subscription_price );
				$_subscription_sign_up_fee = wc_format_decimal( $_subscription_sign_up_fee );
			}

			// Save metadata.
			$zone->set_postmeta( $post_id, '_subscription_price', $_subscription_price );
			$zone->set_postmeta( $post_id, '_subscription_sign_up_fee', $_subscription_sign_up_fee );
		}
	}

	/**
	 * Quick and Bulk product edit.
	 *
	 * @param WC_Product $product Product instance.
	 */
	public static function product_bulk_edit_save( $product ) {

		if ( ! is_callable( array( $product, 'get_type' ) ) || 'subscription' !== $product->get_type() ) {
			return;
		}
		foreach ( WCPBC_Pricing_Zones::get_zones() as $zone ) {
			if ( $zone->is_exchange_rate_price( $product->get_id() ) ) {
				$_subscription_price       = $zone->get_exchange_rate_price_by_post( $product->get_id(), '_subscription_price' );
				$_subscription_sign_up_fee = $zone->get_exchange_rate_price_by_post( $product->get_id(), '_subscription_sign_up_fee' );

				$zone->set_postmeta( $product->get_id(), '_subscription_price', $_subscription_price );
				$zone->set_postmeta( $product->get_id(), '_subscription_sign_up_fee', $_subscription_sign_up_fee );
			}
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
		$actions      = array( 'variable_subscription_sign_up_fee', 'variable_regular_price', 'variable_regular_price_increase', 'variable_regular_price_decrease' );
		$product_type = isset( $_POST['product_type'] ) ? wc_clean( wp_unslash( $_POST['product_type'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

		if ( ! ( in_array( $bulk_action, $actions, true ) && 'variable-subscription' === $product_type ) ) {
			return;
		}

		foreach ( WCPBC_Pricing_Zones::get_zones() as $zone ) {
			foreach ( $variations as $variation_id ) {
				if ( $zone->is_exchange_rate_price( $variation_id ) ) {
					if ( 'variable_subscription_sign_up_fee' === $bulk_action ) {
						$_subscription_sign_up_fee = $zone->get_exchange_rate_price_by_post( $variation_id, '_subscription_sign_up_fee' );
						$zone->set_postmeta( $variation_id, '_subscription_sign_up_fee', $_subscription_sign_up_fee );
					} else {
						// Copy the regular price to the subscription price.
						$_subscription_price = $zone->get_postmeta( $variation_id, '_regular_price' );
						$zone->set_postmeta( $variation_id, '_subscription_price', $_subscription_price );
					}
				}
			}
		}
	}

	/**
	 * After bulk editing variation pricing zone.
	 *
	 * @param int                $variation_id Variable product ID.
	 * @param WCPBC_Pricing_zone $zone Array of varations ID.
	 * @param string             $field Field edited.
	 */
	public static function after_bulk_edit_variation( $variation_id, $zone, $field ) {
		if ( '_regular_price' !== $field ) {
			return;
		}
		// Copy the regular price to the subscription price.
		$_subscription_price = $zone->get_postmeta( $variation_id, '_regular_price' );
		$zone->set_postmeta( $variation_id, '_subscription_price', $_subscription_price );
	}
}

WCPBC_Subscriptions::init();

