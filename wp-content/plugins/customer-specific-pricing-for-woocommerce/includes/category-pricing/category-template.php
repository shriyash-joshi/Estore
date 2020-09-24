<?php
namespace cspCategoryPricing;

if (! defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

if (!class_exists('CategoryTemplate')) {
	class CategoryTemplate {
		public function __construct() {
			$this->featureHeader();
			$this->categorySpecificPricingRules();
			$this->enqueueScripts();
			// Print Feature header
			// Print feature settings if any
			// Print the page content
		}

		private function enqueueScripts() {
			wp_enqueue_script('bootstrap_js', plugins_url('/js/import-js/bootstrap.min.js', dirname(dirname(__FILE__))), array('jquery'), CSP_VERSION, true);
		}
		/**
		 * This method is used to print the html content of the feature page header
		 * This includes
		 * * Feature Title
		 * * Feature Notes
		 *
		 * @return void
		 */
		private function featureHeader() {
			$featureStatus	= get_option('cspCatPricingStatus', 'enable');
			$checked		= 'enable'==$featureStatus?'checked':'';
			?>
			<div class="wrap">
				<h3 class="csp-tab-header">
					<?php 
					esc_html_e('Category Specific Pricing', 'customer-specific-pricing-for-woocommerce'); 
					?>
				</h3>
			</div>
			<!-- Enable Disable Sec -->
			<div class="row feature-switch-row ">
				<div class="col-md-4 feature-switch">
					<div class="row">
						<div class="col-md-1">
						</div>
						<div class="col-md-3">
							<label class="switch" title="Enable/Disable Feature">
								<input type="checkbox" id="csp-cat-feature-switch" <?php echo esc_attr($checked); ?> >
								<span class="slider round"></span>
							</label>
						</div>
						<div class="col-md-3">
						</div>
					</div>
				</div>
				<div class="col-md-6 messages">
				<h4 class="loading-text text-right">
					<?php esc_html_e('Please Wait . . .', 'customer-specific-pricing-for-woocommerce'); ?>
				</h4>
				</div>
				<div class="col-md-2">
				</div>
			</div>
			<!-- Feature Notes -->
			<div class="row csp-cat-main-div-notes" style="display: block;">
				<div class="csp-notes-title">
					<p><?php esc_html_e('Notes', 'customer-specific-pricing-for-woocommerce'); ?></p>
				</div>
				<div class="csp-notes-content">
					<ol>
							<li><?php esc_html_e('If a customer is added more than once, the customer-price combination first in the list will be saved, and other combinations will be removed.', 'customer-specific-pricing-for-woocommerce'); ?></li>
							<li><?php esc_html_e('When a price field is left blank, the regular price will be displayed for that product.', 'customer-specific-pricing-for-woocommerce'); ?></li>
							<li><?php esc_html_e('Make sure the minimum quantity is set before saving the prices; adding the minimum quantity will ensure the prices are saved and displayed accordingly.', 'customer-specific-pricing-for-woocommerce'); ?></li>
							<li><?php esc_html_e('The least price will be applicable to a customer belonging to multiple groups/roles with specific prices.', 'customer-specific-pricing-for-woocommerce'); ?></li>
							<li><?php esc_html_e('The least price will be applicable for a product belonging to multiple categories with specific prices.', 'customer-specific-pricing-for-woocommerce'); ?></li>
							<li><?php esc_html_e('The priorities for prices applied to products are as follows', 'customer-specific-pricing-for-woocommerce'); ?> -
							<ol>
								<li><?php esc_html_e('Customer Specific Product Pricing.', 'customer-specific-pricing-for-woocommerce'); ?></li>
								<li><?php esc_html_e('Role Specific Product Pricing.', 'customer-specific-pricing-for-woocommerce'); ?></li>
								<li><?php esc_html_e('Group Specific Product Pricing.', 'customer-specific-pricing-for-woocommerce'); ?></li>
								<li><?php esc_html_e('Customer Specific Category Pricing.', 'customer-specific-pricing-for-woocommerce'); ?></li>
								<li><?php esc_html_e('Role Specific Category Pricing.', 'customer-specific-pricing-for-woocommerce'); ?></li>
								<li><?php esc_html_e('Group Specific Category Pricing.', 'customer-specific-pricing-for-woocommerce'); ?></li>
								<li><?php esc_html_e('Sale price (if any)', 'customer-specific-pricing-for-woocommerce'); ?></li>
								<li><?php esc_html_e('Regular Price', 'customer-specific-pricing-for-woocommerce'); ?></li>
							</ol>
							</li>
					</ol>
				</div>
			</div>
			<?php
		}


		private function categorySpecificPricingRules() {
			?>
			<form action = "#" method = "post" id="csp-cat-pricing-form" style="display:none">
			<?php wp_nonce_field('csp_save_category_pricing', '_save_category'); ?>
			<input type="hidden" name="save_records">
			<div class="row csp-ct-main-div" style="/**display:none;**/">
				<div class="csp-cat-collapse-wrapper center-block">
					<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="false">
						<!-- USP -->
						<div class="panel panel-default">
							<div class="panel-heading" id="cat-usp-panel">
								<h4 class="panel-title">
									<a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
									<?php esc_html_e('User based category pricing', 'customer-specific-pricing-for-woocommerce'); ?>
									</a>
								</h4>
							</div>
							<div id="collapseOne" class="panel-collapse collapse" role="tabpanel" aria-labelledby="cd-first-time-buyers">
								<div class="panel-body">
									<div class = "user_data">
									<?php
									do_action('csp_show_user_data');
									?>
									</div>
								</div>
							</div>
						</div>
						<!-- RSP -->
						<div class="panel panel-default">
							<div class="panel-heading" role="tab" id="cat-rsp-panel">
								<h4 class="panel-title">
									<a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
									<?php esc_html_e('Role based category pricing', 'customer-specific-pricing-for-woocommerce'); ?>
									</a>
								</h4>
							</div>
							<div id="collapseTwo" class="panel-collapse collapse" role="tabpanel" aria-labelledby="cd-existing-buyers">
								<div class="panel-body">
									<div class = "role_data">
									<?php
										do_action('csp_show_role_data');
									?>
									</div>
								</div>
							</div>
						</div>
						<!-- GSP -->
						<div class="panel panel-default">
							<div class="panel-heading" role="tab" id="cat-gsp-panel">
								<h4 class="panel-title">
									<a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
									<?php esc_html_e('Group based category pricing', 'customer-specific-pricing-for-woocommerce'); ?>
									</a>
								</h4>
							</div>
							<div id="collapseThree" class="panel-collapse collapse" role="tabpanel" aria-labelledby="cd-guest-users">
								<div class="panel-body">
									<div class = "group_data">
										<?php do_action('csp_show_group_data'); ?>
									</div>
								</div>
							</div>
						</div>
						<!-- /GSP -->
					</div>
				</div>
			</div>
			<div class="save-row">
				<div class="wdm-input-group">
					<input type="submit" id="cat_pricing" name="save_cat_price" class="button button-primary" value="<?php esc_html_e('Save Pricing', 'customer-specific-pricing-for-woocommerce'); ?>">
				</div>
				<div class="save-status">
				</div>
			</div>
			</form>
			<?php
		}
	}
}
