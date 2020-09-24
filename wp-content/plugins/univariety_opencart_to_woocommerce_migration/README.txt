=== User Migration ===

Migrating users from Opencart to Woocommerce:
 * Navigate to 'univariety/backend' folder from command line.
 * Run `wp univariety_migrate_users` to start customers migration.
 Re-migarting users from Opencart to Woocommerce:
 * truncate data from unwp_users table.
 * truncate data from unwp_usersmeta table.
 
 == Products Migration ====

Migrating products from Opencart to Woocommerce:
 * copy 'ocnewlocal/image/catalog' folder to 'univariety/backend/wp-content/catalog' 
 * Navigate to 'univariety/backend' folder from command line.
 * Run `wp univariety_migrate_products` to start products migration. 

  == Orders Migration ====

Migrating Orders from Opencart to Woocommerce:
 * Navigate to 'univariety/backend' folder from command line.
 * Run `wp univariety_migrate_orders` to start Orders migration.

 == Reviews Migration ====

Migrating Reviews from Opencart to Woocommerce:
 * Navigate to 'univariety/backend' folder from command line.
 * Run `wp univariety_migrate_reviews` to start Reviews migration.

 
 == Coupons Migration ====

Migrating Coupons from Opencart to Woocommerce:
 * Navigate to 'univariety/backend' folder from command line.
 * Run `wp univariety_migrate_coupons` to start Coupons migration.