<?php
/**
 * Admin View: Coupon pricing by zone
 *
 * @since 2.4.7
 * @package WCPBC/Admin/Views
 */

?>
<div class="options_group wcpbc_pricing">
<?php
		woocommerce_wp_radio(
			array(
				'id'      => $zone->get_postmetakey( '_price_method' ),
				'value'   => $zone->is_exchange_rate_price( get_the_ID() ) ? 'exchange_rate' : 'manual',
				'class'   => 'wcpbc_price_method',
				'label'   => __( 'Amount for', 'wc-price-based-country' ) . ' ' . str_replace( ' ', '&nbsp;', $zone->get_name() . ' (' . get_woocommerce_currency_symbol( $zone->get_currency() ) . ')' ),
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
				'id'            => $zone->get_postmetakey( 'coupon_amount' ),
				'label'         => __( 'Coupon amount', 'woocommerce' ) . ' (' . get_woocommerce_currency_symbol( $zone->get_currency() ) . ')',
				'placeholder'   => '',
				'data_type'     => 'price',
				'wrapper_class' => '_regular_price',
				'value'         => $zone->get_postmeta( get_the_ID(), 'coupon_amount' ),
			)
		);
		?>
	</div>
</div>
