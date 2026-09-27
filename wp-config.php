<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'wonom');

/** Database username */
define('DB_USER', 'root');

/** Database password */
define('DB_PASSWORD', 'khanhkhanh123');

/** Database hostname */
define('DB_HOST', 'localhost');

/** Database charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The database collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

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
define('AUTH_KEY',         'DJ8S)CV*)dV AWN6C@aK3J}s7/+c9b`s)sjmoePK_CVGf,W85`oodgV|<W@h$-w:');
define('SECURE_AUTH_KEY',  'P%DY<^k>QuSe#^) B[;QC`f=ANYoSN++CL=5]#o.ej*/_wrau+0!b!_Hw#ZLWeRt');
define('LOGGED_IN_KEY',    '90RLuT-XiqtJR:Pdby#,_?i+|dDeB_?EejV WkQOA0EgVeX-ap0/*.(X2k;9jR(W');
define('NONCE_KEY',        ',vbpj9pq4>JRQk,`*#/c-:o0#:9SJ|-dGYHZQRsX8g<20=7Ut3N2H/- cK#3b?Gu');
define('AUTH_SALT',        'xr-#Ps:t50q,]F/XvgNHz=U],c_yMWg%C:-d%syeUhRb1Vklsij #Gm]zXET<[vk');
define('SECURE_AUTH_SALT', 'cq93T?Kqg@~(SPYfGo6hHnaP!o=!@. 9,L?3H(gFHi6T}D+IG[0E$#EPYvkEr+^|');
define('LOGGED_IN_SALT',   'b{=G&p%G|XdQVo[IZlTRULQ$o0uR--A4ht%RTP6>j0C[+%O8D}*Z7+fQ%*,NYT!;');
define('NONCE_SALT',       '?bPl3VYwouU2cKSlO)6xqQfhW2sy|3&n9Xk|LLJYQ+Aw`,PYKr3351sr|a=j&n=W');

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'wpn_';

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
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define('WP_DEBUG', false);

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if (!defined('ABSPATH')) {
	define('ABSPATH', __DIR__ . '/');
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
