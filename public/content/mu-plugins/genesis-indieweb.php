<?php
// Indie Web related customizations to Genesis.  These should probably be 
// eventually moved into the appropriate indie web plugin.

// Display webmentions as normal comments.
add_filter( 'genesis_comments', function() {
  global $wp_query;
  if ( array_key_exists('webmention', $wp_query->comments_by_type) ) {
    $wp_query->comments_by_type['comment'] = array_merge($wp_query->comments_by_type['comment'], $wp_query->comments_by_type['webmention']);
  }
}, 0);

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

