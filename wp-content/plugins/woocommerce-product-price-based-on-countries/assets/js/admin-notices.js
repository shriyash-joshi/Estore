/* global wc_price_based_country_admin_notices_params */
jQuery( function( $ ) {
	'use strict';

	/**
	 * Hide notices via Ajax.
	 */
	var wcpbc_notices = {

		init: function(){
			$( '.notice-pbc a.pbc-hide-notice').on('click', this.ajax_hide_notice );
		},

		ajax_hide_notice: function(e) {
			e.preventDefault();
			var el = $(this).closest('.notice-pbc');
			$(el).find('.pbc-wait').remove();
			$(el).append('<div class="pbc-wait"></div>');
			if ( $('.notice-pbc.updating').length > 0 ) {
				var button = $(this);
				setTimeout(function(){
					button.triggerHandler( 'click' );
				}, 100);
				return false;
			}
			$(el).addClass('updating');
			$.post( wc_price_based_country_admin_notices_params.ajax_url, {
					action: 	'wcpbc_hide_notice',
					security: 	$(this).data('nonce'),
					notice: 	$(this).data('notice'),
					remind: 	$(this).hasClass( 'remind-later' ) ? 'yes' : 'no'
			}, function(){
				$(el).removeClass('updating');
				$(el).fadeOut(100);
			});
		}
	};
	wcpbc_notices.init();
});
