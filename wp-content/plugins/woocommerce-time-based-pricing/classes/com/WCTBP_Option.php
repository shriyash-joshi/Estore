<?php class WCTBP_Option
{
	var $rules_cache;
	public function __construct()
	{
		
	}
	public function cl_acf_set_language() 
	{
	  return acf_get_setting('default_language');
	}
	public function get_option($option_name = null, $default_value = null)
	{
		//wctpb_use_alternative_variable_product_reange_price_display
		
		add_filter('acf/settings/current_language',  array(&$this, 'cl_acf_set_language'), 100);
		$return_value = null;
	
		/* wctbp_var_dump(get_field('wctbp_time_periods', 'option'));
		return $return_value; */
		
		if(isset($option_name) && $option_name == 'scheduling_rules')
		{
			$all_data = array();
			if(!isset($this->rules_cache))
			{
				if( have_rows('wctbp_period_rules', 'option'))
					while ( have_rows('wctbp_period_rules', 'option') ) 
					{
						the_row();
						$time_period = array();
						$time_period['dates'] = array();
						$time_period['rule_name_id'] = get_sub_field('wctbp_rule_name_id', 'option'); //Check if value exists: if( $value )
						$time_period['selected_products'] = get_sub_field('wctbp_selected_products', 'option'); 
						$time_period['selected_product_categories'] = get_sub_field('wctbp_selected_product_categories', 'option'); 
						$time_period['selected_strategy'] = get_sub_field('wctbp_selected_strategy', 'option'); //all / except
						$time_period['categories_children'] = get_sub_field('wctbp_categories_children', 'option'); 
						/* $time_period['post_status'] = get_sub_field('wpp_post_status', 'option');  */
								
						//New fields, force default value for backward compatibility		
						
						
						if( have_rows('wctbp_time_period', 'option') )
							while ( have_rows('wctbp_time_period', 'option') ) 
							{
								the_row();
								$temp_date = array(	'quantity_strategy' => get_sub_field('wctbp_quantity_strategy'),
													'stack_rule' => get_sub_field('wctbp_stack_rule'),
													'ignore_rule_if_coupon_is_active' => get_sub_field('wctbp_ignore_rule_if_coupon_is_active'),
													'user_accounts' => get_sub_field('wctbp_user_account'),
													'user_roles' => get_sub_field('wctbp_user_roles'),
													'user_filtering_strategy' => get_sub_field('wctbp_user_roles_filtering_strategy'), //all / except
													'hide_price_for_unauthorized_users' => get_sub_field('wctbp_hide_price_for_unauthorized_users'), //yes / no
													'cumulative_category_quantity' => get_sub_field('wctbp_cumulative_category_quantity'), 
													'day_type' => get_sub_field('wctbp_day_type'),
													'days_of_the_week' => get_sub_field('wctbp_days_of_the_week'),
													'days_of_the_month' => get_sub_field('wctbp_days_of_the_month'),
													'months' => get_sub_field('wctbp_months'),
													'years' => get_sub_field('wctbp_years'),
													'start_hour' => get_sub_field('wctbp_start_hour'),
													'start_minute' => get_sub_field('wctbp_start_minute'),
													'use_end_time' =>  "yes",//get_sub_field('wctbp_use_end_time'), // "yes" or "no"
													'end_hour' => get_sub_field('wctbp_end_hour'),
													'end_minute' => get_sub_field('wctbp_end_minute'),
													'prices' => array()
													  );
								
								//New fields, force default value for backward compatibility
								$temp_date['ignore_rule_if_coupon_is_active'] = $temp_date['ignore_rule_if_coupon_is_active'] ? $temp_date['ignore_rule_if_coupon_is_active']  : false;
								$temp_date['user_accounts'] = $temp_date['user_accounts'] ? $temp_date['user_accounts']  : array();
								$temp_date['stack_rule'] = $temp_date['stack_rule'] ? $temp_date['stack_rule']  : 'no';
								$temp_date['stack_rule'] = is_array($temp_date['stack_rule']) ? 'no' : $temp_date['stack_rule'];
								$temp_date['user_roles'] = $temp_date['user_roles'] ? $temp_date['user_roles']  : array();
								$temp_date['user_filtering_strategy'] = $temp_date['user_filtering_strategy'] ? $temp_date['user_filtering_strategy']  : 'all';
								$temp_date['hide_price_for_unauthorized_users'] = $temp_date['hide_price_for_unauthorized_users'] ? $temp_date['hide_price_for_unauthorized_users']  : 'no';
								$temp_date['quantity_strategy'] = $temp_date['quantity_strategy'] ? $temp_date['quantity_strategy']  : 'cart';							  
								$temp_date['cumulative_category_quantity'] = $temp_date['cumulative_category_quantity'] ? $temp_date['cumulative_category_quantity']  : 'no';	
								
								if( have_rows('wctbp_prices_per_quantity', 'option') )
									while ( have_rows('wctbp_prices_per_quantity', 'option') ) 
									{
										the_row();
										$temp_price_strategy = array('price_strategy' => get_sub_field('wctbp_price_strategy'), // fixed || percentage || value_off || value_add || cart_fixed ||  cart_fixed_add_fee || cart_percentage || cart_percentage_add_fee || cart_free_item
																	 'price_value' => get_sub_field('wctpb_price_value'),
																	 'cart_label' => get_sub_field('wctpb_cart_label'),
																	 'free_shipping' => get_sub_field('wctbp_free_shipping'),
																	 'max_discount_value_applicable' => get_sub_field('wctbp_max_discount_value_applicable'),
																	 'apply_discount_value_per_each_matching_item' => get_sub_field('wctpb_apply_discount_value_per_each_matching_item'),
																	 'min_quantity' => get_sub_field('wctpb_min_quantity'),
																	 'max_quantity' => get_sub_field('wctpb_max_quantity'),
																	 'unique_id' => get_sub_field('wctbp_unique_id'),
																	 'cart_discount_individual_use_only' => get_sub_field('wctbp_cart_discount_individual_use_only'),
																	 'give_away_strategy' => get_sub_field('wctbp_give_away_strategy'), // by_cart_quantity_ranges || every_x_items
																	 'number_of_free_items' => get_sub_field('wctpb_number_of_free_items'),
																	 'every_x_items_vaue' => get_sub_field('wctbp_every_x_items_vaue')
																	);
										
										//New fields, force default value for backward compatibility							  
										$temp_price_strategy['unique_id'] = $temp_price_strategy['unique_id'] ? $temp_price_strategy['unique_id'] : rand(123, 9999999999);
										$temp_price_strategy['free_shipping'] = $temp_price_strategy['free_shipping'] ? $temp_price_strategy['free_shipping'] : false;
										$temp_price_strategy['cart_discount_individual_use_only'] = $temp_price_strategy['cart_discount_individual_use_only'] ? $temp_price_strategy['cart_discount_individual_use_only'] : 'no';
										$temp_price_strategy['give_away_strategy'] = $temp_price_strategy['give_away_strategy'] ? $temp_price_strategy['give_away_strategy'] : 'by_cart_quantity_ranges';
										$temp_price_strategy['number_of_free_items'] = $temp_price_strategy['number_of_free_items'] ? $temp_price_strategy['number_of_free_items'] : 1;
										$temp_price_strategy['every_x_items_vaue'] = $temp_price_strategy['every_x_items_vaue'] ? $temp_price_strategy['every_x_items_vaue'] : 1;
										$temp_price_strategy['apply_discount_value_per_each_matching_item'] = $temp_price_strategy['apply_discount_value_per_each_matching_item'] ? $temp_price_strategy['apply_discount_value_per_each_matching_item'] : 'no';
										$temp_price_strategy['max_discount_value_applicable'] = $temp_price_strategy['max_discount_value_applicable'] ? $temp_price_strategy['max_discount_value_applicable'] : 0;
										
										$temp_date['prices'][] = $temp_price_strategy;
									}
								$time_period['dates'][] = $temp_date;
							} 
						$all_data[] = $time_period;
					
						$return_value = $all_data;
					}
				$this->rules_cache = $all_data;
			}
			else 
			{
				$return_value = $this->rules_cache;
			}
		}
		else
		{	
			$return_value =  get_field($option_name, 'option');
			$return_value = isset($return_value) ? $return_value : $default_value;
		}
		remove_filter('acf/settings/current_language', array(&$this,'cl_acf_set_language'), 100);
		return  $return_value;
	}
	
}
?>