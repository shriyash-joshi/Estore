<?php 
class WCTBP_CartItemTableFragment
{
	public function __construct()
	{
		//Cart page
		add_action('woocommerce_cart_totals_before_order_total', array(&$this, 'add_saved_amount_to_cart_item_table'));
		
		//Checkout page
		add_action('woocommerce_review_order_before_order_total', array(&$this, 'add_saved_amount_to_checkout_item_table'));
		//add_action('woocommerce_review_order_after_shipping', array(&$this, 'add_saved_amount_to_checkout_item_table'));
	}
	public function add_saved_amount_to_cart_item_table()
	{
		global $wctbp_price_changer, $wctbp_cart_addon, $wctbp_option_model, $wctbp_text_model;
		
		$display_saved_amount_on_item_table = $wctbp_option_model->get_option('wctbp_display_saved_amount_on_item_table','no') == 'yes' ? true : false;
		if(!$display_saved_amount_on_item_table)
			return;
		$labels = $wctbp_text_model->get_texts();
		$total_saved = $wctbp_price_changer->get_total_saved() + $wctbp_cart_addon->get_total_saved();
		if($total_saved == 0)
			return;
		?>
		<tr class="">
			<th><?php echo $labels['shop_and_cart_pages_saved_amount_label']; ?></th>
			<td><?php echo wc_price($total_saved); ?></td>
		</tr>
		<?php 
	}
	public function add_saved_amount_to_checkout_item_table()
	{
		global $wctbp_price_changer, $wctbp_cart_addon, $wctbp_option_model, $wctbp_text_model;
		
		$display_saved_amount_on_item_table = $wctbp_option_model->get_option('wctbp_display_saved_amount_on_item_table','no') == 'yes' ? true : false;
		if(!$display_saved_amount_on_item_table)
			return;
		$labels = $wctbp_text_model->get_texts();
		$total_saved = $wctbp_price_changer->get_total_saved() + $wctbp_cart_addon->get_total_saved();
		if($total_saved == 0)
			return;
		?>
		<tr class="">
			<th><?php echo $labels['shop_and_cart_pages_saved_amount_label']; ?></th>
			<td><?php echo wc_price($total_saved); ?></td>
		</tr>
		<?php 
	}
}
?>