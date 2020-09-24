<?php
/**
 * Extend the Woocommerce regular order to get the amounts expressed in the base currency
 *
 * @package WCPBC/Classes
 * @version 2.4.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WC_Order' ) ) {
	include_once WC_ABSPATH . 'includes/class-wc-order.php';
}

/**
 * WC_Order_Base_Currency Class.
 */
class WCPBC_Order_Base_Currency extends WC_Order {

	/**
	 * Pricing zone object.
	 *
	 * @var WCPBC_Pricing_Zone_Pro
	 */
	protected $zone;

	/**
	 * Base currency
	 *
	 * @var string
	 */
	protected $base_currency;

	/**
	 * Constructor.
	 *
	 * @param int $order_id WooCommerce Order ID.
	 */
	public function __construct( $order_id ) {
		parent::__construct( $order_id );

		$this->zone          = WCPBC_Pricing_Zones::get_zone_from_order( $this );
		$this->base_currency = wcpbc_get_base_currency();
		if ( $this->need_convert() ) {
			$this->calculate_taxes();
			$this->calculate_totals( false );
		}
	}

	/**
	 * Don't save
	 *
	 * @return bool
	 */
	public function save() {
		return false;
	}

	/**
	 * Need the order amount to be converts to base currency.
	 *
	 * @return bool
	 */
	protected function need_convert() {
		return ! empty( $this->zone ) && $this->zone->get_currency() !== $this->base_currency;
	}

	/**
	 * Gets order currency.
	 *
	 * @param  string $context View or edit context.
	 * @return string
	 */
	public function get_currency( $context = 'view' ) {
		return $this->base_currency;
	}

	/**
	 * Get line subtotal - this is the cost before discount.
	 *
	 * @param object $item Item to get total from.
	 * @param bool   $inc_tax (default: false).
	 * @param bool   $round (default: true).
	 * @return float
	 */
	public function get_line_subtotal( $item, $inc_tax = false, $round = true ) {
		$amount = parent::get_line_subtotal( $item, $inc_tax, $round );
		if ( empty( $item->is_base_currency ) && $this->need_convert() && ! empty( $amount ) ) {
			$amount = $this->zone->get_base_currency_amount( $amount );
		}
		return $amount;
	}

	/**
	 * Return the Order Base Currency Item Tax class
	 *
	 * @param string $classname Class name.
	 * @return string
	 */
	public function get_order_item_tax_classname( $classname ) {
		if ( 'WC_Order_Item_Tax' === $classname ) {
			$classname = 'WCPBC_Order_Base_Currency_Item_Tax';
		}
		return $classname;
	}

	/**
	 * Return an array of items/products within this order.
	 *
	 * @param string|array $types Types of line items to get (array or string).
	 * @return WC_Order_Item[]
	 */
	public function get_items( $types = 'line_item' ) {

		add_filter( 'woocommerce_get_order_item_classname', array( $this, 'get_order_item_tax_classname' ) );

		$items = array();
		foreach ( parent::get_items( $types ) as $item ) {
			if ( empty( $item->is_base_currency ) && $this->need_convert() ) {
				if ( is_callable( array( $item, 'set_subtotal' ) ) ) {
					$item->set_subtotal( $this->zone->get_base_currency_amount( $item->get_subtotal() ) );
				}
				if ( is_callable( array( $item, 'set_total' ) ) ) {
					$item->set_total( $this->zone->get_base_currency_amount( $item->get_total() ) );
				}
				$item->is_base_currency = true;
			}
			$items[] = $item;
		}

		remove_filter( 'woocommerce_get_order_item_classname', array( $this, 'get_order_item_tax_classname' ) );

		return $items;
	}

	/**
	 * Retrun the amount converts to the base currency
	 *
	 * @param  float $amount Amount to convert.
	 * @return float
	 */
	protected function get_base_amount( $amount ) {
		if ( $this->need_convert() && ! empty( $amount ) ) {
			$amount = $this->zone->get_base_currency_amount( $amount );
		}
		return $amount;
	}
}
