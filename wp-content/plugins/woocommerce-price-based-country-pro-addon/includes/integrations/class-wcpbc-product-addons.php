<?php
/**
 * Handle integration with WooCommerce Product Add-ons by WooCommerce.
 *
 * @version 2.8.12
 * @package WCPBC/Integrations
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WCPBC_Product_Addons' ) ) :

	/**
	 * WCPBC_Product_Addons Class
	 */
	class WCPBC_Product_Addons {

		/**
		 * Hook actions and filters
		 *
		 * @since 2.1.0
		 */
		public static function init() {
			add_action( 'wc_price_based_country_frontend_princing_init', array( __CLASS__, 'princing_init' ) );
			add_filter( 'woocommerce_add_cart_item_data', array( __CLASS__, 'add_cart_item_data' ), 20, 2 );
			add_filter( 'woocommerce_get_cart_item_from_session', array( __CLASS__, 'get_cart_item_from_session' ), 19, 2 );
			add_action( 'product_page_global_addons', array( __CLASS__, 'global_addons_admin' ), 20 );
			add_action( 'product_page_addons', array( __CLASS__, 'global_addons_admin' ), 20 ); // WC Product Addons 3.0 Compatible.
			add_action( 'woocommerce_product_write_panel_tabs', array( __CLASS__, 'tab' ), 11 );
			add_action( 'woocommerce_product_data_panels', array( __CLASS__, 'panel' ) );
			add_action( 'woocommerce_process_product_meta', array( __CLASS__, 'process_meta_box' ), 10, 2 );
			add_action( 'woocommerce-product-addons_start', array( __CLASS__, 'addons_wrapper_start' ), 100 );
			add_action( 'woocommerce_product_addons_start', array( __CLASS__, 'addons_wrapper_start' ), 100 ); // WC Product Addons 3.0 Compatible.
			add_filter( 'wc_price_based_country_ajax_geolocation_addon_content', array( __CLASS__, 'ajax_geolocation_addon_content' ), 10, 2 );
			add_filter( 'wc_price_based_country_ajax_geolocation_product_data', array( __CLASS__, 'ajax_geolocation_product_data' ), 10, 2 );
		}

		/**
		 * Is WooCommerce Product Add-ons < 3.0?
		 *
		 * @since 2.4.2
		 * @return bool
		 */
		private static function is_product_addon_legacy() {
			return version_compare( WC_VERSION, '3.0.0', '<' ) || ( defined( 'WC_PRODUCT_ADDONS_VERSION' ) && version_compare( WC_PRODUCT_ADDONS_VERSION, '3.0', '<' ) );
		}
		/**
		 * Init pricing hooks
		 */
		public static function princing_init() {
			add_filter( 'get_product_addons_fields', array( __CLASS__, 'product_addons_fields' ), 10, 2 );
		}

		/**
		 * Add cart item data.
		 *
		 * @param array $cart_item_meta Cart item meta data.
		 * @param int   $product_id     Product ID.
		 * @return array
		 */
		public static function add_cart_item_data( $cart_item_meta, $product_id ) {
			if ( ! empty( $cart_item_meta['addons'] ) ) {
				$cart_item_meta['wcpbc_addon']['post_data'] = $_POST; // phpcs:ignore WordPress.Security.NonceVerification
				$cart_item_meta['wcpbc_addon']['zone_id']   = empty( WCPBC()->current_zone ) ? '' : WCPBC()->current_zone->get_zone_id();
			}

			return $cart_item_meta;
		}

		/**
		 * Update addons price on get cart item from session.
		 *
		 * @param array $cart_item Cart item data.
		 * @param array $values    Cart item values.
		 * @return array
		 */
		public static function get_cart_item_from_session( $cart_item, $values ) {
			if ( empty( $values['wcpbc_addon'] ) || empty( $GLOBALS['Product_Addon_Cart'] ) ) {
				return $cart_item;
			}

			$cart_item['wcpbc_addon']['post_data'] = $values['wcpbc_addon']['post_data'];
			$cart_item['wcpbc_addon']['zone_id']   = empty( WCPBC()->current_zone ) ? '' : WCPBC()->current_zone->get_zone_id();

			if ( self::is_product_addon_legacy() ) {
				// WooCommerce Product Add-ons < 3.0.
				$add_cart_item_data = $GLOBALS['Product_Addon_Cart']->add_cart_item_data( array(), $cart_item['product_id'], $values['wcpbc_addon']['post_data'], true );

				if ( isset( $add_cart_item_data['addons'] ) ) {
					$cart_item['addons'] = $add_cart_item_data['addons'];
					$cart_item           = $GLOBALS['Product_Addon_Cart']->add_cart_item( $cart_item );
				}
			} else {

				// WooCommerce Product Add-ons 3.0 compatibility.
				$cart_item['addons'] = $values['addons'];

				$cart_addons = self::get_addons( $values['product_id'], $values['wcpbc_addon']['post_data'] );

				if ( isset( $cart_item['addon_parent_id'] ) && isset( $cart_item['price_type'] ) && 'percentage_based' !== $cart_item['price_type'] ) {
					// WC Product Add-ons 3.0. Add-ons cart items.
					$compare_addon = array(
						'name'       => empty( $values['addon_name'] ) ? '' : $values['addon_name'],
						'value'      => empty( $values['addon_label'] ) ? '' : $values['addon_label'],
						'field_type' => empty( $values['field_type'] ) ? '' : $values['field_type'],
						'price_type' => empty( $values['price_type'] ) ? '' : $values['price_type'],
					);

					foreach ( $cart_addons as $addon ) {
						if ( self::equals_addon( $addon, $compare_addon ) ) {
							$cart_item['price'] = $addon['price'];
							break;
						}
					}
				} elseif ( ! isset( $cart_item['addon_parent_id'] ) ) {
					// Product with add-ons.
					foreach ( $values['addons'] as $key => $values ) {
						foreach ( $cart_addons as $addon ) {
							if ( self::equals_addon( $values, $addon ) ) {
								$cart_item['addons'][ $key ] = $addon;
								break;
							}
						}
					}
				}

				if ( ! is_callable( array( $GLOBALS['Product_Addon_Cart'], 'set_cart_items' ) ) ) {
					$cart_item = $GLOBALS['Product_Addon_Cart']->add_cart_item( $cart_item );
				}
			}

			remove_filter( 'woocommerce_get_cart_item_from_session', array( $GLOBALS['Product_Addon_Cart'], 'get_cart_item_from_session' ), 20, 2 );

			return $cart_item;
		}

		/**
		 * Return product addons from post data. Only WC Product Addons +3.0
		 *
		 * @since 2.4.6
		 * @param int   $product_id Product ID.
		 * @param array $post_data Post data.
		 * @return array
		 */
		private static function get_addons( $product_id, $post_data ) {
			// phpcs:disable WordPress.Security.NonceVerification
			$addons = array();

			if ( isset( $_POST ) ) {
				$_postdata = $_POST;
			}

			// Get the addons.
			$_POST = $post_data;
			$data  = $GLOBALS['Product_Addon_Cart']->add_cart_item_data( array(), $product_id, null, null );
			if ( isset( $data['addons'] ) ) {
				$addons = $data['addons'];
			}

			if ( isset( $_POST ) ) {
				$_POST = $_postdata;
			}
			// phpcs:enable

			return $addons;
		}
		/**
		 * Checks if two arrays of add-ons are equals.
		 *
		 * @since 2.4.2
		 * @see get_cart_item_from_session
		 * @param array $addon The add-on.
		 * @param array $compare The values to compare.
		 */
		private static function equals_addon( $addon, $compare ) {
			$addon   = wp_parse_args(
				$addon,
				array(
					'name'       => '',
					'value'      => '',
					'field_name' => '',
					'field_type' => '',
					'price_type' => '',
				)
			);
			$compare = wp_parse_args( $compare, $addon );

			return (
				$addon['name'] === $compare['name'] &&
				$addon['value'] === $compare['value'] &&
				$addon['field_name'] === $compare['field_name'] &&
				$addon['field_type'] === $compare['field_type'] &&
				$addon['price_type'] === $compare['price_type']
			);

		}

		/**
		 * Return addons based on country.
		 *
		 * @param array $addons Array of addons.
		 * @param int   $post_id Post ID.
		 */
		public static function product_addons_fields( $addons, $post_id ) {
			$zone         = WCPBC()->current_zone;
			$zone_id      = $zone->get_zone_id();
			$zone_pricing = array_filter( (array) get_post_meta( $post_id, '_product_addons_zone_pricing', true ) );
			$pricing      = ! empty( $zone_pricing[ $zone_id ] ) ? $zone_pricing[ $zone_id ] : false;

			foreach ( $addons as $loop => $addon ) {

				$is_exchange_rate = wcpbc_is_exchange_rate( $pricing[ $loop ]['price_method'] );

				// Add-on options.
				if ( isset( $addon['options'] ) ) {
					foreach ( $addon['options'] as $index => $option ) {
						if ( ! isset( $option['price_type'] ) || 'percentage_based' !== $option['price_type'] ) {
							if ( $is_exchange_rate || ! isset( $pricing[ $loop ]['price'][ $index ] ) ) {
								$addons[ $loop ]['options'][ $index ]['price'] = $zone->get_exchange_rate_price( $addons[ $loop ]['options'][ $index ]['price'] );
							} else {
								$addons[ $loop ]['options'][ $index ]['price'] = $pricing[ $loop ]['price'][ $index ];
							}
						}
					}
				}

				// Add-on adjust price.
				if ( isset( $addons[ $loop ]['price'] ) && isset( $addon['adjust_price'] ) && $addon['adjust_price'] && isset( $addon['price_type'] ) && 'percentage_based' !== $addon['price_type'] ) {
					if ( $is_exchange_rate || ! isset( $pricing[ $loop ]['adjust_price'] ) ) {
						$addons[ $loop ]['price'] = $zone->get_exchange_rate_price( $addon['price'] );
					} else {
						$addons[ $loop ]['price'] = $pricing[ $loop ]['adjust_price'];
					}
				}

				// Add-on min and max.
				if ( isset( $addon['type'] ) && 'custom_price' === $addon['type'] && isset( $addon['min'] ) ) {
					if ( $is_exchange_rate || ! isset( $pricing[ $loop ]['min'] ) ) {
						$addons[ $loop ]['min'] = $zone->get_exchange_rate_price( $addon['min'] );
					} else {
						$addons[ $loop ]['min'] = $pricing[ $loop ]['min'];
					}
				}

				if ( isset( $addon['type'] ) && 'custom_price' === $addon['type'] && isset( $addon['max'] ) ) {
					if ( $is_exchange_rate || ! isset( $pricing[ $loop ]['max'] ) ) {
						$addons[ $loop ]['max'] = $zone->get_exchange_rate_price( $addon['max'] );
					} else {
						$addons[ $loop ]['max'] = $pricing[ $loop ]['max'];
					}
				}
			}

			return $addons;
		}

		/**
		 * Save zone pricing for addons
		 *
		 * @param int   $post_id Post ID.
		 * @param array $product_addons Products addons.
		 */
		private static function save_zone_pricing_data( $post_id, $product_addons ) {

			$post_data = ! empty( $_POST['_product_addons_zone_pricing'] ) ? wc_clean( wp_unslash( $_POST['_product_addons_zone_pricing'] ) ) : array(); // phpcs:ignore WordPress.Security.NonceVerification
			$pricing   = array();

			foreach ( WCPBC_Pricing_Zones::get_zones() as $zone ) {
				$zone_id      = $zone->get_zone_id();
				$zone_pricing = array();

				foreach ( $product_addons as $loop => $addon ) {

					$zone_pricing[ $loop ]['price_method'] = ! empty( $post_data[ $zone_id ][ $loop ]['price_method'] ) ? $post_data[ $zone_id ][ $loop ]['price_method'] : 'exchange_rate';

					if ( 'manual' === $zone_pricing[ $loop ]['price_method'] ) {

						foreach ( $addon['options'] as $index => $option ) {
							$price_type = empty( $option['price_type'] ) ? 'flat_fee' : $option['price_type'];

							if ( 'percentage_based' !== $price_type ) {
								if ( isset( $post_data[ $zone_id ][ $loop ]['price'][ $index ] ) ) {
									$zone_pricing[ $loop ]['price'][ $index ] = wc_format_decimal( $post_data[ $zone_id ][ $loop ]['price'][ $index ] );
								} else {
									$zone_pricing[ $loop ]['price'][ $index ] = wc_format_decimal( $option['price'] * $zone->get_exchange_rate() );
								}
							}
						}

						$adjust_price = empty( $addon['adjust_price'] ) ? '1' : $addon['adjust_price'];
						$price_type   = empty( $addon['price_type'] ) ? 'flat_fee' : $addon['price_type'];

						if ( $adjust_price && 'percentage_based' !== $price_type ) {
							if ( isset( $post_data[ $zone_id ][ $loop ]['adjust_price'] ) ) {
								$zone_pricing[ $loop ]['adjust_price'] = wc_format_decimal( $post_data[ $zone_id ][ $loop ]['adjust_price'] );
							} elseif ( ! empty( $addon['price'] ) ) {
								$zone_pricing[ $loop ]['adjust_price'] = wc_format_decimal( $addon['price'] * $zone->get_exchange_rate() );
							}
						}

						$addon_type = empty( $addon['type'] ) ? 'multiple_choice' : $addon['type'];
						if ( 'custom_price' === $addon_type ) {
							if ( isset( $post_data[ $zone_id ][ $loop ]['min'] ) ) {
								$zone_pricing[ $loop ]['min'] = wc_format_decimal( $post_data[ $zone_id ][ $loop ]['min'] );
							} elseif ( ! empty( $addon['min'] ) ) {
								$zone_pricing[ $loop ]['min'] = wc_format_decimal( $addon['min'] * $zone->get_exchange_rate() );
							}

							if ( isset( $post_data[ $zone_id ][ $loop ]['max'] ) ) {
								$zone_pricing[ $loop ]['max'] = wc_format_decimal( $post_data[ $zone_id ][ $loop ]['max'] );
							} elseif ( ! empty( $addon['max'] ) ) {
								$zone_pricing[ $loop ]['max'] = wc_format_decimal( $addon['max'] * $zone->get_exchange_rate() );
							}
						}
					}
				}

				$pricing[ $zone_id ] = $zone_pricing;
			}

			update_post_meta( $post_id, '_product_addons_zone_pricing', $pricing );
		}

		/**
		 * Controls the global addons admin page.
		 */
		public static function global_addons_admin() {
			// phpcs:disable WordPress.Security.NonceVerification
			if ( ! empty( $_GET['add'] ) || ! empty( $_GET['edit'] ) ) {

				// Get post_id.
				$post_id = ! empty( $_GET['edit'] ) ? absint( $_GET['edit'] ) : '';
				$post_id = empty( $edit_id ) && ! empty( $_POST['edit_id'] ) ? absint( $_POST['edit_id'] ) : $post_id;

				if ( ! empty( $post_id ) ) {

					$global_addon   = get_post( $post_id );
					$product_addons = array_filter( (array) get_post_meta( $global_addon->ID, '_product_addons', true ) );

					if ( ! empty( $_POST['_product_addons_zone_pricing'] ) ) {

						self::save_zone_pricing_data( $global_addon->ID, $product_addons );
					}

					$zone_pricing = array_filter( (array) get_post_meta( $global_addon->ID, '_product_addons_zone_pricing', true ) );
				}

				// Handle WooCommerce Product Add-ons 3.0 compatibility.
				if ( self::is_product_addon_legacy() ) {
					include WC_Product_Price_Based_Country_Pro::plugin_path() . 'includes/admin/views/product-addons/legacy/html-global-admin-add.php';

				} else {
					include WC_Product_Price_Based_Country_Pro::plugin_path() . 'includes/admin/views/product-addons/html-global-admin-add.php';

					// Redirect to the edit page after save.
					if ( ! empty( $_GET['add'] ) ) {
						wc_enqueue_js(
							'if ( $(\'input[name="edit_id"]\').val() > 0 ) {
								var addons_url = $(location).attr(\'href\').replace("&add=1", "&edit=" + $(\'input[name="edit_id"]\').val());
								$(location).attr(\'href\', addons_url);
							}'
						);
					}
				}
			}
			// phpcs:enable
		}

		/**
		 * Add product tab.
		 */
		public static function tab() {
			?>
			<li class="addons_tab product_addons"><a href="#wcpbc_product_addons_data"><span><?php esc_html_e( 'Add-ons zone pricing', 'wc-price-based-country-pro' ); ?></span></a></li>
			<?php
		}

		/**
		 * Add product panel.
		 */
		public static function panel() {
			global $post;

			$product_addons = array_filter( (array) get_post_meta( $post->ID, '_product_addons', true ) );
			$zone_pricing   = array_filter( (array) get_post_meta( $post->ID, '_product_addons_zone_pricing', true ) );

			// Handle WooCommerce Product Add-ons 3.0 compatibility.
			if ( self::is_product_addon_legacy() ) {
				include WC_Product_Price_Based_Country_Pro::plugin_path() . 'includes/admin/views/product-addons/legacy/html-addon-panel.php';
			} else {
				include WC_Product_Price_Based_Country_Pro::plugin_path() . 'includes/admin/views/product-addons/html-addon-panel.php';
			}
		}

		/**
		 * Process meta box.
		 *
		 * @param int     $post_id Post ID.
		 * @param WP_Post $post Post instance.
		 */
		public static function process_meta_box( $post_id, $post ) {
			if ( ! empty( $_POST['_product_addons_zone_pricing'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				$product_addons = array_filter( (array) get_post_meta( $post_id, '_product_addons', true ) );
				self::save_zone_pricing_data( $post_id, $product_addons );
			}
		}

		/**
		 * Product add-ons wrapper for ajax geolocation
		 *
		 * @param int $product_id Product ID.
		 */
		public static function addons_wrapper_start( $product_id ) {
			if ( is_callable( array( 'WCPBC_Ajax_Geolocation', 'is_enabled' ) ) && WCPBC_Ajax_Geolocation::is_enabled() ) {
				echo '<div class="wc-price-based-country-refresh-area" data-area="addon" data-id="' . esc_attr( $product_id ) . '" data-options="' . esc_attr( wp_json_encode( array( 'product_id' => $product_id ) ) ) . '">';
				add_action( 'woocommerce-product-addons_end', array( __CLASS__, 'addons_wrapper_end' ), 1 );
				add_action( 'woocommerce_product_addons_end', array( __CLASS__, 'addons_wrapper_end' ), 1 );
			}
		}

		/**
		 * Product add-ons wrapper for ajax geolocation
		 *
		 * @param int $product_id Product ID.
		 */
		public static function addons_wrapper_end( $product_id ) {
			echo '</div>';
		}

		/**
		 * Return product addons content
		 *
		 * @param string $content HTML content to return.
		 * @param array  $addon_data Addon data.
		 * @return string
		 */
		public static function ajax_geolocation_addon_content( $content, $addon_data ) {

			$display = isset( $GLOBALS['Product_Addon_Display'] ) ? $GLOBALS['Product_Addon_Display'] : false;
			$post_id = ! empty( $addon_data['product_id'] ) ? absint( $addon_data['product_id'] ) : false;

			if ( $post_id && is_callable( array( $display, 'display' ) ) ) {
				$_product = wc_get_product( $post_id );
				if ( $_product ) {
					$GLOBALS['product'] = $_product; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
				}
				ob_start();
				$display->display( $post_id );
				$content = ob_get_clean();
			}

			return $content;
		}

		/**
		 * Add raw_price to product data array
		 *
		 * @param array      $data Array of data to return.
		 * @param WC_Product $product Product instance.
		 * @return array
		 */
		public static function ajax_geolocation_product_data( $data, $product ) {

			if ( 'no' === get_option( 'woocommerce_prices_include_tax' ) ) {
				$data['raw_price'] = wc_get_price_excluding_tax( $product );
			} else {
				$data['raw_price'] = wc_get_price_including_tax( $product );
			}

			return $data;
		}
	}

	WCPBC_Product_Addons::init();

endif;
