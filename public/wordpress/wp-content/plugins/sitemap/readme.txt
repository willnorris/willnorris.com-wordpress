=== Google Sitemap Generator for WordPress ===
Contributors: arnee
Donate link: http://www.arnebrachhold.de/redir/sitemap-paypal
Tags: google, sitemaps, google sitemaps, yahoo, man, xml sitemap
Requires at least: 2.1
Stable tag: 3.0

This plugin will create a Google sitemaps compliant XML-Sitemap of your WordPress blog.

== Description ==

This plugin will create a Google sitemaps compliant XML-Sitemap of your WordPress blog. It supports all of the WordPress generated pages as well as custom ones. Everytime you edit or create a post, your sitemap will be updated and all major search engines which support the sitemap protocol like ASk.com, Google, MSN Search and YAHOO are notified about the update.

== Installation ==

1. Upload the full directory into your wp-content/plugins directory
2. Make your blog directory writable OR create two files named sitemap.xml and sitemap.xml.gz and make them writable via CHMOD. In most cases, your blog directory is already writable so you don't need to do anything.
3. Double make sure that your blog directory is writable or two writable files named sitemap.xml and sitemap.xml.gz exist!
4. Activate it in the Plugin options
5. Edit or publish a post or click on Rebuild Sitemap on the Sitemap Administration Interface in the menu under Options -> XML Sitemap

== Frequently Asked Questions == 

= I have no comments (or disabled them) and all my postings have a priority of zero! =

Disable automatic priority calculation and define a static priority for posts!

= Do I always have to click on "Rebuild Sitemap" if I modified a post? =

No! If you edit/publish/delete a post, your sitemap gets regenerated!

= So much configuration options… Do I need to change them? =

No! Only if you want. Default values should be ok!

= Works it with all WordPress versions? =

This version works with WordPress 2.1 and better. If you're using an older version, plese check the plugin homepage for the legacy releases.

= I get an fopen error and / or permission denied =

If you get permission errors make sure that the script has writing rights in your blog directory. Try to create the sitemap.xml resp. sitemap.xml.gz at manually and upload them with a ftp program and set the rights with CHMOD. Then restart sitemap generation on the administration page. A good tutorial for changing file permissions can be found on the WordPress Codex.

= Do I really need to use this plugin? =

Maybe not if Google knows you page very well and visits your blog every day. If not, it's a good method to tell google about your pages and the last change of them. This makes Google possible to refresh the page only if it's needed and you save your bandwidth.

== Screenshots ==

1. Administration interface in WordPress 2.