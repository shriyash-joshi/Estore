<?php
/**
 * WooCommerce Price Based Country settings page
 *
 * @version 1.8.0
 * @package WCPBC
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WC_Settings_Price_Based_Country' ) ) :

	/**
	 * WC_Settings_Price_Based_Country Class
	 */
	class WC_Settings_Price_Based_Country extends WC_Settings_Page {

		/**
		 * Zone ID.
		 *
		 * @var string
		 */
		protected $zone_id;

		/**
		 * Constructor.
		 */
		public function __construct() {

			$this->id      = 'price-based-country';
			$this->label   = __( 'Zone Pricing', 'woocommerce-product-price-based-on-countries' );
			$this->zone_id = empty( $_GET['zone_id'] ) ? false : wc_clean( wp_unslash( $_GET['zone_id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification

			$this->init_hooks();
			$this->delete_zone();
		}

		/**
		 * Init action and filters
		 */
		protected function init_hooks() {
			add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( 'woocommerce_settings_' . $this->id, array( $this, 'output' ) );
			add_action( 'woocommerce_sections_' . $this->id, array( $this, 'update_zone_notice' ), 5 );
			add_action( 'woocommerce_sections_' . $this->id, array( $this, 'output_sections' ) );
			add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'save' ) );
			add_action( 'update_option_wc_price_based_country_caching_support', array( __CLASS__, 'clear_cache_notice' ) );
		}

		/**
		 * Delete a zone
		 */
		protected function delete_zone() {
			if ( ! empty( $_GET['delete_zone'] ) && isset( $_GET['tab'] ) && 'price-based-country' === $_GET['tab'] && isset( $_GET['section'] ) && 'zones' === $_GET['section'] ) { // WPCS: CSRF ok.

				if ( empty( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'wc-price-based-country-delete-zone' ) ) { // WPCS: input var ok, sanitization ok.
					wp_die( esc_html__( 'Action failed. Please refresh the page and retry.', 'woocommerce-product-price-based-on-countries' ) );
				}

				$zone = WCPBC_Pricing_Zones::get_zone_by_id( wc_clean( wp_unslash( $_GET['delete_zone'] ) ) );
				if ( ! $zone ) {
					wp_die( esc_html__( 'Zone does not exist!', 'woocommerce-product-price-based-on-countries' ) );
				}

				WCPBC_Pricing_Zones::delete( $zone );

				WC_Admin_Settings::add_message( __( 'Zone have been deleted.', 'woocommerce-product-price-based-on-countries' ) );

			}
		}

		/**
		 * Checks the current section
		 *
		 * @param string $section String to check.
		 * @return bool
		 */
		protected function is_section( $section ) {
			global $current_section;
			return $section === $current_section;
		}

		/**
		 * Get sections
		 *
		 * @return array
		 */
		public function get_sections() {
			$sections = array(
				''      => __( 'General options', 'woocommerce-product-price-based-on-countries' ),
				'zones' => __( 'Pricing zones', 'woocommerce-product-price-based-on-countries' ),
			);
			if ( wcpbc_is_pro() ) {
				$sections['license'] = __( 'License', 'woocommerce-product-price-based-on-countries' );
			}
			return $sections;
		}

		/**
		 * Get settings array
		 *
		 * @return array
		 */
		public function get_settings() {
			$settings = apply_filters(
				'wc_price_based_country_settings_general',
				array(
					array(
						'title' => __( 'General Options', 'woocommerce-product-price-based-on-countries' ),
						'type'  => 'title',
						'desc'  => '',
						'id'    => 'general_options',
					),

					array(
						'title'    => __( 'Price Based On', 'woocommerce-product-price-based-on-countries' ),
						'desc'     => __( 'This controls which address is used to refresh products prices on checkout.', 'woocommerce-product-price-based-on-countries' ),
						'id'       => 'wc_price_based_country_based_on',
						'default'  => 'billing',
						'type'     => 'select',
						'class'    => 'wc-enhanced-select',
						'desc_tip' => true,
						'options'  => array(
							'billing'  => __( 'Customer billing country', 'woocommerce-product-price-based-on-countries' ),
							'shipping' => __( 'Customer shipping country', 'woocommerce-product-price-based-on-countries' ),
						),
					),

					array(
						'title'   => __( 'Shipping', 'woocommerce-product-price-based-on-countries' ),
						'desc'    => __( 'Apply exchange rates to shipping cost.', 'woocommerce-product-price-based-on-countries' ),
						'id'      => 'wc_price_based_country_shipping_exchange_rate',
						'default' => 'no',
						'type'    => 'checkbox',
					),

					array(
						'title'    => __( 'Caching support', 'woocommerce-product-price-based-on-countries' ),
						'desc'     => __( 'Load product prices in the background.', 'woocommerce-product-price-based-on-countries' ),
						'id'       => 'wc_price_based_country_caching_support',
						'default'  => 'no',
						'type'     => 'checkbox',
						// translators: HTML tags.
						'desc_tip' => sprintf( __( 'This fires an AJAX request per page (%1$sread more%2$s).', 'woocommerce-product-price-based-on-countries' ), '<a target="_blank" rel="noopener noreferrer" href="https://www.pricebasedcountry.com/docs/getting-started/geolocation-cache-support/?utm_source=settings&utm_medium=banner&utm_campaign=Docs">', '</a>' ),
					),

					array(
						'title'    => __( 'Test mode', 'woocommerce-product-price-based-on-countries' ),
						'desc'     => __( 'Enable test mode', 'woocommerce-product-price-based-on-countries' ),
						'id'       => 'wc_price_based_country_test_mode',
						'default'  => 'no',
						'type'     => 'checkbox',
						// translators: HTML tags.
						'desc_tip' => sprintf( __( 'Enable test mode to show pricing for a specific country (%1$sHow to test%2$s).', 'woocommerce-product-price-based-on-countries' ), '<a target="_blank" rel="noopener noreferrer" href="https://www.pricebasedcountry.com/docs/getting-started/testing/?utm_source=settings&utm_medium=banner&utm_campaign=Docs">', '</a>' ),
					),

					array(
						'title'   => __( 'Test country', 'woocommerce-product-price-based-on-countries' ),
						'id'      => 'wc_price_based_country_test_country',
						'default' => wc_get_base_location(),
						'type'    => 'select',
						'class'   => 'chosen_select',
						'options' => WC()->countries->countries,
					),

					array(
						'type' => 'sectionend',
						'id'   => 'general_options',
					),
				)
			);

			return $settings;
		}

		/**
		 * Output the settings
		 */
		public function output() {
			ob_start();

			if ( $this->is_section( 'zones' ) ) {
				$this->output_zones_screen();
			} elseif ( $this->is_section( 'license' ) && class_exists( 'WCPBC_License_Settings' ) ) {
				WCPBC_License_Settings::output_fields();
			} else {
				$settings = $this->get_settings();
				WC_Admin_Settings::output_fields( $settings );
			}
			$output = ob_get_clean();

			if ( wcpbc_is_pro() ) {
				echo $output; // phpcs:ignore WordPress.Security.EscapeOutput
			} else {
				self::output_ads( $output );
			}
		}

		/**
		 * Output the settings with ads
		 *
		 * @param string $output The setting page.
		 */
		public function output_ads( $output ) {
			?>
			<div class="wc-price-based-country-setting-wrapper-ads">
				<div class="wc-price-based-country-setting-content"><?php echo $output; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
				<div class="wc-price-based-country-setting-sidebar"><?php include dirname( __FILE__ ) . '/views/html-addons-banner.php'; ?></div>
			</div>
			<?php
		}

		/**
		 * Save settings
		 */
		public function save() {

			if ( $this->is_section( 'zones' ) && $this->zone_id ) {
				$this->save_zone();

			} elseif ( $this->is_section( 'license' ) && class_exists( 'WCPBC_License_Settings' ) ) {
				WCPBC_License_Settings::save_fields();

			} elseif ( ! $this->is_section( 'zones' ) ) {
				// Save settings.
				$settings = $this->get_settings();
				WC_Admin_Settings::save_fields( $settings );

				// Update WooCommerce Default Customer Address.
				if ( 'geolocation_ajax' === get_option( 'woocommerce_default_customer_address' ) && 'yes' === get_option( 'wc_price_based_country_caching_support', 'no' ) ) {
					update_option( 'woocommerce_default_customer_address', 'geolocation' );
				}
			}
		}

		/**
		 * Output the clear cache notice after update "AJAX geolocation"
		 */
		public static function clear_cache_notice() {
			WCPBC_Admin_Notices::add_temp_notice( sprintf( '<strong>%1$s</strong> %2$s', esc_html__( 'Heads up!', 'woocommerce-product-price-based-on-countries' ), esc_html__( 'You have to manually purge the cache of your cache plugin for the changes on the "Caching support" option to take effect.', 'woocommerce-product-price-based-on-countries' ) ) );
		}

		/**
		 * Handles output of the pricing zones page in admin.
		 */
		protected function output_zones_screen() {
			global $hide_save_button;

			$hide_save_button = true; // @codingStandardsIgnoreLine

			if ( $this->zone_id ) {
				// Single zone screen.
				if ( 'new' === $this->zone_id ) {
					$zone = WCPBC_Pricing_Zones::create();
				} else {
					$zone = WCPBC_Pricing_Zones::get_zone_by_id( $this->zone_id );
				}

				if ( ! $zone ) {
					wp_die( esc_html__( 'Zone does not exist!', 'woocommerce-product-price-based-on-countries' ) );
				}

				$allowed_countries = WCPBC_Pricing_Zones::get_allowed_countries( $zone );

				include dirname( __FILE__ ) . '/views/html-admin-page-pricing-zone.php';

			} else {
				// Zone list table.
				include_once WCPBC()->plugin_path() . 'includes/admin/class-wcpbc-admin-zone-list-table.php';

				echo '<h3>' . esc_html__( 'Pricing zones', 'woocommerce-product-price-based-on-countries' ) . ' <a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=price-based-country&section=zones&zone_id=new' ) ) . '" class="add-new-h2">' . esc_html__( 'Add pricing zone', 'woocommerce-product-price-based-on-countries' ) . '</a></h3>';
				echo '<p>' . esc_html__( 'A Pricing Zone is a group of countries to which you sell your products at a different price and (or) currency.', 'woocommerce-product-price-based-on-countries' ) . '</p>';

				$table_list = new WCPBC_Admin_Zone_List_Table();
				$table_list->prepare_items();
				$table_list->views();
				$table_list->display();
			}
		}

		/**
		 * Save a zone from the $_POST array.
		 */
		protected function save_zone() {

			do_action( 'wc_price_based_country_settings_before_save_zone' );

			$postdata                           = wc_clean( wp_unslash( $_POST ) ); // WPCS: CSRF ok.
			$postdata['disable_tax_adjustment'] = isset( $postdata['disable_tax_adjustment'] ) ? 'yes' : 'no';

			if ( 'new' === $this->zone_id ) {
				$zone = WCPBC_Pricing_Zones::create();
			} else {
				$zone = WCPBC_Pricing_Zones::get_zone_by_id( $this->zone_id );
			}

			if ( ! $zone ) {
				wp_die( esc_html__( 'Zone does not exist!', 'woocommerce-product-price-based-on-countries' ) );
			}

			// Fields validation.
			$pass = false;

			if ( empty( $postdata['name'] ) ) {
				WC_Admin_Settings::add_error( __( 'Zone name is required.', 'woocommerce-product-price-based-on-countries' ) );

			} elseif ( empty( $postdata['countries'] ) ) {
				WC_Admin_Settings::add_error( __( 'Add at least one country to the list.', 'woocommerce-product-price-based-on-countries' ) );

			} elseif ( empty( $postdata['exchange_rate'] ) ) {
				WC_Admin_Settings::add_error( __( 'Exchange rate must be nonzero.', 'woocommerce-product-price-based-on-countries' ) );

			} elseif ( apply_filters( 'wc_price_based_country_settings_zone_validation', true ) ) {
				$pass = true;
			}

			if ( $pass ) {
				foreach ( $postdata as $field => $value ) {
					if ( is_callable( array( $zone, 'set_' . $field ) ) ) {
						$zone->{'set_' . $field}( $value );
					}
				}

				$id = WCPBC_Pricing_Zones::save( $zone );

				do_action( 'wc_price_based_country_settings_zone_saved', $id );

				wp_safe_redirect( admin_url( 'admin.php?page=wc-settings&tab=price-based-country&section=zones&zone_id=' . $id . '&updated=1' ) );
			}
		}

		/**
		 * Output the zone update notice
		 */
		public function update_zone_notice() {
			if ( $this->is_section( 'zones' ) && ! empty( $_GET['updated'] ) ) { // WPCS: CSRF ok.
				?>
			<div id="message" class="updated inline">
				<p><strong><?php esc_html_e( 'Zone updated successfully.', 'woocommerce-product-price-based-on-countries' ); ?></strong></p>
				<p>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings&tab=price-based-country&section=zones' ) ); ?>">&larr; <?php esc_html_e( 'Back to Zones', 'woocommerce-product-price-based-on-countries' ); ?></a>
					<a style="margin-left:15px;" href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings&tab=price-based-country&section=zones&zone_id=new' ) ); ?>"><?php esc_html_e( 'Add a new zone', 'woocommerce-product-price-based-on-countries' ); ?></a>
				</p>
			</div>
				<?php
			}
		}
	}

endif;

return new WC_Settings_Price_Based_Country();
