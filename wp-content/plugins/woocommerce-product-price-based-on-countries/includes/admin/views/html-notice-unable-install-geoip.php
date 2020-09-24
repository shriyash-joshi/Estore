<?php
/**
 * Admin View: Notice - Unable to install GeoIP database
 *
 * @package WCPBC/Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$database    = is_callable( array( 'WC_Geolocation', 'get_local_database_path' ) ) ? WC_Geolocation::get_local_database_path() : '';
$geolite_url = defined( 'WC_Geolocation::GEOLITE2_DB' ) ? WC_Geolocation::GEOLITE2_DB : WC_Geolocation::GEOLITE_DB;
?>

<div class="notice notice-error is-dismissible">
	<p>
	<?php // Translators: HTML tags, database path. ?>
	<?php printf( esc_html__( '%1$sUnable to install the GeoIP database.%2$s You have to install it manually: %3$sHow to install the GeoIP database?%4$s', 'woocommerce-product-price-based-on-countries' ), '<strong>', '</strong>', '<a target="_blank" rel="noopener noreferrer" href="https://www.pricebasedcountry.com/docs/common-issues/the-maxmind-geoip-database-does-not-exist/?utm_source=geoipdb-notice&utm_medium=banner&utm_campaign=Docs">', '</a>' ); ?></p>
</div>
