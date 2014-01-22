<?php
/*
Plugin Name: willnorris.com
Description: Personal tweaks to WordPress I want to always be loaded on willnorris.com, regardless of what theme I use.
Author: Will Norris
Author URI: http://willnorris.com/
*/

require_once dirname( __FILE__ ) . '/willnorris/referral.php';

class WJN_Personal {

  public function __construct() {
    add_action('init', array( $this, 'init' ));
    add_action('init', array( $this, 'cleanup_wp' ));
  }

  /**
   * Initialize the plugin, registering WordPess hooks.
   */
  public function init() {
    // shortcodes
    add_shortcode('my_age', array($this, 'my_age'));
    add_shortcode('safe_email', 'willnorris_safe_email');
    add_shortcode('no_amps', array($this, 'no_amps'));
    add_shortcode('recent_posts', array($this, 'recent_posts'));

    add_action('http_api_curl', array($this, 'http_api_curl'));
    add_action('wp', array($this, 'cleanup_plugins'));
    add_filter('intermediate_image_sizes_advanced', array($this, 'filter_image_sizes'));

    add_filter('robots_txt', array($this, 'robots_txt'));

    // ensure proper redirect status code is returned
    add_filter('wp_redirect_status', create_function('$s', 'status_header($s); return $s;'));

    add_action('analytics_tracking_js', array($this, 'analytics_tracking_js'));

    // Hum Extensions
    //add_filter('hum_redirect', array($this, 'hum_google_analytics'), 99, 3);
    //add_filter('hum_legacy_redirect', array($this, '_add_google_analytics'), 99);
    add_filter('hum_redirect_base_c', create_function('', 'return "http://code.willnorris.com/";'));
    add_filter('hum_redirect_base_w', create_function('', 'return "http://wiki.willnorris.com/";'));
    add_filter('amazon_affiliate_id', create_function('', 'return "willnorris-20";'));
    add_filter('hum_legacy_id', array($this, 'legacy_shortlinks'), 10, 2);

    add_filter('template_redirect', array($this, 'googleplus_shortlinks'), 0);

    add_filter('webfinger_user_query', array($this, 'webfinger_user_query'), 10, 3);
    add_filter('webfinger_user_resource', array($this, 'webfinger_user_subject'), 10, 2);
    add_filter('webfinger_user_resources', array($this, 'webfinger_user_resources'), 10, 2);
    add_filter('webfinger_data', array($this, 'webfinger_data'), 10, 2);
  }

  /**
   * Cleanup filters that shouldn't run.
   */
  function cleanup_wp() {
    // remove 'capital_P_dangit'
    foreach ( array( 'the_content', 'the_title', 'comment_text' ) as $filter ) {
      $priority = has_filter($filter, 'capital_P_dangit');
      if ( $priority !== false ) {
        remove_filter( $filter, 'capital_P_dangit', $priority );
      }
    }
  }

  /**
   * Shortcode for displaying my age, in years.
   */
  function my_age() {
    $now = getdate();
    $age = $now['year'] - 1982;

    if ($now['mon'] < 7 && $now['mday'] < 30) {
      $age -= 1;
    }

    return $age;
  }

  /**
   * Handle 'safe_email' shortcode which converts email address into spambot-safe link.
   */
  function safe_email($atts, $content=null) {
    $attr = '';
    if ($atts) {
      foreach($atts as $k => $v) {
        if ($v) {
          $attr .= ' ' . $k . '="' . esc_attr($v) .'"';
        }
      }
    }
    return '<a' . $attr . ' href="mailto:' . antispambot($content) . '">' . antispambot($content) . '</a>';
  }

  function no_amps($atts, $content) {
    return preg_replace('/&#038;/', '&', $content);
  }

  function recent_posts($atts, $content) {
    $posts = '';
    $args = array('posts_per_page' => 20, 'no_found_rows' => true, 'post_status' => 'publish', 'ignore_sticky_posts' => true);
    $count = 0;
    $r = new WP_Query();
    $r->query($args);
    while ($r->have_posts()) {
      $r->the_post();
      if (get_post_format() != '') continue;

      $posts .= sprintf('
        <li>
          <a href="%1$s">%2$s</a>
          <time datetime="%3$s">%4$s</time>
          </li>',
        get_permalink(), get_the_title(), get_the_date('c'), get_the_date());

      if (++$count >= 10) break;
    }
    return '<ul class="post-list">' . $posts . '</ul>';
  }

  /**
   * Use CURL_CA_BUNDLE environment variable to update libcurl's cacert bundle.
   */
  function http_api_curl($handle) {
    if ( getenv('CURL_CA_BUNDLE') ) {
      curl_setopt($handle, CURLOPT_CAINFO, getenv('CURL_CA_BUNDLE'));
    }
  }

  function cleanup_plugins() {
    // move SmartyPants filter after do_shortcodes
    foreach( array('category_description', 'list_cats', 'comment_author', 'comment_text',
                   'single_post_title', 'the_title', 'the_content', 'the_excerpt') as $filter ) {
        $priority = has_filter($filter, 'SmartyPants');
        if ( $priority !== false ) {
          remove_filter($filter, 'SmartyPants', $priority);
          add_filter($filter, 'SmartyPants', 12);
        }
    }
  }

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
  function filter_image_sizes( $sizes) {
    unset( $sizes['thumbnail']);
    unset( $sizes['medium']);
    unset( $sizes['large']);

    return $sizes;
  }

  function legacy_shortlinks($post_id, $path) {
    if ( strpos($path, '/') !== false ) {
      list($subtype, $id) = explode('/', $path, 2);
      if ( $subtype == 'p' ) {
        $post_id = $id;
      }
    }
    return $post_id;
  }

  /**
   * Handle shortlinks to Google+ content.  URLs that begin with the path segment '+' or 'plus' and
   * which have not already been handled by WordPress are redirected to Google+.  If the remaning
   * path looks like a Google+ post ID, construct a permalink URL.  Otherwise, just append the
   * remaining path.
   */
  function googleplus_shortlinks() {
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
  }

  /** Don't crawl plugins or themes directories. */
  function robots_txt($output) {
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
  }

  function analytics_tracking_js() {
    echo "  _gaq.push(['_setAllowAnchor', true]);\n";
  }

  /**
   * Append Google Analytics tracking codes for shortlink redirects to local content.
   */
  function hum_google_analytics($url, $type, $id) {
    $local_types = Hum::local_types();
    if ( $url && in_array($type, $local_types) && GOOGLE_ANALYTICS_ID ) {
      $url = $this->_add_google_analytics($url, "$type/$id");
    }
    return $url;
  }

  function _add_google_analytics($url, $id=null) {
    if ( $id ) {
      $source = 'hum';
    } else {
      global $wp;
      $id = $wp->request;
      $source = 'hum_legacy';
    }

    $ga_codes = array(
      'utm_source' => $source,
      'utm_medium' => $id,
      'utm_campaign' => 'hum'
    );

    $query = build_query($ga_codes);
    return $url . '#' . $query;
  }

  function webfinger_user_query($args, $uri, $scheme) {
    if ($uri == "acct:will@willnorris.com") {
      $args = array(
        'search' => 'willnorris',
        'search_columns' => array('user_login'),
        'meta_compare' => '=',
      );
    }

    return $args;
  }

  function webfinger_user_subject($url, $user) {
    if ($user->user_login == 'willnorris') {
      $url = 'acct:will@willnorris.com';
    }
    return $url;
  }

  function webfinger_user_resources($resources, $user) {
    $skip = array('https://willnorris.com/author/willnorris', 'acct:will@willnorris.com');
    $resources =  array_values(array_diff($resources, $skip));
    $resources[] = 'https://willnorris.com/';
    return $resources;
  }

  function webfinger_data($webfinger, $resoure) {
    $links = array();
    foreach($webfinger['links'] as $link) {
      if ($link['href'] != 'https://willnorris.com/author/willnorris') {
        $links[] = $link;
      }
    }
    $webfinger['links'] = $links;
    return $webfinger;
  }
}

new WJN_Personal;

