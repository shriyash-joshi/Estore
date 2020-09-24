<?php
/**
 * Handle Price Based on Country Pro admin .
 *
 * @version 2.5.3
 * @package WCPBC
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WCPBC_Admin_Pro' ) ) :

	/**
	 * WCPBC_Admin_Pro Class
	 */
	class WCPBC_Admin_Pro {

		/**
		 * Hook actions and filters
		 *
		 * @since 1.0
		 */
		public static function init() {
			add_action( 'woocommerce_settings_page_init', array( __CLASS__, 'settings_page_init' ) );
			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'load_admin_script' ) );
			add_filter( 'woocommerce_general_settings', array( __CLASS__, 'currency_format_settings' ) );
			add_filter( 'wc_price_based_country_settings_general', array( __CLASS__, 'settings_general' ) );
			add_action( 'woocommerce_settings_save_price-based-country', array( __CLASS__, 'before_save_general' ), 5 );
			add_action( 'wc_price_based_country_settings_page_pricing_zone', array( __CLASS__, 'settings_page_pricing_zone' ) );
			add_action( 'wc_price_based_country_settings_before_save_zone', array( __CLASS__, 'settings_before_save_zone' ) );
			add_filter( 'wc_price_based_country_settings_zone_after_column_currency', array( __CLASS__, 'settings_zone_after_column_currency' ), 10, 2 );
			add_action( 'wc_price_based_country_settings_sections', array( __CLASS__, 'settings_sections' ) );
			add_action( 'woocommerce_variable_product_bulk_edit_actions', array( __CLASS__, 'variable_product_bulk_edit_actions' ) );
			add_action( 'woocommerce_bulk_edit_variations_default', array( __CLASS__, 'bulk_edit_variations_default' ), 10, 4 );
			remove_action( 'woocommerce_coupon_options', array( 'WCPBC_Admin_Meta_Boxes', 'coupon_options' ) );
			add_action( 'woocommerce_coupon_options', array( __CLASS__, 'coupon_options' ), 10, 1 );
			add_action( 'woocommerce_coupon_options_save', array( __CLASS__, 'coupon_options_save' ) );
			add_action( 'wp_ajax_wc_price_based_country_load_country_pricing', array( __CLASS__, 'load_country_pricing' ) );
			add_action( 'woocommerce_order_item_add_action_buttons', array( __CLASS__, 'order_item_add_action_buttons' ) );
			add_action( 'admin_init', array( __CLASS__, 'hide_renewal_license_notice' ) );
			add_filter( 'admin_notices', array( __CLASS__, 'renewal_license_notice' ) );
		}

		/**
		 * Remove the currency symbol filter in the settings pages.
		 */
		public static function settings_page_init() {
			remove_filter( 'woocommerce_currency_symbol', array( 'WCPBC_Frontend_Currency', 'alt_currency_symbol' ), 100, 2 );
		}
		/**
		 * Enqueue admin scripts
		 *
		 * @return void
		 */
		public static function load_admin_script() {

			$screen    = get_current_screen();
			$screen_id = $screen ? $screen->id : '';

			if ( in_array( $screen_id, wc_get_screen_ids(), true ) ) {
				// Scripts.
				$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

				wp_register_script( 'wc_price_based_country_pro_admin', WC_Product_Price_Based_Country_Pro::plugin_url() . 'assets/js/admin' . $suffix . '.js', array( 'wc_price_based_country_admin' ), WC_Product_Price_Based_Country_Pro::$version, true );
				wp_enqueue_script( 'wc_price_based_country_pro_admin' );
				wp_localize_script(
					'wc_price_based_country_pro_admin',
					'wc_price_based_country_pro_admin_param',
					array(
						'i18n_enter_a_value'         => esc_js( __( 'Enter a value', 'wc-price-based-country-pro' ) ),
						'i18n_enter_a_value_fixed_or_percent' => esc_js( __( 'Enter a value (fixed or %)', 'wc-price-based-country-pro' ) ),
						'i18n_load_country_pricing_confirm' => esc_js( __( 'Load country pricing? This action will change the items price, the order currency, calculate taxes and update totals based on customer country.', 'wc-price-based-country-pro' ) ),
						'alt_currency_symbols'       => WCPBC_Frontend_Currency::get_alt_currency_symbols(),
						'load_country_pricing_nonce' => wp_create_nonce( 'load-country-pricing' ),
						'subscription_support'       => class_exists( 'WC_Subscriptions' ),
						'name_your_price_support'    => class_exists( 'WC_Name_Your_Price' ),
						'ajax_url'                   => admin_url( 'admin-ajax.php' ),
					)
				);

				// Styles.
				wp_enqueue_style( 'wc-price-based-country-pro-admin', WC_Product_Price_Based_Country_Pro::plugin_url() . 'assets/css/admin.css', array(), WC_Product_Price_Based_Country_Pro::$version );
			}
		}

		/**
		 * Add currency format setting.
		 *
		 * @param array $settings Array of settings.
		 * @return array
		 */
		public static function currency_format_settings( $settings ) {

			$general_settings = array();

			foreach ( $settings as $setting ) {
				if ( 'woocommerce_currency_pos' === $setting['id'] ) {

					$general_settings[] = array(
						'title'    => __( 'Currency Format', 'wc-price-based-country-pro' ),
						'id'       => 'wc_price_based_currency_format',
						'desc'     => __( 'Preview:', 'wc-price-based-country-pro' ) . ' <code id="wc_price_based_currency_format_preview"></code>',
						'desc_tip' => __( 'Enter the currency format. Supports the following placeholders: [code] = currency code, [symbol] = currency symbol, [symbol-alt] = alternative currency symbol (US$, CA$, ...), [price] = product price.', 'wc-price-based-country-pro' ),
						'css'      => 'min-width:350px;',
						'default'  => '[symbol][price]',
						'type'     => 'text',
					);
				}

				$general_settings[] = $setting;
			}

			return $general_settings;
		}

		/**
		 * Add the Pro options to general settings.
		 *
		 * @param array $settings Array with the plugin general settings fields.
		 * @return array
		 */
		public static function settings_general( $settings ) {

			$options        = array();
			$exchange_rates = WCPBC_Update_Exchange_Rates::get_exchange_rates_providers();
			foreach ( $exchange_rates as $id => $provider ) {
				$options[ $id ] = $provider->get_name();
			}

			$pbc_settings = array();
			foreach ( $settings as $setting ) {

				if ( 'sectionend' === $setting['type'] && 'general_options' === $setting['id'] ) {
					$pbc_settings[] = array(
						'title'    => __( 'Exchange rate API', 'wc-price-based-country-pro' ),
						'desc'     => __( 'This controls which API provider will be used to exchange rates auto-updates.', 'wc-price-based-country-pro' ),
						'id'       => 'wc_price_based_country_exchange_rate_api',
						'default'  => current( array_keys( $options ) ),
						'type'     => 'select',
						'class'    => 'wc-enhanced-select',
						'desc_tip' => true,
						'options'  => $options,
					);

					// Add exchange rate extra options.
					foreach ( $exchange_rates as $id => $provider ) {
						$fields = $provider->get_options_fields();
						if ( $fields ) {
							foreach ( $fields as $field ) {
								$field['class']  = empty( $field['class'] ) ? '' : $field['class'];
								$field['class'] .= 'wcpbc_exchange_rate_option show_if_exchange_rate_' . $id;

								$pbc_settings[] = $field;
							}
						}
					}
				}

				$pbc_settings[] = $setting;
			}

			return $pbc_settings;
		}

		/**
		 * Actions before save general settings.
		 */
		public static function before_save_general() {
			if ( empty( $_POST['wc_price_based_country_exchange_rate_api'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				return;
			}

			// Validate the exchange rate options fields.
			$exchange_rate_api = wc_clean( $_POST['wc_price_based_country_exchange_rate_api'] ); // phpcs:ignore WordPress.Security.NonceVerification
			$api_providers     = WCPBC_Update_Exchange_Rates::get_exchange_rates_providers();
			if ( isset( $api_providers[ $exchange_rate_api ] ) ) {
				$api_provider = $api_providers[ $exchange_rate_api ];
				$api_provider->validate_options_fields();
			}
		}

		/**
		 * Add the Pro options to pricing zone settings.
		 *
		 * @param WCPBC_Pricing_Zone $zone Pricing zone instance.
		 */
		public static function settings_page_pricing_zone( $zone ) {
			include dirname( __FILE__ ) . '/views/html-admin-page-pricing-zone-pro.php';
		}

		/**
		 * Update $_POST variable with the exchange rate getting from API.
		 */
		public static function settings_before_save_zone() {
			$auto_exchange_rate = isset( $_POST['auto_exchange_rate'] ) && 'yes' === $_POST['auto_exchange_rate']; // phpcs:ignore WordPress.Security.NonceVerification
			$currency           = isset( $_POST['currency'] ) ? wc_clean( wp_unslash( $_POST['currency'] ) ) : false; // phpcs:ignore WordPress.Security.NonceVerification

			if ( $currency && $auto_exchange_rate ) {
				$rate  = WCPBC_Update_Exchange_Rates::get_exchange_rate_from_api( $currency );
				$error = false;

				if ( ! empty( $rate ) ) {
					$base_currency = get_option( 'woocommerce_currency' );
					$fee           = empty( $_POST['exchange_rate_fee'] ) ? 0 : floatval( $_POST['exchange_rate_fee'] ); // phpcs:ignore WordPress.Security.NonceVerification

					if ( $base_currency !== $currency && 1 != $rate ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
						$_POST['exchange_rate'] = $rate * ( 1 + ( $fee / 100 ) );
					} elseif ( $base_currency === $currency ) {
						$_POST['exchange_rate'] = 1 * ( 1 + ( $fee / 100 ) );
					} else {
						$error = __( 'Error updating the exchange rate. The API returned 1 as the exchange rate.', 'wc-price-based-country-pro' );
					}
				} else {
					$error = __( 'Error updating the exchange rate from API.', 'wc-price-based-country-pro' );
				}

				if ( $error ) {
					WC_Admin_Settings::add_error( $error );
					add_filter( 'wc_price_based_country_settings_zone_validation', '__return_false' );
				}
			} else {
				// Set the exchange rate fee to 0.
				$_POST['exchange_rate_fee'] = '0';
			}
		}

		/**
		 * Description for the currency column of pricing zone table.
		 *
		 * @param string             $output Column output.
		 * @param WCPBC_Pricing_Zone $zone Zone instance.
		 * @return string
		 */
		public static function settings_zone_after_column_currency( $output, $zone ) {
			$description = '';

			if ( $zone->get_auto_exchange_rate() ) {
				$description = ' (auto';
				if ( $zone->get_exchange_rate_fee() ) {
					$description .= ' + ' . $zone->get_exchange_rate_fee() . '%';
				}
				$description .= ')';
			} else {
				$description = ' (manual)';
			}

			return substr( $output, 0, strlen( $output ) - 7 ) . $description . '</span>';

		}

		/**
		 * Add the license settings section.
		 *
		 * @param array $sections Sections.
		 * @return array
		 */
		public static function settings_sections( $sections ) {
			$sections['license'] = __( 'License', 'wc-price-based-country-pro' );
			return $sections;
		}

		/**
		 * Add variable bulk actions options.
		 */
		public static function variable_product_bulk_edit_actions() {

			$variable_actions = array(
				'regular_price'          => __( 'Set regular prices', 'wc-price-based-country-pro' ),
				'regular_price_increase' => __( 'Increase regular prices (fixed amount or percentage)', 'wc-price-based-country-pro' ),
				'regular_price_decrease' => __( 'Decrease regular prices (fixed amount or percentage)', 'wc-price-based-country-pro' ),
				'sale_price'             => __( 'Set sale prices', 'wc-price-based-country-pro' ),
				'sale_price_increase'    => __( 'Increase sale prices (fixed amount or percentage)', 'wc-price-based-country-pro' ),
				'sale_price_decrease'    => __( 'Decrease sale prices (fixed amount or percentage)', 'wc-price-based-country-pro' ),
			);

			foreach ( WCPBC_Pricing_Zones::get_zones() as $zone ) {
				echo '<optgroup label="' . esc_attr( sprintf( '%s ' . __( 'Pricing', 'wc-price-based-country-pro' ) . ' (%s)', $zone->get_name(), get_woocommerce_currency_symbol( $zone->get_currency() ) ) ) . '">';

				foreach ( $variable_actions as $key => $label ) {
					echo '<option value="wcpbc_variable_bluck_edit" data-action="' . esc_attr( $key ) . '" data-zone-id="' . esc_attr( $zone->get_zone_id() ) . '">' . esc_html( $label ) . '</option>';
				}
				echo '</optgroup>';
			}
		}

		/**
		 * Process variations bulk edit.
		 *
		 * @since 2.16
		 * @param string $bulk_action Bulk action.
		 * @param array  $data Post data.
		 * @param int    $product_id Product ID.
		 * @param array  $variations Array of product variations.
		 */
		public static function bulk_edit_variations_default( $bulk_action, $data, $product_id, $variations ) {
			if ( 'wcpbc_variable_bluck_edit' !== $bulk_action ) {
				return;
			}
			$allowed_actions = array( 'regular_price', 'regular_price_increase', 'regular_price_decrease', 'sale_price', 'sale_price_increase', 'sale_price_decrease' );
			$action          = empty( $data['action'] ) ? false : $data['action'];
			$zone_id         = empty( $data['zone_id'] ) ? false : $data['zone_id'];
			$value           = isset( $data['value'] ) ? $data['value'] : false;

			if ( ! $zone_id || ! in_array( $action, $allowed_actions, true ) ) {
				wp_die( esc_html__( 'Action is not allowed!', 'wc-price-based-country-pro' ) );
			}

			$zone = WCPBC_Pricing_Zones::get_zone_by_id( $zone_id );

			if ( ! $zone ) {
				wp_die( esc_html__( 'Zone does not exist!', 'wc-price-based-country-pro' ) );
			}

			$field    = 'regular_price' === substr( $action, 0, 13 ) ? '_regular_price' : '_sale_price';
			$increase = substr( str_replace( array( 'regular_price', 'sale_price' ), '', $action ), 1 );
			$operator = 'increase' === $increase ? '+' : '-';

			foreach ( $variations as $variation_id ) {
				if ( $increase ) {
					$field_value = $zone->get_postmeta( $variation_id, $field );
					if ( '%' === substr( $value, -1 ) ) {
						$percent      = wc_format_decimal( substr( $value, 0, -1 ) );
						$field_value += round( ( $field_value / 100 ) * $percent, wc_get_price_decimals() ) * "{$operator}1";
					} else {
						$field_value += $value * "{$operator}1";
					}
				} else {
					$field_value = wc_format_decimal( $value );
				}

				wcpbc_update_product_pricing(
					$variation_id,
					$zone,
					array(
						'_price_method' => 'manual',
						$field          => $field_value,
					)
				);

				do_action( 'wc_price_based_country_after_bulk_edit_variation', $variation_id, $zone, $field, $data );
			}
		}

		/**
		 * Display coupon amount options.
		 *
		 * @param int $post_id Post ID.
		 * @since 2.4.7
		 */
		public static function coupon_options( $post_id ) {
			$coupon_version = get_post_meta( $post_id, '_wcpbc_pro_version', true );
			$pricing_type   = get_post_meta( $post_id, 'zone_pricing_type', true );

			foreach ( WCPBC_Pricing_Zones::get_zones() as $zone ) {
				if ( empty( $coupon_version ) ) {
					if ( ! wcpbc_is_exchange_rate( $pricing_type ) ) {
						$zone->set_postmeta( $post_id, '_price_method', 'manual' );
						$zone->set_postmeta( $post_id, 'coupon_amount', get_post_meta( $post_id, 'coupon_amount', true ) );
					}
				}

				include WC_Product_Price_Based_Country_Pro::plugin_path() . 'includes/admin/views/html-coupon-pricing.php';
			}
		}

		/**
		 * Save coupon amount options.
		 *
		 * @since 2.4.7
		 * @param int $post_id Post ID.
		 */
		public static function coupon_options_save( $post_id ) {
			$discount_type = get_post_meta( $post_id, 'discount_type', true );
			if ( 'percent' !== $discount_type ) {
				foreach ( WCPBC_Pricing_Zones::get_zones() as $zone ) {
					$price_method = empty( $_POST[ $zone->get_postmetakey( '_price_method' ) ] ) ? '' : wc_clean( wp_unslash( $_POST[ $zone->get_postmetakey( '_price_method' ) ] ) ); // phpcs:ignore WordPress.Security.NonceVerification
					if ( wcpbc_is_exchange_rate( $price_method ) ) {
						$coupon_amount = empty( $_POST['coupon_amount'] ) ? : $zone->get_exchange_rate_price( wc_format_decimal( wc_clean( wp_unslash( $_POST['coupon_amount'] ) ) ), false ); // phpcs:ignore WordPress.Security.NonceVerification
					} else {
						$coupon_amount = empty( $_POST[ $zone->get_postmetakey( 'coupon_amount' ) ] ) ? '' : wc_format_decimal( wc_clean( wp_unslash( $_POST[ $zone->get_postmetakey( 'coupon_amount' ) ] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
					}

					$zone->set_postmeta( $post_id, '_price_method', $price_method );
					$zone->set_postmeta( $post_id, 'coupon_amount', $coupon_amount );
				}
			}
			update_post_meta( $post_id, 'zone_pricing_type', 'nothing' );
			update_post_meta( $post_id, '_wcpbc_pro_version', WC_Product_Price_Based_Country_Pro::$version );
		}

		/**
		 * Display custom order item action buttons
		 *
		 * @since 2.2.2
		 * @param WC_Order $order Order instance.
		 */
		public static function order_item_add_action_buttons( $order ) {
			if ( version_compare( WC_VERSION, '3.0', '>=' ) && $order->is_editable() ) {
				echo '<button type="button" class="button wcpbc-load-pricing-action">' . esc_html__( 'Load country pricing', 'wc-price-based-country-pro' ) . '</button>';
			}
		}

		/**
		 * Update the order with the price of the Order country.
		 *
		 * @since 2.2.2
		 * @throws Exception Invalid order.
		 */
		public static function load_country_pricing() {
			check_ajax_referer( 'load-country-pricing', 'security' );

			if ( ! current_user_can( 'edit_shop_orders' ) ) {
				wp_die( -1 );
			}
			$postdata           = wc_clean( wp_unslash( $_POST ) );
			$order_id           = isset( $postdata['order_id'] ) ? absint( $postdata['order_id'] ) : 0;
			$billing_country    = isset( $postdata['billing_country'] ) ? strtoupper( $postdata['billing_country'] ) : false;
			$shipping_country   = isset( $postdata['shipping_country'] ) ? strtoupper( $postdata['shipping_country'] ) : false;
			$calculate_tax_args = isset( $postdata['tax_args'] ) ? array_map( 'strtoupper', wc_clean( $postdata['tax_args'] ) ) : array();
			$country            = empty( $shipping_country ) || 'billing' === get_option( 'wc_price_based_country_based_on', 'billing' ) ? $billing_country : $shipping_country;
			$_items             = isset( $_POST['items'] ) ? $_POST['items'] : false; // WPCS: sanitization ok.
			// Parse the jQuery serialized items.
			$items = array();
			parse_str( $_items, $items );

			// Get the order.
			$order = wc_get_order( $order_id );
			if ( ! $order ) {
				throw new Exception( __( 'Invalid order', 'wc-price-based-country-pro' ) );
			}

			$zone = WCPBC_Pricing_Zones::get_zone_by_country( $country );

			if ( $zone ) {
				$order->update_meta_data( '_wcpbc_base_exchange_rate', $zone->get_base_currency_amount( 1 ) );
				$order->update_meta_data( '_wcpbc_pricing_zone', $zone->get_data() );

				// Load the front-end pricing for the current zone.
				wcpbc()->current_zone = $zone;
				if ( ! did_action( 'wc_price_based_country_frontend_princing_init' ) ) {
					WCPBC_Frontend_Pricing::init();
				}
			}

			// Set order items totals.
			foreach ( $items['order_item_id'] as $item_id ) {
				$item = WC_Order_Factory::get_order_item( absint( $item_id ) );
				if ( ! $item ) {
					continue;
				}

				if ( 'line_item' === $item->get_type() ) {
					$product = $item->get_product();
					$item->set_total( wc_get_price_excluding_tax( $product, array( 'qty' => $item->get_quantity() ) ) );
					$item->set_subtotal( wc_get_price_excluding_tax( $product, array( 'qty' => $item->get_quantity() ) ) );

					do_action( 'wc_price_based_country_manual_order_before_line_save', $item, $product );

					$item->save();

				} elseif ( 'fee' === $item->get_type() ) {

					$base_amount = $item->get_meta( '_wcpbc_base_amount', true, 'edit' );

					if ( ! $base_amount ) {
						$order_zone  = WCPBC_Pricing_Zones::get_zone_from_order( $order );
						$base_amount = $order_zone ? $order_zone->get_base_currency_amount( $item->get_amount() ) : $item->get_amount();
						$item->update_meta_data( '_wcpbc_base_amount', $base_amount );
					}

					$amount = $zone ? $zone->get_exchange_rate_price( $base_amount ) : $base_amount;

					if ( false === strpos( $item->get_name(), '%' ) ) {
						$formatted_amount = wc_price( $amount );
						/* translators: %s fee amount */
						$item->set_name( sprintf( __( '%s fee', 'wc-price-based-country-pro' ), wc_clean( $formatted_amount ) ) );
					}
					$item->set_amount( $amount );
					$item->set_total( $amount );
					$item->save();
				}
			}

			// Update order pricing zone.
			if ( $zone ) {
				$order->update_meta_data( '_wcpbc_base_exchange_rate', $zone->get_base_currency_amount( 1 ) );
				$order->update_meta_data( '_wcpbc_pricing_zone', $zone->get_data() );

			} else {
				$order->delete_meta_data( '_wcpbc_base_exchange_rate' );
				$order->delete_meta_data( '_wcpbc_pricing_zone' );
			}

			// Grab the order and recalculate taxes.
			$order->set_currency( get_woocommerce_currency() );
			$order->calculate_taxes( $calculate_tax_args );
			$order->calculate_totals( false );
			$order->save();

			include dirname( WC_PLUGIN_FILE ) . '/includes/admin/meta-boxes/views/html-order-items.php';

			wp_die();
		}


		/**
		 * Hide the renewal licence notice.
		 */
		public static function hide_renewal_license_notice() {
			if ( ! empty( $_GET['hide-renewal-license-notice'] ) && check_admin_referer( 'hide_renewal_license_notice' ) ) {
				WCPBC_License_Settings::instance()->unset_renewal_period();
			}
		}

		/**
		 * Display the renew license notice.
		 */
		public static function renewal_license_notice() {
			$page = ( isset( $_GET['page'] ) ? $_GET['page'] : '' ) . '-' . ( isset( $_GET['tab'] ) ? $_GET['tab'] : '' ) . '-' . ( isset( $_GET['section'] ) ? $_GET['section'] : '' ); // phpcs:ignore WordPress.Security.NonceVerification
			if ( 'wc-settings-price-based-country-license' === $page ) {
				// No display for license settings tab.
				return;
			}
			$options      = WCPBC_License_Settings::instance();
			$license_data = $options->get_license_data();
			if ( 'yes' === $license_data['renewal_period'] ) {
				$expires     = new DateTime( $license_data['expires'] );
				$now         = new DateTime();
				$interval    = $expires->diff( $now );
				$days        = $interval->format( '%a' );
				$renewal_url = empty( $license_data['renewal_url'] ) ? 'https://www.pricebasedcountry.com/pricing/?utm_source=activate-license&utm_medium=banner&utm_campaign=Renew' : $license_data['renewal_url'];
				// include the view.
				include dirname( __FILE__ ) . '/views/html-notice-renew.php';
			}

		}
	}

endif;
