=== Comment Saver ===
Contributors: willnorris
Tags: comments
Requires at least: 2.8
Tested up to: 2.8
Stable tag: 1.4

Save comment content in a cookie in case something goes wrong while posting.


== Description ==

Comment Saver temporarily stores the content of comments in a browser cookie
just in case something goes wrong while submitting the comment.  For example,
if the user forgot to include their name and email address and those fields are
required, the comment may be lost on some browsers.  When the user returns to
the comment form, Comment Saver will automatically populate the comment box if
the cookie is still set.  It will remove the temporary cookie once the comment
has been successfully submitted, or after one hour.  


== Installation ==

This plugin follows the [standard WordPress installation method][]:

1. Upload the `comment-saver` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. That's it... there's nothing to configure

[standard WordPress installation method]: http://codex.wordpress.org/Managing_Plugins#Installing_Plugins

== Changelog ==

= version 1.4 =
 - load jQuery.cookie in footer (plugin now requires 2.8)
 - don't call cookie stuff into jQuery ready

= version 1.3 = 
 - use plugins_url (plugin now requires WP 2.6)
 - code cleanup

= version 1.2 =
 - remove accidental php5 dependency

= version 1.1 =
 - remove accidental test code (switch back to using pure js to set cookie)

= version 1.0 =
 - initial release
