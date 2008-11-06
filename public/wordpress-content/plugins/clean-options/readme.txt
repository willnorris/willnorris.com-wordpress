=== Clean Options ===
Contributors: Mittineague
Tags: remove options, wp_options
Requires at least: 2.1
Tested up to: 2.6
Stable tag: Trunk

== License ==
Released under the terms of the GNU General Public License.

== Version History ==
Beta 0.9.7 06-Aug-2008  
- provided for time limit increase  
- updated the $known_ok array (for WordPress 2.6)  
- added test for empty option_name field [autoload != yes block]  

Beta 0.9.6 19-Oct-2007  
- added test for empty option_name field [autoload = yes block]  
- tweaked error handling  

Beta 0.9.5 18-Oct-2007  
- scoped $cur_wp_ver  
- changed WP_Error obj syntax  
- replaced get_alloptions() with get_all_yes_autoload_options()  
- updated the $known_ok array  
- removed $wpdb->hide_errors() from get_all_no_autoload_options()  
- removed global $wp_queries from get_all_no_autoload_options()  

Beta 0.9.4 06-Oct-2007  
- added WordPress ver. 2.3 compatibility  

Beta 0.9.3 06-Jul-2007  
- updated/improved WP core options array  
- provided for memory limit increase  
- optimized memory usage  

Beta 0.9.2 25-Apr-2007  
- improved protection against accidental removal of WP core options  
- - expanded the $known _ ok array  
- - test for non-default install prefix user _ roles option  
- - added backup suggestion  

Beta 0.9.1 24-Apr-2007  
- changed a 'hard-coded' wp_ to $wpdb->  

Beta 0.9.0 22-Apr-2007  
-  added get _ all "rss _ " options  
-  changed str_replace() to wordwrap()  

Beta 0.7.1 17-Apr-2007  
-  added "Further Information" section  

Beta 0.7.0 13-Apr-2007  

== Description ==
Finds orphaned options and allows for their removal from the wp _ options table.

== Long Description ==
= Orphaned Options List =

Listed Options are those that are found in the wp _ options table but are not referenced by "get _ option" or "get _ settings" by any of the PHP files located within your blog directory. If you have deactivated plugins and / or non-used themes in your directory, the associated options will not be considered orphaned until the files are removed.  
Every "rss _ hash" option in the wp_options table will be shown, including current ones.

Non-selectable Options are known to have been created from files present during upgrade or backup, or are legitimate options that do not "fit" the search for get _ option or get _ settings. If you wish to remove them by other means, do so at your own risk.

This plugin finds **ALL** of the "RSS" Options added to the wp _ options table from the blog's dashboard page and other files that parse RSS feeds and cache the results.    
In each pair, the upper option is the cached feed and the lower is the option's timestamp.  
The timestamps of the newer Options that are more likely to be current have no checkbox, but begin with "-" and end with "*# days old*" in italics.  
The timestamps of older options can be selected and end with "**# days old**" in bold.  
Please only remove the older options in which **BOTH** options of the pair can be selected.  

= Orphaned Options Preview =

Spaces have been added after every 10th character of the option _ name and every 20th character of the option _ value to preserve page layout.  
Not all options have values and / or descriptions.  
Please review this information very carefully and only remove Options that you know for certain have been orphaned or deprecated.  
It is strongly suggested that you BACKUP your database before removing any options.  

== Installation ==
1.  If you are upgrading, deactivate the plugin before step 2
2.  Upload 'cleanoptions.php' to the '/wp-content/plugins/' directory
3.  Activate the plugin through the 'Plugins' menu in WordPress
4.  Click the 'Manage' admin menu link, and select 'CleanOptions'

== Frequently Asked Questions ==

= Does this plugin have any limitations? =

The Clean Option plugin searches only PHP files in your blog's folders for get _ option('option _ name') and get _ settings('option _ name'). It does match slight variations such as get _ option - space - ( - space - " etc. but there may be instances where files use values in the wp _ options table that do not match these patterns.  
Nor does it find unused options. It finds orphaned options, that is, options that do not have any files that "get" their values. Some options are known to have been created by files that are temporary, such as during upgrade and back-up.  

This plugin finds **ALL** of the "rss _ hash" options, even those that are current. Rather than tasking the server with a script that identifies current options, this plugin indentifies options that are *likely* to be current based on their timestamp.  

Because of these limitations, the fact that unused options in the wp _ options table have only a negligible effect upon performance, and the unknown effects of removing needed options, only options that are known to have been orphaned or deprecated should be removed.

= How can I help? =

If you find any bugs with this plugin, please let me know.

== Screenshots ==
Orphaned Options List		<http://www.mittineague.com/dev/img/co-screenshot-1.jpg>

RSS Options List		<http://www.mittineague.com/dev/img/co-screenshot-1b.jpg>

Orphaned Options Preview	<http://www.mittineague.com/dev/img/co-screenshot-2.jpg>

== More Info ==
For more info, please visit  
<http://www.mittineague.com/dev/co.php>

For support, please visit (registration required to post)  
<http://www.mittineague.com/forums/viewtopic.php?t=101>

For comments / suggestions, please visit  
<http://www.mittineague.com/blog/2007/04/clean-options-plugin/>

***********************
** AN IMPORTANT NOTE **
***********************
You should not remove options from the wp _ options table unless you are certain they have been orphaned or deprecated.