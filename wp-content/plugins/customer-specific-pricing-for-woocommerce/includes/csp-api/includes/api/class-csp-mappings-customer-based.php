<?php

namespace CSPAPI\Includes\API;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class CspMappingsCustomerBased extends CspMappings {

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
	protected $rest_base = 'csp/usp/';

	/**
	 * Single instance of the class.
	 *
	 * @var Object
	 */
	private static $instance = null;

	/**
	 * Post type.
	 *
	 * @var string
	 */
	protected $post_type = 'product';

	/**
	 * Returns the single instance of the class.
	 */
	public static function getInstance() {
		if (null == static::$instance) {
			static::$instance = new self();
		}
		return static::$instance;
	}
	
	protected function __construct() {
		parent::__construct();
	}

	public function registerRoutes() {
		// DELETE - Delete customer specific pricing (/wp-json/wc/v3/csp-mappings)
		// POST   - Add/ update customer specific pricing (/wp-json/wc/v3/csp-mappings)
		register_rest_route(
			$this->namespace, '/' . $this->rest_base, // @codingStandardsIgnoreLine.
			array(
				array(
					'methods'             => \WP_REST_Server::DELETABLE,
					'callback'            => array($this, 'deleteCsp'),
					//'permission_callback' => array($this, 'delete_items_permissions_check'),
				),
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array($this, 'addCsp'),
					'permission_callback' => array($this, 'create_item_permissions_check'),
				),
			)
		);

		// GET - Retrieve customer specific pricing for a user by user email.
		// (/wp-json/wc/v3/csp-mappings/user/email/user_email@example.com).
		register_rest_route(
			$this->namespace, '/' . $this->rest_base . 'email/(?P<customer_email>[\w.]+\@[.\w]+)', // @codingStandardsIgnoreLine.
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'getCspForUserByEmail' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
				),
			)
		);

		// GET - Retrieve customer specific pricing for a user by user Id.
		// (/wp-json/wc/v3/csp-mappings/user/1).
		register_rest_route(
			$this->namespace, '/' . $this->rest_base . 'user-id/(?P<customer_id>[\d]+)', // @codingStandardsIgnoreLine.
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'getCspForUserById' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
				),
			)
		);

		// GET - Retrieve customer specific pricing for a user (using user email) for a particular product.
		// (/wp-json/wc/v3/csp-mappings/user/email/user_email@example.com/product/16).
		register_rest_route(
			$this->namespace, '/' . $this->rest_base . 'product-id/(?P<product_id>[\d]+)/email/(?P<customer_email>[\w.]+\@[.\w]+)', // @codingStandardsIgnoreLine.
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'getCspForUserProductByEmail' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
				),
			)
		);

		// GET - Retrieve customer specific pricing for a user (using user Id) for a particular product.
		// (/wp-json/wc/v3/csp-mappings/user/id/12/product/16).
		register_rest_route(
			$this->namespace, '/' . $this->rest_base . 'product-id/(?P<product_id>[\d]+)/user-id/(?P<customer_id>[\d]+)', // @codingStandardsIgnoreLine.
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'getCspForUserProductById' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
				),
			)
		);

		// GET - Retrieve all pricing mappings of a specific product.
		// (/wp-json/wc/v3/csp-mappings/product/123).
		register_rest_route(
			$this->namespace, '/' . $this->rest_base . 'product-id/(?P<product_id>[\d]+)', // @codingStandardsIgnoreLine.
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'getCspForProduct' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
				),
			)
		);
	}

	/**
	 * Add a Customer specific pricing.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function addCsp( $request) {
		$items        = array_filter($request->get_params());
		$response     = array();
		$existingData = array();
		if (isset($items['csp_data'])) {
			foreach ($items['csp_data'] as $item) {
				$productId    = cspapiGetDefaultCspData($item, 'product_id');
				$customerId   = cspapiGetDefaultCspData($item, 'customer_id');
				$minQty       = cspapiGetDefaultCspData($item, 'min_qty');
				$discountType = cspapiGetDefaultCspData($item, 'discount_type');
				$price        = cspapiGetDefaultCspData($item, 'csp_price');
				$clientHash   = cspapiGetDefaultCspData($item, 'hash');
	
				if (empty($customerId) || empty(get_userdata($customerId))) {
					$response['failed'][$clientHash] = $this->getFailedMessage('user-invalid', $item);
					continue;
				}

				if ( ( '%'==$discountType && 100<$price ) || ( 0>$price ) ) {
					$response['failed'][$clientHash] = $this->getFailedMessage('discount-invalid', $item);
					continue;
				}
	
				// Generate hash.
				$hash = cspapiGenerateProductHashByEntity($productId, $customerId, $minQty, $discountType, $price);
				
				if (!cspapiIsAPIHashValid($clientHash, $hash, $item)) {
					$response['failed'][$clientHash] = $this->getFailedMessage('hash-invalid', $item);
				} elseif (!cspapiIsValidProduct($productId)) {
					$response['failed'][$clientHash] = $this->getFailedMessage('product-invalid', $item);
				} else {
					$existingData = $this->isCustomerMappingExists($productId, $customerId, $minQty);
					if ($existingData) {
						// 1 for flat and 2 for %.
						$discountType = ( 'flat' == $discountType ) ? 1 : 2;
						if (!cspapiIsSubruleExists($customerId, $productId, $minQty, $discountType) && ( $price != $existingData['price'] || $discountType != $existingData['discount_type'] )) {
							// update the CSP record.
							\WdmCSP\WdmWuspUpdateDataInDB::updateUserPricingInDb($existingData['id'], $price, $discountType, $minQty, $productId);
							$response['success'][$clientHash] = $this->getSuccessMessage('csp-updated', $item);
						} else {
							$response['failed'][$clientHash] = $this->getFailedMessage('csp-exists', $item);
						}
					} else {
						// 1 for flat and 2 for %.
						$discountType = ( 'flat' == $discountType ) ? 1 : 2;
						\WdmCSP\WdmWuspAddDataInDB::insertPricingInDb($customerId, $productId, $discountType, $price, $minQty);
						$response['success'][$clientHash] = $this->getSuccessMessage('csp-added', $item);
					}
				}
			}
		} else {
			$response['failed'] = $this->getFailedMessage('csp-data-invalid');
		}

		$response = $this->generateResponseData($response, $request);

		return apply_filters('cspapi_create_csp_response', $response, $request);
	}

	/**
	 * Remove a Customer specific pricing.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function deleteCsp( $request) {
		global $subruleManager, $ruleManager;
		$items    = array_filter($request->get_params());
		$response = array();
		if (isset($items['csp_data'])) {
	
			foreach ($items['csp_data'] as $item) {
				$productId    = cspapiGetDefaultCspData($item, 'product_id');
				$customerId   = cspapiGetDefaultCspData($item, 'customer_id');
				$minQty       = cspapiGetDefaultCspData($item, 'min_qty');
				$discountType = cspapiGetDefaultCspData($item, 'discount_type');
				$price        = cspapiGetDefaultCspData($item, 'csp_price');
				$clientHash   = cspapiGetDefaultCspData($item, 'hash');

				// Check if user is valid.
				if (empty($customerId) || empty(get_userdata($customerId))) {
					$response['failed'][$clientHash] = $this->getFailedMessage('user-invalid', $item);
					continue;
				}
		
				// Generate hash.
				$hash = cspapiGenerateProductHashByEntity($productId, $customerId, $minQty, $discountType, $price);
				
				$result = $this->delete_item_permissions_check($productId);
				if (!cspapiIsAPIHashValid($clientHash, $hash, $item)) {
					// Check if hash is valid.
					$response['failed'][$clientHash] = $this->getFailedMessage('hash-invalid', $item);
				} elseif (is_wp_error($result)) {
					$response['failed'][$clientHash] = $this->getFailedMessage('delete-unauth', $item);
				} elseif ($this->isCustomerMappingExists($productId, $customerId, $minQty)) {
					\WuspDeleteData\WdmWuspDeleteData::deleteCustomerMapping('user_id', 'wusp_user_pricing_mapping', '%d', 'customer', $customerId, $productId, $minQty);
					//Deactivate subrule for deleted record
					$subruleManager->deactivateSubrulesForCustomersNotInArray($productId, array($customerId), array($minQty));
					$ruleManager->setUnusedRulesAsInactive();
					$response['success'][] = $clientHash;
				} else {
					// If the pariring doesn't exists.
					$response['failed'][$clientHash] = $this->getFailedMessage('csp-not-exists', $item);
				}
			}
		} else {
			$response['failed'] = $this->getFailedMessage('csp-data-invalid');
		}

		$response = $this->generateResponseData($response, $request);
		return apply_filters('cspapi_delete_csp_response', $response, $request);
	}

	/**
	 * Returns a Customer specific pricing applied for user.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @param int             $customerId Customer/ User Id.
	 * @param string          $failedkey Key which will be included in the
	 *                        'failed' section of the response.
	 * @return WP_Error|WP_REST_Response
	 */
	public function getCspForUser( $request, $customerId, $failedkey) {
		$items                 = array_filter($request->get_params());
		$singleProductResponse = false;
		
		if (empty($customerId) || empty(get_userdata($customerId))) {
			$response['failed'][$failedkey] = $this->getFailedMessage('user-invalid', $items);
		} else {
			$cspData  = $this->getCspForUserFromDB($customerId);
			$response = $this->formatCspDataForUserResponse($cspData, $singleProductResponse);
		}

		$response = $this->generateResponseData($response, $request);

		return apply_filters('cspapi_get_csp_for_user_response', $response, $request);
	}

	/**
	 * Returns a Customer specific pricing applied for user by user email.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function getCspForUserByEmail( $request) {
		$items         = array_filter($request->get_params());
		$customerEmail = isset($items['customer_email']) ? $items['customer_email'] : '';
		$customerId    = email_exists($customerEmail);

		// If user email doesn't exist or is invalid.
		if (empty($customerId)) {
			$customerId = 0;
		}

		$response = $this->getCspForUser($request, $customerId, $customerEmail);
		return apply_filters('cspapi_get_csp_for_user_by_email_response', $response, $request);
	}

	/**
	 * Returns a Customer specific pricing applied for user by user Id.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function getCspForUserById( $request) {
		$items      = array_filter($request->get_params());
		$customerId = isset($items['customer_id']) ? (int) $items['customer_id'] : 0;

		$response = $this->getCspForUser($request, $customerId, $customerId);
		return apply_filters('cspapi_get_csp_for_user_by_id_response', $response, $request);
	}

	/**
	 * Returns a Customer specific pricing applied for a user to a product
	 * using user email.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function getCspForUserProductByEmail( $request) {
		$items         = array_filter($request->get_params());
		$customerEmail = isset($items['customer_email']) ? $items['customer_email'] : '';
		$productId     = isset($items['product_id']) ? (int) $items['product_id'] : 0;
		$customerId    = email_exists($customerEmail);

		// If user email doesn't exist or is invalid.
		if (empty($customerId)) {
			$customerId = 0;
		}

		$response = $this->getCspForUserProduct($request, $customerId, $productId, $customerEmail);
		return apply_filters('cspapi_get_csp_for_user_product_by_email_response', $response, $request);
	}

	/**
	 * Returns a Customer specific pricing applied for a user to a product
	 * using user Id.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function getCspForUserProductById( $request) {
		$items      = array_filter($request->get_params());
		$customerId = isset($items['customer_id']) ? (int) $items['customer_id'] : 0;
		$productId  = isset($items['product_id']) ? (int) $items['product_id'] : 0;

		$response = $this->getCspForUserProduct($request, $customerId, $productId, $customerId);
		return apply_filters('cspapi_get_csp_for_user_product_by_id_response', $response, $request);
	}

	/**
	 * Returns a Customer specific pricing applied for a user to a product.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @param int             $customerId Customer Id.
	 * @param int             $productId Product Id.
	 * @param string          $failedkey Key which will be included in the
	 *                        'failed' section of the response.
	 * @return WP_Error|WP_REST_Response
	 */
	public function getCspForUserProduct( $request, $customerId, $productId, $failedKey) {
		$items                 = array_filter($request->get_params());
		$singleProductResponse = true;

		if (empty($customerId) || empty(get_userdata($customerId))) {
			$response['failed'][$failedKey] = $this->getFailedMessage('user-invalid', $items);
		} elseif ($productId <= 0 || !cspapiIsValidProduct($productId)) {
			$response['failed'][$productId] = $this->getFailedMessage('product-invalid', $items);
		} elseif (is_wp_error($this->get_item_permissions_check($productId))) {
			$response['failed'][$failedKey] = $this->getFailedMessage('view-unauth', $items);
		} else {
			$cspData  = $this->getCspForUserFromDB($customerId, $productId);
			$response = $this->formatCspDataForUserResponse($cspData, $singleProductResponse);
		}

		$response = $this->generateResponseData($response, $request);

		return apply_filters('cspapi_get_csp_for_user_product_response', $response, $request);
	}

	/**
	 * Returns a Customer specific pricing applied to a product.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function getCspForProduct( $request) {
		$items          = array_filter($request->get_params());
		$productId      = isset($items['product_id']) ? (int) $items['product_id'] : 0;
		$isProductValid = cspapiIsValidProduct($productId);

		if ($isProductValid) {
			$cspData  = \WdmCSP\WdmWuspGetData::getAllPricesForSingleProduct($productId);
			$response = $this->formatCspDataForProductResponse($cspData);
		} else {
			$response['failed'][$productId] = $this->getFailedMessage('product-invalid', $items);
		}

		$response = $this->generateResponseData($response, $request);
		return apply_filters('cspapi_get_csp_for_product_response', $response, $request);
	}

	/**
	 * Returns the array containing the CSP data for a user.
	 *
	 * @param   int     $userId User Id for whom CSP need to be fetched.
	 * @param   int     $productId  Product id for which CSP data need to
	 *                              fetched. Pass '0', if need to retrieve
	 *                              all products belonging to a user.
	 *                              Default 0.
	 *
	 * @return  array   Array containing CSP data for a particular user.
	 */
	public function getCspForUserFromDB( $userId, $productId = 0) {
		global $wpdb;
		$valueParameter = ( $productId > 0 ) ? array($userId, $productId) : array($userId);
		if ($productId > 0) {
			$cspData = $wpdb->get_results($wpdb->prepare('SELECT product_id, price, flat_or_discount_price, min_qty FROM ' . $wpdb->prefix . 'wusp_user_pricing_mapping WHERE user_id = %d AND product_id = %d  order by product_id', $valueParameter), ARRAY_A);    
		} else {
			$cspData = $wpdb->get_results($wpdb->prepare('SELECT product_id, price, flat_or_discount_price, min_qty FROM ' . $wpdb->prefix . 'wusp_user_pricing_mapping WHERE user_id = %d order by product_id', $valueParameter), ARRAY_A);
		}
		return $cspData;
	}

	/**
	 * Returns the array containing formatted CSP data for API response.
	 * Checks if request has accessibility to read a particular product. If
	 * not, a particular product data is excluded.
	 *
	 * @param   array   $cspData    CSP data for a particular user/ product.
	 * @param   bool    $singleProductResponse  True if '$cspData' contains pricing
	 *                              about a single product. False if '$cspData'
	 *                              contains pricing for all products about a
	 *                              particular user.
	 *
	 * @return  array   Array containing formatted CSP data for API response.
	 */
	public function formatCspDataForUserResponse( $cspData, $singleProductResponse = false) {
		$response = array();
		
		foreach ($cspData as $value) {
			// 1 for flat and 2 for %.
			$discountType = ( '1' == $value['flat_or_discount_price'] ) ? 'flat' : '%';
			$productId    = $value['product_id'];

			if (is_wp_error($this->get_item_permissions_check($productId))) {
				continue;
			}

			if ($singleProductResponse) {
				$response[] = array(
					'min_qty' => (int) $value['min_qty'],
					'discount_type' => $discountType,
					'csp_price' => (float) wc_format_localized_price($value['price']),
				);
			} else {
				$response[$value['product_id']][] = array(
					'min_qty' => (int) $value['min_qty'],
					'discount_type' => $discountType,
					'csp_price' => (float) wc_format_localized_price($value['price']),
				);
			}
		}

		return apply_filters('cspapi_modify_csp_data_user_response_format', $response, $cspData, $singleProductResponse);
	}

	/**
	 * Returns the array containing formatted CSP data for API response.
	 *
	 * @param   array   $cspData    Array containing all pricing mappings of a
	 *                              specific product.
	 *
	 * @return  array   Array containing formatted CSP data for API response.
	 */
	public function formatCspDataForProductResponse( $cspData) {
		$response = array();

		if (is_array($cspData)) {
			foreach ($cspData as $value) {
				$customerId   = $value->user_id;
				$discountType = $value->price_type;
				$price        = $value->price;
				$minQty       = (int) $value->min_qty;

				// 1 for flat and 2 for %.
				$discountType = ( '1' == $discountType ) ? 'flat' : '%';

				// Get formatted price.
				$price = (float) wc_format_localized_price($price);

				$response[] = array(
					'min_qty' => (int) $minQty,
					'discount_type' => $discountType,
					'csp_price' => (float) $price,
					'customer_id' => $customerId
				);
			}
		}

		return apply_filters('cspapi_modify_csp_data_product_response_format', $response, $cspData);
	}

	/**
	 * Check whether customer mapping exists in 'wusp_user_pricing' table.
	 * It is checked by combination of product id, user id, minimum quantity.
	 *
	 * @param   int     $productId  Product Id.
	 * @param   int     $userId     User Id.
	 * @param   int     $minQty     Minimum quantity.
	 *
	 * @return  array|bool    Price of the product along with Id of the row if
	 *                        $shouldReturnPrice is set to true. false if mapping
	 *                        doesn't exist.
	 */
	public function isCustomerMappingExists( $productId, $userId, $minQty) {
		//, $discountType = -1, $price = -1)
		global $wpdb;
		$result         = false;
		$valueParameter = array($productId, $userId, $minQty);
		$result = $wpdb->get_row($wpdb->prepare('SELECT id, price, flat_or_discount_price AS discount_type FROM ' . $wpdb->prefix . 'wusp_user_pricing_mapping WHERE product_id=%d AND user_id=%d AND min_qty=%d', $valueParameter), ARRAY_A);
		if (null == $result) {
			$result = false;
		}
		return apply_filters('cspapi_is_direct_customer_mapping_exists', $result, $userId, $productId, $minQty);
	}
}

CspMappingsCustomerBased::getInstance();
