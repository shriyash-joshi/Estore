<?php

namespace cspCategoryPricing\deleteData;

if (!class_exists('WdmWuspDeleteCategoryData')) {
	/**
	* Deletes the category-specific pricing pairs from database.
	*/
	class WdmWuspDeleteCategoryData {
	
		private static $instance;
		public $errors;
		public $userPriceTable;
		public $rolePriceTable;
		public $groupPriceTable;

		/**
		 * Returns the *Singleton* instance of this class.
		 *
		 * @return Singleton The *Singleton* instance.
		 */
		public static function getInstance() {
			if (null === static::$instance) {
				static::$instance = new static();
			}

			return static::$instance;
		}

		/**
		* Define table names for the category pricing mapping of user/role/ * group
		*/
		public function __construct() {
			global $wpdb;
			$this->userPriceTable		= $wpdb->prefix . 'wcsp_user_category_pricing_mapping';
			$this->rolePriceTable		= $wpdb->prefix . 'wcsp_role_category_pricing_mapping';
			$this->groupPriceTable		= $wpdb->prefix . 'wcsp_group_category_pricing_mapping';
		}
		/**
		* Delete the entries of such user category mapping from
		* user_category_mappings table
		 *
		* @param string $category category slug.
		*/
		public function deleteUserCatEntries( $category ) {
			global $wpdb;
			$wpdb->get_results($wpdb->prepare('DELETE FROM ' . $wpdb->prefix . 'wcsp_user_category_pricing_mapping WHERE cat_slug = %s', $category));
		}

		/**
		* Delete the entries of such role category mapping from
		* role_category_mappings table
		 *
		* @param string $category category slug.
		*/
		public function deleteRoleCatEntries( $category) {
			global $wpdb;
			$wpdb->get_results($wpdb->prepare('DELETE FROM ' . $wpdb->prefix . 'wcsp_role_category_pricing_mapping WHERE cat_slug = %s', $category));
		}

		/**
		* Delete the entries of such group category mapping from
		* group_category_mappings table
		 *
		* @param string $category category slug.
		*/
		public function deleteGroupCatEntries( $category) {
			global $wpdb;
			$wpdb->get_results($wpdb->prepare('DELETE FROM ' . $wpdb->prefix . 'wcsp_group_category_pricing_mapping WHERE cat_slug = %s', $category));
		}

		/**
		* Delete the user pricing record not present in current selection
		* but are in Db.
		 *
		* @param int $userId user-id to be deleted.
		* @param int $minQty min-quantity corresponding to user to be
		* deleted.
		* @param string $category category corresponding to user to be
		* deleted.
		*/
		public function deleteUserCategoryQtyRecords( $userId, $minQty, $category) {
			global $wpdb;
			$wpdb->get_results($wpdb->prepare('DELETE FROM ' . $wpdb->prefix . 'wcsp_user_category_pricing_mapping WHERE user_id = %d AND min_qty = %d AND cat_slug = %s', $userId, $minQty, $category));
		}

		/**
		* Delete the role pricing record not present in current selection
		* but are in Db.
		*
		* @param string $role role to be deleted.
		* @param int $minQty min-quantity corresponding to role to be
		* deleted.
		* @param string $category category corresponding to role to be
		* deleted.
		*/
		public function deleteRoleCategoryQtyRecords( $role, $minQty, $category) {
			global $wpdb;
			$wpdb->get_results($wpdb->prepare('DELETE FROM ' . $wpdb->prefix . 'wcsp_role_category_pricing_mapping WHERE role = %s AND min_qty = %d AND cat_slug = %s', $role, $minQty, $category));
		}

		/**
		* Delete the group pricing record not present in current selection
		* but are in Db.
		 *
		* @param string $groupId group-id to be deleted.
		* @param int $minQty min-quantity corresponding to role to be
		* deleted.
		* @param string $category category corresponding to role to be
		* deleted.
		*/
		public function deleteGroupCategoryQtyRecords( $groupId, $minQty, $category) {
			global $wpdb;
			$wpdb->get_results($wpdb->prepare('DELETE FROM ' . $wpdb->prefix . 'wcsp_group_category_pricing_mapping WHERE group_id = %d AND min_qty = %d AND cat_slug = %s', $groupId, $minQty, $category));
		}

		/**
		* Delete all records of user-category-pricing from the DB.
		* It is done if the current selection is empty.
		*/
		public function deleteAllUserRecords() {
			global $wpdb;
			$wpdb->get_results('DELETE FROM ' . $wpdb->prefix . 'wcsp_user_category_pricing_mapping');
		}

		/**
		* Delete all records of role-category-pricing from the DB.
		* It is done if the current selection is empty.
		*/
		public function deleteAllRoleRecords() {
			global $wpdb;
			$wpdb->get_results('DELETE FROM ' . $wpdb->prefix . 'wcsp_role_category_pricing_mapping');
		}

		/**
		* Delete all records of group-category-pricing from the DB.
		* It is done if the current selection is empty.
		*/
		public function deleteAllGroupRecords() {
			global $wpdb;
			$wpdb->get_results('DELETE FROM ' . $wpdb->prefix . 'wcsp_group_category_pricing_mapping');
		}

		/**
		* Deletes the records from database which are not present in current * page submission
		* Get the current selection data and the data from the Db.
		* Find the difference of the entries.
		* Delete the records not present in current selection from the DB.
		 *
		* @param array $catArray category array for current selection in
		*  user-specific-pricing.
		* @param array $userIdsArray user-id array for current selection in * user-specific-pricing.
		* @param array $minQtyArray Min Quantity array for current selection * in user-specific-pricing.
		*/
		public function removeUserCatQtyList( $catArray, $userIdsArray, $minQtyArray) {
			global $getCatRecords, $cspFunctions;
			$deleteUsers              = array();
			$deleteQty                = array();
			$deletedValues            = array();
			$newArray                 = array();
			$userType = 'user_id';

			if (!empty($userIdsArray)) {
				//array of curremt records
				$newArray = $this->getNewArray($userIdsArray, $catArray, $minQtyArray, $userType);
				// $user_names = "('" . implode("','", $_POST[ 'wdm_woo_username' ]) . "')";
				// $qty = "(" . implode(",", $_POST[ 'wdm_woo_qty' ]) . ")";


				//Fetch existing records from databse
				$existing = $getCatRecords->getCatUserQtyRecords();

				//Seperating records to be deleted, i.e the records which are in DB but not in current submission
				$deletedValues = $cspFunctions->multiArrayDiff($newArray, $existing, $userType, true);

				foreach ($deletedValues as $key => $value) {
					$deleteUsers[] = $existing[$key][$userType];
					$deleteQty[]   = $existing[$key]['min_qty'];
					$deleteCategory[]   = $existing[$key]['cat_slug'];
					unset($value);
				}

				//delete records which are not in submission but saved in the DB
				if (count($deletedValues) > 0) {
					$this->deleteRecords($deleteUsers, $deleteQty, $deleteCategory, 'User');
				}
			}
		}

		/**
		* Deletes the records from database which are not present in current * page submission
		* Get the current selection data and the data from the Db.
		* Find the difference of the entries.
		* Delete the records not present in current selection from the DB.
		 *
		* @param array $catArray category array for current selection in
		*  role-specific-pricing.
		* @param array $rolesArray roles array for current selection in
		* role-specific-pricing.
		* @param array $minQtyArray Min Quantity array for current selection * in role-specific-pricing.
		*/
		public function removeRoleCatQtyList( $catArray, $rolesArray, $minQtyArray) {
			global $getCatRecords, $cspFunctions;
			$deleteRoles              = array();
			$deleteQty                = array();
			$deletedValues            = array();
			$userType = 'role';

			if (isset($rolesArray)) {
				//array of current records
				$newArray = $this->getNewArray($rolesArray, $catArray, $minQtyArray, $userType);

				// $role_names = "('" . implode("','", $_POST[ 'wdm_woo_rolename' ]) . "')";
				// $qty = "(" . implode(",", $_POST[ 'wdm_woo_role_qty' ]) . ")";
 
				//Fetch existing records from databse
				$existing = $getCatRecords->getCatRoleQtyRecords();

				$deletedValues = $cspFunctions->multiArrayDiff($newArray, $existing, $userType, true);
				foreach ($deletedValues as $key => $value) {
					$deleteRoles[] = $existing[$key][$userType];
					$deleteCategory[] = $existing[$key]['cat_slug'];
					$deleteQty[]   = $existing[$key]['min_qty'];
					unset($value);
				}

				$mapping_count = count($deletedValues);
				if ($mapping_count > 0) {
					$this->deleteRecords($deleteRoles, $deleteQty, $deleteCategory, 'Role');
				}
			}
		}

		/**
		* Deletes the records from database which are not present in current * page submission
		* Get the current selection data and the data from the Db.
		* Find the difference of the entries.
		* Delete the records not present in current selection from the DB.
		 *
		* @param array $catArray category array for current selection in
		*  group-specific-pricing.
		* @param array $groupIdsArray group-id array for current selection
		* in group-specific-pricing.
		* @param array $minQtyArray Min Quantity array for current selection * in group-specific-pricing.
		*/
		public function removeGroupCatQtyList( $catArray, $groupIdsArray, $minQtyArray) {
			global $getCatRecords, $cspFunctions;

			$deleteGroups           = array();
			$deleteQty              = array();
			$deletedValues          = array();

			$userType               = 'group_id';
			if (!empty($groupIdsArray)) {
				//array of current records
				$newArray = $this->getNewArray($groupIdsArray, $catArray, $minQtyArray, $userType);

				// $user_names = "('" . implode("','", $_POST[ 'wdm_woo_groupname' ]) . "')";
				// $qty = "(" . implode(",", $_POST[ 'wdm_woo_group_qty' ]) . ")";

				$existing = $getCatRecords->getCatGroupQtyRecords();

				$deletedValues = $cspFunctions->multiArrayDiff($newArray, $existing, $userType, true);

				foreach ($deletedValues as $key => $value) {
					$deleteGroups[] = $existing[$key][$userType];
					$deleteCategory[]   = $existing[$key]['cat_slug'];
					$deleteQty[]   = $existing[$key]['min_qty'];
					unset($value);
				}

				$mapping_count = count($deletedValues);
				if ($mapping_count > 0) {
					$this->deleteRecords($deleteGroups, $deleteQty, $deleteCategory, 'Group');
				}
			}
		}

		/**
		* Prepare the current selection data in a new array.
		 *
		* @param array $userArray user/group/role array for current
		* selection in entity category-specific-pricing.
		* @param array $catArray category array for current selection in
		*  entity-specific-pricing.
		* @param array $minQtyArray Min Quantity array for current selection * in entity-specific-pricing.
		* @param string $type entity type: group/user/role
		* @return array $newArray Array for the current selection data of
		* entity category specific pricing.
		*/
		public function getNewArray( $userArray, $catArray, $minQtyArray, $type) {
			$newArray                 = array();

			foreach ($userArray as $index => $wdmSingleUser) {
				$newArray[] = array(
					$type    => $wdmSingleUser,
					'cat_slug'    => $catArray[ $index ],
					'min_qty' => $minQtyArray[ $index ]
				);
			}

			return $newArray;
		}

		/**
		* Delete the records not present in current selection but are in Db.
		 *
		* @param array $deleteUsers deleted user/group/role from current selection.
		* @param array $deleteQty deleted min-quantity corresponding to the * user/group/role from current selection.
		* @param array $deleteCategory deleted category corresponding to the * user/group/role from current selection.
		* @param string $type entity type user/group/role.
		*/
		public function deleteRecords( $deleteUsers, $deleteQty, $deleteCategory, $type) {
			foreach ($deleteUsers as $index => $singleUser) {
				$function = 'delete' . $type . 'CategoryQtyRecords';
				$this->$function($singleUser, $deleteQty[$index], $deleteCategory[$index]);
			}
		}
	}
}
$GLOBALS['deleteCatRecords'] = WdmWuspDeleteCategoryData::getInstance();
