<?php
/*
Plugin Name: Follow URL
Plugin URI: http://blog.taragana.com
Description: This plugin strips nofollow tags from your comments and comment author URL, which are inserted by default in WordPress 1.5
Version: 1.0
Author: Angsuman Chakraborty
Author URI: http://blog.taragana.com/
*/
// This copies the code from make_clickable and just strips the nofollow tags 
function make_normal_clickable($ret) {
	global $comment;

	if ($comment->user_id > 0) {
		$ret = ' ' . $ret . ' ';
		$ret = preg_replace("#([\s>])(https?)://([^\s<>{}()]+[^\s.,<>{}()])#i", "$1<a href='$2://$3'>$2://$3</a>", $ret);
		$ret = preg_replace("#(\s)www\.([a-z0-9\-]+)\.([a-z0-9\-.\~]+)((?:/[^ <>{}()\n\r]*[^., <>{}()\n\r]?)?)#i", "$1<a href='http://www.$2.$3$4'>www.$2.$3$4</a>", $ret);
		$ret = preg_replace("#(\s)([a-z0-9\-_.]+)@([^,< \n\r]+)#i", "$1<a href=\"mailto:$2@$3\">$2@$3</a>", $ret);
		$ret = trim($ret);
	}
	return $ret;
}

function strip_nofollow($ret) {
	global $comment;

	if ($comment->user_id > 0) {
		$ret = preg_replace("/rel='external nofollow'>/","rel='external'>", $ret);
	}
	return $ret;
}
remove_filter('pre_comment_content', 'wp_rel_nofollow', 15);
remove_filter('comment_text', 'make_clickable');
add_filter('comment_text', 'make_normal_clickable');
add_filter('get_comment_author_link', 'strip_nofollow');
?>
