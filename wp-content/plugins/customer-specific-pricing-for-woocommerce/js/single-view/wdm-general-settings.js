var productOldQuantities = "";
( function ( $ ) {
    'use strict';

    /**
     * All of the code for your admin-specific JavaScript source
     * should reside in this file.
     *
     * Note that this assume you're going to use $, so it prepares
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

    $( function () {
	var productOldQuantities = "";
	var progress_timer;
	var enableAllFields = true;
	var retrieveAlreadySetProducts = new Object;
	var retrieveAlreadySetProductsActions = new Object;
	var retrieveAlreadySetProductsQty = new Object;
	var updated = false;
	var retrieveExistingTitle = new Object;

	var customerProductsSelectionFlag = new Object;

	customerProductsSelectionFlag.flag = 1;

	var getParamters = getSearchParameters();

	if ( $.isPlainObject( single_view_obj.product_result ) ) {
	    enableDualListBox();
	    var response = single_view_obj.product_result;
	    appendProductPriceTable( response );
	    disableEntries();
	    $( '.wdm-csp-product-details-list' ).css( 'opacity', '1' );
	}

	//Hide set price button if 'Edit Rule' button is visible
	if ( $( '#wdm_edit_entries' ).is( ':visible' ) ) {
	    $( '#wdm_csp_set_price' ).css( 'visibility', 'hidden' );
	}

	$( '#wdm_setting_option_type' ).change( function () {
	    var option_type = $( this ).val();
	    if ( $( '#wdm_csp_query_title' ).length ) {
		retrieveExistingTitle.text = $( '#wdm_csp_query_title' ).val();
	    }
	    $( '.wdm-csp-single-view-result-wrapper' ).empty();
	    $( '#loading' ).remove();

	    if ( option_type != -1 ) {
		$( this ).after( '<img src="' + single_view_obj.loading_image_path + '" id="loading"/>' );
		//Send AJAX request

		$.ajax( {
		    type: 'POST',
		    url: single_view_obj.admin_ajax_path,
		    data: {
			action: 'get_type_selection_result',
			option_type: option_type
		    },
		    success: function ( response ) { //response is value returned from php
			$( '#loading' ).remove();
			$( '.wdm-csp-single-view-result-wrapper' ).append( response );
			enableDualListBox();
		    }
		} );
	    }
	} ); //option type selection end

	/**
	* Gets the search parameters from the window location url.
	*/
	function getSearchParameters() {
	    var prmstr = window.location.search.substr( 1 );
	    prmstr = prmstr != null && prmstr != "" ? transformToAssocArray( prmstr ) : { };
	    return prmstr;
	}

	/**
	* Transform the search parameters string to associative array.
	* @param string prmstr string of window location with search paramters
	* @return array params search parameters in an array.
	*/
	function transformToAssocArray( prmstr ) {
	    var params = { };
	    var prmarr = prmstr.split( "&" );
	    for ( var i = 0; i < prmarr.length; i++ ) {
			var tmparr = prmarr[i].split( "=" );
			params[tmparr[0]] = tmparr[1];
	    }
	    return params;
	}

	function enableDualListBox() {
	    if ($('#selected-list_wdm_selections').length) {
	       $('#selected-list_wdm_selections').bootstrapDualListbox({
	           moveOnSelect: false,
		    filterTextClear: single_view_obj.show_all,
		      filterPlaceHolder: single_view_obj.filter,
		      moveSelectedLabel: single_view_obj.move_selected,
		      moveAllLabel: single_view_obj.move_all,
		      removeSelectedLabel: single_view_obj.remove_selected,
		      removeAllLabel: single_view_obj.remove_all, // true/false (forced true on androids, see the comment later)
		      helperSelectNamePostfix: '_helper', // 'string_of_postfix' / false
		      nonSelectedFilter: '', // string, filter the non selected options
		      selectedFilter: '', // string, filter the selected options
		      infoText: single_view_obj.showing_all, // text when all options are visible / false for no info text
		      infoTextFiltered: '<span class="label label-warning">Filtered</span> {0} from {1}', // when not all of the options are visible due to the filter
		      infoTextEmpty: single_view_obj.empty_list,
	       });

	       //Change "Move" button
	       var move_button = $('#bootstrap-duallistbox-nonselected-list_wdm_selections').parents('.box1').find('.move');
	       $('#bootstrap-duallistbox-nonselected-list_wdm_selections').parents('.box1').find('.moveall').before($(move_button));
	    }

	    if ( $( '#wdm_product_lists' ).length ) {
		$( '#wdm_product_lists' ).bootstrapDualListbox( {
		    moveOnSelect: false,
		    filterTextClear: single_view_obj.show_all,
		      filterPlaceHolder: single_view_obj.filter,
		      moveSelectedLabel: single_view_obj.move_selected,
		      moveAllLabel: single_view_obj.move_all,
		      removeSelectedLabel: single_view_obj.remove_selected,
		      removeAllLabel: single_view_obj.remove_all, // true/false (forced true on androids, see the comment later)
		      helperSelectNamePostfix: '_helper', // 'string_of_postfix' / false
		      nonSelectedFilter: '', // string, filter the non selected options
		      selectedFilter: '', // string, filter the selected options
		      infoText: single_view_obj.showing_all, // text when all options are visible / false for no info text
		      infoTextFiltered: '<span class="label label-warning">Filtered</span> {0} from {1}', // when not all of the options are visible due to the filter
		      infoTextEmpty: single_view_obj.empty_list,
		} ); //selectorMinimalHeight: 150

		//Change "Move" button
		var move_button = $( '#bootstrap-duallistbox-nonselected-list_wdm_product_lists' ).parents( '.box1' ).find( '.move' );
		$( '#bootstrap-duallistbox-nonselected-list_wdm_product_lists' ).parents( '.box1' ).find( '.moveall' ).before( $( move_button ) );
	    }
	}

	function disableDualListAndOptionType() {

		// select user
		$('#selected-list_wdm_selections').attr('disabled', 'disabled');
		$( '.csp-selection-list-wrapper .form-group' ).css( 'opacity', '0.5' );

	    //option type

	    $( '#wdm_setting_option_type' ).attr( 'disabled', 'disabled' );
	    $( '.csp-product-list.csp-selection-wrapper-sections .form-group' ).css( 'opacity', '0.5' );

	    //Dual Listbox controls

	    $( '.moveall' ).attr( 'disabled', 'disabled' );

	    $( '.removeall' ).attr( 'disabled', 'disabled' );

	    $( '.move' ).attr( 'disabled', 'disabled' );

	    $( '.remove' ).attr( 'disabled', 'disabled' );

	    $('.filter.form-control').attr( 'disabled', 'disabled' );
	    $( '.wdm-csp-single-view-from-group').css('opacity', '0.5');
	    $('#bootstrap-duallistbox-nonselected-list_wdm_product_lists').attr( 'disabled', 'disabled' );
	    $('#bootstrap-duallistbox-selected-list_wdm_product_lists').attr( 'disabled', 'disabled' );
	}

	function disableRuleTable() {
	    //Buttons

	    $( '#wdm_csp_save_changes' ).attr( 'disabled', 'disabled' );
	    $( '#wdm_csp_set_price' ).attr( 'disabled', 'disabled' );

	    //Query Title
	    $( '#wdm_csp_query_title' ).attr( 'disabled', 'disabled' );

	    //Datatable
	    var table = $( '#example' ).DataTable();
	    table.$( 'input' ).attr( 'readonly', 'readonly' );
	    table.$( 'select' ).attr( 'disabled', 'disabled' );

	    $( '.wdm-csp-product-details-list' ).css( 'opacity', '0.5' );
	}

	function disableEntries() {
	    disableDualListAndOptionType();

	    disableRuleTable()
	}



	function enableDualListAndOptionType() {
	    //option type

	    $( '#wdm_setting_option_type' ).removeAttr( 'disabled' );
		$( '.wdm-csp-single-view-from-group').css('opacity', '1');
	    //Dual Listbox

	    $( '.moveall' ).removeAttr( 'disabled' );

	    $( '.removeall' ).removeAttr( 'disabled' );

	    $( '.move' ).removeAttr( 'disabled' );

	    $( '.remove' ).removeAttr( 'disabled' );
		$('#bootstrap-duallistbox-nonselected-list_wdm_product_lists').removeAttr( 'disabled' );
		$('#bootstrap-duallistbox-selected-list_wdm_product_lists').removeAttr( 'disabled' );
		$('.filter.form-control').removeAttr( 'disabled' );

	    $( '.csp-selection-list-wrapper .form-group' ).css( 'opacity', '1' );
	    $('#selected-list_wdm_selections').removeAttr( 'disabled' );

	    $( '.csp-product-list.csp-selection-wrapper-sections .form-group' ).css( 'opacity', '1' );
	}

	function enableRuleTable() {

	    //Buttons

	    $( '#wdm_csp_save_changes' ).removeAttr( 'disabled' );
	    $( '#wdm_csp_set_price' ).removeAttr( 'disabled' );

	    //Query Title
	    $( '#wdm_csp_query_title' ).removeAttr( 'disabled' );


	    //Datatable
	    var table = $( '#example' ).DataTable();
	    if(enableAllFields) {
	    	table.$( 'input' ).removeAttr( 'readonly' );
	    }
	    table.$( 'select' ).removeAttr( 'disabled' );
	    
	    $( '.wdm-csp-product-details-list' ).css( 'opacity', '1' );
	    $( '.wdm-csp-product-details-list .row.form-group' ).css( 'opacity', '1' );
	}

	function enableEntries() {
	    enableDualListAndOptionType();
	    enableRuleTable();
	}

	function getOptionSelected()
	{
		var selectionType = $( "#wdm_setting_option_type" ).val() + 's';
		if(selectionType == 'customers') {
			return single_view_obj.customer_text;
        }
        if(selectionType == 'roles') {
            return single_view_obj.role_text;
        }
        if(selectionType == 'groups') {
            return single_view_obj.group_text;
        }
	}

	function resetEntries() {
	    //Option Type

	    //Select box
	    $( '#selected-list_wdm_selections option' ).each( function () {
			$( this ).removeAttr( 'selected' )
			$('#bootstrap-duallistbox-nonselected-list_wdm_selections').append($(this));
	    } );

	    $( '#bootstrap-duallistbox-selected-list_wdm_product_lists option' ).each( function () {
			$( this ).removeAttr( 'selected' )
			$( '#bootstrap-duallistbox-nonselected-list_wdm_product_lists' ).append( $( this ) );
	    } );

	    $( '.wdm-csp-product-details-list' ).empty();

	    //Query Title
	    $( '#wdm_csp_query_title' ).val( '' );
	}

	$( 'body' ).delegate( '#csp_value', 'change', function () {

	});

	$( 'body' ).delegate( '.csp_single_view_action', 'change', function () {
		if ( parseInt($(this).val(), 10) == 1) {
			$(this).closest('tr').find('.csp_single_view_value').removeClass('csp-percent-discount');
		} else if ( parseInt($(this).val(), 10) == 2 ) {
			$(this).closest('tr').find('.csp_single_view_value').addClass('csp-percent-discount');
		}
	});

	$( 'body' ).delegate( '#wdm_edit_entries', 'click', function () {
	    disableDualListAndOptionType();
	    var selectionType = getOptionSelected();
	    $( "#wdm_csp_set_price" ).val(single_view_obj.change_text + selectionType + single_view_obj.change_product_selection);
	    $("a.dt-button.buttons-collection.buttons-colvis").hide();
        enableRuleTable();
	    customerProductsSelectionFlag.flag = 0;
	    $( this ).hide();
	    $( '#wdm_back' ).hide();
	    $( '#wdm_csp_set_price' ).css( 'visibility', 'visible' );
	    // $('.csp_single_view_qty').attr('readonly', 'readonly');
	} );

	$( 'body' ).delegate( '#wdm_clear_entries', 'click', function () {
	    enableEntries();
	    resetEntries();
	    $( this ).parent().remove();
	} );

	$( 'body' ).delegate( '#wdm_back', 'click', function () {
		window.history.back();
	} );

	function displaySearchSelection(option_type, selection_name)
	{
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
					//dom: 'Bfrtip',
					"columnDefs": [ { "targets": 3,
					"render": function ( data, type, full, meta ) {
						if(data === '--')
							return data;
						else
					      return '<a href="'+single_view_obj.query_log_link+data+'">'+data+'</a>';
					    }
					}],
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
            }
        });
	}

	$( 'body' ).delegate( '#wdm_csp_set_price', 'click', function () {
	    if ( customerProductsSelectionFlag.flag == 0 ) {
			enableDualListAndOptionType();
			disableRuleTable();

			customerProductsSelectionFlag.flag = 1;
			$( "#wdm_csp_set_price" ).val( 'Set Price' );
			$( '#wdm_csp_set_price' ).removeAttr( 'disabled' );
			$( 'html, body' ).animate( {
			    scrollTop: $( ".wdm-tab-info" ).offset().top
			}, 500 );
	    } else {

			$( 'div.error' ).remove();

			if ( $( '#bootstrap-duallistbox-selected-list_wdm_selections option' ).length > 0 && $( '#bootstrap-duallistbox-selected-list_wdm_product_lists option' ).length > 0 )
			{
				retriveAlreadyExistingValuesOnChange();
			    customerProductsSelectionFlag.flag = 0;
			    disableDualListAndOptionType();

			    //set prices
			    setPrices();

			} else if ( !$( '#bootstrap-duallistbox-selected-list_wdm_selections option' ).length ) {
				var selectionType = getOptionSelected();
			    $( '#wdm_csp_set_price' ).after( '<div class="error"><p>' + selectionType + single_view_obj.error_selection_empty + '</p></div>' );
			} else if ( !$( '#bootstrap-duallistbox-selected-list_wdm_product_lists option' ).length ) {
			    $( '#wdm_csp_set_price' ).after( '<div class="error"><p>' + single_view_obj.error_product_list_empty + '</p></div>' );
			}
		}
	} ); //Set Price button click end

	function setPrices()
	{
	    //Fetch Product Price List
		var selection_list = { };

	    $('#bootstrap-duallistbox-selected-list_wdm_selections option').each(function() {
	        selection_list[$(this).val()] = $(this).text();
	    });
	    var product_list = { };

	    $( '#bootstrap-duallistbox-selected-list_wdm_product_lists option' ).each( function () {
			product_list[$( this ).val()] = $( this ).text();
	    } );

	    $( '.wdm-csp-product-details-list' ).empty();
	    $( '#loading' ).remove();
	    $( '#wdm_csp_set_price' ).after( '<img src="' + single_view_obj.loading_image_path + '" id="loading"/>' );

	    //Gets the product pricing list.
	    $.ajax( {
			type: 'POST',
			url: single_view_obj.admin_ajax_path,
			dataType: 'json',
			data: {
			    action: 'get_product_price_list',
			    selection_list: selection_list,
			    // selected_customer_names: selectedCustomerNames,
			    product_list: product_list,
			    option_type: $( '#wdm_setting_option_type' ).val()
		},
		success: function ( response ) { //response is value returned from php

		    $( '.wdm-csp-product-details-list' ).empty();
		    $( '#loading' ).remove();

		    response = appendAlreadyExistingValues(response);
		    appendProductPriceTable( response );
		    var selectionType = getOptionSelected();
		    $( "#wdm_csp_set_price" ).val(single_view_obj.change_text + selectionType + single_view_obj.change_product_selection);
			enableRuleTable();
			$("a.dt-button.buttons-collection.buttons-colvis").hide();
		    if ( typeof getParamters.query_log !== 'undefined' ) {
				jQuery( "#wdm_csp_save_changes" ).val(single_view_obj.update_rule);
			}
			//Store old quantities in a variable.
			var table = $( '#example' ).DataTable();
			table.$( 'input.csp_single_view_qty' ).each(function() {
				productOldQuantities += jQuery(this).attr('name');
				if(''==jQuery(this).closest('tr').find('td > input.csp_single_view_value').val()) {
					productOldQuantities += "=" + 0 + "&";
				} else {	
					productOldQuantities += "="+jQuery(this).attr('data-oldval')+"&";}
			});
		}
	    });
	}

	function retriveAlreadyExistingValuesOnChange()
	{
		var TableObj = $('#example').DataTable();
		var data = TableObj.rows().data();

	    //Backup current title
	    if ( $( '#wdm_csp_query_title' ).length ) {
			retrieveExistingTitle.text = $( '#wdm_csp_query_title' ).val();
	    }


		for ( var key in data ) {
			for (var i =0; i < data[key].length; i++) {
				try{
					var ele=jQuery(data[key][i]);
				}catch(e){
					continue;
				}

				if (jQuery(data[key][i]).hasClass('csp_single_view_action')) {
					retrieveAlreadySetProductsActions[jQuery(data[key][i]).attr('name')] = data[key][i];
		        }
				
				if (jQuery(data[key][i]).hasClass('csp_single_view_qty')) {
					retrieveAlreadySetProductsQty[jQuery(data[key][i]).attr('name')] = data[key][i];
		        }
				
				if (jQuery(data[key][i]).hasClass('csp_single_view_value')) {
					retrieveAlreadySetProducts[jQuery(data[key][i]).attr('name')] = data[key][i];
		        }
			}
		}
	}

	/**
	* @param array response product pricing list.
	*/
	function appendAlreadyExistingValues( response ) {

		var tempResponse = response;
		if (objectLength(retrieveAlreadySetProductsActions) && objectLength(retrieveAlreadySetProductsQty) && objectLength(retrieveAlreadySetProducts)) {
			for (var i = 0; i < response.value.length; i++) {
				for (var j = 0; j < response.value[i].length; j++ ) {
					var tempAction, tempQty, tempProduct;
					try{
						try{
							var ele=jQuery(response.value[i][j]);
						}catch(e){
							continue;
						}
						tempAction = retrieveAlreadySetProductsActions[ele.attr('name')];
						tempQty = retrieveAlreadySetProductsQty[jQuery(response.value[i][j]).attr('name')];
						tempProduct = retrieveAlreadySetProducts[jQuery(response.value[i][j]).attr('name')];
					} catch(err){
						tempProduct = tempAction = tempQty = undefined;
					}
					
					//loop through already stored actions and set those actions automatically
					if (jQuery(response.value[i][j]).hasClass('csp_single_view_action') && tempAction != undefined) {
						tempResponse.value[i][j] = retrieveAlreadySetProductsActions[jQuery(response.value[i][j]).attr('name')];
			        }

			        //loop through already stored quantities and set those values automatically
					if (jQuery(response.value[i][j]).hasClass('csp_single_view_qty') && tempQty != undefined) {
						tempResponse.value[i][j] = retrieveAlreadySetProductsQty[jQuery(response.value[i][j]).attr('name')];
			        }
			        
					//loop through already stored values and set those values automatically
					if (jQuery(response.value[i][j]).hasClass('csp_single_view_value') && tempProduct != undefined) {
						tempResponse.value[i][j] = retrieveAlreadySetProducts[jQuery(response.value[i][j]).attr('name')];
			        }
				}
			}
		}

		return tempResponse;
	}

	function appendTable(response) {
		$( '<table cellpadding="0" cellspacing="0" border="0" class="display" id="example"></table>' ).prependTo( '.wdm-csp-product-details-list' );

		/* Create an array with the values of all the input boxes in a column, parsed as numbers */
		$.fn.dataTable.ext.order['dom-text-numeric'] = function  ( settings, col )
			{
    		return this.api().column( col, {order:'index'} ).nodes().map( function ( td, i ) {
        	return $('input', td).val() * 1;
    		} );
			}
	    var table = $( '#example' ).dataTable( {
		"data": response['value'],
		"columns": [response['title_name'][0],
					response['title_name'][1],
					response['title_name'][2],
					response['title_name'][3],
					response['title_name'][4],
					response['title_name'][5],
					{"title": response['title_name'][6]['title'],"orderDataType": "dom-text-numeric"},
					{"title": response['title_name'][7]['title'],"orderDataType": "dom-text-numeric"},
				],
		"lengthMenu": [ 10, 50, 100, 150, 200, 250, 300 ],
		dom: 'Blfrtip',
		//stateSave: true,
		buttons: [ {
			extend: 'colvis',
			columns: ':not(:gt(7))'
		    } ],
		"columnDefs": [ {
			"targets": 5,
			"orderable": false
		    }, ],
		language: {
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
		    buttons: {
			colvis: single_view_obj.hide_column_msg
		    }
		}
	    } );
	}

	function appendProductPriceTable( response ) {

		appendTable(response);
	    
	    $( '.wdm-csp-product-details-list' ).hide();
	    $( '.wdm-csp-product-details-list' ).append( response['query_input'] );

	    if ( retrieveExistingTitle.hasOwnProperty( 'text' ) ) {
			$( '#wdm_csp_query_title' ).val( retrieveExistingTitle.text );
	    }

	    $( '.wdm-csp-product-details-list' ).show();

	    $( '.progress' ).hide();
	    $( '.wdm-csp-product-details-list' ).css( 'opacity', '1' );
	    $( 'html, body' ).animate( {
			scrollTop: $( ".wdm-csp-product-details-list" ).offset().top
	    }, 500 );
	}

// For the rules to save changes on click of button.
	$( 'body' ).delegate( '#wdm_csp_save_changes', 'click', function () {
	    $( this ).parent().find( '.error' ).remove();
	    $('.csp_single_view_qty').removeAttr('readonly');
	    if ( $( '#wdm_csp_query_title' ).val().length === 0 ) {
		$( this ).after( '<div class="error"><p>' + single_view_obj.error_query_title_empty + '</p></div>' );
		return false;
	    }

	    var rows = $( "#example" ).dataTable().fnGetNodes();
	    var cells = [ ];
	    var nanVal = [ ];
	    var negativeVal = [ ];
	    var invalidQty = [ ];
	    var maxVal = false;
	    var some_fields_empty = false; //used to check if some fields are empty or not
		var all_fields_valid = false; //used to check if All Fields consist valid value
		var emptyRegPrice=false;

		var i=0;
	    for ( i = 0; i < rows.length; i++ ) {
			var value = $( rows[i] ).find( "td:eq(7)" ).find( '#csp_value' ).val();
			var discType = $( rows[i] ).find( "td:eq(5)" ).find( '.csp_single_view_action' ).val();
			var minQty = $( rows[i] ).find( "td:eq(6)" ).find( '.csp_single_view_qty' ).val();
			var regPrice=parseFloat($( rows[i] ).find( "td:eq(3)" ).html());
			var max = '';

			if ( parseInt(discType, 10) == 2 ) { // used to check if discount type is % then max value can be 100
				if ( parseInt(value, 10) > 100 ) { // if discount type is % and value greater than 100 
					maxVal = true;
					break;
				}
				if (regPrice<=0 || regPrice=='--' || isNaN(regPrice)) {
					emptyRegPrice=true;
					break;
				}

			}

			var convertToDBStorageVal = reverse_number_format(value, wdm_csp_function_object.decimals, wdm_csp_function_object.decimal_separator, wdm_csp_function_object.thousand_separator);
			// if (minQty)
			var numericCheck = isNaN(convertToDBStorageVal);

			// var numericCheck = isNaN( value );
			var negativeCheck = ( convertToDBStorageVal < 0 ) ? true : false;

			if ( value == '' ) {
			    cells.push( value );
			} else if( numericCheck === true ) {
				nanVal.push( value );
			} else if( negativeCheck ) {
				negativeVal.push( value );
			} else if ( cells.length > 0 || nanVal.length > 0 || negativeVal.length > 0 ) {
			    break;
			}

			minQty = parseInt(minQty.trim());
	
			if( (minQty != '' && !isPositiveInt(minQty)) || (minQty == '') ) {
				invalidQty.push( minQty );
				break;
			}
	    }

	    if ( cells.length > 0 ) {
			if ( rows.length === cells.length ) {
			    $( this ).after( '<div class="error"><p>' + single_view_obj.error_all_fields_empty + '</p></div>' );
			    return false;
			} else {
			    some_fields_empty = true;
			}
	    }

	    if(invalidQty.length > 0) {
	    	$( this ).after( '<div class="error"><p>' + single_view_obj.invalid_quantity_value + '</p></div>' );
	    	return false;
	    }

	    if ( nanVal.length > 0 ) {
	    	$( this ).after( '<div class="error"><p>' + single_view_obj.error_field_not_numeric + '</p></div>' );
	    	return false;
		}

	    if ( negativeVal.length > 0 ) {
	    	$( this ).after( '<div class="error"><p>' + single_view_obj.error_field_negative_number + '</p></div>' );
	    	return false;
		}

		if ( maxVal ) {
	    	$( this ).after( '<div class="error"><p>' + single_view_obj.error_field_max_val + '</p></div>' );
	    	return false;
		}

		if (emptyRegPrice) {
			$( this ).after( '<div class="error"><p>' + single_view_obj.empty_regular_price + '</p></div>' );
			$(rows[i]).css('background-color','#d2020238');
			$(rows[i]).hover(function(){
				$(rows[i]).css('background-color','');
			});
			return false;
		}

	    if ( $( '#example' ).find( '.wdm_error' ).length ) {
		//Errors present
		var confirmCheck = confirm( single_view_obj.confirm_msg_if_error );
	    } else if ( some_fields_empty ) {
		var confirmCheck = confirm( single_view_obj.confirm_msg_if_empty );
	    } else {
		var confirmCheck = confirm( single_view_obj.confirm_msg );
	    }

	    if ( confirmCheck == true ) {
			var table = $( '#example' ).DataTable();
			var product_values = table.$( 'input.csp_single_view_value' ).serialize();
			var product_actions = table.$( 'select' ).serialize();
			var product_quantities = table.$( 'input.csp_single_view_qty' ).serialize();
			if (''==productOldQuantities) {
			table.$( 'input.csp_single_view_qty' ).each(function() {
				productOldQuantities += jQuery(this).attr('name');
				productOldQuantities += "="+jQuery(this).attr('data-oldval')+"&";
				});
			}

			var selection_list = '';
		    $('#bootstrap-duallistbox-selected-list_wdm_selections option').each(function() {
		        selection_list += $(this).val() + ',';
		    });
			
			var product_list = '';
			$( "#example tbody tr" ).each( function () {
				product_list += $( this ).find( "td" ).eq( 0 ).text() + ',';
		    } );

			//Send AJAX request

			$( '#loading' ).remove();
			$( this ).after( '<img src="' + single_view_obj.loading_image_path + '" id="loading"/>' );

			//Add/Reset Progress bar

			var pb = $( '.progress .progress-bar' );
			$( pb ).attr( 'data-transitiongoal', 0 ).progressbar( {
			    display_text: 'center',
			    transition_delay: 10
			} );
			$( '.csp-log-progress' ).html( single_view_obj.progress_loading_text );

			$( '.progress' ).show();
			$( '.csp-log-progress' ).show();

			progress_timer = setTimeout( getProgress, 1000 );

			$( '.wdm_result' ).remove();

			$.ajax( {
			    type: 'POST',
			    dataType: 'JSON',
			    url: single_view_obj.admin_ajax_path,
			    data: {
					action: 'save_query_log',
					option_type: $( '#wdm_setting_option_type' ).val(),
					selection_list: selection_list,
					product_list: product_list,
					product_values: product_values,
					product_actions: product_actions,
					product_quantities: product_quantities,
					productOldQuantities: productOldQuantities,
					current_query_id: getParamters.query_log,
					query_title: $( '#wdm_csp_query_title' ).val(),
					option_name: $( '#wdm_csp_query_time' ).val()
			    },
			    success: function ( response ) { //response is value returned from php
					clearTimeout( progress_timer );
					$( '#loading' ).remove();
					$( '.progress .progress-bar' ).attr( 'data-transitiongoal', 100 ).progressbar( {
					    display_text: 'center'
					} );
					// setPrices();
					// updated = true;
					$( '.csp-log-progress' ).html( single_view_obj.progress_complete_text );
					$( '.progress' ).hide();
					$( '.csp-log-progress' ).hide();
					jQuery( '.wdm-csp-product-details-list' ).find('#example_wrapper').remove();
					appendTable(response.product_result);
					$( '#wdm_csp_save_changes' ).after( response.update_div );
					// $('.csp_single_view_qty').attr('readonly', 'readonly');
					// var url = window.location.href;
					if ( typeof getParamters.query_log === 'undefined' ) {
					    getParamters.query_log = $( '.wdm_result' ).attr( 'rule_id' );
						// url += '&query_log='+getParamters.query_log;
				    	jQuery( "#wdm_csp_save_changes" ).val( single_view_obj.update_rule );
					}
				// if (updated) {
				// 	url += '&query_updated=true';
				// }
				// window.location.href = url;
				productOldQuantities = "";
			    }
			});

	    }

	} ); //Save changes button clicked

	function getProgress() {
	    var val = $( '.progress .progress-bar' ).attr( 'data-transitiongoal' );
	    if ( val < 99 ) {
		progress_timer = setTimeout( getProgress, 1000 );
	    }
	    if ( val == 0 ) {
		$( '.csp-log-progress' ).html(single_view_obj.progress_loading_text);
	    }

	    $.ajax( {
		type: 'POST',
		url: single_view_obj.admin_ajax_path,
		dataType: 'json',
		data: {
		    action: 'get_progress_status',
		    option_name: $( '#wdm_csp_query_time' ).val()
		},
		success: function ( response ) { //response is value returned from php
		    $( '.progress .progress-bar' ).attr( 'data-transitiongoal', parseInt( response['value'] ) ).progressbar( {
			display_text: 'center'
		    } );
		    $( '.csp-log-progress' ).html( response['status'] );
		}
	    } );
	}

    } );

} )( jQuery );