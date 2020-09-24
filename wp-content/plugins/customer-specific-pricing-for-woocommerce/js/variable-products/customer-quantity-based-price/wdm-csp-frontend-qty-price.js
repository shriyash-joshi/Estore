jQuery(function($){
    //Variable single product page.
    var quantityList		= JSON.parse(wdm_csp_qty_price_object.qtyList),
    	cspPrices	= JSON.parse(wdm_csp_qty_price_object.csp_prices),
    	minQuantity	= JSON.parse(wdm_csp_qty_price_object.minimum),
    	regularPrices	= wdm_csp_qty_price_object.regular_price,
        current_cart_total = wdm_csp_qty_price_object.cart_contents_total,
        currency = wdm_csp_qty_price_object.currency_symbol,
        regularPriceHtmls=wdm_csp_qty_price_object.regular_price_html,
        cspDiscriptionHtmls=wdm_csp_qty_price_object.csp_price_discriptions,
        price, qtyList, csp_prices, regular_price;

        wdm_csp_qty_price_object.price_suffix = (wdm_csp_qty_price_object.price_suffix)?wdm_csp_qty_price_object.price_suffix:'';
    // Changes the total price on quantity change.

    $('[name=quantity]').change(function(){

    	if (!$('#product_total_price').is(":visible")) {
    		$('#product_total_price').show();
    	}
    	var qty = parseInt(this.value, 10);
        if (!(qty < 1) && !isNaN(qty)) {
            if (typeof csp_prices != 'undefined' && typeof regular_price != 'undefined') {
                price = regular_price;
                if(typeof qtyList != 'undefined') {
                        //Gets the applicable price for quantity.
                    price = getApplicablePrice(qtyList, qty, csp_prices, regular_price);
                }
                    //Calculate the total price for the product.
                showPrices(price, qty, currency, current_cart_total);
            }
        } else {
        	$('#product_total_price').hide();
        }
    });

    $( ".single_variation_wrap" ).on( "hide_variation", function (event) {
        $('#product_total_price').hide();
        if (!wdm_csp_qty_price_object.is_wp_min_max_active) {
            jQuery('.quantity > input[name="quantity"]').attr('min',1);
            jQuery('.quantity > input[name="quantity"]').val(1);   
        }
    });

    $( ".single_variation_wrap" ).on( "show_variation", function (event, variation, purchasable) {
    // Fired when the user selects all the required dropdowns / attributes
    // and a final variation is selected / shown

        //set default quantity values
        if (!wdm_csp_qty_price_object.is_wp_min_max_active) {
            jQuery('.quantity > input[name="quantity"]').attr('min',1);
            jQuery('.quantity > input[name="quantity"]').val(1);
        }
        

	    $('#product_total_price').show();
	    qtyList 		    = quantityList[variation.variation_id];
	    csp_prices 		    = cspPrices[variation.variation_id];
	    min 			    = minQuantity[variation.variation_id];
        regular_price 	    = regularPrices[variation.variation_id];
        regularPriceHtml    = undefined==regularPriceHtmls[variation.variation_id]?'':regularPriceHtmls[variation.variation_id];
        cspDiscriptionHtml  = cspDiscriptionHtmls[variation.variation_id];
        var prod_real_regular_price   = variation.display_regular_price;
        var strikeThrough   = '';
        var csp_keys        = Object.keys(csp_prices);
        var csp_length      = csp_keys.length;

        if (regular_price==undefined) {
            regular_price=prod_real_regular_price;
            var minQty=csp_keys[0];
            jQuery('.quantity > input[name="quantity"]').attr('min',minQty);
            jQuery('.quantity > input[name="quantity"]').val(minQty);
        }
        if (!empty(csp_prices)) {
            if(csp_length == 1 && csp_keys[0] == 1) {
                var current_price = parseFloat(csp_prices[csp_keys[0]]);
                if('1' == wdm_csp_function_object.is_strikethrough_enabled && current_price < prod_real_regular_price) {
                    strikeThrough = "<del><span class='woocommerce-Price-amount amount csp-price'>"+ cspFormatPrice(prod_real_regular_price) + wdm_csp_qty_price_object.price_suffix + "</span></del>";
                }

                jQuery('.woocommerce-variation-price').html("<span class='price'>" + strikeThrough + "<ins><span class='woocommerce-Price-amount amount csp-price'>" + cspFormatPrice(current_price) + wdm_csp_qty_price_object.price_suffix + "</span></ins></span>");
                
                if(undefined!=cspDiscriptionHtml)
                {jQuery('.woocommerce-variation-price').prepend(cspDiscriptionHtml);}
                if (undefined!=regularPriceHtml) 
                {jQuery('.woocommerce-variation-price').prepend(regularPriceHtml);}
                
                jQuery('#product_total_price .price').html( currency + current_price.toFixed(2));
            } else {
                displayTable(min, qtyList, csp_prices, regular_price, regularPriceHtml, cspDiscriptionHtml);
            } 
        } 
        $('[name=quantity]').change();
	} );

    /**
    * Displays the table for quantity wise prices.
    * @param int min minimum quantity
    * @param array qtyList quantity list.
    * @param array csp_prices CSP quantity Pricing array
    * @param float regular_price Regular Price.
    */
    function displayTable(min, qtyList, csp_prices, regular_price, regularPriceHtml, cspDiscriptionHtml)
    {
        var table = showQtyPriceTable(min, qtyList, csp_prices, regular_price, regularPriceHtml, cspDiscriptionHtml);
        $('.woocommerce-variation-price').html(table);
    }

    /**
    * Shows the quantity specific pricing for the Product
    * @param int min minimum quantity
    * @param array qtyList quantity list.
    * @param array csp_prices CSP quantity Pricing array
    * @param float regular_price Regular Price.
    */
    function showQtyPriceTable(min, qtyList, csp_prices, regular_price, regularPriceHtml, cspDiscriptionHtml)
    {
        if (csp_prices !== 'undefined') {
        	var current_qty = parseInt($('[name=quantity]').val(), 10);
			if (current_qty == 1) {
				var current_price = getApplicablePrice(qtyList, current_qty, csp_prices, regular_price);
				jQuery('#product_total_price .price').html( currency + current_price.toFixed(2));		
			}
            // purchasable = true;
            let regular_price_html ="";
            if (wdm_csp_qty_price_object.show_regular_price) {
                regular_price_html=regularPriceHtml;
            }

            var table = cspDiscriptionHtml + regular_price_html+'<div class = "qty-fieldset"><h1 class = "qty-legend"><span>' + wdm_csp_qty_price_object.quantity_discount_text + '</span></h1><div class="qty_table_container"><table class = "qty_table">';


            for (var qty in csp_prices) {
            	qty = parseInt(qty, 10);
            	if(!isNaN(qty)) {
	                var price = csp_prices[qty];
	                table += "<tr>";
                    table += "<td class = 'qty-num'>"+qty+" "+wdm_csp_qty_price_object.more_text+" </td><td class = 'qty-price'>"+ cspFormatPrice(price) + wdm_csp_qty_price_object.price_suffix + "</td>";
	                table += "</tr>";
            	}
            }
            table += "</table></div></div>";
            return table;
        }
        return regular_price;
    }
});
