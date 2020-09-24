<?php
/**
 * WC_PB_Install class
 *
 * @author   SomewhereWarm <info@somewherewarm.com>
 * @package  WooCommerce Product Bundles
 * @since    5.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles installation and updating tasks.
 *
 * @class    WC_PB_Install
 * @version  5.9.0
 */
class WC_PB_Install {

	/** @var array DB updates and callbacks that need to be run per version */
	private static $db_updates = array(
		'3.0.0' => array(
			'wc_pb_update_300',
			'wc_pb_update_300_db_version'
		),
		'5.0.0' => array(
			'wc_pb_update_500_main',
			'wc_pb_update_500_delete_unused_meta',
			'wc_pb_update_500_db_version'
		),
		'5.1.0' => array(
			'wc_pb_update_510_main',
			'wc_pb_update_510_delete_unused_meta',
			'wc_pb_update_510_db_version'
		)
	);

	/**
	 * Whether install() ran in this request.
	 * @var boolean
	 */
	private static $is_install_request;

	/**
	 * Term runtime cache.
	 * @var boolean
	 */
	private static $bundle_term_exists;

	/**
	 * Background update class.
	 * @var WC_PB_Background_Updater
	 */
	private static $background_updater;

	/**
	 * Current plugin version.
	 * @var string
	 */
	private static $current_version;

	/**
	 * Current DB version.
	 * @var string
	 */
	private static $current_db_version;

	/**
	 * Hook in tabs.
	 */
	public static function init() {

		// Installation and DB updates handling.
		add_action( 'init', array( __CLASS__, 'init_background_updater' ), 5 );
		add_action( 'init', array( __CLASS__, 'define_updating_constant' ) );
		add_action( 'init', array( __CLASS__, 'maybe_install' ) );
		add_action( 'admin_init', array( __CLASS__, 'maybe_update' ) );

		// Show row meta on the plugin screen.
		add_filter( 'plugin_row_meta', array( __CLASS__, 'plugin_row_meta' ), 10, 2 );

		// Adds support for the Bundle type - added here instead of 'WC_PB_Meta_Box_Product_Data' as it's used in REST context.
		add_filter( 'product_type_selector', array( __CLASS__, 'product_selector_filter' ) );

		// Get PB plugin and DB versions.
		self::$current_version    = get_option( 'woocommerce_product_bundles_version', null );
		self::$current_db_version = get_option( 'woocommerce_product_bundles_db_version', null );

		include_once( 'class-wc-pb-background-updater.php' );
	}

	/**
	 * Add support for the 'bundle' product type.
	 *
	 * @param  array  $options
	 * @return array
	 */
	public static function product_selector_filter( $options ) {

		$options[ 'bundle' ] = __( 'Product bundle', 'woocommerce-product-bundles' );

		return $options;
	}

	/**
	 * Init background updates.
	 */
	public static function init_background_updater() {
		self::$background_updater = new WC_PB_Background_Updater();
	}

	/**
	 * Installation needed?
	 *
	 * @since  5.5.0
	 *
	 * @return boolean
	 */
	private static function must_install() {
		return version_compare( self::$current_version, WC_PB()->plugin_version(), '<' );
	}

	/**
	 * Installation possible?
	 *
	 * @since  5.5.0
	 *
	 * @return boolean
	 */
	private static function can_install() {
		return ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) && ! defined( 'IFRAME_REQUEST' ) && ! self::is_installing();
	}

	/**
	 * Check version and run the installer if necessary.
	 *
	 * @since  5.5.0
	 */
	public static function maybe_install() {
		if ( self::can_install() && self::must_install() ) {
			self::install();
		}
	}

	/**
	 * Check version and run the installer if necessary.
	 *
	 * @since  6.2.4
	 */
	private static function is_installing() {
		return 'yes' === get_transient( 'wc_pb_installing' );
	}

	/**
	 * Check version and run the installer if necessary.
	 *
	 * @since  6.2.4
	 */
	private static function is_new_install() {
		if ( is_null( self::$bundle_term_exists ) ) {
			self::$bundle_term_exists = get_term_by( 'slug', 'bundle', 'product_type' );
		}
		return ! self::$bundle_term_exists;
	}

	/**
	 * DB update needed?
	 *
	 * @since  5.5.0
	 *
	 * @return boolean
	 */
	private static function must_update() {

		$db_update_versions = array_keys( self::$db_updates );
		$db_version_target  = end( $db_update_versions );

		if ( is_null( self::$current_db_version ) ) {
			return WC_PB_DB::query_bundled_items( array( 'return' => 'count' ) ) === 0;
		} else {
			return version_compare( self::$current_db_version, $db_version_target, '<' );
		}
	}

	/**
	 * DB update possible?
	 *
	 * @since  5.5.0
	 *
	 * @return boolean
	 */
	private static function can_update() {
		return ( self::$is_install_request || self::can_install() ) && current_user_can( 'manage_woocommerce' ) && version_compare( self::$current_db_version, WC_PB()->plugin_version( true ), '<' );
	}

	/**
	 * Run the updater if triggered.
	 *
	 * @since  5.5.0
	 */
	public static function maybe_update() {

		if ( ! empty( $_GET[ 'force_wc_pb_db_update' ] ) && isset( $_GET[ '_wc_pb_admin_nonce' ] ) && wp_verify_nonce( wc_clean( $_GET[ '_wc_pb_admin_nonce' ] ), 'wc_pb_force_db_update_nonce' ) ) {

			if ( self::can_update() && self::must_update() ) {
				self::force_update();
			}

		} elseif ( ! empty( $_GET[ 'trigger_wc_pb_db_update' ] ) && isset( $_GET[ '_wc_pb_admin_nonce' ] ) && wp_verify_nonce( wc_clean( $_GET[ '_wc_pb_admin_nonce' ] ), 'wc_pb_trigger_db_update_nonce' ) ) {

			if ( self::can_update() && self::must_update() ) {
				self::trigger_update();
			}

		} else {

			// Queue upgrade tasks.
			if ( self::can_update() ) {

				if ( ! is_blog_installed() ) {
					return;
				}

				if ( self::must_update() && ! self::is_new_install() ) {

					if ( ! class_exists( 'WC_PB_Admin_Notices' ) ) {
						require_once( WC_PB_ABSPATH . 'includes/admin/class-wc-pb-admin-notices.php' );
					}

					// Add 'update' notice and save early -- saving on the 'shutdown' action will fail if a chained request arrives before the 'shutdown' hook fires.
					WC_PB_Admin_Notices::add_maintenance_notice( 'update' );
					WC_PB_Admin_Notices::save_notices();

					if ( self::auto_update_enabled() ) {
						self::update();
					} else {
						delete_transient( 'wc_pb_installing' );
						delete_option( 'wc_pb_update_init' );
					}

				// Nothing found - this is a new install :)
				} else {
					self::update_db_version();
				}
			}
		}
	}

	/**
	 * If the DB version is out-of-date, a DB update must be in progress: define a 'WC_PB_UPDATING' constant.
	 *
	 * @since  5.5.0
	 */
	public static function define_updating_constant() {
		if ( self::is_update_pending() && ! defined( 'WC_PB_TESTING' ) ) {
			wc_maybe_define_constant( 'WC_PB_UPDATING', true );
		}
	}

	/**
	 * Install PB.
	 */
	public static function install() {

		if ( ! is_blog_installed() ) {
			return;
		}

		// Running for the first time? Set a transient now. Used in 'can_install' to prevent race conditions.
		set_transient( 'wc_pb_installing', 'yes', 10 );

		// Set a flag to indicate we're installing in the current request.
		self::$is_install_request = true;

		// Create tables.
		self::create_tables();

		// if bundle type does not exist, create it.
		if ( self::is_new_install() ) {
			wp_insert_term( 'bundle', 'product_type' );
		}

		if ( ! class_exists( 'WC_PB_Admin_Notices' ) ) {
			require_once( WC_PB_ABSPATH . 'includes/admin/class-wc-pb-admin-notices.php' );
		}

		if ( is_null( self::$current_version ) ) {
			// Add dismissible welcome notice.
			WC_PB_Admin_Notices::add_maintenance_notice( 'welcome' );
		}

		// Update plugin version - once set, 'maybe_install' will not call 'install' again.
		self::update_version();
	}

	/**
	 * Set up the database tables which the plugin needs to function.
	 *
	 * Tables:
	 *     woocommerce_bundled_items - Each bundled item id is associated with a "contained" product id (the bundled product), and a "container" bundle id (the product bundle).
	 *     woocommerce_bundled_itemmeta - Bundled item meta for storing extra data.
	 */
	private static function create_tables() {
		global $wpdb;
		$wpdb->hide_errors();
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( self::get_schema() );
	}

	/**
	 * Get table schema.
	 *
	 * @return string
	 */
	private static function get_schema() {
		global $wpdb;

		$collate = '';

		if ( $wpdb->has_cap( 'collation' ) ) {
			$collate = $wpdb->get_charset_collate();
		}

		$max_index_length = 191;

		$tables = "
CREATE TABLE {$wpdb->prefix}woocommerce_bundled_items (
  bundled_item_id BIGINT UNSIGNED NOT NULL auto_increment,
  product_id BIGINT UNSIGNED NOT NULL,
  bundle_id BIGINT UNSIGNED NOT NULL,
  menu_order BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY  (bundled_item_id),
  KEY product_id (product_id),
  KEY bundle_id (bundle_id)
) $collate;
CREATE TABLE {$wpdb->prefix}woocommerce_bundled_itemmeta (
  meta_id BIGINT UNSIGNED NOT NULL auto_increment,
  bundled_item_id BIGINT UNSIGNED NOT NULL,
  meta_key varchar(255) default NULL,
  meta_value longtext NULL,
  PRIMARY KEY  (meta_id),
  KEY bundled_item_id (bundled_item_id),
  KEY meta_key (meta_key($max_index_length))
) $collate;
		";

		return $tables;
	}

	/**
	 * Update WC PB version to current.
	 */
	private static function update_version() {
		delete_option( 'woocommerce_product_bundles_version' );
		add_option( 'woocommerce_product_bundles_version', WC_PB()->plugin_version() );
	}

	/**
	 * Push all needed DB updates to the queue for processing.
	 */
	private static function update() {

		if ( ! is_object( self::$background_updater ) ) {
			self::init_background_updater();
		}

		$update_queued = false;

		foreach ( self::$db_updates as $version => $update_callbacks ) {

			if ( version_compare( self::$current_db_version, $version, '<' ) ) {

				$update_queued = true;
				WC_PB_Core_Compatibility::log( sprintf( 'Updating to version %s.', $version ), 'info', 'wc_pb_db_updates' );

				foreach ( $update_callbacks as $update_callback ) {
					WC_PB_Core_Compatibility::log( sprintf( '- Queuing %s callback.', $update_callback ), 'info', 'wc_pb_db_updates' );
					self::$background_updater->push_to_queue( $update_callback );
				}
			}
		}

		if ( $update_queued ) {

			// Define 'WC_PB_UPDATING' constant.
			wc_maybe_define_constant( 'WC_PB_UPDATING', true );

			// Keep track of time.
			delete_option( 'wc_pb_update_init' );
			add_option( 'wc_pb_update_init', gmdate( 'U' ) );

			// Dispatch.
			self::$background_updater->save()->dispatch();
		}
	}

	/**
	 * Is auto-updating enabled?
	 *
	 * @since  5.5.0
	 *
	 * @return boolean
	 */
	public static function auto_update_enabled() {
		return apply_filters( 'woocommerce_bundles_auto_update_db', true );
	}

	/**
	 * Trigger DB update.
	 *
	 * @since  5.5.0
	 */
	public static function trigger_update() {
		self::update();
		wp_safe_redirect( admin_url() );
	}

	/**
	 * Force re-start the update cron if everything else fails.
	 */
	public static function force_update() {

		if ( ! is_object( self::$background_updater ) ) {
			self::init_background_updater();
		}

		/**
		 * Updater cron action.
		 */
		do_action( self::$background_updater->get_cron_hook_identifier() );
		wp_safe_redirect( admin_url() );
	}

	/**
	 * Updates plugin DB version when all updates have been processed.
	 */
	public static function update_complete() {

		WC_PB_Core_Compatibility::log( 'Data update complete.', 'info', 'wc_pb_db_updates' );
		self::update_db_version();
		delete_option( 'wc_pb_update_init' );
		wp_cache_flush();
	}

	/**
	 * True if a DB update is pending.
	 *
	 * @return boolean
	 */
	public static function is_update_pending() {
		return self::must_update();
	}

	/**
	 * True if a DB update was started but not completed.
	 *
	 * @since  5.5.0
	 *
	 * @return boolean
	 */
	public static function is_update_incomplete() {
		return false !== get_option( 'wc_pb_update_init', false );
	}


	/**
	 * True if a DB update is in progress.
	 *
	 * @since  5.5.0
	 *
	 * @return boolean
	 */
	public static function is_update_queued() {
		return self::$background_updater->is_update_queued();
	}

	/**
	 * True if an update process is running.
	 *
	 * @return boolean
	 */
	public static function is_update_process_running() {
		return self::is_update_cli_process_running() || self::is_update_background_process_running();
	}

	/**
	 * True if an update background process is running.
	 *
	 * @since  5.5.0
	 *
	 * @return boolean
	 */
	public static function is_update_background_process_running() {
		return self::$background_updater->is_process_running();
	}

	/**
	 * True if a CLI update is running.
	 *
	 * @since  5.5.0
	 *
	 * @return boolean
	 */
	public static function is_update_cli_process_running() {
		return false !== get_transient( 'wc_pb_update_cli_init', false );
	}

	/**
	 * Update DB version to current.
	 *
	 * @param  string  $version
	 */
	public static function update_db_version( $version = null ) {

		$version = is_null( $version ) ? WC_PB()->plugin_version() : $version;

		// Remove suffixes.
		$version = WC_PB()->plugin_version( true, $version );

		delete_option( 'woocommerce_product_bundles_db_version' );
		add_option( 'woocommerce_product_bundles_db_version', $version );

		WC_PB_Core_Compatibility::log( sprintf( 'Database version is %s.', get_option( 'woocommerce_product_bundles_db_version', 'unknown' ) ), 'info', 'wc_pb_db_updates' );
	}

	/**
	 * Get list of DB update callbacks.
	 *
	 * @since  5.5.0
	 *
	 * @return array
	 */
	public static function get_db_update_callbacks() {
		return self::$db_updates;
	}

	/**
	 * Show row meta on the plugin screen.
	 *
	 * @param	mixed  $links
	 * @param	mixed  $file
	 * @return	array
	 */
	public static function plugin_row_meta( $links, $file ) {

		if ( $file == WC_PB()->plugin_basename() ) {
			$row_meta = array(
				'docs'    => '<a href="https://docs.woocommerce.com/document/bundles/">' . __( 'Documentation', 'woocommerce-product-bundles' ) . '</a>',
				'support' => '<a href="' . esc_url( WC_PB_SUPPORT_URL ) . '">' . __( 'Support', 'woocommerce-product-bundles' ) . '</a>',
			);

			return array_merge( $links, $row_meta );
		}

		return $links;
	}
}

WC_PB_Install::init();
