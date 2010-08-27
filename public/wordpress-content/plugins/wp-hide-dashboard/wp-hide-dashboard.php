<?php
/*
Plugin Name: WP Hide Dashboard
Plugin URI: http://kpdesign.net/wp-plugins/wp-hide-dashboard/
Description: A simple plugin that removes the Dashboard menu, the Tools menu, the Personal Options section and the Help link on the Profile page, and prevents Dashboard access to users assigned to the <em>Subscriber</em> role. Useful if you allow your subscribers to edit their own profiles, but don't want them wandering around your WordPress admin section. Note: This version requires a minimum of WordPress 2.9. If you are running a version less than that, please upgrade your WordPress install now.
Author: Kim Parsell
Author URI: http://kpdesign.net/
Version: 2.0
License: MIT License - http://www.opensource.org/licenses/mit-license.php

Copyright (c) 2008-2010 Kim Parsell
Personal Options removal code: Copyright (c) 2010 Large Interactive, LLC, Author: Matthew Pollotta
Based on IWG Hide Dashboard plugin by Thomas Schneider, Copyright (c) 2008 (http://www.im-web-gefunden.de/wordpress-plugins/iwg-hide-dashboard/)

Permission is hereby granted, free of charge, to any person obtaining a copy of this
software and associated documentation files (the "Software"), to deal in the Software
without restriction, including without limitation the rights to use, copy, modify, merge,
publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons
to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or
substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/

/* Disallow direct access to the plugin file */
if (basename($_SERVER['PHP_SELF']) == basename (__FILE__)) {
	die('Sorry, but you cannot access this page directly.');
}

/* Define path to /wp-content/plugins/ */
if (!defined('WP_CONTENT_URL')) define('WP_CONTENT_URL', get_option('siteurl').'/wp-content');
if (!defined('WP_CONTENT_DIR')) define('WP_CONTENT_DIR', ABSPATH.'wp-content');

if (!defined('WP_PLUGIN_URL')) define('WP_PLUGIN_URL', WP_CONTENT_URL.'/plugins');
if (!defined('WP_PLUGIN_DIR')) define('WP_PLUGIN_DIR', WP_CONTENT_DIR.'/plugins');

/* Plugin config - user capability for the top level you want to hide everything from */
$wphd_user_capability = 'edit_posts'; /* [default for Subscriber role = edit_posts] */

/* Hide the Dashboard link, Help menu, Favorites menu, Upgrade notice, and now the Personal Options section too!
	Major thanks to Matt Pollotta of Large Interactive for allowing me to incorporate the functionality from his Hide Personal Options plugin
	(http://matt.largeinteractive.com/removing-personal-options-from-wordpress/28/) into WP Hide Dashboard. More features FTW! */

add_action('admin_head', 'wphd_hide_dashboard', 0);

function wphd_hide_dashboard() {
	global $current_user, $menu, $parent_file, $wphd_user_capability, $wp_db_version;

	/* First, let's get rid of the Help menu, update nag, Turbo nag, Favorites menu, Personal Options section */
	if (!current_user_can(''.$wphd_user_capability.'')) {

		/* For folks still on 2.9/2.9.1/2.9.2. You really should upgrade, you know that, right? */
		if ($wp_db_version === 12329) { /* This is WordPress 2.9, 2.9.1, 2.9.2 */
			echo "\n" . '<style type="text/css" media="screen">#your-profile { display: none; } #update-nag, #screen-meta, .turbo-nag, #favorite-actions { display: none !important; }</style>';
			echo "\n" . '<script type="text/javascript" src="'.WP_PLUGIN_URL.'/wp-hide-dashboard/wphd-hpo.js"></script>' . "\n";
		}

		/* For folks running 3.0 or higher, some leaner code. Don't need to remove Turbo link or Favorites menu any longer.
			Gears support and Turbo link were removed in changeset 11301: http://core.trac.wordpress.org/ticket/11301.
			Favorites menu is hidden by default for Subscribers in version 3.0. */
		else if ($wp_db_version >= 15260) { /* This is WordPress 3.0 */
			echo "\n" . '<style type="text/css" media="screen">#your-profile { display: none; } .update-nag, #screen-meta { display: none !important; }</style>';
			echo "\n" . '<script type="text/javascript" src="'.WP_PLUGIN_URL.'/wp-hide-dashboard/wphd-hpo.js"></script>' . "\n";
		}

	/* Now, let's fix the admin menu. */

		/* First let's take care of the actual Dashboard link. The Dashboard link menu numbers are different in 2.9.x and 3.0. */
		if ($wp_db_version === 12329) { /* This is WordPress 2.9, 2.9.1, 2.9.2 */
			unset($menu[0]);		/* Hides Dashboard menu in 2.9.x and under*/
		} else if ($wp_db_version >= 15260) { /* This is WordPress 3.0 */
			unset($menu[2]);		/* Hides Dashboard menu in 3.0 */
		}

		/* Now on to the rest of the menu. */
		unset($menu[4]);		/* Hides arrow separator under Dashboard link */
		unset($menu[10]);		/* Hides Media menu (contributors +) */
		unset($menu[25]);		/* Hides Comments menu (contributors +) */
		unset($menu[75]);		/* Hides Tools menu in 2.9.x. This is now hidden from Subscribers by default in 3.0, courtesy of changeset 11301 (thanks @nacin). */
		unset($menu[80]);		/* Hides Settings menu (contributors +) */
		unset($menu[9999]);	/* Hides top-level menu for Pods plugin */
	}

	/* Last, but not least, let's redirect folks to their profile when they login or if they try to access the dashboard via direct URL */

	if (!current_user_can(''.$wphd_user_capability.'') && $parent_file == 'index.php') {
		if (headers_sent()) {
			echo '<meta http-equiv="refresh" content="0;url='.admin_url('profile.php').'">';
			echo '<script type="text/javascript">document.location.href="'.admin_url('profile.php').'"</script>';
		} else {
			wp_redirect(admin_url('profile.php'));
		}
	}

} /* That's all folks. You were expecting more? */

?>