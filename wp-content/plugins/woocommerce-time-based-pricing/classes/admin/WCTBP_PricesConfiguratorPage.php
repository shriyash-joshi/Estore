<?php 
class WCTBP_PricesConfiguratorPage
{
	public static $page_id = "woocommerce-pricing_page_acf-options-pricing-bulk-editor";
	public static $page_url_par = "acf-options-pricing-bulk-editor";
	public function __construct()
	{
		//add_filter('acf/init', array(&$this,'init_options_menu'));
		$this->init_options_menu();
		
		add_action( 'wp_print_scripts', array(&$this,'enqueue_custom_scripts') );
	}
	function enqueue_custom_scripts()
	{
		$current_screen = get_current_screen();
		
		if ( class_exists( 'woocommerce' ) && ((isset($_GET['page']) && $_GET['page'] == 'acf-options-pricing-bulk-editor') || (isset($current_screen) && $current_screen->post_type == 'product'))) 
		{
			wp_enqueue_style( 'wctbp-tooltip', WCTBP_PLUGIN_PATH.'/css/tooltip.css' );
			
		} 
	}
	function init_options_menu()
	{
		if( function_exists('acf_add_options_page') ) 
		{
			/*acf_add_options_page(array(
				'page_title' 	=> 'Menu name',
				'menu_title'	=> 'Menu name',
				'menu_slug' 	=> 'wcuf-option-menu',
				'capability'	=> 'edit_posts',
				'icon_url'      => 'dashicons-upload',
				'redirect'		=> false
			));*/
			
			 acf_add_options_sub_page(array(
				'page_title' 	=> 'Pricing! Bulk editor',
				'menu_title'	=> 'Pricing! Bulk editor',
				'parent_slug'	=> 'woocommerce-time-based-pricing',
			));
			
			
			
			add_action( 'current_screen', array(&$this, 'cl_set_global_options_pages') );
		}
	}
	/**
	 * Force ACF to use only the default language on some options pages
	 */
	function cl_set_global_options_pages($current_screen) 
	{
	  if(!is_admin())
		  return;
	  global $wctbp_wpml_helper;
	 //wctbp_var_dump($current_screen->id);
	  $page_ids = array(
		WCTBP_PricesConfiguratorPage::$page_id
	  );
	  
	  if (in_array($current_screen->id, $page_ids)) 
	  {
		$wctbp_wpml_helper->switch_to_default_language();
		add_filter('acf/settings/current_language', array(&$this, 'cl_acf_set_language'), 100);
	  }
	}
	

	function cl_acf_set_language() 
	{
	  return acf_get_setting('default_language');
	}

	/**
	 * Wrapper around get_field() to get the "global" option values.
	 * This is the function you'll want to use in your templates instead of get_field() for "global" options.
	 */
	/* function get_global_option($name) 
	{
	  add_filter('acf/settings/current_language',  array(&$this, 'cl_acf_set_language'), 100);
	  $option = get_field($name, 'option');
	  remove_filter('acf/settings/current_language', array(&$this,'cl_acf_set_language'), 100);
	  return $option;
	} */
}
?>