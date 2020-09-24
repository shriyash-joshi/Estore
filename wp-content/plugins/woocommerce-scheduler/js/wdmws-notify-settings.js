(function($){
	jQuery(document).ready(function () {
		let $notify_customers_radio = $("input[name='wdmws_settings[wdmws_notify_user_period][]']");
		let $allow_guest_user_enrl = $('#wdmws_settings_wdmws_guest_user_enrl_0');

		wdmws_hide_show_custom_hr_field();
		wdmws_hide_show_guest_enrl_method($allow_guest_user_enrl);

		$notify_customers_radio.change(function() {
			wdmws_hide_show_custom_hr_field();	
		});

		// Event when 'Allow guest user enrollment' element is clicked.
		$allow_guest_user_enrl.click(function(){
			wdmws_hide_show_guest_enrl_method($allow_guest_user_enrl);
		});

		// Enrolled Users tab JS
		// Enrolled Users Count table
		if($('#enrolled-users-count').length > 0) {
			$('#enrolled-users-count').DataTable({
				"responsive":true,
				"scrollY":'50vh',
				"paging":true,
				"pageLength":10,
				"serverSide": true,
				"language": {
					"infoFiltered": ""
				},
				"ajax":{
					url:ajaxurl,
					type:'POST',
					dataType:'json',
					// dataSrc: '',
					data:{
						action:'wdmws_enrolled_product_user_count',
						start:wdmws_get_page_offset(),
						search:$("input[type=search]").value
					
					},
				},
				columns: [
					{ data: "product_name" },
					{ data: "users_count" },
				]
			});
		}
		
		if($('#enrolled-users').length > 0) {
			// Enrolled Users List table.
			$('#enrolled-users').DataTable({
				"responsive":true,
				"paging":true,
				"pageLength":10,
				"serverSide": true,
				"language": {
					"infoFiltered": ""
				},
				"ajax":{
					url:ajaxurl,
					type:'POST',
					dataType:'json',
					// dataSrc: '',
					data:{
						action:'wdmws_product_enrolled_users_list',
						start:wdmws_get_page_offset(),
						search:$("input[type=search]").value,
						product_id: $("#enrolled-users").data('product-id')   		
					},
				},
				columns: [
					{ data: "user_email" },
					{ data: "enrolled_date" },
					{ 
						data: "remove_link",
						orderable: false
					}
				]
			});
		}
		
		// When disenroll button is clicked.
		$(document).on('click', 'button.disenroll-user', function() {
			let $this = $(this);
			$.ajax({
				type: 'POST',
				url: ajaxurl,
				data:{
					action:'wdmws_product_disenroll_user',
					user_email: $this.data('email'),
					product_id: $this.val()
				},
				// contentType: false,
				// processData: false,
				beforeSend: function(){
					$this.next('.spinner').addClass('wdmws-visible');
				},
				success: function ( response ) {
					$this.closest('tr').remove();
					let table = $('#enrolled-users').DataTable();
					table.draw();
				}
			});
		});
	
		// Export users list.
		$("#export-users-list").on('click', function() {
			let $this = $(this);
			$.ajax({
				type: 'POST',
				url: ajaxurl,
				data:{
					action:'wdmws_create_users_list_csv',
					product_id: $this.val()
				},
				beforeSend: function(){
					$('#export-data-error').text('');
				},
				success: function ( response ) {
					response = response.trim();
					if ('security_check' == response) {
						$('#export-data-error').text(export_data.security_check);
					} 
					else if('user_list_empty' == response) {
						$('#export-data-error').text(export_data.user_list_empty);
					}
					else {
						var link = document.createElement("a");
						location.href = encodeURI(response);
					}
				}
			});
		});

		// Reset Enrollment Email template
		$('#reset-enrollment-email-template').on('click', function(){
			let $this = $(this);
			$.ajax({
				type: 'POST',
				url: ajaxurl,
				data:{
					action:'wdmws_reset_enrollment_email_template',
					security: $('#reset-enrollment-email-template-nonce').val()
				},
				beforeSend: function(){
					$this.next('.spinner').addClass('wdmws-visible');
				},
				success: function ( response ) {
					$this.next('.spinner').removeClass('wdmws-visible');
					if('-1' != response) {
						if($('#wp-wdmws_enrl_email_body-wrap').hasClass('html-active')){ // We are in text mode
							$('#wdmws_enrl_email_body').val(response); // Update the textarea's content
						} else { // We are in tinyMCE mode
							var activeEditor = tinyMCE.get('wdmws_enrl_email_body');
							if(activeEditor!==null){ // Make sure we're not calling setContent on null
								activeEditor.setContent(response); // Update tinyMCE's content
							}
						}
					}
				}
			});
		});	

		// Reset Notification Email template
		$('#reset-notification-email-template').on('click', function(){
			let $this = $(this);
			$.ajax({
				type: 'POST',
				url: ajaxurl,
				data:{
					action:'wdmws_reset_notification_email_template',
					security: $('#reset-notification-email-template-nonce').val()
				},
				beforeSend: function(){
					$this.next('.spinner').addClass('wdmws-visible');
				},
				success: function ( response ) {
					$this.next('.spinner').removeClass('wdmws-visible');
					if('-1' != response) {
						if($('#wp-wdmws_notification_email_body-wrap').hasClass('html-active')){ // We are in text mode
							$('#wdmws_notification_email_body').val(response); // Update the textarea's content
						} else { // We are in tinyMCE mode
							var activeEditor = tinyMCE.get('wdmws_notification_email_body');
							if(activeEditor!==null){ // Make sure we're not calling setContent on null
								activeEditor.setContent(response); // Update tinyMCE's content
							}
						}
					}
				}
			});
		});

		// When save settings button is clicked.
		$('p.submit #submit').click(function(event){
			if('enable' == wdmws_send_email_confirmation.wdmws_notify_enabled) {
				let notifyEvent      = $('input[name="wdmws_settings[wdmws_notify_event][]"]:checked').val();
				let notifyUserPeriod = $('input[name="wdmws_settings[wdmws_notify_user_period][]"]:checked').val();
				let customProductExpType = $('input[name="wdmws_settings[wdmws_custom_product_expiration_type][]"]:checked').val();
				
				if((typeof notifyEvent != 'undefined' && notifyEvent != wdmws_send_email_confirmation.wdmws_notify_event) ||
				(typeof notifyUserPeriod != 'undefined' && notifyUserPeriod != wdmws_send_email_confirmation.wdmws_notify_user_period) ||
				(typeof customProductExpType != 'undefined' && customProductExpType != wdmws_send_email_confirmation.wdmws_product_exp_type)) {
					event.preventDefault();
					wdmws_send_email_now_confirmation();
				}
			} else {
				let notifyFeatureEnabled;
				if($('input[name="wdmws_settings[wdmws_enable_notify]"]').length > 0) {
					notifyFeatureEnabled = $('input[name="wdmws_settings[wdmws_enable_notify]"]').is(':checked') ? 'enable' : '';
				}

				if(typeof notifyFeatureEnabled != 'undefined' && 'enable' == notifyFeatureEnabled && notifyFeatureEnabled != wdmws_send_email_confirmation.wdmws_notify_enabled) {
					event.preventDefault();
					wdmws_send_email_now_confirmation();
				}
			}
		});

		$('#wdmws-save-send-sbt-modal').click(function(){
			let d = new Date();
			d.setTime(d.getTime() + (90 * 24 * 60 * 60 * 1000));
			let expires = "expires=" + d.toGMTString();
			document.cookie = "wdmwsSendEmailNow=saveSend; expires=" + expires + "; path=/";

			if($('body #woo_single_view_update_details').length >= 1) {
				wdmws_send_product_scheduling_request();
			} else {
				$("p.submit #submit").off("click");
				$("p.submit #submit").click();
			}
			$('#wdmws-send-email-now-modal').modal('hide');
		});

		$('#wdmws-save-wout-send-sbt-modal').click(function(){
			let d = new Date();
			d.setTime(d.getTime() + (90 * 24 * 60 * 60 * 1000));
			let expires = "expires=" + d.toGMTString();
			document.cookie = "wdmwsSendEmailNow=saveWoutSend; expires=" + expires + "; path=/";
			
			if($('body #woo_single_view_update_details').length >= 1) {
				wdmws_send_product_scheduling_request();
			} else {
				$("p.submit #submit").off("click");
				$("p.submit #submit").click();
			}
			$('#wdmws-send-email-now-modal').modal('hide');
		});

		$('#wdmws-wout-save-send-sbt-modal').click(function(){
			$('#wdmws-send-email-now-modal').modal('hide');	
		});

		// Popup for Send email now confirmation
		function wdmws_send_email_now_confirmation()
		{
			$('#wdmws-send-email-now-modal').modal('show');
		}

		// Hide or show customer hour field.
		function wdmws_hide_show_custom_hr_field() {
			let $custom_hr_field = jQuery('#wdmws_settings_wdmws_notify_custom_hr_0').closest('tr');

			if ('custom_hr' == jQuery("input[name='wdmws_settings[wdmws_notify_user_period][]']:checked").val()) {
				$custom_hr_field.show();
			} else {
				$custom_hr_field.hide();
			}	
		}

		// Hide/ show guest user enrollment method (Popup/ field).
		function wdmws_hide_show_guest_enrl_method($allow_guest_user_enrl) {
			let $guest_user_enrl_method = jQuery('#wdmws_settings_wdmws_guest_user_enrl_method_0').closest('tr');

			if($allow_guest_user_enrl.is(':checked'))
			{
				$guest_user_enrl_method.show();
			} else {
				$guest_user_enrl_method.hide();
			}
		}

		function wdmws_get_page_offset(){
			var text=$("current").val();
			return text;
		}
	});

})(jQuery);
