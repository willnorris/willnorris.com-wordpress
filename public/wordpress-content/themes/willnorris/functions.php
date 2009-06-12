<?php

require_once dirname(__FILE__) . '/library/search.php';
require_once dirname(__FILE__) . '/library/widgets.php';



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

	$pages = get_pages();
	foreach ($pages as $page) {
		if ( $page->post_name == 'openid-support' ) {
			$excludes[] = $page->ID;
		}
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
		<meta name = "viewport" content = "width = device-width, initial-scale = 1.0">
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


function willnorris_thematic_description($content) {
	if ( is_home() || is_front_page() ) {
		$content = "\t" . '<meta name="description" content="' . get_bloginfo('description') . '" />' . "\n\n";
	}
	return $content;
}
//add_filter('thematic_create_description', 'willnorris_thematic_description');


function willnorris_thematic_doctitle($elements) {
	if ( array_key_exists('separator', $elements) ) {
		$elements['separator'] = '&#8212;';
	}

	if ( ! array_key_exists('site_name', $elements) ) {
		$elements[] = '&#8212;';
		$elements[] = get_bloginfo('name');
	}

	return $elements;
}
add_filter('thematic_doctitle', 'willnorris_thematic_doctitle');


/**
 * Cleanup lots of hooks from WordPress, thematic, and plugins.
 */
function willnorris_cleanup_hooks() {
	// don't import hcard on account creation (I've had problems with this in the past)
	remove_action('user_register', 'ext_profile_hcard_import');

	if ( !is_front_page() ) {
		remove_action('wp_head', 'actionstream_styles');
		remove_action('wp_head', 'actionstream_ext_styles', 11);
		remove_action('wp_head', 'actionstream_personal_styles', 11);
		wp_deregister_style('ext-profile');
	}

	global $wp_scripts, $wp_styles;

	$key = array_search('ext-profile', $wp_styles->queue);
	$wp_styles->dequeue($key);

	// move scripts to the footer
	$wp_scripts->add_data('jquery', 'group', 1);
	$wp_scripts->add_data('comment-reply', 'group', 1);


	$wp_scripts->add_data('jquery.imagefit', 'group', 1);
	remove_action('wp_head', 'wp_imagefit_js');

	if ( is_front_page() ) {
		// remove jquery plugin from front page
		$key = array_search('jquery.imagefit', $wp_scripts->queue);
		$wp_scripts->dequeue($key);
	} else {
		// add imagefit to footer on non-front page
		add_action('wp_footer', 'wp_imagefit_js', 20);
	}


	// don't load mint on page previews
	if ( is_preview() ) {
		remove_action('wp_footer', 'add_mint_javascript');
	}

	// ensure comment related stuff is only included when it makes sense
	$comments = false;
	if (is_single() || is_page() || is_comments_popup()) {
		global $post;
		if ( 'open' == $post->comment_status ) {
			$comments = true;
		}
	}

	if ( $comments == false ) {
		remove_action('wp_head', 'quoter_head');
		wp_deregister_script('comment-reply');
	}


	// fix share this plugin
	if (function_exists('st_widget')) {
		if (is_single()) { 
			add_filter('the_content', create_function('$c', 'return $c."<p>".st_widget()."</p>";'));
		} else {
			remove_action('wp_head', 'st_widget_head');
		}
	}

	remove_filter('get_avatar', 'ext_profile_avatar');
}
add_filter('wp', 'willnorris_cleanup_hooks', 20);


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


/**
 * Don't include thematics head scripts, which are primarily superfish and related jQuery plugins.
 */
function willnorris_head_scripts($scripts) {
	return '';
}
add_filter('thematic_head_scripts', 'willnorris_head_scripts');


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



function willnorris_admin_footer() {
	// hide annoying box on wp-super-cache config page
	if ($_REQUEST['page'] == 'wpsupercache') {
		echo '<script type="text/javascript">
			jQuery("h3:contains(\'Make WordPress Faster\')").closest("td").hide();
		</script>';
	}
}
add_filter('admin_footer', 'willnorris_admin_footer');


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


