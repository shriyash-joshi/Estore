<?php
/**
 * Currency Switcher template
 *
 * This template can be overridden by copying it to yourtheme/woocommerce-product-price-based-on-countries/currency-switcher.php.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WCPBC/Templates
 * @version 2.7.1
 */

if ( ! defined( 'ABSPATH' ) || empty( $options ) ) {
	exit; // Exit if accessed directly.
}

if ( ! function_exists( 'wcpbc_currency_switcher_script' ) ) {

	/**
	 * Add inline script to handle the currency switcher.
	 */
	function wcpbc_currency_switcher_script() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		wp_enqueue_script( 'wc-price-based-currency-switcher', WC_Product_Price_Based_Country_Pro::plugin_url() . 'assets/js/currency-switcher' . $suffix . '.js', array( 'jquery' ), WC_Product_Price_Based_Country_Pro::$version, true );
		?>
		<form method="post" class="wcpbc-widget-currency-switcher" id="wcpbc-widget-currency-switcher-form" style="display: none;">
			<input type="hidden" name="wcpbc-manual-country" value="" />
		</form>
		<?php
	}
	add_action( 'wp_footer', 'wcpbc_currency_switcher_script', 5 );
}

?>
<select class="wcpbc-currency-switcher currency-switcher<?php echo esc_attr( apply_filters( 'wc_price_based_country_currency_switcher_class', '' ) ); ?>" name="wcpbc-manual-country">
	<?php foreach ( $options as $key => $value ) : ?>
		<option data-currency="<?php echo esc_attr( $currencies[ $key ] ); ?>" value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $selected_country ); ?> ><?php echo esc_html( $value ); ?></option>
	<?php endforeach; ?>
</select>
