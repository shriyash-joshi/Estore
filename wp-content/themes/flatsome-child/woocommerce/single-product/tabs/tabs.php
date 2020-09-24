<?php
/**
 * Single Product tabs
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/tabs/tabs.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.8.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get sections instead of tabs if set.
if ( get_theme_mod( 'product_display' ) == 'sections' ) {
	wc_get_template_part( 'single-product/tabs/sections' );

	return;
}

// Get accordion instead of tabs if set.
if ( get_theme_mod( 'product_display' ) == 'accordian' ) {
	wc_get_template_part( 'single-product/tabs/accordian' );

	return;
}

/**
 * Filter tabs and allow third parties to add their own.
 *
 * Each tab is an array containing title, callback and priority.
 *
 * @see woocommerce_default_product_tabs()
 */
$product_tabs = apply_filters( 'woocommerce_product_tabs', array() );

$tab_count   = 0;
$panel_count = 0;

if ( ! empty( $product_tabs ) ) : ?>

	<div class="woocommerce-tabs wc-tabs-wrapper container tabbed-content">
	  <div class="tab-panels">
			<?php foreach ( $product_tabs as $key => $product_tab ) : ?>
				<div class="active" id="tab-<?php echo esc_attr( $key ); ?>" role="tabpanel" aria-labelledby="tab-title-<?php echo esc_attr( $key ); ?>">
					<?php if ( $key == 'description' && ux_builder_is_active() ) echo flatsome_dummy_text(); // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped ?>
					<?php
					if ( isset( $product_tab['callback'] ) ) {
						call_user_func( $product_tab['callback'], $key, $product_tab );
					}
					?>
				</div>
				<?php $panel_count++; ?>
			<?php endforeach; ?>

			<?php do_action( 'woocommerce_product_after_tabs' ); ?>
		</div>
	</div>

<?php endif; ?>
<script>
jQuery('document').ready(function(){
	jQuery(".tab-click").on('click',function(e) {
		if (this.hash !== "") {
     	e.preventDefault();  
		if (!jQuery(this).parents('.active').length) {		
			if (parseInt(jQuery(window).width()) <= 549) {
				jQuery('html, body').animate({
					scrollTop: eval(jQuery('#' + jQuery(this).attr('data-id')).offset().top-150 -100)
			}, 1000);
			}
			else{
				jQuery('html, body').animate({
					scrollTop: eval(jQuery('#' + jQuery(this).attr('data-id')).offset().top-50 -100)
				}, 1000);
			}
		}	
    } 
})
});
</script>