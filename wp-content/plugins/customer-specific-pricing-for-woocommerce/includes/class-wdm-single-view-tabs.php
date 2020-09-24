<?php

namespace SingleView;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

if (!class_exists('WdmShowTabs')) {
	/**
	* Class for showing tabs of CSP.
	*/
	class WdmShowTabs {
	
		 /**
		 * Constructor that Adds the Menu Page action
		 * Loads scripts for import tab.
		 * Display admin notices for import tabs.
		 * Include the files for the tabs of CSP :
		 * Product-pricing,import-export,category-pricing
		 */
		public function __construct() {
			global $singleView, $categoryPricing;
			global $importExport;

			add_action('admin_init', array($this, 'loadUploadWdmCsp'));
			add_action('admin_init', array($this, 'addPluginRowMeta'));
			add_action('admin_menu', array($this, 'cspPageInit'), 99);

			if (is_admin()) {
				include_once 'single-view/class-wdm-single-view.php';
				$singleView = new \cspSingleView\WdmSingleView();
				//including file Import/Export functionality
				include_once 'import-export/class-wdm-wusp-import-export.php';
				$importExport = new \cspImportExport\WdmWuspImportExport();

				include_once 'category-pricing/class-wdm-wusp-category-pricing.php';
				$categoryPricing = new \cspCategoryPricing\WdmWuspCategoryPricing();

				include_once 'settings/class-wdm-csp-general-settings.php';
				$generalSettings = new \CSPGenSettings\WdmCSPGeneralSettings();

				include_once 'feedback/class-wdm-csp-feedback.php';
				$generalSettings = new \CSPFeedbackTab\WdmCSPFeedbackTab();

				include_once 'cart-discount/class-wdm-csp-cart-discount.php';
				$generalSettings = new \CSPCartDiscount\WdmCSPCartDiscount();
				include_once 'promotion/init.php';
			}
		}

		/**
		* Load the Scripts for the import tab.
		* Gets the current tab , if it is import enqueue scripts.
		*/
		public function loadUploadWdmCsp() {
			$currentTab = $this->getCurrentTab();

			if ('import' == $currentTab) {
				wp_enqueue_script(
					'wdm_csp_import_js',
					plugins_url('/js/import-js/wdm-csp-import.js', dirname(__FILE__)),
					array('jquery'),
					CSP_VERSION
				);

				wp_localize_script(
					'wdm_csp_import_js',
					'wdm_csp_import',
					array(
						'admin_ajax_path'     => admin_url('admin-ajax.php'),
						'import_nonce'        => wp_create_nonce('import_nonce'),
						'header_text'         => __('Import Pricing Rules', 'customer-specific-pricing-for-woocommerce'),
						'loading_image_path'  => plugins_url('/images/loading .gif', dirname(__FILE__)),
						'loading_text'        => __('Importing . .(Please do not close this window until the  import is finished)', 'customer-specific-pricing-for-woocommerce'),
						'import_successfull'  => __('File Imported Successfully', 'customer-specific-pricing-for-woocommerce'),
						'total_no_of_rows'    => __('Total number of rows found ', 'customer-specific-pricing-for-woocommerce'),
						'total_insertion'     => __('. Total number of rows inserted ', 'customer-specific-pricing-for-woocommerce'),
						'total_updated'       => __(', total number of rows updated ', 'customer-specific-pricing-for-woocommerce'),
						'total_skkiped'       => __(', and total number of rows skipped ', 'customer-specific-pricing-for-woocommerce'),
						'import_page_url'     => menu_page_url('customer_specific_pricing_single_view', false) . '&tabie=import',
						'templates_url'       => plugins_url('/templates/', dirname(__FILE__)),
						'user_specific_sample'  => __('User Specific Sample', 'customer-specific-pricing-for-woocommerce'),
						'role_specific_sample'  => __('Role Specific Sample', 'customer-specific-pricing-for-woocommerce'),
						'group_specific_sample' => __('Group Specific Sample', 'customer-specific-pricing-for-woocommerce'),
						/* translators: HelpContext*/
						'HelpContext'			=> sprintf(__('%1$sRecords in a Batch%2$s : specifies number of records to process at a time. %3$s %4$sSimultaneous Batches%5$s : specifies number of such batches to process at the same time.', 'customer-specific-pricing-for-woocommerce'), '<b>', '</b>', '<br>', '<b>', '</b>'),
						)
				);
			}
		}

		/**
		 * Function To add menu page and sub menu page for csp
		 *
		 * @return [void]
		 */
		public function cspPageInit() {
			global $singleViewPage;
			$pluginVersion  = defined('CSP_VERSION')?CSP_VERSION:'';
			$singleViewPage = add_menu_page('CSP Administration', 'CSP', 'manage_options', 'customer_specific_pricing_single_view', array(
				$this,
				'singleViewTabs'
			), plugins_url('images/usp_icon.png', dirname(__FILE__)), 56);
			wp_register_style('csp-common-single-view', plugins_url('single-view/assets/css/wdm-common-single-view.css', __FILE__), array(), $pluginVersion);
		}

		/**
		 * Shows the various tabs in CSP.
		 *
		 * @param string $current current tab name.
		 * @return [void]
		 */
		public function singleViewShowTabs( $current = 'import') {
			$tabs = array(
				'product_pricing' => __('Product Pricing', 'customer-specific-pricing-for-woocommerce'),
				'search_by' => __('Search By & Delete', 'customer-specific-pricing-for-woocommerce'),
				'category_pricing' => __('Category Pricing', 'customer-specific-pricing-for-woocommerce'),
				'cart_discounts' => __('Cart Discounts', 'customer-specific-pricing-for-woocommerce'),
				'import' => __('Import', 'customer-specific-pricing-for-woocommerce'),
				'export' => __('Export', 'customer-specific-pricing-for-woocommerce'),
				'csp_settings' => __('Settings', 'customer-specific-pricing-for-woocommerce'),
				'csp_feedback' => __('Feedback', 'customer-specific-pricing-for-woocommerce'),
				'promotions_tab' => __('What\'s New', 'customer-specific-pricing-for-woocommerce'),
			);
			?>
			<h2 class="nav-tab-wrapper">
			<?php
			foreach ($tabs as $tab => $name) {
				// echo $name;
				$class = ( $tab == $current ) ? ' nav-tab-active' : '';
				echo '<a class="nav-tab' . esc_attr($class) . '" href="admin.php?page=customer_specific_pricing_single_view&tabie=' . esc_attr($tab) . '">' .
				esc_html($name) . '</a>';
			}
			?>
			</h2>
			<?php
		}

		/**
		* Returns the current tab.
		 *
		* @return string $currentTab current tab.
		*/
		public function getCurrentTab() {

			global $pagenow;
			static $currentTab = null;

			if (null!== $currentTab) {
				return $currentTab;
			}


			$pageRequested = isset($_GET['page'])?sanitize_text_field($_GET['page']):'';
			if ('admin.php'== $pagenow && 'customer_specific_pricing_single_view'== $pageRequested) {
				if (isset($_GET['tabie'])) {
					$currentTab = sanitize_text_field($_GET['tabie']);
					return $currentTab;
				}

				if (isset($_GET['tab'])) {
					$currentTab = 'product_pricing';
					return $currentTab;
				}

				$currentTab = 'product_pricing';
				return $currentTab;
			}

			$currentTab = false;
			return $currentTab;
		}
		/**
		 * [importExportTabs Function to navigate through Import/Export tabs in the import/export CSP sub menu page]
		 *
		 * @return [void]
		 */
		public function singleViewTabs() {
			global  $singleView, $importExport, $categoryPricing;

			$currentTab = $this->getCurrentTab();
			
			if ( false === $currentTab) {
				return;
			}

			//Display the poll link as a floating side action button
			$this->getFloatingSideButtonHtml();

			?>
			<div class="wrap">
				<?php
					$this->singleViewShowTabs($currentTab);
				?>
				<div id="poststuffIE">
				<?php
				switch ($currentTab) {
					case 'import':
						$importExport->cspImport();
						break;
					case 'export':
						$importExport->cspExport();
						break;
					case 'product_pricing':
						$singleView->cspSingleView();
						break;
					case 'category_pricing':
						$categoryPricing->cspShowCategoryPricing();
						break;
					case 'search_by':
						do_action('csp_single_view_search_settings');
						break;
					case 'promotions_tab':
						do_action('csp_single_view_promotions');
						break;
					case 'csp_settings':
						do_action('csp_general_settings');
						break;
					case 'csp_feedback':
						do_action('csp_feedback_tab');
						break;
					case 'cart_discounts': // V-4.3.0
						do_action('csp_cart_discounts_tab');
						break;
				}//end of switch
				?>
				</div>
			</div>
			<?php
		} // end of function importExportTabs

		/**
		 * This method is hooked to admin edit hook & adds extra meta links to the
		 * plugin listing on the admin dashboard.
		 *
		 * @since 4.4.3
		 * @return void
		 */
		public function addPluginRowMeta() {
			include_once CSP_PLUGIN_URL . '/includes/class-wdm-plugin-links.php';
			add_filter( 'plugin_row_meta', array( 'WisdmPluginLinks\Links','cspPluginRowMeta'), 10, 2 );
		}


		/**
		 * Displays the CSP Poll link
		 * 
		 * @since 4.4.3.1
		 */
		public function getFloatingSideButtonHtml() {
			wp_enqueue_style('csp-common-single-view');
			$message = __('1-Question Feedback', 'customer-specific-pricing-for-woocommerce');
			?>
			<div class="csp-poll-bar">
				<a href="https://surveys.hotjar.com/s?siteId=769948&surveyId=153645" class="csp-poll" target="_blank"> <?php esc_html_e($message); ?></a> 
			</div>
			<?php
		}

	} //end of class
} //end of if class exists
