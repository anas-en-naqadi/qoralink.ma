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
define( 'DB_NAME', 'tempkcih_qora_link' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

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
define( 'AUTH_KEY',         'XRI<bYDWkvW8h_<* 94ezjX}/c$~$K%=P#J,4!/Gb7LAQo$5O<JG!B}v:Q35WB~Z' );
define( 'SECURE_AUTH_KEY',  '0!c&i`%gc$Ss]l}qt?ug$P/GiXLCKCT~2FlWJ04Iu9+>yXx)S3VM:]0W>W&sAc[X' );
define( 'LOGGED_IN_KEY',    '-c0n{2[^`d5%:c{;17UYkO<U3[F.XQi#6DEdBgj^4C=u|Bjz4hfV++({)Fstl(~S' );
define( 'NONCE_KEY',        '.[v:1ft6&![:[m FeU_ Xel?%90/:Y1*ZO60&U=2lX ;TjXBShioQ*zN,cdw h/i' );
define( 'AUTH_SALT',        '3EB%eDcF8C7((8{IH`*{Ix/r/B+e(cYY=*>y1YRTtD7xh QGUw39K?1s_);KN%n)' );
define( 'SECURE_AUTH_SALT', '@w;Y)7w}QzM_P8Rdo,S88>g}-wgo?RX[=LrD3*EuRq:x1*GfT[8l58<38ba.8-J,' );
define( 'LOGGED_IN_SALT',   '+RGf pn&DySBT9]R^Izh/o Q_kK=iQlN?n3{fL-|vw!r:c8MWf[-y1<@gN{`z:MQ' );
define( 'NONCE_SALT',       '9Hk2NGETvU7m]zcvX/t<`-(Pd+GCQ.adkO#F%uTE#n!yORO<.]Vj:LDK_[!XzVK1' );

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
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', true );

/* Add any custom values between this line and the "stop editing" line. */

set_time_limit(300);
define( 'WP_HOME', 'http://localhost/qora-link' );
define( 'WP_SITEURL', 'http://localhost/qora-link' );
define( 'DISABLE_WP_CRON', true );
define( 'WP_MEMORY_LIMIT', '512M' );


/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
