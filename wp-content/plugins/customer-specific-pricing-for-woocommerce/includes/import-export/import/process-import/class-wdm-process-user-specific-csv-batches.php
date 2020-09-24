<?php

namespace cspImportExport\cspImport;

//File for processing batches for import in CSP
include_once(plugin_dir_path(__FILE__) . 'class-wdm-abstract-process-csv-batches.php');

/**
* Class for importing csv of user-specific-pricing
*/
class WdmProcessUserSpecificCSVBatches extends WdmAbstractProcessCSVBatches {


	private $fetchedUsers = array();
	/**
	* Ajax callback for processing batch for import
	*/
	public function __construct() {
		add_action('wp_ajax_import_customer_specific_file', array($this, 'processBatch'));
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
	 *
	 * @SuppressWarnings(PHPMD.UnusedLocalVariable) Some Local Variables are Extracted from Abstract Object rowStatusMessages
	 */
	protected function processRow( $productId, $sku, $flag, $price, $priceType, $status, $batchNumber, $rowData, $skipCnt, $updateCnt, $insertCnt) {
		//extracts status messages $userDoesNotExist,$recordInserted etc.
		$rowStatusMessages = $this->rowStatusMessages;

		global $wpdb, $subruleManager, $cspFunctions;

		$wdmUserMapping  = $wpdb->prefix . 'wusp_user_pricing_mapping';
		$wdmUsers        = $wpdb->prefix . 'users';
		$updatePrice = 0;
		$user = trim($rowData[ 1 ]);
		$minQty = trim($rowData[ 2 ]);
		$isPriceEmpty = empty(get_post_meta($productId, '_regular_price', true))? true:false;
		if (( $isPriceEmpty && 2==$priceType )) {
			$skipCnt++;
			$status=$discountOnEmptyPrice;
			return $this->wdmGetUspStatusReturnArray($status, $skipCnt, $insertCnt, $updateCnt);
		}
		

		//check all values valid or not
		if ( false!== $flag && false!== $priceType ) {
			$minQty = (int) $minQty;
			//check if product exists or not
			if ($this->isValidProduct($productId)) {
				if (!isset($this->fetchedUsers[$user])) {
					//get user id
					if (!is_multisite()) {
						$this->fetchedUsers[$user] = $wpdb->get_var($wpdb->prepare('SELECT id FROM ' . $wpdb->prefix . 'users where user_login=%s', $user));
					} else {
						$capabilities=$wpdb->prefix . 'capabilities';
						$this->fetchedUsers[$user] = $wpdb->get_var($wpdb->prepare('SELECT id FROM (SELECT id,user_login FROM ' . $wpdb->base_prefix . 'users u JOIN ' . $wpdb->base_prefix . 'usermeta um 
						ON u.id=um.user_id WHERE um.meta_key=%s) users WHERE users.user_login=%s', $capabilities, $user));
					}
				}

				$getUserId = $this->fetchedUsers[$user];
			   
				if (null== $getUserId) {
					 $status = $rowStatusMessages['userDoesNotExist'];
					 $skipCnt ++;
				} else {
					//Update price for existing one
					$result = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . 'wusp_user_pricing_mapping where product_id=%d and user_id=%d and min_qty=%d', $productId, $getUserId, $minQty));
					if (null!= $result) {
						if (( $result->batch_numbers <= $batchNumber ) &&
								( $price != $result->price || $priceType != $result->flat_or_discount_price )
							) {
							$updatePrice = $wpdb->update(
								$wdmUserMapping,
								array(
								'price' => $price,
								'flat_or_discount_price' => $priceType,
								'batch_numbers' => $batchNumber,
								),
								array(
								'id'    =>  $result->id,
								),
								array(
								'%f',
								'%d',
								'%d'
								),
								array(
								'%d',
								)
							);
							if (0!= $updatePrice) {
								$status = $rowStatusMessages['recordUpdated'];
								$subruleManager->deactivateSubrulesOfCustomerForProduct($productId, $getUserId, $minQty);
								$updateCnt ++;
							} else {
								$status = $rowStatusMessages['recordExists'];
								$skipCnt ++;
							}
						} elseif ($result->batch_numbers > $batchNumber) {
							$status = $rowStatusMessages['recordSkipped'];
							$skipCnt ++;
						} else {
							$status = $rowStatusMessages['recordExists'];
							$skipCnt ++;
						}
					} else {
						//add entry in our table
						if ($wpdb->insert(
							$wdmUserMapping,
							array(
							'product_id' => $productId,
							'user_id'   => $getUserId,
							'price' => $price,
							'flat_or_discount_price' => $priceType,
							'batch_numbers' => $batchNumber,
							'min_qty'   => $minQty,
							),
							array(
							'%d',
							'%d',
							'%s',
							'%d',
							'%d'
							)
						)) { //if record inserted
							$status = $rowStatusMessages['recordInserted'];
							$insertCnt ++;
						} else {
							$status = $rowStatusMessages['couldNotInsert'];
							$skipCnt ++;
						}
					}
				}
			} else {
				$status = $rowStatusMessages['productDoesNotExist'];
				$skipCnt ++;
			}
		} else {
			$status = $rowStatusMessages['invalidFieldValues'];
		}
		unset($sku);
		return $this->wdmGetUspStatusReturnArray($status, $skipCnt, $insertCnt, $updateCnt);
	}

	/**
	 * Prapare import row status data in array format and returns it.
	 *
	 * @since 4.3.0 - modified
	 * @param [type] $status
	 * @param [type] $skipCnt
	 * @param [type] $insertCnt
	 * @param [type] $updateCnt
	 * @return void
	 */
	private function wdmGetUspStatusReturnArray( $status, $skipCnt, $insertCnt, $updateCnt) {
		$importStatus= array(
			'record_status' =>  $status,
			'counts'    =>  array(
				'skip_cnt'      =>  $skipCnt,
				'insert_cnt'    =>  $insertCnt,
				'update_cnt'    =>  $updateCnt,
			),
		);
		return $importStatus;
	}
}

new WdmProcessUserSpecificCSVBatches();
