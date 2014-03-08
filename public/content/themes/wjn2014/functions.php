<?php
// Start the engine
require_once( get_template_directory() . '/lib/init.php' );

// Child theme (do not remove)
define( 'CHILD_THEME_NAME', 'WJN 2014' );
define( 'CHILD_THEME_URL', 'https://willnorris.com/' );
define( 'CHILD_THEME_VERSION', '0.1.0' );

add_action( 'after_setup_theme', 'wjn2014_theme_setup' );
function wjn2014_theme_setup() {
  // Add HTML5 markup structure
  add_theme_support( 'html5' );

  // Add viewport meta tag for mobile browsers
  add_theme_support( 'genesis-responsive-viewport' );

  //* Add support for structural wraps
  add_theme_support( 'genesis-structural-wraps', array(
    'header',
    'nav',
    'subnav',
    'site-inner',
    'footer-widgets',
    'footer',
  ) );

  add_theme_support( 'post-formats', array(
    'aside',
    'link',
    'gallery',
    'status',
    'quote',
    'image',
    'video'
  ) );

  // Unregister layout settings
  genesis_unregister_layout( 'content-sidebar-sidebar' );
  genesis_unregister_layout( 'sidebar-content-sidebar' );
  genesis_unregister_layout( 'sidebar-sidebar-content' );

  // Unregister secondary sidebar
  unregister_sidebar( 'sidebar-alt' );

  register_nav_menus( array(
    'social' => 'Social Menu',
  ) );

  // Remove Edit link
  add_filter( 'genesis_edit_post_link', '__return_false' );

  // use author link instead of author posts link
  add_filter( 'genesis_post_info', function( $info) {
    return '[post_date] ' . __( 'by', 'genesis' ) . ' [post_author_link] [post_comments] [post_edit]';
  });

  add_filter( 'comment_author_says_text', '__return_empty_string');

  // remove footer content
  remove_action( 'genesis_footer', 'genesis_do_footer' );
}

// add footer widgets
add_action( 'after_setup_theme', function() {
  // Add support for 3-column footer widgets
  add_theme_support( 'genesis-footer-widgets', 3 );
}, 5);


// Return {theme_dir}/css/style.css as the stylesheet_uri.
add_filter( 'stylesheet_uri', function() {
  return get_stylesheet_directory_uri() . '/css/style.css';
});

add_filter( 'genesis_footer_creds_text', function( $text ) {
  return '[footer_copyright] Will Norris.  Powered by [footer_wordpress_link].';
});

add_filter( 'wp_head', function() { ?>
<link rel="apple-touch-icon" sizes="128x128" href="/logo.jpg" />
<link rel="apple-touch-icon-precomposed" sizes="128x128" href="/logo.jpg" />
<meta name="mobile-web-app-capable" content="yes" />
<script>
  // Chrome occasionally has issues applying this properly in CSS
  (function() { document.documentElement.style.fontSize = "62.5%"; })();
</script>
<?php });

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

add_filter( 'genesis_post_meta', function( $meta ) {
  return $meta . ' [post_syndication]';
});
