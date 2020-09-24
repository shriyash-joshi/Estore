<?php
/**
 * Booking pricing view.
 *
 * @package WCPBC
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div id="wcpbc_bookings_pricing" class="panel panel woocommerce_options_panel hidden">

<?php foreach ( WCPBC_Pricing_Zones::get_zones() as $zone ) : ?>

	<div class="options_group wcpbc_pricing">
	<?php
		wcpbc_pricing_input(
			array(
				'name'    => '_booking_price_method',
				'label'   => __( 'Costs for', 'wc-price-based-country-pro' ),
				'value'   => $zone->get_postmeta( get_the_ID(), '_price_method' ),
				'wrapper' => false,
				'fields'  => array(
					array(
						'name'        => '_wc_booking_cost',
						'label'       => __( 'Base cost', 'wc-price-based-country-pro' ),
						'description' => __( 'One-off cost for the booking as a whole.', 'wc-price-based-country-pro' ),
						'desc_tip'    => true,
					),
					array(
						'name'        => '_wc_booking_base_cost',
						'label'       => __( 'Block cost', 'wc-price-based-country-pro' ),
						'description' => __( 'This is the cost per block booked. All other costs (for resources and persons) are added to this.', 'wc-price-based-country-pro' ),
						'desc_tip'    => true,
					),
					array(
						'name'        => '_wc_display_cost',
						'label'       => __( 'Display cost', 'wc-price-based-country-pro' ),
						'description' => __( 'The cost is displayed to the user on the frontend. Leave blank to have it calculated for you. If a booking has varying costs, this will be prefixed with the word "from:".', 'wc-price-based-country-pro' ),
						'desc_tip'    => true,
					),
				),
			),
			$zone
		);

	?>
		<div class="wcpbc_show_if_manual">

			<?php if ( ! empty( $pricing_rows ) && is_array( $pricing_rows ) ) : ?>

			<div class="pricing_warning" style="display:none;">
				<div id="message" class="inline notice woocommerce-message" style="margin: 10px;">
					<p>
					<?php
					// Translators: strong HTML tag.
					echo sprintf( esc_html__( 'Please, %1$ssave%2$s product changes before edit range cost by zone.', 'wc-price-based-country-pro' ), '<strong>', '</strong>' );
					?>
					</p>
				</div>
			</div>

			<div class="table_grid">
				<table class="widefat">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Range type', 'wc-price-based-country-pro' ); ?></th>
							<th colspan="3"><?php esc_html_e( 'Range', 'wc-price-based-country-pro' ); ?></th>
							<th><?php esc_html_e( 'Base cost', 'wc-price-based-country-pro' ); ?>&nbsp;<a class="tips" data-tip="<?php esc_html_e( 'Enter a cost for this rule. Applied to the booking as a whole.', 'wc-price-based-country-pro' ); ?>">[?]</a></th>
							<th><?php esc_html_e( 'Block cost', 'wc-price-based-country-pro' ); ?>&nbsp;<a class="tips" data-tip="<?php esc_html_e( 'Enter a cost for this rule. Applied to each booking block.', 'wc-price-based-country-pro' ); ?>">[?]</a></th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<th colspan="6">
								<?php // Translators: strong HTML tag. ?>
								<span class="description"><?php printf( esc_html__( 'You can manage rules from the %1$sCost%2$s tab.', 'wc-price-based-country-pro' ), '<strong>', '</strong>' ); ?></span>
							</th>
						</tr>
					</tfoot>
					<tbody id="wcpbc_pricing_rows">
					<?php $wcpbc_pricing = $zone->get_postmeta( get_the_ID(), '_pricing' ); ?>
					<?php
					foreach ( $pricing_rows as $index => $data ) :
						$data['type'] = empty( $data['type'] ) ? 'custom' : $data['type'];
					?>
						<tr>
							<td style="width:180px;"><p><?php echo esc_html( $type_labels[ $data['type'] ] ); ?></p></td>
							<?php self::the_range( $data, $intervals ); ?>
							<td><span class="cost_modifier"><?php echo esc_html( $modifier_labels[ $data['base_modifier'] ] ); ?></span><input type="text" class="wc_input_price" name="wcpbc_booking_pricing_base_cost[<?php echo esc_attr( $index ); ?>][<?php echo esc_attr( $zone->get_zone_id() ); ?>]" value="<?php echo esc_attr( ! empty( $wcpbc_pricing[ $index ]['base_cost'] ) ? wc_format_localized_price( $wcpbc_pricing[ $index ]['base_cost'] ) : '' ); ?>" placeholder="0" /></td>
							<td><span class="cost_modifier"><?php echo esc_html( $modifier_labels[ $data['modifier'] ] ); ?></span><input type="text" class="wc_input_price" name="wcpbc_booking_pricing_cost[<?php echo esc_attr( $index ); ?>][<?php echo esc_attr( $zone->get_zone_id() ); ?>]" value="<?php echo esc_attr( ! empty( $wcpbc_pricing[ $index ]['cost'] ) ? wc_format_localized_price( $wcpbc_pricing[ $index ]['cost'] ) : '' ); ?>" placeholder="0" /></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			</div>	<!-- end table_grid -->

			<?php else : ?>
				<div id="message" class="inline notice woocommerce-message" style="margin: 10px;">
					<?php // Translators: strong HTML tag. ?>
					<p><?php echo sprintf( esc_html__( 'No ranges have been found. If you want manage ranges cost by zone, add ranges on the %1$sCost%2$s tab and %1$ssave%2$s.', 'wc-price-based-country-pro' ), '<strong>', '</strong>' ); ?></p>
				</div>
			<?php endif; ?>

		</div> <!-- end wcpbc_show_if_manual -->
	</div> <!-- end wcpbc_pricing -->

	<?php endforeach; ?>

</div>
