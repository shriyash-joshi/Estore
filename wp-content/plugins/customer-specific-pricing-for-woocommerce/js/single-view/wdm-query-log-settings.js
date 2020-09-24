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

	 	// Array holding selected row IDs
   		var rows_selected = [];
   		var table;

		//
		// Updates "Select all" control in a data table
		//
		/**
		* Function for controlling actions on clicking checkboxes.
		*/
		function updateDataTableSelectAllCtrl(table){
		   var $table             = table.table().node();
		   var $chkbox_all        = $('tbody input[type="checkbox"]', $table);
		   var $chkbox_checked    = $('tbody input[type="checkbox"]:checked', $table);
		   var chkbox_select_all  = $('thead input[name="select_all"]', $table).get(0);

		   // If none of the checkboxes are checked
		   if($chkbox_checked.length === 0){
		      chkbox_select_all.checked = false;
		      if('indeterminate' in chkbox_select_all){
		         chkbox_select_all.indeterminate = false;
		      }

		   // If all of the checkboxes are checked
		   } else if ($chkbox_checked.length === $chkbox_all.length ){
		      chkbox_select_all.checked = true;

		      if('indeterminate' in chkbox_select_all){
		        chkbox_select_all.indeterminate = false;
		      }

		   // If some of the checkboxes are checked
		   } else {
		      chkbox_select_all.checked = true;
		      if('indeterminate' in chkbox_select_all){
		        chkbox_select_all.indeterminate = true;
		      }
		   }
		}

		$('.wdm-csp-query-log-wrapper').prepend( '<table cellpadding="0" cellspacing="0" border="0" class="display" id="example"></table>');

	    	table = jQuery( '#example' ).DataTable( {
				"data": single_view_obj.data,
				"columns": single_view_obj.title_names,
				//dom: '<"top"i>rt<"bottom"flp><"clear">',
				'order': [[1, 'desc']],
	    		"columnDefs": [
				{
			         'targets': 0,
			         'searchable': false,
			         'orderable': false,
			         'className': 'dt-body-center',
			         'render': function (data, type, full, meta){
			             return '<input type="checkbox">';
			         }
			     },
			     {
			      "targets": 1,
					"render": function ( data, type, full, meta ) {
						if(data === '--')
							return data;
						else
					      return '<a href="'+single_view_obj.query_log_link+data+'">'+data+'</a>';
				    }
				},
				{
				 "targets": [2,4],
			      "orderable": true
			    }
				],
				'rowCallback': function(row, data, dataIndex){
			         // Get row ID
			         var rowId = data[0];

			         // If row ID is in the list of selected row IDs
			         if($.inArray(rowId, rows_selected) !== -1){
			            $(row).find('input[type="checkbox"]').prop('checked', true);
			            $(row).addClass('selected');
			         }
		      },
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
		    	}
		});

		jQuery('#wdm_delete_qlog').removeClass('hide');

		 // Handle click on checkbox
	   $('#example tbody').on('click', 'input[type="checkbox"]', function(e){
		      var $row = $(this).closest('tr');

		      // Get row data
		      var data = table.row($row).data();

		      // Get row ID
		      var rowId = data[0];

		      // Determine whether row ID is in the list of selected row IDs
		      var index = $.inArray(rowId, rows_selected);

		      // If checkbox is checked and row ID is not in list of selected row IDs
		      if(this.checked && index === -1){
		         rows_selected.push(rowId);

		      // Otherwise, if checkbox is not checked and row ID is in list of selected row IDs
		      } else if (!this.checked && index !== -1){
		         rows_selected.splice(index, 1);
		      }

		      if(this.checked){
		         $row.addClass('selected');
		      } else {
		         $row.removeClass('selected');
		      }

		      // Update state of "Select all" control
		      updateDataTableSelectAllCtrl(table);

		      // Prevent click event from propagating to parent
		      e.stopPropagation();
	   });

		// Handle click on table cells with checkboxes
	   $('#example').on('click', 'tbody td, thead th:first-child', function(e){
	      $(this).parent().find('input[type="checkbox"]').trigger('click');
	   });

	   // Handle click on "Select all" control
	   $('thead input[name="select_all"]', table.table().container()).on('click', function(e){
	      if(this.checked){
	         $('#example tbody input[type="checkbox"]:not(:checked)').trigger('click');
	      } else {
	         $('#example tbody input[type="checkbox"]:checked').trigger('click');
	      }

	      // Prevent click event from propagating to parent
	      e.stopPropagation();
	   });

	     // Handle table draw event
	   table.on('draw', function(){
	      // Update state of "Select all" control
	      updateDataTableSelectAllCtrl(table);
	   });

	   $('body').delegate('#wdm_delete_qlog','click',function(){

	   		var oTable = $('#example').dataTable();

	   		if(oTable.fnGetData().length === 0)
	   		{
	   			$(this).after('<br><div class="update-nag wdm-qlog-notification">' + single_view_obj.error_log_empty + '</div>');

	   			return false;
	   		}
	   		else if(! (rows_selected.length > 0))
	   		{
	   			$(this).after('<div class="error wdm-qlog-notification"><p>' + single_view_obj.error_message + '</p></div>');

	   			return false;
	   		}

	   		if(! single_view_obj.is_it_safe_to_delete) {
	  			var confirmCheck = confirm( single_view_obj.confirm_msg );
	   			if(!confirmCheck) {
	   				return;
	   			}
	   		}

	   		$('.wdm-qlog-notification').remove();
	   		$('.wdm-csp-query-log-wrapper').find('.updated').remove();
	   		$('.wdm-csp-query-log-wrapper').find('.error').remove();

	   		$(this).after('<img src="' + single_view_obj.loading_image_path + '" id="loading"/>');

   			//Send AJAX request

			jQuery.ajax({

	            type: 'POST',
	            url: single_view_obj.admin_ajax_path,
	            data: {
	                action: 'remove_query_log',
	                query_log_id : rows_selected,
	            },
	            success: function (response) {//response is value returned from php
	               $('#loading').remove();
	               $('.wdm-csp-query-log-wrapper').after(response);
	               var rows = table
				    .rows( '.selected' )
				    .remove()
				    .draw();
				    rows_selected = [];
	            }
	        });
	   });

	 });

})( jQuery );