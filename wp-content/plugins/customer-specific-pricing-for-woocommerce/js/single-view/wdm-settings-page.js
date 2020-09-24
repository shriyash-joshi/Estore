jQuery( document ).ready()
{
    if(jQuery('#csp-show-regular-price').is(':checked')){
        console.log("Visible");
       jQuery('.reg-price-display-text').show(); 
    } else {
        jQuery('.reg-price-display-text').hide();
    }

    jQuery('#csp-show-regular-price').change(function(e){
        if(jQuery(this).is(':checked')){
            // Enable price text box
            jQuery('.reg-price-display-text').show('fast');
        } else {
            //disable price text box
            jQuery('.reg-price-display-text').hide(200);
        }
    });

}