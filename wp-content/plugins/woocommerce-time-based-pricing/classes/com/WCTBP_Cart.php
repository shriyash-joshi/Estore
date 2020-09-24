<?php 
class WCTBP_Cart
{
	var $total_saved = 0;
	var $cart_items = null;
	var $free_shipping_items = array();
	public function __construct()
	{
		//Add to cart validation for non authorized users
		add_action('woocommerce_add_to_cart_validation', array(&$this, 'cart_add_to_validation'), 10, 5);
		add_filter('woocommerce_update_cart_validation', array(&$this, 'cart_update_validation'), 11, 4);
		
		//Checkout
		add_action('woocommerce_checkout_process', array( &$this, 'cart_validation_on_checkout' ));
		
		add_action( 'woocommerce_cart_calculate_fees', array(&$this, 'apply_discount_if_any') );
		//add_action('wp_head', array( &$this,'add_meta'));
		//add_action('wp', array( &$this,'add_headers_meta'));
		
		//add_action( 'woocommerce_cart_loaded_from_session', array(&$this, 'cache_cart_content') );
	}
	/* public function add_meta()
	{
		if((function_exists('is_cart') && is_cart()) || (function_exists('is_checkout') && is_checkout()))
		{
			echo '<meta http-equiv="Cache-control" content="no-cache">';
			echo '<meta http-equiv="Expires" content="-1">'; 
		}
	}
	function add_headers_meta()
	{
		if((function_exists('is_cart') && is_cart()) || (function_exists('is_checkout') && is_checkout()))
		{
			header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1.
			header('Pragma: no-cache');
		}
	} */
	//Add to cart                           //true or false
	public function cart_add_to_validation( $original_result, $product_id, $quantity , $variation_id = 0, $variations = null )
	{
		global $wctbp_product_model;
		$product = new WC_Product($product_id);
		if($wctbp_product_model->get_new_price_or_discount_rule(0, $product) === 'hide_price')
		{
			$original_result = false;
			wc_add_notice( __('You are not authorized to buy this product.','woocommerce-time-based-pricing') ,'error');
		}
		
		return $original_result;
	}
	//Update cart
	public function cart_update_validation($original_result, $cart_item_key, $values, $quantity )
	{
		global $woocommerce,$wctbp_product_model;
		
		$items = WC()->cart->cart_contents;
		$items_to_remove = array();
		if(isset($items[$cart_item_key]))
		{
			$product = new WC_Product($items[$cart_item_key]['product_id']);
			//wctbp_var_dump($product->get_title( )." ".$wctbp_product_model->get_new_price_or_discount_rule(0, $product));
			if($wctbp_product_model->get_new_price_or_discount_rule(0, $product) === 'hide_price')
			{
				wc_add_notice( sprintf(__('You are not authorized to buy %s. It has been removed from cart.','woocommerce-time-based-pricing'), "<strong>".$product->get_title( )."</strong>") ,'error');
				$original_result = false;
				$items_to_remove[] = $cart_item_key;
			}
		}
		foreach((array)$items_to_remove as $item_key_to_remove)
			WC()->cart->remove_cart_item( $item_key_to_remove );
		return $original_result;
	}
	//Checkout
	public function cart_validation_on_checkout()
	{
		//setting a wc_add_notice 'error' will stop checkout process
		$error = false;
		global $wctbp_product_model;
		$items_to_remove = array();
		$items = WC()->cart->get_cart();
		foreach($items as $cart_key => $item)
		{
			$product = new WC_Product($items[$cart_key]['product_id']);
			if($wctbp_product_model->get_new_price_or_discount_rule(0, $product) === 'hide_price')
			{
				wc_add_notice( sprintf(__('You are not authorized to buy %s. It will be removed from the cart.','woocommerce-time-based-pricing'), "<strong>".$product->get_title( )."</strong>") ,'error');
				$items_to_remove[] = $cart_key;
			}
		}
		foreach((array)$items_to_remove as $item_key_to_remove)
			WC()->cart->remove_cart_item( $item_key_to_remove );
		 
	}
	function get_cart_item_by_id($product_id, $is_variation)
	{
		global $woocommerce;
		$quantity = 0;
		foreach((array)$woocommerce->cart->cart_contents as $cart_item)
		{
			if((!$is_variation && $cart_item["product_id"] == $product_id) || ($is_variation && $cart_item["variation_id"] == $product_id))
			{
				return $cart_item["quantity"];
			}
		}
		
		return $quantity;
	}
	function apply_discount_if_any() 
	 {
	   global $woocommerce, $wctbp_product_model, $wctbp_option_model;
	   $this->free_shipping_items = $discounts_array = array();
	   $discounts_to_exclude_because_to_use_individually = array();
	   $discount  = null;
	   $this->total_saved = $discount_counter = $sum_of_all_discouns = 0;
	   $skip = false;
	   
	   //How many rules have to be applied and for some of them, how many items have to be considered.
	   foreach($woocommerce->cart->cart_contents as $cart_item)
	   {
			if(version_compare( WC_VERSION, '2.7', '<' ))
				$discount_rule =  $wctbp_product_model->get_new_price_or_discount_rule($cart_item["data"]->price, $cart_item["data"], $cart_item["data"]->variation_id != "" && $cart_item["data"]->variation_id != 0, 'discount');
			else
				$discount_rule =  $wctbp_product_model->get_new_price_or_discount_rule($cart_item["data"]->get_price('numeric'), $cart_item["data"], $cart_item["data"]->get_type() == 'variation', 'discount');
			
			if(is_array($discount_rule))
			{
				//$discount_rule['cart_item'] = $cart_item;
				//in case the discout has to be applied to each item, the plugin keeps track of all items in the cart and its quantities
				$discount_rule['times_to_apply_discount'] = $discount_rule['apply_discount_value_per_each_matching_item'] ? /* $cart_item["quantity"] */ 1: 1;
				if(isset($discounts_array[$discount_rule['unique_id']]) && $discount_rule['apply_discount_value_per_each_matching_item'])
				{
					$discount_rule['times_to_apply_discount'] += $discounts_array[$discount_rule['unique_id']]['times_to_apply_discount'];
				}
				
				//used for free x items
				$cart_items = wctbp_get_value_if_set($discounts_array, array($discount_rule['unique_id'],'cart_item' ), array());
				
				$discounts_array[$discount_rule['unique_id']] = $discount_rule;
				if($discount_rule['individual_usage_only'])
				{
					$discounts_to_exclude_because_to_use_individually[] = $discount_rule['unique_id'];
				}
				//used for free x items
				$cart_items[] = $cart_item;
				$discounts_array[$discount_rule['unique_id']]['cart_item'] = $cart_items;
				
				$price_rule_metadata = $wctbp_product_model->get_last_pricing_rule_applied_info();
				/* $discounts_array['free_shipping'] = isset($price_rule_metadata['free_shipping']) ? $price_rule_metadata['free_shipping'] : false;
				$discounts_array['product_id'] = $cart_item["variation_id"] != 0 ? $cart_item["variation_id"] : $cart_item["product_id"]; */
				if(isset($price_rule_metadata['free_shipping']) && $price_rule_metadata['free_shipping'])
					$this->free_shipping_items[$cart_item["variation_id"] != 0 ? $cart_item["variation_id"] : $cart_item["product_id"]] = true;
			
			}
			
			if(count($discounts_array) > 1 && count($discounts_to_exclude_because_to_use_individually) > 0)
				foreach($discounts_to_exclude_because_to_use_individually as $rule_unique_id)
				{
					unset($discounts_array[$rule_unique_id]);
				}
	   }
	   
	   //Fee computation
	   foreach((array)$discounts_array as $discount_rule)
	   {
		  $discount_counter++;
		  $additional_text = "";
		  $discount_to_subtract = 0;
		   if($discount_rule['type'] == 'cart_fixed' ) //subtract
		   {
				$discount = $discount_rule['value']*$discount_rule['times_to_apply_discount'];//isset($discount) ? $discount+$discount_rule['value']: $discount_rule['value'];
				$additional_text = wctbp_get_value_if_set($discount_rule, 'cart_label', __('Discount','woocommerce-time-based-pricing'));
		   }
		    if($discount_rule['type'] == 'cart_fixed_add_fee' )
		   {
				$discount = $discount_rule['value']*$discount_rule['times_to_apply_discount'];
				$additional_text = wctbp_get_value_if_set($discount_rule, 'cart_label', __('Fee','woocommerce-time-based-pricing'));
		   }
		   elseif($discount_rule['type'] == 'cart_percentage')
		   {
			   $discount = $woocommerce->cart->subtotal_ex_tax - $sum_of_all_discouns > 0 ? ($woocommerce->cart->subtotal_ex_tax - $sum_of_all_discouns)*($discount_rule['value']/100) : $woocommerce->cart->subtotal_ex_tax*($discount_rule['value']/100); 
			   $discount = $discount > 0 ? round($discount, wc_get_price_decimals()) : $discount; //ceil
			   if(isset($discount_rule['max_discount_value_applicable']) && $discount_rule['max_discount_value_applicable'] != 0)
				   $discount = $discount > $discount_rule['max_discount_value_applicable'] ? round($discount_rule['max_discount_value_applicable'], wc_get_price_decimals())  : $discount;
			   $additional_text = wctbp_get_value_if_set($discount_rule, 'cart_label', __('Discount','woocommerce-time-based-pricing'))." ({$discount_rule['value']}%)";
		   }
		   elseif($discount_rule['type'] == 'cart_percentage_add_fee')
		   {
			   $discount = $woocommerce->cart->subtotal_ex_tax*($discount_rule['value']/100); 
			   $discount = $discount > 0 ? round($discount, wc_get_price_decimals()) : $discount; //ceil
			   $additional_text = wctbp_get_value_if_set($discount_rule, 'cart_label', __('Fee','woocommerce-time-based-pricing'))." ({$discount_rule['value']}%)";
		   }
		   elseif($discount_rule['type'] == 'cart_free_item')
		   {
			   
			   // $temp_product = new WC_Product($cart_item["data"]->product_id );
			    //$cart_item = $discount_rule['cart_item'];
				//wctbp_var_dump(count($discount_rule['cart_item']));
				foreach((array)$discount_rule['cart_item'] as $cart_item)
				{
					$number_of_items_to_give_for_free = floor($cart_item['quantity']/$discount_rule['every_x_items_vaue']);
					if($number_of_items_to_give_for_free == 0)
						continue;
					
					//wctbp_var_dump($cart_item["product_id"]);
					$name = $cart_item["data"]->post->post_title;
					if( $cart_item["data"]->variation_id != "" && $cart_item["data"]->variation_id != 0)
					{
						//$temp_product = new WC_Product_Variation($cart_item["data"]->variation_id );
						$name = $wctbp_product_model->get_variation_complete_name( $cart_item["data"]->variation_id , $discount_rule['cart_item']);
					}
					//NOTE: quantity, in case of "every_x_items" means the range lenght every which a free item is given. For example: quantity:2 means every 2 items
					//$discount_rule['value']: is how many items have to be given for free
					$fee_quantity_label = $discount_rule['give_away_strategy'] != 'every_x_items' ? $discount_rule['value'] : $number_of_items_to_give_for_free;
					$discount_rule['value'] = $discount_rule['give_away_strategy'] != 'every_x_items' ? $discount_rule['value'] : $number_of_items_to_give_for_free;
					
					$additional_text = wctbp_get_value_if_set($discount_rule, 'cart_label', __('Discount','woocommerce-time-based-pricing'))." ({$fee_quantity_label}X - ".$name.")";
					$discount = $discount_rule['value'] * (($cart_item['line_subtotal'] + $cart_item['line_subtotal_tax'])/$cart_item['quantity']);
				
					//special case
					$sum_of_all_discouns += $discount;
					$woocommerce->cart->add_fee(/* ($discount_counter++).". ". */$additional_text, $discount * -1 ,  $apply_tax ); 
					$this->total_saved +=  $discount * -1  < 0 ? $this->get_fee_value($discount, true)  : 0;
					
				}
				//special case 
				continue;
		   } 
		   if($discount_rule['type'] != 'cart_fixed_add_fee') //exclude the 'cart_fixed_add_fee' values
			$sum_of_all_discouns += $discount; 
		   $discount_to_subtract = $discount_rule['type'] != 'cart_fixed_add_fee' && $discount_rule['type'] != 'cart_percentage_add_fee' ? $discount * -1 : $discount; 
		   $apply_tax = $wctbp_option_model->get_option('wctpb_apply_tax_to_cart_discount','no') == 'yes' ? true : false;
		   
		  $this->total_saved +=  $discount_to_subtract < 0 ? $this->get_fee_value($discount, true) : 0;
		   $woocommerce->cart->add_fee(/* $discount_counter.". ". */$additional_text, $discount_to_subtract,  $apply_tax ); 
	   }
	   
	  /*  if(isset($discount))
	   {
		   $discount = $discount > $woocommerce->cart->subtotal_ex_tax ? $woocommerce->cart->subtotal_ex_tax : $discount;
		   $discount *= -1; 
		   $apply_tax = $wctbp_option_model->get_option('wctpb_apply_tax_to_cart_discount','no') == 'yes' ? true : false;
		   $woocommerce->cart->add_fee(__( 'Discount', 'woocommerce-time-based-pricing' ), $discount, $apply_tax, '' ); 
	   } */

    }
	public function get_fee_value($value, $is_taxable = false, $tax_class = '')
	{
		//wctbp_var_dump($is_taxable);
		if($is_taxable)
		{
			 $tax_rates = WC_Tax::get_rates( $tax_class );
             $fee_taxes = WC_Tax::calc_tax( $value, $tax_rates, false );
			 $value += ! empty( $fee_taxes ) ? array_sum( $fee_taxes ) : 0;
		}
		return $value;
	}
	public function cache_cart_content($cart)
	{
		$this->cart_items = $cart->cart_contents;
	}
	public function get_cart_quantity_by_product($product)
	{
		global $woocommerce;
		//if(!isset($this->cart_items))
		{
			$this->cart_items = isset($woocommerce->cart->cart_contents) ? $woocommerce->cart->cart_contents : array();
			//$this->cart_items = WC()->cart->get_cart_from_session();
			//wctbp_var_dump($woocommerce->cart->cart_contents);
		}
		

		foreach($this->cart_items as $cart_key => $item)
		{
			if(($item['variation_id'] != 0 && $product->get_id() == $item['variation_id']) || 
				$item['product_id'] == $product->get_id())
				{
					return $item['quantity'] ; 
				}
		}
		return 0;
	}
	function get_total_saved()
	{
		return $this->total_saved;
	}
	function get_free_shipping_items()
	{
		return $this->free_shipping_items;
	}
}
?>