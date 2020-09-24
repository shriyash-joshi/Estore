<?php
/**
 * Updates
 *
 * Functions for updating data.
 *
 * @package WCPBC
 */

defined( 'ABSPATH' ) || exit;

/**
 * Update db to 1.3.2
 */
function wcpbc_update_132() {
	global $wpdb;

	$regions = get_option( '_oga_wppbc_countries_groups' );

	if ( ! $regions ) {
		$regions = array();
	}

	foreach ( $regions as $region_key => $region ) {

		if ( empty( $regions[ $region_key ]['exchange_rate'] ) ) {
			$regions[ $region_key ]['exchange_rate'] = 1;
		}

		unset( $regions[ $region_key ]['empty_price_method'] );
	}

	delete_option( '_oga_wppbc_countries_groups' );
	add_option( 'wc_price_based_country_regions', $regions );

	add_option( 'wc_price_based_country_test_mode', get_option( 'wc_price_based_country_debug_mode' ) );
	delete_option( 'wc_price_based_country_debug_mode' );

	$test_ip = get_option( 'wc_price_based_country_debug_ip' );
	if ( $test_ip ) {
		$country = WC_Geolocation::geolocate_ip( $test_ip );
		add_option( 'wc_price_based_country_test_country', $country['country'] );
	}
	delete_option( 'wc_price_based_country_debug_ip' );

	if ( wp_next_scheduled( 'wcpbc_update_geoip' ) ) {
		wp_clear_scheduled_hook( 'wcpbc_update_geoip' );
	}

	delete_option( 'wc_price_based_country_update_geoip' );
	delete_option( '_oga_wppbc_apiurl' );
	delete_option( '_oga_wppbc_api_country_field' );

	// Delete geoip db.
	$geoip_db_dir = wp_upload_dir();
	$geoip_db_dir = $geoip_db_dir['basedir'] . '/wc_price_based_country';

	if ( file_exists( $geoip_db_dir . '/GeoLite2-Country.mmdb' ) ) {
		@unlink( $geoip_db_dir . '/GeoLite2-Country.mmdb' );
		@rmdir( $geoip_db_dir );
	}
}

/**
 * Update db to 1.6.0
 */
function wcpbc_update_160() {
	global $wpdb;

	foreach ( WCPBC_Pricing_Zones::get_zones() as $zone ) {

		// Remove "_variable" prefix for prices meta keys.
		$zone_id = esc_attr( $zone->get_id() );
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$wpdb->postmeta} SET meta_key = replace(meta_key, '_variable', '') where meta_key like %s",
				"_{$zone_id}_variable_%"
			)
		);
	}

	// Update shipping option.
	$wc_price_based_shipping_conversion = get_option( 'wc_price_based_shipping_conversion', 'no' );
	update_option( 'wc_price_based_country_shipping_exchange_rate', $wc_price_based_shipping_conversion );

	// Delete deprecated option.
	delete_option( 'wc_price_based_country_hide_ads' );
	delete_option( 'wc_price_based_shipping_conversion' );
}

/**
 * Update db to 1.6.2
 */
function wcpbc_update_162() {

	foreach ( WCPBC_Pricing_Zones::get_zones() as $zone ) {
		$zone_id = $zone->get_id();

		/**
		 * Get variable products without price
		 */
		$products = get_posts(
			array(
				'fields'      => 'ids',
				'numberposts' => -1,
				'post_type'   => 'product',
				'meta_query'  => array(
					'relation' => 'AND',
					array(
						'key'     => "_{$zone_id}_price_method",
						'value'   => 'nothing',
						'compare' => '=',
					),
					array(
						'key'     => "_{$zone_id}_price",
						'compare' => 'NOT EXISTS',
					),
				),
			)
		);

		if ( ! empty( $products ) ) {
			WCPBC_Product_Sync::parent_product_price_sync(
				array(
					'zone'        => $zone_id,
					'product_ids' => $products,
				)
			);
		}
	}
}

/**
 * Update db to 1.8.21
 */
function wcpbc_update_1821() {
	if ( version_compare( WC_VERSION, '3.4', '>=' ) && version_compare( WC_VERSION, '3.9', '<' ) ) {
		WCPBC_Update_GeoIP_DB::install();
	}
}

/**
 * Update db to 2.0.0
 */
function wcpbc_update_200() {
	// Invalidate the WooCommerce Admin cache.
	if ( is_callable( array( 'WC_Cache_Helper', 'get_transient_version' ) ) ) {
		WC_Cache_Helper::get_transient_version( 'woocommerce_reports', true );
	}
}
