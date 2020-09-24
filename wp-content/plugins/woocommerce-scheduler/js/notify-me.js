jQuery(document).ready(function($){
    let $notifyMeBtn = $('.wdm-notify-me-form .wdmws-notify-me-btn');
    let $notifyMeEmailTextField = $('.wdmws-notify-me-email-field');
    // let $notifyMeGuestSbt = $('.button.wdmws-notify-me-guest-sbt');
    let $enrlSuccMessageField = $('.wdmws-enrl-success-msg');
    let $notifyMeModal = $('#notify-me-guest-modal');
    let $notifyMeModalSbt = $('.wdmws-notify-me-guest-sbt-modal');

    // Handle when Notify Me button is clicked.
    $notifyMeBtn.click(function(){
        if($notifyMeEmailTextField.length > 0)
        {
            let $form_data = new FormData();
            let $userEmailField = $('.wdmws-notify-me-email-field');
            let userEmail = $userEmailField.val();
            userEmail = userEmail.trim();

            if (!validateEmailField($enrlSuccMessageField, userEmail)) {
                return false;
            }

            $form_data.append('security', $('.wdm-notify-me-form #_wpnonce').val());
            $form_data.append('action', 'wdmws_product_notification_enrl' );
            $form_data.append('product_id', $notifyMeBtn.data('product-id'));
            $form_data.append('user_email', userEmail);

            sendAjaxRequestForEnrolment($form_data, $notifyMeBtn, $enrlSuccMessageField);
        }
        else if($notifyMeModal.length > 0)
        {
            
            // Handle when guest user is allowed and enrolment method is popup modal.
            $notifyMeModalSbt.off('click').on('click', function(){
                let $form_data = new FormData();
                let $userEmailFieldModal = $('#wdmws-notify-me-guest-email-modal');
                let $enrlSuccessMsgLabel = $('.wdmws-enrl-success-msg-modal');
                let userEmail = $userEmailFieldModal.val();
                userEmail = userEmail.trim();

                if (!validateEmailField($enrlSuccessMsgLabel, userEmail)) {
                    return false;
                }

                $form_data.append('security', $('.wdm-notify-me-form #_wpnonce').val());                
                $form_data.append('action', 'wdmws_product_notification_enrl' );
                $form_data.append('product_id', $notifyMeBtn.data('product-id'));
                $form_data.append('user_email', userEmail);
                
                sendAjaxRequestForEnrolment($form_data, $notifyMeModalSbt, $enrlSuccessMsgLabel)
            });   
        }
        else
        {
            let $form_data = new FormData();
            $form_data.append('security', $('.wdm-notify-me-form #_wpnonce').val());            
            $form_data.append('action', 'wdmws_product_notification_enrl' );
            $form_data.append('product_id', $notifyMeBtn.data('product-id'));

            sendAjaxRequestForEnrolment($form_data, $notifyMeBtn, $enrlSuccMessageField)
        }
    });

    $(document).on('click', '.wdm-notify-me-nonce .wdmws-notify-me-btn', function(){
        let $this = $(this);
        let $notifyMeEmailTextField = $('.wdmws-notify-me-email-field');
        // let $notifyMeGuestSbt = $('.button.wdmws-notify-me-guest-sbt');
        let $enrlSuccMessageField = $('.wdmws-enrl-success-msg');
        let $notifyMeModal = $('#notify-me-guest-modal');
        let $notifyMeModalSbt = $('.wdmws-notify-me-guest-sbt-modal');

        if($notifyMeEmailTextField.length > 0)
        {
            let $form_data = new FormData();
            let $userEmailField = $('.wdmws-notify-me-email-field');
            let userEmail = $userEmailField.val();
            userEmail = userEmail.trim();

            if (!validateEmailField($enrlSuccMessageField, userEmail)) {
                return false;
            }

            $form_data.append('security', $('.wdm-notify-me-nonce #_wpnonce').val());
            $form_data.append('action', 'wdmws_product_notification_enrl' );
            $form_data.append('product_id', $this.data('product-id'));
            $form_data.append('user_email', userEmail);

            sendAjaxRequestForEnrolment($form_data, $this, $enrlSuccMessageField);
        }
        else if($notifyMeModal.length > 0)
        {
            // Handle when guest user is allowed and enrolment method is popup modal.
            $notifyMeModalSbt.click(function(){
                let $form_data = new FormData();
                let $userEmailFieldModal = $('#wdmws-notify-me-guest-email-modal');
                let $enrlSuccessMsgLabel = $('.wdmws-enrl-success-msg-modal');
                let userEmail = $userEmailFieldModal.val();
                userEmail = userEmail.trim();

                if (!validateEmailField($enrlSuccessMsgLabel, userEmail)) {
                    return false;
                }

                $form_data.append('security', $('.wdm-notify-me-nonce #_wpnonce').val());                           
                $form_data.append('action', 'wdmws_product_notification_enrl' );
                $form_data.append('product_id', $this.data('product-id'));
                $form_data.append('user_email', userEmail);
                
                sendAjaxRequestForEnrolment($form_data, $notifyMeModalSbt, $enrlSuccessMsgLabel)
            });   
        }
        else
        {
            let $form_data = new FormData();
            $form_data.append('security', $('.wdm-notify-me-nonce #_wpnonce').val());         
            $form_data.append('action', 'wdmws_product_notification_enrl' );
            $form_data.append('product_id', $this.data('product-id'));

            sendAjaxRequestForEnrolment($form_data, $this, $enrlSuccMessageField)
        }
    });


    // Send ajax request to enrol the user for the notification.
    function sendAjaxRequestForEnrolment($form_data, $sbtButton, $enrlSuccessMsgLabel)
    {        
        $.ajax({
            type: 'POST',
            url: wdmws_notification_object.ajax_url,
            data: $form_data,
            contentType: false,
            processData: false,
            beforeSend: function(){
                $enrlSuccessMsgLabel.hide();
                $sbtButton.addClass('loading');
            },
            success: function ( response ) {
                if('success' == response || 'enrolled' == response)
                {
                    if('success' == response)
                    {
                        $enrlSuccessMsgLabel.text(wdmws_notification_object.enrl_succ_msg);
                        jQuery("form.wdm-notify-me-form > button, .wdmws-notify-me-btn")
                        .attr("disabled", "disabled");
                    }
                    else
                    {
                        $enrlSuccessMsgLabel.text(wdmws_notification_object.already_enrl_msg);
                    }
                    $enrlSuccessMsgLabel.removeClass('wdmws-notify-failure');
                    $enrlSuccessMsgLabel.addClass('wdmws-notify-success');
                    $enrlSuccessMsgLabel.show();
                }
                else
                {
                    $enrlSuccessMsgLabel.text(response);
                    $enrlSuccessMsgLabel.removeClass('wdmws-notify-success');
                    $enrlSuccessMsgLabel.addClass('wdmws-notify-failure');
                    $enrlSuccessMsgLabel.show();
                }
                $sbtButton.removeClass('loading');
            }
        });
    }

    function validateEmailField(msgField, emailAddress)
    {
        if ("" == emailAddress) {
            msgField.text(wdmws_notification_object.email_required_msg);
            msgField.removeClass('wdmws-notify-success');
            msgField.addClass('wdmws-notify-failure');
            msgField.show();
            return false;
        }

        var patt = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
        if (patt.test(emailAddress)) {
            return true;
        } else {
            msgField.text(wdmws_notification_object.email_invalid_msg);
            msgField.removeClass('wdmws-notify-success');
            msgField.addClass('wdmws-notify-failure');
            msgField.show();
            return false;
        }
    }

    $('#wdmws-notify-me-guest-email-modal').keypress(function(e){
        if(e.keyCode == 13)
            {
            $('.wdmws-notify-me-guest-sbt-modal').click();
            event.preventDefault();
            }
        });

        
        $('#notify-me-guest-modal').on('shown.bs.modal', function () {
            $('#wdmws-notify-me-guest-email-modal').focus();
        }) 
    
});
