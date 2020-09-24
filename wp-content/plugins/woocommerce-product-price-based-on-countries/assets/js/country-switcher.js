jQuery( function( $ ) {
	$('body').on('change', 'select.wcpbc-country-switcher', function(){
		var country = $(this).val();
		$('#wcpbc-widget-country-switcher-form input[name="wcpbc-manual-country"]').val(country);
		$('#wcpbc-widget-country-switcher-form').submit();
	});
});