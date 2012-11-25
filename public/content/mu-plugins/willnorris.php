<?php
/*
Plugin Name: willnorris.com
Description: Personal tweaks to WordPress I want to always be loaded on willnorris.com, regardless of what theme I use.
Author: Will Norris
Author URI: http://willnorris.com/
*/

require_once dirname( __FILE__ ) . '/willnorris/referral.php';

/**
 * Instruct search engines not to index the secure version of the site.
 *
 * @see http://www.google.com/support/webmasters/bin/answer.py?hl=en&answer=35302
 */
function willnorris_robots_txt($output) {
  if ( is_ssl() ) {
    $output = "User-agent: *\nDisallow: /\n";
  }

  return $output;
}
add_filter('robots_txt', 'willnorris_robots_txt', 99);


/**
 * Prevent plugins from modifying robots.txt for SSL traffic.
 */
function willnorris_do_robots() {
  if ( is_ssl() ) {
    do_robots();
    exit();
  }
}
add_filter('do_robots', 'willnorris_do_robots', 1);


/**
 * Prevent HTTPS requests from being cached.
 */
function willnorris_prevent_https_cache() {
  if ( is_ssl() ) {
    define('DONOTCACHEPAGE', true);
  }
}
add_action('wp', 'willnorris_prevent_https_cache');


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


// ensure proper redirect status code is returned
add_filter('wp_redirect_status', create_function('$s', 'status_header($s); return $s;'));

// Hum Extensions
add_filter('hum_redirect_base_c', create_function('', 'return "http://code.willnorris.com/";'));
add_filter('hum_redirect_base_w', create_function('', 'return "http://wiki.willnorris.com/";'));
add_filter('amazon_affiliate_id', create_function('', 'return "willnorris-20";'));
