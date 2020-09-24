jQuery(function($) {
    // for simple product page on front-end.
    var qtyList = JSON.parse(wdm_csp_qty_price_object.qtyList),
    csp_prices = JSON.parse(wdm_csp_qty_price_object.csp_prices),
    regular_price = wdm_csp_qty_price_object.regular_price,
    current_cart_total = wdm_csp_qty_price_object.cart_contents_total,
    currency = wdm_csp_qty_price_object.currency_symbol,
    price;

    //Gets the price for that quantity.
    // Shows the total price of product.
    if ($('[name=quantity]').val() > 1 && !isNaN(parseFloat($('[name=quantity]').val()))) {
        var qty = parseFloat($('[name=quantity]').val());
        price = getApplicablePrice(qtyList, qty, csp_prices, regular_price);
        showPrices(price, qty, currency, current_cart_total);
    }
    //Calculate total price on basis of quantity change.
    $('[name=quantity]').change(function() {
        if (!$('#product_total_price').is(":visible")) {
            $('#product_total_price').show();
        }
        var qty = parseFloat(this.value);
        if (!(qty < 1) && !isNaN(parseFloat(qty))) {
            price = getApplicablePrice(qtyList, qty, csp_prices, regular_price);
            showPrices(price, qty, currency, current_cart_total);
        } else {
            $('#product_total_price').hide();
        }
    });

    //    $( ".single_variation_wrap" ).on( "show_variation", function ( event, variation ) {
    //    // Fired when the user selects all the required dropdowns / attributes
    //    // and a final variation is selected / shown
    //     $('.woocommerce-variation-price').html('<h1>Hello</h1>');
    // } );
});;