<?php
/**
 * Admin View: Accommodation booking rate
 *
 * @since 2.4.8
 * @package WCPBC/Admin/Views
 */

?>

<div class="options_group wcpbc_pricing">
<?php
		woocommerce_wp_radio(
			array(
				'id'      => $zone->get_postmetakey( '_wc_accommodation_price_method' ),
				'value'   => $zone->is_exchange_rate_price( get_the_ID() ) ? 'exchange_rate' : 'manual',
				'class'   => 'wcpbc_price_method',
				'label'   => __( 'Cost for', 'wc-price-based-country' ) . ' ' . str_replace( ' ', '&nbsp;', $zone->get_name() . ' (' . get_woocommerce_currency_symbol( $zone->get_currency() ) . ')' ),
				'options' => array(
					'exchange_rate' => __( 'Calculate by the exchange rate', 'wc-price-based-country' ),
					'manual'        => __( 'Set manually', 'wc-price-based-country' ),
				),
			)
		);
	?>
	<div style="display: <?php echo ( $zone->is_exchange_rate_price( get_the_ID() ) ? 'none' : 'block' ); ?>" class="wcpbc_show_if_manual">
		<?php
		woocommerce_wp_text_input(
			array(
				'id'                => $zone->get_postmetakey( '_wc_accommodation_booking_base_cost' ),
				'label'             => __( 'Standard room rate', 'woocommerce-accommodation-bookings' ),
				'placeholder'       => '',
				'type'              => 'number',
				'value'             => round( $zone->get_postmeta( get_the_ID(), '_wc_booking_base_cost' ), 2 ),
				'custom_attributes' => array(
					'min'  => '',
					'step' => '0.01',
				),
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'                => $zone->get_postmetakey( '_wc_accommodation_booking_display_cost' ),
				'label'             => __( 'Display cost', 'woocommerce-accommodation-bookings' ),
				'placeholder'       => '',
				'type'              => 'number',
				'value'             => round( $zone->get_postmeta( get_the_ID(), '_wc_display_cost' ), 2 ),
				'custom_attributes' => array(
					'min'  => '',
					'step' => '0.01',
				),
			)
		);
		?>

		<div class="table_grid">
			<table class="widefat">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Range type', 'woocommerce-accommodation-bookings' ); ?></th>
						<th><?php esc_html_e( 'Starting', 'woocommerce-accommodation-bookings' ); ?></th>
						<th><?php esc_html_e( 'Ending', 'woocommerce-accommodation-bookings' ); ?></th>
						<th><?php esc_html_e( 'Cost', 'woocommerce-accommodation-bookings' ); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ( $rows as $i => $value ) : ?>
				<?php if ( isset( $types[ $value['type'] ] ) ) : ?>
				<tr>
					<td><span><?php echo esc_html( $types[ $value['type'] ] ); ?></span></td>
					<?php if ( 'custom' === $value['type'] ) : ?>
					<td><span><?php echo esc_html( $value['from'] ); ?></span></td>
					<td><span><?php echo esc_html( $value['to'] ); ?></span></td>
					<?php else : ?>
					<td><span><?php echo esc_html( $intervals[ $value['type'] ][ $value['from'] ] ); ?></span></td>
					<td><span><?php echo esc_html( $intervals[ $value['type'] ][ $value['to'] ] ); ?></span></td>
					<?php endif; ?>
					<td>
						<input type="number" min="" step="0.01" placeholder="0" value="<?php echo esc_attr( $costs[ $i ] ); ?>" name="<?php echo esc_attr( $zone->get_postmetakey( '_wc_accommodation_booking_pricing_block_cost' ) . '[' . $value['uniqid'] . ']' ); ?>" />
					</td>
				</tr>
				<?php endif; ?>
				<?php endforeach; ?>
				</tbody>
				<tfoot>
					<tr>
						<th colspan="4"><span class="description"><?php esc_html_e( 'You can manage rates from Rates tab.', 'wc-price-based-counry' ); ?></span></th>
					</tr>
				</tfoot>
			</table>
		</div>
	</div>
</div>
