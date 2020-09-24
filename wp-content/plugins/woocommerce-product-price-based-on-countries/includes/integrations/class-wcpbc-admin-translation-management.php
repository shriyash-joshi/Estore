<?php
/**
 * Handle compatibility with WPML
 *
 * @package WCPBC
 * @version 2.0.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * WCPBC_Admin_Translation_Management Class
 */
class WCPBC_Admin_Translation_Management {

	/**
	 * Returns the metakeys.
	 */
	private static function get_metakeys() {
		return array( '_price', '_regular_price', '_sale_price', '_sale_price_dates_from', '_sale_price_dates_to', '_price_method', '_sale_price_dates' );
	}
	/**
	 * Get Custom plugin fields.
	 *
	 * @param array $wpml_config WPML config array.
	 */
	public static function add_custom_fields( $wpml_config ) {
		$meta_keys = self::get_metakeys();

		foreach ( WCPBC_Pricing_Zones::get_zones() as $zone ) {
			foreach ( $meta_keys as $field ) {
				$wpml_config['wpml-config']['custom-fields']['custom-field'][] = array(
					'value' => $zone->get_postmetakey( $field ),
					'attr'  => array(
						'action' => 'copy',
					),
				);
			}
		}

		return $wpml_config;
	}

	/**
	 * Fields to lock in non-original products.
	 *
	 * @param array $fields Fields.
	 */
	public static function js_lock_fields_ids( $fields ) {
		$meta_keys = self::get_metakeys();

		foreach ( WCPBC_Pricing_Zones::get_zones() as $zone ) {
			foreach ( $meta_keys as $field ) {
				$fields[] = $zone->get_postmetakey( $field );
			}
		}
		return $fields;
	}

	/**
	 * Classes to lock in non-original products.
	 *
	 * @param array $classes Classes.
	 */
	public static function lock_fields_classes( $classes ) {
		$classes[] = '_price_method_wcpbc_field';
		$classes[] = '_sale_price_dates_wcpbc_field';
		return $classes;
	}
}
add_filter( 'wpml_config_array', 'WCPBC_Admin_Translation_Management::add_custom_fields' );
add_filter( 'wcml_js_lock_fields_ids', 'WCPBC_Admin_Translation_Management::js_lock_fields_ids' );
add_filter( 'wcml_js_lock_fields_classes', 'WCPBC_Admin_Translation_Management::lock_fields_classes' );
