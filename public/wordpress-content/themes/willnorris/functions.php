<?php

require_once dirname(__FILE__) . '/library/thematic.php';
require_once dirname(__FILE__) . '/library/cleanup.php';
require_once dirname(__FILE__) . '/library/search.php';
require_once dirname(__FILE__) . '/library/widgets.php';
require_once dirname(__FILE__) . '/library/short-urls.php';


/**
 * Additional arguments to use when building the main menu.  
 */
function willnorris_page_menu_args($args) {
	$args['depth'] = 1;
	return $args;
}
add_filter('wp_page_menu_args', 'willnorris_page_menu_args');


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
?>
		<!-- frame buster -->
		<script type="text/javascript">if (parent.frames.length > 0) top.location.replace(document.location);</script>

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
function willnorris_footer() { 
?>
	<div id="copyright"> &copy; <?php echo date('Y'); ?>
		<address class="vcard author">
			<a class="url fn" href="http://willnorris.com/">Will Norris</a>
		</address>
	</div>
<?php
}
add_action('thematic_abovefooter', 'willnorris_footer');


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

		<!-- Webmaster Tools Verification -->
		<meta name="verify-v1" content="HQ0dYpdfPaUOtTvnC1Aj13WpaGazCoseLMPXXEnqmhA=" >
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

