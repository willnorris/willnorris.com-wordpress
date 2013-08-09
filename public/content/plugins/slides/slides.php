<?php
/*
 Plugin Name: Slides
 Plugin URI: http://github.com/willnorris/wordpress-slides
 Description: Slides
 Author: Will Norris
 Author URI: http://willnorris.com/
 Version: 1.0-trunk
 License: Apache 2 (http://www.apache.org/licenses/LICENSE-2.0.html)
 Text Domain: slides
*/

require_once dirname(__FILE__) . '/admin.php';

/**
 * Register the 'slide' post type.
 */
function slides_register_post_type() {

  // setup custom post type
  $post_type_args = array(
    'labels' => array(
      'name' => __('Slides', 'slides'),
      'singular_name' => __('Slides', 'slides'),
      'all_items' => __('All Slides', 'slides'),
      'add_new_item' => __('Add New Slides', 'slides'),
      'edit_item' => __('Edit Slides', 'slides'),
      'new_item' => __('New Slides', 'slides'),
      'view_item' => __('View Slides', 'slides'),
      'search_items' => __('Search Slides', 'slides'),
      'not_found' => __('No slides found', 'slides'),
      'not_found_in_trash' => __('No slides found in Trash', 'slides'),
    ),
    'public' => true,
    'show_ui' => true,
    'capability_type' => 'post',
    'hierarchical' => false,
    'rewrite' => array(
      'slug' => get_slides_permalink_base(),
      'with_front' => true
    ),
    'has_archive' => true,
    'supports' => array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'custom-fields'),
  );
  register_post_type('slides', $post_type_args);


  // allow slides to have pings and enclosures
  add_action('publish_slides', '_publish_post_hook', 5, 1);
}
add_action('init', 'slides_register_post_type');


/**
 * Perform any post-activation tasks for the plugin such as flushing rewrite
 * rules so that permalinks will work.
 */
function slides_activation_hook() {
  slides_register_post_type();
  flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'slides_activation_hook');


/**
 * Get the URL base for slides permalinks.
 */
function get_slides_permalink_base() {
  $base = get_option('slides_base');
  if ( empty($base) ) {
    $base = 'slides';
  }
  return $base;
}


/**
 * Is the query for specific slides?
 *
 * If the $slides parameter is specified, this function will additionally
 * check if the query is for one of the slides specified.
 *
 * @param mixed $slides Slides ID, title, slug, or array of Slides IDs, titles, and slugs.
 * @return bool
 */
function is_slides( $slides = '' ) {
  return is_singular( 'slides' ) && is_single( $slides );
}


/**
 * Get the URL for the specified slides.
 *
 * @param int|object $post Slides ID or object.
 * @return string URL for the slides, if one exists
 */
function get_slides_url( $slides = '' ) {
  $post = get_post($slides);
  if ( $post ) {
    return get_post_meta($post->ID, '_slides_url', true);
  }
}

function slides_wp_init() {
  if ( is_slides() ) {
    wp_enqueue_script('slides', plugins_url('slides/slides.js'), array(), '', true);
    wp_enqueue_style('slides', plugins_url('slides/slides.css'), array());
    add_filter('the_content', 'slides_content');
  }
}
add_filter('wp', 'slides_wp_init');

function slides_content( $content ) {
  $url = get_slides_url();
  if ( $url ) {
    $content .= '
    <iframe id="slides" src="' . $url . '" width="1024" height="768" style="-webkit-transform: scale(0.5);"></iframe>
    <div id="notes">Loading…</div>';
  }
  return $content;
}

/**
 * Add appropriate metadata if the opengraph plugin is installed.
 *
 * @see http://wordpress.org/extend/plugins/opengraph/
 */
function slides_opengraph_metadata( $metadata ) {
  if ( is_slides() ) {
    $metadata['og:type'] = 'article';
  }

  return $metadata;
}
add_filter('opengraph_metadata', 'slides_opengraph_metadata');


/**
 * The base type to use for slidedeck URLs shortened by the hum plugin.
 *
 * @see http://wordpress.org/extend/plugins/hum/
 */
function slides_hum_base_type() {
  return apply_filters('slides_hum_base_type', 's');
}


/**
 * Add the slides base type as a local type handled by hum.
 *
 * @see http://wordpress.org/extend/plugins/hum/
 */
function slides_hum_local_types($types) {
  $types[] = slides_hum_base_type();
  return $types;
}
add_filter('hum_local_types', 'slides_hum_local_types');


/**
 * Use the slides base type for shortened slides URLs.
 *
 * @see http://wordpress.org/extend/plugins/hum/
 */
function slides_hum_type_prefix($prefix, $post) {
  if ( get_post_type($post) == 'slides' ) {
    $prefix = slides_hum_base_type();
  }
  return $prefix;
}
add_filter('hum_type_prefix', 'slides_hum_type_prefix', 10, 2);


/**
 * Exclude this plugin from update checks, since it's not in the WordPress plugin directory.
 */
function slides_exclude_update_check( $r, $url ) {
  if ( 0 !== strpos( $url, 'http://api.wordpress.org/plugins/update-check' ) ) {
    return $r; // Not a plugin update request. Bail immediately.
  }
  $plugins = unserialize( $r['body']['plugins'] );
  unset( $plugins->plugins[ plugin_basename( __FILE__ ) ] );
  unset( $plugins->active[ array_search( plugin_basename( __FILE__ ), $plugins->active ) ] );
  $r['body']['plugins'] = serialize( $plugins );
  return $r;
}
add_filter( 'http_request_args', 'slides_exclude_update_check', 5, 2 );
