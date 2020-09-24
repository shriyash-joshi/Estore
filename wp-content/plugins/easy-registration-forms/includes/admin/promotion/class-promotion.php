<?php

/**
 *
 * @package    ERForms
 * @author     ERForms
 * @since      1.5.7
 */
class ERForms_Promotion {
    
    /**
    * Primary class constructor.
    *
    * @since 1.5.7
    */
    public function __construct() {
        add_action('admin_init', array( $this, 'init' ) );
        add_action('erf_installed', array($this,'set_activation_redirect') );
        add_action('plugins_loaded',array($this,'redirect'));
        add_action('erf_admin_menus',array($this,'menu'),10,2);
    }
    
    /**
    *
    * @since 1.5.7
    */
    public function init() {
        // Check what page we are on
	$page = isset( $_GET['page'] ) ? sanitize_text_field($_GET['page']) : '';
        
        // Only load if we are actually on the builder
        if ( 'erforms-promotion' === $page ) {
              add_action( 'erforms_admin_page',array( $this, 'output') );
        } 
    }
    
   
    /**
    * @since 1.5.7
    */
    public function output() {
        include 'promotion.php';
    }
    
    public function set_activation_redirect(){
        add_option('erf_plugin_do_promotion', true);
    }
    
    public function redirect(){
        if (get_option('erf_plugin_do_promotion', false)) 
        {
            delete_option('erf_plugin_do_promotion');
            wp_redirect( admin_url('admin.php?page=erforms-promotion'));
            exit;
        }
    }
    
    public function menu($instance,$menu_cap){
        add_submenu_page(
                '',
                __( 'Merry Christmas', 'erforms' ),
                __( 'Addons', 'erforms' ),
                $menu_cap,
                'erforms-promotion',
                array($instance, 'admin_page' )
        );
    }
     
}

new ERForms_Promotion();