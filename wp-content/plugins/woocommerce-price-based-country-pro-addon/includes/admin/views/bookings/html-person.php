<?php
/**
 * Booking person view.
 *
 * @package WCPBC
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$price_method = $zone->is_exchange_rate_price( $person_id ) ? 'exchange_rate' : 'manual';
$visibility   = wcpbc_is_exchange_rate( $price_method ) ? 'style="visibility:hidden;"' : ''

?>
</tr><tr>
	<td>
		<label><?php echo esc_html( wcpbc_price_method_label( __( 'Costs for', 'wc-price-based-country-pro' ), $zone ) ); ?></label>
		<select class="booking_person_price_method" name="<?php echo esc_attr( $zone->get_postmetakey( '_booking_person_price_method' ) . '[' . $loop . ']' ); ?>" class="">
		<?php foreach ( wcpbc_price_method_options() as $key => $value ) : ?>
			<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $price_method ); ?>><?php echo esc_html( $value ); ?></option>
		<?php endforeach ?>
		</select>
	</td>
	<td class="booking_person_cost" <?php echo ( wcpbc_is_exchange_rate( $price_method ) ? 'style="visibility:hidden;"' : '' ); ?>>
		<label><?php esc_html_e( 'Base Cost', 'wc-price-based-country-pro' ); ?>:</label>
		<input type="text" name="<?php echo esc_attr( $zone->get_postmetakey( '_booking_person_person_cost' ) . '[' . $loop . ']' ); ?>" placeholder="0" value="<?php echo esc_attr( $zone->get_postmeta( $person_id, '_cost' ) ); ?>" />
	</td>
	<td class="booking_person_cost" <?php echo 'manual' !== $price_method ? 'style="visibility:hidden;"' : ''; ?>>
		<label><?php esc_html_e( 'Block Cost', 'wc-price-based-country-pro' ); ?>:</label>
		<input type="text" class="" name="<?php echo esc_attr( $zone->get_postmetakey( '_booking_person_block_cost' ) . '[' . $loop . ']' ); ?>" placeholder="0" value="<?php echo esc_attr( $zone->get_postmeta( $person_id, '_block_cost' ) ); ?>" />
	</td>
