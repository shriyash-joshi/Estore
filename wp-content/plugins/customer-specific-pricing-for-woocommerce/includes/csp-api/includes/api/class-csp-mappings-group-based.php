<?php

namespace CSPAPI\Includes\API;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class GroupBasedCspMappings extends CspMappings {

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
	protected $rest_base = 'csp/gsp/';

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
		// DELETE - Delete group specific pricing (/wp-json/wc/v3/gsp-mappings)
		// POST   - Add/ update group specific pricing (/wp-json/wc/v3/gsp-mappings)
		register_rest_route(
			$this->namespace, '/' . $this->rest_base, // @codingStandardsIgnoreLine.
			array(
				array(
					'methods'             => \WP_REST_Server::DELETABLE,
					'callback'            => array($this, 'deleteGsp'),
					// 'permission_callback' => array($this, 'delete_items_permissions_check'),
				),
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array($this, 'addGsp'),
					'permission_callback' => array($this, 'create_item_permissions_check'),
				),
			)
		);

		// GET - Retrieve group specific pricing for a group by group name.
		// (/wp-json/wc/v3/gsp-mappings/group/registered).
		register_rest_route(
			$this->namespace, '/' . $this->rest_base . 'group', // @codingStandardsIgnoreLine.
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'getGspForGroupByName' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
				),
			)
		);

		// GET - Retrieve Group specific pricing for a group (using group Id) for a particular product.
		// (/wp-json/wc/v3/gsp-mappings/group/registered/product/16).
		register_rest_route(
			$this->namespace, '/' . $this->rest_base . 'product-id/(?P<product_id>[\d]+)/group', // @codingStandardsIgnoreLine.
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'getGspForGroupByProductId' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
				),
			)
		);

		// GET - Retrieve all pricing mappings of a specific product.
		// (/wp-json/wc/v3/gsp-mappings/product/123).
		register_rest_route(
			$this->namespace, '/' . $this->rest_base . 'product-id/(?P<product_id>[\d]+)', // @codingStandardsIgnoreLine.
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'getGspForProduct' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
				),
			)
		);
	}

	/**
	 * Add a Group specific pricing.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function addGsp( $request) {
		$items        = array_filter($request->get_params());
		$response     = array();
		$existingData = array();
		if (isset($items['gsp_data'])) {
			foreach ($items['gsp_data'] as $item) {
				$productId    = cspapiGetDefaultCspData($item, 'product_id');
				$groupName    = cspapiGetDefaultCspData($item, 'group_name');
				$minQty       = cspapiGetDefaultCspData($item, 'min_qty');
				$discountType = cspapiGetDefaultCspData($item, 'discount_type');
				$price        = cspapiGetDefaultCspData($item, 'gsp_price');
				$clientHash   = cspapiGetDefaultCspData($item, 'hash');
	
				$groupId = getGroupIdByName($groupName);
				if (empty($groupId)) {
					$response['failed'][$clientHash] = $this->getFailedMessage('group-invalid', $item);
					continue;
				}


				if ( ( '%'==$discountType && 100<$price ) || ( 0>$price ) ) {
					$response['failed'][$clientHash] = $this->getFailedMessage('discount-invalid', $item);
					continue;
				}
				
	
				// Generate hash.
				$hash = cspapiGenerateProductHashByEntity($productId, $groupName, $minQty, $discountType, $price);
				
				if (!cspapiIsAPIHashValid($clientHash, $hash, $item)) {
					$response['failed'][$clientHash] = $this->getFailedMessage('hash-invalid', $item);
				} elseif (!cspapiIsValidProduct($productId)) {
					$response['failed'][$clientHash] = $this->getFailedMessage('product-invalid', $item);
				} else {
					$existingData = $this->isGroupMappingExists($productId, $groupId, $minQty); 
					if ($existingData) {
						// 1 for flat and 2 for %.
						$discountType = ( 'flat' == $discountType ) ? 1 : 2;
						if (!cspapiIsSubruleExists($groupId, $productId, $minQty, $discountType) && ( $price != $existingData['price'] || $discountType != $existingData['discount_type'] )) {
							// update the CSP record.
							\WdmCSP\WdmWuspUpdateDataInDB::updateGroupPricingInDb($existingData['id'], $groupId, $productId, $price, $discountType, $minQty);
							$response['success'][$clientHash] = $this->getSuccessMessage('csp-updated', $item);
						} else {
							$response['failed'][$clientHash] = $this->getFailedMessage('gsp-exists', $item);
						}
					} else {
						// 1 for flat and 2 for %.
						$discountType = ( 'flat' == $discountType ) ? 1 : 2;
						\WdmCSP\WdmWuspAddDataInDB::insertGroupProductPricingInDb($groupId, $productId, $discountType, $price, $minQty);
						$response['success'][$clientHash] = $this->getSuccessMessage('csp-added', $item);
					}
				}
			}
		} else {
			$response['failed'] = $this->getFailedMessage('csp-data-invalid');
		}
		$response = $this->generateResponseData($response, $request);
		return apply_filters('cspapi_create_gsp_response', $response, $request);
	}

	/**
	 * Remove a Group specific pricing.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function deleteGsp( $request) {
		global $subruleManager, $ruleManager;
		$items    = array_filter($request->get_params());
		$response = array();
		if (isset($items['gsp_data'])) {
			foreach ($items['gsp_data'] as $item) {
				$productId    = cspapiGetDefaultCspData($item, 'product_id');
				$groupName    = cspapiGetDefaultCspData($item, 'group_name');
				$minQty       = cspapiGetDefaultCspData($item, 'min_qty');
				$discountType = cspapiGetDefaultCspData($item, 'discount_type');
				$price        = cspapiGetDefaultCspData($item, 'gsp_price');
				$clientHash   = cspapiGetDefaultCspData($item, 'hash');
				$groupId      = getGroupIdByName($groupName);
				// Check if group is valid.
				if (empty($groupId)) {
					$response['failed'][$clientHash] = $this->getFailedMessage('group-invalid', $item);
					continue;
				}
				// Generate hash.
				$hash = cspapiGenerateProductHashByEntity($productId, $groupName, $minQty, $discountType, $price);
				
				$result = $this->delete_item_permissions_check($productId);
				if (!cspapiIsAPIHashValid($clientHash, $hash, $item)) {
					// Check if hash is valid.
					$response['failed'][$clientHash] = $this->getFailedMessage('hash-invalid', $item);
				} elseif (is_wp_error($result)) {
					$response['failed'][$clientHash] = $this->getFailedMessage('delete-unauth', $item);
				} elseif ($this->isGroupMappingExists($productId, $groupId, $minQty)) {
					\WuspDeleteData\WdmWuspDeleteData::deleteCustomerMapping('group_id', 'wusp_group_product_price_mapping', '%d', 'group', $groupId, $productId, $minQty);
					//Deactivate subrule for deleted record
					$subruleManager->deactivateSubrulesForGroupsNotInArray($productId, array($groupId), array($minQty));
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
		return apply_filters('cspapi_delete_gsp_response', $response, $request);
	}

	/**
	 * Returns a Group specific pricing applied for group.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @param int             $groupId Group Id.
	 * @param string          $failedkey Key which will be included in the
	 *                        'failed' section of the response.
	 * @return WP_Error|WP_REST_Response
	 */
	public function getGspForGroup( $request, $groupName, $failedkey) {
		$items                 = array_filter($request->get_params());
		$singleProductResponse = false;
		$groupId               = getGroupIdByName($groupName);
		if (empty($groupId)) {
			$response['failed'][$failedkey] = $this->getFailedMessage('group-invalid', $items);
		} else {
			$gspData  = $this->getGspForGroupFromDB($groupId);
			$response = $this->formatGspDataForGroupResponse($gspData, $singleProductResponse);
		}
		$response = $this->generateResponseData($response, $request);
		return apply_filters('cspapi_get_gsp_for_group_response', $response, $request);
	}

	/**
	 * Returns a Group specific pricing applied for group by group Id.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function getGspForGroupByName( $request) {
		$item      = array_filter($request->get_params());
		$groupName = cspapiGetDefaultCspData($item, 'group_name');
		$response  = $this->getGspForGroup($request, $groupName, $groupName);
		return apply_filters('cspapi_get_gsp_for_group_by_name_response', $response, $request);
	}

	/**
	 * Returns a Group specific pricing applied for a group to a product
	 * using group Id.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function getGspForGroupByProductId( $request) {
		$items     = array_filter($request->get_params());
		$groupName = cspapiGetDefaultCspData($items, 'group_name');
		$productId = cspapiGetDefaultCspData($items, 'product_id');
		$response  = $this->getGspForGroupProduct($request, $groupName, $productId, $groupName);
		return apply_filters('cspapi_get_gsp_for_group_by_product_id_response', $response, $request);
	}

	/**
	 * Returns a Group specific pricing applied for a group to a product.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @param int             $groupId Group Id.
	 * @param int             $productId Product Id.
	 * @param string          $failedkey Key which will be included in the
	 *                        'failed' section of the response.
	 * @return WP_Error|WP_REST_Response
	 */
	public function getGspForGroupProduct( $request, $groupName, $productId, $failedKey) {
		$items                 = array_filter($request->get_params());
		$singleProductResponse = true;
		$groupId               = getGroupIdByName($groupName);
		if (empty($groupId)) {
			$response['failed'][$failedKey] = $this->getFailedMessage('group-invalid', $items);
		} elseif ($productId <= 0 || !cspapiIsValidProduct($productId)) {
			$response['failed'][$productId] = $this->getFailedMessage('product-invalid', $items);
		} elseif (is_wp_error($this->get_item_permissions_check($productId))) {
			$response['failed'][$failedKey] = $this->getFailedMessage('view-unauth', $items);
		} else {
			$gspData  = $this->getGspForGroupFromDB($groupId, $productId);
			$response = $this->formatGspDataForGroupResponse($gspData, $singleProductResponse);
		}
		$response = $this->generateResponseData($response, $request);
		return apply_filters('cspapi_get_gsp_for_group_product_response', $response, $request);
	}

	/**
	 * Returns a Group specific pricing applied to a product.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function getGspForProduct( $request) {
		$items          = array_filter($request->get_params());
		$productId      = isset($items['product_id']) ? (int) $items['product_id'] : 0;
		$isProductValid = cspapiIsValidProduct($productId);
		if ($isProductValid) {
			$gspData  = \WdmCSP\WdmWuspGetData::getAllGroupPricesForSingleProduct($productId);
			$response = $this->formatGspDataForProductResponse($gspData);
		} else {
			$response['failed'][$productId] = $this->getFailedMessage('product-invalid', $items);
		}
		$response = $this->generateResponseData($response, $request);
		return apply_filters('cspapi_get_gsp_for_product_response', $response, $request);
	}

	/**
	 * Returns the array containing the GSP data for a group.
	 *
	 * @param   int     $groupId Group Id for whom GSP need to be fetched.
	 * @param   int     $productId  Product id for which GSP data need to
	 *                              fetched. Pass '0', if need to retrieve
	 *                              all products belonging to a Group.
	 *                              Default 0.
	 *
	 * @return  array   Array containing GSP data for a particular group.
	 */
	public function getGspForGroupFromDB( $groupId, $productId = 0) {
		global $wpdb;
		$valueParameter = ( $productId > 0 ) ? array($groupId, $productId) : array($groupId);
		if ($productId > 0) {
			$gspData = $wpdb->get_results($wpdb->prepare('SELECT product_id, price, flat_or_discount_price, min_qty FROM ' . $wpdb->prefix . 'wusp_group_product_price_mapping WHERE group_id = %d  AND product_id = %d  order by product_id', $valueParameter), ARRAY_A);    
		} else {
			$gspData = $wpdb->get_results($wpdb->prepare('SELECT product_id, price, flat_or_discount_price, min_qty FROM ' . $wpdb->prefix . 'wusp_group_product_price_mapping WHERE group_id = %d order by product_id', $valueParameter), ARRAY_A);
		}
		return $gspData;
	}

	/**
	 * Returns the array containing formatted GSP data for API response.
	 * Checks if request has accessibility to read a particular product. If
	 * not, a particular product data is excluded.
	 *
	 * @param   array   $gspData    GSP data for a particular group/ product.
	 * @param   bool    $singleProductResponse  True if '$gspData' contains pricing
	 *                              about a single product. False if '$gspData'
	 *                              contains pricing for all products about a
	 *                              particular group.
	 *
	 * @return  array   Array containing formatted GSP data for API response.
	 */
	public function formatGspDataForGroupResponse( $gspData, $singleProductResponse = false) {
		$response = array();
		
		foreach ($gspData as $value) {
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
					'gsp_price' => (float) wc_format_localized_price($value['price']),
				);
			} else {
				$response[$value['product_id']][] = array(
					'min_qty' => (int) $value['min_qty'],
					'discount_type' => $discountType,
					'gsp_price' => (float) wc_format_localized_price($value['price']),
				);
			}
		}

		return apply_filters('cspapi_modify_gsp_data_group_response_format', $response, $gspData, $singleProductResponse);
	}

	/**
	 * Returns the array containing formatted GSP data for API response.
	 *
	 * @param   array   $gspData    Array containing all pricing mappings of a
	 *                              specific product.
	 *
	 * @return  array   Array containing formatted GSP data for API response.
	 */
	public function formatGspDataForProductResponse( $gspData) {
		$response = array();

		if (is_array($gspData)) {
			foreach ($gspData as $value) {
				$groupId      = $value->group_id;
				$discountType = $value->price_type;
				$price        = $value->price;
				$minQty       = (int) $value->min_qty;
				$groupName    = getGroupNameById($groupId);

				// 1 for flat and 2 for %.
				$discountType = ( '1' == $discountType ) ? 'flat' : '%';

				// Get formatted price.
				$price = (float) wc_format_localized_price($price);

				$response[] = array(
					'min_qty' => (int) $minQty,
					'discount_type' => $discountType,
					'gsp_price' => (float) $price,
					'group_name' => $groupName
				);
			}
		}

		return apply_filters('cspapi_modify_gsp_data_product_response_format', $response, $gspData);
	}

	/**
	 * Check whether group mapping exists in 'wusp_group_product_price_mapping' table.
	 * It is checked by combination of product id, group id, minimum quantity.
	 *
	 * @param   int     $productId  Product Id.
	 * @param   int     $groupId     group Id.
	 * @param   int     $minQty     Minimum quantity.
	 *
	 * @return  array|bool    Price of the product along with Id of the row if
	 *                        $shouldReturnPrice is set to true. false if mapping
	 *                        doesn't exist.
	 */
	public function isGroupMappingExists( $productId, $groupId, $minQty) {
		global $wpdb;
		$result         = false;
		$valueParameter = array($productId, $groupId, $minQty);
		$result         = $wpdb->get_row($wpdb->prepare('SELECT id, price, flat_or_discount_price AS discount_type FROM ' . $wpdb->prefix . 'wusp_group_product_price_mapping WHERE product_id=%d AND group_id=%d AND min_qty=%d', $valueParameter), ARRAY_A);
		if (null == $result) {
			$result = false;
		}
		return apply_filters('cspapi_is_direct_group_mapping_exists', $result, $groupId, $productId, $minQty);
	}
}

GroupBasedCspMappings::getInstance();
