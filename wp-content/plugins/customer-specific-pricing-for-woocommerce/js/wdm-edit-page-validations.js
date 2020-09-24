jQuery(document).ready(function () {

/*
 * function which checks if any of the rule in rules table is % discount rule
 * in such case function returns true if rule is completely written else returns
 * false, meaning there is no rule specifying % discount.
 */
function isThereAnyPercentRuleIn(tableID)
{
    let content= jQuery('#'+tableID+' > tr');
    let i=0;
        content.each(function(e){
           //if any of the rule is % based then entering a regular price for the product is compulsory.
           if(2==jQuery(content[e].childNodes[1].firstChild).val()){
               if (jQuery(content[e].childNodes[3].firstChild).val()!='') {
                return true;
               }                
            }
            i++;
        });
        if (i<content.length) {
            return true;
        }
        return false;
}

// Edit product page of variable product for each variation.
    var variation_ids;
    function wdm_error_function(error_location = 'top_of_page') {
        jQuery('#wdm_message').remove();
        var wdm_error_flag = 1;
        var wdm_rprice_error = 1;
        var variation_error_id = 0;
        jQuery(".wdm_price").filter(function () {
            var variation_string = jQuery(this).attr('name'); // name of the price input field

            if (jQuery(this).hasClass('wdm_error')) {
                variation_error_id = parseInt(variation_string.replace( /^\D+/g, ''));
                return wdm_error_flag = 0;
            }


        });

        jQuery( ".wdm_qty" ).filter( function () {
            var variation_string = jQuery(this).attr('name'); // name of the price input field
            if ( jQuery( this ).hasClass( 'wdm_error' ) ) {
                variation_error_id = parseInt(variation_string.replace( /^\D+/g, ''));
                return wdm_error_flag = 0;
            }
        } );

        if (wdm_rprice_error === 0) {
            var error_variation_ids = "<span class = 'wdm_error_ids'>";
            var array_size = variation_ids.length;
            // var seperator = "";
            for( var i in variation_ids) {
                var seperator = "";
                if(array_size-1 != i){
                    seperator = ", ";
                }
                error_variation_ids += "#"+variation_ids[i]+seperator;
            }
            error_variation_ids += "</span>";
            var messageText = wdm_csp_function_object.please_verify_regular_prices+error_variation_ids+wdm_csp_function_object.please_set_regular_prices;
            return preventSubmission(messageText, error_location);
        } else if (wdm_error_flag === 0) {
                var messageText = wdm_csp_function_object.please_verify_prices + variation_error_id;
                return preventSubmission(messageText, error_location);
        }
    }

    function pushInErrorVariationIds(variation_id)
    {
        if(jQuery.inArray(variation_id, variation_ids) == -1) {
            variation_ids.push(variation_id);
        }
    }

    jQuery('#variable_product_options').delegate('input[id^="variable_regular_price"]', 'focus', function () {
        jQuery(this).attr('oldValue',$(this).val());
    });


    //If qty is not valid, then highlight the qty box
    jQuery( '#variable_product_options' ).delegate( ".wdm_qty", 'focusout', function () {
        var current_quantity = jQuery( this ).val();
        if(!isPositiveInt(current_quantity) && cspGetClosestElement(this, '.wdm_price').val()!="") {
            jQuery( this ).addClass( 'wdm_error' );
            jQuery( this ).tipTip({content:wdmScpMessages['quantityMessage'],defaultPosition:'top'});
            jQuery( this ).mouseover();
        } else {
            jQuery( this ).mouseout();
            if(jQuery( this ).hasClass( 'wdm_error' )) {
                jQuery( this ).removeClass( 'wdm_error' );
            }
        }
    } ); //end focusout

    //If Price is not valid, then highlight the Price box
    jQuery('#variable_product_options').delegate('.wdm_price', 'focusout', function () {
        highlightPriceError(jQuery(this));
        var qtyField = cspGetClosestElement(this, '.wdm_qty');
        if (jQuery(this).val() != '' && jQuery(qtyField).val() == '') {
            jQuery(qtyField).addClass( 'wdm_error' );
        }
    }); //end live

    //When User edits the invalid field, clear the background of that field
    jQuery( '#variable_product_options' ).delegate( ".wdm_qty", 'focusin', function () {
        jQuery( this ).removeClass( 'wdm_error' );
    } ); //end focusin

    //When User edits the invalid field, clear the background of that field
    jQuery('#variable_product_options').delegate('.wdm_price', 'focusin', function () {
        jQuery(this).removeClass('wdm_error');
    }); //end live

    jQuery('#variable_product_options').delegate('.wdm_price', 'change',function(){
        highlightPriceError(jQuery(this));
        var qtyField = cspGetClosestElement(this, '.wdm_qty');
        if (jQuery(this).val() != '' && jQuery(qtyField).val() == '') {
            jQuery(qtyField).addClass( 'wdm_error' );
            jQuery( qtyField ).tipTip({content:wdmScpMessages['quantityMessage'],defaultPosition:'top'});
            jQuery( qtyField ).mouseover();
        }
    });

    jQuery( '#variable_product_options' ).delegate( '.csp_wdm_action', 'change', function () {
        var price_selector = jQuery(this).closest('tr').find('.wdm_price');

        if(jQuery(this).val() == 1) {
            if(price_selector.hasClass('csp-percent-discount')) {
                price_selector.removeClass('csp-percent-discount');
            }
        } else {
            price_selector.addClass('csp-percent-discount');
        }
        highlightPriceError(price_selector);
    });

    //On clicking Publish or Update button, check if all values are valid. If there is any invalid field, then show alert.
    jQuery("form#post").submit(function () {
        
        if(jQuery('#product-type').val() != 'variable') {
            return;
        }

        variation_ids = [];
        return wdm_error_function();      
    }); //end submit


    //When User edits the invalid field, clear the background of that field
    jQuery( '#userSpecificPricingTab_data' ).delegate( ".wdm_price", 'focusin', function () {
        jQuery( this ).removeClass( 'wdm_error' );
    } ); //end focusin

    //If qty is not valid, then highlight the qty box
    jQuery( '#userSpecificPricingTab_data' ).delegate( ".wdm_qty", 'focusout', function () {
        var current_quantity = jQuery( this ).val();
        current_quantity = current_quantity.trim();
        if(!isPositiveInt(current_quantity) && (cspGetClosestElement(this, '.wdm_price').val() != '')) {
            jQuery( this ).addClass( 'wdm_error' );
            jQuery( this ).tipTip({content:wdmScpMessages['quantityMessage'],defaultPosition:'top'});
            jQuery( this ).mouseover();
        } else {
            jQuery(this).mouseout();
            if(jQuery( this ).hasClass( 'wdm_error' )) {
                jQuery( this ).removeClass( 'wdm_error' );
            }
        }
    } ); //end focusout

    //When User edits the invalid field, clear the background of that field
    jQuery( '#userSpecificPricingTab_data' ).delegate( ".wdm_qty", 'focusin', function () {
        jQuery( this ).removeClass( 'wdm_error' );
        jQuery(this).mouseout();
    } ); //end focusin

    /*Simple Products*/
    //On clicking Publish or Update button, check if all values are valid. If there is any invalid field, then show alert.
    jQuery( "form#post" ).submit( function () {
        
        if(jQuery('#product-type').val() != 'simple') {
            return;
        }

        jQuery('#wdm_message').remove();
        var wdm_error_flag = 1;
        jQuery( ".wdm_price" ).filter( function () {
            if ( jQuery( this ).hasClass( 'wdm_error' ) ) {
                return wdm_error_flag = 0;
            }
        } );
        jQuery( ".wdm_qty" ).filter( function () {
            if ( jQuery( this ).hasClass( 'wdm_error' ) ) {
                return wdm_error_flag = 0;
            }
        } );

        if(jQuery("#_regular_price").val() == '') {
            if( isThereAnyPercentRuleIn('wdm_user_specific_pricing_tbody') ||
                isThereAnyPercentRuleIn('wdm_role_specific_pricing_tbody') ||
                isThereAnyPercentRuleIn('wdm_group_specific_pricing_tbody') ){
                    jQuery('#poststuff').before("<div id='wdm_message' class='error my-notice'><p>"+wdm_csp_edit_page_object.regular_price_required_for_percent_discount+"</p></div>").focus();
                    jQuery("html, body").animate({
                        scrollTop: 0
                      }, "fast");
                    return false;
                }
            
        }
        
        if ( wdm_error_flag === 0 ) {
            jQuery('#poststuff').before("<div id='wdm_message' class='error my-notice'><p>"+wdm_csp_edit_page_object.please_verify_prices+"</p></div>").focus();
            jQuery("html, body").animate({
                scrollTop: 0
            }, "slow");
            return false;
        }
    } ); //end submit
    // Highlights if price field has any error.
    // Highlights if quantity field has any error.
    jQuery('#userSpecificPricingTab_data').delegate('.wdm_price', 'change',function(){
        highlightPriceError(jQuery(this));
        var qtyField = cspGetClosestElement(this, '.wdm_qty');
        if (jQuery(this).val() != '' && jQuery(qtyField).val() == '') {
            jQuery(qtyField).addClass( 'wdm_error' );
            jQuery( qtyField ).tipTip({content:wdmScpMessages['quantityMessage'],defaultPosition:'top'});
            jQuery( qtyField ).mouseover();
        }
    });
    // Highlights if price field has any error.
    jQuery( '#userSpecificPricingTab_data' ).delegate( ".wdm_price", 'focusout', function () {
        highlightPriceError(jQuery(this));
    });

    //Change the price type and class of the selector according to the selection.
    jQuery( '#userSpecificPricingTab_data' ).delegate( '.csp_wdm_action', 'change', function () {

        var price_selector = jQuery(this).closest('tr').find('.wdm_price');

        if(jQuery(this).val() == 1) {
            if(price_selector.hasClass('csp-percent-discount')) {
                price_selector.removeClass('csp-percent-discount');
            }
        } else {
            price_selector.addClass('csp-percent-discount');
        }
        highlightPriceError(price_selector);
    });
    

    /**
     * 
     */
    jQuery('.csp_wdm_action').live('change',function(){
        let selectElement=jQuery(this);
        if(selectElement.val()==2)
            {
            let productType='variable';
            //variable product
            let val=selectElement.closest('.data').children('.variable_pricing').children('p').children('input[name*="variable_regular_price"]').val();
            //Simple Product
            if(val==undefined)
                {
                val=jQuery('#_regular_price').val();
                productType='simple';
                } else{
                productType='variable';
            }
        
            if(val=='' || val==null || val==undefined){
                val=prompt("For applying % discount, you must mention Regular Price\nEnter Regular Price if you want to set it up",1);
                if (val!=undefined && !isNaN(val) && isPositiveInteger(val)) {
                    if ('simple'==productType) {
                        jQuery('#_regular_price').val(val);
                        return true;
                    }
                    else{
                        selectElement.closest('.data').children('.variable_pricing').children('p').children('input[name*="variable_regular_price"]').val(val);
                        return true;
                    }
                } else  {
                    selectElement.find('option[value="2"]').attr('selected',false);
                selectElement.find('option[value="1"]').attr('selected',true);
                selectElement.addClass('wdm_error_temp');
                setTimeout(function() 
                    {
                    selectElement.removeClass('wdm_error_temp');
                    },2000);    
                }
            }
            return false;
            }
        });

    jQuery('#woocommerce-product-data').delegate('span.csp-notes-title','click', function(){
        $content = jQuery(this).next('.csp-notes-content');
        $content.addClass('active');
        if($content.css('max-height')=='0px')
        {
            $content.css('max-height','min-content');
        } else {
            $content.css('max-height','0px');
        }
    });
});
