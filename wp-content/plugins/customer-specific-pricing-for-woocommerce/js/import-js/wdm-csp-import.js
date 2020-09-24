// @author WisdmLabs

var csp_rows_read = 0;
var csp_insert_cnt = 0;
var csp_update_cnt = 0;
var csp_skip_cnt = 0;
var import_status="processing";
// var import_unique_id = 'product_id';


/**
 * Following function batchDataSender() first gets called on page load and,
 * executes if batchImportList have some data. it is then repeatedly called by,
 * ajax success event in senddata() method. and sends data if it hasn't used all
 * records in batchImportList. This method can also be used to show
 * "progress status/progress bar" on csp import csv page.  
 */

 function confirmationBeforeLeavingPage()
 {
    jQuery(window).bind('beforeunload', function(){
        return 'Leaving this page will abort the import process, do you want to leave this page ?';
      });
 }
var batchCall=0;
 function batchDataSender()
{
batchSize=batchImportList.length;
if (batchSize!=0 && batchSize>batchCall) {
    let batch=batchImportList[batchCall++];
    senddata(batch.batchName,batch.fileType,batch.batchNo,batch.uniqueId);
    }
return true;
}

function senddata(filename, type, batchNumber, uniqueId){
    jQuery('.update-nag').hide();
    jQuery('.wdm_import_form').hide();
    if ( csp_rows_read == 0 ) {
        jQuery( '.import-header' ).text(wdm_csp_import.loading_text);
        // jQuery( '.import-header' ).append( '<img src="' + wdm_csp_import.loading_image_path + '" id="loading"/>' );
        jQuery( "#wdm_import_data" ).show();
    }
    var file = filename;
    var fileType = type;
    var action;

    if ( fileType == 'role' ) {
        action = 'import_role_specific_file'
    } else if ( fileType == 'user' ) {
        action = 'import_customer_specific_file';
    } else if ( fileType == 'group' ) {
        action = 'import_group_specific_file';
    }

    jQuery.ajax({
        type: "POST",
        url: wdm_csp_import.admin_ajax_path,
        data: {
            'action' : action,
            'file_name' : file,
            'file_type' : fileType,
            'batch_number' : batchNumber,
            '_wp_import_nonce' : wdm_csp_import.import_nonce,
            'unique_id' : uniqueId
        },
        dataType: "json",
        async: true,
        success: function(response){
            
            // Increment the counters
            csp_rows_read   += response.rows_read;
            csp_insert_cnt  += response.insert_cnt;
            csp_update_cnt  += response.update_cnt;
            csp_skip_cnt    += response.skip_cnt;

            jQuery('#inserted').text(csp_insert_cnt);
            jQuery('#updated').text(csp_update_cnt);
            jQuery('#skipped').text(csp_skip_cnt);
            updateTotalRecordsProcessed();
            jQuery('#total-processed').attr('current_val',csp_rows_read);

            if ( csp_rows_read == parseInt(jQuery('#total-records').text(), 10) ) {
                jQuery( '#loading' ).remove();
                jQuery( '.import-header' ).text(wdm_csp_import.header_text);
                //Success Message
                //jQuery('#wdm_import_data').after('<div class="wdm_summary"><p>' + wdm_csp_import.total_no_of_rows + csp_rows_read + wdm_csp_import.total_insertion + csp_insert_cnt + wdm_csp_import.total_updated + csp_update_cnt + wdm_csp_import.total_skkiped + csp_skip_cnt + '</p></div><div><a href="'+wdm_csp_import.import_page_url+'">Go Back To Import</a></div>');
                jQuery.ajax({
                    type: "POST",
                    url: wdm_csp_import.admin_ajax_path,
                    data: {
                        'action' : 'drop_batch_numbers',
                        'file_type' : fileType,
                    },
                    success: function(response){} 
                });

                jQuery('.csp-download-report').removeAttr('disabled');
                jQuery( '.wdm_message_p' ).text(wdm_csp_import.import_successfull);
                jQuery( '#wdm_message' ).show('fast');
                jQuery('.progress-bar').removeClass('progress-bar-striped active');
                jQuery(window).off('beforeunload');
            }
            let total=parseInt(jQuery('#total-records').text(), 10);
            let percent=parseInt((csp_rows_read/total)*100);
            jQuery('#percent-progress').text(percent+'%');
            jQuery('#import-progress-bar').css('width',percent+'%');
            jQuery('#import-progress-bar').attr('aria-valuenow',percent);
            
            batchDataSender();
        }
    });
}

function updateTotalRecordsProcessed() {
    jQuery('#total-processed').prop('Counter',jQuery('#total-processed').attr('current_val')).animate({
        Counter: csp_rows_read
    }, {
        duration: 400,
        easing: 'swing',
        step: function (now) {
            jQuery('#total-processed').text(Math.ceil(now));
        }
    }); 
}


jQuery( 'document' ).ready( function ( jQuery ) {

    //Enable Data PopOvers 
    jQuery('#import-help-text[data-toggle="popover"]').popover({content : wdm_csp_import.HelpContext}); 
      
    //On import tab page
    jQuery( '#wdm_message' ).hide();
    jQuery( '#dd_show_import_options' ).on( "change", function () {
        jQuery( "#wdm_import_data" ).hide();
        jQuery( '#wdm_message' ).hide();
        var importUsing = jQuery("input[name='wdm_csp_import_using']:checked").val() == 'sku' ? jQuery("input[name='wdm_csp_import_using']:checked").val() : 'product_id';
        setCSVTemplateUrl(jQuery(this).val() + '_' + importUsing);
    } );

    jQuery("input.wdm_csp_import_using").on("change", function(){
        var importUsing = jQuery("input[name='wdm_csp_import_using']:checked").val() == 'sku' ? jQuery("input[name='wdm_csp_import_using']:checked").val() : 'product_id';
        var importType  = jQuery('#dd_show_import_options').val();

        setCSVTemplateUrl(importType + '_' + importUsing);
    });



    jQuery( ".wdm_import_form" ).submit( function (event) {
        if ( jQuery('#wdm_message').hasClass('error') ) {
            jQuery( '.import-header' ).hide();
            jQuery( "#wdm_import_data" ).hide();
            event.preventDefault();
        }
    } );


    /**
    * Set the CSV template URL for import
    * @param string importType import type user/group/role
    */
    function setCSVTemplateUrl(importType) {
        switch(importType) {
            case 'Wdm_User_Specific_Pricing_Import_product_id':
                jQuery('.sample-csv-import-template-link').attr('href', wdm_csp_import.templates_url + 'user_specific_pricing_sample.csv');
                jQuery('span.import-type').text(wdm_csp_import.user_specific_sample);
                break;

            case 'Wdm_Group_Specific_Pricing_Import_product_id':
                jQuery('.sample-csv-import-template-link').attr('href', wdm_csp_import.templates_url + 'group_specific_pricing_sample.csv');
                jQuery('span.import-type').text(wdm_csp_import.group_specific_sample);
                break;

            case 'Wdm_Role_Specific_Pricing_Import_product_id':
                jQuery('.sample-csv-import-template-link').attr('href', wdm_csp_import.templates_url + 'role_specific_pricing_sample.csv');
                jQuery('span.import-type').text(wdm_csp_import.role_specific_sample);
                break;

            case 'Wdm_User_Specific_Pricing_Import_sku':
                jQuery('.sample-csv-import-template-link').attr('href', wdm_csp_import.templates_url + 'user_specific_pricing_sample_sku.csv');
                jQuery('span.import-type').text(wdm_csp_import.user_specific_sample);
                break;

            case 'Wdm_Group_Specific_Pricing_Import_sku':
                jQuery('.sample-csv-import-template-link').attr('href', wdm_csp_import.templates_url + 'group_specific_pricing_sample_sku.csv');
                jQuery('span.import-type').text(wdm_csp_import.group_specific_sample);
                break;

            case 'Wdm_Role_Specific_Pricing_Import_sku':
                jQuery('.sample-csv-import-template-link').attr('href', wdm_csp_import.templates_url + 'role_specific_pricing_sample_sku.csv');
                jQuery('span.import-type').text(wdm_csp_import.role_specific_sample);
                break;

            default:
                jQuery('.sample-csv-import-template-link').attr('href', wdm_csp_import.templates_url + 'user_specific_pricing_sample.csv');
                jQuery('span.import-type').text(wdm_csp_import.user_specific_sample);
        }
    }

    //onclicking the notice on top of page.
    jQuery(document).on( 'click', '.wusp-import-notice .notice-dismiss', function() {
        jQuery.ajax({
            url: wdm_csp_import.admin_ajax_path,
            data: {
                action: 'wusp_dismiss_import_notice'
            }
        })

    });

    jQuery('.csp-download-report').click(function() {
        jQuery(this).attr('disabled',true);
        jQuery(this).css('cursor', 'progress');

        jQuery.ajax({
            type: "POST",
            url: wdm_csp_import.admin_ajax_path,
            data: {
                'action' : 'csp_download_report',
                '_wp_import_nonce' : wdm_csp_import.import_nonce,
            },
            success: function(response){
                download(response,"ImportReport.csv")
                //operation to start download
                jQuery('.csp-download-report').removeAttr('disabled');
                jQuery('.csp-download-report').css('cursor', 'pointer');
            }
        });
     });


     function download(dataurl, filename) {
        var a = document.createElement("a");
        a.href = dataurl;
        a.setAttribute("download", filename);
        var event = document.createEvent("MouseEvents");
        event.initMouseEvent(
            "click", true, false, window, 0, 0, 0, 0, 0
            , false, false, false, false, 0, null
            );
        a.dispatchEvent(event);
        return false;
      }
} );

