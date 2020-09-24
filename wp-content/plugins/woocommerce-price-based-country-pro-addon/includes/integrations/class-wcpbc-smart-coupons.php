<?php
/**
 * Handle integration with WooCommerce Smart Coupons.
 *
 * @see https://woocommerce.com/products/smart-coupons/
 * @version 2.8.3
 * @package WCPBC
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WCPBC_Smart_Coupons' ) ) :

	/**
	 *
	 * WCPBC_Smart_Coupons Class
	 */
	class WCPBC_Smart_Coupons {

		/**
		 * Array to store the coupon amounts by pricing zone.
		 *
		 * @var array
		 */
		private static $data = array();

		/**
		 * Current order ID.
		 *
		 * @var int
		 */
		private static $current_order_id = false;

		/**
		 * Current order index for order data.
		 *
		 * @var int
		 */
		private static $current_index = -1;

		/**
		 * Flag to remove the hooks for the smart coupon email.
		 *
		 * @var boolean
		 */
		private static $remove_coupon_email_hooks = false;

		/**
		 * Hook actions and filters
		 */
		public static function init() {
			add_filter( 'generate_smart_coupon_action', array( __CLASS__, 'generate_smart_coupon_action' ), -100, 5 );
			add_filter( 'generate_smart_coupon_action', array( __CLASS__, 'remove_smart_coupon_email_hooks' ), 100 );
			add_filter( 'woocommerce_email_order_items_args', array( __CLASS__, 'add_coupon_loaded_hook' ) );
			add_action( 'woocommerce_order_status_changed', array( __CLASS__, 'order_status_changed' ), 100, 3 );
			add_action( 'wc_smart_coupons_frontend_styles_and_scripts', array( __CLASS__, 'smart_coupon_frontend_scripts' ) );
			add_action( 'add_meta_boxes', array( __CLASS__, 'add_smart_coupon_order_meta_box' ), 11 );
		}

		/**
		 * Is a Name Your Price product?
		 *
		 * @param WC_Product $product Product object.
		 * @return bool
		 */
		private static function is_name_your_price( $product ) {
			return class_exists( 'WC_Name_Your_Price_Helpers' ) && is_callable( 'WC_Name_Your_Price_Helpers', 'is_nyp' ) && WC_Name_Your_Price_Helpers::is_nyp( $product );
		}

		/**
		 * Get the amount by the pricing zone from the product.
		 *
		 * @param WC_Product $product Product instance.
		 * @return array
		 */
		private static function get_amounts_from_product( $product ) {
			$amounts    = array();
			$add_filter = false;

			$amounts['base']  = $product->get_price( 'edit' );
			$amounts['zones'] = array();

			foreach ( WCPBC_Pricing_Zones::get_zones() as $zone ) {
				$amounts['zones'][ $zone->get_zone_id() ] = array(
					'_price_method' => $zone->get_postmeta( $product->get_id(), '_price_method' ),
					'coupon_amount' => $zone->get_postmeta( $product->get_id(), '_price' ),
				);
			}

			return $amounts;
		}

		/**
		 * Get the amount by the pricing zone from the parent coupon.
		 *
		 * @param int $coupon_id Coupon ID.
		 * @return array
		 */
		private static function get_amounts_from_coupon( $coupon_id ) {
			$amounts = array();

			$amounts['base']  = get_post_meta( $coupon_id, 'coupon_amount', true );
			$amounts['zones'] = array();

			foreach ( WCPBC_Pricing_Zones::get_zones() as $zone ) {
				$amounts['zones'][ $zone->get_zone_id() ] = array(
					'_price_method' => $zone->get_postmeta( $coupon_id, '_price_method' ),
					'coupon_amount' => $zone->get_postmeta( $coupon_id, 'coupon_amount' ),
				);
			}

			return $amounts;
		}

		/**
		 * Get the amount by the pricing zone when the coupon amount was set for the user.
		 *
		 * @see https://docs.woocommerce.com/document/smart-coupons/how-to-sell-gift-card-of-any-amount/
		 *
		 * @param double   $amount The amount set by the user.
		 * @param WC_Order $order Order instance.
		 * @return array
		 */
		private static function get_amounts_from_user( $amount, $order ) {
			$amounts = array();

			$order_zone = WCPBC_Pricing_Zones::get_zone_from_order( $order );
			if ( $order_zone ) {
				$order_zone_id   = $order_zone->get_zone_id();
				$amounts['base'] = round( $order_zone->get_base_currency_amount( $amount ), WCPBC_Frontend_Currency::base_num_decimals() );
			} else {
				$order_zone_id   = false;
				$amounts['base'] = $amount;
			}
			$amounts['zones'] = array();

			foreach ( WCPBC_Pricing_Zones::get_zones() as $zone ) {
				$amounts['zones'][ $zone->get_zone_id() ] = array(
					'_price_method' => ( $order_zone_id === $zone->get_zone_id() ? 'manual' : false ),
					'coupon_amount' => ( $order_zone_id === $zone->get_zone_id() ? $amount : $zone->get_exchange_rate_price( $amounts['base'] ) ),
				);
			}

			return $amounts;
		}

		/**
		 * Set the coupons amount by pricing zone and add the hooks to update the generate coupon.
		 *
		 * @see WC_Smart_Coupons::generate_smart_coupon_action()
		 *
		 * @param mixed     $email The email address.
		 * @param float     $amount The amount.
		 * @param int       $order_id The order id.
		 * @param WC_Coupon $coupon The coupon object.
		 * @param string    $discount_type The discount type.
		 */
		public static function generate_smart_coupon_action( $email, $amount, $order_id, $coupon, $discount_type ) {
			if ( $order_id && 'smart_coupon' === $discount_type ) {

				$ref_coupon_id = $coupon->get_id();
				$order         = wc_get_order( $order_id );

				if ( ! isset( self::$data[ $order_id ] ) ) {
					self::$current_index = -1; // Init the index.

					$sc_called_credit_details = get_post_meta( $order_id, 'sc_called_credit_details', true );

					$order_items = (array) $order->get_items();
					$data        = array();

					if ( count( $order_items ) > 0 ) {

						foreach ( $order_items as $item_id => $item ) {

							$product       = $order->get_product_from_item( $item );
							$product_type  = ( is_object( $product ) && is_callable( array( $product, 'get_type' ) ) ) ? $product->get_type() : '';
							$product_id    = ( in_array( $product_type, array( 'variable', 'variable-subscription', 'variation', 'subscription_variation' ), true ) ) ? ( ( is_object( $product ) && is_callable( array( $product, 'get_parent_id' ) ) ) ? $product->get_parent_id() : 0 ) : ( ( is_object( $product ) && is_callable( array( $product, 'get_id' ) ) ) ? $product->get_id() : 0 );
							$coupon_titles = get_post_meta( $product_id, '_coupon_title', true );

							if ( $coupon_titles && is_array( $coupon_titles ) ) {
								foreach ( $coupon_titles as $coupon_title ) {
									$coupon    = new WC_Coupon( $coupon_title );
									$coupon_id = $coupon->get_id();
									if ( empty( $coupon_id ) ) {
										continue;
									}
									if ( 'yes' === get_post_meta( $coupon_id, 'is_pick_price_of_product', true ) && $product->get_price() >= 0 ) {
										if ( $product->get_price() && ! self::is_name_your_price( $product ) ) {

											$data[ $coupon_id ][] = self::get_amounts_from_product( $product );

										} elseif ( isset( $sc_called_credit_details[ $item_id ] ) ) {

											$data[ $coupon_id ][] = self::get_amounts_from_user( $sc_called_credit_details[ $item_id ], $order );

										}
									} else {
										$data[ $coupon_id ][] = self::get_amounts_from_coupon( $coupon_id );
									}
								}
							}
						}
					}

					self::$data[ $order_id ] = $data;
				}

				if ( ! empty( self::$data[ $order_id ][ $ref_coupon_id ] ) ) {
					self::$current_order_id = $order_id;
					self::$current_index++;

					add_action( 'wc_sc_new_coupon_generated', array( __CLASS__, 'coupon_generated' ) );

					// Display the correct coupon amount and currency in the smart coupon email.
					if ( 'yes' === get_option( 'smart_coupons_is_send_email', 'yes' ) ) {
						self::add_smart_coupon_email_hooks( $order );
					}
				}
			}
			return $email;
		}

		/**
		 * Update the coupon amount.
		 *
		 * @param int $args Arguments array.
		 */
		public static function coupon_generated( $args ) {

			$coupon_id     = $args['new_coupon_id'];
			$ref_coupon    = $args['ref_coupon'];
			$ref_coupon_id = $ref_coupon->get_id();

			if ( self::$current_order_id && isset( self::$data[ self::$current_order_id ][ $ref_coupon_id ][ self::$current_index ] ) ) {
				$amounts = self::$data[ self::$current_order_id ][ $ref_coupon_id ][ self::$current_index ];

				update_post_meta( $coupon_id, 'coupon_amount', $amounts['base'] );
				update_post_meta( $coupon_id, '_wcpbc_base_amount', $amounts['base'] );

				foreach ( WCPBC_Pricing_Zones::get_zones() as $zone ) {
					if ( isset( $amounts['zones'][ $zone->get_zone_id() ] ) ) {
						foreach ( $amounts['zones'][ $zone->get_zone_id() ] as $key => $value ) {
							$zone->set_postmeta( $coupon_id, $key, $value );
						}
					}
				}

				// Set flags for coupon.
				update_post_meta( $coupon_id, '_wcpbc_pro_version', WC_Product_Price_Based_Country_Pro::$version );
				update_post_meta( $coupon_id, '_wcpbc_smart_coupon', 'yes' );
			}

			remove_action( 'wc_sc_new_coupon_generated', array( __CLASS__, 'coupon_generated' ) );
		}

		/**
		 * Set coupon amount for smart coupon for the emails.
		 *
		 * @param WC_Coupon $coupon Coupon instance.
		 */
		public static function coupon_loaded( $coupon ) {
			$coupon_id = $coupon->get_id();

			if ( 'yes' === get_post_meta( $coupon_id, '_wcpbc_smart_coupon', true ) ) {

				$zone = WCPBC_Pricing_Zones::get_zone_from_order( self::$current_order_id );

				if ( ! $zone ) {
					$amount = get_post_meta( $coupon_id, 'coupon_amount', true );
				} else {
					$amount = $zone->get_post_price( $coupon_id, 'coupon_amount', 'coupon' );
				}
				$coupon->set_amount( $amount );
			}
		}

		/**
		 * Add the coupon loaded hook to display the correct coupon amount in WooCommerce emails.
		 *
		 * @param array $args Order items args.
		 * @return array
		 */
		public static function add_coupon_loaded_hook( $args ) {
			if ( isset( $args['order'] ) ) {
				self::$current_order_id = $args['order']->get_id();
				add_action( 'woocommerce_coupon_loaded', array( __CLASS__, 'coupon_loaded' ), 30 );
				add_filter( 'woocommerce_mail_content', array( __CLASS__, 'remove_coupon_loaded_hook' ) );
			}

			return $args;
		}

		/**
		 * Remove the coupon loaded hook.
		 *
		 * @param string $value Value to return when is bind to a filter.
		 */
		public static function remove_coupon_loaded_hook( $value = '' ) {
			remove_action( 'woocommerce_coupon_loaded', array( __CLASS__, 'coupon_loaded' ), 30 );
			remove_filter( 'woocommerce_mail_content', array( __CLASS__, 'remove_coupon_loaded_hook' ) );
			return $value;
		}

		/**
		 * Set zone data and add hooks to display correct amount and currency in coupons template.
		 *
		 * @param int|WC_order $order The order.
		 */
		private static function add_smart_coupon_email_hooks( $order ) {
			self::$remove_coupon_email_hooks = true;

			WCPBC_Frontend_Currency::email_order_zone_data(
				array(
					'order' => $order,
				)
			);

			add_action( 'woocommerce_coupon_loaded', array( __CLASS__, 'coupon_loaded' ), 30 );
		}

		/**
		 * Clear email zone data and hooks.
		 *
		 * @param string $value Return the value.
		 * @return string
		 */
		public static function remove_smart_coupon_email_hooks( $value = '' ) {
			if ( self::$remove_coupon_email_hooks ) {
				self::$remove_coupon_email_hooks = false;
				self::remove_coupon_loaded_hook();
				WCPBC_Frontend_Currency::clear_email_order_zone_data();
			}
			return $value;
		}

		/**
		 * Update Store Credit balance.
		 *
		 * @since 2.8.6
		 * @param integer $order_id   The order id.
		 * @param string  $old_status Old order status.
		 * @param string  $new_status New order status.
		 */
		public static function order_status_changed( $order_id, $old_status, $new_status ) {
			$order = wc_get_order( $order_id );
			if ( ! $order ) {
				return;
			}

			$action = false;
			if ( in_array( $new_status, array( 'on-hold', 'processing', 'completed' ), true ) && in_array( $old_status, array( 'pending', 'failed' ), true ) ) {
				$action = 'update';
			} elseif ( in_array( $new_status, array( 'pending', 'refunded', 'cancelled', 'failed' ), true ) && in_array( $old_status, array( 'on-hold', 'processing', 'completed' ), true ) ) {
				$action = 'restore';
			}

			if ( $action ) {
				$order_zone      = WCPBC_Pricing_Zones::get_zone_from_order( $order );
				$coupons         = is_callable( array( $order, 'get_coupon_codes' ) ) ? $order->get_coupon_codes() : $order->get_used_coupons();
				$sc_contribution = $order->get_meta( 'smart_coupons_contribution' );

				if ( is_array( $sc_contribution ) && ! empty( $sc_contribution ) && is_array( $coupons ) ) {
					foreach ( $coupons as $code ) {
						if ( ! array_key_exists( $code, $sc_contribution ) ) {
							continue;
						}
						$coupon      = new WC_Coupon( $code );
						$coupon_id   = $coupon->get_id();
						$base_amount = get_post_meta( $coupon_id, '_wcpbc_base_amount', true );

						if ( is_numeric( $base_amount ) && 'smart_coupon' === $coupon->get_discount_type() ) {
							self::update_credit_balance( $coupon_id, $base_amount, $sc_contribution[ $code ], $order_zone, ( 'restore' === $action ? -1 : 1 ) );
						}
					}
				}
			}
		}

		/**
		 * Decrease credit balance.
		 *
		 * @param int                $coupon_id Coupon ID.
		 * @param double             $base_amount Coupon base amount.
		 * @param double             $sc_contribution Smart Coupon contribution amount.
		 * @param WCBPC_Pricing_Zone $order_zone Pricing zone of the order.
		 * @param int                $operator 1: Decrease balance. -1: Increment balance.
		 */
		private static function update_credit_balance( $coupon_id, $base_amount, $sc_contribution, $order_zone, $operator = 1 ) {

			$base_contribution = $order_zone ? $order_zone->get_base_currency_amount( $sc_contribution ) : $sc_contribution;
			$credit_remaining  = max( 0, round( $base_amount - ( $base_contribution * $operator ), WCPBC_Frontend_Currency::base_num_decimals() ) );

			update_post_meta( $coupon_id, '_wcpbc_base_amount', $credit_remaining );
			update_post_meta( $coupon_id, 'coupon_amount', $credit_remaining );

			if ( $credit_remaining <= 0 ) {
				foreach ( WCPBC_Pricing_Zones::get_zones() as $zone ) {
					if ( ! $zone->is_exchange_rate_price( $coupon_id ) ) {
						$zone->set_postmeta( $coupon_id, 'coupon_amount', 0 );
					}
				}
			} else {
				foreach ( WCPBC_Pricing_Zones::get_zones() as $zone ) {
					if ( ! $zone->is_exchange_rate_price( $coupon_id ) ) {
						if ( ! $zone->is_exchange_rate_price( $coupon_id ) ) {
							if ( $order_zone && $order_zone->get_zone_id() === $zone->get_zone_id() ) {
								$zone_amount = $zone->get_postmeta( $coupon_id, 'coupon_amount' ) - ( $sc_contribution * $operator );
							} else {
								$zone_amount = $zone->get_exchange_rate_price( $credit_remaining );
							}
							$zone_amount = max( 0, round( $zone_amount, $zone->get_price_num_decimals() ) );
							$zone->set_postmeta( $coupon_id, 'coupon_amount', $zone_amount );
						}
					}
				}
			}
		}

		/**
		 * Smart coupon frontend scripts.
		 */
		public static function smart_coupon_frontend_scripts() {
			if ( is_checkout() || is_cart() ) {
				add_filter( 'woocommerce_queued_js', array( __CLASS__, 'fix_smart_coupon_scripts' ) );
			}
		}

		/**
		 * Fix bug on smart coupon script to prevent double available coupons refresh.
		 *
		 * @param string $js The queued JavaScript.
		 */
		public static function fix_smart_coupon_scripts( $js ) {
			return str_replace( "jQuery('div#coupons_list').replaceWith( response );", "//jQuery('div#coupons_list').replaceWith( response );", $js );
		}

		/**
		 * Add the smart coupon details order meta_box
		 */
		public static function add_smart_coupon_order_meta_box() {
			global $post;
			if ( 'shop_order' === $post->post_type && class_exists( 'WC_SC_Display_Coupons' ) && is_callable( array( 'WC_SC_Display_Coupons', 'get_instance' ) ) && is_callable( array( WC_SC_Display_Coupons::get_instance(), 'sc_generated_coupon_data_metabox' ) ) ) {
				add_meta_box( 'wcpbc-sc-generated-coupon-data', __( 'Coupon Sent', 'woocommerce-smart-coupons' ), array( __CLASS__, 'smart_coupon_order_meta_box' ), 'shop_order', 'normal' ); // phpcs:ignore
				remove_meta_box( 'sc-generated-coupon-data', 'shop_order', 'normal' );
			}
		}

		/**
		 * Display the Price Based on Country Coupon Sent metabox.
		 */
		public static function smart_coupon_order_meta_box() {
			global $post;
			if ( ! empty( $post->ID ) ) {
				self::$current_order_id = $post->ID;
				self::add_smart_coupon_email_hooks( $post->ID );
				WC_SC_Display_Coupons::get_instance()->sc_generated_coupon_data_metabox();
				self::remove_smart_coupon_email_hooks();
			}
		}

		/**
		 * Checks the environment for compatibility problems.
		 *
		 * @return boolean
		 */
		public static function check_environment() {
			if ( version_compare( WC_VERSION, '3.0', '<' ) ) {
				// translators: 1: HTML tag, 2: HTML tag, 3: WooCommerce version.
				add_action( 'admin_notices', array( __CLASS__, 'min_version_notice' ) );
				return false;
			}

			return true;
		}

		/**
		 * Display admin minimun version required
		 */
		public static function min_version_notice() {
			// translators: 1: HTML tag, 2: HTML tag, 3: WooCommerce version.
			$notice = sprintf( __( '%1$sPrice Based on Country Pro & WooCommerce Smart Coupons%2$s compatibility requires WooCommerce +3.0. You are running WooCommerce %3$s.', 'wc-price-based-country-pro' ), '<strong>', '</strong>', WC_VERSION );
			echo '<div id="message" class="error"><p>' . wp_kses_post( self::$notice ) . '</p></div>';
		}
	}

	if ( WCPBC_Smart_Coupons::check_environment() ) {
		WCPBC_Smart_Coupons::init();
	}

endif;
