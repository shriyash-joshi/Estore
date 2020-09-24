<?php
/**
 * Admin View: Product Export
 *
 * @since 2.4.2
 * @package WCPBC/Admin/Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<table style="display:none;">
	<tr id="wcpbc-addon-pricing-submenu">
		<td colspan="2">
			<ul class="subsubsub">
				<li><a href="#" class="current" data-id="poststuff"><?php esc_html_e( 'Add-on fields', 'wc-price-based-country-pro' ); ?></a> | </li>
				<li><a href="#" class="" data-id="wcpbc-addon-pricing"><?php esc_html_e( 'Zone Pricing Add-on fields', 'wc-price-based-country-pro' ); ?></a></li>
			</ul>
		</td>
	</tr>
	<tr id="wcpbc-addon-pricing" style="display: none;">
		<td class="postbox" colspan="2" style="padding: 0;">
			<?php include dirname( __FILE__ ) . '/html-addon-panel.php'; ?>
		</td>
	</tr>
</table>
<?php
	wc_enqueue_js( "
		// Insert addon pricing
		$('#poststuff').closest('tr').before( $('#wcpbc-addon-pricing-submenu') );
		$('#wcpbc-addon-pricing').insertAfter( $('#wcpbc-addon-pricing-submenu') );

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

		// Submenu navs.
		$('#wcpbc-addon-pricing-submenu ul.subsubsub li a').on('click', function( event ){
			event.preventDefault();
			var \$menu = $(this).closest('ul');
			\$menu.find('li a').removeClass('current');
			$(this).addClass('current');
			\$menu.find('li a').each(function( index ) {
				var \$tab = $( '#' + $(this).data('id') );
				if ( $(this).hasClass('current') ) {
					\$tab.show();
				} else {
					\$tab.hide();
				}
			});
		});
	");
?>
