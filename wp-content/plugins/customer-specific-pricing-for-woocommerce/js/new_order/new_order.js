jQuery(document).ready(function () {
    //For new-oder from dashboard.
    var quantityBasedPricing = {};



    //while editing an existing order fetch and save Quantity-Price Pairs.
    function getQtyBasedPricingForExistingItems(){
     //disable orderitems section
        var cspIds=jQuery('#order_line_items').find('.item').find('.csp_order_item_product_id');
        var product_id      = [];
        var user_id = jQuery( '#customer_user' ).val();

        let length=cspIds.length;
        if (length>0) {
            for(let i=0;i<length;i++){
                product_id.push(cspIds[i].value);
                }   
        }
        var url = wdm_new_order_ajax.ajaxurl;
        jQuery.ajax({
            type: 'POST',
            url: url,
            data: {
                action: 'get_quantity_price_pairs',
                customer_id: user_id,
                product_id: product_id,
                order_id:wdm_new_order_ajax.order_id
            },
            success: function (response) {//response is value returned from php
                response = jQuery.parseJSON(response);

                if( user_id == '' || !(user_id in quantityBasedPricing) ) {
                    quantityBasedPricing[user_id] = {};
                }

                // If there is no error, then process the array
                if(!("error" in response)) {
                    for (var single_product_id in response) {
                      if (response.hasOwnProperty(single_product_id)) {
                        quantityBasedPricing[user_id][single_product_id] = {
                            csp_prices : response[single_product_id].csp_prices,
                            qtyList : response[single_product_id].qtyList,
                            regular_price: response[single_product_id].regular_price,
                        }
                    }
                }
            }

        }
    });

        //enable order items section
    }
    getQtyBasedPricingForExistingItems();


    jQuery('[name=customer_user]').change(function () {

        var customer_id = jQuery('[name=customer_user]').val();
        
        if(customer_id == ''){
            customer_id=0;
        }
        var url = wdm_new_order_ajax.ajaxurl;
        jQuery.ajax({
            type: 'POST',
            url: url,
            data: {
                action: 'get_customer_id',
                customer_id: customer_id,
                order_id:wdm_new_order_ajax.order_id
            },
            success: function (response) {//response is value returned from php

            }
        });

    });

    // On adding product in the order form dashboard.
    jQuery( document.body ).on('wc_backbone_modal_response', function(event, target, order_item){
        var user_id = jQuery( '#customer_user' ).val();
        // var product_id = order_item.add_order_items; // temporary commented
        var $item_table_row  = jQuery('table.widefat tbody tr');
		var product_id      = [];

        $item_table_row.each( function() {
            var item_id = jQuery( this ).find( ':input[name="item_id"]' ).val();

            if(null != item_id) {
                product_id.push(item_id);
            }
        });

            //To get product ids on submission of the form in modal popup in WC V3.4
            if(product_id.length==0)
            {
                jQuery("#add_item_id > option").each(function(i,e){
                product_id.push(jQuery(e).val());
               });
            }

        var url = wdm_new_order_ajax.ajaxurl;
        jQuery.ajax({
            type: 'POST',
            url: url,
            data: {
                action: 'get_quantity_price_pairs',
                customer_id: user_id,
                product_id: product_id,
                order_id:wdm_new_order_ajax.order_id
            },
            success: function (response) {//response is value returned from php
                response = jQuery.parseJSON(response);

                if( user_id == '' || !(user_id in quantityBasedPricing) ) {
                    quantityBasedPricing[user_id] = {};
                }

                // If there is no error, then process the array
                if(!("error" in response)) {
                    for (var single_product_id in response) {
                      if (response.hasOwnProperty(single_product_id)) {
                        quantityBasedPricing[user_id][single_product_id] = {
                            csp_prices : response[single_product_id].csp_prices,
                            qtyList : response[single_product_id].qtyList,
                            regular_price: response[single_product_id].regular_price,
                        }
                    }
                }
            }

        }
    });
});

    //when the product is added to order, gets the specific pricing details for product.
    jQuery('#woocommerce-order-items' ).on('quantity_changed', 'input.quantity', function(data){

        var user_id = jQuery( '#customer_user' ).val();
        var product_id = jQuery(this).closest('tr.item').find('.csp_order_item_product_id').val();
        //If we do not have csp data of a user, then return.
        if( user_id == '' || !(user_id in quantityBasedPricing) || quantityBasedPricing[user_id][product_id]['csp_prices'].length == 0 || !Number.isInteger(Number(this.value))) {
            return;
        }

        var qty = parseInt(this.value, 10);
        if (!(qty < 1) && !isNaN(qty)) {
            price = getApplicablePrice(
                quantityBasedPricing[user_id][product_id]['qtyList'], 
                qty, 
                quantityBasedPricing[user_id][product_id]['csp_prices'], 
                quantityBasedPricing[user_id][product_id]['regular_price']
                );

            if(!isNaN(price)) {
                var price_total = price * qty;
                var coupons = [];
                var $this = jQuery(this);

                jQuery('.wc_coupon_list li a.tips span').each(function(){
                    coupons.push(jQuery(this).html());
                });

                if(coupons.length > 0)
                {
                    jQuery.ajax({
                        type: 'POST',   
                        url: wdm_new_order_ajax.ajaxurl,
                        data: {
                            action: 'wdm_get_discount_amount',
                            quantity: qty,
                            coupons: coupons,
                            product_price: price,
                            price_total: price_total,
                            product_id: product_id,
                            order_id: wdm_new_order_ajax.order_id
                        },
                        success: function (discountTotal) {//response is value returned from php
                            discountTotal = jQuery.parseJSON(discountTotal);
            
                            // alert("response > " + discountTotal);
                            var formattedPrice = parseFloat( accounting.formatNumber( discountTotal , woocommerce_admin_meta_boxes.rounding_precision, '' ) )
                            .toString()
                            .replace( '.', woocommerce_admin.mon_decimal_point );    
                            $this.closest('tr.item').find('input.line_total').val(discountTotal);                            
                        }
                    });
                }


                var formattedPrice = parseFloat( accounting.formatNumber( price_total , woocommerce_admin_meta_boxes.rounding_precision, '' ) )
                .toString()
                .replace( '.', woocommerce_admin.mon_decimal_point );
                $this.closest('tr.item').find('input.line_subtotal').val(formattedPrice);  
                if(coupons.length == 0)
                {
                    $this.closest('tr.item').find('input.line_total').val(formattedPrice);                            
                }                
            }
        }
    });
});
