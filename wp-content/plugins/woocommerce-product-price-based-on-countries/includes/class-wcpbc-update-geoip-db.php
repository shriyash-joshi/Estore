<?php
/**
 * Updating the geolocation database for WooCommerce minor 3.9
 *
 * This product includes free IP to Country Lite database by DB-IP, available from https://db-ip.com.
 *
 * @package WCPBC
 * @version 1.8.21
 */

defined( 'ABSPATH' ) || exit;

/**
 * WCPBC_Update_GeoIP_DB Class
 */
class WCPBC_Update_GeoIP_DB {

	/**
	 * Hook in geolocation functionality.
	 */
	public static function init() {
		if ( ! self::supports_geolite2() || false === get_option( 'wc_price_based_country_dbip_prefix', false ) ) {
			return;
		}

		add_filter( 'woocommerce_geolocation_local_database_path', array( __CLASS__, 'get_local_database_path' ) );
		add_filter( 'pre_update_option_woocommerce_default_customer_address', array( __CLASS__, 'maybe_update_database' ), 10, 2 );
		add_action( 'woocommerce_geoip_updater', array( __CLASS__, 'update_database' ) );

		remove_filter( 'pre_update_option_woocommerce_default_customer_address', array( 'WC_Geolocation', 'maybe_update_database' ), 10, 2 );
		remove_action( 'woocommerce_geoip_updater', array( 'WC_Geolocation', 'update_database' ) );
	}

	/**
	 * Check if server supports MaxMind GeoLite2 Reader.
	 *
	 * @since 3.4.0
	 * @return bool
	 */
	private static function supports_geolite2() {
		return version_compare( PHP_VERSION, '5.4.0', '>=' );
	}

	/**
	 * Checks if the GeoIP Database is needed.
	 *
	 * @param string $default_customer_address Default customer address value. Default woocommerce_default_customer_address option value.
	 */
	private static function database_required( $default_customer_address = false ) {
		$default_customer_address = false === $default_customer_address ? get_option( 'woocommerce_default_customer_address' ) : $default_customer_address;
		return in_array( $default_customer_address, array( 'geolocation', 'geolocation_ajax' ), true ) && empty( $_SERVER['HTTP_CF_IPCOUNTRY'] ) && empty( $_SERVER['GEOIP_COUNTRY_CODE'] ) && empty( $_SERVER['HTTP_X_COUNTRY_CODE'] );
	}

	/**
	 * Returns the database url to download it.
	 */
	private static function get_database_url() {
		$database_url = false;
		$response     = wp_remote_get(
			'https://db-ip.com/db/download/ip-to-country-lite',
			array(
				'redirection' => 5,
				'timeout'     => 15,
				'sslverify'   => false,
			)
		);
		$html         = wp_remote_retrieve_body( $response );
		$matches      = array();
		preg_match_all( '/https:\/\/download.db-ip.com\/free\/dbip-country-lite-.*mmdb.gz/', $html, $matches );
		if ( count( $matches ) && count( $matches[0] ) ) {
			$database_url = $matches[0][0];
		}
		return $database_url;
	}

	/**
	 * Maybe trigger a DB update for the first time.
	 *
	 * @param  string $new_value New value.
	 * @param  string $old_value Old value.
	 * @return string
	 */
	public static function maybe_update_database( $new_value, $old_value ) {
		if ( $new_value !== $old_value && self::database_required( $new_value ) ) {
			self::update_database( true );
		}

		return $new_value;
	}

	/**
	 * Path to our local db.
	 *
	 * @param string $path Filter callback parameter.
	 * @return string
	 */
	public static function get_local_database_path( $path = '' ) {
		$uploads_dir     = wp_upload_dir();
		$database_path   = trailingslashit( $uploads_dir['basedir'] ) . 'woocommerce_uploads/';
		$database_prefix = get_option( 'wc_price_based_country_dbip_prefix' );
		if ( ! empty( $database_prefix ) ) {
			$database_path .= $database_prefix . '-';
		}
		$database_path .= 'dbip-country-lite.mmdb';

		return $database_path;
	}

	/**
	 * Update geoip database.
	 *
	 * @param bool $force Forces the database updated.
	 */
	public static function update_database( $force = false ) {
		$logger = wc_get_logger();

		if ( ! self::supports_geolite2() ) {
			$logger->notice( 'Requires PHP 5.4 to be able to download DB-IP country lite database', array( 'source' => 'geolocation' ) );
			return;
		}

		if ( ! self::database_required() && ! $force ) {
			return;
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';

		$target_path = self::get_local_database_path();
		$tmp_file    = download_url( self::get_database_url() );

		if ( ! is_wp_error( $tmp_file ) ) {
			WP_Filesystem();

			global $wp_filesystem;

			try {
				// Make sure target dir exists.
				$wp_filesystem->mkdir( dirname( $target_path ) );

				$out_filename = trailingslashit( dirname( $tmp_file ) ) . basename( $target_path, 'mmdb' );
				$wp_filesystem->delete( $out_filename );

				// Extract files.
				$file     = gzopen( $tmp_file, 'rb' );
				$out_file = fopen( $out_filename, 'wb' );
				while ( ! gzeof( $file ) ) {
					fwrite( $out_file, gzread( $file, 4096 ) );
				}
				fclose( $out_file );
				gzclose( $file );

				// Move file and delete temp.
				$wp_filesystem->move( $out_filename, $target_path, true );
				$wp_filesystem->delete( $out_filename );

			} catch ( Exception $e ) {
				$logger->notice( $e->getMessage(), array( 'source' => 'wcpbc-geolocation' ) );

				// Reschedule download of DB.
				wp_clear_scheduled_hook( 'woocommerce_geoip_updater' );
				wp_schedule_event( strtotime( 'first tuesday of next month' ), 'monthly', 'woocommerce_geoip_updater' );

				// Close files?
				@fclose( $out_file );
				@gzclose( $file );
			}

			// Delete temp file regardless of success.
			$wp_filesystem->delete( $tmp_file );

		} else {
			$logger->notice(
				'Unable to download GeoIP Database: ' . $tmp_file->get_error_message(),
				array( 'source' => 'wcpbc-geolocation' )
			);
		}
	}

	/**
	 * Install the DB-IP database.
	 */
	public static function install() {
		$dbip_prefix = get_option( 'wc_price_based_country_dbip_prefix', false );
		if ( false === $dbip_prefix ) {
			// Set the db prefix.
			$dbip_prefix = wp_generate_password( 32, false );
			update_option( 'wc_price_based_country_dbip_prefix', $dbip_prefix );
		}

		// Update the database.
		self::update_database();

		// Reschedule download of DB.
		wp_clear_scheduled_hook( 'woocommerce_geoip_updater' );
		wp_schedule_event( strtotime( 'first tuesday of next month' ), 'monthly', 'woocommerce_geoip_updater' );

		self::init();
	}
}
