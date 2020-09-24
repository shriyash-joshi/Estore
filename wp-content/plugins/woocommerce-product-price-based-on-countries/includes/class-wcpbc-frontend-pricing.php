<?php
/**
 * Front-end pricing.
 *
 * @version 1.8.6
 * @package WCPBC
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WCPBC_Frontend_Pricing class.
 */
class WCPBC_Frontend_Pricing {

	/**
	 * Init the frontend pricing
	 */
	public static function init() {
		if ( ! wcpbc_the_zone() ) {
			return;
		}
		self::init_hooks();
		do_action( 'wc_price_based_country_frontend_princing_init' );
	}

	/**
	 * Hook actions and filters
	 *
	 * @since 1.7.0
	 */
	private static function init_hooks() {
		self::add_product_properties_filters();

		add_filter( 'woocommerce_currency', array( __CLASS__, 'get_currency' ), 100 );
		add_filter( 'woocommerce_get_variation_prices_hash', array( __CLASS__, 'get_variation_prices_hash' ) );
		add_filter( 'woocommerce_add_cart_item', array( __CLASS__, 'set_cart_item_price' ), -10 );
		add_filter( 'woocommerce_get_cart_item_from_session', array( __CLASS__, 'set_cart_item_price' ), -10 );
		add_filter( 'woocommerce_get_catalog_ordering_args', array( __CLASS__, 'get_catalog_ordering_args' ) );
		add_filter( 'posts_clauses', array( __CLASS__, 'filter_price_post_clauses' ), 25, 2 );
		add_filter( 'the_posts', array( __CLASS__, 'remove_product_query_filters' ) );
		add_filter( 'woocommerce_product_query_meta_query', array( __CLASS__, 'product_query_meta_query' ), 10, 2 );
		add_filter( 'woocommerce_price_filter_meta_keys', array( __CLASS__, 'price_filter_meta_keys' ) );
		add_filter( 'woocommerce_price_filter_sql', array( __CLASS__, 'price_filter_sql' ) );
		add_filter( 'pre_transient_wc_products_onsale', array( __CLASS__, 'product_ids_on_sale' ), 10, 2 );
		add_filter( 'woocommerce_shortcode_products_query', array( __CLASS__, 'get_variation_prices_hash' ) );
		add_filter( 'woocommerce_package_rates', array( __CLASS__, 'package_rates' ), 10, 2 );
		add_filter( 'woocommerce_shipping_zone_shipping_methods', array( __CLASS__, 'shipping_zone_shipping_methods' ), 10, 4 );
		add_filter( 'woocommerce_adjust_non_base_location_prices', array( __CLASS__, 'adjust_non_base_location_prices' ) );
		add_action( 'woocommerce_coupon_loaded', array( __CLASS__, 'coupon_loaded' ) );
		add_action( 'woocommerce_new_order', array( __CLASS__, 'update_order_meta' ) );
	}

	/**
	 * Add product properties filters.
	 */
	public static function add_product_properties_filters() {

		foreach ( array( 'regular_price', 'sale_price', 'price' ) as $prop ) {
			add_filter( 'woocommerce_product_get_' . $prop, array( __CLASS__, 'get_product_price_property' ), 5, 2 );
			add_filter( 'woocommerce_product_variation_get_' . $prop, array( __CLASS__, 'get_product_price_property' ), 5, 2 );
			add_filter( 'woocommerce_variation_prices_' . $prop, array( __CLASS__, 'get_product_price_property' ), 5, 2 );
		}

		foreach ( array( 'date_on_sale_from', 'date_on_sale_to' ) as $prop ) {
			add_filter( 'woocommerce_product_get_' . $prop, array( __CLASS__, 'get_product_date_property' ), 5, 2 );
			add_filter( 'woocommerce_product_variation_get_' . $prop, array( __CLASS__, 'get_product_date_property' ), 5, 2 );
		}

	}

	/**
	 * Is a supported product type?
	 *
	 * @param WC_Product $product Product instance.
	 * @return bool
	 */
	private static function is_supported_product( $product ) {
		$support = array_unique( apply_filters( 'wc_price_based_country_product_types_overriden', array( 'simple', 'variable', 'external', 'variation' ) ) );
		$type    = is_callable( array( $product, 'get_type' ) ) ? $product->get_type() : false;

		return ( in_array( $type, $support, true ) );
	}

	/**
	 * Returns the current metakey for the currenty filter.
	 *
	 * @param WC_Product $product Product instance.
	 * @return string Property name or False if overwrite is no needed.
	 */
	private static function get_metakey_from_filter( $product ) {
		$metakey = false;
		$prop    = str_replace( array( 'woocommerce_variation_prices_', 'woocommerce_product_variation_get_', 'woocommerce_product_get_' ), '', current_filter() );

		if ( ! array_key_exists( $prop, $product->get_changes() ) && self::is_supported_product( $product ) ) {
			$metakey    = $prop;
			$date_props = array(
				'date_on_sale_from' => 'sale_price_dates_from',
				'date_on_sale_to'   => 'sale_price_dates_to',
			);

			if ( isset( $date_props[ $prop ] ) ) {
				$metakey = $date_props[ $prop ];
			}

			$metakey = '_' === substr( $metakey, 0, 1 ) ? $metakey : '_' . $metakey;
		}
		return $metakey;
	}

	/**
	 * Retrun a product price property.
	 *
	 * @since 1.9.0
	 * @param mixed      $value Property value.
	 * @param WC_Product $product Product instance.
	 * @return mixed
	 */
	public static function get_product_price_property( $value, $product ) {
		$meta_key = self::get_metakey_from_filter( $product );
		if ( ! $meta_key ) {
			return $value;
		}

		return wcpbc_the_zone()->get_price_prop( $product, $value, $meta_key );
	}

	/**
	 * Retrun a product date property.
	 *
	 * @since 1.9.0
	 * @param mixed      $value Property value.
	 * @param WC_Product $product Product instance.
	 * @return mixed
	 */
	public static function get_product_date_property( $value, $product ) {
		$meta_key = self::get_metakey_from_filter( $product );

		if ( ! $meta_key ) {
			return $value;
		}

		return wcpbc_the_zone()->get_date_prop( $product, $value, $meta_key );
	}

	/**
	 * Return price meta data value
	 *
	 * @deprecated 1.9.0
	 * @param null|array|string $meta_value The value get_metadata() should return - a single metadata value or an array of values.
	 * @param int               $object_id Object ID.
	 * @param string            $meta_key Meta key.
	 * @param bool              $single Whether to return only the first value of the specified $meta_key.
	 */
	public static function get_price_metadata( $meta_value, $object_id, $meta_key, $single ) {
		wc_deprecated_function( 'WCPBC_Frontend_Pricing::get_price_metadata', '1.9.0' );
		return wcpbc_the_zone()->get_post_price( $object_id, $meta_key );
	}

	/**
	 * Get currency code.
	 *
	 * @param string $currency_code Currency code.
	 * @return string
	 */
	public static function get_currency( $currency_code ) {
		return wcpbc_the_zone()->get_currency();
	}

	/**
	 * Returns unique cache key to store variation child prices
	 *
	 * @param array $price_hash Unique cache key.
	 * @return array
	 */
	public static function get_variation_prices_hash( $price_hash ) {
		$price_hash[] = wcpbc_the_zone()->get_postmetakey() . wcpbc_the_zone()->get_currency() . wcpbc_the_zone()->get_exchange_rate();
		return $price_hash;
	}

	/**
	 * Set pricing zone price for items in the cart. Fix compatibility issue for plugins that uses 'edit' context to get the price.
	 *
	 * @since 1.8.4
	 * @param array $cart_item Cart item.
	 * @return array
	 */
	public static function set_cart_item_price( $cart_item ) {
		self::adjust_product_price( $cart_item['data'] );
		return $cart_item;
	}

	/**
	 * Set the product price to the pricing zone price.
	 *
	 * Fixed issues with discounts plugins.
	 *
	 * @param WC_Product $product Product instance.
	 */
	public static function adjust_product_price( &$product ) {
		if ( ! self::is_supported_product( $product ) ) {
			return;
		}

		foreach ( array( '_price', '_regular_price', '_sale_price' ) as $meta_key ) {
			$getter = 'get' . $meta_key;
			$setter = 'set' . $meta_key;
			$value  = $product->{$getter}( 'edit' );

			// Force change on the prices properties updating it with a ridiculous value.
			$product->{$setter}( -9999 );

			// Set the real price.
			$product->{$setter}(
				wcpbc_the_zone()->get_price_prop(
					$product,
					$value,
					$meta_key
				)
			);
		}
	}

	/**
	 * Override _price metakey in array of arguments for ordering products based on the selected values.
	 *
	 * @param array $args Ordering args.
	 * @return array
	 */
	public static function get_catalog_ordering_args( $args ) {
		if ( isset( $args['meta_key'] ) && '_price' === $args['meta_key'] ) {
			$args['meta_key'] = wcpbc_the_zone()->get_postmetakey( '_price' ); // WPCS: slow query ok.
		} elseif ( isset( $args['orderby'] ) && 'price' === $args['orderby'] ) {
			// Since WC 3.1.
			add_filter( 'posts_clauses', array( __CLASS__, 'order_by_price_post_clauses' ), 20 );
		}

		return $args;
	}

	/**
	 * Replace the _price metakey in order post clauses.
	 *
	 * @version 1.8.6
	 * @param array $args Query args.
	 * @return array
	 */
	public static function order_by_price_post_clauses( $args ) {
		global $wpdb;
		if ( version_compare( WC_VERSION, '3.6', '<' ) ) {
			$args['join'] = str_replace( "meta_key='_price'", "meta_key='" . wcpbc_the_zone()->get_postmetakey( '_price' ) . "'", $args['join'] ); // WPCS: slow query ok.
		} else {

			$args['join']    = self::append_wcpbc_price_table_join( $args['join'] );
			$args['orderby'] = str_replace( array( 'wc_product_meta_lookup.max_price ', 'wc_product_meta_lookup.min_price ' ), array( 'wcpbc_price.max_price ', 'wcpbc_price.min_price ' ), $args['orderby'] );
		}

		return $args;
	}

	/**
	 * Replace the _price metakey in filter post clauses. WC 3.6 compatibility.
	 *
	 * @param array    $args Query args.
	 * @param WC_Query $wp_query WC_Query object.
	 * @return array
	 */
	public static function filter_price_post_clauses( $args, $wp_query ) {
		global $wpdb;

		if ( version_compare( WC_VERSION, '3.6', '<' ) || ! $wp_query->is_main_query() || ( ! isset( $_GET['max_price'] ) && ! isset( $_GET['min_price'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return $args;
		}

		$args['join']  = self::append_wcpbc_price_table_join( $args['join'] );
		$args['where'] = str_replace( array( ' wc_product_meta_lookup.min_price >= ', ' wc_product_meta_lookup.max_price <= ' ), array( ' wcpbc_price.min_price >= ', ' wcpbc_price.max_price <= ' ), $args['where'] );

		return $args;
	}

	/**
	 * Join wcpbc_price to posts if not already joined.
	 *
	 * @since 1.8.5
	 * @version 1.8.6
	 * @param string $sql SQL join.
	 * @return string
	 */
	private static function append_wcpbc_price_table_join( $sql ) {
		global $wpdb;

		if ( ! strstr( $sql, 'wcpbc_price' ) ) {
			$sql .= $wpdb->prepare(
				" LEFT JOIN (
					SELECT post_meta.post_id, min( post_meta.meta_value + 0) as min_price, max( post_meta.meta_value + 0) as max_price
					FROM {$wpdb->postmeta} post_meta
					INNER JOIN {$wpdb->wc_product_meta_lookup} product_meta_lookup ON post_meta.post_id = product_meta_lookup.product_id WHERE post_meta.meta_key = %s GROUP BY post_meta.post_id
				) wcpbc_price ON {$wpdb->posts}.ID = wcpbc_price.post_id",
				wcpbc_the_zone()->get_postmetakey( '_price' )
			);
		}

		return $sql;
	}

	/**
	 * Remove custom pre_get_post filters after the main WooCommerce query is done. WC 3.6 compatibility.
	 *
	 * @param array $posts Posts from WP Query.
	 * @return array
	 */
	public static function remove_product_query_filters( $posts ) {
		remove_filter( 'posts_clauses', array( __CLASS__, 'order_by_price_post_clauses' ), 20 );
		remove_filter( 'posts_clauses', array( __CLASS__, 'filter_price_post_clauses' ), 25, 2 );
		return $posts;
	}

	/**
	 * Override _price metakey in meta query for filtering by price.
	 *
	 * @param array    $meta_query Meta query args.
	 * @param WC_Query $q WC Query instance.
	 * @return array
	 */
	public static function product_query_meta_query( $meta_query, $q ) {
		if ( isset( $meta_query['price_filter']['key'] ) && '_price' === $meta_query['price_filter']['key'] ) {
			$meta_query['price_filter']['key'] = wcpbc_the_zone()->get_postmetakey( '_price' );
		}
		return $meta_query;
	}

	/**
	 * Override _price metakey for get filtered min and max price for current products.
	 *
	 * @param array $meta_keys Metadata keys array.
	 * @return array
	 */
	public static function price_filter_meta_keys( $meta_keys ) {
		return array( wcpbc_the_zone()->get_postmetakey( '_price' ) );
	}

	/**
	 * Override price filter SQL. WC 3.6 compatibility.
	 *
	 * @param string $sql Price filter sql.
	 * @return string
	 */
	public static function price_filter_sql( $sql ) {
		global $wpdb;

		if ( version_compare( WC_VERSION, '3.6', '<' ) ) {
			return $sql;
		}

		$where_pos = strpos( strtoupper( $sql ), 'WHERE ' );
		if ( $where_pos ) {
			$_sql = "
				SELECT min( wcpbc_price.meta_value + 0 ) as min_price, max( wcpbc_price.meta_value + 0 ) as max_price
				FROM {$wpdb->wc_product_meta_lookup} wc_product_meta_lookup
				LEFT JOIN {$wpdb->postmeta} wcpbc_price ON wc_product_meta_lookup.product_id = wcpbc_price.post_id and wcpbc_price.meta_key = '" . wcpbc_the_zone()->get_postmetakey( '_price' ) . "'
			";
			$sql  = $_sql . substr( $sql, $where_pos );
		}
		return $sql;
	}

	/**
	 * Returns an array containing the IDs of the products that are on sale. Filter through get_transient
	 *
	 * @param mixed  $value The default value to return if the transient does not exist.
	 * @param string $transient Transient name.
	 * @return array
	 */
	public static function product_ids_on_sale( $value, $transient = false ) {
		global $wpdb;

		// Load from cache.
		$ids_on_sale = get_transient( 'wcpbc_products_onsale' );

		// Valid cache found.
		if ( false !== $ids_on_sale && is_array( $ids_on_sale ) && isset( $ids_on_sale[ wcpbc_the_zone()->get_id() ] ) ) {
			return $ids_on_sale[ wcpbc_the_zone()->get_id() ];
		}

		$ids_on_sale = is_array( $ids_on_sale ) ? $ids_on_sale : array();
		$decimals    = absint( wc_get_price_decimals() );

		$on_sale_posts = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT post.ID, post.post_parent FROM `{$wpdb->posts}` AS post
				LEFT JOIN `{$wpdb->postmeta}` AS meta ON post.ID = meta.post_id
				LEFT JOIN `{$wpdb->postmeta}` AS meta2 ON post.ID = meta2.post_id
				WHERE post.post_type IN ( 'product', 'product_variation' )
					AND post.post_status = 'publish'
					AND meta.meta_key = %s
					AND meta2.meta_key = %s
					AND CAST( meta.meta_value AS DECIMAL ) >= 0
					AND CAST( meta.meta_value AS CHAR ) != ''
					AND CAST( meta.meta_value AS DECIMAL( 10, %d ) ) = CAST( meta2.meta_value AS DECIMAL( 10, %d ) )
				GROUP BY post.ID",
				wcpbc_the_zone()->get_postmetakey( '_sale_price' ),
				wcpbc_the_zone()->get_postmetakey( '_price' ),
				$decimals,
				$decimals
			)
		);

		$ids_on_sale[ wcpbc_the_zone()->get_id() ] = array_unique( array_map( 'absint', array_merge( wp_list_pluck( $on_sale_posts, 'ID' ), array_diff( wp_list_pluck( $on_sale_posts, 'post_parent' ), array( 0 ) ) ) ) );

		set_transient( 'wcpbc_products_onsale', $ids_on_sale, DAY_IN_SECONDS * 30 );

		return $ids_on_sale[ wcpbc_the_zone()->get_id() ];
	}

	/**
	 * Apply exchange rate to shipping cost
	 *
	 * @param array $rates Rates.
	 * @param array $package Cart items.
	 * @return float
	 */
	public static function package_rates( $rates, $package ) {

		if ( 'yes' === get_option( 'wc_price_based_country_shipping_exchange_rate', 'no' ) ) {

			foreach ( $rates as $rate ) {
				$change = false;

				if ( ! isset( $rate->cost ) ) {
					continue;
				}

				if ( ! isset( $rate->wcpbc_data ) ) {

					$rate->wcpbc_data = array(
						'exchange_rate' => wcpbc_the_zone()->get_exchange_rate(),
						'orig_cost'     => $rate->cost,
						'orig_taxes'    => isset( $rate->taxes ) ? $rate->taxes : array(),
					);

					$change = true;

				} elseif ( wcpbc_the_zone()->get_exchange_rate() !== $rate->wcpbc_data['exchange_rate'] ) {

					$rate->wcpbc_data['exchange_rate'] = wcpbc_the_zone()->get_exchange_rate();

					$change = true;

				}

				if ( $change ) {
					// Apply exchange rate.
					if ( ! wc_prices_include_tax() ) {
						$rate->cost = wcpbc_the_zone()->get_exchange_rate_price( $rate->cost, true, 'shipping', $rate );
					} else {
						$rate->cost = wcpbc_the_zone()->get_exchange_rate_price( $rate->cost, false );
					}

					// Recalculate taxes.
					$rate_taxes = isset( $rate->taxes ) ? $rate->taxes : array();
					foreach ( $rate->wcpbc_data['orig_taxes'] as $i => $tax ) {
						$rate_taxes[ $i ] = ( $tax / $rate->wcpbc_data['orig_cost'] ) * $rate->cost;
					}
					$rate->taxes = $rate_taxes;
				}
			}
		}

		return $rates;
	}

	/**
	 * Apply exchange rate to free shipping min amount
	 *
	 * @param array            $methods Array of shipping methods.
	 * @param array            $raw_methods Raw methods.
	 * @param array            $allowed_classes Array of allowed classes.
	 * @param WC_Shipping_Zone $shipping Shipiing zone instance.
	 * @return array
	 */
	public static function shipping_zone_shipping_methods( $methods, $raw_methods, $allowed_classes, $shipping ) {
		if ( apply_filters( 'wc_price_based_country_free_shipping_exchange_rate', ( 'yes' === get_option( 'wc_price_based_country_shipping_exchange_rate', 'no' ) ) ) ) {
			foreach ( $methods as $instance_id => $method ) {
				if ( isset( $method->id ) && ! empty( $method->min_amount ) && 'free_shipping' === $method->id ) {
					$method->min_amount = wcpbc_the_zone()->get_exchange_rate_price( $method->min_amount, true, 'free_shipping', $method->id );
				}
			}
		}
		return $methods;
	}

	/**
	 * Filters the non-base location tax adjust.
	 *
	 * @param bool $adjust True or False.
	 * @return bool
	 */
	public static function adjust_non_base_location_prices( $adjust ) {
		if ( wcpbc_the_zone()->get_disable_tax_adjustment() ) {
			$adjust = false;
		}
		return $adjust;
	}

	/**
	 * Apply exchange rate to coupon
	 *
	 * @param WC_Coupon $coupon Coupon instance.
	 */
	public static function coupon_loaded( $coupon ) {
		if ( ! is_callable( array( $coupon, 'get_id' ) ) ) {
			return;
		}

		$zone_pricing_type = get_post_meta( $coupon->get_id(), 'zone_pricing_type', true );

		if ( wcpbc_is_exchange_rate( $zone_pricing_type ) && 'percent' !== $coupon->get_discount_type() ) {
			$amount = wcpbc_the_zone()->get_exchange_rate_price( $coupon->get_amount(), true, 'coupon', $coupon->get_id() );
			$coupon->set_amount( $amount );
		}

		if ( $coupon->get_minimum_amount() ) {
			$amount = wcpbc_the_zone()->get_exchange_rate_price( $coupon->get_minimum_amount(), true, 'coupon', $coupon->get_id() );
			$coupon->set_minimum_amount( $amount );

		}
		if ( $coupon->get_maximum_amount() ) {
			$amount = wcpbc_the_zone()->get_exchange_rate_price( $coupon->get_maximum_amount(), true, 'coupon', $coupon->get_id() );
			$coupon->set_maximum_amount( $amount );
		}
	}

	/**
	 * Add zone data to order meta
	 *
	 * @since 1.9
	 * @param int $order_id Order ID.
	 */
	public static function update_order_meta( $order_id ) {
		update_post_meta( $order_id, '_wcpbc_base_exchange_rate', wcpbc_the_zone()->get_base_currency_amount( 1 ) );
		update_post_meta( $order_id, '_wcpbc_pricing_zone', wcpbc_the_zone()->get_data() );
	}
}
