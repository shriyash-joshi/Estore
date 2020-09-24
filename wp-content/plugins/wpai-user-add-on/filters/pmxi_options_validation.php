<?php

function pmui_pmxi_options_validation($errors, $post, $importObj){

	if ( ! empty($post['pmui']['import_users']) and $post['wizard_type'] != 'matching' ) { 
		
		if ( '' == $post['pmui']['login'] ) {
			$errors->add('form-validation', __('`Login` must be specified', 'wp_all_import_user_add_on'));
		}
		if ( '' == $post['pmui']['email'] ) {
			$errors->add('form-validation', __('`Email` must be specified', 'wp_all_import_user_add_on'));
		}				

	} elseif ( ! empty($post['pmsci_customer']['import_customers']) and $post['wizard_type'] != 'matching' ) {

		if ( '' == $post['pmsci_customer']['login'] ) {
			$errors->add('form-validation', __('`Login` must be specified', 'wp_all_import_user_add_on'));
		}
		if ( '' == $post['pmsci_customer']['email'] ) {
			$errors->add('form-validation', __('`Email` must be specified', 'wp_all_import_user_add_on'));
		}	

	}

	return $errors;
}

?>