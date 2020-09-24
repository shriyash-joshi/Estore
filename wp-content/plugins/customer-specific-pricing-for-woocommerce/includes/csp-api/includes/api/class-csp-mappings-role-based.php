<?php

namespace CSPAPI\Includes\API;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class RoleBasedCspMappings extends CspMappings {

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
	protected $rest_base = 'csp/rsp/';

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
		// DELETE - Delete role specific pricing (/wp-json/wc/v3/rsp-mappings)
		// POST   - Add/ update role specific pricing (/wp-json/wc/v3/rsp-mappings)
		register_rest_route(
			$this->namespace, '/' . $this->rest_base, // @codingStandardsIgnoreLine.
			array(
				array(
					'methods'             => \WP_REST_Server::DELETABLE,
					'callback'            => array($this, 'deleteRsp'),
					// 'permission_callback' => array($this, 'delete_items_permissions_check'),
				),
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array($this, 'addRsp'),
					'permission_callback' => array($this, 'create_item_permissions_check'),
				),
			)
		);

		// GET - Retrieve role specific pricing for a role by role name.
		// (/wp-json/wc/v3/rsp-mappings/role/subscriber).
		register_rest_route(
			$this->namespace, '/' . $this->rest_base . 'role/(?P<role>\w[\w\s\-]*)', // @codingStandardsIgnoreLine.
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'getRspForRoleByName' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
				),
			)
		);

		// GET - Retrieve Role specific pricing for a Role (using Role Id) for a particular product.
		// (/wp-json/wc/v3/rsp-mappings/role/subscriber/product/16).
		register_rest_route(
			$this->namespace, '/' . $this->rest_base . 'product-id/(?P<product_id>[\d]+)/role/(?P<role>\w[\w\s\-]*)', // @codingStandardsIgnoreLine.
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'getRspForRoleByProductId' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
				),
			)
		);

		// GET - Retrieve all pricing mappings of a specific product.
		// (/wp-json/wc/v3/rsp-mappings/product/123).
		register_rest_route(
			$this->namespace, '/' . $this->rest_base . 'product-id/(?P<product_id>[\d]+)', // @codingStandardsIgnoreLine.
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'getRspForProduct' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
				),
			)
		);
	}

	/**
	 * Add a Role specific pricing.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function addRsp( $request) {
		$items        = array_filter($request->get_params());
		$response     = array();
		$existingData = array();

		if (isset($items['rsp_data'])) {
			foreach ($items['rsp_data'] as $item) {
				$productId    = cspapiGetDefaultCspData($item, 'product_id');
				$role         = cspapiGetDefaultCspData($item, 'role');
				$minQty       = cspapiGetDefaultCspData($item, 'min_qty');
				$discountType = cspapiGetDefaultCspData($item, 'discount_type');
				$price        = cspapiGetDefaultCspData($item, 'rsp_price');
				$clientHash   = cspapiGetDefaultCspData($item, 'hash');
	
				if (empty($role) || !role_exists($role)) {
					$response['failed'][$clientHash] = $this->getFailedMessage('role-invalid', $item);
					continue;
				}

				if ( ( '%'==$discountType && 100<$price ) || ( 0>$price ) ) {
					$response['failed'][$clientHash] = $this->getFailedMessage('discount-invalid', $item);
					continue;
				}
				
	
				// Generate hash.
				$hash = cspapiGenerateProductHashByEntity($productId, $role, $minQty, $discountType, $price);
				
				if (!cspapiIsAPIHashValid($clientHash, $hash, $item)) {
					$response['failed'][$clientHash] = $this->getFailedMessage('hash-invalid', $item);
				} elseif (!cspapiIsValidProduct($productId)) {
					$response['failed'][$clientHash] = $this->getFailedMessage('product-invalid', $item);
				} else {
					$existingData = $this->isRoleMappingExists($productId, $role, $minQty); 
					if ($existingData) {
						// 1 for flat and 2 for %.
						$discountType = ( 'flat' == $discountType ) ? 1 : 2;
						if (!cspapiIsSubruleExists($role, $productId, $minQty, $discountType) && ( $price != $existingData['price'] || $discountType != $existingData['discount_type'] )) {
							// update the CSP record.
							\WdmCSP\WdmWuspUpdateDataInDB::updateRolePricingInDb($existingData['id'], $role, $productId, $price, $discountType, $minQty);
							$response['success'][$clientHash] = $this->getSuccessMessage('csp-updated', $item);
						} else {
							$response['failed'][$clientHash] = $this->getFailedMessage('rsp-exists', $item);
						}
					} else {
						// 1 for flat and 2 for %.
						$discountType = ( 'flat' == $discountType ) ? 1 : 2;
						\WdmCSP\WdmWuspAddDataInDB::insertRoleProductMappingInDb($role, $productId, $discountType, $price, $minQty);
						$response['success'][$clientHash] = $this->getSuccessMessage('csp-added', $item);
					}
				}
			}
		} else {
			$response['failed'] = $this->getFailedMessage('csp-data-invalid');
		}

		$response = $this->generateResponseData($response, $request);

		return apply_filters('cspapi_create_rsp_response', $response, $request);
	}

	/**
	 * Remove a Role specific pricing.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function deleteRsp( $request) {
		global $subruleManager, $ruleManager;
		$items    = array_filter($request->get_params());
		$response = array();

		if (isset($items['rsp_data'])) {
			foreach ($items['rsp_data'] as $item) {
				$productId    = cspapiGetDefaultCspData($item, 'product_id');
				$role         = cspapiGetDefaultCspData($item, 'role');
				$minQty       = cspapiGetDefaultCspData($item, 'min_qty');
				$discountType = cspapiGetDefaultCspData($item, 'discount_type');
				$price        = cspapiGetDefaultCspData($item, 'rsp_price');
				$clientHash   = cspapiGetDefaultCspData($item, 'hash');

				// Check if role is valid.
				if (empty($role) || !role_exists($role)) {
					$response['failed'][$clientHash] = $this->getFailedMessage('role-invalid', $item);
					continue;
				}


				// Generate hash.
				$hash = cspapiGenerateProductHashByEntity($productId, $role, $minQty, $discountType, $price);
				
				$result = $this->delete_item_permissions_check($productId);
				if (!cspapiIsAPIHashValid($clientHash, $hash, $item)) {
					// Check if hash is valid.
					$response['failed'][$clientHash] = $this->getFailedMessage('hash-invalid', $item);
				} elseif (is_wp_error($result)) {
					$response['failed'][$clientHash] = $this->getFailedMessage('delete-unauth', $item);
				} elseif ($this->isRoleMappingExists($productId, $role, $minQty)) {
					\WuspDeleteData\WdmWuspDeleteData::deleteCustomerMapping('role', 'wusp_role_pricing_mapping', '%s', 'role', $role, $productId, $minQty);
					//Deactivate subrule for deleted record
					$subruleManager->deactivateSubrulesForRolesNotInArray($productId, array($role), array($minQty));
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
		return apply_filters('cspapi_delete_rsp_response', $response, $request);
	}

	/**
	 * Returns a Role specific pricing applied for role.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @param string          $role role slug.
	 * @param string          $failedkey Key which will be included in the
	 *                        'failed' section of the response.
	 * @return WP_Error|WP_REST_Response
	 */
	public function getRspForrole( $request, $role, $failedkey) {
		$items                 = array_filter($request->get_params());
		$singleProductResponse = false;
		
		if (empty($role) || !role_exists($role)) {
			$response['failed'][$failedkey] = $this->getFailedMessage('role-invalid', $items);
		} else {
			$rspData  = $this->getRspForRoleFromDB($role);
			$response = $this->formatRspDataForRoleResponse($rspData, $singleProductResponse);
		}

		$response = $this->generateResponseData($response, $request);

		return apply_filters('cspapi_get_rsp_for_role_response', $response, $request);
	}

	/**
	 * Returns a Role specific pricing applied for role by role slug.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function getRspForRoleByName( $request) {
		$item = array_filter($request->get_params());
		$role = cspapiGetDefaultCspData($item, 'role');

		$response = $this->getRspForRole($request, $role, $role);
		return apply_filters('cspapi_get_rsp_for_role_by_name_response', $response, $request);
	}

	/**
	 * Returns a Role specific pricing applied for a role to a product
	 * using role slug.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function getRspForRoleByProductId( $request) {
		$items     = array_filter($request->get_params());
		$role      = cspapiGetDefaultCspData($items, 'role');
		$productId = cspapiGetDefaultCspData($items, 'product_id');

		$response = $this->getRspForRoleProduct($request, $role, $productId, $role);
		return apply_filters('cspapi_get_rsp_for_role_by_product_id_response', $response, $request);
	}

	/**
	 * Returns a Role specific pricing applied for a role to a product.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @param string          $role role slug.
	 * @param int             $productId Product Id.
	 * @param string          $failedkey Key which will be included in the
	 *                        'failed' section of the response.
	 * @return WP_Error|WP_REST_Response
	 */
	public function getRspForRoleProduct( $request, $role, $productId, $failedKey) {
		$items                 = array_filter($request->get_params());
		$singleProductResponse = true;

		if (empty($role) || !role_exists($role)) {
			$response['failed'][$failedKey] = $this->getFailedMessage('role-invalid', $items);
		} elseif ($productId <= 0 || !cspapiIsValidProduct($productId)) {
			$response['failed'][$productId] = $this->getFailedMessage('product-invalid', $items);
		} elseif (is_wp_error($this->get_item_permissions_check($productId))) {
			$response['failed'][$failedKey] = $this->getFailedMessage('view-unauth', $items);
		} else {
			$rspData  = $this->getRspForRoleFromDB($role, $productId);
			$response = $this->formatRspDataForRoleResponse($rspData, $singleProductResponse);
		}

		$response = $this->generateResponseData($response, $request);

		return apply_filters('cspapi_get_rsp_for_role_product_response', $response, $request);
	}

	/**
	 * Returns a Role specific pricing applied to a product.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function getRspForProduct( $request) {
		$items          = array_filter($request->get_params());
		$productId      = isset($items['product_id']) ? (int) $items['product_id'] : 0;
		$isProductValid = cspapiIsValidProduct($productId);

		if ($isProductValid) {
			$rspData  = \WuspSimpleProduct\WrspSimpleProduct\WdmWuspSimpleProductsRsp::getAllRolePricesForSingleProduct($productId);
			$response = $this->formatRspDataForProductResponse($rspData);
		} else {
			$response['failed'][$productId] = $this->getFailedMessage('product-invalid', $items);
		}

		$response = $this->generateResponseData($response, $request);
		return apply_filters('cspapi_get_rsp_for_product_response', $response, $request);
	}

	/**
	 * Returns the array containing the RSP data for a role.
	 *
	 * @param   int     $role role slug for whom RSP need to be fetched.
	 * @param   int     $productId  Product id for which RSP data need to
	 *                              fetched. Pass '0', if need to retrieve
	 *                              all products belonging to a Role.
	 *                              Default 0.
	 *
	 * @return  array   Array containing RSP data for a particular role.
	 */
	public function getRspForRoleFromDB( $role, $productId = 0) {
		global $wpdb;
		$valueParameter = ( $productId > 0 ) ? array($role, $productId) : array($role);
		if ($productId > 0) {
			$rspData = $wpdb->get_results($wpdb->prepare('SELECT product_id, price, flat_or_discount_price, min_qty FROM ' . $wpdb->prefix . 'wusp_role_pricing_mapping WHERE role = %s  AND product_id = %d order by product_id', $valueParameter), ARRAY_A);
		} else {
			$rspData = $wpdb->get_results($wpdb->prepare('SELECT product_id, price, flat_or_discount_price, min_qty FROM ' . $wpdb->prefix . 'wusp_role_pricing_mapping WHERE role = %s order by product_id', $valueParameter), ARRAY_A);
		}
		return $rspData;
	}

	/**
	 * Returns the array containing formatted RSP data for API response.
	 * Checks if request has accessibility to read a particular product. If
	 * not, a particular product data is excluded.
	 *
	 * @param   array   $rspData    RSP data for a particular role/ product.
	 * @param   bool    $singleProductResponse  True if '$rspData' contains pricing
	 *                              about a single product. False if '$rspData'
	 *                              contains pricing for all products about a
	 *                              particular role.
	 *
	 * @return  array   Array containing formatted RSP data for API response.
	 */
	public function formatRspDataForRoleResponse( $rspData, $singleProductResponse = false) {
		$response = array();
		
		foreach ($rspData as $value) {
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
					'rsp_price' => (float) wc_format_localized_price($value['price']),
				);
			} else {
				$response[$value['product_id']][] = array(
					'min_qty' => (int) $value['min_qty'],
					'discount_type' => $discountType,
					'rsp_price' => (float) wc_format_localized_price($value['price']),
				);
			}
		}

		return apply_filters('cspapi_modify_rsp_data_role_response_format', $response, $rspData, $singleProductResponse);
	}

	/**
	 * Returns the array containing formatted RSP data for API response.
	 *
	 * @param   array   $rspData    Array containing all pricing mappings of a
	 *                              specific product.
	 *
	 * @return  array   Array containing formatted RSP data for API response.
	 */
	public function formatRspDataForProductResponse( $rspData) {
		$response = array();

		if (is_array($rspData)) {
			foreach ($rspData as $value) {
				$role         = $value->role;
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
					'rsp_price' => (float) $price,
					'role' => $role
				);
			}
		}

		return apply_filters('cspapi_modify_rsp_data_product_response_format', $response, $rspData);
	}

	/**
	 * Check whether role mapping exists in 'wusp_role_pricing_mapping' table.
	 * It is checked by combination of product id, role slug, minimum quantity.
	 *
	 * @param   int     $productId  Product Id.
	 * @param   int     $role       role slug.
	 * @param   int     $minQty     Minimum quantity.
	 *
	 * @return  array|bool    Price of the product along with Id of the row if
	 *                        $shouldReturnPrice is set to true. false if mapping
	 *                        doesn't exist.
	 */
	public function isRoleMappingExists( $productId, $role, $minQty) {
		global $wpdb;
		$result         = false;
		$valueParameter = array($productId, $role, $minQty);
		$result = $wpdb->get_row($wpdb->prepare('SELECT id, price, flat_or_discount_price AS discount_type FROM ' . $wpdb->prefix . 'wusp_role_pricing_mapping WHERE product_id=%d AND role=%s AND min_qty=%d', $valueParameter), ARRAY_A);
		if (null == $result) {
			$result = false;
		}
		return apply_filters('cspapi_is_direct_role_mapping_exists', $result, $role, $productId, $minQty);
	}
}

RoleBasedCspMappings::getInstance();
