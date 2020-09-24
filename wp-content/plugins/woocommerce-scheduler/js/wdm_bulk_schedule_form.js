jQuery( document ).ready(function() {

    var backtrackingObject = { 
        productLaunch   : { wdmSingleFormScheduleTimer: "wdmSingleFormScheduleLaunch", wdmSingleFormScheduleLaunch: "wdmSingleFormScheduleType", wdmSingleFormScheduleType: "initial"}, 
        wholeDay        : { wdmSingleFormScheduleTimer: "wdmSingleFormScheduleTypeWholeDay", wdmSingleFormScheduleTypeWholeDay: "wdmSingleFormScheduleAvailability", wdmSingleFormScheduleAvailability: "wdmSingleFormScheduleType", wdmSingleFormScheduleType:"initial"}, 
        specificTime    : { wdmSingleFormScheduleTimer: "wdmSingleFormScheduleSpecificTime", wdmSingleFormScheduleSpecificTime: "wdmSingleFormScheduleAvailability", wdmSingleFormScheduleAvailability: "wdmSingleFormScheduleType", wdmSingleFormScheduleType:"initial" },
        scheduleDuration: { wdmSingleFormScheduleAvailability: "wdmSingleFormScheduleType", wdmSingleFormScheduleType:"initial"}
    }
    jQuery("body").delegate(".wdmNewSchedule", "click", function(){
        
    });

    /**
     * Following block of code is used to select radio buttons
     * on clik of thair parent div element having class name
     * "wdmFormRadioWrapper".
     * This click event clears values of all the radio buttons
     * having same group(name) & set radio button inside the clicked
     * div.
     */
    jQuery( "body" ).delegate( ".wdmFormRadioWrapper", "click", function(e) {
        let target=jQuery(e.target);
        if (target.is("input") || target.is("label"))
            {return;}
        let parent= jQuery(this).closest('.wdm-form-step');
        let elem=jQuery(this).children('label').children('input');
        let groupName=elem.attr('wdm-radio-group');
        jQuery(parent).find('input[wdm-radio-group='+groupName+']').removeAttr("checked");
        elem.attr('checked',true);
        jQuery(elem).closest('.wisdmScheduler').find('.wdmFormNextButton').trigger( "click" );
    });

    /**
     * Following block of code is used to check & Uncheck Checkboxes
     * on clik of thair parent div element having class name
     * "wdmFormCheckboxWrapper".
     * This click event switch the value of check box
     * 
     */
    jQuery( "body" ).delegate( ".wdmFormCheckboxWrapper", "click", function(e) {
        let target=jQuery(e.target);
        if (target.is("input") || target.is('label') || target.is("b") || target.is("p"))
            {return;}
        let elem=jQuery(this).find('input')
        let checked=jQuery(elem).attr('checked');
        if (checked=="checked") {
            jQuery(elem).removeAttr('checked');
        } else {
            jQuery(elem).attr('checked', 'checked')
        }
    });

    /**
     * Manage the days selection. checking all boxes when everyday is selected
     * & unchecking everyday when some box gets unchecked. 
     */
    jQuery( "body" ).delegate( ".wdmWeekDayCheckBoxes", "click", function(e) {
        if (jQuery(this).attr('data-week-day')=="Everyday") {
            if (jQuery(this).prop('checked')==true) {
                jQuery(this).closest('.wdm-days-row').find('input').prop('checked',true);
                jQuery(this).closest('.wdm-days-row').find('input').parent('label').addClass('wdmws-color-checked');
            }
            else{
                jQuery(this).closest('.wdm-days-row').find('input').prop('checked',false);
                jQuery(this).closest('.wdm-days-row').find('input').parent('label').removeClass('wdmws-color-checked');
            }
        } else {
            let everydayElem=jQuery(this).closest('.wdm-days-row').find('input[data-week-day="Everyday"]');
            let checkBoxArray=jQuery(this).closest('.wdm-days-row').find('input');
            let allchecked=0;
            if (jQuery(this).prop('checked')) {
                jQuery(this).parent('label').addClass('wdmws-color-checked');   
            } else {
                jQuery(this).parent('label').removeClass('wdmws-color-checked');
            }
            checkBoxArray.each(function() {
                if (jQuery(this).attr('data-week-day')!="Everyday") {
                    allchecked+=jQuery(this).prop('checked')?1:0;                    
                }
            });
            if (allchecked==7) {
                jQuery(everydayElem).prop('checked',true);
                jQuery(this).closest('.wdm-days-row').find('input[data-week-day="Everyday"]').parent('label').addClass('wdmws-color-checked');
            } else{
                jQuery(everydayElem).prop('checked',false);
                jQuery(this).closest('.wdm-days-row').find('input[data-week-day="Everyday"]').parent('label').removeClass('wdmws-color-checked');
            }
        }
    });



    /**
     * Next Button click
     * get form data & add it to the array 
     * dicide next screen based on the selection  
     */
    jQuery( "body" ).delegate( ".wdmFormNextButton", "click", function() {
        let currentStep=jQuery(this).next('.wdmStepManager').val();
        if (currentStep!="finalStep") {
            //get form data
            let stepData=wdmValidateGetStepData(this);
            let nextStep=stepDecisionMaker(stepData);
            if (nextStep!=-1) {
             //hide current form & show next form
            jQuery(this).closest('.wdm-form-container').find("."+currentStep).hide();
            jQuery(this).closest('.wdm-form-container').find("."+nextStep).show();
            jQuery(this).next('.wdmStepManager').val(nextStep);   
                if (nextStep!="wdmSingleFormScheduleType") {
                    jQuery(this).prev('.wdmFormPreviousButton').removeAttr("disabled");   
                }
                if (nextStep=="wdmSingleFormScheduleTimer") {
                    jQuery(this).addClass('button-set-schedule');
                    jQuery(this).attr('id','woo_single_view_update_details');
                    jQuery(this).text("Set Schedule");
                    //jQuery(this).attr("disabled","disabled");
                }   
            }
        }
    });

    /**
     * Prev Button click
     * remove previosuly added data fron an array 
     * go to previos screen
     */
    jQuery( "body" ).delegate( ".wdmFormPreviousButton", "click", function() {
        //remove previously added data from array
        let currentStep=jQuery(this).next().next('.wdmStepManager').val();
        if (currentStep!="wdmSingleFormScheduleType") {
            let scheduleType=getFormScheduleType(this);
            if (scheduleType!=undefined) {
                jQuery(this).closest(".wdm-form-container").find("."+currentStep).hide();
                currentStep=backtrackingObject[scheduleType][currentStep];
                jQuery(this).next().next('.wdmStepManager').val(currentStep);
                jQuery(this).closest(".wdm-form-container").find("."+currentStep).show();                 
            } else {
                jQuery(this).closest(".wdm-form-container").find("."+currentStep).hide();
                currentStep='wdmSingleFormScheduleType';//first step
                jQuery(this).next().next('.wdmStepManager').val(currentStep);
                jQuery(this).closest(".wdm-form-container").find("."+currentStep).show(); 
            }
        } if (currentStep=="wdmSingleFormScheduleType") {
            jQuery(this).attr("disabled", "disabled");
        }
        if (currentStep!="wdmSingleFormScheduleTimer") {
            jQuery(this).next('.wdmFormNextButton').html("Next");
            jQuery(this).next('.wdmFormNextButton').removeClass('button-set-schedule');
            jQuery(this).next('.wdmFormNextButton').removeAttr("id");
        }
    });

    /**
     * collect the data for which step is written,
     * write that data to hidden scheduler data fields.
     */
    function wdmValidateGetStepData(element)
        {
            let data,stepDiv;
            wdmEnableDateTimePickers();
            let currentStep=jQuery(element).next('.wdmStepManager').val();
            switch (currentStep) {
                case "wdmSingleFormScheduleType":
                    data= jQuery(element).closest('.wdm-form-container').find('input[wdm-radio-group="ScheduleType"]:checked').val();
                    let endTimerCheckbox=jQuery(element).closest('.wdm-form-container').find('.wdmEndTimer')
                    if (data=="productLaunch") {
                        jQuery(endTimerCheckbox).prop('checked',false);
                        jQuery(endTimerCheckbox).closest('.wdmFormCheckboxWrapper').hide();
                    } else {
                        jQuery(endTimerCheckbox).closest('.wdmFormCheckboxWrapper').show();
                    }
                    if (data==undefined || data=="") {
                        return "error";
                    }      
                return data;

                case "wdmSingleFormScheduleAvailability":
                    data= jQuery(element).closest('.wdm-form-container').find('input[wdm-radio-group="AvailabilityType"]:checked').val();
                    if (data==undefined || data=="") {
                        return "error";
                    }      
                return data;

                case "wdmSingleFormScheduleLaunch":
                    let date,time;
                    stepDiv     =jQuery(element).closest('.wdmSingleForm');
                    date        =jQuery(stepDiv).find('.wdmLaunchDate').val();
                    time        =jQuery(stepDiv).find('.wdmLaunchTime').val();
                    if(!wdmValidateDate(date)){
                            alert ("Please enter a valid date");
                            jQuery(stepDiv).find('.wdmLaunchDate').css("border", "1px solid red");
                            jQuery(stepDiv).find('.wdmLaunchDate').click(function(){
                                jQuery(this).css("border", "");
                            });
                            return -1;
                        }
                    if (!wdmValidateTime(time)) {
                        alert ("Please enter a valid time");
                            jQuery(stepDiv).find('.wdmLaunchTime').css("border", "1px solid red");
                            jQuery(stepDiv).find('.wdmLaunchTime').click(function(){
                                jQuery(this).css("border", "");
                            });
                            return -1;
                    }
                    data="gotoFinalStep";
                return data;

                case "wdmSingleFormScheduleTypeWholeDay":
                    let fromDateWd,fromTimeWd,toDateWd,toTimeWd,skipStartDateWd,skipEndDateWd,fromTimestampWd,toTimestampWd,skipStartTimestamp,skipEndTimestamp;
                    stepDiv         =jQuery(element).closest('.wdmSingleForm');
                    fromDateWd      =jQuery(stepDiv).find('.wdmWdStartDate').val();
                    fromTimeWd      =jQuery(stepDiv).find('.wdmWdStartTime').val();
                    toDateWd        =jQuery(stepDiv).find('.wdmWdEndDate').val();
                    toTimeWd        =jQuery(stepDiv).find('.wdmWdEndTime').val();
                    skipStartDateWd =jQuery(stepDiv).find('.wdmWdSkipStartDate').val();
                    skipEndDateWd   =jQuery(stepDiv).find('.wdmWdSkipEndDate').val();

                    if(!(wdmValidateDate(fromDateWd) && wdmValidateDate(toDateWd) && wdmValidateTime(fromTimeWd) && wdmValidateTime(toTimeWd))) {
                        alert("Please select valid date and times");
                        return -1;
                    }

                    fromTimestampWd =(new Date(fromDateWd+" "+fromTimeWd)).getTime();
                    toTimestampWd   =(new Date(toDateWd+" "+toTimeWd)).getTime();
                    
                    if(fromTimestampWd>=toTimestampWd){
                            alert("Sorry, can not create the schedule which expires before or at the same time of being created.\n Please Recheck the Dates & Times");
                            return -1;
                        }
                    
                    if (wdmValidateDate(skipStartDateWd) && wdmValidateDate(skipEndDateWd)) {
                        skipStartTimestamp =new Date(skipStartDateWd).getTime();
                        skipEndTimestamp   =new Date(skipEndDateWd).getTime();
                        if(!skipDurationIsBetweenScheduleStartAndEndTime(new Date(fromDateWd).getTime(),new Date(toDateWd).getTime(),skipStartTimestamp,skipEndTimestamp)){
                            alert("Sorry,\n there's something wrong with skip duration. please recheck the values entered");
                            return -1;
                        }   
                    } else {
                        jQuery(stepDiv).find('.wdmWdSkipStartDate').val("");
                        jQuery(stepDiv).find('.wdmWdSkipEndDate').val("");
                    }
                return "gotoFinalStep";
                case "wdmSingleFormScheduleSpecificTime":
                let fromDateLt,fromTimeLt,toDateLt,toTimeLt,skipStartDateLt,skipEndDateLt,fromTimestampLt,toTimestampLt,skipLtStartTimestamp,skipLtEndTimestamp;
                stepDiv         =jQuery(element).closest('.wdmSingleForm');
                fromDateLt      =jQuery(stepDiv).find('.wdmLtStartDate').val();
                fromTimeLt      =jQuery(stepDiv).find('.wdmLtStartTime').val();
                toDateLt        =jQuery(stepDiv).find('.wdmLtEndDate').val();
                toTimeLt        =jQuery(stepDiv).find('.wdmLtEndTime').val();
                skipStartDateLt =jQuery(stepDiv).find('.wdmLtSkipStartDate').val();
                skipEndDateLt   =jQuery(stepDiv).find('.wdmLtSkipEndDate').val();

                if(!(wdmValidateDate(fromDateLt) && wdmValidateDate(toDateLt) && wdmValidateTime(fromTimeLt) && wdmValidateTime(toTimeLt))) {
                    alert("Please select valid date and times");
                    return -1;
                }
                let arbitraryDate="01/01/2019";
                fromTimestampLtDate =(new Date(fromDateLt)).getTime();
                toTimestampLtDate   =(new Date(toDateLt)).getTime();
                fromTimestampLt =(new Date(arbitraryDate+" "+fromTimeLt)).getTime();
                toTimestampLt   =(new Date(arbitraryDate+" "+toTimeLt)).getTime();
                
                if(fromTimestampLtDate>toTimestampLtDate || fromTimestampLt>=toTimestampLt){
                        alert("Sorry, can not create the schedule which expires before or at the same time of being created.\n Please Recheck the Dates & Times");
                        return -1;
                    }
                
                if (wdmValidateDate(skipStartDateLt) && wdmValidateDate(skipEndDateLt)) {
                    skipLtStartTimestamp =new Date(skipStartDateLt).getTime();
                    skipLtEndTimestamp   =new Date(skipEndDateLt).getTime();
                    if(!skipDurationIsBetweenScheduleStartAndEndTime(new Date(fromDateLt).getTime(),new Date(toDateLt).getTime(),skipLtStartTimestamp,skipLtEndTimestamp)){
                        alert("Sorry,\n there's something wrong with skip duration. please recheck the values entered");
                        return -1;
                    }   
                } else {
                    jQuery(stepDiv).find('.wdmLtSkipStartDate').val("");
                    jQuery(stepDiv).find('.wdmLtSkipEndDate').val("");
                }
                return "gotoFinalStep";

                default:
                    break;
            }
        }

        function wdmEnableDateTimePickers(){
            let today = new Date();
            let str=(today.getMonth()+1)+"-"+today.getDate()+"-"+today.getFullYear();
            today=new Date(str);
            jQuery(".wdmDatePicker").bsDatetimepicker({
                //minDate:today,
                format : 'MM/DD/YYYY',
                showClear : true,
                useCurrent: true //Important! See issue #1075
                });
        
        jQuery(".wdmTimePicker").bsDatetimepicker({
                    format : 'LT',
                    showClear : true,
                    useCurrent: true //Important! See issue #1075
                    });
        }


     /**
     * Function which contains logic to deside next step
     */
    function stepDecisionMaker(choiceMade)
    {
        switch (choiceMade) {
            case "productLaunch":
                return "wdmSingleFormScheduleLaunch"; 
            case "scheduleDuration":
                return "wdmSingleFormScheduleAvailability";
            case "wholeDay":
                return "wdmSingleFormScheduleTypeWholeDay";
            case "specificTime":
                return "wdmSingleFormScheduleSpecificTime";
            case "wdmSingleFormScheduleLaunch":
                return "wdmSingleFormScheduleTimer";
            case "gotoFinalStep":
                return "wdmSingleFormScheduleTimer";
            case "error":
                return -1;
            default:
                return -1;
        }
    }
    

    function skipDurationIsBetweenScheduleStartAndEndTime(scheduleStart,scheduleEnd,skipStart,skipEnd)
        {
            if (skipStart>skipEnd) {
                return false;
            } else if(skipStart<=scheduleStart || skipEnd>=scheduleEnd){
                return false;
            }
            return true;
        }

    /**
     * Checks if the value for the date provided is in correct format
     * 
     */
    function wdmValidateDate(date)
        {
            let dateValidator=/^(0[1-9]|1[012])[- /.](0[1-9]|[12][0-9]|3[01])[- /.](19|20)\d\d$/;
            return dateValidator.test(date);
        }

    /**
     * checks if the value provided for the time is in a correct format
     * @param  time 
     */
    function wdmValidateTime(time)
        {
            let timeValidator=/^(0?[1-9]|1[012]):[0-5][0-9] (AM|PM)$/;
            return timeValidator.test(time);
        }

    function getFormScheduleType(FormElement)
        {
            let type=jQuery(FormElement).closest('.wdm-form-container').find('input[wdm-radio-group="ScheduleType"]:checked').val();
            if (type=="scheduleDuration") {
                type=jQuery(FormElement).closest('.wdm-form-container').find('input[wdm-radio-group="AvailabilityType"]:checked').val();
            }
            return type;
        }


    //Date-Time Picker pre-validations for limited duration schedule type
            jQuery( "body" ).delegate( ".wdmLtEndDate", "focus", function(e) {
                let date= jQuery(this).closest('.wdm-row').find('.wdmLtStartDate').val();
                if (date.length>0) {
                    date = new Date(date);
                    jQuery(this).data("DateTimePicker").minDate(date);    
                }
            });

            jQuery( "body" ).delegate( ".wdmLtStartDate", "focus", function(e) {
                let today = new Date();
                let str=(today.getMonth()+1)+"-"+today.getDate()+"-"+today.getFullYear();
                today=new Date(str);    
                jQuery(this).data("DateTimePicker").minDate(today);    
            });

            jQuery( "body" ).delegate( ".wdmLtSkipStartDate", "focus", function(e) {
                let dateStart= jQuery(this).closest('.wdmSingleFormScheduleSpecificTime').find('.wdmLtStartDate').val();
                let dateEnd= jQuery(this).closest('.wdmSingleFormScheduleSpecificTime').find('.wdmLtEndDate').val();
                if(dateStart.length>0)
                {
                    dateStart= new Date(dateStart);
                }
                if (dateEnd.length>0) {
                    dateEnd=new Date(dateEnd);    
                }
                if ((typeof(dateStart)=="object" && typeof(dateEnd)=="object")) {
                    if(dateEnd.getTime()-dateStart.getTime()<(48*60*60*1000)) {
                        jQuery(this).data("DateTimePicker").minDate(dateStart);
                        jQuery(this).data("DateTimePicker").maxDate(dateStart);
                    } else{
                        dateStart.setDate(dateStart.getDate() + 1);
                        dateEnd.setDate(dateEnd.getDate() - 1);
                        jQuery(this).data("DateTimePicker").minDate(dateStart);
                        jQuery(this).data("DateTimePicker").maxDate(dateEnd);
                    }
                }
            });
       
            jQuery( "body" ).delegate(".wdmLtSkipEndDate", "focus", function(e) {
                let dateStart= jQuery(this).closest('.wdmSingleFormScheduleSpecificTime').find('.wdmLtStartDate').val();
                let dateEnd= jQuery(this).closest('.wdmSingleFormScheduleSpecificTime').find('.wdmLtEndDate').val();
                if(dateStart.length>0)
                {
                    dateStart= new Date(dateStart);
                }
                if (dateEnd.length>0) {
                    dateEnd=new Date(dateEnd);    
                }
                if ((typeof(dateStart)=="object" && typeof(dateEnd)=="object")) {
                    if(dateEnd.getTime()-dateStart.getTime()<(48*60*60*1000)) {
                        jQuery(this).data("DateTimePicker").minDate(dateStart);
                        jQuery(this).data("DateTimePicker").maxDate(dateStart);
                    } else{
                        dateStart.setDate(dateStart.getDate() + 1);
                        dateEnd.setDate(dateEnd.getDate() - 1);
                        jQuery(this).data("DateTimePicker").minDate(dateStart);
                        jQuery(this).data("DateTimePicker").maxDate(dateEnd);
                    }
                }
                let skipStart=jQuery(this).closest('.wdm-row').find('input.wdmLtSkipStartDate').val();
                if (skipStart.length>0) {
                    skipStart=new Date(skipStart);
                    jQuery(this).data("DateTimePicker").minDate(skipStart);
                }
            });


        
        //Date-Time Picker pre-validations for whole time schedule type
            jQuery( "body" ).delegate( ".wdmWdEndDate", "focus", function(e) {
                let date= jQuery(this).closest('.wdmSingleFormScheduleTypeWholeDay').find('.wdmWdStartDate').val();
                if (date.length>0) {
                    date = new Date(date);
                    jQuery(this).data("DateTimePicker").minDate(date);    
                }
            });

            jQuery( "body" ).delegate( ".wdmWdStartDate", "focus", function(e) {
                let today = new Date();
                let str=(today.getMonth()+1)+"-"+today.getDate()+"-"+today.getFullYear();
                today=new Date(str);    
                jQuery(this).data("DateTimePicker").minDate(today);    
            });

            jQuery( "body" ).delegate( ".wdmWdSkipStartDate", "focus", function(e) {
                let dateStart= jQuery(this).closest('.wdmSingleFormScheduleTypeWholeDay').find('.wdmWdStartDate').val();
                let dateEnd= jQuery(this).closest('.wdmSingleFormScheduleTypeWholeDay').find('.wdmWdEndDate').val();
                if(dateStart.length>0)
                {
                    dateStart= new Date(dateStart);
                }
                if (dateEnd.length>0) {
                    dateEnd=new Date(dateEnd);    
                }
                if ((typeof(dateStart)=="object" && typeof(dateEnd)=="object")) {
                    if(dateEnd.getTime()-dateStart.getTime()<(48*60*60*1000)) {
                        jQuery(this).data("DateTimePicker").minDate(dateStart);
                        jQuery(this).data("DateTimePicker").maxDate(dateStart);
                    } else{
                        dateStart.setDate(dateStart.getDate() + 1);
                        dateEnd.setDate(dateEnd.getDate() - 1);
                        jQuery(this).data("DateTimePicker").minDate(dateStart);
                        jQuery(this).data("DateTimePicker").maxDate(dateEnd);
                    }
                }
            });
        
            jQuery( "body" ).delegate(".wdmWdSkipEndDate", "focus", function(e) {
                let dateStart= jQuery(this).closest('.wdmSingleFormScheduleTypeWholeDay').find('.wdmWdStartDate').val();
                let dateEnd= jQuery(this).closest('.wdmSingleFormScheduleTypeWholeDay').find('.wdmWdEndDate').val();
                if(dateStart.length>0)
                {
                    dateStart= new Date(dateStart);
                }
                if (dateEnd.length>0) {
                    dateEnd=new Date(dateEnd);    
                }
                if ((typeof(dateStart)=="object" && typeof(dateEnd)=="object")) {
                    if(dateEnd.getTime()-dateStart.getTime()<(48*60*60*1000)) {
                        jQuery(this).data("DateTimePicker").minDate(dateStart);
                        jQuery(this).data("DateTimePicker").maxDate(dateStart);
                    } else{
                        dateStart.setDate(dateStart.getDate() + 1);
                        dateEnd.setDate(dateEnd.getDate() - 1);
                        jQuery(this).data("DateTimePicker").minDate(dateStart);
                        jQuery(this).data("DateTimePicker").maxDate(dateEnd);
                    }
                }
                let skipStart=jQuery(this).closest('.wdm-row').find('input.wdmWdSkipStartDate').val();
                if (skipStart.length>0) {
                    skipStart=new Date(skipStart);
                    jQuery(this).data("DateTimePicker").minDate(skipStart);
                }
            });
    
    //Date-Time Picker pre-validations for Launch schedule type
        jQuery( "body" ).delegate( ".wdmLaunchDate", "focus", function(e) {
            let today = new Date();
            let str=(today.getMonth()+1)+"-"+today.getDate()+"-"+today.getFullYear();
            today=new Date(str);    
            jQuery(this).data("DateTimePicker").minDate(today);    
        });
});
