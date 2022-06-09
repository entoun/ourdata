<?php

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', "entoun_database" );

/** Database username */
define( 'DB_USER', "entoun" );

/** Database password */
define( 'DB_PASSWORD', "jqF%jFuFWNy+4jb" );

/** Database hostname */
define( 'DB_HOST', "localhost" );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

define( 'FS_METHOD', 'direct' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
/*define( 'AUTH_KEY',         'put your unique phrase here' );
define( 'SECURE_AUTH_KEY',  'put your unique phrase here' );
define( 'LOGGED_IN_KEY',    'put your unique phrase here' );
define( 'NONCE_KEY',        'put your unique phrase here' );
define( 'AUTH_SALT',        'put your unique phrase here' );
define( 'SECURE_AUTH_SALT', 'put your unique phrase here' );
define( 'LOGGED_IN_SALT',   'put your unique phrase here' );
define( 'NONCE_SALT',       'put your unique phrase here' );*/
define('AUTH_KEY',         'b=GJ~|EZ%VR#/iK<+}oZ+pPrh[~^D%PU!01o,wU9{3=)+Ce-,+iAVVZ.6<D?1nz8');
define('SECURE_AUTH_KEY',  '1V+g$y)cvBzlH:a|YP67TfmScz1)i(/^)h{5.yHfZ^2Jw(^h|@Y=N{sWswjNNA2X');
define('LOGGED_IN_KEY',    'y-W1?LN`f=[[-T/~sNX2Hf6lO!Tns=4)uE6z=yvzQca^=ymHIX#&}Xp(ny5.tHj(');
define('NONCE_KEY',        'njMEj65Uk;VTWg]k#|rN,}ZAn{@.q;3l!?*IeQMS-z7|WMR>z?PipTF[I([$qLSh');
define('AUTH_SALT',        'vu2e-]?loAZ3x%j!iSj4jb|,Le&&Q4LPxl->S%^((xQB<Q.i*+^q#=|?v45y?P7D');
define('SECURE_AUTH_SALT', '1k2I1+e6ZvwtOjHcyI8`k4=?*TB%p=>/XBw[at6^FQw@&7UFcOtNlxm^5+l_im91');
define('LOGGED_IN_SALT',   'LUv-wa<V slIUmr9+{(Q-_^2ucN$ev]L,/!q$F;+PwsnRdKtZ_z89Iu@Txg?|@[_');
define('NONCE_SALT',       ':VOR6P[&KA*YBwwT|KYBnk)Ipu!|h]t$8i#2]U-Q.EYB`&%M&(jsVoX]%q {iz:U');
/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */

define( 'WP_AUTO_UPDATE_CORE', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';

define( 'WP_HOME', 'https://entoun.com' );
define( 'WP_SITEURL', 'https://entoun.com' );