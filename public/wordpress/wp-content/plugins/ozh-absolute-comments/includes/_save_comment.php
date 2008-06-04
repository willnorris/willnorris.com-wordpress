<?php
/*
Part of Plugin: Absolute Comments
*/

if (!defined('ABSPATH')) require_once('../../../../wp-config.php');
if (!function_exists('wp_ozh_cqr_take_over') or !current_user_can('edit_posts')) die('You cannot do this');


if (!@$_POST) exit;

check_admin_referer('ozh-quickreply');

if (isset($_POST['action']) && $_POST['action'] == 'cqr_ajaxstore') {
	$cqr_doajax = true;
} else {
	$cqr_doajax = false;
}

$cqr_replyto = intval(attribute_escape($_POST['comment_ID']));
$cqr_threaded = intval(attribute_escape($_POST['cqr_threaded']));

global $user_ID;
$comment_content = trim($_POST['content']);
$comment_post_ID = intval(attribute_escape($_POST['comment_post_ID']));
$user = get_userdata( $user_ID );
if ( !empty($user->display_name) )
	$comment_author = $user->display_name;
else 
	$comment_author = $user->user_nicename;
$comment_author_email = $user->user_email;
$comment_author_url = $user->user_url;
$commentdata = compact('comment_post_ID', 'comment_author', 'comment_author_email', 'comment_author_url', 'comment_content', 'comment_type', 'user_ID');

// Trick: we don't want wp_die() to send any header, so we pretend we do_action 'admin_head'. Enclosed into output buffering to catch any unwanted display it could generate.
/**/
ob_start();
do_action('admin_head');
ob_end_clean();
/**/

$comment_id = wp_new_comment( $commentdata );

if ($cqr_threaded == '1') {
	global $wpdb;
	$q = $wpdb->query("UPDATE {$wpdb->comments} SET comment_parent='$cqr_replyto' WHERE comment_ID='$comment_id'");
}

if ($cqr_doajax) {
	header('Content-Type: text/xml');
	echo "<?"."xml version=\"1.0\"?>\n"; 
	echo <<<XML
<response>
	<id>$comment_id</id>
	<replyto>$cqr_replyto</replyto>
	<post>$comment_post_ID</post>
</response>
XML;
} else {
	$location = get_bloginfo('wpurl').'/wp-admin/edit-comments.php';
	wp_redirect($location);
}
?>
