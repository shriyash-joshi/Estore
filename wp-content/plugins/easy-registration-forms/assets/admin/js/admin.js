jQuery(document).ready(function(){
    $= jQuery;
    
    var show_progress= function(state){
        if(state)
        {
            $(".erf-ajax-progress").show();
        }
        else
        {
             $(".erf-ajax-progress").hide();
        }
    }
    
    /*
     * 
     * @params {string,string} form_name,form_type (Registration or Contact Form)
     * Send ajax request to create new form. 
     * Used actions: erf_new_form
     */
    var create_form= function(form_name,form_type){
        if(form_name=="")
            return;
        
        form_type = form_type || 'reg';

        var request_data= { 
                            action: 'erf_new_form',
                            title : form_name,
                            form_type: form_type
                          };
        //show_progress(true);                  
        $.post(ajaxurl,request_data,function(response){
              if(response.success)
              {
                  window.location= response.data.redirect;
              }
              else
              {
                  $("#erf_overview_add_form_response").text("Something went wrong.");
              }
        }).complete(function(){
            //show_progress(false);
        });
    };
   
    /*
     * Used for following HTML files: admin/overview/html/overview.php
     */ 
    $("#erf_overview_add_form").click(function(){
        $("#erf_overview_add_form_dialog").show();
    });
    $(".erf-add-pop").click(function(){
        $("#erf_overview_add_form_dialog").show();
    });
    
    $(".form-configurations").click(function(){
        $("#form-configurations").show();
        $("#form-configurations .modal-content").addClass("animate fadeIn").delay(500).queue(function(){
            $(this).removeClass("animate fadeIn").dequeue();
        });
                
    });
    
    $(".erf_close_dialog").click(function(){
        var closeButton = $(this);
        $("#form-configurations .modal-content").addClass("animate fadeOut").delay(500).queue(function(){
            $("#form-configurations .modal-content").removeClass("animate fadeOut").dequeue();
        });
        
        setTimeout(function(){     
            closeButton.closest('.erf_dialog').hide();
        }, 400);
        
    });
    /*
     * Used on individual submission page for adding note
     */
    $('#erf_submission_add_note').click(function(){
        $("#erf_submission_add_note_dialog").show();
    });

    /*
     * Used on Settings page
     */
    $('#erf_submission_print').click(function(){
        var container= $('#erf_admin_submission_details');
        if(container.length>0){
            container.printThis({
                importCSS: true,
                loadCSS: "",
            });
        }
        
    });
    
    $("#erf_configuration_form").submit(function(){
    });

    // Show hide child parent options
    //$('.erf-child-rows').slideUp();
    $(".erf-has-child input").each(function(){
            $(this).change(function(){
                var targetElement= $(this);
                var rowContainer= targetElement.closest('.erf-row');
                var index= targetElement.data('child-index');
                if(!index)
                    index=0;
                
                if(targetElement.data('has-child')!=1){
                    rowContainer.next('.erf-child-rows').slideUp(); 
                }
                
                
                
                if(targetElement.is(':checked')){
                    rowContainer.nextUntil('.erf-row').slideUp();
                    if(index!=-1)
                    rowContainer.nextAll().eq(index).slideDown();
                    $(document).trigger('erf_parent_row_changed', [$(this)]);
                    return;
                }
                else
                {   
                    rowContainer.nextAll().eq(index).slideUp();
                    $(document).trigger('erf_parent_row_changed', [$(this)]);
                    return;
                }
                
            });
        });
   
    if($('#erf_configure_limit_by_date').length>0){
        $('#erf_configure_limit_by_date').datepicker({ dateFormat: 'yy-mm-dd', minDate: new Date()});
    }
    
    $("#form-code").click(function(){
        $(this).focus();
        $(this).select();        
        document.execCommand('copy');
        $('#copy-message').fadeIn('slow').delay('200').fadeOut('slow');
        
    });
    $(".erf-shortcode").click(function(){
        $(this).focus();
        $(this).select();        
        document.execCommand('copy');
        $(this).siblings('.copy-message').fadeIn('slow').delay('200').fadeOut('slow');
        
    });
    
    /*
     * Used for following HTML files: admin/submission/html/payment-part.php
     */ 
    $("#erf_payment_change_status").click(function(){
        $("#erf_payment_status_dialog").show();
    });
    
    $("#erf_overview_add_form_btn").click(function(){
        var form_name= $("#erf_overview_input_form_name").val(); 
        var form_type= $('input[name=erf_overview_input_form_type]:checked').val();
        create_form(form_name,form_type); 
    });
    
    $(".erf_overview_delete_form_btn").click(function(e){
        var deleteURL=$(this).data('delete-url');
        var dialog= $("#erf_overview_delete_form_dialog");
        dialog.show();
        dialog.find('.erf-confirm-btn').click(function() {
            dialog.hide();
            location.href=deleteURL;
        });

        dialog.find('.erf-close-btn').click(function() {
            dialog.hide();
        });
    });
 
    $(".erf-form-card-menu").click(function(){
        $(this).closest('.erf-form-card').find('.erf-card-actions').toggleClass('erform-hidden');
        $(this).toggleClass("active");
    });
    
    $(".erf-wrapper .notice").each(function(){
        $(this).insertBefore($(this).closest('.erf-wrapper'));
    });
    
    /*
     * Used for following HTML file: admin/submission/html/submissions.php
     */ 
    $("#erf_submissions_change_columns").click(function(){
        $("#erf_change_subs_cols_dialog").show();
    });
    $("#erf_change_sub_cols_btn").click(function(){
        $(this).append('<span class="erf-loader"></span>');
        var request_data= { 
                            action: 'erf_change_sub_columns',
                            columns: $("#erf_sub_columns").val(),
                            form: $(this).data('form-id')
                          };
        $.post(ajaxurl,request_data,function(response){
           window.location.reload();
        }).complete(function(){
            $(this).find('.erf-loader').remove();
        });
    });
    
    $("#erf_clear_sub_columns").click(function(){
        $("#erf_sub_columns").val('');
    });
    
    /*
     * Used for following HTML file: admin/submission/html/submission.php
     */
    $('.erf_edit_submission').click(function(){
        $(this).closest('form').submit();
    });
    
    /*
     * User for All Forms page : admin/form/html/dashboard.php
     */
    $('.erf-dashboard-section').each(function(){
        var add_div_content= $(this).find('.erf-section-item-container');
        if(add_div_content.length>0 && add_div_content.html()==''){
            $(this).closest('.erf-dashboard-section').hide();
        }
    })
    
    /*
     * Used on submission table page
     */
    $(".erf_sub_delete").click(function(event){
        var text_helpers = erf_admin_data.text_helpers;
        if(confirm(text_helpers.sub_del_prompt)){
          return;
        } 
        event.preventDefault();
    });
});


function erfWPSanitizeTitle( title, maxlength ) {
        var maxlength = maxlength || title.length;
        title.replace(/\W/g, '').slice(0,maxlength);
	
        return  replaceSpacesWithUnderscore(title)
                .replace(/\W/g, '').slice(0,maxlength);
        
	function replaceSpacesWithUnderscore( str ) {
		return str
			// Replace one or more blank spaces with a single underscore (_)
			.replace(/ +/g,'_')
			// Replace two or more dashes (-) with a single underscore (_).
			.replace(/-{2,}/g, '_');
	}

}