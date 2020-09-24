(function( $ ) {
	//'use strict';

	/**
	 * All of the code for your admin-specific JavaScript source
	 * should reside in this file.
	 *
	 * Note that this assume you're going to use jQuery, so it prepares
	 * the $ function reference to be used within the scope of this
	 * function.
	 *
	 * From here, you're able to define handlers for when the DOM is
	 * ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * Or when the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and so on.
	 *
	 * Remember that ideally, we should not attach any more than a single DOM-ready or window-load handler
	 * for any particular page. Though other scripts in WordPress core, other plugins, and other themes may
	 * be doing this, we should try to minimize doing that in our own work.
	 */
	
	$(function() {
		var days_selection_type = '';

	     /**
	     * creates ajax request to display Selection list
	     */
	     
	    $('#woo_schedule_selection_type').change( function( ){
	    	$('.wdm_selections').remove(); // empty the response
			jQuery('.wdm_selection_details').empty(); //empty the container
		
			if ($('#woo_schedule_selection_type').val() == -1) {
				return;
			} else {
				//display loading animation
	            $( '#woo_schedule_selection_type' ).parent().after( '<img src="' + woo_single_view_obj.loading_image_path + '" alt="' + woo_single_view_obj.loading_text + '" id="loading">' );
					
				$.ajax({
					method: "post",
					url: woo_single_view_obj.admin_ajax_path,
					data: {
					    'action':'handle_selection_type',
	                                    'selection' : jQuery('#woo_schedule_selection_type').val()
					},
					success: function( data ) {
						$('#loading').remove(); //remove loading
						$('.wdm_selection_row').append(data); // Append data to Parent row
					}
				});
			}
	    });
	    
	    /*
	     * Display settings for selected selection
	     */	    
	    
	   $("body").delegate("#woo_schedule_display_selection", "click", function(){
			jQuery('.wdm_selection_details').empty(); //empty the container
			jQuery('#message').remove();
			if (jQuery('#woo_schedule_selections').val() != null && jQuery('#woo_schedule_selections').val() != -1) {
				//display loading animation
				$( '<img src="' + woo_single_view_obj.loading_image_path + '" alt="' + woo_single_view_obj.loading_text + '" id="loading" class="loading-left">' ).insertAfter('#woo_schedule_display_selection' );	
				$.ajax({
					method: "post",
					url: woo_single_view_obj.admin_ajax_path,
					data: {
					    'action':'display_scheduler_fields',
	                    'selections_id' : $('#woo_schedule_selections').val(),
					    'selection_type' : $('#woo_schedule_selections').attr('selection_type')
					},
					success:function( data ) {
	                    $( '#loading' ).remove(); //remove loading
						jQuery('.wdm_selection_details').append(data);
						jQuery('.scheduleTable').DataTable({
							"dom": '<"top"<"col-md-12 col-lg-6 search-filter"f><"col-md-12 col-lg-6 count-entry"li>>rt<"bottom"p>',
							 "oLanguage": {
							      "sLengthMenu": "Show_MENU_ ",
							      "sSearch": "",
							      "sInfo": "_START_ to _END_ of _TOTAL_ entries",
							      "oPaginate": {
							      		"sNext": "<span class='dashicons dashicons-arrow-right-alt2'></span>",
        								"sPrevious": "<span class='dashicons dashicons-arrow-left-alt2'></span>",
      								}
							    },
							"columnDefs": [
							  {"className": "dt-center", "targets": "_all"},
							  {"targets": 0, "orderable": true},
							  {"targets": '_all', "orderable": false}

							]});

					    updateExpirationType();
					    if (days_selection_type == "per_day"){
					        mindate = moment(new Date()).format('MM/DD/YYYY');
					    } else{
					        mindate = moment(new Date());
					    }
					    
					    jQuery('#wdm_start_date').bsDatetimepicker({
					        minDate: mindate,//moment().format('MM/DD/YYYY'),
					        format : 'MM/DD/YYYY, LT',
					        defaultDate : moment(new Date()),
					        ignoreReadonly : true,
					        showClear : true
					    });

					    jQuery('#wdm_end_date').bsDatetimepicker({
					        minDate: mindate,//moment().format('MM/DD/YYYY'),
					        format : 'MM/DD/YYYY, LT',
					        ignoreReadonly : true,
					        showClear : true,
					        useCurrent: true //Important! See issue #1075
					    });

					    //revise 'End date' --- minimum date after change in Start date

					    jQuery("#wdm_start_date").on("dp.change", function (e) {
					        jQuery('#wdm_end_date').data("DateTimePicker").minDate(e.date);
					    });
					    
					    if (jQuery('#wdm_start_date').length > 0) {
					        jQuery('#wdm_start_date').data("DateTimePicker").date(null);
					        jQuery('#wdm_end_date').data("DateTimePicker").date(null);
					    }
					}
				});
			} else {
				selection_type=jQuery('#woo_schedule_selections').attr('selection_type');
				if(jQuery('#message').length == 0){
					jQuery('.wdm-woo-scheduler-settings').prepend('<div id="message" class="error"><p>' + woo_single_view_obj.please_select_msg + ' ' + selection_type + ' </p></div>')
				}
			}
	    });


		$("body").delegate("#woo_schedule_edit_selection", "click", function(){
			jQuery('.wdm_selection_details').empty(); //empty the container
			jQuery('#message').remove();
			if (jQuery('#woo_schedule_selections').val() != null && jQuery('#woo_schedule_selections').val() != -1) {
				//display loading animation
				$( '<img src="' + woo_single_view_obj.loading_image_path + '" alt="' + woo_single_view_obj.loading_text + '" id="loading" class="loading-left">' ).insertAfter('#woo_schedule_display_selection' );	
				$.ajax({
					method: "post",
					url: woo_single_view_obj.admin_ajax_path,
					data: {
					    'action':'edit_scheduler_fields',
	                                    'selections_id' : $('#woo_schedule_selections').val(),
					    'selection_type' : $('#woo_schedule_selections').attr('selection_type')
					},
					success:function( data ) {
						$( '#loading' ).remove(); //remove loading
						jQuery('.wdm_selection_details').append(data);
						jQuery('input.wdmWeekDayCheckBoxes[checked]').parent().addClass("wdmws-color-checked"); 
					}
				});
			} else {
				selection_type=jQuery('#woo_schedule_selections').attr('selection_type');
				if(jQuery('#message').length == 0){
					jQuery('.wdm-woo-scheduler-settings').prepend('<div id="message" class="error"><p>' + woo_single_view_obj.please_select_msg + ' ' + selection_type + ' </p></div>')
				}
			}
	    });


        $("body").delegate('[name^="days_of_week"]', 'change', function() {
            if (jQuery(this).is(":checked")) {
                var $this = jQuery(this);

            	if (jQuery('#wdm_start_date').val() == "") {
            		alert("No date selected. Please select the date then select the days.");
                	jQuery($this).attr('checked', false);
            		return;
            	}

                var selectedDay = jQuery(this).attr('day_of_week');
                var from = new Date(jQuery('#wdm_start_date').val());
                var to = new Date(jQuery('#wdm_end_date').val());

                if (!wdmwscheckValidDay(selectedDay, from, to)) {
                	alert("Day selected does not lie between the date selected");
                	jQuery($this).attr('checked', false);
                }
            }
        });

		function updateExpirationType()
		{
			$.ajax({
				method: "post",
				url: woo_single_view_obj.admin_ajax_path,
				data: {
				    'action':'get_expiration_type'
				},
				success:function( data ) {
					if (data != null) {
						days_selection_type = data;
					}
				}
			});
	    }


	    /*
		 * validations are performed on the front end and,
		 * the schedule data sent by ajax to schedule selected products/categories.
	     */
	    $("body").delegate("#woo_single_view_update_details","click",function(event){
			scheduleType=getFormScheduleType();
			var scheduleData={};
			switch (scheduleType) {
				case "productLaunch":
					scheduleData.type			=scheduleType;
					scheduleData.startDate		=jQuery("#wdmLaunchDate").val();
					scheduleData.startTime		=jQuery("#wdmLaunchTime").val();
					scheduleData.startTimer		=jQuery("#startTimer").attr("checked")=="checked"?true:false;
					scheduleData.hideUnavailable=jQuery("#wdmHideUnavailabe").attr("checked")=="checked"?true:false;
					break;
				case "wholeDay":
					scheduleData.type			=scheduleType;
					scheduleData.startDate		=jQuery("#wdmWdStartDate").val();
					scheduleData.startTime		=jQuery("#wdmWdStartTime").val();
					scheduleData.endDate		=jQuery("#wdmWdEndDate").val();
					scheduleData.endTime		=jQuery("#wdmWdEndTime").val();
					scheduleData.skipStartDate	=jQuery("#wdmWdSkipStartDate").val();
					scheduleData.skipEndDate	=jQuery("#wdmWdSkipEndDate").val();
					scheduleData.daysSelected	={};
					scheduleData.daysSelected	=wdmGetDaysSelected('wholeDay');
					scheduleData.startTimer		=jQuery("#startTimer").attr("checked")=="checked"?true:false;
					scheduleData.endTimer		=jQuery("#wdmEndTimer").attr("checked")=="checked"?true:false;
					scheduleData.hideUnavailable=jQuery("#wdmHideUnavailabe").attr("checked")=="checked"?true:false;
					break;
				case "specificTime":
					scheduleData.type			=scheduleType;
					scheduleData.startDate		=jQuery("#wdmLtStartDate").val();
					scheduleData.startTime		=jQuery("#wdmLtStartTime").val();
					scheduleData.endDate		=jQuery("#wdmLtEndDate").val();
					scheduleData.endTime		=jQuery("#wdmLtEndTime").val();
					scheduleData.skipStartDate  =jQuery("#wdmLtSkipStartDate").val();
					scheduleData.skipEndDate	=jQuery("#wdmLtSkipEndDate").val();
					scheduleData.daysSelected	={};
					scheduleData.daysSelected	=wdmGetDaysSelected('specificTime');
					scheduleData.startTimer		=jQuery("#startTimer").attr("checked")=="checked"?true:false;
					scheduleData.endTimer		=jQuery("#wdmEndTimer").attr("checked")=="checked"?true:false;
					scheduleData.hideUnavailable=jQuery("#wdmHideUnavailabe").attr("checked")=="checked"?true:false;
					break;			
				default:
					return 0;
			}
			//send ajax request for schedule with list of selected product/category
			$('#woo_single_view_update_details').parent().find('p.wdm-message').remove();

				var confMsg = "";
				if (jQuery('#woo_schedule_selection_type').val() == "category") {
					confMsg = woo_single_view_obj.confirm_cat_save_msg;
				}
				if (jQuery('#woo_schedule_selection_type').val() == "product") {
					confMsg = woo_single_view_obj.confirm_product_save_msg;
				}
				var r = confirm(confMsg);
				var d = true;

				if (scheduleType!="productLaunch" && jQuery.isEmptyObject(scheduleData.daysSelected)) {
					d = confirm("You have not selected the weekdays. Click cancel to select. If days are not selected the products will not be visible");
				}

				// if(r == true && d == true && 'enable' == wdmws_send_email_confirmation.wdmws_notify_enabled) {
				// 	event.preventDefault();
				// 	r = false;
				// 	wdmws_send_email_now_confirmation();
				// }

			  	if (r == true && d == true) {
					$( '<img src="' + woo_single_view_obj.loading_image_path + '" alt="' + woo_single_view_obj.loading_text + '" id="loading" class="loading-left">' ).insertBefore('#wdm-schedule-result');	
					jQuery('#wdm-schedule-result').find("p.wdm-message").text("");
					jQuery('#wdm-schedule-result').find("p.wdm-message").removeClass("bg-success bg-danger");
					$.ajax({
						method: "post",
						url: woo_single_view_obj.admin_ajax_path,
						data: {
						    'action'			:'update_expiration_details',
						    'selection_type' 	: $('#woo_schedule_selections').attr('selection_type'),
						    'selections_id'		: $('#woo_schedule_selections').val(),
							'scheduleData'		: scheduleData,
						},
						success:function( data ) {
							$( '#loading' ).remove();
							if (data=="Details Updated") {
								jQuery('#wdm-schedule-result').find("p.wdm-message").text(data);
								jQuery('#wdm-schedule-result').find("p.wdm-message").removeClass("bg-danger").addClass("bg-success");
								data = data.replace('details updated','');
							}
							else {
								jQuery('#wdm-schedule-result').find("p.wdm-message").text(data);
								jQuery('#wdm-schedule-result').find("p.wdm-message").removeClass("bg-success").addClass("bg-danger");
								data = data.replace('details updated','');
							}
						}
					});
				}
	    });
	});
	/*
	 * Delete Product data
	 */
	$(document).delegate(".wdm_delete_product","click",function(){
		var r = confirm(woo_single_view_obj.delete_confirm_msg);

		var ref = $(this);

		if (r == true) {
		   $.ajax({
			method: "post",
			url: woo_single_view_obj.admin_ajax_path,
			data: {
			    'action':'remove_product_details',
			    'product_id' : $(this).attr('product_id')
			},
			success:function( data ) {
				$('#loading').remove(); //remove loading
				
				if (data.length) {
					
					$(ref).append(data);
				}
				else
				{
					$(ref).parents('tr').remove();
				}
				return false;
			}
		  });
		}

		return false;
	});
	 
	 /*
	 * Delete Product data
	 */
	
	 $(document).delegate(".wdm_delete_term","click",function(){		
		var r = confirm(woo_single_view_obj.delete_confirm_msg);
		
		var ref = $(this);
		
		if (r == true) {
		   $.ajax({
			method: "post",
			url: woo_single_view_obj.admin_ajax_path,
			data: {
			    'action':'remove_term_details',
			    'term_id' : $(this).attr('term_id')
			},
			success:function( data ) {
				$('#loading').remove(); //remove loading
				$(ref).parents('tr').remove();
				return false;
			}
		  });
		}
		
		return false;
	 });

})( jQuery );

function wdmws_send_product_scheduling_request()
{
	scheduleType=getFormScheduleType();
			var scheduleData={};
			switch (scheduleType) {
				case "productLaunch":
					scheduleData.type			=scheduleType;
					scheduleData.startDate		=jQuery("#wdmLaunchDate").val();
					scheduleData.startTime		=jQuery("#wdmLaunchTime").val();
					scheduleData.startTimer		=jQuery("#startTimer").attr("checked")=="checked"?true:false;
					scheduleData.hideUnavailable=jQuery("#wdmHideUnavailabe").attr("checked")=="checked"?true:false;
					break;
				case "wholeDay":
					scheduleData.type			=scheduleType;
					scheduleData.startDate		=jQuery("#wdmWdStartDate").val();
					scheduleData.startTime		=jQuery("#wdmWdStartTime").val();
					scheduleData.endDate		=jQuery("#wdmWdEndDate").val();
					scheduleData.endTime		=jQuery("#wdmWdEndTime").val();
					scheduleData.skipStart		=jQuery("#wdmWdSkipStartDate").val();
					scheduleData.skipEnd		=jQuery("#wdmWdSkipEndDate").val();
					scheduleData.daysSelected	={};
					scheduleData.daysSelected	=wdmGetDaysSelected('wholeDay');
					scheduleData.startTimer		=jQuery("#startTimer").attr("checked")=="checked"?true:false;
					scheduleData.endTimer		=jQuery("#wdmEndTimer").attr("checked")=="checked"?true:false;
					scheduleData.hideUnavailable=jQuery("#wdmHideUnavailabe").attr("checked")=="checked"?true:false;
					break;
				case "specificTime":
					scheduleData.type			=scheduleType;
					scheduleData.startDate		=jQuery("#wdmLtStartDate").val();
					scheduleData.startTime		=jQuery("#wdmLtStartTime").val();
					scheduleData.endDate		=jQuery("#wdmLtEndDate").val();
					scheduleData.endTime		=jQuery("#wdmLtEndTime").val();
					scheduleData.skipStart		=jQuery("#wdmLtSkipStartDate").val();
					scheduleData.skipEnd		=jQuery("#wdmLtSkipEndDate").val();
					scheduleData.daysSelected	={};
					scheduleData.daysSelected	=wdmGetDaysSelected('specificTime');
					scheduleData.startTimer		=jQuery("#startTimer").attr("checked")=="checked"?true:false;
					scheduleData.endTimer		=jQuery("#wdmEndTimer").attr("checked")=="checked"?true:false;
					scheduleData.hideUnavailable=jQuery("#wdmHideUnavailabe").attr("checked")=="checked"?true:false;
					break;			
				default:
					return 0;
			}

	$( '<img src="' + woo_single_view_obj.loading_image_path + '" alt="' + woo_single_view_obj.loading_text + '" id="loading" class="loading-left">' ).insertAfter('#woo_single_view_update_details' );	
	jQuery('#wdm-schedule-result').find("p.wdm-message").text("");
	jQuery('#wdm-schedule-result').find("p.wdm-message").removeClass("bg-success bg-danger");
					
	$.ajax({
		method: "post",
		url: woo_single_view_obj.admin_ajax_path,
		data: {
		'action'			:'update_expiration_details',
		'selection_type' 	: $('#woo_schedule_selections').attr('selection_type'),
		'selections_id'		: $('#woo_schedule_selections').val(),
		'scheduleData'		: scheduleData,
			
		},
		success:function( data ) {
			$( '#loading' ).remove();
			if (data=="Details Updated") {
				jQuery('#wdm-schedule-result').find("p.wdm-message").text(data);
				jQuery('#wdm-schedule-result').find("p.wdm-message").removeClass("bg-danger").addClass("bg-success");
				data = data.replace('details updated','');
			}
			else {
				jQuery('#wdm-schedule-result').find("p.wdm-message").text(data);
				jQuery('#wdm-schedule-result').find("p.wdm-message").removeClass("bg-success").addClass("bg-danger");
				data = data.replace('details updated','');
			}
		}
	});
}

function wdmwscheckValidDay(selectedDay, from, to) {
	var DAYS = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
	var ValidDays = [];
	var InvalidDays = true;
	var d = from;

	while (d <= to) {
		if (selectedDay == DAYS[d.getDay()]) {
			InvalidDays = false;
			break;
		}

		d = new Date(d.getTime() + (24 * 60 * 60 * 1000));
	}        

	if (InvalidDays == true) {
		return false;
	}

	return true;
}

		function getFormScheduleType()
        {
            let type=jQuery("div.wisdmScheduler").find('input[wdm-radio-group="ScheduleType"]:checked').val();
            if (type=="scheduleDuration") {
                type=jQuery("div.wisdmScheduler").find('input[wdm-radio-group="AvailabilityType"]:checked').val();
            }
            return type;
		}
		

		function wdmGetDaysSelected(selectionType)
		{
			let name;
			name ="wdmLtWeekdays";
			if (selectionType=="wholeDay") {
			 name="wdmWdWeekdays";
			}

			let checkedDaysHtml=jQuery('input[name^='+name+']:checked');
		
			
			let checkedDays={};
			checkedDaysHtml.each(function(i,elem){
				checkedDays[elem.getAttribute('data-week-day')]="on";		
			});
			return checkedDays;
		}
