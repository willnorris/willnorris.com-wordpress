<?php
// track website visits from the QR code on the back of my business card


// Accept 'ref' query variable.
add_action( 'query_vars', function( $vars ) {
  $vars[] = 'ref';
  return $vars;
});


// Parse requests with 'ref' variable.
add_action( 'parse_request', function( $wp ) {
  if ( array_key_exists( 'ref', $wp->query_vars ) ) {
    $ref_code = $wp->query_vars['ref'];
    do_action("willnorris_referral_{$ref_code}");
  }
});


// Redirect requests with ref=card to include Google Analytics tracking data.
add_action('willnorris_referral_card', function() {
  $args = array(
    'utm_source' => 'card',
    'utm_medium' => 'qr',
    'utm_campagin' => 'willnorris',
    'ref' => null
  );

  $url = add_query_arg($args);
  wp_redirect($url);
  exit;
});
