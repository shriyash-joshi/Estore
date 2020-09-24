(function ($) {
    /*****************************Front Form *******************/
    $(document).bind('erf_process_form', function (ev, selector, submission) {
        var erfContainer = selector || $(".erf-container");
        var formContainer = erfContainer.find('.erf-front-form');

        if (formContainer.length == 0)
            return;

        /*****************************Parsley configuration *******************/
        var parsleyConfig = {//Parsley error container configuration
            errorsContainer: function (pEle) {
                var $err = pEle.$element.closest('.form-group');
                return $err;
            },
        }
        // Adding custom parsley validator for Confirm Password field
        window.Parsley.addValidator('confirmPassword', {
            validateString: function (value, passwordFieldId) {
                return $("#" + passwordFieldId).val() == value;
            },
            messages: {
                en: erf_global.parsley.confirmPassword,
            }
        });
        parsleyConfig.excluded = 'input:hidden,select:hidden,textarea:hidden,file:hidden,:disabled,.erf-disabled';
        /*****************************Parsley configuration ends here *******************/

        if (erf_global.logged_in == 1) {
            formContainer.find("[user_roles=true]").attr('disabled', true);
        }

        /****************** Form Layout function ****************/
        var twoColumnLayout = function (form, selector) {
            form.find(selector).each(function () {
                var page = $(this);
                var pageElements = page.children('div');
                var twoColumnWrapper = $('<div class="erf-two-columns" />');
                var row = [];
                for (i = 0; i < pageElements.length; i++) {
                    var field = $(pageElements[i]);
                    var fieldNext = $(pageElements[i + 1]);

                    if (field.hasClass('form-group') && fieldNext.length > 0 && fieldNext.hasClass('form-group')) {
                        pageElements.filter(':eq(' + i + '),:eq(' + (i + 1) + ')').wrapAll(twoColumnWrapper);
                        i++;
                    } else {
                        field.wrap(twoColumnWrapper);
                    }
                }

            });
        }

        var getFormLayout = function (form) {
            var formContainer = form.closest('.erf-container');
            if (formContainer.hasClass('erf-layout-two-column'))
            {
                return 2;
            }
            return 1;
        }
        /****************** Form Layout functions ends here ****************/

        /****************** Ajax Form submission ***************************/
        submit_form = function (form) {
            var formData = new FormData(form);
            $.ajax({
                url: erf_global.ajax_url,
                type: 'POST',
                data: formData,
                async: true,
                success: function (response) {
                    $(document).trigger('erf_sub_response_received', [form, response]);
                    erf_handle_form_ajax_response(response, selector, form);
                },
                cache: false,
                contentType: false,
                processData: false
            });
        }
        /****************** Ajax Form submission ends here ***************************/

        /****************** Multipage functions **************************************/
        var paginateForm = function (form) {
            var navContainer = form.find('.erf-form-nav'); // It holds previous button
            var pages = []; // Holds all the pages. Note: There are no HTML sections to hold page elements.
            var pageBreaks = []; // Elements which defines page break
            var submitButton = form.find('.erf-submit-button button');
            var submitButtonTitle = submitButton.html();
            var nextButton;
            var prevButton;
            var hideTabs = false;

            // Adds index to each input element so that Parsley can validate them in individual group.
            var addElementGroup = function (formElements, index) {
                formElements.attr('data-parsley-group', 'block-' + index);
                formElements.find(':input').attr('data-parsley-group', 'block-' + index);
            }

            // Ads and registers previous button 
            var addNavButtons = function (index) {
                navContainer.html('');
                // Creating nav buttons
                submitButton.removeClass('erf-nav-next');
                prevButton = submitButton.clone();
                prevButton.attr('type', 'button');
                prevButton.addClass('erf-nav-prev');
                prevButton.html(erf_global.js_strings.prev);
                nextButton = submitButton.clone();
                nextButton.html(erf_global.js_strings.next);
                nextButton.addClass('erf-nav-next');

                prevButton.click(function () {
                    var selectedIndex = parseInt(navContainer.attr('current-page-index')) - 1;
                    goToPage(selectedIndex);
                });
                if (index > 0)  // Do not add Previous button on first Page
                    navContainer.append(prevButton);
                navContainer.append(nextButton);
                if (index == (pages.length - 1)) {
                    nextButton.html(submitButtonTitle);
                }
                submitButton.remove();
            }

            // Remove Page Break closest DIV
            form.find('div[custom-type=page-break]').unwrap();

            // Check if first element is page Break.
            var firstPageBreak = form.find('.rendered-form div').first().attr('custom-type');
            if (firstPageBreak != 'page-break')  // First element is not page break. 
            {
                form.find('.rendered-form').prepend('<div custom-type="page-break" class="page-break">Page Break</div>');
                hideTabs = true; // Hides tab system for Page breaks
            }

            var pageBreaks = form.find('div[custom-type=page-break]');
            pageBreaks.addClass('page-break');
            navContainer.attr('current-page-index', 0);   // Assigning current page index    
            if (pageBreaks.length > 1) {
                form.find('.erf-external-form-elements').hide(); // Hiding external form elements for multipage forms
            }

            var goToPage = function (selectedIndex, next) {
                var currentPageIndex = form.find('.active-page').attr('page-index');
                var next = next || false;
                if (next || selectedIndex < currentPageIndex) {
                    for (i = 0; i < pages.length; i++) {
                        if (selectedIndex == i)
                        {
                            pages[i].show();
                            navContainer.attr('current-page-index', selectedIndex);
                            pageBreaks.removeClass('active-page');
                            $(pageBreaks[selectedIndex]).addClass('active-page');
                            navContainer.html('');
                        } else
                            pages[i].hide();
                    }
                    addNavButtons(selectedIndex);
                }

                if ((selectedIndex + 1) == pageBreaks.length) {
                    form.find('.erf-external-form-elements').show(); // Showing external form elements at last pagination
                } else {
                    form.find('.erf-external-form-elements').hide();
                }
                // Scroll on top
                $('html, body').animate({
                    scrollTop: form.closest('.erf-container').offset().top
                }, 300);
            }
            if (hideTabs || pageBreaks.length == 1) {
                pageBreaks.hide();
            }
            if (pageBreaks.length > 1) {
                pageBreaks.each(function (index) {
                    var formElements = $(this).nextUntil('div[custom-type=page-break]');
                    if (formElements.length == 0) { // Inserting blank page
                        formElements = $('<div class="form-group">&nbsp;</div>');
                    }
                    formElements.wrapAll('<div class="erf-page erf-page-' + index + '"></div>');
                    pages.push(formElements);
                    addElementGroup(formElements, index);
                    if (index > 0)
                        formElements.hide(); // Be default hiding all the page elements except initial page
                });
            }


            if (pages.length > 1) {


                form.attr('erf-multipage', 1); // Adding multipage flag

                // By default show first page elements
                pages[0].show();

                // Adding index properties to each page break, Appends page breaks in form starting
                for (i = 0; i < pageBreaks.length; i++) {
                    let span = document.createElement("span");
                    span.innerHTML = $(pageBreaks[i]).html();
                    $(pageBreaks[i]).html(span);
                    $(pageBreaks[i]).attr('page-index', i);
                    if (i == 0) {
                        $(pageBreaks[i]).addClass('active-page');
                    }
                    form.find('.rendered-form').before(pageBreaks[i]);
                }
                pageBreaks.wrapAll("<div class='erf-page-breaks'></div>");

                // Registers click event to allow jumping to previous pages
                pageBreaks.click(function () {
                    var selectedIndex = $(this).attr('page-index');
                    goToPage(selectedIndex);
                });

                var formLayout = getFormLayout(form);
                if (formLayout == 2)
                {
                    twoColumnLayout(form, '.erf-page');
                }


                /*
                 * Binds submit button.
                 * Hides previous page elements and shows new elements (If any)
                 * Registers parsley validation.
                 * Sends ajax request on successfull validation.
                 */
                $(form).submit(function (event) {
                    event.preventDefault();
                    var currentIndex = parseInt(navContainer.attr('current-page-index'));
                    var nextIndex = currentIndex + 1;
                    var formInstance = form.parsley(parsleyConfig);
                    pageBreaks.removeClass('erf-has-errors');
                    if (pages[nextIndex] !== void 0) {
                        goToPage(nextIndex, true);
                    }
                    formInstance.whenValidate({
                        group: 'block-' + currentIndex
                    }).done(function () {  // Triggers for last page.
                        if (nextIndex == pages.length) {
                            $(document).trigger('erf_form_submit_pressed', [form]);
                            // Check if reCaptcha token has to be added
                            if (form.find('.erf-recaptcha').length > 0 && erf_global.recaptcha_v == 3 && typeof grecaptcha !== 'undefined' && grecaptcha) {
                                grecaptcha.ready(function () {
                                    erf_recaptcha.execute('submission', function (token) {
                                        form.find('[name="g-recaptcha-response"]').remove(); // Removing any existing token
                                        form.prepend('<input type="hidden" name="g-recaptcha-response" value="' + token + '">');
                                        submit_form(form[0]);
                                    });
                                });
                            } else {
                                submit_form(form[0]);
                            }
                        }
                    });

                });
            }
        }
        /****************** Multipage functions ends here ****************************/


        formContainer.each(function () {
            var formInstance = $(this).parsley(parsleyConfig);
            Parsley.addMessages('en', erf_global.parsley);

            // Cloning submit button at last to add any elements externally in Form and delete auto generated button
            var submitBtnBlock = $(this).find('.erf-submit-button');
            var genratedButtonBlock = $(this).find('[type=submit]').closest('.form-group ');

            if (genratedButtonBlock.length > 0) {
                genratedButtonBlock.clone().appendTo(submitBtnBlock);
                genratedButtonBlock.remove();
            }


            var form = $(this);
            var formLayout = getFormLayout(form);
            var formGroups = form.find('.rendered-form');

            /* Change user_email type to email */
            var userEmailField = form.find('input[type=user_email]');
            if (userEmailField.length > 0) {
                userEmailField.attr('data-parsley-type', "email");
                userEmailField.attr('type', "email");
            }
            
            
            /**
             * Check Merge fields in RichText
             */
            var rtElements = form.find('.erf-rich-text');
            rtElements.each(function(){
               let el = $(this); 
            });
            /*
             * Initialize datepicker for each of the date field.
             */
            form.find('.erf-date-field').each(function () {
                var minDate = $(this).attr('min');
                var maxDate = $(this).attr('max');
                var dateFormat = $(this).data('date-format');
                var dateConfig = {dateFormat: dateFormat, changeMonth: true, changeYear: true, yearRange: '-100:+20', monthNames: erf_global.datepicker_defaults.monthNames,
                    monthNamesShort: erf_global.datepicker_defaults.monthNamesShort, closeText: erf_global.datepicker_defaults.closeText, currentText: erf_global.datepicker_defaults.currentText,
                    nextText: erf_global.datepicker_defaults.nextText, prevText: erf_global.datepicker_defaults.prevText, dayNames: erf_global.datepicker_defaults.dayNames, dayNamesShort: erf_global.datepicker_defaults.dayNamesShort,
                    dayNamesMin: erf_global.datepicker_defaults.dayNamesMin, firstDay: erf_global.datepicker_defaults.firstDay, isRTL: erf_global.datepicker_defaults.isRTL};
                if (minDate)
                    dateConfig.minDate = new Date(minDate);
                if (maxDate)
                    dateConfig.maxDate = new Date(maxDate);
                dateConfig.onSelect = function (date) {
                    $(this).trigger('input');
                } // Triggering 'input' event on every date select action
                $(this).datepicker(dateConfig);
                $(this).removeAttr('min'); // Removing min attribute to disable default field validation
                $(this).removeAttr('max');  // Removing max attribute to disable default field validation
            });


            // Current URL for after login redirect (Only for registration forms)
            form.find('#erform_redirect_to').val($(location).attr('href'));

            // Button position
            form.find('button[data-erf-btn-pos]').each(function () {
                var positionName = $(this).data('erf-btn-pos');
                if (positionName) {
                    $(this).closest('.erf-button').addClass('erf-btn-' + positionName);
                }

            });

            /* Payment related */
            var paymentWrapper = form.find('.erf-payment-wrapper');
            if (paymentWrapper.length > 0) {
                //formGroups.append(paymentWrapper.clone(true, true).html());
                //paymentWrapper.remove();

                var getPrice = function (element) {
                    var priceValue = 0;
                    var planType = element.data('plan-type');
                    if (planType == '')
                        return;
                    if (planType == 'user') {
                        priceValue = parseFloat(element.val());
                        if (isNaN(priceValue)) {
                            return 0;
                        }
                        priceValue = priceValue;
                    } else if (planType == 'product') {
                        if (element.is(':checked')) {
                            priceValue = parseFloat(element.data('erf-price'));
                            if (isNaN(priceValue)) {
                                return 0;
                            }
                            priceValue = priceValue;
                        }
                    }

                    return priceValue;
                }

                var updatePrieHTML = function (finalPrice) {
                    var totalPaymentContainer = form.find('.erf-total-payment:first');
                    totalPaymentContainer.html(finalPrice.round(2));
                    if (finalPrice <= 0) {
                        form.find('.erf-payment-methods').slideUp();
                    } else
                    {
                        form.find('.erf-payment-methods').slideDown();
                    }
                }
                form.find('.erf-price').on('input', function () {
                    var finalPrice = 0;
                    var tempPrice = 0;

                    $('.erf-price').each(function () { // Calculating price from each plan field
                        tempPrice = getPrice($(this));
                        if (tempPrice < 0) {
                            tempPrice *= -1;
                        }
                        finalPrice += tempPrice;
                    });
                    updatePrieHTML(finalPrice);
                });

                form.find('input.erf-price').on('keyup blur', function () {
                    var finalPrice = 0;
                    var tempPrice = 0;
                    $('.erf-price').each(function () { // Calculating price from each plan field
                        tempPrice = getPrice($(this));
                        if (tempPrice < 0) {
                            tempPrice *= -1;
                        }
                        finalPrice += tempPrice;
                    });
                    updatePrieHTML(finalPrice);
                });

            }
            /* Payment ends here */

            paginateForm(form);
            var is_multipage = form.attr('erf-multipage');
            if (is_multipage != 1) {
                if (formLayout == 2) // Check if two column layout
                {
                    twoColumnLayout(form, '.rendered-form');
                }

                $(this).submit(function (event) {
                    event.preventDefault();
                    var tempFormInstance = $(this)[0];
                    $(document).trigger('erf_form_submit_pressed', [$(this)]);
                    /*
                     * Generating reCaptcha v3 token before submission (If configured).
                     */
                    if (form.find('.erf-recaptcha').length > 0 && erf_global.recaptcha_v == 3 && typeof grecaptcha !== 'undefined' && grecaptcha) {
                        grecaptcha.ready(function () {
                            erf_recaptcha.execute('submission', function (token) {
                                form.find('[name="g-recaptcha-response"]').remove(); // Removing any existing token
                                form.prepend('<input type="hidden" name="g-recaptcha-response" value="' + token + '">');
                                submit_form(tempFormInstance);
                            });
                        });
                    } else
                    {
                        submit_form(tempFormInstance);
                    }
                });
            } else
            {
                submitBtnBlock.find('button').html(erf_global.js_strings.next);
                submitBtnBlock.find('button').addClass('erf-nav-next erf-submit-button');
            }

            // Procssing hook for each form.
            var formID = form.data('erf-form-id');
            var submissionID = form.data('erf-submission-id');
            var submission = null;
            var formData = null;
            var temp = erforms_get_form_from_gs(formID);
            if (temp) {
                if (temp.hasOwnProperty('submission')) {
                    submission = temp.submission;
                }
                /*
                 * Interpolating form fields (File fields excluded) to register "input" event for two way binding.
                 * It must be done before prefilling the data.
                 */
                if (temp.hasOwnProperty('form')) {
                    formData = temp.form;
                    $(document).trigger('erf_interpolate_form', [form, formData]);
                }

                var userMeta;
                if (temp.hasOwnProperty('user_meta')) {
                    userMeta = temp.user_meta;
                }
                /*
                 * Interpolating File upload fields  to register "input" event for two way binding.
                 * It must be done after prefilling the data.
                 */
                if (temp.hasOwnProperty('form')) {
                    $(document).trigger('erf_interpolate_file_fields', [form, formData]);
                }
                $(document).trigger('erf_process_form_conditions', [form, submission, formData]);
                $(document).trigger('erf_process_form_dynamic_fields', [form, submission, formData, userMeta]);
                $(document).trigger('erf_edit_submission_form', [form, submission, formData]);
                prefilUsermeta(userMeta, form);  // Prefil user meta.
            }
            var validImageTypes = ['image/gif', 'image/jpeg', 'image/png', 'image/jpg'];
            form.find('.erf-file input[type=file]').each(function () {
                $(this).on('input', function (event) {
                    if (event.target.files.length == 0)
                        return;
                    if (window.FileReader) {
                    } else {
                        return;
                    }
                    var imageFile = event.target.files[0];
                    var fileContainer = $(this).closest('.erf-file');
                    fileContainer.find('.file_preview').remove();
                    var fileType = imageFile['type'];
                    if (!(validImageTypes.indexOf(fileType) > -1)) {
                        return;
                    }
                    var previewContainer = $('<div/>', {class: 'erf_img_preview file_preview'});
                    var preview = $('<img/>');
                    fileContainer.append(previewContainer);
                    previewContainer.append(preview)
                    var fileReader = new FileReader();
                    fileReader.onload = function () {
                        preview.attr('src', fileReader.result);
                    }
                    fileReader.readAsDataURL(imageFile);
                });
            });
            // Providing blank value to dropdown options where no value is defined.
            form.find('.erf-select select option:not([value])').val('');
            form.find('[type=number]').attr('step', 'any'); // To allow use of decimal in number field
            form.find('.hide-label').closest('.form-group').find('label:first').hide(); // Hiding labels for indiviual fields on class basis ('hide-label')
        });


        /*
         * Handling Other Option for Checkbox and Radio Buttons
         */
        formContainer.find('.other-val').hide();
        formContainer.find('.other-val').closest('label').removeAttr('for'); // Removing parent's label's form attribute to disable hiding of textfield on focus.
        formContainer.find('.other-val').closest('label').contents().filter(function () {
            return this.nodeType == 3;
        }).replaceWith(erf_global.js_strings.other);
        formContainer.find('input.other-option').each(function () {
            if ($(this).is(':radio')) { // Radio Button handling
                var radioGroupName = $(this).attr('name');
                var parentGroup = $(this).closest('.radio-group');
                var otherElementId = '#' + radioGroupName + '-other-value';
                formContainer.find("[name='" + radioGroupName + "']").on('input', function () {
                    if ($(this).hasClass('other-option')) {
                        parentGroup.find(otherElementId).slideDown();
                        parentGroup.find(otherElementId).keyup(function () {
                            parentGroup.find('.other-option').val($(this).val());
                        });
                    } else {
                        parentGroup.find(otherElementId).slideUp();
                    }
                });
            } else {
                $(this).on('input', function () {
                    var self = $(this);
                    var otherElement = self.siblings('label').children('.other-val');
                    if (otherElement.length == 0)
                        return;

                    if ($(this).is(':checked')) {
                        otherElement.slideDown();
                        otherElement.keyup(function () {
                            self.val(otherElement.val());
                        });
                        return;
                    }
                    otherElement.slideUp();
                });
            }

        });
        // Other option handling ended. 

        formContainer.find('input[masking]').each(function () {
            var pattern = $(this).attr('masking');
            if (pattern) {
                var target = this;
                $(target).mask(pattern);
            }
        });

        formContainer.find('div[custom-type=spacer]').each(function () {
            var height = $(this).attr('height');
            $(this).html('');
            if (height) {
                $(this).css('height', height + 'px');
            }
        });



        /*
         * Used for Front form (For admin only)
         */
        var erforms_change_form_layout = function (form) {
            var formData = new FormData(form);
            $.ajax({
                url: erf_global.ajax_url,
                type: 'POST',
                data: formData,
                async: false,
                success: function (response) {
                    try {
                        if (response.success)
                        {
                            location.reload();
                        }
                    } catch (ex) {
                    }
                },
                cache: false,
                contentType: false,
                processData: false
            });
        }

    });
    $(document).bind('erf_form_submit_pressed', function (ev, form) {
        // Remove all previous errors and error blocks
        form.find('[custom-type=page-break]').removeClass('erf-has-errors');
        form.find('.erf-errors').hide();
        form.find('.erf-errors .erf-errors-body').html('');
        var formParentBlock = form.closest('.erf-container');
        formParentBlock.find('.erf-field-error').remove();

        var submitButton = form.find('.erf-submit-button button');
        if (submitButton.length == 0) { // In case of multipage form
            submitButton = form.find('.erf-form-nav :submit');
        }
        erforms_show_btn_loader(submitButton, true);
    });

    $(document).bind('erf_sub_response_received', function (ev, form, response) {
        form = $(form);
        var submitButton = form.find('[type="submit"]');
        if (submitButton.length > 0) {
            submitButton.removeClass('erf-disabled');
            submitButton.find('.erf-loader').remove();
        }
        // Check for reCaptcha elements
        if (form.find('.erf-recaptcha').length > 0 && erf_global.recaptcha_v == 2 && typeof grecaptcha !== undefined) {
            grecaptcha.reset();
        }
    });


    var prefilUsermeta = function (data, form) {
        if (typeof data === 'undefined')
            return;
        if (data.hasOwnProperty('attachments')) {
            for (var key in data.attachments) {
                attachFileWithField(form, data.attachments[key], 0, false);
            }
            delete data.attachments;
        }

        for (var key in data) {
            var metaVal = data[key];
            var field = $("[name=" + key + "]");
            if (field.length == 0) {
                field = $("[name='" + key + "[]']");
                if (field.length == 0)
                    continue;
            }
            if (field.is(':checkbox')) {
                if (metaVal instanceof Array) {
                    field.each(function () {
                        $(this).prop("checked", ($.inArray($(this).val(), metaVal) != -1));
                    });
                }
            } else if (field.is(':file')) {
                continue;
            } else if (field.is(':radio')) {
                field.filter('[value="' + metaVal + '"]').attr('checked', true);
            } else {
                field.val(metaVal);
            }
            field.trigger('input');
        }
    }
    $(document).bind('erf_interpolate_form', function (ev, form, formData) {
        interpolate_form(form, formData);
    });

    $(document).bind('erf_interpolate_file_fields', function (ev, form, formData) {
        interpolate_file_fields(form, formData);
    });

    /*Dynamic Field Population */
    $(document).bind('erf_process_form_dynamic_fields', function (ev, form, submission, formData, userMeta) {
        var formId = form.find('[name=erform_id]').val();
        var applyConfiguration = function (data) {
            form.find('[type=tel][enable-intl=1]').each(function () {
                let self = this;
                setTimeout(function () { // Delaying explicitely 
                    erforms_set_intl_tel_field(form, $(self));
                }, 1000);
            });

            // Password field configuration
            if (data.hasOwnProperty('pass_config')) {
                form.find('[type=password]').each(function () {
                    $(this).password(data.pass_config);
                });
            }
        }

        var fieldCommandsOnLoad = function (field_name) {
            var temp = erforms_get_form_from_gs(formId);
            if (temp && temp.hasOwnProperty('load_commands')) {
                var commands = temp.load_commands;
                var dynamicFields = [];
                for (var i = 0; i < commands.length; i++) {
                    var command = commands[i];
                    for (var j = 0; j < command.on.length; j++) {
                        var field_name = command.on[j];
                        dynamicFields.push(field_name);
                        if (command.options) {
                            setDropdownOptions(field_name, command.data);
                        }
                        if (typeof (command.default_value) !== 'undefined') {
                            if (Array.isArray(command.default_value) && command.default_value[i]) {
                                erforms_set_field_value(form, field_name, command.default_value[j]);
                            } else {
                                erforms_set_field_value(form, field_name, command.default_value);
                            }
                        }
                    }

                    if (command.callback) {
                        if (typeof window[command.callback] === "function")
                        {
                            window[command.callback](form, command);
                        }
                    }
                }
                if (userMeta !== undefined) {
                    var tempUserMeta = $.extend({}, userMeta);
                    for (var key in tempUserMeta) {
                        if (dynamicFields.indexOf(key) == -1) {
                            delete tempUserMeta[key];
                        }
                    }
                    prefilUsermeta(tempUserMeta, form); // Prefil data after loading dynamic fields
                }
                $(document).trigger('erf_edit_submission_field', [form, field_name, submission]);
            }
        }

        var getElementValue = function (name) {
            var fieldInstance = form.find('[name="' + name + '"]');
            var value = fieldInstance.val();
            return value;
        }

        var setElementValue = function (name, value) {
            var fieldInstance = form.find('[name="' + name + '"]');
            if (fieldInstance.is(':radio')) {
                fieldInstance.filter('[value="' + value + '"]').prop('checked', true);
            } else if (fieldInstance.is(':checkbox')) {
                if (value) {
                    if (value.constructor === Array) {
                        for (var i = 0; i < value.length; i++) {
                            fieldInstance.filter('[value="' + value[i] + '"]').prop('checked', true);
                        }
                    } else
                    {
                        fieldInstance.filter('[value="' + value + '"]').prop('checked', true);
                    }
                }

            } else {
                fieldInstance.val(value);
            }
            fieldInstance.trigger('input');
        }
        var setDropdownOptions = function (name, values) {
            var fieldInstance = form.find('[name="' + name + '"]');
            fieldInstance.empty();
            if (values.length == 0) {
                fieldInstance.editableSelect();
            } else
            {
                if (fieldInstance.hasClass('es-input')) {
                    fieldInstance.editableSelect('destroy'); // In case it is an editable select
                }
                var fieldInstance = form.find('[name="' + name + '"]');
                $.each(values, function (val, label) {
                    fieldInstance.append($('<option>', {
                        value: val,
                        text: label
                    }));
                });
            }
        }

        var fieldCommandOnChange = function (rules) {
            for (var i = 0; i < rules.length; i++) {
                var changeRule = rules[i];
                (function (changeRule) {
                    var data = {action: 'erforms_field_change_command', change_action: changeRule.action, field_name: changeRule.field_name, form_id: formId};
                    var fieldInstance = form.find('[name="' + changeRule.field_name + '"]');

                    if (fieldInstance.length == 0)
                        return;

                    if (fieldInstance.prop('disabled')) {
                        return;
                    }

                    fieldInstance.on('input', function () {
                        if (fieldInstance.changeRequest) {
                            fieldInstance.changeRequest.abort();
                        }
                        data.field_value = getElementValue(changeRule.field_name);
                        fieldInstance.changeRequest = $.post(erf_global.ajax_url, data, function (res) {
                            if (res.success) {
                                var commands = res.data.commands;
                                var dynamicFields = [];
                                for (var i = 0; i < commands.length; i++) {
                                    var command = commands[i];
                                    for (var j = 0; j < command.on.length; j++) {
                                        var field_name = command.on[j];
                                        dynamicFields.push(field_name);
                                        if (command.options) {
                                            setDropdownOptions(field_name, command.data)
                                        }
                                        if (typeof (command.default_value) !== 'undefined') {
                                            if (Array.isArray(command.default_value) && command.default_value[j]) {
                                                erforms_set_field_value(form, field_name, command.default_value[j]);
                                            } else {
                                                erforms_set_field_value(form, field_name, command.default_value);
                                            }
                                        }
                                    }
                                    if (command.callback) {
                                        if (typeof window[command.callback] === "function")
                                        {
                                            window[command.callback](form, fieldInstance, data, command);
                                        }
                                    }
                                    fieldInstance.parsley().removeError(fieldInstance.prop('name'));
                                    if (command.error) {
                                        fieldInstance.parsley().addError(fieldInstance.prop('name'), {message: command.error, updateClass: true});
                                    } else
                                    {
                                        fieldInstance.parsley().removeError(fieldInstance.prop('name'));
                                    }

                                }
                                if (userMeta !== undefined) {
                                    var tempUserMeta = $.extend({}, userMeta);
                                    for (var key in tempUserMeta) {
                                        if (dynamicFields.indexOf(key) == -1) {
                                            delete tempUserMeta[key];
                                        } else
                                        {
                                            delete userMeta[key];
                                        }
                                    }
                                    prefilUsermeta(tempUserMeta, form); // Prefil data after loading dynamic fields
                                }
                                $(document).trigger('erf_edit_submission_field', [form, field_name, submission]);
                            }
                        }).fail(function (xhr, textStatus, e) {
                            //console.log(xhr.responseText);
                        });
                    });
                })(changeRule)
            }
        }

        if (typeof 'form_' + formId !== undefined) {
            var temp = window['form_' + formId].form_meta;
            var rules = temp.dynamic_rules;
            if (rules.load.length > 0) {
                fieldCommandsOnLoad();
            }

            if (rules.change.length > 0) {
                fieldCommandOnChange(rules.change);
            }
            applyConfiguration(temp);
        }
    });


    /** Formula ends here */

    /*****************************Front form ends here*******************/

    /*****************************Edit submissions here ****************/
    $(document).on('erf_edit_submission_form', function (ev, form, submission, formData)
    {
        if (typeof form === "undefined" || !formData || !submission)
            return;
        var prepareForm = function () {
            if (!erf_global.is_admin) {
                form.find(':input,:radio,:checkbox').not('[type="submit"]').addClass('erf-disabled');  // By default disable all the fields
                if (formData.hasOwnProperty('en_edit_sub') && formData.hasOwnProperty('edit_fields') && formData.edit_fields.length > 0) {
                    for (var i = 0; i < formData.edit_fields.length; i++) {
                        var single = $("[name=" + formData.edit_fields[i] + "]"); // For single fields
                        var multi = $("[name='" + formData.edit_fields[i] + "[]']"); // For array type of fields
                        if (single.length > 0) {
                            single.removeClass('erf-disabled');
                        }

                        if (multi.length > 0) {
                            multi.removeClass('erf-disabled');
                        }
                    }
                }
            }
            $.each(submission.fields_data, function (key, field_data) {
                $(document).trigger('erf_edit_submission_field', [form, field_data.f_name, submission]);
            });
            $('<input>').attr({style: 'display:none;', type: 'text', name: 'submission_id', value: submission.id}).appendTo(form);
            if (submission.hasOwnProperty('attachments')) {
                // Looping through attachment fields to generate preview and add delete action.
                for (var c = 0; c < submission.attachments.length; c++) {
                    attachFileWithField(form, submission.attachments[c], submission.id, true);
                }
            }
        }
        prepareForm();
    });
    $(document).on('erf_edit_submission_field', function (ev, form, fieldName, submission) {
        if (!submission)
            return;
        $.each(submission.fields_data, function (key, field_data) {
            if (fieldName == field_data.f_name) {
                var field = erforms_set_field_value(form, fieldName, field_data.f_val);
                if (field !== false) {
                    field.trigger('input');
                }
            }
        });

    });
    /*****************************Edit submissions ends here ****************/

    /*****************************Utils ****************/

    var erf_recaptcha = {
        execute: function (action, callback) {
            grecaptcha.execute(erf_global.rc_site_key, {'action': action}).then(function (token) {
                callback(token);
            });
        }
    }

    var attachFileWithField = function (container, attachment, submissionID, allowDeletion) {
        var allowDeletion = allowDeletion || false;
        var attachment_field = container.find("[name='" + attachment.f_name + "']");
        if (attachment_field.length == 0)
            return;
        if (attachment_field.hasClass('erf-disabled')) {
            allowDeletion = false;
        }
        attachment_field.find('.file_preview').remove(); // Avoid duplicate file previews
        var file_exists = false;
        var deleteFile;
        // Add <image> tag if field has any image_url.
        if (attachment.hasOwnProperty('image_url')) {
            var fileContainer = attachment_field.closest('.erf-file');
            var previewContainer = $('<div/>', {class: 'erf_img_preview file_preview'});
            var preview = $('<img/>');
            previewContainer.append(preview);
            preview.attr('src', attachment.image_url);
            if (allowDeletion) {
                deleteFile = $('<a/>', {class: 'erf_delete_attachment'});
                deleteFile.html(erf_global.js_strings.delete_file);
                previewContainer.append(deleteFile);
            }
            fileContainer.append(previewContainer);
            file_exists = true;
        }
        // Add <a> tag if file in non image.
        if (attachment.hasOwnProperty('link_url')) {
            var fileContainer = attachment_field.closest('.erf-file');
            var previewContainer = $('<div/>', {class: 'erf_link_preview file_preview'});
            var preview = $('<a/>');
            fileContainer.append(previewContainer);
            previewContainer.append(preview);
            preview.attr('href', attachment.link_url);
            preview.html(attachment.link_label);
            preview.attr('target', '_blank');
            if (allowDeletion) {
                deleteFile = $('<a/>', {class: 'erf_delete_attachment'});
                deleteFile.html('&nbsp;' + erf_global.js_strings.delete_file);
                previewContainer.append(deleteFile);
            }
            file_exists = true;
        }

        if (file_exists) {
            var isRequired = attachment_field.prop('required');
            attachment_field.removeAttr('required');
            attachment_field.closest('.erf-file').find('.erf-required').hide();
            if (allowDeletion) {
                // Registerting delete action to allow existing file removal.
                deleteFile.click(function (e) {
                    e.preventDefault();
                    if (isRequired) { // Adding required attribute if field is required.
                        attachment_field.attr('required', 'required');
                        attachment_field.closest('.erf-file').find('.erf-required').show();
                    }
                    deleteFile.closest('.file_preview').remove();
                    $.post(erf_global.ajax_url, {file_id: attachment.f_val, f_name: attachment.f_name, submission_id: submissionID, action: 'erforms_delete_sub_attachment'}, function (res) {
                        if (res.success) {
                            $(document).trigger('erf_sub_file_deleted', [attachment.f_val, res]);
                            attachment_field.trigger('input');
                        }
                    }).fail(function (xhr, textStatus, e) {
                        alert('Unable to connect to server');
                    });
                });
            }
        }
    }

    /*
     * Interpolate form and registers the events to handle two way binding for the 
     * fields short tag (__Field'label__) and input field. 
     * Note: It do not interpolates File fields.
     */
    function interpolate_form(form, formData) {
        var nonInputFields = ['paragraph', 'button'];
        var elems = form.find('.erf_dynamic_ph');
        // Looping through all the form input fields to identify span elements with field placeholders
        if (elems.length > 0) {
            for (var i = 0; i < formData.fields.length; i++) {
                let singleField = formData.fields[i];
                if (singleField.hasOwnProperty('type') && nonInputFields.indexOf(singleField.type) == -1) {
                    singleField.label = singleField.label.toLowerCase();
                    let searchLabel = '__' + singleField.label + '__';
                    let searchRefLabel = '%' + singleField.dataRefLabel.toLowerCase() + '%';
                    elems.each(function () {
                        var item = $(this);
                        if (item.text().toLowerCase() == searchLabel || item.text().toLowerCase() == searchRefLabel) {
                            //item.html('');
                            /*************** Closure ******************/
                            (function (fieldName) {

                                // Handling single input fields
                                form.find('[name="' + fieldName + '"]').on('input', function () {
                                    item.html($(this).val());
                                });

                                // Handling multi input fields (Checkbox,Multiselect dropdown)
                                form.find('[name="' + fieldName + '[]"]').on('input', function () {
                                    var selectedValues = []; // Stores only checked/selected values
                                    if ($(this).is(':checkbox')) {
                                        form.find('[name="' + fieldName + '[]"]:checked').each(function () {
                                            selectedValues.push($(this).val());
                                        });
                                    } else if ($(this).is('select')) {
                                        selectedValues = $(this).val();
                                    }
                                    item.html(selectedValues.join(','));
                                });

                                // Checking for file previews
                                var fileField = form.find('[name="' + fieldName + '"]:file');
                                if (fileField.length > 0) {
                                    var parentField = fileField.closest('.form-group');
                                    if (parentField.length > 0) {
                                        var preview = parentField.find('.erf_img_preview');
                                        if (preview.length > 0) {
                                            item.html(preview[0].outerHTML);
                                            item.find('.erf_delete_attachment').remove();
                                        }
                                    }
                                }

                            })(singleField.name);

                            /*************** Closure ends here******************/
                        }
                    });
                }
            }
        }
        elems.text('');


        // Formula fields processing
        var formulaFields = form.find('.formula-field');
        if (formulaFields.length == 0)
            return;

        var refs = [];
        // Storing references to each field which are used in formula expressions
        formulaFields.each(function () {
            var expression = $(this).data('expression');
            if (expression) {
                const tokenRegex = /%([^[%]+)%/g;
                const matches = expression.match(tokenRegex);
                if (matches) {
                    refs = refs.concat(matches.map(m => m.trim().replace(new RegExp("%", "g"), '')));
                }
            }
        });
        refs = Array.from(new Set(refs)); // Eliminating duplicates
        refs.forEach(function (ref) { // Watching for any changes
            let field = form.find("[data-ref-label='" + ref + "']");
            if (field.is(':checkbox')) {
                field.on('change', (function () {
                    process_formula_fields(form, formulaFields);
                }));
            } else {
                field.on('input', (function () {
                    process_formula_fields(form, formulaFields);
                }));
            }

        });
        process_formula_fields(form, formulaFields); // Initializing at loading time
    }

    /*
     * Interpolate form and registers the events to handle two way binding between the file upload fields
     */
    function interpolate_file_fields(form, formData) {
        var nonInputFields = ['paragraph', 'button'];
        var elems = form.find('.erf_dynamic_ph_file');
        // Looping through all the form input fields to identify span elements with field placeholders
        if (elems.length > 0) {
            for (var i = 0; i < formData.fields.length; i++) {
                var singleField = formData.fields[i];
                if (singleField.hasOwnProperty('type') && nonInputFields.indexOf(singleField.type) == -1) {
                    singleField.label = singleField.label.toLowerCase();
                    var searchLabel = '__' + singleField.label + '__';
                    let searchRefLabel = '%' + singleField.dataRefLabel.toLowerCase() + '%';
                    elems.each(function () {
                        var item = $(this);
                        if (item.text().toLowerCase() == searchLabel || item.text().toLowerCase() == searchRefLabel) {
                            item.html('');
                            /*************** Closure ******************/
                            (function (fieldName) {
                                form.find('[name="' + fieldName + '"]').on('input', function () {
                                    if ($(this).is(':file')) { // Handling file type field
                                        var file = $(this);
                                        setTimeout(function () { // Implementing the preview action after few seconds to make sure preview div is available
                                            var filePreview = file.closest('.form-group').find('.erf_img_preview');
                                            if (filePreview.length > 0) {
                                                item.html(filePreview[0].outerHTML);
                                                item.find('.erf_delete_attachment').remove(); // Removing "Delete File" button
                                            } else {
                                                item.html('');
                                            }
                                        }, 1500);
                                    }
                                });

                                // Checking for file previews
                                var fileField = form.find('[name="' + fieldName + '"]:file');
                                if (fileField.length > 0) {
                                    var parentField = fileField.closest('.form-group');
                                    if (parentField.length > 0) {
                                        var preview = parentField.find('.erf_img_preview');
                                        if (preview.length > 0) {
                                            item.html(preview[0].outerHTML);
                                            item.find('.erf_delete_attachment').remove();
                                        }
                                    }
                                }
                            })(singleField.name);
                            /*************** Closure ends here******************/
                        }
                    });
                }
            }
        }
    }

    // Executes expression of all the formula fields available in the form.
    function process_formula_fields(form, formulaFields) {
        formulaFields.each(function (i, field) {
            var expression = $(this).data('expression');
            if (expression) {
                let output = frml_evaluate(expression,
                                function (token) {
                                    let temp = form.find("[data-ref-label='" + token + "']");
                                    let tokenValue = erforms_get_field_value_by_ref(form, token);
                                    if (temp.hasClass('erf-date-field')) { // Check if field is date type
                                        tokenValue = temp.datepicker('getDate');
                                    }
                                    if (temp.attr('enable-intl')) { // Check if field is date type
                                        let phoneFieldId = temp.attr('id');
                                        tokenValue = form.find("[name='" + phoneFieldId + "-intl']").val();
                                    }
                                    return tokenValue;
                                });
                if($(field).is('input')){
                    $(field).val(output);
                    $(field).trigger('input');
                } else{
                    $(field).html(output);
                }
            }
        });
    }

    function frml_evaluate(expression, templateResolverFn, reportErrors) {
        let {Parser, ParserRules, setRandomGenerator, ParserStart, setTemplateResolverFn} = erformsFrmlEngine;
        const p = new Parser(ParserRules, ParserStart);
        setTemplateResolverFn(templateResolverFn);
        setRandomGenerator(null);
        p.feed(expression);
        if (p.results.length > 1 && reportErrors) {
            let previousResult = p.results[0];
            p.results.forEach(function (result) {
                if (previousResult !== result) {
                    console.warn(JSON.stringify(p.results, null, 2));
                }
                previousResult = result;
            });
        } else if (expression && expression.trim() && p.results.length === 0) {
            throw new Error("Unexpected end of expression");
        }
        let result = p.results[0];
        if (typeof result == 'undefined' || Number.isNaN(result) || result === Infinity) {
            return null;
        }
        return result;
    }


    /*****************************Utils ends here ****************/

    /*****************************Login Form ****************************/
    $(document).bind('erf_process_login_form', function (ev) {
        $(".erf-show-register,.erf-show-login,.erf-show-lost-password").click(function () {
            var erfContainer = $(this).closest('.erf-container');
            var loginContainer = erfContainer.find('.erf-login-container');
            var registrationForm = erfContainer.find('.erf-reg-form-container');
            var lostPasswordContainer = erfContainer.find('.erf-password-lost-container');
            var otpContainer = erfContainer.find('.erf-otp-container');
            var updatePasswordContainer = erfContainer.find('.erf-password-update-container');

            if (!erfContainer.hasClass('erf-login-register')) {
                if (registrationForm.length > 0)
                    registrationForm.slideUp();
            }
            if (loginContainer.length > 0)
                loginContainer.slideUp();

            if (lostPasswordContainer.length > 0)
                lostPasswordContainer.slideUp();

            if (otpContainer.length > 0)
                otpContainer.slideUp();

            if (updatePasswordContainer.length > 0)
                updatePasswordContainer.slideUp();

            if ($(this).hasClass('erf-show-register')) {
                registrationForm.slideDown();
            }

            if ($(this).hasClass('erf-show-login')) {
                loginContainer.slideDown();
            }

            if ($(this).hasClass('erf-show-lost-password')) {
                ;
                lostPasswordContainer.slideDown();
            }
        });

        $('.erf-reset-password').click(function () {
            var lostPasswordContainer = $(this).closest('.erf-password-lost-container');
            var erfContainer = $(this).closest('.erf-container');
            //var loginContainer = erfContainer.find('.erf-login-container');
            var otpContainer = erfContainer.find('.erf-otp-container');
            lostPasswordContainer.find('.erf-error').html();
            submitButton = $(this);
            erforms_show_btn_loader(submitButton, true);
            $.ajax({
                url: erf_global.ajax_url,
                type: 'POST',
                data: {'user_login': lostPasswordContainer.find('#erf_user_login').val(), 'action': 'erf_reset_password'},
                success: function (response) {
                    try {
                        response = JSON.parse(response);
                        if (response.success) {
                            lostPasswordContainer.slideUp();
                            otpContainer.slideDown();
                            otpContainer.find('.erf-message').html(response.msg);
                            otpContainer.find('.erf-error').html('');
                            lostPasswordContainer.find('.erf-error').html('');
                        } else {
                            lostPasswordContainer.find('.erf-error').html(response.msg);
                        }
                        erforms_show_btn_loader(submitButton, false);
                    } catch (ex) {
                    }
                },
            });
        });

        $('.erf-otp').click(function () {
            var otpContainer = $(this).closest('.erf-otp-container');
            var erfContainer = $(this).closest('.erf-container');
            var updatePasswordContainer = erfContainer.find('.erf-password-update-container');
            otpContainer.find('.erf-error').html();
            submitButton = $(this);
            erforms_show_btn_loader(submitButton, true);
            $.ajax({
                url: erf_global.ajax_url,
                type: 'POST',
                data: {'user_login': erfContainer.find('#erf_user_login').val(), 'user_otp': otpContainer.find('#erf_user_otp').val(), 'action': 'erf_otp_check'},
                success: function (response) {
                    try {
                        response = JSON.parse(response);
                        if (response.success) {
                            otpContainer.slideUp();
                            updatePasswordContainer.slideDown();
                            updatePasswordContainer.find('.erf-error').html('');
                            otpContainer.find('.erf-error').html('');
                        } else {
                            otpContainer.find('.erf-error').html(response.msg);
                        }
                        erforms_show_btn_loader(submitButton, false);
                    } catch (ex) {
                    }
                },
            });
        });

        $('.erf-update-password').click(function () {
            var updatePasswordContainer = $(this).closest('.erf-password-update-container');
            var erfContainer = $(this).closest('.erf-container');
            var loginContainer = erfContainer.find('.erf-login-container');
            updatePasswordContainer.find('.erf-error').html();
            submitButton = $(this);
            erforms_show_btn_loader(submitButton, true);
            $.ajax({
                url: erf_global.ajax_url,
                type: 'POST',
                data: {'password': updatePasswordContainer.find('#erf_user_password').val(), 'cpassword': updatePasswordContainer.find('#erf_user_cpassword').val(), 'user_login': erfContainer.find('#erf_user_login').val(), 'user_otp': erfContainer.find('#erf_user_otp').val(), 'action': 'erf_update_password'},
                success: function (response) {
                    try {
                        response = JSON.parse(response);
                        if (response.success) {
                            updatePasswordContainer.slideUp();
                            loginContainer.slideDown();
                            loginContainer.find('.erf-message').html(response.msg);
                            loginContainer.find('.erf-error').html('');
                            updatePasswordContainer.find('.erf-error').html('');
                        } else {
                            updatePasswordContainer.find('.erf-error').html(response.msg);
                        }
                        erforms_show_btn_loader(submitButton, false);
                    } catch (ex) {
                    }
                },
            });
        });

        var sendLoginRequest = function (loginForm, loginContainer, data) {
            var submitButton = loginForm.find('.erf-submit-button button');
            $.ajax({
                url: erf_global.ajax_url,
                type: 'POST',
                data: data,
                async: true,
                success: function (response) {
                    try {
                        erforms_show_btn_loader(submitButton, false);
                        response = JSON.parse(response);
                        if (response.success) {
                            if (response.hasOwnProperty('reload')) {
                                loginContainer.find("#erf_login_reload_form").submit();
                            } else if (response.hasOwnProperty('redirect')) {
                                $(location).attr('href', response.redirect);
                            }
                        } else {
                            if (loginForm.find('.erf-recaptcha').length > 0 && erf_global.recaptcha_v == 2 && typeof grecaptcha !== 'undefined' && grecaptcha) {
                                grecaptcha.reset();
                            }
                            loginContainer.find('.erf-error').html(response.msg);
                        }

                    } catch (ex) {
                        erforms_show_btn_loader(submitButton, false);
                    }
                },
            });
        }

        $('.erf-login-form').submit(function (e) {
            var loginForm = $(this);
            var loginContainer = loginForm.closest('.erf-login-container');
            var rememberme = '';
            if (loginForm.find('#erf_rememberme').is(':checked')) {
                rememberme = 'forever';
            }

            loginContainer.find('.erf-error').html('');
            var data = {
                'erf_username': loginForm.find('#erf_username').val(),
                'erf_password': loginForm.find('#erf_password').val(),
                'rememberme': rememberme,
                'erf_login_nonce': loginForm.find('#erf_login_nonce').val(),
                'action': 'erf_login_user'
            };

            var submitButton = loginForm.find('.erf-submit-button button');
            erforms_show_btn_loader(submitButton, true);

            // If reCaptcha enabled
            var recap = loginForm.find('.erf-recaptcha');
            if (recap.length > 0) {
                if (erf_global.recaptcha_v == 2) { // reCaptcha version 2 API integrated
                    data['g-recaptcha-response'] = loginForm.find('[name="g-recaptcha-response"]').val();
                    sendLoginRequest(loginForm, loginContainer, data);
                } else if (erf_global.recaptcha_v == 3 && typeof grecaptcha !== 'undefined' && grecaptcha) { // reCaptcha version 3 API integrated
                    grecaptcha.ready(function () {
                        erf_recaptcha.execute('login', function (token) {
                            data['g-recaptcha-response'] = token;
                            sendLoginRequest(loginForm, loginContainer, data);
                        });
                    });
                }
            } else {
                sendLoginRequest(loginForm, loginContainer, data);
            }
            e.preventDefault();
        });

    });
    /*****************************Login Form ends here****************************/

    /*****************************My Account ****************************/
    $(document).bind('erf_process_my_account', function (ev) {
        var windowWidth = $(window).width();
        var myAccountDiv = $('.erf-my-account');
        if (myAccountDiv.length == 0)
            return;
        var autoResize = function () {
            if (windowWidth > 1024) {
                if (myAccountDiv.innerWidth() < 700) {
                    myAccountDiv.closest('.erf-container').addClass('erf-small-inner');
                }
            }
        };
        autoResize();
        $(window).resize(function () {
            autoResize();
        });
        $('.erf-load-submission-row').click(function () {
            var submissionID = $(this).data('submission-id');
            var formID = $(this).data('form-id');
            var submissionContainer = $(this).siblings('.erf-modal');
            var contentContainer = submissionContainer.find('.erf-modal-body');
            submissionContainer.toggle();
            contentContainer.html(erf_global.js_strings.loading_submission_info)
            // Fetch updated submission info.
            $.post(erf_global.ajax_url, {form_id: formID, submission_id: submissionID, action: 'erforms_get_submission_html'}, function (res) {
                if (res.success) {
                    if (res.data.hasOwnProperty('html')) {
                        contentContainer.html(res.data.html);
                    }
                }
            }).fail(function (xhr, textStatus, e) {
                contentContainer.html(erf_global.js_strings.edit_form_load_error);
            });
        });

        var registerCloseModal = function () {
            $('.erf-modal-close').click(function () {
                $(this).closest('.erf-modal').hide();
            });
        }

        registerCloseModal();
        /*
         * Handles "Edit" action.
         * Loads Edit form in a modal window.
         */
        $('.erf-edit-submission-row').click(function (e) {
            e.preventDefault();
            var modalContainer = $(this).parent('.erf-my-account-col-edit').find('.erf-modal');
            var submissionContainer = modalContainer.find('.erf-modal-body');
            var submissionID = $(this).data('submission-id');
            var formID = $(this).data('form-id');
            modalContainer.show();
            submissionContainer.html(erf_global.js_strings.loading_edit_form);
            //Fetching Edit form
            $.post(erf_global.ajax_url, {form_id: formID, submission_id: submissionID, action: 'erforms_get_form_for_edit'}, function (res) {
                if (res.success) {
                    if (res.data.hasOwnProperty('form_html')) {
                        submissionContainer.html(res.data.form_html);
                        erforms_set_form_into_gs(formID, res.data.localize);
                        $(document).trigger('erf_process_form', [modalContainer]);
                    }
                    //$(document).trigger('erf_submission_edit_form', [formID,submissionID,res]);
                } else {
                    if (res.data.hasOwnProperty('error')) {
                        submissionContainer.html(res.data.error);
                    }
                    //$(document).trigger('erf_submission_edit_error', [formID,submissionID,res]);
                }
                registerCloseModal();
            }).fail(function (xhr, textStatus, e) {
                submissionContainer.html(erf_global.js_strings.edit_form_load_error);
            });
        });

        // Delete submission
        $('.erf-delete-submission-row').click(function () {
            var deletionConfirmed = confirm(erf_global.my_account.sub_del_confirm_msg);
            if (!deletionConfirmed)
                return;
            var submissionID = $(this).data('submission-id');
            var formID = $(this).data('form-id');
            var row = $(this).closest('.erf-my-account-details');
            $.post(erf_global.ajax_url, {form_id: formID, submission_id: submissionID, action: 'erforms_delete_submission'}, function (res) {
                if (res.success) {
                    row.remove();
                    $(document).trigger('erf_submission_deleted', [formID, submissionID, res]);
                } else {
                    if (res.data.hasOwnProperty('msg')) {
                        alert(res.data.msg);
                        $(document).trigger('erf_submission_deletion_error', [formID, submissionID, res]);
                    }
                }
            }).fail(function (xhr, textStatus, e) {
                alert('Unable to connect to server');
            });
        });

        $('.erf-my-account-nav a').click(function () {
            $('.erf-my-account-nav a').parent().removeClass('erf-my-account-navigation-link-active');
            $(this).parent().addClass('erf-my-account-navigation-link-active');
            var tagid = $(this).data('tag');
            $('.erf-my-account-profile-tab').removeClass('active').addClass('erf-hidden');
            $('#' + tagid).addClass('active').removeClass('erf-hidden');
        });

        window.Parsley.addValidator('confirmPassword', {
            validateString: function (value, passwordFieldId) {
                return $("#" + passwordFieldId).val() == value;
            },
            messages: {
                en: erf_global.parsley.confirmPassword,
            }
        });


        $(".erf-change-password").submit(function (event) {
            var form = $(this);
            var formContainer = $(form).closest('.erf-my-account-profile-tab');
            var formInstance = form.parsley();
            var formData = new FormData(form[0]);
            var errorContainer = formContainer.find('.erf-errors');
            errorContainer.html('');
            form.find('button[type="submit"]').append('<span class="erf-loader"></span>');

            $.ajax({
                url: erf_global.ajax_url,
                type: 'POST',
                data: formData,
                async: true,
                success: function (response) {
                    if (response.success) {
                        var message = response.data.msg;
                        form.find('button[type="submit"] .erf-loader').remove();
                        formContainer.html(message);

                    } else
                    {
                        form.find('button[type="submit"] .erf-loader').remove();
                        if (response.data.hasOwnProperty('errors')) {
                            for (var i = 0; i < response.data.errors.length; i++) {
                                errorContainer.append('<div class="erf-error-row">' + response.data.errors[i] + '</div><br>');
                            }
                        }

                    }
                },
                cache: false,
                contentType: false,
                processData: false
            });
            event.preventDefault();
        });
        $('.erf-password').password(erf_global.my_account.pass_config);
        $(".erf-print-submission").click(function () {
            $(this).closest('.erf-submission-info').find('.erf-modal-body').printThis({
                importCSS: true,
                loadCSS: "",
            });
        });
    });
    $(window).bind("load", function () {
        var currentURL = $(location).attr('href');
        if (currentURL.indexOf('erf_paged') != -1) {
            $('a[data-tag="submissions"]').trigger("click");
        }
    });
    /*****************************My Account ends here ****************************/

    /****************************Common*******************************************/
    $(document).bind('erf_register_common_scripts', function (ev) {
        $(".erf_resend_ver_link").click(function () {
            var tar = $(this);
            var container = tar.closest('.erf-container');
            $.ajax({
                url: erf_global.ajax_url,
                type: 'POST',
                data: {nonce: $(this).data('nonce'), sub: $(this).data('sub'), 'action': 'erf_resend_verification'},
                success: function (response) {
                    try {
                        if (response.success) {
                            erf_popup_message({content: response.data.msg}, container);
                        } else {
                            erf_popup_message({content: response.data.msg}, container);
                        }
                        ;
                    } catch (ex) {
                    }
                    tar.removeClass('erf-text-spinner');
                },
                beforeSend: function () {
                    tar.addClass('erf-text-spinner');
                }
            });
        });
    });
})(jQuery);

/*Form Ends here*/

jQuery(document).ready(function () {
    erf_global_triggers();
});