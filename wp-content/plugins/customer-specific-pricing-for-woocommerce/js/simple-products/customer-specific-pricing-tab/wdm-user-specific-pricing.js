jQuery('document').ready(function () {
    //For single simple product page: User specific pricing tab.
    wdm_accordion();
    function wdm_accordion() {
        jQuery('.accordion').accordion({
            clearStyle: true,
            heightStyle: "content",
            collapsible: true,
        });
    }

    jQuery('#variable_product_options').on('click', function () {
        wdm_accordion();
    });


    jQuery('.expand_all').on('click', function () {
        wdm_accordion();
    });


    //variable which holds the tbody element of table.
    var scntDiv = jQuery('#wdm_user_specific_pricing_tbody');

    //Add first row of Username And Price
    wdm_temp_select_holder = jQuery(wdm_user_pricing_object.wdm_users_dropdown_html);
    wdm_temp_select_holder.find('option[value="' + wdm_user_pricing_object.wdm_first_username + '"]').attr('selected', true);

    //Start new row
    start_row = "<tr>";

    //Show User dropdown
    select_holder = "<td class='wdm_left_td' ><select name='wdm_woo_username[]' class='chosen-select'>" + wdm_temp_select_holder.html() + "</select></td>";


    //show price type dropdown
    type_holder = "<td class = 'wdm_left_td discount_options'><select name='wdm_price_type[]' class='chosen-select csp_wdm_action'>";

    for(var j = 1; j <= 2; j++) {
        if (wdm_user_pricing_object.wdm_first_user_price_type == j) {
            type_holder += "<option value ='"+j+"' selected>"+wdm_user_pricing_object.discountOptions[wdm_user_pricing_object.wdm_first_user_price_type]+"</option>";
        } else {
            type_holder += "<option value ='"+j+"'>"+wdm_user_pricing_object.discountOptions[j]+"</option>";
        }
    }
    type_holder += "</select></td>";

    //Show Quantity Textbox
    qty_textbox = "<td class='wdm_left_td'><input type='number' min = '1' name='wdm_woo_qty[]' class='wdm_qty' value='" + wdm_user_pricing_object.wdm_first_qty_user + "' ></td>";

    //Show Price's Textbox
    if ( wdm_user_pricing_object.wdm_first_user_price_type == 2 ) {
        price_textbox = "<td class='wdm_left_td'><input type='text' name='wdm_woo_price[]' class='wdm_price csp-percent-discount' value='" + wdm_user_pricing_object.wdm_first_price_of_user + "' /></td>";
    } else {
        price_textbox = "<td class='wdm_left_td'><input type='text' name='wdm_woo_price[]' class='wdm_price' value='" + wdm_user_pricing_object.wdm_first_price_of_user + "' /></td>"; 
    }


    //Show Remove row button
    remove_row_button = "<td class='wdm_left_td'><a class='wdm_remove_pair_link' href='#' id='remScnt'  ><img class='remove_user_price_pair_row_image' alt='Remove Row' title='Remove Row' src='" + wdm_user_pricing_object.remove_image_path + "'/></a>";
    //If only one row is available
    add_new_row = "";
    if (!wdm_user_pricing_object.more_than_one_row) {
        add_new_row = "<a class='wdm_add_pair_link' href='#' id='wdm_add_new_user_price_pair'><img class='add_new_row_image' src='" + wdm_user_pricing_object.add_image_path + "' /></a>";
    }

    end_row = "</td></tr>";

    scntDiv.prepend(start_row + select_holder + type_holder + qty_textbox + price_textbox + remove_row_button + add_new_row + end_row);

    wdm_temp_select_holder = null;
    wdm_temp_html_holder = null;
    if (typeof chosen === "function") {
        jQuery(".chosen-select").chosen({'width': '200px'});
    }

    //Get total number of Username-Price Pair rows
    var i = jQuery('#wdm_user_specific_pricing_tbody tr').size() + 1;



    //Add New row on clicking 'Add New User-Price Pair button
    jQuery('#userSpecificPricingTab_data').delegate('#wdm_add_new_user_price_pair', 'click', function () {
        //Show thead if it is hidden

        if (i === 1) {
            jQuery(".username_price_thead").show();
        }

        if (!jQuery(this).attr('type')=='button' || jQuery(this).attr('type')==undefined) {
            let qtyField=jQuery(this.parentElement.parentElement.children[2].children[0]);
            if(!entereredValidCSPQuantity(qtyField))
            {
                jQuery(qtyField).tipTip({content:wdmScpMessages['quantityMessage'],defaultPosition:'top'});
                jQuery( qtyField ).mouseover();
                return false;
            }
        }
        //Start new row
        start_row = "<tr>";

        //Show User dropdown
        select_holder = "<td class='wdm_left_td' >" + wdm_user_pricing_object.wdm_users_dropdown_html + "</td>";

        //show price type dropdown
        type_holder = "<td class = 'wdm_left_td discount_options'><select name='wdm_price_type[]' class='chosen-select csp_wdm_action'>";
        
        for(var j = 1; j <= 2; j++) {
            if (j == 1) {
                type_holder += "<option value ='"+j+"' selected>"+wdm_user_pricing_object.discountOptions[j]+"</option>";
            } else {
                type_holder += "<option value ='"+j+"'>"+wdm_user_pricing_object.discountOptions[j]+"</option>";
            }
        }
        type_holder += "</select></td>";

        //Show Quantity Textbox
        qty_textbox = "<td class='wdm_left_td'><input type='number' min = '1' name='wdm_woo_qty[]' class='wdm_qty' value = '' /></td>";

        //Show Price's Textbox
        price_textbox = "<td class='wdm_left_td'><input type='text' name='wdm_woo_price[]' class='wdm_price' /></td>";

        //Show Remove row button
        remove_row_button = "<td><a class='wdm_remove_pair_link' href='#' id='remScnt'  ><img class='remove_user_price_pair_row_image' alt='Remove Row' title='Remove Row' src='" + wdm_user_pricing_object.remove_image_path + "'/></a>";

        //Add new pair button
        add_new_row = "<a class='wdm_add_pair_link' href='#' id='wdm_add_new_user_price_pair'><img class='add_new_row_image' alt='Add Row' title='Add Row' src='" + wdm_user_pricing_object.add_image_path + "' /></a>";

        //Lets remove add_new_pair_btn associated with earlier row
       // jQuery(".wdm_add_pair_link").remove();

        //end row
        end_row = "</td></tr>";

        jQuery(start_row + select_holder + type_holder + qty_textbox + price_textbox + remove_row_button + add_new_row + end_row).appendTo(scntDiv);
        var parent_table_element = jQuery("table.wdm_simple_product_usp_table");
        parent_table_element.show();
        
        jQuery(this).remove();

        if (typeof chosen === "function") {
            jQuery(".chosen-select").chosen({'width': '200px'});
        }
        i++;
        return false;
    }); //end live


    // //When User edits the invalid field, clear the background of that field
    // jQuery('#userSpecificPricingTab_data').delegate('.wdm_price', 'focusin', function () {
    //     jQuery(this).removeClass('wdm_error');
    // }); //end delegate


    //If Remove Row or minus image is clicked, the corresponding row is removed.
    jQuery('#userSpecificPricingTab_data').delegate('#remScnt', 'click', function ()
    {
        var parent_table_element = jQuery(this).parents("table.wdm_simple_product_usp_table");
        user_price_pair_removal_element_var = jQuery(this);
        let qtyField =jQuery(this.parentElement.parentElement.children[2].children[0]);
        jQuery(qtyField).mouseout();
        //hide thead when there are no pairs of username and price
        if (i <= 2) {
            jQuery(".username_price_thead").hide();
        }

        user_price_pair_removal_element_var.parents('tr').remove();

        //Append Add new pair button to second last row after last row is removed
        if (!jQuery("#wdm_user_specific_pricing_tbody tr:last td:last .wdm_add_pair_link").length) {
            jQuery("#wdm_user_specific_pricing_tbody tr:last td:last").append("<a class='wdm_add_pair_link' href='#' id='wdm_add_new_user_price_pair'><img class='add_new_row_image' src='" + wdm_user_pricing_object.add_image_path + "' /></a>");
        }

        var last_tr_element = parent_table_element.find("tr:last");


        if (last_tr_element.find("th:last").length) {
            parent_table_element.hide();
            parent_table_element.before('<button type="button" class="button" id="wdm_add_new_user_price_pair">'+wdm_user_pricing_object.add_new_customer_text+'</button>');
        }
        else if (!jQuery("#wdm_user_specific_pricing_tbody tr:last td:last .wdm_add_pair_link").length) {
            jQuery("#wdm_user_specific_pricing_tbody tr:last td:last").append("<a class='wdm_add_pair_link' href='#' id='wdm_add_new_user_price_pair'><img class='add_new_row_image' src='" + wdm_user_pricing_object.add_image_path + "' /></a>");
        }

        i--;

        return false;
    }); //end live
});