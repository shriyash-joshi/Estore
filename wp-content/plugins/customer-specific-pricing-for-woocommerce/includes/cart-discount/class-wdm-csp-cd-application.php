<?php

namespace CSPCartDiscount;

if (!class_exists('WdmCSPCartDiscountApplication')) {

	class WdmCSPCartDiscountApplication {
	
		private $featureSettings;
		private $discountRules;
		private $congratulationsText;
		private $beforeOfferText;
		private $userType;
		private $maxDiscount;
		private $productsExcluded;
		private $catsExcluded;
		private $subRules;
		private $closestRuleToTheEligibleCartTotal;
		public function __construct() {
			include_once('data-store/class-feature-settings.php');
			include_once('data-store/cart-discount-rules.php');
			$this->featureSettings= new Settings\WdmCSPCDSettings();
			$this->discountRules = new WdmCspCdRules();
			
			if ($this->featureSettings->getfetureEnabled()=='enable') {
				add_action('woocommerce_cart_calculate_fees', array($this,'applyOfferIfApplicable'), 10);
			}
		}

		
		/**
		 * Display the offer notification on the cart page
		 * * Check if the cart containts & total is close to the any cart discount rule stored and active
		 * * Display the offer text mentioned in the rule on the cart page
		 * * Display the amount required to be added in order to get eligible for the offer
		 *
		 * @since 4.3.0
		 * @return void
		 */
		public function applyOfferIfApplicable() {
			$this->subRules = array();
			$userType   = $this->getCurrentUserType();
			$this->userType = $userType;
			$eligible   = $this->userEligibleForTheOffer($userType);
			if ('eligible'!=$eligible) {
				return false;
			}
			//calculate cart total accordding to eligible products
			$items     = $this->extractCartItemsDataForDiscountAnalysis(WC()->cart->get_cart());
			$excludedProducts   = $this->getExcludedProducts($userType);
			$excludedCategories = $this->getExcludedCategoryIds($userType);
			$cartTotalEligibleForDiscount=0;
			foreach ($items as $item) {
				if (in_array($item['id'], $excludedProducts)) {
					continue;
				}
				if (!empty(array_intersect($item['catIds'], $excludedCategories))) {
					continue ;
				}

				$cartTotalEligibleForDiscount += $item['subTotal'];
			}

			$discountRules = $this->getApplicableSubRules($userType);

			if (empty($discountRules) || 0==$cartTotalEligibleForDiscount) {
				return false;
			}
			wp_enqueue_style('csp_cd_offer_css', plugins_url('/css/single-view/cart-discounts-offer-page.css', dirname(dirname(__FILE__))), array(), CSP_VERSION);
			wp_enqueue_script('csp-cd-modal-js', plugins_url('/js/cart-discount/cart-discount-modal.js', dirname(dirname(__FILE__))), array('jquery'), CSP_VERSION, true);
			$cdOfferText = $this->getOfferText($userType);
			$maxDiscount = $this->getMaxDiscount($userType);
			foreach ($discountRules as $aRule) {
				if ($cartTotalEligibleForDiscount >= $aRule['min']) {
					if (!is_numeric($aRule['max']) || $aRule['max']>=$cartTotalEligibleForDiscount) {
						$congratulationsText = $this->getCongratulationsText($userType);
						$this->congratulationsText = str_replace('[percent-value]', $aRule['discount'] . '%', $congratulationsText);
						add_action('woocommerce_before_cart', array($this,'showCongratulationsMessage'), 99);
 
						$discountValue = $cartTotalEligibleForDiscount * ( $aRule['discount']/100 );
						if (is_numeric($maxDiscount)) {
							$discountValue = $maxDiscount<$discountValue ? $maxDiscount : $discountValue;
						}
					
						$discountText = apply_filters('csp-cd-filter-cart-discount-label-text', 'Cart Discount', $cdOfferText, $aRule);
						WC()->cart->add_fee($discountText, -$discountValue);
						return true;
					}
				}
			}

			$cdThreshold=$this->featureSettings->getCDThresholdPercent();
			if (0==$cdThreshold) {
				return false;
			}
			//show the notice
			$closestMin=$this->getAMinTotalClosestTo($cartTotalEligibleForDiscount, $discountRules);
			if ($closestMin) {
				$cartTotalWithThreshold = $closestMin * $cdThreshold / 100;
				if ($cartTotalEligibleForDiscount>= $cartTotalWithThreshold) {
					$differance = $closestMin - $cartTotalEligibleForDiscount;

					$closestDiscount    = $this->closestRuleToTheEligibleCartTotal['discount'];
					$offerText          = $this->getOfferText($userType);
					$offerText          = str_replace('[difference-amount]', wc_price($differance), $offerText);
					$this->beforeOfferText    = str_replace('[percent-value]', $closestDiscount . '%', $offerText);
					add_action('woocommerce_before_cart', array($this,'showOfferMessage'), 99);
				}
			}
		}

		/**
		 * This method displays the Cart page notice/notification when
		 * the cart value is eligible for the discounts
		 * & a popup containing the offer details such as included/excluded
		 * products, categories. maximum discount value etc.
		 *
		 * @since 4.3.0
		 */
		public function showCongratulationsMessage() {
			$buttonText     = $this->getButtonText();
			$shopMoreLink   = $this->getShopLink();
			$allowedHtml=   array(
				'div'	=> array(
								'role'=>true,
								'class'=>true),
				'span'	=> array(
								'id'=>true,
								'onclick'=>true),
				'a'		=> array(
								'href'=>true),
				'button'=> array(
					'class'=>true),
			);


			$offerHtml      =   '<div class="woocommerce-message csp-cd-congrats" role="notice">
                                ' . $this->congratulationsText . ' 
                                <span id="csp-cd-more" onclick="cspcdOpenOfferModal()">' . __('See More', 'customer-specific-pricing-for-woocommerce') . '</span> 
                                <span id="csp-cd-shop-more">
                                <a href="' . $shopMoreLink . '"><button class="csp-cd-btn-shop-more">' . $buttonText . '</button></a>
                                </span>';
			echo wp_kses($offerHtml, $allowedHtml);
			?>
				<?php $this->printOfferModalContent(); ?>
			</div>
			<?php
		}


		/**
		 * This method displays the Cart page notice/notification when
		 * the cart value is close to the value with the discounts
		 * & a popup containing the offer details such as included/excluded
		 * products, categories. maximum discount value etc.
		 *
		 * @since 4.3.0
		 */
		public function showOfferMessage() {
			$buttonText     = $this->getButtonText();
			$shopMoreLink   = $this->getShopLink();
			$allowedHtml=   array(
				'div'	=> array(
								'role'=>true,
								'class'=>true),
				'span'	=> array(
								'id'=>true,
								'onclick'=>true),
				'a'		=> array(
								'href'=>true),
				'button'=> array(
					'class'=>true),
			);
			$offerHtml      =   '<div class="woocommerce-message csp-cd-offer" role="notice">
                                ' . $this->beforeOfferText . ' 
                                <span id="csp-cd-more" onclick="cspcdOpenOfferModal()">' . __('See More', 'customer-specific-pricing-for-woocommerce') . '</span> 
                                <span id="csp-cd-shop-more">
                                    <a href="' . $shopMoreLink . '"><button class="csp-cd-btn-shop-more-offer">' . $buttonText . '</button></a>
                                </span>';
			echo wp_kses($offerHtml, $allowedHtml);
			?>
				<?php $this->printOfferModalContent(); ?>
			</div>
			<?php
		}



		/**
		 * Prints the modal & content to be displayed in the offer modal,
		 * * Discounts with ranges.
		 * * Excluded products if any.
		 * * Excluded categories if any.
		 * * Maximum applicable discount.
		 *
		 * @return void
		 */
		private function printOfferModalContent() {
			$allowedHtml=   array(
								'span'	=> array(
												'class'=>true)
						);
			?>
			<!-- The Modal -->
			<div id="cd-offers-modal" class="csp-cd-modal" style="display:none;">
			<!-- Modal content -->
			<div class="csp-cd-modal-content">
				  <div>
				  <span class="csp-cd-modal-close" onclick="cspcdCloseOfferModal()">&times;</span>
				  <span><?php esc_html_e('Offer Details', 'customer-specific-pricing-for-woocommerce'); ?></span>
				  </div>
				  <div class="csp-cd-offer-modal-content">
					<?php
					echo '<ul>';
					foreach ($this->subRules as $aSubRule) {
						$min = wc_price($aSubRule['min']);
						$max = is_numeric($aSubRule['max'])? 'to ' . wc_price($aSubRule['max']): 'and more';
						/* translators: %1$s%%: value of applicable % discount %2$s:minimum cart total required %3$s:Max. cart total text */
						$message = sprintf(__('Get %1$s%% discount on a cart value %2$s %3$s', 'customer-specific-pricing-for-woocommerce'), number_format((float) $aSubRule['discount'], 2, '.', '')+0, $min, $max);
						echo '<li>' . wp_kses($message, $allowedHtml) . '</li>';
					}
					echo '</ul>';
					if (is_numeric($this->maxDiscount) && apply_filters('csp-cd-mention-max-discount-applicable', true)) {
						echo '<span>';
						esc_html_e('Maximum applicable discount : ', 'customer-specific-pricing-for-woocommerce');
						echo wp_kses(wc_price($this->maxDiscount), $allowedHtml);
						echo '</span><br>';
					}
					?>
					<span class="csp-cd-tc">
					<?php
					$exProducts     = $this->getNamesFromExclusionLists($this->productsExcluded);
					$exProductCats  = $this->getNamesFromExclusionLists($this->catsExcluded);
					if (( !empty($exProducts) || !empty($exProductCats) ) && apply_filters('csp-cd-mention-exclusions', true)) {
						echo wp_kses(__('Discount will be applicable on the total of eligible products. following products & categories are <strong>not applicabe</strong> for the discounts<br>', 'customer-specific-pricing-for-woocommerce'), array('strong'=>true,'br'=>true));
						if (!empty($exProducts)) {
							echo '<strong>';
							esc_html_e('Products : ', 'customer-specific-pricing-for-woocommerce');
							echo '</strong>';
							echo esc_html(implode(', ', $exProducts)) . '. ';
						}
						if (!empty($exProductCats)) {
							echo '<strong>';
							esc_html_e('Product Categories : ', 'customer-specific-pricing-for-woocommerce');
							echo '</strong>';
							echo esc_html(implode(', ', $exProductCats)) . '.';
						}
					}
					?>

					</span>
					<span>
						<?php do_action('csp_add_custom_message_in_cart_discount_modal'); ?>
					</span>
				  </div>
				</div>
			</div>
			<?php
		}

		/**
		 * Returns the minimum cart value in the rules which is greater than
		 * the eligible cart total & closest to the current eligible cart total.
		 * * returns false - if eligible cart total > all the minimum totals
		 *
		 * @since 4.3.0
		 * @param double $theEligibleCartTotal
		 * @param array $rules
		 */
		private function getAMinTotalClosestTo( $theEligibleCartTotal, $rules) {
			$closestMin=false;
			$closestRule = array();
			foreach ($rules as $aRule) {
				if (is_numeric($aRule['min']) && $theEligibleCartTotal < $aRule['min']) {
					if (empty($closestMin)) {
						$closestMin  = $aRule['min'];
						$closestRule = $aRule;
						continue;
					}
					$closestMin = $closestMin>$aRule['min']?$aRule['min']:$closestMin;
					$closestRule= $closestMin>$aRule['min']?$aRule:$closestRule;
				}
			}
			$this->closestRuleToTheEligibleCartTotal = $closestRule;
			return $closestMin;
		}


		/**
		 * This method iterates through cart items & gives minimal data required
		 * to perform discount calculations of the cart items.
		 *
		 * @since 4.3.0
		 * @param array $itemsInACart - array of objects in the cart
		 * @return array $items - minimal data of the cart elements required for the operation
		 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
		 **/
		private function extractCartItemsDataForDiscountAnalysis( $itemsInACart) {
			$items = array();
			
			foreach ($itemsInACart as $key => $cartItem) {
				if (!in_array($cartItem['data']->post_type, array('product', 'product_variation'))) {
					continue;
				}
				
				$item = array();
				$productId = ( 0!=$cartItem['variation_id'] )?$cartItem['variation_id']:$cartItem['product_id'];
				$item['id'] = $productId;
				$catIds =array();
				$catIds = $cartItem['data']->get_category_ids();
				if ('product_variation'==$cartItem['data']->post_type) {
					$terms = get_the_terms($cartItem['product_id'], 'product_cat');
					foreach ($terms as $term) {
							$catIds[] = $term->term_id;
					}
				}
				$item['catIds']     = $catIds;
				$item['subTotal']   = $cartItem['line_subtotal'];
				$items[]=$item;
			}
			return $items;
		}

		/**
		 * Checks if the current user is logged in
		 * if the customer have past orders returns everyone
		 * if the customer is a first time buyer on the site returns 'first-time-user'
		 *
		 * @since 4.3.0
		 * @return $userType : not-logged-in|first-time-buyer|everyone
		 */
		public function getCurrentUserType() {
			$userType='guest-user';
			if (is_user_logged_in()) {
				$userType='first-time-buyer';
				$customerOrders = get_posts(array(
					'numberposts' => 1,
					'meta_key'    => '_customer_user',
					'meta_value'  => get_current_user_id(),
					'post_type'   => wc_get_order_types(),
					'post_status' => array_keys(wc_get_order_statuses()),
				));

				if (count($customerOrders)>0) {
					$userType='existing-buyer';
				}
			}
			return $userType;
		}


		/**
		 * Gets the current users type and makes a decision
		 * wether the current user is eligible for the discounts or not.
		 * using the user, role & group exclusions defined by the admin
		 *
		 * @since 4.3.0
		 * @param string $userType
		 * @return string - 'eligible' Or 'not_eligible'
		 */
		private function userEligibleForTheOffer( $userType) {
			switch ($userType) {
				case 'first-time-buyer':
					$userId         = get_current_user_id();
					$groupIds       = $this->getGroupIdsForTheUser($userId);
					$userRoles      = $this->getUserRoles();
					$excludedGroups = $this->getExcludedGroups('ftb');
					$excludedRoles  = $this->getExcludedRoles('ftb');
					$excludedUsers  = $this->getExcludedUsers('ftb');
					
					if (in_array($userId, $excludedUsers)) {
						return 'not_eligible';
					}

					if (!empty(array_intersect($groupIds, $excludedGroups))) {
						return 'not_eligible';
					}

					if (!empty(array_intersect($userRoles, $excludedRoles))) {
						return 'not_eligible';
					}
					break;
				case 'existing-buyer':
					$userId         = get_current_user_id();
					$groupIds       = $this->getGroupIdsForTheUser($userId);
					$userRoles      = $this->getUserRoles();
					$excludedGroups = $this->getExcludedGroups('exb');
					$excludedRoles  = $this->getExcludedRoles('exb');
					$excludedUsers  = $this->getExcludedUsers('exb');
					
					if (in_array($userId, $excludedUsers)) {
						return 'not_eligible';
					}
				
					if (!empty(array_intersect($groupIds, $excludedGroups))) {
						return 'not_eligible';
					}
				
					if (!empty(array_intersect($userRoles, $excludedRoles))) {
						return 'not_eligible';
					}
					break;
				case 'guest-user':
					break;
				default:
					# code...
					break;
			}
			return 'eligible';
		}


		/**
		 * Returns the group ids to which the user belong
		 *
		 * @since 4.3.0
		 * @param int $userId
		 * @return array $groupIds
		 */
		private function getGroupIdsForTheUser( $userId) {
			$groupIds = array();
			if (!is_user_logged_in() || !defined('GROUPS_CORE_VERSION')) {
				return $groupIds;
			}
			global $wpdb;
			$userGroupId        = $wpdb->get_results($wpdb->prepare('SELECT group_id FROM ' . $wpdb->prefix . 'groups_user_group WHERE user_id=%d', $userId));
			foreach ($userGroupId as $groupId) {
				$groupIds[] = $groupId->group_id;
			}
			return $groupIds;
		}

		/**
		 * Returns an array of role sugs to which the current user belongs
		 *
		 * @since 4.3.0
		 * @return array - user roles
		 */
		private function getUserRoles() {
			$user = wp_get_current_user();
			return $user->roles;
		}
	
		/**
		 * Returns an array of group ids which are exluded for the user type
		 * by admin
		 *
		 * @since 4.3.0
		 * @param [type] $userType
		 * @return void
		 */
		private function getExcludedGroups( $userType) {
			$groupIds = array();
			$excludeList = get_option('csp_cd_' . $userType . '_excluded_user_groups');
			if (!empty($excludeList)) {
				foreach ($excludeList as $item) {
					$groupIds[] = $item['id'];
				}
			}
			return $groupIds;
		}

		/**
		 * Returns an array of role ids which are exluded for the user type
		 * by admin.
		 *
		 * @since 4.3.0
		 * @param [type] $userType
		 * @return void
		 */
		private function getExcludedRoles( $userType) {
			$roleSlugs = array();
			$excludeList = get_option('csp_cd_' . $userType . '_excluded_user_roles');
			if (!empty($excludeList)) {
				foreach ($excludeList as $item) {
					$roleSlugs[] = $item['id'];
				}
			}
			return $roleSlugs;
		}

		/**
		 * Returns an array of user ids which are not allowed
		 * to get cart discount by admins
		 *
		 * @since 4.3.0
		 * @param string $userType
		 * @return array - user ids
		 */
		private function getExcludedUsers( $userType) {
			$userIds = array();
			$excludeList = get_option('csp_cd_' . $userType . '_excluded_users');
			if (!empty($excludeList)) {
				foreach ($excludeList as $item) {
					$userIds[] = $item['id'];
				}
			}
			return $userIds;
		}


		/**
		 * Returns an array of excluded product ids for the user type
		 *
		 * @since 4.3.0
		 * @param string $userType
		 * @return array - excluded product ids
		 */
		private function getExcludedProducts( $userType) {
			switch ($userType) {
				case 'first-time-buyer':
					$userType = 'ftb';
					break;
				case 'existing-buyer':
					$userType = 'exb';
					break;
				case 'guest-user':
					$userType = 'gu';
					break;
			}
			$productIds = array();
			$excludeList = get_option('csp_cd_' . $userType . '_excluded_products');
			$this->productsExcluded = $excludeList;
			if (!empty($excludeList)) {
				foreach ($excludeList as $item) {
					$productIds[] = $item['id'];
				}
			}
			return $productIds;
		}


		/**
		 * Returns an array of excluded product category ids
		 * for the user type
		 *
		 * @since 4.3.0
		 * @param string $userType
		 * @return array - category Ids
		 */
		private function getExcludedCategoryIds( $userType) {
			switch ($userType) {
				case 'first-time-buyer':
					$userType = 'ftb';
					break;
				case 'existing-buyer':
					$userType = 'exb';
					break;
				case 'guest-user':
					$userType = 'gu';
					break;
			}
			$catIds = array();
			$excludeList = get_option('csp_cd_' . $userType . '_excluded_product_categories');
			$this->catsExcluded = $excludeList;
			if (!empty($excludeList)) {
				foreach ($excludeList as $item) {
					$catIds[] = $item['id'];
				}
			}
			return $catIds;
		}


		/**
		 * Returns an array of min-max prices ranges
		 * & discounts created for usertype
		 *
		 * @since 4.3.0
		 * @param [type] $userType
		 * @return array - [min,max,discount]
		 */
		private function getApplicableSubRules( $userType) {
			switch ($userType) {
				case 'first-time-buyer':
					$userType = 'ftb';
					break;
				case 'existing-buyer':
					$userType = 'exb';
					break;
				case 'guest-user':
					$userType = 'gu';
					break;
			}

			$rules = get_option('csp_cd_' . $userType . '_subrules');
			$rules = apply_filters('csp_filter_cart_discount_limits', $rules);
			$this->subRules = $rules;
			return $rules;
		}


		/**
		 * Returns the offer text defined for the current users user type
		 * by admin
		 *
		 * @since 4.3.0
		 * @param string $userType
		 * @return string $offerText, Offer Text
		 */
		private function getCongratulationsText( $userType) {
			switch ($userType) {
				case 'first-time-buyer':
					$userType = 'ftb';
					break;
				case 'existing-buyer':
					$userType = 'exb';
					break;
				case 'guest-user':
					$userType = 'gu';
					break;
			}

			$offerText = get_option('csp_cd_' . $userType . '_after_offer_text');
			return $offerText;
		}

				/**
		 * Returns the offer text defined for the current users user type
		 * by admin
		 *
		 * @since 4.3.0
		 * @param string $userType
		 * @return string $offerText, Offer Text
		 */
		private function getOfferText( $userType) {
			switch ($userType) {
				case 'first-time-buyer':
					$userType = 'ftb';
					break;
				case 'existing-buyer':
					$userType = 'exb';
					break;
				case 'guest-user':
					$userType = 'gu';
					break;
			}

			$offerText = get_option('csp_cd_' . $userType . '_before_offer_text');
			return $offerText;
		}

		/**
		 * Get max discount allowed for the user type,
		 *
		 * @since 4.3.0
		 * @param string $userType
		 * @return double - returns double or empty value
		 */
		private function getMaxDiscount( $userType) {
			switch ($userType) {
				case 'first-time-buyer':
					$userType = 'ftb';
					break;
				case 'existing-buyer':
					$userType = 'exb';
					break;
				case 'guest-user':
					$userType = 'gu';
					break;
			}

			$maxDiscount = get_option('csp_cd_' . $userType . '_max_discount_allowed');
			$maxDiscount = apply_filters('wdm_csp_convert_price_value', $maxDiscount);
			$this->maxDiscount = $maxDiscount;
			return $maxDiscount;
		}


		/**
		 * Extract names from the array of id-name pairs
		 * and returns them as an array.
		 *
		 * @since 4.3.0
		 * @param array $exclusionsList - array of id-name pairs
		 * @return array $names
		 */
		private function getNamesFromExclusionLists( $exclusionsList) {
			$names = array();
			if (!empty($exclusionsList)) {
				foreach ($exclusionsList as $anExclusion) {
					$names[] = $anExclusion['name'];
				}
			}
			return $names;
		}


		/**
		 * Get "shop more" button text for the user type
		 */
		private function getButtonText() {
			$defaultShopButtonText= __('Continue Shopping', 'customer-specific-pricing-for-woocommerce');
			switch ($this->userType) {
				case 'first-time-buyer':
					$userType = 'ftb';
					break;
				case 'existing-buyer':
					$userType = 'exb';
					break;
				case 'guest-user':
					$userType = 'gu';
					break;
			}

			$buttonText = get_option('csp_cd_' . $userType . '_shop_button_text', $defaultShopButtonText);
		
			return $buttonText;
		}


		/**
		 * Get "shop more" button text for the user type
		 */
		private function getShopLink() {
			$defaultShopButtonText= get_permalink(wc_get_page_id('shop'));
			switch ($this->userType) {
				case 'first-time-buyer':
					$userType = 'ftb';
					break;
				case 'existing-buyer':
					$userType = 'exb';
					break;
				case 'guest-user':
					$userType = 'gu';
					break;
			}

			$buttonLink = get_option('csp_cd_' . $userType . '_shop_button_link', $defaultShopButtonText);
		
			return $buttonLink;
		}
	}
}
