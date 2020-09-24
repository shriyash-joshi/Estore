jQuery(document).ready(function($){
	var days = {'0': 'Sunday', '1': 'Monday', '2': 'Tuesday', '3': 'Wednesday', '4': 'Thursday', '5': 'Friday', '6': 'Saturday'};
	var variation_data = wdmws_variation_timer_object.variation_data;
	var selectedVariation = "";
	var addToCartFlag = false;
	var attributes;
	var systemTimeFlag = false;


	/**
	 * This function returns the next availability|unavailability dateTime-stamp by comparing the
	 * current dateTimestamp with availability|unAvailabilityTimestamp.
	 * @param {array} availabilityPairs - utc array of start & end time pairs 
	 * @param {string} getNext - unavailability/ availability time
	 */
	function getWdmNextAvailability(availabilityPairs, getNext){
		let currentDateTime=new Date().getTime();
		var availabilityPairs= JSON.parse(availabilityPairs);

		 if (getNext=="unavailable") 
		 {
			 let retVal="";			
			 availabilityPairs.makeUnAvailable.forEach(function (nexttimestamp) {
				nexttimestamp=new Date(nexttimestamp*1000);
				nexttimestamp.setTime( nexttimestamp.getTime() + nexttimestamp.getTimezoneOffset()*60*1000 );
				nexttimestamp=nexttimestamp.getTime();
				if (currentDateTime<nexttimestamp) {
					if(retVal==""){
						retVal= nexttimestamp+2000; //2 seconds delay
					}
					return false;
					}		
				});
			return retVal;
		} 
		else {
			let retVal="";
			availabilityPairs.makeAvailable.forEach(function (nexttimestamp) {
				nexttimestamp=new Date(nexttimestamp*1000);
				nexttimestamp.setTime( nexttimestamp.getTime() + nexttimestamp.getTimezoneOffset()*60*1000 );
				nexttimestamp=nexttimestamp.getTime();
				if (currentDateTime<nexttimestamp) {
					if(retVal==""){
						retVal= nexttimestamp+2000; //2 seconds delay
					}
					return false;
					}		
				});
			return retVal;			
			}
	}

	jQuery( document ).delegate(".reset_variations", "click", function () {
		if (typeof (history.pushState) != "undefined") {
	        var UrlObj = { Title:jQuery(document).find("title").text(), Url: wdmws_variation_timer_object.product_url};
	        history.pushState(UrlObj, UrlObj.Title, UrlObj.Url);
	    }
	});

	jQuery( document ).delegate(".single_variation_wrap", "show_variation", function (event, variation, purchasable) {
		selectedVariation = variation;
		// if (typeof (history.pushState) != "undefined") {
		// 	var UrlObj = { Title:jQuery(document).find("title").text(), Url: wdmws_variation_timer_object.product_url};
		// 	history.pushState(UrlObj, UrlObj.Title, UrlObj.Url);
		// }
		attributes = variation.attributes
		
		// location.reload();
		var variation_id = variation.variation_id;
		var timerData = variation_data[variation_id];
		var selectedDays = timerData.selectedDays;
		var day = new Date(timerData.tillTime).getDay();

		jQuery('div.summary.entry-summary > form > div > div.woocommerce-variation-add-to-cart.variations_button').click(function(event){
			if (jQuery(this).hasClass('disabled')) {
				alert('Sorry, this product is unavailable. Please choose a different combination.');
				event.preventDefault();
			}
		});

		jQuery('.wdmws_timer_circles p').css({'color':wdmws_variation_timer_object.wdmws_font_color});

		jQuery('.wdmws_timer_circles p').hide();

		// if (!jQuery('#wdmws_start_timer').is(':visible') && !jQuery('#wdmws_end_timer').is(':visible')) {
		// 	addToCartFlag = false;
		// 	jQuery('div.summary.entry-summary > form > div > div.woocommerce-variation-add-to-cart.variations_button').hide();	
		// }


		if (jQuery('#wdmws_start_timer').is(':visible')) {
			addToCartFlag = false;
			jQuery('div.summary.entry-summary > form > div > div.woocommerce-variation-add-to-cart.variations_button').hide();
		}


		if (jQuery('#wdmws_end_timer').is(':visible')) {
			addToCartFlag = true;
			jQuery('div.summary.entry-summary > form > div > div.woocommerce-variation-add-to-cart.variations_button').show();
		}

		if (timerData.wdmws_is_scheduled) {
			if(timerData.scheduleType=="productLaunch")	{
				if(!timerData.availability){
					$launchTime=new Date(timerData.startTime).getTime()+2000;//2seconds delay
					wdmwsShowStartTimer($launchTime, timerData.enableStartTimer, timerData.scheduleType);
				}
			} else {
				if (timerData.availability) {
					//if product is available
					let nextDateTimeUnavailable = getWdmNextAvailability(timerData.availabilityPairs, 'unavailable');
					if (""!=nextDateTimeUnavailable) {
					wdmwsShowEndTimer(nextDateTimeUnavailable, timerData.enableEndTimer, timerData.scheduleType)	
					}	
				} else {
					let nextDateTimeAvailable = getWdmNextAvailability(timerData.availabilityPairs, 'available');
					if(""!=nextDateTimeAvailable)
					{
					wdmwsShowStartTimer(nextDateTimeAvailable, timerData.enableStartTimer, timerData.scheduleType);
					}
				}
				
			}
	        // if (typeof timerData != undefined && !timerData.availability) {
	        // 	if (timerData.isToBeScheduledCondition1) {
		    //     	if (timerData.showCurrentTimer1) {
			// 			wdmwsShowStartTimer(timerData.nextDate, timerData.wdmwsShowTimer, timerData.enableProductSpecificTimer, timerData.enableStartTimer);
		    //         } else if (timerData.showCurrentTimer2) {
			// 			showBeginTimer(timerData.wdmwsShowTimer, timerData.enableProductSpecificTimer, timerData.wdmBeginDate, selectedDays, wdmws_variation_timer_object.schedule_type, timerData.enableStartTimer);
		    //         }
	        // 	} else if (timerData.isToBeScheduledCondition2) {
	        // 		if (timerData.showAfterTimer1) {
	        // 			wdmwsShowStartTimer(timerData.nextDate, timerData.wdmwsShowTimer, timerData.enableProductSpecificTimer, timerData.enableStartTimer);
	        // 		} else if (timerData.showAfterTimer2) {
	        // 			showBeginTimer(timerData.wdmwsShowTimer, timerData.enableProductSpecificTimer, timerData.startTime, selectedDays, wdmws_variation_timer_object.schedule_type, timerData.enableStartTimer);
	        // 		}
	        // 	} else if (timerData.isToBeScheduledCondition3) {
			// 		showBeginTimer(timerData.wdmwsShowTimer, timerData.enableProductSpecificTimer, timerData.wdmBeginDate, selectedDays, wdmws_variation_timer_object.schedule_type, timerData.enableStartTimer);
	        // 	}
			// } else {
			// 	if (timerData.scheduleWillEnd1 && wdmws_variation_timer_object.schedule_type == "per_day") {
			// 		wdmwsShowEndTimer(timerData.tillTime, timerData.wdmwsShowTimer, timerData.enableProductSpecificTimer, timerData.enableEndTimer);
			// 	} else {
			// 		wdmwsShowEndTimer(timerData.wdmFinishDate, timerData.wdmwsShowTimer, timerData.enableProductSpecificTimer, timerData.enableEndTimer);
			// 	}
			// }
		}
	});

	function changeCurrentProductUrl(attributes)
	{
		var url = window.location.href;    
	
		jQuery.each(attributes, function(index, currentValue) {
			if (url.indexOf('?') > -1) {
			   url += '&'+index+'='+currentValue;
			}else{
			   url += '?'+index+'='+currentValue;
			}
		});
		setTimeout(function(){
			window.location.href = url;
		}, 5000);
	}

	function showBeginTimer(wdmwsShowTimer, enableProductSpecificTimer, wdmBeginDate, selectedDays, schedule_type, enableStartTimer)
	{
	    if (!isSelectedDay(wdmBeginDate, selectedDays, schedule_type)) {
	        return;
	    }

	    wdmwsShowStartTimer(wdmBeginDate, wdmwsShowTimer, enableProductSpecificTimer, enableStartTimer);
	}

	function isSelectedDay(date, selectedDays, schedule_type)
	{
		var day = new Date(date).getDay();
		day = days[day];

	    if (schedule_type == 'entire_day') {
	        return true;
	    } else if (!selectedDays.hasOwnProperty(day)) {
	        return false;
	    } else if (selectedDays[day] != "on") {
	        return false;
	    }
	    return true;
	}

	function addListenersForTimer()
	{
		if (jQuery('#wdmws_start_timer').is(':visible')) {
			jQuery("#wdmws_start_timer").TimeCircles({count_past_zero: false}).addListener(variationCountdownComplete);
		}		
		if (jQuery('#wdmws_end_timer').is(':visible')) {
			jQuery("#wdmws_end_timer").TimeCircles({count_past_zero: false}).addListener(variationCountdownComplete);
		}
	}
	
	function SendAjaxRequest(variation_id, timerDateTime, product_status)
	{
		jQuery.ajax({
			method: "post",
			url: wdmws_variation_timer_object.admin_ajax,
			data: {
			    'action':'update_variation_availability',
			    'variation_id': variation_id,
			    'product_status': product_status,
			    'cron_date': timerDateTime,
			    'current_system_time': Math.floor(new Date().getTime() / 1000),
			    '_wdmws_timer': wdmws_variation_timer_object.wdmws_timer_nonce,
			},
			success:function( data ) {
				if (data == 'Security Check') {
					alert(data);
					return;
				}
				// if (data == "no") {
				// 	alert("The Add to cart button will become active only when the timer on the server reaches the set sale time.");
				// }
			}
		});
	}

	function variationCountdownComplete(unit, value, total){
		if (total == 2) {
			if (addToCartFlag) {
				SendAjaxRequest(selectedVariation.variation_id, jQuery('#wdmws_end_timer').data('date'), 'no');
			} else {
				SendAjaxRequest(selectedVariation.variation_id, jQuery('#wdmws_start_timer').data('date'),'yes');
			}
		}
		
		if(total<=0){
			if (addToCartFlag) {
				jQuery('#display_end_timer p').hide();
				jQuery(this).fadeOut('slow').replaceWith('<p class = "wdm_message">'+wdmws_variation_timer_object.wdmws_expiration_message+'</p>').css({'color': 'red'});
				// location.reload(true);
				jQuery('div.summary.entry-summary > form > div > div.woocommerce-variation-add-to-cart.variations_button').removeClass('woocommerce-variation-add-to-cart-enabled');
				jQuery('div.summary.entry-summary > form > div > div.woocommerce-variation-add-to-cart.variations_button').addClass('woocommerce-variation-add-to-cart-disabled');
				jQuery('div.summary.entry-summary > form > div > div.woocommerce-variation-add-to-cart.variations_button > button').addClass('disabled');

				changeCurrentProductUrl(attributes);
				// window.location.href = "http://local.wordpress.dev/product/auto-draft-8/?attribute_pa_color=blue&attribute_pa_kuchaur=dimagkharab";
				// jQuery(".single_variation_wrap").trigger( "show_variation", selectedVariation, false);
				// jQuery('div.summary.entry-summary > form > div > div.woocommerce-variation-add-to-cart.variations_button').hide();
			} else {
				// jQuery(".single_variation_wrap").trigger( "show_variation", selectedVariation, false);

				jQuery('#display_start_timer p').hide();
				jQuery('p.wdm_message').hide();
				jQuery(this).fadeOut('slow');
				// location.reload(true);
				jQuery('div.summary.entry-summary > form > div > div.woocommerce-variation-add-to-cart.variations_button').removeClass('woocommerce-variation-add-to-cart-disabled');
				jQuery('div.summary.entry-summary > form > div > div.woocommerce-variation-add-to-cart.variations_button').addClass('woocommerce-variation-add-to-cart-enabled');
				jQuery('div.summary.entry-summary > form > div > div.woocommerce-variation-add-to-cart.variations_button > button').removeClass('disabled');
				jQuery('div.summary.entry-summary > form > div > div.woocommerce-variation-add-to-cart.variations_button').show();
				changeCurrentProductUrl(attributes);
				// window.location.href = "http://local.wordpress.dev/product/auto-draft-8/?attribute_pa_color=blue&attribute_pa_kuchaur=dimagkharab";
				//div.summary.entry-summary > form > div > div.woocommerce-variation-add-to-cart.variations_button.woocommerce-variation-add-to-cart-enabled
			}

		}
	}
function format_time(date_obj) {
  // formats a javascript Date object into a 12h AM/PM time string
  var hour = date_obj.getHours();
  var minute = date_obj.getMinutes();
  var amPM = (hour > 11) ? "pm" : "am";
  if(hour > 12) {
    hour -= 12;
  } else if(hour == 0) {
    hour = "12";
  }
  if(minute < 10) {
    minute = "0" + minute;
  }
  return hour + ":" + minute + amPM;
}

	function wdmwsShowStartTimer(wdmBeginDate, enableStartTimer, scheduleType)
	{
		if (wdmBeginDate == null) {
			return;
		}
		var beginDateTime = new Date(wdmBeginDate).toString();
		// beginTime = beginTime.setTime(beginTime.getTime() + (12*60*60*1000));
		var currTime = new Date().getTime();
		if (wdmBeginDate < currTime) {
			jQuery('p.wdm_message').hide();
			jQuery('div.summary.entry-summary > form > div > div.woocommerce-variation-add-to-cart.variations_button').removeClass('woocommerce-variation-add-to-cart-disabled');
			jQuery('div.summary.entry-summary > form > div > div.woocommerce-variation-add-to-cart.variations_button').addClass('woocommerce-variation-add-to-cart-enabled');
			jQuery('div.summary.entry-summary > form > div > div.woocommerce-variation-add-to-cart.variations_button > button').removeClass('disabled');
			jQuery('div.summary.entry-summary > form > div > div.woocommerce-variation-add-to-cart.variations_button > button').removeClass('wc-variation-selection-needed');
			jQuery('div.summary.entry-summary > form > div > div.woocommerce-variation-add-to-cart.variations_button').show();
			return;
		}
		

		jQuery('#wdmws_end_timer').hide();
		if (scheduleType=="productLaunch") {
			jQuery('#display_start_timer p.start').hide();
			jQuery('#display_start_timer p.launch').show();
		} else {
			jQuery('#display_start_timer p.start').show();
			jQuery('#display_start_timer p.launch').hide();
		}
		
		if (enableStartTimer) {
			jQuery('#wdmws_start_timer').data('date', beginDateTime);
			jQuery('#wdmws_start_timer').TimeCircles({
			    "animation": "ticks",
			    "bg_width": 1,
			    "fg_width": 0.04,
			    "count_past_zero": false,
			    "circle_bg_color": wdmws_variation_timer_object.wdmws_background_color,
			    "time": {
			        "Days": {
			            "text": wdmws_variation_timer_object.timerFieldTexts["days"],
			            "color": wdmws_variation_timer_object.wdmws_front_color,
			            "show": true
			        },
			        "Hours": {
			            "text": wdmws_variation_timer_object.timerFieldTexts["hours"],
			            "color": wdmws_variation_timer_object.wdmws_front_color,
			            "show": true
			        },
			        "Minutes": {
			            "text": wdmws_variation_timer_object.timerFieldTexts["minutes"],
			            "color": wdmws_variation_timer_object.wdmws_front_color,
			            "show": true
			        },
			        "Seconds": {
			            "text": wdmws_variation_timer_object.timerFieldTexts["seconds"],
			            "color": wdmws_variation_timer_object.wdmws_front_color,
			            "show": true
			        }
			    }
			});
			addListenersForTimer();
		}
	}

	function wdmwsShowEndTimer(wdmFinishDate, enableEndTimer)
	{
		if (wdmFinishDate == null) {
			return;
		}

		var finishDateTime = new Date(wdmFinishDate).toString();
		// finishTime = finishTime.setTime(finishTime.getTime() + (12*60*60*1000));
		var currTime = new Date().getTime();

		if (wdmFinishDate < currTime) {
			jQuery('p.wdm_message').hide();
			return;
		}

		jQuery('#wdmws_start_timer').hide();
		if (enableEndTimer) {
			jQuery('#display_end_timer p').show();
			jQuery('#display_start_timer p').hide();
			jQuery('#wdmws_end_timer').data('date', finishDateTime);
			jQuery('#wdmws_end_timer').TimeCircles({
			    "animation": "ticks",
			    "bg_width": 1,
			    "fg_width": 0.04,
			    "count_past_zero": false,
			    "circle_bg_color": wdmws_variation_timer_object.wdmws_background_color,
			    "time": {
			        "Days": {
			            "text": wdmws_variation_timer_object.timerFieldTexts["days"],
			            "color": wdmws_variation_timer_object.wdmws_front_color,
			            "show": true
			        },
			        "Hours": {
			            "text": wdmws_variation_timer_object.timerFieldTexts["hours"],
			            "color": wdmws_variation_timer_object.wdmws_front_color,
			            "show": true
			        },
			        "Minutes": {
			            "text": wdmws_variation_timer_object.timerFieldTexts["minutes"],
			            "color": wdmws_variation_timer_object.wdmws_front_color,
			            "show": true
			        },
			        "Seconds": {
			            "text": wdmws_variation_timer_object.timerFieldTexts["seconds"],
			            "color": wdmws_variation_timer_object.wdmws_front_color,
			            "show": true
			        }
			    }
			});
			addListenersForTimer();
		}
	}
});
