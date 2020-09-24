function validate()
{
        var type = woo_schedule_validate_obj.type;

        /* Date variables */
        var start_date = jQuery('#wdm_start_date').val();
        var end_date = jQuery('#wdm_end_date').val();

        var st_dt = new Date(start_date);
        var ed_dt = new Date(end_date);

        var flag_hr = "";
        var flag_min = "";

        /* Time variables */
        var start_time_hr = st_dt.getHours();
        var start_time_min = st_dt.getMinutes();
        var end_time_hr = ed_dt.getHours();
        var end_time_min = ed_dt.getMinutes();

        /*
         *
         * Validating Dates and Validating Time
         *
         */

    if(type == 'per_day')
    {
            if(start_date != "" && end_date != "")
            {

                if(st_dt.getTime() > ed_dt.getTime()){
                    // alert(woo_schedule_validate_obj.error_msg_start_date_less_than_end_date);
                    // return false;
                }

                if(start_time_hr != -1 && start_time_min != -1 && end_time_hr != -1 && end_time_min != -1  )
                {
                	//01/01/2011 is dummy value for date

                    // if(Date.parse('01/01/2011 '+start_time_hr+':'+start_time_min+':00') > Date.parse('01/01/2011 '+end_time_hr+':'+end_time_min+':59')){
                    //     alert(woo_schedule_validate_obj.error_msg_end_time_more_than_start_time+"Here");
                    //     return false;
                    // }
                }
                else{
                    alert(woo_schedule_validate_obj.error_msg_time_empty);
                    return false;
                }

            }//start date - end date empty check ends
            else if(start_date === "" && end_date === "")
            {
                if(start_time_hr == -1 && start_time_min == -1 && end_time_hr && end_time_min == -1)
                {
                    return true;
                }
                else{
                	alert(woo_schedule_validate_obj.error_msg_details_empty);
                	return false;
            	}
            }
            else
            {
                alert(woo_schedule_validate_obj.error_msg_details_empty);
                return false;
            }
    }//if ends -- per day type ends
    else
    {
    	//Checking for Entire day schedule

        if(start_date != "" && end_date != "")
        {
        	if(st_dt.getTime() > ed_dt.getTime()){
                alert(woo_schedule_validate_obj.error_msg_start_date_less_than_end_date);
                return false;
            }

            if(start_time_hr != -1 && start_time_min != -1 && end_time_hr != -1 && end_time_min != -1  )
            {
                start_time = new Date(st_dt.getFullYear(),st_dt.getMonth(),st_dt.getDate(),start_time_hr,start_time_min,00,00); //start_date.getTime();
                end_time = new Date(ed_dt.getFullYear(),ed_dt.getMonth(),ed_dt.getDate(),end_time_hr,end_time_min,59,00); //end_date.getTime();

                if(start_time <= end_time)
                {
                    return true;
                 }else{
                    alert(woo_schedule_validate_obj.error_msg_end_time_more_than_start_time);
                    return false;
                }
            }
            else{
                alert(woo_schedule_validate_obj.error_msg_time_empty);
                return false;
            }
        }
        else if(start_date === "" && end_date === "")
        {
            if(start_time_hr == -1 && start_time_min == -1 && end_time_hr == -1 && end_time_min == -1)
            {
                return true;
            }
            else
            {
                alert(woo_schedule_validate_obj.error_msg_details_empty);
                return false;
            }
        }
        else{
                alert(woo_schedule_validate_obj.error_msg_details_empty);
                return false;
            }
    }

    return true;
}