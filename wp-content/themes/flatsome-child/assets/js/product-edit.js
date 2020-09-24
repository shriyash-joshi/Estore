// to trigger product type change on program type selection
// don't make changes here
jQuery('.acf-field-5f03a0268bedc select').on('change',function(){
    var selectedProduct =  jQuery(this).val();
    if( selectedProduct !== undefined && selectedProduct =='normal'){
        jQuery('#product-type').val("simple").prop('selected',true).trigger('change');
    }
    else  if( selectedProduct !== undefined && selectedProduct =='combo'){
        jQuery('#product-type').val("bundle").prop('selected',true).trigger('change');
    }

    else  if( selectedProduct !== undefined && selectedProduct =='batch'){
        jQuery('#product-type').val("variable").prop('selected',true).trigger('change');
    }

    else  if( selectedProduct !== undefined && selectedProduct =='external'){
        jQuery('#product-type').val("external").prop('selected',true).trigger('change');
    }

    else {
        jQuery('#product-type').val("simple").prop('selected',true).trigger('change');
    }

});
