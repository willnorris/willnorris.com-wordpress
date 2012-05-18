<?php
/*
Plugin Name: OpenID Support
Description: OpenID Support Table
Author: Will Norris
Author URI: http://willnorris.com/
*/


/**
 * Add openid-support shortcode.
 */
function openid_support_table($attrs, $content) {
  $table_file = dirname(__FILE__) . '/openid-support/table.html';

  $support_table = '
    <div id="openid-support">
      ' . file_get_contents( $table_file ) . '
    </div>

    <p id="last-modified">Table Last Updated: ' . date('r', filemtime($table_file) ) . '</p>
    ';

  return $support_table;

}
add_shortcode('openid_support_table', 'openid_support_table');

