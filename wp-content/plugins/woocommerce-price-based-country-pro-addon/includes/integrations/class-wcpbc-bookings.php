<?php
/**
 * Handle integration with WooCommerce Bookings by WooCommerce.
 *
 * @version 2.4.11
 * @package WCPBC
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WCPBC_Bookings' ) ) :

	/**
	 *
	 * WCPBC_Bookings Class
	 */
	class WCPBC_Bookings {

		/**
		 * Admin data to handle resource admin output.
		 *
		 * @var array
		 */
		private static $admin_data = null;

		/**
		 * Hook actions and filters
		 */
		public static function init() {
			add_filter( 'wc_price_based_country_price_meta_keys', array( __CLASS__, 'booking_cost_meta_keys' ) );
			add_action( 'wc_price_based_country_frontend_princing_init', array( __CLASS__, 'pricing_init' ) );
			add_filter( 'woocommerce_product_data_tabs', array( __CLASS__, 'register_tab' ), 20 );
			add_action( 'woocommerce_product_data_panels', array( __CLASS__, 'init_admin_data' ), 1 );
			add_action( 'woocommerce_bookings_after_resource_block_cost', array( __CLASS__, 'after_resource_block_cost' ), 10, 2 );
			add_action( 'woocommerce_bookings_after_person_max_column', array( __CLASS__, 'after_person_max_column' ) );
			add_action( 'woocommerce_product_data_panels', array( __CLASS__, 'booking_panels' ) );
			add_action( 'woocommerce_process_product_meta_booking', array( __CLASS__, 'save_data' ) );
			add_filter( 'woocommerce_add_cart_item_data', array( __CLASS__, 'add_cart_item_data' ), 11, 2 );
			add_filter( 'woocommerce_get_cart_item_from_session', array( __CLASS__, 'get_cart_item_from_session' ), 11, 3 );
		}

		/**
		 * Add booking product cost meta keys
		 *
		 * @param array $meta_keys Metadata keys.
		 * @return array
		 */
		public static function booking_cost_meta_keys( $meta_keys ) {
			array_push( $meta_keys, '_wc_booking_base_cost', '_wc_booking_cost', '_wc_display_cost', '_wc_booking_block_cost' );
			return $meta_keys;
		}

		/**
		 * Add filter needed to pricing zone
		 */
		public static function pricing_init() {
			add_filter( 'woocommerce_product_get_pricing', array( __CLASS__, 'get_pricing' ), 10, 2 );
			add_filter( 'woocommerce_product_get_resource_base_costs', array( __CLASS__, 'get_resource_base_costs' ), 10, 2 );
			add_filter( 'woocommerce_product_get_resource_block_costs', array( __CLASS__, 'get_resource_block_costs' ), 10, 2 );
			add_filter( 'woocommerce_product_booking_person_type_get_cost', array( __CLASS__, 'get_person_type_cost' ), 10, 2 );
			add_filter( 'woocommerce_product_booking_person_type_get_block_cost', array( __CLASS__, 'get_person_type_block_cost' ), 10, 2 );
		}

		/**
		 * Return booking product pricing prop
		 *
		 * @param array      $value Booking pricing.
		 * @param WC_Product $product Product instance.
		 * @return array
		 */
		public static function get_pricing( $value, $product ) {

			if ( 'WC_Product_Booking' === get_class( $product ) ) {
				$pricing          = WCPBC()->current_zone->get_postmeta( $product->get_id(), '_pricing' );
				$is_exchange_rate = WCPBC()->current_zone->is_exchange_rate_price( $product->get_id() );

				foreach ( $value as $i => $data ) {

					if ( $is_exchange_rate ) {
						// Exchange rate.
						if ( ! in_array( $data['modifier'], array( 'times', 'divide' ), true ) ) {
							$value[ $i ]['cost']      = WCPBC()->current_zone->get_exchange_rate_price( $data['cost'] );
							$value[ $i ]['base_cost'] = WCPBC()->current_zone->get_exchange_rate_price( $data['base_cost'] );
						}
					} else {
						$value[ $i ]['cost']      = isset( $pricing[ $i ]['cost'] ) ? $pricing[ $i ]['cost'] : '';
						$value[ $i ]['base_cost'] = isset( $pricing[ $i ]['base_cost'] ) ? $pricing[ $i ]['base_cost'] : '';
					}
				}
			}

			return $value;
		}

		/**
		 * Return booking product resource base cost
		 *
		 * @param array      $value Resource base cost.
		 * @param WC_Product $product Product instance.
		 * @return array
		 */
		public static function get_resource_base_costs( $value, $product ) {
			if ( in_array( get_class( $product ), array( 'WC_Product_Booking', 'WC_Product_Accommodation_Booking' ), true ) ) {
				$value = self::get_resource_costs_value( $product->get_id(), $value );
			}

			return $value;
		}

		/**
		 * Return booking product resource block cost
		 *
		 * @param array      $value Resource block cost.
		 * @param WC_Product $product Product instance.
		 * @return array
		 */
		public static function get_resource_block_costs( $value, $product ) {
			if ( in_array( get_class( $product ), array( 'WC_Product_Booking', 'WC_Product_Accommodation_Booking' ), true ) ) {
				$value = self::get_resource_costs_value( $product->get_id(), $value, 'block' );
			}

			return $value;
		}

		/**
		 * Return booking product resource base or costs array
		 *
		 * @param int    $post_id Post ID.
		 * @param array  $costs Resource base|block cost.
		 * @param string $field base|block.
		 * @return array
		 */
		private static function get_resource_costs_value( $post_id, $costs, $field = 'base' ) {

			$zone_costs    = WCPBC()->current_zone->get_postmeta( $post_id, "_resource_{$field}_costs" );
			$price_methods = WCPBC()->current_zone->get_postmeta( $post_id, '_resource_price_method' );

			foreach ( $costs as $id => $value ) {
				if ( ! isset( $zone_costs[ $id ] ) || wcpbc_is_exchange_rate( $price_methods[ $id ] ) ) {
					$cost[ $id ] = WCPBC()->current_zone->get_exchange_rate_price( $value );
				} else {
					$cost[ $id ] = $zone_costs[ $id ];
				}
			}

			return $cost;
		}

		/**
		 * Return person type cost
		 *
		 * @param string     $value Person type cost.
		 * @param WC_Product $object Product instance.
		 * @return float
		 */
		public static function get_person_type_cost( $value, $object ) {
			if ( 'WC_Product_Booking_Person_Type' === get_class( $object ) ) {
				$value = self::get_person_type_costs_value( $object->get_id(), '_cost', $value );
			}

			return $value;
		}

		/**
		 * Return person type block cost
		 *
		 * @param array      $value Person type block cost.
		 * @param WC_Product $object Product instance.
		 * @return array
		 */
		public static function get_person_type_block_cost( $value, $object ) {
			if ( 'WC_Product_Booking_Person_Type' === get_class( $object ) ) {
				$value = self::get_person_type_costs_value( $object->get_id(), '_block_cost', $value );
			}

			return $value;
		}

		/**
		 * Return the person cost or block cost
		 *
		 * @param int    $post_id Post ID.
		 * @param string $meta_key Metadata key.
		 * @param float  $value The value of the original metadata.
		 * @return float
		 */
		private static function get_person_type_costs_value( $post_id, $meta_key, $value ) {

			if ( WCPBC()->current_zone->is_exchange_rate_price( $post_id ) ) {
				$value = WCPBC()->current_zone->get_exchange_rate_price( $value );
			} else {
				$value = WCPBC()->current_zone->get_postmeta( $post_id, $meta_key );
			}

			return $value;
		}

		/**
		 * Add tabs to WC 2.6+
		 *
		 * @param  array $tabs Tabs.
		 * @return array
		 */
		public static function register_tab( $tabs ) {

			$tabs['wcpbc_bookings_pricing'] = array(
				'label'    => __( 'Pricing Zone Costs', 'wc-price-based-country-pro' ),
				'target'   => 'wcpbc_bookings_pricing',
				'class'    => array(
					'show_if_booking',
				),
				'priority' => 80,
			);

			return $tabs;
		}

		/**
		 * Init resource field to manage resources output
		 */
		public static function init_admin_data() {

			global $post, $bookable_product;

			if ( empty( $bookable_product ) || $bookable_product->get_id() !== $post->ID ) {
				$bookable_product = new WC_Product_Booking( $post->ID );
			}

			// Init admin data.
			self::$admin_data = array(
				'resource_loop'     => 0,
				'resource_ids'      => $bookable_product->get_resource_ids(),
				'person_types_loop' => 0,
			);

			foreach ( WCPBC_Pricing_Zones::get_zones() as $zone ) {
				$zone_id = $zone->get_zone_id();

				self::$admin_data['base_costs'][ $zone_id ]   = $zone->get_postmeta( get_the_ID(), '_resource_base_costs' );
				self::$admin_data['block_costs'][ $zone_id ]  = $zone->get_postmeta( get_the_ID(), '_resource_block_costs' );
				self::$admin_data['price_method'][ $zone_id ] = $zone->get_postmeta( get_the_ID(), '_resource_price_method' );
			}

		}

		/**
		 * Display the resource pricing fields
		 *
		 * @param int $resource_id Resource ID.
		 * @param int $post_id Post ID.
		 */
		public static function after_resource_block_cost( $resource_id, $post_id ) {

			if ( ! isset( $_POST['loop'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification

				$loop = self::$admin_data['resource_loop'];
				self::$admin_data['resource_loop']++;

				$resource_id = current( self::$admin_data['resource_ids'] );
				next( self::$admin_data['resource_ids'] );

				$base_costs    = self::$admin_data['base_costs'];
				$block_costs   = self::$admin_data['block_costs'];
				$price_methods = self::$admin_data['price_method'];

			} else {
				$loop          = intval( $_POST['loop'] ); // phpcs:ignore WordPress.Security.NonceVerification
				$resource_id   = '';
				$base_costs    = '';
				$block_costs   = '';
				$price_methods = '';
			}

			include WC_Product_Price_Based_Country_Pro::plugin_path() . 'includes/admin/views/bookings/html-resource.php';
		}

		/**
		 * Display the person pricing fields
		 *
		 * @param int $person_id Person ID.
		 */
		public static function after_person_max_column( $person_id ) {

			$post_id = ! empty( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : get_the_ID(); // phpcs:ignore WordPress.Security.NonceVerification
			$loop    = ! empty( $_POST['loop'] ) ? intval( $_POST['loop'] ) : self::$admin_data['person_types_loop']; // phpcs:ignore WordPress.Security.NonceVerification

			foreach ( WCPBC_Pricing_Zones::get_zones() as $zone ) {
				include WC_Product_Price_Based_Country_Pro::plugin_path() . 'includes/admin/views/bookings/html-person.php';
			}

			self::$admin_data['person_types_loop'] = ++$loop;
		}

		/**
		 * Show the booking panels views
		 */
		public static function booking_panels() {

			// Get pricing rows.
			$pricing_rows = get_post_meta( get_the_ID(), '_wc_booking_pricing', true );

			// Fill intervals.
			$intervals = array(
				'months' => array(
					'1'  => __( 'January', 'wc-price-based-country-pro' ),
					'2'  => __( 'February', 'wc-price-based-country-pro' ),
					'3'  => __( 'March', 'wc-price-based-country-pro' ),
					'4'  => __( 'April', 'wc-price-based-country-pro' ),
					'5'  => __( 'May', 'wc-price-based-country-pro' ),
					'6'  => __( 'June', 'wc-price-based-country-pro' ),
					'7'  => __( 'July', 'wc-price-based-country-pro' ),
					'8'  => __( 'August', 'wc-price-based-country-pro' ),
					'9'  => __( 'September', 'wc-price-based-country-pro' ),
					'10' => __( 'October', 'wc-price-based-country-pro' ),
					'11' => __( 'November', 'wc-price-based-country-pro' ),
					'12' => __( 'December', 'wc-price-based-country-pro' ),
				),
				'days'   => array(
					'1' => __( 'Monday', 'wc-price-based-country-pro' ),
					'2' => __( 'Tuesday', 'wc-price-based-country-pro' ),
					'3' => __( 'Wednesday', 'wc-price-based-country-pro' ),
					'4' => __( 'Thursday', 'wc-price-based-country-pro' ),
					'5' => __( 'Friday', 'wc-price-based-country-pro' ),
					'6' => __( 'Saturday', 'wc-price-based-country-pro' ),
					'7' => __( 'Sunday', 'wc-price-based-country-pro' ),
				),
			);

			for ( $i = 1; $i <= 52; $i ++ ) {
				// translators: Week of year.
				$intervals['weeks'][ $i ] = sprintf( __( 'Week %s', 'wc-price-based-country-pro' ), $i );
			}

			// Range type labels.
			$type_labels = array(
				'custom'     => __( 'Date range', 'wc-price-based-country-pro' ),
				'months'     => __( 'Range of months', 'wc-price-based-country-pro' ),
				'weeks'      => __( 'Range of weeks', 'wc-price-based-country-pro' ),
				'days'       => __( 'Range of days', 'wc-price-based-country-pro' ),
				'time'       => __( 'Time Range', 'wc-price-based-country-pro' ),
				'persons'    => __( 'Person count', 'wc-price-based-country-pro' ),
				'blocks'     => __( 'Block count', 'wc-price-based-country-pro' ),
				'time'       => __( 'Time Range (all week)', 'wc-price-based-country-pro' ),
				'time:range' => __( 'Date Range with time', 'wc-price-based-country-pro' ),
			);

			$modifier_labels = array(
				''       => '+',
				'plus'   => '+',
				'minus'  => '-',
				'times'  => '&times;',
				'divide' => '&divide;',
				'equals' => '=',
			);

			foreach ( $intervals['days'] as $key => $label ) {
				$type_labels[ 'time:' . $key ] = $label;
			}

			include WC_Product_Price_Based_Country_Pro::plugin_path() . 'includes/admin/views/bookings/html-pricing.php';
		}

		/**
		 * Display range part of booking pricing
		 *
		 * @param array $data Pricing row data.
		 * @param array $intervals Intervals.
		 */
		private static function the_range( $data, $intervals ) {

			$type = $data['type'];
			$from = $data['from'];
			$to   = $data['to'];

			if ( isset( $intervals[ $type ][ $from ] ) ) {
				$from = $intervals[ $type ][ $from ];
			}

			if ( isset( $intervals[ $type ][ $to ] ) ) {
				$to = $intervals[ $type ][ $to ];
			}

			if ( isset( $data['from_date'] ) ) {
				$from = $data['from_date'] . ' ' . $from;
			}

			if ( isset( $data['to_date'] ) ) {
				$to = $data['to_date'] . ' ' . $to;
			}
			echo '<td style="border-right: none; width: 150px;"><p>' . esc_html( $from ) . '</p></td>';
			echo '<td style="border-right: none;"><p>' . esc_html__( 'to', 'wc-price-based-country-pro' ) . '</p></td>';
			echo '<td style="width: 150px;"><p>' . esc_html( $to ) . '</p></td>';
		}

		/**
		 * Save meta data
		 *
		 * @param int $post_id Post ID.
		 */
		public static function save_data( $post_id ) {

			$post_data     = wc_clean( wp_unslash( $_POST ) ); // WPCS: CSRF ok.
			$has_resources = isset( $post_data['resource_id'] ) && isset( $post_data['_wc_booking_has_resources'] );
			$has_persons   = isset( $post_data['person_id'] ) && isset( $post_data['_wc_booking_has_persons'] );

			foreach ( WCPBC_Pricing_Zones::get_zones() as $zone ) {

				$price_method = $zone->get_input_var( '_booking_price_method' );
				$pricing      = array();

				if ( wcpbc_is_exchange_rate( $price_method ) ) {

					$costs['booking_cost']       = isset( $post_data['_wc_booking_cost'] ) ? wc_format_decimal( $post_data['_wc_booking_cost'] ) : false;
					$costs['booking_base_cost']  = isset( $post_data['_wc_booking_base_cost'] ) ? wc_format_decimal( $post_data['_wc_booking_base_cost'] ) : wc_format_decimal( $post_data['_wc_booking_block_cost'] );
					$costs['booking_block_cost'] = $costs['booking_base_cost']; // Backward compatibility with WooCommerce Booking < 1.10.12.
					$costs['display_cost']       = isset( $post_data['_wc_display_cost'] ) ? wc_format_decimal( $post_data['_wc_display_cost'] ) : false;

					foreach ( $costs as $index => $value ) {
						$costs[ $index ] = ! empty( $value ) ? $zone->get_exchange_rate_price( $value, false ) : $value;
					}

					$row_size = isset( $post_data['wc_booking_pricing_type'] ) ? count( $post_data['wc_booking_pricing_type'] ) : 0;

					for ( $i = 0; $i < $row_size; $i ++ ) {
						foreach ( array( 'base_cost', 'cost' ) as $pricing_cost ) {
							$key_cost                       = 'wc_booking_pricing_' . $pricing_cost;
							$key_cost_modifier              = 'wc_booking_pricing_' . $pricing_cost . '_modifier';
							$pricing_cost_value             = empty( $post_data[ $key_cost ][ $i ] ) ? '' : wc_format_decimal( $post_data[ $key_cost ][ $i ] );
							$pricing_cost_modifier          = empty( $post_data[ $key_cost_modifier ][ $i ] ) ? '' : wc_clean( $post_data[ $key_cost_modifier ][ $i ] );
							$pricing[ $i ][ $pricing_cost ] = ! empty( $pricing_cost_value ) && ! in_array( $pricing_cost_modifier, array( 'times', 'divide' ), true ) ? $zone->get_exchange_rate_price( $pricing_cost_value, false ) : $pricing_cost_value;
						}
					}
				} else {

					// Price manually.
					$costs['booking_cost']       = wc_format_decimal( $zone->get_input_var( '_wc_booking_cost' ) );
					$costs['booking_base_cost']  = wc_format_decimal( $zone->get_input_var( '_wc_booking_base_cost' ) );
					$costs['booking_block_cost'] = $costs['booking_base_cost']; // Backward compatibility with WooCommerce Booking < 1.10.12.
					$costs['display_cost']       = wc_format_decimal( $zone->get_input_var( '_wc_display_cost' ) );

					$row_size = isset( $post_data['wc_booking_pricing_type'] ) ? count( $post_data['wc_booking_pricing_type'] ) : 0;
					$zone_id  = $zone->get_zone_id();

					for ( $i = 0; $i < $row_size; $i ++ ) {
						if ( isset( $post_data['wcpbc_booking_pricing_base_cost'][ $i ][ $zone_id ] ) ) {
							$pricing[ $i ]['base_cost'] = wc_format_decimal( $post_data['wcpbc_booking_pricing_base_cost'][ $i ][ $zone_id ] );
						} else {
							$pricing[ $i ]['base_cost'] = ! empty( $post_data['wc_booking_pricing_base_cost'][ $i ] ) ? wc_format_decimal( $zone->get_exchange_rate_price( $post_data['wc_booking_pricing_base_cost'][ $i ], false ) ) : '';
						}

						if ( isset( $post_data['wcpbc_booking_pricing_cost'][ $i ][ $zone_id ] ) ) {
							$pricing[ $i ]['cost'] = wc_format_decimal( wc_clean( $post_data['wcpbc_booking_pricing_cost'][ $i ][ $zone_id ] ) );
						} else {
							$pricing[ $i ]['cost'] = ! empty( $post_data['wc_booking_pricing_cost'][ $i ] ) ? wc_format_decimal( $zone->get_exchange_rate_price( $post_data['wc_booking_pricing_cost'][ $i ], false ) ) : '';
						}
					}
				}

				foreach ( $costs as $key => $value ) {
					$zone->set_postmeta( $post_id, "_wc_{$key}", $value );
				}

				$zone->set_postmeta( $post_id, '_pricing', $pricing );
				$zone->set_postmeta( $post_id, '_price', $costs['display_cost'] );
				$zone->set_postmeta( $post_id, '_price_method', $price_method );

				// Update resources.
				self::update_resources( $post_id, $zone, $post_data );

				// Update Person Types.
				self::update_person_types( $zone, $post_data );
			}
		}

		/**
		 * Update resources
		 *
		 * @param int                $post_id Post ID.
		 * @param WCPBC_Pricing_Zone $zone Pricing zone instance.
		 * @param array              $post_data POST array.
		 */
		public static function update_resources( $post_id, $zone, $post_data ) {
			$resources = self::get_posted_resources( $zone, $post_data );

			$zone->set_postmeta( $post_id, '_resource_base_costs', wp_list_pluck( $resources, 'base_cost' ) );
			$zone->set_postmeta( $post_id, '_resource_block_costs', wp_list_pluck( $resources, 'block_cost' ) );
			$zone->set_postmeta( $post_id, '_resource_price_method', wp_list_pluck( $resources, 'price_method' ) );
		}

		/**
		 * Get posted resources
		 *
		 * @param WCPBC_Pricing_Zone $zone Pricing zone instance.
		 * @param array              $post_data POST array.
		 * @return array
		 */
		private static function get_posted_resources( $zone, $post_data ) {

			$zone_id   = $zone->get_zone_id();
			$resources = array();

			if ( isset( $post_data['resource_id'] ) && isset( $post_data['_wc_booking_has_resources'] ) ) {

				$resource_ids             = $post_data['resource_id'];
				$resource_menu_order      = $post_data['resource_menu_order'];
				$resource_price_method    = $post_data[ "_{$zone_id}_booking_resource_price_method" ];
				$resource_base_cost       = $post_data['resource_cost'];
				$resource_block_cost      = $post_data['resource_block_cost'];
				$resource_zone_base_cost  = $post_data[ "_{$zone_id}_booking_resource_cost" ];
				$resource_zone_block_cost = $post_data[ "_{$zone_id}_booking_resource_block_cost" ];

				foreach ( $resource_menu_order as $key => $value ) {
					$price_method = wc_clean( $resource_price_method[ $key ] );
					$base_cost    = wcpbc_is_exchange_rate( $price_method ) ? wc_clean( $resource_base_cost[ $key ] ) : $resource_zone_base_cost[ $key ];
					$block_cost   = wcpbc_is_exchange_rate( $price_method ) ? wc_clean( $resource_block_cost[ $key ] ) : $resource_zone_block_cost[ $key ];
					$_rate        = wcpbc_is_exchange_rate( $price_method ) ? $zone->get_exchange_rate() : 1;

					$base_cost  = empty( $base_cost ) ? $base_cost : wc_format_decimal( $base_cost ) * $_rate;
					$block_cost = empty( $block_cost ) ? $block_cost : wc_format_decimal( $block_cost ) * $_rate;

					$resources[ absint( $resource_ids[ $key ] ) ] = array(
						'base_cost'    => $base_cost,
						'block_cost'   => $block_cost,
						'price_method' => $price_method,
					);
				}
			}

			return $resources;
		}

		/**
		 * Update person types meta data
		 *
		 * @param WCPBC_Pricing_Zone $zone Pricing zone instance.
		 * @param array              $post_data POST array.
		 */
		public static function update_person_types( $zone, $post_data ) {
			if ( ! ( isset( $post_data['person_id'] ) && isset( $post_data['_wc_booking_has_persons'] ) ) ) {
				return;
			}

			foreach ( $post_data['person_id'] as $i => $person_id ) {
				$price_method = $zone->get_input_var( '_booking_person_price_method', $i );
				$cost         = wcpbc_is_exchange_rate( $price_method ) ? $zone->get_exchange_rate_price( wc_format_decimal( $post_data['person_cost'][ $i ] ) ) : wc_format_decimal( $zone->get_input_var( '_booking_person_person_cost', $i ) );
				$block_cost   = wcpbc_is_exchange_rate( $price_method ) ? $zone->get_exchange_rate_price( wc_format_decimal( $post_data['person_block_cost'][ $i ] ) ) : wc_format_decimal( $zone->get_input_var( '_booking_person_block_cost', $i ) );

				$zone->set_postmeta( $person_id, '_price_method', $price_method );
				$zone->set_postmeta( $person_id, '_cost', $cost );
				$zone->set_postmeta( $person_id, '_block_cost', $block_cost );
			}
		}

		/**
		 * Add posted data to the cart item
		 *
		 * @param array $cart_item_meta Cart item meta.
		 * @param int   $product_id Product ID.
		 * @return array
		 */
		public static function add_cart_item_data( $cart_item_meta, $product_id ) {

			if ( empty( $cart_item_meta['booking'] ) ) {
				return $cart_item_meta;
			}

			$cart_item_meta['booking']['_posted_data']   = $_POST; // phpcs:ignore WordPress.Security.NonceVerification
			$cart_item_meta['booking']['_wcpbc_zone_id'] = empty( WCPBC()->current_zone ) ? '' : WCPBC()->current_zone->get_zone_id();

			return $cart_item_meta;
		}

		/**
		 * Get data from the session and add to the cart item's meta
		 *
		 * @param array  $cart_item Cart Item.
		 * @param array  $values Values.
		 * @param string $cart_item_key Cart item key..
		 * @return array cart item
		 */
		public static function get_cart_item_from_session( $cart_item, $values, $cart_item_key ) {

			if ( empty( $values['booking']['_posted_data'] ) || ! isset( $cart_item['booking']['_wcpbc_zone_id'] ) ) {
				return $cart_item;
			}

			$cart_item['booking']['_wcpbc_zone_id'] = empty( WCPBC()->current_zone ) ? '' : WCPBC()->current_zone->get_zone_id();

			if ( $cart_item['booking']['_wcpbc_zone_id'] !== $values['booking']['_wcpbc_zone_id'] && class_exists( 'WC_Booking_Form' ) ) {

				// Cancelled the booking temporally to recalculate cost.
				$post_status = get_post_status( $cart_item['booking']['_booking_id'] );

				wp_update_post(
					array(
						'ID'          => $cart_item['booking']['_booking_id'],
						'post_status' => 'cancelled',
					)
				);

				// Recalculate the cost.
				if ( is_callable( array( 'WC_Bookings_Cost_Calculation', 'calculate_booking_cost' ) ) ) {
					$data = wc_bookings_get_posted_data( $values['booking']['_posted_data'], $cart_item['data'] );
					$cost = WC_Bookings_Cost_Calculation::calculate_booking_cost( $data, $cart_item['data'] );
				} else {
					$booking_form = new WC_Booking_Form( $cart_item['data'] );
					$cost         = $booking_form->calculate_booking_cost( $values['booking']['_posted_data'] );
				}

				// Reset booking to initial status.
				wp_update_post(
					array(
						'ID'          => $cart_item['booking']['_booking_id'],
						'post_status' => $post_status,
					)
				);

				if ( ! is_wp_error( $cost ) ) {
					// Re-set the booking cost.
					$cart_item['booking']['_cost'] = $cost;
					$cart_item['data']->set_price( $cost );
				} else {
					// Log the error.
					$logger = new WC_Logger();
					$logger->add( 'wc_price_based_country', 'WCPBC_Bookings error calculate_booking_cost: ' . $cost->get_error_message() );
				}
			}

			return $cart_item;
		}
	}

	WCPBC_Bookings::init();

endif;
