<?php 
class WCTBP_Dashboard
{
	public function __construct()
	{
		
		 add_action( 'wp_dashboard_setup', array( &$this, 'add_server_time_widget' ) );
		 //add_action( 'woocommerce_process_product_meta',  array( &$this, 'save_widget_data') );
	}
	public function add_server_time_widget()
	{
		if(current_user_can('manage_woocommerce') || current_user_can('edit_posts'))
			wp_add_dashboard_widget( 'wctbp-server-time', __('WooCommerce Pricing! - Server time', 'woocommerce-time-based-pricing'), array( &$this, 'render_server_time_widget' ));
		 
	}
	function render_server_time_widget()
	{
		global $wctbp_option_model;
		$hour_offset = $wctbp_option_model->get_option('wctbp_time_offset', 0);
		//$minute_offset = $wctbp_option_model->get_option('wctbp_time_minute_offset', 0);
		
		/* $hour = date("H",strtotime($time_offset.' minutes');
		$minute =  */
		?>
		<p class="form-field">
			<label  style="display: inline;"><?php echo __( 'Current server time with offset (date format: dd/mm/yyyy):', 'woocommerce-time-based-pricing' ); ?></label>
			<span class="wrap">
				<strong style=" font-size: 20px;"><?php echo date("d/m/Y H:i",strtotime($hour_offset.' minutes')); ?></strong>
			</span>
			<br/>
			<span style="display:block; clear:both;" class="description"><?php _e( sprintf('Prices rules are syncronized with server time. Configure a minutes time offset by the <a href="%s">Configurator</a>', get_admin_url()."admin.php?page=".WCTBP_GeneralOptionsPage::$page_url_par), 'woocommerce-time-based-pricing' ); ?></span> 
		</p>
							
		<?php
	}
	
}
?>