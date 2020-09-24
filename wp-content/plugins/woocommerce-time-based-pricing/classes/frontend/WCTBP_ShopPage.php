<?php 
class WCTBP_ShopPage
{
	var $already_add_to_cart_buttons_modified = array();
	public function __construct()
	{
		add_action( 'wp', array(&$this, 'remove_ajax') );
		add_action('wp_head', array( &$this,'add_meta'));
		add_action('init', array( &$this,'init'));
	}
	public function init()
	{
		global  $wctbp_option_model;
		
		$disable_hide_price_feature = $wctbp_option_model->get_option('wctbp_disable_hide_price_feature', false);
		if($disable_hide_price_feature)
			return; 
		
		//price hide
		add_filter('woocommerce_loop_add_to_cart_link',array( &$this,'remove_shop_page_add_to_cart' ), 10, 2);
	}
	public function remove_ajax()
	{
		if(function_exists('is_shop') && is_shop())
		{
			global $wctbp_option_model;
			$disable_ajax = $wctbp_option_model->get_option('wctpb_disable_shop_page_ajax_add_to_cart_function', 'yes');
			if($disable_ajax == 'yes')
				wp_enqueue_script('wctbp-shop-page', WCTBP_PLUGIN_PATH. '/js/frontend-shop-page.js' ,array('jquery')); 
		
		}
		
	}
	public function add_meta()
	{
		global $wctbp_option_model;
		$disable_ajax = $wctbp_option_model->get_option('wctpb_disable_shop_page_ajax_add_to_cart_function', 'yes');
		if($disable_ajax == 'yes')
		{
			echo '<meta http-equiv="Cache-control" content="no-cache">';
			echo '<meta http-equiv="Expires" content="-1">'; 
		}
	}
	//Remove "Add to cart" buttons for Expired products (if option has been enabled)
	public function remove_shop_page_add_to_cart( $add_to_cart_text, $product ) 
	{
		global $wctbp_product_model,$wctbp_option_model;
		
		//wctbp_var_dump(isset($wctbp_product_model->id_to_new_price_cache[$product->id]));
		if(isset($this->already_add_to_cart_buttons_modified[$product->get_id()]))
			return;
		$this->already_add_to_cart_buttons_modified[$product->get_id()] = true; 
		
		if($wctbp_product_model->get_new_price_or_discount_rule($product->get_price('wctbp'), $product) !== 'hide_price')
			echo $add_to_cart_text; 
	}
}
?>