<?php
// track website visits from the QR code on the back of my business card


/**
 * Accept 'ref' query variable.
 */
function willnorris_referral_query_vars( $vars ) {
  $vars[] = 'ref';
  return $vars;
}
add_action('query_vars', 'willnorris_referral_query_vars');


/**
 * Parse requests with 'ref' variable.
 */
function willnorris_referral_parse_request( $wp ) {
  if ( array_key_exists( 'ref', $wp->query_vars ) ) {
    $ref_code = $wp->query_vars['ref'];
    do_action("willnorris_referral_{$ref_code}");
  }
}
add_action('parse_request', 'willnorris_referral_parse_request');


/**
 * Redirect requests with ref=card to include Google Analytics tracking data.
 */
function willnorris_qrcode_redirect() {
  $args = array(
    'utm_source' => 'card',
    'utm_medium' => 'qr',
    'utm_campagin' => 'willnorris',
    'ref' => null
  );

  $url = add_query_arg($args);
  wp_redirect($url);
  exit;
}
add_action('willnorris_referral_card', 'willnorris_qrcode_redirect');

