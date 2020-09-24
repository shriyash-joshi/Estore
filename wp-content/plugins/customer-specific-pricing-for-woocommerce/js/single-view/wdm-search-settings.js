var cspRuleDel;
(function( $ ) {
	'use strict';
	/**
	 * All of the code for your admin-specific JavaScript source
	 * should reside in this file.
	 *
	 * Note that this assume you're going to use jQuery, so it prepares
	 * the $ function reference to be used within the scope of this
	 * function.
	 *
	 * From here, you're able to define handlers for when the DOM is
	 * ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * Or when the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and so on.
	 *
	 * Remember that ideally, we should not attach any more than a single DOM-ready or window-load handler
	 * for any particular page. Though other scripts in WordPress core, other plugins, and other themes may
	 * be doing this, we should try to minimize doing that in our own work.
	 */

	 $(function() {
	 	//On changing the selection of the rule-type.
	 	$('#wdm_setting_option_type').change(function(){

			$('input.wdm-remove-sel-csp-price').hide();

			var option_type = jQuery(this).val();
			$('.wdm-csp-single-view-result-wrapper').empty();
			$('#loading').remove();

			if(option_type != -1)
			{
				$(this).after('<img src="' + single_view_obj.loading_image_path + '" id="loading"/>');

				//Send AJAX request

				jQuery.ajax({
		            type: 'POST',
		            url: single_view_obj.admin_ajax_path,
		            data: {
		                action: 'get_search_selection_result',
		                option_type : option_type,
		                single_view_action : 'search'
		            },
		            success: function (response) {//response is value returned from php
		               $('#loading').remove();
		               $('.wdm-csp-single-view-result-wrapper').append(response);
		            }
		        });
			}

		});//option type selection end

		$('body').delegate('#selected-list_wdm_selections','change',function(){
			var option_type = $('#wdm_setting_option_type').val();
			var selection_name = $(this).val();

			$('input.wdm-remove-sel-csp-price').hide();
			$('.wdm-selection-price-list-wrapper').remove();
			$('#loading').remove();

			if(selection_name != -1)
			{
				$(this).after('<img src="' + single_view_obj.loading_image_path + '" id="loading"/>');

				//Send AJAX request
				// displaySearchSelection(option_type, selection_name);
				jQuery.ajax({

		            type: 'POST',
		            url: single_view_obj.admin_ajax_path,
		            dataType : 'json',
		            data: {
		                action: 'display_product_prices_selection',
		                option_type : option_type,
		                selection_name : selection_name
		            },
		            success: function (response) {//response is value returned from php
		               $('#loading').remove();
		               $('<div class="wdm-selection-price-list-wrapper"></div>').appendTo($('.wdm-csp-single-view-result-wrapper'));

		               	$('.wdm-selection-price-list-wrapper').append( '<table cellpadding="0" cellspacing="0" border="0" class="display" id="example"></table>');

			        	var table = jQuery( '#example' ).dataTable( {
							"data": response,
							"columns": single_view_obj.title_names,
							"lengthMenu": [[10, 25, 50, 100, 1000, 5000, -1], [10, 25, 50, 100, 1000, 5000, "All"]],
							'order': [[1, 'asc']],
							//dom: 'Bfrtip',
							"columnDefs": [
								{
									'targets': 0,
									'className': 'dt-body-center',
								},
								{
									'targets': [0,7],
									'searchable': false,
									'orderable': false,
								},
								{
									"targets": 5,
									"render": function ( data, type, full, meta ) {
										if(data === '--')
											return data;
										else if('category' == data) {
											return data;
										}
										else
										return '<a href="'+single_view_obj.query_log_link+data+'&selection_name='+selection_name+'">'+data+'</a>';
									}
								}
							],
							'language':{
							'lengthMenu': single_view_obj.length_menu,
							'info': single_view_obj.showing_info,
							'infoEmpty': single_view_obj.info_empty,
							'emptyTable': single_view_obj.empty_table,
							'infoFiltered': single_view_obj.info_filtered,
							'zeroRecords': single_view_obj.zero_records,
							'loadingRecords': single_view_obj.loading_records,
							'processing': single_view_obj.processing,
							'search': single_view_obj.search,
							'paginate': {
						        "first":        single_view_obj.first,
						        "previous":     single_view_obj.prev,
						        "next":         single_view_obj.next,
						        "last":         single_view_obj.last
						    },
						},
						});

						$('input.wdm-remove-sel-csp-price').show(); // code added
						$('.csp-help-tip').tipTip({
							'attribute': 'data-tip',
							'maxWidth': '111px',
							'fadeIn': 50,
							'fadeOut': 50,
							'delay': 200
						});
						// hideDeleteDisableSelAllCb(); // code commented						
		            }
		        });
			}

		});//Selection type selection end

		// function displaySearchSelection(option_type, selection_name)
		// {
		// 	jQuery.ajax({

	 //            type: 'POST',
	 //            url: single_view_obj.admin_ajax_path,
	 //            dataType : 'json',
	 //            data: {
	 //                action: 'display_product_prices_selection',
	 //                option_type : option_type,
	 //                selection_name : selection_name
	 //            },
	 //            success: function (response) {//response is value returned from php
	 //               $('#loading').remove();
	 //               $('<div class="wdm-selection-price-list-wrapper"></div>').appendTo($('.wdm-csp-single-view-result-wrapper'));

	 //               	$('.wdm-selection-price-list-wrapper').append( '<table cellpadding="0" cellspacing="0" border="0" class="display" id="example"></table>');

		//         	var table = jQuery( '#example' ).dataTable( {
		// 				"data": response,
		// 				"columns": single_view_obj.title_names,
		// 				//dom: 'Bfrtip',
		// 				"columnDefs": [ { "targets": 3,
		// 				"render": function ( data, type, full, meta ) {
		// 					if(data === '--')
		// 						return data;
		// 					else
		// 				      return '<a href="'+single_view_obj.query_log_link+data+'">'+data+'</a>';
		// 				    }
		// 				}],
		// 				'language':{
		// 				'lengthMenu': single_view_obj.length_menu,
		// 				'info': single_view_obj.showing_info,
		// 				'infoEmpty': single_view_obj.info_empty,
		// 				'emptyTable': single_view_obj.empty_table,
		// 				'infoFiltered': single_view_obj.info_filtered,
		// 				'zeroRecords': single_view_obj.zero_records,
		// 				'loadingRecords': single_view_obj.loading_records,
		// 				'processing': single_view_obj.processing,
		// 				'search': single_view_obj.search,
		// 				'paginate': {
		// 			        "first":        single_view_obj.first,
		// 			        "previous":     single_view_obj.prev,
		// 			        "next":         single_view_obj.next,
		// 			        "last":         single_view_obj.last
		// 			    },
		// 			},
		// 			});
	 //            }
	 //        });
		// }

		// Handle click on "Select all" control checkbox.
		$(document).on('click', 'thead input[name="select_all"]', function(e){
			if(this.checked){
				$('#example tbody input[type="checkbox"]:not(:checked)').trigger('click');
			} else {
				$('#example tbody input[type="checkbox"]:checked').trigger('click');
			}
		});

		// Handle click on checkbox.
		$(document).on('click', '#example tbody input[type="checkbox"]', function(){
			var $chkbox 	= $(this);
			var $tr_wrapper = $(this).closest('tr') ;
			updateDataTableSelectAllCtrl();

			if (true == $chkbox.prop("checked")) {
				$tr_wrapper.addClass('selected');
			} else {
				$tr_wrapper.removeClass('selected');
			}
		});

		// When remove trash icon is clicked to remove the CSP price.
		$(document).on('click', '#example tbody a.wdm-csp-rm-record:not(.wdm-csp-cat-record)', function(){
			var option_type    	= $('#wdm_setting_option_type').val();
			var selection_name 	= $('#selected-list_wdm_selections').val();
			// Used to decide whether the CSP record is 'Role specific', 'Group
			// Specific' or 'Customer Specific'.
			var role_group		= $(this).data('role-group');
			var deleteRecord;
			if('customer' == option_type) {
				if ('Role' == role_group) {
					deleteRecord = confirm(single_view_obj.remove_rec_role_conf_txt);
				} else if ('Group' == role_group) {
					deleteRecord = confirm(single_view_obj.remove_rec_group_conf_txt);
				} else {
					deleteRecord = confirm(single_view_obj.remove_rec_conf_txt);
				}
			} else {
				deleteRecord = confirm(single_view_obj.remove_rec_conf_txt);
			}

			// If user cancels to delete the record.
			if (deleteRecord == false) {
				return;
			}

			var record_data = {
				product_id: $(this).data('product-id'),
				min_qty: $(this).data('min-qty'),
				act_price: $(this).data('act-price'),
				dis_type: $(this).data('dis-type'),
				rule_no: $(this).data('rule-no'),
				csp_source: $(this).data('source'),
			}

			$(this).after('<img src="' + single_view_obj.loading_image_path + '" id="loading"/>');
			cspRuleDel = $(this).closest('tr');
			jQuery.ajax({
				type: 'POST',
				url: single_view_obj.admin_ajax_path,
				data: {
					action: 'remove_single_csp_price',
					option_type : option_type,
					selection_name : selection_name,
					record_data: record_data,
				},
				success: function (response) {//response is value returned from php
					$('#loading').remove();	
					$(cspRuleDel).hide(500);			
				   	$('body #selected-list_wdm_selections').click();
				}
			});
		});

		/* When 'Delete' (input#remove-sel-csp-price) button is clicked to
		 * remove the selected CSP record(s).
		 */
		$('input#remove-sel-csp-price').click(function(){
			var $chkbox_checked    = $('table#example tbody input[type="checkbox"]:checked');
			// If none of the records is selected.
			if (! $chkbox_checked.length >= 1) {
				alert(single_view_obj.error_selection_empty);
				return false;
			}

			var option_type    	= $('#wdm_setting_option_type').val();
			var selection_name 	= $('#selected-list_wdm_selections').val();
			var selected_records = [];
			var record_data      = {};
			var $trash_button;
			var deleteRecord;

			if('customer' == option_type) {
				deleteRecord = confirm(single_view_obj.remove_sel_rec_customer_opt_type_conf_txt);
			} else {
				deleteRecord = confirm(single_view_obj.remove_sel_rec_conf_txt);
			}

			// If user cancels to delete the record.
			if (deleteRecord == false) {
				return false;
			}

			$chkbox_checked.each(function( index, element ) {
				$trash_button = $(element).closest('tr').find('a.wdm-csp-rm-record');
				record_data = {
					product_id: $trash_button.data('product-id'),
					min_qty: $trash_button.data('min-qty'),
					act_price: $trash_button.data('act-price'),
					dis_type: $trash_button.data('dis-type'),
					rule_no: $trash_button.data('rule-no'),
					csp_source: $trash_button.data('source'),
				};

				selected_records.push(record_data);
			});
			$(this).after('<img src="' + single_view_obj.loading_image_path + '" id="loading"/>');

			jQuery.ajax({
				type: 'POST',
				url: single_view_obj.admin_ajax_path,
				data: {
					action: 'remove_bulk_csp_price',
					option_type : option_type,
					selection_name : selection_name,
					record_data: selected_records,
				},
				success: function (response) {//response is value returned from php
					$('#loading').remove();
				   	$('body #selected-list_wdm_selections').click();
				}
			});

			return;
		});

		// When pagination is changed.
		$(document).on('click', '#example_wrapper .paginate_button a', function(){
			// hideDeleteDisableSelAllCb(); // code commented
		});

		/**
	     * Updates "Select all" control in a data table
		 * Function for controlling actions when checkboxes are clicked.
		 */
		function updateDataTableSelectAllCtrl(){
			var $chkbox_all         = $('table#example tbody input[type="checkbox"]').not(":disabled");
			var $chkbox_checked     = $('table#example tbody input[type="checkbox"]:checked');
			var $chkbox_select_all  = $('table#example thead input[name="select_all"]');

			if ($chkbox_checked.length === $chkbox_all.length ){
				$chkbox_select_all.prop('checked', true);
			} else {
			   $chkbox_select_all.prop('checked', false);
			}
		}

		/**
		 * This function hides/shows the 'Delete' button and enables/disables the 'Select All' checkbox.
		 */
		function hideDeleteDisableSelAllCb()
		{
			// All non-disabled checkboxes.
			var $chkbox_all = $('table#example tbody input[type="checkbox"]').not(":disabled");

			// If none of checkboxes are enabled.
			if ($chkbox_all.length == 0) {
				// disable the 'All Section' checkbox
				$('thead input[name="select_all"]').prop("disabled", true);
				$('input.wdm-remove-sel-csp-price').hide();
			} else {
				// // Show the 'Delete' button only if respone is having data.
				// if("undefined" != typeof response && response.length >= 1) {
					$('thead input[name="select_all"]').prop("disabled", false);
					$('input.wdm-remove-sel-csp-price').show();
				// }
			}
		}
	 });
})( jQuery );
