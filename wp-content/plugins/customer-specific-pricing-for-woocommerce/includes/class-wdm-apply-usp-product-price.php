<?php

namespace WuspSimpleProduct;

//use WuspGetData as cspGetData;

//check whether a class with the same name exists
if (! class_exists('WuspCSPProductPrice')) {
	/**
	 * Class to Display & Process data of Simple Products for User Specific Pricing
	 */
	//class declaration
	class WuspCSPProductPrice extends \WuspSimpleProduct\WuspCSPProductPriceCommons {
	
		public $appliedPriceInCart = false;
		
		public $productCategories = array();
		/**
		* Includes files for the category based specific pricing.
		* Action for updating cart item with the specific price.
		* Action for enqueuing scripts for front-end.
		* Action for showing quantity based pricing on front-end.
		* Action for showing the product price total on basis of custom
		* pricing.
		* Action for applying custom pricing for variations
		*/
		public function __construct() {
			include_once 'category-pricing/class-wdm-wusp-get-category-data.php';
			include_once 'category-pricing/class-wdm-wusp-add-category-data.php';
			include_once 'category-pricing/class-wdm-wusp-delete-category-data.php';
			add_action('woocommerce_before_calculate_totals', array($this, 'applyQuantityPriceInCart'), 2);
			add_action('wp_enqueue_scripts', array($this, 'cspFrontEndScript'));

			if (defined('WC_VERSION')) {
				$this->hookWcGetPriceFilter();
			} else {
				add_action('woocommerce_loaded', array($this, 'hookWcGetPriceFilter'));
			}

			add_action('woocommerce_before_shop_loop', array($this, 'setInsideShopLoopFlag'));
			add_action('woocommerce_after_shop_loop', array($this, 'unsetInsideShopLoopFlag'));
			add_filter('woocommerce_cross_sells_columns', array($this, 'setFlagForUpsellsAndCrossSells'), 10);
			add_filter('woocommerce_upsells_columns', array($this, 'setFlagForUpsellsAndCrossSells'), 10);
			add_filter('woocommerce_get_price_html', array($this, 'showQuantityBasedPricing', ), 1, 2);
			add_filter('woocommerce_product_is_on_sale', array($this, 'showCSPAsSalePrice', ), 1, 2);
			add_action('woocommerce_single_product_summary', array($this,'cspQuantityBasedProductTotal',), 10);
			add_filter('woocommerce_variation_prices', array( $this, 'applyCSPVariationPrice' ), 10, 2);
			add_filter('wdm_csp_show_total_price', array($this, 'shouldShowTotalPrice'), 20, 2);
			add_filter('woocommerce_product_variation_get_price', array( $this, 'applyCustomPrice' ), 1, 2);
			add_action('woocommerce_after_cart_item_quantity_update', array($this, 'cspLimitCartQuantity'), 1, 4);
			add_filter('woocommerce_is_purchasable', array($this, 'cspIsPurchasableForUser', ), 1, 2);
			add_filter('woocommerce_variation_is_purchasable', array($this, 'cspIsPurchasableForUser', ), 1, 2);
			add_filter('woocommerce_add_to_cart_validation', array($this,'cspValidateAddToCart'), 10, 4);
			add_filter('woocommerce_quantity_input_min', array($this,'cspSetDefaultMinimumQty'), 1, 2);
			add_filter('woocommerce_loop_add_to_cart_link', array($this, 'wdmReplaceAddToCartButton'), 10, 2);
			add_filter('wdm_get_csp_for_product_qty', array($this,'wdmGetCSPrice'), 20, 4);
			add_filter('woocommerce_cart_item_price', array($this, 'filterMiniCartPrice'), 90, 3);
		}



		

		/**
		 * This method validates if user has selected valid allowed,
		 * quantity of a product to buy for both simple and variable products.
		 * if selected quantity is not valid according to the csp rules, method returns
		 * false to the filter & displays minimum quantity error message on product page
		 */
		public function cspValidateAddToCart( $passed, $productId, $quantity, $variationId = '') {
			$regularPrice    = get_post_meta($productId, '_regular_price', true);
			$regularPrice    = apply_filters('wdm_csp_regular_price', $regularPrice, $productId);
			$productPriceSet = is_numeric($regularPrice);
			if ($productPriceSet) {
				return $passed;
			}
			if (''!=$variationId) {
				$minQty =$this->cspGetDefaultMinimumQty($variationId);
				if (1!=$minQty && $quantity<$minQty) {
					wc_add_notice(__('Minimum purchasable quantity for this product is  ' . $minQty, 'customer-specific-pricing-for-woocommerce'), 'error');
					return false;
				}
				//if variation is not sold individually check if variation parent is sold individually
				$soldIndividually =get_post_meta($variationId, '_sold_individually', true)=='yes'?true:false;
				if (!$soldIndividually) {
					$soldIndividually =get_post_meta($productId, '_sold_individually', true)=='yes'?true:false;
				}
				//when variation gets added to the cart redirect user to variable product page.
				$this->setcurrentAddToCartId($variationId);
				add_filter('woocommerce_add_to_cart_redirect', array($this, 'getProductUrl'), 99, 1);
			} else {
					$minQty =$this->cspGetDefaultMinimumQty($productId);
				if (1!=$minQty && $quantity<$minQty) {
					wc_add_notice(__('Minimum purchasable quantity for this product is ' . $minQty, 'customer-specific-pricing-for-woocommerce'), 'error');
					return false;
				}
				$soldIndividually =get_post_meta($productId, '_sold_individually', true)=='yes'?true:false;
				$this->setcurrentAddToCartId($productId);
				add_filter('woocommerce_add_to_cart_redirect', array($this, 'getProductUrl'), 99, 1);
			}
			if ($minQty>1 && $soldIndividually) {
				add_filter('woocommerce_is_sold_individually', '__return_false');
			}
			return $passed;
		}


		public function getProductUrl( $url) {
			$redirectToCustomUrl = apply_filters('wdm_csp_redirect_to_custom_url_after_add_to_cart', 'unset', $url, 99);
			if ('unset' == $redirectToCustomUrl) {
				$redirectToCustomUrl =get_permalink($this->getcurrentAddToCartId());
			}
			return $redirectToCustomUrl;
		}
		/**
		 * This method is called by a filter "woocommerce_after_cart_item_quantity_update"
		 * i.e when the value of the product quantity is updated in the cart.
		 * This method checks if the product for which quanity updated has csp rules,
		 * which are forcing the product quantity, in such case product qauntity is set to
		 * allowed menimum & message is shown to user. all other products quantity gets updated.
		 *
		 */
		public function cspLimitCartQuantity( $cartItemKey, $quantity, $oldQuantity, $cart) {
			$productId   =$cart->cart_contents[$cartItemKey]['product_id'];
			$product     =wc_get_product($productId);
			$productName =$product->get_title();
			if ($product->is_type('variable')) {
				$productId =$cart->cart_contents[$cartItemKey]['variation_id'];
			}
			$minQty =$this->cspGetDefaultMinimumQty($productId);
			$minQty = apply_filters('csp-filter-min-purchsable-quantity', $minQty, $productId);
			if ($quantity < $minQty) {
				$cart->cart_contents[ $cartItemKey ]['quantity'] = $minQty;
				$productName                                     =wc_get_product($productId)->get_title();
				/* translators: %1$s :Product Name, %2$d: minimum purchase quantity */
				$notice =  sprintf(__('Minimum purchase quantity for %1$s is %2$d', 'customer-specific-pricing-for-woocommerce'), $productName, $minQty);
				wc_add_notice(esc_html($notice), 'notice');
				$oldQuantity =$minQty;
			}
		}

		/**
		 * This method tells wether the current product is purchasable for the user
		 * Returns false when
		 *  - the product is out of stock.
		 *  - the products regular price is not set & no csp rule specified
		 *    for the current user.
		 *
		 * Returns true when
		 *  - product is in stock & available to purchase for everyone.
		 *  - product is in stock but no regular price set for the product but
		 *    csp rule for the current user is specified.
		 *  - returns true & set min qty to purchase to minimum quantity
		 *    specified in rule
		 *
		 * @param bool $purchasable parameter with actual product purchasability flag
		 * @param object $product woocommerce product object.
		 * @return bool if product is purchasable for the user acording to csp rules.
		 */
		public function cspIsPurchasableForUser( $purchasable, $product) {
			//echo '<pre>';
			
			$productId  = $product->get_id();
			$userId     = get_current_user_id();
			$cspPrices  = self::getQuantityBasedPricing($productId, $userId);
			$isCSPSet   = empty($cspPrices)?false:true;
			$stock      = $product->get_stock_quantity();
			$minQty     = $this->getMinQty($cspPrices);
			$WcMcCSPSet = apply_filters('is_wcmc_csp_set_for_the_product', false, $product);
			if (!$purchasable) {
				if (!( $product->exists() && ( 'publish'===$product->get_status() || current_user_can('edit_post', $product->get_id()) ) )) {
					return false;
				}
				if (!$isCSPSet && !$WcMcCSPSet) {
					return false;
				}
			}
			$wdmProductStockCSP =$this->wdmProductStockCSP($isCSPSet, $product, $minQty, $stock);
			if ('NOCSP'!=$wdmProductStockCSP) {
					return $wdmProductStockCSP;
			}
			if ($minQty>1) {
				add_filter('woocommerce_is_sold_individually', '__return_false');
			}
			return $purchasable;
		}


		/**
		 * Stock Management when CSP applied
		 *
		 */
		private function wdmProductStockCSP( $isCSPSet, $product, $minQty, $stock) {
			if ($isCSPSet) {
				if ($product->managing_stock()) {
					if (!$product->is_in_stock()) {
						if ($product->backorders_allowed()) {
							if ($minQty>1) {
								add_filter('woocommerce_is_sold_individually', '__return_false');
							}
						}
						return true;
					}
					if ($stock<$minQty && !$product->backorders_allowed()) {
						add_action('wdm_before_csp_total_price_html', array($this,'wdmAvailabilityMessage'), 20, 1);
						return false;
					}
					if ($minQty>1 && ( $stock>=$minQty || $product->backorders_allowed() )) {
						add_filter('woocommerce_is_sold_individually', '__return_false');
						return true;
					}
				}
			}
			return 'NOCSP';
		}


		public function wdmAvailabilityMessage( $product) {
			$stock   =$product->get_stock_quantity();
			$message = "<p><b>Sorry</b>, This product is no longer purchasable.<br><b>Only $stock left in stock.</b></p>";
			$message = apply_filters('wdm_minimum_qty_less_in_stock_message', $message, $stock);
			echo esc_html($message);
		}

		/**
		 * This function changes archive page product add to cart button
		 * to "Show Special Pricing" when ,
		 * -the regular price is not specified for the product &&
		 * -no CSP rule for the product for quanity 1 is specified.
		 * (when admin wants to specify minimum purchase quantity using CSP)
		 *
		 * @param [type] $button
		 * @param [type] $product
		 * @return void
		 */
		public function wdmReplaceAddToCartButton( $button, $product) {
			if ($product->get_type()!='simple') {
				return $button;
			}
			if ($product->managing_stock() && !$product->is_in_stock()) {
				return $button;
			}
		  
			$productId =$product->get_id();
			$userId    =get_current_user_id();
			$cspPrices =self::getQuantityBasedPricing($productId, $userId);
			if (empty($cspPrices)) {
				return $button;
			}
			$priceForSingleQtyExist =array_key_exists(1, $cspPrices);
			$regularPrice           = get_post_meta($productId, '_regular_price', true);
			$regularPrice           = apply_filters('wdm_csp_regular_price', $regularPrice, $productId);
			$regPriceEmpty          = empty($regularPrice)? true:false;
			$minQty                 =$this->getMinQty($cspPrices);
			$stock                  = $product->get_stock_quantity();
			if (!$priceForSingleQtyExist && $regPriceEmpty) {
				if (!empty($stock) && $minQty>$stock) {
					return $button;
				}
				$cspSettings              = get_option('wdm_csp_settings');
				$specialPricingButtonText = isset($cspSettings['special_pricing_button_text'])
											&& !empty($cspSettings['special_pricing_button_text']) ? $cspSettings['special_pricing_button_text']:__('Show Special Pricing', 'customer-specific-pricing-for-woocommerce');
				return '<a class="button" href="' . $product->get_permalink() . '">' . $specialPricingButtonText . '</a>';
			}
			return $button;
		}

		/**
		 * This method is used to check if csp rules are set in suct a way that
		 * user has to buy minimum 'x' products, in such case method returns minimum quantity
		 * to the filter woocommerce_quantity_input_min
		 */
		public function cspSetDefaultMinimumQty( $min, $product) {
			$productPriceSet = is_numeric($product->get_regular_price());
			if ($productPriceSet) {
				return $min;
			}
			$productId =$product->get_id();
			$userId    =get_current_user_id();
			
			$cspPrices =self::getQuantityBasedPricing($productId, $userId);
			foreach ($cspPrices as $key => $value) {
				if (''!=$value || !empty($value)) {
					$min =$key;
					add_filter('woocommerce_is_sold_individually', '__return_false');
					break;
				}
			}
			return $min;
		}

		/**
		 * This method returns minimum product quantity according to the csp rules when,
		 * products regular price is not set.
		 */
		private function cspGetDefaultMinimumQty( $productId) {
			$min       =1;
			$cspPrices =self::getQuantityBasedPricing($productId, get_current_user_id());
			foreach ($cspPrices as $key => $value) {
				if (''!=$value || !empty($value)) {
					$min =$key;
					break;
				}
			}
			return $min;
		}

		public function setFlagForUpsellsAndCrossSells( $columns) {
			$this->setInsideShopLoopFlag();
			return $columns;
		}


	   

		/**
		* Decides the hook on basis of woocommerce vaerion to apply custom
		* product price.
		*/
		public function hookWcGetPriceFilter() {
			if (version_compare(WC_VERSION, '3.0', '<')) {
				add_filter('woocommerce_get_price', array($this, 'applyCustomPrice', ), 1, 2);
			} else {
				add_filter('woocommerce_product_get_price', array($this, 'applyCustomPrice', ), 1, 2);
			}
		}

		public function showCartItemPrice( $price, $cart_item, $cart_item_key) {
			$product_id = !empty($cart_item['variation_id']) ? $cart_item['variation_id'] : $cart_item['product_id'];
			$db_price   = self::getDBPrice($product_id, $price, $cart_item['quantity']);
			unset($cart_item_key);
			return $db_price;
		}

		/**
		* Gets the Price for that quantity of Product on basis of Specific
		* pricings.
		* Apply CSP price in cart for quantity more than 1.
		* Update the cart object , with the specific price applicable.
		 *
		* @param object $cart_object WC-Cart-Object.
		*/
		public function applyQuantityPriceInCart() {
			global $woocommerce;
			$cartFlag            = false;
			$qtyUpdated          =false;
			$qtyUpdatedArray     = array();
			$productsSkippingCSP = array();
			$cart                = $woocommerce->cart->get_cart();
			foreach ($woocommerce->cart->get_cart() as $cart_item_key => $cart_item) {
				$cart_product_id = !empty($cart_item['variation_id']) ? $cart_item['variation_id'] : $cart_item['product_id'];
				if (!apply_filters('wdm_csp_apply_quantity_price_in_cart', true, $cart_product_id, $cart_item)) {
					$productsSkippingCSP[] =$cart_product_id;
					continue;
				}
				$priceArray =self::getQuantityBasedPricing($cart_product_id);
				$minQty     =$this->getMinQty($priceArray);
				if ($minQty > $cart_item['quantity']) {
					$cart_item['quantity'] =$minQty;
					$qtyUpdated            =true;
					array_push($qtyUpdatedArray, $cart_item['data']->get_title());
					$woocommerce->cart->set_quantity($cart_item_key, $minQty);
				}
				$price = self::getDBPrice($cart_product_id, self::getProductPrice($cart_item['data']), $cart_item['quantity']);

				if (version_compare(WC_VERSION, '3.0', '<')) {
					$woocommerce->cart->cart_contents[$cart_item_key]['data']->price = $price;
					$cartFlag = true;
				} else {
					$woocommerce->cart->cart_contents[$cart_item_key]['data']->set_price($price);
					$woocommerce->cart->cart_contents[$cart_item_key]['data']->update_meta_data('CSPApplied', true);
					$cartFlag = true;
				}
			}
			if ($qtyUpdated) {
				wc_add_notice(__('Minimum purchase quantity for following product/products is changed : ' . implode(',', $qtyUpdatedArray), 'customer-specific-pricing-for-woocommerce'), 'notice');
			}

			if (!empty($cart) && $cartFlag) {
				$this->appliedPriceInCart = true;
			}
			$this->skipCSPForProductsInCart =$productsSkippingCSP;
		}

		/**
		* Display the total price for that product.
		* Gets the product Id or variation id on basis of Product type.
		* Gets the price applicable for that quantity.
		*/
		public function cspQuantityBasedProductTotal() {
			global $product, $cspFunctions;

			$cspSettings = get_option('wdm_csp_settings');
			if (isset($cspSettings['wdm_csp_hide_price_total']) && 'enable'==$cspSettings['wdm_csp_hide_price_total']) {
				return '';
			}

			$product_id           = $cspFunctions->getProductId($product);
			$current_price_status = true;

			if ($cspFunctions::isPepActive()) {
				$current_price_status = get_post_meta($product_id, '_enable_price', true) === 'yes' ? true : false;
			}

			if (!apply_filters('wdm_csp_show_total_price', $current_price_status, $product)) {
				return;
			}

			$priceTotalHtml = '';
			$allowedHtml    = array('div'	=>	array(
												'class'=>true,
												'id'=>true,
												'style'=>true,  
											),
								 'span'	=>	array(
												'class'=>true,
												'id'=>true,
												'style'=>true,  
												)
								);
			do_action('wdm_before_csp_total_price_html', $product);

			if ($product->is_type('variable')) {
				$db_price       = $product->get_variation_regular_price();
				$db_price       = $this->getUnitPrice($product_id, $db_price);
				$displayPrice   = self::getDisplayPrices($product, $db_price);
				$priceTotalHtml =  sprintf('<div class="csp-hide-product-total" id="product_total_price">%s %s</div>', __('Product Total:', 'customer-specific-pricing-for-woocommerce'), '<span class="price">' . wc_price($displayPrice) . '</span><span class="price-suffix">' . $product->get_price_suffix() . '</span>');
			} else {
				$db_price       = self::cspGetRegularPrice($product_id);
				$db_price       = $this->getUnitPrice($product_id, $db_price);
				$displayPrice   = self::getDisplayPrices($product, $db_price);
				$display        =$this->cspGetDefaultMinimumQty($product_id)==1? 'none':'block';
				$priceTotalHtml =  sprintf('<div id="product_total_price" style="display:' . $display . ';">%s %s</div>', __('Product Total:', 'customer-specific-pricing-for-woocommerce'), '<span class="price">' . wc_price($displayPrice) . '</span><span class="price-suffix">' . $product->get_price_suffix() . '</span>');
			}

			/* Note: Use only allowed html fields from array $allowedHtml*/
			echo wp_kses(apply_filters('wdm_csp_price_html', $priceTotalHtml, $product, $displayPrice), $allowedHtml);

			do_action('wdm_after_csp_total_price_html', $product);
		}

		/**
		* Gets the price applicable for 1 quantity or a unit.
		* Gets the regular prices and the quantity pricing arrays.
		* Gets the price applicable for that quantity.
		 *
		* @param int $product_id Product/variation id.
		* @param float $price Product price in wc db.
		* @return floar $db_price price for that quantity
		*/
		public function getUnitPrice( $product_id, $price = 0) {
			$price = self::cspGetRegularPrice($product_id);
		
			$csp_prices = self::getQuantityBasedPricing($product_id);
			$min        = $this->getMinQty($csp_prices);
			$db_price   = $price;
			if ( 1==$min) {
				$qtyList  = array_keys($csp_prices);
				$db_price = self::getApplicablePriceForQty($qtyList, $csp_prices, $min);
			}
			if (empty($price)) {
				$qtyList   = array_keys($csp_prices);
				$db_price  = self::getApplicablePriceForQty($qtyList, $csp_prices, $min);
				$db_price *= $min;
			}

			return $db_price;
		}

		/**
		* Check if user is logged in and it is the Product page.
		* Enqueue scripts and styles for front-end.
		* Gets the data to be sent for Simple and Vqariable Product.
		* Sends the data for that to the js.
		*/
		public function cspFrontEndScript() {
			// exit;
			global $post;

			if (is_user_logged_in() && is_product() && apply_filters('wdm_csp_load_single_product_assets', true, $post)) {
				$array_passed_to_js = array();
				$product            = wc_get_product($post->ID);

				
				// Strikethrough
				$cspSettings            = get_option('wdm_csp_settings');
				$isStrikeThroughEnabled = isset($cspSettings['enable_striketh']) && 'enable' == $cspSettings['enable_striketh'] ? true : false;
				$isStrikeThroughEnabled = apply_filters('wdm_csp_strikethrough_price_enabled', $isStrikeThroughEnabled, $product);

				wp_enqueue_script('jquery');
				wp_enqueue_style('wdm_csp_product_frontend_css', plugins_url('/css/wdm-csp-product-frontend.css', dirname(__FILE__)), array(), CSP_VERSION);
				wp_enqueue_script('wdm_csp_functions', plugins_url('/js/wdm-csp-functions.js', dirname(__FILE__)), array('jquery'), CSP_VERSION);
				wp_localize_script(
					'wdm_csp_functions',
					'wdm_csp_function_object',
					array(
					'decimal_separator' => wc_get_price_decimal_separator(),
					'thousand_separator' => wc_get_price_thousand_separator(),
					'decimals' => wc_get_price_decimals(),
					'price_format' => get_woocommerce_price_format(),
					'currency_symbol' => get_woocommerce_currency_symbol(),
					'is_strikethrough_enabled' => $isStrikeThroughEnabled,
					)
				);

				if (is_callable(array($product, 'get_type'))) {
					$product_type = $product->get_type();
				} else {
					$product_type = $product->product_type;
				}

				if ('simple' == $product_type) {
					$array_passed_to_js                           = $this->getSimpleProductsArrayTobePassed($product);
					$array_passed_to_js['quantity_discount_text'] = __('Quantity Discount', 'customer-specific-pricing-for-woocommerce');
					wp_enqueue_script('wdm_csp_qty_price', plugins_url('/js/simple-products/customer-quantity-based-price/wdm-csp-frontend-qty-price.js', dirname(__FILE__)), array('jquery'), CSP_VERSION);
					wp_localize_script('wdm_csp_qty_price', 'wdm_csp_qty_price_object', $array_passed_to_js);
				} elseif ('variable' == $product_type) {
					$array_passed_to_js                           = $this->getVariableProductsArrayTobePassed($product);
					$array_passed_to_js['quantity_discount_text'] = __('Quantity Discount', 'customer-specific-pricing-for-woocommerce');
					wp_enqueue_script('wdm_csp_qty_price', plugins_url('/js/variable-products/customer-quantity-based-price/wdm-csp-frontend-qty-price.js', dirname(__FILE__)), array('jquery'), CSP_VERSION);
					wp_localize_script('wdm_csp_qty_price', 'wdm_csp_qty_price_object', $array_passed_to_js);
				}
			}
		}

		/**
		* Gets the simple Product array to be passed to js.
		* Gets the simple product Id.
		* Gets the Display prices i.e, quantity based specific prices for front-end.
		* Gets the regular price to be displayed on front-end.
		* Prepare the data for localization.
		 *
		* @param object $product Simple product object.
		* @return array array of simple product data for js.
		*/
		public function getSimpleProductsArrayTobePassed( $product) {
			global $woocommerce, $cspFunctions;
			$product_id = $cspFunctions->getProductId($product);
			$csp_prices = self::getDisplayPrices($product, self::getQuantityBasedPricing($product_id));
			$qtyList    = array_keys($csp_prices);

			$regular_price = self::getDisplayPrices($product, self::cspGetRegularPrice($product_id));

			return array(
				'qtyList'               => json_encode($qtyList),
				'csp_prices'            => json_encode($csp_prices),
				'regular_price'         => $regular_price,
				'cart_contents_total'   => $woocommerce->cart->cart_contents_total,
				'currency_symbol'       => get_woocommerce_currency_symbol(),
			);
		}

		/**
		* Gets the variable Product array to be passed to js.
		* Gets the variation ids.
		* For each variation ids :
		* Gets the Display prices i.e, quantity based specific prices for
		* front-end.
		* Gets the regular price to be displayed on front-end.
		* Prepare the data for localization.
		 *
		* @param object $product Variable product object.
		* @return array array of variable product data for js.
		*/
		public function getVariableProductsArrayTobePassed( $product) {
			global $woocommerce;
			$cspSettings          = get_option('wdm_csp_settings');
			$csp_prices           = array();
			$regular_prices       = array();
			$qtyList              = array();
			$min                  = array();
			$variation_ids        = $product->get_children();
			$regularPriceHtml     = array();
			$cspPriceDiscriptions = array();

			$showRegularPrice = isset($cspSettings['show_regular_price']) && 'enable' == $cspSettings['show_regular_price'] ? true : false;
			$regularPriceText = '';
			if ($showRegularPrice) {
				$regularPriceText =isset($cspSettings['regular_price_display_text']) ? $cspSettings['regular_price_display_text']:'Regular Price';
			}
			foreach ($variation_ids as $variation_id) {
				$csp_prices[$variation_id] = self::getDisplayPrices($product, self::getQuantityBasedPricing($variation_id));
				$regular_price             = wc_get_product($variation_id)->get_regular_price();
				if (!empty($csp_prices[$variation_id])) {
					$qtyList[$variation_id] = array_keys($csp_prices[$variation_id]);
					$min[$variation_id]     = $this->getMinQty($csp_prices[$variation_id]);
				}


				if (!empty($regular_price) && $regular_price!=$csp_prices[$variation_id][1]) {
					$regular_prices[$variation_id]   = $regular_price;
					$regularPriceHtml[$variation_id] = $this->wdmAddRegularPriceNearPriceTable($regular_prices[$variation_id], $cspSettings, $variation_id);
				}

				$cspPriceDiscriptions[$variation_id] =$this->wdmGetCSPDiscriptionIfAvailable($cspSettings, $variation_id);
			}
			
			
			return array(
				'price_suffix'              => $product->get_price_suffix(),
				'minimum'                   => json_encode($min),
				'qtyList'                   => json_encode($qtyList),
				'csp_prices'                => json_encode($csp_prices),
				'regular_price'             => $regular_prices,
				'cart_contents_total'       => $woocommerce->cart->cart_contents_total,
				'currency_symbol'           => get_woocommerce_currency_symbol(),
				'unavailable_text'          => __('Sorry, this product is unavailable. Please choose a different combination.', 'woocommerce'),
				'more_text'                 => __(' and more :', 'customer-specific-pricing-for-woocommerce'),
				'is_wp_min_max_active'      => defined('WC_MIN_MAX_QUANTITIES'),
				'show_regular_price'        => $showRegularPrice,
				'regular_price_html'        => $regularPriceHtml,
				'csp_price_discriptions'    => $cspPriceDiscriptions,
			);
		}


		/**
		 * Apply Custom Price when user adds the variable product in the cart.
		 * For the variation ids which do not have any specific pricing, apply
		 * regular pricing for those.
		 * For others apply the specific pricing.
		 *
		 * @param array $prices_array Specific pricing array
		 * @param object $product - Woocommerce Product object (WC_Product).
		 * @return array $prices_array specific pricing array.
		 */

		public function applyCSPVariationPrice( $prices_array, $product) {
			if (! is_user_logged_in() || ! apply_filters('wdm_csp_calc_variable_prod_price', true, $product)) {
				return $prices_array;
			}

			global $getCatRecords;
			$cspSettings			= $this->getCSPSettings();
			$discountOnSalePrice	= isset($cspSettings['enable_sale_price_discount'])?$cspSettings['enable_sale_price_discount']:'disabled';
			
			$prices	= $prices_array['regular_price'];
			if ('enable'==$discountOnSalePrice) {
				$prices	= $prices_array['price'];	
			}
			
			$variationIds  	= array_keys($prices);
			$user			= wp_get_current_user();
			$userId 		= $user->ID;
			$userRoles		= $user->roles;
			$userGroups		= $this->getGroupIdsForTheUser($userId);
			
			//Get Product Specific CSP Rules
			$uspRules		= $this->getAllUspRulesForUser($variationIds, $userId);
			$rspRules		= $this->getAllRspRulesForUser($variationIds, $userRoles);
			$gspRules		= $this->getAllGspRulesForUser($variationIds, $userGroups);

			$variationsToSkip 	= array();
			$uspPrices 			= $this->cspCalculatePrices($uspRules, $prices);
			$prices 			= $this->replaceWithCspPrices($prices, $uspPrices);
			$variationsToSkip	= array_keys($uspPrices);
			$rspPrices			= $this->cspCalculatePrices($rspRules, $prices, $variationsToSkip);
			$prices 			= $this->replaceWithCspPrices($prices, $rspPrices);
			$variationsToSkip	= array_merge($variationsToSkip, array_keys($rspPrices));
			$gspPrices			= $this->cspCalculatePrices($gspRules, $prices, $variationsToSkip);
			$prices 			= $this->replaceWithCspPrices($prices, $gspPrices);
			$variationsToSkip	= array_merge($variationsToSkip, array_keys($gspPrices));


			//Get Product-Category Specific CSP Rules when the category specific discount is active
			if ($getCatRecords->featureActive) {
				global $cspFunctions;
				$productCats      	= $cspFunctions->getProductCategories($product);
				$productCats		= $cspFunctions->getArrayColumn($productCats, 'slug');
				
				$uspCatRules		= $this->getAllUspCatRulesForUser($productCats, $userId);
				$rspCatRules		= $this->getAllRspCatRulesForUser($productCats, $userRoles);
				$gspCatRules		= $this->getAllGspCatRulesForUser($productCats, $userGroups);
			
				$uspCatPrices		= $this->cspCalculateCategoryPrices($uspCatRules, $prices, $variationsToSkip);
				$prices 			= $this->replaceWithCspPrices($prices, $uspCatPrices);
				$variationsToSkip	= array_merge($variationsToSkip, array_keys($uspCatPrices));
				$rspCatPrices		= $this->cspCalculateCategoryPrices($rspCatRules, $prices, $variationsToSkip);
				$prices 			= $this->replaceWithCspPrices($prices, $rspCatPrices);
				$variationsToSkip	= array_merge($variationsToSkip, array_keys($rspCatPrices));
				$gspCatPrices		= $this->cspCalculateCategoryPrices($gspCatRules, $prices, $variationsToSkip);
				$prices 			= $this->replaceWithCspPrices($prices, $gspCatPrices);
			}
			
 
			$prices_array['price']	= apply_filters( 'wdm_csp_variations_qty_prices_filter', $prices, $product);
			return $prices_array;
		}

		/**
		 * Apply Custom Price when user adds the simple product in the cart.
		 * If some price is specific set for that product for that user return
		 * that price.
		 * Otherwise return the regular price.
		 * Returns price for single quantity(eg. show price on Shop page).
		 * 
		 * @param float $price Product Price.
		 * @param object $product Product object.
		 * @return float $price custome price.
		 */
		public function applyCustomPrice( $price, $product) {
			global $cspFunctions;
			if (( true== $this->appliedPriceInCart && $this->getInsideShopLoopFlag()== false )
			|| in_array($cspFunctions->getProductId($product), $this->skipCSPForProductsInCart)
			) {
				return $price;
			}

			//If user is not logged in, Original Price should be returned
			if (! is_user_logged_in() || ! apply_filters('wdm_csp_calc_simple_prod_price', true, $product)) {
				return $price;
			}

			if (!is_admin()) {
				$product_id = $cspFunctions->getProductId($product);
				$minQty     =$this->getMinQty(self::getQuantityBasedPricing($product_id));
				$db_price   = self::getDBPrice($product_id, $price, $minQty, get_current_user_id());
				if (isset($db_price) && $db_price) {
					return apply_filters('wdm_apply_csp_product_price', $db_price, $product_id);
				}

				if ($product->is_type('variable')) {
					return 0;
				}
			}

			return $price;
		}

		/**
		* Gets the Quantity based pricing of that product, i.e, quantity
		* pricing pairs.
		* Gets the Regular Product Price.
		* Gets the Price applicable for that quantity for that product.
		 *
		* @param int $product_id Variation id if variable product else
		* product id.
		* @param int/float $price WC-Product-Price saved in database.
		* @param int $qty Quantity of the Product in cart.
		* @param int $user_id User Id
		*/
		public static function getDBPrice( $product_id, $price, $qty = 1, $user_id = null) {
			$product                = wc_get_product($product_id);
			$csp_prices             = self::getQuantityBasedPricing($product_id, $user_id);
			$original_product_price = self::getProductPrice($product);
			$qtyList                = empty($csp_prices)?array():array_keys($csp_prices);
			return apply_filters('wdm_get_csp_product_price', self::getPriceForQuantity($qty, $qtyList, $csp_prices, $original_product_price), $product_id, $price, $qty = 1, $csp_prices, $qtyList);
		}

		/**
		 * Checks whether the CSP price is set for the user.
		 *
		 * @param int $productId    Product ID.
		 * @param int $userId       User ID.
		 *
		 * @return bool Returns true if CSP price is set or false otherwise.
		 */
		public static function isCSPPriceSet( $productId, $userId) {
			$cspPrices = self::getQuantityBasedPricing($productId, $userId);
			if (empty($cspPrices)) {
				$isCSPPriceSet = false;
			} else {
				$isCSPPriceSet = true;
			}

			return $isCSPPriceSet;
		}

		/**
		* Gets the price applicable for that quantity of product
		 *
		* @param int $qty Quantity of Product.
		* @param int $productId Product Id.
		* @param array $qtyList Min-quantity list in specific pricing.
		* @param array $csp_prices Quantity based pricing.
		* @param int/float $original_product_price Regular product price.
		* @return int/float price for that minimum quantity.
		*
		*/
		public static function getPriceForQuantity( $qty, $qtyList, $csp_prices, $original_product_price) {
			if (!empty($qty) && 1==$qty  && !in_array(1, $qtyList)) {
				return $original_product_price;
			}

			if (empty($qtyList) && empty($csp_prices)) {
				return $original_product_price;
			}

			if ($qty >= 1) {
				return self::getApplicablePriceForQty($qtyList, $csp_prices, $qty);
			}
		}

		/**
		* Gets the regular price of the Product.
		 *
		* @param int $product_id Product Id.
		* @return regular price of Product.
		*/
		public static function cspGetRegularPrice( $product_id) {
			$product = wc_get_product($product_id);

			return apply_filters('wdm_csp_regular_price', self::getProductPrice($product), $product_id);
		}

		/**
		 * Gets the Category specific pricing for the product.
		 * Gets the product categories and their slugs.
		 * Gets the category based pricing for user/role/group sorted on
		 * basis of minimum quantities.
		 * Gets the minimum quantity-pricing array.
		 * If directly accesed, i.e, setting regular price for first row.
		 * Returns the category-quantity pricing array.
		 *
		 * @global object $getCatRecords Object of the class to get category specific data
		 * @param object $product Product object in cart.
		 * @param mixed $userId default null
		 * @param mixed $direct default false If accesed directly and not from the getQuantityBasedPricing function
		 * @return if category based price is found, price is returned. Otherwise it returns regular price
		 */
		public static function getCategoryBasedPricing( $product, $userId = null, $direct = false) {
			global $getCatRecords, $cspFunctions;
			
			$userId				= ( null === $userId ) ? get_current_user_id() : $userId;
			$productCats		= array();
			$catSpecificPrices = array();
			$qtyList           = array();

			if ($getCatRecords->featureActive) {
				$productCats       = $cspFunctions->getProductCategories($product);
			}

			//The product does not belong to any category.
			if (!count($productCats)) {
				return array();
			}

			$catArray = $cspFunctions->getArrayColumn($productCats, 'slug');

			$groupCatPrices = false;
			if ($getCatRecords->featureActive) {
				$userCatPrices  = $getCatRecords->getUsersCategoryPricingPairs($userId, $catArray, $product->get_id());
				$roleCatPrices  = $getCatRecords->getRolesCategoryPricingPairs($userId, $catArray, $product->get_id());
				$groupCatPrices =self::getGroupCategoryPricesIfGroupsActive($product, $userId, $catArray, $getCatRecords);	
			}

			if (( isset($userCatPrices) && $userCatPrices ) || ( isset($roleCatPrices) && $roleCatPrices ) || ( isset($groupCatPrices) && $groupCatPrices )) {
				$qtyList = self::getQtyList($userCatPrices, $groupCatPrices, $roleCatPrices);
			}

			if (!isset($qtyList) || count($qtyList) <= 0) {
				return $qtyList;
			}

			$catSpecificPrices = self::getQtyPriceArray($product, $qtyList, $userCatPrices, $roleCatPrices, $groupCatPrices);
			
			if ($direct) {
				$catSpecificPrices = array_map('wc_format_decimal', $catSpecificPrices);
			}

			return $catSpecificPrices;
		}


		/**
		 * This method checks if groups plugin is activated,
		 * or activated networkwide in multisite network.
		 * & returns list of groups category pricing pairs for the user if exist
		 *
		 * @return void
		 */
		public static function getGroupCategoryPricesIfGroupsActive( $product, $userId, $catSlugs, $getCatRecords) {
			global $getCatRecords;
			if (self::wdmGroupsPluginActive()) {
				$groupCatPrices = $getCatRecords->getGroupsCategoryPricingPairs($userId, $catSlugs, $product->get_id());
				return $groupCatPrices;
			}
			return '';
		}

		/**
		 * Checks if groups plugin active or active sitewide in case of multisite setup
		 * returns true when active
		 *
		 * @return bool
		 */
		public static function wdmGroupsPluginActive() {
			global $cspFunctions;
			return $cspFunctions->wdmIsActive('groups/groups.php');
		}
		/**
		* Merge the category specific and product specific for group/
		* role/user on basis of min-quantity.
		* On the basis of quantities sort the pricing array.
		 *
		* @param array $priceArray1 pricing mapping user/role/group specific.
		* @param array $priceArray2 category pricing user/role/group/
		* specific.
		* @return array $cspPrices csp Prices min-quantity sorted
		*/

		public static function mergeProductCatPrices( $priceArray1, $priceArray2) {
			global $cspFunctions;
			$cspPrices = array();
			if (empty($priceArray1) && empty($priceArray2)) {
				return array();
			}

			$qtyArray1 = array_keys($priceArray1);
			$qtyArray2 = array_keys($priceArray2);

			$qtysArray = array_unique(array_merge($qtyArray1, $qtyArray2));


			foreach ($qtysArray as $qty) {
				if ($cspFunctions->hasQtyInPriceArray($qtyArray1, $qty)) {
					$cspPrices[$qty] = $priceArray1[$qty];
				} elseif ($cspFunctions->hasQtyInPriceArray($qtyArray2, $qty)) {
					$cspPrices[$qty] = $priceArray2[$qty];
				}
			}

			ksort($cspPrices);

			return $cspPrices;
		}
		/**
		* Returns true if Quantity is there in quantity pricing array.
		 *
		* @param array $qtysArray MinQuantities array
		* @param int $qty Quantity of Product.
		* @return true if present in array
		*/
		public static function hasQtyInPriceArray( $qtysArray, $qty) {
			if (count($qtysArray) > 0 && in_array($qty, $qtysArray)) {
				return true;
			}
			return false;
		}

		/**
		* Gets the user/role/group specific pricing,quantity pairs for the
		* product.
		* Get the category specific pricing pairs for user/role/group sorted * as per quantities.
		* Merge both the quantities with keys as min-quantities.
		 *
		* @param int $product_id Variation id if variable product else
		* product id.
		* @param mixed $user_id User Id/s. default null.
		* @return array $mergedPrices merged pricing pairs of product
		* specific and category specific pricing with quantities as keys.
		*/
		public static function getQuantityBasedPricing( $product_id, $user_id = null) {
			if (is_user_logged_in() || ! empty($user_id)) {
				//Check if WPML is active
				$product_id = apply_filters('wdm_filter_product_id_for_csp_pricing', $product_id);
				$product    = wc_get_product($product_id);
				$user_id    = empty($user_id) ? get_current_user_id() : $user_id;
				$qtyList    = array();
				$csp_price  = \WdmCSP\WdmWuspGetData::getPriceOfProductForUser($user_id, $product_id);
				$rsp_price  = WrspSimpleProduct\WdmWuspSimpleProductsRsp::getQtyPricePairsOfProductForRole($user_id, $product_id);


				$gsp_price = false;
				$gsp_price =self::wdmGetGspPrice($user_id, $product_id);
				
				if (( isset($csp_price) && $csp_price ) || ( isset($rsp_price) && $rsp_price ) || ( isset($gsp_price) && $gsp_price )) {
					$qtyList = self::getQtyList($csp_price, $gsp_price, $rsp_price);
				}

				if (!isset($qtyList) || count($qtyList) <= 0) {
					$catPrices = self::getCategoryBasedPricing($product, $user_id);
					if (!empty($catPrices) && $product->get_type() != 'variable') {
						$catPrices =self::wdmGetRuleForQuantityOneWhenNotSpecified($product_id, $catPrices);
						ksort($catPrices);
						$catPrices = array_map('wc_format_decimal', $catPrices);
						return apply_filters('wdm_csp_qty_prices_filter', $catPrices, $product);
					} else {
						$qtyList =self::wdmGetRuleForQuantityOneWhenNotSpecified($product_id, $qtyList);
						return apply_filters('wdm_csp_qty_prices_filter', $qtyList, $product);
					}
				}


				$cspPrices = self::getQtyPriceArray($product, $qtyList, $csp_price, $rsp_price, $gsp_price);
				$catPrices = self::getCategoryBasedPricing($product, $user_id);

				$mergedPrices = self::mergeProductCatPrices($cspPrices, $catPrices);

				if (!empty($mergedPrices)) {
					ksort($mergedPrices);
				} else {
					$mergedPrices = $cspPrices;
					ksort($mergedPrices);
				}
				
				$mergedPrices =self::wdmGetRuleForQuantityOneWhenNotSpecified($product_id, $mergedPrices);
				
				$mergedPrices = array_map('wc_format_decimal', $mergedPrices);
				return apply_filters('wdm_csp_qty_prices_filter', $mergedPrices, $product);
			}
		}


		/**
		 * This method checks if csp rule is set for quantity one.
		 * If it is not set, checks if regular price is set, if regular file is set
		 * adds sale or regular price (whichever is lesser) to the index 1 of the merged prices.
		 * sorts & returns the merged price array.
		 *
		 * @param int $product_id
		 * @param array $mergedPrices
		 * @return array $mergedPrices
		 */
		private static function wdmGetRuleForQuantityOneWhenNotSpecified( $product_id, $mergedPrices) {
			global $cspFunctions;
			if (!array_key_exists(1, $mergedPrices)) {
				$regPrice     =get_post_meta($product_id, '_regular_price', true);
				$regPrice     = apply_filters('wdm_csp_regular_price', $regPrice, $product_id);
				$isPriceEmpty = empty($regPrice);
				if (!$isPriceEmpty) {
					$salePrice    =$cspFunctions->wdmGetSalePrice($product_id);
					$isPriceEmpty = empty($salePrice);
					if (!$isPriceEmpty) {
						$regPrice = $regPrice>$salePrice ? $salePrice:$regPrice;
					}
					$mergedPrices[1] =$regPrice;
					ksort($mergedPrices);
				}
			}
			return $mergedPrices;
		}


		/**
		 * Returns gsp_price array if groups plugin is active else return false
		 */
		public static function wdmGetGspPrice( $user_id, $product_id) {
			if (self::wdmGroupsPluginActive()) {
				$gsp_price = \WdmCSP\WdmWuspGetData::getQtyPricePairsOfProductForGroup($user_id, $product_id);
				return $gsp_price;
			}
			return false;
		}
		/**
		* For each minimum quantity get the specific price for the product.
		* For direct access,i.e, for the first row,set price to the regular
		* price.
		 *
		* @param object $product wc Product.
		* @param array $qtyList min-quantity list.
		* @param array $priceArray1 entity 1 pricing-quantity array
		* @param array $priceArray2 entity 2 pricing-quantity array
		* @param array $priceArray3 entity 3 pricing-quantity array
		* @param mixed $direct true if accesed directly.
		* @return array $cspPrices Specific prices for min-quantity.
		*/
		public static function getQtyPriceArray( $product, $qtyList, $priceArray1, $priceArray2, $priceArray3, $direct = false) {
			global $cspFunctions;
			$cspPrices    = array();
			$regularPrice =false;
			$regPrice     = get_post_meta($product->get_id(), '_regular_price', true);
			$regPrice     = apply_filters('wdm_csp_regular_price', $regPrice, $product->get_id());
			$isPriceEmpty =empty($regPrice)?true:false;
			if (!$isPriceEmpty) {
				$regularPrice = $cspFunctions->wdmGetCurrentPrice($product->get_id());
			}
			
			$cspPrices =self::getCalculatedCSPPrices($qtyList, $priceArray1, $priceArray2, $priceArray3, $regularPrice);

			// Setting Price for Quantity 1
			if ($direct && ( !isset($cspPrices) || count($cspPrices) == 0 || !isset($cspPrices[1]) )) {
				$cspPrices[1] = self::getProductPrice($product);
			}

			if (!empty($cspPrices)) {
				ksort($cspPrices);
			}

			return $cspPrices;
		}

		private static function getCalculatedCSPPrices( $qtyList, $priceArray1, $priceArray2, $priceArray3, $regularPrice) {
			global $cspFunctions;
			$cspPrices =array();
			foreach ($qtyList as $qty) {
				if ($cspFunctions->hasQty($priceArray1, $qty)) {
					$qtyPrice =$cspFunctions->priceForQuantity($qty, $priceArray1, $regularPrice);
					if ('NO_PRICE'!==$qtyPrice) {
						$cspPrices[$qty] = $qtyPrice;
					}
				} elseif ($cspFunctions->hasQty($priceArray2, $qty)) {
					$qtyPrice =$cspFunctions->priceForQuantity($qty, $priceArray2, $regularPrice);
					if ('NO_PRICE'!==$qtyPrice) {
						$cspPrices[$qty] = $qtyPrice;
					}
				} elseif ($cspFunctions->hasQty($priceArray3, $qty)) {
					$qtyPrice =$cspFunctions->priceForQuantity($qty, $priceArray3, $regularPrice);
					if ('NO_PRICE'!==$qtyPrice) {
						$cspPrices[$qty] = $qtyPrice;
					}
				}
			}
			return $cspPrices;
		}

		/**
		* Displays the quantity based pricing on front-end.
		* Gets the quantity pricing pairs , get the display prices for same.
		* Gets the regular price to be display.
		* Get the minimum quantity.
		* Displays the quantity based pricing in the table format.
		 *
		* @param float $price Product Price.
		* @param object $product Product object.
		* @return mixed html of table if quantity based pricing is there
		* otherwise the price for product.
		*/
		public function showQuantityBasedPricing( $price, $product) {
			global $wp_query, $cspFunctions;

			if (!is_user_logged_in()) {
				return $price;
			}

			$product_id = $cspFunctions->getProductId($product);
			
			if (!is_product() || $wp_query->queried_object_id != $product_id || !apply_filters('wdm_csp_show_quantity_based_price', true, $product)) {
				return $price;
			}

			//$user_id    = get_current_user_id();
			$csp_prices   = self::getQuantityBasedPricing($product_id);
			$regularPrice = $product->get_regular_price();
			$regularPrice = apply_filters('wdm_csp_regular_price', $regularPrice, $product_id);
			$regularPrice = apply_filters('wdm_csp_convert_price_value', $regularPrice);
			$cspSettings  = get_option('wdm_csp_settings');

			if (isset($csp_prices) && $csp_prices) {
				if (1 === count($csp_prices)) {
					$keys = array_keys($csp_prices);
					// If the CSP price is set only for single quanitity of product.
					if (1 == $keys[0]) {
						// CSP price for single quantity.
						$cspPrice = $csp_prices[$keys[0]];
						// fetching strikethrough setting
						
						$isStrikeThroughEnabled = isset($cspSettings['enable_striketh']) && 'enable' == $cspSettings['enable_striketh'] ? true : false;
						$isStrikeThroughEnabled = apply_filters('wdm_csp_strikethrough_price_enabled', $isStrikeThroughEnabled, $product);

						if ($isStrikeThroughEnabled && $cspPrice < $regularPrice) {
							$price = wc_format_sale_price(self::getDisplayPrice($product, $regularPrice), self::getDisplayPrice($product, $cspPrice)) . $product->get_price_suffix();
						} else {
							$price = wc_price(self::getDisplayPrice($product, $cspPrice)) . $product->get_price_suffix();
						}
						$regPriceDisplay = $cspPrice < $regularPrice ? $this->wdmAddRegularPriceNearPriceTable($regularPrice, $cspSettings, $product_id):'';
						$price           =$regPriceDisplay . $price;
						$price           =$this->wdmGetCSPDiscriptionIfAvailable($cspSettings, $product_id) . $price;
						return apply_filters('wdm_csp_html_price_format', $price, $csp_prices[$keys[0]], $product);
					}
				}
				$table = self::wdmGetQtyDiscountTable($product, $csp_prices);
				$table = $this->wdmAddRegularPriceNearPriceTable($regularPrice, $cspSettings, $product_id) . $table;
				$table = $this->wdmGetCSPDiscriptionIfAvailable($cspSettings, $product_id) . $table;
				return apply_filters('wdm_csp_html_price_format', $table, $product, $csp_prices);
			}
			return $price;
		}


		/**
		 * Creates a Table which contain Quantity based discount rules to display on
		 * product page.
		 */
		public static function wdmGetQtyDiscountTable( $product, $csp_prices) {
			$table    = '<div class = "qty-fieldset"><h1 class="qty-legend"><span>' . __('Quantity Discount', 'customer-specific-pricing-for-woocommerce') . '</span></h1><div class="qty_table_container"><table class = "qty_table">';
			$moreText = __('and more :', 'customer-specific-pricing-for-woocommerce');

			foreach ($csp_prices as $qty => $price) {
                $discount =  $product->get_regular_price()-$price;
                $discount_rate = round(($discount/$product->get_regular_price())*100,2);
				$table .= '<tr>';
				$table .= '<td class = "qty-num">' . $qty . ' ' . $moreText . '</td><td class = "qty-price">' . wc_price(self::getDisplayPrice($product, $price)) . $product->get_price_suffix() . '</td><td>'.$discount_rate.'% OFF</td>';
				$table .= '</tr>';
			}
			$table .= '</table></div></div>';
			return $table;
		}

		/**
		* Returns the min quantity list for all the pricing pairs for that
		* product.
		 *
		* @param array $csp_price User specific quantity pricing pairs.
		* @param array $gsp_price Group specific quantity pricing pairs.
		* @param array $rsp_price Role specific quantity pricing pairs.
		* @return array $qtyList Min Quantity list from Pricing pairs of
		* that product.
		*/

		public static function getQtyList( $csp_price = array(), $gsp_price = array(), $rsp_price = array()) {
			$qtyList = array();

			if (is_array($csp_price) && count($csp_price) > 0) {
				foreach ($csp_price as $csp) {
					if (!in_array($csp->min_qty, $qtyList)) {
						array_push($qtyList, $csp->min_qty);
					}
				}
			}

			if (is_array($rsp_price) && count($rsp_price) > 0) {
				foreach ($rsp_price as $rsp) {
					if (!in_array($rsp->min_qty, $qtyList)) {
						array_push($qtyList, $rsp->min_qty);
					}
				}
			}

			if (is_array($gsp_price) && count($gsp_price) > 0) {
				foreach ($gsp_price as $gsp) {
					if (!in_array($gsp->min_qty, $qtyList)) {
						array_push($qtyList, $gsp->min_qty);
					}
				}
			}

			return $qtyList;
		}

		/**
		* Gets the minimum quantity from the minimum quantities pricing array
		 *
		* @param array $priceArray quantity pricing array
		* @return $min minimum quantity.
		*/
		public function getMinQty( $priceArray) {
			if (empty($priceArray) || count($priceArray) == 0) {
				return false;
			}
			$keys = array_keys($priceArray);
			$min  = $keys[0];
			foreach ($keys as $qty) {
				if ($qty < $min) {
					$min = $qty;
				}
			}

			return $min;
		}

		// public static function priceForQuantity($quantity, $priceArray, $regular_price)
		// {
		//     if (count($priceArray) == 0) {
		//         return false;
		//     }

		//     foreach ($priceArray as $a) {
		//         if ($a->min_qty == $quantity) {
		//             if ($a->price_type == 2) {
		//                 return ($regular_price) - (round(($a->price * $regular_price), wc_get_price_decimals()) / 100);
		//             }
		//             return $a->price;
		//         }
		//     }
		// }

		/**
		* Returns the price to be applied for a quantity range.
		* If quantity doesn't fits in range, apply the price for the maximum * in quantity list.
		 *
		* @param array $qtyList Min-quantity list in specific pricing.
		* @param array $csp_prices Quantity based pricing.
		* @param int $qty Quantity of Product.
		* @return int/float CSP Price to be applied on basis of quantity
		* range.
		*/
		public static function getPriceInQtyRange( $qtyList, $csp_prices, $qty) {
			$qtyListSize = count($qtyList);
			for ($i = 0; $i < $qtyListSize; $i++) {
				$next = $i + 1;
				if ($qty > $qtyList[$i]) {
					if ($next != $qtyListSize && $qty < $qtyList[$next]) {
						return $csp_prices[$qtyList[$i]];
					}

					if ($next == $qtyListSize) {
						return $csp_prices[$qtyList[$i]];
					}
				}
			}
		}

		/**
		* Returns the price applicable for that minimum quantity.
		 *
		* @param array $qtyList Min-quantity list in specific pricing.
		* @param array $csp_prices Quantity based pricing.
		* @param int $qty Quantity of Product.
		* @return int/float price for that minimum quantity.
		*/
		public static function getApplicablePriceForQty( $qtyList, $csp_prices, $qty) {
			if (in_array($qty, $qtyList)) {
				return $csp_prices[$qty];
			} else {
				return self::getPriceInQtyRange($qtyList, $csp_prices, $qty);
			}
		}

		public static function applyTaxOnPrices( $product, $prices) {
			if (is_array($prices)) {
				$new_prices = array();
				foreach ($prices as $key => $price) {
					if (is_numeric($price)) {
						if (version_compare(WC_VERSION, '3.0', '<')) {
							$new_prices[$key] = $product->get_price_including_tax(1, $price);
						} else {
							$new_prices[$key] = wc_get_price_including_tax($product, array('price' => $price));
						}
					} else {
						$new_prices[$key] = 0;
					}
				}
				return $new_prices;
			}

			if (is_numeric($prices)) {
				if (version_compare(WC_VERSION, '3.0', '<')) {
					return $product->get_price_including_tax(1, $prices);
				} else {
					return wc_get_price_including_tax($product, array('price' => $prices));
				}
			}
		}

		/**
		* Gets the prices to be displayed on front-end.
		* Regular prices and specific prices on basis of quantities
		 *
		* @param object $product Product object.
		* @param array $prices quantity based prices/regular price.
		*/
		public static function getDisplayPrices( $product, $prices) {
			if (is_array($prices)) {
				$new_prices = array();
				foreach ($prices as $key => $price) {
					if (is_numeric($price)) {
						$new_prices[$key] = self::getDisplayPrice($product, $price);
					}
				}
				return $new_prices;
			}
			if (is_numeric($prices)) {
				return self::getDisplayPrice($product, $prices);
			}
		}

		/**
		* Gets the price to be displayed on front-end.
		* Regular price and specific price on basis of quantity
		 *
		* @param object $product Product object.
		* @param array $price quantity based price/regular price.
		* @param int $qty quantity of product
		*/
		public static function getDisplayPrice( $product, $price, $qty = 1) {
			$price = round($price, wc_get_price_decimals());

			if (version_compare(WC_VERSION, '3.0', '<')) {
				return $product->get_display_price($price, $qty);
			} else {
				return wc_get_price_to_display($product, array(
					'price' => $price,
					'qty'   => $qty,
					));
			}
		}

		/**
		* Gets the price set in WC database.
		 *
		* @param object $productObject Product object.
		*/
		public static function getProductPrice( $productObject) {
			if (version_compare(WC_VERSION, '3.0', '<')) {
				return $productObject->price ;
			}
			//With WC 2.7 when we pass context parameter as edit, we get unfiltered value
			return $productObject->get_price('edit');
		}

		/**
		 * Callback to detemine whether CSP price should be shown as sale price.
		 *
		 * @param bool    $onsale
		 * @param object  $product  Product object.
		 *
		 * @return bool Returns true if CSP price should be shown as sale price,
		 *              false otherwise.
		 */
		public function showCSPAsSalePrice( $onSale, $product) {
			global $cspFunctions;
			
			$userId           = get_current_user_id();
			$cspDontCheckSale = is_admin() && !is_ajax();
			$cspDontCheckSale = apply_filters('wdm_csp_check_sale', $cspDontCheckSale, $product, $userId);
			
			if (empty($userId) || $cspDontCheckSale) {
				return $onSale;
			}

			if ($product->get_type()=='variable') {
				$variations       =$product->get_visible_children();
				$variationsPrices =$product->get_variation_prices();
				//if any of the variation is on sale variable product should display sale badge.
				foreach ($variations as $vId) {
					if ($this->variationSaleEnabled($variationsPrices, $vId)) {
							$onSale =true;
							break;
					}
				}
			}
			
			$productId              = $cspFunctions->getProductId($product);
			$cspSettings            = get_option('wdm_csp_settings');
			$isStrikeThroughEnabled = isset($cspSettings['enable_striketh']) && 'enable' == $cspSettings['enable_striketh'] ? true : false;
			$isStrikeThroughEnabled = apply_filters('wdm_csp_strikethrough_price_enabled', $isStrikeThroughEnabled, $product);
			$isCSPPriceSet          = self::isCSPPriceSet($productId, $userId);
			
			if (true == $isCSPPriceSet) { // Check if CSP price is set.
				if ($isStrikeThroughEnabled) {
					// Fetch the CSP price for single quantity.
					$csp_single_qty_price = self::getDBPrice($productId, $product->get_regular_price());
					$onSale               = $csp_single_qty_price >= floatval($product->get_regular_price()) ? false : true;
				}
			}
			
			return apply_filters('wdm_csp_is_on_sale', $onSale, $product, $userId);
		}

		/**
		 * Determines whether the 'Product Total' should be shown on product
		 * page. The product total is shown only if the product is 'simple' or
		 * 'variable' product.
		 *
		 * @param $shouldShow   bool    True or false.
		 * @param $product      object  Product object.
		 *
		 * @return bool     Returns false if the product is neither 'simple' nor
		 *                  'variable'.
		 */
		public function shouldShowTotalPrice( $shouldShow, $product) {
			$productType = $product->get_type();

			if ('simple' != $productType && 'variable' != $productType) {
				$shouldShow = false;
			}
			return $shouldShow;
		}
	}
}
