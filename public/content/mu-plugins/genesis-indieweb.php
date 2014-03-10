<?php
// Indie Web related customizations to Genesis.  These should probably be
// eventually moved into the appropriate indie web plugin.

// Display webmentions as normal comments.
add_filter( 'genesis_comments', function() {
  global $wp_query;
  if ( array_key_exists('webmention', $wp_query->comments_by_type) ) {
    foreach ( $wp_query->comments_by_type['webmention'] as $webmention ) {
      $wm_type = get_comment_meta($webmention->comment_ID, 'semantic_linkbacks_type', true);
      if ( 'reply' == $wm_type ) {
        $wp_query->comments_by_type['comment'][] = $webmention;
      }
    }
  }
}, 0);

function webmention_facepile( $type ) {
  global $wp_query;
  if ( array_key_exists('webmention', $wp_query->comments_by_type) ) {
    $webmentions = array();

    foreach ( $wp_query->comments_by_type['webmention'] as $webmention ) {
      $wm_type = get_comment_meta($webmention->comment_ID, 'semantic_linkbacks_type', true);
      if ( $type == $wm_type ) {
        $webmentions[] = $webmention;
      }
    }

    $defaults = array(
      'type'        => 'webmention',
      'avatar_size' => 30,
      'format'      => 'html5', //* Not necessary, but a good example
      'callback'    => 'webmention_facepile_comment_callback',
    );

    $args = apply_filters( 'genesis_comment_list_args', $defaults );
    wp_list_comments( $args, $webmentions );
  }
}

function webmention_facepile_comment_callback( $comment, array $args, $depth ) {
  $GLOBALS['comment'] = $comment;

  $author = get_comment_author();
  $url    = get_comment_author_url();
  $face   = get_avatar( $comment, $args['avatar_size'], '', $author );

  if ( ! empty( $url ) && 'http://' !== $url ) {
    $face = sprintf( '<a href="%s" rel="external nofollow" title="%s">%s</a>', esc_url( $url ), $author, $face );
  }

  echo '<li id="comment-' . get_comment_ID() . '">' . $face;
}

add_action( 'genesis_comments', function() {
  global $wp_query;

  if ( array_key_exists('webmention', $wp_query->comments_by_type) && sizeof($wp_query->comments_by_type['webmention'])>0 ) {
    echo '<h3>Likes and Reposts</h3>';

    genesis_markup( array(
      'html5'   => '<div %s>',
      'xhtml'   => '<div id="comments">',
      'context' => 'entry-likes',
    ) );
    echo '<ul class="facepile">';
    webmention_facepile('like');
    echo '</ul>';
    echo '</div>';

    genesis_markup( array(
      'html5'   => '<div %s>',
      'xhtml'   => '<div id="comments">',
      'context' => 'entry-reposts',
    ) );
    echo '<ul class="facepile">';
    webmention_facepile('repost');
    echo '</ul>';
    echo '</div>';
  }
});

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

/**
 * Adds microformats v2 support to the post author image.
 * This is quite ugly.
 */
function uf2_genesis_attr_entry_author_image( $output, $atts ) {
  $email_hash = md5( strtolower( trim( get_the_author_meta( 'email' ) ) ) );
  $url = 'https://secure.gravatar.com/avatar/' . $email_hash . '?s=128';
  $data = '<data class="p-photo" value="' . $url . '" />';

  $pos = strrpos( $output, '</' );
  return substr( $output, 0, $pos ) . $data . substr( $output, $pos );
}
add_filter( 'genesis_post_author_link_shortcode', 'uf2_genesis_attr_entry_author_image', 20, 2 );

