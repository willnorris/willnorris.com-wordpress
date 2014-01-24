<?php
/* Template Name: Subscribe To Comments */

if ( isset($wp_subscribe_reloaded) ) {
  global $posts;
  $posts = $wp_subscribe_reloaded->subscribe_reloaded_manage();
}

remove_action( 'genesis_entry_header', 'genesis_post_info', 12 );
remove_action( 'genesis_entry_footer', 'genesis_post_meta' );

// Remove site footer elements
remove_action( 'genesis_before_footer', 'genesis_footer_widget_areas' );
remove_action( 'genesis_footer', 'genesis_footer_markup_open', 5 );
remove_action( 'genesis_footer', 'genesis_do_footer' );
remove_action( 'genesis_footer', 'genesis_footer_markup_close', 15 );

genesis();
