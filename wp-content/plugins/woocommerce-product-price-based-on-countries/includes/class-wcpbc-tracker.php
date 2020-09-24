<?php
/**
 * Price Based on Country Tracker
 *
 * This is a tracker class to track plugin usage based on if the customer has opted in.
 * No personal information is tracked, only general WooCommerce settings, general product, order and user counts and admin email for discount code.
 *
 * @class 		WCPBC_Tracker
 * @version		1.7.8
 * @category	Class
 * @author 		oscargare
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WCPBC_Tracker {

	/**
     * URL to the API endpoint
     *
     * @var string
     */
	private static $api_url = 'https://tracking.pricebasedcountry.com/';

	/**
     * Tracker ID
     *
     * @var string
     */
	private static $tracker_id = '1ed4e16c036924a22f058d245238f8e1ab4420b1';


	/**
	 * Hook into cron event.
	 */
	public static function init() {
		add_action( 'woocommerce_tracker_send_event', array( __CLASS__, 'send_tracking_data' ) );

		// plugin deactivate actions
        add_action( 'plugin_action_links_' . WCPBC()->plugin_basename(), array( __CLASS__, 'plugin_action_links' ) );
        add_action( 'admin_footer', array( __CLASS__, 'deactivate_scripts' ) );
        add_action( 'wp_ajax_wc_price_based_country_submit_deactivation', array( __CLASS__, 'send_tracking_deactivation' ) );
	}

	/**
	 * Decide whether to send tracking data or not.
	 *
	 * @param boolean $override
	 */
	public static function send_tracking_data( $override = false ) {
		// Don't trigger this on AJAX Requests
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}

		$last_send = self::get_last_send_time();
		if ( $last_send && $last_send > strtotime( '-1 week' ) ) {
            return;
        }

		// Update time first before sending to ensure it is set
		update_option( 'wc_price_based_country_tracker_last_send', time() );

		$params = self::get_tracking_data();

		self::send_request( $params );
	}

	/**
	 * send tracking deactivation data.
	 *
	 * @param boolean $override
	 */
	public static function send_tracking_deactivation() {
		$params = self::get_tracking_data();

		$params['deactivation'] = array(
            'reason_id'     => sanitize_text_field( $_POST['reason_id'] ),
            'reason_info'   => isset( $_POST['reason_info'] ) ? trim( stripslashes( $_POST['reason_info'] ) ) : ''
		);

		self::send_request( $params );
		wp_die();
	}

	/**
     * Send request to remote endpoint
     *
     * @param  array  $params
     *
     * @return void
     */
    private static function send_request( $params ) {

		$params['_tracker_id'] 	= self::$tracker_id; 	// Add tracker id to params

		wp_safe_remote_post( self::$api_url, array(
				'method'      => 'POST',
				'timeout'     => 45,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking'    => false,
				'headers'     => array( 'user-agent' => 'PBCTracker;' . md5( esc_url( home_url( '/' ) ) ) . ';' ),
				'body'        => json_encode( $params ),
				'cookies'     => array(),
				'sslverify'   => false
			)
		);
	}

	/**
	 * Get the last time tracking data was sent.
	 * @return int|bool
	 */
	private static function get_last_send_time() {
		return get_option( 'wc_price_based_country_tracker_last_send', false );
	}

	/**
	 * Get all the tracking data.
	 * @return array
	 */
	private static function get_tracking_data() {
		$data                       = array();

		// General site info
		$data['url']                = home_url('/');
		$data['email']              = get_option( 'admin_email' );
		$data['theme']              = self::get_theme_info();

		// WordPress Info
		$data['wp']                 = self::get_wordpress_info();

		// Server Info
		$data['server']             = self::get_server_info();

		// Plugin info
		$all_plugins                = self::get_all_plugins();
		$data['active_plugins']     = $all_plugins['active_plugins'];
		$data['inactive_plugins']   = $all_plugins['inactive_plugins'];

		// Store count info
		$data['users']              = self::get_user_counts();
		$data['products']           = self::get_product_counts();
		$data['orders']             = self::get_order_counts();

		// Payment gateway info
		$data['gateways']           = self::get_active_payment_gateways();

		// Shipping method info
		$data['shipping_methods']   = self::get_active_shipping_methods();

		// Get options info
		$data['settings']           = self::get_settings();

		// Get pricing zones
		$data['pricing_zones']		= self::get_pricing_zones();

		return $data;
	}

	/**
	 * Get the current theme info, theme name and version.
	 * @return array
	 */
	public static function get_theme_info() {
		$theme_data        = wp_get_theme();
		$theme_child_theme = is_child_theme() ? 'yes' : 'no';
		$theme_wc_support  = ! current_theme_supports( 'woocommerce' ) ? 'no' : 'yes';

		return array( 'name' => $theme_data->Name, 'version' => $theme_data->Version, 'child_theme' => $theme_child_theme, 'wc_support' => $theme_wc_support );
	}

	/**
	 * Get WordPress related data.
	 * @return array
	 */
	private static function get_wordpress_info() {
		$wp_data = array();

		$memory = wc_let_to_num( WP_MEMORY_LIMIT );

		if ( function_exists( 'memory_get_usage' ) ) {
			$system_memory = wc_let_to_num( @ini_get( 'memory_limit' ) );
			$memory        = max( $memory, $system_memory );
		}

		$wp_data['memory_limit'] = size_format( $memory );
		$wp_data['debug_mode']   = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? 'yes' : 'no';
		$wp_data['locale']       = get_locale();
		$wp_data['version']      = get_bloginfo( 'version' );
		$wp_data['multisite']    = is_multisite() ? 'yes' : 'no';

		return $wp_data;
	}

	/**
	 * Get server related info.
	 * @return array
	 */
	private static function get_server_info() {
		$server_data = array();

		if ( isset( $_SERVER['SERVER_SOFTWARE'] ) && ! empty( $_SERVER['SERVER_SOFTWARE'] ) ) {
			$server_data['software'] = $_SERVER['SERVER_SOFTWARE'];
		}

		if ( function_exists( 'phpversion' ) ) {
			$server_data['php_version'] = phpversion();
		}

		if ( function_exists( 'ini_get' ) ) {
			$server_data['php_post_max_size'] = size_format( wc_let_to_num( ini_get( 'post_max_size' ) ) );
			$server_data['php_time_limt'] = ini_get( 'max_execution_time' );
			$server_data['php_max_input_vars'] = ini_get( 'max_input_vars' );
			$server_data['php_suhosin'] = extension_loaded( 'suhosin' ) ? 'yes' : 'no';
		}

		global $wpdb;
		$server_data['mysql_version'] = $wpdb->db_version();

		$server_data['php_max_upload_size'] = size_format( wp_max_upload_size() );
		$server_data['php_default_timezone'] = date_default_timezone_get();
		$server_data['php_soap'] = class_exists( 'SoapClient' ) ? 'yes' : 'no';
		$server_data['php_fsockopen'] = function_exists( 'fsockopen' ) ? 'yes' : 'no';
		$server_data['php_curl'] = function_exists( 'curl_init' ) ? 'yes' : 'no';

		return $server_data;
	}

	/**
	 * Get all plugins grouped into activated or not.
	 * @return array
	 */
	private static function get_all_plugins() {
		// Ensure get_plugins function is loaded
		if ( ! function_exists( 'get_plugins' ) ) {
			include ABSPATH . '/wp-admin/includes/plugin.php';
		}

		$plugins        	 = get_plugins();
		$active_plugins_keys = get_option( 'active_plugins', array() );
		$active_plugins 	 = array();

		foreach ( $plugins as $k => $v ) {
			// Take care of formatting the data how we want it.
			$formatted = array();
			$formatted['name'] = strip_tags( $v['Name'] );
			if ( isset( $v['Version'] ) ) {
				$formatted['version'] = strip_tags( $v['Version'] );
			}
			if ( isset( $v['Author'] ) ) {
				$formatted['author'] = strip_tags( $v['Author'] );
			}
			if ( isset( $v['Network'] ) ) {
				$formatted['network'] = strip_tags( $v['Network'] );
			}
			if ( isset( $v['PluginURI'] ) ) {
				$formatted['plugin_uri'] = strip_tags( $v['PluginURI'] );
			}
			if ( in_array( $k, $active_plugins_keys ) ) {
				// Remove active plugins from list so we can show active and inactive separately
				unset( $plugins[ $k ] );
				$active_plugins[ $k ] = $formatted;
			} else {
				$plugins[ $k ] = $formatted;
			}
		}

		return array( 'active_plugins' => $active_plugins, 'inactive_plugins' => $plugins );
	}

	/**
	 * Get user totals based on user role.
	 * @return array
	 */
	private static function get_user_counts() {
		$user_count          = array();
		$user_count_data     = count_users();
		$user_count['total'] = $user_count_data['total_users'];

		// Get user count based on user role
		foreach ( $user_count_data['avail_roles'] as $role => $count ) {
			$user_count[ $role ] = $count;
		}

		return $user_count;
	}

	/**
	 * Get product totals based on product type.
	 * @return array
	 */
	private static function get_product_counts() {
		$product_count          = array();
		$product_count_data     = wp_count_posts( 'product' );
		$product_count['total'] = $product_count_data->publish;

		$product_statuses = get_terms( 'product_type', array( 'hide_empty' => 0 ) );
		foreach ( $product_statuses as $product_status ) {
			$product_count[ $product_status->name ] = $product_status->count;
		}

		return $product_count;
	}

	/**
	 * Get order counts based on order status.
	 * @return array
	 */
	private static function get_order_counts() {
		$order_count      = array();
		$order_count_data = wp_count_posts( 'shop_order' );

		foreach ( wc_get_order_statuses() as $status_slug => $status_name ) {
			$order_count[ $status_slug ] = $order_count_data->{ $status_slug };
		}

		return $order_count;
	}

	/**
	 * Get a list of all active payment gateways.
	 * @return array
	 */
	private static function get_active_payment_gateways() {
		$active_gateways = array();
		$gateways        = WC()->payment_gateways->payment_gateways();
		foreach ( $gateways as $id => $gateway ) {
			if ( isset( $gateway->enabled ) && 'yes' === $gateway->enabled ) {
				$active_gateways[ $id ] = $gateway->title;
			}
		}

		return $active_gateways;
	}

	/**
	 * Get a list of all active shipping methods.
	 * @return array
	 */
	private static function get_active_shipping_methods() {
		$active_methods   = array();
		$shipping_methods = WC()->shipping->get_shipping_methods();
		foreach ( $shipping_methods as $id => $shipping_method ) {
			if ( isset( $shipping_method->enabled ) && 'yes' === $shipping_method->enabled ) {
				$active_methods[ $id ] = $shipping_method->title;
			}
		}

		return $active_methods;
	}

	/**
	 * Get options.
	 * @return array
	 */
	private static function get_settings() {
		return array(
			'currency'                        => get_woocommerce_currency(),
			'base_location'                   => WC()->countries->get_base_country(),
			'weight_unit'                     => get_option( 'woocommerce_weight_unit' ),
			'dimension_unit'                  => get_option( 'woocommerce_dimension_unit' ),
			'calc_taxes'                      => get_option( 'woocommerce_calc_taxes' ),
			'prices_include_tax'              => get_option( 'woocommerce_prices_include_tax' ),
			'tax_based_on'                    => get_option( 'woocommerce_tax_based_on' ),
			'woocommerce_tax_display_shop'    => get_option( 'woocommerce_tax_display_shop' ),
			'woocommerce_tax_display_cart'    => get_option( 'woocommerce_tax_display_cart' ),
			'coupons_enabled'                 => get_option( 'woocommerce_enable_coupons' ),
			'price_based_on'                  => get_option( 'wc_price_based_country_based_on' ),
			'shipping_exchange_rate'          => get_option( 'wc_price_based_country_shipping_exchange_rate' ),
			'caching_support'				  => get_option( 'wc_price_based_country_caching_support' )
		);
	}

	/**
	 * Get pricing zones.
	 * @return array
	 */
	private static function get_pricing_zones() {
		$zones = array();

		foreach ( get_option( 'wc_price_based_country_regions', array() ) as $zone_id => $zone ) {
			$zones[$zone_id] = $zone;
			$zones[$zone_id]['countries'] = '|'. implode( '|', $zone['countries'] ) .'|';
		}

		return $zones;
	}

	/**
     * Hook into action links and modify the deactivate link
     *
     * @param  array  $links
     *
     * @return array
     */
    public static function plugin_action_links( $links ) {

        if ( array_key_exists( 'deactivate', $links ) ) {
            $links['deactivate'] = str_replace( '<a', '<a class="' . self::$tracker_id . '-deactivate-link"', $links['deactivate'] );
        }

        return $links;
    }

	/**
     * Handle the plugin deactivation feedback
     *
     * @return void
     */
    public static function deactivate_scripts() {
        global $pagenow;

        if ( 'plugins.php' != $pagenow ) {
            return;
        }

        $reasons = array(
            array(
                'id'          => 'could-not-understand',
                'text'        => 'I couldn\'t understand how to make it work',
                'type'        => 'textarea',
                'placeholder' => 'Would you like us to assist you?'
            ),
            array(
                'id'          => 'found-better-plugin',
                'text'        => 'I found a better plugin',
                'type'        => 'text',
                'placeholder' => 'Which plugin?'
            ),
            array(
                'id'          => 'not-have-that-feature',
                'text'        => 'The plugin is great, but I need specific feature that you don\'t support',
                'type'        => 'textarea',
                'placeholder' => 'Could you tell us more about that feature?'
            ),
            array(
                'id'          => 'is-not-working',
                'text'        => 'The plugin is not working',
                'type'        => 'textarea',
                'placeholder' => 'Could you tell us a bit more whats not working?'
            ),
            array(
                'id'          => 'looking-for-other',
                'text'        => 'It\'s not what I was looking for',
                'type'        => '',
                'placeholder' => ''
            ),
            array(
                'id'          => 'did-not-work-as-expected',
                'text'        => 'The plugin didn\'t work as expected',
                'type'        => 'textarea',
                'placeholder' => 'What did you expect?'
            ),
            array(
                'id'          => 'other',
                'text'        => 'Other',
                'type'        => 'textarea',
                'placeholder' => 'Could you tell us a bit more?'
            ),
        );

        ?>

        <div class="pbc-dr-modal" id="<?php echo self::$tracker_id; ?>-pbc-dr-modal">
            <div class="pbc-dr-modal-wrap">
                <div class="pbc-dr-modal-header">
                    <h3><?php _e( 'If you have a moment, please let us know why you are deactivating:', 'woocommerce-product-price-based-on-countries' ); ?></h3>
                </div>

                <div class="pbc-dr-modal-body">
                    <ul class="reasons">
                        <?php foreach ($reasons as $reason) { ?>
                            <li data-type="<?php echo esc_attr( $reason['type'] ); ?>" data-placeholder="<?php echo esc_attr( $reason['placeholder'] ); ?>">
                                <label><input type="radio" name="selected-reason" value="<?php echo $reason['id']; ?>"> <?php echo $reason['text']; ?></label>
                            </li>
                        <?php } ?>
                    </ul>
                </div>

                <div class="pbc-dr-modal-footer">
                    <a href="#" class="dont-bother-me"><?php _e( 'I rather wouldn\'t say', 'woocommerce-product-price-based-on-countries' ); ?></a>
                    <button class="button-secondary"><?php _e( 'Submit & Deactivate', 'woocommerce-product-price-based-on-countries' ); ?></button>
                    <button class="button-primary"><?php _e( 'Cancel', 'woocommerce-product-price-based-on-countries' ); ?></button>
                </div>
            </div>
        </div>

        <style type="text/css">
            .pbc-dr-modal {
                position: fixed;
                z-index: 99999;
                top: 0;
                right: 0;
                bottom: 0;
                left: 0;
                background: rgba(0,0,0,0.5);
                display: none;
            }

            .pbc-dr-modal.modal-active {
                display: block;
            }

            .pbc-dr-modal-wrap {
                width: 475px;
                position: relative;
                margin: 10% auto;
                background: #fff;
            }

            .pbc-dr-modal-header {
                border-bottom: 1px solid #eee;
                padding: 8px 20px;
            }

            .pbc-dr-modal-header h3 {
                line-height: 150%;
                margin: 0;
            }

            .pbc-dr-modal-body {
                padding: 5px 20px 20px 20px;
            }

            .pbc-dr-modal-body .reason-input {
                margin-top: 5px;
                margin-left: 20px;
            }

            .pbc-dr-modal-body textarea, .pbc-dr-modal-body input[type="text"]{
            	width: 100%;
            }

            .pbc-dr-modal-footer {
                border-top: 1px solid #eee;
                padding: 12px 20px;
                text-align: right;
            }
        </style>

        <script type="text/javascript">
            (function($) {
                $(function() {
                    var modal = $( '#<?php echo self::$tracker_id; ?>-pbc-dr-modal' );
                    var deactivateLink = '';

                    $( '#the-list' ).on('click', 'a.<?php echo self::$tracker_id; ?>-deactivate-link', function(e) {
                        e.preventDefault();

                        modal.addClass('modal-active');
                        deactivateLink = $(this).attr('href');
                        modal.find('a.dont-bother-me').attr('href', deactivateLink).css('float', 'left');
                    });

                    modal.on('click', 'button.button-primary', function(e) {
                        e.preventDefault();

                        modal.removeClass('modal-active');
                    });

                    modal.on('click', 'input[type="radio"]', function () {
                        var parent = $(this).parents('li:first');

                        modal.find('.reason-input').remove();

                        var inputType = parent.data('type'),
                            inputPlaceholder = parent.data('placeholder'),
                            reasonInputHtml = '<div class="reason-input">' + ( ( 'text' === inputType ) ? '<input type="text" size="40" />' : '<textarea rows="5" cols="45"></textarea>' ) + '</div>';

                        if ( inputType !== '' ) {
                            parent.append( $(reasonInputHtml) );
                            parent.find('input, textarea').attr('placeholder', inputPlaceholder).focus();
                        }
                    });

                    modal.on('click', 'button.button-secondary', function(e) {
                        e.preventDefault();

                        var button = $(this);

                        if ( button.hasClass('disabled') ) {
                            return;
                        }

                        var $radio = $( 'input[type="radio"]:checked', modal );

                        var $selected_reason = $radio.parents('li:first'),
                            $input = $selected_reason.find('textarea, input[type="text"]');

                        $.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            data: {
                                action: 'wc_price_based_country_submit_deactivation',
                                reason_id: ( 0 === $radio.length ) ? 'none' : $radio.val(),
                                reason_info: ( 0 !== $input.length ) ? $input.val().trim() : ''
                            },
                            beforeSend: function() {
                                button.addClass('disabled');
                                button.text('Processing...');
                            },
                            complete: function() {
                                window.location.href = deactivateLink;
                            }
                        });
                    });
                });
            }(jQuery));
        </script>

        <?php
    }
}
WCPBC_Tracker::init();
