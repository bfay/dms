<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'dmsDBbqpsb');

/** MySQL database username */
define('DB_USER', 'dmsDBbqpsb');

/** MySQL database password */
define('DB_PASSWORD', 'cUoZfUAz4q');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'tT*]Wit9Lfq6IT+<2bm2EPy.Pbm;AUfr7Iq$<Uf<3Eq$EQb.3NYj}7Jr^JUf>7fr$');
define('SECURE_AUTH_KEY',  'rNv,NYk!:Vh:8Ks@[Vg[4Go@FRcw_OZl:9hs_KV~[8hs4GRLa_;9ht9LW~]Wht5HS');
define('LOGGED_IN_KEY',    '{ny>BNv,0Yk}BNv^NYk}7gv:8Ks@JVg[4Go@FRc|4coz1Cl-COd|1Zo-COw|1ZkHT');
define('NONCE_KEY',        '@8!sVw8o#9l-Ka#9l-Gw|ShixHW_2exDS~Oe]9bq6P+{aq6LxHW.2QjEq^Pf{Aq*');
define('AUTH_SALT',        'j^nQ^$f^Mo4Jv|Uk0Jv,Rk0Fh:Cs!Nd:Co!Nc[t_Si;Ht_Oh:Dp9Om+La#Am+La#6');
define('SECURE_AUTH_SALT', 'kN!Vo8o!Vw]Z:Gw[Z[CsCVW]Dt#W#9p9S_5X<EqDX#Wq9T_EX<j2My<Xm6Lx<Iu,U');
define('LOGGED_IN_SALT',   '+5~hH;P+Ha#9m+$IX<6jyIX.6iyIfvFU^bvBQ$Xr7Mo4Jv,Uk0Jv,QjBds8N@J');
define('NONCE_SALT',       'l~CKVd![18how4CKRw![1Vdk~#;5aht-9HOW-#:SZht19GOs-_[SZhx*#2Wemt29H');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);define('FS_METHOD', 'direct');

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
