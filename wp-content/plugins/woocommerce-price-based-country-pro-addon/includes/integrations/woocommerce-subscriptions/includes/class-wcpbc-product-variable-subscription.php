<?php
/**
 * Overwrite Subscription Product Variable Class
 *
 * @class 		WCPBC_Product_Variable_Subscription
 * @category	Class
 * @since		1.1.2
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WCPBC_Product_Variable_Subscription extends WC_Product_Variable_Subscription_legacy {
	
	/**
	 * Construct
	 *
	 * @access public
	 * @param mixed $product
	 */
	public function __construct( $product ) {

		parent::__construct( $product );			
		$this->subscription_sign_up_fee = get_post_meta( $this->id, '_subscription_sign_up_fee', true );		
		$this->subscription_price 		= get_post_meta( $this->id, '_price', true );				
	}	
}
