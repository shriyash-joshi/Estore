<?php 
class WCTBP_Text
{
	var $cache = null;
	public function __construct()
	{
	}
	public function get_texts()
	{
		if(isset($this->cache))
			return $this->cache;
		
		$all_data = array();
		$all_data['sale_badge_text'] = get_field('wctbp_sale_badge_text', 'option'); 		
		$all_data['sale_badge_text'] = $all_data['sale_badge_text'] != null ? $all_data['sale_badge_text'] : ""; 		
		
		$all_data['product_page_after_price_text'] = get_field('wctbp_product_page_after_price_text', 'option'); 		
		$all_data['product_page_after_price_text'] = $all_data['product_page_after_price_text'] != null ? $all_data['product_page_after_price_text'] : ""; 	
		
		$all_data['product_page_before_total_price_text'] = get_field('wctbp_product_page_before_total_price_text', 'option'); 		
		$all_data['product_page_before_total_price_text'] = $all_data['product_page_before_total_price_text'] != null ? $all_data['product_page_before_total_price_text'] : __( ' - Total: ', 'woocommerce-time-based-pricing' ); 		
		
		$all_data['product_page_discount_percentage_text'] = get_field('wctbp_product_page_discount_percentage_text', 'option'); 		
		$all_data['product_page_discount_percentage_text'] = isset($all_data['product_page_discount_percentage_text']) ? str_replace("%", "%%", $all_data['product_page_discount_percentage_text']) : __( '%s off!', 'woocommerce-time-based-pricing' ); 		
		$all_data['product_page_discount_percentage_text'] = str_replace("{value}", "%s", $all_data['product_page_discount_percentage_text']);
		
		$all_data['shop_page_discount_percentage_text'] = get_field('wctbp_shop_page_discount_percentage_text', 'option'); 		
		$all_data['shop_page_discount_percentage_text'] = isset($all_data['shop_page_discount_percentage_text']) ? str_replace("%", "%%", $all_data['shop_page_discount_percentage_text']) : __( '%s off!', 'woocommerce-time-based-pricing' ); 		
		$all_data['shop_page_discount_percentage_text'] = str_replace("{value}", "%s", $all_data['shop_page_discount_percentage_text']);

		$all_data['shop_page_discount_percentage_variable_product_text'] = get_field('wctbp_shop_page_discount_percentage_variable_product_text', 'option'); 		
		$all_data['shop_page_discount_percentage_variable_product_text'] = isset($all_data['shop_page_discount_percentage_variable_product_text']) ? str_replace("%", "%%", $all_data['shop_page_discount_percentage_variable_product_text']) : __( 'up to %s off!', 'woocommerce-time-based-pricing' ); 		
		$all_data['shop_page_discount_percentage_variable_product_text'] = str_replace("{value}", "%s", $all_data['shop_page_discount_percentage_variable_product_text']);

		$all_data['shop_and_cart_pages_saved_amount_label'] = get_field('wctbp_shop_and_cart_pages_saved_amount_label', 'option'); 		
		$all_data['shop_and_cart_pages_saved_amount_label'] = $all_data['shop_and_cart_pages_saved_amount_label'] != null ? $all_data['shop_and_cart_pages_saved_amount_label'] : __( 'You saved', 'woocommerce-time-based-pricing' ); 		
		
		$this->cache = $all_data;
		
		return $all_data;
	}
}
?>