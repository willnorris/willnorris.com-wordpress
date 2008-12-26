=== WP Hide Dashboard ===
Contributors: kpdesign
Donate link: http://www.kpdesign.net/wp-plugins/wp-hide-dashboard/
Tags: admin, administration, dashboard, hide
Requires at least: 2.5
Tested up to: 2.7
Stable tag: 1.1

Hide the Dashboard link (2.5+) and Tools menu (2.7) from your blog subscribers when they are logged in.

== Description ==

This plugin removes the Dashboard link and the Tools menu, and prevents access to the Dashboard for users assigned to the `Subscriber` role. This is useful if you want to allow your subscribers to edit their own profiles, but don't want them wandering around the rest of your WordPress admin section.

Users belonging to any of the other WordPress roles will continue to see the Dashboard link and the Tools menu, and have access to the other sections of the WordPress admin that corresponds to their role's capabilities.

Based on the [IWG Hide Dashboard](http://www.im-web-gefunden.de/wordpress-plugins/iwg-hide-dashboard/ "IWG Hide Dashboard") plugin by Thomas Schneider, which requires having the Role Manager plugin activated in order for it to function.

This plugin relies only on core WordPress capabilities.

== Installation ==

= Installation Instructions: =

1. Download the plugin and unzip it to a folder on your computer.
2. Upload the `wp-hide-dashboard` folder to the `wp-content/plugins/` directory.
3. Activate the plugin through the Plugins section in WordPress.
4. That's it! There is no configuration necessary.

== Frequently Asked Questions ==

None

== Screenshots ==

1. Screenshot of the upper-left portion of 2.6 admin section
2. Screenshot of the upper-left portion of 2.7 admin section

== Support ==

Support is provided at http://www.kpdesign.net/wp-plugins/wp-hide-dashboard/

== History ==

**Version 1.1:**  
- Added WordPress version checking.  
- Added code for defining path to /wp-content/plugins/ if outside the WordPress directory.  
- Added removal of Tools menu and collapsible arrow from the menu area in 2.7.

**Version 1.0:**  
- Initial release