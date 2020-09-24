<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<table style="display:none;">
	<tr id="wcpbc-addon-pricing">
		<th>
			<label for="wcpbc-addons"><?php _e( 'Add-ons zone pricing', 'wc-price-based-country-pro' ); ?></label>
		</th>
		<td class="postbox">
			<?php include( dirname( __FILE__ ) . '/html-addon-panel.php' ); ?>
		</td>
	</tr>
</table>
<script type="text/javascript">
	jQuery(document).ready(function($){

		// Insert addon pricing
		$('#poststuff').closest('tr').after('<tr id="clear"><th></th><td></td></tr>')
		$('#wcpbc-addon-pricing').insertAfter($('#clear'));

		// Open and close the Product Add-On metaboxes
		jQuery('#wcpbc-addon-pricing .wc-metaboxes-wrapper').on('click', '.wc-metabox h3', function(event){
			// If the user clicks on some form input inside the h3, like a select list (for variations), the box should not be toggled
			if (jQuery(event.target).filter(':input, option').length) {
				return;
			}

			jQuery(this).next('.wc-metabox-content').toggle();
			openclose();
			})
		.on('click', '.expand_all', function(){
			jQuery(this).closest('.wc-metaboxes-wrapper').find('.wc-metabox > table').show();
			openclose();
			return false;
		})
		.on('click', '.close_all', function(){
			jQuery(this).closest('.wc-metaboxes-wrapper').find('.wc-metabox > table').hide();
			openclose();
			return false;
		});
		jQuery('.wc-metabox.closed').each(function(){
			jQuery(this).find('.wc-metabox-content').hide();
		});
		<?php if ( empty( $_GET['edit'] ) && ! ( empty( $_GET['add'] ) ) ) : ?>

		if ( $('input[name="edit_id"]').val() > 0 ) {
			var url_ga = '<?php echo admin_url( 'edit.php?post_type=product&page=global_addons' ); ?>';
			$(location).attr('href', url_ga + '&edit=' + $('input[name="edit_id"]').val() );
		}
		<?php endif; ?>
	});
</script>