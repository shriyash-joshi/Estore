<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WCPBC_Subscription_Reports' ) ) :

/**
 * Subscriptions Reports
 *
 * @author      OscarGare
 * @category    Admin
 * @version     1.1.2
 */
class WCPBC_Subscription_Reports {


	/**
	 * Hook actions and filters
	 *
	 */
	public static function init() {

		add_filter( 'wcs_reports_subscription_events_sign_up_data', array( __CLASS__, 'reports_subscription_events_sign_up_data' ), 10, 2 );
		add_filter( 'wcs_reports_upcoming_recurring_revenue_data', array( __CLASS__, 'reports_upcoming_recurring_revenue_data' ), 10, 2 );

		add_filter( 'wcs_reports_product_query', array( __CLASS__, 'reports_product_query' ) );
		add_filter( 'wcs_reports_product_lifetime_value_query', array( __CLASS__, 'reports_product_lifetime_value_query' ) );
		add_filter( 'wcs_reports_current_customer_query', array( __CLASS__, 'current_customer_query' ) );
		add_filter( 'wcs_reports_current_customer_renewal_switch_total_query', array( __CLASS__, 'current_customer_renewal_switch_total_query' ) );
		add_filter( 'wcs_reports_customer_total_query', array( __CLASS__, 'customer_total_query' ) );
		add_filter( 'wcs_reports_customer_total_renewal_switch_query', array( __CLASS__, 'customer_total_renewal_switch' ) );

		add_filter( 'wc_price_based_country_tabs_report_notice', array( __CLASS__, 'tabs_report_notice' ) );
	}

	/**
	 * Return new subscription orders data - WC_Report_Subscription_Events_By_Date
	 *
	 * @return array
	 */
	public static function reports_subscription_events_sign_up_data( $data, $args ) {

		global $wpdb;

		$query = "SELECT SUM(subscriptions.count) as count,
				order_posts.post_date as post_date,
				SUM(order_total_post_meta.meta_value) as signup_totals
			FROM {$wpdb->posts} AS order_posts
			INNER JOIN (
				SELECT COUNT(DISTINCT(subscription_posts.ID)) as count,
					subscription_posts.post_parent as order_id
					FROM {$wpdb->posts} as subscription_posts
				WHERE subscription_posts.post_type = 'shop_subscription'
					AND subscription_posts.post_date >= %s
					AND subscription_posts.post_date < %s
				GROUP BY order_id
			) AS subscriptions ON subscriptions.order_id = order_posts.ID
			LEFT JOIN {$wpdb->postmeta} AS order_total_post_meta
				ON order_posts.ID = order_total_post_meta.post_id
			WHERE  order_posts.post_type IN ( '" . implode( "','", wc_get_order_types( 'order-count' ) ) . "' )
				AND order_posts.post_status IN ( 'wc-" . implode( "','wc-", $args['order_status'] ) . "' )
				AND order_posts.post_date >= %s
				AND order_posts.post_date < %s
				AND order_total_post_meta.meta_key = '_order_total'
			GROUP BY YEAR(order_posts.post_date), MONTH(order_posts.post_date), DAY(order_posts.post_date)
			ORDER BY post_date ASC";

		$_query = self::add_exchange_rate_to_query( $query, 'order_total_post_meta.meta_value', 'WHERE', 'order_posts', 'LEFT' );

		if ( $query <> $_query ) {

			$report = new WC_Admin_Report();
			$report->calculate_current_range( self::get_current_range() );

			$query_end_date = date( 'Y-m-d', strtotime( '+1 DAY', $report->end_date ) );

			$data = $wpdb->get_results( $wpdb->prepare( $_query,
					date( 'Y-m-d', $report->start_date ),
					$query_end_date,
					date( 'Y-m-d', $report->start_date ),
					$query_end_date
			) );

		}

		return $data;
	}

	/**
	 * Return upcoming recurring revenue data - WC_Report_Upcoming_Recurring_Revenue
	 *
	 * @return array
	 */
	public static function reports_upcoming_recurring_revenue_data( $data, $args ) {

		global $wpdb;

		$report = new WC_Report_Upcoming_Recurring_Revenue();
		$report->calculate_current_range( self::get_current_range() );

		$query = "SELECT
				DATE_FORMAT(ms.meta_value, '%s') as scheduled_date,
				SUM(mo.meta_value) as recurring_total,
				COUNT(mo.meta_value) as total_renewals,
				group_concat(p.ID) as subscription_ids,
				group_concat(mi.meta_value) as billing_intervals,
				group_concat(mp.meta_value) as billing_periods,
				group_concat(me.meta_value) as scheduled_ends,
				group_concat(mo.meta_value) as subscription_totals
					FROM {$wpdb->prefix}posts p
				LEFT JOIN {$wpdb->prefix}postmeta ms
					ON p.ID = ms.post_id
				LEFT JOIN {$wpdb->prefix}postmeta mo
					ON p.ID = mo.post_id
				LEFT JOIN {$wpdb->prefix}postmeta mi
					ON p.ID = mi.post_id
				LEFT JOIN {$wpdb->prefix}postmeta mp
					ON p.ID = mp.post_id
				LEFT JOIN {$wpdb->prefix}postmeta me
					ON p.ID = me.post_id
			WHERE p.post_type = 'shop_subscription'
				AND p.post_status = 'wc-active'
				AND mo.meta_key = '_order_total'
				AND ms.meta_key = '_schedule_next_payment'
				AND ms.meta_value BETWEEN '%s' AND '%s'
				AND mi.meta_key = '_billing_interval'
				AND mp.meta_key = '_billing_period'
				AND me.meta_key = '_schedule_end '
			GROUP BY {$report->group_by_query}
			ORDER BY ms.meta_value ASC";

		$_query = self::add_exchange_rate_to_query( $query, 'mo.meta_value', 'WHERE', 'p', 'LEFT' );

		if ( $query <> $_query ) {

			$data = $wpdb->get_results( $wpdb->prepare( $_query,
					'%Y-%m-%d',
					date( 'Y-m-d', $report->start_date ),
					date( 'Y-m-d', strtotime( '+1 DAY', $report->end_date ) )
			) );

		}

		return $data;
	}

	/**
	 * Return query report results
	 */
	private static function get_report_data( $data, $query, $field, $post_alias, $report_class = 'WC_Admin_Report' ) {
		global $wpdb;

		$_query = self::add_exchange_rate_to_query( $query, 'order_total_post_meta.meta_value', 'WHERE', 'order_posts', 'LEFT' );

		if ( $query <> $_query ) {

			$report = new WC_Admin_Report();
			$report->calculate_current_range( self::get_current_range() );

			$query_end_date = date( 'Y-m-d', strtotime( '+1 DAY', $report->end_date ) );
		}

		return $data;
	}

	/**
	 * Helper function to get the report's current range
	 *
	 * @return string
	 */
	private static function get_current_range() {

		$current_range = ! empty( $_GET['range'] ) ? sanitize_text_field( $_GET['range'] ) : '7day';

		if ( ! in_array( $current_range, array( 'custom', 'year', 'month', 'last_month', '7day' ) ) ) {
			$current_range = '7day';
		}

		return $current_range;
	}

	/**
	 * Return product report query - WC_Report_Subscription_By_Product
	 *
	 * @return array
	 */
	public static function reports_product_query( $query ) {
		return self::add_exchange_rate_to_query( $query, 'subscription_line_items.product_total', 'WHERE', 'subscriptions', 'LEFT' );
	}

	/**
	 * Return product lifetime value report query - WC_Report_Subscription_By_Product
	 *
	 * @return array
	 */
	public static function reports_product_lifetime_value_query( $query ) {
		return self::add_exchange_rate_to_query( $query, 'wcoimeta2.meta_value', 'WHERE', 'wcorders', 'INNER' );
	}

	/**
	 * Return current customer query - WC_Report_Subscription_By_Customer
	 *
	 * @return array
	 */
	public static function current_customer_query( $query ) {
		return self::add_exchange_rate_to_query( $query, 'parent_total.meta_value', 'WHERE', 'parent_order', 'LEFT' );
	}

	/**
	 * Return customer renewal switch total query - WC_Report_Subscription_By_Customer
	 *
	 * @return array
	 */
	public static function current_customer_renewal_switch_total_query( $query ) {
		return self::add_exchange_rate_to_query( $query, 'renewal_switch_totals.meta_value', 'WHERE', 'renewal_order_posts', 'LEFT' );
	}

	/**
	 * Return customer total query - WC_Report_Subscription_By_Customer
	 *
	 * @return array
	 */
	public static function customer_total_query( $query ) {
		return self::add_exchange_rate_to_query( $query, 'parent_total.meta_value', 'WHERE', 'parent_order', 'LEFT' );
	}

	/**
	 * Return customer total renewal switch query - WC_Report_Subscription_By_Customer
	 *
	 * @return array
	 */
	public static function customer_total_renewal_switch( $query ) {
		return self::add_exchange_rate_to_query( $query, 'renewal_switch_totals.meta_value', 'INNER JOIN', 'subscription_posts', 'INNER' );
	}

	/**
	 * Return query with currency exchange rates
	 */
	private static function add_exchange_rate_to_query( $query, $line_item_field, $search, $post_alias, $join_type ) {

		$currency_rates = WCPBC_Pricing_Zones::get_currency_rates();

		if ( $currency_rates && ( $pos = strripos( $query, $search ) ) ) {

			$caseex = wcpbc_built_query_case( $line_item_field, $currency_rates );
			$query = substr( $query, 0, $pos) . ' ' . wcpbc_built_join_meta_currency( $post_alias, $join_type ) . ' ' . substr( $query, $pos );
			$query  = str_replace( $line_item_field, $caseex, $query );
		}

		return $query;
	}

	/**
	 * Return subscription report screen id
	 */
	public static function tabs_report_notice( $tabs ) {
		$tabs[] = 'subscriptions';
		return $tabs;
	}
}

endif;

WCPBC_Subscription_Reports::init();