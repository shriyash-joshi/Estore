jQuery(document).ready(function(){
	jQuery( document ).on( 'click', '.notice-dismiss', function () {
		var nonce = jQuery( this ).closest( '.notice-wdmad' ).data( 'nonce' );
		jQuery.ajax( ajaxurl,
		  {
		    type: 'POST',
		    data: {
		      action: 'wdm_dismissed_notice_handler',
		      nonce: nonce
		    }
		  } );
	});
});