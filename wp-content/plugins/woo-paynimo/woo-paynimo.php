<?php

/*
Plugin Name: WooCommerce Paynimo  
Plugin URI: https://zessta.com
Description: WooCommerce Paynimo payment gateway integration
Version: 1.0
*/

add_action( 'plugins_loaded', 'woo_paynimo_init', 0 );
function woo_paynimo_init() {
    //if condition use to do nothin while WooCommerce is not installed
	if ( ! class_exists( 'WC_Payment_Gateway' ) ) return;
    include_once( 'woo-paynimo-class.php' );
    
	// class add it too WooCommerce
    add_filter( 'woocommerce_payment_gateways', 'zess_add_paynimo' );
    
	function zess_add_paynimo( $methods ) {
		$methods[] = 'WP_Gateway_Paynimo';
		return $methods;
	}
}
// Add custom action links
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'zess_paynimo_action_links' );
function zess_paynimo_action_links( $links ) {
	$plugin_links = array(
		'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout' ) . '">' . __( 'Settings', 'woo-paynimo' ) . '</a>',
	);
	return array_merge( $plugin_links, $links );
}