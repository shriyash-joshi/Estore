<?php
/**
 * Display notices in admin
 *
 * @since   1.7.0
 * @version 1.8.10
 * @package WCPBC
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WCPBC_Admin_Notices', false ) ) :

	/**
	 * WCPBC_Admin_Notices Class
	 */
	class WCPBC_Admin_Notices {

		/**
		 * Notices
		 *
		 * @var array
		 */
		private static $notices = false;

		/**
		 * Flag notices change.
		 *
		 * @var bool
		 */
		private static $changed = false;

		/**
		 * Init notices
		 */
		public static function init() {
			add_action( 'admin_init', array( __CLASS__, 'hide_notice' ) );
			add_action( 'admin_head', array( __CLASS__, 'enqueue_notices' ) );
			add_action( 'wp_ajax_wcpbc_hide_notice', array( __CLASS__, 'ajax_hide_notice' ) );
			add_action( 'shutdown', array( __CLASS__, 'save' ) );

			// Product type not supported.
			add_action( 'woocommerce_product_options_general_product_data', array( __CLASS__, 'display_product_type_not_supported' ), 9 );
		}

		/**
		 * Init notices array
		 */
		private static function init_notices() {
			if ( ! self::$notices ) {

				self::$notices = array();
				self::$changed = false;

				$notices = apply_filters(
					'wc_price_based_country_admin_notices',
					array(
						'welcome'                    => array( 'hide' => 'no' ),
						'tracking'                   => array( 'hide' => 'no' ),
						'updated'                    => array( 'hide' => 'yes' ),
						'geolocation'                => array(
							'hide'    => 'no',
							'screens' => array( 'woocommerce_page_wc-settings' ),
						),
						'geolocation_ajax'           => array(
							'hide'    => 'no',
							'screens' => array( 'woocommerce_page_wc-settings' ),
						),
						'maxmind_geoip_database'     => array( 'hide' => 'no' ),
						'incompatible_multicurrency' => array( 'hide' => 'no' ),
						'updated_geoip'              => array( 'hide' => 'no' ),
						'reports'                    => array(
							'hide'    => 'no',
							'screens' => array( 'woocommerce_page_wc-reports', 'toplevel_page_wc-reports' ),
						),
						'request_review'             => array(
							'hide'     => 'yes',
							'interval' => '+21 days',
						),
					)
				);

				$store_notices = get_user_meta( get_current_user_id(), 'wc_price_based_country_admin_notices', true );
				if ( ! is_array( $store_notices ) ) {
					$store_notices = array();
				}

				foreach ( $notices as $key => $notice ) {
					self::$notices[ $key ]               = $notice;
					self::$notices[ $key ]['hide']       = isset( $store_notices[ $key ]['hide'] ) ? $store_notices[ $key ]['hide'] : self::$notices[ $key ]['hide'];
					self::$notices[ $key ]['display_at'] = isset( $store_notices[ $key ]['display_at'] ) ? $store_notices[ $key ]['display_at'] : '';
				}

				// Add the product type not supported notices store in the user meta.
				foreach ( $store_notices as $key => $notice ) {
					if ( empty( self::$notices[ $key ] ) && 'product_type_' === substr( $key, 0, 13 ) ) {
						self::$notices[ $key ] = $notice;
					}
				}
			}
		}

		/**
		 * Add notices to admin_notices hook
		 */
		public static function enqueue_notices() {
			if ( ! current_user_can( 'manage_woocommerce' ) ) {
				return;
			}

			$notices_screens = array_merge( wc_get_screen_ids(), array( 'plugins', 'dashboard' ) );
			$screen          = get_current_screen();
			$screen_id       = $screen ? $screen->id : '';
			if ( ! in_array( $screen_id, $notices_screens, true ) ) {
				return;
			}

			// Init notices array.
			self::init_notices();

			foreach ( self::$notices as $key => $notice ) {

				if ( 'yes' === $notice['hide'] && ! isset( $notice['display_at'] ) && ! empty( $notice['interval'] ) ) {
					self::add_notice( $key, true );
				}

				if ( ! empty( $notice['display_at'] ) && time() > $notice['display_at'] ) {
					$notice['hide'] = 'no';
				}

				if ( 'no' === $notice['hide'] && ( empty( $notice['screens'] ) || in_array( $screen_id, $notice['screens'], true ) ) ) {
					$callback = empty( $notice['callback'] ) ? array( __CLASS__, 'display_' . $key . '_notice' ) : $notice['callback'];
					if ( is_callable( $callback ) ) {
						if ( 'woocommerce_page_wc-settings' === $screen_id ) {
							$current_tab = empty( $_GET['tab'] ) ? 'general' : sanitize_title( wp_unslash( $_GET['tab'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
							add_action( 'woocommerce_settings_' . $current_tab, $callback, 0 );
						} else {
							add_action( 'admin_notices', $callback );
						}
					}
				}
			}
		}

		/**
		 * Add a notice to display
		 *
		 * @param string  $notice Notice ID.
		 * @param boolean $delay Delay the notice display?.
		 */
		public static function add_notice( $notice, $delay = false ) {
			if ( ! $notice ) {
				return;
			}

			// Init notices array.
			self::init_notices();

			if ( ! empty( self::$notices[ $notice ] ) ) {
				if ( empty( $delay ) ) {
					self::$notices[ $notice ]['hide'] = 'no';
				} elseif ( ! empty( self::$notices[ $notice ]['interval'] ) ) {
					self::$notices[ $notice ]['hide']       = 'yes';
					self::$notices[ $notice ]['display_at'] = strtotime( self::$notices[ $notice ]['interval'] );
				}

				self::$changed = true;
			}
		}

		/**
		 * Add a custom notice
		 *
		 * @since 1.7.7
		 * @param string $message Notice text.
		 * @param string $type info|warning|error.
		 */
		public static function add_temp_notice( $message, $type = 'info' ) {
			// Init notices array.
			self::init_notices();

			// Add the notice.
			if ( empty( self::$notices['temp'] ) ) {
				self::$notices['temp'] = array(
					'hide'    => 'no',
					'notices' => array(),
				);
			}

			self::$notices['temp']['notices'][] = array(
				'message' => $message,
				'type'    => $type,
			);
		}

		/**
		 * Remove a notice
		 *
		 * @param string $notice Notice ID.
		 */
		public static function remove_notice( $notice ) {
			// Init notices array.
			self::init_notices();

			// Remove notices.
			self::$notices[ $notice ]['hide']       = 'yes';
			self::$notices[ $notice ]['interval']   = '';
			self::$notices[ $notice ]['display_at'] = '';

			self::$changed = true;
		}

		/**
		 * Save notices in shutdown.
		 */
		public static function save() {
			if ( self::$changed ) {
				if ( isset( self::$notices['temp'] ) ) {
					unset( self::$notices['temp'] );
				}

				update_user_meta( get_current_user_id(), 'wc_price_based_country_admin_notices', self::$notices );
			}
		}

		/**
		 * Hide a notice via ajax.
		 */
		public static function ajax_hide_notice() {

			check_ajax_referer( 'pbc-hide-notice', 'security' );

			if ( isset( $_POST['notice'] ) ) {

				if ( ! current_user_can( 'manage_woocommerce' ) ) {
					wp_die( esc_html__( 'Cheatin&#8217; huh?', 'woocommerce-product-price-based-on-countries' ) );
				}

				$notice = sanitize_text_field( wp_unslash( $_POST['notice'] ) );

				if ( ! empty( $_POST['remind'] ) && 'yes' === $_POST['remind'] ) {
					self::add_notice( $notice, true );
				} else {
					self::remove_notice( $notice );
				}
			}

			wp_die();
		}


		/**
		 * Hide a notice.
		 */
		public static function hide_notice() {
			if ( isset( $_GET['pbc-hide-notice'] ) && isset( $_GET['_wpnonce'] ) ) { // WPCS: CSRF ok.

				if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_wpnonce'] ) ), 'pbc_hide_notice_nonce' ) ) { // WPCS: CSRF ok.
					wp_die( esc_html__( 'Action failed. Please refresh the page and retry.', 'woocommerce-product-price-based-on-countries' ) );
				}
				if ( ! current_user_can( 'manage_woocommerce' ) ) {
					wp_die( esc_html__( 'You don&#8217;t have permission to do this.', 'woocommerce-product-price-based-on-countries' ) );
				}

				$hide_notice = wc_clean( wp_unslash( $_GET['pbc-hide-notice'] ) ); // WPCS: CSRF ok.

				if ( ! empty( $_GET['remind'] ) && 'yes' === $_GET['remind'] && ! empty( self::$notices[ $hide_notice ]['interval'] ) ) {
					// Remind later.
					self::add_notice( $hide_notice, true );
				} else {
					// Hide notice.
					self::remove_notice( $hide_notice );
				}

				if ( ! empty( $_GET['wcpbc_tracker_optin'] ) && 'tracking' === $hide_notice && false === get_option( 'wc_price_based_country_allow_tracking', false ) ) {
					// Tracking notice.
					$_tracker_optin = ( 'yes' === $_GET['wcpbc_tracker_optin'] ) ? 'yes' : 'no';

					update_option( 'wc_price_based_country_allow_tracking', $_tracker_optin );

					if ( 'yes' === $_tracker_optin ) {
						include_once WCPBC()->plugin_path() . 'includes/class-wcpbc-tracker.php';
						WCPBC_Tracker::send_tracking_data();
					}
				}
			}
		}

		/**
		 * Display temporaly notices
		 *
		 * @since 1.7.8
		 */
		public static function display_temp_notice() {

			if ( empty( self::$notices['temp']['notices'] ) ) {
				return;
			}
			foreach ( self::$notices['temp']['notices'] as $notice ) {
				$callback = 'display_' . wc_clean( $notice['message'] ) . '_notice';
				if ( is_callable( array( __CLASS__, $callback ) ) ) {
					self::{$callback}();
				} else {
					$type    = $notice['type'];
					$message = $notice['message'];
					include_once dirname( __FILE__ ) . '/views/html-notice-default.php';
				}
			}
		}

		/**
		 * If we have just installed, show a welcome message
		 */
		public static function display_welcome_notice() {
			include_once dirname( __FILE__ ) . '/views/html-notice-welcome.php';
		}

		/**
		 * Update db admin notice
		 */
		public static function display_update_db_notice() {
			include_once dirname( __FILE__ ) . '/views/html-notice-update-db.php';
		}

		/**
		 * Updated admin notice
		 */
		public static function display_updated_notice() {
			include_once dirname( __FILE__ ) . '/views/html-notice-updated.php';
		}

		/**
		 * Request review notice
		 */
		public static function display_request_review_notice() {
			include_once dirname( __FILE__ ) . '/views/html-notice-request-review.php';
		}

		/**
		 * Display geolocation notice.
		 */
		public static function display_geolocation_notice() {
			if ( ! in_array( get_option( 'woocommerce_default_customer_address' ), array( 'geolocation', 'geolocation_ajax' ), true ) ) {
				include_once dirname( __FILE__ ) . '/views/html-notice-geolocation.php';
			}
		}

		/**
		 * Display geolocation ajax notice.
		 */
		public static function display_geolocation_ajax_notice() {
			if ( 'geolocation' !== get_option( 'woocommerce_default_customer_address' ) && 'yes' === get_option( 'wc_price_based_country_caching_support', 'no' ) ) {
				include_once dirname( __FILE__ ) . '/views/html-notice-geolocation-ajax.php';
			}
		}

		/**
		 * Update db admin notice
		 */
		public static function display_maxmind_geoip_database_notice() {
			if ( version_compare( WC_VERSION, '3.9', '<' ) && false !== get_option( 'wc_price_based_country_dbip_prefix', false ) && ! wcpbc_geoipdb_exists() && false === get_transient( 'wcpbc_updated_geoip' ) ) {
				include_once dirname( __FILE__ ) . '/views/html-notice-maxmind-geoip-database.php';
			}
		}

		/**
		 * Incompatible Multicurrency notice
		 */
		public static function display_incompatible_multicurrency_notice() {
			global $woocommerce_wpml;

			// translators: 1,2: bold HTML tag, 3: Plugin name.
			$message = __( 'It looks like another multicurrency plugin is active on your site. %1$sPrice Based on Country will not work properly%2$s. We recomended you disable %3$s.', 'woocommerce-product-price-based-on-countries' );

			$plugins = array(
				'Alg_WC_Currency_Switcher' => 'Currency Switcher for WooCommerce by Algoritmika Ltd',
				'WOOCS_STARTER'            => 'Currency Switcher for WooCommerce by realmag777',
				'WOOCS'                    => 'Currency Switcher for WooCommerce by realmag777',
				'WOOMULTI_CURRENCY_F'      => 'Woo Multi Currency by VillaTheme',
				'FMA_Multi_Currency'       => 'FMA Woo Multi Currency by FME Addons',
				'BeRocket_CE'              => 'Currency Exchange for WooCommerce by BeRocket',
			);

			foreach ( $plugins as $class_name => $plugin_name ) {
				if ( class_exists( $class_name ) ) {
					$message_text = sprintf( $message, '<span style="color:#a00;">', '</span>', '<strong>' . $plugin_name . '</strong>' );
					include_once dirname( __FILE__ ) . '/views/html-notice-incompatible-multicurrency.php';
				}
			}

			// woo-exchange-rate plugin.
			if ( defined( 'WOOER_PLUGIN_URL' ) ) {
				$message_text = sprintf( $message, '<span style="color:#a00;">', '</span>', '<strong>Woo Exchange Rate by Pavel Kolomeitsev</strong>' );
				include_once dirname( __FILE__ ) . '/views/html-notice-incompatible-multicurrency.php';
			}

			// WPML multi-currency enable.
			if ( ! empty( $woocommerce_wpml->settings['enable_multi_currency'] ) ) {
				$message_text = sprintf( $message, '<span style="color:#a00;">', '</span>', '<a href="' . admin_url( 'admin.php?page=wpml-wcml&tab=multi-currency' ) . '">' . __( 'WooCommerce Multilingual multiple currencies option', 'woocommerce-product-price-based-on-countries' ) . '</a>' );
				include_once dirname( __FILE__ ) . '/views/html-notice-incompatible-multicurrency.php';
			}
		}

		/**
		 * Tracking notice
		 */
		public static function display_tracking_notice() {
			if ( get_option( 'wc_price_based_country_allow_tracking', false ) === false ) {
				include_once dirname( __FILE__ ) . '/views/html-notice-tracking.php';
			}
		}

		/**
		 * GeoIP database update result
		 */
		public static function display_updated_geoip_notice() {
			$updated_geoip = get_transient( 'wcpbc_updated_geoip' );
			if ( false !== $updated_geoip ) {
				delete_transient( 'wcpbc_updated_geoip' );

				if ( 'yes' === $updated_geoip ) {
					$message = 'GeoIP database updated';
					$type    = 'success';
					include dirname( __FILE__ ) . '/views/html-notice-default.php';
				} else {
					include dirname( __FILE__ ) . '/views/html-notice-unable-install-geoip.php';
				}
			}
		}

		/**
		 * WooCommerce Reports notice.
		 */
		public static function display_reports_notice() {

			if ( ! isset( $_GET['tab'] ) || in_array( wp_unslash( $_GET['tab'] ), apply_filters( 'wc_price_based_country_tabs_report_notice', array( 'orders' ) ), true ) ) { // WPCS: CSRF ok.

				$base_currency = wcpbc_get_base_currency();
				$rates         = WCPBC_Pricing_Zones::get_currency_rates();
				$rates_string  = array();

				foreach ( $rates as $currency => $rate ) {
					$rates_string[] = "{$base_currency}/{$currency} {$rate}";
				}

				if ( ! empty( $rates_string ) ) {
					$rates_string = '<strong>' . implode( '</strong> - <strong>', $rates_string ) . '</strong>';

					echo '<div class="notice notice-info"><p>';
					// Translators: Shop base currency.
					echo esc_html( sprintf( __( 'Totals in different currency to %s has been calculate by following exchange rates:', 'woocommerce-product-price-based-on-countries' ), $base_currency ) ) . ' ' . wp_kses_post( $rates_string );
					echo '</p></div>';
				}
			}
		}

		/**
		 * Output product type not supported notice.
		 */
		public static function display_product_type_not_supported() {
			$supported_product_types   = array_keys( wcpbc_product_types_supported() );
			$third_party_product_types = wcpbc_product_types_supported( 'third-party' );

			foreach ( wc_get_product_types() as $value => $label ) {
				if ( ! in_array( $value, $supported_product_types, true ) ) {
					$notice = 'product_type_' . $value . '_not_supported';
					$class  = 'not-supported';
					if ( empty( self::$notices[ $notice ]['hide'] ) || 'yes' !== self::$notices[ $notice ]['hide'] ) {
						include dirname( __FILE__ ) . '/views/html-notice-product-type-not-supported.php';
					}
				} elseif ( isset( $third_party_product_types[ $value ] ) ) {
					$notice = 'product_type_' . $value . '_third_party';
					$class  = 'third-party';
					if ( empty( self::$notices[ $notice ]['hide'] ) || 'yes' !== self::$notices[ $notice ]['hide'] ) {
						include dirname( __FILE__ ) . '/views/html-notice-product-type-not-supported.php';
					}
				}
			}
		}
	}

endif;
