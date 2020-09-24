<?php
defined( 'ABSPATH' ) || exit;
?>
<section class="woocommerce-order-details">

	<h3 class="woocommerce-order-details__title"><?php esc_html_e( 'Assessment Details', 'woocommerce' ); ?></h3>

	<table class="woocommerce-table woocommerce-table--order-details shop_table order_details">

		<thead>
			<tr>
				<th class="woocommerce-table__product-name product-name"><?php esc_html_e( 'Product', 'woocommerce' ); ?></th>
				<th class="woocommerce-table__product-table product-total"><?php esc_html_e( 'Program', 'woocommerce' ); ?></th>
				<th class="woocommerce-table__product-table product-total"><?php esc_html_e( 'Action', 'woocommerce' ); ?></th>
			</tr>
		</thead>

		<tbody>
		<?php foreach ( $order_assessments as $assessment) {
			    $product = wc_get_product( $assessment->product_id );?>				
				<tr>
					<td><?php echo $product->get_name();?></td>
					<td><?php echo $assessment->assessment_name;?></td>
					<td><a target="_blank" class='button secondary' href='<?php echo $assessment->test_link;?>'>Start Program</a></td>
				</tr>
		<?php } ?>
		</tbody>
		<tfoot>
		</tfoot>
	</table>
	</section><!-- /.col2-set -->
