<?php
/*
Part of Plugin: Absolute Comments
*/

if (!defined('ABSPATH')) require_once('../../../../wp-config.php');
if (!function_exists('wp_ozh_cqr_take_over') or !current_user_can('edit_posts')) die('You cannot do this');

if ($wp_version >= '2.3') {
	require_once(ABSPATH.'/wp-admin/includes/admin.php');
} else {
	require_once(ABSPATH.'/wp-admin/admin-functions.php') ;
}

global $comments;

$cid = intval(attribute_escape($_GET['id']));

// Fetch latest 5 comments
list($comments, $total) = _wp_get_comment_list( false, 0, 5 );

// Find comment which has id $cid
while( current($comments)->comment_ID != $cid ) {
	if (next($comments) === false) {
		// We've looped through and didn't find it :
		$script = "<script type=\"text/javascript\">
		window.location = '".get_bloginfo('wpurl')."/wp-admin/edit-comments.php';
		location.replace = '".get_bloginfo('wpurl')."/wp-admin/edit-comments.php';
		</script>";
		die("$script <p>Refreshing...</p>");
	}
}

$comment = current($comments);
get_comment( $comment ); 
_wp_comment_list_item( $comment->comment_ID );

?>