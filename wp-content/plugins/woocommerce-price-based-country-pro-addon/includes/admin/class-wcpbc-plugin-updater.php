<?php
/**
 * Allows plugins to use their own update API.
 *
 * @author oscargare
 * @version 2.4.12
 * @package WCPBC/Updater
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WCPBC_Plugin_Updater' ) ) :

	/**
	 * WCPBC_Plugin_Updater Class
	 */
	class WCPBC_Plugin_Updater {

		/**
		 * Plugin name.
		 *
		 * @var string
		 */
		protected $name;

		/**
		 * Plugin slug.
		 *
		 * @var string
		 */
		protected $slug;

		/**
		 * Plugin version.
		 *
		 * @var string
		 */
		protected $version;

		/**
		 * API data.
		 *
		 * @var array
		 */
		protected $api_data;

		/**
		 * Cache key.
		 *
		 * @var string
		 */
		protected $cache_key;

		/**
		 * Callabe on error action.
		 *
		 * @var string
		 */
		protected $on_error;

		/**
		 * Class constructor.
		 *
		 * @param string   $_plugin_file Path to the plugin file.
		 * @param string   $_version     Plugin version.
		 * @param array    $_api_data    Optional data to send with API calls.
		 * @param callable $_on_error    Optional callable function when check update api call return a error.
		 */
		public function __construct( $_plugin_file, $_version, $_api_data = array(), $_on_error = false ) {

			$this->name     = plugin_basename( $_plugin_file );
			$this->slug     = basename( $_plugin_file, '.php' );
			$this->version  = $_version;
			$this->api_data = wp_parse_args(
				$_api_data,
				array(
					'item_id'     => 0,
					'license_key' => '',
					'api_key'     => '',
				)
			);
			$this->on_error = $_on_error;

			$this->cache_key = 'wcpbc_update_check_' . md5( $this->slug . wp_json_encode( $this->api_data ) );

			// Set up hooks.
			$this->init();
		}

		/**
		 * Set up WordPress filters to hook into WP's update process.
		 *
		 * @return void
		 */
		public function init() {
			add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_update' ) );
			add_filter( 'plugins_api', array( $this, 'plugin_api_call' ), 10, 3 );
		}

		/**
		 * Check for Updates at the defined API endpoint and modify the update array.
		 *
		 * This function dives into the update API just when WordPress creates its update array,
		 * then adds a custom API call and injects the custom plugin data retrieved from the API.
		 * It is reassembled from parts of the native WordPress plugin update code.
		 * See wp-includes/update.php line 121 for the original wp_update_plugins() function.
		 *
		 * @uses WC_Plugin_API_Wrapper::update_check()
		 *
		 * @param array $_transient_data Update array build by WordPress.
		 * @return array Modified update array with custom plugin data.
		 */
		public function check_update( $_transient_data ) {

			if ( ! is_object( $_transient_data ) ) {
				$_transient_data = new stdClass();
			}

			if ( ! empty( $_transient_data->response ) && ! empty( $_transient_data->response[ $this->name ] ) ) {
				return $_transient_data;
			}

			$data = $this->get_cached_version_info();

			if ( empty( $data ) ) {

				$data = WC_Plugin_API_Wrapper::update_check(
					$this->api_data['item_id'],
					$this->api_data['api_key'],
					$this->api_data['license_key'],
					$this->version
				);

				$this->set_version_info_cache( $data );
			}

			if ( is_wp_error( $data ) ) {
				if ( is_callable( $this->on_error ) ) {
					call_user_func( $this->on_error, $data );
				}
			} elseif ( $data ) {

				if ( version_compare( $this->version, $data->new_version, '<' ) ) {

					$data->package = add_query_arg( 'token', $this->api_data['api_key'], $data->package );

					$_transient_data->response[ $this->name ] = $data;
				}

				$_transient_data->last_checked           = current_time( 'timestamp' );
				$_transient_data->checked[ $this->name ] = $this->version;

			}

			return $_transient_data;
		}

		/**
		 * Return the cached version info.
		 *
		 * @return mixed Version info object or false is expired.
		 */
		protected function get_cached_version_info() {
			$cache = get_option( $this->cache_key );
			if ( empty( $cache['timeout'] ) || time() > $cache['timeout'] ) {
				// Cache is expired.
				return false;
			}

			$cache['value'] = maybe_unserialize( $cache['value'] );
			return $cache['value'];
		}

		/**
		 * Set cached version info.
		 *
		 * @param mixed $value Value to be cached.
		 */
		public function set_version_info_cache( $value = '' ) {
			$data = array(
				'timeout' => strtotime( '+3 hours', time() ),
				'value'   => maybe_unserialize( $value ),
			);
			update_option( $this->cache_key, $data, 'no' );
		}

		/**
		 * Updates information on the "View version x.x details" page with custom data.
		 *
		 * @uses api_request()
		 *
		 * @param mixed  $def The result object or array. Default false.
		 * @param string $action The type of information being requested from the Plugin Installation API.
		 * @param object $args Plugin API arguments.
		 * @return object
		 */
		public function plugin_api_call( $def, $action, $args ) {
			if ( 'plugin_information' !== $action ) {
				return $def;
			}

			if ( ! isset( $args->slug ) || $args->slug !== $this->slug ) {
				return $def;
			}

			$response = WC_Plugin_API_Wrapper::plugin_information(
				$this->api_data['item_id'],
				$this->api_data['api_key'],
				$this->api_data['license_key']
			);

			return $response;
		}

		/**
		 * Debug result.
		 *
		 * @param Object $res Response.
		 * @param string $action Action.
		 * @param array  $args Array of arguments.
		 */
		private function debug_result( $res, $action, $args ) {
			echo '<pre>' . esc_html( print_r( $res, true ) ) . '</pre>'; // phpcs:ignore
			return $res;
		}
	}

endif;
