<div class="row content-row row-divided row-large row-reverse">
	
	<div class="large-12 ">
	
				<?php echo do_shortcode('[block id="product-page-slider"]');?>
	
	</div>
	
	<div id="product-sidebar" class="col large-4 hide-for-medium shop-sidebar <?php flatsome_sidebar_classes(); ?>">
		<?php
			do_action('flatsome_before_product_sidebar');
			/**
			 * woocommerce_sidebar hook
			 *
			 * @hooked woocommerce_get_sidebar - 10
			 */
			if (is_active_sidebar( 'product-sidebar' ) ) {
				dynamic_sidebar('product-sidebar');
			} else if(is_active_sidebar( 'shop-sidebar' )) {
				dynamic_sidebar('shop-sidebar');
			}

			echo do_shortcode('[product_dilemmas]');

			echo do_shortcode('[block id="simple-program-right-side-attributes"]');

			echo do_shortcode('[block id="students-benefited-block"]');

		?>
	</div>

	<div class="col large-8">
		<div class="product-main">
		<div class="row">
		<div class="product-info summary entry-summary col col-fit <?php flatsome_product_summary_classes();?>">
				<?php
					/**
					 * woocommerce_single_product_summary hook
					 *
					 * @hooked woocommerce_template_single_title - 5
					 * @hooked woocommerce_template_single_rating - 10
					 * @hooked woocommerce_template_single_price - 10
					 * @hooked woocommerce_template_single_excerpt - 20
					 * @hooked woocommerce_template_single_add_to_cart - 30
					 * @hooked woocommerce_template_single_meta - 40
					 * @hooked woocommerce_template_single_sharing - 50
					 */
					do_action( 'woocommerce_single_product_summary' );
				?>

			</div>
		</div>
		</div>
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
</div>
