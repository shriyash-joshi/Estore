jQuery( function () {
    //Role Specific pricing tab for simple products.
    var wdm_temp_select_holder = null;
    var wdm_temp_html_holder = null;
    var role_scntDiv = jQuery( '#wdm_role_specific_pricing_tbody' );
    wdm_temp_select_holder = wdm_role_pricing_object.wdm_roles_dropdown_html;

    //Start new row
    start_row = "<tr>";

    //Show User Role dropdown
    select_holder = "<td class='wdm_left_td' ><select name='wdm_woo_rolename[]' class='chosen-select'>" + wdm_temp_select_holder + "</select></td>";

    //show price type dropdown
    type_holder = "<td class = 'wdm_left_td discount_options'><select name='wdm_role_price_type[]' class='chosen-select csp_wdm_action'>";
    for(var j = 1; j <= 2; j++) {
        if (wdm_role_pricing_object.wdm_first_role_price_type == j) {
            type_holder += "<option value ='"+j+"' selected>"+wdm_role_pricing_object.discountOptions[wdm_role_pricing_object.wdm_first_role_price_type]+"</option>";
        } else {
            type_holder += "<option value ='"+j+"'>"+wdm_role_pricing_object.discountOptions[j]+"</option>";
        }
    }
    type_holder += "</select></td>";

    //Show Quantity Textbox
    qty_textbox = "<td class='wdm_left_td'><input type='number' min = '1' name='wdm_woo_role_qty[]' class='wdm_qty' value='" + wdm_role_pricing_object.wdm_first_role_qty + "' /></td>";

    //Show Price's Textbox
    if ( wdm_role_pricing_object.wdm_first_role_price_type == 2 ) {
        price_textbox = "<td colspan=3 class='wdm_left_td'><input type='text' name='wdm_woo_role_price[]' class='wdm_price csp-percent-discount' value='" + wdm_role_pricing_object.wdm_first_role_price + "' /></td>";
    } else {
        price_textbox = "<td colspan=3 class='wdm_left_td'><input type='text' name='wdm_woo_role_price[]' class='wdm_price' value='" + wdm_role_pricing_object.wdm_first_role_price + "' /></td>";        
    }

    //Show Remove row button
    remove_row_button = "<td><a class='wdm_remove_pair_link' href='#' id='role_remScnt'  ><img class='remove_user_price_pair_row_image' alt='Remove Row' title='Remove Row' src='" + wdm_role_pricing_object.remove_image_path + "'/></a>";

    //If only one row is available
    add_new_row = "";
    if ( !wdm_role_pricing_object.more_than_one_row ) {
        add_new_row = "<a class='wdm_add_role_pair_link' href='#' id='wdm_add_new_role_price_pair'><img class='add_new_row_image' src='" + wdm_role_pricing_object.add_image_path + "' /></a>";
    }

    end_row = "</td></tr>";

// Add the elements for role specific tab.
    role_scntDiv.prepend( start_row + select_holder + type_holder + qty_textbox + price_textbox + remove_row_button + add_new_row + end_row );
    wdm_temp_select_holder = null;
    wdm_temp_html_holder = null;
    if ( typeof chosen === "function" ) {
        jQuery( ".chosen-select" ).chosen( { 'width': '200px' } );
    }

    //Get total number of Username-Price Pair rows
    var i = jQuery( '#wdm_role_specific_pricing_tbody tr' ).size() + 1;


    //Add New row on clicking 'Add New User-Price Pair button
    //jQuery( '#wdm_add_new_role_price_pair' ).live( 'click', function () {
    jQuery( '#userSpecificPricingTab_data' ).delegate('#wdm_add_new_role_price_pair','click', function () {
        //Show thead if it is hidden
        if ( i === 1 ) {
            jQuery( ".role_price_thead" ).show();
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
        select_holder = "<td class='wdm_left_td' ><select name='wdm_woo_rolename[]' class='chosen-select'>" + wdm_role_pricing_object.wdm_roles_dropdown_html + "</select></td>";

        //show price type dropdown
        type_holder = "<td class = 'wdm_left_td discount_options'><select name='wdm_role_price_type[]' class='chosen-select csp_wdm_action'>";
        for(var j = 1; j <= 2; j++) {
            if (j == 1) {
                type_holder += "<option value ='"+j+"' selected>"+wdm_role_pricing_object.discountOptions[j]+"</option>";
            } else {
                type_holder += "<option value ='"+j+"'>"+wdm_role_pricing_object.discountOptions[j]+"</option>";
            }
        }
        type_holder += "</select></td>";

        //Show Quantity Textbox
        qty_textbox = "<td class='wdm_left_td'><input type='number' min = '1' name='wdm_woo_role_qty[]' class='wdm_qty' value = '' /></td>";
       
        //Show Price's Textbox
        price_textbox = "<td colspan=3 class='wdm_left_td'><input type='text' name='wdm_woo_role_price[]' class='wdm_price' /></td>";

        //Show Remove row button
        remove_row_button = "<td><a class='wdm_remove_pair_link' href='#' id='role_remScnt'  ><img class='remove_user_price_pair_row_image' alt='Remove Row' title='Remove Row' src='" + wdm_role_pricing_object.remove_image_path + "'/></a>";

        //Add new pair button
        add_new_row = "<a class='wdm_add_role_pair_link' href='#' id='wdm_add_new_role_price_pair'><img class='add_new_row_image' src='" + wdm_role_pricing_object.add_image_path + "' /></a>";

        //Lets remove wdm_add_role_pair_link associated with earlier row
        jQuery( ".wdm_add_role_pair_link" ).remove();

        //end row
        end_row = "</td></tr>";

        // Add fields when adding a new row for role specific tab
        jQuery( start_row + select_holder + type_holder + qty_textbox + price_textbox + remove_row_button + add_new_row + end_row ).appendTo( role_scntDiv );
        var parent_table_element = jQuery("table.wdm_simple_product_rsp_table");
        parent_table_element.show();
        jQuery(this).remove();
        if ( typeof chosen === "function" ) {
            jQuery( ".chosen-select" ).chosen( { 'width': '200px' } );
        }
        i++;
        return false;
    } ); //end live

    //If Remove Row image is clicked, the corresponding row is removed.
    jQuery('#userSpecificPricingTab_data').delegate('#role_remScnt', 'click', function () 
    {
        var parent_table_element = jQuery(this).parents('table.wdm_simple_product_rsp_table');
        role_price_pair_removal_element_var = jQuery( this );
        let qtyField =jQuery(this.parentElement.parentElement.children[2].children[0]);
        jQuery(qtyField).mouseout();
        //hide thead when there are no pairs of rolename and price
        if ( i <= 2 ) {
            jQuery( ".role_price_thead" ).hide();
        }

        role_price_pair_removal_element_var.parents( 'tr' ).remove();

        //Append Add new pair button to second last row after last row is removed
        if ( !jQuery( "#wdm_role_specific_pricing_tbody tr:last td:last .wdm_add_role_pair_link" ).length ) {
            jQuery( "#wdm_role_specific_pricing_tbody tr:last td:last" ).append( "<a class='wdm_add_role_pair_link' href='#' id='wdm_add_new_role_price_pair'><img class='add_new_row_image' src='" + wdm_role_pricing_object.add_image_path + "' /></a>" );
        }

        var last_tr_element = parent_table_element.find("tr:last");


        if (last_tr_element.find("th:last").length) {
            parent_table_element.hide();
            parent_table_element.before('<button type="button" class="button" id="wdm_add_new_role_price_pair">'+wdm_role_pricing_object.add_new_role_text+'</button>');
        }
        else if ( !jQuery( "#wdm_role_specific_pricing_tbody tr:last td:last .wdm_add_role_pair_link" ).length ) {
            jQuery( "#wdm_role_specific_pricing_tbody tr:last td:last" ).append( "<a class='wdm_add_role_pair_link' href='#' id='wdm_add_new_role_price_pair'><img class='add_new_row_image' src='" + wdm_role_pricing_object.add_image_path + "' /></a>" );
        }
        i--;

        return false;
    } ); //end live
} );
