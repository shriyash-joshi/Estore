<?php
/**
 *
 * @package    ERForms
 * @author     ERForms
 * @since      1.0.0
 */
class ERForms_Help {

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
		if('erforms-field-shortcodes' === $page){
                    add_action('admin_enqueue_scripts',array( $this,'enqueues'));
                    add_action('erforms_admin_page',array( $this,'field_shortcodes'));
                }
	}

	public function enqueues() {
            wp_enqueue_script('jquery-ui-tabs');
            wp_enqueue_style('erf-admin-style');
	}

        
        public function field_shortcodes(){
            $form_id = isset($_GET['form_id']) ? sanitize_text_field($_GET['form_id']) : 0;
            $form = erforms()->form->get_form($form_id);
            include 'html/field_shortcodes.php';
        }
}
new ERForms_Help;
