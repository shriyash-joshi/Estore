<?php

namespace cspNewOrder;

/**
 * Called when new order is created from backend
 */
if (!class_exists('WdmCustomerSpecificPricingNewOrder')) {
	class WdmCustomerSpecificPricingNewOrder {
	
		/**
		 * Call class functions on actions and filters
		 */
		/**
		 * Action for enqueuing scripts and styles.
		 * Action for setting customer-id by admin only.
		 * Action to Get the quantity pricing pairs for the product.
		 * Action to Add the product in order row.
		 */
		public function __construct() {
			add_action('admin_enqueue_scripts', array($this, 'wdmEnqueueScripts'), 10);
			$postArray = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
			if (isset($postArray['action']) && 'woocommerce_add_order_item' == $postArray['action']) {
				add_action('woocommerce_new_order_item', array($this,'cspChangeTotalSubtotal'), 100, 3);
				add_action('woocommerce_admin_order_item_values', array($this, 'cspChangeTemplateTotalSubtotal'), 100, 3);
			}

			if ($this->isAddNewOrderPage()) {
				// Add functions to hooks for wocommerce backend order
				add_filter('woocommerce_order_get_items', array($this,'cspGetOrderItem'), 10, 3);
				add_action('woocommerce_order_before_calculate_totals', function () {
					remove_filter('woocommerce_order_get_items', array($this,'cspGetOrderItem'), 10);
				});
				add_action('wp_ajax_wdm_get_discount_amount', array($this, 'wdmGetDiscountAmount'));
			}

			add_action('wp_ajax_get_customer_id', array($this, 'wdmSetCustomerId'));
			add_action('wp_ajax_get_quantity_price_pairs', array($this, 'getOrderItemsQuantityPricePair'));
			add_action('woocommerce_before_order_itemmeta', array($this, 'addProductInOrderRow'), 10, 3);
		}

		public function cspGetOrderItem( $orderItems, $wc_order, $types) {
			// woocommerce backend order, calculate product price,
			// for example, when coupon is applied.
			foreach ($orderItems as $orderItem) {
				if ($orderItem instanceof \WC_Order_Item_Product) {
					$cspPrice = -1;
					$productQuantity = $orderItem->get_quantity();
					// Fetch the variation ID.
					$productId = $orderItem->get_variation_id();

					// If variation ID is '0', then fetch the product ID.
					if (empty($productId)) {
						$productId = $orderItem->get_product_id();
					}

					$productChanges = $orderItem->get_changes();
					$productPrice = -1;
					$orderId = $wc_order->get_order_number();
					$userId = get_post_meta($orderId, 'csp_customer_id', true);

					$product = wc_get_product($productId);
					if (!array_key_exists('subtotal', $productChanges)) {
						$productPrice = $product->get_price();
						$cspPrice = \WuspSimpleProduct\WuspCSPProductPrice::getDBPrice($productId, $productPrice, $productQuantity, $userId);
						$cspPrice = self::excludeTaxFromPrices($product, $cspPrice);
						$orderItem->set_subtotal($productQuantity * $cspPrice);
					}

					if (!array_key_exists('total', $productChanges)) {
						$productPrice = ( -1 == $productPrice ) ? $product->get_price() : $productPrice;
						$cspPrice = ( -1 == $cspPrice ) ? \WuspSimpleProduct\WuspCSPProductPrice::getDBPrice($productId, $productPrice, $productQuantity, $userId) : $cspPrice;
						$cspPrice = self::excludeTaxFromPrices($product, $cspPrice);
						/**
						 * Don't apply CSP price if '$orderItem->get_total()' is less than original product cost ($productPrice * $productQuantity).
						 * Because if '$orderItem->get_total()' is less, it indicates that coupon is applied on the product price.
						 */
						if (( $productPrice * $productQuantity ) == $orderItem->get_total()) {
							$orderItem->set_total($productQuantity * $cspPrice);
						}
					}
				}
			}
			unset($types);
			return $orderItems;
		}

		/**
		 * #63931 
		 */
		public function cspChangeTemplateTotalSubtotal( $product, $orderItem, $orderItemId) {
			$this->cspChangeTotalSubtotal($orderItemId, $orderItem, $orderItem->get_order_id());
		}

		public function cspChangeTotalSubtotal( $item_id, $orderItem, $orderId) {
			// woocommerce backend order, when product is added.
			do_action('csp-before-dashboard-order-change-total-subtotal');
			$userId = get_post_meta($orderId, 'csp_customer_id', true);
			if ($orderItem instanceof \WC_Order_Item_Product &&
			   !empty($userId)) {
				// Fetch the variation ID.
				$productId = $orderItem->get_variation_id();

				// If variation ID is '0', then fetch the product ID.
				if (empty($productId)) {
					$productId = $orderItem->get_product_id();
				}

				$product = wc_get_product($productId);
				$productQuantity = $orderItem->get_quantity();
				$cspPrice = \WuspSimpleProduct\WuspCSPProductPrice::getDBPrice($productId, $product->get_price(), $productQuantity, $userId);
				$cspPrice = self::excludeTaxFromPrices($product, $cspPrice);
				// set product subtotal
				$orderItem->set_subtotal($productQuantity * $cspPrice);
				// set product total
				$orderItem->set_total($productQuantity * $cspPrice);
			}
			do_action('csp-after-dashboard-order-change-total-subtotal');
			unset($item_id);
		}

		/**
		 * Enqueue the js file
		 */
		public function wdmEnqueueScripts() {
			global $post;
			if (!empty($post) && 'shop_order'==$post->post_type) {
				wp_enqueue_script('wdm_csp_functions', plugins_url('/js/wdm-csp-functions.js', dirname(dirname(__FILE__))), array('jquery'), CSP_VERSION);

				wp_enqueue_script('wdm_new_order_js', plugins_url('/js/new_order/new_order.js', dirname(dirname(__FILE__))), array('jquery'), CSP_VERSION, true);
				wp_localize_script('wdm_new_order_js', 'wdm_new_order_ajax', array('ajaxurl' => admin_url('admin-ajax.php'), 'order_id' => $post->ID));
			}
		}

		/**
		* For that customer or user-id:
		* Get the specific pricing details of the Product selected.
		* Get the quantity pricing pairs for the product.
		*/
		public function getOrderItemsQuantityPricePair() {
			$response = array();
			//$order_id = $_REQUEST['order_id'];
			do_action('csp-before-dashboard-item-price-list');
			$postArray = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
			$product_ids = isset($postArray['product_id'])?$postArray['product_id']:array();

			if (!is_array($product_ids)) {
				$product_ids = array_map('trim', explode(',', $product_ids));
			}

			$user_id = isset($postArray['customer_id']) ? intval($postArray['customer_id']) : 0;

			if ( 0!==$user_id) {
				if (!empty($product_ids)) {
					if (is_array($product_ids)) {
						foreach ($product_ids as $product_id) {
							$response[$product_id] = $this->productQuantityPricePair($product_id, $user_id);
						}
					} else {
						$response[$product_id] = $this->productQuantityPricePair($product_id, $user_id);
					}
				}
				echo json_encode($response);
			} else {
				echo json_encode(array('error' => 'NO_DATA_FOUND'));
			}
			do_action('csp-after-dashboard-item-price-list');
			die();
		}

		/**
		* Gets the product Quantity Pricing pair for the product.
		* For these prices exclude the taxes from price.
		* Get the quantity list from the pricing pairs.
		* Gets the regular price excluding price for the product.
		 *
		* @param int $productId Product Id.
		* @param int $userId USer Id.
		* @return array $response specific pricing details for the product.
		*/
		public function productQuantityPricePair( $productId, $userId) {
			$productObject = wc_get_product($productId);
			$response = array();
			$csp_prices = \WuspSimpleProduct\WuspCSPProductPrice::getQuantityBasedPricing($productId, $userId);
			$response['csp_prices'] = self::excludeTaxFromPrices($productObject, $csp_prices);
			$response['qtyList'] = array_keys($csp_prices);
			$response['regular_price'] = self::excludeTaxFromPrices($productObject, \WuspSimpleProduct\WuspCSPProductPrice::cspGetRegularPrice($productId));
			return $response;
		}

		/**
		* Exclude the taxes from the price of products.
		 *
		* @param object $product Product object.
		* @param array $prices Quantity Pricing array.
		* @return array $new_prices Prices excluding taxes.
		*/
		public static function excludeTaxFromPrices( $product, $prices) {
			if (is_array($prices)) {
				$new_prices = array();
				foreach ($prices as $key => $price) {
					if (is_numeric($price)) {
						if (version_compare(WC_VERSION, '2.7', '<')) {
							$new_prices[$key] = $product->get_price_excluding_tax(1, $price);
						} else {
							$new_prices[$key] = wc_get_price_excluding_tax($product, array('price' => $price));
						}
					} else {
						$new_prices[$key] = 0;
					}
				}
				return $new_prices;
			}

			if (is_numeric($prices)) {
				if (version_compare(WC_VERSION, '2.7', '<')) {
					return $product->get_price_excluding_tax(1, $prices);
				} else {
					return wc_get_price_excluding_tax($product, array('price' => $prices));
				}
			}
		}

		/**
		* Allows only admin to set customer id.
		* If any other user with other capabilities give security error.
		* Update the Post meta table with the order-id and customer id.
		*/
		public function wdmSetCustomerId() {
			//Allow only admin to get selection
			$capability_required = apply_filters('csp_get_customers_id_capability', 'manage_options');
			$can_user_get_id = apply_filters('csp_can_user_get_customers_id', current_user_can($capability_required));
			$postArray = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
			if (!$can_user_get_id && is_numeric(intval($postArray['order_id']))) {
				echo 'Security Check';
				exit;
			}
			//check if Order post meta exists for the user or not.
			update_post_meta($postArray['order_id'], 'csp_customer_id', $postArray['customer_id']);
			die();
		}

		public function wdmGetDiscountAmount() {
			// woocommerce backend order
			$coupons       = $post['coupons'];
			$productPrice  = $post['product_price'];
			$priceTotal    = $post['price_total'];
			$productId     = $post['product_id'] ;
			$quantity      = $post['quantity'];
			$finalPrice = $priceTotal;
			$seqDisEnabled = get_option('woocommerce_calc_discounts_sequentially', 'no');

			foreach ($coupons as $coupon) {
				$couponObj = new \WC_Coupon($coupon);
				$product = wc_get_product($productId);
				if (!$couponObj->is_valid_for_product($product)) {
					continue;
				}

				$product->set_price($productPrice);
				$cart_item = array(
					'data'     => $product,
					'quantity' => $quantity
				);

				if ('yes' === $seqDisEnabled) {
					$discountedAmount = $couponObj->get_discount_amount($finalPrice, $cart_item);
					$finalPrice -= $discountedAmount;
				} else {
					$discountedAmount = $couponObj->get_discount_amount($priceTotal, $cart_item);
					$finalPrice -= $discountedAmount;
				}
			}

			echo wp_kses($finalPrice, array('span'=>array('class'=>true)));
			die();
		}


		/**
		* Adds the product in order row.
		 *
		* @param object $item The item being displayed
		* @param int $item_id The id of the item being displayed
		* @param object $_product Product object.
		*/
		public function addProductInOrderRow( $item_id, $item, $_product) {
			$product_id = $item['product_id'];

			if (! empty($item['variation_id'])) {
				$product_id = $item['variation_id'];
			}

			echo "<input type='hidden' class='csp_order_item_product_id' value='" . esc_attr($product_id) . "'>";
			unset($item_id);
			unset($_product);
		}

		/**
		 * Checks whether the current page is new order creation page.
		 *
		 * @return bool Returns true if the current page is new order creation
		 *              page, false otherwise.
		 */
		public function isAddNewOrderPage() {
			if (!is_admin()) {
				return false;
			}

			global $pagenow;
			$postType = isset($_GET['post_type']) ? sanitize_text_field($_GET['post_type']) : false;

			if ('post-new.php' == $pagenow && 'shop_order' == $postType) {
				return true;
			}
			
			$httpReferer = isset($_SERVER['HTTP_REFERER']) ? sanitize_text_field($_SERVER['HTTP_REFERER']) : false;
			if (defined('DOING_AJAX') && DOING_AJAX && false !== strpos($httpReferer, '/post-new.php') && false !== strpos($httpReferer, 'post_type=shop_order')) {
				return true;
			}
			 
			return false;
		}
	}
}
