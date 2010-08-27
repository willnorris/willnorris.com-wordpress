=== MicroID ===
Contributors: willnorris
Tags: microid
Requires at least: 1.5
Tested up to: 2.6.1
Stable tag: 1.1

Add MicroIDs to your blog to enable ownership claims with third-parties.

== Description ==

"MicroID enables anyone to claim verifiable ownership over content hosted
anywhere on the web" ([microid.org][]).  This plugin makes that easier by
generating MicroIDs for you based on an identifier WordPress already has, or by
an additional identifier you provide.

[microid.org]: http://microid.org/

Partially inspired by other MicroID plugins by [Richard Miller][] and [Eran Sandler][].

[Richard Miller]: http://www.richardkmiller.com/wp-microid
[Eran Sandler]: http://eran.sandler.co.il/microid-wordpress-plugin/

== Installation ==

This plugin follows the standard WordPress installation method:

1. Upload `microid.php` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Configure through the 'MicroID' section of the 'Options' menu

== Frequently Asked Questions ==

= How do I get help if I have a problem? =

Please direct support questions to the "Plugins and Hacks" section of the
[WordPress.org Support Forum][].  Just make sure and include the tag 'microid',
so that I'll see your post.  Additionally, you can file a bug report at
<http://dev.wp-plugins.org/report>.

[WordPress.org Support Forum]: http://wordpress.org/support/

== Screenshots ==

1. Add a new MicroID using an Identity URI or the default admin data.

== Changelog ==

= version 1.1 =
 - display microid on author pages (props: Craig Andrews)
 - check is_front_page() in addition to is_home()

= version 1.0 =
 - initial release

