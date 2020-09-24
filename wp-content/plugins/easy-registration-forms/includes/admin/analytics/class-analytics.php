<?php
/**
 * Form Settings
 *
 * @package    ERForms
 * @author     ERForms
 * @since      1.0.0
 */
class ERForms_Analytics {

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
		if ( 'erforms-analytics' === $page ) {
                    add_action( 'admin_enqueue_scripts', array( $this, 'enqueues' ) );
                    add_action( 'erforms_admin_page',array( $this, 'output') );
		}
	}
        
        /**
	 * Enqueue assets for the analytics page.
	 *
	 * @since 1.0.0
	 */
	public function enqueues() {
            wp_enqueue_script('google_charts', 'https://www.gstatic.com/charts/loader.js');
            // Hook for addons.
            do_action( 'erf_admin_analytics_enqueue' );
	}
        
	/**
	 * Load the appropriate files to build the page.
	 *
	 * @since 1.0.0
	 */
	public function output() {
            $period= isset($_GET['period']) ? absint($_GET['period']) : 7;
            $forms= erforms()->form->get();
            $temp= 0;
            if(!empty($forms))
            {
                $temp= $forms[0]->ID;
            }
            $form_id= isset($_GET['form_id']) ? absint($_GET['form_id']) : $temp;
            $chart_data= erforms()->submission->submissions_data_for_chart($form_id,$period);
            
            include 'html/analytics.php';
	}
        
}
new ERForms_Analytics;