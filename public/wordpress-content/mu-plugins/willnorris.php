<?php
/**
 * Personal tweaks to WordPress I want to always be loaded, regardless of what theme I use.
 */


/**
 * Instruct search engines not to index the secure version of the site.
 */
function willnorris_robots_txt($output) {
  if ( is_ssl() ) {
    $output = "User-agent: *\nDisallow: /\n";
  }

  return $output;
}
add_filter('robots_txt', 'willnorris_robots_txt', 99);


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

