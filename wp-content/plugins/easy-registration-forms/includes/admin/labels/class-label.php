<?php

/**
 * Custom Labels
 *
 * @package    ERForms
 * @author     ERForms
 * @since      1.4.1
 */
class ERForms_Admin_Label {
    
    /**
    * Primary class constructor.
    *
    * @since 1.4.1
    */
    public function __construct() {
           add_action( 'admin_init', array( $this, 'init' ) );
           add_action('erf_admin_submission_enqueue',array($this,'submission_enqueues'));
    }
    
    /**
    *
    * @since 1.4.1
    */
    public function init() {
        // Check what page we are on
	$page = isset( $_GET['page'] ) ? sanitize_text_field($_GET['page']) : '';
        
        // Only load if we are actually on the builder
        if ( 'erforms-labels' === $page ) {
              add_action( 'erforms_admin_page',array( $this, 'output') );
              add_action( 'admin_enqueue_scripts', array( $this, 'enqueues' ) );
        } 
    }
    
    public function submission_enqueues(){
        wp_enqueue_style('erf-labels',ERFORMS_PLUGIN_URL.'assets/admin/css/amsify.suggestags.css');
    }
    
    public function enqueues(){
        wp_enqueue_script('erf-jscolor',ERFORMS_PLUGIN_URL.'assets/admin/js/jscolor.js');
    }
    /**
    * @since 1.4.1
    */
    public function output() {
        $errors= array();
        if(isset($_POST['erf_save_label'])){
            $term= $this->save_label();
            if(is_wp_error($term)){
                $duplicate_error= $term->get_error_message('duplicate_term_name');
                if(empty($duplicate_error)){
                    $duplicate_error= $term->get_error_message('term_exists');
                }
                if(!empty($duplicate_error))
                    array_push($errors,__('Label already in use.','erforms'));
                else
                    array_push($errors,$term->get_error_message());
            }
        }
        $labels= erforms()->label->get_labels();
        include 'html/labels.php';
    }
    
    private function save_label(){
        // Sanitize request data
        $name= sanitize_text_field(wp_unslash($_POST['label_name']));
        $desc= sanitize_text_field(wp_unslash($_POST['label_desc']));
        $color= sanitize_text_field(wp_unslash($_POST['label_color']));
        if(empty($name) || empty($color)){
            return new WP_Error('invalid_req', __("Both Label name and color are required fields.", "erforms"));
        }
        return erforms()->label->save(array('name'=>$name,'desc'=>$desc,'color'=>$color));
    }
}

new ERForms_Admin_Label();