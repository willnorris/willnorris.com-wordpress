<?php
/*
Plugin Name: WP-Imagefit
Plugin URI: http://factoryjoe.com/projects/wp-imagefit
Description: WP-Imagefit is a plugin that extends jQuery to proportionally resize images posts to fit the width of the containin column. Original jQuery <a href="http://www.ollicle.com/eg/jquery/imagefit/">Imagefit plugin</a> written by <a href="http://www.ollicle.com/">Oliver Boermans</a>.
Author: FactoryCity
Author URI: http://factoryjoe.com/
Version: 0.2

This plugin is released under GPL:
http://www.opensource.org/licenses/gpl-license.php
*/

// todo: add support for more classnames (i.e. ".storycontent", ".entrytext")
function wp_imagefit_js() {
  $wp_imagefit_script = "
	<!-- begin imagefit script -->
  <script type=\"text/javascript\">\n
  jQuery(window).load(function(){\n
    jQuery('.hentry').imagefit();\n
  });\n
  </script>\n
	<!-- end imagefit script -->\n";
	
	echo($wp_imagefit_script);
}

/**
* Adds in the necessary JavaScript files
**/
function wpimagefit_add_scripts() {
	if (function_exists('wp_enqueue_script') && function_exists('wp_register_script')) {
		wp_register_script('jquery', get_bloginfo('wpurl') . '/wp-content/plugins/wp-imagefit/js/jquery.js');
		wp_enqueue_script('jquery.imagefit', get_bloginfo('wpurl') . '/wp-content/plugins/wp-imagefit/js/jquery.imagefit.js', array('jquery'), '0.2');
	} else {
		wpimagefit_add_scripts_legacy();
	}
}

function wpimagefit_add_scripts_legacy() {
	if (function_exists('wp_enqueue_script') && function_exists('wp_register_script')) { wpimagefit_add_scripts(); return; }
	print('<script type="text/javascript" src="'.get_bloginfo('wpurl') . '/wp-content/plugins/wordpress-automatic-upgrade/js/jquery.js"></script>'."\n");
}

add_action('init', 'wpimagefit_add_scripts');
add_action('wp_head', 'wp_imagefit_js');
?>