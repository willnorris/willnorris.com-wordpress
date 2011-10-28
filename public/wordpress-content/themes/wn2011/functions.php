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
 * Additional <head> stuff.
 */
function willnorris_header() {
?>
    <!-- mobile support -->
    <meta name = "viewport" content = "width = device-width, initial-scale = 1.0" />
<?php
}
add_action('wp_head', 'willnorris_header', 5);


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

