<?php 
class WCTBP_Tax
{
	function __construct()
	{
	}
	static function get_product_price_with_tax_according_settings($product, $qty = '', $price = '')
	{
		$tax_status = get_option( 'woocommerce_tax_display_shop' );
		if(function_exists ('wc_get_price_excluding_tax'))
			return 'incl' === $tax_status ? wc_get_price_including_tax( $product, array( 'qty' => $qty, 'price' => $price ) ) : wc_get_price_excluding_tax( $product, array( 'qty' => $qty, 'price' => $price ) );
		
		return 'incl' === $tax_status ?  $product->get_price_including_tax($qty, $price) : $product->get_price_excluding_tax($qty, $price);
	}
	static function get_product_price_including_tax($product, $quantity = '', $price = '')
	{
		/* $args = wp_parse_args( $args, array(
			'qty'   => '',
			'price' => '',
		  ) );*/
		 
		$args = array('qty' => $quantity, 'price' => $price);
		if(function_exists ('wc_get_price_including_tax'))
			return wc_get_price_including_tax($product, $args);
			
		return $product->get_price_including_tax($quantity, $price);
	}
	static function get_product_price_excluding_tax($product, $quantity = '', $price = '')
	{
		$args = array('qty' => $quantity, 'price' => $price);
		if(function_exists ('wc_get_price_excluding_tax'))
			return wc_get_price_excluding_tax($product, $args);
			
		return $product->get_price_excluding_tax($quantity, $price);
	}
}
?>