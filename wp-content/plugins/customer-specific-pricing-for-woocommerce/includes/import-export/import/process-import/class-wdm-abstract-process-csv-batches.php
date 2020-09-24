<?php

namespace cspImportExport\cspImport;

/**
* Class for processing batches for import in CSP.
*/
abstract class WdmAbstractProcessCSVBatches {


	public $discountOptions = array();

	public $rowStatusMessages = array();

	/**
	 * Initiates Batch Processing. It does basic validations and if validations are passed, reads batch row by row and processes the data.
	 * After processing the batch completely, it returns the json array, so that status can be shown on the screen.
	 */
	public function processBatch() {
		
		set_time_limit(0);
		wp_suspend_cache_addition(true);

		$nonce              = !empty($_REQUEST['_wp_import_nonce'])?sanitize_text_field($_REQUEST['_wp_import_nonce']):'';
		$nonce_verification = wp_verify_nonce($nonce, 'import_nonce');

		//Override nonce verification for extending import functionality in any third party extension
		$nonce_verification = apply_filters('csp_import_nonce_verification', $nonce_verification);
		
		if (! $nonce_verification) {
			 echo 'Security Check';
			 exit;
		} else {
			//Allow only admin to import csv files
			$capabilityToImport = apply_filters('csp_import_allowed_user_capability', 'manage_options');
			$can_user_import    = apply_filters('csp_can_user_import_csv', current_user_can($capabilityToImport));

			if (!$can_user_import) {
				echo 'Security Check';
				exit;
			}

			$upload   = wp_upload_dir();
			$batchDir = $upload['basedir'] . '/importCsv';
			$csvFile  = '';
			$fileName = !empty($_POST['file_name'])?sanitize_file_name($_POST['file_name']):'';
			if (!empty($fileName) && file_exists($batchDir . '/' . $fileName)) {
				$csvFile = $batchDir . '/' . $fileName;
			} else {
				echo 'Invalid File';
				exit();
			}

			$batchNumber = 0;
			$batchNumber = isset($_POST['batch_number'])? sanitize_text_field($_POST['batch_number']):'';
			if (absint($batchNumber) <= 0) {
					echo 'Invalid Batch Number';
					exit();
			}

			$this->discountOptions = array(
				'0'=>'-',
				'1'=>__('Flat', 'customer-specific-pricing-for-woocommerce'),
				'2'=>'%'
			);

			$this->rowStatusMessages = array(
				'invalidFieldValues'    => __('Flat Price or % or Min Qty Invalid', 'customer-specific-pricing-for-woocommerce'),
				'productDoesNotExist'   => isset($_POST['unique_id']) && 'sku' == $_POST['unique_id'] ? __('Invalid SKU', 'customer-specific-pricing-for-woocommerce') : __('Invalid Product Id', 'customer-specific-pricing-for-woocommerce'),
				'couldNotInsert'        => __('Record could not be inserted', 'customer-specific-pricing-for-woocommerce'),
				'recordInserted'        => __('Record Inserted', 'customer-specific-pricing-for-woocommerce'),
				'recordUpdated'         => __('Record Updated', 'customer-specific-pricing-for-woocommerce'),
				'recordExists'          => __('Record already exists', 'customer-specific-pricing-for-woocommerce'),
				'userDoesNotExist'      => __('User does not exist', 'customer-specific-pricing-for-woocommerce'),
				'recordSkipped'         => __('Record Skipped', 'customer-specific-pricing-for-woocommerce'),
				'roleDoesNotExist'      => __('Role does not exist', 'customer-specific-pricing-for-woocommerce'),
				'groupDoesNotExist'     => __('Group does not exist', 'customer-specific-pricing-for-woocommerce'),
				'discountOnEmptyPrice'  => __('% Discount not allowed', 'customer-specific-pricing-for-woocommerce'),
			);

			$this->wdmProcessImport($csvFile, $batchNumber);
		}
	}


	private function wdmProcessImport( $csvFile, $batchNumber) {
		global $wpdb,$ruleManager,$cspFunctions;
		$getfile = fopen($csvFile, 'r');
		if (false !== $getfile) {
			$updateCnt    = 0;
			$insertCnt    = 0;
			$skipCnt      = 0;
			$count        =0;
			$responseData = array();
			$recordsRead  = array();
			$resultsArray = array();
			while (false !== ( $data      = fgetcsv($getfile, 0, ',') )) {
				$count++;
				$result  = $data;
				$str     = implode(',', $result);
				$rowData = array_map('trim', explode(',', $str));
				//$columnCount             = count($rowData);
				// Id on which the prices are imported (sku/product id)
				$primaryId    = $rowData[ 0 ];
				$minQty       = $rowData[ 2 ];
				$flatPrice    = $rowData[ 3 ];
				$percentPrice = isset($rowData[ 4 ]) ? $rowData[ 4 ] : 0;
				$price        = 0;
				$status       = null;
				$sku          = '';
				$hasTranslations = false;
				$post 		  = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
				if (isset($post['unique_id']) && !empty($primaryId)) {
					$productId = $this->getProductIdOfImportById($post['unique_id'], $primaryId);
					$hasTranslations = $productId!=$primaryId ? apply_filters('wdm_check_for_product_translations', $productId):false;
					$sku       = 'sku' != $post['unique_id'] ? true: $primaryId;
				}

				if (empty($productId)) {
					$status = $this->rowStatusMessages['productDoesNotExist'];
				}

				$flag = $this->shouldRowBeProcessed($flatPrice, $percentPrice, $minQty);

				if (false===$flag) {
					$skipCnt++;
						
					$status = $this->rowStatusMessages['invalidFieldValues'];
				}

				$priceType = $this->selectFlatPriceOrDiscount($flatPrice, $percentPrice);

				if ( 1==$priceType) {
					$price = $flatPrice;
				}

				if ( 2==$priceType) {
					$price = $percentPrice;
				}

				$recordsRead[$count] = $this->processRow($productId, $sku, $flag, $price, $priceType, $status, $batchNumber, $rowData, $skipCnt, $updateCnt, $insertCnt);
				if (!empty($hasTranslations)) {
					foreach ($hasTranslations as $translation) {
						$this->processRow($translation, $sku, $flag, $price, $priceType, $status, $batchNumber, $rowData, $skipCnt, $updateCnt, $insertCnt);
					}
				}
				$result[]            = $recordsRead[$count]['record_status'];
				$resultsArray[]      = $result;
				$skipCnt             = $recordsRead[$count]['counts']['skip_cnt'];
				$insertCnt           = $recordsRead[$count]['counts']['insert_cnt'];
				$updateCnt           = $recordsRead[$count]['counts']['update_cnt'];

				unset($recordsRead[$count]['skip_count']['counts']);

				$wpdb->flush();
			}//end of while

			fclose($getfile);
			unlink($csvFile);
			$ruleManager->setUnusedRulesAsInactive();
			$csvName ='batch' . $batchNumber;
			$cspFunctions->wdmSaveCSV($csvName, $resultsArray, 'cspReports');
			// $responseData['records'] =$recordsRead;
			$responseData['rows_read']  = $count;
			$responseData['insert_cnt'] = $insertCnt;
			$responseData['update_cnt'] = $updateCnt;
			$responseData['skip_cnt']   = $skipCnt;
			echo json_encode($responseData);
			die();
		}
	}


	/**
	 * Returns the porduct id for the selected import by type. i.e according to the import using selected by user (sku/product id)
	 *
	 * @param  string $uniqueId     Selection by user (sku/product id)
	 * @param  int $productId       Product Id of row
	 * @return array                Complete information about the row after proceesing.
	 */
	protected function getProductIdOfImportById( $uniqueId, $productId) {
		return apply_filters('wdm_csp_import_product_id', 'sku' == $uniqueId  ? wc_get_product_id_by_sku($productId) : (int) $productId);
	}

	/**
	 * Reads the row data. Checks in the database if current row pair already exists. If it does not exist, then adds that pair in database.
	 *
	 * @param  int $productId       Product Id of row
	 * @param  boolean $flag        if flag is false, row is ommitted
	 * @param  mix $price           This can either be integer or float. It is a flat or percentage price to be applied
	 * @param  int $priceType       if 1, then it is a flat price. If 2, then it is a percentage discount. If false, row is ommitted
	 * @param  string $status       Row status
	 * @param  int $batchNumber     Current Batch Number
	 * @param  array $rowData       Current row data
	 * @param  int $skipCnt         Number of rows skipped till now
	 * @param  int $updateCnt       Number of rows updated till now
	 * @param  int $insertCnt       Number of rows inserted till now
	 * @return array                Complete information about the row after proceesing.
	 */
	abstract protected function processRow( $productId, $sku, $flag, $price, $priceType, $status, $batchNumber, $rowData, $skipCnt, $updateCnt, $insertCnt);

	/**
	 * Decides whether csv row should be processed or not depends on pricetype and * min quantity
	 *
	 * @param  float $flatPrice    Flat Price mentioned in CSV row
	 * @param  float $percentPrice Percentage discount mentioned in CSV row
	 * @param  int $minQty       Min Quantity associated with row
	 * @return boolean               If Flat price, Percentage (Discount) Price and Min Qty values are valid, return true. Else returns false
	 */
	protected function shouldRowBeProcessed( $flatPrice, $percentPrice, $minQty) {
		$priceType = static::selectFlatPriceOrDiscount($flatPrice, $percentPrice);

		if ( false === $priceType) {
			return false;
		}

		if (empty($minQty)) {
			return false;
		}

		if (!is_numeric($minQty)) {
			return false;
		}

		if (is_float($minQty + 0)) {
			return false;
		}

		$minQty = (int) $minQty;

		if ($minQty > 0) {
			return true;
		}

		return false;
	}

	/**
	* Select the flat price or discount percent price.
	 *
	* @param  float $flatPrice    Flat Price mentioned in CSV row
	* @param  float $percentPrice Percentage discount mentioned in CSV row
	* @return bool/int return 1 for flat price and 2 for discount otherwise false.
	*/
	protected function selectFlatPriceOrDiscount( $flatPrice, $percentPrice) {

		if (empty($flatPrice) && empty($percentPrice)) {
			return false;
		}

		if (!empty($flatPrice)) {
			if (is_numeric($flatPrice) && $flatPrice >= 0) {
				return 1;
			}
		}

		if (!empty($percentPrice)) {
			if (is_numeric($percentPrice)) {
				if ($percentPrice >= 0 && $percentPrice <= 100) {
					return 2;
				}
			}
		}

		return false;
	}

	/**
	* Checks if it is a valid product.
	 *
	* @param int $productId Product Id.
	* @return bool true if valid else false.
	*/
	protected function isValidProduct( $productId) {
		$productObject = wc_get_product($productId);
		if (false!== $productObject) {
			$productType = $productObject->get_type();
			if ('simple'==$productType || 'variation' == $productType) {
				return true;
			}
		}
		return false;
	}
}
