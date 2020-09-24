<div class="row content-row row-divided row-large row-reverse">
		<div class="large-12 ">	
					<?php echo do_shortcode('[block id="product-page-slider"]');?>	
		</div>
	</div>	
	<?php 
	$product_tabs = apply_filters( 'woocommerce_product_tabs', array() );
	$tab_count   = 0;
	if ( ! empty( $product_tabs ) ) : ?>
		<div style="background: #E2E6E6;max-width: 100%;margin-bottom: 10px;padding: 0;position: sticky;top: 60px;z-index: 9;
    height: 60px;padding-left: 40px;" class="woocommerce-tabs wc-tabs-wrapper container tabbed-content">
			<ul class="tabs wc-tabs product-tabs  <?php flatsome_product_tabs_classes(); ?>" role="tablist">
				<?php foreach ( $product_tabs as $key => $product_tab ) : ?>
					<li class="<?php echo esc_attr( $key ); ?>_tab <?php if ( $tab_count == 0 ) echo 'active'; ?>" id="tab-title-<?php echo esc_attr( $key ); ?>" role="tab" aria-controls="tab-<?php echo esc_attr( $key ); ?>">
						<a class="tab-click" data-id="tab-<?php echo esc_attr( $key ); ?>" href="#tab-<?php echo esc_attr( $key ); ?>">
							<?php echo wp_kses_post( apply_filters( 'woocommerce_product_' . $key . '_tab_title', $product_tab['title'], $key ) ); ?>
						</a>
					</li>
					<?php $tab_count++; ?>
				<?php endforeach; ?>		
					<?php global $product;?>		
					<li class="right uni-program-wishlist"> <?php echo do_shortcode('[yith_wcwl_add_to_wishlist]');?> </li>
					<li class="right uni-program-share"> <?php echo do_shortcode('[TheChamp-Sharing]'); ?></li>
					<li class="right"> <a id="program-gift" style="margin-top: -10px;" href="<?php echo get_site_url()?>/checkout/?is_gift=true&add-to-cart=<?php echo $product->get_id();?>"><img src='<?php echo get_stylesheet_directory_uri().'/assets/img/gift.png';?>' style='width:40px;height:auto;'/></a></li>		
			</ul>
		</div>
<?php endif;?>