<?php
/**
 * Handle integration with Affiliate WP.
 *
 * @version 2.4.0
 * @package WCPBC/Integrations
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WCPBC_Affiliate_WP' ) ) :

	/**
	 * WCPBC_Affiliate_WP Class
	 */
	class WCPBC_Affiliate_WP {

		/**
		 * Hook actions and filters
		 */
		public static function init() {
			add_action( 'admin_init', array( __CLASS__, 'user_update_affwp_exchange_rates' ), 5 );
			add_action( 'admin_init', array( __CLASS__, 'install' ) );
			add_action( 'woocommerce_scheduled_sales', array( __CLASS__, 'update_affwp_exchange_rates' ), 20 );
			add_action( 'wc_price_based_country_before_region_data_save', array( __CLASS__, 'update_affwp_exchange_rate' ), 20 );
			add_filter( 'wc_price_based_country_default_region_data', array( __CLASS__, 'default_region_data' ), 20 );
			add_filter( 'wc_price_based_country_table_region_column_currency', array( __CLASS__, 'column_currency' ), 20, 3 );
			add_filter( 'affwp_calc_referral_amount', array( __CLASS__, 'calculate_referral_amount' ), 100, 5 );
			add_filter( 'mla_product_referral_woocommerce', array( __CLASS__, 'mla_product_referral' ), 100, 2 );
		}

		/**
		 * Update the addwp exchage rates
		 */
		public static function update_affwp_exchange_rates() {
			$affwp_currency = affwp_get_currency();
			$wc_currency    = get_option( 'woocommerce_currency' );
			$affwp_option   = get_option( 'wc_price_based_country_affwp', array() );
			$zones          = get_option( 'wc_price_based_country_regions', array() );

			if ( $affwp_currency !== $wc_currency ) {

				// Get affwp currency exchange rates.
				$to_currency = array( $wc_currency );

				foreach ( $zones as $zone ) {
					$to_currency[] = $zone['currency'];
				}

				$rates = WCPBC_Update_Exchange_Rates::get_exchange_rate_from_api( array_unique( $to_currency ), $affwp_currency );
				if ( $rates ) {
					foreach ( $zones as $zone_id => $zone ) {
						if ( isset( $rates[ $zone['currency'] ] ) ) {
							$zones[ $zone_id ]['affwp_exchange_rate'] = $rates[ $zone['currency'] ];
						}
					}
				}

				$affwp_option = array(
					'currency'      => $affwp_currency,
					'exchange_rate' => $rates[ $wc_currency ],
				);

			} else {

				$affwp_option = array(
					'currency'      => $affwp_currency,
					'exchange_rate' => '1',
				);

				foreach ( $zones as $zone_id => $zone ) {
					unset( $zones[ $zone_id ]['affwp_exchange_rate'] );
				}
			}

			update_option( 'wc_price_based_country_affwp', $affwp_option );
			update_option( 'wc_price_based_country_regions', $zones );
		}

		/**
		 * User update the affwp exchange rates
		 */
		public static function user_update_affwp_exchange_rates() {
			if ( isset( $_GET['wc_price_based_country_update_affwp_nonce'] ) && wp_verify_nonce( $_GET['wc_price_based_country_update_affwp_nonce'], 'wc_price_based_country_update_affwp' ) ) { // WPCS: sanitization ok.
				self::update_affwp_exchange_rates();
				add_action( 'admin_notices', array( __CLASS__, 'display_affwp_updated_notice' ) );
			}
		}

		/**
		 * Check the affiliatewp option
		 */
		public static function install() {
			$affwp_option = get_option( 'wc_price_based_country_affwp', false );
			if ( ! $affwp_option ) {
				$affwp_option = array(
					'currency'      => affwp_get_currency(),
					'exchange_rate' => '1',
				);

				update_option( 'wc_price_based_country_affwp', $affwp_option );
			}

			if ( ( get_option( 'woocommerce_currency' ) !== $affwp_option['currency'] && '1' === $affwp_option['exchange_rate'] ) || affwp_get_currency() !== $affwp_option['currency'] ) {
				// Update exchange rates require.
				add_action( 'admin_notices', array( __CLASS__, 'display_affwp_notice' ) );
			}
		}

		/**
		 * Display exchange rates updated notice
		 */
		public static function display_affwp_updated_notice() {
		?><div class="notice notice-success is-dismissible">
				<p><?php esc_html_e( 'Exchange rates of AffiliateWP currency have been updated.', 'wc-price-based-country-pro' ); ?></p>
			</div>
		<?php
		}

		/**
		 * Display option needs update notice
		 */
		public static function display_affwp_notice() {
		?>
			<div class="error">
				<p>
				<?php
					// translators: HTML tags.
					echo wp_kses_post( sprintf( __( 'To add compatibility with AffiliateWP, %1$sPrice Based on Country%2$s needs to update the exchange rates.', 'wc-price-based-country-pro' ), '<strong>', '</strong>' ) );
				?>
				</p>
				<p class="submit"><a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=wc-settings&tab=price-based-country&section=zones' ), 'wc_price_based_country_update_affwp', 'wc_price_based_country_update_affwp_nonce' ) ); ?>" class="wc-update-now button-primary"><?php esc_html_e( 'Update exchange rates now', 'wc-price-based-country-pro' ); ?></a></p>
			</div>
		<?php
		}

		/**
		 * Update exchange rate from api before save
		 *
		 * @since 2.3.0
		 * @return void
		 */
		public static function update_affwp_exchange_rate() {
			if ( get_option( 'woocommerce_currency' ) !== affwp_get_currency() && ! empty( $_POST['currency'] ) ) { // WPCS: CSRF ok.
				$rate = WCPBC_Update_Exchange_Rates::get_exchange_rate_from_api( wc_clean( $_POST['currency'] ), affwp_get_currency() ); // WPCS: CSRF ok, sanitization ok.
				if ( $rate ) {
					$_POST['affwp_exchange_rate'] = $rate;
				}
			}
		}

		/**
		 * Add aff exchange rate to default region data
		 *
		 * @since 2.3.0
		 * @param  array $defaults Default region data values.
		 * @return array
		 */
		public static function default_region_data( $defaults ) {
			$defaults['affwp_exchange_rate'] = 0;
			return $defaults;
		}

		/**
		 * Display Affiliate WP exchange rate in currency column.
		 *
		 * @param string $output Columns content.
		 * @param array  $zone Zone data.
		 * @param string $default_zone_key Default zone key.
		 * @return string
		 */
		public static function column_currency( $output, $zone, $default_zone_key ) {

			$format = '<br /><span class="description">1 %s = %s %s</span> (AffiliateWP)';

			if ( $zone['key'] === $default_zone_key ) {

				if ( get_option( 'woocommerce_currency' ) !== affwp_get_currency() ) {

					$affwp_option = get_option( 'wc_price_based_country_affwp', false );

					$output .= sprintf( $format, affwp_get_currency(), $affwp_option['exchange_rate'], get_option( 'woocommerce_currency' ) );
				}
			} elseif ( ! empty( $zone['affwp_exchange_rate'] ) ) {

				$output .= sprintf( $format, affwp_get_currency(), $zone['affwp_exchange_rate'], $zone['currency'] );
			}

			return $output;
		}

		/**
		 * Converts a recurring referral amount to the AffiliateWP currency, before the referral is created.
		 *
		 * @param  string $referral_amount Referral amount.
		 * @param  int    $affiliate_id Affiliate ID.
		 * @param  string $amount Order amount.
		 * @param  int    $reference The order ID.
		 * @param  int    $product_id Product ID.
		 * @return float
		 */
		public static function calculate_referral_amount( $referral_amount, $affiliate_id, $amount, $reference, $product_id ) {

			if ( defined( 'WOOCOMMERCE_CHECKOUT' ) && $reference ) {

				$exchange_rate = 1;

				$type = empty( $product_id ) ? '' : get_post_meta( $product_id, '_affwp_woocommerce_product_rate_type', true );

				if ( empty( $type ) ) {
					$type = affwp_get_affiliate_rate_type( $affiliate_id );
				}

				if ( 'flat' !== $type ) {
					// Get the exchange rate.
					$exchange_rate = self::get_exchange_rate_from_order( $reference );
				}

				if ( floatval( $exchange_rate ) !== 1 && $exchange_rate > 0 ) {
					$decimals        = affwp_get_decimal_count();
					$referral_amount = round( $referral_amount / $exchange_rate, $decimals );
				}
			}

			return $referral_amount;
		}

		/**
		 * Converts AffiliateWP MLA referrals at a per product level for WooCommerce only
		 *
		 * @param array $referral Referral data.
		 * @param array $filter_vars Filter vars.
		 * @return array
		 */
		public static function mla_product_referral( $referral, $filter_vars ) {

			$exchange_rate = 1;
			$type          = $filter_vars['rate_type'];
			$reference     = $filter_vars['matrix_data']['args']['reference'];

			if ( 'flat' !== $type ) {
				// Get the exchange rate.
				$exchange_rate = self::get_exchange_rate_from_order( $reference );
			}

			if ( 1 !== $exchange_rate && $exchange_rate > 0 ) {
				$decimals                            = affwp_get_decimal_count();
				$referral['product_referral_amount'] = round( $referral['product_referral_amount'] / $exchange_rate, $decimals );
				$referral['product_referral_log'][]  = __( 'Amount converted by PBC', 'wc-price-based-country' ) . ': ' . $referral['product_referral_amount'];

			}

			return $referral;
		}

		/**
		 * Retrun the exchange rate for the order
		 *
		 * @param int $order Order ID.
		 * @return float
		 */
		private static function get_exchange_rate_from_order( $order ) {
			$zone = WCPBC_Pricing_Zones::get_zone_from_order( $order );
			if ( $zone ) {
				$exchange_rate = $zone->get_affwp_exchange_rate();
			} else {
				$affwp_option  = get_option( 'wc_price_based_country_affwp', false );
				$exchange_rate = empty( $affwp_option['exchange_rate'] ) ? 1 : floatval( $affwp_option['exchange_rate'] );
			}

			return $exchange_rate;
		}
	}

	WCPBC_Affiliate_WP::init();

endif;
