<?php

namespace CSPCartDiscount\Settings;

if (!class_exists('WdmCSPUserProductData')) {
	/**
	 * Class contains the global settings for the cart discount feature
	 *
	 * @since 4.3.0
	 */
	class WdmCSPCDSettings {
	
		/**
		 * Possible Values
		 * True             - Feature Enabled
		 * False            - Feature Disabled
		 * "Rules Empty"    - Feature Enabled But No Rules Present
		 *
		 * @since 4.3.0
		 * @var mixed
		 */

		private $cspCDSettings=array();
		
		/**
		 * Constructor for the class
		 */
		public function __construct() {
			//Change feature status
			add_action('wp_ajax_nopriv_set_feature_enabled', array($this, 'changeFeatureStatus'));
			add_action('wp_ajax_set_feature_enabled', array($this, 'changeFeatureStatus'));

			//change threshold for the rule display
			add_action('wp_ajax_nopriv_csp_cd_set_settings', array($this, 'updateSettings'));
			add_action('wp_ajax_csp_cd_set_settings', array($this, 'updateSettings'));

			$this->getCSPCDSettings();
		}


		/**
		 * Method which is used to activate/deactivate cart discount feature.
		 *
		 * @return void
		 */
		public function changeFeatureStatus() {
			if (empty($_POST['cd_nonce']) || !wp_verify_nonce(sanitize_text_field($_POST['cd_nonce']), 'wdm-csp-cd')) {
				echo 'Security Check';
				exit();
			}
			if (isset($_POST['featureStatus']) && in_array($_POST['featureStatus'], array('enable','disable'))) {
				echo esc_html($this->setFeatureEnabled(sanitize_text_field($_POST['featureStatus'])));
				die();
			}
		}


		/**
		 * AJAX callback to set the % threshold amount
		 *
		 * @return void
		 */
		public function updateSettings() {
			if (empty($_POST['cd_nonce']) || !wp_verify_nonce(sanitize_text_field($_POST['cd_nonce']), 'wdm-csp-cd')) {
				echo 'Security Check';
				exit();
			}
			if (isset($_POST['thresholdPercent'])) {
				echo esc_html($this->setCartThreshold(sanitize_text_field($_POST['thresholdPercent'])));
			}

			if (isset($_POST['minMaxDiscount'])) {
				echo esc_html($this->setminMaxDiscount(sanitize_text_field($_POST['minMaxDiscount'])));
			}
			die();
		}

		/**
		 * Returns the status of the feature
		 * if feature is turned off returns false
		 *
		 * @todo if feature is active but no rules present or no rules are active return rules empty
		 * @since 4.3.0
		 * @return mixed status of the feature;
		 */
		public function getfetureEnabled() {
			$statusEnabled = isset($this->cspCDSettings['featureEnabled'])?$this->cspCDSettings['featureEnabled']:false;
			// if ($statusEnabled) {
			//     // if no cart rules/no-active-cart-rules present set $statusEnabled to "Rules Empty"
			// }
			return $statusEnabled;
		}

		/**
		 * This function returns the threshold set after crossing which the offer can be displayed on the cart page
		 * ex. if threshold is 80% and there is a cart rule for the cart total 1000 , when cart total reach 80% of 1000
		 * i.e. 800, the offer text will be displayed on the cart page.
		 *
		 * @return void
		 */
		public function getCDThresholdPercent() {
			if (isset($this->cspCDSettings['thresholdPercent'])) {
				if ($this->cspCDSettings['thresholdPercent'] > 100) {
					return apply_filters('csp_cd_threshold_percent', 80);
				}
				return $this->cspCDSettings['thresholdPercent'];
			}
			return apply_filters('csp_cd_threshold_percent', 80);
		}



		/**
		 * This method returns the binary value "min"/"max"
		 * Value from this method will be used to deside, whether to apply the rule
		 * giving maximum discount or to apply a rule giving minimum discount
		 *
		 * @return void
		 */
		public function getMinOrMaxCDtoApply() {
			if (isset($this->cspCDSettings['cdMinMax'])) {
				if (!in_array($this->cspCDSettings['cdMinMax'], array('min', 'max'))) {
					return apply_filters('csp_cd_min_max', 'max');
				}
				return $this->cspCDSettings['cdMinMax'];
			}
			return apply_filters('csp_cd_min_max', 'max');
		}


		/**
		 * Set value for the status of the feature
		 *
		 * @param [type] $featureEnabled
		 * @return void
		 */
		private function setFeatureEnabled( $featureEnabled) {
			if (!empty($featureEnabled)) {
				$this->cspCDSettings['featureEnabled']=$featureEnabled;
				return update_option('WdmCSPCDSettings', $this->cspCDSettings);
			}
		}


		/**
		 * Sets value for the threshold %,
		 * When the cart amount crosses the % amount of any of the cart discount rule
		 * offer text of that rule will be displayed on the cart page.
		 *
		 * @param [type] $featureEnabled
		 * @return void
		 */
		private function setCartThreshold( $thresholdPercent) {
			if (!empty($thresholdPercent) && ( $thresholdPercent>=0 && $thresholdPercent<=100 )) {
				$this->cspCDSettings['thresholdPercent']=$thresholdPercent;
				return update_option('WdmCSPCDSettings', $this->cspCDSettings);
			}
		}


		/**
		 * Sets the value for cdMinMax,
		 * This value is used when there are multiple applicable rules
		 * to deside wether to apply the rule which gives maximum discount
		 * or to apply the rule which will give minimum discount
		 *
		 * @param [type] $cdMinMax
		 * @return void
		 */
		private function setminMaxDiscount( $cdMinMax) {
			if (!empty($cdMinMax) && in_array($cdMinMax, array('min', 'max'))) {
				$this->cspCDSettings['cdMinMax']=$cdMinMax;
				return update_option('WdmCSPCDSettings', $this->cspCDSettings);
			}
		}
		/**
		 * This method fetches the feature settings from the database.
		 *
		 * @return void
		 */
		private function getCSPCDSettings() {
			$this->cspCDSettings=get_option('WdmCSPCDSettings', array());
		}
	}
}
