<?php
/**
 * Admin View: Product Export
 *
 * @since 2.4.2
 * @package WCPBC/Admin/Views
 */

$price_type_labels = array(
	'flat_fee'       => __( 'Flat Fee', 'wc-price-based-country-pro' ),
	'quantity_based' => __( 'Quantity Based', 'wc-price-based-country-pro' ),
);

?>
<div id="wcpbc_product_addons_data" class="panel woocommerce_options_panel wc-metaboxes-wrapper" <?php echo ( empty( $product_addons ) ? 'style="min-height: inherit;"' : '' ); ?>>

	<?php if ( empty( $product_addons ) ) : ?>
		<div class="inline notice woocommerce-message" style="margin: 10px;">
			<p>
			<?php
				// translators: 1, 2 strong HTML tag.
				echo wp_kses_post( sprintf( __( 'Before you can edit a zone pricing for add-ons you need to add some add-ons and %1$ssave%2$s.', 'wc-price-based-country-pro' ), '<strong>', '</strong>' ) );
			?>
			</p>
		</div>
	<?php else : ?>

	<div class="wc-pao-field-header">
		<p><strong><?php esc_html_e( 'Zone Pricing Add-on fields', 'wc-price-based-country-pro' ); ?></strong></p>
		<p class="wc-pao-toolbar wc-pao-has-addons">
			<a href="#" class="wc-pao-expand-all expand_all"><?php esc_html_e( 'Expand all', 'wc-price-based-country-pro' ); ?></a>&nbsp;/&nbsp;<a href="#" class="wc-pao-close-all close_all"><?php esc_html_e( 'Close all', 'wc-price-based-country-pro' ); ?></a>
		</p>
	</div>

	<div id="wcpbc-addons-update-required" class="inline notice woocommerce-message" style="margin: 10px;display:none;">
		<p><?php esc_html_e( 'Please, update the add-ons fields changes before update the zone pricing.', 'wc-price-based-country-pro' ); ?></p>
	</div>

	<div class="wc-metaboxes">

	<?php foreach ( WCPBC_Pricing_Zones::get_zones() as $zone ) : ?>

		<div class="wc-metabox closed">
			<h3>
				<div class="handlediv" title="<?php esc_html_e( 'Click to toggle', 'wc-price-based-country-pro' ); ?>"></div>
				<strong><?php echo esc_html( $zone->get_name() . ' (' . get_woocommerce_currency_symbol( $zone->get_currency() ) . ') ' ); ?></strong>
			</h3>
			<table cellpadding="0" cellspacing="0" class="wc-metabox-content">
				<tbody>
				<?php
				foreach ( $product_addons as $loop => $addon ) :
					$addon_zone_data           = empty( $zone_pricing[ $zone->get_zone_id() ][ $loop ] ) ? array() : $zone_pricing[ $zone->get_zone_id() ][ $loop ];
					$addon_price_method        = empty( $addon_zone_data['price_method'] ) ? 'exchange_rate' : $addon_zone_data['price_method'];
					$addon_type                = ! empty( $addon['type'] ) ? $addon['type'] : 'multiple_choice';
					$addon_type_formatted      = is_callable( array( $GLOBALS['Product_Addon_Admin'], 'convert_type_name' ) ) ? $GLOBALS['Product_Addon_Admin']->convert_type_name( $addon_type ) : '';
					$price_type                = ! empty( $addon['price_type'] ) ? $addon['price_type'] : '';
					$addon_title               = ! empty( $addon['name'] ) ? $addon['name'] : '';
					$addon_options             = ! empty( $addon['options'] ) ? $addon['options'] : array();
					$display_option_rows_class = 'multiple_choice' !== $addon_type && 'checkbox' !== $addon_type ? 'hide' : 'show';
					$display_limits_rows_class = 'custom_price' === $addon_type ? 'show' : 'hide';
					$display_price_rows_class  = 'hide' === $display_limits_rows_class && 'hide' === $display_option_rows_class && 'heading' !== $addon_type && ! empty( $addon['adjust_price'] ) && 'percentage_based' !== $price_type ? 'show' : 'hide';
					$has_flat_fee_price        = 'show' === $display_price_rows_class || 'show' === $display_limits_rows_class;
					$input_name                = '_product_addons_zone_pricing[' . $zone->get_zone_id() . '][' . $loop . ']';

					// Has flat fee prices.
					if ( 'show' === $display_option_rows_class ) {
						// Check options.
						foreach ( $addon_options as $option ) {
							if ( 'percentage_based' !== $option['price_type'] ) {
								$has_flat_fee_price = true;
								break;
							}
						}
					}
				?>
				<tr>
					<td>
						<div class="wcpbc-pao-addon <?php echo esc_attr( wcpbc_is_exchange_rate( $addon_price_method ) ? 'closed' : '' ); ?> ">

							<div class="wcpbc-pao-addon-header">
								<div class="wcpbc-pao-col1">
									<h2 class="wcpbc-pao-addon-name"><?php echo esc_html( $addon_title ); ?></h2>
									<small class="wcpbc-pao-addon-type"><?php echo esc_html( $addon_type_formatted ); ?></small>
								</div>

								<div class="">
									<select class="wcpbc-price-method" id="addon_zone_price_method_<?php echo esc_attr( $zone->get_zone_id() . '-' . $loop ); ?>" name="<?php echo esc_attr( $input_name ); ?>[price_method]" style="width: auto !important;float: none;margin: 5px 0 0 5px;">
										<option <?php selected( $addon_price_method, 'exchange_rate' ); ?> value="exchange_rate"><?php esc_html_e( 'Calculate prices by exchange rate', 'wc-price-based-country-pro' ); ?></option>
										<option <?php selected( $addon_price_method, 'manual' ); ?> value="manual"><?php esc_html_e( 'Set prices manually', 'wc-price-based-country-pro' ); ?></option>
									</select>
								</div>
							</div><!-- wcpbc-pao-addon-header -->

							<div class="wc-pao-addon-content">

							<?php if ( $has_flat_fee_price ) : ?>

								<div class="wc-pao-addon-content-option-rows <?php echo esc_attr( $display_option_rows_class ); ?>">

									<div class="wc-pao-addon-content-option-inner">
										<div class="wc-pao-addon-content-headers">
											<div class="wc-pao-addon-content-option-header">
												<?php esc_html_e( 'Option', 'woocommerce-product-addons' ); ?>
											</div>

											<div class="wc-pao-addon-content-price-header">
												<div class="wc-pao-addon-content-price-wrap">
													<?php esc_html_e( 'Price', 'woocommerce-product-addons' ); ?>
												</div>
											</div>
										</div>

										<div class="wc-pao-addon-content-options-container">
											<?php foreach ( $addon_options as $index => $option ) : ?>
											<?php if ( 'percentage_based' !== $option['price_type'] ) : ?>

											<div class="wc-pao-addon-option-row">
												<div class="wc-pao-addon-content-label full">
													<span><?php echo esc_html( $option['label'] ); ?></span>
												</div>
												<div class="wc-pao-addon-content-price-type">
												<span><?php echo esc_html( $price_type_labels[ $option['price_type'] ] ); ?></span>
												</div>
												<div class="wc-pao-addon-content-price">
													<input type="text" name="<?php echo esc_attr( $input_name ); ?>[price][<?php echo esc_attr( $index ); ?>]" value="<?php echo esc_attr( isset( $addon_zone_data['price'][ $index ] ) ? ( wc_format_localized_price( $addon_zone_data['price'][ $index ] ) ) : '' ); ?>" placeholder="0.00" class="wc_input_price" />
												</div>
											</div><!-- wc-pao-addon-option-row -->

											<?php endif; ?>
											<?php endforeach; ?>
										</div>

									</div><!-- wc-pao-addon-content-option-inner -->

								</div><!-- wc-pao-addon-content-option-rows -->

								<div class="wc-pao-addon-content-non-option-rows">

									<div class="wc-pao-row wc-pao-addon-adjust-price-container <?php echo esc_attr( $display_price_rows_class ); ?>">
										<div class="wc-pao-addon-adjust-price-settings <?php echo esc_attr( $display_price_rows_class ); ?>" >
											<label for="<?php echo esc_attr( $input_name ); ?>[adjust_price]"><?php echo esc_html( $price_type_labels[ $price_type ] ); ?></label>
											<input type="text" name="<?php echo esc_attr( $input_name ); ?>[adjust_price]" value="<?php echo esc_attr( isset( $addon_zone_data['adjust_price'] ) ? ( wc_format_localized_price( $addon_zone_data['adjust_price'] ) ) : '' ); ?>" placeholder="0.00" class="wc-pao-addon-adjust-price-value wc_input_price" />
										</div>
									</div><!-- wc-pao-row wc-pao-addon-adjust-price-container -->

									<div class="wc-pao-row wc-pao-addon-restrictions-container <?php echo esc_attr( $display_limits_rows_class ); ?>">
										<label>
											<?php esc_html_e( 'Limit price range', 'wc-price-based-country-pro' ); ?>
										</label>
										<div class="wc-pao-addon-restrictions-settings <?php echo esc_attr( $display_limits_rows_class ); ?>">
											<div class="wc-pao-addon-min-max <?php echo esc_attr( $display_limits_rows_class ); ?>">
												<input type="number" name="<?php echo esc_attr( $input_name ); ?>[min]" value="<?php echo esc_attr( isset( $addon_zone_data['min'] ) ? $addon_zone_data['min'] : '' ); ?>" placeholder="0" min="0" style="display:inline;;min-width:80px;margin-left:0 !important;" />&nbsp;<span>&mdash;</span>&nbsp;
												<input type="number" name="<?php echo esc_attr( $input_name ); ?>[max]" value="<?php echo esc_attr( isset( $addon_zone_data['max'] ) ? $addon_zone_data['max'] : '' ); ?>" placeholder="999" min="0" style="display:inline;min-width:80px;"/>
												&nbsp;<em><?php esc_html_e( 'Enter a minimum and maximum value for the limit range.', 'wc-price-based-country-pro' ); ?></em>
											</div>
										</div>
									</div><!-- wc-pao-row wc-pao-addon-restrictions-container -->

								</div><!-- wc-pao-addon-content-non-option-rows -->


							<?php else : ?>

							<div class="inline notice woocommerce-message" style="margin: 10px;">
								<p><?php esc_html_e( 'This field has not a flat fee price.', 'wc-price-based-country-pro' ); ?></p>
							</div>

							<?php endif; ?>

							</div><!-- wc-pao-addon-content -->

						</div><!-- wc-pao-addon -->
					</td>
				</tr>
				<?php endforeach; ?>

				</tbody>
			</table>
		</div>

	<?php endforeach; ?>

	</div>

	<?php endif; ?>

</div>
