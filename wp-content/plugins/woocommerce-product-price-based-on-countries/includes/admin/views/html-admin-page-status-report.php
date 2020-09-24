<?php
/**
 * Admin View: Page - Status Report.
 *
 * @package WCPBC/Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $wpdb;

?>
<table class="wc_status_table widefat" cellspacing="0">
<thead>
		<tr>
			<th colspan="3" data-export-label="Geolocation debug info"><h2><?php esc_html_e( 'Geolocation debug info', 'woocommerce-product-price-based-on-countries' ); ?></h2></th>
		</tr>
	</thead>
	<tbody id="wcpbc-geolocation-debug">
		<tr>
			<td data-export-label="Default customer location"><?php esc_html_e( 'Default customer location', 'woocommerce-product-price-based-on-countries' ); ?>:</td>
			<td class="help">&nbsp;</td>
			<td><?php echo esc_html( get_option( 'woocommerce_default_customer_address' ) ); ?></td>
		</tr>
		<tr id="wcpbc-geoipdb-exists" data-value="<?php echo ( wcpbc_geoipdb_exists() ? 'yes' : '' ); ?>">
			<td data-export-label="MaxMind GeoIP database"><?php esc_html_e( 'MaxMind GeoIP database', 'woocommerce-product-price-based-on-countries' ); ?>:</td>
			<td class="help"><?php echo wc_help_tip( __( 'The GeoIP database from MaxMind is used to geolocate customers.', 'woocommerce-product-price-based-on-countries' ) ); ?></td>
			<td>
			<?php
			if ( wcpbc_geoipdb_exists() ) {
				echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
			} else {
				echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . esc_html__( 'The MaxMind GeoIP Database does not exist - Geolocation will not function.', 'woocommerce-product-price-based-on-countries' ) . '</mark>';
			}
			?>
			</td>
		</tr>
		<?php if ( version_compare( WC_VERSION, '3.9', '>=' ) ) : ?>
		<tr>
			<td data-export-label="MaxMind GeoIP license"><?php esc_html_e( 'MaxMind license', 'woocommerce-product-price-based-on-countries' ); ?>:</td>
			<td class="help"><?php echo wc_help_tip( __( 'The key that will be used when dealing with MaxMind Geolocation services.', 'woocommerce-product-price-based-on-countries' ) ); ?></td>
			<td>
			<?php
			$maxmind_geolocation_settings = get_option( 'woocommerce_maxmind_geolocation_settings' );
			if ( empty( $maxmind_geolocation_settings['license_key'] ) ) {
				// translators: admin url.
				echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( esc_html__( 'Geolocation has not been configured. You must enter a valid license key on the %1$sMaxMind integration settings page%2$s.', 'woocommerce-product-price-based-on-countries' ), '<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=integration&section=maxmind_geolocation' ) ) . '">', '</a>' ) . '</mark>';
			} else {
				echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
			}
			?>
			</td>
		</tr>
		<?php endif; ?>
		<?php
		foreach ( array( 'MM_COUNTRY_CODE', 'GEOIP_COUNTRY_CODE', 'HTTP_CF_IPCOUNTRY', 'HTTP_X_COUNTRY_CODE', 'HTTP_X_REAL_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' ) as $server_var ) :
			$server_var_value = isset( $_SERVER[ $server_var ] ) ? wcpbc_sanitize_server_var( $_SERVER[ $server_var ] ) : false; // WPCS: sanitization ok, CSRF ok.
			?>
		<tr id="wcpbc-<?php echo esc_html( str_replace( '_', '-', strtolower( $server_var ) ) ); ?>" data-value="<?php echo esc_html( false !== $server_var_value ? $server_var_value : '' ); ?>">
			<td data-export-label="<?php echo esc_attr( $server_var ); ?>"><?php echo esc_html( $server_var ); ?>:</td>
			<td class="help">&nbsp;</td>
			<td><?php echo ( empty( $_SERVER[ $server_var ] ) ? '<mark class="no dashicons dashicons-no-alt"></mark>' : esc_html( $server_var_value ) ); ?></td>
		</tr>
		<?php endforeach; ?>
		<tr>
			<td data-export-label="Real external IP"><?php esc_html_e( 'Real external IP', 'woocommerce-product-price-based-on-countries' ); ?>:</td>
			<td class="help">&nbsp;</td>
			<td id="wcpbc-real-external-ip"></td>
			<?php
				wc_enqueue_js(
					"function show_real_ip(ip){
						$('#wcpbc-real-external-ip').text(ip.trim());
						$('#wcpbc-real-external-ip').data('value', ip.trim());
						$( '#wcpbc-geolocation-debug' ).trigger( 'wc_price_based_country_real_external_ip_loaded' );
					}
					$.get('https://icanhazip.com/', show_real_ip)
					.fail(function(){
						$.get('https://ident.me/', show_real_ip);
					});"
				);
				?>
		</tr>
		<tr id="wcpbc-use-remote-addr" data-value="<?php echo defined( 'WCPBC_USE_REMOTE_ADDR' ) && WCPBC_USE_REMOTE_ADDR ? '1' : ''; ?>">
			<td data-export-label="WCPBC_USE_REMOTE_ADDR">Const WCPBC_USE_REMOTE_ADDR:</td>
			<td class="help">&nbsp;</td>
			<td><?php echo defined( 'WCPBC_USE_REMOTE_ADDR' ) && WCPBC_USE_REMOTE_ADDR ? '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>' : '<mark class="no">&ndash;</mark>'; ?></td>
		</tr>
		<tr id="wcpbc-geolocation-test">
			<td data-export-label="Geolocation Test"><?php esc_html_e( 'Geolocation Test', 'woocommerce-product-price-based-on-countries' ); ?>:</td>
			<td class="help">&nbsp;</td>
			<td class="wcpbc-geolocation-test-result"><?php esc_html_e( 'Runing', 'woocommerce-product-price-based-on-countries' ); ?>...</td>
		</tr>
	</tbody>
</table>

<table class="wc_status_table widefat" cellspacing="0">
	<thead>
		<tr>
			<th colspan="3" data-export-label="PBC Settings"><h2>Price Based on Country <?php esc_html_e( 'General options', 'woocommerce-product-price-based-on-countries' ); ?></h2></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td data-export-label="Version"><?php esc_html_e( 'Version', 'woocommerce-product-price-based-on-countries' ); ?>:</td>
			<td class="help">&nbsp;</td>
			<td><?php echo esc_html( wcpbc()->version ); ?></td>
		</tr>
		<tr>
			<td data-export-label="Base location"><?php esc_html_e( 'Base location', 'woocommerce-product-price-based-on-countries' ); ?>:</td>
			<td class="help">&nbsp;</td>
			<td><?php echo esc_html( get_option( 'woocommerce_default_country' ) ); ?></td>
		</tr>
		<tr>
			<td data-export-label="Base currency"><?php esc_html_e( 'Base currency', 'woocommerce-product-price-based-on-countries' ); ?>:</td>
			<td class="help">&nbsp;</td>
			<td><?php echo esc_html( wcpbc_get_base_currency() ); ?></td>
		</tr>
		<tr>
			<td data-export-label="Price Based On"><?php esc_html_e( 'Price Based On', 'woocommerce-product-price-based-on-countries' ); ?>:</td>
			<td class="help">&nbsp;</td>
			<td><?php echo esc_html( get_option( 'wc_price_based_country_based_on', 'billing' ) ); ?></td>
		</tr>
		<tr>
			<td data-export-label="Shipping"><?php esc_html_e( 'Shipping', 'woocommerce-product-price-based-on-countries' ); ?>:</td>
			<td class="help">&nbsp;</td>
			<td><?php echo 'yes' === get_option( 'wc_price_based_country_shipping_exchange_rate', 'no' ) ? '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>' : '<mark class="no">&ndash;</mark>'; ?></td>
		</tr>
		<tr>
			<td data-export-label="Test mode"><?php esc_html_e( 'Test mode', 'woocommerce-product-price-based-on-countries' ); ?>:</td>
			<td class="help">&nbsp;</td>
			<td><?php echo 'yes' === get_option( 'wc_price_based_country_test_mode', 'no' ) ? '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>' : '<mark class="no">&ndash;</mark>'; ?></td>
		</tr>
		<tr>
			<td data-export-label="Test country"><?php esc_html_e( 'Test country', 'woocommerce-product-price-based-on-countries' ); ?>:</td>
			<td class="help">&nbsp;</td>
			<td><?php echo wp_kses_post( 'yes' === get_option( 'wc_price_based_country_test_mode', 'no' ) ? get_option( 'wc_price_based_country_test_country', '<mark class="no">&ndash;</mark>' ) : '<mark class="no">&ndash;</mark>' ); ?></td>
		</tr>
		<tr>
			<td data-export-label="Load products price in background"><?php esc_html_e( 'Load products price in background', 'woocommerce-product-price-based-on-countries' ); ?>:</td>
			<td class="help">&nbsp;</td>
			<td><?php echo 'yes' === get_option( 'wc_price_based_country_caching_support', 'no' ) ? '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>' : '<mark class="no">&ndash;</mark>'; ?></td>
		</tr>
		<?php if ( wcpbc_is_pro() ) : ?>
		<tr>
			<td data-export-label="Currency format"><?php esc_html_e( 'Currency Format', 'woocommerce-product-price-based-on-countries' ); ?>:</td>
			<td class="help">&nbsp;</td>
			<td><?php echo esc_html( get_option( 'wc_price_based_currency_format' ) ); ?></td>
		</tr>
		<tr>
			<td data-export-label="Currency format"><?php esc_html_e( 'Exchange rate API', 'woocommerce-product-price-based-on-countries' ); ?>:</td>
			<td class="help">&nbsp;</td>
			<td><?php echo esc_html( get_option( 'wc_price_based_country_exchange_rate_api' ) ); ?></td>
		</tr>
		<?php endif; ?>
		<tr>
			<td data-export-label="Prices entered with tax"><?php esc_html_e( 'Prices entered with tax', 'woocommerce-product-price-based-on-countries' ); ?>:</td>
			<td class="help">&nbsp;</td>
			<td><?php echo 'yes' === get_option( 'woocommerce_prices_include_tax', 'no' ) ? '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>' : '<mark class="no">&ndash;</mark>'; ?></td>
		</tr>
		<tr>
			<td data-export-label="Calculate tax based on"><?php esc_html_e( 'Calculate tax based on', 'woocommerce-product-price-based-on-countries' ); ?>:</td>
			<td class="help">&nbsp;</td>
			<td><?php echo esc_html( get_option( 'woocommerce_tax_based_on' ) ); ?></td>
		</tr>
		<tr>
			<td data-export-label="Display prices in the shop"><?php esc_html_e( 'Display prices in the shop', 'woocommerce-product-price-based-on-countries' ); ?>:</td>
			<td class="help">&nbsp;</td>
			<td><?php echo esc_html( get_option( 'woocommerce_tax_display_shop' ) ); ?></td>
		</tr>
	</tbody>
</table>
<?php
$cont      = 0;
$max_zones = 10;
$zones     = WCPBC_Pricing_Zones::get_zones();
?>
<?php foreach ( $zones as $zone ) : ?>
<table class="wc_status_table widefat" cellspacing="0">
	<?php if ( $cont >= $max_zones ) : ?>
		<thead>
			<tr>
				<th colspan="3" data-export-label="<?php echo esc_html( sprintf( 'Showing %s pricing zones of %s', $cont, count( $zones ) ) ); ?>"><h2><?php echo esc_html( sprintf( 'Showing %s zones of %s', $cont, count( $zones ) ) ); ?></h2></th>
			</tr>
		</thead>
		</tbody>
			<tr>
				<td data-export-label="Total pricing zones">Total zones:</td>
				<td class="help">&nbsp;</td>
				<td><?php echo count( $zones ); ?></td>
			</tr>
		</tbody>
		<?php break; ?>
	<?php endif; ?>
	<?php $cont++; ?>
	<thead>
		<tr>
			<th colspan="3" data-export-label="Zone Pricing <?php echo esc_html( $zone->get_name() ); ?>"><h2><?php echo esc_html( __( 'Zone Pricing', 'woocommerce-product-price-based-on-countries' ) . ': "' . $zone->get_name() . '"' ); ?></h2></th>
		</tr>
	</thead>
	<tbody>
	<?php foreach ( $zone->get_data() as $key => $value ) : ?>
		<tr>
			<td data-export-label="<?php echo esc_html( $key ); ?>"><?php echo esc_html( $key ); ?>:</td>
			<td class="help">&nbsp;</td>
			<td><?php echo esc_html( is_array( $value ) ? implode( ' | ', $value ) : $value ); ?></td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>
<?php endforeach; ?>
