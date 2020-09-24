<?php
/**
 * Plugin information API Request.
 *
 * @package WC_Plugin_API
 */

defined( 'ABSPATH' ) || exit;

/**
 * WC_Plugin_Api_Request_Plugin_Information Class.
 */

if ( ! class_exists( 'WCPBC_License_Settings' ) && class_exists( 'WC_Settings_API' ) ) :

	/**
	 * WCPBC_License_Settings Class.
	 */
	class WCPBC_License_Settings extends WC_Settings_API {

		/**
		 * The single instance of the class.
		 *
		 * @var WCPBC_License_Settings
		 */
		protected static $_instance = null; // phpcs:ignore

		/**
		 * License data.
		 *
		 * @var array
		 */
		protected $license_data;

		/**
		 * License status.
		 *
		 * @var string
		 */
		protected $status = '';

		/**
		 * License key.
		 *
		 * @var string
		 */
		protected $license_key = '';

		/**
		 * API key.
		 *
		 * @var string
		 */
		protected $api_key = '';

		/**
		 * Constructor.
		 */
		public function __construct() {

			// The plugin ID. Used for option names.
			$this->plugin_id = 'wc_price_based_country_';

			// ID of the class extending the settings API. Used in option names.
			$this->id = 'license';

			// Init form fields.
			$this->init_form_fields();

			// Define user set variables.
			$this->license_key  = $this->get_option( 'license_key' );
			$this->api_key      = $this->get_option( 'api_key' );
			$this->license_data = wp_parse_args(
				$this->get_option( 'license_data' ),
				array(
					'status'         => '',
					'expires'        => '',
					'product_id'     => '',
					'renewal_period' => '',
					'renewal_url'    => '',
					'timeout'        => '0',
				)
			);
		}

		/**
		 * Save the settings.
		 */
		public function save_settings() {
			$this->settings['license_key']  = $this->license_key;
			$this->settings['api_key']      = $this->api_key;
			$this->settings['license_data'] = $this->license_data;

			return update_option( $this->get_option_key(), $this->settings );
		}

		/**
		 * Set license data.
		 *
		 * @param array $data license data to set.
		 */
		protected function set_license_data( $data ) {
			if ( ! is_array( $data ) ) {
				$data = (array) $data;
			}

			$data = wp_parse_args(
				$data,
				array(
					'status'         => '',
					'expires'        => '',
					'product_id'     => '',
					'renewal_period' => '',
					'renewal_url'    => '',
					'timeout'        => '0',
				)
			);
			foreach ( $data as $prop => $value ) {
				if ( isset( $this->license_data[ $prop ] ) ) {
					$this->license_data[ $prop ] = wc_clean( $value );
				}
			}

			$this->license_data['timeout'] = empty( $this->license_data['status'] ) ? 0 : strtotime( '+3 hours', time() );
		}

		/**
		 * Adds the license status errors.
		 *
		 * @param string $error_message Default error message.
		 */
		protected function add_license_status_error( $error_message ) {

			if ( ! empty( $this->license_data['status'] ) ) {

				switch ( $this->license_data['status'] ) {
					case 'expired':
						// translators: 1: expired date, 2,3: HTML tags.
						$error_message = '<strong>' . sprintf( __( 'The licence expired on %1$s. %2$sRenew your license now%3$s', 'wc-price-based-country-pro' ), date_i18n( wc_date_format(), strtotime( $this->license_data['expires'] ) ), '<a href="' . esc_url( $this->license_data['renewal_url'] ) . '">', '</a>' ) . '</strong>';
						break;
					case 'not_found':
						// translators: 1,2: HTML tags.
						$error_message = sprintf( __( 'The license does not exist or expired more than 30 days ago. %1$sBuy a license%2$s.', 'wc-price-based-country-pro' ), '<a href="https://www.pricebasedcountry.com/pricing/?utm_source=activate-license&utm_medium=banner&utm_campaign=Renew">', '</a>' );
						break;
				}
			}
			$this->add_error( $error_message );
		}

		/**
		 * Activate the license.
		 *
		 * @param string $license_key License key.
		 */
		protected function activate_license( $license_key ) {
			if ( empty( $license_key ) ) {
				return false;
			}

			$this->api_key     = '';
			$this->license_key = $license_key;
			$this->set_license_data( array() );

			$response = WC_Plugin_API_Wrapper::activate_license( $license_key );

			if ( is_wp_error( $response ) ) {

				$this->set_license_data( $response->get_error_data() );
				$this->add_license_status_error( $response->get_error_message() );

				WC_Admin_Settings::add_error( __( 'Unable to activate the license.', 'wc-price-based-country-pro' ) );

			} else {

				// Activate the license.
				if ( ! empty( $response->api_key ) ) {
					$this->api_key = $response->api_key;
				} else {
					WC_Admin_Settings::add_error( __( 'Unable to activate the license. Check the status log file.', 'wc-price-based-country-pro' ) );
				}

				$license_data = isset( $response->license ) ? $response->license : array();

				$this->set_license_data( $license_data );
			}

			return $this->save_settings();
		}

		/**
		 * Deactivate the license.
		 */
		protected function deactivate_license() {
			$result   = false;
			$response = WC_Plugin_API_Wrapper::deactivate_license( $this->license_key, $this->api_key );

			if ( is_wp_error( $response ) ) {
				$this->set_license_data( $response->get_error_data() );
				$this->license_status_error();

				WC_Admin_Settings::add_error( __( 'Unable to deactivate the license.', 'wc-price-based-country-pro' ) );

			} elseif ( $response ) {
				$this->api_key     = '';
				$this->license_key = '';
				$this->set_license_data( array() );

				$result = $this->save_settings();
			}

			return $result;
		}

		/**
		 * Check the license status.
		 */
		public function check_license_status() {

			if ( empty( $this->api_key ) || empty( $this->license_key ) || time() < $this->license_data['timeout'] ) {
				return;
			}

			$response = WC_Plugin_API_Wrapper::status_check( $this->license_key, $this->api_key );

			if ( is_wp_error( $response ) ) {
				$this->set_license_data( $response->get_error_data() );
			} elseif ( $response ) {
				$this->set_license_data( $response );
			}

			if ( 'active' !== $this->license_data['status'] ) {
				$this->api_key = '';
			}
			$this->save_settings();
		}

		/**
		 * Update license settings on update_check error.
		 *
		 * @param WP_Error $error Error object.
		 */
		public function update_check_error( $error ) {
			$this->set_license_data( $error->get_error_data() );
			$this->api_key = '';
			$this->save_settings();
		}

		/**
		 * Unset the renewal period.
		 */
		public function unset_renewal_period() {
			$this->license_data['renewal_period'] = 'no';
			$this->save_settings();
		}

		/**
		 * License key getter.
		 *
		 * @return string
		 */
		public function get_license_key() {
			return $this->license_key;
		}

		/**
		 * API key getter.
		 *
		 * @return string
		 */
		public function get_api_key() {
			return $this->api_key;
		}

		/**
		 * License data getter.
		 *
		 * @return string
		 */
		public function get_license_data() {
			return $this->license_data;
		}

		/**
		 * License has been activated.
		 *
		 * @return boolean
		 */
		public function is_license_active() {
			return ( 'active' === $this->license_data['status'] && ! empty( $this->api_key ) && ! empty( $this->license_key ) );
		}

		/**
		 * Initialize settings form fields.
		 */
		public function init_form_fields() {

			$this->form_fields = array(
				'status'      => array(
					'title' => __( 'License status', 'wc-price-based-country-pro' ),
					'type'  => 'status_info',
				),
				'toggle'      => array(
					'title' => __( 'Toggle activation', 'wc-price-based-country-pro' ),
					'type'  => 'toggle_activation',
				),
				'license_key' => array(
					'title'             => __( 'License Key', 'wc-price-based-country-pro' ),
					'type'              => 'license_key',
					'description'       => __( 'Enter your Price Based on Country Pro license key', 'wc-price-based-country-pro' ),
					'desc_tip'          => true,
					'default'           => '',
					'custom_attributes' => array( 'autocomplete' => 'off' ),
				),
			);
		}

		/**
		 * Processes and saves options.
		 *
		 * @return bool
		 */
		public function process_admin_options() {
			$this->init_settings();

			$fields      = $this->get_form_fields();
			$post_data   = $this->get_post_data();
			$license_key = $this->get_field_value( 'license_key', $fields['license_key'] );

			if ( ! $this->is_license_active() ) {
				// Activate the license.
				$result = $this->activate_license( $license_key );

			} elseif ( $this->is_license_active() && ! empty( $post_data['save'] ) && 'deactivate' === $post_data['save'] ) {
				// Deactivate the license.
				$result = $this->deactivate_license();

			}

			return $result;
		}

		/**
		 * Output the admin options table.
		 */
		public function admin_options() {

			$this->check_license_status();

			if ( $this->is_license_active() ) {
				$GLOBALS['hide_save_button'] = true; // phpcs:ignore
			}
			$this->display_errors();
			parent::admin_options();
		}

		/**
		 * Generate Status Info HTML.
		 *
		 * @param  mixed $key The field key.
		 * @param  mixed $data Field data.
		 * @since  1.0.0
		 * @return string
		 */
		public function generate_status_info_html( $key, $data ) {

			$field_key = $this->get_field_key( $key );
			$defaults  = array(
				'title' => '',
			);

			$data  = wp_parse_args( $data, $defaults );
			$style = 'color:white; padding: 3px 6px; background:' . ( $this->is_license_active() ? 'green' : 'red' ) . ';';
			// translators: License status.
			$text = sprintf( __( 'you are %s receiving updates', 'wc-price-based-country-pro' ), $this->is_license_active() ? '' : sprintf( __( '%1$snot%2$s', 'wc-price-based-country-pro' ), '<strong>', '</strong>' ) );

			ob_start();
			?>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="<?php echo esc_attr( $field_key ); ?>"><?php echo wp_kses_post( $data['title'] ); ?></label>
				</th>
				<td class="forminp">
					<fieldset>
						<span style="color:white; padding: 3px 6px; background:<?php echo ( $this->is_license_active() ? 'green' : 'red' ); ?>">
							<?php echo esc_html( $this->is_license_active() ? 'Active' : 'Inactive' ); ?>
						</span>
						&nbsp; - &nbsp; <?php echo wp_kses_post( $text ); ?>
					</fieldset>
				</td>
			</tr>
			<?php

			return ob_get_clean();
		}

		/**
		 * Generate Toggle Activation HTML.
		 *
		 * @param  mixed $key The field key.
		 * @param  mixed $data Field data.
		 * @return string
		 */
		public function generate_toggle_activation_html( $key, $data ) {
			$field_key = $this->get_field_key( $key );
			$defaults  = array(
				'title' => '',
			);

			$data = wp_parse_args( $data, $defaults );

			ob_start();

			?>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="<?php echo esc_attr( $field_key ); ?>"><?php echo wp_kses_post( $data['title'] ); ?></label>
				</th>
				<td class="forminp">
					<fieldset>
					<?php
					if ( $this->is_license_active() ) {
						echo '<button type="submit" name="save" value="deactivate">' . esc_html__( 'Deactivate License', 'wc-price-based-country-pro' ) . '</button>';
						echo '<p class="description">' . esc_html__( 'Deactivate your license so you can activate it  on another WooCommerce site', 'wc-price-based-country-pro' ) . '</p>';
					} else {
						esc_html_e( 'First add your Price Based on Country license key.', 'wc-price-based-country-pro' );
					}
					?>
					</fieldset>
				</td>
			</tr>
			<?php

			return ob_get_clean();
		}

		/**
		 * Generate License key input box
		 *
		 * @param  mixed $key The field key.
		 * @param  mixed $data Field data.
		 * @return string
		 */
		public function generate_license_key_html( $key, $data ) {

			if ( $this->is_license_active() ) {
				$data['disabled']       = true;
				$this->settings[ $key ] = str_repeat( '*', 24 ) . substr( $this->settings[ $key ], -6 );
			}

			$text_html = $this->generate_text_html( $key, $data );

			if ( $this->is_license_active() && ! empty( $this->license_data['expires'] ) ) {
				// translators: Expire date.
				$text_html .= '<tr valign="top"><th colspan="2">' . sprintf( __( 'Your Price Based on Country Pro license will expire on %s', 'wc-price-based-country-pro' ), date_i18n( wc_date_format(), strtotime( $this->license_data['expires'] ) ) ) . '</td></tr>';
			}
			return $text_html;
		}

		/**
		 * Singelton implementation
		 *
		 * @return WCPBC_License_Settings
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		/**
		 * Output option fields
		 */
		public static function output_fields() {
			self::instance()->admin_options();
		}

		/**
		 * Save option fields
		 */
		public static function save_fields() {
			self::instance()->process_admin_options();
		}
	}

endif;
