=== Scheduler for WooCommerce Plugin by Wisdmlabs ===
Contributors: WisdmLabs
Tested up to:  5.3.2
Current Version: 2.3.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This extension plugin allows you to schedule product purchase availability in your WooCommerce store.

Tested with WooCommerce version 4.0.1

== Description ==

The Scheduler for WooCommerce provides store owners an option to set a purchase availability time for products in the store. By entering the start date and end date for a product, you can sell the product in your WooCommerce store, for a particular time duration. Once the time expires, the product will still be available in the store, but you will not be able to purchase it.

== Installation ==

How to install '__Scheduler for WooCommerce Plugin by Wisdmlabs__'

= Plugin Installation and Set Up Steps =
1. Upon purchasing the Scheduler for WooCommerce Plugin, an email will be sent to the registered email id, with the download link for the plugin and a purchase receipt id. Download the plugin using the download link.
2. Go to Plugin-> Add New menu in your dashboard and click on the ‘Upload’ tab. Choose the ‘woocommerce-scheduler.zip’ file to be uploaded and click on ‘Install Now’.
3. After the plugin has been installed successfully, click on the Activate Plugin link or activate the Scheduler for WooCommerce Plugin from your Plugins page.
4. A Scheduler for WooCommerce License sub-menu will be created under Plugins menu in your dashboard. Click on this menu and enter your purchased product’s license key. Click on Activate License. If license is valid, an ‘Active’ status message will be displayed, else ‘Inactive’ will be displayed.
5. Upon entering a valid license key, and activating the license, product display date settings, 'Start Date' and 'End Date' will be added in every WooCommerce product add/edit admin settings page. 
6. During the Start Date- End Date period, the product will be available for purchase in your WooCommerce store.
7. Before the Start Date, and after the End Date, the product will still be available in your shop, but the 'Add-to-Cart' button will be disabled.
8. To display a product unavailability message, go to WooCommerce -> Settings -> General and enter a value for 'Product Expiration Message'
9. To schedule, go to Products -> Schedule Products and schedule based on product/ category.

== Features ==
1. Works with Simple and Variable WooCommerce Product Types 
2. Customizable Product Purchase Unavailability Message
3. Single page to schedule products from dashboard.
4. Schedule products as well as categories
5. Schedule multiple products or categories at once.
6. Display timer for availability of products on front-end
7. Intuitive Interface
8. Fallback Support
9. Easy to Use

== Change Log ==
== 2.3.5 == (20/04/2020)
* Fix - Products with the custom product types are getting drafted while publishing.
* Fix - Other minor bug fixes.
* Tweak - Removed the external framework used & added custom settings pages
* Improvement - Included a shortcode to show the timer on the elementor custom product page.

== 2.3.4 == (26/01/2020)
* Fix - Hiding a variable product when all of its variations are hidden.
* Fix - When a product is hidden & schedule gets deleted the product stays hidden on the shop page.
* Improvement - A hook to prevent hiding a parent variable product when all of its variations are hidden.


== 2.3.3 == (08/01/2020)
* Fix - Issue of posts getting drafted after WP 5.3 release.
* Fix - UI issues due to the compatibility with WP 5.3.
* Fix - WooCommerce inactive issue on the multisite.
* Improvement - Minor UI improvements.

== 2.3.2 ==
* Fix - Issue with "Hide when unavailable" feature for product variations
* Fix - The default text is always shown as an expiry message.
* Improvement - Performance Improvements
* Tweak - Timer texts are now translatable "Days","Hrs","Mins","Secs".

== 2.3.1 ==
* Fix - Shop page loading delay.
* Fix - Migration issue while updating the plugin.

= 2.3.0 - 2019/04/25 =
* Fix - Automatic Publishing of the product after clicking Add New and immediately closing the page.
* Fix - Hide if unavailable option saving even when the product is not scheduled.
* Fix - Add To Cart Button gets hidden during the very first availability of the simple product.
* Fix - Crons for the variants not listed in current view get deleted while updating the variable product.
* Fix - Notification Modal Popup - page refresh on pressing enter.
* Fix - Sorting & Searching In Enrollment Count Page.
* Tweak - Compatibility with the commonly used plugin Advanced Custom Fields: date-time Pickers weren't working when ACF active.
* Feature - Hide When Unavailable for category scheduling &  Product bulk scheduling.
* Feature - Schedule types are moved to the product level from global settings (You can now use both per day & Entire Duration simultaneously in their new form.)
* Feature - New schedule type Product Launch is added for product & product variations.
* Feature - New Schedule Type Introduced for the Entire Duration named Whole Day, New Schedule Type Introduced for the Per Day named Specific Time On The Selected Days
* Feature - Skip Duration and the Weekday selection Options for both schedule types.  
* Improvement - Feedback Tab and modal

= 2.2.2 =
* Fix - Solved the issue of schedules getting completed before time. i.e; UTC - (x) Timezones.

= 2.2.1 =
* Fix - Fixed the issue of not setting the notification cron from the product edit page.
* Fix - Made the product scheduling start time and end time fields mandatory on product edit page to prevent the issues which were getting generated at the time of setting the notification cron.
* Fix - Email templates not visible in the email settings.
* Fix - Error on schedules for emoty start/end time.

= 2.2.0 =
* Feature - Notify User Feature

= 2.1.3 =
* Tweak - New license code.

= 2.1.2 =
* Fix: Timer issue fixed on Safari browser for iOS

= 2.1.1 =
* Feature - Option to clear the schedule data from a product edit page.
* Improvement - Updated licensing code.
* Improvement - Compatibility with latest woocommerce.
* Improvement - Compatibility with PHP 7+

= 2.1.0 =
* Feature - Added Timer feature on single product pages.
* Improvement - Moved the scheduler settings to a new dashboard menu "Scheduler for WooCommerce" and added two tabs General & Global, in the settings page.
* Improvement - Changed the 24 Hours clock to 12 Hours clock.
* Improvement -  Added  a warning message on days selection section. This warning will be displayed only if the selected days does not fall within the selected range of dates.
* Fix - Added an unavailable warning message for variable product if all variations are unavailable.

= 2.0.4 =
* Fix - Hide if unavailable was not preserved after updating.
* Improvement - Note on save changes button to update the product.
* Improvement - Compatibility with WooCommerce 3.0.x.
* Improvement - Integrated updated licensing code.

= 2.0.3 =
* Fix - Issue in setting new product as Draft fixed

= 2.0.2 =
* Tweak - Compatibility with Customer Specific Pricing plugin

= 2.0.1 =
* Fix - Variable Product Day’s selection issue solved
* Fix - Hide custom message notice for non admin user
* Fix - On plugin activation days were not being shown for per day option
* Fix - Custom message on product expiration for Variable Product

= 2.0.0 =
Hide Unavailable Products
Schedule variations feature available
New Licensing code added

= 1.4.2 =
Resolved issues with the plugin license activation

= 1.4.1 =
Plugin code made PSR2 compatible
Resolved issues with year and mandatory field

= 1.4.0 =
*Single page to schedule products from dashboard.
*Multiple products can be scheduled at once.
*Products can be scheduled based on categories
*Multiple categories can be scheduled at once.
*Table view to display multiple schedules for products and categories and to be able to delete them.

= 1.3.0 =
*Feature to shedule the product, for selective days in a week, under 'Per day' scheduling option
*Feature to display separate product expiration messages on the shop page and the single product page

= 1.2.1 =
*Labels changed

= 1.2 =
*Added functionality for both per day as well as entire day scheduling
*Added filter for changing product expiration message, filter name is 'wdm_expiration_message'.


= 1.1.4 =
*Restricted add-to-cart functionality for a product if product is expired

= 1.1.3 =
* Solved the issue for the 'Add to Cart' button enabled when product is unavailable

= 1.1.2 =
*Solved the product expiration message update issue

= 1.1.1 =
*Solved scheduling issue on single product page

= 1.1.0 =
*Feature to schedule product based on time along with date

= 1.0.0 =
*Plugin Released


