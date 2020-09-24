<?php
namespace rules;

require_once 'wdm-csp-rule.php';

/**
 * This class extends the class rule & is used to create
 * the Group based CSP rule objcect for the category & products.
 * 
 * @since 4.3.0
 */
class GroupBasedRule extends ARule {

	
	private $group;
	
	/**
	 * To create Group based CSP rule for a category or the product
	 * pass the following parameters while Initialization
	 *
	 * @since 4.3.0
	 * @param int|string $productOrCatIdentifier - product id or the category slug.
	 * @param string $group -  id of the user group.
	 * @param int $discountType - 1 for flat pricing or 2 for % discount
	 * @param int $quantity - minimum purchase quantity for rule eligibility > 0
	 * @param double $value - value of the % discount or flat price
	 * @param string $ruleFor - Optional : "category" or "product", default : "product"
	 */
	public function __construct( $productOrCatIdentifier, $group, $discountType, $quantity, $value, $ruleFor = 'product') {
		if ('product'==$ruleFor) {
			$this->setProductId($productOrCatIdentifier);
		} else {
			$this->setCategoryIdentifier($productOrCatIdentifier);
		}
		$this->setGroup($group);
		$this->setDiscountType($discountType);
		$this->setQuantity($quantity);
		$this->setValue($value);
	}
	
	/**
	 * Returns the group-id defined for the rule
	 *
	 * @since 4.3.0
	 * @return int
	 */
	public function getGroup() {
		return $this->group;
	}

	/**
	 * Sets the groupId for the rule
	 *
	 * @param int $group
	 * @return void
	 */
	private function setGroup( $group) {
		$this->group=$group;
	}
}
