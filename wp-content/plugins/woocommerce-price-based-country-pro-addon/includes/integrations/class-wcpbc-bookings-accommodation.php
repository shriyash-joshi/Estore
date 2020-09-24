<?php
/**
 * Handle integration with WooCommerce Accommodation Bookings by WooCommerce.
 *
 * @version 2.4.10
 * @package WCPBC/Integrations
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WCPBC_Bookings_Accommodation' ) && class_exists( 'WCPBC_Bookings' ) ) :

	/**
	 *
	 * WCPBC_Bookings Class
	 */
	class WCPBC_Bookings_Accommodation {

		/**
		 * Hook actions and filters
		 */
		public static function init() {
			add_action( 'wc_price_based_country_frontend_princing_init', array( __CLASS__, 'pricing_init' ) );
			add_filter( 'woocommerce_product_data_tabs', array( __CLASS__, 'register_tab' ), 20 );
			add_action( 'woocommerce_accommodation_bookings_after_display_cost', array( __CLASS__, 'booking_pricing_unique_id' ) );
			add_action( 'woocommerce_accommodation_bookings_after_booking_pricing_override_block_cost', array( __CLASS__, 'after_booking_pricing' ), 10, 2 );
			add_action( 'woocommerce_product_data_panels', array( __CLASS__, 'booking_accommodation_panel' ), 100 );
			add_action( 'woocommerce_process_product_meta_accommodation-booking', array( __CLASS__, 'save_data' ) );
		}

		/**
		 * Add filter needed to pricing zone
		 */
		public static function pricing_init() {
			add_filter( 'woocommerce_product_get_pricing', array( __CLASS__, 'get_pricing' ), 10, 2 );
		}

		/**
		 * Return booking product pricing prop
		 *
		 * @param array      $value Booking pricing.
		 * @param WC_Product $product Product instance.
		 * @return array
		 */
		public static function get_pricing( $value, $product ) {

			if ( 'WC_Product_Accommodation_Booking' === get_class( $product ) ) {

				$is_exchange_rate = WCPBC()->current_zone->is_exchange_rate_price( $product->get_id() );
				$zone_pricing     = WCPBC()->current_zone->get_postmeta( $product->get_id(), '_wc_booking_pricing' );

				foreach ( $value as $i => $row ) {
					if ( $is_exchange_rate || ! isset( $zone_pricing[ $i ]['cost'] ) ) {
						$value[ $i ]['cost'] = WCPBC()->current_zone->get_exchange_rate_price( $row['cost'] );
					} else {
						$value[ $i ]['cost']          = $zone_pricing[ $i ]['cost'];
						$value[ $i ]['modifier']      = $zone_pricing[ $i ]['modifier'];
						$rates[ $i ]['base_modifier'] = $zone_pricing[ $i ]['base_modifier'];
					}
				}
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

			$tabs['wcpbc_accommodation_bookings_rates'] = array(
				'label'    => __( 'Pricing Zone Rates', 'wc-price-based-country-pro' ),
				'target'   => 'wcpbc_accommodation_bookings_rates',
				'class'    => array(
					'show_if_accommodation-booking bookings_pricing_tab',
				),
				'priority' => 80,
			);

			return $tabs;
		}

		/**
		 * Add a unique id to booking pricing rows
		 *
		 * @param int $post_id Post ID.
		 */
		public static function booking_pricing_unique_id( $post_id ) {
			$pricing = get_post_meta( $post_id, '_wc_booking_pricing', true );
			$pricing = empty( $pricing ) ? array() : $pricing;
			foreach ( $pricing as $i => $row ) {
				if ( empty( $row['uniqid'] ) ) {
					$pricing[ $i ]['uniqid'] = uniqid();
				}
			}
			update_post_meta( $post_id, '_wc_booking_pricing', $pricing );
		}

		/**
		 * Display booking pricing row unique ID input.
		 *
		 * @param array $row Bookking pricing row.
		 * @param int   $post_id The post ID.
		 */
		public static function after_booking_pricing( $row, $post_id ) {
			$uniqid = empty( $row['uniqid'] ) ? '' : $row['uniqid'];
			echo '<input type="hidden" name="wc_accommodation_booking_pricing_uniqid[]" value="' . esc_attr( $uniqid ) . '" />';
		}

		/**
		 * Display the booking accommodation panel views
		 */
		public static function booking_accommodation_panel() {
			$types = array(
				'custom' => __( 'Range of certain nights', 'woocommerce-accommodation-bookings' ),
				'months' => __( 'Range of months', 'woocommerce-accommodation-bookings' ),
				'weeks'  => __( 'Range of weeks', 'woocommerce-accommodation-bookings' ),
				'days'   => __( 'Range of nights during the week', 'woocommerce-accommodation-bookings' ),
			);

			$intervals['months'] = array(
				'1'  => __( 'January', 'woocommerce-accommodation-bookings' ),
				'2'  => __( 'February', 'woocommerce-accommodation-bookings' ),
				'3'  => __( 'March', 'woocommerce-accommodation-bookings' ),
				'4'  => __( 'April', 'woocommerce-accommodation-bookings' ),
				'5'  => __( 'May', 'woocommerce-accommodation-bookings' ),
				'6'  => __( 'June', 'woocommerce-accommodation-bookings' ),
				'7'  => __( 'July', 'woocommerce-accommodation-bookings' ),
				'8'  => __( 'August', 'woocommerce-accommodation-bookings' ),
				'9'  => __( 'September', 'woocommerce-accommodation-bookings' ),
				'10' => __( 'October', 'woocommerce-accommodation-bookings' ),
				'11' => __( 'November', 'woocommerce-accommodation-bookings' ),
				'12' => __( 'December', 'woocommerce-accommodation-bookings' ),
			);

			$intervals['days'] = array(
				'1' => __( 'Monday', 'woocommerce-accommodation-bookings' ),
				'2' => __( 'Tuesday', 'woocommerce-accommodation-bookings' ),
				'3' => __( 'Wednesday', 'woocommerce-accommodation-bookings' ),
				'4' => __( 'Thursday', 'woocommerce-accommodation-bookings' ),
				'5' => __( 'Friday', 'woocommerce-accommodation-bookings' ),
				'6' => __( 'Saturday', 'woocommerce-accommodation-bookings' ),
				'7' => __( 'Sunday', 'woocommerce-accommodation-bookings' ),
			);

			for ( $i = 1; $i <= 53; $i ++ ) {
				// translators: Week of year.
				$intervals['weeks'][ $i ] = sprintf( __( 'Week %s', 'woocommerce-accommodation-bookings' ), $i );
			}

			$rows = get_post_meta( get_the_ID(), '_wc_booking_pricing', true );
			$rows = empty( $rows ) ? array() : $rows;

			echo '<div id="wcpbc_accommodation_bookings_rates" class="panel panel woocommerce_options_panel bookings_extension hidden">';

			foreach ( WCPBC_Pricing_Zones::get_zones() as $zone ) {
				// Calculate the display costs.
				$zone_rows = $zone->get_postmeta( get_the_ID(), '_wc_booking_pricing' );
				$base_cost = $zone->get_postmeta( get_the_ID(), '_wc_booking_base_cost' );
				$costs     = array();
				foreach ( $rows as $i => $row ) {
					$costs[ $i ] = '';
					if ( isset( $zone_rows[ $i ]['cost'] ) && isset( $zone_rows[ $i ]['modifier'] ) ) {
						$costs[ $i ] = 'plus' === $zone_rows[ $i ]['modifier'] ? $base_cost + $zone_rows[ $i ]['cost'] : $base_cost - $zone_rows[ $i ]['cost'];
					}
				}
				// Display the view.
				include WC_Product_Price_Based_Country_Pro::plugin_path() . 'includes/admin/views/bookings/html-accomodation-rates.php';
			}
			echo '</div>';
		}

		/**
		 * Save meta data
		 *
		 * @param int $post_id Post ID.
		 */
		public static function save_data( $post_id ) {
			$postdata = wc_clean( wp_unslash( $_POST ) ); // WPCS: CSRF ok.
			$row_size = isset( $postdata['wc_accommodation_booking_pricing_type'] ) ? count( $postdata['wc_accommodation_booking_pricing_type'] ) : 0;

			foreach ( WCPBC_Pricing_Zones::get_zones() as $zone ) {
				$price_method = isset( $postdata[ $zone->get_postmetakey( '_wc_accommodation_price_method' ) ] ) ? $postdata[ $zone->get_postmetakey( '_wc_accommodation_price_method' ) ] : '';
				$base_cost    = 0;
				$display_cost = 0;
				$pricing      = array();

				if ( wcpbc_is_exchange_rate( $price_method ) ) {
					$base_cost    = $zone->get_exchange_rate_price( floatval( $postdata['_wc_accommodation_booking_base_cost'] ), false );
					$display_cost = $zone->get_exchange_rate_price( floatval( $postdata['_wc_accommodation_booking_display_cost'] ), false );
				} else {
					$base_cost    = floatval( $postdata[ $zone->get_postmetakey( '_wc_accommodation_booking_base_cost' ) ] );
					$display_cost = floatval( $postdata[ $zone->get_postmetakey( '_wc_accommodation_booking_display_cost' ) ] );
					// Get rates.
					$pricing = self::get_pricing_rows( $postdata, $base_cost, $zone );
				}

				// Update post meta data.
				$zone->set_exchange_rate_price( $post_id, wcpbc_is_exchange_rate( $price_method ) );
				$zone->set_postmeta( $post_id, '_wc_booking_base_cost', $base_cost );
				$zone->set_postmeta( $post_id, '_wc_booking_block_cost', $base_cost );
				$zone->set_postmeta( $post_id, '_wc_display_cost', $display_cost );

				// Update pricing rates.
				$zone->set_postmeta( $post_id, '_wc_booking_pricing', $pricing );

				// Update resources.
				WCPBC_Bookings::update_resources( $post_id, $zone, $postdata );

				// Update Person Types.
				WCPBC_Bookings::update_person_types( $zone, $postdata );
			}
		}

		/**
		 * Get pricing rows
		 *
		 * @param array              $postdata Sanitize $_POST array.
		 * @param float              $base_cost Base cost.
		 * @param WCPBC_Zone_Pricing $zone Zone pricing instance.
		 * @return array
		 */
		private static function get_pricing_rows( $postdata, $base_cost, $zone ) {
			$rates = array();
			if ( ! empty( $postdata['wc_accommodation_booking_pricing_uniqid'] ) ) {
				$base_cost = absint( $base_cost );
				$datakey   = $zone->get_postmetakey( '_wc_accommodation_booking_pricing_block_cost' );

				foreach ( $postdata['wc_accommodation_booking_pricing_uniqid'] as $i => $row_id ) {
					if ( empty( $row_id ) || ! isset( $postdata[ $datakey ][ $row_id ] ) ) {
						$block_cost = $zone->get_exchange_rate_price( floatval( $postdata['wc_accommodation_booking_pricing_block_cost'][ $i ] ), false );
					} else {
						$block_cost = $postdata[ $datakey ][ $row_id ];
					}

					$rates[ $i ]['modifier']      = $block_cost > $base_cost ? 'plus' : 'minus';
					$rates[ $i ]['base_modifier'] = $rates[ $i ]['modifier'];
					$rates[ $i ]['cost']          = absint( $block_cost - $base_cost );
					$rates[ $i ]['base_cost']     = 0;
				}
			}
			return $rates;
		}

		/**
		 * Display admin minimun version required
		 */
		public static function min_version_notice() {
			// translators: 1: HTML tag, 2: HTML tag, 3: German Market version.
			$notice = sprintf( __( '%1$sPrice Based on Country Pro & WooCommerce Accommodation Bookings%2$s compatibility requires WooCommerce Accommodation Bookings version +1.1.5. You are running Accommodation Bookings %3$s.', 'wc-price-based-country-pro' ), '<strong>', '</strong>', WC_ACCOMMODATION_BOOKINGS_VERSION );
			echo '<div id="message" class="error"><p>' . wp_kses_post( $notice ) . '</p></div>';
		}
	} // End Class.

	if ( version_compare( WC_ACCOMMODATION_BOOKINGS_VERSION, '1.1.5', '>=' ) ) {
		WCPBC_Bookings_Accommodation::init();
	} else {
		add_action( 'admin_notices', array( 'WCPBC_Bookings_Accommodation', 'min_version_notice' ) );
	}
endif;
