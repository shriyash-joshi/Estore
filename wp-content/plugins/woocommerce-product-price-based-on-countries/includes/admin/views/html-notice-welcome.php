<?php
/**
 * Admin View: Notice - Welcome
 *
 * @package WCPBC/Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="notice notice-info notice-pbc pbc-welcome-panel">
	<div class="pbc-welcome-panel-body">
		<a class="notice-dismiss" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'pbc-hide-notice', 'welcome' ), 'pbc_hide_notice_nonce' ) ); ?>"><?php esc_html_e( 'Dismiss', 'woocommerce-product-price-based-on-countries' ); ?></a>
		<h2>
		<?php
			// translators: 1: PBoC version.
			echo esc_html( sprintf( __( 'Welcome to WooCommerce Price Based on Country %1$s', 'woocommerce-product-price-based-on-countries' ), WCPBC()->version ) );
		?>
		</h2>
		<p class="about-description"><?php esc_html_e( 'Where do you want to start?', 'woocommerce-product-price-based-on-countries' ); ?></p>
		<p class="">
			<a class="button-secondary" target="_blank" rel="noopener noreferrer" href="https://www.pricebasedcountry.com/docs/?utm_source=welcome&utm_medium=banner&utm_campaign=Docs"><span class="dashicons dashicons-book"></span><?php esc_html_e( 'Getting Started Guide', 'woocommerce-product-price-based-on-countries' ); ?></a>
			<a class="button-secondary" href="<?php echo esc_url( admin_url( wp_nonce_url( 'admin.php?page=wc-settings&tab=price-based-country&pbc-hide-notice=welcome', 'pbc_hide_notice_nonce' ) ) ); ?>"><span class="dashicons dashicons-admin-generic"></span><?php esc_html_e( 'Settings', 'woocommerce-product-price-based-on-countries' ); ?></a>
		</p>
	</div>
</div>
