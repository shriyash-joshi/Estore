<?php
/**
 * Booking resource view.
 *
 * @package WCPBC
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
</td></tr><tr><td colspan="2">

<?php
foreach ( WCPBC_Pricing_Zones::get_zones() as $zone ) :
	$zone_id      = $zone->get_zone_id();
	$base_cost    = empty( $base_costs[ $zone_id ][ $resource_id ] ) ? '' : $base_costs[ $zone_id ][ $resource_id ];
	$block_cost   = empty( $block_costs[ $zone_id ][ $resource_id ] ) ? '' : $block_costs[ $zone_id ][ $resource_id ];
	$price_method = empty( $price_methods[ $zone_id ][ $resource_id ] ) || wcpbc_is_exchange_rate( $price_methods[ $zone_id ][ $resource_id ] ) ? 'exchange_rate' : 'manual';
	$visibility   = wcpbc_is_exchange_rate( $price_method ) ? 'hidden' : 'visible';
?>
<table class="wcpbc_booking_resources_pricing">
	<tr>
		<td>
			<label><?php echo esc_html( wcpbc_price_method_label( __( 'Costs for', 'wc-price-based-country-pro' ), $zone ) ); ?></label>
			<select name="<?php echo esc_attr( $zone->get_postmetakey( '_booking_resource_price_method' ) . '[' . $loop . ']' ); ?>" class="booking_resource_price_method">
			<?php foreach ( wcpbc_price_method_options() as $key => $value ) : ?>
			<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $price_method ); ?>><?php echo esc_html( $value ); ?></option>
			<?php endforeach ?>
			</select>
		</td>
		<td class="booking_resource_cost" style="visibility: <?php echo esc_attr( $visibility ); ?>;">
			<label><?php esc_html_e( 'Base Cost', 'wc-price-based-country-pro' ); ?>:</label>
			<input type="text" class="wc_input_price" name="<?php echo esc_attr( $zone->get_postmetakey( '_booking_resource_cost' ) . '[' . $loop . ']' ); ?>" value="<?php echo esc_attr( $base_cost ); ?>" placeholder="0.00" />
		</td>
		<td class="booking_resource_cost" style="visibility: <?php echo esc_attr( $visibility ); ?>;">
			<label><?php esc_html_e( 'Block Cost', 'wc-price-based-country-pro' ); ?>:</label>
			<input type="text" class="wc_input_price" name="<?php echo esc_attr( $zone->get_postmetakey( '_booking_resource_block_cost' ) . '[' . $loop . ']' ); ?>" value="<?php echo esc_attr( $block_cost ); ?>" placeholder="0.00" />
		</td>
	</tr>
</table>
<?php endforeach; ?>
