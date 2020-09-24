var wctbp_price_update_request = null;
var wctbp_original_content = "";
var wctbp_variation_price_container_exists = false;

jQuery(document).ready(function()
{
	
	if(wctbp.disable_product_page_live_price_display == 'true')
	{
		jQuery('div.woocommerce-variation-price span.price').animate({opacity: '1'}, 0);
		jQuery('div.summary.entry-summary p.price').animate({opacity: '1'}, 0);
		return;
	}
	wctbp_variation_price_container_exists = wctbp.product_type != 'simple' /* jQuery('div.woocommerce-variation-price span.price').length */ > 0 ? true : false;
	if(wctbp_variation_price_container_exists)
		jQuery('div.summary.entry-summary p.price').animate({opacity: '1'}, 0);
	
	jQuery(document).on('change', '.quantity input.qty, #qty', wctbp_set_quantity);
	jQuery(document).on('show_variation', wctbp_set_quantity);
	setTimeout(function(){wctbp_set_quantity(null)}, 1000);
	
	//wcuf
	if(typeof wcuf_options != 'undefined' && wcuf_options.cart_quantity_as_number_of_uploaded_files == 'true' && jQuery('.wcuf_upload_fields_row_element').length > 0)
	{
		jQuery(document).on('change', '.wcuf_quantity_per_file_input', wctbp_on_wcuf_quantity_change);
		jQuery(document).on('wcuf_added_multiple_files', wctbp_on_wcuf_quantity_change);
		jQuery(document).on('wcuf_deleted_file_in_multiple_selection', wctbp_on_wcuf_quantity_change);
	}
});

function wctbp_set_quantity(event)
{
	var quantity = jQuery('.quantity input.qty').length < 1 ? jQuery('#qty').val() : jQuery('.quantity input.qty').val();
	wctbp_on_quantity_change(event, quantity);
}
function wctbp_on_wcuf_quantity_change(event)
{
	var quantity = 0;
	jQuery('.wcuf_quantity_per_file_input').each(function(index, obj)
	{
		quantity += parseInt(jQuery(this).val());
	});
	
	wctbp_on_quantity_change(event, quantity);
}
function wctbp_on_quantity_change(event, quantity)
{
	
	var product_id = /* jQuery('input[name=add-to-cart]').val() */ wctbp.product_id;
	var variation_id = jQuery('input[name=variation_id]').val();
	var random = Math.floor((Math.random() * 1000000) + 999);
	var formData = new FormData();
	formData.append('action', 'wctbp_update_price');
	formData.append('product_id', product_id);
	formData.append('variation_id', variation_id);
	formData.append('quantity', quantity);
	
	if(typeof variation_id !== 'undefined' && variation_id == 0)
		return;
	
	//UI
	//var wctbp_original_content = "";
	if(typeof variation_id !== 'undefined' && wctbp_variation_price_container_exists)
	{
		wctbp_original_content = wctbp_original_content == "" ? jQuery('div.woocommerce-variation-price span.price').html() : wctbp_original_content;
		jQuery('div.woocommerce-variation-price span.price').html(wctbp.wctbp_loading_message).animate({opacity: '1'}, 0);
	}
	else
	{
		wctbp_original_content = wctbp_original_content == "" ? jQuery('div.summary.entry-summary p.price').html() : wctbp_original_content; //div.summary.entry-summary div p.price
		jQuery('div.summary.entry-summary p.price').html(wctbp.wctbp_loading_message);
	}
	wctbp_original_content = wctbp_original_content == wctbp.wctbp_loading_message ? "" : wctbp_original_content;
	
	if(wctbp_price_update_request != null)
		wctbp_price_update_request.abort();
	wctbp_price_update_request = jQuery.ajax({
		url: wctbp.wctbp_ajax_url+"?nocache="+random,
		type: 'POST',
		data: formData,
		async: true,
		success: function (data) 
		{
			
			if(data == "no")
			{
				if (typeof variation_id !== 'undefined' && wctbp_variation_price_container_exists)
					jQuery('div.woocommerce-variation-price span.price').html(wctbp_original_content);
				else
					jQuery('div.summary.entry-summary p.price').html(wctbp_original_content);
			}
			else 
			{
				if(typeof variation_id !== 'undefined' && wctbp_variation_price_container_exists)
					jQuery('div.woocommerce-variation-price span.price').html(data);
				else
					jQuery('div.summary.entry-summary p.price').html(data);
			}
			
			//UI 
			if(typeof variation_id !== 'undefined' && wctbp_variation_price_container_exists)
				jQuery('div.woocommerce-variation-price span.price').animate({opacity: '1'}, 400);
			else
				jQuery('div.summary.entry-summary p.price').animate({opacity: '1'}, 400);
						
		},
		error: function (data) 
		{
			//console.log(data);
			//alert("Error: "+data);
		},
		cache: false,
		contentType: false,
		processData: false
	});	
}