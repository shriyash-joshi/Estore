=== Price Based on Country for WooCommerce ===
Contributors: oscargare
Tags: price based country, dynamic price based country, price by country, dynamic price, woocommerce, geoip, country-targeted pricing
Requires at least: 3.8
Tested up to: 5.4
Stable tag: 2.0.10
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Add multicurrency support to WooCommerce, allowing you set product's prices in multiple currencies based on country of your site's visitor.

== Description ==

**Price Based on Country for WooCommerce** allows you to sell the same product in multiple currencies based on the country of the customer.

= How it works =

The plugin detects automatically the country of the website visitor throught the geolocation feature included in WooCommerce (2.3.0 or later) and display the currency and price you have defined previously for this country.

You have two ways to set product's price for each country:

* Calculate price by applying the exchange rate.
* Set price manually.

When country changes on checkout page, the cart, the order preview and all shop are updated to display the correct currency and pricing.

= Multicurrency =
Sell and receive payments in different currencies, reducing the costs of currency conversions.

= Country Switcher =
The extension include a country switcher widget to allow your customer change the country from the frontend of your website.

= Shipping currency conversion =
Apply currency conversion to Flat and International Flat Rate Shipping.

= Compatible with WPML =
WooCommerce Product Price Based on Countries is officially compatible with [WPML](https://wpml.org/extensions/woocommerce-product-price-based-countries/).

= Upgrade to Pro =

>This plugin offers a Pro addon which adds the following features:

>* Guaranteed support by private ticket system.
>* Automatic updates of exchange rates.
>* Add an exchange rate fee.
>* Round to nearest.
>* Display the currency code next to price.
>* Compatible with the WooCommerce built-in CSV importer and exporter.
>* Thousand separator, decimal separator and number of decimals by pricing zone.
>* Currency switcher widget.
>* Support to WooCommerce Subscriptions by Prospress .
>* Support to WooCommerce Product Bundles by SomewhereWarm .
>* Support to WooCommerce Product Add-ons by WooCommerce .
>* Support to WooCommerce Bookings by WooCommerce .
>* Support to WooCommerce Composite Product by SomewhereWarm.
>* Support to WooCommerce Name Your Price by Kathy Darling.
>* Bulk editing of variations princing.
>* Support for manual orders.
>* More features and integrations is coming.

>[Get Price Based on Country Pro now](https://www.pricebasedcountry.com?utm_source=wordpress.org&utm_medium=readme&utm_campaign=Extend)

= Requirements =

* WooCommerce 3.4 or later.
* If you want to receive payments in more of one currency, a payment gateway that supports them.

== Installation ==

1. Download, install and activate the plugin.
1. Go to WooCommerce -> Settings -> Product Price Based on Country and configure as required.
1. Go to the product page and sets the price for the countries you have configured avobe.

= Adding a country selector to the front-end =

Once you’ve added support for multiple country and their currencies, you could display a country selector in the theme. You can display the country selector with a shortcode or as a hook.

**Shortcode**

[wcpbc_country_selector other_countries_text="Other countries"]

**PHP Code**

do_action('wcpbc_manual_country_selector', 'Other countries');

= Customize country selector (only for developers) =

1. Add action "wcpbc_manual_country_selector" to your theme.
1. To customize the country selector:
	1. Create a directory named "woocommerce-product-price-based-on-countries" in your theme directory.
	1. Copy to the directory created avobe the file "country-selector.php" included in the plugin.
	1. Work with this file.

== Frequently Asked Questions ==

= How might I test if the prices are displayed correctly for a given country? =

If you are in a test environment, you can configure the test mode in the setting page.

In a production environment you can use a privacy VPN tools like [TunnelBear](https://www.tunnelbear.com/) or [ZenMate](https://zenmate.com/)

You should do the test in a private browsing window to prevent data stored in the session. Open a private window on [Firefox](https://support.mozilla.org/en-US/kb/private-browsing-use-firefox-without-history#w_how-do-i-open-a-new-private-window) or on [Chrome](https://support.google.com/chromebook/answer/95464?hl=en)

== Screenshots ==

1. /assets/screenshot-1.png
2. /assets/screenshot-2.png
3. /assets/screenshot-3.png
4. /assets/screenshot-4.png
5. /assets/screenshot-5.png
5. /assets/screenshot-6.png

== Changelog ==

= 2.0.10 (2020-08-04) =
* Fixed: Plugin causes an error on the "Hand-picked Products" block. Thanks @blogjunkie

= 2.0.9 (2020-07-07) =
* Added: Tested up WooCommerce 4.3.
* Tweak: Improve the compatibility with the PayPal Express Checkout by WooCommerce 2.0+ payment method.
* Tweak: Limit the number of pricing zones displayed on the System Status Report.

= 2.0.8 (2020-05-28) =
* Added: Tested up WooCommerce 4.2.
* Fixed: Compatibility issue with WooCommerce Dynamic Pricing by Lucas Stark.
* Fixed: Checkout issue on PayPal Express Checkout by WooCommerce 2.0+ payment method.
* Fixed: Geolocation cache support via AJAX does not refresh the "content shortcode" when there are no products on the page.

= 2.0.7 (2020-05-22) =
* Added: Geolocation cache support via AJAX compatible with Variation Swatches for WooCommerce Pro by Emran Ahmed.
* Added: Geolocation cache support via AJAX compatible with Flatsome Infinite Scroll.
* Fixed: PHP error - Undefined function on WooCommerce < 3.6
* Fixed: Incorrect domain path.

= 2.0.6 (2020-05-14) =
* Fixed: Deprecated use of implode in the pricing zones table.
* Added: Improve compatibility with WooCommerce UPS Shipping method.

= 2.0.5 (2020-05-01) =
* Added: Tested up WooCommerce 4.1.
* Fixed: The option "Convert by exchange rate" of coupons is not saved on coupon update.
* Fixed: The geolocation test detects false positives.
* Tweak: Admin notices improvements.
* Tweak: Improve compatibility with object cache plugins.

= 2.0.4 (2020-04-21) =
* Fixed: Issue on the "caching support" option that could affect to discount plugins.
* Fixed: Geolocation function only returns countries included in the allowed countries option.
* Tweak: New admin notice after update the "caching support" option.
* Dev: new filter wc_price_based_country_free_shipping_exchange_rate.

= 2.0.3 (2020-04-16) =
* Fixed: Bug on WooCommerce Admin (Analytics) query.
* Fixed: Compatibility issues with WooCommerce Dynamic Pricing & Discounts by RightPress.

= 2.0.2 (2020-04-11) =
* Fixed: Bug reading the "sale date from" and "sale date to" fields when the value is empty.
* Fixed: Bug calculating the coupon amount by the exchange rate.
* Fixed: The geolocation test fails when the external IP is a IP6.
* Tweak: Display all countries of the pricing zones in the country switcher widget.
* Dev: new filter wc_price_based_country_allow_all_countries.

= 2.0.1 (2020-04-09) =
* Fixed: PHP error - undefined function wcpbc_get_overwrite_meta_keys in the "WPML" integration.

= 2.0.0 (2020-04-09) =
* Required WooCommerce 3.4 or higher.
* Added: Compatibility with "WooCommerce Admin".
* Added: Compatibility with "WooCommerce Blocks".
* Added: Improvements in the product price synchronization: DB updates in the background. No more timeout errors.
* Added: Remove the frontend JavaScript that refreshes the minicart on country switcher change.
* Added: Replace the font spinner by a pure CSS spinner (to improve the page load time).
* Added: Cache the AJAX geolocation response when the site is using a object cache.
* Fixed: Force mini cart refresh when the pricing zone change by extending the cart hash.
* Fixed: Refresh the minicart on checkout page when the country change.
* Fixed: The product shortcode cache does not store a value by pricing zone.

[See changelog for all versions](https://plugins.svn.wordpress.org/woocommerce-product-price-based-on-countries/trunk/changelog.txt).

== Upgrade Notice ==

= 2.0 =
<strong>2.0 is a major update</strong>, make a backup before updating. 2.0 requires WooCommerce 3.4 or higher. If you are using the <strong>Pro version</strong>, you must update it to the <strong>latest version</strong>.