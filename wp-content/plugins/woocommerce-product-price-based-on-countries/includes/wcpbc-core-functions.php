<?php
/**
 * Core Functions
 *
 * @package WCPBC
 * @version 1.8.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Return the current pricing zone or false if the customer country does not match in a pricing zone.
 *
 * Alias of WCPBC()->current_zone.
 *
 * @since 1.8
 * @return WCPBC_Pricing_Zone
 */
function wcpbc_the_zone() {
	if ( wcpbc()->current_zone ) {
		return wcpbc()->current_zone;
	}
	return false;
}

/**
 * Return base currency
 *
 * @return string
 */
function wcpbc_get_base_currency() {
	return get_option( 'woocommerce_currency' );
}

/**
 * Check is WooCommerce frontend
 *
 * @since 1.6.6
 * @return bool
 */
function wcpbc_is_woocommerce_frontend() {
	return function_exists( 'WC' ) && ! empty( WC()->customer );
}

/**
 * Is the value exchange rate?
 *
 * @since 1.7.15
 * @param string $value Value to check.
 * @return bool
 */
function wcpbc_is_exchange_rate( $value = false ) {
	return empty( $value ) || 'exchange_rate' === $value;
}

/**
 * Return the object property value. Add compatibility with WC 2.6
 *
 * @deprecated 1.9.0
 * @since 1.8.0
 * @param mixed  $object The object instance.
 * @param string $prop_name Property name.
 * @return mixed
 */
function wcpbc_get_prop_value( $object, $prop_name ) {
	wc_deprecated_function( 'wcpbc_get_prop_value', '1.9.0' );

	$props   = is_array( $prop_name ) ? $prop_name : array( $prop_name );
	$value   = array();
	$mapping = array(
		'billing_country' => 'get_country',
		'parent_id'       => 'get_parent',
		'type'            => 'get_type',
		'amount'          => 'coupon_amount',
	);

	if ( version_compare( WC_VERSION, '3.0', '>=' ) ) {
		foreach ( $props as $prop ) {
			$get            = 'get_' . $prop;
			$value[ $prop ] = $object->{$get}();
		}
	} else {
		foreach ( $props as $prop ) {
			$get            = ! empty( $mapping[ $prop ] ) ? $mapping[ $prop ] : $prop;
			$value[ $prop ] = 'get_' === substr( $get, 0, 4 ) ? $object->{$get}() : $object->$get;
		}
	}

	if ( 1 === count( $value ) ) {
		$value = $value[ $prop_name ];
	}
	return $value;
}

/**
 * Set an object property value. Add compatibility with WC 2.6
 *
 * @since 1.8.0
 * @param mixed  $object The object instance.
 * @param string $prop_name Property name.
 * @param mixed  $value Property value.
 */
function wcpbc_set_prop_value( $object, $prop_name, $value ) {

	$mapping = array(
		'billing_country' => 'set_country',
		'amount'          => 'coupon_amount',
	);

	if ( version_compare( WC_VERSION, '3.0', '>=' ) ) {
		$set = 'set_' . $prop_name;
		$object->{$set}( $value );
	} else {
		$set = ! empty( $mapping[ $prop_name ] ) ? $mapping[ $prop_name ] : $prop_name;
		if ( 'set_' === substr( $get, 0, 4 ) ) {
			$object->{$set}( $value );
		} else {
			$object->{$set} = $value;
		}
	}
}

/**
 * Get WooCommerce customer country
 *
 * @return string
 */
function wcpbc_get_woocommerce_country() {

	$country = false;

	if ( wcpbc_is_woocommerce_frontend() ) {

		$country          = wc()->customer->get_billing_country();
		$shipping_country = wc()->customer->get_shipping_country();
		if ( ! empty( $shipping_country ) && $country !== $shipping_country && 'shipping' === get_option( 'wc_price_based_country_based_on', 'billing' ) ) {
			$country = $shipping_country;
		}
	}

	return $country;
}

/**
 * Set WooCommerce customer country
 *
 * @param string $country Customer country.
 */
function wcpbc_set_woocommerce_country( $country ) {

	if ( ! wcpbc_is_woocommerce_frontend() || ! in_array( $country, array_keys( wc()->countries->countries ), true ) ) {
		return;
	}

	$billing_country = wc()->customer->get_billing_country();
	$shipping_county = wc()->customer->get_shipping_country();

	if ( $billing_country !== $shipping_county && 'shipping' === get_option( 'wc_price_based_country_based_on', 'shipping' ) && apply_filters( 'woocommerce_ship_to_different_address_checked', get_option( 'woocommerce_ship_to_destination' ) === 'shipping' ? 1 : 0 ) ) {
		wc()->customer->set_shipping_country( $country );
	} else {
		wc()->customer->set_billing_country( $country );
		wc()->customer->set_shipping_country( $country );
	}
}

/**
 * Alias of WCPBC_Pricing_Zones::get_zone_by_country
 *
 * @since 1.7.0
 * @param string $country The country.
 * @return WCPBC_Pricing_Zone
 */
function wcpbc_get_zone_by_country( $country = '' ) {
	if ( ! class_exists( 'WCPBC_Pricing_Zones' ) ) {
		return false;
	}
	$country = empty( $country ) ? wcpbc_get_woocommerce_country() : $country;
	return WCPBC_Pricing_Zones::get_zone_by_country( $country );
}

/**
 * Return is a value is empty and no-zero
 *
 * @since 1.7.0
 * @param string $value The value to check.
 * @return bool
 */
function wcpbc_empty_nozero( $value ) {
	return ( empty( $value ) && ! ( is_numeric( $value ) && 0 === absint( $value ) ) ); // WPCS: loose comparison ok.
}

/**
 * Sort a array with locale-sensitive
 *
 * @since 1.6.0
 * @param array $arr Array to sort.
 * @return true
 */
function wcpbc_maybe_asort_locale( &$arr ) {

	try {
		$coll = function_exists( 'collator_create' ) ? collator_create( get_locale() ) : false;
		if ( $coll ) {
			return collator_asort( $coll, $arr );
		} else {
			return asort( $arr );
		}
	} catch ( Exception $e ) {
		return asort( $arr );
	}
}

/**
 *  Return a sanitize a $_SERVER var
 *
 * @since 1.7.10
 * @param string $value IP to sanitize.
 * @return string
 */
function wcpbc_sanitize_server_var( $value ) {
	return strtoupper( sanitize_text_field( wp_unslash( $value ) ) );
}

/**
 * Check if Maxmind GeoIP database exists
 *
 * @since 1.7.12
 * @return boolean
 */
function wcpbc_geoipdb_exists() {
	$exists = ! empty( $_SERVER['HTTP_CF_IPCOUNTRY'] ) || ! empty( $_SERVER['GEOIP_COUNTRY_CODE'] ) || ! empty( $_SERVER['HTTP_X_COUNTRY_CODE'] );
	if ( ! $exists ) {
		if ( version_compare( WC_VERSION, '3.9', '<' ) && is_callable( array( 'WC_Geolocation', 'get_local_database_path' ) ) ) {
			$database = WC_Geolocation::get_local_database_path();
			$exists   = file_exists( $database );
		} else {
			$maxmind_geolocation = WC()->integrations->get_integration( 'maxmind_geolocation' );
			if ( $maxmind_geolocation && is_callable( array( $maxmind_geolocation, 'get_database_service' ) ) ) {
				$database = $maxmind_geolocation->get_database_service()->get_database_path();
				$exists   = file_exists( $database );
			}
		}
	}
	return $exists;
}

/**
 * Is Pro version
 *
 * @since 1.6.11
 * return boolean
 */
function wcpbc_is_pro() {
	return class_exists( 'WC_Product_Price_Based_Country_Pro' );
}

/**
 * Return an array of product type supported
 *
 * @since 1.7.0
 * @param string $source basic|pro|third-party.
 * @param string $context Context to use the function.
 * @return boolean
 */
function wcpbc_product_types_supported( $source = '', $context = '' ) {

	$types = array(
		'basic' => array(
			'simple'   => 'Simple product',
			'grouped'  => 'Grouped product',
			'external' => 'External/Affiliate product',
			'variable' => 'Variable product',
		),
		'pro'   => array(
			'bundle'                => 'WooCommerce Product Bundles',
			'booking'               => 'WooCommerce Bookings',
			'accommodation-booking' => 'WooCommerce Accommodation Bookings',
			'composite'             => 'WooCommerce Composite Products',
			'nyp-wcpbc'             => 'WooCommerce Name Your Price',
			'job_package'           => 'Listing Payments by Astoundify',
		),
	);

	if ( 'product-data' !== $context || ! class_exists( 'WCPBC_Subscriptions' ) ) {
		$types['pro']['subscription']          = 'WooCommerce Subscriptions';
		$types['pro']['variable-subscription'] = 'WooCommerce Subscriptions';
	}

	$types['third-party'] = apply_filters( 'wc_price_based_country_third_party_product_types', array() );

	if ( empty( $source ) ) {
		$types = array_merge( $types['basic'], $types['pro'], $types['third-party'] );
	} elseif ( 'pro' === $source ) {
		$types = $types['pro'];
	} elseif ( 'basic' === $source ) {
		$types = $types['basic'];
	} else {
		$types = $types['third-party'];
	}

	return $types;
}

/**
 * Return the price method options.
 *
 * @since 1.8.0
 * @return array
 */
function wcpbc_price_method_options() {
	return array(
		'exchange_rate' => __( 'Calculate prices by the exchange rate', 'woocommerce-product-price-based-on-countries' ),
		'manual'        => __( 'Set prices manually', 'woocommerce-product-price-based-on-countries' ),
	);
}

/**
 * Return price method label.
 *
 * @since 1.8.0
 * @param string             $text Text to construct the price method label.
 * @param WCPBC_Pricing_Zone $zone Pricing zone instance.
 * @return string
 */
function wcpbc_price_method_label( $text, $zone ) {
	return $text . ' ' . str_replace( ' ', '&nbsp;', sprintf( '%s (%s)', $zone->get_name(), get_woocommerce_currency_symbol( $zone->get_currency() ) ) );
}

/**
 * Output a product pricing input control.
 *
 * @since 1.8.0
 * @deprecated typographic error
 * @param array              $field Field arguments.
 * @param WCPBC_Pricing_Zone $zone Pricig zone instance.
 */
function wcpbc_princing_input( $field, $zone ) {
	if ( function_exists( 'wc_deprecated_function' ) ) {
		wc_deprecated_function( 'wcpbc_princing_input', '1.8.15', 'wcpbc_pricing_input' );
	} else {
		_deprecated_function( 'wcpbc_princing_input', '1.8.15', 'wcpbc_pricing_input' );
	}
	wcpbc_pricing_input( $field, $zone );
}

/**
 * Output a product pricing input control.
 *
 * @since 1.8.15
 * @param array              $field Field arguments.
 * @param WCPBC_Pricing_Zone $zone Pricig zone instance.
 */
function wcpbc_pricing_input( $field, $zone ) {
	global $thepostid, $post;
	$thepostid = empty( $thepostid ) ? $post->ID : $thepostid; // phpcs:ignore WordPress.NamingConventions

	$field['name']           = empty( $field['name'] ) ? '_price_method' : $field['name'];
	$field['id']             = empty( $field['id'] ) ? str_replace( array( '[', ']' ), array( '_', '' ), $field['name'] ) : $field['id'];
	$field['value']          = empty( $field['value'] ) ? $zone->get_postmeta( $thepostid, $field['name'] ) : $field['value'];
	$field['label']          = empty( $field['label'] ) ? __( 'Price for', 'woocommerce-product-price-based-on-countries' ) : $field['label'];
	$field['fields']         = isset( $field['fields'] ) && is_array( $field['fields'] ) ? $field['fields'] : array();
	$field['wrapper']        = isset( $field['wrapper'] ) ? $field['wrapper'] : true;
	$field['wrapper_class']  = empty( $field['wrapper_class'] ) ? '' : $field['wrapper_class'];
	$field['wrapper_class'] .= ' wcpbc_pricing wcpbc_pricing_' . $zone->get_zone_id();

	if ( $field['wrapper'] ) {
		echo '<div class="' . esc_attr( $field['wrapper_class'] ) . '">';
	}

	woocommerce_wp_radio(
		array(
			'id'            => $zone->get_postmetakey( $field['id'] ),
			'name'          => $zone->get_postmetakey( $field['name'] ),
			'value'         => wcpbc_is_exchange_rate( $field['value'] ) ? 'exchange_rate' : 'manual',
			'class'         => 'wcpbc_price_method',
			'label'         => wcpbc_price_method_label( $field['label'], $zone ),
			'wrapper_class' => '_price_method_wcpbc_field',
			'options'       => wcpbc_price_method_options(),
		)
	);

	foreach ( $field['fields'] as $child_field ) {
		$child_field['name']              = empty( $child_field['name'] ) ? '' : $child_field['name'];
		$child_field['id']                = empty( $child_field['id'] ) ? str_replace( array( '[', ']' ), array( '_', '' ), $child_field['name'] ) : $child_field['id'];
		$child_field['label']             = empty( $child_field['label'] ) ? '' : sprintf( $child_field['label'], get_woocommerce_currency_symbol( $zone->get_currency() ) );
		$child_field['value']             = isset( $child_field['value'] ) ? $child_field['value'] : $zone->get_postmeta( $thepostid, $child_field['name'] );
		$child_field['type']              = empty( $child_field['type'] ) ? 'text' : $child_field['type'];
		$child_field['data_type']         = isset( $child_field['data_type'] ) ? $child_field['data_type'] : '';
		$child_field['custom_attributes'] = isset( $child_field['custom_attributes'] ) && is_array( $child_field['custom_attributes'] ) ? $child_field['custom_attributes'] : array();
		$child_field['wrapper_class']     = empty( $child_field['wrapper_class'] ) ? ' ' : $child_field['wrapper_class'] . ' ';
		$child_field['wrapper_class']    .= $child_field['id'] . '_wcpbc_field wcpbc_show_if_manual';

		if ( empty( $child_field['data_type'] ) && 'text' === $child_field['type'] ) {
			$child_field['data_type'] = 'price';
		}
		if ( empty( $child_field['value'] ) && isset( $child_field['default_value'] ) ) {
			$child_field['value'] = $child_field['default_value'];
		}
		if ( 'date' === $child_field['data_type'] ) {
			$child_field['custom_attributes']['maxlength'] = '10';
			$child_field['custom_attributes']['pattern']   = apply_filters( 'woocommerce_date_input_html_pattern', '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])' );
			$child_field['value']                          = empty( $child_field['value'] ) ? '' : date_i18n( 'Y-m-d', $child_field['value'] + ( floatval( get_option( 'gmt_offset', 0 ) ) * HOUR_IN_SECONDS ) );
		}

		$child_field['id']   = $zone->get_postmetakey( $child_field['id'] );
		$child_field['name'] = $zone->get_postmetakey( $child_field['name'] );

		// Output the field.
		if ( 'radio' === $child_field['type'] ) {
			woocommerce_wp_radio( $child_field );
		} else {
			woocommerce_wp_text_input( $child_field );
		}
	}

	if ( $field['wrapper'] ) {
		echo '</div>';
	}
}

/**
 * Return timestamp from a date string.
 *
 * @since 1.8.18
 * @param string $value Date as string.
 * @return int
 */
function wcpbc_string_to_timestamp( $value ) {
	if ( version_compare( WC_VERSION, '3.0', '>=' ) ) {
		$timestamp = wc_string_to_timestamp( get_gmt_from_date( gmdate( 'Y-m-d H:i:s', wc_string_to_timestamp( $value ) ) ) );
	} else {
		$timestamp = strotime( $value );
	}
	return $timestamp;
}

/**
 * Save the product pricing for a zone
 *
 * @since 1.8.0
 * @param int                $post_id Post ID.
 * @param WCPBC_Pricing_Zone $zone Pricig zone instance.
 * @param array              $data The product pricing.
 */
function wcpbc_update_product_pricing( $post_id, $zone, $data = array() ) {

	if ( empty( $data['_price_method'] ) || wcpbc_is_exchange_rate( $data['_price_method'] ) ) {
		// Exchange rate.
		$data['_price_method']          = 'exchange_rate';
		$data['_regular_price']         = $zone->get_exchange_rate_price_by_post( $post_id, '_regular_price' );
		$data['_sale_price']            = $zone->get_exchange_rate_price_by_post( $post_id, '_sale_price' );
		$data['_price']                 = $zone->get_exchange_rate_price_by_post( $post_id, '_price' );
		$data['_sale_price_dates']      = 'default';
		$data['_sale_price_dates_from'] = '';
		$data['_sale_price_dates_to']   = '';

	} else {
		// Manual.
		$data['_price_method']     = 'manual';
		$data['_regular_price']    = isset( $data['_regular_price'] ) ? $data['_regular_price'] : $zone->get_postmeta( $post_id, '_regular_price' );
		$data['_sale_price']       = isset( $data['_sale_price'] ) ? $data['_sale_price'] : $zone->get_postmeta( $post_id, '_sale_price' );
		$data['_sale_price_dates'] = isset( $data['_sale_price_dates'] ) ? $data['_sale_price_dates'] : $zone->get_postmeta( $post_id, '_sale_price_dates' );

		// Sale Dates.
		if ( 'manual' !== $data['_sale_price_dates'] ) {
			$data['_sale_price_dates']      = 'default';
			$data['_sale_price_dates_from'] = get_post_meta( $post_id, '_sale_price_dates_from', true );
			$data['_sale_price_dates_to']   = get_post_meta( $post_id, '_sale_price_dates_to', true );
		} else {

			// Force date from to beginning of day.
			if ( isset( $data['_sale_price_dates_from'] ) ) {
				if ( ! empty( $data['_sale_price_dates_from'] ) ) {
					$data['_sale_price_dates_from'] = wcpbc_string_to_timestamp( date( 'Y-m-d 00:00:00', strtotime( $data['_sale_price_dates_from'] ) ) );
				}
			} else {
				$data['_sale_price_dates_from'] = $zone->get_postmeta( $post_id, '_sale_price_dates_from' );
			}

			// Force date to to the end of the day.
			if ( isset( $data['_sale_price_dates_to'] ) ) {
				if ( ! empty( $data['_sale_price_dates_to'] ) ) {
					$data['_sale_price_dates_to'] = wcpbc_string_to_timestamp( date( 'Y-m-d 23:59:59', strtotime( $data['_sale_price_dates_to'] ) ) );
				}
			} else {
				$data['_sale_price_dates_to'] = $zone->get_postmeta( $post_id, '_sale_price_dates_to' );
			}
		}

		$data['_regular_price'] = wc_format_decimal( $data['_regular_price'] );
		$data['_sale_price']    = wc_format_decimal( $data['_sale_price'] );

		// Update price if on sale.
		$current_time = current_time( 'timestamp', true );

		if ( ! empty( $data['_sale_price_dates_to'] ) && empty( $data['_sale_price_dates_from'] ) ) {
			$data['_sale_price_dates_from'] = $current_time;
		}

		if ( ! wcpbc_empty_nozero( $data['_sale_price'] ) && empty( $data['_sale_price_dates_to'] ) && empty( $data['_sale_price_dates_from'] ) ) {
			$data['_price'] = $data['_sale_price'];
		} elseif ( ! wcpbc_empty_nozero( $data['_sale_price'] ) && $data['_sale_price_dates_from'] && $data['_sale_price_dates_from'] <= $current_time ) {
			$data['_price'] = $data['_sale_price'];
		} else {
			$data['_price'] = $data['_regular_price'];
		}

		if ( $data['_sale_price_dates_to'] && $data['_sale_price_dates_to'] < $current_time ) {
			$data['_price']                 = $data['_regular_price'];
			$data['_sale_price']            = '';
			$data['_sale_price_dates_from'] = '';
			$data['_sale_price_dates_to']   = '';
		}
	}

	// Save metadata.
	$zone->set_postmeta( $post_id, '_price_method', $data['_price_method'] );
	$zone->set_postmeta( $post_id, '_regular_price', $data['_regular_price'] );
	$zone->set_postmeta( $post_id, '_sale_price', $data['_sale_price'] );
	$zone->set_postmeta( $post_id, '_price', $data['_price'] );
	$zone->set_postmeta( $post_id, '_sale_price_dates', $data['_sale_price_dates'] );
	$zone->set_postmeta( $post_id, '_sale_price_dates_from', $data['_sale_price_dates_from'] );
	$zone->set_postmeta( $post_id, '_sale_price_dates_to', $data['_sale_price_dates_to'] );
}


/**
 * Built a SELECT CASE expression to uses in order querys
 *
 * @since 1.8.0
 * @param string $field Field name to multiply by exchange rate.
 * @param array  $rates Array of currency exchange rates.
 * @return string
 */
function wcpbc_built_query_case( $field, $rates ) {

	$case_ex = ' CASE meta__order_currency.meta_value ';
	foreach ( $rates as $currency => $rate ) {
		$case_ex .= "WHEN '{$currency}' THEN ( {$field} / ({$rate})) ";
	}
	$case_ex .= "ELSE {$field} END ";

	return $case_ex;
}

/**
 * Built a JOIN query expression to uses in order querys.
 *
 * @since 1.8.0
 * @param string $from_table From table name.
 * @param string $join_type Join type (INNER, LEFT or RIGHT).
 * @param string $id_field Order ID field. Default "ID".
 * @return string
 */
function wcpbc_built_join_meta_currency( $from_table = false, $join_type = 'INNER', $id_field = 'ID' ) {
	global $wpdb;

	$from_table = $from_table ? $from_table : 'posts';
	return ' ' . $join_type . " JOIN {$wpdb->postmeta} AS meta__order_currency ON ( {$from_table}.{$id_field} = meta__order_currency.post_id AND meta__order_currency.meta_key = '_order_currency' ) ";
}

/**
 * Returns the parent product types (variable, grouped).
 *
 * @return array
 */
function wcpbc_get_parent_product_types() {
	wc_deprecated_function( 'wcpbc_get_parent_product_types', '2.0.0', 'WCPBC_Product_Sync::get_parent_product_types' );
	if ( is_callable( array( 'WCPBC_Product_Sync', 'get_parent_product_types' ) ) ) {
		return WCPBC_Product_Sync::get_parent_product_types();
	}
	return array();
}


/**
 * Return a a array with all currencies avaiables in WooCommerce with associate countries
 *
 * @param string $currency_code Currency code.
 * @return array
 */
function wcpbc_get_currencies_countries( $currency_code = false ) {

	$currencies = array(
		'AED' => array( 'AE' ),
		'ARS' => array( 'AR' ),
		'AUD' => array( 'AU', 'CC', 'CX', 'HM', 'KI', 'NF', 'NR', 'TV' ),
		'BDT' => array( 'BD' ),
		'BRL' => array( 'BR' ),
		'BGN' => array( 'BG' ),
		'CAD' => array( 'CA' ),
		'CLP' => array( 'CL' ),
		'CNY' => array( 'CN' ),
		'COP' => array( 'CO' ),
		'CZK' => array( 'CZ' ),
		'DKK' => array( 'DK', 'FO', 'GL' ),
		'DOP' => array( 'DO' ),
		'EUR' => array( 'AD', 'AT', 'AX', 'BE', 'BL', 'CY', 'DE', 'EE', 'ES', 'FI', 'FR', 'GF', 'GP', 'GR', 'IE', 'IT', 'LT', 'LU', 'LV', 'MC', 'ME', 'MF', 'MQ', 'MT', 'NL', 'PM', 'PT', 'RE', 'SI', 'SK', 'SM', 'TF', 'VA', 'YT' ),
		'HKD' => array( 'HK' ),
		'HRK' => array( 'HR' ),
		'HUF' => array( 'HU' ),
		'ISK' => array( 'IS' ),
		'IDR' => array( 'ID' ),
		'INR' => array( 'IN' ),
		'NPR' => array( 'NP' ),
		'ILS' => array( 'IL' ),
		'JPY' => array( 'JP' ),
		'KIP' => array( 'LA' ),
		'KRW' => array( 'KR' ),
		'MYR' => array( 'MY' ),
		'MXN' => array( 'MX' ),
		'NGN' => array( 'NG' ),
		'NOK' => array( 'BV', 'NO', 'SJ' ),
		'NZD' => array( 'CK', 'NU', 'NZ', 'PN', 'TK' ),
		'PYG' => array( 'PY' ),
		'PHP' => array( 'PH' ),
		'PLN' => array( 'PL' ),
		'GBP' => array( 'GB', 'GG', 'GS', 'IM', 'JE' ),
		'RON' => array( 'RO' ),
		'RUB' => array( 'RU' ),
		'SGD' => array( 'SG' ),
		'ZAR' => array( 'ZA' ),
		'SEK' => array( 'SE' ),
		'CHF' => array( 'LI' ),
		'TWD' => array( 'TW' ),
		'THB' => array( 'TH' ),
		'TRY' => array( 'TR' ),
		'UAH' => array( 'UA' ),
		'USD' => array( 'BQ', 'EC', 'FM', 'IO', 'MH', 'PW', 'TC', 'TL', 'US', 'VG' ),
		'VND' => array( 'VN' ),
		'EGP' => array( 'EG' ),
	);

	if ( $currency_code && array_key_exists( $currency_code, $currencies ) ) {
		$currencies = $currencies[ $currency_code ];
	}

	return $currencies;
}
