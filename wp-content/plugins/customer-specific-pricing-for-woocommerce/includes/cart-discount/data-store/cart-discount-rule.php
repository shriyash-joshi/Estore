<?php
namespace CSPCartDiscount;

if (!class_exists('cspCdRule')) {
   
	class CspCdRule {
	
		private $dbRule;

		public function __construct( $dbRule) {
			$this->dbRule=$dbRule;
		}

		public static function getItemsIncluded( $products) {
			$products=\unserialize($products);
			return $products['included'];
		}

		public static function getItemsExcluded( $products) {
			$products=\unserialize($products);
			return $products['excluded'];
		}
	

		public static function getRuleInArrayFormat( $dbRule) {
			$rule = array(
					'id'            => $dbRule->rule_id,
					'title'         => $dbRule->rule_title,
					'status'        => $dbRule->status,
					'userType'      => $dbRule->user_type,
					'discountType'  => $dbRule->discount_type,
					'discountValue' => $dbRule->discount_value,
					'minCartValue'  => $dbRule->min_cart_val,
					'maxCartValue'  => $dbRule->max_cart_val,
					'startDate'     => $dbRule->start_date,
					'endDate'       => $dbRule->end_date,
					'productsExcluded'=>self::getItemsExcluded($dbRule->products),
					'categoriesExcluded'=>self::getItemsExcluded($dbRule->categories),
					'offerText'     =>$dbRule->offer_text,
					);
			return $rule;
		}
	}
}
