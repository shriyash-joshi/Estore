<?php
/**
 * Sync product price by the exchange rate.
 *
 * @package WCPBC
 * @version 1.9.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * WCPBC_Update_GeoIP_DB Class
 */
class WCPBC_Product_Sync {

	/**
	 * Background process to sync prices.
	 *
	 * @var WCPBC_Product_Sync_Background
	 */
	protected static $background_process;

	/**
	 * Init hooks.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'init_background_process' ) );
		add_action( 'add_option_wc_price_based_country_regions', array( __CLASS__, 'add_pricing_zone' ), 10, 2 );
		add_action( 'update_option_wc_price_based_country_regions', array( __CLASS__, 'update_pricing_zones' ), 10, 2 );
		add_action( 'woocommerce_variable_product_sync_data', array( __CLASS__, 'queue_parent_product_sync' ) );
		add_action( 'woocommerce_after_grouped_object_save', array( __CLASS__, 'queue_parent_product_sync' ) );

		// Scheduled sales action.
		add_action( 'wc_after_products_starting_sales', array( __CLASS__, 'after_products_starting_sales' ) );
		add_action( 'wc_after_products_ending_sales', array( __CLASS__, 'after_products_ending_sales' ) );
		add_action( 'woocommerce_scheduled_sales', array( __CLASS__, 'queue_manual_scheduled_sales' ), 20 );

		// Delete transients.
		add_action( 'woocommerce_delete_product_transients', array( __CLASS__, 'delete_product_transients' ) );
	}

	/**
	 * Returns the variable product types.
	 *
	 * @return array
	 */
	public static function get_variable_product_types() {
		return array_unique( apply_filters( 'wc_price_based_country_parent_product_types', array( 'variable' ) ) );
	}

	/**
	 * Returns the parent product types (variable, grouped).
	 *
	 * @return array
	 */
	public static function get_parent_product_types() {
		return array_unique( array_merge( self::get_variable_product_types(), array( 'grouped' ) ) );
	}

	/**
	 * Init background process.
	 */
	public static function init_background_process() {
		if ( ! self::$background_process ) {
			include_once dirname( __FILE__ ) . '/class-wcpbc-product-sync-background.php';
			self::$background_process = new WCPBC_Product_Sync_Background();
		}
	}

	/**
	 * Update product prices by exchange rate of all pricing zones.
	 */
	public static function sync_all() {
		self::init_background_process();
		foreach ( WCPBC_Pricing_Zones::get_zones() as $zone ) {
			self::$background_process->push_to_queue(
				array(
					'task' => 'products_sync',
					'zone' => $zone->get_id(),
				)
			);
		}
	}

	/**
	 * Fires the exchange rate price sync after the option wc_price_based_country_regions has been added.
	 *
	 * @param string $option Option name.
	 * @param mixed  $value The new option value.
	 */
	public static function add_pricing_zone( $option, $value ) {
		self::update_pricing_zones( array(), $value );
	}

	/**
	 * Fires the exchange rate price sync after the option wc_price_based_country_regions has been updated.
	 *
	 * @param mixed $old_value The old option value.
	 * @param mixed $value     The new option value.
	 */
	public static function update_pricing_zones( $old_value, $value ) {
		if ( ! is_array( $value ) ) {
			return;
		}

		self::init_background_process();

		foreach ( $value as $id => $data ) {
			if ( isset( $data['exchange_rate'] ) && ! empty( $data['exchange_rate'] ) && ( ! isset( $old_value[ $id ]['exchange_rate'] ) || $old_value[ $id ]['exchange_rate'] !== $data['exchange_rate'] ) ) {
				// Exchange rate changed.
				self::$background_process->push_to_queue(
					array(
						'task' => 'products_sync',
						'zone' => $id,
					)
				);
			}
		}

		foreach ( $old_value as $id => $data ) {
			if ( ! isset( $value[ $id ] ) ) {
				// Zone has been delete.
				self::$background_process->push_to_queue(
					array(
						'task' => 'delete_zone_metadata',
						'zone' => $id,
					)
				);
			}
		}
	}

	/**
	 * Queue process to sync products' price by the exchange rate.
	 *
	 * @param string $zone_id Pricing zone ID.
	 */
	public static function queue_sync_exchange_rate_price( $zone_id ) {

		self::$background_process->push_to_queue(
			array(
				'task' => 'parent_product_price_method',
				'zone' => $zone_id,
			)
		);

		foreach ( array( '_regular_price', '_sale_price', '_price' ) as $metakey ) {
			self::$background_process->push_to_queue(
				array(
					'task'    => 'update_exchange_rate_prices',
					'zone'    => $zone_id,
					'metakey' => $metakey,
				)
			);
		}
	}

	/**
	 * Delete the metadata of a pricing zone.
	 *
	 * @param string $zone_id Pricing zone ID.
	 * @return int
	 */
	public static function delete_zone_metadata( $zone_id ) {
		global $wpdb;
		return $wpdb->query(
			$wpdb->prepare(
				"
				DELETE FROM {$wpdb->postmeta}
				 WHERE (meta_key like %s OR meta_key like %s)
				",
				'_' . $wpdb->esc_like( $zone_id ) . '_%pric%',
				'_' . $wpdb->esc_like( $zone_id ) . '_%cost%'
			)
		);
	}


	/**
	 * Queue the price sync of a parent product with children.
	 *
	 * @param WC_Product $product Product instance.
	 */
	public static function queue_parent_product_sync( $product ) {
		self::init_background_process();

		$type = is_callable( array( $product, 'get_type' ) ) ? $product->get_type() : false;
		if ( ! $type ) {
			return;
		}

		$type = in_array( $type, self::get_variable_product_types(), true ) ? 'variable' : 'grouped';

		foreach ( WCPBC_Pricing_Zones::get_zones() as $zone ) {
			self::$background_process->push_to_queue(
				array(
					'task'        => 'parent_product_price_sync',
					'zone'        => $zone->get_zone_id(),
					'product_ids' => is_callable( array( $product, 'get_id' ) ) ? array( $product->get_id() ) : 0,
					'type'        => $type,
					'context'     => 'variation_update',
				)
			);
		}
	}

	/**
	 * Update product prices after starting sales.
	 *
	 * @param array $product_ids Array of product IDs which starting/ending sales.
	 */
	public static function after_products_starting_sales( $product_ids ) {
		foreach ( WCPBC_Pricing_Zones::get_zones() as $zone ) {

			self::default_starting_ending_sales( $zone, $product_ids, 'start' );

			foreach ( $product_ids as $product_id ) {
				if ( $zone->is_exchange_rate_price( $product_id ) ) {
					$price = $zone->get_exchange_rate_price_by_post( $product_id, '_price' );
					$zone->set_postmeta( $product_id, '_price', $price );
				}
			}
		}
	}

	/**
	 * Update product prices after ending sales.
	 *
	 * @param array $product_ids Array of product IDs which starting/ending sales.
	 */
	public static function after_products_ending_sales( $product_ids ) {
		foreach ( WCPBC_Pricing_Zones::get_zones() as $zone ) {

			self::default_starting_ending_sales( $zone, $product_ids, 'end' );

			foreach ( $product_ids as $product_id ) {
				if ( $zone->is_exchange_rate_price( $product_id ) ) {
					$price = $zone->get_exchange_rate_price_by_post( $product_id, '_price' );
					$zone->set_postmeta( $product_id, '_price', $price );
					$zone->set_postmeta( $product_id, '_sale_price', '' );
				}
			}
		}
	}

	/**
	 * Update manual prices with default sale price dates after products starting|ending sales.
	 *
	 * @see wc_scheduled_sales function.
	 *
	 * @param mixed  $zone_id Pricing zone ID.
	 * @param array  $product_ids Array of product IDs which starting/ending sales.
	 * @param string $sales (Optional) 'start' for starting sales; 'end' for ending sales. Default 'start'.
	 */
	public static function default_starting_ending_sales( $zone_id, $product_ids, $sales = 'start' ) {
		$zone = WCPBC_Pricing_Zones::get_zone( $zone_id );
		if ( ! $zone ) {
			return false;
		}

		$updated = 0;
		$rows    = self::get_starting_ending_sales( $zone, $sales, 'default', $product_ids );

		foreach ( $rows as $row ) {
			$price = 'start' === $sales ? $row->sale_price : $row->regular_price;
			if ( $price || 'start' !== $sales ) { // If sale price is not empty, set sale price.
				$zone->set_postmeta( $row->ID, '_price', $price );
				$updated++;
			}
		}

		return $updated;
	}

	/**
	 * Queue the scheduled sales update.
	 */
	public static function queue_manual_scheduled_sales() {
		self::init_background_process();

		foreach ( WCPBC_Pricing_Zones::get_zones() as $zone ) {
			self::$background_process->push_to_queue(
				array(
					'task' => 'scheduled_sales',
					'zone' => $zone->get_zone_id(),
				)
			);
		}
	}


	// phpcs:disable WordPress.DB.PreparedSQL

	/**
	 * Returns a meta data query.
	 *
	 * @param array $args Array of arguments.
	 */
	private static function get_metaquery( $args ) {
		global $wpdb;

		$args = wp_parse_args(
			$args,
			array(
				'meta_key'   => '',
				'meta_value' => '',
				'select'     => '',
				'post_alias' => '',
				'metakeys'   => array(),
				'join'       => array(),
				'where'      => array(),
				'groupby'    => '',
			)
		);

		$query        = '';
		$placeholders = array();
		$joins        = is_array( $args['join'] ) ? $args['join'] : array();
		$post_alias   = empty( $args['post_alias'] ) ? 'posts' : $args['post_alias'];

		if ( ! empty( $args['meta_key'] ) ) {
			$query         .= "SELECT {$post_alias}.ID as post_id, %s as meta_key, " . $args['meta_value'] . ' as meta_value ';
			$placeholders[] = $args['meta_key'];
		} else {
			$query .= 'SELECT ' . $args['select'] . ' ';
		}

		foreach ( $args['metakeys'] as $i => $metakey ) {
			$alias = isset( $metakey['alias'] ) ? $metakey['alias'] : 'metakey_' . $i;
			$join  = isset( $metakey['join'] ) && 'left' === strtolower( $metakey['join'] ) ? 'LEFT' : 'INNER';

			$joins[]        = $join . " JOIN {$wpdb->postmeta} {$alias} ON ({$post_alias}.ID = {$alias}.post_id and {$alias}.meta_key = %s)";
			$placeholders[] = isset( $metakey['value'] ) ? $metakey['value'] : $metakey;
		}

		$query .= "FROM {$wpdb->posts} {$post_alias} " . implode( ' ', $joins ) . " WHERE {$post_alias}.post_status = 'publish'";

		if ( ! empty( $args['where'] ) && is_array( $args['where'] ) ) {
			$query .= ' AND ' . implode( ' AND ', $args['where'] );
		}

		if ( ! empty( $args['groupby'] ) ) {
			$query .= ' GROUP BY ' . $args['groupby'];
		}

		return $wpdb->prepare( $query, $placeholders );
	}

	/**
	 * Update post meta data table.
	 *
	 * @param array $querys Array that contains the insert and update querys.
	 * @return int
	 */
	private static function update_metadata( $querys ) {
		global $wpdb;

		$rows = 0;

		if ( isset( $querys['update'] ) && ! empty( $querys['update'] ) ) {
			$rows += $wpdb->query(
				"
				UPDATE {$wpdb->postmeta} meta INNER JOIN (
				" . implode( ' UNION ', $querys['update'] ) . ') metadata ON metadata.post_id = meta.post_id and metadata.meta_key = meta.meta_key
				SET meta.meta_value = metadata.meta_value
				'
			);
		}

		if ( isset( $querys['insert'] ) && ! empty( $querys['insert'] ) ) {
			$rows += $wpdb->query(
				"
				INSERT IGNORE INTO {$wpdb->postmeta} (`post_id`, `meta_key`, `meta_value`)
				SELECT
					`post_id`, `meta_key`, `meta_value`
				FROM (
				" . implode( ' UNION ', $querys['insert'] ) . ') metadata'
			);
		}

		return $rows;
	}

	// phpcs:enable

	/**
	 * Insert a price method metadata for the parent product types (variable, grouped, ...).
	 *
	 * @param mixed $zone_id Pricing zone ID.
	 */
	public static function parent_product_price_method( $zone_id ) {
		global $wpdb;

		$zone = WCPBC_Pricing_Zones::get_zone( $zone_id );
		if ( ! $zone ) {
			return false;
		}

		$querys   = array();
		$term_ids = get_terms(
			array(
				'fields'   => 'ids',
				'taxonomy' => 'product_type',
				'slug'     => self::get_parent_product_types(),
			)
		);

		if ( empty( $term_ids ) || is_wp_error( $term_ids ) ) {
			return false;
		}

		// Generate the query for the update.
		$query_args = array(
			'meta_key'   => $zone->get_postmetakey( '_price_method' ),
			'meta_value' => "'nothing'",
			'join'       => array(
				"INNER JOIN {$wpdb->term_relationships} term_relationships ON (posts.ID = term_relationships.object_id)",
			),
			'metakeys'   => array(
				array(
					'value' => $zone->get_postmetakey( '_price_method' ),
					'alias' => 'meta__price_method',
				),
			),
			'where'      => array(
				"posts.post_type in ('product')",
				'term_relationships.term_taxonomy_id IN (' . implode( ',', $term_ids ) . ')',
				"meta__price_method.meta_value <> 'nothing'",
			),
		);

		$querys['update'][] = self::get_metaquery( $query_args );

		// Update the query args to generate the query we'll use in the insert.
		$query_args['metakeys'][0]['join'] = 'left';
		$query_args['where'][2]            = 'meta__price_method.meta_id IS NULL';

		$querys['insert'][] = self::get_metaquery( $query_args );

		return self::update_metadata( $querys );
	}

	/**
	 * Update the exchange rate prices.
	 *
	 * @param mixed  $zone_id Pricing zone ID.
	 * @param string $metakey Price meta key (_price, _regular_price, _sale_price ).
	 */
	public static function update_exchange_rate_prices( $zone_id, $metakey ) {

		if ( ! in_array( $metakey, array( '_price', '_regular_price', '_sale_price' ), true ) ) {
			return false;
		}

		$zone = WCPBC_Pricing_Zones::get_zone( $zone_id );
		if ( ! $zone ) {
			return false;
		}

		$updated          = 0;
		$querys           = array();
		$s_exchange_rate  = "CASE TRIM(COALESCE(meta__price.meta_value, '')) WHEN '' THEN '' ELSE TRIM(ROUND((meta__price.meta_value+0) * (%s), 8))+0 END";
		$exchange_rate_op = sprintf( $s_exchange_rate, $zone->get_exchange_rate() );

		// Generate the query for the update.
		$query_args = array(
			'meta_key'   => $zone->get_postmetakey( $metakey ),
			'meta_value' => $exchange_rate_op,
			'metakeys'   => array(
				array(
					'value' => $metakey,
					'alias' => 'meta__price',
				),
				array(
					'value' => $zone->get_postmetakey( $metakey ),
					'alias' => 'meta__price_zone',
				),
				array(
					'value' => $zone->get_postmetakey( '_price_method' ),
					'alias' => 'meta__price_method',
					'join'  => 'left',
				),
			),
			'where'      => array(
				"posts.post_type in ('product', 'product_variation')",
				"COALESCE(meta__price_method.meta_value, 'exchange_rate') = 'exchange_rate'",
				sprintf( "CASE TRIM(COALESCE(meta__price_zone.meta_value, '')) WHEN '' THEN '' ELSE (meta__price_zone.meta_value+0) END <> %s", $exchange_rate_op ),
			),
		);

		$querys['update'][] = self::get_metaquery( $query_args );

		// Update the query args to generate the query we'll use in the insert.
		$query_args['metakeys'][1]['join'] = 'left';
		$query_args['where'][2]            = 'meta__price_zone.meta_id IS NULL';

		$querys['insert'][] = self::get_metaquery( $query_args );

		// Do the update.
		$updated = self::update_metadata( $querys );

		if ( '_price' === $metakey && $updated > 0 ) {
			// Sync the variable products.
			self::init_background_process();
			self::$background_process->push_to_queue(
				array(
					'task'    => 'parent_product_price_sync',
					'zone'    => $zone_id,
					'type'    => 'variable',
					'context' => 'exchange_rate',
				)
			);
		}

		return $updated;
	}

	/**
	 * Returns the min and max price query for variable products.
	 *
	 * @param array $args Array of arguments.
	 * @return array
	 */
	private static function get_min_max_price_query_args( $args ) {
		global $wpdb;

		$args = wp_parse_args(
			$args,
			array(
				'price_metakey' => '',
				'product_types' => array(),
				'parent_join'   => array(),
				'where'         => array(),
			)
		);

		$term_ids = get_terms(
			array(
				'fields'   => 'ids',
				'taxonomy' => 'product_type',
				'slug'     => $args['product_types'],
			)
		);
		$term_ids = empty( $term_ids ) || is_wp_error( $term_ids ) ? array( -1 ) : $term_ids;

		$args['parent_join'] = array_merge(
			$args['parent_join'],
			array(
				"INNER JOIN {$wpdb->term_relationships} term_relationships ON (parent_posts.ID = term_relationships.object_id)",
				"LEFT JOIN  {$wpdb->postmeta} meta_parent_price ON (parent_posts.ID = meta_parent_price.post_id and meta_parent_price.meta_key = '" . esc_attr( $args['price_metakey'] ) . "') ",
			)
		);
		$args['where']       = array_merge(
			$args['where'],
			array(
				"parent_posts.post_type in ('product')",
				"parent_posts.post_status = 'publish'",
				'term_relationships.term_taxonomy_id IN (' . implode( ',', $term_ids ) . ')',
			)
		);

		return array(
			'select'   => 'parent_posts.ID as post_id, MIN(meta__price.meta_value+0) as min_price, MAX(meta__price.meta_value+0) as max_price',
			'join'     => $args['parent_join'],
			'metakeys' => array(
				array(
					'value' => $args['price_metakey'],
					'alias' => 'meta__price',
				),
			),
			'where'    => $args['where'],
			'groupby'  => 'parent_posts.ID HAVING ABS(COALESCE(MIN(meta_parent_price.meta_value+0),0) - MIN(meta__price.meta_value+0)) > 0.000001 OR ABS(COALESCE(MAX(meta_parent_price.meta_value+0),0) - MAX(meta__price.meta_value+0))>0.000001',
		);
	}

	/**
	 * Retruns the variable sync price query args.
	 *
	 * @param WCPBC_Pricing_Zone $zone Pricing zone object.
	 * @return array
	 */
	private static function variable_sync_query_args( $zone ) {
		global $wpdb;
		return self::get_min_max_price_query_args(
			array(
				'price_metakey' => $zone->get_postmetakey( '_price' ),
				'product_types' => self::get_variable_product_types(),
				'parent_join'   => array(
					"INNER JOIN {$wpdb->posts} parent_posts ON posts.post_parent = parent_posts.ID",
				),
				'where'         => array(
					"posts.post_type in ('product_variation')",
				),
			)
		);
	}

	/**
	 * Retruns the grouped sync price query args.
	 *
	 * @param WCPBC_Pricing_Zone $zone Pricing zone object.
	 * @return array
	 */
	private static function grouped_sync_query_args( $zone ) {
		global $wpdb;
		return self::get_min_max_price_query_args(
			array(
				'price_metakey' => $zone->get_postmetakey( '_price' ),
				'product_types' => array( 'grouped' ),
				'parent_join'   => array(
					"INNER JOIN {$wpdb->postmeta} meta__children ON meta_key = '_children' and INSTR(meta__children.meta_value, CONCAT(':', posts.ID, ';') ) > 0",
					"INNER JOIN {$wpdb->posts} parent_posts ON parent_posts.ID = meta__children.post_id",
				),
			)
		);
	}

	/**
	 * Sync the price of the parent products.
	 *
	 * @param array $args Array of arguments.
	 * @param int   $limit Max number or records to be processed.
	 * @return int
	 */
	public static function parent_product_price_sync( $args = array(), $limit = 100 ) {
		global $wpdb;

		$args = wp_parse_args(
			$args,
			array(
				'zone'        => '',
				'type'        => 'variable',
				'context'     => 'variation_update',
				'product_ids' => false,
			)
		);

		$updated = 0;
		$zone    = WCPBC_Pricing_Zones::get_zone( $args['zone'] );
		if ( ! $zone ) {
			return $updated;
		}

		$query_args     = array();
		$product_filter = false;

		if ( 'variable' === $args['type'] ) {
			// Variable products.
			$query_args = self::variable_sync_query_args( $zone );

			if ( 'exchange_rate' === $args['context'] ) {

				$filter = self::get_metaquery(
					array(
						'select'     => 'product_variations.ID',
						'post_alias' => 'product_variations',
						'metakeys'   => array(
							array(
								'value' => $zone->get_postmetakey( '_price_method' ),
								'alias' => 'meta__price_method',
								'join'  => 'left',
							),
						),
						'where'      => array(
							"product_variations.post_type in ('product_variation')",
							"COALESCE(meta__price_method.meta_value, 'exchange_rate') = 'exchange_rate'",
							'product_variations.post_parent = parent_posts.ID',
						),
					)
				);

				$product_filter = 'exists (' . $filter . ')';
			}
		} else {

			// Grouped products.
			$query_args = self::grouped_sync_query_args( $zone );
		}

		if ( ! empty( $args['product_ids'] ) && $query_args ) {

			$args['product_ids'] = is_array( $args['product_ids'] ) ? $args['product_ids'] : array( $args['product_ids'] );

			$post_ids_filter = 'parent_posts.ID in (' . implode( ',', array_map( 'absint', $args['product_ids'] ) ) . ')';
			$product_filter  = $product_filter ? '((' . $product_filter . ') OR (' . $post_ids_filter . '))' : $post_ids_filter;

			$query_args['where'][] = $product_filter;
		}

		if ( $query_args ) {
			$query = self::get_metaquery( $query_args );

			if ( $limit ) {
				$query .= ' LIMIT ' . absint( $limit );
			}

			// Do the update.
			$rows = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.PreparedSQL

			foreach ( $rows as $row ) {
				self::update_parent_product_price( $zone, $row->post_id, $row->min_price, $row->max_price );
				$updated++;
			}
		}

		return $updated;
	}

	/**
	 * Update parent price.
	 *
	 * @param WCPBC_Pricing_Zone $zone Pricing zone instance.
	 * @param int                $product_id Product ID.
	 * @param float              $min_price Min price.
	 * @param float              $max_price Max price.
	 */
	private static function update_parent_product_price( $zone, $product_id, $min_price, $max_price ) {
		$min_price = floatval( $min_price );
		$max_price = floatval( $max_price );

		$zone->set_postmeta( $product_id, '_price_method', 'nothing' );
		$zone->delete_postmeta( $product_id, '_price' );
		$zone->add_postmeta( $product_id, '_price', $min_price );

		if ( $max_price > $min_price ) {
			$zone->add_postmeta( $product_id, '_price', $max_price );
		}
	}

	/**
	 * Returns an array of IDs of products that have sales starting soon.
	 *
	 * @param WCPBC_Pricing_Zone $zone Pricing zone instance.
	 * @param string             $sales (Optional) 'start' for starting sales; 'end' for ending sales. Default 'start'.
	 * @param string             $sale_price_dates (Optional) Sale price date meta key value.
	 * @param array              $post_ids (Optional) Product Ids to processed.
	 * @return array
	 */
	private static function get_starting_ending_sales( $zone, $sales = 'start', $sale_price_dates = 'manual', $post_ids = false ) {
		global $wpdb;

		$where = false;

		if ( 'manual' === $sale_price_dates ) {
			$where = array(
				'meta__sale_price_dates_from_to.meta_value > 0',
				$wpdb->prepare( 'meta__sale_price_dates_from_to.meta_value < %s', time() ),
			);
		} elseif ( is_array( $post_ids ) && ! empty( $post_ids ) ) {
			$where = array(
				'posts.ID in (' . implode( ',', array_map( 'absint', $post_ids ) ) . ')',
			);
		}

		if ( ! $where ) {
			return array();
		}

		$date_metakey = '_sale_price_dates_from';
		$price_meta   = 'meta__sale_price';

		if ( 'start' !== $sales ) {
			$date_metakey = '_sale_price_dates_to';
			$price_meta   = 'meta__regular_price';
		}

		$args = array(
			'select'   => 'posts.ID, posts.post_parent, ' . $price_meta . '.meta_value as ' . str_replace( 'meta__', '', $price_meta ),
			'metakeys' => array(
				array(
					'value' => $zone->get_postmetakey( $date_metakey ),
					'alias' => 'meta__sale_price_dates_from_to',
					'join'  => 'left',
				),
				array(
					'value' => $zone->get_postmetakey( '_price_method' ),
					'alias' => 'meta__price_method',
				),
				array(
					'value' => $zone->get_postmetakey( '_sale_price_dates' ),
					'alias' => 'meta__sale_price_dates',
					'join'  => 'left',
				),
				array(
					'value' => $zone->get_postmetakey( '_price' ),
					'alias' => 'meta__price',
					'join'  => 'left',
				),
				array(
					'value' => $zone->get_postmetakey( '_regular_price' ),
					'alias' => 'meta__regular_price',
					'join'  => 'left',
				),
				array(
					'value' => $zone->get_postmetakey( '_sale_price' ),
					'alias' => 'meta__sale_price',
					'join'  => 'left',
				),
			),
			'where'    => array_merge(
				array(
					"posts.post_type in ('product', 'product_variation')",
					"meta__price_method.meta_value = 'manual'",
					$wpdb->prepare( "COALESCE(meta__sale_price_dates.meta_value, 'default') = %s", $sale_price_dates ),
					'meta__price.meta_value != ' . $price_meta . '.meta_value',
				),
				$where
			),
		);

		$query = self::get_metaquery( $args );

		return $wpdb->get_results( $query );  // phpcs:ignore WordPress.DB.PreparedSQL
	}

	/**
	 * Function which handles the start and end of scheduled sales via cron.
	 *
	 * @param mixed $zone_id Pricing zone ID.
	 */
	public static function scheduled_sales( $zone_id ) {
		$zone = WCPBC_Pricing_Zones::get_zone( $zone_id );
		if ( ! $zone ) {
			return false;
		}

		$updated    = 0;
		$parent_ids = array();

		// Sales which are due to start.
		$rows = self::get_starting_ending_sales( $zone, 'start' );

		foreach ( $rows as $row ) {
			if ( $row->sale_price ) {
				$updated ++;
				$zone->set_postmeta( $row->ID, '_price', $row->sale_price );
				$zone->set_postmeta( $row->ID, '_sale_price_dates_from', '' );
				if ( $row->post_parent ) {
					$parent_ids[] = $row->post_parent;
				}
			} else {
				$zone->set_postmeta( $row->ID, '_sale_price_dates_from', '' );
				$zone->set_postmeta( $row->ID, '_sale_price_dates_to', '' );
			}
		}

		// Sales which are due to end.
		$rows = self::get_starting_ending_sales( $zone, 'end' );

		foreach ( $rows as $row ) {
			$updated ++;
			$zone->set_postmeta( $row->ID, '_price', $row->regular_price );
			$zone->set_postmeta( $row->ID, '_sale_price', '' );
			$zone->set_postmeta( $row->ID, '_sale_price_dates_from', '' );
			$zone->set_postmeta( $row->ID, '_sale_price_dates_to', '' );

			if ( $row->post_parent ) {
				if ( $row->post_parent ) {
					$parent_ids[] = $row->post_parent;
				}
			}
		}

		$parent_ids = array_unique( array_map( 'absint', $parent_ids ) );
		if ( ! empty( $parent_ids ) ) {
			self::init_background_process();
			self::$background_process->push_to_queue(
				array(
					'task'        => 'parent_product_price_sync',
					'zone'        => $zone->get_zone_id(),
					'product_ids' => $parent_ids,
					'type'        => 'variable',
					'context'     => 'variation_update',
				)
			);
		}

		return $updated;
	}

	/**
	 * Clear all WCPBC transients cache for product data.
	 */
	public static function delete_product_transients() {
		delete_transient( 'wcpbc_products_onsale' );
	}
}

