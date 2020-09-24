<?php

namespace CSPAPI\Includes\API;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

abstract class CspMappings extends \WC_REST_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'wc/v3';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'csp-mappings';

	/**
	 * Post type.
	 *
	 * @var string
	 */
	protected $post_type = 'product';
	
	protected function __construct() {
		add_action('rest_api_init', array($this, 'registerRoutes'), 20);
	}

	/**
	 * Generate response to sent as an API response.
	 *
	 * @param   array   $response   Array containing data using which response
	 *                              would be generated.
	 * @param   WP_REST_Request $request Full details about the request.
	 *
	 * @return  WP_REST_Response
	 */
	public function generateResponseData( $response, $request) {
		$wpRestResponse = new \WP_REST_Response();
		$response       = apply_filters('cspapi_modify_response', $response, $request);

		$wpRestResponse->set_data(
			$response
		);
		
		return $wpRestResponse;
	}

	/**
	 * Check if a given request has access to create an item.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function create_item_permissions_check( $request) {
		// @codingStandardsIgnoreLine.
		if ( ! wc_rest_check_post_permissions('product', 'create')) {
			return new \WP_Error('woocommerce_rest_cannot_create', __('Sorry, you are not allowed to create/ add customer specific pricing.', 'customer-specific-pricing-for-woocommerce'), array('status' => rest_authorization_required_code()));
		}
		
		unset($request);
		return true;
	}

	/**
	 * Check if a given request has access to read an item.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function get_items_permissions_check( $request) {
		// @codingStandardsIgnoreLine.
		if (! wc_rest_check_post_permissions($this->post_type, 'read')) {
			return new \WP_Error('cspapi_rest_cannot_view', __('Sorry, you cannot list resources.', 'customer-specific-pricing-for-woocommerce'), array('status' => rest_authorization_required_code()));
		}

		unset($request);
		return true;
	}

	/**
	 * Check if a given request has access to read an item.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function get_item_permissions_check( $productId) {
		// @codingStandardsIgnoreLine.
		if (! wc_rest_check_post_permissions('product', 'read', $productId)) {
			return new \WP_Error('cspapi_rest_cannot_view', __('Sorry, you cannot list resources.', 'customer-specific-pricing-for-woocommerce'), array('status' => rest_authorization_required_code()));
		}

		return true;
	}

	/**
	 * Check if a given request has access to delete items.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return bool|WP_Error
	 */
	public function delete_items_permissions_check( $request) {
		// @codingStandardsIgnoreLine.
		if (! wc_rest_check_post_permissions($this->post_type, 'delete')) {
			return new \WP_Error('cspapi_rest_cannot_delete', __('Sorry, you are not allowed to delete these resources.', 'customer-specific-pricing-for-woocommerce'), array('status' => rest_authorization_required_code()));
		}

		unset($request);
		return true;
	}

	/**
	 * Check if a given request has access to delete an item.
	 *
	 * @param  int  $productId  Product Id for which CSP needs to be deleted.
	 * @return bool|WP_Error
	 */
	public function delete_item_permissions_check( $productId) {
		// @codingStandardsIgnoreLine.
		$post = get_post($productId);

		if ($post && ! wc_rest_check_post_permissions('product', 'delete', $post->ID)) {
			return new \WP_Error('cspapi_rest_cannot_delete', __('Sorry, you are not allowed to delete this resource.', 'customer-specific-pricing-for-woocommerce'), array('status' => rest_authorization_required_code()));
		}

		return true;
	}
	
	/**
	 * Returns the failed message for the provided key.
	 *
	 * @param   string  $key    Key for which message should be returned.
	 * @param   array   $item   Array containing data for which request is failed.
	 *
	 * @return  string
	 */
	public function getFailedMessage( $key, $item = array()) {
		$failedMessages = array(
			'hash-invalid'      => __('Hash is not valid.', 'customer-specific-pricing-for-woocommerce'),
			'csp-exists'        => __('Customer mapping already exists.', 'customer-specific-pricing-for-woocommerce'),
			'gsp-exists'        => __('Group mapping already exists.', 'customer-specific-pricing-for-woocommerce'),
			'rsp-exists'        => __('Role mapping already exists.', 'customer-specific-pricing-for-woocommerce'),
			'user-invalid'      => __('Not a valid user.', 'customer-specific-pricing-for-woocommerce'),
			'group-invalid'     => __('Not a valid group.', 'customer-specific-pricing-for-woocommerce'),
			'role-invalid'      => __('Not a valid Role.', 'customer-specific-pricing-for-woocommerce'),
			'product-invalid'   => __('Not a valid product.', 'customer-specific-pricing-for-woocommerce'),
			'csp-not-exists'    => __('Pairing doesn\'t exists.', 'customer-specific-pricing-for-woocommerce'),
			'delete-unauth'     => __('Sorry, you are not allowed to delete this resource.', 'customer-specific-pricing-for-woocommerce'),
			'view-unauth'       => __('Sorry, you are not allowed to view this resource.', 'customer-specific-pricing-for-woocommerce'),
			'csp-data-invalid'  => __('Not a valid set of data.', 'customer-specific-pricing-for-woocommerce'),
			'discount-invalid'  => __('Invalid Discount Value', 'customer-specific-pricing-for-woocommerce'),
		);

		return apply_filters('cspapi_failed_message', $failedMessages[$key], $key, $item);
	}

	/**
	 * Returns the success message for the provided key.
	 *
	 * @param   string  $key    Key for which message should be returned.
	 * @param   array   $item   Array containing data for which request is succeeded.
	 *
	 * @return  string
	 */
	public function getSuccessMessage( $key, $item) {
		$successMessage = array(
			'csp-updated'    => __('Record updated.', 'customer-specific-pricing-for-woocommerce'),
			'csp-added'      => __('Record added.', 'customer-specific-pricing-for-woocommerce'),
		);

		return apply_filters('cspapi_success_message', $successMessage[$key], $key, $item);
	}
}
