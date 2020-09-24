jQuery(document).ready(function()
{
	//console.log(vanquish_activator_settings);
	jQuery(document).on('click', '#activation_button', vanquish_on_activation_click);
});
function vanquish_on_activation_click(event)
{
	event.stopPropagation();
	event.preventDefault();
	
	if(jQuery('#input_purchase_code').val() == "" || jQuery('#input_buyer').val()  == "")
	{
		alert(vanquish_activator_settings.empty_fields_error);
		return false;
	}
	//UI
	jQuery('#activation_button, .input_field').prop('disabled', true);
	vanquish_set_status_message(vanquish_activator_settings.status_default_message);
	
	
	var formData = new FormData();
	formData.append('purchase_code', jQuery('#input_purchase_code').val());	
	formData.append('buyer', jQuery('#input_buyer').val());				
	formData.append('domain', jQuery('#domain').val());				
	formData.append('item_id', jQuery('#item_id').val());	
	//console.log(vanquish_activator_settings.verifier_url+"?"+(new URLSearchParams(formData).toString()));
	jQuery.ajax({
		url: vanquish_activator_settings.verifier_url+"?"+(new URLSearchParams(formData).toString()),
		type: 'GET',
		dataType: 'jsonp',
		jsonpCallback: "activation",
		crossDomain: true,
		//data: formData,
		async: true,
		success: function (data) 
		{			
			//console.log(data);
			//result = JSON.parse(data);
			result = data;
			switch(result.code)
			{
				case "ok": vanquish_set_status_message(vanquish_activator_settings.purchase_code_valid); 
						   vanquish_activation_complete(jQuery('#item_id').val(), jQuery('#domain').val()); 
						   return;
						   break;
				case "db_connection_error": ;
				case "db_connection_error_select": ;
				case "db_connection_error_update": vanquish_set_status_message(vanquish_activator_settings.db_error); break;
				case "invalid_buyer": vanquish_set_status_message(vanquish_activator_settings.buyer_invalid); break;
				case "invalid_item_id": vanquish_set_status_message(vanquish_activator_settings.purchase_code_invalid); break;
				case "invalid_purchase_code": vanquish_set_status_message(vanquish_activator_settings.purchase_code_invalid); break;
				case "max_num_domain_reached": vanquish_set_status_message(vanquish_activator_settings.num_domain_reached+result.domains); break;
			}
			
			//UI
			jQuery('#activation_button, .input_field').prop('disabled', false);
			//jQuery('#status').fadeOut();
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
	
	return false;
}
function vanquish_set_status_message(message)
{
	jQuery('#status').html(message);
	jQuery('#status').fadeIn();
}
function vanquish_activation_complete(id, domain)
{
	var formData = new FormData();
	formData.append('action', "vanquish_activation_"+id);	
	formData.append('id', id);	
	formData.append('domain', domain);	
	
	jQuery.ajax({
		url: ajaxurl,
		type: 'POST',
		data: formData,
		async: true,
		success: function (data) 
		{
			location.reload();
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