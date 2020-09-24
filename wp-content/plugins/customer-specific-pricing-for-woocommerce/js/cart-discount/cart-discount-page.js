var productList=Array();
var catList = Array();
var usersList = Array();
var userRolesList = Array();
var userGroupsList = Array();
var modalInitialData = Array();

jQuery( document ).ready(function() {
    
    if (jQuery('#csp-cd-feature-switch').prop('checked')) {
        jQuery('.row.csp-cd-main-div').css('display','block');
    }

    //checking minimum values on focusout
    jQuery('div.sub-rule-section').delegate('input.cd-min-cart-val', 'focusout', function(){
        let $currentRow = jQuery(this).closest('div.cd-range-row');
        let currentMin = jQuery(this).val();
        if (currentMin!='') {
            if (currentMin < 0) {
                jQuery(this).val(0);
                currentMin = 0;
            }

            if ($currentRow.prev('div.cd-range-row').length > 0) {
                let prevMax = $currentRow.prev('div.cd-range-row').find('input.cd-max-cart-val').val();
                if (parseFloat(currentMin) <= parseFloat(prevMax)) {
                    jQuery(this).addClass('csp-cd-highlight');
                    let $msgSpan = $currentRow.find('div.rule-range-messages > span');
                    $msgSpan.text(wdm_csp_cd_object.error_messages['current_min_less_than_prev_max']);
                    $msgSpan.show('fast');
                }
            }
        } else {
            let currentMax = $currentRow.find('input.cd-max-cart-val').val();
            if (currentMax!='') {
                jQuery(this).addClass('csp-cd-highlight');
                let $msgSpan = $currentRow.find('div.rule-range-messages > span');
                $msgSpan.text(wdm_csp_cd_object.error_messages['empty_min_value']);
                $msgSpan.show('fast');
            }
        }
    });

    // validate max value on focusout
    jQuery('div.sub-rule-section').delegate('input.cd-max-cart-val', 'focusout', function(){
        let $currentRow = jQuery(this).closest('div.cd-range-row');
        let currentMax = jQuery(this).val();
        if (currentMax!='') {
            if (currentMax < 0) {
                jQuery(this).val('');
                currentMax = 0;
            }

            let currentMin = $currentRow.find('input.cd-min-cart-val').val();
            if (currentMin == '') {
                $currentRow.find('input.cd-min-cart-val').addClass('csp-cd-highlight');
                let $msgSpan = $currentRow.find('div.rule-range-messages > span');
                $msgSpan.text(wdm_csp_cd_object.error_messages['empty_min_value']);
                $msgSpan.show('fast');
                return false;
            }

            if(parseFloat(currentMin) >= parseFloat(currentMax)) {
                jQuery(this).addClass('csp-cd-highlight');
                let $msgSpan = $currentRow.find('div.rule-range-messages > span');
                $msgSpan.text(wdm_csp_cd_object.error_messages['min_is_greater_than_max']);
                $msgSpan.show('fast');
                return false;
            }

            if ($currentRow.next('div.cd-range-row').length > 0) {
                let $nextRow = $currentRow.next('div.cd-range-row');
                let nextMin = $nextRow.find('input.cd-min-cart-val').val();
                if (nextMin!='') {
                    if (parseFloat(nextMin)<=parseFloat(currentMax)) {
                        $nextRow.find('input.cd-min-cart-val').addClass('csp-cd-highlight');
                        let $msgSpan = $nextRow.find('div.rule-range-messages > span');
                        $msgSpan.text(wdm_csp_cd_object.error_messages['current_min_less_than_prev_max']);
                        $msgSpan.show('fast');
                    return false;
                    }
                }
            }
        } else {
            if ($currentRow.next('div.cd-range-row').length > 0) {
                jQuery(this).addClass('csp-cd-highlight');
                let $msgSpan = $currentRow.find('div.rule-range-messages > span');
                $msgSpan.text(wdm_csp_cd_object.error_messages['empty_max_value']);
                $msgSpan.show('fast');
            }
        }
    });

    jQuery('div.sub-rule-section').delegate('input.csp-cd-highlight', 'focus', function(){
        let $row = jQuery(this).closest('div.cd-range-row');
        $row.find('input').removeClass('csp-cd-highlight');
        $row.find('div.rule-range-messages > span').hide(200);
    });

    jQuery('div.sub-rule-section').delegate('div.csp-cd-highlight-row', 'click', function(){
        jQuery(this).find('div.rule-range-messages > span').hide(200);
        jQuery(this).removeClass('csp-cd-highlight-row');
    });

    //Accordion
    jQuery('.panel-collapse').on('show.bs.collapse', function () {
        jQuery(this).siblings('.panel-heading').addClass('active');
      });
    
    jQuery('.panel-collapse').on('hide.bs.collapse', function () {
        jQuery(this).siblings('.panel-heading').removeClass('active');
      });

    //Adding & Removing a new rule to & from the rule sets
    jQuery('div.sub-rule-section').delegate('span.icons.cd-add-new','click',function(e) {
        let $currentRow = jQuery(this).closest('div.cd-range-row');
        let currentRuleValidity = isRuleRowDataValid($currentRow);
        let allRulesValidity = validateAllRangesIn($currentRow.closest('div.sub-rule-section'));
        if('row_data_valid'==allRulesValidity.status && 'row_data_valid'==currentRuleValidity) {
            let min = $currentRow.find('input.cd-min-cart-val').val();
            let max = $currentRow.find('input.cd-max-cart-val').val();
            if(min.length!=0 && max.length!=0){
                $currentRow.after(getNewRangeRow());
                $currentRow.find('input.cd-max-cart-val').attr('title','');
                let $newlyAddedRow = $currentRow.next('.row.cd-range-row')
                $newlyAddedRow.find('input.cd-max-cart-val').attr('title',wdm_csp_cd_object.no_max_limit_info);
                $newlyAddedRow.find('.cd-min-cart-val').focus();
                $currentRow.find('div.add-remove-icons').html('');
            }
        } else
            {
                let status  = currentRuleValidity=='row_data_valid'?allRulesValidity.status:currentRuleValidity;
                let row     = currentRuleValidity=='row_data_valid'?allRulesValidity.row:$currentRow;
                showRangeRowMessage(row,status);
            }
    });

    jQuery('div.sub-rule-section').delegate('span.icons.cd-add-new','keypress',function(event) {
        let keycode = (event.keyCode ? event.keyCode : event.which);
                if(keycode == '13'){
                    jQuery(this).click(); 
                }
    });

    jQuery('div.sub-rule-section').delegate('span.icons.cd-remove','click',function(e) {
        let $currentRow = jQuery(this).closest('div.cd-range-row');
        let $parent     = $currentRow.closest('div.sub-rule-section');
        let $Rules      = $parent.find('div.cd-range-row');
        let ruleRowCount= $Rules.length;

        if(ruleRowCount>1) {
            $currentRow.remove();
            let addRemoveHtm    = "<span class='icons cd-remove' title='Remove this discount range'> </span><span class='icons cd-add-new' tabindex='0' title='Add new discount range'> </span>"
            jQuery($Rules[ruleRowCount-2]).find('div.add-remove-icons').html(addRemoveHtm);
            jQuery($Rules[ruleRowCount-2]).find('.cd-max-cart-val').attr('title', wdm_csp_cd_object.no_max_limit_info);            
        } else {
            $currentRow.find('input').val('');
            showRangeRowMessage($currentRow, "only_remaining_rule");
        }
    });

    jQuery('div.sub-rule-section').delegate('input.cd-discount-input','focusout',function(e) {
        let $currentRow = jQuery(this).closest('div.cd-range-row');

        let discountVal    = parseFloat(jQuery(this).val());
        if (!isNaN(discountVal)) {
            if (discountVal<=0 || discountVal>100) {
                let $msgSpan = $currentRow.find('div.rule-range-messages > span');
                $msgSpan.text(wdm_csp_cd_object.error_messages['wrong_discount_value']);
                $msgSpan.show();
                jQuery(this).val('');
                jQuery(this).addClass('csp-cd-highlight');
            }
        } else {
            if ($currentRow.find('input.cd-min-cart-val').val()!='') {
                let $msgSpan = $currentRow.find('div.rule-range-messages > span');
                $msgSpan.text(wdm_csp_cd_object.error_messages['empty_discount_value']);
                $msgSpan.show();
                jQuery(this).addClass('csp-cd-highlight');   
            }
        }
    });

    jQuery('div.cd-notes-title').click(function(){
        $content = jQuery(this).next('.cd-notes-content');
        $content.addClass('active');
        if($content.css('max-height')=='0px')
        {
            $content.css('max-height','min-content');
        } else {
            $content.css('max-height','0px');
        }
    });

    //Saving all the rules
    jQuery('button.save-all-cd-rules').click(function(){
        // Validate cart discount settings for first time users 
        let ftbSubRules,ftbMaxDiscount,ftbButtonText,ftbButtonLink, ftbBeforeOfferText, ftbAfterOfferText, ftbExclusions;
        let exbSubRules, exbMaxDiscount, exbButtonText,exbButtonLink, exbBeforeOfferText, exbAfterOfferText, exbExclusions;
        let guSubRules, guMaxDiscount,guButtonText,guButtonLink, guBeforeOfferText, guAfterOfferText , guExclusions;

        let RuleSection     = jQuery('div.src-first-time-buyers');
        let validationStatus; 
        validationStatus    = validateAllRangesIn(RuleSection);
            if (!(['row_data_valid','all_fields_empty'].includes(validationStatus.status))) {
                showRangeRowMessage(validationStatus.row, validationStatus.status);
                return false;
            }
            ftbSubRules     = getAllSubrules(RuleSection);
            ftbMaxDiscount  = parseFloat(jQuery('#max-discount-first-time-buyers').val());
            if (!isNaN(ftbMaxDiscount)) {
                if (ftbMaxDiscount<0) {
                    alert(wdm_csp_cd_object.error_messages.invalid_max_discount);
                    jQuery('#max-discount-first-time-buyers').focus();
                    return false;
                }
            }
            ftbButtonText       = jQuery('#shop-more-btn-first-time-buyers').val();
            ftbButtonLink       = jQuery('#shop-more-btn-link-first-time-buyers').val();
            if (!cspIsValid(ftbButtonLink)) {
                alert("Please recheck if the shop page button links are correct.");
                return false;
            }
            ftbBeforeOfferText  = jQuery('#before-offer-text-first-time-buyers').val();
            ftbAfterOfferText   = jQuery('#after-offer-text-first-time-buyers').val();
            ftbExclusions       = getAllExclusionsFor('firstTimeBuyers');

        // Validate cart discount settings for existing buyers
        RuleSection         = jQuery('div.src-existing-buyers');
        validationStatus    = validateAllRangesIn(RuleSection);
            if (!(['row_data_valid','all_fields_empty'].includes(validationStatus.status))) {
                showRangeRowMessage(validationStatus.row, validationStatus.status);
                return false;
            }
            exbSubRules     = getAllSubrules(RuleSection);
            exbMaxDiscount  = parseFloat(jQuery('#max-discount-existing-buyers').val());
        if (!isNaN(exbMaxDiscount)) {
            if (exbMaxDiscount<0) {
                alert(wdm_csp_cd_object.error_messages.invalid_max_discount);
                jQuery('#max-discount-existing-buyers').focus();
                return false;
            }
        }
            exbButtonText       = jQuery('#shop-more-btn-existing-buyers').val();
            exbButtonLink       = jQuery('#shop-more-btn-link-existing-buyers').val();
            if (!cspIsValid(exbButtonLink)) {
                alert("Please recheck if the shop page button links are correct.");
                return false;
            }
            exbBeforeOfferText  = jQuery('#before-offer-text-existing-buyers').val();
            exbAfterOfferText   = jQuery('#after-offer-text-existing-buyers').val();
            exbExclusions       = getAllExclusionsFor('existingBuyers');

        // Validate cart discount settings for guest users if guest checkout is enabled.
        if (wdm_csp_cd_object.guest_checkout_enabled=="yes") {
            RuleSection         = jQuery('div.src-guest-users');
            validationStatus    = validateAllRangesIn(RuleSection);
                if (!(['row_data_valid','all_fields_empty'].includes(validationStatus.status))) {
                    showRangeRowMessage(validationStatus.row, validationStatus.status);
                    return false;
                }
            guSubRules      = getAllSubrules(RuleSection);   
            guMaxDiscount  = parseFloat(jQuery('#max-discount-guest-users').val());
            if (!isNaN(guMaxDiscount)) {
                if (guMaxDiscount<0) {
                    alert(wdm_csp_cd_object.error_messages.invalid_max_discount);
                    jQuery('#max-discount-existing-buyers').focus();
                    return false;
                }
            }
            guButtonText        = jQuery('#shop-more-btn-guest-users').val();
            guButtonLink        = jQuery('#shop-more-btn-link-guest-users').val();
            if (!cspIsValid(guButtonLink)) {
                alert("Please recheck if the shop page button links are correct.");
                return false;
            }
            guBeforeOfferText   = jQuery('#before-offer-text-guest-users').val();
            guAfterOfferText    = jQuery('#after-offer-text-guest-users').val();
            guExclusions        = getAllExclusionsFor('guestUsers');;
        }

        let ftbData = {'subRules': ftbSubRules, 'maxDiscount':ftbMaxDiscount, 'buttonText':ftbButtonText, 'buttonLink':ftbButtonLink, 'beforeOfferText':ftbBeforeOfferText, 'afterOfferText':ftbAfterOfferText, 'exclusions': ftbExclusions}; 
        let exbData = {'subRules': exbSubRules, 'maxDiscount':exbMaxDiscount, 'buttonText':exbButtonText, 'buttonLink':exbButtonLink, 'beforeOfferText':exbBeforeOfferText, 'afterOfferText':exbAfterOfferText, 'exclusions': exbExclusions};
        let guData  = {'subRules': guSubRules,  'maxDiscount':guMaxDiscount,  'buttonText':guButtonText,  'buttonLink':guButtonLink,  'beforeOfferText':guBeforeOfferText,  'afterOfferText':guAfterOfferText,  'exclusions': guExclusions};

        sendRuleDataToSave(ftbData, exbData, guData);
    });



    jQuery('input.url-input').focusout(function(){
        let $this = jQuery(this);
        let url = $this.val();
        if (!cspIsValid(url)) {
            $this.addClass('csp-cd-highlight');
        }
    });
    jQuery('input.url-input').focus(function(){
        let $this = jQuery(this);
            $this.removeClass('csp-cd-highlight');
    });

        /**
     * When Feature gets enabled or disabled using a switch.
     * ajax call to save the feature setting and enable/ disable the display of the form
     */
    jQuery('#csp-cd-feature-switch').change(function() {
        let featureStatus = jQuery('#csp-cd-feature-switch').prop('checked')?"enable":"disable";
        let data          = {'action': 'set_feature_enabled',
                                'featureStatus':featureStatus, 
                                'cd_nonce': wdm_csp_cd_object.nonce,
                            };
        jQuery('.loading-text').css('display','block');
        jQuery.ajax(
            {
                type: 'POST',
                url: wdm_csp_cd_object.ajax_url,
                data: data,
                success: function(msg){
                    if (jQuery('#csp-cd-feature-switch').prop('checked')) {
                        jQuery('.row.csp-cd-main-div').css('display','block');
                        jQuery('.row.csp-cd-main-div').focus();
                    } else {
                        jQuery('.row.csp-cd-main-div').css('display','none');
                    }
                    jQuery('.loading-text').css('display','none');
                }
            });
    });
      


    //parce the product & category list received from the script localization
    productList     = JSON.parse(wdm_csp_cd_object.product_list);
    catList         = JSON.parse(wdm_csp_cd_object.product_cat_list);
    usersList       = JSON.parse(wdm_csp_cd_object.site_users_list);
    userRolesList   = JSON.parse(wdm_csp_cd_object.user_roles_list);
    userGroupsList  = JSON.parse(wdm_csp_cd_object.group_id_name_list);
    //Search Modals
        //Searching in products list for the products and listing the search results to let the user select products
        jQuery('.txt-search-product').keyup(function(){
            var searchField = jQuery(this).val();
            if(searchField === '')  {
                jQuery(this).closest('.modal-body').find('.filter-records').html('');
            return;
            }
            let searchResult=cdSearchIn(searchField, productList);
            
            jQuery(this).closest('.modal-body').find('.filter-records').html(searchResult);
        });
    
        /*Searching in the product category list & listing the categories as per the search results
         to be included or excluded in the rule 
         */
        jQuery('.txt-search-category').keyup(function(){
            var searchField = jQuery(this).val();
            if(searchField === '')  {
                jQuery(this).closest('.modal-body').find('.filter-records').html('');
            return;
            }
            let searchResult=cdSearchIn(searchField, catList);
            
            jQuery(this).closest('.modal-body').find('.filter-records').html(searchResult);
        });
    
        jQuery('.txt-search-users').keyup(function(){
            var searchField = jQuery(this).val();
            if(searchField === '')  {
                jQuery(this).closest('.modal-body').find('.filter-records').html('');
            return;
            }
            let searchResult=cdSearchIn(searchField, usersList);
            
            jQuery(this).closest('.modal-body').find('.filter-records').html(searchResult);
        });
        
        jQuery('.txt-search-user-roles').keyup(function(){
            var searchField = jQuery(this).val();
            if(searchField === '')  {
                jQuery(this).closest('.modal-body').find('.filter-records').html('');
            return;
            }
            let searchResult=cdSearchIn(searchField, userRolesList);
            
            jQuery(this).closest('.modal-body').find('.filter-records').html(searchResult);
        });
    
        jQuery('.txt-search-user-groups').keyup(function(){
            var searchField = jQuery(this).val();
            if(searchField === '')  {
                jQuery(this).closest('.modal-body').find('.filter-records').html('');
            return;
            }
            let searchResult=cdSearchIn(searchField, userGroupsList);
            
            jQuery(this).closest('.modal-body').find('.filter-records').html(searchResult);
        });

        //add the clicked element to the selected products/categories/users/roles/groups list
        jQuery('.filter-records').delegate('button','click',function(){
            let select= jQuery(this).closest('.modal-body').find('div.selected');
            var selectIds=Array();
            var selectedIds= jQuery(select).find('button');
            selectedIds.each(function(){
                selectIds.push(jQuery(this).attr('pid'));
            });
    
            let id= jQuery(this).attr('pid');
            if ( empty(selectIds) || (-1)==jQuery.inArray( id, selectIds )) {
                let cnt;
                let $modal = jQuery(this).closest('.modal');
                let $buttonBadge = jQuery("button[data-target=#"+$modal.attr('id')+"]").find('span.badge');
    
                jQuery(select).append(this);
                cnt=jQuery(select).find('button').length;
                cnt=parseInt(cnt);
                $modal.find('span.exclude_count').text(cnt);
                $buttonBadge.text(cnt);
            }
        });

    //remove selected element on clicking the element from selected products/categories/users/roles/groups list
    jQuery( "div.selected" ).delegate( "button", "click", function() {
        let cnt=0;
        let $modal = jQuery(this).closest('.modal');
        let $buttonBadge = jQuery("button[data-target=#"+$modal.attr('id')+"]").find('span.badge');
        let select= $modal.find('div.selected');
        jQuery(this).remove();
        cnt=jQuery(select).find('button').length;
        cnt=parseInt(cnt);
        $modal.find('span.exclude_count').text(cnt);
        $buttonBadge.text(cnt);
    });

    /**
     * Rules List Search box functionality
     * to search for the rules by their titles
     */
    jQuery("#csp-cd-search").on("keyup", function() {
        var value = jQuery(this).val().toLowerCase();
            jQuery(".wdm-csp-cd-rule-list .cd-rule-box").filter(function() {
                jQuery(this).toggle(jQuery(this).text().toLowerCase().indexOf(value) > -1);
            });
        });


        // To save the feature settings such as threshold to display offer
        jQuery('#saveFeatureSettings').on('click', function() {
            var $this = jQuery(this);
            let thresholdPercent= parseFloat(jQuery('#percentThreshold').val());
            let minMaxDiscountVal = jQuery('#minMaxDiscount').find("option:selected").val();
            if (thresholdPercent<0 && thresholdPercent>100) {
                alert("Enter a % value between 0 to 100");
                return false;
            }
            let data          =  {'action': 'csp_cd_set_settings',
                                  'thresholdPercent': thresholdPercent,
                                  'minMaxDiscount':minMaxDiscountVal,
                                  'cd_nonce': wdm_csp_cd_object.nonce,
                                 };
            $this.attr('disabled',true);
            $this.text('Saving');
            jQuery.ajax(
                {
                    type: 'POST',
                    url: wdm_csp_cd_object.ajax_url,
                    data: data,
                    success: function(msg){
                        $this.text('Save Settings');
                        $this.removeAttr('disabled');
                            jQuery('button.cd_save_status').show('fast');
                            setTimeout(function(){jQuery('button.cd_save_status').hide(200);},2000);
                    }
                });
        });

        jQuery('.csp-modal-cancel').click(function(){
            let $thisModal = jQuery(this).closest('div.csp-cd-modal');
            $thisModal.find('div.selected').html(modalInitialData.selectedElements);
            $thisModal.find('span.exclude_count').text(modalInitialData.selectionCount);
            buttonId = $thisModal.attr('id');
            jQuery('button[data-target="#'+buttonId+'"]').find('span').text(modalInitialData.selectionCount);
        });

        jQuery('div.csp-cd-modal').on('shown.bs.modal', function (e) {
            let $thisModal = jQuery(this);
            let selections = $thisModal.find('div.selected').html();
            modalInitialData = {selectedElements:selections,selectionCount:jQuery(e.relatedTarget).find('span').text()};
        })
}); //End Document ready


    /**
     * This function is used to search the given string in the given array,
     * to create & return the html for matching results.
     * @param {string} searchField 
     * @param {array} list 
     */
    function cdSearchIn(searchField, list)
    {
        var regex = new RegExp(searchField, "i");
        var output = '';
        var count = 1;
        jQuery.each(list, function(key, val){
            if ((val.name.search(regex) != -1)) {
            output += '<button class="btn btn-default btn-sm btn-product-label" pid="'+val.ID+'">'+val.name+'</button>';
        }
      });
      return output;
    }



    /**
     * Returns an array of ids of the products or the categories selected 
     * in the modal id passed as a parameter
     * @param {string} modalId 
     */
    function getSelectedItemsInModal(modalId)
    {
        let select= jQuery(modalId).find('div.selected');
        var selectIds=Array();
        var selectedIds= jQuery(select).find('button');
        selectedIds.each(function(){
            selectIds.push(jQuery(this).attr('pid'));
        });
        return selectIds;
    }


    
function getNewRangeRow()
{
    let row  = "<div class='row cd-range-row'>";
        row += "<div class='col-md-2 text-center'>";
        row += "<input type='number' class='form-control cd-min-cart-val' min='0'  value='' placeholder='Min cart value'>";
        row += "</div>";
        row += "<div class='col-md-1 text-center'>";
        row += "-";
        row += "</div>";
        row += "<div class='col-md-2 text-center'>";
        row += "<input type='number' name='' id='' class='form-control cd-max-cart-val' min='0' value='' placeholder='Max cart value'>";
        row += "</div>";
        row += "<div class='col-md-2 text-center'>";
        row += "<input type='number' name='' id='' max='100' min='0.01' step='0.01' value='' class='form-control cd-discount-input' placeholder='% discount'>";
        row += "</div>";
        row += "<div class='col-md-2 text-left add-remove-icons'>";
        row += "<span class='icons cd-remove'  title='Remove this discount range'> </span>";
        row += "<span class='icons cd-add-new' tabindex='0' title='Add new discount range'> </span>";
        row += "</div>";
        row += "<div class='col-md-3 text-center rule-range-messages'> <span></span>";
        row += "</div>";
        row += "</div>";

    return row;
}

/**
 * Gets a jQuery object containing the cart value range data
 * & returns 
 * * row_data_valid - for valid range
 * * all_fields_empty - when all the fields in the rule are empty
 * * discount_not_specified - when discount field is empty
 * * max_not_specified - when maximum cart total is not specified in the rule
 * * min_is_greater_than_max - when minimum cart value specified is greater than maximum cart value specified
 * 
 * @param object $currentRow 
 */
function isRuleRowDataValid($currentRow)
{
    let min = $currentRow.find('input.cd-min-cart-val').val();
    let max = $currentRow.find('input.cd-max-cart-val').val();
    let discount = $currentRow.find('input.cd-discount-input').val();

    if (min.length==0 && max.length==0 && discount.length==0) {
         return 'all_fields_empty';
    }

    if(discount.length==0){
        return "discount_not_specified";
        }

    if (parseFloat(min)>0) {
        if (!isNaN(parseFloat(max))) {
            if(parseFloat(min)>=parseFloat(max))
            {
                return 'min_is_greater_than_max';
            }
        } else {
            return 'max_not_specified';
        }
    }
    return 'row_data_valid';
}

/**
 * This methods shows an error in front of the row which contains an error.
 * 
 * @param {object} $row 
 * @param {string} message 
 */
function showRangeRowMessage($row, message)
{
    $row.closest('div.panel.panel-default').find('a[aria-expanded=false]').click();
    $row.addClass('csp-cd-highlight-row');
    message = wdm_csp_cd_object.error_messages[message];
    let messageSpan = $row.find('div.rule-range-messages > span');
    messageSpan.text(message);
    messageSpan.show('fast');
}


/**
 * This method validates all the cart discount ranges in the provided range sections
 * returns,
 * * Errors in a row if it finds an invalid row.
 * * current_min_less_than_prev_max - when row contains invalid range related to its previous row
 * @param {object} $rangeSection 
 */
function validateAllRangesIn($rangeSection)
{
    $Rules = $rangeSection.find('div.cd-range-row');
    let ruleValidity, prevMax=0;
    let min=0, max=0;
    let length = $Rules.length;
    let $rule;

    //if only one rule exist in a section
    if (length<2) {
        $rule = jQuery($Rules[0]);
        $rowValidityStatus = isRuleRowDataValid($rule);
        $rowValidityStatus = $rowValidityStatus=='max_not_specified'?'row_data_valid':$rowValidityStatus;
        return {'status':$rowValidityStatus, 'row':$rule};
    }

    for (let i = 0; i < length-1; i++) {
        $rule = jQuery($Rules[i]);
        ruleValidity = isRuleRowDataValid($rule);
        if(ruleValidity!='row_data_valid'){
            return {'status':ruleValidity, 'row':$rule};
        }

        min         = parseFloat($rule.find('input.cd-min-cart-val').val());
        max         = parseFloat($rule.find('input.cd-max-cart-val').val());
        discount    = parseFloat($rule.find('input.cd-discount-input').val()); 

        if(i!=0) {
            if (min<=prevMax) {
               return  {'status':'current_min_less_than_prev_max', 'row':$rule, 'prevMax':prevMax};
            }
        }
        prevMax = max;
    }
    $rule = jQuery($Rules[length-1]);
    ruleValidity = isRuleRowDataValid($rule);
    if(!(['row_data_valid', 'all_fields_empty', 'max_not_specified'].includes(ruleValidity))) {
        return {'status':ruleValidity, 'row':$rule};
    }
    
    if (ruleValidity!='all_fields_empty') {
        min         = parseFloat($rule.find('input.cd-min-cart-val').val());
        if (min<=prevMax) {
            return {'status':'current_min_less_than_prev_max', 'row':$rule, 'prevMax':prevMax};
        }
    }
    return {'status':'row_data_valid', 'row':$rule};
}


function getAllExclusionsFor(userType)
{
    let productIds=[],categoryIds=[],userIds=[],userRoleSlugs=[],userGroupIds=[];
    let length = 0;
    switch (userType) {
        case 'firstTimeBuyers':
                selected        = jQuery('div#modal-prod-ex-first-time-buyers').find('.csp-cd-selected-products > button');
                productIds      = returnSelectedItemIdentifiers(selected);
                
                selected        = jQuery('div#modal-cat-ex-first-time-buyers').find('.csp-cd-selected-product_cat > button');
                categoryIds     = returnSelectedItemIdentifiers(selected);

                selected        = jQuery('div#modal-users-ex-first-time-buyers').find('.csp-cd-selected-users > button');
                userIds         = returnSelectedItemIdentifiers(selected);

                selected        = jQuery('div#modal-user-roles-ex-first-time-buyers').find('.csp-cd-selected-user-roles > button');
                userRoleSlugs   = returnSelectedItemIdentifiers(selected);

                selected        = jQuery('div#modal-user-groups-ex-first-time-buyers').find('.csp-cd-selected-user-groups > button');
                userGroupIds   = returnSelectedItemIdentifiers(selected);
        break;
        case 'existingBuyers':
                selected        = jQuery('div#modal-prod-ex-existing-buyers').find('.csp-cd-selected-products > button');
                productIds      = returnSelectedItemIdentifiers(selected);
                
                selected        = jQuery('div#modal-cat-ex-existing-buyers').find('.csp-cd-selected-product_cat > button');
                categoryIds     = returnSelectedItemIdentifiers(selected);

                selected        = jQuery('div#modal-users-ex-existing-buyers').find('.csp-cd-selected-users > button');
                userIds         = returnSelectedItemIdentifiers(selected);

                selected        = jQuery('div#modal-user-roles-ex-existing-buyers').find('.csp-cd-selected-user-roles > button');
                userRoleSlugs   = returnSelectedItemIdentifiers(selected);

                selected        = jQuery('div#modal-user-groups-ex-existing-buyers').find('.csp-cd-selected-user-groups > button');
                userGroupIds   = returnSelectedItemIdentifiers(selected);
        break;
        case 'guestUsers':
                selected        = jQuery('div#modal-prod-ex-guest-users').find('.csp-cd-selected-products > button');
                productIds      = returnSelectedItemIdentifiers(selected);
                
                selected        = jQuery('div#modal-cat-ex-guest-users').find('.csp-cd-selected-product_cat > button');
                categoryIds     = returnSelectedItemIdentifiers(selected);
        break;
        default:
            break;
    }

    return {'productIds':productIds,'categoryIds':categoryIds,'userIds':userIds,'userRoleSlugs':userRoleSlugs, 'userGroupIds':userGroupIds};
}

function returnSelectedItemIdentifiers(selections)
{
    let identifiers = [], length=0;
    let tmp=[];
    length = selections.length;
    if (length>0) {
        for (let i = 0; i < length; i++) {
            tmp = {'id':jQuery(selections[i]).attr('pid'), 'name':jQuery(selections[i]).text()};
            identifiers.push(tmp);
            tmp = [];
        }
    }
    return identifiers;
}


function getAllSubrules(RuleSection)
{
    let ruleArray = [];
    let rules = RuleSection.find('.cd-range-row');
    let min, max, discount, rule;
    if (rules.length>1) {
        for (let i = 0; i < rules.length-1; i++) {
            $rule= jQuery(rules[i]);
            min         = $rule.find('input.cd-min-cart-val').val();
            max         = $rule.find('input.cd-max-cart-val').val();
            discount    = $rule.find('input.cd-discount-input').val();
        
        ruleArray.push({'min':min,'max':max, 'discount':discount});
        }
        $rule = jQuery(rules[rules.length-1]);
        min         = $rule.find('input.cd-min-cart-val').val();
        max         = $rule.find('input.cd-max-cart-val').val();
        discount    = $rule.find('input.cd-discount-input').val();
        if (min.length>0 && discount.length>0) {
            ruleArray.push({'min':min,'max':max, 'discount':discount});
        }
    } else {
        $rule = jQuery(rules[0]);
        min         = $rule.find('input.cd-min-cart-val').val();
        max         = $rule.find('input.cd-max-cart-val').val();
        discount    = $rule.find('input.cd-discount-input').val();
        if (min.length>0 && discount.length>0) {
            ruleArray.push({'min':min,'max':max, 'discount':discount});
        }
    }
    return ruleArray;
}


function sendRuleDataToSave(ftbData, exbData, guData)
{
    jQuery('button.save-all-cd-rules').text('Saving..');
    jQuery('button.save-all-cd-rules').attr('disabled', true);
    jQuery('button.save-all-cd-rules').css('cursor','progress');
    jQuery.ajax(
        {
            type: 'POST',
            url: wdm_csp_cd_object.ajax_url,
            data:  { 
                    'action' : 'save_rule_data',
                    'cd_nonce': wdm_csp_cd_object.nonce,
                    'ftbData': ftbData,
                    'exbData': exbData,
                    'guData' : guData,
                    },
            success: function(rsp){
                    jQuery('button.save-all-cd-rules').text('Save All');
                    jQuery('button.save-all-cd-rules').removeAttr('disabled');
                    jQuery('button.save-all-cd-rules').css('cursor','pointer');
                    alert("Rules are successfully saved");
                }
        });
}

/**
 * Returns wether submited string is a valid URL or not.
 * @param {*} url 
 */
function cspIsValid(url) {
    return /^(https?|s?ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(url);
}