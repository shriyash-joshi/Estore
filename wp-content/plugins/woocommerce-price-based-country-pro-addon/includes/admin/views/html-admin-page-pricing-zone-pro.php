<?php
/**
 * Pricing zone Pro settings
 *
 * @package WCPBC
 */

?>
<!-- Auto update exchange rate -->
<tr valign="top">
	<th scope="row" class="titledesc">
		<label for="auto_exchange_rate"><?php esc_html_e( 'Auto update Exchange Rate', 'wc-price-based-country-pro' ); ?></label>
	</th>
	<td class="forminp forminp-radio">
		<fieldset>
			<ul>
				<li>
					<label><input name="auto_exchange_rate" value="yes" <?php checked( ( $zone->get_auto_exchange_rate() ? 'yes' : 'no' ), 'yes' ); ?> type="radio"> <?php esc_html_e( 'Yes, Update exchange rate daily from API provider.', 'wc-price-based-country-pro' ); ?></label>
				</li>
				<li>
					<label><input name="auto_exchange_rate" value="no" <?php checked( ( $zone->get_auto_exchange_rate() ? 'yes' : 'no' ), 'no' ); ?> type="radio"> <?php esc_html_e( 'No, I will enter exchange rate manually.', 'wc-price-based-country-pro' ); ?></label>
				</li>
			</ul>
		</fieldset>
	</td>
</tr>

<!-- Exchange Rate fee -->
<tr valign="top">
	<th scope="row" class="titledesc">
		<label for="exchange_rate_fee"><?php esc_html_e( 'Exchange Rate Fee %', 'wc-price-based-country-pro' ); ?></label>
		<?php echo wc_help_tip( __( 'Enter a fee (percentage) to increment the auto exchange rate.', 'wc-price-based-country-pro' ) ); // WPCS: XSS ok. ?>
	</th>
	<td class="forminp forminp-select">
		<input name="exchange_rate_fee" id="exchange_rate_fee" style="width:60px;" value="<?php echo esc_attr( $zone->get_exchange_rate_fee() ); ?>" class="" placeholder="" min="0" max="100" step="<?php echo esc_attr( apply_filters( 'wc_price_based_country_exchange_rate_fee_step', '1' ) ); ?>" type="number" />
	</td>
</tr>

<!-- Round up -->
<tr valign="top">
	<th scope="row" class="titledesc">
		<label for="round_nearest"><?php esc_html_e( 'Round to Nearest', 'wc-price-based-country-pro' ); ?></label>
		<?php echo wc_help_tip( __( 'Round up the price by exchange rate to the nearest. The result will be a multiple of the value that you select.', 'wc-price-based-country-pro' ) ); // WPCS: XSS ok. ?>
	</th>
	<td class="forminp forminp-select">
	<?php
		$round_nearest = empty( $zone->get_round_nearest() ) ? '' : $zone->get_round_nearest();
	?>
		<select name="round_nearest" id="round_nearest" class="chosen_select">
			<option <?php selected( $round_nearest, '' ); ?> value=""><?php esc_html_e( 'Deactivate', 'wc-price-based-country-pro' ); ?></option>
			<option <?php selected( $round_nearest, '0.05' ); ?> value="0.05" ><?php esc_html_e( '0.05 ( 1785.42 to 1785.45 )', 'wc-price-based-country-pro' ); ?></option>
			<option <?php selected( $round_nearest, '0.5' ); ?> value="0.5"><?php esc_html_e( '0.50 ( 1785.42 to 1785.50 )', 'wc-price-based-country-pro' ); ?></option>
			<option <?php selected( $round_nearest, '5' ); ?> value="5"><?php esc_html_e( '5 ( 1785.42 to 1790 )', 'wc-price-based-country-pro' ); ?></option>
			<option <?php selected( $round_nearest, '50' ); ?> value="50"><?php esc_html_e( '50 ( 1785.42 to 1800 )', 'wc-price-based-country-pro' ); ?></option>
			<option <?php selected( $round_nearest, '500' ); ?> value="500"><?php esc_html_e( '500 ( 1785.42 to 2000 )', 'wc-price-based-country-pro' ); ?></option>
		</select>
	</td>
</tr>

</table>

<h2><?php esc_html_e( 'Currency Options', 'wc-price-based-country-pro' ); ?></h2>
<p><?php esc_html_e( 'The following options affect how prices are displayed on the frontend.', 'wc-price-based-country-pro' ); ?></p>
<table class="form-table">

<!-- Currency format -->
<tr valign="top">
	<th scope="row" class="titledesc">
		<label for="currency_format"><?php esc_html_e( 'Currency Format', 'wc-price-based-country-pro' ); ?></label>
		<?php echo wc_help_tip( __( 'Enter the currency format. Supports the following placeholders: [code] = currency code, [symbol] = currency symbol, [symbol-alt] = alternative currency symbol (US$, CA$, ...), [price] = product price.', 'wc-price-based-country-pro' ) ); // WPCS: XSS ok. ?>
	</th>
	<td class="forminp forminp-text">
		<input name="currency_format" id="currency_format" style="min-width:350px;" value="<?php echo esc_attr( $zone->get_currency_format() ); ?>" class="wc_price_based_country_preview_format" placeholder="<?php echo esc_attr( get_option( 'wc_price_based_currency_format' ) ); ?>" type="text">
		<span class="description">
		<?php
			// Translators: HTML tags.
			printf( esc_html__( 'Leave empty to use %1$sdefault currency format%2$s.', 'wc-price-based-country-pro' ), '<a target="_blank" rel="noopener noreferrer" href="https://www.pricebasedcountry.com/docs/getting-started/settings-options/?utm_source=settings&utm_medium=banner&utm_campaign=Docs">', '</a>' );
		?>
		</span>
		<p class="description"><?php esc_html_e( 'Preview:', 'wc-price-based-country-pro' ); ?> <code id="wc_price_based_currency_format_preview" data-default="<?php echo esc_attr( get_option( 'wc_price_based_currency_format' ) ); ?>"></code></p>
	</td>
</tr>

<!-- Thousand Separator -->
<tr valign="top">
	<th scope="row" class="titledesc">
		<label for="price_thousand_sep"><?php esc_html_e( 'Thousand Separator', 'wc-price-based-country-pro' ); ?></label>
		<?php echo wc_help_tip( __( 'This sets the thousand separator of displayed prices.', 'wc-price-based-country-pro' ) ); // WPCS: XSS ok. ?>
	</th>
	<td class="forminp forminp-text">
		<input name="price_thousand_sep" id="price_thousand_sep" style="width:50px;" value="<?php echo esc_attr( $zone->get_price_thousand_sep() ); ?>" class="" placeholder="" type="text" />
	</td>
</tr>

<!-- Decimal Separator -->
<tr valign="top">
	<th scope="row" class="titledesc">
		<label for="price_decimal_sep"><?php esc_html_e( 'Decimal Separator', 'wc-price-based-country-pro' ); ?></label>
		<?php echo wc_help_tip( __( 'This sets the decimal separator of displayed prices.', 'wc-price-based-country-pro' ) ); // WPCS: XSS ok. ?>
	</th>
	<td class="forminp forminp-text">
		<input name="price_decimal_sep" id="price_decimal_sep" style="width:50px;" value="<?php echo esc_attr( $zone->get_price_decimal_sep() ); ?>" class="wc_price_based_country_preview_decimal_sep" placeholder="" type="text" />
	</td>
</tr>

<!-- Num Decimals -->
<tr valign="top">
	<th scope="row" class="titledesc">
		<label for="price_num_decimals"><?php esc_html_e( 'Number of Decimals', 'wc-price-based-country-pro' ); ?></label>
		<?php echo wc_help_tip( __( 'This sets the number of decimal points shown in displayed prices.', 'wc-price-based-country-pro' ) ); // WPCS: XSS ok. ?>
	</th>
	<td class="forminp forminp-text">
		<input name="price_num_decimals" id="price_num_decimals" style="width:50px;" value="<?php echo esc_attr( $zone->get_price_num_decimals() ); ?>" class="wc_price_based_country_preview_num_decimals" placeholder="" min="0" step="1" type="number" />
	</td>
</tr>
<?php if ( wc_prices_include_tax() ) : ?>
</table>
<h2><?php esc_html_e( 'Taxes', 'wc-price-based-country-pro' ); ?></h2>
<table class="form-table">
<?php endif; ?>
