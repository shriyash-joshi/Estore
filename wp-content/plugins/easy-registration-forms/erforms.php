<?php
/**
 * Plugin Name: Easy Registration Forms
 * Plugin URI:  http://easyregistrationforms.com
 * Description: User friendly WordPress form plugin. Use Drag & Drop form builder to create your forms.
 * Author:      EasyRegistrationForms
 * Version:     2.0.8
 * Text Domain: erforms
 * Domain Path: /languages
 *
 *
 * @package    ERForms
 * @author     ERForms
 * @since      1.0.0
 */
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Don't allow multiple versions to be active
if (!class_exists('ERForms')) {

    /**
     * Main class.
     *
     * @since 1.0.0
     *
     * @package ERForms
     */
    final class ERForms {

        /**
         * One is the loneliest number that you'll ever do.
         *
         * @since 1.0.0
         *
         * @var object
         */
        private static $instance;

        /**
         * Plugin version.
         *
         * @since 1.0.0
         *
         * @var string
         */
        public $version = '2.0.8';

        /**
         * The form data handler instance.
         *
         * @since 1.0.0
         *
         * @var object ERForms_Form_Handler
         */
        public $form;

        /**
         * The front-end instance.
         *
         * @since 1.0.0
         *
         * @var object ERForms_Frontend
         */
        public $frontend;

        /**
         * The process instance.
         *
         * @since 1.0.0
         *
         * @var object ERForms_Submission_Entry
         */
        public $submission;

        /**
         * The smart tags instance.
         *
         * @since 1.0.0
         *
         * @var object
         */
        public $errors= array();
        
        /**
         * Set global options
         */
        public $options;
        
        /*
         * User model
         */
        public $user;
        
        public $plan;
        
        public $label;
        
        public $initial_login_status; // Stores login status before proceeding with the request
        
        /*
         * Holds enabled extension names
         */
        public $extensions= array();
        
        /**
         * Main Instance.
         *
         * Insures that only one instance of ERForms exists in memory at any one
         * time.
         *
         * @since 1.0.0
         *
         * @return ERForms
         */
        public static function instance() {

            if (!isset(self::$instance) && !( self::$instance instanceof ERForms )) {
                self::$instance = new ERForms;
                self::$instance->constants();
                self::$instance->includes();
                
                register_activation_hook(__FILE__, array(self::$instance, 'activation'));
                register_deactivation_hook(__FILE__, array(self::$instance, 'deactivation'));
                add_action('plugins_loaded', array(self::$instance, 'set_common_objects'));
            }
            return self::$instance;
        }
        
        /*
         * Invoked on activation
         */
        public function activation()
        {
            if (is_multisite()) { 
                global $wpdb;
                foreach ($wpdb->get_col("SELECT blog_id FROM $wpdb->blogs") as $blog_id) {
                    switch_to_blog($blog_id);
                    ERForms_Options::create_default_options();
                    restore_current_blog();
                } 

            } else {
                ERForms_Options::create_default_options();
            }
            do_action( 'erf_installed' );

        }
        
        /*
         * Invoked on Deactivation
         */
        public function deactivation(){
            wp_clear_scheduled_hook('erf_submission_report');
        }

        /**
         * Setup plugin constants.
         *
         * @since 1.0.0
         */
        private function constants() {
            // Plugin version.
            if (!defined('ERFORMS_VERSION')) {
                define('ERFORMS_VERSION', $this->version);
            }

            // Plugin Folder Path.
            if (!defined('ERFORMS_PLUGIN_DIR')) {
                define('ERFORMS_PLUGIN_DIR', plugin_dir_path(__FILE__));
            }

            // Plugin Folder URL.
            if (!defined('ERFORMS_PLUGIN_URL')) {
                define('ERFORMS_PLUGIN_URL', plugin_dir_url(__FILE__));
            }

            // Plugin Root File.
            if (!defined('ERFORMS_PLUGIN_FILE')) {
                define('ERFORMS_PLUGIN_FILE', __FILE__);
            }
            
            // Forms Post Type
            if (!defined('ERFORMS_FORM_POST_TYPE')) {
                define('ERFORMS_FORM_POST_TYPE', 'erforms');
            }
            
             // Payment Status
            if (!defined('ERFORMS_COMPLETED')) {
                define('ERFORMS_COMPLETED', 'completed');
            }
            if (!defined('ERFORMS_PENDING')) {
                define('ERFORMS_PENDING', 'pending');
            }
            if (!defined('ERFORMS_HOLD')) {
                define('ERFORMS_HOLD', 'hold');
            }
            if (!defined('ERFORMS_DECLINED')) {
                define('ERFORMS_DECLINED', 'declined');
            }
            if (!defined('ERFORMS_REFUNDED')) {
                define('ERFORMS_REFUNDED', 'refunded');
            }
            if (!defined('ERFORMS_CANCELED')) {
                define('ERFORMS_CANCELED', 'canceled');
            }
        }

        /**
         * Loads the plugin language files.
         *
         * @since 1.0.0
         */
        public function load_textdomain() {
            load_plugin_textdomain('erforms', false, dirname(plugin_basename(__FILE__)) . '/languages/');
        }
        
     
        /**
         * Include files.
         *
         * @since 1.0.0
         */
        private function includes() {

            // Global includes.
             require_once ERFORMS_PLUGIN_DIR . 'includes/functions.php';
             require_once ERFORMS_PLUGIN_DIR . 'includes/class-install.php';
             require_once ERFORMS_PLUGIN_DIR . 'includes/class-post.php';
             require_once ERFORMS_PLUGIN_DIR . 'includes/class-term.php';
             require_once ERFORMS_PLUGIN_DIR . 'includes/class-form.php';
             require_once ERFORMS_PLUGIN_DIR . 'includes/class-submission.php';
             require_once ERFORMS_PLUGIN_DIR . 'includes/class-frontend.php';
             require_once ERFORMS_PLUGIN_DIR . 'includes/class-validator.php';
             require_once ERFORMS_PLUGIN_DIR . 'includes/class-validation.php';
             require_once ERFORMS_PLUGIN_DIR . 'includes/class-user.php';
             require_once ERFORMS_PLUGIN_DIR . 'includes/class-options.php';
             require_once ERFORMS_PLUGIN_DIR . 'includes/class-form-widget.php';
             require_once ERFORMS_PLUGIN_DIR . 'includes/class-login-widget.php';
             require_once ERFORMS_PLUGIN_DIR . 'includes/class-emails.php';
             require_once ERFORMS_PLUGIN_DIR . 'includes/class-plan.php';
             require_once ERFORMS_PLUGIN_DIR . 'includes/class-payment.php';
             require_once ERFORMS_PLUGIN_DIR . 'includes/class-submission-export.php';
             require_once ERFORMS_PLUGIN_DIR . 'includes/class-submission-formatter.php';
             require_once ERFORMS_PLUGIN_DIR . 'includes/class-do-actions.php';
             require_once ERFORMS_PLUGIN_DIR . 'includes/class-wc.php';
             require_once ERFORMS_PLUGIN_DIR . 'includes/class-label.php';
             require_once ERFORMS_PLUGIN_DIR . 'includes/class-form-render.php';
             
             if( is_admin()){
                require_once ERFORMS_PLUGIN_DIR . 'includes/admin/class-list-table.php';
                require_once ERFORMS_PLUGIN_DIR . 'includes/admin/class-list-cards.php';
                require_once ERFORMS_PLUGIN_DIR . 'includes/admin/class-admin.php';
                require_once ERFORMS_PLUGIN_DIR . 'includes/admin/help/class-help.php';
                require_once ERFORMS_PLUGIN_DIR . 'includes/admin/addon/class-addon.php';
                require_once ERFORMS_PLUGIN_DIR . 'includes/admin/plan/class-plan.php';
                require_once ERFORMS_PLUGIN_DIR . 'includes/admin/overview/class-form-overview.php';
                require_once ERFORMS_PLUGIN_DIR . 'includes/admin/form/class-form-dashboard.php';
                require_once ERFORMS_PLUGIN_DIR . 'includes/admin/settings/class-settings.php';
                require_once ERFORMS_PLUGIN_DIR . 'includes/admin/submission/class-submission.php';
                require_once ERFORMS_PLUGIN_DIR . 'includes/admin/analytics/class-analytics.php';
                require_once ERFORMS_PLUGIN_DIR . 'includes/admin/labels/class-label.php';
                require_once ERFORMS_PLUGIN_DIR . 'includes/admin/tools/class-tools.php';
             }
        }

        /**
         * Setup common objects.
         *
         * @since 1.0.0
         */
        public function set_common_objects() {
            $this->load_textdomain();
            // Global objects.
            $this->options = ERForms_Options::instance();  
            $this->form = ERForms_Form::get_instance();
            $this->frontend   = ERForms_Frontend::get_instance();
            $this->submission    = ERForms_Submission::get_instance();
            $this->user    =  ERForms_User::get_instance();
            $this->plan  = ERForms_Plan::get_instance();
            $this->label= ERForms_Label::get_instance();
            $this->initial_login_status = is_user_logged_in();
            // Hook now that all the stuff is loaded.
            do_action('erforms_loaded');
        }

    }

    /**
     * Returns one instance
     *
     * @since 1.0.0
     * @return object
     */
    function erforms() {
        return ERForms::instance();
    }

    erforms();
} // End if().
