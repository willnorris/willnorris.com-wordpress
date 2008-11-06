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
	echo '
	<script type="text/javascript"> jQuery(function(){ jQuery(".hentry").imagefit(); }); </script>';
}

/**
* Adds in the necessary JavaScript files
**/
function wpimagefit_add_scripts() {
	wp_enqueue_script('jquery.imagefit', plugins_url('wp-imagefit') . '/js/jquery.imagefit.min.js', array('jquery'), '0.2');
}


add_action('init', 'wpimagefit_add_scripts');
add_action('wp_head', 'wp_imagefit_js');
?>
