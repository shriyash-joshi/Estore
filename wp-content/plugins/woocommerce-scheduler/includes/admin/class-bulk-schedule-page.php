<?php
namespace Includes\AdminSettings;

if (!class_exists('schedulerAdminBulkSchedules')) {

    class schedulerAdminBulkSchedules
    {
        
        public function __construct() {
            $this->enqueueStylesForPage();
            $this->pageHtml();
            $this->enqueueScriptsForPage();
        }


        public function enqueueStylesForPage() {
            wp_enqueue_style('general_settings_css');
            wp_enqueue_style('jquery_datatables_css');
            wp_enqueue_style('bootstrap_css');
            wp_enqueue_style('wdm_datepicker_css');
            wp_enqueue_style('wdm_bulk_edit_page_form');
            wp_enqueue_style('wdm-scheduler-style');
            //select 2 script
            wp_enqueue_script('woo_singleview_select2_js_handler');
            wp_enqueue_style('woo_select2_css');
            wp_enqueue_style('bootstrap_select2_css');
        }


        public function pageHtml() {
            $pageTitle = __('Bulk Schedule', 'woocommerce-scheduler');
	        ?>
            <style>
            .wdmws-page-title {
                font-size:28px;
                font-weight:400;
                color:#000000;
                line-height:1.2rem;
            }
            </style>
	        <h1 class="wdmws-page-title"><?php echo esc_html($pageTitle);?></h1>
	        <div class="wdm-woo-scheduler-settings">
            	<div class="container-fluid wdm_selection_criteria">
                	<div class="row wdm_selection_row">
                    	<!-- Select Basic -->
                    	<div class="form-group col-md-12">
                        	<label class="col-md-1 control-label" for="woo_schedule_selection_type"><?php _e('Select Option', WDM_WOO_SCHED_TXT_DOMAIN); ?></label>
                        	<div class="col-md-4">
                            	<select id="woo_schedule_selection_type" name="woo_schedule_selection_type" class="form-control">
                                	<option value="-1" disabled selected><?php _e('Select Product or Category', WDM_WOO_SCHED_TXT_DOMAIN); ?></option>
                                	<option value="product"><?php _e('Products', WDM_WOO_SCHED_TXT_DOMAIN); ?></option>
                                	<option value="category"><?php _e('Product category', WDM_WOO_SCHED_TXT_DOMAIN); ?></option>
                            	</select>
                        	</div>
                    	</div>
                	</div>
            	</div>
	            <div class="container-fluid wdm_selection_details">
            	</div>
	        </div>
	        <?php
        }


        public function enqueueScriptsForPage()
        {
            wp_enqueue_script('wdm_moment_js_handler');
            wp_enqueue_script('wdm_datepicker');
            $wdmwsSettings = \Includes\AdminSettings\WdmWsSettings::getSettings();
            $expiration_type = isset($wdmwsSettings) && isset($wdmwsSettings['wdmws_custom_product_expiration_type'][0]) ? $wdmwsSettings['wdmws_custom_product_expiration_type'][0] : 'per_day' ;
            wp_enqueue_script('wdm_singleview_js_handler');
            wp_enqueue_script('jquery_datatables_js');
            wp_enqueue_script('wdm_bulk_js_form');
            wp_enqueue_script('wdm-scheduler-script');
            
            $days_selection_type = $expiration_type;

            $array_to_sent = array(
                'admin_ajax_path'        => admin_url('admin-ajax.php'),
               'loading_image_path'     => plugins_url('../css/images/loading.gif', dirname(__FILE__)),
               'loading_text'           => __('Loading', WDM_WOO_SCHED_TXT_DOMAIN),
               'details_updated_msg'    => __('Details updated.', WDM_WOO_SCHED_TXT_DOMAIN),
               'selection_empty_msg'    => __('Selection empty.', WDM_WOO_SCHED_TXT_DOMAIN),
               'confirm_product_save_msg'     => __('This will override all previous products schedule entries, Do you want to proceed?', WDM_WOO_SCHED_TXT_DOMAIN),
               'confirm_cat_save_msg'       => __('This will override all previous category schedule entries, Do you want to proceed?', WDM_WOO_SCHED_TXT_DOMAIN),
               'delete_confirm_msg'     => __('This will delete entry, Do you want to proceed?', WDM_WOO_SCHED_TXT_DOMAIN),
               'please_select_msg'      => __('Please select', WDM_WOO_SCHED_TXT_DOMAIN),
               'days_selection_type'    => $days_selection_type,
               'wdmFeedbackStatus'      =>get_option('wdmws_feedback_status'),
            );

            wp_localize_script('wdm_singleview_js_handler', 'woo_single_view_obj', $array_to_sent);
            
            //validation script
            wp_enqueue_script('woo_singleview_validate_js_handler');
        
             $array_to_sent = array('type' => $expiration_type,
                   'error_msg_end_time_more_than_start_time' => __('End time can\'t be less than start time', WDM_WOO_SCHED_TXT_DOMAIN),
                   'error_msg_time_empty' => __('Please Select Time', WDM_WOO_SCHED_TXT_DOMAIN),
                   'error_msg_start_date_less_than_end_date' => __('Start date must be less than End date', WDM_WOO_SCHED_TXT_DOMAIN),
                   'error_msg_details_empty' => __('Please enter all details', WDM_WOO_SCHED_TXT_DOMAIN));
        
             wp_localize_script('woo_singleview_validate_js_handler', 'woo_schedule_validate_obj', $array_to_sent);
        }
    }
    

}
