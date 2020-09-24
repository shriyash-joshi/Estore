<?php
/**
 * Handle integration with WooCommerce Product Bundles by SomewhereWarm.
 *
 * @version 2.5.4
 * @package WCPBC
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WCPBC_Bundles' ) ) :

	/**
	 * WCPBC_Bundles Class
	 */
	class WCPBC_Bundles {

		/**
		 * Hook actions and filters
		 *
		 * @since 1.0
		 */
		public static function init() {
			add_action( 'wc_price_based_country_pro_installed', array( __CLASS__, 'install' ) );
			add_action( 'woocommerce_product_object_updated_props', array( __CLASS__, 'deferred_synced_bundle' ) );
			add_action( 'woocommerce_process_product_meta_bundle', array( __CLASS__, 'process_product_meta' ), 10 );
			add_action( 'wc_price_based_country_csv_import_product_bundle', array( __CLASS__, 'copy_metadata' ), 10, 2 );
			add_action( 'wc_price_based_country_manual_order_before_line_save', array( __CLASS__, 'manual_order_before_line_save' ), 10, 2 );
			add_filter( 'wc_price_based_country_price_meta_keys', array( __CLASS__, 'bundles_price_meta_keys' ) );
			add_filter( 'wc_price_based_country_ajax_geolocation_product_data', array( __CLASS__, 'ajax_geolocation_product_data' ), 10, 3 );
		}

		/**
		 * Install. Update product bundles price meta_keys from regular, sale and price meta_keys
		 */
		public static function install() {

			foreach ( WCPBC_Pricing_Zones::get_zones() as $zone ) {

				$posts = get_posts(
					array(
						'posts_per_page' => -1,
						'post_type'      => 'product',
						'tax_query'      => array( // WPCS: slow query ok.
							array(
								'taxonomy' => 'product_type',
								'field'    => 'slug',
								'terms'    => 'bundle',
							),
						),
						'meta_query'     => array( // WPCS: slow query ok.
							'relation' => 'AND',
							array(
								'key'   => $zone->get_postmetakey( '_price_method' ),
								'value' => 'manual',
							),
							array(
								'key'     => $zone->get_postmetakey( '_wc_pb_base_price' ),
								'compare' => 'NOT EXISTS',
							),
						),
					)
				);

				foreach ( $posts as $post ) {
					self::copy_metadata( $post->ID, $zone );
				}
			}
		}

		/**
		 * Pricing zone use on the sync bundle price function.
		 *
		 * @var array
		 */
		private static $deferred_product_sync = false;

		/**
		 * Add $product to the deferred_product_sync array.
		 *
		 * @param WC_Product_Bundle $product Product bundle instance.
		 */
		public static function deferred_synced_bundle( $product ) {
			if ( is_callable( array( $product, 'get_type' ) ) && 'bundle' === $product->get_type() ) {

				if ( false === self::$deferred_product_sync ) {
					add_action( 'shutdown', array( __CLASS__, 'sync_bundles_price' ) );
					self::$deferred_product_sync = array();
				}
				self::$deferred_product_sync[ $product->get_id() ] = $product;
			}
		}

		/**
		 * Pricing zone use on the sync bundle price function.
		 *
		 * @var WCPBC_Pricing_Zone
		 */
		private static $sync_zone = false;

		/**
		 * Sync the bundles price.
		 */
		public static function sync_bundles_price() {
			if ( ! is_array( self::$deferred_product_sync ) ) {
				return;
			}

			add_action( 'woocommerce_before_init_bundled_item', array( __CLASS__, 'on_sync_before_init_bundled_item' ) );
			add_action( 'woocommerce_after_init_bundled_item', array( __CLASS__, 'on_sync_after_init_bundled_item' ) );
			add_filter( 'woocommerce_bundled_item_hash', array( __CLASS__, 'on_sync_bundled_item_hash' ) );

			foreach ( self::$deferred_product_sync as $product ) {

				$product_id    = $product->get_id();
				$bundled_items = $product->get_bundled_items( 'edit' );

				foreach ( WCPBC_Pricing_Zones::get_zones() as $zone ) {
					self::$sync_zone = $zone;

					$min_raw_price         = floatval( $zone->get_post_price( $product_id, '_wc_pb_base_price' ) );
					$min_raw_regular_price = floatval( $zone->get_post_price( $product_id, '_wc_pb_base_price' ) );

					$bundled_items = $product->get_bundled_items( 'edit' );

					foreach ( $bundled_items as $bundled_item ) {
						if ( $bundled_item->is_priced_individually() ) {
							$min_quantity = $bundled_item->get_quantity(
								'min',
								array(
									'context'        => 'price',
									'check_optional' => true,
								)
							);

							$min_raw_price += $min_quantity * floatval( $bundled_item->min_price );
						}
					}

					$zone->set_postmeta( $product->get_id(), '_price', $min_raw_price );
				}

				self::$deferred_product_sync = false;
			}

			remove_action( 'woocommerce_before_init_bundled_item', array( __CLASS__, 'on_sync_before_init_bundled_item' ) );
			remove_action( 'woocommerce_after_init_bundled_item', array( __CLASS__, 'on_sync_after_init_bundled_item' ) );
			remove_filter( 'woocommerce_bundled_item_hash', array( __CLASS__, 'on_sync_bundled_item_hash' ) );
		}

		/**
		 * Before bundle item sync prices.
		 */
		public static function on_sync_before_init_bundled_item() {
			add_filter( 'woocommerce_bundled_item_raw_price', array( __CLASS__, 'on_sync_set_raw_prices' ), 0, 4 );
		}

		/**
		 * After bundle item sync prices.
		 */
		public static function on_sync_after_init_bundled_item() {
			remove_filter( 'woocommerce_bundled_item_raw_price', array( __CLASS__, 'on_sync_set_raw_prices' ), 0, 4 );
		}

		/**
		 * Set the raw prices for the current zone and return the raw price.
		 *
		 * @param mixed           $price Raw price.
		 * @param WC_Product      $product Product instance.
		 * @param int             $discount Price discount.
		 * @param WC_Bundled_Item $bundled_item Bundle item instance.
		 * @return mixed
		 */
		public static function on_sync_set_raw_prices( $price, $product, $discount, $bundled_item ) {
			if ( empty( $product->on_sync_set_raw_prices ) && false !== self::$sync_zone ) {
				$product->set_price( self::$sync_zone->get_post_price( $product->get_id(), '_price' ) );
				$product->set_regular_price( self::$sync_zone->get_post_price( $product->get_id(), '_regular_price' ) );
				$product->on_sync_set_raw_prices = 1;

				return $bundled_item->get_raw_price( $product );
			}
			return $price;
		}

		/**
		 * Add hash to bundle item cache. Using on sync bundle item price.
		 *
		 * @param array $hash Cache parts.
		 */
		public static function on_sync_bundled_item_hash( $hash ) {
			if ( false !== self::$sync_zone ) {
				$hash[] = self::$sync_zone->get_id() . self::$sync_zone->get_currency() . self::$sync_zone->get_exchange_rate();
			}
			return $hash;
		}

		/**
		 * Add product bundles pricing meta keys
		 *
		 * @param array $meta_keys Pricing metadata keys.
		 * @return array
		 */
		public static function bundles_price_meta_keys( $meta_keys ) {
			array_push( $meta_keys, '_wc_pb_base_regular_price', '_wc_pb_base_sale_price', '_wc_pb_base_price' );
			return $meta_keys;
		}

		/**
		 * Save product metadata
		 *
		 * @param int $post_id Post ID.
		 */
		public static function process_product_meta( $post_id ) {
			WCPBC_Admin_Meta_Boxes::process_product_meta( $post_id );
			foreach ( WCPBC_Pricing_Zones::get_zones() as $zone ) {
				if ( ! $zone->is_exchange_rate_price( $post_id ) ) {
					self::copy_metadata( $post_id, $zone );
				} else {
					self::set_by_exchange_rate_metadata( $post_id, $zone );
				}
			}
		}

		/**
		 * Copy product price to the bundle metadata.
		 *
		 * @param int                $post_id Post ID.
		 * @param WCPBC_Pricing_Zone $zone Pricing zone instance..
		 */
		public static function copy_metadata( $post_id, $zone ) {
			$regular_price = $zone->get_postmeta( $post_id, '_regular_price' );
			$sale_price    = $zone->get_postmeta( $post_id, '_sale_price' );
			$price         = $zone->get_postmeta( $post_id, '_price' );

			$zone->set_postmeta( $post_id, '_wc_pb_base_regular_price', $regular_price );
			$zone->set_postmeta( $post_id, '_wc_pb_base_sale_price', $sale_price );
			$zone->set_postmeta( $post_id, '_wc_pb_base_price', $price );
		}

		/**
		 * Set product price to the bundle metadata.
		 *
		 * @param int                $post_id Post ID.
		 * @param WCPBC_Pricing_Zone $zone Pricing zone instance..
		 */
		public static function set_by_exchange_rate_metadata( $post_id, $zone ) {
			$regular_price = $zone->get_exchange_rate_price_by_post( $post_id, '_wc_pb_base_regular_price' );
			$sale_price    = $zone->get_exchange_rate_price_by_post( $post_id, '_wc_pb_base_sale_price' );
			$price         = $zone->get_exchange_rate_price_by_post( $post_id, '_wc_pb_base_price' );

			$zone->set_postmeta( $post_id, '_wc_pb_base_regular_price', $regular_price );
			$zone->set_postmeta( $post_id, '_wc_pb_base_sale_price', $sale_price );
			$zone->set_postmeta( $post_id, '_wc_pb_base_price', $price );
		}

		/**
		 * Update the order line item subtotal before save.
		 *
		 * @since 2.5.2
		 * @param WC_Order_Line_Item $line_item Order line item instance.
		 * @param WC_Product         $product Product instance, of the line item.
		 */
		public static function manual_order_before_line_save( $line_item, $product ) {
			if ( ! ( function_exists( 'wc_pb_is_bundled_order_item' ) && function_exists( 'wc_pb_get_bundled_order_item_container' ) && wc_pb_is_bundled_order_item( $line_item ) ) ) {
				return;
			}
			$container_line_item = wc_pb_get_bundled_order_item_container( $line_item );
			$container_product   = $container_line_item->get_product();
			$bundled_item_id     = $line_item->get_meta( '_bundled_item_id' );
			$bundled_item        = $container_product->get_bundled_item( $bundled_item_id );
			if ( $bundled_item->is_priced_individually() ) {
				$bundled_item_discount = $bundled_item->get_discount();
				if ( $bundled_item_discount ) {
					$subtotal = wc_get_price_excluding_tax( $product, array( 'qty' => $bundled_item->get_quantity() * $container_line_item->get_quantity() ) ) * ( 1 - (float) $bundled_item_discount / 100 );
					$line_item->set_total( $subtotal );
					$line_item->set_subtotal( $subtotal );
				}
			} else {
				$line_item->set_total( 0 );
				$line_item->set_subtotal( 0 );
			}
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
			if ( $is_single && 'bundle' === $product->get_type() ) {

				$data['bundle_price_data'] = $product->get_bundle_price_data();
				$data['bundled_items']     = array();

				foreach ( $product->get_bundled_items() as $bundled_item ) {
					$bundled_item->add_price_filters();

					$data['bundled_items'][ $bundled_item->product->get_id() ] = array(
						'id'         => $bundled_item->product->get_id(),
						'price_html' => $bundled_item->product->get_price_html(),
						'variations' => '',
					);

					if ( $bundled_item->product->get_type() === 'variable' || $bundled_item->product->get_type() === 'variable-subscription' ) {
						$data['bundled_items'][ $bundled_item->product->get_id() ]['variations'] = $bundled_item->get_product_variations();
					}

					$bundled_item->remove_price_filters();
				}
			}
			return $data;
		}
	}
endif;

WCPBC_Bundles::init();

