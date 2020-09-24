<?php
/**
 * Admin View: Notice - Product type NOT supported
 *
 * @package WCPBC/Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<p style="display:none;" class="notice-pbc wc-price-based-country-upgrade-notice warning wc-pbc-show-if-<?php echo esc_attr( $class ); ?> product-type-<?php echo esc_attr( $value ); ?>">
	<a style="padding: 12px;text-decoration:none;" class="notice-dismiss pbc-hide-notice" data-nonce="<?php echo esc_attr( wp_create_nonce( 'pbc-hide-notice' ) ); ?>" data-notice="<?php echo esc_attr( $notice ); ?>" href="#"></a>
	<?php
	if ( 'third-party' === $class ) {
		// translators: HTML tags.
		printf( esc_html( __( 'Hi, %1$sPrice Based on Country%2$s is compatible with this product type by a third party development. %3$sGreat!, hide this alert%4$s.', 'woocommerce-product-price-based-on-countries' ) ), '<strong>', '</strong>', '<a class="pbc-hide-notice" data-nonce="' . esc_attr( wp_create_nonce( 'pbc-hide-notice' ) ) . '" data-notice="' . esc_attr( $notice ) . '" href="#">', '</a>' );
	} else {
		// translators: HTML tags.
		printf( esc_html( __( 'Hi, this product type is not supported by %1$sPrice Based on Country%2$s. Use it with caution.', 'woocommerce-product-price-based-on-countries' ) ), '<strong>', '</strong>' );
	}
	?>
</p>
