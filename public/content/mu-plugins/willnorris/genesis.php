<?php
// Customizations to the Genesis framework that should be active regardless of 
// which Genesis child theme I use.

// Add microformats to links in social menu.
add_filter( 'nav_menu_link_attributes', function( $atts, $item, $args ) {
  if( $args->theme_location == 'social' ) {
    if ( !array_key_exists('class', $atts) ) {
      $atts['class'] = '';
    }
    $atts['class'] .= ' u-url url';
  }
  return $atts;
}, 10, 3);

add_shortcode( 'post_syndication', function( $atts ) {
  global $post;

  $defaults = array(
    'after'  => '',
    'before' => __( 'Also view on: ', 'wjn2014' ),
    'sep'    => ', ',
  );
  $atts = shortcode_atts( $defaults, $atts, 'post_syndication' );

  $syns = array();
  $syndications = get_post_meta($post->ID, 'syndication');
  foreach ( $syndications as $url ) {
    $name = parse_url($url, PHP_URL_HOST);
    switch ( strtolower($name) ) {
      case 'plus.google.com':
        $name = 'Google+'; break;
      case 'twitter.com':
      case 'www.twitter.com':
        $name = 'Twitter'; break;
      case 'facebook.com':
      case 'www.facebook.com':
        $name = 'Facebook'; break;
    }
    $syns[] = '<a rel="syndication" class="u-syndication" href="' . $url . '">' . $name . '</a>';
  }
  if ( $syns ) {
    $syn = $atts['before'] . join($atts['sep'], $syns) . $atts['after'];
    return sprintf( '<span %s>', genesis_attr( 'entry-syndication' ) ) . $syn . '</span>';
  }
});

