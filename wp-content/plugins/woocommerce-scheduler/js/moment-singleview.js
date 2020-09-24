// jQuery(document).ready(function () {
//     console.log("heloo");
//     var selection_type= moment_singleview_obj.days_selection_type;
//     if(selection_type == "per_day"){
//         mindate = moment().format('MM/DD/YYYY');
//     }
//     else{
//         mindate = moment();
//     }
    
//     jQuery('#wdm_start_date').datetimepicker({
//         minDate: mindate,//moment().format('MM/DD/YYYY'),
//         format : 'MM/DD/YYYY, HH:mm',
//         defaultDate : moment(),
//         ignoreReadonly : true,
//         showClear : true
//     });

//     jQuery('#wdm_end_date').datetimepicker({
//         minDate: mindate,//moment().format('MM/DD/YYYY'),
//         format : 'MM/DD/YYYY, HH:mm',
//         ignoreReadonly : true,
//         showClear : true,
//         useCurrent: true //Important! See issue #1075
//     });

//     //revise 'End date' --- minimum date after change in Start date

//     jQuery("#wdm_start_date").on("dp.change", function (e) {

//         //console.log(e);

//         jQuery('#wdm_end_date').data("DateTimePicker").minDate(e.date);
//     });
//     if (jQuery('#wdm_start_date').length > 0) {
//         jQuery('#wdm_start_date').data("DateTimePicker").date(null);        
//         jQuery('#wdm_end_date').data("DateTimePicker").date(null);
//     }


// });