<?php
/*
Part of Plugin: Absolute Comments
*/

function auth_redirect() {return;} // We don't want a login form. Security & Permissions are handled by current_user_can()

if (!defined('ABSPATH')) require_once('../../../../wp-config.php');
if (!function_exists('wp_ozh_cqr_take_over') or !current_user_can('edit_posts')) die('You cannot do this');

require_once(ABSPATH.'/wp-admin/admin.php');

if ( get_option('show_avatars') )
	add_filter( 'comment_author', 'floated_admin_avatar' );

global $comments;

$cid = intval(attribute_escape($_GET['id']));
$checkbox = (intval(attribute_escape($_GET['checkbox'])) == 1)? false : true;

// Fetch latest 5 comments
list($comments, $total) = _wp_get_comment_list( '',false, 0, 5 );

// Find comment which has id $cid
while( true ) {
	$_current = current($comments);
	$current = $_current->comment_ID;
	if ($current == $cid) break;
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

_wp_comment_row($comment->comment_ID, 'detail', false, $checkbox );

?>