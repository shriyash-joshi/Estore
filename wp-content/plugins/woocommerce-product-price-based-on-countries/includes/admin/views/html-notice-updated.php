<?php
/**
 * Admin View: Notice - Data Updated
 *
 * @package WCPBC/Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<?php if ( 'admin_notices' === current_action() ) : ?>
<div class="notice notice-success notice-pbc pbc-is-dismissible">
<?php else : ?>
<div id="message" class="updated inline notice-pbc pbc-is-dismissible">
<?php endif; ?>
	<a class="notice-dismiss notice-pbc-close" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'pbc-hide-notice', 'updated', remove_query_arg( 'update_wc_price_based_country_nonce' ) ), 'pbc_hide_notice_nonce' ) ); ?>"><?php esc_html_e( 'Dismiss', 'woocommerce-product-price-based-on-countries' ); ?></a>
	<p><strong>WooCommerce Price Based on Country:</strong> <?php esc_html_e( 'Data update complete. Thank you for updating to the latest version!', 'woocommerce-product-price-based-on-countries' ); ?></p>
</div>
