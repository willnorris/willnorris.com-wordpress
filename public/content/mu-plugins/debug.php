<?php
/*
Plugin Name: Debug
Description: Provide extra debugging information.
Author: Will Norris
Author URI: http://willnorris.com/
*/


/**
 * Log a backtrace for deprecation warnings to the PHP error log.
 */
function debug_deprecated_backtrace() {
  if ( WP_DEBUG ) {
    error_log(print_r(debug_backtrace(), true));
  }
}
add_action('deprecated_function_run', 'debug_deprecated_backtrace');
add_action('deprecated_file_included', 'debug_deprecated_backtrace');
add_action('deprecated_argument_run', 'debug_deprecated_backtrace');
add_action('doing_it_wrong_run', 'debug_deprecated_backtrace');


/**
 * Accept debug query variables.
 */
function debug_query_vars( $vars ) {
  $vars[] = 'phpinfo';
  return $vars;
}
add_action('query_vars', 'debug_query_vars');


/**
 * Display PHP info when requested.  Only displayed to logged in admins.
 */
function debug_phpinfo_parse_request( $wp ) {
  if ( array_key_exists( 'phpinfo', $wp->query_vars ) ) {
    if ( is_user_logged_in() && current_user_can('manage_options') ) {
      phpinfo();
      exit;
    }
  }
}
add_action('parse_request', 'debug_phpinfo_parse_request');


/**
 * Add rewrite rules for debug features.
 */
function debug_rewrite_rules( $wp_rewrite ) {
  $debug_rules = array(
    '.info' => 'index.php?phpinfo=true',
  );

  $wp_rewrite->rules = $debug_rules + $wp_rewrite->rules;
}
add_action('generate_rewrite_rules', 'debug_rewrite_rules');

