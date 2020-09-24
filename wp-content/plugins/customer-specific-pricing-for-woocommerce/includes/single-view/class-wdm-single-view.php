<?php

namespace cspSingleView;

if (!class_exists('WdmSingleView')) {
	/**
	* Class for the Product-pricing tab.
	* Define and display tabs for set-rule and rule-log.
	* Includes the files for the functioning of the settings involved.
	* Enqueues required scripts and styles.
	*/
	class WdmSingleView {
	

		private $csp_single_view_menu_slug = 'customer_specific_pricing_single_view';
		private $general_settings_key = 'product_pricing';
		private $search_settings_key = 'search_settings';
		private $query_log_setting_key = 'rule_log';
		private $csp_settings_tabs = array();

		/**
		* Action for Defining the tabs for set rules and rule log.
		* Action for enqueuing required scripts and styles.
		* Includes files for general/search/query-log settings.
		*/
		public function __construct() {
			add_action('init', array($this, 'wdmDefineTabs'), 99);
			add_action('admin_enqueue_scripts', array($this, 'enqueueScripts'));
			//Include files for General settings
			include_once('settings/general.php');
			new general\WdmSingleViewGeneral();
			//Include files for Search settings
			include_once('settings/search_setting.php');
			new searchSetting\WdmSingleViewSearch();
			//Include files for Query-Log settings
			include_once('settings/query_log_setting.php');
			new queryLogSetting\WdmSingleViewQueryLog();
		}

		/**
		* Enqueue the required scripts for the admin side.
		*/
		public function enqueueScripts() {
			wp_enqueue_script('wdm_csp_functions', plugins_url('/js/wdm-csp-functions.js', dirname(dirname(__FILE__))), array( 'jquery' ), CSP_VERSION);
			wp_localize_script(
				'wdm_csp_functions',
				'wdm_csp_function_object',
				array(
				'decimal_separator' => wc_get_price_decimal_separator(),
				'thousand_separator' => wc_get_price_thousand_separator(),
				'decimals' => wc_get_price_decimals(),
				'price_format' => get_woocommerce_price_format(),
				'currency_symbol' => get_woocommerce_currency_symbol(),
				)
			);
		}
		/**
		* Define the tabs for set rules and rule log.
		*/
		public function wdmDefineTabs() {
			$this->csp_settings_tabs[$this->general_settings_key] = __('Set Rules', 'customer-specific-pricing-for-woocommerce');
			$this->csp_settings_tabs[$this->query_log_setting_key] = __('Rule Log', 'customer-specific-pricing-for-woocommerce');
		}

		/**
		* Gets the tab of Product-Pricing for CSP.
		* Displays the optional tab of set-rule and rule log inside that.
		*/
		public function cspSingleView() {
			$tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : $this->general_settings_key;
			?>
			<div class="wrap">
			<h3 class = 'import-export-header'><?php esc_html_e('Product Pricing', 'customer-specific-pricing-for-woocommerce'); ?></h3>
			<?php $this->getOptionsTab(); ?>
			<?php do_action('csp_single_view_' . $tab); ?>
			</div>
			<?php
		}

		/*        private function getOptionsTab()
		{
		$current_tab = isset($_GET['tab']) ? $_GET['tab'] : $this->general_settings_key;

		screen_icon();
		echo '<h2 class="nav-tab-wrapper">';
		foreach ($this->csp_settings_tabs as $tab_key => $tab_caption) {
			$active = $current_tab == $tab_key ? 'nav-tab-active' : '';
			echo '<a class="nav-tab ' . $active . '" href="?page=' . $this->csp_single_view_menu_slug . '&tab=' . $tab_key . '">' . $tab_caption . '</a>';
		}
		echo '</h2>';
		}
		*/

		/**
		* Display optional tab of Rule-log or set-rule in Product-Pricing
		* tab.
		*/
		private function getOptionsTab() {
			$current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : $this->general_settings_key;

			?>
			<div>
			<ul class="subsubsub hrline">
				<?php

				foreach ($this->csp_settings_tabs as $tab_key => $tab_caption) {
					$active = $current_tab == $tab_key ? 'nav-tab-active' : '';
					if ('rule_log'==$tab_key) {
						echo '<li><a class="' . esc_attr($active) . '" href="?page=' . esc_attr($this->csp_single_view_menu_slug) . '&tab=' . esc_attr($tab_key) . '">' . esc_html($tab_caption) . '</a></li>';
					} else {
						echo '<li><a class="' . esc_attr($active) . '" href="?page=' . esc_attr($this->csp_single_view_menu_slug) . '&tab=' . esc_attr($tab_key) . '">' . esc_html($tab_caption) . '</a> |</li>';
					}
				}
				?>
			</ul>
			</div>
			<hr/>
			<?php
		}
	}
}
