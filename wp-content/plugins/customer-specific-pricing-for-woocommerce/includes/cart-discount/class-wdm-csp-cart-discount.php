<?php

namespace CSPCartDiscount;

if (!class_exists('WdmCSPCartDiscount')) {
	/**
	* Class for the Settings tab on the admin page.
	*/
	class WdmCSPCartDiscount {
	
		private $featureSettings;
		private $discountRules;
		private $defaultButtonText;
		private $defaultButtonLink;
		private $noMaxDiscountHelpText;
		private $defaultafterOfferText;
		private $defaultbeforeOfferText;
		/**
		* Adds action for the Settings tab and saving the settings.
		*/
		public function __construct() {
			add_action('csp_cart_discounts_tab', array($this,'cartDiscountTabCallBack'));
			include_once('data-store/class-feature-settings.php');
			include_once('data-store/cart-discount-rules.php');
			include_once('data-store/class-wdm-csp-user-product-data.php');
			$this->featureSettings          = new Settings\WdmCSPCDSettings();
			$this->discountRules            = new WdmCspCdRules();
			$this->noMaxDiscountHelpText    = __('No limit on the maximum value will be applied if the max value range in the last field is not defined', 'customer-specific-pricing-for-woocommerce');
			$this->defaultbeforeOfferText   = __('Add [difference-amount] worth additional products to the cart and avail [percent-value] discount.', 'customer-specific-pricing-for-woocommerce');
			$this->defaultafterOfferText    = __('Congratulations you have got [percent-value] discount.', 'customer-specific-pricing-for-woocommerce');
			$this->defaultButtonText        = __('Continue Shopping', 'customer-specific-pricing-for-woocommerce');
			$this->defaultButtonLink        = get_permalink(wc_get_page_id('shop'));
		}

		/**
		 * For the Settings tab
		 * * Enqueues the scripts and styles.
		 * * Loads Settings tab content for cart discount feature.
		 *
		 * @since 4.3.0
		 */
		public function cartDiscountTabCallBack() {
			?>
			<div class="wrap"><h3 class="csp-tab-header">
			<?php esc_html_e('Cart Discounts', 'customer-specific-pricing-for-woocommerce'); ?></h3>
			</div>
			<?php
			$this->featureStatusHtml($this->featureSettings);
			$this->getCartDiscountPageHtml();
			$this->enqueueStylesScripts();
		}

		/**
		 * Prints the cart discount page containing different modules & sections
		 *
		 * @since 4.3.0
		 * @return void
		 */
		public function getCartDiscountPageHtml() {
			?>
			<div class="row csp-cd-main-div-notes" style="display: block;">
				<div class="cd-notes-title"><?php esc_html_e('Notes', 'customer-specific-pricing-for-woocommerce'); ?></div>
				<div class="cd-notes-content">
					<ol>
						<li><?php esc_html_e('The Cart Discount is calculated on the total price of all the eligible products present in the cart.', 'customer-specific-pricing-for-woocommerce'); ?></li>
						<li><?php esc_html_e('In a situation when a valid coupon code is applied to the products which are eligible for cart discount, the final price will be calculated by adding the Coupon Code discount and Cart Value Discount.', 'customer-specific-pricing-for-woocommerce'); ?></li>
					</ol>
				</div>
			</div>
			<div class="row csp-cd-main-div" style="display:none;">
				<div class="csp-cd-collapse-wrapper center-block">
					<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
						<?php
							$this->getCdRulesHtmlForFirstTimeBuyers();
							$this->getCdRulesHtmlForExistingBuyers();
							$this->getCdRulesHtmlForGuestUsers();
						?>
					</div>
				</div>
			</div>
			<div class="save-row">
				<div>
					<button class="btn btn-primary save-all-cd-rules">
						<?php esc_html_e('Save All', 'customer-specific-pricing-for-woocommerce'); ?>
					</button>
				</div>
				<div class="save-status">
				</div>
				</div>
			<?php
		}


		/**
		 * Prints an accordion panel to define a cart discount rule
		 * for the first time buyers. first time buyers are the buyers who
		 * are non-registered users.
		 *
		 * @since 4.3.0
		 * @return void
		 */
		private function getCdRulesHtmlForGuestUsers() {
			$guestCheckoutEnabled = get_option('woocommerce_enable_guest_checkout', 'yes');
			?>
			<div class="panel panel-default">
				<div class="panel-heading" role="tab" id="cd-guest-users">
					<h4 class="panel-title">
						<a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
						<?php esc_html_e('Guest Users', 'customer-specific-pricing-for-woocommerce'); ?>
						</a>
					</h4>
				</div>
				<div id="collapseThree" class="panel-collapse collapse" role="tabpanel" aria-labelledby="cd-guest-users">
					<div class="panel-body">
						<?php
						if ('no'==$guestCheckoutEnabled) {
							esc_html_e('This feature will be available only when guest checkout is enabled in woocommerce settings');
						} else {
							$this->getCdRulesDefinedFor('guestUsers');
							$this->getCdRuleConditionsFor('guestUsers');
							$this->getRuleExclusionsFor('guestUsers');
						}
						?>
					</div>
				</div>
			</div>
			<?php
		}


		/**
		 * Prints an accordion panel to define a cart discount rule
		 * for the first time buyers. first time buyers are the buyers who
		 * are registered users of a store.
		 *
		 * @since 4.3.0
		 * @return void
		 */
		private function getCdRulesHtmlForExistingBuyers() {
			?>
			<div class="panel panel-default">
				<div class="panel-heading" role="tab" id="cd-existing-buyers">
					<h4 class="panel-title">
						<a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
						<?php esc_html_e('Existing Buyers', 'customer-specific-pricing-for-woocommerce'); ?>
						</a>
					</h4>
				</div>
				<div id="collapseTwo" class="panel-collapse collapse" role="tabpanel" aria-labelledby="cd-existing-buyers">
					<div class="panel-body">
						<?php
							$this->getCdRulesDefinedFor('existingBuyers');
							$this->getCdRuleConditionsFor('existingBuyers');
							$this->getRuleExclusionsFor('existingBuyers');
						?>
					</div>
				</div>
			</div>
			<?php
		}

		/**
		 * Prints an accordion panel to define a cart discount rule
		 * for the first time buyers. first time buyers are the buyers who
		 * are registered users of a store but haven made any purchse yet.
		 *
		 * @since 4.3.0
		 * @return void
		 */
		private function getCdRulesHtmlForFirstTimeBuyers() {
			?>
			<div class="panel panel-default">
				<div class="panel-heading" id="cd-first-time-buyers">
					<h4 class="panel-title">
						<a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
						<?php esc_html_e('First Time Buyers', 'customer-specific-pricing-for-woocommerce'); ?>
						</a>
					</h4>
				</div>
				<div id="collapseOne" class="panel-collapse collapse" role="tabpanel" aria-labelledby="cd-first-time-buyers">
					<div class="panel-body">
						<?php
							$this->getCdRulesDefinedFor('firstTimeBuyers');
							$this->getCdRuleConditionsFor('firstTimeBuyers');
							$this->getRuleExclusionsFor('firstTimeBuyers');
						?>
					</div>
				</div>
			</div>
			<?php
		}

		/**
		 * Returns all the exclusions selected for the user type
		 *
		 * @since 4.3.0
		 * @param [type] $userType
		 * @return void
		 */
		private function getExclusionsFor( $userType) {
			$productIds     = get_option('csp_cd_' . $userType . '_excluded_products');
			$catIds         = get_option('csp_cd_' . $userType . '_excluded_product_categories');
			$userIds        = get_option('csp_cd_' . $userType . '_excluded_users');
			$userRoleSlugs  = get_option('csp_cd_' . $userType . '_excluded_user_roles');
			$userGroupIds   = get_option('csp_cd_' . $userType . '_excluded_user_groups');

			return array(
			'productIds'    => !empty($productIds)?$productIds:array(),
			'catIds'        => !empty($catIds)?$catIds:array(),
			'userIds'       => !empty($userIds)?$userIds:array(),
			'userRoleSlugs' => !empty($userRoleSlugs)?$userRoleSlugs:array(),
			'userGroupIds'  => !empty($userGroupIds)?$userGroupIds:array(),
			);
		}

		/**
		 * Fetches & prints all the exclusions selected for the
		 * user type with input boxes & modals to pickup & modify
		 * the exclusions selected.
		 * * Excluded Products
		 * * Excluded Product Categories
		 * * Excluded Users
		 * * Excluded User Roles
		 * * Excluded User Groups
		 *
		 * @since 4.3.0
		 * @param string $userType
		 * @return void
		 */
		private function getRuleExclusionsFor( $userType) {
			$exclusionsHelp = __('Cart discount rules defined above are not applicable on excluded products or categories. 
        Cart Discounts are not applicable if a User, Role, or a Group is excluded.', 'customer-specific-pricing-for-woocommerce');
			$userClass='';
			$exclusions = array();
			switch ($userType) {
				case 'firstTimeBuyers':
					$userClass  ='first-time-buyers';
					$exclusions = $this->getExclusionsFor('ftb');
					break;
				case 'existingBuyers':
					$userClass='existing-buyers';
					$exclusions = $this->getExclusionsFor('exb');
					break;
				case 'guestUsers':
					$userClass='guest-users';
					$exclusions = $this->getExclusionsFor('gu');
					break;
				default:
					break;
			}
			?>
			<div class="rule-exclusions-<?php echo esc_attr($userClass); ?>">
				<div>
					<label>
					<?php esc_html_e('Discount Exclusions ', 'customer-specific-pricing-for-woocommerce'); ?>
					<span class="dashicons dashicons-editor-help" title="<?php echo esc_attr($exclusionsHelp); ?>"></span>
					</label>
				</div>
				<div class="row exclusion-picker-buttons">
					<div class="col-md-2 text-center">
						<button type="button" id="csp-cd-products-exclude-<?php echo esc_attr($userClass); ?>" 
								class="btn btn-default" data-toggle="modal"
								data-target="#modal-prod-ex-<?php echo esc_attr($userClass); ?>">
								<?php esc_html_e('Products', 'customer-specific-pricing-for-woocommerce'); ?>
								<span class="badge" id="selected-ex-product-count-<?php echo esc_attr($userClass); ?>"><?php echo count($exclusions['productIds']); ?></span>
						</button>
						<?php $this->getProdPickerExcludeModal($userClass, $exclusions['productIds']); ?>
					</div>
					<div class="col-md-2 text-center">
						<button type="button" id="csp-cd-cat-exclude-<?php echo esc_attr($userClass); ?>" 
								class="btn btn-default" data-toggle="modal"
								data-target="#modal-cat-ex-<?php echo esc_attr($userClass); ?>">
							<?php esc_html_e('Categories', 'customer-specific-pricing-for-woocommerce'); ?>
							<span class="badge" id="selected-ex-cat-count<?php echo esc_attr($userClass); ?>"><?php echo count($exclusions['catIds']); ?></span>
						</button>
						<?php $this->getCatPickerExcludeModal($userClass, $exclusions['catIds']); ?>
					</div>
					<?php
					if ('guestUsers'==$userType) {
						echo '</div>';
						return true;
					}
					?>
					<div class="col-md-2 text-center">
						<button type="button" id="csp-cd-users-exclude-<?php echo esc_attr($userClass); ?>" 
								class="btn btn-default" data-toggle="modal"
								data-target="#modal-users-ex-<?php echo esc_attr($userClass); ?>">
							<?php esc_html_e('Users', 'customer-specific-pricing-for-woocommerce'); ?>
							<span class="badge" id="selected-ex-users-count<?php echo esc_attr($userClass); ?>"><?php echo count($exclusions['userIds']); ?></span>
						</button>
						<?php $this->getUserPickerExcludeModal($userClass, $exclusions['userIds']); ?>
					</div>
					<div class="col-md-2 text-center">
						<button type="button" id="csp-cd-user-roles-exclude-<?php echo esc_attr($userClass); ?>" 
								class="btn btn-default" data-toggle="modal"
								data-target="#modal-user-roles-ex-<?php echo esc_attr($userClass); ?>">
							<?php esc_html_e('User Roles', 'customer-specific-pricing-for-woocommerce'); ?>
							<span class="badge" id="selected-ex-user-roles-count<?php echo esc_attr($userClass); ?>"><?php echo count($exclusions['userRoleSlugs']); ?></span>
						</button>
						<?php $this->getUserRolePickerExcludeModal($userClass, $exclusions['userRoleSlugs']); ?>
					</div>
					<?php
					if (!defined('GROUPS_CORE_VERSION')) {
						echo '</div></div>';
						return true;
					}
					?>
					<div class="col-md-2 text-center">
						<button type="button" id="csp-cd-user-groups-exclude-<?php echo esc_attr($userClass); ?>" 
								class="btn btn-default" data-toggle="modal"
								data-target="#modal-user-groups-ex-<?php echo esc_attr($userClass); ?>">
							<?php esc_html_e('User Groups', 'customer-specific-pricing-for-woocommerce'); ?>
							<span class="badge" id="selected-ex-user-groups-count<?php echo esc_attr($userClass); ?>"><?php echo count($exclusions['userGroupIds']); ?></span>
						</button>
						<?php $this->getUserGroupPickerExcludeModal($userClass, $exclusions['userGroupIds']); ?>
					</div>
				</div>
			</div>
			<?php
		}

		/**
		 * Fetches conditions,
		 * * Maximum Applicable Discount
		 * * Offer Text
		 * for given user type & prints it in the form of input boxes
		 *
		 * @since 4.3.0
		 * @param string $userType
		 * @return void
		 */
		private function getCdRuleConditionsFor( $userType) {
			$maxDiscountHelp            = __('You can limit the maximum discount that a user can avail per order.', 'customer-specific-pricing-for-woocommerce');
			$shopMoreButtonHelp         = __("This button will be shown on the cart page message & will redirect users to the url you specify in 'Shop More Button Link' below", 'customer-specific-pricing-for-woocommerce');
			$shopMoreButtonLinkHelp     = __('You can change the link to the custom product page link, default is the shop page link', 'customer-specific-pricing-for-woocommerce');
			$beforeOfferTextHelp        = __('This will be the message displayed when cart total reaches close to the discount range.', 'customer-specific-pricing-for-woocommerce');
			$afterOfferTextHelp         = __('This will be the message displayed when cart total gets eligible for the discount & discount gets applied.', 'customer-specific-pricing-for-woocommerce');
			$dynamicFieldsHelpText      = __('This texts will be replaced by the actual values', 'customer-specific-pricing-for-woocommerce');
			$userClass                  = '';
			switch ($userType) {
				case 'firstTimeBuyers':
					$userClass='first-time-buyers';
					$maxDiscount    = get_option('csp_cd_ftb_max_discount_allowed', '');
					$buttonText     = get_option('csp_cd_ftb_shop_button_text', '');
					$shopMoreLink   = get_option('csp_cd_ftb_shop_button_link', '');
					$beforeOfferText= get_option('csp_cd_ftb_before_offer_text', '');
					$afterOfferText = get_option('csp_cd_ftb_after_offer_text', '');
					break;
				case 'existingBuyers':
					$userClass='existing-buyers';
					$maxDiscount    = get_option('csp_cd_exb_max_discount_allowed', '');
					$buttonText     = get_option('csp_cd_exb_shop_button_text', '');
					$shopMoreLink   = get_option('csp_cd_exb_shop_button_link', '');
					$beforeOfferText= get_option('csp_cd_exb_before_offer_text', '');
					$afterOfferText = get_option('csp_cd_exb_after_offer_text', '');
					break;
				case 'guestUsers':
					$userClass='guest-users';
					$maxDiscount    = get_option('csp_cd_gu_max_discount_allowed', '');
					$buttonText     = get_option('csp_cd_gu_shop_button_text', '');
					$shopMoreLink   = get_option('csp_cd_gu_shop_button_link', '');
					$beforeOfferText= get_option('csp_cd_gu_before_offer_text', '');
					$afterOfferText = get_option('csp_cd_gu_after_offer_text', '');
					break;
				default:
					break;
			}
			$buttonText         = ''==$buttonText?$this->defaultButtonText:$buttonText;
			$shopMoreLink       = ''==$shopMoreLink?$this->defaultButtonLink:$shopMoreLink;
			$afterOfferText     = ''==$afterOfferText?$this->defaultafterOfferText:$afterOfferText;
			$beforeOfferText    = ''==$beforeOfferText?$this->defaultbeforeOfferText:$beforeOfferText;
			?>
			<hr class="subrule-seperator">
			<div class="conditions-<?php echo esc_attr($userClass); ?>">
				<!-- Max Discount Value -->
				<div class="row cd-rule-conditions">
					<div class="col-md-4">
						<label for="max-discount-<?php echo esc_attr($userClass); ?>">
						<?php esc_html_e('Maximum Discount Value Allowed ', 'customer-specific-pricing-for-woocommerce'); ?>
						<span class="dashicons dashicons-editor-help" title="<?php echo esc_attr($maxDiscountHelp); ?>"></span>
						</label>
					</div>
					<div class="col-md-6">
						<input type="number" min="0" name="max-discount-<?php echo esc_attr($userClass); ?>" id="max-discount-<?php echo esc_attr($userClass); ?>" value="<?php echo esc_attr($maxDiscount); ?>">
					</div>
				</div>
				<!-- Text For The Shop More Button On Offer Notification -->
				<div class="row cd-rule-conditions">
					<div class="col-md-4">
						<label for="shop-more-btn-<?php echo esc_attr($userClass); ?>">
						<?php esc_html_e('Shop More Button Text ', 'customer-specific-pricing-for-woocommerce'); ?>
						<span class="dashicons dashicons-editor-help" title="<?php echo esc_attr($shopMoreButtonHelp); ?>"></span>
						</label>
					</div>
					<div class="col-md-6">
						<input type="text" min="0" name="shop-more-btn-<?php echo esc_attr($userClass); ?>" id="shop-more-btn-<?php echo esc_attr($userClass); ?>" value="<?php echo esc_attr($buttonText); ?>">
					</div>
				</div>
				<!-- Text Box for the link to the shop page -->
				<div class="row cd-rule-conditions">
					<div class="col-md-4">
						<label for="shop-more-btn-link-<?php echo esc_attr($userClass); ?>">
						<?php esc_html_e('Shop More Button Link ', 'customer-specific-pricing-for-woocommerce'); ?>
						<span class="dashicons dashicons-editor-help" title="<?php echo esc_attr($shopMoreButtonLinkHelp); ?>"></span>
						</label>
					</div>
					<div class="col-md-6">
						<input type="url" min="0" class="url-input" name="shop-more-btn-link-<?php echo esc_attr($userClass); ?>" id="shop-more-btn-link-<?php echo esc_attr($userClass); ?>" value="<?php echo esc_attr($shopMoreLink); ?>">
					</div>
				</div>
				<!-- Text Before Offer Applied-->
				<div class="row cd-rule-conditions">
					<div class="col-md-4">
						<label for="before-offer-text-<?php echo esc_attr($userClass); ?>">
						<?php esc_html_e('Message Before Discount Application ', 'customer-specific-pricing-for-woocommerce'); ?>
						<span class="dashicons dashicons-editor-help" title="<?php echo esc_attr($beforeOfferTextHelp); ?>"></span>
						</label>
					</div>
					<div class="col-md-6">
						<textarea name="before-offer-text-<?php echo esc_attr($userClass); ?>" id="before-offer-text-<?php echo esc_attr($userClass); ?>" cols="40" rows="5"><?php esc_html_e(trim($beforeOfferText)); ?></textarea><br>
						<span class="available-dynamic-fields" title="<?php echo esc_attr($dynamicFieldsHelpText); ?>">
						[difference-amount], [percent-value]</span>
					</div>
				</div>
				<!-- Text After Offer Applied-->
				<div class="row cd-rule-conditions">
					<div class="col-md-4">
						<label for="after-offer-text-<?php echo esc_attr($userClass); ?>">
						<?php esc_html_e('Message After Discount Application ', 'customer-specific-pricing-for-woocommerce'); ?>
						<span class="dashicons dashicons-editor-help" title="<?php echo esc_attr($afterOfferTextHelp); ?>"></span>
						</label>
					</div>
					<div class="col-md-6">
						<textarea name="after-offer-text-<?php echo esc_attr($userClass); ?>" id="after-offer-text-<?php echo esc_attr($userClass); ?>" cols="40"
						rows="5"><?php esc_html_e(trim($afterOfferText)); ?></textarea>
						<br>
						<span class="available-dynamic-fields" title="<?php echo esc_attr($dynamicFieldsHelpText); ?>">
						[percent-value]</span><br>
					</div>
				</div>
			</div>
			<?php
		}

		/**
		 * Fetches all the subrules defined & prints them one after the other
		 * for the provided user type
		 *
		 * @since 4.3.0
		 * @param string $userType
		 * @return void
		 */
		private function getCdRulesDefinedFor( $userType) {
			$userClass='';
			$subRules= array();
			switch ($userType) {
				case 'firstTimeBuyers':
					$userClass='first-time-buyers';
					$subRules = get_option('csp_cd_ftb_subrules', array());
					break;
				case 'existingBuyers':
					$userClass='existing-buyers';
					$subRules = get_option('csp_cd_exb_subrules', array());
					break;
				case 'guestUsers':
					$userClass='guest-users';
					$subRules = get_option('csp_cd_gu_subrules', array());
					break;
				default:
					break;
			}
			$subRules = !empty($subRules)?$subRules:array();
			?>
			<div class="sub-rule-section src-<?php echo esc_attr($userClass); ?>">
				<div class="row">
					<div class="col-md-6">
						<label>
						<?php esc_html_e('Cart Value Ranges', 'customer-specific-pricing-for-woocommerce'); ?>
						<span class="dashicons dashicons-editor-help" title="<?php esc_html_e('You can set the cart discount rules based on cart total amount inside the range', 'customer-specific-pricing-for-woocommerce'); ?>"></span> 
						</label>
					</div>
					</div>
				<div class="row">
					<div class="col-md-2 text-center"> <h6> <?php esc_html_e('Mimimum Cart Value', 'customer-specific-pricing-for-woocommerce'); ?></h6></div>
					<div class="col-md-1 text-center"></div>
					<div class="col-md-2 text-center"><h6><?php esc_html_e('Maximum Cart Value', 'customer-specific-pricing-for-woocommerce'); ?></h6></div>
					<div class="col-md-2 text-center"><h6><?php esc_html_e('% Discount Value', 'customer-specific-pricing-for-woocommerce'); ?></h6></div>
				</div>
			<?php
			$subRuleCount = 0;
			$subRuleCount=count($subRules);
			if (0== $subRuleCount) {
				$this->getCdRangeRow('', '', '', true);
			} else {
				for ($i=0; $i < $subRuleCount-1; $i++) {
					$this->getCdRangeRow($subRules[$i]['min'], $subRules[$i]['max'], $subRules[$i]['discount'], false);
				}
				$this->getCdRangeRow($subRules[$subRuleCount-1]['min'], $subRules[$subRuleCount-1]['max'], $subRules[$subRuleCount-1]['discount'], true);
			}
			?>
			</div>
			<?php
		}

		/**
		 * Prints an HTMl for the range row in the cart discount subrule section
		 * this prints min-max range & discount value inputs
		 *
		 * @since 4.3.0
		 * @param double $min
		 * @param double $max
		 * @param int $discount - 0 to 100
		 * @param boolean $last - when it is the last saved row
		 * @return void
		 */
		private function getCdRangeRow( $min, $max, $discount, $last = false) {
			$emptyMaxValueInfo = $last?$this->noMaxDiscountHelpText:'';
			?>
			<div class="row cd-range-row">
				<div class="col-md-2 text-center">
					<input type="number" class="form-control cd-min-cart-val" placeholder="Min cart value" min="0"  value="<?php echo esc_attr($min); ?>">
				</div>
				<div class="col-md-1 text-center">
					 - 
				</div>
				<div class="col-md-2 text-center" >
					<input type="number" name="" id="" class="form-control cd-max-cart-val" placeholder="Max cart value" min="0" value="<?php echo esc_attr($max); ?>" title="<?php echo esc_attr($emptyMaxValueInfo); ?>">
				</div>
				<div class="col-md-2 text-center">
					<input type="number" name="" id="" max="100" min="0.01" step="0.01" value="<?php echo esc_attr($discount); ?>" placeholder="% discount" class="form-control cd-discount-input">
				</div>
				<div class="col-md-2 text-left add-remove-icons">
					<?php
					if ($last) {
						?>
						<span class="icons cd-remove"  title="Remove this discount range"></span>
						<span class="icons cd-add-new" tabindex="0" title="Add new discount range"></span>
						<?php
					}
					?>
				</div>
				<div class="col-md-3 text-center rule-range-messages">
					<span></span>
				</div>
			</div>
			<?php
		}

		/**
		 * Prints the setting showing features status i.e wether feature is active or not
		 * also prints a switch to activate / deactivate the feature.
		 *
		 * @since 4.3.0
		 * @return null
		 */
		public function featureStatusHtml( $featureSettings) {
			$checked=$featureSettings->getfetureEnabled()=='enable'?'checked':'';
			?>
			<div class="row feature-switch-row ">
				<div class="col-md-4 feature-switch">
					<div class="row">
						<div class="col-md-1">
						</div>
						<div class="col-md-3">
							<label class="switch" title="Enable/Disable Feature">
								<input type="checkbox" id="csp-cd-feature-switch" <?php echo esc_attr($checked); ?> >
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
				<div class="col-md-2 text-right">
					<button class="btn btn-xs btn-secondary" data-toggle="modal" data-target="#modal-feature-settings">
								<span class="dashicons dashicons-admin-generic"></span>
					</button>
					<?php $this->getFeatureSettingsPopUp(); ?>
				</div>
			</div>
			<?php
		}

		/**
		 * Enqueues scripts styles with localized data to be used on the feature page
		 *
		 * @since 4.3.0
		 * */
		private function enqueueStylesScripts() {
			//Bootstrap
			wp_enqueue_style('csp_bootstrap_css', plugins_url('/css/import-css/bootstrap.css', dirname(dirname(__FILE__))), array(), CSP_VERSION);
			wp_enqueue_style('csp_cart_page_css', plugins_url('/css/single-view/cart-discount.css', dirname(dirname(__FILE__))), array(), CSP_VERSION);
			wp_enqueue_script('bootstrap_js', plugins_url('/js/import-js/bootstrap.min.js', dirname(dirname(__FILE__))), array('jquery'), CSP_VERSION, true);
			wp_enqueue_script('csp_cart_discount_js', plugins_url('/js/cart-discount/cart-discount-page.js', dirname(dirname(__FILE__))), array('jquery'), CSP_VERSION);


			$errorMessages = array(
				'all_fields_empty'          => __('Please fill all the fields to add a new rule', 'customer-specific-pricing-for-woocommerce'),
				'discount_not_specified'    => __('Please specify a discount value', 'customer-specific-pricing-for-woocommerce'),
				'max_not_specified'         => __('Please specify Max Discount', 'customer-specific-pricing-for-woocommerce'),
				'min_is_greater_than_max'   => __('Max. cart total should must be greater than min', 'customer-specific-pricing-for-woocommerce'),
				'current_min_less_than_prev_max' => __('Min cart total should must be greater than prev. max cart total', 'customer-specific-pricing-for-woocommerce'),
				'invalid_max_discount'      => __('Please check max discount value entered', 'customer-specific-pricing-for-woocommerce'),
				'empty_discount_value'      => __('Discount value cannot be empty', 'customer-specific-pricing-for-woocommerce'),
				'empty_min_value'           => __('Minimum value can not be empty here', 'customer-specific-pricing-for-woocommerce'),
				'empty_max_value'           => __('Maximum value can not be empty here', 'customer-specific-pricing-for-woocommerce'),
				'wrong_discount_value'      => __('Specify a value between 0 to 100', 'customer-specific-pricing-for-woocommerce'),
			);

			$productDataObject= new WdmCSPUserProductData();
			wp_localize_script(
				'csp_cart_discount_js',
				'wdm_csp_cd_object',
				array(
					'ajax_url' => admin_url('admin-ajax.php'),
					'nonce'    => wp_create_nonce('wdm-csp-cd'),
					'product_list'=> \json_encode($productDataObject->getAllProductsIdNamePairs()),
					'product_cat_list' =>\json_encode($productDataObject->getProductCatSlugPairs()),
					'site_users_list' => \json_encode($productDataObject->getAllSiteUserIdUserNamePairs()),
					'user_roles_list' => \json_encode($productDataObject->getAllUserRoles()),
					'group_id_name_list' => \json_encode($productDataObject->getAllUserGroupIdNamePairs()),
					'guest_checkout_enabled' => get_option('woocommerce_enable_guest_checkout', 'yes'),
					'error_messages'        => $errorMessages,
					'no_max_limit_info'     => $this->noMaxDiscountHelpText,
					)
			);
		}


		/**
		 * Adds a pop up containing a form with global settings for the cart discounts
		 *
		 * @since 4.3.0
		 * @return void
		 */
		public function getFeatureSettingsPopUp() {
			?>
			<!-- Modal -->
			<div class="modal fade" id="modal-feature-settings" role="dialog">
				<div class="modal-dialog">
					<!-- Modal content-->
					<div class="modal-content">
						<div class="modal-header text-center">
							<button type="button" class="close" data-dismiss="modal">&times;</button>
							<h4 class="modal-title"><?php esc_html_e('Cart Discount Settings', 'customer-specific-pricing-for-woocommerce'); ?></h4>
						</div>
						<div class="modal-body text-left">
						<div class="row">
							<div class="col-md-6">
							<label for="percentThreshold">
								<?php
								esc_html_e('Cart Discount Message Threshold ', 'customer-specific-pricing-for-woocommerce');
								?>
							</label>
							<span class="dashicons dashicons-editor-help" title="
							<?php
							esc_attr_e("Set a percentage of 'Minimum Cart Value' after which  the 'Cart Discount Message' is displayed.", 'customer-specific-pricing-for-woocommerce');
							?>
							"></span>
							<div class="feature-settings-help-text">
							<?php
							esc_html_e("(Eg- If $200 is a 'Minimum Cart Value' and 90% is set as a threshold percentage, then 'Cart Discount Message' will be displayed if total cart value is equal to or above $180.)", 'customer-specific-pricing-for-woocommerce');
							?>
							</div>
							</div>
							<div class="col-md-3">
								<input type="number" min="0" max="100" step="0.01" id="percentThreshold"
								name="percentThreshold" class="form-control"
								value="<?php echo esc_attr($this->featureSettings->getCDThresholdPercent()); ?>"
								>
							</div>
						</div>
						<br>
						<div class="row hidden">
							<div class="col-md-6">
							<label for="minMaxDiscount">
								<?php
								esc_html_e('Discount Rule to apply', 'customer-specific-pricing-for-woocommerce');
								?>
							</label>
							<span class="dashicons dashicons-editor-help" title="
							<?php
							esc_attr_e('This is to select wether to apply the rule giving max. discount or a rule giving min discount on a Order', 'customer-specific-pricing-for-woocommerce');
							?>
							"></span>
							</div>
							<div class="col-md-6">
								<select type="number" min="0" max="100" step="0.01" id="minMaxDiscount"
								name="minMaxDiscount" class="form-control"
								>
									<?php
										$min ='';
										$max ='';
									if ('min'==$this->featureSettings->getMinOrMaxCDtoApply()) {
										$min='selected';
									} else {
										$max = 'selected';
									}
									?>
									<option value="min" <?php echo esc_attr($min); ?>><?php esc_html_e('Minimum', 'customer-specific-pricing-for-woocommerce'); ?></option>
									<option value="max" <?php echo esc_attr($max); ?>><?php esc_html_e('Maximum', 'customer-specific-pricing-for-woocommerce'); ?></option>
								</select>
							</div>
						</div>
						<div class="row text-right buttons-div">
							<div class="col-md-6"> </div>
							<div class="col-md-3 saving_animation">
								<button class="cd_save_status btn btn-link"><?php esc_html_e('Settings Saved', 'customer-specific-pricing-for-woocommerce'); ?></span>
							</div>
							<div class="col-md-3">
								<button type="submit" class="btn btn-primary" id="saveFeatureSettings">
								<?php esc_html_e('Save Settings', 'customer-specific-pricing-for-woocommerce'); ?>
								</button>
							</div>
						</div>
						</div>
					</div>
				</div>
			</div>
			<?php
		}
		


		/**
		 * Includes the modal used to pick products to be excluded
		 *
		 * @since 4.3.0
		 * @return void
		 */
		public function getProdPickerExcludeModal( $userClass, $selected) {
			$selectCount = count($selected);
			?>
			<!-- Modal -->
			<div class="modal csp-cd-modal fade" id="modal-prod-ex-<?php echo esc_attr($userClass); ?>" role="dialog">
				<div class="modal-dialog">
					<!-- Modal content-->
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="csp-modal-cancel close" data-dismiss="modal">&times;</button>
							<h4 class="modal-title"><?php esc_html_e('Select Products To Exclude', 'customer-specific-pricing-for-woocommerce'); ?></h4>
						</div>
						<div class="modal-body">
							<div class="row">
								<div class="col-md-10 text-left"><h5><?php esc_html_e('Selected Products', 'customer-specific-pricing-for-woocommerce'); ?></h5></div>
								<div class="col-md-2 text-right">
									<span class="exclude_count badge badge-pill badge-secondary"><?php echo esc_html($selectCount); ?></span>
								</div>
							</div>
							<div class="csp-cd-selected-products selected">
								<?php
								$this->printSelections($selected);
								?>
							</div>
							<hr>
							<?php $this->getElementPicker(); ?>
						</div>
					</div>
				</div>
			</div>
			<?php
		}



		/**
		 * Includes the modal to pickup the categories to be excluded in the rule
		 *
		 * @since 4.3.0
		 * @return void
		 */
		public function getCatPickerExcludeModal( $userClass, $selected) {
			$selectCount = count($selected);
			?>
			<!-- Modal -->
			<div class="modal csp-cd-modal fade" id="modal-cat-ex-<?php echo esc_attr($userClass); ?>" role="dialog">
				<div class="modal-dialog">
					<!-- Modal content-->
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="csp-modal-cancel close" data-dismiss="modal">&times;</button>
							<h4 class="modal-title"><?php esc_html_e('Select Product Categories To Exclude', 'customer-specific-pricing-for-woocommerce'); ?></h4>
						</div>
						<div class="modal-body">
						<div class="row">
							<div class="col-md-10 text-left"><h5>
								<?php esc_html_e('Selected Product Categories', 'customer-specific-pricing-for-woocommerce'); ?></h5></div>
							<div class="col-md-2 text-right">
								<span class="exclude_count badge badge-pill badge-secondary"><?php echo esc_html($selectCount); ?></span>
							</div>
						</div>
							<div class="csp-cd-selected-product_cat selected">
								<?php
								$this->printSelections($selected);
								?>
							</div>
							<hr>
							<?php $this->getElementPicker('category'); ?>
						</div>
					</div>
				</div>
			</div>
			<?php
		}


		/**
		 * Includes the modal to pickup the categories to be excluded in the rule
		 *
		 * @since 4.3.0
		 * @return void
		 */
		public function getUserPickerExcludeModal( $userClass, $selected) {
			$selectCount = count($selected);
			?>
			<!-- Modal -->
			<div class="modal csp-cd-modal fade" id="modal-users-ex-<?php echo esc_attr($userClass); ?>" role="dialog">
				<div class="modal-dialog">
					<!-- Modal content-->
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="csp-modal-cancel close" data-dismiss="modal">&times;</button>
							<h4 class="modal-title"><?php esc_html_e('Select users to exclude from getting discounts', 'customer-specific-pricing-for-woocommerce'); ?></h4>
						</div>
						<div class="modal-body">
						<div class="row">
							<div class="col-md-10 text-left"><h5>
								<?php esc_html_e('Selected Users', 'customer-specific-pricing-for-woocommerce'); ?></h5></div>
							<div class="col-md-2 text-right">
								<span class="exclude_count badge badge-pill badge-secondary"><?php echo esc_html($selectCount); ?></span>
							</div>
						</div>
							<div class="csp-cd-selected-users selected">
								<?php
								$this->printSelections($selected);
								?>
							</div>
							<hr>
							<?php $this->getElementPicker('users'); ?>
						</div>
					</div>
				</div>
			</div>
			<?php
		}

		/**
		 * Includes the modal to pickup the categories to be excluded in the rule
		 *
		 * @since 4.3.0
		 * @return void
		 */
		public function getUserRolePickerExcludeModal( $userClass, $selected) {
			$selectCount = count($selected);
			?>
			<!-- Modal -->
			<div class="modal csp-cd-modal fade" id="modal-user-roles-ex-<?php echo esc_attr($userClass); ?>" role="dialog">
				<div class="modal-dialog">
					<!-- Modal content-->
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="csp-modal-cancel close" data-dismiss="modal">&times;</button>
							<h4 class="modal-title"><?php esc_html_e('Select user roles to exclude from getting discounts', 'customer-specific-pricing-for-woocommerce'); ?></h4>
						</div>
						<div class="modal-body">
						<div class="row">
							<div class="col-md-10 text-left"><h5>
								<?php esc_html_e('Selected User Roles', 'customer-specific-pricing-for-woocommerce'); ?></h5></div>
							<div class="col-md-2 text-right">
								<span class="exclude_count badge badge-pill badge-secondary"><?php echo esc_html($selectCount); ?></span>
							</div>
						</div>
							<div class="csp-cd-selected-user-roles selected">
								<?php
								$this->printSelections($selected);
								?>
							</div>
							<hr>
							<?php $this->getElementPicker('user-roles'); ?>
						</div>
					</div>
				</div>
			</div>
			<?php
		}

		/**
		 * Includes the modal to pickup the categories to be excluded in the rule
		 *
		 * @since 4.3.0
		 * @return void
		 */
		public function getUserGroupPickerExcludeModal( $userClass, $selected) {
			$selectCount = count($selected);
			?>
			<!-- Modal -->
			<div class="modal csp-cd-modal fade" id="modal-user-groups-ex-<?php echo esc_attr($userClass); ?>" role="dialog">
				<div class="modal-dialog">
					<!-- Modal content-->
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="csp-modal-cancel close" data-dismiss="modal">&times;</button>
							<h4 class="modal-title"><?php esc_html_e('Select user groups to exclude from getting discounts', 'customer-specific-pricing-for-woocommerce'); ?></h4>
						</div>
						<div class="modal-body">
						<div class="row">
							<div class="col-md-10 text-left"><h5>
								<?php esc_html_e('Selected User Groups', 'customer-specific-pricing-for-woocommerce'); ?></h5></div>
							<div class="col-md-2 text-right">
								<span class="exclude_count badge badge-pill badge-secondary"><?php echo esc_html($selectCount); ?></span>
							</div>
						</div>
							<div class="csp-cd-selected-user-groups selected">
								<?php
								$this->printSelections($selected);
								?>
							</div>
							<hr>
							<?php $this->getElementPicker('user-groups'); ?>
						</div>
					</div>
				</div>
			</div>
			<?php
		}


		/**
		 * Prints the selected elements in the form of buttons
		 *
		 * @since 4.3.0
		 * @param [type] $selected
		 * @return void
		 */
		private function printSelections( $selected) {
			foreach ($selected as $element) {
				?>
			<button class="btn btn-default btn-sm btn-product-label" pid="<?php echo esc_attr($element['id']); ?>">
				<?php
				echo esc_html(trim($element['name']));
				?>
			</button>
				<?php
			}
		}


		/**
		 * This method prints the product/category picker form in the modal
		 * according to the passed parameter
		 *
		 * @since 4.3.0
		 * @param string $object - "Product/Category"
		 * @return void
		 */
		public function getElementPicker( $object = 'product') {
			$placeholder = __('Search', 'customer-specific-pricing-for-woocommerce');
			?>
				<!-- <form role="form"> -->
					<div class="form-group">
						<input type="input" class="txt-search-<?php echo esc_attr($object); ?> form-control input-sm"
								placeholder="<?php echo esc_attr($placeholder); ?>">
					</div>
				<!-- </form> -->
				<div class="filter-records"></div>
				<div class="csp-modal-buttons">
				<button class="btn btn-primary" data-dismiss="modal"><?php esc_html_e('Save & close', 'customer-specific-pricing-for-woocommerce'); ?></button>
				<button class="btn btn-secondary csp-modal-cancel" data-dismiss="modal"><?php esc_html_e('Cancel', 'customer-specific-pricing-for-woocommerce'); ?></button>
				</div>
			<?php
		}
	}
}
