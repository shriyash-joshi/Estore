<?php
/**
 * Currency Switcher Widget.
 *
 * @version 2.5.0
 * @package WCPBC
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WCPBC_Widget_Currency_Switcher class.
 */
class WCPBC_Widget_Currency_Switcher extends WC_Widget {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->widget_description = __( 'A currency switcher for your store.', 'wc-price-based-country-pro' );
		$this->widget_id          = 'wcpbc_currency_switcher';
		$this->widget_name        = __( 'WooCommerce Currency Switcher', 'wc-price-based-country-pro' );
		$this->settings           = array(
			'title'                  => array(
				'type'  => 'text',
				'std'   => __( 'Currency', 'wc-price-based-country-pro' ),
				'label' => __( 'Title', 'wc-price-based-country-pro' ),
			),
			'currency_display_style' => array(
				'type'  => 'display_style',
				'std'   => '',
				'label' => __( 'Currency display style', 'wc-price-based-country-pro' ),
			),
		);

		add_action( 'woocommerce_widget_field_display_style', array( $this, 'field_display_style' ), 10, 4 );

		parent::__construct();
	}

	/**
	 * Output currency display style field.
	 *
	 * @param string $key Field key.
	 * @param string $value Field value.
	 * @param array  $setting Widget settings.
	 * @param object $instance Widget instance.
	 */
	public function field_display_style( $key, $value, $setting, $instance ) {
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>"><?php echo esc_html( $setting['label'] ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( $key ) ); ?>" type="text" value="<?php echo esc_attr( $value ); ?>" placeholder="[name] ([symbol])" />
		</p>
		<p class="description"><?php esc_html_e( 'Supports the following placeholders: [name] = currency name, [code] = currency code and [symbol] = currency symbol', 'wc-price-based-country-pro' ); ?></p>
		<?php
	}

	/**
	 * Widget function.
	 *
	 * @see WP_Widget
	 *
	 * @param array $args Args.
	 * @param array $instance Object instance.
	 */
	public function widget( $args, $instance ) {

		$currencies = array();
		$countries  = array();

		$base_country  = wc_get_base_location();
		$base_country  = $base_country['country'];
		$base_currency = wcpbc_get_base_currency();

		$selected_currency = $base_currency;
		$customer_country  = wcpbc_get_woocommerce_country();

		foreach ( WCPBC_Pricing_Zones::get_zones() as $zone ) {

			if ( ! count( $zone->get_countries() ) ) {
				continue;
			}

			if ( count( $zone->get_countries() ) > 1 && in_array( $base_country, $zone->get_countries(), true ) ) {
				// Get first country that isn't base country.
				foreach ( $zone->get_countries() as $region_country ) {
					if ( $region_country !== $base_country ) {
						$_country = $region_country;
						break;
					}
				}
			} else {
				$_country = $zone->get_countries();
				$_country = $_country[0];
			}

			// Check selected currency.
			if ( in_array( $customer_country, $zone->get_countries(), true ) ) {
				$selected_currency = $zone->get_currency();
				$_country          = $customer_country;
			}

			// Add currency to array.
			$currencies[ $_country ] = $zone->get_currency();

			// Add zone countries to array.
			$countries = array_merge( $countries, $zone->get_countries() );
		}

		// Add base country.
		if ( ! in_array( $base_country, $countries, true ) ) {
			$currencies[ $base_country ] = $base_currency;
		}

		// Add others countries currency.
		if ( ! in_array( $base_currency, $currencies, true ) ) {
			$country_diff            = array_diff( array_keys( WC()->countries->countries ), $countries );
			$_country                = current( $country_diff );
			$currencies[ $_country ] = $base_currency;
		}

		// Filter currencies array.
		$currencies = array_unique( $currencies );

		// Generate select options array.
		$options = array();

		$wc_currencies = get_woocommerce_currencies();

		$display_style = empty( $instance['currency_display_style'] ) ? '[name] ([symbol])' : $instance['currency_display_style'];

		foreach ( $currencies as $country => $currency ) {

			$options['options'][ $country ]    = apply_filters(
				'wc_price_based_country_currency_widget_text',
				str_replace( array( '[code]', '[symbol]', '[name]', ' ' ), array( $currency, get_woocommerce_currency_symbol( $currency ), $wc_currencies[ $currency ], '&nbsp;' ), $display_style ),
				$currency
			);
			$options['currencies'][ $country ] = $currency;

			if ( $currency === $selected_currency ) {
				$options['selected_country'] = $country;
			}
		}

		wcpbc_maybe_asort_locale( $options['options'] );

		$widget_data = wp_json_encode(
			array(
				'instance' => $instance,
				'id'       => $this->widget_id,
			)
		);

		$this->widget_start( $args, $instance );

		echo '<div class="wc-price-based-country wc-price-based-country-refresh-area" data-area="widget" data-id="' . esc_attr( md5( $widget_data ) ) . '" data-options="' . esc_attr( $widget_data ) . '">';
		wc_get_template(
			'currency-switcher.php',
			$options,
			'woocommerce-product-price-based-on-countries/',
			WC_Product_Price_Based_Country_Pro::plugin_path() . 'templates/'
		);
		echo '</div>';

		$this->widget_end( $args );
	}
}
?>
