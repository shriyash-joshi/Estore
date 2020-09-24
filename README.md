WooCommerce Setup
======================================
Setup instructions (Assuming you have lamp or lemp setup ready )

1. Import database (univariety.sql) 
  
  mysql -u root -p {databasename} < {path to sql file}/univariety.sql

2. Setup virtual host using root folder of the repo: http://univariety.local/ 

3. Edit wp-config.php and change the database credentials

define( 'DB_NAME', 'univariety' );

/** MySQL database username */
define( 'DB_USER', 'univariety' );

/** MySQL database password */
define( 'DB_PASSWORD', 'U5j;b@K>9Zx$' );
/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

4. access the setup virtual host url and admin 
   
## setup wp cli before creating admin user

https://wp-cli.org/

## create admin user

  wp user create xyz xyz@univariety.com --role=administrator

 virtualhosturl/wp-admin



## Google Login Oauth key setup

Following doc will help us in creating google oauth credentials for Google Login

http://support.heateor.com/how-to-get-google-plus-client-id/

Thank you.
