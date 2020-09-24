<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       univariety.com
 * @since      1.0.0
 *
 * @package    Univariety_opencart_to_woocommerce_migration
 * @subpackage Univariety_opencart_to_woocommerce_migration/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Univariety_opencart_to_woocommerce_migration
 * @subpackage Univariety_opencart_to_woocommerce_migration/includes
 * @author     univariety <univariety@gmail.com>
 */
class Univariety_opencart_to_woocommerce_migration_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'univariety_opencart_to_woocommerce_migration',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
