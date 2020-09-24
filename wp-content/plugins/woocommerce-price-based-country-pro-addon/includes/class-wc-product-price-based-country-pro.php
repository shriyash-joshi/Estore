<?php
/**
 * WooCommerce Price Based on Country Pro Add-on setup
 *
 * @package WCPBC
 * @version 2.6.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * WC_Product_Price_Based_Country_Pro Class
 */
class WC_Product_Price_Based_Country_Pro {

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	public static $version = '2.8.13';

	/**
	 * Admin notices.
	 *
	 * @var array
	 */
	private static $admin_notices;

	/**
	 * Min WC required version.
	 *
	 * @var string
	 */
	private static $min_wc_version = '3.4';

	/**
	 * Min PBC required version.
	 *
	 * @var string
	 */
	private static $min_pbc_version = '2.0.0';

	/**
	 * Get the plugin url.
	 *
	 * @return string
	 */
	public static function plugin_url() {
		return plugin_dir_url( WCPBC_PRO_PLUGIN_FILE );
	}

	/**
	 * Get the plugin path.
	 *
	 * @return string
	 */
	public static function plugin_path() {
		return plugin_dir_path( WCPBC_PRO_PLUGIN_FILE );
	}

	/**
	 * Init plugin, Hook actions and filters
	 *
	 * @since 1.0
	 */
	public static function init() {

		self::$admin_notices = array();

		self::includes();

		register_activation_hook( WCPBC_PRO_PLUGIN_FILE, array( __CLASS__, 'install' ) );
		register_deactivation_hook( WCPBC_PRO_PLUGIN_FILE, array( __CLASS__, 'clear_weekly_jobs' ) );

		add_action( 'plugins_loaded', array( __CLASS__, 'init_plugin' ), 20 );
		add_action( 'init', array( __CLASS__, 'load_textdomain' ) );
		add_action( 'admin_init', array( __CLASS__, 'update_plugin_version' ) );
		add_action( 'admin_notices', array( __CLASS__, 'display_notices' ) );
	}

	/**
	 * Include required files
	 *
	 * @access private
	 * @since 1.0
	 * @return void
	 */
	private static function includes() {

		include_once self::plugin_path() . 'includes/abstracts/class-wcpbc-exchange-rates-provider.php';
		include_once self::plugin_path() . 'includes/class-wcpbc-update-exchange-rates.php';
		include_once self::plugin_path() . 'includes/class-wcpbc-frontend-currency.php';
		include_once self::plugin_path() . 'includes/class-wcpbc-integrations-pro.php';
		include_once self::plugin_path() . 'includes/class-wcpbc-shortcodes.php';

		if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
			include_once self::plugin_path() . 'includes/admin/class-wc-plugin-api-wrapper.php';
			include_once self::plugin_path() . 'includes/admin/class-wcpbc-plugin-updater.php';
			include_once self::plugin_path() . 'includes/admin/class-wcpbc-admin-pro.php';
			include_once self::plugin_path() . 'includes/admin/class-wcpbc-admin-product-csv.php';
		}
	}

	/**
	 * Load text domain
	 *
	 * @since 2.2.7
	 */
	public static function load_textdomain() {
		load_plugin_textdomain( 'wc-price-based-country-pro', false, dirname( plugin_basename( WCPBC_PRO_PLUGIN_FILE ) ) . '/languages/' );
	}

	/**
	 * Install
	 *
	 * @since 1.1
	 * @return void
	 */
	public static function install() {

		// Update currency format option.
		$currency_format = get_option( 'wc_price_based_currency_format', false );

		if ( ! $currency_format ) {
			$currency_pos = get_option( 'woocommerce_currency_pos' );
			$format       = '[symbol][price]';

			switch ( $currency_pos ) {
				case 'left':
					$format = '[symbol][price]';
					break;
				case 'right':
					$format = '[price][symbol]';
					break;
				case 'left_space':
					$format = '[symbol]&nbsp;[price]';
					break;
				case 'right_space':
					$format = '[price]&nbsp;[symbol]';
					break;
			}

			update_option( 'wc_price_based_currency_format', $format );
		}

		// WooCommerce subscriptions supports.
		delete_transient( 'wc_report_subscription_events_by_date' );
		delete_transient( 'wc_report_upcoming_recurring_revenue' );

		// Trigger action.
		do_action( 'wc_price_based_country_pro_installed' );

		update_option( 'wc_price_based_country_pro_version', self::$version );

		// Increments the transient version to invalidate cache.
		WC_Cache_Helper::get_transient_version( 'product', true );
	}

	/**
	 * Plugin deactivation.
	 */
	public static function clear_weekly_jobs() {
		wp_clear_scheduled_hook( 'wc_price_based_country_weekly_scheduled_events' );
	}

	/**
	 * Update plugin version
	 *
	 * @since 2.1.9
	 * @return void
	 */
	public static function update_plugin_version() {

		$current_version = get_option( 'wc_price_based_country_pro_version', '1.0' );

		if ( ! defined( 'IFRAME_REQUEST' ) && $current_version !== self::$version ) {

			if ( version_compare( $current_version, '2.1.9', '<' ) ) {
				wp_clear_scheduled_hook( 'wc_price_based_country_pro_cron' );
			}

			if ( version_compare( $current_version, '2.2.9', '<' ) ) {
				if ( 'fixerio' === get_option( 'wc_price_based_country_exchange_rate_api' ) ) {
					update_option( 'wc_price_based_country_exchange_rate_api', 'floatrates' );
				}
			}

			if ( version_compare( $current_version, '2.3.0', '<' ) ) {
				if ( 'yahoofinance' === get_option( 'wc_price_based_country_exchange_rate_api' ) ) {
					update_option( 'wc_price_based_country_exchange_rate_api', 'floatrates' );
				}
			}

			update_option( 'wc_price_based_country_pro_version', self::$version );

			if ( 'yes' === get_option( 'wc_price_based_country_caching_support', 'no' ) && is_callable( array( 'WC_Cache_Helper', 'get_transient_version' ) ) ) {
				// Increments the transient version to invalidate cache.
				WC_Cache_Helper::get_transient_version( 'product', true );
			}
		}
	}

	/**
	 * Init plugin
	 *
	 * @access private
	 * @since 1.0
	 * @return void
	 */
	public static function init_plugin() {

		if ( ! self::check_environment() ) {
			return;
		}

		// Include dependent files.
		include_once self::plugin_path() . 'includes/class-wcpbc-pricing-zone-pro.php';
		include_once self::plugin_path() . 'includes/admin/class-wcpbc-license-settings.php';
		if ( version_compare( WC_VERSION, '3.0', '>=' ) ) {
			include_once self::plugin_path() . 'includes/class-wcpbc-order-base-currency.php';
			include_once self::plugin_path() . 'includes/class-wcpbc-order-base-currency-item-tax.php';
		}

		// Init plugin.
		WCPBC_Update_Exchange_Rates::init();
		WCPBC_Frontend_Currency::init();
		WCPBC_Integrations_Pro::init();
		WCPBC_Shortcodes::init();

		if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {

			WCPBC_Admin_Pro::init();
			WCPBC_Admin_Product_CSV::init();
			self::updater();
		}

		// Register widget.
		add_action( 'widgets_init', array( __CLASS__, 'register_widgets' ), 20 );
		// Front-end pricing init.
		add_action( 'wc_price_based_country_frontend_princing_init', array( __CLASS__, 'frontend_princing_init' ) );

		// Rest API orders.
		add_action( 'parse_request', array( __CLASS__, 'rest_api_init' ), 0 );

		// Scheduled events.
		add_filter( 'cron_schedules', array( __CLASS__, 'add_schedules' ) );

		if ( ! wp_next_scheduled( 'wc_price_based_country_weekly_scheduled_events' ) ) {
			wp_schedule_event( time() + 10, 'weekly', 'wc_price_based_country_weekly_scheduled_events' );
		}

		add_action( 'wc_price_based_country_weekly_scheduled_events', array( __CLASS__, 'weekly_jobs' ) );
	}

	/**
	 * Checks the environment for compatibility problems.
	 *
	 * @return boolean
	 */
	private static function check_environment() {

		if ( ! class_exists( 'WC_Product_Price_Based_Country' ) ) {
			// translators: HTML Tags.
			self::$admin_notices[] = sprintf( __( '%1$sPrice Based on Country Pro%2$s needs the Basic version to work. Learn how to install Price Based on Country in the %3$sGetting Started Guide%4$s.', 'wc-price-based-country-pro' ), '<strong>', '</strong>', '<a href="https://www.pricebasedcountry.com/docs/getting-started/">', '</a>' );
			return false;
		}

		if ( ! defined( 'WC_VERSION' ) ) {
			// translators: HTML Tags.
			self::$admin_notices[] = sprintf( __( '%1$sPrice Based on Country Pro%2$s requires WooCommerce to be activated to work. Learn how to install Price Based on Country in the %3$sGetting Started Guide%4$s.', 'wc-price-based-country-pro' ), '<strong>', '</strong>', '<a href="https://www.pricebasedcountry.com/docs/getting-started/">', '</a>' );
			return false;
		}

		if ( version_compare( WC_VERSION, self::$min_wc_version, '<' ) ) {
			// translators: HTML Tags.
			self::$admin_notices[] = sprintf( __( 'Price Based on Country Pro - The minimum WooCommerce version required for this plugin is %1$s. You are running %2$s.', 'wc-price-based-country-pro' ), self::$min_wc_version, WC_VERSION );
			return false;
		}

		if ( version_compare( WCPBC()->version, self::$min_pbc_version, '<' ) ) {
			// translators: HTML Tags.
			self::$admin_notices[] = sprintf( __( '%1$sPrice Based on Country Pro%2$s - The minimum Price Based on Country Basic version required for this plugin is %3$s. You are running %4$s.', 'wc-price-based-country-pro' ), '<strong>', '</strong>', self::$min_pbc_version, WCPBC()->version );
			return false;
		}

		return true;
	}

	/**
	 * Init plugin updater
	 */
	private static function updater() {
		if ( ! is_admin() || ! class_exists( 'WCPBC_License_Settings' ) ) {
			return;
		}

		$options     = WCPBC_License_Settings::instance();
		$license_key = $options->get_license_key();
		$api_key     = $options->get_api_key();

		if ( ! $license_key || ! $api_key ) {
			add_action( 'after_plugin_row_' . plugin_basename( WCPBC_PRO_PLUGIN_FILE ), array( __CLASS__, 'plugin_update_row' ), 20 );
			add_filter( 'wp_get_update_data', array( __CLASS__, 'update_data' ) );
			add_filter( 'wc_price_based_country_admin_notices', array( __CLASS__, 'no_license_admin_notices' ) );
			return;
		}

		$updater = new WCPBC_Plugin_Updater(
			WCPBC_PRO_PLUGIN_FILE,
			self::$version,
			array(
				'item_id'     => 1450,
				'license_key' => $license_key,
				'api_key'     => $api_key,
			),
			array( $options, 'update_check_error' )
		);
	}

	/**
	 * Display admin notices
	 */
	public static function display_notices() {
		foreach ( self::$admin_notices as $notice ) {
			echo '<div id="message" class="error"><p>' . wp_kses_post( $notice ) . '</p></div>';
		}
	}

	/**
	 * Display a warning message after plugin row if the license is not set.
	 */
	public static function plugin_update_row() {
		$wp_list_table = _get_list_table( 'WP_Plugins_List_Table' );
		$slug          = basename( WCPBC_PRO_PLUGIN_FILE, '.php' );
		// translators: HTML Tags.
		$message = sprintf( __( '%1$sWarning!%2$s You didn\'t set your license key, which means you\'re missing out on updates and support! Enter your %3$slicense key%4$s.', 'wc-price-based-country-pro' ), '<strong>', '</strong>', '<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=price-based-country&section=license' ) ) . '">', '</a>' );
		?>
		<tr class="plugin-update-tr active" id="<?php echo esc_attr( $slug . '-nolicense' ); ?> "><td colspan="<?php echo esc_attr( $wp_list_table->get_column_count() ); ?>" class="plugin-update colspanchange"><div class="update-message notice inline notice-error notice-alt">
			<p>
				<?php echo wp_kses_post( $message ); ?>
			</p>
		</div></td></tr>
		<script>
		jQuery(document).ready(function($){
			$('tr[data-slug="woocommerce-price-based-on-country-pro-add-on"]').addClass('update');
		});
		</script>
		<?php
	}

	/**
	 * Increments the plugins count to display an alert on the plugin menu item.
	 *
	 * @param array $update_data Update data array.
	 * @return array
	 */
	public static function update_data( $update_data ) {
		if ( current_user_can( 'update_plugins' ) ) {
			$update_data['counts']['plugins']++;
		}
		return $update_data;
	}

	/**
	 * Add admin notices
	 *
	 * @param array $notices Admin notices.
	 * @return array
	 */
	public static function no_license_admin_notices( $notices ) {
		$notices['no_license'] = array(
			'hide'     => 'no',
			'callback' => array( __CLASS__, 'display_no_license_notice' ),
		);
		return $notices;
	}

	/**
	 * Check license is active
	 */
	public static function display_no_license_notice() {

		if ( ! ( isset( $_GET['tab'] ) && 'price-based-country' === $_GET['tab'] && isset( $_GET['section'] ) && 'license' === $_GET['section'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			// translators: HTML Tags.
			$notice = sprintf( __( '%1$sWarning!%2$s You didn\'t set your %1$sWooCommerce Price Based Country Pro Add-on%2$s license key yet, which means you\'re missing out on updates and support! Enter your %3$slicense key%4$s.', 'wc-price-based-country-pro' ), '<strong>', '</strong>', '<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=price-based-country&section=license' ) ) . '">', '</a>' );
			echo '<div id="message" class="error' . ( 'admin_notices' !== current_action() ? ' inline' : '' ) . '"><p>' . wp_kses_post( $notice ) . '</p></div>';
		}
	}

	/**
	 * Register Widgets
	 */
	public static function register_widgets() {
		if ( class_exists( 'WC_Widget' ) ) {
			include_once self::plugin_path() . 'includes/class-wcpbc-widget-currency-switcher.php';
			register_widget( 'WCPBC_Widget_Currency_Switcher' );
		}
	}

	/**
	 * Front-end pricing init
	 */
	public static function frontend_princing_init() {
		add_action( 'woocommerce_coupon_loaded', array( __CLASS__, 'coupon_loaded' ), 20 );
	}

	/**
	 * Set coupon amount
	 *
	 * @param WC_Coupon $coupon Coupon instance.
	 */
	public static function coupon_loaded( $coupon ) {
		$coupon_id     = version_compare( WC_VERSION, '3.0', '<' ) ? $coupon->id : $coupon->get_id();
		$discount_type = version_compare( WC_VERSION, '3.0', '<' ) ? $coupon->discount_type : $coupon->get_discount_type();
		$pro_version   = get_post_meta( $coupon_id, '_wcpbc_pro_version', true );

		if ( in_array( $discount_type, array( 'percent', 'sign_up_fee_percent', 'recurring_percent' ), true ) || empty( $pro_version ) ) {
			return;
		}

		$amount = wcpbc_the_zone()->get_post_price( $coupon_id, 'coupon_amount', 'coupon' );

		if ( version_compare( WC_VERSION, '3.0', '<' ) ) {
			$coupon->coupon_amount = $amount;
		} else {
			$coupon->set_amount( $amount );
		}
	}

	/**
	 * WooCommerce REST API init.
	 */
	public static function rest_api_init() {
		if ( ! ( function_exists( 'wc' ) && is_callable( array( wc(), 'is_rest_api_request' ) ) && wc()->is_rest_api_request() && ! empty( $GLOBALS['wp']->query_vars['rest_route'] ) ) ) {
			return;
		}
		$route = untrailingslashit( $GLOBALS['wp']->query_vars['rest_route'] );

		// Support for manual order via REST API.
		if ( preg_match( '/\/wc\/v\d\/orders$/', $route ) && 'post' === strtolower( $_SERVER['REQUEST_METHOD'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$body   = is_callable( array( 'WP_REST_Server', 'get_raw_data' ) ) ? WP_REST_Server::get_raw_data() : '';
			$params = json_decode( $body );
			if ( $params ) {
				$billing_country  = isset( $params->billing->country ) ? strtoupper( $params->billing->country ) : false;
				$shipping_country = isset( $params->shipping->country ) ? strtoupper( $params->shipping->country ) : false;
				$country          = empty( $shipping_country ) || 'billing' === get_option( 'wc_price_based_country_based_on', 'billing' ) ? $billing_country : $shipping_country;

				$zone = WCPBC_Pricing_Zones::get_zone_by_country( $country );

				if ( $zone ) {
					// Load the front-end pricing for the current zone.
					wcpbc()->current_zone = $zone;
					if ( ! did_action( 'wc_price_based_country_frontend_princing_init' ) ) {
						WCPBC_Frontend_Pricing::init();
					}
				}
			}
		}
	}

	/**
	 * Registers new cron schedules.
	 *
	 * @param array $schedules Schedules.
	 * @return array
	 */
	public static function add_schedules( $schedules = array() ) {
		// Adds once weekly to the existing schedules.
		$schedules['weekly'] = array(
			'interval' => 604800,
			'display'  => __( 'Once Weekly', 'wc-price-based-country-pro' ),
		);

		return $schedules;
	}

	/**
	 * Weekly jobs.
	 */
	public static function weekly_jobs() {
		if ( defined( 'DOING_CRON' ) && DOING_CRON ) {

			if ( ! class_exists( 'WC_Plugin_API_Wrapper' ) ) {
				include_once self::plugin_path() . 'includes/admin/class-wc-plugin-api-wrapper.php';
			}

			// Check the license status.
			$options = WCPBC_License_Settings::instance();
			$options->check_license_status();
		}
	}
}


