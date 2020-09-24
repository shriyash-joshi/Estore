<?php

namespace CSPFeedbackTab;

if (!class_exists('WdmCSPFeedbackTab')) {
	/**
	* Class for the Settings tab on the admin page.
	*/
	class WdmCSPFeedbackTab {
	
		/**
		* Adds action for the Settings tab and saving the settings.
		*/
		public function __construct() {
			add_action('csp_feedback_tab', array($this,'feedbackTabcallback'));
		}

		

		/**
		* For the Settings tab
		* Enqueues the scripts and styles.
		*/
		public function feedbackTabcallback() {
			self::enqueueScript();
			?>
			<div class="typeform-widget" data-url="" data-transparency="100" data-hide-headers=true data-hide-footer=true style="width: 100%; height: 500px;">
				<iframe src="https://surveys.hotjar.com/s?siteId=769948&surveyId=153644" style ="height:100%; width:100%; text-align:center; margin-top:15px;" frameborder="0">
				</iframe>
			</div>
			</div>
			<?php
		}

		/**
		* Enqueue the scripts
		*/
		private function enqueueScript() {
			//Bootstrap
			wp_enqueue_style('csp_bootstrap_css', plugins_url('/css/import-css/bootstrap.css', dirname(dirname(__FILE__))), array(), CSP_VERSION);
		}
	}
}
