jQuery(document).ready(function(){
	jQuery(document).delegate('#wdm-schedule-clear', 'click', function (e) {
		if (!canClear()) {
			return false;
		}

		var result = confirm("Do you realy want to clear the schedule? Click ok to continue.");
		// alert(result);
		if (result) {
			clearAllFields();
		}
		return false;
	});
	jQuery( '#product-type' ).change(function() {
		if (jQuery( '#product-type' ).val() != 'simple' || jQuery( '#product-type' ).val() != 'course') {
			jQuery('.wdm_simple_schedule').hide();
			jQuery('.wdm-select-days-cont').hide();
		}
		if (jQuery( '#product-type' ).val() == 'simple' || jQuery( '#product-type' ).val() == 'course') {
			jQuery('.wdm_simple_schedule').show();
			jQuery('.wdm-select-days-cont').show();
		}
	});

	function canClear()
	{
		if (jQuery('.wdm_start_date').val() !== "") {
			return true;
		} else if (jQuery('.wdm_end_date').val() !== "") {
			return true;
		} else if (jQuery('.wdm_start_time').val() !== "") {
			return true;
		} else if (jQuery('.wdm_end_time').val() !== "") {
			return true;
		} else if (jQuery('.wdm_show_timer').is(":checked")) {
			return true;
		} else if (jQuery('[name^="days_of_week"]').is(":checked")) {
			return true;
		} else if (jQuery('#_hide_if_unavailable').is(":checked")) {
			return true;			
		}

		return false;
	}

	function clearAllFields()
	{
		jQuery('.wdm_start_date').val("");
		jQuery('.wdm_end_date').val("");
		jQuery('.wdm_start_time').val("");
		jQuery('.wdm_end_time').val("");
		jQuery('#_hide_if_unavailable').attr('checked',false);
		jQuery('.wdm_show_timer').attr('checked', false);
		jQuery('[name^="days_of_week"]').attr('checked', false);
	}
});