<?php
/**
 * Admin View: Notice - Tracking
 *
 * @package WCPBC/Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$y_args = array(
	'wcpbc_tracker_optin' => 'yes',
	'pbc-hide-notice'     => 'tracking',
);
$n_args = array(
	'wcpbc_tracker_optin' => 'no',
	'pbc-hide-notice'     => 'tracking',
);
?>

<div class="notice notice-info notice-pbc">
	<p>
	<?php
		// translators: HTML tags.
		printf( esc_html( __( 'In order to improve all our features and functionality, %1$sWooCommerce Price Based on Country%2$s needs to collect non-sensitive diagnostic data and usage information. %3$sFind out more%4$s.', 'woocommerce-product-price-based-on-countries' ) ), '<strong>', '</strong>', '<a target="_blank" rel="noopener noreferrer" href="https://www.pricebasedcountry.com/usage-tracking/">', '</a>' );
	?>
	</p>
	<p class="submit">
		<a class="button-primary" href="<?php echo esc_url( wp_nonce_url( add_query_arg( $y_args ), 'pbc_hide_notice_nonce' ) ); ?>"><?php esc_html_e( 'Allow', 'woocommerce-product-price-based-on-countries' ); ?></a>
		<a class="skip button-secondary" href="<?php echo esc_url( wp_nonce_url( add_query_arg( $n_args ), 'pbc_hide_notice_nonce' ) ); ?>"><?php esc_html_e( 'No, do not bother me again', 'woocommerce-product-price-based-on-countries' ); ?></a>
	</p>
</div>
