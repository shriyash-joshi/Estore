jQuery(document).ready(function ($) {
	
	//Show or Hide feature settings according to the feature status.
	if (!jQuery('#wdmws_enable_notify').prop('checked')) {
		jQuery('div.wdmws-notify-settings-section').next('table').css('display','none');
		jQuery('.wdmws-notify-settings-section').prev('h2').css('display','none');
	}
	jQuery('#wdmws_enable_notify').click(function() {
		if ($(this).prop('checked')) {
			jQuery('div.wdmws-notify-settings-section').next('table').css('display','block');
			jQuery('.wdmws-notify-settings-section').prev('h2').css('display','block');
		} else {
			jQuery('div.wdmws-notify-settings-section').next('table').css('display','none');
			jQuery('.wdmws-notify-settings-section').prev('h2').css('display','none');
		}
	});

	//Show or Hide guest user enrollment interface setting
	if (!jQuery('#wdmws_guest_user_enrl').prop('checked')) {
		jQuery('#wdmws_guest_user_enrl_by_popup').closest('tr').hide();
	}
	jQuery('#wdmws_guest_user_enrl').click(function() {
		if (jQuery(this).prop('checked')) {
			jQuery('#wdmws_guest_user_enrl_by_popup').closest('tr').show();
		} else {
			jQuery('#wdmws_guest_user_enrl_by_popup').closest('tr').hide();
		}
	});

	//Show or Hide Custom hours field according to the selection.
	if ('custom_hr'!=jQuery('input[name="wdmws_notify_user_period"]:checked').val()) {
		jQuery('#wdmws_notify_custom_hr').closest('tr').hide();
	}
	jQuery('input[name="wdmws_notify_user_period"]').click(function () {
		if ('custom_hr'==jQuery('input[name="wdmws_notify_user_period"]:checked').val()) {
			jQuery('#wdmws_notify_custom_hr').closest('tr').show();
		} else {
			jQuery('#wdmws_notify_custom_hr').closest('tr').hide();
		}
	});	  
});
