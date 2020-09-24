=== Sequential Order Number for WooCommerce ===
Contributors: webtoffee
Donate link: https://www.webtoffee.com/plugins/
Tags: sequential order number, woocommerce order number, woocommerce sequential order number, woocommerce custom order number, advanced order number
Requires at least: 3.0.1
Tested up to: 5.5
Stable tag: 1.2.3
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Generates Sequential Order Number for WooCommerce Orders

== Description ==

WordPress uses an ID system for posts, pages, and media files. WooCommerce uses the same ID for order number too. When a new WooCommerce order is created it may not always get the next order in sequence as the ID may have already been used up by another post or page. Using this plugin, you will always get sequential order number for woocommerce.

The plugin currently doesn't support Subscription orders.

If you have no orders in your store, your orders will start counting from order number 1. If you have existing orders, the order number will pick up from your highest order number.

If you like to make your other plugin ( invoice / payment ) compatible with Sequential Order Numbers for WooCommerce, please make below tweak. Instead of referencing $order->id  or $order->get_id() when fetching order data, use $order->get_order_number()

<ul>
&#9989; Tested OK with WooCommerce 4.3.3.
</ul>

== Need of Sequential Order Numbers ==

Usually, an eCommerce store receives thousands of orders each day. Each of these orders has to be recorded for the smooth functioning of the store and for any future references of the same. The more sorted the data, the easier would be its management. Sorting of orders would be an easy task if order numbers were in a sequence.

Sequencing of order numbers helps solve the majority of issues related to orders. A disciplined number system has its advantages in improving the efficiency of the store and the pace of its transactions. Therefore enabling your store to generate sequential order numbers is a must for the management of your orders.

== Benefits of Sequential Order Numbers ==

Makes store management easy - Using sequential ordering helps make store management easy and flexible. Order numbers being in a sequence helps easily estimate the orders received each day hence making order management easy for the store.

Helps you to find and track orders fast - If you have a huge WooCommerce store with orders pouring in each day. Tracking of a particular order is going to be a tiresome task. Thus by assigning a unique identity to each order, it gets easy to track or find a particular order among thousands of orders.

Effortless estimation of the number of orders received - When order numbers are given in a sequence of natural numbers or alphabets it becomes easy to estimate the number of orders in your store within seconds.

Easier recording of orders - Sequential numbering helps to record orders easily. When random numbers were given for orders store owner had a hard time keeping a record of the orders.  


= About WebToffee.com =

<a rel="nofollow" href="https://www.webtoffee.com/">WebToffee</a> creates quality WordPress/WooCommerce plugins that are easy to use and customize. We are proud to have thousands of customers actively using our plugins across the globe.


== Installation ==

This section describes how to install the Sequential Order Number for WooCommerce plugin and get it working.

1. Upload the plugin file to the /wp-content/plugins/ directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= Can I do custom formating for the Order Number? =

Yes, you can set custom prefix and start number.


== Screenshots ==


== Changelog ==

= 1.2.3 =
 * WooCommerce 4.3.3 Tested OK

= 1.2.2 =
 * WooCommerce 4.3.1 Tested OK

= 1.2.1 =
 * WooCommerce 4.0.1 Tested OK
 * WordPress 5.4 Tested OK

= 1.2.0 =
 * WooCommerce 4.0.0 Tested OK

= 1.1.9 =
* Feedback Capture Improvement.

= 1.1.8 =
* Security update.

= 1.1.7 =
* Tested OK with WP 5.4 Beta and  WooCommerce 3.9.1

= 1.1.6 =
* Tested OK with WP 5.3 and  WooCommerce 3.8.1

= 1.1.5 =
* WC tested OK with 3.7.1. 

= 1.1.4 =
* WC tested OK with 3.7.0. 

= 1.1.3 =
* Bug fix with last update. 

= 1.1.2 =
* Introduced Settings 
* Custom Start Number
* Custom Prefix

= 1.1.1 =
* Tested OK with WP 5.2 and  WooCommerce 3.6.5

= 1.1.0 =
* Tested OK with WP 5.1.1 and  WooCommerce 3.5.7

= 1.0.9 =
* Tested OK with WP 5.0.3 and  WooCommerce 3.5.3

= 1.0.8 =
* Content updates.

= 1.0.7 =
* Content updates.

= 1.0.6 =
* Tested OK with WooCommerce 3.5.1

= 1.0.5 =
* WC Tested OK with 3.4.5.

= 1.0.4 =
* Optimization.

= 1.0.3 =
* Minor content changes.

= 1.0.2 =
* Readme content updates.

= 1.0.1 =
* Fixed issue with dashboard order search functionality.

= 1.0.0 =
* Initial commit.

== Upgrade Notice ==

= 1.2.3 =
 * WooCommerce 4.3.3 Tested OK
