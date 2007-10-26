=== SEO Title Tag ===
Contributors: Netconcepts
Donate link: http://www.netconcepts.com/
Tags: SEO, titles, google, meta
Requires at least: 2.0.2
Tested up to: 2.2.1
Stable tag: 2.0

Search engine optimize your blog's title tags. Mass edit the title tags of posts, pages, category pages, tag pages - indeed, any URL!

== Description ==

Title tags are arguably the most important of the on-page factors for search engine optimization ("SEO"). It blows my mind how post titles are also used as title tags by WordPress, considering that post titles should be catchy, pithy, and short-and-sweet; whereas title tags should incorporate synonyms and alternate phrases to capture additional search visibility.

Now, thankfully, there is a solution, allowing you to decouple post titles from title tags. Introducing... the SEO Title Tag WordPress plugin.

SEO Title Tag makes is dead-easy to optimize the title tags across your WordPress-powered blog or website. Not just your posts, not just your home page, but any and every title tag on your site! If this plugin, along with a few hours of keyword research and copywriting of optimized titles, doesn't make a significant impact on your search traffic, you're doing something wrong!

Features include:

*   Allows you to override a page's or a post's title tag with a custom one.

*   A Title Tag input box in the Edit Post and Write Post forms. (Previously in version 1.0 you had to use the Custom Field box.)

*   Mass editing of title tags for all posts, static pages, category pages, tag pages, tag conjunction pages, archive by month pages, - indeed, any URL - all in one go.

*   Define a custom title tag for your home page (or, more accurately, your Posts page, if you have chosen a static Front Page set under Options -> Reading), through the Options -> SEO Title Tag page in the WordPress admin.

*   Define the title tag of 404 error pages, also through Options -> SEO Title Tag.

*   Handles internal search result pages too.

*   For users of the [UltimateTagWarrior](http://www.neato.co.nz/ultimate-tag-warrior) plugin (which should be everybody!), incorporates the tag name into the title tag on "tag pages" (sorry for the confusing use of "tag" in two contexts here - as in tagging and HTML title tags).

*   (When used with Ultimate Tag Warrior) Customize the title tags on tag pages, and on tag conjunction pages too (e.g. http://www.netconcepts.com/tag/seo+articles). The latter is done through Manage -> Title Tags -> URLs; see screenshot #4 for an example.

*   Title tags of category pages can optionally be set to the category description. If you use a Meta Tag plugin like [Add Meta Tags](http://www.g-loaded.eu/2006/01/05/add-meta-tags-wordpress-plugin/), then you should not use this feature and instead let the Meta Tag plugin use the category description for the meta description on category pages.

*   If you choose to keep the blog name in your title tags (not recommended!), the order of the blog name and the title are automatically reversed, giving more keyword prominence to the title instead of the blog name. Note there is also an option to replace your blog name with a shorter blog nickname.

SEO Title Tag is authored by SEO specialist web agency [Netconcepts](http://www.netconcepts.com). Version 1.0 was by Netconcepts' president [Stephan Spencer](http://www.stephanspencer.com/). Version 2.0 was a collaborative effort - Stephan did the concept development and Netconcepts' code jockeys Oliver Kastler, Mike Harding and Elton Fry did all the heavy lifting. It is completely free and has been released as "open source" under the GPL license. So enjoy!

== Installation ==

1. (If upgrading from version 1.0 of SEO Title Tag, be sure to deactivate the old version beforehand.)
1. Upload the seo-title-tag directory and the files within it to your wp-content/plugins directory.
1. Activate the plugin.
1. Under Presentation -> Theme Editor in the WordPress admin, select "Header" from the list and replace `<title><?php bloginfo('name'); wp_title(); ?></title>` (or whatever you have in your `<title>` container) with `<title><?php if (function_exists('seo_title_tag')) { seo_title_tag(); } else { bloginfo('name'); wp_title();} ?></title>`
1. Configure the settings under Options -> SEO Title Tag. You'll want specify a title tag for your home page which will override your blog name as the home page's title tag, specify a title tag for 404 error pages, and enable the UltimateTagWarrior support if using that plugin. You can also configure here whether you want all the rest of your site's title tags to have your blog name, or a shortened version of your blog name, or neither, appended to the end.
1. For those of you with a static Front Page chosen under Options -> Reading, the "home page" described in the point above is actually the Posts page, and as such, the SEO Title Tag options page will actually will say "Posts Page" instead of "Home Page" - because it detects that you have selected a static Front Page. In such a scenario, in order to also customize the Front Page's title tag, specify a Title Tag on that page's Edit Page form, or within Manage -> Title Tags -> Pages.
1. Define custom title tags for your existing posts, static pages, category pages, tag pages and tag conjunction pages (if using UltimateTagWarrior) in the admin under Manage -> Title Tags.
1. When writing a new post/page, define a title tag by typing something into the "Title Tag (optional)" field. If you're happy to use the post title as the title tag, then you can leave it blank.

== Screenshots ==

1. Mass edit title tags of pages
2. Mass edit title tags of posts
3. Mass edit title tags of categories
4. Mass edit title tags of URLs
5. Mass edit title tags of tag pages

== To-do ==

1. support mass editing of meta descriptions?
1. import titles by uploading a file in CSV format
1. possibly rename custom option fields and table name
1. move the UTW tags to an extra table (like categories)
1. autocheck if plugin upgrade is available (like Podpress does)

== Feedback? ==

Got a bug to report? Or an enhancement to recommend? Or perhaps even some code to submit for inclusion in the next release? Great! Share your feedback with the author, Stephan Spencer, either [here](http://www.netconcepts.com/seo-title-tag-plugin/) or [here](http://www.stephanspencer.com/archives/2006/07/13/seo-title-tag-plugin/).
