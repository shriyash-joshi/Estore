<?php
/**
 *
 * @package    ERForms
 * @author     ERForms
 * @since      1.0.0
 */
class ERForms_Form_Overview {

	/**
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Maybe load overview page.
		add_action( 'admin_init', array( $this, 'init' ) );
	}

	/**
	 * Determing if the user is viewing the overview page, if so, party on.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		// Check what page we are on.
		$page = isset( $_GET['page'] ) ? sanitize_text_field($_GET['page']) : '';

		// Only load if we are actually on the overview page.
		if ( 'erforms-overview' === $page ) {            
			// Load the class that builds the overview table.
			require_once 'class-form-cards.php';
                        
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueues' ) );
			add_action( 'erforms_admin_page',    array( $this, 'output'   ) );

			// Provide hook for addons.
			do_action( 'erforms_overview_init' );
		}
	}

	

	/**
	 * Enqueue assets for the overview page.
	 *
	 * @since 1.0.0
	 */
	public function enqueues() {
            // Hook for addons.
            do_action( 'erf_admin_overview_enqueue' );
            
	}

	/**
	 * Build the output for the overview page.
	 *
	 * @since 1.0.0
	 */
	public function output() {
            $options_model= erforms()->options;
            $options= erforms()->options->get_options();
            if(isset($_POST['erf_consent_allow'])){
                $options['consent_allowed']=1;
                $options_model->save_options($options);
                $email_model= ERForms_Emails::get_instance();
                $email_model->report_usage();
            }
            else if(isset($_POST['erf_consent_disallow'])){
                $options['consent_allowed']=1;
                $options_model->save_options($options);
            }
            
            include 'html/overview.php';
		
	}
}
new ERForms_Form_Overview;