<?php

/*
Plugin Name: WP-MicroID
Plugin URI: http://www.richardkmiller.com/wp-microid
Description: Inserts MicroID tags in the head and around the posts and comments.
Version: 1.0
Author: Richard K Miller
Author URI: http://www.richardkmiller.com

Copyright (c) 2006 Richard K Miller
Released under the GNU General Public License (GPL)
http://www.gnu.org/licenses/gpl.txt
*/

function microid_hash($email, $url)
{
	return sha1(sha1("mailto:" . trim($email)) . sha1(trim($url)));
}

function insert_microid_meta_tag()
{
	$microid = microid_hash(get_bloginfo('admin_email'), get_bloginfo('url'));
	echo "<meta name='microid' content='$microid' />";
}

function add_microid_on_post($content = '')
{
	$microid = microid_hash(get_the_author_email(), get_permalink());
	return "<div class='microid-$microid'>$content</div>";
}

function add_microid_on_comment($comment = '')
{
	$microid = microid_hash(get_comment_author_email(), get_permalink());
	return "<div class='microid-$microid'>$comment</div>";
}

add_action('wp_head', 'insert_microid_meta_tag');

add_filter('the_content', 'add_microid_on_post');

add_filter('comment_text', 'add_microid_on_comment');

?>
