<?php
/**
 * WooCommerce Price Based Country Zones List table.
 *
 * @package WCPBC\Admin
 * @since   1.6.0
 * @version 1.8.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * WCPBC_Admin_Zone_List_Table Class
 */
class WCPBC_Admin_Zone_List_Table extends WP_List_Table {

	/**
	 * Base currency
	 *
	 * @var string
	 */
	protected $base_currency;

	/**
	 * Initialize the regions table list
	 */
	public function __construct() {
		$this->base_currency = get_option( 'woocommerce_currency' );

		parent::__construct(
			array(
				'singular' => __( 'Pricing zone', 'woocommerce-product-price-based-on-countries' ),
				'plural'   => __( 'Pricing zones', 'woocommerce-product-price-based-on-countries' ),
				'ajax'     => false,
			)
		);
	}

	/**
	 * Get a list of CSS classes for the WP_List_Table table tag.
	 *
	 * @return array List of CSS classes for the table tag.
	 */
	protected function get_table_classes() {
		return array( 'widefat', 'fixed', $this->_args['plural'] );
	}

	/**
	 * Get list columns
	 *
	 * @return array
	 */
	public function get_columns() {
		return apply_filters(
			'wc_price_based_country_settings_zone_columns',
			array(
				'cb'        => '',
				'name'      => __( 'Zone name', 'woocommerce-product-price-based-on-countries' ),
				'countries' => __( 'Countries', 'woocommerce-product-price-based-on-countries' ),
				'currency'  => __( 'Currency', 'woocommerce-product-price-based-on-countries' ),
			)
		);
	}

	/**
	 * Default column handler.
	 *
	 * @param WCPBC_Zone $item        Item being shown.
	 * @param string     $column_name Name of column being shown.
	 * @return string Default column output.
	 */
	public function column_default( $item, $column_name ) {
		return apply_filters( 'wc_price_based_country_settings_zone_column_' . $column_name, $item );
	}

	/**
	 * Column cb.
	 *
	 * @param WCPBC_Zone $zone Pricing zone instance.
	 * @return string
	 */
	public function column_cb( $zone ) {
		if ( $zone->get_zone_id() ) {
			return '<span></span>';
		} else {
			return '<span class="zone-worldwide-icon"></span>';
		}
	}

	/**
	 * Return name column.
	 *
	 * @param WCPBC_Zone $zone Pricing zone instance.
	 * @return string
	 */
	public function column_name( $zone ) {

		if ( $zone->get_zone_id() ) {

			$edit_url    = admin_url( 'admin.php?page=wc-settings&tab=price-based-country&section=zones&zone_id=' . $zone->get_zone_id() );
			$actions     = array(
				'id'    => sprintf( 'Slug: %s', $zone->get_zone_id() ),
				'edit'  => '<a href="' . esc_url( $edit_url ) . '">' . __( 'Edit', 'woocommerce-product-price-based-on-countries' ) . '</a>',
				'trash' => '<a class="submitdelete wcpbc-delete-zone" title="' . esc_attr__( 'Delete', 'woocommerce-product-price-based-on-countries' ) . '" href="' . esc_url( wp_nonce_url( add_query_arg( array( 'delete_zone' => $zone->get_zone_id() ), admin_url( 'admin.php?page=wc-settings&tab=price-based-country&section=zones' ) ), 'wc-price-based-country-delete-zone' ) ) . '">' . __( 'Delete', 'woocommerce-product-price-based-on-countries' ) . '</a>',
			);
			$row_actions = array();
			foreach ( $actions as $action => $link ) {
				$row_actions[] = '<span class="' . esc_attr( $action ) . '">' . $link . '</span>';
			}

			$output  = sprintf( '<a href="%1$s">%2$s</a>', esc_url( $edit_url ), $zone->get_name() );
			$output .= '<div class="row-actions">' . implode( ' | ', $row_actions ) . '</div>';

		} else {
			$output = '<span>' . $zone->get_name() . '</span><div class="row-actions">&nbsp;</div>';
		}

		return $output;
	}

	/**
	 * Return countries column.
	 *
	 * @param WCPBC_Zone $zone Pricing zone instance.
	 * @return string
	 */
	public function column_countries( $zone ) {
		$output = '';

		if ( $zone->get_zone_id() ) {
			$countries = array();
			foreach ( $zone->get_countries() as $country ) {
				$countries[] = WC()->countries->countries[ $country ];
			}
			$output = implode( ', ', $countries );

		} else {
			$output = __( 'Default pricing and currency are used for countries that are not included in any other pricing zone.', 'woocommerce-product-price-based-on-countries' );
		}

		return $output;
	}

	/**
	 * Return currency column
	 *
	 * @param WCPBC_Zone $zone Pricing zone instance.
	 * @return string
	 */
	public function column_currency( $zone ) {
		$currencies = get_woocommerce_currencies();

		$output = $currencies[ $zone->get_currency() ] . ' (' . get_woocommerce_currency_symbol( $zone->get_currency() ) . ') <br />';

		if ( $zone->get_zone_id() ) {
			$output .= '<span class="description">1 ' . $this->base_currency . ' = ' . wc_format_localized_decimal( $zone->get_exchange_rate() ) . ' ' . $zone->get_currency() . '</span>';
			$output  = apply_filters( 'wc_price_based_country_settings_zone_after_column_currency', $output, $zone );
		}
		return $output;
	}

	/**
	 * Prepare table list items.
	 */
	public function prepare_items() {

		$default_zone = WCPBC_Pricing_Zones::create();
		$default_zone->set_name( __( 'Countries not covered by your other zones', 'woocommerce-product-price-based-on-countries' ) );
		$default_zone->set_currency( $this->base_currency );

		$zones   = WCPBC_Pricing_Zones::get_zones();
		$zones[] = $default_zone;

		$this->_column_headers = array( $this->get_columns(), array(), array() );
		$this->items           = $zones;
	}

	/**
	 * Generate the table navigation above or below the table. No need the tablenav section.
	 *
	 * @param string $which The location of the extra table nav markup: 'top' or 'bottom'.
	 */
	protected function display_tablenav( $which ) {
	}

	/**
	 * Generates content for a single row of the table.
	 *
	 * @param WCPBC_Zone $zone Pricing zone instance.
	 */
	public function single_row( $zone ) {
		if ( $zone->get_zone_id() ) {
			parent::single_row( $zone );
		} else {
			echo '<tr class="zone-worldwide">';
			$this->single_row_columns( $zone );
			echo '</tr>';
		}
	}
}
