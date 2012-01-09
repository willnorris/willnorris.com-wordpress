<?php

/**
 * Add license to site head.
 */
function willnorris_license_head() {
  if ( defined('CC_LICENSE') ) {
    echo '<link rel="license" type="text/html" href="' . CC_LICENSE . '" />' . "\n";
  }
}
add_action('wp_head', 'willnorris_license_head');


/**
 * Add the Creative Commons XML namespace.
 */
function willnorris_license_xmlns() {
  if ( defined('CC_LICENSE') ) {
    echo 'xmlns:creativeCommons="http://backend.userland.com/creativeCommonsRssModule"' . "\n";
  }
}
add_action('rdf_ns', 'willnorris_license_xmlns');
add_action('rss2_ns', 'willnorris_license_xmlns');
add_action('atom_ns', 'willnorris_license_xmlns');


/**
 * Add the Creative Commons XML element.
 */
function willnorris_license_xmlfeed() {
  if ( defined('CC_LICENSE') ) {
    echo '<creativeCommons:license>' . CC_LICENSE . '</creativeCommons:license>' . "\n";
  }
}
add_action('rdf_header', 'willnorris_license_xmlfeed');
add_action('rss2_head', 'willnorris_license_xmlfeed');
add_action('atom_head', 'willnorris_license_xmlfeed');
