<?php

namespace WuspDeleteData;

class WdmWuspDeleteData {
	/**
	* Deleting/Syncing pricing for products when they are deleted.
	* Deletes the subrules associated with that product.
	* Deletes the pricing_mapping entries for the deleted product for all user/group
	* /role
	* Sets the Unused rules status as inactive.
	* Deletes the rule whose total_subrules are 0
	 *
	* @param int $productId deleted product Id.
	*/
	public static function deleteMappingForProducts( $productId) {
		global $wpdb, $subruleManager, $ruleManager;
		$subruleManager->deleteSubruleIdsForProduct($productId);
		$wpdb->delete($wpdb->prefix . 'wusp_user_pricing_mapping', array('product_id' => $productId), array('%d'));
		$wpdb->delete($wpdb->prefix . 'wusp_role_pricing_mapping', array('product_id' => $productId), array('%d'));
		$wpdb->delete($wpdb->prefix . 'wusp_group_product_price_mapping', array('product_id' => $productId), array('%d'));
		$ruleManager->setUnusedRulesAsInactive();
		$ruleManager->deleteRuleWithZeroNumberOfSubrules();
	}

	/**
	* Deleting/Syncing pricing for groups when they are deleted.
	* Gets the associated entities for the active subrules of the particular
	* rule.
	* Deletes the rules and subrules associated with that.
	* Deletes the entry in product_price_mapping for that group id,
	 *
	* @param int $groupId Group Id
	*/
	public static function deleteMappingForGroups( $groupId) {
		global $subruleManager, $ruleManager, $wpdb;
		$ruleIds  = array();
		$subrules = $subruleManager->getAllRuleInfoForAssociatedEntity($groupId, 'group');
		foreach ($subrules as $key) {
			$ruleIds[] = $key['rule_id'];
		}
		$ruleIds = array_unique($ruleIds);
		foreach ($ruleIds as $ruleId) {
			$ruleManager->deleteRule($ruleId);
		}
		$wpdb->delete($wpdb->prefix . 'wusp_group_product_price_mapping', array('group_id' => $groupId), array('%d'));
	}

	 /**
	* Deleting/Syncing pricing for users when they are deleted.
	* Deletes the rules associated with that user.
	* Deletes the pricing_mapping entries for the deleted user for all user/group
	* /role
	*
	* @param string $entityColumn Column for where clause user_id|role|group_id
	* @param string $entityTable  Table name from which to delete the record
	* @param string $entityFormat Format for where clause %d or %s
	* @param int    $entity       deleted user Id|group id|role.
	* @param int    $productId    product id to delete record for.
	* @param int    $minQty       qty of product.
	*/
	public static function deleteCustomerMapping( $entityColumn, $entityTable, $entityFormat, $ruleType, $entity, $productId = 0, $minQty = 0) {
		global $subruleManager, $ruleManager, $wpdb;

		$deleteWhere = array($entityColumn => $entity);
		$whereFormat = array($entityFormat);
		if (!empty($productId)) {
			$deleteWhere['product_id'] = $productId;
			array_push($whereFormat, '%d');
		}
		if (!empty($minQty)) {
			$deleteWhere['min_qty'] = $minQty;
			array_push($whereFormat, '%d');
		}
		$wpdb->delete($wpdb->prefix . $entityTable, $deleteWhere, $whereFormat);
	}


	public static function deleteRoleGroupMapping( $table_name, $selections) {
		global $wpdb;

		foreach ($selections as $single_selection) {
			$wpdb->delete($table_name, array('id' => $single_selection));
		}
	}
}
