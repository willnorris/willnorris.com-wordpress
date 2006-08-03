<?php
require_once('../../../wp-config.php');
header('Content-type: ' . get_option('html_type') . '; charset=' . get_option('blog_charset'));
global $comment, $comments, $post, $wpdb, $user_ID, $user_identity, $user_email, $user_url;

function fail($s) {
	header('HTTP/1.0 500 Internal Server Error');
	echo $s;
	exit;
}

foreach($_POST as $k=>$v) {
	$_POST[$k] = urldecode($v);
}

$comment_post_ID = (int) $_POST['comment_post_ID'];

$post_status = $wpdb->get_var("SELECT comment_status FROM $wpdb->posts WHERE ID = '$comment_post_ID'");

if ( empty($post_status) ) {
	do_action('comment_id_not_found', $comment_post_ID);
	fail(__('The post you are trying to comment on does not curently exist in the database.','k2_domain'));
} elseif ( 'closed' ==  $post_status ) {
	do_action('comment_closed', $comment_post_ID);
	fail(__('Sorry, comments are closed for this item.','k2_domain'));
}

$comment_author       = trim($_POST['author']);
$comment_author_email = trim($_POST['email']);
$comment_author_url   = trim($_POST['url']);
$comment_content      = trim($_POST['comment']);

// If the user is logged in
get_currentuserinfo();
if ( $user_ID ) :
	$comment_author       = addslashes($user_identity);
	$comment_author_email = addslashes($user_email);
	$comment_author_url   = addslashes($user_url);
else :
	if ( get_option('comment_registration') )
		fail(__('Sorry, you must be logged in to post a comment.','k2_domain'));
endif;

$comment_type = '';

if ( get_settings('require_name_email') && !$user_ID ) {
	if ( 6 > strlen($comment_author_email) || '' == $comment_author )
		fail(__('Error: please fill the required fields (name, email).','k2_domain'));
	elseif ( !is_email($comment_author_email))
		fail(__('Error: please enter a valid email address.','k2_domain'));
}

if ( '' == $comment_content )
	fail(__('Error: please type a comment.','k2_domain'));

$commentdata = compact('comment_post_ID', 'comment_author', 'comment_author_email', 'comment_author_url', 'comment_content', 'comment_type', 'user_ID');

	// Simple duplicate check
	$dupe = "SELECT comment_ID FROM $wpdb->comments WHERE comment_post_ID = '$comment_post_ID' AND ( comment_author = '$comment_author' ";
	if ( $comment_author_email )
		$dupe .= "OR comment_author_email = '$comment_author_email' ";
		$dupe .= ") AND comment_content = '$comment_content' LIMIT 1";
	if ( $wpdb->get_var($dupe) )
		fail(__('Duplicate comment detected; it looks as though you\'ve already said that!','k2_domain'));
                
$new_comment_ID = wp_new_comment($commentdata);

if ( !$user_ID ) :
        setcookie('comment_author_' . COOKIEHASH, stripslashes($comment_author), time() + 30000000, COOKIEPATH, COOKIE_DOMAIN);
        setcookie('comment_author_email_' . COOKIEHASH, stripslashes($comment_author_email), time() + 30000000, COOKIEPATH, COOKIE_DOMAIN);
        setcookie('comment_author_url_' . COOKIEHASH, stripslashes($comment_author_url), time() + 30000000, COOKIEPATH, COOKIE_DOMAIN);
endif;

$comment = $wpdb->get_row("SELECT * FROM {$wpdb->comments} WHERE comment_ID = " . $new_comment_ID);

$post->comment_status = $wpdb->get_var("SELECT comment_status FROM {$wpdb->posts} WHERE ID = {$comment_post_ID}");

ob_start();
$comments = array($comment);
include(TEMPLATEPATH . '/comments.php');
$commentout = ob_get_clean();
preg_match('#<li(.*?)>(.*)</li>#ims', $commentout, $matches);
echo "<li style=\"display:none\"".$matches[1].">".$matches[2]."</li>";

?>