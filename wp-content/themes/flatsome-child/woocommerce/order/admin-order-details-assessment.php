<?php
defined( 'ABSPATH' ) || exit;
?>
<section class="woocommerce-order-details">

	<table class="widget fixed">

		<thead>
			<tr>
				<th class="manage-column column-columnname"><?php esc_html_e( 'Product', 'woocommerce' ); ?></th>
				<th class="manage-column column-columnname"><?php esc_html_e( 'Program', 'woocommerce' ); ?></th>
				<th class="manage-column column-columnname"><?php esc_html_e( 'Link', 'woocommerce' ); ?></th>
                <th class="manage-column column-columnname"><?php esc_html_e( 'Test User Email', 'woocommerce' ); ?></th>
				<th class="manage-column column-columnname"><?php esc_html_e( 'User Pin', 'woocommerce' ); ?></th>
			</tr>
		</thead>

		<tbody>
		<?php foreach ( $order_assessments as $assessment) {
			    $product = wc_get_product( $assessment->product_id );?>				
				<tr>
					<td class="column-name"><?php echo $product->get_name();?></td>
					<td class="column-name"><?php echo $assessment->assessment_name;?></td>
					<td class="column-name"><?php echo $assessment->test_link;?></td>
                    <td class="column-name"><?php echo $assessment->test_user_email;?></td>
                    <td class="column-name"><?php echo $assessment->test_user_id;?></td>
				</tr>
		<?php } ?>
		</tbody>
		<tfoot>
		</tfoot>
	</table>
	</section><!-- /.col2-set -->

<style>

table {
  border-collapse: collapse;
}

.manage-column,.column-name {
  border: 1px solid black;
}
</style>
