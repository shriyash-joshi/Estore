<?php
/**
 * The template for displaying the country switcher
 *
 * This template can be overridden by copying it to yourtheme/woocommerce-product-price-based-on-countries/country-selector.php.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WCPBC/Templates
 * @version 1.8.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! function_exists( 'wcpbc_manual_country_script' ) ) {

	/**
	 * Add inline form and enqueue script to handle the currency switcher.
	 */
	function wcpbc_manual_country_script() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		wp_enqueue_script( 'wc-price-based-country-switcher', WCPBC()->plugin_url() . 'assets/js/country-switcher' . $suffix . '.js', array( 'jquery' ), wcpbc()->version, true );
		?>
		<form method="post" id="wcpbc-widget-country-switcher-form" class="wcpbc-widget-country-switcher" style="display: none;">
			<input type="hidden" name="wcpbc-manual-country" value="" />
		</form>
		<?php
	}
}
add_action( 'wp_footer', 'wcpbc_manual_country_script', 5 );

if ( empty( $countries ) ) {
	exit;
}
?>
<select class="wcpbc-country-switcher country-switcher">
	<?php foreach ( $countries as $key => $value ) : ?>
		<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $selected_country ); ?> ><?php echo esc_html( $value ); ?></option>
	<?php endforeach; ?>
</select>
