<div id="wcpbc_product_addons_data" class="panel woocommerce_options_panel wc-metaboxes-wrapper" <?php echo ( empty( $product_addons ) ? 'style="min-height: inherit;"' : '' ); ?>>

	<?php if ( empty( $product_addons ) ) : ?>
		<div id="message" class="inline notice woocommerce-message" style="margin: 10px;">
			<p><?php echo sprintf( __( 'Before you can edit a zone pricing for add-ons you need to add some add-ons and %ssave%s.', 'wc-price-based-country-pro' ), '<strong>','</strong>'); ?></p>
		</div>
	<?php else : ?>

	<p class="toolbar">
		<a href="#" class="close_all"><?php _e( 'Close all', 'wc-price-based-country-pro' ); ?></a> / <a href="#" class="expand_all"><?php _e( 'Expand all', 'wc-price-based-country-pro' ); ?></a>
	</p>

	<div class="wc-metaboxes">

	<?php foreach ( get_option( 'wc_price_based_country_regions', array() ) as $zone_id => $zone ) : ?>

		<div class="wc-metabox closed">
			<h3>
				<div class="handlediv" title="<?php _e( 'Click to toggle', 'wc-price-based-country-pro' ); ?>"></div>
				<strong><?php echo $zone[ 'name' ] . ' (' . get_woocommerce_currency_symbol( $zone[ 'currency' ] ) . ') ' ; ?></strong>
			</h3>
			<table cellpadding="0" cellspacing="0" class="wc-metabox-content">
				<tbody>
				<?php
				foreach ( $product_addons as $kaddon => $addon ) {
					if ( $addon['type'] !== 'custom_price' ) {
						$price_method = empty( $zone_pricing[$zone_id][$kaddon]['price_method'] ) ? 'exchange_rate' : $zone_pricing[$zone_id][$kaddon]['price_method'];
					?>
						<tr>
							<td class="wcpbc-product-addon">
								<label for="addon_zone_price_method_<?php echo "{$zone_id}_{$kaddon}"; ?>"><?php echo __( 'Group', 'wc-price-based-country-pro' ) . '"' . esc_attr( $addon['name'] ) . '"'; ?> &mdash;</label>
								<select class="wcpbc-price-method" id="addon_zone_price_method_<?php echo "{$zone_id}_{$kaddon}"; ?>" name="_product_addons_zone_pricing[<?php echo $zone_id; ?>][<?php echo $kaddon; ?>][price_method]" class="">
									<option <?php selected( 'exchange_rate', $price_method ); ?> value="exchange_rate"><?php _e( 'Calculate prices by exchange rate', 'wc-price-based-country-pro' ); ?></option>
									<option <?php selected( 'manual', $price_method ); ?> value="manual"><?php _e( 'Set prices manually', 'wc-price-based-country-pro' ); ?></option>
								</select>
							</td>
						</tr>
						<tr <?php echo ( $price_method === 'manual' ? 'style="display:table-row;"' : '' ); ?> class="wcpbc-product-addon-options">
							<td class="data">
								<table cellspacing="0" cellpadding="0">
									<thead>
										<tr>
											<th><?php _e( 'Label', 'wc-price-based-country-pro' ); ?></th>
											<th class="price_column"><?php _e( 'Price', 'wc-price-based-country-pro' ); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php foreach ( $addon[ 'options' ] as $index => $option ) : ?>
										<tr>
											<td><div class="wcpbc-product-addon-option-label"><?php echo esc_attr( $option['label'] ); ?></div></td>
											<td><input type="text" name="_product_addons_zone_pricing[<?php echo $zone_id; ?>][<?php echo $kaddon; ?>][price][<?php echo $index; ?>]" value="<?php echo ( isset( $zone_pricing[$zone_id][$kaddon]['price'][$index] ) ? esc_attr( wc_format_localized_price( $zone_pricing[$zone_id][$kaddon]['price'][$index] ) ) : '' ); ?>" placeholder="0.00" class="wc_input_price" /></td>
										</tr>
										<?php endforeach; ?>
									</tbody>
								</table>
							</td>
						</tr>
					<?php
					}
				}
				?>
				</tbody>
			</table>
		</div>

	<?php endforeach; ?>

	</div>

	<?php endif; ?>

</div>
<script type="text/javascript">
	jQuery(document).ready(function($){
		$('.wcpbc-price-method').on( 'change', function(){
			$(this).closest('tr').next('.wcpbc-product-addon-options').toggle( $(this).val() == 'manual' );
		});
	});
</script>