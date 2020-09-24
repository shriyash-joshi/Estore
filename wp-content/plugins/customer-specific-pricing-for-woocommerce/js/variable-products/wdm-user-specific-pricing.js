/**
* Function for base 64 decode of data.
* @param string data selector
*/
function wdm_base64_decode(data) {
    //  discuss at: http://phpjs.org/functions/base64_decode/
    // original by: Tyler Akins (http://rumkin.com)
    // improved by: Thunder.m
    // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    //    input by: Aman Gupta
    //    input by: Brett Zamir (http://brett-zamir.me)
    // bugfixed by: Onno Marsman
    // bugfixed by: Pellentesque Malesuada
    // bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    //   example 1: base64_decode('S2V2aW4gdmFuIFpvbm5ldmVsZA==');
    //   returns 1: 'Kevin van Zonneveld'
    //   example 2: base64_decode('YQ===');
    //   returns 2: 'a'

    var b64 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=';
    var o1, o2, o3, h1, h2, h3, h4, bits, i = 0,
            ac = 0,
            dec = '',
            tmp_arr = [];

    if (!data) {
        return data;
    }

    data += '';

    do { // unpack four hexets into three octets using index points in b64
        h1 = b64.indexOf(data.charAt(i++));
        h2 = b64.indexOf(data.charAt(i++));
        h3 = b64.indexOf(data.charAt(i++));
        h4 = b64.indexOf(data.charAt(i++));

        bits = h1 << 18 | h2 << 12 | h3 << 6 | h4;

        o1 = bits >> 16 & 0xff;
        o2 = bits >> 8 & 0xff;
        o3 = bits & 0xff;

        if (h3 === 64) {
            tmp_arr[ac++] = String.fromCharCode(o1);
        } else if (h4 === 64) {
            tmp_arr[ac++] = String.fromCharCode(o1, o2);
        } else {
            tmp_arr[ac++] = String.fromCharCode(o1, o2, o3);
        }
    } while (i < data.length);

    dec = tmp_arr.join('');

    return dec.replace(/\0+$/, '');
}
jQuery(document).ready(function () {
    //Add the customer specific pricing tab for every variation of variable product.
    wdm_accordion();
    function wdm_accordion() {
        jQuery('.accordion').accordion({
            clearStyle: true,
            heightStyle: "content",
            collapsible: true,
        });
    }

    // jQuery( '#variable_product_options' ).on( 'woocommerce_variations_added', function () {
    //         wdm_accordion();
    // } );
    jQuery('#variable_product_options').on('click', function () {
        wdm_accordion();
    });


    jQuery('.expand_all').on('click', function () {
        wdm_accordion();
    });


    //Add new row when there are no rows
    jQuery('#variable_product_options').delegate('span.add_var_csp', 'click', function () {
        var table_to_be_considered = jQuery(this).next("table.wdm_variable_product_usp_table");
        var wdm_user_dropdown_csp = wdm_base64_decode(jQuery(this).parents('div.wdm_user_price_mapping_wrapper').find('.wdm_hidden_user_dropdown_csp').html()); //user dropdown html
        var variation_post_id = jQuery(this).parents('div.wdm_user_price_mapping_wrapper').find('.wdm_hidden_variation_data_csp').html(); //variation post id
        jQuery(this).remove();
        table_to_be_considered.show();
        table_to_be_considered.append(
                '<tr class="single_variable_csp_row">' +
                '<td>' + wdm_user_dropdown_csp + '</td>' +
                '<td><select name="wdm_user_price_type_v[' + variation_post_id + '][]" class="chosen-select csp_wdm_action"><option value = 1>'+wdm_variable_product_csp_object.flat+'</option><option value = 2>%</option></select></td>' +
                '<td><input type="number" min = "1" size="5" name="wdm_woo_variation_qty[' + variation_post_id + '][]" class="wdm_qty" /></td>' +
                '<td><input type="text" style="width:75px;" size="5" name="wdm_woo_variation_price[' + variation_post_id + '][]" class="wdm_price" /></td>' +
                '<td class="remove_var_csp" style="color:#ff0000;cursor: pointer;" tabindex="0"><img src="' + wdm_variable_product_csp_object.minus_image + '"></td>' +
                '<td class="add_var_csp" style="cursor: pointer;"><img src="' + wdm_variable_product_csp_object.plus_image + '"  tabindex="0"></td>' +
                '</tr>'
                );
    });
    //Add row when button is clicked
    jQuery('#variable_product_options').delegate('td.add_var_csp', 'click', function () {
        var wdm_user_dropdown_csp = wdm_base64_decode(jQuery(this).parents('div.wdm_user_price_mapping_wrapper').find('.wdm_hidden_user_dropdown_csp').html()); //user dropdown html
        var variation_post_id = jQuery(this).parents('div.wdm_user_price_mapping_wrapper').find('.wdm_hidden_variation_data_csp').html(); //variation post id
        
        let qtyField=jQuery(this.parentElement.children[2].children[0]);
        if(!entereredValidCSPQuantity(qtyField))
        {
           jQuery(qtyField).tipTip({content:wdmScpMessages['quantityMessage'],defaultPosition:'top'});
           jQuery( qtyField ).mouseover();
           return false;
        }

        var wdm_append_to_parent = jQuery(this).parents("table.wdm_variable_product_usp_table");
        wdm_append_to_parent.append(
                '<tr class="single_variable_csp_row">' +
                '<td>' + wdm_user_dropdown_csp + '</td>' +
                '<td><select name="wdm_user_price_type_v[' + variation_post_id + '][]" class="chosen-select csp_wdm_action"><option value = 1>'+wdm_variable_product_csp_object.flat+'</option><option value = 2>%</option></select></td>' +
                '<td><input type="number" size="5" min = "1" name="wdm_woo_variation_qty[' + variation_post_id + '][]" class="wdm_qty" /></td>' +
                '<td><input type="text" style="width:75px;" size="5" name="wdm_woo_variation_price[' + variation_post_id + '][]" class="wdm_price" /></td>' +
                '<td class="remove_var_csp" style="color:#ff0000;cursor: pointer;"  tabindex="0"><img src="' + wdm_variable_product_csp_object.minus_image + '"></td>' +
                '<td class="add_var_csp" style="cursor: pointer;"><img src="' + wdm_variable_product_csp_object.plus_image + '"  tabindex="0"></td>' +
                '</tr>'
                );

        jQuery(this).remove(); //Remove Add Button

    });

//Remove row when remove button is clicked
    jQuery('#variable_product_options').delegate('td.remove_var_csp', 'click', function () {
        let qtyField=jQuery(this.parentElement.children[2].children[0]);
        jQuery(qtyField).mouseout();
        var parent_table_element = jQuery(this).parents("table.wdm_variable_product_usp_table");
        //enabled save changes button on edit variable product page whenever a user pricing pair is removed.

        jQuery(this).closest('.woocommerce_variation').addClass('variation-needs-update');
        jQuery('button.cancel-variation-changes, button.save-variation-changes').removeAttr('disabled');
        jQuery('#variable_product_options').trigger('woocommerce_variations_input_changed');

        jQuery(this).parent('tr').remove();
        var last_tr_element = parent_table_element.find("tr:last");


        if (last_tr_element.find("th:last").length) {
            parent_table_element.hide();
            parent_table_element.before('<span class="add_var_csp" style="cursor: pointer; margin: 1em;display:block;"><button type="button" class="button"  tabindex="0">'+wdm_variable_product_csp_object.add_new_pair+'</button></span>');
        }
        else if (!last_tr_element.find("td:last").hasClass('add_var_csp')) {
            last_tr_element.append('<td class="add_var_csp" style="cursor: pointer;"><img src="' + wdm_variable_product_csp_object.plus_image + '"  tabindex="0"></td>');
        }
    });

    // var wdm_float_pattern = /^\d+(.\d*)?$/;

    // jQuery('#variable_product_options').delegate('.wdm_price', 'change',function(){
    //     var price = jQuery(this).val();
    //     if(price == '0') {
    //         jQuery(this).val(parseFloat(price).toFixed(2));
    //     }
    // });


    // //If qty is not valid, then highlight the qty box
    // jQuery( '#userSpecificPricingTab_data' ).delegate( ".wdm_qty", 'focusout', function () {
    //     if (jQuery( this ).val() == "") {
    //         return;
    //     }
    //     if ( jQuery( this ).val() <= 0 ) {
    //         jQuery( this ).addClass( 'wdm_error' );
    //     }
    // } ); //end focusout

    // //When User edits the invalid field, clear the background of that field
    // jQuery( '#userSpecificPricingTab_data' ).delegate( ".wdm_qty", 'focusin', function () {
    //     jQuery( this ).removeClass( 'wdm_error' );
    // } ); //end focusin

    jQuery('#variable_product_options').delegate('.remove_var_csp, .remove_var_csp_v, .remove_var_g_csp, .add_var_csp, .add_var_csp_v, .add_var_g_csp').keypress(function(event){
        var keycode = (event.keyCode ? event.keyCode : event.which);
        if(keycode==13){
        event.target.click();
        }
        });
});


