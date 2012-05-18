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

if ( file_exists( dirname( dirname(__FILE__) ) . '/wp-local-config.php') ) {
  require_once( dirname( dirname(__FILE__) ) . '/wp-local-config.php' );
} else {
  echo 'Unable to find local WordPress configuration file.';
  die;
}

// setup local paths
if ( defined('WP_HOME') ) {
  define('WP_SITEURL', WP_HOME . '/wordpress');
  define('WP_CONTENT_DIR', dirname(__FILE__) . '/content');
  define('WP_CONTENT_URL', WP_HOME . '/content');
  define('PLUGINDIR', '../content/plugins');
}

// turn on host verification for OpenID
define('Auth_OpenID_VERIFY_HOST', true);

// use custom CA cert bundle if defined
if (getenv('CURL_CA_BUNDLE')) {
  define('Auth_OpenID_CAINFO', getenv('CURL_CA_BUNDLE'));
}

// Google+ ID
define('GOOGLE_PLUS_ID', '111832530347449196055');

// Google Analytics ID
define('GOOGLE_ANALYTICS_ID', 'UA-620101-2');

// Creative Commons License
define('CC_LICENSE', 'http://creativecommons.org/licenses/by-nc-sa/3.0/');

// turn off post revisions
if ( !defined('WP_POST_REVISIONS') ) {
  define('WP_POST_REVISIONS', false);
}

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
if ( !defined('WP_DEBUG') )
  define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
  define('ABSPATH', dirname(__FILE__) . '/wordpress/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
