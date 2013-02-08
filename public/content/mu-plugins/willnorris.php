<?php
/*
Plugin Name: willnorris.com
Description: Personal tweaks to WordPress I want to always be loaded on willnorris.com, regardless of what theme I use.
Author: Will Norris
Author URI: http://willnorris.com/
*/

require_once dirname( __FILE__ ) . '/willnorris/referral.php';

/**
 * Shortcode for displaying my age, in years.
 */
function willnorris_my_age() {
  $now = getdate();
  $age = $now['year'] - 1982;

  if ($now['mon'] < 7 && $now['mday'] < 30) {
    $age -= 1;
  }

  return $age;
}
add_shortcode('my_age', 'willnorris_my_age');


function willnorris_no_amps($atts, $content) {
  return preg_replace('/&#038;/', '&', $content);
}
add_shortcode('no_amps', 'willnorris_no_amps');


function willnorris_recent_posts($atts, $content) {
  $posts = '';
  $args = array('posts_per_page' => 20, 'no_found_rows' => true, 'post_status' => 'publish', 'ignore_sticky_posts' => true);
  $count = 0;
  $r = new WP_Query();
  $r->query($args);
  while ($r->have_posts()) {
    $r->the_post();
    if (get_post_format() != '') continue;

    $posts .= sprintf('
      <li>
        <a href="%1$s">%2$s</a>
        <time datetime="%3$s">%4$s</time>
        </li>',
      get_permalink(), get_the_title(), get_the_date('c'), get_the_date());

    if (++$count >= 10) break;
  }
  return '<ul class="post-list">' . $posts . '</ul>';
}
add_shortcode('recent_posts', 'willnorris_recent_posts');

/**
 * Use CURL_CA_BUNDLE environment variable to update libcurl's cacert bundle.
 */
function willnorris_http_api_curl($handle) {
  if ( getenv('CURL_CA_BUNDLE') ) {
    curl_setopt($handle, CURLOPT_CAINFO, getenv('CURL_CA_BUNDLE'));
  }
}
add_action('http_api_curl', 'willnorris_http_api_curl');


function willnorris_cleanup_plugins() {
  // move SmartyPants filter after do_shortcodes
  foreach( array('category_description', 'list_cats', 'comment_author', 'comment_text',
                 'single_post_title', 'the_title', 'the_content', 'the_excerpt') as $filter ) {
      $priority = has_filter($filter, 'SmartyPants');
      if ( $priority !== false ) {
        remove_filter($filter, 'SmartyPants', $priority);
        add_filter($filter, 'SmartyPants', 12);
      }
  }
}
add_action('wp', 'willnorris_cleanup_plugins');


/**
 * Handle 'safe_email' shortcode which converts email address into spambot-safe link.
 */
function willnorris_safe_email($atts, $content=null) {
  $attr = '';
  if ($atts) {
    foreach($atts as $k => $v) {
      if ($v) {
        $attr .= ' ' . $k . '="' . esc_attr($v) .'"';
      }
    }
  }
  return '<a' . $attr . ' href="mailto:' . antispambot($content) . '">' . antispambot($content) . '</a>';
}
add_shortcode('safe_email', 'willnorris_safe_email');


/**
 * Remove standard image sizes so that these sizes are not
 * created during the Media Upload process
 *
 * Tested with WP 3.2.1
 *
 * Hooked to intermediate_image_sizes_advanced filter
 * See wp_generate_attachment_metadata( $attachment_id, $file ) in wp-admin/includes/image.php
 *
 * @param $sizes, array of default and added image sizes
 * @return $sizes, modified array of image sizes
 * @author Ade Walker http://www.studiograsshopper.ch
 */
function sgr_filter_image_sizes( $sizes) {

  unset( $sizes['thumbnail']);
  unset( $sizes['medium']);
  unset( $sizes['large']);

  return $sizes;
}
add_filter('intermediate_image_sizes_advanced', 'sgr_filter_image_sizes');


// ensure proper redirect status code is returned
add_filter('wp_redirect_status', create_function('$s', 'status_header($s); return $s;'));

// Hum Extensions
add_filter('hum_redirect_base_c', create_function('', 'return "http://code.willnorris.com/";'));
add_filter('hum_redirect_base_w', create_function('', 'return "http://wiki.willnorris.com/";'));
add_filter('amazon_affiliate_id', create_function('', 'return "willnorris-20";'));
