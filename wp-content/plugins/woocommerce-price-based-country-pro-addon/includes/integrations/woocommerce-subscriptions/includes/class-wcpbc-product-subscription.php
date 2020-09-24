<?php
/**
 * Overwrite Subscription Product Class
 *
 * @class 		WCPBC_Product_Subscription
 * @category	Class
 * @since		1.1.2
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WCPBC_Product_Subscription extends WC_Product_Subscription_Legacy {		

	/**
	 * Create a simple subscription product object.
	 *
	 * @access public
	 * @param mixed $product
	 */
	public function __construct( $product ) {		
		parent::__construct( $product );		
		$this->subscription_sign_up_fee = get_post_meta( $this->id, '_subscription_sign_up_fee', true );		
	}	
}
