<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
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

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'Hzm/~@i>dNAe1i3!1.m1gjzD-DYL:iyfeq9kXT4Lcw{l=OKlo=J8$oB/*cS!z$PW' );
define( 'SECURE_AUTH_KEY',  'MbFjk${9/mf%+*q8ig;IaIguR=:aw=YbwGS{nuac-xjJ0$FX*[xP,Es 8yWjVF8a' );
define( 'LOGGED_IN_KEY',    '1,3*}/?]y;BUWwYhn=fn[tS_^#88SuGWC{Wr;911EVThHCXtRTX?NPv,:tf*mK6:' );
define( 'NONCE_KEY',        '-ml/<!4U}%7DwMWen?l`#w#V8>AegUa08#GWb<d}2Mse+W&<HOCG$c=Bb+&iTC3N' );
define( 'AUTH_SALT',        '?14l~Brjr/t7!N7*RA+;8,6W,EBpPY6bS[$rD;-$Z>dN!E22>P8u<e/c@)B:ACoj' );
define( 'SECURE_AUTH_SALT', 'a`O8N{;2@wE|5[[)31TB u!!NcW$sP4oTZhSQ76k=/;a[Igb`JN6eh:ndqDUt}fq' );
define( 'LOGGED_IN_SALT',   'v!7&})V5<NrW.$@Xx>)T~rJRS=p4%5G=(}zySP@k!Npfv9$=IqXdNtN+tgu-LR8S' );
define( 'NONCE_SALT',       'Npd1&U(3Si-vKH7Cr>{Fcg7pR2YA1n3QwYl}V5<v<i(T+m6O)mk3WJ7([{!|Rn52' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'unwp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once( ABSPATH . 'wp-settings.php' );
