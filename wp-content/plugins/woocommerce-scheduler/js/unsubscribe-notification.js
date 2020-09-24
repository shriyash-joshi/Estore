jQuery(document).ready(function($){
    $('#wdmws-unsubscribe-submit').click(function(){
        let $form_data = new FormData();
        let $this = $(this);

        $form_data.append('security', $('#unsubscription-options-wrapper #_wpnonce').val());            
        $form_data.append('action', 'wdmws_disenroll_user_from_notification' );
        $form_data.append('unsubscription_type', $('input[name=wdmws-unsubscription]:checked').val());
        $form_data.append('product_id', $('#wdmws-unsubscribe-submit').data('product-id'));
        $form_data.append('product_name', $('#wdmws-unsubscribe-submit').data('product-name'));
        $form_data.append('user_email', $('#wdmws-unsubscribe-submit').data('user-email'));

        $.ajax({
            type: 'POST',
            url: wdmws_unsubscribe.admin_ajax,
            data: $form_data,
            contentType: false,
            processData: false,
            beforeSend: function(){
                $('.wdmws-unsubscription-message').hide();
                $this.addClass('loading');
            },
            success: function (response) {
                $this.removeClass('loading');
                response=response.replace(/(\r\n|\n|\r)/gm, "");
                if ('product' == response) {
                    $('#wdmws-prod-unsub-radio').prop('disabled', true);
                    $('#wdmws-allprod-unsub-radio').prop('checked', true);
                    $('#wdmws-product-unsubscription-message').show();
                } else if ('all' == response) {
                    $this.prop('disabled', true);
                    $('#wdmws-prod-unsub-radio').prop('disabled', true);
                    $('#wdmws-allprod-unsub-radio').prop('disabled', true);
                    $('#wdmws-allproduct-unsubscription-message').show();
                } else {
                    $('#wdmws-error-unsubscription-message').text(response);
                    $('#wdmws-error-unsubscription-message').show();
                }
            }
        });
    });
});
