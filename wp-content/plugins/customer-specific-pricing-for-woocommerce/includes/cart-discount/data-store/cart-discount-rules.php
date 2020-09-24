<?php
namespace CSPCartDiscount;

if (!class_exists('WdmCspCdRules')) {
   
	class WdmCspCdRules {
	
		//private $rules;
		private $tableName;

		public function __construct() {
			global $wpdb;
			$this->tableName=$wpdb->prefix . 'wcsp_cart_discount_rules';
			include_once('cart-discount-rule.php');
			//Save a new rule to the database
			add_action('wp_ajax_nopriv_save_rule_data', array($this, 'saveRuleDataAjax'));
			add_action('wp_ajax_save_rule_data', array($this, 'saveRuleDataAjax'));
		}
	

		/**
		 * Validates the rule data sent to be saved in the database
		 * returns an array with the validation status & erros when rule has invalid data
		 * returns an array containing validation status & rule data when the rule is valid.
		 *
		 * @param [type] $ruleData
		 * @return void
		 */
		private function validateRuleData( $ruleData) {
			$errors     = array();
			$ruleData   = $this->validateEmptyFields($ruleData);
			$errors     = $this->validateTitle($ruleData['title'], $errors);
			$errors     = $this->validateDiscountType($ruleData['discountType'], $ruleData['discountValue'], $errors);
			$errors     = $this->validateCartValues($ruleData['minCartValue'], $ruleData['maxCartValue'], $errors);
			$errors     = $this->validateOfferDuration($ruleData['startDate'], $ruleData['endDate'], $errors);
			//$errors = $this->validateSelections($ruleData['productsExcluded'], 'products', $errors);
			//$errors = $this->validateSelections($ruleData['categoriesExcluded'], 'categories', $errors);

			if (!empty($errors)) {
				return array('inStatus'=>'-1','errors'=>$errors);
			}
			return array('inStatus'=>1,'ruleData'=>$ruleData);
		}



		/**
		 * Validates if the offer start date is earlier than the offer expiry date
		 *
		 * @param [type] $startDate
		 * @param [type] $endDate
		 * @param [type] $errors
		 * @return array $errors
		 */
		public function validateOfferDuration( $startDate, $endDate, $errors) {
			if (!empty($startDate) && !empty($endDate)) {
				if (strtotime($startDate)>strtotime($endDate)) {
					$errors[]=__('Start date is later than end date', 'customer-specific-pricing-for-woocommerce');
				}
			}
			return $errors;
		}

		/**
		 * Validate cart values provided in rule fields
		 * * min & max cart values should must be greater than 0
		 * * max value is optional but when provided it should be greater than
		 * min value
		 *
		 * @param [type] $min
		 * @param [type] $max
		 * @param [type] $errors
		 * @return void
		 */
		public function validateCartValues( $min, $max, $errors) {
			if (!empty($min) && $min<=0) {
				$errors[]= __('Minimum price must be greater than 0', 'customer-specific-pricing-for-woocommerce');
			}

			if (empty($min)) {
				$errors[]= __('Minimum cart total is required', 'customer-specific-pricing-for-woocommerce');
			}

			if (!empty($max)) {
				if ($min>$max) {
					$errors[]=__('Min cart total is greater than max cart total', 'customer-specific-pricing-for-woocommerce');
				}
			}
			return $errors;
		}

		/**
		 * Validate if correct value for the discount type is selected
		 * * 0 to 100 for % discount
		 * * greater than 0 for flat discount
		 *
		 * @param [type] $type
		 * @param [type] $value
		 * @param [type] $errors
		 * @return void
		 */
		private function validateDiscountType( $type, $value, $errors) {
			if (empty($type) || empty($value)) {
				$errors[]=__('Selecting Discount type & value is required', 'customer-specific-pricing-for-woocommerce');
			} else {
				if ($value<0) {
					$errors[]=__('Discount value must be greater than 0', 'customer-specific-pricing-for-woocommerce');
				}
				if ('percent'==$type && ( $value<0 || $value>100 )) {
					$errors[]=__('Select % discount value between 0 to 100', 'customer-specific-pricing-for-woocommerce');
				}
			}
			return $errors;
		}

		private function validateTitle( $title, $errors) {
			if (empty($title)) {
				$errors[]=__('Rule Title Is Empty', 'customer-specific-pricing-for-woocommerce');
			}
			return $errors;
		}


		/**
		 * Checks all the rule fields, if any of the fields are emty or not
		 * exist inserts empty value to that field this makes the rule data easier to validate.
		 *
		 * @SuppressWarnings(PHPMD.NPathComplexity);
		 * @since 4.3.0
		 * @param array $ruleData
		 * @return array $rule
		 */
		private function validateEmptyFields( $ruleData) {
			$rule = array(
				'id'                => !empty($ruleData['id'])?$ruleData['id']:'',
				'title'             => !empty($ruleData['title'])?$ruleData['title']:'',
				'status'            => !empty($ruleData['status'])?$ruleData['status']:'',
				'userType'          => !empty($ruleData['userType'])?$ruleData['userType']:'',
				'discountType'      => !empty($ruleData['discountType'])?$ruleData['discountType']:'',
				'discountValue'     => !empty($ruleData['discountValue'])?$ruleData['discountValue']:'',
				'minCartValue'      => !empty($ruleData['minCartValue'])?$ruleData['minCartValue']:'',
				'maxCartValue'      => !empty($ruleData['maxCartValue'])?$ruleData['maxCartValue']:'',
				'startDate'         => !empty($ruleData['startDate'])?$ruleData['startDate']:'',
				'endDate'           => !empty($ruleData['endDate'])?$ruleData['endDate']:'',
				'productsIncluded'  => !empty($ruleData['productsIncluded'])?$ruleData['productsIncluded']:array(),
				'productsExcluded'  => !empty($ruleData['productsExcluded'])?$ruleData['productsExcluded']:array(),
				'categoriesIncluded'=> !empty($ruleData['categoriesIncluded'])?$ruleData['categoriesIncluded']:array(),
				'categoriesExcluded'=> !empty($ruleData['categoriesExcluded'])?$ruleData['categoriesExcluded']:array(),
				'offerText'         => !empty($ruleData['offerText'])?$ruleData['offerText']:'',
				);
			return $rule;
		}


		/**
		 * * Verify Nonce
		 * * Check if post variable contains the rule data
		 * * validate the rule date
		 * * save the rule data to the database and return the array
		 * * containing ruleId & Title.
		 *
		 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
		 * extract() is extracting the variables from an array
		 */
		public function saveRuleDataAjax() {
			if (empty($_POST['cd_nonce']) || !wp_verify_nonce(sanitize_text_field($_POST['cd_nonce']), 'wdm-csp-cd')) {
				echo 'Security Check';
				exit();
			}
			$postArray = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
			$ftbData = !empty($postArray['ftbData'])?$postArray['ftbData']:null;
			$exbData = !empty($postArray['exbData'])?$postArray['exbData']:null;
			$guData  = !empty($postArray['guData'])?$postArray['guData']:null;

			$ftbStatus  = $this->saveData($ftbData, 'ftb');
			$exbStatus  = $this->saveData($exbData, 'exb');
			$guStatus   = $this->saveData($guData, 'gu');


			echo \json_encode(array('ftbStatus'=>$ftbStatus,'exbStatus'=>$exbStatus, 'guStatus'=>$guStatus));
			die();
		}


		/**
		 * Thi smethod is used to save the data for all the three user types
		 */
		private function saveData( $data, $userType) {
			$this->clearRuleDataFor($userType);
			if (!empty($data)) {
				$subRules = $this->getArrayIndexData($data, 'subRules');
				$this->saveSubRulesFor($userType, $subRules);

				if (is_numeric($data['maxDiscount'])) {
					update_option('csp_cd_' . $userType . '_max_discount_allowed', $data['maxDiscount']);
				}
				
				$exclusions     = $this->getArrayIndexData($data, 'exclusions');
				$exProducts     = $this->getArrayIndexData($exclusions, 'productIds');
				$exCats         = $this->getArrayIndexData($exclusions, 'categoryIds');
				$exUsers        = $this->getArrayIndexData($exclusions, 'userIds');
				$exUserRoles    = $this->getArrayIndexData($exclusions, 'userRoleSlugs');
				$exUserGroups   = $this->getArrayIndexData($exclusions, 'userGroupIds');
				$buttonText     = isset($data['buttonText'])?$data['buttonText']:'';
				$buttonLink     = isset($data['buttonLink'])?$data['buttonLink']:'';
				$beforeText     = isset($data['beforeOfferText'])?$data['beforeOfferText']:'';
				$afterText      = isset($data['afterOfferText']) ?$data['afterOfferText'] :'';

				update_option('csp_cd_' . $userType . '_shop_button_text', $buttonText);
				update_option('csp_cd_' . $userType . '_shop_button_link', $buttonLink);
				update_option('csp_cd_' . $userType . '_before_offer_text', $beforeText);
				update_option('csp_cd_' . $userType . '_after_offer_text', $afterText);
				update_option('csp_cd_' . $userType . '_excluded_products', $exProducts);
				update_option('csp_cd_' . $userType . '_excluded_product_categories', $exCats);
				update_option('csp_cd_' . $userType . '_excluded_users', $exUsers);
				update_option('csp_cd_' . $userType . '_excluded_user_roles', $exUserRoles);
				update_option('csp_cd_' . $userType . '_excluded_user_groups', $exUserGroups);
			}
			return true;
		}


		/**
		 * Checks if an array index exist & returns the data of the specified index
		 * when data for the array index is present
		 *
		 * @return void
		 */
		private function getArrayIndexData( $array, $index) {
			$data = array();
			if (!empty($array) && isset($array[$index])) {
				$data = $array[$index];
			}
			return $data;
		}

		private function saveSubRulesFor( $userType, $subRules) {
			$optionName = 'csp_cd_' . $userType . '_subrules';
			update_option($optionName, $subRules);
		}


		private function clearRuleDataFor( $userType) {
			update_option('csp_cd_' . $userType . '_subrules', '');
			update_option('csp_cd_' . $userType . '_max_discount_allowed', '');
			update_option('csp_cd_' . $userType . '_offer_text', '');
			update_option('csp_cd_' . $userType . '_excluded_products', '');
			update_option('csp_cd_' . $userType . '_excluded_product_categories', '');
			update_option('csp_cd_' . $userType . '_excluded_users', '');
			update_option('csp_cd_' . $userType . '_excluded_user_roles', '');
			update_option('csp_cd_' . $userType . '_excluded_user_groups', '');
		}
	}
}
