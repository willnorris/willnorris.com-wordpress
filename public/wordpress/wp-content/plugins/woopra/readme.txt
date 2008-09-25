=== Woopra Analytics Plugin ===
Contributors: eliekhoury, markjaquith, shanef
Web site: http://www.woopra.com
Tags: statistics, analytics, stats, real-time
Requires at least: 2.0
Tested up to: 2.6.1
Stable tag: 1.3.4

This plugin adds Woopra's real-time analytics to any WordPress installation.  Activate the plugin and configure your site ID in the Woopra settings.

== Description ==

Woopra is the world's most comprehensive, information rich, easy to use, real-time Web tracking and analysis application.

Features include:

*   Live Tracking and Web Statistics
*   A rich user interface and client monitoring application
*   Real-time Analytics
*   Manage Multiple Blogs and Websites
*   Deep analytic and search capabilities
*   Click-to-chat
*   Visitor and member tagging
*   Real-time notifications
*   Easy Installation and Update Notification

== Installation ==

These installation instructions assume you have an active account established on Woopra.com.  If not, please visit the site and sign up for service.

1. Extract the Woopra.zip file to a location on your local machine
2. Upload the `woopra` folder and all contents into the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Configure your Website ID and API keys in the Woopra Settings menu
(Your ID and API key can be found in the Members area on Woopra.com)

IMPORTANT NOTE: 
In order for the WordPress Plugin to work, your WordPress theme must have the following code immediately before the </BODY> element in the footer.php file:

    `<?php wp_footer(); ?>`

For more detailed installation instructions refer to:
http://www.woopra.com/installation-guide/