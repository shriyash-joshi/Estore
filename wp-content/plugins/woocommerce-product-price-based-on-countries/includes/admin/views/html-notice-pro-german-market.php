<?php
/**
 * Admin View: Notice - Upgrade to Pro to German Market support support
 *
 * @package WCPBC/Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="notice notice-info notice-pbc pbc-is-dismissible">
	<a class="notice-dismiss notice-pbc-close" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'pbc-hide-notice', 'pro_german_market' ), 'pbc_hide_notice_nonce' ) ); ?>"><?php esc_html_e( 'Dismiss' ); ?></span></a>
	<p>
	<?php
		// Translators: HTML tags.
		printf( esc_html( __( 'Hi! Are you using %1$sGerman Market%2$s plugin? That\'s great! %1$sPrice Based on Country Pro%2$s supports the German Market plugin. %3$sRead more%4$s.', 'woocommerce-product-price-based-on-countries' ) ), '<strong>', '</strong>', '<a href="https://www.pricebasedcountry.com/2018/12/10/compatible-with-german-market-by-marketpress/?utm_source=german-market&utm_medium=banner&utm_campaign=Get_Pro" target="_blank" rel="noopener noreferrer">', '</a>' );
	?>
	</p>
</div>
