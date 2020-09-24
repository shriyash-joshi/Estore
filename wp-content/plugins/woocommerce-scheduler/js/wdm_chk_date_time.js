jQuery(document).ready(function ($) {

        if (jQuery('div.wdm_ws_publis, div.wdm_ws_stock, div.wdm_ws_outstock').is(':visible')) {
            jQuery("html, body").animate({
                scrollTop: jQuery('#message').offset().top - 50
            }, "slow");           
        }

        jQuery('#_stock_status').change(function() {
            if (jQuery(this).val() == 'outofstock') {
                jQuery('div.wdm_ws_stock').show();
                jQuery("html, body").animate({
                    scrollTop: jQuery('#message').offset().top - 50
                }, "slow"); 
            } else if (jQuery(this).val() == 'instock') {
                jQuery('div.wdm_ws_outstock').hide();
                jQuery('div.wdm_ws_stock').hide();
            }
        });

        jQuery('#variable_product_options').delegate('[id^="variable_stock_status"]', 'change', function () {
            if (jQuery(this).val() == 'outofstock') {
                jQuery('div.wdm_ws_stock').show();
                jQuery("html, body").animate({
                    scrollTop: jQuery('#message').offset().top - 50
                }, "slow"); 
            } else if (jQuery(this).val() == 'instock') {
                jQuery('div.wdm_ws_outstock').hide();
                jQuery('div.wdm_ws_stock').hide();
            }            
        });

        $('.expand_all').click(function(){
          scheduler_assign_date_time_picker();
        });
        
        jQuery(document).on('change', '.wdm_show_timer', function(){
            if (jQuery(this).val() == 0)
            {
                jQuery(this).val(1);
            } else if (jQuery(this).val() == 1)
            {
                jQuery(this).val(0);
            }
        });

        $(document).on('change', '[name^="days_of_week"]', function(){
            if (jQuery(this).is(":checked")) {
                var $this = jQuery(this);
                var selectedDay = jQuery(this).next().html();

                var start_date = jQuery(this).parent().siblings('.wdm-start-date-end-data-cont').find('.wdm_start_date').val();
                var end_date = jQuery(this).parent().siblings('.wdm-start-date-end-data-cont').find('.wdm_end_date').val();
                var from = new Date(start_date);
                var to = new Date(end_date);

                if (!checkValidDay(selectedDay, from, to)) {
                    alert("Day selected does not lie between the date selected");
                    jQuery($this).attr('checked', false);
                }
            }
        });


        $('body').on('click', '.woocommerce_variation.wc-metabox', function() {
            var start_date = $(this).find('.wdm_start_date');
            var end_date = $(this).find('.wdm_end_date');
            var variant = $(this);
            scheduler_assign_date_time_picker();
            start_date.on("dp.change",function (e) {
            $('.save-variation-changes').removeAttr('disabled');
            $('.cancel-variation-changes').removeAttr('disabled');
            variant.addClass('variation-needs-update');
            });

            var start_time = $(this).find('.wdm_start_time');
            var end_time = $(this).find('.wdm_end_time');

             start_time.on("dp.change",function (e) {
            $('.save-variation-changes').removeAttr('disabled');
            $('.cancel-variation-changes').removeAttr('disabled');
            variant.addClass('variation-needs-update');
            });

            end_date.on("dp.change",function (e) {
            $('.save-variation-changes').removeAttr('disabled');
            $('.cancel-variation-changes').removeAttr('disabled');
            variant.addClass('variation-needs-update');
            });

            end_time.on("dp.change",function (e) {
            $('.save-variation-changes').removeAttr('disabled');
            $('.cancel-variation-changes').removeAttr('disabled');
            variant.addClass('variation-needs-update');
            });

        });
        
        if ($(".wdm_simple_schedule")[0])
        {
            var start_date = $('.wdm_simple_schedule').find('.wdm_start_date');
            var end_date = $('.wdm_simple_schedule').find('.wdm_end_date');
        
            start_date.bsDatetimepicker({
            format : 'MM/DD/YYYY',
            showClear : true,
            useCurrent: true //Important! See issue #1075
            });
            end_date.bsDatetimepicker({
            format : 'MM/DD/YYYY',
            showClear : true,
            useCurrent: true //Important! See issue #1075
            });
            
            var start_time = $('.wdm_simple_schedule').find('.wdm_start_time');
            var end_time = $('.wdm_simple_schedule').find('.wdm_end_time');

            start_time.bsDatetimepicker({
            format: 'LT',
            showClear : true,
            useCurrent: true //Important! See issue #1075
            });
            end_time.bsDatetimepicker({
            format: 'LT',
            showClear : true,
            useCurrent: true //Important! See issue #1075
            });       
        }
       
       function scheduler_assign_date_time_picker(){
        jQuery('.wdm_start_date').bsDatetimepicker({
            format : 'MM/DD/YYYY',
            showClear : true,
            useCurrent: true //Important! See issue #1075
            });
            jQuery('.wdm_end_date').bsDatetimepicker({
            format : 'MM/DD/YYYY',
            showClear : true,
            useCurrent: true //Important! See issue #1075
            });


            jQuery('.wdm_start_time').bsDatetimepicker({
            format: 'LT',
            showClear : true,
            useCurrent: true //Important! See issue #1075
            });
            jQuery('.wdm_end_time').bsDatetimepicker({
            format: 'LT',
            showClear : true,
            useCurrent: true //Important! See issue #1075
            });
       }

        function checkValidDay(selectedDay, from, to) {
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



       jQuery('#publish , .save-variation-changes').click(function (event){
            var current_date = new Date();
            if (jQuery(".wdm_simple_schedule")[0]){
                var start_date = jQuery('.wdm_simple_schedule').find('.wdm_start_date').val();
                var end_date = jQuery('.wdm_simple_schedule').find('.wdm_end_date').val();

                var start_time = jQuery('.wdm_simple_schedule').find('.wdm_start_time').val();
                var end_time = jQuery('.wdm_simple_schedule').find('.wdm_end_time').val();

                var from = new Date(start_date);
                var to = new Date(end_date);
                var checked = [];

                var is_start_end_time_empty = (null == start_time || '' == start_time || null == end_time || '' == end_time) ? true : false;
                
                if (null != start_date && '' != start_date && null != end_date && '' != end_date && is_start_end_time_empty) {
                    alert(scheduler_option.err_start_end_time_empty);
                    event.preventDefault();
                    return false;
                }

                if(scheduler_option.option == 'per_day') {
                    var startTime = start_date + "," + start_time;;
                    var startTime = new Date(startTime).getTime();
                    var endTime = start_date + "," + end_time;
                    var endTime = new Date(endTime).getTime();

                    if (startTime > endTime) {
                        alert("The schedule time set is invalid for per day settings.");
                        return false;
                    }

                    jQuery('[name^="days_of_week"]:checked').each(function() {
                        var selectedDay = jQuery(this).next().html();
                        if (!checkValidDay(selectedDay, from, to)) {
                            alert(scheduler_option.err_selection + selectedDay + scheduler_option.err_not_btwn);
                            jQuery(this).attr('checked', false);
                        } else {
                            checked.push((jQuery(this).attr('day_of_week')));
                        }
                    });
                    var selectionEmpty = true;
                    if (jQuery.isEmptyObject(checked) && start_date != "") {
                        selectionEmpty = confirm(scheduler_option.empty_selection);
                    }
                    if (!selectionEmpty) {
                        return false;
                    }
                }
                start_val = new Date(start_date).getTime();
                end_val = new Date(end_date).getTime();

                var startDateTime = start_date+ ' ' +start_time;
                startDateTime = new Date(startDateTime).getTime();
                var endDateTime = end_date + ' ' + end_time;
                endDateTime = new Date(endDateTime).getTime();

                if(end_val < start_val){
                    alert(scheduler_option.err_start_date_limit);
                        return false;
                }
                else if(start_val == end_val ){
                	if(endDateTime < startDateTime){
                        alert(scheduler_option.err_start_time_limit + start_val + end_val);
                        return false;
                    }
                }
                if(scheduler_option.option == 'per_day'){
                    if(endDateTime < startDateTime){
                        alert(scheduler_option.err_start_time_limit);
                        return false;
                    }
       			}
            } else {
                flag=0;
                selectedDayFlag = 0;
                var variationids = [];
                jQuery('.wdm_start_date').each(function(){
                    var start_date =jQuery(this).val();
                    var end_date =jQuery(this).parent().siblings().find('.wdm_end_date').val();
                    var start_time =jQuery(this).parent().parent().siblings().find('.wdm_start_time').val();
                    var end_time =jQuery(this).parent().parent().siblings().find('.wdm_end_time').val();
                    var variation_id = jQuery(this).parent().parent().siblings().find('.wdm_end_time').data('variation_id');
                    
                    var from = new Date(start_date);
                    var to = new Date(end_date);

                    var is_start_end_time_empty = (null == start_time || '' == start_time || null == end_time || '' == end_time) ? true : false;
                
                    if (null != start_date && '' != start_date && null != end_date && '' != end_date && is_start_end_time_empty) {
                        flag = 4;
                    }

                    if(scheduler_option.option == 'per_day') {
                        jQuery('[name^="days_of_week['+variation_id+']"]:checked').each(function(){
                            var selectedDay = jQuery(this).next().html();
                            if (!checkValidDay(selectedDay, from, to)) {
                                if (!inArray(variation_id, variationids)) {
                                    variationids.push(variation_id);
                                }
                                selectedDayFlag = 1;
                                jQuery(this).attr('checked', false);
                            }
                        });
                    }

                    var startDateTime = start_date+ ' ' +start_time;
                    startDateTime = new Date(startDateTime).getTime();
                    var endDateTime = end_date + ' ' + end_time;
                    endDateTime = new Date(endDateTime).getTime();
                    start_val = new Date(start_date).getTime();
                    end_val = new Date(end_date).getTime();
                    if(end_val < start_val){
                        flag=1;
                    }
                    else if(start_val == end_val ){
                    	if(endDateTime < startDateTime){
                            flag = 2;
                            return false;
                        }
                    }
                    if(scheduler_option.option == 'per_day'){
                        if(endDateTime < startDateTime){
                            flag=2;
                        }
                        var startTime = start_date + "," + start_time;;
                        var startTime = new Date(startTime).getTime();
                        var endTime = start_date + "," + end_time;
                        var endTime = new Date(endTime).getTime();

                        if (startTime > endTime) {
                            flag = 3;
                        }
                    }
                    else{

                    }
                });
                if (selectedDayFlag == 1) {
                    var varitions = variationids.join();
                    alert(scheduler_option.err_day_selected + varitions + scheduler_option.err_not_btwn);
                    return false;
                }
                if (flag == 3) {
                    alert("The schedule time set is invalid for per day settings.");
                    return false;
                }
                if(flag == 1){
                    alert(scheduler_option.err_start_date_limit);
                    return false;
                } else if (flag == 4) {
                    alert(scheduler_option.err_start_end_time_empty);
                    return false;
                }
                else if(flag == 2){
                    alert(scheduler_option.err_start_time_limit);
                    return false;
                }
            }
        });

    function inArray(needle, haystack) {
        var length = haystack.length;
        for(var i = 0; i < length; i++) {
            if(haystack[i] == needle) return true;
        }
        return false;
    }

});
