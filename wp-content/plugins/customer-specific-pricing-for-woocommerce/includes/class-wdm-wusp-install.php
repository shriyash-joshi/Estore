<?php

namespace WdmWuspInstall;

if (! class_exists('WdmWuspInstall')) {
	/**
	* This class is for the installation functions.
	* It creates the required tables for the CSP Plugin.
	* It is responsible for deleting the entries from the csp related tables whose
	* parent (user, group, product, category) entries no more exists
	*/
	class WdmWuspInstall {
	
		/*
		 * Creates all tables required for the plugin. It creates three
		 * tables in the database. wusp_user_pricing_mapping table stores the mapping of
		 * User, Product and specific pricing. wusp_group_product_price_mapping table stores
		 * the mapping of Group, Product and specific pricing.
		 * wusp_role_pricing_mapping table stores the mapping of Role, Product and specific
		 * pricing. Also creates two tables which handles the pricing manager rules. rule
		 * table and subrule table. rule table used to store rules associated to the
		 * products and subrule stores single subrules of main rule.
		 * Three tables for the category specific pricing :
		 * 1: User category pricing mapping: user_id, category slug ,flat/discount
		 * and pricing.
		 * 2: Group category pricing mapping: group_id, category slug ,flat/discount * and pricing.
		 * 3: Role category pricing mapping: role, category slug,flat/discount and
		 * pricing.
		 */

		public static function createTables() {
			global $wpdb;
			$wpdb->hide_errors();

			$collate = self::getWpCharsetCollate();

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';

			$user_pricing_table          = $wpdb->prefix . 'wusp_user_pricing_mapping';
			$group_price_table           = $wpdb->prefix . 'wusp_group_product_price_mapping';
			$role_price_table            = $wpdb->prefix . 'wusp_role_pricing_mapping';
			$wdm_rules_table             = $wpdb->prefix . 'wusp_rules';
			$wdm_subrules_table          = $wpdb->prefix . 'wusp_subrules';
			$user_category_pricing_table = $wpdb->prefix . 'wcsp_user_category_pricing_mapping';
			$group_category_price_table  = $wpdb->prefix . 'wcsp_group_category_pricing_mapping';
			$role_category_price_table   = $wpdb->prefix . 'wcsp_role_category_pricing_mapping';
			//Create CSP tables

			//Create User Pricing Table
			self::createUserProductPricingTable($user_pricing_table, $collate);

			//Create Group Pricing Table
			self::createGroupProductPricingTable($group_price_table, $collate);

			//Create Role Pricing Table
			self::createRoleProductPricingTable($role_price_table, $collate);

			self::createUserCartegoryPricingTable($user_category_pricing_table, $collate);
			self::createGroupCartegoryPricingTable($group_category_price_table, $collate);
			self::createRoleCartegoryPricingTable($role_category_price_table, $collate);
			//Changes added by Sumit Starts here

			//Create Rule Log Table
			self::createRuleTable($wdm_rules_table, $collate);

			//Create SubRules Log Table
			self::createSubruleTable($wdm_subrules_table, $collate);

			//Check if wp_wusp_user_mapping && wp_wusp_pricing_mapping exist or not. If exist then do following.
			//Check if wp_wusp_user_pricing_mapping table exists. If it does not
			//exist, then create table wp_wusp_user_pricing_mapping from the wp_wusp_pricing_mapping and wp_wusp_user_mapping
			// OR merge both table wp_wusp_pricing_mapping and wp_wusp_user_mapping in single table wp_wusp_user_pricing_mapping.

			$user_mapping_table    = $wpdb->prefix . 'wusp_user_mapping';
			$pricing_mapping_table = $wpdb->prefix . 'wusp_pricing_mapping';
			$emptyPricingTable     = $wpdb->get_var('SELECT COUNT(*) FROM ' . $wpdb->prefix . 'wusp_user_pricing_mapping');

			if (( $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $user_mapping_table)) || $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $pricing_mapping_table)) ) && empty($emptyPricingTable)) {
				self::importToNewUspTables();
			}

			if (!$wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $user_pricing_table)) || !$wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $wdm_subrules_table)) || !$wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $wdm_rules_table)) || !$wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $group_price_table)) || !$wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $role_price_table))) {
				add_action('admin_notices', array('self','cspTablesCreated'));
			}
			self::cleanupDatabase();
		}
		
		/**
		* Creates the user pricing mapping table with user_id,product_id, flat/
		* discount and pricing.
		* Checks if such table already exists, if no create and if yes check if it
		* contains min_qty column if not add the column
		 *
		* @param string $user_pricing_table wusp_user_pricing_mapping table name
		* @param string $collate default charset-collate of WordPress database
		*/
		protected static function createUserProductPricingTable( $user_pricing_table, $collate) {
			/*global $wpdb;
			if (!$wpdb->get_var("SHOW COLUMNS FROM `$enquiryTableName` LIKE 'enquiry_hash';")) {
			$wpdb->query("ALTER TABLE $enquiryTableName ADD enquiry_hash VARCHAR(75)");
			$wpdb->query("ALTER TABLE $enquiryTableName ADD UNIQUE INDEX `enquiry_hash` (`enquiry_hash`)");
			}*/
			global $wpdb;
			if (! $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s;', $user_pricing_table))) {
				$user_table_query = "
                CREATE TABLE IF NOT EXISTS {$user_pricing_table} (
                                    id bigint(20) PRIMARY KEY NOT NULL AUTO_INCREMENT,
                                    product_id bigint(20),
                                    user_id bigint(20),
                                    price numeric(13,4) UNSIGNED,
                                    min_qty bigint(20) UNSIGNED NOT NULL DEFAULT 1,
                                    flat_or_discount_price TINYINT(1),
                                    INDEX product_id (product_id),
                                    INDEX user_id (user_id),
                                    INDEX min_qty (min_qty)
                                ) $collate;
                                ";
				@dbDelta($user_table_query);
			} elseif (!$wpdb->get_var('SHOW COLUMNS FROM ' . $wpdb->prefix . 'wusp_user_pricing_mapping LIKE "min_qty";')) {
				$wpdb->query('ALTER TABLE ' . $wpdb->prefix . 'wusp_user_pricing_mapping ADD min_qty BIGINT(20) UNSIGNED NOT NULL DEFAULT 1, ADD INDEX min_qty (min_qty)');
			}
		}

		/**
		* Creates the group pricing mapping table with group_id,product_id, flat/
		* discount and pricing and unique key of product_id, group_id, min_qty
		* Checks if such table already exists, if no create one.
		* If yes,  1: Modify the price column datatype to decimal.
		* 2: Add the min_qty to the table.
		* 3: Check if the unique key unique_group_product_price exists for the table.
		*    :If yes, drop it and create a new unique key :unique_group_product_qty
		*    :with product_id, group_id and product quantity.
		*
		* @param string $group_price_table wusp_group_pricing_mapping table name
		* @param string $collate default charset-collate of WordPress database
		*/
		protected static function createGroupProductPricingTable( $group_price_table, $collate) {
			global $wpdb;
			$table_present_result = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $group_price_table));

			if (null !== $table_present_result || $table_present_result == $group_price_table) {
				$wpdb->query('ALTER TABLE ' . $wpdb->prefix . 'wusp_group_product_price_mapping MODIFY price numeric(13,4)');

				if (!$wpdb->get_var($wpdb->prepare('SHOW COLUMNS FROM ' . $wpdb->prefix . 'wusp_group_product_price_mapping LIKE %s', 'min_qty'))) {
					$wpdb->query('ALTER TABLE ' . $wpdb->prefix . 'wusp_group_product_price_mapping ADD min_qty BIGINT(20) UNSIGNED NOT NULL DEFAULT 1');
				}

				$keyExist = $wpdb->get_results('SHOW KEYS FROM ' . $wpdb->prefix . 'wusp_group_product_price_mapping where Key_name = "unique_group_product_price"');
				if ($keyExist) {
					$wpdb->query('ALTER TABLE ' . $wpdb->prefix . 'wusp_group_product_price_mapping 
                    DROP INDEX unique_group_product_price, 
                    ADD UNIQUE KEY unique_group_product_qty ("product_id","group_id","min_qty")');
				}
			} else {
				$group_table_query = "
                    CREATE TABLE IF NOT EXISTS {$group_price_table} (
                                        id bigint(20) NOT NULL AUTO_INCREMENT,
                                        group_id bigint(20),
                                        product_id bigint(20),
                                        price numeric(13,4),
                                        min_qty bigint(20) UNSIGNED NOT NULL DEFAULT 1,
                                        flat_or_discount_price TINYINT(1),
                                        UNIQUE KEY unique_group_product_qty (product_id,group_id,min_qty),
                                        PRIMARY KEY  (id)
                                    ) $collate;";
				@dbDelta($group_table_query);
			}
		}
		/**
		* Creates the role pricing mapping table with role,product_id, flat/
		* discount and pricing.
		* Checks if such table already exists, if no create one.
		* If yes,  1: Modify the price column datatype to decimal and role column
		* datatype to varchar
		* 2: Add the min_qty to the table.
		* 3: Check if the unique key unique_role_product_price exists for the table.
		*    :If yes, drop it and create a new unique key :unique_role_product_qty
		*    :with product_id, role and product quantity.
		*
		* @param string $role_price_table wusp_role_pricing_mapping table name
		* @param string $collate default charset-collate of WordPress database
		*/

		protected static function createRoleProductPricingTable( $role_price_table, $collate) {
			global $wpdb;
			$table_present_result = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $role_price_table));

			if ( null!==$table_present_result || $table_present_result == $role_price_table) {
				$wpdb->query('ALTER TABLE ' . $wpdb->prefix . 'wusp_role_pricing_mapping
                  MODIFY role VARCHAR(60),
                  MODIFY price numeric(13,4)');

				if (!$wpdb->get_var('SHOW COLUMNS FROM ' . $wpdb->prefix . 'wusp_role_pricing_mapping LIKE "min_qty"')) {
					$wpdb->query('ALTER TABLE ' . $wpdb->prefix . 'wusp_role_pricing_mapping ADD min_qty BIGINT(20) UNSIGNED NOT NULL DEFAULT 1');
				}

				$keyExist = $wpdb->get_results('SHOW KEYS FROM ' . $wpdb->prefix . 'wusp_role_pricing_mapping where Key_name = "unique_role_product_price"');

				if ($keyExist) {
					$wpdb->query('ALTER TABLE ' . $wpdb->prefix . 'wusp_role_pricing_mapping 
                    DROP INDEX unique_role_product_price, 
                    ADD UNIQUE KEY "unique_role_product_qty" ("product_id","role","min_qty")');
				}
			} else {
				$role_table_query = "
                CREATE TABLE IF NOT EXISTS {$role_price_table} (
                                    id bigint(20) NOT NULL AUTO_INCREMENT,
                                    role varchar(60) NOT NULL,
                                    price numeric(13,4),
                                    min_qty bigint(20) UNSIGNED NOT NULL DEFAULT 1,
                                    flat_or_discount_price TINYINT(1),
                                    product_id bigint(20),
                                    UNIQUE KEY unique_role_product_qty (product_id,role,min_qty),
                                    PRIMARY KEY  (id)
                            ) $collate;
                            ";
				@dbDelta($role_table_query);
			}
		}
		/**
		* Creates the user category pricing mapping table with user_id,cat_slug,
		* flat/discount and pricing.
		* Checks if such table already exists, if no create one.
		 *
		* @param string $user_category_pricing_table
		* wcsp_user_category_pricing_mapping table name
		* @param string $collate default charset-collate of WordPress database
		*/
		protected static function createUserCartegoryPricingTable( $user_category_pricing_table, $collate) {
			global $wpdb;
			if (! $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $user_category_pricing_table))) {
				$user_table_query = "
                CREATE TABLE IF NOT EXISTS {$user_category_pricing_table} (
                                    id bigint(20) PRIMARY KEY NOT NULL AUTO_INCREMENT,
                                    cat_slug varchar(60) NOT NULL,
                                    user_id bigint(20),
                                    price numeric(13,4) UNSIGNED,
                                    min_qty bigint(20) UNSIGNED NOT NULL DEFAULT 1,
                                    flat_or_discount_price TINYINT(1),
                                    INDEX cat_slug (cat_slug),
                                    INDEX user_id (user_id),
                                    INDEX min_qty (min_qty)
                                ) $collate;
                                ";
				@dbDelta($user_table_query);
			}
		}
		/**
		* Creates the group category pricing mapping table with group_id,cat_slug,
		* flat/discount and pricing.
		* Checks if such table already exists, if no create one.
		 *
		* @param string $group_category_price_table
		* wcsp_group_category_pricing_mapping table name
		* @param string $collate default charset-collate of WordPress database
		*/
		protected static function createGroupCartegoryPricingTable( $group_category_price_table, $collate) {
			global $wpdb;
			if (! $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $group_category_price_table))) {
				$group_table_query = "
                    CREATE TABLE IF NOT EXISTS {$group_category_price_table} (
                                        id bigint(20) NOT NULL AUTO_INCREMENT,
                                        group_id bigint(20),
                                        cat_slug varchar(60) NOT NULL,
                                        price numeric(13,4),
                                        min_qty bigint(20) UNSIGNED NOT NULL DEFAULT 1,
                                        flat_or_discount_price TINYINT(1),
                                        UNIQUE KEY unique_group_product_qty (cat_slug,group_id,min_qty),
                                        PRIMARY KEY  (id)
                                    ) $collate;";
				@dbDelta($group_table_query);
			}
		}
		/**
		* Creates the role category pricing mapping table with role,cat_slug,
		* flat/discount and pricing.
		* Checks if such table already exists, if no create one.
		 *
		* @param string $role_category_price_table
		* wcsp_role_category_pricing_mapping table name
		* @param string $collate default charset-collate of WordPress database
		*/
		protected static function createRoleCartegoryPricingTable( $role_category_price_table, $collate) {
			global $wpdb;
			if (! $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $role_category_price_table))) {
				$role_table_query = "
                CREATE TABLE IF NOT EXISTS {$role_category_price_table} (
                                    id bigint(20) NOT NULL AUTO_INCREMENT,
                                    role varchar(60) NOT NULL,
                                    price numeric(13,4),
                                    min_qty bigint(20) UNSIGNED NOT NULL DEFAULT 1,
                                    flat_or_discount_price TINYINT(1),
                                    cat_slug varchar(60) NOT NULL,
                                    UNIQUE KEY unique_role_product_qty (cat_slug,role,min_qty),
                                    PRIMARY KEY  (id)
                            ) $collate;
                            ";
				@dbDelta($role_table_query);
			}
		}
		/**
		* Creates the Product pricing rules table with rule_id,rule_type,rule_title,
		* creation and modification time, active status, total_subrules.
		* Checks if such table already exists, if no create one.
		 *
		* @param string $wdm_rules_table wusp_rules table name
		* @param string $collate default charset-collate of WordPress database
		*/
		protected static function createRuleTable( $wdm_rules_table, $collate) {
			$wdm_rules_query = "
            CREATE TABLE IF NOT EXISTS {$wdm_rules_table} (
                                rule_id bigint(20) NOT NULL AUTO_INCREMENT,
                                rule_title  text,
                                rule_type varchar(20),
                                rule_creation_time datetime,
                                rule_modification_time datetime,
                                active TINYINT(1),
                                total_subrules SMALLINT(5) UNSIGNED NOT NULL,
                                PRIMARY KEY  (rule_id),
                                INDEX active (active),
                                INDEX rule_type (rule_type)
                        ) $collate;
                        ";
			@dbDelta($wdm_rules_query);
		}
		/**
		* Creates the Product pricing rules table with subrule_id,
		* rule_id,rule_type,active status,flat/discount and pricing.
		* creation and modification time, active status, total_subrules.
		* Checks if such table already exists, if no create one.
		 *
		* @param string $wdm_subrules_table wusp_subrules table name
		* @param string $collate default charset-collate of WordPress database
		*/
		protected static function createSubruleTable( $wdm_subrules_table, $collate) {
			global $wpdb;
			$table_present_result = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $wdm_subrules_table));
			if ( null===$table_present_result || $table_present_result != $wdm_subrules_table) {
				$wdm_subrules_query = "
                CREATE TABLE IF NOT EXISTS {$wdm_subrules_table} (
                                    subrule_id bigint(20) NOT NULL AUTO_INCREMENT,
                                    rule_id bigint(20) UNSIGNED NOT NULL,
                                    product_id bigint(20) UNSIGNED NOT NULL,
                                    rule_type varchar(20),
                                    associated_entity varchar(50),
                                    flat_or_discount_price TINYINT(1),
                                    price numeric(13,4) UNSIGNED,
                                    min_qty bigint(20) UNSIGNED NOT NULL DEFAULT 1,
                                    active TINYINT(1),
                                    PRIMARY KEY  (subrule_id),
                                    INDEX rule_id (rule_id),
                                    INDEX product_id (product_id),
                                    INDEX rule_type (rule_type),
                                    INDEX associated_entity (associated_entity),
                                    INDEX active (active)
                            ) $collate;
                            ";
				@dbDelta($wdm_subrules_query);
			} elseif (!$wpdb->get_var('SHOW COLUMNS FROM ' . $wpdb->prefix . 'wusp_subrules LIKE "min_qty"')) {
				$wpdb->query('ALTER TABLE ' . $wpdb->prefix . 'wusp_subrules ADD min_qty BIGINT(20) UNSIGNED NOT NULL DEFAULT 1');
			}
		}


		/**
		* Gets the default charset and collate for the systems' WordPress database.
		 *
		* @return string $charset_collate query of charset collate
		*/
		protected static function getWpCharsetCollate() {

			global $wpdb;
			$charset_collate = '';

			if (! empty($wpdb->charset)) {
				$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
			}

			if (! empty($wpdb->collate)) {
				$charset_collate .= " COLLATE $wpdb->collate";
			}

			return $charset_collate;
		}
		/**
		* If the required tables are not created on install,show error message.
		*/
		public static function cspTablesCreated() {
			?>
			<div id="message" class="error">
				<p><?php esc_html__('Please try to deactivate and then activate the plugin again.', 'customer-specific-pricing-for-woocommerce'); ?></p>
			</div>
			<?php
		}
		/**
		* Imports the data from the previous two tables, wusp_user_mapping and
		* wusp_pricing_mapping into a single table wusp_user_pricing_mapping
		* Select the user_id, product_id, price, flat_or_discount_price from both the tables with similar common attribute.
		* Check if the user_id in the above table exists in users table.
		* Update the details in wusp_user_pricing_mapping table.
		*/
		public static function importToNewUspTables() {
			global $wpdb;
			$fetched_users         = array();
			$user_pricing_table    = $wpdb->prefix . 'wusp_user_pricing_mapping';

			$find_user_mapping = $wpdb->get_results('SELECT user_id, product_id, price, flat_or_discount_price FROM ' . $wpdb->prefix . 'wusp_user_mapping AS UMT, ' . $wpdb->prefix . 'wusp_pricing_mapping AS PMT WHERE UMT.id = PMT.user_product_id');
			if ($find_user_mapping) {
				foreach ($find_user_mapping as $single_user_mapping) {
					if (! isset($fetched_users[ $single_user_mapping->user_id ])) {
						$fetched_users[ $single_user_mapping->user_id ] = $wpdb->get_var($wpdb->prepare('SELECT id FROM ' . $wpdb->prefix . 'users where id=%d', $single_user_mapping->user_id));
					}
					$get_user_id = $fetched_users[ $single_user_mapping->user_id ];
					if ( null!==$get_user_id) {
						$wpdb->insert($user_pricing_table, array(
							'price'                  => $single_user_mapping->price,
							'product_id'             => $single_user_mapping->product_id,
							'user_id'                => $single_user_mapping->user_id,
							'flat_or_discount_price' => $single_user_mapping->flat_or_discount_price,
						), array(
							'%s',
							'%d',
							'%d',
							'%d',
						));
					}
				}// end of foreach
			}// end of if find_user_mapping
			// }
		}
		/**
		* Deletes the entries whose products, categories , groups or users are no
		* longer present in the database.
		*/
		public static function cleanupDatabase() {
			if (version_compare(CSP_VERSION, '4.1.0') >= 0) {
				self::deleteEntriesOfCatDeletedUsers();
				self::deleteEntriesOfDeletedCategories();
				self::deleteEntriesOfCatDeletedGroups();
			}

			if (version_compare(CSP_VERSION, '4.2.0') >= 0) {
				self::deleteCSPForGroupedExternalTypes();
			}

			self::deleteEntriesOfProductDeletedUsers();
			self::deleteEntriesOfDeletedGroups();
			self::deleteEntriesOfDeletedProducts();
		}

		/**
		* Deletes the entries of the User category pricing from table
		* wcsp_user_category_pricing_mapping for the deleted users.
		* Gets the distinct ids from wcsp_user_category_pricing_mapping table.
		* Gets the distinct ids from users table.
		* The user ids which are not there in users table delete those ids data from * the wcsp_user_category_pricing_mapping table.
		*/
		public static function deleteEntriesOfCatDeletedUsers() {
			global $wpdb;
			$deletedCatUsers = array();
			$wcspCatTable    = $wpdb->prefix . 'wcsp_user_category_pricing_mapping';

			$mappedCatUserIds = $wpdb->get_col('SELECT DISTINCT user_id FROM ' . $wpdb->prefix . 'wcsp_user_category_pricing_mapping');
			
			$args = array (
				'fields' => 'id',
			);
			 
			// Create the WP_User_Query object
			$wp_user_query = new \WP_User_Query($args);

			$AllAvailableUsers = $wp_user_query->get_results();
			
			if ($mappedCatUserIds && $AllAvailableUsers && is_array($mappedCatUserIds) && is_array($AllAvailableUsers)) {
				$deletedCatUsers = array_diff($mappedCatUserIds, $AllAvailableUsers);
			}

			if ($deletedCatUsers) {
				$wpdb->query($wpdb->prepare('DELETE FROM ' . $wpdb->prefix . 'wcsp_user_category_pricing_mapping WHERE user_id IN (' . implode(', ', array_fill(0, count($deletedCatUsers), '%s')) . ')', $deletedCatUsers));
			}
		}
		/**
		* Deletes the entries of the User category pricing from table
		* wusp_user_pricing_mapping for the deleted users.
		* Gets the distinct ids from wusp_user_pricing_mapping table.
		* Gets the distinct ids from users table.
		* The user ids which are not there in users table delete those ids data from * the wusp_user_pricing_mapping table.
		*/
		public static function deleteEntriesOfProductDeletedUsers() {
			global $wpdb;
			$deletedProductUsers = array();

			$args = array (
				'fields' => 'id',
			);
			 
			// Create the WP_User_Query object
			$wp_user_query = new \WP_User_Query($args);

			$AllAvailableUsers = $wp_user_query->get_results();

			$mappedProductUserIds = $wpdb->get_col('SELECT DISTINCT user_id FROM ' . $wpdb->prefix . 'wusp_user_pricing_mapping');

			if ($mappedProductUserIds && $AllAvailableUsers && is_array($mappedProductUserIds) && is_array($AllAvailableUsers)) {
				$deletedProductUsers = array_diff($mappedProductUserIds, $AllAvailableUsers);
			}

			if ($deletedProductUsers) {
				$wpdb->query('DELETE FROM ' . $wpdb->prefix . 'wusp_user_pricing_mapping WHERE user_id IN (' . implode(', ', array_fill(0, count($deletedProductUsers), '%s')) . ')', $deletedProductUsers);
			}
		}
		/**
		* Check if groups plugin is activated.
		* If yes ,
		* Deletes the entries of the Group category pricing from table
		* wcsp_group_category_pricing_mapping for the deleted groups.
		* Gets the distinct ids from wcsp_group_category_pricing_mapping table.
		* Gets the distinct ids from groups table.
		* The group ids which are not there in groups table delete those ids data
		* from the wcsp_group_category_pricing_mapping table.
		*/
		public static function deleteEntriesOfCatDeletedGroups() {
			global $wpdb;
			if (self::wdmIsActive('groups/groups.php')) {
				$deletedGroups    = array();
				
				$mappedGroupIds = $wpdb->get_col('SELECT DISTINCT group_id FROM ' . $wpdb->prefix . 'wcsp_group_category_pricing_mapping');

				$AllAvailableGroups = $wpdb->get_col('SELECT DISTINCT group_id FROM ' . $wpdb->prefix . 'groups_group');

				//find out deleted groups
				if ($mappedGroupIds && $AllAvailableGroups && is_array($mappedGroupIds) && is_array($AllAvailableGroups)) {
					$deletedGroups = array_diff($mappedGroupIds, $AllAvailableGroups);
				}

				// Delete them from {$wpdb->prefix}wusp_group_product_price_mapping
				if ($deletedGroups) {
					$wpdb->query($wpdb->prepare('DELETE FROM ' . $wpdb->prefix . 'wcsp_group_category_pricing_mapping WHERE group_id IN (' . implode(', ', array_fill(0, count($deletedGroups), '%s')) . ')', $deletedGroups));
				}
			}
		}
		/**
		* Check if groups plugin is activated.
		* If yes ,
		* Deletes the entries of the Group category pricing from table
		* wusp_group_product_price_mapping for the deleted groups.
		* Gets the distinct ids from wusp_group_product_price_mapping table.
		* Gets the distinct ids from groups table.
		* The group ids which are not there in groups table delete those ids data
		* from the wusp_group_product_price_mapping table.
		*/
		public static function deleteEntriesOfDeletedGroups() {
			global $wpdb;
			
			if (self::wdmIsActive('groups/groups.php')) {
				$deletedGroups     = array();
				$mappedGroupIds = $wpdb->get_col('SELECT DISTINCT group_id FROM ' . $wpdb->prefix . 'wusp_group_product_price_mapping');

				$AllAvailableGroups = $wpdb->get_col('SELECT DISTINCT group_id FROM ' . $wpdb->prefix . 'groups_group');

				//find out deleted groups
				if ($mappedGroupIds && $AllAvailableGroups && is_array($mappedGroupIds) && is_array($AllAvailableGroups)) {
					$deletedGroups = array_diff($mappedGroupIds, $AllAvailableGroups);
				}

				// Delete them from {$wpdb->prefix}wusp_group_product_price_mapping
				if ($deletedGroups) {
					$wpdb->query($wpdb->prepare('DELETE FROM ' . $wpdb->prefix . 'wusp_group_product_price_mapping WHERE group_id IN (' . implode(', ', array_fill(0, count($deletedGroups), '%s')) . ')', $deletedGroups));
				}
			}
		}

		/**
		* Delete the entries for specific table for the deleted products.
		 *
		* @param string $deleteTable Table name.
		* @param array $deletedProducts array of deleted products.
		*/
		public static function deleteEntries( $deleteTable, $deletedProducts) {
			global $wpdb;
			if (empty($deletedProducts)) {
				return ;
			}

			switch ($deleteTable) {
				case $wpdb->prefix . 'wusp_user_pricing_mapping':
					$wpdb->query('DELETE FROM ' . $wpdb->prefix . 'wusp_user_pricing_mapping WHERE product_id IN (' . implode(', ', array_fill(0, count($deletedProducts), '%s')) . ')', $deletedProducts);
					break;

				case $wpdb->prefix . 'wusp_role_pricing_mapping':
					$wpdb->query('DELETE FROM ' . $wpdb->prefix . 'wusp_role_pricing_mapping WHERE product_id IN (' . implode(', ', array_fill(0, count($deletedProducts), '%s')) . ')', $deletedProducts);
					break;

				case $wpdb->prefix . 'wusp_group_product_price_mapping':
					$wpdb->query('DELETE FROM ' . $wpdb->prefix . 'wusp_group_product_price_mapping WHERE product_id IN (' . implode(', ', array_fill(0, count($deletedProducts), '%s')) . ')', $deletedProducts);
					break;

				default:
					# code...
					break;
			}
		}

		/**
		* Deletes the entries from pricing_mapping table whose products
		* are no longer present in the database.
		 *
		* @param array $ProductsInUserTable Product_ids in product-pricing-mapping
		* table
		* @param string $pricingTable table name of product-pricing-mapping
		* table.
		* @param array $allProducts all products in database.
		*/
		public static function deleteUserEntries( $ProductsInUserTable, $pricingTable, $allProducts) {
			if ($ProductsInUserTable && $allProducts) {
				$deletedProducts = array_diff($ProductsInUserTable, $allProducts);
				self::deleteEntries($pricingTable, $deletedProducts);
			}
		}

		//Removed Unused methods

		/**
		* Gets the ids of all products type posts from the posts table.
		* Deletes the entries in the product-pricing-mapping tables for deleted products for all user/group/role
		* Deleting/Syncing pricing for products when they are deleted, i.e, deleting * the subrules and rules associated with that products.
		*/
		public static function deleteEntriesOfDeletedProducts() {
			global $wpdb;
			$userPricingTable  = "{$wpdb->prefix}wusp_user_pricing_mapping";
			$rolePricingTable  = "{$wpdb->prefix}wusp_role_pricing_mapping";
			$groupPricingTable = "{$wpdb->prefix}wusp_group_product_price_mapping";
			$deletedProducts   = array();

			$allProducts = $wpdb->get_col('SELECT ID FROM ' . $wpdb->prefix . 'posts WHERE post_type IN ("product", "product_variation")');

			$ProductsInUserTable = $wpdb->get_col('SELECT product_id FROM ' . $wpdb->prefix . 'wusp_user_pricing_mapping');

			// Delete them from wusp_user_pricing_mapping {$wpdb->prefix}wusp_group_product_price_mapping
			self::deleteUserEntries($ProductsInUserTable, $userPricingTable, $allProducts);

			foreach ($deletedProducts as $deleteProductId) {
				\WuspDeleteData\WdmWuspDeleteData::deleteMappingForProducts($deleteProductId);
			}
			//reset $deletedProducts
			$deletedProducts = array();

			$ProductsInRoleTable = $wpdb->get_col('SELECT product_id FROM ' . $wpdb->prefix . 'wusp_role_pricing_mapping');

			// Delete them from {$wpdb->prefix}wusp_user_pricing_mapping
			self::deleteUserEntries($ProductsInRoleTable, $rolePricingTable, $allProducts);

			foreach ($deletedProducts as $deleteProductId) {
				\WuspDeleteData\WdmWuspDeleteData::deleteMappingForProducts($deleteProductId);
			}
			//reset $deletedProducts
			$deletedProducts = array();

			$ProductsInGroupTable = $wpdb->get_col('SELECT product_id FROM ' . $wpdb->prefix . 'wusp_group_product_price_mapping');

			// Delete them from {$wpdb->prefix}wusp_user_pricing_mapping
			self::deleteUserEntries($ProductsInGroupTable, $groupPricingTable, $allProducts);

			foreach ($deletedProducts as $deleteProductId) {
				\WuspDeleteData\WdmWuspDeleteData::deleteMappingForProducts($deleteProductId);
			}
		}
		
		/**
		* Delete the entries from the Category price mapping tables whose categories are not present currently.
		* Gets the products category present currently,
		* Deletes the entries whose categories are not present.
		*/
		public static function deleteEntriesOfDeletedCategories() {
			global $wpdb;
			$groupCatTable = $wpdb->prefix . 'wcsp_group_category_pricing_mapping';
			$roleCatTable  = $wpdb->prefix . 'wcsp_role_category_pricing_mapping';
			$userCatTable  = $wpdb->prefix . 'wcsp_user_category_pricing_mapping';
			
			$catSlugArray = self::getCategoryArray();

			if (empty($catSlugArray)) {
				return;
			}

			if (!empty($catSlugArray)) {
				self::deleteCatEntries($catSlugArray, $userCatTable);
				self::deleteCatEntries($catSlugArray, $roleCatTable);
				self::deleteCatEntries($catSlugArray, $groupCatTable);
			}
		}
		/**
		* Delete the entries whose product categories are not present currently.
		 *
		* @param array $combineCategories array of Product categories.
		* @param string $table table name (group,role,user) category-pricing mapping.
		*/
		public static function deleteCatEntries( $combineCategories, $table) {
			global $wpdb;
			switch ($table) {
				case $wpdb->prefix . 'wcsp_user_category_pricing_mapping':
					$wpdb->get_results($wpdb->prepare('DELETE FROM ' . $wpdb->prefix . 'wcsp_user_category_pricing_mapping WHERE cat_slug NOT IN (' . implode(', ', array_fill(0, count($combineCategories), '%s')) . ')', $combineCategories));
					break;
					
				case $wpdb->prefix . 'wcsp_role_category_pricing_mapping':
					$wpdb->get_results($wpdb->prepare('DELETE FROM ' . $wpdb->prefix . 'wcsp_role_category_pricing_mapping WHERE cat_slug NOT IN (' . implode(', ', array_fill(0, count($combineCategories), '%s')) . ')', $combineCategories));
					break;

				case $wpdb->prefix . 'wcsp_group_category_pricing_mapping':
					$wpdb->get_results($wpdb->prepare('DELETE FROM ' . $wpdb->prefix . 'wcsp_group_category_pricing_mapping WHERE cat_slug NOT IN (' . implode(', ', array_fill(0, count($combineCategories), '%s')) . ')', $combineCategories));
					break;				

				default:
					# code...
					break;
			}
		}

		/**
		* Gets the Product categories which are active now.
		 *
		* @return array $catSlugArray Product Categories slugs.
		*/
		public static function getCategoryArray() {
			$catSlugArray = array();
			$taxonomy     = 'product_cat';
			$orderby      = 'name';
			$show_count   = 0;      // 1 for yes, 0 for no
			$pad_counts   = 0;      // 1 for yes, 0 for no
			$hierarchical = 1;      // 1 for yes, 0 for no
			$title        = '';
			$empty        = false;

			$args           = array(
				 'taxonomy'     => $taxonomy,
				 'orderby'      => $orderby,
				 'show_count'   => $show_count,
				 'pad_counts'   => $pad_counts,
				 'hierarchical' => $hierarchical,
				 'title_li'     => $title,
				 'hide_empty'   => $empty
			);
			$all_categories = get_categories($args);

			foreach ($all_categories as $cat) {
				$catSlugArray[] = $cat->slug;
			}

			return $catSlugArray;
		}



		/**
		 * This method checks if plugin (sent as the parameter) is activated on site
		 *
		 * @param [type] $plugin - string containing plugin-directory/plugin-name.php to check
		 * if its activated on site
		 * @return bool true if $plugin is activated on site or sitewide in multisite setup.
		 */
		public static function wdmIsActive( $plugin) {
			$arrayOfActivatedPlugins = apply_filters('active_plugins', get_option('active_plugins'));
			$wcActiveOnSite          =in_array($plugin, $arrayOfActivatedPlugins);
			$wcActiveSiteWide        =false;
			if (is_multisite()) {
				$arrayOfActivatedPlugins = get_site_option('active_sitewide_plugins');
				$wcActiveSiteWide        = array_key_exists($plugin, $arrayOfActivatedPlugins);
			}
			if ($wcActiveOnSite || $wcActiveSiteWide) {
				return true;
			}
			return false;
		}



		/**
		 * Removed the CSP set for grouped and external types of products.
		 *
		 * @return void
		 */
		public static function deleteCSPForGroupedExternalTypes() {
			$args = array(
				'post_type' => 'product',
				'post_status' => 'publish',
				'tax_query' => array(
					array(
						'taxonomy' => 'product_type',
						'field'    => 'name',
						'terms'    => array('grouped', 'external'),
					),
				),
				'fields' => 'ids'
			);
			
			$query      = new \WP_Query($args);
			$productIds = $query->posts;

			foreach ($productIds as $productId) {
				deletePricingPairsForProduct($productId);
			}
		}
	}

}

