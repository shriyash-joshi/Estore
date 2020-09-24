<?php 
$wctbp_active_plugins = get_option('active_plugins');
$wctbp_acf_pro = 'advanced-custom-fields-pro/acf.php';
$wctbp_acf_pro_is_aleady_active = in_array($wctbp_acf_pro, $wctbp_active_plugins) || class_exists('acf') ? true : false;
if(!$wctbp_acf_pro_is_aleady_active)
	include_once( WCTBP_PLUGIN_ABS_PATH . '/classes/acf/acf.php' );

$wctbp_hide_menu = true;
if ( ! function_exists( 'is_plugin_active' ) ) 
{
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' ); 
}
/* Checks to see if the acf pro plugin is activated  */
if ( is_plugin_active('advanced-custom-fields-pro/acf.php') )  {
	$wctbp_hide_menu = false;
}

/* Checks to see if the acf plugin is activated  */
if ( is_plugin_active('advanced-custom-fields/acf.php') ) 
{
	add_action('plugins_loaded', 'wctbp_load_acf_standard_last', 10, 2 ); //activated_plugin
	add_action('deactivated_plugin', 'wctbp_detect_plugin_deactivation', 10, 2 ); //activated_plugin
	$wctbp_hide_menu = false;
}
function wctbp_detect_plugin_deactivation(  $plugin, $network_activation ) { //after
   // $plugin == 'advanced-custom-fields/acf.php'
	//wctbp_var_dump("wctbp_detect_plugin_deactivation");
	$acf_standard = 'advanced-custom-fields/acf.php';
	if($plugin == $acf_standard)
	{
		$active_plugins = get_option('active_plugins');
		$this_plugin_key = array_keys($active_plugins, $acf_standard);
		if (!empty($this_plugin_key)) 
		{
			foreach($this_plugin_key as $index)
				unset($active_plugins[$index]);
			update_option('active_plugins', $active_plugins);
			//forcing
			deactivate_plugins( plugin_basename( WP_PLUGIN_DIR.'/advanced-custom-fields/acf.php') );
		}
	}
} 
function wctbp_load_acf_standard_last($plugin, $network_activation = null) { //before
	$acf_standard = 'advanced-custom-fields/acf.php';
	$active_plugins = get_option('active_plugins');
	$this_plugin_key = array_keys($active_plugins, $acf_standard);
	if (!empty($this_plugin_key)) 
	{ 
		foreach($this_plugin_key as $index)
			//array_splice($active_plugins, $index, 1);
			unset($active_plugins[$index]);
		//array_unshift($active_plugins, $acf_standard); //first
		array_push($active_plugins, $acf_standard); //last
		update_option('active_plugins', $active_plugins);
	} 
}
if(!$wctbp_acf_pro_is_aleady_active)
	add_filter('acf/settings/path', 'wctbp_acf_settings_path');
function wctbp_acf_settings_path( $path ) 
{
 
    // update path
    $path = WCTBP_PLUGIN_ABS_PATH. '/classes/acf/';
    
    // return
    return $path;
    
}
if(!$wctbp_acf_pro_is_aleady_active)
	add_filter('acf/settings/dir', 'wctbp_acf_settings_dir');
function wctbp_acf_settings_dir( $dir ) {
 
    // update path
    $dir =  WCTBP_PLUGIN_PATH . '/classes/acf/';
    
    // return
    return $dir;
    
}

function wctbp_acf_init() {
    
    include WCTBP_PLUGIN_ABS_PATH . "/assets/fields.php";
    
}

add_action('acf/init', 'wctbp_acf_init'); 

//hide acf menu
if($wctbp_hide_menu)	
	add_filter('acf/settings/show_admin', '__return_false');


//********************************************
//custom role field
add_action('acf/include_field_types', 'wctbp_include_field_types_role_selector');

function wctbp_include_field_types_role_selector( $version ) {
	
 if(!class_exists('acf_field_role_selector'))
	include_once(WCTBP_PLUGIN_ABS_PATH.'/classes/com/vendor/acf-role-selector-field/acf-role_selector-v5.php');
}

add_action('acf/include_field_types', 'wctbp_include_field_types_variant_selector');

function wctbp_include_field_types_variant_selector( $version ) {
	
 
 if(!class_exists('acf_field_product_variant_selector'))
	include_once(WCTBP_PLUGIN_ABS_PATH.'/classes/com/vendor/acf-product-variant-field/acf-product_variant_selector.php');
}

function wctb_include_field_types_unique_id( $version ) {
	if(!class_exists('acf_field_unique_id'))
		include_once(WCTBP_PLUGIN_ABS_PATH.'/classes/com/vendor/acf-field-unique-id/acf-unique_id-v5.php');
}
add_action('acf/include_field_types', 'wctb_include_field_types_unique_id');


// Custom filters
function wctbp_get_variation_complete_name($variation_id)
{
	$error = false;
	$variation = null;
	if(version_compare( WC_VERSION, '2.7', '<' ))
	{
		try
		{
			$variation = new WC_Product_Variation($variation_id);
		}
		catch(Exception $e){$error = true;}
		if($error) //no longer executed
			try
			{
				$error = false;
				$variation = new WC_Product($variation_id);
				return $variation->get_title();
			}catch(Exception $e){$error = true;}
		
		if($error)
			return false;
	}
	else // > 3.0
	{
		$variation = wc_get_product($variation_id);
		if($variation == null)
			return "";
		if($variation->is_type('simple'))
			return $variation->get_title();
	}
	
	$product_name = $variation->get_title()." - ";	
	if($product_name == " - " || $variation->get_title() == '')
		return false;
	
	$attributes_counter = 0;
	$attributes = "";
	
	foreach($variation->get_variation_attributes( ) as $attribute_name => $value)
	{
		
		if($attributes_counter > 0)
			$attributes .= ", ";
		$meta_key = urldecode( str_replace( 'attribute_', '', $attribute_name ) ); 
		
		$attributes .= " ".wc_attribute_label($meta_key).": ".$value;
		$attributes_counter++;
	}
	if($attributes == "")
		return false;
	$product_name .= $attributes;
	
	return $product_name;
}
	
function wctbp_change_product_name( $title, $post, $field, $post_id ) {

    if($post->post_type == "product_variation" )
	{
		$variation_name = wctbp_get_variation_complete_name($post->ID);
		$title_temp = "#{$post->ID} - ".$variation_name;
		$title = $variation_name != false && !ctype_space($variation_name) && $variation_name != '' ? $title_temp : $title;
	}
	 return $title;
}
add_filter('acf/fields/post_object/result', 'wctbp_change_product_name', 10, 4);
//Avoid custom fields metabox removed by pages
add_filter('acf/settings/remove_wp_meta_box', '__return_false');
?>