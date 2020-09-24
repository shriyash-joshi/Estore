<?php 
class WCTBP_PriceChanger
{
	var $filter_woocommerce_shortcode_products_query_cache = null;
	var $total_saved = 0;
	var $total_saved_already_computed_items = array();
	var $free_shipping_items = array();
	public function __construct()
	{
		//if(!is_admin())
		{
			add_action( 'wp_loaded', array(&$this, 'init') );			
			add_filter('woocommerce_get_price_html', array(&$this, 'modify_html_price'), 10, 2 );
			add_filter('woocommerce_cart_item_price', array(&$this, 'modify_cart_html_row_price'), 10, 3 ); 
			//add_filter('woocommerce_get_variation_price_html', array(&$this, 'modify_variation_sub_title_product_single_text'), 10, 2 ); 
			//add_filter('woocommerce_show_variation_price', array(&$this, 'modify_variation_html_price'), 10, 2 ); 
			
			
			//Cart
			//add_action('woocommerce_add_to_cart_validation', array(&$this, 'cart_add_to_validation'), 10, 5);
			//add_action('woocommerce_update_cart_validation', array(&$this, 'cart_update_validation'), 10, 4);
			//add_filter('woocommerce_cart_item_price',array(&$this, 'modify_product_cart_price'), 10, 3 ); 
			
			//Sale badge
			add_filter( 'woocommerce_product_is_on_sale', array(&$this,'show_sale_badge'), 10, 2 ); 
			add_filter( 'woocommerce_sale_flash', array(&$this,'filter_woocommerce_sale_flash'), 10, 3 ); 
		}
		//Ajax
		add_action('wp_ajax_nopriv_wctbp_update_price', array(&$this, 'ajax_update_price'));
		add_action('wp_ajax_wctbp_update_price', array(&$this, 'ajax_update_price'));
		
		//seach (?) and sale_products query 
		add_filter( 'woocommerce_shortcode_products_query', array(&$this,'filter_woocommerce_shortcode_products_query'), 99, 3 );
		
		//Free shippung
		//add_filter( 'woocommerce_shipping_free_shipping_is_available', array( $this, 'set_free_shipping_for_elegible_item' ), 20 );
		add_filter( 'woocommerce_product_needs_shipping', array( $this, 'set_free_shipping_for_elegible_product' ), 10, 2 );
		//add_action('woocommerce_update_cart_validation', array(&$this, 'cart_update_validation'), 10, 4);
	}
	public function init()
	{
		if(version_compare( WC_VERSION, '2.7', '<' ))
		{
			add_filter('woocommerce_get_price', array(&$this, 'modify_product_price'), 12, 2 ); 
			add_filter('woocommerce_get_regular_price', array(&$this, 'modify_product_price'), 12, 2 ); 
		}
		else 
		{
			add_filter('woocommerce_product_get_price', array(&$this, 'modify_product_price'), 12, 2 ); 
			add_filter('woocommerce_product_get_regular_price', array(&$this, 'modify_product_price'), 12, 2 ); 
			add_filter('woocommerce_product_variation_get_price', array(&$this, 'modify_product_price'), 12, 2 ); 
			add_filter('woocommerce_product_variation_get_regular_price', array(&$this, 'modify_product_price'), 12, 2 ); 
		}
	}
	//Free shipping
	public function set_free_shipping_for_elegible_product( $needs_shipping, $product )
	{
		 global $woocommerce, $wcps_product_model, $wctbp_cart_addon;
		//wctbp_var_dump( $is_available );
		
		if(!isset($woocommerce) || $woocommerce->cart == null)
			return $needs_shipping;
		
		$cart_items = $woocommerce->cart->get_cart_contents(); //ok
		//$cart_items = $woocommerce->cart->get_cart();
		//$cart_items = WC()->cart->get_cart();
		
		$cart_discount_items = $wctbp_cart_addon->get_free_shipping_items();
		$price_change_items = $this->get_free_shipping_items();
		
		foreach ( $cart_items as $key => $item ) //why for each one? I do not remember...
		{
			if(isset($cart_discount_items[$product->get_id()]) || isset($price_change_items[$product->get_id()]))
			{
				$needs_shipping = false;
			}
			
		}
		  return $needs_shipping;
	}
	public function set_free_shipping_for_elegible_item( $is_available ) 
	{
		global $woocommerce, $wcps_product_model, $wctbp_cart_addon;
		//wctbp_var_dump( $is_available );
		
		//$cart_items = $woocommerce->cart->get_cart();
		$cart_items = $woocommerce->cart->get_cart_contents();
		
		$cart_discount_items = $wctbp_cart_addon->get_free_shipping_items();
		$price_change_items = $this->get_free_shipping_items();
		
		foreach ( $cart_items as $key => $item ) 
		{
			$item_id = isset($item['variation_id']) && $item['variation_id'] != 0 ? $item['variation_id'] : $item['product_id'];
			if(isset($cart_discount_items[$item_id]) || isset($price_change_items[$item_id]))
			{
				 $item['data']->set_virtual(true);
			}
			
		}
		
		return $is_available;
	}
	public function cart_update_validation($original_result, $cart_item_key, $values, $quantity )
	{
		global $woocommerce, $wcps_product_model, $wctbp_cart_addon;
		
		$cart_discount_items = $wctbp_cart_addon->get_free_shipping_items();
		$price_change_items = $this->get_free_shipping_items();
		$items = WC()->cart->cart_contents;
		//wctbp_var_dump($cart_item_key);
		if(isset($items[$cart_item_key]))
		{
			$item_id = $items[$cart_item_key]['variation_id'] != 0 ? $items[$cart_item_key]['variation_id'] : $items[$cart_item_key]['product_id'];
			if(isset($cart_discount_items[$item_id]) || isset($price_change_items[$item_id]))
			{
				//wctbp_var_dump("ok");
				$items[$cart_item_key]['data']->set_virtual(true);
			}
		}
		//wctbp_var_dump($original_result);
		return $original_result;
	}
	//end free shipping 
	/* function modify_variation_sub_title_product_single_text($html_text, $product, $variation)
	{
		wctbp_var_dump($product);
		return $this->modify_variation_html_price($html_text, $product);
	} */
	private function getTextBetweenTags($string, $tagname) {
		$pattern = "/<$tagname ?.*>(.*)<\/$tagname>/";
		preg_match($pattern, $string, $matches);
		return isset($matches[1]) ? $matches[1] : $string;
	}
	function filter_woocommerce_shortcode_products_query( $query_args, $atts, $loop_name = '' ) 
	{ 
		global $wctbp_product_model, $wctbp_option_model;
		
		if( $loop_name != 'sale_products' || $wctbp_option_model->get_option('wctpb_display_sale_badge', 'yes') != 'yes' || @is_shop() || @is_product())
			return $query_args;
		
		
		$all_products = $wctbp_product_model->get_products_with_pricing_rules_applied(null);
		//wctbp_var_dump($all_products);
		$var_counter = 0;
		if(!isset($query_args["posts_per_page"]))
			$query_args["posts_per_page"] = 12;
		if(!isset($this->filter_woocommerce_shortcode_products_query_cache))
		{
			$this->filter_woocommerce_shortcode_products_query_cache = array();
			if(isset($all_products) && is_array($all_products) && !empty($all_products))
			{
				foreach($all_products as $product)
				{
					$tmp_product = wc_get_product($product->id);
					$is_on_sale = false;
					if($this->show_sale_badge(false, $tmp_product))
					{
						$query_args["post__in"][] = $product->id;
						$this->filter_woocommerce_shortcode_products_query_cache[] = $product->id;
						
					}
					/* else if( $tmp_product->is_on_sale( )) //No need, in the  $query_args there are already sale products
						$is_on_sale = true; */
					
					if($is_on_sale)
						if($var_counter++ == $query_args["posts_per_page"])
								break;
				}
			}
		}
		else 
			foreach((array)$this->filter_woocommerce_shortcode_products_query_cache as $cached_id)
				$query_args["post__in"][] = $cached_id;
				
		return $query_args; 
	}
	function ajax_update_price()
	{
		global $wctbp_product_model, $wctbp_option_model,  $wctbp_text_model;
		$quantity = isset($_POST['quantity']) ? $_POST['quantity'] : 0;
		$product_id = isset($_POST['product_id']) ? $_POST['product_id'] : null;
		$variation_id = isset($_POST['variation_id']) && $_POST['variation_id'] != 'undefined' ? $_POST['variation_id'] : null;
		$product = $variation_id != null ? new WC_Product_Variation($variation_id)  :  new WC_Product($product_id);
		
		$new_price = $wctbp_product_model->get_new_price_or_discount_rule($product->get_price('numeric'), $product, isset($variation_id), 'price', $quantity, false);
		//wctbp_var_dump($new_price );
		if(!isset($new_price) || $new_price == false)
		{
			//echo "no";
			$new_price = $product->get_price('numeric');
			$html = wc_price(WCTBP_Tax::get_product_price_with_tax_according_settings($product));
		}
		else
		{
			$html = $this->modify_html_price($product->get_price('numeric'), $product, $quantity, true);
		}
		
		if($quantity > 1 && $wctbp_option_model->get_option('wctbp_display_total_price_next_single_price', 'yes') == 'yes')
		{
			$text = $wctbp_text_model->get_texts();
			$new_price = $product->get_tax_status() == 'taxable' ? WCTBP_Tax::get_product_price_including_tax($product, 1, $new_price) : $new_price;
			$new_price = $new_price;
			$html .= $text['product_page_before_total_price_text'].wc_price(($new_price*$quantity));
		}
		echo $html;
			
		wp_die();
	}
	function filter_woocommerce_sale_flash($html_text, $post, $product)
	{
		global $wctbp_text_model;
		$texts = $wctbp_text_model->get_texts();
		if($texts['sale_badge_text'] != "")
			$html_text = '<span class="onsale">' . $texts['sale_badge_text'] . '</span>';
		return $html_text; 
	}
	function show_sale_badge( $is_on_sale, $product ) 
	{ 
		/* if(is_admin())
			return $is_on_sale; */
		
		global  $wctbp_product_model, $wctbp_option_model;	
		$display_badge = $wctbp_option_model->get_option('wctpb_display_sale_badge', 'yes');
		
		if($display_badge === 'no')
			return $is_on_sale;
		
		if(is_a($product, 'WC_Product_Variable'))
		{
			$old_numeric_min = $product->get_variation_regular_price();
			$old_numeric_max = $product->get_variation_regular_price('max');
			$result = $wctbp_product_model->get_min_max_price_variations($product->get_id());
			
			/* wctbp_var_dump($old_numeric_min." ".$old_numeric_max);
			wctbp_var_dump($result); */
			
			//wp_die();
			if( ($result['min'] != "" && $result['min'] < $old_numeric_min)  || ($result['max'] != "" && $result['max'] < $old_numeric_max))
				return true;
		}
		else 
		{
			$price = $this->modify_product_price($product->get_price('numeric'), $product);
			if($price < $product->get_price('numeric')) 
				return true;
		}
		
		return $is_on_sale;
	}
  
	function modify_cart_html_row_price($price, $cart_item = null, $cart_item_key = null)
	{
		//wctbp_var_dump($cart_item["data"]);
		if($cart_item == null || $cart_item_key == null)
		    return $price;
		
		return $this->modify_html_price($price, $cart_item['data'], 0, false, true);
	}
	function get_total_saved()
	{
		return $this->total_saved;
	}
	function get_free_shipping_items()
	{
		return $this->free_shipping_items;
	}
	function modify_html_price($price, $product, $quantity = 0, $is_ajax = false, $is_cart = false) 
	{
		if(is_admin() && !$is_ajax)
			return $price; 
		
		global $wctbp_product_model,$wctbp_option_model, $wctbp_text_model;
		$additional_texts = $wctbp_text_model->get_texts();
		$display_tax = !$is_cart ? get_option('woocommerce_tax_display_shop') : get_option('woocommerce_tax_display_cart');
		$page_type = $is_cart ? 'cart' : 'shop';
		$prices_entered_include_tax = get_option('woocommerce_prices_include_tax');
		$price_display_suffix = get_option('woocommerce_price_display_suffix');
		$price_display_suffix = $display_tax == 'incl' && !$is_cart ? $price_display_suffix : "";
		$product_page_discount_percentage_text = $additional_texts['product_page_discount_percentage_text'];
		$shop_page_discount_percentage_text = $additional_texts['shop_page_discount_percentage_text'];
		$shop_page_discount_percentage_variable_product_text = $additional_texts['shop_page_discount_percentage_variable_product_text'];
		$show_percentage_discount_on_shop_page = $wctbp_option_model->get_option('wctbp_shop_page_display_discount_percentage_next_single_price', 'yes') == 'yes' ? true : false;
		$show_percentage_discount_on_product_page = $wctbp_option_model->get_option('wctbp_display_discount_percentage_next_single_price', 'yes') == 'yes' ? true : false;
		$is_variable_product = false;
		
		if(!$is_cart && $is_ajax) //product_page
		{
			$page_type = 'product';
			$price_display_suffix .= $additional_texts['product_page_after_price_text'];
		}
		
		//$was_it_modified = false;
		$display_old_price = $wctbp_option_model->get_option('wctpb_display_old_price','yes');
		$display_badge = $wctbp_option_model->get_option('wctpb_display_sale_badge', 'yes');
		$display_items_price_without_tax = $wctbp_option_model->get_option('wctpb_display_items_price_without_tax','no');
		$old_price = $price;
		$original_html_price = $price;
		$old_variable_product_show_price_method = $wctbp_option_model->get_option('wctpb_use_alternative_variable_product_reange_price_display', 'no');
		$total_price = null;
        $total_old_price = null;
		$is_sale_price = false;
		$discount_applied = 0;
		$discount_applied_string = "";
		
		//$price = HTML String ---> €1,00–€3,00  get_woocommerce_currency_symbol
		if(is_a($product, 'WC_Product_Variable'))
		{
			$is_variable_product = true;
			
			//$temp = new WC_Product_Variable($product->id);
			//$min = $wctbp_product_model->get_presale_price_if_any($product->id, $temp->get_variation_price()); // $temp->get_variation_price();
			//$max = $wctbp_product_model->get_presale_price_if_any($product->id, $temp->get_variation_price('max'));  //$temp->get_variation_price('max');
			//$price = wc_price($min). " - ".wc_price($max);
			$result = $wctbp_product_model->get_min_max_price_variations($product->get_id(), $quantity);
			
			if($result['hide_price'])
				return "";
			$old_numeric_min = $product->get_variation_price(); //regular price tiene anche conto dello sconto
			$old_numeric_max = $product->get_variation_price('max');
				
			
			if( $result['min'] < $old_numeric_min  || $result['max'] < $old_numeric_max)
			{
				$is_sale_price = true;
				$discount_percentage_min = $result['min'] < $old_numeric_min ? round ((1 - $result['min'] / $old_numeric_min) * 100) : 0;
				$discount_percentage_max = $result['max'] < $old_numeric_max ? round ((1 - $result['max'] / $old_numeric_max) * 100) : 0;
				
				//Discount display for variable product
				if($discount_percentage_min >= $discount_percentage_max && $discount_percentage_min != 0 && 
					isset($result['min_rule_info']) && isset($result['min_rule_info']['price_strategy']))
				{
					if(/* $result['min_rule_info']['price_strategy'] == 'fixed' || */ $result['min_rule_info']['price_strategy'] == 'value_off')
					{
						$discount_applied =  $result['min_rule_info']['price_value'] ;
						$discount_applied_string =  wc_price($discount_applied) ;
					}
					elseif($result['min_rule_info']['price_strategy'] == 'percentage' )
					{
						$discount_applied =  $discount_percentage_min ;
						$discount_applied_string =  $discount_applied."%" ;
					}
				}
				else if($discount_percentage_max >= $discount_percentage_min && $discount_percentage_max != 0 && 
						isset($result['min_rule_info']) && isset($result['min_rule_info']['price_strategy']))
				{
					if(/* $result['max_rule_info']['price_strategy'] == 'fixed' || */ $result['max_rule_info']['price_strategy'] == 'value_off')
					{
						$discount_applied =  $result['max_rule_info']['price_value'] ;
						$discount_percentage_max =  wc_price($discount_applied) ;
					}
					elseif($result['min_rule_info']['price_strategy'] == 'percentage' )
					{
						$discount_applied =  $discount_percentage_max ;
						$discount_applied_string =  $discount_applied."%" ;
					}
				}
			}
			
			if($display_items_price_without_tax == 'no')
			{
				/* $tax = $product->get_price_excluding_tax() != 0 ? $product->get_price_including_tax()/$product->get_price_excluding_tax() : null;
				$old_numeric_min = $product->tax_status == 'taxable' ? $old_numeric_min*$tax: $old_numeric_min;
				$old_numeric_max = $product->tax_status == 'taxable' ? $old_numeric_max*$tax: $old_numeric_max;  
				$result['min'] = $product->tax_status == 'taxable' ? $result['min']*$tax : $result['min'] ;
				$result['max'] = $product->tax_status == 'taxable' ? $result['max']*$tax : $result['max'] ; */
				
				$tax = false;
				$old_numeric_min = $display_tax == 'incl' && $product->get_tax_status() == 'taxable' ? WCTBP_Tax::get_product_price_including_tax($product, 1, $old_numeric_min) : $old_numeric_min;
				$old_numeric_max = $display_tax == 'incl' && $product->get_tax_status() == 'taxable' ? WCTBP_Tax::get_product_price_including_tax($product, 1, $old_numeric_max) : $old_numeric_max ;
				$result['min'] = $display_tax == 'incl' && $product->get_tax_status() == 'taxable' ? WCTBP_Tax::get_product_price_including_tax($product, 1, $result['min']) : $result['min'] ;
				$result['max'] = $display_tax == 'incl' && $product->get_tax_status() == 'taxable' ? WCTBP_Tax::get_product_price_including_tax($product, 1, $result['max']) : $result['max'] ; 
			}
			
			
			$old_price =  wc_price($old_numeric_min). " - ".wc_price($old_numeric_max);
			//$was_it_modified = true;
			$price = wc_price($result['min']). " - ".wc_price($result['max']);
			$old_price = $old_numeric_min != $old_numeric_max ? wc_price($old_numeric_min). " - ".wc_price($old_numeric_max) : wc_price($old_numeric_max);
			//price html manipulation
			$old_price = !isset($old_variable_product_show_price_method) || $old_variable_product_show_price_method !== 'yes' ? $old_price : wc_price($old_numeric_min);
			//$old_price = $result['min'] == $old_numeric_min ? "" : $old_price; //Otherwise in case all variation have the same price, old price was never displayed
			$price = !isset($old_variable_product_show_price_method) || $old_variable_product_show_price_method !== 'yes' ? $price : '<span class="wctbp_variable_price_from from">'.__("From: ","woocommerce-time-based-pricing").' </span>'.wc_price($result['min']);
			
			if($old_numeric_min == $result['min'] && $old_numeric_max == $result['max'])
				return $original_html_price;
			//In case it was free and still is free no html price manipulation
			if( $old_numeric_min == 0 && $old_numeric_max ==  0 && $result['min'] ==  0 && $result['min'] == 0)
				return ""; //"Free!" text
			if( wc_price($result['min']) == wc_price($result['max']))
					$price = wc_price($result['min']);
				
			//wctbp_var_dump($old_price );
		} 
		else if($display_old_price == 'yes' || $display_badge == 'yes' || $show_percentage_discount_on_shop_page || $is_ajax) //Simple and variation
		{
			$tax = WCTBP_Tax::get_product_price_excluding_tax($product) != 0 ? WCTBP_Tax::get_product_price_including_tax($product)/WCTBP_Tax::get_product_price_excluding_tax($product) : null;
			
			if(version_compare( WC_VERSION, '2.7', '<' ))
				$original_price = isset($product->variation_id) && $product->variation_id != "" && $product->variation_id != 0 ? $wctbp_product_model->get_product_price($product->variation_id) : $wctbp_product_model->get_product_price($product->id);
			else 
				$original_price = $wctbp_product_model->get_product_price($product->get_id());
			
			/* if(is_a($product, 'WC_Product_Booking'))
				$original_price = $product->get_price('wcps'); */
			
			if($prices_entered_include_tax == 'yes' /* && isset($tax) && $tax != 0 */)
				$original_price = WCTBP_Tax::get_product_price_excluding_tax($product) /* $original_price / $tax */;
			
			if(version_compare( WC_VERSION, '2.7', '<' ))
				$temp_price =  isset($original_price) ? $wctbp_product_model->get_new_price_or_discount_rule($original_price, $product, isset($product->variation_id) && $product->variation_id != "" && $product->variation_id != 0, 'price', $quantity, false) : 0;
			else
				$temp_price =  isset($original_price) ? $wctbp_product_model->get_new_price_or_discount_rule($original_price, $product,  $product->get_type() == 'variation' , 'price', $quantity, false) : 0;
			
			
			if(isset($temp_price) && $temp_price == 'hide_price')
				return "";
			
			if( is_numeric($temp_price)/* $temp_price != 0 */) //if is numeric is a new price
			{
				//NO: already performed on line 405
				/* if(!is_a($product, 'WC_Product_Variation') && $prices_entered_include_tax == 'yes' && isset($tax) && $tax != 0)
					$temp_price = $temp_price / $tax; */
				
				if($temp_price < $original_price) 
				{
					$is_sale_price = true;
					$tmp_discount_applied = round ((1 - $temp_price/$original_price) * 100);
					$last_applied_pricing_rule_info = $wctbp_product_model->get_last_pricing_rule_applied_info() ;
					
					if($last_applied_pricing_rule_info['price_strategy'] == 'value_off')
					{
						$discount_applied =  $last_applied_pricing_rule_info['price_value'] ;
						$discount_applied_string =  wc_price($discount_applied) ;
					}
					else if($last_applied_pricing_rule_info['price_strategy'] == 'percentage')
					{
						$discount_applied =  $tmp_discount_applied ;
						$discount_applied_string =  $tmp_discount_applied."%" ;
					}
				}
				
				//In case it was free and still is free no html price manipulation
				if( $original_price == 0 && $temp_price == 0)
					//return $is_cart ? $old_price : $old_price.$additional_texts['product_page_after_price_text'];
					return  $is_ajax ? wc_price($old_price): $old_price;
			
				$old_price = $original_price;
				
				//$was_it_modified = true;
				if($display_items_price_without_tax == 'no')
				{
					$total_price = $display_tax == 'incl' && isset($tax) && $tax != 0 ? ($temp_price*$tax*$quantity): ($temp_price*$quantity);
					$total_old_price = $display_tax == 'incl' && isset($tax) && $tax != 0 ? ($old_price*$tax*$quantity): ($old_price*$quantity);
					$price = $display_tax == 'incl' && isset($tax) && $tax != 0 ? wc_price($temp_price*$tax): wc_price($temp_price);
					//$price = $display_tax == 'incl' && isset($tax) && $tax != 0 ? WCTBP_Tax::get_product_price_including_tax($product, 1, $temp_price) : wc_price($temp_price);
					$old_price = $display_tax == 'incl' && isset($tax) && $tax != 0 ? wc_price($old_price*$tax): wc_price($old_price); 
					
				   /*  wctbp_var_dump($tax);
					wctbp_var_dump($original_price);
					wctbp_var_dump($temp_price);
					wctbp_var_dump(WCTBP_Tax::get_product_price_including_tax($product, 1, $temp_price));
					wctbp_var_dump($price);  */
				}
				else
				{
					$total_price =  ($temp_price*$quantity);
					$total_old_price = isset($tax) && $tax != 0 ? ($old_price*$tax*$quantity): ($old_price*$quantity);
					$price =  wc_price($temp_price);
					$old_price =  wc_price($old_price);
				}
			}
			else //In case there is not price change for Variation/Simple product
			{
				//return $is_cart ? $original_html_price : $original_html_price.$additional_texts['product_page_after_price_text'];
				return $is_ajax ? wc_price($original_html_price): $original_html_price;
				
				/* $tax = $product->get_price_excluding_tax() != 0 ? $product->get_price_including_tax()/$product->get_price_excluding_tax() : null;
				$total_price = $display_tax == 'incl' && isset($tax) && $tax != 0 ? ($original_price*$tax*$quantity): ($original_price*$quantity);
				$total_old_price = $display_tax == 'incl' && isset($tax) && $tax != 0 ? ($original_price*$tax*$quantity): ($original_price*$quantity);
				$old_price = $display_tax == 'incl' && isset($tax) && $tax != 0 ? wc_price($original_price*$tax): wc_price($original_price); 
				$price =  $display_tax == 'incl' && isset($tax) && $tax != 0 ? wc_price($original_price*$tax): wc_price($original_price);  */
			}
		}
		
		
		//Discount display
		if($is_sale_price)
		{
			if($page_type == 'shop' && $discount_applied != 0 && $show_percentage_discount_on_shop_page)
			{
				if(!$is_variable_product)
					$price_display_suffix.= $product_page_discount_percentage_text != "" ? " ".sprintf($product_page_discount_percentage_text,$discount_applied_string) : "";
				else
					$price_display_suffix.= $shop_page_discount_percentage_variable_product_text != "" ? " ".sprintf($shop_page_discount_percentage_variable_product_text,$discount_applied_string) : "";
			}
			else if($page_type == 'product' && $discount_applied != 0 && $show_percentage_discount_on_product_page && $product_page_discount_percentage_text != "")
			{
				$price_display_suffix.= " ".sprintf($product_page_discount_percentage_text,$discount_applied_string);
			}
		}
		
		$temp_html_old_price = $this->getTextBetweenTags($old_price, 'span') != "" ? $this->getTextBetweenTags($old_price, 'span') : $old_price;
		$temp_html_price = $this->getTextBetweenTags($price, 'span') != "" ? $this->getTextBetweenTags($price, 'span') : $price;
		if(($display_old_price == 'yes'/*  || ($display_badge == 'yes' && $is_sale_price ) */) && ($temp_html_price !=  $temp_html_old_price ))
		{
			//Price is changed and old price is displayed
			$price = $this->format_price($price, $total_price, $old_price, $total_old_price); //MAIN
			
			return $price.$price_display_suffix ;
		}
		
		if(isset($temp_price) && $temp_price == 'hide_price')
			$price = "";
		 
		 
		  //No price change
		 if($price !="" && strpos($price, get_woocommerce_currency_symbol()) === false) 
		 {
			return $this->format_price($price, $total_price).$price_display_suffix;//wc_price($price);
		 }
		 else //Old price is not dispayed and price has changed
		 {
			if ($price == '') 
					return '';
			else if (is_numeric($price)) //Quantity change on product cart (JS) without applying any rule, so price is numeric
			{
				
				return $this->format_price(($price), ($price * $quantity)).$price_display_suffix;
			}
			else 
			{	
				//NO!!! Variable product with old price display method
				/* if(isset($old_variable_product_show_price_method) && $old_variable_product_show_price_method == 'yes')
					return $this->format_price($price, $total_price, $old_price); */
				
				return $this->format_price($price, $total_price).$price_display_suffix; //MAIN
			}
				
		 }
		
		//if no price change
		//return $price !="" && strpos($price, get_woocommerce_currency_symbol()) === false ? wc_price($price) : $price/* $was_it_modified ?  $price : wc_price($price) */;
	}
	function modify_product_cart_price($price, $product, $cart_item_key)
	{
		global $wctbp_cart;
		$wctbp_cart = WC()->cart->get_cart();
		return $this->modify_product_price($price, $product);
	}
	function modify_product_price($price, $product) 
	{
		global $wctbp_cart_addon, $wctbp_product_model;
		if(is_admin() /* || did_action('woocommerce_get_price_including_tax') || did_action('woocommerce_get_price_excluding_tax') */)
			return $price;
		
		//WooTheme Product Addon: it already adds the modified price to product meta. If so, price is not recomputed again
		if(method_exists($product , 'get_changes'))
		{
			$changes = $product->get_changes(); //This containes the changes added by Product Addon
			if(isset($changes) && isset($changes['price'])) 
				return $price;
		}
		
		 global $wctbp_product_model;
		/*return $wctbp_product_model->get_presale_price_if_any($product->id, $price); */
		//wctbp_var_dump($product->get_type());
		if(version_compare( WC_VERSION, '2.7', '<' ))
			$new_price = $wctbp_product_model->get_new_price_or_discount_rule($price, $product, isset($product->variation_id) && $product->variation_id != "" && $product->variation_id != 0, 'price', 0, false); //Without vat
		else
			$new_price = $wctbp_product_model->get_new_price_or_discount_rule($price, $product,  $product->get_type() == 'variation', 'price', 0, false); //Without vat
		
		if(isset($new_price) && $new_price === 'hide_price')
				return "";
			
		if(is_numeric($new_price) && !isset($this->total_saved_already_computed_items[$product->get_id()]))
		{
			$cart_quantity = $wctbp_cart_addon->get_cart_quantity_by_product($product);
			$price_rule_metadata = $wctbp_product_model->get_last_pricing_rule_applied_info();
			
			if(isset($price_rule_metadata['free_shipping']) && $price_rule_metadata['free_shipping'])
					$this->free_shipping_items[$product->get_id()] = true;
				
			if($cart_quantity > 0)
			{
				$this->total_saved_already_computed_items[$product->get_id()] = true;
				$difference = ($price - $new_price );
				$this->total_saved += $difference  * $cart_quantity > 0 ? WCTBP_Tax::get_product_price_including_tax($product, 1, $difference)  * $cart_quantity : 0;
			}
			
		}
				
		return is_numeric($new_price) ? $new_price /* round($new_price, wc_get_price_decimals()) */ :  $price; //check 0 new price
	}
	 private function format_price($price, $total_price = null, $old_price = null, $total_old_price = null) {
       
  	    $output = "";
        /*$output.= '<span style="display:inline;color:#333;"> ' . __('Unit Price', 'woocommerce-time-based-pricing') . ':</span> ';
		*/
		
        if ($old_price && ($price != $old_price)) {
            $output.='<span style="color:#c3c3c3; text-decoration: line-through;">' . strip_tags($old_price) . '</span> ';
        }

         $output.= is_numeric($price) ?  wc_price($price) : $price;
        /*if ($total_price) {
            $output.='<br/>';
            $output.='<span style="display:inline;color:#333">' . __('Total', 'woocommerce-time-based-pricing') . ':</span> ';
            if ($total_old_price) {
                $output.='<span style="color:#c3c3c3; text-decoration: line-through;">' . strip_tags(wc_price($total_old_price)) . '</span> ';
            }
            $output.=wc_price($total_price);
        } */

        return $output;
    }
}
?>