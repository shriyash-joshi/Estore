<?php
/**
 * Handle integration with WooCommerce Customer History by Brian Richards.
 *
 * @version 2.5.0
 * @package WCPBC
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WCPBC_Customer_History' ) && version_compare( WC_VERSION, '3.0', '>' ) ) :

	/**
	 * WCPBC_Customer_History
	 */
	class WCPBC_Customer_History {

		/**
		 * WCCH_Show_History instance.
		 *
		 * @var WCCH_Show_History
		 */
		private static $show_history;

		/**
		 * Init integration
		 */
		public static function init() {
			add_action( 'admin_init', array( __CLASS__, 'register_metaboxes' ), 20 );

			/*
			* Instance show history and remove hooks
			*/
			self::$show_history = new WCCH_Show_History();
			remove_action( 'admin_init', array( self::$show_history, 'register_metaboxes' ) );
			remove_filter( 'woocommerce_shop_order_search_fields', array( self::$show_history, 'search_customer_history' ) );
			remove_action( 'woocommerce_email_order_meta', array( self::$show_history, 'email_customer_history' ), 10, 2 );
		}

		/**
		 * Register "Customer Browsing History" metabox for Order posts.
		 */
		public static function register_metaboxes() {
			remove_meta_box( 'woocommerce-customer-purchase-history', 'shop_order', 'normal' );
			add_meta_box( 'woocommerce-customer-purchase-history', __( 'Customer Purchase History', 'woocommerce-customer-history' ), array( __CLASS__, 'render_purchase_history' ), 'shop_order', 'normal', 'default' );
		}

		/**
		 * Add filter to display purchase history on base currency
		 */
		public static function add_order_filters() {
			add_filter( 'woocommerce_order_get_total', array( __CLASS__, 'get_order_total' ), 1, 2 );
			add_filter( 'woocommerce_order_get_currency', array( __CLASS__, 'get_order_currency' ), 1, 2 );
		}

		/**
		 * Remove filter to display purchase history on base currency
		 */
		public static function remove_order_filters() {
			remove_filter( 'woocommerce_order_get_total', array( __CLASS__, 'get_order_total' ), 1, 2 );
			remove_filter( 'woocommerce_order_get_currency', array( __CLASS__, 'get_order_currency' ), 1, 2 );
		}

		/**
		 * Return order total on base currency
		 *
		 * @param float    $value Order total.
		 * @param WC_Order $order Order instance.
		 * @return float
		 */
		public static function get_order_total( $value, $order ) {
			$zone = WCPBC_Pricing_Zones::get_zone_from_order( $order );
			return $zone->get_base_currency_amount( $value );
		}

		/**
		 * Return base currency
		 *
		 * @param float    $value Order total.
		 * @param WC_Order $order Order instance.
		 * @return float
		 */
		public static function get_order_currency( $value, $order ) {
			return wcpbc_get_base_currency();
		}

		/**
		 * Output browsing history for metabox and email.
		 *
		 * @param object $order Order post object.
		 */
		public static function render_purchase_history( $order = 0 ) {
			self::add_order_filters();
			self::$show_history->render_purchase_history( $order );
			self::remove_order_filters();
		}

	}
	WCPBC_Customer_History::init();

endif;
