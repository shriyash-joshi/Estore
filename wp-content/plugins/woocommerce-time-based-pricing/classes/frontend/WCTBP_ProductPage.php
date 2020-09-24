<?php 
class WCTBP_ProductPage
{
    var $already_add_to_cart_buttons_modified;
	public function __construct()
	{
		add_action('init', array( &$this, 'remove_single_product_page_add_to_cart_links'));
		add_action('woocommerce_after_single_product', array( &$this,'force_remove_single_product_page_add_to_cart_button'));
		add_action('wp_head', array( &$this, 'add_js_scripts'));
		add_action('woocommerce_after_add_to_cart_button', array( &$this, 'add_new_price_html_box'));
	}
	public function remove_single_product_page_add_to_cart_links()
	{
		global $wctbp_option_model;
		
		$disable_hide_price_feature = $wctbp_option_model->get_option('wctbp_disable_hide_price_feature', false);
		if($disable_hide_price_feature)
			return; 
		
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 ); 
		remove_action( 'woocommerce_simple_add_to_cart', 'woocommerce_simple_add_to_cart', 30 ); 
		remove_action( 'woocommerce_variable_add_to_cart', 'woocommerce_variable_add_to_cart', 30 );
		
		add_action( 'woocommerce_single_product_summary', array( &$this,'remove_single_product_page_add_to_cart_link' ), 30);
		add_action( 'woocommerce_simple_add_to_cart', array( &$this,'remove_single_product_page_simple_add_to_cart_link' ), 30);
		add_action( 'woocommerce_variable_add_to_cart', array( &$this,'remove_single_product_page_variable_add_to_cart_link' ), 30);
	}
	public function remove_single_product_page_simple_add_to_cart_link()
	{
		$this->remove_single_product_page_add_to_cart_link('simple');
	}
	public function remove_single_product_page_variable_add_to_cart_link()
	{
		$this->remove_single_product_page_add_to_cart_link('variable');
	}
	public function remove_single_product_page_add_to_cart_link($add_to_cart_code = '') 
	{
		global  $post,$product, $wctbp_product_model, $wctbp_option_model;
		$disable_hide_price_feature = $wctbp_option_model->get_option('wctbp_disable_hide_price_feature', false);
		if($disable_hide_price_feature)
			return;
	    
    	if(isset($this->already_add_to_cart_buttons_modified[$add_to_cart_code]) && isset($this->already_add_to_cart_buttons_modified[$add_to_cart_code][$post->ID]))
			return; 
		
		if(!isset($this->already_add_to_cart_buttons_modified[$add_to_cart_code]))
			$this->already_add_to_cart_buttons_modified[$add_to_cart_code] = array();
		
		$this->already_add_to_cart_buttons_modified[$add_to_cart_code][$post->ID] = true; 
		
		//wctbp_var_dump($wctbp_product_model->get_new_price_or_discount_rule(0, $product));
		if($wctbp_product_model->get_new_price_or_discount_rule(0, $product) !== 'hide_price')
		{
			switch($add_to_cart_code)
			{
				case '': woocommerce_template_single_add_to_cart(); break;
				case 'simple': woocommerce_simple_add_to_cart(); break;
				case 'variable': woocommerce_variable_add_to_cart(); break;
			}
		}
	}
	public function add_js_scripts()
	{
		if(!is_admin() && @is_product())
		{
			global $product, $post, $wctbp_option_model; 
			
			$wc_product = wc_get_product($post->ID);
			wp_enqueue_script('wctbp-product-page', WCTBP_PLUGIN_PATH.'/js/frontend-product-page.js', array('jquery'));
			$translation_array = array(
					'wctbp_ajax_url' => admin_url('admin-ajax.php'),
					'wctbp_loading_message' => __('Computing price...', 'woocommerce-time-based-pricing'),
					'product_id' => $post->ID,
					'product_type' => $wc_product->get_type(),
					'disable_product_page_live_price_display' => $wctbp_option_model->get_option('wctbp_disable_product_page_live_price_display', 'no') == 'yes' ? 'true' : 'false'
				);
			wp_localize_script( 'wctbp-product-page', 'wctbp', $translation_array );
			
			wp_enqueue_style('wctbp-product-page', WCTBP_PLUGIN_PATH.'/css/frontend-product-page.css');
		}
	}
	function add_new_price_html_box()
	{
		/* global $post;
		$product = wc_get_product($post->ID);
		echo wc_price(WCTBP_Tax::get_product_price_with_tax_according_settings($product)); */
	}
	//forced
	public function force_remove_single_product_page_add_to_cart_button()
	{
		//FORCING ADD TO CART HIDING
		//note: some themes doens't lauch the correct event
		global $product, $wctbp_product_model, $disable_hide_price_feature, $wctbp_option_model;
		$disable_hide_price_feature = $wctbp_option_model->get_option('wctbp_disable_hide_price_feature', false);
		if($disable_hide_price_feature)
			return;
		if($wctbp_product_model->get_new_price_or_discount_rule($product->get_price('wctbp'), $product) === 'hide_price'):
		?>
		<script>
		function wctbp_remove_add_to_cart(event)
			{
				event.stopImmediatePropagation();
				event.preventDefault();
				return false;
			}
			
		
			jQuery('.single_add_to_cart_button').css('display', 'none');
			jQuery('.cart').css('display', 'none');
			jQuery('.cart').css('opacity', 0);
			jQuery('.qty-cart').css('display', 'none');
			jQuery('.qty-cart').css('opacity', 0);
			jQuery('.single_add_to_cart_button').click(wctbp_remove_add_to_cart);
		jQuery(document).ready(function()
		{	
			jQuery('.qty-cart').remove();
			jQuery('.single_add_to_cart_button').remove();
		});
		</script>
		<?php
		endif;
	}
	//end Remove
}
?>