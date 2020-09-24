<?php
/**
 * Plugin Name: Order Assessment Email
 * Description: Sending order assessment links to student / parent
 * Author: Sreenivas
 * Version: 1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function add_order_assessment_email( $email_classes ) {

	// include our custom email class
	require_once( 'includes/class-wc-order-assessment-email.php' );
	// add the email class to the list of email classes that WooCommerce loads
	$email_classes['WC_Order_Assessment_Email'] = new WC_Order_Assessment_Email();
	return $email_classes;

}
add_filter( 'woocommerce_email_classes', 'add_order_assessment_email' );
