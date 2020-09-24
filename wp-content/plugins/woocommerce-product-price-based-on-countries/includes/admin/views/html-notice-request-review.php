<?php
/**
 * Admin View: Notice - Request a review
 *
 * @package WCPBC/Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args = array(
	'pbc-hide-notice' => 'request_review',
	'remind'          => 'yes',
);
?>
<div class="notice notice-info notice-pbc pbc-request-review">
	<p class="rate-description">
	<?php
		// translators: HTML tags.
		printf( esc_html( __( 'Hi! Do you like %1$sWooCommerce Price Based on Country%2$s? Would you mind taking a moment to rate it? It won\'t take more than a minute. Thanks for your support!', 'woocommerce-product-price-based-on-countries' ) ), '<strong>', '</strong>' );
	?>
	<span class="dashicons dashicons-smiley"></span></p>
	<p class="submit">
		<a class="button-primary" target="_blank" rel="noopener noreferrer" href="https://wordpress.org/support/plugin/woocommerce-product-price-based-on-countries/reviews/?filter=5#new-post"><?php esc_html_e( 'Yes, rate now!', 'woocommerce-product-price-based-on-countries' ); ?></a>
		<a class="button-secondary" href="<?php echo esc_url( wp_nonce_url( add_query_arg( $args ), 'pbc_hide_notice_nonce' ) ); ?>"><?php esc_html_e( 'Remind me later', 'woocommerce-product-price-based-on-countries' ); ?></a>
		<a class="button-secondary" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'pbc-hide-notice', 'request_review' ), 'pbc_hide_notice_nonce' ) ); ?>"><?php esc_html_e( 'No, thanks', 'woocommerce-product-price-based-on-countries' ); ?></a>
	</p>
</div>
