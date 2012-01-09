<?php

/**
 * Log a backtrace for deprecation warnings to the PHP error log.
 */
function willnorris_deprecated_backtrace() {
  if ( WP_DEBUG ) {
    error_log(print_r(debug_backtrace(), true));
  }
}
add_action('deprecated_function_run', 'willnorris_deprecated_backtrace');
add_action('deprecated_file_included', 'willnorris_deprecated_backtrace');
add_action('deprecated_argument_run', 'willnorris_deprecated_backtrace');
add_action('doing_it_wrong_run', 'willnorris_deprecated_backtrace');

