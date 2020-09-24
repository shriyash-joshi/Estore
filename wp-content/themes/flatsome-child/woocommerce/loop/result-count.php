<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="woocommerce-result-count">
		<nav class="gridlist-toggle"><span id="grid" title="Grid view" class="uni-gridlist grid active"><i class="dashicons dashicons-grid-view"></i> <em>Grid view</em></span><span id="list" title="List view" class="uni-gridlist list"><i class="dashicons dashicons-exerpt-view"></i> <em>List view</em></span></nav>
</div>

<script>
	jQuery('.uni-gridlist').on('click',function(){
		jQuery(this).addClass('active');
		if( jQuery(this).hasClass('list') ){		
			jQuery('.grid').removeClass('active');			
			jQuery('.products').addClass('list');
		} 	
		if(jQuery(this).hasClass('grid')) {
			jQuery('.list').removeClass('active');
			jQuery('.products').removeClass('list');
		}	
	});
</script>