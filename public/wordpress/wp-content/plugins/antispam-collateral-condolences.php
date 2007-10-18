<?php
/*
Plugin Name: Antispam Collateral Condolences
Version: 0.2
Plugin URI: http://txfx.net/code/wordpress/antispam-collateral-condolences/
Description: Notifies people when their comment is caught as spam.
Author: Mark Jaquith
Author URI: http://txfx.net/
*/

function txfx_die_if_spam($original_redirect, $comment) {

	if ( $comment && ( 'spam' == $comment->comment_approved || '0' == $comment->comment_approved)  ) {
		$original_redirect = preg_replace('|#.*?$|', '', $original_redirect);
		$caught_as = ( '0' == $comment->comment_approved ) ? 'moderation' : 'spam';
		return add_query_arg('caught_as', $caught_as, $original_redirect) . '#comment-caught';
	}

	return remove_query_arg('caught_as', $original_redirect);
}

function txfx_comment_was_caught() {
	if ( isset($_GET['caught_as']) )
		return true;
	return false;
}

function txfx_get_caught_message() {
	if ( !txfx_comment_was_caught() )
		return false;
	elseif ( 'moderation' == $_GET['caught_as'] )
		return 'Your comment was placed in the moderation queue and the site administrator has been notified.  Your comment will not appear on the site until the administrator has approved it.';
	elseif ( 'spam' == $_GET['caught_as'] )
		return 'Your comment was caught by this site\'s anti-spam defenses.  Please notify the site administrator so your comment can be rescued.';
	else
		return false;
}

function txfx_alert_user_js() {
	if ( false === ( $caught_message = txfx_get_caught_message() ) )
		return;
	echo "<script type='text/javascript'><!--\nalert('" . js_escape($caught_message) . "');\n--></script>";
}

function txfx_alert_user_comment_form() {
	if ( false === ( $caught_message = txfx_get_caught_message() ) )
		return;
	echo '<p id="comment-caught">' . $caught_message . '</p>';
}

add_filter('comment_post_redirect', 'txfx_die_if_spam', 999, 2);

// Choose one, or disable both and use your own
add_action('wp_head', 'txfx_alert_user_js');
// add_action('comment_form', 'txfx_alert_user_comment_form');

?>
