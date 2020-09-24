=== Customer Specific Pricing for WooCommerce ===

Current Version: 4.4.4

Author:  WisdmLabs

Author URI: https://wisdmlabs.com/

Plugin URI: https://wisdmlabs.com/woocommerce-user-specific-pricing-extension/

Tags: WooCommerce pricing add on, customer based pricing WooCommerce, per customer pricing WooCommerce

Requires at least: 4.2

Tested up to: 5.3.2

WC Requires at least: 3.0.0

WC Tested up to: 4.0.1

Stable tag: 4.4.4

License: GNU General Public License v2 or later

== Description ==

The Customer Specific Pricing for WooCommerce Plugin allows the store owner, to set specific product prices for individual customers, user roles, or groups. In case a price is not set for a customer, the default price of the product will be applied.


== Installation ==

Important: This plugin is a premium extension for the WooCommerce plugin. You must have the WooCommerce plugin already installed.

= Minimum Requirements =

* WordPress 4.2 or greater
* PHP version 5.6 or greater
* MySQL version 5.0 or greater

= Manual installation =

1. Upon purchasing the Customer Specific Pricing for WooCommerce, an email will be sent to the registered email id, with the download link for the plugin and a purchase receipt id. Download the Customer Specific Pricing plugin using the download link.

2. Go to Plugin-> Add New menu in your dashboard and click on the Upload' tab. Choose the 'customer-specific-pricing-for-woocommerce.zip' file to be uploaded and click on Install Now.

3. After the plugin has installed successfully, click on the Activate Plugin link or activate the Customer Specific Pricing plugin from your Plugins page.

4. A CSP License sub-menu will be created under Plugins menu in your dashboard. Click on this menu and enter your purchased product's license key. Click on Activate License. If license is valid, an 'Active' status message will be displayed, else 'Not Active' will be displayed.

5. Upon entering a valid license key, and activating the license, a 'Customer Specific Pricing' tab will be created under the 'Product Data' section for every single Product settings page in your dashboard.


== Change Log ==
= 4.4.3 =
* Fix           - Back end Order calculation issue when taxation is enabled.

= 4.4.3 =
* Feature       - Added a hook to enable importing CSP rules for all the products having the same SKU.
* Feature       - Added a hook that will allow applying user-specific global discounts/prices.
* Improvement   - Improved the load time of archive pages.
* Improvement   - Improvement in CSP API.
* Fix           - Category pricing data display issue (Duplicate entries displayed on search by page)
* Fix           - Deletion of a customer-specific rule log entry on the deletion of a subrule
* Fix           - Issue while saving the page having special offers shortcode
* Fix           - "null" is being displayed as a suffix for variable products when there's no suffix

= 4.4.2 =
* Fix           - Pricing rules issue for categories.

= 4.4.1 =
* Fix           - Fatal error on user deletion.
* Tweak         - Compatibility Tested with WC 3.9.1

= 4.4.0 =
* Feature       - API to manage CSP prices.
* Feature       - Allowing a 100% discount.
* Feature       - User Specific CSP Archive Page.
* Improvement   - An Option to disable category pricing feature.
* Improvement   - Changes in admin UI of category pricing.
* Tweak         - Switched the position of the category pricing tab & search by & delete tab
* Fix           - Warning On WP-Admin When Expired License is present.
* Fix           - Deletion from search by & delete not working when multiple groups are assigned to the user.

= 4.3.5 =
* Improvement   - Performance optimization.
* Improvement   - Admin page UI/UX changes.
* Fix           - Minor Bug Fixes.

= 4.3.4 =
* Fix           - Warning message on admin panels of some sites due to custom menu order.

= 4.3.3 =
* Feature       - Compatibility Testing with WC 3.8 & WP 5.3
* Improvement   - UI/UX on CSP "Seach by & Delete" admin page.
* Improvement   - Position of CSP Admin menu has been put closed to products menu.
* Tweak         - Added a hook to change the redirection after adding the product to the cart.
* Tweak         - Added a filter to disable the file type check.
* Fix           - Style conflict on the product pricing tab (CSP Settings Page) after WordPress 5.3 update.
* Fix           - UX issue preventing the addition of the new rule on the product page. (issue with the plugin all in one wp security) 
* Fix           - the notice shown while adding a product to the cart.

= 4.3.2 =
* Feature - Compatibility Testing with WC 3.7.1 & WP 5.2.4
* Tweak   - Improvement in Import/Export Page.
* Fix     - Issue with the mini cart product multiplier.
* Fix     - Issue when the sale price is stored as the comma separated value in database.
* Fix     - Regular price text is also visible on the products without CSP.

= 4.3.1 =
* Fix     - Minor Translation Issues


= 4.3.0 =
* Feature - Cart total based discounts.
* Feature - Progress status on the import page & report download option. 
* Feature - Show actual price above the pricing table.
* Feature - Label for price description table.
* Tweak   - Compatibility with Product Tables.
* Fix     - Moved the migration process in previous implementations to the upgrade hook from activation hook.

= 4.2.4 =
* Feature - Setting to hide product total.
* Fix - CSP prices are not applicable when editing the saved manual order.
* Fix - CSP not working as per the specified priorities for the role.
* Fix - Role-based pricing rules reset when 'shop manager' edits the product.
* Fix - Display issue due to loose targeting [Varaiable products quantity field].
* Fix - Issue with the manual orders when an Older Version of the WooCommerce is installed.
* Fix - When the customer is logged in, products that are out of stock is not shown out of stock.

= 4.2.3 =
* Feature - Added Compatibility with WordPress network setup (multisite).
* Feature - Enabled to specify “Customer Specific Price” without specifying the “Regular Price”.
* Feature - Enabled to apply “Customer Specific Price” on “Sale Price”.
* Feature - Feedback tab on CSPs setting page.
* Fix - High server resource consumption during bulk import fixed.
* Improvement - Information tooltips with messages while keeping qty field empty in csp rules.

= 4.2.2 =
Fix: Issue with the JS code for Internet Explorer 11 and prior versions due to which prices for variable products were being displayed wrong.
Improvement: Compatibility with Wisdm Product Enquiry Pro plugin for Hiding the Prices for Non-logged in users.

= 4.2.1 =
Fix - Issue on WooCommerce order edit page.
Fix - Issue related to CSP price getting in the email after an order is placed or completed.

= 4.2.0 =
* Feature - Added capability to import/export the CSP by SKU.
* Feature - Added capability to delete the CSP price from the 'Search By & Delete' tab.
* Feature - Added strikethrough functionality for CSP price.
* Fix - Issue with WooCommerce backend order.

= Beta 4.2.0 =
* Fix - WooCommerce Manual orders with coupons.

= 4.1.3 =
* Tweak - New license code.

= 4.1.2 =
* Fix - While viewing order from dashboard the price was displayed 1.
* Fix - The Order details show NAN as the price.
* Fix - On shop page the CSP prices were displayed incorrectly if the product is already in cart.
* Fix - Category Pricing tab showing duplicate records.
* Fix - The qty can be edited on edit rule.

= 4.1.1 =
* Fix - Resolved issue with edit rule when editing/updating records.
* Fix - Dashboard orders for single quantity.
* Fix - Exporting records issue.

= 4.1.0 =
* Feature - Category Based Pricing .
* Feature - Category based pricing option included in the search results under ‘Search By’ tab.
* Tweak - Replaced placeholder for minimum quantity with by adding default minimum quantity for prices.
* Tweak - ‘Pricing Manager’ tab renamed as ‘Product Pricing’.
* Tweak - ‘Search By’ option moved to the main tab of the plugin settings.
* Fix - Resolved issue for restoring set prices when editing price rules.

= 4.0.1 =
* Feature - Added Compatibility with WooCommerce 2.7
* Fix - Updated license code.
* Fix - Quantity based pricing display.
* Fix - Added Compatibility with WooCommerce's Tax settings
* Fix - Remove all group specific pricing records of a variable product.
* Fix - Update quantity deletes all records of same user and min quantity.
* Fix - Creating Order from Dashboard not working

= 4.0.0 =
* Feature - Set quantity based pricing from product edit page and pricing manager.
* Feature - Set quantity based pricing through CSV import.
* Feature - Show quantity wise pricing table on single product page.
* Fix - Variable product price validations on click of save changes button.
* Fix - Accept price according to woocommerce currency options.

= 3.1.2 =
* Tweak - Improvements in the import functionality. Load time reduced.
* Feature - Apply same rule for multiple customers, groups and roles.
* Fix - Validation for discount type.

= 3.1.1 =
* Fix - Fixed the table structure of rule log screen.
* Fix - Showing 'Discount Type' text in one line on single product page.

= 3.1.0 =
* Tweak - Replaced the add new pair button with plus icon.
* Feature - Added a feature to set percentage discount.

= 3.0.3 =
* Made the Plugin Translation Ready.
* Tweak - Changed the layout of single view.
* Fix - Improved the security for import and export feature.
* Fix - The CSP main menu page was not getting displayed due to licensing.
* Fix - The warning message was displayed late while deleting a rule log without selecting the rule log.
* Fix - From edit product page the price Zero was not getting set.
* Fix - The CSP price pairs were not getting deleted for variable products when all records removed for particular variation.
* Fix - The attributes for variable product were not getting displayed when generalised.

= 3.0.2 =
* Licensing code updated.

= 3.0.1 =
* Fixed Compatibility with php lower than 5.5. Now plugin is compatible from php 5.3 onwards.

= 3.0.0 =
* Added Pricing Manager which allows admin to set pricing for multiple products on single page.
* Added cleanup procedure to clean up the unwanted data on activation of the plugin
* Removing CSP related data when user or group is deleted.
* Combined wusp_user_mapping and wusp_pricing_mapping tables and created wusp_user_pricing_mapping to improve performance optimization.
* Made PSR 2 Compatible

= 2.1.1 =
* CSP prices applicable with order creation from backend
* Save prices with Save Changes button
* Compatible with PHP version less than 5.4
* Compatible with WooCommerce 2.4.7 and WordPress 4.3.1

= 2.1 =
* Import/Export feature added.

= 2.0.2 =
* Compatible with WordPress 4.2.3
* Compatible with WooCommerce 2.4.4

= 2.0.1 =
* Licensing error fixed
* Pricing error with decimal values fixed

= 2.0.0 =
* User Role Specific Pricing feature added
* Group Specific Pricing feature added

= 1.2.2 =
* Bug Fixes
* Compatible with WooCommerce 2.3.5

= 1.2.1 = 
* Resolved mysqli_warning while saving meta
* Wrapped required variables inside isset
* Removed printing arrays
* Changed License Year
* Made compatible with latest WooCommerce Version i.e. WooCommerce 2.3.3

= 1.2.0 =
* Plugin upgraded to work with variable products.

= 1.0.1 =
* Modified the Plugin upgrade flow.

= 1.0.0 =
* Plugin Released


== Frequently Asked Questions ==

= Help! I lost my license key? =
In case you have misplaced your purchased product license key, kindly go back and retrieve your purchase receipt id from your mailbox. Use this receipt id to make a support request to retrieve your license key.

= How do I contact you for support? =
You can direct your support request to us, using the Support form on our website.

= What will happen if my license expires? =
Every purchased license is valid for one year from the date of purchase. During this time you will recieve free updates and support. If the license expires, you will still be able to use CSP, but you will not recieve any support or updates.

= Do you have a refund policy? =
Yes. Refunds will be provided under the following conditions: 
-Refunds will be granted only if CSP does not work on your site and has integration issues, which we are unable to fix, even after support requests have been made.
-Refunds will not be granted if you have no valid reason to discontinue using the plugin. CSP only guarantees compatibility with the
WooCommerce plugin.
-Refund requests will be rejected for reasons of incompatibility with third party plugins.

= I have activated plugin but I still do not see an option to add pricing for users. What to do? =
Make sure that you have entered license key for the product. To do so, you can go to 'CSP License' page found as a submenu under 'Plugins' menu. Once you have activated the license successfully, you will be able to add pricing for users by going to Product create/edit page. So you can add pricing on per product basis.

= What kind of users does this plugin support? =
You can add pricing for any user with an account on your website.

= Is there any limit on how many customer-price pairs can be added? =
No, there is no such limit. You can add as many customer-price pairs as you want.

