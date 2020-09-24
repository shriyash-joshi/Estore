jQuery(document).ready(function () {
    // Edit product/product page for adding the group-specific pricing tab for every variation.
    
        //Add new row when there are no rows
    jQuery('#variable_product_options').delegate('span.add_var_g_csp', 'click', function () {
        var table_to_be_considered = jQuery(this).next("table.wdm_variable_product_gsp_table");
        var wdm_group_dropdown_csp = wdm_base64_decode(jQuery(this).parents('div.wdm_group_price_mapping_wrapper').find('.wdm_hidden_group_dropdown_csp').html()); //user dropdown html

        var variation_post_id = jQuery(this).parents('div.wdm_group_price_mapping_wrapper').find('.wdm_hidden_variation_group_data_csp').html(); //variation post id

        jQuery(this).remove();
        table_to_be_considered.show();
        table_to_be_considered.append(
                '<tr class="single_variable_csp_row">' +
                '<td>' + wdm_group_dropdown_csp + '</td>' +
                '<td><select name="wdm_group_price_type_v[' + variation_post_id + '][]" class="chosen-select csp_wdm_action"><option value = 1>'+wdm_variable_product_csp_object.flat+'</option><option value = 2>%</option></select></td>' +
                '<td><input type="number" min = "1" size="5" name="wdm_woo_variation_group_qty[' + variation_post_id + '][]" class="wdm_qty" />' +
                '<td><input type="text" style="width:75px;" size="5" name="wdm_woo_variation_group_price[' + variation_post_id + '][]" class="wdm_price" />' +
                '<td class="remove_var_g_csp" style="color:#ff0000;cursor: pointer;"  tabindex="0"><img src="' + wdm_variable_product_csp_object.minus_image + '"></td>' +
                '<td class="add_var_g_csp" style="cursor: pointer;"><img src="' + wdm_variable_product_csp_object.plus_image + '"  tabindex="0"></td>' +
                '</tr>'
                );
    });
    //Add row when button is clicked
    jQuery('#variable_product_options').delegate('td.add_var_g_csp', 'click', function () {

        let qtyField=jQuery(this.parentElement.children[2].children[0]);
        if(!entereredValidCSPQuantity(qtyField))
        {
           jQuery(qtyField).tipTip({content:wdmScpMessages['quantityMessage'],defaultPosition:'top'});
           jQuery( qtyField ).mouseover();
           return false;
        }
        //base 64 decode for the entry.
        var wdm_group_dropdown_csp = wdm_base64_decode(jQuery(this).parents('div.wdm_group_price_mapping_wrapper').find('.wdm_hidden_group_dropdown_csp').html()); //user dropdown html

        var variation_post_id = jQuery(this).parents('div.wdm_group_price_mapping_wrapper').find('.wdm_hidden_variation_group_data_csp').html(); //variation post id

        var wdm_append_to_parent = jQuery(this).parents("table.wdm_variable_product_gsp_table");
        //Group Pricing tab with visible and not visible entities.
        wdm_append_to_parent.append(
                '<tr class="single_variable_csp_row">' +
                '<td>' + wdm_group_dropdown_csp + '</td>' +
                '<td><select name="wdm_group_price_type_v[' + variation_post_id + '][]" class="chosen-select csp_wdm_action"><option value = 1>' + wdm_variable_product_csp_object.flat + '</option><option value = 2>%</option></select></td>' +
                '<td><input type="number" min = "1" size="5" name="wdm_woo_variation_group_qty[' + variation_post_id + '][]"  class="wdm_qty" />' +
                '<td><input type="text" style="width:75px;" size="5" name="wdm_woo_variation_group_price[' + variation_post_id + '][]"  class="wdm_price" />' +
                '<td class="remove_var_g_csp" style="color:#ff0000;cursor: pointer;"  tabindex="0"><img src="' + wdm_variable_product_csp_object.minus_image + '"></td>' +
                '<td class="add_var_g_csp" style="cursor: pointer;"><img src="' + wdm_variable_product_csp_object.plus_image + '"  tabindex="0"></td>' +
                '</tr>'
                );

        jQuery(this).remove(); //Remove Add Button

    });

//Remove row when remove button is clicked
    jQuery('#variable_product_options').delegate('td.remove_var_g_csp', 'click', function () {
        
        let qtyField=jQuery(this.parentElement.children[2].children[0]);
        jQuery(qtyField).mouseout();

        var parent_table_element = jQuery(this).parents("table.wdm_variable_product_gsp_table");

        //enabled save changes button on edit variable product page whenever a group pricing pair is removed.
        jQuery(this).closest('.woocommerce_variation').addClass('variation-needs-update');
        jQuery('button.cancel-variation-changes, button.save-variation-changes').removeAttr('disabled');
        jQuery('#variable_product_options').trigger('woocommerce_variations_input_changed');

        jQuery(this).parent('tr').remove();

        var last_tr_element = parent_table_element.find("tr:last");

        // Show the add row icon on the last row.
        if (last_tr_element.find("th:last").length) {
            parent_table_element.hide();
            parent_table_element.before('<span class="add_var_g_csp" style="cursor: pointer; margin: 1em;display:block;"><button type="button" class="button">Add New Group-Price Pair </button></span>');
        }
        else if (!last_tr_element.find("td:last").hasClass('add_var_g_csp')) {
            last_tr_element.append('<td class="add_var_g_csp" style="cursor: pointer;"><img src="' + wdm_variable_product_csp_object.plus_image + '"  tabindex="0"></td>');
        }
    });
});
