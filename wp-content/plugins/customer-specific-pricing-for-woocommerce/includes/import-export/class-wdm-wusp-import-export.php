<?php

namespace cspImportExport;

if (!class_exists('WdmWuspImportExport')) {
	/**
	* Class for loading Import and Export functionality.
	*/
	class WdmWuspImportExport {
	
		/**
		* Includes files for import and export functionality.
		*/
		public function __construct() {
			//Include files for Import
			include_once('import/class-wdm-wusp-import.php');

			//Include files for Export
			include_once('export/class-wdm-wusp-export.php');
		}

		/**
		* Loads the js for the import functionality.
		*/
		public function loadUploadWdmCsp() {
			wp_enqueue_script('wdm_csp_upload_js', plugins_url('/js/import-js/wdm-csp-upload.js', dirname(dirname(dirname(__FILE__)))), array('jquery'), CSP_VERSION);

			wp_localize_script(
				'wdm_csp_upload_js',
				'wdm_csp_upload',
				array(
					'admin_ajax_path'     => admin_url('admin-ajax.php'),
					'import_nonce'        => wp_create_nonce('import_nonce'),
					'loading_image_path'  => plugins_url('/images/loading.gif', dirname(dirname(dirname(__FILE__)))),
				)
			);
		}


		/**
		* Makes the instance of export class.
		* Makes the dropdown options for export.
		* If groups plugin is inactive unset group specific pricing from dropdown.
		*/
		public function cspExport() {
			global $cspFunctions;
			?>
			<div class="wrap">
				<?php
					$wdm_export = new cspExport\WdmWuspExport();

					$exportDropdown = array(
						'User' => __('User Specific Pricing', 'customer-specific-pricing-for-woocommerce'),
						'Role' => __('Role Specific Pricing', 'customer-specific-pricing-for-woocommerce'),
						'Group' =>__('Group Specific Pricing', 'customer-specific-pricing-for-woocommerce'),
					);

					if (!$cspFunctions->wdmIsActive('groups/groups.php')) {
						unset($exportDropdown['Group']);
					}

					//Below values are shown in the dropdown of Export page
					$wdm_export->setOptionValuesPair($exportDropdown);
					
					do_action('show_export');
					?>
			</div>
			<?php
		}
		/**
		* Makes the instance of import class.
		* Makes the dropdown options for import.
		* If groups plugin is inactive unset group specific pricing from
		* dropdown.
		*/
		public function cspImport() {
			global $cspFunctions;
			?>
			<div class="wrap">
				<?php
					$wdm_import = new cspImport\WdmWuspImport();

					$importDropdown = array(
						'Wdm_User_Specific_Pricing_Import' => __('User Specific Pricing', 'customer-specific-pricing-for-woocommerce'),
						'Wdm_Role_Specific_Pricing_Import' => __('Role Specific Pricing', 'customer-specific-pricing-for-woocommerce'),
						'Wdm_Group_Specific_Pricing_Import' => __('Group Specific Pricing', 'customer-specific-pricing-for-woocommerce'),
					);

					if (!$cspFunctions->wdmIsActive('groups/groups.php')) {
						unset($importDropdown['Wdm_Group_Specific_Pricing_Import']);
					}

					//Below values are shown in the dropdown of Import page
					$wdm_import->setOptionValuesPair($importDropdown);

					do_action('show_import');
					?>
			</div>
			<?php
		}
	}
}
