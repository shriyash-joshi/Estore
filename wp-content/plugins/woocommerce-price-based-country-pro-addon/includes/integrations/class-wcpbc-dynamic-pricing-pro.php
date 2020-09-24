<?php
/**
 * Handle integration with WooCommerce Dynamic Pricing by Developed by Lucas Stark .
 *
 * @see https://woocommerce.com/products/dynamic-pricing/
 * @since 2.7.2
 * @package WCPBC
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WCPBC_Dynamic_Pricing_Pro' ) ) :

	/**
	 * WCPBC_Dynamic_Pricing_Pro Class
	 */
	class WCPBC_Dynamic_Pricing_Pro {

		/**
		 * Hook actions and filters
		 */
		public static function init() {
			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_scripts' ) );
			add_action( 'admin_init', array( __CLASS__, 'save_advanced_category_options' ) );
			add_action( 'woocommerce_process_product_meta', array( __CLASS__, 'save_meta_data' ), 10 );
			add_filter( 'wc_price_based_country_dynamic_pricing_category_advanced_rulesets', array( __CLASS__, 'get_advanced_category_pricing_rules' ) );
			add_filter( 'wc_price_based_country_dynamic_pricing_product_rulesets', array( __CLASS__, 'get_product_pricing_rules' ), 10, 2 );
		}

		/**
		 * Return the ruleset type.
		 */
		private static function get_ruleset_type() {
			$rule_set_type = false;
			$screen        = get_current_screen();
			$screen_id     = $screen ? $screen->id : '';

			if ( 'product' === $screen_id ) {
				$rule_set_type = 'product';

			} elseif ( 'woocommerce_page_wc_dynamic_pricing' === $screen_id ) {
				// Check the Dynamic Pricing Settings tab.
				$tab  = isset( $_GET['tab'] ) ? wc_clean( $_GET['tab'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
				$view = isset( $_GET['view'] ) ? wc_clean( $_GET['view'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

				if ( 'category' === $tab && '1' === $view ) {
					$rule_set_type = 'advanced_category';
				}
			}
			return $rule_set_type;

		}

		/**
		 * Return the pricing rules for a zone.
		 *
		 * @param string          $type Type of rule set.
		 * @param WC_Pricing_Zone $zone Pricing zone instance.
		 */
		private static function get_admin_pricing_rules( $type, $zone ) {
			global $thepostid, $post;
			$rules = array();

			if ( 'product' === $type ) {
				$thepostid = empty( $thepostid ) ? $post->ID : $thepostid; // phpcs:ignore WordPress.NamingConventions
				$_rules    = self::get_product_pricing_rules( array(), $thepostid, $zone );

			} elseif ( 'advanced_category' === $type ) {
				$_rules = self::get_advanced_category_pricing_rules( $rules, $zone->get_zone_id() );
			}

			// Re-order the index to match with the index of Dynamic Pricing table.
			foreach ( $_rules as $rule_set_key => $rule_sets ) {
				$rules[ $rule_set_key ] = array();
				foreach ( $rule_sets as $mode => $rows ) {
					$rules[ $rule_set_key ][ $mode ] = array();
					$index                           = 0;
					foreach ( $rows as $row ) {
						$rules[ $rule_set_key ][ $mode ][ ++$index ] = $row;
					}
				}
			}

			return $rules;
		}

		/**
		 * Admin scripts
		 */
		public static function admin_scripts() {
			$rule_set_type = self::get_ruleset_type();

			if ( ! in_array( $rule_set_type, array( 'product', 'advanced_category' ), true ) ) {
				return;
			}

			// Build the script data.
			$data = array(
				'zones'    => array(),
				'security' => wp_create_nonce( 'wcpbc-save-' . $rule_set_type ),
			);

			foreach ( WCPBC_Pricing_Zones::get_zones() as $zone ) {

				$data['zones'][] = array(
					'zone_id'       => $zone->get_zone_id(),
					'label'         => wcpbc_price_method_label( __( 'Amount for', 'wc-price-based-country-pro' ), $zone ),
					'pricing_rules' => self::get_admin_pricing_rules( $rule_set_type, $zone ),
				);
			}

			// Enqueque the script.
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			wp_register_script( 'wc_price_based_country_pro_dynamic_pricing', WC_Product_Price_Based_Country_Pro::plugin_url() . 'assets/js/admin-dynamic-pricing' . $suffix . '.js', array( 'wp-util', 'wc_price_based_country_admin' ), WC_Product_Price_Based_Country_Pro::$version, true );
			wp_localize_script( 'wc_price_based_country_pro_dynamic_pricing', 'wc_price_based_country_pro_dynamic_pricing_params', $data );
			wp_enqueue_script( 'wc_price_based_country_pro_dynamic_pricing' );

			// Print the row template.
			add_action( 'admin_footer', array( __CLASS__, 'pricing_rule_row_template' ) );
		}

		/**
		 * Add the pricing row script template
		 */
		public static function pricing_rule_row_template() {
			?>
			<script type="text/html" id="tmpl-wcpbc-pricing-rule-row">
			<tr id="{{data.zone_id}}_pricing_{{data.rule_mode}}_row_{{data.rule_id}}_{{data.index}}" class="wcpbc_pricing_{{data.rule_mode}}_row_{{data.rule_id}}_{{data.index}}">
				<td colspan="2" class="wcpbc_pricing_rule_row_label">
					{{{data.label}}}
				</td>
				<td>
					<select id="wcpbc_pricing_rules_{{data.zone_id}}_{{data.rule_id}}_{{data.rule_mode}}s_{{data.index}}_price_method" name="wcpbc_pricing_rules[{{data.zone_id}}][{{data.rule_id}}][{{data.rule_mode}}s][{{data.index}}][price_method]" class="wcpbc_pricing_rules_price_method">
					<?php foreach ( wcpbc_price_method_options() as $value => $label ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
					</select>
				</td>
				<td>
					<input type="text" id="wcpbc_pricing_rules_{{data.zone_id}}_{{data.rule_id}}_{{data.rule_mode}}s_{{data.index}}_amount" name="wcpbc_pricing_rules[{{data.zone_id}}][{{data.rule_id}}][{{data.rule_mode}}s][{{data.index}}][amount]" class="wcpbc_pricing_rules_price_amount wc_input_price" />
				</td>
				<td></td>
			</tr>
			</script>
			<?php
		}

		/**
		 * Save advanced category pricing rule option
		 */
		public static function save_advanced_category_options() {
			if ( ! ( isset( $_POST['wcpbc-advanced-category-nonce'] ) && wp_verify_nonce( $_POST['wcpbc-advanced-category-nonce'], 'wcpbc-save-advanced_category' ) && isset( $_POST['option_page'] ) && '_a_category_pricing_rules' === $_POST['option_page'] ) ) {
				return;
			}
			$postdata = isset( $_POST['wcpbc_pricing_rules'] ) ? wc_clean( $_POST['wcpbc_pricing_rules'] ) : false; // phpcs:ignore WordPress.Security.NonceVerification
			update_option( 'wcpbc_a_category_pricing_rules', $postdata );
		}

		/**
		 * Save the Dynamic Pricing meta data.
		 *
		 * @param int $product_id Post ID.
		 */
		public static function save_meta_data( $product_id ) {
			$postdata = isset( $_POST['wcpbc_pricing_rules'] ) ? wc_clean( $_POST['wcpbc_pricing_rules'] ) : array(); // phpcs:ignore WordPress.Security.NonceVerification

			foreach ( WCPBC_Pricing_Zones::get_zones() as $zone ) {
				if ( isset( $postdata[ $zone->get_zone_id() ] ) ) {
					$data = $postdata[ $zone->get_zone_id() ];
					$zone->set_postmeta( $product_id, '_pricing_rules', $data );
				} else {
					$zone->delete_postmeta( $product_id, '_pricing_rules' );
				}
			}
		}

		/**
		 * Return pricing_rules set manual price. Basic version set the exchange rate prices.
		 *
		 * @param mixed              $pricing_rules Pricing rules.
		 * @param int                $post_id Post ID.
		 * @param WCPBC_Pricing_Zone $zone Pricing zone object.
		 * @return array
		 */
		public static function get_product_pricing_rules( $pricing_rules, $post_id, $zone = false ) {
			$zone               = ! $zone && wcpbc_the_zone() ? wcpbc_the_zone() : $zone;
			$zone_pricing_rules = $zone->get_postmeta( $post_id, '_pricing_rules' );
			if ( is_array( $zone_pricing_rules ) ) {
				$pricing_rules = $zone_pricing_rules;
			}
			return $pricing_rules;
		}

		/**
		 * Return the advanced category pricing rules.
		 *
		 * @param string $pricing_rules Rules to retrun.
		 * @param string $zone_id Pricing zone ID.
		 * @return array
		 */
		public static function get_advanced_category_pricing_rules( $pricing_rules = array(), $zone_id = false ) {
			$option_value = get_option( 'wcpbc_a_category_pricing_rules', array() );
			$zone_id      = empty( $zone_id ) && wcpbc_the_zone() ? wcpbc_the_zone()->get_zone_id() : $zone_id;

			if ( is_array( $option_value ) && isset( $option_value[ $zone_id ] ) ) {
				$pricing_rules = $option_value[ $zone_id ];
			}
			return $pricing_rules;
		}
	}

	WCPBC_Dynamic_Pricing_Pro::init();

endif;
