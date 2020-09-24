<?php
/**
 * Form Settings
 *
 * @package    ERForms
 * @author     ERForms
 * @since      1.0.0
 */
class ERForms_Settings {

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'init' ) );
                add_action( 'admin_enqueue_scripts', array( $this, 'enqueues' ) );
	}

	/**
	 *
	 * @since 1.0.0
	 */
	public function init() {
                
		// Check what page we are on
		$page = isset( $_GET['page'] ) ? sanitize_text_field($_GET['page']) : '';
                
		// Only load if we are actually on the builder
		if ( 'erforms-settings' === $page ) {
                    add_action( 'admin_enqueue_scripts', array( $this, 'enqueues' ) );
                    add_action( 'erforms_admin_page',array( $this, 'output') );
		}
	}
        
        /**
	 * Enqueue assets for the overview page.
	 *
	 * @since 1.0.0
	 */
	public function enqueues() {
            // Hook for addons.
            do_action( 'erf_admin_settings_enqueue' );
	}
        
	/**
	 * Load the appropriate files to build the page.
	 *
	 * @since 1.0.0
	 */
	public function output() {
           $options= $this->save_settings(); 
           include 'html/settings.php';
	}
        
        private function save_settings(){ 
            $options_model= erforms()->options;
            $options= $options_model->get_options();
            $_post= wp_unslash($_POST);
            if(isset($_post['erf_save_settings'])){
                $options['rc_site_key'] = sanitize_text_field($_post['rc_site_key']);
                $options['rc_secret_key'] =   sanitize_text_field($_post['rc_secret_key']);
                $upload_dir =   sanitize_text_field($_post['upload_dir']);
                $options['upload_dir'] =   empty($upload_dir) ? 'erf_uploads' : $upload_dir;
                $options['recaptcha_configured'] =   empty($_post['recaptcha_configured']) ? 0 : 1;
                $options['recaptcha_version'] =   absint($_post['recaptcha_version']);
                $options['en_wc_my_account'] =   empty($_post['en_wc_my_account']) ? 0 : 1;
                $options['default_register_url'] =   empty($_post['default_register_url']) ? 0 : absint($_post['default_register_url']);
                $options['after_login_redirect_url']= esc_url_raw($_post['after_login_redirect_url']);
                $options['social_login']= sanitize_text_field($_post['social_login']);
                $payment_methods= !empty($_post['payment_methods']) ? array_map('sanitize_text_field',$_post['payment_methods']) : array();
                $options['payment_methods']= $payment_methods;
                $options['currency']= sanitize_text_field($_post['currency']);
                $options['en_role_redirection'] =   empty($_post['en_role_redirection']) ? 0 : absint($_post['en_role_redirection']);
                $options['en_login_recaptcha'] =   empty($_post['en_login_recaptcha']) ? 0 : absint($_post['en_login_recaptcha']);
                $roles= erforms_wp_roles();
                foreach($roles as $key=>$role){
                    if(!empty($_post[$key.'_login_redirection'])){
                        $options[$key.'_login_redirection']= esc_url_raw($_post[$key.'_login_redirection']);
                    }
                }
                
                $options['en_payment_pending_email'] =   isset($_post['en_payment_pending_email']) ? 1 : 0;
                $options['en_payment_completed_email'] =   isset($_post['en_payment_completed_email']) ? 1 : 0;
                $options['payment_pending_email'] =   isset($_post['payment_pending_email']) ?  wp_kses_post($_post['payment_pending_email']) : '';
                $options['payment_completed_email'] =   isset($_post['payment_completed_email']) ? wp_kses_post($_post['payment_completed_email']) : '';
                $options['note_user_email_sub']= isset($_post['note_user_email_sub']) ? sanitize_text_field($_post['note_user_email_sub']) : '';
                $options['note_user_email_from_name']= isset($_post['note_user_email_from_name']) ? sanitize_text_field($_post['note_user_email_from_name']) : '';
                $options['note_user_email_from']= isset($_post['note_user_email_from']) ? sanitize_text_field($_post['note_user_email_from']) : '';
                $options['en_note_user_email']= isset($_post['en_note_user_email']) ? 1 : 0;
                $options['note_user_email']= isset($_post['note_user_email']) ? wp_kses_post($_post['note_user_email']) : '';
                
                $options['en_pay_completed_admin_email'] = isset($_post['en_pay_completed_admin_email']) ? 1 : 0;
                $options['completed_pay_admin_email_from'] = isset($_post['completed_pay_admin_email_from']) ? sanitize_text_field($_post['completed_pay_admin_email_from']) : '';
                $options['completed_pay_admin_email_from_name'] = isset($_post['completed_pay_admin_email_from_name']) ? sanitize_text_field($_post['completed_pay_admin_email_from_name']) : '';
                $options['completed_pay_admin_email_subject'] = isset($_post['completed_pay_admin_email_subject']) ? sanitize_text_field($_post['completed_pay_admin_email_subject']) : '';
                $options['pay_completed_admin_email'] = isset($_post['pay_completed_admin_email']) ? wp_kses_post($_post['pay_completed_admin_email']) : '';
                $options['completed_pay_admin_email_to']= isset($_post['completed_pay_admin_email_to']) ? sanitize_text_field($_post['completed_pay_admin_email_to']) : '';
                
                // Display settings for login form
                $options['login_layout']= isset($_post['login_layout']) ? sanitize_text_field($_post['login_layout']) : '';
                $options['login_field_style']= isset($_post['login_field_style']) ? sanitize_text_field($_post['login_field_style']) : '';
                $options['login_label_position']= isset($_post['login_label_position']) ? sanitize_text_field($_post['login_label_position']) : '';
                $options['logout_redirection']= isset($_post['logout_redirection']) ? esc_url_raw($_post['logout_redirection']) : '';
                $options['hide_admin_bar'] =   empty($_post['hide_admin_bar']) ? 0 : 1;
                $options['allow_login_from'] =   isset($_post['allow_login_from']) ? sanitize_text_field($_post['allow_login_from']) : 'both';
                
                // Forgot Password
                $options['forgot_pass_email_subject'] = isset($_post['forgot_pass_email_subject']) ? sanitize_text_field($_post['forgot_pass_email_subject']) : '';
                $options['forgot_pass_email'] = isset($_post['forgot_pass_email']) ? wp_kses_post($_post['forgot_pass_email']) : '';
                
                if(isset($_post['gmap_api'])){
                    $options['gmap_api'] = sanitize_text_field($_post['gmap_api']);
                }
                $options= apply_filters('erf_before_save_settings',$options);
                $options_model->save_options($options);
            }
            
            if(isset($_post['savec'])){// Save and Close
                $tab= isset($_post['erf_gs_tab']) ? sanitize_text_field($_post['erf_gs_tab']) : '';
                $url= admin_url('admin.php?page=erforms-settings');
                if(!empty($tab)){
                    $url .= '&tab='.$tab;
                }
                erforms_redirect($url);
                exit;
            }
            
            return $options;
        }
        /**
    * Enqueue assets for the overview page.
    *
    * @since 1.0.0
    */
        
}

new ERForms_Settings;
