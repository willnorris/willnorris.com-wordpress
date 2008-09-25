<?php
/*
Plugin Name: Woopra
Plugin URI: http://www.woopra.com
Description: This plugin adds Woopra's real-time analytics to any WordPress installation.  Simply sign up at Woopra.com, then activate the plugin and configure your site ID in the Woopra settings.
Version: 1.3.4
Author: Elie El Khoury
Author URI: http://www.woopra.com
*/


function woo_session_start() {
	global $woopra_events;

	if (!isset($_SESSION)) {
 		session_start();
	}

	if (isset($_SESSION['temp_woopra_comment'])) {
		$comment_id = $_SESSION['temp_woopra_comment'];	
		$tmp_comment = get_comment($comment_id);
		$woopra_events['Comment'] = $tmp_comment->comment_content;
		unset($_SESSION['temp_woopra_comment']);
	}
}

function woo_detect() {
	global $woopra_visitor;

	$author = str_replace("\"","\\\"",$_COOKIE['comment_author_'.COOKIEHASH]);
	$email = str_replace("\"","\\\"",$_COOKIE['comment_author_email_'.COOKIEHASH]);
	if (!empty($author)) {
		$woopra_visitor['name'] = $author;
		$woopra_visitor['email'] = $email;
	}

	if (is_user_logged_in()) {
		global $userdata;
		get_currentuserinfo();
		$woopra_visitor['name'] = $userdata->user_login;
		$woopra_visitor['email'] = $userdata->user_email;
	}
}

function woo_comment($comment_id) {
	if (get_option('woopra_show_comments')=='YES') {
		if (!isset($_SESSION)) {
 			session_start();
		}
		$_SESSION['temp_woopra_comment'] = $comment_id;
	}
}

function woo_widget() {

	if (!woopra_do_track()) {
		return;
	}

	global $woopra_visitor;
	global $woopra_events;
	
	$woopra_id = get_option('woopra_website_id');

	if ($woopra_id == NULL || $woopra_id == '') {
		echo '<!-- Woopra Plugin requires setup -->';
		return;
	}

	echo "<script type=\"text/javascript\">\r\n";
	echo "var woopra_id = '" . $woopra_id . "';\r\n";
	echo "var woopra_visitor = new Array();\r\n";
	echo "var woopra_event = new Array();\r\n";

	if (get_option('woopra_auto_tag_commentators') == 'YES' && $woopra_visitor['name'] != NULL) {
		echo "woopra_visitor['name'] = '" . js_escape( $woopra_visitor['name'] ) . "';\r\n";
		echo "woopra_visitor['email'] = '" . js_escape($woopra_visitor['email']) . "';\r\n";
		echo "woopra_visitor['avatar'] = 'http://www.gravatar.com/avatar.php?gravatar_id=" . md5(strtolower($woopra_visitor['email'])) . "&size=60&default=http%3A%2F%2Fstatic.woopra.com%2Fimages%2Favatar.png';\r\n";
	}

	if ($woopra_events) {
		foreach ($woopra_events as $woopra_event_key => $woopra_event_value) {
			echo "woopra_event['$woopra_event_key'] = '" . js_escape($woopra_event_value) . "';\r\n";
		}
	}

	echo "</script>\r\n";

       $websiteid = get_option('woopra_website_id');
	echo "<script src=\"http://static.woopra.com/js/woopra.js\" type=\"text/javascript\"></script>";

}

function woopra_do_track() {
	if (get_option('woopra_ignore_admin') == 'YES' && function_exists('current_user_can') && current_user_can('manage_options')) {
		return false;
	}

	
	return true;
}

function woopra_add_menu () {
	if (function_exists('add_menu_page')) {
		if (get_option('woopra_analytics_tab') && get_option('woopra_analytics_tab') =='toplevel') {
			add_menu_page(
				"Woopra Analytics"
				, "Woopra Analytics"
				, "manage_options"
				, "woopra_analytics.php"
				, "woopra_analytics_show_content"); 
		}
		else {
			add_submenu_page(
				'index.php',
 				"Woopra Analytics"
 				, "Woopra Analytics"
				, 'manage_options'
				, "woopra-analytics"
 				, "woopra_analytics_show_content");
		}
	
	}
	if (function_exists('add_options_page')) {
		 add_options_page(
		 	"Woopra Settings"
		 	, "Woopra Settings"
		 	, 7
		 	, basename(__FILE__)
		 	, 'woopra_print_admin_html');
	}
}

function woopra_print_admin_html() {
	?>
	<div class="wrap">
	
	<?php

                $smsg = "";
		if (isset($_POST['submitoptions'])) {
			check_admin_referer('update-woopra-options');
			if (isset($_POST['autotag'])) {
				update_option('woopra_auto_tag_commentators','YES');
			}
			else {
				update_option('woopra_auto_tag_commentators','NO');
			}

			if (isset($_POST['woopratab'])) {
				update_option('woopra_analytics_tab',$_POST['woopratab']);
			}

			if (isset($_POST['trackadmin'])) {
				update_option('woopra_track_admin','YES');
			}
			else {
				update_option('woopra_track_admin','NO');
			}
			
			if (isset($_POST['ignoreadmin'])) {
				update_option('woopra_ignore_admin','YES');
			}
			else {
				update_option('woopra_ignore_admin','NO');
			}
			
			if (isset($_POST['showcomments'])) {
				update_option('woopra_show_comments','YES');
			}
			else {
				update_option('woopra_show_comments','NO');
			}
			
			if (isset($_POST['apikey'])) {
				update_option('woopra_api_key', $_POST['apikey']);
			}
			else {
				update_option('woopra_api_key','');
			}

			update_option('woopra_website_id', $_POST['websiteid']);
			?>

			<div id="message" class="updated fade"><p>Settings updated!</p></div>

	<?php } ?>
        
	<h2>Woopra Settings</h2>
	<p>For more info about installation and customization, please visit <a href="http://www.woopra.com/installation-guide">the installation page in your member&#8217;s area</a></p>
	<form action="" method="post">
	<?php if ( function_exists('wp_nonce_field') ) wp_nonce_field('update-woopra-options'); ?>
	<table class="form-table">
		<tr valign="top">
		<th scope="row">Website ID</th>
		<td>
		<input type="text" value="<?php echo attribute_escape( get_option('woopra_website_id') ); ?>" id="websiteid" name="websiteid"/><br/>
		You can find the Website ID in <a href="http://www.woopra.com/members/">your member&#8217;s area</a>
		</td>
		</tr>
		<tr valign="top">
		<th scope="row">API Key <small>(Optional)</small></th>
		<td>
		<input type="text" value="<?php echo attribute_escape( get_option('woopra_api_key') ); ?>" id="apikey" name="apikey"/><br/>
		You can find the Website's API Key in <a href="http://www.woopra.com/members/">your member&#8217;s area</a>
		</td>
		</tr>
		<tr valign="top">
		<th scope="row">Show Analytics</th>
		<td>
		<input type="radio" <?php echo (get_option('woopra_analytics_tab') || get_option('woopra_analytics_tab')!='toplevel')?"checked":""; ?> id ="woopratab1" name="woopratab" value="dashboard"/> <label for="woopratab1">At the dashboard menu</label><br />
		<input type="radio" <?php echo (get_option('woopra_analytics_tab') && get_option('woopra_analytics_tab')=='toplevel')?"checked":""; ?> id ="woopratab2" name="woopratab" value="toplevel"/> <label for="woopratab2">At the top level menu</label>
		</td>
		</tr>
		<tr valign="top">
		<th scope="row">Ignore Administrator</th>
		<td>
		<input type="checkbox" <?php echo (get_option('woopra_ignore_admin')=='YES')?"checked":""; ?> id ="ignoreadmin" name="ignoreadmin"/> <label for="ignoreadmin">Ignore Administrator Visits</label><br />Enable this check box if you want Woopra to ignore your or any other administrator visits.
		</td>
		</tr>
		<tr valign="top">
		<th scope="row">Admin Area</th>
		<td>
		<input type="checkbox" <?php echo (get_option('woopra_track_admin')=='YES')?"checked":""; ?> id ="trackadmin" name="trackadmin"/> <label for="trackadmin">Track admin pages</label><br />Admin pages are all pages under <?php echo get_option('siteurl'); ?>/wp-admin/
		</td>
		</tr>
		<tr valign="top">
		<th scope="row">Auto Tagging</th>
		<td>
		<input type="checkbox" <?php echo (get_option('woopra_auto_tag_commentators')=='YES')?"checked":""; ?> id="autotag" name="autotag"/> <label for="autotag">Automatically tag members &amp; commentators</label>
		</td>
		</tr>
		<tr valign="top">
		<th scope="row">Show Comments</th>
		<td>
		<input type="checkbox" <?php echo (get_option('woopra_show_comments')=='YES')?"checked":""; ?> id="showcomments" name="showcomments"/> <label for="showcomments">Show comments as they are posted.</label><br />You will see an excerpt of the comment in the Woopra Live section
		</td>
		</tr>
	</table>
	<p class="submit"><input type="submit" name="submitoptions" value="Save Changes" /></p>
	</form>
	</div>
	
<?php 
}


function woopra_analytics_head() {
	echo "<script src=\"". get_option('siteurl') ."/wp-content/plugins/woopra/woopra_analytics.js?1\"></script>\r\n";
	echo "<script src=\"". get_option('siteurl') ."/wp-content/plugins/woopra/swfobject.js\"></script>\r\n";
	echo "<script src=\"". get_option('siteurl') ."/wp-content/plugins/woopra/datepicker.js\"></script>\r\n";
	echo "<link rel='stylesheet' href='". get_option('siteurl') ."/wp-content/plugins/woopra/woopra_analytics.css' type='text/css' />";
	echo "<link rel='stylesheet' href='". get_option('siteurl') ."/wp-content/plugins/woopra/datepicker.css' type='text/css' />";
}

include 'woopra_analytics.php';

add_action('admin_menu', 'woopra_add_menu');
add_action('template_redirect', 'woo_detect');
add_action('template_redirect', 'woo_session_start');
add_action('comment_post', 'woo_comment');
add_action('admin_print_scripts', 'woopra_analytics_head');
add_action('wp_footer', 'woo_widget', 10);


if (get_option('woopra_track_admin') == 'YES') {
	add_action('admin_footer', 'woo_widget');
}


?>