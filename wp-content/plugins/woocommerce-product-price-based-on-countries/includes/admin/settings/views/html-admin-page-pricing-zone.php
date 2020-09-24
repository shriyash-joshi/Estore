<?php
/**
 * Pricing zone admin
 *
 * @package WCPBC
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="settings-panel wcpbc-zone-settings">

	<h2>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings&tab=price-based-country&section=zones' ) ); ?>"><?php esc_html_e( 'Pricing zones', 'woocommerce-product-price-based-on-countries' ); ?></a> &gt;
		<span class="wcpbc-zone-name"><?php echo esc_html( $zone->get_name() ? $zone->get_name() : __( 'Zone', 'woocommerce-product-price-based-on-countries' ) ); ?></span>
	</h2>

	<table class="form-table">

		<!-- Name -->
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="name"><?php esc_html_e( 'Zone Name', 'woocommerce-product-price-based-on-countries' ); ?></label>
				<?php echo wp_kses_post( wc_help_tip( __( 'This is the name of the zone for your reference.', 'woocommerce-product-price-based-on-countries' ) ) ); ?>
			</th>
				<td class="forminp forminp-text">
					<input name="name" id="name" type="text" value="<?php echo esc_attr( $zone->get_name() ); ?>"/>
				</td>
		</tr>

		<!-- Country multiselect -->
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="countries"><?php esc_html_e( 'Countries', 'woocommerce-product-price-based-on-countries' ); ?></label>
				<?php echo wp_kses_post( wc_help_tip( __( 'These are countries inside this zone. Customers will be matched against these countries.', 'woocommerce-product-price-based-on-countries' ) ) ); ?>
			</th>
			<td class="forminp">
				<select multiple="multiple" name="countries[]" style="width:350px" data-placeholder="<?php esc_html_e( 'Choose countries&hellip;', 'woocommerce-product-price-based-on-countries' ); ?>" title="Country" class="chosen_select">
					<?php
					foreach ( $allowed_countries as $country_code => $country_name ) {
						echo '<option value="' . esc_attr( $country_code ) . '" ' . selected( in_array( $country_code, $zone->get_countries(), true ), true, false ) . '>' . esc_html( WC()->countries->countries[ $country_code ] ) . '</option>';
					}
					?>
				</select><br />
				<a class="select_all button" href="#"><?php esc_html_e( 'Select all', 'woocommerce-product-price-based-on-countries' ); ?></a>
				<a class="select_none button" href="#"><?php esc_html_e( 'Select none', 'woocommerce-product-price-based-on-countries' ); ?></a>
				<a class="select_eur button" data-countries='<?php echo esc_attr( '["' . implode( '","', array_intersect( wcpbc_get_currencies_countries( 'EUR' ), array_keys( $allowed_countries ) ) ) . '"]' ); ?>' href="#"><?php esc_html_e( 'Select Eurozone', 'woocommerce-product-price-based-on-countries' ); ?></a>
				<a class="select_eur_none button" data-countries='<?php echo esc_attr( '["' . implode( '","', wcpbc_get_currencies_countries( 'EUR' ) ) . '"]' ); ?>' href="#"><?php esc_html_e( 'Unselect Eurozone', 'woocommerce-product-price-based-on-countries' ); ?></a>
			</td>
		</tr>

		<!-- Currency select -->
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="currency"><?php esc_html_e( 'Currency', 'woocommerce-product-price-based-on-countries' ); ?></label>
			</th>
			<td class="forminp forminp-select">
				<select name="currency" id="currency" class="chosen_select">
					<?php
					foreach ( get_woocommerce_currencies() as $code => $name ) {
						echo '<option value="' . esc_attr( $code ) . '" ' . selected( $zone->get_currency(), $code ) . '>' . esc_html( $name . ' (' . get_woocommerce_currency_symbol( $code ) . ')' ) . '</option>';
					}
					?>
				</select>
			</td>
		</tr>
	<?php if ( wcpbc_is_pro() ) : ?>
	</table>
	<h2><?php esc_html_e( 'Exchange Rate Options', 'woocommerce-product-price-based-on-countries' ); ?></h2>
	<table class="form-table">
	<?php endif; ?>
		<!-- Exchange rate -->
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="exchange_rate"><?php esc_html_e( 'Exchange Rate', 'woocommerce-product-price-based-on-countries' ); ?></label>
			</th>
			<td class="forminp forminp-text">
				<span style="line-height:30px;">1 <?php echo esc_html( get_option( 'woocommerce_currency' ) ); ?> = </span><input name="exchange_rate" id="exchange_rate" type="text" class="short wc_input_decimal" value="<?php echo esc_attr( wc_format_localized_decimal( $zone->get_exchange_rate() ) ); ?>" style="width: 348px;" />
			</td>
		</tr>

		<?php do_action( 'wc_price_based_country_settings_page_pricing_zone', $zone ); ?>

		<?php if ( wc_prices_include_tax() ) : ?>
		<!-- Price entered with tax -->
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="exchange_rate"><?php esc_html_e( 'Price entered with tax', 'woocommerce-product-price-based-on-countries' ); ?></label>
			</th>
			<td class="forminp forminp-checkbox">
				<fieldset>
					<legend class="screen-reader-text"><span><?php esc_html_e( 'Do not adjust taxes based on location.', 'woocommerce-product-price-based-on-countries' ); ?></span></legend>
					<label for="disable_tax_adjustment">
						<input name="disable_tax_adjustment" id="disable_tax_adjustment" type="checkbox" <?php echo $zone->get_disable_tax_adjustment() ? 'checked="checked"' : ''; ?> value="1"> 
						<?php
						// Translators: 1,2 Link to doc.
						printf( esc_html( __( 'Do not adjust taxes based on location (%1$sread more%2$s)', 'woocommerce-product-price-based-on-countries' ) ), '<a target="_blank" rel="noopener noreferrer" href="https://www.pricebasedcountry.com/docs/getting-started/prices-entered-with-tax-show-a-wrong-value/?utm_source=settings&utm_medium=banner&utm_campaign=Docs">', '</a>' );
						?>
					</label>
					<p class="description"><?php esc_html_e( 'Check this to disable tax adjustment. e.g., If a product costs 10 including tax, all users will pay 10 regardless of country taxes.', 'woocommerce-product-price-based-on-countries' ); ?></p>
				</fieldset>
			</td>
		</tr>
		<?php endif; ?>
	</table>

	<input type="hidden" name="page" value="wc-settings" />
	<input type="hidden" name="tab" value="wc_price_based_country" />
	<input type="hidden" name="section" value="zones" />

	<p class="submit">
		<?php submit_button( __( 'Save Changes', 'woocommerce-product-price-based-on-countries' ), 'primary', 'save', false ); ?>
		<?php if ( $zone->get_zone_id() ) : ?>
		<a class="wcpbc-delete-zone" style="color: #a00; text-decoration: none; margin-left: 10px;" href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'delete_zone' => $zone->get_zone_id() ), admin_url( 'admin.php?page=wc-settings&tab=price-based-country&section=zones' ) ), 'wc-price-based-country-delete-zone' ) ); ?>"><?php esc_html_e( 'Delete zone', 'woocommerce-product-price-based-on-countries' ); ?></a>
		<?php endif; ?>
	</p>

</div>
