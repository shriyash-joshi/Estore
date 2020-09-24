<?php wc_get_template_part( 'single-product/layouts/product','header'  ); ?>
	
	<div class="row content-row row-divided row-large row-reverse">
	
	<div id="product-sidebar" class="col large-4  shop-sidebar <?php flatsome_sidebar_classes(); ?>">
		<?php wc_get_template_part( 'single-product/layouts/product-widgets'  ); ?>			
	</div>

	<div class="col large-8">
		
		<div class="product-footer">
			<?php
					/**
					 * woocommerce_after_single_product_summary hook
					 *
					 * @hooked woocommerce_output_product_data_tabs - 10
					 * @hooked woocommerce_upsell_display - 15
					 * @hooked woocommerce_output_related_products - 20
					 */
					do_action( 'woocommerce_after_single_product_summary' );
				?>
		</div>
  </div>
  <?php echo do_shortcode('[related_products limit="12"]');?>
</div>
