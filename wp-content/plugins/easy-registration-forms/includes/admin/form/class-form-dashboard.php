<?php
/**
 * Form Dashboard
 *
 * @package    ERForms
 * @author     ERForms
 * @since      1.0.0
 */
class ERForms_Dashboard {

	/**
	 * Current view (panel)
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $view;

	/**
	 * Available panels.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	public $panels;

	/**
	 * Current template information.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	public $template;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'init' ) );
                
	}

	/**
	 *
	 * @since 1.0.0
	 */
	public function init() { 
                
		// Check what page we are on
		$page = isset( $_GET['page'] ) ? sanitize_text_field($_GET['page']) : '';
                
		// Only load if we are actually on the builder
		if ( 'erforms-dashboard' === $page ) {

			// Load form if found
			$form_id = isset( $_GET['form_id'] ) ? absint( $_GET['form_id'] ) : false;
                        if(empty($form_id))
                            return;
			add_action( 'admin_enqueue_scripts',array( $this, 'enqueues'));
			add_action( 'erforms_admin_page',array( $this, 'output') );
                        
                        add_action( 'admin_head', array($this,'admin_head') );
                        wp_enqueue_editor();
                        wp_enqueue_media();
		}
	}
        
        /*
         * Adds JS variable in global scope to be used in different JS files. 
         * For eg: TinyMCE plugin
         */
        public function admin_head(){
            $form_id = isset( $_GET['form_id'] ) ? absint( $_GET['form_id'] ) : false;
            if(!$form_id)
                return;
            
            // check if WYSIWYG is enabled
            if ( get_user_option('rich_editing') == 'true') { 
                add_filter("mce_external_plugins", array($this,'embed_fields_button_js'));
                add_filter('mce_buttons', array($this,'register_fields_button'));
            }

            $fields= erforms_get_fields_tinymce($form_id);
            ?>
            <script>
                var erf_form_fields= <?php echo $fields; ?>;
            </script>
            <?php
            
        }
        
        /*
         * Enqueues JS file as external plugin in TinyMCE
         */
        public function embed_fields_button_js($plugin_array){
        
            $plugin_array['erf_fields_button'] = ERFORMS_PLUGIN_URL.'assets/admin/js/tinymce/fields-button.js';
            return $plugin_array;
        }
        
        /*
         * Registers Add Field button in TinyMCE
         */
        function register_fields_button($buttons) {
            array_push($buttons, "erf_fields_button");
            return $buttons;
        }
         
	/**
	 * Enqueue assets for the builder.
	 *
	 * @since 1.0.0
	 */
	public function enqueues() {
            wp_enqueue_script('jquery-ui-sortable');
            wp_enqueue_script('jquery-ui-datepicker','',array('jquery'));
            wp_enqueue_style('jquery-datepicker',ERFORMS_PLUGIN_URL.'assets/admin/css/jquery-datepicker.css');
            wp_enqueue_style('jquery-theme',ERFORMS_PLUGIN_URL.'assets/admin/css/jquery-theme.css');
            
            // Registering Scripts  
            wp_register_script("erf-font-awesome-icon-picker",ERFORMS_PLUGIN_URL.'assets/admin/js/font-awesome/fontawesome-iconpicker.min.js');		
            wp_register_script("erf-masked-input",ERFORMS_PLUGIN_URL.'assets/js/jquery.masked.input.js',array('jquery'));	
            wp_register_script('timepicker', ERFORMS_PLUGIN_URL . 'assets/admin/js/jquery.timepicker.min.js',array('jquery'));
            wp_register_script('vuejs', ERFORMS_PLUGIN_URL . 'assets/admin/js/vue_2.6.10.js',array('jquery'));
            wp_register_script('vuejs_sortable', ERFORMS_PLUGIN_URL . 'assets/admin/js/sortable.min.js',array('vuejs'));
            wp_register_script('vuejs_draggable', ERFORMS_PLUGIN_URL . 'assets/admin/js/vue-draggable.min.js',array('vuejs'));
            wp_register_script('formula-engine',ERFORMS_PLUGIN_URL.'assets/js/formula-engine.js',array(),ERFORMS_VERSION);
            wp_register_script("erf-builder",ERFORMS_PLUGIN_URL.'assets/admin/js/builder.js',array('formula-engine'),ERFORMS_VERSION);
            
            // Registering Styles
            wp_register_style('erf-font-awesome-css','https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
            wp_register_style('erf-font-awesome-icon-picker',ERFORMS_PLUGIN_URL.'assets/admin/css/font-awesome/fontawesome-iconpicker.min.css');
            wp_register_style('erf-builder-style',ERFORMS_PLUGIN_URL.'assets/admin/css/form-builer.css');
            wp_register_style('timepicker', ERFORMS_PLUGIN_URL . 'assets/admin/css/jquery.timepicker.min.css');
             
            // Hook for addons.
            do_action( 'erf_builder_enqueue' );
	}

	/**
	 * Load the appropriate files to build the page.
	 *
	 * @since 1.0.0
	 */
	public function output() {
            $form_id = isset( $_GET['form_id'] ) ? absint( $_GET['form_id'] ) : false;
            $form_model= erforms()->form;
            $text_helpers= erforms_text_helpers();
            $action= isset($_REQUEST['action']) ? sanitize_text_field($_REQUEST['action']) : null;
            
            if($action=='delete'){
                $this->delete_report($form_id);
            } else if($action=='run'){ 
                $this->run_report_manually($form_id);
            } 
            $this->update_report_status($form_id); // Check for report update status
            

            $form= $this->save_and_load($form_id);
            if(empty($form))
                return;
            $roles= erforms_wp_roles_dropdown();
            wp_localize_script('erf-builder','erf_data',array('form_data'=>$form,'locale'=>get_locale(),'text_helpers'=>$text_helpers,'roles'=>$roles,'field_types'=>erforms_field_types($form['type'])));
            $options= erforms()->options->get_options();
            include 'html/dashboard.php';
		
	}
        
        private function save_and_load($form_id){
            $form_model= erforms()->form;
            $form= $form_model->get_form($form_id);
            $this->save_configuration($form);
            $this->save_report($form);
            $this->save_notifications($form);
            do_action('form_dashboard_save_and_load',$form);
            // Get update form data
            $form= erforms()->form->get_form($form_id);
            return $form;
        }
        
        public function save_notifications($form){
            if(!isset($_POST['erf_save_notifications']) || empty($form)){
                return;
            }
            $_post= wp_unslash($_POST);
            $form_model= erforms()->form;
            $form['enabled_auto_reply']= isset($_post['enabled_auto_reply']) ? absint($_post['enabled_auto_reply']) : 0;
            $form['auto_reply_subject']= isset($_post['auto_reply_subject']) ? sanitize_text_field($_post['auto_reply_subject']) : '';
            $form['auto_reply_msg']=   isset($_post['auto_reply_msg']) ?  wp_kses_post($_post['auto_reply_msg']) : '';
            $form['auto_reply_from']= isset($_post['auto_reply_from']) ? sanitize_text_field($_post['auto_reply_from']) : '';
            $form['auto_reply_from_name']= isset($_post['auto_reply_from_name']) ? sanitize_text_field($_post['auto_reply_from_name']) : '';
            $form['auto_reply_to']= isset($_post['auto_reply_to']) ? sanitize_text_field($_post['auto_reply_to']) : '';
            if(!empty($form['auto_reply_to'])){
                $sanitized_list= array();
                $list= explode(',', $form['auto_reply_to']);
                foreach($list as $email){
                    $email= sanitize_text_field($email);
                    $sanitized_list[]= $email;
                }
                $form['auto_reply_to']= implode(',', $sanitized_list);
            } 
            
            $form['enable_admin_notification']= isset($_post['enable_admin_notification']) ? absint($_post['enable_admin_notification']) : 0;
            $form['admin_notification_from']= isset($_post['admin_notification_from']) ? sanitize_text_field($_post['admin_notification_from']) : '';
            $form['admin_notification_from_name']= isset($_post['admin_notification_from_name']) ? sanitize_text_field($_post['admin_notification_from_name']) : '';
            $form['admin_notification_subject']= isset($_post['admin_notification_subject']) ? sanitize_text_field($_post['admin_notification_subject']) : '';
            $form['admin_notification_msg']= isset($_post['admin_notification_msg']) ? wp_kses_post($_post['admin_notification_msg']) : '';
            $form['enable_act_notification']= isset($_post['enable_act_notification']) ? absint($_post['enable_act_notification']) : 0;
            $form['user_act_subject']= isset($_post['user_act_subject']) ? sanitize_text_field($_post['user_act_subject']) : '';
            $form['user_act_msg']= isset($_post['user_act_msg']) ? wp_kses_post($_post['user_act_msg']) : '';
            if($form['type']=='reg'){
                $form['user_act_from']= isset($_post['user_act_from']) ? sanitize_text_field($_post['user_act_from']) : '';
                $form['user_act_from_name']= isset($_post['user_act_from_name']) ? sanitize_text_field($_post['user_act_from_name']) : '';
                $form['en_user_ver_msg']= isset($_post['en_user_ver_msg']) ? 1 : 0;
                $form['user_ver_from']= isset($_post['user_ver_from']) ? sanitize_text_field($_post['user_ver_from']) : '';
                $form['user_ver_from_name']= isset($_post['user_ver_from_name']) ? sanitize_text_field($_post['user_ver_from_name']) : '';
                $form['user_ver_subject']= isset($_post['user_ver_subject']) ? sanitize_text_field($_post['user_ver_subject']) : '';
                $form['user_ver_email_msg']= isset($_post['user_ver_email_msg']) ? wp_kses_post($_post['user_ver_email_msg']) : '';
            }
           
            
            $form['enable_edit_notifications']= isset($_post['enable_edit_notifications']) ? absint($_post['enable_edit_notifications']) : 0;
            $form['edit_sub_user_from']= isset($_post['edit_sub_user_from']) ? sanitize_text_field($_post['edit_sub_user_from']):'';
            $form['edit_sub_user_from_name']= isset($_post['edit_sub_user_from_name']) ? sanitize_text_field($_post['edit_sub_user_from_name']):'';
            $form['edit_sub_user_subject']= isset($_post['edit_sub_user_subject']) ? sanitize_text_field($_post['edit_sub_user_subject']):'';
            $form['edit_sub_user_email']= isset($_post['edit_sub_user_email']) ? wp_kses_post($_post['edit_sub_user_email']):'';
            $form['edit_sub_admin_from']= isset($_post['edit_sub_admin_from']) ? sanitize_text_field($_post['edit_sub_admin_from']):'';
            $form['edit_sub_admin_from_name']= isset($_post['edit_sub_admin_from_name']) ? sanitize_text_field($_post['edit_sub_admin_from_name']):'';
            $form['edit_sub_admin_subject']= isset($_post['edit_sub_admin_subject']) ? sanitize_text_field($_post['edit_sub_admin_subject']):'';
            $form['edit_sub_admin_email']= isset($_post['edit_sub_admin_email']) ? wp_kses_post($_post['edit_sub_admin_email']):'';
            $edit_sub_admin_list= $_post['edit_sub_admin_list'];
            if(!empty($edit_sub_admin_list))
            {
                $admin_sanitized_list= array();
                $admin_list_array= explode(',', $edit_sub_admin_list);
                foreach($admin_list_array as $admin_email){
                    $admin_email= sanitize_text_field($admin_email);
                    if(ERForms_Validation::email($admin_email)){
                        $admin_sanitized_list[]= $admin_email;
                    }
                }
                $form['edit_sub_admin_list']= implode(',', $admin_sanitized_list);
            }
            else
            {
                $form['edit_sub_admin_list']= '';
            }
            
            $form['enable_delete_notifications']= isset($_post['enable_delete_notifications']) ? absint($_post['enable_delete_notifications']) : 0;
            $form['delete_sub_user_from']= isset($_post['delete_sub_user_from']) ? sanitize_text_field($_post['delete_sub_user_from']):'';
            $form['delete_sub_user_from_name']= isset($_post['delete_sub_user_from_name']) ? sanitize_text_field($_post['delete_sub_user_from_name']):'';
            $form['delete_sub_user_subject']= isset($_post['delete_sub_user_subject']) ? sanitize_text_field($_post['delete_sub_user_subject']):'';
            $form['delete_sub_user_email']= isset($_post['delete_sub_user_email']) ? wp_kses_post($_post['delete_sub_user_email']):'';
            $form['delete_sub_admin_from']= isset($_post['delete_sub_admin_from']) ? sanitize_text_field($_post['delete_sub_admin_from']):'';
            $form['delete_sub_admin_from_name']= isset($_post['delete_sub_admin_from_name']) ? sanitize_text_field($_post['delete_sub_admin_from_name']):'';
            $form['delete_sub_admin_subject']= isset($_post['delete_sub_admin_subject']) ? sanitize_text_field($_post['delete_sub_admin_subject']):'';
            $form['delete_sub_admin_email']= isset($_post['delete_sub_admin_email']) ? wp_kses_post($_POST['delete_sub_admin_email']):'';
            
            $admin_notification_to= $_post['admin_notification_to'];
            if(!empty($admin_notification_to))
            {
                $admin_sanitized_list= array();
                $admin_list_array= explode(',', $admin_notification_to);
                foreach($admin_list_array as $admin_email){
                    $admin_email= sanitize_text_field($admin_email);
                    if(ERForms_Validation::email($admin_email)){
                        $admin_sanitized_list[]= $admin_email;
                    }
                }
                $form['admin_notification_to']= implode(',', $admin_sanitized_list);
            }
            else
            {
                $form['admin_notification_to']='';
            }
           
            $form= apply_filters('erforms_save_notifications',$form);
            $form_model->update_form($form);
            if(isset($_POST['savec'])){// Save and Close
                $url= admin_url('admin.php?page=erforms-dashboard&form_id='.$form['id']);
                erforms_redirect($url);
                exit;
            }
        }
        
        public function save_configuration($form){
            if(!isset($_POST['erf_save_configuration']) || empty($form)){
                return;
            }
            $_post= wp_unslash($_POST);
            $form_model= erforms()->form;
            $form['default_role'] = isset($_post['default_role']) ? sanitize_text_field($_post['default_role']) : array();
            $form['auto_login'] =   isset($_post['auto_login']) ? absint($_post['auto_login']) : 0;
            $form['redirect_to'] =  isset($_post['redirect_to']) ? esc_url_raw($_post['redirect_to']) : '';
            $form['before_form']=   wp_kses_post($_post['before_form']);
            $form['success_msg'] =  isset($_post['success_msg']) ? wp_kses_post($_post['success_msg']) : '';
            
            $form['recaptcha_enabled']= isset($_post['recaptcha_enabled']) ? absint($_post['recaptcha_enabled']) : 0;
            $form['en_pwd_restriction']= isset($_post['en_pwd_restriction']) ? absint($_post['en_pwd_restriction']) : 0;
            $form['pwd_res_question']=   isset($_post['pwd_res_question']) ? sanitize_text_field($_post['pwd_res_question']) : '';
            $form['pwd_res_answer']=   isset($_post['pwd_res_question']) ? sanitize_text_field($_post['pwd_res_answer']) : '';
            $form['auto_user_activation']= isset($_post['auto_user_activation']) ? absint($_post['auto_user_activation']) : 0;
            $form['label_position']=   sanitize_text_field($_post['label_position']);
            $form['layout']=   sanitize_text_field($_post['layout']);
            $form['access_roles']= isset($_post['access_roles']) ? $_post['access_roles'] : array();
            $form['access_roles'] = array_map('sanitize_text_field',$form['access_roles']);
            $form['access_denied_msg']= isset($_post['access_denied_msg']) ? wp_kses_post($_post['access_denied_msg']) : '';
            $form['enable_unique_id']= isset($_post['enable_unique_id']) ? absint($_post['enable_unique_id']) : 0;
            $form['unique_id_gen_method']= sanitize_text_field($_post['unique_id_gen_method']);
            $form['unique_id_index']= absint($_post['unique_id_index']);
            $form['unique_id_prefix']= sanitize_text_field($_post['unique_id_prefix']);
            $form['unique_id_padding']= absint($_post['unique_id_padding']);
            $form['unique_id_offset']= absint($_post['unique_id_offset']);
            $form['enable_external_url']= isset($_post['enable_external_url']) ? absint($_post['enable_external_url']) : 0;
            $form['external_url']= esc_url_raw($_post['external_url']);
            $form['enable_limit']= isset($_post['enable_limit']) ? 1 : 0;
            $form['limit_type']= isset($_post['limit_type']) ? sanitize_text_field($_post['limit_type']) : 'date';
            $form['limit_by_date']= isset($_post['limit_by_date']) ? sanitize_text_field($_post['limit_by_date']) : 'date';
            $form['limit_by_number']= isset($_post['limit_by_number']) ? absint($_post['limit_by_number']) : 0;
            $form['limit_time']= isset($_post['limit_time']) ? sanitize_text_field($_post['limit_time']) : '';
            $form['limit_message']= isset($_post['limit_message']) ? wp_kses_post($_post['limit_message']) : '';
            $form['allow_re_register']= isset($_post['allow_re_register']) ? absint($_post['allow_re_register']) : 0;
            $form['enable_login_form']= isset($_post['enable_login_form']) ? absint($_post['enable_login_form']) : 0;
            $form['show_before_login_form']= isset($_post['show_before_login_form']) ? absint($_post['show_before_login_form']) : 0;
            $form['login_and_register']= isset($_post['login_and_register']) ? absint($_post['login_and_register']) : 0;
            $form['pwd_res_err']= isset($_post['pwd_res_err']) ? sanitize_text_field($_post['pwd_res_err']) : '';
            $form['pwd_res_description']= isset($_post['pwd_res_description']) ? sanitize_text_field($_post['pwd_res_description']) : '';
            $form['pwd_res_en_logged_in']= isset($_post['pwd_res_en_logged_in']) ? absint($_post['pwd_res_en_logged_in']) : 0;
            $form['allow_only_registered']= isset($_post['allow_only_registered']) ? absint($_post['allow_only_registered']) : 0;
            $form['plan_enabled']= isset($_post['plan_enabled']) ? 1 : 0;
            if(isset($_post['plans'])){
                    $enabled= isset($_post['plans']['enabled']) ? array_map('absint',$_post['plans']['enabled']) : array();
                    $required= isset($_post['plans']['required']) ? array_map('absint',$_post['plans']['required']) : array();
                    $form['plans']= array('enabled'=>$enabled,'required'=>$required);
            }
            $form['allow_single_plan']= isset($_post['allow_single_plan']) ? 1 : 0;
            $form['payment_header']= isset($_post['payment_header']) ? sanitize_text_field($_post['payment_header']) : '';

            if(erforms_show_opt_in()){
                 $form['opt_in']= isset($_post['opt_in']) ? 1 : 0;
                 $form['opt_text']= sanitize_text_field($_post['opt_text']);
                 $form['opt_default_state']= absint($_post['opt_default_state']);
            }
           
            if($form['type']=='reg'){
                $form['en_email_verification']= isset($_post['en_email_verification']) ? 1 : 0;
                if(!empty($form['en_email_verification'])){
                    $form['en_user_ver_msg']=1;
                }
                else
                {
                     $form['en_user_ver_msg']=0;
                }
                //$form['act_link_expiry']= isset($_POST['act_link_expiry']) ? absint($_POST['act_link_expiry']) : 0;
                $form['user_acc_verification_msg']= isset($_post['user_acc_verification_msg']) ? wp_kses_post($_post['user_acc_verification_msg']) : '';
                $form['after_user_ver_page']= isset($_post['after_user_ver_page']) ? absint($_post['after_user_ver_page']) : 0;
                if(!empty($form['after_user_ver_page'])){
                    $page_content= get_post_field('post_content', $form['after_user_ver_page']);
                    if(!has_shortcode($page_content,'erforms_account_verification')){
                        $new_content = array(
                                        'ID'           => $form['after_user_ver_page'],
                                        'post_content' => $page_content.' <br> '.'[erforms_account_verification]',
                                    );
                        wp_update_post($new_content);
                    }
                }
                $form['auto_login_after_ver']= isset($_post['auto_login_after_ver']) ? 1 : 0;
            }
            $form['en_edit_sub']= isset($_post['en_edit_sub']) ? absint($_post['en_edit_sub']) : 0;
            $form['allow_sub_deletion']= isset($_post['allow_sub_deletion']) ? absint($_post['allow_sub_deletion']) : 0;
            $form['edit_fields']= empty($_post['edit_fields']) ? array() : array_map('sanitize_text_field',$_post['edit_fields']);
            $form['field_style']= empty($_post['field_style']) ? '' : sanitize_text_field($_post['field_style']);
            $form['primary_field']= isset($_post['primary_field']) ? sanitize_text_field($_post['primary_field']) : '';
            $form['primary_contact_name_field']= isset($_post['primary_contact_name_field']) ? sanitize_text_field($_post['primary_contact_name_field']) : '';
            $form['dis_mul_sub']= isset($_post['dis_mul_sub']) ? absint($_post['dis_mul_sub']) : 0;
            $form['mul_sub_denial_msg']= isset($_post['mul_sub_denial_msg']) ? wp_kses_post($_post['mul_sub_denial_msg']) : '';
            
            $form= apply_filters('erforms_save_configuration',$form);
            $form_model->update_form($form);
            if(isset($_POST['savec'])){// Save and Close
                $url= admin_url('admin.php?page=erforms-dashboard&form_id='.$form['id']);
                erforms_redirect($url);
                wp_die();
            }
        }
        
        public function save_report($form){
            if(!isset($_POST['erf_save_report']) || empty($form)){
                return;
            }
            $post = wp_unslash($_POST);
            $form_model= erforms()->form;
            $report= array();
            $report['name'] = sanitize_text_field($post['name']);
            $report['description'] =  sanitize_text_field($post['description']);
            $field_names= !empty($post['field_names']) ? explode(',', sanitize_text_field($post['field_names'])): array();
            $report['fields']= array();
            $fields = erforms_get_report_fields($form['id']);
            if(is_array($field_names) && !empty($field_names)){
                foreach($field_names as $name){
                    $single= array();
                    $single['alias']= sanitize_text_field($post[$name.'_alias']);
                    $single['included']= isset($post[$name.'_included']) ? absint($post[$name.'_included']): 0;
                    $single['label']= $fields[$name];
                    $report['fields'][$name]= $single;
                }
            }
            else{
                foreach ($fields as $name => $label){
                    $single= array();
                    $single['alias']= sanitize_text_field($post[$name.'_alias']);
                    $single['included']= isset($post[$name.'_included']) ? absint($post[$name.'_included']): 0;
                    $single['label']= $label;
                    $report['fields'][$name]= $single;
                }
            }
            
            $report['receipents'] =  sanitize_text_field($post['receipents']);
            $report['email_subject'] =  sanitize_text_field($post['email_subject']);
            $report['email_message'] =  wp_kses_post($post['email_message']);
            $report['active']= isset($post['active']) ? absint($post['active']) : 0;
            $report['created']= isset($post['created']) ? sanitize_text_field($post['created']) : current_time( 'timestamp' );
            $report['time']= sanitize_text_field($post['time']);
            $report['recurrence'] =  sanitize_text_field($post['recurrence']);
            $report['last']= empty($post['last']) ? 0 : sanitize_text_field($post['last']);
            $report['start_date']= isset($post['start_date']) ? sanitize_text_field($post['start_date']) : '';
            $index= isset($post['index']) ? sanitize_text_field($post['index']) : -1;
            
            
            $df= erforms_php_date_format_by_js_format('yy-mm-dd').' h:ia'; // PHP compatibel Date format as per the javascript datepicker format
            $dt= DateTime::createFromFormat($df,$report['start_date'].' '.$report['time']); // Coverting Date and Time into DateTime object
            $time_offset= !empty($post['time_offset']) ? HOUR_IN_SECONDS * $post['time_offset'] : 0; // Time offset as per the browser.
            
            if(!empty($dt)){
                if($index>=0){ // Update existing report
                    $schedule = false;
                    // Fetching old report to identify changes in DateTime or recurrence
                    $old_report=  $form['reports'][$index];
                    if(!empty($old_report['start_date'])){ // In case 'start_date' exist for old report.
                        $old_dt = DateTime::createFromFormat($df,$old_report['start_date'].' '.$old_report['time']);
                        /*
                         *  Reschedule report only if 
                         *  1. Recurrence changed.
                         *  2. DateTime.
                         *  3. Old report's datetime is empty or not valid. 
                         */
                        if(empty($old_dt) || ($dt->getTimestamp()+$time_offset) != ($old_dt->getTimestamp()+$time_offset) || $old_report['recurrence']!=$report['recurrence']){
                            $schedule= true;
                        }
                    }
                    else
                    {
                        $schedule= true;
                    }
                    if($schedule){
                        erforms_schedule_report($dt->getTimestamp()+$time_offset,$report['recurrence'],array('form_id'=>$form['id'],'index'=>$index));
                    }
                    $form['reports'][$index]= $report;
                }
                else{ // Inserting new report
                     array_push($form['reports'], $report);
                     $index= count($form['reports'])-1;
                     erforms_schedule_report($dt->getTimestamp()+$time_offset,$report['recurrence'],array('form_id'=>$form['id'],'index'=>$index));
                }
            }
            $form_model->update_form($form); 
        }
        
        public function delete_report($form_id){
            // Delete report action
            $nonce=  isset($_REQUEST['nonce']) ? sanitize_text_field($_REQUEST['nonce']) : '';
            if (!wp_verify_nonce($nonce,'erf-report-delete-nonce')) {
             die('Invalid security token.'); 
            }
            $index= isset($_GET['index']) ? absint($_GET['index']) : -1;
            if($index>=0){
                $form= erforms()->form->get_form($form_id);
                unset($form['reports'][$index]);
                erforms_delete_scheduled_report(array('form_id'=>$form_id,'index'=>$index));
                erforms()->form->update_form($form);
            } 
        }
        
        public function update_report_status($form_id){
            // Edit report status action
            $status= isset($_GET['status']) ? absint($_GET['status']) : null;
            if($status=='0' || $status=='1'){
                $nonce=  isset($_REQUEST['nonce']) ? sanitize_text_field($_REQUEST['nonce']) : '';
                if (!wp_verify_nonce($nonce,'erf-report-edit-nonce')) {
                 die('Invalid security token.'); 
                }
                $index= isset($_GET['index']) ? absint($_GET['index']) : -1;
                if($index>=0){
                    $form= erforms()->form->get_form($form_id);
                    if($form['reports'][$index]){
                        $form['reports'][$index]['active']= $status;
                        erforms()->form->update_form($form);
                    }
                } 
            }
        }
        
        public function run_report_manually($form_id){
            $nonce=  isset($_REQUEST['nonce']) ? sanitize_text_field($_REQUEST['nonce']) : '';
            if (false && !wp_verify_nonce($nonce,'erf-report-run-nonce')) {
             die('Invalid security token.'); 
            }
            $index= isset($_REQUEST['index']) ? absint($_REQUEST['index']) : -1;
            if($index>=0){
                $form= erforms()->form->get_form($form_id);
                erforms()->submission->send_submission_report($form_id,$index);
            }
            
        }
        
}

new ERForms_Dashboard;
