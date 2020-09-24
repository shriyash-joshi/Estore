<?php
/**
 * WC_PB_Order_Again class
 *
 * @author   SomewhereWarm <info@somewherewarm.com>
 * @package  WooCommerce Product Bundles
 * @since    5.8.1
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Order-again functions and filters.
 *
 * @class    WC_PB_Order_Again
 * @version  5.8.1
 */
class WC_PB_Order_Again {

	/*
	 * Initilize.
	 */
	public static function init() {

		// Put back cart item data to allow re-ordering of bundles.
		add_filter( 'woocommerce_order_again_cart_item_data', array( __CLASS__, 'order_again_cart_item_data' ), 10, 3 );

		if ( WC_PB_Core_Compatibility::is_wc_version_gte( '3.5' ) ) {

			// Initialize parent-child associations from order-again keys.
			add_filter( 'woocommerce_get_cart_item_from_session', array( __CLASS__, 'get_cart_item_from_session' ), -100, 3 );

			// Finalize parent-child associations from order-again keys.
			add_action( 'woocommerce_cart_loaded_from_session', array( __CLASS__, 'cart_loaded_from_session' ), -100 );
		}
	}

	/*
	|--------------------------------------------------------------------------
	| Filter hooks.
	|--------------------------------------------------------------------------
	*/

	/**
	 * Inialize cart item data when re-ordering.
	 * Depending on whether cart session data is loaded, a different technique is needed.
	 *
	 * @param  array     $cart_item
	 * @param  array     $order_item
	 * @param  WC_Order  $order
	 * @return array
	 */
	public static function order_again_cart_item_data( $cart_item, $order_item, $order ) {

		if ( wc_pb_is_bundle_container_order_item( $order_item ) ) {

			if ( ! $order_item->meta_exists( '_stamp' ) ) {
				return $cart_item;
			}

			$cart_item[ 'stamp' ]         = $order_item->get_meta( '_stamp', true );
			$cart_item[ 'bundled_items' ] = array();

			if ( WC_PB()->cart->is_cart_session_loaded() ) {

				// If this is part of a Composite, the Composite will add the Bundle to the cart.
				if ( WC_PB()->compatibility->is_composited_order_item( $order_item, $order ) ) {
					return $cart_item;
				}

			} else {

				$cart_id = $order_item->get_meta( '_bundle_cart_key', true );

				if ( ! empty( $cart_id ) ) {
					$cart_item[ 'order_again_bundle_cart_key' ] = $cart_id;
				}
			}

		} elseif ( wc_pb_is_bundled_order_item( $order_item, $order ) ) {

			$bundled_item_id = $order_item->get_meta( '_bundled_item_id', true );

			if ( WC_PB()->cart->is_cart_session_loaded() ) {

				if ( $bundled_item_id ) {

					$modified_cart = false;

					// Copy all cart data of the "orphaned" bundled cart item into the one already added by the container on 'woocommerce_add_to_cart'.
					foreach ( WC()->cart->cart_contents as $check_cart_item_key => $check_cart_item_data ) {

						if ( empty( $check_cart_item_data[ 'bundled_item_id' ] ) ) {
							continue;
						}

						if ( absint( $bundled_item_id ) !== absint( $check_cart_item_data[ 'bundled_item_id' ] ) ) {
							continue;
						}

						$existing_bundled_cart_item     = $check_cart_item_data;
						$existing_bundled_cart_item_key = $check_cart_item_key;

						foreach ( $cart_item as $key => $value ) {
							if ( ! isset( $existing_bundled_cart_item[ $key ] ) ) {
								WC()->cart->cart_contents[ $existing_bundled_cart_item_key ][ $key ] = $value;
								$modified_cart = true;
							}
						}
					}

					// Cart data changed? Recalculate totals and set session.
					if ( $modified_cart ) {
						WC()->cart->calculate_totals();
					}
				}

				// Identify this as a cart item that is originally part of a bundle. Will be removed since it has already been added to the cart by its container.
				$cart_item[ 'is_order_again_bundled' ] = 'yes';

			} else {

				if ( $bundled_item_id ) {
					$cart_item[ 'bundled_item_id' ] = $bundled_item_id;
				}

				$cart_id       = $order_item->get_meta( '_bundle_cart_key', true );
				$bundled_by    = $order_item->get_meta( '_bundled_by', true );
				$configuration = $order_item->get_meta( '_stamp', true );

				if ( ! empty( $cart_id ) ) {
					$cart_item[ 'order_again_bundle_cart_key' ] = $cart_id;
				}

				if ( ! empty( $bundled_by ) ) {
					$cart_item[ 'order_again_bundled_by' ] = $bundled_by;
				}

				if ( ! empty( $configuration ) ) {
					$cart_item[ 'stamp' ] = $configuration;
				}
			}

		}

		return $cart_item;
	}

	/**
	 * Initialize parent-child associations from order-again keys.
	 *
	 * @param  array  $cart_item
	 * @param  array  $cart_session_item
	 * @param  array  $cart_item_key
	 * @return array
	 */
	public static function get_cart_item_from_session( $cart_item, $cart_session_item, $cart_item_key ) {

		if ( ! did_action( 'woocommerce_ordered_again' ) ) {
			return $cart_item;
		}

		if ( ! empty( $cart_item[ 'order_again_bundled_by' ] ) ) {

			if ( empty( $cart_session_item[ 'order_again_bundle_cart_key' ] ) ) {
				return $cart_item;
			}

			foreach ( WC()->cart->cart_contents as $search_container_item_key => $search_container_item ) {

				if ( empty( $search_container_item[ 'order_again_bundle_cart_key' ] ) ) {
					continue;
				}

				if ( $cart_session_item[ 'order_again_bundled_by' ] === $search_container_item[ 'order_again_bundle_cart_key' ] ) {
					// Add reference to parent key in child.
					$cart_item[ 'bundled_by' ] = $search_container_item_key;
					// Break the search.
					break;
				}
			}

			// Clean up.
			unset( $cart_item[ 'order_again_bundle_cart_key' ] );
			unset( $cart_item[ 'order_again_bundled_by' ] );
		}

		return $cart_item;
	}

	/**
	 * Finalize parent-child associations from order-again keys.
	 *
	 * @param  WC_Cart  $cart
	 * @return void
	 */
	public static function cart_loaded_from_session( $cart ) {

		if ( ! did_action( 'woocommerce_ordered_again' ) ) {
			return;
		}

		if ( empty( $cart->cart_contents ) ) {
			return;
		}

		foreach ( $cart->cart_contents as $cart_item_key => $cart_item ) {

			if ( wc_pb_is_bundle_container_cart_item( $cart_item ) ) {

				foreach ( $cart->cart_contents as $search_child_key => $search_child_item ) {

					if ( ! wc_pb_maybe_is_bundled_cart_item( $search_child_item ) ) {
						continue;
					}

					if ( $search_child_item[ 'bundled_by' ] === $cart_item_key ) {

						// Add reference to child key in parent item.
						WC()->cart->cart_contents[ $cart_item_key ][ 'bundled_items' ][] = $search_child_key;
						// Invalidate session data.
						WC()->session->set( 'cart_totals', null );
					}
				}

				// Clean up.
				unset( WC()->cart->cart_contents[ $cart_item_key ][ 'order_again_bundle_cart_key' ] );
			}
		}
	}
}

WC_PB_Order_Again::init();
