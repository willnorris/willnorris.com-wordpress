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

