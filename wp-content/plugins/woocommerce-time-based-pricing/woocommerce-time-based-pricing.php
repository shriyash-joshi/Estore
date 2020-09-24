<?php 
/*
Plugin Name: WooCommerce Pricing & Discounts!
Description: Set products prices or discounts per time periods or recurring events according to product quantities and user roles.
Author: Lagudi Domenico
Version: 12.9
*/

/* 
Copyright: WooCommerce Pricing! uses the ACF PRO plugin. ACF PRO files are not to be used or distributed outside of the WooCommerce Pricing! plugin.
*/
define('WCTBP_PLUGIN_PATH', rtrim(plugin_dir_url(__FILE__), "/") )  ;
define('WCTBP_PLUGIN_ABS_PATH', plugin_dir_path( __FILE__ ) );

if ( !defined('WP_CLI') && ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ||
					   (is_multisite() && array_key_exists( 'woocommerce/woocommerce.php', get_site_option('active_sitewide_plugins') ))
					 )	
	)
{
	//For some reasins the theme editor in some installtion won't work. This directive will prevent that.
	if(isset($_POST['action']) && $_POST['action'] == 'edit-theme-plugin-file')
		return;
	
	$wctbp_id = 14679278;
	$wctbp_name = "WooCommerce Pricing & Discounts!";
	$wctbp_activator_slug = "wctbp-activator";
	
	//Com
	include_once('classes/com/WCTBP_Acf.php');
	include_once( "classes/com/WCTBP_Globals.php");
	require_once('classes/admin/WCTBP_ActivationPage.php');
	
	add_action('admin_notices', 'wctbp_admin_notices' );
	add_action('init', 'wctbp_init' );
		add_action('admin_menu', 'wctbp_init_act');
	if(defined('DOING_AJAX') && DOING_AJAX)
			wctbp_init_act();
}
function wctbp_init()
{
	load_plugin_textdomain('woocommerce-time-based-pricing', false, basename( dirname( __FILE__ ) ) . '/languages' );
	/* if(is_admin())
		wctbp_init_act(); */
}
function wctbp_init_act()
{
	global $wctbp_activator_slug, $wctbp_name, $wctbp_id;
	new WCTBP_ActivationPage($wctbp_activator_slug, $wctbp_name, 'woocommerce-time-based-pricing', $wctbp_id, WCTBP_PLUGIN_PATH);
}
function wctbp_admin_notices()
{
	global $wctbp_notice, $wctbp_name, $wctbp_activator_slug;
	if($wctbp_notice && (!isset($_GET['page']) || $_GET['page'] != $wctbp_activator_slug))
	{
		 ?>
		<div class="notice notice-success">
			<p><?php echo sprintf(__( 'To complete the <span style="color:#96588a; font-weight:bold;">%s</span> plugin activation, you must verify your purchase license. Click <a href="%s">here</a> to verify it.', 'woocommerce-time-based-pricing' ), $wctbp_name, get_admin_url()."admin.php?page=".$wctbp_activator_slug); ?></p>
		</div>
		<?php
	}
}
function wctbp_setup()
{
	global $wctbp_cart, $wctbp_wpml_helper, $wctbp_option_model, $wctbp_user_model, $wctbp_time_model, $wctbp_product_model, 
	$wctbp_cart_addon, $wctbp_text_model, $wctbp_product_table_addon, $wctbp_dashboard, $wctbp_price_changer, $wctbp_shop_page, 
	$wctbp_product_page, $wctbp_cart_item_fragment_addon;
	
	$wctbp_cart = array();
	if(!class_exists('WCTBP_Tax'))
	{
		require_once('classes/com/WCTBP_Tax.php');
	}
	if(!class_exists('WCTBP_Wpml'))
	{
		require_once('classes/com/WCTBP_Wpml.php');
		$wctbp_wpml_helper = new WCTBP_Wpml();
	}
	if(!class_exists('WCTBP_Option'))
	{
		require_once('classes/com/WCTBP_Option.php');
		$wctbp_option_model = new WCTBP_Option();
	}
	
	if(!class_exists('WCTBP_User'))
	{
		require_once('classes/com/WCTBP_User.php');
		$wctbp_user_model = new WCTBP_User();
	}
	if(!class_exists('WCTBP_Time'))
	{
		require_once('classes/com/WCTBP_Time.php');
		$wctbp_time_model = new WCTBP_Time();
	}
	if(!class_exists('WCTBP_Product'))
	{
		require_once('classes/com/WCTBP_Product.php');
		$wctbp_product_model = new WCTBP_Product();
	}
	
	if(!class_exists('WCTBP_Cart'))
	{
		require_once('classes/com/WCTBP_Cart.php');
		$wctbp_cart_addon = new WCTBP_Cart();
	}
	if(!class_exists('WCTBP_Text'))
	{
		require_once('classes/com/WCTBP_Text.php');
		$wctbp_text_model = new WCTBP_Text();
	}
	
	//Admin
	if(!class_exists('WCTBP_ProductTablePage'))
	{
		require_once('classes/admin/WCTBP_ProductTablePage.php');
		$wctbp_product_table_addon = new WCTBP_ProductTablePage();
	}
	if(!class_exists('WCTBP_TextCustomizerPage'))
	{
		require_once('classes/admin/WCTBP_TextCustomizerPage.php');
		
	}
	if(!class_exists('WCTBP_Dashboard'))
	{
		require_once('classes/admin/WCTBP_Dashboard.php');
		$wctbp_dashboard = new WCTBP_Dashboard();
	}
	if(!class_exists('WCTBP_PricesConfiguratorPage'))
		require_once('classes/admin/WCTBP_PricesConfiguratorPage.php');
	if(!class_exists('WCTBP_GeneralOptionsPage'))
		require_once('classes/admin/WCTBP_GeneralOptionsPage.php');
	
	//Frontend
	if(!class_exists('WCTBP_PriceChanger'))
	{
		require_once('classes/frontend/WCTBP_PriceChanger.php');
		$wctbp_price_changer = new WCTBP_PriceChanger();
	}	
	if(!class_exists('WCTBP_ShopPage'))
	{
		require_once('classes/frontend/WCTBP_ShopPage.php');
		$wctbp_shop_page = new WCTBP_ShopPage();
	}
	if(!class_exists('WCTBP_ProductPage'))
	{
		require_once('classes/frontend/WCTBP_ProductPage.php');
		$wctbp_product_page = new WCTBP_ProductPage();
	}
	if(!class_exists('WCTBP_CartItemTableFragment'))
	{
		require_once('classes/frontend/WCTBP_CartItemTableFragment.php');
		$wctbp_cart_item_fragment_addon = new WCTBP_CartItemTableFragment();
	}
	
	add_action('admin_menu', 'wctbp_init_admin_panel');
	add_action('admin_init', 'wctbp_admin_init');
}
function wctbp_admin_init()
{
	$remove = remove_submenu_page( 'woocommerce-time-based-pricing', 'woocommerce-time-based-pricing');
}	
function wctbp_init_admin_panel()
{
	if(!current_user_can('manage_woocommerce'))
		return;
	$place = $place = wctbp_get_free_menu_position(55, 0.1);
	
	$hookname  = add_menu_page( __( 'WooCommerce Pricing!', 'woocommerce-time-based-pricing' ), __( 'WooCommerce Pricing!', 'woocommerce-time-based-pricing' ), 'manage_woocommerce', 'woocommerce-time-based-pricing', null, WCTBP_PLUGIN_PATH."/images/dollar-icon.png", (string)$place );
	//add_submenu_page('woocommerce-time-based-pricing', __('Configurator','woocommerce-time-based-pricing'), __('Configurator','woocommerce-time-based-pricing'), 'edit_shop_orders', 'woocommerce-time-based-pricing-bulk-import', 'wctbp_render_bulk_import_page');
	$price_configurator = new WCTBP_PricesConfiguratorPage();
	$general_options = new WCTBP_GeneralOptionsPage();
	$wctbp_text_customizer_page = new WCTBP_TextCustomizerPage();
	
	//wctbp_var_dump($remove );	
}
function wctbp_get_free_menu_position($start, $increment = 0.1)
{
	foreach ($GLOBALS['menu'] as $key => $menu) {
		$menus_positions[] = $key;
	}
	
	if (!in_array($start, $menus_positions)) return $start;

	/* the position is already reserved find the closet one */
	while (in_array($start, $menus_positions)) 
	{
		$start += $increment;
	}
	return $start;
}
function wctbp_var_dump($data)
{
	echo "<pre>";
	var_dump($data);
	echo "</pre>";
}
?>