<?php
/**
 * Handle integration with Listing Payments for WP Job Manager.
 *
 * @see https://astoundify.com/products/wp-job-manager-listing-payments/
 * @version 2.6.4
 * @package WCPBC
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WCPBC_Job_Package' ) ) :

	/**
	 *
	 * WCPBC_Job_Package Class
	 */
	class WCPBC_Job_Package {

		/**
		 * Hook actions and filters
		 */
		public static function init() {
			add_action( 'woocommerce_product_options_general_product_data', array( __CLASS__, 'product_data_options' ), 5 );
			add_action( 'woocommerce_process_product_meta_job_package', array( 'WCPBC_Admin_Meta_Boxes', 'process_product_meta' ) );
			add_filter( 'wc_price_based_country_product_types_overriden', array( __CLASS__, 'product_types_overriden' ) );
		}

		/**
		 * Adds show_if_job_package class to the pricing div.
		 *
		 * @since 2.0.0
		 */
		public static function product_data_options() {
			?>
			<script type="text/javascript">
				jQuery( function($) {
					$( 'div.options_group.wcpbc_pricing' ).addClass( 'show_if_job_package' );
				} );
			</script>
			<?php
		}

		/**
		 * Add job_package product type to the handled product types.
		 *
		 * @param array $types Array of product types.
		 */
		public static function product_types_overriden( $types ) {
			array_push( $types, 'job_package' );
			return $types;
		}
	}

endif;

WCPBC_Job_Package::init();
