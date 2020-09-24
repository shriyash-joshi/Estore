<?php

namespace WdmCSP;

if (!class_exists('WdmCspProductTables')) {
	

	class WdmCspProductTables {
	
		
		public function __construct() {
			add_filter('wc_product_table_data_price', array($this, 'addCSPWrapperToPriceHtml'), 99, 2);
			add_action('wc_product_table_after_get_table', array($this, 'enqueScriptsForProductTables'), 98, 1);
			add_action('wc_product_table_after_get_table', array($this, 'addcspModalToDisplayPrices'), 99, 1);
			//ajax call to retrive csp quantity based prices
			add_action('wp_ajax_pt_get_csp_price_for_product_id', array($this, 'getCspPriceArrayForProduct'));
			include_once(CSP_PLUGIN_URL . '/includes/class-wdm-apply-usp-product-price.php');
		}

		/**
		 * Only gets called for simple & variation product types
		 */
		public function addCSPWrapperToPriceHtml( $priceHtml, $product) {
			if (wp_doing_ajax()) {
				if ($product->get_type()=='variable') {
					$prices = $this->getVariationPrices($product->get_variation_prices());
					$priceHtml = !empty($prices)?wc_price(min($prices)) . '-' . wc_price(max($prices)):$priceHtml;
				} else {
					$price = apply_filters('wdm_get_csp_for_product_qty', $product->get_price(), $product->get_id(), 1, get_current_user_id());
					if ($price>$product->get_price()) {
						$priceHtml = wc_price($price);
					} else {
						$product->set_price($price);
						$priceHtml = $product->get_price_html();
					}
				}
			}
			$html="<div class='product-table-price' name='" . $product->get_id() . "'>" . $priceHtml . '</div>';
			return $html;
		}

		private function getVariationPrices( $variationPrices) {
			$prices = $variationPrices['price'];
			foreach ($prices as $vid => $price) {
				$prices[$vid] = apply_filters('wdm_get_csp_for_product_qty', $price, $vid, 1, get_current_user_id());
			}
			return $prices;
		}

		public function getCspPriceArrayForProduct() {
			$postArray = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

			if (empty($postArray['pid'])) {
				echo json_encode(array('error' =>true));
				die();
			}
			$productId = absInt($postArray['pid']);
			$productObject = wc_get_product($productId);
			$cspPrices = \WuspSimpleProduct\WuspCSPProductPrice::getQuantityBasedPricing($productId, get_current_user_id());
			$cspPrices = self::excludeTaxFromPrices($productObject, $cspPrices);
			$regularPrice = $productObject->get_price();
			$title  =   isset($postArray['productName'])?$postArray['productName']:'';
			$productPrices = array('id'=>$productId, 'title' => $title, 'prices'=>$cspPrices, 'regularPrice'=> $regularPrice);
			echo json_encode($productPrices);
			die();
		}


		/**
		 * Undocumented function
		 *
		 * @since 4.3.0
		 * @param [type] $productTableInstance
		 * @return void
		 */
		public function enqueScriptsForProductTables( $productTableInstance) {
			if (\in_array('price', $productTableInstance->args->columns)) {
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
					)
				);
				
				
				wp_enqueue_script(
					'wdm_barn2_product_table',
					plugins_url('js/product-tables/wdm-barn2-product-table-simple.js', dirname(__FILE__)),
					array('jquery', 'jquery-ui-tooltip'),
					CSP_VERSION
				);

				wp_localize_script(
					'wdm_barn2_product_table',
					'wdm_csp_pt_object',
					array(
						'ajax_url' => admin_url('admin-ajax.php'),
					)
				);
				
				wp_enqueue_style(
					'tooltip-style-pt-page',
					plugins_url('/css/product-tables/price-table-modal-style.css', dirname(__FILE__)),
					array(),
					CSP_VERSION
				);
			}
		}

		public function addcspModalToDisplayPrices( $productTableInstance) {
			if (!in_array('price', $productTableInstance->args->columns)) {
				return 0;
			}
			?>
			<!-- The Modal -->
			<div id="cspPriceModal" class="csp-modal">
				<!-- Modal content -->
				<div class="csp-modal-content">
					<div class="csp-modal-header">
						<span class="csp-close">&times;</span>
						<strong><?php esc_html_e('Quantity Based Special Pricing Options', 'customer-specific-pricing-for-woocommerce'); ?></strong>
					</div>
					<div class="csp-modal-body">
						<div class="csp-loader"></div>
						<div class="csp-modal-price-table">

						</div>
					</div>
				</div>
			</div>
			<?php
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
	}
}
