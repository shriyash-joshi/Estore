<?php
namespace rules;

/**
 * A rule class represents a single rule interface which contains,
 * the most common elemnets of the csp rules such as,
 * # ElementID as Product or Category Id
 * # Discount Type
 * # Quantity
 * # Value
 * 
 * @since 4.3.0
 * @todo Analysis On : weather all the CSP rule operations(validate/update/delete/add)
 * can be managed through superclass of this class & implement if it increases
 * the overall efficiancy of the plugin.
 *
 */
class ARule {

	private $discountType;
	private $quantity;
	private $value;
	private $productId;
	private $categoryIdentifier;


	/* Getters */
	/**
	 * Returns category id returns null when
	 * the rule is product spesific
	 *
	 * @since 4.3.0
	 * @return string $categoryIdentifier - Product Category Slug
	 */
	public function getCategoryIdentifier() {
		return $this->categoryIdentifier;
	}

	/**
	 * Returns the discount type in string format
	 * 1 - Flat Pricing
	 * 2 - Percent Discount
	 *
	 * @since 4.3.0
	 * @return string $discountType - Discount Type String
	 */
	public function getDiscountType() {
		return 1==$this->discountType?'Flat Pricing':'Percent Discount';
	}

	/**
	 * Returns product id, returns null when
	 * the rule is category spesific
	 *
	 * @since 4.3.0
	 * @return int $productId - Product Id for the rule
	 */
	public function getProductId() {
		return $this->productId;
	}

	/**
	 * Returns the minimum quantity field of the rule
	 *
	 * @since 4.3.0
	 * @return int $quantity - Minimum Quantity
	 */
	public function getQuantity() {
		return $this->quantity;
	}

	/**
	 * Returns the value of the product/category for the rule
	 *
	 * @since 4.3.0
	 * @return double $value - Price Or % Discount Value
	 */
	public function getValue() {
		return $this->value;
	}


	/* Setters */

	/**
	 * Sets the value of price or % discount for the rule
	 *
	 * @since 4.3.0
	 * @param double $value
	 * @return void
	 */
	public function setValue( $value) {
		$this->value= $value;
	}

	/**
	 * Sets the minimum quantity for the rule
	 *
	 * @since 4.3.0
	 * @param int $quantity
	 * @return boolean
	 */
	public function setQuantity( $quantity) {
		$this->quantity=$quantity;
	}

	/**
	 * Sets the productId for the rule
	 *
	 * @since 4.3.0
	 * @param int $productId
	 * @return boolean
	 */
	public function setProductId( $productId) {
		$this->productId=$productId;
	}

	/**
	 * Sets the discount Type for the rule
	 *
	 * @since 4.3.0
	 * @param int $discountType - 1:flat pricing, 2: % discount.
	 * @return boolean
	 */
	public function setDiscountType( $discountType) {
		$this->discountType= $discountType;
	}

	/**
	 * Sets the category slug as an category identifier for the rule
	 *
	 * @since 4.3.0
	 * @param string $categoryIdentifier - category slug
	 * @return void
	 */
	public function setCategoryIdentifier( $categoryIdentifier) {
		$this->categoryIdentifier=$categoryIdentifier;
	}
}
