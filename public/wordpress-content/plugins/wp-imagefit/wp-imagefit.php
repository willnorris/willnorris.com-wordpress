<?php
/*
Plugin Name: WP-Imagefit
Plugin URI: http://factoryjoe.com/projects/wp-imagefit
Description: WP-Imagefit is a plugin that extends jQuery to proportionally resize images posts to fit the width of the containin column. Original jQuery <a href="http://www.ollicle.com/eg/jquery/imagefit/">Imagefit plugin</a> written by <a href="http://www.ollicle.com/">Oliver Boermans</a>.
Author: FactoryCity
Author URI: http://factoryjoe.com/
Version: 0.4

This plugin is released under GPL:
http://www.opensource.org/licenses/gpl-license.php
*/


// todo: add support for more classnames (i.e. ".storycontent", ".entrytext")
function wp_imagefit_js() {
  echo '
  <script type="text/javascript"> 
    jQuery(window).load(function(){ 
      jQuery(".hentry").imagefit(); 
    }); 
  </script>';
}

/**
* Adds in the necessary JavaScript files
**/
function wp_imagefit_add_scripts() {
  if (function_exists('plugins_url')) {
    $js = plugins_url('/wp-imagefit/js/jquery.imagefit.min.js');
  } else {
    /* pre-2.6 compatibility */
    if ( ! defined( 'WP_CONTENT_URL' ) )
        define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
    if ( ! defined( 'WP_PLUGIN_URL' ) )
        define( 'WP_PLUGIN_URL', WP_CONTENT_URL . '/plugins' );

    $js = WP_PLUGIN_URL . '/wp-imagefit/js/jquery.imagefit.min.js';
  }

  wp_enqueue_script('jquery.imagefit', $js, array('jquery'));
}


add_action('init', 'wp_imagefit_add_scripts');
add_action('wp_head', 'wp_imagefit_js');
?>
