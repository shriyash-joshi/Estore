jQuery( function( $ ) {
	$('body').on('change', 'select.wcpbc-currency-switcher', function(){
		var country = $(this).val();
		$('#wcpbc-widget-currency-switcher-form input[name="wcpbc-manual-country"]').val(country);
		$('#wcpbc-widget-currency-switcher-form').submit();
	});
});