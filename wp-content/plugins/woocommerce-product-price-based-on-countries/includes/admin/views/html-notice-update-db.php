<?php
/**
 * Admin View: Notice - Update DB
 *
 * @package WCPBC/Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php if ( 'admin_notices' === current_action() ) : ?>
<div class="notice notice-error">
<?php else : ?>
<div id="message" class="error inline">
<?php endif; ?>
	<p>
	<?php
	// translators: HTML tags.
	printf( esc_html( __( '%1$sWooCommerce Price Based on Country Database Update Required%2$s We just need to update your install to the latest version', 'woocommerce-product-price-based-on-countries' ) ), '<strong>', '</strong> &#8211;' );
	?>
	</p>
	<p class="submit" style="margin-top:0;"><a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=wc-settings&tab=price-based-country' ), 'do_update_wc_price_based_country', 'update_wc_price_based_country_nonce' ) ); ?>" class="wc-update-now button-primary"><?php esc_html_e( 'Run the updater', 'woocommerce-product-price-based-on-countries' ); ?></a></p>
</div>
<script type="text/javascript">
	jQuery('.wc-update-now').click('click', function(){
		var answer = confirm( '<?php esc_html_e( 'It is strongly recommended that you backup your database before proceeding. Are you sure you wish to run the updater now?', 'woocommerce-product-price-based-on-countries' ); ?>' );
		return answer;
	});
</script>
