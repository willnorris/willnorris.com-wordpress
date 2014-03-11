<?php
/*
Plugin Name: willnorris.com
Description: Personal tweaks to WordPress I want to always be loaded on willnorris.com, regardless of what theme I use.
Author: Will Norris
Author URI: https://willnorris.com/
*/

require_once dirname( __FILE__ ) . '/willnorris/referral.php';
require_once dirname( __FILE__ ) . '/willnorris/genesis.php';

// Cleanup filters that shouldn't run.
add_action( 'init', function() {
  // remove 'capital_P_dangit'
  foreach ( array( 'the_content', 'the_title', 'comment_text' ) as $filter ) {
    $priority = has_filter($filter, 'capital_P_dangit');
    if ( $priority !== false ) {
      remove_filter( $filter, 'capital_P_dangit', $priority );
    }
  }

  // disable WordPress sanitization to allow more than just $allowedtags from /wp-includes/kses.php
  remove_filter('pre_user_description', 'wp_filter_kses');
  // add sanitization for WordPress posts
  add_filter( 'pre_user_description', 'wp_filter_post_kses');
});

// Shortcode for displaying my age, in years.
add_shortcode( 'my_age', function() {
  $now = getdate();
  $age = $now['year'] - 1982;

  if ($now['mon'] < 7 && $now['mday'] < 30) {
    $age -= 1;
  }

  return $age;
});

// Handle 'safe_email' shortcode which converts email address into spambot-safe link.
add_shortcode( 'safe_email', function ( $atts, $content=null ) {
  $attr = '';
  if ($atts) {
    foreach($atts as $k => $v) {
      if ($v) {
        $attr .= ' ' . $k . '="' . esc_attr($v) .'"';
      }
    }
  }
  return '<a' . $attr . ' href="mailto:' . antispambot($content) . '">' . antispambot($content) . '</a>';
});

add_shortcode( 'no_amps', function( $atts, $content ) {
  return preg_replace('/&#038;/', '&', $content);
});

// Use CURL_CA_BUNDLE environment variable to update libcurl's cacert bundle.
add_action( 'http_api_curl', function( $handle ) {
  if ( getenv('CURL_CA_BUNDLE') ) {
    curl_setopt($handle, CURLOPT_CAINFO, getenv('CURL_CA_BUNDLE'));
  }
});

// cleanup plugins
add_action( 'wp', function() {
  // move SmartyPants filter after do_shortcodes
  foreach( array('category_description', 'list_cats', 'comment_author', 'comment_text',
                 'single_post_title', 'the_title', 'the_content', 'the_excerpt') as $filter ) {
    $priority = has_filter($filter, 'SmartyPants');
    if ( $priority !== false ) {
      remove_filter($filter, 'SmartyPants', $priority);
      add_filter($filter, 'SmartyPants', 12);
    }
  }
});

// Don't crawl plugins or themes directories.
add_filter( 'robots_txt', function( $output ) {
  $output = "User-agent: *\n";

  $disallow = array(
      site_url(),                // wordpress system directory
      plugins_url(),             // plugins
      content_url('mu-plugins'), // must-use plugins
      content_url('themes'),     // themes
      content_url('cache'),      // w3 total cache
  );
  foreach($disallow as $url) {
    $output .= 'Disallow: ' . trailingslashit(parse_url($url, PHP_URL_PATH)) . "\n";
  }

  return $output;
});

// ensure proper redirect status code is returned
add_filter( 'wp_redirect_status', function( $s ) {
  status_header($s);
  return $s;
});

add_action( 'analytics_tracking_js', function() {
  echo "  _gaq.push(['_setAllowAnchor', true]);\n";
});

// Hum Extensions
add_filter( 'hum_redirect_base_c', function() { return "http://code.willnorris.com/"; });
add_filter( 'hum_redirect_base_w', function() { return "http://wiki.willnorris.com/"; });
add_filter( 'hum_legacy_id', function( $post_id, $path ) {
  if ( strpos($path, '/') !== false ) {
    list($subtype, $id) = explode('/', $path, 2);
    if ( $subtype == 'p' ) {
      $post_id = $id;
    }
  }
  return $post_id;
}, 10, 2);

/**
 * Handle shortlinks to Google+ content.  URLs that begin with the path segment '+' or 'plus' and
 * which have not already been handled by WordPress are redirected to Google+.  If the remaning
 * path looks like a Google+ post ID, construct a permalink URL.  Otherwise, just append the
 * remaining path.
 */
add_filter( 'template_redirect', function() {
  if ( is_404() ) {
    global $wp;

    if ( strpos($wp->request, '/') !== false ) {
      list($type, $id) = @explode('/', $wp->request, 2);
    } else {
      $type = $wp->request;
      $id = null;
    }

    if ( $type == '+' || $type == 'plus' ) {
      $url = 'https://plus.google.com/' . GOOGLE_PLUS_ID;
      if ( $id ) {
        if ( strpos('/', $id) === false && preg_match('/[0-9A-Z]/', $id) ) {
          $url .= '/posts/' . $id;
        } else {
          $url .= '/' . $id;
        }
      }
      wp_redirect($url, 301);
      exit;
    }
  }
}, 0);

/**
 * Remove standard image sizes so that these sizes are not
 * created during the Media Upload process
 *
 * Tested with WP 3.2.1
 *
 * Hooked to intermediate_image_sizes_advanced filter
 * See wp_generate_attachment_metadata( $attachment_id, $file ) in wp-admin/includes/image.php
 *
 * @param $sizes, array of default and added image sizes
 * @return $sizes, modified array of image sizes
 * @author Ade Walker http://www.studiograsshopper.ch
 */
add_filter( 'intermediate_image_sizes_advanced', function( $sizes ) {
  unset( $sizes['thumbnail'] );
  unset( $sizes['medium'] );
  unset( $sizes['large'] );

  return $sizes;
});


// WebFinger hooks

// Since my WordPress username is "willnorris", webfinger will expect lookups of
// "acct:willnorris@willnorris.com".  Also allow queries for "acct:will@willnorris.com".
add_filter( 'webfinger_user_query', function( $args, $uri, $scheme ) {
  if ($uri == "acct:will@willnorris.com") {
    $args = array(
      'search' => 'willnorris',
      'search_columns' => array('user_login'),
      'meta_compare' => '=',
    );
  }

  return $args;
}, 10, 3);

// Use acct:will@willnorris.com as my webfinger subject.
add_filter( 'webfinger_user_resource', function( $url, $user ) {
  if ($user->user_login == 'willnorris') {
    $url = 'acct:will@willnorris.com';
  }
  return $url;
}, 10, 2);

// Customize webfinger resource URLs.
add_filter( 'webfinger_user_resources', function( $resources, $user ) {
  $skip = array('https://willnorris.com/author/willnorris', 'acct:will@willnorris.com');
  $resources =  array_values(array_diff($resources, $skip));
  $resources[] = 'https://willnorris.com/';
  return $resources;
}, 10, 2);

// Customize webfinger data.
add_filter( 'webfinger_data', function( $webfinger, $resoure ) {
  $links = array();
  foreach($webfinger['links'] as $link) {
    if ('https://willnorris.com/author/willnorris' != $link['href']) {
      $links[] = $link;
    }
  }
  $webfinger['links'] = $links;
  return $webfinger;
}, 10, 2);


// don't require comment quiz for webmentions.
add_filter( 'quiz_required', function( $required, $commentdata ) {
  return ( 'webmention' == $commentdata['comment_type'] ) ? false : $required;
}, 10, 2);

// Disable trackbacks globally (but leave pingbacks alone)
add_action( 'pings_open', function( $open ) {
  return ( '1' == get_query_var('tb') ) ? false : $open;
});

// Always approve webmentions.  I wonder how long it will take until this
// becomes a problem.  At that point, I might switch to auto-approve likes and
// reposts, but hold replies for moderation.
add_filter( 'pre_comment_approved', function( $approved , $commentdata ) {
  return ( 'webmention' == $commentdata['comment_type'] ) ? true : $approved;
}, 99, 2);

// Exclude some plugins from being activated on development workstation.
// TODO(willnorris): switch WP_HOME check to some new constant like DEV_SERVER
add_filter( 'option_active_plugins', function( $options ) {
  if ( 'https://willnorris.com' != WP_HOME ) {
    $exclude = array(
      'pushover-notifications/pushover-notifications.php',
      'w3-total-cache/w3-total-cache.php',
    );
    $options = array_diff($options, $exclude);
  }
  return $options;
});
