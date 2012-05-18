<?php
/*
Plugin Name: Google+ Tools
Description: Google+ tools, such as +1 button and rel="author" links
Author: Will Norris
Author URI: http://willnorris.com/
*/


/**
 * Add rel=author link to head
 */
function google_plus_rel_author() {
  if ( defined('GOOGLE_PLUS_ID') ) {
    echo '<link rel="author me" href="https://plus.google.com/' . GOOGLE_PLUS_ID . '" />' . "\n";
  }
}
add_action('wp_head', 'google_plus_rel_author');


/**
 * Return a +1 button for the specified post.
 */
function plusone_button($post) {
  return '<div class="plusone-button"><g:plusone size="small" href="' . get_permalink($post) . '"></g:plusone></div>';
}


/**
 * Add +1 button script.
 */
function plusone_script() {
?>
  <script>
    (function() {
      jQuery('<script>', {async:true, src:'https://apis.google.com/js/plusone.js'}).prependTo('script:first');
    })();
  </script>
<?php
}
add_action('wp_footer', 'plusone_script');

