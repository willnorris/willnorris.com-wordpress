=== Creative Commons Configurator ===
Donate link: http://www.g-loaded.eu/about/donate/
Tags: license, creative commons, metadata, legal
Requires at least: 1.5.2
Tested up to: 2.8.4
Stable tag: 1.2

Adds Creative Commons license information to your posts, pages and feeds. Fully customizable.


== Description ==

[Creative-Commons-Configurator](http://www.g-loaded.eu/2006/01/14/creative-commons-configurator-wordpress-plugin/ "Official Creative-Commons-Configurator Homepage") had been initially released in early 2006. It is **actively maintained**.

This plugin is the only tool a user will ever need in order to set a [Creative Commons License](http://creativecommons.org/) on a WordPress blog and control the inclusion or display of the license information and relevant metadata into the blog pages or the syndication feeds. All configuration is done via a page in the administration panel. Template tags are also available for those who need complete customization.

Features at a glance:

- Configuration page in the WordPress administration panel. No manual editing of files is needed for basic usage.
- License selection by using the web-based license selection API from CreativeCommons.org.
- The license information can be reset at any time. This action also removes the options that are stored in the WordPress database.
- Adds license information to:
 - The HTML head area of the every blog page (this is for search engine bots only – Not visible to human visitors).
 - The Atom, RSS 2.0 and RDF (RSS 1.0) feeds through the Creative Commons RSS module, which validates properly. This option is compatible only with WordPress 2 or newer due to technical reasons. It will not appear on versions older than 2.0.
 - Displays a block with license information under the published content. Basic customization (license information and formatting) is available through the configuration panel.
- Some template tags are provided for use in your theme templates.
- The plugin is ready for localization.


== Installation ==

1. Extract the compressed (zip) package in the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Visit the plugin's administration panel at `Options->License` to read the detailed instructions about customizing the display of license information.

Read more information about the [Creative-Commons-Configurator](http://www.g-loaded.eu/2006/01/14/creative-commons-configurator-wordpress-plugin/ "Official Creative-Commons-Configurator Homepage").


== Frequently Asked Questions ==

= Where can I get support? =

Creative-Commons-Configurator is released as free software without warranties or official support. You can still get first class support from the [community of users](http://www.codetrax.org/projects/wp-cc-configurator/boards "Creative-Commons-Configurator Users").

= I found a bug! =

Please, be kind enough to [file a bug report](http://www.codetrax.org/projects/wp-cc-configurator/issues/new "File bug about Creative-Commons-Configurator") to our issue database. This is the only way to bring the issue to the plugin author's attention.

= I want to request a new feature! =

Please, use our [issue database](http://www.codetrax.org/projects/wp-cc-configurator/issues "Creative-Commons-Configurator Issue Database") to submit your requests.

= How can I thank you? =

This plugin is released as free software. On the other hand, it requires time and effort to develop and maintain. I would appreciate either:

- a small [donation](http://www.g-loaded.eu/about/donate/ "Donate here") as a sign of appreciation of the effort and energy put into this project, or
- a blog post that describes why you like or dislike Creative-Commons-Configurator.

Thanks in advance!


== Screenshots ==

No screenshots have been uploaded.


== Changelog ==

Please read the dynamic [changelog](http://www.codetrax.org/projects/wp-cc-configurator/changelog "Creative-Commons-Configurator ChangeLog")

= Sat Oct 24 2009 - v1.2 =
* Released under the Apache License v2
* Added readme.txt for WordPress plugin repository
= Tue Jan 6 2009 – v1.1 =
* Use rawurldecode() on the values that are returned by the CC API.
* Removed the border attribute from the image hyperlink in order to comply with
XHTML 1.1.
= Thu Mar 15 2007 – v1.0 =
* The plugin was almost re-written from scratch. Many new features have been added and others have been modified so to provide the best functionality and ease of use.
* The license selection engine from CreativeCommons.org is now used in order to select a license for your blog. No more copying and pasting of license code.
* A new license info layer is introduced for placing under the published content. Customization of that layer is possible either from the config panel or with CSS.
* WARNING: the bccl_display_full_html_license() template tag has been replaced by bccl_full_html_license(). Make sure you update your theme templates.
* New template tags are available.
* The configuration panel has been reworked.
* The plugin is ready for translations.
= Sat Feb 24 2007 – v0.6 =
* Supports CC v3
= Wed Nov 01 2006 – v0.5 =
* When the options where modified in the administration panel, a confirmation was asked. This behaviour has been corrected and the options are saved immediately.
* Wordpress escaped some characters in the extra message that is displayed after the post’s body. This resulted in corrupted HTML code. This has been corrected (thanks John)
= Wed Oct 04 2006 – v0.4 =
* Plugin information update
= Mon Jan 16 2006 =
* Update to version 0.2
* Added a WordPress version check, so that the option to include licensing info in the feeds does not appear in older WP version than 2.0.
* Added an informational section in the configuration page about the template tags that can be used in the theme.
* Added success message after successful license reset.
* Added success message after successful license code submission.
* Added error message if license code does not seem valid.
* Added some Creative Commons license code verification. Seems to work with all licenses, but is very primitive. Only the basic HTML code structure is checked.
* The default licensing info message that is displayed after the post’s body was modified.
* Added one more option. Now a user can define some custom code that will be displayed
together with the default message below the post’s body.
* Added some template tags that can be used by a user on a theme.
* More modularization of the code.
* Minor fixes, so it works properly with other CC licenses, eg Developing Nations, Sampling etc.
* Minor form HTML code fixes.
= Sat Jan 14 2006 =
* Initial v0.1 release

