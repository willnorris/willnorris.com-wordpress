<?php
/*
Plugin Name: Comment Saver
Description: Saves comment text in a temporary cookie before it is submitted.
Author: Will Norris
Plugin URI: http://wordpress.org/extend/plugins/comment-saver/
Author URI: http://willnorris.com/
Version: 1.4
License: Dual GPL (http://www.fsf.org/licensing/licenses/info/GPLv2.html) and Modified BSD (http://www.fsf.org/licensing/licenses/index_html#ModifiedBSD)
*/


add_action('comment_form', 'comment_saver_form');
add_filter('comment_post_redirect', 'comment_saver_cleanup', 10, 2);
add_action('wp', 'comment_saver_js');


/** 
 * Get path for comment saver cookie. 
 */
function comment_saver_cookie_path() {
	$parts = parse_url(get_option('home'));
	$path = $parts['path'];
	return $path ? $path : '/';
}


/** 
 * Setup require javascript. 
 */
function comment_saver_js() {
	if (is_single() || is_comments_popup()) {
		wp_enqueue_script('jquery.cookie', plugins_url('comment-saver/jquery.cookie.min.js'), array('jquery'), false, true);
	}
}


/** 
 * Add jQuery actions to save and restore comment. 
 */
function comment_saver_form($id) { 
	$cookieName = 'comment_saver_post' . $id;
	$path = comment_saver_cookie_path();

	echo '
	<script type="text/javascript">
		jQuery(function() {
			jQuery("#commentform").submit(function() {
				jQuery.cookie("' . $cookieName . '", jQuery("#comment").val(), {expires: (1/24), path: "' . $path . '"});
			});
			if (jQuery("#comment").val() == "") {
				jQuery("#comment").val(jQuery.cookie("' . $cookieName . '"));
			}
		});
	</script>';
}


/**
 * Cleanup comment saver cookie.
 */
function comment_saver_cleanup($location, $comment) {
	$path = comment_saver_cookie_path();
	setcookie('comment_saver_post' . $comment->comment_post_ID, null, -1, $path);
	return $location;
}

?>
