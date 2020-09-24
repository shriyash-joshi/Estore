var cspPrices = Array();
/*This file contains the code to convert WC_PRICE function of php to javascript */
jQuery(document).ready(function () {
    //closing a modal
    jQuery(".csp-close").click(function(){
        jQuery('#cspPriceModal').css('display','none');
    });
    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function(event) {
        if (jQuery(event.target).attr('id') == 'cspPriceModal') {
            jQuery('#cspPriceModal').css('display','none');
        }
    }
    jQuery('.wc-product-table-wrapper').delegate('.product-table-price','click',function() {
        let productRow      =jQuery(this).closest('.product-row');
        let productId       = jQuery(this).attr('name');
        let productTitle    = jQuery(productRow).find('.single-product-link').text();
        let productType     = jQuery(productRow).hasClass('product-type-variable')?"variable":"simple";
        let headColor       = jQuery('table.wc-product-table th').css('background-color');
        let textColor       = jQuery('table.wc-product-table th').css('color');
        jQuery('.csp-modal-header').css('background-color', headColor);
        jQuery('.csp-modal-header, .csp-close').css('color', textColor);
        // jQuery('.csp-loader').css('border-top-color', headColor);
        if (productType=="variable") {
            //check if variation selection is selected if selected get variant id
            let selectedVariant = jQuery(productRow).find('input.variation_id');
            if (selectedVariant.length==0) {
                return 0;
            }

            productId = jQuery(selectedVariant).val();
            if (productId=="" || parseInt(productId)<1 || productId==undefined) {
                jQuery('#cspPriceModal').find('.csp-modal-price-table').empty();
                jQuery('#cspPriceModal').find('.csp-modal-price-table').html("Please select the attributes");
                jQuery('#cspPriceModal').css('display','block');
                return 0;           
            }
            let attributes          = jQuery(productRow).find('select');
            for (let i = 0; i < attributes.length; i++) {
                productTitle += " " + jQuery(attributes[i]).find('option:selected').text() + ",";
                
            }
        }
        jQuery('#cspPriceModal').find('.csp-modal-price-table').empty();
        jQuery('#cspPriceModal').find('.csp-loader').css('display', 'block');
        jQuery('#cspPriceModal').css('display','block');
        //check if price already fetched , display prices if fetched
        if (!jQuery.isEmptyObject(cspPrices[productId])) {
            let tableHtml               ='<table class="pt-csp-table"><tbody>';
            let prices                  = cspPrices[productId];
            productTitle                = '<div><span class="csp-product-title">'+productTitle+'</span></div>';
            jQuery.each(prices, function(index, value){
                tableHtml += '<tr><td>'+index+' Or More </td><td>'+cspFormatPrice(value.toFixed(2))+'/Item</td></tr>';
            });
            tableHtml += '</tbody></table>';

            jQuery('#cspPriceModal').find('.csp-modal-price-table').html(productTitle+tableHtml);
            jQuery('#cspPriceModal').find('.csp-loader').css('display', 'none');
            return true;
        }
        //if csp not exist try to fetch
        jQuery.ajax({
            type: 'POST',
            url: wdm_csp_pt_object.ajax_url, 
            data: {
                action:     'pt_get_csp_price_for_product_id',
                pid:        productId,
                productName: productTitle,
                },
            success: function (response) {//response is value returned from php
                let productPrices               = JSON.parse(response);
                let prices                      = productPrices['prices'];
                let productTitle                = productPrices['title'];
                let tableHtml                   = '<table class="pt-csp-table"><tbody>';
                cspPrices[productId]            = prices;
                productTitle                    = '<div><span class="csp-product-title">'+productTitle+'</span></div>';
                
                jQuery.each(prices, function(index, value){
                    tableHtml += '<tr><td>'+index+' Or More </td><td>'+cspFormatPrice(value.toFixed(2))+'/Item</td></tr>';
                });
                tableHtml += '</tbody></table>';
                jQuery('#cspPriceModal').find('.csp-modal-price-table').html(productTitle+tableHtml);
                jQuery('#cspPriceModal').find('.csp-loader').css('display', 'none');
            }
        });
    });


    jQuery('.wc-product-table-wrapper').delegate('.add-to-cart-button > .quantity > input.qty','change',function() {
            let productRow      = jQuery(this).closest('.product-row');
            let productId       = jQuery(productRow).find('div.product-table-price').attr('name');
            let priceField      = jQuery(productRow).find('span.woocommerce-Price-amount');
            let productType     = jQuery(productRow).hasClass('product-type-variable')?"variable":"simple";

            if (productType=="variable") {
                //check if variation selection is selected if selected get variant id
                let selectedVariant = jQuery(productRow).find('input.variation_id');
                if (selectedVariant.length==0) {
                    return 0;
                }
    
                productId = jQuery(selectedVariant).val();
                if (productId=="" || parseInt(productId)<1 || productId==undefined) {
                    return 0;           
                }
                let variantPriceField = jQuery(productRow).find('.woocommerce-variation.single_variation').find('.woocommerce-Price-amount.amount');
                if (variantPriceField.length>0) {
                    priceField = variantPriceField;
                }
            }

            if (priceField.length>1) {
                priceField      = priceField[priceField.length-1];
            }
            jQuery(priceField).html("<div class='csp-loader'></div>");
            let currentQty      = jQuery(this).val();
            
            if (!jQuery.isEmptyObject(cspPrices[productId])) {
                let prices          = cspPrices[productId];
                let price           = getPriceFor(currentQty, prices);
                price               = cspFormatPrice(price.toFixed(2));
                jQuery(priceField).html(price);
                return 0;
            }   

        //if csp not exist try to fetch
        jQuery.ajax({
            type: 'POST',
            url: wdm_csp_pt_object.ajax_url, 
            data: {
                action:     'pt_get_csp_price_for_product_id',
                pid:        productId,
                },
            success: function (response) {//response is value returned from php
                let productPrices               = JSON.parse(response);
                let prices                      = productPrices['prices'];
                cspPrices[productId]            = prices;
                let price                       = parseFloat(productPrices['regularPrice']);
                if (!jQuery.isEmptyObject(prices)) {
                    price           = getPriceFor(currentQty, prices);   
                }
                price               = cspFormatPrice(price.toFixed(2));
                jQuery(priceField).html(price);
            }
        });

        }
    );

    //update prices on variation selections
    jQuery( "table.wc-product-table").delegate("form.variations_form.cart.initialised","show_variation", function ( event, variation ) {
        jQuery(this).find('.add-to-cart-button > .quantity > input.qty').trigger("change");
    } );

});


function getPriceFor(currentQty, prices)
{
    currentQty=parseInt(currentQty);
    let price = 'undefined';
    let indexes = Array();
    if (!jQuery.isEmptyObject(prices)) {
        jQuery.each(prices, function(index, value){
            indexes.push(parseInt(index));
        });
    }

    price = qtyInRange(prices, indexes, currentQty);

    return price;
}

/**
* Returns the price appliacble in between the range of min-quantity.
* @param array qtyList min-qty min quantity list.
* @param int qty quantity of product
* @param array csp_prices quantity pricing apirs.
* @param int/float regular_price regular price of product.
* @return price for that quantity range applicable
*/
function qtyInRange(csp_prices, qtyList, qty) {
    var qtyListSize = qtyList.length;
    for (var i in qtyList) {
        var next = parseInt(i, 10) + 1;
        if (qty > qtyList[i]) {
            if (next != qtyListSize && qty < qtyList[next]) {
                return parseFloat(csp_prices[qtyList[i]]);
            }
            if (next == qtyListSize) {
                return parseFloat(csp_prices[qtyList[i]]);
            }
        } else {
            return parseFloat(csp_prices[qtyList[i]]);
        }
    }
}