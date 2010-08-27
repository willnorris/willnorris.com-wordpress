=== Cookies for Comments ===
Contributors: donncha, automattic
Tags: cookies, comments, spam
Tested up to: 3.0
Stable tag: 0.5.1
Requires at least: 2.9.2

Sets a cookie on a random URL that is then checked when a comment is posted. If the cookie is missing the comment is marked as spam. This plugin will reduce your comment spam by at least 90%, probably.

== Description ==
This plugin adds a stylesheet to your blog's html source code. When a browser loads that stylesheet a cookie is dropped. If that user then leaves a comment the cookie is checked. If it doesn't exist the comment is marked as spam.

For the adventurous, add these lines to your .htaccess and it will block spam attempts before they ever get to WordPress. Replace the Xs with the cookie that was set in your browser after viewing your blog. Make sure the lines go above the standard WordPress rules.

	`RewriteCond %{HTTP_COOKIE} !^.*XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX.*$`
	`RewriteRule ^wp-comments-post.php - [F,L]`

If you use WordPress MU, replace wp-comments-post.php above with wp-signup.php to block spam signups.

	`RewriteCond %{HTTP_COOKIE} !^.*XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX.*$`
	`RewriteRule ^wp-signup.php - [F,L]`

== Installation ==
Copy into your plugins folder and activate. If you are using a caching plugin such as [WP Super Cache](http://ocaoimh.ie/wp-super-cache/) make sure you clear the cache after enabling this plugin.

== Changelog ==

= 0.5.1 =
* Generate cfc_key all the time if it's missing, not just on serving the css html
* Added MU signup form mod_rewrite rules to docs and admin page
* Added Settings page link to plugins page.
* Add explanation text to css file.
* Add docs on how to use CFC to protect the MU signup form
* Show htaccess rules on admin page.
* Don't let wp-super-cache cache this page.
* Store cfc_key in sitemeta for WordPress MU sites
* Added mod_rewrite rules to block spam comments before they get to WordPress

== Frequently Asked Questions ==

= The cookie isn't being set by the plugin. Why? =

If you use wp-minify make sure you add the Cookies for Comments CSS file to the list of CSS files that shouldn't be minified.
