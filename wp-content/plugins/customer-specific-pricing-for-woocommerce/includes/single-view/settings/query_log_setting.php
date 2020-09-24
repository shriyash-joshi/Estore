<?php

namespace cspSingleView\queryLogSetting;

if (! class_exists('WdmSingleViewQueryLog')) {

	/**
	* This class controls actions of rule log tab.
	*/
	class WdmSingleViewQueryLog {
	
		/**
		* Action for rule log tab actions.
		*/
		public function __construct() {
			add_action('csp_single_view_rule_log', array( $this, 'queryLogSettingsCallback' ));
		}

		/**
		* Displays the rules set.
		* Prepares the data to be sent to js.
		* Displays the error messages.
		*/
		public function queryLogSettingsCallback() {
			global $wpdb, $ruleManager;
			$activeRulesTab  = '';
			$safeToDeleteTab = '';
			$activeCondition = '';
			if (isset($_GET[ 'subtab' ]) && ( 'safe_to_delete'== $_GET[ 'subtab' ] )) {
				$safeToDeleteTab = 'current';
				$activeCondition = 0;
			} else {
				$activeRulesTab  = 'current';
				$activeCondition = 1;
			}
			?> 
			<div class="wdm-clear">
			</div>
			<div class = "rule-vertical-space">
				<ul class="subsubsub">
					<li>
						<a href="admin.php?page=customer_specific_pricing_single_view&tab=rule_log" class="<?php echo esc_attr($activeRulesTab); ?>"><?php esc_html_e('Active', 'customer-specific-pricing-for-woocommerce'); ?></a> |
					</li>
					<li>
					<a href="admin.php?page=customer_specific_pricing_single_view&tab=rule_log&subtab=safe_to_delete" class="<?php echo esc_attr($safeToDeleteTab); ?>"><?php esc_html_e('Inactive', 'customer-specific-pricing-for-woocommerce'); ?></a>
					</li>
				</ul>
			</div> 
			

			<?php
			self::enqueueScript();

			$query_log_result = $wpdb->get_results($wpdb->prepare("SELECT rule_id, rule_id as rule_number, rule_title, rule_type, DATE_FORMAT( `rule_creation_time` ,  '%%d-%%M-%%Y %%k:%%i:%%s') AS  'rule_time', DATE_FORMAT(  `rule_modification_time` ,  '%%d-%%M-%%Y %%k:%%i:%%s' ), active FROM " . $wpdb->prefix . 'wusp_rules WHERE active = %d ORDER BY  `rule_time` DESC', $activeCondition), ARRAY_N);

			foreach ($query_log_result as $key => $res) {
				if ('Customer' == $res[3]) {
					$res[3] = __('Customer', 'customer-specific-pricing-for-woocommerce');
				} elseif ('Role' == $res[3] ) {
					$res[3] = __('Role', 'customer-specific-pricing-for-woocommerce');
				} elseif ( 'Group'==$res[3] ) {
					$res[3] = __('Group', 'customer-specific-pricing-for-woocommerce');
				}
				$query_log_result[$key] = $res;
			}

			if (count($query_log_result) > 0) {
				$titles = array(
					array( 'title' => '<input name="select_all" value="1" type="checkbox">' ),
					array( 'title' => __('Rule No.', 'customer-specific-pricing-for-woocommerce') ),
					array( 'title' => __('Rule Title', 'customer-specific-pricing-for-woocommerce') ),
					array( 'title' => __('Rule Type', 'customer-specific-pricing-for-woocommerce') ),
					array( 'title' => __('Rule Creation Time', 'customer-specific-pricing-for-woocommerce') ),
					array( 'title' => __('Rule Modification Time', 'customer-specific-pricing-for-woocommerce') ),
					array( 'title' => __('Active', 'customer-specific-pricing-for-woocommerce') ),
				);

				$array_to_be_sent = array( 'admin_ajax_path'     => admin_url('admin-ajax.php'),
					'loading_image_path' => plugins_url('/images/loading .gif', dirname(dirname(dirname(__FILE__)))),
					'title_names'        => $titles,
					'query_log_link'     => admin_url('/admin.php?page=customer_specific_pricing_single_view&tab=product_pricing&query_log='),
					'data'               => $query_log_result,
					'error_message'      => __('Please, select some log.', 'customer-specific-pricing-for-woocommerce'),
					'error_log_empty'    => __('Please, Save some Rule log. Rule log list empty.', 'customer-specific-pricing-for-woocommerce'),
					'confirm_msg'        => __('Are you sure, You want to delete this rule?', 'customer-specific-pricing-for-woocommerce'),
					'length_menu' => __('Show _MENU_ entries', 'customer-specific-pricing-for-woocommerce'),
					'showing_info'=> __('Showing _START_ to _END_ of _TOTAL_ entries', 'customer-specific-pricing-for-woocommerce'),
					'empty_table' => __('No data available in table', 'customer-specific-pricing-for-woocommerce'),
					'info_empty'=> __('Showing 0 to 0 of 0 entries', 'customer-specific-pricing-for-woocommerce'),
					'info_filtered'=> __('(filtered from _MAX_ total entries)', 'customer-specific-pricing-for-woocommerce'),
					'zero_records'=> __('No matching records found', 'customer-specific-pricing-for-woocommerce'),
					'loading_records'=> __('Loading...', 'customer-specific-pricing-for-woocommerce'),
					'processing' => __('Processing...', 'customer-specific-pricing-for-woocommerce'),
					'search' => __('Search:', 'customer-specific-pricing-for-woocommerce'),
					'first' => __('First', 'customer-specific-pricing-for-woocommerce'),
					'prev' => __('Previous', 'customer-specific-pricing-for-woocommerce'),
					'next' => __('Next', 'customer-specific-pricing-for-woocommerce'),
					'last' => __('Last', 'customer-specific-pricing-for-woocommerce'),
					'is_it_safe_to_delete' => isset($_GET[ 'subtab' ]) && ( 'safe_to_delete'== $_GET[ 'subtab' ] ) ? true : false,
				);

				wp_register_script('csp_single_qlog_js', plugins_url('/js/single-view/wdm-query-log-settings.js', dirname(dirname(dirname(__FILE__)))), array( 'jquery' ), CSP_VERSION);
				wp_enqueue_script('csp_single_qlog_js');

				wp_localize_script('csp_single_qlog_js', 'single_view_obj', $array_to_be_sent);
				?>
				<!-- <h4 class="wdm-csp-single-view-main-title">
				<?php 
				echo esc_html__('Rule Log', 'customer-specific-pricing-for-woocommerce');
				?>
				 </h4> -->

				<div class="wdm-csp-query-log-wrapper">

					<input type="button" class="btn btn-primary hide" id="wdm_delete_qlog" value="<?php esc_attr_e('Delete', 'customer-specific-pricing-for-woocommerce'); ?>
				">
				</div>
				<?php
			} else {
				?>
				<div class="notice-error csp-no-data-error">
					<p>
						<?php
						if (isset($_GET[ 'subtab' ]) && ( 'safe_to_delete'== $_GET[ 'subtab' ] )) {
							esc_html_e('No inactive rules found.', 'customer-specific-pricing-for-woocommerce');
						} else {
							esc_html_e('No active rules found.', 'customer-specific-pricing-for-woocommerce');
						}
						?>
						</p>
				</div>
				<?php
			}
		}

		//function ends -- Search Tab callback

		/**
		* Enqueue the required scripts and styles for the query log.
		*/
		private function enqueueScript() {
			//Enqueue JS & CSS

			wp_enqueue_style('csp_general_css_handler', plugins_url('/css/single-view/wdm-single-view.css', dirname(dirname(dirname(__FILE__)))), array(), CSP_VERSION);

			//Bootstrap
			wp_enqueue_style('csp_bootstrap_css', plugins_url('/css/import-css/bootstrap.css', dirname(dirname(dirname(__FILE__)))), array(), CSP_VERSION);

			//Datatable
			wp_enqueue_script('csp_singleview_datatable_js', plugins_url('/js/single-view/jquery.dataTables.min.js', dirname(dirname(dirname(__FILE__)))), array( 'jquery' ), CSP_VERSION);
			wp_enqueue_script('csp_singleview_bootstrap_datatable_js', plugins_url('/js/single-view/dataTables.bootstrap.min.js', dirname(dirname(dirname(__FILE__)))), array( 'jquery' ), CSP_VERSION);
			wp_enqueue_script('csp_singleview_button_js', plugins_url('/js/single-view/dataTables.buttons.min.js', dirname(dirname(dirname(__FILE__)))), array( 'csp_singleview_datatable_js' ), CSP_VERSION);
			wp_enqueue_script('csp_singleview_button_column_js', plugins_url('/js/single-view/buttons.colVis.min.js', dirname(dirname(dirname(__FILE__)))), array( 'csp_singleview_datatable_js' ), CSP_VERSION);

			wp_enqueue_style('csp_datatable_bootstrap_css', plugins_url('/css/single-view/dataTables.bootstrap.min.css', dirname(dirname(dirname(__FILE__)))), array(), CSP_VERSION);
			wp_enqueue_style('csp_datatable_css', plugins_url('/css/single-view/jquery.dataTables.min.css', dirname(dirname(dirname(__FILE__)))), array(), CSP_VERSION);
			wp_enqueue_style('csp_button_datatable_css', plugins_url('/css/single-view/buttons.dataTables.min.css', dirname(dirname(dirname(__FILE__)))), array(), CSP_VERSION);
		}

		//enqueueScript ends
	}

}
