<?php


/**
 * Theme Setup.
 */
function willnorris_setup() {
  add_theme_support( 'post-formats', array( 'aside', 'link', 'status' ) );
}
add_action('after_setup_theme', 'willnorris_setup');


/**
 *
 */
function willnorris_wp( $wp ) {
  wp_enqueue_script('jquery');
  //wp_enqueue_script('modernizr');
}
add_action('wp', 'willnorris_wp');


/**
 * Cleanup actions registered by plugins.
 */
function willnorris_wp_cleanup( $wp ) {
  wp_deregister_style('openid');
}
add_action('wp', 'willnorris_wp_cleanup', 999);


/**
 * Return {theme_dir}/css/screen.css as the stylesheet_uri.
 */
function willnorris_stylesheet_uri( $stylesheet_uri ) {
  $stylesheet_dir_uri = get_stylesheet_directory_uri();
  $stylesheet_uri = $stylesheet_dir_uri . '/css/screen.css';
  return $stylesheet_uri;
}
add_filter('stylesheet_uri', 'willnorris_stylesheet_uri');


/**
 * Additional arguments to use when building the main menu.  
 */
function willnorris_page_menu_args($args) {
  $args['depth'] = 1;
  return $args;
}
add_filter('wp_page_menu_args', 'willnorris_page_menu_args', 11);


/**
 * Exclude front page from menu, since the <h1> links there already.
 */
function willnorris_list_pages_exludes($excludes) {
  if (get_option('show_on_front') == 'page') {
    $excludes[] = get_option('page_on_front');
  }

  $openid_support = get_page_by_path('openid-support');
  if ( $openid_support ) {
    $excludes[] = $openid_support->ID;
  }

  return $excludes;
}
add_filter('wp_list_pages_excludes', 'willnorris_list_pages_exludes');


/**
 * Add openid-support shortcode.
 */
function willnorris_openid_support_table($attrs, $content) {
  $table_file = dirname(__FILE__) . '/openid-support-table.html';

  $support_table = '
    <div id="openid-support">
      ' . file_get_contents( $table_file ) . '
    </div>

    <p id="last-modified">Table Last Updated: ' . date('r', filemtime($table_file) ) . '</p>
    ';

  return $support_table;

}
add_shortcode('openid_support_table', 'willnorris_openid_support_table');


/**
 * Shortcode for displaying my age, in years.
 */
function willnorris_my_age() {
  $now = getdate();
  $age = $now['year'] - 1982;

  if ($now['mon'] < 7 && $now['mday'] < 30) {
    $age -= 1;
  }

  return $age;
}
add_shortcode('my_age', 'willnorris_my_age');


/**
 * Add the meta tags to verify this domain for various search engine webmaster tools.
 */
function willnorris_search_engine_validation() {
  if ( is_front_page() ) {
  ?>
    <meta property="fb:admins" content="625871840" />
    <!-- Webmaster Tools Verification -->
    <?php
  }
}
add_action('wp_head', 'willnorris_search_engine_validation');


/**
 * Use CURL_CA_BUNDLE environment variable to update libcurl's cacert bundle.
 */
function willnorris_http_api_curl($handle) {
  if ( getenv('CURL_CA_BUNDLE') ) {
    curl_setopt($handle, CURLOPT_CAINFO, getenv('CURL_CA_BUNDLE'));
  }
}
add_action('http_api_curl', 'willnorris_http_api_curl');


/**
 * Prevent HTTPS requests from being cached.
 */
function willnorris_prevent_https_cache() {
  if ( is_ssl() ) {
    define('DONOTCACHEPAGE', true);
  }
}
add_action('wp', 'willnorris_prevent_https_cache');


/**
 * Attachments don't need comments
 */
function willnorris_comments_open($open, $post_id) {
	if ( is_attachment($post_id) ) {
		$open = false;
	}
	return $open;
}
add_filter('comments_open', 'willnorris_comments_open', 10, 2);


/**
 * Add +1 button script.
 */
function willnorris_plusone_script() {
?>
  <script>
    (function() {
      jQuery('<script>', {async:true, src:'https://apis.google.com/js/plusone.js'}).prependTo('script:first');
    })();
  </script>
<?php
}
add_action('wp_footer', 'willnorris_plusone_script');


/**
 * Return a +1 button for the specified post.
 */
function willnorris_plusone_button($post) {
  return '<div class="plusone-button"><g:plusone size="small" href="' . get_permalink($post) . '"></g:plusone></div>';
}


// ensure proper redirect status code is returned
add_filter('wp_redirect_status', create_function('$s', 'status_header($s); return $s;'));

// hum extensions
add_filter('hum_shortlink_base', create_function('', 'return "http://wjn.me/";'));
add_filter('hum_redirect_base_w', create_function('', 'return "http://wiki.willnorris.com/";'));
add_filter('amazon_affiliate_id', create_function('', 'return "willnorris-20";'));
