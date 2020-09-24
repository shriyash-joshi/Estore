<div class="wrap about-wrap full-width-layout erf-addons erf-wrapper">
    <div class="erf-page-title">
        <h1 class="wp-heading-inline"><?php _e('Easy Registration Form Add-on', 'erforms'); ?></h1>
    </div>
    <div class="erf-add-on-wrap erforms-admin-content">
        <div class="erf-add-on">
            <a target="_blank" href="http://www.easyregistrationforms.com/product/gdpr-compliance/">
            <div class="add-on-img">
                <img src="<?php echo ERFORMS_PLUGIN_URL.'/assets/admin/images/addons/gdpr-compliance.png' ?>">
            </div>
            <h3><?php _e('GDPR compliance', 'erforms'); ?></h3>
            <p><?php _e('Allow to add GDPR/privacy compliance checkbox in the end of form. Also integrates with WordPress Export Personal Data and Erase Personal Data.', 'erforms'); ?></p>
            </a>
        </div>
        <div class="erf-add-on">
            <a target="_blank" href="http://www.easyregistrationforms.com/product/conditional-field-extension/" target="_blank">
            <div class="add-on-img">
                        
                <img src="<?php echo ERFORMS_PLUGIN_URL.'/assets/admin/images/addons/conditional-logics.png' ?>">
            </div>
            <h3><?php _e('Conditional Logics', 'erforms'); ?></h3>
            <p><?php _e('Conditional Logic extension allows you to show/hide fields on the basis of desired conditions in addition mail can be sent to various recipients on the basis of selected.', 'erforms'); ?></p>
            </a>
        </div>
        <div class="erf-add-on">
            <a target="_blank" href="http://www.easyregistrationforms.com/product/mailchimp-extension/">
            <div class="add-on-img">
                <img src="<?php echo ERFORMS_PLUGIN_URL.'/assets/admin/images/addons/mail-chimp.png' ?>">
            </div>
            <h3><?php _e('Mailchimp Integration', 'erforms'); ?></h3>
            <p><?php _e('Create MailChimp signup forms in WordPress to grow your email list.', 'erforms'); ?></p>
            </a>
        </div>        
        <div class="erf-add-on">
            <a target="_blank" href="http://www.easyregistrationforms.com/product/easy-registration-paypal-integration/">
            <div class="add-on-img">
                <img src="<?php echo ERFORMS_PLUGIN_URL.'/assets/admin/images/addons/paypal.png' ?>">
            </div>
            <h3><?php _e('PayPal Integration', 'erforms'); ?></h3>
            <p><?php _e('Allows user to pay through PayPal', 'erforms'); ?></p>
            </a>
        </div>
        <div class="erf-add-on">
            <a target="_blank" href="https://www.easyregistrationforms.com/product/easy-registration-stripe-integration/">
            <div class="add-on-img">
                <img src="<?php echo ERFORMS_PLUGIN_URL.'/assets/admin/images/addons/stripe.jpg' ?>">
            </div>
            <h3><?php _e('Stripe Integration', 'erforms'); ?></h3>
            <p><?php _e('Allows user to pay through Stripe', 'erforms'); ?></p>
            </a>
        </div>
        <div class="erf-add-on">
            <a target="_blank" href="https://www.easyregistrationforms.com/product/easy-registration-submission-importer/">
            <div class="add-on-img">
                <img src="<?php echo ERFORMS_PLUGIN_URL.'/assets/admin/images/addons/submission-importer.png' ?>">
            </div>
            <h3><?php _e('Submission Importer', 'erforms'); ?></h3>
            <p><?php _e('allows to import bulk data as submissions from any CSV file.', 'erforms'); ?></p>
            </a>
        </div>
        <div class="erf-add-on">
            <a target="_blank" href="https://www.easyregistrationforms.com/product/submission-views/">
            <div class="add-on-img">
                <img src="<?php echo ERFORMS_PLUGIN_URL.'/assets/admin/images/addons/views.png' ?>">
            </div>
            <h3><?php _e('Submission Views', 'erforms'); ?></h3>
            <p><?php _e('Simple yet powerful way to display Submission entries on your website.', 'erforms'); ?></p>
            </a>
        </div>
        <div class="erf-add-on">
            <a target="_blank" href="https://www.easyregistrationforms.com/product/plugin-bundle/">
            <div class="add-on-img">
                <img src="<?php echo ERFORMS_PLUGIN_URL.'/assets/admin/images/addons/plugin-bundle.png' ?>">
            </div>
            <h3><?php _e('Plugin Bundle', 'erforms'); ?></h3>
            <p><?php _e('All our premium features in one bundle.', 'erforms'); ?></p>
            </a>
        </div>
    </div>

    
    
    <div class="erf-feature-request">
        <h4><?php _e('Have a feature in mind, share with us ', 'erforms'); ?><a href="http://www.easyregistrationforms.com/support/" target="_blank"><?php _e('here', 'erforms'); ?></a></h4>
    </div>
</div>

<style>
    .erf-add-on-wrap{
        display: flex;
	flex-direction: row;
	flex-wrap: wrap;
	justify-content: space-between;
	align-items: stretch;
	align-content: center;
    }
    .erf-add-on-wrap .erf-add-on{
        width: 23%;
        max-width: 300px;
        text-align: center;
        padding: 10px;
        margin-bottom: 30px;
        box-sizing: border-box;
        box-shadow: 0 0 2rem 0 rgba(136,152,170,.15);
        border-radius: 10px;
        transition: all 0.15s ease-in-out;
    }
    .erf-add-on-wrap .erf-add-on:hover{
        transform: translateY(-5px);
    }
    .erf-add-on-wrap a{
        text-decoration: none;
        color: inherit;
    }
    .erf-add-on-wrap img{
        border-radius: 4px; 
    }
    .erf-add-on-wrap h3{
        font-size: 17px;
    }
    .erf-feature-request h4{
        text-align: center;
    }
    @media all and (max-width: 1200px) {}
    @media all and (max-width: 979px) {}
    @media all and (max-width: 767px) {
        .erf-add-on-wrap .erf-add-on{
            width: 50%;
            max-width: 300px;
        }
    }
    @media all and (max-width: 479px) {}

</style>
