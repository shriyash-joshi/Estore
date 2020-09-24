<?php
/**
 * Extend order item tax to prevent save action
 *
 * @package WCPBC/Classes
 * @version 2.4.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WC_Order_Item_Tax' ) ) {
	include_once WC_ABSPATH . 'includes/class-wc-order-item-tax.php';
}

/**
 * WC_Order_Base_Currency Class.
 */
class WCPBC_Order_Base_Currency_Item_Tax extends WC_Order_Item_Tax {
	/**
	 * Don't save
	 *
	 * @return bool
	 */
	public function save() {
		return false;
	}
}


