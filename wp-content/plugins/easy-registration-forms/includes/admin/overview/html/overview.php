<div id="erforms-overview" class="wrap erforms-admin-wrap erforms-overview erf-wrapper erf-wrapper-bg">
    <?php
        $form_cards = new ERForms_Form_Cards;
        $form_cards->prepare_items();
    ?>
       
    <?php if(isset($options['consent_allowed']) && $options['consent_allowed']==2): ?>
    <div class="updated settings-error notice is-dismissible">
        <form method="post">
            <p>
                In order for us to better serve you, allow us to track usage of this plugin.
                &nbsp;<input type="submit" name="erf_consent_allow" value="Allow" class="button action"/>
                <input type="submit" name="erf_consent_disallow" value="Disallow" class="button action"/>
            </p>
        </form>
    </div>
    <?php endif; ?>
    <form id="erforms-overview-table" method="get" action="<?php echo admin_url('admin.php?page=erforms-overview'); ?>">
        <div class="erf-page-title">
            <h1 class="wp-heading clearfix">
                <?php _e('All Forms', 'erforms'); ?>
                <div class="erf-search-form">
                    <?php $search = isset($_GET['filter_key']) ? esc_attr(urldecode(wp_unslash($_GET['filter_key']))) : ''; ?>
                    <label><input class="erf-input-field" type="text" value="<?php echo esc_attr($search); ?>" name="filter_key" placeholder="<?php _e('Search in Form','erforms'); ?>"></label>
                </div>
            </h1>
        </div>
        <input type="hidden" name="page" value="erforms-overview" />
        <input type="submit" style="display:none"/>
    </form>    
    <div class="erforms-admin-content">
        <div class="erf-card-wrap">
            <?php $form_cards->views(); ?>
            <?php $form_cards->display(); ?>
        </div>
    </div>

</div>



<div id="erf_overview_add_form_dialog" class="erf_dialog" style="display: none;">
    <div class="modal-dialog">    
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php _e('ADD NEW FORM','erforms'); ?></h5>
                <button type="button" class="close erf_close_dialog">
                    <span>×</span>
                </button>
            </div>

            <div class="modal-body">
                <div class="erf-form-name">
                    <?php _e('Name of your form','erforms'); ?>
                    <input type="text" id="erf_overview_input_form_name" class="erf-input-field"/></div>
                <div id="erf_overview_add_form_response"></div>
                <div class="erf-ajax-progress" style="display:none"></div>

                <div class="erf-form-type">
                    <div class="erf-form-type-head">
                        <input value="reg" type="radio" name="erf_overview_input_form_type" id="registration-form" checked/>
                        <label for="registration-form"><?php _e('User Registration Form','erforms'); ?></label>
                    </div>
                </div>
                <?php do_action('erf_form_type'); ?>
                <div class="erf-form-type">
                    <div class="erf-form-type-head">
                        <input type="radio" name="erf_overview_input_form_type" value="contact" id="contact-form"/>
                        <label for="contact-form"><?php _e('Contact/Other Form','erforms'); ?></label> 
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <input type="button" id="erf_overview_add_form_btn" class="button button-primary" value="<?php _e('Save','erforms'); ?>" />
            </div>
        </div>
    </div>
</div>


<div id="erf_overview_delete_form_dialog" class="erf_dialog" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h5><?php _e('Are you sure you want to delete?','erforms'); ?></h5>
            <button type="button" class="close erf_close_dialog">
                <span>×</span>
            </button>
        </div>
        <div class="modal-body">
            <?php _e('All the related submissions will be deleted.','erforms'); ?>
        </div>
        <div class="modal-footer">
            <input type="button" class="button button-primary erf-close-btn" value="<?php _e('Close','erforms'); ?>" />
            <input type="button" class="button button-danger erf-confirm-btn" value="<?php _e('Confirm','erforms'); ?>" />
        </div>                                
    </div>
</div>
<div class="wrap additional-features">
    <div class="features-title">
        <h1><?php _e('Additional Features', 'erforms'); ?></h1>
    </div>
    <div class="erf-add-ons flex-s-e">
        <div class="erf-add-on">
            <a
                target="_blank"
                href="http://www.easyregistrationforms.com/product/conditional-field-extension/"
            >
                <div class="add-on-img">
                    <img
                        src="<?php echo ERFORMS_PLUGIN_URL.'/assets/admin/images/addons/conditional-logics.png' ?>"
                    />
                </div>
                <div class="description">
                    <h3><?php _e('Conditional Logics', 'erforms'); ?></h3>
                    <p>
                        <?php _e('Conditional Logic extension allows you to show/hide fields on the basis of desired conditions in addition mail can be sent to various recipients on the basis of selected.', 'erforms'); ?>
                    </p>
                </div>
            </a>
        </div>
        <div class="erf-add-on">
            <a
                target="_blank"
                href="http://www.easyregistrationforms.com/product/easy-registration-paypal-integration/"
            >
                <div class="add-on-img">
                    <img
                        src="<?php echo ERFORMS_PLUGIN_URL.'/assets/admin/images/addons/paypal.png' ?>"
                    />
                </div>
                <div class="description">
                    <h3><?php _e('PayPal Integration', 'erforms'); ?></h3>
                    <p>
                        <?php _e('Allows user to pay through PayPal', 'erforms'); ?>
                    </p>
                </div>
            </a>
        </div>
        <div class="erf-add-on">
            <a
                target="_blank"
                href="https://www.easyregistrationforms.com/product/easy-registration-stripe-integration/"
            >
                <div class="add-on-img">
                    <img
                        src="<?php echo ERFORMS_PLUGIN_URL.'/assets/admin/images/addons/stripe.jpg' ?>"
                    />
                </div>
                <div class="description">
                    <h3><?php _e('Stripe Integration', 'erforms'); ?></h3>
                    <p>
                        <?php _e('Allows user to pay through Stripe', 'erforms'); ?>
                    </p>
                </div>
            </a>
        </div>
        <div class="erf-add-on">
            <a
                target="_blank"
                href="http://www.easyregistrationforms.com/product/mailchimp-extension/"
            >
                <div class="add-on-img">
                    <img
                        src="<?php echo ERFORMS_PLUGIN_URL.'/assets/admin/images/addons/mail-chimp.png' ?>"
                    />
                </div>
                <div class="description">
                    <h3><?php _e('Mailchimp Integration', 'erforms'); ?></h3>
                    <p>
                        <?php _e('Create MailChimp signup forms in WordPress to grow your email list.', 'erforms'); ?>
                    </p>
                </div>
            </a>
        </div>
        <div class="erf-add-on">
            <a
                target="_blank"
                href="http://www.easyregistrationforms.com/product/gdpr-compliance/"
            >
                <div class="add-on-img">
                    <img
                        src="<?php echo ERFORMS_PLUGIN_URL.'/assets/admin/images/addons/gdpr-compliance.png' ?>"
                    />
                </div>
                <div class="description">
                    <h3><?php _e('GDPR compliance', 'erforms'); ?></h3>
                    <p>
                        <?php _e('Allow to add GDPR/privacy compliance checkbox in the end of form. Also integrates with WordPress Export Personal Data and Erase Personal Data.', 'erforms'); ?>
                    </p>
                </div>
            </a>
        </div>
        <div class="erf-add-on">
            <a
                target="_blank"
                href="https://www.easyregistrationforms.com/product/easy-registration-submission-importer/"
            >
                <div class="add-on-img">
                    <img
                        src="<?php echo ERFORMS_PLUGIN_URL.'/assets/admin/images/addons/submission-importer.png' ?>"
                    />
                </div>
                <div class="description">
                    <h3><?php _e('Submission Importer', 'erforms'); ?></h3>
                    <p>
                        <?php _e('allows to import bulk data as submissions from any CSV file.', 'erforms'); ?>
                    </p>
                </div>
            </a>
        </div>
        <div class="erf-add-on">
            <a
                target="_blank"
                href="https://www.easyregistrationforms.com/product/submission-views/"
            >
                <div class="add-on-img">
                    <img
                        src="<?php echo ERFORMS_PLUGIN_URL.'/assets/admin/images/addons/views.png' ?>"
                    />
                </div>
                <div class="description">
                    <h3><?php _e('Submission Views', 'erforms'); ?></h3>
                    <p>
                        <?php _e('Simple yet powerful way to display Submission entries on your website.', 'erforms'); ?>
                    </p>
                </div>
            </a>
        </div>
        <div class="erf-add-on">
            <a
                target="_blank"
                href="https://www.easyregistrationforms.com/product/plugin-bundle/"
            >
                <div class="add-on-img">
                    <img
                        src="<?php echo ERFORMS_PLUGIN_URL.'/assets/admin/images/addons/plugin-bundle.png' ?>"
                    />
                </div>
                <div class="description">
                    <h3><?php _e('Plugin Bundle', 'erforms'); ?></h3>
                    <p>
                        <?php _e('All our premium features in one bundle.', 'erforms'); ?>
                    </p>
                </div>
            </a>
        </div>
    </div>
</div>


<script>
    window.addEventListener('click', function(e){ 
        $=jQuery;
        var target= $(e.target);
        if(!target.hasClass('menu-icon')){
            $('.erf-card-actions').addClass('erform-hidden');
        }
    });
</script>    