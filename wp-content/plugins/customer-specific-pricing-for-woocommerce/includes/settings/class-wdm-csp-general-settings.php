<?php

namespace CSPGenSettings;

if (!class_exists('WdmCSPGeneralSettings')) {
	/**
	* Class for the Settings tab on the admin page.
	*/
	class WdmCSPGeneralSettings {
	
		/**
		* Adds action for the Settings tab and saving the settings.
		*/
		public function __construct() {
			add_action('admin_init', array($this, 'registerSetting'));
			add_action('csp_general_settings', array($this,'generalSettingsCallback'));
		}

		/**
		 * Register the setting having some handle
		 */
		public function registerSetting() {
			register_setting('wdm_csp_options', 'wdm_csp_settings', array('sanitize_callback' => array($this, 'cspSanitizeSettings')));
		}

		/**
		* Sanitize new settings for the form.
		 *
		* @param array $newValue new value of settings
		*/
		public function cspSanitizeSettings( $newValue) {
			if (empty($newValue)) {
				return array();
			}
		   
			return $newValue;
		}

		/**
		* For the Settings tab
		* Enqueues the scripts and styles.
		*/
		public function generalSettingsCallback() {
			self::enqueueScript();

			settings_errors();
			?>
			<!--dashboard settings design-->
			<div class="wrap">
				<h3 class="csp-tab-header"><?php esc_html_e('General Settings', 'customer-specific-pricing-for-woocommerce'); ?></h3>
		</div>
		<div class="container">
				<form name="ask_product_form" id="ask_product_form" class="form-table" method="POST" action="options.php">
					<?php
					settings_fields('wdm_csp_options');
					$cspSettings                   	= get_option('wdm_csp_settings');
					$isStrikeThroughEnabled        	= isset($cspSettings['enable_striketh']) && 'enable' == $cspSettings['enable_striketh'] ? true : false;
					$hidePriceTotal                	= isset($cspSettings['wdm_csp_hide_price_total']) && 'enable' == $cspSettings['wdm_csp_hide_price_total'] ? true : false;
					$isSalePriceDiscountEnabled    	= isset($cspSettings['enable_sale_price_discount']) && 'enable' == $cspSettings['enable_sale_price_discount'] ? true : false;
					$specialPricingButtonText      	= isset($cspSettings['special_pricing_button_text']) ? $cspSettings['special_pricing_button_text']:'Show Special Pricing';
					$showRegPrice                  	= isset($cspSettings['show_regular_price']) && 'enable' == $cspSettings['show_regular_price'] ? true : false;
					$regularPriceDisplayText       	= isset($cspSettings['regular_price_display_text']) ? $cspSettings['regular_price_display_text']:'Regular Price';
					$priceDiscriptionLabelText     	= isset($cspSettings['csp_discription_text']) ? $cspSettings['csp_discription_text']:'';
					$isAPIEnabled                  	= isset($cspSettings['csp_api_status']) && 'enable' == $cspSettings['csp_api_status'] ? true:false;
					$CSPArchiveSignedInRequiredText	= isset($cspSettings['csp_archive_signed_in_required_text']) ? $cspSettings['csp_archive_signed_in_required_text']:'Please log in to see the Special Offers for you';
					$CSPArchiveNoOffersText			= isset($cspSettings['csp_archive_no_offers_text']) ? $cspSettings['csp_archive_no_offers_text']:'Oops no special offers available';

					$strikeThroughHelp             	=   __('Check this option to display Strike-through Prices (valid for CSP Plugin prices only).', 'customer-specific-pricing-for-woocommerce');
					$hidePriceTotalHelp            	=   __('Check this option to hide Price Total on the product page.', 'customer-specific-pricing-for-woocommerce');
					$discountHelp                  	=   __('Check this option to apply CSP Plugin discounts on default product Sale Price.', 'customer-specific-pricing-for-woocommerce');
					$specialButtonHelp             	=   __('Add custom text (for eg, Read More) for the products that cannot be purchased as a single quantity.', 'customer-specific-pricing-for-woocommerce');
					$showRegPriceHelp              	=   __('Check this option to display Regular Price on product page along with CSP prices.', 'customer-specific-pricing-for-woocommerce');
					$regularPriceDisplayTextHelp   	=   __('A text to be shown in front of the regular price eg. Regular Price : $500', 'customer-specific-pricing-for-woocommerce');
					$priceDiscriptionLabelTextHelp 	=   __('A text to be shown on the product page describing the pricing options.', 'customer-specific-pricing-for-woocommerce');
					$enableAPIHelp                 	=   __('Enabling CSP API allows you to manage the CSP pricing rules using Woocommerce Rest API', 'customer-specific-pricing-for-woocommerce');
					$CSPArchiveSignedInRequiredHelp	= 	__('A text to be shown on the CSP product archive page if the user is not signed in.', 'customer-specific-pricing-for-woocommerce');
					$CSPArchiveNoOffersTextHelp		=	__('A text to be shown on the CSP product archive page if no CSP priced products are available for the user', 'customer-specific-pricing-for-woocommerce');

					$this->settingStrikeThrough($isStrikeThroughEnabled, $strikeThroughHelp);
					$this->settingShowRegPrice($showRegPrice, $regularPriceDisplayText, $showRegPriceHelp, $regularPriceDisplayTextHelp);
					$this->settingPriceDiscription($priceDiscriptionLabelText, $priceDiscriptionLabelTextHelp);
					$this->settingHidePriceTotal($hidePriceTotal, $hidePriceTotalHelp);
					$this->settingPercentDiscountOnSalePrice($isSalePriceDiscountEnabled, $discountHelp);
					$this->settingSpecialPricingButtonText($specialPricingButtonText, $specialButtonHelp);
					$this->settingEnableAPI($isAPIEnabled, $enableAPIHelp);
					$this->cspProductArchivePageSettings($CSPArchiveSignedInRequiredText, $CSPArchiveSignedInRequiredHelp, $CSPArchiveNoOffersText, $CSPArchiveNoOffersTextHelp);
					?>
				<input type="submit" class="wdm_csp_input submit button-primary" value="<?php esc_attr_e('Save changes', 'customer-specific-pricing-for-woocommerce'); ?>" id="wdm_ask_button" />
				</form>
			</div>
			<?php
		}


		public function cspProductArchivePageSettings( $CSPArchiveSignedInRequiredText, $CSPArchiveSignedInRequiredHelp, $CSPArchiveNoOffersText, $CSPArchiveNoOffersTextHelp) {
			?>
			<div class="csp-setting-section-seperator">
			<hr>
			<h4><?php esc_html_e('Special Shop Page Settings', 'customer-specific-pricing-for-woocommerce'); ?></h4>
			<h6><a target="_balnk" href="https://wisdmlabs.com/docs/article/wisdm-customer-specific-pricing/csp-getting-started/csp-user-guide/user-specific-product-archives/"><?php esc_html_e('How to create special shop page', 'customer-specific-pricing-for-woocommerce'); ?><span class="dashicons dashicons-external" style="font-size: 16px;"></span></a></h6>
			</div>
			<br>
			<div class="fd">
				<div class='left_div col-md-3 form-control-label'>
					<label for="csp_archive_signed_in_required_text"><?php esc_html_e('A Message For The Non Logged In User', 'customer-specific-pricing-for-woocommerce'); ?></label>
				</div>
				<div class='right_div col-md-7 form-control-label'>
					<input type="text" class="wdm_csp_input" name="wdm_csp_settings[csp_archive_signed_in_required_text]" value="<?php echo esc_attr(trim($CSPArchiveSignedInRequiredText)); ?>" id="csp_archive_signed_in_required_text" placeholder="<?php esc_html_e('Please log in to see the Special Offers for you', 'customer-specific-pricing-for-woocommerce'); ?>" />
					<br>
					<p class="help-text"><?php echo esc_html($CSPArchiveSignedInRequiredHelp); ?></p>
				</div>
				<div class='clear'></div>
			</div >
			<br>

			<div class="fd">
				<div class='left_div col-md-3 form-control-label'>
					<label for="csp_archive_no_offers_text"><?php esc_html_e('No Special Pricing Text', 'customer-specific-pricing-for-woocommerce'); ?></label>
				</div>
				<div class='right_div col-md-7 form-control-label'>
					<input type="text" class="wdm_csp_input" name="wdm_csp_settings[csp_archive_no_offers_text]" value="<?php echo esc_attr(trim($CSPArchiveNoOffersText)); ?>" id="csp_archive_no_offers_text" placeholder="<?php esc_html_e('Oops! No special offers available currently', 'customer-specific-pricing-for-woocommerce'); ?>" />
					<br>
					<p class="help-text"><?php echo esc_html($CSPArchiveNoOffersTextHelp); ?></p>
				</div>
				<div class='clear'></div>
			</div >
			<br>
			<?php
		}

		/**
		 * Method returns the html required to display the setting option -
		 * Textbox to get the text to be displayed on the button where minimum purchase quantity
		 * feature is used.
		 *
		 * @since 4.3.0
		 * @param [type] $specialPricingButtonText
		 * @param [type] $specialButtonHelp
		 * @return void
		 */
		public function settingSpecialPricingButtonText( $specialPricingButtonText, $specialButtonHelp) {
			?>
			<div class="fd">
				<div class='left_div col-md-3 form-control-label'>
					<label for="csp-special-pricing-button-text"><?php esc_html_e('Show Special Pricing Button Text', 'customer-specific-pricing-for-woocommerce'); ?></label>
				</div>
				<div class='right_div col-md-7 form-control-label'>
					<input type="text" class="wdm_csp_input" name="wdm_csp_settings[special_pricing_button_text]" value="<?php echo esc_attr(trim($specialPricingButtonText)); ?>" id="csp-special-pricing-button-text" placeholder="show special pricing options" />
					<br>
					<p class="help-text"><?php echo esc_html($specialButtonHelp); ?></p>
				</div>
				<div class='clear'></div>
			</div >
			<br>
			<?php
		}

		/**
		 * Method returns the html required to display the setting option -
		 * enable % discount on the sale price
		 *
		 * @since 4.3.0
		 * @param [type] $isSalePriceDiscountEnabled
		 * @param [type] $discountHelp
		 * @return void
		 */
		public function settingPercentDiscountOnSalePrice( $isSalePriceDiscountEnabled, $discountHelp) {
			?>
			<div class="fd">
				<div class='left_div col-md-3 form-control-label'>
					<label for="csp-enable-sale-price-discount"><?php esc_html_e('Enable % Discount On Sale Price', 'customer-specific-pricing-for-woocommerce'); ?></label>
				</div>
				<div class='right_div col-md-6 form-control-label'>
					<input type="checkbox" class="wdm_csp_input wdm_wpi_checkbox" name="wdm_csp_settings[enable_sale_price_discount]" value="enable" <?php checked(1, $isSalePriceDiscountEnabled); ?>
					 id="csp-enable-sale-price-discount" />
					<br>
					<p class="help-text"><?php echo esc_html($discountHelp); ?></p>
				</div>
				<div class='clear'>
				</div>
			</div >
			<br>
			<?php
		}


		/**
		 * Method returns the html required to display the setting option -
		 * hidePriceTotal
		 *
		 * @since 4.3.0
		 * @param [type] $hidePriceTotal
		 * @param [type] $hidePriceTotalHelp
		 * @return void
		 */
		public function settingHidePriceTotal( $hidePriceTotal, $hidePriceTotalHelp) {
			?>
			<div class="fd">
				<div class='left_div col-md-3 form-control-label'>
					<label for="wdm_csp_hide_price_total"><?php esc_html_e('Hide Price Total', 'customer-specific-pricing-for-woocommerce'); ?></label>
				</div>
				<div class='right_div col-md-6 form-control-label'>
					<input type="checkbox" class="wdm_csp_input wdm_wpi_checkbox" name="wdm_csp_settings[wdm_csp_hide_price_total]" value="enable" 
					<?php 
					checked(1, $hidePriceTotal);
					?>
					 id="wdm_csp_hide_price_total" />
						<br>
						<p class="help-text"><?php echo esc_html($hidePriceTotalHelp); ?></p>
				</div>
				<div class='clear'>
				</div>
			</div>
			<br>
			<?php
		}

		/**
		 * Method returns the html required to display the setting option -
		 * checkbox for enable strikethrough setting.
		 *
		 * @param [type] $isStrikeThroughEnabled - existing/saved setting option for strikethrough
		 * @param [type] $strikeThroughHelp      - Help text for the setting
		 * @return void
		 */
		private function settingStrikeThrough( $isStrikeThroughEnabled, $strikeThroughHelp) {
			?>
			<div class="fd">
				<div class='left_div col-md-3 form-control-label'>
					<label for="csp-enable-striketh"><?php esc_html_e('Enable strikethrough', 'customer-specific-pricing-for-woocommerce'); ?></label>
				</div>
				<div class='right_div col-md-6 form-control-label'>
					<input type="checkbox" class="wdm_csp_input wdm_wpi_checkbox" name="wdm_csp_settings[enable_striketh]" value="enable" 
					<?php 
					checked(1, $isStrikeThroughEnabled);
					?>
					 id="csp-enable-striketh" />
					<br>
					<p class="help-text"><?php echo esc_html($strikeThroughHelp); ?></p>
				</div>
				<div class='clear'></div>
			</div >
			<br>
			<?php
		}


		/**
		 * Method returns the html required to display the setting option -
		 * checkbox to enable Show regular price setting & to get text to be
		 * shown in front of regular price.
		 *
		 * @since 4.3.0
		 * @param [type] $showRegPrice
		 * @param [type] $regularPriceDisplayText
		 * @param [type] $showRegPriceHelp
		 * @param [type] $regularPriceDisplayTextHelp
		 * @return void
		 */
		private function settingShowRegPrice( $showRegPrice, $regularPriceDisplayText, $showRegPriceHelp, $regularPriceDisplayTextHelp) {
			?>
			<div class="fd">
				<div class='left_div col-md-3 form-control-label'>
					<label for="csp-show-regular-price"><?php esc_html_e('Show Regular Price', 'customer-specific-pricing-for-woocommerce'); ?></label>
				</div>
				<div class='right_div col-md-6 form-control-label'>
					<input type="checkbox" class="wdm_csp_input wdm_wpi_checkbox" name="wdm_csp_settings[show_regular_price]" value="enable" <?php checked(1, $showRegPrice); ?>
					 id="csp-show-regular-price" />
					<br>
					<p class="help-text"><?php echo esc_html($showRegPriceHelp); ?></p>
				</div>
				<div class='clear'></div>
				</div >
				<br>
				<div class="fd reg-price-display-text">
					<div class='left_div col-md-3 form-control-label'>
					<label for="csp-regular-price-display-text"><?php esc_html_e('Regular Price Display Text', 'customer-specific-pricing-for-woocommerce'); ?></label>
				</div>
				<div class='right_div col-md-7 form-control-label'>
					<input type="text" class="wdm_csp_input" name="wdm_csp_settings[regular_price_display_text]" value="<?php echo esc_attr(trim($regularPriceDisplayText)); ?>" id="csp-regular-price-display-text" placeholder="Text to display for regular price" />
					<br>
					<p class="help-text"><?php echo esc_html($regularPriceDisplayTextHelp); ?></p>
				</div>
				<div class='clear'>
				</div>
				<br>
			</div >
			<?php
		}


		/**
		 * Method returns the html required to display the setting option -
		 * textbox to get text to be displayed as the price discription.
		 *
		 * @since 4.3.0
		 * @param [type] $priceDiscriptionLabelText
		 * @param [type] $priceDiscriptionLabelTextHelp
		 * @return void
		 */
		public function settingPriceDiscription( $priceDiscriptionLabelText, $priceDiscriptionLabelTextHelp) {
			?>
			<div class="fd">
				<div class='left_div col-md-3 form-control-label'>
					<label for="csp-discription-text"><?php esc_html_e('Price Description Text', 'customer-specific-pricing-for-woocommerce'); ?></label>
				</div>
				<div class='right_div col-md-7 form-control-label'>
					<input type="text" class="wdm_csp_input" name="wdm_csp_settings[csp_discription_text]" value="<?php echo esc_attr(trim($priceDiscriptionLabelText)); ?>" id="csp-discription-text" placeholder="Text to be displayed as a discription for the price" />
					<br>
					<p class="help-text"><?php echo esc_html($priceDiscriptionLabelTextHelp); ?></p>
				</div>
				<div class='clear'>
				</div>
			<br>
			</div >
			<?php
		}


		/**
		 * Prints the setting to Enable/Disable the CSP API
		 *
		 * @param string $isAPIEnabled
		 * @param string $enableAPIHelp
		 * @return void
		 */
		public function settingEnableAPI( $isAPIEnabled, $enableAPIHelp) {
			?>
			<div class="fd">
				<div class='left_div col-md-3 form-control-label'>
					<label for="csp_api_status"><?php esc_html_e('Enable API', 'customer-specific-pricing-for-woocommerce'); ?></label>
				</div>
				<div class='right_div col-md-6 form-control-label'>
					<input type="checkbox" class="wdm_csp_input wdm_wpi_checkbox" name="wdm_csp_settings[csp_api_status]" value="enable" <?php checked(1, $isAPIEnabled); ?>
					 id="csp_api_status" />
					<br>
					<p class="help-text"><?php echo esc_html($enableAPIHelp); ?></p>
				</div>
				<div class='clear'>
				</div>
			</div >
			<br>
			<?php
		}


		/**
		 * Enqueue the scripts
		 */
		private function enqueueScript() {
			//Bootstrap
			wp_enqueue_style('csp_bootstrap_css', plugins_url('/css/import-css/bootstrap.css', dirname(dirname(__FILE__))), array(), CSP_VERSION);
			wp_enqueue_style('csp_general_settings_tab', plugins_url('/css/settings-tab.css', dirname(dirname(__FILE__))), array(), CSP_VERSION);
			wp_enqueue_script('csp_settings_page_js', plugins_url('/js/single-view/wdm-settings-page.js', dirname(dirname(__FILE__))), array('jquery'), CSP_VERSION);
		}
	}
}
