function erf_util_redirect(url, timer) {
    var timer = timer || 2000;
    if (erf_util_is_url(url)) {
        setTimeout(function () {
            window.location = url;
        }, timer);
    }
}

function erf_util_is_url(str) {
    var pattern = new RegExp('^(https?:\\/\\/)?' + // protocol
            '((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.?)+[a-z]{2,}|' + // domain name
            '((\\d{1,3}\\.){3}\\d{1,3}))' + // OR ip (v4) address
            '(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*' + // port and path
            '(\\?[;&a-z\\d%_.~+=-]*)?' + // query string
            '(\\#[-a-z\\d_]*)?$', 'i'); // fragment locator
    return pattern.test(str);
}


function erf_update_url_query(uri, key, value) {
    var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
    var separator = uri.indexOf('?') !== -1 ? "&" : "?";
    if (uri.match(re)) {
        return uri.replace(re, '$1' + key + "=" + value + '$2');
    } else {
        return uri + separator + key + "=" + value;
    }
}

function erf_handle_form_ajax_response(response, selector, form) {
    var formParentBlock = selector || jQuery(form).closest('.erf-container');
    if (formParentBlock.find('.erf-edit-submission-form .erf-reg-form-container').length > 0) { // Check for edit submission pop up
        formParentBlock = formParentBlock.find('.erf-edit-submission-form .erf-reg-form-container');
    }

    if (response.success)
    {
        /* Remove Form HTML and place Thank You message */
        if (response.msg)
        {
            jQuery('html, body').animate({
                scrollTop: formParentBlock.offset().top
            }, 300);
            jQuery(form).remove();
            formParentBlock.html(response.msg);
        }

        /* Handling redirection */
        if (response.redirect_to)
        {
            if (response.msg == "") {
                erf_util_redirect(response.redirect_to, 0);
            } else
                erf_util_redirect(response.redirect_to);
            return;
        }

        /* Handling page reload */
        if (response.reload && response.form_id) {
            var currentUrl = document.location.href;
            currentUrl = erf_update_url_query(currentUrl, 'erf_form', response.form_id);
            document.location.href = erf_update_url_query(currentUrl, 'erf_auto_login', 1);
            return;
        }
        // Create the event.
        var formSubmitEvent = document.createEvent('Event');

        // Define that the event name is 'erforms_submit_response'.
        formSubmitEvent.initEvent('erforms_submit_response', true, true);
        formSubmitEvent.detail = {'formParent': formParentBlock, 'response': response};
        document.dispatchEvent(formSubmitEvent);
        jQuery(document).trigger('erf_register_common_scripts');
        //erf_global_triggers();
    } else
    {
        var data = response.data;
        var formErrors = [];
        for (i = 0; i < data.length; i++) {
            if (data[i][0]) {
                var fieldElement = jQuery(form).find('input[name=' + data[i][0] + ']');
                if (fieldElement.length > 0) {
                    fieldElement.after('<div class="erf-field-error">' + data[i][1] + '</div>');
                    var parsleyGroup = fieldElement.data('parsley-group');
                    if (parsleyGroup) {
                        var errorPageIndex = parseInt(parsleyGroup.replace('block-', ''));
                        if (!fieldElement.is(':visible')) {
                            jQuery(form).find('[page-index=' + errorPageIndex + ']').addClass('erf-has-errors');
                        }
                    }

                }
                formErrors.push("<div class='erf-error-row'>" + data[i][1] + "</div>");
            }

        }
        erf_show_form_errors(form, formErrors);
    }
}

function erf_show_form_errors(form, errors) {
    $ = jQuery;
    if ($(form).length == 0 || errors.length == 0)
        return;

    var container = $(form).find('.erf-errors');
    container.show();
    for (var i = 0; i < errors.length; i++) {
        container.find('.erf-errors-body').append(errors[i]);
    }
}

Number.prototype.round = function (p) {
    p = p || 10;
    return parseFloat(this.toFixed(p));
};

function geo_ip_lookpup_tel(success, failure) {
    jQuery.get("https://ipinfo.io", function () {}, "jsonp").always(function (resp) {
        var countryCode = (resp && resp.country) ? resp.country : "";
        success(countryCode);
    });
}

function erf_global_triggers() {
    $ = jQuery;
    $(document).trigger('erf_process_form');
    $(document).trigger('erf_process_login_form');
    $(document).trigger('erf_process_my_account');
    $(document).trigger('erf_register_common_scripts');
}

function erf_popup_message(args, container) {
    $ = jQuery;
    if (container.length == 0)
        container = $('body');
    var popup = $('<div/>', {
        class: 'erf-modal erf-modal-small'
    });
    popup.append('<div class="erf-modal-header">&nbsp;<button type="button" class="erf-modal-close">X</button></div><div class="erf-modal-body"></div>');
    popup.find('.erf-modal-body').html(args.content);
    container.append(popup);
    popup.find('.erf-modal-close').click(function () {
        popup.remove();
    });
    popup.show();
}

function erf_get_moment_format(format) {
    switch (format) {
        case 'mm/dd/yy' :
            return 'MM/DD/YYYY';
        case 'dd/mm/yy' :
            return 'DD/MM/YYYY';
        case 'mm-dd-yy' :
            return 'MM-DD-YYYY';
        case 'dd-mm-yy' :
            return 'DD-MM-YYYY';
        default:
            return 'MM/DD/YYYY';
    }
    return 'MM/DD/YYYY';
}

/*
 * Returns data from Global scope (window)
 */
function erforms_get_form_from_gs(formId) {
    if (typeof 'form_' + formId !== 'undefined') {
        return window['form_' + formId];
    }
    return false;
}

/*
 * Set data into Global scope (window)
 */
function erforms_set_form_into_gs(formId, form) {
    window['form_' + formId] = form;
}

function erforms_show_btn_loader(btn, state) {
    if (state) { // Show Loader
        btn.append('<span class="erf-loader"></span>');
        btn.addClass('erf-disabled');
        return;
    }
    // Hide loader
    btn.find('.erf-loader').remove();
    btn.removeClass('erf-disabled');
}

function erforms_random_str(length) {
    var result = '';
    var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    var charactersLength = characters.length;
    for (var i = 0; i < length; i++) {
        result += characters.charAt(Math.floor(Math.random() * charactersLength));
    }
    return result;
}

function erforms_get_field_value(form, fieldName) {
    $ = jQuery;
    var value;
    var nameSelector = "[name='"+ fieldName + "']";
    var field = form.find(nameSelector);
    if (field.length == 0)
        return value;

    if (field.is(':checkbox')) {
        value = [];
        form.find(nameSelector + ":checked").each(function () {
            value.push($(this).val());
        });
    } else if (field.prop('type') == 'select-multiple') {
        value = [];
        form.find(nameSelector + " option:selected").each(function () {
            value.push($(this).val());
        });
    } else if (field.is(':radio')) {
        value = form.find(nameSelector + ":checked").val();
    } else {
        value = field.val();
    }
    return value;
}

function erforms_get_field_value_by_ref(form, ref){
    $ = jQuery;
    var field = form.find("[data-ref-label='" + ref + "']");
    var value;
    if (field.length == 0)
        return value;
    var fieldName = field.attr('name');
    return erforms_get_field_value(form, fieldName);
}

function erforms_set_field_value(form, fieldName, fieldValue) {
    $ = jQuery;
    var field = form.find("[name=" + fieldName + "]");
    if (field.length == 0) {
        field = $("[name='" + fieldName + "[]']");
    }
    if (field.length == 0)
        return false;
    if (field.is(':radio') || field.is(':checkbox')) {
        field.each(function () {
            if (Array.isArray(fieldValue)) {
                if (fieldValue.indexOf($(this).val()) >= 0) {
                    $(this).prop('checked', true);
                }
            } else if ($(this).val() == fieldValue) {
                $(this).prop('checked', true);
            }
        });
    } else if (field.is(':file')) {
        return false;
    } else{
        field.val(fieldValue);
        if(field.attr('type')==='tel'){
            erforms_set_intl_tel_field(form,field);
        }
    }
    return field;
}

function erforms_set_intl_tel_field(form,field) {
    $= jQuery;
    var formId = form.data('erf-form-id');
    var data = typeof(window['form_' + formId]) !== 'undefined' ? window['form_' + formId].form_meta : undefined;
    if(data===undefined) return;
    data.tel_config.hiddenInput = field.attr('name') + "-intl";
    data.tel_config.geoIpLookup = window[data.tel_config.geoIpLookup];
    var hiddenInput = data.tel_config.hiddenInput;
    field.intlTelInput(data.tel_config);
    var dialCode = '';
    field.bind('input', function () {
        dialCode = field.intlTelInput("getSelectedCountryData").dialCode;
        if (dialCode)
            form.find('[name="' + hiddenInput + '"]').val(field.intlTelInput('getNumber'));
    });
    field.on("countrychange", function () {
        form.find('[name="' + hiddenInput + '"]').val(field.intlTelInput('getNumber'));
    });
    dialCode = field.intlTelInput("getSelectedCountryData").dialCode;
    if (dialCode) {
        setTimeout(function () {
            form.find('[name="' + hiddenInput + '"]').val(field.intlTelInput("getNumber"));
        }, 1000);
    }
}