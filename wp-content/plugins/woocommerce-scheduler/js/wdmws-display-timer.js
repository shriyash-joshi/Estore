jQuery(document).ready(function(){
	var addToCartFlag = false;
	if (wdmws_timer_object.wdmws_is_scheduled) {
		jQuery('.wdmws_timer_circles p').css({'color':wdmws_timer_object.wdmws_font_color});
		if (!jQuery('#wdmws_start_timer').is(':visible') && !jQuery('#wdmws_end_timer').is(':visible') && wdmws_timer_object.is_available == 'no') {
			addToCartFlag = false;
			jQuery('div.summary.entry-summary > form').not('.wdm-notify-me-form').hide();
		}

		if(wdmws_timer_object.is_product_cat_available=="no")
			{
			jQuery('div.summary.entry-summary > form').not('.wdm-notify-me-form').hide();
			}

		if (jQuery('#wdmws_start_timer').is(':visible')) {
			addToCartFlag = false;
			jQuery('div.summary.entry-summary > form').not('.wdm-notify-me-form').hide();
		}


		if (jQuery('#wdmws_end_timer').is(':visible')) {
			addToCartFlag = true;
			jQuery('div.summary.entry-summary > form').not('.wdm-notify-me-form').show();
		}

		var timerDate = new Date(jQuery("#wdmws_end_timer").data('date')).getTime();
		var CurrentTime = new Date().getTime();

		showSimpleStartTimer();
		showSimpleEndTimer();

		jQuery("#wdmws_end_timer").TimeCircles({count_past_zero: false}).addListener(countdownComplete);
		jQuery("#wdmws_start_timer").TimeCircles({count_past_zero: false}).addListener(countdownComplete);
	}

	function showSimpleStartTimer()
	{
		jQuery('#wdmws_start_timer').TimeCircles({
		    "animation": "ticks",
		    "bg_width": 1,
		    "fg_width": 0.04,
		    "count_past_zero": false,
		    "circle_bg_color": wdmws_timer_object.wdmws_background_color,
		    "time": {
		        "Days": {
		            "text": wdmws_timer_object.timerFieldTexts["days"],
		            "color": wdmws_timer_object.wdmws_front_color,
		            "show": true
		        },
		        "Hours": {
		            "text": wdmws_timer_object.timerFieldTexts["hours"],
		            "color": wdmws_timer_object.wdmws_front_color,
		            "show": true
		        },
		        "Minutes": {
		            "text": wdmws_timer_object.timerFieldTexts["minutes"],
		            "color": wdmws_timer_object.wdmws_front_color,
		            "show": true
		        },
		        "Seconds": {
		            "text": wdmws_timer_object.timerFieldTexts["seconds"],
		            "color": wdmws_timer_object.wdmws_front_color,
		            "show": true
		        }
		    }
		});
	}


	function showSimpleEndTimer()
	{
		jQuery('#wdmws_end_timer').TimeCircles({
		    "animation": "ticks",
		    "bg_width": 1,
		    "fg_width": 0.04,
		    "count_past_zero": false,
		    "circle_bg_color": wdmws_timer_object.wdmws_background_color,
		    "time": {
		        "Days": {
		            "text": wdmws_timer_object.timerFieldTexts["days"],
		            "color": wdmws_timer_object.wdmws_front_color,
		            "show": true
		        },
		        "Hours": {
		            "text": wdmws_timer_object.timerFieldTexts["hours"],
		            "color": wdmws_timer_object.wdmws_front_color,
		            "show": true
		        },
		        "Minutes": {
		            "text": wdmws_timer_object.timerFieldTexts["minutes"],
		            "color": wdmws_timer_object.wdmws_front_color,
		            "show": true
		        },
		        "Seconds": {
		            "text": wdmws_timer_object.timerFieldTexts["seconds"],
		            "color": wdmws_timer_object.wdmws_front_color,
		            "show": true
		        }
		    }
		});

	}

	jQuery('div.summary.entry-summary > form:not(.wdm-notify-me-form) > button').click(function(event){
		if (jQuery(this).hasClass('disabled')) {
			alert('Sorry, '+wdmws_timer_object.expirationMsg);
			event.preventDefault();
		}
	});

	function SendAjaxRequest(product_status)
	{
		jQuery.ajax({
			method: "post",
			url: wdmws_timer_object.admin_ajax,
			data: {
			    'action':'update_variation_availability',
			    'product_id' : wdmws_timer_object.product_id,
			    'product_status': product_status,
			    '_wdmws_timer': wdmws_timer_object.wdmws_timer_nonce,
			},
			success:function( data ) {
				if (data == 'Security Check') {
					alert(data);
				}
			}
		});
	}
	
	function countdownComplete(unit, value, total){
		if (total == 2) {
			if (addToCartFlag) {
				SendAjaxRequest('no', jQuery('#wdmws_end_timer').data('date'));
			} else {
				SendAjaxRequest('yes', jQuery('#wdmws_start_timer').data('date'));
			}
		}

		if(total<=0){
			if (addToCartFlag) {
				// jQuery('div.summary.entry-summary > form').hide();
				jQuery('#display_end_timer p').hide();
				jQuery('p.wdm_message').hide();
				jQuery('div.summary.entry-summary > form:not(.wdm-notify-me-form) > button').addClass('disabled');
				// jQuery('div.summary.entry-summary > form').hide();
				jQuery(this).fadeOut('slow').replaceWith('<p class = "wdm_message">'+wdmws_timer_object.expirationMsg+'</p>');
				setTimeout(function(){
					location.reload(true);
					// window.location.href = url;
				}, 5000);
			} else {
				jQuery('p.wdm_message').hide();
				jQuery('#display_start_timer p').hide();
				jQuery(this).fadeOut('slow');
				jQuery('div.summary.entry-summary > form').not('.wdm-notify-me-form').show();
				setTimeout(function(){
					location.reload(true);
					// window.location.href = url;
				}, 5000);
				// location.reload(true);
			}

		}
	}
});
