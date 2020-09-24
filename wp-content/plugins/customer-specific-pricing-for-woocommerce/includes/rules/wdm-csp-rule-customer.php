<?php
namespace rules;

require_once 'wdm-csp-rule.php';

/**
 * This class extends the class rule & is used to create
 * the User based CSP rule objcect for the category & products.
 * 
 * @since 4.3.0
 */
class CustomerBasedRule extends ARule {

	/**
	 * Customer/User ID
	 *
	 * @var int
	 */
	private $customer;


	/**
	 * To create User?customer based CSP rule for a category or the product
	 * pass the following parameters while Initialization
	 *
	 * @since 4.3.0
	 * @param int|string $productOrCatIdentifier - product id or the category slug.
	 * @param string $customer -  id of the user.
	 * @param int $discountType - 1 for flat pricing or 2 for % discount
	 * @param int $quantity - minimum purchase quantity for rule eligibility > 0
	 * @param double $value - value of the % discount or flat price
	 * @param string $ruleFor - Optional : "category" or "product", default : "product"
	 */
	public function __construct( $productOrCatIdentifier, $customerId, $discountType, $quantity, $value, $ruleFor = 'product') {
		if ('product'==$ruleFor) {
			$this->setProductId($productOrCatIdentifier);
		} else {
			$this->setCategoryIdentifier($productOrCatIdentifier);
		}
				$this->setCustomer($customerId);
				$this->setDiscountType($discountType);
				$this->setQuantity($quantity);
				$this->setValue($value);
	}

	/**
	 * Returns the customer id for the rule
	 *
	 * @since 4.3.0
	 * @return void
	 */
	public function getCustomer() {
		return $this->customer;
	}

	/**
	 * Sets the customer id for the rule
	 *
	 * @param int $customer - user id
	 * @return void
	 */
	public function setCustomer( $customer) {
		$this->customer=$customer;
	}
}
