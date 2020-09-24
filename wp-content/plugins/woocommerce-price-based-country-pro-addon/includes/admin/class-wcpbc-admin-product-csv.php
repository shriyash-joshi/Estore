<?php
/**
 * Handle product Importer and exporter integration.
 *
 * @version 2.5.0
 * @package WCPBC
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WCPBC_Admin_Product_CSV' ) ) :

	/**
	 * WCPBC_Admin_Product_CSV
	 */
	class WCPBC_Admin_Product_CSV {

		/**
		 * Metakeys to import/export.
		 *
		 * @var array
		 */
		private static $metakeys = array(
			'_regular_price'         => '%s regular price (%s)',
			'_sale_price'            => '%s sale price (%s)',
			'_sale_price_dates_from' => '%s date sale price starts',
			'_sale_price_dates_to'   => '%s date sale price ends',
		);

		/**
		 * Array of Pricing zones.
		 *
		 * @var array
		 */
		private static $zones = false;

		/**
		 * Array of zone fields.
		 *
		 * @var array
		 */
		private static $zone_fields = false;

		/**
		 * Init hooks.
		 */
		public static function init() {
			add_filter( 'woocommerce_product_export_column_names', array( __CLASS__, 'column_mapping' ) );
			add_filter( 'woocommerce_product_export_product_default_columns', array( __CLASS__, 'column_mapping' ) );
			add_filter( 'woocommerce_csv_product_import_mapping_options', array( __CLASS__, 'column_mapping' ) );
			add_filter( 'woocommerce_csv_product_import_mapping_default_columns', array( __CLASS__, 'csv_product_import_mapping_default_columns' ) );
			add_filter( 'woocommerce_product_import_pre_insert_product_object', array( __CLASS__, 'process_import' ), 10, 2 );
			self::add_export_column_filters();
		}

		/**
		 * Return the pricing zones
		 *
		 * @param string $id Zone Id.
		 * @return array
		 */
		private static function get_zones( $id = '' ) {
			$value = false;

			if ( ! self::$zones ) {
				self::$zones = WCPBC_Pricing_Zones::get_zones();
			}
			if ( ! $id ) {
				$value = self::$zones;
			} elseif ( ! empty( self::$zones[ $id ] ) ) {
				$value = self::$zones[ $id ];
			}

			return $value;
		}

		/**
		 * Return the pricing zones fields
		 *
		 * @param string $id Field id.
		 * @return array
		 */
		private static function get_zone_field( $id = '' ) {

			if ( ! self::$zone_fields ) {
				// generate zone fields.
				self::$zone_fields = array();
				foreach ( self::get_zones() as $zone ) {
					foreach ( array_keys( self::$metakeys ) as $metakey ) {
						self::$zone_fields[ $zone->get_postmetakey( $metakey ) ] = array(
							'metakey' => $metakey,
							'zone'    => $zone,
						);
					}
				}
			}

			return isset( self::$zone_fields[ $id ] ) ? self::$zone_fields[ $id ] : false;
		}

		/**
		 *  Add the needed filter to export pricing column
		 */
		private static function add_export_column_filters() {

			foreach ( self::get_zones() as $zone ) {
				foreach ( self::$metakeys as $metakey => $label ) {
					add_filter( 'woocommerce_product_export_product_column_' . $zone->get_postmetakey( $metakey ), array( __CLASS__, 'process_export' ), 10, 3 );
				}
			}
		}

		/**
		 * Register columns in the importer/exporter.
		 *
		 * @param array $columns Columns names.
		 * @return array $columns
		 */
		public static function column_mapping( $columns ) {

			foreach ( self::get_zones() as $zone ) {
				foreach ( self::$metakeys as $metakey => $label ) {
					$columns[ $zone->get_postmetakey( $metakey ) ] = sprintf( $label, $zone->get_name(), $zone->get_currency() );
				}
			}

			return $columns;
		}

		/**
		 * Add automatic mapping support for 'Custom Column'.
		 * This will automatically select the correct mapping for columns named 'Custom Column' or 'custom column'.
		 *
		 * @param array $columns Default columns.
		 * @return array $columns
		 */
		public static function csv_product_import_mapping_default_columns( $columns ) {

			foreach ( self::get_zones() as $zone ) {
				foreach ( self::$metakeys as $metakey => $label ) {
					$index             = sprintf( $label, $zone->get_name(), $zone->get_currency() );
					$columns[ $index ] = $zone->get_postmetakey( $metakey );
				}
			}

			return $columns;
		}

		/**
		 * Provide the data to be exported for one item in the column.
		 *
		 * @param mixed      $value Column value. Default: ''.
		 * @param WC_Product $product Product instance.
		 * @param string     $column_id Column Id.
		 * @return mixed $value
		 */
		public static function process_export( $value, $product, $column_id ) {

			if ( in_array( $product->get_type(), WCPBC_Product_Sync::get_parent_product_types(), true ) ) {
				return '';
			}

			$zone_field = self::get_zone_field( $column_id );

			if ( $zone_field ) {

				$metakey = $zone_field['metakey'];
				$zone    = $zone_field['zone'];

				if ( '_price_method' === $metakey ) {
					$value = $zone->is_exchange_rate_price( $product->get_id() ) ? 'yes' : 'no';
				} elseif ( in_array( $metakey, array( '_regular_price', '_sale_price' ), true ) ) {
					$value = wc_format_localized_price( $zone->get_post_price( $product->get_id(), $metakey ) );
				} else {
					$value = $zone->get_postmeta( $product->get_id(), $metakey );
					$value = ! empty( $value ) ? date_i18n( 'Y-m-d', $value ) : '';
				}
			}

			return $value;
		}

		/**
		 * Process the data read from the CSV file.
		 *
		 * @param WC_Product $product Product being imported or updated.
		 * @param array      $data CSV data read for the product.
		 * @return WC_Product $product
		 */
		public static function process_import( $product, $data ) {

			if ( in_array( $product->get_type(), WCPBC_Product_Sync::get_parent_product_types(), true ) ) {
				return $product;
			}

			foreach ( self::get_zones() as $zone ) {

				$metavalues   = array();
				$updated      = false;
				$date_updated = false;

				foreach ( array_keys( self::$metakeys ) as $metakey ) {

					if ( isset( $data[ $zone->get_postmetakey( $metakey ) ] ) ) {
						$metavalues[ $metakey ] = wc_clean( $data[ $zone->get_postmetakey( $metakey ) ] );
					}
				}
				if ( ! empty( $metavalues ) ) {
					$metavalues['_price_method']     = 'manual';
					$metavalues['_sale_price_dates'] = ( ! empty( $metavalues['_sale_price_dates_from'] ) && strtotime( $metavalues['_sale_price_dates_from'] ) ) ? 'manual' : 'default';

					wcpbc_update_product_pricing( $product->get_id(), $zone, $metavalues );

					do_action( 'wc_price_based_country_csv_import_product_' . $product->get_type(), $product->get_id(), $zone, $metavalues, $data );
				}
			}
			return $product;
		}
	}
endif;
