<?php

namespace CSPAPI;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

require_once 'cspapi-functions.php';

/**
* This class final CspApi cannot be extended and its method do not get overridden.
* This class is responsible for the Checking the Dependancies.
*/
if (!class_exists('CspApi')) {
	final class CspApi {
	
		/**
		 * Set to false when dependencies required to run plugin are not fulfilled.
		 *
		 * @var bool
		 */
		private $dependenciesFullfilled = true;
		protected static $instance      = null;

		/**
		 * Function to create a singleton instance of class and return the same.
		 *
		 * @return [Object] Singleton instance of CspApi class.
		 */
		public static function getInstance() {
			if (!self::$instance) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		* This function checks the dependancies.
		* Includes the files needed.
		*/
		private function __construct() {
			$this->dependenciesFullfilled = $this->checkDependencies();

			if ($this->dependenciesFullfilled) {
				add_action('init', array($this, 'includeFiles'));
			}

			add_action('admin_init', array($this, 'deactivatePlugins'));
		}

		/**
		 * This function is used to check dependencies
		 * Dependencies : Woocommerce and CSP active or not.
		 * : Php Version more than 5.3.0
		 *
		 * @return boolean true if dependancies fulfilled.
		 */
		private function checkDependencies() {
			$activePlugins = apply_filters('active_plugins', get_option('active_plugins'));
			$cspPlugin     = 'customer-specific-pricing-for-woocommerce/customer-specific-pricing-for-woocommerce.php';

			// Check if WooCommerce and CSP plugins are active.
			if (!in_array('woocommerce/woocommerce.php', $activePlugins) || !in_array($cspPlugin, $activePlugins)) {
				return false;
			}

			if (function_exists('phpversion')) {
				$phpVersion = phpversion();
			} elseif (defined('PHP_VERSION')) {
				$phpVersion = PHP_VERSION;
			}

			// Check if PHP version is greater than 5.3.0.
			if (version_compare($phpVersion, '5.3.0', '<')) {
				return false;
			}

			return true;
		}

		/**
		 * If WooCommerce and CSP is not active, deactivates itself.
		 */
		public function deactivatePlugins() {
			$activePlugins = apply_filters('active_plugins', get_option('active_plugins'));
			$cspPlugin     = 'customer-specific-pricing-for-woocommerce/customer-specific-pricing-for-woocommerce.php';

			// Check if WooCommerce is active.
			if (!in_array('woocommerce/woocommerce.php', $activePlugins)) {
				//TODO : Disable API
				add_action('admin_notices', array(
					$this,
					'wcNotActiveNotice', ));
				return;
			} elseif (!in_array($cspPlugin, $activePlugins)) {
				//TODO : disable the API
				add_action('admin_notices', array($this, 'cspNotActiveNotice'));
				return;
			}
		}

		/**
		 * Notice if woocommerce is not active
		 */
		public function wcNotActiveNotice() {
			?>
			<div class='error'>
				<p>
					<?php 
					echo esc_html__('WooCommerce plugin is not active. In order to make the CSP API plugin work, you need to install and activate WooCommerce first.', 'customer-specific-pricing-for-woocommerce');
					?>
				</p>
			</div>
			<?php
		}

		/**
		 * Notice if woocommerce is not active
		 */
		public function cspNotActiveNotice() {
			?>
			<div class='error'>
				<p>
					<?php 
					echo esc_html__('Customer Specific Pricing plugin is not active. In order to make the CSP API plugin work, you need to install and activate Customer Specific Pricing first.', 'customer-specific-pricing-for-woocommerce');
					?>
				</p>
			</div>
			<?php
		}

		/*
		 * This function includes required files
		 */
		public function includeFiles() {
			//Includes required files
			require_once CSP_PLUGIN_URL . '/includes/csp-api/file-includes.php';
		}
	}
}

CspApi::getInstance();
