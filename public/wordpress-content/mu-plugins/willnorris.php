<?php
/**
 * Personal tweaks to WordPress I want to always be loaded, regardless of what theme I use.
 */


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


/**
 * Use CURL_CA_BUNDLE environment variable to update libcurl's cacert bundle.
 */
function willnorris_http_api_curl($handle) {
  if ( getenv('CURL_CA_BUNDLE') ) {
    curl_setopt($handle, CURLOPT_CAINFO, getenv('CURL_CA_BUNDLE'));
  }
}
add_action('http_api_curl', 'willnorris_http_api_curl');


// ensure proper redirect status code is returned
add_filter('wp_redirect_status', create_function('$s', 'status_header($s); return $s;'));

// Hum Extensions
add_filter('hum_shortlink_base', create_function('', 'return "http://wjn.me/";'));
add_filter('hum_redirect_base_c', create_function('', 'return "http://code.willnorris.com/";'));
add_filter('hum_redirect_base_w', create_function('', 'return "http://wiki.willnorris.com/";'));
add_filter('amazon_affiliate_id', create_function('', 'return "willnorris-20";'));
