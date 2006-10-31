<?php

/*
Plugin Name: Gravatar
Plugin URI: http://www.gravatar.com/implement.php#section_2_2
Description: This plugin allows you to generate a gravatar URL complete with rating, size, default, and border options. See the <a href="http://www.gravatar.com/implement.php#section_2_2">documentation</a> for syntax and usage.
Version: 1.1
Author: Tom Werner
Author URI: http://www.mojombo.com/

CHANGES
2004-11-14 Fixed URL ampersand XHTML encoding issue by updating to use proper entity
*/

function gravatar($rating = false, $size = false, $default = false, $border = false) {
	global $comment;
	$url = "http://www.gravatar.com/avatar.php?gravatar_id=".md5($comment->comment_author_email);
	if($rating && $rating != '')
		$url .= "&amp;rating=".$rating;
	if($size && $size != '')
		$url .="&amp;size=".$size;
	if($default && $default != '')
		$url .= "&amp;default=".urlencode($default);
	if($border && $border != '')
		$url .= "&amp;border=".$border;

	if (function_exists('url_cache')) $cached = url_cache($url);

	if (ereg(get_option('siteurl'), $cached)) {
		echo $cached;
	} else {
		echo gravatar_cache_default($url, $default);
	}
}


function gravatar_cache_default($url, $default) {

	$contents = uc_get_contents($default, null, null);

	if ($contents)
		uc_cache_contents($url, $contents);

	if (uc_is_cached($url, 3600))
		return uc_get_local_url($url);
	else 
		return $url;
}

?>
