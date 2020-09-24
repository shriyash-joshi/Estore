<?php
/**
 * WooCommerce Price Based on Country main class
 *
 * @package WCPBC
 * @version 2.0.10
 */

defined( 'ABSPATH' ) || exit;

/**
 * WC_Product_Price_Based_Country Class
 */
class WC_Product_Price_Based_Country {

	/**
	 * Product Price Based Country version
	 *
	 * @var string
	 */
	public $version = '2.0.10';

	/**
	 * The front-end pricing zone
	 *
	 * @var WCPBC_Pricing_Zone
	 */
	public $current_zone = null;

	/**
	 * Min WC required version.
	 *
	 * @var string
	 */
	protected $min_wc_version = '3.4';

	/**
	 * Min Pro required version.
	 *
	 * @var string
	 */
	protected $min_pro_version = '2.8.9';

	/**
	 * Enviroment alert
	 *
	 * @var string
	 */
	protected $environment_alert = '';

	/**
	 * The single instance of the class.
	 *
	 * @var WC_Product_Price_Based_Country
	 */
	protected static $_instance = null;

	/**
	 * Main WC_Product_Price_Based_Country Instance
	 *
	 * @static
	 * @see WCPBC()
	 * @return WC_Product_Price_Based_Country
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Get the plugin url.
	 *
	 * @return string
	 */
	public function plugin_url() {
		return plugin_dir_url( WCPBC_PLUGIN_FILE );
	}

	/**
	 * Get the plugin path.
	 *
	 * @return string
	 */
	public function plugin_path() {
		return plugin_dir_path( WCPBC_PLUGIN_FILE );
	}

	/**
	 * Return the plugin base name
	 *
	 * @return string
	 * @since 1.7.4
	 */
	public function plugin_basename() {
		return plugin_basename( WCPBC_PLUGIN_FILE );
	}

	/**
	 * WC_Product_Price_Based_Country Constructor.
	 */
	public function __construct() {
		$this->includes();

		register_activation_hook( WCPBC_PLUGIN_FILE, array( 'WCPBC_Install', 'plugin_activate' ) );
		register_deactivation_hook( WCPBC_PLUGIN_FILE, array( 'WCPBC_Install', 'plugin_deactivate' ) );

		add_action( 'init', array( $this, 'load_textdomain' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( WCPBC_PLUGIN_FILE ), array( $this, 'plugin_action_links' ) );
		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
		add_action( 'plugins_loaded', array( $this, 'init_plugin' ), 5 );
	}

	/**
	 * Include required files used in admin and on the frontend.
	 */
	private function includes() {

		include_once $this->plugin_path() . 'includes/class-wcpbc-pricing-zone.php';
		include_once $this->plugin_path() . 'includes/class-wcpbc-pricing-zones.php';
		include_once $this->plugin_path() . 'includes/wcpbc-core-functions.php';
		include_once $this->plugin_path() . 'includes/class-wcpbc-integrations.php';
		include_once $this->plugin_path() . 'includes/class-wcpbc-frontend.php';
		include_once $this->plugin_path() . 'includes/class-wcpbc-frontend-pricing.php';
		include_once $this->plugin_path() . 'includes/class-wcpbc-ajax-geolocation.php';
		include_once $this->plugin_path() . 'includes/class-wcpbc-update-geoip-db.php';
		include_once $this->plugin_path() . 'includes/admin/class-wcpbc-admin-analytics.php';
		include_once $this->plugin_path() . 'includes/class-wcpbc-product-sync.php';

		if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
			include_once $this->plugin_path() . 'includes/class-wcpbc-install.php';
			include_once $this->plugin_path() . 'includes/admin/class-wcpbc-admin-notices.php';
			include_once $this->plugin_path() . 'includes/admin/class-wcpbc-admin.php';
			include_once $this->plugin_path() . 'includes/admin/class-wcpbc-admin-meta-boxes.php';
			include_once $this->plugin_path() . 'includes/admin/class-wcpbc-admin-ads.php';
		}

		if ( ( defined( 'DOING_CRON' ) || is_admin() ) && 'yes' === get_option( 'wc_price_based_country_allow_tracking', 'no' ) ) {
			include_once $this->plugin_path() . 'includes/class-wcpbc-tracker.php';
		}
	}

	/**
	 * Localisation
	 *
	 * @since 1.6.3
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'woocommerce-product-price-based-on-countries', false, dirname( plugin_basename( WCPBC_PLUGIN_FILE ) ) . '/languages/' );
	}

	/**
	 * Show action links on the plugin screen.
	 *
	 * @since 1.6.11
	 * @param mixed $links Plugin Action links.
	 * @return array
	 */
	public function plugin_action_links( $links ) {

		$action_links = array(
			'settings' => '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=price-based-country' ) . '" aria-label="' . esc_attr__( 'View Price Based on Country settings', 'woocommerce-product-price-based-on-countries' ) . '">' . esc_html__( 'Settings', 'woocommerce-product-price-based-on-countries' ) . '</a>',
		);

		if ( ! wcpbc_is_pro() ) {
			$action_links['get-pro'] = '<a target="_blank" rel="noopener noreferrer" style="color:#46b450;" href="https://www.pricebasedcountry.com/pricing/?utm_source=action-link&utm_medium=banner&utm_campaign=Get_Pro" aria-label="' . esc_attr__( 'Get Price Based on Country Pro', 'woocommerce-product-price-based-on-countries' ) . '">' . esc_html__( 'Get Pro', 'woocommerce-product-price-based-on-countries' ) . '</a>';
		}

		return array_merge( $action_links, $links );
	}

	/**
	 * Show row meta on the plugin screen.
	 *
	 * @since 1.7.0
	 * @param mixed $links Plugin Row Meta.
	 * @param mixed $file  Plugin Base file.
	 * @return array
	 */
	public function plugin_row_meta( $links, $file ) {
		if ( plugin_basename( WCPBC_PLUGIN_FILE ) === $file ) {
			$row_meta = array(
				'docs' => '<a href="' . esc_url( 'https://www.pricebasedcountry.com/docs/?utm_source=row-meta&utm_medium=banner&utm_campaign=Docs' ) . '" aria-label="' . esc_attr__( 'View documentation', 'woocommerce-product-price-based-on-countries' ) . '">' . esc_html__( 'Docs', 'woocommerce-product-price-based-on-countries' ) . '</a>',
			);

			return array_merge( $links, $row_meta );
		}

		return (array) $links;
	}

	/**
	 * Checks the environment for compatibility problems.
	 *
	 * @return boolean
	 */
	private function check_environment() {
		if ( ! defined( 'WC_VERSION' ) ) {
			// translators: HTML Tags.
			$this->environment_alert = sprintf( __( '%1$sPrice Based on Country%2$s requires WooCommerce to be activated to work. Learn how to install Price Based on Country in the %3$sGetting Started Guide%4$s.', 'woocommerce-product-price-based-on-countries' ), '<strong>', '</strong>', '<a href="https://www.pricebasedcountry.com/docs/getting-started/">', '</a>' );
			return false;
		}

		if ( version_compare( WC_VERSION, $this->min_wc_version, '<' ) ) {
			// translators: HTML Tags.
			$this->environment_alert = sprintf( __( 'The minimum WooCommerce version required for this plugin is %1$s. You are running %2$s.', 'woocommerce-product-price-based-on-countries' ), $this->min_wc_version, WC_VERSION );
			return false;
		}

		if ( class_exists( 'WC_Product_Price_Based_Country_Pro' ) && isset( WC_Product_Price_Based_Country_Pro::$version ) && version_compare( WC_Product_Price_Based_Country_Pro::$version, $this->min_pro_version, '<' ) ) {
			// translators: HTML Tags.
			$this->environment_alert = sprintf( __( 'You are using a not supported version of %1$sPrice Based on Country Pro%2$s. Update Price Based on Country Pro to the latest version, or the plugin will not work.', 'woocommerce-product-price-based-on-countries' ), '<strong>', '</strong>' );

			if ( class_exists( 'WCPBC_Admin' ) ) {
				// Admin init to allow users set the license.
				WCPBC_Admin::init();
			}
			return false;
		}

		return true;
	}

	/**
	 * Init plugin
	 *
	 * @since 1.8.0
	 */
	public function init_plugin() {

		if ( ! $this->check_environment() ) {
			add_action( 'admin_notices', array( $this, 'environment_notice' ) );
			return;
		}

		if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
			// Admin request.
			WCPBC_Install::init();
			WCPBC_Admin::init();
			WCPBC_Admin_Meta_Boxes::init();
			WCPBC_Admin_Notices::init();
			WCPBC_Admin_Ads::init();
		}

		WCPBC_Frontend::init();
		WCPBC_Ajax_Geolocation::init();
		WCPBC_Product_Sync::init();

		if ( $this->is_rest_api( 'wc-analytics' ) ) {
			WCPBC_Admin_Analytics::init();
		}

		if ( version_compare( WC_VERSION, '3.4', '>=' ) && version_compare( WC_VERSION, '3.9', '<' ) ) {
			WCPBC_Update_GeoIP_DB::init();
		}

		add_action( 'widgets_init', array( $this, 'register_widgets' ) );
		add_action( 'woocommerce_init', array( $this, 'frontend_init' ), 999 );
		add_action( 'init', array( $this, 'ajax_frontend_init' ), 999 );

	}

	/**
	 * Display the environment alert
	 */
	public function environment_notice() {
		echo '<div id="message" class="error">' . sprintf( '<p><strong>%1$s</strong></p>%2$s', 'Price Based on Country - ' . esc_html__( 'Heads up!', 'woocommerce-product-price-based-on-countries' ), wp_kses_post( wpautop( $this->environment_alert ) ) ) . '</div>';
	}

	/**
	 * Register Widgets
	 *
	 * @since 1.5.0
	 */
	public function register_widgets() {
		if ( class_exists( 'WC_Widget' ) ) {
			include_once $this->plugin_path() . 'includes/class-wcpbc-widget-country-selector.php';
			register_widget( 'WCPBC_Widget_Country_Selector' );
		}
	}

	/**
	 * Init front-end
	 */
	public function frontend_init() {

		if ( $this->is_rest_api_frontend() ) {
			// Products block support. Needs customer info.
			$this->initialize_customer();
		}

		if ( ! $this->is_frontend() || apply_filters( 'wc_price_based_country_stop_pricing', false ) ) {
			// Do only on frontend requests.
			return;
		}

		do_action( 'wc_price_based_country_before_frontend_init' );

		// Set the current zone.
		$this->current_zone = wcpbc_get_zone_by_country();

		// Init frontend pricing.
		WCPBC_Frontend_Pricing::init();

		do_action( 'wc_price_based_country_frontend_init' );
	}

	/**
	 * Init frontend on AJAX requests. Improve compatibility with plugins which adds the "wp_ajax_nopriv_..." action on the 'init' hook.
	 */
	public function ajax_frontend_init() {
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX && ! did_action( 'wc_price_based_country_frontend_init' ) ) {
			$this->frontend_init();
		}
	}

	/**
	 * Is a frontend request?.
	 *
	 * @return bool
	 */
	private function is_frontend() {
		return wcpbc_is_woocommerce_frontend() && ! defined( 'DOING_CRON' ) && ( ! is_admin() || $this->is_ajax_frontend() );
	}

	/**
	 * Check the referer type.
	 *
	 * @since 2.0.10
	 * @param string $type admin or frontend.
	 */
	private function is_referer_type( $type ) {
		$type         = is_array( $type ) ? $type : array( $type );
		$referer      = false;
		$http_referer = str_replace( array( 'https://', 'http://', 'www.' ), '', trailingslashit( wp_get_referer() ) );
		if ( ! empty( $http_referer ) ) {
			$admin_url = str_replace( array( 'https://', 'http://', 'www.' ), '', trailingslashit( admin_url( '/' ) ) );
			$referer   = ( false !== strpos( $http_referer, $admin_url ) ) ? 'admin' : 'frontend';
		}

		return in_array( $referer, $type, true );
	}

	/**
	 * Is AJAX frontend request?.
	 */
	private function is_ajax_frontend() {
		$is_ajax_frontend = false;
		$action           = defined( 'DOING_AJAX' ) && DOING_AJAX && isset( $_REQUEST['action'] ) ? wc_clean( wp_unslash( $_REQUEST['action'] ) ) : false; // phpcs:ignore WordPress.Security.NonceVerification
		if ( ! $action ) {
			return $is_ajax_frontend;
		}

		if ( $this->is_referer_type( false ) ) {
			// Check the ajax no priv action.
			$is_ajax_frontend = has_action( 'wp_ajax_nopriv_' . $action );
		} else {
			$is_ajax_frontend = $this->is_referer_type( 'frontend' );
		}

		return apply_filters( 'wc_price_based_country_is_ajax_frontend', $is_ajax_frontend, $action );
	}

	/**
	 * Is Rest API request?.
	 *
	 * @since 2.0.0
	 * @param string $path Path to check. Default ''.
	 * @return bool
	 */
	private function is_rest_api( $path = '' ) {
		if ( empty( $_SERVER['REQUEST_URI'] ) ) {
			return false;
		}
		$path                = empty( $path ) ? $path : trailingslashit( $path );
		$rest_prefix         = trailingslashit( rest_get_url_prefix() ) . $path;
		$is_rest_api_request = ( false !== strpos( $_SERVER['REQUEST_URI'], $rest_prefix ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		return $is_rest_api_request;
	}

	/**
	 * Is Rest API frontend request?.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	private function is_rest_api_frontend() {
		$is_rest_api_frontend = false;

		if ( $this->is_rest_api() && $this->is_referer_type( array( 'frontend', false ) ) ) {

			$frontend_rest_api_routes = array_merge(
				array( 'wc/store/' ),
				apply_filters( 'wc_price_based_country_frontend_rest_routes', array() )
			);

			foreach ( $frontend_rest_api_routes as $route ) {
				$is_rest_api_frontend = $this->is_rest_api( $route );

				if ( $is_rest_api_frontend ) {
					break;
				}
			}
		}

		return $is_rest_api_frontend;
	}

	/**
	 * Initialize the customer object.
	 */
	private function initialize_customer() {
		if ( did_action( 'before_woocommerce_init' ) && ! doing_action( 'before_woocommerce_init' ) && function_exists( 'WC' ) && is_callable( array( WC(), 'initialize_session' ) ) && ( is_null( WC()->customer ) || ! WC()->customer instanceof WC_Customer ) ) {

			if ( ! function_exists( 'wc_get_chosen_shipping_method_ids' ) ) {
				// Frontend includes.
				WC()->frontend_includes();
			}

			WC()->initialize_session();
			WC()->customer = new WC_Customer( get_current_user_id(), true );
		}
	}
}
