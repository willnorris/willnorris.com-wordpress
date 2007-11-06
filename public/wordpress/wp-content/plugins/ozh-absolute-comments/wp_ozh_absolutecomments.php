<?php
/*
Plugin Name: Absolute Comments
Plugin URI: http://planetozh.com/blog/my-projects/absolute-comments-manager-instant-reply/
Description: Reply instantly to comments, either from the email notification, or the usual Manage page, without loading the post first.
Author: Ozh
Author URI: http://planetozh.com/
Version: 1.0
*/

/***************************************************************/
/************** Optional Editing Below *************************/

/* Note: all 'cqr' references mean 'comment quick reply', original name for the plugin */

$wp_ozh_cqr['editor_rows'] = 5;
	// integer: number of lines of the Editor for comment replying
	// false: leave as WordPress default (editable through Options / Writing page)

$wp_ozh_cqr['show_icon'] = true;
	// boolean: show little icon next to "Reply" links

$wp_ozh_cqr['prefill_reply'] = '%%name%% &amp;raquo; ';
	// string: text (HTML) pattern to prefill comment. Set to empty string to disable this feature.
	// Uses the following tokens:
	// %%link%% : comment permalink
	// %%name%% : commenter's name
	// Examples :
	//  	'%%name%% &amp;raquo; ' => 'Joe &raquo; '
	//		'@<a href="%%link%%">%%name%%</a>: ' => '@<a href="#comment-1234">Joe</a>: '
	//		'@%%name%%: ' => '@Joe: '
	
$wp_ozh_cqr['show_threaded'] = true;
	// boolean: Add option to reply in threaded	mode

$wp_ozh_cqr['show_allcomments'] = true;
	// boolean: Show link to display all comments for a post

$wp_ozh_cqr['allcomments_useWP'] = false;
	// boolean: Use WordPress' original page to show all comments from a post.
	// true: use page /wp-admin/edit.php?p=XXX (WordPress original page)
	// false: use our own custom page, which some might find better (it *is* definitely better:)

$wp_ozh_cqr['mail_promote'] = true;
	// boolean: Add a promoting link to the plugin in mail footers. Might help new people
	// find about this great plugin if you usually reply by email to comments !

/***************************************************************/
/***************** Do Not Modify Below *************************/

if (!function_exists('add_filter')) die('You cannot do this');

function wp_ozh_cqr_request_handler() {
	// Only people who can access the admin area are allowed here, of course
	if (!current_user_can('edit_posts')) return false;

	global $wp_ozh_cqr, $wp_version, $user_identity;

	$wp_ozh_cqr['path'] = dirname(wp_ozh_cqr_basename(__FILE__));
	
	// Store a reply ?
	if (isset($_POST['cqr_action']) && $_POST['cqr_action'] == 'reply') {
		wp_ozh_cqr_savereply();
		return false;
	}
	
	// Only on 'edit-comments.php' or 'edit.php' we need the cool javascript magic
	if (
		strpos($_SERVER['REQUEST_URI'], 'edit-comments.php') !== false
		or strpos($_SERVER['REQUEST_URI'], 'edit.php') !== false
	) {
		wp_enqueue_script('jquery');
		wp_enqueue_script('admin-comments');
		add_action('admin_footer','wp_ozh_cqr_print_js');
	}

	// Only on 'edit-comments?quick_reply=XX' we need to hijack the admin page
	if ( strpos($_SERVER['QUERY_STRING'], 'quick_reply=') === false )
		return false;
	
	// Still with us ? Take over the admin page. Hands up, this is a robbery!
	global $menu,$submenu,$wpdb;
	$title = __('Reply to Comments');
	$parent_file = 'edit-comments.php';
	
	require_once(ABSPATH.'/wp-admin/admin.php');
	if ($wp_version >= '2.3') {
		require_once(ABSPATH.'/wp-admin/includes/admin.php');
	} else {
		require_once(ABSPATH.'/wp-admin/admin-functions.php') ;
	}
	require_once(ABSPATH.'/wp-admin/admin-functions.php');
	require_once(ABSPATH.'/wp-includes/pluggable.php');
	require(ABSPATH . '/wp-admin/menu.php');
	require_once(ABSPATH.'/wp-admin/admin-header.php');

	if (isset($_GET['quick_reply'])) $cqr = attribute_escape($_GET['quick_reply']);
	
	// Case: ?quick_reply=view_all&post_id=XXX
	if ($cqr == 'view_all') {
		if (isset($_GET['post_id'])) $post_id = intval(attribute_escape($_GET['quick_reply']));
		wp_ozh_cqr_include('_view_all.php');
		return true;
	}
	
	// Case: ?quick_reply=XXX
	if ( ! $comment = get_comment(intval($cqr)) ) {
		echo '<div id="message" class="updated fade"><p>'.wp_ozh_cqr__('Oops, no comment with this ID.').sprintf(' <a href="%s">'.wp_ozh_cqr__('Go back').'</a>!', 'javascript:history.go(-1)').'</p></div>';
		return true;
	}
	
	$wp_ozh_cqr['comment'] = $comment;

	echo '<div class="wrap"><h2>'.wp_ozh_cqr__('Your Reply').'</h2>';
	wp_ozh_cqr_include('_reply_form.php');
	echo '</div>';

	echo "<div class='wrap'>\n";
	wp_ozh_cqr_include('_view_comment.php');
	echo "</div>\n\n";
	
	return true;
}


function wp_ozh_cqr_savereply() {
	wp_ozh_cqr_include('_save_comment.php');
}

// Hijack admin page if needed
function wp_ozh_cqr_take_over() {
	if (wp_ozh_cqr_request_handler()) {
		include(ABSPATH.'/wp-admin/admin-footer.php');
		die();
	}
}

// Add the convenient Reply link to mail notifications
function wp_ozh_cqr_email($text, $comment_id) {
	global $wp_ozh_cqr;
	$text .= wp_ozh_cqr__('Reply').': '.get_bloginfo('wpurl').'/wp-admin/edit-comments.php?quick_reply='.$comment_id."\r\n";
	if ($wp_ozh_cqr['mail_promote'])
		$text .= '[ '.wp_ozh_cqr__('Powered by')." Absolute Comments * http://planetozh.com/redir/absolute/ ]\r\n";
	return $text;
}

// Translation wrapper. If ($escape) replace all quotes '" with \' for use inside Javascript strings
function wp_ozh_cqr__($str, $escape=false) {
	if (!defined('WPLANG')) return $str;
	// Load translation file if needed, return translated if available
	global $l10n;
	if (!isset($l10n['absolutecomments']))
		load_plugin_textdomain('absolutecomments', 'wp-content/plugins/'.plugin_basename(dirname(__FILE__)).'/translations');
	$string = __($str, 'absolutecomments');
	if ($escape) $string=str_replace(array("'",'"'),array("\'","\'"),$string);
	return $string;
}

// Include the mighty javascript
function wp_ozh_cqr_print_js() {
	global $wp_ozh_cqr, $user_ID;
	wp_ozh_cqr_include('_print_js.php');
}


// Built in function plugin_basename() is broken for Win32 installs, as of WP 2.2 -- the following is added to WordPress core in 2.3
function wp_ozh_cqr_basename($file) {
	$file = str_replace('\\','/',$file); // sanitize for Win32 installs
	$file = preg_replace('|/+|','/', $file); // remove any duplicate slash
	$file = preg_replace('|^.*/wp-content/plugins/|','',$file); // get relative path from plugin dir
	return $file;
}


function wp_ozh_cqr_include($inc) {
	global $wp_ozh_cqr;
	include(ABSPATH.'/wp-content/plugins/'.$wp_ozh_cqr['path'].'/includes/'.basename($inc));
}

// All set up, now tell WP what to do
add_filter('comment_notification_text', 'wp_ozh_cqr_email', 10, 2);
if (is_admin())
	add_action('init', 'wp_ozh_cqr_take_over');

	
?>