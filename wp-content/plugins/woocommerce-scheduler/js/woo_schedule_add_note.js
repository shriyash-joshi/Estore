jQuery(document).ready(function () {

    var week_flag = false;
    var startDateFlag = false;
    var endDateFlag = false;
    var startTimeFlag = false;
    var endTimeFlag = false;
    var startDate = "";
    var endDate = "";
    var startTime = "";
    var endTime = "";

    jQuery('#variable_product_options').delegate('input.wdm_start_date', 'dp.change', function () {
        if (startDate == "") {
            startDate = this.value;
        } else if (startDate != this.value) {
            startDateFlag = true;            
        }
    });

    jQuery('#variable_product_options').delegate('input.wdm_end_date', 'dp.change', function () {
        if (endDate == "") {
            endDate = this.value;
        } else if (endDate != this.value) {
            endDateFlag = true;            
        }
    });    

    jQuery('#variable_product_options').delegate('input.wdm_start_time', 'dp.change', function () {
        if (startTime == "") {
            startTime = this.value;
        } else if (startTime != this.value) {
            startTimeFlag = true;            
        }
    });  

    jQuery('#variable_product_options').delegate('input.wdm_end_time', 'dp.change', function () {
        if (endTime == "") {
            endTime = this.value;
        } else if (endTime != this.value) {
            endTimeFlag = true;            
        }        
    });
 
    jQuery('#variable_product_options').delegate('input.wdm_day_of_week', 'change', function () {
        week_flag = true;
    }); 

    
    jQuery('#publish, .save-variation-changes').click(function(e){
        let allAreValid=wdmValidateAllSchedules();
        if (!allAreValid) {
            alert("One or more schedules are not finalized , please check.");
            e.preventDefault();
            return false;
        }
    });

    //On clicking Save Changes, check if all values are valid. If there is any invalid field, then show alert.
    jQuery("#variable_product_options").on('woocommerce_variations_save_variations_button', function(e){
        jQuery('#wdm_message').hide();
        var wrapper = jQuery('#variable_product_options').find('.woocommerce_variations')
        var messageText = "Please update the product to apply the scheduling changes";
        wrapper.before("<div id='wdm_message' class='error my-notice'><p>" + messageText + "</p></div>").focus();
        jQuery("html, body").animate({
                scrollTop: jQuery('#wdm_message').offset().top - 50
            }, "slow");
    });


    function wdmValidateAllSchedules()
    {
        let wdmActiveScheduleForms=jQuery(".wdm-form-container:visible");
        
        for (let i = 0; i < wdmActiveScheduleForms.length; i++) {
            const isFinalized = jQuery(wdmActiveScheduleForms[i]).find('.wdmFormNextButton').text()=="Done";
            if (!isFinalized) {
                return false;
            }
        }
        return true;
    }

});

// please click update button for the changes to get applicable
// Please update the product to apply the scheduling changes