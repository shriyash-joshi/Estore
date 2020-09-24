<?php

namespace cspSingleView;

if (! class_exists('WdmRuleManagement')) {


	/**
	* Class that includes functions for rule management.
	*/
	class WdmRuleManagement {
	

		/**
		 * The reference to *Singleton* instance of this class
		 * 
		 * @var Singleton The reference to *Singleton* instance of this class
		 */
		private static $instance;
		public $errors;
		public $ruleTable;

		/**
		 * Returns the *Singleton* instance of this class.
		 *
		 * @return Singleton The *Singleton* instance.
		 */
		public static function getInstance() {
			if (null === static::$instance) {
				static::$instance = new static();
			}

			return static::$instance;
		}

		/**
		 * Protected constructor to prevent creating a new instance of the
		 * *Singleton* via the `new` operator from outside of this class.
		 * Gives the table for the User specific rules, which saves the
		 * Rules.
		 * Action for settings the unused rules as inactive.
		 */
		protected function __construct() {
			global $wpdb;
			$this->ruleTable = $wpdb->prefix . 'wusp_rules';
			add_action('woocommerce_process_product_meta_simple', array( $this, 'setUnusedRulesAsInactive' ), 9999);
			add_action('woocommerce_ajax_save_product_variations', array( $this, 'setUnusedRulesAsInactive' ), 9999);
			//$this->getCompleteRuleData( 25 );
		}

		/**
		* Add the error message.
		 *
		* @param string $message error message.
		*/
		private function addError( $message) {
			$this->errors .= $message;
		}

		/**
		* Add the rule in the DB.
		 *
		* @param string $ruleTitle Rule title.
		* @param string $ruleType Rule-type.
		* @return mixed insert_id if adding into DB is succesful otherwise
		* false.
		*/
		public function addRule( $ruleTitle, $ruleType) {
			global $wpdb;
			$ruleTitle       = stripcslashes($ruleTitle);
			$creationTime = gmdate('Y-m-d h:i:s', current_time('timestamp'));
			$insertStatus = $wpdb->insert(
				$this->ruleTable,
				array(
				'rule_title'             => $ruleTitle,
				'rule_type'              => ucfirst(strtolower($ruleType)),
				'rule_creation_time'     => $creationTime,
				'rule_modification_time' => $creationTime,
				'active'                 => 1,
				),
				array(
				'%s',
				'%s',
				'%s',
				'%s',
				'%d'
				)
			);
			if (false!==$insertStatus) {
				return $wpdb->insert_id;
			}
			$this->addError(__('Could not insert rule in the database', 'customer-specific-pricing-for-woocommerce'));
			return false;
		}

		/**
		* Updates the rule with the rule details and status as active.
		 *
		* @param int $ruleId Rule id.
		* @param array $dataTobeUpdated rule-type,rule-title
		* @return bool true if updated else false.
		*/
		public function updateRule( $ruleId, $dataTobeUpdated) {
			global $wpdb;
			$noOfRowsUpdated = 0;

			if (! isset($dataTobeUpdated[ 'active' ])) {
				$dataTobeUpdated[ 'active' ] = 1;
			}
			$dataTobeUpdated[ 'rule_modification_time' ] = gmdate('Y-m-d h:i:s', current_time('timestamp'));
			$sizeOfData                                  = count($dataTobeUpdated);
			$queryPlaceholders                           = array_fill(0, $sizeOfData, '%s');
			$columnsTobeUpdated                          = array_keys($dataTobeUpdated);

			$positionOfActiveFlag = array_search('active', $columnsTobeUpdated);
			if (false!==$positionOfActiveFlag) {
				$queryPlaceholders[ $positionOfActiveFlag ] = '%d';
			}

			$checkRuleTypeInData = array_search('rule_type', $columnsTobeUpdated);
			if (false!==$checkRuleTypeInData) {
				$dataTobeUpdated[ 'rule_type' ] = ucfirst(strtolower($dataTobeUpdated[ 'rule_type' ]));
			}

			$noOfRowsUpdated = $wpdb->update($this->ruleTable, $dataTobeUpdated, array(
				'rule_id' => $ruleId,
			), $queryPlaceholders, array(
				'%d'
			));
			if ( $noOfRowsUpdated || 0==$noOfRowsUpdated) {
				return true;
			}
			$this->addError(__('Could not update rule in the database. Please check if correct data is added in the form.', 'customer-specific-pricing-for-woocommerce'));
			return false;
		}
		/**
		* Deletes the Rule.
		* Gets the Rule type for the rule.
		* Deletes the subrules of the specific rule Id.
		*/
		public function deleteRule( $ruleId) {
			global $wpdb, $subruleManager;
			$ruleType = $this->getRuleType($ruleId);
			if (null!== $ruleType) {
				$subruleManager->deleteSubrulesOfRule($ruleId, $ruleType);
			}
			$wpdb->delete($this->ruleTable, array(
				'rule_id' => $ruleId,
			), array(
				'%d'
			));
		}

		/**
		* Deletes the rule whose total_subrules are 0
		*/
		public function deleteRuleWithZeroNumberOfSubrules() {
			global $wpdb;
			$wpdb->delete($this->ruleTable, array('total_subrules' => 0));
		}
		/**
		* Sets the Unused rules status as inactive.
		* Finds the active rules from the USP rules table
		* Gets the USP subrules for the USP rules which are active from the database usp_rules table
		* Gets the array of USP rule_ids with the no.of inactive subrules
		* Find out all those rule ids which are active but whose all subrules are inactive or 0
		* Take the rule_ids of such rules and update their status of the rule to inactive.
		 *
		* @global $wpdb global WordPress database variable
		*/
		public function setUnusedRulesAsInactive() {
			global $wpdb, $subruleManager;
			$findActiveRules    = $wpdb->get_results($wpdb->prepare('SELECT rule_id, total_subrules FROM ' . $wpdb->prefix . 'wusp_rules WHERE active = %d', 1), ARRAY_A);
			$rulesNumOfSubrules = array();
			if ($findActiveRules) {
				foreach ($findActiveRules as $singleActiveRule) {
					$rulesNumOfSubrules[ $singleActiveRule[ 'rule_id' ] ] = $singleActiveRule[ 'total_subrules' ];
				}

				//find keys of $rulesNumOfSubrules and search for number of inactive rules for those rule ids
				$rulesCountOfInactive = $subruleManager->getCountOfInactiveSubrulesForRules(array_keys($rulesNumOfSubrules));
				if ( false===$rulesCountOfInactive) {
					return;
				}
				//Find out all those rule ids which are active but whose all subrules are inactive or 0
				$rulesNumOfSubrules = array_intersect_assoc($rulesCountOfInactive, $rulesNumOfSubrules);

				$ruleIds = array_keys($rulesNumOfSubrules);
				if ($ruleIds) {
					$wpdb->query($wpdb->prepare('UPDATE ' . $wpdb->prefix . 'wusp_rules SET active= 0 WHERE rule_id IN (' . implode(', ', array_fill(0, count($ruleIds), '%s')) . ' )', $ruleIds));
				}
			}
		}
		/**
		* Updates the total no. of subrules for the particular rule_id.
		 *
		* @param int $ruleId rule id.
		* @param bool $deleteRuleWithZeroSubrules True if rule with zero
		*                   subrules should be deleted, or false otherwise.
		*/
		public function updateTotalNumberOfSubrules( $ruleId, $deleteRuleWithZeroSubrules = false) {
			global $wpdb, $subruleManager;
			$subrulesTotal = $subruleManager->countSubrules($ruleId);

			if (0 == $subrulesTotal && $deleteRuleWithZeroSubrules) {
				$this->deleteRule($ruleId);
			} else {
				$wpdb->update($this->ruleTable, array(
					'total_subrules' => $subrulesTotal,
				), array(
					'rule_id' => $ruleId,
				), array(
					'%d',
				), array(
					'%d',
				));
			}
		}

		public function getCompleteRuleData( $ruleId) {
			global $wpdb, $subruleManager;
			$completeRuleInfo = array();
			$mainRuleInfo     = $wpdb->get_results($wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . 'wusp_rules WHERE rule_id=%d LIMIT 1', $ruleId), ARRAY_A);
			if (null== $mainRuleInfo) {
				return false;
			}
			$completeRuleInfo               = $mainRuleInfo[ 0 ];
			$completeRuleInfo[ 'subrules' ] = array();
			$subrulesInfo                   = $subruleManager->getAllSubrulesInfoForRule($ruleId);
			if ( null!=$subrulesInfo) {
				$completeRuleInfo[ 'subrules' ] = $subrulesInfo;
			}
			return $completeRuleInfo;
		}

		/**
		* Returns the rule_title for the rule id.
		 *
		* @param int $ruleId Rule Id.
		* @return array $ruleTitle rule title.
		*/
		public function getRuleTitle( $ruleId) {
			global $wpdb;
			$ruleTitle = $wpdb->get_results($wpdb->prepare('SELECT rule_title FROM ' . $wpdb->prefix . 'wusp_rules WHERE rule_id=%d LIMIT 1', $ruleId), ARRAY_A);
			return $ruleTitle[ 0 ];
		}
		/**
		* Gets the rule type for particular rule id.
		 *
		* @param int $ruleId Rule Id.
		* @return string $ruleType rule
		*/
		public function getRuleType( $ruleId) {
			global $wpdb;
			$ruleType = $wpdb->get_var($wpdb->prepare('SELECT rule_type FROM ' . $wpdb->prefix . 'wusp_rules WHERE rule_id = %d', $ruleId));
			return $ruleType;
		}
	}

}
$GLOBALS['ruleManager'] = WdmRuleManagement::getInstance();
