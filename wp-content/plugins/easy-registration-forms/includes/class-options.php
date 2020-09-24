<?php

/**
 * Options handler
 *
 *
 * @package    ERForms
 * @author     ERForms
 * @since      1.0.0
 */
class ERForms_Options {
    
    private static $instance;
    
    private function __construct() {
    }
    
    public static function instance() {

        if (!isset(self::$instance) && !( self::$instance instanceof ERForms_Options )) {
            self::$instance = new ERForms_Options;
        }
        return self::$instance;
    }
        
        
    public function save_options($options){
        update_option('erf_gsettings',$options);
    }
    
    public function get_options(){
        $options= get_option('erf_gsettings');
        $default_options= self::get_default_options();
        return wp_parse_args($options,$default_options);
    }
    
    public static function get_default_options(){
        $options= array('rc_site_key'=>'','rc_secret_key'=>'','after_login_redirect_url'=>'','preview_page'=>0,
                            'recaptcha_configured'=>0,'default_register_url'=>0,'payment_methods'=>array(),
                            'send_offline_email'=>0,'currency'=>'USD','consent_allowed'=>2,'en_role_redirection'=>0,'social_login'=>'','en_wc_my_account'=>1,
                            'upload_dir'=>'erf_uploads','offline_email'=>'','payment_pending_email'=>__('Hi {{display_name}}, <br><br> Your payment is pending. Here are the payment details: <br>{{payment_details}}','erforms'),
                            'payment_completed_email'=>__('Hi {{display_name}}, <br><br> Your payment is completed. Here are the payment details: <br>{{payment_details}}','erforms'),
                            'en_payment_pending_email'=>1,'en_payment_completed_email'=>1,'pending_pay_email_subject'=>__('Payment Pending','erforms'),
                            'completed_pay_email_subject'=>__('Payment Completed','erforms'),'pending_pay_email_from'=>'',
                            'pending_pay_email_from_name'=>'','offline_email_subject'=>__('Offline Payment','erforms'),
                            'completed_pay_email_from'=>'','completed_pay_email_from_name'=>'','en_note_user_email'=>1,
                            'note_user_email_subject'=>__('Submission note added.','erforms'),'note_user_email'=>'Hello {{display_name}}, <br><br> {{message}}','note_user_email_sub'=>__('Note added','erforms'),'note_user_email_from_name'=>'','note_user_email_from'=>'',
                            'completed_pay_admin_email_subject'=>__('Submission Payment Completed','erforms'),'en_pay_completed_admin_email'=>1,'completed_pay_admin_email_from'=>'',
                            'completed_pay_admin_email_from_name'=>'','pay_completed_admin_email'=>__('Hi Admin, <br><br> Payment completed. Here are the submission details: <br>{{REGISTRATION_DATA}}','erforms'),
                            'completed_pay_admin_email_to'=>'','login_layout'=>'one-column','login_field_style'=>'rounded-corner','login_label_position'=>'top','en_login_recaptcha'=>0,'logout_redirection'=>'',
                            'hide_admin_bar'=>0,'allow_login_from'=>'both','gmap_api'=>'','recaptcha_version'=>2,'forgot_pass_email_subject'=>sprintf( __( '[%s] Password Reset' ), erforms_site_name() ),'forgot_pass_email'=>erforms_forgot_pass_email_content());
        // consent_allowed=2 means user has not allowed or disallowed yet.
        $options= apply_filters('erf_default_global_options',$options);
        return $options;
    }
    
    public static function create_default_options(){
        $options= self::get_default_options();
        // Role based redirection options
        $roles= erforms_wp_roles();
        foreach($roles as $key=>$role){
            $options[$key.'_login_redirection']= '';
        }
        
        $global_settings= get_option('erf_gsettings');
        if(empty($global_settings)){
            $global_settings= $options;
        }
        else{
            foreach($options as $key=>$default){
                if(!isset($global_settings[$key])){
                    $global_settings[$key]= $default;
                }
            }
        }
        
        update_option('erf_gsettings',$global_settings);
        
        self::form_preview_check();
    }
    
    /**
     * Check if preview page exists, if not create it.
     *
     * @since 1.1.9
     */
    private static function form_preview_check() {
            if (!is_admin()){
                    return;
            }
            $options= get_option('erf_gsettings');
            // Verify page exits
            $preview = $options['preview_page'];

            if ($preview) {
                $preview_page = get_post($preview);
                // Check to see if the visibility has been changed, if so correct it
                if ( ! empty( $preview_page ) && 'private' !== $preview_page->post_status ) {
                        $preview_page->post_status = 'private';
                        wp_update_post($preview_page);
                        return;
                } elseif (!empty( $preview_page ) ) {
                        return;
                }
            }

            // Create the custom preview page
            $content = '<p>' . __( 'This is the ERForms internal page.', 'erforms' ) . '</p>';
            $content .= '<p>' . __( 'The page is set to private, so it is not publicly accessible. Please do not delete this page :) .', 'erforms' ) . '</p>';
            $content .= ' [erforms_preview] ';
            $args    = array(
                    'post_type'      => 'page',
                    'post_name'      => 'erforms-preview',
                    'post_author'    => 1,
                    'post_title'     => __( 'ERForms Preview', 'erforms' ),
                    'post_status'    => 'private',
                    'post_content'   => $content,
                    'comment_status' => 'closed',
            );

            $id = wp_insert_post( $args );
            if ($id) {
                $options['preview_page']= $id;
                update_option('erf_gsettings',$options);
            }
    }
}
