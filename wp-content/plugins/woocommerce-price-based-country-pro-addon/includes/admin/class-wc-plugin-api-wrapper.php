<?php
/**
 * WooCommerce Plugin API Wrapper Class.
 *
 * Handle WooCommerce Plugin API calls.
 *
 * @author oscargare
 * @version 1.1.2
 * @package WCPBC/Updater
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WC_Plugin_API_Wrapper' ) ) :

	/**
	 * WC_Plugin_API_Wrapper Class
	 */
	class WC_Plugin_API_Wrapper {

		/**
		 * API url.
		 *
		 * @var string
		 */
		private static $api_url = 'https://www.pricebasedcountry.com/wc-api/plugin-api/';

		/**
		 * Do a request to API.
		 *
		 * @param string $action API request action.
		 * @param array  $params Request parameters.
		 * @param bool   $assoc  When TRUE, returned objects will be converted into associative arrays.
		 * @return array
		 */
		private static function api_request( $action, $params, $assoc = false ) {
			global $wp_version;

			$data     = false;
			$url      = add_query_arg( 'request', $action, self::$api_url );
			$response = wp_remote_post(
				$url,
				array(
					'body'        => $params,
					'redirection' => 5,
					'timeout'     => 15,
					'httpversion' => '1.0',
					'blocking'    => true,
					'cookies'     => array(),
					'sslverify'   => false,
					'user-agent'  => 'WordPress/' . get_bloginfo( 'version' ) . '; ' . get_bloginfo( 'url' ),
				)
			);
			$logger   = new WC_Logger();

			if ( is_wp_error( $response ) ) {
				$logger->add( 'wc_price_based_country', 'Error on API request: .' . $response->get_error_message() );
			} else {

				if ( 200 === absint( $response['response']['code'] ) ) {
					// Success.
					$data = json_decode( $response['body'], $assoc );

				} else {
					// Error.
					$error = json_decode( $response['body'], true );

					if ( ! empty( $error['errors'] ) ) {
						// Error defined by the API.
						$data = new WP_Error();

						foreach ( $error['errors'] as $code => $message ) {
							$error_data = isset( $error['error_data'][ $code ] ) ? $error['error_data'][ $code ] : array();
							$data->add( 'api-error', $message[0], $error_data );
						}
					} else {
						// Unknow error.
						$logger->add( 'wc_price_based_country', 'Error on API request: ' . $response['response']['code'] . ' - ' . wp_strip_all_tags( $response['body'] ) );
					}
				}
			}

			return $data;
		}

		/**
		 * Activate a license key
		 *
		 * @param sting $license_key License key.
		 * @return array
		 */
		public static function activate_license( $license_key ) {
			return self::api_request(
				'activate',
				array(
					'license'  => $license_key,
					'home_url' => get_bloginfo( 'url' ),
				)
			);
		}

		/**
		 * Deactivate a license key
		 *
		 * @param sting $license_key License key.
		 * @param sting $api_key API Key.
		 * @return array
		 */
		public static function deactivate_license( $license_key, $api_key ) {
			return self::api_request(
				'deactivate',
				array(
					'license' => $license_key,
					'api-key' => $api_key,
				)
			);
		}

		/**
		 * Check status of activation
		 *
		 * @param sting $license_key License key.
		 * @param sting $api_key API Key.
		 * @return array
		 */
		public static function status_check( $license_key, $api_key ) {
			return self::api_request(
				'status_check',
				array(
					'license' => $license_key,
					'api-key' => $api_key,
				)
			);
		}

		/**
		 * Update check
		 *
		 * @param int   $plugin_id Plugin ID.
		 * @param sting $api_key API Key.
		 * @param sting $license_key License key.
		 * @param sting $version The current plugin version.
		 * @return array
		 * @access public
		 */
		public static function update_check( $plugin_id, $api_key, $license_key, $version ) {
			return self::api_request(
				'update_check',
				array(
					'id'      => $plugin_id,
					'api-key' => $api_key,
					'license' => $license_key,
					'version' => $version,
				)
			);
		}

		/**
		 * Plugin information
		 *
		 * @param int   $plugin_id Plugin ID.
		 * @param sting $api_key API Key.
		 * @param sting $license_key License key.
		 * @return array
		 */
		public static function plugin_information( $plugin_id, $api_key, $license_key ) {
			$response = self::api_request(
				'plugin_information',
				array(
					'id'      => $plugin_id,
					'api-key' => $api_key,
					'license' => $license_key,
				),
				true
			);

			if ( ! is_wp_error( $response ) ) {
				$data = new StdClass();
				foreach ( $response as $key => $value ) {
					$data->$key = $value;
				}
				$response = $data;
			}
			return $response;
		}
	}

endif;
