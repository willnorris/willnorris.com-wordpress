<?php
/*
Plugin Name: Angsuman's Permanent Redirect
Plugin URI: http://blog.taragana.com/
Description: Permanently redirects posts and pages that contains "redirect" custom field, to the value of the "redirect" custom field.
Author: Angsuman Chakraborty
Version: 1.0
Author URI: http://blog.taragana.com/
*/
?>
<?php
$redirect_key = 'redirect';
// DO NOT MODIFY BELOW THIS LINE
add_action('template_redirect', 'redirector');
function redirector() {
	global $wp_query, $redirect_key;
	
	if(is_single() || is_page()) {
		$redirect = get_post_meta($wp_query->post->ID, $redirect_key, true);
		if('' != $redirect) {
            header("HTTP/1.1 301 Moved Permanently"); 
            header("Location: " . $redirect);
            header("Status: 301 Moved Permanently");
            exit(); 			
		}
	}
}
?>