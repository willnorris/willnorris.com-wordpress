<?php

/*
Plugin Name: Full Text Feed
Version: 1.04
Plugin URI: http://cavemonkey50.com/code/full-feed/
Description: Prevents WordPress 2.1+ from adding a more link to your website's feed.
Author: Ronald Heft, Jr.
Author URI: http://cavemonkey50.com/
*/

function ff_restore_text ($content) {
	if ( is_feed() ) {		
		global $post, $page, $pages;
		
		if ( !empty($post->post_password) ) { // if there's a password
			if ( stripslashes($_COOKIE['wp-postpass_'.COOKIEHASH]) != $post->post_password ) {	// and it doesn't match the cookie
				$content = get_the_password_form();
				return $content;
			}
		}
	
		if ( $page > count($pages) )
			$page = count($pages);
	
		$content = preg_replace('/<!--more(.*?)?-->/', '', $pages[$page-1]);
	}
	
	return $content;
}

add_filter('the_content', 'ff_restore_text', -1);

?>