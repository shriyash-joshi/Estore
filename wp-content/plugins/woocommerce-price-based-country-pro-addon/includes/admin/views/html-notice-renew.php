<?php
/**
 * Admin View: Notice - Review
 *
 * @package WCPBC/Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="notice notice-warning">
	<p>
	<?php
	if ( 'expired' === $license_data['status'] ) {
		// translators: HTML tags.
		printf( esc_html( __( 'Your license key for %1$sPrice Based on Country Pro%2$s has expired. It\'s time to %1$srenew and save %3$s off%2$s the original price.', 'wc-price-based-country-pro' ) ), '<strong>', '</strong>', '50%' );
	} else {
		// translators: HTML tags.
		printf( esc_html( __( 'Your license key for %1$sPrice Based on Country Pro%2$s is expiring within %3$s days. It\'s time to %1$srenew and save %4$s off%2$s the original price.', 'wc-price-based-country-pro' ) ), '<strong>', '</strong>', absint( $days ), '50%' );
	}
	?>
	</p>
	<p>
		<a class="button-primary" href="<?php echo esc_url( $renewal_url ); ?>"><?php esc_html_e( 'Renew your license now', 'wc-price-based-country-pro' ); ?></a>
		<a class="skip button-secondary" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'hide-renewal-license-notice', '1' ), 'hide_renewal_license_notice' ) ); ?>"><?php esc_html_e( 'I already renewed my license.', 'wc-price-based-country-pro' ); ?></a>
	</p>
</div>
