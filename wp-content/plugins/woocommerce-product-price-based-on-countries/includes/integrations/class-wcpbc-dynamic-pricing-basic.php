<?php
/**
 * Integration with WooCommerce Dynamic Pricing.
 *
 * @package WCPBC
 * @version 2.0.8
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WCPBC_Dynamic_Pricing_Basic' ) ) {
	exit;
}

/**
 * WCPBC_Dynamic_Pricing_Basic Class
 */
class WCPBC_Dynamic_Pricing_Basic {

	/**
	 * Apply exchange rate to rules and return.
	 *
	 * @param array  $rules Array of rules.
	 * @param string $rule_set Rule set key.
	 * @param string $mode Rule mode (rules|blockrules).
	 * @param array  $zone_rules Pricing zone rules.
	 * @return array
	 */
	private static function rules_zone_amount( $rules, $rule_set = '', $mode = '', $zone_rules = array() ) {
		if ( is_array( $rules ) ) {
			foreach ( $rules as $i => $rule ) {

				if ( in_array( $rule['type'], array( 'percentage_discount', 'percent_adjustment', 'percent_product' ), true ) ) {
					continue;
				}

				$amount       = isset( $zone_rules[ $rule_set ][ $mode ][ $i ]['amount'] ) ? $zone_rules[ $rule_set ][ $mode ][ $i ]['amount'] : false;
				$price_method = false !== $amount && isset( $zone_rules[ $rule_set ][ $mode ][ $i ]['price_method'] ) ? $zone_rules[ $rule_set ][ $mode ][ $i ]['price_method'] : false;

				if ( wcpbc_is_exchange_rate( $price_method ) ) {
					$amount = wc_format_decimal( $rule['amount'] );
					if ( $amount ) {
						$rules[ $i ]['amount'] = wc_format_localized_price( wcpbc_the_zone()->get_exchange_rate_price( $amount ) );
					}
				} else {

					$rules[ $i ]['amount'] = $amount;
				}
			}
		}
		return $rules;
	}

	/**
	 * Hook actions and filters.
	 */
	public static function init() {
		add_filter( 'woocommerce_product_get__pricing_rules', array( __CLASS__, 'get_product_pricing_rules' ), 10, 2 );

		// General rulesets.
		self::simple_membership();
		self::simple_category();
		self::advanced_category();
	}

	/**
	 * Return pricing_rules after apply the exchange rate.
	 *
	 * @param mixed      $pricing_rules Pricing rules.
	 * @param WC_Product $product Product Instance.
	 * @return array
	 */
	public static function get_product_pricing_rules( $pricing_rules, $product ) {
		if ( ! is_callable( array( $product, 'get_id' ) ) ) {
			return $pricing_rules;
		}

		$post_id = $product->get_id();

		if ( ! empty( $pricing_rules ) ) {

			$zone_rules = apply_filters( 'wc_price_based_country_dynamic_pricing_product_rulesets', array(), $post_id );

			foreach ( $pricing_rules as $rule_set => $rules ) {

				foreach ( array( 'rules', 'blockrules' ) as $mode ) {
					if ( ! isset( $rules[ $mode ] ) ) {
						continue;
					}
					$pricing_rules[ $rule_set ][ $mode ] = self::rules_zone_amount(
						$rules[ $mode ],
						$rule_set,
						$mode,
						$zone_rules
					);
				}
			}
		}

		return $pricing_rules;
	}

	/**
	 * Apply exchange rate to Pricing Simple Membership Rules.
	 */
	public static function simple_membership() {
		if ( is_callable( 'WC_Dynamic_Pricing_Simple_Membership', 'instance' ) && isset( WC_Dynamic_Pricing_Simple_Membership::instance()->available_rulesets ) ) {

			$zone_rules = apply_filters( 'wc_price_based_country_dynamic_pricing_membership_simple_rulesets', array() );

			WC_Dynamic_Pricing_Simple_Membership::instance()->available_rulesets = self::rules_zone_amount(
				WC_Dynamic_Pricing_Simple_Membership::instance()->available_rulesets,
				'membership',
				'rules',
				$zone_rules
			);
		}
	}

	/**
	 * Apply exchange rate to Pricing Simple Category Rules.
	 */
	public static function simple_category() {
		if ( ! is_callable( 'WC_Dynamic_Pricing_Simple_Category', 'instance' ) ) {
			return;
		}

		foreach ( array( 'available_rulesets', 'available_advanced_rulesets' ) as $ruleset_prop ) {
			if ( ! ( isset( WC_Dynamic_Pricing_Simple_Category::instance()->{$ruleset_prop} ) && is_array( WC_Dynamic_Pricing_Simple_Category::instance()->{$ruleset_prop} ) ) ) {
				continue;
			}

			$zone_rules = apply_filters( 'wc_price_based_country_dynamic_pricing_category_' . str_replace( 'available_', '', $ruleset_prop ), array() );

			foreach ( WC_Dynamic_Pricing_Simple_Category::instance()->{$ruleset_prop} as $rule_set => $rules ) {

				foreach ( array( 'rules', 'blockrules' ) as $mode ) {
					if ( ! isset( $rules[ $mode ] ) ) {
						continue;
					}

					WC_Dynamic_Pricing_Simple_Category::instance()->{$ruleset_prop}[ $rule_set ][ $mode ] = self::rules_zone_amount(
						$rules[ $mode ],
						$rule_set,
						$mode,
						$zone_rules
					);

				}
			}
		}
	}

	/**
	 * Apply exchange rate to Pricing Advanced Category Rules.
	 */
	public static function advanced_category() {
		if ( ! ( is_callable( 'WC_Dynamic_Pricing_Advanced_Category', 'instance' ) && isset( WC_Dynamic_Pricing_Advanced_Category::instance()->adjustment_sets ) && is_array( WC_Dynamic_Pricing_Advanced_Category::instance()->adjustment_sets ) ) ) {
			return;
		}

		$zone_rules = apply_filters( 'wc_price_based_country_dynamic_pricing_category_advanced_rulesets', array() );

		foreach ( WC_Dynamic_Pricing_Advanced_Category::instance()->adjustment_sets as $rule_set => $adjustment_set ) {

			$mode = 'bulk' === $adjustment_set->mode ? 'rules' : 'blockrules';

			WC_Dynamic_Pricing_Advanced_Category::instance()->adjustment_sets[ $rule_set ]->pricing_rules = self::rules_zone_amount(
				WC_Dynamic_Pricing_Advanced_Category::instance()->adjustment_sets[ $rule_set ]->pricing_rules,
				$rule_set,
				$mode,
				$zone_rules
			);

		}
	}
}

add_action( 'wc_price_based_country_frontend_princing_init', array( 'WCPBC_Dynamic_Pricing_Basic', 'init' ) );
