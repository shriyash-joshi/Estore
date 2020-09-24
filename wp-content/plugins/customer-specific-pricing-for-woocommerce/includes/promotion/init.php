<?php

namespace wdmCSPPromotion;

/**
 * Promotions
 *
 * To register the Quick Course Module.
 *
 * @package Advanced_Modules
 *
 * @since   1.0
 *
 *
 */
if (!class_exists('CspPromotion')) {

	class CspPromotion {
	
		private static $instance = null;
		// private $pluginSlug = 'csp';

		/**
		 * Private constructor to make this a singleton
		 *
		 */
		public function __construct() {
			add_action('csp_single_view_promotions', array($this, 'cspPromotionPage'), 10);
			add_action('admin_enqueue_scripts', array($this, 'cspLoadStyles'));
		}
		/**
		 * Function to instantiate our class and make it a singleton
		 */
		public static function getInstance() {
			if (!self::$instance) {
				self::$instance = new self();
			}

			return self::$instance;
		}


		public function cspPromotionPage() {
			update_option('csp-update-status', 'visited_whats_new_page');
			$promotionsDirPath = plugin_dir_url(__FILE__, '/includes/promotion');
			?>	
			<div class="main-content">
				<div class="heading-subheading">
					<h1 class="csp-wn-head">
						<?php 
							esc_html_e('CSP v4.4.3 has been optimized for speed - making it lightning fast.');
						?>
						<span class="dashicons dashicons-performance"></span>
					</h1>
				</div>
				<div class="content">
					<p class="content-text">
					<?php esc_html_e('Refer to our below test results to know more (captured without the use of any caching techniques). On a Storefront theme v2.5.5 & with 25k+ products in a web store, PHP version 7.3.9.', 'customer-specific-pricing-for-woocommerce'); ?>
					</p>
					<div class="csp-wn-img-section">
						<figure class="feature-col">
								<a href="<?php echo esc_url($promotionsDirPath . 'images/before-update.png'); ?>" target="_blank" rel="noopener noreferrer">
									<h3 class="image-head"><?php esc_html_e('Load time before the update :', 'customer-specific-pricing-for-woocommerce'); ?></h3>
									<img  src="<?php echo esc_url($promotionsDirPath . 'images/before-update.png'); ?>" class="feature-image">
								</a>
						</figure>
						<figure class="feature-col">
								<a href="<?php echo esc_url($promotionsDirPath . 'images/after-update.png'); ?>" target="_blank" rel="noopener noreferrer">
									<h3 class="image-head"><?php esc_html_e('Load time After the update :', 'customer-specific-pricing-for-woocommerce'); ?></h3>
									<img  src="<?php echo esc_url($promotionsDirPath . 'images/after-update.png'); ?>" class="feature-image">
								</a>
						</figure>
					</div>
				</div>
				<hr class="feature-seperator">
				<div class="csp-wn-section-other"> 
					<div class="heading-subheading">
						<h2 class="csp-wn-subhead">
							<?php
							esc_html_e('Other Changes & Improvements', 'customer-specific-pricing-for-woocommerce');
							?>
						</h2>
					</div>
					<div class="content">
						<h3><?php esc_html_e('New filters included', 'customer-specific-pricing-for-woocommerce'); ?></h3>
						<ul>
							<li>
								<?php 
								esc_html_e('A filter to enable the global discounts is included.', 'customer-specific-pricing-for-woocommerce');
								?>
									<a target="_blank" href="https://wisdmlabs.com/docs/article/wisdm-customer-specific-pricing/csp-tips-and-tricks/applying-global-or-sitewide-discounts-using-customer-specific-pricing/">
										<?php
										esc_html_e('Documentation', 'customer-specific-pricing-for-woocommerce');
										?>
									</a>
							</li>
							<li>
								<?php 
									esc_html_e('A filter to enable importing all the products with the same SKU at once.', 'customer-specific-pricing-for-woocommerce');
								?>
									<a target="_blank" href="https://wisdmlabs.com/docs/article/wisdm-customer-specific-pricing/csp-faqs/is-it-possible-how-to-enable-importing-of-all-the-products-with-the-same-sku-at-once/">
										<?php
										esc_html_e('Documentation', 'customer-specific-pricing-for-woocommerce');
										?>
									</a>
							</li>
						</ul>
						<h3><?php esc_html_e('Bug Fixes & Improvements', 'customer-specific-pricing-for-woocommerce'); ?></h3>
						<ul>
							<li>
								<?php 
									esc_html_e('Fixed the issue causing deletion of a customer-specific rule log entry on the deletion of a subrule.', 'customer-specific-pricing-for-woocommerce');
								?>
							</li>
							<li>
								<?php 
									esc_html_e('Fixed the issue generating error while saving the page having special offers shortcode.', 'customer-specific-pricing-for-woocommerce');
								?>
							</li>
							<li>
								<?php 
									esc_html_e('Improvement added where "null" is being displayed as a suffix for variable products when there\'s no suffix specified.', 'customer-specific-pricing-for-woocommerce');
								?>
							</li>
							<li>
								<?php 
									esc_html_e('Fixed the issue with manual order calculation when taxation is enabled.', 'customer-specific-pricing-for-woocommerce');
								?>
							</li>
						</ul>
					</div>
				</div>
				<div class="csp-cta">
					<a href="https://wisdmlabs.com/contact-us/#support" class="button" target="_blank">Support</a>
					<a href="https://wisdmlabs.com/docs/product/wisdm-customer-specific-pricing/" class="button" target="_blank">Docs</a>
					<a href="https://wisdmlabs.com/docs/article/wisdm-customer-specific-pricing/changelog-csp/changelog-csp/" class="button" target="_blank">Changelog</a>
				</div>
			</div>
			<?php
		}


		public function cspLoadStyles() {
			global $wdmPluginDataCSP;
			if (!empty($_GET['tabie']) && 'promotions_tab' == $_GET['tabie']) {
				// $min = (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG === true) ? '':'.min';

				wp_register_style($wdmPluginDataCSP['pluginSlug'] . '-promotion', plugins_url('assets/css/extension.css', __FILE__), array(), $wdmPluginDataCSP['pluginVersion']);

				// Enqueue admin styles
				wp_enqueue_style($wdmPluginDataCSP['pluginSlug'] . '-promotion');
			}
		}

		// End of functions
	}
	CspPromotion::getInstance();
}
