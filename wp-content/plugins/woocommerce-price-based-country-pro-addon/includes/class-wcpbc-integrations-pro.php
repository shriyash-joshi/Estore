<?php
/**
 * Integrations
 *
 * Handle integrations between PBC and 3rd-Party plugins
 *
 * @class  WCPBC_Integrations_Pro
 * @version 2.4.8
 * @package WCPBC
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WCPBC_Integrations_Pro' ) ) :

	/**
	 * WCPBC_Integrations_Pro Class
	 */
	class WCPBC_Integrations_Pro {

		/**
		 * Metadata keys to filter on get_post_meta.
		 *
		 * @var array
		 */
		private static $price_metakeys = array();

		/**
		 * Add built-in integrations
		 */
		public static function init() {

			$integrations = array(
				'WC_Subscriptions'                 => dirname( __FILE__ ) . '/integrations/woocommerce-subscriptions/class-wcpbc-subscriptions.php',
				'WC_Bundles'                       => dirname( __FILE__ ) . '/integrations/class-wcpbc-bundles.php',
				'WC_Product_Addons'                => dirname( __FILE__ ) . '/integrations/class-wcpbc-product-addons.php',
				'WC_Bookings'                      => dirname( __FILE__ ) . '/integrations/class-wcpbc-bookings.php',
				'WC_Accommodation_Bookings_Plugin' => dirname( __FILE__ ) . '/integrations/class-wcpbc-bookings-accommodation.php',
				'WC_Composite_Products'            => dirname( __FILE__ ) . '/integrations/class-wcpbc-composite.php',
				'Affiliate_WP'                     => dirname( __FILE__ ) . '/integrations/class-wcpbc-affiliate-wp.php',
				'WooCommerce_Customer_History'     => dirname( __FILE__ ) . '/integrations/class-wcpbc-customer-history.php',
				'Woocommerce_German_Market'        => dirname( __FILE__ ) . '/integrations/class-wcpbc-german-market.php',
				'WC_Name_Your_Price'               => dirname( __FILE__ ) . '/integrations/class-wcpbc-name-your-price.php',
				'WC_Product_Job_Package'           => dirname( __FILE__ ) . '/integrations/class-wcpbc-job-package.php',
				'WooCommerce_Germanized'           => dirname( __FILE__ ) . '/integrations/class-wcpbc-germanized.php',
				'WC_Smart_Coupons'                 => dirname( __FILE__ ) . '/integrations/class-wcpbc-smart-coupons.php',
				'wc_add_fees_load_plugin_version'  => dirname( __FILE__ ) . '/integrations/class-wcpbc-additional-fees.php',
				'WC_Dynamic_Pricing'               => dirname( __FILE__ ) . '/integrations/class-wcpbc-dynamic-pricing-pro.php',
			);

			foreach ( $integrations as $class => $integration_file ) {
				if ( class_exists( $class ) || function_exists( $class ) ) {
					include_once $integration_file;
				}
			}

			self::$price_metakeys = apply_filters( 'wc_price_based_country_price_meta_keys', array() );

			if ( ! empty( self::$price_metakeys ) ) {
				add_action( 'wc_price_based_country_frontend_princing_init', array( __CLASS__, 'frontend_princing_init' ) );
			}
		}

		/**
		 * Frontend pricing init.
		 */
		public static function frontend_princing_init() {
			add_filter( 'get_post_metadata', array( __CLASS__, 'get_post_metadata' ), 10, 4 );
		}

		/**
		 * Return a meta data value
		 *
		 * @param null|array|string $meta_value The value get_metadata() should return - a single metadata value or an array of values.
		 * @param int               $object_id Object ID.
		 * @param string            $meta_key Meta key.
		 * @param bool              $single Whether to return only the first value of the specified $meta_key.
		 */
		public static function get_post_metadata( $meta_value, $object_id, $meta_key, $single ) {
			if ( $single && in_array( $meta_key, self::$price_metakeys, true ) ) {

				remove_filter( 'get_post_metadata', array( __CLASS__, 'get_post_metadata' ), 10, 4 );
				$meta_value = wcpbc_the_zone()->get_post_price( $object_id, $meta_key );
				add_filter( 'get_post_metadata', array( __CLASS__, 'get_post_metadata' ), 10, 4 );
			}

			return $meta_value;
		}
	}
endif;
