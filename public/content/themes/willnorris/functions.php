<?php

require_once dirname(__FILE__) . '/library/thematic.php';
require_once dirname(__FILE__) . '/library/cleanup.php';
require_once dirname(__FILE__) . '/library/search.php';
require_once dirname(__FILE__) . '/library/widgets.php';
require_once dirname(__FILE__) . '/library/short-urls.php';


function willnorris_setup() {
	add_theme_support( 'post-formats', array( 'aside', 'link', 'status' ) );
}
add_action('after_setup_theme', 'willnorris_setup');


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
 * To optimize the order of styles and scripts, we need to move the main
 * theme stylesheet to a little lower in the page.  This function will 
 * hide the styleeshet only the first time it is called.
 *
 * @see http://code.google.com/speed/page-speed/docs/rtt.html#PutStylesBeforeScripts
 */
function willnorris_create_stylesheet($stylesheet) {
	static $count;
	if ( !isset($count) ) $count = 0;
	if ( $count++ <= 0 ) $stylesheet = '';

	return $stylesheet;
}
add_filter('thematic_create_stylesheet', 'willnorris_create_stylesheet');


/**
 * Add last modified time to stylesheet URI to ensure freshness.
 *
 * @see http://markjaquith.wordpress.com/2009/05/04/force-css-changes-to-go-live-immediately/
 */
function willnorris_stylesheet_uri($stylesheet) {
	return $stylesheet . '?' . filemtime( get_stylesheet_directory() . '/style.css' );
}
add_filter('stylesheet_uri', 'willnorris_stylesheet_uri');

/**
 * Additional <head> stuff.
 */
function willnorris_header() { 
/*
		<!-- frame buster -->
		<script type="text/javascript">if (parent.frames.length > 0) top.location.replace(document.location);</script>
 */

?>
		<?php echo thematic_create_stylesheet(); ?>

		<!-- iPhone support -->
		<meta name = "viewport" content = "width = device-width, initial-scale = 1.0" />
		<link media="only screen and (max-device-width: 480px)" href="<?php bloginfo('stylesheet_directory'); ?>/iphone.css" type="text/css" rel="stylesheet" />
<?php
}
add_action('wp_head', 'willnorris_header', 5);


/**
 * Add copyright at bottom of the page.
 */
function willnorris_abovefooter() { 
?>
	<div id="copyright"> &copy; 2002 &mdash; <?php echo date('Y'); ?>
		<address class="vcard author">
			<a class="url fn" href="http://willnorris.com/">Will Norris</a>
		</address>
	</div>
<?php
}
add_action('thematic_abovefooter', 'willnorris_abovefooter');


/**
 * Custom post title format for front page.
 */
function willnorris_single_post_title($title) {
	$p = get_query_var('p');
	if ($p == get_option('page_on_front')) {
		$title = get_bloginfo('name') . ' | ' . get_bloginfo('description');
	}

	return $title;
}
add_filter('single_post_title', 'willnorris_single_post_title');


add_filter('wp_redirect_status', create_function('$s', 'status_header($s); return $s;'));
add_filter('avatar_size', create_function('$s', 'return 32;'));
add_filter('extended_profile_first_name', create_function('$n', 'return "<span class=\"given-name\">William</span>";'));
add_filter('extended_profile_adr', create_function('$s', 'return preg_replace("/Current (Address)/", "\\\1", $s);'));


function willnorris_postfooter_postcategory($postcategory) {
	if ( is_attachment() ) {
		$postcategory = '';
	}
	return $postcategory;
}
add_filter('thematic_postfooter_postcategory', 'willnorris_postfooter_postcategory');


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


function willnorris_get_attachment_link($link, $id, $size, $permalink, $icon, $text) {
	$post = get_post($id);

	if ( $post->post_excerpt && empty($text) ) {

		if ( ( is_int($size) && $size != 0 ) or ( is_string($size) && $size != 'none' ) or $size != false ) {
			$link_text = wp_get_attachment_image($id, $size, $icon);
		}
		if( trim($link_text) != '' ) return $link;

		$link_text = $post->post_excerpt;

		if ( $permalink ) {
			$url = get_attachment_link($_post->ID);
		} else {
			$url = wp_get_attachment_url($post->ID);
		}

		$post_title = esc_attr($post->post_title);

		$link = "<a href='$url' title='$post_title'>$link_text</a>";
	}

	return $link;
}
add_filter('wp_get_attachment_link', 'willnorris_get_attachment_link', 10, 6);


/**
 * Replace extended profile hCard with anti-spambot version of email 
 * addres.  Also add link to PGP key,  IRC nick, and i-name.
 */
function willnorris_profile_email($email, $user_id) {
	$userdata = get_userdata($user_id);

	$email = '<dt>Email:</dt> <dd><a class="email" href="mailto:' . esc_attr(antispambot("$userdata->user_email")) . '">' . antispambot($userdata->user_email) . '</a><br />
		<a href="' . get_bloginfo('url') . '/about/pgp">My PGP Key</a></dd>';
	$email .= '
	<dt>IRC:</dt>
	<dd><a href="irc://irc.freenode.net/willnorris,isnick">willnorris@freenode</a></dd>

	<dt>i-name:</dt>
	<dd><a href="http://xri.net/=willnorris">=willnorris</a></dd>';

	return $email;
}
add_action('extended_profile_email', 'willnorris_profile_email', 10, 2);

function willnorris_profile_tel($tel, $user_id) {
	$userdata = get_userdata($user_id);
	if ( $userdata->tel ) {
		$link = 'callto:' . preg_replace('/[^0-9]/', '', $userdata->tel);
		$tel = '<dt>Telephone:</dt> <dd class="tel"><a href="' . $link . '">' . $userdata->tel . '</a></dd>';
	}
	return $tel;
}
add_action('extended_profile_tel', 'willnorris_profile_tel', 10, 2);

/**
 * Add Jabber to extended profile hCard
 */
function willnorris_profile_jabber($jabber, $user_id) {
	$userdata = get_userdata($user_id);
	return '<dt>Jabber:</dt> <dd><a class="url" href="xmpp:' . esc_attr(antispambot("$userdata->jabber")) . '">' . antispambot($userdata->jabber) . '</a></dd>';
}
add_action('extended_profile_jabber', 'willnorris_profile_jabber', 10, 2);


function willnorris_postfooter($content) {
	//Add related pages to end of post
	if ( function_exists('related_pages') ) {
		if ( related_pages_exist() ) {
			$content = related_pages(array(), false) . $content;
		}
	}

	return $content;
}
add_filter('thematic_postfooter', 'willnorris_postfooter');


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


function willnorris_comment_form() {
?>
	<div id="form-markdown-allowed" class="form-section">
		<p>You may use <a href="http://daringfireball.net/projects/markdown/syntax">Markdown</a> syntax or basic <abbr title="<?php esc_attr_e(allowed_tags()); ?>">HTML</abbr>.</p>
	</div>
<?php
}
add_action('comment_form', 'willnorris_comment_form', 1);


/**
 * Add the meta tags to verify this domain for various search engine webmaster tools.
 */
function willnorris_search_engine_validation() {
	if ( is_front_page() ) {
		?>

    <meta property="fb:admins" content="625871840" />
		<!-- Webmaster Tools Verification -->
    <meta name="google-site-verification" content="jgAYNKuVSYXv6EQKtcbwFEdW72Wkp8n7z8-8LNPO0VU" />
		<meta name="google-site-verification" content="RtjWa-bgrkaODyX7Zm4y_Co-99vLe1PDdQZ-GLyqdb4" />
		<meta name="verify-v1" content="HQ0dYpdfPaUOtTvnC1Aj13WpaGazCoseLMPXXEnqmhA=" />
		<meta name="verify-v1" content="H2qO+9/u0nX4DfYb71gnbTEtQ+Fn++f9gF5JD5iyoNs=" />
		<meta name="verify-v1" content="6fT8csNQZqJDDCYAuxQ2gCd90XcYPgJF/hi3crcZHDQ=" />
		<meta name="y_key" content="2a28a782c2529131" />
		<meta name="msvalidate.01" content="7433086B59994DBAC6E36AE0D0955E5F" />

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

function willnorris_openid_rewrite_rules($wp_rewrite) {
	$openid_rules = array(
		'wordpress/openid/(.+)' => 'index.php?openid=$matches[1]',
	);

	$wp_rewrite->rules = $openid_rules + $wp_rewrite->rules;
}
#add_action('generate_rewrite_rules', 'willnorris_openid_rewrite_rules');


/**
 * Prevent HTTPS requests from being cached.
 */
function willnorris_prevent_https_cache() {
	if ( is_ssl() ) {
		define('DONOTCACHEPAGE', true);
	}
}
add_action('wp', 'willnorris_prevent_https_cache');


function willnorris_toggle_trackbacks() {
?>
	<script type="text/javascript">
		jQuery(function() {
			jQuery('#trackbacks-list > h3').css('cursor', 'pointer').click(function() {
				jQuery('#trackbacks-list > ol').toggle();
				return false;
			});
		});
	</script>
<?php
}
add_action('wp_footer', 'willnorris_toggle_trackbacks');


function willnorris_oembed($html, $url, $attr) {
	if ( array_key_exists('link', $attr) && $attr['link'] ) { 
		$html = '<a href="' . $url . '">' . $html . '</a>';
	}   

	if ( array_key_exists('class', $attr) && $attr['class'] ) { 
		$html = '<span class="' . $attr['class'] . '">' . $html . '</span>';
	}   

	return $html;
}
add_action('embed_oembed_html', 'willnorris_oembed', 10, 3);

add_filter('hum_shortlink_base', create_function('', 'return "http://wjn.me/";'));
add_filter('hum_redirect_base_w', create_function('', 'return "http://wiki.willnorris.com/";'));
add_filter('amazon_affiliate_id', create_function('', 'return "willnorris-20";'));


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


function willnorris_plusone_button($post) {
  return '<div class="plusone-button"><g:plusone size="small" href="' . get_permalink($post) . '"></g:plusone></div>';
}

function willnorris_googleplus_links($post) {
  $links = '';

  $googleplus_url = get_post_meta($post->ID, '_googleplus_url', true);
  if ($post->post_type == 'post' && $googleplus_url) {
    $links .= ' <a href="' . $googleplus_url . '">Discuss on Google+</a>';
    $links .= '<span class="meta-sep"> | </span>';
  }
  $links .= ' ' . willnorris_plusone_button($post);

  return $links;
}


function willnorris_postfooter_postcomments($postmeta) {
  global $post;
  $postmeta = willnorris_googleplus_links($post);
  return $postmeta;
}

function willnorris_postfooter_postconnect($postmeta) {
  global $post;
  $postmeta .= ' ' . willnorris_plusone_button($post);
  return $postmeta;
}

//add_filter('thematic_postheader_postmeta', 'willnorris_postheader_postmeta');
add_filter('thematic_postfooter_postcomments', 'willnorris_postfooter_postcomments');
add_filter('thematic_postfooter_postconnect', 'willnorris_postfooter_postconnect');