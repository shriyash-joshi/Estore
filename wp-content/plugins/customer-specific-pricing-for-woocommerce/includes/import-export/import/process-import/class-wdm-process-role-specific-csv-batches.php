<?php

namespace cspImportExport\cspImport;

//File for processing batches for import in CSP
include_once(plugin_dir_path(__FILE__) . 'class-wdm-abstract-process-csv-batches.php');

/**
* Class for importing csv of role-specific-pricing
*/
class WdmProcessRoleSpecificCSVBatches extends WdmAbstractProcessCSVBatches {


	//private $fetchedUsers = array();
	/**
	* Ajax callback for processing batch for import
	*/
	public function __construct() {
		add_action('wp_ajax_import_role_specific_file', array($this, 'processBatch'));
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
	 * @SuppressWarnings(PHPMD.UnusedLocalVariable) Some Local Variables are Extracted from Abstract Object rowStatusMessages
	 */
	protected function processRow( $productId, $sku, $flag, $price, $priceType, $status, $batchNumber, $rowData, $skipCnt, $updateCnt, $insertCnt) {

		$rowStatusMessages = $this->rowStatusMessages;

		global $wpdb, $subruleManager;

		$wdmRoleMapping  = $wpdb->prefix . 'wusp_role_pricing_mapping';

		$updatePrice = 0;
		$role = strtolower(trim($rowData[ 1 ]));
		$minQty = trim($rowData[ 2 ]);
		$isPriceEmpty = empty(get_post_meta($productId, '_regular_price', true))? true:false;
		if (( $isPriceEmpty && 2 ==$priceType )) {
			$skipCnt++;
			return array(
				'record_status' =>  $discountOnEmptyPrice,
				'counts'    =>  array(
					'skip_cnt'      =>  $skipCnt,
					'insert_cnt'    =>  $insertCnt,
					'update_cnt'    =>  $updateCnt,
				),
			);
		}
		//check all values valid or not
		if (false!== $flag && false!== $priceType) {
			//check if product exists or not
			if ($this->isValidProduct($productId)) {
				$minQty = (int) $minQty;
				// Check if Role exists
				if ($GLOBALS['wp_roles']->is_role($role)) {
					//Update price for existing one
					$result = $wpdb->get_row($wpdb->prepare('SELECT * from ' . $wpdb->prefix . 'wusp_role_pricing_mapping WHERE role = %s AND product_id = %d AND min_qty = %d', $role, intval($productId), $minQty));
					if ( null!= $result) {
						if (( $result->batch_numbers <= $batchNumber ) &&
								( $price != $result->price || $priceType != $result->flat_or_discount_price )
							) {
							$updatePrice = $wpdb->update(
								$wdmRoleMapping,
								array(
								'price' => $price,
								'flat_or_discount_price' => $priceType,
								'batch_numbers' => $batchNumber,
								),
								array(
								'id' => $result->id,
								),
								array(
								'%f',
								'%d',
								'%d',
								),
								array(
								'%d',
								)
							);
							if (0!= $updatePrice) {
								$status = $rowStatusMessages['recordUpdated'];
								$subruleManager->deactivateSubrulesOfRoleForProduct($productId, $role, $minQty);
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
							$wdmRoleMapping,
							array(
							'product_id' => $productId,
							'role'   => $role,
							'price' => $price,
							'flat_or_discount_price' => $priceType,
							'batch_numbers' => $batchNumber,
							'min_qty'   => $minQty,
							),
							array(
							'%d',
							'%s',
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
				} else {
					$status = $rowStatusMessages['roleDoesNotExist'];
					$skipCnt ++;
				}
			} else {
				$status = $rowStatusMessages['productDoesNotExist'];
				$skipCnt ++;
			}
		} else {
			$status = $rowStatusMessages['invalidFieldValues'];
		}
		unset($sku);
		return array(
			'record_status' =>  $status,
			'counts'    =>  array(
				'skip_cnt'      =>  $skipCnt,
				'insert_cnt'    =>  $insertCnt,
				'update_cnt'    =>  $updateCnt,
			),
		);
	}
}

new WdmProcessRoleSpecificCSVBatches();
