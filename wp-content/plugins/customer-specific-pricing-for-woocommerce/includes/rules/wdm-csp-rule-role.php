<?php
namespace rules;

require_once 'wdm-csp-rule.php';

/**
 * This class extends the class rule & is used to create
 * the role based CSP rule objcect for the category & products.
 * 
 * @since 4.3.0
 */
class RoleBasedRule extends ARule {

	/**
	 * Role of the user for which this CSP rule is created.
	 *
	 * @var string
	 */
	private $role;


	/**
	 * To create role based CSP rule for a category or the product
	 * pass the following parameters while Initialization
	 *
	 * @since 4.3.0
	 * @param int|string $productOrCatIdentifier - product id or the category slug.
	 * @param string $role - slug of the users role.
	 * @param int $discountType - 1 for flat pricing or 2 for % discount
	 * @param int $quantity - minimum purchase quantity for rule eligibility > 0
	 * @param double $value - value of the % discount or flat price
	 * @param string $ruleFor - Optional : "category" or "product", default : "product"
	 */
	public function __construct( $productOrCatIdentifier, $role, $discountType, $quantity, $value, $ruleFor = 'product') {
		if ('product'==$ruleFor) {
			$this->setProductId($productOrCatIdentifier);
		} else {
			$this->setCategoryIdentifier($productOrCatIdentifier);
		}
		$this->setRole($role);
		$this->setDiscountType($discountType);
		$this->setQuantity($quantity);
		$this->setValue($value);
	}


	/**
	 * Returns the role-slug defined for the rule
	 *
	 * @since 4.3.0
	 * @return string
	 */
	public function getRole() {
		return $this->role;
	}

	/**
	 * Sets the role-slug for the rule
	 *
	 * @param string $role
	 * @return void
	 */
	private function setRole( $role) {
		$this->role=$role;
	}
}
