<?php 
class WCTBP_ProductTablePage
{
	public function __construct()
	{
		add_action( 'manage_product_posts_custom_column', array(&$this, 'manage_product_schedule_column'), 10, 2 );
		add_filter( 'manage_edit-product_columns', array(&$this, 'add_post_schedule_info_column'),15 );
	}
	function manage_product_schedule_column( $column, $post_id ) 
	{
		global $wctbp_product_model;
		if ( $column != 'wctbp-pricing-rules' ) 
			return;

		$rules = $wctbp_product_model->get_price_rules_by_prduct_id($post_id, 'product');
		$specific_product_rules = $wctbp_product_model->retrieve_rules_by_time_and_user_roles_specific_to_the_product($post_id);
		$general_title_already_printed = false;
		$general_title = '<strong>'.__('General rules: ', 'woocommerce-time-based-pricing').'</strong><br/>';;
		
		if($rules)
		{
			$general_title_already_printed = true;
			echo $general_title;
		
			foreach((array)$rules as $rule)
			{
			  echo '<a class="" target="_blank" href="'.admin_url().'admin.php?page='.WCTBP_PricesConfiguratorPage::$page_url_par.'">'.
					//'<span class="dashicons dashicons-calendar-alt"></span>'.
					$rule.
					'</a><br/>';
			}
		}
			
		//Variations			
		if(($variations = $wctbp_product_model->get_variations($post_id) ) != null)
		{
			foreach((array)$variations as $variation)
			{
				$rules2 = $wctbp_product_model->get_price_rules_by_prduct_id($variation->ID, 'product');
				foreach((array)$rules2 as $rule)
				{
					if(!$general_title_already_printed)
					{
						$general_title_already_printed = true;
						echo $general_title;
					}
					_e('(ID: ','woocommerce-time-based-pricing');
					echo $variation->ID.') <a class="" target="_blank" href="'.admin_url().'admin.php?page='.WCTBP_PricesConfiguratorPage::$page_url_par.'">'.
						//'<span class="dashicons dashicons-calendar-alt"></span>'.
						$rule.
						'</a><br/>';
				}
			}
		}
		
		
		//specific rules
		if($specific_product_rules && is_array($specific_product_rules))
		{
			if(!empty($rules)|| !empty($rules2))
				echo '<br/>';
			
			echo '<strong>'.__('Specific product rules: ', 'woocommerce-time-based-pricing').'</strong><br/>';
			
			foreach((array)$specific_product_rules as $rule)
			{
				echo $rule['rule_name'].
					'<br/>';
			}
		}
		
	}
	function add_post_schedule_info_column($columns)
	 {

	   //remove column
	   //unset( $columns['tags'] );

	   //add column
	   $columns['wctbp-pricing-rules'] =__('Pricing rule(s)', 'woocommerce-time-based-pricing'); 

	   return $columns;
	}
}
?>